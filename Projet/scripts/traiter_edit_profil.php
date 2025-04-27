<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/connexion.php');
    exit;
}

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/edit_profil.php');
    exit;
}

// Charger les utilisateurs depuis le fichier JSON
$utilisateurs = json_decode(file_get_contents('../data/utilisateurs.json'), true);
if ($utilisateurs === null) {
    die('Erreur lors du chargement de utilisateurs.json');
}

// Vérifier si le nouveau username est déjà pris
$new_username = trim($_POST['username']);
foreach ($utilisateurs as $u) {
    if ($u['username'] === $new_username && $u['username'] !== $_SESSION['user_id']) {
        header('Location: ../views/edit_profil.php?error=username_taken');
        exit;
    }
}

// Trouver et mettre à jour l'utilisateur
$updated = false;
foreach ($utilisateurs as &$utilisateur) {
    if ($utilisateur['username'] == $_SESSION['user_id']) {
        $utilisateur['username'] = $new_username;
        $utilisateur['infos']['bio'] = trim($_POST['bio'] ?? $utilisateur['infos']['bio'] ?? '');
        // Mettre à jour la photo de profil si une nouvelle image est choisie
        $new_profile_picture = trim($_POST['profile_picture'] ?? '');
        if ($new_profile_picture !== '') {
            $utilisateur['profile_picture'] = $new_profile_picture;
        } elseif (isset($utilisateur['profile_picture']) && $new_profile_picture === '') {
            unset($utilisateur['profile_picture']); // Supprimer la photo si "Aucune photo" est choisie
        }
        $_SESSION['user_id'] = $new_username; // Synchronise la session
        $updated = true;
        break;
    }
}

if (!$updated) {
    header('Location: ../views/edit_profil.php?error=user_not_found');
    exit;
}

// Sauvegarder les modifications
$result = file_put_contents('../data/utilisateurs.json', json_encode($utilisateurs, JSON_PRETTY_PRINT));
if ($result === false) {
    die('Erreur lors de la sauvegarde de utilisateurs.json');
}

// Rediriger vers le profil avec un indicateur de mise à jour
header('Location: ../views/profil.php?updated=true');
exit;
?>