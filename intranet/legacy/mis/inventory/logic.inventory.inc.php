<?php
require_once('HTML/QuickForm/advmultiselect.php');

if (!$user->isInGroup(['GG_MISINV_tabletManager','GG_MISINV_fixedAssetManager','gg_MIS','ROLE_MISM','ROLE_CIO','superuser','prod_IT_referent'])) {
	$DEFAULT_ERROR[] = "ERROR: You do not have access to this module";

	return;
}

$DEFAULT_TITLE .= '\Inventory [NEW]';
$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=inventory">Home</a>
EOF;
if ($user->isInGroup(['GG_MISINV_tabletManager','gg_MIS','ROLE_MISM','ROLE_CIO','superuser'])) {
$DEFAULT_MENU .= <<<EOF
 | <a href="$php_self?m[0]=inventory&m[1]=addHardware">Create item</a>
EOF;
}
$DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=inventory&m[1]=reports">Reports</a>
EOF;
if ($user->isInGroup(['GG_MISINV_tabletManager','gg_MIS','ROLE_MISM','ROLE_CIO','superuser'])) {
    $DEFAULT_MENU .= <<<EOF
 | <a href="$php_self?m[0]=inventory&m[1]=advSearch">Advanced Search</a>
EOF;
}
if ($user->isInGroup(['gg_MIS','ROLE_MISM','ROLE_CIO','superuser'])) {
    $DEFAULT_MENU .= <<<EOF
 | <a href="$php_self?m[0]=inventory&m[1]=search">Search</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=inventory&m[1]=admin">Administration</a>
 | <a href="./inventory/inventory_admin.php">Maintain Inventory</a>
EOF;
}

$DEFAULT_ERROR[] = "WARNING: Module under development";
$xItems = [
	"id" => "Item#",
	"dt" => "Date",
	"name" => "Type",
	"category" => "Category",
	"brand_name" => "Brand",
	"model" => "Model",
	"manufacturer_sn" => "Manufacturer SN",
	"tld_sn" => "TLD SN",
	"fixasset_id" => 'Fix Asset ID',
	"state" => "State",
	"description" => "Description",
	"dt_warranty_end" => "End of warranty",
	"buyer_location" => "Requestor BU",
	"buyer_dpt" => "Requestor Department",
	"destination_type" => "Destination Type",
	"destination" => "Location",
	"assignee" => "Location ID",
	"hidden_status" => "Disposed?",
    "notify" => "Warranty Notification?",
];

