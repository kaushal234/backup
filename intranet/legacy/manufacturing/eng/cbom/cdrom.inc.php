<?php
if(!$user->isInGroup(array("gg_SUPPORT","gg_SERVICE","gg_ADMIN","gg_ENG","gg_PARTS"))){
    error_log("An unauthorized person tried to download a zipped CBOM manual :".$user->itsDetails["email"], 1,
            "devteam@tld-america.com");
    $DEFAULT_ERROR[]="ERROR: you do not have the permissions to access this function";
	return;
}
$filelist = array();
$skelDir = "$INTRA_PATH/product_support/publications/manuals/cdrom_skeleton";
//list of files to pass to zip program
if ($handle = opendir($skelDir)) {
   while (false !== ($fn = readdir($handle))) {
       if ($fn != "." && $fn != "..") {
           $filelist[] = "$skelDir/$fn";
       }
   }
   closedir($handle);
}

$page .=<<<EOF
<html>
<head>
<title>TLD Manual $erp-$sn-$date</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="tld-gse.css" rel="stylesheet" type="text/css">
</head>
<body>
<h1>TLD Manual $erp-$sn-$date</h1>
EOF;

//get the chapters
$chapters = $myCBOM->getChapters();
$edm = new basicEDM($erp);
if(count($chapters)){
	$page .= "<h2>Chapters 1, 2, 3</h2><ul>";
	foreach($chapters as $chapter){
		$file = $edm->getFilenameByPNDate($chapter['t_sitm'], $date);
		$v = new tldReleasedController();
		$filepath = $v->getPathToFile($erp, $file);
		$filelist[] = $filepath;
		$page .=<<<EOF
	<li><a href="$file">${chapter['t_dsca']}</a></li>
EOF;
	}
	$page .= "</ul>";
}
//get the schematics
$schematics = $myCBOM->getSchematics();
if(count($schematics)){
	$page .= "<h2>Schematics</h2><ul>";
	foreach($schematics as $schematic){
		$file = $edm->getFilenameByPNDate($schematic['t_sitm'], $date);
		$v = new tldReleasedController();
		$filepath = $v->getPathToFile($erp, $file);
		$filelist[] = $filepath;
		$page .=<<<EOF
	<li><a href="$file">${schematic['t_dsca']}</a></li>
EOF;
	}
	$page .= "</ul>";
}

//get the parts book
$partsfilepath = "/tmp/$erp-$sn-$date-partsbook.pdf";
$cbomPDF = new tldCBOMPDF($erp, $sn, $date);
$cbomPDF->Output($partsfilepath, 'F');
$filelist[] = $partsfilepath;
$page .=<<<EOF
<h2>Parts Book</h2>
<ul>
<li><a href="$erp-$sn-$date-partsbook.pdf">Parts Book $erp-$sn-$date</a></li>
</ul></body></html>
EOF;
$indexfilepath = "/tmp/menu.html";
$smarty->assign("body", $page);
file_put_contents($indexfilepath, $smarty->fetch("intranet.plain.tpl"));
$filelist[] = $indexfilepath;

$zipfile = "/tmp/$erp-$sn-$date-Manual_".date("c").".zip";

shell_exec("zip -q -j $zipfile '".implode("' '", $filelist)."'");
$file = new basicFile($zipfile);
$file->outFile();
exit;
