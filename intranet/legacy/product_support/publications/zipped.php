<?php
include_once("common.inc.php");
include_once("eng.inc.php");
include_once("product_support.inc.php");
include_once("publications.inc.php");
include_once("zip.inc.php");
include_once("CDROM/SchematicsTmpDirectory.php");
include_once("CDROM/SchematicsFileCollection.php");
include_once("CDROM/SchematicHTMLCollection.php");

ini_set("memory_limit", "768M");
ini_set("max_execution_time", "60");

$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);
$supervisor = new tldUser($user->getSupervisor());

error_log("START - Zipped CDROM manual download");

// Check if the Manual ID and the ER ID is set
if (empty($id) && empty($eqid)) {
	$error = "ERROR: id or equipment id empty";
	error_log($error);
	echo $error;
	exit;
}

error_log("User#{$user->getID()} {$user->getEmail()} requested CDROM manual of ER# $eqid");
tldUtils::logUserAccess();

error_log("Checking permissions");

// Following TTS#83593 - Asia download allowed list
$gg_ASIA_DOWNLOAD_ALLOWED = [
	64,        // allen.fu@tld-asia.com
	954,    // chris.tam@tld-asia.com
	66,        // alex.lam@tld-asia.com
	322,    // mei.hou@tld-asia.com
	774,    // jenny.chen@tld-asia.com
	758,    // qianmin.tang@tld-asia.com
	103        // peter.ng@tld-asia.com
];

// only persons from tld-asia.com in the authorized group can download CDROM
if (($user->itsDetails["location"] == 3 || $user->itsDetails["location"] == 4 ||
		$user->itsDetails["location"] == 24 || $user->itsDetails["location"] == 25 || $user->itsDetails["location"] == 34)
	&& ((in_array($user->itsId, $gg_ASIA_DOWNLOAD_ALLOWED)) == false) && (!$user->isInGroup(["gg_SUPPORT"]))) {
	error_log("An unauthorized person tried to download a zipped CDROM manual#" . $id . " (ER id#" . $eqid . ") :" . $user->itsDetails["email"], 1,
		"devteam@tld-america.com");
	// log the download even in the logs
	tldUtils::log_event("unauthorized CDROM manual#" . $id . " (ER id#" . $eqid . ") Download attempt by: " . $user->itsDetails["email"]);
	echo "ERROR: you do not have the permissions to access this function";
	exit;
}

if (!$user->isInGroup(["gg_SUPPORT", "gg_ADMIN", "gg_ENG", "gg_PARTS", "gg_PARTS_AGENTS", "gg_SALES", "gg_SALES_AGENTS", "gg_SERVICE", "gg_SERVICE_AGENTS"])) {
	error_log("An unauthorized person tried to download a zipped CDROM manual#" . $id . " (ER id#" . $eqid . ") :" . $user->itsDetails["email"], 1, "devteam@tld-america.com", "From: noreply@tld-gse.com\r\n");
	// log the download even in the logs
	tldUtils::log_event("unauthorized CDROM manual#" . $id . " (ER id#" . $eqid . ") Download attempt by: " . $user->itsDetails["email"]);
	echo "ERROR: you do not have the permissions to access this function";
	exit;
}

if ($user->isInGroup(["gg_SERVICE", "gg_SERVICE_AGENTS", "gg_SALES", "gg_SALES_AGENTS", "gg_PARTS", "gg_PARTS_AGENTS", "gg_ADMIN"], ["return_rows" => true])) {
	// email the PSM
	$subject = "zipped CDROM manual download";
	$message = "A person from the group Service or Sales downloaded a zipped CDROM manual#" . $id . " (ER id#" . $eqid . ") :" . $user->itsDetails["email"];
	// create ER object
	$er = new tldEquipment($eqid);
	// get the ER linked ERP
	$erERP = tldLocation::getERPByLocation($er->itsDetails["man_location"]);
	// send email to all the PSM with matching ERP
	tldGroup::emailGroup("role_PSM", $erERP, $subject, $message);

	// email the Person's supervisor
	error_log("One of your team members from the group Service or Sales downloaded a zipped CDROM manual#" . $id . " (ER id#" . $eqid . ") :" . $user->itsDetails["email"], 1, $supervisor->itsDetails["email"], "From: noreply@tld-gse.com\r\n");

	// log the download even in the logs
	tldUtils::log_event("zipped CDROM manual#" . $id . " (ER id#" . $eqid . ") Downloaded by: " . $user->itsDetails["email"]);
}

error_log("Preparing manual skeleton");

$PATH = "product_support/publications";
$smarty = tldUtils::getSmarty("intranet");
$files = [];

$skelDir = "$INTRA_PATH/product_support/publications/manuals/cdrom_skeleton";
//list of files to pass to zip program
if ($handle = opendir($skelDir)) {
	while (false !== ($fn = readdir($handle))) {
		if ($fn != "." && $fn != "..") {
			$files[] = "$skelDir/$fn";
		}
	}
	closedir($handle);
}

