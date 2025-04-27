<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/connexion.php');
    exit;
}

$voyage_id = isset($_POST['voyage_id']) ? (int)$_POST['voyage_id'] : 0;
if ($voyage_id <= 0) {
    header('Location: ../views/recherche.php');
    exit;
}

$voyages = json_decode(file_get_contents('../data/voyages.json'), true);
$voyage = null;
foreach ($voyages as $v) {
    if ($v['id'] === $voyage_id) {
        $voyage = $v;
        break;
    }
}
if (!$voyage) {
    header('Location: ../views/recherche.php');
    exit;
}

// Initialiser la session si nécessaire
if (!isset($_SESSION['personnalisations'])) {
    $_SESSION['personnalisations'] = [];
}

// Traiter les options soumises
$options_submitted = $_POST['options'] ?? [];
$personnalisations = $_SESSION['personnalisations'][$voyage_id] ?? [];

foreach ($voyage['etapes'] as $index => $etape) {
    if (isset($options_submitted[$index]) && is_array($options_submitted[$index])) {
        foreach ($etape['options'] as $key => $option) {
            if (isset($options_submitted[$index][$key])) {
                $choix = $options_submitted[$index][$key];
                if (in_array($choix, $option['valeurs'])) {
                    $personnalisations[$index][$key] = $choix;
                } else {
                    $personnalisations[$index][$key] = $option['choix'] ?? 'Non spécifié';
                }
            }
        }
    }
}

// Mettre à jour la session
$_SESSION['personnalisations'][$voyage_id] = $personnalisations;

header("Location: ../views/voyage_details.php?id=$voyage_id&updated=1");
exit;
?>