<?php
// Vérifier que le script s'exécute via HTTP/HTTPS
if (PHP_SAPI === 'cli' || empty($_SERVER['REMOTE_ADDR'])) {
    die('Ce script doit être exécuté via un serveur web (HTTP/HTTPS).');
}

// Démarrer la session avec gestion des erreurs
if (!session_start()) {
    die('Erreur lors du démarrage de la session.');
}

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

// Charger les fichiers JSON avec gestion des erreurs
$utilisateurs_file = '../data/utilisateurs.json';
$voyages_file = '../data/voyages.json';

if (!file_exists($utilisateurs_file) || !file_exists($voyages_file)) {
    die('Erreur : Fichier JSON manquant.');
}

$utilisateurs = json_decode(file_get_contents($utilisateurs_file), true);
$voyages = json_decode(file_get_contents($voyages_file), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die('Erreur lors du décodage JSON : ' . json_last_error_msg());
}

$user = null;
foreach ($utilisateurs as &$utilisateur) {
    if ($utilisateur['username'] === $_SESSION['user_id']) {
        $user = &$utilisateur;
        break;
    }
}

if (!$user) {
    header('Location: connexion.php');
    exit;
}

$cart_voyages = array_filter($user['voyages'] ?? [], function($v) {
    return is_array($v) && ($v['statut_paiement'] ?? null) === 'en attente';
});

function getVoyageById($voyages, $id) {
    foreach ($voyages as $v) {
        if ($v['id'] == $id) {
            return $v;
        }
    }
    return null;
}

