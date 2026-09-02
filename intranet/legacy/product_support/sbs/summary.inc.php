<?php

use ApiBundle\Client;
use Symfony\Component\HttpClient\Exception\ClientException;

$DEFAULT_TITLE .= "\Summary";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sbs&m[1]=view&m[2]=summary&m[3]=cla_sum&id=$id" title="Classic Summary">Classic Summary</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=sbs&m[1]=view&m[2]=summary&m[3]=spr_sum&id=$id" title="SPR Summary">SPR Summary</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=sbs&m[1]=view&m[2]=summary&m[3]=csr_sum&id=$id" title="CSR Summary">CSR Summary</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=sbs&m[1]=view&m[2]=summary&m[3]=not_sum&id=$id" title="NOT Summary">NOT Summary</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=sbs&m[1]=view&m[2]=summary&m[3]=genspr&id=$id" title="Generate a new Spare Parts Request">New SPR</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=sbs&m[1]=view&m[2]=summary&m[3]=gencsr&id=$id" title="Generate a new Customer Service Request">New CSR</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=sbs&m[1]=view&m[2]=summary&m[3]=gennot&id=$id" title="Generate a new Customer Notification">New NOT</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=sbs&m[1]=view&m[2]=summary&m[3]=xls&id=$id">XLS</a>
EOF;

