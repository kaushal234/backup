<?php
ob_start();
?>

<p class="item_dear">Dear <?= $this->itsContact->getFirstname() ?> <?= $this->itsContact->getLastname() ?>,</p>
<p>Following-up on your request to "<?=$sso?> on Call" on <?= $this->getOpenDate() ?> your case has been investigated by our Service Team  and the following actions have been carried out:</p>
<p><b><?= $txt1 ?></b></p>
<p>We do hope that our support is to your satisfaction. Please visit our <a href="https://www.tld-gse.com/extranet">support website</a> to find product information, manuals, tutorials, troubleshooting steps, and much more.</p>
<p>In the meantime our <?=$sso?> on Call advisor is always available should you have any other questions or inquiries.</p>
<p>Sincerely,</p>
<p><?=$sso?> Service Team</p>

<?php
return ob_get_clean();
?>
