<?php

use ApiBundle\Client;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Router;

$id = trim($id);
if(empty($id) || !is_numeric($id)){
    $DEFAULT_ERROR[] = "ERROR: No WC or invalid id set!";
    return;
}
$wc = new tldWC($id);
if($wc->isEmpty() || [] === $wc->getHeader()){
    $DEFAULT_ERROR[] = "ERROR: WC#$id empty!";
    return;
}
$header = $wc->getHeader();
$parts = array_reduce($wc->getParts(), static function ($memo, $part) {
    $memo[$part['part_number']] = $part['quantity'];

    return $memo;
}, []);

if ('' !== $wc->itsHeader['part_failing']) {
    $parts[trim($wc->itsHeader['part_failing'])] = 1;
}

$parameters = ['warrantyClaimId' => $header['id'], 'location' => $header['man_location']];
foreach ($parts as $partNumber => $quantity) {
    $parameters["parts[$partNumber]"] = $quantity;
}
$container = $kernel->getContainer();
$router = $container->get('router');
$route = $router->generate('vendor_warranty_claim_add_from_wc', $parameters);
$vwcRoute = $router->generate('vendor_warranty_claim_home', ['filter_vendor_warranty_claims[warrantyClaimId][value]' => $id]);
$smarty->assign("wc", $header);
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=wc&m[1]=view&id=$id">General</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=wc&m[1]=view&m[2]=edit&id=$id">Edit</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=wc&m[1]=view&m[2]=tasks&id=$id" title="Related tasks">Tasks</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=wc&m[1]=view&m[2]=links&id=$id">Links</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=wc&m[1]=view&m[2]=support&id=$id">Support</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=wc&m[1]=view&m[2]=service&id=$id">Service</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=wc&m[1]=view&m[2]=parts&id=$id">Parts</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=wc&m[1]=view&m[2]=status&id=$id">Status</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=wc&m[1]=view&m[2]=log&id=$id">Log</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=wc&m[1]=view&m[2]=files&id=$id">Files</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=wc&m[1]=view&m[2]=pdf&id=$id" title="Get PDF version">PDF</a>&nbsp;|&nbsp;
<a href="/en/private/common/index.php?m[0]=emails&m[1]=newEmail&module=wc&id=$id" title="Email this WC">Email</a>&nbsp;|&nbsp;
<a href="$vwcRoute" >VWCs</a>&nbsp;|&nbsp;
<a href="$route">Transfer to VWC</a>&nbsp;|&nbsp;
<a href="/en/private/product_support/wc/wc_admin.php?mode=record_view&form_type=main_tpl&id=$id">Admin</a>
EOF;

if ($user->isInGroup(["role_PSM", "role_PSE", "role_PSA", "role_QAM", "role_COO", "role_CEO"])) {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=wc&m[1]=view&m[2]=filteringFlag&id=$id">Set filtering flag</a>
EOF;
}

$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
EOF;

$body .= $smarty->fetch("$PATH/wc/view/wc.menu.tpl");

