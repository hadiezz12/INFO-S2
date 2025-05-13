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

// Ajouter le voyage au panier (statut en attente)
foreach ($utilisateurs as &$utilisateur) {
    if ($utilisateur['username'] === $_SESSION['user_id']) {
        $utilisateur['voyages'] = $utilisateur['voyages'] ?? [];
        $already_reserved = false;
        foreach ($utilisateur['voyages'] as $voyage_data) {
            $reserved_id = is_array($voyage_data) ? ($voyage_data['id'] ?? null) : $voyage_data;
            $statut_paiement = is_array($voyage_data) ? ($voyage_data['statut_paiement'] ?? null) : null;
            if ($reserved_id === $voyage_id && $statut_paiement !== 'payé') {
                $already_reserved = true;
                break;
            }
        }
        if (!$already_reserved) {
            $utilisateur['voyages'][] = [
                'id' => $voyage_id,
                'tickets' => 1,
                'statut_paiement' => 'en attente'
            ];
        }
        break;
    }
}

file_put_contents('../data/utilisateurs.json', json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Redirige vers la page précédente avec un paramètre pour la pop-up
$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../views/recherche.php';
$sep = (strpos($redirect, '?') === false) ? '?' : '&';
header("Location: {$redirect}{$sep}added=1");
exit;
?>