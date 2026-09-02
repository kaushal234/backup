<?php

use ApiBundle\Client;
use ApiBundle\Http\FileStreamedResponseFactory;
use Symfony\Component\HttpClient\Exception\ClientException;
use Legacy\Controller\Manufacturing\Engineering\BillOfMaterialController;

require_once 'API/Provider/Manufacturing/JobShop/JobShopBillOfMaterialBenchmarkProvider.php';
require_once 'API/Factory/Manufacturing/JobShop/JobShopBillOfMaterialBenchmarkTableFactory.php';

include_once("vault.inc.php");
require_once 'HTML/QuickForm/advmultiselect.php';

$DEFAULT_TITLE .= "\BOM";

if (!$date) {
    $date = date('Y-m-d');
}
$dateTime = new \DateTime($date);
$dateTime->setTime(23, 59, 59);

$container = $kernel->getContainer();
$client = $container->get(Client::class);
$router = $container->get('router');

switch ($m[1] ?? null) {
    case "view":
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=bom&m[1]=view&erp=$erp&pn=$pn&date=$date">SINGLE Level</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=bom&m[1]=view&erp=$erp&pn=$pn&date=$date&multi=TRUE">MULTI Level</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=bom&m[1]=view&m[2]=pdf&erp=$erp&pn=$pn&date=$date">PDF (TESTING)</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=bom&m[1]=view&m[2]=edmpdf&erp=$erp&pn=$pn&date=$date">EDM PDF</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=bom&m[1]=view&m[2]=purDefault&erp=$erp&pn=$pn&date=$date&multi=TRUE">Purchasing</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=bom&m[1]=view&m[2]=WCSumBySubParts&erp=$erp&pn=$pn&date=$date">WC Count</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=bom&m[1]=view&m[2]=pur&erp=$erp&pn=$pn&date=$date&multi=TRUE">Purchasing Benchmark</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=bom&m[1]=view&m[2]=ZipAllDiagram&erp=$erp&pn=$pn&date=$date">Zip&nbsp;all&nbsp;drawings</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=bom&m[1]=view&m[2]=ZipAllDiagram&erp=$erp&pn=$pn&date=$date&flat=1&formats[]=jpg">Zip&nbsp;all&nbsp;drawings&nbsp;(PDF&nbsp;+&nbsp;JPG,&nbsp;Flat)</a>
EOF;


        switch ($m[2] ?? null) {
            case 'pur':
                $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER["REQUEST_URI"]}&m[3]=xls">XLS version</a>&nbsp;|&nbsp;<a href="bom/BOMBenchmarktoolHelppage.doc">Help</a>
EOF;

                if (null === ($pn ?? null)) {
                    $DEFAULT_ERROR[] = sprintf("ERROR: No online data available for ERP %s and Part number %s", $erp ?? '', $pn ?? '');
                    break;
                }
                $pn = trim($pn);

                if (!$date) {
                    $date = date('Y-m-d');
                }

                $controller = new BillOfMaterialController(smarty: $smarty);
                $body = $controller->purchasingBenchmark($erp, $pn, $dateTime);
                break;
            case 'ZipAllDiagram':
            case 'sph':
            case 'box':
                $body = "This page has not been migrated with LN and should not be displayed anymore.";
                break;
            case 'WCSumBySubParts':
                try {
                    $bom = $client->get(
                        sprintf('/ion/bill_of_material_items/site=%d;project=%s;product=%s', $erp, $project, $pn),
                        [
                            'query' => [
                                'date' => $dateTime->format(\DateTimeInterface::ATOM),
                            ]
                        ]
                    );
                } catch (ClientException $exception) {
                    $DEFAULT_ERROR[] = "ERROR: BOM not found.";
                    break;
                }

                $bom = new tldBOM($erp, $pn, $date ?? '', false, [], $bom);
                $bom_wc = $bom->itsBOMAsArray;
                // WC Count
                foreach ($bom_wc as &$line) {
                    $pn = $line['t_sitm'];
                    $WCCount = tldWC::WCCountByPN($pn);
                    $line['wccount'] = $WCCount[0]['count'];
                }
                $smarty->assign("width", "1028");
                $smarty->assign("bom", $bom);
                $smarty->assign("bom_wc", $bom_wc);
                $body .= $smarty->fetch("$PATH/bom/view.bom.wc.tpl");
                break;
            case 'purDefault':
                try {
                    $bom = $client->get(sprintf('/ion/bill-of-materials/purchasing_views/site=%s;project=;product=%s', $erp, $pn));
                } catch (ClientException $exception) {
                    $DEFAULT_ERROR[] = "ERROR: BOM not found.";
                    break;
                }
                $bom = new tldBOM($erp, $pn, $date ?? '', false, [], $bom);
                if ('xls' === ($m[3] ?? null)) {
                    $bomList = $bom->itsBOMAsArray;
                    foreach ($bomList as &$line) {
                        if ('0000-00-00' === $line['t_exdt'] || '1970-01-01' === $line['t_exdt']) {
                            $line['status'] = 'OK';
                        } else {
                            $line['status'] = "This item was changed {$line['t_exdt']}";
                        }
                    }
                    $report = new tldXLS(
                        $bomList,
                        [
                            'xItems' => [
                                't_sitm' => 'Part Number',
                                't_pono' => 'Position',
                                't_dsca' => 'Description',
                                't_qana' => 'Qty',
                                'productQuantity' => 'PBOM Qty',
                                't_cuni' => 'UM',
                                'expired' => 'Status',
                                't_revi' => 'Rev',
                                't_indt' => 'Effective Date',
                                'supplySource' => 'Supply Source',
                                'supplier' => 'Supplier #',
                                'supplierName' => 'Supplier Name',
                                'leadTime' => 'Lead Time',
                                'inventoryOnHand' => 'On Hand',
                                'inventoryOnOrder' => 'On Order',
                                'allocated' => 'Availability',
                            ],
                            'showTitles' => true,
                        ]
                    );
                    $report->out();
                    exit;
                }
                if (count($bom->itsBOMAsArray) !== 0) {
                    $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[3]=xls">XLS version</a>
EOF;
                }
                $smarty->assign("width", "1028");
                $smarty->assign("bom", $bom);
                $body .= $smarty->fetch("$PATH/bom/view.bom.pur.tpl");
                break;
            case 'diagram':
                if (empty($pn)) {
                    exit;
                }
                try {
                    $bom = $client->get(
                        sprintf('/ion/bill_of_material_items/site=%d;project=%s;product=%s', $erp, $project, $pn),
                        [
                            'query' => [
                                'date' => $dateTime->format(\DateTimeInterface::ATOM),
                            ]
                        ]
                    );
                } catch (ClientException $exception) {
                    $DEFAULT_ERROR[] = "ERROR: BOM not found.";
                    break;
                }
                $bom = new tldBOM($erp, $pn, $date ?? '', false, [], $bom);
                $template = "intranet.plain.tpl";
                $file = $bom->getJPGFile();
                if ($file === false) {
                    $body .= "ERROR: No file found for this item...";
                    break;
                }
                switch ($m[3] ?? null) {
                    default:
                        $smarty->assign("bom", $bom);
                        $body .= $smarty->fetch("$PATH/bom/view.bom.diagram.tpl");
                        break;
                }
                break;
            case 'pdf':
                try {
                    $fileStreamResponse = new FileStreamedResponseFactory($client);
                    $dateTime->setTime(23, 59, 59);

                    $response = $fileStreamResponse->create(
                        sprintf('ion/bill_of_material_item_pdf/site=%d;project=%s;product=%s', $erp, $project, $pn),
                        [
                            'headers' => ['Accept' => 'application/pdf'],
                            'query' => [
                                'date' => $dateTime->format(\DateTimeInterface::ATOM),
                            ]
                        ]
                    );

                    $response->send();
                    exit;
                } catch (ClientException $exception) {
                    $DEFAULT_ERROR[] = "ERROR: BOM not found.";
                    break;
                }
            case 'edmpdf':
                try {
                    $bom = $client->get(
                        sprintf('/ion/bill_of_material_items/site=%d;project=%s;product=%s', $erp, $project, $pn),
                        [
                            'query' => [
                                'date' => $dateTime->format(\DateTimeInterface::ATOM),
                            ]
                        ]
                    );
                } catch (ClientException $exception) {
                    $DEFAULT_ERROR[] = "ERROR: BOM not found.";
                    break;
                }
                $bom = new tldEDMBOM($erp, $pn, $date, $bom);
                $pdf = new tldEDMBOMPDF($pn, $date, $bom);
                if (null !== ($download ?? null)) {
                    $pdf->Output("$pn-$date.pdf", 'D');
                } else {
                    $pdf->Output();
                }
                exit;
            default:
                try {
                    $url = $multi ? 'bill_of_material_items' : 'bill-of-materials/intranet_views';
                    $bom = $client->get(
                        sprintf('/ion/%s/site=%d;project=%s;product=%s', $url, $erp, $project, $pn),
                        [
                            'query' => [
                                'date' => $dateTime->format(\DateTimeInterface::ATOM),
                                'depth' => $multi ? 20 : 0,
                                'normalizationGroups' => ['ion:item:non_conformity'],
                            ]
                        ]
                    );
                } catch (ClientException $exception) {
                    $DEFAULT_ERROR[] = "ERROR: BOM not found.";
                    break;
                }

                $bom = new tldBOM(intval($erp), $pn, $date ?? '', (bool)($multi ?? null), [], $bom);
                $sitmBOMs = array_column($bom->itsBOMAsArray, 't_sitm');

                // Get form
                $form = new HTML_QuickForm('frmByNum', 'get');
                $form->addElement('hidden', 'm[0]', 'bom');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('header', 'title', 'Get BOM');
                $form->addElement('select', 'erp', 'Factory', ["" => "", 540 => "TLD EUR"] + tldLocation::getFactoryList("smartyOptions"));
                $form->addElement('text', 'pn', 'Part Number');
                $form->addElement('text', 'date', 'Date');
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->addRule('pn', 'This is required', 'required');
                $form->setDefaults(["erp" => $erp_default, "date" => date("Y-m-d")]);
                $form->setDefaults(["erp" => $DEFAULT_ERP]);
                $body .= $form->toHTML();
                if (count($bom->itsBOMAsArray) > 0) {
                    $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER["REQUEST_URI"]}&m[2]=xls">XLS version</a>
EOF;
                    switch ($m[2] ?? null) {
                        case 'xls':
                            $bom_array = $bom->itsBOMAsArray;
                            foreach ($bom_array as &$line) {
                                if ($line['t_exdt'] == "0000-00-00" || $line['t_exdt'] == "1970-01-01") {
                                    $line['status'] = 'OK';
                                } else {
                                    $line['status'] = "This item was changed {$line['t_exdt']}";
                                }
                            }
                            $report = new tldXLS(
                                $bom_array,
                                [
                                    "xItems" => [
                                        't_sitm' => 'Part Number',
                                        't_pono' => 'Position',
                                        't_dsca' => 'Description',
                                        't_qana' => 'Qty',
                                        'productQuantity' => 'PBOM Qty',
                                        't_cuni' => 'UM',
                                        'status' => 'Status',
                                        't_revi' => 'Rev',
                                        't_indt' => 'Effective Date',
                                        't_csig_edm' => 'Code',
                                        't_exin' => 'Extra Info',
                                        't_opno' => 'Box',
                                        'P' => 'P',
                                        'M' => 'M',
                                        'O' => 'O',
                                        'C' => 'C',
                                        't_stoc' => 'Availability',
                                        't_csel' => 'Owner',
                                    ],
                                    "showTitles" => true,
                                ]
                            );
                            $report->out();
                            exit;
                            break;
                        default:
                            $eapNum = tldEAP::byOpenPartNumber($sitmBOMs);

                            foreach ($eapNum as $eapNumLine) {
                                if (false !== $lineBOMassocAtEAP = array_search($eapNumLine['pn'], $sitmBOMs, true)) {
                                    $bom->itsBOMAsArray[$lineBOMassocAtEAP]['eap'] = true;
                                    $bom->itsBOMAsArray[$lineBOMassocAtEAP]['eap_pn'] = $eapNumLine['pn'];
                                }
                            }

                            $pdcNum = tldPDC::byPartNumber($sitmBOMs, ['EXTRA_WHERE' => "AND pdc.status NOT IN ('CLOSED', 'REJECTED')"]);

                            foreach ($pdcNum as $pdc) {
                                if (false !== $lineBOMassocAtPDC = array_search($pdc['pn'], $sitmBOMs, true)) {
                                    $bom->itsBOMAsArray[$lineBOMassocAtPDC]['pdc'] = true;
                                    $bom->itsBOMAsArray[$lineBOMassocAtPDC]['pdc_pn'] = $pdc['pn'];
                                }
                            }

                            $location = $client->findOneBy('locations', ['erp' => $erp]);

                            $cachedLocation = [];
                            foreach ($bom->itsBOMAsArray as $key => $item) {
                                $bom->itsBOMAsArray[$key]['linkNcr'] = $router->generate('non_conformity_home', [
                                    'filter_non_conformity[location][value]=' => $location->toArray()['@id'],
                                    'filter_non_conformity[partNumber][value]=' => $item['partNumber'] ?? $item['product'],
                                ]);
                            }

                            $JS_INCLUDE=["/shared/javascript/overlib/overlib.js"];
                            $smarty->assign("js_includes", $JS_INCLUDE);
                            $overlib = $smarty->fetch('overlib.inc.js.tpl');
                            $smarty->assign("html_head", $overlib);
                            $smarty->assign("width", "1028");
                            $smarty->assign("bom", $bom);
                            $body .= $smarty->fetch("$PATH/bom/view.bom.tpl");
                            break;
                    }
                } else {
                    $DEFAULT_ERROR[] = "No BOM";
                }
        } // switch($m[2])
        break;
    default:
        $body = $smarty->fetch("$PATH/bom/homepage.bom.tpl");
        // Get erp# from IP
        $erp_default = tldLocation::getERPByID($user->getBUID());
        // Get form
        $form = new HTML_QuickForm('frmByNum', 'get');
        $form->addElement('hidden', 'm[0]', 'bom');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('header', 'title', 'Get BOM');
        $form->addElement('select', 'erp', 'Factory', ["" => "", 540 => "TLD EUR", 620 => "TLD GST", 680 => "TLD CHI"] + tldLocation::getFactoryList("smartyOptions"));
        $form->addElement('text', 'pn', 'Part Number');
        $form->addElement('text', 'date', 'Date');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('pn', 'This is required', 'required');
        $form->setDefaults(["erp" => $erp_default, "date" => date("Y-m-d")]);
        $body .= $form->toHTML();
        break;
}

function _isBOMConflict(array $bom1_line, tldBOM $bom2)
{
    if ($bom1_line['level'] !== 1) {
        return false;
    }
    foreach ($bom2->itsBOMAsArray as $line) {
        if ($line['level'] !== 1) {
            continue;
        }
        if ($bom1_line['t_sitm'] === $line['t_sitm'] && $bom1_line['t_mitm'] === $line['t_mitm'] && $bom1_line['t_pono'] === $line['t_pono']) {
            return false;
        }
    }

    return true;
}
