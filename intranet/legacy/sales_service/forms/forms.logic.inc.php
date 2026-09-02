<?php
switch($m[1]){
case 'changeBU':
    $form = new HTML_QuickForm('frmChangeBU', 'post');
    $form->addElement(  'hidden', 'm[1]', 'changeBU');
    $form->addElement(  'header', 'title', 'Change Company');
    $form->addElement(  'select', 'buid', 'Company', tldLocation::getSalesOrgList("smartyOptionsIDLocation"));
    $form->addElement(  'submit', 'btnSubmit', 'Submit');
    $form->setDefaults(array("buid"=>$DEFAULT_BUID));
    $body .= $form->toHTML();
break;
}
?>