<?php
ob_start();
?>

<p class="item_dear">&#x5C0A;&#x656C;&#x7684;<?= $this->itsContact->getFirstname() ?> <?= $this->itsContact->getLastname() ?>,</p>
<p>&#x5173;&#x4E8E;&#x60A8;&#x767B;&#x8BB0;&#x7684; TOC#<?= $this->itsHeader['id'] ?> &#x7684;&#x670D;&#x52A1;&#x8BF7;&#x6C42;&#xFF0C;&#x6211;&#x4EEC;&#x901A;&#x77E5;&#x60A8;&#xFF0C;&#x4EE5;&#x4E0B;&#x96F6;&#x914D;&#x4EF6;&#x7684;&#x5305;&#x88F9;&#x5DF2;&#x53D1;&#x8FD0;&#xFF1A;</p>

<ul>
    <li>&#x88C5;&#x7BB1;&#x5355;: #<?= $partsTracking['packing_slip'] ?></li>
    <li>&#x5FEB;&#x9012;&#x8FD0;&#x5355;&#x53F7;: <?= $partsTracking['tracking'] ?></li>
    <?php foreach ($partsTracking['parts'] as $parts): ?>
        <ul>
            <li>&#x6599;&#x53F7;: #<?= $parts['part_number'] ?></li>
            <li>&#x4EA7;&#x54C1;&#x63CF;&#x8FF0;: <?= $parts['description'] ?></li>
            <li>&#x53D1;&#x8FD0;&#x6570;&#x91CF;: <?= $parts['quantity'] ?></li>
        </ul>
        <br>
    <?php endforeach; ?>
</ul>
<p>&#x5982;&#x679C;&#x60A8;&#x6709;&#x4EFB;&#x4F55;&#x5176;&#x4ED6;&#x95EE;&#x9898;&#x6216;&#x7591;&#x95EE;&#xFF0C;&#x8BF7;&#x968F;&#x65F6;&#x8054;&#x7CFB;&#x60A8;&#x7684;TLD&#x547C;&#x53EB;&#x670D;&#x52A1;&#x987E;&#x95EE;&#x3002;</p>
<p>&#x6B64;&#x81F4;&#xFF0C;</p>
<p>TLD &#x670D;&#x52A1;&#x56E2;&#x961F;</p>

<?php
return ob_get_clean();
?>
