<?php
$DEFAULT_TITLE .= "/Module";
$DEFAULT_MENU .=<<<EOF
	<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="$php_self?m[0]=module">Home</a>
EOF;
//	&nbsp;|&nbsp;<a href="$php_self?m[0]=module&m[1]=form&m[2]=search" title="Search for a module">Search</a>

if($user->isInGroup(array("superuser"))){
	$DEFAULT_MENU .=<<<EOF
	&nbsp;|&nbsp;<a href="module/module_admin.php">Maintain Modules</a>
EOF;
}

switch($m[1]){
case 'form':
	switch($m[2]){
	case 'search':
		$DEFAULT_TITLE .= "\Search";
		$form = new HTML_QuickForm('frmSPRSearch', 'post');
		$form->addElement(	'hidden', 'm[0]', $m[0]);
		$form->addElement(	'hidden', 'm[1]', $m[1]);
		$form->addElement(	'hidden', 'm[2]', $m[2]);
		$form->addElement(	'header', 'title','Module Search');
		$form->addElement(	'text',   'target', 'Look for', array("size"=>"20"));
		$form->addElement(	'submit', 'btnSubmit', 'Submit');
		$form->addRule('target', 'This is required', 'required');
		if($form->validate()){
			$vars = tldUtils::cleanupFormInput($form->exportValues());
            //$rows = tldSPR::search("%{$vars['target']}%");
            //$caption = "Search result for '{$vars['target']}'";
		}
		else $body = $form->toHTML();
	break;
	}
	if(isset($rows,$caption)) $body .= _getListing($rows,$caption);
break;
case 'view':
	include("view.module.inc.php");
break;
default:
	$body = $smarty->fetch("$PATH/module/homepage.module.tpl");
	$body.= _getListing(tldModule::getList(),"Module list");
break;
}


function _getGeneralTab($header){
	$report = new tldAssocTable(
		$header,
		array(
			"id"			=>"Module#",
			"module"		=>"Module name",
			"dsc"			=>"Description",
			"oid_fullname"	=>"Module Owner",
			"uid_fullname"	=>"MIS Owner",
			"note"			=>"Details",
		    "help_page_id"	=>"Procedure DMS#",
		    "help_title"	=>"Help page title",
		    "user_guide_id"	=>"User guide DMS#",
		    "guide_title"	=>"User guide title"
		),
		array("title"=>"General")
	);
	return $report->fetch();
}

function _getListing($rows, $title)
{
    global $php_self, $DMS_URL;
    $report = new tldReportColumnar(
        $rows,
        [
            "xItems" => [
                "id" => "Module#",
                "module" => "Module name",
                "dsc" => "Description",
                "oid_fullname" => "Module Owner",
                "uid_fullname" => "MIS Owner",
                "help_page_id" => "Procedure DMS#",
                "user_guide_id" => "User guide DMS#",
                "migrated" => "Migrated to Symfony",
            ],
            "title" => $title,
            "links" => [
                "id" => "$php_self?m[0]=module&m[1]=view&id=",
                "help_page_id" => "$DMS_URL/index.php?m[0]=view&id=",
                "user_guide_id" => "$DMS_URL/index.php?m[0]=view&id=",
            ],
        ]
    );

    return $report->fetch();
}
?>

