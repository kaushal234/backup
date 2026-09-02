<?php

if(!$user->isInGroup(array("gg_ADMIN","gg_PARTS"))){
    $DEFAULT_ERROR[] = "ERROR: You do not have permissions";
    return;
}

$DEFAULT_TITLE .= "\Customer account";

switch($m[1]){
	case 'view':
		$DEFAULT_MENU .=<<<EOF
			<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="$php_self?m[0]=customer_account&m[1]=step1">Switch SPH</a>&nbsp;|
			<a href="$php_self?m[0]=customer_account&m[1]=step2&sph=$sph">Switch customer</a>&nbsp;|
			<a href="$php_self?m[0]=customer_account&m[1]=view&m[2]=open_line_items&sph=$sph&id=$id">Open line items</a>&nbsp;|
			<a href="$php_self?m[0]=customer_account&m[1]=view&m[2]=shipped_line_items&sph=$sph&id=$id">Shipped line items</a>
EOF;
		switch($m[2]) {
			case 'open_line_items':
				$DEFAULT_TITLE .= "\Open Line Items (BETA)";
				$smarty->assign("id",$id);
				$smarty->assign('sph',$sph);
				$body .= $smarty->fetch("$PATH/customer_account/homepage.open_line_items.tpl");
				$rows = getOpenLineItems($id, $target, $sph);
				if(count($rows)==0) {
					$DEFAULT_ERROR[] = "No open line items for customer '$id'.";
				} else {
					$body .= getGeneralTab('open', "Open line items for customer '$id'...", $rows);
				}
				break;
			case 'shipped_line_items':
				$DEFAULT_TITLE .= "\Shipped Line Items (BETA)";
				$rows = getShippedLineItems($id, $target, $sph);
				if(count($rows)==0) {
					$DEFAULT_ERROR[] = "No shipped line items for customer '$id'.";
				} else {
					$smarty->assign("id",$id);
					$smarty->assign('sph',$sph);
					$body .= $smarty->fetch("$PATH/customer_account/homepage.shipped_line_items.tpl");
					$body .= getGeneralTab('shipped', "Shipped line items for customer '$id'...", $rows);
				}
				break;
		}
		break;
	case 'step2':
		if($t_cuno || $t_nama) {
			if($btnSubmitCustomerIdOpenLineItems || $btnSubmitCustomerIdShippedLineItems) {
				$customer = $t_cuno;
			} else {
				$customer = $t_nama;
			}
			if($btnSubmitCustomerIdOpenLineItems || $btnSubmitCustomerNameOpenLineItems) {
				$type = 'open_line_items';
			} else {
				$type = 'shipped_line_items';
			}
			header("Location: $php_self?m[0]=customer_account&m[1]=view&m[2]=$type&sph=$sph&id=$customer");
		} else {
			$smarty->assign('title','Step 2 : Choose your customer');
			$smarty->assign('customers',getCustomers($sph));
			$smarty->assign('sph',$sph);
			$body .= $smarty->fetch("$PATH/customer_account/customer.form.tpl");
		}
		break;
	default:
	case 'step1':
		if($sph) {
			header("Location: $php_self?m[0]=customer_account&m[1]=step2&sph=$sph");
		} else {
			$smarty->assign('title','Step 1 : Choose your Spare Parts Hub');
			//$smarty->assign('sph',tldLocation::getSPHList("smartyOptions"));
			$body .= $smarty->fetch("$PATH/customer_account/sph.form.tpl");
		}
		break;
}

function getOpenLineItems($customer,$target, $sph) {
	switch($sph) {
		case 500:
		case 520:
		case 540:
		case 300:
		case 600:
		case 640:
		case 680:
			$query=<<<EOF
				SELECT TOP 50
					't_odat' = CONVERT(VARCHAR, T1.t_odat, 112),
					T1.t_orno,
					RTRIM(T1.t_item) AS t_item,
					T1.t_oqua,
					't_dqua' = CASE WHEN (T1.t_dqua = 0)
						THEN T1.t_oqua
						ELSE T1.t_bqua
					END,
					't_ddta' = CONVERT(VARCHAR, T1.t_ddta, 112),
					'DEARLY' = DATEDIFF(day, GETDATE(), T1.t_ddta),
					RTRIM(T3.t_dsca) AS t_dsca,
					RTRIM(T4.t_aitc) AS t_aitc,
					(CASE WHEN (T5.t_eono <> ' ')
						THEN T5.t_eono
						ELSE T5.t_refa
					END) as t_eono
				FROM
					ttdsls041$sph AS T1
					LEFT JOIN ttdsls045$sph AS T2 ON T1.t_orno = T2.t_orno AND T1.t_pono = T2.t_pono
					LEFT JOIN ttiitm001$sph AS T3 ON T1.t_item = T3.t_item
					LEFT JOIN ttiitm012$sph AS T4 ON T1.t_item = T4.t_item AND T1.t_cuno = T4.t_cuno
					LEFT JOIN ttdsls040$sph AS T5 ON T1.t_orno = T5.t_orno
					LEFT JOIN ttccom010300 AS T6 ON T5.t_cuno = T6.t_cuno
				WHERE
					((T2.t_ssls < 4 AND T1.t_dqua < T1.t_oqua) OR (T2.t_ssls = 9 AND T1.t_bqua <> 0))
					AND T3.t_citg < 20000
EOF;
			if($target) {
				$query .=<<<EOF
					AND (
						COALESCE(UPPER(RTRIM(T1.t_item)),'') + '~' +
						COALESCE(UPPER(RTRIM(T5.t_eono)),'') + '~' +
						COALESCE(UPPER(T1.t_orno),'') + '~' +
						COALESCE(UPPER(RTRIM(T4.t_aitc)),'')
						LIKE UPPER('%$target%')
					)
EOF;
			}
			if($customer) {
				$query .= <<<EOF
					AND UPPER(T1.t_cuno) = UPPER('$customer')
EOF;
			}
			$query.=<<<EOF
				ORDER BY t_odat DESC
EOF;
			$opt1="odbc";
			$opt2=array("src"=>"baan");
			break;
	}
	$rows = tldUtils::getSqlToAssocArray($query, $opt1, $opt2);
	foreach($rows as &$row) {
		if($row['DEARLY'] < 0) {
			$row['DLATE'] = substr($row['DEARLY'],1);
			unset($row['DEARLY']);
		}
	}
	return $rows;
}


