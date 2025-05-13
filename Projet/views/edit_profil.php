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

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id']) || !$user) {
    header('Location: connexion.php');
    exit;
}

// Charger les images prédéfinies
$avatars = json_decode(file_get_contents('../data/avatars.json'), true);

// Vérifier s'il y a une erreur
$error_message = '';
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'username_taken') {
        $error_message = "Ce nom d'utilisateur est déjà pris. Choisissez-en un autre.";
    } elseif ($_GET['error'] === 'username_empty') {
        $error_message = "Le nom d'utilisateur ne peut pas être vide.";
    } elseif ($_GET['error'] === 'user_not_found') {
        $error_message = "Utilisateur non trouvé.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Festival Goers - Modifier le Profil</title>
    <link rel="stylesheet" id="theme-css" href="../css/styles.css?v=<?= time(); ?>">
    <script defer src="../scripts/main.js"></script>
    <script defer src="../scripts/edit_profil.js"></script>
</head>
<body>
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
                <?php else: ?>
                    <li><a href="profil.php">Profil</a></li>
                <?php endif; ?>
                <?php if (isset($user['role']) && $user['role'] === 'admin'): ?>
                    <li><a href="admin.php">Admin</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main class="edit-profil">
        <section class="edit-profile">
            <h2>Modifier mon profil</h2>
            <?php if ($error_message): ?>
                <p style="color: #e74c3c; text-align: center;"><?php echo $error_message; ?></p>
            <?php endif; ?>
            <form id="edit-profile-form" action="../scripts/traiter_edit_profil.php" method="POST" class="edit-profile-form">
                <div class="edit-profile-group">
                    <label for="username">Nom d'utilisateur :</label>
                    <span class="editable" data-field="username"><?php echo htmlspecialchars($user['username']); ?></span>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" style="display: none;" required>
                </div>
                <div class="edit-profile-group">
                    <label for="bio">Bio :</label>
                    <span class="editable" data-field="bio"><?php echo htmlspecialchars($user['infos']['bio'] ?? ''); ?></span>
                    <textarea id="bio" name="bio" rows="3" style="display: none;"><?php echo htmlspecialchars($user['infos']['bio'] ?? ''); ?></textarea>
                </div>
                <div class="edit-profile-group">
                    <label for="profile_picture">Photo de profil :</label>
                    <select id="profile_picture" name="profile_picture" onchange="updateProfilePicture(this)">
                        <option value="" <?php echo empty($user['profile_picture']) ? 'selected' : ''; ?>>Aucune photo</option>
                        <?php foreach ($avatars as $avatar): ?>
                            <option value="<?php echo htmlspecialchars($avatar['path']); ?>" 
                                    <?php echo (isset($user['profile_picture']) && $user['profile_picture'] === $avatar['path']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($avatar['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="profile-pic-container">
                        <?php if (!empty($user['profile_picture'])): ?>
                            Photo actuelle : <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Photo de profil" class="edit-profile-current-pic">
                        <?php endif; ?>
                    </p>
                </div>
                <div class="edit-profile-actions">
                    <button type="button" class="btn edit-profile-cancel-btn" onclick="resetForm()">Annuler</button>
                    <button type="submit" class="btn edit-profile-save-btn">Enregistrer</button>
                </div>
            </form>
        </section>
    </main>
    <footer>
        <p>© 2025 Festival Goers - Tous droits réservés</p>
    </footer>
</body>
</html>