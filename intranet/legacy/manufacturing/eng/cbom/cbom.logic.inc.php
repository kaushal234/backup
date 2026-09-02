<?php
use ApiBundle\Client;
use Symfony\Component\HttpClient\Exception\ClientException;
use ApiBundle\Http\FileStreamedResponseFactory;

$start = time();
require_once 'PHPExcel.php';
require_once 'PHPExcel/Reader/Excel2007.php';
require_once('HTML/QuickForm/advmultiselect.php');
include_once('sales_service.inc.php');

if (empty($sn) && isset($m[1])) {
    $DEFAULT_ERROR[] = 'Parameter SN (Project #) is required to access this page !';
    return;
}

global $kernel;
$request = $kernel->getContainer()->get('request_stack')->getCurrentRequest();
$clientIp = $request->getClientIp();

$DEFAULT_TITLE .= "\CBOM";
switch ($m[1]) {
    case 'view':
        $dateTime = new \DateTime($date);
        $dateTime->setTime(23, 59, 59);

        $container = $kernel->getContainer();
        $client = $container->get(Client::class);

        $loc = tldLocation::getLocationByERP($erp);
        $sol = tldSOL::getIdByCBOM(strtoupper($sn), $loc);
        $ers = tldEquipment::byConstraints(['t_prno' => strtoupper($sn), 'man_location' => $loc]);

        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=cbom&m[1]=view&erp=$erp&date=$date&sn=$sn">CBOM Top Level</a>
 | <a href="$php_self?m[0]=cbom&m[1]=view&m[2]=pva&erp=$erp&date=$date&sn=$sn" title="Configurator Varient List">Variant Config</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cbom&m[1]=view&m[2]=csig&m[3]=material&erp=$erp&date=$date&sn=$sn" title="Manufacturing Material List">Material List</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cbom&m[1]=view&m[2]=csig&m[3]=pmoc&erp=$erp&date=$date&sn=$sn" title="Recommended Spare Parts List">PMOC</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cbom&m[1]=view&m[2]=csig&erp=$erp&date=$date&sn=$sn" title="Full document list">Doc List</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cbom&m[1]=view&m[2]=csig&m[3]=chapters&erp=$erp&date=$date&sn=$sn" title="Chapters 0, 1, 2, 3, 5">Chapters 0,1,2,3,5</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cbom&m[1]=view&m[2]=csig&m[3]=schematics&erp=$erp&date=$date&sn=$sn" title="Schematic Diagram List">Schematics</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cbom&m[1]=view&m[2]=csig&m[3]=ai&erp=$erp&date=$date&sn=$sn" title="Assembly Instructions">AI</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cbom&m[1]=view&m[2]=csig&m[3]=partsbook&erp=$erp&date=$date&sn=$sn" title="Parts book list">Parts Book</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cbom&m[1]=view&m[2]=csig&m[3]=partsbook_pdf&erp=$erp&date=$date&sn=$sn" title="Parts book PDF">Parts Book PDF</a>
EOF;
        if (!empty($sol['id'])) {
            $DEFAULT_MENU .= <<<EOF
 | <a href="/en/private/sales_service/sales.php?m[0]=sol&m[1]=view&id={$sol['id']}" title="Go to SOR Line {$sol['id']}">SOR&nbsp;Line#{$sol['id']}</a>
EOF;
        }

        $multiGetParameter = '';
        if ($multi ?? null) {
            $multiGetParameter = '&multi=true';
            $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=cbom&m[1]=view&erp=$erp&date=$date&sn=$sn&selang=$selang">SINGLE Level</a>
EOF;
        } else {
            $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=cbom&m[1]=view&erp=$erp&date=$date&sn=$sn&multi=true&selang=$selang">MULTI Level</a>
EOF;

        }
        if (count($ers) === 1) {
            $er = current($ers);
            $DEFAULT_MENU .= <<<EOF
 | <a href="/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view&id={$er['id']}" title="Go to ER {$er['sn']}">ER&nbsp;{$er['sn']}</a>
EOF;
        }

        try {
            $languages = $client->get(sprintf('/ion/languages'));
        } catch (ClientException $exception) {
            $DEFAULT_ERROR[] = "ERROR: Languages cannot be fetched .";
            break;
        }
        foreach ($languages['hydra:member'] as $value) {
            $selected = '';

            if ($selang === $value['iso639_2']) {
                $selected = 'selected="selected"';
            }

            $sOptions[] = '<option value="'.$value['iso639_2'].'"'.$selected.'>'.$value['name'].'</option>';
        }
        $sOptions = implode('', (array)$sOptions);

        $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=cbom&m[1]=view&m[2]=cbom_csv&erp=$erp&date=$date&sn=$sn&selang=$selang$multiGetParameter" title="CBOM CSV Download">CSV Version</a>
&nbsp;|&nbsp;<select onchange="window.location=window.location+'&selang='+this.value;"><option>select alt language...</option>$sOptions</select>
EOF;

        $DOCTYPES = [
            'AIG', 'AIE', 'AIH', 'AIM',
            'PID', 'ATP', 'SPI',
            'ESC', 'HSC', 'PSC', 'BSC', 'FLD', 'RTD', 'PPD', 'PRG', 'PRM', 'GAD',
            'CH0', 'CH1', 'CH2', 'CH3', 'CH5',
        ];
        switch ($m[2]) {
            case 'map':
            case 'pdf':
                $body = "This page has not been migrated with LN and should not be displayed anymore.";
                break;
            case 'pva':
                try {
                    $query['date'] = $dateTime->format(\DateTimeInterface::ATOM);
                    $query['normalizationGroups'] = ['variant'];
                    $cbom = $client->get(sprintf('/ion/customized-bill-of-materials/variants/site=%d;project=%s', $erp, $sn), ['query' => $query]);
                } catch (ClientException $exception) {
                    $DEFAULT_ERROR[] = "ERROR: CBOM not found.";
                    break;
                }

                foreach ($cbom['productVariants'] as $productVariant) {
                    $report = new tldAssocTable(
                        $productVariant,
                        [
                            'productVariant' => 'Variant#',
                            'description' => 'Description',
                            'item' => 'Generic Item Type',
                            'referenceOrder' => 'Project#',
                        ],
                        ['title' => 'Product Variant']
                    );
                    $body .= $report->fetch();

                    $report = new tldReportColumnar(
                        $productVariant['options'],
                        [
                            'xItems' => [
                                'sequenceNumber' => 'Seq',
                                'productFeature' => 'Code',
                                'description' => 'Description',
                                'option' => 'Code',
                                'optionDescriptionByProductFeature' => 'Description',
                            ],
                            'title' => 'Variant Config',
                        ]
                    );
                    $body .= $report->fetch();
                }

                break;
            case 'csig':
                $NEXT_STEP = "$php_self?m[0]=getfile&m[1]=drawing&erp=$erp&date=$date&item=";
                $_columns = [
                    't_sitm' => 'PN',
                    't_dsca' => 'EDM Description',
                    'altdsca' => "Alternative {$altDesc[$selang]} Description",
                    't_qana' => 'Qty',
                    't_cuni' => 'UM',
                    't_revi' => 'Rev',
                    't_csig_edm' => 'Code',
                    'p' => 'P',
                    'm' => 'M',
                    'o' => 'O',
                    'c' => 'C',
                ];
                switch ($m[3]) {
                    case 'partsbook':
                        try {
                            $cbom = $client->get(
                                sprintf('/ion/customized-bill-of-materials/chapters/site=%d;project=%s', $erp, $sn),
                                [
                                    'query' => [
                                        'date' => $dateTime->format(\DateTimeInterface::ATOM),
                                        'signalCodeFilter' => implode('|', ['SPA','SPB','SPC','SPD','SPE','SPF','SPG','SPH','SPI','SPJ','SPK','SPL','SPM','SPN','IGA','IGB','IGC','IGD','IGE','IGF','IGG','IGH','IGI','IGJ','IGK','IGL','IGM','IGN','IEA','IEB','IEC','IED','IEE','IEF','IEG','IEH','IEI','IEJ','IEK','IEL','IEM','IEN','IHA','IHB','IHC','IHD','IHE','IHF','IHG','IHH','IHI','IHJ','IHK','IHL','IHM','IHN','IMA','IMB','IMC','IMD','IME','IMF','IMG','IMH','IMI','IMJ','IMK','IML','IMM','IMN']),
                                        'signalCodeFilterMethod' => 'Equals',
                                        'signalCodeAttribute' => 'engineeringSignalCode',
                                        'otherLanguage' => $selang,
                                    ]
                                ]
                            );
                        } catch (ClientException $exception) {
                            $DEFAULT_ERROR[] = "ERROR: CBOM not found.";
                            break 2;
                        }
                        $myCBOM = new tldCBOM($erp, $date, $sn, ['lang' => $selang], $cbom['items']);
                        $rows = $myCBOM->itsBOMAsArray;
                        $NEXT_STEP = "$php_self?m[0]=bom&m[1]=view&erp=$erp&date=$date&pn=";
                        $DEFAULT_TITLE .= "\Chapter 4 Partsbook";
                        break;
                    case 'partsbook_pdf':
                        try {
                            $fileStreamResponse = new FileStreamedResponseFactory($client);

                            $response = $fileStreamResponse->create(
                                sprintf('ion/manual_customized_bill_of_materials_pdf/site=%d;project=%s', $erp, $sn),
                                [
                                    'headers' => ['Accept' => 'application/pdf'],
                                    'timeout' => 300,
                                    'query' => [
                                        'date' => $dateTime->format(\DateTimeInterface::ATOM),
                                        'signalCodeFilter' => implode('|', ['SPA','SPB','SPC','SPD','SPE','SPF','SPG','SPH','SPI','SPJ','SPK','SPL','SPM','SPN','IGA','IGB','IGC','IGD','IGE','IGF','IGG','IGH','IGI','IGJ','IGK','IGL','IGM','IGN','IEA','IEB','IEC','IED','IEE','IEF','IEG','IEH','IEI','IEJ','IEK','IEL','IEM','IEN','IHA','IHB','IHC','IHD','IHE','IHF','IHG','IHH','IHI','IHJ','IHK','IHL','IHM','IHN','IMA','IMB','IMC','IMD','IME','IMF','IMG','IMH','IMI','IMJ','IMK','IML','IMM','IMN']),
                                        'signalCodeFilterMethod' => 'Equals',
                                        'signalCodeAttribute' => 'engineeringSignalCode',
                                        'otherLanguage' => $selang,
                                    ]
                                ]
                            );

                            $response->send();
                            exit;
                        } catch (ClientException $exception) {
                            $DEFAULT_ERROR[] = "ERROR: CBOM not found.";
                            break;
                        }
                    case 'material':
                        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=cbom&m[1]=view&m[2]=csig&m[3]=material&erp=$erp&date=$date&sn=$sn" title="Manufacturing Material List View">View Material List</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cbom&m[1]=view&m[2]=csig&m[3]=material&m[4]=xls&erp=$erp&date=$date&sn=$sn" title="Manufacturing Material List Download">Material List (CSV)</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cbom&m[1]=view&m[2]=csig&m[3]=material&m[4]=parts_xls&erp=$erp&date=$date&sn=$sn" title="Parts Translations Download">Get Parts Translations (CSV)</a>
EOF;

                        $entityLink = 'materials';
                        if (isset($m[4]) && 'xls' === $m[4]) {
                            $entityLink .= '_csvs';
                        }

                        try {
                            $cbom = $client->get(
                                sprintf('/ion/customized-bill-of-materials/%s/site=%d;project=%s', $entityLink, $erp, $sn),
                                [
                                    'query' => [
                                        'date' => $dateTime->format(\DateTimeInterface::ATOM),
                                        'flatResult' => 1,
                                        'depth' => 20,
                                        'otherLanguage' => $selang,
                                    ]
                                ]
                            );
                        } catch (ClientException $exception) {
                            $DEFAULT_ERROR[] = "ERROR: CBOM not found.";
                            break 2;
                        }
                        $myCBOM = new tldCBOM($erp, $date, $sn, ['lang' => $selang], $cbom['items']);
                        $rows = $myCBOM->itsBOMAsArray;
                        switch ($m[4]) {
                            case 'xls':
                                $_extra_columns = [
                                    't_kitm' => 'Item Type',
                                    't_csgp' => 'Stat Code',
                                    't_oqmf' => 'Multi Order Qty',
                                    't_mioq' => 'Min Order Qty',
                                    't_sfst' => 'Safey Stock',
                                    't_oltm' => 'Leadtime',
                                    't_suno' => 'Main Supplier',
                                    'supplier_nama' => 'Supplier Name',
                                    'supplier_pn' => 'Supplier PN',
                                    't_buyr' => 'Buyer Code',
                                    't_copr' => 'Price',
                                    't_cpgs' => 'Price Group',
                                    't_citg' => 'Item Group',
                                ];
                                $xItems = $_columns + $_extra_columns;
                                set_time_limit(0);
                                $report = new tldCSV(
                                    $rows,
                                    [
                                        'xItems' => $xItems,
                                        'showTitles' => true,
                                    ]
                                );
                                $report->out();
                                exit;
                            case 'parts_xls':
                                $body = "This page has not been migrated with LN and should not be displayed anymore.";
                                break;
                            default:
                                $NEXT_STEP = "$php_self?m[0]=getfile&m[1]=drawing&erp=$erp&date=$date&item=";
                                $DEFAULT_TITLE .= "\Material List";
                        }
                        break;
                    case 'pmoc':
                        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=cbom&m[1]=view&m[2]=csig&m[3]=pmoc&erp=$erp&date=$date&sn=$sn" title="Recommended Spare Parts List PMOC">PMOC</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cbom&m[1]=view&m[2]=csig&m[3]=pmoc&m[4]=p&erp=$erp&date=$date&sn=$sn" title="Recommended Spare Parts List P ONLY">P</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cbom&m[1]=view&m[2]=csig&m[3]=pmoc&m[4]=m&erp=$erp&date=$date&sn=$sn" title="Recommended Spare Parts List M ONLY">M</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cbom&m[1]=view&m[2]=csig&m[3]=pmoc&m[4]=o&erp=$erp&date=$date&sn=$sn" title="Recommended Spare Parts List O ONLY">O</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cbom&m[1]=view&m[2]=csig&m[3]=pmoc&m[4]=c&erp=$erp&date=$date&sn=$sn" title="Recommended Spare Parts List C ONLY">C</a>
EOF;
                        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=cbom&m[1]=view&m[2]=csig&m[3]=pmoc&m[5]=excel&erp=$erp&date=$date&sn=$sn" title="Recommended Spare Parts List PMOC">Download Excel</a>
EOF;

                        try {
                            $cbom = $client->get(
                                sprintf('/ion/customized-bill-of-materials/materials/site=%d;project=%s', $erp, $sn),
                                [
                                    'query' => [
                                        'depth' => 20,
                                        'flatResult' => 1,
                                        'otherLanguage' => $selang,
                                    ]
                                ]
                            );
                        } catch (ClientException $exception) {
                            $DEFAULT_ERROR[] = "ERROR: CBOM not found.";
                            break 2;
                        }

                        $myCBOM = new tldCBOM($erp, $date, $sn, ['lang' => $selang], $cbom['items']);
                        $rows = $myCBOM->itsBOMAsArray;

                        foreach ($rows as $key => $row) {
                            if ((null !== $row['p'] || null !== $row['m'] || null !== $row['o'] || null !== $row['c']) && (empty($m[4]) || null !== $row[$m[4]])) {
                                continue;
                            }
                            unset($rows[$key]);
                        }

                        if (isset($m[5]) && 'excel' === $m[5]) {
                            $objPHPExcel = new PHPExcel();
                            $objPHPExcel->setActiveSheetIndex(0);
                            $objPHPExcel->getActiveSheet()->setTitle('PMOC');
                            $lineIndex = 1;
                            $columnIndex = 0;

                            // First line, headers
                            foreach ($_columns as $index => $header) {
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columnIndex++, $lineIndex, $header);
                            }

                            $columnIndex = 0;
                            $lineIndex++;

                            // Content lines
                            foreach ($rows as $row) {
                                foreach ($_columns as $index => $val) {
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columnIndex++, $lineIndex, $row[$index]);
                                }

                                $columnIndex = 0;
                                $lineIndex++;
                            }

                            $fileName = sprintf('%s-PMOC', $sn);
                            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
                            header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                            header(sprintf('Content-Disposition: attachment; filename="%s.xlsx"', $fileName));
                            $objWriter->save('php://output');
                            exit;
                        }

                        $NEXT_STEP = "$php_self?m[0]=getfile&m[1]=drawing&erp=$erp&date=$date&item=";
                        $DEFAULT_TITLE .= "\PMOC";
                        if ($m[4]) {
                            $_columns = [
                                't_sitm' => 'PN',
                                't_dsca' => 'EDM Description',
                                'altdsca' => "Alternative {$altDesc[$selang]} Description",
                                't_qana' => 'Qty',
                                't_cuni' => 'UM',
                                't_revi' => 'Rev',
                                't_csig_edm' => 'Code',
                                $m[4] => strtoupper($m[4]),
                            ];
                        }
                        break;
                    case 'ai':
                        try {
                            $cbom = $client->get(
                                sprintf('/ion/customized-bill-of-materials/chapters/site=%d;project=%s', $erp, $sn),
                                [
                                    'query' => [
                                        'date' => $dateTime->format(\DateTimeInterface::ATOM),
                                        'signalCodeFilter' => implode('|', ['AIM', 'AIE', 'AIH', 'AIG']),
                                        'signalCodeFilterMethod' => 'Equals',
                                        'signalCodeAttribute' => 'engineeringSignalCode',
                                        'otherLanguage' => $selang,
                                    ]
                                ]
                            );
                        } catch (ClientException $exception) {
                            $DEFAULT_ERROR[] = "ERROR: CBOM not found.";
                            break 2;
                        }
                        $myCBOM = new tldCBOM($erp, $date, $sn, ['lang' => $selang], $cbom['items']);
                        $rows = $myCBOM->itsBOMAsArray;
                        $DEFAULT_TITLE .= "\Assembly Instructions";
                        break;
                    case 'schematics':
                        try {
                            $cbom = $client->get(
                                sprintf('/ion/customized-bill-of-materials/chapters/site=%d;project=%s', $erp, $sn),
                                [
                                    'query' => [
                                        'date' => $dateTime->format(\DateTimeInterface::ATOM),
                                        'signalCodeFilter' => implode('|', ['ESC', 'HSC', 'PSC', 'BSC', 'FLD', 'RTD', 'PPD', 'PRG', 'PRM', 'GAD']),
                                        'signalCodeFilterMethod' => 'Equals',
                                        'signalCodeAttribute' => 'engineeringSignalCode',
                                        'otherLanguage' => $selang,
                                    ]
                                ]
                            );
                        } catch (ClientException $exception) {
                            $DEFAULT_ERROR[] = "ERROR: CBOM not found.";
                            break 2;
                        }
                        $myCBOM = new tldCBOM($erp, $date, $sn, ['lang' => $selang], $cbom['items']);
                        $rows = $myCBOM->itsBOMAsArray;
                        $DEFAULT_TITLE .= "\Schematics";
                        break;
                    case 'chapters':
                        try {
                            $cbom = $client->get(sprintf('/ion/customized-bill-of-materials/chapters/site=%d;project=%s', $erp, $sn), [
                                'query' => [
                                    'date' => $dateTime->format(\DateTimeInterface::ATOM),
                                    'signalCodeFilter' => implode('|', ['CH0', 'CH1', 'CH2', 'CH3', 'CH5']),
                                    'signalCodeFilterMethod' => 'Equals',
                                    'signalCodeAttribute' => 'engineeringSignalCode',
                                    'otherLanguage' => $selang,
                                ]
                            ]);
                        } catch (ClientException $exception) {
                            $DEFAULT_ERROR[] = "ERROR: CBOM not found.";
                            break 2;
                        }
                        $myCBOM = new tldCBOM($erp, $date, $sn, ['lang' => $selang], $cbom['items']);
                        $rows = $myCBOM->itsBOMAsArray;
                        $DEFAULT_TITLE .= "\Chapters 0,1,2,3,5";
                        break;
                    default:
                        try {
                            $cbom = $client->get(
                                sprintf('/ion/customized-bill-of-materials/chapters/site=%d;project=%s', $erp, $sn),
                                [
                                    'query' => [
                                        'date' => $dateTime->format(\DateTimeInterface::ATOM),
                                        'signalCodeFilter' => 'VMP',
                                        'signalCodeFilterMethod' => 'DoesNotEqual',
                                        'signalCodeAttribute' => 'engineeringSignalCode',
                                        'otherLanguage' => $selang,
                                    ]
                                ]
                            );
                        } catch (ClientException $exception) {
                            $DEFAULT_ERROR[] = "ERROR: CBOM not found.";
                            break 2;
                        }
                        $myCBOM = new tldCBOM($erp, $date, $sn, ['lang' => $selang], $cbom['items']);
                        $rows = $myCBOM->itsBOMAsArray;

                        foreach ($rows as $key => $row) {
                            if (empty(trim($row['engineeringSignalCode']))) {
                                unset($rows[$key]);
                            }
                        }
                        $NEXT_STEP = "$php_self?m[0]=bom&m[1]=view&erp=$erp&date=$date&pn=";
                        $DEFAULT_TITLE .= "\Document List";
                }


                $report = new tldReportMultiLevel(
                    $rows,
                    ['t_csig_fullname'],
                    $_columns,
                    [
                        'passField' => 't_sitm',
                        'title' => "ERP: $erp, Project#: $sn as of $date",
                        'url' => $NEXT_STEP,
                    ]
                );

                $body .= $report->fetch();
                break;
            case 'cbom_csv':
            default:
                try {
                    $query['date'] = $dateTime->format(\DateTimeInterface::ATOM);

                    if ($selang) {
                        $query['otherLanguage'] = $selang;
                    }


                    if ($multi) {
                        $query['depth'] = 20;
                        $cbom = $client->get(sprintf('/ion/customized-bill-of-materials/multi_level_views/site=%d;project=%s', $erp, $sn), ['query' => $query]);
                    } else {
                        $cbom = $client->get(sprintf('/ion/customized-bill-of-materials/views/site=%d;project=%s', $erp, $sn), ['query' => $query]);
                    }
                } catch (ClientException $exception) {
                    $DEFAULT_ERROR[] = "ERROR: CBOM not found.";
                    break;
                }

                if (empty($cbom)) {
                    $DEFAULT_ERROR[] = 'No CBOM';
                    break;
                }

                $myCBOM = new tldCBOM($erp, $date, $sn, ['lang' => $selang], $cbom['items']);
                if ($myCBOM->isEmpty()) {
                    $DEFAULT_ERROR[] = 'No CBOM';
                    break;
                }

                $rows = [];
                sortRows($myCBOM->itsBOMAsArray, $rows);

                if ($rows) {
                    $sitmCBOMs = array_column($rows, 't_sitm');
                    $pnImpactedByEAP = array_unique(array_column(tldEAP::byOpenPartNumber($sitmCBOMs), 'pn'));

                    $pnImpactedByPDC = array_unique(array_column(tldPDC::byPartNumber($sitmCBOMs, ['EXTRA_WHERE' => "AND pdc.status NOT IN ('CLOSED', 'REJECTED')"]), 'pn'));

                    $pn = implode("','", array_map([TldDatabase::class, 'escape'], $sitmCBOMs));
                    $project = TldDatabase::escape(trim($sn));
                    $query = <<<SQL
			SELECT 
				COUNT(DISTINCT(question_id)) AS nb,
				pn.t_item,
				owner,
				qst.model
			FROM 
				pi_questions AS qst
			LEFT JOIN 
				pi_questions_pn_xref AS pn ON question_id = qst.id
			LEFT JOIN 
				pi_unit_family AS model ON model.family = qst.model
			LEFT JOIN 
				service AS er ON er.sn = model.unit
			WHERE 
				pn.t_item in ('$pn')
			AND t_prno = '$project'
			GROUP BY pn.t_item,owner;
SQL;
                    $questions = tldUtils::getSqlToAssocArray($query);
                    $questionsByPn = [];
                    foreach ($questions as $questionPN) {
                        $form = new HTML_QuickForm('frmHoursAmount', 'post', '/en/private/manufacturing/index.php');
                        $form->addElement('hidden', 'm[0]', 'pi');
                        $form->addElement('hidden', 'm[1]', 'reports');
                        $form->addElement('hidden', 'm[2]', 'piByFilter');
                        $form->addElement('submit', 'btnSubmit', $questionPN['nb'], 'style="background:none;border:none;padding:0;cursor:pointer;outline:none;" onmouseover=\'this.style.textDecoration="underline"\' onmouseout=\'this.style.textDecoration="none"\'');
                        $form->addElement('hidden', 't_item', $questionPN['t_item']);
                        $form->addElement('hidden', 'model', $questionPN['model']);
                        $form->addElement('hidden', 'owner', $questionPN['owner']);
                        $questionsByPn[$questionPN['t_item']][$questionPN['owner']] = $form->toHtml();
                    }
                }

                $group = "Manufactured Item: {$rows[0]['t_mitm']} &nbsp;&nbsp;&nbsp;&nbsp; {$cbom['itemDescription']}";
                // Data adjustments
                foreach ($rows as &$row) {
                    if ($row['t_opol']) {
                        $row['IC'] = 'Cu';
                        $row['sitm'] = $row['t_sitm'];
                    } else {
                        $row['IC'] = 'St';
                        $row['sitm'] = "<a href=\"$php_self?m[0]=bom&m[1]=view&erp=$erp&date=$date&pn={$row['t_sitm']}\">{$row['t_sitm']}</a>";
                    }
                    // case of conflict items
                    if ($row['edm_csel'] === 'CON') {
                        $itemDesc = trim($row['itm_dsca']);
                        $row['t_dsca'] = "<a href=\"#\" title=\"$itemDesc\">{$row['t_dsca']}</a>";
                    }
                    if (in_array($row['t_sitm'], $pnImpactedByEAP, true)) {
                        $row['eap'] = true;
                    }
                    if (in_array($row['t_sitm'], $pnImpactedByPDC, true)) {
                        $row['pdc'] = true;
                    }

                    if (false !== stripos($row['t_dsca'], 'option') || 0 === strpos($row['t_dsca'], 'OPT')) {
                        $row['t_dsca'] = "<font color='blue'>{$row['t_dsca']}</font>";
                    }

                    $row['pi_pcq'] = $row['pi_ecq'] = $row['pi_qcq'] = '';

                    if ($myCBOM->itsLang === 'CH') {
                        $row['altdsca'] = mb_convert_encoding($row['altdsca'], 'UTF-8', 'GBK');
                    }

                    if (!array_key_exists($row['t_sitm'], $questionsByPn)) {
                        continue;
                    }
                    if (array_key_exists('PCQ', $questionsByPn[$row['t_sitm']])) {
                        $row['pi_pcq'] = $questionsByPn[$row['t_sitm']]['PCQ'];
                    }
                    if (array_key_exists('ECQ', $questionsByPn[$row['t_sitm']])) {
                        $row['pi_ecq'] = $questionsByPn[$row['t_sitm']]['ECQ'];
                    }
                    if (array_key_exists('QCQ', $questionsByPn[$row['t_sitm']])) {
                        $row['pi_qcq'] = $questionsByPn[$row['t_sitm']]['QCQ'];
                    }
                }

                if (isset($m[2]) && 'cbom_csv' === $m[2]) {
                    $xItems = [
                        't_pono' => 'Pos Number',
                        'level' => 'Level',
                        't_sitm' => 'PN',
                        't_csig_edm' => 'Code',
                        't_dsca' => 'EDM Description',
                        'altdsca' => "Alternative {$altDesc[$selang]} Description",
                        'IC' => 'IC',
                        't_qana' => 'Qty',
                        'productQuantity' => 'PBOM Qty',
                        't_opno' => 'Oper',
                        't_exin' => 'Extra',
                        'eap_pn' => 'EAP flag',
                    ];

                    /* Do not Use $key => $row in this foreach to avoid collision with reference affectation in the loop above */
                    foreach ($rows as $key => $item) {
                        $rows[$key]['t_pono'] = str_repeat('.', ((int) $item['level'])).$item['t_pono'];
                    }

                    set_time_limit(0);
                    $report = new tldCSV($rows, [
                            'xItems' => $xItems,
                            'showTitles' => true,
                    ]);
                    $report->out();
                    exit;
                }

                // Display report
                $smarty->assign("width", "1028");
                $smarty->assign("rows", $rows);
                $smarty->assign("group", $group);
                $smarty->assign('erp', $erp);
                $smarty->assign('sn', $sn);
                $smarty->assign('date', $myCBOM->itsDate);
                $smarty->assign('altLang', $altDesc[$selang]);
                $body .= $smarty->fetch("$PATH/cbom/view.cbom.tpl");
                break;
        }
        break;
    case 'byCSIG':
        $body = "This page has not been migrated with LN and should not be displayed anymore.";
        break;
    default:
        $body = $smarty->fetch("$PATH/cbom/homepage.cbom.tpl");
        // Get erp# from IP
        $bus = tldLocation::byOutsideNetworkAddress($clientIp);
        if (count($bus) === 1) {
            $erp_default = $bus[0]['erp'];
        } else { // Else look ENG role
            $erp_gg_ENG = (array)$user->isInGroup('gg_ENG');
            $erp_default = $erp_gg_ENG[0];
        }
        // Get form
        $form = new HTML_QuickForm('frmByNum', 'get');
        $form->addElement('hidden', 'm[0]', 'cbom');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('header', 'title', 'Get CBOM');
        $form->addElement('select', 'erp', 'Factory',
            ['' => ''] + tldLocation::getFactoryList('smartyOptions'));
        $form->addElement('text', 'sn', 'Project Number');
        $form->addElement('text', 'date', 'Date');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->setDefaults(['erp' => $erp_default, 'date' => date('Y-m-d')]);
        $form->setDefaults(['erp' => $DEFAULT_ERP]);
        $body .= $form->toHTML();
        break;
}

function sortRows(array &$rows, array &$result)
{
    foreach ($rows as $row) {
        $result[] = $row;

        if (!empty($row['children'])) {
            sortRows($row['children'], $result);
        }
    }
}

function _groupMaterialsPN($rows)
{
    $array = [];
    foreach ($rows as $row) {
        if (!$array[$row['t_sitm']]) {
            $array[$row['t_sitm']] = $row;
        } else {
            $array[$row['t_sitm']]['t_qana'] += $row['t_qana'];
        }
    }
    return $array;
}
