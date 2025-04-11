<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Charger les utilisateurs depuis le fichier JSON
$utilisateurs_json = file_get_contents('../data/utilisateurs.json');
$utilisateurs = $utilisateurs_json ? json_decode($utilisateurs_json, true) : [];
error_log("Utilisateurs chargés : " . print_r($utilisateurs, true));

if (isset($_POST['email']) && isset($_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    error_log("Tentative de connexion - Email: $email");

    foreach ($utilisateurs as $utilisateur) {
        error_log("Vérification pour : " . $utilisateur['email']);
        if ($utilisateur['email'] == $email) {
            if (password_verify($password, $utilisateur['password'])) {
                $_SESSION['user_id'] = $utilisateur['username'];
                $_SESSION['role'] = $utilisateur['role'];
                error_log("Connexion réussie pour : " . $utilisateur['username']);
                header('Location: ../views/profil.php');
                exit;
            } else {
                error_log("Mot de passe incorrect pour : $email");
                $_SESSION['error_message'] = 'Mot de passe incorrect.';
                header('Location: ../views/connexion.php');
                exit;
            }
        }
    }

    error_log("Email non trouvé : $email");
    $_SESSION['error_message'] = 'Aucun utilisateur trouvé avec cet email.';
    header('Location: ../views/connexion.php');
    exit;
} else {
    $_SESSION['error_message'] = 'Veuillez remplir tous les champs.';
    header('Location: ../views/connexion.php');
    exit;
}
?>

