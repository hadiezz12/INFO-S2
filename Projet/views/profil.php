<?php
require_once '../scripts/config.php';

// Définir la fonction au début
function calculateTotalAmount($user_voyages, $voyages) {
    $total = 0;
    foreach ($user_voyages as $voyage_data) {
        $voyage_id = is_array($voyage_data) ? ($voyage_data['id'] ?? null) : $voyage_data;
        $tickets = is_array($voyage_data) ? ($voyage_data['tickets'] ?? 1) : 1;
        $statut_paiement = is_array($voyage_data) ? ($voyage_data['statut_paiement'] ?? null) : null;
        // Ignorer les voyages payés
        if ($statut_paiement === 'payé') {
            continue;
        }
        foreach ($voyages as $v) {
            if ($v['id'] == $voyage_id) {
                $total += (float)$v['prix_total'] * $tickets;
                break;
            }
        }
    }
    return number_format($total, 2, '.', '');
}

// Charger les utilisateurs pour déterminer $user
$user = null;
if (isset($_SESSION['user_id'])) {
    $utilisateurs_json = file_get_contents('../data/utilisateurs.json');
    $utilisateurs = $utilisateurs_json ? json_decode($utilisateurs_json, true) : [];
    if (is_array($utilisateurs)) {
        foreach ($utilisateurs as $utilisateur) {
            if ($utilisateur['username'] === $_SESSION['user_id']) {
                $user = $utilisateur;
                break;
            }
        }
    }
}

// Charger les utilisateurs et voyages depuis les fichiers JSON
$utilisateurs = json_decode(file_get_contents('../data/utilisateurs.json'), true);
$voyages = json_decode(file_get_contents('../data/voyages.json'), true);

// Chercher l'utilisateur avec $_SESSION['user_id']
$user = null;
foreach ($utilisateurs as &$utilisateur) {
    if ($utilisateur['username'] === $_SESSION['user_id']) {
        $user = &$utilisateur;
        break;
    }
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id']) || !$user) {
    if (isset($_GET['token']) && !empty($_GET['token'])) {
        $_SESSION['error_message'] = "Session expirée. Veuillez vous reconnecter pour valider le paiement.";
        $_SESSION['redirect_token'] = $_GET['token'];
        header('Location: connexion.php');
        exit;
    }
    header('Location: connexion.php');
    exit;
}

// Gestion du changement de nombre de tickets via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['voyage_id']) && isset($_POST['tickets'])) {
    $voyage_id = (int)$_POST['voyage_id'];
    $tickets = max(1, min(6, (int)$_POST['tickets']));
    foreach ($user['voyages'] as &$voyage_data) {
        $id = is_array($voyage_data) ? ($voyage_data['id'] ?? null) : $voyage_data;
        $statut_paiement = is_array($voyage_data) ? ($voyage_data['statut_paiement'] ?? null) : null;
        if ($id === $voyage_id && $statut_paiement !== 'payé') {
            if (!is_array($voyage_data)) {
                $voyage_data = ['id' => $voyage_id];
            }
            $voyage_data['tickets'] = $tickets;
            break;
        }
    }
    file_put_contents('../data/utilisateurs.json', json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header("Location: profil.php");
    exit;
}