switch($m[2]){
case 'filteringFlag':
    // Check permissions
    if (!$user->isInGroup(["role_PSM", "role_PSE", "role_PSA", "role_QAM", "role_COO", "role_CEO", "role_RME", "ROLE_CSM"])) {
        $DEFAULT_ERROR[] = "ERROR: You do not have permission to access this page";
        break;
    }
    if ($wc->getFilteringFlag() == 'FILTERED') {
        $DEFAULT_ERROR[] = "WARNING: This WC is already set to FILTERED";
    }
	// Listing
	$filteringFlagList = array_combine(tldWC::getFilteringFlagList(),tldWC::getFilteringFlagList());
	$moduleList = array_keys(tldUtils::getModLinks());
	$moduleList = array_combine($moduleList,$moduleList);
    $categories = tldEquipment::getWarrantyCategories();
	// Form
	$form = new HTML_QuickForm('frm', 'post');
	$form->addElement('hidden', 'm[0]', 'wc');
	$form->addElement('hidden', 'm[1]', 'view');
	$form->addElement('hidden', 'm[2]', 'filteringFlag');
	$form->addElement('hidden', 'id', $id);
	$form->addElement('header', 'title', "Set filtering flag");
	$form->addElement('select', 'filtering_flag', 'Flag status', $filteringFlagList);
    $form->addElement('select', 'category', 'Category', $categories);
	$form->addElement('textarea', 'comment', 'Comment',	array("rows"=>5, "cols"=>40));
	$form->addElement('header', 'title1', "Any module record to link?");

	for ($i = 1; $i <= 5; $i++) {
        $form->addElement('select', "modules[$i][name]", 'Module', [''=>'']+$moduleList);
        $form->addElement('text', "modules[$i][id]", 'Module ref#');
    }

	$form->addElement('submit', 'btnSubmit', 'Submit');
	$form->addRule('filtering_flag','Required','required');
	$form->setDefaults(array('filtering_flag'=>'FILTERED', 'comment' => str_replace(['<br>'], "\n", $wc->itsHeader['prod_man_comments']),));

	if(!$form->validate()){
		$body = $form->toHTML();
		break;
	}

	$vars = tldUtils::cleanupFormInput($form->exportValues());
	$previousCategory = (string) $header['category'];
	// Update flag
    $e = $wc->setFilteringFlag($vars['filtering_flag'], $vars['category']);
	if(is_string($e)){
	    $DEFAULT_ERROR[] = "INTERNAL ERROR: can not set Filtering flag. Reason: $e";
	    break;
	}
	$body.="Filtering flag set to {$vars['filtering_flag']}";
	// Log
	$log = "Filtering flag set to {$vars['filtering_flag']}";
	if(!empty($vars['comment'])){
	    $log.="\r\n{$vars['comment']}";
	}
	$wc->addLogEntry($user->getID(),$log);
	// Log the category change as a separate entry (records who and when)
	$newCategory = (string) $vars['category'];
	if($newCategory !== $previousCategory){
	    if('' === $newCategory){
	        $categoryLog = "Category removed (was '$previousCategory')";
	    }elseif('' === $previousCategory){
	        $categoryLog = "Category set to '$newCategory'";
	    }else{
	        $categoryLog = "Category changed from '$previousCategory' to '$newCategory'";
	    }
	    $wc->addLogEntry($user->getID(),TldDatabase::escape($categoryLog));
	}
	// link if any
    foreach($vars['modules'] as $module) {
        if(!empty($module['name']) && !empty($module['id'])) {
            $wc->addLinkTo($module['name'],$module['id']);
            $body.="<br>{$module['name']}#{$module['id']} linked to the warranty";
        }
    }
	$body .= _getGeneralTab();
break;
case 'edit':
	// Check permissions
	if(!$user->isInGroup(array("warranty","gg_PARTS","gg_SERVICE","gg_SUPPORT","gg_ADMIN","gg_ACCT","role_CSD"))){
		$DEFAULT_ERROR[] = "ERROR: You do not have permission to access this page";
		break;
	}
	// Get listing
	$factoryList = tldLocation::getFactoryList("smartyOptionsLocationLocation");
	$salesOrgList = tldLocation::getSalesOrgList("smartyOptionsLocationLocation");
	$customerList = tldCustomer::getList("smartyOptionsCust_name");
	$typeList = tldType::getTypes("en","smartyOptions");
	$modelList = tldModel::getList();
	$techList = tldGroup::getUserListByMultipleGroup(array("gg_SERVICE"=>"gg_SERVICE"),"",array("smartyOptionsTech_name"=>"smartyOptionsTech_name"));
	$yesNo = array("YES"=>"YES","NO"=>"NO");
	$unitOperationList = tldEquipment::getUnitOperationStatusList();

	// Form
	$form = new HTML_QuickForm('frmEdit', 'post');
	$form->addElement(  'hidden', 		'm[0]', 				'wc');
	$form->addElement(  'hidden', 		'm[1]', 				'view');
	$form->addElement(  'hidden', 		'm[2]', 				'edit');
	$form->addElement(  'hidden', 		'id',   				$id);
	$form->addElement(  'header', 		'title', 				"Edit WC#$id");
	$form->addElement(	'header', 		'title', 				"<b>Warranty Details</b>");
	$form->addElement(	'text', 		'id',					'WC#', 					array('disabled'=>'disabled'));
	$form->addElement(	'text', 		'warranty_status',		'Status', 				array('disabled'=>'disabled'));
	$form->addElement(	'text', 		'entered_by',			'Entered By', 			array('disabled'=>'disabled','size'=>'30'));
	$form->addElement(	'textarea', 	'claimant_details',		'Claimant Details',		array("rows"=>5, "cols"=>40));
	$form->addElement(	'textarea', 	'warranty_details',		'Warranty Details', 	array('disabled'=>'disabled',"rows"=>5, "cols"=>40));
	$form->addElement(  'select', 		'customer_name', 		"Customer Name",		array(""=>"")+$customerList);
	$form->addElement(	'text', 		'serial_number',		'Equipment S/N');
	$form->addElement(	'select', 		'type',					'Equipment Type', 		array(""=>"")+$typeList);
	$form->addElement(	'select', 		'model',				'Model', 				array(""=>"")+$modelList);
	$form->addElement(	'textarea', 	'equipment_location',	'Equipment Location',	array("rows"=>5, "cols"=>40));
	$form->addElement(	'select', 		'man_location',			'Manufacturer Location',array(""=>"")+$factoryList);
	$form->addElement(	'select', 		'sales_org',			'Sales Organisation',	array(""=>"")+$salesOrgList);
	$form->addElement(	'text', 		'hours',				'Hour Meter (in Hrs)');
    if ($user->isInGroup(["role_PSM", "role_PSE", "role_PSA", "gg_ADMIN"])) {
        $form->addElement(  'date',   		'claim_date', 			'Claim Date', 			array("format"=>"Y-m-d", 'addEmptyOption'=>FALSE, "minYear"=>date("Y")-2, "maxYear"=>date("Y")+1));
        $form->addElement('select', 'er_operation_status', 'ER operation status', ["" => ""] + $unitOperationList);
        $form->addRule('er_operation_status', 'Required', 'required');
    }
    $form->addElement(	'header', 		'title', 				"<b>For Product Manager</b>");
	$form->addElement(	'text', 		'prod_man_accept_user',	'Signed By', 		 	array('disabled'=>'disabled','size'=>'30'));
	$form->addElement(	'text', 		'prod_man_accept_date',	'Signed Date', 			array('disabled'=>'disabled'));
	$form->addElement(	'textarea', 	'prod_man_comments',	'Comments',				array("rows"=>5, "cols"=>40));
	$form->addElement(	'header', 		'title', 				"<b>For Service Personnel</b>");
	$form->addElement(	'textarea', 	'problem_desc',			'Problem Description',	array("rows"=>5, "cols"=>40));
	$form->addElement(	'text', 		'part_failing',			'Critical part failing');
	$form->addElement(	'textarea', 	'extranet_prob_desc',	'Extranet Problem Description',array("rows"=>5, "cols"=>40));
	$form->addElement(	'text', 		'est_man_hours',		'Estimate Labour (Hrs)');
	$form->addElement(	'select', 		'technician',			'Technician',			array(""=>"")+$techList);
	$form->addElement(	'text', 		'technician_cost_te',	'T&E Cost');
	$form->addElement(	'text', 		'technician_cost_labour','Labour Cost');
	$form->addElement(	'text', 		'parts_cost',			'Parts Cost');
	$form->addElement(	'textarea', 	'note_cost',			'Cost Note',			array("rows"=>5, "cols"=>40));
	$form->addElement(	'select', 		'intervention',			'TLD Personnel Required?',array(""=>"")+$yesNo);
	$form->addElement(  'date',   		'service_date_delivery','Work Date', 			array("format"=>"Y-m-d", 'addEmptyOption'=>TRUE, "minYear"=>date("Y")-2, "maxYear"=>date("Y")+1));
	$form->addElement(	'textarea', 	'service_comments',		'Service Comments',		array("rows"=>5, "cols"=>40));
	$form->addElement(	'textarea', 	'service_ship_inst',	'Shipping Instructions',array("rows"=>5, "cols"=>40));
	$form->addElement(	'header', 		'title', 				"<b>For Parts Dept</b>");
	$form->addElement(	'textarea', 	'parts_order_ref',		'Parts Order Ref',		array("rows"=>5, "cols"=>40));
	$form->addElement(  'date',   		'parts_date_delivery',	'Est. Delivery Date', 	array("format"=>"Y-m-d", 'addEmptyOption'=>TRUE, "minYear"=>date("Y")-2, "maxYear"=>date("Y")+1));
	$form->addElement(	'textarea', 	'parts_courier',		'Courier Name & Tracking',array("rows"=>5, "cols"=>40));
	$form->addElement(	'textarea', 	'part_return_address',	'Parts Return Address',	array("rows"=>5, "cols"=>40));
	$form->addElement(  'submit', 		'btnSubmit', 			'Submit');
	// Set required
	//$required=array('customer_name', 'serial_number', 'type', 'model', 'man_location', 'sales_org');
	//foreach($required as $key=>$field) $form->addRule($field, 'Required', 'required');
	// Set defaults

    // Replace some HTML code in utf-8
    $header['prod_man_comments'] = str_replace(['<br>', '\r\n'], "\r\n", $header['prod_man_comments']);
    $form->setDefaults($header);

	if($header['service_date_delivery'] == '0000-00-00') $form->setDefaults(array('service_date_delivery' => array('Y' => '', 'm' => '', 'd' => '')));
	if($header['parts_date_delivery'] == '0000-00-00')$form->setDefaults(array('parts_date_delivery' => array('Y' => '', 'm' => '', 'd' => '')));

	if(!$form->validate()){
		$body = $form->toHTML();
        $body = preg_replace_callback("/(&amp;#[0-9]+;)/", function($matches) { return mb_convert_encoding($matches[1], "UTF-8", "HTML-ENTITIES");  }, $body);
        // Write special characters like Chinese
		break;
	}

	$vars = tldUtils::cleanupFormInput($form->exportValues());
	// Check ER
	$erResult = tldEquipment::bySN($vars['serial_number']);
	$er = new tldEquipment($erResult[0]['id']);
	// -- check if exist
	if($er->isEmpty()){
	    $DEFAULT_ERROR[] = "ERROR: ER {$vars['serial_number']} not found, update not possible";
	    $body = $form->toHTML();
        break;
	}
	// -- check if PAS
	if($er->isPAS()){
	    $DEFAULT_ERROR[] = "ERROR: This ER {$vars['serial_number']} is a PRE-ASSEMBLY, update not allowed";
	    $body = $form->toHTML();
        break;
	}
	$fields = array(
		'claimant_details', 'customer_name', 'serial_number', 'type', 'model', 'equipment_location', 'man_location', 'sales_org',
		'hours', 'prod_man_comments', 'problem_desc', 'extranet_prob_desc', 'est_man_hours', 'technician', 'technician_cost_te', 'technician_cost_labour',
		'parts_cost', 'note_cost', 'intervention', 'service_date_delivery', 'service_comments', 'service_ship_inst', 'parts_order_ref', 'parts_date_delivery',
		'parts_courier', 'part_return_address','part_failing'
	);
    if ($user->isInGroup(["role_PSM", "role_PSE", "role_PSA", "gg_ADMIN"])) {
        array_push($fields, 'er_operation_status', 'claim_date');
	}
	// Implode dates
	if(!empty($vars['claim_date'])) $vars['claim_date'] = implode('-', $vars['claim_date']);
	if(!empty($vars['service_date_delivery'])) $vars['service_date_delivery'] = implode('-', $vars['service_date_delivery']);
	if(!empty($vars['parts_date_delivery'])) $vars['parts_date_delivery'] = implode('-', $vars['parts_date_delivery']);
	// Update the WC
	$e = $wc->update($vars, $fields);
	if(is_string($e)){
		$DEFAULT_ERROR[] = "ERROR: Problem updating...<br/>Reason: $e";
		break;
	}
	// Update hour meter ER
	$wc->setERHourMeter($vars['hours']);
	// Check updates to log
	$wc_updated = new tldWC($id);
	$header_updated = $wc_updated->getHeader();
	$logs = array();
	$FIELD_DESIGNATION = array(
	    'claim_date'=>'Claim Date',
	    'claimant_details'=>'Claimant Details',
	    'customer_name'=>'Customer Name',
	    'serial_number'=>'Equipment S/N',
	    'type'=>'Equipment Type',
	    'model'=>'Model',
	    'equipment_location'=>'Equipment Location',
	    'man_location'=>'Manufacturer Location',
	    'sales_org'=>'Sales Organisation',
	    'hours'=>'Hour Meter (in Hrs)',
	    'er_operation_status'=>'ER operation status',
	    'prod_man_comments'=>'Comments',
	    'problem_desc'=>'Problem Description',
	    'part_failing'=>'Critical part failing',
	    'extranet_prob_desc'=>'Extranet Problem Description',
	    'est_man_hours'=>'Estimate Labour (Hrs)',
	    'technician'=>'Technician',
	    'technician_cost_te'=>'T&E Cost',
	    'technician_cost_labour'=>'Labour Cost',
	    'parts_cost'=>'Parts Cost',
	    'note_cost'=>'Cost Note',
	    'intervention'=>'TLD Personnel Required?',
	    'service_date_delivery'=>'Work Date',
	    'service_comments'=>'Service Comments',
	    'service_ship_inst'=>'Shipping Instructions',
	    'parts_order_ref'=>'Parts Order Ref',
	    'parts_date_delivery'=>'Est. Delivery Date',
	    'parts_courier'=>'Courier Name & Tracking',
	    'part_return_address'=>'Parts Return Address'
	);
	// -- Logging changes if warranty_status <> PENDING
	if($header['warranty_status'] != 'PENDING' || $header_updated['claim_date'] !== $header['claim_date']){
		$fields_to_log = array(
			'claim_date', 'claimant_details', 'customer_name', 'serial_number', 'type', 'model', 'equipment_location', 'man_location', 'sales_org',
			'hours', 'prod_man_comments', 'problem_desc', 'extranet_prob_desc', 'est_man_hours', 'technician', 'technician_cost_te', 'technician_cost_labour',
			'parts_cost', 'note_cost', 'intervention', 'service_date_delivery', 'service_comments', 'service_ship_inst', 'parts_order_ref', 'parts_date_delivery',
			'parts_courier', 'part_return_address','part_failing'
		);
		//Log if updated header <> original header
		foreach($fields_to_log as $field){
			if($header_updated[$field]!=$header[$field]){
				$logs[]="<li><b>{$FIELD_DESIGNATION[$field]}</b> from '{$header[$field]}' to '{$header_updated[$field]}'</li>";
			}
		}
	}
	// -- Logging changes if ER operation status updated !!! ANYTIME !!!
    if($header_updated['er_operation_status']!=$header['er_operation_status']){
		$logs[]="<li><b>{$FIELD_DESIGNATION['er_operation_status']}</b> from '{$header['er_operation_status']}' to '{$header_updated['er_operation_status']}'</li>";
    }
	// -- Log if applicable
	if(count($logs ?? [])){
	    $msg = "WC updated:<br><ul>".implode("",$logs)."</ul>";
	    $e = $wc->addLogEntry(
	        $user->getID(),
	        TldDatabase::escape($msg)
	    );
	    if(is_string($e)){
	        $DEFAULT_ERROR[] = "ERROR: Problem adding logs...<br/>Reason: $e";
	    }
	    $body .= "<br>".$msg;
	}
	// confirm and display WC
	$body .= "WC edited successfully!";
	$wc->refresh();
	$body .= _getGeneralTab();
break;
case 'pdf':
    $DEFAULT_TITLE .="\PDF";
    // Get general info
    $general = new tldAssocTable(
        $wc->getHeader(),
        array(
            "id"                =>"WC#",
            "warranty_status"   =>"Status",
            "customer_name"     =>"Customer Name",
            "serial_number"     =>"Equipment SN",
            "type"              =>"Equipment Type",
            "model"             =>"Equipment Model",
            "hours"             =>"Hourmeter",
            "equipment_location"=>"Equipment Location",
            "extranet_prob_desc"=>"Problem Description",
            "category"          =>"Category"
        ),
        array(
            "title"=>"General"
        )
    );
    // Get parts list
    $parts = new tldReportColumnar(
        $wc->getParts(),
        array(
            "xItems"=>array(
                "supply_it"=>"Track(Y/N)",
                "part_number"=>"PN",
                "part_description"=>"Description",
                "sn"=>"SN",
                "brand"=>"Brand",
                "um"=>"UM",
                "quantity"=>"Qty Shipped",
                "qty_in"=>"Qty Returned",
                "d_in"=>"Date Returned",
                "failure_type"=>"Failure Type",
                "failure_system"=>"Failure System"
            ),
            "title"=>"Parts List",
            "sortable"=>"N"
        )
    );
    $shipping = new tldAssocTable( $header,
                array( "parts_order_ref"=>"Problem Description",
                "parts_date_delivery"=>"Parts Delivery Date",
                "parts_courier"=>"Courier",
                "part_return_address"=>"Return Address"
                ),
                array("title"=>"Parts Delivery")
            );
    $courier = tldCourier::outMultiple($header['parts_courier']);
    if($courier){
        $courier = "<h3>Courier Tracking Information</h3>\n$courier";
    }
    $pdf_body = <<<EOF
<img src="/shared/tld_logos/tld-2inch.jpg" width="144" height="83" />
<br>
<br>
{$general->fetch()}
<br>
{$parts->fetch()}
<br>
{$shipping->fetch()}
<br>
$courier
<br>
<br><br><b>Entered By:</b> {$header['entered_by']}
EOF;
    // Generate PDF
    $smarty->assign("body", $pdf_body);
    $smarty->assign("title", "Warranty Claim #$id - ".date("Y-m-d"));
    $result = $smarty->fetch("intranet.plain.tpl");
    $conv = new tldHTML2PDF($result);
    $conv->outFile("WC#{$id}_coverpage.pdf");
    exit;
break;
case 'log':
    $DEFAULT_TITLE .= "\Log";

    $form = new HTML_QuickForm('frmNewLogComment', 'post');
    $form->addElement('hidden', 'm[0]', 'wc');
    $form->addElement('hidden', 'm[1]', 'view');
    $form->addElement('hidden', 'm[2]', 'log');
    $form->addElement('hidden', 'id', $id);
    $form->addElement('header', 'title', '<b>INTERNAL LOGS & Communication</b>');
    $form->addElement('textarea', 'comment', 'Comment',
        ['wrap' => 'VIRTUAL', 'cols' => '60', 'rows' => '5']);
    $form->addElement('submit', 'btnSubmit', 'Submit');

    if ($form->validate()) {
        $vars = tldUtils::cleanupFormInput($form->exportValues());
        try  {
            $e = $wc->addLogEntry($user->getID(), $vars['comment']);
            $body .= '<b>Log comment added successfully!</b>';
            $logComment = $form->getElement('comment');
            $logComment->setValue('');
        } catch (Exception $e) {
            $DEFAULT_ERROR[] = "ERROR: Unable to add log comment to WC#{$id}. Reason: {$e}";
            break;
        }
    }
    $body .= $form->toHTML();

    $report = new tldReportColumnar(
        $wc->getLog(),
        [
            'xItems' => [
                'id' => 'ID#',
                'date' => 'Date',
                'poster_fullname' => 'Poster',
                'comment' => 'Comment'
            ]
        ]
    );

    $body .= $report->fetch();
break;
case 'status':
    if (!$user->isInGroup(["role_PSM", "role_PSE", "role_PSA", 'gg_ADMIN', 'wc', 'role_COO'])) {
        $DEFAULT_ERROR[] = "ERROR: Only PSM, PSA, PSE can change status";
        break;
    }
    $currentStatus = $wc->getStatus();
    $allowedStatus = $wc->getStatusAllowed($user->getID());
    // Check status
    if(empty($allowedStatus)){
        $DEFAULT_ERROR[]="ERROR: Status change not allowed with actual status";
        break;
    }
    // Get form
    $form = new HTML_QuickForm('frmChangeStatus', 'post');
    $form->addElement(  'header', 'title', 'Change status');
    $form->addElement(  'hidden', 'm[0]', 'wc');
    $form->addElement(  'hidden', 'm[1]', 'view');
    $form->addElement(  'hidden', 'm[2]', 'status');
    $form->addElement(  'hidden', 'id', $id);
    $form->addElement(  'select', 'status_id', 'New Status', array(""=>"")+$allowedStatus);
    $form->addElement(  'textarea', 'comment', 'Comment', array("rows"=>10, "cols"=>40));
    $form->addElement(  'submit', 'submit', 'Submit');
    $form->addRule('comment', 'Required', 'required');
    $form->addRule('status_id', 'Required', 'required');

    if(!$form->validate()){
        $body = $form->toHTML();
        break;
    }

    $rawVars = $form->exportValues();
    $vars = tldUtils::cleanupFormInput($rawVars);
    $parsedComment = str_replace(["\r\n", "\r", "\n"], "<br/>", $rawVars['comment']);
    $new_status = $allowedStatus[$vars['status_id']];

    $factory = new tldLocation($wc->getFactoryID());
    if (
        !$user->isInGroupLevel(["role_PSM", "role_PSE", "role_PSA", 'gg_ADMIN', 'wc', 'role_COO'], $factory->getERP())
        && $user->itsDetails['location'] !== $wc->itsHeader['man_location']
    ) {
        $DEFAULT_ERROR[] = "the user to ACCEPT, REJECT or CONDITIONAL a WC must belong to the same BU's than the MANUFACTURING LOCATION of the ER.";
    } else {
        $e = $wc->changeStatus($new_status,$user->getID());
        if(is_string($e)){
            $DEFAULT_ERROR[] = "INTERNAL ERROR: Status not updated.<br/>Reason: $e";
            break;
        }
        // Add log
        $m = $new_status;
        if($vars['comment']) $m .= "\n".$vars['comment'];
        $wc->addLogEntry($user->getID(), $m);
        // Add support comment
        $comment = str_replace(["\\r\\n", "\r\n", "\r", "\n"], "<br>", $header['prod_man_comments']);

        // Add a line break if there is already a comment
        if (strlen($comment) > 1) {
            $comment .= "<br>";
        }
        $comment .= str_replace(["\\r\\n", "\r\n", "\r", "\n"], "<br>", $vars['comment']);

        $e = $wc->psmUpdate(["prod_man_comments" => TldDatabase::escape($comment)]);
        if(is_string($e)){
            $DEFAULT_ERROR[] = "INTERNAL ERROR: Production comment not updated.<br/>Reason: $e";
        }
        $body .= "WC status successfully changed to $new_status";
        // refresh
        $wc->refresh();

        $to = [$wc->itsHeader['entered_by']];
    }

    // POST action
    switch($new_status){
    case 'SALES CONCESSION':
        $tocID = $wc->getTocID();
        if (null !== $tocID['id']){
            $toc = new tldTOC($tocID['id']);
            $toc->update(['toc_type' => 'SSO']);
        }

    	$erp = tldLocation::getERPByLocation($header['sales_org']);
    	// get EVP & CSM
    	$evpgrp = new tldGroup("role_EVP",$erp);
    	$to = [...$to ?? [],$evpgrp->getEmailList()];
    	$csmgrp = new tldGroup("role_CSM",$erp);
    	$to = [...$to ?? [],$csmgrp->getEmailList()];
    	// email details
    	$subject = "WC#$id has changed to SALES CONCESSION - {$header['sales_org']}, {$header['customer_name']}, {$header['serial_number']}";
    	$emailbody = <<<EOF
WC#$id has changed to SALES CONCESSION
<br><br>
Reason:<br>$parsedComment
<br><br>
When a WC has been declared SALES CONCESSION, it becomes the responsibility of the Sales Entity to
challenge this status, in case it would be unreasonable. If the SALES CONCESSION is justified,
the Sales Entity should consider to invoice all direct costs related to this WC to the customer.
<br><br>
<a href="https://www.tld-gse.com/en/private/product_support/index.ps.php?m[0]=wc&m[1]=view&id=$id">Click here to view WC details</a>
EOF;
    	// Send email
    	$wc->sendEmail(
    			array_unique($to),
    			$user->getEmail(),
    			$subject,
    			$emailbody
    	);
    break;
    case 'REJECTED':
        $tocID = $wc->getTocID();
        if (null !== $tocID['id']){
            global $kernel;
            try {
                $container = $kernel->getContainer();
                $client = $container->get(Client::class);
            } catch (\Exception $e) {
                $DEFAULT_ERROR[] = 'Client could not be fetched.';
                break;
            }

            try {
                $apiTechnicianOnCallType = $client->findOneBy('service/technician_on_call_types', ['name' => 'toc.type.not_define_yet']);
            } catch (\Exception $e) {
                $DEFAULT_ERROR[] = 'Could not find correct type';
                break;
            }

            try {
                $apiTechnicianOnCall = $client->findOneBy('service/technician_on_calls', ['legacyId' => $tocID['id']]);
                $client->put(sprintf('service/technician_on_calls/%s', $apiTechnicianOnCall['id']), [
                    'json' => [
                        'technicianOnCallType' => $apiTechnicianOnCallType['@id'],
                    ]
                ]);
            } catch (\Exception $e) {
                $DEFAULT_ERROR[] = 'TOC has not been updated';
                break;
            }
        }

        $erp = tldLocation::getERPByLocation($header['sales_org']);
        // get EVP & CSM
        $evpgrp = new tldGroup("role_EVP",$erp);
        $to = array_merge($to,$evpgrp->getEmailList());
        $csmgrp = new tldGroup("role_CSM",$erp);
        $to = array_merge($to,$csmgrp->getEmailList());
        // email details
        $subject = "WC#$id has been REJECTED - {$header['sales_org']}, {$header['customer_name']}, {$header['serial_number']}";
        $emailbody = <<<EOF
WC#$id has been REJECTED
<br><br>
Reason:<br>$parsedComment
<br><br>
When a WC has been declared REJECTED, it becomes the responsibility of the Sales Entity to
challenge this status, in case it would be unreasonable. If the rejection is justified,
the Sales Entity should consider to invoice all direct costs related to this WC to the customer.
<br><br>
<a href="https://www.tld-gse.com/en/private/product_support/index.ps.php?m[0]=wc&m[1]=view&id=$id">Click here to view WC details</a>
EOF;
        // Send email
        if (!empty($to)) {
            $wc->sendEmail(
                array_unique($to),
                $user->getEmail(),
                $subject,
                $emailbody
            );
        } else {
            $DEFAULT_ERROR[] = "ERROR: No recipients for sending enail notification...";
        }
    break;
    case 'DISPUTE':
        $erp = tldLocation::getERPByLocation($header['sales_org']);
        // get EVP & CSM
        $evpgrp = new tldGroup("role_EVP", $erp);
        $to = array_merge($to, $evpgrp->getEmailList());
        $csmgrp = new tldGroup("role_CSM", $erp);
        $to = array_merge($to, $csmgrp->getEmailList());
        // email details
        $subject = "WC#$id has changed to DISPUTE - {$header['sales_org']}, {$header['customer_name']}, {$header['serial_number']}";
        $emailbody = <<<EOF
WC#$id has changed to DISPUTE
<br><br>
Reason:<br>$parsedComment
<br><br>
When a WC has changed to DISPUTE, it becomes the responsibility of the Sales Entity to
challenge this status, in case it would be unreasonable. If the rejection is justified,
the Sales Entity should consider to invoice all direct costs related to this WC to the customer.
<br><br>
<a href="https://www.tld-gse.com/en/private/product_support/index.ps.php?m[0]=wc&m[1]=view&id=$id">Click here to view WC details</a>
EOF;
        // Send email
        if (!empty($to)) {
            $wc->sendEmail(
                array_unique($to),
                $user->getEmail(),
                $subject,
                $emailbody
            );
        } else {
            $DEFAULT_ERROR[] = "ERROR: No recipients for sending enail notification...";
        }
        break;
    }

    // If WC linked to a OPEN TOC, notify ASM

    $linksTo = $wc->getLinksToHere();
    foreach($linksTo as $link){
        if($link['module']!='TOC') continue;
        $tocid = $link['parent_id'];
    }
    if(!empty($tocid)){
        // Search ASM using CRT
        $toc = new tldTOC($tocid);
        $crtList = array();
        $a = array(
            "erp_location_id"=>$toc->getSSOID(),
            "customer_id"=>$toc->getCUID()
        );
        $crtList = tldCRT::byConstraints($a);
        $asmEmails = array();
        foreach($crtList as $crt){
            if(empty($crt['sales_rep_email'])) continue;
            $asmEmails[]=$crt['sales_rep_email'];
        }
        if(empty($asmEmails)){
            $DEFAULT_ERROR[] = "WARNING: No ASM found from CRTs linked to the customer in TOC#$tocid";
        }else{
            $erp = tldLocation::getERPByLocation($header['sales_org']);
            $csmgrp = new tldGroup("role_CSM",$erp);
            $asmEmails = array_merge($asmEmails, $csmgrp->getEmailList(), [$wc->itsHeader['entered_by']]);
            $asmEmails = array_unique($asmEmails);
            // Send email
            $e = $wc->sendEmail(
                $asmEmails,
                "noreply@tld-gse.com",
                "WC#$id status update to $new_status",
                "<b>Reason for Status change:</b><br>".$parsedComment,
                $user->getEmail()
            );
            if(is_string($e)){
                $DEFAULT_ERROR[] = "INTERNAL ERROR: email not sent to ASM.<br>Reason: $e";
            }else{
                $body .= "<br>Email notification sent to ".implode(', ',$asmEmails);
            }
        }
    }else{
        $DEFAULT_ERROR[] = "WARNING: No TOC found linked to this WC to notify ASM";
    }
    // display header
    $body .= _getGeneralTab();
break;
case "tasks":
    $DEFAULT_TITLE .="\Tasks";
    $DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=form&m[2]=newTask&module=WC&parent_id=$id">New Task</a>
EOF;
    $tocList = [];
    $tocTasks = [];
    // Retrieve all TOC directly linked to the WC
    foreach ($wc->getLinksFromHere('TOC') as $link) {
        $tocList[] = $link['item'];
    }
    foreach ($wc->getLinksToHere('TOC') as $link) {
        $tocList[] = $link['parent_id'];
    }
    // Get all tasks of directly linked TOC
    foreach ($tocList as $module_id) {
        $toc = new tldTOC($module_id);
        $tocTasks = (array)$toc->getTasks();
    }
    $sess['calendar']['tasks'] = array_merge($wc->getTasks(), $tocTasks);

    $formWCTasks = new tldReportMultiLevel(
        $wc->getTasks(),
        ['status', 'due_date'],
        [
            'id' => 'Task#',
            'status' => 'Status',
            'due_date' => 'Due',
            'task' => 'Task',
            'assignee_fullname' => 'Assignee'
        ],
        [
            'passField' => 'id',
            'title' => 'Tasks',
            'url' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id='
        ]
    );

    $formTOCTasks = new tldReportMultiLevel(
        $tocTasks,
        ['status', 'due_date'],
        [
            'id' => 'Task#',
            'status' => 'Status',
            'due_date' => 'Due',
            'task' => 'Task',
            'assignee_fullname' => 'Assignee'
        ],
        [
            'passField' => 'id',
            'title' => 'TOC Tasks',
            'url' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id='
        ]
    );
    $body .= $formWCTasks->fetch();
    $body .= $formTOCTasks->fetch();
break;
case 'links':
    $DEFAULT_MENU.=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/en/private/common/index.php?m[0]=links&m[1]=form&m[2]=newLink&module=WC&parent_id=$id">New Link</a>
EOF;
    $report = new tldReportColumnar(
        $wc->getLinks(),
        [
	        'xItems'=> [
                'id'=>'ID#',
                'type'  =>'Module',
                'item'  =>'Ref#',
                'dsca'  =>'Description'
	        ],
	        'title'=>'Links',
	        'links'=> ['id' =>'/en/private/common/index.php?m[0]=links&m[1]=view&id='],
        ]
    );
    $body .= $report->fetch();

    $linksToHere = tldModLink::byItem($id, 'WC');
    $report      = new tldReportColumnar(
        $linksToHere,
        array(
            'xItems' => array(
                'id'        => 'ID#',
                'module'    => 'Module',
                'parent_id' => 'Ref#',
                'dsca'      => 'Description'
            ),
            'title'  => 'Links TO Here...',
            'links'  => [
                'id'        => "/en/private/common/index.php?m[0]=links&m[1]=view&reversed=1&erp=$erp&id=",
                'parent_id' => [
                    'url'    => '/en/private/common/index.php?m[0]=links&m[1]=view&reversed=1&erp=$erp&id=',
                    'params' => ['id' => 'id']
                ],
            ]
        )
    );
    $body .= $report->fetch();

    // Links To Linked TOC
    foreach ($linksToHere as $linkToHere) {
        if ('TOC' !== $linkToHere['module']) {
            continue;
        }
        $linkId        = $linkToHere['parent_id'];
        $currentModule = strtoupper($m[0]);

        $links  = array_filter(tldModLink::byParent($linkId, 'TOC'),
            static function ($link) use ($currentModule, $id) {
                return $link['type'] !== $currentModule || ($link['type'] === $currentModule && $link['item'] !== $id);
            });
        $report = new tldReportColumnar(
            $links,
            [
                'xItems' => [
                    'id'   => 'Link ID',
                    'type' => 'Module',
                    'item' => 'Ref#',
                    'dsca' => 'Description'
                ],
                'title'  => "TOC #$linkId Links",
                'links'  => [
                    'id'   => "/en/private/common/index.php?m[0]=links&m[1]=view&reversed=1&erp=$erp&id=",
                    'item' => [
                        'url'    => '/en/private/common/index.php?m[0]=links&m[1]=view&m[2]=redirect',
                        'params' => ['id' => 'id']
                    ],
                ]
            ]
        );
        $body .= $report->fetch();
    }


break;
case "support":
    $form = new tldAssocTable(
        $header,
        array(
            "warranty_status"=>"Status",
            "prod_man_accept_user"=>"Signed By",
            "prod_man_accept_date"=>"Signed Date",
            "prod_man_comments"=>"Comments"
        ),
        array("title"=>"Product Support Section")
    );
    $body .= $form->fetch();
break;
case "service":
    $form = new tldAssocTable(
        $header,
        array(
            "problem_desc"          =>"Problem Description",
            "extranet_prob_desc"    =>"Extranet Problem Description",
            "est_man_hours"         =>"Hours Labour",
            "technician"            =>"Technician",
            "technician_cost_te"    =>"Travel Expenses",
            "technician_cost_labour"=>"Labour Cost",
            "parts_cost"            =>"Parts Cost",
            "note_cost"             =>"Notes cost",
            "intervention"          =>"TLD Personnel Required",
            "service_date_delivery" =>"Work Date (yyyy-mm-dd)",
            "service_comments"      =>"Comments",
            "service_ship_inst"     =>"Shipping Instructions "
        ),
        array("title"=>"Service Section")
    );
    $body .= $form->fetch();
break;
case "parts":
    $form = new tldAssocTable(
        $header,
        array(
            "parts_order_ref"=>"Problem Description",
            "parts_date_delivery"=>"Parts Delivery Date",
            "parts_courier"=>"Courier",
            "part_return_address"=>"Return Address"
        ),
        array("title"=>"Parts Section")
    );
    $body .= $form->fetch();
    $courier = tldCourier::outMultiple($header['parts_courier']);
    if($courier){
        $body .= "<h4>Courier Tracking Information</h4>".$courier;
    }
    $parts = $wc->getParts();
    foreach($parts as &$part){
    	$count = tldWC::WCCountByPN($part['part_number']);
    	$countyear = tldWC::WCCountByPNByPast12Month($part['part_number']);
    	$part['wc_count_total'] = $count[0]['count'];
    	$part['wc_count_year'] = $countyear[0]['count'];
    }
    $report = new tldReportColumnar(
        $parts,
        array(
            "xItems"=>array(
                "id"=>"ID#",
                "supply_it"=>"Track(Y/N)",
                "notes"=>"Note",
                "brand"=>"Part Brand",
                "part_number"=>"PN",
                "part_description"=>"Description",
                "sn"=>"SN",
                "failure_type"=>"Failure Type",
                "failure_system"=>"Failure System",
                "um"=>"UM",
                "quantity"=>"Qty Shipped",
                "qty_in"=>"Qty Returned",
                "d_in"=>"Date Returned",
                "wc_count_total"=>"WC Count Total",
                "wc_count_year"=>"Last 12 months WC Count"
            ),
            "title"=>"Parts List",
            "links"=>array(
                "part_number"=>"/en/private/manufacturing/eng/dev.php?m[0]=bom&m[1]=view&erp=".tldLocation::getERPByLocation($header['man_location'])."&date=".date('Y-m-d')."&pn=",
                'wc_count_total' => [
                    'url'=>"/en/private/product_support/index.ps.php?m[0]=wc&m[1]=listing&m[2]=byPN",
                    "params"=>["pn" => "part_number"]
                ],
                'wc_count_year' => [
                    'url'=>"/en/private/product_support/index.ps.php?m[0]=wc&m[1]=listing&m[2]=byPNWithin12Month",
                    "params"=>["pn" => "part_number"]
                ],
            ),
        	"functions"=>array(
        		 "PN Inventory"=>array(
        		 	"url"=>"/en/private/parts/parts.php?m[0]=inv&m[1]=view&id=",
        		 	"param"=>"part_number"
        		)
        	)
        )
    );
    $body .= $report->fetch();
    $trackinglist = tldWC::getTrackingList($id);
    foreach ($trackinglist AS $key=>$val){
        $query = <<<SQL
SELECT 
sols.t_ttyp + CAST(sols.t_invn AS char) as t_invn
FROM ttdsls040{$val['erp']} as sors 
LEFT JOIN ttdsls045{$val['erp']} as sols on sors.t_orno=sols.t_orno 
WHERE sors.t_orno={$val['so_no']} and sols.t_dino ={$val['packing_slip']} 
SQL;
        $row = tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
        $trackinglist[$key]['t_invn'] = $row['t_invn'];

    }
    $report = new tldReportColumnar(
            $trackinglist,
    		[
                "xItems" => [
                    "id" => "ID#",
                    "erp" => "ERP#",
                    "so_no" => "SO#",
                    "packing_slip" => "Packing Slip#",
                    "tracking_no" => "Tracking#",
                    "date" => "Date"
                ],
                "title" => "Tracking List",
                "links" => [
                    "part_number" => "/en/private/manufacturing/eng/dev.php?m[0]=bom&m[1]=view&erp=".tldLocation::getERPByLocation($header['man_location'])."&date=".date('Y-m-d')."&pn=",
                    "tracking_no" => [
                        "url" => "$php_self?m[0]=wc&m[1]=tracking&tracking_no=",
                        "params" => [
                            "tracking_no" => "tracking_no"
                        ],
                        "target" => "_blank"
                    ],
                    "packing_slip" => [
                        "url" => "https://www.tld-gse.com/en/private/finance/finance.php?m[0]=ps&m[1]=view",
                        "params" => [
                            "erp" => "erp",
                            "id" => "packing_slip"
                        ]
                    ]
                ],
                "functions" => [
                    "INV PDF" => [
                        "url" => "/en/private/finance/finance.php?m[0]=archive&erp={$val['erp']}&doctype=SALES%20INVOICE&id=",
                        "param" => "t_invn",
                    ],
                    "Delete" => [
                        "img" => "/shared/icons/miscellaneous/delete.png",
                        "url" => "$php_self?m[0]=wc&m[1]=view&m[2]=parts&m[3]=trackingdel&id=$id&tracking_id=",
                        "param" => "id",
                        "confirmPopup" => "Are you sure you want to delete this part?"
                    ]
                ]
    		]
    );
    $body .= $report->fetch();
break;
case "files":
	if($user->isInGroup(array("warranty","gg_PARTS","gg_SERVICE","gg_SERVICE_AGENTS","gg_SUPPORT","gg_ADMIN"))){
		$DEFAULT_MENU.=<<<EOF
<a href="$php_self?m[0]=wc&m[1]=view&m[2]=files&m[3]=add&id=$id">Add File</a>&nbsp;|
EOF;
	}

    $DEFAULT_MENU.=<<<EOF
&nbsp;<a href="$php_self?m[0]=wc&m[1]=view&m[2]=files&m[3]=download_all_wc&id=$id">Download all WC files</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=wc&m[1]=view&m[2]=files&m[3]=download_all_toc&id=$id">Download all TOC files</a>
EOF;

    global $kernel;
    switch($m[3]){
        case 'download_all_wc':
            $files = $wc->getFiles();
            $zip = new tldFileZIP(tempnam('/tmp', "wc_files").".zip");

            foreach($files as $file){
                $zip->addFile($kernel->getContainer()->getParameter('upload_dir').'/warranty_files/'.$file['filename']);
            }
            $zip->out("wc_files.zip");
            break;
        case 'download_all_toc':
            $rows = array_merge(tldModLink::byParent($id, 'WC', 'TOC'), tldModLink::byItem($id, 'WC', 'TOC'));
            foreach ($rows as $row) {
                if ($row['type'] === 'TOC') {
                    $toc = new tldTOC($row["item"]);
                    $filesFromTOC = $toc->getFiles();
                } else {
                    $toc = new tldTOC($row["parent_id"]);
                    $filesFromTOC = $toc->getFiles();
                }
            }
            $zipname = tempnam('/tmp', "toc_files").".zip";
            $zip = new ZipArchive;
            $zip->open($zipname, ZipArchive::CREATE);
            foreach ($filesFromTOC as $file) {
                $zip->addFile($file['filepath'], $file['filename']);
            }

            $zip->close();

            header('Content-Type: application/zip');
            header('Content-disposition: attachment; filename='.'toc_files.zip');
            header('Content-Length: ' . filesize($zipname));
            readfile($zipname);
            break;
		case 'add':
			$date = date('Y-m-d');
			// Form
			$form = new HTML_QuickForm('frmAddFile', 'post');
			$form->addElement(  'hidden', 		'm[0]', 				'wc');
			$form->addElement(  'hidden', 		'm[1]', 				'view');
			$form->addElement(  'hidden', 		'm[2]', 				'files');
			$form->addElement(  'hidden', 		'm[3]', 				'add');
			$form->addElement(  'hidden', 		'id',   				 $id);
			$form->addElement(  'hidden', 		'parent_id',   			 $id);
			$form->addElement(  'hidden',   	'date', 				 $date);
			$form->addElement(  'header', 		'title', 				"Add File to WC#$id");
			$form->addElement(	'text', 		'description',			'Description', 			array('size'=>'30'));
			$form->addElement(	'file', 		'attachment',			'Filename');
			$form->addElement(  'submit', 		'btnSubmit', 			'Submit');
			//Set required
			$form->addRule('description', 'Required', 'required');
			$form->addRule('attachment', 'Required', 'required');
			if(!$form->validate()){
				$body = $form->toHTML();
				break 2;
			}else{
				$vars = tldUtils::cleanupFormInput($form->exportValues());
				$file = $form->getElement('attachment');
				$file_array = $file->getValue();
				$timestamp=time();
				$clean_name = basicFile::cleanupName($file_array['name']);
				$filename = $timestamp.'-'.$clean_name;
				if($file_array['tmp_name']!=""){
					$f = $file->moveUploadedFile($GLOBALS['UPLOADS_PATH']."/warranty_files",$filename);
					if(!$f){
						$DEFAULT_ERROR[] = "ERROR: Problem uploading file...<br/> $f";
					}
				}
				$vars ['filename'] = $filename;
				// Insert WC File
				$e = tldWC::insertFile($vars);
				if(is_string($e)){
					$DEFAULT_ERROR[] = "ERROR: Problem adding WC File...<br/>Reason: $e";
					break;
				}
				// Logging if warranty_status <> PENDING
				if($header['warranty_status'] != 'PENDING'){
					$msg = "WC File '$filename' uploaded";
					$e = $wc->addLogEntry(
						$user->getID(),
						TldDatabase::escape($msg)
					);
					if(is_string($e)){
						$DEFAULT_ERROR[] = "ERROR: Problem adding logs...<br/>Reason: $e";
					}
				}
			}
			$body .= "WC File uploaded successfully!";
		break;
		case 'del':
			// Check permissions
			if(!$user->isInGroup(array("warranty","gg_PARTS","gg_SERVICE","gg_SERVICE_AGENTS","gg_SUPPORT","gg_ADMIN"))){
				$DEFAULT_ERROR[] = "ERROR: You do not have permission to access this page.";
				break;
			}
			// Check File ID
			if(!empty($file_id) && is_numeric($file_id)){
				$file = tldWC::getFileByID($file_id);
				$filepath = $GLOBALS['UPLOADS_PATH']."/warranty_files/".$file['filename'];
				$del = tldWC::deleteFile($file_id);
				$e = unlink($filepath);
				if(is_string($del) || is_string($e)){
					$DEFAULT_ERROR[]= "INTERNAL ERROR: File not deleted!<br/>Reason: $del; $e";
				}else{
					$body = "File ".$file['filename']." deleted successfully!";
					// Logging if warranty_status <> PENDING
					if($header['warranty_status'] != 'PENDING'){
						$msg = "WC File '".$file['filename']."' deleted";
						$log = $wc->addLogEntry(
							$user->getID(),
							TldDatabase::escape($msg)
						);
						if(is_string($log)){
							$DEFAULT_ERROR[] = "ERROR: Problem adding logs...<br/>Reason: $e";
						}
					}
				}
			}else $DEFAULT_ERROR[]="ERROR: File id empty or invalid!";

		break;
        case 'changeVisibility':
            $request = $kernel->getContainer()->get('request_stack');
            // Check permissions
            if(!$user->isInGroup(array('superuser','role_QAM', 'role_QE', 'ROLE_QA'))){
                $DEFAULT_ERROR[] = "ERROR: You do not have permission to access this page.";
                break;
            }

            // Check File ID
            if(!empty($file_id) && is_numeric($file_id)){
                $error = tldWC::updateVisibilityOfFileByID($file_id);
                /** @var Session $session */
                $session = $request->getSession();
                if (is_string($error)) {
                    $session->getFlashBag()->add('error', 'Visibility of file could not be updated.');
                } else {
                    $session->getFlashBag()->add('success', 'Visibility of file updated successfully.');
                }
            }else $DEFAULT_ERROR[]="ERROR: File id empty or invalid!";

            header("Location: http://{$_SERVER['HTTP_HOST']}/en/private/purchasing/vendor-warranty-claims/$vendorWarrantyClaimId/show");
            exit;
	}

    if($fileid){
        $wc->outFile($fileid);
        $template="NO_TEMPLATE";
    }else{
    	$files = $wc->getFiles();
        $form = new tldReportColumnar(
            $files,
            array(
                "xItems"=>array(
                    "id"=>"File ID#",
                    "date"=>"Date",
                    "description"=>"Description",
                    "filename"=>"Filename"
                ),
                "title"=>"Files Section",
                "links"=>array("id"=>"$php_self?m[0]=wc&m[1]=view&m[2]=files&m[3]=outFile&id=$id&fileid="),
            	"functions"=>array(
            		"Delete"=>array(
            			"img"=>"/shared/icons/miscellaneous/delete.png",
            			"url"=>"$php_self?m[0]=wc&m[1]=view&m[2]=files&m[3]=del&id=$id&file_id=",
            			"param"=>"id",
            			"confirmPopup"=>"Are you sure you want to delete this file?"
            		)
            	)
            )
        );
        $body .= $form->fetch();
		$rows = array_merge(tldModLink::byParent($id, 'WC', 'TOC'), tldModLink::byItem($id, 'WC', 'TOC'));
		foreach ($rows as $row) {
            if ($row['type'] === 'TOC') {
                $toc = new tldTOC($row["item"]);
                $tocfiles[$row["item"]] = $toc->getFiles();
            } else {
                $toc = new tldTOC($row["parent_id"]);
                $tocfiles[$row["parent_id"]] = $toc->getFiles();
            }
        }
        foreach($tocfiles as $k => $v){
            if(!empty($v)){
                foreach ($v as $m => $n) {
                    $arr[$k][] = $n;
                }
                $tocReport = new tldReportColumnar(
                    $arr[$k],
                    [
                        "xItems" => [
                            "id" => "File ID",
                            "date" => "Date",
                            "description" => "Description",
                            "filename" => "Filename"
                        ],
                        "links" => [
                            "id" => "/en/private/common/index.php?m[0]=files&m[1]=view&m[2]=out&id="
                        ],
                        "title" => "Files from TOC#$k",
                    ]
                );
                $body .= $tocReport->fetch();
            }
        }
    }
break;
case "vwc":
    $router = $kernel->getContainer()->get('router');
    $url = $kernel->getContainer()->get('router')->generate('vendor_warranty_claim_home', [], Router::ABSOLUTE_URL);
    header("Location: $url");
break;
default:
    $body .= getLinkedTOCLastLog();
    $body .= _getGeneralTab();
break;
}


