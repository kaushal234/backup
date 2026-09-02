<?php
include_once('Image/Graph.php');
include_once('../kpi/kpi.common.inc.php');
include_once('sales_service.inc.php');
require_once('HTML/QuickForm/advmultiselect.php');

$overlib = $smarty->fetch('overlib.inc.js.tpl');
$overlib .= '<script type="text/javascript">$(function(){$(".overlib").overlib()});</script>';
$autosuggest = $smarty->fetch('autosuggest.inc.js.tpl');
$autosuggest .= '<script type="text/javascript">$(function(){var uci=$("[name=suno]").autosuggest({message:"Begin to type a portion of the name for auto suggestions",
onChange:function(o){$(uci.input.display).val($(o.input.display).val());
$(uci.input.value).val($(o.input.value).val());}});});</script>';

$smarty->assign('html_head', $overlib . $autosuggest);

$DEFAULT_TITLE .= "\Reports";
$DEFAULT_MENU .= <<<EOF
<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=reports">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=reports&m[2]=ppv">PPV</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=reports&m[2]=beta">BETA</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=reports&m[2]=dataset">Dataset</a>
EOF;

switch ($m[1]) {
    case 'vendorinformation':
        $erpList = tldLocation::getERPList('smartyOptions');
        // Get form
        $form = new HTML_QuickForm('frmMRPOrders', 'get', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'vendorinformation');
        $form->addElement('header', 'title', 'Select company:');
        $form->addElement('select', 'z', 'Company#', $erpList);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('z', 'This is required', 'required');
        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }
        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $erpObj = new tldBaanERP($vars['z']);
        $rows = $erpObj->getSupplierData(null, null, ['orderBy' => 't_suno']);
        $xItems = [
            't_suno'  =>	'Supplier#',
            't_sust'	=>	'Status',
            't_ccty'	=>	'Country',
            't_nama'	=>	'Name 1',
            't_namb'	=>	'Name 2',
            't_namc'	=>	'Address 1',
            't_namd'	=>	'Address 2',
            't_name'	=>	'City 1',
            't_namf'	=>	'City 2',
            't_pstc'	=>	'Post number',
            't_seak'	=>	'Search key',
            't_refs'	=>	'Sale reference',
            't_refa'	=>	'Accounting reference',
            't_ocus'	=>	'Our customer number',
            't_telp'	=>	'Telephone',
            't_telx'	=>	'Telex',
            't_tefx'	=>	'Fax',
            't_mail'	=>	'Email',
            't_clan'	=>	'Language',
            't_cbrn'	=>	'Line of business',
            't_ccur'	=>	'Currency',
            't_cotp'	=>	'Order type',
            't_ccon'	=>	'Contact',
            't_cpay'	=>	'Terms of payment',
            't_paym'	=>	'Payment method',
            't_cfsg'	=>	'Financial supplier group',
            't_qual'	=>	'Inspection',
            't_fovn'	=>	'Tax number',
            't_beid'	=>	'Business entity indentifier',
            't_duns'	=>	'DUNS',
            't_cage'	=>	'CAGE',
        ];
        if(isset($rows)){
            $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=csv">CSV version</a>
EOF;
            if ( $m[2] === 'csv') {
                $report = new tldCSV(
                    $rows,
                    [
                        'xItems' => $xItems,
                        'showTitles' => true,
                    ]
                );
                $report->out('Vendor list.csv');
                exit;
            }
        }

        $report = new tldReportColumnar($rows,
            [
                'xItems' => $xItems,
                'title' => $caption,
            ]
        );
        $body .= $report->fetch();
    break;
    case 'ExportItemData':
        $logger->reset();
        $logger->setHandlers([]);
        $logger = null;
        $today = new DateTime();
        $thisYear = $today->format('Y');
        $lastYear = $thisYear - 1;
        $beforelastYear = $lastYear - 1;
//        MLM/SPH mgr and CPO
        if (!$user->isInGroup(['role_MLM', 'role_SPM', 'role_CPO','role_BYR','role_COO','role_RCOO','role_CEO'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permissions...';
            break;
        }
        $xItems = [
            'ITEM' => 'PN#',
            'DESCRIPTION' => 'Description',
            't_kitm' => 'Item type',
            'xref' => 'Xref',
            'ERP' => 'BAAN Company',
            't_suno' => 'ERP Vendor#',
            't_nama' => 'Vendor Name',
            't_buyr' => 'Buyer Name',
            't_ccur' => 'Purchasing Currency',
            't_prip' => 'Current Price',
            't_ltpr' => 'Last Purchased Price',
            't_ltpp' => 'Last Transaction Date',
            'STDCCUR' => 'STD Currency',
            'STDCOST' => 'STD Cost',
            't_oint' => 'Order Interval',
            'usagecuryear' => $thisYear . ' usage',
            'past12month' => 'Past 12 months usage',
            'usagelastyear' => $lastYear . ' usage',
            'usagebeforelastyear' => $beforelastYear . ' usage',
            't_allo' => 'Allocated',
            't_stks' => 'Actual Nettable Inventory',
            'LEAD' => 'Leadtime',
            't_sfst' => 'Safty Stock',
            't_mioq' => 'MOQ',
            't_conv' => 'conversion factor',
            't_dscd' => 'Standard',
        ];
        $erpList = tldLocation::getERPList('smartyOptions');
        // Get form
        $form = new HTML_QuickForm('frmMRPOrders', 'get', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'ExportItemData');
        $form->addElement('hidden', 'm[2]', 'csv');
        $form->addElement('header', 'title', 'Select company:');
        $form->addElement('header', 'title', 'Choose the BU you want to see in your report(Maximum 2 BUs) :');
        $ams =& $form->addElement('advmultiselect', 'z', null, $erpList, ['size' => 15, 'class' => 'pool', 'style' => 'width:382px;']);
        $ams->setLabel(['ERP', 'available', 'chosen']);
        $ams->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
        $ams->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('z', 'This is required', 'required');
        $form->addRule('item', 'This is required', 'required');
        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $thisMonth = $today->format('n');
        $dateYM = [];
        for ($i = $thisMonth - 1; $i > 0; $i--) {
            $monthsArg[] = 't_aupp_' . ($i);
        }
        for ($i = $thisMonth; $i <= 12; $i++) {
            $monthsArg1[] = 't_aupp_' . ($i);
        }
        $year = [];
        //get item statistical usage for each last 3 years
        $yearsList = range($thisYear - 2, $thisYear);

        $arg = implode(' + ', $monthsArg);
        $cons = '';
        if(!empty($arg)){
            $cons = "(t_year IN ($thisYear) AND ($arg)>0) or ";
        }
        $arg1 = implode(' + ', $monthsArg1);
        $yearsList = "'" . implode("','", $year) . "'";
        $WHERE = "$cons ((t_year IN ($lastYear) AND ($arg1)>0))";
        if ($thisMonth === 1) {
            $WHERE = "t_year IN ($yearsList) AND (t_aupp_1+t_aupp_2+t_aupp_3+t_aupp_4+t_aupp_5+t_aupp_6+t_aupp_7+t_aupp_8+t_aupp_9+t_aupp_10+t_aupp_11+t_aupp_12)>0";
        }
        if (count($vars['z']) > 0) {$dateYM = [];
            for ($i = 0; $i < 12; $i++) {
                $today->modify('-1 month');
                $year = $today->format('Y');
                $month = $today->format('n');

                //the last 12 month can be on 2 years so we associate the month to its year
                $dateYM[$year][] = 't_aupp_' . $month;
                //get the names of args
                // because in tdinv750 the values are listed by year, january is t_aupp_1, february is t_aupp_2 ect...
                $dateYM['query'][] = $year . '.t_aupp_' . $month;
                $monthsArg[] = 't_aupp_' . ($i + 1);
            }
            //sum all the arg selected
            $selectArg = 'Y' . implode(' + Y', $dateYM['query']);
            unset($dateYM['query']);
            //create the query. Join the same table if a second year is needed
            $years = array_keys($dateYM);
            foreach ($vars['z'] as $k => $v) {
                $subquery = <<<SQL
SELECT 
      {$selectArg} AS past12month FROM ttdinv750{$v} AS Y$years[0]
SQL;
                if (count($years) === 2) {
                    $subquery .= <<<SQL
    INNER JOIN ttdinv750{$v} AS Y$years[1]
    ON Y$years[0].t_item = Y$years[1].t_item
SQL;
                }
                $subquery .= <<<SQL
    WHERE Y$years[0].t_item = itm.t_item
    AND Y$years[0].t_year = '$years[0]'
SQL;
                if (count($years) === 2) {
                    $subquery .= <<<SQL
    AND Y$years[1].t_year = '$years[1]'
SQL;
                }

                $query2 = <<<SQL
        SELECT RTRIM(t_item) AS t_item
        FROM ttdinv750{$v}
        WHERE $WHERE
SQL;

        $query[] = <<<SQL
SELECT
    itm.t_item AS ITEM,
    (
        SELECT
            edm.t_dsca
        FROM
            dbo.ttiedm010$v AS edm
        WHERE
                edm.t_eitm=itm.t_item
    ) AS DESCRIPTION,
    (
        SELECT  (t_aupp_1+ t_aupp_2 +t_aupp_3+ t_aupp_4+ t_aupp_5+t_aupp_6+ t_aupp_7+t_aupp_8+t_aupp_9+ t_aupp_10+t_aupp_11+t_aupp_12)
        FROM ttdinv750$v WHERE t_year IN ('$thisYear') AND t_item = itm.t_item
    ) AS usagecuryear,
    (
        SELECT  (t_aupp_1+ t_aupp_2 +t_aupp_3+ t_aupp_4+ t_aupp_5+t_aupp_6+ t_aupp_7+t_aupp_8+t_aupp_9+ t_aupp_10+t_aupp_11+t_aupp_12)
        FROM ttdinv750$v WHERE t_year IN ('$lastYear') AND t_item = itm.t_item
    ) AS usagelastyear,
    ($subquery) AS past12month,
    (
        SELECT  (t_aupp_1+ t_aupp_2 +t_aupp_3+ t_aupp_4+ t_aupp_5+t_aupp_6+ t_aupp_7+t_aupp_8+t_aupp_9+ t_aupp_10+t_aupp_11+t_aupp_12)
        FROM ttdinv750$v WHERE t_year IN ('$beforelastYear') AND t_item = itm.t_item
    ) AS usagebeforelastyear,
    itm.t_kitm,
    $v AS ERP,
    itm.t_suno,
    (SELECT SUP.t_nama FROM ttccom020$v AS SUP WHERE SUP.t_suno = itm.t_suno) AS t_nama,
    itm.t_buyr,
    itm.t_ccur,
    CAST(itm.t_prip AS money) AS t_prip,
    SUBSTRING(convert(varchar, itm.t_ltpp, 120), 0, 11) AS t_ltpp,
    (SELECT com.t_ccur FROM ttccom000300 AS com
     WHERE com.t_ncmp=$v
    ) AS STDCCUR,
    CAST(ROUND(itm.t_copr,2) AS money) AS STDCOST,
    itm.t_allo,
    itm.t_stoc AS stoc,
    itm.t_oltm AS LEAD,
    itm.t_sfst,
    itm.t_mioq,
    itm.t_oint,
    itm.t_ltpr,
    itm.t_dscd,
    (SELECT MAX(itm004.t_conv) FROM ttiitm004$v AS itm004 WHERE itm004.t_item=itm.t_item) AS t_conv,
    CONVERT(VARCHAR, stuff((select ',' + CAST(LTRIM(RTRIM(AIC.t_aitc)) AS varchar)  FROM ttiitm012$v as AIC WHERE (AIC.t_citt='SUP' OR AIC.t_citt='MFG') AND AIC.t_item = itm.t_item for xml path('')),1,1,'')) as xref,
    (SELECT sum(t_stks) from ttdilc101$v AS inventory LEFT JOIN ttcmcs003$v AS whs ON inventory.t_cwar=whs.t_cwar WHERE inventory.t_item=itm.t_item and t_nwrh=1) AS t_stks

FROM
    ttiitm001$v AS itm
WHERE itm.t_item in ($query2)
SQL;

    }
}

        $rows = tldUtils::getSqlToAssocArray(implode(' UNION ', $query), 'odbc', ['src' => 'baan']);
        $caption = 'Full Item data exportation';

        if (isset($rows, $caption)) {
            if (isset($m[2]) && $m[2] === 'csv') {
                $report = new tldCSV(
                    $rows,
                    [
                        'xItems' => $xItems,
                        'showTitles' => true,
                    ]
                );
                $report->out('item list.csv');
                exit;
            }
        }
        break;
    case 'ItemBySupplierByBU':
// Get listing
        $erpList = tldLocation::getERPList('smartyOptions');
        $today = new DateTime();
        $thisYear = $today->format('Y');
        $lastYear = $thisYear - 1;
        $twoYearsAgo = $thisYear - 2;
        $threeYearsAgo = $thisYear - 3;
        $xItems = [
            'comp' => 'Company#',
            'item' => 'Item#',
            'dsca' => 'Description',
            'cupp' => 'Unit',
            'ccur' => 'Currency',
            'prip' => ' Purchase Price',
            'suno' => 'Supplier#',
            'copr' => 'Cost Price',
            'stoc' => 'On Hand',
            'sfst' => 'Safety Stock',
            'uscu' => 'Cumulative Issue',
            'ltdt' => 'Last Inventory Transaction Date',
            'past12month' => 'Annual usage qty of past 12 month',
            'usagelastyear' => "Annual usage qty of $lastYear",
            'usage2yearsago' => "Annual usage qty of $twoYearsAgo",
            'usage3yearsago' => "Annual usage qty of $threeYearsAgo",
        ];

        // Get form
        $form = new HTML_QuickForm('frmMRPOrders', 'get', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'ItemBySupplierByBU');
        $form->addElement('header', 'title', 'Select company:');
        $form->addElement('text', 'sup', 'Supplier#(Allow to select ALL)');
        $form->addElement('text', 'item', 'Item#(If supplier="All", fill in with PN)');
        $form->addElement('select', 'z', 'Company#',
            ['' => '', 'ALL' => 'ALL'] + $erpList);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('z', 'This is required', 'required');
        $form->addRule('sup', 'This is required', 'required');
        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $today = new DateTime();
        if ($vars['z'] !== 'ALL') {
            $dateYM = [];
            for ($i = 0; $i < 12; $i++) {
                $today->modify('-1 month');
                $year = $today->format('Y');
                $month = $today->format('n');

                //the last 12 month can be on 2 years so we associate the month to its year
                $dateYM[$year][] = 't_aupp_' . $month;
                //get the names of args
                // because in tdinv750 the values are listed by year, january is t_aupp_1, february is t_aupp_2 ect...
                $dateYM['query'][] = $year . '.t_aupp_' . $month;
                $monthsArg[] = 't_aupp_' . ($i + 1);
            }
            //sum all the arg selected
            $selectArg = 'Y' . implode(' + Y', $dateYM['query']);
            unset($dateYM['query']);
            //create the query. Join the same table if a second year is needed
            $years = array_keys($dateYM);
            $subquery = <<<SQL
SELECT 
      {$selectArg} AS past12month FROM ttdinv750{$vars['z']} AS Y$years[0]
SQL;
    if (count($years) === 2) {
        $subquery .= <<<SQL
    INNER JOIN ttdinv750{$vars['z']} AS Y$years[1]
    ON Y$years[0].t_item = Y$years[1].t_item
SQL;
    }
    $subquery .= <<<SQL
    WHERE Y$years[0].t_item = itm001.t_item
    AND Y$years[0].t_year = '$years[0]'
SQL;
    if (count($years) === 2) {
        $subquery .= <<<SQL
    AND Y$years[1] . t_year = '$years[1]'
SQL;
    }

    $query = <<<EOF
select
    {$vars['z']}  as comp,
	itm001.t_item item,
	itm001.t_dsca dsca,
	itm001.t_cupp cupp,
	itm001.t_ccur ccur,
	convert(varchar(100), cast(itm001.t_prip as decimal(15,2))) as prip,
	itm001.t_suno suno,
	convert(varchar(100), cast(itm001.t_copr as decimal(15,2))) as copr,
	itm001.t_stoc stoc,
	itm001.t_sfst sfst,
	itm001.t_uscu uscu,
	(
        SELECT  (t_aupp_1+ t_aupp_2 +t_aupp_3+ t_aupp_4+ t_aupp_5+t_aupp_6+ t_aupp_7+t_aupp_8+t_aupp_9+ t_aupp_10+t_aupp_11+t_aupp_12)
        FROM ttdinv750{$vars['z']} WHERE t_year IN ('$thisYear') AND t_item = itm001.t_item
    ) AS usagecuryear,
    ($subquery) AS past12month,
    (
        SELECT  (t_aupp_1+ t_aupp_2 +t_aupp_3+ t_aupp_4+ t_aupp_5+t_aupp_6+ t_aupp_7+t_aupp_8+t_aupp_9+ t_aupp_10+t_aupp_11+t_aupp_12)
        FROM ttdinv750{$vars['z']} WHERE t_year IN ('$lastYear') AND t_item = itm001.t_item
    ) AS usagelastyear,
    (
        SELECT  (t_aupp_1+ t_aupp_2 +t_aupp_3+ t_aupp_4+ t_aupp_5+t_aupp_6+ t_aupp_7+t_aupp_8+t_aupp_9+ t_aupp_10+t_aupp_11+t_aupp_12)
        FROM ttdinv750{$vars['z']} WHERE t_year IN ('$twoYearsAgo') AND t_item = itm001.t_item
    ) AS usage2yearsago,
    (
        SELECT  (t_aupp_1+ t_aupp_2 +t_aupp_3+ t_aupp_4+ t_aupp_5+t_aupp_6+ t_aupp_7+t_aupp_8+t_aupp_9+ t_aupp_10+t_aupp_11+t_aupp_12)
        FROM ttdinv750{$vars['z']} WHERE t_year IN ('$threeYearsAgo') AND t_item = itm001.t_item
    ) AS usage3yearsago,
	SUBSTRING(convert(varchar,itm001.t_ltdt,120), 0, 11) AS ltdt
from
	ttiitm001{$vars['z']} itm001
WHERE itm001.t_ltdt<>'1753-01-01 00:00:00.000'
EOF;
    $caption = "Purchase Orders for company {$vars['z']} supplier#{$vars['sup']} ";
    if (strtoupper($vars['sup']) !== 'ALL') {
        $query .= " and itm001.t_suno LIKE '%{$vars['sup']}%' ";
    }
    if ($vars['item'] != '') {
        $query .= " and itm001.t_item LIKE '%{$vars['item']}%' ";
        $caption .= "item#{$vars['item']}";
    }

    $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
} else {
    foreach ($erpList as $key => $id) {
        $dateYM = [];
        $today = new DateTime();
        for ($i = 0; $i < 12; $i++) {
            $today->modify('-1 month');
            $year = $today->format('Y');
            $month = $today->format('n');

            //the last 12 month can be on 2 years so we associate the month to its year
            $dateYM[$year][] = 't_aupp_' . $month;
            //get the names of args
            // because in tdinv750 the values are listed by year, january is t_aupp_1, february is t_aupp_2 ect...
            $dateYM['query'][] = $year . '.t_aupp_' . $month;
            $monthsArg[] = 't_aupp_' . ($i + 1);
        }
        //sum all the arg selected
        $selectArg = 'Y' . implode(' + Y', $dateYM['query']);
        unset($dateYM['query']);
        //create the query. Join the same table if a second year is needed
        $years = array_keys($dateYM);
        $subquery = <<<SQL
SELECT 
      {$selectArg} AS past12month FROM ttdinv750$key AS Y$years[0]
SQL;

        if (count($years) === 2) {
            $subquery .= <<<SQL
        INNER JOIN ttdinv750$key AS Y$years[1]
        ON Y$years[0].t_item = Y$years[1].t_item
SQL;
        }
        $subquery .= <<<SQL
    WHERE Y$years[0].t_item = itm001.t_item
    AND Y$years[0].t_year = '$years[0]'
SQL;
        if (count($years) === 2) {
            $subquery .= <<<SQL
    AND Y$years[1].t_year = '$years[1]'
SQL;
        }
        $query = <<<EOF
select
    $key as comp,
	itm001.t_item item,
	itm001.t_dsca dsca,
	itm001.t_cupp cupp,
	itm001.t_ccur ccur,
	convert(varchar(100), cast(itm001.t_prip as decimal(15,2))) as prip,
	itm001.t_suno suno,
	convert(varchar(100), cast(itm001.t_copr as decimal(15,2))) as copr,
	itm001.t_stoc stoc,
	itm001.t_sfst sfst,
	itm001.t_uscu uscu,
	($subquery) AS past12month,
	(
        SELECT  (t_aupp_1+ t_aupp_2 +t_aupp_3+ t_aupp_4+ t_aupp_5+t_aupp_6+ t_aupp_7+t_aupp_8+t_aupp_9+ t_aupp_10+t_aupp_11+t_aupp_12)
        FROM ttdinv750$key WHERE t_year IN ('$thisYear') AND t_item = itm001.t_item
    ) AS usagecuryear,
    (
        SELECT  (t_aupp_1+ t_aupp_2 +t_aupp_3+ t_aupp_4+ t_aupp_5+t_aupp_6+ t_aupp_7+t_aupp_8+t_aupp_9+ t_aupp_10+t_aupp_11+t_aupp_12)
        FROM ttdinv750$key WHERE t_year IN ('$lastYear') AND t_item = itm001.t_item
    ) AS usagelastyear,
    (
        SELECT  (t_aupp_1+ t_aupp_2 +t_aupp_3+ t_aupp_4+ t_aupp_5+t_aupp_6+ t_aupp_7+t_aupp_8+t_aupp_9+ t_aupp_10+t_aupp_11+t_aupp_12)
        FROM ttdinv750$key WHERE t_year IN ('$twoYearsAgo') AND t_item = itm001.t_item
    ) AS usage2yearsago,
    (
        SELECT  (t_aupp_1+ t_aupp_2 +t_aupp_3+ t_aupp_4+ t_aupp_5+t_aupp_6+ t_aupp_7+t_aupp_8+t_aupp_9+ t_aupp_10+t_aupp_11+t_aupp_12)
        FROM ttdinv750$key WHERE t_year IN ('$threeYearsAgo') AND t_item = itm001.t_item
    ) AS usage3yearsago,
	SUBSTRING(convert(varchar,itm001.t_ltdt,120), 0, 11) AS ltdt
from
	ttiitm001$key itm001
WHERE itm001.t_ltdt<>'1753-01-01 00:00:00.000'
EOF;
        $caption = "Purchase Orders for ALL company supplier#{$vars['sup']} ";
        if (strtoupper($vars['sup']) !== 'ALL') {
            $query .= " and itm001.t_suno LIKE '%{$vars['sup']}%' ";
        }
        if ($vars['item'] != '') {
            $query .= " and itm001.t_item LIKE '%{$vars['item']}%' ";
            $caption .= "item#{$vars['item']}";
        }
        if (strtoupper($vars['sup']) === 'ALL' && empty($vars['item'])) {
            $DEFAULT_ERROR[] = 'ERROR: Search need to more percise...';
            break;
        }
        $arr = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
        foreach ($arr as $k => $v) {
            $rows[$key . $k] = $v;
        }
    }
}
        if (isset($rows, $caption)) {
            $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=xls">XLS version</a>&nbsp;|&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=csv">CSV version</a>
EOF;
    switch ($m[2]) {
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
        case 'csv':
            $report = new tldCSV(
                $rows,
                [
                    'xItems' => $xItems,
                    'showTitles' => true,
                ]
            );
            $report->out();
            exit;
            break;
        default:

            $report = new tldReportColumnar($rows,
                [
                    'xItems' => $xItems,
                    'title' => $caption,
                ]
            );
            $body .= $report->fetch();

            break;
    }
}
        break;

    case 'ItemIssue':

        // Get listing
        $erpList = tldLocation::getERPList('smartyOptions');
        // Get form
        $yesno = ['Yes', 'No'];
        $display = ['List', 'Matrix'];
        $form = new HTML_QuickForm('frmItemIssue', 'get', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'ItemIssue');
        $form->addElement('header', 'title', 'Select parameters:');
        $form->addElement('text', 'if', 'Item from');
        $form->addElement('text', 'it', 'Item to');
        $form->addElement('select', 'Item_Type', 'Item Type', ['', 1 => 'Purchased Item', 2 => 'Manufactured Item']);
        $form->addElement('date', 'x', 'From', ['format' => 'Y-m', 'minYear' => date('Y') - 10, 'maxYear' => date('Y')]);
        $form->addElement('date', 'y', 'To', ['format' => 'Y-m', 'minYear' => date('Y') - 10, 'maxYear' => date('Y')]);
        $form->addElement('select', 'z', 'Company#', ['' => ''] + $erpList);
        $form->addElement('select', 'd', 'Detail per month', array_combine($yesno, $yesno));
        $form->addElement('select', 'e', 'Display', array_combine($display, $display));
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('x', 'This is required', 'required');
        $form->addRule('y', 'This is required', 'required');
        $form->addRule('z', 'This is required', 'required');
        $form->setDefaults(['if' => '']);
        $form->setDefaults(['it' => 'zzzzzzzzzz']);
        $form->setDefaults(['x' => date('Y-01-01')]);
        $form->setDefaults(['y' => date('Y-m-d')]);

        $body = $form->toHTML();

        if (!$form->validate()) {
            break;
        }
        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $where = '';
        if (!empty($vars['Item_Type'])) {
            $where = " and ITM.t_kitm = {$vars['Item_Type']} ";
        }

        $query = <<<SQL
            SELECT TOP 10000
                tld750.t_item,
                tld750.t_aupp_1,
                tld750.t_aupp_2,
                tld750.t_aupp_3,
                tld750.t_aupp_4,
                tld750.t_aupp_5,
                tld750.t_aupp_6,
                tld750.t_aupp_7,
                tld750.t_aupp_8,
                tld750.t_aupp_9,
                tld750.t_aupp_10,
                tld750.t_aupp_11,
                tld750.t_aupp_12,
                ITM.t_dsca,
                case ITM.t_kitm WHEN '1' THEN 'Purchased Item' WHEN '2' THEN 'Manufactured Item'
                END t_kitm,
                ITM.t_cuqs,
                ITM.t_copr,
                ITM.t_buyr,
                ITM.t_suno,
                tld750.t_year
                from ttdinv750{$vars['z']} AS tld750
                LEFT JOIN ttiitm001{$vars['z']} AS ITM ON tld750.t_item=ITM.t_item
                where tld750.t_item>='{$vars['if']}' and tld750.t_item<='{$vars['it']}' and
                tld750.t_year >= {$vars['x']['Y']} and
                tld750.t_year <= {$vars['y']['Y']}
SQL;
        $query .= $where;
        $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);

        //the tab for change the query result structure
        $proceedTabForTLDMatrix = [];
        //counter to have 10000 ( we transform a line into 12 sublines
        $count = 0;
        //display by month
        if ($vars['d'] === 'Yes') {
            $xItems = [
                't_item' => 'Item',
                't_year' => 'Year',
                't_mont' => 'Month',
                't_uscu' => 'Quantity',
            ];

            $dateFrom = new \DateTime($vars['x']['Y'] . '-' . $vars['x']['m']);
            $dateTo = new \DateTime($vars['y']['Y'] . '-' . $vars['y']['m']);
            //this while transform the data to have the same structure as the ttdtld750
            //In the tdinv750 a line = one year (all month issues on the line)
            // In the tdtld750 a line = a month
            foreach ($rows as $index => $line) {
                //Now we must transform a line to 12 sub lines
                for ($i = 1; $i <= 12; $i++) {

                    $currentLineDate = new \DateTime($line['t_year'] . '-' . $i);
                    if ($currentLineDate >= $dateFrom && $currentLineDate <= $dateTo) {
                        $count++;
                        //the original structure when the tdtld750 was used
                        $proceedTabForTLDMatrix[] = [
                            't_item' => $line['t_item'],
                            't_year' => $line['t_year'],
                            't_mont' => $i,
                            'matrixdate' => $line['t_year'] . '-' . sprintf('%02u', $i),
                            't_uscu' => $line['t_aupp_' . $i],
                            't_dsca' => $line['t_dsca'],
                            't_kitm' => $line['t_kitm'],
                            't_cuqs' => $line['t_cuqs'],
                            't_copr' => $line['t_copr'],
                            't_buyr' => $line['t_buyr'],
                            't_suno' => $line['t_suno'],
                        ];
                    }
                    //rules defined the limit to 10000 lines
                    if ($count >= 10000) {
                        break 2;
                    }
                }
            }

        } else {
            $xItems = [
                't_item' => 'Item',
                't_year' => 'Year',
                't_uscu' => 'Quantity',
            ];
            $count = 0;
            foreach ($rows as $index => $line) {
                $count++;
                //the original structure when the tdtld750 was used
                $totalYear = 0;
                //total item issues for the year
                for ($i = 1; $i <= 12; $i++) {
                    $totalYear += $line['t_aupp_' . $i];
                }
                $proceedTabForTLDMatrix[] = [
                    't_item' => $line['t_item'],
                    't_year' => $line['t_year'],
                    'matrixdate' => $line['t_year'],
                    't_uscu' => $totalYear,
                    't_dsca' => $line['t_dsca'],
                    't_kitm' => $line['t_kitm'],
                    't_cuqs' => $line['t_cuqs'],
                    't_copr' => $line['t_copr'],
                    't_buyr' => $line['t_buyr'],
                    't_suno' => $line['t_suno'],
                ];

                //rules defined the limit to 10000 lines
                if ($count >= 10000) {
                    break;

                }

            }
        }


        $caption = "Item Issues for company {$vars['z']}" . ' (10000 results max)';
        $rows = $proceedTabForTLDMatrix;
        if (isset($rows, $caption)) {
            $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=xls">XLS version</a>&nbsp;|&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=csv">CSV version</a>
EOF;
            if ($vars['e'] === 'List') {
                $xItems = ['t_item' => 'Item',
                    't_year' => 'Year',
                    't_mont' => 'Month',
                    't_uscu' => 'Quantity',
                    't_dsca' => 'Description',
                    't_kitm' => 'Item Type',
                    't_cuqs' => 'UM',
                    't_copr' => 'Std Cost',
                    't_buyr' => 'Buyer Code',
                    't_suno' => 'Supplier Code'];
            }
            switch ($m[2]) {
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
                case 'csv':
                    $report = new tldCSV(
                        $rows,
                        [
                            'xItems' => $xItems,
                            'showTitles' => true,
                        ]
                    );
                    $report->out();
                    exit;
                    break;
                default:
                    if ($vars['e'] === 'List') {
                        $report = new tldReportColumnar(
                            $rows,
                            ['xItems' => $xItems, 'title' => $caption]
                        );
                    } else {
                        $report = new tldMatrix(
                            $rows,
                            'matrixdate', 't_item', 't_uscu', '',
                            "Item Issues for company {$vars['z']}" . ' (10000 results max)',
                            ['doNotShowXTotals' => false]
                        );
                    }
                    $body .= $report->fetch();
                    break;
            }
        }
        break;

    case 'InvCheckRequest':
        // Get listing
        $erpList = tldLocation::getERPList('smartyOptions');
        $orderStatus = ['Open', 'Closed', 'All'];

        // Get form
        $form = new HTML_QuickForm('frmInvCheckRequest', 'post', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'InvCheckRequest');
        $form->addElement('header', 'title', 'Select parameters:');
        $form->addElement('text', 's', 'Supplier');
        $form->addElement('text', 'o', 'Order');
        $form->addElement('text', 'b', 'Buyer name');
        $form->addElement('text', 'r', 'Results');
        $form->addElement('select', 'z', 'Company#',
            ['' => ''] + $erpList);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('z', 'This is required', 'required');
        $form->setDefaults(['r' => '10']);
        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());

        // Items with check_request
        $query = "select distinct t_orno, t_pono from ap_check_request where erp={$vars['z']} ";
        if ($vars['o'] != '') {
            $query .= 'and t_orno=' . $vars['o'] . ' ';
        }
        $query .= 'order by t_orno asc';
        $rowsTmp1 = tldUtils::getSqlToAssocArray($query);

        // Get all records
        $rows1 = [];
        foreach ($rowsTmp1 as $key1 => $pol) {
            $query = '';
            $query .= 'SELECT ';
            $query .= '	pur041.t_suno as w_suno, ';
            $query .= "	(select com020.t_nama from ttccom020{$vars['z']} com020 where com020.t_suno = pur041.t_suno) as w_nama, ";
            $query .= '	pur041.t_orno as w_orno, ';
            $query .= "	(select pur040.t_cotp from ttdpur040{$vars['z']} pur040 where pur040.t_orno = pur041.t_orno) as w_cotp, ";
            $query .= '	pur041.t_odat as w_odat, ';
            $query .= '	pur041.t_pono as w_pono, ';
            $query .= '	pur041.t_item as w_item, ';
            $query .= "	(select itm001.t_dsca from ttiitm001{$vars['z']} itm001 where itm001.t_item = pur041.t_item) as w_dsca, ";
            $query .= '	pur041.t_oqua as w_oqua, ';
            $query .= '	pur041.t_cuqp as w_cuqp, ';
            $query .= '	pur041.t_pric as w_pric, ';
            $query .= '	pur041.t_amta as w_amta, ';
            $query .= "	(select pur040.t_ccon from ttdpur040{$vars['z']} pur040 where pur040.t_orno = pur041.t_orno) as w_ccon, ";
            $query .= "	(select com001.t_nama from ttccom001{$vars['z']} com001 where com001.t_emno = ";
            $query .= "		(select pur040.t_ccon from ttdpur040{$vars['z']} pur040 where pur040.t_orno = pur041.t_orno)) as w_namb, ";
            $query .= "	(select sum(pur045.t_dqua) from ttdpur045{$vars['z']} pur045 where pur045.t_orno = pur041.t_orno and pur045.t_pono = pur041.t_pono) as w_dqua, ";
            $query .= "	CASE WHEN (select sum(pur045.t_dqua) from ttdpur045{$vars['z']} pur045 where pur045.t_orno = pur041.t_orno and pur045.t_pono = pur041.t_pono) >= pur041.t_oqua ";
            $query .= "	  THEN CONVERT(char(20), (select sum(pur045.t_dqua) from ttdpur045{$vars['z']} pur045 where pur045.t_orno = pur041.t_orno and pur045.t_pono = pur041.t_pono)) ";
            $query .= "	  ELSE '(*) ' +  CONVERT(char(20), (select sum(pur045.t_dqua) from ttdpur045{$vars['z']} pur045 where pur045.t_orno = pur041.t_orno and pur045.t_pono = pur041.t_pono)) END as w_dqua_str, ";
            $query .= "	(select sum(pur046.t_qana) from ttdpur046{$vars['z']} pur046 where pur046.t_orno = pur041.t_orno and pur046.t_pono = pur041.t_pono) as w_qana, ";
            $query .= "	CASE WHEN (select sum(pur046.t_qana) from ttdpur046{$vars['z']} pur046 where pur046.t_orno = pur041.t_orno and pur046.t_pono = pur041.t_pono) >= pur041.t_oqua ";
            $query .= "	  THEN CONVERT(char(20), (select sum(pur046.t_qana) from ttdpur046{$vars['z']} pur046 where pur046.t_orno = pur041.t_orno and pur046.t_pono = pur041.t_pono)) ";
            $query .= "	  ELSE '(*) ' +  CONVERT(char(20), (select sum(pur046.t_qana) from ttdpur046{$vars['z']} pur046 where pur046.t_orno = pur041.t_orno and pur046.t_pono = pur041.t_pono)) END as w_qana_str, ";
            $query .= "	(select com020.t_qual from ttccom020{$vars['z']} com020 where com020.t_suno = pur041.t_suno) as w_quas, ";
            $query .= "	(select CASE itm001.t_qual WHEN null then ' ' WHEN '1' then 'Yes' WHEN '2' then ' ' ELSE ' ' END from ttiitm001{$vars['z']} itm001 where itm001.t_item = pur041.t_item) as w_quai, ";
            $query .= "	CASE pur041.t_qual WHEN null then ' ' WHEN '1' then 'Yes' WHEN '2' then ' ' ELSE ' ' END as w_quap, ";
            $query .= "   (select max(pur045.t_spur) FROM ttdpur045{$vars['z']} pur045 WHERE pur045.t_orno=pur041.t_orno and pur045.t_pono=pur041.t_pono) as w_spur, ";
            $query .= "	(select mcs039.t_dsca from ttcmcs039{$vars['z']} mcs039 where mcs039.t_modc=1 and mcs039.t_stno = (select max(pur045.t_spur) FROM ttdpur045{$vars['z']} pur045 WHERE pur045.t_orno=pur041.t_orno and pur045.t_pono=pur041.t_pono) ) as w_dscb ";
            $query .= "FROM ttdpur041{$vars['z']} pur041 ";
            $query .= 'WHERE ';
            $query .= "  pur041.t_orno='{$rowsTmp1[$key1]['t_orno']}' and ";
            $query .= "  pur041.t_pono='{$rowsTmp1[$key1]['t_pono']}' ";

            // Filter by supplier
            if ($vars['s'] != '') {
                $query .= " and pur041.t_suno='{$vars['s']}' ";
            }

            // Filter by contact name
            $query .= "	and UPPER((select com001.t_nama from ttccom001{$vars['z']} com001 where com001.t_emno = ";
            $query .= "		(select pur040.t_ccon from ttdpur040{$vars['z']} pur040 where pur040.t_orno = pur041.t_orno))) like UPPER('%{$vars['b']}%') ";


            $rowsTmp2 = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);

            $rows1 = array_merge($rows1, $rowsTmp2);
        }

        foreach ($rows1 as $key1 => $pol) {
            // Add Check-Request reason and id
            $query = "select t_mtcr, id from ap_check_request where erp={$vars['z']} and t_orno={$rows1[$key1]['w_orno']} and t_pono={$rows1[$key1]['w_pono']}";
            $rows_Reason = tldUtils::getSqlToAssocArray($query);
            $rows1[$key1]['reason'] = $rows_Reason[0]['t_mtcr'];
            $rows1[$key1]['checkRequestId'] = $rows_Reason[0]['id'];

            // Add supplier invoice#
            $query = "select t_xref from ap_invoice_header where t_inno = (select t_inno from ap_invoice_lines where erp={$vars['z']} and t_orno={$rows1[$key1]['w_orno']} and t_pono={$rows1[$key1]['w_pono']})";
            $rows_Xref = tldUtils::getSqlToAssocArray($query);
            $rows1[$key1]['inv_xref'] = $rows_Xref[0]['t_xref'];

            // Add invoice quantity and amount
            $query = "select t_inqt, t_inmt from ap_invoice_lines where erp={$vars['z']} and t_orno={$rows1[$key1]['w_orno']} and t_pono={$rows1[$key1]['w_pono']}";
            $rows_Qty = tldUtils::getSqlToAssocArray($query);
            $rows1[$key1]['t_inmt'] = $rows_Qty[0]['t_inmt'];
            $rows1[$key1]['t_inqt'] = $rows_Qty[0]['t_inqt'];

            // Add last user comment
            $query = 'select t_usrm from ap_check_request_comments where ';
            $query .= "  parent_id=(select id from ap_check_request where erp={$vars['z']} and t_orno={$rows1[$key1]['w_orno']} and t_pono={$rows1[$key1]['w_pono']}) and ";
            $query .= '  id=(select max(id) from ap_check_request_comments WHERE ';
            $query .= "      parent_id=(select id from ap_check_request where erp={$vars['z']} and t_orno={$rows1[$key1]['w_orno']} and t_pono={$rows1[$key1]['w_pono']}))";
            $rows_Last_User = tldUtils::getSqlToAssocArray($query);
            $rows1[$key1]['last_User'] = $rows_Last_User[0]['t_usrm'];
        }
        $body = $form->toHTML();
        $body .= include('invCheckRequest.tpl.php');
        break;

    case 'inventory':
        $DEFAULT_TITLE .= "\Inventory";
        $_title = "Inventory details in $DEFAULT_ERP";
        $xItems = [
            't_item' => 'Item#',
            't_dsca' => 'Description',
            't_suno' => 'Supplier#',
            't_nama' => 'Supplier Name',
            't_info' => 'Buyer',
            'whse_loca' => 'Warehouse - Location',
            't_strs' => 'Stock On Hand',
            't_allo' => 'Allocated QTY',
            't_ordr' => 'On order QTY',
            't_copr' => 'STD price',
            'total_copr' => 'Total STD price',
            't_slmp' => 'Slow Moving Rate',
            't_tran' => 'Date Of Transfer',
        ];
        // Declare filters
        $a = [];
        switch ($m[2]) {
            case 'byBuyerByWarehouse':
                // need memory
                ini_set('memory_limit', -1);
                // prepare report
                $cwar = TldDatabase::escape($x);
                $byr = TldDatabase::escape($y);
                if ($cwar !== 'ALL') {
                    $_title .= ", warehouse '$cwar'";
                    if (!empty($cwar)) {
                        $a[] = " stock.t_cwar='$cwar'";
                    } else {
                        $a[] = 'stock.t_cwar IS NULL';
                    }
                }
                if ($byr !== 'ALL') {
                    $_title .= ", buyer '$byr'";
                    if (!empty($byr)) {
                        $a[] = "byr.t_info='$byr'";
                    } else {
                        $a[] = 'byr.t_info IS NULL';
                    }
                }
                // Additional Filters
                switch ($m[3]) {
                    case 'byBuyer':
                        $email = TldDatabase::escape($email);
                        $_title .= " - Dashboard Buyer $email";
                        if (empty($byr)) {
                            $a[] = "byr.t_info='$email'";
                        }
                        break;
                    case 'bySupplier':
                        $suno = TldDatabase::escape($suno);
                        $_title .= " - Dashboard Supplier $suno";
                        $a[] = "itm.t_suno='$suno'";
                        break;
                    case 'byItem':
                        $pn = TldDatabase::escape($pn);
                        $_title .= " - Dashboard Item $pn";
                        $a[] = "itm.t_item='$pn'";
                        break;
                }
                break;
        }
        // Get data
        $baanCompObj = new tldBaanERP($DEFAULT_ERP);
        $rows = $baanCompObj->getInventoryRawDataByConstraints(implode(' AND ', $a));
        // Display
        if (count($rows)) {
            $DEFAULT_MENU .= <<<EOF
<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&out=xls">XLS</a>
&nbsp;|&nbsp;<a href="{$_SERVER['REQUEST_URI']}&out=csv">CSV</a>
EOF;
        }

        switch ($out) {
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
            case 'csv':
                $report = new tldCSV(
                    $rows,
                    [
                        'xItems' => $xItems,
                        'showTitles' => true,
                    ]
                );
                $report->out();
                exit;
                break;
        }

        $report = new tldReportColumnar(
            $rows,
            [
                'xItems' => $xItems,
                'title' => $_title,
                'showNumberOfRows' => true,
                'sumTotalsArray' => ['total_copr'],
            ]
        );
        $body .= $report->fetch();
        break;
    case 'supplier':
        $DEFAULT_TITLE .= "\Supplier";
        switch ($m[2]) {
            case 'revenue':
                $DEFAULT_TITLE .= "\Revenue";
                switch ($m[3]) {
                    case 'byERP':
                        $DEFAULT_TITLE .= "\By BU";

                        // form
                        $form = new HTML_QuickForm('frmByNum', 'post');
                        $form->addElement('hidden', 'm[0]', 'reports');
                        $form->addElement('hidden', 'm[1]', 'supplier');
                        $form->addElement('hidden', 'm[2]', 'revenue');
                        $form->addElement('hidden', 'm[3]', 'byERP');
                        $form->addElement('header', 'title', 'Supplier revenues');
                        $type = tldLocation::getERPList('smartyOptions');

                        $sql =<<<SQL
SELECT t_suno, t_nama FROM ttccom020
SQL;

                        $suppList = [];
                        foreach ($type as $k => $cat) {
                            $data  = tldUtils::getSqlToAssocArray($sql.$k,'odbc', ['src' => 'baan']);
                            $suppList[$k][''] = '';
                            $suppList[$k]['ALL'] = 'ALL';
                            foreach ($data as $model) {
                                $suppList[$k][$model['t_suno']] = $model['t_suno'] . ' -> ' . $model['t_nama'];
                            }
                        }

                        $sel =& $form->addElement('hierselect', 'model', 'Select Suppliers');
                        $sel->setOptions([['' => ''] + $type, ['' => ''] + $suppList]);
                        $form->addElement('submit', 'btnSubmit', 'Submit');
                        $body .= $form->toHTML();
                        $rows = [];
                        $_title = 'Supplier revenues for the last 5 years (from invoice table tdpur046) ';
                        $vars = tldUtils::cleanupFormInput($form->exportValues());

                        // Begin logs
                        tldModLog::insert([
                            'parent_id' => 0,
                            'module' => 'MONITORING',
                            'poster' => $user->getID(),
                            'comment' => TldDatabase::escape('MFG/PUR/Reports/Supplier/Revenu/By BU - START'),
                        ]);
                        // Process
                        $constraints = '';
                        if ($vars['model'][1] !== 'ALL') {
                            $constraints = "supplier.t_suno = '{$vars['model'][1]}'";
                            $_title .= ' for ' . $vars['model'][1];
                        }
                        $xItems = [
                            't_suno' => 'Supplier#',
                            't_nama' => 'Supplier Name',
                            't_ccon' => 'Buyer Name',
                            'comp_tccur' => 'CUR',
                        ];
                        $actualYear = date('Y');

                        // Loop on 5 year window
                        if($constraints != ''){
                            $constraints .= " AND ";
                        }
                        $constraints .= "t_apry IN (";
                        for ($year = $actualYear; $year > ($actualYear - 4); $year--) {
                            $xItems[$year] = "$year";
                            $constraints .= "$year,";
                        }
                        $xItems[$year = $actualYear-4] = "$year";
                        $constraints .= "$year)";
                        $data = tldERPVendor::getRevenueByConstraints($vars['model'][0], $constraints);
                        // Assign values to final result
                        foreach ($data as $val) {
                                if (empty($rows[$val['t_suno']])) {
                                    $rows[$val['t_suno']] = $val;
                                }
                                $rows[$val['t_suno']][$val['t_apry']] = $val['totalInDcur'];
                            }

                        // End logs
                        tldModLog::insert([
                            'parent_id' => 0,
                            'module' => 'MONITORING',
                            'poster' => $user->getID(),
                            'comment' => TldDatabase::escape('MFG/PUR/Reports/Supplier/Revenu/By BU - END'),
                        ]);
                        break;
                }
                break;
        }

        // Display
        switch ($out) {
            case 'xls':
                $report = new tldXLS(
                    $sess['listing']['data'],
                    [
                        'xItems' => $sess['listing']['xItems'],
                        'showTitles' => true,
                    ]
                );
                $report->out();
                exit;
                break;
            default:
                if (!count($rows)) {
                    break;
                }
                $DEFAULT_MENU .= <<<EOF
<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=reports&m[1]=supplier&out=xls">XLS</a>
EOF;
                $sess['listing']['xItems'] = $xItems;
                $sess['listing']['data'] = $rows;
                $report = new tldReportColumnar(
                    $rows,
                    [
                        'xItems' => $xItems,
                        'title' => $_title,
                        'showNumberOfRows' => true,
                        'showzero' => true,
                    ]
                );
                $body .= $report->fetch();
                break;
        }
        break;

    case 'POSummary':
        $xItems = [
            'total_ordered_k' => 'Total Ordered(K)',
            'total_delivered_k' => 'Total Delivered(K)',
            'total_invoiced_k' => 'Total invoiced(K)',
            'currency' => 'Currency',
        ];

        switch ($m[2]) {
            case 'byERP':
                $smarty->assign('locations', tldLocation::getERPList('smartyOptions'));
                $smarty->assign('NEXT_STEP', "$php_self?m[0]=reports&m[1]=POSummary&m[2]=bySupByBuyerByMonth&x=");
                $body = $smarty->fetch('manufacturing/erp/select.erp.tpl');
                break;
            case 'bySupByBuyerByMonth':
                $erpObj = new tldBaanERP($x);
                // BUYER
                $query = <<<EOF
		SELECT DISTINCT t_emno,t_nama FROM ttccom001$x WHERE t_namb!='' AND t_info!=''
EOF;
                $buyers = $suppliers = [];
                foreach (tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']) as $pol) {
                    $buyers[$pol['emno']] = "{$pol['t_emno']}->{$pol['t_nama']}";
                }
                foreach ($erpObj->getSupplierData(null, null, ['orderBy' => 't_suno']) as $sup) {
                    $suppliers[$sup['t_suno']] = "{$sup['t_suno']} -> {$sup['t_nama']}";
                }
                $form = new HTML_QuickForm('frmByNum', 'post');
                $form->addElement('hidden', 'm[0]', 'reports');
                $form->addElement('hidden', 'm[1]', 'POSummary');
                $form->addElement('hidden', 'm[2]', 'bySupByBuyerByMonth');
                $form->addElement('hidden', 'x', $x);
                $form->addElement('header', 'title', 'PO Summary By Month By Supplier By Buyer');
                $form->addElement('select', 'suno', 'Supplier Name', ['' => ''] + $suppliers);
                $form->addElement('select', 'buyer', 'Buyer', ['' => ''] + $buyers);
                $form->addElement('date', 'start', 'Start Date', ['format' => 'Ymd', 'maxYear' => date('Y')]);
                $form->addElement('date', 'end', 'End Date', ['format' => 'Ymd', 'maxYear' => date('Y')]);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->setDefaults(
                    [
                        'start' => ['d' => '01', 'm' => date('m'), 'Y' => date('Y')],
                        'end' => ['d' => date('d'), 'm' => date('m'), 'Y' => date('Y')],
                    ]
                );
                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break;
                }
                $form->freeze();
                $var = $form->exportValues();
                $start = implode('-', $var['start']);
                $end = implode('-', $var['end']);
                $fields = ['suno', 'buyer'];
                $constraints = ['1=1'];
                foreach ($fields as $field) {
                    if (empty($_REQUEST[$field])) {
                        continue;
                    }
                    $val = TldDatabase::escape($_REQUEST[$field]);
                    switch ($field) {
                        case 'suno':
                            $constraints[] = "T2.t_suno='$val'";
                            break;
                        case 'buyer':
                            $constraints[] = "T2.t_ccon='$val'";
                            break;
                    }
                }
                $rows = tldPOL::sumInvoicedByPeriodByConstraints($x, $start, $end, implode(' AND ', $constraints));
                $rows1 = tldPOL::sumOrderedByPeriodByConstraints($x, $start, $end, implode(' AND ', $constraints));
                $rows2 = tldPOL::sumDeliveryByPeriodByConstraints($x, $start, $end, implode(' AND ', $constraints));
                $summary = [];
                if (empty($rows['total_invoiced_k'])) {
                    $rows['total_invoiced_k'] = 0;
                }
                if (empty($rows1['total_ordered_k'])) {
                    $rows1['total_ordered_k'] = 0;
                }
                if (empty($rows2['total_delivered_k'])) {
                    $rows2['total_delivered_k'] = 0;
                }
                $summary[] = array_merge($rows, $rows1, $rows2);

                $ftitle = "PO Summary From $start To $end By Supplier {$var['suno']} By Buyer {$var['buyer']} ERP#$x";
                $report = new tldReportColumnar(
                    $summary,
                    [
                        'xItems' => $xItems,
                        'title' => $ftitle,
                        'showNumberOfRows' => false,
                        '',
                        'showzero' => true,
                    ]
                );
                $body .= $report->fetch();
                break;
        }
        break;

    case 'activityByYearByBU':
        $xItems = [
            'comp' => 'Company',
            't_suno' => 'Vendor#',
            't_nama' => 'Vendor Name',
            't_sust' => 'Vendor Status',
            't_ccon' => 'Buyer Name',
            't_cbrn' => 'Activity Code',
            'nbLineDelivered' => 'Line Delivered',
            'total_delivery' => 'Delivered Qty',
            'revenue' => 'Total Revenue',
            'currency' => 'Currency',
            'reliability' => 'Reliability',
            'ncr' => 'Total NCRs',
            'freq' => 'NCR Frequency',
            'cost' => 'NCR Cost Ratio',
        ];

        switch ($m[2]) {
            case 'byERP':
                $smarty->assign('locations', ['ALL' => 'ALL'] + tldLocation::getERPList('smartyOptions'));
                $smarty->assign('NEXT_STEP', "$php_self?m[0]=reports&m[1]=activityByYearByBU&m[2]=byPeriod&x=");
                $body = $smarty->fetch('manufacturing/erp/select.erp.tpl');
                break;
            case 'byPeriod':
                // Listing
                if ($x !== 'ALL') {
                    $erpObj = new tldBaanERP($x);
                    $data = $erpObj->getSupplierData(null, null, ['orderBy' => 't_suno']);
                    foreach ($data as $sup) {
                        $suppliers[$sup['t_suno']] = "{$sup['t_suno']} -> {$sup['t_nama']}";
                    }
                }
// Form
                $form = new HTML_QuickForm('frmByNum', 'post');
                $form->addElement('hidden', 'm[0]', 'reports');
                $form->addElement('hidden', 'm[1]', 'activityByYearByBU');
                $form->addElement('hidden', 'm[2]', 'byPeriod');
                $form->addElement('hidden', 'x', $x);
                $form->addElement('header', 'title', 'Supplier activity summary by year by BU (from receipt table tdpur045)');
                if ($x !== 'ALL') {
                    $form->addElement('select', 'suno', 'Supplier#', ['' => ''] + $suppliers);
                } else {
                    $form->addElement('text', 'suno', 'Supplier Name');
                }
                $form->addElement('date', 'start', 'Start Date', ['format' => 'Ymd', 'maxYear' => date('Y')]);
                $form->addElement('date', 'end', 'End Date', ['format' => 'Ymd', 'maxYear' => date('Y')]);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->setDefaults(
                    [
                        'start' => ['d' => '01', 'm' => date('m'), 'Y' => date('Y')],
                        'end' => ['d' => date('d'), 'm' => date('m'), 'Y' => date('Y')],
                    ]
                );

                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break;
                }

                $vars = $form->exportValues();
                $start = implode('-', $vars['start']);
                $end = implode('-', $vars['end']);

                $vars = tldUtils::cleanupFormInput($vars);
                // Get BAAN activity
                // -- construct constraints
                $a = '';
                if ($vars['suno']) {
                    $a = ['SUP.t_suno' => $vars['suno']];
                }
                if ($vars['x'] === 'ALL') {
                    foreach (tldLocation::getERPList('smartyOptions') as $key => $val) {
                        $rets = tldERPVendor::getActivityByPeriodByConstraints($key, $start, $end, $a);
                        foreach ($rets as $v) {
                            $rows[$key] = $v;
                        }
                    }
                    // Get WEB activity
                    $vendor = [];
                    foreach ($rows as $key => $row) {
                        if (isset($row['t_suno'])) {
                            $t_suno = $row['t_suno'];
                            $query = <<<EOF
                    SELECT
                        COUNT(id) AS total_ncr
                    FROM ncr
                    WHERE
                        vendor_erp=$key AND vendor_id LIKE trim('$t_suno')
                        AND date BETWEEN '$start' AND '$end'
EOF;
                            $vendor[$key] = tldUtils::getSqlToAssocArray($query);
                            $opts = [
                                'ds' => $start,
                                'de' => $end,
                            ];
                            $ncrfreq[$key] = tldNCR::getFrequencyStatsRatioByVendor($key, $row['t_suno'], $opts);
                            $ncrcost[$key] = tldNCR::getCostStatsRatioByVendor($key, $row['t_suno'], $opts);

                        }
                    }

                    foreach ($vendor as $key => $val) {
                        foreach ($val as $i => $j) {
                            foreach ($j as $z => $y) {
                                if (isset($rows[$key])) {
                                    $c[$key] = $rows[$key];
                                    $c[$key]['ncr'] = $y['total_ncr'];
                                    $c[$key]['freq'] = $ncrfreq[$key];
                                    $c[$key]['cost'] = $ncrcost[$key];
                                    $c[$key]['comp'] = $key ?: $x;
                                }
                            }
                        }
                    }
                    $ftitle = "Supplier activity summary By Date {$start} - {$end} for All ERP (from receipt table tdpur045)";
                    $sess['reports']['activityByYearByBU'] = $c;
                } else {
                    $rows = tldERPVendor::getActivityByPeriodByConstraints($vars['x'], $start, $end, $a);

                    $x = $vars['x'];
                    // Get WEB activity
                    $vendor = [];
                    foreach ($rows as $key => $row) {
                        if (isset($row['t_suno'])) {
                            $t_suno = $row['t_suno'];
                            $query = <<<EOF
                        SELECT
                            COUNT(id) AS ncr
                        FROM ncr
                        WHERE
                            vendor_erp=$x AND vendor_id LIKE trim('$t_suno')
                            AND date BETWEEN '$start' AND '$end'
EOF;
                            $rows[$key] = tldUtils::getSqlRowToAssocArray($query);
                            $opts = [
                                'ds' => $start,
                                'de' => $end,
                            ];
                            $rows[$key]['freq'] = tldNCR::getFrequencyStatsRatioByVendor($x, $row['t_suno'], $opts);
                            $rows[$key]['cost'] = tldNCR::getCostStatsRatioByVendor($x, $row['t_suno'], $opts);
                            $rows[$key]['comp'] = $x;
                            $rows[$key]['t_suno'] = $row['t_suno'];
                            $rows[$key]['t_nama'] = $row['t_nama'];
                            $rows[$key]['t_sust'] = $row['t_sust'];
                            $rows[$key]['t_ccon'] = $row['t_ccon'];
                            $rows[$key]['t_cbrn'] = $row['t_cbrn'];
                            $rows[$key]['currency'] = $row['currency'];
                            $rows[$key]['nbLineDelivered'] = $row['nbLineDelivered'];
                            $rows[$key]['total_delivery'] = $row['total_delivery'];
                            $rows[$key]['revenue'] = $row['revenue'];
                            $rows[$key]['reliability'] = $row['reliability'];
                        }
                    }

                    $ftitle = "Supplier activity summary By Date {$start} - {$end} for ERP {$x} (from receipt table tdpur045)";
                    $sess['reports']['activityByYearByBU'] = $rows;
                }
                break;
        }


        switch ($m[3]) {
            case 'xls':
                $report = new tldXLS(
                    $sess['reports']['activityByYearByBU'],
                    [
                        'xItems' => $xItems,
                        'showTitles' => true,
                    ]
                );
                $report->out();
                exit;
                break;
            default:
                if (!isset($a, $ftitle)) {
                    break;
                }
                $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=reports&m[1]=activityByYearByBU&m[3]=xls">XLS version</a>
EOF;
                $report = new tldReportColumnar(
                    $sess['reports']['activityByYearByBU'],
                    [
                        'xItems' => $xItems,
                        'title' => $ftitle,
                        'showNumberOfRows' => true,
                        'showzero' => true,
                    ]
                );
                $body .= $report->fetch();
                break;
        }
        break;
    case 'PurItemsToControl':
        $xItems = [
            'item' => 'Item',
            'dsca' => 'Description',
            'suno' => 'Supplier',
            'nama' => 'Name',
            'qual' => 'Sup.Control',
        ];
        // Get listing
        $erpList = tldLocation::getERPList('smartyOptions');
        $form = new HTML_QuickForm('frmPurItemsToControl', 'get', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'PurItemsToControl');
        $form->addElement('header', 'title', 'Select company:');
        $form->addElement('select', 'z', 'Company#',
            ['' => ''] + $erpList);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('z', 'This is required', 'required');

        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $query = <<<EOF
