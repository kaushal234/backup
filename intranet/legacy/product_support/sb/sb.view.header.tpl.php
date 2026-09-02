<?php
ob_start();
?>

<table border="0" width="100%" cellspadding="2" cellspacing="4">
  <tr>
    <td align="center" bgcolor="#7EB0D7">
    	<b>SB#</b><?= $sb->getID() ?> - <?= $sb->getFactory() ?> - <?= $sb->getStatus() ?><br>
    	<?= $sb->getCategory() ?> (<?= $sb->getIFactor() ?>)<br>
    	<?= $sb->itsHeader['title'] ?>
	</td>
    <td align="center" bgcolor="<?= $sb->isConfidential() ? '#FF5252':'#FFB843'; ?>">
    	<b><?= $sb->isConfidential() ? 'CONFIDENTIAL':'Not confidential'; ?></b><br>
    	<?php  if($sb->isConfidential()): ?>
    	Customer <u>must</u> not know the existence of this SB
    	<?php  else: ?>
    	Customer have access to this SB
    	<?php  endif;?>
	</td>
    <td align="center" bgcolor="#C5C5C5">
        <?php  if (!in_array($sb->getStatus(), ['CLOSED', 'CANCELLED'], true)): ?>
    	<b>Actor:</b> <?= _getActor($sb->getStatus()) ?><br>
    	<b>Actions:</b>
    		<a href="#" title="<?= _getActions($sb->getStatus()) ?>">
    			<img src="/shared/icons/application/help.png">
			</a>
    	<?php  else: ?>
        Status <?= $sb->getStatus() ?>, nothing to be done
    	<?php  endif; ?>
	</td>
  </tr>
</table>

<br/>

<?php

function _getActor($status)
{
    switch ($status) {
        case 'PENDING':
            return 'Product Support Manager';
        case 'CSM_APPROVAL':
            return 'Customer Service Manager';
        case 'SSD_DECISION':
            return 'Sales Service Director';
        case 'IMPLEMENTATION':
            return 'Customer Service Manager & personal';
    }
}

function _getActions($status)
{
    $actions = [];
    switch ($status) {
        case 'PENDING':
            $actions[] = 'Set SB information, parts & files';
            $actions[] = 'Add ER coverages';
            $actions[] = 'Generate ER list from ER coverage';
            $actions[] = 'Change status to CSM_APPROVAL';
            break;
        case 'CSM_APPROVAL':
            $actions[] = 'Check SB information, parts & files';
            $actions[] = 'If not ok, contact PSM or create task';
            $actions[] = 'If ok, sign the SB to approve';
            break;
        case 'SSD_DECISION':
            $actions[] = 'Define ER decision for parts & service via SSD decision dashboard';
            $actions[] = 'Sign the SB to confirm final decision';
            break;
        case 'IMPLEMENTATION':
            $actions[] = 'Use Implementation dashboard';
            $actions[] = 'Implement SSD & customer decision';
            break;
    }
    // prepare html
    $actionList = '';
    foreach ($actions as $k => $action) {
        $k++;
        $actionList .= "$k - $action\r\n";
    }
    // return result
    return $actionList;
}

return ob_get_clean();
