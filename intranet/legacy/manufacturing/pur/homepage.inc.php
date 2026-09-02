<?php
require_once('HTML/QuickForm/autocomplete.php');
$overlib = $smarty->fetch('overlib.inc.js.tpl');
$overlib .= '<script type="text/javascript">$(function(){$(".overlib").overlib()});</script>';
$autosuggest = $smarty->fetch('autosuggest.inc.js.tpl');
$autosuggest .= '<script type="text/javascript">$(function(){var uci=$("[name=t_suno]").autosuggest({message:"Begin to type a portion of the name for auto suggestions",
onChange:function(o){$(uci.input.display).val($(o.input.display).val());
$(uci.input.value).val($(o.input.value).val());}});
});</script>';
$smarty->assign("html_head",$overlib.$autosuggest);

// Get default dashboard vars
if (isset($_GET['erp'])){
    $DEFAULT_ERP = $_GET['erp'];
}
$BU_DASH = new tldLocation(tldLocation::getIDByERP($DEFAULT_ERP));
$ID_DASH = $user->getID();
$TYPE_DASH = NULL;
$DEFAULT_TITLE.="\My Dashboard";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=home">My Dashboard</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=home&m[1]=statistics">Statistics</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=home&m[1]=selectPN">Dashboard by PN</a>
EOF;

$_ACL_DASH_MLM = array("role_MLM","role_MLS","role_planner","role_COO","role_CPO");
if($user->isInGroup($_ACL_DASH_MLM)){
    $DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=home&m[1]=selectBuyer">Dashboard by buyer</a>
EOF;
}
$_ACL_DASH_COST = ["role_MLM","role_MLS","role_planner","role_COO","role_CPO","role_BYR"];
if($user->isInGroup($_ACL_DASH_COST)){
    $DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=home&m[1]=historicCost">Historic cost report</a>
EOF;
}

$statistics = false;

