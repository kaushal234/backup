<?php
$DEFAULT_TITLE .= "\Equotes";

switch($m[1]){
default:
	$body .= $smarty->fetch("$PATH/equotes/homepage.equotes.tpl");
	$form = new HTML_QuickForm('frmEquotesLogin', 'post', 'http://equotes.tld-america.com/finance/t_index.asp');
	$form->addElement('hidden', 'UserName', $user->getEmail());
	$form->addElement('hidden', 'Password', $user->getPassword());
	$form->addElement('hidden', 'Submit', "Submit");
	$form->addElement('header', 'title', 'Equotes login transfer...');
	$form->addElement('radio', 'Str_langue', 'English', '', 'UK');
	$form->addElement('radio', 'Str_langue', 'French', '', 'FR');
	$form->addElement('radio', 'Str_langue', 'Chinese', '', 'ZH');
	$form->addElement('submit', 'btnSubmit', 'Login');
	$form->setDefaults(array("Str_langue"=>"UK"));
	$body .= $form->toHtml();
}
?>
