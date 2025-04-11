<?php
session_start();
require('getapikey.php');

// Activer les logs
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/Applications/MAMP/logs/php_error.log');

// Récupérer l'ID de la transaction depuis les paramètres GET
$transaction = isset($_GET['transaction']) ? $_GET['transaction'] : ('TRANS' . time() . 'William');
$montant = isset($_GET['montant']) ? $_GET['montant'] : '330.00';
$vendeur = 'MIM_H';
$statut = 'accepted';

// Générer le hash de contrôle correct
$api_key = getAPIKey($vendeur);
$control = md5($api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#" . $statut . "#");

error_log("Simulation - Transaction : '$transaction'");
error_log("Simulation - Montant : '$montant'");
error_log("Simulation - Vendeur : '$vendeur'");
error_log("Simulation - Statut : '$statut'");
error_log("Simulation - Control : '$control'");

// Mettre à jour le statut de paiement dans le fichier JSON
if (isset($_SESSION['user_id'])) {
    $utilisateurs_json = file_get_contents('../data/utilisateurs.json');
    $utilisateurs = $utilisateurs_json ? json_decode($utilisateurs_json, true) : [];
    
    foreach ($utilisateurs as &$utilisateur) {
        if ($utilisateur['username'] === $_SESSION['user_id']) {
            foreach ($utilisateur['voyages'] as &$voyage) {
                if (!is_array($voyage)) {
                    $voyage = ['id' => $voyage];
                }
                if (!isset($voyage['date_paiement'])) {
                    $voyage['date_paiement'] = date('Y-m-d');
                }
            }
            unset($voyage);
            break;
        }
    }
    unset($utilisateur);
    
    // Sauvegarde du fichier JSON mis à jour
    file_put_contents('../data/utilisateurs.json', json_encode($utilisateurs, JSON_PRETTY_PRINT));
    error_log("Simulation - Fichier JSON mis à jour");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="3;url=../views/profil.php">
    <title>Simulation de paiement</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            text-align: center;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .success {
            color: #2ecc71;
            font-weight: bold;
            font-size: 24px;
            margin-bottom: 20px;
        }
        p {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        a:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Simulation de paiement</h1>
        <p class="success">✅ Paiement accepté avec succès!</p>
        <p><strong>Transaction ID:</strong> <?php echo htmlspecialchars($transaction); ?></p>
        <p><strong>Montant:</strong> <?php echo htmlspecialchars($montant); ?> €</p>
        <p>Vous allez être redirigé vers votre profil dans 3 secondes...</p>
        <p><a href="../views/profil.php">Retourner au profil</a></p>
    </div>
</body>
</html>
