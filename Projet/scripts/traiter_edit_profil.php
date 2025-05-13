<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Non connecté']);
    exit;
}

// Vérifier si c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/edit_profil.php');
    exit;
}

// Charger les utilisateurs
$utilisateurs = json_decode(file_get_contents('../data/utilisateurs.json'), true);
if ($utilisateurs === null) {
    echo json_encode(['error' => 'Erreur lors du chargement de utilisateurs.json']);
    exit;
}

// Vérifier le nom d'utilisateur
$new_username = trim($_POST['username'] ?? '');
if (empty($new_username)) {
    header('Location: ../views/edit_profil.php?error=username_empty');
    exit;
}
foreach ($utilisateurs as $u) {
    if ($u['username'] === $new_username && $u['username'] !== $_SESSION['user_id']) {
        header('Location: ../views/edit_profil.php?error=username_taken');
        exit;
    }
}

// Mettre à jour l'utilisateur
$updated = false;
foreach ($utilisateurs as &$utilisateur) {
    if ($utilisateur['username'] === $_SESSION['user_id']) {
        $utilisateur['username'] = $new_username;
        $utilisateur['infos']['bio'] = trim($_POST['bio'] ?? $utilisateur['infos']['bio'] ?? '');
        $new_profile_picture = trim($_POST['profile_picture'] ?? '');
        if ($new_profile_picture !== '') {
            $utilisateur['profile_picture'] = $new_profile_picture;
        } else {
            unset($utilisateur['profile_picture']);
        }
        $_SESSION['user_id'] = $new_username;
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
    echo json_encode(['error' => 'Erreur lors de la sauvegarde de utilisateurs.json']);
    exit;
}

header('Location: ../views/profil.php?updated=true');
?>