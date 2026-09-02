<?php
include_once('common.inc.php');
include_once('erp.inc.php');	

$bijections = tldUtils::getSqlToAssocArray('SELECT id, erp, item FROM pi_bijection');
foreach ($bijections as $bijection){
	$erp = tldERP::getERPOb($bijection["erp"]);
	$id = $bijection['id'];
	$description = $erp->getItemData($bijection['item'])['DESCRIPTION'];
	$sql = <<<sql
UPDATE pi_bijection
SET description = '$description'
WHERE id = '$id'
sql;
	tldUtils::sqlExecute($sql);
}
