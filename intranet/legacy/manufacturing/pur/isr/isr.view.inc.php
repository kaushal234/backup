<?php
if(empty($id) || !is_numeric($id)){
	$DEFAULT_ERROR[]=  "ERROR: ISR id empty or invalid";
	return;
}
$isr = new tldISR($id);
if($isr->isEmpty()){
	$DEFAULT_ERROR[]=  "ERROR: No ISR#$id found...";
	return;
}
$header = $isr->getHeader();

$DEFAULT_TITLE .= "\ISR#$id";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=isr&m[1]=view&id=$id">General</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=isr&m[1]=view&m[2]=edit&id=$id" title="Edit this ISR">Edit</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=isr&m[1]=view&m[2]=lines&id=$id" title="ISR Lines">Lines</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=isr&m[1]=view&m[2]=tasks&id=$id" title="Related tasks">Tasks</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=isr&m[1]=view&m[2]=files&id=$id" title="Related files">Files</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=isr&m[1]=view&m[2]=log&id=$id" title="Activity Log">Log</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=isr&m[1]=view&m[2]=link&id=$id" title="Link">Links</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=isr&m[1]=view&m[2]=status&id=$id" title="Change ISR status">Status</a>
EOF;

if($user->isInGroup(array("gg_ADMIN","superuser"))){
	$DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="isr/isr.admin.inc.php?mode=record_view&form_type=main_tpl&id=$id" title="Edit this ISR">Admin</a>
EOF;
}

