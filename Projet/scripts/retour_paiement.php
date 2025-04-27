<?php
require_once '../scripts/config.php';
require_once 'getapikey.php';

// Fonction pour lire les fichiers JSON
function safeJsonDecode($filePath, $assoc = true) {
    $content = @file_get_contents($filePath);
    if ($content === false) {
        error_log("Erreur de lecture : $filePath");
        return null;
    }
    $data = json_decode($content, $assoc);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Erreur JSON dans $filePath : " . json_last_error_msg());
        return null;
    }
    return $data;
}

// Gérer les requêtes GET (retour depuis la plateforme externe)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['token']) && !empty($_GET['token'])) {
        $token = $_GET['token'];
        $_SESSION['redirect_token'] = $token;
        error_log("Requête GET reçue avec token : $token");

        // Charger les utilisateurs depuis utilisateurs.json
        $utilisateurs = safeJsonDecode('../data/utilisateurs.json');
        $payment = null;
        $payment_user_id = null;

        foreach ($utilisateurs as $utilisateur) {
            if (isset($utilisateur['pending_payments'][$token])) {
                $payment = $utilisateur['pending_payments'][$token];
                $payment_user_id = $utilisateur['username'];
                break;
            }
        }

        if (!$payment || !isset($payment['user_id']) || !isset($payment['transaction']) || !isset($payment['montant'])) {
            error_log("Données de paiement introuvables pour token : $token");
            $_SESSION['error_message'] = "Veuillez vous connecter pour valider le paiement.";
            header("Location: ../views/connexion.php");
            exit();
        }

        // Restaurer les données dans la session
        $_SESSION['user_id'] = $payment['user_id'];
        $_SESSION['last_transaction'] = $payment['transaction'];
        $_SESSION['last_montant'] = $payment['montant'];
        error_log("Données de paiement restaurées pour user_id : {$payment['user_id']}, transaction : {$payment['transaction']}");

        // Simuler une requête POST
        ?>
        <!DOCTYPE html>
        <html>
        <body>
            <form id="paymentForm" action="retour_paiement.php" method="POST">
                <input type="hidden" name="transaction" value="<?php echo htmlspecialchars($_SESSION['last_transaction']); ?>">
                <input type="hidden" name="montant" value="<?php echo htmlspecialchars($_SESSION['last_montant']); ?>">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <?php
                $api_key = getAPIKey('FestivalGoers');
                $control = md5($api_key . "#" . $_SESSION['last_transaction'] . "#" . $_SESSION['last_montant'] . "#FestivalGoers#http://127.0.0.1/Projet/scripts/retour_paiement.php?token=" . $token . "#");
                ?>
                <input type="hidden" name="control" value="<?php echo htmlspecialchars($control); ?>">
            </form>
            <script>document.getElementById('paymentForm').submit();</script>
        </body>
        </html>
        <?php
        exit();
    } else {
        error_log("Requête GET sans token valide");
        $_SESSION['error_message'] = "Accès non autorisé.";
        header("Location: ../views/connexion.php");
        exit();
    }
}

// Vérifier la méthode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Méthode non autorisée");
    $_SESSION['error_message'] = "Méthode non autorisée.";
    header("Location: ../views/connexion.php");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    error_log("Session user_id manquante");
    $_SESSION['error_message'] = "Vous devez être connecté.";
    header("Location: ../views/connexion.php");
    exit();
}

// Récupérer et valider les données POST
$transaction = $_POST['transaction'] ?? '';
$control = $_POST['control'] ?? '';
$montant = $_POST['montant'] ?? '';
$token = $_POST['token'] ?? '';
$api_key = getAPIKey('FestivalGoers');
$control_check = md5($api_key . "#" . $transaction . "#" . $montant . "#FestivalGoers#http://127.0.0.1/Projet/scripts/retour_paiement.php?token=" . $token . "#");

error_log("POST reçu : transaction=$transaction, montant=$montant, token=$token, control=$control, attendu=$control_check");

if ($control !== $control_check) {
    error_log("Échec du hash : reçu '$control', attendu '$control_check'");
    $_SESSION['error_message'] = "Erreur de validation du paiement.";
    header("Location: ../views/profil.php");
    exit();
}

