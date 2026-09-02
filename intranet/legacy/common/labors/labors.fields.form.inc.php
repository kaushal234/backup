<?php
// listing
$peopleList = tldDirectory::getUserList("smartyOptions");
// Form
$form->addElement(  'select', 'user_id', 'Concerned User', array(''=>'')+$peopleList);
$form->addElement(  'text',   'dt_work', 'Work date', array("class"=>"datepicker"));
$form->addElement(  'text', 'description', 'Description');
$form->addElement(  'text', 'nb_hours', 'Nb hours');
// Rules
$requiredFields = array('user_id','dt_work','description','nb_hours');
foreach($requiredFields as $requiredField){
    $form->addRule($requiredField, "Required", "required");
}
$form->addRule("nb_hours", "Should be numeric or decimal (using dot)", "numeric");
$form::registerRule('checkdate', 'callback', 'checkWorkDate');
$form->addRule('date', "Date not valid", 'checkdate', TRUE);
$form->addElement(  'submit', 'btnSubmit', 'Submit');


// Call back functions

function checkWorkDate($dt){
    if($dt=='0000-00-00') return FALSE;
    try{
        $date = new DateTime($dt);
    }catch(Exception $e){
        return FALSE;
    }
    return TRUE;
}