select
itm.t_item item,
itm.t_dsca dsca,
itm.t_suno suno,
(select com.t_nama from ttccom020{$vars['z']} com where com.t_suno=itm.t_suno) 'nama',
(select CASE com.t_qual WHEN 1 then 'YES' WHEN 2 then 'NO' END from ttccom020{$vars['z']} com where com.t_suno=itm.t_suno) 'qual'
from ttiitm001{$vars['z']} itm where itm.t_qual=1
EOF;
        $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
        $caption = "Purchase Items to Control for company {$vars['z']}";
        $report = new tldReportColumnar($rows,
            [
                'xItems' => $xItems,
                'title' => $caption,
            ]
        );
        $body .= $report->fetch();
        break;
    case 'PNNotReceived':
        $xItems = [
            't_suno' => 'Vendor#',
            't_nama' => 'Vendor Name',
            't_orno' => 'PO#',
            't_cotp' => 'Order Type',
            't_odat' => 'PO Date',
            't_pono' => 'Line#',
            't_item' => 'PN#',
            't_revi' => 'Rev',
            't_dsca' => 'PN description',
            't_aitc' => 'Vendor PN#',
            't_oqua' => 'Ordered Qty',
            't_dqua' => 'Delivery Qty',
            't_bqua' => 'Back Order Qty',
            't_ddta' => 'Orig Del Date',
            't_resc' => 'Resc Del Date',
            't_resm' => 'Resc Message',
            't_ddtc' => 'Confirm Date',
            't_ddtd' => 'Updated Date',
            't_oltm' => 'Lead Time',
            'th_date' => 'Theoretical Delivery Date',
            'late_resc' => 'Days against Resc Date',
            't_copr' => 'Std Cost Price',
            't_prip' => 'Item pur.price',
            't_pric' => 'Order price',
            't_namb' => 'Contact',
            't_csgp' => 'Pur.Stat.Group',
            't_cbrn' => 'Line of Business',

        ];
        // Get listing
        $erpList = tldLocation::getERPList('smartyOptions');
        $form = new HTML_QuickForm('frmPO', 'get', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'PNNotReceived');
        $form->addElement('header', 'title', 'Select company:');
        $form->addElement('select', 'x', 'Company#', ['' => ''] + $erpList);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $body = $form->toHTML();
        if (!$form->validate()) {
            break;
        }
        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $x = $vars['x'];

        $a = <<<EOF