switch($m[3]){
case 'xls':
    $report = new tldXLS(
        $sb->getSummary(),
        array(
        	"xItems"=>array(
                "sb_id"=>"SB",
                "entered_date"=>"Date",
                "urgency"=>"Severity",
                "sn"=>"ER SN",
                "model"=>"Model",
        		"sales_org"=>"SSO",
        		"customer_name"=>"Customer",
                "airport_code"=>"Airport Code",
                "log_id"=>"Log#",
                "log_dt"=>"Log date",
                "spr_id"=>"SPR#",
                "spr_status"=>"SPR Status",
                "csr_id"=>"CSR#",
                "csr_status"=>"CSR Status",
                "tbd"=>"TBD?",
        		"done"=>"Done?",
            ),
        	"showTitles"=>true
        )
    );
	$report->out("SB$id\_summary_".date('Ymd').'.xls');
	exit;
break;
case 'csr':
    $DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sbs&m[1]=view&m[2]=summary&m[3]=gencsr&id=$id">New CSR</a>
EOF;
    $DEFAULT_TITLE .="\Customer Service Requests";
    $report = new tldReportColumnar(
        $sb->getLinksFromHere("CSR"),
        array(
        	"xItems"=>array(
                "id"	=>"ID#",
                "type"	=>"Module",
                "item"	=>"Ref#"
            ),
            "title"=>"Linked CSR",
            "links"=>array("id"=>"/en/private/common/index.php?m[0]=links&m[1]=view&m[2]=redirect&erp=$erp&id=")
        )
    );
    $body .= $report->fetch();
break;
case 'gencsr':
	// Get all ER related to the SB
    $rows = $sb->getAffectedEquipment(array("tbd"=>"Y"));
    if(count($rows) == 0){
    	$DEFAULT_ERROR[]="No affected equipment(s) found";
    	break; // break if no equipment
    }
    $csrs = $sb->getLinksFromHere("CSR");
	$erWithCSR = array();
    if(count($csrs)){
        foreach($csrs as $csr){
            $mycsr = new tldCSR($csr['item']);
            $erWithCSR[] = $mycsr->getParentID();
        }
    }
	// FORM -->
    $form = new HTML_QuickForm('frmGenCSR', 'post');
    $form->addElement(	'hidden', 'm[0]', 'sbs');
    $form->addElement(	'hidden', 'm[1]', 'view');
    $form->addElement(	'hidden', 'm[2]', 'summary');
    $form->addElement(	'hidden', 'm[3]', 'gencsr');
    $form->addElement(	'hidden', 'id', $id);
    $textInfo = array();
    foreach ($rows as $row){
    	$textInfo[$row['id']] = $row['sn'].' - '.$row['model'].' - '.$row['customer_name'].' - '.$row['airport_code'].$row['sales_org'];
    }
    // List all ER related to the CSR
    $form->addElement(	'header', 'title', 'Select ER(s) to generate CSR');
	// LOOP twice to GROUP BY CSR
    foreach ($rows as $row){
    	$a = array();
    	if(!in_array($row['id'], $erWithCSR)){
    		$a[] = & $form->createElement(	'checkbox','erids['.$row['id'].']','Category', "Select", "Y");
			$id_element = & $form->createElement('text', 'ids['.$row['id'].']');
	        $id_element->freeze();
	        $a[] = $id_element;
	        $d['ids['.$row['id'].']'] = $textInfo[$row['id']];
	        $form->addGroup($a, null, null, '&nbsp;-&nbsp;');
    	}
    }
    foreach ($rows as $row){
    	$a = array();
    	if(in_array($row['id'], $erWithCSR)){
    		$a[] = & $form->createElement(	'checkbox', 'erids['.$row['id'].']',
    					'CSR Already Created', 'CSR Already Created', array("disabled"=>"disable"));
			$id_element = & $form->createElement('text', 'ids['.$row['id'].']');
	        $id_element->freeze();
	        $a[] = $id_element;
	        $d['ids['.$row['id'].']'] = $textInfo[$row['id']];
	        $form->addGroup($a, null, null, '&nbsp;-&nbsp;');
    	}
    }
    $form->setDefaults($d);
    $form->addElement(	'submit', 'btnSubmit', 'Submit');
    $form->addRule(	'erids_info', 'This is required', 'required');

    if(!$form->validate()){
        if(!$user->isInGroup(array("gg_ADMIN","gg_SERVICE"))){
            $form->freeze();
        }
        $body.= $form->toHTML();
        break;
    }

    $vars = tldUtils::cleanupFormInput($form->exportValues());
    $erids = array_keys($vars['erids']);
    if(!count($erids)){
        $DEFAULT_ERROR[]="ERROR: No ER selected to generate CSR";
        break;
    }
    // Generate CSR
    foreach($erids as $erid){
        $er = new tldEquipment($erid);
        // Prepare csr data
        $data = array(
            "parent_id"=>$er->getID(),
            "entered_by"=>$user->getID(),
            "sso_id"=>$er->getSSOID(),
            "work_type"=>"Service Bulletin",
            "apc"=>$er->getAPC(),
            "hourmeter"=>$er->getHours(),
            "short_desc"=>"SB#$id implementation",
            "int_desc"=>"SB#$id implementation",
            "ext_desc"=>"SB#$id implementation",
            "module"=>"SB",
            "module_id"=>$sb->getID(),
        );
        // Create CSR
        $csrid = tldCSR::insert($data);
        if(is_string($csrid)){
            $DEFAULT_ERROR[] = "ERROR: Could not create new CSR. Reason: $csrid";
            continue;
        }
        // Link this sb to the new csr
        $sb->addLinkTo("CSR", $csrid);
        // Confirmation message
        $body.=<<<EOF
<br><a href="/en/private/sales_service/service.php?m[0]=csr&m[1]=view&id=$csrid">CSR#$csrid</a> created for ER#$erid SN#{$er->getSN()}
EOF;
    }
break;
case 'spr':
    if(count($sb->getPartsList())){
    $DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sbs&m[1]=view&m[2]=genspr&id=$id" title="Generate a new Spare Parts Request">New SPR</a>
EOF;
    }
    $DEFAULT_TITLE .="\Spare Parts Requests";
    $DEFAULT_ERROR[] = "WARNING: No spare parts listed in this SB...";
    $report = new tldReportColumnar($sb->getLinksFromHere("SPR"),
            array("xItems"=>array(
                        "id"	=>"ID#",
                        "type"	=>"Module",
                        "item"	=>"Ref#"
                    ),
                    "title"=>"Linked SPRs",
                    "links"=>array("id"=>"/en/private/common/index.php?m[0]=links&m[1]=view&m[2]=redirect&erp=$erp&id=")
                    )
                );
    $body .= $report->fetch();
break;
case 'genspr':
    $rows = $sb->getAffectedEquipment(array("tbd"=>"Y"));
    if(count($rows) == 0){
        $DEFAULT_ERROR[] = "ERROR: No equipment is affected";
        break;
    }
    $parts = $sb->getPartsList();
    if(count($parts) == 0){
        $DEFAULT_ERROR[] = "ERROR: No spare parts list entered for this SB";
        break;
    }
    $sprs = $sb->getLinksFromHere("SPR");
    $erWithSPR = array();
    if(count($sprs)){
        foreach($sprs as $spr){
            $myspr = new tldSPR($spr['item']);
            $ers = $myspr->getLinksFromHere("ER");
            if(count($ers)){
                foreach($ers as $er){
                    $erWithSPR[] = $er['item'];
                }
            }
        }
    }
    // Create labels
	$textInfo = array();
	foreach ($rows as $row)
		$textInfo[$row['id']] = $row['sn'].' - '.$row['model'].' - '.$row['customer_name'].' - '.$row['airport_code'].' '.$row['sales_org'];
	// FORM -->
    $form = new HTML_QuickForm('frmGenSPR', 'post');
    $form->addElement(	'hidden', 'm[0]', 'sbs');
    $form->addElement(	'hidden', 'm[1]', 'view');
    $form->addElement(	'hidden', 'm[2]', 'summary');
    $form->addElement(	'hidden', 'm[3]', 'genspr');
    $form->addElement(	'hidden', 'id', $id);
    // List of ER information.
    $form->addElement(	'header', 'title', 'Select customer information to new SPR');
    $form->addElement(	'select','erids_info',"Customer related to: ", array(""=>"")+$textInfo);
	// List all ER related to the SPR
	$form->addElement(	'header', 'title', 'Select ER(s) to new SPR');
	// LOOP twice to GROUP BY SPR already created or not
	foreach ($rows as $row){
        $a = array();
        if(!in_array($row['id'], $erWithSPR)){
			$a[] = & $form->createElement(	'checkbox', 'erids['.$row['id'].']', 'Category', "Select", "Y");
        	$id_element =& $form->createElement( 'text', 'ids['.$row['id'].']');
	        $id_element->freeze();
	        $a[] = $id_element;
	        $d['ids['.$row['id'].']'] = $textInfo[$row['id']];
	        $form->addGroup($a, null, null, '&nbsp;-&nbsp;');
        }
    }
    foreach ($rows as $row){
        $a = array();
        if(in_array($row['id'], $erWithSPR)){
        	$a[] = & $form->createElement(	'checkbox', 'erids['.$row['id'].']',
                        'SPR Already Created', 'SPR Already Created', array("disabled"=>"disable"));
        	$id_element =& $form->createElement( 'text', 'ids['.$row['id'].']');
	        $id_element->freeze();
	        $a[] = $id_element;
	        $d['ids['.$row['id'].']'] = $textInfo[$row['id']];
	        $form->addGroup($a, null, null, '&nbsp;-&nbsp;');
        }
    }
    $form->setDefaults($d);
    $form->addRule(	'erids_info', 'This is required', 'required');
    $form->addRule(	'sph_id', 'This is required', 'required');
    $form->addElement(	'submit', 'btnSubmit', 'Submit');

    if($form->validate()){
    	$a = tldUtils::cleanupFormInput($form->exportValues());
        $erids = array_keys($a['erids']);
        // If no erids then skip
        if(empty($erids)){
            $DEFAULT_ERROR[] = "ERROR: No equipment selected...";
            break;
        }
        // Assign automaticly customer information to the SPR
        $cu = tldEquipment::getCustomerInfoFromER($a['erids_info']);
        $p['nota'] = "Ship parts for SB#$id application on specified Equipment";
        $p = array_merge($cu,$p);
        // SAVE DATA ON SESSION
        $sess["newSPRfromSB"]["spr_data"]=$p;
        $sess["newSPRfromSB"]["spr_erids"]=$erids;
        $sess["newSPRfromSB"]["sb"]=$id;
        // REDIRECT user to SPR module to be able to create a SPR
        header("Location: /en/private/parts/parts.php?m[0]=spr&m[1]=form&m[2]=new");
    } else {
        if (!$user->isInGroup(["gg_ADMIN", "gg_SERVICE", "gg_PARTS", "role_PSM", "role_PSE", "role_PSA"])) {
            $form->freeze();
        }
        $body .= $form->toHTML();
    }
break;
case 'gennot':
    $rows = $sb->getAffectedEquipment(array("tbd"=>"Y"));
    //reset the session var for this wizard form
    $sess['sbs']['gennot']['erids']=array();
    if(count($rows) == 0){
        $DEFAULT_ERROR[] = "ERROR: No equipment is affected";
        break; //no equipment
    }

    $form = new HTML_QuickForm('frmGenNOT', 'post');
    $form->addElement(	'hidden', 'm[0]', 'sbs');
    $form->addElement(	'hidden', 'm[1]', 'view');
    $form->addElement(	'hidden', 'm[2]', 'summary');
    $form->addElement(	'hidden', 'm[3]', 'gennot2');
    $form->addElement(	'hidden', 'id', $id);
	$form->addElement(	'header', 'title', 'SPH info at the end of the NOT (optionnal)');
	$form->addElement(	'select', 'sph_id',   'SPH',
		array(""=>"")+tldLocation::getSPHList("smartyOptionsIDLocation"));
    $form->addElement(	'header', 'title', 'Affected ER to generate NOT');
    foreach ($rows as $row){
        $a = array();
        $a[] =& $form->createElement(	'checkbox', 'erids['.$row['id'].']',
        	'Category', "Select", "Y");
        $id_element =& $form->createElement(	'text', 'ids['.$row['id'].']');
        $id_element->freeze();
        $a[] = $id_element;
        $d['ids['.$row['id'].']'] = $row['sn'].' - '.$row['customer_name'].' - '
            .$row['airport_code'].' - '.$row['sales_org'];
        $form->addGroup($a, null, null, '&nbsp;-&nbsp;');
    }
    $form->setDefaults($d);
    $form->addElement(	'submit', 'btnSubmit', 'Submit');
    if(!$user->isInGroup(array("gg_ADMIN","gg_SERVICE"))){
        $form->freeze();
    }
    $body .= $form->toHTML();
break;
case 'gennot2':
    if(isset($erids)){
        $sess['sbs']['gennot']['erids'] = array_keys($erids);
    }
    if(empty($sess['sbs']['gennot']['erids'])){
        $DEFAULT_ERROR[] = "ERROR: No equipment selected...";
        break;
    }
    if(!empty($sph_id) && is_numeric($sph_id)){
    	$sph = new tldLocation($sph_id);
    	$smarty->assign('sph',$sph->itsDetails);
    }

    // Get some listing from the list of ER chosen on previous step
    $cust_namas = array(""=>"");
    $extranet_users = array();
    $combinations = array();
    foreach($sess['sbs']['gennot']['erids'] as $erid){
        $er = new tldEquipment($erid);
        // Get the list of customers
        $custName = $er->getCustomerName();
		$cust_namas[$custName] = $custName;
        // Get list of locations
        $location = tldLocation::byLocationName($er->itsDetails['sales_org']);

        // Check if user process should be skipped or not
        foreach($combinations as $combination){
        	if($combination['customer']==$custName
        	&& $combination['ssoid']==$location['id']){
        		continue 2;
        	}
        }
        $combinations[] = array(
        	"customer"=>$custName,
        	"ssoid"=>$location['id']
        );

        // Get User list for this customer & SSO
        $datas = extranetUser::byConstraints(
        	array(
        		'cust.customer_name'=>$custName,
        		'crt.erp_location_id'=>$location['id'],
        		'roles.role'=>"role_ST"
        	)
        );
        // Get the list of affected role_ST of this SB NOT
        foreach($datas as $data){
        	if(empty($data['id'])) continue;
        	$extranet_users[$data['id']] = $data['fullname'];
        }
    }

	// Get list of user to set by default in form
	$exu_default = array();
	foreach($extranet_users as $exuid=>$extranet_user){
		$exu = new extranetUser($exuid);
		$crtRoles = $exu->getCRTnRoles();
		// foreach crt roles look fl_NO_SB_NOT & role_ST for each combination
		$ST = 0;
		$FL = 0;
		foreach($crtRoles as $role){
			foreach($combinations as $combination){
				if($role['erp_location_id']==$combination['ssoid']
				&& $role['customer_name']==$combination['customer']){
					if($role['role']=="role_ST") $ST++;
					if($role['role']=="fl_NO_SB_NOT") $FL++;
				}
			}
		}
		// Finally
		if($ST>$FL){
    		$exu_default[] = $exuid;
    	}
	}

	// Get FORM
    $NUM_NEW_USERS = 5;
    $form = new HTML_QuickForm('frmGenNOT2', 'post','','','',true);
    $form->addElement(	'header', 'title', 'Generate NOT');
    $form->addElement(	'hidden', 'm[0]', 'sbs');
    $form->addElement(	'hidden', 'm[1]', 'view');
    $form->addElement(	'hidden', 'm[2]', 'summary');
    $form->addElement(	'hidden', 'm[3]', 'gennot2');
    $form->addElement(	'hidden', 'id', $id);
    $form->addElement(	'text', 'sub', 'Subject', array("size"=>100));
    $form->addElement(	'textarea', 'msg', 'Message',
		array("wrap"=>"VIRTUAL", "cols"=>"80", "rows"=>"20")
	);
    if(count($extranet_users)){
        $exu =& $form->addElement('advmultiselect', 'ex_users', null, $extranet_users,
			array('size' => 15,
                 'class' => 'pool',
                 'style' => 'width:300px;'
			)
        );
        $exu->setLabel(array('Send to customers', 'Addressbook', 'Recipients (max 20)'));
        $exu->setButtonAttributes('add',    array('value' => '-->>', 'class' => 'inputCommand'));
        $exu->setButtonAttributes('remove', array('value' => '<<--', 'class' => 'inputCommand'));
        // By default put users that have the role_ST and not fl_NO_SB_NOT
        $d['ex_users']=$exu_default;
    }else{
        $DEFAULT_ERROR[] = "WARNING: There are no extranet accounts linked to one or more of the equipment you selected";
    }
    $ams =& $form->addElement('advmultiselect', 'cc_users', null,
		tldDirectory::getUserlist("smartyOptions"),
		array(
			'size' => 15,
			'class' => 'pool',
			'style' => 'width:300px;'
		)
    );
    $ams->setLabel(array('CC TLD', 'Addressbook', 'CC (max 20)'));
    $ams->setButtonAttributes('add',
    	array('value' => '-->>', 'class' => 'inputCommand')
    );
    $ams->setButtonAttributes('remove',
    	array('value' => '<<--', 'class' => 'inputCommand')
    );

    // Create list of customer names and their CRT for the drop down box below for new extranet users
	$custList=array();
	$crtList=array();
	foreach($cust_namas as $name){
		$cust = new tldCustomer($name);
		$data=$cust->getCRT();
		$custList[$cust->itsID] = $name;
		foreach($data as $crt){
			$crtList[$cust->itsID][$crt['id']]="CRT#{$crt['id']} - {$crt['erp']} - {$crt['cuno']}";
		}
	}

    // New extranet user fields
    $form->addElement(	'header', 'title', '<span style="color:red;">IMPORTANT NOTICE - When creating a new extranet user, 
    	please make sure to fill every fields. Otherwise it will not be saved.</span>');
    for($i=0; $i<$NUM_NEW_USERS; $i++){
        $form->addElement(	'header', 'title', 'New extranet user #'.($i+1));
        $sel =& $form->addElement('hierselect', "cust[$i][customer_crt]", 'Customer Name & CRT');
		$sel->setOptions(array(array(""=>"")+$custList, array(""=>"")+$crtList));
        $form->addElement(	'text', "cust[$i][firstname]", 'First Name', array('size'=>50));
        $form->addElement(	'text', "cust[$i][lastname]", 'Last Name', array('size'=>50));
        $form->addElement(	'text', "cust[$i][email]", 'Email', array('size'=>50));
        $form->addRule("cust[$i][email]", 'E-Mail for New extranet user #'.($i+1).' not valid', 'email', null, 'client');
    }

    $erlist .= sprintf("%-20s%-20s%-20s%-20s", "TLD Serial Number", "Customer Asset#", "Model#", "Last Known Location")."\n";
    $modelList = array();
    foreach($sess['sbs']['gennot']['erids'] as $erid){
        $er = new tldEquipment($erid);
        $erHeader = $er->getHeader();
        $erlist .= sprintf("%-20s%-20s%-20s%-20s", $erHeader['sn'], $erHeader['cust_asset_num'], $erHeader['model'], $erHeader['airport_code'])."\n";
        if(!in_array($erHeader['model'], $modelList)){
            $modelList[] = $erHeader['model'];
        }
    }
    $d['sub'] = "TLD {$header['urgency_fullname']} SB#$id for ".implode(',', $modelList);
    // Create default notification
    $smarty->assign("erlist", $erlist);
    $smarty->assign("sb", $sb->getHeader());
    $smarty->assign("user", $user->getHeader());
    $d['msg'] = $smarty->fetch("$PATH/sbs/sb.notification.msg.tpl");
    // Set defaults
    $form->setDefaults($d);
	// Set required
	$fields=array("sub","msg");
	foreach($fields as $field) $form->addRule($field, 'This is required', 'required');
    $form->addElement(	'submit', 'btnSubmit', 'Submit');

    if (!$form->validate()){
        $body .= $form->toHTML();
        break;
    }

//IF THE FORM IS OK then proceed
    $vars = tldUtils::cleanupFormInput($form->exportValues());

    //process input
    //add the current user to the email list.
    $tld_list[] = $user->getEmail();
    //anyone to cc to?
    if(count($cc_users ?? [])){
        foreach($cc_users as $cc_user){
            $myUser = new tldUser($cc_user);
            $tld_list[] = $myUser->getEmail();
        }
    }
    //what existing users to send to
    $ext_list = array();
    if(count($ex_users)){
        foreach($ex_users as $ex_user){
            $myExUser = new extranetUser($ex_user);
            $ext_list[] = $myExUser->getEmail();
        }
    }

    // Any new users to create and send to?
    // Use a second list to send a different notification (to push customer get an account on line)
    $new_ext_list = array();
    for($i=0; $i<$NUM_NEW_USERS; $i++){
		$existing = NULL;
        // check if every fields have been filled, else do not insert extranet user
    	if(!empty($vars["cust"][$i]['customer_crt'][0]) && !empty($vars["cust"][$i]['email'])
    	&& !empty($vars["cust"][$i]['firstname']) && !empty($vars["cust"][$i]['lastname']) ){
    		$existing = extranetUser::byUserid($vars["cust"][$i]['email']);
    		if(!empty($existing)){
    			$DEFAULT_ERROR[] = "ERROR: Extranet account already exists for ".implode(" ", $vars["cust"][$i]);
    			continue;
    		}
    		$vars["cust"][$i]['customer_name']=$custList[$vars["cust"][$i]['customer_crt'][0]];
    		$vars["cust"][$i]['userid']=$vars["cust"][$i]['email'];
            $vars["cust"][$i]['enable']='N';

            global $kernel;

            $xuProfilePayload = [];

            try {
                $client = $kernel->getContainer()->get(Client::class);
            } catch (Exception $e) {
                $DEFAULT_ERROR[] = "ERROR: Could not get Client. Reason: $e";
                break;
            }

            try {
                $customer = $client->findOneBy('sales/customers', ['q' => $vars["cust"][$i]['customer_name']]);
                $xuProfilePayload['customer'] = $customer['@id'];
                $xuProfilePayload['company_name'] = $customer['name'];
            } catch (Exception $e) {
                $DEFAULT_ERROR[] = 'ERROR: Could not get customer. Reason: ' . $e->getMessage();
                break;
            }

            $lastname = mb_convert_encoding($vars["cust"][$i]['lastname'], 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8');
            $firstname = mb_convert_encoding($vars["cust"][$i]['firstname'], 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8');
            $username = mb_convert_encoding($vars["cust"][$i]['email'], 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8');

            try {
                $extranetUser = $client->save('sales/extranet_users',
                    [
                        'email' => $username,
                        'disabled' => $vars["cust"][$i]['enable'] === 'N' ? true : false,
                        'username' => $username,
                        'lastname' => $lastname,
                        'firstname' => $firstname,
                        'extranetUserProfile' => $xuProfilePayload,
                    ]);
            } catch (ClientException $e) {
                $errors = json_decode($e->getResponse()->getContent(), true);
                $DEFAULT_ERROR[] = 'ERROR: Could not add Extranet User. Reason: '.$errors['hydra:description'];
                break;
            }

            $ext_user = new extranetUser($extranetUser['legacyId']);
            $ext_user->addLogEntry("Extranet user created from SB NOT system");
            // Add CRT and role
            try {
                $crt = $client->findOneBy('sales/customer_relationship_teams', ['legacyId' => $vars["cust"][$i]['customer_crt'][1] ]);
            } catch (Exception $e) {
                $DEFAULT_ERROR[] = "ERROR: Could not get CRT. Reason: " . $e->getMessage();
                $body .= $form->toHtml();
                break;
            }
            $crt_id = $crt['legacyId'];
            // Default roles
            $roles_to_add = array("role_ST","fl_NO_SB_NOT");
            // Add roles to contact
            foreach($roles_to_add as $role){
                // Check if not already setup
                try {
                    $extranetUserGroup = $client->findOneBy('sales/extranet_user_groups', ['name' => $role]);
                } catch (RangeException $e) {
                    $DEFAULT_ERROR[] = "ERROR: Could not get Extranet User Group. Reason: " . $e->getMessage();
                    break;
                }

                try {
                    $client->findOneBy('sales/extranet_user_acls',
                        [
                            'crt.erpLocation' => $crt['erpLocation']['@id'],
                            'crt.customer' => $crt['customer']['@id'],
                            'extranetUser' => $extranetUser['@id'],
                            'extranetUserGroup.name' => $role
                        ]
                    );
                    $addRole = false;
                    continue;
                } catch (RangeException $e) {
                    $addRole = true;
                }

                if ($addRole) {
                    try {
                        $client->save('sales/extranet_user_acls',
                            [
                                'extranetUserGroup' => $extranetUserGroup['@id'],
                                'crt' => $crt['@id'],
                                'extranetUser' => $extranetUser['@id'],
                            ]
                        );
                    } catch (ClientException $e) {
                        $errors = json_decode($e->getResponse()->getContent(), true);
                        $DEFAULT_ERROR[] = 'ERROR: Could not role '.$role.' for CRT #'.$crt_id.'. Reason: '.$errors['hydra:description'];
                        break;
                    }
                    $ext_user->addLogEntry("Role $role added for CRT#$crt_id");
                }
            }
            $new_ext_list[] = $vars['cust'][$i]['email'];
        }
    }
    // Send the NOT
    // if only tld people for the NOT
	if(empty($ext_list) && empty($new_ext_list) && !empty($tld_list)){
	    $TO = array_unique($tld_list);
	    $message = wordwrap(implode(", ", $TO)."\n\n<br><pre>".stripslashes($msg)."</pre>");
	    $e1 = tldUtils::emailAttachment(implode(',', $TO),
	        $user->getEmail(),
	        $vars['sub'],
	        $message
	    );
    }
    // To existing extranet user
    if(!empty($ext_list)){
	    $TO = array_unique(array_merge($ext_list,$tld_list));
	    $message = wordwrap(implode(", ", $TO)."\n\n<br><pre>".stripslashes($msg)."</pre>");
	    $e1 = tldUtils::emailAttachment(implode(',', $TO),
	        $user->getEmail(),
	        $vars['sub'],
	        $message
	    );
    }
	// To new extranets users, add message to inform them how to register
    if(!empty($new_ext_list)){
    	$TO = array_unique(array_merge($new_ext_list,$tld_list));
    	$message2 = $smarty->fetch("$PATH/sbs/sb.extranet.msg.tpl").wordwrap(implode(", ", $TO)."\n\n<br><pre>".stripslashes($msg)."</pre>");
    	$e2 = tldUtils::emailAttachment(implode(',', $TO),
            $user->getEmail(),
            $vars['sub'],
            $message2
    	);
	}
    if($e1 || $e2){
        $logid = $sb->addLogEntry($user->getID(),TldDatabase::escape($message.$message2));
        if(!is_string($logid)){
            $log = new tldModLog($logid);
            //add links to the ERs
            foreach($sess['sbs']['gennot']['erids'] as $erid){
                $log->addLinkTo("ER", $erid);
            }
            $body .= "Email notification sent, copy has been saved to the logs";
            if(!empty($new_ext_list)){
            	$body .= "<br/>Extranet users created successfully for:<br/>".implode('<br/>',$new_ext_list);
            }
        }
        else $DEFAULT_ERROR[] = "INTERNAL ERROR: could not save email notification on logs...";
    }else{
        $DEFAULT_ERROR[] = "ERROR: could not send email notification...";
    }
    $body .= getSummaryPage();
break;
case 'spr_sum':
	$report = new tldReportMultiLevel($sb->getSummary(NULL,"SPR"),
		array("sales_org","customer_name","sn"),
		array(
            "airport_code"	=>"Airport Code",
            "done"			=>"Done?",
            "spr_id"		=>"SPR#",
            "spr_status"	=>"SPR Status"
		),
		array(
			"passField"=>"id",
			"title"=>"Summary",
			"url"=>"$php_self?m[0]=equipment&m[1]=view&id=",
			"showNumberOfRowsByLevel"=>array(1),
			"links"=>array("spr_id"=>"/en/private/parts/parts.php?m[0]=spr&m[1]=view&id=")
		)
	);
	$body .= $report->fetch();
break;
case 'csr_sum':
	$report = new tldReportMultiLevel(
	    $sb->getSummary(NULL,"CSR"),
		array("sales_org","customer_name","sn"),
		array(
            "airport_code"	=>"Airport Code",
            "done"			=>"Done?",
            "csr_id"		=>"CSR#",
			"csr_status"	=>"CSR Status"
		),
		array(
			"passField"=>"id",
			"title"=>"Summary",
			"url"=>"$php_self?m[0]=equipment&m[1]=view&id=",
			"showNumberOfRowsByLevel"=>array(1),
			"links"=>array("csr_id"=>"/en/private/sales_service/service.php?m[0]=csr&m[1]=view&id=")
		)
	);
	$body .= $report->fetch();
break;
case 'not_sum':
	$report = new tldReportMultiLevel($sb->getSummary(NULL,"NOT"),
		array("sales_org","customer_name","sn"),
		array(
            "airport_code"	=>"Airport Code",
            "done"			=>"Done?",
            "log_id"		=>"Log#",
            "log_dt"		=>"Log date"
		),
		array(
			"passField"=>"id",
			"title"=>"Summary",
			"url"=>"$php_self?m[0]=equipment&m[1]=view&id=",
			"showNumberOfRowsByLevel"=>array(1),
			"links"=>array("log_id"=>"/en/private/common/index.php?m[0]=logs&m[1]=view&id=")
		)
	);
	$body .= $report->fetch();
break;
case 'cla_sum':
     $body .= getSummaryPage();
}
?>
