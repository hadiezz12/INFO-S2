<?php
require 'getapikey.php';
$transaction = 'TRANS' . time() . 'William';
$montant = '330.00';
$vendeur = 'MIM_H';
$statut = 'accepted';
$api_key = getAPIKey($vendeur);
$control = md5($api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#" . $statut . "#");
?>
<!DOCTYPE html>
<html>
<head><title>Test Redirection</title></head>
<body>
    <h1>Test de redirection retour_paiement.php</h1>
    <p>Cliquez sur le lien pour simuler le retour de CY Bank:</p>
    <a href="retour_paiement.php?transaction=<?php echo $transaction; ?>&montant=<?php echo $montant; ?>&vendeur=<?php echo $vendeur; ?>&statut=<?php echo $statut; ?>&control=<?php echo $control; ?>">
        Tester la redirection
    </a>
</body>
</html>
