<?php
session_start();

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

$voyages = json_decode(file_get_contents('../data/voyages.json'), true);
$utilisateurs = json_decode(file_get_contents('../data/utilisateurs.json'), true);
$voyage_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$voyage = null;
foreach ($voyages as $v) {
    if ($v['id'] === $voyage_id) {
        $voyage = $v;
        break;
    }
}
if (!$voyage) {
    header("Location: recherche.php");
    exit();
}

$is_banned = false;
$is_reserved = false;
if (isset($_SESSION['user_id'])) {
    foreach ($utilisateurs as $utilisateur) {
        if ($utilisateur['username'] === $_SESSION['user_id']) {
            if (isset($utilisateur['statut']) && $utilisateur['statut'] === 'banned') {
                $is_banned = true;
            }
            if (isset($utilisateur['voyages']) && is_array($utilisateur['voyages'])) {
                foreach ($utilisateur['voyages'] as $voyage_data) {
                    $reserved_id = is_array($voyage_data) ? ($voyage_data['id'] ?? null) : $voyage_data;
                    if ($reserved_id === $voyage_id) {
                        $is_reserved = true;
                        break;
                    }
                }
            }
            break;
        }
    }
}

$updated = isset($_GET['updated']) && $_GET['updated'] == 1;
$added = isset($_GET['added']) && $_GET['added'] == 1;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du voyage - Festival Goers</title>
    <!-- Script inline pour styles de base -->
    <script>
        (function() {
            function getCookie(name) {
                const value = `; ${document.cookie}`;
                const parts = value.split(`; ${name}=`);
                if (parts.length === 2) return parts.pop().split(';').shift();
                return null;
            }
            const theme = getCookie('theme') || 'dark';
            const style = document.createElement('style');
            style.textContent = theme === 'dark'
                ? 'body { background-color: #1a1a1a; color: #e8eaed; visibility: hidden; }'
                : 'body { background-color: #f0f0f0; color: #333; visibility: hidden; }';
            document.head.appendChild(style);
            // Fallback : charger le CSS si main.js échoue
            setTimeout(() => {
                const cssLink = document.getElementById('theme-css');
                if (!cssLink.href) {
                    cssLink.href = `../css/${theme === 'dark' ? 'styles' : 'light'}.css?v=${new Date().getTime()}`;
                }
            }, 1000);
        })();
    </script>
    <!-- Charger le CSS via main.js -->
    <link id="theme-css" rel="stylesheet" href="">
    <script src="../scripts/main.js" defer></script>
    <link rel="stylesheet" href="../css/panier.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="../css/popup.css?v=<?= time(); ?>">
