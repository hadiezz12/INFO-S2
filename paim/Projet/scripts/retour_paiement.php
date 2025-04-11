<?php
session_start();
require('getapikey.php');

// Activer les logs
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/Applications/MAMP/logs/php_error.log');

// Retrieve return parameters
$transaction = $_GET['transaction'] ?? null;
$montant = $_GET['montant'] ?? null;
$vendeur = $_GET['vendeur'] ?? null;
$statut = $_GET['statut'] ?? null;
$control = $_GET['control'] ?? null;

error_log("Retour - Transaction : '$transaction'");
error_log("Retour - Montant : '$montant'");
error_log("Retour - Vendeur : '$vendeur'");
error_log("Retour - Statut : '$statut'");
error_log("Retour - Control reçu : '$control'");

if (!$transaction || !$montant || !$vendeur || !$statut || !$control) {
    error_log("Paramètres manquants");
    die("Missing parameters.");
}

// Validate the control hash
$api_key = getAPIKey($vendeur);
error_log("Retour - API Key : '$api_key'");
if (!preg_match('/^[0-9a-zA-Z]{15}$/', $api_key)) {
    error_log("Retour - API Key invalide");
    die("Invalid API key.");
}

$expected_control = md5($api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#" . $statut . "#");
error_log("Retour - Control attendu : '$expected_control'");
if ($control !== $expected_control) {
    error_log("Retour - Control invalide. Reçu: '$control', Attendu: '$expected_control'");
    die("Invalid control hash. Received: '$control', Expected: '$expected_control'");
}

// Process the payment result
if ($statut === 'accepted' && isset($_SESSION['user_id'])) {
    $utilisateurs_json = file_get_contents('../data/utilisateurs.json');
    $utilisateurs = $utilisateurs_json ? json_decode($utilisateurs_json, true) : [];
    foreach ($utilisateurs as &$utilisateur) {
        if ($utilisateur['username'] === $_SESSION['user_id']) {
            foreach ($utilisateur['voyages'] as &$voyage) {
                if (!is_array($voyage)) {
                    $voyage = ['id' => $voyage];
                }
                if (!isset($voyage['date_paiement'])) {
                    $voyage['date_paiement'] = date('Y-m-d');
                }
            }
            unset($voyage);
            break;
        }
    }
    unset($utilisateur);
    file_put_contents('../data/utilisateurs.json', json_encode($utilisateurs, JSON_PRETTY_PRINT));
    $message = "Paiement accepté. Transaction ID: " . htmlspecialchars($transaction);
    $redirect = "../views/profil.php";
} else {
    $message = "Paiement refusé. Transaction ID: " . htmlspecialchars($transaction);
    $redirect = "../views/profil.php";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="3;url=<?php echo $redirect; ?>">
    <title>Résultat du paiement</title>
</head>
<body>
    <p><?php echo $message; ?></p>
    <p>Redirection dans 3 secondes... <a href="<?php echo $redirect; ?>">Retour au profil</a></p>
</body>
</html>