function calculateCartTotal($cart_voyages, $voyages) {
    $total = 0;
    foreach ($cart_voyages as $v) {
        $voyage = getVoyageById($voyages, $v['id']);
        if ($voyage) {
            $total += (float)$voyage['prix_total'] * ($v['tickets'] ?? 1);
        }
    }
    return number_format($total, 2, '.', '');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon panier - Festival Goers</title>
    <link id="theme-css" rel="stylesheet" href="../css/styles.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="../css/panier.css?v=<?= time(); ?>">
</head>
<body>
<header>
    <button id="theme-switch" type="button">
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-120q-150 0-255-105T120-480q0-150 105-255t255-105q14 0 27.5 1t26.5 3q-41 29-65.5 75.5T444-660q0 90 63 153t153 63q55 0 101-24.5t75-65.5q2 13 3 26.5t1 27.5q0 150-105 255T480-120Z"/></svg>
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-280q-83 0-141.5-58.5T280-480q0-83 58.5-141.5T480-680q83 0 141.5 58.5T680-480q0 83-58.5 141.5T480-280ZM200-440H40v-80h160v80Zm720 0H760v-80h160v80ZM440-760v-160h80v160h-80Zm0 720v-160h80v160h-80ZM256-650l-101-97 57-59 96 100-52 56Zm492 496-97-101 53-55 101 97-57 59Zm-98-550 97-101 59 57-100 96-56-52ZM154-212l101-97 55 53-97 101-59-57Z"/></svg>
    </button>
    <nav class="navbar">
        <div class="logo"><a href="../index.php">Festival Goers</a></div>
        <ul class="nav-links">
            <li><a href="../index.php">Accueil</a></li>
            <li><a href="presentation.php">Présentation</a></li>
            <li><a href="recherche.php">Recherche</a></li>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <li><a href="inscription.php">Inscription</a></li>
                <li><a href="connexion.php">Connexion</a></li>
            <?php endif; ?>
            <li><a href="profil.php" class="active">Profil</a></li>
            <?php if (isset($user['role']) && $user['role'] === 'admin'): ?>
                <li><a href="admin.php">Admin</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<main>  
    <section class="profile">
        <h2>Mon panier</h2>
        <?php if (isset($_SESSION['success'])): ?>
            <p class="success"><?php echo htmlspecialchars($_SESSION['success']); ?></p>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <p class="error"><?php echo htmlspecialchars($_SESSION['error']); ?></p>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <?php if (empty($cart_voyages)): ?>
            <p>Votre panier est vide.</p>
        <?php else: ?>
            <div class="profile-results-list">
                <?php foreach ($cart_voyages as $voyage_data): 
                    $voyage = getVoyageById($voyages, $voyage_data['id']);
                    if (!$voyage) {
                        continue;
                    }
                    $tickets = $voyage_data['tickets'] ?? 1;
                ?>
                <div class="profile-result-item" data-voyage-id="<?php echo htmlspecialchars($voyage['id']); ?>">
                    <img src="<?php echo htmlspecialchars($voyage['etapes'][0]['image'] ?? ''); ?>" alt="">
                    <div class="profile-result-details">
                        <h3><?php echo htmlspecialchars($voyage['titre']); ?></h3>
                        <p><span>📍</span> <?php echo htmlspecialchars($voyage['etapes'][0]['lieu']); ?></p>
                        <p><span>🗓️</span> <?php echo htmlspecialchars($voyage['dates']['debut'] . " - " . $voyage['dates']['fin']); ?></p>
                        <p class="price" data-base-price="<?php echo htmlspecialchars($voyage['prix_total']); ?>">
                            <span>💰</span> <?php echo number_format($voyage['prix_total'] * $tickets, 2); ?> € (<?php echo $tickets; ?> ticket<?php echo $tickets > 1 ? 's' : ''; ?>)
                        </p>
                        <form method="POST" action="../scripts/update_panier.php" class="ticket-selector" style="display:inline;">
                            <input type="hidden" name="voyage_id" value="<?php echo htmlspecialchars($voyage['id']); ?>">
                            <label for="tickets_<?php echo htmlspecialchars($voyage['id']); ?>">Tickets :</label>
                            <select name="tickets" id="tickets_<?php echo htmlspecialchars($voyage['id']); ?>" data-voyage-id="<?php echo htmlspecialchars($voyage['id']); ?>">
                                <?php for ($i = 1; $i <= 6; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $tickets == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                            <button type="submit">Mettre à jour</button>
                        </form>
                        <a href="../scripts/supprimer_reservation.php?id=<?php echo htmlspecialchars($voyage['id']); ?>&from=panier" class="btn btn-danger" onclick="return confirm('Voulez-vous vraiment supprimer ce voyage du panier ?');">Supprimer</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if (!empty($cart_voyages)): ?>
                <div class="finalize-action" id="finalize-action">
                    <form action="../scripts/cybank_payment.php" method="POST" id="payment-form">
                        <?php
                        $username_safe = preg_replace('/[^0-9a-zA-Z]/', '', $user['username']);
                        $transaction = 'TRANS' . time() . $username_safe;
                        $transaction = substr($transaction, 0, 24);
                        $token = bin2hex(random_bytes(16));
                        $montant = calculateCartTotal($cart_voyages, $voyages);

                        // Sauvegarder les données de paiement dans utilisateurs.json
                        $payment_data = [
                            'token' => $token,
                            'user_id' => $_SESSION['user_id'],
                            'transaction' => $transaction,
                            'montant' => $montant,
                            'timestamp' => time()
                        ];
                        $user['pending_payments'] = isset($user['pending_payments']) ? $user['pending_payments'] : [];
                        $user['pending_payments'][$token] = $payment_data;
                        foreach ($utilisateurs as &$u) {
                            if ($u['username'] === $_SESSION['user_id']) {
                                $u['pending_payments'] = $user['pending_payments'];
                                break;
                            }
                        }
                        file_put_contents($utilisateurs_file, json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                        error_log("Données de paiement sauvegardées dans utilisateurs.json pour token : $token");

                        $_SESSION['payment_token'] = $token;
                        $_SESSION['payment_user_id'] = $_SESSION['user_id'];
                        $_SESSION['last_transaction'] = $transaction;
                        $_SESSION['last_montant'] = $montant;
                        error_log("Transaction générée dans panier.php : '$transaction'");
                        ?>
                        <input type="hidden" name="transaction" value="<?php echo htmlspecialchars($transaction); ?>">
                        <input type="hidden" name="montant" id="total-amount" value="<?php echo htmlspecialchars($montant); ?>">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        <input type="hidden" name="retour" value="http://127.0.0.1/Projet/scripts/retour_paiement.php?token=<?php echo htmlspecialchars($token); ?>">
                        <button type="submit" class="btn btn-finalize">Finaliser la réservation (<?php echo htmlspecialchars($montant); ?> €)</button>
                    </form>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</main>
<footer>
    <p>© 2025 Festival Goers - Tous droits réservés</p>
</footer>
<script src="../scripts/main.js" defer></script> <!-- Ajout de main.js pour gérer le thème -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Stocker les voyages et leurs prix de base
    const voyageData = {};
    document.querySelectorAll('.profile-result-item').forEach(item => {
        const voyageId = item.dataset.voyageId;
        const priceElement = item.querySelector('.price');
        if (priceElement && priceElement.dataset.basePrice) {
            const basePrice = parseFloat(priceElement.dataset.basePrice);
            voyageData[voyageId] = { basePrice };
        }
    });

    // Mettre à jour le prix dynamiquement
    document.querySelectorAll('select[name="tickets"]').forEach(select => {
        select.addEventListener('change', function() {
            const voyageId = this.dataset.voyageId;
            const tickets = parseInt(this.value);
            const item = document.querySelector(`.profile-result-item[data-voyage-id="${voyageId}"]`);
            const priceElement = item.querySelector('.price');
            const basePrice = voyageData[voyageId]?.basePrice;
            if (basePrice) {
                const newPrice = (basePrice * tickets).toFixed(2);
                priceElement.innerHTML = `<span>💰</span> ${newPrice} € (${tickets} ticket${tickets > 1 ? 's' : ''})`;

                // Mettre à jour le montant total
                let total = 0;
                const items = document.querySelectorAll('.profile-result-item');
                items.forEach(item => {
                    const itemVoyageId = item.dataset.voyageId;
                    const itemSelect = item.querySelector('select[name="tickets"]');
                    if (itemSelect && voyageData[itemVoyageId]?.basePrice) {
                        const itemTickets = parseInt(itemSelect.value);
                        const itemBasePrice = voyageData[itemVoyageId].basePrice;
                        total += itemBasePrice * itemTickets;
                    }
                });
                const totalFormatted = total.toFixed(2);
                const totalInput = document.querySelector('#total-amount');
                if (totalInput) {
                    totalInput.value = totalFormatted;
                }
                const finalizeButton = document.querySelector('.btn-finalize');
                if (finalizeButton) {
                    finalizeButton.textContent = `Finaliser la réservation (${totalFormatted} €)`;
                }
                // Masquer le bouton si le panier est vide
                const finalizeAction = document.querySelector('#finalize-action');
                if (items.length === 0 && finalizeAction) {
                    finalizeAction.style.display = 'none';
                }
            }
        });
    });
});
</script>
</body>
</html>