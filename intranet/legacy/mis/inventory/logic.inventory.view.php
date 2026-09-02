<?php
if (!isset($id) || !is_numeric($id)) {
	$DEFAULT_ERROR[] = "ERROR: Parameters empty or invalid";

	return;
}
if(!$user->isInGroup(['GG_MISINV_tabletManager','GG_MISINV_fixedAssetManager','gg_MIS','ROLE_MISM','ROLE_CIO','superuser','prod_IT_referent'])){
    $DEFAULT_ERROR[] = "ERROR: You do not have access to this module";

    return;
}
$item = new Tld_Mis_Inventory_Item($id);
$itemHeader = $item->header;
if(!($user->isInGroup(['GG_MISINV_tabletManager','GG_MISINV_fixedAssetManager','gg_MIS','ROLE_MISM','ROLE_CIO','superuser']) || ($user->isInGroup(['prod_IT_referent']) && $itemHeader['destination_id'] === $user->getID()))){
    $DEFAULT_ERROR[] = "ERROR: You do not have access to this item";
    return;
}
if ($item->isEmpty()) {
	$DEFAULT_ERROR[] = "ERROR: Item#$id not found";

	return;
}

$DEFAULT_TITLE .= "\Item#$id";
$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=inventory&m[1]=view&id=$id">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=inventory&m[1]=view&m[2]=edit&id=$id">Edit</a>
EOF;
If (!$item->isAssigned()) {
	$DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=inventory&m[1]=view&m[2]=assign&id=$id">Assign</a>
EOF;
} else {
	$DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=inventory&m[1]=view&m[2]=reassign&id=$id">Reassign</a>
EOF;
	$DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=inventory&m[1]=view&m[2]=unassign&id=$id">Unassign</a>
EOF;
}
	$DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=inventory&m[1]=view&m[2]=label&id=$id">Label</a> 
&nbsp;|&nbsp;<a href="$php_self?m[0]=inventory&m[1]=view&m[2]=history&id=$id">History & logs</a>
EOF;
if($user->isInGroup(['gg_MIS','ROLE_MISM','ROLE_CIO','superuser','prod_IT_referent'])) {
    $DEFAULT_MENU .= <<<EOF
 | <a href="$php_self?m[0]=inventory&m[1]=view&m[2]=links&id=$id">Links</a>
EOF;
}
if($user->isInGroup(['gg_MIS','ROLE_MISM','ROLE_CIO','superuser'])) {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=inventory&m[1]=view&m[2]=files&id=$id">Files</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=inventory&m[1]=view&m[2]=contract&id=$id">Maintenance Contract</a>
EOF;
}
// Check + link for assignment here <----