switch ($m[1]) {
	case 'invadd.getinfo':
        if(!empty($buid)){
            $loca = strlen(tldLocation::getLocationByID($buid))>3 ? strtoupper(substr(tldLocation::getLocationByID($buid), 4,3)) : strtoupper(tldLocation::getLocationByID($buid));
            if($buid==55){
                $loca = 'KEM';
            }
        }
		$body = '';

		$query = <<<EOF
    SELECT	short_desc, length
    FROM mis_inventory_item_types
    WHERE id = $typeid
EOF;

		$row = tldUtils::getSqlRowToAssocArray($query);
		$length = strlen($row['short_desc']) + $row['length'];
        $query1 = <<<EOF
SELECT	CONCAT('{$row['short_desc']}',max(RIGHT(tld_sn, {$row['length']}))) AS tld_sn
    FROM mis_inventory_items
    LEFT JOIN mis_inventory_item_types ON mis_inventory_item_types.id = mis_inventory_items.type_id
    WHERE tld_sn like "{$row['short_desc']}%" AND LENGTH(tld_sn)=$length AND RIGHT(tld_sn, {$row['length']}) not like '%[^0-9]%'
EOF;
        $partsn = $row['short_desc'];

        if($row['short_desc'] === 'SRV') {
            $length = strlen($loca) + strlen($func) + $row['length'];
            $partsn = $loca.$func;
            $query1 = <<<EOF
SELECT	CONCAT('$loca','$func',max(RIGHT(tld_sn, {$row['length']}))) AS tld_sn
    FROM mis_inventory_items
    LEFT JOIN mis_inventory_item_types ON mis_inventory_item_types.id = mis_inventory_items.type_id
    WHERE tld_sn like "$loca$func%" AND LENGTH(tld_sn)=$length AND RIGHT(tld_sn, {$row['length']}) not like '%[^0-9]%'
EOF;
        }
		$ret = tldUtils::getSqlRowToAssocArray($query1);
		if (empty($ret) || !is_numeric(str_replace($partsn, '', $ret['tld_sn']))) {
			$tldsn = $row['short_desc'] . str_repeat("0", $row['length'] - 1) . '1';
            if($row['short_desc'] === 'SRV'){
                $tldsn = $loca . $func.str_repeat("0", $row['length'] - 1) . '1';
            }
		} elseif (preg_match('/[a-zA-Z]/', str_replace($partsn, '', $ret['tld_sn']))) {
			$tldsn = $row['short_desc'] . str_repeat("0", $row['length'] - 1) . '1';
            if($row['short_desc'] === 'SRV'){
                $tldsn = $loca .$func. str_repeat("0", $row['length'] - 1) . '1';
            }
		} else {
			$val = (int)(preg_replace('/[\.a-zA-Z]/', "", $ret['tld_sn'])) + 1;
			$tldsn = $row['short_desc'] . str_repeat("0", $row['length'] - strlen($val)) . $val;
            if($row['short_desc'] === 'SRV'){
                $tldsn = $loca .$func. str_repeat("0", $row['length'] - strlen($val)) . $val;
            }
		}

        //Special Case for laptop catagory
        $year = date('y');
        $manufacturerSn = isset($_POST['manufacturer_sn'])
            ? trim($_POST['manufacturer_sn'])
            : '';
        $manufacturerSn = preg_replace('/[^A-Za-z0-9\-]/', '', $manufacturerSn);

        if ($row['short_desc'] === 'LAP') {
            $tldsn = sprintf(
                '%s%s-%s',
                strtoupper($row['short_desc']),
                $year,
                $manufacturerSn
            );
        }
		header('Content-type: text/json');
		if (empty($row['short_desc']) || $row['length'] == 0) {
			$row['status'] = '0';
			$row['typeid'] = $_POST['typeid'];
		} else {
			$row['status'] = '1';
			$row['tldsn'] = $tldsn;
		}
		$_SESSION['sn'] = $tldsn;
		echo json_encode($row);
		exit;
		break;
	case 'addHardware':
        $loca = strtoupper(substr(tldLocation::getLocationByID($user->getBUID()), 4,6));
		$stateList = Tld_Mis_Inventory_Item::getStateList('HARDWARE');
        if ($user->isInGroup(['gg_MIS','ROLE_MISM','ROLE_CIO','superuser'])) {
            $body .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="$php_self?m[0]=inventory&m[1]=addSoftware">Switch to add Software Form</a>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="$php_self?m[0]=inventory&m[1]=addISP">Switch to add Internet Line Form</a><br><br>
EOF;
        }
		$DEFAULT_TITLE .= '\Create item';
		// Form
		$form = new HTML_QuickForm('frmNew', 'post');
		$form->addElement('hidden', 'm[0]', 'inventory');
		$form->addElement('hidden', 'm[1]', 'addHardware');
		$form->addElement('header', 'title', "Create new item");
		include('form.inventory.item.php');
		$form->setDefaults(['buyer_bu_id' => $user->getBUID()]);

		if (!$form->validate()) {
			$body .= $form->toHTML();
			break;
		}

		$vars = tldUtils::cleanupFormInput($form->exportValues());

		if ($vars['type_group']['btnAdd']) {
			header("Location: http://{$_SERVER['HTTP_HOST']}/en/private/mis/mis.php?m[0]=inventory&m[1]=admin&m[2]=types&m[3]=add" );
		}
		if (empty($vars['dt_warranty_end']['warranty_date'])) {
			$vars['dt_warranty_end'] = date('Y-m-d', strtotime($vars['dt_warranty_end']['warranty_year']));
		}else{
			$vars['dt_warranty_end'] = $vars['dt_warranty_end']['warranty_date'];
		}
		if(!empty($vars['type_group']['type_id'])){
            $vars['type_id'] = $vars['type_group']['type_id'];
        }else{
            $vars['type_id'] = $vars['type_id'];
        }
		// create
		$body = '';

		$query = <<<EOF
    SELECT	short_desc, length
    FROM mis_inventory_item_types
    WHERE id = '{$vars['type_id']}'
EOF;

		$row = tldUtils::getSqlRowToAssocArray($query);

        $length = strlen($row['short_desc']) + $row['length'];

		$query1 = <<<EOF
SELECT	CONCAT('{$row['short_desc']}',max(RIGHT(tld_sn, {$row['length']}))) AS tld_sn
    FROM mis_inventory_items
    LEFT JOIN mis_inventory_item_types ON mis_inventory_item_types.id = mis_inventory_items.type_id
    WHERE tld_sn like "{$row['short_desc']}%" AND LENGTH(tld_sn)=$length AND RIGHT(tld_sn, {$row['length']}) not like '%[^0-9]%'
EOF;
		$ret = tldUtils::getSqlRowToAssocArray($query1);
		if (empty($ret || !is_numeric(str_replace($row['short_desc'], '', $ret['tld_sn'])))) {
			$vars['tld_sn'] = $row['short_desc'] . str_repeat("0", $row['length'] - 1) . '1';
		} else {
			if (preg_match('/[a-zA-Z]/', str_replace($row['short_desc'], '', $ret['tld_sn']))) {
				$vars['tld_sn'] = $row['short_desc'] . str_repeat("0", $row['length'] - 1) . '1';
			} else {
				$val = (int)(preg_replace('/[\.a-zA-Z]/', "", $ret['tld_sn'])) + 1;
				$vars['tld_sn'] = $row['short_desc'] . str_repeat("0", $row['length'] - strlen($val)) . $val;
			}
		}

        //Special Case for laptop
        if($row['short_desc'] === 'LAP'){
            $manufacturerSn = trim($vars['manufacturer_sn'] ?? '');
            $manufacturerSn = preg_replace('/[^A-Za-z0-9\-]/', '', $manufacturerSn);
            $year = date('y');
            $vars['tld_sn'] = sprintf(
                '%s%s-%s',
                strtoupper($row['short_desc']),
                $year,
                strtoupper($manufacturerSn)
            );
        }

        if($row['short_desc'] === 'SRV'){
            $vars['tld_sn'] = $_SESSION['sn'];
        }

        if ($vars['dt_warranty_end'] == '1969-12-31') {
            $DEFAULT_ERROR[] = "ERROR: Fail to add item, missing warranty end date!";
            break;
        }
        if (empty($vars['type_id'])) {
            $DEFAULT_ERROR[] = "ERROR: Fail to add item, missing item type!";
            break;
        }
        //Existing  Serial number Check
        $checkQuery = "SELECT COUNT(*) AS cnt FROM mis_inventory_items WHERE tld_sn = '{$vars['tld_sn']}'";
        $exists = tldUtils::getSqlRowToAssocArray($checkQuery);
        if ($exists['cnt'] > 0) {
            $DEFAULT_ERROR[] = "ERROR: Serial number already exists!";
            break;
        }

		$e = Tld_Mis_Inventory_Item::insert($vars);
		if (is_string($e)) {
			$DEFAULT_ERROR[] = "ERROR: Can not add new item. Reason: $e";
			break;
		}
		$body .= "<p>Item#$e successfully created! <a href=\"$php_self?m[0]=inventory&m[1]=view&id=$e\">Click here to see</a></p>";
		break;
    case 'addISP':
        $loca = strtoupper(substr(tldLocation::getLocationByID($user->getBUID()), 4,6));
        $stateList = Tld_Mis_Inventory_Item::getStateList('ISP');
        if ($user->isInGroup(['gg_MIS','ROLE_MISM','ROLE_CIO','superuser'])) {
            $body .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="$php_self?m[0]=inventory&m[1]=addSoftware">Switch to add Software Form</a><br>
EOF;
        }
        $DEFAULT_TITLE .= '\Create item';
        // Form
        $form = new HTML_QuickForm('frmNew', 'post');
        $form->addElement('hidden', 'm[0]', 'inventory');
        $form->addElement('hidden', 'm[1]', 'addISP');
        $form->addElement('header', 'title', "Create new item");
        include('form.inventory.item.php');
        $form->setDefaults(['buyer_bu_id' => $user->getBUID()]);

        if (!$form->validate()) {
            $body .= $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());

        if ($vars['type_group']['btnAdd']) {
            header("Location: http://{$_SERVER['HTTP_HOST']}/en/private/mis/mis.php?m[0]=inventory&m[1]=admin&m[2]=types&m[3]=add" );
        }
        if (empty($vars['dt_warranty_end']['warranty_date'])) {
            $vars['dt_warranty_end'] = date('Y-m-d', strtotime($vars['dt_warranty_end']['warranty_year']));
        }else{
            $vars['dt_warranty_end'] = $vars['dt_warranty_end']['warranty_date'];
        }
        if(!empty($vars['type_group']['type_id'])){
            $vars['type_id'] = $vars['type_group']['type_id'];
        }else{
            $vars['type_id'] = $vars['type_id'];
        }
        // create
        $body = '';

        $query = <<<EOF
    SELECT	short_desc, length
    FROM mis_inventory_item_types
    WHERE id = '{$vars['type_id']}'
EOF;

        $row = tldUtils::getSqlRowToAssocArray($query);

        $length = strlen($row['short_desc']) + $row['length'];

        $query1 = <<<EOF
SELECT	CONCAT('{$row['short_desc']}',max(RIGHT(tld_sn, {$row['length']}))) AS tld_sn
    FROM mis_inventory_items
    LEFT JOIN mis_inventory_item_types ON mis_inventory_item_types.id = mis_inventory_items.type_id
    WHERE tld_sn like "{$row['short_desc']}%" AND LENGTH(tld_sn)=$length AND RIGHT(tld_sn, {$row['length']}) not like '%[^0-9]%'
EOF;
        $ret = tldUtils::getSqlRowToAssocArray($query1);
        if (empty($ret || !is_numeric(str_replace($row['short_desc'], '', $ret['tld_sn'])))) {
            $vars['tld_sn'] = $row['short_desc'] . str_repeat("0", $row['length'] - 1) . '1';
        } else {
            if (preg_match('/[a-zA-Z]/', str_replace($row['short_desc'], '', $ret['tld_sn']))) {
                $vars['tld_sn'] = $row['short_desc'] . str_repeat("0", $row['length'] - 1) . '1';
            } else {
                $val = (int)(preg_replace('/[\.a-zA-Z]/', "", $ret['tld_sn'])) + 1;
                $vars['tld_sn'] = $row['short_desc'] . str_repeat("0", $row['length'] - strlen($val)) . $val;
            }
        }
        if ($vars['dt_warranty_end'] == '1969-12-31') {
            $DEFAULT_ERROR[] = "ERROR: Fail to add item, missing warranty end date!";
            break;
        }
        if (empty($vars['type_id'])) {
            $DEFAULT_ERROR[] = "ERROR: Fail to add item, missing item type!";
            break;
        }
        $e = Tld_Mis_Inventory_Item::insert($vars);
        if (is_string($e)) {
            $DEFAULT_ERROR[] = "ERROR: Can not add new item. Reason: $e";
            break;
        }
        $body .= "<p>Item#$e successfully created! <a href=\"$php_self?m[0]=inventory&m[1]=view&id=$e\">Click here to see</a></p>";
        break;
	case 'addSoftware':
		$stateList = Tld_Mis_Inventory_Item::getStateList('SOFTWARE');
		$body .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="$php_self?m[0]=inventory&m[1]=addHardware">Switch to add Hardware Form</a><br>
EOF;
		$DEFAULT_TITLE .= '\Create item';
		// Form
		$form = new HTML_QuickForm('frmNew', 'post');
		$form->addElement('hidden', 'm[0]', 'inventory');
		$form->addElement('hidden', 'm[1]', 'addSoftware');
		$form->addElement('header', 'title', "Create new item");
		include('form.inventory.item.php');
		$form->setDefaults(['buyer_bu_id' => $user->getBUID()]);
		$form->setDefaults(['state' => 'UNLIMITED']);
		$form->setDefaults(['qty' => '1']);
		if (!$form->validate()) {
			$body .= $form->toHTML();
			break;
		}

		$vars = tldUtils::cleanupFormInput($form->exportValues());
		if ($vars['type_group']['btnAdd']) {
			header("Location: http://{$_SERVER['HTTP_HOST']}/en/private/mis/mis.php?m[0]=inventory&m[1]=admin&m[2]=types&m[3]=add" );
		}
		if (empty($vars['dt_warranty_end']['warranty_date'])) {
			$vars['dt_warranty_end'] = date('Y-m-d', strtotime($vars['dt_warranty_end']['warranty_year']));
		}else{
			$vars['dt_warranty_end'] = $vars['dt_warranty_end']['warranty_date'];
		}
		$vars['type_id'] = $vars['type_group']['type_id'];
		// create
		$body = '';

		$query = <<<EOF
    SELECT	short_desc, length
    FROM mis_inventory_item_types
    WHERE id = '{$vars['type_group']['type_id']}'
EOF;

		$row = tldUtils::getSqlRowToAssocArray($query);

		$length = strlen($row['short_desc']) + $row['length'];

		$query1 = <<<EOF
    SELECT item.tld_sn
    from tld.mis_inventory_items as item
    where item.id=
    (SELECT	max(mis_inventory_items.id)
    FROM mis_inventory_items
    LEFT JOIN mis_inventory_item_types ON mis_inventory_item_types.id = mis_inventory_items.type_id
    WHERE tld_sn like "{$row['short_desc']}%" AND LENGTH(tld_sn)=$length)
EOF;

		$ret = tldUtils::getSqlRowToAssocArray($query1);
		if (empty($ret || !is_numeric(str_replace($row['short_desc'], '', $ret['tld_sn'])))) {
			$vars['tld_sn'] = $row['short_desc'] . str_repeat("0", $row['length'] - 1) . '1';
		} else {
			if (preg_match('/[a-zA-Z]/', str_replace($row['short_desc'], '', $ret['tld_sn']))) {
				$vars['tld_sn'] = $row['short_desc'] . str_repeat("0", $row['length'] - 1) . '1';
			} else {
				$val = (int)(preg_replace('/[\.a-zA-Z]/', "", $ret['tld_sn'])) + 1;
				$vars['tld_sn'] = $row['short_desc'] . str_repeat("0", $row['length'] - strlen($val)) . $val;
			}
		}
        if ($vars['dt_warranty_end'] == '1969-12-31') {
            $vars['dt_warranty_end'] = '0000-00-00';
        }
        if (empty($vars['type_id'])) {
            $DEFAULT_ERROR[] = "ERROR:  Fail to add item, missing item type!";
            break;
        }
		$e = Tld_Mis_Inventory_Item::insert($vars);
		if (is_string($e)) {
			$DEFAULT_ERROR[] = "ERROR: Can not add new item. Reason: $e";
			break;
		}
		$body .= "<p>Item#$e successfully created! <a href=\"$php_self?m[0]=inventory&m[1]=view&id=$e\">Click here to see</a></p>";
		break;
    case 'search':
        if (!$user->isInGroup(["gg_MIS","GG_MISINV_tabletManager"])) {
            $DEFAULT_ERROR[] = "ERROR: You do not have permissions...";
            break;
        }
        $DEFAULT_TITLE .= "\Advanced search";

        // Get form
        $form = new HTML_QuickForm('frmSearch', 'get', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'inventory');
        $form->addElement('hidden', 'm[1]', 'search');
        $form->addElement('header', 'title', "Search MIS inventory");
        $form->addElement('text', 'description', 'Description keyword');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addElement('reset', 'btnReset', 'Reset');
        // Set Required
        $form->addRule('description', 'Required', 'required');
        if (!$form->validate()) {
            $body .= $form->toHTML();
        }
        $a = tldUtils::cleanupFormInput($form->exportValues());
        if (empty($a)) {
            $DEFAULT_ERROR[] = "ERROR: Not enough constraints to run a safe search...";
            break;
        }
        $rows = Tld_Mis_Inventory_Module::search($a['description']);

        $_TITLE = "Search results";
        if ([] === $rows) {
            $DEFAULT_ERROR[] = "ERROR: No matches found...";
            break;
        }
        $form = new tldReportColumnar(
            $rows,
            [
                "xItems" => $xItems,
                "title" => $_title,
                "links" => ["id" => "$php_self?m[0]=inventory&m[1]=view&id="],
            ]
        );
        $body .= $form->fetch();
        break;
	case 'advSearch':
		if (!$user->isInGroup(["gg_MIS","GG_MISINV_tabletManager"])) {
			$DEFAULT_ERROR[] = "ERROR: You do not have permissions...";
			break;
		}

        switch($m[2] ?? []) {
            case 'csv':
                $report = new tldCSV(
                    $sess['inv']['listing'],
                    [
                        "xItems" => $xItems,
                        "showTitles" => true
                    ]
                );
                $report->out();
                exit;
                break;
        }

		$DEFAULT_TITLE .= "\Advanced search";
		// Get listing
		$locations = tldLocation::getLocationList('smartyOptions');
		$people = [];
		$people_list = tldDirectory::byConstraints("lastname<>'' AND firstname<>''");
		foreach ($people_list AS &$p) {
			if ($p['hidden']) {
				$p['fullname'] .= " [HIDDEN]";
			}
			$people[$p['id']] = $p['fullname'];
		}
		$tpls = tldUtils::optionsByKeyValue(tldSEQTpl::getSEQTplList(), "id", "name");
		$steps = ["1" => "1", "2" => "2", "3" => "3", "4" => "4", "5" => "5"];
		$status = array_combine(tldTask::getStatusList(), tldTask::getStatusList());
		$modules = tldUtils::optionsByKeyValue(tldModule::getList(), "module", "dsc");
		$divisions = tldRegion::getListAsIdDivision();
		$typeList = Tld_Mis_Inventory_ItemType::getListAsIdName();
		$brandList = Tld_Mis_Inventory_ItemBrand::getListAsIdName();
		$stateList = Tld_Mis_Inventory_Item::getStateList();
		$locationList = tldLocation::getLocationList("smartyOptions");
		$erpList = tldLocation::getERPList("smartyOptionsIDLocation");
		$dptList = tldDepartment::getListAsIdDepartment();
		$categoryList = Tld_Mis_Inventory_ItemType::getCategoryList();
		$yesNoList = ['0' => 'No', '1' => 'Yes', '2' => 'IN PROGRESS'];
		// Get form
		$form = new HTML_QuickForm('frmSearch', 'get', '', '', '', true);
		$form->addElement('hidden', 'm[0]', 'inventory');
		$form->addElement('hidden', 'm[1]', 'advSearch');
		$form->addElement('header', 'title', "Advanced search Tasks/Sequences");
		$form->addElement('select', 'destination_id', 'Assignee', ["" => ""] + $people);
		$form->addElement('select', 'location', 'Location', ["" => "", "people" => "People", "location" => "Location"]);
		$form->addElement('select', 'category', 'Category', ["" => ""] + $categoryList);
		$form->addElement('select', 'type_id', 'Type', ["" => ""] + $typeList);
		$form->addElement('select', 'brand_id', 'Brand', ["" => ""] + $brandList);
		$form->addElement('select', 'state', 'State', ["" => ""] + $stateList);
		$form->addElement('select', 'buyer_bu_id', 'Requestor BU', ["" => ""] + $erpList);
		$form->addElement('select', 'buyer_dpt_id', 'Requestor Department', ["" => ""] + $dptList);
		$form->addElement('select', 'hidden', 'Disposed?', ["" => ""] + $yesNoList);
		$form->addElement('text', 'description', 'Description keyword');
        $form->addElement('text', 'manufacturer_sn', 'Manufacturer SN');
		$form->addElement('header', 'title', "Misc");
		$form->addElement('text', 'dt_from', 'OPEN date from',
			["class" => "datepicker"]);
		$form->addElement('text', 'dt_to', 'OPEN date to',
			["class" => "datepicker"]);
		$form->addElement('text', 'dt_warranty_start', 'Warranty date from',
			["class" => "datepicker"]);
		$form->addElement('text', 'dt_warranty_end', 'Warranty date to',
			["class" => "datepicker"]);
		$form->addElement('submit', 'btnSubmit', 'Submit');
		$form->addElement('reset', 'btnReset', 'Reset');
		if (!$form->validate()) {
			$body .= $form->toHTML();
		}
		if ($user->isInGroup(["gg_MIS","GG_MISINV_tabletManager"])) {
			$acl_form_fields = [
				"destination_id", "location", "type_id", "brand_id", "state", "buyer_bu_id", "buyer_dpt_id", "hidden", "manufacturer_sn","description", "dt_from", "dt_to", "dt_warranty_start", "dt_warranty_end","category",
			];
		} else {
			$acl_form_fields = ["dt_from", "dt_to"];
		}

        $vars = $form->exportValues();
		$a = [];
		foreach ($vars as $key => $raw) {
			if (!in_array($key, $acl_form_fields) || $raw === '') {
                continue;
            }
			if (in_array($raw, ["%"])) {
                continue;
            }
			if (in_array($key, ["bu_id", "parent_id"])) {
				$key = "T1.$key";
			}
			// Construct constraint query
			switch ($key) {
				case "dt_from";
					try {
						$date = new DateTime($raw);
					} catch (Exception $e) {
						continue 2;
					}
					$date = $date->format('Y-m-d');
					$a[] = " DATEDIFF('$date',t1.dt)<=0 ";
					break;
				case "dt_to";
					try {
						$date = new DateTime($raw);
					} catch (Exception $e) {
						continue 2;
					}
					$date = $date->format('Y-m-d');
					$a[] = " DATEDIFF('$date',t1.dt)>=0 ";
					break;
				case "dt_warranty_start";
					try {
						$date = new DateTime($raw);
					} catch (Exception $e) {
						continue 2;
					}
					$date = $date->format('Y-m-d');
					$a[] = " DATEDIFF('$date',t1.dt_warranty_end)<=0 ";
					break;
				case "dt_warranty_end";
					try {
						$date = new DateTime($raw);
					} catch (Exception $e) {
						continue 2;
					}
					$date = $date->format('Y-m-d');
					$a[] = " DATEDIFF('$date',t1.dt_warranty_end)>=0 ";
					break;
				case "buyer_bu_id":
					$raw = TldDatabase::escape($raw);
					$a[] = " t1.buyer_bu_id=$raw ";
					break;
				case "description":
                case "manufacturer_sn":
					$raw = TldDatabase::escape($raw);
					$a[] = " $key LIKE '%$raw%' ";
					break;
				case "destination_id":
					$raw = TldDatabase::escape($raw);
					$a[] = " t4.destination_id =$raw  ";
					break;
				case "location":
					$raw = TldDatabase::escape($raw);
					$a[] = " t5.name LIKE '%$raw%'  ";
					break;
				case "category":
					$raw = TldDatabase::escape($raw);
					$a[] = " t2.category LIKE '%$raw%'  ";
					break;
				case "hidden":
					$raw = TldDatabase::escape($raw);
					$a[] = " t1.hidden =$raw  ";
					break;
				default:
					$raw = TldDatabase::escape($raw);
					if (is_numeric($raw)) {
						$a[] = " $key=$raw ";
					} elseif (is_string($raw)) {
						$a[] = " $key LIKE '$raw' ";
					}
					break;
			}
		}
		if (empty($a)) {
			$DEFAULT_ERROR[] = "ERROR: Not enough constraints to run a safe search...";
			break;
		}
		if(!$user->isInGroup(['superuser']) && $user->isInGroup(['GG_MISINV_tabletManager'])){
            $a[] = " t1.type_id = 10  ";
        }
		$constraints = implode("AND", $a);
		$rows = Tld_Mis_Inventory_Module::advSearch($constraints);

        $sess['inv']['listing'] = $rows;
        if (count($sess['inv']['listing']) && $user->isInGroup(['gg_MIS', 'GG_MISINV_tabletManager'])) {
            $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=inventory&m[1]=advSearch&m[2]=csv">Download CSV</a>
EOF;
        }


		$_TITLE = "Advanced Search results";
		if (count($rows) == 0) {
			$DEFAULT_ERROR[] = "ERROR: No matches found...";
			break;
		}

		$report = new tldReportColumnar(
			$rows,
			[
				"xItems" => $xItems,
				"title" => $_title,
				"links" => ["id" => "$php_self?m[0]=inventory&m[1]=view&id="],
			]
		);
		$body .= $report->fetch();
		break;
	case 'reports':
		switch ($m[2]) {
            case "TTSreport":
                switch ($m[3]) {
                    case 'csv':
                        if (!$user->isInGroup(["gg_ADMIN", "gg_MIS"])) {
                            $DEFAULT_ERROR[] = "ERROR: You do not have the permission to access this page";
                            break;
                        }

                        $report = new tldCSV(
                            $_SESSION['data_report'],
                            [
                                "xItems" => [
                                    "total" => "Quantity",
                                    "month" => "Date",
                                    "category" => "Category",
                                ],
                                "showTitles" => true,
                            ]
                        );
                        $report->out();
                        unset($_SESSION['data_report']);
                        exit;
                        break;
                }
                $addressList = tldGroup::getUserListByMultipleGroup(["gg_MIS"], "", ["smartyOptions" => true]);
                $username = " ";
                $form = new HTML_QuickForm('frmInventoryList', 'post');
                $form->addElement('header', 'title', "Closed TTS report");
                $form->addElement('hidden', 'm[0]', 'inventory');
                $form->addElement('hidden', 'm[1]', 'reports');
                $form->addElement('hidden', 'm[2]', 'TTSreport');
                $form->addElement('text', 'dt_from', 'Date from', ["class" => "datepicker"]);
                $form->addElement('text', 'dt_to', 'Date to', ["class" => "datepicker"]);
                $ams =& $form->addElement(
                    'advmultiselect', 'teammembers', null,
                    $addressList,
                    [
                        'size' => 10,
                        'class' => 'pool',
                        'style' => 'width:500px;'
                    ]
                );
                $ams->setLabel(['Select MIS member', 'Addressbook', 'CC']);
                $ams->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
                $ams->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->addRule('teammembers', 'Required', 'required');
                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break;
                }
                # If the form validates then freeze the data
                $form->freeze();
                $vars = tldUtils::cleanupFormInput($form->exportValues());
                foreach($vars['teammembers'] AS $key=>$val){
                    foreach($addressList AS $k=>$v){
                        if ($val == $k ){
                            $username .= $addressList[$k]. '  ';
                        }
                    }
                }
                $ITs = implode(",",$vars['teammembers']);
                $fields = [ 'dt_from', 'dt_to'];
                $a = [];
                foreach ($fields as $field) {
                    if (empty($vars[$field])) {
                        continue;
                    }
                    switch ($field) {
                        case 'dt_from':
                            $a[] = " DATEDIFF('{$vars[$field]}',date)<=0 ";
                            break;
                        case 'dt_to':
                            $a[] = " DATEDIFF('{$vars[$field]}',date)>=0 ";
                            break;
                        default:
                            $a[] = " $field={$vars[$field]} ";
                            break;
                    }
                }
                $where = implode(' AND ',$a);
                $query =<<<SQL
                SELECT count(*) AS total,LEFT (a.dt_closed,7) AS month, a.parent_id,mis_tts.category FROM `tasks` AS a
                LEFT JOIN mis_tts ON mis_tts.id=a.parent_id AND a.module LIKE 'TTS'
                WHERE a.id in
                ( SELECT DISTINCT(tasks.id) FROM `tasks` LEFT JOIN `tasks_comments` ON tasks.id = tasks_comments.parent_id
                WHERE tasks.module LIKE 'TTS' AND a.dt_closed<>'0000-00-00' AND tasks_comments.poster in ($ITs))
                AND $where
                GROUP BY MONTH,module,mis_tts.category
SQL;
                $rows = tldUtils::getSqlToAssocArray($query);
                $_title = "Count of TTS by date by IT team ".$username;
                if (count($rows) == 0) {
                    $DEFAULT_ERROR[] = "ERROR: No matches found...";
                    break;
                }
                $form = new tldReportColumnar(
                    $rows,
                    [
                        "xItems" => [
                                "total" => "Quantity",
                                "month" => "Date",
                                "category" => "Category",

                            ],
                        "title" => $_title,
                        "links" => "",
                    ]
                );
                $_SESSION['data_report'] = $rows;
                if ($user->isInGroup(["gg_mis"])) {
                    $DEFAULT_MENU .= <<<EOF
    		<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="$php_self?m[0]=inventory&m[1]=reports&m[2]=TTSreport&m[3]=csv">Download to CSV</a>
EOF;
                }

                $body .= $form->fetch();

                break;
            case "activeItemByBU":
                if(!$user->isInGroup(['gg_MIS','ROLE_MISM','ROLE_CIO','superuser'])){
                    $DEFAULT_ERROR[] = "ERROR: You do not have access to this report";
                    break;
                }
                switch ($m[3]) {
                    case 'csv':
                        if (!$user->isInGroup(["gg_ADMIN", "gg_MIS"])) {
                            $DEFAULT_ERROR[] = "ERROR: You do not have the permission to access this page";
                            break;
                        }

                        $report = new tldCSV(
                            $_SESSION['data_report'],
                            [
                                "xItems" => [
                                    "id" => "Item#",
                                    "dt" => "Date",
                                    "type_name" => "Type",
                                    "brand_name" => "Brand",
                                    "model" => "Model",
                                    "manufacturer_sn" => "Manufacturer SN",
                                    "tld_sn" => "TLD SN",
                                    "fixasset_id" => 'Fix Asset ID',
                                    "state" => "State",
                                    "description" => "Description",
                                    "dt_warranty_end" => "End of warranty",
                                    "buyer_location" => "Requestor BU",
                                    "buyer_dpt" => "Requestor Department",
                                    "destination" => "Destination",
                                    "hidden_status" => "Disposed?",
                                ],
                                "showTitles" => true,
                            ]
                        );
                        $report->out();
                        unset($_SESSION['data_report']);
                        exit;
                        break;
                }
                // Listing
                $BU_List = tldLocation::getERPList('smartyOptionsIDLocation');
                $stateList = Tld_Mis_Inventory_Item::getStateList();
                $dptList = tldDepartment::getListAsIdDepartment();
                // Get form
                $form = new HTML_QuickForm('frmInventoryList', 'post');
                $form->addElement('header', 'title', "List of active items by BU");
                $form->addElement('hidden', 'm[0]', 'inventory');
                $form->addElement('hidden', 'm[1]', 'reports');
                $form->addElement('hidden', 'm[2]', 'activeItemByBU');
                $form->addElement('select', 'buyer_bu_id', 'Requestor BU', ["" => ""] + $BU_List);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                // Set Required
                $form->addRule('buyer_bu_id', 'Required', 'required');
                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break;
                }
                # If the form validates then freeze the data
                $form->freeze();
                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $bu = tldLocation::getLocationByERP(tldLocation::getERPByID($vars['buyer_bu_id']));
                if (!empty($bu)) {
                    $title = "List of active items by BU - Company $bu";
                }
                $dept = $vars['buyer_dpt_id'];
                $data = Tld_Mis_Inventory_Item::byConstraints("destination_type = 'PEOPLE' AND destination_id !='' AND hidden =0 AND buyer_bu_id={$vars['buyer_bu_id']} ");
                $_SESSION['data_report'] = $data;
                $report = new tldReportColumnar(
                    $data,
                    [
                        "xItems" => [
                            "id" => "Item#",
                            "dt" => "Date",
                            "type_name" => "Type",
                            "brand_name" => "Brand",
                            "model" => "Model",
                            "manufacturer_sn" => "Manufacturer SN",
                            "tld_sn" => "TLD SN",
                            "fixasset_id" => 'Fix Asset ID',
                            "state" => "State",
                            "description" => "Description",
                            "dt_warranty_end" => "End of warranty",
                            "buyer_location" => "Requestor BU",
                            "buyer_dpt" => "Requestor Department",
                            "destination" => "Destination",
                            "hidden_status" => "Disposed?",
                        ],
                        "title" => $title,
                        "links" => ["id" => "$php_self?m[0]=inventory&m[1]=view&id="],
                    ]
                );
                if ($user->isInGroup(["gg_mis", "gg_ACCT"])) {
                    $DEFAULT_MENU .= <<<EOF
    		<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="$php_self?m[0]=inventory&m[1]=reports&m[2]=activeItemByBU&m[3]=csv">Download to CSV</a>
EOF;
                }
                $body .= $report->fetch();
                break;
            case "numberDaysDateToReached":
                if(!$user->isInGroup(['gg_MIS','ROLE_MISM','ROLE_CIO','superuser'])){
                    $DEFAULT_ERROR[] = "ERROR: You do not have access to this report";
                    break;
                }
                switch ($m[3]) {
                    case 'csv':
                        if (!$user->isInGroup(["gg_ADMIN", "gg_MIS"])) {
                            $DEFAULT_ERROR[] = "ERROR: You do not have the permission to access this page";
                            break;
                        }

                        $report = new tldCSV(
                            $_SESSION['datareport'],
                            [
                                "xItems" => [
                                    "id" => "Item#",
                                    "dt" => "Date",
                                    "type_name" => "Type",
                                    "brand_name" => "Brand",
                                    "model" => "Model",
                                    "manufacturer_sn" => "Manufacturer SN",
                                    "tld_sn" => "TLD SN",
                                    "fixasset_id" => 'Fix Asset ID',
                                    "state" => "State",
                                    "description" => "Description",
                                    "dt_warranty_end" => "End of warranty",
                                    "buyer_location" => "Requestor BU",
                                    "buyer_dpt" => "Requestor Department",
                                    "destination" => "Destination",
                                    "hidden_status" => "Disposed?",
                                    "dt_to"=>"Date_to",
                                ],
                                "showTitles" => true,
                            ]
                        );
                        $report->out();
                        unset($_SESSION['datareport']);
                        exit;
                        break;
                }
                // Listing
                $BU_List = tldLocation::getERPList('smartyOptionsIDLocation');
                $stateList = Tld_Mis_Inventory_Item::getStateList();
                $dptList = tldDepartment::getListAsIdDepartment();
                // Get form
                $form = new HTML_QuickForm('frmInventoryList', 'post');
                $form->addElement('header', 'title', "List of items date_to is reached");
                $form->addElement('hidden', 'm[0]', 'inventory');
                $form->addElement('hidden', 'm[1]', 'reports');
                $form->addElement('hidden', 'm[2]', 'numberDaysDateToReached');
                $form->addElement('select', 'buyer_bu_id', 'Requestor BU', ["" => ""] + $BU_List);
                $form->addElement('text', 'days', 'Number of days before the date_to is reached');
                $form->addElement('submit', 'btnSubmit', 'Submit');
                // Set Required
                $form->addRule('days', 'Required', 'required');
                $form->addRule('days', 'numerical value only','numeric');
                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break;
                }
                # If the form validates then freeze the data
                $form->freeze();
                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $bu = tldLocation::getLocationByERP(tldLocation::getERPByID($vars['buyer_bu_id']));
                if (!empty($bu)) {
                    $title = "{$vars['days']} of days before the date_to is reached - Company $bu";
                }
                $dept = $vars['buyer_dpt_id'];
                $data = Tld_Mis_Inventory_Item::getDevicesBeforeDateTo($vars['buyer_bu_id'],$vars['days']);
                $_SESSION['datareport'] = $data;
                $report = new tldReportColumnar(
                    $data,
                    [
                        "xItems" => [
                            "id" => "Item#",
                            "dt" => "Date",
                            "type_name" => "Type",
                            "brand_name" => "Brand",
                            "model" => "Model",
                            "manufacturer_sn" => "Manufacturer SN",
                            "tld_sn" => "TLD SN",
                            "fixasset_id" => 'Fix Asset ID',
                            "state" => "State",
                            "description" => "Description",
                            "dt_warranty_end" => "End of warranty",
                            "buyer_location" => "Requestor BU",
                            "buyer_dpt" => "Requestor Department",
                            "destination" => "Destination",
                            "hidden_status" => "Disposed?",
                            "dt_to"=>"Date_to",
                        ],
                        "title" => $title,
                        "links" => ["id" => "$php_self?m[0]=inventory&m[1]=view&id="],
                    ]
                );
                if ($user->isInGroup(["gg_mis", "gg_ACCT"])) {
                    $DEFAULT_MENU .= <<<EOF
    		<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="$php_self?m[0]=inventory&m[1]=reports&m[2]=numberDaysDateToReached&m[3]=csv">Download to CSV</a>
EOF;
                }
                $body .= $report->fetch();
            break;
			case "itemWithOpenTask":
                if(!$user->isInGroup(['gg_MIS','ROLE_MISM','ROLE_CIO','superuser'])){
                    $DEFAULT_ERROR[] = "ERROR: You do not have access to this report";
                    break;
                }
                switch ($m[3]) {
                    case 'csv':
                        if (!$user->isInGroup(["gg_ADMIN", "gg_MIS"])) {
                            $DEFAULT_ERROR[] = "ERROR: You do not have the permission to access this page";
                            break;
                        }

                        $report = new tldCSV(
                            $_SESSION['data_report'],
                            [
                                "xItems" => [
                                    "id" => "Item#",
                                    "dt" => "Date",
                                    "type_name" => "Type",
                                    "brand_name" => "Brand",
                                    "model" => "Model",
                                    "manufacturer_sn" => "Manufacturer SN",
                                    "tld_sn" => "TLD SN",
                                    "fixasset_id" => 'Fix Asset ID',
                                    "state" => "State",
                                    "description" => "Description",
                                    "dt_warranty_end" => "End of warranty",
                                    "buyer_location" => "Requestor BU",
                                    "buyer_dpt" => "Requestor Department",
                                    "destination" => "Destination",
                                    "hidden_status" => "Disposed?",
                                ],
                                "showTitles" => true,
                            ]
                        );
                        $report->out();
                        unset($_SESSION['data_report']);
                        exit;
                        break;
                }
                // Listing
                $BU_List = tldLocation::getERPList('smartyOptionsIDLocation');
                $stateList = Tld_Mis_Inventory_Item::getStateList();
                $dptList = tldDepartment::getListAsIdDepartment();
                // Get form
                $form = new HTML_QuickForm('frmInventoryList', 'post');
                $form->addElement('header', 'title', "List of item with open task");
                $form->addElement('hidden', 'm[0]', 'inventory');
                $form->addElement('hidden', 'm[1]', 'reports');
                $form->addElement('hidden', 'm[2]', 'itemWithOpenTask');
                $form->addElement('select', 'buyer_bu_id', 'Requestor BU', ["" => ""] + $BU_List);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                // Set Required
                $form->addRule('buyer_bu_id', 'Required', 'required');

                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break;
                }
                # If the form validates then freeze the data
                $form->freeze();
                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $bu = tldLocation::getLocationByERP(tldLocation::getERPByID($vars['buyer_bu_id']));
                if (!empty($bu)) {
                    $title = "Items with open task - Company $bu";
                }
                $dept = $vars['buyer_dpt_id'];
                $data = Tld_Mis_Inventory_Item::getDevicesWithOpenTask($vars['buyer_bu_id']);
                $_SESSION['data_report'] = $data;
                $report = new tldReportColumnar(
                    $data,
                    [
                        "xItems" => [
                            "id" => "Item#",
                            "dt" => "Date",
                            "type_name" => "Type",
                            "brand_name" => "Brand",
                            "model" => "Model",
                            "manufacturer_sn" => "Manufacturer SN",
                            "tld_sn" => "TLD SN",
                            "fixasset_id" => 'Fix Asset ID',
                            "state" => "State",
                            "description" => "Description",
                            "dt_warranty_end" => "End of warranty",
                            "buyer_location" => "Requestor BU",
                            "buyer_dpt" => "Requestor Department",
                            "destination" => "Destination",
                            "hidden_status" => "Disposed?",
                        ],
                        "title" => $title,
                        "links" => ["id" => "$php_self?m[0]=inventory&m[1]=view&id="],
                    ]
                );
                if ($user->isInGroup(["gg_mis", "gg_ACCT"])) {
                    $DEFAULT_MENU .= <<<EOF
    		<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="$php_self?m[0]=inventory&m[1]=reports&m[2]=itemWithOpenTask&m[3]=csv">Download to CSV</a>
EOF;
                }
                $body .= $report->fetch();
                break;
            case "itemOutOfWarrantyByBU":
                if(!$user->isInGroup(['gg_MIS','ROLE_MISM','ROLE_CIO','superuser'])){
                    $DEFAULT_ERROR[] = "ERROR: You do not have access to this report";
                    break;
                }
                switch ($m[3]) {
                    case 'csv':
                        if (!$user->isInGroup(["gg_ADMIN", "gg_MIS"])) {
                            $DEFAULT_ERROR[] = "ERROR: You do not have the permission to access this page";
                            break;
                        }

                        $report = new tldCSV(
                            $_SESSION['data_report'],
                            [
                                "xItems" => [
                                    "id" => "Item#",
                                    "dt" => "Date",
                                    "type_name" => "Type",
                                    "brand_name" => "Brand",
                                    "model" => "Model",
                                    "manufacturer_sn" => "Manufacturer SN",
                                    "tld_sn" => "TLD SN",
                                    "fixasset_id" => 'Fix Asset ID',
                                    "state" => "State",
                                    "description" => "Description",
                                    "dt_warranty_end" => "End of warranty",
                                    "buyer_location" => "Requestor BU",
                                    "buyer_dpt" => "Requestor Department",
                                    "destination" => "Destination",
                                    "hidden_status" => "Disposed?",
                                ],
                                "showTitles" => true,
                            ]
                        );
                        $report->out();
                        unset($_SESSION['data_report']);
                        exit;
                        break;
                }
                // Listing

                $BU_List = tldLocation::getERPList('smartyOptionsIDLocation');
                $stateList = Tld_Mis_Inventory_Item::getStateList();
                $dptList = tldDepartment::getListAsIdDepartment();
                // Get form
                $form = new HTML_QuickForm('frmInventoryList', 'post');
                $form->addElement('header', 'title', "List of item out of warranty by BU by status");
                $form->addElement('hidden', 'm[0]', 'inventory');
                $form->addElement('hidden', 'm[1]', 'reports');
                $form->addElement('hidden', 'm[2]', 'itemOutOfWarrantyByBU');
                $form->addElement('select', 'buyer_bu_id', 'Requestor BU', ["" => ""] + $BU_List);
                $form->addElement('select', 'buyer_dpt_id', 'Requestor Department', ["" => ""] + $dptList);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                // Set Required
                $form->addRule('bu', 'Required', 'required');
                // Set Default
                $form->setDefaults([
                        "start" => date("Y") . "-01-01",
                        "end" => date("Y-m-d")]
                );
                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break;
                }
                # If the form validates then freeze the data
                $form->freeze();
                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $a = [];
                $acl_fields = ["buyer_bu_id", "buyer_dpt_id"];
                foreach ($vars as $k => $var) {
                    if (!in_array($k, $acl_fields) || empty($var)) {
                        continue;
                    }
                    $a[$k] = $var;
                }
                $bu = tldLocation::getLocationByERP(tldLocation::getERPByID($vars['buyer_bu_id']));
                if (!empty($bu)) {
                    $title = "Items out of warranty - Factory $bu";
                }
                $dept = $vars['buyer_dpt_id'];

                $data = Tld_Mis_Inventory_Item::getItemsOutOfWarranty($a);
                $_SESSION['data_report'] = $data;
                $report = new tldReportColumnar(
                    $data,
                    [
                        "xItems" => [
                            "id" => "Item#",
                            "dt" => "Date",
                            "type_name" => "Type",
                            "brand_name" => "Brand",
                            "model" => "Model",
                            "manufacturer_sn" => "Manufacturer SN",
                            "tld_sn" => "TLD SN",
                            "fixasset_id" => 'Fix Asset ID',
                            "state" => "State",
                            "description" => "Description",
                            "dt_warranty_end" => "End of warranty",
                            "buyer_location" => "Requestor BU",
                            "buyer_dpt" => "Requestor Department",
                            "destination" => "Destination",
                            "hidden_status" => "Disposed?",
                        ],
                        "title" => $title,
                        "links" => ["id" => "$php_self?m[0]=inventory&m[1]=view&id="],
                    ]
                );
                if ($user->isInGroup(["warranty", "gg_PARTS", "gg_SERVICE", "gg_SUPPORT", "gg_ADMIN", "gg_ACCT"])) {
                    $DEFAULT_MENU .= <<<EOF
    		<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="$php_self?m[0]=inventory&m[1]=reports&m[2]=itemOutOfWarrantyByBU&m[3]=csv">Download to CSV</a>
EOF;
                }
                $body .= $report->fetch();

                break;
            case "itemUnassigned":
                if(!$user->isInGroup(['gg_MIS','ROLE_MISM','ROLE_CIO','superuser'])){
                    $DEFAULT_ERROR[] = "ERROR: You do not have access to this report";
                    break;
                }
				switch ($m[3]) {
					case 'csv':
						if (!$user->isInGroup(["gg_ADMIN", "gg_MIS"])) {
							$DEFAULT_ERROR[] = "ERROR: You do not have the permission to access this page";
							break;
						}

						$report = new tldCSV(
							$_SESSION['data_report'],
							[
								"xItems" => [
									"id" => "Item#",
									"dt" => "Date",
									"type_name" => "Type",
									"brand_name" => "Brand",
									"model" => "Model",
									"manufacturer_sn" => "Manufacturer SN",
									"tld_sn" => "TLD SN",
									"fixasset_id" => 'Fix Asset ID',
									"state" => "State",
									"description" => "Description",
									"dt_warranty_end" => "End of warranty",
									"buyer_location" => "Requestor BU",
									"buyer_dpt" => "Requestor Department",
									"destination" => "Destination",
									"hidden_status" => "Disposed?",
								],
								"showTitles" => true,
							]
						);
						$report->out();
						unset($_SESSION['data_report']);
						exit;
						break;
				}
				// Listing
				$BU_List = tldLocation::getERPList('smartyOptionsIDLocation');
				$stateList = Tld_Mis_Inventory_Item::getStateList();
				$dptList = tldDepartment::getListAsIdDepartment();
				// Get form
				$form = new HTML_QuickForm('frmInventoryList', 'post');
				$form->addElement('header', 'title', "List of item unassigned");
				$form->addElement('hidden', 'm[0]', 'inventory');
				$form->addElement('hidden', 'm[1]', 'reports');
				$form->addElement('hidden', 'm[2]', 'itemUnassigned');
				$form->addElement('select', 'buyer_bu_id', 'Requestor BU', ["" => ""] + $BU_List);
				$form->addElement('submit', 'btnSubmit', 'Submit');
				// Set Required
				$form->addRule('buyer_bu_id', 'Required', 'required');

				if (!$form->validate()) {
					$body = $form->toHTML();
					break;
				}
				# If the form validates then freeze the data
				$form->freeze();
				$vars = tldUtils::cleanupFormInput($form->exportValues());
				$bu = tldLocation::getLocationByERP(tldLocation::getERPByID($vars['buyer_bu_id']));
				if (!empty($bu)) {
                    $title = "Unassigned item list - Company $bu";
                }
				$dept = $vars['buyer_dpt_id'];
				$data = Tld_Mis_Inventory_Item::getUnassignedDevices($vars['buyer_bu_id']);
				$_SESSION['data_report'] = $data;
				$report = new tldReportColumnar(
					$data,
					[
						"xItems" => [
							"id" => "Item#",
							"dt" => "Date",
							"type_name" => "Type",
							"brand_name" => "Brand",
							"model" => "Model",
							"manufacturer_sn" => "Manufacturer SN",
							"tld_sn" => "TLD SN",
							"fixasset_id" => 'Fix Asset ID',
							"state" => "State",
							"description" => "Description",
							"dt_warranty_end" => "End of warranty",
							"buyer_location" => "Requestor BU",
							"buyer_dpt" => "Requestor Department",
							"destination" => "Destination",
							"hidden_status" => "Disposed?",
						],
						"title" => $title,
						"links" => ["id" => "$php_self?m[0]=inventory&m[1]=view&id="],
					]
				);
				if ($user->isInGroup(["gg_mis", "gg_ACCT"])) {
					$DEFAULT_MENU .= <<<EOF
    		<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="$php_self?m[0]=inventory&m[1]=reports&m[2]=itemWithOpenTask&m[3]=csv">Download to CSV</a>
EOF;
				}
				$body .= $report->fetch();
				break;
            case "itemDisposedByBU":
                if(!$user->isInGroup(['GG_MISINV_fixedAssetManager','gg_MIS','ROLE_MISM','ROLE_CIO','superuser'])){
                    $DEFAULT_ERROR[] = "ERROR: You do not have access to this report";
                    break;
                }
                switch ($m[3]) {
                    case 'csv':
                        if (!$user->isInGroup(["gg_ADMIN", "gg_MIS","GG_MISINV_fixedAssetManager"])) {
                            $DEFAULT_ERROR[] = "ERROR: You do not have the permission to access this page";
                            break;
                        }

                        $report = new tldCSV(
                            $_SESSION['data_report'],
                            [
                                "xItems" => [
                                    "id" => "Item#",
                                    "dt" => "Date",
                                    "type_name" => "Type",
                                    "brand_name" => "Brand",
                                    "model" => "Model",
                                    "manufacturer_sn" => "Manufacturer SN",
                                    "tld_sn" => "TLD SN",
                                    "fixasset_id" => 'Fix Asset ID',
                                    "state" => "State",
                                    "description" => "Description",
                                    "dt_warranty_end" => "End of warranty",
                                    "buyer_location" => "Requestor BU",
                                    "buyer_dpt" => "Requestor Department",
                                    "destination" => "Destination",
                                    "hidden_status" => "Disposed?",
                                ],
                                "showTitles" => true,
                            ]
                        );
                        $report->out();
                        unset($_SESSION['data_report']);
                        exit;
                        break;
                }
                // Listing

                $BU_List = tldLocation::getERPList('smartyOptionsIDLocation');
                $stateList = Tld_Mis_Inventory_Item::getStateList();
                $dptList = tldDepartment::getListAsIdDepartment();
                // Get form
                $form = new HTML_QuickForm('frmInventoryList', 'post');
                $form->addElement('header', 'title', "List of item out of warranty by BU by status");
                $form->addElement('hidden', 'm[0]', 'inventory');
                $form->addElement('hidden', 'm[1]', 'reports');
                $form->addElement('hidden', 'm[2]', 'itemDisposedByBU');
                $form->addElement('select', 'buyer_bu_id', 'Requestor BU', ["" => ""] + $BU_List);
                $form->addElement('select', 'buyer_dpt_id', 'Requestor Department', ["" => ""] + $dptList);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                // Set Required
                $form->addRule('bu', 'Required', 'required');

                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break;
                }
                # If the form validates then freeze the data
                $form->freeze();
                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $a = [];
                $acl_fields = ["buyer_bu_id", "buyer_dpt_id"];
                foreach ($vars as $k => $var) {
                    if (!in_array($k, $acl_fields) || empty($var)) {
                        continue;
                    }
                    $a[$k] = $var;
                }
                $bu = tldLocation::getLocationByERP(tldLocation::getERPByID($vars['buyer_bu_id']));
                if (!empty($bu)) {
                    $title = "Items disposed - Factory $bu";
                }
                $dept = $vars['buyer_dpt_id'];

                $data = Tld_Mis_Inventory_Item::getItemsDisposed($a);
                $_SESSION['data_report'] = $data;
                $report = new tldReportColumnar(
                    $data,
                    [
                        "xItems" => [
                            "id" => "Item#",
                            "dt" => "Date",
                            "type_name" => "Type",
                            "brand_name" => "Brand",
                            "model" => "Model",
                            "manufacturer_sn" => "Manufacturer SN",
                            "tld_sn" => "TLD SN",
                            "fixasset_id" => 'Fix Asset ID',
                            "state" => "State",
                            "description" => "Description",
                            "dt_warranty_end" => "End of warranty",
                            "buyer_location" => "Requestor BU",
                            "buyer_dpt" => "Requestor Department",
                            "destination" => "Destination",
                            "hidden_status" => "Disposed?",
                        ],
                        "title" => $title,
                        "links" => ["id" => "$php_self?m[0]=inventory&m[1]=view&id="],
                    ]
                );
                if ($user->isInGroup(["warranty", "gg_PARTS", "gg_SERVICE", "gg_SUPPORT", "gg_ADMIN", "gg_ACCT"])) {
                    $DEFAULT_MENU .= <<<EOF
    		<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="$php_self?m[0]=inventory&m[1]=reports&m[2]=itemOutOfWarrantyByBU&m[3]=csv">Download to CSV</a>
EOF;
                }
                $body .= $report->fetch();

                break;
            case 'assignedActiveComputerByBU':
                if(!$user->isInGroup(['GG_MISINV_tabletManager','gg_MIS','ROLE_MISM','ROLE_CIO','superuser'])){
                    $DEFAULT_ERROR[] = "ERROR: You do not have access to this report";
                    break;
                }
                $BU_List = tldLocation::getERPList('smartyOptionsIDLocation');
                // Get form
                $form = new HTML_QuickForm('frmInventoryList', 'post');
                $form->addElement('header', 'title', "List assigned computers by BU");
                $form->addElement('hidden', 'm[0]', 'inventory');
                $form->addElement('hidden', 'm[1]', 'reports');
                $form->addElement('hidden', 'm[2]', 'assignedActiveComputerByBU');
                $form->addElement('select', 'bu', 'Requestor BU', ["" => ""] + $BU_List);

                $form->addElement('submit', 'btnSubmit', 'Submit');
                // Set Required
                $form->addRule('bu', 'Required', 'required');
                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break;
                }
                # If the form validates then freeze the data
                $form->freeze();
                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $bu = $vars['bu'];
                $erp = tldLocation::getLocationByERP(tldLocation::getERPByID($bu));
                if (!empty($bu)) {
                    $bu_title = "Assigned active computers list in $erp";
                }
                $data = Tld_Mis_Inventory_Item::getAssignedActiveComputersByBU($bu);
                $_SESSION['data_report'] = $data;
                $report = new tldReportColumnar(
                    $data,
                    [
                        "xItems" => [
                            "id" => "Item#",
                            "dt" => "Date",
                            "type_name" => "Type",
                            "brand_name" => "Brand",
                            "model" => "Model",
                            "manufacturer_sn" => "Manufacturer SN",
                            "tld_sn" => "TLD SN",
                            "fixasset_id" => 'Fix Asset ID',
                            "state" => "State",
                            "description" => "Description",
                            "dt_warranty_end" => "End of warranty",
                            "buyer_location" => "Requestor BU",
                            "buyer_dpt" => "Requestor Department",
                            "destination" => "Destination",
                            "hidden_status" => "Disposed?",
                        ],
                        "title" => $bu_title,
                        "links" => ["id" => "$php_self?m[0]=inventory&m[1]=view&id="],
                    ]
                );
                if ($user->isInGroup(["warranty", "gg_PARTS", "gg_SERVICE", "gg_SUPPORT", "gg_ADMIN", "gg_ACCT"])) {
                    $DEFAULT_MENU .= <<<EOF
    		<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="$php_self?m[0]=inventory&m[1]=reports&m[2]=itemOutOfWarrantyByBU&m[3]=csv">Download to CSV</a>
EOF;
                }
                $body .= $report->fetch();
            break;
            case 'listLicenseByBU':
                if(!$user->isInGroup(['GG_MISINV_tabletManager','gg_MIS','ROLE_MISM','ROLE_CIO','superuser'])){
                    $DEFAULT_ERROR[] = "ERROR: You do not have access to this report";
                    break;
                }
                $BU_List = tldLocation::getERPList('smartyOptionsIDLocation');
                // Get form
                $form = new HTML_QuickForm('frmInventoryList', 'post');
                $form->addElement('header', 'title', "List license by BU");
                $form->addElement('hidden', 'm[0]', 'inventory');
                $form->addElement('hidden', 'm[1]', 'reports');
                $form->addElement('hidden', 'm[2]', 'listLicenseByBU');
                $form->addElement('select', 'bu', 'Requestor BU', ["" => ""] + $BU_List);

                $form->addElement('submit', 'btnSubmit', 'Submit');
                // Set Required
                $form->addRule('bu', 'Required', 'required');
                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break;
                }
                # If the form validates then freeze the data
                $form->freeze();
                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $bu = $vars['bu'];
                $erp = tldLocation::getLocationByERP(tldLocation::getERPByID($bu));
                if (!empty($bu)) {
                    $bu_title = "List license by $erp";
                }
                $data = Tld_Mis_Inventory_Item::getLicenseByBU($bu);
                $_SESSION['data_report'] = $data;
                $report = new tldReportColumnar(
                    $data,
                    [
                        "xItems" => [
                            "id" => "Item#",
                            "dt" => "Date",
                            "type_name" => "Type",
                            "brand_name" => "Brand",
                            "model" => "Model",
                            "manufacturer_sn" => "Manufacturer SN",
                            "tld_sn" => "TLD SN",
                            "fixasset_id" => 'Fix Asset ID',
                            "state" => "State",
                            "description" => "Description",
                            "dt_warranty_end" => "End of warranty",
                            "buyer_location" => "Requestor BU",
                            "buyer_dpt" => "Requestor Department",
                            "destination" => "Destination",
                            "hidden_status" => "Disposed?",
                        ],
                        "title" => $bu_title,
                        "links" => ["id" => "$php_self?m[0]=inventory&m[1]=view&id="],
                    ]
                );
                if ($user->isInGroup(["warranty", "gg_PARTS", "gg_SERVICE", "gg_SUPPORT", "gg_ADMIN", "gg_ACCT"])) {
                    $DEFAULT_MENU .= <<<EOF
    		<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="$php_self?m[0]=inventory&m[1]=reports&m[2]=itemOutOfWarrantyByBU&m[3]=csv">Download to CSV</a>
EOF;
                }
                $body .= $report->fetch();
            break;
			case 'tabletsByBU':
                if(!$user->isInGroup(['GG_MISINV_tabletManager','gg_MIS','ROLE_MISM','ROLE_CIO','superuser'])){
                    $DEFAULT_ERROR[] = "ERROR: You do not have access to this report";
                    break;
                }
				$BU_List = tldLocation::getERPList('smartyOptionsIDLocation');
				$status = ["" => "", "ASSIGNED" => "ASSIGNED", "UNASSIGNED" => "UNASSIGNED"];
				$stateList = Tld_Mis_Inventory_Item::getStateList();
				$dptList = tldDepartment::getListAsIdDepartment();
				// Get form
				$form = new HTML_QuickForm('frmInventoryList', 'post');
				$form->addElement('header', 'title', "List of tablets by BU");
				$form->addElement('hidden', 'm[0]', 'inventory');
				$form->addElement('hidden', 'm[1]', 'reports');
				$form->addElement('hidden', 'm[2]', 'tabletsByBU');
				$form->addElement('select', 'bu', 'Requestor BU', ["" => ""] + $BU_List);

				$form->addElement('submit', 'btnSubmit', 'Submit');
				// Set Required
				$form->addRule('bu', 'Required', 'required');
				if (!$form->validate()) {
					$body = $form->toHTML();
					break;
				}
				# If the form validates then freeze the data
				$form->freeze();
				$vars = tldUtils::cleanupFormInput($form->exportValues());
				$bu = $vars['bu'];
				$erp = tldLocation::getLocationByERP(tldLocation::getERPByID($bu));
				if (!empty($bu)) {
                    $bu_title = "Tablets list in $erp";
                }
				$data = Tld_Mis_Inventory_Item::getTabletsByBU($bu);
				$_SESSION['data_report'] = $data;
				$report = new tldReportColumnar(
					$data,
					[
						"xItems" => [
							"id" => "Item#",
							"dt" => "Date",
							"type_name" => "Type",
							"brand_name" => "Brand",
							"model" => "Model",
							"manufacturer_sn" => "Manufacturer SN",
							"tld_sn" => "TLD SN",
							"fixasset_id" => 'Fix Asset ID',
							"state" => "State",
							"description" => "Description",
							"dt_warranty_end" => "End of warranty",
							"buyer_location" => "Requestor BU",
							"buyer_dpt" => "Requestor Department",
							"destination" => "Destination",
							"hidden_status" => "Disposed?",
						],
						"title" => $bu_title,
						"links" => ["id" => "$php_self?m[0]=inventory&m[1]=view&id="],
					]
				);
				if ($user->isInGroup(["warranty", "gg_PARTS", "gg_SERVICE", "gg_SUPPORT", "gg_ADMIN", "gg_ACCT"])) {
					$DEFAULT_MENU .= <<<EOF
    		<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="$php_self?m[0]=inventory&m[1]=reports&m[2]=itemOutOfWarrantyByBU&m[3]=csv">Download to CSV</a>
EOF;
				}
				$body .= $report->fetch();
				break;
			case 'warrantyEnd':
                if(!$user->isInGroup(['gg_MIS','ROLE_MISM','ROLE_CIO','superuser'])){
                    $DEFAULT_ERROR[] = "ERROR: You do not have access to this report";
                    break;
                }
				$BU_List = tldLocation::getERPList('smartyOptionsIDLocation');
				$status = ["" => "", "ASSIGNED" => "ASSIGNED", "UNASSIGNED" => "UNASSIGNED"];
				$stateList = Tld_Mis_Inventory_Item::getStateList();
				$dptList = tldDepartment::getListAsIdDepartment();
				// Get form
				$form = new HTML_QuickForm('frmInventoryList', 'post');
				$form->addElement('header', 'title', "List of tablets by BU");
				$form->addElement('hidden', 'm[0]', 'inventory');
				$form->addElement('hidden', 'm[1]', 'reports');
				$form->addElement('hidden', 'm[2]', 'warrantyEnd');
				$form->addElement('select', 'bu', 'Requestor BU', ["" => ""] + $BU_List);
				$groupWarrantytime[] = &$form->createElement('text', 'warranty_end_value', 'Warranty End in');
				$groupWarrantytime[] = &$form->createElement('select', 'warranty_end_unit', '', [
					"DAY" => "DAYS",
					"WEEK" => "WEEKS",
					"MONTH" => "MONTHS",
					"YEAR" => "YEARS",
				]);
				$form->addGroup($groupWarrantytime, 'warranty_end', 'Warranty end in the coming:', ' ');

				$form->addElement('submit', 'btnSubmit', 'Submit');

				// add the rule for the repetition
				$ruleEvery['warranty_end_value'][] = [
					'This is required',
					'required',
				];
				$ruleEvery['warranty_end_value'][] = [
					'numerical value only',
					'numeric',
				];
				$ruleEvery['warranty_end_value'][] = [
					'integer value only',
					'nopunctuation',
				];
				$form->addGroupRule('warranty_end', $ruleEvery);
				if (!$form->validate()) {
					$body = $form->toHTML();
					break;
				}
				# If the form validates then freeze the data
				$vars = tldUtils::cleanupFormInput($form->exportValues());
				$bu = $vars['bu'];
				$erp = tldLocation::getLocationByERP(tldLocation::getERPByID($bu));
				if (!empty($bu)) {
                    $bu_title = "Devices that will reach warranty end in {$vars['warranty_end']['warranty_end_value']} coming {$vars['warranty_end']['warranty_end_unit']}S - $erp";
                }
				$data = Tld_Mis_Inventory_Item::getDevicesByWarrantyEnd($vars['warranty_end']['warranty_end_value'], $vars['warranty_end']['warranty_end_unit'], $bu);
				$_SESSION['data_report'] = $data;
				$report = new tldReportColumnar(
					$data,
					[
						"xItems" => [
							"id" => "Item#",
							"dt" => "Date",
							"type_name" => "Type",
							"brand_name" => "Brand",
							"model" => "Model",
							"manufacturer_sn" => "Manufacturer SN",
							"tld_sn" => "TLD SN",
							"fixasset_id" => 'Fix Asset ID',
							"state" => "State",
							"description" => "Description",
							"dt_warranty_end" => "End of warranty",
							"buyer_location" => "Requestor BU",
							"buyer_dpt" => "Requestor Department",
							"destination" => "Destination",
							"hidden_status" => "Disposed?",
						],
						"title" => $bu_title,
						"links" => ["id" => "$php_self?m[0]=inventory&m[1]=view&id="],
					]
				);
				if ($user->isInGroup(["warranty", "gg_PARTS", "gg_SERVICE", "gg_SUPPORT", "gg_ADMIN", "gg_ACCT"])) {
					$DEFAULT_MENU .= <<<EOF
    		<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="$php_self?m[0]=inventory&m[1]=reports&m[2]=itemOutOfWarrantyByBU&m[3]=csv">Download to CSV</a>
EOF;
				}
				$body .= $report->fetch();
				break;
            case 'byUserID':
                if (!isset($id) || !is_numeric($id)) {
                    $DEFAULT_ERROR[] = "ERROR: Parameters empty or invalid";
                    return;
                }
                $rows = Tld_Mis_Inventory_Item::byConstraints("destination_type = 'PEOPLE' AND destination_id =$id ");
                $report = new tldReportColumnar(
                    $rows,
                    [
                        "xItems" => [
                            "id" => "Item#",
                            "dt" => "Date",
                            "type_name" => "Type",
                            "brand_name" => "Brand",
                            "model" => "Model",
                            "manufacturer_sn" => "Manufacturer SN",
                            "tld_sn" => "TLD SN",
                            "fixasset_id" => 'Fix Asset ID',
                            "state" => "State",
                            "description" => "Description",
                            "dt_warranty_end" => "End of warranty",
                            "buyer_location" => "Requestor BU",
                            "buyer_dpt" => "Requestor Department",
                            "destination" => "Destination",
                            "hidden_status" => "Disposed?",
                        ],
                        "title" => $bu_title,
                        "links" => ["id" => "$php_self?m[0]=inventory&m[1]=view&id="],
                    ]
                );
                $body .= $report->fetch();
                break;
            case 'byBUByType':
                $type = TldDatabase::escape($y);
                $location = TldDatabase::escape($x);
                $bu_title = 'Inventory item ';
                $where = [];
                if ($type !== "ALL") {
                    $where[] = " type_name LIKE '$type'";
                    $bu_title .= " TYPE:".$type." ";
                }
                if ($location !== "ALL") {
                    $where[] = " buyer_location LIKE '$location'";
                    $bu_title .= " LOCATION:".$location." ";
                }
                $rows = Tld_Mis_Inventory_Item::byConstraints(implode(' AND ',$where));
                $report = new tldReportColumnar(
                    $rows,
                    [
                        "xItems" => [
                            "id" => "Item#",
                            "dt" => "Date",
                            "type_name" => "Type",
                            "brand_name" => "Brand",
                            "model" => "Model",
                            "manufacturer_sn" => "Manufacturer SN",
                            "tld_sn" => "TLD SN",
                            "fixasset_id" => 'Fix Asset ID',
                            "state" => "State",
                            "description" => "Description",
                            "dt_warranty_end" => "End of warranty",
                            "buyer_location" => "Requestor BU",
                            "buyer_dpt" => "Requestor Department",
                            "destination" => "Destination",
                            "hidden_status" => "Disposed?",
                        ],
                        "title" => $bu_title,
                        "links" => ["id" => "$php_self?m[0]=inventory&m[1]=view&id="],
                    ]
                );
                $body .= $report->fetch();
            break;
            case 'byDeptByType':
                $type = TldDatabase::escape($y);
                $dept = TldDatabase::escape($x);
                $cat = TldDatabase::escape($z);
                $_title = 'Inventory item ';
                $where = [];
                $where[] = " type_category LIKE '$cat' ";
                if ($type !== "ALL") {
                    $where[] = " type_name LIKE '$type'";
                    $_title .= " TYPE:".$type." ";
                }
                if ($dept !== "ALL") {
                    if(empty($dept)){
                        $where[] = " buyer_dpt is NULL";
                    }else{
                        $where[] = " buyer_dpt LIKE '$dept'";
                        $_title .= " DEPARTMENT:".$dept." ";
                    }
                }
                if (!empty($bu)) {
                    $where[] = " buyer_bu_id =$bu ";
                    $_title .= " BU:".tldLocation::getLocationByID($bu);
                }
                if (!empty($region)) {
                    $where[] = " region LIKE '$region' ";
                    $_title .= " Region:".$region;
                }
                $rows = Tld_Mis_Inventory_Item::byConstraints(implode(' AND ',$where));
                $report = new tldReportColumnar(
                    $rows,
                    [
                        "xItems" => [
                            "id" => "Item#",
                            "dt" => "Date",
                            "type_name" => "Type",
                            "brand_name" => "Brand",
                            "model" => "Model",
                            "manufacturer_sn" => "Manufacturer SN",
                            "tld_sn" => "TLD SN",
                            "fixasset_id" => 'Fix Asset ID',
                            "state" => "State",
                            "description" => "Description",
                            "dt_warranty_end" => "End of warranty",
                            "buyer_location" => "Requestor BU",
                            "buyer_dpt" => "Requestor Department",
                            "destination" => "Destination",
                            "hidden_status" => "Disposed?",
                        ],
                        "title" => $_title,
                        "links" => ["id" => "$php_self?m[0]=inventory&m[1]=view&id="],
                    ]
                );
                $body .= $report->fetch();
                break;
            case 'licenseListByBU':
                if(!$user->isInGroup(['gg_MIS','ROLE_MISM','ROLE_CIO','superuser'])){
                    $DEFAULT_ERROR[] = "ERROR: You do not have access to this report";
                    break;
                }
                $BU_List = tldLocation::getERPList('smartyOptionsIDLocation');
                // Get form
                $form = new HTML_QuickForm('frmInventoryList', 'post');
                $form->addElement('header', 'title', "List of tablets by BU");
                $form->addElement('hidden', 'm[0]', 'inventory');
                $form->addElement('hidden', 'm[1]', 'reports');
                $form->addElement('hidden', 'm[2]', 'licenseListByBU');
                $form->addElement('hidden', 'cat', 'SOFTWARE');
                $form->addElement('select', 'bu', 'Requestor BU', ["" => ""] + $BU_List);
                $form->addElement('submit', 'btnSubmit', 'Submit');

                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break;
                }
                # If the form validates then freeze the data
                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $STATS = Tld_Mis_Inventory_Item::licenseByBUByType($vars['bu']);
                $reportINV = new tldMatrix(
                    $STATS,
                    "buyer_dept", "type_name", "qty",
                    "$php_self?m[0]=inventory&m[1]=reports&m[2]=byDeptByType$extra_url_inv&bu={$vars['bu']}&z={$vars['cat']}",
                    "Inventory item by type by BU"
                );
                $body = $reportINV->fetch();
                break;
            case 'withContractbyRegionByBU':
                if(!$user->isInGroup(['gg_MIS','ROLE_MISM','ROLE_CIO','superuser'])){
                    $DEFAULT_ERROR[] = "ERROR: You do not have access to this report";
                    break;
                }
                $bu = $user->getBUID();
                $region = tldLocation::getRegionByBUID($bu);
                $form = new HTML_QuickForm('FrmInventory');
                $form->addElement('header', 'title', 'List items with ongoing maintenance contract by Region/BU');
                $form->addElement('hidden', 'm[0]', 'inventory');
                $form->addElement('hidden', 'm[1]', 'reports');
                $form->addElement('hidden', 'm[2]', 'withContractbyRegionByBU');
                $form->addElement('select', 'region', 'Region',
                    [""=>""] + tldLocation::getRegionList());
                $form->addElement('select', 'bu', 'Location',
                    [""=>""] + tldLocation::getERPList("smartyOptionsIDLocation"));
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $body .= $form->toHTML();

                if ($form->validate()) {
                    $vars = tldUtils::cleanupFormInput($form->exportValues());
                }
                $constraints = [];
                $fields = [ 'region','bu'];
                foreach($fields as $field) {
                    if (empty($vars[$field])) {
                        continue;
                    }
                    switch ($field) {
                        case 'region':
                            $constraints[] = "  buyer_bu.region LIKE '{$vars['region']}'";
                            $bu_title .= " - " . $vars['region'];
                            break;
                        case 'bu':
                            $constraints[] = " buyer_bu.id={$vars['bu']}";
                            $bu_title .= " - " . tldLocation::getLocationByID($bu);
                            break;
                    }
                }
                $rows = Tld_Mis_Inventory_Item::listItemWithContractByRegionByDept(implode(' AND ',$constraints));
                $report = new tldReportColumnar(
                    $rows,
                    [
                        "xItems" => [
                            "id" => "Item#",
                            "dt" => "Date",
                            "type_name" => "Type",
                            "brand_name" => "Brand",
                            "model" => "Model",
                            "manufacturer_sn" => "Manufacturer SN",
                            "tld_sn" => "TLD SN",
                            "fixasset_id" => 'Fix Asset ID',
                            "state" => "State",
                            "description" => "Description",
                            "dt_warranty_end" => "End of warranty",
                            "buyer_location" => "Requestor BU",
                            "buyer_dpt" => "Requestor Department",
                            "destination" => "Destination",
                            "hidden_status" => "Disposed?",
                            "start_dt"=>"Contract start",
                            "end_dt"=>"Contract end",
                        ],
                        "title" => $_title,
                        "links" => ["id" => "$php_self?m[0]=inventory&m[1]=view&id="],
                    ]
                );
                $body .= $report->fetch();
            break;
            case 'byRegionByBUByDEPT':
                if(!$user->isInGroup(['gg_MIS','ROLE_MISM','ROLE_CIO','superuser','GG_MISINV_fixedAssetManager'])){
                    $DEFAULT_ERROR[] = "ERROR: You do not have access to this report";
                    break;
                }
                $bu = $user->getBUID();
                $region = tldLocation::getRegionByBUID($bu);
                $form = new HTML_QuickForm('FrmInventory');
                $form->addElement('header', 'title', 'Inventory Item by Region/BU');
                $form->addElement('hidden', 'm[0]', 'inventory');
                $form->addElement('hidden', 'm[1]', 'reports');
                $form->addElement('hidden', 'm[2]', 'byRegionByBUByDEPT');
                $form->addElement('select', 'region', 'Region', [""=>""] + tldLocation::getRegionList());
                $form->addElement('select', 'bu', 'Location', [""=>""] + tldLocation::getERPList("smartyOptionsIDLocation"));
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $body .= $form->toHTML();

                if ($form->validate()) {
                    $vars = tldUtils::cleanupFormInput($form->exportValues());
                }
                $constraints = [];
                $fields = [ 'region','bu'];
                foreach($fields as $field) {
                    if (empty($vars[$field])) {
                        continue;
                    }
                    switch ($field) {
                        case 'region':
                            $constraints[] = "  buyer_bu.region LIKE '{$vars['region']}'";
                            $bu_title .= " - " . $vars['region'];
                            break;
                        case 'bu':
                            $constraints[] = " buyer_bu.id={$vars['bu']}";
                            $bu_title .= " - " . tldLocation::getLocationByID($bu);
                            break;
                    }
                }
                $constraints[] = " itemType.category LIKE 'HARDWARE'";
                $STATS = Tld_Mis_Inventory_Item::countItemByRegionByDeptByType(implode(' AND ',$constraints));
                $report = new tldMatrix(
                    $STATS,
                    "buyer_dpt", "type_name", "qty",
                    "$php_self?m[0]=inventory&m[1]=reports&m[2]=byDeptByType&z=HARDWARE&region={$vars['region']}&bu={$vars['bu']}&$extra_url_inv",
                    "Inventory item by BU by item type $bu_title"
                );
                $body .= $report->fetch();
                break;
            case 'pdmConnectedUsers':
                if(!$user->isInGroup(['gg_MIS', 'ROLE_CIO', 'superuser'])){
                    $DEFAULT_ERROR[] = "ERROR: You do not have access to this report";
                    break;
                }

                $query = <<<SQL
