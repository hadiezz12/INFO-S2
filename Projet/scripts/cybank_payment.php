<?php
require('getapikey.php');
require_once '../scripts/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/Applications/MAMP/logs/php_error.log');

$transaction = trim($_POST['transaction'] ?? '');
$montant = trim($_POST['montant'] ?? '');
$retour = trim($_POST['retour'] ?? '');
$token = trim($_POST['token'] ?? '');

error_log("Transaction reçue : '$transaction'");
error_log("Montant reçu : '$montant'");
error_log("Retour reçu : '$retour'");
error_log("Token reçu : '$token'");

$length = strlen($transaction);
error_log("Longueur calculée : $length");
if (empty($transaction)) { error_log("Transaction vide"); die("Invalid transaction ID - Empty"); }
if ($length < 10) { error_log("Transaction trop courte : $length"); die("Invalid transaction ID - Too short"); }
if ($length > 24) { error_log("Transaction trop longue : $length"); die("Invalid transaction ID - Too long"); }
if (!preg_match('/^[0-9a-zA-Z]+$/', $transaction)) { error_log("Caractères invalides dans transaction : '$transaction'"); die("Invalid transaction ID - Invalid characters"); }
if (!preg_match('/^[0-9]+\\.[0-9]{2}$/', $montant)) { error_log("Montant invalide : '$montant'"); die("Invalid montant."); }
if (!filter_var($retour, FILTER_VALIDATE_URL)) { error_log("Retour invalide : '$retour'"); die("Invalid retour URL."); }
if (empty($token) || !preg_match('/^[0-9a-f]{32}$/', $token)) { error_log("Token invalide : '$token'"); die("Invalid token."); }

$vendeur = 'MIM_H';
$montant = number_format((float)$montant, 2, '.', '');
error_log("Montant ajusté : '$montant'");

$api_key = getAPIKey($vendeur);
error_log("API Key générée : '$api_key'");
if (!preg_match('/^[0-9a-zA-Z]{15}$/', $api_key)) { error_log("API Key invalide : '$api_key'"); die("Invalid API key."); }

$control = md5($api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#" . $retour . "#");
error_log("Control généré : '$control'");
error_log("Données envoyées à CY Bank : transaction='$transaction', montant='$montant', vendeur='$vendeur', retour='$retour', token='$token', control='$control'");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CY Bank Payment</title>
</head>
<body>
    <h1>Redirection vers CY Bank</h1>
    <form action="https://www.plateforme-smc.fr/cybank/index.php" method="POST">
        <input type="hidden" name="transaction" value="<?php echo htmlspecialchars($transaction); ?>">
        <input type="hidden" name="montant" value="<?php echo htmlspecialchars($montant); ?>">
        <input type="hidden" name="vendeur" value="<?php echo htmlspecialchars($vendeur); ?>">
        <input type="hidden" name="retour" value="<?php echo htmlspecialchars($retour); ?>">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <input type="hidden" name="control" value="<?php echo htmlspecialchars($control); ?>">
        <button type="submit">Valider et payer</button>
    </form>
    <script>
        document.querySelector('form').submit();
    </script>
</body>
</html>