T2.t_srnb=(SELECT MAX(T3.t_srnb) FROM ttdpur045$x AS T3
WHERE T2.t_orno=T3.t_orno AND T2.t_pono=T3.t_pono)
AND (T2.t_spur<9 OR (T2.t_spur=9 AND T2.t_bqua>0))
AND T1.t_oqua>T1.t_dqua AND ITM.t_copr=0
EOF;

        $_title .= ", for ITEM '$x'";
        $rows = tldPOL::byConstraints($x, $a);
        $report = new tldReportColumnar(
            $rows,
            [
                'xItems' => $xItems,
                'title' => 'Open PO with 0 standard cost',
            ]
        );
        $body .= $report->fetch();
        if (isset($rows)) {
            $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[3]=xls">XLS version</a>
EOF;
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
            }
        }
        break;

    case 'ReceiptLabels':
        $xItems = [
            'item' => 'Item',
            'loca' => 'Location',
            'xqua' => 'Position in qty',
            'bqua' => 'Quantity',
            'dsca' => 'Description',
            'orno' => 'Order',
            'pono' => 'Position',
            'revi' => 'Revision',
            'ddta' => 'Planned date',
            'ddtc' => 'Confirmed date',
            'ddtd' => 'Changed date',
            'ddtx' => 'Actual date',

        ];
        // Get listing
        $erpList = tldLocation::getERPList('smartyOptions');
        // Get form
        $form = new HTML_QuickForm('frmReceiptLabels', 'get', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'ReceiptLabels');
        $form->addElement('header', 'title', 'Select PO and company:');
        $form->addElement('text', 'PO', 'Purchase Order:');
        $form->addElement('select', 'z', 'Company#',
            ['' => ''] + $erpList);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('z', 'This is required', 'required');

        if ($form->validate()) {
            $vars = tldUtils::cleanupFormInput($form->exportValues());
            $rows = tldUtils::getSqlToAssocArray("EXEC eVendorPoLabels '{$vars['PO']}','{$vars['z']}'", 'odbc', ['src' => 'baan']);

            $heightLine = 30;
            $heightLineTop = 0;
            $heightLineBottom = 35;
            $heightMarginTopLabel = 22;
            $heightMarginBottomLabel = 23;
            $html = '';
            $html .= '<html>';
            $html .= "<head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'></head>";
            $html .= '<body>';

            $Col = 1;
            $Lig = 1;

            $barcodeOptions = [
                'height' => 30,
                'width' => 100,
            ];
            foreach ($rows as $key1 => $value1) {
                if ($Lig === 11) {
                    // Bottom margin
                    $html .= '</table>';
                    $html .= "<p style='page-break-after:always;'></p>";
                    $Lig = 1;
                }

                if ($Lig === 1) {
                    // Top margin per page
                    $html .= "<table border=0 style='width:100%; border-collapse:collapse; padding:0pt;'>";
                }

                if ($Col == 1) {
                    $html .= "<tr style='width:100%; height:" . $heightLine . 'pt; max-height:' . $heightLine . "pt; padding:0pt;'><td style='width:55%; height:" . $heightLine . 'pt; max-height:' . $heightLine . "pt; padding:0pt;'>";
                } else {
                    $html .= "<td style='width:45%; height:" . $heightLine . 'pt; max-height:' . $heightLine . "pt; padding:0pt;'>";
                }

                // top margin per label
                $html .= "<table border=0 style='width:100%; table-layout:fixed; border-collapse:collapse; padding:0pt;'>";
                $html .= "  <tr style='width:100%; height:" . $heightMarginTopLabel . 'pt; max-height:' . $heightMarginTopLabel . "pt; padding:0pt;'>";
                $html .= "    <td colspan=2 style='min-width:100%; width:100%; height:" . $heightMarginTopLabel . 'pt; max-height:' . $heightMarginTopLabel . "pt; padding:0pt;'>&nbsp;</td>";
                $html .= '  </tr>';


                $Image = new tldBarcode($rows[$key1]['orno'], 'Code39', $barcodeOptions);
                $html .= "  <tr style='width:50%; height:" . $heightLine . 'pt; max-height:' . $heightLine . "pt; padding:0pt;'>";
                $html .= "    <td style='min-width:67%; width:67%; height:" . $heightLine . 'pt; max-height:' . $heightLine . "pt; overflow:hidden; font-family:monospace; font-size:10pt; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; padding:0pt;'>PO:&nbsp;" . $rows[$key1]['orno'] . '&nbsp;Line:&nbsp;' . $rows[$key1]['pono'] . '</td>';
                $html .= "    <td style='min-width:33%; width:33%; max-width:33%; height:" . $heightLine . 'pt; max-height:' . $heightLine . "pt; padding:0pt;'>" . $Image->toHtml() . '</td>';
                $html .= '  </tr>';

                $Image = new tldBarcode($rows[$key1]['pono'], 'Code39', $barcodeOptions);
                $html .= "  <tr style='width:50%; height:" . $heightLine . 'pt; max-height:' . $heightLine . "pt;'>";
                $html .= "    <td style='min-width:67%; width:67%; height:" . $heightLine . 'pt; max-height:' . $heightLine . "pt; overflow:hidden; font-family:monospace; font-size:10pt; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; padding:0pt;'>PN:&nbsp;" . $rows[$key1]['item'] . '&nbsp;Rev:&nbsp;' . $rows[$key1]['revi'] . '</td>';
                $html .= "    <td style='min-width:33%; width:33%; max-width:33%; height:" . $heightLine . 'pt; max-height:' . $heightLine . "pt; padding:0pt;'>" . $Image->toHtml() . '</td>';
                $html .= '  </tr>';

                $Image = new tldBarcode($rows[$key1]['item'], 'Code39', $barcodeOptions);
                $html .= "  <tr style='width:50%; height:" . $heightLine . 'pt; max-height:' . $heightLine . "pt;'>";
                $html .= "    <td style='min-width:67%; width:67%; height:" . $heightLine . 'pt; max-height:' . $heightLine . "pt; overflow:hidden; font-family:monospace; font-size:10pt; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; padding:0pt;'>" . $rows[$key1]['dsca'] . '</td>';
                $html .= "    <td style='min-width:33%; width:33%; max-width:33%; height:" . $heightLine . 'pt; max-height:' . $heightLine . "pt; padding:0pt;'>" . $Image->toHtml() . '</td>';
                $html .= '  </tr>';

                $html .= "  <tr style='width:50%; height:" . $heightLine . 'pt; max-height:' . $heightLine . "pt;'>";
                $html .= "    <td              style='min-width:67%; width:67%;                height:" . $heightLine . 'pt; max-height:' . $heightLine . "pt; overflow:hidden; font-family:monospace; font-size:10pt; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; padding:0pt;'>" . $rows[$key1]['ddtx'] . '&nbsp;&nbsp;Loc:&nbsp;' . $rows[$key1]['loca'] . '</td>';
                $html .= "    <td align=center style='min-width:33%; width:33%; max-width:33%; height:" . $heightLine . 'pt; max-height:' . $heightLine . "pt; overflow:hidden; font-family:monospace; font-size:10pt; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; padding:0pt;'>QTY:&nbsp;" . $rows[$key1]['xqua'] . '/' . $rows[$key1]['bqua'] . '</td>';
                $html .= '  </tr>';

                // bottom margin per label, not on last label
                if ($Lig < 9) {
                    $html .= "  <tr style='width:100%; height:" . $heightMarginBottomLabel . 'pt; max-height:' . $heightMarginBottomLabel . "pt; padding:0pt;'>";
                    $html .= "    <td colspan=2 style='min-width:100%; width:100%; height:" . $heightMarginBottomLabel . 'pt; max-height:' . $heightMarginBottomLabel . "pt; padding:0pt;'>&nbsp;</td>";
                    $html .= '  </tr>';
                }

                $html .= '</table>';

                if ($Col === 1) {
                    $Col = 2;
                    $html .= '</td>';
                } else {
                    $Col = 1;
                    $html .= '</td></tr>';
                }

                ++$Lig;
            }


            $html .= '</body>';
            $html .= '</html>';

            $pdf = new tldHTML2PDF($html, ['encoding' => 'utf-8', 'margin' => '1']);
            $pdf->outFile('aaa.pdf');
            exit;

        }
        $body = $form->toHTML();
        break;
    case 'MRPOrders':
        $xItems = [
            'koor' => 'Kind of order',
            'orno' => 'Order',
            'pono' => 'Order line',
            'suno' => 'Supplier',
            'nama' => 'Supp name',
            'item' => 'Item',
            'dsca' => 'Description',
            'rqan' => 'Quantity',
            'pric' => 'Price',
            'oltm' => 'Supplier lead time',
            'pddt' => 'Actual date',
            'resm' => 'Message',
            'date' => 'Requirement date',
            'namb' => 'Buyer',
        ];

        // Get listing
        $erpList = tldLocation::getERPList('smartyOptions');
        // Get form
        $form = new HTML_QuickForm('frmMRPOrders', 'get', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'MRPOrders');
        $form->addElement('header', 'title', 'Select company:');
        $form->addElement('select', 'z', 'Company#', ['' => ''] + $erpList);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('z', 'This is required', 'required');

        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());

        $query = <<<EOF
select
case mrp030.t_koor
WHEN '1' THEN 'Production order'
WHEN '2' THEN 'Purchase order'
WHEN '3' THEN 'Sales order'
WHEN '4' THEN 'POF-MPS'
WHEN '5' THEN 'POA-MPS'
WHEN '6' THEN 'POF-MRP'
WHEN '7' THEN 'POA-MRP'
WHEN '8' THEN 'POF-PRP'
WHEN '9' THEN 'POA-PRP'
WHEN '10' THEN 'POM-PRP'
WHEN '11' THEN 'MRP sales forecast'
WHEN '12' THEN 'MPS rough material req.'
WHEN '13' THEN 'Sales quotation'
WHEN '14' THEN 'Purchase contract'
WHEN '15' THEN 'Sales contract'
WHEN '16' THEN 'Warehouse order'
WHEN '17' THEN 'Service order'
WHEN '18' THEN 'PRP purchase order'
WHEN '19' THEN 'PRP warehouse order'
WHEN '20' THEN 'ISM warehouse order'
WHEN '21' THEN 'Replenishment order'
WHEN '22' THEN 'DRP replenishment order'
WHEN '23' THEN 'DRP sales forecast'
WHEN '24' THEN 'ECO orders'
WHEN '25' THEN 'MPS interplant order'
WHEN '26' THEN 'Production batch'
WHEN '27' THEN 'Warehouse order'
WHEN '28' THEN 'MPS production batch'
WHEN '29' THEN 'MRP production batch'
END koor,
	mrp030.t_orno orno,
	mrp030.t_pono pono,
	pur040.t_suno suno,
	com020.t_nama nama,
	mrp030.t_item item,
	itm001.t_dsca dsca,
	mrp030.t_rqan rqan,
	convert(varchar(100), cast(pur041.t_pric as decimal(15,2))) as  pric,
	itm001.t_oltm oltm,
	CASE substring(convert(varchar, mrp030.t_pddt, 120), 1, 10)
        WHEN '1753-01-01' THEN ''
        ELSE substring(convert(varchar, mrp030.t_pddt, 120), 1, 10)
    END pddt,
	case mrp030.t_resm
		WHEN '1' THEN 'Reschedule-in'
		WHEN '2' THEN 'Reschedule-out'
	END resm,
	CASE substring(convert(varchar, mrp030.t_date, 120), 1, 10)
        WHEN '1753-01-01' THEN ''
        ELSE substring(convert(varchar, mrp030.t_date, 120), 1, 10)
    END date,
	com001.t_namb namb
from
	ttimrp030{$vars['z']} mrp030,
	ttdpur040{$vars['z']} pur040,
	ttccom020{$vars['z']} com020,
	ttiitm001{$vars['z']} itm001,
	ttdpur041{$vars['z']} pur041,
	ttccom001{$vars['z']} com001
where
	mrp030.t_orno = pur040.t_orno and
	mrp030.t_orno = pur041.t_orno and
	mrp030.t_pono = pur041.t_pono and
	mrp030.t_item = itm001.t_item and
	itm001.t_buyr = com001.t_emno and
	pur040.t_suno = com020.t_suno
EOF;


        $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
        $caption = "MRP Orders for company {$vars['z']}";

        if (isset($rows, $caption)) {
            $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=xls">XLS version</a>&nbsp;|&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=csv">CSV version</a>
EOF;
    switch ($m[2]) {
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
        case 'csv':
            $report = new tldCSV(
                $rows,
                [
                    'xItems' => $xItems,
                    'showTitles' => true,
                ]
            );
            $report->out();
            exit;
            break;
        default:

            $report = new tldReportColumnar($rows,
                [
                    'xItems' => $xItems,
                    'title' => $caption,
                ]
            );
            $body .= $report->fetch();

            break;
    }
}
        break;

    case 'CriticalcomponentsStatus':
        $xItems = [
            't_item' => 'Item',
            't_dsca' => 'Description',
            't_csig' => 'Sig Code',
            't_suno' => 'Suppl',
            't_kitm' => 'Kind',
            't_stoc' => 'On hand',
            't_sfst' => 'Safe',
            't_ordr' => 'On order',
            't_allo' => 'Alloc',
            't_cuni' => 'Unit',
            't_cwar' => 'Wareh',
            't_citg' => 'Itm grp',
            't_cpcp' => 'Cost Price Comp',
            't_copr' => 'Std Cost',
            't_pics' => 'Consum.',
            't_osys' => 'Order Sys',
            't_cpha' => 'Phan',
            't_namb' => 'Byr',
            't_oltm' => 'LT',
            't_crmp' => 'Critical component status',
            't_nama' => 'Suppl name',
        ];

        // Get listing
        $erpList = tldLocation::getERPList('smartyOptions');
        // Get form
        $form = new HTML_QuickForm('frmCriticalcomponentsStatus', 'get', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'CriticalcomponentsStatus');
        $form->addElement('header', 'title', 'Select company:');
        $form->addElement('select', 'z', 'Company#',
            ['' => ''] + $erpList);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('z', 'This is required', 'required');

        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());

        $query = <<<EOF
select
	itm001.t_item,
	itm001.t_dsca,
	itm001.t_csig,
	itm001.t_suno,
	itm001.t_kitm,
	itm001.t_stoc,
	itm001.t_sfst,
	itm001.t_ordr,
	itm001.t_allo,
	itm001.t_cuni,
	itm001.t_cwar,
	itm001.t_citg,
	itm001.t_cpcp,
	itm001.t_copr,
	itm001.t_pics,
	itm001.t_osys,
	itm001.t_cpha,
	com001.t_namb,
	itm001.t_oltm,
	itm001.t_crmp,
	com020.t_nama
from
	ttiitm001{$vars['z']} itm001,
	ttccom001{$vars['z']} com001,
	ttccom020{$vars['z']} com020
where
	itm001.t_buyr=com001.t_emno and
	itm001.t_suno=com020.t_suno and
	itm001.t_crmp = 1 and
	itm001.t_kitm = 1
EOF;


        $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
        $caption = "Critical components in MPS for company {$vars['z']}";

        if (isset($rows, $caption)) {
            $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=xls">XLS version</a>&nbsp;|&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=csv">CSV version</a>
EOF;
    switch ($m[2]) {
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
        case 'csv':
            $report = new tldCSV(
                $rows,
                [
                    'xItems' => $xItems,
                    'showTitles' => true,
                ]
            );
            $report->out();
            exit;
            break;
        default:

            $report = new tldReportColumnar($rows,
                [
                    'xItems' => $xItems,
                    'title' => $caption,
                ]
            );
            $body .= $report->fetch();

            break;
    }
}
        break;

    case 'PipoItems':
        $xItems = [
            'w_item' => 'Item',
            'w_dscc' => 'Size',
            'w_dscd' => 'Standard',
            'w_dsca' => 'Description',
            'w_cpha' => 'Phantom',
            'w_kitm' => 'Kind of item',
            'w_stoc' => 'Inventory on hand',
            'w_copr' => 'Standard cost price',
            'w_hmvp' => 'Firm Inbound',
            'w_hmvm' => 'Firm Outbound',
            'w_smvp' => 'Projected Inbound',
            'w_smvm' => 'Projected Outbound',
            'w_hsmvm' => 'Total Outbound',
            'w_basc1' => 'Switch',
            'w_basc2' => 'Switch including projected (forecasts)',
            'w_buyr' => 'Buyer',
            'w_init' => 'Initials',
            'w_suno' => 'Supplier',
            'w_nama' => 'Sup name',
            'w_sfst' => 'Safety stock',
            'w_oltm' => 'Order lead time',
            'w_ctyp' => 'Product type',
            'w_cwar' => 'Warehouse',
        ];

        // Get listing
        $erpList = tldLocation::getERPList('smartyOptions');
        // Get form
        $form = new HTML_QuickForm('frmPipoItems', 'get', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'PipoItems');
        $form->addElement('header', 'title', 'Select company:');
        $form->addElement('select', 'z', 'Company#',
            ['' => ''] + $erpList);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('z', 'This is required', 'required');

        if ($form->validate()) {
            $vars = tldUtils::cleanupFormInput($form->exportValues());
            $rows = tldUtils::getSqlToAssocArray("EXEC PIPO_Items '{$vars['z']}'", 'odbc', ['src' => 'baan']);
            $caption = "Phase In Phase Out follow up for company {$vars['z']}";

            if (isset($rows, $caption)) {
                $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=xls">XLS version</a>&nbsp;|&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=csv">CSV version</a>
EOF;
                switch ($m[2]) {
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
                    case 'csv':
                        $report = new tldCSV(
                            $rows,
                            [
                                'xItems' => $xItems,
                                'showTitles' => true,
                            ]
                        );
                        $report->out();
                        exit;
                        break;
                    default:

                        $report = new tldReportColumnar($rows,
                            [
                                'xItems' => $xItems,
                                'title' => $caption,
                            ]
                        );
                        $body .= $report->fetch();

                        break;
                }
            }


        } else {
            $body = $form->toHTML();
        }
        break;

    case 'matrixPO':
        $cells = [];
        $SUNO_DASH = $vars['t_suno'];
        $BU_DASH = new tldLocation(tldLocation::getIDByERP($DEFAULT_ERP));

        switch ($m[2]) {
            case 'POLineCancellationByWarehouseForERP':
                if ($buyer) {
                    $buyer = new tldUser($buyer);
                    $buyer_email = $buyer->getEmail();

                    $form1 = new tldMatrix(
                        tldPOL::countCancellationByWHSPddtByERPByBuyer($DEFAULT_ERP, $buyer_email),
                        'level', 't_cwar', 'num',
                        "$php_self?m[0]=reports&m[1]=byCancellationByERPPddt&m[2]=byWHSbyBuyer&z=$buyer_email",
                        'PO Cancellation count by urgency, by Warehouse - ' . $BU_DASH->getShortName(),
                        [
                            'xItems' => ['Urgent', 'Week', 'Month', '>Month'],
                            'doNotShowXTotals' => true,
                        ]
                    );
                    // Matrix Amount PO Line Cancellation By ERP Level
                    $form2 = new tldMatrix(
                        tldPOL::countValueCancellationByWHSPddtByERPByBuyer($DEFAULT_ERP, $buyer_email),
                        'level', 't_cwar', 'num',
                        "$php_self?m[0]=reports&m[1]=byCancellationByERPPddt&m[2]=byWHSbyBuyer&z=$buyer_email",
                        'PO Cancellation EUR Amount, by urgency, by Warehouse - ' . $BU_DASH->getShortName(),
                        [
                            'xItems' => ['Urgent', 'Week', 'Month', '>Month'],
                            'doNotShowXTotals' => true,
                        ]
                    );
                } else {
                    $form1 = new tldMatrix(
                        tldPOL::countCancellationByWHSPddtByERP($DEFAULT_ERP),
                        'level', 't_cwar', 'num',
                        "$php_self?m[0]=reports&m[1]=byCancellationByERPPddt&m[2]=byWHS",
                        'PO Cancellation count by urgency, by Warehouse - ' . $BU_DASH->getShortName(),
                        [
                            'xItems' => ['Urgent', 'Week', 'Month', '>Month'],
                            'doNotShowXTotals' => true,
                        ]
                    );
                    // Matrix Amount PO Line Cancellation By ERP Level
                    $form2 = new tldMatrix(
                        tldPOL::countValueCancellationByWHSPddtByERP($DEFAULT_ERP),
                        'level', 't_cwar', 'num',
                        "$php_self?m[0]=reports&m[1]=byCancellationByERPPddt&m[2]=byWHS",
                        'PO Cancellation EUR Amount, by urgency, by Warehouse - ' . $BU_DASH->getShortName(),
                        [
                            'xItems' => ['Urgent', 'Week', 'Month', '>Month'],
                            'doNotShowXTotals' => true,
                        ]
                    );
                }

                break;

            case 'POLineReschedulingByWarehouseForERP':
                if ($buyer) {
                    $form1 = new tldMatrix(
                        tldPOL::countReschedulingByWHSPddtByERPByBuyer($DEFAULT_ERP, $buyer_email, $f1),
                        'level', 't_cwar', 'num',
                        "$php_self?m[0]=reports&m[1]=byReschedulingByERPPddt&m[2]=byWHSbyBuyer&io=$f1&z=$buyer_email",
                        'PO Rescheduling count by urgency, by Warehouse - ' . $BU_DASH->getShortName(),
                        [
                            'xItems' => ['Urgent', 'Week', 'Month', '>Month'],
                            'doNotShowXTotals' => true,
                        ]
                    );
                    // Matrix Amount PO Line Rescheduling By ERP Level
                    $form2 = new tldMatrix(
                        tldPOL::countValueReschedulingByWHSPddtByERPByBuyer($DEFAULT_ERP, $buyer_email, $f2),
                        'level', 't_cwar', 'num',
                        "$php_self?m[0]=reports&m[1]=byReschedulingByERPPddt&m[2]=byWHSbyBuyer&io=$f2&z=$buyer_email",
                        'PO Rescheduling EUR Amount, by urgency, by Warehouse - ' . $BU_DASH->getShortName(),
                        [
                            'xItems' => ['Urgent', 'Week', 'Month', '>Month'],
                            'doNotShowXTotals' => true,
                        ]
                    );
                } else {
                    $form1 = new tldMatrix(
                        tldPOL::countReschedulingByWHSPddtByERP($DEFAULT_ERP, $f1),
                        'level', 't_cwar', 'num',
                        "$php_self?m[0]=reports&m[1]=byReschedulingByERPPddt&m[2]=byWHS&io=$f1",
                        'PO Rescheduling count by urgency, by Warehouse - ' . $BU_DASH->getShortName(),
                        [
                            'xItems' => ['Urgent', 'Week', 'Month', '>Month'],
                            'doNotShowXTotals' => true,
                        ]
                    );
                    // Matrix Amount PO Line Rescheduling By ERP Level
                    $form2 = new tldMatrix(
                        tldPOL::countValueReschedulingByWHSPddtByERP($DEFAULT_ERP, $f2),
                        'level', 't_cwar', 'num',
                        "$php_self?m[0]=reports&m[1]=byReschedulingByERPPddt&m[2]=byWHS&io=$f2",
                        'PO Rescheduling EUR Amount, by urgency, by Warehouse - ' . $BU_DASH->getShortName(),
                        [
                            'xItems' => ['Urgent', 'Week', 'Month', '>Month'],
                            'doNotShowXTotals' => true,
                        ]
                    );
                }

                break;
        }

        $cells[] = $form1->fetch();
        $cells[] = $form2->fetch();

        function _getIOLink($fnum)
        {
            global $php_self;
            $html = '';
            $params = $_GET;
            $key = "f{$fnum}";
            $filter = $params[$key];
            if ('i' !== $filter) {
                $html .= "<a href='{$php_self}?" . http_build_query(array_merge($params, [$key => 'i'])) . "'>";
            }
            $html .= 'Reschedule In';
            if ('i' !== $filter) {
                $html .= '</a>';
            }
            $html .= ' &nbsp; ';
            if ('o' !== $filter) {
                $html .= "<a href='{$php_self}?" . http_build_query(array_merge($params, [$key => 'o'])) . "'>";
            }
            $html .= 'Reschedule Out';
            if ('o' !== $filter) {
                $html .= '</a>';
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
                'cols' => 2,
                'attribs' => ['table' => " width='100%'", 'tr' => " bgcolor='#FFFFFF'"],
                'title' => 'Common Purchasing matrix reports',
            ]
        );
        $body .= $report->fetch();

        break;

