<?php
include_once('Image/Graph.php');
include_once("../kpi/kpi.common.inc.php");

$PATH .= "/reports";
$DEFAULT_TITLE .= "\Reports";

switch($m[1]){
case 'matrix':
    switch($m[2]){
        case 'dmsKPI':
            $statusList = [
                'EXPIRED' => 'EXPIRED',
                'REVISION' => 'REVISION',
                'APPROVAL' => 'APPROVAL',
            ];
            // Get form
            $form = new HTML_QuickForm('DMS', 'post');
            $form->addElement(      'hidden', 'm[0]', 'reports');
            $form->addElement(      'hidden', 'm[1]', 'matrix');
            $form->addElement(      'hidden', 'm[2]', 'dmsKPI');
            $form->addElement(      'header', 'title','Select status:');
            $form->addElement(      'select',       'status',    'Status#',
                array(""=>"")+$statusList);
            $form->addElement(      'submit', 'btnSubmit', 'Submit');
            $form->addRule('status', 'This is required', 'required');

            if(!$form->validate()){
                $body .= $form->toHTML();
                break;
            }

            $vars = tldUtils::cleanupFormInput($form->exportValues());
            $status = $vars['status'];
            $date = date('Y-m')."-01";
            $start = new DateTime($date);
            $start->sub(new DateInterval('P12M'));
            $ds = $start->format('Y-m-d');
            $de = date('Y-m-d');
            $data = array();
            // Get window
            $query = <<<EOF
SELECT nam_period AS xval
FROM fin_periods AS p
WHERE
    PERIOD_DIFF( DATE_FORMAT(CONCAT('$de','-01'),'%Y%m'), p.nam_period )>=0
    AND PERIOD_DIFF( DATE_FORMAT(CONCAT('$ds','-01'),'%Y%m'), p.nam_period )<=0
ORDER BY nam_period
EOF;
            $graph_period = tldUtils::getSqlToAssocArray($query);
            $factory = tldLocation::getLocationByID($buid);
            // Foreach months, calculate KPI
            foreach($graph_period as $period){
                $period_Ym = $period['xval'];
                $period_Ymd = substr($period['xval'],0,4)."-".substr($period['xval'],-2,2)."-01";
                $date = new DateTime($period_Ymd);
                $period = $date->format('Y-m-d');
                $query=<<<EOF

SELECT count(*) as val, people.bu_id AS zval
FROM (

SELECT *
FROM (

SELECT *
FROM mod_logs
WHERE module = 'DMS'
ORDER BY `date` DESC
) AS a
GROUP BY `parent_id`
ORDER BY `date` DESC
) AS b
LEFT JOIN dms AS c ON b.parent_id = c.id
LEFT JOIN people ON people.id=c.owner_id
WHERE (c.status LIKE '$status'
AND DATEDIFF(LAST_DAY('$period'),b.date)>=0) AND DATEDIFF(LAST_DAY('$period'),DATE_ADD(c.dt_act, INTERVAL c.periodicity MONTH))>=0
GROUP BY people.bu_id
EOF;
                $rows = tldUtils::getSqlToAssocArray($query);
                foreach($rows as $k=>$v){
                    $data[$k][$period]['xval'] = substr($period, 0, -3);
                    $data[$k][$period]['yval'] = $v['val'];
                    $data[$k][$period]['zval'] = $v['zval'];
                }

            }
            $k = 0;
            foreach ($data as $key => $val) {
                foreach ($val as $key2 => $val2) {
                    $newhello[$k]['xval'] = $val2['xval'];
                    $newhello[$k]['yval'] = $val2['yval'];
                    $newhello[$k]['zval'] = tldLocation::getLocationByID($val2['zval']);
                    $k++;
                }
            }

            $status = strtolower($status);

            $form = new tldMatrix(
                $newhello,
                "xval", "zval", "yval",
                "$php_self?m[0]=reports&m[1]=listing&m[2]=dmsStatusByBUByMonth&status=".$status,
                "$status DMS, ALL Locations"
            );
            $body .= $form->fetch();
            break;
        case 'dmsAVGKPI':
            break;
    }
break;
case 'listing':
    switch($m[2]){
        case 'dmsStatusByBUByMonth':

        $status = strtoupper(TldDatabase::escape($status));

        $query=<<<EOF

SELECT c.*, CONCAT(people.lastname,', ',people.firstname) AS ownername,locations.location
FROM (

SELECT *
FROM (

SELECT *
FROM mod_logs
WHERE module = 'DMS'
ORDER BY `date` DESC
) AS a
GROUP BY `parent_id`
ORDER BY `date` DESC
) AS b
LEFT JOIN dms AS c ON b.parent_id = c.id
LEFT JOIN people ON people.id=c.owner_id
LEFT JOIN locations ON locations.id=people.bu_id
WHERE (c.status LIKE '$status'
AND DATEDIFF(LAST_DAY('$x-01'),b.date)>=0) 
AND DATEDIFF(LAST_DAY('$x-01'),DATE_ADD(c.dt_act, INTERVAL c.periodicity MONTH))>=0
AND locations.location LIKE '$y'
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);
        $xItems = array(
            "id"=>"DMS#",
            "dt_act"=>"Active Date",
            "ownername"=>"Owner",
            "location"=>"Location",
            "title" =>"Title",
            "subject"=>"Subject",
            "description"=>"Description",
            "status"=>"Status",
            "sysref" =>"System Reference",
        );
        $caption = $status." DMS for company {$y} in {$x}";
        $links=array("id"=>"$DMS_URL/index.php?m[0]=view&id=");

        break;
        case 'dmsStatusByBUForSalesMaterial':
            $query = <<<EOF
SELECT c.*, CONCAT(people.lastname,', ',people.firstname) AS ownername,locations.location
FROM (
    SELECT *
    FROM (
        SELECT *
        FROM mod_logs
        WHERE module = 'DMS'
        ORDER BY `date` DESC
    ) AS a
    GROUP BY `parent_id`
    ORDER BY `date` DESC
) AS b
LEFT JOIN dms AS c ON b.parent_id = c.id
LEFT JOIN people ON people.id=c.owner_id
LEFT JOIN locations ON locations.id=people.bu_id
WHERE c.status LIKE '$x'
AND locations.location LIKE '$y'
AND c.type_id = 9
EOF;

            $rows = tldUtils::getSqlToAssocArray($query);
            $xItems = [
                'id' => 'DMS#',
                'dt_act' => 'Active Date',
                'ownername' => 'Owner',
                'location' => 'Location',
                'title' =>"Title',
                'subject' => 'Subject',
                'description' => 'Description",
            ];
            $caption = "DMS for company $y in $x";
            $links= ['id' => "$DMS_URL/index.php?m[0]=view&id="];

        case 'dmsKPI':
//              $erpList = tldLocation::getERPList("smartyOptions");
                $buList = tldLocation::getBuListAsIdLocation();
                // Get form
                $form = new HTML_QuickForm('DMS', 'post');
                $form->addElement(      'hidden', 'm[0]', 'reports');
                $form->addElement(      'hidden', 'm[1]', 'listing');
                $form->addElement(      'hidden', 'm[2]', 'dmsKPI');
//              $form->addElement(      'hidden', 'm[3]', 'byBUID');
                $form->addElement(      'header', 'title','Select company:');
                $form->addElement(      'select',       'z',    'Company#',
                                array(""=>"")+$buList);
                $form->addElement(      'submit', 'btnSubmit', 'Submit');
                $form->addRule('z', 'This is required', 'required');

            if(!$form->validate()){
        $body .= $form->toHTML();
        break;
            }

            $vars = tldUtils::cleanupFormInput($form->exportValues());
            $buid = $vars['z'];
            $GraphURL = "/en/private/manufacturing/kpi/graphs.php?m[0]=past12&m[1]=dmsKPI&buid=$buid&m[2]=";

        $body.= _getKPIgraph(
                $GraphURL."Expired",
                "DMS Monthly expired past 12 month",
                $help["Monthly expired past 12 month target"]
        );
        break;
        case 'dmsAVGKPI':
//              $erpList = tldLocation::getERPList("smartyOptions");
                $buList = tldLocation::getBuListAsIdBU();
                // Get form
                $form = new HTML_QuickForm('DMS', 'post');
                $form->addElement(      'hidden', 'm[0]', 'reports');
                $form->addElement(      'hidden', 'm[1]', 'listing');
                $form->addElement(      'hidden', 'm[2]', 'dmsAVGKPI');
                //              $form->addElement(      'hidden', 'm[3]', 'byBUID');
                $form->addElement(      'header', 'title','Select company:');
                $form->addElement(      'select',       'z',    'Company#',
                                array(""=>"")+$buList);
                $form->addElement(      'submit', 'btnSubmit', 'Submit');
                $form->addRule('z', 'This is required', 'required');

                if(!$form->validate()){
                        $body .= $form->toHTML();
                        break;
                }

                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $buid = $vars['z'];
                $GraphURL = "/en/private/manufacturing/kpi/graphs.php?m[0]=past12&m[1]=dmsAVGKPI&buid=$buid&m[2]=";

                $body.= _getKPIgraph(
                                $GraphURL."Revision",
                                "DMS Monthly average days in revision past 12 month",
                                $help["Monthly average time in revision past 12 month target"]
                );
    break;
	case 'Receipt_Approvals_todo':
        $xItems = [
            "t_orno" => "Order",
            "t_pono" => "Position",
            "t_srnb" => "Sequence",
            //"t_spur"=>"PO line status",
            //"t_spu2"=>"Description",
            "t_item" => "Item",
            "t_dsca" => "Description",
            "t_rev1" => "PO revision",
            "t_rev2" => "Item revision",
            "t_prev" => "Last purchase revision",
            //"t_reno"=>"Receipt Number",
            //"t_dino"=>"Packing Slip Number",
            "t_date" => "Receipt Date",
            "t_dqua" => "Delivered Qty",
            "t_bqua" => "Back order Qty",
            //"t_quap"=>"Approved Qty",
            //"t_quad"=>"Rejected Qty",
            //"t_cdis"=>"Reason for Rejection",
            "t_cwar" => "Default warehouse",
            "t_loca" => "Default location",
            "t_requ" => "Requirement (Order # Qty # Requirement Date # Status)",
            //"t_inbd"=>"Inbounded?",
            //"t_acti"=>"Action"
            "t_ccon" => "Buyer",
            "t_namb" => "Name",
            "t_txta" => "PO line text",
            "t_refa" => "RefA",
            "t_refb" => "RefB",
            "t_suno" => "Supplier",
            "t_nama" => "Name",
            "t_txt" => "Inspection type",
        ];

        // Get listing
		$erpList = tldLocation::getERPList("smartyOptions");

		// Get form
		$form = new HTML_QuickForm('frmReceipt_Approvals_todo', 'get', "", "", "", true);
		$form->addElement(	'hidden', 'm[0]', 'reports');
		$form->addElement(	'hidden', 'm[1]', 'listing');
		$form->addElement(	'hidden', 'm[2]', 'Receipt_Approvals_todo');
		$form->addElement(	'header', 'title','Select company:');
		$form->addElement(	'select', 	'z',	'Company#',
			array(""=>"")+$erpList);
		$form->addElement(	'submit', 'btnSubmit', 'Submit');
		$form->addRule('z', 'This is required', 'required');

		if($form->validate()){
			$vars = tldUtils::cleanupFormInput($form->exportValues());

			$reviDate = new DateTime(date('Y-m-d'));
			$reviDate2 = $reviDate->format('Y-m-d');

            $query = "select ";
            $query .= "PUR045.t_orno, ";
            $query .= "PUR045.t_pono, ";
            $query .= "PUR045.t_srnb, ";
            $query .= "PUR045.t_suno, ";
            $query .= "(select COM020.t_nama from ttccom020{$vars['z']} COM020 where COM020.t_suno=PUR045.t_suno) t_nama, ";
            $query .= "PUR045.t_item, ";
            $query.="(select ITM001.t_txtp from ttiitm001{$vars['z']} ITM001 where ITM001.t_item=PUR045.t_item) t_txtp, ";
            $query .= "(select ITM001.t_dsca from ttiitm001{$vars['z']} ITM001 where ITM001.t_item=PUR045.t_item) t_dsca, ";
            $query .= "(select PUR041.t_revi from ttdpur041{$vars['z']} PUR041 where PUR041.t_orno=PUR045.t_orno and PUR041.t_pono=PUR045.t_pono) t_rev1, ";
            $query .= "(select max(EDM100.t_revi) from ttiedm100400 EDM100 where EDM100.t_eitm=PUR045.t_item and EDM100.t_indt<='" . $reviDate2 . "' and (EDM100.t_exdt>='" . $reviDate2 . "' or EDM100.t_exdt='01/01/1753')) t_rev2, ";
            $query .= "(select ITM001.t_cwar from ttiitm001{$vars['z']} ITM001 where ITM001.t_item=PUR045.t_item) t_cwar, ";
            $query .= "   (select min(ILC007.t_loca) from ttdilc007{$vars['z']} ILC007 where ";
            $query .= "      ILC007.t_item=PUR045.t_item and ";
            $query .= "      ILC007.t_cwar=(select ITM001.t_cwar from ttiitm001{$vars['z']} ITM001 where ITM001.t_item=PUR045.t_item) and ";
            $query .= "      ILC007.t_prio=";
            $query .= "         (select min(ILC007.t_prio) from ttdilc007{$vars['z']} ILC007 where ";
            $query .= "            ILC007.t_item=PUR045.t_item and ";
            $query .= "            ILC007.t_cwar=(select ITM001.t_cwar from ttiitm001{$vars['z']} ITM001 where ITM001.t_item=PUR045.t_item) ";
            $query .= "   )) t_loca, ";
            $query .= "PUR045.t_reno, ";
            $query .= "PUR045.t_dino, ";
            $query .= "SUBSTRING(convert(varchar, t_date, 120), 0, 11) AS t_date, ";
            $query .= "PUR045.t_dqua, ";
            $query .= "PUR045.t_bqua, ";
            $query .= "PUR045.t_quap, ";
            $query .= "PUR045.t_quad, ";
            $query .= "PUR045.t_cdis, ";
            $query .= "PUR045.t_spur, ";
            $query .= "(CASE ";
            $query .= "   WHEN PUR045.t_spur=1 THEN 'Print Purchase Orders' ";
            $query .= "   WHEN PUR045.t_spur=2 THEN 'Print Goods Received Notes' ";
            $query .= "   WHEN PUR045.t_spur=3 THEN 'Maintain Receipts' ";
            $query .= "   WHEN PUR045.t_spur=4 THEN 'Print Claims' ";
            $query .= "   WHEN PUR045.t_spur=5 THEN 'Maintain Approvals' ";
            $query .= "   WHEN PUR045.t_spur=6 THEN 'Print Storage Lists' ";
            $query .= "   WHEN PUR045.t_spur=7 THEN 'Print Return Notes' ";
            $query .= "   WHEN PUR045.t_spur=8 THEN 'Print Purchase Invoices' ";
            $query .= "   WHEN PUR045.t_spur=9 THEN 'Process Delivered Purchase Order' ELSE 'N/A' END ";
            $query .= ") as t_spu2, ";
            $query .= "(CASE ";
            $query .= "   WHEN (select count(*) from ttdilc111{$vars['z']} ILC111 where ILC111.t_orno=PUR045.t_orno and ILC111.t_pono=PUR045.t_pono)>0 THEN 'No' ELSE 'Yes' ";
            $query .= "END) as t_inbd, ";
            $query .= "(select PUR040.t_ccon from ttdpur040{$vars['z']} PUR040 where PUR040.t_orno=PUR045.t_orno) t_ccon, ";
            $query .= "(select PUR040.t_refa from ttdpur040{$vars['z']} PUR040 where PUR040.t_orno=PUR045.t_orno) t_refa, ";
            $query .= "(select PUR040.t_refb from ttdpur040{$vars['z']} PUR040 where PUR040.t_orno=PUR045.t_orno) t_refb, ";
            $query .= "(select COM001.t_nama from ttccom001{$vars['z']} COM001 where COM001.t_emno= (select PUR040.t_ccon from ttdpur040{$vars['z']} PUR040 where PUR040.t_orno=PUR045.t_orno)) t_namb, ";
            $query .= "(select CAST(t_text AS varchar(240)) from ttttxt010{$vars['z']} where t_ctxt=(select t_txta from ttdpur041{$vars['z']} PUR041 where PUR041.t_orno=PUR045.t_orno and PUR041.t_pono=PUR045.t_pono)) t_txta ";
            $query .= "from ttdpur045{$vars['z']} PUR045 where ";
            $query .= "PUR045.t_spur=5 and ";
            $query .= "(select count(*) from ttdpur041{$vars['z']} PUR041 where PUR041.t_orno=PUR045.t_orno and PUR041.t_pono=PUR045.t_pono and PUR041.t_qual=1)>0 ";

            $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);

            // Add requirement
            foreach ($rows as $key1 => $value1) {
                $tmp_item = $rows[$key1]['t_item'];
                $tmp_pono = $rows[$key1]['t_refa'];
                $tmp_order = $rows[$key1]['t_refb'];
                $tmp_sup = $rows[$key1]['t_suno'];
                $tmp_repd = $rows[$key1]['t_date'];
                if($vars['z']==620){
                    $query7 = "SELECT TOP 1 COM010.t_iscn  AS t_comp FROM  ttdsls040{$vars['z']} AS T1 LEFT JOIN  ttccom010{$vars['z']} COM010 ON COM010.t_cuno=T1.t_cuno WHERE T1.t_orno='". $tmp_order ."'  ORDER BY T1.t_orno DESC ";
                    $rows7 = tldUtils::getSqlRowToAssocArray($query7, "odbc", ["src" => "baan"]);
                    $comp = $rows7['t_comp'];
                    if ($comp){
                        $query8 = "SELECT SUBSTRING(CONVERT(VARCHAR, CASE WHEN(POL.t_ddtc <> '1753-01-01 00:00:00.000')
                                    THEN POL.t_ddtc
                                    ELSE POL.t_ddta END, 120), 0 , 11) AS t_ddta 
                                    FROM ttdpur041$comp AS POL 
                                    WHERE POL.t_orno='". $tmp_pono ."' AND POL.t_item ='". $tmp_item ."' ";
                        $rows8 = tldUtils::getSqlRowToAssocArray($query8, "odbc", ["src" => "baan"]);
                        $rows[$key1]['t_ddta'] = $rows8['t_ddta'];
                        $xItems += ["t_ddta"=>"Date of part arrived to sister plant"];
                    }
                }
                $query6 = "SELECT TOP 1 T1.t_revi FROM  ttdpur041{$vars['z']} AS T1,ttdpur045{$vars['z']} AS T2 WHERE T1.t_orno=T2.t_orno AND T1.t_pono=T2.t_pono AND T2.t_suno='" . $tmp_sup . "' AND T1.t_item='" . $tmp_item . "' AND T2.t_date< '" . $tmp_repd . "' ORDER BY T2.t_date DESC ";
                $rows6 = tldUtils::getSqlRowToAssocArray($query6, "odbc", ["src" => "baan"]);
                $rows[$key1]['t_prev'] = $rows6['t_revi'];
                $query2 = "select INV150.t_orno, INV150.t_pono, INV150.t_ponb, INV150.t_date, INV150.t_koor, INV150.t_qana from ttdinv150{$vars['z']} INV150 where INV150.t_kotr=2 and INV150.t_koor in(1,3) and  INV150.t_item='" . $tmp_item . "'";
                $rows2 = tldUtils::getSqlToAssocArray($query2, "odbc", ["src" => "baan"]);
                $requirement = "";
                foreach ($rows2 as $key2 => $value2) {
					// get production order status
					if ($rows2[$key2]['t_koor']==1) {
						$query3="select CASE ";
						$query3.="   WHEN SFC001.t_osta=1 THEN 'Free' ";
						$query3.="   WHEN SFC001.t_osta=2 THEN 'Planned' ";
						$query3.="   WHEN SFC001.t_osta=3 THEN 'Printed' ";
						$query3.="   WHEN SFC001.t_osta=4 THEN 'Released' ";
						$query3.="   WHEN SFC001.t_osta=5 THEN 'Active' ";
						$query3.="   WHEN SFC001.t_osta=6 THEN 'Completed' ELSE 'N/A' ";
						$query3.="END as PDNO_STATUS ";
						$query3.="from ttisfc001{$vars['z']} SFC001 where SFC001.t_pdno='".$rows2[$key2]['t_orno']."'";
						$rows3 = tldUtils::getSqlRowToAssocArray($query3, "odbc", array("src"=>"baan"));
					};

					// get sales order status
					if ($rows2[$key2]['t_koor']==3) {
						$query4="select CASE ";
						$query4.="   WHEN min(SLS045.t_ssls)=1 THEN 'Print Order Acknowledgements' ";
						$query4.="   WHEN min(SLS045.t_ssls)=2 THEN 'Print Picking Lists' ";
						$query4.="   WHEN min(SLS045.t_ssls)=3 THEN 'Maintain Deliveries' ";
						$query4.="   WHEN min(SLS045.t_ssls)=4 THEN 'Print Packing Slips' ";
						$query4.="   WHEN min(SLS045.t_ssls)=5 THEN 'Print Bills of Lading' ";
						$query4.="   WHEN min(SLS045.t_ssls)=6 THEN 'Print Sales Invoices' ";
						$query4.="   WHEN min(SLS045.t_ssls)=7 THEN 'Process Delivered Sales Order' ";
						$query4.="   WHEN min(SLS045.t_ssls)=8 THEN 'Generate Outbound Advice' ";
						$query4.="   WHEN min(SLS045.t_ssls)=9 THEN 'Print Proforma Invoices' ";
						$query4.="   WHEN min(SLS045.t_ssls)=10 THEN 'Link Advance Receipts to Pro' ELSE 'N/A' ";
						$query4.="END as SLNO_STATUS ";
						$query4.="from ttdsls045{$vars['z']} SLS045 where SLS045.t_orno='".$rows2[$key2]['t_orno']."' and SLS045.t_pono='".$rows2[$key2]['t_pono']."'" ;
						$rows4 = tldUtils::getSqlRowToAssocArray($query4, 'odbc', ['src' => 'baan']);
					};

					$date_tmp=substr($rows2[$key2]['t_date'],0,10);
					if ($rows2[$key2]['t_koor']==1) { $requirement.="<table border=0><tr><td nowrap>".$rows2[$key2]['t_orno']."(WO) # ".$rows2[$key2]['t_qana']." # ".$date_tmp." # ".$rows3['PDNO_STATUS']."</td></tr></table>"; }
					if ($rows2[$key2]['t_koor']==3) { $requirement.="<table border=0><tr><td nowrap>".$rows2[$key2]['t_orno']."(SO) # ".$rows2[$key2]['t_qana']." # ".$date_tmp." # ".$rows4['SLNO_STATUS']."</td></tr></table>"; }
				}
					$rows[$key1]['t_requ']=$requirement;
					if ($rows[$key1]['t_spur']==5) { $rows[$key1]['t_acti']="TO INSPECT"; }
					if ($rows[$key1]['t_spur']>5) { $rows[$key1]['t_acti']="INSPECTED"; }
                    if ($rows[$key1]['t_txtp'] !== 0) {
                        $texta = tldBaanERP::getTXTA($rows[$key1]['t_txtp']);
                        $rows[$key1]['t_txt'] = $texta['t_text'];
                    }
			}
            $caption = "Reicept Approvals to do for company {$vars['z']}";
            $links=array("t_item"=>"/en/private/manufacturing/eng/dev.php?m[0]=getfile&m[1]=drawing&erp={$vars['z']}&date={$reviDate2}&item=");


		}//else{
			$body = $form->toHTML();
		//}
	break;
        case 'ERWithMissingEngineInformation':
            // Get form
            $form = new HTML_QuickForm('frmERWithMissingEngineInformation', 'get', "", "", "", true);
            $form->addElement('hidden', 'm[0]', 'reports');
            $form->addElement('hidden', 'm[1]', 'listing');
            $form->addElement('hidden', 'm[2]', 'ERWithMissingEngineInformation');
            $form->addElement('header', 'title', 'Select company:');
            $form->addElement('select', 'man_location', 'Factory', ["" => ""] + tldEquipment::getUsedFactoryList());
            $form->addElement('submit', 'btnSubmit', 'Submit');
            $form->addRule('factory', 'This is required', 'required');

            if (!$form->validate()) {
                $body = $form->toHTML();
                break;
            }
            $rawVars = $form->exportValues();
            $query = <<<EOF
