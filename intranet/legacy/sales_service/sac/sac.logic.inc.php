<?php
include_once("common.inc.php");
include_once("configurator.inc.php");
include_once("dms.inc.php");

if(!$user->isInGroup(array("gg_ADMIN","acl_tld_cms","acl_tld_cmsl"))){
    $DEFAULT_ERROR[] = "ERROR: You do not have permissions";
    return;
}

$DEFAULT_TITLE .= "\Sales App Configurator (TLD CMS)";
$DEFAULT_MENU.=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sac">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sac&m[1]=element&m[2]=add" title="Add a new Root Element">Add Root Element</a>
&nbsp;|&nbsp;<a href="/en/private/mis/mis.php?m[0]=help&m[1]=dms&id=4534" title="Help DMS">Help DMS</a>
EOF;

$sac = new tldSalesAppConfigurator();
if($sac->isEmpty()){
    $DEFAULT_ERROR[] = "ERROR: Sales App Configurator not found";
    return;
}

switch($m[1]){
case 'element':
    switch($m[2]){
    case 'add':
        $DEFAULT_TITLE .= "\Add";
        if(!$user->isInGroup(array("gg_ADMIN","acl_tld_cms"))){
            $DEFAULT_ERROR[] = "ERROR: You do not have permissions";
            break;
        }
        // Listing
        $typeList = tldSalesAppConfiguratorElement::getTypeList();
        $statusList = tldSalesAppConfiguratorElement::getStatusList();
        $yesNoList = array('Y'=>'Yes','N'=>'No');
        $peopleList = tldDirectory::getUserlist("smartyOptions");
        // Form
        $form = new HTML_QuickForm('frmNew', 'post');
    	$form->addElement(	'hidden', 'm[0]', 'sac');
    	$form->addElement(	'hidden', 'm[1]', 'element');
    	$form->addElement(	'hidden', 'm[2]', 'add');
    	$form->addElement(	'hidden', 'parent_id', $_REQUEST['parent_id']);
    	$form->addElement(	'hidden', 'poster_id', $user->getID());
    	$form->addElement(	'header', 'title', "Element details");
    	$form->addElement(  'select', 'type', 'Type of ressource', array(""=>"")+$typeList);
        $form->addElement(	'text',   'value', 'Value');
    	$form->addElement(	'header', 'title', "Element options");
    	$form->addElement(  'select', 'status', 'Status', array(""=>"")+$statusList);
    	$form->addElement(  'select', 'encryption', 'Enable encryption?', array(""=>"")+$yesNoList);
    	$form->addElement(  'text', 'location', 'Location', array("size"=>"50"));
    	// Rules
        $requiredFields = array('type','value','status','encryption');
    	foreach($requiredFields as $required) $form->addRule($required, 'Required', 'required');
    	$form->addElement(	'submit', 'btnSubmit', 'Submit');
    	$form->setDefaults(array("type"=>"FOLDER"));

    	if(!$form->validate()){
    		$body = $form->toHTML();
    		break;
    	}

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $vars['value'] = stringCleaner($vars['value']);

        if (stringChecker($vars['value'])) {
            $DEFAULT_ERROR[] = "ERROR in the field name or value -- Please don't use the following characters :    ".'\\ / : * ? " < > |';
            break;
        }

		$e = tldSalesAppConfigurator::addElement($vars);
    	if(!is_numeric($e)){
			$DEFAULT_ERROR[] = "INTERNAL ERROR: Element NOT created!<br/>Reason: $e";
			break;
		}

        $element = new tldSalesAppConfiguratorElement($e);
        $log = $element->addLogEntry($user->getID(), "Root Element#$e was created");
        $body .=<<<EOF
			<br/><br/>Element#$e successfully created! <a href="$php_self?m[0]=sac&m[1]=element&m[2]=view&id=$e">Click here to view.</a></br>
EOF;
        break;
    case 'view':
        if(empty($_REQUEST['id']) || !is_numeric($_REQUEST['id'])){
            $DEFAULT_ERROR[]="ERROR: Parameters sent empty or invalid";
            break;
        }
        $id = (int)$_REQUEST['id'];
        $element = new tldSalesAppConfiguratorElement($id);
        if($element->isEmpty()){
            $DEFAULT_ERROR[]="ERROR: Element not found";
            break;
        }
        $header = $element->getHeader();

        $DEFAULT_TITLE .= "\Element#$id";
        $DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sac&m[1]=element&m[2]=view&id=$id">General</a>
EOF;
        if($element->getParentID() != 0){
        	$DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=sac&m[1]=element&m[2]=view&id={$element->getParentID()}">Go to Parent</a>
EOF;
        }

        $DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=sac&m[1]=element&m[2]=view&m[3]=edit&id=$id" title="Edit Element#$id">Edit</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sac&m[1]=element&m[2]=view&m[3]=delete&id=$id" title="Delete Element#$id">Delete</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sac&m[1]=element&m[2]=view&m[3]=log&id=$id" title="Activity logs">Log</a>
EOF;

        if($element->getTypeValue() == "FOLDER"){
        	$DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=sac&m[1]=element&m[2]=view&m[3]=addfolder&id=$id" title="Add a Folder to Element#$id">Add Folder</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sac&m[1]=element&m[2]=view&m[3]=adddms&id=$id" title="Add a DMS to Element#$id">Add DMS</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sac&m[1]=element&m[2]=view&m[3]=addgallery&id=$id" title="Add a Gallery to Element#$id">Add Gallery</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sac&m[1]=element&m[2]=view&m[3]=addlink&id=$id" title="Add a Youtube Link to Element#$id">Add Youtube link</a>
EOF;
        }

        switch($m[3]){
        case 'addfolder':
        	$DEFAULT_TITLE .= "\Add Folder";
        	if(!$user->isInGroup(array("gg_ADMIN","acl_tld_cms"))){
        	    $DEFAULT_ERROR[] = "ERROR: You do not have permissions";
        	    break;
        	}
        	// Listing
        	$statusList = tldSalesAppConfiguratorElement::getStatusList();
        	$peopleList = tldDirectory::getUserlist("smartyOptions");
        	// Form
        	$form = new HTML_QuickForm('frmNew', 'post');
        	$form->addElement(	'hidden', 'm[0]', 'sac');
        	$form->addElement(	'hidden', 'm[1]', 'element');
        	$form->addElement(	'hidden', 'm[2]', 'view');
        	$form->addElement(	'hidden', 'm[3]', 'addfolder');
        	$form->addElement(	'hidden', 'id', $id);
        	$form->addElement(	'hidden', 'parent_id', $id);
        	$form->addElement(	'hidden', 'poster_id', $user->getID());
        	$form->addElement(	'header', 'title', "Folder name");
        	$form->addElement(  'text',   'type', 'Type', array('disabled'=>'disabled'));
        	$form->addElement(	'text',   'value', 'Name');
        	$form->addElement(	'header', 'title', "Folder options");
        	$form->addElement(  'select', 'status', 'Status', array(""=>"")+$statusList);
        	// Rules
        	$requiredFields = array('value','status');
        	foreach($requiredFields as $required) $form->addRule($required, 'Required', 'required');
        	$form->setDefaults(array("type"=>"FOLDER"));
        	$form->addElement(	'submit', 'btnSubmit', 'Submit');

        	if(!$form->validate()){
        		$body = $form->toHTML();
        		break;
        	}

        	$vars = tldUtils::cleanupFormInput($form->exportValues());

            if (stringChecker($vars['value'])) {
                $DEFAULT_ERROR[] = "ERROR in the field name or value -- Please don't use the following characters :    ".'\\ / : * ? " < > |';
                break;
            }

        	$e = tldSalesAppConfigurator::addElement($vars);
        	if(!is_numeric($e)){
        		$DEFAULT_ERROR[] = "INTERNAL ERROR: Folder NOT created!<br/>Reason: $e";
        		break;
        	}else{
        		$element = new tldSalesAppConfiguratorElement($e);
        		$log = $element->addLogEntry($user->getID(), "Element#$e (Folder) was created");
        		$body .=<<<EOF
			<br/><br/>Element#$e (Folder) successfully created! <a href="$php_self?m[0]=sac&m[1]=element&m[2]=view&id=$e">Click here to view.</a> or <a href="$php_self?m[0]=sac&m[1]=element&m[2]=view&id=$id">here to go back to Parent.</a></br>
EOF;
        	}
        break;
        case 'adddms':
        	$DEFAULT_TITLE .= "\Add DMS";
        	// Listing
            $dmsList = tldUtils::getSqlToAssocArray("select id, LEFT(title, 35) AS 'title' from dms");
            $dmsList[] = ["id"=>0,"title"=>""];
        	$statusList = tldSalesAppConfiguratorElement::getStatusList();
        	$yesNoList = array('Y'=>'Yes','N'=>'No');
        	$peopleList = tldDirectory::getUserlist("smartyOptions");
        	// Form
        	$form = new HTML_QuickForm('frmNew', 'post');
        	$form->addElement(	'hidden', 'm[0]', 'sac');
        	$form->addElement(	'hidden', 'm[1]', 'element');
        	$form->addElement(	'hidden', 'm[2]', 'view');
        	$form->addElement(	'hidden', 'm[3]', 'adddms');
        	$form->addElement(	'hidden', 'id', $id);
        	$form->addElement(	'hidden', 'parent_id', $id);
        	$form->addElement(	'hidden', 'poster_id', $user->getID());
        	$form->addElement(	'header', 'title', "DMS Number");
        	$form->addElement(  'text',   'type', 'Type', array('disabled'=>'disabled'));
        	$form->addElement(	'text',   'value', 'DMS#', ["id"=>"value"]);
        	$form->addElement("select", "value_title", "DMS title", array_column($dmsList, "title", 'id'), ["id"=>"value_title", "disabled"=>""]);
        	$form->addElement(	'header', 'title', "DMS options");
        	$form->addElement(  'select', 'status', 'Status', array(""=>"")+$statusList);
        	$form->addElement(  'select', 'encryption', 'Enable encryption?', array(""=>"")+$yesNoList);
        	// Rules
        	$requiredFields = array('value','status','encryption');
        	foreach($requiredFields as $required) $form->addRule($required, 'Required', 'required');
        	$form->setDefaults(array("type"=>"DMS"));
        	$form->addElement(	'submit', 'btnSubmit', 'Submit');

        	if(!$form->validate()){
                $js = <<<JS
$(document).ready(function() {
    function changeDMS() {
        $('#value_title').val($('#value').val()).change();
    }
    $('#value_title').val(0).change();
    $('#value').on("change keyup paste keypress", changeDMS);
});
JS;
                $smarty->assign("html_head", $smarty->get_template_vars("html_head") .'<script type="text/javascript">' . $js . "</script>");
        		$body = $form->toHTML();
        		break;
        	}

        	$vars = tldUtils::cleanupFormInput($form->exportValues());
        	$e = tldSalesAppConfigurator::addElement($vars);
        	if(!is_numeric($e)){
        		$DEFAULT_ERROR[] = "INTERNAL ERROR: DMS NOT created!<br/>Reason: $e";
        		break;
        	}else{
        		$element = new tldSalesAppConfiguratorElement($e);
        		$log = $element->addLogEntry($user->getID(), "Element#$e (DMS) was created");
        		$body .=<<<EOF
			<br/><br/>Element#$e (DMS) successfully created! <a href="$php_self?m[0]=sac&m[1]=element&m[2]=view&id=$e">Click here to view.</a> or <a href="$php_self?m[0]=sac&m[1]=element&m[2]=view&id=$id">here to go back to Parent.</a></br>
EOF;
        	}
        break;
        case 'addgallery':
            $DEFAULT_TITLE .= "\Add Gallery";
            // Listing
            $statusList = tldSalesAppConfiguratorElement::getStatusList();
            $yesNoList = array('Y'=>'Yes','N'=>'No');
            $peopleList = tldDirectory::getUserlist("smartyOptions");
            // Form
            $form = new HTML_QuickForm('frmNew', 'post');
            $form->addElement(	'hidden', 'm[0]', 'sac');
            $form->addElement(	'hidden', 'm[1]', 'element');
            $form->addElement(	'hidden', 'm[2]', 'view');
            $form->addElement(	'hidden', 'm[3]', 'addgallery');
            $form->addElement(	'hidden', 'id', $id);
            $form->addElement(	'hidden', 'parent_id', $id);
            $form->addElement(	'hidden', 'poster_id', $user->getID());
            $form->addElement(	'header', 'title', "Gallery");
            $form->addElement(	'static', null, null,"Upload an <b>image</b> or enter a <b>URL</b> pointing to one");
            $form->addElement(  'text',   'type', 'Type', array('disabled'=>'disabled'));
            $form->addElement(	'text',   'value',	  'Gallery Name');
            $form->addElement(  'file',   'file',	  'Attachment');
            $form->addElement(	'static', null, null,"URL must finish with the picture's extension (Example: .jpg)");
            $form->addElement(  'text',   'location', 'Link URL', array("size"=>"50"));
            $form->addElement(	'header', 'title', "Gallery options");
            $form->addElement(  'select', 'status', 'Status', array(""=>"")+$statusList);
            $form->addElement(  'select', 'encryption', 'Enable encryption?', array(""=>"")+$yesNoList);
            // Rules
            $requiredFields = array('status','encryption','value');
            foreach($requiredFields as $required) $form->addRule($required, 'Required', 'required');
            $form->setDefaults(array("type"=>"GALLERY"));
            $form->addElement(	'submit', 'btnSubmit', 'Submit');

            if(!$form->validate()){
                $body = $form->toHTML();
                break;
            }

            $vars = tldUtils::cleanupFormInput($form->exportValues());

            if (stringChecker($vars['value'])) {
                $DEFAULT_ERROR[] = "ERROR in the field name or value -- Please don't use the following characters :    ".'\\ / : * ? " < > |';
                break;
            }

            // Check temp file
            $file = $form->getElement("file");
            $file_array = $file->getValue();
            if($file_array['tmp_name']=="" && empty($vars['location'])){
                $DEFAULT_ERROR[] = "ERROR: You need to enter a link URL if you choose not to upload a gallery";
                $body = $form->toHTML();
                break;
            }
            $e = tldSalesAppConfigurator::addElement($vars);
            if(!is_numeric($e)){
                $DEFAULT_ERROR[] = "INTERNAL ERROR: Gallery NOT created!<br/>Reason: $e";
                break;
            }
            if($file_array['tmp_name']!=""){
                $data_update = array();
                $modfile = array();
                $modfile['parent_id'] = $e;
                $modfile['module'] = "SAC";
                $modfile['poster'] = $user->getID();
                $modfile['description'] = $vars['value']."_$e";
                $modfile['level'] = 0;
                $f = tldModFile::insert($modfile,$file_array);
                if(is_string($f)){
                    $DEFAULT_ERROR[] = "ERROR: There was a problem attaching the file. Reason: $f";
                }else{
                    $body .= "File successfully attached...";
                }
                $data_update['location'] = $f;
            }
            $element = new tldSalesAppConfiguratorElement($e);
            $u = $element->update($data_update);
            if(is_string($u)){
                $DEFAULT_ERROR[] = "ERROR: Problem updating...<br/>Reason: $u";
                break;
            }
            $log = $element->addLogEntry($user->getID(), "Element#$e (Gallery) was created");
            $body .=<<<EOF
			<br/><br/>Element#$e (DMS) successfully created! <a href="$php_self?m[0]=sac&m[1]=element&m[2]=view&id=$e">Click here to view.</a> or <a href="$php_self?m[0]=sac&m[1]=element&m[2]=view&id=$id">here to go back to Parent.</a></br>
EOF;
        break;
        case 'addlink':
        	$DEFAULT_TITLE .= "\Add Youtube Link";
        	// Listing
        	$statusList = tldSalesAppConfiguratorElement::getStatusList();
        	// Form
        	$form = new HTML_QuickForm('frmNew', 'post');
        	$form->addElement(	'hidden', 'm[0]', 'sac');
        	$form->addElement(	'hidden', 'm[1]', 'element');
        	$form->addElement(	'hidden', 'm[2]', 'view');
        	$form->addElement(	'hidden', 'm[3]', 'addlink');
        	$form->addElement(	'hidden', 'id', $id);
        	$form->addElement(	'hidden', 'parent_id', $id);
        	$form->addElement(	'hidden', 'poster_id', $user->getID());
        	$form->addElement(	'header', 'title', "Link name");
        	$form->addElement(  'text',   'type', 'Type', array('disabled'=>'disabled'));
        	$form->addElement(	'text',   'value', 'Name');
        	$form->addElement(	'header', 'title', "Link options");
        	$form->addElement(	'static', null, null,"https://www.youtube.com/watch?v=<b>sMzQJYtcpiA</b>");
        	$form->addElement(	'static', null, null,"In the Youtube link example above, the characters in bold are the Youtube Link ID#");
        	$form->addElement(  'text',   'location', 'Youtube Link ID#');
        	$form->addElement(  'select', 'status', 'Status', array(""=>"")+$statusList);
        	// Rules
        	$requiredFields = array('value','status','location');
        	foreach($requiredFields as $required) $form->addRule($required, 'Required', 'required');
        	$form->setDefaults(array("type"=>"LINK"));
        	$form->addElement(	'submit', 'btnSubmit', 'Submit');

        	if(!$form->validate()){
        		$body = $form->toHTML();
        		break;
        	}

        	$vars = tldUtils::cleanupFormInput($form->exportValues());

            if (stringChecker($vars['value'])) {
                $DEFAULT_ERROR[] = "ERROR in the field name or value -- Please don't use the following characters :    ".'\\ / : * ? " < > |';
                break;
            }

        	$e = tldSalesAppConfigurator::addElement($vars);
        	if(!is_numeric($e)){
        		$DEFAULT_ERROR[] = "INTERNAL ERROR: Link NOT created!<br/>Reason: $e";
        		break;
        	}else{
        		$element = new tldSalesAppConfiguratorElement($e);
        		$log = $element->addLogEntry($user->getID(), "Element#$e (Link) was created");
        		$body .=<<<EOF
			<br/><br/>Element#$e (Link) successfully created! <a href="$php_self?m[0]=sac&m[1]=element&m[2]=view&id=$e">Click here to view.</a> or <a href="$php_self?m[0]=sac&m[1]=element&m[2]=view&id=$id">here to go back to Parent.</a></br>
EOF;
        	}
        break;
        case 'edit':
        	$DEFAULT_TITLE .= "\Edit";
        	if(!$user->isInGroup(array("gg_ADMIN","acl_tld_cms"))){
        	    $DEFAULT_ERROR[] = "ERROR: You do not have permissions";
        	    break;
        	}
        	$typeList = tldSalesAppConfiguratorElement::getTypeList();
        	$statusList = tldSalesAppConfiguratorElement::getStatusList();
        	$yesNoList = array('Y'=>'Yes','N'=>'No');
        	$peopleList = tldDirectory::getUserlist("smartyOptions");
        	$form = new HTML_QuickForm('frmNew', 'post');
        	$form->addElement(	'hidden', 'm[0]', 'sac');
        	$form->addElement(	'hidden', 'm[1]', 'element');
        	$form->addElement(	'hidden', 'm[2]', 'view');
        	$form->addElement(	'hidden', 'm[3]', 'edit');
        	$form->addElement(	'hidden', 'id', $id);
        	$form->addElement(	'header', 'title', "Element details");
        	$form->addElement(  'select', 'type', 'Type of ressource', array(""=>"")+$typeList);
        	$form->addElement(	'text',   'value', 'Value');
        	$form->addElement(	'header', 'title', "Element options");
        	$form->addElement(  'select', 'status', 'Status', array(""=>"")+$statusList);
        	$form->addElement(  'select', 'encryption', 'Enable encryption?', array(""=>"")+$yesNoList);
        	$form->addElement(  'text', 'location', 'Location', array("size"=>"50"));
        	// Rules
        	$requiredFields = array('type','value','status');
        	foreach($requiredFields as $required) $form->addRule($required, 'Required', 'required');
        	$form->addElement(	'submit', 'btnSubmit', 'Submit');
        	$form->addElement(  'reset',  'btnReset',  'Reset');
        	$data_default = array();
        	$options =  $element->getOptions();
        	foreach($options as $option){
        		$data_default[$option['keytag']] = $option['value'];
        	}
        	$data_default = array_merge($header,$data_default);
        	$form->setDefaults($data_default);
        	if(!$form->validate()){
        		$body = $form->toHTML();
        		break;
        	}
        	$vars = $form->exportValues();

            if (stringChecker($vars['value'])) {
                $DEFAULT_ERROR[] = "ERROR in the field name or value -- Please don't use the following characters :    ".'\\ / : * ? " < > |';
                break;
            }

        	$e = $element->update($vars);
        	if(is_string($e)){
        		$DEFAULT_ERROR[] = "ERROR: Problem updating...<br/>Reason: $e";
        		break;
        	}
        	//Get updated header
        	$data_updated = array();
			$element_updated = new tldSalesAppConfiguratorElement($id);
			$header_updated = $element_updated->getHeader();
			$options_updated =  $element_updated->getOptions();
			foreach($options_updated as $option_updated){
				$data_updated[$option_updated['keytag']] = $option_updated['value'];
			}
			$data_updated = array_merge($header_updated,$data_updated);
			$logs = array();
			$fields =  array("type","value","status","encryption","location");
			$FIELD_DESIGNATION = array(
					'type'=>'Type',
					'value'=>'Value',
					'status'=>'Status',
					'encryption'=>'Encryption',
					'location'=>'Location'
			);
			//Log if updated header <> original header
			foreach($fields as $field){
				if($data_updated[$field]!=$data_default[$field]){
					$logs[]="<li><b>{$FIELD_DESIGNATION[$field]}</b> from '{$data_default[$field]}' to '{$data_updated[$field]}'</li>";
				}
			}
			if(count($logs)){
				$msg = "Element#$id updated:<br><ul>".implode("",$logs)."</ul>";
				$e = $element->addLogEntry(
					$user->getID(),
					TldDatabase::escape($msg)
				);
				if(is_string($e)){
					$DEFAULT_ERROR[] = "ERROR: Problem adding logs...<br/>Reason: $e";
				}
				$body .= "<br>".$msg;
			}
        	$body .= "Element#$id updated successfully!";
        	$body .= getGeneralTab();
        break;
        case 'delete':
        	$DEFAULT_TITLE .="\Delete";
        	if(!$user->isInGroup(array("gg_ADMIN"))){
        		$DEFAULT_ERROR[]="ERROR: You do not have permission to delete Elements.";
        		break;
        	}
        	$form = new HTML_QuickForm('frmDelete');
        	$form->addElement('hidden', 	'm[0]', 		'sac');
        	$form->addElement('hidden', 	'm[1]', 		'element');
        	$form->addElement('hidden', 	'm[2]', 		'view');
        	$form->addElement('hidden', 	'm[3]', 		'delete');
        	$form->addElement('hidden', 	'id', 			$id);
        	$form->addElement('header', 	'title', 		"Do you want to delete Element#$id ? This is irreversible and will also delete all child Elements.");
        	$form->addElement('submit', 	'btnSubmit', 	'Confirm');
        	if(!$form->validate()){
        		$body = $form->toHTML();
        		break;
        	}else{
        	    if($element->getTypeValue() == "GALLERY" && is_numeric($element->getLocationValue())){
        	        $modfile = new tldModFile($element->getLocationValue());
        	        if($modfile->isEmpty()){
        	            $DEFAULT_ERROR[]="ERROR: File #".$element->getLocationValue()." not found";
        	        }else{
        	            $delFile = $modfile->delete();
        	            if(is_string($delFile)) $DEFAULT_ERROR[]="ERROR: Could not delete file. Reason: $delFile";
        	        }
        	    }
        		$e = $element->delete();
        		if(is_string($e)){
        			$DEFAULT_ERROR[] = "ERROR: Problem deleting...<br/>Reason: $e";
        			break;
        		}
        		$log = $element->addLogEntry(
        				$user->getID(),
        				"Element#$id deleted"
        		);
        		if(is_string($log)){
        			$DEFAULT_ERROR[] = "ERROR: Problem adding logs...<br/>Reason: $log";
        		}
        		$body .= "Element#$id deleted successfully!";
        	}
        break;
        case 'log':
        	$DEFAULT_TITLE .="\Log";
        	$report = new tldReportColumnar(
        			$element->getLog(),
        			array(
        					"xItems"=>array(
        							"id"                =>"ID#",
        							"date"              =>"Date",
        							"poster_fullname"   =>"Poster",
        							"comment"           =>"Comment"
        					)
        			)
        	);
        	$body.=$report->fetch();
        break;
        default:
        	$body .= getGeneralTab();
        break;
        }
    break;
    }
break;
default:
    $body.= include('sac.view.tree.tpl.php');
break;
}

