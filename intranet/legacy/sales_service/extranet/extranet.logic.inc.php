<?php
include_once("sales_service.inc.php");
$DEFAULT_TITLE .= "\Extranet User";

if(!$user->isInGroup(array("gg_ADMIN","gg_SALES","gg_PARTS","gg_SERVICE","extranet"))){
    $DEFAULT_ERROR[] = "ERROR: You do not have permission to access this module";
    return;
}

$DEFAULT_MENU.=<<<EOF
	<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="$php_self?m[0]=extranet">Home</a>&nbsp;|&nbsp;
	<a href="$php_self?m[0]=extranet&m[1]=byNumber">By number</a>&nbsp;|&nbsp;
	<a href="$php_self?m[0]=extranet&m[1]=listing&m[2]=search">Search</a>&nbsp;|&nbsp;
	<a href="$php_self?m[0]=extranet&m[1]=reports">Reports</a>&nbsp;|&nbsp;
	<a href="extranet/help.pdf" title="Get PDF guide!">Help</a>&nbsp;|&nbsp;
EOF;

switch($m[1]){
case 'byNumber':
    $body = "This page has been migrated and should not be displayed anymore.";
break;
case 'view':
	include("extranet/extranet.view.inc.php");
break;
case 'reports':
    $body = "This page has been migrated and should not be displayed anymore.";
break;
case 'listing':
    $body = "This page has been migrated and should not be displayed anymore.";
break;
default:
    $body = "This page has been migrated and should not be displayed anymore.";
break;
}

/**
 * @deprecated
 * @throws Exception
 */
function _getGeneralTab(){
	throw new Exception("This is no longer used");
}

/**
 * @deprecated
 * @throws Exception
 */
function _getListing($rows,$title){
    throw new Exception("This is no longer used");
}

/**
 * @deprecated
 * @throws Exception
 */
function _generateChangeListLog($fields, $DataBefore, $DataAfter){
    throw new Exception("This is no longer used");
}

?>