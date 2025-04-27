<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Festival Goers - Présentation</title>
    <link rel="stylesheet" href="../css/styles.css?v=<?= time(); ?>">
    <style>
        .more-festivals {
            text-align: center;
            margin-top: 10px;
            font-size: 24px;
        }
        .more-festivals a {
            text-decoration: none;
            color: #007bff;
        }
        .more-festivals a:hover {
            text-decoration: underline;
        }
    </style>
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
    </script>
</body>
</html>