switch ($m[2]) {
    case 'links':
        $DEFAULT_TITLE .= "\Links";
        $DEFAULT_MENU.=<<<EOF
	<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="/en/private/common/index.php?m[0]=links&m[1]=form&m[2]=newLink&module=IINV&parent_id=$id">New Link</a>
EOF;
        $links = tldModLink::byParent($id, 'IINV');
        $report = new tldReportColumnar($links,
            array("xItems"=>array(
                "id"=>"ID#",
                "type"	=>"Module",
                "item"	=>"Ref#",
                "dsca"  =>"Description"
            ),
                "title"=>"Links",
                "links"=>array("id"=>"/en/private/common/index.php?m[0]=links&m[1]=view&m[2]=redirect&id=")
            )
        );
        $body .= $report->fetch();
        break;
    case 'contract':
        $DEFAULT_TITLE .= "\Maintenance Contracts";
        if(!$user->isInGroup(['GG_MISINV_tabletManager','GG_MISINV_fixedAssetManager','gg_MIS','ROLE_MISM','ROLE_CIO','superuser'])){
            $DEFAULT_ERROR[] = "ERROR: You do not have access to this module!";
            return;
        }
        $DEFAULT_MENU.=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=inventory&m[1]=view&m[2]=contract&m[3]=add&id=$id">Add Contract</a>
EOF;
        $contract = $item->getContract();
        if(!empty($contract['id'])){
            $DEFAULT_MENU.=<<<EOF
&nbsp;&nbsp;|&nbsp;
<a href="$php_self?m[0]=inventory&m[1]=view&m[2]=contract&m[3]=renew&id=$id">Renew Contract</a>
EOF;
        }

    switch($m[3]) {
        case 'add':
        case 'renew':
            $date = date('Y-m-d');
            // Form
            $form = new HTML_QuickForm('frmAddFile', 'post');
            $form->addElement('hidden', 'm[0]', 'inventory');
            $form->addElement('hidden', 'm[1]', 'view');
            $form->addElement('hidden', 'm[2]', 'contract');
            $form->addElement('hidden', 'm[3]', 'add');
            $form->addElement('hidden', 'id', $id);
            $form->addElement('hidden', 'parent_id', $id);
            $form->addElement('hidden', 'date', $date);
            $form->addElement('text', 'start_dt', 'Start Date', ["class" => "datepicker"]);
            $form->addElement('text', 'end_dt', 'End Date', ["class" => "datepicker"]);
            $form->addElement('text', 'support', 'Support Number');
            $form->addElement('text', 'account', 'Account Number');
            $form->addElement('text', 'link', 'Web Link');
            $form->addElement('header', 'titleInfo', 'Warning: Filename length is limited to ' . tldFile::fileNameLengthLimit . ' chars');
            $form->addElement('file', 'file', 'Attachment');
            $form->addElement('submit', 'btnSubmit', 'Submit');
            //Set required
            $form->addRule("start_dt", "Required field", "required");
            $form->addRule("file", "Required field", "required");
            $form->addRule('support', 'Required', 'required');

            if($m[3] == "renew"){
                $form->setDefaults([
                        "start_dt" => $contract['end_dt'],
                        "support" => $contract['support'],
                        "account" => $contract['account'],
                        "link" => $contract['link'],
                        ]
                );
            }
            if (!$form->validate()) {
                $body = $form->toHTML();
                break;
            } else {
                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $file = $form->getElement('file');
                $file_array = $file->getValue();
                $timestamp = time();
                $clean_name = basicFile::cleanupName($file_array['name']);
                if(empty($clean_name)){
                    $DEFAULT_ERROR[] = "ERROR: Problem uploading file...";
                    return;
                }
                $filename = $timestamp . '-' . $clean_name;
                if ($file_array['tmp_name'] != "") {
                    $f = $file->moveUploadedFile($GLOBALS['UPLOADS_PATH'] . "/mis_inventory_contracts", $filename);
                    if (!$f) {
                        $DEFAULT_ERROR[] = "ERROR: Problem uploading file...<br/> $f";
                    }
                }
                $vars ['filename'] = $filename;
                // Insert WC File
                $e = Tld_Mis_Inventory_Contract::insert($vars);
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "ERROR: Problem adding contract File...<br/>Reason: $e";
                    break;
                }
            }
            $body .= "Contract File uploaded successfully!";
            break;
        case 'del':
            // Check permissions
            if (!$user->isInGroup(["gg_MIS", "gg_ADMIN"])) {
                $DEFAULT_ERROR[] = "ERROR: You do not have permission to access this page.";
                break;
            }
            // Check File ID
            if (!empty($file_id) && is_numeric($file_id)) {
                $del = Tld_Mis_Inventory_Contract::deleteFile($file_id);
                if (is_string($del)) {
                    $DEFAULT_ERROR[] = "INTERNAL ERROR: File not deleted!<br/>Reason: $del; $e";
                } else {
                    $body = "File " . $file['filename'] . " deleted successfully!";
                }
            } else {
                $DEFAULT_ERROR[] = "ERROR: File id empty or invalid!";
            }
            break;
        case 'getAttachment':
            $template="NO_TEMPLATE";
            Tld_Mis_Inventory_Contract::outAttachment($fileid);
            break;
    }
    $report = new tldReportColumnar(
        Tld_Mis_Inventory_Contract::byParent($id),
        [
            "xItems" => [
                "id" => "File#",
                "start_dt" => "Start Date",
                "end_dt" => "End Date",
                "account" => "Account",
                "support" => "Support",
                "link" => "Link",
                "filename" => "Filename",
            ],
            "functions" => [
                "View" => [
                    "url" =>"$php_self?m[0]=inventory&m[1]=view&m[2]=contract&m[3]=getAttachment&id=$id",
                    "param" => ['fileid' => 'id'],
                    "img" => "/shared/icons/application/file.png",
                ],
                "Delete" => [
                    "url" => "$php_self?m[0]=inventory&m[1]=view&m[2]=contract&m[3]=del&id=$id",
                    "param" => ['file_id' => 'id'],
                    "img" => "/shared/icons/application/delete.png",
                ],
            ],
            "title" => "Contracts",
        ]
    );
    $body .= $report->fetch();
    break;
	case 'label':
		if (!$item->isEmpty()) {
			$item->outPDFLabel();
			exit;
		} else {
			$smarty->assign("error", "No id TLD MIS INV id set");
		}
		break;
	case 'edit':
        if(!($user->isInGroup(['GG_MISINV_tabletManager','GG_MISINV_fixedAssetManager','gg_MIS','ROLE_MISM','ROLE_CIO','superuser']) || ($user->isInGroup(['prod_IT_referent']) && $itemHeader['destination_id'] === $user->getID()))){
            $DEFAULT_ERROR[] = "ERROR: You do not have access to this module!";
            return;
        }

		$DEFAULT_TITLE .= '\Edit';
		$stateList = Tld_Mis_Inventory_Item::getStateList(Tld_Mis_Inventory_Item::getCategory($id));

		// Form
		$form = new HTML_QuickForm('frmNew', 'post');
		$form->addElement('hidden', 'm[0]', 'inventory');
		$form->addElement('hidden', 'm[1]', 'view');
		$form->addElement('hidden', 'm[2]', 'edit');
		$form->addElement('hidden', 'id', $id);
		$form->addElement('header', 'title', "Edit item#$id");

		include('form.inventory.item.php');
		$form->setDefaults($itemHeader);
		$form->setDefaults(
			[
				"type_group[type_id]" => $itemHeader['type_id'],
				"dt_warranty_end[warranty_date]"=>$itemHeader['dt_warranty_end'],
			]
		);
		if (!$form->validate()) {
			$body .= $form->toHTML();
			break;
		}

		$vars = tldUtils::cleanupFormInput($form->exportValues());
        if(!empty($vars['type_group']['type_id'])){
            $vars['type_id'] = $vars['type_group']['type_id'];
        }else{
            $vars['type_id'] = $vars['type_id'];
        }
		if (empty($vars['dt_warranty_end']['warranty_date'])) {
			$vars['dt_warranty_end'] = date('Y-m-d', strtotime($vars['dt_warranty_end']['warranty_year']));
		}else{
			$vars['dt_warranty_end'] = $vars['dt_warranty_end']['warranty_date'];
		}
		// update
        $fieldsToUpdate = [
            'dt_warranty_end', 'type_id', 'description', 'brand_id', 'model',
            'manufacturer_sn', 'tld_sn', 'fixasset_id', 'state', 'buyer_bu_id', 'buyer_dpt_id', 'hidden', 'qty', 'notify'
        ];
		$e = $item->update($vars, $fieldsToUpdate);
		if (is_string($e)) {
			$DEFAULT_ERROR[] = "ERROR: Can not add new item. Reason: $e";
			break;
		}
		// Check what was updated
		$updated = [];
		$itemFields = Tld_Mis_Inventory_Item::getFields();
		$headerBefore = $item->header;
		$item->refresh();
		$headerAfter = $item->header;
		foreach ($itemFields as $field => $label) {
			if ($headerBefore[$field] <> $headerAfter[$field]) {
				$updated[] = "<li>$label updated from {$headerBefore[$field]} to $headerAfter[$field]</li>";
			}
		}
		// log
		$log = tldDatabase::escape("Fields updated:<ul>" . implode('', $updated) . "</ul>");
		$e = $item->addLogEntry($user->getID(), $log);
		// confirm
		$body .= "<p>Item successfully updated!</p>$log";
		break;
	case 'assign':
	case 'reassign':
        if(!($user->isInGroup(['GG_MISINV_tabletManager','GG_MISINV_fixedAssetManager','gg_MIS','ROLE_MISM','ROLE_CIO','superuser']) || ($user->isInGroup(['prod_IT_referent']) && $itemHeader['destination_id'] === $user->getID()))){
            $DEFAULT_ERROR[] = "ERROR: You do not have access to this module!";
            return;
        }
		switch ($m[3]) {
			case "selectLocation":
				$smarty->assign("locations", tldLocation::getLocationList("smartyOptions"));
				$smarty->assign("NEXT_STEP", "$php_self?m[0]=inventory&m[1]=view&m[2]=$m[2]&m[3]=assignLocation&id=$id&location=");
				$body = $smarty->fetch("manufacturing/erp/select.erp.tpl");
				break;
			case "assignLocation":
				$erp = tldLocation::getERPByLocation(tldLocation::getLocationByID($location));
				$ModelList = [];
				foreach ($type as $k => $v) {
					if ($k == 'PEOPLE') {
						$ModelList[$k] = tldDirectory::getUserlistByERP(tldLocation::getIDByERP($erp), "smartyOptions");
					}
					if ($k == 'LOCATION') {
						$ModelList[$k] = TldMIS::getBuildingListByLocation($location, 'smartyOptions');
					}
				}

				$form = new HTML_QuickForm('formSelectAssignee', 'post');
				$form->addElement('hidden', 'm[0]', 'inventory');
				$form->addElement('hidden', 'm[1]', 'view');
				$form->addElement('hidden', 'm[2]', $m[2]);
				$form->addElement('hidden', 'm[3]', 'assignLocation');
				$form->addElement('hidden', 'id', $id);
				$form->addElement('hidden', 'erp', $erp);
				$form->addElement('select', 'assignee', 'Assignment', TldMIS::getBuildingListByLocation($location, 'smartyOptions'));
				$form->addElement('text', 'dt_from', 'Date from',
					["class" => "datepicker"]);
				$form->addElement('text', 'dt_to', 'Date to',
					["class" => "datepicker"]);
				$form->addElement('textarea', 'comment', 'Comments', ["wrap" => "VIRTUAL", "cols" => "40", "rows" => "8"]);
				$form->addElement('text', 'task_id', "Sequence# (if any)");
				$form->addElement('submit', 'btnSubmit', 'Submit');
				$form->addRule("assignee", "Required...", 'required');
				$form->addRule("dt_from", "Required...", 'required');
				$form->addRule('task_id', 'Should be numeric', 'numeric');
				$form->setDefaults($set_defaults);
				$form->setDefaults(["dt_from" => date("Y-m-d")]);
				if (!$form->validate()) {
					$body = $form->toHTML();
					break;
				}
				$vars = tldUtils::cleanupFormInput($form->exportValues());
				$dt_from = $vars['dt_from'];
				$dt_to = $vars['dt_to'];
				$module = $vars['m'][2];
				$newid = $item->$module([
					"item_id" => $vars['id'],
					"poster_id" => $user->getID(),
					"destination_type" => 'LOCATION',
					"destination_id" => $vars['assignee'],
					"dt_from" => $dt_from,
					"dt_to" => $dt_to,
					"comment" => $vars['comment'],
					"task_id" => $vars['task_id'],
				]);
				if ((!is_numeric($newid) && $module == 'assign') || (!empty($newid) && $module == 'reassign')) {
					$DEFAULT_ERROR[] = "ERROR: There was a problem during assignment. $newid";
					break;
				}
				$FIELD_DESIGNATION = [
					'destination_type' => 'Destination Type',
					'destination' => 'Destination',
					'dt_from' => 'Date From',
					'dt_to' => 'Date To',
					'comment' => 'Comment',
					'task_id' => 'Task ID',
				];
				$fields_to_log = ['destination_type', 'destination', 'dt_from', 'dt_to', 'comment', 'task_id'];
				$item_header = $item->getHeader();

				foreach ($fields_to_log as $field) {
					$logs[] = "<li><b>{$FIELD_DESIGNATION[$field]}</b>: '{$item_header[$field]}'</li>";
				}
				$msg = "Assignment Record:<br><ul>" . implode("", $logs) . "</ul>";
				$e = $item->addLogEntry(
					$user->getID(),
					TldDatabase::escape($msg)
				);
				if (is_string($e)) {
					$DEFAULT_ERROR[] = "ERROR: Problem adding logs...<br/>Reason: $e";
				}
				$body .= <<<EOF
<a href="$php_self?m[0]=inventory&m[1]=view&id=$id">Assign complete, click here to see details...</a>
EOF;
			break;
			default:
				$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="$php_self?m[0]=inventory&m[1]=view&m[2]=$m[2]&m[3]=selectLocation&id=$id">Assign to Location</a>
EOF;
				$form = new HTML_QuickForm('formSelectAssignee', 'post');
				$form->addElement('hidden', 'm[0]', 'inventory');
				$form->addElement('hidden', 'm[1]', 'view');
				$form->addElement('hidden', 'm[2]', $m[2]);
				$form->addElement('hidden', 'id', $id);
				$form->addElement('select', 'destination', 'Assignee',
                    tldDirectory::getUserlistByERP('', "smartyOptions")
				);
				$form->addElement('text', 'dt_from', 'Date from',
					["class" => "datepicker"]);
				$form->addElement('text', 'dt_to', 'Date to',
					["class" => "datepicker"]);
				$form->addElement('textarea', 'comment', 'Comments', ["wrap" => "VIRTUAL", "cols" => "40", "rows" => "8"]);
				$form->addElement('text', 'task_id', "Sequence# (if any)");
				$form->addElement('submit', 'btnSubmit', 'Submit');
				$form->addRule("destination", "Required...", 'required');
				$form->addRule("dt_from", "Required...", 'required');
				$form->addRule('task_id', 'Should be numeric', 'numeric');
				$form->setDefaults($set_defaults);
				$form->setDefaults(["dt_from" => date("Y-m-d")]);
				if (!$form->validate()) {
					$body .= $form->toHTML();
					break;
				}
				$vars = tldUtils::cleanupFormInput($form->exportValues());
				$dt_from = $vars['dt_from'];
				$dt_to = $vars['dt_to'];
				$module = $vars['m'][2];
				$newid = $item->$module([
					"item_id" => $vars['id'],
					"poster_id" => $user->getID(),
					"destination_type" => 'PEOPLE',
					"destination_id" => $vars['destination'],
					"dt_from" => $dt_from,
					"dt_to" => $dt_to,
					"comment" => $vars['comment'],
					"task_id" => $vars['task_id'],
				]);
				if ((!is_numeric($newid) && $module == 'assign') || (!empty($newid) && $module == 'reassign')) {
					$DEFAULT_ERROR[] = "ERROR: There was a problem during assignment. $newid";
					break;
				}
				$FIELD_DESIGNATION = [
					'destination_type' => 'Destination Type',
					'destination' => 'Destination',
					'dt_from' => 'Date From',
					'dt_to' => 'Date To',
					'comment' => 'Comment',
					'task_id' => 'Task ID',
				];
				$fields_to_log = ['destination_type', 'destination', 'dt_from', 'dt_to', 'comment', 'task_id'];
				$item_header = $item->getHeader();

				foreach ($fields_to_log as $field) {
					$logs[] = "<li><b>{$FIELD_DESIGNATION[$field]}</b>: '{$item_header[$field]}'</li>";
				}
				$msg = "Assignment Record:<br><ul>" . implode("", $logs) . "</ul>";
				$e = $item->addLogEntry(
					$user->getID(),
					TldDatabase::escape($msg)
				);
				if (is_string($e)) {
					$DEFAULT_ERROR[] = "ERROR: Problem adding logs...<br/>Reason: $e";
				}
				$body .= <<<EOF
<a href="$php_self?m[0]=inventory&m[1]=view&id=$id">Assign complete, click here to see details...</a>
EOF;
		}
		break;
	case 'unassign':
        if(!($user->isInGroup(['GG_MISINV_tabletManager','GG_MISINV_fixedAssetManager','gg_MIS','ROLE_MISM','ROLE_CIO','superuser']) || ($user->isInGroup(['prod_IT_referent']) && $itemHeader['destination_id'] === $user->getID()))){
            $DEFAULT_ERROR[] = "ERROR: You do not have access to this module!";
            return;
        }
		if (!empty($id) && is_numeric($id)) {
			$del = $item->unassign($id);
			if (is_string($del)) {
				$DEFAULT_ERROR[] = "INTERNAL ERROR: Unassign failed!<br/>Reason: $del";
			} else {
				$body .= "Device #$id unassign successfully!";
				// Logging if warranty_status <> PENDING
				$msg = "Device #$id unassigned!";
				$log = $item->addLogEntry(
					$user->getID(),
					TldDatabase::escape($msg)
				);
				if (is_string($log)) {
					$DEFAULT_ERROR[] = "ERROR: Problem adding logs...<br/>Reason: $e";
				}
			}
		}
		break;
	case 'history':
            $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=inventory&m[1]=view&m[2]=history&m[3]=add&id=$id">Add</a>