function getGeneralTab(){
	global $id;
	$element = new tldSalesAppConfiguratorElement($id);
	$header = $element->getHeader();
	// General tab
	$report = new tldAssocTable(
			$header,
			array(
					"id"=>"Element#",
					"value"=>"Value"
			),
			array("title"=>"Sales App Element#$id")
	);
	$cells[] = $report->fetch();
	// Option tab
	$options =  $element->getOptions();
	$FIELD_DESIGNATION = array(
			'type'=>'Type',
			'poster_id'=>'Poster',
			'value'=>'Value',
			'status'=>'Status',
			'encryption'=>'Encryption',
			'location'=>'Location'
	);
	foreach($options as &$option){
		$option['key_cleaned'] = $FIELD_DESIGNATION[$option['keytag']];
	}
	$report = new tldReportColumnar(
			$options,
			array(
				"xItems"=>array(
					"id"				=>"Option#",
					"key_cleaned"		=>"Key",
					"value"				=>"Value"
				),
				array("title"=>"Options")
			)
	);
	$cells[] = $report->fetch();
	// Forwarder if applicable
	$report = new tldHTMLTable(
			$cells,
			array(
					"cols"=>2,
					"attribs"=>array(
							"table"=>" width='100%'",
							"tr"=>" bgcolor='#FFFFFF'"
					)
			)
	);
	$body.= $report->fetch();
	$body.= include('sac.view.tree.currentlevel.tpl.php');
	return $body;
}

