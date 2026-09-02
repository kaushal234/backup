<?
if (empty($id) && empty($eqid)) {
	exit;
}
include_once("common.inc.php");
include_once("eng.inc.php");
include_once("product_support.inc.php");
include_once("publications.inc.php");
//include("File/Archive.php");
include("zip.inc.php");

$PATH = "sales_service/publications";
$smarty = tldUtils::getSmarty("intranet");

$skelDir = "$INTRA_PATH/sales_service/publications/manuals/cdrom_skeleton";
//list of files to pass to zip program
if ($handle = opendir($skelDir)) {
	while (false !== ($fn = readdir($handle))) {
		if ($fn != "." && $fn != "..") {
			$files .= " '$skelDir/$fn'";
		}
	}
	closedir($handle);
}

/*
$files .= " /home/www/www.tld-gse.com/tld-gse.css".
		" /home/www/www.tld-gse.com/shared/icons/pdf-icon.gif".
		" /home/www/www.tld-gse.com/shared/icons/tld-icon.gif";
*/
if ($eqid) {
	$eq = new tldEquipment($eqid);
	$schematics = $eq->getSchematics();

	$vault = new tldReleasedController();
	foreach ($schematics as $schematic) {
		$edm = new basicEDM($schematic["brand"]);
		$file = $vault->getPathToFile($schematic["brand"], $edm->getCurrentFilename($schematic["serial"]));
		$files .= " '$file'";
		$schematic_files[] = ["label" => $schematic["component"] . $schematic["serial"], "file" => basename($file)];
	}
	$smarty->assign("schematics", $schematic_files);
	$manuals = $eq->getManuals();
	if (count($manuals) == 1) {
		$id = $manuals[0]["id"];
	} else {
		echo "Only one manual id allowed.";
		exit;
	}
}
$cache_manuals = "$UPLOADS_PATH/cache/manuals";
$tmpDir = "/tmp/manual_$id";
$zipfile = "$cache_manuals/manual_$id.zip";

if (file_exists($zipfile)) {
//	echo "modified:".date ("F d Y H:i:s.", filemtime($zipfile));
//	exit;
}
if (!file_exists($tmpDir)) {
	mkdir($tmpDir);
}
$smarty->assign("id", $id);
//put manual together
$myManual = new manual($id);

$docs = $myManual->getDetails();
//put documents together
if (count($docs)) {
	foreach ($docs as $doc) {
		//add doc to categorized list to make index page
		if ($doc["category"]) {
			$sortedDocs[$doc["category"]][] = $doc;
		} else {
			$sortedDocs["NO Category"][] = $doc;
		}
		if ($doc["doc_type"] == "MANUAL:SECTION") {
			$manualSections[] = $doc;
		}
		//make file list to pass to zip program
        $files .= " '" . document::getFilePath($GLOBALS["UPLOADS_PATH"], $doc["diagram_filename"]) . "'";
		$document = new document($doc["id"]);
		$headerArray = $document->itsHeader;
		if (empty($headerArray)) {
			return "$id NOT FOUND.";
		}
		$smarty->assign("header", $headerArray);
		//get detail
		$smarty->assign("detail", $document->getDetailArray());
		$docname = "$tmpDir/" . $doc["id"] . ".html";
		$files .= " '$docname'";
		$docHandle = fopen($docname, "w");

		$smarty->assign("title", $headerArray["endescription"]);
		$body = $smarty->fetch("$PATH/documents/zip.document.tpl");
		fwrite($docHandle, $body);
	}
}
//make index page

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
	$files .= " '$pageName'";
//echo $pageName;
//echo $index;
//	echo $files;
	$categoryHandle = fopen("$pageName", "w");
	fwrite($categoryHandle, $index);
}

//chapter 4 and manual sections
$ch4Filename = $myManual->getPDF(true);
$files .= " '$ch4Filename'";

$manualSections[] = ["id" => "ch4",
	"endescription" => "Chapter 4",
	"frdescription" => "Chapter 4",
	"diagram_filename" => basename($ch4Filename)];
$smarty->assign("sections", $manualSections);
$index = $smarty->fetch("$PATH/manuals/zip.menu.tpl");
$pageName = "$tmpDir/menu.html";
$files .= " '$pageName'";
$fileHandle = fopen($pageName, "w");
fwrite($fileHandle, $index);

shell_exec("zip -q -j $zipfile $files");
$file = new basicFile("$zipfile");
$file->outFile();

