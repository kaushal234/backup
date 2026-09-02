<?php
// List of vars
$USER_DASH_HTML = null;
$GLOBAL_DASH_HTML = null;

// ------------------------------------------------
//             DASHBOARD SELECTION
// ------------------------------------------------

$USER_DASH_TITLE = null;
$USER_DASH_ACL = [
    'Factory Dashboard' => ['role_PSM', 'role_PSE', 'role_PSA', 'role_COO'],
    'Service Dashboard' => ['role_CSM', 'role_CSA', 'role_AST'],
    'SSO Dashboard' => ['role_EVP', 'role_CEO'],
    'Group Dashboard' => ['role_COO', 'role_CSM', 'role_GTD'],
];
// Get allowed dashboards following user profile
$USER_DASH_ALLOWED = [];
foreach ($USER_DASH_ACL as $dashboardName => $permissions) {
    if ($dashboardName === 'Group Dashboard') {
        foreach ($permissions as $permission) {
            if (!$user->isInGroupLevel($permission, 900)) {
                continue;
            }
            $USER_DASH_ALLOWED[] = $dashboardName;
        }
        if ($user->isInGroup(['superuser'])) {
            $USER_DASH_ALLOWED[] = $dashboardName;
        }

        continue;
    }

    if (!$user->isInGroup($permissions)) {
        continue;
    }
    $USER_DASH_ALLOWED[] = $dashboardName;

}
// Default dashboard applied to the user
$USER_DASH_DEFAULT = end($USER_DASH_ALLOWED);
// Look if there is no dashboard requested
$USER_DASH_CURRENT = null;
if (!empty($_REQUEST['dashboard_type'])) {
    if (!in_array($_REQUEST['dashboard_type'], $USER_DASH_ALLOWED, true)) {
        $DEFAULT_ERROR[] = 'ERROR: You do not have permission to access this dashboard.';
        return;
    }
    $sess['sb']['dash'] = $_REQUEST['dashboard_type'];
}
// Check session
if (!empty($sess['sb']['dash'])) {
    $USER_DASH_CURRENT = $sess['sb']['dash'];
} else {
    $USER_DASH_CURRENT = $USER_DASH_DEFAULT;
    $sess['sb']['dash'] = $USER_DASH_CURRENT;
}

// ------------------------------------------------
//                 USER BU SELECTION
// ------------------------------------------------

// Get default BU and listing --->

switch ($USER_DASH_CURRENT) {
    case 'Factory Dashboard':
        $USER_BU_LIST = tldLocation::getFactoryList('smartyOptionsIDLocation');
        $USER_BU_DEFAULT = $user->getBUID();
        break;
    case 'Service Dashboard':
    case 'SSO Dashboard':
        $USER_BU_DEFAULT = $user->getBUID();
        $USER_BU_LIST = tldLocation::getSalesOrgList('smartyOptionsIDLocation');
        break;
    case 'Group Dashboard':
        $USER_BU_DEFAULT = 'ALL';
        $USER_BU_LIST = ['ALL' => 'ALL'] + tldLocation::getSalesOrgList('smartyOptionsIDLocation');
        break;
    default:
        $DEFAULT_ERROR[] = 'ERROR: Can not determine BU from your profile';
        break;
}
if (!array_key_exists($USER_BU_DEFAULT, $USER_BU_LIST)) {
    $USER_BU_DEFAULT = current(array_keys($USER_BU_LIST));
}

// BU switch request --->

$USER_BU_CURRENT = null;
if (!empty($_REQUEST['buid'])) {
    if (!array_key_exists($_REQUEST['buid'], $USER_BU_LIST)) {
        $DEFAULT_ERROR[] = 'ERROR: BU not allowed for this dashboard';
        return;
    }
    $sess['sb']['buid'] = $_REQUEST['buid'];
}

// Assign BU --->

// Look session
if (empty($sess['sb']['buid']) || !array_key_exists($sess['sb']['buid'], $USER_BU_LIST)) {
    $sess['sb']['buid'] = $USER_BU_DEFAULT;
}
// Assign current BU
if ($sess['sb']['buid'] !== 'ALL') {
    $USER_BU_CURRENT = new tldLocation(TldDatabase::escape($sess['sb']['buid']));
    $USER_BU_CURRENT_NAME = $USER_BU_CURRENT->getShortName();
} elseif ($sess['sb']['buid'] === 'ALL') {
    $USER_BU_CURRENT_NAME = 'ALL';
} else {
    $DEFAULT_ERROR[] = 'ERROR: Can not determine dashboard & BU from your profile';
    return;
}

// Location swith --->

