<?php

use ApiBundle\Client;

$DEFAULT_TITLE .= "\Revisions Detail";
global $kernel;
$request = $kernel->getContainer()->get('request_stack')->getCurrentRequest();
$clientIp = $request->getClientIp();
$client = $kernel->getContainer()->get(Client::class);

switch($m[1]){
case "view":
    try {
        $engineeringRevisions = $client->get(
            sprintf('ion/engineering_items/item=%s;project=', $item));
        usort($engineeringRevisions['revisions'], function($firstRevision, $latestRevision){
            return (strtotime($firstRevision['effectiveDate']) < strtotime($latestRevision['effectiveDate'])) ? 1 : -1;
        });
    } catch (ClientException $exception) {
        $DEFAULT_ERROR[] = _("ERROR: Item not found.");
        break;
    }
	$smarty->assign("revisions_detail", $engineeringRevisions['revisions']);
	$smarty->assign("part_number", $item);
	$smarty->assign("erp", $erp);
	$body = $smarty->fetch("$PATH/revisions_detail/view.revisions_detail.tpl");
break;
default:
	$body = $smarty->fetch("$PATH/revisions_detail/homepage.revisions_detail.tpl");
// Get erp# from IP
    $bus = tldLocation::byOutsideNetworkAddress($clientIp);
    if(count($bus)==1){
        $erp_default = $bus[0]['erp'];
    }else{ // Else look ENG role
        $erp_gg_ENG = (array)$user->isInGroup("gg_ENG");
	    $erp_default = $erp_gg_ENG[0];
    }
	// Get form
	$form = new HTML_QuickForm('frmByNum', 'get');
	$form->addElement(	'hidden', 'm[0]', 'revisions_detail');
	$form->addElement(	'hidden', 'm[1]', 'view');
	$form->addElement(	'header', 'title', 'Get Revisions detail');
	$form->addElement(	'select', 'erp', 'Factory',
		array(""=>"")+tldLocation::getFactoryList("smartyOptions"));
	$form->addElement(	'text',   'item', 'Part Number');
	$form->addElement(	'submit', 'btnSubmit', 'Submit');
	$form->setDefaults(array("erp"=>$erp_default));
	$body .= $form->toHTML();
break;
}
