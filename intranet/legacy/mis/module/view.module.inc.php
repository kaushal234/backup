<?php
if(empty($id)){
	$DEFAULT_ERROR[]=  "ERROR: no line id set...";
	return;
}
$module = new tldModule($id);
if($module->isEmpty()){
	$DEFAULT_ERROR[]=  "ERROR: No module#$id found...";
	return;
}
$header = $module->getHeader();

$DEFAULT_TITLE .= "\Module#$id";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=module&m[1]=view&id=$id">General</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=module&m[1]=view&m[2]=edit&id=$id">Edit</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=module&m[1]=view&m[2]=roles&id=$id">Roles</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=module&m[1]=view&m[2]=log&id=$id">Logs</a>
EOF;

if($user->isInGroup(array("superuser"))){
	$DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="module/module_admin.php?mode=record_view&form_type=main_tpl&id=$id">Admin</a>
EOF;
}

switch($m[2]){
case 'edit':
    $DEFAULT_TITLE .= "\Edit";
    if(!$user->isInGroup(array("gg_MIS")) && $user->getID()!=$module->getMOOID()){
        $DEFAULT_ERROR[]="ERROR: Permission denied";
        break;
    }
    // Display form
    $fields = array();
    $form = new HTML_QuickForm('frmAddRole', 'post');
    $form->addElement(  'header', 'title', 'Edit Module info');
    $form->addElement(  'hidden', 'm[0]', 'module');
    $form->addElement(  'hidden', 'm[1]', 'view');
    $form->addElement(  'hidden', 'm[2]', 'edit');
    $form->addElement(  'hidden', 'id', $id);
    if($user->isInGroup("gg_MIS")){
        //$fields = array_merge($fields, array());
        // All fields (later)
    }
    if($user->isInGroup(array("gg_MIS")) || $user->getID()==$module->getMOOID()){
        $fields = array_merge($fields, array('user_guide_id','help_page_id'));
        $form->addElement(  'text', 'user_guide_id', 'User guide DMS#');
        $form->addElement(  'text', 'help_page_id', 'Procedure DMS#');
    }
    $form->setDefaults($header);
    $form->addElement(  'submit', 'btnSubmit', 'Submit');

    if(!$form->validate()){
        $body = $form->toHTML();
        break;
    }

    $vars = tldUtils::cleanupFormInput($form->exportValues());
    $e = $module->update($vars,$fields);
    if(is_string($e)){
        $DEFAULT_ERROR[]="ERROR: Could not update. Reason: $e";
        break;
    }
    $module->addLogEntry($user->getID(),"Update");
    $body.="Module updated successfully";
break;
case 'roles':
    $DEFAULT_TITLE .="\Roles";
    $DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=module&m[1]=view&m[2]=roles&m[3]=add&id=$id">Add</a>
EOF;
    switch($m[3]){
    case 'add':
        if(!$user->isInGroup("gg_MIS")){
            $DEFAULT_ERROR[]="ERROR: You do not have permissions...";
            break;
        }
        $moduleRoleList = $module->getImpactedRoleListOptionsByRoleRole();
        $groupList = array_column(tldGroup::getGroups(), 'group_name', 'group_name');
        // Display form
        $form = new HTML_QuickForm('frmAddRole', 'post');
        $form->addElement(  'header', 'title', 'Add new Roles');
        $form->addElement(  'hidden', 'm[0]', 'module');
        $form->addElement(  'hidden', 'm[1]', 'view');
        $form->addElement(  'hidden', 'm[2]', 'roles');
        $form->addElement(  'hidden', 'm[3]', 'add');
        $form->addElement(  'hidden', 'id', $id);
        $ams =& $form->addElement(
            'advmultiselect', 'roles', null,
            array_diff($groupList,$moduleRoleList),
            array('size' => 10, 'class' => 'pool', 'style' => 'width:200px;')
        );
        $ams->setLabel(array('Select new groups', 'Roles List', 'Roles to add'));
        $ams->setButtonAttributes(
            'add', array('value' => '-->>', 'class' => 'inputCommand'));
        $ams->setButtonAttributes(
            'remove', array('value'=>'<<--', 'class'=>'inputCommand'));
        $form->addElement(  'submit', 'btnSubmit', 'Submit');
        $form->addRule('roles', 'Required', 'required');

        if(!$form->validate()){
            $body = $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        foreach($vars['roles'] as $role){
            $role = TldDatabase::escape($role);
            $e = $module->addImpactedRole($role);
            if(is_string($e)){
                $DEFAULT_ERROR[]="ERROR: Role '$role' not added, reason: $e";
            }else{
                $body.="<br/>Role '$role' added successfully.";
            }
        }
    break;
    case 'delete':
        if(!$user->isInGroup("gg_MIS")){
            $DEFAULT_ERROR[]="ERROR: You do not have permissions...";
            break;
        }
        $listid = $_GET['listid'];
        if(empty($listid) || !is_numeric($listid)){
            $DEFAULT_ERROR[]="ERROR: Parameters sent empty or invalid...";
            break;
        }
        // Check if list entry linked to this record
        $list = new tldModList($listid);
        if($list->itsHeader['parent_id']<>$module->getID()
        || $list->itsHeader['module']<>"MOD"){
            $DEFAULT_ERROR[]="ERROR: Entry role not attached to module ".$module->getModule();
            break;
        }
        $e = tldModList::delete($listid);
        if(is_string($e)){
            $DEFAULT_ERROR[]="ERROR: Role not deleted, reason: $e";
            break;
        }
        $body = "Role deleted successfully!";
    break;
    }
    // List all roles for the module
    $report = new tldReportColumnar(
        $module->getImpactedRoleList(),
        array(
            "xItems"=>array(
                "value" =>"Role",
                "id"    =>"Delete?"
            ),
            "title"=>"Module roles",
            "links"=>array(
                "id"=>array(
                    "url"=>"$php_self?m[0]=module&m[1]=view&m[2]=roles&m[3]=delete&id=$id",
                    "params"=>array("listid"=>"id"),
                    "confirmPopup"=>"Are you sure to delete this role?"
                )
            ),
            "showItemNumbers"=>TRUE
        )
    );
    $body .= $report->fetch();
break;
case 'log':
    $DEFAULT_TITLE .="\Log";
    $DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=module&m[1]=view&m[2]=log&m[3]=add&id=$id">Add</a>
EOF;
    switch($m[3]){
    case 'add':
        if(!$user->isInGroup("gg_MIS") && $user->getID()!=$module->getMOOID()){
            $DEFAULT_ERROR[]="ERROR: You do not have permissions...";
            break;
        }
        // Check if roles attached to the module
        if(!count($module->getImpactedRoleList())){
            $DEFAULT_ERROR[]="WARNING: No roles attached to this module so nobody will receive the notification...";
        }
        $form = new HTML_QuickForm('frmAddComment', 'post');
        $form->addElement(  'header', 'title', 'Add Module change log');
        $form->addElement(  'header', 'title', 'WARNING: a notification will be sent');
        $form->addElement(  'hidden', 'm[0]', 'module');
        $form->addElement(  'hidden', 'm[1]', 'view');
        $form->addElement(  'hidden', 'm[2]', 'log');
        $form->addElement(  'hidden', 'm[3]', 'add');
        $form->addElement(  'hidden', 'id', $id);
        $form->addElement(  'textarea', 'comment', 'Comment',
            array("wrap"=>"VIRTUAL", "cols"=>"50", "rows"=>"6"));
        $form->addElement(  'submit', 'btnSubmit', 'Submit');
        $form->addRule('comment', 'Required', 'required');

        if(!$form->validate()){
            $body = $form->toHTML();
            break;
        }

        $a = $form->exportValues();
        $vars = tldUtils::cleanupFormInput($a);
        // Prepare notification
        $TO = $module->getImpactedRoleEmailList();
        $CC = array("devteam@tld-america.com");
        $mooUser = new tldUser($module->getMOOID());
        $CC[]=$mooUser->getEmail();
        $misUser = new tldUser($module->getMISID());
        $CC[]=$misUser->getEmail();
        $subject = "[MIS] New change log for {$module->getFullname()} ({$module->getModule()})";
        $logChange = mb_convert_encoding($a['comment'], 'UTF-8', mb_list_encodings());
        $body = <<<EOF
<p>Dear Intranet User,</p>

<p>This automatic notification is designed to inform you that a new modification has been applied to the
{$module->getFullname()} ({$module->getModule()}) in the intranet and have been recorded with details below:

<p>$logChange</p>

<p>If you need further information regarding this change, please contact the MOO of this module.<br/>
{$mooUser->getFullname()}<br/>
{$mooUser->getEmail()}<p>
EOF;
        // Send email if role attached to module
        if(count($module->getImpactedRoleList())){
            tldUtils::emailAttachment(
                $TO,
                $mooUser->getEmail(),
                $subject,
                $body,
                NULL,
                $CC
            );
            $body = "<p>Email change log notification sent to ".implode(',',$TO)."</p>";
        }
        // Log the change and who was notified
        $e = $module->addLogEntry($user->getID(), $vars['comment']);
        if(is_string($e)){
            $DEFAULT_ERROR[]="ERROR: Log not recorded, reason: $e";
            break;
        }
        $body.= "<p>Module change log added successfully!</p>";
    break;
    default:
        $report = new tldReportColumnar(
            $module->getLog(),
            array(
                "xItems"=>array(
                    "id"                =>"ID#",
                    "date"              =>"Date",
                    "poster_fullname"   =>"Poster",
                    "comment"           =>"Comment"
                ),
                "title"=>"Module Change logs"
            )
        );
        $body .= $report->fetch();
    break;
    }
break;
default:
	$body = _getGeneralTab($header);
break;
}

?>
