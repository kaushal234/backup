<?php declare(strict_types=1);
ob_start();
?>

<h3><?php echo _('Security and Access definition'); ?></h3>

<table>
  <tr>
    <td style="vertical-align:middle;"><strong><?php echo _('Portal'); ?></strong></td>
    <td>
      <ul>
        <li><strong>INTRANET</strong>: <?php echo _('DMS page is accessible only to Alvest personnel via any internal Alvest portal'); ?></li>
        <li><strong>EXTRANET</strong>: <?php echo _('DMS page is accessible by Alvest personnel or Extranet users via EXTRANET portal'); ?></li>
        <li><strong>EVENDOR</strong>: <?php echo _('DMS page is accessible by Alvest personnel or eVendor users via EVENDOR portal'); ?></li>
      </ul>
    </td>
  </tr>
  <tr>
    <td style="vertical-align:middle;"><strong><?php echo _('Access type'); ?></strong></td>
    <td>
      <ul>
        <li><strong>PUBLIC</strong>: <?php echo _('No user restriction access in portal defined above'); ?></li>
        <li><strong>CONFIDENTIAL</strong>: <?php echo _('Restriction rules applies in portal defined above'); ?></li>
      </ul>
    </td>
  </tr>
</table>

<?php
$_BUFF = ob_get_contents();
ob_end_clean();

return $_BUFF;
?>