switch($m[1]){
case 'historicCost':
    switch($m[2]){
        case 'csv':
            if(!isset($_SESSION['data_report'])){
                $DEFAULT_ERROR[] = "ERROR: Session not set, data is missing!";
                break;
            }
            $xItems = [
                "erp" => "ERP#",
                "t_item" => "PN#",
                "t_dsca" => "Description",
                "t_oltm" => "Lead Time",
                "t_odat" => "PO date",
                "t_suno" => "PO supplier#",
                "t_nama" => "PO supplier name",
                "t_pric" => "PO cost",
                "t_ccur" => "PO currency",
                "qty" => "Sum of QTY ordered at this price"
            ];
            $report = new tldCSV(
                $_SESSION['data_report'],
                array(
                    "xItems"=>$xItems,
                    "showTitles"=>true
                )
            );
            $report->out();
            unset($_SESSION['data_report']);
            exit;
            break;
    }
    $form = new HTML_QuickForm('frmSelectPN', 'post');
    $form->addElement(  'header', 'title', "Historic item cost report");
    $form->addElement(  'hidden', 'm[0]', 'home');
    $form->addElement(  'hidden', 'm[1]', 'historicCost');
    $form->addElement(  'select', 'erp', 'Factory',
        tldLocation::getERPList("smartyOptions"));
    $form->addElement('text', 'dt_from', 'OPEN date from',
        ["class" => "datepicker"]);
    $form->addElement('text', 'dt_to', 'OPEN date to',
        ["class" => "datepicker"]);
    $form->addElement(  'text', 'sup_from', 'From supplier');
    $form->addElement(  'text', 'sup_to', 'To supplier');
    $form->addElement(  'text', 'PN_from', 'From PN');
    $form->addElement(  'text', 'PN_to', 'To PN');
    $form->addElement(  'submit', 'btnSubmit', 'Submit');
    $form->addRule('dt_from', 'This is required', 'required');
    $form->addRule('dt_to', 'This is required', 'required');
    $form->setDefaults([
        'erp' => tldLocation::getERPByID($user->getBUID()),
        'dt_from' => '2010-10-01',
        'dt_to' => date('Y-m-d'),
    ]);

    if(!$form->validate()){
        $body = $form->toHTML();
        return;
    }
    $vars = tldUtils::cleanupFormInput($form->exportValues());
    $TITLE = "Historic cost report ";
    $query .= <<<SQL
SELECT $erp as erp,RTRIM(T1.t_item) AS t_item,ITM.t_dsca,ITM.t_oltm,
       SUBSTRING(convert(varchar, MAX(T1.t_odat), 120), 0, 11) AS t_odat,
       T1.t_suno AS t_suno, SUP.t_nama AS t_nama,
       T1.t_pric as t_pric, T3.t_ccur as t_ccur,sum(T1.t_oqua) as qty
FROM ttdpur041$erp AS T1
    LEFT JOIN dbo.ttimrp030$erp AS MRP ON T1.t_orno=MRP.t_orno AND T1.t_pono=MRP.t_pono AND MRP.t_koor=2
    LEFT JOIN dbo.ttimrp031$erp AS MRP2 ON T1.t_orno=MRP2.t_orno AND T1.t_pono=MRP2.t_pono AND MRP2.t_koor=2
    LEFT JOIN tld..ttdpur041$erp AS tldpur ON T1.t_orno=tldpur.t_orno AND T1.t_pono=tldpur.t_pono
    LEFT JOIN dbo.ttiitm001$erp AS ITM ON ITM.t_item=T1.t_item
    LEFT JOIN ttdpur040$erp AS T3 ON T1.t_orno=T3.t_orno
    LEFT JOIN ttccom020$erp AS SUP ON T1.t_suno=SUP.t_suno
    LEFT JOIN ttccom001$erp as EMP ON T3.t_ccon=EMP.t_emno
    LEFT JOIN ttttxt010$erp as TXT ON TXT.t_ctxt=T1.t_txta
    LEFT JOIN ttdpur045$erp AS T2 ON T2.t_orno=T1.t_orno AND T2.t_pono=T1.t_pono
WHERE (TXT.t_seqe IS NULL OR TXT.t_seqe = 1) AND T2.t_srnb=( SELECT MAX(T3.t_srnb) FROM ttdpur045$erp AS T3 WHERE T2.t_orno=T3.t_orno AND T2.t_pono=T3.t_pono )
SQL;

    if ('' !== $pnFrom = trim($vars['PN_from'])) {
        $query .= <<<SQL
                AND T1.t_item >= '$pnFrom'
SQL;
        $TITLE .= " , PN > $pnFrom";
    }
    if ('' !== $pnTo = trim($vars['PN_to'])) {
        $query .= <<<SQL
                AND T1.t_item <= '$pnTo'
SQL;
        $TITLE .= " , PN < $pnTo";
    }
    if (('' == $pnTo = trim($vars['PN_to'])) && '' !== $pnFrom) {
        $query .= <<<SQL
                AND T1.t_item <= '$pnFrom'
SQL;
        $TITLE .= " , PN < $pnFrom";
    }
    if ('' !== $supFrom = trim($vars['sup_from'])) {
        $query .= <<<SQL
        AND T1.t_suno >= '$supFrom'
SQL;
        $TITLE .= " , Supplier# > $supFrom";
    }
    if ('' !== $supTo = trim($vars['sup_to'])) {
        $query .= <<<SQL
        AND T1.t_suno <= '$supTo'
SQL;
        $TITLE .= " , Supplier# < $supTo";
    }
        foreach ($vars as $key => $raw) {
            // Construct constraint query
            switch ($key) {
                case "dt_from":
                case "dt_to":
                    try {
                        $date = new DateTime($raw);
                    } finally {
                        $dateTimeErrors = DateTime::getLastErrors();
                    }

                    if (!empty($dateTimeErrors['warning_count']) || !empty($dateTimeErrors['error_count'])) {
                        // do something, there was an error
                        $DEFAULT_ERROR[] = "The provided date is invalid";
                        break;
                    }
                    $date = $date->format('Y-m-d');
                    if ($key == "dt_from") {
                        $query .= " AND DATEDIFF(day,'$date',SUBSTRING(convert(varchar, T1.t_odat, 120), 0, 11))>=0 ";
                        $TITLE .= " , PO Date from $date";
                    }
                    if ($key == "dt_to") {
                        $query .= " AND DATEDIFF(day,'$date',SUBSTRING(convert(varchar, T1.t_odat, 120), 0, 11))<=0 ";
                        $TITLE .= " , PO Date to $date";
                    }
                    break;
            }
        }

    $query .= ' GROUP BY T1.t_pric,T1.t_suno,T1.t_item,ITM.t_dsca,ITM.t_oltm, SUP.t_nama,T3.t_ccur';
        $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    $_SESSION['data_report'] = $rows;
    if(isset($rows)){
        $DEFAULT_MENU.=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="$php_self?m[0]=home&m[1]=historicCost&m[2]=csv">Download to CSV</a>
EOF;
    }
    $xItems = [
        "erp" => "ERP#",
        "t_item" => "PN#",
        "t_dsca" => "Description",
        "t_oltm" => "Lead Time",
        "t_odat" => "PO date",
        "t_suno" => "PO supplier#",
        "t_nama" => "PO supplier name",
        "t_pric" => "PO cost",
        "t_ccur" => "PO currency",
        "qty" => "Sum of QTY ordered at this price"
    ];
    $report = new tldReportColumnar(
        $rows,
        array(
            "xItems"=>$xItems,
            "title"=>$TITLE,
            "showNumberOfRows"=>TRUE,
            "links"=>array(
                "t_orno"=>"$php_self?m[0]=po&m[1]=view&erp=$x&id="
            )
        )
    );
    $body = $report->fetch();
    return;
break;
case 'selectPN':
    $form = new HTML_QuickForm('frmSelectPN', 'post');
    $form->addElement(  'header', 'title', "Enter Part Number for ERP#$DEFAULT_ERP");
    $form->addElement(  'hidden', 'm[0]', 'home');
    $form->addElement(  'hidden', 'm[1]', 'selectPN');
    $form->addElement(  'text', 't_item', 'Item#');
    $form->addElement(  'submit', 'btnSubmit', 'Submit');
    $form->addRule('t_item', 'This is required', 'required');

    if(!$form->validate()){
        $body = $form->toHTML();
        return;
    }

    $vars = tldUtils::cleanupFormInput($form->exportValues());
    // Check item in company
    $itm = new tldITM($vars['t_item'],$DEFAULT_ERP);
    if($itm->isEmpty()){
        $DEFAULT_ERROR[]="ERROR: Item#{$vars['t_item']} not found in $DEFAULT_ERP";
        $body = $form->toHTML();
        return;
    }
    $ITM_DASH = $vars['t_item'];
    $TYPE_DASH = "ITM";
    $statistics = true;
break;
case 'selectBuyer':
    if(!$user->isInGroup($_ACL_DASH_MLM)){
        $DEFAULT_ERROR[]="ERROR: You do not have permissions...";
        break;
    }
    $buyerGrp = new tldGroup("role_BYR",$DEFAULT_ERP);
    $buyers = array_column($buyerGrp->getUserlist(), 'fullname', 'id');
    if(empty($buyers)){
        $DEFAULT_ERROR[]="ERROR: No buyers found for this BU...";
        break;
    }
    $form = new HTML_QuickForm('frmSelectUser', 'post');
    $form->addElement(  'header', 'title', 'Select buyer');
    $form->addElement(  'hidden', 'm[0]', 'home');
    $form->addElement(  'hidden', 'm[1]', 'selectBuyer');
    $form->addElement(  'select', 'uid', 'Buyer', array(""=>"")+$buyers);
    $form->addElement(  'submit', 'btnSubmit', 'Submit');
    $form->addRule('uid', 'This is required', 'required');

    if(!$form->validate()){
        $body = $form->toHTML();
        return;
    }

    $vars = tldUtils::cleanupFormInput($form->exportValues());
    $ID_DASH = $vars['uid'];
    $TYPE_DASH = "BYR";
    $statistics = true;
break;
case 'selectSupplier':
    $body = "This page has been migrated and should not be displayed anymore.";
break;
case 'statistics':
default:
    $statistics = $m[1] === 'statistics';

    // Check if BYR or MLM
    if($user->isInGroup(array_merge($_ACL_DASH_MLM,tldGroup::getManagerGroups()))){
        $TYPE_DASH = "MLM";
    }elseif($user->isInGroup("role_BYR")){
        $TYPE_DASH = "BYR";
    }elseif($user->isInGroup("role_WS")){
        $TYPE_DASH = "Warehouse supervisor";
    }
break;
}

