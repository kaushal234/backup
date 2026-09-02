<?php
include_once('HTML/QuickForm/advmultiselect.php');

if(empty($id)){
   $DEFAULT_ERROR[] = "ERROR: No SB# set";
    return; //get out of this include
}
$sb = new tldSB($id);
if($sb->isEmpty()){
   $DEFAULT_ERROR[] = "SB#$id not found...";
   return;
}
$header = $sb->getHeader();
$DEFAULT_TITLE .= "\SB#$id";
$DEFAULT_MENU.=<<<EOF
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sbs&m[1]=view&id=$id">General</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sbs&m[1]=view&m[2]=summary&id=$id" title="Summary">Summary</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sbs&m[1]=view&m[2]=tasks&id=$id" title="Linked Tasks">Tasks</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sbs&m[1]=view&m[2]=bps&id=$id" title="Linked Processes">BP</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sbs&m[1]=view&m[2]=files&id=$id" title="File attachments">Files</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sbs&m[1]=view&m[2]=log&id=$id" title="Activity log">Log</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sbs&m[1]=view&m[2]=links&id=$id" title="Links">Links</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sbs&m[1]=view&m[2]=parts&id=$id" title="Parts List">Parts</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sbs&m[1]=view&m[2]=sn&id=$id" title="List of affected equipment">ER</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sbs&m[1]=view&m[2]=customers&id=$id" title="List of affected customers">Cust</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sbs&m[1]=view&m[2]=sso&id=$id" title="List of affected SSO">SSO</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sbs&m[1]=view&m[2]=done&id=$id" title="List of equipment with SB performed on it">Done</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sbs&m[1]=view&m[2]=coverpage&id=$id" title="PDF Coverpage">Coverpage</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sbs&m[1]=view&m[2]=change_status&id=$id" title="Change Status">Status</a>
EOF;

if($sb->getUrgency() != "SB:COMPULSORY"){
$DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=sbs&m[1]=view&m[2]=tbd&id=$id" title="To Be Done list">TBD</a>
EOF;
}
if($user->isInGroup(array("gg_SUPPORT","gg_ADMIN"))){
$DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=sbs&m[1]=view&m[2]=genlinks&id=$id" title="Create links to ERs">Gen Links</a>
EOF;
}
if($user->isInGroup(array("gg_SUPPORT","superuser"))){
$DEFAULT_MENU.=<<<EOF
&nbsp;|&nbsp;<a href="/en/private/product_support/sbs/sbs_admin.php?mode=record_view&form_type=main_tpl&id=$id">Edit</a>
EOF;
}

// List ER Sales org linked to the user domain
$domain_list = array(
    "tld-america.com"=>array("TLD AME","TLD LAC"),
    "tld-europe.com"=>array("TLD EUR"),
    "tld-meai.com"=>array("TLD MEAI"),
    "tld-asia.com"=>array("TLD SIN","TLD ASI","TLD CHI"),
    "tld-group.com"=>array("TLD AME","TLD EUR","TLD MEAI","TLD SIN","TLD ASI","TLD CHI")
);

if($sb->getStatus()=='LOCKED'){
    $DEFAULT_ERROR[] = "This SB has been transfered to the SB3 module following MIS Project#1859";
    $DEFAULT_ERROR[] = "<a href=\"$php_self?m[0]=sb&m[1]=view&id=$id\">Click here to access this SB in the new SB3 module</a>";
}

