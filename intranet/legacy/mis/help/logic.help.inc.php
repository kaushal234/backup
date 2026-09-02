<?php
include_once('dms.inc.php');
$DEFAULT_TITLE .= '\Help';

if ($m[1] === 'dms') {
    if (empty($id) || !is_numeric($id)) {
        $DEFAULT_ERROR[] = "ERROR: parameter set empty or invalid";
        exit;
    }
    // Get File
    $e = tldDMS::downloadByPortal($id, ['INTRANET', 'EVENDOR', 'EXTRANET'], isset($filenameFromDMS) && (bool)$filenameFromDMS, $user);
    if (null !== $e) {
        echo $e;
    }
    exit;
}

$body = $smarty->fetch("$PATH/help/homepage.help.tpl");

?>