SELECT * FROM [TechnicalSupportStatistics].[dbo].[TLDPDMClients]
SQL;

                $users = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'pdm']);

                $users = array_map(static function($user) {
                    $user['LastSeen'] = (new \DateTime($user['LastSeen']))->format('Y-m-d H:i');
                    return $user;
                }, $users);

                $report = new tldReportColumnar(
                    $users,
                    [
                        'xItems' => [
                            'HostName' => 'Computer HostName',
                            'LastSeen' => 'Last Connection time',
                        ],
                    ]
                );
                $body .= $report->fetch();
                break;
			default:
				$body = $smarty->fetch("$PATH/inventory/reports/homepage.reports.tpl");
				break;
		}
		break;
	case 'view':
	case 'admin':
		include("inventory/logic.inventory.{$m[1]}.php");
		break;
	default:
        if($user->isInGroup(['gg_MIS','ROLE_MISM','ROLE_CIO','superuser'])) {
            $form = new HTML_QuickForm('frmSearch', 'get', '', '', '', true);
            $form->addElement('hidden', 'm[0]', 'inventory');
            $form->addElement('hidden', 'm[1]', 'search');
            $form->addElement('header', 'title', "Quick search MIS inventory");
            $form->addElement('text', 'description', 'Keyword');
            $form->addElement('submit', 'btnSubmit', 'Submit');
            $body = $form->toHTML();
            if ($display == 1) {
                $STATS = Tld_Mis_Inventory_Item::itemByRegionByType($region);
                $report = new tldMatrix(
                    $STATS,
                    "regional", "type_name", "qty",
                    "",
                    "Inventory item by region by item type $bu_title"
                );
                $body .= $report->fetch();
                $body .= "<br>";
            } else {
                $body .= "<a href=\"mis.php?m[0]=inventory&display=1\">Show dashboard by Region by item type</a><p>";
            }
            $STATS = Tld_Mis_Inventory_Item::itemByBUByType();
            $reportINV = new tldMatrix(
                $STATS,
                "buyer_location", "type_name", "qty",
                "$php_self?m[0]=inventory&m[1]=reports&m[2]=byBUByType$extra_url_inv",
                "Inventory item by BU by item type"
            );
            $body .= "<a href=\"#\" onclick=\"javascript:$('#filter').toggle();\">Show/hide dashboard by BU by item type</a>
<div id=\"filter\" style=\"display:none;\">
{$reportINV->fetch()}
</div><br>";
            $rows = Tld_Mis_Inventory_Item::byConstraints("1=1");
        }else{
            $opt[] = " WHERE item.buyer_bu_id ={$user->getBUID()}  ";
            if ($user->isInGroup(['prod_IT_referent'])){
                $opt = "destination_id  = {$user->getID()} or type_name LIKE 'SHOPFLOOR TABLET' ";
            }
            if ($user->isInGroup(['GG_MISINV_tabletManager'])){
                $opt[] = "itemType.name  LIKE 'SHOPFLOOR TABLET' ";
            }
            $rows = Tld_Mis_Inventory_Item::byConstraints($opt);
        }
        if($user->isInGroup(['GG_MISINV_tabletManager','gg_MIS','ROLE_MISM','ROLE_CIO','superuser','prod_IT_referent'])){
            $pagination = new tldPagination('Inventory List', $rows, 50);
            $smarty->assign("pagination", $pagination);
            $smarty->assign("months", $months);
            $body .= $smarty->fetch("mis/inventory/list.tpl");
        }

		break;
}