switch($m[2]){
case 'genlinks':
    if(!$user->isInGroup(array("gg_ADMIN","gg_SUPPORT"))){
        $DEFAULT_ERROR[] = "ERROR: You are not authorised to generate ER links...";
    }
	if($header["status"]=="CLOSED"){
		$DEFAULT_ERROR[] = "ERROR: You can not modify TBD list with this SB status.";
		break;
	}
    $form = new HTML_QuickForm('frmGenlinks', 'post');
    $form->addElement(	'header', 'title', 'Generate ER Links');
    $form->addElement(	'hidden', 'm[0]', 'sbs');
    $form->addElement(	'hidden', 'm[1]', 'view');
    $form->addElement(	'hidden', 'm[2]', 'genlinks');
    $form->addElement(	'hidden', 'id', $id);
    $form->addElement(	'select', 'confirm', 'Confirm', array(""=>"", "Y"=>"Y"));
    $form->addElement(	'submit', 'btnSubmit', 'Submit');
    $form->addRule('confirm', 'This field is required...', 'required');

    if ($form->validate()){
        if($confirm=='Y'){
            $e = $sb->clearAffectedList($id);
            $e .= $sb->generateAffectedList($id);
            if($e){
                $DEFAULT_ERROR[] = $e;
            }
            $body .= getAffectedPage();
        }
    }else{
        $DEFAULT_ERROR[] = "!!WARNING!! Are you sure you want to generate ER links? All existing links will be cleared first.";
        $body .= $form->toHTML();
    }

break;
case 'files':
    $DEFAULT_TITLE .= "\Attachments";
    switch($m[3]){
    case 'getFile':
        $template="NO_TEMPLATE";
        $sb->outFile();
    break;
    case 'getAttachment':
        $template="NO_TEMPLATE";
        $sb->outAttachment($fileid);
    break;
    }
    $report = new tldReportColumnar(
        $sb->getFiles(),
        array(
            "xItems"=>array(
                "id"			=>"ID",
                "date"			=> "Date",
                "description"	=> "Description",
                "filename"		=> "Filename"
            ),
            "title"=>"Attachments",
            "links"=>array(
                "id"=>"$php_self?m[0]=sbs&m[1]=view&m[2]=files&m[3]=getAttachment&id=$id&fileid="
            )
        )
    );
    $body .= $report->fetch();
break;
case 'customers':
    $a = array(
        "customer_name"=>"Customer",
        "num_cust_er_affected"=>"Number of affected equipment",
        "num_done"=>"Number done",
        "num_online"=>"Number of Extranet Accounts"
    );
    if($sb->getUrgency() == "SB:RECOMMENDED"){
        $a["num_tbd"] = "Number TBD done";
    }
    $report = new tldReportColumnar(
        $sb->getAffectedCustomers(),
        array(
            "xItems"=>$a,
            "title"=>"Affected customers"
        )
    );
    $body .= $report->fetch();
break;
case 'sso':
    $report = new tldReportColumnar(
        $sb->getAffectedSSO(),
        array(
            "xItems"=>array(
                "sales_org"=>"SSO",
                "sso_er_affected"=>"Number of affected equipment",
                "num_done"=>"Number done"
            ),
            "title"=>"Affected SSO"
        )
    );
    $body .= $report->fetch();
break;
case 'sn':
    $DEFAULT_TITLE .= "\Affected Equipment";
    $body .= getAffectedPage();
break;
case 'log':
    $DEFAULT_TITLE .="\Log";

    $report = new tldReportColumnar(
        $sb->getLog(),
        array(
            "xItems"=>array(
                "id"=>"ID#",
                "date"=>"Date",
                "poster_fullname"=>"Poster",
                "comment"=>"Comment"
            )
        )
    );
    $body .= $report->fetch();
break;
case 'summary':
    include("summary.inc.php");
break;
case 'change_status':
	if($user->isInGroup(array("gg_SUPPORT","gg_ADMIN"))){
		include("change_status.inc.php");
	}else{
		$DEFAULT_ERROR[] = "ERROR: You are not permitted to change SB status...";
	}
break;
case 'links':
    $DEFAULT_TITLE .="\Links";
    $DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sbs&m[1]=view&m[2]=links&id=$id">Links</a>
&nbsp;|&nbsp;<a href="/en/private/common/index.php?m[0]=links&m[1]=form&m[2]=newLink&module=SB&parent_id=$id">New Link</a>
&nbsp;|&nbsp;<a href="/en/private/common/index.php?m[0]=links&m[1]=graphviz&mod=SB&pid=$id">Map</a>
EOF;

    switch($m[3]){
    case 'map':
        $body .=<<<EOF
<img src="/en/private/common/index.php?m[0]=links&m[1]=graphviz&type=gif&mod=SB&pid=$id">
EOF;
    break;
    default:
        $report = new tldReportColumnar(
            $sb->getLinksFromHere(),
            array(
                "xItems"=>array(
                    "id"=>"ID#",
                    "type"	=>"Module",
                    "item"	=>"Ref#",
                    "dsca"  =>"Description"
                ),
                "title"=>"Links FROM here...",
                "links"=>array(
                    "id"=>"/en/private/common/index.php?m[0]=links&m[1]=view&m[2]=redirect&erp=$erp&id="
                )
            )
        );
        $body .= $report->fetch();
        $report = new tldReportColumnar($sb->getLinksToHere(),
                array("xItems"=>array(
                            "id"=>"ID#",
                            "module"	=>"Module",
                            "parent_id"	=>"Ref#",
                            "dsca"  =>"Description"
                        ),
                        "title"=>"Links TO Here...",
                        "links"=>array("id"=>"/en/private/common/index.php?m[0]=links&m[1]=view&m[2]=redirect&reversed=1&erp=$erp&id=")
                        )
                    );
        $body .= $report->fetch();
    }
break;
case 'tbd':
    $rows = $sb->getAffectedEquipment();
    if(count($rows) == 0){
    	$DEFAULT_ERROR[] = "ERROR: No equipments affected!";
    	break;
    }
    // Form to select by default ER(s) by ALL, by Sales Org or by Customer name
    $CUST = array(""=>"");
    $SSO = array(""=>"");
    foreach($rows as $row){
    	if(!in_array($row['customer_name'],$CUST))
    		$CUST[$row['customer_name']]=$row['customer_name'];
    	if(!in_array($row['sales_org'],$SSO))
    		$SSO[$row['sales_org']]=$row['sales_org'];
    }
    $formTool = new HTML_QuickForm('frmSelectTool', 'get');
    $formTool->addElement(	'hidden', 'm[0]', 'sbs');
    $formTool->addElement(	'hidden', 'm[1]', 'view');
    $formTool->addElement(	'hidden', 'm[2]', 'tbd');
    $formTool->addElement(	'hidden', 'id', $id);
    $formTool->addElement(	'header', 'title', 'Selection tool');
    $formTool->addElement(	'submit', 'mode', 'Set ALL units to Y');
    $formTool->addElement(	'submit', 'mode', 'Set ALL units to N');
    $formTool->addElement(	'header', 'title', 'Selection tool by criteria');
    $formTool->addElement(	'select', 'ByCUST', "By Customer", $CUST);
    $formTool->addElement(	'submit', 'mode', 'Set Y units by customer');
    $formTool->addElement(	'submit', 'mode', 'Set N units by customer');
    if($formTool->validate()){
    	$default = $formTool->exportValues();
    }

    // List ER affected to put TBD
    $form = new HTML_QuickForm('frmTBD', 'post');
    $form->addElement(	'hidden', 'm[0]', 'sbs');
    $form->addElement(	'hidden', 'm[1]', 'view');
    $form->addElement(	'hidden', 'm[2]', 'tbd');
    $form->addElement(	'hidden', 'id', $id);
    $form->addElement(	'header', 'title', 'TBD');
	$form->addElement(	'submit', 'btnSubmit', 'Submit');

	$er_list_sso=0;

    foreach ($rows as $row){
   		// Check if the ER sales org can be managed by the user using his domain name.
    	if(in_array($row['sales_org'],$domain_list[$user->getDomain()]) || $user->isInGroup(array('superuser'))){
	        $a = array();
	        $el =& $form->createElement(	'select', 'erids['.$row['id'].']',
	                        'Category', array("?"=>"?", "N"=>"N","Y"=>"Y"), $option);
	        // Put default value
			if(!empty($default) && ($default['mode']=='Set ALL units to Y' ||
			($default['mode']=='Set Y units by customer' && $default['ByCUST']==$row['customer_name']))){
				$d['erids['.$row['id'].']'] = "Y";
	    	}
	    	elseif(!empty($default) && ($default['mode']=='Set ALL units to N' ||
	    	($default['mode']=='Set N units by customer' && $default['ByCUST']==$row['customer_name']))){
	    		$d['erids['.$row['id'].']'] = "N";
	    	}else{
	    		$d['erids['.$row['id'].']'] = $row['tbd'];
	    	}
			$a[] = $el;
			// Create labels
	        $id_element =& $form->createElement(	'text', 'ids['.$row['id'].']', $row['id']);
	        $id_element->freeze();
	        $a[] = $id_element;
	        $d['ids['.$row['id'].']'] = $row['id'];

	        $sn_element =& $form->createElement(	'text', 'sns['.$row['id'].']', $row['sn']);
	        $sn_element->freeze();
	        $a[] = $sn_element;
	        $d['sns['.$row['id'].']'] = $row['sn'];

	        $cus_element =& $form->createElement(	'text', 'cus['.$row['id'].']', $row['customer_name']);
	        $cus_element->freeze();
	        $a[] = $cus_element;
	        $d['cus['.$row['id'].']'] = $row['customer_name'];

	        $aps_element =& $form->createElement(	'text', 'aps['.$row['id'].']', $row['airport_code']);
	        $aps_element->freeze();
	        $a[] = $aps_element;
	        $d['aps['.$row['id'].']'] = $row['airport_code'];

	        $sso_element =& $form->createElement(	'text', 'ssos['.$row['id'].']', $row['sales_org']);
	        $sso_element->freeze();
	        $a[] = $sso_element;
	        $d['ssos['.$row['id'].']'] = $row['sales_org'];

	        $form->addGroup($a, null, null, '&nbsp;-&nbsp;');
	        $er_list_sso++;
    	}
    }
    $form->setDefaults($d);
    $form->addElement(	'reset', 'btnReset', 'Reset');
    $form->addElement(	'submit', 'btnSubmit', 'Submit');

    if(!$form->validate()){
        if($er_list_sso==0){
            $DEFAULT_ERROR[] = "ERROR: No ER impacted for ".implode(" or ",$domain_list[$user->getDomain()]);
        }
        $body .= $formTool->toHTML();
        $body .= $form->toHTML();
        break;
    }

    if(!$user->isInGroup(array("role_EVP","gg_ADMIN"))){
		$DEFAULT_ERROR[] = "ERROR: You do not have permissions to make TBD list.";
		break;
	}
	if( !in_array($header["status"],array("ER_SELECTION","IMPLEMENTATION")) ){
		$DEFAULT_ERROR[] = "ERROR: You can not modify TBD list with this SB status.";
		break;
	}
    # If the form validates then freeze the data
    foreach ($rows as $row){
        $resp = $erids[$row['id']];
        if(in_array($resp, array('Y', 'N'))){
            $e = $sb->addTBD($row['id'], $resp);
            if(is_string($e)) $DEFAULT_ERROR[] = $e;
            else $sb->addLogEntry($user->getID(), "Added/updated TBD list for ".$row['id']." to $resp");
        }
    }
    $body .= "<br/>TBD process finished !";
    $body .= getAffectedPage();
break;
case 'done':
    $rows = $sb->getAffectedEquipment();

    if(count($rows) == 0) break; //no equipment
    $form = new HTML_QuickForm('frmDone', 'post');
    $form->addElement(	'header', 'title', 'Done');
    $form->addElement(	'hidden', 'm[0]', 'sbs');
    $form->addElement(	'hidden', 'm[1]', 'view');
    $form->addElement(	'hidden', 'm[2]', 'done');
    $form->addElement(	'hidden', 'id', $id);

    foreach ($rows as $row){
        $a = array();
        $a[] =& $form->createElement(	'select', 'erids['.$row['id'].']',
                        'Category', array("N"=>"N","Y"=>"Y"));
        $d['erids['.$row['id'].']'] = $row['done'];

        $id_element =& $form->createElement(	'text', 'ids['.$row['id'].']', $row['id']);
        $id_element->freeze();
        $a[] = $id_element;
        $d['ids['.$row['id'].']'] = $row['id'];

        $sn_element =& $form->createElement(	'text', 'sns['.$row['id'].']', $row['sn']);
        $sn_element->freeze();
        $a[] = $sn_element;
        $d['sns['.$row['id'].']'] = $row['sn'];

        $cus_element =& $form->createElement(	'text', 'cus['.$row['id'].']', $row['customer_name']);
        $cus_element->freeze();
        $a[] = $cus_element;
        $d['cus['.$row['id'].']'] = $row['customer_name'];

        $aps_element =& $form->createElement(	'text', 'aps['.$row['id'].']', $row['airport_code']);
        $aps_element->freeze();
        $a[] = $aps_element;
        $d['aps['.$row['id'].']'] = $row['airport_code'];

        $sso_element =& $form->createElement(	'text', 'ssos['.$row['id'].']', $row['sales_org']);
        $sso_element->freeze();
        $a[] = $sso_element;
        $d['ssos['.$row['id'].']'] = $row['sales_org'];

        $form->addGroup($a, null, null, '&nbsp;-&nbsp;');
    }
    $form->setDefaults($d);
    $form->addElement(	'submit', 'btnSubmit', 'Submit');
    if ($form->validate()){
        # If the form validates then freeze the data
        foreach ($rows as $row){
            if($erids[$row['id']] <> $row['done']){
                if($row['done'] == 'Y'){
                    $e = $sb->removeDone($row['id']);
                    $sb->addLogEntry($user->getID(), "Removed ".$row['sn']." from DONE list");
                }else{
                    $e = $sb->addDone($row['id']);
                    $sb->addLogEntry($user->getID(), "Added ".$row['sn']." to DONE list");
                }
                if($e) $DEFAULT_ERROR[] = $e;
            }
        }
        $body .= getAffectedPage();
    }else{
        if(!$user->isInGroup(array("gg_ADMIN","gg_SUPPORT","gg_SERVICE","gg_PARTS"))){
            $form->freeze();
        }
        $body .= $form->toHTML();
    }
break;
case "tasks":
    $DEFAULT_TITLE .="\Tasks";
    $DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=form&m[2]=newTask&module=SB&parent_id=$id">New Task</a>
EOF;
    $sess["calendar"]["tasks"] = $sb->getTasks();
    $form = new tldReportMultiLevel(
        $sess["calendar"]["tasks"],
        array("status","due_date"),
        array(
            "id"				=>"Task#",
            "status"			=>"Status",
            "due_date"			=>"Due",
            "task"				=>"Task",
            "assignee_fullname"	=>"Assignee"
        ),
        array(
            "passField"=>"id",
            "title"=>"Tasks",
            "url"=>"/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id="
        )
    );
    $body .= $form->fetch();
break;
case "bps":
	$DEFAULT_TITLE .="\Processes";
	$body .= _viewBPSTab($sb);
break;
case 'parts':
    $DEFAULT_TITLE .= "\Parts List";
    $DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/en/private/parts/parts.php?m[0]=cart&m[1]=import&m[2]=bySB&id=$id">Add to Cart</a>
EOF;
    $factory_erp = tldLocation::getERPByLocation($header['factory']);
    $report = new tldReportColumnar(
        $sb->getPartsList(),
        array(
            "xItems"=>array(
                "pn" => "Part Number",
                "dsca"	=> "Description",
                "qty"	=> "Qty",
                "um"	=> "UM"
                ),
            "title"=>"Parts List",
            "links"=>array(
                "pn"=>array(
                    "url"=>"/en/private/manufacturing/eng/dev.php?m[0]=bom&m[1]=view&erp=$factory_erp",
                    "params"=>array("pn"=>"pn")
                )
            )
        )
    );
    $body .= $report->fetch();
break;
case 'coverpage':
    $form = new tldAssocTable(
        $header,
        array(
            "id"				=>"SB#",
            "urgency"			=>"Severity",
            "sb_type"			=>"Type",
            "title"				=>"Title",
            "description"		=>"Description",
            "entered_date"		=>"Date"
        ),
        array(
            "title"=>"Service Bulletin #$id"
        )
    );
    $body .= $form->fetch();
    $report = new tldReportColumnar(
        $sb->getDetail(),
        array(
            "xItems"=>array(
                "model"		=> "Model",
                "sn_from"	=> "From SN",
                "sn_to"		=> "to SN",
                "sn_list"	=> "SN List"
            ),
            "title"=>"Affected Equipment",
            "sortable"=>"no"
        )
    );
    $body .= $report->fetch();
    $report = new tldReportColumnar(
        $sb->getPartsList(),
        array(
            "xItems"=>array(
                "pn" => "Part Number",
                "dsca"	=> "Description",
                "qty"	=> "Qty",
                "um"	=> "UM"
            ),
            "title"=>"Parts List",
            "sortable"=>"no"
        )
    );
    $body .= $report->fetch();

    include_once("publications.inc.php");
    $smarty->assign("body", $body);
    $smarty->assign("title", "Service Bulletin #$id");
    $result = $smarty->fetch("intranet.plain.tpl");

    $conv = new html2pdf($result, "https://www.tld-gse.com", $WEB_ROOT);
    $conv->outFile("sbs${id}_coverpage.pdf");
    exit;
break;
default:
	$body .= _getGeneralTab();
}
function _getGeneralTab(){
	global $DEFAULT_MENU, $DEFAULT_ERROR, $header, $sb;
    $DEFAULT_TITLE .= "\General";

    if($sb->getUrgency() == "SB:RECOMMENDED"){
        if($header['numAffected'] > $header['num_tbd']){
            $DEFAULT_ERROR[] = "WARNING: Not all SSO responses received yet for this RECOMMENDED SB...";
        }
    }

    $form = new tldAssocTable(
        $header,
        array(
            "id"				=>"SB#",
            "vstatus"			=>"Status",
            "urgency"			=>"Severity",
            "factory"			=>"Factory",
            "sb_type"			=>"Type",
            "title"				=>"Title",
            "description"		=>"Description",
            "entered_date"		=>"Date",
            "numAffected"		=>"Num Affected",
            "num_tbd"			=>"Num TBD",
            "numDone"			=>"Num Done"
        ),
        array(
            "title"=>"General"
        )
    );
    return $form->fetch();
}
function _viewBPSTab($sb){
	$form = new tldReportColumnar(
        $sb->getBPS("ALL"),
        array(
            "xItems"=>array(
                "id"	=>"BP#",
                "status"		=>"Status",
                "dt_opened"		=>"Date Opened",
                "dt_closed"		=>"Date Closed",
                "short_desc"	=>"Description"
            ),
            "title"=>"Processes",
            "links"=>array(
                "id"=>"/en/private/calendar/calendar.php?m[0]=bp&m[1]=view&id="
            )
        )
    );
	return $form->fetch();
}

?>
