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

// Détecter si le bouton "Rechercher" a été cliqué (requête GET avec paramètre q même vide)
$has_searched = isset($_GET['recherche']);

// Si recherche (champ rempli OU bouton cliqué), afficher tous les résultats ou filtrer selon le mot-clé
if ($voyages && $has_searched) {
    if ($search_term) {
        foreach ($voyages as $voyage) {
            $titre = strtolower($voyage['titre']);
            $lieu = strtolower($voyage['etapes'][0]['lieu']);
            if (strpos($titre, $search_term) !== false || strpos($lieu, $search_term) !== false) {
                $results[] = $voyage;
            }
        }
    } else {
        // Pas de mot-clé mais recherche soumise : afficher tous les festivals
        $results = $voyages;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Festival Goers - Recherche</title>
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
    <script src="../scripts/script.js"></script>
</head>
<body>
    <header>
        <button id="theme-switch">
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eAeD"><path d="M480-120q-150 0-255-105T120-480q0-150 105-255t255-105q14 0 27.5 1t26.5 3q-41 29-65.5 75.5T444-660q0 90 63 153t153 63q55 0 101-24.5t75-65.5q2 13 3 26.5t1 27.5q0 150-105 255T480-120Z"/></svg>
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eAeD"><path d="M480-280q-83 0-141.5-58.5T280-480q0-83 58.5-141.5T480-680q83 0 141.5 58.5T680-480q0 83-58.5 141.5T480-280ZM200-440H40v-80h160v80Zm720 0H760v-80h160v80ZM440-760v-160h80v160h-80Zm0 720v-160h80v160h-80ZM256-650l-101-97 57-59 96 100-52 56Zm492 496-97-101 53-55 101 97-57 59Zm-98-550 97-101 59 57-100 96-56-52ZM154-212l101-97 55 53-97 101-59-57Z"/></svg>
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
                <button type="submit" name="recherche" class="btn">Rechercher</button>
            </form>
            <!-- Barre de tri toujours visible -->
            <div class="sort-bar" style="margin-bottom:1.5rem;display:flex;align-items:center;gap:1rem;">
                <label for="sort-select" class="text">Trier par :</label>
                <select id="sort-select">
                    <option value="date">Date</option>
                    <option value="prix">Prix</option>
                    <option value="duree">Durée</option>
                    <option value="pays">Pays</option>
                </select>
                <button id="sort-order-btn" type="button" title="Changer l'ordre">▲</button>
            </div>
            <div class="search-results">
                <?php if ($has_searched): ?>
                    <?php if (!empty($results)): ?>
                        <h2>Résultats (<?php echo count($results); ?> trouvés)</h2>
                        <div class="results-list" id="results-list">
                            <?php foreach ($results as $voyage): 
                                // Extraction du pays depuis le lieu de la première étape
                                $lieu = $voyage['etapes'][0]['lieu'] ?? '';
                                $pays = '';
                                if (preg_match('/([A-Za-zÀ-ÿ\s]+),\s*([A-Za-zÀ-ÿ\s]+)$/u', $lieu, $matches)) {
                                    $pays = trim($matches[2]);
                                } elseif (preg_match('/([A-Za-zÀ-ÿ\s]+)$/u', $lieu, $matches)) {
                                    $pays = trim($matches[1]);
                                }
                            ?>
                                <div class="result-item"
                                    data-date="<?php echo htmlspecialchars($voyage['dates']['debut']); ?>"
                                    data-prix="<?php echo htmlspecialchars($voyage['prix_total']); ?>"
                                    data-duree="<?php echo isset($voyage['dates']['duree']) ? htmlspecialchars($voyage['dates']['duree']) : ''; ?>"
                                    data-pays="<?php echo htmlspecialchars($pays); ?>"
                                >
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
                        <p>Aucun résultat trouvé. Essayez autre chose !</p>
                    <?php endif; ?>
                <?php else: ?>
                    <p>Utilisez la barre de recherche pour trouver un festival.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <footer>
        <p>© 2025 Festival Goers - Tous droits réservés</p>
    </footer>
    <?php if ($has_searched && !empty($results)): ?>
    <!-- Ajout du script de tri uniquement si résultats affichés -->
    <script src="../scripts/search-sort.js?v=<?= time(); ?>"></script>
    <?php endif; ?>
<?php
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $utilisateurs_json = file_get_contents('../data/utilisateurs.json');
    $utilisateurs = $utilisateurs_json ? json_decode($utilisateurs_json, true) : [];
    foreach ($utilisateurs as $u) {
        if ($u['username'] === $_SESSION['user_id'] && isset($u['voyages']) && is_array($u['voyages'])) {
            foreach ($u['voyages'] as $v) {
                if (is_array($v) && ($v['statut_paiement'] ?? null) === 'en attente') {
                    $cart_count++;
                }
            }
        }
    }
}
?>
    <script>
        // Afficher le contenu après chargement
        window.addEventListener('load', () => {
            document.body.style.visibility = 'visible';
        });
    </script>
</body>
</html>