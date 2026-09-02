<?php
include_once("erp.inc.php");

// Add overlib library for this section
$JS_INCLUDE=array("/shared/javascript/overlib/overlib.js");
$smarty->assign("js_includes",$JS_INCLUDE);

$DEFAULT_TITLE .= "\OTDP";

switch($m[1]){
case "erpItem":
	$DEFAULT_TITLE .= "\Dashboard";
    if ($erp && $item) {
        $smarty->assign("erp", $erp);
        $smarty->assign("item", $item);
        $body = $smarty->fetch("$PATH/dash/view.erpitem.dash.tpl");
	}
break;
}