switch($m[2]){
case 'lines':
    $DEFAULT_TITLE .= "\Lines";
    $DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=isr&m[1]=view&m[2]=lines&id=$id" title="ISR Lines">PUR view</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=isr&m[1]=view&m[2]=lines&m[3]=acct_view&id=$id" title="ISR Lines">ACCT view</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=isr&m[1]=view&m[2]=lines&m[3]=quickAdd&id=$id" title="Add Lines">Add Line</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=isr&m[1]=view&m[2]=lines&m[3]=download&doc=PACKING_SLIP&id=$id" title="Download all packing slips">Download ZIP (Packing Slips)</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=isr&m[1]=view&m[2]=lines&m[3]=download&doc=SALES_INVOICE&id=$id" title="Download all packing slips">Download ZIP (Invoices)</a>
EOF;
    // Get packing slips
    $psList = $isr->getLines();

    switch($m[3]){
    case 'download':
        switch($m[4]){
        case 'confirm':
            // Check files to zip in session
            if(empty($sess['mfg']['pur']['isr']['zip']['doc']) ||
            count($sess['mfg']['pur']['isr']['zip']['files'])==0){
                $DEFAULT_ERROR[]="ERROR: Nothing to zip found in session...";
                break;
            }
            // Get info
            $DOC = $sess['mfg']['pur']['isr']['zip']['doc'];
            $PDF = $sess['mfg']['pur']['isr']['zip']['files'];
            // Reset session
            $sess['mfg']['pur']['isr']['zip'] = NULL;
            // Create Zip in Temp folder
            $zip = new tldFileZIP(tempnam('/tmp', "ISR").".zip");
            // Add files in the zipped file
            foreach($PDF as $pdf){
                $zip->addFile($pdf);
            }
            // Send the file
            $zip->out($DOC."_ISR_".$id.".zip");
            exit;
        break;
        default:
            $ERP = $isr->itsHeader['bu_from_erp'];
            $archive = new tldArchive($ERP);
            $path_archive = tldArchive::getWebRoot();
            $pdfs = array();

            // Type of docs
            switch($doc){
            case 'PACKING_SLIP':
                foreach($psList as $ps){
                    // Look in archive
                    $files = $archive->byTypeID("PACKING SLIP", $ps['t_dino']);
                    // Check if there is archive
                    if(empty($files)){
                        $DEFAULT_ERROR[]="WARNING: No archives found in $ERP for PACKING SLIP#".$ps['t_dino'];
                        continue;
                    }
                    // Add the file in the list
                    $file_path = $path_archive."/".$files[0]['filepath'];
                    if(!file_exists($file_path)){
                        $DEFAULT_ERROR[]="WARNING: Archive File not found in $ERP for PACKING SLIP#".$ps['t_dino'];
                        continue;
                    }
                    $pdfs[] = $file_path;
                }
            break;
            case 'SALES_INVOICE':
                $invList = array();
                foreach($psList as $ps){
                    $dino = new tldDINO($ps['t_dino'],$ERP);
                    // Foreach PS get SO data
                    foreach($dino->getDetail() as $dinoDetail){
                        $so = new tldSO($dinoDetail['t_orno'],$ERP);
                        // Foreach SO get Inv#
                        foreach($so->getDetail() as $soDetail){
							if($soDetail['t_dino'] == $ps['t_dino']){
								$invList[]=$soDetail['t_ttyp'].$soDetail['t_invn'];
							}
                        }
                    }
                }
                $invList = array_unique($invList);
                // Look for archive PDF
                foreach($invList as $invID){
                    $files = $archive->byTypeID("SALES INVOICE", $invID);
                    // Check if there is archive
                    if(empty($files)){
                        $DEFAULT_ERROR[]="WARNING: No archives found in $ERP for SALES INVOICE#".$invID;
                        continue;
                    }
                    // Add the file in the list
                    $file_path = $path_archive."/".$files[0]['filepath'];
                    if(!file_exists($file_path)){
                        $DEFAULT_ERROR[]="WARNING: Archive File not found in $ERP for SALES INVOICE#".$invID;
                        continue;
                    }
                    $pdfs[] = $file_path;
                }
            break;
            }

            $pdfs = array_unique($pdfs);
            $nb_files = count($pdfs);
            // Check files
            if($nb_files==0){
                $DEFAULT_ERROR[]="ERROR: Nothing to zip, no PDF archive found...";
                break;
            }
            // Save in session
            $sess['mfg']['pur']['isr']['zip'] = array(
                "doc"=>$doc,
                "files"=>$pdfs
            );
            $body = <<<EOF
<p>$nb_files PDF files for $doc found in ISR#$id</p>
<p><a href="$php_self?m[0]=isr&m[1]=view&m[2]=lines&m[3]=download&m[4]=confirm&id=$id">
Click here to confirm and download the zip file</a></p>
EOF;
        break;
        }
    break;
    case 'delete':
        if(in_array($isr->getStatus(),array("CLOSED"))){
            $DEFAULT_ERROR[] = "ERROR: Can not unlink Packing slip when ISR is CLOSED";
    	    break;
        }
        if(!in_array($isrlid,array_column($psList, 'id', 't_dino'))){
            $DEFAULT_ERROR[] = "ERROR: This ISR Line do not belong to ISR#$id";
    	    break;
        }
        $form = new HTML_QuickForm('frmGenSPR', 'post');
        $form->addElement(	'hidden', 'm[0]', 'isr');
        $form->addElement(	'hidden', 'm[1]', 'view');
        $form->addElement(	'hidden', 'm[2]', 'lines');
        $form->addElement(	'hidden', 'm[3]', 'delete');
        $form->addElement(	'hidden', 'id', $id);
        $form->addElement(	'hidden', 'isrlid', $isrlid);
        $form->addElement(	'header', 'title', "Are sure you want to delete line#$isrlid?");
        $form->addElement(	'select', 'confirm', 'Confirm?', array("Y"=>"I confirm"));
        $form->addElement(	'submit', 'btnSubmit', 'Submit');

        if(!$form->validate()){
    	    $body .= $form->toHTML();
    	    break;
    	}

    	$vars = tldUtils::cleanupFormInput($form->exportValues());
    	if($vars['confirm']!="Y"){
    	    $DEFAULT_ERROR[] = "ERROR: ISR Line not deleted.<br>Reason: Not confirmed";
    	    break;
    	}
        $e = tldISRL::delete($isrlid);
        if(is_string($e)){
            $DEFAULT_ERROR[] = "ERROR: ISR Line not deleted.<br>Reason: $e";
    	    break;
        }
        $body = "Packing Slip #$isrlid successfully unlinked to ISR#$id";
        $body.= _getLineReport();
    break;
    case 'quickAdd':
        // Get list of packing slip from ERP
    	$dinos = tldDINO::byCustomerERP(
    	    $header['cuno'],
    	    $header['bu_from_erp'],
    	    array("getAll"=>"Y")
    	);
    	$dinos = array_slice($dinos, 0, 100);
    	if(empty($dinos)){
    	    $DEFAULT_ERROR[] = "WARNING: No packing slips found for CUNO#{$header['cuno']} in {$header['bu_from_erp']}";
    	}
    	// Get list of packing slip from Intranet by Customer ERP from
	    $psLinkedToISR = tldUtils::optionsByKeyValue(
	        tldISRL::byConstraints("parent_id IN (SELECT isr.id FROM isr
	        	WHERE isr.bu_from_id={$header['bu_from_id']} AND isr.cuno='{$header['cuno']}')"
	        ),
	        "t_dino",
	        "t_dino"
	    );
    	// Filters and rearange data, 1 packing slip = 1 PO = multiple POL
    	$rows = array();
    	foreach($dinos as $dino){
    	    // Get PS since last month
    	    list($y, $m, $d) = explode("-", $dino['t_ddat']);
    	    if( mktime(0, 0, 0, $m+1, $d, $y) <= time() ){
    	        continue;
    	    }
    	    // PS already listed
    	    if(isset($rows[$dino['t_dino']])){
    	        continue;
    	    }
    	    // PS already linked to an ISR with same ERP from / customer of this ISR
    	    if(in_array($dino['t_dino'],$psLinkedToISR)){
    	        continue;
    	    }
    	    // Get PS details
    	    $ps = new tldDINO($dino['t_dino'], $header['bu_from_erp']);
            $psLines = $ps->getDetail();
            // Re-arange data
            $rows[$dino['t_dino']] = $dino;
    	    foreach($psLines as $psLine){
    	        $rows[$dino['t_dino']]['lines'][] = $psLine;
    	    }
    	}

    	// Display form
        $form = new HTML_QuickForm('frmGenSPR', 'post');
        $form->addElement(	'hidden', 'm[0]', 'isr');
        $form->addElement(	'hidden', 'm[1]', 'view');
        $form->addElement(	'hidden', 'm[2]', 'lines');
        $form->addElement(	'hidden', 'm[3]', 'quickAdd');
        $form->addElement(	'hidden', 'id', $id);
        $form->addElement(	'header', 'title', 'Manual entry');
        $form->addElement(	'text', 'ps_manual', 'Packing Slip#');
    	$form->addElement(	'header', 'title', "Or select Packing Slip from {$header['bu_from_fullname']}
        	<br>- Outbounded since a month
        	<br>- And not linked to an another ISR"
    	);
    	foreach($rows as $row){
    	    // Display the packing slip check box
    	    $psLabel = <<<EOF
<a href="/en/private/finance/finance.php?m[0]=ps&m[1]=view&erp={$header['bu_from_erp']}&id={$row['t_dino']}">Pack#{$row['t_dino']}</a>
EOF;
            $psDetail = <<<EOF
PO#{$row['t_orno']} created the {$row['t_ddat']}</a>
EOF;
            foreach($row['lines'] as $line){
                $psDetail.= "<br><span style=\"margin-left:30px;\">-</span>
				Line#{$line['t_pono']} PN#{$line['t_item']} {$line['t_dsca']} Qty {$line['t_dqua']}";
            }
    	    $form->addElement("checkbox","ps[{$row['t_dino']}]",$psLabel,$psDetail);
        }
        $form->addElement(	'submit', 'btnSubmit', 'Submit');

        if(!$form->validate()){
    	    $body .= $form->toHTML();
    	    break;
    	}

    	$vars = tldUtils::cleanupFormInput($form->exportValues());
    	if(!empty($vars['ps_manual'])){
    	    $vars['ps'][$vars['ps_manual']]=1;
    	}
    	if(empty($vars['ps'])){
    	    $DEFAULT_ERROR[]="ERROR: No packing slip entered or selected";
    	    $body = $form->toHTML();
    	    break;
    	}
    	// Insert ISRL
    	foreach($vars['ps'] as $psid=>$v){
    	    $e = tldISRL::insert(
    	        array(
    	        	"parent_id"=>$id,
    	            "t_dino"=>$psid
    	        )
    	    );
    	    if(is_string($e)){
    	        $DEFAULT_ERROR[]="INTERNAL ERROR: Packing Slip #$psid not added.<br>Reason: $e";
    	        continue;
    	    }
    	    $body.= "<br/>Packing Slip #$psid added to ISR#$id";
    	}
    	$body.= _getLineReport();
    break;
    case 'acct_view':
        $body = _getLineReport("acct");
    break;
    default:
        $body = _getLineReport();
    break;
    }