$FLAG_DISPLAY_DASH = TRUE;
// Check type of dashboard
if(empty($TYPE_DASH)){
    $DEFAULT_ERROR[]="WARNING: Could not get info to define the dashboard type...";
    $FLAG_DISPLAY_DASH = FALSE;
}
// Check user to display dashboard
$USER_DASH = new tldUser($ID_DASH);
if(empty($USER_DASH->itsDetails)){
    $DEFAULT_ERROR[]="WARNING: Could not get dashboard, User#$ID_DASH not found...";
    $FLAG_DISPLAY_DASH = FALSE;
}
// Check BU ERP
if(empty($DEFAULT_ERP)){
    $DEFAULT_ERROR[]="WARNING: Could not get dashboard, ERP BU not defined...";
    $DEFAULT_ERROR[]="Reason: Your account is not configured correctly";
    $DEFAULT_ERROR[]="Or you have not selected the BU from MFG/PUR/Change company section";
    $FLAG_DISPLAY_DASH = FALSE;
}

////////////////////////////////////////////////////////
//              STATS REPORTS                         //
////////////////////////////////////////////////////////


if (true === $statistics) {
    if ($FLAG_DISPLAY_DASH) {

        $cells = [];
        $STATS = [
            "po" => NULL,
            "rfq" => NULL,
            "mrp" => NULL,
            "contract" => NULL,
            "qa" => NULL
        ];

        switch ($TYPE_DASH) {
            case 'Warehouse supervisor':
                $baanCompany = new tldBaanERP($DEFAULT_ERP);
                $STATS['inv'] = $baanCompany->countInventoryRawMaterialValueByBuyerByWarehouseByConstraints();
            break;
            case 'ITM':
                $TITLE_DASH = "ITEM dashboard in $DEFAULT_ERP for $ITM_DASH";
                // Get PO stats
                $STATS['po'] = tldPOL::countOpenStatsByItem($DEFAULT_ERP, $ITM_DASH);
                $extra_url_po = "&m[4]=byItem&y=" . $ITM_DASH;
                // Get RFQ stats
                $STATS['rfq'] = tldRFQ::countStatsByItem($DEFAULT_ERP, $ITM_DASH);
                $extra_url_rfq = "&m[3]=byItem&pn=" . $ITM_DASH;
                // Get MRP stats
                $STATS['mrp'] = tldMRP::countStatsByItem($DEFAULT_ERP, $ITM_DASH);
                $extra_url_mrp = "&m[3]=byItem&pn=" . $ITM_DASH;
                // Get NCR stats for last 12 Months
                $date = date('Y-m') . "-01";
                $start = new DateTime($date);
                $start->sub(new DateInterval('P12M'));
                $STATS['qa'] = tldNCR::getPerfStatsByERPByPeriodByConstraints(
                    $BU_DASH->getID(),
                    $start->format('Y-m-d'),
                    date('Y-m-d'),
                    "byItem",
                    ['pn' => $ITM_DASH]
                );
                // Get OTDP stats
                $date = date('Y-m') . "-01";
                $start = new DateTime($date);
                $start->sub(new DateInterval('P3M'));
                $rows = tldERPVendor::getOTDPByPeriodByPart(
                    $DEFAULT_ERP,
                    $start->format('Y-m-d'),
                    $date,
                    $ITM_DASH
                );
                // Calculate avg of OTDP stats
                $reliability = 0;
                $total = 0;
                if (count($rows) > 0) {
                    foreach ($rows as $row) {
                        $reliability += $row['reliable_lines'];
                        $total += $row['total_lines'];
                    }
                    $STATS['qa']['reliability'] = round($reliability * 100 / $total, 2);
                } else {
                    $STATS['qa']['reliability'] = 0;
                }
                $extra_url_qa_otdp = "&m[3]=byItem&y=" . $ITM_DASH . "&ds=" . $start->format('Y-m-d') . "&de=$date";
                // Calculate inventory value
                $baanCompany = new tldBaanERP($DEFAULT_ERP);
                $STATS['inv'] = $baanCompany->countInventoryRawMaterialValueByBuyerByWarehouseByConstraints(['itm.t_item' => $ITM_DASH]);
                $extra_url_inv = "&m[3]=byItem&pn=$ITM_DASH";
                break;
            case 'MLM':
                $TITLE_DASH = $BU_DASH->getShortName() . " dashboard - ERP#" . $DEFAULT_ERP;
                // Get PO stats
                $STATS['po'] = tldPOL::countOpenStatsByConstraints($DEFAULT_ERP);
                // Get RFQ stats
                $STATS['rfq'] = tldRFQ::countStatsByConstraints($DEFAULT_ERP);
                // Get MRP stats
                $STATS['mrp'] = tldMRP::countStatsByConstraints($DEFAULT_ERP);
                // Get Baan contract stats
                // ????
                // Get NCR stats for last 12 Months
                $date = date('Y-m') . "-01";
                $start = new DateTime($date);
                $start->sub(new DateInterval('P12M'));
                $STATS['qa'] = tldNCR::getPerfStatsByERPByPeriodByConstraints(
                    $BU_DASH->getID(),
                    $start->format('Y-m-d'),
                    date('Y-m-d')
                );
                // Get OTDP stats
                $date = date('Y-m') . "-01";
                $start = new DateTime($date);
                $start->sub(new DateInterval('P3M'));
                $rows = tldERPVendor::getOTDPByPeriodByConstraints(
                    $DEFAULT_ERP,
                    $start->format('Y-m-d'),
                    $date
                );
                // Calculate avg of OTDP stats
                $reliability = 0;
                $total = 0;
                if (count($rows) > 0) {
                    foreach ($rows as $row) {
                        $reliability += $row['reliable_lines'];
                        $total += $row['total_lines'];
                    }
                    $STATS['qa']['reliability'] = round($reliability * 100 / $total, 2);
                } else {
                    $STATS['qa']['reliability'] = 0;
                }
                $extra_url_qa_otdp = "&ds=" . $start->format('Y-m-d') . "&de=$date";
                // Calculate inventory value
                $baanCompany = new tldBaanERP($DEFAULT_ERP);
                $STATS['inv'] = $baanCompany->countInventoryRawMaterialValueByBuyerByWarehouseByConstraints();
                $extra_url_inv = "";
                break;
            case 'BYR':
                $TITLE_DASH = "BUYER dashboard - " . $USER_DASH->getFullname();
                // Get PO stats
                $STATS['po'] = tldPOL::countOpenStatsByBuyerEmail($DEFAULT_ERP, $USER_DASH->getEmail());
                $extra_url_po = "&m[4]=byBuyerID&y=" . $USER_DASH->getID();
                // Get RFQ stats
                $STATS['rfq'] = tldRFQ::countStatsByBuyerEmail($DEFAULT_ERP, $USER_DASH->getEmail());
                $extra_url_rfq = "&m[3]=byBuyerID&uid=" . $USER_DASH->getID();
                // Get MRP stats
                $STATS['mrp'] = tldMRP::countStatsByBuyerEmail($DEFAULT_ERP, $USER_DASH->getEmail());
                $extra_url_mrp = "&m[3]=byBuyerID&uid=" . $USER_DASH->getID();
                // Get Baan contract stats
                // ????
                // Get NCR stats for last 12 Months
                $date = date('Y-m') . "-01";
                $start = new DateTime($date);
                $start->sub(new DateInterval('P12M'));
                $STATS['qa'] = tldNCR::getPerfStatsByERPByPeriodByConstraints(
                    $BU_DASH->getID(),
                    $start->format('Y-m-d'),
                    date('Y-m-d'),
                    "byBuyer",
                    ['uid' => $USER_DASH->getID()]
                );
                // Get OTDP stats
                $date = date('Y-m') . "-01";
                $start = new DateTime($date);
                $start->sub(new DateInterval('P3M'));
                $rows = tldERPVendor::getOTDPByPeriodByBuyerEmail(
                    $DEFAULT_ERP,
                    $start->format('Y-m-d'),
                    $date,
                    $USER_DASH->getEmail()
                );
                // Calculate avg of OTDP stats
                $reliability = 0;
                $total = 0;
                if (count($rows) > 0) {
                    foreach ($rows as $row) {
                        $reliability += $row['reliable_lines'];
                        $total += $row['total_lines'];
                    }
                    $STATS['qa']['reliability'] = round($reliability * 100 / $total, 2);
                } else {
                    $STATS['qa']['reliability'] = 0;
                }
                $buyer = $USER_DASH->getID();
                $extra_url_qa_otdp = "&m[3]=byBuyerID&y=" . $USER_DASH->getID() . "&ds=" . $start->format('Y-m-d') . "&de=$date";
                // Calculate inventory value
                $baanCompany = new tldBaanERP($DEFAULT_ERP);
                $STATS['inv'] = $baanCompany->countInventoryRawMaterialValueByBuyerByWarehouseByConstraints();
                $extra_url_inv = "&m[3]=byBuyer&email=" . $USER_DASH->getEmail();
                break;
        }

        // Title of Dashboard --------------------------------->
        $body .= "<h2>$TITLE_DASH</h2>";
        $xItems2 = [
            't_nama' => 'Buyer Name',
            't_emno' => 'Buyer Number',
            'ERP' => 'Company',
        ];
        $erpObj = new tldBaanERP($DEFAULT_ERP);
        $form = new tldAssocTable(
            $erpObj->getEmployeeData($supplier->itsHeader['t_ccon']),
            $xItems2,
            [
                "title" => ""
            ]
        );

        if(!empty($SUNO_DASH)) {
            $body .= $form->fetch();


            $reportUsage = new tldMatrix(
                $revenue[$SUNO_DASH],
                "t_date", "t_suno", "totalInDcur",
                "",
                "Turnover in {$revenue['t_curr']}",
                [
                    "doNotShowTotals" => TRUE,
                    "xItemsRawOrder" => FALSE,
                ]
            );

            $body .= $reportUsage->fetch();
            $company = new tldEvendorCompany($DEFAULT_ERP, $SUNO_DASH);
            $company_info_master = $company->getCompanyClassificationInformation();
            if ($company->isSlave()) {
                $companymaster = new tldEvendorCompany($company_info['master_erp'], $company_info['master_suno']);
                $company_info_master = $companymaster->getCompanyClassificationInformation();
            }
            $smarty->assign('comp', $company_info_master);
            $smarty->assign('purchase_list', $company->getPossibleStatus());
            $smarty->assign('expert_list', tldEvendorCompany::getExpertLevel());
            $body .= $smarty->fetch("$PATH/vendors/view.briefcompany.tpl");

        }

        // PO lines info report ------------------------------->
        $reportPO = new tldAssocTable(
            $STATS['po'],
            [
                "lateVsConfDate" => "Late Order Lines vs Rescheduled date",
                "late" => "Late order lines vs Last confirmed date",
                "unconfirmed" => "Unconfirmed Order Lines",
                "within_seven_days" => "Lines to be delivered within 7 days",
                "within_thirty_days" => "Lines to be delivered within 30 days",
                "within_ninety_days" => "Lines to be delivered within 90 days"
            ],
            [
                "title" => "OPEN PO Lines Statistics",
                "links" => [
                    "lateVsConfDate" => "$php_self?m[0]=po&m[1]=listing&m[2]=byOpenLines&m[3]=byLateVsConfDate$extra_url_po&x=$DEFAULT_ERP&val=",
                    "late" => "$php_self?m[0]=po&m[1]=listing&m[2]=byOpenLines&m[3]=byLate$extra_url_po&x=$DEFAULT_ERP&val=",
                    "unconfirmed" => "$php_self?m[0]=po&m[1]=listing&m[2]=byOpenLines&m[3]=byUnconfirmed$extra_url_po&x=$DEFAULT_ERP&val=",
                    "within_seven_days" => "$php_self?m[0]=po&m[1]=listing&m[2]=byOpenLines&m[3]=byWithin7Days$extra_url_po&x=$DEFAULT_ERP&val=",
                    "within_thirty_days" => "$php_self?m[0]=po&m[1]=listing&m[2]=byOpenLines&m[3]=byWithin30Days$extra_url_po&x=$DEFAULT_ERP&val=",
                    "within_ninety_days" => "$php_self?m[0]=po&m[1]=listing&m[2]=byOpenLines&m[3]=byWithin90Days$extra_url_po&x=$DEFAULT_ERP&val="
                ]
            ]
        );
        $cells[] = $reportPO->fetch();

        // RFQ stats ------------------------------------------>
        $reportRFQ = new tldAssocTable(
            $STATS['rfq'],
            [
                "nb_open" => "RFQ in progress"
            ],
            [
                "title" => "RFQ Statistics",
                "links" => [
                    "nb_open" => "$php_self?m[0]=rfq&m[1]=listing&m[2]=byOpen$extra_url_rfq&erp=$DEFAULT_ERP&val=",
                ]
            ]
        );
        $cells[] = $reportRFQ->fetch();

        // Planned PO (MRP) ------------------------------------------>
        $reportMRP = new tldAssocTable(
            $STATS['mrp'],
            [
                "late" => "Late MRP",
                "within_seven_days" => "MRP within 7 days"
            ],
            [
                "title" => "MRP Statistics",
                "links" => [
                    "late" => "$php_self?m[0]=mrp&m[1]=listing&m[2]=byLate$extra_url_mrp&erp=$DEFAULT_ERP&val=",
                    "within_seven_days" => "$php_self?m[0]=mrp&m[1]=listing&m[2]=byWithin7Days$extra_url_mrp&erp=$DEFAULT_ERP&val=",
                ]
            ]
        );
        $cells[] = $reportMRP->fetch();

        // OTDP & NCR stats ----------------------------------->
        $reportQA = new tldAssocTable(
            $STATS['qa'],
            [
                "reliability" => "OTDP Reliability for previous 3 months (in %)",
            ],
            [
                "title" => "Supplier Performance",
                "links" => [
                    "reliability" => "$php_self?m[0]=po&m[1]=listing&m[2]=byReceiptsPeriod$extra_url_qa_otdp&x=$DEFAULT_ERP&val=",
                ]
            ]
        );
        $cells[] = $reportQA->fetch();

        // Display ---------------------------------->
        $report = new tldHTMLTable(
            $cells,
            [
                "cols" => 4,
                "attribs" => [
                    "table" => " width='100%'",
                    "tr" => " bgcolor='#FFFFFF'"
                ]
            ]
        );
        $body .= $report->fetch();

        // Gross Inventory value stats ----------------------------------->

        $reportINV = new tldMatrix(
            $STATS['inv'],
            "t_cwar", "t_info", "num",
            "$php_self?m[0]=reports&m[1]=inventory&m[2]=byBuyerByWarehouse$extra_url_inv",
            "Gross Inventory value"
        );
        $body.= $reportINV->fetch();

        // Matrix Cancellation and Resceduling --------------------------->

        $cells = [];
        $SUNO_DASH = $vars['t_suno'];
        $BU_DASH = new tldLocation(tldLocation::getIDByERP($DEFAULT_ERP));

        if ($buyer) {
            $buyer = new tldUser($buyer);
            $buyer_email = $buyer->getEmail();
            $form1 = new tldMatrix(
                tldPOL::countCancellationByWHSPddtByERPByBuyer($DEFAULT_ERP, $buyer_email),
                "level", "t_cwar", "num",
                "$php_self?m[0]=reports&m[1]=byCancellationByERPPddt&m[2]=byWHSbyBuyer&z=$buyer_email",
                "PO Cancellation count by urgency, by Warehouse - " . $BU_DASH->getShortName(),
                [
                    "xItems" => ["Urgent", "Week", "Month", ">Month"],
                    "doNotShowXTotals" => true
                ]
            );
            // Matrix Amount PO Line Cancellation By ERP Level
            $form2 = new tldMatrix(
                tldPOL::countValueCancellationByWHSPddtByERPByBuyer($DEFAULT_ERP, $buyer_email),
                "level", "t_cwar", "num",
                "$php_self?m[0]=reports&m[1]=byCancellationByERPPddt&m[2]=byWHSbyBuyer&z=$buyer_email",
                "PO Cancellation EUR Amount, by urgency, by Warehouse - " . $BU_DASH->getShortName(),
                [
                    "xItems" => ["Urgent", "Week", "Month", ">Month"],
                    "doNotShowXTotals" => true
                ]
            );
            $form3 = new tldMatrix(
                tldPOL::countReschedulingByWHSPddtByERPByBuyer($DEFAULT_ERP, $buyer_email, $f1),
                "level", "t_cwar", "num",
                "$php_self?m[0]=reports&m[1]=byReschedulingByERPPddt&m[2]=byWHSbyBuyer&io=$f1&z=$buyer_email",
                "PO Rescheduling count by urgency, by Warehouse - " . $BU_DASH->getShortName(),
                [
                    "xItems" => ["Urgent", "Week", "Month", ">Month"],
                    "doNotShowXTotals" => true
                ]
            );
            // Matrix Amount PO Line Rescheduling By ERP Level
            $form4 = new tldMatrix(
                tldPOL::countValueReschedulingByWHSPddtByERPByBuyer($DEFAULT_ERP, $buyer_email, $f2),
                "level", "t_cwar", "num",
                "$php_self?m[0]=reports&m[1]=byReschedulingByERPPddt&m[2]=byWHSbyBuyer&io=$f2&z=$buyer_email",
                "PO Rescheduling EUR Amount, by urgency, by Warehouse - " . $BU_DASH->getShortName(),
                [
                    "xItems" => ["Urgent", "Week", "Month", ">Month"],
                    "doNotShowXTotals" => true
                ]
            );
        } else {
            $form1 = new tldMatrix(
                tldPOL::countCancellationByWHSPddtByERP($DEFAULT_ERP),
                "level", "t_cwar", "num",
                "$php_self?m[0]=reports&m[1]=byCancellationByERPPddt&m[2]=byWHS",
                "PO Cancellation count by urgency, by Warehouse - " . $BU_DASH->getShortName(),
                [
                    "xItems" => ["Urgent", "Week", "Month", ">Month"],
                    "doNotShowXTotals" => true
                ]
            );
            // Matrix Amount PO Line Cancellation By ERP Level
            $form2 = new tldMatrix(
                tldPOL::countValueCancellationByWHSPddtByERP($DEFAULT_ERP),
                "level", "t_cwar", "num",
                "$php_self?m[0]=reports&m[1]=byCancellationByERPPddt&m[2]=byWHS",
                "PO Cancellation EUR Amount, by urgency, by Warehouse - " . $BU_DASH->getShortName(),
                [
                    "xItems" => ["Urgent", "Week", "Month", ">Month"],
                    "doNotShowXTotals" => true
                ]
            );
            $form3 = new tldMatrix(
                tldPOL::countReschedulingByWHSPddtByERP($DEFAULT_ERP, $f1),
                "level", "t_cwar", "num",
                "$php_self?m[0]=reports&m[1]=byReschedulingByERPPddt&m[2]=byWHS&io=$f1",
                "PO Rescheduling count by urgency, by Warehouse - " . $BU_DASH->getShortName(),
                [
                    "xItems" => ["Urgent", "Week", "Month", ">Month"],
                    "doNotShowXTotals" => true
                ]
            );
            // Matrix Amount PO Line Rescheduling By ERP Level
            $form4 = new tldMatrix(
                tldPOL::countValueReschedulingByWHSPddtByERP($DEFAULT_ERP, $f2),
                "level", "t_cwar", "num",
                "$php_self?m[0]=reports&m[1]=byReschedulingByERPPddt&m[2]=byWHS&io=$f2",
                "PO Rescheduling EUR Amount, by urgency, by Warehouse - " . $BU_DASH->getShortName(),
                [
                    "xItems" => ["Urgent", "Week", "Month", ">Month"],
                    "doNotShowXTotals" => true
                ]
            );
        }

        $cells[] = $form1->fetch();
        $cells[] = $form2->fetch();
        $cells[] = $form3->fetch();
        $cells[] = $form4->fetch();

        function _getIOLink($fnum)
        {
            global $php_self;
            $html = '';
            $params = $_GET;
            $key = "f{$fnum}";
            $filter = $params[$key];
            if ('i' !== $filter) {
                $html .= "<a href='{$php_self}?".http_build_query(array_merge($params, [$key => 'i']))."'>";
            }
            $html .= 'Reschedule In';
            if ('i' !== $filter) {
                $html .= "</a>";
            }
            $html .= ' &nbsp; ';
            if ('o' !== $filter) {
                $html .= "<a href='{$php_self}?".http_build_query(array_merge($params, [$key => 'o']))."'>";
            }
            $html .= 'Reschedule Out';
            if ('o' !== $filter) {
                $html .= "</a>";
            }
            return $html;
        }

        $legends = <<<EOF
<br>
<table>
<tr><th colpsan=2 align="center"><u>Legend:</u></th></tr>
<tr><td>Urgent </td><td>: Delivery Date over due</td></tr>
<tr><td>Week </td><td>: Delivery date within a week</td></tr>
<tr><td>Month </td><td>: Delivery date within a Month</td></tr>
<tr><td>>Month </td><td>: Delivery date superior to a Month</td></tr>
</table>
EOF;
        $cells[] = $legends;

        // Display Matrix Reports
        $report = new tldHTMLTable(
            $cells,
            [
                "cols" => 2,
                "attribs" => ["table" => " width='100%'", "tr" => " bgcolor='#FFFFFF'"],
                "title" => "Common Purchasing matrix reports"
            ]
        );
        $body .= $report->fetch();

        return;
    }
}