// If equipment id is set then get the schematics for this unit
if (empty($id) && $eqid) {
	error_log("Get equipment schematics");

	$equipment = $er ?: new tldEquipment($eqid);
	$manuals = $equipment->getManuals();
	if (count($manuals) !== 1) {
		echo "Only one manual id allowed.";
		exit;
	}

	$id = $manuals[0]['id'];
	$manualDirectory = new SchematicsTmpDirectory($id);

    $schematicsFileCollection = new SchematicsFileCollection($equipment, $manualDirectory);
	$schematicsFileCollection->init();

    $files = array_merge($files, array_column($schematicsFileCollection->getSchematics(), 'file'));
    $smarty->assign("schematics", (new SchematicHTMLCollection())->generate($schematicsFileCollection->getSchematics()));
}


$cache_manuals = "$HOME_DIR/cache/manuals";
$tmpDir = "/tmp/manual_$id";
$zipfile = "$cache_manuals/manual_$id.zip";

error_log("Create temp manual folder $tmpDir");

if (!file_exists($tmpDir)) {
	if (!mkdir($tmpDir)) {
		error_log("ERROR: Could not create folder");
	}
} else {
	error_log("Folder already exists");
}
$smarty->assign("id", $id);


error_log("Put manual and documents together");

//put manual together
$myManual = new manual($id);
$docs = $myManual->getDetails();
//put documents together
if (count($docs)) {
	foreach ($docs as $doc) {
		//add doc to categorized list to make index page
		$doc['filepath'] = pathinfo($doc["diagram_filename"])['basename'];
		if ($doc["category"]) {
			$sortedDocs[$doc["category"]][] = $doc;
		} else {
			$sortedDocs["NO Category"][] = $doc;
		}
		if ($doc["doc_type"] == "MANUAL:SECTION") {
			$manualSections[] = $doc;
		}
		//make file list to pass to zip program
		if (!empty($doc["diagram_filename"])) {
			$files[] = document::getFilePath($GLOBALS["UPLOADS_PATH"], $doc["diagram_filename"]);
		}
		if (empty($doc["id"])) {
			error_log("Doc ID EMPTY");
			continue;
		}
		$document = new document($doc["id"]);
		$headerArray = $document->itsHeader;
		if (empty($headerArray)) {
			error_log("Doc#$id NOT FOUND");
			continue;
		}
		$smarty->assign("header", $headerArray);
		//get detail
		$smarty->assign("detail", $document->getDetailArray());
		$docname = "$tmpDir/" . $doc["id"] . ".html";
		$files[] = $docname;
		$docHandle = fopen($docname, "w");

		$smarty->assign("title", $headerArray["endescription"]);
		$body = $smarty->fetch("$PATH/documents/zip.document.tpl");
		fwrite($docHandle, $body);
	}
}

// Make index page

error_log("Make index page");

$manHeader = $myManual->getHeader();
$smarty->assign("title", $manHeader["description"]);
$smarty->assign("header", $manHeader);
$categories = array_keys($sortedDocs);
$categories[] = "MANUAL INDEX";
foreach ($categories as $category) {
	$smarty->assign("categories", $categories);
	$smarty->assign("category", $category);
	$smarty->assign("documents", $sortedDocs[$category]);
	$index = $smarty->fetch("$PATH/manuals/zip.manual.test.tpl");
	$pageName = "$tmpDir/$category.html";
	$files[] = $pageName;
	$categoryHandle = fopen("$pageName", "w");
	fwrite($categoryHandle, $index);
}

// Add the pn ref index
$myManual->outPNRefIndex("$tmpDir/pnref.pdf");
$files[] = "$tmpDir/pnref.pdf";
$manualSections[] = [
	"id" => "pnref",
	"endescription" => "Part Number Reference",
	"frdescription" => "",
	"diagram_filename" => "pnref.pdf",
	"filepath" => "pnref.pdf",
];

error_log("Chapter 4 and manual sections");

//chapter 4
$ch4Filename = "$tmpDir/chapter4.pdf";
$pdf = new tldOnlineManualPDF($id, $erp, $myManual->getLang());
$pdf->Output($ch4Filename);
$files[] = $ch4Filename;
//manual sections
$manualSections[] = [
	"id" => "ch4",
	"endescription" => "Chapter 4",
	"frdescription" => "Chapter 4",
	"diagram_filename" => basename($ch4Filename),
	"filepath" => basename($ch4Filename),
];
$smarty->assign("sections", $manualSections);
$index = $smarty->fetch("$PATH/manuals/zip.menu.tpl");
$pageName = "$tmpDir/menu.html";
$files[] = $pageName;
$fileHandle = fopen($pageName, "w");
fwrite($fileHandle, $index);

error_log("Zip manual files");

// Zip all files generated from the manual
$cmd = "zip -q -j $zipfile '" . implode("' '", $files) . "'";
$cleanCmd = escapeshellcmd($cmd);
$output = null;
exec($cleanCmd, $output);
if (!empty($output)) {
	error_log("ERROR: " . json_encode($output));
}

error_log("Send Zipped CDROM manual");

// Send the file
$file = new basicFile("$zipfile");
if (!$file->isFile()) {
	error_log("ERROR: $zipfile not found");
}
$file->outFile();

if (isset($manualDirectory)) {
    $manualDirectory->remove();
}

error_log("END - Zipped CDROM manual download");
?>