break;
case 'edit':
    $DEFAULT_TITLE .= "\Edit";
    if($isr->getStatus()=='CLOSED'){
        $DEFAULT_ERROR[]="ERROR: Can not edit this ISR as it is CLOSED";
        break;
    }
	$erpList = tldLocation::getERPList("smartyOptionsIDLocation");
	$userList = tldDirectory::getUserlist("smartyOptions");
	$form = new HTML_QuickForm('frmAddISR', 'post');
	$form->addElement(	'hidden', 'm[0]', 'isr');
	$form->addElement(	'hidden', 'm[1]', 'view');
	$form->addElement(	'hidden', 'm[2]', 'edit');
	$form->addElement(	'hidden', 'id', $id);
	$form->addElement(	'header', 'title',"Edit ISR#$id");
	$form->addElement('select', 'bu_from_id', 'BU from', array(""=>"")+$erpList);
	$form->addElement('select', 'bu_to_id', 'BU to', array(""=>"")+$erpList);
	$form->addElement('text', 'cuno', 'ERP Customer#');
    $form->addElement('select', 'ttype', 'Transportation type',
        ["" => "", "AIR" => "AIR", "OCEAN" => "OCEAN", "TRAIN"=>"TRAIN", "ROAD" => "ROAD"]
    );
    $form->addElement( 'text', 'cnum', 'Container#');
    $form->addElement( 'select', 'container_type', 'Container Type',['','20GP'=>'20GP', '40GP'=>'40GP', '40HQ'=>'40HQ', '40OT in gauge'=>'40OT in gauge', '40OT out gauge'=>'40OT out gauge' ,'air'=>'air']);
	$form->addElement( 'text', 'tnum', 'Tracking#<br/><em>Start with ups, dhl, fed etc.<br/>followed by #</em>');
	$form->addElement( 'date', 'dt_outb', 'Outbound Date',
	    array("format"=>"Y-m-d","minYear"=>date('Y'),"maxYear"=>date('Y')+3,'addEmptyOption'=>TRUE)
	);
	$form->addElement( 'date', 'dt_ship', 'Shipping Date',
	    array("format"=>"Y-m-d","minYear"=>date('Y'),"maxYear"=>date('Y')+3,'addEmptyOption'=>TRUE)
	);
	$form->addElement( 'date', 'dt_eta', 'ETA Date',
	    array("format"=>"Y-m-d","minYear"=>date('Y'),"maxYear"=>date('Y')+3,'addEmptyOption'=>TRUE)
	);
	$form->addElement( 'textarea', 'notes', 'Notes', array("rows"=>5, "cols"=>30));
	$form->addElement(	'header', 'title', "ISR documents");
	$form->addElement(	'file', 'doc_ship', "Shipping document<br><em>{$header['doc_ship']}</em>");
	$form->addElement(	'file', 'doc_qa', "Quality document<br><em>{$header['doc_qa']}</em>");
	$form->addElement(	'file', 'doc_inv', "Invoice document<br><em>{$header['doc_inv']}</em>");
	$form->addElement('submit', 'btnSubmit', 'Submit');
	$form->addElement('reset', 	'btnReset',  'Reset');
	$fields=array("bu_from_id","bu_to_id","cuno","ttype","dt_outb","dt_ship","dt_eta");
	foreach($fields as $field){
	    $form->addRule($field, 'This is required', 'required');
	}
	$form->setDefaults($header);

	if(!$form->validate()){
	    $body .= $form->toHTML();
	    break;
	}

	$vars = tldUtils::cleanupFormInput($form->exportValues());
	// Check customer number in ERP
	$bu = new tldLocation($vars['bu_from_id']);
	$cust = new tldERPCustomer($bu->getERP(),$vars['cuno']);
	if($cust->isEmpty()){
	    $DEFAULT_ERROR[]="ERROR: Customer {$vars['cuno']} not found in ".$buTo->getERP();
		$body = $form->toHTML();
	    break;
	}
	$vars['dt_outb']=implode('-',$vars['dt_outb']);
	$vars['dt_ship']=implode('-',$vars['dt_ship']);
	$vars['dt_eta']=implode('-',$vars['dt_eta']);
	$e = $isr->update($vars);
	if(is_string($e)){
		$DEFAULT_ERROR[]="INTERNAL ERROR: ISR not updated<br/>Reason: $e";
		break;
	}
	$body .= "ISR#$e successfully updated";
	// Upload DOC files if updated
	$DocFields = array('doc_ship','doc_qa','doc_inv');
	$DocNames = array();
	foreach($DocFields as $DocField){
	    if($header[$DocField]!=$vars[$DocField]){
	        continue;
	    }
		$fileTmp = $form->getElement($DocField);
		$fileData = $fileTmp->getValue();
		if(!empty($fileData["tmp_name"])){
			$file = new basicFile($fileData["tmp_name"]);
			$filename = basename(time()."_".$fileData["name"]);
			if($file->copyFile(tldUtils::getPathToUploadFile("isr",$filename))){
				$DocNames[$DocField] = $filename;
			}else{
			    $DEFAULT_ERROR[]="INTERNAL ERROR: Could not copy file '$filename' for $DocField";
			}
		}
	}
	// Update ISR for docs
	if(count($DocNames)>0){
		$res = $isr->update($DocNames);
		if(!is_string($res)){
			foreach($DocNames as $field=>$fileName){
                $body .= "<br/>$fileName successfully added as $field document";
			}
		}else{
		    $DEFAULT_ERROR[]="INTERNAL ERROR: Problem updating ISR#$e documents";
		}
	}
	$body .= _getGeneralTab();
