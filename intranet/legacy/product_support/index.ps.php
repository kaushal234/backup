<?php

require_once __DIR__.'/../legacy_autoload.php';
require_once 'autoload_shared.php';

include_once 'common.inc.php';
include_once 'product_support.inc.php';
include_once 'forms_and_reports.inc.php';
include_once 'sales_service.inc.php';

session_start();
if (!isset($_SESSION['sess'])) {
    $_SESSION['sess'] = null;
}
$sess =& $_SESSION['sess'];

$body = '';
$user = new tldUser($GLOBALS['PHP_AUTH_USER']);

$smarty = tldUtils::getSmarty('intranet');

$smarty->assign('m', $m);
$smarty->assign('currentUser', $user->itsDetails);

$php_self = $_SERVER['PHP_SELF'];
$DEFAULT_MENU = <<<EOF
<a href="$php_self">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pdc" title="Product Demerit Claims">PDC</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment" title="Equipment Records">ER</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=wc" title="Warranty Claims">WC</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sbs" title="Service Bulletins">SB</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sb" title="Service Bulletins">SB3 (NEW)</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=publications" title="Publications">Pubs</a>
&nbsp;|&nbsp;<a href="/en/private/sales/catalogue/lead_times" title="Standard lead time">Standard lead time</a>
EOF;
if (!$user->isAgent() || $user->isInGroup(['gg_PARTS_AGENTS', 'gg_SALES_AGENTS'])) {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=odp" title="ODP Online">ODP</a>
EOF;
}
if ($user->isInGroup(['gg_SUPPORT', 'gg_ENG', 'gg_ADMIN'])) {
    $DEFAULT_MENU .= <<<EOF
	&nbsp;|&nbsp;<a href="$php_self?m[0]=admin">Admin</a>
EOF;
}

$PATH = 'product_support';
$DEFAULT_TEMPLATE = 'intranet.tpl';
$DEFAULT_TITLE = 'Support Module';
$DEFAULT_ERROR = $DEFAULT_SUCCESS = [];

switch ($m[0]) {
    case 'demerits':
        $m[0] = 'pdc'; // internal redirection
    default:
        if ($m[0]) {
            include $m[0] . "/${m[0]}.logic.inc.php";
        } else {
            $body = $smarty->fetch("$PATH/homepage.ps.tpl");
            if (!$user->isInGroup(['gg_ADMIN', 'role_PSM', 'role_PSE', 'role_PSA', 'gg_SUPPORT'])) {
                break;
            }

            $matrixFactorySupport = new tldMatrix(
                tldTOC::countByFactorySupportRequired(),
                'sso_fullname', 'factory_fullname', 'num',
                '/en/private/sales_service/service.php?m[0]=toc&m[1]=listing&m[2]=byFactorySupportRequired',
                'TOC IN PROGRESS & SUSPENDED with Factory Support Required<br>Count by SSO, Factory'
            );
            $body .= $matrixFactorySupport->fetch();
        }
        break;
}
$smarty->assign('menu', $DEFAULT_MENU . (isset($menu) ? $menu : ''));
$smarty->assign('body', $body);
$smarty->assign('title', $DEFAULT_TITLE);
$smarty->assign('error', implode('<br>', $DEFAULT_ERROR));
$smarty->assign('success', implode('<br>', $DEFAULT_SUCCESS));
$smarty->assign("js_includes",$JS_INCLUDE ?? []);

if (empty($template)) {
    $template = $DEFAULT_TEMPLATE;
}

if ($template !== 'NO_TEMPLATE') {
    $smarty->display($template);
}
