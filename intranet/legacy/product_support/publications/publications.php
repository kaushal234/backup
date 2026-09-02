<?php
include_once("common.inc.php");
include_once("publications.inc.php");
include_once("forms_and_reports.inc.php");
include("zip.inc.php");

session_start();
if (!isset($_SESSION['sess'])) {
    $_SESSION['sess'] = null;
}
$sess =& $_SESSION['sess'];

$tldUser = new tldUser($GLOBALS["PHP_AUTH_USER"]);

$smarty = tldUtils::getSmarty("intranet");
$smarty->assign("m", $m);

$php_self = $_SERVER['PHP_SELF'];
$DEFAULT_MENU = <<<EOF
	<a href="$php_self">Home</a>&nbsp;|&nbsp;<a href="$php_self?m[0]=manuals">Manuals</a>&nbsp;|&nbsp;<a href="$php_self?m[0]=documents">Documents</a>
EOF;

$PATH = "sales_service/publications";
$DEFAULT_TEMPLATE = "intranet.tpl";
$DEFAULT_TITLE = "Publications Module";

tldUtils::logUserAccess();

//DOCUMENT FUNCTIONS
switch ($m[0]) {
    case 'file':
        $template = "";
        switch ($m[1]) {
            case diagramImg:
                $document = new document($_REQUEST["id"]);
                $document->outFile();
                break;
        }
        break;
    case 'manuals':
        switch ($m[1]) {
            case 'view':
                if ($id) {
                    $smarty->assign("id", $id);
                    //put manual together
                    $myManual = new manual($id);
                    $smarty->assign("header", $myManual->getHeader());
                    $smarty->assign("docType", $docType);
                    $smarty->assign("docCat", $docCat);
                    $smarty->assign("documents", $myManual->getCategorizedDetails());
                    $body = $smarty->fetch("$PATH/manuals/manual.tpl");
                } else {
                    $smarty->assign("error", "No Manual ID set");
                }
                break;
            case 'pdf':
                if (empty($id)) {
                    $smarty->assign("error", "No Manual ID set");
                } else {
                    $smarty->assign("id", $id);
                    $myManual = new manual($id, "", $lang);
                    $myManual->outPDF();
                    $template = "NO_TEMPLATE";
                }
                break;
            case 'search':
                switch ($m[2]) {
                    case 'byBrandModel':
                        switch ($m[3]) {
                            case 'brand':
                                if (!$id) {
                                    $smarty->assign("error", "No Brand was selected...");
                                } else {
                                    $form = new tldHTMLList(manual::getModels("brand='$id'"),
                                        ["key" => "model", "value" => "model"],
                                        "$php_self?m[0]=manuals&m[1]=search&m[2]=byBrandModel&m[3]=model&id=",
                                        ["title" => "Step 2: Please select Model of equipment..."]
                                    );
                                    $body = $form->fetch();
                                }
                                break;
                            case 'model':
                                if (!$id) {
                                    $smarty->assign("error", "No Model was selected...");
                                } else {
                                    $form = new tldReportMultiLevel(manual::getManuals("model='$id'"),
                                        ["id"],
                                        ["id" => "Manual#",
                                            "date" => "Date",
                                            "brand" => "Brand",
                                            "model" => "Model",
                                            "description" => "Description",
                                            "features" => "Features",
                                        ],
                                        ["passField" => "id",
                                            "title" => "Step 3: Please select Manual...",
                                            "url" => "$php_self?m[0]=manuals&m[1]=view&id="]
                                    );
                                    $body = $form->fetch();
                                }
                                break;
                            default:
                                $form = new tldHTMLList(manual::getBrands(),
                                    ["key" => "brand", "value" => "brand"],
                                    "$php_self?m[0]=manuals&m[1]=search&m[2]=byBrandModel&m[3]=brand&id=",
                                    ["title" => "Step 1: Please select Brand of equipment..."]
                                );
                                $body = $form->fetch();
                        }

                        break;
                }
                break;
            default:
                $body = $smarty->fetch("$PATH/manuals/homepage.manuals.tpl");
        }
        break;
    case 'documents':
        switch ($m[1]) {
            case 'viewImage':
                $document = new document($id);
                //get header
                $headerArray = $document->itsHeader;
                if (empty($headerArray)) {
                    return "$id NOT FOUND.";
                }
                $smarty->assign("header", $headerArray);
                $body = $smarty->fetch("$PATH/documents/document.image.tpl");
                $template = "intranet.plain.tpl";
                break;
            case 'view':
                if ($id) {
                    $document = new document($id);
                    //get header
                    $headerArray = $document->itsHeader;
                    if (empty($headerArray)) {
                        return "$id NOT FOUND.";
                    }
                    $smarty->assign("header", $headerArray);
                    $smarty->assign("brand", $REQUEST_VARS["brand"]);
                    //get detail
                    $smarty->assign("detail", $document->getDetailArray());
                    $body = $smarty->fetch("$PATH/documents/document.tpl");
                } else {
                    $smarty->assign("error", "No Document ID set");
                }
                break;
            case 'pdf':
                $doc = new document($id);
                $doc->outPDF();
                $template = "NO_TEMPLATE";
                break;
                break;
            case 'search':
                switch ($m[2]) {
                    case 'byPartNumber':
                        if ($id) {
                            $xItems = [
                                "man_id" => "Manual#",
                                "brand" => "Brand",
                                "model" => "Model",
                                "doc_id" => "Document#",
                                "category" => "Category",
                                "endescription" => "Description",
                            ];
                            $rows = document::findDocsContainingPN($id);
                            if ($rows) {
                                $report = new tldReportColumnar(
                                    $rows,
                                    [
                                        "xItems" => [
                                            "man_id" => "Manual#",
                                            "brand" => "Brand",
                                            "model" => "Model",
                                            "doc_id" => "Document#",
                                            "category" => "Category",
                                            "endescription" => "Description",
                                        ],
                                        "title" => "Results for '$id' from Documents",
                                        "links" => ["man_id" => "/en/private/product_support/index.ps.php?m[0]=publications&m[1]=manuals&m[2]=view&id=",
                                            "doc_id" => "/en/private/product_support/index.ps.php?m[0]=publications&m[1]=documents&m[2]=view&id="],
                                    ]
                                );
                                $body .= $report->fetch();
                            } else {
                                $DEFAULT_ERROR[] = "ERROR: No entries for $id...";
                            }
                        } else {
                            $body .= $smarty->fetch("$PATH/publications/manuals/search/form.search.byPartNumber.tpl");
                        }
                        break;
                    case 'byPartNumberER':
                        if ($id) {
                            $xItems = [
                                "man_id" => "Manual#",
                                "brand" => "Brand",
                                "model" => "Model",
                                "sn" => "SN#",
                                "cusname" => "Customer (Name)",
                                "doc_id" => "Document#",
                                "category" => "Category",
                                "endescription" => "Description",
                            ];
                            $rows = document::findDocsContainingPNbyER($id);
                            if ($rows) {
                                $report = new tldReportColumnar(
                                    $rows,
                                    [
                                        "xItems" => [
                                            "man_id" => "Manual#",
                                            "brand" => "Brand",
                                            "model" => "Model",
                                            "sn" => "SN#",
                                            "cusname" => "Customer (Name)",
                                            "doc_id" => "Document#",
                                            "category" => "Category",
                                            "endescription" => "Description",
                                        ],
                                        "title" => "Results for '$id' from Documents",
                                        "links" => ["man_id" => "/en/private/product_support/index.ps.php?m[0]=publications&m[1]=manuals&m[2]=view&id=",
                                            "sn" => "/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=search&m[2]=bySN&sn=",
                                            "doc_id" => "/en/private/product_support/index.ps.php?m[0]=publications&m[1]=documents&m[2]=view&id="],
                                    ]
                                );
                                $body .= $report->fetch();
                            } else {
                                $DEFAULT_ERROR[] = "ERROR: No entries for $id...";
                            }
                        } else {
                            $body .= $smarty->fetch("$PATH/publications/manuals/search/form.search.byPartNumberER.tpl");
                        }
                        break;
                }
                break;
            default:
                $body = $smarty->fetch("$PATH/documents/homepage.documents.tpl");
        }
        break;
    case 'search':
        switch ($m[1]) {
            case 'manualID':
                $body = performSearch($_REQUEST, "byBrandModel");
                break;
        }
        break;
    default:
        $body = $smarty->fetch("$PATH/homepage.publications.tpl");
}

