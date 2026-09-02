<?php

switch($module){
case 'PDC':
case 'SCAR':
    $form->addElement('text', 'pn', 'Part Number');
	$requiredFields = array('pn');
break;
default:
    $form->addElement('text', 'pn', 'Part Number');
    $form->addElement('text', 'dsc', 'Description');
    $form->addElement('text', 'qty', 'Quantity');
	$requiredFields = array('pn','dsc','qty');
break;
}
