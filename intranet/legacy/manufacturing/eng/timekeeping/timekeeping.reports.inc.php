<?php

use Symfony\Component\HttpFoundation\Request;

$DEFAULT_TITLE .= "\Reports";

switch ($m[2]) {
    case 'meapHours':
    case 'meapHours100-1000':
        $xItemsMeap = [
            'meap_id' => 'MEAP#',
            'ifactor' => 'IF',
            'factory' => 'Factory',
            'status' => 'Phase',
            'hours_actual' => 'Actual Time (in Hours)',
            'meap_short_desc' => 'Short Description',
        ];
        // Get listing
        $factoryList = tldLocation::getFactoryList('smartyOptions');
        $IFList = ['10' => '10', '100' => '100', '1000' => '1000', '10000' => '10000'];
        $status = ['ALL' => 'ALL', 'ALL (OPENED)' => 'ALL (OPENED)', 'GATE_0' => 'GATE_0', 'GATE_1' => 'GATE_1', 'GATE_2' => 'GATE_2', 'GATE_3' => 'GATE_3', 'GATE_4' => 'GATE_4',
            'PHASE_0' => 'PHASE_0', 'PHASE_1' => 'PHASE_1', 'PHASE_2' => 'PHASE_2', 'PHASE_3' => 'PHASE_3', 'PHASE_4' => 'PHASE_4', 'REJECTED' => 'REJECTED', 'CLOSED' => 'CLOSED'];
        //Get form
        $form = new HTML_QuickForm('frmHoursAmount', 'post');
        $form->addElement('hidden', 'm[0]', 'timekeeping');
        $form->addElement('hidden', 'm[1]', 'reports');
        $form->addElement('hidden', 'm[2]', $m[2]);
        $form->addElement('header', 'title', 'MEAP Hours Report - Select filters');
        $form->addElement('select', 'location', 'Company/Location', ['' => '', 'All' => 'All',] + $factoryList);
        $form->addElement('text', 'hours_per_day', 'Hours per day');
        if ($m[2] !== 'meapHours100-1000') {
            $form->addElement('select', 'if', 'Importance Factor', ['' => ''] + $IFList);
        }
        $form->addElement('select', 'status', 'Status', ['' => ''] + $status);
        $form->addElement('date', 'start', 'Date', ['format' => 'Y-m-d', 'addEmptyOption' => false, 'maxYear' => date('Y')]);
        $form->addElement('date', 'end', 'Date', ['format' => 'Y-m-d', 'addEmptyOption' => false, 'maxYear' => date('Y')]);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('location', 'Required', 'required');
        $form->addRule('hours_per_day', 'Required', 'required');
        $form->addRule('status', 'Required', 'required');
        $form->setDefaults(['hours_per_day' => '8', 'start' => date('Y-m-d', strtotime('first day of -3 months')), 'end' => date('Y-m-d', strtotime('last day of last month'))]);

        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }
        // Cleaning data
        $vars = tldUtils::cleanupFormInput($form->exportValues());

        if ($m[2] === 'meapHours100-1000') {
            $vars['if'] = ['100', '1000', '10000'];
        }

        if (!is_numeric($vars['hours_per_day'])) {
            $DEFAULT_ERROR[] = 'ERROR: The Hours per day entry must be numeric.';
            break;
        }
        $start = vsprintf('%1$04d-%2$02d-%3$02d', $vars['start']);
        $end = vsprintf('%1$04d-%2$02d-%3$02d', $vars['end']);
        $data = tldTimekeeping::getMEAPHours($vars['location'], (int)$vars['hours_per_day'], (array) $vars['if'], $vars['status'], $start, $end);
        if ($data) {
            $factory_name = TldLocation::getLocationByERP($vars['location']);
            if ($vars['location'] === 'All') {
                $factory_name = 'All Locations';
                $data[0]['factory'] = 'All';
            }
            if (!in_array((int)$vars['location'], tldTimekeeping::getErpUsingActualHours(), true)) {
                $title_hours = "($hours_per_day hours per day)";
            }
            if ($m[2] === 'meapHours100-1000') {
                $total = array_sum(array_column($data, 'hours_actual'));
                $data = array_map(static function ($meap) use ($total) {
                    $meap['pct'] = round($meap['hours_actual'] * 100 / $total, 2);
                    return $meap;
                }, $data);

                unset($xItemsMeap['meap_short_desc']);
                $xItemsMeap['pct'] = 'Percent';
                $xItemsMeap['meap_short_desc'] = 'Short Description';
            }
            $report = new tldReportColumnar(
                $data,
                [
                    'xItems' => $xItemsMeap,
                    'title' => "MEAP Hours Report for $factory_name - Between $start and $end $title_hours",
                    'links' => ['meap_id' => '/en/private/manufacturing/eng/dev.php?m[0]=meap&m[1]=view&id='],
                    'functions' => [
                        'Timesheets' => [
                            'url' => '/en/private/manufacturing/eng/dev.php?m[0]=timekeeping&m[1]=reports&m[2]=timesheets&link=MEAP&factory=' . $vars['location'] . "&start=$start&end=$end&hours_per_day=$hours_per_day",
                            'param' => ['id' => 'meap_id'],
                        ],
                    ],
                ]
            );
            $body .= $report->fetch();
        } else {
            $body = $form->toHTML();
            $body .= '<br>No records...';
        }
        break;
    case 'HoursAmount':
        // Get listing
        $engList = tldGroup::getUserListByMultipleGroup(['acl_timekeeping'], '', 'smartyOptions');
        $factoryList = tldLocation::getFactoryList('smartyOptions');
        $ModuleList = ['EAP', 'GWF', 'MEAP', 'NCR', 'PDC', 'SB'];
        $ModuleList = array_combine($ModuleList, $ModuleList);
        $xItems = [
            'factory' => 'Company/Location',
            'hours_actual' => 'Total Hours',
        ];
        //Get form
        $form = new HTML_QuickForm('frmHoursAmount', 'post');
        $form->addElement('hidden', 'm[0]', 'timekeeping');
        $form->addElement('hidden', 'm[1]', 'reports');
        $form->addElement('hidden', 'm[2]', 'HoursAmount');
        $form->addElement('header', 'title', 'Select filters');
        $form->addElement('select', 'location', 'Company/Location', ['' => '', 'All' => 'All',] + $factoryList);
        $form->addElement('text', 'hours_per_day', 'Hours per day');
        $form->addElement('select', 'module', 'Module', ['' => ''] + $ModuleList);
        $form->addElement('text', 'module_id', 'Module #ID');
        $form->addElement('select', 'user_id', 'User', ['' => ''] + $engList);
        $form->addElement('date', 'start', 'Date', ['format' => 'Y-m-d', 'addEmptyOption' => false, 'minYear' => date('Y') - 3, 'maxYear' => date('Y')]);
        $form->addElement('date', 'end', 'Date', ['format' => 'Y-m-d', 'addEmptyOption' => false, 'minYear' => date('Y') - 3, 'maxYear' => date('Y')]);
        $form->addElement('submit', 'btnSubmit', 'Submit', ['onclick' => "return confirm('Are you sure of your filters before running this report?');"]);
        $form->addRule('location', 'Required', 'required');
        $form->addRule('hours_per_day', 'Required', 'required');
        $form->setDefaults(['hours_per_day' => '8', 'start' => date('Y-m-d', strtotime('first day of -3 months')), 'end' => date('Y-m-d', strtotime('last day of last month'))]);

        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }
        // Cleaning data
        $vars = tldUtils::cleanupFormInput($form->exportValues());

        if (empty($vars['module']) && !empty($vars['module_id'])) {
            $DEFAULT_ERROR[] = 'ERROR: If the Module #ID entry is filled, you must choose a Module type!';
            break;
        }
        if ($vars['module'] === 'MEAP' && empty($vars['module_id'])) {
            $DEFAULT_ERROR[] = 'ERROR: You cannot run this report at MEAP level without choosing an MEAP #ID!';
            break;
        }
        if (empty($vars['user_id']) && (empty($vars['module']))) {
            $DEFAULT_ERROR[] = 'ERROR: If the report is not run for a specific user, you must choose a Module type!';
            break;
        }
        if (!is_numeric($vars['hours_per_day'])) {
            $DEFAULT_ERROR[] = 'ERROR: The Hours per day entry must be numeric.';
            break;
        }
        $start = vsprintf('%1$04d-%2$02d-%3$02d', $vars['start']);
        $end = vsprintf('%1$04d-%2$02d-%3$02d', $vars['end']);
        $data = tldTimekeeping::getHoursAmount($vars['location'], $vars['hours_per_day'], $vars['module'], $vars['module_id'], $vars['user_id'], $start, $end);
        if ($data) {
            $factory_name = TldLocation::getLocationByERP($vars['location']);
            if ($vars['location'] === 'All') {
                $factory_name = 'All Locations';
                $data[0]['factory'] = 'All';
            }
            if (!in_array((int)$vars['location'], tldTimekeeping::getErpUsingActualHours(), true)) {
                $title_hours = "($hours_per_day hours per day)";
            }
            if (!empty($vars['module']) && !empty($vars['module_id'])) {
                $mod_name = $vars['module'] . '# ' . $vars['module_id'];
                $data[0]['module'] = $mod_name;
                $xItems['module'] = 'Module#';
            }
            if (!empty($vars['module']) && empty($vars['module_id'])) {
                $mod_name = $vars['module'];
                $data[0]['module'] = $mod_name;
                $xItems['module'] = 'Module';
            }
            if (!empty($user_id)) {
                $xItems['user_fullname'] = 'User';
            }
            $report = new tldReportColumnar(
                $data,
                [
                    'xItems' => $xItemsMeap,
                    'title' => "Total Hours for $factory_name - Between $start and $end $title_hours",
                ]
            );
            $body .= $report->fetch();
        } else {
            $body = $form->toHTML();
            $body .= '<br>No records...';
        }
        break;
    case 'perEngineerPerDay':
        $userid = $user_id;
        if (empty($date)) {
            $date = date('Y');
        } else {
            $curdate = $date;
            $date = substr($date, 0, 4);
            // get the user id from the GET parameter
            $userid = strtok($y, ':');
        }
        $engineer = new tldUser($userid);
        if ($x === 'ALL' && ($date !== null || $curdate !== null)) {
            $nbDays = $nbDays ?: 20;
            $date_start = date('Y-m-d', strtotime("-$nbDays days"));
            $date_end = date('Y-m-d');
            $date_title = "Last $nbDays days";
        } elseif ($x === 'ALL') {
            if (!empty($nbDays)) {
                $date_start = date('Y-m-d', strtotime("-$nbDays days"));
                $date_end = date('Y-m-d');
                $date_title = "Last $nbDays days";
            } else {
                $date_start = date('Y-m-d', strtotime($curdate));
                $date_end = date('Y-m-d', strtotime("$date_start +1 month -1 day"));
                $date_title = 'Current Month';
            }
        } elseif (strlen($x) === 5) {
            $date_start = $date_end = $date_title = "$date-" . str_replace('/', '-', $x);
            // Could be a couple day/month from the previous year
            if ($date_start > date('Y-m-d')) {
                $date_start = $date_end = $date_title = str_replace($date, $date-1, $date_start);
            }
        } else {
            $date_start = $date_end = str_replace('/', '-', $x);
            $date_end = str_replace('/', '-', $x);
            $userid = strtok($y, ':');
            $date_title = $date . '-' . $date_start;
        }
        if (!in_array((int)$location, tldTimekeeping::getErpUsingActualHours(), true)) {
            $actual = 'time_actual';
            $actual_desc = 'Time (in % of the day)';
            $forecast = 'time_forecast';
            $forecast_desc = 'Forecasted Time (in % of the day)';
        } else {
            $actual = 'hours_actual';
            $actual_desc = 'Time (in Hours)';
            $forecast = 'hours_forecast';
            $forecast_desc = 'Forecasted Time (in Hours)';
        }
        $engineer_fullname = $engineer->getFullname();
        if ($engineer_fullname === ', ') {
            $engineer_fullname = '';
        }
        $data = tldTimekeeping::perEngineer($userid, $date_start, $date_end, $location);

        if (isset($data)) {

            $request = Request::createFromGlobals();
            $currentUrl = $request->getRequestUri();

            $DEFAULT_MENU .= <<<EOF
            <br>
	        <a href="$currentUrl&m[3]=csv">Download CSV</a>  /  <a href="$currentUrl&m[3]=xls">Download XLS</a>
EOF;

            $xItems = [
                'id' => 'Timesheet#',
                'man_location' => 'Location',
                'user_fullname' => 'User',
                'dt_open' => 'Date',
                'category' => 'Category',
                'product_type' => 'Product Type',
                'project' => 'Project Type',
                'link' => 'Link Type',
                'module_id' => 'Module ID#',
                $actual => $actual_desc,
                $forecast => $forecast_desc,
                'comment' => 'Comment',
            ];

            switch ($m[3]) {
                case 'csv':
                    (new tldCSV(
                        $data,
                        [
                            'xItems' => $xItems,
                            'showTitles' => true,
                        ]
                    ))->out('timesheets_listing_report.csv');
                    exit;
                case 'xls':
                    $report = new tldXLS(
                        $data,
                        [
                            'xItems' => $xItems,
                            'showTitles' => true,
                        ]
                    );
                    $report->out('timesheets_listing_report.xls');
                    exit;
            }

            $report = new tldReportColumnar(
                $data,
                [
                    'xItems' => $xItems,
                    'title' => "Timesheets listing for $engineer_fullname - $date_title",
                    'links' => ['id' => "$php_self?m[0]=timekeeping&m[1]=view&id="],
                ]
            );
        }
        $body = $report->fetch();
        break;
    case 'perEngineer':
        if (!$user->isInGroup(['gg_ADMIN', 'role_ES', 'role_EM', 'role_FC'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permission to access this module';
            return;
        }
        switch ($m[3]) {
            case 'fullCSV':
                if (!in_array((int)$location, tldTimekeeping::getErpUsingActualHours(), true)) {
                    $actual = 'time_actual';
                    $actual_desc = 'Time (in % of the day)';
                    $forecast = 'time_forecast';
                    $forecast_desc = 'Forecasted Time (in % of the day)';
                } else {
                    $actual = 'hours_actual';
                    $actual_desc = 'Time (in Hours)';
                    $forecast = 'hours_forecast';
                    $forecast_desc = 'Forecasted Time (in Hours)';
                }
                $data = tldTimekeeping::perEngineer($user_id, $date_start, $date_end, $location);
                $report = new tldCSV(
                    $data,
                    [
                        'xItems' => [
                            'id' => 'Timesheet#',
                            'man_location' => 'Location',
                            'user_fullname' => 'User',
                            'dt_open' => 'Date',
                            'category' => 'Category',
                            'product_type' => 'Product Type',
                            'project' => 'Project Type',
                            'link' => 'Link Type',
                            'module_id' => 'Module ID#',
                            $actual => $actual_desc,
                            $forecast => $forecast_desc,
                            'comment' => 'Comment',
                        ],
                        'showTitles' => true,
                    ]
                );
                $report->out('timekeeping_report.csv');
                exit;
                break;
            case 'xls':
                if (!in_array((int)$location, tldTimekeeping::getErpUsingActualHours(), true)) {
                    $actual = 'time_actual';
                    $actual_desc = 'Time (in % of the day)';
                    $forecast = 'time_forecast';
                    $forecast_desc = 'Forecasted Time (in % of the day)';
                } else {
                    $actual = 'hours_actual';
                    $actual_desc = 'Time (in Hours)';
                    $forecast = 'hours_forecast';
                    $forecast_desc = 'Forecasted Time (in Hours)';
                }
                $data = tldTimekeeping::perEngineer($user_id, $date_start, $date_end, $location);
                $report = new tldXLS(
                    $data,
                    [
                        'xItems' => [
                            'id' => 'Timesheet#',
                            'man_location' => 'Location',
                            'user_fullname' => 'User',
                            'dt_open' => 'Date',
                            'category' => 'Category',
                            'product_type' => 'Product Type',
                            'project' => 'Project Type',
                            'link' => 'Link Type',
                            'module_id' => 'Module ID#',
                            $actual => $actual_desc,
                            $forecast => $forecast_desc,
                            'comment' => 'Comment',
                        ],
                        'showTitles' => true,
                    ]
                );
                $report->out('timekeeping_report.xls');
                exit;
                break;
        }
        // Get listing
        $engList = tldGroup::getUserListByMultipleGroup(['acl_timekeeping'], '', 'smartyOptions');
        //Get form
        $form = new HTML_QuickForm('frmPerEngineer', 'post');
        $form->addElement('hidden', 'm[0]', 'timekeeping');
        $form->addElement('hidden', 'm[1]', 'reports');
        $form->addElement('hidden', 'm[2]', 'perEngineer');
        $form->addElement('header', 'title', 'Select filters');
        $form->addElement('select', 'user_id', 'User', ['' => ''] + $engList);
        $form->addElement('date', 'date_start', 'Date', ['format' => 'Y-m-d', 'addEmptyOption' => false, 'minYear' => date('Y') - 3, 'maxYear' => date('Y')]);
        $form->addElement('date', 'date_end', 'Date', ['format' => 'Y-m-d', 'addEmptyOption' => false, 'minYear' => date('Y') - 3, 'maxYear' => date('Y')]);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('user_id', 'Required', 'required');
        $form->setDefaults(['date_start' => date('Y-m-d', strtotime('first day of -3 months')), 'date_end' => date('Y-m-d', strtotime('last day of last month'))]);
        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $date_start = vsprintf('%1$04d-%2$02d-%3$02d', $vars['date_start']);
        $date_end = vsprintf('%1$04d-%2$02d-%3$02d', $vars['date_end']);
        $engineer = new tldUser($vars['user_id']);
        $engineer_BUID = $engineer->getBUID();
        $engineer_fullname = $engineer->getFullname();
        $location = tldLocation::getERPByID($engineer_BUID);
        if (!in_array((int)$location, tldTimekeeping::getErpUsingActualHours(), true)) {
            $actual = 'time_actual';
            $actual_desc = 'Time (in % of the day)';
            $forecast = 'time_forecast';
            $forecast_desc = 'Forecasted Time (in % of the day)';
        } else {
            $actual = 'hours_actual';
            $actual_desc = 'Time (in Hours)';
            $forecast = 'hours_forecast';
            $forecast_desc = 'Forecasted Time (in Hours)';
        }
        $data = tldTimekeeping::perEngineer($user_id, $date_start, $date_end, $location);
        foreach ($data as &$row) {
            $row['url'] = $row['module_id'];
            if ($row['category'] === 'Engineering Task' && (int)$row['module_id'] !== 0) {
                $row['url'] = getModuleLink($row['link'], $row['module_id']);
            }
        }
        unset($row);
        if (isset($data)) {
            $report = new tldReportColumnar(
                $data,
                [
                    'xItems' => [
                        'id' => 'Timesheet#',
                        'man_location' => 'Location',
                        'user_fullname' => 'User',
                        'dt_open' => 'Date',
                        'category' => 'Category',
                        'product_type' => 'Product Type',
                        'project' => 'Project Type',
                        'link' => 'Link Type',
                        'url' => 'Module ID#',
                        $actual => $actual_desc,
                        $forecast => $forecast_desc,
                        'comment' => 'Comment',
                    ],

                    'title' => "Timesheets listing for $engineer_fullname - Between $date_start and $date_end",
                    'links' => ['id' => "$php_self?m[0]=timekeeping&m[1]=view&id="],
                ]
            );
            $DEFAULT_MENU .= <<<EOF
        <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <a href="$php_self?m[0]=timekeeping&m[1]=reports&m[2]=perEngineer&m[3]=xls&location=$location&user_id=$user_id&date_start=$date_start&date_end=$date_end">Download XLS</a>&nbsp;|&nbsp;
        <a href="$php_self?m[0]=timekeeping&m[1]=reports&m[2]=perEngineer&m[3]=fullCSV&location=$location&user_id=$user_id&date_start=$date_start&date_end=$date_end">Download CSV</a>
EOF;
    $body .= $report->fetch();
}
        break;
    case 'perSupervisor':
        if (!$user->isInGroup(['gg_ADMIN', 'role_ES', 'role_EM', 'role_FC'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permission to access this module';
            return;
        }
        switch ($m[3]) {
            case 'fullCSV':
                if (!in_array((int)$location, tldTimekeeping::getErpUsingActualHours(), true)) {
                    $actual = 'time_actual';
                    $actual_desc = 'Time (in % of the day)';
                    $forecast = 'time_forecast';
                    $forecast_desc = 'Forecasted Time (in % of the day)';
                } else {
                    $actual = 'hours_actual';
                    $actual_desc = 'Time (in Hours)';
                    $forecast = 'hours_forecast';
                    $forecast_desc = 'Forecasted Time (in Hours)';
                }
                $dataFull = [];
                $supervisor = new tldUser($user_id);
                $subordinates = $supervisor->getSubordinates('smartyOptions');
                foreach ($subordinates as $engID => $engName) {
                    $data = tldTimekeeping::perEngineer($engID, $date_start, $date_end, $location);
                    if (is_array($data)) {
                        array_splice($dataFull, count($dataFull), 0, $data);
                    }
                }
                $report = new tldCSV(
                    $dataFull,
                    [
                        'xItems' => [
                            'id' => 'Timesheet#',
                            'man_location' => 'Location',
                            'user_fullname' => 'User',
                            'dt_open' => 'Date',
                            'category' => 'Category',
                            'product_type' => 'Product Type',
                            'project' => 'Project Type',
                            'link' => 'Link Type',
                            'module_id' => 'Module ID#',
                            $actual => $actual_desc,
                            $forecast => $forecast_desc,
                            'comment' => 'Comment',
                        ],
                        'showTitles' => true,
                    ]
                );
                $report->out('timekeeping_report.csv');
                exit;
                break;
            case 'xls':
                if (!in_array((int)$location, tldTimekeeping::getErpUsingActualHours(), true)) {
                    $actual = 'time_actual';
                    $actual_desc = 'Time (in % of the day)';
                    $forecast = 'time_forecast';
                    $forecast_desc = 'Forecasted Time (in % of the day)';
                } else {
                    $actual = 'hours_actual';
                    $actual_desc = 'Time (in Hours)';
                    $forecast = 'hours_forecast';
                    $forecast_desc = 'Forecasted Time (in Hours)';
                }
                $dataFull = [];
                $supervisor = new tldUser($user_id);
                $subordinates = $supervisor->getSubordinates('smartyOptions');
                foreach ($subordinates as $engID => $engName) {
                    $data = tldTimekeeping::perEngineer($engID, $date_start, $date_end, $location);
                    if (is_array($data)) {
                        array_splice($dataFull, count($dataFull), 0, $data);
                    }
                }
                $report = new tldXLS(
                    $dataFull,
                    [
                        'xItems' => [
                            'id' => 'Timesheet#',
                            'man_location' => 'Location',
                            'user_fullname' => 'User',
                            'dt_open' => 'Date',
                            'category' => 'Category',
                            'product_type' => 'Product Type',
                            'project' => 'Project Type',
                            'link' => 'Link Type',
                            'module_id' => 'Module ID#',
                            $actual => $actual_desc,
                            $forecast => $forecast_desc,
                            'comment' => 'Comment',
                        ],
                        'showTitles' => true,
                    ]
                );
                $report->out('timekeeping_report.xls');
                exit;
                break;
        }
        // Get listing
        $supervisorList = [];
        // Fetch people with position: Engineering Manager, Senior Expert Engineer, Product Line Engineer
        $unOrdersupervisor = tldUser::byFunctions([18, 60, 61]);
        foreach ($unOrdersupervisor as $supervisor) {
            $supervisorList[$supervisor['id']] = $supervisor['fullname'];
        }
        //Get form
        $form = new HTML_QuickForm('frmPerSupervisor', 'post');
        $form->addElement('hidden', 'm[0]', 'timekeeping');
        $form->addElement('hidden', 'm[1]', 'reports');
        $form->addElement('hidden', 'm[2]', 'perSupervisor');
        $form->addElement('header', 'title', 'Select filters');
        $form->addElement('select', 'user_id', 'Supervisor', ['' => ''] + $supervisorList);
        $form->addElement('date', 'date_start', 'Date', ['format' => 'Y-m-d', 'addEmptyOption' => false, 'minYear' => date('Y') - 3, 'maxYear' => date('Y')]);
        $form->addElement('date', 'date_end', 'Date', ['format' => 'Y-m-d', 'addEmptyOption' => false, 'minYear' => date('Y') - 3, 'maxYear' => date('Y')]);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('user_id', 'Required', 'required');
        $form->setDefaults(['date_start' => date('Y-m-d', strtotime('yesterday')), 'date_end' => date('Y-m-d', strtotime('yesterday'))]);
        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $date_start = vsprintf('%1$04d-%2$02d-%3$02d', $vars['date_start']);
        $date_end = vsprintf('%1$04d-%2$02d-%3$02d', $vars['date_end']);
        $dataFull = [];
        $supervisor = new tldUser($vars['user_id']);
        $supervisor_fullname = $supervisor->getFullname();
        $subordinates = $supervisor->getSubordinates('smartyOptions');
        $engineer_BUID = $supervisor->getBUID();
        $location = tldLocation::getERPByID($engineer_BUID);
        if (!in_array((int)$location, tldTimekeeping::getErpUsingActualHours(), true)) {
            $actual = 'time_actual';
            $actual_desc = 'Time (in % of the day)';
            $forecast = 'time_forecast';
            $forecast_desc = 'Forecasted Time (in % of the day)';
        } else {
            $actual = 'hours_actual';
            $actual_desc = 'Time (in Hours)';
            $forecast = 'hours_forecast';
            $forecast_desc = 'Forecasted Time (in Hours)';
        }
        foreach ($subordinates as $engID => $engName) {
            //Start loop
            $data = tldTimekeeping::perEngineer($engID, $date_start, $date_end, $location);
            foreach ($data as &$row) {
                $row['url'] = $row['module_id'];
                if ($row['category'] === 'Engineering Task' && (int)$row['module_id'] !== 0) {
                    $row['url'] = getModuleLink($row['link'], $row['module_id']);
                }
            }
            unset($row);
            if (is_array($data)) {
                array_splice($dataFull, count($dataFull), 0, $data);
            }
        }
        // END LOOP
        if (isset($dataFull)) {
            $report = new tldReportColumnar(
                $dataFull,
                [
                    'xItems' => [
                        'id' => 'Timesheet#',
                        'man_location' => 'Location',
                        'user_fullname' => 'User',
                        'dt_open' => 'Date',
                        'category' => 'Category',
                        'product_type' => 'Product Type',
                        'project' => 'Project Type',
                        'link' => 'Link Type',
                        'url' => 'Module ID#',
                        $actual => $actual_desc,
                        $forecast => $forecast_desc,
                        'comment' => 'Comment',
                    ],
                    'title' => "Team Members Timesheets listing for $supervisor_fullname - Between $date_start and $date_end",
                    'links' => ['id' => "$php_self?m[0]=timekeeping&m[1]=view&id="],
                ]
            );
            $DEFAULT_MENU .= <<<EOF
        <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <a href="$php_self?m[0]=timekeeping&m[1]=reports&m[2]=perSupervisor&m[3]=xls&location=$location&user_id=$user_id&date_start=$date_start&date_end=$date_end">Download XLS</a>&nbsp;|&nbsp;
        <a href="$php_self?m[0]=timekeeping&m[1]=reports&m[2]=perSupervisor&m[3]=fullCSV&location=$location&user_id=$user_id&date_start=$date_start&date_end=$date_end">Download CSV</a>
EOF;
    $body .= $report->fetch();
}
        break;
    case 'perDay':
        if (!$user->isInGroup(['gg_ADMIN', 'role_ES', 'role_EM', 'acl_timekeeping'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permission to access this module';
            return;
        }
        (!$user->isInGroup(['role_ES', 'role_EM'])) ? $userid = $user->getID() : $userid = '';
        //Get listing
        $factoryList = tldLocation::getFactoryList('smartyOptions');
        //Get form
        $form = new HTML_QuickForm('frmFullReport', 'post');
        $form->addElement('hidden', 'm[0]', 'timekeeping');
        $form->addElement('hidden', 'm[1]', 'reports');
        $form->addElement('hidden', 'm[2]', 'perDay');
        $form->addElement('header', 'title', 'Select filters');
        $form->addElement('select', 'location', 'Company/Location', ['' => ''] + $factoryList);
        $form->addElement('date', 'date', 'Date', ['format' => 'Y-m', 'addEmptyOption' => false, 'minYear' => date('Y') - 3, 'maxYear' => date('Y')]);
        $form->addElement('text', 'hours_per_day', 'Hours per day');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('location', 'Required', 'required');
        $form->setDefaults(['hours_per_day' => '8', 'date' => date('Y-m')]);
        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $date = vsprintf('%1$04d-%2$02d', $vars['date']);
        $location = $vars['location'];
        if (!is_numeric($vars['hours_per_day'])) {
            $DEFAULT_ERROR[] = 'ERROR: The Hours per day entry must be numeric.';
            break;
        }
        $data = tldTimekeeping::perDay($date, $location, $hours_per_day, 'ENG', $userid);
        if (isset($data)) {
            $reportSchedule = new tldMatrix(
                $data,
                'dt_open', 'user_fullname', 'hours_actual',
                "$php_self?m[0]=timekeeping&m[1]=reports&m[2]=perEngineerPerDay&user_id=$ID_DASH&location=$location&date=$date",
                "<br>Engineers Schedule - $date",
                ['decimals' => 'true']
            );

            $body .= $reportSchedule->fetch();
        }

        break;
    case 'fullReport':
        if (!$user->isInGroup(['gg_ADMIN', 'role_ES', 'role_EM', 'role_FC', 'gg_ACCT'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permission to access this module';
            return;
        }
        switch ($m[3]) {
            case 'fullCSV':
                if (!in_array((int)$location, tldTimekeeping::getErpUsingActualHours(), true)) {
                    $actual = 'time_actual_calcul';
                    $actual_desc = 'Time (in Hours)';
                    $forecast = 'time_forecast_calcul';
                    $forecast_desc = 'Forecasted Time (in Hours)';
                } else {
                    $actual = 'hours_actual';
                    $actual_desc = 'Time (in Hours)';
                    $forecast = 'hours_forecast';
                    $forecast_desc = 'Forecasted Time (in Hours)';
                }
                $data = $_SESSION['data_report'];
                $report = new tldCSV(
                    $data,
                    [
                        'xItems' => [
                            'id' => 'ID#',
                            'man_location' => 'Location',
                            'dt_open' => 'Date',
                            'user_fullname' => 'User',
                            'category' => 'Category',
                            'product_type' => 'Product Type',
                            'meap' => 'MEAP#',
                            'meap_model' => 'MEAP Model',
                            'project' => 'Project Type',
                            'module' => 'Module ID#',
                            'task_id' => 'Task#',
                            'link' => 'Link type',
                            $actual => $actual_desc,
                            $forecast => $forecast_desc,
                            'comment' => 'Comment',
                            'tasks_desc' => 'Task Description',
                        ],
                        'showTitles' => true,
                    ]
                );
                $report->out('timekeeping_report.csv');
                exit;
                break;
            case 'xls':
                if (!in_array((int)$location, tldTimekeeping::getErpUsingActualHours(), true)) {
                    $actual = 'time_actual_calcul';
                    $actual_desc = 'Time (in Hours)';
                    $forecast = 'time_forecast_calcul';
                    $forecast_desc = 'Forecasted Time (in Hours)';
                } else {
                    $actual = 'hours_actual';
                    $actual_desc = 'Time (in Hours)';
                    $forecast = 'hours_forecast';
                    $forecast_desc = 'Forecasted Time (in Hours)';
                }
                $data = $_SESSION['data_report'];
                $report = new tldXLS(
                    $data,
                    [
                        'xItems' => [
                            'id' => 'ID#',
                            'man_location' => 'Location',
                            'dt_open' => 'Date',
                            'user_fullname' => 'User',
                            'category' => 'Category',
                            'product_type' => 'Product Type',
                            'meap' => 'MEAP#',
                            'meap_model' => 'MEAP Model',
                            'project' => 'Project Type',
                            'module' => 'Module ID#',
                            'task_id' => 'Task#',
                            'link' => 'Link type',
                            $actual => $actual_desc,
                            $forecast => $forecast_desc,
                            'comment' => 'Comment',
                            'tasks_desc' => 'Task Description',
                        ],
                        'showTitles' => true,
                    ]
                );
                $report->out('timekeeping_report.xls');
                exit;
                break;
        }
        //Get listing
        $factoryList = tldLocation::getFactoryList('smartyOptions');
        //Get form
        $form = new HTML_QuickForm('frmFullReport', 'post');
        $form->addElement('hidden', 'm[0]', 'timekeeping');
        $form->addElement('hidden', 'm[1]', 'reports');
        $form->addElement('hidden', 'm[2]', 'fullReport');
        $form->addElement('header', 'title', 'Select filters (limited to 3 months)');
        $form->addElement('select', 'location', 'Company/Location', ['' => ''] + $factoryList);
        $form->addElement('date', 'start', 'Start Date', ['format' => 'Y-m-d', 'addEmptyOption' => false, 'minYear' => date('Y') - 3, 'maxYear' => date('Y')]);
        $form->addElement('date', 'end', 'End Date', ['format' => 'Y-m-d', 'addEmptyOption' => false, 'minYear' => date('Y') - 3, 'maxYear' => date('Y')]);
        $form->addElement('text', 'hours_per_day', 'Hours per day');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('location', 'Required', 'required');
        $form->setDefaults(['hours_per_day' => '8', 'start' => date('Y-m-d', strtotime('first day of -3 months')), 'end' => date('Y-m-d', strtotime('last day of last month'))]);
        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $start = vsprintf('%1$04d-%2$02d-%3$02d', $vars['start']);
        $ustart = strtotime($start);
        $end = vsprintf('%1$04d-%2$02d-%3$02d', $vars['end']);
        $uend = strtotime($end);
        if (($uend - $ustart) > ($uend - strtotime('-6 months', $uend))) {
            $DEFAULT_ERROR[] = 'Date range limited to six months max, please change your date selection';
            $body = $form->toHTML();
            break;
        }
        $location = $vars['location'];
        if (!in_array((int)$location, tldTimekeeping::getErpUsingActualHours(), true)) {
            $actual = 'time_actual_calcul';
            $actual_desc = 'Time (in Hours)';
            $forecast = 'time_forecast_calcul';
            $forecast_desc = 'Forecasted Time (in Hours)';
        } else {
            $actual = 'hours_actual';
            $actual_desc = 'Time (in Hours)';
            $forecast = 'hours_forecast';
            $forecast_desc = 'Forecasted Time (in Hours)';
        }
        if (!is_numeric($vars['hours_per_day'])) {
            $DEFAULT_ERROR[] = 'ERROR: The Hours per day entry must be numeric.';
            break;
        }
        $hours_per_day = (int)$vars['hours_per_day'];
        $data = tldTimekeeping::fullReportENG($start, $end, $location, $hours_per_day);
        if (isset($_SESSION['data_report'])) {
            unset($_SESSION['data_report']);
        }
        $_SESSION['data_report'] = $data;
        if (isset($data)) {
            $report = new tldReportColumnar(
                $data,
                [
                    'xItems' => [
                        'id' => 'ID#',
                        'man_location' => 'Location',
                        'dt_open' => 'Date',
                        'user_fullname' => 'User',
                        'category' => 'Category',
                        'product_type' => 'Product Type',
                        'meap' => 'MEAP#',
                        'meap_model' => 'MEAP Model',
                        'project' => 'Project Type',
                        'module' => 'Module ID#',
                        'task_id' => 'Task#',
                        'link' => 'Link type',
                        $actual => $actual_desc,
                        $forecast => $forecast_desc,
                        'comment' => 'Comment',
                        'tasks_desc' => 'Task Description',
                    ],
                    'title' => "Full listing for $location - Between $start and $end ($hours_per_day hours per day)",
                    'links' => ['id' => "$php_self?m[0]=timekeeping&m[1]=view&id="],
                ]
            );
            $DEFAULT_MENU .= <<<EOF
        <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <a href="$php_self?m[0]=timekeeping&m[1]=reports&m[2]=fullReport&m[3]=xls&location=$location">Download XLS</a>&nbsp;|&nbsp;
        <a href="$php_self?m[0]=timekeeping&m[1]=reports&m[2]=fullReport&m[3]=fullCSV&location=$location">Download CSV</a>
EOF;
    $body .= $report->fetch();
}
        break;
    case 'timesheets':
        if (empty($id) || empty($link)) {
            $DEFAULT_ERROR[] = 'ERROR: Module ID or Link type not set...';
            break;
        }
        $DEFAULT_MENU .= <<<EOF
        <br>
        <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <a href="$php_self?m[0]=timekeeping&m[1]=reports&m[2]=timesheets&m[3]=csv&link=$link&factory=$factory&start=$start&end=$end&id=$id&hours_per_day=$hours_per_day">Download CSV</a>
EOF;
        $xItems = [
            'id' => 'Timesheet#',
            'man_location' => 'Location',
            'user_fullname' => 'User',
            'dt_open' => 'Date',
            'category' => 'Category',
            'product_type' => 'Product Type',
            'project' => 'Project Type',
            'link' => 'Link Type',
            'module_id' => 'Module#',
            'tasks_id' => 'Tasks#',
            'time_actual' => 'Actual Time (in % of the day)',
            'time_forecast' => 'Forecasted Time (in % of the day)',
            'hours_actual' => 'Actual Time (in Hours)',
            'hours_forecast' => 'Forecasted Time (in Hours)',
            'comment' => 'Comment',
        ];
        if ($link === 'MEAP') {
            $rows = tldTimekeeping::getMEAPTimesheets($factory, $id, $start, $end);
            $title = " - Between $start and $end";
        } else {
            $rows = tldTimekeeping::byConstraints("(module_id LIKE $id AND link LIKE '$link') OR eap LIKE $id");
        }
        foreach ($rows as &$row) {
            if ('Task' === $row['link']) {
                $row['module_id'] = $row['tasks_parent_id'] ?? '';
            }

            if ($hours_per_day !== '' && $hours_per_day !== '0' && null !== $hours_per_day) {
                if ("0" === $row['time_actual']) {
                    $row['time_actual'] = $row['hours_actual'] * 100 / $hours_per_day;
                }
            }
        }

        unset($row);

        switch ($m[3]) {
            case 'csv':
                (new tldCSV(
                    $rows,
                    [
                        'xItems' => $xItems,
                        'showTitles' => true,
                    ]
                ))->out('timekeeping_report.csv');
                exit;
        }
        if (isset($rows)) {
            $body .= (new tldReportColumnar(
                $rows,
                [
                    'xItems' => $xItems,
                    'title' => "Timesheets linked to $link#$id $title",
                    'links' => [
                        'id' => '/en/private/manufacturing/eng/dev.php?m[0]=timekeeping&m[1]=view&id=',
                        'tasks_id' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=',
                    ],
                ]
            ))->fetch();
        }
        break;
    case 'meapTimekeeping':
        //Get form
        $form = new HTML_QuickForm('meapTimekeeping', 'post');
        $form->addElement('hidden', 'm[0]', 'timekeeping');
        $form->addElement('hidden', 'm[1]', 'reports');
        $form->addElement('hidden', 'm[2]', $m[2]);
        $form->addElement('header', 'title', 'MEAP timekeeping split per period Report - Select filters');
        $form->addElement('text', 'meapId', 'MEAP #ID');
        $form->addElement('date', 'start', 'Date', ['format' => 'Y-m-d', 'addEmptyOption' => false, 'maxYear' => date('Y')]);
        $form->addElement('date', 'end', 'Date', ['format' => 'Y-m-d', 'addEmptyOption' => false, 'maxYear' => date('Y')]);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('status', 'Required', 'required');
        $form->addRule('start', 'Required', 'required');
        $form->addRule('end', 'Required', 'required');
        $form->setDefaults(['start' => date('Y-m-d', strtotime('first day of -3 months')), 'end' => date('Y-m-d', strtotime('last day of last month'))]);


        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }
        // Cleaning data
        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $start = vsprintf('%1$04d-%2$02d-%3$02d', $vars['start']);
        $end = vsprintf('%1$04d-%2$02d-%3$02d', $vars['end']);

        $meap = new tldMEAP($vars['meapId']);
        $pcds = $meap->getPCD($meap->getId(), 'MEAP PHASE');
        $initialised = false;
        $lastLoop = false;
        $data = [];
        foreach($pcds as $key => $pcd) {
            if ($pcd['actual_date'] !== null && $pcd['actual_date'] < $start ) {
                continue;
            }

            $startDate = $start;
            if ($initialised === true) {
                $startDate = (new DateTime($pcds[$key - 1]['actual_date']))->modify('+1 day')->format('Y-m-d');
            }

            $endDate = $end;
            if ($pcd['actual_date'] !== null && $pcd['actual_date'] < $end) {
                $endDate = $pcd['actual_date'];
            } else {
                $lastLoop = true;
            }

            $result = tldTimekeeping::getMEAPHoursDetail($startDate, $endDate, $vars['meapId'], substr($pcd['description'], 0, 7));
            $result[0]['startDate'] = $startDate;
            $result[count($result) - 1]['closedDate']= $endDate;
            $data[] = $result;

            $initialised = true;

            if ($lastLoop) {
                break;
            }
        }

        $report = new tldReportColumnar(
            array_merge_recursive(...$data),
            [
                'xItems' => [
                    'meap_id' => 'MEAP#',
                    'phase' => 'Phase',
                    'OpenDate' => 'Period',
                    'hours_actual' => 'Actual Time (in Hours)',
                ],
                'title' => "MEAP Hours Detailed Report for MEAP ".$vars['meapId'],
                'links' => ['meap_id' => '/en/private/manufacturing/eng/dev.php?m[0]=meap&m[1]=view&id='],
                'functions' => [
                    'Timesheets' => [
                        'url' => '/en/private/manufacturing/eng/dev.php?m[0]=timekeeping&m[1]=reports&m[2]=timesheets&link=MEAP&factory=All&hours_per_day=8',
                        'param' => ['id' => 'meap_id', 'start' => 'startDate', 'end' => 'closedDate'],
                    ],
                ],
            ]
        );
        $body .= $report->fetch();

        break;
    default:
        $body = $smarty->fetch("$PATH/timekeeping/reports/homepage.reports.tpl");
        break;
}
