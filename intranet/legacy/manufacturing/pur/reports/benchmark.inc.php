<?php
require_once('HTML/QuickForm/advmultiselect.php'); 

switch($m[2]){
	case 'supplier':
        // only use factories with real companies in the ERP
        $factories = array_filter(tldLocation::getLocationList('smartyOptionsERPLocation'), function($erp) {
            return !in_array($erp, [0, 800, 390, 999], true);
        }, ARRAY_FILTER_USE_KEY);
		switch($m[3]){
            case 'step3':
                // Step 3 - Select triggers and compare BUs
                if (!$erp) {
                    $DEFAULT_ERROR[] = "Factory BU was not set";
                    break;
                }
                if (!$sup) {
                    $DEFAULT_ERROR[] = "Supplier was not set";
                    break;
                }
                $erpList = tldLocation::getERPList("smartyOptions");
                $form = new HTML_QuickForm('frmBenchmarkSupplier3', 'get', "", "", '', true);
                $form->addElement('hidden', 'm[0]', 'reports');
                $form->addElement('hidden', 'm[1]', 'benchmark');
                $form->addElement('hidden', 'm[2]', 'supplier');
                $form->addElement('hidden', 'm[3]', $m[3]);
                $form->addElement('hidden', 'erp', $erp);
                $form->addElement('hidden', 'sup', $sup);
                $form->addElement('header', 'title', 'Compare With Other Factories (REQUIRED):');
                unset($factories[$erp]);
                $ams =& $form->addElement('advmultiselect', 'bus', null,
                    $factories,
                    [
                        'size' => 8,
                        'class' => 'pool',
                        'style' => 'width:150px;'
                    ]
                );
                $ams->setLabel(['Compare With:', 'Factories:', 'Selected:']);
                $ams->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
                $ams->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
                $form->addElement('header', 'title', 'Filter Triggers (OPTIONAL):');
                $form->addElement('text', 'trg_vol', 'Benchmark Volume Trigger (greater than)');
                $form->addElement('select', 'trg_bol', '', ["AND" => "AND", "OR" => "OR"]);
                $form->addElement('text', 'trg_pct', 'Benchmark Percent Trigger (greater than)');
                $form->addRule('bus', 'This is required', 'required');
                $form->addElement('submit', 'btnSubmit', 'Submit', ['class' => 'disablesubmit']);
                if (!$form->validate()) {
                    $body .= $form->toHTML();
                    break;
                }

                // Cleanup vars
                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $vars['trg_vol'] = (int)$vars['trg_vol'];
                $vars['trg_pct'] = (float)preg_replace('/[^0-9.]/', '', $vars['trg_pct']) / 100;
                $vars['trg_bol'] = ('OR' == $vars['trg_bol']) ? 'OR' : 'AND';
                $bus = array_map('intval', $vars['bus']);
                $erp = (int)$erp;
                $suno = TldDatabase::escape($vars['sup']);

                if (!isset($sess['BenchmarkSupplierData' . md5(serialize($vars))])) {
                    // Get default currency
                    $query = <<<EOF
					SELECT com.t_ccur
					FROM ttccom000$erp com
					WHERE com.t_ncmp=$erp
EOF;
                    $res = tldUtils::getSqlRowToAssocArray($query, "odbc", ["src" => "baan"]);
                    $cur = $res['t_ccur'];

                    $today = new DateTime();
                    $dateYM = [];
                    for ($i = 0; $i < 12; $i++) {
                        $today->modify('-1 month');
                        $year = $today->format('Y');
                        $month = $today->format('n');
                        $dateYM[$year][] = 't_aupp_' . $month;
                    }
                    foreach ($dateYM as $year => $sel) {
                        $dateYM[$year] = "select " . implode(" + ", $sel) . " as 'total' from ttdinv750" . $erp . " where t_item = RTRIM(ITM.t_item) and t_year = '" . $year . "'";
                    }
                    $dateYM = implode(" union all ", $dateYM);
                    // Get supplier items
                    $query = <<<EOF
					SELECT
						RTRIM(ITM.t_item) AS item,
						RTRIM(ITM.t_dsca) AS dsca,
						RTRIM(ITM.t_suno) AS suno,
						RTRIM(SUP.t_nama) AS nama,
						(SELECT SUM(total) FROM ($dateYM) x
                        ) as months12,
                        (select (t_aupp_1+t_aupp_2+t_aupp_3+t_aupp_4+t_aupp_5+t_aupp_6+t_aupp_7+t_aupp_8+t_aupp_9+t_aupp_10+t_aupp_11+t_aupp_12) 
                        from ttdinv750$erp where t_item=RTRIM(ITM.t_item) and t_year =year(dateadd(year, -1, getdate()))
                        ) as years1,
                        (select (t_aupp_1+t_aupp_2+t_aupp_3+t_aupp_4+t_aupp_5+t_aupp_6+t_aupp_7+t_aupp_8+t_aupp_9+t_aupp_10+t_aupp_11+t_aupp_12) 
                        from ttdinv750$erp where t_item=RTRIM(ITM.t_item) and t_year =year(dateadd(year, -2, getdate()))
                        ) as years2,                      
						ITM.t_prip AS prip,
						ITM.t_ccur AS ccur,
						ITM.t_cuni AS cuni,
						CASE
							WHEN ITM.t_ltdt='1753-01-01 00:00:00.000' OR ITM.t_ltdt='1900-01-01 00:00:00.000' THEN NULL
							ELSE ITM.t_ltdt
						END AS ltdt
					FROM ttiitm001$erp ITM
					LEFT JOIN ttccom020$erp SUP ON ITM.t_suno=SUP.t_suno
					WHERE ITM.t_suno='$suno'
EOF;
                    $sup_items = tldUtils::getSqlToAssocArray($query, "odbc", ["src" => "baan"]);
                    // Begin building master rows
                    $master = $conversion_rates = [];
                    foreach ($sup_items AS $item) {
                        $line = $item;
                        $min = [$item['prip']];
                        foreach ($bus AS $bu) {
                            $queryxerf = <<<EOF
                            SELECT TOP 1 AIC.t_aitc AS xerf
                            FROM ttiitm012$erp as AIC
                            LEFT JOIN ttccom020$erp AS SUP ON AIC.t_suno=SUP.t_suno
                            WHERE (AIC.t_citt='SUP' OR AIC.t_citt='MFG') AND UPPER(AIC.t_item) like UPPER('{$item['item']}%') AND SUP.t_suno ='$suno'
EOF;
                            $ret = tldUtils::getSqlRowToAssocArray($queryxerf, "odbc", ["src" => "baan"]);
                            $line['xref'] = $ret['xerf'];
                            $query = <<<EOF
							SELECT
								RTRIM(ITM.t_suno) AS suno_$bu,
								RTRIM(SUP.t_nama) AS nama_$bu,
								ITM.t_prip AS prip_$bu,
								ITM.t_ccur AS ccur_$bu,
								ITM.t_cuni AS cuni_$bu
							FROM ttiitm001$bu ITM
							LEFT JOIN ttccom020$bu SUP ON ITM.t_suno=SUP.t_suno
							WHERE RTRIM(ITM.t_item) LIKE '{$item['item']}'
EOF;
                            $bu_item = tldUtils::getSqlRowToAssocArray($query, "odbc", ["src" => "baan"]);
                            if (empty($bu_item)) continue;
                            if ($bu_item["ccur_$bu"] AND !isset($conversion_rates[$conversion_rates[$bu_item["ccur_$bu"]]])) {
                                $conversion_rates[$bu_item["ccur_$bu"]] = tldForex::getRate($cur, $bu_item["ccur_$bu"]);
                            }
                            $bu_item["converted_prip_$bu"] = $bu_item["prip_$bu"] * (1 / $conversion_rates[$bu_item["ccur_$bu"]]);
                            $line += $bu_item;

                            if ($bu_item["converted_prip_$bu"] > 0) {
                                $min[] = $bu_item["converted_prip_$bu"];
                            }
                        }
                        $min = min($min);
                        $line['ltdt'] = ($line['ltdt']) ? date('Y-m-d', strtotime($line['ltdt'])) : '';
                        $line += @[
                            'analysis_min' => $min,
                            'analysis_vol' => $item['prip'] - $min,
                            'analysis_pct' => ($item['prip'] - $min) / $item['prip']
                        ];
                        $master[] = $line;
                    }

                    // Filter rows
                    $rows = [];
                    foreach ($master AS $row) {
                        $pass_vol = ($row['analysis_vol'] >= $vars['trg_vol']) ? true : false;
                        $pass_pct = ($row['analysis_pct'] >= $vars['trg_pct']) ? true : false;
                        if ('OR' == $vars['trg_bol'] AND (!$pass_vol AND !$pass_pct)) {
                            continue;
                        }
                        if ('OR' != $vars['trg_bol'] AND !$pass_vol OR !$pass_pct) {
                            continue;
                        }
                        $rows[] = $row;
                    }

                    // Prepare report display
                    foreach ($rows AS &$row) {
                        $row['display_prip'] = number_format(round($row['prip'], 2), 2) . " {$row['ccur']} {$row['cuni']}";
                        $row['display_analysis_min'] = number_format(round($row['analysis_min'], 2), 2) . " $cur";
                        $row['display_analysis_vol'] = number_format(round($row['analysis_vol'], 2), 2);
                        $row['display_analysis_pct'] = round($row['analysis_pct'] * 100) . "%";
                        foreach ($bus AS $bu) {
                            if (!isset($row['suno_' . $bu])) continue;
                            $row["display_prip_$bu"] = number_format(round($row["converted_prip_$bu"], 2), 2) . " {$row['ccur']} {$row['cuni_'.$bu]}";
                            $supplier = [];
                            if (!empty($row["nama_$bu"])) $supplier[] = $row["nama_$bu"];
                            if (!empty($row["suno_$bu"])) $supplier[] = $row["suno_$bu"];
                            if (count($supplier) == 2) $supplier[1] = "({$supplier[1]})";
                            $row["supplier_$bu"] = implode(' ', $supplier);
                        }
                    }
                    $sess['BenchmarkSupplierData' . md5(serialize($vars))] = $rows;
                } else {
                    $rows = $sess['BenchmarkSupplierData' . md5(serialize($vars))];
                }

                if (!count($rows)) {
                    $DEFAULT_ERROR[] = "Supplier $suno in compay #$erp does not have any listed items";
                    break;
                }

                $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER["REQUEST_URI"]}&m[4]=xls">XLS version</a>&nbsp;|&nbsp;
<a href="{$_SERVER["REQUEST_URI"]}&m[4]=csv">CSV version</a>            
EOF;
                if ($m[4]) {
                    $xItems = [
                        'item' => 'Item',
                        'dsca' => 'Description',
                        'xref' => 'x-Ref',
                        'prip' => 'Price',
                        'cuni' => 'UM',
                        'ltdt' => 'Last Transaction Date',
                        'months12' => 'Passed 12 months',
                        'years1' => 'Year-1',
                        'years2' => 'Year-2'
                    ];
                    foreach ($bus AS $bu) {
                        $xItems["converted_prip_$bu"] = "$bu Price";
                        $xItems["cuni_$bu"] = "$bu UM";
                        $xItems["supplier_$bu"] = "$bu Main Supplier";
                    }
                    $xItems += [
                        'analysis_min' => 'MIN',
                        'analysis_vol' => 'Volume',
                        'analysis_pct' => 'Percent'
                    ];
                    switch ($m[4]) {
                        case 'xls':
                            $report = new tldXLS(
                                $rows,
                                [
                                    "xItems" => $xItems,
                                    "showTitles" => TRUE
                                ]
                            );
                            $report->out();
                            exit;
                            break;
                        case 'csv':
                            $report = new tldCSV(
                                $rows,
                                [
                                    "xItems" => $xItems,
                                    "showTitles" => TRUE
                                ]
                            );
                            $report->out();
                            exit;
                            break;
                    }
                }

                // Settings
                $xItems = [
                    'item' => 'Item',
                    'dsca' => 'Description',
                    'xref' => 'x-Ref',
                    'display_prip' => 'Price',
                    'ltdt' => 'Last Transaction Date',
                    'months12' => 'Passed 12 months',
                    'years1' => 'Year-1',
                    'years2' => 'Year-2'
                ];
                foreach ($bus AS $bu) {
                    $xItems["display_prip_$bu"] = 'Price';
                    $xItems["supplier_$bu"] = 'Main Supplier';
                }
                $xItems += [
                    'display_analysis_min' => 'MIN',
                    'display_analysis_vol' => 'Volume',
                    'display_analysis_pct' => 'Percent'
                ];
                $sortable = ['item', 'dsca', 'xref','display_prip', 'ltdt', 'months12', 'years1', 'years2', 'display_analysis_min', 'display_analysis_vol', 'display_analysis_pct'];
                $groupHeaders = [
                    [
                        'columns' => ['display_analysis_min', 'display_analysis_vol', 'display_analysis_pct'],
                        'title' => 'ANALYSIS'
                    ]
                ];
                $columnSettings = [
                    'item' => [
                        'cellAttributes' => 'style="text-align:left;font-weight:bold;"'
                    ],
                    'dsca' => [
                        'cellAttributes' => 'style="text-align:left;font-weight:bold;"'
                    ],
                    'xref' => [
                        'cellAttributes' => 'style="text-align:left;font-weight:bold;"'
                    ],
                    'display_prip' => [
                        'cellAttributes' => 'style="font-weight:bold;"'
                    ],
                    'ltdt' => [
                        'cellAttributes' => 'style="font-weight:bold"'
                    ],
                    'months12' => [
                        'cellAttributes' => 'style="font-weight:bold;"'
                    ],
                    'years1' => [
                        'cellAttributes' => 'style="font-weight:bold;"'
                    ],
                    'years2' => [
                        'cellAttributes' => 'style="font-weight:bold;;border-right:2px solid #999;"',
                        'thAttributes' => 'style="border-right:2px solid #ccc;"'
                    ],
                    'display_analysis_min' => [
                        'title' => 'Minimum price found from all selected BUs'
                    ],
                    'display_analysis_vol' => [
                        'title' => "Difference from base selected BU ($erp) from MIN shown in volume amount"
                    ],
                    'display_analysis_pct' => [
                        'title' => "Difference from base selected BU ($erp) from MIN shown in percent amount"
                    ]
                ];
                foreach ($bus AS $bu) {
                    $sortable[] = "display_prip_$bu";
                    $sortable[] = "supplier_$bu";
                    $groupHeaders[] = [
                        'columns' => ["display_prip_$bu", "supplier_$bu"],
                        'title' => "{$factories[$bu]} ($bu)"
                    ];
                    $columnSettings["supplier_$bu"] = [
                        'cellAttributes' => 'style="text-align:left;border-right:2px solid #999;"',
                        'thAttributes' => 'style="border-right:2px solid #ccc;"'
                    ];
                }
                $title = "Benchmark Supplier ERP#$erp {$rows[0]['nama']} ({$rows[0]['suno']})";
                if (!empty($vars['trg_vol']) OR !empty($vars['trg_pct'])) {
                    $title .= " Using Filter Triggers: Volume > {$vars['trg_vol']} {$vars['trg_bol']} Percent > " . ($vars['trg_pct'] * 100) . "%";
                }

                $smarty->assign("width", "100%");
                $form = new tldGanttChart('BenchmarkSupplier', $rows, $xItems, [
                    // Options
                    'title' => $title,
                    'parentAttributes' => [
                        'table' => 'border="0" cellspacing="0" cellpadding="8"'
                    ],
                    'sortable' => $sortable,
                    'groupHeaders' => $groupHeaders,
                    'columnSettings' => $columnSettings
                ]);
                $body .= $form->fetch();
                break;
            case 'step2':
                // Step 2 - Select suppier
                if (!$erp) {
                    $DEFAULT_ERROR[] = "Factory BU was not set";
                    break;
                }
                $form = new HTML_QuickForm('frmBenchmarkSupplier2', 'get', "", "", "", true);
				$form->addElement(	'hidden', 'm[0]', 'reports');
				$form->addElement(	'hidden', 'm[1]', 'benchmark');
				$form->addElement(	'hidden', 'm[2]', 'supplier');
				$form->addElement(	'hidden', 'm[3]', 'step2');
				$form->addElement(	'hidden', 'erp', $erp);
				$form->addElement(	'text', 'txt', "Search company $erp vendor name for...");
				$form->addElement(	'submit', 'btnFind', 'Continue...');
				if(!$form->validate()){
					$body .=<<<EOF
					<p>To use wildcards in your search, use % to represent multiple characters or _ for a single character. You
					may also search using the SUNO number as well.</p>
EOF;
					$body .= $form->toHTML();
					break;
				}
				$form->freeze();
				$params = $_GET;
				$params['m'][3] = 'step3';
				$erpOb = tldERP::getERPOb($erp);
				$rows = $erpOb->getSupplierData(TldDatabase::escape($txt), 'searchByID');
				if(empty($rows)){
					$txt = str_replace(array(TldDatabase::escape('%'),TldDatabase::escape('_')), array('%','_'), TldDatabase::escape($txt));
					$rows = $erpOb->getSupplierData($txt, "searchByName");
				}
				$report = new tldHTMLList(
					$rows,
					array(
						"key"=>array("sup"=>"t_suno"),
						"value"=>array("t_nama","t_suno")
					),
					"$php_self?".http_build_query($params),
					array("title"=>"Please select ERP $erp vendor to benchmark purchasing costs")
				);
				$body .= $report->fetch();
			break;
			default:
				// Step 1 - Select ERP
				$form = new HTML_QuickForm('frmBenchmarkSupplier1', 'get');
				$form->addElement(	'hidden', 'm[0]', 'reports');
				$form->addElement(	'hidden', 'm[1]', 'benchmark');
				$form->addElement(	'hidden', 'm[2]', 'supplier');
				$form->addElement(	'hidden', 'm[3]', 'step2');
				$form->addElement(	'header', 'title','Select factory:');
				$form->addElement(	'select', 	'erp',	'Factory BU',
					array(""=>"")+$factories);
				$form->addElement(	'submit', 'btnSubmit', 'Submit');
				$body .= $form->toHTML();
			break;
		}
	break;
}
