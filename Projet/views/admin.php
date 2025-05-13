<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/connexion.php");
    exit();
}
$users = json_decode(file_get_contents('../data/utilisateurs.json'), true);
$is_admin = false;
foreach ($users as $user) {
    if ($user['username'] === $_SESSION['user_id'] && isset($user['role']) && $user['role'] === 'admin') {
        $is_admin = true;
        break;
    }
}
if (!$is_admin) {
    header("Location: ../index.php");
    exit();
}

// Charger les utilisateurs pour affichage dynamique
$utilisateurs = json_decode(file_get_contents('../data/utilisateurs.json'), true);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gestion des utilisateurs</title>
    <link id="theme-css" rel="stylesheet" href="../css/styles.css?v=<?= time(); ?>">
    <script src="../scripts/main.js" defer></script>
    <style>
        body { visibility: hidden; }
        .action-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</head>
<body class="admin-page">
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
        <section class="admin-panel">
            <h1>Gestion des utilisateurs</h1>
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="user-list">
                    <?php foreach ($utilisateurs as $utilisateur): ?>
                        <tr data-username="<?php echo htmlspecialchars($utilisateur['username']); ?>">
                            <td><?php echo htmlspecialchars($utilisateur['username']); ?></td>
                            <td><?php echo htmlspecialchars($utilisateur['email']); ?></td>
                            <td>
                                <span class="status <?php 
                                    echo ($utilisateur['role'] === 'admin') ? 'status-admin' : 
                                         ((isset($utilisateur['statut']) && $utilisateur['statut'] === 'vip') ? 'status-vip' : 
                                         ((isset($utilisateur['statut']) && $utilisateur['statut'] === 'banned') ? 'status-banned' : 'status-normal')); 
                                ?>">
                                    <?php echo ($utilisateur['role'] === 'admin') ? 'Admin' : 
                                               (isset($utilisateur['statut']) ? ucfirst($utilisateur['statut']) : 'Normal'); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($utilisateur['role'] !== 'admin'): ?>
                                    <button class="action-btn vip-btn" data-username="<?php echo htmlspecialchars($utilisateur['username']); ?>" data-action="toggle_vip">
                                        <?php echo (isset($utilisateur['statut']) && $utilisateur['statut'] === 'vip') ? 'Retirer VIP' : 'VIP'; ?>
                                    </button>
                                    <button class="action-btn ban-btn" data-username="<?php echo htmlspecialchars($utilisateur['username']); ?>" data-action="toggle_ban">
                                        <?php echo (isset($utilisateur['statut']) && $utilisateur['statut'] === 'banned') ? 'Débannir' : 'Bannir'; ?>
                                    </button>
                                <?php else: ?>
                                    <span>NON MODIFIABLE</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
    <footer>
        <p>© 2025 Festival Goers - Tous droits réservés</p>
    </footer>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.action-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const username = this.dataset.username;
                    const action = this.dataset.action;
                    const actionText = action === 'toggle_vip' ? 'modifier le statut VIP' : 'bannir/débannir';
                    if (!confirm(`Voulez-vous vraiment ${actionText} cet utilisateur ?`)) {
                        return;
                    }

                    // Désactiver et griser le bouton
                    this.disabled = true;
                    this.classList.add('disabled');

                    // Attendre 2 secondes avant la requête
                    setTimeout(() => {
                        fetch('../scripts/traiter_admin.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `username=${encodeURIComponent(username)}&action=${encodeURIComponent(action)}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                alert(`Erreur : ${data.error}`);
                                this.disabled = false;
                                this.classList.remove('disabled');
                                return;
                            }

                            // Mettre à jour l'interface
                            const row = document.querySelector(`tr[data-username="${username}"]`);
                            const statusSpan = row.querySelector('.status');
                            if (action === 'toggle_vip') {
                                const isVip = data.statut === 'vip';
                                this.textContent = isVip ? 'Retirer VIP' : 'VIP';
                                statusSpan.textContent = isVip ? 'Vip' : 'Normal';
                                statusSpan.className = `status ${isVip ? 'status-vip' : 'status-normal'}`;
                            } else if (action === 'toggle_ban') {
                                const isBanned = data.statut === 'banned';
                                this.textContent = isBanned ? 'Débannir' : 'Bannir';
                                statusSpan.textContent = isBanned ? 'Banned' : 'Normal';
                                statusSpan.className = `status ${isBanned ? 'status-banned' : 'status-normal'}`;
                            }

                            // Réactiver le bouton
                            this.disabled = false;
                            this.classList.remove('disabled');
                        })
                        .catch(error => {
                            console.error('Erreur lors de la requête :', error);
                            alert('Erreur réseau lors de la mise à jour.');
                            this.disabled = false;
                            this.classList.remove('disabled');
                        });
                    }, 2000); // Délai de 2 secondes
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