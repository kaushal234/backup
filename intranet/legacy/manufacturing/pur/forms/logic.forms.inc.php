<?php
switch($m[1]){
case 'changeBU':
    $form = new HTML_QuickForm('frmChangeBU', 'post');
    $form->addElement(  'hidden', 'm[1]', 'changeBU');
    $form->addElement(  'header', 'title', 'Change Company');
    $form->addElement(  'select', 'erp', 'Company', tldLocation::getERPList("smartyOptions"));
    $form->addElement(  'submit', 'btnSubmit', 'Submit');
    $form->setDefaults(array("erp"=>$DEFAULT_ERP));
    $body .=  $form->toHTML();
break;
}
?>