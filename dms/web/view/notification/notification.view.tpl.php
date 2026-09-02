<?php declare(strict_types=1);
ob_start();

$dmsNotRules = $dms->getNotificationRules();
$dmsUserList = $dms->getNotificationUserList();
?>

<p><?php echo sprintf(_('Found %s users to notify from below rules'), count($dmsUserList)); ?></p>

<h3><?php echo _('Actual Notification rules'); ?></h3>

<?php if (count($dmsNotRules)) { ?>
<table cellpadding="2" class="tld_table">
  <thead>
    <tr bgcolor="#2971A8">
      <th align="center">#</th>
      <th align="center"><?php echo _('Division'); ?></th>
      <th align="center"><?php echo _('Subdivision'); ?></th>
      <th align="center"><?php echo _('Region'); ?></th>
      <th align="center"><?php echo _('Business unit'); ?></th>
      <th align="center"><?php echo _('Department'); ?></th>
      <th align="center"><?php echo _('TLD functions'); ?></th>
      <th align="center"><?php echo _('Delete?'); ?></th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($dmsNotRules as $k => $rule) {
      ++$k;
      $bgColor = ($k % 2) ? '#dedede' : '#efefef'; ?>
    <tr bgcolor="<?php echo $bgColor; ?>">
      <td><?php echo $k; ?></td>
      <td><?php echo $rule['division']; ?></td>
      <td><?php echo $rule['subdivision']; ?></td>
      <td><?php echo $rule['region']; ?></td>
      <td><?php echo $rule['location']; ?></td>
      <td><?php echo $rule['department']; ?></td>
      <td><?php echo $rule['function_dsc']; ?></td>
      <td align="center">
        <a href="<?php echo "$php_self?m[0]=view&m[1]=notification&m[2]=rules&m[3]=delete&notid={$rule['id']}&id=".$dms->getID(); ?>"
        onClick="javascript:return confirm('<?php echo _('Are you sure to delete this rule?'); ?>');">
          <img src="/shared/icons/application/delete.png" alt="Delete?" />
        </a>
      </td>
    </tr>
  <?php
  } ?>
  </tbody>
</table>
<?php } else { ?>
<p><?php echo _('No data...'); ?></p>
<?php } ?>

<?php
      $_BUFF = ob_get_contents();
ob_end_clean();

return $_BUFF;
?>