break;
case 'status':
    $currentStatus = $isr->getStatus();
    $allowedStatus = $isr->getAllowedStatus();
    // Check status
    if(empty($allowedStatus)){
        $DEFAULT_ERROR[]="ERROR: Status change not allowed with actual status";
		break;
    }
    // Get form
    $form = new HTML_QuickForm('frmChangeStatus', 'post');
	$form->addElement(	'header', 'title', 'Change status');
	$form->addElement(	'hidden', 'm[0]', 'isr');
	$form->addElement(	'hidden', 'm[1]', 'view');
	$form->addElement(	'hidden', 'm[2]', 'status');
	$form->addElement(	'hidden', 'id', $id);
	$form->addElement(	'select', 'status_id', 'New Status', $allowedStatus);
	$form->addElement(  'textarea', 'comment', 'Comment', array("rows"=>10, "cols"=>40));
	$form->addElement(	'submit', 'submit', 'Submit');
	$form->addRule('comment', 'Required', 'required');
	$form->addRule('status_id', 'Required', 'required');

	if(!$form->validate()){
		$body = $form->toHTML();
		break;
	}

	$vars = tldUtils::cleanupFormInput($form->exportValues());
	$new_status = $allowedStatus[$vars['status_id']];

	// Checks
    switch($new_status){
	case "IN PROGRESS":
		// Check permissions
		if(!$user->isInGroup(['gg_ADMIN','gg_PUR','GG_PARTS'])){
			$DEFAULT_ERROR[]=  "ERROR: You do not have permissions to change status to IN PROGRESS";
			break 2;
		}
		// Check if packing slip attached
        if(count($isr->getLines())==0){
			$DEFAULT_ERROR[]=  "ERROR: Can not update status, no lines created for this ISR";
			break 2;
		}
		// Check if docs are ok
		if(!$isr->isFullyDocAttached()){
			$txt="ISR#$id do not have all documents attached, please proceed ASAP.";
			$DEFAULT_ERROR[]="WARNING: $txt";
			// Create task
			$e = tldTask::insert($id,
				array(
					"due_date"	=>array("Y"=>date("Y"),"m"=>date("m"),"d"=>date("d")),
					"bu_id"		=>$header["bu_from_id"],
    				"assignee"	=>$header["poster_id"],
    				"assignor"	=>$user->getID(),
    				"task"		=>$txt
				),
				"ISR"
			);
			if(is_string($e)){
			    $DEFAULT_ERROR[]="ERROR: Could not create the task to ask document.<br>Reason: $e";
			    break;
			}
			$txt = "Task#$e created to attach all documents";
			$isr->addLogEntry($user->getID(),$txt);
			$body.="<br/>$txt";
		}
    break;
	case "CLOSED":
		// Check permissions
		if(!$user->isInGroup(['gg_ADMIN','gg_PUR','GG_PARTS'])){
			$DEFAULT_ERROR[]=  "ERROR: You do not have permissions to change status to CLOSED";
			break 2;
		}
		// Check if docs are ok
		if(!$isr->isFullyDocAttached()){
			$DEFAULT_ERROR[]=  "ERROR: ISR#$id do not have all documents attached";
			break 2;
		}
	break;
	}

	$e = $isr->changeStatus($new_status);
	if(is_string($e)){
		$DEFAULT_ERROR[] = "INTERNAL ERROR: Status not updated.<br/>Reason: $e";
		break;
	}
	$m = $new_status;
	if($vars['comment']) $m .= "\n".$vars['comment'];
	$isr->addLogEntry($user->getID(), $m);
	$body .= "<br/>ISR status successfully changed to $new_status";
	$header = $isr->getHeader();
	$body .= _getGeneralTab();
