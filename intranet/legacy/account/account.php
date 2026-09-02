<?php
include_once("common.inc.php");
include_once("forms_and_reports.inc.php");

session_start();
if(!isset($_SESSION['sess'])) {
	$_SESSION['sess'] = null;
}
$sess =& $_SESSION['sess'];

$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);
$userid = $GLOBALS["PHP_AUTH_USER"];

$smarty = tldUtils::getSmarty("intranet");
$php_self = $_SERVER['PHP_SELF'];

//initialize vars
$PATH = "account";
$DEFAULT_TEMPLATE = "intranet.tpl";
$DEFAULT_TITLE = "My Account";
$DEFAULT_ERROR = array();
$JS_INCLUDE = array();

$menu = <<<EOF
	<tld:include src="account/layout.html.twig" />
EOF;

switch($m ?? null){
case 'adressbook':
	$menu .=<<<EOF
		<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<a href="$php_self?m=adressbook&a=add">Add Recipient</a>
EOF;
	switch($a){
		case 'add':
			// Create the form
			$form = new HTML_QuickForm('frmEmail','post');
			$form->addElement('header', 'title', 'Recipient Email');
			$form->addElement('hidden', 'm', 'adressbook');
			$form->addElement('hidden', 'a', 'add');
			$form->addElement('text', 'list_key', 'Recipient Name:',
                array('size="30"','maxlength="50"'));
			$form->addElement('text', 'value', 'Recipient Email:',
                array('size="30"','maxlength="50"'));
			$form->addElement('submit', 'btnSubmit', 'Submit');
			$form->addRule(	'list_key', 'Required', 'required');
			$form->addRule(	'value', 'Required', 'required');
			$form->addRule( 'value', 'Email not valid', 'email');
			if($form->validate()){
				$form->freeze();
				$data = $form->getSubmitValues();
				// Get Adressbook
				foreach($user->getEmailAdressbook() as $email){
					$adressbook[$email["list_key"]]=$email["value"];
				}
				// Check first if email already exists
				if( !in_array($data['value'],$adressbook) ){
					$e=$user->setEmailAdressbook($data);
					if(is_numeric($e)){
						$body .= "<p>Recipient added successfully !</p>";
					}
					else {
						$DEFAULT_ERROR[] = "ERROR: Problem occurred when creating the contact";
					}
				}
				else {
					$DEFAULT_ERROR[] = "ERROR: This email already exists in your adressbook";
				}
			}
			else {
				$body .= $form->toHTML();
			}
		break;
		case 'del':
			if(!empty($id) && is_numeric($id)){
				$adressbook = new tldModList($id);
				$row = $adressbook->getHeader();
				// Check owner
				if($row['parent_id'] === $user->getID()){
					$e = tldModList::delete($id);
					if(!is_string($e)) {
						$body .= "<p>Contact deleted successfully !</p>";
					}
					else {
						$DEFAULT_ERROR[] = "ERROR: Problem occurred for deleting Recipient #$id";
					}
				}
				else {
					$DEFAULT_ERROR[] = "ERROR: This contact is not yours";
				}
			}
			else {
				$DEFAULT_ERROR[] = "ERROR: Contact with id '$id' not valid";
			}
		break;
	}
	$rows = $user->getEmailAdressbook();
	$report = new tldReportColumnar(
		$rows,
		array(
			"xItems"=>array("list_key"=>"Name","value"=>"Email","id"=>"Delete"),
			"links"	=>array(
				"id"=>array(
					"url"=>"$php_self?m=adressbook&a=del",
					"params"=>array("id"=>"id"),
					"confirmPopup"=>"Are you sure to delete this contact ?"
				)
			),
			"title"	=>"Personnal email adressbook",
			"showItemNumbers"=>true
		)
	);
	$body .= $report->fetch();
break;
case 'signature':
	$DEFAULT_TITLE .= "\Email signature";
	switch($a){
		case 'send':
			$smarty->assign("data",$user->itsDetails);
			$data = $smarty->fetch("account/email.signature.tpl");
			$fp = fopen('/tmp/signature.htm', 'w');
			flock($fp,LOCK_EX);
			fwrite($fp, $data);
			flock($fp,LOCK_UN);
			fclose($fp);
			$signature = new basicFile('/tmp/signature.htm');
			$signature->outFile();
			exit;
		default:
			$body = <<<EOF
			<h3>Generate email signature</h3>
			<p>By clicking the link below, you will download your personnal TLD email signature.<br/>
			Save it to your computer in My documents and use this file to make your signature into outlook.</p>
			<p><a href="$php_self?m=signature&a=send">Send the signature file now</a></p>
EOF;
		break;
	}
break;
case 'groups':
	$DEFAULT_TITLE .= "\Groups";
	$report = new tldReportColumnar(
		$user->getGroups(),
		array(
			"xItems"=>array(
            	"group_name"	=>"Group Name",
            	"level"			=>"Level",
            	"description"	=>"Description"
            ),
			"title"=>"Your groups and Roles"
		)
	);
	$body .= $report->fetch();
break;
default:
	$body .= "<h3>Home</h3>";
	$smarty->assign("person",$user->itsDetails);
	$body .= $smarty->fetch("account/view.card.tpl");
}

$smarty->assign("js_includes",$JS_INCLUDE);
$smarty->assign("body",$body);
if(empty($title)) {
	$title = $DEFAULT_TITLE;
}
$smarty->assign("title",$title);
$smarty->assign("error", implode("<br>", $DEFAULT_ERROR));

if(empty($template)) {
	$template = $DEFAULT_TEMPLATE;
}
if($template !== "NO_TEMPLATE") {
	$smarty->display($template);
}


function _getGeneralTab(){
	global $smarty,$user;
	$body .= "<h3>Home</h3>";
	$smarty->assign("person",$user->itsDetails);
	$body .= $smarty->fetch("account/view.card.tpl");
	return $body;
}
