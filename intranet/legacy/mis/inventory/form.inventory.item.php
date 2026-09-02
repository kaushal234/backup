<?php
// Listing
$typeList = Tld_Mis_Inventory_ItemType::getListAsIdName($m[1]);
$brandList = Tld_Mis_Inventory_ItemBrand::getListAsIdName();
$locationList = tldLocation::getLocationList("smartyOptions");
$dptList = tldDepartment::getListAsIdDepartment();
$serverCAT = Tld_Mis_Inventory_ItemType::getServerCAT();
$yesNoList = ['0' => 'No', '1' => 'Yes', '2' => 'IN PROGRESS'];
$yesNo = ['0' => 'No', '1' => 'Yes'];
// Fields
if ($user->isInGroup(["GG_MISINV_tabletManager"]) && !$user->isInGroup(["GG_MIS","superuser"])) {
    $form->addElement('select', 'type_id', 'Type', [""=>"", '10' => "SHOPFLOOR TABLET"], ['id' => 'typeid', 'onchange' => "msgAjax()"]);
}else{
    $form->addGroup(
        [
            $form->createElement('select', 'type_id', 'Type', ["" => ""] + $typeList, ['id' => 'typeid', 'onchange' => "msgAjax()"]),
            $form->createElement('submit', 'btnAdd', '+'),
        ], 'type_group' , 'Type'
    );
}
$form->addElement('select', 'functionality', 'Functionality (for server only)', $serverCAT, ['id' => 'func','style' => 'display:none', 'onchange' => "msgAjax()"]);
$form->addElement('select', 'brand_id', 'Brand', ["" => ""] + $brandList);
if($m[1] === 'addHardware' || ($m[2] === 'edit' && Tld_Mis_Inventory_Item::getCategory($id) === 'HARDWARE')){
    $form->addElement('text', 'model', "Model");
    $form->addElement('text', 'manufacturer_sn', "Manufacturer SN");
    $refe = ['id' => 'warranty_date', 'class' => 'datepicker','onchange' => "checkdate()"];
}elseif($m[1] === 'addSoftware' || ($m[2] === 'edit' && Tld_Mis_Inventory_Item::getCategory($id) === 'SOFTWARE')){
    $form->addElement('text', 'manufacturer_sn', "Installation Key");
    $form->addElement('text', 'qty', "Quantity", ['id' => 'quantity','onblur' => "checkqty()"]);
    $refe = ['id' => 'warranty_date', 'class' => 'datepicker','onchange' => "checkdate()",'style' => 'display:none'];
}else{
    $form->addElement('text', 'bandwidth', "Bandwidth");
    $form->addElement('text', 'cost', "Cost per month");
    $form->addElement('text', 'ipaddress', "IP address");
    $form->addElement('text', 'contract', "Contract NO");
    $form->addElement('text', 'hotline', "Service hotline");
    $form->addElement('textarea', 'salecontact', "Sales contact information",["rows"=> 5,"cols"=>40]);
    $refe = ['id' => 'warranty_date', 'class' => 'datepicker','onchange' => "checkdate()",'style' => 'display:none'];
}
if($m[2] === 'edit'){
    $form->addElement('text', 'tld_sn', "TLD SN", ['disabled' => 'readonly']);
}else{
    $form->addElement('text', 'tld_sn', "TLD SN", ['id' => 'tldsn', 'class' => 'nob', 'disabled' => 'readonly']);
}
$form->addElement('text', 'fixasset_id', 'Fix Asset ID');
$form->addElement('select', 'state', 'State', ["" => ""] + $stateList, ['id' => 'state','onchange' => "checkstate()"]);
$form->addElement('textarea', 'description', 'Description', ["rows" => 5, "cols" => 40]);
$form->addElement('header', 'title', "Misc info");

$form->addGroup(
    [
        $form->createElement('text', 'warranty_date', "End of warranty",  $refe),
        $form->createElement('radio', "warranty_year", 'oneyear', '1 year', '+1 year', ['id' => 'warranty_year1','onclick' => "checkyear()", 'style' => 'display:none']),
        $form->createElement('radio', "warranty_year", 'twoyears', '2 years', '+2 year', ['id' => 'warranty_year2','onclick' => "checkyear()", 'style' => 'display:none']),
        $form->createElement('radio', "warranty_year", 'threeyears','3 years', '+3 year', ['id' => 'warranty_year3','onclick' => "checkyear()", 'style' => 'display:none']),
        $form->createElement('radio', "warranty_year", 'fouryears','4 years', '+4 year', ['id' => 'warranty_year4','onclick' => "checkyear()", 'style' => 'display:none']),
    ], 'dt_warranty_end' , 'End of warranty', null
);


$form->addElement('select', 'buyer_bu_id', 'Requestor BU', ["" => ""] + $locationList,['id' => 'buid', 'onchange' => "msgAjax()"]);
$form->addElement('select', 'buyer_dpt_id', 'Requestor Department', ["" => ""] + $dptList);
$form->addElement('select', 'hidden', 'Disposed?', ["" => ""] + $yesNoList);
$form->addElement('select', 'notify', 'Warranty Notification?', ["" => ""] + $yesNo);
include_once 'inv_ajax.tpl';
// rules
$requiredFields = [
    'type_id', 'brand_id', 'model', 'manufacturer_sn',
    'state', 'dt_warranty_end', 'buyer_bu_id', 'hidden',
];
if($m[1] === 'addSoftware') {
    $requiredFields = [
        'type_id', 'brand_id', 'manufacturer_sn',
        'state', 'buyer_bu_id', 'hidden', 'qty','buyer_dpt_id',
    ];
    $form->addRule("qty", 'Should be numeric', 'numeric');
}
if($m[1] === 'addISP') {
    $requiredFields = [
        'type_id', 'brand_id', 'bandwidth',
        'state', 'cost', 'hotline', 'contract','salecontact',
    ];
    $contact = <<<EOF
Sales Name: 
Mobile Phone: 
Email address: 
Title: 
EOF;
    $form->setDefaults(
        ['salecontact' => $contact]
    );
    $form->addRule("qty", 'Should be numeric', 'numeric');
}
if(!$form->isSubmitted() || $btnSubmit){
    foreach ($requiredFields as $requiredField) {
        $form->addRule($requiredField, 'Required', 'required');
    }
    $ruleType['type_id'][] = [
        'This is required',
        'required',
    ];
    $form->addGroupRule('type_group', $ruleType);
}
$form->addElement('submit', 'btnSubmit', 'Submit');