//# PPV BEGIN #######################################
    case 'PPVInvDashBoard':

        // Get listing
        $erpList = tldLocation::getERPList('smartyOptions');

        // Get form
        $form = new HTML_QuickForm('frmPPVInvDashBoard', 'get', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'PPVInvDashBoard');
        $form->addElement('header', 'title', 'Select dates and company:');
        $form->addElement('date', 'x', 'From', ['format' => 'Y-m-d', 'minYear' => date('Y') - 5, 'maxYear' => date('Y') + 2]);
        $form->addElement('date', 'y', 'To', ['format' => 'Y-m-d', 'minYear' => date('Y') - 5, 'maxYear' => date('Y') + 2]);
        $form->addElement('select', 'z', 'Company#', ['' => ''] + $erpList);
        $form->addElement('text', 'v', 'Nb top');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('x', 'This is required', 'required');
        $form->addRule('y', 'This is required', 'required');
        $form->addRule('z', 'This is required', 'required');
        $form->addRule('v', 'This is required', 'required');
        $form->setDefaults(['x' => date('Y-01-01')]);
        $form->setDefaults(['y' => date('Y-m-d')]);
        $form->setDefaults(['v' => '20']);

        if (isset($_GET['erp'])) {
            $form->setDefaults(['z' => $_GET['erp']]);
        }

        if ($form->validate()) {
            $vars = tldUtils::cleanupFormInput($form->exportValues());


            $vars['from'] = implode('-', $vars['x']);
            $vars['to'] = implode('-', $vars['y']);

            $vars['$dateTmpF'] = $vars['x']['Y'] . '-' . substr('00' . $vars['x']['m'], -2) . '-' . substr('00' . $vars['x']['d'], -2);
            $vars['$dateTmpT'] = $vars['y']['Y'] . '-' . substr('00' . $vars['y']['m'], -2) . '-' . substr('00' . $vars['y']['d'], -2);

            if (strtotime($vars['to']) - strtotime($vars['from']) > 366 * 24 * 60 * 60) {
                $vars['to'] = date('Y-m-d', strtotime($vars['from']) + 366 * 24 * 60 * 60);
                $vars['$dateTmpT'] = $vars['to'];
            }

            $vars['uidF'] = '0';
            $vars['uidT'] = '999999';


            // 100% PPV
            $xItems = [
                'stdmatonly' => 'STD(MAT ONLY) for inv.qty',
                'poamount' => 'PO amnt for inv.qty',
                'amtb' => 'INV AMOUNT',
                'PPVinv' => 'PPV INV',
                'FxinvImpact' => 'FX INV',
                'PPVinvnet' => 'PPV INV NET',
                'VarRate' => 'VAR%',
                'stdeach'=>'STD(MAT Only) each',
                'poeach'=>'PO Amount each',
            ];

            $rows = tldUtils::getSqlToAssocArray("EXEC PPV_Inv_All_Cumul '{$vars['$dateTmpF']}','{$vars['$dateTmpT']}', '{$vars['z']}', '{$vars['uidF']}', '{$vars['uidT']}', 'N'", 'odbc', ['src' => 'baan']);
            $rows2 = tldUtils::getSqlToAssocArray("EXEC PPV_Inv_Top '{$vars['$dateTmpF']}','{$vars['$dateTmpT']}', '{$vars['z']}', '{$vars['uidF']}', '{$vars['uidT']}', 'D', '{$vars['v']}'", 'odbc', ['src' => 'baan']);
            $total = 0;
            foreach ($rows2 as $key => $val) {
                $rows2[$key]['stdeach'] = round($val['stdmatonly']/$val['oqua'],1);
                $rows2[$key]['poeach'] = round($val['poamount']/$val['oqua'],1);
                $total += $val['oqua'];
                if ($rows2[$key]['amtb']==0) {
                    unset($rows2[$key]);
                }
            }
            foreach ($rows as $key => $val) {
                $rows[$key]['stdeach'] = round($val['stdmatonly']/$total,1);
                $rows[$key]['poeach'] = round($val['poamount']/$total,1);
                if ($rows[$key]['amtb']==0) {
                    unset($rows[$key]);
                }
            }
            $caption = "Report from {$vars['from']} to {$vars['to']} for company {$vars['z']} in local currency<br><br>100% PPV";

            $report = new tldReportColumnar($rows,
                [
                    'xItems' => $xItems,
                    'title' => $caption,
                ]
            );
            $body .= $report->fetch();

            // TOP 20 BONI
            $xItems = [
                'item' => 'ITEM',
                'dsca' => 'LABEL',
                'suno' => 'SUPPLIER',
                'nama' => 'SUPPLIER NAME',
                'ccon' => 'BUYER',
                'namb' => 'BUYER NAME',
                'orno' => 'ORDER',
                'pono' => 'POSITION',
                'oqua' => 'ORDER LINE QTY',
                'stdmatonly' => 'STD(MAT ONLY) for inv.qty',
                'poamount' => 'PO amnt for inv.qty',
                'amtb' => 'INV AMOUNT',
                'PPVinv' => 'PPV INV',
                'FxinvImpact' => 'FX',
                'PPVinvnet' => 'PPV INV NET',
                'VarRate' => 'VAR%',
                'VarStar' => '*',
                'stdeach'=>'STD(MAT Only) each',
                'poeach'=>'PO Amount each',
            ];

            $caption = 'Top ' . $vars['v'] . ' Boni';

            $report = new tldReportColumnar($rows2,
                [
                    'xItems' => $xItems,
                    'title' => $caption,
                    'sumTotalsArray' => ['stdmatonly', 'poamount', 'amtb', 'PPVinv', 'FxinvImpact', 'PPVinvnet'],
                ]
            );
            $body .= $report->fetch();

            // TOP 20 MALI
            $xItems = [
                'item' => 'ITEM',
                'dsca' => 'LABEL',
                'suno' => 'SUPPLIER',
                'nama' => 'SUPPLIER NAME',
                'ccon' => 'BUYER',
                'namb' => 'BUYER NAME',
                'orno' => 'ORDER',
                'pono' => 'POSITION',
                'oqua' => 'ORDER LINE QTY',
                'stdmatonly' => 'STD(MAT ONLY) for inv.qty',
                'poamount' => 'PO amnt for inv.qty',
                'amtb' => 'INV AMOUNT',
                'PPVinv' => 'PPV INV',
                'FxinvImpact' => 'FX',
                'PPVinvnet' => 'PPV INV NET',
                'VarRate' => 'VAR%',
                'VarStar' => '*',
            ];

            $rows = tldUtils::getSqlToAssocArray("EXEC PPV_Inv_Top '{$vars['$dateTmpF']}','{$vars['$dateTmpT']}', '{$vars['z']}', '{$vars['uidF']}', '{$vars['uidT']}', 'A',  '{$vars['v']}'", 'odbc', ['src' => 'baan']);
            $caption = 'Top ' . $vars['v'] . ' Mali';
            foreach ($rows as $key => $val) {
                if ($rows[$key]['amtb']==0) {
                    unset($rows[$key]);
                }
            }
            $report = new tldReportColumnar($rows,
                [
                    'xItems' => $xItems,
                    'title' => $caption,
                    'sumTotalsArray' => ['stdmatonly', 'poamount', 'amtb', 'PPVinv', 'FxinvImpact', 'PPVinvnet'],
                ]
            );
            $body .= $report->fetch();

            // Volatility
            $xItems = [
                'stdmatonly' => 'STD(MAT ONLY) for inv.qty',
                'poamount' => 'PO amnt for inv.qty',
                'amtb' => 'INV AMOUNT',
                'PPVinv' => 'PPV INV',
                'FxinvImpact' => 'FX INV',
                'PPVinvnet' => 'PPV INV NET',
                'VarRate' => 'VAR% VOLATILITY',

            ];

            $rows = tldUtils::getSqlToAssocArray("EXEC PPV_Inv_All_Cumul '{$vars['$dateTmpF']}','{$vars['$dateTmpT']}', '{$vars['z']}', '{$vars['uidF']}', '{$vars['uidT']}', 'Y'", 'odbc', ['src' => 'baan']);
            $caption = 'Volatility (All fields in absolute value)';
            foreach ($rows as $key => $val) {
                if ($rows[$key]['amtb']==0) {
                    unset($rows[$key]);
                }
            }
            $report = new tldReportColumnar($rows,
                [
                    'xItems' => $xItems,
                    'title' => $caption,
                ]
            );
            $body .= $report->fetch();


            // By buyer
            $xItems = [
                'ccon' => 'BUYER',
                'namb' => 'BUYER NAME',
                'stdmatonly' => 'STD(MAT ONLY) for inv.qty',
                'poamount' => 'PO amnt for inv.qty',
                'amtb' => 'INV AMOUNT',
                'PPVinv' => 'PPV INV',
                'FxinvImpact' => 'FX',
                'PPVinvnet' => 'PPV INV NET',
                'stdmatPO' => 'STD MAT PO',
                'VarRate' => 'VAR%',
            ];

            $rows = tldUtils::getSqlToAssocArray("EXEC PPV_Inv_Buyer_Cumul '{$vars['$dateTmpF']}','{$vars['$dateTmpT']}', '{$vars['z']}', '{$vars['uidF']}', '{$vars['uidT']}', 'Y'", 'odbc', ['src' => 'baan']);
            $caption = 'PPV Buyers';
            foreach ($rows as $key => $val) {
                if ($rows[$key]['amtb']==0) {
                    unset($rows[$key]);
                }
            }
            $report = new tldReportColumnar($rows,
                [
                    'xItems' => $xItems,
                    'title' => $caption,
                ]
            );
            $body .= $report->fetch();
        } else {
            $body = $form->toHTML();
        }
        break;


    case 'PPVStdDashBoard':

        // Get listing
        $erpList = tldLocation::getERPList('smartyOptions');

        // Get form
        $form = new HTML_QuickForm('frmPPVStdDashBoard', 'get', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'PPVStdDashBoard');
        $form->addElement('header', 'title', 'Select dates and company:');
        $form->addElement('date', 'x', 'From',
            ['format' => 'Y-m-d', 'minYear' => date('Y') - 5, 'maxYear' => date('Y') + 2]);
        $form->addElement('date', 'y', 'To',
            ['format' => 'Y-m-d', 'minYear' => date('Y') - 5, 'maxYear' => date('Y') + 2]);
        $form->addElement('select', 'z', 'Company#',
            ['' => ''] + $erpList);
        $form->addElement('text', 'v', 'Nb top');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('x', 'This is required', 'required');
        $form->addRule('y', 'This is required', 'required');
        $form->addRule('z', 'This is required', 'required');
        $form->addRule('v', 'This is required', 'required');
        $form->setDefaults(['x' => date('Y-01-01')]);
        $form->setDefaults(['y' => date('Y-m-d')]);
        $form->setDefaults(['v' => '20']);

        if (isset($_GET['erp'])) {
            $form->setDefaults(['z' => $_GET['erp']]);
        }

        if ($form->validate()) {
            $vars = tldUtils::cleanupFormInput($form->exportValues());


            $vars['from'] = implode('-', $vars['x']);
            $vars['to'] = implode('-', $vars['y']);

            $vars['$dateTmpF'] = $vars['x']['Y'] . '-' . substr('00' . $vars['x']['m'], -2) . '-' . substr('00' . $vars['x']['d'], -2);
            $vars['$dateTmpT'] = $vars['y']['Y'] . '-' . substr('00' . $vars['y']['m'], -2) . '-' . substr('00' . $vars['y']['d'], -2);

            if (strtotime($vars['to']) - strtotime($vars['from']) > 366 * 24 * 60 * 60) {
                $vars['to'] = date('Y-m-d', strtotime($vars['from']) + 366 * 24 * 60 * 60);
                $vars['$dateTmpT'] = $vars['to'];
            }

            $vars['uidF'] = '0';
            $vars['uidT'] = '999999';


            // 100% PPV
            $xItems = [
                'stdmatonly' => 'STD(MAT ONLY)',
                'poamount' => 'PO AMOUNT',
                'variance' => 'PPV STD',
                'FxImpact' => 'FX',
                'PPVstdnet' => 'PPV STD NET',
                'VarRate' => 'VAR%',
            ];

            $rows = tldUtils::getSqlToAssocArray("EXEC PPV_Std_All_Cumul '{$vars['$dateTmpF']}','{$vars['$dateTmpT']}', '{$vars['z']}', '{$vars['uidF']}', '{$vars['uidT']}', 'N'", 'odbc', ['src' => 'baan']);
            foreach ($rows as $key => $val) {
                $rows[$key]['stdeach'] = round($val['stdmatonly']/$val['oqua'],1);
                $rows[$key]['poeach'] = round($val['poamount']/$val['dqua'],1);
            }
            $caption = "Report from {$vars['from']} to {$vars['to']} for company {$vars['z']} in local currency<br><br>100% PPV";

            $report = new tldReportColumnar($rows,
                [
                    'xItems' => $xItems,
                    'title' => $caption,
                ]
            );
            $body .= $report->fetch();

            // TOP 20 BONI
            $xItems = [
                'item' => 'ITEM',
                'dsca' => 'LABEL',
                'suno' => 'SUPPLIER',
                'nama' => 'SUPPLIER NAME',
                'ccon' => 'BUYER',
                'namb' => 'BUYER NAME',
                'orno' => 'ORDER',
                'pono' => 'POSITION',
                'oqua' => 'ORDER LINE QTY',
                'dqua' => 'RECEIVED QTY',
                'stdmatonly' => 'STD(MAT ONLY)',
                'poamount' => 'PO AMOUNT',
                'variance' => 'PPV STD (VARIANCE)',
                'FxImpact' => 'FX',
                'PPVstdnet' => 'PPV STD NET',
                'VarRate' => 'VAR%',
                'stdeach'=>'STD(MAT Only) each',
                'poeach'=>'PO Amount each',
                't_prip'=>'Current Price',
                'minipric'=>'Mini Price Last 24 months',
            ];

            $rows = tldUtils::getSqlToAssocArray("EXEC PPV_Std_Top '{$vars['$dateTmpF']}','{$vars['$dateTmpT']}', '{$vars['z']}', '{$vars['uidF']}', '{$vars['uidT']}', 'D', '{$vars['v']}'", 'odbc', ['src' => 'baan']);
            $myerp = tldERP::getERPOb($vars['z']);
            if(empty($myerp)){
                echo "ERROR: No data for ERP#{$vars['z']}";
                exit;
            }
            foreach ($rows as $key => $val) {
                $rows[$key]['variance_abs'] = abs($val['variance']);
                $rows[$key]['stdeach'] = round($val['stdmatonly']/$val['oqua'],1);
                $rows[$key]['poeach'] = round($val['poamount']/$val['dqua'],1);
                $header = $myerp->getItemData($rows[$key]['item']);
                $rows[$key]['t_prip'] =$header['t_prip'];
                $miniprice = tldPOL::miniReceipts24Month($vars['z'],$rows[$key]['item']);
                $rows[$key]['minipric'] = $miniprice['minipric'];
            }
            $caption = 'Top ' . $vars['v'] . ' Boni';

            $report = new tldReportColumnar($rows,
                [
                    'xItems' => $xItems,
                    'title' => $caption,
                    'sumTotalsArray' => ['stdmatonly', 'poamount', 'variance', 'variance', 'FxImpact', 'PPVstdnet'],
                ]
            );
            $body .= $report->fetch();

            // TOP 20 MALI
            $xItems = [
                'item' => 'ITEM',
                'dsca' => 'LABEL',
                'suno' => 'SUPPLIER',
                'nama' => 'SUPPLIER NAME',
                'ccon' => 'BUYER',
                'namb' => 'BUYER NAME',
                'orno' => 'ORDER',
                'pono' => 'POSITION',
                'oqua' => 'ORDER LINE QTY',
                'dqua' => 'RECEIVED QTY',
                'stdmatonly' => 'STD(MAT ONLY)',
                'poamount' => 'PO AMOUNT',
                'variance' => 'PPV STD (VARIANCE)',
                'FxImpact' => 'FX',
                'PPVstdnet' => 'PPV STD NET',
                'VarRate' => 'VAR%',
                'stdeach'=>'STD(MAT Only) each',
                'poeach'=>'PO Amount each',
                't_prip'=>'Current Price',
                'minipric'=>'Mini Price Last 24 months',
            ];

            $rows = tldUtils::getSqlToAssocArray("EXEC PPV_Std_Top '{$vars['$dateTmpF']}','{$vars['$dateTmpT']}', '{$vars['z']}', '{$vars['uidF']}', '{$vars['uidT']}', 'A',  '{$vars['v']}'", 'odbc', ['src' => 'baan']);
            $myerp = tldERP::getERPOb($vars['z']);
            if(empty($myerp)){
                echo "ERROR: No data for ERP#{$vars['z']}";
                exit;
            }
            foreach ($rows as $key => $val) {
                $rows[$key]['stdeach'] = round($val['stdmatonly']/$val['oqua'],1);
                $rows[$key]['poeach'] = round($val['poamount']/$val['dqua'],1);
                $header = $myerp->getItemData($rows[$key]['item']);
                $rows[$key]['t_prip'] =$header['t_prip'];
                $miniprice = tldPOL::miniReceipts24Month($vars['z'],$rows[$key]['item']);
                $rows[$key]['minipric'] = $miniprice['minipric'];
            }
            $caption = 'Top ' . $vars['v'] . ' Mali';

            $report = new tldReportColumnar($rows,
                [
                    'xItems' => $xItems,
                    'title' => $caption,
                    'sumTotalsArray' => ['stdmatonly', 'poamount', 'variance', 'variance', 'FxImpact', 'PPVstdnet'],
                ]
            );
            $body .= $report->fetch();

            // Volatility
            $xItems = [
                'stdmatonly' => 'STD(MAT ONLY)',
                'poamount' => 'PO AMOUNT',
                'variance' => 'PPV STD',
                'FxImpact' => 'FX',
                'PPVstdnet' => 'PPV STD NET',
                'VarRate' => 'VAR% VOLATILITY',
            ];

            $rows = tldUtils::getSqlToAssocArray("EXEC PPV_Std_All_Cumul '{$vars['$dateTmpF']}','{$vars['$dateTmpT']}', '{$vars['z']}', '{$vars['uidF']}', '{$vars['uidT']}', 'Y'", 'odbc', ['src' => 'baan']);
            $caption = 'Volatility (All fields in absolute value)';

            $report = new tldReportColumnar($rows,
                [
                    'xItems' => $xItems,
                    'title' => $caption,
                ]
            );
            $body .= $report->fetch();


            // By buyer
            $xItems = [
                'ccon' => 'BUYER',
                'namb' => 'Buyer Name',
                'stdmatonly' => 'STD(MAT ONLY)',
                'poamount' => 'PO AMOUNT',
                'variance' => 'PPV STD',
                'FxImpact' => 'FX',
                'PPVstdnet' => 'PPV STD NET',
                'stdmatPO' => 'STD MAT PO',
                'VarRate' => 'VAR%',
            ];

            $rows = tldUtils::getSqlToAssocArray("EXEC PPV_Std_Buyer_Cumul '{$vars['$dateTmpF']}','{$vars['$dateTmpT']}', '{$vars['z']}', '{$vars['uidF']}', '{$vars['uidT']}', 'Y'", 'odbc', ['src' => 'baan']);
            $caption = 'PPV Buyers';

            $report = new tldReportColumnar($rows,
                [
                    'xItems' => $xItems,
                    'title' => $caption,
                ]
            );
            $body .= $report->fetch();

        } else {
            $body = $form->toHTML();
        }
        break;

//---------------------------------------
    case 'PPVStdDetail':
        $xItems = [
            'comp' => 'Company',
            'suno' => 'Supplier',
            'nama' => 'Sup. Name',
            'ccurc' => 'Main currency',
            'odat' => 'Order date',
            'orno' => 'PO number',
            'pono' => 'Position',
            'srnb' => 'Reception',
            'oqua' => 'Po line qty',
            'ccon' => 'Buyer',
            'namb' => 'Buyer name',
            'item' => 'Item',
            'dsca' => 'Description',
            'cuni' => 'stock unit (main unit)',
            'cuqp' => 'Purchase unit',
            'cupp' => 'Price unit',
            'conv' => 'Conversion factor',
            'date' => 'Receipt date',
            'dqua' => 'Receipt qty',
            'pric' => 'Order price',
            'ccuro' => 'Order currency',
            'dtcopr' => 'Std price date',
            'copr' => 'Std price by unit',
            'rtfxo' => 'Currency rate:order date',
            'rtfxr' => 'Currency rate:receipt date',
            'rtfxc' => 'Currency rate:Std price date',
            'stdmatonly' => 'Std price',
            'poamount' => 'PO amount',
            'variance' => 'PPV std (Variance)',
            'stdmatPO' => 'Std price at PO date rate calculation',
            'PPVstdnet' => 'PPV std net',
            'FxImpact' => 'FX impact',
            'variance_abs' => 'Absolute PPV std (Variance)',
            'stdeach'=>'STD(MAT Only) each',
            'poeach'=>'PO Amount each',
            't_prip'=>'Current Price',
        ];
        // Get listing
        $erpList = tldLocation::getERPList('smartyOptions');

        // Get form
        $form = new HTML_QuickForm('frmPPVStdDetail', 'get', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'PPVStdDetail');
        $form->addElement('header', 'title', 'Select dates and company:');
        $form->addElement('date', 'x', 'From', ['format' => 'Y-m-d', 'minYear' => date('Y') - 5, 'maxYear' => date('Y') + 2]);
        $form->addElement('date', 'y', 'To', ['format' => 'Y-m-d', 'minYear' => date('Y') - 5, 'maxYear' => date('Y') + 2]);
        $form->addElement('select', 'z', 'Company#', ['' => ''] + $erpList);
        $form->addElement('text', 'uidF', 'Buyer from');
        $form->addElement('text', 'uidT', 'Buyer to');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('x', 'This is required', 'required');
        $form->addRule('y', 'This is required', 'required');
        $form->addRule('z', 'This is required', 'required');
        $form->setDefaults(['x' => date('Y-01-01')]);
        $form->setDefaults(['y' => date('Y-m-d')]);
        $form->setDefaults(['uidF' => '']);
        $form->setDefaults(['uidT' => '999999']);

        if ($form->validate()) {
            $vars = tldUtils::cleanupFormInput($form->exportValues());
            $vars['from'] = implode('-', $vars['x']);
            $vars['to'] = implode('-', $vars['y']);
            $vars['$dateTmpF'] = $vars['x']['Y'] . '-' . substr('00' . $vars['x']['m'], -2) . '-' . substr('00' . $vars['x']['d'], -2);
            $vars['$dateTmpT'] = $vars['y']['Y'] . '-' . substr('00' . $vars['y']['m'], -2) . '-' . substr('00' . $vars['y']['d'], -2);

            if (strtotime($vars['to']) - strtotime($vars['from']) > 366 * 24 * 60 * 60) {
                $vars['to'] = date('Y-m-d', strtotime($vars['from']) + 366 * 24 * 60 * 60);
                $vars['$dateTmpT'] = $vars['to'];
            }

            $rows = tldUtils::getSqlToAssocArray("EXEC PPV_Standard_Detailled '{$vars['$dateTmpF']}','{$vars['$dateTmpT']}', '{$vars['z']}', '{$vars['uidF']}', '{$vars['uidT']}', 'Y'", 'odbc', ['src' => 'baan']);
            $caption = "PPV Standard detailled from {$vars['from']} to {$vars['to']} for company {$vars['z']}";

            if (isset($rows, $caption)) {
                $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=xls">XLS version</a>&nbsp;|&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=csv">CSV version</a>
EOF;
                $myerp = tldERP::getERPOb($vars['z']);
                if(empty($myerp)){
                    echo "ERROR: No data for ERP#$invs";
                    exit;
                }
                foreach ($rows as $key => $val) {
                    $rows[$key]['variance_abs'] = abs($val['variance']);
                    $rows[$key]['stdeach'] = round($val['stdmatPO']/$val['oqua'],1);
                    $rows[$key]['poeach'] = round($val['poamount']/$val['oqua'],1);
                    $header = $myerp->getItemData($rows[$key]['item']);
                    $rows[$key]['t_prip'] =$header['t_prip'];
                }

                switch ($m[2]) {
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
                    case 'csv':
                        $report = new tldCSV(
                            $rows,
                            [
                                'xItems' => $xItems,
                                'showTitles' => true,
                            ]
                        );
                        $report->out();
                        exit;
                        break;
                    default:

                        $report = new tldReportColumnar($rows,
                            [
                                'xItems' => $xItems,
                                'title' => $caption,
                            ]
                        );
                        $body .= $report->fetch();
                        break;
                }
            }
        } else {
            $body = $form->toHTML();
        }
        break;


//---------------------------------------
    case 'PPVInvDetail':
        $xItems = [
            'comp' => 'Company',
            'suno' => 'Supplier',
            'nama' => 'Sup. Name',
            'ccurc' => 'Main currency',
            'odat' => 'Order date',
            'orno' => 'PO number',
            'pono' => 'Position',
            'srnb' => 'Series for Receipt',
            'srni' => 'Series for Invoice',
            'oqua' => 'Po line qty',
            'ccon' => 'Buyer',
            'namb' => 'Buyer name',
            'item' => 'Item',
            'dsca' => 'Description',
            'cuni' => 'stock unit (main unit)',
            'cuqp' => 'Purchase unit',
            'cupp' => 'Price unit',
            'conv' => 'Conversion factor',
            'date' => 'Receipt date',
            'dqua' => 'Receipt qty',
            'pric' => 'Order price',
            'ccuro' => 'Order currency',
            'dtcopr' => 'Std price date',
            'copr' => 'Std price by unit',
            'rtfxo' => 'Currency rate:order date',
            'rtfxr' => 'Currency rate:receipt date',
            'rtfxc' => 'Currency rate:Std price date',
            'stdmatonly' => 'Std price',
            'poamount' => 'PO amount',
            'variance' => 'PPV std (Variance)',
            'stdmatPO' => 'Std price at PO date rate calculation',
            'PPVstdnet' => 'PPV std net',
            'FxImpact' => 'FX impact on PO',
            'invn' => 'Invoice number',
            'pdat' => 'Invoice date',
            'qana' => 'Invoiced qty',
            'amtb' => 'Invoiced amount',
            'rtfxi' => 'Currency rate:invoice date',
            'ccuri' => 'Invoice currency',
            'PPVinv' => 'PPV Invoice',
            'PPVinvnet' => 'PPV Inv net',
            'FxinvImpact' => 'FX impact on invoice',
        ];
        // Get listing
        $erpList = tldLocation::getERPList('smartyOptions');

        // Get form
        $form = new HTML_QuickForm('frmPPVInvDetail', 'get', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'PPVInvDetail');
        $form->addElement('header', 'title', 'Select dates and company:');
        $form->addElement('date', 'x', 'From', ['format' => 'Y-m-d', 'minYear' => date('Y') - 5, 'maxYear' => date('Y') + 2]);
        $form->addElement('date', 'y', 'To', ['format' => 'Y-m-d', 'minYear' => date('Y') - 5, 'maxYear' => date('Y') + 2]);
        $form->addElement('select', 'z', 'Company#', ['' => ''] + $erpList);
        $form->addElement('text', 'uidF', 'Buyer from');
        $form->addElement('text', 'uidT', 'Buyer to');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('x', 'This is required', 'required');
        $form->addRule('y', 'This is required', 'required');
        $form->addRule('z', 'This is required', 'required');
        $form->setDefaults(['x' => date('Y-01-01')]);
        $form->setDefaults(['y' => date('Y-m-d')]);
        $form->setDefaults(['uidF' => '']);
        $form->setDefaults(['uidT' => '999999']);

        if ($form->validate()) {
            $vars = tldUtils::cleanupFormInput($form->exportValues());
            $vars['from'] = implode('-', $vars['x']);
            $vars['to'] = implode('-', $vars['y']);
            $vars['$dateTmpF'] = $vars['x']['Y'] . '-' . substr('00' . $vars['x']['m'], -2) . '-' . substr('00' . $vars['x']['d'], -2);
            $vars['$dateTmpT'] = $vars['y']['Y'] . '-' . substr('00' . $vars['y']['m'], -2) . '-' . substr('00' . $vars['y']['d'], -2);

            if (strtotime($vars['to']) - strtotime($vars['from']) > 366 * 24 * 60 * 60) {
                $vars['to'] = date('Y-m-d', strtotime($vars['from']) + 366 * 24 * 60 * 60);
                $vars['$dateTmpT'] = $vars['to'];
            }

            $rows = tldUtils::getSqlToAssocArray("EXEC PPV_Invoice_Detailled '{$vars['$dateTmpF']}','{$vars['$dateTmpT']}', '{$vars['z']}', '{$vars['uidF']}', '{$vars['uidT']}'", 'odbc', ['src' => 'baan']);
            $caption = "PPV Invoice detailled from {$vars['from']} to {$vars['to']} for company {$vars['z']}";
            foreach ($rows AS $key=>$row){
                if ($rows[$key]['amtb']==0) {
                    unset($rows[$key]);
                }
                if ($rows[$key]['PPVinv']) {
                    $rows[$key]['PPVinv'] = round($rows[$key]['PPVinv'],2);
                }
                if ($rows[$key]['PPVinvnet']) {
                    $rows[$key]['PPVinvnet'] = round($rows[$key]['PPVinvnet'],2);
                }
            }
            if (isset($rows, $caption)) {
                $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=xls">XLS version</a>&nbsp;|&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=csv">CSV version</a>
EOF;
                switch ($m[2]) {
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
                    case 'csv':
                        $report = new tldCSV(
                            $rows,
                            [
                                'xItems' => $xItems,
                                'showTitles' => true,
                            ]
                        );
                        $report->out();
                        exit;
                        break;
                    default:
                        $report = new tldReportColumnar($rows,
                            [
                                'xItems' => $xItems,
                                'title' => $caption,
                            ]
                        );
                        $body .= $report->fetch();
                        break;
                }
            }
        } else {
            $body = $form->toHTML();
        }
        break;



//# PPV END #######################################


