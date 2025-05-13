<?php
session_start();

// Définir le chemin de la racine du projet
define('ROOT_DIR', dirname(__DIR__));

// Charger les utilisateurs pour déterminer $user
$user = null;
if (isset($_SESSION['user_id'])) {
    $utilisateurs_json = @file_get_contents(ROOT_DIR . '/data/utilisateurs.json');
    if ($utilisateurs_json === false) {
        $error = "Erreur de chargement des utilisateurs.";
    } else {
        $utilisateurs = json_decode($utilisateurs_json, true);
        if (is_array($utilisateurs)) {
            foreach ($utilisateurs as $utilisateur) {
                if ($utilisateur['username'] === $_SESSION['user_id']) {
                    $user = $utilisateur;
                    break;
                }
            }
        }
    }
}

// Vérifier si l'utilisateur est déjà connecté
if (isset($_SESSION['user_id'])) {
    header("Location: profil.php");
    exit();
}

// Traiter le formulaire POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm-password'] ?? '';

    // Validation des champs
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = "Tous les champs doivent être remplis.";
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $error = "Le nom d'utilisateur doit contenir entre 3 et 50 caractères.";
    } elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
        $error = "Seuls les lettres, chiffres, tirets et soulignés sont autorisés pour le nom d'utilisateur.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse email n'est pas valide.";
    } elseif (strlen($email) > 255) {
        $error = "L'email ne doit pas dépasser 255 caractères.";
    } elseif (strlen($password) < 8 || strlen($password) > 20) {
        $error = "Le mot de passe doit contenir entre 8 et 20 caractères.";
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $error = "Le mot de passe doit contenir une majuscule, une minuscule et un chiffre.";
    } elseif ($password !== $confirmPassword) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        // Charger les utilisateurs
        if (!file_exists(ROOT_DIR . '/data/utilisateurs.json')) {
            $utilisateurs = [];
        } else {
            $utilisateurs_json = @file_get_contents(ROOT_DIR . '/data/utilisateurs.json');
            if ($utilisateurs_json === false) {
                $error = "Erreur de chargement des utilisateurs.";
            } else {
                $utilisateurs = json_decode($utilisateurs_json, true);
                if (!is_array($utilisateurs)) {
                    $utilisateurs = [];
                }
            }
        }

        // Vérifier si le nom d'utilisateur ou l'email existe déjà
        if (!isset($error)) {
            foreach ($utilisateurs as $user_check) {
                if ($user_check['username'] === $username) {
                    $error = "Le nom d'utilisateur est déjà pris.";
                    break;
                }
                if ($user_check['email'] === $email) {
                    $error = "Cet email est déjà utilisé.";
                    break;
                }
            }
        }

        // Ajouter le nouvel utilisateur
        if (!isset($error)) {
            $newUser = [
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'normal',
                'dates' => [
                    'inscription' => date('Y-m-d H:i:s'),
                    'last_login' => null
                ]
            ];

            $utilisateurs[] = $newUser;
            if (@file_put_contents(ROOT_DIR . '/data/utilisateurs.json', json_encode($utilisateurs, JSON_PRETTY_PRINT)) === false) {
                $error = "Erreur lors de l'enregistrement de l'utilisateur.";
            } else {
                $_SESSION['success_message'] = "Inscription réussie. Vous pouvez maintenant vous connecter.";
                header('Location: inscription.php');
                exit;
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
    <title>Festival Goers - Inscription</title>
    <link id="theme-css" rel="stylesheet" href="">
    <script src="../scripts/main.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/inscription.css">
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
                    <li><a href="inscription.php" class="active">Inscription</a></li>
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
        <section class="signup">
            <h2>Inscription</h2>
            <?php if (isset($_SESSION['success_message'])): ?>
                <p style="color: #10B981; text-align: center;"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></p>
            <?php elseif (isset($error)): ?>
                <p style="color: #EF4444; text-align: center;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <form action="inscription.php" method="post" id="signup-form">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" maxlength="50" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>
                    <span class="error-message"></span>
                    <span class="char-counter">0/50</span>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" maxlength="255" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                    <span class="error-message"></span>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <div class="password-container input-wrapper">
                        <input type="password" id="password" name="password" maxlength="20" required>
                        <i class="fas fa-eye toggle-password"></i>
                    </div>
                    <span class="error-message"></span>
                    <span class="char-counter">0/20</span>
                </div>
                <div class="form-group">
                    <label for="confirm-password">Confirmez le mot de passe</label>
                    <div class="password-container input-wrapper">
                        <input type="password" id="confirm-password" name="confirm-password" maxlength="20" required>
                        <i class="fas fa-eye toggle-password"></i>
                    </div>
                    <span class="error-message"></span>
                </div>
                <button type="submit" class="btn">S'inscrire</button>
            </form>
        </section>
    </main>
    <footer>
        <p>© 2025 Festival Goers - Tous droits réservés</p>
    </footer>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('signup-form');
            const usernameInput = document.getElementById('username');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm-password');
            const usernameCounter = usernameInput.nextElementSibling.nextElementSibling;
            const passwordCounter = passwordInput.parentElement.nextElementSibling.nextElementSibling;

            // Mettre à jour le compteur de caractères
            function updateCharCounter(input, counter) {
                counter.textContent = `${input.value.length}/${input.maxLength}`;
            }

            // Valider un champ et afficher l'erreur
            async function validateField(input, errorElement, validationFn) {
                const error = await validationFn(input.value);
                errorElement.textContent = error || '';
                errorElement.style.display = error ? 'block' : 'none';
                return !error;
            }

            // Vérifier l’unicité via AJAX
            async function checkUniqueness(field, value) {
                if (!value) return '';
                try {
                    const response = await fetch('../scripts/check_user.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `field=${field}&value=${encodeURIComponent(value)}`
                    });
                    const data = await response.json();
                    return data.exists ? `Ce ${field} est déjà pris.` : '';
                } catch (error) {
                    console.error('Erreur AJAX:', error);
                    return 'Erreur lors de la vérification.';
                }
            }

            // Fonctions de validation
            const validateUsername = async value => {
                if (!value) return 'Le nom d’utilisateur est requis.';
                if (value.length < 3) return 'Le nom d’utilisateur doit contenir au moins 3 caractères.';
                if (value.length > 50) return 'Le nom d’utilisateur ne doit pas dépasser 50 caractères.';
                if (!/^[a-zA-Z0-9_-]+$/.test(value)) return 'Seuls les lettres, chiffres, tirets et soulignés sont autorisés.';
                return await checkUniqueness('username', value);
            };

            const validateEmail = async value => {
                if (!value) return 'L’email est requis.';
                if (value.length > 255) return 'L’email ne doit pas dépasser 255 caractères.';
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return 'L’email n’est pas valide.';
                return await checkUniqueness('email', value);
            };

            const validatePassword = value => {
                if (!value) return 'Le mot de passe est requis.';
                if (value.length < 8) return 'Le mot de passe doit contenir au moins 8 caractères.';
                if (value.length > 20) return 'Le mot de passe ne doit pas dépasser 20 caractères.';
                if (!/[A-Z]/.test(value)) return 'Le mot de passe doit contenir une majuscule.';
                if (!/[a-z]/.test(value)) return 'Le mot de passe doit contenir une minuscule.';
                if (!/[0-9]/.test(value)) return 'Le mot de passe doit contenir un chiffre.';
                return '';
            };

            const validateConfirmPassword = value => {
                if (!value) return 'La confirmation est requise.';
                if (value !== passwordInput.value) return 'Les mots de passe ne correspondent pas.';
                return '';
            };

            // Mettre à jour les compteurs en temps réel
            usernameInput.addEventListener('input', () => {
                updateCharCounter(usernameInput, usernameCounter);
            });

            passwordInput.addEventListener('input', () => {
                updateCharCounter(passwordInput, passwordCounter);
            });

            // Valider le formulaire à la soumission
            form.addEventListener('submit', async e => {
                e.preventDefault();
                const isUsernameValid = await validateField(usernameInput, usernameInput.nextElementSibling, validateUsername);
                const isEmailValid = await validateField(emailInput, emailInput.nextElementSibling, validateEmail);
                const isPasswordValid = await validateField(passwordInput, passwordInput.parentElement.nextElementSibling, validatePassword);
                const isConfirmPasswordValid = await validateField(confirmPasswordInput, confirmPasswordInput.parentElement.nextElementSibling, validateConfirmPassword);

                if (isUsernameValid && isEmailValid && isPasswordValid && isConfirmPasswordValid) {
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

            // Initialiser les compteurs
            updateCharCounter(usernameInput, usernameCounter);
            updateCharCounter(passwordInput, passwordCounter);

            // Afficher le contenu après chargement
            window.addEventListener('load', () => {
                document.body.style.visibility = 'visible';
            });
        });
    </script>
</body>
</html>