if (!preg_match('/^[0-9a-zA-Z]{10,24}$/', $transaction)) {
    error_log("Transaction invalide : '$transaction'");
    $_SESSION['error_message'] = "Transaction invalide.";
    header("Location: ../views/profil.php");
    exit();
}

if (!preg_match('/^[0-9]+\\.[0-9]{2}$/', $montant)) {
    error_log("Montant invalide : '$montant'");
    $_SESSION['error_message'] = "Montant invalide.";
    header("Location: ../views/profil.php");
    exit();
}

// Charger les données
$utilisateurs = safeJsonDecode('../data/utilisateurs.json');
$voyages = safeJsonDecode('../data/voyages.json');
if ($utilisateurs === null || $voyages === null) {
    error_log("Échec du chargement JSON");
    $_SESSION['error_message'] = "Erreur de chargement des données.";
    header("Location: ../views/profil.php");
    exit();
}

// Vérifier les permissions
if (!is_writable('../data/utilisateurs.json')) {
    error_log("utilisateurs.json non accessible en écriture");
    $_SESSION['error_message'] = "Erreur serveur : mise à jour impossible.";
    header("Location: ../views/profil.php");
    exit();
}

// Mettre à jour l'utilisateur
$user_found = false;
$voyage_ids = array_column($voyages, 'id');
foreach ($utilisateurs as &$utilisateur) {
    if ($utilisateur['username'] === $_SESSION['user_id']) {
        $user_found = true;
        $voyages_updated = [];
        $updated = false;

        foreach ($utilisateur['voyages'] as $voyage_data) {
            $voyage_id = is_array($voyage_data) ? ($voyage_data['id'] ?? null) : $voyage_data;
            $tickets = is_array($voyage_data) ? ($voyage_data['tickets'] ?? 1) : 1;
            $existing_statut = is_array($voyage_data) ? ($voyage_data['statut_paiement'] ?? null) : null;

            if (!in_array($voyage_id, $voyage_ids)) {
                error_log("Voyage ID $voyage_id non trouvé pour {$utilisateur['username']}");
                $voyages_updated[] = is_array($voyage_data) ? $voyage_data : ['id' => $voyage_id, 'tickets' => $tickets];
                continue;
            }

            if ($existing_statut === 'payé') {
                error_log("Voyage ID $voyage_id déjà payé pour {$utilisateur['username']}");
                $voyages_updated[] = is_array($voyage_data) ? $voyage_data : ['id' => $voyage_id, 'tickets' => $tickets];
                continue;
            }

            $voyage_entry = [
                'id' => $voyage_id,
                'tickets' => $tickets,
                'date_paiement' => date('Y-m-d H:i:s'),
                'statut_paiement' => 'payé',
                'transaction_id' => $transaction
            ];
            $voyages_updated[] = $voyage_entry;
            $updated = true;
            error_log("Voyage ID $voyage_id payé pour {$utilisateur['username']}, transaction : $transaction");
        }

        if ($updated) {
            $utilisateur['voyages'] = $voyages_updated;
            // Supprimer les données de paiement temporaire
            if (isset($utilisateur['pending_payments'][$token])) {
                unset($utilisateur['pending_payments'][$token]);
                if (empty($utilisateur['pending_payments'])) {
                    unset($utilisateur['pending_payments']);
                }
                error_log("Données temporaires supprimées pour token : $token");
            }
            if (file_put_contents('../data/utilisateurs.json', json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
                error_log("Erreur d'écriture dans utilisateurs.json, transaction : '$transaction'");
                $_SESSION['error_message'] = "Erreur lors de la sauvegarde.";
                header("Location: ../views/profil.php");
                exit();
            }
            $_SESSION['success_message'] = "Paiement validé ! Réservations mises à jour.";
            error_log("utilisateurs.json mis à jour pour {$utilisateur['username']}");
        } else {
            error_log("Aucun voyage mis à jour pour {$utilisateur['username']} : déjà payés ou invalides");
            $_SESSION['error_message'] = "Aucun voyage à mettre à jour.";
        }
        break;
    }
}

if (!$user_found) {
    error_log("Utilisateur non trouvé, transaction : '$transaction', user_id : " . ($_SESSION['user_id'] ?? 'non défini'));
    $_SESSION['error_message'] = "Utilisateur non trouvé.";
    header("Location: ../views/connexion.php");
    exit();
}

header("Location: ../views/profil.php");
exit();
?>