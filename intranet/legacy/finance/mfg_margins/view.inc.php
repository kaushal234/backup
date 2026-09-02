<?php
if(empty($id) OR !is_numeric($id)){
    $DEFAULT_ERROR[]=  "ERROR: no ID set...";
    return;
}
$margin = new tldMargin($id);
if($margin->isEmpty()){
	$DEFAULT_ERROR[]=  "ERROR: No Record #$id found...";
	return;
}
switch($m[2]){
	case 'log':
        $body = "This page has been migrated and should not be displayed anymore.";
	break;
	case 'edit':
        $body = "This page has been migrated and should not be displayed anymore.";
	break;
	default:
        $body = "This page has been migrated and should not be displayed anymore.";
	break;
}
?>
