<?php
include_once('publications.inc.php');
include_once('vault.inc.php');
include_once('erp.others.inc.php');  // For Sage item lookup

$JS_INCLUDE=["/shared/javascript/overlib/overlib.js"];
$DEFAULT_TITLE .= '/Part Dashboard';
$id = strtoupper($id);

switch ($m[1]) {
    case 'view':
        if (!$user->isInGroup(['gg_ADMIN', 'gg_PARTS', 'gg_SUPPORT', 'gg_PUR', 'gg_SERVICE', 'gg_BOOST', 'gg_PARTS_AGENTS', 'role_RME', 'gg_ENG', 'role_FC', 'role_ASM'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permissions';
            break;
        }

        $authorizationChecker = $kernel->getContainer()->get('security.authorization_checker.legacy');

        if (!$authorizationChecker->isGranted('SUBDIVISION_FEATURE_PARTS_DASHBOARD_FULL_VIEW')) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permissions';
            break;
        }

        // Secure data
        $id = TldDatabase::escape($id);

        $DEFAULT_TITLE .= "/PN# $id";
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=inv&m[1]=view&id=$id">Part Dashboard</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=inv&m[1]=view&m[2]=eap&id=$id" title="Related EAPS">EAPs</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=inv&m[1]=view&m[2]=wc&id=$id" title="Warranties with this PN">WC</a>
&nbsp;|&nbsp;<a href="/en/private/manufacturing/pur/dev.php?m[0]=xref&m[1]=searchByAltPNByTLDPN&erpid=$DEFAULT_ERP&pn=$id" title="Search By Alt PN">XRef</a>
&nbsp;|&nbsp;<a href="/en/private/manufacturing/whse/dev.php?m[0]=inv&m[1]=byPNPlanned&id=$id&erp=$DEFAULT_ERP&btnSubmit=Submit" title="Warehouse">Warehouse</a>
EOF;
        $query = <<<EOF
	SELECT COUNT(*) AS count
	FROM sage_xref
	WHERE TLD_Item='$id'
EOF;
        $chk = tldUtils::getSqlRowToAssocArray($query);
        if ($chk['count'] > 0) {
            $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=inv&m[1]=view&m[2]=sage&id=$id" title="Get Sage Inventory">Sage Inventory</a>
EOF;
        }
        switch ($m[2]) {
            case 'eap':
                //list EAPS
                $rows = tldEAP::byPartNumber($id);
                $report = new tldReportColumnar(
                    $rows,
                    [
                        'xItems' => [
                            'id' => 'EAP#',
                            'status' => 'Status',
                            'ifactor' => 'iFactor',
                            'overnight' => 'OVERNIGHT',
                            'dt_opened' => 'Date',
                            'nb_task' => 'BP tasks ?',
                            'short_desc' => 'Short Description',
                        ],
                        'title' => 'Related EAPs',
                        'links' => ['id' => '/en/private/manufacturing/eng/dev.php?m[0]=eap&m[1]=view&id='],
                        'showzero' => true,
                    ]
                );
                $body .= $report->fetch();
                break 2;
            case 'wc':
                //list warranties
                $form = new tldReportColumnar(
                    tldWC::byPartNumberByConstraints($id),
                    [
                        'xItems' => [
                            'id' => 'WC#',
                            'warranty_status' => 'Status',
                            'claim_date' => 'Date',
                            'entered_by' => 'Entered By',
                            'model' => 'Model',
                            'serial_number' => 'Serial Number',
                            'problem_desc' => 'Problem Description',
                        ],
                        'title' => 'Warranties With This Part Number',
                        'links' => [
                            'id' => '/en/private/product_support/index.ps.php?m[0]=wc&m[1]=view&id=',
                        ],
                    ]
                );
                $body .= $form->fetch();
                break 2;
            case 'sage':
                //list Sage inventory
                try {
                    $sage = new SageAPI;
                    $res = $sage->getPriceAndAvailability($id, 'YES');
                } catch (Exception $e) {
                    $DEFAULT_ERROR[] = 'Sage Item does not exists';
                    break 2;
                }
                if (empty($res['Item']['Location'])) {
                    $DEFAULT_ERROR[] = 'Sage Item does not exists';
                    break 2;
                }
                $sage_inv = [];
                foreach ($res['Item']['Location'] as $loc) {
                    $sage_inv[] = [
                        'MasterID' => $res['Item']['MasterID'],
                        'SageItemId' => $res['Item']['SageItemId'],
                        'ItemDescription' => $res['Item']['ItemDescription'],
                        'SageUID' => $res['Item']['SageUID'],
                        'Location' => SageAPI::getLocation($loc['Name']),
                        'Currency' => $loc['UnitPrice']['Currency'],
                        'Price' => $loc['UnitPrice']['_content'],
                        'OnHand' => $loc['OnHand']['_content'],
                        'OnHandUM' => $loc['OnHand']['Unit'],
                        'OnOrder' => $loc['OnOrder']['_content'],
                        'OnOrderUM' => $loc['OnOrder']['Unit'],
                    ];
                }
                $form = new tldReportColumnar(
                    $sage_inv,
                    [
                        'xItems' => [
                            'MasterID' => 'TLD PN',
                            'SageItemId' => 'SAGE PN',
                            'ItemDescription' => 'DESCRIPTION',
                            'SageUID' => 'SAGE UID',
                            'Location' => 'SAGE LOCATION',
                            'Currency' => 'CURRENCY',
                            'Price' => 'PRICE',
                            'OnHand' => 'ON HAND',
                            'OnHandUM' => 'ON HAND UM',
                            'OnOrder' => 'ON ORDER',
                            'OnOrderUM' => 'ON ORDER UM',
                        ],
                        'title' => 'Sage Inventory',
                    ]
                );
                $body .= $form->fetch();
                break 2;
        }

        // 1 - Standard search form + cart

        $formPN = new HTML_QuickForm('form', 'get');
        $formPN->addElement('header', 'title', 'Search inventory');
        $formPN->addElement('hidden', 'm[0]', 'inv');
        $formPN->addElement('hidden', 'm[1]', 'view');
        $formPN->addElement('text', 'id', 'PN');
        $formPN->addElement('submit', 'btnSubmit', 'Search');
        $formPN->addElement('submit', 'btnSubmitShipments', 'Show Shipments');
        $formPN->setDefaults(['qty' => 1]);

        if ($formPN->validate() && $formPN->exportValue('btnSubmitShipments') !== null) {
            header(
                sprintf(
                    'Location: %s?m[0]=&m[1]=bySearch&erp=%s&item=%s',
                    $php_self,
                    $DEFAULT_ERP,
                    $formPN->exportValue('id')
                )
            );
        }

        $formCart = new HTML_QuickForm('form', 'post');
        $formCart->addElement('header', 'title', 'Add to cart');
        $formCart->addElement('hidden', 'm[0]', 'cart');
        $formCart->addElement('text', 'id', 'PN');
        $formCart->addElement('text', 'qty', 'Qty', ['size' => 3]);
        $formCart->addElement('reset', 'btnReset', 'Reset');
        $formCart->addElement('submit', 'btnSubmit', 'Add to cart');
        $formCart->setDefaults(
            [
                'qty' => 1,
                'id' => $id,
            ]
        );
        $id = trim($id);
        // Include jqzoominc lib to have the zoom on pictures
        $smarty->assign('html_head', $smarty->fetch(__DIR__ . '/jqzoominc.inv.tpl'));
        // Get Photo Vault Controller
        $photoController = new tldPhotoController();
        // Get single Photo Vault Controller
        $filePath = $photoController->fileExistsInVault($DEFAULT_ERP, "$id.JPG");
        $imgParams = [];
        if (!empty($filePath) && $filePath !== -1) {
            $imgParams[] = [
                'link' => [
                    'm' => [0 => 'getfile', 1 => 'photo'],
                    'erp' => $DEFAULT_ERP,
                    'item' => $id,
                ],
                'img' => [
                    'm' => [0 => 'getfile', 1 => 'photo'],
                    'erp' => $DEFAULT_ERP,
                    'item' => $id,
                    'new_width' => 256,
                ],
            ];
            $IMG = <<<EOF
<a href="/en/private/manufacturing/eng/dev.php?m[0]=getfile&m[1]=photo&erp=$DEFAULT_ERP&item=$id" class="jqzoom" style="" title="">
<img src="/en/private/manufacturing/eng/dev.php?m[0]=getfile&m[1]=photo&erp=$DEFAULT_ERP&new_width=256&item=$id&new_width=256" title="" align="right">
</a>
EOF;
        }
        // If the PN folder exists, add multiple PN pictures
        $folderpath = $photoController->fileExistsInVault($DEFAULT_ERP, $id);
        if (!empty($folderpath) && $folderpath !== -1) {
            // Get list of the other files in vault
            $files = $photoController->getDirFilelist($DEFAULT_ERP, $folderpath);
            // retrieve all picture files to display it
            foreach ($files as $key => $file) {
                if (strtolower($file) === 'thumbs.db') {
                    continue;
                }
                $imgParams[] = [
                    'link' => [
                        'm' => [0 => 'getfile', 1 => 'photo'],
                        'erp' => $DEFAULT_ERP,
                        'item' => $id,
                        'key' => $key,
                    ],
                    'img' => [
                        'm' => [0 => 'getfile', 1 => 'photo'],
                        'erp' => $DEFAULT_ERP,
                        'item' => $id,
                        'key' => $key,
                        'new_width' => 128,
                    ],
                ];
            }
        }
        $imgHtml = '';
        foreach ($imgParams as $img) {
            $imgHtml .= '<a href="/en/private/manufacturing/eng/dev.php?' . http_build_query($img['link']) . '" class="jqzoom"><img src="/en/private/manufacturing/eng/dev.php?' . http_build_query($img['img']) . '" alt="" align="right" /></a>' . "\n";
        }
        $linkHtml = '';
        foreach ($imgParams as $key => $img) {
            $linkHtml .= '<br/><a href="/en/private/manufacturing/eng/dev.php?' . http_build_query($img['link']) . '" target="_blank">Open Image #' . ($key + 1) . ' In New Window</a>' . "\n";
        }

        $body = <<<EOF
<table width="100%">
  <tr>
  	<td width="25%">{$formPN->toHTML()}</td>
  	<td width="30%">{$formCart->toHTML()}</td>
  	<td>$imgHtml</td>
  </tr>
  <tr>
  	<td colspan="2">&nbsp;</td>
  	<td>$linkHtml</td>
  </tr>
</table>
EOF;

        // 2 - Display now Parts data for all BUs

        $body .= '<h2>Live data</h2>';
        $cells = [];
        $TXTA = [];
        $invs = [220, 250, 300, 400, 410, 420, 500, 510, 520, 540, 560, 570, 600, 620, 640, 660, 680];

        // get languages
        $_langs = [];
        $query = <<<EOF
    SELECT t_clan, RTRIM(t_dsca) AS t_dsca
    FROM tttaad110000
EOF;
        $res = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
        foreach ($res as $r) {
            $_langs[$r['t_clan']] = $r['t_dsca'];
        }

        $pmoc = null;
        foreach ($invs as $inv) {
            $myerp = tldERP::getERPOb($inv);
            $header = $myerp->getItemData($id);
            $pmoc = $pmoc ?: trim($header['pmoc']);
            // Source ITM
            $txta = $a = $t = [];
            $rows = tldBaanERP::getTXT($header['t_txta']);
            foreach ($rows as $row) {
                if (!in_array($row['t_text'], (array)$t[$row['t_clan']], false)) {
                    // Fix for Task#407321 to drop french text in asian companies
                    if (in_array((int)$inv, [600, 620, 640, 660, 680], true) && (int)$row['t_clan'] === 4) {
                        continue;
                    }
                    // End fix
                    $a[$row['t_clan']] .= $row['t_text'];
                    $t[$row['t_clan']][] = $row['t_text'];
                }
            }
            foreach ($a as $k => &$v) {
                $v = "<em><u>{$_langs[$k]}</u>:</em> " . _cleanTXT($v);
            }
            unset($v);
            $str = trim(implode('<hr/>', array_unique($a)));
            if (!empty($str)) {
                $txta[] = array_merge($rows[0], ['SRC' => 'ITM', 'TXT' => $str, 'ERP' => $inv]);
            }

            // finally add the text field to the TEXT header
            foreach ($txta as $a) {
                $TXTA[$a['t_ctxt']] = $a;
            }

            if (!empty($header)) {
                // Get inventory data
                $rows = $myerp->getInvData($id);
                if (count($rows)) {
                    foreach ($rows as $row) {
                        $row['wh_sfst'] = $row['sfst'];
                        $lines[] = array_merge($header, $row);
                    }
                } else {
                    $lines[] = $header;
                }
            }
        }

        $TXTA = array_values($TXTA);

        $xitems = [
            'ERP' => 'ERP',
            'ITEM' => 'Part Number',
            'ALT_DESCRIPTION' => 'Description',
            'LEAD' => 'Leadtime',
            't_csig' => 'Signal Code',
            'WT' => 'Weight',
        ];

        // Warehouse information
        if ($user->isInGroup(['gg_ADMIN', 'gg_PARTS', 'gg_SUPPORT', 'gg_PUR', 'gg_BOOST', 'gg_PARTS_AGENTS', 'role_RME', 'gg_ENG', 'gg_SERVICE', 'role_FC', 'role_ASM'])) {
            $xitems = array_merge(
                $xitems,
                [
                    't_cwar' => 'Warehouse Code',
                    'cwar_fullname' => 'Warehouse',
                    'reop' => 'Warehouse Reop',
                    'wh_sfst' => 'Warehouse Safety',
                ]
            );
        }
        // stock information
        $xitems = array_merge(
            $xitems,
            [
                't_sfst' => 'Item Safety',
                'stoc' => 'On Hand',
                'ordr' => 'On Order',
                'allo' => 'Alloc',
                'avail' => 'Avail',
            ]
        );
        // Purchasing parts info
        if ($user->isInGroup(['gg_ADMIN', 'gg_PARTS', 'gg_SUPPORT', 'gg_PUR', 'role_FC', 'role_ASM', 'gg_ENG'])) {
            $xitems['t_ccur'] = 'Pur Cur';
            $xitems['t_prip'] = 'Pur Price';
            $xitems['t_ltpp'] = 'Last Pur Price Date';
        }
        // Standard parts info
        if ($user->isInGroup(['gg_ADMIN', 'gg_PARTS', 'gg_SUPPORT', 'gg_PUR', 'gg_BOOST', 'role_FC', 'role_ASM', 'gg_ENG'])) {
            $xitems['UM'] = 'UM';
            $xitems['STDCCUR'] = 'STD Cur';
            $xitems['STDCOST'] = 'Std Cost';
        }
        // MIP parts info
        if ($user->isInGroup(['gg_ADMIN', 'gg_PARTS', 'gg_SUPPORT', 'gg_BOOST', 'role_FC', 'gg_PARTS_AGENTS'])) {
            $xitems['CURRENCY'] = 'MIP Cur';
            $xitems['MIP'] = 'MIP';

            $mip_array = [];

            foreach ($lines as $line) {
                $mip_array[$line['MIPPILOT']]['MIPPILOT'] = $line['MIPPILOT'];
                $mip_array[$line['MIPPILOT']]['MIPCOEF'] = $line['MIPCOEF'];
                $mip_array[$line['MIPPILOT']]['MIPMULT'] = $line['MIPMULT'];
            }

            $xItems = [
                'MIPPILOT' => 'Pilot',
                'MIPCOEF' => 'Category',
                'MIPMULT' => 'Multiplier',
            ];

            $pmoc = '';
            if ($user->isAgent() || ($user->isInGroup('gg_PARTS_AGENTS') && !$user->isInGroup('SUPERUSER'))) {
                // Agent should not access those information
                unset($xItems['MIPCOEF'], $xItems['MIPMULT']);
            } else {
                $pmoc = (new tldReportColumnar([['PMOC' => $pmoc ?? '']], ['sortable' => 'no']))->fetch();
            }

            $mip_report = new tldReportColumnar(
                $mip_array,
                [
                    'xItems' => $xItems,
                    'title' => 'MIP Details',
                ]
            );

            $mip = $mip_report->fetch();

            $body = <<<EOF
<table width="100%">
  <tr>
  	<td width="15%">{$formPN->toHTML()}</td>
  	<td width="20%">{$formCart->toHTML()}</td>
  	<td width="20%">{$mip}{$pmoc}</td>
  	<td>$imgHtml</td>
  </tr>
  <tr>
  	<td colspan="3">&nbsp;</td>
  	<td>$linkHtml</td>
  </tr>
</table>
EOF;
        }
        // Links to access EDM/BOM
        $functions = [];
        if ($user->isInGroup(['gg_ADMIN', 'gg_PARTS', 'gg_SUPPORT', 'gg_PUR', 'gg_SERVICE', 'role_RME', 'gg_ENG', 'role_FC', 'role_ASM'])) {
            $functions = [
                'EDM' => [
                    'url' => '/en/private/manufacturing/eng/dev.php?m[0]=edm&m[1]=view&',
                    'param' => [
                        'erp' => 'ERP', 'item' => 'ITEM',
                    ],
                ],
                'BOM' => [
                    'url' => '/en/private/manufacturing/eng/dev.php?m[0]=bom&m[1]=view&',
                    'param' => [
                        'erp' => 'ERP', 'pn' => 'ITEM',
                    ],
                ],
            ];
        }

        // Display the report
        $report = new tldReportColumnar(
            $lines,
            [
                'xItems' => $xitems,
                'functions' => $functions,
            ]
        );
        $body .= $report->fetch();

        // Item Text report
        $report = new tldReportColumnar(
            $TXTA,
            [
                'xItems' => [
                    't_ctxt' => 'Text#',
                    'dt' => 'Date',
                    'ERP' => 'ERP#',
                    'SRC' => 'Source',
                    'TXT' => 'Text',
                ],
                'title' => "Text for PN# $id",
            ]
        );
        if ($user->isInGroup(['gg_ADMIN', 'gg_PARTS', 'gg_SUPPORT', 'gg_PUR', 'gg_SERVICE', 'role_RME', 'gg_ENG', 'role_FC', 'role_ASM'])) {
            $body .= $report->fetch();
        }
        break;
    case 'listing':
        switch ($m[2]) {
            case 'byRSPL':
                $man = new manual($id);
                $rows = $man->getRSPL($m[3]);
                unset($id);
                foreach ($rows as $row) {
                    $id[] = $row['pn'];
                }
                break;
        }
        $rows = tldERP::searchCachedItemDatabyPN($id);
        $smarty->assign('parts', $rows);
        $body .= $smarty->fetch("$PATH/inv/list.inv.tpl");
        break;
    default:
        $form = new HTML_QuickForm('form', 'get');
        $form->addElement('header', 'title', 'Search inventory');
        $form->addElement('hidden', 'm[0]', 'inv');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('text', 'id', 'PN', " onclick=\"javascript:this.value=''\"");
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->setDefaults(['qty' => 1]);
        $body .= $form->toHTML();
        break;
}

// Functions:
function _cleanTXT($string)
{
    // Clean special char
    $string = str_replace(['>', '<'], '', $string);
    // HTML entities
    $string = htmlspecialchars($string, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1');
    // Clean line return
    $string = str_replace(["\r\n", "\r", "\n"], '<br/>', $string);
    // Trim
    $string = trim($string);

    return $string;
}