//# DATASET BEGIN #######################################
    case 'consumption':
        $xItems = [
            't_osta' => 'Order status',
            't_prdt' => 'Start Date',
            't_dldt' => 'Delivery Date',
            't_pdno' =>'Production order',
            't_mitm' => 'Item',
            't_cprj' => 'Project',
            't_dsca' =>'Description',
            't_pono' => 'Position',
            't_sitm' => 'Item',
            't_pics' => 'Floor stock',
            't_dsca' => 'Description',
            't_opno' => 'Operation',
            't_cwar' => 'Warehouse',
            't_stoc' => 'Inventory on hand',
            't_ques' => 'Estimated qty',
            't_qucs' => 'Actual qty',
            't_issu' => 'Issue',
            "qty" => "Delta sur estim&eacute;",
            "qty1" => "Qt&eacute; de stock apr&egrave;s conso",
        ];

        // Get listing
        $erpList = tldLocation::getERPList('smartyOptions');
        // Get form
        $form = new HTML_QuickForm('frmDTS_01', 'get', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'consumption');
        $form->addElement('header', 'title', 'Select company:');
        $form->addElement('select', 'z', 'Company#',
            ['' => ''] + $erpList);
        $form->addElement('text', 'start', 'Date from', ['class' => 'datepicker']);
        $form->addElement('text', 'end', 'Date to', ['class' => 'datepicker']);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('z', 'This is required', 'required');
        $form->addRule('start', 'This is required', 'required');
        $form->addRule('end', 'This is required', 'required');

        if ($form->validate()) {
            $vars = tldUtils::cleanupFormInput($form->exportValues());
            $erp = $vars['z'];
            $query = <<<EOF
SELECT ttisfc001520.t_osta, ttisfc001520.t_prdt, ttisfc001520.t_dldt, tticst001520.t_pdno,
       ttisfc001520.t_mitm, tticst001520.t_cprj, ttipcs021520.t_dsca, tticst001520.t_pono,
       tticst001520.t_sitm, ttiitm001520.t_pics, ttiitm001520.t_dsca, tticst001520.t_opno,
       tticst001520.t_cwar, ttiitm001520.t_stoc, tticst001520.t_ques, tticst001520.t_qucs,
       tticst001520.t_cpcs, tticst001520.t_issu, tticst001520.t_subd, ttiitm001520.t_suno,
       ttccom001520.t_namb,(tticst001520.t_ques-tticst001520.t_qucs) AS "qty" , (ttiitm001520.t_stoc-tticst001520.t_issu) AS "qty1"
FROM baandb.dbo.tticst001{$erp} tticst001520
INNER JOIN baandb.dbo.ttiitm001{$erp} AS ttiitm001520 ON  tticst001520.t_sitm=ttiitm001520.t_item
INNER JOIN baandb.dbo.ttipcs021{$erp} AS ttipcs021520 ON ttipcs021520.t_cprj=tticst001520.t_cprj
INNER JOIN baandb.dbo.ttisfc001{$erp} AS ttisfc001520 ON tticst001520.t_pdno=ttisfc001520.t_pdno AND ttisfc001520.t_mitm=ttipcs021520.t_item
INNER JOIN baandb.dbo.ttccom001{$erp} AS ttccom001520 ON ttiitm001520.t_buyr=ttccom001520.t_emno
WHERE ttiitm001520.t_pics <> 1 AND ttisfc001520.t_osta < 7 AND tticst001520.t_issu > 0 AND ttisfc001520.t_prdt >= '{$vars['start']}' AND ttisfc001520.t_prdt <= '{$vars['end']}'
EOF;

            $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
            $caption = "Shortage Screen for company {$vars['z']}";

            if (isset($rows, $caption)) {
                $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=xls">XLS version</a>&nbsp;|&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=csv">CSV version</a>
EOF;
                switch ($m[2]) {
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
                    case 'csv':
                        $report = new tldCSV(
                            $rows,
                            [
                                'xItems' => $xItems,
                                'showTitles' => true,
                            ]
                        );
                        $report->out();
                        exit;
                        break;
                    default:

                        $report = new tldReportColumnar($rows,
                            [
                                'xItems' => $xItems,
                                'title' => $caption,
                            ]
                        );
                        $body .= $report->fetch();

                        break;
                }
            }


        } else {
            $body = $form->toHTML();
        }
        break;
    case 'DTS_01': // BAAN C / ENCOURS
        $xItems = [
            'pdno' => 'Production order',
            'mitm' => 'Item',
            'cprj' => 'Project',
            'dsca' => 'Description',
            'pono' => 'Position',
            'sitm' => 'Item',
            'pics' => 'Floor stock',
            'dsca2' => 'Description',
            'opno' => 'Operation',
            'cwar' => 'Warehouse',
            'stoc' => 'Inventory on hand',
            'ques' => 'Estimated qty',
            'qucs' => 'Actual qty',
            'delta' => 'DELTA SUR ESTIME',
            'cpcs' => 'Actual cost price',
            'issu' => 'Issue',
            'subd' => 'Subsequent Delivery',
            'valo' => 'VALO LIGNE',
            'osta' => 'Order status',
        ];

        // Get listing
        $erpList = tldLocation::getERPList('smartyOptions');
        // Get form
        $form = new HTML_QuickForm('frmDTS_01', 'get', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'DTS_01');
        $form->addElement('header', 'title', 'Select company:');
        $form->addElement('select', 'z', 'Company#',
            ['' => ''] + $erpList);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('z', 'This is required', 'required');

        if ($form->validate()) {
            $vars = tldUtils::cleanupFormInput($form->exportValues());

            $query = <<<EOF
SELECT cst001.t_pdno pdno, sfc001.t_mitm mitm, cst001.t_cprj cprj, pcs021.t_dsca dsca, cst001.t_pono pono, cst001.t_sitm sitm, itm001.t_pics pics, itm001.t_dsca dsca2, cst001.t_opno opno, cst001.t_cwar cwar, itm001.t_stoc stoc, cst001.t_ques ques, cst001.t_qucs qucs, cst001.t_ques - cst001.t_qucs as "delta", cst001.t_cpcs cpcs, cst001.t_issu issu, cst001.t_subd subd, cst001.t_qucs  *  cst001.t_cpcs as "valo", sfc001.t_osta osta
FROM tticst001{$vars['z']} cst001, ttiitm001{$vars['z']} itm001, ttipcs021{$vars['z']} pcs021, ttisfc001{$vars['z']} sfc001
WHERE cst001.t_sitm=itm001.t_item and pcs021.t_cprj=cst001.t_cprj and cst001.t_pdno=sfc001.t_pdno and sfc001.t_mitm=pcs021.t_item
and itm001.t_pics <> 1 AND (cst001.t_qucs * cst001.t_cpcs) >= '900' AND sfc001.t_osta BETWEEN 4 AND 6
ORDER BY 1,6,18
EOF;

            $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
            $caption = "BAAN C / ENCOURS for company {$vars['z']}";

            if (isset($rows, $caption)) {
                $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=xls">XLS version</a>&nbsp;|&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=csv">CSV version</a>
EOF;
                switch ($m[2]) {
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
                    case 'csv':
                        $report = new tldCSV(
                            $rows,
                            [
                                'xItems' => $xItems,
                                'showTitles' => true,
                            ]
                        );
                        $report->out();
                        exit;
                        break;
                    default:

                        $report = new tldReportColumnar($rows,
                            [
                                'xItems' => $xItems,
                                'title' => $caption,
                            ]
                        );
                        $body .= $report->fetch();

                        break;
                }
            }


        } else {
            $body = $form->toHTML();
        }
        break;


    case 'DTS_02': // BAAN C / ENCOURS S-E
        $xItems = [
            'pdno' => 'Production order',
            'mitm' => 'Item',
            'cprj' => 'Project',
            'pono' => 'Position',
            'sitm' => 'Item',
            'dsca' => 'Description',
            'opno' => 'Operation',
            'cwar' => 'Warehouse',
            'stoc' => 'Inventory on hand',
            'ques' => 'Estimated qty',
            'qucs' => 'Actual qty',
            'cpcs' => 'Actual cost price',
            'issu' => 'Issue',
            'subd' => 'Subsequent Delivery',
            'valo' => 'Valorization (actual qty x actual cost price)',
            'osta' => 'Order status',
        ];

        // Get listing
        $erpList = tldLocation::getERPList('smartyOptions');
        // Get form
        $form = new HTML_QuickForm('frmDTS_02', 'get', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'DTS_02');
        $form->addElement('header', 'title', 'Select company:');
        $form->addElement('select', 'z', 'Company#',
            ['' => ''] + $erpList);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('z', 'This is required', 'required');

        if ($form->validate()) {
            $vars = tldUtils::cleanupFormInput($form->exportValues());

            $query = <<<EOF
SELECT cst001.t_pdno pdno, sfc001.t_mitm mitm, cst001.t_cprj cprj, cst001.t_pono pono, cst001.t_sitm sitm, itm001.t_dsca dsca, cst001.t_opno opno, cst001.t_cwar cwar, itm001.t_stoc stoc, cst001.t_ques ques, cst001.t_qucs qucs, cst001.t_cpcs cpcs, cst001.t_issu issu, cst001.t_subd subd, cst001.t_qucs  *  cst001.t_cpcs as valo, sfc001.t_osta osta
FROM tticst001{$vars['z']} cst001, ttiitm001{$vars['z']} itm001, ttisfc001{$vars['z']} sfc001
WHERE cst001.t_sitm=itm001.t_item and cst001.t_pdno=sfc001.t_pdno
and sfc001.t_osta BETWEEN 4 AND 6
ORDER BY 1,15
EOF;

            $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
            $caption = "Work in process for company {$vars['z']}";

            if (isset($rows, $caption)) {
                $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=xls">XLS version</a>&nbsp;|&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=csv">CSV version</a>
EOF;
                switch ($m[2]) {
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
                    case 'csv':
                        $report = new tldCSV(
                            $rows,
                            [
                                'xItems' => $xItems,
                                'showTitles' => true,
                            ]
                        );
                        $report->out();
                        exit;
                        break;
                    default:

                        $report = new tldReportColumnar($rows,
                            [
                                'xItems' => $xItems,
                                'title' => $caption,
                            ]
                        );
                        $body .= $report->fetch();

                        break;
                }
            }


        } else {
            $body = $form->toHTML();
        }
        break;

    case 'DTS_03': // BAAN C / RESTE A SERVIR POUR APPRO
        $xItems = [
            'cprj' => 'Project',
            'mitm' => 'Item',
            'pdno' => 'Production order',
            'pono' => 'Position',
            'sitm' => 'Item',
            'kitm' => 'Item type',
            'dsca' => 'Description',
            'ques' => 'Estimated qty',
            'stoc' => 'Inventory on Hand',
            'ordr' => 'Inventory on order',
            'allo' => 'Allocated inventory',
            'suno' => 'Supplier',
            'oltm' => 'Order lead time',
            'buyr' => 'Buyer',
            'cpcs' => 'Actual cost price',
        ];

        // Get listing
        $erpList = tldLocation::getERPList('smartyOptions');
        // Get form
        $form = new HTML_QuickForm('frmDTS_03', 'get', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'DTS_03');
        $form->addElement('header', 'title', 'Select company:');
        $form->addElement('select', 'z', 'Company#',
            ['' => ''] + $erpList);
        $form->addElement('text', 'v', 'Production order');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('z', 'This is required', 'required');
        $form->setDefaults(['v' => '0']);

        if ($form->validate()) {
            $vars = tldUtils::cleanupFormInput($form->exportValues());

            $query = <<<EOF
SELECT cst001.t_cprj cprj, sfc001.t_mitm mitm, cst001.t_pdno pdno, cst001.t_pono pono, cst001.t_sitm sitm, itm001.t_kitm kitm, itm001.t_dsca dsca, cst001.t_ques ques, itm001.t_stoc stoc, itm001.t_ordr ordr, itm001.t_allo allo, itm001.t_suno suno, itm001.t_oltm oltm, itm001.t_buyr buyr, cst001.t_cpcs cpcs
FROM tticst001{$vars['z']} cst001,ttiitm001{$vars['z']} itm001,ttisfc001{$vars['z']} sfc001
WHERE cst001.t_sitm=itm001.t_item AND cst001.t_pdno=sfc001.t_pdno
AND cst001.t_pdno = {$vars['v']}
ORDER BY 4
EOF;

            $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
            $caption = "RESTE A SERVIR POUR APPRO for company {$vars['z']}";

            if (isset($rows, $caption)) {
                $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=xls">XLS version</a>&nbsp;|&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=csv">CSV version</a>
EOF;
                switch ($m[2]) {
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
                    case 'csv':
                        $report = new tldCSV(
                            $rows,
                            [
                                'xItems' => $xItems,
                                'showTitles' => true,
                            ]
                        );
                        $report->out();
                        exit;
                        break;
                    default:

                        $report = new tldReportColumnar($rows,
                            [
                                'xItems' => $xItems,
                                'title' => $caption,
                            ]
                        );
                        $body .= $report->fetch();

                        break;
                }
            }


        } else {
            $body = $form->toHTML();
        }
        break;

    case 'DTS_04': // BAAN C / RESTE A SERVIR S-E
        $xItems = [
            'pdno' => 'Production order',
            'mitm' => 'Item',
            'cprj' => 'Project',
            'pono' => 'Position',
            'sitm' => 'Item',
            'dsca' => 'Description',
            'opno' => 'Operation',
            'cwar' => 'Warehouse',
            'stoc' => 'Inventory on Hand',
            'ques' => 'Estimated qty',
            'qucs' => 'Actual qty',
            'cpcs' => 'Actual cost price',
            'issu' => 'Issue',
            'subd' => 'Subsequent Delivery',
            'buyr' => 'Buyer',
        ];

        // Get listing
        $erpList = tldLocation::getERPList('smartyOptions');
        // Get form
        $form = new HTML_QuickForm('frmDTS_04', 'get', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'DTS_04');
        $form->addElement('header', 'title', 'Select company:');
        $form->addElement('select', 'z', 'Company#',
            ['' => ''] + $erpList);
        $form->addElement('text', 'v', 'Production order');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('z', 'This is required', 'required');
        $form->setDefaults(['v' => '0']);

        if ($form->validate()) {
            $vars = tldUtils::cleanupFormInput($form->exportValues());

            $query = <<<EOF
SELECT cst001.t_pdno pdno, sfc001.t_mitm mitm, cst001.t_cprj cprj, cst001.t_pono pono, cst001.t_sitm sitm, itm001.t_dsca dsca, cst001.t_opno opno, cst001.t_cwar cwar, itm001.t_stoc stoc, cst001.t_ques ques, cst001.t_qucs qucs, cst001.t_cpcs cpcs, cst001.t_issu issu, cst001.t_subd subd, itm001.t_buyr buyr
FROM tticst001{$vars['z']} cst001, ttiitm001{$vars['z']} itm001, ttisfc001{$vars['z']} sfc001
WHERE cst001.t_sitm = itm001.t_item AND cst001.t_pdno = sfc001.t_pdno
AND cst001.t_pdno = {$vars['v']}
ORDER BY 4
EOF;

            $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
            $caption = "BAAN C / RESTE A SERVIR S-E for company {$vars['z']}";

            if (isset($rows, $caption)) {
                $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=xls">XLS version</a>&nbsp;|&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=csv">CSV version</a>
EOF;
                switch ($m[2]) {
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
                    case 'csv':
                        $report = new tldCSV(
                            $rows,
                            [
                                'xItems' => $xItems,
                                'showTitles' => true,
                            ]
                        );
                        $report->out();
                        exit;
                        break;
                    default:

                        $report = new tldReportColumnar($rows,
                            [
                                'xItems' => $xItems,
                                'title' => $caption,
                            ]
                        );
                        $body .= $report->fetch();

                        break;
                }
            }


        } else {
            $body = $form->toHTML();
        }
        break;

    case 'DTS_05': // BAAN C / RESTE A SERVIR VEH
        // Get listing
        $erpList = tldLocation::getERPList('smartyOptions');
        // Get form
        $form = new HTML_QuickForm('frmDTS_05', 'get', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'DTS_05');
        $form->addElement('header', 'title', 'Select company:');
        $form->addElement('select', 'z', 'Company#', ['' => ''] + $erpList);
        $form->addElement('text', 'v', 'Production order');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('z', 'This is required', 'required');
        $form->setDefaults(['v' => '0']);

        if ($form->validate()) {
            $vars = tldUtils::cleanupFormInput($form->exportValues());
            $rows = tldEquipment::getConsumedPartsReport((int) $vars['z'], (int) $vars['v']);
            if (!$rows) {
                $DEFAULT_ERROR[] = 'ERROR: Report is empty';
                break;
            }
            $xItems = tldEquipment::getConsumedPartsReportItems();

            $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=xls">XLS version</a>&nbsp;|&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=csv">CSV version</a>
EOF;
            $filename = "{$rows[0]['t_mitm']}-{$vars['v']}-" . date('YmdHi');
            switch ($m[2]) {
                case 'xls':
                    $report = new tldXLS(
                        $rows,
                        [
                            'xItems' => $xItems,
                            'showTitles' => true,
                        ]
                    );
                    $report->out("$filename.xls");
                    exit;
                    break;
                case 'csv':
                    $report = new tldCSV(
                        $rows,
                        [
                            'xItems' => $xItems,
                            'showTitles' => true,
                        ]
                    );
                    $report->out("$filename.csv");
                    exit;
                    break;
                default:

                    $report = new tldReportColumnar($rows,
                        [
                            'xItems' => $xItems,
                            'title' => "BAAN C / RESTE A SERVIR VEH for company {$vars['z']}",
                            'stickyHeader' => true,
                        ]
                    );
                    $body .= $report->fetch();

                    break;
            }

        } else {
            $body = $form->toHTML();
        }
        break;
    case 'DTS_06': // BAAN CLOTURE 2 OF CLOTURES DS LE MOIS
        $xItems = [
            'pdno' => 'Production order',
            'cprj' => 'Project',
            'mitm' => 'Item',
            'dsca' => 'Description',
            'prdt' => 'Production start date',
            'dldt' => 'Delivery date',
            'qrdr' => 'Quantity ordered',
            'qdlv' => 'Quantity delivered',
            'RAR' => 'Reste � R�aliser',
            'cldt' => 'Closing date',
            'osta' => 'Order status',
        ];

        // Get listing
        $erpList = tldLocation::getERPList('smartyOptions');
        // Get form
        $form = new HTML_QuickForm('frmDTS_06', 'get', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'DTS_06');
        $form->addElement('header', 'title', 'Select company:');
        $form->addElement('select', 'z', 'Company#',
            ['' => ''] + $erpList);
        $form->addElement('date', 'date_from', 'Date from',
            [
                'language' => 'en',
                'format' => 'Ymd',
                'minYear' => date('Y') - 2,
                'maxYear' => date('Y'),
                'addEmptyOption' => true,
            ]
        );
        $form->addElement('date', 'date_to', 'Date to',
            [
                'language' => 'en',
                'minYear' => date('Y') - 2,
                'maxYear' => date('Y'),
                'format' => 'Ymd',
            ]);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->setDefaults(['date_from' => date('Y-01-01')]);
        $form->setDefaults(['date_to' => date('Y-m-d')]);
        $form->addRule('z', 'This is required', 'required');

        if ($form->validate()) {
            $vars = tldUtils::cleanupFormInput($form->exportValues());


            $dte_from = implode('-', $vars['date_from']);
            $dte_to = implode('-', $vars['date_to']);

            $query = <<<EOF
SELECT sfc001.t_pdno pdno, sfc001.t_cprj cprj, sfc001.t_mitm mitm, itm001.t_dsca dsca,
CASE substring(convert(varchar, sfc001.t_prdt, 120), 1, 10) WHEN '1753-01-01' THEN '' ELSE substring(convert(varchar, sfc001.t_prdt, 120), 1, 10) END prdt,
CASE substring(convert(varchar, sfc001.t_dldt, 120), 1, 10) WHEN '1753-01-01' THEN '' ELSE substring(convert(varchar, sfc001.t_dldt, 120), 1, 10) END dldt,
sfc001.t_qrdr qrdr, sfc001.t_qdlv qdlv, sfc001.t_qrdr - sfc001.t_qdlv as "RAR",
CASE substring(convert(varchar, sfc001.t_cldt, 120), 1, 10) WHEN '1753-01-01' THEN '' ELSE substring(convert(varchar, sfc001.t_cldt, 120), 1, 10) END cldt,
sfc001.t_osta osta
FROM ttisfc001{$vars['z']} sfc001, ttiitm001{$vars['z']} itm001
WHERE sfc001.t_mitm = itm001.t_item
AND (sfc001.t_qrdr -  sfc001.t_qdlv) = 0 AND sfc001.t_cldt >= '$dte_from' AND sfc001.t_cldt <= '$dte_to' AND sfc001.t_cprj <> ''
EOF;


            $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
            $caption = "BAAN CLOTURE 2 OF CLOTURES DS LE MOIS for company {$vars['z']}";

            if (isset($rows, $caption)) {
                $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=xls">XLS version</a>&nbsp;|&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=csv">CSV version</a>
EOF;
                switch ($m[2]) {
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
                    case 'csv':
                        $report = new tldCSV(
                            $rows,
                            [
                                'xItems' => $xItems,
                                'showTitles' => true,
                            ]
                        );
                        $report->out();
                        exit;
                        break;
                    default:

                        $report = new tldReportColumnar($rows,
                            [
                                'xItems' => $xItems,
                                'title' => $caption,
                            ]
                        );
                        $body .= $report->fetch();

                        break;
                }
            }


        } else {
            $body = $form->toHTML();
        }
        break;
    case 'DTS_07': // BAAN CLOTURE 31 LISTE OF VEH pour liste cloture - Tableau des marges
        $xItems = [
            'pdno' => 'Production order',
            'cprj' => 'Project',
            'mitm' => 'Item',
            'dsca' => 'Description',
            'prdt' => 'Production start date',
            'dldt' => 'Delivery date',
            'qrdr' => 'Quantity ordered',
            'qdlv' => 'Quantity delivered',
            'RAR' => 'Reste � Rliser',
            'osta' => 'Order status',
        ];

        // Get listing
        $erpList = tldLocation::getERPList('smartyOptions');
        // Get form
        $form = new HTML_QuickForm('frmDTS_07', 'get', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'DTS_07');
        $form->addElement('header', 'title', 'Select company:');
        $form->addElement('select', 'z', 'Company#',
            ['' => ''] + $erpList);
        $form->addElement('date', 'date_from', 'Date from',
            [
                'language' => 'en',
                'format' => 'Ymd',
                'minYear' => date('Y') - 2,
                'maxYear' => date('Y'),
                'addEmptyOption' => true,
            ]
        );
        $form->addElement('date', 'date_to', 'Date to',
            [
                'language' => 'en',
                'minYear' => date('Y') - 2,
                'maxYear' => date('Y'),
                'format' => 'Ymd',
            ]);

        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('z', 'This is required', 'required');
        $form->setDefaults(['date_from' => date('Y-01-01')]);
        $form->setDefaults(['date_to' => date('Y-m-d')]);

        if ($form->validate()) {
            $vars = tldUtils::cleanupFormInput($form->exportValues());


            $dte_from = implode('-', $vars['date_from']);
            $dte_to = implode('-', $vars['date_to']);

            $query = <<<EOF
SELECT sfc001.t_pdno pdno, sfc001.t_cprj cprj, sfc001.t_mitm mitm, itm001.t_dsca dsca,
CASE substring(convert(varchar, sfc001.t_prdt, 120), 1, 10) WHEN '1753-01-01' THEN '' ELSE substring(convert(varchar, sfc001.t_prdt, 120), 1, 10) END prdt,
CASE substring(convert(varchar, sfc001.t_dldt, 120), 1, 10) WHEN '1753-01-01' THEN '' ELSE substring(convert(varchar, sfc001.t_dldt, 120), 1, 10) END dldt,
sfc001.t_qrdr qrdr, sfc001.t_qdlv qdlv, sfc001.t_qrdr - sfc001.t_qdlv as "RAR", sfc001.t_osta osta
FROM ttisfc001{$vars['z']} sfc001, ttiitm001{$vars['z']} itm001
WHERE sfc001.t_mitm = itm001.t_item
AND sfc001.t_cprj <> '' AND sfc001.t_cldt >= '$dte_from' AND sfc001.t_cldt <= '$dte_to'
ORDER BY 3,2,1
EOF;


            $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
            $caption = "BAAN CLOTURE 31 LISTE OF VEH pour liste cloture - Tableau des marges for company {$vars['z']}";

            if (isset($rows, $caption)) {
                $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=xls">XLS version</a>&nbsp;|&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=csv">CSV version</a>
EOF;
                switch ($m[2]) {
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
                    case 'csv':
                        $report = new tldCSV(
                            $rows,
                            [
                                'xItems' => $xItems,
                                'showTitles' => true,
                            ]
                        );
                        $report->out();
                        exit;
                        break;
                    default:

                        $report = new tldReportColumnar($rows,
                            [
                                'xItems' => $xItems,
                                'title' => $caption,
                            ]
                        );
                        $body .= $report->fetch();

                        break;
                }
            }


        } else {
            $body = $form->toHTML();
        }
        break;
    case 'DTS_08': // BAAN CLOTURE 32 OF MIS EN STOCK DANS LE MOIS pour liste cloture - Tableau des marges
        $xItems = [
            'cprj' => 'Project',
            'dsca' => 'Description',
            'qana' => 'Quantity',
            'seak' => 'Search key',
            'clot' => 'Lot',
            'orno' => 'Order number',
            'qrdr' => 'Quantity ordered',
            'trdt' => 'Transaction date',
            'refe' => 'Reference',
            'osta' => 'Order status',
            'rlcd' => 'Relation',
            'bcac' => 'Budget Calculation Code',
            'cbdg' => 'Budget code',
            'loca' => 'Location',
            'psta' => 'Project stage',
            'kost' => 'Inventory transaction type',
            'dscb' => 'Description 2',
            'dscc' => 'Description 3',
        ];

        // Get listing
        $erpList = tldLocation::getERPList('smartyOptions');
        // Get form
        $form = new HTML_QuickForm('frmDTS_08', 'get', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'DTS_08');
        $form->addElement('header', 'title', 'Select company:');
        $form->addElement('select', 'z', 'Company#',
            ['' => ''] + $erpList);
        $form->addElement('date', 'date_from', 'Date from',
            [
                'language' => 'en',
                'format' => 'Ymd',
                'minYear' => date('Y') - 2,
                'maxYear' => date('Y'),
                'addEmptyOption' => true,
            ]
        );
        $form->addElement('date', 'date_to', 'Date to',
            [
                'language' => 'en',
                'minYear' => date('Y') - 2,
                'maxYear' => date('Y'),
                'format' => 'Ymd',
            ]);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('z', 'This is required', 'required');
        $form->setDefaults(['date_from' => date('Y-01-01')]);
        $form->setDefaults(['date_to' => date('Y-m-d')]);

        if ($form->validate()) {
            $vars = tldUtils::cleanupFormInput($form->exportValues());


            $dte_from = implode('-', $vars['date_from']);
            $dte_to = implode('-', $vars['date_to']);

            $query = <<<EOF
SELECT ilc301.t_cprj cprj, pcs020.t_dsca dsca, pcs025.t_qana qana, pcs020.t_seak seak, ilc301.t_clot clot, ilc301.t_orno orno, sfc001.t_qrdr qrdr,
CASE substring(convert(varchar, ilc301.t_trdt, 120), 1, 10) WHEN '1753-01-01' THEN '' ELSE substring(convert(varchar, ilc301.t_trdt, 120), 1, 10) END trdt,
pcs020.t_refe refe, sfc001.t_osta osta, ilc301.t_rlcd rlcd, pcs020.t_bcac bcac, pcs020.t_cbdg cbdg, ilc301.t_loca loca, pcs020.t_psta psta, ilc301.t_kost kost, pcs020.t_dscb dscb, pcs020.t_dscc dscc
FROM ttdilc301{$vars['z']} ilc301, ttipcs020{$vars['z']} pcs020, ttipcs025{$vars['z']} pcs025, ttisfc001{$vars['z']} sfc001
WHERE pcs020.t_cprj = ilc301.t_cprj AND pcs025.t_cprj = pcs020.t_cprj AND sfc001.t_pdno = ilc301.t_orno
AND ilc301.t_trdt >= '$dte_from' AND ilc301.t_trdt <= '$dte_to' AND ilc301.t_cprj <> '' AND ilc301.t_orno > 599999 AND ilc301.t_cwar = 'FGS'
ORDER BY 8,5
EOF;


            $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
            $caption = "BAAN CLOTURE 32 OF MIS EN STOCK DANS LE MOIS pour liste cloture - Tableau des marges for company {$vars['z']}";

            if (isset($rows, $caption)) {
                $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=xls">XLS version</a>&nbsp;|&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=csv">CSV version</a>
EOF;
                switch ($m[2]) {
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
                    case 'csv':
                        $report = new tldCSV(
                            $rows,
                            [
                                'xItems' => $xItems,
                                'showTitles' => true,
                            ]
                        );
                        $report->out();
                        exit;
                        break;
                    default:

                        $report = new tldReportColumnar($rows,
                            [
                                'xItems' => $xItems,
                                'title' => $caption,
                            ]
                        );
                        $body .= $report->fetch();

                        break;
                }
            }


        } else {
            $body = $form->toHTML();
        }
        break;
    case 'DTS_09': // BAAN CLOTURE 4 SUIVI OF POUR CONNAITRE LES VENTES PAR PERIODE pour liste tableau des marges
        $xItems = [
            'cprj' => 'Project',
            'item' => 'Item',
            'clot' => 'Lot',
            'cwar' => 'Warehouse',
            'trdt' => 'Transaction date',
            'orno' => 'Order number',
            'rlcd' => 'Relation',
            'qstr' => 'Qty (Storage unit)',
            'qstk' => 'Qty (Inventory unit)',
        ];

        // Get listing
        $erpList = tldLocation::getERPList('smartyOptions');
        // Get form
        $form = new HTML_QuickForm('frmDTS_09', 'get', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'DTS_09');
        $form->addElement('header', 'title', 'Select company:');
        $form->addElement('select', 'z', 'Company#',
            ['' => ''] + $erpList);
        $form->addElement('date', 'date_from', 'Date from',
            [
                'language' => 'en',
                'format' => 'Ymd',
                'minYear' => date('Y') - 2,
                'maxYear' => date('Y'),
                'addEmptyOption' => true,
            ]
        );
        $form->addElement('date', 'date_to', 'Date to',
            [
                'language' => 'en',
                'minYear' => date('Y') - 2,
                'maxYear' => date('Y'),
                'format' => 'Ymd',
            ]);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('z', 'This is required', 'required');
        $form->setDefaults(['date_from' => date('Y-01-01')]);
        $form->setDefaults(['date_to' => date('Y-m-d')]);

        if ($form->validate()) {
            $vars = tldUtils::cleanupFormInput($form->exportValues());


            $dte_from = implode('-', $vars['date_from']);
            $dte_to = implode('-', $vars['date_to']);

            $query = <<<EOF
SELECT ilc301.t_cprj cprj, ilc301.t_item item, ilc301.t_clot clot, ilc301.t_cwar cwar,
CASE substring(convert(varchar, ilc301.t_trdt, 120), 1, 10) WHEN '1753-01-01' THEN '' ELSE substring(convert(varchar, ilc301.t_trdt, 120), 1, 10) END trdt,
ilc301.t_orno orno, ilc301.t_rlcd rlcd, ilc301.t_qstr qstr, ilc301.t_qstk qstk
FROM ttdilc301{$vars['z']} ilc301
WHERE ilc301.t_cprj <> '' AND ilc301.t_trdt >= '$dte_from' AND ilc301.t_trdt <= '$dte_to'
ORDER BY 3,8,6
EOF;

            $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
            $caption = "BAAN CLOTURE 4 SUIVI OF POUR CONNAITRE LES VENTES PAR PERIODE pour liste tableau des marges for company {$vars['z']}";

            if (isset($rows, $caption)) {
                $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=xls">XLS version</a>&nbsp;|&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=csv">CSV version</a>
EOF;
                switch ($m[2]) {
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
                    case 'csv':
                        $report = new tldCSV(
                            $rows,
                            [
                                'xItems' => $xItems,
                                'showTitles' => true,
                            ]
                        );
                        $report->out();
                        exit;
                        break;
                    default:

                        $report = new tldReportColumnar($rows,
                            [
                                'xItems' => $xItems,
                                'title' => $caption,
                            ]
                        );
                        $body .= $report->fetch();

                        break;
                }
            }


        } else {
            $body = $form->toHTML();
        }
        break;
    case 'DTS_10': // BAAN TARIFS EXTRACTION VALO
        $xItems = [
            'pdno' => 'Production order',
            'pono' => 'Position',
            'pics' => 'Floor stock',
            'sitm' => 'Item',
            'dsca' => 'Description',
            'kitm' => 'Item type',
            'ques' => 'Estimated qty',
            'cuni' => 'Inventory unit',
            'matc' => 'Material costs',
            'oprc' => 'Operation costs',
            'copr' => 'Standard cost price',
            'cvat' => 'Tax code',
            'ccur' => 'Purchase currency',
            'prip' => 'Purchase price',
            'avpr' => 'Average purchase price',
            'ltpr' => 'Latest purchase price',
            'suno' => 'Supplier',
            'buyr' => 'Buyer',
            'cplb' => 'Planner',
            'DELTA' => 'Delta PRS et Prix d achat',
        ];

        // Get listing
        $erpList = tldLocation::getERPList('smartyOptions');
        // Get form
        $form = new HTML_QuickForm('frmDTS_10', 'get', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'DTS_10');
        $form->addElement('header', 'title', 'Select company:');
        $form->addElement('select', 'z', 'Company#',
            ['' => ''] + $erpList);
        $form->addElement('text', 'v', 'Production order');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('z', 'This is required', 'required');
        $form->setDefaults(['v' => '0']);

        if ($form->validate()) {
            $vars = tldUtils::cleanupFormInput($form->exportValues());

            $query = <<<EOF
SELECT cst001.t_pdno pdno, cst001.t_pono pono, itm001.t_pics pics, cst001.t_sitm sitm, itm001.t_dsca dsca, itm001.t_kitm kitm, cst001.t_ques ques, itm001.t_cuni cuni, itm001.t_matc matc, itm001.t_oprc oprc, itm001.t_copr copr, itm001.t_cvat cvat, itm001.t_ccur ccur, itm001.t_prip prip, itm001.t_avpr avpr, itm001.t_ltpr ltpr, itm001.t_suno suno, itm001.t_buyr buyr, itm001.t_cplb cplb, itm001.t_prip - itm001.t_copr as "Delta"
FROM tticst001{$vars['z']} cst001, ttiitm001{$vars['z']} itm001, ttipcs021{$vars['z']} pcs021, ttisfc001{$vars['z']} sfc001
WHERE cst001.t_sitm = itm001.t_item AND pcs021.t_cprj = cst001.t_cprj AND cst001.t_pdno = sfc001.t_pdno AND sfc001.t_mitm = pcs021.t_item
AND cst001.t_pdno = {$vars['v']}
EOF;

            $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
            $caption = "BAAN TARIFS EXTRACTION VALO for company {$vars['z']}";

            if (isset($rows, $caption)) {
                $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=xls">XLS version</a>&nbsp;|&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=csv">CSV version</a>
EOF;
                switch ($m[2]) {
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
                    case 'csv':
                        $report = new tldCSV(
                            $rows,
                            [
                                'xItems' => $xItems,
                                'showTitles' => true,
                            ]
                        );
                        $report->out();
                        exit;
                        break;
                    default:

                        $report = new tldReportColumnar($rows,
                            [
                                'xItems' => $xItems,
                                'title' => $caption,
                            ]
                        );
                        $body .= $report->fetch();

                        break;
                }
            }


        } else {
            $body = $form->toHTML();
        }
        break;

    case 'DTS_11': // BAAN TARIFS EXTRACTION VALO - Valo fin 2010
        $xItems = [
            'pdno' => 'Production order',
            'pono' => 'Position',
            'pics' => 'Floor stock',
            'sitm' => 'Item',
            'dsca' => 'Description',
            'kitm' => 'Item type',
            'ques' => 'Estimated qty',
            'cuni' => 'Inventory unit',
            'matc' => 'Material costs',
            'oprc' => 'Operation costs',
            'copr' => 'Standard cost price',
            'cvat' => 'Tax code',
            'ccur' => 'Purchase currency',
            'prip' => 'Purchase price',
            'avpr' => 'Average purchase price',
            'ltpr' => 'Latest purchase price',
            'suno' => 'Supplier',
            'buyr' => 'Buyer',
            'cplb' => 'Planner',
            'DELTA' => 'Delta PRS et Prix d achat',
            'valo' => 'VALO LIGNE',

        ];

        // Get listing
        $erpList = tldLocation::getERPList('smartyOptions');
        // Get form
        $form = new HTML_QuickForm('frmDTS_11', 'get', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'DTS_11');
        $form->addElement('header', 'title', 'Select company:');
        $form->addElement('select', 'z', 'Company#',
            ['' => ''] + $erpList);
        $form->addElement('text', 'v', 'Production order');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('z', 'This is required', 'required');
        $form->setDefaults(['v' => '0']);

        if ($form->validate()) {
            $vars = tldUtils::cleanupFormInput($form->exportValues());

            $query = <<<EOF
SELECT cst001.t_pdno pdno, cst001.t_pono pono, itm001.t_pics pics, cst001.t_sitm sitm, itm001.t_dsca dsca, itm001.t_kitm kitm, cst001.t_ques ques, itm001.t_cuni cuni, itm001.t_matc matc, itm001.t_oprc oprc, itm001.t_copr copr, itm001.t_cvat cvat, itm001.t_ccur ccur, itm001.t_prip prip, itm001.t_avpr avpr, itm001.t_ltpr ltpr, itm001.t_suno suno, itm001.t_buyr buyr, itm001.t_cplb cplb, itm001.t_prip - itm001.t_copr as "Delta" ,  cst001.t_ques * itm001.t_copr as "valo"
FROM tticst001{$vars['z']} cst001, ttiitm001{$vars['z']} itm001, ttipcs021{$vars['z']} pcs021, ttisfc001{$vars['z']} sfc001
WHERE cst001.t_sitm = itm001.t_item AND pcs021.t_cprj = cst001.t_cprj AND cst001.t_pdno = sfc001.t_pdno AND sfc001.t_mitm = pcs021.t_item
AND cst001.t_pdno = {$vars['v']}
EOF;

            $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
            $caption = "BAAN TARIFS EXTRACTION VALO - Valo fin 2010 for company {$vars['z']}";

            if (isset($rows, $caption)) {
                $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=xls">XLS version</a>&nbsp;|&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=csv">CSV version</a>
EOF;
                switch ($m[2]) {
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
                    case 'csv':
                        $report = new tldCSV(
                            $rows,
                            [
                                'xItems' => $xItems,
                                'showTitles' => true,
                            ]
                        );
                        $report->out();
                        exit;
                        break;
                    default:

                        $report = new tldReportColumnar($rows,
                            [
                                'xItems' => $xItems,
                                'title' => $caption,
                            ]
                        );
                        $body .= $report->fetch();

                        break;
                }
            }


        } else {
            $body = $form->toHTML();
        }
        break;

    case 'DTS_12': // BAAN VALO. STOCK (PN et SOMME Qt�)
        $xItems = [
            'cwar' => 'Warehouse',
            'loca' => 'Location',
            'item' => 'Item',
            'dsca' => 'Description',
            'stun' => 'Storage Unit',
            'stks' => 'Inventory (Inventory Unit)',
            'copr' => 'Standard cost price',
            'valo' => 'VALO LIGNE',
        ];

        // Get listing
        $erpList = tldLocation::getERPList('smartyOptions');
        // Get form
        $form = new HTML_QuickForm('frmDTS_12', 'get', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'DTS_12');
        $form->addElement('header', 'title', 'Select company:');
        $form->addElement('select', 'z', 'Company#',
            ['' => ''] + $erpList);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('z', 'This is required', 'required');

        if ($form->validate()) {
            $vars = tldUtils::cleanupFormInput($form->exportValues());

            $query = <<<EOF
SELECT ilc101.t_cwar cwar, ilc101.t_loca loca, ilc101.t_item item, itm001.t_dsca dsca, ilc101.t_stun stun, ilc101.t_stks stks, itm001.t_copr copr, ilc101.t_stks * itm001.t_copr as "valo"
FROM ttdilc101{$vars['z']} ilc101, ttiitm001{$vars['z']} itm001
WHERE itm001.t_item = ilc101.t_item
EOF;

            $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
            $caption = "BAAN VALO. STOCK (PN et SOMME Qt�) for company {$vars['z']}";

            if (isset($rows, $caption)) {
                $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=xls">XLS version</a>&nbsp;|&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=csv">CSV version</a>
EOF;
                switch ($m[2]) {
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
                    case 'csv':
                        $report = new tldCSV(
                            $rows,
                            [
                                'xItems' => $xItems,
                                'showTitles' => true,
                            ]
                        );
                        $report->out();
                        exit;
                        break;
                    default:

                        $report = new tldReportColumnar($rows,
                            [
                                'xItems' => $xItems,
                                'title' => $caption,
                            ]
                        );
                        $body .= $report->fetch();

                        break;
                }
            }


        } else {
            $body = $form->toHTML();
        }
        break;

    case 'DTS_13': // BAAN CONTROLE OF ST1 - FGS
        $xItems = [
            'pdno' => 'Production order',
            'cprj' => 'Project',
            'mitm' => 'Item',
            'cwar' => 'Warehouse',
        ];

        // Get listing
        $erpList = tldLocation::getERPList('smartyOptions');
        // Get form
        $form = new HTML_QuickForm('frmDTS_13', 'get', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'DTS_13');
        $form->addElement('header', 'title', 'Select company:');
        $form->addElement('select', 'z', 'Company#',
            ['' => ''] + $erpList);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('z', 'This is required', 'required');

        if ($form->validate()) {
            $vars = tldUtils::cleanupFormInput($form->exportValues());

            $query = <<<EOF
SELECT sfc001.t_pdno pdno, sfc001.t_cprj cprj, sfc001.t_mitm mitm, sfc001.t_cwar cwar
FROM ttisfc001{$vars['z']} sfc001
WHERE sfc001.t_cwar in('FGS', 'ST1')
EOF;

            $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
            $caption = "BAAN CONTROLE OF ST1 - FGS for company {$vars['z']}";

            if (isset($rows, $caption)) {
                $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=xls">XLS version</a>&nbsp;|&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=csv">CSV version</a>
EOF;
                switch ($m[2]) {
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
                    case 'csv':
                        $report = new tldCSV(
                            $rows,
                            [
                                'xItems' => $xItems,
                                'showTitles' => true,
                            ]
                        );
                        $report->out();
                        exit;
                        break;
                    default:

                        $report = new tldReportColumnar($rows,
                            [
                                'xItems' => $xItems,
                                'title' => $caption,
                            ]
                        );
                        $body .= $report->fetch();

                        break;
                }
            }


        } else {
            $body = $form->toHTML();
        }
        break;

    case 'DTS_14': // BAAN LOCALISATION
        $xItems = [
            'loca' => 'Location',
            'strt' => 'Row',
            'coln' => 'Lev.%',
            'rack' => 'Bin',
            'cwar' => 'Warehouse',
            'seak' => 'Searck key',
            'item' => 'Item',
            'dsca' => 'Description',
            'stks' => 'Inventory (Inventory Unit)',
            'stun' => 'Storage Unit',
        ];

        // Get listing
        $erpList = tldLocation::getERPList('smartyOptions');
        // Get form
        $form = new HTML_QuickForm('frmDTS_14', 'get', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'DTS_14');
        $form->addElement('header', 'title', 'Select company:');
        $form->addElement('select', 'z', 'Company#',
            ['' => ''] + $erpList);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('z', 'This is required', 'required');

        if ($form->validate()) {
            $vars = tldUtils::cleanupFormInput($form->exportValues());

            $query = <<<EOF
SELECT ilc101.t_loca loca, ilc001.t_strt strt, ilc001.t_coln coln, ilc001.t_rack rack, ilc101.t_cwar cwar, ilc001.t_seak seak, ilc101.t_item item, itm001.t_dsca dsca, ilc101.t_stks stks, ilc101.t_stun stun
FROM ttdilc101{$vars['z']} ilc101, ttdilc001{$vars['z']} ilc001, ttiitm001{$vars['z']} itm001
WHERE ilc101.t_loca = ilc001.t_loca AND ilc101.t_cwar = ilc001.t_cwar AND itm001.t_item = ilc101.t_item AND ilc001.t_seak = 'OBSOLETE'
EOF;

            $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
            $caption = "BAAN LOCALISATION for company {$vars['z']}";

            if (isset($rows, $caption)) {
                $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=xls">XLS version</a>&nbsp;|&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=csv">CSV version</a>
EOF;
                switch ($m[2]) {
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
                    case 'csv':
                        $report = new tldCSV(
                            $rows,
                            [
                                'xItems' => $xItems,
                                'showTitles' => true,
                            ]
                        );
                        $report->out();
                        exit;
                        break;
                    default:

                        $report = new tldReportColumnar($rows,
                            [
                                'xItems' => $xItems,
                                'title' => $caption,
                            ]
                        );
                        $body .= $report->fetch();

                        break;
                }
            }


        } else {
            $body = $form->toHTML();
        }
        break;

    case 'DTS_15': // BAAN OBSOLETES
        $xItems = [
            'dsca' => 'Description',
            'dscc' => 'Size',
            'dscd' => 'Standard',
            'seak' => 'Searck key',
            'cwar' => 'Warehouse',
            'loca' => 'Location',
            'strt' => 'Row',
            'coln' => 'Lev.%',
            'rack' => 'Bin',
            'ltdt' => 'Last inventory transaction date',
            'stoc' => 'Inventory on Hand',
            'blck' => 'Inventory on hold',
            'ordr' => 'Inventory on order',
            'allo' => 'Allocated inventory',
            'copr' => 'Standard cost price',
            'valo' => 'VALO LIGNE',
        ];

        // Get listing
        $erpList = tldLocation::getERPList('smartyOptions');
        // Get form
        $form = new HTML_QuickForm('frmDTS_15', 'get', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'DTS_15');
        $form->addElement('header', 'title', 'Select company:');
        $form->addElement('select', 'z', 'Company#',
            ['' => ''] + $erpList);
        $form->addElement('date', 'date_from', 'Date from',
            [
                'language' => 'en',
                'format' => 'Ymd',
                'minYear' => date('Y') - 2,
                'maxYear' => date('Y'),
                'addEmptyOption' => true,
            ]
        );
        $form->addElement('date', 'date_to', 'Date to',
            [
                'language' => 'en',
                'minYear' => date('Y') - 2,
                'maxYear' => date('Y'),
                'format' => 'Ymd',
            ]);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('z', 'This is required', 'required');
        $form->setDefaults(['date_from' => date('Y-01-01')]);
        $form->setDefaults(['date_to' => date('Y-m-d')]);

        if ($form->validate()) {
            $vars = tldUtils::cleanupFormInput($form->exportValues());

            $dte_from = implode('-', $vars['date_from']);
            $dte_to = implode('-', $vars['date_to']);


            $query = <<<EOF
SELECT itm001.t_item item, itm001.t_dsca dsca, itm001.t_dscc dscc, itm001.t_dscd dscd, ilc001.t_seak seak, itm001.t_cwar cwar, ilc101.t_loca loca, ilc001.t_strt strt, ilc001.t_coln coln, ilc001.t_rack rack,
CASE substring(convert(varchar, itm001.t_ltdt, 120), 1, 10) WHEN '1753-01-01' THEN '' ELSE substring(convert(varchar, itm001.t_ltdt, 120), 1, 10) END ltdt,
itm001.t_stoc stoc, itm001.t_blck blck, itm001.t_ordr ordr, itm001.t_allo allo, itm001.t_copr copr, itm001.t_copr *  itm001.t_stoc as valo
FROM ttiitm001{$vars['z']} itm001, ttdinv001{$vars['z']} inv001, ttdilc101{$vars['z']} ilc101, ttdilc001{$vars['z']} ilc001
WHERE inv001.t_item = itm001.t_item AND ilc101.t_item = inv001.t_item AND ilc101.t_loca = ilc001.t_loca AND itm001.t_ltdt >= '$dte_from' AND itm001.t_ltdt <= '$dte_to' AND inv001.t_stoc <> 0 AND  itm001.t_allo = 0
ORDER BY 7,1
EOF;

            $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
            $caption = "BAAN OBSOLETES for company {$vars['z']}";

            if (isset($rows, $caption)) {
                $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=xls">XLS version</a>&nbsp;|&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=csv">CSV version</a>
EOF;
                switch ($m[2]) {
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
                    case 'csv':
                        $report = new tldCSV(
                            $rows,
                            [
                                'xItems' => $xItems,
                                'showTitles' => true,
                            ]
                        );
                        $report->out();
                        exit;
                        break;
                    default:

                        $report = new tldReportColumnar($rows,
                            [
                                'xItems' => $xItems,
                                'title' => $caption,
                            ]
                        );
                        $body .= $report->fetch();

                        break;
                }
            }


        } else {
            $body = $form->toHTML();
        }
        break;
