<?php
$DEFAULT_TITLE .="\ER Upgrade";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=er_upgrade">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=er_upgrade&m[1]=form&m[2]=byNum">By Number</a>
EOF;
if($user->isInGroup(array("gg_MIS"))){
	$DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="er_upgrade/er_upgrade_admin.php">Admin</a>
EOF;
	
}

switch($m[1]){
	case 'form':
		switch($m[2]){
			case 'byNum':
				$DEFAULT_TITLE .= "\ER Upgrade by Number";
				$form = new HTML_QuickForm('frmByNum', 'post');
				$form->addElement(	'hidden', 'm[0]', 'er_upgrade');
				$form->addElement(	'hidden', 'm[1]', 'view');
				$form->addElement(	'header', 'title', "ER Upgrade by Number");
				$form->addElement(	'text', 'id', 'ER Upgrade#');
				$form->addElement(	'submit', 'btnSubmit', 'Submit');
				$body .= $form->toHTML();
			break;
		}
	break;
	case 'view':
		if(empty($id) OR !is_numeric($id)){
			$DEFAULT_ERROR[]=  "ERROR: no ID set...";
			return;
		}
		$er_upgrade = new tldEquipment_Upgrade($id);
		if($er_upgrade->isEmpty()){
			$DEFAULT_ERROR[]=  "ERROR: No ER Upgrade#$id found...";
			return;
		}
		
		$header = $er_upgrade->getHeader();
		$DEFAULT_TITLE .= "\ER Upgrade#$id";
		$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=er_upgrade&m[1]=view&id=$id">General</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=er_upgrade&m[1]=view&m[2]=edit&id=$id" title="Edit ER Upgrade#$id">Edit</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=er_upgrade&m[1]=view&m[2]=log&id=$id" title="Logs ER Upgrade#$id">Logs</a>
EOF;

	switch($m[2]){
		case 'log':
			$report = new tldReportColumnar(
			$er_upgrade->getFullLog(),
				array(
					"xItems"=>array(
							"id"                =>"ID#",
							"date"              =>"Date",
							"poster_fullname"   =>"Poster",
							"module"            =>"Module",
							"comment"           =>"Comment"
					)
				)
			);
			$body .= $report->fetch();
		break 2;
		case 'edit':
			if(!$user->isInGroup(array("role_PSM","gg_PARTS","gg_SERVICE","gg_MIS"))){
				$DEFAULT_ERROR[]=  "ERROR: You do not have the permission to access this page!";
				break;
			}
			// Form
			$form = new HTML_QuickForm('frmEdit', 'post');
			$form->addElement(  'hidden', 'm[0]', 'er_upgrade');
			$form->addElement(  'hidden', 'm[1]', 'view');
			$form->addElement(  'hidden', 'm[2]', 'edit');
			$form->addElement(  'hidden', 'id',   $id);
			$form->addElement(  'header', 'title', "Edit ER Upgrade#$id");
    		$form->addElement('date',   	'dt_upgrade',	'Effective Upgrade Date', 	array("format"=>"Y-m-d", 'addEmptyOption'=>FALSE, "minYear"=>date("Y")-3, "maxYear"=>date("Y")));
    		$form->addElement('textarea', 	'description', 	'Upgrade Description', 		array("wrap"=>"VIRTUAL", "cols"=>"40", "rows"=>"8"));
    		$form->addElement('file', 		'file', 		'Attachment');
    		$form->addElement('submit', 	'btnSubmit', 	'Submit');
			$required=array('dt_upgrade', 'description');
			foreach($required as $key=>$field) $form->addRule($field, 'Required', 'required');
			$form->setDefaults($header);
			
			if(!$form->validate()){
				$body = $form->toHTML();
				break 2;
			}
			
			$vars = tldUtils::cleanupFormInput($form->exportValues());
			$dt_upgrade = vsprintf('%1$04d-%2$02d-%3$02d', $vars['dt_upgrade']);
			$vars['dt_upgrade'] = $dt_upgrade;
			// Check date
			$dt_upgrade=strtotime($dt_upgrade);
			$er = new tldEquipment($header['parent_id']);
			$dt_er=strtotime($er->getCreateDate());
			$diff= ($dt_upgrade - $dt_er)/86400;
			if($diff < 0){
				$DEFAULT_ERROR[]=  "ERROR: Effective Upgrade Date is older than ER creation date!";
				break 2;
			}
			$fields = array('dt_upgrade', 'description');
			$file = $form->getElement("file");
			$file_array = $file->getValue();
			if($file_array['tmp_name']!=""){
				// Check if there is an existing file for this ER Upgrade
				$fileID = tldModFile::byParent($header['id'],"ER_UPGRADE");
				if($fileID){
					$DEFAULT_ERROR[]=  "ERROR: There is already a file linked to this ER Upgrade. Please delete before uploading a new one.";
					break 2;
				}
			}
			$e = $er_upgrade->update($vars, $fields);
			if(is_string($e)){
				$DEFAULT_ERROR[] = "ERROR: Problem updating...<br/>Reason: $e";
				break 2;
			}
			if($file_array['tmp_name']!=""){
				$vars = Array();
				$vars['module'] = "ER_UPGRADE";
				$vars['parent_id'] = $id;
				$vars['filename'] = $file_array['name'];
				$vars['poster'] = $user->getID();
				$e = tldModFile::insert($vars,$file_array);
				$flagFile = 1;
			}
			if(is_string($e)){
				$DEFAULT_ERROR[] = "ERROR: There was a problem attaching the file. Reason: $e";
				$flagFile = 0;
			}
			// Logging
			$FIELD_DESIGNATION = array(
					'dt_upgrade'=>'Effective Upgrade Date',
					'description'=>'Description'
			);
			//Get updated header
			$er_upgrade_updated = new tldEquipment_Upgrade($id);
			$header_updated = $er_upgrade_updated->getHeader();
			$logs = array();
			$fields_to_log = array('dt_upgrade','description');
			//Log if updated header <> original header
			foreach($fields_to_log as $field){
				if($header_updated[$field]!=$header[$field]){
					$logs[]="<li><b>{$FIELD_DESIGNATION[$field]}</b> from '{$header[$field]}' to '{$header_updated[$field]}'</li>";
				}
			}
			if($flagFile == 1) $logs[]="<li><b>New file uploaded</b>'</li>";
			if(count($logs)){
				$msg = "ER Upgrade updated:<br><ul>".implode("",$logs)."</ul>";
				$e = $er_upgrade->addLogEntry(
						$user->getID(),
						TldDatabase::escape($msg)
				);
				if(is_string($e)){
					$DEFAULT_ERROR[] = "ERROR: Problem adding logs...<br/>Reason: $e";
				}
				$body .= "<br>".$msg;
				$body .= "ER Upgrade updated successfully!";
			}
			$body .= getGeneralTab();
		break 2;
		default:
			$body .= getGeneralTab();
		break 2;
	}
	default:
		$rows = tldEquipment_Upgrade::getLatest();
    	$report = new tldReportColumnar(
    		$rows,
    		array(
    				"xItems"=>array(
    						"id"=>"ID#",
    						"parent_id"=>"ER#",
    						"poster_fullname"=>"Poster",
    						"dt_open"=>"Created on",
    						"dt_upgrade"=>"Effective Upgrade Date",
    						"description"=>"Description",
							"pn" => "Part Number",
							"mod_link"=>"File"
    				),
    				"links"=>array(
    						"mod_link"=>array(
    								"url"=>"/en/private/common/index.php?m[0]=files&m[1]=view&id=",
    								"params"=>array("id"=>"mod_file")
    						),
    						"id"=>array(
    								"url"=>"$php_self?m[0]=er_upgrade&m[1]=view",
    								"params"=>array("id"=>"id")
    						),
    						"parent_id"=>array(
    								"url"=>"$php_self?m[0]=equipment&m[1]=view",
    								"params"=>array("id"=>"parent_id")
    						)
    				),
    				"title"	=> "Latest ER Upgrades"
    		)
    	);
		$body .= $report->fetch();
	break;
}

