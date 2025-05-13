<?php
session_start();

// Log pour vérifier que voir_recap.php est accédé
error_log("Accès à voir_recap.php avec URL : " . $_SERVER['REQUEST_URI']);

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

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

// Charger les voyages
$voyages = json_decode(file_get_contents('../data/voyages.json'), true);
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

// Vérifier si le voyage est réservé par l'utilisateur et récupérer le nombre de tickets
$utilisateurs = json_decode(file_get_contents('../data/utilisateurs.json'), true);
$is_reserved = false;
$is_banned = false;
$nb_tickets = 1; // Par défaut, 1 ticket si non spécifié
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
                    $nb_tickets = is_array($voyage_data) ? ($voyage_data['tickets'] ?? 1) : 1;
                    break;
                }
            }
        }
        break;
    }
}
if (!$is_reserved || $is_banned) {
    header("Location: voyage_details.php?id=$voyage_id");
    exit();
}

// Calculer le prix total avec les personnalisations (par ticket)
$base_price = $voyage['prix_total'] ?? 0;
$additional_price = 0;
if (isset($_SESSION['personnalisations'][$voyage_id]) && is_array($_SESSION['personnalisations'][$voyage_id])) {
    foreach ($_SESSION['personnalisations'][$voyage_id] as $etape_index => $etape_options) {
        if (isset($voyage['etapes'][$etape_index]['options']) && is_array($voyage['etapes'][$etape_index]['options'])) {
            foreach ($voyage['etapes'][$etape_index]['options'] as $option_key => $option_details) {
                if (isset($etape_options[$option_key]) && isset($option_details['prix'])) {
                    $additional_price += $option_details['prix'];
                }
            }
        }
    }
}
// Prix total par ticket (base + personnalisations), puis multiplié par le nombre de tickets
$price_per_ticket = $base_price + $additional_price;
$total_price = $price_per_ticket * $nb_tickets;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Récapitulatif du voyage - Festival Goers</title>
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
    <style>
        .btn-retour-inline:hover {
            background: #0056b3 !important;
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }
    </style>
</head>
<body>
    <header>
        <button id="theme-switch">
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eAeD"><path d="M480-120q-150 0-255-105T120-480q0-150 105-255t255-105q14 0 27.5 1t26.5 3q-41 29-65.5 75.5T444-660q0 90 63 153t153 63q55 0 101-24.5t75-65.5q2 13 3 26.5t1 27.5q0 150-105 255T480-120Z"/></svg>
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eAeD"><path d="M480-280q-83 0-141.5-58.5T280-480q0-83 58.5-141.5T480-680q83 0 141.5 58.5T680-480q0 83-58.5 141.5T480-280ZM200-440H40v-80h160v80Zm720 0H760v-80h160v80ZM440-760v-160h80v160h-80Zm0 720v-160h80v160h-80ZM256-650l-101-97 57-59 96 100-52 56Zm492 496-97-101 53-55 101 97-57 59Zm-98-550 97-101 59 57-100 96-56-52ZM154-212l101-97 55 53-97 101-59-57Z"/></svg>
        </button>
        <nav class="navbar">
            <div class="logo"><a href="../index.php">Festival Goers</a></div>
            <ul class="nav-links">
                <li><a href="../index.php">Accueil</a></li>
                <li><a href="presentation.php">Présentation</a></li>
                <li><a href="recherche.php">Recherche</a></li>
                <li><a href="profil.php">Profil</a></li>
                <?php if (isset($user['role']) && $user['role'] === 'admin'): ?>
                    <li><a href="admin.php">Admin</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main>
        <div class="ticket-container">
            <div class="ticket-header">
                <h1>Festival Goers - Votre Ticket</h1>
                <p>Réservation pour <?php echo htmlspecialchars($_SESSION['user_id']); ?></p>
                <p>ID du voyage : <?php echo htmlspecialchars($voyage_id); ?></p>
            </div>
            <div class="ticket-section">
                <h2>Détails du voyage</h2>
                <p><span>Titre :</span> <?php echo isset($voyage['titre']) ? htmlspecialchars($voyage['titre']) : 'Voyage sans titre'; ?></p>
                <p><span>Lieu :</span> <?php echo isset($voyage['lieu']) ? htmlspecialchars($voyage['lieu']) : 'Lieu non spécifié'; ?></p>
                <p><span>Dates :</span> 
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
                <p><span>Nombre de tickets :</span> <?php echo htmlspecialchars($nb_tickets); ?></p>
            </div>
            <div class="ticket-section">
                <h2>Étapes du voyage</h2>
                <?php if (isset($voyage['etapes']) && is_array($voyage['etapes'])): ?>
                    <?php foreach ($voyage['etapes'] as $index => $etape): ?>
                        <div class="etape-item2">
                            <p><span>Étape :</span> <?php echo isset($etape['titre']) ? htmlspecialchars($etape['titre']) : 'Étape sans titre'; ?></p>
                            <p><span>Jour :</span> <?php echo isset($etape['jour']) ? htmlspecialchars($etape['jour']) : 'Jour non spécifié'; ?></p>
                            <p><span>Dates :</span> 
                                <?php 
                                if (isset($etape['dates'])) {
                                    echo htmlspecialchars($etape['dates']['arrivee'] ?? 'Non spécifié') . ' - ' . htmlspecialchars($etape['dates']['depart'] ?? 'Non spécifié');
                                } else {
                                    echo 'Dates non spécifiées';
                                }
                                ?>
                            </p>
                            <p><span>Lieu :</span> <?php echo isset($etape['lieu']) ? htmlspecialchars($etape['lieu']) : 'Lieu non spécifié'; ?></p>
                            <p><span>Description :</span> <?php echo isset($etape['description']) ? htmlspecialchars($etape['description']) : 'Description non spécifiée'; ?></p>
                            <?php if (isset($etape['options']) && is_array($etape['options'])): ?>
                                <?php foreach ($etape['options'] as $key => $option): ?>
                                    <?php
                                    $choix = $_SESSION['personnalisations'][$voyage_id][$index][$key] ?? $option['choix'] ?? 'Non spécifié';
                                    $prix_option = isset($option['prix']) ? " (+" . htmlspecialchars($option['prix']) . " €)" : '';
                                    ?>
                                    <p><span><?php echo ucfirst($key); ?> :</span> <?php echo htmlspecialchars($choix) . $prix_option; ?></p>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Aucune étape disponible pour ce voyage.</p>
                <?php endif; ?>
            </div>
            <div class="ticket-section">
                <h2>Options personnalisables</h2>
                <?php if (isset($voyage['options']) && is_array($voyage['options'])): ?>
                    <?php foreach ($voyage['options'] as $option): ?>
                        <p><?php echo htmlspecialchars($option); ?></p>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Aucune option disponible.</p>
                <?php endif; ?>
            </div>
            <div class="ticket-section">
                <h2>Prix total</h2>
                <p><span>Prix par ticket :</span> <?php echo htmlspecialchars($price_per_ticket); ?> €</p>
                <p><span>Total (<?php echo $nb_tickets; ?> ticket<?php echo $nb_tickets > 1 ? 's' : ''; ?>) :</span> <?php echo htmlspecialchars($total_price); ?> €</p>
            </div>
            <div class="ticket-retour" style="text-align: center;">
                <button onclick="window.location='profil.php'" type="button" class="btn-retour-inline" style="padding: 12px 24px; background: #007bff; color: white; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; z-index: 1000; position: relative; transition: all 0.3s ease;">Retour au profil</button>
            </div>
        </div>
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
</body>
</html>