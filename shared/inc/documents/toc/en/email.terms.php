<?php
ob_start();
?>

<p class="terms"><em><span style="text-decoration:underline;">Warranty disclaimer:</span><br>
Unless otherwise provided, the only warranty, which <?=$sso['name']?> makes in connection with its equipment, 
parts and products, is the published <?=$sso['name']?> general warranty conditions. Any work, repair, shipment 
of parts required by the customer, outside of the general warranty conditions application or 
resulting from a breach of warranty by the customer, is subject to invoicing from <?=$sso['name']?> to the 
customer.</em></p>

<?php
return ob_get_clean();
?>