// Récupérer les voyages de l'utilisateur
$user_voyages = isset($user['voyages']) ? $user['voyages'] : [];
// Filtrer pour n'afficher que les voyages payés
$user_voyages = array_filter($user_voyages, function($voyage_data) {
    $statut = is_array($voyage_data) ? ($voyage_data['statut_paiement'] ?? null) : null;
    return $statut === 'payé';
});
// Vérifier s'il y a des voyages non payés
$has_non_paid_voyages = false;
foreach ($user_voyages as $voyage_data) {
    $statut_paiement = is_array($voyage_data) ? ($voyage_data['statut_paiement'] ?? null) : null;
    if ($statut_paiement !== 'payé') {
        $has_non_paid_voyages = true;
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Festival Goers - Profil</title>
    <link id="theme-css" rel="stylesheet" href="../css/styles.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="../css/panier.css?v=<?= time(); ?>">
    <script src="../scripts/main.js" defer></script>
    <style>
        body { visibility: hidden; }
    </style>
</head>
<body>
    <header>
        <button id="theme-switch" type="button">
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-120q-150 0-255-105T120-480q0-150 105-255t255-105q14 0 27.5 1t26.5 3q-41 29-65.5 75.5T444-660q0 90 63 153t153 63q55 0 101-24.5t75-65.5q2 13 3 26.5t1 27.5q0 150-105 255T480-120Z"/></svg>
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-280q-83 0-141.5-58.5T280-480q0-83 58.5-141.5T480-680q83 0 141.5 58.5T680-480q0 83-58.5 141.5T480-280ZM200-440H40v-80h160v80Zm720 0H760v-80h160v80ZM440-760v-160h80v160h-80Zm0 720v-160h80v160h-80ZM256-650l-101-97 57-59 96 100-52 56Zm492 496-97-101 53-55 101 97-57 59Zm-98-550 97-101 59 57-100 96-56-52ZM154-212l101-97 55 53-97 101-59-57Z"/></svg>
        </button>
        <button id="shopping" onclick="window.location.href='/Projet/views/panier.php';"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#FFFFFF"><path d="M440-600v-120H320v-80h120v-120h80v120h120v80H520v120h-80ZM280-80q-33 0-56.5-23.5T200-160q0-33 23.5-56.5T280-240q33 0 56.5 23.5T360-160q0 33-23.5 56.5T280-80Zm400 0q-33 0-56.5-23.5T600-160q0-33 23.5-56.5T680-240q33 0 56.5 23.5T760-160q0 33-23.5 56.5T680-80ZM40-800v-80h131l170 360h280l156-280h91L692-482q-11 20-29.5 31T622-440H324l-44 80h480v80H280q-45 0-68.5-39t-1.5-79l54-98-144-304H40Z"/></svg></button>
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
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <p><?php echo htmlspecialchars($_SESSION['success_message']); ?></p>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger">
                    <p><?php echo htmlspecialchars($_SESSION['error_message']); ?></p>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
            <h2>Mon Profil</h2>
            <div class="profile-info">
                <img src="<?php echo htmlspecialchars($user['profile_picture'] ?? '../images/profile.jpg'); ?>" class="profile-pic" alt="">
                <div class="profile-details">
                    <p><strong>Nom d'utilisateur:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Bio:</strong> <?php echo htmlspecialchars($user['infos']['bio'] ?? 'Non renseignée'); ?></p>
                    <?php if (isset($user['role']) && $user['role'] === 'admin'): ?>
                        <p><strong>Rôle:</strong> Administrateur</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="profile-actions">
                <a href="edit_profil.php" class="btn">Modifier le profil</a>
                <a href="../scripts/deconnexion.php" class="btn">Se déconnecter</a>
            </div>
            <h2>Mes voyages</h2>
            <?php if (!empty($user_voyages)): ?>
                <div class="profile-results-list">
                    <?php foreach ($user_voyages as $voyage_data): ?>
                        <?php
                        $voyage_id = is_array($voyage_data) ? ($voyage_data['id'] ?? null) : $voyage_data;
                        $tickets = is_array($voyage_data) ? ($voyage_data['tickets'] ?? 1) : 1;
                        $statut_paiement = is_array($voyage_data) ? ($voyage_data['statut_paiement'] ?? null) : null;
                        $voyage = null;
                        foreach ($voyages as $v) {
                            if ($v['id'] == $voyage_id) {
                                $voyage = $v;
                                break;
                            }
                        }
                        if ($voyage):
                            $prix_base = (float)($voyage['prix_total']);
                            $prix_total = $prix_base * $tickets;
                        ?>
                            <div class="profile-result-item" data-voyage-id="<?php echo $voyage['id']; ?>">
                                <img src="<?php echo htmlspecialchars($voyage['etapes'][0]['image'] ?? 'https://via.placeholder.com/150x90'); ?>" alt="<?php echo htmlspecialchars($voyage['titre']); ?>">
                                <div class="profile-result-details">
                                    <h3><?php echo htmlspecialchars($voyage['titre']); ?></h3>
                                    <p><span>📍</span> <?php echo htmlspecialchars($voyage['etapes'][0]['lieu']); ?></p>
                                    <p><span>🗓️</span> <?php echo $voyage['dates']['debut'] . " - " . $voyage['dates']['fin']; ?></p>
                                    <p class="price" data-base-price="<?php echo $prix_base; ?>"><span>💰</span> <?php echo number_format($prix_total, 2); ?> € (<?php echo $tickets; ?> ticket<?php echo $tickets > 1 ? 's' : ''; ?>)</p>
                                    <p><span>💳 Statut :</span> <?php echo $statut_paiement ? htmlspecialchars($statut_paiement) : 'En attente'; ?></p>
                                    <a href="voir_recap.php?id=<?php echo $voyage['id']; ?>" class="btn">Voir le récapitulatif</a>
                                    <?php if ($statut_paiement !== 'payé'): ?>
                                        <a href="../scripts/supprimer_reservation.php?id=<?php echo $voyage['id']; ?>" class="btn btn-danger" onclick="return confirm('Voulez-vous vraiment supprimer cette réservation ?');">Supprimer</a>
                                        <form method="POST" class="ticket-selector">
                                            <input type="hidden" name="voyage_id" value="<?php echo $voyage['id']; ?>">
                                            <label for="tickets_<?php echo $voyage['id']; ?>">Tickets :</label>
                                            <select name="tickets" id="tickets_<?php echo $voyage['id']; ?>" data-voyage-id="<?php echo $voyage['id']; ?>">
                                                <?php for ($i = 1; $i <= 6; $i++): ?>
                                                    <option value="<?php echo $i; ?>" <?php echo $tickets == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                                <?php endfor; ?>
                                            </select>
                                            <button type="submit">Mettre à jour</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php if ($has_non_paid_voyages): ?>
                <?php endif; ?>
            <?php else: ?>
                <p class="bas">Aucun voyage réservé pour le moment.</p>
            <?php endif; ?>
        </section>
    </main>
    <footer>
        <p>© 2025 Festival Goers - Tous droits réservés</p>
    </footer>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Stocker les voyages et leurs prix de base
            const voyageData = {};
            document.querySelectorAll('.profile-result-item').forEach(item => {
                const voyageId = item.dataset.voyageId;
                const priceElement = item.querySelector('.price');
                const basePrice = parseFloat(priceElement.dataset.basePrice);
                voyageData[voyageId] = { basePrice };
            });

            // Mettre à jour le prix dynamiquement
            document.querySelectorAll('select[name="tickets"]').forEach(select => {
                select.addEventListener('change', function() {
                    const voyageId = this.dataset.voyageId;
                    const tickets = parseInt(this.value);
                    const item = document.querySelector(`.profile-result-item[data-voyage-id="${voyageId}"]`);
                    const priceElement = item.querySelector('.price');
                    const basePrice = voyageData[voyageId].basePrice;
                    const newPrice = (basePrice * tickets).toFixed(2);
                    priceElement.innerHTML = `<span>💰</span> ${newPrice} € (${tickets} ticket${tickets > 1 ? 's' : ''})`;

                    // Mettre à jour le montant total
                    let total = 0;
                    document.querySelectorAll('.profile-result-item').forEach(item => {
                        const itemVoyageId = item.dataset.voyageId;
                        const itemSelect = item.querySelector('select[name="tickets"]');
                        if (itemSelect) {
                            const itemTickets = parseInt(itemSelect.value);
                            const itemBasePrice = voyageData[itemVoyageId].basePrice;
                            total += itemBasePrice * itemTickets;
                        }
                    });
                    const totalFormatted = total.toFixed(2);
                    const totalInput = document.querySelector('#total-amount');
                    totalInput.value = totalFormatted;
                    const finalizeButton = document.querySelector('.btn-finalize');
                    if (finalizeButton) {
                        finalizeButton.textContent = `Finaliser la réservation (${totalFormatted} €)`;
                    }
                });
            });

            // Afficher le contenu après chargement
            window.addEventListener('load', () => {
                document.body.style.visibility = 'visible';
            });
        });
    </script>
</body>
</html>