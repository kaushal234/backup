<?php
include_once("erp.inc.php");
include_once("sales_service.inc.php");
include_once("$PATH/user/user.webcalendar.php");

$DEFAULT_TITLE .= "\User";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=user">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=user&m[1]=form&m[2]=byNumber">By number</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=user&m[1]=form&m[2]=search">Search</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=user&m[1]=reports">Reports</a>
EOF;

if(!$user->isInGroup(array("superuser"))){
    $DEFAULT_ERROR[]="ERROR: You do not have permissions";
    return;
}

switch($m[1]){
case 'form':
    switch($m[2]){
    case 'byNumber':
        $form = new HTML_QuickForm('frmByNum', 'post');
        $form->addElement(  'hidden', 'm[0]', 'user');
        $form->addElement(  'hidden', 'm[1]', 'view');
        $form->addElement(  'header', 'header', "User by ID");
        $form->addElement(  'text', 'id', 'User#');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $body = $form->toHTML();
    break;
    }
break;
case 'reports':
	include('reports/reports.logic.inc.php');
break;
case 'view':
    if(empty($id) || !is_numeric($id)){
        $DEFAULT_ERROR[]="ERROR: Data sent empty or invalid";
        break;
    }
    $userObj = new tldUser($id);
    if(!$userObj->isValid()){
        $DEFAULT_ERROR[]="ERROR: User#$id not valid";
        break;
    }
    // Few info
    if($userObj->isDisabled()){
        $DEFAULT_ERROR[]="WARNING: User is DISABLED";
    }
    if(!$userObj->isInTLDDomain()){
        $DEFAULT_ERROR[]="WARNING: User is not in TLD DOMAIN";
    }
    $header = $userObj->itsDetails;
    
    $DEFAULT_TITLE .= "\User#$id";
    $DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=user&m[1]=view&id=$id">General</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=user&m[1]=view&m[2]=webcal&id=$id">Webcalendar</a>
EOF;

    switch($m[2]){
    case 'webcal':
        $DEFAULT_TITLE .= "\Webcalendar";
        $DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=user&m[1]=view&m[2]=webcal&id=$id">Home</a>
EOF;
        // Check if already in webcal
        $emailArray = explode("@",$header['email']);
        $calUser = new tldCalUser($emailArray[0]);
        if($calUser->isEmpty()){
            $isInWebcal = FALSE;
        }else{
            $isInWebcal = TRUE;
        }
        
        // Get menu
        if($isInWebcal){
            $DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=user&m[1]=view&m[2]=webcal&m[3]=calAccess&id=$id">Calendar Access</a>
EOF;
        }else{
            $DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=user&m[1]=view&m[2]=webcal&m[3]=add&id=$id">Transfer to Wecalendar</a>
EOF;
        }
        
        switch($m[3]){
        case 'add':
            if($isInWebcal){
                $DEFAULT_ERROR[]="ERROR: Can not add to webcalendar, user already exist";
                break;
            }
            $form = new HTML_QuickForm('frmByNum', 'post');
            $form->addElement(  'hidden', 'm[0]', 'user');
            $form->addElement(  'hidden', 'm[1]', 'view');
            $form->addElement(  'hidden', 'm[2]', 'webcal');
            $form->addElement(  'hidden', 'm[3]', 'add');
            $form->addElement(  'hidden', 'id', $id);
            $form->addElement(  'header', 'title', "Create webcalendar account for ".$userObj->getFullname());
            $form->addElement(  'select', 'isEnable', 'Enable?', array("Y"=>"Y","N"=>"N"));
            $form->addElement(  'select', 'isAdmin', 'Admin?', array("N"=>"N","Y"=>"Y"));
            $form->addElement('submit', 'btnSubmit', 'I Confirm and create account...');
            
            if(!$form->validate()){
                $body.= $form->toHTML();
                break;
            }
            
            $vars = tldUtils::cleanupFormInput($form->exportValues());
            // Prepare data
            $emailArray = preg_split("/@/",$header['email']);
            $a = array(
                "cal_login"=>$emailArray[0],
            	"cal_passwd"=>md5($header['password']),
                "cal_lastname"=>$header['lastname'],
                "cal_firstname"=>$header['firstname'],
                "cal_is_admin"=>$vars['isAdmin'],
                "cal_email"=>$header['email'],
                "cal_enabled"=>$vars['isEnable'],
                "cal_telephone"=>$header['direct_phone'],
                "cal_address"=>$header['address'],
                "cal_title"=>$header['title'],
            );
            $a = tldUtils::cleanupFormInput($a);
            $e = tldCalUser::insert($a);
            if(is_string($e)){
                $DEFAULT_ERROR[]="ERROR: Can not create account. Reason: $e";
                break;
            }
            $body.="Account successfully created in webcalendar with information below:<br>";
            $body.="Login: {$a['cal_login']}<br>";
            $body.="Pass: same as intranet password<br>";
        break;
        case 'calAccess':
            if(!$isInWebcal){
                $DEFAULT_ERROR[]="ERROR: Can not add permissions, user do not exist";
                break;
            }
            // Look for actual rights
            $webcalUserPermissions = $calUser->getPermissions();
            $permissionDefault = array();
            foreach($webcalUserPermissions as $val){
                $permissionDefault[$val['cal_other_user']]=$val;
            }
            // Get list of users
            $webcalUserList = tldCalUser::getUserListEnable();
            // Form
            $form = new HTML_QuickForm('frmByNum', 'post');
            $form->addElement(  'hidden', 'm[0]', 'user');
            $form->addElement(  'hidden', 'm[1]', 'view');
            $form->addElement(  'hidden', 'm[2]', 'webcal');
            $form->addElement(  'hidden', 'm[3]', 'calAccess');
            $form->addElement(  'hidden', 'id', $id);
            $form->addElement(  'header', 'header', "Assign access permissions for ".$webcalHeader['cal_login']);
            foreach($webcalUserList as $k=>$webcalUser){
                if(in_array($webcalUser['cal_login'], array("admin","itcalendar",$webcalHeader['cal_login']))) continue;
                $form->addElement(  'header', 'header'.$k, "Calendar of ".$webcalUser['cal_login']);
                $field = & $form->addElement(  'select', "acl[{$webcalUser['cal_login']}][cal_can_invite]", 'Can invite?', array("N"=>"N","Y"=>"Y"));
                $field->setValue($permissionDefault[$webcalUser['cal_login']]['cal_can_invite']);
                $field = & $form->addElement(  'select', "acl[{$webcalUser['cal_login']}][cal_can_email]", 'Can email?', array("N"=>"N","Y"=>"Y"));
                $field->setValue($permissionDefault[$webcalUser['cal_login']]['cal_can_email']);
                $field = & $form->addElement(  'select', "acl[{$webcalUser['cal_login']}][cal_see_time_only]", 'Can See Time Only?', array("N"=>"N","Y"=>"Y"));
                $field->setValue($permissionDefault[$webcalUser['cal_login']]['cal_see_time_only']);
            }
            $form->addElement('button', 'btn', 'Set all to Y',
                array("onClick"=>'$(".form_field_acl").val("Y");')
            );
            $form->addElement('reset', 'btnReset', 'Reset form');
            $form->addElement('submit', 'btnSubmit', 'Submit');
            
            if(!$form->validate()){
                $body .= $form->toHTML();
                break;
            }
            
            $vars = tldUtils::cleanupFormInput($form->exportValues());
            // Delete all permissions
            $e = $calUser->deletePermissions();
            
            // Add new ones
            foreach($vars['acl'] as $login=>$vals){
                $vals = tldUtils::cleanupFormInput($vals);
                $a = array(
                    "cal_login"=>$calUser->getID(),
                    "cal_other_user"=>$login,
                    "cal_can_view"=>63,
                    "cal_can_edit"=>63,
            		"cal_can_approve"=>63,
            		"cal_can_invite"=>$vals['cal_can_invite'],
            		"cal_can_email"=>$vals['cal_can_email'],
            		"cal_see_time_only"=>$vals['cal_see_time_only']
                );
                $e = $calUser->addPermissions($a);
                if(is_string($e)){
                    $DEFAULT_ERROR[]="ERROR: Can not add permissions with $login -> $e";
                    continue;
                }
                $body.="<br/>Permissions added for $login";
            }
            $body.= "<p>Permission addition process completed...</p>";
        break;
        default:
            if($isInWebcal){
                $body.=$calUser->getPrintVersion();
            }else{
                $DEFAULT_ERROR[]="WARNING: User not found in webcalendar";
            }
        break;
        }
    break;
    default:
        $body = getView();
    break;
    }
break;
}


