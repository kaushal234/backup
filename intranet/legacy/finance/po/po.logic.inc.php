<?php
include_once("erp.inc.php");

if(!$user->isInGroup(array("gg_ADMIN","gg_ACCT","gg_SALES","gg_PARTS","gg_PUR","gg_MIS","gg_SUPPORT","gg_PRODUCTION"))){
	$DEFAULT_ERROR[] = "You do not have permissions for this page..";
	return;
}

$DEFAULT_TITLE .= "\Purchase Orders";
$DEFAULT_MENU .=<<<EOF
    <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="$php_self?m[0]=po">Home</a>
EOF;

switch($m[1]){
case 'view':
  	if(empty($id) || !is_numeric($id)){
		$DEFAULT_ERROR[] = "ERROR: id not set or invalid";
		break;
	}
	if(empty($erp) || !is_numeric($erp)){
		$DEFAULT_ERROR[] = "ERROR: erp not set or invalid";
		break;
	}

	$DEFAULT_TITLE .= "\PO#$id - $erp";

	$arch = new tldArchive($erp);
    $rows = $arch->byTypeID("PURCHASE ORDER", $id);
    // Check if results
    if(count($rows)<1){
        $DEFAULT_ERROR[] = "No results found...";
        break;
    }
    // Check if we want listing or out the last file directly
    if($out==1){
    	if(empty($rows[0]['filepath'])){
    		$DEFAULT_ERROR[] = "ERROR: No file path set in archive...";
            break;
    	}
    	$url = tldArchive::getWebRoot()."/".$rows[0]['filepath'];
    	$file = new basicFile($url);
    	if(!$file->fileExists()){
        	$DEFAULT_ERROR[] = "ERROR: File not found...";
            break;
    	}
		$template = 'NO_TEMPLATE';
    	$file->outFile();
    }
    else{
    	$smarty->assign("lines", $rows);
    	$body .= $smarty->fetch("finance/archive/archive.list.tpl");
    }
break;
default:
	$body .= $smarty->fetch("finance/po/homepage.po.tpl");
    // Get list
    $erpList = tldLocation::getERPList("smartyOptions");
    // Get Form
    $form = new HTML_QuickForm('frmInvByNum', 'post');
    $form->addElement(	'hidden', 'm[0]', 'po');
    $form->addElement(	'hidden', 'm[1]', 'view');
    $form->addElement(	'header', 'title', "Purchase Orders by Number");
    $form->addElement(	'select', 'erp', 'Factory', $erpList);
    $form->addElement(	'text', 'id', 'PO#');
    $form->addElement(	'checkbox', 'out', NULL, 'Check to get file directly');
    $form->addElement(	'submit', 'btnSubmit', 'Submit');
    $body .= $form->toHTML();
}
