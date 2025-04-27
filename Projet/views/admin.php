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
    <link rel="stylesheet" href="../css/styles.css?v=<?= time(); ?>">
</head>
<body class="admin-page">
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
                    <!-- Suppression de la ligne statique vide -->
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
                                    <form method="POST" action="../scripts/traiter_admin.php" style="display:inline;">
                                        <input type="hidden" name="username" value="<?php echo htmlspecialchars($utilisateur['username']); ?>">
                                        <button type="submit" name="action" value="toggle_vip" class="action-btn vip-btn">
                                            <?php echo (isset($utilisateur['statut']) && $utilisateur['statut'] === 'vip') ? 'Retirer VIP' : 'VIP'; ?>
                                        </button>
                                        <button type="submit" name="action" value="toggle_ban" class="action-btn ban-btn">
                                            <?php echo (isset($utilisateur['statut']) && $utilisateur['statut'] === 'banned') ? 'Débannir' : 'Bannir'; ?>
                                        </button>
                                    </form>
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
    <script src="../scripts/script.js"></script>
    <script>
        function toggleVip(button) {
            let row = button.closest('tr');
            let statusSpan = row.querySelector('.status');
            if (statusSpan.classList.contains('status-normal')) {
                statusSpan.classList.remove('status-normal');
                statusSpan.classList.add('status-vip');
                statusSpan.textContent = 'VIP';
                button.textContent = 'Retirer VIP';
            } else if (statusSpan.classList.contains('status-vip')) {
                statusSpan.classList.remove('status-vip');
                statusSpan.classList.add('status-normal');
                statusSpan.textContent = 'Normal';
                button.textContent = 'VIP';
            }
        }

        function banUser(button) {
            let row = button.closest('tr');
            let statusSpan = row.querySelector('.status');
            if (statusSpan.classList.contains('status-normal') || statusSpan.classList.contains('status-vip')) {
                statusSpan.classList.remove('status-normal', 'status-vip');
                statusSpan.classList.add('status-banned');
                statusSpan.textContent = 'Banned';
                button.textContent = 'Débannir';
            } else if (statusSpan.classList.contains('status-banned')) {
                statusSpan.classList.remove('status-banned');
                statusSpan.classList.add('status-normal');
                statusSpan.textContent = 'Normal';
                button.textContent = 'Bannir';
            }
        }

        document.querySelectorAll('.action-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                let action = this.value === 'toggle_vip' ? 'modifier le statut VIP' : 'bannir/débannir';
                if (!confirm(`Voulez-vous vraiment ${action} cet utilisateur ?`)) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
