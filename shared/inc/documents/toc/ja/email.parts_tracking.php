<?php
	ob_start();
?>

<p class="item_dear"><?= $this->itsContact->getFirstname() ?> <?= $this->itsContact->getLastname() ?>&#x69D8;</p>
<p>TOC<?= $this->itsHeader['id'] ?>&#x3067;&#x767B;&#x9332;&#x3055;&#x308C;&#x307E;&#x3057;&#x305F;&#x8CB4;&#x793E;&#x3054;&#x8981;&#x671B;&#x306B;&#x3064;&#x304D;&#x307E;&#x3057;&#x3066;&#x306F;&#x3001;&#x4EE5;&#x4E0B;&#x306E;&#x90E8;&#x54C1;&#x304C;&#x51FA;&#x8377;&#x3055;&#x308C;&#x307E;&#x3057;&#x305F;&#x306E;&#x3067;&#x3054;&#x9023;&#x7D61;&#x3057;&#x307E;&#x3059;&#x3002;</p>

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

<p>&#x3054;&#x8CEA;&#x554F;&#x7B49;&#x3054;&#x3056;&#x3044;&#x307E;&#x3057;&#x305F;&#x3089;&#x5F0A;&#x793E;&#x30B5;&#x30FC;&#x30D3;&#x30B9;&#x30C1;&#x30FC;&#x30E0;&#x306B;&#x3054;&#x9023;&#x7D61;&#x3044;&#x305F;&#x3060;&#x304D;&#x307E;&#x3059;&#x3088;&#x3046;&#x304A;&#x9858;&#x3044;&#x3057;&#x307E;&#x3059;&#x3002;</p>
<p>&#x3088;&#x308D;&#x3057;&#x304F;&#x304A;&#x9858;&#x3044;&#x81F4;&#x3057;&#x307E;&#x3059;&#x3002;</p>
<p>TLD &#x30B5;&#x30FC;&#x30D3;&#x30B9;&#x30C1;&#x30FC;&#x30E0;</p>

<?php
return ob_get_clean();
?>
