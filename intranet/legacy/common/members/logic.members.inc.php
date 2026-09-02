<?php
include_once('calendar.inc.php');
include_once('quality.inc.php');
include_once('product_support.inc.php');
include_once('mis.inc.php');

$DEFAULT_TITLE .= "\Members";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=members">Home</a>
EOF;

// Secure common vars used here
$parent_id = TldDatabase::escape($_REQUEST['parent_id']);
$module = TldDatabase::escape($_REQUEST['module']);

// Global ACL checks ---->

switch($module){
case 'GWF':
    $gwf = new tldGWF($parent_id);
    if($gwf->isEmpty()){
        $DEFAULT_ERROR[] = "ERROR: Could not create gwf object";
        return;
    }

    //if gwf is private then only allow the assignor to add maintain members
    if($gwf->isPrivate() && !$gwf->isMember($user->getID()) && !$user->isInGroup("superuser")){
        $DEFAULT_ERROR[] = "ERROR: Only members can access this private GWF";
        return;
    }
break;
case 'CPA':
    $cpa = new tldCPA($parent_id);
    if($cpa->isEmpty()){
        $DEFAULT_ERROR[] = "ERROR: Could not create CPA object";
        return;
    }
    // Allow ONLY CPA initiator and ADMIN to maintain members
    if($cpa->getInitiatorID()!=$user->getID() && !$user->isInGroup(array("gg_ADMIN","role_CMO","role_QAM"))){
        $DEFAULT_ERROR[] = "ERROR: Only initiator or admin can manage followers";
        return;
    }
break;
case 'TTS':
    $tts = new tldTTS($parent_id);
    if($tts->isEmpty()){
        $DEFAULT_ERROR[] = "ERROR: Could not create $module object";
        return;
    }
    // Allow only MIS and Owner handle members
    if(!$user->isInGroup(["gg_MIS"]) && $tts->getOwner()!=$user->getID()){
        $DEFAULT_ERROR[] = "ERROR: Only MIS and Project owner can manage members";
        return;
    }
break;
}

// Members logic ---->

