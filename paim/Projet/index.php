<?php
session_start();

// Charger les utilisateurs pour déterminer $user
$user = null;
if (isset($_SESSION['user_id'])) {
    $utilisateurs_json = file_get_contents('data/utilisateurs.json');
    $utilisateurs = $utilisateurs_json ? json_decode($utilisateurs_json, true) : [];
    if (is_array($utilisateurs)) {
        foreach ($utilisateurs as $utilisateur) {
            if ($utilisateur['username'] === $_SESSION['user_id']) {
                $user = $utilisateur;
                break;
            }
        }
    } else {
        error_log("Erreur : impossible de charger utilisateurs.json dans index.php");
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Festival Goers - Accueil</title>
    <link rel="stylesheet" href="css/styles.css?v=<?= time(); ?>">
    <script src="scripts/script.js"></script>
</head>
<body id="home-page">
    <header>
        <nav class="navbar">
            <div class="logo"><a href="index.php">Festival Goers</a></div>
            <ul class="nav-links">
                <li><a href="index.php">Accueil</a></li>
                <li><a href="views/presentation.php">Présentation</a></li>
                <li><a href="views/recherche.php">Recherche</a></li>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <li><a href="views/inscription.php">Inscription</a></li>
                    <li><a href="views/connexion.php">Connexion</a></li>
                <?php endif; ?>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="views/profil.php">Profil</a></li>
                <?php endif; ?>
                <?php if (isset($user['role']) && $user['role'] === 'admin'): ?>
                    <li><a href="views/admin.php">Admin</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main>
        <section class="hero">
            <div class="background"></div>
            <h1>Bienvenue sur Festival Goers</h1>
            <p><strong>Explorez les festivals du monde entier avec Festival Goers. Rejoignez notre communauté de passionnés de festivals et partagez vos expériences inoubliables.</strong></p>
            <a href="views/recherche.php" class="btn">Réserver maintenant</a>
        </section>
        <section class="why-choose-us">
            <div class="content-box">
                <h2>Pourquoi choisir notre agence ?</h2>
                <p>💫 <strong>Vivez l'expérience ultime des festivals !</strong></p>
                <p>Avec notre agence, chaque voyage devient une aventure inoubliable. Nous vous garantissons :</p>
                <ul>
                    <li>✨ <strong>Accès VIP</strong> aux meilleurs festivals du monde</li>
                    <li>✈️ <strong>Forfaits tout compris</strong> (vols, hébergement & entrées exclusives)</li>
                    <li>🎉 <strong>Moments uniques</strong> avec des activités immersives et after-parties privées</li>
                </ul>
            </div>
        </section>
        <section class="featured-festivals">
            <h2>🌍 Nos Festivals Incontournables</h2>
            <div class="festival-gallery">
                <div class="festival-item">
                    <img src="https://images.ctfassets.net/pjshm78m9jt4/1Zxw4QGTKqxU87hT3jTOzk/894cbd6f7dfee3ffd127bb6758d547de/01HFKS2KVQ8R1TWSP5X1313TSX.jpg?fm=avif&fit=fill&w=720&h=405&q=80" alt="Festival 1">
                    <h3>Festival de Glastonbury</h3>
                    <p>🇬🇧 Royaume-Uni</p>
                </div>
                <div class="festival-item">
                    <img src="images/Ubm.webp" alt="Festival 2">
                    <h3>Burning Man</h3>
                    <p>🇺🇸 États-Unis</p>
                </div>
                <div class="festival-item">
                    <img src="https://premiotravels.com/wp-content/uploads/2024/04/Tomorrowland-Festival-in-Belgium-Holiday-Travel-and-Tour-Package.jpeg" alt="Festival 3">
                    <h3>Tomorrowland</h3>
                    <p>🇧🇪 Belgique</p>
                </div>
            </div>
        </section>
    </main>
    <footer>
        <p>© 2025 Festival Goers - Tous droits réservés</p>
    </footer> 
</body>
</html>

