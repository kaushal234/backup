<?php
include_once("common.inc.php");
include_once("forms_and_reports.inc.php");

session_start();
if(!isset($_SESSION['sess'])) $_SESSION['sess'] = null;
$sess =& $_SESSION['sess'];

$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);
$php_self = $_SERVER['PHP_SELF'];
$PATH="ftp";
$DEFAULT_ERROR = array();

error_log($user->getEmail()." FTP MODULE -> {$GLOBALS['REQUEST_URI']}");
$dir_ftp = "$INTRA_PATH/ftp/files/";

$BODY = <<<EOF
<div style="color:red;">
<p>WARNING: PLEASE MAKE SURE TO ONLY DOWNLOAD FILES BELOW DURING NONE WORKING HOURS AS MUCH AS POSSIBLE, THANK YOU!</p>
</div>
EOF;

switch($m[0]){
default:
    $data = _getFileList($dir_ftp);
    foreach($data as $k=>$filename){
        $file_size=round(filesize($dir_ftp.$filename)/(1024*1024),2);
        $url = "files/".$filename;
        $BODY.= "<br><a href=\"$url\">$filename ($file_size Mo)</a>";
    }
break;
}

$errors = implode("<br>", $DEFAULT_ERROR);
$BODY.= <<<EOF
<p style="color:red;">$errors</p>
EOF;

echo $BODY;

function _getFileList($directory){
    if(!is_dir($directory)) return;
    $file_list = array();
    $dir = opendir($directory);
    while(false!==($file = readdir($dir))){
        $char_file=explode('.',$file);
        if($file!='.' && $file!='..' && is_file($directory.$file)
        && $char_file[0]!='' && strtoupper($char_file[count($char_file)-1])!='PHP'){
            $file_list[]=$file;
        }
    }
    return $file_list;
}
?>