switch($m[1]){
case "new":
    $DEFAULT_TITLE .= "\New member";
	$DEFAULT_MENU="";

	// Check module
    $module = strtoupper($module);
    if(!_isModuleValid($module)){
        $DEFAULT_ERROR[] = "ERROR: Module '$module' is not valid";
		break;
    }
	// Check parent_id
	if(empty($parent_id) && !is_numeric($parent_id)){
		$DEFAULT_ERROR[] = "ERROR: parameters missing";
		break;
	}
    // Display back link
	$body .=_getBackModuleLink($module, $parent_id);

	// People List allowed following user profile and module
	$peopleList = array();
	switch($module){
    case 'PDC':
        $pdc = new tldPDC($parent_id);
        if($pdc->isEmpty()){
    		$DEFAULT_ERROR[] = "ERROR: Could not found PDC#$parent_id";
    		return;
    	}
    	if($user->isInGroupLevel("role_PSA",$pdc->getFactoryERP()) || $user->isInGroupLevel("role_PSE",$pdc->getFactoryERP()) || $user->getID()==$pdc->getInitiatorID() || $user->isInGroup(['role_CSD', 'role_GTD', 'role_CMO', 'superuser', 'role_CSM', 'role_COO', 'role_PSM', 'role_RME'])){
    	    $peopleList = tldDirectory::getUserlist("smartyOptions");
    	}else{
    	    $peopleList[$user->getID()]=$user->getFullname();
    	}
    break;
    case 'SCAR':
	    $scar = new tldSCAR($parent_id);
        if($scar->isEmpty()){
    		$DEFAULT_ERROR[] = "ERROR: Could not found SCAR#$parent_id";
    		return;
    	}
    	if($user->isInGroupLevel("role_QAM",$scar->getFactoryERP()) || $user->isInGroupLevel("role_QE",$scar->getFactoryERP()) || $user->getID()==$scar->getPosterID() || $user->isInGroup("superuser")){
    	    $peopleList = tldDirectory::getUserlist("smartyOptions");
    	}else{
    	    $peopleList[$user->getID()]=$user->getFullname();
    	}
    break;
    default:
        $peopleList = tldDirectory::getUserlist("smartyOptions");
    break;
	}

    // Form
	$form = new HTML_QuickForm('frmNew', 'post');
	$form->addElement(	'header', 'title', "Submit new $module Member");
	$form->addElement(	'hidden', 'm[0]', 'members');
	$form->addElement(	'hidden', 'm[1]', 'new');
	$form->addElement(	'hidden', 'module', $module);
	$form->addElement(	'hidden', 'parent_id', $parent_id);
    $ams =& $form->addElement('advmultiselect', 'members', null,
        $peopleList,
        array('size' => 10, 'class' => 'pool', 'style' => 'width:500px;')
    );
    $ams->setLabel(array('Members...', 'Addressbook', 'CC'));
    $ams->setButtonAttributes('add',    array('value' => '-->>', 'class' => 'inputCommand'));
    $ams->setButtonAttributes('remove', array('value' => '<<--', 'class' => 'inputCommand'));

    if ('GWF' === $module) {
        $amsFunctions =& $form->addElement('advmultiselect', 'functions', null,
            tldFunction::getList('smartyOptions'),
            array('size' => 10, 'class' => 'pool', 'style' => 'width:320px;')
        );
        $amsFunctions->setLabel(array('By TLD function...', 'Functions', 'CC'));
        $amsFunctions->setButtonAttributes('add',    array('value' => '-->>', 'class' => 'inputCommand'));
        $amsFunctions->setButtonAttributes('remove', array('value' => '<<--', 'class' => 'inputCommand'));
    }
    $form->addElement(	'submit', 'btnSubmit', 'Submit');


	if(!$form->validate()){
	    $body = $form->toHTML();
	    break;
	}

	// Clean data
    $vars = tldUtils::cleanupFormInput($form->exportValues());
    $members = tldUtils::cleanupFormInput($vars['members']);
    $functionMembers = [];
    foreach ($vars['functions'] as $code) {
        $functionMembers = array_merge($functionMembers, tldFunction::getUserlist($code, 'smartyOptions', 'id'));
    }

    $members = array_unique(array_merge($members, $functionMembers));
    // Check there is member to add
    if(!count($members)){
        $DEFAULT_ERROR[] = "ERROR: No members to add selected";
        $body = $form->toHTML();
		break;
    }
    // Add members
    foreach($members as $uid){
        // Check this member is allowed to be added
        if(!array_key_exists($uid,$peopleList)){
            $DEFAULT_ERROR[] = "ERROR: You are not allowed to add {$peopleList[$uid]} as a member";
            continue;
        }
        $e = tldModMember::insert($vars['module'], $vars['parent_id'], $uid);
        if(is_string($e)){
            $DEFAULT_ERROR[] = "ERROR: could not add {$peopleList[$uid]} as a member";
            continue;
        }
        $DEFAULT_ERROR[] = "Successfully added {$peopleList[$uid]} as a member";
    }
break;
case 'delete':
    // Check module & parent_id
    $module = strtoupper($module);
    if(!_isModuleValid($module)){
        $DEFAULT_ERROR[] = "ERROR: Module '$module' is not valid";
		break;
    }
	if(empty($parent_id) && !is_numeric($parent_id)){
		$DEFAULT_ERROR[] = "ERROR: parameters missing";
		break;
	}
    // Display back link
	$body .=_getBackModuleLink($module, $parent_id);

    // Get member for the module record concerned
    $members = tldModMember::byParent($parent_id, $module);
    $mbrs = array_column($members, 'fullname', 'id');

    // Member List allowed following user profile and module
	$peopleList = array();
	switch($module){
    case 'PDC':
        $pdc = new tldPDC($parent_id);
        if($pdc->isEmpty()){
    		$DEFAULT_ERROR[] = "ERROR: Could not found PDC#$parent_id";
    		return;
    	}
    	if( $user->isInGroupLevel("role_PSA",$pdc->getFactoryERP()) || $user->isInGroupLevel("role_PSE",$pdc->getFactoryERP()) || $user->isInGroupLevel("role_PSM",$pdc->getFactoryERP()) || $user->getID()==$pdc->getInitiatorID() || $user->isInGroup(array("role_CSD","role_GTD","role_CMO","superuser"))){
    	    $peopleList = tldDirectory::getUserlist("smartyOptions");
    	}else{
    	    $peopleList[$user->getID()]=$user->getFullname();
    	}
    break;
    case 'SCAR':
	    $scar = new tldSCAR($parent_id);
        if($scar->isEmpty()){
    		$DEFAULT_ERROR[] = "ERROR: Could not found SCAR#$parent_id";
    		return;
    	}
    	if($user->isInGroupLevel("role_QAM",$scar->getFactoryERP()) || $user->isInGroupLevel("role_QE",$scar->getFactoryERP()) || $user->getID()==$scar->getPosterID() || $user->isInGroup("superuser")){
    	    $peopleList = tldDirectory::getUserlist("smartyOptions");
    	}else{
    	    $peopleList[$user->getID()]=$user->getFullname();
    	}
    break;
    default:
        $peopleList = tldDirectory::getUserlist("smartyOptions");
    break;
	}
	// xref members with allowed
    $mbrs = array_intersect_key($mbrs,$peopleList);

	// Form
    $form = new HTML_QuickForm('frmMemberRemove', 'post');
    $form->addElement(  'header', 'title', "Remove members for $module#$parent_id");
    $form->addElement(  'hidden', 'm[0]', 'members');
    $form->addElement(  'hidden', 'm[1]', 'delete');
    $form->addElement(  'hidden', 'module', $module);
    $form->addElement(  'hidden', 'parent_id', $parent_id);
    $ams =& $form->addElement('advmultiselect', 'members', null,
        $mbrs, array('size'=>10, 'class'=>'pool', 'style'=>'width:500px;')
    );
    $ams->setLabel(array('Members...', 'Addressbook', 'CC'));
    $ams->setButtonAttributes('add', array('value'=>'-->>', 'class'=>'inputCommand'));
    $ams->setButtonAttributes('remove', array('value'=>'<<--', 'class'=>'inputCommand'));
    $form->addElement(  'submit', 'btnSubmit', 'Submit');

    if(!$form->validate()){
	    $body = $form->toHTML();
	    break;
	}

	// Clean data
    $vars = tldUtils::cleanupFormInput($form->exportValues());
    if(!count($vars['members'])){
        $DEFAULT_ERROR[] = "ERROR: Could not remove members. Reason: no member selected";
        break;
    }
    foreach($vars['members'] as $uid){
        // Check if allowed
        if(!array_key_exists($uid,$mbrs)){
            $DEFAULT_ERROR[] = "ERROR: You are not allowed to add {$mbrs[$uid]} as a member";
            continue;
        }
        // Delete member
        $e = tldModMember::delete($module, $parent_id, $uid);
        if(is_string($e)){
            $DEFAULT_ERROR[] = "ERROR: could not delete {$mbrs[$uid]} as a member";
            continue;
        }
        $body.= "<br>Successfully deleted {$mbrs[$uid]} as a member";
    }
break;
}


function _isModuleValid($module){
    return in_array($module,array_keys(tldUtils::getModLinks()));
}

function _getBackModuleLink($module, $parent_id){
    $url = tldModLink::getURL($module, $parent_id);
    return "<p><a href=\"$url\">Click here to go back to $module #$parent_id</a></p>";
}

?>
