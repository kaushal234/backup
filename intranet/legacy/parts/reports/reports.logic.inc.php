<?php

include_once('erp.inc.php');
$DEFAULT_TITLE .= "\Spare Parts Reports";
$overlib = $smarty->fetch('overlib.inc.js.tpl');
$overlib .= '<script type="text/javascript">$(function(){$(".overlib").overlib()});</script>';
$autosuggest = $smarty->fetch('autosuggest.inc.js.tpl');
$autosuggest .= '<script type="text/javascript">$(function(){var uci=$("[name=cust]").autosuggest({message:"Begin to type a portion of the name for auto suggestions",
onChange:function(o){$(uci.input.display).val($(o.input.display).val());
$(uci.input.value).val($(o.input.value).val());}});});</script>';
$smarty->assign('html_head', $overlib . $autosuggest);
if (!$user->isInGroup(['gg_ADMIN', 'gg_PARTS', 'role_MLM', 'gg_PUR', 'role_FC'])) {
	$DEFAULT_ERROR[] = 'ERROR: You do not have permissions';
	return;
}

switch ($m[1]) {
	case 'edit':
		switch ($m[2]) {
			case 'update':
				foreach ($_POST['vat'] as $vat_id => $data) {

					// Secure data
					$data = tldUtils::cleanupFormInput($data);
					// Update the part
					// Get actual header

					$fields = ['id', 'tobeinvoice', 'comment'];
					$e = tldVAT::insertComments($data, $fields);
					if (!is_string($e)) {
						$body = '<br/>Part(s) updated successfully !';
					} else {
						$DEFAULT_ERROR[] = "ERROR: Problem updating...<br/>Reason: $e";
					}
				}
				break;
		}
		break;
	case 'listing':
		switch ($m[2]) {
			case 'VMPOrder':
				$xItems = [
					't_cuno' => 'Cust_Code',
					't_nama' => 'Customer Name',
					't_orno' => 'SO',
					't_pono' => 'Line',
					't_odat' => 'Created',
					't_item' => 'PN',
					't_dsca' => 'Description',
					't_buyr' => 'Buyer',
					'purs_suno' => 'Supplier#',
					'sup_name' => 'Supplier Name',
					't_pics' => 'Floor Stock',
					't_csig' => 'Signal Code',
				];
				// Get listing
				$erpList = tldLocation::getERPList('smartyOptions');
				// Get form
				$form = new HTML_QuickForm('frmVMPOrder', 'get', '', '', '', true);
				$form->addElement('hidden', 'm[0]', 'reports');
				$form->addElement('hidden', 'm[1]', 'listing');
				$form->addElement('hidden', 'm[2]', 'VMPOrder');
				$form->addElement('header', 'title', 'Select company for VPM parts:');
				$form->addElement('text', 'start', 'Date from', ['class' => 'datepicker']);
				$form->addElement('text', 'end', 'Date to', ['class' => 'datepicker']);
				$form->addElement('select', 'z', 'Company#',
					['' => ''] + $erpList);
				$form->addElement('submit', 'btnSubmit', 'Submit');
				$form->addRule('z', 'This is required', 'required');
				$form->setDefaults([
						'start' => date('Y') . '-01-01',
						'end' => date('Y-m-d')]
				);

				if (!$form->validate()) {
					$body = $form->toHTML();
					break;
				}

				# If the form validates then freeze the data
				$form->freeze();
				$titlevmp = 'VPM parts list ';
				$erp = TldDatabase::escape($z);
				$start = TldDatabase::escape($start);
				$end = TldDatabase::escape($end);
				if (!empty($start)) {
					$WHERE .= " sors.t_odat >= '$start' ";
					$titlevmp .= "Period > $start ";
				}
				if (!empty($end)) {
					$WHERE .= "  AND sors.t_odat<= '$end' ";
					$titlevmp .= "Period < $end ";
				}
				$WHERE .= " AND itms.t_csig LIKE '%VMP%' ";
				$rows = tldSO::byConstraints($erp, $WHERE, $opt);
				$caption = "VMP parts for company $erp " . $titlevmp;
				break;
			case 'SoBacklog':
				$xItems = [
					't_cuno' => 'Customer',
					't_odat' => 'Order date',
					't_orno' => 'Order',
					't_pono' => 'Position',
					't_eono' => 'Customer order',
					't_epos' => 'Position',
					't_refa' => 'Ref A',
					't_refb' => 'Ref B',
					't_item' => 'Item',
					't_dsca' => 'Description',
					't_oqua' => 'Order qty',
					't_dqua' => 'Ship qty',
					't_rasr' => 'Back Order',
					't_ddta_delay' => 'Delay',
					't_ddta' => 'Planned Ship Date',
					't_amta' => 'amount',
					't_ssls' => 'Line statut',
					't_csel' => 'Select. Code',
					't_scom' => 'Ship Complete',
					'e_dsca' => 'Terms payment',
					't_crep' => 'Vendor',
					't_copr' => 'Std cost price',
					't_oltm' => 'Order lead time',
					't_AAAA' => 'PRS/0.95',
					't_BBBB' => 'Order lead time/5x7 (Delay)',
					't_CCCC' => 'Today + Delay',
					't_dscc' => 'PIPO (Item size)',
					't_oqmf' => 'Order Quantity Multiple of',
					't_mioq' => 'Minimum Order Quantity',
					't_buyr' => 'Buyer',
					't_nam2' => 'Buyer name 2',
					't_cplb' => 'Planner',
					't_nam3' => 'Planner name 2',
					't_suno' => 'Supplier',
					't_cwar' => 'Warehouse',
					't_odat' => 'Order date',
					't_stoc' => 'Inventory on hand',
					't_ordr' => 'Inventory on order',
					't_allo' => 'Allocated inventory',
				];
				// Get listing
				$erpList = tldLocation::getERPList('smartyOptions');
				// Get form
				$form = new HTML_QuickForm('frmSoBacklog', 'get', '', '', '', true);
				$form->addElement('hidden', 'm[0]', 'reports');
				$form->addElement('hidden', 'm[1]', 'listing');
				$form->addElement('hidden', 'm[2]', 'SoBacklog');
				$form->addElement('header', 'title', 'Select SO# and company:');
				$form->addElement('text', 'so_from', 'SO from');
				$form->addElement('text', 'so_to', 'SO to');
				$form->addElement('select', 'z', 'Company#',
					['' => ''] + $erpList);
				$form->addElement('submit', 'btnSubmit', 'Submit');
				$form->addRule('so_from', 'This is required', 'required');
				$form->addRule('so_to', 'This is required', 'required');
				$form->addRule('z', 'This is required', 'required');
				$form->setDefaults(['y' => date('Y-m-d')]);

				if ($form->validate()) {
					$vars = tldUtils::cleanupFormInput($form->exportValues());

					$query = 'select ';
					$query .= 'SLS041.t_cuno, ';
					$query .= 'SLS041.t_orno, ';
					$query .= 'SLS041.t_pono, ';
					$query .= 'SLS041.t_item, ';
					$query .= 'SLS041.t_oqua, ';
					$query .= 'SLS041.t_dqua, ';
					$query .= '(SLS041.t_oqua-SLS041.t_dqua) t_rasr, ';
					$query .= 'SUBSTRING(convert(varchar, SLS041.t_ddta, 120), 0, 11) AS t_ddta, ';
					$query .= "CASE WHEN SLS041.t_ddta < GETDATE() THEN '*' ELSE '' END t_ddta_delay, ";
					$query .= 'convert(varchar(100), cast(SLS041.t_amta as decimal(15,3))) as t_amta, ';
					$query .= 'SLS041.t_eono, ';
					$query .= 'SLS041.t_epos,  ';
					$query .= "(select ITM001.t_dsca from ttiitm001{$vars['z']} ITM001 where ITM001.t_item=SLS041.t_item) t_dsca, ";
					$query .= "(select ITM001.t_csel from ttiitm001{$vars['z']} ITM001 where ITM001.t_item=SLS041.t_item) t_csel, ";
					$query .= "convert(varchar(100), cast((select ITM001.t_copr from ttiitm001{$vars['z']} ITM001 where ITM001.t_item=SLS041.t_item) as decimal(15,5))) as t_copr, ";
					$query .= "(select ITM001.t_oltm from ttiitm001{$vars['z']} ITM001 where ITM001.t_item=SLS041.t_item) t_oltm, ";
					$query .= "(select SLS040.t_refa from ttdsls040{$vars['z']} SLS040 where SLS040.t_orno=SLS041.t_orno) t_refa, ";
					$query .= "(select SLS040.t_refb from ttdsls040{$vars['z']} SLS040 where SLS040.t_orno=SLS041.t_orno) t_refb, ";
					$query .= "CASE (select SLS040.t_scom from ttdsls040{$vars['z']} SLS040 where SLS040.t_orno=SLS041.t_orno) WHEN 1 THEN 'Yes' ELSE 'No' END t_scom, ";
					$query .= "(select MCS013.t_dsca from ttcmcs013{$vars['z']} MCS013 where MCS013.t_cpay= (select SLS040.t_cpay from ttdsls040{$vars['z']} SLS040 where SLS040.t_orno=SLS041.t_orno) ) e_dsca, ";
					$query .= "(select SLS040.t_crep from ttdsls040{$vars['z']} SLS040 where SLS040.t_orno=SLS041.t_orno) t_crep, ";
					$query .= "SUBSTRING(convert(varchar, (select SLS040.t_odat from ttdsls040{$vars['z']} SLS040 where SLS040.t_orno=SLS041.t_orno), 120), 0, 11) AS t_odat, ";
					$query .= "(select top 1 SLS045.t_ssls from ttdsls045{$vars['z']} SLS045 where SLS045.t_orno=SLS041.t_orno and SLS045.t_pono=SLS041.t_pono order by SLS045.t_srnb desc) t_ssls, ";
					$query .= "(select top 1 SLS045.t_ssls from ttdsls045{$vars['z']} SLS045 where SLS045.t_orno=SLS041.t_orno and SLS045.t_pono<>SLS041.t_pono and SLS045.t_ssls<>7 order by SLS045.t_srnb desc) t_ssl2, ";
					$query .= "convert(varchar(100), cast((select ITM001.t_copr/0.95 from ttiitm001{$vars['z']} ITM001 where ITM001.t_item=SLS041.t_item) as decimal(15,5))) as t_AAAA, ";
					$query .= "(select ITM001.t_oltm/5*7 from ttiitm001{$vars['z']} ITM001 where ITM001.t_item=SLS041.t_item) t_BBBB, ";
					$query .= "SUBSTRING(convert(varchar, (dateadd(d, (select ITM001.t_oltm/5*7 from ttiitm001{$vars['z']} ITM001 where ITM001.t_item=SLS041.t_item), getdate())), 120), 0, 11) AS t_CCCC, ";
					$query .= "(select ITM001.t_dscc from ttiitm001{$vars['z']} ITM001 where ITM001.t_item=SLS041.t_item) t_dscc, ";
					$query .= "(select ITM001.t_oqmf from ttiitm001{$vars['z']} ITM001 where ITM001.t_item=SLS041.t_item) t_oqmf, ";
					$query .= "(select ITM001.t_mioq from ttiitm001{$vars['z']} ITM001 where ITM001.t_item=SLS041.t_item) t_mioq, ";
					$query .= "(select ITM001.t_buyr from ttiitm001{$vars['z']} ITM001 where ITM001.t_item=SLS041.t_item) t_buyr, ";
					$query .= "(select COM001.t_namb from ttccom001{$vars['z']} COM001 where COM001.t_emno=(select ITM001.t_buyr from ttiitm001{$vars['z']} ITM001 where ITM001.t_item=SLS041.t_item)) t_nam2, ";
					$query .= "(select ITM001.t_cplb from ttiitm001{$vars['z']} ITM001 where ITM001.t_item=SLS041.t_item) t_cplb, ";
					$query .= "(select COM001.t_namb from ttccom001{$vars['z']} COM001 where COM001.t_emno=(select ITM001.t_cplb from ttiitm001{$vars['z']} ITM001 where ITM001.t_item=SLS041.t_item)) t_nam3, ";
					$query .= "(select ITM001.t_suno from ttiitm001{$vars['z']} ITM001 where ITM001.t_item=SLS041.t_item) t_suno, ";
					$query .= "(select ITM001.t_cwar from ttiitm001{$vars['z']} ITM001 where ITM001.t_item=SLS041.t_item) t_cwar, ";
					$query .= "(select ITM001.t_stoc from ttiitm001{$vars['z']} ITM001 where ITM001.t_item=SLS041.t_item) t_stoc, ";
					$query .= "(select ITM001.t_ordr from ttiitm001{$vars['z']} ITM001 where ITM001.t_item=SLS041.t_item) t_ordr, ";
					$query .= "(select ITM001.t_allo from ttiitm001{$vars['z']} ITM001 where ITM001.t_item=SLS041.t_item) t_allo, ";
					$query .= "(SELECT com.t_iscn as t_iscn FROM ttccom020{$vars['z']} com WHERE com.t_suno=SLS041.t_cuno) t_iscn ";
					$query .= 'from ';
					$query .= "ttdsls041{$vars['z']} SLS041 ";
					$query .= 'where ';
					$query .= "SLS041.t_orno>='{$vars['so_from']}' and ";
					$query .= "SLS041.t_orno<'{$vars['so_to']}' ";
					$query .= 'order by SLS041.t_cuno asc, SLS041.t_orno asc, SLS041.t_pono asc ';

					$rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
					if ($vars['z'] == 620) {
						$xItems = [
							't_cuno' => 'Customer',
							't_odat' => 'Order date',
							't_orno' => 'Order',
							't_pono' => 'Position',
							't_eono' => 'Customer order',
							't_epos' => 'Position',
							't_refa' => 'Ref A',
							't_refb' => 'Ref B',
							't_item' => 'Item',
							't_dsca' => 'Description',
							't_oqua' => 'Order qty',
							't_dqua' => 'Ship qty',
							't_rasr' => 'Back Order',
							't_ddta_delay' => 'Delay',
							't_ddta' => 'Planned Ship Date',
							'resc_date' => 'Reschdelue Date',
							't_amta' => 'amount',
							't_ssls' => 'Line statut',
							't_csel' => 'Select. Code',
							't_scom' => 'Ship Complete',
							'e_dsca' => 'Terms payment',
							't_crep' => 'Vendor',
							't_copr' => 'Std cost price',
							't_oltm' => 'Order lead time',
							't_AAAA' => 'PRS/0.95',
							't_BBBB' => 'Order lead time/5x7 (Delay)',
							't_CCCC' => 'Today + Delay',
							't_dscc' => 'PIPO (Item size)',
							't_oqmf' => 'Order Quantity Multiple of',
							't_mioq' => 'Minimum Order Quantity',
							't_buyr' => 'Buyer',
							't_nam2' => 'Buyer name 2',
							't_cplb' => 'Planner',
							't_nam3' => 'Planner name 2',
							't_suno' => 'Supplier',
							't_cwar' => 'Warehouse',
							't_odat' => 'Order date',
							't_stoc' => 'Inventory on hand',
							't_ordr' => 'Inventory on order',
							't_allo' => 'Allocated inventory',
						];

						foreach ($rows as $key1 => $value1) {
							// Do not keep orders with all lines in status 7
							if ($rows[$key1]['t_ssls'] == '7' and $rows[$key1]['t_ssl2'] == '') {
								unset($rows[$key1]);
							}

							// translate status
							if ($rows[$key1]['t_ssls'] == '1') {
								$rows[$key1]['t_ssls'] = 'Print Order Ack.';
							}
							if ($rows[$key1]['t_ssls'] == '8') {
								$rows[$key1]['t_ssls'] = 'Gener. outbound advic.';
							}
							if ($rows[$key1]['t_ssls'] == '3') {
								$rows[$key1]['t_ssls'] = 'Maintain Delivery';
							}
							if ($rows[$key1]['t_ssls'] == '4') {
								$rows[$key1]['t_ssls'] = 'Print Packing Slips';
							}
							if ($rows[$key1]['t_ssls'] == '6') {
								$rows[$key1]['t_ssls'] = 'Print Sales Invoices';
							}
							if ($rows[$key1]['t_ssls'] == '7') {
								$rows[$key1]['t_ssls'] = 'Suppression ligne';
							}
							if (!empty($rows[$key1]['t_iscn'])) {
								$query = <<<EOF
                            SELECT SUBSTRING(convert(varchar, DATEADD(day, -(edi.t_dayr),t1.t_date), 120), 0, 11) AS resc_date 
                            FROM ttimrp030{$rows[$key1]['t_iscn']} as t1 
                            LEFT JOIN ttccom020{$rows[$key1]['t_iscn']} as sup ON sup.t_iscn={$vars['z']} 
                            LEFT JOIN ttcedi900{$rows[$key1]['t_iscn']} as edi on edi.t_suno =sup.t_suno
                            WHERE t1.t_orno='{$rows[$key1]['t_eono']}' AND t1.t_item='{$rows[$key1]['t_item']}'
EOF;
								$res = tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
								$rows[$key1]['resc_date'] = $res['resc_date'];
							}
						}
					}
					$caption = "SO Backlog for company {$vars['z']}";
				}//else{
				$body = $form->toHTML();
				//}
				break;

			case 'PartsShippedByAirOrSeaByItem':
				$xItems = [
					't_item' => 'Item',
					't_dsca' => 'Description',
					't_suno' => 'Supplier',
					't_buyr' => 'Buyer',
					't_cplb' => 'Planner',
					't_reop' => 'Reorder point SP1',
					't_stoc' => 'Inventory SP1',
					't_allo' => 'Allocated Inventory',
					't_oqua' => 'Ordered qty',
					't_zzz4' => 'Last 12 months consumption',
					't_zzz5' => 'Last 12 months orders (sales/production)',
					't_oltm' => 'Order lead time',
				];
				// Get listing
				$erpList = tldLocation::getERPList('smartyOptions');
				// Get form
				$form = new HTML_QuickForm('frmPartsShippedByAirOrSeaByItem', 'get', '', '', '', true);
				$form->addElement('hidden', 'm[0]', 'reports');
				$form->addElement('hidden', 'm[1]', 'listing');
				$form->addElement('hidden', 'm[2]', 'PartsShippedByAirOrSeaByItem');
				$form->addElement('header', 'title', 'Select dates and company:');
				$form->addElement('select', 'z', 'Company#',
					['' => ''] + $erpList);
				$form->addElement('submit', 'btnSubmit', 'Submit');
				$form->addRule('x', 'This is required', 'required');
				$form->addRule('y', 'This is required', 'required');
				$form->addRule('z', 'This is required', 'required');
				$form->setDefaults(['y' => date('Y-m-d')]);

				if ($form->validate()) {
					$vars = tldUtils::cleanupFormInput($form->exportValues());
					$dateLastYear = date('Y-m-d', strtotime('Last Year', time()));
					$query = 'select ';
					$query .= 'ITM001.t_item, ';
					$query .= 'ITM001.t_dsca, ';
					$query .= 'ITM001.t_suno, ';
					$query .= 'ITM001.t_buyr, ';
					$query .= 'ITM001.t_ordr, ';
					$query .= "(select INV001.t_reop from ttdinv001{$vars['z']} INV001 where INV001.t_item=ITM001.t_item and INV001.t_cwar='SP1') t_reop, ";
					$query .= "(select INV001.t_stoc from ttdinv001{$vars['z']} INV001 where INV001.t_item=ITM001.t_item and INV001.t_cwar='SP1') t_stoc, ";
					$query .= "(select INV001.t_allo from ttdinv001{$vars['z']} INV001 where INV001.t_item=ITM001.t_item and INV001.t_cwar='SP1') t_allo, ";
					$query .= 'ITM001.t_oltm, ';
					$query .= "CASE ITM001.t_cplb WHEN 400001 THEN 'SEA' ELSE 'AIR' END t_cplb, ";
					$query .= "CASE (select sum(SLS041.t_oqua) from ttdsls041{$vars['z']} SLS041, ttdsls040{$vars['z']} SLS040 where SLS040.t_orno=SLS041.t_orno and SLS040.t_odat>= '" . $dateLastYear . "' and SLS041.t_item = ITM001.t_item and (SLS040.t_cotp='SN3' or (SLS040.t_cotp>='W01' and SLS040.t_cotp<='W99'))) WHEN NULL THEN 0 ELSE (select sum(SLS041.t_oqua) from ttdsls041{$vars['z']} SLS041, ttdsls040{$vars['z']} SLS040 where SLS040.t_orno=SLS041.t_orno and SLS040.t_odat>= '" . $dateLastYear . "' and SLS041.t_item = ITM001.t_item and (SLS040.t_cotp='SN3' or (SLS040.t_cotp>='W01' and SLS040.t_cotp<='W99'))) END +  ";
					$query .= "CASE (select sum(CST001.t_qune) from tticst001{$vars['z']} CST001, ttisfc001{$vars['z']} SFC001 where CST001.t_pdno=SFC001.t_pdno and SFC001.t_prdt>= '" . $dateLastYear . "' and CST001.t_sitm = ITM001.t_item) WHEN NULL THEN 0 ELSE (select sum(CST001.t_qune) from tticst001{$vars['z']} CST001, ttisfc001{$vars['z']} SFC001 where CST001.t_pdno=SFC001.t_pdno and SFC001.t_prdt>= '" . $dateLastYear . "' and CST001.t_sitm = ITM001.t_item) END t_zzz4, ";
					$query .= "CASE (select count(SLS041.t_orno) from ttdsls041{$vars['z']} SLS041, ttdsls040{$vars['z']} SLS040 where SLS040.t_orno=SLS041.t_orno and SLS040.t_odat>= '" . $dateLastYear . "' and SLS041.t_item = ITM001.t_item and (SLS040.t_cotp='SN3' or (SLS040.t_cotp>='W01' and SLS040.t_cotp<='W99'))) WHEN NULL THEN 0 ELSE (select count(SLS041.t_orno) from ttdsls041{$vars['z']} SLS041, ttdsls040{$vars['z']} SLS040 where SLS040.t_orno=SLS041.t_orno and SLS040.t_odat>= '" . $dateLastYear . "' and SLS041.t_item = ITM001.t_item and (SLS040.t_cotp='SN3' or (SLS040.t_cotp>='W01' and SLS040.t_cotp<='W99'))) END +  ";
					$query .= "CASE (select count(CST001.t_pdno) from tticst001{$vars['z']} CST001, ttisfc001{$vars['z']} SFC001 where CST001.t_pdno=SFC001.t_pdno and SFC001.t_prdt>= '" . $dateLastYear . "' and CST001.t_sitm = ITM001.t_item) WHEN NULL THEN 0 ELSE (select count(CST001.t_pdno) from tticst001{$vars['z']} CST001, ttisfc001{$vars['z']} SFC001 where CST001.t_pdno=SFC001.t_pdno and SFC001.t_prdt>= '" . $dateLastYear . "' and CST001.t_sitm = ITM001.t_item) END t_zzz5 ";
					$query .= "from ttiitm001{$vars['z']} ITM001 where  ";
					$query .= "ITM001.t_cplb in ('400001', '400002') ";
					$query .= 'order by ITM001.t_item asc ';
					$rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
					$caption = "Parts Shipped by AIR or SEA for company {$vars['z']}";
				}//else{
				$body = $form->toHTML();
				//}
				break;

			case 'PartsShippedByAirOrSea':
				$xItems = [
					't_item' => 'Item',
					't_dsca' => 'Description',
					't_suno' => 'Supplier',
					't_buyr' => 'Buyer',
					't_cplb' => 'Planner',
					't_reop' => 'Reorder point SP1',
					't_strs' => 'Inventory SP1',
					't_allo' => 'Allocated Inventory',
					't_orno' => 'Purchase order',
					't_pono' => 'Position',
					't_oqua' => 'Ordered qty',
					't_totq' => 'Tot qty',
					't_ddta' => 'Planned del. date',
					't_ddtc' => 'Confirmed del. date',
					't_zzz4' => 'Last 12 months consumption',
					't_zzz5' => 'Last 12 months orders (sales/production)',
					't_oltm' => 'Order lead time',

				];
				// Get listing
				$erpList = tldLocation::getERPList('smartyOptions');
				// Get form
				$form = new HTML_QuickForm('frmPartsShippedByAirOrSea', 'get', '', '', '', true);
				$form->addElement('hidden', 'm[0]', 'reports');
				$form->addElement('hidden', 'm[1]', 'listing');
				$form->addElement('hidden', 'm[2]', 'PartsShippedByAirOrSea');
				$form->addElement('header', 'title', 'Select dates and company:');
				$form->addElement('select', 'z', 'Company#',
					['' => ''] + $erpList);
				$form->addElement('submit', 'btnSubmit', 'Submit');
				$form->addRule('x', 'This is required', 'required');
				$form->addRule('y', 'This is required', 'required');
				$form->addRule('z', 'This is required', 'required');
				$form->setDefaults(['y' => date('Y-m-d')]);

				if ($form->validate()) {
					$vars = tldUtils::cleanupFormInput($form->exportValues());
					$dateLastYear = date('Y-m-d', strtotime('Last Year', time()));
					$query = 'select ';
					$query .= 'PUR045.t_orno, ';
					$query .= 'PUR045.t_pono, ';
					$query .= 'PUR045.t_item, ';
					$query .= "(select PUR041.t_oqua from ttdpur041{$vars['z']} PUR041 where PUR041.t_orno=PUR045.t_orno and PUR041.t_pono=PUR045.t_pono) t_oqua, ";
					$query .= "(select PUR041.t_ddta from ttdpur041{$vars['z']} PUR041 where PUR041.t_orno=PUR045.t_orno and PUR041.t_pono=PUR045.t_pono) t_ddta, ";
					$query .= "(select PUR041.t_ddtc from ttdpur041{$vars['z']} PUR041 where PUR041.t_orno=PUR045.t_orno and PUR041.t_pono=PUR045.t_pono) t_ddtc, ";
					$query .= "(select PUR041.t_ddtd from ttdpur041{$vars['z']} PUR041 where PUR041.t_orno=PUR045.t_orno and PUR041.t_pono=PUR045.t_pono) t_ddtd, ";
					$query .= "(select PUR040.t_suno from ttdpur040{$vars['z']} PUR040 where PUR040.t_orno=PUR045.t_orno) t_suno, ";
					$query .= "(select ITM001.t_buyr from ttiitm001{$vars['z']} ITM001 where ITM001.t_item=PUR045.t_item) t_buyr, ";
					$query .= "CASE (select ITM001.t_cplb from ttiitm001{$vars['z']} ITM001 where ITM001.t_item=PUR045.t_item) WHEN 400001 THEN 'SEA' ELSE 'AIR' END t_cplb, ";
					$query .= "(select ITM001.t_dsca from ttiitm001{$vars['z']} ITM001 where ITM001.t_item=PUR045.t_item) t_dsca, ";
					$query .= "(select ITM001.t_oltm from ttiitm001{$vars['z']} ITM001 where ITM001.t_item=PUR045.t_item) t_oltm, ";
					$query .= "(select ITM001.t_allo from ttiitm001{$vars['z']} ITM001 where ITM001.t_item=PUR045.t_item) t_allo, ";
					$query .= "(select INV001.t_reop from ttdinv001{$vars['z']} INV001 where INV001.t_item=PUR045.t_item and INV001.t_cwar='SP1') t_reop, ";
					$query .= "(select sum(ILC101.t_strs) from ttdilc101{$vars['z']} ILC101 where ILC101.t_item=PUR045.t_item and ILC101.t_cwar='SP1') t_strs, ";
					$query .= "CASE (select sum(SLS041.t_oqua) from ttdsls041{$vars['z']} SLS041, ttdsls040{$vars['z']} SLS040 where SLS040.t_orno=SLS041.t_orno and SLS040.t_odat>= '" . $dateLastYear . "' and SLS041.t_item = PUR045.t_item and (SLS040.t_cotp='SN3' or (SLS040.t_cotp>='W01' and SLS040.t_cotp<='W99'))) WHEN NULL THEN 0 ELSE (select sum(SLS041.t_oqua) from ttdsls041{$vars['z']} SLS041, ttdsls040{$vars['z']} SLS040 where SLS040.t_orno=SLS041.t_orno and SLS040.t_odat>= '" . $dateLastYear . "' and SLS041.t_item = PUR045.t_item and (SLS040.t_cotp='SN3' or (SLS040.t_cotp>='W01' and SLS040.t_cotp<='W99'))) END +  ";
					$query .= "CASE (select sum(CST001.t_qune) from tticst001{$vars['z']} CST001, ttisfc001{$vars['z']} SFC001 where CST001.t_pdno=SFC001.t_pdno and SFC001.t_prdt>= '" . $dateLastYear . "' and CST001.t_sitm = PUR045.t_item) WHEN NULL THEN 0 ELSE (select sum(CST001.t_qune) from tticst001{$vars['z']} CST001, ttisfc001{$vars['z']} SFC001 where CST001.t_pdno=SFC001.t_pdno and SFC001.t_prdt>= '" . $dateLastYear . "' and CST001.t_sitm = PUR045.t_item) END t_zzz4, ";
					$query .= "CASE (select count(SLS041.t_orno) from ttdsls041{$vars['z']} SLS041, ttdsls040{$vars['z']} SLS040 where SLS040.t_orno=SLS041.t_orno and SLS040.t_odat>= '" . $dateLastYear . "' and SLS041.t_item = PUR045.t_item and (SLS040.t_cotp='SN3' or (SLS040.t_cotp>='W01' and SLS040.t_cotp<='W99'))) WHEN NULL THEN 0 ELSE (select count(SLS041.t_orno) from ttdsls041{$vars['z']} SLS041, ttdsls040{$vars['z']} SLS040 where SLS040.t_orno=SLS041.t_orno and SLS040.t_odat>= '" . $dateLastYear . "' and SLS041.t_item = PUR045.t_item and (SLS040.t_cotp='SN3' or (SLS040.t_cotp>='W01' and SLS040.t_cotp<='W99'))) END +  ";
					$query .= "CASE (select count(CST001.t_pdno) from tticst001{$vars['z']} CST001, ttisfc001{$vars['z']} SFC001 where CST001.t_pdno=SFC001.t_pdno and SFC001.t_prdt>= '" . $dateLastYear . "' and CST001.t_sitm = PUR045.t_item) WHEN NULL THEN 0 ELSE (select count(CST001.t_pdno) from tticst001{$vars['z']} CST001, ttisfc001{$vars['z']} SFC001 where CST001.t_pdno=SFC001.t_pdno and SFC001.t_prdt>= '" . $dateLastYear . "' and CST001.t_sitm = PUR045.t_item) END t_zzz5 ";
					$query .= "from ttdpur045{$vars['z']} as PUR045 where  ";
					//$query.="PUR045.t_spur<9 and ";  // status = open
					$query .= "PUR045.t_srnb=(SELECT MAX(T3.t_srnb) FROM ttdpur045{$vars['z']} AS T3 WHERE PUR045.t_orno=T3.t_orno AND PUR045.t_pono=T3.t_pono) ";
					$query .= 'AND (PUR045.t_spur<9 OR (PUR045.t_spur=9 AND PUR045.t_bqua>0)) and ';
					$query .= "PUR045.t_orno in (select PUR040.t_orno from ttdpur040{$vars['z']} PUR040 ";
					$query .= "  where PUR045.t_orno = PUR040.t_orno and PUR040.t_suno in ('DE8800', 'DE9302')) and ";
					$query .= "PUR045.t_item in (select ITM001.t_item from ttiitm001{$vars['z']} ITM001 ";
					$query .= "  where PUR045.t_item = ITM001.t_item and ITM001.t_cplb in ('400001', '400002')) ";

					$rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
					// Manage Confirmed del. date => ddtd if exists, else ddtc

					foreach ($rows as $key1 => $value1) {
						$dateTmp = new DateTime($rows[$key1]['t_ddta']);
						$rows[$key1]['t_ddta'] = $dateTmp->format('Y-m-d');

						$dateTmp = new DateTime($rows[$key1]['t_ddtc']);
						$rows[$key1]['t_ddtc'] = $dateTmp->format('Y-m-d');

						$dateTmp = new DateTime($rows[$key1]['t_ddtd']);
						if ($dateTmp->format('Y') > 1753) {
							$rows[$key1]['t_ddtc'] = $dateTmp->format('Y-m-d');
						}

						$rows[$key1]['t_totq'] = 0;
						foreach ($rows as $key2 => $value2) {
							if ($rows[$key1]['t_item'] == $rows[$key2]['t_item']) {
								$rows[$key1]['t_totq'] += $rows[$key2]['t_oqua'];
							}
						}
					}

					$caption = "Parts Shipped by AIR or SEA for company {$vars['z']}";
				}//else{
				$body = $form->toHTML();
				//}
				break;

			case 'fedexMonthly':
				$xItems = [
					'ddat' => 'Del.Date',
					'dqua' => 'Delivered Qty',
					'orno' => 'Order',
					'pono' => 'Position',
					'ddta' => 'Pl.D.Dt.',
					'odat' => 'Order Date',
					'srnb' => 'Del',
					'cuqs' => 'U/M',
					'IFR' => 'IFR',
					'AVT' => 'AVT',
					'WFR' => 'WFR',
					'item' => 'Item',
					'dqua180' => 'Delivered Qty 180 Days',

				];
				// Get listing
				$erpList = tldLocation::getERPList('smartyOptions');
				// Get form
				$form = new HTML_QuickForm('frmFedexMonthly', 'get', '', '', '', true);
				$form->addElement('hidden', 'm[0]', 'reports');
				$form->addElement('hidden', 'm[1]', 'listing');
				$form->addElement('hidden', 'm[2]', 'fedexMonthly');
				$form->addElement('header', 'title', 'Select dates and company:');
				$form->addElement('date', 'x', 'From',
					['format' => 'Y-m-d', 'minYear' => date('Y') - 2, 'maxYear' => date('Y') + 2]);
				$form->addElement('date', 'y', 'To',
					['format' => 'Y-m-d', 'minYear' => date('Y') - 2, 'maxYear' => date('Y') + 2]);
				$form->addElement('select', 'z', 'Company#',
					['' => ''] + $erpList);
				$form->addElement('submit', 'btnSubmit', 'Submit');
				$form->addRule('x', 'This is required', 'required');
				$form->addRule('y', 'This is required', 'required');
				$form->addRule('z', 'This is required', 'required');
				$form->setDefaults(['y' => date('Y-m-d')]);

				if ($form->validate()) {
					$vars = tldUtils::cleanupFormInput($form->exportValues());
					$vars['from'] = sprintf('%04d-%02d-%02d', $vars['x']['Y'], $vars['x']['m'], $vars['x']['d']);
					$vars['to'] = sprintf('%04d-%02d-%02d', $vars['y']['Y'], $vars['y']['m'], $vars['y']['d']);
					$rows = tldUtils::getSqlToAssocArray("EXEC Fedex_Weekly_Report '{$vars['from']}','{$vars['to']}','{$vars['z']}'",
						'odbc', ['src' => 'baan']
					);

					$caption = "Fedex Monthly report from {$vars['from']} to {$vars['to']} for company {$vars['z']}";
				} else {
					$body = $form->toHTML();
				}
				break;

			case 'InventoryForecastTool2':
				$form = new HTML_QuickForm('frmInventoryForecastTool2', 'get', '', '', '', true);
				$form->addElement('hidden', 'm[0]', 'reports');
				$form->addElement('hidden', 'm[1]', 'listing');
				$form->addElement('hidden', 'm[2]', 'InventoryForecastTool2');
				$form->addElement('header', 'title', 'Select dates and company:');
				$form->addElement('text', 'pn_from', 'Item from');
				$form->addElement('text', 'pn_to', 'Item to');
				$form->addElement('select', 'erp', 'Company#',
					['' => ''] + tldLocation::getERPList('smartyOptions'));
				$form->addElement('select', 'no_mvt', 'Items with no inventory & no mvt',
					['N' => 'N', 'Y' => 'Y']);
				$form->addElement('select', 'tran_detail', 'Transactions detail',
					['N' => 'N', 'Y' => 'Y']);
				$form->addElement('select', 'incl_soft', 'Include Soft Transactions',
					['Y' => 'Y', 'N' => 'N']);
				$form->addElement('select', 'm[3]', 'Results in...',
					['' => 'WEB', 'xls' => 'XLS']);
				$form->addElement('submit', 'btnSubmit', 'Submit');
				$form->addRule('pn_to', 'This is required', 'required');
				$form->addRule('erp', 'This is required', 'required');
				$pnfrom = (empty($_GET['pn_from'])) ? null : $_GET['pn_from'];
				$pnto = (empty($_GET['pn_to'])) ? 'ZZZ' : $_GET['pn_to'];
				$form->setDefaults(
					[
						'pn_from' => $pnfrom,
						'pn_to' => $pnto,
						'erp' => $_GET['erp'],
						'no_mvt' => $_GET['no_mvt'],
						'tran_detail' => $_GET['tran_detail'],
						'incl_soft' => $_GET['incl_soft'],
					]
				);

				if (!$form->validate() && $_GET['validate'] <> true) {
					$body = $form->toHTML();
					break;
				}

				$vars = tldUtils::cleanupFormInput($form->exportValues());
				if ($vars['tran_detail'] === 'Y') {
					$xItems = [
						'item' => 'Item',
						'suno' => 'Main supplier',
						'nama' => 'Supplier name',
						'cwar' => 'Warehouse',
						'leth' => 'Theoric Lead time',
						'lerl' => 'Real Lead time',
						'saft' => 'Safety stock',
						'reor' => 'Reorder point',
						'dsca' => 'Item description',
						'buyr' => 'Buyer',
						'ctyp' => 'Product type',
						'stoc' => 'Current on hand',
						'uome' => 'U/M',
						'copr' => 'Standard cost',
						'val0' => 'Current inventory value',
						'qty1p' => 'Qty+ end M',
						'qty1n' => 'Qty- end M',
						'qty1' => 'Qty end M',
						'val1' => 'Value end M',
						'qty2p' => 'Qty+ end M+1',
						'qty2n' => 'Qty- end M+1',
						'qty2' => 'Qty end M+1',
						'val2' => 'Value end M+1',
						'qty3p' => 'Qty+ end M+2',
						'qty3n' => 'Qty- end M+2',
						'qty3' => 'Qty end M+2',
						'val3' => 'Value end M+2',
						'qty4p' => 'Qty+ end M+3',
						'qty4n' => 'Qty- end M+3',
						'qty4' => 'Qty end M+3',
						'val4' => 'Value end M+3',
						'qty5p' => 'Qty+ end M+4',
						'qty5n' => 'Qty- end M+4',
						'qty5' => 'Qty end M+4',
						'val5' => 'Value end M+4',
						'qty6p' => 'Qty+ end M+5',
						'qty6n' => 'Qty- end M+5',
						'qty6' => 'Qty end M+5',
						'val6' => 'Value end M+5',
					];
				} else {
					$xItems = [
						'item' => 'Item',
						'suno' => 'Main supplier',
						'nama' => 'Supplier name',
						'cwar' => 'Warehouse',
						'leth' => 'Theoric Lead time',
						'lerl' => 'Real Lead time',
						'saft' => 'Safety stock',
						'reor' => 'Reorder point',
						'dsca' => 'Item description',
						'buyr' => 'Buyer',
						'ctyp' => 'Product type',
						'stoc' => 'Current on hand',
						'uome' => 'U/M',
						'copr' => 'Standard cost',
						'val0' => 'Current inventory value',
						'qty1' => 'Qty end M',
						'val1' => 'Value end M',
						'qty2' => 'Qty end M+1',
						'val2' => 'Value end M+1',
						'qty3' => 'Qty end M+2',
						'val3' => 'Value end M+2',
						'qty4' => 'Qty end M+3',
						'val4' => 'Value end M+3',
						'qty5' => 'Qty end M+4',
						'val5' => 'Value end M+4',
						'qty6' => 'Qty end M+5',
						'val6' => 'Value end M+5',
					];
				}
				$rows = tldUtils::getSqlToAssocArray(
					"EXEC Inventory_Projection_2 '{$vars['pn_from']}','{$vars['pn_to']}','{$vars['erp']}','{$vars['no_mvt']}','{$vars['tran_detail']}','{$vars['incl_soft']}','Month'",
					'odbc',
					['src' => 'baan']
				);
				$caption = "Inventory Forecast report from {$vars['pn_from']} to {$vars['pn_to']} for company {$vars['erp']}";
				$body = $form->toHTML();
				break;


			case 'ActivityReportTldAme':
				$xItems = [
					'ddat' => 'Date',
					'ctol' => 'CT Sales order lines',
					'caol' => 'CA Sales order lines',
					'ctwl' => 'CT Warr. lines',
					'cawl' => 'CA Warr. lines',
					'toto' => 'Total Orders',
					'ctsi' => 'CT Shipped Items',
					'casi' => 'CA Shipped Items',
					'ctsw' => 'CT Shipped Warr.',
					'casw' => 'CA Shipped Warr.',
					'tots' => 'Total Shipments',
					'acwl' => 'ACE Shipped WC lines',
					'acwo' => 'ACE Shipped WC orders',
					'acwt' => 'ACE Shipped WC items',
					'acal' => 'ACE Shipped accommodation lines',
					'acao' => 'ACE Shipped accommodation orders',
					'acat' => 'ACE Shipped accommodation items',
					'ctrl' => 'CT Receipt lines',
					'carl' => 'CA Receipt lines',
					'ctri' => 'CT Receipt items',
					'cari' => 'CA Receipt items',
					'ctqo' => 'CT quotes',
					'caqo' => 'CA quotes',
				];
				// Get listing
				$erpList = tldLocation::getERPList('smartyOptions');
				// Get form
				$form = new HTML_QuickForm('frmActivityReportTldAme', 'get', '', '', '', true);
				$form->addElement('hidden', 'm[0]', 'reports');
				$form->addElement('hidden', 'm[1]', 'listing');
				$form->addElement('hidden', 'm[2]', 'ActivityReportTldAme');
				$form->addElement('header', 'title', 'Select dates and company:');
				$form->addElement('date', 'x', 'From',
					['format' => 'Y-m-d', 'minYear' => date('Y') - 2, 'maxYear' => date('Y') + 2]);
				$form->addElement('date', 'y', 'To',
					['format' => 'Y-m-d', 'minYear' => date('Y') - 2, 'maxYear' => date('Y') + 2]);
				$form->addElement('submit', 'btnSubmit', 'Submit');
				$form->addRule('x', 'This is required', 'required');
				$form->addRule('y', 'This is required', 'required');
				$form->setDefaults(['y' => date('Y-m-d')]);

				if (!$form->validate()) {
					$body = $form->toHTML();
					break;
				}

				$vars = tldUtils::cleanupFormInput($form->exportValues());
				$vars['from'] = implode('-', $vars['x']);
				$vars['to'] = implode('-', $vars['y']);
				$rows = tldUtils::getSqlToAssocArray("EXEC SPH_Activity_Report '{$vars['from']}','{$vars['to']}','{$vars['z']}'",
					'odbc', ['src' => 'baan']
				);

				$caption = "SPH America Activity report from {$vars['from']} to {$vars['to']} for company {$vars['z']}";
				break;

			case 'ActivityReportTldEur':
				$xItems = [
					'ddat' => 'Date',
					'slsa' => 'SOL Sales',
					'slwa' => 'SOL Warranties',
					'slse' => 'SOL Service',
					'sler' => 'SOL ER',
					'tot1' => 'SOL Total',
					'shsa' => 'Shipped items Sales',
					'shwa' => 'Shipped items Warranties',
					'shse' => 'Shipped items Service',
					'sher' => 'Shipped items ER',
					'tot2' => 'Shipped items Total',
					'recl' => 'Receipt lines',
					'reci' => 'Receipt items',
					'quot' => 'Quotes',
					'sbmt' => 'SB MTL',
					'sbst' => 'SB STL',
				];
				// Get listing
				$erpList = tldLocation::getERPList('smartyOptions');
				// Get form
				$form = new HTML_QuickForm('frmActivityReportTldEur', 'get', '', '', '', true);
				$form->addElement('hidden', 'm[0]', 'reports');
				$form->addElement('hidden', 'm[1]', 'listing');
				$form->addElement('hidden', 'm[2]', 'ActivityReportTldEur');
				$form->addElement('header', 'title', 'Select dates and company:');
				$form->addElement('date', 'x', 'From',
					['format' => 'Y-m-d', 'minYear' => date('Y') - 2, 'maxYear' => date('Y') + 2]);
				$form->addElement('date', 'y', 'To',
					['format' => 'Y-m-d', 'minYear' => date('Y') - 2, 'maxYear' => date('Y') + 2]);
				$form->addElement('submit', 'btnSubmit', 'Submit');
				$form->addRule('x', 'This is required', 'required');
				$form->addRule('y', 'This is required', 'required');
				$form->setDefaults(['y' => date('Y-m-d')]);

				if (!$form->validate()) {
					$body = $form->toHTML();
					break;
				}

				$vars = tldUtils::cleanupFormInput($form->exportValues());
				$vars['from'] = implode('-', $vars['x']);
				$vars['to'] = implode('-', $vars['y']);
				$rows = tldUtils::getSqlToAssocArray("EXEC SPH_Activity_Report_EU '{$vars['from']}','{$vars['to']}','{$vars['z']}'",
					'odbc', ['src' => 'baan']
				);
				$caption = "SPH Europe Activity report from {$vars['from']} to {$vars['to']} for company {$vars['z']}";
				break;

			case 'ActivityReportTldSHA':
				$xItems = [
					'ddat' => 'Date',
					'shin' => 'Interco orders',
					'shdo' => 'Domestic orders',
					'shwa' => 'WC orders',
					'shfo' => 'FOC orders',
					'tot2' => 'Total Orders',
					'quot' => 'Quote',
				];
				// Get listing
				$erpList = tldLocation::getERPList('smartyOptions');
				// Get form
				$form = new HTML_QuickForm('frmActivityReportTldSHA', 'get', '', '', '', true);
				$form->addElement('hidden', 'm[0]', 'reports');
				$form->addElement('hidden', 'm[1]', 'listing');
				$form->addElement('hidden', 'm[2]', 'ActivityReportTldSHA');
				$form->addElement('header', 'title', 'Select dates and company:');
				$form->addElement('date', 'x', 'From',
					['format' => 'Y-m-d', 'minYear' => date('Y') - 2, 'maxYear' => date('Y') + 2]);
				$form->addElement('date', 'y', 'To',
					['format' => 'Y-m-d', 'minYear' => date('Y') - 2, 'maxYear' => date('Y') + 2]);
				$form->addElement('submit', 'btnSubmit', 'Submit');
				$form->addRule('x', 'This is required', 'required');
				$form->addRule('y', 'This is required', 'required');
				$form->setDefaults(['y' => date('Y-m-d')]);

				if (!$form->validate()) {
					$body = $form->toHTML();
					break;
				}

				$vars = tldUtils::cleanupFormInput($form->exportValues());
				$vars['from'] = implode('-', $vars['x']);
				$vars['to'] = implode('-', $vars['y']);
				$rows = tldUtils::getSqlToAssocArray("EXEC SPH_Activity_Report_SHA '{$vars['from']}','{$vars['to']}','{$vars['z']}'",
					'odbc', ['src' => 'baan']
				);
				$caption = "SPH Shanghai Activity report from {$vars['from']} to {$vars['to']} for company {$vars['z']}";
				break;
			case 'PartsShippedWithUnit':
				$xItems = [
					't_orno' => 'Order',
					't_cotp' => 'Order type',
					't_item' => 'Item',
					't_dsca' => 'Description',
					't_cwar' => 'Warehouse',
					't_oqua' => 'Quantity',
					't_copr' => 'Standard cost price',
					't_pric' => 'Unit price',
					't_ldam_1' => 'Discount 1',
					't_ldam_2' => 'Discount 2',
					't_ldam_3' => 'Discount 3',
					't_amta' => 'Amount',
				];
				// Get listing
				$erpList = tldLocation::getERPList('smartyOptions');
				// Get form
				$form = new HTML_QuickForm('frmPartsShippedWithUnit', 'get', '', '', '', true);
				$form->addElement('hidden', 'm[0]', 'reports');
				$form->addElement('hidden', 'm[1]', 'listing');
				$form->addElement('hidden', 'm[2]', 'PartsShippedWithUnit');
				$form->addElement('header', 'title', 'Select dates and company:');
				$form->addElement('date', 'x', 'From',
					['format' => 'Y-m-d', 'minYear' => date('Y') - 2, 'maxYear' => date('Y') + 2]);
				$form->addElement('date', 'y', 'To',
					['format' => 'Y-m-d', 'minYear' => date('Y') - 2, 'maxYear' => date('Y') + 2]);
				$form->addElement('text', 'orderType', 'Order type');
				$form->addElement('text', 'cwarFrom', 'Warehouse from');
				$form->addElement('text', 'cwarTo', 'Warehouse to');
				$form->addElement('select', 'z', 'Company#',
					['' => ''] + $erpList);
				$form->addElement('submit', 'btnSubmit', 'Submit');
				$form->addRule('orderType', 'This is required', 'required');
				$form->addRule('x', 'This is required', 'required');
				$form->addRule('y', 'This is required', 'required');
				$form->addRule('z', 'This is required', 'required');
				$form->setDefaults(['y' => date('Y-m-d')]);

				if (!$form->validate()) {
					$body = $form->toHTML();
					break;
				}

				$vars = tldUtils::cleanupFormInput($form->exportValues());
				$vars['from'] = implode('-', $vars['x']);
				$vars['to'] = implode('-', $vars['y']);

				$query = <<<EOF
select
    F1.t_orno,
    (select F4.t_cotp from ttdsls040{$vars['z']} as F4 where F1.t_orno=F4.t_orno) as t_cotp,
	F1.t_item,
	(select F2.t_dsca from ttiitm001{$vars['z']} as F2 where F1.t_item=F2.t_item) as t_dsca,
	F1.t_cwar,
	F1.t_oqua,
	(select F3.t_copr from ttiitm001{$vars['z']} F3 where F1.t_item=F3.t_item) as t_copr,
	F1.t_pric,
	F1.t_ldam_1,
	F1.t_ldam_2,
	F1.t_ldam_3,
	F1.t_amta
from
	ttdsls041{$vars['z']} as F1
where
	F1.t_orno in (select t_orno from ttdsls040{$vars['z']} where t_cotp='{$vars['orderType']}') and
	F1.t_cwar >= '{$vars['cwarFrom']}' and
	F1.t_cwar <= '{$vars['cwarTo']}' and
	F1.t_odat >= '{$vars['from']}' and
	F1.t_odat <= '{$vars['to']}'
EOF;

				$rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
				$caption = "Recorded parts to be shipped with units from {$vars['from']} to {$vars['to']} for company {$vars['z']}";
				break;
			case 'topSelling':
				$xItems = [
					't_item' => 'PN#',
					't_dsca' => 'Description',
					't_cwar' => 'Warehouse',
					't_loca' => 'Location',
					't_oltm' => 'Leadtime',
					't_stoc' => 'On hand',
					't_reop' => 'Re-order point',
					'quantity' => 'Sold Qty',
				];
				$user = new tldUser($GLOBALS['PHP_AUTH_USER']);
				if (!isset($erp)) {
					$erp = tldLocation::getERPByID($user->getBUID());
				}
				$warehouseList = tldUtils::optionsByKeyValue(tldCWAR::byERP($erp), 't_cwar', 't_cwar');
				$where = '';
				$caption = 'Top 100 selling parts ';
				$form = new HTML_QuickForm('frmVATInvoice', 'get', '', '', '', true);
				$form->addElement('hidden', 'm[0]', 'reports');
				$form->addElement('hidden', 'm[1]', 'listing');
				$form->addElement('hidden', 'm[2]', 'topSelling');
				$form->addElement('header', 'title', 'Select warehouse:');
				$form->addElement('select', 'warehouse', 'Warehouse', ['ALL' => 'ALL'] + $warehouseList);
				$form->addElement('text', 'start', 'Date from', ['class' => 'datepicker']);
				$form->addElement('text', 'end', 'Date to', ['class' => 'datepicker']);
				$form->addElement('submit', 'btnSubmit', 'Submit');
				$form->addRule('start', 'This is required', 'required');
				$form->addRule('end', 'This is required', 'required');
				$form->setDefaults([
						'start' => date('Y') . '-01-01',
						'end' => date('Y-m-d')]
				);
				$form->setDefaults(['y' => date('Y-m-d')]);
				if ($form->validate()) {
					$vars = tldUtils::cleanupFormInput($form->exportValues());
					$start = TldDatabase::escape($vars['start']);
					$end = TldDatabase::escape($vars['end']);
					$caption .= "between $start - $end ";
					$where = ' 1=1 ';
					if ($vars['warehouse'] !== 'ALL') {
						$where .= " AND itm.t_cwar LIKE '{$vars['warehouse']}' ";
						$caption .= "by warehouse - {$vars['warehouse']}";
					}
					$query = 'SELECT TOP 100 ';
					$query .= 'itm.t_item, ';
					$query .= 'itm.t_dsca, ';
					$query .= 'itm.t_oltm, ';
					$query .= 'itm.t_cwar, ';
					$query .= 'itm.t_stoc, ';
					$query .= "(SELECT inv001.t_reop FROM ttdinv001$erp AS inv001 WHERE inv001.t_item=itm.t_item and inv001.t_cwar=itm.t_cwar ) AS t_reop, ";
					$query .= 'count(sls.t_dqua) AS quantity, ';
					$query .= "(SELECT TOP 1 ilc301.t_loca FROM ttdilc101$erp AS ilc301 WHERE ilc301.t_item=itm.t_item) AS t_loca ";
					$query .= "FROM ttdsls045$erp AS sls ";
					$query .= "LEFT JOIN ttiitm001$erp AS itm ON itm.t_item=sls.t_item WHERE";
					$query .= $where;
					$query .= "and sls.t_invd>='" . $start . "' ";
					$query .= "and sls.t_invd<='" . $end . "' ";
					$query .= 'Group By itm.t_item, itm.t_dsca, itm.t_oltm, itm.t_cwar,itm.t_stoc,itm.t_reop ';
					$query .= 'Order By quantity DESC';

					$rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
					if (!count($rows)) {
						$DEFAULT_ERROR[] = 'No data found...';
						break;
					}
				} else {
					$body = $form->toHTML();
				}
				break;

			case 'VatInvoice':
				$xItems = [
					't_odat' => 'Order Date',
					't_orno' => 'Order#',
					't_cotp' => 'Type',
					't_dino' => 'Pack#',
					't_invn' => 'Inv#',
					't_info' => 'Sale REP',
					't_cuno' => 'Cust#',
					't_nama' => 'Cust Name',
					'vat_no' => 'VAT#',
					'vat_date' => 'Invoice date',
					'amount' => 'VAT Amount',
					't_amta' => 'SO Amount',
					'status' => 'Status',
					'tobeinvoice' => 'TODO',
					'comment' => 'Comment',
					'commentdate' => 'Date',
					't_refa' => 'Ref A',
					't_refb' => 'Ref B',
					't_eono' => 'Cust PO#',
					't_pono' => '#',
					//              "t_ssls"=>"Stat",
					't_oqua' => 'Qty',
					't_dqua' => 'Del',
					't_bqua' => 'Back',
					't_item' => 'PN',
					't_dsca' => 'Description',
					'ch_dsca' => 'Description(CH)',
					't_ordr' => 'OO',
					't_stoc' => 'OH',
					't_allo' => 'AL',
					'trdt' => 'Date',
				];
				$cusList = tldERPCustomer::getCustomerList(680);
				$customerList = [];
				foreach ($cusList AS $customer) {
					$customerList[$customer['t_cuno']] = $customer['t_cuno'] . '->' . $customer['t_nama'];
				}
				$form = new HTML_QuickForm('frmVATInvoice', 'get', '', '', '', true);
				$form->addElement('hidden', 'm[0]', 'reports');
				$form->addElement('hidden', 'm[1]', 'listing');
				$form->addElement('hidden', 'm[2]', 'VatInvoice');
				$form->addElement('header', 'title', 'Select dates and company:');
				$form->addElement('date', 'x', 'From',
					['format' => 'Y-m-d', 'minYear' => date('Y') - 1, 'maxYear' => date('Y')]);
				$form->addElement('date', 'y', 'To',
					['format' => 'Y-m-d', 'minYear' => date('Y') - 1, 'maxYear' => date('Y')]);
				$form->addElement('select', 'status', 'Status',
					['' => '', 'SHIPPED' => 'Shipped but NO VAT Invoice', 'INVOICED' => 'VAT Invoice but not Paid', 'PAID' => 'PAID']);
				$form->addElement('select', 'rep', 'Sales Rep',
					['' => '', '70' => 'CiCi Tang', '71' => 'Ma Jun', '69' => 'Shi Qing']);
				$form->addElement('select', 'cust', 'Customer',
					['' => ''] + $customerList);
				$form->addElement('submit', 'btnSubmit', 'Submit');
				$form->setDefaults(['y' => date('Y-m-d')]);
				if ($form->validate()) {
					$vars = tldUtils::cleanupFormInput($form->exportValues());
					$vars['from'] = implode('-', $vars['x']);
					$vars['to'] = implode('-', $vars['y']);
					$w[] = "inv.t_trdt BETWEEN '{$vars['from']}' AND '{$vars['to']}' AND sors.t_orno NOT LIKE '61%' ";
					if ($vars['rep']) {
						$w[] = "sors.t_orno LIKE '{$vars['rep']}%' ";
					}
					if ($vars['cust']) {
						$w[] = "sors.t_cuno = '{$vars['cust']}'";
					}
					if ($vars['status']) {
						$status = $vars['status'];
					}
					$rows = _getLastPartsDataByConstraints($w, $status);
					$caption = "VAT Invoice Status in {$vars['status']} From {$vars['from']} To {$vars['to']}";
				} else {
					$body = $form->toHTML();
				}
				break;
		}

		if (isset($rows, $caption)) {
			$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[3]=xls">XLS version</a>
EOF;
			if ($vars['status'] === 'SHIPPED') {
				$DEFAULT_MENU .= <<<EOF
	&nbsp;|&nbsp;<a href="{$_SERVER['REQUEST_URI']}&m[3]=edit">Quick Edit</a>
EOF;
			}
			// Clean rows
			foreach ($rows AS &$row) {
				$row = array_map('trim', $row);
			}
			unset ($row);
			switch ($m[3]) {
				case 'xls':
					$report = new tldXLS(
						$rows,
						[
							'xItems' => $xItems,
							'showTitles' => true,
						]
					);
					$report->out();
					exit;
					break;
				case 'edit':
					$smarty->assign('nextURL', "$php_self?m[0]=reports&m[1]=edit&m[2]=update");
					$smarty->assign('vat', $rows);
					$body .= $smarty->fetch("$PATH/reports/vat.line.tpl");
					break;
				default:

					$report = new tldReportColumnar($rows,
						[
							'xItems' => $xItems,
							'title' => $caption,
							'showItemNumbers' => true,
							'sumTotalsArray' => ['amount', 't_amta'],
						]
					);
					$body .= $report->fetch();

					break;
			}
		}
		break;

	default:
	    global $kernel;
	    $router = $kernel->getContainer()->get('router');
        $smarty->assign('mip_export_url', $router->generate('sph_export_prices_sph_list'));
		$body .= $smarty->fetch("$PATH/reports/homepage.reports.tpl");
		break;
}

