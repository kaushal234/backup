<?php
include_once("common.inc.php");
include_once("eng.inc.php");

session_start();
if(!isset($_SESSION['sess_bomsession'])) $_SESSION['sess_bomsession'] = null;
$sess_bomsession =& $_SESSION['sess_bomsession'];

if(empty($sess_bomsession)){
	$sam = new tldBOMSession();
}else{
	$sam = unserialize($sess_bomsession);
}

echo $sam->perform($m,$n);
$sess_bomsession = serialize($sam);
