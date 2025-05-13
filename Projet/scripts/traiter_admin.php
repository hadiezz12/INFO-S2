<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Non connecté']);
    exit;
}

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
    echo json_encode(['error' => 'Accès non autorisé']);
    exit;
}

// Vérifier si les données POST sont présentes
$username = $_POST['username'] ?? '';
$action = $_POST['action'] ?? '';
if (empty($username) || empty($action)) {
    echo json_encode(['error' => 'Données manquantes']);
    exit;
}

// Charger les utilisateurs
$utilisateurs = json_decode(file_get_contents('../data/utilisateurs.json'), true);
if ($utilisateurs === null) {
    echo json_encode(['error' => 'Erreur lors du chargement de utilisateurs.json']);
    exit;
}

// Trouver et modifier l'utilisateur
$updated = false;
foreach ($utilisateurs as &$utilisateur) {
    if ($utilisateur['username'] === $username && $utilisateur['role'] !== 'admin') {
        if ($action === 'toggle_vip') {
            if (isset($utilisateur['statut']) && $utilisateur['statut'] === 'vip') {
                unset($utilisateur['statut']);
                $newStatut = 'normal';
            } else {
                $utilisateur['statut'] = 'vip';
                $newStatut = 'vip';
            }
        } elseif ($action === 'toggle_ban') {
            if (isset($utilisateur['statut']) && $utilisateur['statut'] === 'banned') {
                unset($utilisateur['statut']);
                $newStatut = 'normal';
            } else {
                $utilisateur['statut'] = 'banned';
                $newStatut = 'banned';
            }
        } else {
            echo json_encode(['error' => 'Action non valide']);
            exit;
        }
        $updated = true;
        break;
    }
}

if (!$updated) {
    echo json_encode(['error' => 'Utilisateur non trouvé ou non modifiable']);
    exit;
}

// Sauvegarder les modifications
$result = file_put_contents('../data/utilisateurs.json', json_encode($utilisateurs, JSON_PRETTY_PRINT));
if ($result === false) {
    echo json_encode(['error' => 'Erreur lors de la sauvegarde de utilisateurs.json']);
    exit;
}

echo json_encode(['success' => true, 'statut' => $newStatut]);
exit;
?>