break;
case "tasks":
	$DEFAULT_TITLE .="\Tasks";
	$DEFAULT_MENU .=<<<EOF
		<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=form&m[2]=newTask&module=isr&parent_id=$id">New Task</a>
EOF;
	$sess["calendar"]["tasks"] = $isr->getTasks();
	$form = new tldReportMultiLevel($sess["calendar"]["tasks"],
				array("status","due_date"),
				array(	"id"				=>"Task#",
						"status"			=>"Status",
						"due_date"			=>"Due",
						"task"				=>"Task",
						"assignee_fullname"	=>"Assignee"
						),
				array("passField"=>"id",
				"url"=>"/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=")
				);
	$body .= $form->fetch();
break;
case 'log':
	$DEFAULT_TITLE .="\Log";
	$log = $isr->getLog();
	$report = new tldReportColumnar($log,
		array("xItems"=>array(
				"id"				=>"ID#",
				"date"				=>"Date",
				"poster_fullname"	=>"Poster",
				"comment"			=>"Comment")
		)
	);
	$body .= $report->fetch();
break;
case 'link':
	$DEFAULT_MENU .= <<<EOF
	<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="/en/private/common/index.php?m[0]=links&m[1]=form&m[2]=newLink&module=ISR&parent_id=$id">Add New Link</a>
