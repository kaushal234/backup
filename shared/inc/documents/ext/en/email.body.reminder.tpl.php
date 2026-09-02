<?php
ob_start();
?>
<p class="item_dear">Dear <?= $data['firstname'] ?> <?= $data['lastname'] ?>,</p>
<p>Your application for a TLD Extranet Site account has been well received. However, we realized you already had an account in our system.</p>
<p><b>Your username is:</b>     <?= $data['userid'] ?></p>
<p><b>Your password is:</b>     <?= $data['password'] ?></p>

<p>To access the partners site use the following url <a href="https://www.tld-gse.com/extranet">https://www.tld-gse.com/extranet</a> or go through the 'login' page on our main website <a href="https://www.tld-group.com">https://www.tld-group.com</a></p>
<p>If you require any assistance or have any questions regarding our website, please do not hesitate to contact us.</p>

<p>Best regards,</p>
<p>
TLD MIS Department
</p>
<?php
return ob_get_clean();
?>
