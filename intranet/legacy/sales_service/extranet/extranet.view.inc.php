<?php
require_once("HTML/QuickForm.php");
require_once('HTML/QuickForm/advmultiselect.php'); 
require_once('erp.inc.php'); 

// Add overlib library for CRT section (delivery address)
$JS_INCLUDE=array("/shared/javascript/overlib/overlib.js");

if(empty($id) || !is_numeric($id)){
    $DEFAULT_ERROR[]=  "ERROR: No line id set or invalid id...";
    return;
}
$ext = new extranetUser($id);
if($ext->isEmpty()){
    $DEFAULT_ERROR[]=  "ERROR: No Extranet user found with id '$id'...";
    return;
}
$header = $ext->getHeader();

$DEFAULT_TITLE .= "\Extranet User#$id";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=extranet&m[1]=view&id=$id">General</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=extranet&m[1]=view&m[2]=roles&id=$id" title="Manage Customer Relationship Team and roles">CRT & Roles</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=extranet&m[1]=view&m[2]=email&id=$id" title="Send Confirmation Email">Confirmation Email</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=extranet&m[1]=view&m[2]=edit&id=$id" title="Edit Extranet User">Edit</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=extranet&m[1]=view&m[2]=log&id=$id" title="Customer log">Log</a>
EOF;

if($ext->isEnable() && $user->isInGroup(['ACL_XU_SHADOW'])){
    $DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=extranet&m[1]=view&m[2]=shadow&id=$id" title="Log to Extranet as current user" target="_blank">Shadow</a>
EOF;
}

switch($m[2]){
case 'email':
    $body = "This page has been migrated and should not be displayed anymore.";
break;
case 'edit':
    $body = "This page has been migrated and should not be displayed anymore.";
break;
case 'shadow':
    $body = "This page has been migrated and should not be displayed anymore.";
break;
case 'roles':
    $body = "This page has been migrated and should not be displayed anymore.";
break;
case 'log':
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
function getCdelStringFromArray($cdel){
    throw new Exception("This is no longer used");
}
?>
