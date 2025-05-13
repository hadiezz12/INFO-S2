<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/connexion.php');
    exit;
}

$voyage_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($voyage_id <= 0) {
    $_SESSION['error'] = 'Erreur : ID de voyage invalide.';
    $redirect_to = isset($_GET['from']) && $_GET['from'] === 'panier' ? '../views/panier.php' : '../views/profil.php';
    header('Location: ' . $redirect_to);
    exit;
}

// Charger les utilisateurs
$utilisateurs_file = '../data/utilisateurs.json';
if (!file_exists($utilisateurs_file)) {
    $_SESSION['error'] = 'Erreur : Fichier utilisateurs.json manquant.';
    $redirect_to = isset($_GET['from']) && $_GET['from'] === 'panier' ? '../views/panier.php' : '../views/profil.php';
    header('Location: ' . $redirect_to);
    exit;
}

$utilisateurs = json_decode(file_get_contents($utilisateurs_file), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    $_SESSION['error'] = 'Erreur lors du décodage JSON : ' . json_last_error_msg();
    $redirect_to = isset($_GET['from']) && $_GET['from'] === 'panier' ? '../views/panier.php' : '../views/profil.php';
    header('Location: ' . $redirect_to);
    exit;
}

// Trouver et modifier l'utilisateur
$updated = false;
foreach ($utilisateurs as &$utilisateur) {
    if ($utilisateur['username'] === $_SESSION['user_id']) {
        if (isset($utilisateur['voyages']) && is_array($utilisateur['voyages'])) {
            $utilisateur['voyages'] = array_filter($utilisateur['voyages'], function($voyage_data) use ($voyage_id) {
                return !is_array($voyage_data) || !isset($voyage_data['id']) || $voyage_data['id'] != $voyage_id;
            });
            $utilisateur['voyages'] = array_values($utilisateur['voyages']);
            $updated = count($utilisateur['voyages']) < count($utilisateur['voyages']) + 1;
        }
        break;
    }
}

// Sauvegarder si une suppression a eu lieu
if ($updated) {
    file_put_contents($utilisateurs_file, json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $_SESSION['success'] = 'Voyage supprimé avec succès.';
} else {
    $_SESSION['error'] = 'Erreur : Voyage non trouvé dans le panier.';
}

// Rediriger en fonction du paramètre 'from'
$redirect_to = isset($_GET['from']) && $_GET['from'] === 'panier' ? '../views/panier.php' : '../views/profil.php';
header('Location: ' . $redirect_to);
exit;
?>