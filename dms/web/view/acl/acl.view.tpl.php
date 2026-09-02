<?php declare(strict_types=1);
ob_start();

$dmsAclRules = $dms->getAclRules();
?>

<h3><?php echo _('Actual restriction rules'); ?></h3>

<?php if (count($dmsAclRules)) { ?>
<table cellpadding="2" class="tld_table">
  <thead>
    <tr bgcolor="#2971A8">
        <th align="center">#</th>
        <th align="center"><?php echo _('Division'); ?></th>
        <th align="center"><?php echo _('Subdivision'); ?></th>
        <th align="center"><?php echo _('Region'); ?></th>
        <th align="center"><?php echo _('Business unit'); ?></th>
        <th align="center"><?php echo _('Department'); ?></th>
        <th align="center"><?php echo _('Alvest functions'); ?></th>
        <th align="center"><?php echo _('Delete?'); ?></th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($dmsAclRules as $k => $acl) {
      ++$k;
      $bgColor = ($k % 2) ? '#dedede' : '#efefef'; ?>
    <tr bgcolor="<?php echo $bgColor; ?>">
      <td><?php echo $k; ?></td>
      <td><?php echo $acl['division']; ?></td>
      <td><?php echo $acl['subdivision']; ?></td>
      <td><?php echo $acl['region']; ?></td>
      <td><?php echo $acl['location']; ?></td>
      <td><?php echo $acl['department']; ?></td>
      <td><?php echo $acl['function_dsc']; ?></td>
      <td align="center">
        <a href="<?php echo "$php_self?m[0]=view&m[1]=acl&m[2]=rules&m[3]=delete&aclid={$acl['id']}&id=".$dms->getID(); ?>"
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
