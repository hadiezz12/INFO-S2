<?php
session_start();

// Charger les utilisateurs pour déterminer $user
$user = null;
if (isset($_SESSION['user_id'])) {
    $utilisateurs_json = file_get_contents('../data/utilisateurs.json'); // Chemin corrigé pour index.php
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

// Charger les voyages
$voyages = json_decode(file_get_contents('../data/voyages.json'), true);
$results = [];
$search_term = isset($_GET['q']) ? strtolower(trim($_GET['q'])) : '';

if ($search_term && $voyages) {
    foreach ($voyages as $voyage) {
        $titre = strtolower($voyage['titre']);
        $lieu = strtolower($voyage['etapes'][0]['lieu']);
        if (strpos($titre, $search_term) !== false || strpos($lieu, $search_term) !== false) {
            $results[] = $voyage;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Festival Goers - Recherche</title>
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
        <section class="search-section">
            <h1>Rechercher un festival</h1>
            <form action="recherche.php" method="GET" class="search-form">
                <input type="text" name="q" placeholder="Entrez un festival ou un lieu..." value="<?php echo htmlspecialchars($search_term); ?>">
                <button type="submit" class="btn">Rechercher</button>
            </form>
            <div class="search-results">
                <?php if ($search_term): ?>
                    <?php if (!empty($results)): ?>
                        <h2>Résultats (<?php echo count($results); ?> trouvés)</h2>
                        <div class="results-list">
                            <?php foreach ($results as $voyage): ?>
                                <div class="result-item">
                                    <img src="<?php echo htmlspecialchars($voyage['etapes'][0]['image'] ?? 'https://via.placeholder.com/200x120'); ?>" alt="<?php echo htmlspecialchars($voyage['titre']); ?>">
                                    <div class="result-details">
                                        <h3><?php echo htmlspecialchars($voyage['titre']); ?></h3>
                                        <p><span>📍</span> <?php echo htmlspecialchars($voyage['etapes'][0]['lieu']); ?></p>
                                        <p><span>🗓️</span> <?php echo $voyage['dates']['debut'] . " - " . $voyage['dates']['fin']; ?></p>
                                        <p><span>💰</span> <?php echo $voyage['prix_total']; ?> €</p>
                                        <a href="voyage_details.php?id=<?php echo $voyage['id']; ?>" class="btn">Voir les détails</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>Aucun résultat pour "<?php echo htmlspecialchars($search_term); ?>". Essayez autre chose !</p>
                    <?php endif; ?>
                <?php else: ?>
                    <p>Entrez un mot-clé pour commencer votre recherche.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <footer>
        <p>© 2025 Festival Goers - Tous droits réservés</p>
    </footer>
</body>
</html>