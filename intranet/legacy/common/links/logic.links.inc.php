<?php
include_once('calendar.inc.php');

$DEFAULT_TITLE .= "\Links";
$DEFAULT_MENU .=<<<EOF
    <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="$php_self?m[0]=links">Home</a>
EOF;

if($user->isInGroup("superuser")){
	$DEFAULT_MENU .=<<<EOF
	&nbsp;|&nbsp;<a href="links/links_admin.php">Admin</a>
EOF;
}

switch($m[1]){
case "form":
	switch($m[2]){
	case "newLink":
		$DEFAULT_MENU="";
		$DEFAULT_TITLE .= "\New Link";
		//check module
        $module = strtoupper($module);
		$modules = tldTask::getModuleList();
		if(!in_array($module, $modules)){
			$DEFAULT_ERROR[] = "ERROR: Module '$module' is not valid";
			break;
		}
        if($module == 'IINV'){
            $modules =[
                1=>"SEQ",
                2=>"TASK"
            ];
        }
		//check parent_id
		if(empty($parent_id)){
			$DEFAULT_ERROR[] = "ERROR: parent_id not set";
			break;
		}
		$url = tldModLink::getURL($module, $parent_id);
		$body .=<<<EOF
		<a href="$url">Click here to go back to $module #$parent_id</a>
EOF;
		$form = new HTML_QuickForm('frmNew', 'post');
		$form->addElement(	'header', 'title', "Submit new $module Link");
		$form->addElement(	'hidden', 'm[0]', 'links');
		$form->addElement(	'hidden', 'm[1]', 'form');
		$form->addElement(	'hidden', 'm[2]', 'newLink');
		$form->addElement(	'hidden', 'module', $module);
		$form->addElement(	'hidden', 'parent_id', $parent_id);
		$form->addElement(	'select', 'type', 'Ref Type', $modules);
		$form->addElement(	'text', 'item', 'Ref#');
        if($module != 'IINV') {
            $form->addElement('static', null, null, "Please note that to link an ER, the ID must be used, not the SN.");
        }
		$form->addElement(	'submit', 'btnSubmit', 'Submit');
		$form->addRule("type","This is a required field.","required");
		$form->addRule("item","This is a required field.","required");

		if(!$form->validate()){
		    $body .= $form->toHTML();
		    break;
		}

		$vals = tldUtils::cleanupFormInput($form->exportValues());
		$error = tldModLink::insert($vals['module'], $vals['parent_id'], $modules[$vals['type']], $vals['item']);
		if(is_string($error)){
			$DEFAULT_ERROR[] = "ERROR: There was an error adding the new link. Reason: $error";
		}
        $body.= "<p>{$modules[$vals['type']]}#{$vals['item']} linked successfully!</p>";
        $body.= "<p><a href=\"/en/private/common/index.php?m[0]=links&m[1]=form&m[2]=newLink&module=$module&parent_id=$parent_id\">Add a new link to $module #$parent_id</a></p>";
	break;
	}
break;
case 'view':
	if(empty($id) || !is_numeric($id)){
		$DEFAULT_ERROR[] = "ERROR: Empty or invalid id";
		$body = "<a href=\"javascript:history.back()\">Click here to go back.</a>";
		break;
	}
	// Get the link and data
	$link = new tldModLink($id);
	$header = $link->itsHeader;
	$module = $link->getModule();
	$parent_id = $link->getParentID();

	if($reversed){
		$url = $link->getReversedURL();
		$urlReversed = tldModLink::getURL($link->getType(), $link->getItem());
	}else{
		$url = tldModLink::getURL($link->getType(), $link->getItem());
		$urlReversed = $link->getReversedURL();
	}

	// ACL for deleting links
	$aclDefault = tldGroup::getManagerGroups();
    $aclByModule = [
        'CSR' => ['role_CSM'],
        'PDC' => ['gg_SUPPORT'],
        'WC' => ['gg_SUPPORT'],
        'ER' => ['gg_SUPPORT'],
        'SOL' => ['gg_SALES'],
        'SPR' => ['gg_SALES', 'gg_SERVICE'],
        'SOR' => ['gg_SALES'],
        'TOC' => ['role_CSM', 'gg_SALES', 'gg_SERVICE'],
        'EAP' => ['eap', 'gg_mod_eap_admin', 'gg_ENG'],
        'MEAP' => ['eap', 'gg_mod_eap_admin', 'gg_ENG'],
        'SCAR' => ['role_QAM', 'role_QE'],
        'NCR' => ['role_QAM', 'role_QA'],
        'FAQ' => ['role_QAM', 'role_QE', 'ROLE_BYR', 'role_EM', 'ROLE_QA', 'role_MLM', 'role_ENG', 'ROLE_FAQ'],
    ];
	$allowed = $aclDefault;
	if(in_array($module,array_keys($aclByModule))){
		foreach($aclByModule[$module] as $group) $allowed[]=$group;
	}

	$body .="<a href=\"$urlReversed\">Click here to go back</a>";

	switch($m[2]){
    case 'delconf':
  		if($user->isInGroup($allowed)){
			$e = tldModLink::delete($id);
			if(is_string($e)){
    			$DEFAULT_ERROR[] = "ERROR: There was a problem deleting this link, returned error was $e..";
            }else{
                $DEFAULT_ERROR[] = "Link has now been deleted...";
    			tldUtils::log_event("Link#$id for $module#$parent_id deleted by ".$user->getFullname());
                $body =<<<EOF
				<br><br><br><a href="$urlReversed">Click here to go back.</a>
EOF;
            }
		}
		else $DEFAULT_ERROR[] = "ERROR: You do not have permissions to delete this type of link..";
    break;
    case 'del':
        $body =<<<EOF
        <a href="$urlReversed">Click here to cancel and go back.</a><br><br>
EOF;
        if($user->isInGroup($allowed)){
            $DEFAULT_ERROR[] = "WARNING: You are about to delete link#$id";
            $body .=<<<EOF
        <a href="$php_self?m[0]=links&m[1]=view&m[2]=delconf&reversed=$reversed&id=$id">Click here to confirm delete.</a>
EOF;
		}else{
			$DEFAULT_ERROR[] = "ERROR: You do not have permissions to delete this type of file..";
		}
    break;
	case 'redirect':
		if(empty($url)){
			$DEFAULT_ERROR[] = "ERROR: No link available.";
			$url = $_SERVER["HTTP_REFERER"];
        	$body =<<<EOF
            <a href="javascript:history.back()">Click here to go back.</a>
EOF;
			break;
        }
        $queryString = false === strpos($url, '?') ? "?erp=$erp" : "&erp=$erp";
        $body =<<<EOF
        <meta http-equiv="refresh" content="0;URL=$url$queryString">
        <a href="$url$queryString">If the screen does not refresh automatically, click here.</a>
EOF;
	break;
	default:
		$url = "$php_self?m[0]=links&m[1]=view&m[2]=redirect&reversed=$reversed&erp=$erp&id=$id";
		if($user->isInGroup($allowed)){
			$body .=<<<EOF
    		<h3>Link ID#$id</h3>
    		<br/>
    		<ul>
    			<li><a href="$url">View Link</a></li>
    		</ul>
    		<br/>
    		<ul>
    			<li><a href="$php_self?m[0]=links&m[1]=view&m[2]=del&reversed=$reversed&id=$id">Delete Link</a></li>
    		</ul>
EOF;
		}else{
			$body .=<<<EOF
            <meta http-equiv="refresh" content="0;URL=$url">
            <a href="$url">If the screen does not refresh automatically, click here.</a>
EOF;
		}
	break;
	}
break;
default:

break;
}

?>