$frmSwitch = new HTML_QuickForm('frmSwitch', 'post', '', '', ['id' => 'frmSwitch']);
$frmSwitch->addElement('hidden', 'm[0]', 'sb');
$frmSwitch->addElement('header', 'header', 'Location Switch');
$frmSwitch->addElement('select', 'buid', 'Location', ['' => ''] + $USER_BU_LIST, ['onChange' => "javascript:$('#frmSwitch').submit();"]);

// ---------------------------------------------------------
//                 USER DASHBOARD CONTENTS
// ---------------------------------------------------------

switch ($USER_DASH_CURRENT) {
    case 'Factory Dashboard':

        // Matrix "SB by Status by Category" for my Factory

        $a = ['sb.bu_id' => TldDatabase::escape($USER_BU_CURRENT->getID())];

        $matrix = new tldMatrix(
            tldSB3::countByCategoryStatusByConstraints($a),
            'status', 'category', 'num',
            "$php_self?m[0]=sb&m[1]=listing&m[2]=byCategoryStatus&m[3]=byFactory&buid=" . $USER_BU_CURRENT->getID(),
            'SB Count by Status, by Category, Factory ' . $USER_BU_CURRENT->getShortName(),
            [
                'xItems' => tldSB3::getStatusList(),
                'yItems' => tldSB3::getCategoryList(),
            ]
        );
        $USER_DASH_HTML .= $matrix->fetch();

        // Metrics below -> Maybe in report section instead...
        // Matrix "SB with Late Signature - By SSO by Status" for my Factory
        // statistique AVG time - CSM_APPROVAL + ER_SELECTION

        break;
    case 'Service Dashboard':

        // Matrix "IMPLEMENTATION - ER Count by ISI by SB Category" for my SSO

        // SB awaiting signature (CSM_APPROVAL) for my SSO
        $a = [
            'sgn.sso_id' =>$sess['sb']['buid'],
        ];
        $a = tldUtils::cleanupFormInput($a);

        $matrix = new tldMatrix(
            tldSB3::countAwaitingSignatureByCategoryStatusByConstraints($a),
            'status', 'category', 'num',
            "$php_self?m[0]=sb&m[1]=listing&m[2]=awaitingSignatureByCategoryStatusBySSO&ssoid={$sess['sb']['buid']}",
            "SB awaiting signature for $USER_BU_CURRENT_NAME, by status by category",
            [
                'yItems' => tldSB3::getCategoryList(),
            ]
        );
        $USER_DASH_HTML .= $matrix->fetch();

        $a = [
            'er.sso_service' => $USER_BU_CURRENT_NAME,
            'sb.status' => '%IMPLEMENTATION',
        ];
        $a = tldUtils::cleanupFormInput($a);
        $partialImplementationSBLines = tldSB_Line::countByStatusByCategoryByConstraints($a);
        $matrix = new tldMatrix(
            tldSB_Line::countByStatusByCategoryByConstraints($a),
            'status', 'category', 'num',
            "$php_self?m[0]=sb&m[1]=listing&m[2]=lines&m[3]=sbImplementationByStatusByCategory&m[4]=bySSO&ssoid={$sess['sb']['buid']}",
            "SB (PARTIAL) IMPLEMENTATION, ER Count by ISI, by SB Category in $USER_BU_CURRENT_NAME",
            [
                'xItems' => tldSB_Line::getStatusList(),
                'yItems' => tldSB3::getCategoryList(),
            ]
        );
        $USER_DASH_HTML .= $matrix->fetch();

        // SB (PARTIAL) IMPLEMENTATION, ER Count By OPEN ISI
        $partialImplementationSBOpenLines = array_filter($partialImplementationSBLines, function ($item) {
            return $item['status'] !== "" && $item['status'] !== "CLOSED";
        });

        $totals = [
            "COMPULSORY" => 0,
            "RECOMMENDED" => 0,
            "INFORMATION" => 0,
        ];

        foreach ($partialImplementationSBOpenLines as $item) {
            if (isset($totals[$item['category']])) {
                $totals[$item['category']] += (int) $item['num'];
            }
        }

        foreach ($totals as $category => $total) {
            $partialImplementationSBOpenLines[] = [
                "status" => "TOTAL_OPEN",
                "category" => $category,
                "num" => $total,
            ];
        }

        $matrix = new tldMatrix(
            $partialImplementationSBOpenLines,
            'status', 'category', 'num',
            "$php_self?m[0]=sb&m[1]=listing&m[2]=lines&m[3]=sbImplementationByStatusByCategory&m[4]=bySSO&ssoid={$sess['sb']['buid']}",
            "SB (PARTIAL) IMPLEMENTATION, ER Count By OPEN ISI, by SB Category in $USER_BU_CURRENT_NAME",
            [
                'xItems' => array_merge(tldSB_Line::getOpenStatusList(), ['TOTAL_OPEN' => 'TOTAL_OPEN']),
                'yItems' => tldSB3::getCategoryList(),
                'doNotShowYTotals' => true
            ]
        );
        $USER_DASH_HTML .= $matrix->fetch();


        // Stat "Total Estimated Hours of operation"
        $matrix = new tldMatrix(
            tldSB_Line::countEstimatedOperationHoursByStatusByCategoryByConstraints($a),
            'status', 'category', 'num',
            "$php_self?m[0]=sb&m[1]=listing&m[2]=lines&m[3]=sbImplementationByStatusByCategory&m[4]=bySSO&ssoid={$sess['sb']['buid']}",
            "SB (PARTIAL) IMPLEMENTATION, Estimated Hours of operation by ISI, by SB Category in $USER_BU_CURRENT_NAME",
            [
                'xItems' => tldSB_Line::getStatusList(),
                'yItems' => tldSB3::getCategoryList(),
                'decimals' => true,
            ]
        );
        $USER_DASH_HTML .= $matrix->fetch();
        $USER_DASH_HTML .= <<<EOF
<p><i>Note for Estimated Hours of operation report:<br>ER with service decision equal to 4 and service customer response equal to N or ? are excluded</i></p>
<p><a href="$php_self?m[0]=sb&m[1]=reports&m[2]=sbImplementationLaborByStatusAPCBySSO&ssoid={$sess['sb']['buid']}">Click here to get Estimated Hours of operation report by APC</a></p>
EOF;

        // Matrix ER count TLD_TO_IMPLEMENT by SB Category by tech
        $a = [
            'er.sso_service' => $USER_BU_CURRENT_NAME,
            'sb_lines.status' => 'TLD_TO_IMPLEMENT',
        ];
        $a = tldUtils::cleanupFormInput($a);

        $matrix = new tldMatrix(
            tldSB_Line::countByCategoryTechByConstraints($a),
            'category', 'csr_tech_fullname', 'num',
            "$php_self?m[0]=sb&m[1]=listing&m[2]=lines&m[3]=lineTldToImplementByCategoryTechBySSO&ssoid={$sess['sb']['buid']}",
            'ER count TLD_TO_IMPLEMENT by SB Category by Technician',
            [
                'xItems' => tldSB3::getCategoryList(),
            ]
        );
        $USER_DASH_HTML .= $matrix->fetch();
        break;
    case 'SSO Dashboard':

        // SB awaiting your signature (CSM_APPROVAL + SSD_DECISION) for my SSO

        $a = ['sgn.sso_id' => $sess['sb']['buid']];
        $a = tldUtils::cleanupFormInput($a);

        $matrix = new tldMatrix(
            tldSB3::countAwaitingSignatureByCategoryStatusByConstraints($a),
            'status', 'category', 'num',
            "$php_self?m[0]=sb&m[1]=listing&m[2]=awaitingSignatureByCategoryStatusBySSO&ssoid={$sess['sb']['buid']}",
            "SB awaiting signature for $USER_BU_CURRENT_NAME, by status by category",
            [
                'yItems' => tldSB3::getCategoryList(),
            ]
        );
        $USER_DASH_HTML .= $matrix->fetch();

        // Matrix "SB SSD_DECISION - ER Count by decision By category" for my SSO
        $a = [
            'er.sso_service' => $USER_BU_CURRENT_NAME,
            'sb.status' => 'SSD_DECISION',
        ];
        $a = tldUtils::cleanupFormInput($a);

        $matrix = new tldMatrix(
            tldSB_Line::countByCategoryDecisionByConstraints($a),
            'ssd_decision_status', 'category', 'num',
            "$php_self?m[0]=sb&m[1]=listing&m[2]=lines&m[3]=sbSsdDecisionByCategoryDecisionBySSO&ssoid={$sess['sb']['buid']}",
            "SB SSD_DECISION, ER Count by decision by category for $USER_BU_CURRENT_NAME",
            [
                'yItems' => tldSB3::getCategoryList(),
            ]
        );
        $USER_DASH_HTML .= $matrix->fetch();

        // Matrix "IMPLEMENTATION - ER Count by ISI by SB Category" for my SSO
        $a = [
            'er.sso_service' => $USER_BU_CURRENT_NAME,
            'sb.status' => '%IMPLEMENTATION',
        ];
        $a = tldUtils::cleanupFormInput($a);

        $matrix = new tldMatrix(
            tldSB_Line::countByStatusByCategoryByConstraints($a),
            'status', 'category', 'num',
            "$php_self?m[0]=sb&m[1]=listing&m[2]=lines&m[3]=sbImplementationByStatusByCategory&m[4]=bySSO&ssoid={$sess['sb']['buid']}",
            "SB (PARTIAL) IMPLEMENTATION, ER Count by ISI, by SB Category in $USER_BU_CURRENT_NAME",
            [
                'xItems' => tldSB_Line::getStatusList(),
                'yItems' => tldSB3::getCategoryList(),
            ]
        );
        $USER_DASH_HTML .= $matrix->fetch();
        break;
    case 'Group Dashboard':

        // Matrix "SB by Status by Category" For ALL Factory

        $matrix = new tldMatrix(
            tldSB3::countByCategoryStatusByConstraints(),
            'status', 'category', 'num',
            "$php_self?m[0]=sb&m[1]=listing&m[2]=byCategoryStatus",
            'SB Count by Status, by Category for all factory',
            [
                'xItems' => tldSB3::getStatusList(),
                'yItems' => tldSB3::getCategoryList(),
            ]
        );
        $USER_DASH_HTML .= $matrix->fetch();

        // Matrix "IMPLEMENTATION - ER Count by ISI by SB Category"

        $a = ['sb.status' => '%IMPLEMENTATION'];
        $extra_link = $extra_title = '';
        if (isset($sess['sb']['buid']) && $sess['sb']['buid'] !== 'ALL') {
            $a['er.sso_service'] = $USER_BU_CURRENT_NAME;
            $extra_title = ', ' . $USER_BU_CURRENT_NAME;
            $extra_link = "&m[4]=bySSO&ssoid={$sess['sb']['buid']}";
        }
        $a = tldUtils::cleanupFormInput($a);
        $partialImplementationSBLines = tldSB_Line::countByStatusByCategoryByConstraints($a);
        $matrix = new tldMatrix(
            $partialImplementationSBLines,
            'status', 'category', 'num',
            "$php_self?m[0]=sb&m[1]=listing&m[2]=lines&m[3]=sbImplementationByStatusByCategory$extra_link",
            "SB (PARTIAL) IMPLEMENTATION, ER Count By All ISI, by SB Category$extra_title",
            [
                'xItems' => array_merge(['' => ''],tldSB_Line::getStatusList()),
                'yItems' => tldSB3::getCategoryList(),
            ]
        );
        $USER_DASH_HTML .= $matrix->fetch();

        $partialImplementationSBOpenLines = array_filter($partialImplementationSBLines, function ($item) {
            return $item['status'] !== "" && $item['status'] !== "CLOSED";
        });

        $totals = [
            "COMPULSORY" => 0,
            "RECOMMENDED" => 0,
            "INFORMATION" => 0,
        ];

        foreach ($partialImplementationSBOpenLines as $item) {
            if (isset($totals[$item['category']])) {
                $totals[$item['category']] += (int) $item['num'];
            }
        }

        foreach ($totals as $category => $total) {
            $partialImplementationSBOpenLines[] = [
                "status" => "TOTAL_OPEN",
                "category" => $category,
                "num" => $total,
            ];
        }

        $matrix = new tldMatrix(
            $partialImplementationSBOpenLines,
            'status', 'category', 'num',
            "$php_self?m[0]=sb&m[1]=listing&m[2]=lines&m[3]=sbImplementationByStatusByCategory$extra_link",
            "SB (PARTIAL) IMPLEMENTATION, ER Count By OPEN ISI, by SB Category$extra_title",
            [
                'xItems' => array_merge(tldSB_Line::getOpenStatusList(), ['TOTAL_OPEN' => 'TOTAL_OPEN']),
                'yItems' => tldSB3::getCategoryList(),
                'doNotShowYTotals' => true
            ]
        );
        $USER_DASH_HTML .= $matrix->fetch();
        break;
    default:
        // no dashboard
        break;
}

// ------------------------------------------------
//                     DISPLAY
// ------------------------------------------------

// Menu --->

$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sb&m[1]=dashboard&dashboard_type=$USER_DASH_DEFAULT">My dashboard</a>
EOF;
// Display possible dashboard to use
foreach ($USER_DASH_ALLOWED as $dashboardName) {
    if ($USER_DASH_DEFAULT == $dashboardName) {
        continue;
    }
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=sb&m[1]=dashboard&dashboard_type=$dashboardName">$dashboardName</a>
EOF;
}

// Display page --->

$body .= <<<EOF
<h2 style="border-bottom: 1px solid grey;">$USER_DASH_CURRENT - $USER_BU_CURRENT_NAME BU</h2>
<br>{$frmSwitch->toHTML()}
$USER_DASH_HTML
EOF;
