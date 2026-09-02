<?
include_once("common.inc.php");
include_once("publications.inc.php");
session_start();

$tldUser = new tldUser($GLOBALS["PHP_AUTH_USER"]);

$smarty = tldUtils::getSmarty("intranet");

$php_self = $GLOBALS["PHP_SELF"];
$PATH = "sales_service/publications";
$DEFAULT_TEMPLATE = "intranet.tpl";

switch($m[0]){
	case outpdf:
		$doc = new document($id);
		$smarty->assign("diagram_path", $doc->itsDiagramUploadDir);
		//get header
		$headerArray = $doc->getHeaderArray($id);
		if(count($headerArray) == 0)
			return "$id NOT FOUND.";
		$smarty->assign("header", $headerArray);

		//get detail
		$smarty->assign("detail", $doc->getDetailArray($id));
		$smarty->assign("body",$smarty->fetch("$PATH/documents/pdf.document.tpl"));
	//echo $html;
		$html = $smarty->fetch($DEFAULT_TEMPLATE);
		$pdf = new html2pdf($html);
		$pdf->outFile();
		$template = "NO_TEMPLATE";
		break;
	break;
}
?>
