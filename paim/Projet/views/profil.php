<?php
session_start();

// Définir la fonction au début
function calculateTotalAmount($user_voyages, $voyages) {
    $total = 0;
    foreach ($user_voyages as $voyage_data) {
        $voyage_id = is_array($voyage_data) ? ($voyage_data['id'] ?? null) : $voyage_data;
        $tickets = is_array($voyage_data) ? ($voyage_data['tickets'] ?? 1) : 1;
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
    header('Location: connexion.php');
    exit;
}

// Gestion du changement de nombre de tickets via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['voyage_id']) && isset($_POST['tickets'])) {
    $voyage_id = (int)$_POST['voyage_id'];
    $tickets = max(1, min(6, (int)$_POST['tickets']));
    foreach ($user['voyages'] as &$voyage_data) {
        $id = is_array($voyage_data) ? ($voyage_data['id'] ?? null) : $voyage_data;
        if ($id === $voyage_id) {
            if (!is_array($voyage_data)) {
                $voyage_data = ['id' => $voyage_id];
            }
            $voyage_data['tickets'] = $tickets;
            break;
        }
    }
    file_put_contents('../data/utilisateurs.json', json_encode($utilisateurs, JSON_PRETTY_PRINT));
    header("Location: profil.php");
    exit;
}

// Récupérer les voyages de l'utilisateur
$user_voyages = isset($user['voyages']) ? $user['voyages'] : [];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Festival Goers - Profil</title>
    <link rel="stylesheet" href="../css/styles.css?v=<?= time(); ?>">
</head>
<body>
    <header>
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
                            <div class="profile-result-item">
                                <img src="<?php echo htmlspecialchars($voyage['etapes'][0]['image'] ?? 'https://via.placeholder.com/150x90'); ?>" alt="<?php echo htmlspecialchars($voyage['titre']); ?>">
                                <div class="profile-result-details">
                                    <h3><?php echo htmlspecialchars($voyage['titre']); ?></h3>
                                    <p><span>📍</span> <?php echo htmlspecialchars($voyage['etapes'][0]['lieu']); ?></p>
                                    <p><span>🗓️</span> <?php echo $voyage['dates']['debut'] . " - " . $voyage['dates']['fin']; ?></p>
                                    <p><span>💰</span> <?php echo number_format($prix_total, 2); ?> € (<?php echo $tickets; ?> ticket<?php echo $tickets > 1 ? 's' : ''; ?>)</p>
                                    <a href="voyage_détails.php?id=<?php echo $voyage['id']; ?>&readonly=true" class="btn">Voir les détails</a>
                                    <a href="../scripts/supprimer_reservation.php?id=<?php echo $voyage['id']; ?>" class="btn btn-danger" onclick="return confirm('Voulez-vous vraiment supprimer cette réservation ?');">Supprimer</a>
                                    <form method="POST" class="ticket-selector">
                                        <input type="hidden" name="voyage_id" value="<?php echo $voyage['id']; ?>">
                                        <label for="tickets_<?php echo $voyage['id']; ?>">Tickets :</label>
                                        <select name="tickets" id="tickets_<?php echo $voyage['id']; ?>">
                                            <?php for ($i = 1; $i <= 6; $i++): ?>
                                                <option value="<?php echo $i; ?>" <?php echo $tickets == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                        <button type="submit">Mettre à jour</button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <!-- Dans la section finalize-action -->
<div class="finalize-action">
    <form action="../scripts/cybank_payment.php" method="POST">
        <?php
        $username_safe = preg_replace('/[^0-9a-zA-Z]/', '', $user['username']);
        $transaction = 'TRANS' . time() . $username_safe;
        $transaction = substr($transaction, 0, 24);
        error_log("Transaction générée dans profil.php : '$transaction'");
        ?>
        <input type="hidden" name="transaction" value="<?php echo htmlspecialchars($transaction); ?>">
        <input type="hidden" name="montant" value="<?php echo calculateTotalAmount($user_voyages, $voyages); ?>">
        <input type="hidden" name="retour" value="http://127.0.0.1/Projet/scripts/retour_paiement.php">
        <button type="submit" class="btn btn-finalize">Finaliser la réservation</button>
    </form>
</div>
            <?php else: ?>
                <p class="bas">Aucun voyage réservé pour le moment.</p>
            <?php endif; ?>
        </section>
    </main>
    <footer>
        <p>© 2025 Festival Goers - Tous droits réservés</p>
    </footer>
</body>
</html>