SELECT ers.*,
    engs.serial AS eng_sn,
    dpfs.serial AS dpf_sn,
    scrs.serial AS scr_sn
FROM service as ers
    LEFT JOIN service_serials AS engs ON ers.id=engs.parent_id AND engs.component='ENGINE'
    LEFT JOIN service_serials AS dpfs ON ers.id=dpfs.parent_id AND dpfs.component='ENGINE, DPF'
    LEFT JOIN service_serials AS scrs ON ers.id=scrs.parent_id AND scrs.component='ENGINE, SCR'
HAVING
    ers.date_entered>'2010-11-01'
    AND ers.man_location = "{$rawVars['man_location']}"
    AND (eng_sn IS NULL
        OR (
            (
                ers.eng_tier='Tier 4F/COMIV'
                AND (
                    dpf_sn IS NULL
                    OR scr_sn IS NULL
                )
            ) OR (
                ers.eng_tier='Tier 4i/COMIIIB'
                AND (
                    dpf_sn IS NULL
                    OR scr_sn IS NULL
                )
            )
        )
    )
EOF;
            $rows = tldUtils::getSqlToAssocArray($query);
            $report = new tldReportColumnar(
                $rows,
                [
                    "xItems" => [
                        "id" => "ID#",
                        "sn" => "SN#",
                        "customer_name" => "Customer",
                        "model" => "Model",
                        "eng_tier" => "Emission Rating",
                        "eng_sn" => "Engine SN",
                        "dpf_sn" => "DPF SN",
                        "scr_sn" => "SCR SN",
                    ],
                    "title" => "Equipment with missing engine information for {$rawVars['man_location']}",
                    "links" => [
                        "id" => "/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view&id=",
                    ],
                ]
            );
            $body .= $report->fetch();
            break;
	}

	if(isset($rows,$caption)){
		$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER["REQUEST_URI"]}&m[3]=xls">XLS version</a>&nbsp;|&nbsp;
