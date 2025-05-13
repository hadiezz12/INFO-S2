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

// Vérifier si l'utilisateur est déjà connecté
$already_logged_in = isset($_SESSION['user_id']);
if ($already_logged_in) {
    $_SESSION['info_message'] = "Vous êtes déjà connecté à un compte.";
    if (isset($_SESSION['redirect_token']) && !empty($_SESSION['redirect_token'])) {
        $redirect_token = $_SESSION['redirect_token'];
        unset($_SESSION['redirect_token']);
        header("Location: ../scripts/retour_paiement.php?token=$redirect_token");
        exit;
    }
    header("Location: profil.php");
    exit;
}

// Vérifier s'il y a un message d'erreur
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['error_message']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Festival Goers - Connexion</title>
    <link id="theme-css" rel="stylesheet" href="../css/styles.css?v=<?= time(); ?>">
    <script src="../scripts/main.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { visibility: hidden; }
        .error-message { color: red; font-size: 0.9em; display: none; }
        .char-counter { font-size: 0.9em; color: #666; }
        .form-group { position: relative; margin-bottom: 1.5em; }
        .password-container { position: relative; }
        .toggle-password { position: absolute; right: 10px; top: 70%; transform: translateY(-50%); cursor: pointer; }
    </style>
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
                    <li><a href="connexion.php" class="active">Connexion</a></li>
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
        <section class="login">
            <h2>Connexion</h2>
            <?php if ($error_message): ?>
                <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>
            <form action="../scripts/traiter_connexion.php" method="post" id="login-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" maxlength="255" required>
                    <span class="error-message"></span>
                </div>
                <div class="form-group password-container2">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" maxlength="50" required>
                    <i class="fas fa-eye toggle-password"></i>
                    <span class="error-message"></span>
                    <span class="char-counter">0/50</span>
                </div>
                <button type="submit" class="btn">Se connecter</button>
            </form>
            <p>Pas encore inscrit ? <a href="inscription.php">Créez un compte</a></p>
        </section>
    </main>
    <footer>
        <p>© 2025 Festival Goers - Tous droits réservés</p>
    </footer>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('login-form');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const passwordCounter = passwordInput.nextElementSibling.nextElementSibling.nextElementSibling;

            // Mettre à jour le compteur de caractères
            function updateCharCounter(input, counter) {
                counter.textContent = `${input.value.length}/${input.maxLength}`;
            }

            // Valider un champ et afficher l'erreur
            function validateField(input, errorElement, validationFn) {
                const error = validationFn(input.value);
                errorElement.textContent = error || '';
                errorElement.style.display = error ? 'block' : 'none';
                return !error;
            }

            // Fonctions de validation
            const validateEmail = value => {
                if (!value) return 'L’email est requis.';
                if (value.length > 255) return 'L’email ne doit pas dépasser 255 caractères.';
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return 'L’email n’est pas valide.';
                return '';
            };

            const validatePassword = value => {
                if (!value) return 'Le mot de passe est requis.';
                if (value.length > 50) return 'Le mot de passe ne doit pas dépasser 50 caractères.';
                return '';
            };

            // Mettre à jour le compteur en temps réel
            passwordInput.addEventListener('input', () => {
                updateCharCounter(passwordInput, passwordCounter);
            });

            // Valider le formulaire à la soumission
            form.addEventListener('submit', e => {
                e.preventDefault();
                const isEmailValid = validateField(emailInput, emailInput.nextElementSibling, validateEmail);
                const isPasswordValid = validateField(passwordInput, passwordInput.nextElementSibling.nextElementSibling, validatePassword);

                if (isEmailValid && isPasswordValid) {
                    form.submit();
                }
            });

            // Basculer l’affichage du mot de passe
            document.querySelectorAll('.toggle-password').forEach(toggle => {
                toggle.addEventListener('click', () => {
                    const input = toggle.previousElementSibling;
                    if (input.type === 'password') {
                        input.type = 'text';
                        toggle.classList.remove('fa-eye');
                        toggle.classList.add('fa-eye-slash');
                    } else {
                        input.type = 'password';
                        toggle.classList.remove('fa-eye-slash');
                        toggle.classList.add('fa-eye');
                    }
                });
            });

            // Initialiser le compteur
            updateCharCounter(passwordInput, passwordCounter);

            // Afficher le contenu après chargement
            window.addEventListener('load', () => {
                document.body.style.visibility = 'visible';
            });
        });
    </script>
</body>
</html>