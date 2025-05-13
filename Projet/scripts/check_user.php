<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

$field = $_POST['field'] ?? '';
$value = $_POST['value'] ?? '';

if (!in_array($field, ['username', 'email']) || empty($value)) {
    echo json_encode(['error' => 'Données invalides']);
    exit;
}

$utilisateurs_json = file_get_contents('../data/utilisateurs.json');
$utilisateurs = $utilisateurs_json ? json_decode($utilisateurs_json, true) : [];
$exists = false;

foreach ($utilisateurs as $user) {
    if ($user[$field] === $value) {
        $exists = true;
        break;
    }
}

echo json_encode(['exists' => $exists]);
exit;
?>