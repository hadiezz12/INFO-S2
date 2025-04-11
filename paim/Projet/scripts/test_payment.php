<?php
require('getapikey.php');
$transaction = 'TRANS_1743864363_William';
$montant = '330'; // Entier
$retour = 'http://localhost/Projet/scripts/retour_paiement.php';
$vendeur = 'MIM_H';
$api_key = getAPIKey($vendeur);
$control = md5($api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#" . $retour . "#");
?>
<form action="https://www.plateforme-smc.fr/cybank/index.php" method="POST">
    <input type="hidden" name="transaction" value="<?php echo $transaction; ?>">
    <input type="hidden" name="montant" value="<?php echo $montant; ?>">
    <input type="hidden" name="vendeur" value="<?php echo $vendeur; ?>">
    <input type="hidden" name="retour" value="<?php echo $retour; ?>">
    <input type="hidden" name="control" value="<?php echo $control; ?>">
    <button type="submit">Payer</button>
</form>