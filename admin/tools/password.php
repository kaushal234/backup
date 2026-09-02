<?php
$form = new HTML_QuickForm('frmNewTask', 'post');
$form->addElement(	'hidden', 'm[0]', 'password');
$form->addElement(	'header', 'title', "Set options below");
$form->addElement(	'text', 'length', "Password length");
$form->addElement(	'select', 'use_upper', 'Use upper case?',
    array(1=>"Y",0=>"N")
);
$form->addElement(	'select', 'use_lower', 'Use lower case?',
    array(1=>"Y",0=>"N")
);
$form->addElement(	'select', 'use_number', 'Use numbers?',
    array(1=>"Y",0=>"N")
);
$form->addRule('length', 'Required','required');
$form->addElement(	'submit', 'btnSubmit', 'Generate');
$form->setDefaults(array('length'=>8));

if(!$form->validate()){
    $body .= $form->toHTML();
    return;
}

$vars = tldUtils::cleanupFormInput($form->exportValues());
$seed = create_password(
    $vars['length'],
    $vars['use_upper'],
    $vars['use_lower'],
    $vars['use_number']
);
$body .= $form->toHTML();
$body .= <<<EOF
<p><strong>Password:</strong> $seed</p>
EOF;

function create_password($length=8,$use_upper=1,$use_lower=1,$use_number=1){
	$upper = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$lower = "abcdefghijklmnopqrstuvwxyz";
	$number = "0123456789";
	//$special = "#{([-|_\@)]=+-*!?;:./";
	if($use_upper){
		$seed_length += 26;
		$seed .= $upper;
	}
	if($use_lower){
		$seed_length += 26;
		$seed .= $lower;
	}
	if($use_number){
		$seed_length += 10;
		$seed .= $number;
	}
	for($x=1;$x<=$length;$x++){
		$password .= $seed{mt_rand(0,$seed_length-1)};
	}
	return($password);
}
?>