function getGeneralTab(){
	global $er_upgrade;
	$header = $er_upgrade->getHeader();
	$fileID = tldModFile::byParent($header['id'],"ER_UPGRADE");
	if($fileID){
		$header['mod_file'] = $fileID[0]['id'];
	}
	$report = new tldAssocTable(
			$header,
			array(
    				"id"=>"ID#",
    				"parent_id"=>"ER#",
    				"poster_fullname"=>"Poster",
    				"dt_open"=>"Created on",
    				"dt_upgrade"=>"Effective Upgrade Date",
    				"description"=>"Description",
					"pn" => "Part Number",
    				"mod_file"=>"File"
			),
			array(
				  "title"=>"ER Upgrade Details",
				  "links"=>array(
	        				"parent_id"=>"$php_self?m[0]=equipment&m[1]=view&id=",
				  		    "mod_file"=>"/en/private/common/index.php?m[0]=files&m[1]=view&id=",
					  		"pn" => "/en/private/manufacturing/eng/dev.php?m[0]=bom&m[1]=view&erp=&btnSubmit=Submit&date={$header['dt_upgrade']}&pn=",
				  )
			)
	);

	$body.= $report->fetch();
	$er_id = $header['parent_id'];
	$body .= "<br><br> <a href='$php_self?m[0]=equipment&m[1]=view&m[2]=upgrade&id=$er_id'>Click here to get redirected to the ER module</a> ";
	return $body;
}

?>

