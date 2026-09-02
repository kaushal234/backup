<?php
$PATH .= "/reports";
$DEFAULT_TITLE .= "\Reports";

switch($m[1]) {
    case 'aeroSalesReport':
        // Allow Alvest, AERO and MIS
        if ('5' !== $user->itsDetails['bu_id'] && '45' !== $user->itsDetails['bu_id'] && '98' !== $user->itsDetails['bu_id'] && !$user->isInGroupLevel('gg_ADMIN',900)
        && !$user->isInGroup('SUPERUSER')) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permissions to access this page.';
            break;
        }

        switch($m[2]) {
            case 'xls':
                $data = $_SESSION['data_report'];
                $xItems = array_diff($_SESSION['data_fields'], ['SO ACK', 'Invoice', 'Create Task', 'View Task']);

                $reportXLS = new tldXLS(
                    $data,
                    [
                        'xItems' => $xItems,
                        'showTitles' => true
                    ]
                );
                $reportXLS->out('AERO_Sales_Order_Report.xls');
                exit;
                break;
        }

        // Define a date interval
        $begin = new \DateTime('first day of 12 months ago');
        $end = new \DateTime('+ 2 months');
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
        $salesRep = tldUtils::getSqlToAssocArray($query, "odbc", ["src" => "baan"]);

        foreach ($salesRep as $value) {
            $salesList[] = $value['sales_rep'];
        }

        // Generate the statuses as we cant get them from BaaN
        $statusSO = [
            0 => "Release Installment Line",
            1 => "Print Order Ack/Project Required",
            3 => "Maintain Deliveries",
            4 => "Print Packing Slips",
            6 => "Print Sales Invoices",
            7 => "Closed Sales Order"
        ];
        // Fields for display report and XLS Download
        $xItems = [
            'sales_rep' => 'Sales Representative',
            'customer_name' => 'Customer Name',
            'customer#' => 'Customer Number',
            'SO#' => 'Sales Order #',
            'customer_PO#' => 'Customer PO#',
            'ordered_qty' => 'Ordered Quantity',
            'item#' => 'Item #',
            'item_desc' => 'Item Description',
            'status_code' => 'Status',
            'project#' => 'Project#',
            'price' => 'Price',
            'currency' => 'Currency',
            'SO_date' => 'Sales Order Date',
            'SO_del_date' => 'Sales Order Delivery Date',
            'SO_plan_recp_date' => 'Sales Order Planned Receipt Date',
            'days_past_due' => 'Days Past Due',
            'soack' => 'SO ACK',
            'invoice' => 'Invoice',
            'task' => 'Create Task',
            'task_view' => 'View Task',
        ];

        $form = new HTML_QuickForm('frmSlsAero', 'post');
        $form->addElement('header', 'title', 'Sorting Options:');
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'aeroSalesReport');
        $form->addElement('select', 'start', 'From', array_combine($datePeriod, $datePeriod));
        $form->addElement('select', 'end', 'To', array_combine($datePeriod, $datePeriod));
        $form->addElement('select', 't_nama', 'Sales Representative', ["" => "ALL"] + array_combine($salesList, $salesList));
        $form->addElement('text', 'customer', 'Customer Name');
        $form->addElement('text', 't_cuno', 'Customer #');
        $form->addElement('text', 't_item', 'Part #');
        $form->addElement('text', 't_eono', 'Customer PO#');
        $form->addElement('select', 't_ssls', 'Sales Status', ["" => "ALL"] + $statusSO);
        $form->addElement('text', 't_orno', 'Sales Order #');
        $form->addElement('text', 't_clot', 'Serial #');
        $form->addElement('checkbox', 'open_sls', 'Only Open Sales Orders');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->setDefaults(['end' => end($datePeriod)]);

        if (!$form->validate()) {
            $body = $form->toHtml();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $vars['open_sls'] = $vars['open_sls'] ? true : false;
        $vars['customer'] = strtoupper($vars['customer']);
        $from = '';
        $until = '';
        $salesOrderNb = '';

        // Set the period if Serial# is not specified
        if (empty($vars['t_clot'])) {
            $from = $vars['start'];
            $until = $vars['end'];
        }
        // Check if we want only Open SO
        if (false !== $vars['open_sls']) {
            unset($vars['t_ssls']);
        }
        $from = $vars['start'];
        $until = $vars['end'];
        unset($vars['m'], $vars['btnSubmit'], $vars['start'], $vars['end']);
        $rows = tldSOL::getSalesOrdersAeroReport($from, $until, $vars);
        // For XLS Download
        $_SESSION['data_report'] = $rows;
        $_SESSION['data_fields'] = $xItems;
        // Get the SO ACK Data
        $arch = new tldArchive(250);

        foreach ($rows as $field => $value) {
            if (7 === $value['status_code']) {
                $rows[$field]['days_past_due'] = 0;
            }
            $rows[$field]['status_code'] = $statusSO[$value['status_code']];
            $rows[$field]['soack'] = "PDF";
            $rows[$field]['invoice'] = "Link";
            $task = tldTask::byParent($value['SO#'], 'ASO', 'ALL');
            if (!empty($task)) {
                $rows[$field]['task_view'] = "View task";
                $rows[$field]['task_id'] = $task[0]['id'];
            } else {
                $rows[$field]['task'] = "Create new task";
            }
            $soAckData = $arch->byTypeID("SALES ORDER ACK", $value['SO#']);
            $rows[$field]['so_pdf_url'] = $soAckData[0]['filepath'];
        }

        $report = new tldReportColumnar(
            $rows,
            [
                'xItems' => $xItems,
                'links' => [
                    'SO#' => [
                        'url' => '/en/private/finance/finance.php?m[0]=so&m[1]=view&erp=250',
                        'params' => ['id' => 'SO#']
                    ],
                    'item#' => [
                        'url' => '/en/private/parts/parts.php?m[0]=inv&m[1]=view',
                        'params' => ['id' => 'item#']
                    ],
                    'soack' => [
                        'url' => "/en/private/strs_pdf/archive/$salesOrderNb",
                        'vars' => [$salesOrderNb => 'so_pdf_url'],
                    ],
                    'invoice' => [
                        'url' => '/en/private/parts/parts.php?_qf__frmSOByNum=&btnSubmit=Submit&erp=250&m[0]=&m[1]=bySearch',
                        'params' => ['orno' => 'SO#']
                    ],
                    'task' => [
                        'url' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=form&m[2]=newTask&module=ASO',
                        'params' => ['parent_id' => 'SO#']
                    ],
                    'task_view' => [
                        'url' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view',
                        'params' => ['id' => 'task_id']
                    ],
                ],
                'title' => 'Sales Orders by Sales Representative',
            ]
        );

        $DEFAULT_MENU .=<<<EOF
	<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="$php_self?m[0]=reports&m[1]=aeroSalesReport&m[2]=xls">Download XLS</a>&nbsp;
EOF;
        $body .= $report->fetch();
    break;
default:

    // Display link for Alvest, AERO and MIS
    if ('5' === $user->itsDetails['bu_id'] || '45' === $user->itsDetails['bu_id'] || '98' === $user->itsDetails['bu_id'] || $user->isInGroupLevel('gg_ADMIN',900)
    || $user->isInGroup('SUPERUSER')) {
        $aeroProfile = true;
        $smarty->assign("aero", $aeroProfile);
    }
    $body = $smarty->fetch("$PATH/homepage.reports.tpl");
break;
}
?>
