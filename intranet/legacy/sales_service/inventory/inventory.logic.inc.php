<?php
include_once("sales_service.inc.php");

$DEFAULT_TITLE .= "\Inventory";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=inventory" title="Inventory Homepage">Home (Reset Listing)</a>
EOF;
$body .= $smarty->fetch("$PATH/inventory/homepage.inventory.tpl");
$matrix = new tldMatrix(
		tldEquipment::inventoryCountByFactoryStatus(0),
		"man_location", "inv_status", "num",
		"$php_self?m[0]=inventory&light=0",
		"Inventory Count by Status, Factory, ER not light"
);
$body .= $matrix->fetch()."</br>";

$matrix = new tldMatrix(
	tldEquipment::inventoryCountByFactoryStatus(1),
	"man_location", "inv_status", "num",
	"$php_self?m[0]=inventory&light=1",
	"Inventory Count by Status, Factory, ER light"
);
$body .= $matrix->fetch()."</br>";

$matrix = new tldMatrix(
	tldEquipment::inventoryCountByFactoryStatusYellowTag(0),
	"man_location", "inv_status", "num",
	"$php_self?m[0]=inventory&light=0&yellow_tag=1",
	"Inventory Count by Status, Factory, ER light, Yellow Green Tag"
);
$body .= $matrix->fetch()."</br>";

$matrix = new tldMatrix(
	tldEquipment::inventoryCountByFactoryStatusWithSOL(0),
	"man_location", "inv_status", "num",
	"$php_self?m[0]=inventory&light=0&link_sol=1",
	"Inventory Count by Status, Factory, ER light, Stock Unit ER linked to a SOL"
);
$body .= $matrix->fetch()."</br>";

if(!empty($y) && $y != "ALL"){
	$status_title = " - Status: $y";
	$status = $y;
}
if(!empty($x) && $x != "ALL"){
	$factory_title = " - Factory: $x";
	$factory = $x;
}
if(!empty($yellow_tag)){
	$yellow_tag_title = " - Yellow Tag";
}
if(!empty($link_sol)){
	$link_sol_title = " - Stock Unit ER linked to a SOL";
}

$erTypeList = array(""=>"") + tldType::getList("smartyOptions_Name");
$form = new HTML_QuickForm('FrmCrab');
$form->addElement('header', 'title', 'Filter by Type');
$form->addElement('hidden', 'm[0]', 'inventory');
$form->addElement('hidden', 'x', $x);
$form->addElement('hidden', 'y', $y);
$form->addElement('hidden', 'light', $light);
$form->addElement('select', 'type', 'Type', $erTypeList);
$form->addElement('submit', 'btnSubmit', 'Submit');
$body .= $form->toHTML();

if($form->validate() && !empty($type)){
	$type_title = " - Type: $type";
}

$report = new tldReportColumnar(
    tldEquipment::getInventory($factory, $status, $type, $light, $yellow_tag, $link_sol),
    [
        "xItems" => [
            "man_location" => "Factory",
            "sn" => "ER#",
            "model" => "Model",
            "status" => "Status",
            "er_status" => "ER Status",
            "customer" => "Customer (User)",
            "location_short" => "Location",
            "mfg_comments" => "Manufacturing Comments",
			"dgt_rev" => "Estimated GT",
			"dgt_com" => "First GT"
        ],
        "title" => "Inventory $factory_title $status_title $type_title $yellow_tag_title $link_sol_title",
        "links" => [
            "sn" => "/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=search&m[2]=bySN&sn=",
        ],
        "functions" => [
            "Option&nbsp;list" => [
                "url" => "/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view&m[2]=options&id=",
                "param" => "id",
            ],
        ],
    ]
);
$body .= $report->fetch();

?>