$smarty->assign("menu", $DEFAULT_MENU . (isset($menu) ? $menu : ''));
$smarty->assign("body", $body);
if (empty($title)) {
    $title = $DEFAULT_TITLE;
}
$smarty->assign("title", $title);

if (empty($template)) {
    $template = $DEFAULT_TEMPLATE;
}
if ($template <> "NO_TEMPLATE") {
    $smarty->display($template);
}

exit;

function performSearch($REQUEST_VARS, $searchType)
{
    if (empty($searchType)) {
        return;
    }
    $smarty = getSmarty();
    $REQUEST_VARS = tldUtils::cleanupFormInput($REQUEST_VARS);
    $offset = $REQUEST_VARS["offset"];
    $MAX_ROWS_PER_PAGE = 20;
    if (empty($REQUEST_VARS["offset"])) {
        $offset = 0;
    }

    switch ($searchType) {
        case 'manualsByDocNum':
            $id = TldDatabase::escape($REQUEST_VARS["id"]);
            $query = <<<EOF
			SELECT manuals.*
			FROM manuals, manuals_docs
			WHERE manuals.id = manuals_docs.parent_id AND
			manuals_docs.doc_num=$id
EOF;
            $smarty->assign("NEXT_STEP", "m[0]=search&m[1]=manualsByDocNum&id=" . urlencode($id));
            $template = "private/search/manual.list.tpl";
            break;
        case 'byCustomer':
            $customer = TldDatabase::escape($REQUEST_VARS["customer"]);
            $query = <<<EOF
			SELECT service. * , service_serials. *
			FROM service, service_serials
			WHERE customer_name
			LIKE '$customer%' AND (
			service.id = service_serials.parent_id OR service_serials.parent_id IS NULL
			) AND service_serials.component = 'MANUAL'
EOF;
            $smarty->assign("NEXT_STEP", "m[0]=search&m[1]=byCustomer&customer=" . urlencode($REQUEST_VARS["customer"]));
            $template = "private/search/equipment.record.list.tpl";
            break;
        case 'bySerialNumber':
            $sn = TldDatabase::escape(rtrim($REQUEST_VARS["sn"]));
            $query = <<<EOF
			SELECT service. * , service_serials. *
			FROM service, service_serials
			WHERE sn
			LIKE '$sn%' AND (
			service.id = service_serials.parent_id OR service_serials.parent_id IS NULL
			) AND service_serials.component = 'MANUAL'
			LIMIT 50
EOF;
            $smarty->assign("NEXT_STEP", "m[0]=search&m[1]=bySerialNumber&sn=" . urlencode($REQUEST_VARS["sn"]));
            $template = "private/search/equipment.record.list.tpl";
            break;
    }

    $rows = tldUtils::getSqlToAssocArray($query);
    $smarty->assign("error", $error);
    $smarty->assign("count", count($rows));
    $smarty->assign("offset", $offset);
    $smarty->assign("limit", $MAX_ROWS_PER_PAGE);
    //get detail
    if (is_array($rows)) {
        $smarty->assign("rows", array_slice($rows, $offset, $MAX_ROWS_PER_PAGE));
    }
    return $smarty->fetch($template);
}

?>
