<?php
include_once("publications.inc.php");

if(!isset($sess["parts"]["cart"]) || $m[1]=="reset"){
	$sess["parts"]["cart"] = new tldCart();
}
if(isset($_REQUEST['erp_whs'])){
    $sess['CART_ERPS'] = $_REQUEST['erp_whs'];
}
$SPHS = tldLocation::getSPHList("smartyOptions");
$DEFAULT_TITLE .= "/Cart";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=cart&m[1]=" title="Show cart contents">Show Cart</a>
EOF;

if(!$user->isInGroup(array("gg_ADMIN","gg_PARTS","gg_PARTS_AGENTS","role_RME","gg_ENG"))){
    $DEFAULT_ERROR[] = "ERROR: You do not have permissions";
    return;
}

// Cart lang for ALT description
if(isset($selang)){
	$sess["parts"]["cart_lang"] = $selang;
}
if(empty($sess["parts"]["cart_lang"]) || $m[1]=="reset"){
	$sess["parts"]["cart_lang"] = 'EN';
}
$altDesc = tldList::getBaanLanguages();
foreach($altDesc AS $key => $value) {
    $sOptions[] = '<option value="'.$key.'">'.$value.'</option>';
}
$sOptions = implode('', (array)$sOptions);
if($sess["parts"]["cart_lang"]=='CH'){
	header('Content-Type: text/html; charset=utf-8');
	$smarty->assign("lang", "utf8");
}
foreach($sess['parts']['cart']->itsItems AS $pn => &$item){
	$item['item']['OTHER_DESCRIPTION'] = tldCBOM::getDescLang($pn, $sess["parts"]["cart_lang"]);
}

