<?php
ob_start();
?>
<p class="item_dear">Dear Customer,</p>
<p>The purpose of this message is to officially advise you that TLD has issued a SB#<?= $data['id'] ?>; following is a description of the bulletin:</p>
<p><b>Title</b>: <?= $data['title'] ?></p>
<p><b>Description</b>: <?= $data['description'] ?></p>
<?php
return ob_get_clean();
