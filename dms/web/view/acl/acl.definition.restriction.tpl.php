<?php declare(strict_types=1);
ob_start();
?>

<div id="rule_definition">

<h3><?php echo _('Restriction rules definition'); ?></h3>

<?php
$_NOTE[] = _('Your attention is requested!');
$_NOTE[] = _('Access type set to CONFIDENTIAL is an exceptionnal process that should be used in very exceptionnal cases');
?>

<table>
  <tr>
    <td style="vertical-align:middle;"><strong><?php echo _('Dimension rules'); ?></strong></td>
    <td>
      <ul>
        <li><strong><?php echo _('Division'); ?>/<?php echo _('Subdivision'); ?>/<?php echo _('Region'); ?></strong>: <?php echo _('All people set in the selected list of Alvest Division / Subdivision / Region'); ?></li>
        <li><strong><?php echo _('Business Unit'); ?></strong>: <?php echo _('All people set in the selected list of Alvest Business Unit'); ?></li>
        <li><strong><?php echo _('Department'); ?></strong>: <?php echo _('All people set in the selected list of Alvest Department'); ?></li>
        <li><strong><?php echo _('Function'); ?></strong>: <?php echo _('All people set in the selected list of Alvest function'); ?></li>
      </ul>
    </td>
  </tr>
</table>

</div>

<?php
$_BUFF = ob_get_contents();
ob_end_clean();

return $_BUFF;
?>
