<?php
include_once 'erp.inc.php';
include_once 'common.inc.php';
include_once 'forms_and_reports.inc.php';
include_once 'HTML/QuickForm.php';

session_start();
if (!isset($_SESSION['sess'])) $_SESSION['sess'] = null;
$sess =& $_SESSION['sess'];

$user = new tldUser($GLOBALS['PHP_AUTH_USER']);

$smarty = tldUtils::getSmarty('intranet');

$php_self = $_SERVER['PHP_SELF'];
$DEFAULT_MENU = <<<EOF
<a href="$php_self">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=forex">Forex</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=so">Sales Orders</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=ps">Packing Slips</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=inv">Invoices</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=po">Purchase Orders</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=backlog">SSO Bookings Summary</a>
&nbsp;|&nbsp;<a href="/en/private/sales_service/sales.php?m[0]=activity2">Sales Activity</a>
&nbsp;|&nbsp;<a href="/en/private/manufacturing/index.php?m[0]=activity">Manufacturing Activity</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=procedures">Procedures</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=archive">Archive</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=msg">MSG (BETA)</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=reports">Reports</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sor_tran">SOR Trans</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=mfg_margins">Manufacturing Margins</a>
EOF;

//initialize vars
$PATH = 'finance';
$DEFAULT_TEMPLATE = 'intranet.tpl';
$DEFAULT_TITLE = 'Finance';
$DEFAULT_ERROR = [];

switch ($m[0]) {
    case 'ps':
        if($user->isAgent() && !$user->isInGroup('gg_PARTS_AGENTS')){
            $DEFAULT_ERROR[] = 'You do not have permissions for this page.';
            break;
        }
        include "{$m[0]}/{$m[0]}.logic.inc.php";
        break;
    case 'forex':
    case 'inv':
    case 'ap':
    case 'so':
    case 'po':
    case 'backlog':
    case 'procedures':
    case 'archive':
    case 'sor_tran':
    case 'msg':
    case 'reports':
    case 'mfg_margins':
    case 'vat':
        if($user->isAgent() && !$user->isInGroup('gg_SALES_AGENTS')){
            $DEFAULT_ERROR[] = 'You do not have permissions for this page.';
            break;
        }
        include "{$m[0]}/{$m[0]}.logic.inc.php";
        break;
    default:
        if($user->isAgent() && !$user->isInGroup('gg_SALES_AGENTS')){
            $DEFAULT_ERROR[] = 'You do not have permissions for this page.';
            break;
        }
        $body = $smarty->fetch("$PATH/homepage.finance.tpl");
        $sequencesLink = [];
        $sequencesLink[] = [
            'link' => '/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=assets.disposal',
            'desc' => 'Start an Assets Disposal Sequence',
        ];
        if ($user->isInGroup([
            'gg_ADMIN',
            'gg_helpdesk_admin',
            'role_CFO',
            'role_COO',
            'role_CEO',
            'role_EVP',
            'role_MPE',
            'seq_investmentbudget.request.level.1',
        ])
        ) {
            $sequencesLink[] = [
                'link' => '/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=investmentbudget.request',
                'desc' => 'Start an Investment Budget Request Sequence (SSO)',
            ];
            $sequencesLink[] = [
                'link' => '/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=investmentbudget.request.factory',
                'desc' => 'Start an Investment Budget Request Sequence (Factory)',
            ];
        }
        if (in_array($user->getRegionID(), ['7', '16', '22']) || $user->isInGroup(['superuser'])) {
            $sequencesLink[] = [
                'link' => '/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=prepayment_request_sage.request',
                'desc' => 'Start a Prepayment Request APA-Sage',
            ];
            $sequencesLink[] = [
                'link' => '/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=vendor_creation_request_sage.request',
                'desc' => 'Start a New Vendor Creation Request APA-Sage ',
            ];
        }
        if ($user->getBUID() == 1 || $user->getBUID() == 4 || $user->getBUID() == 32 || $user->getBUID() == 3 || $user->getBUID() == 30 || $user->getBUID() == 34 || $user->isInGroup([
                'superuser',
                'gg_EXCOM',
            ])
        ) {
            $sequencesLink[] = [
                'link' => '/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=nonproductionpurchase.request',
                'desc' => 'Start an Non-production purchase request',
            ];
        }
            $sequencesLink[] = [
                'link' => '/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=newpayment.request',
                'desc' => 'Start an payment request',
            ];

        if (in_array($user->getBUID(), ['32', '1', '71', '57', '2', '38', '6'], true) || $user->isInGroup(['superuser'])) {
            $sequencesLink[] = [
                'link' => '/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=payment_request_asi.request',
                'desc' => 'Start an payment request for Asia',
            ];
        }

            $sequencesLink[] = [
                'link' => '/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=newprepayment.request',
                'desc' => 'Start an prepayment request',
            ];

            $sequencesLink[] = [
                'link' => '/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=stamp.request',
                'desc' => 'Start an stamp request(prototype)',
            ];

        if ($user->isInGroup(['superuser', 'role_inventory', 'gg_ADMIN', 'gg_ACCT', 'gg_PARTS'])) {
            $sequencesLink[] = [
                'link' => '/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=acct.invadjust',
                'desc' => 'Start an Inventory Adjustment Sequence(For SPH)',
            ];
        }
        if ($user->isInGroup([
            'superuser',
            'role_inventory',
            'gg_ADMIN',
            'gg_ACCT',
            'gg_PARTS',
            'gg_QUALITY',
            'gg_ENG',
            'gg_PUR',
            'role_GL',
            'role_PM',
        ])
        ) {
            $sequencesLink[] = [
                'link' => '/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=acct.positiveadj',
                'desc' => 'Start a Positive Inventory Adjustment Sequence(BETA)',
            ];
            $sequencesLink[] = [
                'link' => '/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=acct.negativeadj',
                'desc' => 'Start a Negative Inventory Adjustment Sequence(BETA)',
            ];
            $sequencesLink[] = [
                'link' => '/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=acct.loanpart',
                'desc' => 'Start a Loan Parts Request Sequence(BETA)',
            ];
        }
        if (count($sequencesLink ?? []) > 0) {
            $body .= '<h3>Sequences</h3>';
            $body .= '<ul>';
            foreach ($sequencesLink as $link) {
                $body .= "<li><a href=\"{$link['link']}\">{$link['desc']}</a></li>";
            }
            $body .= '</ul>';
        }
        break;
}