$DEFAULT_MENU.=<<<EOF
&nbsp;|&nbsp;Cart ALT Description Language: <select onchange="window.location.href='$php_self?m[0]=cart&m[1]=&selang='+this.value;"><option>select alt language...</option>$sOptions</select>
EOF;
switch($m[1]){
case 'transfer':
    include('cart/transfer.inc.php');
break;
case 'import':
    switch($m[2]){
    case 'bySN_1':
        if(empty($sn)){
            $DEFAULT_ERROR[] = "ERROR: No SN set...";
            break 2;
        }
        if(empty($_REQUEST['codes'])){
            $DEFAULT_ERROR[] = "ERROR: No PMOC code selected...";
            break 2;
        }
        $rows = tldEquipment::bySN(trim($sn));
        if(count($rows) <> 1){
            $DEFAULT_ERROR[] = "ERROR: Could not identify ER for SN:$sn, pls check and try again.";
            break 2;
        }
        $prno = $rows[0]['t_prno'];
        $loc = tldLocation::byLocationName($rows[0]['man_location']);
        if(empty($loc)){
            $DEFAULT_ERROR[] = "ERROR: Could not identify ERP system for SN:$sn, pls contact product support.";
            break 2;
        }
        $erp = $loc['erp'];
        $myCBOM = new tldCBOM($erp, date("Y-m-d"), $prno);
        if($myCBOM->isEmpty()){
            $DEFAULT_ERROR[] = "ERROR: Could not find a CBOM for SN: $sn, pls contact product support.";
            break 2;
        }
        // Construct constraints
        $a = array();
        foreach($_REQUEST['codes'] as $code=>$val){
            $a[$code] = strtoupper($code);
        }
        if(count($a)>0){
            $constraints = tldUtils::constructWhere($a,"OR");
            $rows2 = $myCBOM->getPMOC($constraints);
        }else{
            $rows2 = $myCBOM->getPMOC();
        }
        // Add cart item
        foreach($rows2 as $row){
            addItemToCart($row['t_sitm'], $row['t_qana'], '', $row['p'].$row['m'].$row['o'].$row['c']);
        }
    break;
    case 'byPMOC':
        $myCBOM = new tldCBOM($erp, $date, $sn);
        if($myCBOM->isEmpty()){
            $DEFAULT_ERROR[] = "No CBOM";
            break;
        }
        $rows = $myCBOM->getPMOC($code);
        if(count($rows)==0){
           $DEFAULT_ERROR[] = "No PMOC found...";
           break;
        }

        foreach($rows as $row){
            $pmoc = $row['p'].$row['m'].$row['o'].$row['c'];
        	addItemToCart($row['t_sitm'], $row['t_qana'], NULL, $pmoc);
        }
    break;
    case 'byRSPL':
        if(empty($id)){
            $DEFAULT_ERROR[] = "ERROR: No SB# set";
            break;
        }
        $man = new manual($id);
        $rows = $man->getRSPL($m[3]);
        if([] === $rows){
           $DEFAULT_ERROR[] = "No RSPL found...";
           break;
        }
        foreach($rows as $row){
            addItemToCart($row['pn'], $row['qty'], '', $row['group_p'].$row['group_m'].$row['group_o'].$row['group_c']);
        }
    break;
    case 'bySB':
        if(empty($id)){
            $DEFAULT_ERROR[] = "ERROR: No SB# set";
            break;
        }
        $sb = new tldSB($id);
        if($sb->isEmpty()){
           $DEFAULT_ERROR[] = "SB#$id not found...";
           break;
        }
        $rows = $sb->getPartsList();
        if(count($rows)==0){
            $DEFAULT_ERROR[] = "WARNING: no parts found under SB#$id";
            break;
        }
        foreach($rows as $row){
            addItemToCart($row['pn'], $row['qty'], '', $row['p'].$row['m'].$row['o'].$row['c']);
        }
    break;
    case 'bySPR':
        if(empty($id)){
            $DEFAULT_ERROR[] = "ERROR: No SPR# set";
            break;
        }
        $spr = new tldSPR($id);
        if($spr->isEmpty()){
           $DEFAULT_ERROR[] = "SPR#$id not found...";
           break;
        }
        $rows = $spr->getLines();
        if(count($rows)==0){
            $DEFAULT_ERROR[] = "WARNING: no parts found under SPR#$id";
            break;
        }
        foreach($rows as $row){
            addItemToCart($row['item'], $row['oqua'], '', $row['p'].$row['m'].$row['o'].$row['c']);
        }
    break;
    case 'byWC':
        if(empty($id)){
            $DEFAULT_ERROR[] = "ERROR: No WC# set";
            break;
        }
        $wc = new tldWC($id);
        if($wc->isEmpty()){
           $DEFAULT_ERROR[] = "WC#$id not found...";
           break;
        }
        $rows = $wc->getParts();
        if(count($rows)==0){
            $DEFAULT_ERROR[] = "WARNING: no parts found under WC#$id";
            break;
        }
        foreach($rows as $row){
            addItemToCart($row['part_number'], $row['quantity'], '', $row['p'].$row['m'].$row['o'].$row['c']);
        }
    break;
    case 'byMSG':
        // Check access
        if(!$user->isInGroup(array("gg_PARTS","role_SPM","gg_ADMIN"))){
			$DEFAULT_ERROR[]="ERROR: Only PARTS have permissions to transmit MSG#$id to CART";
			break;
		}
		// Check params
        if(!is_numeric($id) || empty($id)){
            $DEFAULT_ERROR[] = "ERROR: MSG# empty or invalid";
            break;
        }
        $msg = new tldERPMSG($id);
        if($msg->isEmpty()){
           $DEFAULT_ERROR[] = "ERROR: MSG#$id not found...";
           break;
        }
        $header = $msg->getHeader();
        // MSG data
        $DATA_MSG = $msg->getData();
		// Transform the xml from the "ERP from" to TLD array
		$erpFromObj = tldERP::getERPOb($header['erp_from']);
		$rows = $erpFromObj->outPurchaseOrder($DATA_MSG);
		// Add TLD CUNO
		$cuno = tldBaanERP::getInboundRelation($header['erp_from'],$header['erp_to']);
		if(isset($cuno['t_cuno'])) {
            $rows['header']['customer']['t_cuno'] = $cuno['t_cuno'];
        }
		// Add all data to the session for transfer actions
        $sess["CART_DEFAULT_DATA"] = $rows;
        $sess["CART_DEFAULT_DATA"]['MSG_ID'] = $id;
        // Check lines
        if(count($rows['lines'])==0){
            $DEFAULT_ERROR[] = "WARNING: No parts found for data under MSG#$id";
            break;
        }
        // Add lines to the cart
        foreach($rows['lines'] as $row){
            addItemToCart($row['product'], $row['qty'], '', $row['p'].$row['m'].$row['o'].$row['c']);
        }
    break;
    }
    $body .= getForms();
    $body .= getCart();
    $body .= getCart2();
break;
case 'empty':
    $form = new HTML_QuickForm('cancel', 'get');
	$form->addElement(	'hidden', 'm[0]', 'cart');
	$form->addElement(	'submit', 'btnSubmit', 'Cancel');
    $cancelForm = $form->toHTML();

    $form = new HTML_QuickForm('continue', 'post');
	$form->addElement(	'hidden', 'm[0]', 'cart');
	$form->addElement(	'hidden', 'm[1]', 'empty');
	$form->addElement(	'submit', 'btnSubmit', 'Empty');
    if($form->validate()){
        $sess["parts"]["cart"]->emptyAll();
    }else{
        $DEFAULT_ERROR[] = "Do you really want to empty the contents of your cart?";
        $body .= $cancelForm.
            $form->toHTML();
    }
    $body .= getCart();
break;
case 'export':
    if(!$user->isInGroup(array("gg_ADMIN","gg_PARTS","role_RME","gg_ENG"))){
        $DEFAULT_ERROR[] = "ERROR: You do not have permissions";
        break;
    }
	$erp = new tldBaanERP($DEFAULT_ERP);
    $whs = array();
	foreach($sess['cart']['parts'] as $pn=>$a){
        $row = array();
        $row['item'] = $pn;
        $item = $erp->getItemData($pn);
        $row['EDM_DESCRIPTION'] = $item['DESCRIPTION'];
        $row['ITM_DESCRIPTION'] = $item['ALT_DESCRIPTION'];
        $row['pmoc'] = $a['item']['pmoc'];
        $row['um'] = $item['UM'];
        $row['qty'] = $a['qty'];
        $row['cur'] = $item['CURRENCY'];
        $row['mip'] = $item['MIP'];
        $row['sig'] = $item['t_csig'];
        $invs = $erp->getInvData($pn, "byItemOnly");
        if(count($invs)){
            $row['reop'] = $invs[0]['reop'];
        }
        $invs = $erp->getInvData($pn);
        if(count($invs)){
            foreach($invs as $inv){
                if(!in_array($inv['t_cwar'], $whs)){
                    $whs[] = $inv['t_cwar'];
                }
                $row[$inv['t_cwar']] = $inv['reop'];
            }
        }

        $rows[] = $row;
    }
    $report = new tldXLS(
        $rows,
        array(
            "xItems"=>array(
                "item"=>"PN",
                "EDM_DESCRIPTION"=>"EDM DESCRIPTION",
        		"ITM_DESCRIPTION"=>"ITM DESCRIPTION",
                "qty"=>"QTY",
                "um"=>"UM",
                "cur"=>"CUR",
                "mip"=>"MIP",
                "reop"=>"TOT STK",
                "pmoc"=>"PMOC",
                "sig"=>"SIGNAL CODE"
            )+array_combine((array)$whs, (array)$whs),
            "showTitles"=>true,
            "cellDataType"=>array(
                "item"=>"string"
            )
        )
    );
    $report->out();
    exit;
break;
case 'edit':
	$DEFAULT_ERROR[] = "NOTE: Please set 0 for the quantity to delete a line";
    $smarty->assign('cart', $sess['parts']['cart']->toArray());
	$body .= $smarty->fetch("parts/cart/edit.cart.tpl");
break;
case 'update':
	foreach($qty as $item=>$v){
        if(!is_numeric($qty[$item])){
            $DEFAULT_ERROR[] = "ERROR: Item $item, qty of '{$qty[$item]}' invalid";
        	continue;
        }
        // Case to delete the line (set 0)
        if($qty[$item]==0){
        	$sess['parts']['cart']->remove($item);
        	continue;
        }
        // Else update qty
        $sess['parts']['cart']->update($item, $qty[$item], $back[$item]);
	}
    $body .= getForms();
    $body .= getCart();
break;
case 'retrieve':
	$form = new HTML_QuickForm('form', 'post');
	$form->addElement(	'hidden', 'm[0]', 'cart');
	$form->addElement(	'hidden', 'm[1]', 'retrieve');
	$form->addElement(	'text', 'id', 'ID# of Web Sales Order to Retrieve');
	$form->addElement(	'submit', 'btnSubmit', 'Search');
	if ($form->validate()){
		$wso = new tldWSO($id);
		if($wso->isEmpty()){
            $DEFAULT_ERROR[] = "WARNING: Web Sales Order #$id not found...";
		}else{
			$sess["parts"]["cart"]->emptyAll();
			$parts = $wso->getLines();
			if(count($parts)){
				foreach($parts as $part){
					$sess["parts"]["cart"]->add($part['ITEM'], $part, $part['t_oqua'], $back);
				}
			}else{
                $DEFAULT_ERROR[] = "WARNING: No items found in Web Sales Order #$id...";
			}
		}
	}else{
		if(!$sess['parts']['cart']->isEmpty()){
            $DEFAULT_ERROR[] =<<<EOF
WARNING: You have items in your cart, if you retrieve a saved cart,
  all items in your cart will be removed.
EOF;
		}
		$body .= $form->toHTML();
	}
break;
default:
    if(!empty($id) && !empty($qty) && is_numeric($qty)){
        addItemToCart($id, $qty);
    }
    $body .= getForms();
    $body .= getCart();
    $body .= getCart2();
break;
}

