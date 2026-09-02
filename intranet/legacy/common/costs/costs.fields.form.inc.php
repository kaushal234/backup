<?php
// listing
$curList = tldForex::getCurrencyList();
$peopleList = tldDirectory::getUserList("smartyOptions");
// Form
$form->addElement(  'select', 'uid', 'Concerned User', array(''=>'')+$peopleList);
$form->addElement(  'text',   'date', 'Cost date', array("class"=>"datepicker"));
$form->addElement(  'select', 'type', 'Cost type', 
    array(''=>'')+tldModCost::getCostTypeByModuleAsTypeType($module));
$form->addElement(  'text', 'description', 'Description');
//$form->addElement(  'select', 'um', 'UM', array());
//$form->addElement(  'text', 'qty', 'Quantity');
$form->addElement(  'select', 'cur', 'Currency', array(''=>'')+$curList);
$form->addElement(  'text', 'price', 'Price');
// Rules
$requiredFields = array('type','description','cur','price','uid','date');
foreach($requiredFields as $requiredField){
    $form->addRule($requiredField, "Required", "required");
}
$form->addRule("price", "Should be numeric or decimal (using dot)", "numeric");
$form::registerRule('checkdate', 'callback', 'checkCostDate');
$form->addRule('date', "Date not valid", 'checkdate', TRUE);

// Default value
$form->setDefaults(array('cur'=>$sess['dcur']));
// Buttons
$form->addElement(  'submit', 'btnSubmit', 'Submit');


// Call back functions

function checkCostDate($dt){
    if($dt=='0000-00-00') return FALSE;
    try{
        $date = new DateTime($dt);
    }catch(Exception $e){
        return FALSE;
    }
    return TRUE;
}
