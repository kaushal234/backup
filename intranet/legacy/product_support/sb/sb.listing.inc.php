<?php
$DEFAULT_TITLE .= "\Listing";

// Default SB Listing
$xItems = [
    'id' => 'SB#',
    'dt' => 'Date',
    'poster_fullname' => 'Poster',
    'factory' => 'Factory',
    'status' => 'Status',
    'category' => 'Category',
    'confidential' => 'Confidential?',
    'ifactor' => 'IF',
    'title' => 'Title',
    'factory_part_availability_status' => 'Factory parts availability'
];

switch ($m[2]) {
    case 'lines':
        // Default SB Lines Listing
        $xItems = [
            'parent_id' => 'SB#',
            'sb_title' => 'Title',
            'sb_status' => 'Status',
            'category' => 'Category',
            'labor' => 'Labors (in Min)',
            'id' => 'Line#',
            'sn' => 'SN#',
            'cust_asset_num' => 'Asset #',
            'model' => 'Model',
            'apc_code' => 'APC',
            'apc_country_name' => 'APC Country',
            'buyer_customer_name' => 'BUYER customer',
            'user_customer_name' => 'USER customer',
            'man_location' => 'Factory',
            'sso_name' => 'SSO',
            'dt_implementation' => 'SB implementation',
            'status' => 'ISI',
        ];
        $options = [
            'links' => [
                'parent_id' => "$php_self?m[0]=sb&m[1]=view&id=",
                'sn' => [
                    'url' => '/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view',
                    'params' => ['id' => 'er_id'],
                    'target' => '_blank',
                ],
            ],
        ];

        switch ($m[3]) {
            case 'search':
                // Listing
                $customerList = tldCustomer::getList('smartyOptionsCust_name');
                // Form
                $form = new HTML_QuickForm('frmNew');
                $form->addElement('hidden', 'm[0]', 'sb');
                $form->addElement('hidden', 'm[1]', 'listing');
                $form->addElement('hidden', 'm[2]', 'lines');
                $form->addElement('hidden', 'm[3]', 'search');
                $form->addElement('header', 'headform1', 'Search');
                $form->addElement('select', 'buyer_customer_name', 'BUYER customer', ['' => ''] + $customerList);
                $form->addElement('select', 'user_customer_name', 'USER customer', ['' => ''] + $customerList);
                $form->addElement('submit', 'btnSubmit', 'Submit');

                if (!$form->validate()) {
                    $body .= $form->toHTML();
                    return;
                }

                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $constraints = [];
                $fields = ['buyer_customer_name' => 'buyer_customer.customer_name', 'user_customer_name' => 'user_customer.customer_name'];
                foreach ($fields as $field => $column) {
                    if (empty($vars[$field])) {
                        continue;
                    }
                    $constraints[] = "$column LIKE '{$vars[$field]}'";
                }
                if (!count($constraints)) {
                    $DEFAULT_ERROR[] = 'ERROR: Could not search, not enough constraint set.';
                    return;
                }
                $rows = tldSB_Line::byConstraints('1=1', ['where' => implode(' AND ', $constraints)]);
                $_title = 'SB Lines results';
                break;
            case 'sbSsdDecisionByCategoryDecisionBySSO':
                // Check SSO
                if (empty($_REQUEST['ssoid']) || !is_numeric($_REQUEST['ssoid'])) {
                    $DEFAULT_ERROR[] = 'ERROR: Parameters sent empty or invalid';
                    break 2;
                }
                $sso = new tldLocation($_REQUEST['ssoid']);
                // Constraints
                $a = ['sb.status' => 'SSD_DECISION', 'er.sales_org' => $sso->getShortName()];
                $category = TldDatabase::escape($_REQUEST['y']);
                $decision = TldDatabase::escape($_REQUEST['x']);
                // Report data
                unset($xItems['status'], $xItems['dt_implementation']);
                $xItems['ssd_decision_status'] = 'Decision';
                $_title = "ER by SB Category $category by SSD decision $decision, SSO " . $sso->getShortName();
                $rows = tldSB_Line::byCategoryDecisionByConstraints($category, $decision, $a);
                break;
            case 'lineTldToImplementByCategoryTechBySSO':
                // Check SSO
                if (empty($_REQUEST['ssoid']) || !is_numeric($_REQUEST['ssoid'])) {
                    $DEFAULT_ERROR[] = 'ERROR: Parameters sent empty or invalid';
                    break 2;
                }
                $sso = new tldLocation($_REQUEST['ssoid']);
                // Constraints
                $a = ['sb_lines.status' => 'TLD_TO_IMPLEMENT', 'er.sales_org' => $sso->getShortName()];
                $category = TldDatabase::escape($_REQUEST['x']);
                $tech = TldDatabase::escape($_REQUEST['y']);
                // Report data
                $xItems['csr_tech_fullname'] = 'Technician';
                $xItems['csr_id'] = 'CSR#';
                $options['links']['csr_id'] = [
                    'url' => '/en/private/sales_service/service.php?m[0]=csr&m[1]=view',
                    'params' => ['id' => 'csr_id'],
                    'target' => '_blank',
                ];
                $_title = "ER TLD_TO_IMPLEMENT by SB Category $category by Technician $tech, SSO " . $sso->getShortName();
                $rows = tldSB_Line::byCategoryTechByConstraints($category, $tech, $a);
                break;
            case 'sbImplementationByStatusByCategory':
                $xItems ['warranty_end_date'] = 'Warranty End Date';
                $xItems['part_decision'] = 'TLD Decision Parts';
                $xItems['service_decision'] = 'TLD Decision Service';
                $xItems['csr_id'] = 'CSR#';
                $xItems['csr_status'] = 'CSR Status';
                $options['links']['csr_id'] = [
                    'url' => '/en/private/sales_service/service.php?m[0]=csr&m[1]=view',
                    'params' => ['id' => 'csr_id'],
                    'target' => '_blank',
                ];
                $a = ['sb.status' => '%IMPLEMENTATION'];
                $status = TldDatabase::escape($_REQUEST['x']);
                $category = TldDatabase::escape($_REQUEST['y']);
                $_title = "SB (PARTIAL) IMPLEMENTATION, ER by SB Category $category By ISI $status";
                if (isset($m[4]) && $m[4] === 'bySSO') {
                    if (empty($_REQUEST['ssoid']) || !is_numeric($_REQUEST['ssoid'])) {
                        $DEFAULT_ERROR[] = 'ERROR: Parameters sent empty or invalid';
                        break 2;
                    }
                    $sso = new tldLocation($_REQUEST['ssoid']);
                    $_title .= ', SSO ' . $sso->getShortName();
                    $a['er.sso_service'] = $sso->getShortName();
                }
                if ($status === 'TLD_TO_SHIP'){
                    $xItems['spr_id'] = 'SPR#';
                    $xItems['spr_status'] = 'SPR Status';
                    $options = [
                        'links' => $options['links'] + [
                            'spr_id' => "/en/private/parts/parts.php?m[0]=spr&m[1]=view&id="
                        ]
                    ];
                }

                $rows = tldSB_Line::byStatusByCategoryByConstraints($status, $category, $a);
                break;
            case 'sbImplementationLaborByStatusAPCBySSO':
                // Check SSO
                if (empty($_REQUEST['ssoid']) || !is_numeric($_REQUEST['ssoid'])) {
                    $DEFAULT_ERROR[] = 'ERROR: Parameters sent empty or invalid';
                    break 2;
                }
                $sso = new tldLocation($_REQUEST['ssoid']);
                // Constraints
                $a = [
                    'sb.status' => '%IMPLEMENTATION',
                    'er.sso_service' => $sso->getShortName(),
                ];
                $status = TldDatabase::escape($_REQUEST['x']);
                $apc = TldDatabase::escape($_REQUEST['y']);
                $_title = "SB (PARTIAL) IMPLEMENTATION, ER labors by APC $apc by ISI $status, SSO " . $sso->getShortName();
                $rows = tldSB_Line::byEstimatedOperationHoursByStatusAPCByConstraints($status, $apc, $a);
                break;
        }
        break;
    case 'awaitingSignatureByCategoryStatusBySSO':
        $status = TldDatabase::escape($_REQUEST['x']);
        $category = TldDatabase::escape($_REQUEST['y']);
        // Check SSO
        if (empty($_REQUEST['ssoid']) || !is_numeric($_REQUEST['ssoid'])) {
            $DEFAULT_ERROR[] = 'ERROR: Parameters sent empty or invalid';
            break;
        }
        $sso = new tldLocation($_REQUEST['ssoid']);
        // Constraints
        $a = <<<EOF
sb.id IN(SELECT parent_id FROM sb_signature WHERE sso_id={$sso->getID()} AND user_id=0)
EOF;
        // Report
        $_title = "SB awaiting signature by Category $category By Status $status";
        $rows = tldSB3::byAwaitingSignatureByCategoryStatusByConstraints($category, $status, $a);
        foreach ($rows as &$row) {
            $row['days_until_autosign'] = tldSB3::getDaysUntilAutosign($row);
        }
        unset($row);
        $xItems['days_until_autosign'] = 'Days until Auto-sign';
        break;
    case 'byCategoryStatus':
        $a = null;
        $status = TldDatabase::escape($_REQUEST['x']);
        $category = TldDatabase::escape($_REQUEST['y']);
        $_title = "SB by Category $category By Status $status";
        if (isset($m[3]) && $m[3] === 'byFactory') {
            if (empty($_REQUEST['buid']) || !is_numeric($_REQUEST['buid'])) {
                $DEFAULT_ERROR[] = 'ERROR: Parameters sent empty or invalid';
                break;
            }
            $bu = new tldLocation($_REQUEST['buid']);
            $_title .= ', Factory ' . $bu->getShortName();
            $a = ['sb.bu_id' => $bu->itsID];
        }
        $rows = tldSB3::byCategoryStatusByConstraints($category, $status, $a);
        break;
    case 'byFactoryStatus':
        $status = TldDatabase::escape($_REQUEST['x']);
        $factory = TldDatabase::escape($_REQUEST['y']);
        $rows = tldSB3::byFactoryStatusByConstraints($factory, $status);
        $_title = 'Search results';
        break;
    case 'bySBDate':
        $data = $_GET;
        $type = $data['y'];
        $decision = $data['decision'];

        if ($decision === 'CSM_APPROVAL') {
            $decision = 'CSM APPROVAL';
            $key = 'dt_ssd_approval';
        } else {
            $decision = 'SSD DECISION';
            $key = 'dt_ssd_decision';
        }
        $a = [];
        if ($data['factory'] !== 'ALL') {
            $a[] = " sb.bu_id = '{$data['factory']}'";
        }
        if ($data['sso'] !== 'ALL') {
            $a[] = " service.sso_service = '{$data['sso']}'";
        }
        if ($data['x'] === 'ALL') {
            if ($data['from']) {
                $a[] = " sb.$key >= '{$data['from']}'";
            }
            if ($data['to']) {
                $a[] = " sb.$key <= '{$data['to']}'";
            }
        } else {
            $a[] = " DATE_FORMAT(sb.$key, '%Y-%m') = '{$data['x']}'";
        }
        $rows = tldSB3::getSBByDate($a, $decision, $type);
        $xItems = [
            'id' => 'SB#',
            'description' => 'Description',
            'status' => 'Status',
            $key => $decision,
        ];
        if ($type === 'Total Compulsory SB Lines') {
            $xItems = [
                'id' => 'SB#',
                'description' => 'Description',
                'status' => 'Status',
                $key => $decision,
                'lineId' => 'SB Line#',
                'statusLine' => 'Status Line',
                'er_id' => 'ER#',
                'customer_name' => 'Customer name',
            ];
        }
        $options = [
            'links' => [
                'id' => "$php_self?m[0]=sb&m[1]=view&id=",
                'er_id' => "$php_self?m[0]=equipment&m[1]=view&id=",
            ],
        ];
        $_title = $type;
        break;
    case 'search':
        $form = new HTML_QuickForm('frmNew');
        $form->addElement('hidden', 'm[0]', 'sb');
        $form->addElement('hidden', 'm[1]', 'listing');
        $form->addElement('hidden', 'm[2]', 'search');
        $form->addElement('header', 'headform', 'Search');
        $form->addElement('text', 'keyword', 'Keyword');
        $form->addElement('header', 'headform1', 'Filters');
        $form->addElement('select', 'status', 'Status', ['' => ''] + tldSB3::getStatusList());
        $form->addElement('select', 'poster_id', 'Poster', ['' => ''] + tldDirectory::getUserlist('smartyOptions'));
        $form->addElement('select', 'bu_id', 'Factory', ['' => ''] + tldLocation::getFactoryList('smartyOptionsIDLocation'));
        $form->addElement('select', 'sso_service', 'SSO', ['' => ''] + tldLocation::getSalesOrgList('smartyOptionsLocationLocation'));
        $form->addElement('select', 'category', 'Category', ['' => ''] + tldSB3::getCategoryList());
        $form->addElement('select', 'type', 'Type', ['' => ''] + tldSB3::getTypeList());
        $form->addElement('select', 'ifactor', 'IFactor', ['' => ''] + tldSB3::getIFactorList());
        $form->addElement('select', 'confidential', 'Confidential?', ['' => '', 'Y' => 'Y', 'N' => 'N']);
        $form->addElement('select', 'factory_part_availability_status', 'Factory parts availability', ['' => ''] + tldSB3::getPartsAvailabilityChoicesList());
        $form->addElement('text', 'title', 'Title');
        $form->addElement('select', 'customer', 'Customer', ['' => ''] + tldCustomer::getList('smartyOptions'));
        $form->addElement('select', 'airport_code', 'Airport', ['' => ''] + tldAirport::getList());
        $form->addElement('select', 'del_ctry', 'Country', ['' => ''] + tldCountry::optionsAsNameName());
        $form->addElement('select', 'ertype', 'Unit Type', ['' => ''] + tldType::getList('smartyOptions_Name'));
        $form->addElement('select', 'model', 'Model', ['' => ''] + tldModel::getList());
        $form->addElement('submit', 'btnSubmit', 'Submit');

        if (!$form->validate()) {
            $body .= $form->toHTML();
            return;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $a = [];
        // 1. keyword
        if (!empty($vars['keyword'])) {
            $keyword = $vars['keyword'];
            $a[] = "(sb.title LIKE '%$keyword%' OR sb.description LIKE '%$keyword%')";
        }
        // 2. filters
        $searchFields = ['status', 'poster_id', 'bu_id', 'category', 'type', 'ifactor', 'confidential', 'title', 'customer', 'airport_code', 'sso_service', 'del_ctry', 'ertype', 'factory_part_availability_status', 'model'];
        foreach ($searchFields as $searchField) {
            if (empty($vars[$searchField])) {
                continue;
            }
            $fieldName = $searchField;
            if ('ertype' === $fieldName) {
                $fieldName = 'type';
            }
            switch ($searchField) {
                case 'title':
                    $a[] = "$searchField LIKE '%{$vars[$searchField]}%'";
                    break;
                case 'customer':
                    $a[] = "(SELECT COUNT(DISTINCT sb_lines.parent_id) FROM sb_lines INNER JOIN service er ON er.id=er_id WHERE sb_lines.parent_id=sb.id AND (er.customer_id ={$vars[$searchField]} OR er.buyer_customer_id={$vars[$searchField]}) )";
                    break;
                case 'airport_code':
                case 'model':
                case 'sso_service':
                case 'del_ctry':
                case 'ertype':
                    $a[] = "(SELECT COUNT(DISTINCT sb_lines.parent_id) FROM sb_lines INNER JOIN service er ON er.id=er_id WHERE sb_lines.parent_id=sb.id AND er.$fieldName ='{$vars[$searchField]}' )";
                    break;
                default:
                    $a[] = "sb.$searchField='{$vars[$searchField]}'";
                    break;
            }
        }

        // Check if search is safe
        if (!$a) {
            $DEFAULT_ERROR[] = 'ERROR: Not enough constraints set to make a safe search';
            $body .= $form->toHTML();
            return;
        }

        $rows = tldSB3::byConstraints(implode(' AND ', $a));
        $_title = 'Search results';
        break;
    case 'factoryIsiCount':
        // Form
        $form = new HTML_QuickForm('frmFactoryIsiCount'); //, 'post', '', '', null, true);
        $form->addElement('hidden', 'm[0]', 'sb');
        $form->addElement('hidden', 'm[1]', 'listing');
        $form->addElement('hidden', 'm[2]', 'factoryIsiCount');
        $form->addElement('header', 'formHeader', 'Select a Factory');
        $form->addElement('select', 'factory', 'Factory', ['' => ''] + tldLocation::getFactoryList('smartyOptionsLocationLocation'));
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('factory', 'You must select a factory.', 'required');

        if (!$form->validate()) {
            $body .= $form->toHtml();
            return;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());

        $a = ['sb.status' => '%IMPLEMENTATION'];
        $title = 'ER Count by ISI by SB';

        if ($vars['factory'] !== '') {
            $a['er.man_location'] = $vars['factory'];
            $title .= " for factory {$vars['factory']}";
        }

        $rows = tldSB3::countLinesPerISIImplementation($a);
        $xItems = [
            'id' => 'SB#',
            'dt' => 'Date',
            'dt_implementation' => 'Implementation Date',
            'poster' => 'Poster',
            'category' => 'Category',
            'confidential' => 'Conf.',
            'ifactor' => 'IF',
            'title' => 'Title',
            'description' => 'Description',
            'TLD_TO_NOTIFY' => 'TLD_TO_NOTIFY',
            'CUSTOMER_TO_DECIDE' => 'CUSTOMER_TO_DECIDE',
            'TLD_TO_SHIP' => 'TLD_TO_SHIP',
            'TLD_TO_IMPLEMENT' => 'TLD_TO_IMPLEMENT',
            'CLOSED' => 'CLOSED',
            'total' => 'Total',
        ];
        $report = new tldReportColumnar(
            $rows,
            [
                'xItems' => $xItems,
                'title' => $title,
                'showzero' => true,
                'showNumberOfRows' => true,
                'links' => ['id' => '/en/private/product_support/index.ps.php?m[0]=sb&m[1]=view&id='],
            ]
        );

        $body .= $report->fetch();
        break;
}


// ------------------------------
//             OUTPUT
// ------------------------------

switch ($out) {
    case 'xls':
        $report = new tldXLS(
            $sess['sb']['listing'],
            [
                'xItems' => $sess['sb']['xItems'],
                'showTitles' => true,
            ]
        );
        $report->out();
        exit;
    default:
        if (count($rows) < 1) {
            $DEFAULT_ERROR[] = 'ERROR: No SB found...';
            break;
        }
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sb&m[1]=listing&out=xls">XLS version</a>
EOF;
        $sess['sb']['listing'] = $rows;
        $sess['sb']['xItems'] = $xItems;
        $body .= _getListing($rows, $_title, $xItems, $options);
        break;
}