function addItemToCart($id, $qty, $back="", $pmoc=""){
    global $sess, $DEFAULT_ERP, $DEFAULT_ERROR;
    $erp = new tldBaanERP($DEFAULT_ERP);
    $item = $erp->getItemData($id);
    if(empty($item)){
        $DEFAULT_ERROR[] =<<<EOF
    <b>'$id'</b> does not exist in company $DEFAULT_ERP<br>
    You must add it to your ERP system before you can order it.
EOF;
    }else{
        $item['pmoc'] = $pmoc;
        $sess["parts"]["cart"]->add($id, $item, $qty, $back);
    }

}

function getForms(){
    $r = "<table><tr><td>";
    $form = new HTML_QuickForm('form', 'post');
    $form->addElement(	'header', 'title', 'Add to cart');
    $form->addElement(	'hidden', 'm[0]', 'cart');
    $form->addElement(	'text', 'id', 'PN');
    $form->addElement(	'text', 'qty', 'Qty',
        array("size"=>3)
    );
    $form->addElement(	'submit', 'btnSubmit', 'Add to cart');
    $form->setDefaults(array("qty"=>1));
    $r .= $form->toHTML();

    $r .= "</td><td>";
    $form = new HTML_QuickForm('frmSPH', 'post', $php_self, '', '', true);
    $form->addElement(	'hidden', 'm[0]', 'cart');
    $form->addElement(	'header', 'title', "Select SPH");
    $form->addElement(  'checkbox', "erp_whs[300]", 'TLD AME', 'select');
    $form->addElement(  'checkbox', "erp_whs[600]", 'TLD ASI', 'select');
    $form->addElement(  'checkbox', "erp_whs[640]", 'TLD SHA', 'select');
    $form->addElement(  'checkbox', "erp_whs[540]", 'TLD EUR', 'select');
    $form->addElement(	'submit', 'btnSubmit', 'Submit');
    $r .= $form->toHTML();
    $r .= "</td></tr></table>";

    return $r;
}

