<?php
	ob_start();
?>

<p class="item_dear">Dear <?= $this->itsContact->getFirstname() ?> <?= $this->itsContact->getLastname() ?>,</p>
<p>Regarding your Service request registered as TOC <?= $this->itsHeader['id'] ?>, we inform you that the following parts parcels have been shipped:</p>

<ul>
    <li>Packing Slip: #<?= $partsTracking['packing_slip'] ?></li>
    <li>Tracking: #<?= $partsTracking['tracking'] ?></li>
    <?php foreach ($partsTracking['parts'] as $parts): ?>
        <ul>
            <li>Part Number: #<?= $parts['part_number'] ?></li>
            <li>Part Description: <?= $parts['description'] ?></li>
            <li>Qty Shipped: <?= $parts['quantity'] ?></li>
        </ul>
        <br>
    <?php endforeach; ?>
</ul>

<p>Please contact your TLD On Call advisor should you have any questions or inquiries.</p>
<p>Sincerely,</p>
<p>TLD Service Team</p>


<?php
return ob_get_clean();
?>