//# DATASET END   #######################################

    case 'poa_mrp':
        ini_set('max_execution_time', '240');
        $query = <<<EOF
select
itm.t_suno, mrp.t_item, itm.t_dsca, mrp.t_oqan, mrp.t_podt, mrp.t_pddt,
itm.t_sfst, itm500.t_stoc AS stoc500, itm.t_stoc as stoc520, itm540.t_stoc as stoc540,
itm.t_oltm, itm.t_prip, itm.t_dscc,
itm.t_buyr, byr.t_namb as byr_namb,mrp.t_cplb, pln.t_namb as pln_namb,
mrp.t_osta, mrp.t_trdt, mrp.t_orno, itm.t_csgp
from ttiitm001520 as itm
 join ttimrp021520 as mrp on itm.t_item=mrp.t_item
 left join ttccom001520 as byr on itm.t_buyr=byr.t_emno
 left join ttccom001520 as pln on mrp.t_cplb=pln.t_emno
 left join ttiitm001500 as itm500 on itm.t_item=itm500.t_item
 left join ttiitm001540 as itm540 on itm.t_item=itm540.t_item
EOF;
        $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
        foreach ($rows as $key => $row) {
            foreach ($row as $k => $r) {
                if (!is_numeric($k)) {
                    unset($rows[$key][$k]);
                }
            }
        }
        $report = new tldCSV(
            $rows
        );
        $report->out();
        exit;
        break;
    case 'poa_sic':
        $query = <<<EOF
select
inv.t_suno, inv.t_item, itm.t_dsca, inv.t_oqan, inv.t_ordt, inv.t_dldt,
itm.t_sfst, itm500.t_stoc AS stoc500, itm.t_stoc as stoc520, itm540.t_stoc as stoc540,
itm.t_oltm, itm.t_prip, itm.t_dscc,
inv.t_buyr, byr.t_namb as byr_namb, inv.t_cplb, pln.t_namb as pln_namb,
inv.t_appr, 'DTE Trans', inv.t_orno, itm.t_csgp
from ttdinv310520 as inv
 left join ttiitm001520 as itm on inv.t_item=itm.t_item
 left join ttccom001520 as byr on inv.t_buyr=byr.t_emno
 left join ttccom001520 as pln on inv.t_cplb=pln.t_emno
 left join ttiitm001500 as itm500 on itm.t_item=itm500.t_item
 left join ttiitm001540 as itm540 on itm.t_item=itm540.t_item
EOF;
        $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
        foreach ($rows as $key => $row) {
            foreach ($row as $k => $r) {
                if (!is_numeric($k)) {
                    unset($rows[$key][$k]);
                }
            }
        }
        $report = new tldXLS(
            $rows
        );
        $report->out();
        exit;
        break;
    case 'pof_mrp':
        ini_set('max_execution_time', '120');
        $query2 = <<<EOF
select
itm.t_suno, mrp.t_item, itm.t_dsca, mrp.t_oqan, mrp.t_psdt, mrp.t_pfdt,
itm.t_sfst, itm500.t_stoc AS stoc500, itm.t_stoc as stoc520, itm540.t_stoc as stoc540,
itm.t_oltm, itm.t_prip, itm.t_dscc,
itm.t_buyr, byr.t_namb as byr_namb, mrp.t_cplb, pln.t_namb as pln_namb,
mrp.t_osta, mrp.t_trdt, mrp.t_orno, itm.t_csgp
from ttiitm001520 as itm
 join ttimrp020520 as mrp on itm.t_item=mrp.t_item
 left join ttccom001520 as byr on itm.t_buyr=byr.t_emno
 left join ttccom001520 as pln on mrp.t_cplb=pln.t_emno
 left join ttiitm001500 as itm500 on itm.t_item=itm500.t_item
 left join ttiitm001540 as itm540 on itm.t_item=itm540.t_item
EOF;
        $rows = tldUtils::getSqlToAssocArray($query2, 'odbc', ['src' => 'baan']);
        foreach ($rows as $key => $row) {
            foreach ($row as $k => $r) {
                if (!is_numeric($k)) {
                    unset($rows[$key][$k]);
                }
            }
        }
        $report = new tldXLS(
            $rows
        );
        $report->out();
        exit;
        break;
    case 'poa_merged':
        $form = new HTML_QuickForm('formPOA_MERGED', 'post');
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'poa_merged');
        $form->addElement('header', 'title', 'POA Merged Report');

        $form->addElement('select', 'erp', 'Comp', ['500' => '500','510' => '510', '520' => '520', '540' => '540']);
        $form->addElement('date', 'planned_order_date_start', 'Planned Order Date Start',
            [
                'language' => 'en',
                'format' => 'Ymd',
                'minYear' => '1990',
                'maxYear' => date('Y') + 1,
                'addEmptyOption' => true,
            ]
        );
        $form->addElement('date', 'planned_order_date_end', 'Planned Order Date End',
            [
                'language' => 'en',
                'minYear' => date('Y') - 1,
                'maxYear' => date('Y') + 9,
                'format' => 'Ymd',
            ]);
        $form->addElement('date', 'planned_delivery_date_start', 'Planned Delivery Date Start',
            [
                'language' => 'en',
                'format' => 'Ymd',
                'minYear' => '1990',
                'maxYear' => date('Y') + 1,
                'addEmptyOption' => true,
            ]
        );
        $form->addElement('date', 'planned_delivery_date_end', 'Planned Delivery Date End',
            [
                'language' => 'en',
                'minYear' => date('Y') - 1,
                'maxYear' => date('Y') + 9,
                'format' => 'Ymd',
            ]);
        $form->addElement('text', 'vendor_id_start', 'Vendor ID Start', ['maxlength' => 6]);
        $form->addElement('text', 'vendor_id_end', 'Vendor ID End', ['maxlength' => 6]);
        $form->addElement('text', 'buyer_id_start', 'Buyer ID Start', ['maxlength' => 6]);
        $form->addElement('text', 'buyer_id_end', 'Buyer ID End', ['maxlength' => 6]);
        $form->addElement('text', 'planner_id_start', 'Planner ID Start', ['maxlength' => 6]);
        $form->addElement('text', 'planner_id_end', 'Planner ID End', ['maxlength' => 6]);
        $form->addElement('submit', 'btn', 'Generate report...');
        $form->setDefaults(
            [
                'erp' => 520,
                'planned_order_date_start' => ['Y' => '1990', 'm' => '01', 'd' => '01'],
                'planned_order_date_end' => ['Y' => date('Y'), 'm' => date('m'), 'd' => date('d')],
                'planned_delivery_date_start' => ['Y' => '1990', 'm' => '01', 'd' => '01'],
                'planned_delivery_date_end' => ['Y' => date('Y'), 'm' => date('m'), 'd' => date('d')],
                'vendor_id_start' => '000000',
                'vendor_id_end' => 'ZZZZZZ',
                'buyer_id_start' => '257000',
                'buyer_id_end' => '257099',
                'planner_id_start' => '000000',
                'planner_id_end' => '999999',
            ]
        );

        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }

        $vars = $form->exportValues();
        //do santiy checks
        if (!in_array($vars['erp'], [500, 510, 520, 540])) {
            $DEFAULT_ERROR[] = 'ERROR: Invalid company number...' . $vars['erp'];
            break;
        }

        if ($vars['buyer_id_start'] < 0 || $vars['buyer_id_start'] > 999999) {
            $DEFAULT_ERROR[] = 'ERROR: Invalid buyer id start...' . $vars['buyer_id_start'];
            break;
        }
        if ($vars['buyer_id_end'] < 0 || $vars['buyer_id_end'] > 999999) {
            $DEFAULT_ERROR[] = 'ERROR: Invalid buyer id end...' . $vars['buyer_id_end'];
            break;
        }
        if ($vars['planner_id_start'] < 0 || $vars['planner_id_start'] > 999999) {
            $DEFAULT_ERROR[] = 'ERROR: Invalid planner id start...' . $vars['planner_id_start'];
            break;
        }
        if ($vars['planner_id_end'] < 0 || $vars['planner_id_end'] > 999999) {
            $DEFAULT_ERROR[] = 'ERROR: Invalid planner id end...' . $vars['planner_id_end'];
            break;
        }
        $podt_start = implode('-', $vars['planned_order_date_start']);
        $podt_end = implode('-', $vars['planned_order_date_end']);
        $pddt_start = implode('-', $vars['planned_delivery_date_start']);
        $pddt_end = implode('-', $vars['planned_delivery_date_end']);
        $erp = $vars['erp'];
        ini_set('max_execution_time', '360');

        $query = <<<EOF
(
select
    itm.t_suno, mrp.t_item, itm.t_dsca, mrp.t_oqan,
    SUBSTRING(convert(varchar, mrp.t_podt, 120), 0 , 11) AS date1,
    SUBSTRING(convert(varchar, mrp.t_pddt, 120), 0 , 11) AS date2,
    itm.t_sfst, itm500.t_stoc AS stoc500, itm510.t_stoc AS stoc510, itm520.t_stoc as stoc520, itm540.t_stoc as stoc540,
    itm.t_oltm, itm.t_prip, itm.t_dscc,
    itm.t_buyr, byr.t_namb as byr_namb,mrp.t_cplb, pln.t_namb as pln_namb,
    CASE mrp.t_osta
        WHEN 1 THEN 'Planifi'
        WHEN 2 THEN 'Planifi ferme'
        WHEN 3 THEN 'Confirm'
        WHEN 4 THEN 'Transfr'
        WHEN 5 THEN 'Annul'
    END,
    SUBSTRING(convert(varchar, mrp.t_trdt, 120), 0 , 11) AS date3,
    mrp.t_orno, itm.t_csgp
from ttiitm001$erp as itm
    join ttimrp021$erp as mrp on itm.t_item=mrp.t_item
    left join ttccom001$erp as byr on itm.t_buyr=byr.t_emno
    left join ttccom001$erp as pln on mrp.t_cplb=pln.t_emno
    left join ttiitm001500 as itm500 on itm.t_item=itm500.t_item
    left join ttiitm001510 as itm510 on itm.t_item=itm510.t_item
    left join ttiitm001520 as itm520 on itm.t_item=itm520.t_item
    left join ttiitm001540 as itm540 on itm.t_item=itm540.t_item
WHERE
    mrp.t_podt BETWEEN '$podt_start' AND '$podt_end'
    AND mrp.t_pddt BETWEEN '$pddt_start' AND '$pddt_end'
    AND itm.t_suno BETWEEN '${vars['vendor_id_start']}' AND '${vars['vendor_id_end']}'
    AND itm.t_buyr BETWEEN '${vars['buyer_id_start']}' AND '${vars['buyer_id_end']}'
    AND itm.t_cplb BETWEEN '${vars['planner_id_start']}' AND '${vars['planner_id_end']}'
)UNION(
select
    itm.t_suno, inv.t_item, itm.t_dsca, inv.t_oqan,
    SUBSTRING(convert(varchar, inv.t_ordt, 120), 0 , 11) AS date1,
    SUBSTRING(convert(varchar, inv.t_dldt, 120), 0 , 11) AS date2,
    itm.t_sfst, itm500.t_stoc AS stoc500, itm510.t_stoc as stoc510, itm520.t_stoc as stoc520, itm540.t_stoc as stoc540,
    itm.t_oltm, itm.t_prip, itm.t_dscc,
    inv.t_buyr, byr.t_namb as byr_namb, inv.t_cplb, pln.t_namb as pln_namb,
    case inv.t_appr
        when 1 then 'OUI'
        when 2 then 'NON'
    end,
    '' AS date3, inv.t_orno, itm.t_csgp
from ttdinv310$erp as inv
    left join ttiitm001$erp as itm on inv.t_item=itm.t_item
    left join ttccom001$erp as byr on inv.t_buyr=byr.t_emno
    left join ttccom001$erp as pln on inv.t_cplb=pln.t_emno
    left join ttiitm001500 as itm500 on itm.t_item=itm500.t_item
    left join ttiitm001510 as itm510 on itm.t_item=itm510.t_item
    left join ttiitm001520 as itm520 on itm.t_item=itm520.t_item
    left join ttiitm001540 as itm540 on itm.t_item=itm540.t_item
WHERE
    inv.t_ordt BETWEEN '$podt_start' AND '$podt_end'
    AND inv.t_dldt BETWEEN '$pddt_start' AND '$pddt_end'
    AND inv.t_suno BETWEEN '${vars['vendor_id_start']}' AND '${vars['vendor_id_end']}'
    AND inv.t_buyr BETWEEN '${vars['buyer_id_start']}' AND '${vars['buyer_id_end']}'
    AND inv.t_cplb BETWEEN '${vars['planner_id_start']}' AND '${vars['planner_id_end']}'
)UNION(
select
    itm.t_suno, mrp.t_item, itm.t_dsca, mrp.t_oqan,
    SUBSTRING(convert(varchar, mrp.t_psdt, 120), 0 , 11) AS date1,
    SUBSTRING(convert(varchar, mrp.t_pfdt, 120), 0 , 11) AS date2,
    itm.t_sfst, itm500.t_stoc AS stoc500, itm510.t_stoc as stoc510, itm520.t_stoc as stoc520, itm540.t_stoc as stoc540,
    itm.t_oltm, itm.t_prip, itm.t_dscc,
    itm.t_buyr, byr.t_namb as byr_namb, mrp.t_cplb, pln.t_namb as pln_namb,
    CASE mrp.t_osta
        WHEN 1 THEN 'Planifi'
        WHEN 2 THEN 'Planifi ferme'
        WHEN 3 THEN 'Confirm'
        WHEN 4 THEN 'Transfr'
        WHEN 5 THEN 'Annul'
    END,
    SUBSTRING(convert(varchar, mrp.t_trdt, 120), 0 , 11) AS date3,
    mrp.t_orno, itm.t_csgp
from ttiitm001$erp as itm
    join ttimrp020$erp as mrp on itm.t_item=mrp.t_item
    left join ttccom001$erp as byr on itm.t_buyr=byr.t_emno
    left join ttccom001$erp as pln on mrp.t_cplb=pln.t_emno
    left join ttiitm001500 as itm500 on itm.t_item=itm500.t_item
    left join ttiitm001510 as itm510 on itm.t_item=itm510.t_item
    left join ttiitm001520 as itm520 on itm.t_item=itm520.t_item
    left join ttiitm001540 as itm540 on itm.t_item=itm540.t_item
WHERE
    mrp.t_psdt BETWEEN '$podt_start' AND '$podt_end'
    AND mrp.t_pfdt BETWEEN '$pddt_start' AND '$pddt_end'
    AND itm.t_suno BETWEEN '${vars['vendor_id_start']}' AND '${vars['vendor_id_end']}'
    AND itm.t_buyr BETWEEN '${vars['buyer_id_start']}' AND '${vars['buyer_id_end']}'
    AND mrp.t_cplb BETWEEN '${vars['planner_id_start']}' AND '${vars['planner_id_end']}'
 )
EOF;

        $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
//        foreach ($rows as $key => $row) {
//            foreach ($row as $k => $r) {
//                if (!is_numeric($k)) {
//                    unset($rows[$key][$k]);
//                }
//            }
//        }
        $report = new tldXLS(
            $rows
        );
        $report->out();
        exit;
        break;

    case 'poa_merged_520':
        $form = new HTML_QuickForm('formPOA_MERGED', 'post');
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'poa_merged_520');
        $form->addElement('header', 'title', 'POA Merged Report');

        $form->addElement('select', 'erp', 'Comp', ['220' => '220','250' => '250','300' => '300','400' => '400','410' => '410','420' => '420', '500' => '500', '520' => '520', '540' => '540','570' => '570','600' => '600', '620' => '620', '640' => '640','680' => '680', '660' => '660']);
        $form->addElement('date', 'planned_order_date_start', 'Planned Order Date Start',
            [
                'language' => 'en',
                'format' => 'Ymd',
                'minYear' => '1990',
                'maxYear' => date('Y') + 1,
                'addEmptyOption' => true,
            ]
        );
        $form->addElement('date', 'planned_order_date_end', 'Planned Order Date End',
            [
                'language' => 'en',
                'minYear' => date('Y') - 1,
                'maxYear' => date('Y') + 9,
                'format' => 'Ymd',
            ]);
        $form->addElement('date', 'planned_delivery_date_start', 'Planned Delivery Date Start',
            [
                'language' => 'en',
                'format' => 'Ymd',
                'minYear' => '1990',
                'maxYear' => date('Y') + 1,
                'addEmptyOption' => true,
            ]
        );
        $form->addElement('date', 'planned_delivery_date_end', 'Planned Delivery Date End',
            [
                'language' => 'en',
                'minYear' => date('Y') - 1,
                'maxYear' => date('Y') + 9,
                'format' => 'Ymd',
            ]);
        $form->addElement('text', 'vendor_id_start', 'Vendor ID Start', ['maxlength' => 6]);
        $form->addElement('text', 'vendor_id_end', 'Vendor ID End', ['maxlength' => 6]);
        $form->addElement('text', 'buyer_id_start', 'Buyer ID Start', ['maxlength' => 6]);
        $form->addElement('text', 'buyer_id_end', 'Buyer ID End', ['maxlength' => 6]);
        $form->addElement('text', 'planner_id_start', 'Planner ID Start', ['maxlength' => 6]);
        $form->addElement('text', 'planner_id_end', 'Planner ID End', ['maxlength' => 6]);
        $form->addElement('submit', 'btn', 'Generate report...');
        $form->setDefaults(
            [
                'erp' => 520,
                'planned_order_date_start' => ['Y' => '1990', 'm' => '01', 'd' => '01'],
                'planned_order_date_end' => ['Y' => date('Y'), 'm' => date('m'), 'd' => date('d')],
                'planned_delivery_date_start' => ['Y' => '1990', 'm' => '01', 'd' => '01'],
                'planned_delivery_date_end' => ['Y' => date('Y'), 'm' => date('m'), 'd' => date('d')],
                'vendor_id_start' => '000000',
                'vendor_id_end' => 'ZZZZZZ',
                'buyer_id_start' => '257000',
                'buyer_id_end' => '257099',
                'planner_id_start' => '000000',
                'planner_id_end' => '999999',
            ]
        );

        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }

        $vars = $form->exportValues();
        //do santiy checks
        if (!in_array($vars['erp'], [220,250,300,400,410,420, 500,510, 520, 540,570,600,620,640,660,680])) {
            $DEFAULT_ERROR[] = 'ERROR: Invalid company number...' . $vars['erp'];
            break;
        }
        if ($vars['buyer_id_start'] < 0 || $vars['buyer_id_start'] > 999999) {
            $DEFAULT_ERROR[] = 'ERROR: Invalid buyer id start...' . $vars['buyer_id_start'];
            break;
        }
        if ($vars['buyer_id_end'] < 0 || $vars['buyer_id_end'] > 999999) {
            $DEFAULT_ERROR[] = 'ERROR: Invalid buyer id end...' . $vars['buyer_id_end'];
            break;
        }
        if ($vars['planner_id_start'] < 0 || $vars['planner_id_start'] > 999999) {
            $DEFAULT_ERROR[] = 'ERROR: Invalid planner id start...' . $vars['planner_id_start'];
            break;
        }
        if ($vars['planner_id_end'] < 0 || $vars['planner_id_end'] > 999999) {
            $DEFAULT_ERROR[] = 'ERROR: Invalid planner id end...' . $vars['planner_id_end'];
            break;
        }
        $podt_start = implode('-', $vars['planned_order_date_start']);
        $podt_end = implode('-', $vars['planned_order_date_end']);
        $pddt_start = implode('-', $vars['planned_delivery_date_start']);
        $pddt_end = implode('-', $vars['planned_delivery_date_end']);
        $erp = $vars['erp'];
        ini_set('max_execution_time', '360');

        $query = <<<EOF
(
select
    itm.t_suno, mrp.t_item, itm.t_dsca, mrp.t_oqan,
    SUBSTRING(convert(varchar, mrp.t_podt, 120), 0 , 11) AS date1,
    SUBSTRING(convert(varchar, mrp.t_pddt, 120), 0 , 11) AS date2,
    itm.t_sfst, itm500.t_stoc AS stoc500, itm520.t_stoc as stoc520, itm540.t_stoc as stoc540,
    itm.t_oltm, itm.t_prip, itm.t_dscc,
    itm.t_buyr, byr.t_namb as byr_namb,mrp.t_cplb, pln.t_namb as pln_namb,
    't_osta' = CASE mrp.t_osta
        WHEN 1 THEN 'Planifi'
        WHEN 2 THEN 'Planifi ferme'
        WHEN 3 THEN 'Confirm'
        WHEN 4 THEN 'Transfr'
        WHEN 5 THEN 'Annul'
    END,
    SUBSTRING(convert(varchar, mrp.t_trdt, 120), 0 , 11) AS date3,
    mrp.t_orno, itm.t_csgp,
    itm520.t_copr, itm.t_ccde, itm.t_ctyo, itm420.t_stoc AS stoc420
from ttiitm001$erp as itm
    join ttimrp021$erp as mrp on itm.t_item=mrp.t_item
    left join ttccom001$erp as byr on itm.t_buyr=byr.t_emno
    left join ttccom001$erp as pln on mrp.t_cplb=pln.t_emno
    left join ttiitm001420 as itm420 on itm.t_item=itm420.t_item
    left join ttiitm001500 as itm500 on itm.t_item=itm500.t_item
    left join ttiitm001520 as itm520 on itm.t_item=itm520.t_item
    left join ttiitm001540 as itm540 on itm.t_item=itm540.t_item
WHERE
    mrp.t_podt BETWEEN '$podt_start' AND '$podt_end'
    AND mrp.t_pddt BETWEEN '$pddt_start' AND '$pddt_end'
    AND itm.t_suno BETWEEN '${vars['vendor_id_start']}' AND '${vars['vendor_id_end']}'
    AND itm.t_buyr BETWEEN '${vars['buyer_id_start']}' AND '${vars['buyer_id_end']}'
    AND itm.t_cplb BETWEEN '${vars['planner_id_start']}' AND '${vars['planner_id_end']}'
)UNION(
select
    itm.t_suno, inv.t_item, itm.t_dsca, inv.t_oqan,
    SUBSTRING(convert(varchar, inv.t_ordt, 120), 0 , 11) AS date1,
    SUBSTRING(convert(varchar, inv.t_dldt, 120), 0 , 11) AS date2,
    itm.t_sfst, itm500.t_stoc AS stoc500, itm520.t_stoc as stoc520, itm540.t_stoc as stoc540,
    itm.t_oltm, itm.t_prip, itm.t_dscc,
    inv.t_buyr, byr.t_namb as byr_namb, inv.t_cplb, pln.t_namb as pln_namb,
    case inv.t_appr
        when 1 then 'OUI'
        when 2 then 'NON'
    end,
    '' AS date3, inv.t_orno, itm.t_csgp,
    itm520.t_copr, itm.t_ccde, itm.t_ctyo, itm420.t_stoc AS stoc420
from ttdinv310$erp as inv
    left join ttiitm001$erp as itm on inv.t_item=itm.t_item
    left join ttccom001$erp as byr on inv.t_buyr=byr.t_emno
    left join ttccom001$erp as pln on inv.t_cplb=pln.t_emno
    left join ttiitm001420 as itm420 on itm.t_item=itm420.t_item
    left join ttiitm001500 as itm500 on itm.t_item=itm500.t_item
    left join ttiitm001520 as itm520 on itm.t_item=itm520.t_item
    left join ttiitm001540 as itm540 on itm.t_item=itm540.t_item
WHERE
    inv.t_ordt BETWEEN '$podt_start' AND '$podt_end'
    AND inv.t_dldt BETWEEN '$pddt_start' AND '$pddt_end'
    AND inv.t_suno BETWEEN '${vars['vendor_id_start']}' AND '${vars['vendor_id_end']}'
    AND inv.t_buyr BETWEEN '${vars['buyer_id_start']}' AND '${vars['buyer_id_end']}'
    AND inv.t_cplb BETWEEN '${vars['planner_id_start']}' AND '${vars['planner_id_end']}'
)UNION(
select
    itm.t_suno, mrp.t_item, itm.t_dsca, mrp.t_oqan,
    SUBSTRING(convert(varchar, mrp.t_psdt, 120), 0 , 11) AS date1,
    SUBSTRING(convert(varchar, mrp.t_pfdt, 120), 0 , 11) AS date2,
    itm.t_sfst, itm500.t_stoc AS stoc500, itm520.t_stoc as stoc520, itm540.t_stoc as stoc540,
    itm.t_oltm, itm.t_prip, itm.t_dscc,
    itm.t_buyr, byr.t_namb as byr_namb, mrp.t_cplb, pln.t_namb as pln_namb,
    't_osta' = CASE mrp.t_osta
        WHEN 1 THEN 'Planifi'
        WHEN 2 THEN 'Planifi ferme'
        WHEN 3 THEN 'Confirm'
        WHEN 4 THEN 'Transfr'
        WHEN 5 THEN 'Annul'
    END,
    SUBSTRING(convert(varchar, mrp.t_trdt, 120), 0 , 11) AS date3,
    mrp.t_orno, itm.t_csgp,
    itm520.t_copr, itm.t_ccde, itm.t_ctyo, itm420.t_stoc AS stoc420
from ttiitm001$erp as itm
    join ttimrp020$erp as mrp on itm.t_item=mrp.t_item
    left join ttccom001$erp as byr on itm.t_buyr=byr.t_emno
    left join ttccom001$erp as pln on mrp.t_cplb=pln.t_emno
    left join ttiitm001420 as itm420 on itm.t_item=itm420.t_item
    left join ttiitm001500 as itm500 on itm.t_item=itm500.t_item
    left join ttiitm001520 as itm520 on itm.t_item=itm520.t_item
    left join ttiitm001540 as itm540 on itm.t_item=itm540.t_item
WHERE
    mrp.t_trdt <> '' and
    mrp.t_psdt BETWEEN '$podt_start' AND '$podt_end'
    AND mrp.t_pfdt BETWEEN '$pddt_start' AND '$pddt_end'
    AND itm.t_suno BETWEEN '${vars['vendor_id_start']}' AND '${vars['vendor_id_end']}'
    AND itm.t_buyr BETWEEN '${vars['buyer_id_start']}' AND '${vars['buyer_id_end']}'
    AND mrp.t_cplb BETWEEN '${vars['planner_id_start']}' AND '${vars['planner_id_end']}'
 )
