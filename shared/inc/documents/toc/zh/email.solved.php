<?php
ob_start();
?>

<p class="item_dear">&#x5C0A;&#x656C;&#x7684;<?= $this->itsContact->getFirstname() ?> <?= $this->itsContact->getLastname() ?>,</p>
<p>&#x6839;&#x636E;&#x8D35;&#x5904;&#x63D0;&#x51FA;&#x7684;<?= $this->itsHeader['type'] ?>&#x5728;<?= $this->itsHeader['airport_code']?>&#x673A;&#x573A;&#x8BBE;&#x5907;&#x7684;&#x8BF7;&#x6C42;&#xFF0C;&#x6211;&#x4EEC;&#x5F88;&#x9AD8;&#x5174;&#x5730;&#x901A;&#x77E5;&#x60A8;&#xFF0C;&#x8D35;&#x5904;&#x7684;TOC#<?= $this->itsID?>&#x8BF7;&#x6C42;&#x73B0;&#x5DF2;&#x59A5;&#x5584;&#x89E3;&#x51B3;&#x3002;&#x60A8;&#x53EF;&#x4EE5;&#x5728;<a href="https://www.tld-gse.com/extranet/index.php?m[0]=toc&m[1]=view&id=<?= $this->itsID ?>">&#x5916;&#x8054;&#x7F51;</a>&#x4E0A;&#x4E86;&#x89E3;&#x66F4;&#x591A;&#x8BE6;&#x7EC6;&#x4FE1;&#x606F;&#x3002;</p>
<p>&#x5E0C;&#x671B;&#x6211;&#x4EEC;&#x63D0;&#x4F9B;&#x7684;&#x670D;&#x52A1;&#x80FD;&#x591F;&#x4EE4;&#x60A8;&#x6EE1;&#x610F;&#xFF0C;&#x82E5;&#x60A8;&#x80FD;&#x7A0D;&#x5FAE;&#x82B1;&#x8D39;&#x4E00;&#x70B9;&#x65F6;&#x95F4;&#x8BC4;&#x4EF7;&#x4E00;&#x4E0B;&#x60A8;&#x6B64;&#x6B21;&#x7684;&#x4F53;&#x9A8C; <a href="<?= $tokenLink ?>">(1&#x5206;&#x949F;)</a> &#xFF0C;&#x6211;&#x4EEC;&#x5C06;&#x4E0D;&#x80DC;&#x611F;&#x6FC0;&#x3002;</p>
<p>&#x5982;&#x6709;&#x4EFB;&#x4F55;&#x7591;&#x95EE;&#xFF0C;&#x8BF7;&#x5C3D;&#x5FEB;&#x4E0E;&#x6211;&#x4EEC;&#x8054;&#x7CFB;&#x3002;&#x82E5;&#x5728;&#x63A5;&#x4E0B;&#x6765;&#x7684;&#x31;&#x34;&#x5929;&#x5185;&#x6211;&#x4EEC;&#x6CA1;&#x6709;&#x6536;&#x5230;&#x60A8;&#x7684;&#x4EFB;&#x4F55;&#x6D88;&#x606F;&#x6216;&#x6765;&#x4FE1;&#xFF0C;&#x6211;&#x4EEC;&#x5C06;&#x8BA4;&#x4E3A;&#x6B64;&#x8BF7;&#x6C42;&#x5DF2;&#x7ECF;&#x7ED3;&#x6848;&#x3002;</p>
<p>&#x611F;&#x8C22;&#x60A8;&#x4FE1;&#x8D56;&#x5E76;&#x9009;&#x7528;<?= $sso ?>&#x5730;&#x9762;&#x88C5;&#x5907;</p>
<p>&#x6B64;&#x81F4;&#xFF0C;</p>
<p><?= $sso ?>&#x20;&#x670D;&#x52A1;&#x56E2;&#x961F;</p>

<?php
return ob_get_clean();
?>