////////////////////////////////////////////////////////
//                  FORM & TOOLS                      //
////////////////////////////////////////////////////////

$cells = array();

// PO direct view (middle column)

$middle = NULL;
$form = new HTML_QuickForm('frmByNum', 'post');
$form->addElement(  'hidden', 'm[0]', 'po');
$form->addElement(  'hidden', 'm[1]', 'view');
$form->addElement(  'header', 'title', 'Get PO');
$form->addElement(  'select', 'erp', 'Location',
    tldLocation::getERPList("smartyOptions"));
$form->addElement(  'text',   'id', 'PO#');
$form->addElement(  'submit', 'btnSubmit', 'Submit');
$form->setDefaults(array("erp"=>$DEFAULT_ERP));
$middle.= $form->toHTML();

// last Price change audit (middle column)

$form = new HTML_QuickForm('frmByNum', 'post');
$form->addElement(  'hidden', 'm[0]', 'po');
$form->addElement(  'hidden', 'm[1]', 'listing');
$form->addElement(  'hidden', 'm[2]', 'all');
$form->addElement(  'hidden', 'm[3]', 'byItem');
$form->addElement(  'header', 'title', 'Item price audit');
$form->addElement(  'select', 'x', 'Location',
    tldLocation::getERPList("smartyOptions"));
