<?php
switch($m[1]){
case 'erp':
	$erp = tldERP::getERPOb($CURRENT_ERP);
//	$erp = new tldBaanERP($CURRENT_ERP);
	if($user->isInGroup(array("gg_ADMIN","gg_PARTS"))){
		$smarty->assign("options", array("showPricing"=>true));
	}
	$smarty->assign("parts", $erp->getItemData($id));

	$body = $smarty->fetch("$PATH/search/list.itemdata.tpl");
break;
default:
	$body = $smarty->fetch("$PATH/search/homepage.search.tpl");
}

?>