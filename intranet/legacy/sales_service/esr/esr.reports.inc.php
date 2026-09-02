<?php
$DEFAULT_TITLE .= "\Reports";

    switch($m[2]){
	case 'esrCostBySSOByYear':
		$ssoList = tldLocation::getSalesOrgList("smartyOptionsIDLocation");
		$form = new HTML_QuickForm('frmNew', 'post');
		$form->addElement(  'hidden', 'm[0]', 'esr');
		$form->addElement(  'hidden', 'm[1]', 'reports');
		$form->addElement(  'hidden', 'm[2]', 'esrCostBySSOByYear');
		$form->addElement(  'header', 'title', "ESR Cost By SSO By Year");;
		$form->addElement(  'select', 'sso_id', 	'SSO', ["" => ""] + $ssoList);
        $form->addElement(  'date',   	'year', 	'Year', ["format" => "Y", 'addEmptyOption' => FALSE, "minYear" => date("Y") - 5, "maxYear" => date("Y")]);
        $form->addElement(  'submit', 'btnSubmit', 'Submit');
		$form->addRule('sso_id','Required','required');
		$form->addRule('year','Required','required');

		$form->setDefaults(["year" => date("Y")]);
        if(!$form->validate()){
			$body = $form->toHTML();
			break;
		}
		$vars = tldUtils::cleanupFormInput($form->exportValues());
		$year = $vars['year']['Y'];
		$sso = $vars['sso_id'];
        $erp = tldLocation::getERPByID($sso);
		$data = tldESR::ESRCostBySSOByYear($sso, $year);
        $xItem =[
            "esr_id"=>'ESR#',
            "dt_shipped" => "ESR Ship Date",
            "quantity" => "Quantity",
            "model" => "Unit/Model",
            "factory"=>"Manufacturer Location",
            "country" => "Country delivery",
            "delivery_loc" => "City delivery",
            "esr_inco" => "Incoterm",
            "modality" => "Modality",
            "comp_email_fwd" => "Forwarder",
            "currency"=>"Currency",
            "total" => "Cost",
        ];
		if($data){
            $report = new tldReportColumnar(
                $data,
                [
                    "xItems" => $xItem,
                    "links" => [
                        "esr_id" => [
                            "url" => "/en/private/sales_service/sales.php?m[0]=esr&m[1]=view",
                            "params" => ["id" => "esr_id"]
                        ]
                    ],
                    "title" => "ESR Cost By SSO - $erp By Year - $year",
                    "sumTotalsArray"=> ["total_dcur" => "total"]
                ]
            );
            $body .= $report->fetch();
        }
        break;
    case 'byEstArrivalDate':
    	$ssoList = tldLocation::getSalesOrgList("smartyOptionsIDLocation");
    	$form = new HTML_QuickForm('frmNew', 'post');
    	$form->addElement(  'hidden', 'm[0]', 'esr');
    	$form->addElement(  'hidden', 'm[1]', 'reports');
    	$form->addElement(  'hidden', 'm[2]', 'byEstArrivalDate');
    	$form->addElement(  'header', 'title', "ESR By Estimated Arrival Date and SSO");;
    	$form->addElement(  'select', 'sso_id', 	'SSO',				array(""=>"")+$ssoList);
    	$form->addElement(  'text',   'start', 		'Start Date', 		array('class'=>'datepicker'));
    	$form->addElement(  'text',   'end', 		'End Date', 		array('class'=>'datepicker'));
    	$form->addElement(  'submit', 'btnSubmit', 'Submit');
    	$form->addRule('sso_id','Required','required');
    	$form->addRule('start','Required','required');
    	$form->addRule('end','Required','required');
    	$form->setDefaults(array("start"=>date("Y-m-d"),'end'=>date("Y-m-d")));
    	if(!$form->validate()){
    		$body = $form->toHTML();
    		break;
    	}
    	$vars = tldUtils::cleanupFormInput($form->exportValues());
    	$start = $vars['start'];
    	$end = $vars['end'];
    	$sso = $vars['sso_id'];
    	$data = tldESR::byEstArrivalDate($sso, $start, $end);
        $xItem = [
            "esr_id" => "ESR#",
            "esrl_id" => "ESR Lines#",
            "equip_sn" => "Equipment SN#",
            "model" => "Model",
            "sol_inco" => "SOL Incoterm",
            "esr_inco" => "ESR Incoterm",
            "sol_inco_loc" => "Inco Location",
            "delivery_loc" => "ER Delivery Location",
            "departure" => "Departure",
            "final_dest" => "Final Destination",
            "er_invoice_num" => "ERP Invoice Number",
            "er_dt_shipped" => "ER Shipped Date",
            "dt_pick_up" => "Est. pick up date",
            "dt_shipped" => "Vessel Loading date",
            "dt_estimated" => "Est. date of arrival",
            "dt_arrived" => "Actual date of arrival",
            "unit_gross_selling_price" => "Unit gross selling price",
            "currency" => "Currency"
        ];
        
        $solPricesAndCurrencies = [];
        foreach ($data as &$esrLine){
            if (empty($solId = $esrLine['sol_id'])){
                continue;
            }
            
            if (!array_key_exists($solId, $solPricesAndCurrencies)) {
                $sol = new tldSOL($solId, true);
                $solPricesAndCurrencies[$solId] = [
                    'price' => $sol->getSubtotals()['pris_tot_in_dcur'],
                    'currency' => $sol->getDCUR()
                ];
            }

            $esrLine['unit_gross_selling_price'] = $solPricesAndCurrencies[$solId]['price'];
            $esrLine['currency'] = $solPricesAndCurrencies[$solId]['currency'];
        }
        
        if($data){
    		$report = new tldReportColumnar(
    				$data,
    				array(
    						"xItems"=>$xItem,
    						"links"=>array(
    								"equip_sn"=>array(
    										"url"=>"/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view&id=",
    										"params"=>array("id"=>"erid")
    								),
    								"esr_id"=>array(
    										"url"=>"/en/private/sales_service/sales.php?m[0]=esr&m[1]=view",
    										"params"=>array("id"=>"esr_id")
    								)
    						),
    						"title"=>"ESR by Estimated Arrival Date - Period $start to $end - ".tldLocation::getLocationByID($sso)
    				)
    		);
    		$body.= $report->fetch();
    	}
    break;
	case 'byESRShipmentDate':
        $ssoList = tldLocation::getSalesOrgList("smartyOptionsIDLocation");
        $form = new HTML_QuickForm('frmNew', 'post');
        $form->addElement('hidden', 'm[0]', 'esr');
        $form->addElement('hidden', 'm[1]', 'reports');
        $form->addElement('hidden', 'm[2]', 'byESRShipmentDate');
        $form->addElement('header', 'title', "ESR By Shipment Date and SSO");;
        $form->addElement('select', 'sso_id', 'SSO', ["" => ""] + $ssoList);
        $form->addElement('text', 'start', 'Start Date', ['class' => 'datepicker']);
        $form->addElement('text', 'end', 'End Date', ['class' => 'datepicker']);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('sso_id', 'Required', 'required');
        $form->addRule('start', 'Required', 'required');
        $form->addRule('end', 'Required', 'required');
        $form->setDefaults(["start" => date("Y-m-d"), 'end' => date("Y-m-d")]);
        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }
        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $start = $vars['start'];
        $end = $vars['end'];
        $sso = $vars['sso_id'];
        $data = tldESR::byESRShipmentDate($sso, $start, $end);
        $xItem = [
            "esr_id" => "ESR#",
            "esrl_id" => "ESR Lines#",
            "equip_sn" => "Equipment SN#",
            "model" => "Model",
            "sol_inco" => "SOL Incoterm",
            "esr_inco" => "ESR Incoterm",
            "sol_inco_loc" => "Inco Location",
            "delivery_loc" => "ER Delivery Location",
            "final_dest" => "Final Destination",
            "er_invoice_num" => "ERP Invoice Number",
            "er_dt_shipped" => "ER Shipped Date",
            "dt_shipped" => "Vessel Loading date",
            "dt_estimated" => "Estimated date of arrival",
            "dt_arrived" => "Actual date of arrival"
        ];
        if ($data) {
            $report = new tldReportColumnar(
                $data,
                [
                    "xItems" => $xItem,
                    "links" => [
                        "equip_sn" => [
                            "url" => "/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view&id=",
                            "params" => ["id" => "erid"]
                        ],
                        "esr_id" => [
                            "url" => "/en/private/sales_service/sales.php?m[0]=esr&m[1]=view",
                            "params" => ["id" => "esr_id"]
                        ]
                    ],
                    "title" => "ESR by Shipment Date - Period $start to $end - " . tldLocation::getLocationByID($sso)
                ]
            );
            $body .= $report->fetch();
        }
        break;
        case 'bySSOStatus':
            $xItems = tldESR::getStatusList();
        $form = new tldMatrix(
            tldESR::countBySSOStatus(),
            "status", "location_sso", "num",
            "$php_self?m[0]=esr&m[1]=listing&m[2]=bySSOStatus",
            "ESR Count by Sales Org, Status",
			array(
				"xItems"=>$xItems,
				"style"=>array(
					"xItems"=>array()
						)
				)
        );
        $body.=$form->fetch();
    break;
    default:
    	$body = $smarty->fetch("$PATH/esr/reports/homepage.reports.tpl");
    break;
    }
switch($out){
    case 'xls':
        $report = new tldXLS(
            $sess['esr']['listing'],
            array(
                "xItems"=>$sess['esr']['xItems'],
                "showTitles"=>true
            )
        );
        $report->out();
        exit;
        break;
    default:
        if(count($data ?? [])<1){
            $DEFAULT_ERROR[] = "ERROR: No ESR found...";
            break;
        }
        $DEFAULT_MENU .=<<<EOF
<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=esr&m[1]=reports&out=xls">XLS version</a>
EOF;
        $sess['esr']['listing'] = $data;
        $sess['esr']['xItems'] = $xItem;

        break;
}

?>