$form->addElement(  'text',   'y', 'Part Number');
$form->addElement(  'submit', 'btnSubmit', 'Submit');
$form->setDefaults(array("x"=>$DEFAULT_ERP));
$middle.= $form->toHTML();
$cells[] = $middle;

$doctypes = [
    "SALES ORDER ACK", "SALES INVOICE",
    "FINANCE INVOICE", "SALES QUOTATION",
    "PURCHASE ORDER", "PACKING SLIP"
];
$form = new HTML_QuickForm('frmArchive', 'get', "/en/private/finance/finance.php", '', '', true);
$form->addElement('hidden', 'm[0]', 'archive');
$form->addElement('header', 'title', 'Document Archive');
$form->addElement('select', 'erp', 'Company', $erps);
$form->addElement('select', 'doctype', 'Doc type',
    array_combine($doctypes, $doctypes)
);
$form->addElement('text', 'id', 'Doc#', ["size" => 12]);
$form->addElement('submit', 'btnSubmit', 'Submit');
$form->setDefaults(["erp" => $DEFAULT_ERP]);
$cells[] = $form->toHTML() . ' NB. For invoices inc type e.g. SLU22600308';
// Legend's dashboard matrix (right column)

$links = <<<EOF
<h3>Sequences</h3>
<ul>
    <li><a href="/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=mfg.pur.slow_moving_inventory">
    Start new follow up slow moving inventory sequence</a></li>
    <li><a href="/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=mfg.pur.part.cost_roll_request">
    Start new part cost roll request sequence</a></li>
    <li><a href="/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=acct.positiveadj">
    Start a Positive Inventory Adjustment Sequence(BETA)</a></li>
    <li><a href="/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=acct.negativeadj">
    Start a Negative Inventory Adjustment Sequence(BETA)</a></li>
    <li><a href="/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=acct.loanpart">
    Start a Loan Parts Request Sequence(BETA)</a></li>