function _getLastPartsDataByConstraints($w, $status = '')
{
	global $email, $DEFAULT_ERP;
	if (!empty($w) && is_array($w)) {
		$WHERE = implode(' AND ', $w);
	}

	$SORT = TldDatabase::escape($sort);

	$query = 'SELECT ';
	$query .= 'sors.t_orno, sors.t_refa, sors.t_refb, sors.t_eono, ';
	$query .= 'sors.t_cuno, sors.t_crep, sors.t_cotp, ';
	$query .= 'SUBSTRING(convert(varchar, sors.t_odat, 120), 0, 11) AS t_odat, ';
	$query .= 'cus.t_nama, ';
	$query .= 'reps.t_info, ';
	$query .= 'sols.t_pono, ';
	$query .= 'sols.t_item, ';
	$query .= "(select min(TLD890.t_dsca) from ttitld890400 TLD890 where TLD890.t_eitm=sols.t_item and TLD890.t_clan like '%CH%') AS ch_dsca, ";
	$query .= 'sols.t_oqua, ';
	$query .= 'sols.t_dqua, ';
	$query .= 'sols.t_bqua, ';
	$query .= 'sols.t_dino, ';
	$query .= 'sols.t_ssls, ';
	$query .= 'sols.t_ttyp + CAST(sols.t_invn AS char) as t_invn, CAST(sols.t_invn AS char) as t_inv,';
	$query .= 'itms.t_dsca, ';
	$query .= 'itms.t_ordr, ';
	$query .= 'inv.t_trdt, ';
	$query .= 'SUBSTRING(convert(varchar, inv.t_trdt, 120), 0, 11) AS trdt, ';
	$query .= 'sods.t_amta, ';
	if ((int)$DEFAULT_ERP === 540) {
		$query .= '(select sum(INV001.t_stoc) ';
		$query .= 'from ttdinv001' . $DEFAULT_ERP . ' INV001 ';
		$query .= ' where INV001.t_item  = sols.t_item ';
		$query .= "and INV001.t_cwar in ('SP1', 'DT2')) as t_stoc, ";
	} else {
		$query .= 'itms.t_stoc, ';
	}

	$query .= 'itms.t_allo ';
	$query .= 'FROM ';
	$query .= 'ttdsls040' . $DEFAULT_ERP . ' as sors ';

	$query .= 'LEFT JOIN ttdsls045' . $DEFAULT_ERP . ' as sols on sors.t_orno=sols.t_orno ';
	$query .= 'LEFT JOIN ttdsls041' . $DEFAULT_ERP . ' as sods on sods.t_orno=sors.t_orno and sols.t_pono=sods.t_pono ';
	$query .= 'LEFT JOIN ttiitm001' . $DEFAULT_ERP . ' AS itms ON sols.t_item=itms.t_item ';
	$query .= 'LEFT JOIN ttccom010' . $DEFAULT_ERP . ' AS cus ON sors.t_cuno=cus.t_cuno ';
	$query .= 'LEFT JOIN ttccom001' . $DEFAULT_ERP . ' AS reps on sors.t_crep=reps.t_emno ';
	$query .= 'LEFT JOIN ttdinv700' . $DEFAULT_ERP . ' AS inv ON inv.t_item=itms.t_item AND inv.t_orno=sols.t_orno ';
	$query .= 'WHERE ' . $WHERE . ' ';
	$query .= 'ORDER BY ' . $SORT . ' ';
	$query .= 'sors.t_orno, sols.t_pono ';
	$rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
	if ((int)$DEFAULT_ERP === 680) {
		foreach ($rows as $key => $val) {
			$invn = $val['t_inv'];
			$query = <<<EOF
						SELECT
							LEFT(trim(vat.vat_date),11) AS vat_date, RIGHT(trim(vat.vat_no),8) AS vat_no, (vat.amount+vat.tax) AS amount, vat.remark
						FROM vat
						WHERE
							vat.invoice_no LIKE trim('$invn')
EOF;
			$ret[] = tldUtils::getSqlToAssocArray($query);
			$t_ninv = $val['t_inv'];
			$t_orno = $val['t_orno'];
			$inv = new tldINV($t_ninv, $DEFAULT_ERP, ['so' => $t_orno]);
			$query = <<<EOF
						SELECT
							*
						FROM vat_comments
						WHERE
							id LIKE trim('$invn')
EOF;
			$comment = tldUtils::getSqlRowToAssocArray($query);
			$header = $inv->getHeader();
			if (count($ret)) {
				$rows[$key]['vat_date'] = $ret[$key][0][0];
				$rows[$key]['vat_no'] = $ret[$key][0][1];
				$rows[$key]['amount'] = $ret[$key][0][2];
				$rows[$key]['status'] = $header['status'];
				$rows[$key]['tobeinvoice'] = $comment['tobeinvoice'];
				$rows[$key]['comment'] = $comment['comment'];
				$rows[$key]['commentdate'] = $comment['date'];
			}
		}
	}
	if ($status == '') {
		return $rows;
	}

	foreach ($rows AS $v) {
		if ($v['status'] == $status) {
			$a[] = $v;
		} elseif ($status === 'INVOICED') {
			if ($v['status'] === 'UNPAID' && !empty($v['vat_no'])) {
				$a[] = $v;
			}
		} elseif ($status === 'SHIPPED') {
			if ($v['status'] === 'UNPAID' && empty($v['vat_no'])) {
				$a[] = $v;
			}
		}
	}
	return $a;
}
