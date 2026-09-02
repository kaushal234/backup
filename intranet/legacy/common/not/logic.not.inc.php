<?php
$DEFAULT_TITLE .= "\NOT";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=not">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=not&m[1]=search">Search</a>
&nbsp;|&nbsp;<a href="not/not_admin.php">Admin</a>
EOF;

switch($m[1]){
case 'search':
    $DEFAULT_TITLE .= "\Search";
    if(!$user->isInGroup("superuser")){
        $DEFAULT_ERROR[] = "ERROR: You do not have permissions";
        break;
    }
    // Listing
    $moduleList = array_keys(tldUtils::getModLinks());
    $moduleList = array_combine($moduleList,$moduleList);
    $userList = tldDirectory::getUserlist("smartyOptions");
    // Get form
    $form = new HTML_QuickForm('frmNew', 'post');
    $form->addElement(  'hidden', 'm[0]', 'not');
    $form->addElement(  'hidden', 'm[1]', 'search');
    $form->addElement(  'header', 'title', "Search NOT");
    $form->addElement('select', 'module', 'Module', array(""=>"")+$moduleList);
    $form->addElement('text', 'parent_id', 'Ref#');
    $form->addElement('text', 'dt', 'Date');
    $form->addElement('select', 'uid', 'Poster', array(""=>"")+$userList);
    $form->addElement('text', 'email_from', 'From');
    $form->addElement('text', 'email_to', 'To');
    $form->addElement('text', 'email_cc', 'Cc');
    $form->addElement('text', 'email_bcc', 'Bcc');
    $form->addElement('text', 'email_subject', 'Subject');
    $form->addElement('text', 'email_body', 'Body');
    $form->addElement('submit', 'btnSubmit', 'Submit');
    
    if(!$form->validate()){
        $body .= $form->toHTML();
        break;
    }
    
    $vars = tldUtils::cleanupFormInput($form->exportValues());
    $fields = array("parent_id","module","dt","uid","email_from","email_to","email_cc","email_bcc","email_subject","email_body");
    $constraints = array();
    foreach($fields as $field){
        if(empty($vars[$field])) continue;
        switch($field){
        default:
            $constraints[$field] = $vars[$field];
        break;
        }
    }
    if(count($constraints)==0){
        $DEFAULT_ERROR[]="ERROR: Not enough constraints set...";
        $body .= $form->toHTML();
        break;
    }
    $rows = tldModNot::byConstraints($constraints);
    $report = new tldReportColumnar(
        $rows,
        array(
            "xItems"=>array(
                "id"               =>"ID#",
                "parent_id"        =>"Ref#",
                "module"           =>"Module",
                "dt"               =>"Date",
                "poster_fullname"  =>"Poster",
            	"email_from"       =>"From",
                "email_to"         =>"To",
                "email_cc"         =>"Copy",
                "email_subject"    =>"Subject"
            ),
            "title"=>"Search result",
            "links"=>array(
                "id"=>"$php_self?m[0]=not&m[1]=view&id="
            )
        )
    );
    $body.= $report->fetch();
break;
case 'view':
    if(empty($id) || !is_numeric($id)){
        $DEFAULT_ERROR[] = "ERROR: Parameters sent empty or invalid";
        break;
    }
    $not = new tldModNot($id);
    if($not->isEmpty()){
        $DEFAULT_ERROR[] = "ERROR: NOT not found";
        break;
    }
    $module = $not->getModule();
    $parent_id = $not->getParentID();
    $backLinkHtml = _getBackModuleLink($module, $parent_id);
    
    $DEFAULT_TITLE .= "\NOT#$id";
    $DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=not&m[1]=view&id=$id">Home</a>&nbsp;|&nbsp;
<a href="not/not_admin.php?mode=record_view&form_type=main_tpl&id=$id">Admin</a>
EOF;

    $report = new tldAssocTable(
        $not->itsHeader,
        array(
            "id"               =>"ID#",
            "parent_id"        =>"Ref#",
            "module"           =>"Module",
            "dt"               =>"Date",
            "poster_fullname"  =>"Poster",
        	"email_from"       =>"From",
            "email_to"         =>"To",
            "email_cc"         =>"Copy",
            "email_bcc"        =>"Hidden copy",
            "email_subject"    =>"Subject",
            "email_body"       =>"Body"
        ),
        array("title"=>"NOT#$id")
    );
    $body = $backLinkHtml.$report->fetch();
break;
default:
    $body = <<<EOF
<h3>Module NOT</h3>
<p>Welcome to common module NOT</p>
EOF;
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