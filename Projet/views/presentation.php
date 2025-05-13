<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Festival Goers - Présentation</title>
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
        <section class="presentation">
            <h1>À propos de <span>Festival Goers</span></h1>
            <p>Festival Goers est une agence de voyages spécialisée dans les festivals à travers le monde. Nous proposons des séjours préconfigurés permettant aux passionnés de musique et de culture de vivre des expériences inoubliables.</p>
            <h2>Notre Concept</h2>
            <ul class="concept-list">
                <li>📍 Sélection de circuits de festivals exclusifs</li>
                <li>🏨 Choisissez votre hébergement, transport et restauration</li>
                <li>🎟️ Ajoutez des activités selon vos envies</li>
                <li>🗓️ Profitez d’un planning détaillé pour chaque voyage
                    <?php
                    $voyages = json_decode(file_get_contents('../data/voyages.json'), true);
                    if ($voyages === null) {
                        echo "<p>Erreur de lecture de voyages.json : " . json_last_error_msg() . "</p>";
                    } elseif (empty($voyages)) {
                        echo "<p>(Planning en cours de préparation)</p>";
                    } else {
                        echo "<div class='planning-example'>";
                        echo "<select id='festival-select' onchange='updatePlanning(this.value)'>";
                        foreach (array_slice($voyages, 0, 3) as $index => $voyage) {
                            echo "<option value='$index'>" . htmlspecialchars($voyage['titre']) . "</option>";
                        }
                        echo "<option value='more'>...</option>";
                        echo "</select>";
                        echo "<button id='toggle-planning' class='btn-planning' onclick='togglePlanning()'>Détails</button>";
                        echo "<div id='planning-content' class='planning-content' style='display: none;'>";
                        echo "<script>const voyages = " . json_encode(array_slice($voyages, 0, 3)) . ";</script>";
                        echo "</div>";
                        echo "<a href='voyage_details.php?id=" . $voyages[0]['id'] . "' class='planning-link' id='details-link'>En savoir plus</a>";
                        echo "</div>";
                    }
                    ?>
                </li>
            </ul>
            <h2>Nos Festivals en Vedette</h2>
            <div class="gallery">
                <img src="https://images.ctfassets.net/pjshm78m9jt4/1Zxw4QGTKqxU87hT3jTOzk/894cbd6f7dfee3ffd127bb6758d547de/01HFKS2KVQ8R1TWSP5X1313TSX.jpg?fm=avif&fit=fill&w=720&h=405&q=80" alt="Festival 1">
                <img src="../images/Ubm.webp" alt="Festival 2">
                <img src="https://premiotravels.com/wp-content/uploads/2024/04/Tomorrowland-Festival-in-Belgium-Holiday-Travel-and-Tour-Package.jpeg" alt="Festival 3">
            </div>
        </section>
    </main>
    <footer>
        <p>© 2025 Festival Goers - Tous droits réservés</p>
    </footer>
    <script>
        function updatePlanning(index) {
            if (index === 'more') {
                window.location.href = 'recherche.php';
                return;
            }
            const voyage = voyages[index];
            const content = document.getElementById('planning-content');
            let totalCost = voyage.prix_total;
            content.innerHTML = `
                <p class='planning-title'>${voyage.titre}</p>
                <ul>
                    ${voyage.etapes.map(etape => `
                        <li>
                            <span>🗓️ ${etape.dates.arrivee} - ${etape.dates.depart}</span>
                            <span>📍 ${etape.lieu}</span>
                            ${Object.entries(etape.options).map(([option, details]) => {
                                totalCost += details.prix;
                                return `<span>⚙️ ${details.choix}</span>`;
                            }).join('')}
                        </li>
                    `).join('')}
                </ul>
                <p class='planning-cost'>💰 ${totalCost} €</p>
            `;
            document.getElementById('details-link').href = `voyage_details.php?id=${voyage.id}`;
        }
        function togglePlanning() {
            const content = document.getElementById('planning-content');
            const button = document.getElementById('toggle-planning');
            if (content.style.display === 'none' || content.style.display === '') {
                content.style.display = 'block';
                button.textContent = 'Masquer';
            } else {
                content.style.display = 'none';
                button.textContent = 'Détails';
            }
        }
        updatePlanning(0); // Premier voyage par défaut, caché
        // Afficher le contenu après chargement
        window.addEventListener('load', () => {
            document.body.style.visibility = 'visible';
        });
    </script>
</body>
</html>