EOF;
	$report = new tldReportColumnar(
		tldModLink::byParent($id, 'ISR'),
		[
			'xItems' => [
				'id' => 'ID#',
				'type' => 'Module',
				'item' => 'Ref#',
				'dsca' => 'Description',
			],
			'title' => 'Links FROM Here...',
			'links' => ['id' => '/en/private/common/index.php?m[0]=links&m[1]=view&m[2]=redirect&id='],
		]
	);
	$body .= $report->fetch();
	$report = new tldReportColumnar(
		tldModLink::byItem($id, 'ISR'),
		[
			'xItems' => [
				'id' => 'ID#',
				'module' => 'Module',
				'parent_id' => 'Ref#',
				'dsca' => 'Description',
			],
			'title' => 'Links TO Here...',
			'links' => ['id' => "/en/private/common/index.php?m[0]=links&m[1]=view&m[2]=redirect&reversed=1&erp=$erp&id="],
		]
	);
	$body .= $report->fetch();
break;
case 'files':
	$DEFAULT_TITLE .="\Files";
	$DEFAULT_MENU.=<<<EOF
		<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<a href="/en/private/common/index.php?m[0]=files&m[1]=form&m[2]=newFile&module=isr&parent_id=$id">Add New File</a>
EOF;
	$report = new tldReportColumnar($isr->getFiles(),
		array("xItems"=>array("id"=>"File ID",
			"date"=>"Date",
			"description"=>"Description",
			"filename"=>"Filename"),
			"links"=>array("id"=>"/en/private/common/index.php?m[0]=files&m[1]=view&id=")
		)
	);
	$body .= $report->fetch();
	if(!empty($header['doc_ship'])) {
		$attchment[0]['date'] = $header['dt'];
		$attchment[0]['type'] = 'shipping document';
		$attchment[0]['filename'] = $header['doc_ship'];
		$attchment[0]['poster'] = $header['poster_fullname'];
	}
	if(!empty($header['doc_qa'])){
		$attchment[1]['date'] = $header['dt'];
		$attchment[1]['type'] = 'quality document';
		$attchment[1]['filename'] = $header['doc_qa'];
		$attchment[1]['poster'] = $header['poster_fullname'];
	}
	if(!empty($header['doc_inv'])){
		$attchment[2]['date'] = $header['dt'];
		$attchment[2]['type'] = 'invoice document';
		$attchment[2]['filename'] = $header['doc_inv'];
		$attchment[2]['poster'] = $header['poster_fullname'];
	}

	$report1 = new tldReportColumnar($attchment,
		["xItems"=>[
			"date"=>"Date",
			"poster"=>"Poster",
			"type"=>"File Type",
			"filename"=>"Filename"],
			"links"=>["filename"=>"/en/private/uploads/isr/"],
			"title"=>"Documents attched in ISR#{$header['id']}"
		]
	);
	if(isset($attchment)) {
		$body .= "<br>" . $report1->fetch();
		$body .= "<br><font color='red'>You can update above files in <a href='/en/private/manufacturing/pur/dev.php?m[0]=isr&m[1]=view&m[2]=edit&id={$header['id']}'>edit</a> mode.</font>";
	}
