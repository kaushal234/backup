<?php
$DEFAULT_TITLE .= "\Shopcams";

switch($m[1] ?? null){
    case 'getPhoto':
        $jpg = new tldFileJPG(tldUtils::getPathToUploadFile('webcam', $_GET['photo']));
        $jpg->outFile('','',array('width'=>200));
        exit;
    break;
	default:
		$body = include("$PATH/cams.tpl.inc.php");
	break;
}

?>
