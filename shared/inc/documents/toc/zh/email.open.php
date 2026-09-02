<?php
ob_start();
?>

<p class="item_dear">&#x5C0A;&#x656C;&#x7684;<?= $this->itsContact->getFirstname() ?> <?= $this->itsContact->getLastname() ?>,</p>
<p>&#x6839;&#x636E;&#x60A8;&#x5728;<?= $this->getOpenDate() ?>,&#x5411;<?= $sso ?>&#x652F;&#x6301;&#x670D;&#x52A1;&#x90E8;&#x95E8;&#x63D0;&#x51FA;&#x7684;&#x8BF7;&#x6C42;&#xFF0C;&#x6211;&#x4EEC;&#x901A;&#x77E5;&#x60A8;&#xFF0C;&#x6211;&#x4EEC;&#x7684;&#x670D;&#x52A1;&#x56E2;&#x961F;&#x6B63;&#x5728;&#x5904;&#x7406;&#x60A8;&#x7684;&#x8BF7;&#x6C42;&#x3002;&#x8BF7;&#x67E5;&#x9605;&#x60A8;&#x7684;&#x8BF7;&#x6C42;&#x7F16;&#x53F7;TOC#<?= $this->itsID ?>&#x4EE5;&#x4E86;&#x89E3;&#x6709;&#x5173;&#x6B64;&#x8BF7;&#x6C42;&#x7684;&#x4EFB;&#x4F55;&#x8FDB;&#x4E00;&#x6B65;&#x4FE1;&#x606F;&#x3002;</p>
<p>&#x6211;&#x4EEC;&#x4F1A;&#x5728;&#x95EE;&#x9898;&#x89E3;&#x51B3;&#x540E;&#x901A;&#x77E5;&#x60A8;&#x3002;&#x60A8;&#x8FD8;&#x53EF;&#x4EE5;&#x6536;&#x5230;&#x4E2D;&#x95F4;&#x8FC7;&#x7A0B;&#x62A5;&#x544A;&#xFF0C;&#x8BF4;&#x660E;&#x6240;&#x91C7;&#x53D6;&#x7684;&#x5404;&#x79CD;&#x884C;&#x52A8;&#x3002;</p>
<p>&#x5982;&#x679C;&#x60A8;&#x6709;&#x4EFB;&#x4F55;&#x5176;&#x4ED6;&#x95EE;&#x9898;&#x6216;&#x7591;&#x95EE;&#xFF0C;&#x8BF7;&#x968F;&#x65F6;&#x8054;&#x7CFB;&#x201C;<?= $sso ?>&#x547C;&#x53EB;&#x201D;&#x670D;&#x52A1;&#x3002;</p>
<p>&#x6B64;&#x81F4;&#xFF0C;</p>
<p><?= $sso ?> &#x670D;&#x52A1;&#x56E2;&#x961F;</p>

<?php
return ob_get_clean();
?>