break;
default:
	// show some warnings
	if(empty($header['doc_ship']))
		$DEFAULT_ERROR[]="WARNING: Shipping document missing";
	if(empty($header['doc_qa']))
		$DEFAULT_ERROR[]="WARNING: Quality document missing";
	if(empty($header['doc_inv']))
		$DEFAULT_ERROR[]="WARNING: Invoice document missing";
	// General page
	$body = _getGeneralTab($header);
	$rows = array();
	$amnt = 0;
    foreach($isr->getLines() as $ps){
        // Get packing slip info to get SO#
        $dino = new tldDINO($ps['t_dino'], $header['bu_from_erp']);
        $e = new tldBaanERP($header['bu_from_erp']);
        foreach($dino->getDetail() as $detail){
            $detail['isrlid']=$ps['id'];
            // Get PO# from SO#
            $so = new tldSO($detail['t_orno'], $header['bu_from_erp']);
            // Get the PO#
            $detail['t_eono'] = $so->itsDetails['t_eono'];
            $detail['t_ccur'] = $so->itsDetails['t_ccur'];
            // Get the item information
            $item = $e->getItemData($detail['t_item']);
            $hscode = $e->getItemData($detail['t_item']);
            $detail['t_wght']= $item['WT'];
            $detail['totalwght']= $item['WT']*$detail['t_dqua'];
            $detail['t_ctyo']= $item['t_ctyo'];
            $detail['t_ccde']= $hscode['t_ccde'];
            $amnt = $amnt + $detail['t_amnt'];
            $rows[] = $detail;
        }
    }
    $sess['isr']['packingslip'] = $rows;
    if(count($rows)==0){
        $DEFAULT_ERROR[]="No rows founded...";
        break;
    }

    $DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=isr&m[1]=view&m[2]=xls&id={$header['id']}">XLS Download</a>
EOF;
    $xItem = array(
        "t_dino"=>"Packing slip#",
        "invoice"=>"Inovice#",
        "t_orno"=>"SO#",
        "t_eono"=>"PO#",
        "t_pono"=>"Item#SO",
        "t_pric"=>"Unit Price",
        "t_amnt" =>"Amount",
        "t_ccur" =>"Currency",
        "t_wght"=>"WT(KGS)",
        "totalwght"=>"Total WT",
        "t_ctyo"=>"COO",
        "t_ccde"=>"HS Code",
        "t_item"=>"Part Number",
        "t_dsca"=>"Description",
        "t_dscb"=>"Description(ALT)",
        "t_dqua"=>"Packing slip Qty",
    );

    if($header['bu_from_erp'] == '300' || $header['bu_from_erp'] == '400') {
        $xItem['t_wght'] = "WT(LBS)";

    }

    $report = new tldReportColumnar(
	    $rows,
		array(
			"xItems"=>$xItem,
			"links"=>array(
    		    "t_orno"=>"/en/private/finance/finance.php?m[0]=so&m[1]=view&erp={$header['bu_from_erp']}&id=",
				"invoice"=>"/en/private/finance/finance.php?m[0]=inv&m[1]=view&erp={$header['bu_from_erp']}&id=",
				"t_dino"=>"/en/private/finance/finance.php?m[0]=ps&m[1]=view&erp={$header['bu_from_erp']}&id=",
    		    "t_eono"=>"/en/private/manufacturing/pur/dev.php?m[0]=po&m[1]=view&erp={$header['bu_to_erp']}&id=",
    		    "t_item"=>"/en/private/manufacturing/eng/dev.php?m[0]=bom&m[1]=view&erp={$header['bu_from_erp']}&pn="
			),
			"sumTotalsArray"=>["t_amnt"],
			"title"=>"Packing slip"
		)
	);
    if($m[2]=='xls'){
            $report = new tldXLS(
                $rows,
                array(
                    "xItems"=>$xItem,
                    "showTitles"=>true
                )
            );
            $report->out();
            exit;
    }
    $body.=$report->fetch();
    $result =[];
    foreach($rows as $key=>$val){
        $cur = $val['t_ccur'];
        if (isset($val['t_amnt'])){
            $result[$cur]['amnt'] +=$val['t_amnt'];
            $result[$cur]['t_curr'] = $cur;
        }
    }
    if(count($result)>1){
        $report = new tldReportColumnar($result,
            [
                "xItems"=> [
                    "t_curr"=>"Currency",
                    "amnt"=>"Amount"
                ],
                "title"=>"Total Amount By Currency"
            ]
        );
    	$body .= $report->fetch();

    }
break;
}