</ul>
EOF;

// Slow moving parts report (right column)

$query = <<<EOF
SELECT
	bu_id,
	(SELECT location FROM locations 
		WHERE locations.id=bu_id
	) AS bu,
	CASE
		WHEN cur_step=1 THEN 'Inventory'
		WHEN cur_step=2 THEN 'Process'
		WHEN cur_step=3 THEN 'Conclusion'
	END AS step,
	COUNT(*) AS num
FROM
	tasks
WHERE
	tplno=30 AND
	status<>'CLOSED' AND
	cur_step BETWEEN 1 AND 3
GROUP BY
	step,
	bu
EOF;
$rows = tldUtils::getSqlToAssocArray($query);
$form = new tldMatrix(
    $rows,
    "step", "bu", "num",
    "$php_self?m[0]=reports&m[1]=SEQReport",
    "Slow Moving Inventory Sequences",
    array(
        "xItems"=>array("Inventory", "Process", "Conclusion"),
    )
);
$cells[] = $links.$form->fetch();

// Display --------------------------->

$report = new tldHTMLTable(
    $cells,
    array(
        "cols"=>2,
        "attribs"=>array("table"=>" width='100%'","tr"=>" bgcolor='#FFFFFF'"),
        "title"=>"Common Purchasing tools"
    )
);
$body .= $report->fetch();
