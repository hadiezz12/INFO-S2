<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/connexion.php');
    exit;
}
$voyage_id = isset($_POST['voyage_id']) ? (int)$_POST['voyage_id'] : 0;
$tickets = max(1, min(6, (int)($_POST['tickets'] ?? 1)));
$utilisateurs = json_decode(file_get_contents('../data/utilisateurs.json'), true);
foreach ($utilisateurs as &$utilisateur) {
    if ($utilisateur['username'] === $_SESSION['user_id']) {
        foreach ($utilisateur['voyages'] as &$voyage_data) {
            if (is_array($voyage_data) && $voyage_data['id'] == $voyage_id && ($voyage_data['statut_paiement'] ?? null) === 'en attente') {
                $voyage_data['tickets'] = $tickets;
                break;
            }
        }
        break;
    }
}
file_put_contents('../data/utilisateurs.json', json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
header('Location: ../views/panier.php');
exit;
