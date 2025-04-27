<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/connexion.php');
    exit;
}

$voyage_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($voyage_id <= 0) {
    header('Location: ../views/recherche.php');
    exit;
}

// Charger les utilisateurs
$utilisateurs = json_decode(file_get_contents('../data/utilisateurs.json'), true);

// Ajouter le voyage à l'utilisateur
foreach ($utilisateurs as &$utilisateur) {
    if ($utilisateur['username'] === $_SESSION['user_id']) {
        $utilisateur['voyages'] = $utilisateur['voyages'] ?? [];
        // Vérifier si le voyage n'est pas déjà réservé
        $already_reserved = false;
        foreach ($utilisateur['voyages'] as $voyage_data) {
            $reserved_id = is_array($voyage_data) ? ($voyage_data['id'] ?? null) : $voyage_data;
            if ($reserved_id === $voyage_id) {
                $already_reserved = true;
                break;
            }
        }
        if (!$already_reserved) {
            $utilisateur['voyages'][] = $voyage_id; // Ajout simple avec ID
        }
        break;
    }
}

file_put_contents('../data/utilisateurs.json', json_encode($utilisateurs, JSON_PRETTY_PRINT));
header('Location: ../views/profil.php');
exit;
?>