function stringCleaner($string){
	$unwanted_array = array(
			'�'=>'S', '�'=>'s', '�'=>'Z', '�'=>'z', '�'=>'A', '�'=>'A', '�'=>'A', '�'=>'A', '�'=>'A', '�'=>'A', '�'=>'A', '�'=>'C', '�'=>'E', '�'=>'E',
			'�'=>'E', '�'=>'E', '�'=>'I', '�'=>'I', '�'=>'I', '�'=>'I', '�'=>'N', '�'=>'O', '�'=>'O', '�'=>'O', '�'=>'O', '�'=>'O', '�'=>'O', '�'=>'U',
			'�'=>'U', '�'=>'U', '�'=>'U', '�'=>'Y', '�'=>'B', '�'=>'Ss', '�'=>'a', '�'=>'a', '�'=>'a', '�'=>'a', '�'=>'a', '�'=>'a', '�'=>'a', '�'=>'c',
			'�'=>'e', '�'=>'e', '�'=>'e', '�'=>'e', '�'=>'i', '�'=>'i', '�'=>'i', '�'=>'i', '�'=>'o', '�'=>'n', '�'=>'o', '�'=>'o', '�'=>'o', '�'=>'o',
			'�'=>'o', '�'=>'o', '�'=>'u', '�'=>'u', '�'=>'u', '�'=>'y', '�'=>'y', '�'=>'b', '�'=>'y'
	);
	$string = strtr( $string, $unwanted_array );
	return $string;
}

function stringChecker($string){
    if (strpbrk($string, '\\/:*?"<>|'))
        return true;
    return false;
}