function _getLineReport($viewOption=""){
    global $isr,$header,$id,$php_self;
    $rows = array();
    // Check the type of view to use
    switch($viewOption){
    case 'acct':
        $title = "ISR Lines structured by Sales Order and Invoice#";
        $xFilterItems = array("t_orno","t_invn");
        $xItems = array(
            "t_dino"=>"Pack#",
            "t_pono"=>"Item#",
            "t_item"=>"Part Number",
            "t_dsca"=>"Description",
            "t_cuqs"=>"UM",
            "t_oqua"=>"Qty",
            "t_dqua"=>"Del",
            "t_bqua"=>"Back",
            "t_ssls"=>"Status<br>(7=Shipped)",
            "t_orno"=>"SO#",
        	"t_ddat"=>"Delivery Date",
        	"t_eono"=>"PO#",
            "t_invn"=>"Invoice#"
        );
        $xLinks = array(
            "t_orno"=>"/en/private/finance/finance.php?m[0]=so&m[1]=view&erp={$header['bu_from_erp']}&id=",
        	"t_dino"=>"/en/private/finance/finance.php?m[0]=ps&m[1]=view&erp={$header['bu_from_erp']}&id=",
        	"t_item"=>"/en/private/manufacturing/eng/dev.php?m[0]=bom&m[1]=view&erp={$header['bu_from_erp']}&pn=",
            "t_eono"=>"/en/private/manufacturing/pur/dev.php?m[0]=po&m[1]=view&erp={$header['bu_to_erp']}&id=",
            "t_invn"=>array(
                "url"=>"/en/private/finance/finance.php?m[0]=inv&m[1]=view&erp={$header['bu_from_erp']}",
                "params"=>array(
        			"ttyp"=>"t_ttyp",
                	"id"=>"t_invn"
                )
            )
        );
        // Foreach lines get PS
        foreach($isr->getLines() as $ps){
            $dino = new tldDINO($ps['t_dino'],$header['bu_from_erp']);
            // Foreach PS get SO data
            foreach($dino->getDetail() as $dinoDetail){
                $so = new tldSO($dinoDetail['t_orno'],$header['bu_from_erp']);
                // Foreach SO get Inv#
                $invList = array();
                foreach($so->getDetail() as $soDetail){
                    $invList[$soDetail['t_invn']]=$soDetail['t_ttyp'];
                }
                foreach($invList as $invID=>$invType){
                    $dinoDetail['isrlid']=$ps['id'];
                    $dinoDetail['t_invn']=$invID;
                    $dinoDetail['t_ttyp']=$invType;
                    $dinoDetail['t_eono']=$so->itsDetails['t_eono'];
                    $rows[] = $dinoDetail;
                }
            }
        }
    break;
    default:
        $title = "ISR Lines structured by Packin slip and Sales Order";
        $xFilterItems = array("t_dino","t_orno");
        $xItems = array(
            "t_pono"=>"Item#",
            "t_item"=>"Part Number",
            "t_dsca"=>"Description",
            "t_cuqs"=>"UM",
            "t_oqua"=>"Qty",
            "t_dqua"=>"Del",
            "t_bqua"=>"Back",
            "t_ssls"=>"Status<br>(7=Shipped)",
            "t_orno"=>"SO#",
        	"t_ddat"=>"Delivery Date",
            "t_eono"=>"PO#",
            "t_dino"=>"Unlink Pack#"
        );
        $xLinks = array(
            "t_orno"=>"/en/private/finance/finance.php?m[0]=so&m[1]=view&erp={$header['bu_from_erp']}&id=",
            "t_item"=>"/en/private/manufacturing/eng/dev.php?m[0]=bom&m[1]=view&erp={$header['bu_from_erp']}&pn=",
            "t_eono"=>"/en/private/manufacturing/pur/dev.php?m[0]=po&m[1]=view&erp={$header['bu_to_erp']}&id=",
            "t_dino"=>array(
                "url"=>"$php_self?m[0]=isr&m[1]=view&m[2]=lines&m[3]=delete&id=$id",
                "params"=>array("isrlid"=>"isrlid")
            )
        );
        // Foreach PS get POs data
        foreach($isr->getLines() as $ps){
            $dino = new tldDINO($ps['t_dino'], $header['bu_from_erp']);
            foreach($dino->getDetail() as $detail){
                // Get PO# from SO#
                $so = new tldSO($detail['t_orno'],$header['bu_from_erp']);
                $detail['t_eono']=$so->itsDetails['t_eono'];
                $detail['isrlid']=$ps['id'];
                $rows[] = $detail;
            }
        }
    break;
    }
    // Create report
    $report = new tldReportMultilevel(
        $rows,
        $xFilterItems,
        $xItems,
        array(
            "title"=>$title,
            "links"=>$xLinks
        )
    );
    return $report->fetch();
}
?>
