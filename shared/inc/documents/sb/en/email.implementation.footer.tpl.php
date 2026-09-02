<?php
ob_start();
?>


<p>Please acknowledge the receipt of this message by an email or a phone call.</p>
<p>For complete details regarding this TLD Technical Bulletin, please access our <a href="https://www.tld-gse.com/extranet/index.php">TLD Support website</a>. Our Sales, Service and Parts support teams are available for any additional information you may require.</p>

<p>Best regards,</p>
<p>TLD Support</p>
<p>
<?= $data['user']['fullname'] ?><br>
<?= $data['sso_name'] ?><br>
<?= $data['user']['email'] ?><br>
<?= $data['user']['direct_phone'] ?><br>
</p>
<?php
return ob_get_clean();