EOF;
        $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
        foreach ($rows as $key => $row) {
            $query = "select count(*) as nb from ttdpur030$erp where t_suno='" . trim($rows[$key]['t_suno']) . "' and t_item='" . trim($rows[$key]['t_item']) . "' and t_tdat='1753-01-01'";
            $rows2 = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
            $arr = $rows[$key]['t_ccde'];
            if (empty($row['date3'])){
                unset($rows[$key]);
            }
            if ($rows2[0]['nb'] < 2) {
                $rows[$key]['t_ccde'] = 'N';
            } else {
                $rows[$key]['t_ccde'] = 'Y';
            }
            $rows[$key]['ccde'] = $arr;
        }
        $report = new tldXLS(
            $rows,
        );
        $report->out();
        exit;
        break;
    case 'mrp':
        $form = new HTML_QuickForm('formmrp_MERGED', 'post');
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'mrp');
        $form->addElement('header', 'title', 'POA Merged Report');

        $form->addElement('select', 'erp', 'Comp', ['220' => '220','250' => '250','300' => '300','400' => '400','410' => '410','420' => '420', '500' => '500', '520' => '520', '540' => '540','570' => '570','600' => '600', '620' => '620', '640' => '640','680' => '680', '660' => '660']);
        $form->addElement('date', 'planned_order_date_start', 'Planned Order Date Start',
            [
                'language' => 'en',
                'format' => 'Ymd',
                'minYear' => '1990',
                'maxYear' => date('Y') + 1,
                'addEmptyOption' => true,
            ]
        );
        $form->addElement('date', 'planned_order_date_end', 'Planned Order Date End',
            [
                'language' => 'en',
                'minYear' => date('Y') - 1,
                'maxYear' => date('Y') + 9,
                'format' => 'Ymd',
            ]);
        $form->addElement('date', 'planned_delivery_date_start', 'Planned Delivery Date Start',
            [
                'language' => 'en',
                'format' => 'Ymd',
                'minYear' => '1990',
                'maxYear' => date('Y') + 1,
                'addEmptyOption' => true,
            ]
        );
        $form->addElement('date', 'planned_delivery_date_end', 'Planned Delivery Date End',
            [
                'language' => 'en',
                'minYear' => date('Y') - 1,
                'maxYear' => date('Y') + 9,
                'format' => 'Ymd',
            ]);
        $form->addElement('text', 'vendor_id_start', 'Vendor ID Start', ['maxlength' => 6]);
        $form->addElement('text', 'vendor_id_end', 'Vendor ID End', ['maxlength' => 6]);
        $form->addElement('text', 'buyer_name', 'Buyer Name', ['maxlength' => 6]);
        $form->addElement('text', 'buyer_id_start', 'Buyer ID Start', ['maxlength' => 6]);
        $form->addElement('text', 'buyer_id_end', 'Buyer ID End', ['maxlength' => 6]);
        $form->addElement('text', 'planner_id_start', 'Planner ID Start', ['maxlength' => 6]);
        $form->addElement('text', 'planner_id_end', 'Planner ID End', ['maxlength' => 6]);
        $form->addElement('submit', 'btn', 'Generate report...');
        $form->setDefaults(
            [
                'erp' => 520,
                'planned_order_date_start' => ['Y' => '1990', 'm' => '01', 'd' => '01'],
                'planned_order_date_end' => ['Y' => date('Y'), 'm' => date('m'), 'd' => date('d')],
                'planned_delivery_date_start' => ['Y' => '1990', 'm' => '01', 'd' => '01'],
                'planned_delivery_date_end' => ['Y' => date('Y'), 'm' => date('m'), 'd' => date('d')],
                'vendor_id_start' => '000000',
                'vendor_id_end' => 'ZZZZZZ',
                'buyer_id_start' => '0',
                'buyer_id_end' => '999999',
                'planner_id_start' => '0',
                'planner_id_end' => '999999',
            ]
        );

        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }

        $vars = $form->exportValues();
        //do santiy checks
        if (!in_array($vars['erp'], [220,250,300,400,410,420, 500, 510, 520, 540,570,600,620,640,660,680])) {
            $DEFAULT_ERROR[] = 'ERROR: Invalid company number...' . $vars['erp'];
            break;
        }
        if ($vars['buyer_id_start'] < 0 || $vars['buyer_id_start'] > 999999) {
            $DEFAULT_ERROR[] = 'ERROR: Invalid buyer id start...' . $vars['buyer_id_start'];
            break;
        }
        if ($vars['buyer_id_end'] < 0 || $vars['buyer_id_end'] > 999999) {
            $DEFAULT_ERROR[] = 'ERROR: Invalid buyer id end...' . $vars['buyer_id_end'];
            break;
        }
        if ($vars['planner_id_start'] < 0 || $vars['planner_id_start'] > 999999) {
            $DEFAULT_ERROR[] = 'ERROR: Invalid planner id start...' . $vars['planner_id_start'];
            break;
        }
        if ($vars['planner_id_end'] < 0 || $vars['planner_id_end'] > 999999) {
            $DEFAULT_ERROR[] = 'ERROR: Invalid planner id end...' . $vars['planner_id_end'];
            break;
        }
        $podt_start = implode('-', $vars['planned_order_date_start']);
        $podt_end = implode('-', $vars['planned_order_date_end']);
        $pddt_start = implode('-', $vars['planned_delivery_date_start']);
        $pddt_end = implode('-', $vars['planned_delivery_date_end']);
        $erp = $vars['erp'];
        $buyerName = strtoupper($vars['buyer_name']);
        ini_set('max_execution_time', '360');

        $query = <<<EOF
(
select
    itm.t_suno, mrp.t_item, itm.t_dsca, mrp.t_oqan,
    SUBSTRING(convert(varchar, mrp.t_podt, 120), 0 , 11) AS date1,
    SUBSTRING(convert(varchar, mrp.t_pddt, 120), 0 , 11) AS date2,
    itm.t_sfst, itm500.t_stoc AS stoc500, itm520.t_stoc as stoc520, itm540.t_stoc as stoc540,
    itm.t_oltm, itm.t_prip, itm.t_dscc,
    itm.t_buyr, byr.t_namb as byr_namb,mrp.t_cplb, pln.t_namb as pln_namb,
    't_osta' = CASE mrp.t_osta
        WHEN 1 THEN 'Planifi'
        WHEN 2 THEN 'Planifi ferme'
        WHEN 3 THEN 'Confirm'
        WHEN 4 THEN 'Transfr'
        WHEN 5 THEN 'Annul'
    END,
    SUBSTRING(convert(varchar, mrp.t_trdt, 120), 0 , 11) AS date3,
    mrp.t_orno, itm.t_csgp, itm.t_csig,
    itm520.t_copr, itm.t_ccde, itm.t_ctyo, itm420.t_stoc AS stoc420,itm220.t_stoc AS stoc220,
        itm250.t_stoc AS stoc250,
        itm300.t_stoc AS stoc300,
        itm400.t_stoc AS stoc400,
        itm410.t_stoc AS stoc410,
        itm570.t_stoc AS stoc570,
        itm600.t_stoc AS stoc600,
        itm620.t_stoc AS stoc620,
        itm640.t_stoc AS stoc640,
        itm660.t_stoc AS stoc660,
        itm680.t_stoc AS stoc680
from ttiitm001$erp as itm
    join ttimrp021$erp as mrp on itm.t_item=mrp.t_item
    left join ttccom001$erp as byr on itm.t_buyr=byr.t_emno
    left join ttccom001$erp as pln on mrp.t_cplb=pln.t_emno
    left join ttiitm001420 as itm420 on itm.t_item=itm420.t_item
    left join ttiitm001500 as itm500 on itm.t_item=itm500.t_item
    left join ttiitm001520 as itm520 on itm.t_item=itm520.t_item
    left join ttiitm001540 as itm540 on itm.t_item=itm540.t_item
    left join ttiitm001220 as itm220 on itm.t_item=itm220.t_item
    left join ttiitm001250 as itm250 on itm.t_item=itm250.t_item
    left join ttiitm001300 as itm300 on itm.t_item=itm300.t_item
    left join ttiitm001400 as itm400 on itm.t_item=itm400.t_item
    left join ttiitm001410 as itm410 on itm.t_item=itm410.t_item
    left join ttiitm001570 as itm570 on itm.t_item=itm570.t_item
    left join ttiitm001600 as itm600 on itm.t_item=itm600.t_item
    left join ttiitm001620 as itm620 on itm.t_item=itm620.t_item
    left join ttiitm001640 as itm640 on itm.t_item=itm640.t_item
    left join ttiitm001660 as itm660 on itm.t_item=itm660.t_item
    left join ttiitm001680 as itm680 on itm.t_item=itm680.t_item
WHERE
    mrp.t_podt BETWEEN '$podt_start' AND '$podt_end'
    AND mrp.t_pddt BETWEEN '$pddt_start' AND '$pddt_end'
    AND itm.t_suno BETWEEN '${vars['vendor_id_start']}' AND '${vars['vendor_id_end']}'
    AND itm.t_buyr BETWEEN '${vars['buyer_id_start']}' AND '${vars['buyer_id_end']}'
    AND itm.t_cplb BETWEEN '${vars['planner_id_start']}' AND '${vars['planner_id_end']}'
    AND byr.t_namb LIKE '$buyerName%'
)UNION(
select
    itm.t_suno, inv.t_item, itm.t_dsca, inv.t_oqan,
    SUBSTRING(convert(varchar, inv.t_ordt, 120), 0 , 11) AS date1,
    SUBSTRING(convert(varchar, inv.t_dldt, 120), 0 , 11) AS date2,
    itm.t_sfst, itm500.t_stoc AS stoc500, itm520.t_stoc as stoc520, itm540.t_stoc as stoc540,
    itm.t_oltm, itm.t_prip, itm.t_dscc,
    itm.t_buyr, byr.t_namb as byr_namb, inv.t_cplb, pln.t_namb as pln_namb,
    case inv.t_appr
        when 1 then 'OUI'
        when 2 then 'NON'
    end,
    '' AS date3, inv.t_orno, itm.t_csgp, itm.t_csig,
    itm520.t_copr, itm.t_ccde, itm.t_ctyo, itm420.t_stoc AS stoc420,itm220.t_stoc AS stoc220,
        itm250.t_stoc AS stoc250,
        itm300.t_stoc AS stoc300,
        itm400.t_stoc AS stoc400,
        itm410.t_stoc AS stoc410,
        itm570.t_stoc AS stoc570,
        itm600.t_stoc AS stoc600,
        itm620.t_stoc AS stoc620,
        itm640.t_stoc AS stoc640,
        itm660.t_stoc AS stoc660,
        itm680.t_stoc AS stoc680
from ttdinv310$erp as inv
    left join ttiitm001$erp as itm on inv.t_item=itm.t_item
    left join ttccom001$erp as byr on itm.t_buyr=byr.t_emno
    left join ttccom001$erp as pln on inv.t_cplb=pln.t_emno
    left join ttiitm001420 as itm420 on itm.t_item=itm420.t_item
    left join ttiitm001500 as itm500 on itm.t_item=itm500.t_item
    left join ttiitm001520 as itm520 on itm.t_item=itm520.t_item
    left join ttiitm001540 as itm540 on itm.t_item=itm540.t_item
    left join ttiitm001220 as itm220 on itm.t_item=itm220.t_item
    left join ttiitm001250 as itm250 on itm.t_item=itm250.t_item
    left join ttiitm001300 as itm300 on itm.t_item=itm300.t_item
    left join ttiitm001400 as itm400 on itm.t_item=itm400.t_item
    left join ttiitm001410 as itm410 on itm.t_item=itm410.t_item
    left join ttiitm001570 as itm570 on itm.t_item=itm570.t_item
    left join ttiitm001600 as itm600 on itm.t_item=itm600.t_item
    left join ttiitm001620 as itm620 on itm.t_item=itm620.t_item
    left join ttiitm001640 as itm640 on itm.t_item=itm640.t_item
    left join ttiitm001660 as itm660 on itm.t_item=itm660.t_item
    left join ttiitm001680 as itm680 on itm.t_item=itm680.t_item
WHERE
    inv.t_ordt BETWEEN '$podt_start' AND '$podt_end'
    AND inv.t_dldt BETWEEN '$pddt_start' AND '$pddt_end'
    AND inv.t_suno BETWEEN '${vars['vendor_id_start']}' AND '${vars['vendor_id_end']}'
    AND inv.t_buyr BETWEEN '${vars['buyer_id_start']}' AND '${vars['buyer_id_end']}'
    AND inv.t_cplb BETWEEN '${vars['planner_id_start']}' AND '${vars['planner_id_end']}'
    AND byr.t_namb LIKE '$buyerName%'
)UNION(
select
    itm.t_suno, mrp.t_item, itm.t_dsca, mrp.t_oqan,
    SUBSTRING(convert(varchar, mrp.t_psdt, 120), 0 , 11) AS date1,
    SUBSTRING(convert(varchar, mrp.t_pfdt, 120), 0 , 11) AS date2,
    itm.t_sfst, itm500.t_stoc AS stoc500, itm520.t_stoc as stoc520, itm540.t_stoc as stoc540,
    itm.t_oltm, itm.t_prip, itm.t_dscc,
    itm.t_buyr, byr.t_namb as byr_namb, mrp.t_cplb, pln.t_namb as pln_namb,
    't_osta' = CASE mrp.t_osta
        WHEN 1 THEN 'Planifi'
        WHEN 2 THEN 'Planifi ferme'
        WHEN 3 THEN 'Confirm'
        WHEN 4 THEN 'Transfr'
        WHEN 5 THEN 'Annul'
    END,
    SUBSTRING(convert(varchar, mrp.t_trdt, 120), 0 , 11) AS date3,
    mrp.t_orno, itm.t_csgp,itm.t_csig,
    itm520.t_copr, itm.t_ccde, itm.t_ctyo, itm420.t_stoc AS stoc420,itm220.t_stoc AS stoc220,
        itm250.t_stoc AS stoc250,
        itm300.t_stoc AS stoc300,
        itm400.t_stoc AS stoc400,
        itm410.t_stoc AS stoc410,
        itm570.t_stoc AS stoc570,
        itm600.t_stoc AS stoc600,
        itm620.t_stoc AS stoc620,
        itm640.t_stoc AS stoc640,
        itm660.t_stoc AS stoc660,
        itm680.t_stoc AS stoc680
from ttiitm001$erp as itm
    join ttimrp020$erp as mrp on itm.t_item=mrp.t_item
    left join ttccom001$erp as byr on itm.t_buyr=byr.t_emno
    left join ttccom001$erp as pln on mrp.t_cplb=pln.t_emno
    left join ttiitm001420 as itm420 on itm.t_item=itm420.t_item
    left join ttiitm001500 as itm500 on itm.t_item=itm500.t_item
    left join ttiitm001520 as itm520 on itm.t_item=itm520.t_item
    left join ttiitm001540 as itm540 on itm.t_item=itm540.t_item
    left join ttiitm001220 as itm220 on itm.t_item=itm220.t_item
    left join ttiitm001250 as itm250 on itm.t_item=itm250.t_item
    left join ttiitm001300 as itm300 on itm.t_item=itm300.t_item
    left join ttiitm001400 as itm400 on itm.t_item=itm400.t_item
    left join ttiitm001410 as itm410 on itm.t_item=itm410.t_item
    left join ttiitm001570 as itm570 on itm.t_item=itm570.t_item
    left join ttiitm001600 as itm600 on itm.t_item=itm600.t_item
    left join ttiitm001620 as itm620 on itm.t_item=itm620.t_item
    left join ttiitm001640 as itm640 on itm.t_item=itm640.t_item
    left join ttiitm001660 as itm660 on itm.t_item=itm660.t_item
    left join ttiitm001680 as itm680 on itm.t_item=itm680.t_item
WHERE
    mrp.t_psdt BETWEEN '$podt_start' AND '$podt_end'
    AND mrp.t_pfdt BETWEEN '$pddt_start' AND '$pddt_end'
    AND itm.t_suno BETWEEN '${vars['vendor_id_start']}' AND '${vars['vendor_id_end']}'
    AND itm.t_buyr BETWEEN '${vars['buyer_id_start']}' AND '${vars['buyer_id_end']}'
    AND mrp.t_cplb BETWEEN '${vars['planner_id_start']}' AND '${vars['planner_id_end']}'
    AND byr.t_namb LIKE '$buyerName%'
 )
EOF;

        $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
        foreach ($rows as $key => $row) {
            $query = "select count(*) as nb from ttdpur030$erp where t_suno='" . trim($rows[$key]['t_suno']) . "' and t_item='" . trim($rows[$key]['t_item']) . "' and t_tdat='1753-01-01'";
            $rows2 = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
            $itm = new tldITM($rows[$key]['t_item'], $erp);
            $mrbstock = $itm->getMRBStock($rows[$key]['t_item'], $erp);
            $rows[$key]['mrb'] = $mrbstock['stoc'];
            $arr = $rows[$key]['t_ccde'];
            if ($rows2[0]['nb'] < 2) {
                $rows[$key]['t_ccde'] = 'N';
            } else {
                $rows[$key]['t_ccde'] = 'Y';
            }
            $rows[$key]['ccde'] = $arr;
        }
        $report = new tldXLS(
            $rows,
            [
                'xItems' => [
                    't_suno' => 'Supplier#',
                    't_item' => 'Item#',
                    't_dsca' => 'Description',
                    't_oqan' => 'QTY',
                    'date1' => 'Planned order date',
                    'date2' => 'Planned delivery date',
                    't_sfst' => 'Safty stock',
                    't_oltm' => 'Lead time',
                    't_prip' => 'Purchase price',
                    't_dscc' => 'Size',
                    't_buyr' => 'Buyer#',
                    'byr_namb' => 'Buyer name',
                    't_cplb' => 'Planner#',
                    'pln_namb' => 'Planner name',
                    'plan' => 'Plan',
                    't_osta' => 'Order status',
                    'date3' => 'Transaction date',
                    't_orno' => 'Order#',
                    't_csgp' => 'STAT group',
                    't_copr' => 'STD price',
                    't_ccde' => 'Commodity code',
                    't_ctyo' => 'COO',
                    'ccde' => 'Statistic Number',
                    't_csig' => 'Signal Code',
                    'mrb' => 'MRB Stock',
                    'stoc500' => 'MTL Stock',
                    'stoc510' => 'DTV Stock',
                    'stoc520' => 'STL Stock',
                    'stoc540' => 'TLD EUR Stock',
                    'stoc420' => 'SHE Stock',
                    'stoc220' => 'PV Stock',
                    'stoc250' => 'AS Stock',
                    'stoc300' => 'AME Stock',
                    'stoc400' => 'WIN Stock',
                    'stoc410' => 'WIC Stock',
                    'stoc570' => 'TLD LEB Stock',
                    'stoc600' => 'ASI Stock',
                    'stoc620' => 'GST Stock',
                    'stoc640' => 'SHA Stock',
                    'stoc660' => 'WUX Stock',
                    'stoc680' => 'CHI Stock',

                ],
                'showTitles' => true
            ]
        );
        $report->out();
        exit;
        break;

    case 'OTDPbyVBC':
        if (empty($DEFAULT_ERP)) {
            $DEFAULT_ERROR[] = 'ERROR: No ERP company selected...';
            break;
        }

        $xItems = [
            't_reno' => 'Receipt number',
            't_namc' => 'Buyer name',
            't_namb' => 'Name 2',
            't_orno' => 'Order number',
            't_pono' => 'Position number',
            't_srnb' => 'Sequence number',
            't_csgp' => 'Purchase statistics group',
            't_item' => 'Item',
            't_dsca' => 'Description',
            't_suno' => 'Supplier',
            't_nama' => 'Supplier name',
            't_cbrn' => 'Line of business',
            't_pric' => 'Price',
            't_ccur' => 'Currency',
            't_ratp' => 'Purchase rate',
            't_quap' => 'Approved qty',
            't_amnt' => 'Amount',
            't_odat' => 'Order date',
            't_ddta' => 'Planned delivery date',
            't_ddtc' => ' Confirmed delivery date',
            't_ddtd' => 'Changed delivery date',
            't_date' => 'Receipt date',
            't_ddtb' => 'Current planned date',
            't_oltm' => 'Order lead time',
            't_cotp' => 'Order type',
        ];

        // Get listing
        $erpList = tldLocation::getERPList('smartyOptions');

        // Get form
        $form = new HTML_QuickForm('frmOTDPbyVBC', 'get', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'OTDPbyVBC');
        $form->addElement('header', 'title', 'Select dates and company:');
        $form->addElement('date', 'x', 'From',
            ['format' => 'Y-m-d', 'minYear' => date('Y') - 2, 'maxYear' => date('Y') + 2]);
        $form->addElement('date', 'y', 'To',
            ['format' => 'Y-m-d', 'minYear' => date('Y') - 2, 'maxYear' => date('Y') + 2]);
// SUPPLIER
        $erpObj = new tldBaanERP($DEFAULT_ERP);
        $data = $erpObj->getSupplierData(null, null, ['orderBy' => 't_suno']);
        if (empty($data)) {
            $DEFAULT_ERROR[] = "No vendors found for ERP#$erp";
            break;
        }
        foreach ($data as $sup) {
            $suppliers[$sup['t_suno']] = $sup['t_suno'] . ' -> ' . $sup['t_nama'];
        }
        $form->addElement('select', 'suno', 'Supplier', ['' => ''] + $suppliers);
// BUYER
        $query = <<<EOF
		select distinct F1.t_nama as nama from ttccom001$DEFAULT_ERP F1 where F1.t_namb<>'' and F1.t_info<>''
