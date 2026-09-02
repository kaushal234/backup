<?php
$DEFAULT_TITLE .= "\Reports";
$DEFAULT_MENU .= <<<EOF
<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=reports">Home</a>
EOF;

switch ($m[1]) { 
    case 'listing':
        switch ($m[2]) {
            case 'APreport':
                if (!$user->isInGroup(["gg_ADMIN", "gg_ACCT", "superuser", "role_CFO", "role_FC"])) {
                    echo "You do not have permissions for this page..";
                    exit;
                }
                $smarty = tldUtils::getSmarty("intranet");
                header('Content-Type: text/html; charset=utf-8');
                $smarty->assign("lang", "utf8");

                $xItems = [
                    "t_suno" => "Vendor Code",
                    "t_nama" => "Vendor Name",
                    "t_ttyp" => "Transaction Type",
                    "t_ninv" => "DOC#",
                    "t_isup" => "Supplier Inv#",
                    "t_orno" => "Our PO#",
                    "t_docd" => "DOC date",
                    "t_dued" => "Due date",
                    "t_ccur" => "Currency",
                    "t_amti" => "Amt in PO Cur",
                    "t_amth" => "Amt in local currency",
                    "t_balc" => "Bal in PO Cur",
                    "t_balh" => "Bal in local currency",
                ];

                // Get listing
                $erpList = tldLocation::getERPList("smartyOptions");
                // Get form
                $form = new HTML_QuickForm('frmAPreport', 'get', "", "", "", true);
                $form->addElement('hidden', 'm[0]', 'reports');
                $form->addElement('hidden', 'm[1]', 'listing');
                $form->addElement('hidden', 'm[2]', 'APreport');
                $form->addElement('header', 'title', 'Select values:');
                $form->addElement('select', 'z', 'Company#', ["" => ""] + $erpList);
                $form->addElement('text', 'cunoN', 'Supplier number');
                $form->addElement('text', 'trantype', 'Transcation Type');
                $form->addElement('text', 'invnN', 'Invoice number');

                $form->addElement('date', 'invx', 'Invoice Issue From',
                    ["format" => "Y-m-d", "minYear" => date('Y') - 2, "maxYear" => date('Y')]);
                $form->addElement('date', 'invy', 'Invoice Issue To',
                    ["format" => "Y-m-d", "minYear" => date('Y') - 2, "maxYear" => date('Y')]);
                $form->addElement('date', 'payx', 'Payment From',
                    ["format" => "Y-m-d", 'addEmptyOption' => TRUE, "minYear" => date('Y') - 2, "maxYear" => date('Y') + 1]);
                $form->addElement('date', 'payy', 'Payment To',
                    ["format" => "Y-m-d", 'addEmptyOption' => TRUE, "minYear" => date('Y') - 2, "maxYear" => date('Y') + 1]);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->addRule('invx', 'This is required', 'required');
                $form->addRule('invy', 'This is required', 'required');
                $form->addRule('z', 'This is required', 'required');

                $form->setDefaults(["invx" => date('Y-m-d', strtotime('-90 days'))]);
                $form->setDefaults(["invy" => date('Y-m-d')]);
                $form->setDefaults(["payx" => date('Y-m-d', strtotime('-90 days'))]);
                $form->setDefaults(["payy" => date('Y-m-d')]);
                if ($form->validate()) {
                    $vars = tldUtils::cleanupFormInput($form->exportValues());
                    $vars['invfrom'] = implode('-', $vars['invx']);
                    $vars['invto'] = implode('-', $vars['invy']);
                    foreach ($_REQUEST as $key => $raw) {
                        switch ($key) {
                            case "payx";
                                try {
                                    $date = new DateTime(implode("-", $raw));
                                } catch (Exception $e) {
                                    continue 2;
                                }
                                $date = $date->format('Y-m-d');
                                $a = " AND t1.t_dued>='$date' ";
                                break;
                            case "payy";
                                try {
                                    $date = new DateTime(implode("-", $raw));
                                } catch (Exception $e) {
                                    continue 2;
                                }
                                $date = $date->format('Y-m-d');
                                $b = " AND t1.t_dued<='$date' ";
                                break;
                        }
                    }

                    $query = "select ";
                    $query .= "RTRIM(t1.t_suno) AS t_suno, ";
                    $query .= "(SELECT sup.t_nama FROM ttccom020{$vars['z']} AS sup WHERE sup.t_suno=t1.t_suno) t_nama, ";
                    $query .= "t1.t_ttyp t_ttyp, ";
                    $query .= "t1.t_ninv t_ninv, ";
                    $query .= "t1.t_orno t_orno, ";
                    $query .= "SUBSTRING(CONVERT(VARCHAR, t1.t_docd, 120), 0, 11) AS t_docd, ";
                    $query .= "SUBSTRING(CONVERT(VARCHAR, t1.t_dued, 120), 0, 11) AS t_dued, ";
                    $query .= "t1.t_ccur t_ccur, ";
                    $query .= "t1.t_amti t_amti, ";
                    $query .= "t1.t_amth t_amth, ";
                    $query .= "t1.t_balc t_balc, ";
                    $query .= "t1.t_balh t_balh, ";
                    $query .= "t1.t_orno t_orno, ";
                    $query .= "t1.t_isup t_isup  ";

                    $query .= "FROM ttfacp200{$vars['z']} AS t1  ";
                    $query .= "WHERE 1=1 ";

                    if ($vars['invnN'] <> '') {
                        $query .= "and t1.t_ninv='{$vars['invnN']}' ";
                    }
                    if ($vars['trantype'] <> '') {
                        $query .= "and t1.t_ttyp LIKE '{$vars['trantype']}' ";
                    }
                    if ($vars['cunoN'] <> '') {
                        $query .= "and t1.t_suno LIKE '{$vars['cunoN']}' ";
                    }

                    $query .= "and (t1.t_docd>='" . $vars['invfrom'] . "' ";
                    $query .= "and t1.t_docd<='" . $vars['invto'] . "') ";
                    if ($a) {
                        $query .= $a;
                    }
                    if ($b) {
                        $query .= $b;
                    }
                    $rows = tldUtils::getSqlToAssocArray($query, "odbc", ["src" => "baan"]);
                    $caption = "AP report from {$vars['invfrom']} to {$vars['invto']} for company {$vars['z']}";
                }//else{
                $body = $form->toHTML();
                //}
                break;

            case 'HandBookFinishGoodList':
                $xItems = [
                    "t_item" => "PN",
                    "t_dsca" => "Description",
                    "t_cuni" => "Unit",
                    "t_stoc" => "On Hand",
                    "t_quan"=>"Quantity",
                    "t_copr" => "StdCost",
                    "t_cprj" => "Project",
                    "t_psts" =>"Status",
                    "t_clot"=>"ER#"
                ];

                // Get listing
                if($erp == 640){
                    $man= "AND man_location LIKE 'TLD SHA'";
                }
                if($erp == 660){
                    $man= "AND man_location LIKE 'TLD WUX'";
                }
                $query = <<<EOF
SELECT t_prno
FROM service
WHERE date_shipped = '0000-00-00'  $man
EOF;

                $proj = tldUtils::getSqlToAssocArray($query);
                foreach ($proj as $key=>$val){
                    foreach($val as $k=>$v){
                        if (!empty($v)){
                            $projs .= trim($v).',';
                        }
                    }
                }
                $list = rtrim($projs,',');
                $query = "select ";
                $query .= "ITM001.t_item t_item, ITM001.t_dsca, ITM001.t_cuni, INV700.t_cprj, PCS020.t_psts,ITM001.t_stoc t_stoc, LTC001.t_clot t_clot, INV001.t_stoc t_dfstoc,CONVERT(VARCHAR(100), CAST(ITM001.t_copr AS DECIMAL(15,2))) t_copr, ";
                $query .= "ABS(INV700.t_quan) t_quan ";
                $query .= "FROM ttdinv001$erp INV001 ";
                $query .= "LEFT JOIN ttdinv700$erp INV700 ON INV001.t_item=INV700.t_item and INV700.t_cwar=INV001.t_cwar ";
                $query .= "LEFT JOIN ttiitm001$erp ITM001 ON INV700.t_item=ITM001.t_item  ";

                $query .= "LEFT JOIN ttcmcs003$erp MCS003 ON INV700.t_cwar=MCS003.t_cwar and MCS003.t_nwrh=1 and MCS003.t_cwar='$warehouse'";
                $query .= "LEFT JOIN ttipcs020$erp PCS020 ON PCS020.t_cprj=INV700.t_cprj ";
                $query .= "LEFT JOIN ttisfc001$erp SFC001 ON SFC001.t_cprj=PCS020.t_cprj ";
                $query .= "LEFT JOIN ttdltc001$erp LTC001 ON LTC001.t_cprj = INV700.t_cprj AND LTC001.t_cprj<>'' ";

                $query .= "WHERE INV001.t_cwar LIKE '$warehouse'
                 AND PCS020.t_psts in (5,6) AND INV700.t_koor=1 AND PCS020.t_cprj in ($list)  AND ITM001.t_item = '$t_item' ";

                $rows = tldUtils::getSqlToAssocArray($query, "odbc", ["src" => "baan"]);

                $caption = "HandBook Finish goods report for company $erp $warehouse Warehouse";
                $links = [
                    "t_cprj" => [
                        "url" => "/en/private/manufacturing/eng/dev.php?m%5B0%5D=cbom&m%5B1%5D=view&erp=$erp&btnSubmit=Submit",
                        "params" => [
                            "sn" => "t_cprj"
                        ],
                    ]
                ];
                break;
            case 'HandBookWIPList':
                $xItems = [
                    "t_item" => "PN",
                    "t_dsca" => "Description",
                    "t_cuni" => "Unit",
                    "t_stoc" => "On Hand",
                    "t_dfstoc" => $warehouse,
                    "t_quan" => "Quantity",
                    "t_copr" => "StdCost",
                    "t_cprj"=>"Project",
                    "t_clot"=>"ER#"
                ];

                $query1 =<<<SQL
                select ITM001.t_item t_item, ITM001.t_dsca t_dsca, ITM001.t_cuni t_cuni, ITM001.t_stoc t_stoc, LTC001.t_clot t_clot, INV001.t_stoc t_dfstoc,CONVERT(VARCHAR(100),
                CAST(ITM001.t_copr AS DECIMAL(15,2))) t_copr, PCS020.t_cprj, ABS(INV700.t_quan) AS t_quan
FROM ttdinv001$erp INV001
  LEFT JOIN ttdinv700$erp INV700 ON INV001.t_item=INV700.t_item and INV700.t_cwar=INV001.t_cwar
  LEFT JOIN ttiitm001$erp ITM001 ON INV700.t_item=ITM001.t_item
  LEFT JOIN ttcmcs003$erp MCS003 ON INV700.t_cwar=MCS003.t_cwar and MCS003.t_nwrh=1 and MCS003.t_cwar='$warehouse'
  LEFT JOIN ttipcs020$erp PCS020 ON PCS020.t_cprj=INV700.t_cprj
  LEFT JOIN ttisfc001$erp SFC001 ON SFC001.t_cprj=PCS020.t_cprj
  LEFT JOIN ttdltc001$erp LTC001 ON LTC001.t_cprj = INV700.t_cprj AND LTC001.t_cprj<>''
WHERE INV001.t_cwar LIKE '$warehouse' AND INV700.t_koor=1
            AND ITM001.t_item like '{$t_item}' AND PCS020.t_psts = 3
SQL;

                $rows = tldUtils::getSqlToAssocArray($query1, "odbc", ["src" => "baan"]);
                $caption = "HandBook WIP list for company $erp $warehouse Warehouse";
                $links = [
                    "t_cprj" => [
                        "url" => "/en/private/manufacturing/eng/dev.php?m%5B0%5D=cbom&m%5B1%5D=view&erp=$erp&btnSubmit=Submit",
                        "params" => [
                            "sn" => "t_cprj"
                        ],
                    ]
                ];
                break;

            case 'APInvoicesControl_Receipts':
                $xItems = [
                    "w_suno" => "Supplier",
                    "w_orno" => "Purchase order",
                    "w_pono" => "Position",
                    "w_srnb" => "Sequence number",
                    "w_item" => "Item",
                    "w_dsca" => "Description",
                    "w_date" => "Receipt date",
                    "w_dqua" => "Delivered qty",
                    "w_bqua" => "Back order qty",
                    "w_quad" => "Rejected qty",
                ];
                // Get listing
                $erpList = tldLocation::getERPList("smartyOptions");

                // Get form
                $form = new HTML_QuickForm('frmAPInvoicesControl_Receipts', 'get', "", "", "", true);
                $form->addElement('hidden', 'm[0]', 'reports');
                $form->addElement('hidden', 'm[1]', 'listing');
                $form->addElement('hidden', 'm[2]', 'APInvoicesControl_Receipts');
                $form->addElement('header', 'title', 'Select parameters:');
                $form->addElement('text', 'o', 'Order');
                $form->addElement('text', 'linef', 'Line from');
                $form->addElement('text', 'linet', 'Line to');
                $form->addElement('select', 'z', 'Company#',
                    ["" => ""] + $erpList);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->addRule('z', 'This is required', 'required');

                if ($form->validate()) {
                    $vars = tldUtils::cleanupFormInput($form->exportValues());

                    $rows = tldUtils::getSqlToAssocArray("EXEC AP_Invoices_Control_Receipts '{$vars['o']}','{$vars['linef']}','{$vars['linet']}','{$vars['z']}'", "odbc", ["src" => "baan"]);
                    $caption = "AP Invoices control - receipts details for company {$vars['z']}";
                } // else{
                $body = $form->toHTML();
                //}
                break;


            case 'APInvoicesControl_Invoices':
                $xItems = [
                    "w_orno" => "Purchase order",
                    "w_pono" => "Position",
                    "w_srnb" => "Sequence back order",
                    "w_srni" => "Sequence invoice",
                    "w_item" => "Item",
                    "w_dsca" => "Description",
                    "w_invn" => "Invoice number",
                    "w_pdat" => "Approval date",
                    "w_dued" => "Due date",
                    "w_qana" => "Quantity",
                    "w_amtc" => "Amount in order currency",
                    "w_isup" => "Supplier invoice",
                ];
                // Get listing
                $erpList = tldLocation::getERPList("smartyOptions");

                // Get form
                $form = new HTML_QuickForm('frmAPInvoicesControl_Invoices', 'get', "", "", "", true);
                $form->addElement('hidden', 'm[0]', 'reports');
                $form->addElement('hidden', 'm[1]', 'listing');
                $form->addElement('hidden', 'm[2]', 'APInvoicesControl_Invoices');
                $form->addElement('header', 'title', 'Select parameters:');
                $form->addElement('text', 'o', 'Order');
                $form->addElement('text', 'linef', 'Line from');
                $form->addElement('text', 'linet', 'Line to');
                $form->addElement('select', 'z', 'Company#',
                    ["" => ""] + $erpList);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->addRule('z', 'This is required', 'required');

                if ($form->validate()) {
                    $vars = tldUtils::cleanupFormInput($form->exportValues());

                    $rows = tldUtils::getSqlToAssocArray("EXEC AP_Invoices_Control_Invoices '{$vars['o']}','{$vars['linef']}','{$vars['linet']}','{$vars['z']}'", "odbc", ["src" => "baan"]);
                    $caption = "AP Invoices control - invoices details for company {$vars['z']}";

                    // Hide due date if not filled: 1900-01-01
                    foreach ($rows as $key1 => $value1) {
                        if ($rows[$key1]['w_dued'] == '1900-01-01') {
                            $rows[$key1]['w_dued'] = '';
                        }
                    } // foreach ($rows as $key1 => $value1)


                } // else{
                $body = $form->toHTML();
                //}
                break;
            case 'CapexReport':
                if (!$user->isInGroup(["gg_ADMIN", "GG_EXCOM", "superuser", "role_CFO", "role_FC"])) {
                    echo "You do not have permissions for this page..";
                    exit;
                }

                $xItems = ["id" => "Sequence ID", "task" => "Capex"];

                $DateOptions = ['class' => 'date-picker'];
                $erpList = tldLocation::getERPList("smartyOptions");
                $form = new HTML_QuickForm('frmCapexReport', 'get', "", "", "", true);
                $form->addElement('hidden', 'm[0]', 'reports');
                $form->addElement('hidden', 'm[1]', 'listing');
                $form->addElement('hidden', 'm[2]', 'CapexReport');
                $form->addElement('header', 'title', 'Choose date:');
                $form->addElement('text', 'date', 'Date', $DateOptions);
                $form->addElement('select', 'bu', 'Company#',
                    ["" => ""] + $erpList);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->addRule('bu', 'This is required', 'required');
                $form->addRule('date', 'This is required', 'required');
                if ($form->validate()) {
                    $vars = tldUtils::cleanupFormInput($form->exportValues());
                    $vars["date"] = explode("-", $vars["date"]);
                    $query = "select tasks.id, task
from tasks
  left join locations on locations.id = tasks.bu_id
where (tplno = 41 or tplno = 46) and MONTH(date) = '{$vars["date"][1]}' and YEAR(date) = '{$vars["date"][0]}'
and locations.erp = '{$vars["bu"]}'";

                    $rows = tldUtils::getSqlToAssocArray($query);
                    $caption = "Capex report for erp {$vars["bu"]} : {$vars["date"][0]}-{$vars["date"][1]}";
                    $links = [
                        "id" => [
                            "url" => "/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view",
                            "params" => [
                                "id" => "id"
                            ],
                        ]
                    ];

                }
                $js = "<script type='text/javascript'>
$(function() {
    var selector = $('.date-picker');
    selector.datepicker( {
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
        dateFormat: 'yy-mm',
        onClose: function(dateText, inst) {
            $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
        }
    });
});
</script>
<style type='text/css'>
.ui-datepicker-calendar {
    display: none;
    }
</style>";


                $smarty = tldUtils::getSmarty("intranet");
                $smarty->assign("html_head",$js);
                $body .= $form->toHTML();
                break;


        }

        if (isset($rows, $caption)) {
            if (($m[2] <> 'PPVInvDashBoard') && ($m[2] <> 'PPVStdDashBoard')) {
                $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER["REQUEST_URI"]}&m[3]=xls">XLS version</a>&nbsp;|&nbsp;
<a href="{$_SERVER["REQUEST_URI"]}&m[3]=csv">CSV version</a>
EOF;
            }
            switch ($m[3]) {
                case 'xls':
                    $report = new tldXLS(
                        $rows,
                        [
                            "xItems" => $xItems,
                            "showTitles" => TRUE,
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
                            "showTitles" => TRUE,
                        ]
                    );
                    $report->out();
                    exit;
                    break;
                default:

                    $report = new tldReportColumnar(
                        $rows,
                        [
                            "xItems" => $xItems,
                            "title" => $caption,
                            "links" => $links,
                        ]
                    );
                    $body .= $report->fetch();

                    break;
            }
        }
        break;

    default:
        $body = $smarty->fetch("$PATH/reports/homepage.reports.tpl");
        break;
}
function decodeDocs($str){
    //split on the ;
    $docs = explode(";", $str);
    //split on #
    if(count($docs)==0)return;
    foreach($docs as $doc){
        list($type, $num) = explode("#", $doc);
        if(in_array($type, array("SB","SPR","WC"))){
            $url = tldModLink::getURL($type, $num);
            $r .=<<<EOF
<a href="$url">$type $num</a><br>
EOF;
        }elseif($type=='SN'){
            $r .=<<<EOF
<a href="/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=search&m[2]=bySN&sn=$num">$type $num</a><br>
EOF;
        }
    }
    return $r;
}
function _getDisplayPo($po,$mode=NULL){

    $a = $po->asArray();
    $report = new tldAssocTable(
        $a["header"],
        [
            "t_orno" => "PO#",
            "t_cotp" => "Order Type",
            "t_ccur" => "Currency",
            "t_suno" => "Supplier#",
            "supplier" => "Supplier Name",
            "t_odat" => "Order Date",
            "t_refb" => "Ref B",
            "t_cpay" => "Payment terms",
            "t_cdec" => "Delivery terms",
            "t_ccty" => "Country",
        ],
        ["title" => "Purchase Order #$id"]
    );
    $GLOBALS["smarty"]->assign("header", $report->fetch());
    $report = new tldAssocTable(
        $a["header"]["cwar_data"],
        [
            "t_nama" => "Company Name",
            "t_namb" => "Address",
            "t_namc" => "",
            "t_namd" => "",
            "t_name" => ""
        ],
        ["title" => "Delivery Address"]
    );
    $GLOBALS["smarty"]->assign("delivery_address", $report->fetch());

    switch($mode){
        case 'shipped':
            $rows = $po->getShippedDetail();
            break;
        default:
            $rows = array_merge($po->getDetail() , $po->getShippedDetail());

            foreach($rows as &$row){
                $erp = $po->getERP();
                $query1 =<<<SQL
SELECT ITM.t_wght,ITM.t_dscc,
(SELECT mcs010.t_dsca FROM ttcmcs010{$erp} AS mcs010 WHERE mcs010.t_ccty=ITM.t_ctyo) AS t_ctyo,
itm.t_ccde AS t_ccde,
(select min(TLD890.t_dsca) from ttitld890400 TLD890 where TLD890.t_eitm=ITM.t_item and TLD890.t_clan like '%CH%') t_dscb,
(select MCS028.t_dsca FROM ttcmcs028{$a["header"]["t_comp"]} MCS028 WHERE MCS028.t_ccde=itm.t_ccde) t_dssh
FROM ttiitm001{$erp} AS ITM
LEFT JOIN ttiitm001{$a["header"]["t_comp"]} AS itm ON ITM.t_item = itm.t_item
WHERE ITM.t_item = '{$row['t_item']}'
SQL;

                $data2 = tldUtils::getSqlRowToAssocArray($query1, "odbc", array("src"=>"baan"));
                $row['t_wght'] =$data2['t_wght'];
                $row['t_dscc'] =$data2['t_dscc'];
                $row['t_ctyo'] =$data2['t_ctyo'];
                $row['t_ccde'] =$data2['t_ccde'];
                $row['t_dscb'] =$data2['t_dscb'];
                $row['t_dssh'] =$data2['t_dssh'];
                $total = $total + (float)$row['t_amta'];
            }
            break;
    }

    $GLOBALS["smarty"]->assign("mode",$mode);
    $GLOBALS["smarty"]->assign("po",$a["header"]["t_orno"]);
    $GLOBALS["smarty"]->assign("detail",$rows);
    $GLOBALS["smarty"]->assign("total",$total);
    return $GLOBALS["smarty"]->fetch($GLOBALS["PATH"]."/reports/view.po.tpl");
}