EOF;

        if ($m[3] === 'add') {
            $form = new HTML_QuickForm('frm', 'post');
            $form->addElement('hidden', 'm[0]', 'inventory');
            $form->addElement('hidden', 'm[1]', 'view');
            $form->addElement('hidden', 'm[2]', 'history');
            $form->addElement('hidden', 'm[3]', 'add');
            $form->addElement('hidden', 'id', $id);
            $form->addElement('header', 'header', 'Add log');
            $form->addElement('textarea', 'comment', 'Comment');
            $form->addElement('submit', 'btnSubmit', 'Submit');

            if (!$form->validate() && !$form->isSubmitted()) {
                $body .= $form->toHTML();
                break;
            }
            $vars = $form->exportValues();
            $e = $item->addLogEntry(
                $user->getID(),
                TldDatabase::escape($vars['comment'])
            );
            if (is_string($e)) {
                $DEFAULT_ERROR[] = "ERROR: Problem adding logs...<br/>Reason: $e";
            }
        }

		$report = new tldReportColumnar(
			$item->getLogs(),
			[
				"xItems" => [
					"id" => "#",
					"date" => "Date",
					"poster_fullname" => "Poster",
					"comment" => "Comment",
				],
				"title" => "Logs",
			]
		);
		$body .= $report->fetch();
		break;
	case 'files':
		$DEFAULT_TITLE .= "\Files";
        if(!($user->isInGroup(['GG_MISINV_tabletManager','GG_MISINV_fixedAssetManager','gg_MIS','ROLE_MISM','ROLE_CIO','superuser']) || ($user->isInGroup(['prod_IT_referent']) && $itemHeader['destination_id'] === $user->getID()))){
            $DEFAULT_ERROR[] = "ERROR: You do not have access to this module!";
            return;
        }
		$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/en/private/common/index.php?m[0]=files&m[1]=form&m[2]=newFile&module=MISINV_ITM&parent_id=$id">Add File</a>
EOF;
		$report = new tldReportColumnar(
			$item->getFiles(),
			[
				"xItems" => [
					"id" => "File#",
					"date" => "Date",
					"description" => "Description",
					"filename" => "Filename",
				],
				"functions" => [
					"View" => [
						"url" => "/en/private/common/index.php?m[0]=files&m[1]=view&m[2]=out&id=",
						"param" => ['id' => 'id'],
						"img" => "/shared/icons/application/file.png",
					],
					"Edit" => [
						"url" => "/en/private/common/index.php?m[0]=files&m[1]=form&m[2]=editDescription&id=",
						"param" => ['id' => 'id'],
						"img" => "/shared/icons/application/edit.png",
					],
					"Delete" => [
						"url" => "/en/private/common/index.php?m[0]=files&m[1]=view&m[2]=delconf&id=",
						"param" => ['id' => 'id'],
						"img" => "/shared/icons/application/delete.png",
					],
				],
				"title" => "Files",
			]
		);
		$body .= $report->fetch();
		break;
	default:
		$body .= Tld_Mis_Inventory_Module::getItemView($item);
		break;
}