<a href="{$_SERVER["REQUEST_URI"]}&m[3]=csv">CSV version</a>
EOF;
        switch($m[3]){
        case 'xls':
            foreach ($rows as &$row) {
                $row['t_requ'] = strip_tags($row['t_requ']);
            }
            $report = new tldXLS(
            	$rows,
            	array(
            		"xItems"=>$xItems,
            		"showTitles"=>TRUE
            	)
            );
    		$report->out();
    		exit;
        break;
        case 'csv':
            $report = new tldCSV(
            	$rows,
            	array(
            		"xItems"=>$xItems,
            		"showTitles"=>TRUE
            	)
            );
    		$report->out();
    		exit;
        break;
        default:

			$report = new tldReportColumnar($rows,
				array(
					"xItems"=>$xItems,
					"title"=>$caption,
					"links"=>$links
				)

			);
			$body .= $report->fetch();

		break;
        }
	}
break;
default:
	$body = $smarty->fetch("$PATH/homepage.reports.tpl");
break;
}
function _getKPIgraph($GraphURL, $_TITLE, $groupTarget){
	global $help;
	$body=<<<EOF
<br><br><img src="$GraphURL"><br/>
EOF;
	$groupTargetText="<b>Group Target:</b> ".$groupTarget;
	$popupDef = new tldOverlib(
			$help[$_TITLE].$groupTargetText,
			array(
					"CAPTION"=>$_TITLE,
					"WIDTH"=>"500",
					"linkName"=>$_TITLE
			)
	);
	$body.=$popupDef->fetch();
	return $body;
}
?>