function getListing($rows, $title){
    global $php_self;
    $report = new tldReportColumnar(
        $rows,
        array(
            "xItems"=>array(
                "id"=>"User#",
                "lastname"=>"Lastname",
                "firstname"=>"Firstname",
                "reports_to_fullname"=>"Reports to",
                "division"=>"Division",
    			"location"=>"Location",
                "department"=>"Department",
                "title"=>"Title",
                "email"=>"Email"
            ),
            "links"=>array("id"=>"$php_self?m[0]=user&m[1]=view&id="),
            "title"=>$title
        )
    );
    return $report->fetch();
}

function getView(){
    global $userObj;
    $report = new tldAssocTable(
        $userObj->itsDetails,
        array(
            "id"=>"User#",
            "lastname"=>"Lastname",
            "firstname"=>"Firstname",
            "reports_to_fullname"=>"Reports to",
            "division"=>"Division",
            "department"=>"Department",
            "title"=>"Title",
            "bu"=>"BU",
            "phone"=>"Phone",
            "direct_phone"=>"Direct phone",
            "fax"=>"Fax",
            "mobile"=>"Mobile",
            "home_phone"=>"Home phone",
            "email"=>"Email & login",
            "baan_id"=>"Baan user",
            "password"=>"Password",
            "address"=>"Address"
        ),
        array("title"=>"General")
    );
    $general = $report->fetch();
    // Add groups
    $report = new tldReportColumnar(
        $userObj->getGroups(),
        array(
            "xItems"=>array(
                "group_name"=>"Group",
                "email"=>"Email",
                "level"=>"BU"
            ),
            "title"=>"Groups & permissions"
        )
    );
    return $general.$report->fetch();
}
?>