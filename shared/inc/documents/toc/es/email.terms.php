<?php
ob_start();
?>

<p class="terms"><em><span style="text-decoration:underline;">Avisos de Garantía:</span><br>
A menos de haber acordado algo distinto, la única garantía por la cual <?=$sso['name']?> se hace responsable, 
es por aquella publicada en nuestra sección de Términos Generales de Garantía. Cualquier trabajo, 
reparación, envío de partes y repuestos, solicitados por el cliente y que se encuentren fuera de 
los Términos Generales de Garantía, se encuentran sujetos a ser facturados por <?=$sso['name']?>.</em></p>

<?php
return ob_get_clean();
?>
