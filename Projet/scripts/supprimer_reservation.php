<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/connexion.php');
    exit;
}

$voyage_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($voyage_id <= 0) {
    header('Location: ../views/profil.php');
    exit;
}

// Charger les utilisateurs
$utilisateurs = json_decode(file_get_contents('../data/utilisateurs.json'), true);

// Trouver et modifier l'utilisateur
$updated = false;
foreach ($utilisateurs as &$utilisateur) {
    if ($utilisateur['username'] === $_SESSION['user_id']) {
        if (isset($utilisateur['voyages']) && is_array($utilisateur['voyages'])) {
            $new_voyages = [];
            foreach ($utilisateur['voyages'] as $voyage_data) {
                $reserved_id = is_array($voyage_data) ? ($voyage_data['id'] ?? null) : $voyage_data;
                if ($reserved_id != $voyage_id) {
                    $new_voyages[] = $voyage_data; // Garder les autres voyages
                } else {
                    $updated = true;
                }
            }
            $utilisateur['voyages'] = $new_voyages;
        }
        break;
    }
}

// Sauvegarder si une suppression a eu lieu
if ($updated) {
    file_put_contents('../data/utilisateurs.json', json_encode($utilisateurs, JSON_PRETTY_PRINT));
}

// Rediriger vers le profil
header('Location: ../views/profil.php');
exit;
?>