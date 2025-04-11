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

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

// Charger les utilisateurs depuis le fichier JSON
$utilisateurs = json_decode(file_get_contents('../data/utilisateurs.json'), true);

// Trouver l'utilisateur en utilisant l'ID (username) de la session
$user = null;
foreach ($utilisateurs as $utilisateur) {
    if ($utilisateur['username'] == $_SESSION['user_id']) {
        $user = $utilisateur;
        break;
    }
}

if (!$user) {
    header('Location: profil.php');
    exit;
}

// Vérifier s'il y a une erreur (ex. username déjà pris)
$error_message = '';
if (isset($_GET['error']) && $_GET['error'] === 'username_taken') {
    $error_message = "Ce nom d'utilisateur est déjà pris. Choisissez-en un autre.";
}

// Charger les images prédéfinies
$avatars = json_decode(file_get_contents('../data/avatars.json'), true);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Festival Goers - Modifier le Profil</title>
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
    <main class="edit-profil">
        <section class="edit-profile">
            <h2>Modifier mon profil</h2>
            <?php if ($error_message): ?>
                <p style="color: #e74c3c; text-align: center;"><?php echo $error_message; ?></p>
            <?php endif; ?>
            <form action="../scripts/traiter_edit_profil.php" method="POST" class="edit-profile-form">
                <div class="edit-profile-group">
                    <label for="username">Nom d'utilisateur :</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>
                <div class="edit-profile-group">
                    <label for="bio">Bio :</label>
                    <textarea id="bio" name="bio" rows="3"><?php echo htmlspecialchars($user['infos']['bio'] ?? ''); ?></textarea>
                </div>
                <!-- Ajout du champ pour choisir une photo prédéfinie -->
                <div class="edit-profile-group">
                    <label for="profile_picture">Photo de profil :</label>
                    <select id="profile_picture" name="profile_picture">
                        <option value="">Aucune photo</option>
                        <?php foreach ($avatars as $avatar): ?>
                            <option value="<?php echo htmlspecialchars($avatar['path']); ?>" 
                                    <?php echo (isset($user['profile_picture']) && $user['profile_picture'] === $avatar['path']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($avatar['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($user['profile_picture'])): ?>
                        <p>Photo actuelle : <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Photo de profil" class="edit-profile-current-pic"></p>
                    <?php endif; ?>
                </div>
                <div class="edit-profile-actions">
                    <a href="profil.php" class="btn edit-profile-cancel-btn">Annuler</a>
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