EOF;
        $rowsTmp1 = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
        $buyers = ['ALL' => 'ALL'];
        foreach ($rowsTmp1 as $key1 => $value1) {
            $buyers[$rowsTmp1[$key1][nama]] = $rowsTmp1[$key1][nama];
        }
        $form->addElement('select', 'buyer', 'Buyer', ['ALL' => 'ALL'] + $buyers);
        $form->addRule('buyer', 'Required', 'required');

        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('x', 'This is required', 'required');
        $form->addRule('y', 'This is required', 'required');
        $form->setDefaults(['x' => date('Y-01-01')]);
        $form->setDefaults(['y' => date('Y-m-d')]);

        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $vars['from'] = implode('-', $vars['x']);
        $vars['to'] = implode('-', $vars['y']);
        $vars['$dateTmpF'] = $vars['x']['Y'] . '-' . substr('00' . $vars['x']['m'], -2) . '-' . substr('00' . $vars['x']['d'], -2);
        $vars['$dateTmpT'] = $vars['y']['Y'] . '-' . substr('00' . $vars['y']['m'], -2) . '-' . substr('00' . $vars['y']['d'], -2);

        $rows = tldUtils::getSqlToAssocArray("EXEC OTDP_By_Vendor_Buyer_Company '{$vars['$dateTmpF']}','{$vars['$dateTmpT']}', $DEFAULT_ERP, '{$vars['suno']}', '{$vars['buyer']}'", 'odbc', ['src' => 'baan']);
        $caption = "OTDP By Vendor/Buyer/Company from {$vars['from']} to {$vars['to']}";

        if (isset($rows, $caption)) {
            $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=xls">XLS version</a>&nbsp;|&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[2]=csv">CSV version</a>
EOF;
    switch ($m[2]) {
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
        case 'csv':
            $report = new tldCSV(
                $rows,
                [
                    'xItems' => $xItems,
                    'showTitles' => true,
                ]
            );
            $report->out();
            exit;
            break;
        default:

            $report = new tldReportColumnar($rows,
                [
                    'xItems' => $xItems,
                    'title' => $caption,
                ]
            );
            $body .= $report->fetch();
            break;
    }
}
        break;


    case 'vendorOTDP':
        if (empty($DEFAULT_ERP)) {
            $DEFAULT_ERROR[] = 'ERROR: No ERP company selected...';
            break;
        }
        // Create form
        $form = new HTML_QuickForm('frmByNum', 'post');
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'vendorOTDP');
        $form->addElement('hidden', 'm[2]', $m[2]);
        switch ($m[2]) {
            case 'byBUID':
                $form->addElement('header', 'title', 'Vendor OTDP By Location');
                $erps = tldUtils::optionsByKeyValue(tldLocation::getLocationList(), 'erp', 'location');
                $form->addElement('select', 'erp', 'Location', ['' => ''] + $erps);
                $form->addRule('erp', 'Required', 'required');
                $form->setDefaults(['erp' => isset($_GET['erp']) ? $_GET['erp'] : $DEFAULT_ERP]);
                break;
            case 'bySuno':
                $erpObj = new tldBaanERP($DEFAULT_ERP);
                $data = $erpObj->getSupplierData(null, null, ['orderBy' => 't_suno']);
                if (empty($data)) {
                    $DEFAULT_ERROR[] = "No vendors found for ERP#$erp";
                    break;
                }
                foreach ($data as $sup) {
                    $suppliers[$sup['t_suno']] = $sup['t_suno'] . ' -> ' . $sup['t_nama'];
                }
                $form->addElement('header', 'title', "Vendor OTDP By Vendor for ERP#$DEFAULT_ERP");
                $form->addElement('select', 'suno', 'Supplier', ['' => ''] + $suppliers);
                $form->addRule('suno', 'Required', 'required');
                break;
            case 'byPart':
                $form->addElement('header', 'title', "Vendor OTDP By Part for ERP#$DEFAULT_ERP");
                $form->addElement('text', 'pn', 'PN#');
                $form->addRule('pn', 'Required', 'required');
                break;
            case 'byBuyer':
                $buyerGrp = new tldGroup('role_BYR', $DEFAULT_ERP);
                $buyers = tldUtils::optionsByKeyValue($buyerGrp->getUserlist(), 'email', 'fullname');
                if (empty($buyers)) {
                    $DEFAULT_ERROR[] = "ERROR: No buyers found for ERP#$DEFAULT_ERP...";
                    break;
                }
                $form->addElement('header', 'title', "Vendor OTDP By Buyer for ERP#$DEFAULT_ERP");
                $form->addElement('select', 'email', 'Buyer', ['ALL' => 'ALL'] + $buyers);
                $form->addRule('email', 'Required', 'required');
                break;
        }
        $form->addElement('date', 'period[ds]', 'Start',
            ['format' => 'Y-m', 'minYear' => date('Y') - 2, 'maxYear' => date('Y')]
        );
        $form->addElement('date', 'period[de]', 'End',
            ['format' => 'Y-m', 'minYear' => date('Y') - 2, 'maxYear' => date('Y')]
        );
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addElement('reset', 'btnReset', 'Reset');
        $form->setDefaults(
            [
                'period' => [
                    'ds' => ['Y' => date('Y') - 1, 'm' => date('m')],
                    'de' => date('Y-m'),
                ],
            ]
        );

        if (!$form->validate()) {
            $body .= $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        switch ($m[2]) {
            case 'byBUID':
                $erp = $vars['erp'];
                break;
            default:
                $erp = $DEFAULT_ERP;
                break;
        }
        // Checking data
        $ds = new DateTime(implode('-', $vars['period']['ds']) . '-01');
        $de = new DateTime(implode('-', $vars['period']['de']) . '-01');
        if ($ds > $de) {
            $DEFAULT_ERROR[] = "ERROR: Start date {$ds->format('Y-m')} can not be greater than End date {$de->format('Y-m')}";
            $body .= $form->toHTML();
            break;
        }
        $sess['mfg']['kpi']['period']['ds'] = $ds->format('Y-m');
        $sess['mfg']['kpi']['period']['de'] = $de->format('Y-m');
        // Display KPI
        $buid = tldLocation::getIDByERP($erp);
        $GraphURL = "/en/private/manufacturing/kpi/graphs.php?m[0]=history&m[1]=otdpVendors&buid=$buid&m[2]=";
        switch ($m[2]) {
            case 'byBUID':
                $body .= _getKPIgraph(
                    $GraphURL . 'reliability',
                    'OTDP Vendors reliability Definition',
                    $help['OTDP Vendors reliability Target']
                );
                break;
            case 'bySuno':
                $body .= _getKPIgraph(
                    $GraphURL . 'reliability&m[3]=byVendor&suno=' . $vars['suno'],
                    'OTDP Vendors reliability by Vendor Definition',
                    $help['OTDP Vendors reliability Target']
                );
                break;
            case 'byPart':
                $ob = tldERP::getERPOb($erp);
                $isValid = $ob->getItemData($vars['pn']);
                if ($isValid == 0) {
                    $DEFAULT_ERROR[] = "PN#{$vars['pn']} does not exist in ERP#$erp";
                    break;
                }
                $body .= _getKPIgraph(
                    $GraphURL . 'reliability&m[3]=byPart&pn=' . $vars['pn'],
                    'OTDP Vendors reliability by Parts Definition',
                    $help['OTDP Vendors reliability Target']
                );
                break;
            case 'byBuyer':
                $data = [];
                if ($vars['email'] !== 'ALL') {
                    $data[$vars['email']] = $vars['email'];
                } else {
                    $data = $buyers; // $buyers from the form above
                }
                foreach ($data as $email => $val) {
                    $body .= _getKPIgraph(
                        $GraphURL . 'reliability&m[3]=byBuyer&email=' . $email,
                        'OTDP Vendors reliability by Buyer Definition',
                        $help['OTDP Vendors reliability Target']
                    );
                }
                break;
        }
        break;
    case 'vendorPOConfirm':
        if (empty($DEFAULT_ERP)) {
            $DEFAULT_ERROR[] = 'ERROR: No ERP company selected...';
            break;
        }
        // Create form
        $form = new HTML_QuickForm('frmByNum', 'post');
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'vendorPOConfirm');
        $form->addElement('hidden', 'm[2]', $m[2]);
        switch ($m[2]) {
            case 'byBUID':
                $form->addElement('header', 'title', 'PO Confirm By Location');
                $erps = tldUtils::optionsByKeyValue(tldLocation::getLocationList(), 'erp', 'location');
                $form->addElement('select', 'erp', 'Location', ['' => ''] + $erps);
                $form->addRule('erp', 'Required', 'required');
                $form->setDefaults(['erp' => $DEFAULT_ERP]);
                break;
            case 'bySunoBuyer':
                $erpObj = new tldBaanERP($DEFAULT_ERP);
                $data = $erpObj->getSupplierData(null, null, ['orderBy' => 't_suno']);
                if (empty($data)) {
                    $DEFAULT_ERROR[] = "No vendors found for ERP#$erp";
                    break;
                }
                foreach ($data as $sup) {
                    $suppliers[$sup['t_suno']] = $sup['t_suno'] . ' -> ' . $sup['t_nama'];
                }
                $form->addElement('header', 'title', "PO Confirm By Vendor and Buyer for ERP#$DEFAULT_ERP");
                $form->addElement('select', 'suno', 'Supplier', ['' => '', 'ALL' => 'ALL'] + $suppliers);
                $form->addRule('suno', 'Required', 'required');

                $buyerGrp = new tldGroup('role_BYR', $DEFAULT_ERP);
                $buyers = tldUtils::optionsByKeyValue($buyerGrp->getUserlist(), 'id', 'fullname');
                if (empty($buyers)) {
                    $DEFAULT_ERROR[] = "ERROR: No buyers found for ERP#$DEFAULT_ERP...";
                    break;
                }
                $form->addElement('select', 'buyer', 'Buyer', ['' => '', 'ALL' => 'ALL'] + $buyers);
                $form->addRule('buyer', 'Required', 'required');
                break;
        }
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addElement('reset', 'btnReset', 'Reset');

        if (!$form->validate()) {
            $body .= $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        switch ($m[2]) {
            case 'byBUID':
                $erp = $vars['erp'];
                break;
            default:
                $erp = $DEFAULT_ERP;
                break;
        }
        // Display KPI
        $buid = tldLocation::getIDByERP($erp);
        $GraphURL = "/en/private/manufacturing/kpi/graphs.php?m[0]=&m[1]=ConfirmPOPast12Months&buid=$buid&rep=&sup=";
        switch ($m[2]) {
            case 'byBUID':
                $body .= _getKPIgraph(
                    $GraphURL,
                    'Vendor PO Confirm Definition',
                    $help['Vendor PO Confirm Group Target']
                );
                break;
            case 'bySunoBuyer':
                if ($vars['buyer'] !== 'ALL') {
                    $GraphURL .= "&rep={$vars['buyer']}";
                }
                if ($vars['suno'] !== 'ALL') {
                    $GraphURL .= "&sup={$vars['suno']}";
                }
                $body .= _getKPIgraph(
                    $GraphURL,
                    'Vendor PO Confirm Definition',
                    $help['Vendor PO Confirm Group Target']
                );
                break;
        }
        break;
    case 'inventorywipraw':
        if (empty($DEFAULT_ERP)) {
            $DEFAULT_ERROR[] = 'ERROR: No ERP company selected...';
            break;
        }
        // Create form
        $form = new HTML_QuickForm('frmByNum', 'post');
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'inventorywipraw');
        $form->addElement('hidden', 'm[2]', $m[2]);
        switch ($m[2]) {
            case 'byBUID':
                if (isset($_GET['erp'])) {
                    $DEFAULT_ERP = $_GET['erp'];
                }
                $form->addElement('header', 'title', 'Inventory Value By Company');
                $erps = tldUtils::optionsByKeyValue(tldLocation::getFactoryList(), 'erp', 'location');
                $form->addElement('select', 'erp', 'Location', ['' => ''] + $erps);
                $form->addRule('erp', 'Required', 'required');
                $form->setDefaults(['erp' => $DEFAULT_ERP]);
                break;
        }
        $form->addElement('submit', 'btnSubmit', 'Submit');

        if (!$form->validate()) {
            $body .= $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        switch ($m[2]) {
            case 'byBUID':
                $erp = $vars['erp'];
                break;
            default:
                $erp = $DEFAULT_ERP;
                break;
        }
        // Display KPI
        $buid = tldLocation::getIDByERP($erp);
        $GraphURL = "/en/private/manufacturing/index.php?m[0]=activity&m[1]=graphs&m[2]=inventoryKPIValue&factory=$buid";
        switch ($m[2]) {
            case 'byBUID':
                $body .= _getKPIgraph(
                    $GraphURL,
                    'Inventory KPI Definition',
                    ''
                );
                break;
        }
        break;
    case 'ITR':
        if (empty($DEFAULT_ERP)) {
            $DEFAULT_ERROR[] = 'ERROR: No ERP company selected...';
            break;
        }
        // Create form
        $form = new HTML_QuickForm('frmByNum', 'post');
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'ITR');
        $form->addElement('hidden', 'm[2]', $m[2]);
        switch ($m[2]) {
            case 'byBUID':
                $form->addElement('header', 'title', 'ITR By Location');
                $erps = tldUtils::optionsByKeyValue(tldLocation::getFactoryList(), 'erp', 'location');
                $form->addElement('select', 'erp', 'Location', ['' => ''] + $erps);
                $form->addRule('erp', 'Required', 'required');
                $form->setDefaults(['erp' => $DEFAULT_ERP]);
                break;
        }
        $form->addElement('submit', 'btnSubmit', 'Submit');

        if (!$form->validate()) {
            $body .= $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        switch ($m[2]) {
            case 'byBUID':
                $erp = $vars['erp'];
                break;
            default:
                $erp = $DEFAULT_ERP;
                break;
        }
        // Display KPI
        $buid = tldLocation::getIDByERP($erp);
        $GraphURL = "/en/private/manufacturing/kpi/graphs.php?m[0]=history&m[1]=ITR&buid=$buid&m[2]=";
        switch ($m[2]) {
            case 'byBUID':
                $body .= _getKPIgraph(
                    $GraphURL,
                    'ITR Definition',
                    ''
                );
                break;
        }
        break;
    case 'po':
        if (empty($erp) || empty($suno)) {
            $DEFAULT_ERROR[] = 'ERROR: No erp or suno set';
            break;
        }
        $DEFAULT_TITLE .= "\Open PO line items for $erp $suno";
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=reports&m[1]=po&erp=$erp&suno=$suno">Online Version</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=reports&m[1]=po&m[2]=csv&erp=$erp&suno=$suno">CSV Version</a>
EOF;
        $pos = tldPO::getDetailByVendorERP(
            $suno,
            $erp,
            [
                'mode' => 'openLineItems',
                'sortField' => $field,
                'sortOrder' => $order,
            ]
        );
        if (count($pos) < 1) {
            $DEFAULT_ERROR[] = 'No Purchase Orders found';
            break;
        }
        // Get EDI SO
        $erpObj = new tldBaanERP($erp);
        $sup = $erpObj->getSupplierData($suno);
        if ($sup['t_inrl'] == 1) {
            $is_edi = 1;
            $smarty->assign('is_edi', $is_edi);
            foreach ($pos as &$po) {
                $query = <<<EOF
			SELECT
				sor.t_orno,
				(
					SELECT
						sol.t_pono
					FROM
						ttdsls041{$sup['t_iscn']} sol
					WHERE
						sol.t_orno=sor.t_orno AND
						sol.t_epos='{$po['t_pono']}'
				) AS t_pono
			FROM
				ttdsls040{$sup['t_iscn']} sor
			WHERE
				sor.t_eono='{$po['t_orno']}'
EOF;
                $res = tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
                $po['SO'] = trim($res['t_orno']);
                $po['SOL'] = trim($res['t_pono']);
                $po['SOERP'] = trim($sup['t_iscn']);
            }
        }
        $smarty->assign('pos', $pos);
        $fields = [
            't_orno' => 'Purch. Order#',
            't_pono' => 'Line No.',
            't_odat' => 'PO Order Date',
        ];
        if ($is_edi) {
            $fields += [
                'SO' => 'SO#',
                'SOL' => 'SO Line#',
            ];
        }
        $fields += [
            't_item' => 'Part Number',
            't_dsca' => 'Description',
            't_oqua' => 'Order Qty',
            't_dqua' => 'Del Qty',
            't_bqua' => 'Back Qty',
            't_ddta' => 'Orig Del. Date',
            't_resc' => 'Resched Del. Date',
            't_ddtc' => 'Confirmed Date',
            't_ddtb' => 'Current Del. Date',
            'days_to_del' => 'Countdown Days',
            't_ddts' => 'Actual Shipping Date',
        ];
        $smarty->assign('fields', $fields);
        $smarty->assign('order', $order);
        $smarty->assign('erp', $erp);
        $smarty->assign('suno', $suno);

        switch ($m[2]) {
            case 'plain':
                $template = 'empty.tpl';
                $smarty->assign('plain', 'YES');
                break;
            case 'csv':
                $report = new tldCSV(
                    $pos,
                    [
                        'xItems' => $fields,
                        'showTitles' => true,
                    ]
                );
                $report->out();
                exit;
                break;
        }
        $body = $smarty->fetch("$PATH/reports/rpt_by_date.tpl");
        break;
    case 'byCancellationByERPPddt':
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=reports&m[1]=byCancellationByERPPddt&m[2]={$m[2]}&m[3]=xls&y=$y&x=$x&z=$z">XLS Version</a>
EOF;
        // Secure data
        $location = TldDatabase::escape($y);
        $level = TldDatabase::escape($x);
        $buyer_email = TldDatabase::escape($z);
        switch ($m[2]) {
            case 'byWHS':
                $rows = tldPOL::byCancellationByWHSPddtByERP($DEFAULT_ERP, $location, $level);
                break;
            case 'byWHSbyBuyer':
                $rows = tldPOL::byCancellationByWHSByBuyerPddtByERP($DEFAULT_ERP, $location, $level, $buyer_email);
                break;
            default:
                $rows = tldPOL::byCancellationByERPPddt($location, $level);
                break;
        }

        switch ($m[3]) {
            case 'xls':
                $report = new tldCSV(
                    $rows,
                    [
                        'xItems' => [
                            't_nama' => 'Buyer',
                            't_orno' => 'PO#',
                            't_suno' => 'Vendor code',
                            't_pono' => 'Line #',
                            't_pddt' => 'Planned delivery date',
                            't_item' => 'Part #',
                            't_dsca' => 'Description',
                            't_oqan' => 'Order Qty',
                            't_cuni' => 'UM',
                            't_amta' => 'Amount',
                            't_ccur' => 'Currency',
                            't_copr' => 'Std Cost',
                            't_text' => 'Text Field',
                        ],
                        'showTitles' => true,
                    ]
                );
                $report->out();
                exit;
                break;
            default:
                $body .= _listPOLC($rows, "PO Line cancellation for $location, urgency: $level");
                break;
        }
        break;
    case 'byReschedulingByERPPddt':
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=reports&m[1]=byReschedulingByERPPddt&m[2]={$m[2]}&m[3]=xls&y=$y&x=$x&z=$z">XLS Version</a>
EOF;
        // Secure data
        $location = TldDatabase::escape($y);
        $level = TldDatabase::escape($x);
        $buyer_email = TldDatabase::escape($z);

        switch ($m[2]) {
            case 'byWHS':
                $rows = tldPOL::byReschedulingByWHSPddtByERP($DEFAULT_ERP, $location, $level, ['io' => $io]);
                break;
            case 'byWHSbyBuyer':
                $rows = tldPOL::byReschedulingByWHSByBuyerPddtByERP($DEFAULT_ERP, $location, $level, $buyer_email, ['io' => $io]);
                break;
            default:
                $rows = tldPOL::byReschedulingByERPPddt($location, $level);
                break;
        }

        switch ($m[3]) {
            case 'xls':
                $rows = array_map(static function ($row) {
                    $row['t_text'] = $row['t_text'][0] === '=' ? "'" . $row['t_text'] : $row['t_text'];
                    return $row;
                }, $rows);
                $report = new tldXLS(
                    $rows,
                    [
                        'xItems' => [
                            't_nama' => 'Buyer',
                            'v_nama' => 'Vendor name',
                            't_suno' => 'Vendor code',
                            't_orno' => 'PO#',
                            't_pono' => 'Line #',
                            't_pddt' => 'Planned delivery date',
                            't_date' => 'Reschedule date',
                            'day_diff' => 'Days Diff',
                            't_item' => 'Part #',
                            't_dsca' => 'Description',
                            't_oqan' => 'Order Qty',
                            't_cuni' => 'UM',
                            'message' => 'Message',
                            't_amta' => 'Amount',
                            't_ccur' => 'Currency',
                            't_copr' => 'Std Cost',
                            't_text' => 'Text Field',
                        ],
                        'showTitles' => true,
                    ]
                );
                $report->out();
                exit;
                break;
            default:
                $body .= _listPOLR($rows, "PO Line Rescheduling for $location, urgency: $level");
                break;
        }
        break;
    case 'SEQReport':
        $cond = ['T1.tplno' => 30];
        switch ($x) {
            case 'Inventory':
                $cond['T1.cur_step'] = 1;
                break;
            case 'Process':
                $cond['T1.cur_step'] = 2;
                break;
            case 'Conclusion':
                $cond['T1.cur_step'] = 3;
                break;
        }
        if ($y !== 'ALL') {
            $cond['T1.bu_id'] = tldLocation::getIDByLocation($y);
        }

        $constraints = [];
        foreach ($cond as $key => $value) {
            $contraints[] = "$key=$value";
        }
        $rows = tldTask::byOpenByConstraints(implode(' AND ', $contraints));
        $report = new tldReportColumnar($rows,
            [
                'xItems' => [
                    'id' => 'Task#',
                    'module' => 'Module',
                    'status' => 'Status',
                    'cur_step' => 'Current Step',
                    'due_date' => 'Due',
                    'assignor_fullname' => 'Assignor',
                    'assignee_fullname' => 'Assignee',
                    'task' => 'Task',
                ],
                'title' => 'SEQ Slow Moving Item by Current Step',
                'links' => [
                    'id' => [
                        'url' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=',
                        'params' => ['id' => 'id'],
                    ],
                ],
            ]

        );
        $body .= $report->fetch();
        break;
    case 'benchmark':
        include 'reports/benchmark.inc.php';
        break;
    case 'output':
        switch ($out) {
            case 'xls':
                $report = new tldXLS(
                    $sess['listing']['data'],
                    [
                        'xItems' => $sess['listing']['xItems'],
                        'showTitles' => true,
                    ]
                );
                $report->out();
                exit;
                break;
            case 'csv':
                $report = new tldCSV(
                    $sess['listing']['data'],
                    [
                        'xItems' => $sess['listing']['xItems'],
                        'showTitles' => true,
                    ]
                );
                $report->out();
                exit;
                break;
        }
        break;
    case 'aeroShippingReport':
        if (!$user->isInGroupLevel('gg_ADMIN', 250) && !$user->isInGroupLevel('gg_PARTS', 250)
            && !$user->isInGroupLevel('gg_PUR', 250) && !$user->isInGroupLevel('gg_SALES', 250) && !$user->isInGroup('SUPERUSER')) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permissions to access this page.';
            break;
        }

        switch ($m[2]) {
            case 'xls':
                $data = $_SESSION['data_report'];
                $xItems = array_diff($_SESSION['data_fields'], ['Files', 'Create Task', 'View Task']);
                $reportXLS = new tldXLS(
                    $data,
                    [
                        'xItems' => $xItems,
                        'showTitles' => true,
                    ]
                );
                $reportXLS->out('AERO_Sales_Purchase_Order_Shipping_Report.xls');
                exit;
                break;
        }

        // Define a date interval
        $begin = new \DateTime('first day of 12 months ago');
        $end = new \DateTime('next month');
        $interval = new DateInterval('P1M');
        $dateInterval = new DatePeriod($begin, $interval, $end);
        foreach ($dateInterval as $period) {
            $datePeriod[] = $period->format('Y-m');
        }

        // Obtain every Sales REP from baan for AERO ERP
        $query = <<<EOF
SELECT t_nama as sales_rep
FROM ttccom001250
EOF;
        $salesRep = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);

        foreach ($salesRep as $value) {
            $salesList[] = $value['sales_rep'];
        }

        // Generate the statuses as we cant get them from BaaN
        $statusSO = [
            0 => 'Release Installment Line',
            1 => 'Print Order Ack/Project Required',
            3 => 'Maintain Deliveries',
            4 => 'Print Packing Slips',
            6 => 'Print Sales Invoices',
            7 => 'Closed Sales Order',
        ];

        // Same for PO Type
        $typePO = [
            'PU1' => 'Standard',
            'PN1' => 'Unit',
            'PD1' => 'Direct',
        ];

        $form = new HTML_QuickForm('frmShipAero', 'post');
        $form->addElement('header', 'title', 'Sorting Options:');
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'aeroShippingReport');
        $form->addElement('hidden', 'shipping', 1);
        $form->addElement('select', 'start', 'From', array_combine($datePeriod, $datePeriod));
        $form->addElement('select', 'end', 'To', array_combine($datePeriod, $datePeriod));
        $form->addElement('select', 't_nama', 'Sales Representative', ['' => 'ALL'] + array_combine($salesList, $salesList));
        $form->addElement('text', 'customer', 'Customer Name');
        $form->addElement('text', 't_cuno', 'Customer #');
        $form->addElement('text', 't_eono', 'Customer PO #');
        $form->addElement('text', 'po_number', 'PO #');
        $form->addElement('text', 't_orno', 'Sales Order #');
        $form->addElement('text', 't_clot', 'Serial #');
        $form->addElement('select', 't_ssls', 'Sales Status', ['' => 'ALL'] + $statusSO);
        $form->addElement('checkbox', 'open_sls', 'Only Open Sales Orders');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->setDefaults(['end' => end($datePeriod)]);

        if (!$form->validate()) {
            $body = $form->toHtml();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $vars['customer'] = strtoupper($vars['customer']);
        $vars['open_sls'] = $vars['open_sls'] ? true : false;
        $from = '';
        $until = '';

        // Set the period if Serial# is not specified
        if (empty($vars['t_clot'])) {
            $from = $vars['start'];
            $until = $vars['end'];
        }
        // Check if we want only Open SO
        if (false !== $vars['open_sls']) {
            unset($vars['t_ssls']);
        }
        $salesOrderNb = '';
        unset($vars['m'], $vars['btnSubmit'], $vars['start'], $vars['end']);
        $rows = tldSOL::getSalesOrdersAeroReport($from, $until, $vars);

        $fields = [
            'SO#' => 'Sales Order#',
            'PO#' => 'Purchase Order#',
            'customer_name' => 'Customer Name',
            'order_type' => 'PO Order Type',
            'supp_addr' => 'Supplier Address',
            'supp_city' => 'Supplier City/State',
            'del_addr' => 'Delivery Address',
            'del_city' => 'Delivery City/State',
            'sales_rep' => 'Sales Representative',
            'part#' => 'Part#',
            'project#' => 'Project#',
            'item_desc' => 'Item Description',
            'PO_planned_del_date' => 'PO Planned Delivery Date',
            'warehouse_code' => 'Warehouse Code',
            'warehouse_desc' => 'Warehouse Description',
            'status_code' => 'Status',
            'tracking#' => 'Tracking# and Info',
            'customer_PO#' => 'Customer PO#',
            'files' => 'Files',
            'task' => 'Create Task',
            'task_view' => 'View Task',
        ];
        // For XLS Download
        $_SESSION['data_report'] = $rows;
        $_SESSION['data_fields'] = $fields;

        foreach ($rows as $field => $value) {
            $rows[$field]['status_code'] = $statusSO[$value['status_code']];
            $rows[$field]['order_type'] = $typePO[$value['order_type']];
            $rows[$field]['files'] = 'Link';
            $task = tldTask::byParent($value['SO#'], 'ASO');
            if (!empty($task)) {
                $rows[$field]['task_view'] = 'View task';
                $rows[$field]['task_id'] = $task[0]['id'];
            } else {
                $rows[$field]['task'] = 'Create new task';
            }

        }

        $report = new tldReportColumnar(
            $rows,
            [
                'xItems' => $fields,
                'links' => [
                    'SO#' => [
                        'url' => '/en/private/finance/finance.php?m[0]=so&m[1]=view&erp=250',
                        'params' => ['id' => 'SO#'],
                    ],
                    'item#' => [
                        'url' => '/en/private/parts/parts.php?m[0]=inv&m[1]=view',
                        'params' => ['id' => 'item#'],
                    ],
                    'files' => [
                        'url' => '/en/private/parts/parts.php?_qf__frmSOByNum=&btnSubmit=Submit&erp=250&m[0]=&m[1]=bySearch',
                        'params' => ['orno' => 'SO#'],
                    ],
                    'task' => [
                        'url' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=form&m[2]=newTask',
                        'params' => ['id' => 'SO#'],
                    ],
                    'task_view' => [
                        'url' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view',
                        'params' => ['id' => 'task_id'],
                    ],
                ],
                'title' => 'Sales and Purchase Orders by SO#',
            ]
        );

        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=reports&m[1]=aeroShippingReport&m[2]=xls">Download XLS</a>&nbsp;
EOF;
        $body .= $report->fetch();
        break;
    case 'aeroPurReport':
        if (!$user->isInGroupLevel('gg_ADMIN', 250) && !$user->isInGroupLevel('gg_PARTS', 250)
            && !$user->isInGroupLevel('gg_PUR', 250) && !$user->isInGroupLevel('gg_SALES', 250) && !$user->isInGroup('SUPERUSER')) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permissions to access this page.';
            break;
        }

        switch ($m[2]) {
            case 'xls':
                $data = $_SESSION['data_report'];
                $xItems = array_diff($_SESSION['data_fields'], ['Create Task', 'View Task']);
                $reportXLS = new tldXLS(
                    $data,
                    [
                        'xItems' => $xItems,
                        'showTitles' => true,
                    ]
                );
                $reportXLS->out('AERO_Purchase_Order_Report.xls');
                exit;
                break;
        }

        // Define a date interval
        $begin = new \DateTime('first day of 24 months ago');
        $end = new \DateTime('next month');
        $interval = new DateInterval('P1M');
        $dateInterval = new DatePeriod($begin, $interval, $end);
        foreach ($dateInterval as $period) {
            $datePeriod[] = $period->format('Y-m');
        }

        // Obtain every Sales REP from baan for AERO ERP
        $query = <<<EOF
SELECT t_nama as buyer
FROM ttccom001250
EOF;
        $buyer = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);

        foreach ($buyer as $value) {
            $buyersList[] = $value['buyer'];
        }

        // PO Type
        $typePO = [
            'PU1' => 'Unit',
            'PN1' => 'Standard',
            'PD1' => 'Direct Delivery',
        ];
        // PO Status
        $statusPO = [
            1 => 'Open',
            2 => 'Print Goods Received Note',
            3 => 'Maintain Receipts',
            4 => 'Print Claims',
            5 => 'Maintain Approvals',
            6 => 'Print Storage List',
            7 => 'Print Return Note',
            8 => 'Print Purchase Invoice',
            9 => 'Closed',
        ];
        // Fields for report and XLS Download
        $fields = [
            'PO#' => 'Purchase Order#',
            'pos' => 'Position',
            'order_type' => 'PO Order Type',
            'supplier#' => 'Supplier#',
            'supplier' => 'Supplier Name',
            'supp_addr' => 'Supplier Address',
            'supp_addr2' => 'Supplier Address 2',
            'supp_city' => 'Supplier City/State',
            'warehouse_code' => 'Warehouse Code',
            'price' => 'Price',
            'currency' => 'Currency',
            'ordered_qty' => 'Ordered Qty',
            'del_qty' => 'Delivered Qty',
            'buyer' => 'Buyer',
            'item#' => 'Part#',
            'project#' => 'Project#',
            'item_desc' => 'Item Description',
            'item_group' => 'Item Group',
            'pur_status_code' => 'PO Status',
            'PO_date' => 'PO Date',
            'PO_planned_del_date' => 'Planned Delivery Date',
            'PO_receipt_date' => 'Receipt Date',
            'task' => 'Create Task',
            'task_view' => 'View Task',
        ];

        $form = new HTML_QuickForm('frmShipAero', 'post');
        $form->addElement('header', 'title', 'Sorting Options:');
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'aeroPurReport');
        $form->addElement('hidden', 'purchasing', 1);
        $form->addElement('select', 'start', 'From', array_combine($datePeriod, $datePeriod));
        $form->addElement('select', 'end', 'To', array_combine($datePeriod, $datePeriod));
        $form->addElement('select', 't_nama', 'Buyer', ['' => 'ALL'] + array_combine($buyersList, $buyersList));
        $form->addElement('text', 'supplier', 'Supplier Name');
        $form->addElement('text', 't_suno', 'Supplier #');
        $form->addElement('text', 'po_number', 'PO #');
        $form->addElement('text', 't_item', 'Part #');
        $form->addElement('select', 't_cotp', 'Order Type', ['' => 'ALL'] + $typePO);
        $form->addElement('select', 't_spur', 'Order Status (Only for PO Lines)', ['' => 'ALL'] + $statusPO);
        $form->addElement('checkbox', 'open_po', 'Only Open PO');
        $form->addElement('checkbox', 'details_po', 'Display PO Lines');
        $form->addElement('checkbox', 'spend_report', 'Spend Report (No Statuses)');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->setDefaults(['end' => end($datePeriod)]);

        if (!$form->validate()) {
            $body = $form->toHtml();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $vars['supplier'] = strtoupper($vars['supplier']);
        $vars['open_po'] = $vars['open_po'] ? true : false;
        $vars['details_po'] = ($vars['details_po'] || $vars['spend_report']);
        $vars['spend_report'] = $vars['spend_report'] ? true : false;
        $from = $vars['start'];
        $until = $vars['end'];

        unset($vars['m'], $vars['btnSubmit'], $vars['start'], $vars['end']);
        $rows = tldSOL::getSalesOrdersAeroReport($from, $until, $vars);
        // For XLS Download
        $_SESSION['data_report'] = $rows;
        $_SESSION['data_fields'] = $fields;

        foreach ($rows as $field => $value) {
            $rows[$field]['order_type'] = $typePO[$value['order_type']];
            $rows[$field]['pur_status_code'] = $statusPO[$value['pur_status_code']];
            $task = tldTask::byParent($value['PO#'], 'ASO');
            if (!empty($task)) {
                $rows[$field]['task_view'] = 'View task';
                $rows[$field]['task_id'] = $task[0]['id'];
            } else {
                $rows[$field]['task'] = 'Create new task';
            }
        }

        // Adapt the fields depend if we do not want PO Lines
        if (true !== $vars['details_po']) {
            $fields = [
                'PO#' => 'Purchase Order#',
                'order_type' => 'PO Order Type',
                'supplier#' => 'Supplier#',
                'supplier' => 'Supplier Name',
                'supp_addr' => 'Supplier Address',
                'supp_addr2' => 'Supplier Address 2',
                'supp_city' => 'Supplier City/State',
                'currency' => 'Currency',
                'buyer' => 'Buyer',
                'order_date' => 'PO Date',
                'delivery_date' => 'Delivery Date',
                'PO_receipt_date' => 'Receipt Date',
                'task' => 'Create Task',
                'task_view' => 'View Task',
            ];
        }

        $report = new tldReportColumnar(
            $rows,
            [
                'xItems' => $fields,
                'links' => [
                    'PO#' => [
                        'url' => '/en/private/manufacturing/pur/dev.php?m[0]=po&m[1]=view&erp=250',
                        'params' => ['id' => 'PO#'],
                    ],
                    'item#' => [
                        'url' => '/en/private/parts/parts.php?m[0]=inv&m[1]=view',
                        'params' => ['id' => 'item#'],
                    ],
                    'task' => [
                        'url' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=form&m[2]=newTask',
                        'params' => ['id' => 'PO#'],
                    ],
                    'task_view' => [
                        'url' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view',
                        'params' => ['id' => 'task_id'],
                    ],
                ],
                'title' => 'Purchase Orders by PO#',
            ]
        );

        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=reports&m[1]=aeroPurReport&m[2]=xls">Download XLS</a>&nbsp;
EOF;
        $body .= $report->fetch();
        break;
    case 'aeroBOMReport':
        switch ($m[2]) {
            case 'xls':
                $data = $_SESSION['data_report'];
                $xItems = $_SESSION['data_fields'];
                unset($xItems['drawing']);

                $reportXLS = new tldXLS(
                    $data,
                    [
                        'xItems' => $xItems,
                        'showTitles' => true,
                    ]
                );
                $reportXLS->out('AERO_BOM_Items_Report.xls');
                exit;
                break;
        }

        // Generate the item statuses
        $itemStatus = [
            1 => 'Purchased',
            2 => 'Manufactured',
            3 => 'Generic',
            4 => 'Cost',
            5 => 'Service',
            6 => 'Subcontracting',
        ];

        $form = new HTML_QuickForm('frmBOMAero', 'post');
        $form->addElement('header', 'title', 'Effective Date:');
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'aeroBOMReport');
        $form->addElement('date', 'start', 'Start Date', ['format' => 'Ymd', 'maxYear' => date('Y')]);
        $form->addElement('date', 'end', 'End Date', ['format' => 'Ymd', 'maxYear' => date('Y')]);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->setDefaults(
            [
                'start' => [
                    'd' => '01',
                    'm' => date('m'),
                    'Y' => date('Y'),
                ],
                'end' => [
                    'd' => date('d'),
                    'm' => date('m'),
                    'Y' => date('Y'),
                ],
            ]);

        if (!$form->validate()) {
            $body = $form->toHtml();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $startDate = implode('-', $vars['start']);
        $endDate = implode('-', $vars['end']);
        $rows = tldEDMBOM::aeroBOMAndEDMReport($startDate, $endDate);

        foreach ($rows as $field => $value) {
            $rows[$field]['item_type'] = $itemStatus[$value['item_type']];
            $rows[$field]['drawing'] = '<img src="/shared/bluesphere/16x16/actions/filesaveas.png" alt="Save file to your hard disk"></a>';
        }

        $fields = [
            'bom_item' => 'Item#',
            'item_desc' => 'Item Description',
            'revision' => 'Revision',
            'rev_description' => 'Revision Description',
            'effective_date' => 'Effective Date',
            'item_type' => 'Item Type',
            'drawing' => 'Drawing',
            'user_login' => 'User',
        ];

        $report = new tldReportColumnar(
            $rows,
            [
                'xItems' => $fields,
                'links' => [
                    'bom_item' => [
                        'url' => '/en/private/parts/parts.php?m[0]=inv&m[1]=view',
                        'params' => ['id' => 'bom_item'],
                    ],
                    'drawing' => [
                        'url' => '/en/private/manufacturing/eng/dev.php?m[0]=getfile&m[1]=drawing&erp=250',
                        'params' => [
                            'item' => 'bom_item',
                            'date' => 'effective_date',
                        ],
                    ],
                ],
                'title' => 'BOM Items Details by Effective Date Period',
            ]
        );

        // For XLS Download
        $_SESSION['data_report'] = $rows;
        $_SESSION['data_fields'] = $fields;
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=reports&m[1]=aeroBOMReport&m[2]=xls">Download XLS</a>&nbsp;
EOF;
        $body .= $report->fetch();
        break;
    case 'home':
    default: // @DANGER
        switch ($m[2]) {
            case 'ppv':
                $body = $smarty->fetch("$PATH/reports/ppv.tpl");
                break;
            case 'beta':
                $body = $smarty->fetch("$PATH/reports/beta.tpl");
                break;
            case 'dataset':
                $body = $smarty->fetch("$PATH/reports/dataset.tpl");
                break;
            default:
                $body = $smarty->fetch("$PATH/reports/default.tpl");
                break;
        }
        break;
}

function _listPOLR($rows, $title = '')
{
    foreach ($rows as &$row) {
        $row['t_text'] = htmlentities($row['t_text']);
    }
    $report = new tldReportColumnar($rows,
        [
            'xItems' => [
                't_nama' => 'Buyer',
                'v_nama' => 'Vendor name',
                't_suno' => 'Vendor code',
                't_orno' => 'PO#',
                't_pono' => 'Line #',
                't_pddt' => 'Planned delivery date',
                't_date' => 'Reschedule date',
                'day_diff' => 'Days Diff',
                't_item' => 'Part #',
                't_dsca' => 'Description',
                't_oqan' => 'Order Qty',
                't_cuni' => 'UM',
                't_amta' => 'Amount',
                't_ccur' => 'Currency',
                't_copr' => 'Amount Std Cost',
                't_text' => 'Text Field',
            ],
            'title' => $title,
            'links' => [
                't_orno' => "$php_self?m[0]=po&m[1]=view&erp=" . $rows[0]['erp'] . '&id=',
            ],
        ]
    );
    return $report->fetch();
}


function _listPOLC($rows, $title = '')
{
    foreach ($rows as &$row) {
        $row['t_text'] = htmlentities($row['t_text']);
    }
    $report = new tldReportColumnar($rows,
        [
            'xItems' => [
                't_nama' => 'Buyer',
                't_orno' => 'PO#',
                't_suno' => 'Vendor code',
                't_pono' => 'Line #',
                't_pddt' => 'Planned delivery date',
                't_item' => 'Part #',
                't_dsca' => 'Description',
                't_oqan' => 'Order Qty',
                't_cuni' => 'UM',
                't_amta' => 'Amount',
                't_ccur' => 'Currency',
                't_copr' => 'Amount Std Cost',
                't_text' => 'Text Field',
            ],
            'title' => $title,
            'links' => [
                't_orno' => "$php_self?m[0]=po&m[1]=view&erp=" . $rows[0]['erp'] . '&id=',
            ],
        ]
    );
    return $report->fetch();
}

function _getKPIgraph($GraphURL, $_TITLE, $groupTarget)
{
    global $help;
    $body = <<<EOF
<br><br><img src="$GraphURL"><br/>
EOF;
    $groupTargetText = '<b>Group Target:</b> ' . $groupTarget;
    $popupDef = new tldOverlib(
        $help[$_TITLE] . $groupTargetText,
        [
            'CAPTION' => $_TITLE,
            'WIDTH' => '500',
            'linkName' => $_TITLE,
        ]
    );
    $body .= $popupDef->fetch();
    return $body;
}