function getCart(){
    global $smarty, $sess, $DEFAULT_MENU, $DEFAULT_ERP;
    if(!$sess['parts']['cart']->isEmpty()){
    	global $m, $id;
    	if($m[2] == 'byMSG' AND !empty($id)){
    		$transparams = "&msgid=$id";
    	}
        $DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=cart&m[1]=edit" title="Edit cart contents">Edit</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cart&m[1]=empty" title="Empty cart contents">Empty</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cart&m[1]=transfer$transparams" title="Transfer cart to Baan">Transfer</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cart&m[1]=export" title="Export cart contents to Excel file">Export</a>
EOF;
    }
    $smarty->assign('DEFAULT_ERP', $DEFAULT_ERP);

    $erps[] = $DEFAULT_ERP;
    foreach($sess['CART_ERPS'] ?? [] as $cart_erp=>$val){
        if(!in_array($cart_erp, $erps, false)){
            $erps[] = $cart_erp;
        }
    }
    $rows = $sess['parts']['cart']->toArray();

    $erpobj = new tldBaanERP($DEFAULT_ERP);

    $whs[$DEFAULT_ERP] = [];
    foreach ($rows['lines'] as $pn => $a) {
        $item = array_key_exists('ERP', $a['item'] ?? []) && $DEFAULT_ERP === (int) $a['item']['ERP'] ? $a['item'] : $erpobj->getItemData($pn);
        $extrarows[$DEFAULT_ERP][$pn]['cur'] = $item['CURRENCY'];
        $extrarows[$DEFAULT_ERP][$pn]['mip'] = $item['MIP'];
        $extrarows[$DEFAULT_ERP][$pn]['ALT_DESCRIPTION'] = $item['ALT_DESCRIPTION'];
        $extrarows[$DEFAULT_ERP][$pn]['OTHER_DESCRIPTION'] = $item['OTHER_DESCRIPTION'];
        $extrarows[$DEFAULT_ERP][$pn]['TOT Actual Stock'] = $item['stoc'];
        $invs = $erpobj->getInvData($pn, "byItemOnly");
        if ([] !== $invs) {
            $extrarows[$DEFAULT_ERP][$pn]['reop'] = $invs[0]['reop'];
        }
        $invs = $erpobj->getInvData($pn);
        if ([] !== $invs) {
            foreach ($invs as $inv) {
                if (!in_array($inv['t_cwar'], $whs[$DEFAULT_ERP], true)) {
                    $whs[$DEFAULT_ERP][] = $inv['t_cwar'];
                }
                $extrarows[$DEFAULT_ERP][$pn]['inv'][$inv['t_cwar']] = $inv['reop'];
            }
        }
    }
    $smarty->assign('whs', $whs);
    $smarty->assign('colspan_defaultcolspan_erp', 3+count($whs[$DEFAULT_ERP]));
    $smarty->assign('extrarows', $extrarows);
    $smarty->assign('cart', $rows);

    return $smarty->fetch("parts/cart/cart.tpl");
}

