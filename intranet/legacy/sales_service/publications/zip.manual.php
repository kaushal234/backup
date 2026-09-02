<?
if (empty($id) && empty($eqid)) {
    exit;
}
include_once 'common.inc.php';
include_once 'eng.inc.php';
include_once 'product_support.inc.php';
include_once 'publications.inc.php';
include 'zip.inc.php';
$PATH = 'sales_service/publications';
$smarty = tldUtils::getSmarty('intranet');

//list of files to pass to zip program
$files .= ' /home/www/www.tld-gse.com/tld-gse.css' .
    ' /home/www/www.tld-gse.com/shared/icons/pdf-icon.gif' .
    ' /home/www/www.tld-gse.com/shared/icons/tld-icon.gif';
if ($eqid) {
    $eq = new tldEquipment($eqid);
    $schematics = $eq->getSchematics();

    $vault = new tldReleasedController();
    $schematicFiles = [];
    foreach ($schematics as $schematic) {
        $edm = new basicEDM($schematic['brand']);
        $file = $vault->getPathToFile($schematic['brand'], $edm->getCurrentFilename($schematic['serial']));
        $files .= " '$file'";
        $schematicFiles[] = ['label' => $schematic['component'] . $schematic['serial'], 'file' => basename($file)];
    }
    $smarty->assign('schematics', $schematicFiles);
    $manuals = $eq->getManuals();
    if (count($manuals) !== 1) {
        echo 'Only one manual id allowed.';
        exit;
    }

    $id = $manuals[0]['id'];
}

$smarty->assign('id', $id);
//put manual together
$myManual = new manual($id);
$docs = $myManual->getDetails();
//put documents together
if (count($docs)) {
    foreach ($docs as $doc) {
        //add doc to categorized list to make index page
        $sortedDocs[$doc['doc_type']][$doc['category']][] = $doc;
        //make file list to pass to zip program
        $files .= " '" . document::getFilePath($GLOBALS["UPLOADS_PATH"], $doc["diagram_filename"]) . "'";
        $document = new document($doc['id']);
        $headerArray = $document->getHeader();
        if (empty($headerArray)) {
            return "$id NOT FOUND.";
        }
        $smarty->assign('header', $headerArray);
        //get detail
        $smarty->assign('detail', $document->getDetailArray());
        $docname = '/tmp/' . $doc['id'] . '.html';
        $files .= " '$docname'";
        $docHandle = fopen($docname, 'w');

        $smarty->assign('title', $headerArray['endescription']);
        $body = $smarty->fetch("$PATH/documents/zip.document.tpl");
        fwrite($docHandle, $body);
    }
}
//make index page
$manHeader = $myManual->getHeader();
$smarty->assign('title', $manHeader['description']);
$smarty->assign('header', $manHeader);
$smarty->assign('documents', $sortedDocs);
$index = $smarty->fetch("$PATH/manuals/zip.manual.tpl");
$manualName = 'index.html';
$files .= " '$manualName'";
//echo $files;
$manualHandle = fopen($manualName, 'w');
fwrite($manualHandle, $index);

$zipfile = tempnam('/tmp', "manual_$id_");
shell_exec("zip -q -j $zipfile $files");
$file = new basicFile("$zipfile.zip");
$file->outFile();
