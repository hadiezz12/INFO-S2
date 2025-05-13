<?php
require_once '../scripts/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = "Méthode non autorisée.";
    header("Location: ../views/connexion.php");
    exit();
}

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($email) || empty($password)) {
    $_SESSION['error_message'] = "Veuillez remplir tous les champs.";
    header("Location: ../views/connexion.php");
    exit();
}

$utilisateurs_json = file_get_contents('../data/utilisateurs.json');
$utilisateurs = $utilisateurs_json ? json_decode($utilisateurs_json, true) : [];
$user_found = false;

foreach ($utilisateurs as $utilisateur) {
    if ($utilisateur['email'] === $email && password_verify($password, $utilisateur['password'])) {
        $_SESSION['user_id'] = $utilisateur['username'];
        $user_found = true;
        error_log("Connexion réussie pour {$utilisateur['username']}");

        // Restaurer le contexte du paiement si un token est présent
        if (isset($_SESSION['redirect_token']) && !empty($_SESSION['redirect_token'])) {
            $token = $_SESSION['redirect_token'];
            $payment = isset($utilisateur['pending_payments'][$token]) ? $utilisateur['pending_payments'][$token] : null;

            if ($payment && $payment['user_id'] === $_SESSION['user_id']) {
                $_SESSION['last_transaction'] = $payment['transaction'];
                $_SESSION['last_montant'] = $payment['montant'];
                error_log("Données de paiement restaurées pour token : $token, user_id : {$payment['user_id']}");
            } else {
                error_log("Données de paiement introuvables ou non valides pour token : $token");
                $_SESSION['last_transaction'] = 'TRANS' . time();
                $_SESSION['last_montant'] = '0.00';
            }

            unset($_SESSION['redirect_token']);
            header("Location: ../scripts/retour_paiement.php?token=$token");
            exit();
        }

        header("Location: ../views/profil.php");
        exit();
    }
}

if (!$user_found) {
    $_SESSION['error_message'] = "Email ou mot de passe incorrect.";
    header("Location: ../views/connexion.php");
    exit();
}
?>