function getShippedLineItems($customer, $target, $sph) {
	switch($sph) {
		case 500:
		case 520:
		case 540:
		case 300:
		case 600:
		case 640:
		case 680:
			$query=<<<EOF
				SELECT TOP 50
					T1.t_cuno,
					T1.t_orno,
					T1.t_dino,
					RTRIM(T1.t_item) AS t_item,
					T1.t_dqua,
					CONVERT(VARCHAR, T1.t_ddat, 112) AS t_ddat,
					(CASE WHEN (T2.t_eono <> ' ')
						THEN T2.t_eono
						ELSE T2.t_refa
					END) as t_eono,
					RTRIM(T4.t_aitc) AS t_aitc,
					RTRIM(T5.t_dsca) AS t_dsca
				FROM
					ttdsls045$sph AS T1
					LEFT JOIN ttdsls040$sph AS T2 ON T1.t_orno = T2.t_orno
					LEFT JOIN ttdsls041$sph AS T3 ON T1.t_orno = T3.t_orno AND T1.t_pono = T3.t_pono
					LEFT JOIN ttiitm012$sph AS T4 ON T1.t_item = T4.t_item AND T1.t_cuno = T4.t_cuno
					LEFT JOIN ttiitm001$sph AS T5 ON T1.t_item = T5.t_item
				WHERE
					T1.t_dqua > 0
					AND T5.t_citg < 20000
EOF;
			if($target) {
				$query .=<<<EOF
					AND (
						COALESCE(UPPER(RTRIM(T1.t_item)),'') + '~' +
						COALESCE(UPPER(RTRIM(T2.t_eono)),'') + '~' +
						COALESCE(UPPER(T1.t_orno),'') + '~' +
						COALESCE(UPPER(RTRIM(T4.t_aitc)),'')  + '~' +
						COALESCE(UPPER(RTRIM(T1.t_dino)),'')
						LIKE UPPER('%$target%')
					)
EOF;
			}
			if($customer) {
				$query .= <<<EOF
					AND UPPER(T1.t_cuno) = UPPER('$customer')
EOF;
			}
			$query.=<<<EOF
				ORDER BY T1.t_ddat DESC
EOF;
		$opt1 = "odbc";
		$opt2 = ["src" => "baan"];
	}
	return tldUtils::getSqlToAssocArray($query, $opt1, $opt2);
}


function getGeneralTab($type, $title, $rows) {
	if($type == 'open') {
		$fields = array(
			"t_odat"=>"Date",
			"t_eono"=>"PO#",
			"t_orno"=>"SO#",
			"t_aitc"=>"Customer P/N",
			"t_item"=>"TLD P/N",
			"t_dsca"=>"Description",
			"t_oqua"=>"Qty Ordered",
			"t_dqua"=>"Qty to ship",
			"t_ddta"=>"Promised date",
			"DEARLY"=>"Days early",
			"DLATE"=>"Days late"
		);
	} else {
		$fields = array(
			"t_eono"=>"PO#",
			"t_orno"=>"SO#",
			"t_dino"=>"Packing slip",
			"t_aitc"=>"Customer P/N",
			"t_item"=>"TLD P/N",
			"t_dsca"=>"Description",
			"t_dqua"=>"Qty Shipped",
			"t_ddat"=>"Shipping date"
		);
	}
	$report = new tldReportColumnar(
		$rows, [
			"xItems" => $fields,
			"title" => $title,
		]
	);
	return $report->fetch();
}


function getCustomers($sph) {
	if(in_array($sph,array(520,540))) $sph=500;
	switch($sph) {
		case 500:
		case 300:
		case 600:
		case 640:
		case 680:
			$query=<<<EOF
				SELECT DISTINCT
					RTRIM(t_cuno) AS t_cuno,
					RTRIM(t_nama) AS t_nama
				FROM ttccom010$sph
				ORDER BY t_nama
EOF;
		$opt1 = "odbc";
		$opt2 = ["src" => "baan"];
		break;
	}
	return tldUtils::getSqlToAssocArray($query, $opt1, $opt2);
}

