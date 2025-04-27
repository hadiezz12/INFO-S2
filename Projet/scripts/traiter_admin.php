<?php
session_start();

// Vérifier si l'utilisateur est un admin
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

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../views/admin.php");
    exit();
}

$username = $_POST['username'] ?? '';
$action = $_POST['action'] ?? '';

// Charger les utilisateurs
$utilisateurs = json_decode(file_get_contents('../data/utilisateurs.json'), true);
if ($utilisateurs === null) {
    die('Erreur lors du chargement de utilisateurs.json');
}

// Trouver et modifier l'utilisateur
$updated = false;
foreach ($utilisateurs as &$utilisateur) {
    if ($utilisateur['username'] === $username && $utilisateur['role'] !== 'admin') {
        if ($action === 'toggle_vip') {
            if (isset($utilisateur['statut']) && $utilisateur['statut'] === 'vip') {
                unset($utilisateur['statut']); // Retirer VIP
            } else {
                $utilisateur['statut'] = 'vip'; // Passer en VIP
            }
        } elseif ($action === 'toggle_ban') {
            if (isset($utilisateur['statut']) && $utilisateur['statut'] === 'banned') {
                unset($utilisateur['statut']); // Débannir
            } else {
                $utilisateur['statut'] = 'banned'; // Bannir
            }
        }
        $updated = true;
        break;
    }
}

if (!$updated) {
    header("Location: ../views/admin.php?error=user_not_found");
    exit();
}

// Sauvegarder les modifications
$result = file_put_contents('../data/utilisateurs.json', json_encode($utilisateurs, JSON_PRETTY_PRINT));
if ($result === false) {
    die('Erreur lors de la sauvegarde de utilisateurs.json');
}

// Rediriger vers la page admin
header("Location: ../views/admin.php");
exit();
?>