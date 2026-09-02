<?php
set_time_limit(0);

include('common.inc.php');
include_once("calendar.inc.php");
include_once("forms_and_reports.inc.php");
require_once("HTML/QuickForm.php");
require_once('HTML/QuickForm/advmultiselect.php');

session_start();
if(!isset($_SESSION['sess'])) $_SESSION['sess'] = null;
$sess =& $_SESSION['sess'];

$php_self = $_SERVER['PHP_SELF'];
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);
$DEFAULT_ERROR = array();
$PATH = dirname(__FILE__);

$DEFAULT_TITLE = "MIS TOOL";
$DEFAULT_MENU = <<<EOF
<a href="$php_self" title="MIS Tools Homepage">Home</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=user">User</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=password">Password generator</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=translation_request">Translation Request</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=acct_confirm_email">Email Account Confirmation</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=notification">Notification tool</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=synonyms" title="Manage BAAN DB Synonyms for shared tables">Manage BAAN DB synonyms</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=baanLicenses">Baan Licenses</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=datasheet_upload">Datasheet Upload</a>&nbsp;
EOF;

switch($m[0]){
case 'notification':
    $DEFAULT_ERROR[] =  "WARNING: Carefull with this feature, will send an email to all intranet account";
    
    $form = new HTML_QuickForm('frmNewTask', 'post');
    $form->addElement(	'hidden', 'm[0]', 'notification');
    $form->addElement(	'header', 'title', "Submit notification to all intranet account");
    $form->addElement(	'text', 'subject', 'Subject');
    $form->addElement(	'textarea', 'message', 'Message',
        array("wrap"=>"VIRTUAL", "cols"=>"60", "rows"=>"8")
    );
    $form->addElement(	'submit', 'btnSubmit', 'Submit');
    
    if(!$form->validate()){
        $body .= $form->toHTML();
        break;
    }
    
    $vars = $form->exportValues();
    
    $peopleList = tldUser::byConstraints(['hidden'=>0, 'disabled'=>'N']);
    // send one by one
    foreach($peopleList as $people){
        if(empty($people['email'])) continue;
        $e = tldUtils::emailAttachment(
            $people['email'],
            "noreply@tld-gse.com",
            $vars['subject'],
            $vars['message']
        );
    }
    $count = count($peopleList);
    $body = "Successfully sent $count emails";
break;
case 'datasheet_upload':
	include('datasheet_upload.php');
break;
case 'user':
    include("$PATH/{$m[0]}/logic.{$m[0]}.inc.php");
break;
case 'password':
    include('password.php');
break;
case 'baanLicenses':
	include("$PATH/{$m[0]}/logic.{$m[0]}.inc.php");
break;

case 'synonyms':
    include('baan_sql_synonym_generator.php');
break;
case 'acct_confirm_email':
    $DEFAULT_TITLE .= "/Email Account Confirmation";
    switch($m[1]){
    case 'intranet':
        $DEFAULT_TITLE .= "/Intranet";
        if(empty($id) || !is_numeric($id)){
            $DEFAULT_ERROR[]="ERROR: User ID not sent or invalid";
            break 2;
        }
        $acct_user = new tldUser($id);
        if(!$acct_user->isValid()){
            $DEFAULT_ERROR[]="ERROR: User#$id not valid or not found";
            break 2;
        }
        $to = $acct_user->getEmail();
        $cc = $user->getEmail();
        $subject = "Your TLD Intranet Account";
        $msg = <<<EOF
<p>Dear User,</p>
<p>We are pleased to announce that your TLD Intranet account have been created with the following credential information:</p>
<p>Login: {$acct_user->itsDetails['email']}<br/>
Password: {$acct_user->itsDetails['password']}</p>
<p>To connect the TLD Intranet website, please connect to <a href="http://www.tld-gse.com/en/private">http://www.tld-gse.com/en/private</a></p>
<p>Best Regards,<br/>
Webmaster TLD</p>
EOF;
        $e = tldUtils::emailAttachment(
            $to,
            "noreply@tld-gse.com",
            $subject,
            $msg,
            NULL,
            $cc
        );
        $body = "Email successfully sent to $to with credentials";
    break;
    default:
        $body = "Welcome to the Email Account Confirmation tool";
    break;
    }
break;
case 'translation_request':
    $DEFAULT_TITLE.= "/New Translation Request";
    // List of translation owner
    $OWNER = array(
        "en"=>762,   // LE PAPE	Renaud
        "fr"=>912,   // LAMBERT Julien
        "de"=>423,   // PORTELE	CHRISTOPH
        "pt"=>585,   // PINTO Renato
        "es"=>1052,  // Isaac ROMERO
    	"ja"=>2720,  // IRIE ARISA
        "zh"=>322,   // HOU	Mei
        "ru"=>935    // GEVORKOVA Alla
    );
    $LANG = array(
        "en"=>"English",
        "fr"=>"French",
        "de"=>"German",
        "pt"=>"Portuguese",
        "es"=>"Spanish",
        "ja"=>"Japanese",
        "zh"=>"Chinese",
        "ru"=>"Russian"
    );
    $form = new HTML_QuickForm('frmNewTask', 'post');
    $form->addElement(	'hidden', 'm[0]', 'translation_request');
    $form->addElement(	'hidden', 'assignee', $user->getID());
    $form->addElement(	'header', 'title', "Submit new Translation task");
    $form->addElement(	'date', 'due_date', 'Due Date or use below',
        array(	"format"=>"Ymd", "minYear"=>date("Y"), "maxYear"=>date("Y")+2)
    );
    $form->addElement(	'select', 'due_date_value', 'Due date plus (overrides due date above)',
        array(0,1,2,3,4,5,6,7,8,9,10,11,12)
    );
    $form->addElement(	'select', 'due_date_unit', 'Due date unit',
        array("DAY"=>"DAYS","WEEK"=>"WEEKS","MONTH"=>"MONTHS","QUARTER"=>"QUARTERS","YEAR"=>"YEARS")
    );
    $form->addElement(	'textarea', 'text', 'Text to translate',
        array("wrap"=>"VIRTUAL", "cols"=>"60", "rows"=>"8")
    );
    $list = array();
    foreach($OWNER as $lang=>$ownerID){
        $ownerUser = new tldUser($ownerID);
        $list[$lang]=strtoupper($lang)." -> ".$ownerUser->getFullName();
    }
    $ams =& $form->addElement(
        'advmultiselect', 'lang', null,
        $list,
        array(
            'size' => 10,
            'class' => 'pool',
            'style' => 'width:200px;'
        )
    );
    $ams->setLabel(array('Select Translation language', NULL, 'Lang bellow to translate'));
    $ams->setButtonAttributes('add',    array('value'=>'-->>', 'class'=>'inputCommand'));
    $ams->setButtonAttributes('remove', array('value'=>'<<--', 'class'=>'inputCommand'));
    $form->addElement(	'file', 'filename', 'File');
    $form->addElement(	'submit', 'btnSubmit', 'Submit');
    $form->setDefaults(
        array(
            "due_date"=>array("Y"=>date("Y"),
                "m"=>date("m"),
                "d"=>date("d")
            )
        )
    );
    $form->addRule('task', 'Required','required');

    if(!$form->validate()){
        $body .= $form->toHTML();
        break;
    }

    $vars = tldUtils::cleanupFormInput($form->exportValues());
    $vals = array();
    if($vars['due_date_value']<>0){
        $vals['due_date'] = array(
        	"value"=>$vars['due_date_value'],
        	"unit"=>$vars['due_date_unit']
        );
    }
    $vals["assignor"] = $user->getId();
    // Foreach lang selected to translate, create a task to the owner
	foreach($vars["lang"] as $lang){
	    $vals["assignee"] = $OWNER[$lang];
	    $vals["task"] = <<<EOF
Please translate attached text into {$LANG[$lang]}

{$vars['text']}
EOF;
	    $error = tldTask::insert(NULL, $vals, "USER");
        if(!is_numeric($error)) {
            $DEFAULT_ERROR[] =  "ERROR: Can not create task!<br>Reason: $error";
            continue;
        }
        $task = new tldTask($error);
        // Add file if any
        $file = $form->getElement("filename");
        $comment["file_info"] = $file->getValue();
        $comment["poster"] = $user->getId();
        if(!empty($comment["file_info"])){
            $task->addComment($comment);
        }
        // Send notification
        $assignee = new tldUser($task->getAssignee());
        $assignor = new tldUser($task->getAssignor());
        $assignee_fullname = $assignee->getFullname();
        $message =<<<EOF
Task #$error has been assigned to $assignee_fullname.\n<br>
Please log in to the TLD-GSE intranet and go to the calendar module to view your open tasks.\n<br>
<a href="http://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$error">
Click here to go to Task.
</a>
EOF;
        $subject = "Tasks, New: #$error opened for ".$assignee->getFullname()." by ".$assignor->getFullname();
        $task->notifyAssignee($message, $subject, NULL);
        // Add comment for creation details
        $task->addComment(
            array(
            	"poster"=>$user->getId(),
            	"comment"=>$subject
            )
        );
        $body .=<<<EOF
<br><a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$error">Task#$error created for {$LANG[$lang]}</a>
EOF;
	}
break;
default:
    $DEFAULT_TITLE.= "/Home";
    $body = "Welcome to the TLD ADMIN TOOL";
break;
}

// Do not touch !!!! ---->

$_TITLE = $DEFAULT_TITLE;
$_MENU = $DEFAULT_MENU;
$_BODY = $body;
$_ERROR = implode("<br>", $DEFAULT_ERROR);

echo <<<EOF
<!DOCTYPE html>
<html>
  <head>
	<title>$_TITLE</title>
	<link href="/tld-gse.css" rel="stylesheet" type="text/css">
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<script type="text/javascript" src="/include/sorttable.js"></script>
	<script type="text/javascript" src="/shared/js/jquery-1.3.2.min.js"></script>
  </head>
  <body style="z-index:0;">
  <div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000; overflow:auto;"></div>
	<!-- inner table-->
	<table width="1024" border="0" align="center">
	  <tr>
		<td>
		<p><img src="/shared/icons/tld-icon.gif" align="right">
		<strong><font size="4" face="Arial, Helvetica, sans-serif">$_TITLE</font></strong><br>
		$_MENU</p>
		</td>
	  </tr>
	  <tr>
		<td>
		  <p class="alert">$_ERROR</p>
		  $_BODY
		</td>
	  </tr>
	</table>
  </body>
</html>
EOF;
?>