/*
 * $CREATE_NEG_APNs = tldVWC::byQuery(array("status"=>"CREATE_NEG_APN"));
 * $body .= _listVWC($CREATE_NEG_APNs, "VWCs Pending Negative APN Transactions");
 * $CREATE_POS_APNs = tldVWC::byQuery(array("status"=>"CREATE_POS_APN"));
 * $body .= _listVWC($CREATE_POS_APNs, "VWCs Pending Positive APN Transactions");
 * $GLOBALS['sess']["vwc"]["list"] = $CREATE_NEG_APNs + $CREATE_POS_APNs;
 */

$smarty->assign('error', implode('<br>', $DEFAULT_ERROR));
$smarty->assign('menu', $DEFAULT_MENU . (isset($menu) ? $menu : ''));
if (empty($title)){
    $title = $DEFAULT_TITLE;
}
$smarty->assign('title', $title);
$smarty->assign('body', $body);

if (empty($template))
    $template = $DEFAULT_TEMPLATE;
if ($template !== 'NO_TEMPLATE') {
    $smarty->display($template);
}

function _listVWC($rows, $title = '')
{
    $report = new tldReportColumnar(
        $rows,
        [
            'xItems' => [
                'id' => 'VWC #',
                'status' => 'Status',
                'type' => 'Type',
                'date' => 'Date',
                'erp' => 'ERP',
                'location' => 'Location',
                'suno' => 'Supplier Num',
                'module' => 'Module',
                'parent_id' => 'Ref#'],
            'links' => ['id' => '/en/private/manufacturing/pur/dev.php?m[0]=vwc&m[1]=view&id='],
            'title' => $title,
        ]
    );

    return $report->fetch();
}