</head>
<body>
    <header>
        <button id="theme-switch">
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-120q-150 0-255-105T120-480q0-150 105-255t255-105q14 0 27.5 1t26.5 3q-41 29-65.5 75.5T444-660q0 90 63 153t153 63q55 0 101-24.5t75-65.5q2 13 3 26.5t1 27.5q0 150-105 255T480-120Z"/></svg>
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-280q-83 0-141.5-58.5T280-480q0-83 58.5-141.5T480-680q83 0 141.5 58.5T680-480q0 83-58.5 141.5T480-280ZM200-440H40v-80h160v80Zm720 0H760v-80h160v80ZM440-760v-160h80v160h-80Zm0 720v-160h80v160h-80ZM256-650l-101-97 57-59 96 100-52 56Zm492 496-97-101 53-55 101 97-57 59Zm-98-550 97-101 59 57-100 96-56-52ZM154-212l101-97 55 53-97 101-59-57Z"/></svg>
            <button id="shopping" onclick="window.location.href='/Projet/views/panier.php';"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#FFFFFF"><path d="M440-600v-120H320v-80h120v-120h80v120h120v80H520v120h-80ZM280-80q-33 0-56.5-23.5T200-160q0-33 23.5-56.5T280-240q33 0 56.5 23.5T360-160q0 33-23.5 56.5T280-80Zm400 0q-33 0-56.5-23.5T600-160q0-33 23.5-56.5T680-240q33 0 56.5 23.5T760-160q0 33-23.5 56.5T680-80ZM40-800v-80h131l170 360h280l156-280h91L692-482q-11 20-29.5 31T622-440H324l-44 80h480v80H280q-45 0-68.5-39t-1.5-79l54-98-144-304H40Z"/></svg></button>
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
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="profil.php">Profil</a></li>
                <?php endif; ?>
                <?php if (isset($user['role']) && $user['role'] === 'admin'): ?>
                    <li><a href="admin.php">Admin</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main>
        <?php if ($is_banned): ?>
            <div class="alert alert-banned">
                <p><strong>Attention :</strong> Votre compte est banni. Vous ne pouvez pas effectuer d'actions sur les voyages.</p>
            </div>
        <?php endif; ?>
        <?php if ($updated): ?>
            <div class="alert alert-success">
                <p><strong>Succès :</strong> Vos personnalisations ont été enregistrées.</p>
            </div>
        <?php endif; ?>
        <?php if ($added): ?>
            <div class="popup-success" id="popup-added">
                <span class="popup-close" onclick="document.getElementById('popup-added').style.display='none';">&times;</span>
                <p>Voyage ajouté à votre panier !<br>
                <a href="panier.php" class="popup-link">Voir le panier</a></p>
            </div>
            <script>
                setTimeout(() => {
                    let popup = document.getElementById('popup-added');
                    if (popup) popup.style.display = 'none';
                }, 3500);
            </script>
        <?php endif; ?>
        <section class="voyage-details">
            <h1><?php echo isset($voyage['titre']) ? htmlspecialchars($voyage['titre']) : 'Voyage sans titre'; ?></h1>
            <div class="voyage-header">
                <img src="<?php echo htmlspecialchars($voyage['etapes'][0]['image'] ?? 'https://via.placeholder.com/720x405'); ?>" alt="<?php echo htmlspecialchars($voyage['titre']); ?>">
                <div class="voyage-summary">
                    <p><span>Lieu :</span> <?php echo isset($voyage['lieu']) ? htmlspecialchars($voyage['lieu']) : 'Lieu non spécifié'; ?></p>
                    <p>
                <span>Dates :</span> 
                        <?php 
                        if (isset($voyage['dates'])) {
                            if (is_array($voyage['dates'])) {
                                echo htmlspecialchars(implode(' - ', $voyage['dates']));
                            } else {
                                echo htmlspecialchars($voyage['dates']);
                            }
                        } else {
                            echo 'Dates non spécifiées';
                        }
                        ?>
                    </p>
                    <p><span>Prix total :</span> <?php echo isset($voyage['prix_total']) ? htmlspecialchars($voyage['prix_total']) : 'Prix non spécifié'; ?> €</p>
                </div>
            </div>
            <h2>Étapes du voyage</h2>
            <div class="etapes-list">
                <?php if (isset($voyage['etapes']) && is_array($voyage['etapes'])): ?>
                    <?php foreach ($voyage['etapes'] as $index => $etape): ?>
                        <div class="etape-item">
                            <h3><?php echo isset($etape['titre']) ? htmlspecialchars($etape['titre']) : 'Étape sans titre'; ?></h3>
                            <p><span>Jour :</span> <?php echo isset($etape['jour']) ? htmlspecialchars($etape['jour']) : 'Jour non spécifié'; ?></p>
                            <p><span>Description :</span> <?php echo isset($etape['description']) ? htmlspecialchars($etape['description']) : 'Description non spécifiée'; ?></p>
                            <?php if (isset($etape['options']) && is_array($etape['options'])): ?>
                                <?php foreach ($etape['options'] as $key => $option): ?>
                                    <?php
                                    $choix = $_SESSION['personnalisations'][$voyage_id][$index][$key] ?? $option['choix'] ?? 'Non spécifié';
                                    ?>
                                    <p><span><?php echo ucfirst($key); ?> :</span> <?php echo htmlspecialchars($choix); ?></p>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Aucune étape disponible pour ce voyage.</p>
                <?php endif; ?>
            </div>
            <h2>Options personnalisables</h2>
            <div class="options-list">
                <?php if (isset($voyage['options']) && is_array($voyage['options'])): ?>
                    <?php foreach ($voyage['options'] as $option): ?>
                        <p><?php echo htmlspecialchars($option); ?></p>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Aucune option disponible.</p>
                <?php endif; ?>
            </div>
            <div class="voyage-actions">
                <a href="recherche.php" class="btn btn-retour">Retour</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($is_banned): ?>
                        <span class="btn btn-primary" style="background-color: #e74c3c; cursor: not-allowed;">Compte banni - Action impossible</span>
                    <?php elseif ($is_reserved): ?>
                        <span class="btn btn-primary" style="background-color: #95a5a6; cursor: not-allowed;">Voyage réservé</span>
                        <a href="voir_recap.php?id=<?php echo $voyage_id; ?>" class="btn btn-primary">Voir le récapitulatif</a>
                    <?php else: ?>
                        <a href="personnaliser.php?id=<?php echo $voyage_id; ?>" class="btn btn-primary">Personnaliser</a>
                        <a href="../scripts/reserver_voyage.php?id=<?php echo $voyage_id; ?>" class="btn btn-primary">Réserver</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="connexion.php" class="btn btn-primary">Connectez-vous pour personnaliser</a>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <footer>
        <p>© 2025 Festival Goers - Tous droits réservés</p>
    </footer>
    <script>
        // Afficher le contenu après chargement
        window.addEventListener('load', () => {
            document.body.style.visibility = 'visible';
        });
    </script>
<script>
window.shoppingBagConfig = {
    count: <?php echo (int)$cart_count; ?>,
    link: "panier.php",
    icon: "" test/shopping_bag_24dp_1F1F1F_FILL0_wght400_GRAD0_opsz24.svg"
};
</script>
<script src="../scripts/shopping-bag.js?v=<?= time(); ?>"></script>
</body>
</html>