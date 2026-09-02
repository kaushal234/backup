<?php
ob_start();
?>

<p class="terms"><em><span style="text-decoration:underline;">Traitement des garanties:</span><br>
Le cas échéant, les conditions générales de garantie éditées par <?=$sso['name']?> s'appliquent aux équipements 
produits et vendus par <?=$sso['name']?>. Toute réparation, action sur l'équipement ou modification de ce 
dernier, hors des conditions de garantie entraînera la facturation par <?=$sso['name']?> des pièces et 
de la main d'œuvre au client.</em></p>

<?php
return ob_get_clean();
?>