function getCart2(){
    global $smarty, $sess, $DEFAULT_MENU, $DEFAULT_ERP, $SPHS;
    if($sess['parts']['cart']->isEmpty()){
        return "<h2>No items in cart...</h2>";
    }
    $start = time();
    /* @todo : uncoment when getCart() replaced totaly by getCart2
    $DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=cart&m[1]=edit" title="Edit cart contents">Edit</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cart&m[1]=empty" title="Empty cart contents">Empty</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cart&m[1]=transfer" title="Transfer cart to Baan">Transfer</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cart&m[1]=export" title="Export cart contents to Excel file">Export</a>
EOF;
*/
    //generate list of erps to iterate over
    $erps[] = $DEFAULT_ERP ?? 300;
    foreach ($sess['CART_ERPS'] ?? [] as $cart_erp => $val) {
        if (!in_array($cart_erp, $erps, false) && $cart_erp != 650 && $cart_erp != 630) {
            $erps[] = $cart_erp;
        }
    }

    $cart = $sess['parts']['cart']->toArray();
    $extrarows = $whs = [];
    foreach($erps as $erp){
        $erpobj = new tldBaanERP($erp);
        $whs[$erp] = [];
        foreach($cart['lines'] as $pn=>$a){
            $item = array_key_exists('ERP', $a['item'] ?? []) && $erp === (int) $a['item']['ERP'] ? $a['item'] : $erpobj->getItemData($pn);
            $extrarows[$erp][$pn]['cur'] = $item['CURRENCY'];
            $extrarows[$erp][$pn]['mip'] = $item['MIP'];
            $extrarows[$erp][$pn]['cuqs'] = $item['UM'];
            $extrarows[$erp][$pn]['sfst'] = $item['t_sfst'];

            $invs = $erpobj->getInvData($pn, "byItemOnly");
            if(count($invs)){
                $extrarows[$erp][$pn]['reop'] = $invs[0]['reop'];
            }
            $invs = $erpobj->getInvData($pn);
            if(count($invs)){
                foreach($invs as $inv){
                    if(!in_array($inv['t_cwar'], $whs[$erp])){
                        $whs[$erp][] = $inv['t_cwar'];
                    }
                    $extrarows[$erp][$pn]['inv'][$inv['t_cwar']]['econ'] = $inv['econ'];
                    $extrarows[$erp][$pn]['inv'][$inv['t_cwar']]['sfst'] = $inv['sfst'];
                    $extrarows[$erp][$pn]['inv'][$inv['t_cwar']]['reop'] = $inv['reop'];
                }
            }
        }
    }
    $r = <<<EOF
<h3>Cart Contents (NEW)</h3>
<table border="1" cellspacing="0" cellpadding="1" class="smalltext" bordercolor="#FFFFFF" width="100%">
<tr bgcolor="#D0D0D0">
    <th colspan="11">&nbsp;</th>
EOF;

    foreach($erps as $erp){
        $r .= "<th colspan='";
        $r .= 5+2*count($whs[$erp]);
        $r .= "'>".$SPHS[$erp]."</th>";
    }
    $r .=<<<EOF
    <th colspan="2">&nbsp;</th>
</tr>
<tr bgcolor="#D0D0D0">
    <th colspan="11">&nbsp;</th>
EOF;
    foreach($erps as $erp){
        $r .= "<th colspan='3'>Item Data</th>";
        foreach($whs[$erp] as $wh){
            $r .= "<th colspan='2'>$wh</th>";
        }
        $r .= "<th colspan='2'>Total</th>";
    }
    $r .=<<<EOF
    <th colspan="2">Combined</th>
</tr>
<tr bgcolor="#D0D0D0">
    <th>Item</th>
    <th>PN</th>
    <th>EDM Description</th>
    <th>ALT Description</th>
    <th>ITM Description</th>
    <th>P</th>
    <th>M</th>
    <th>O</th>
    <th>C</th>
    <th>Qty</th>
    <th>UM</th>
    <th>Signal Code</th>
EOF;
    foreach($erps as $erp){
        $r .= "<th>Cur</th><th>MIP</th><th>UM</th>";
        foreach($whs[$erp] as $wh){
            $r .= "<th>Safety</th>";
            $r .= "<th>Reop</th>";
        }
        $r .= "<th>Safety</th><th>Reop</th>";
    }
    $r .=<<<EOF
    <th>Tot Safety</th>
    <th>Tot Reop</th>
</tr>
EOF;
    $i = 0;
    $sess['cart']['parts'] = $cart['lines'];
    foreach ($cart['lines'] as $pn => $line) {
        $i++;
        $tot_qty = 0;
        $tot_sfst = 0;
        $item = $line['item'];
        $pmoc = [
            "P" => (strpos($item['pmoc'], "P") !== false) ? "P" : "&nbsp;",
            "M" => (strpos($item['pmoc'], "M") !== false) ? "M" : "&nbsp;",
            "O" => (strpos($item['pmoc'], "O") !== false) ? "O" : "&nbsp;",
            "C" => (strpos($item['pmoc'], "C") !== false) ? "C" : "&nbsp;",
        ];
        $r .= <<<EOF
<tr>
    <td>$i</td>
    <td><a href="$php_self?m[0]=inv&m[1]=view&id=$pn">$pn</a></td>
    <td>${item['DESCRIPTION']}</td>
    <td>${item['OTHER_DESCRIPTION']}</td>
    <td>${item['ALT_DESCRIPTION']}</td>
    <td>${pmoc['P']}</td>
    <td>${pmoc['M']}</td>
    <td>${pmoc['O']}</td>
    <td>${pmoc['C']}</td>
    <td>${line['qty']}</td>
    <td>${item['UM']}</td>
    <td>${item['t_csig']}</td>
EOF;
        foreach($erps as $erp){
            $extrarow = $extrarows[$erp][$pn];
            $erp_tot_qty = 0;
            $erp_tot_sfst = $extrarow['sfst'];

            $row =<<<EOF
    <td%FLAG_TD%>${extrarow['cur']}</td>
    <td%FLAG_TD%>${extrarow['mip']}</td>
    <td%FLAG_TD%>${extrarow['cuqs']}</td>
EOF;
            $inv = $extrarow['inv'];
            foreach($whs[$erp] as $wh){
                $erp_tot_sfst += $inv[$wh]['sfst'];
                $row .= "<td%FLAG_TD%>";
                $row .= $inv[$wh]['sfst']==0 ? "&nbsp;" : $inv[$wh]['sfst'];
                $row .= "</td>";

                $erp_tot_qty += $inv[$wh]['reop'];
                $row .= "<td%FLAG_TD%>";
                $row .= $inv[$wh]['reop']==0 ? "&nbsp;" : $inv[$wh]['reop'];
                $row .= "</td>";
            }
            $tot_sfst += $erp_tot_sfst;
            $tot_qty += $erp_tot_qty;
            $row .=<<<EOF
    <td bgcolor="FFCC99"%FLAG_SFTOT_TD%>$erp_tot_sfst</td>
    <td bgcolor="CCFF00"%FLAG_ECTOT_TD%>$erp_tot_qty</td>
EOF;
			if($erp_tot_qty < $line['qty']){
                $row = str_replace(['%FLAG_TD%', '%FLAG_SFTOT_TD%', '%FLAG_ECTOT_TD%'], ' style="color:#fff;font-weight:bold;background-color:#900;"', $row);
            }else{
                $row = str_replace(['%FLAG_TD%', '%FLAG_SFTOT_TD%', '%FLAG_ECTOT_TD%'], ['', ' style="background-color:#fc9;"', ' style="background-color:#cf0;"'], $row);
            }
            $r .= $row;
        }
        $r .=<<<EOF
    <td bgcolor="FFCC99">$tot_sfst</td>
    <td bgcolor="CCFF00">$tot_qty</td>
</tr>
EOF;
    }
    return $r."</table>".(time()-$start)."secs";
}

