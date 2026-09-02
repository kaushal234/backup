<?php

declare(strict_types=1);

use App\App;
use Symfony\Component\HttpFoundation\Cookie;

require __DIR__.'/../vendor/autoload.php';

include_once 'common.inc.php';
include_once 'forms_and_reports.inc.php';
include_once 'HTML/QuickForm.php';
include_once 'HTML/QuickForm/advmultiselect.php';
include_once 'calendar.inc.php';
include_once 'dms.inc.php';
include_once 'help.inc.php';
include_once 'functions.php';

$app = new App();
$app->run();

if ($app->isAuthorized()) {
    $_MENU = "
<a href=\"$php_self\">"._('Home')."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=form&m[1]=byNumber\">"._('By Number')."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=form&m[1]=search\">"._('Search').'</a>&nbsp;|&nbsp;
<a href="https://www.tld-gse.com/en/private/?agent=dms_search">'._('AI Search')."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=form&m[1]=add\">"._('Add')."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=reports\">"._('Reports')."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=admin\">"._('Admin')."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=view&id=45\">"._('DMS User Guide')."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=view&id=1\">"._('DMS Help Page').'</a>
';

    switch ($m[0] ?? null) {
        case 'reports':
        case 'listing':
        case 'form':
        case 'view':
            $_PATH .= "/{$m[0]}";
            include "$_PATH/{$m[0]}.logic.php";
            break;
        case 'dashboard':
        case 'switchstaging':

            if (array_search('GG_STAGING', array_column($user->getUserGroups(), 'group_name'), true)) {
                $COOKIE_NAME = 'ALVEST_STAGING';
                $domain = implode('.', array_slice(explode('.', $app->request->getRequest()->getHttpHost()), -2, 2));

                Cookie::create(
                    $COOKIE_NAME,
                    $COOKIE_NAME,
                    isset($_COOKIE[$COOKIE_NAME]) ? time() - 3600 : time() + 86400,
                    '/',
                    $domain,
                    false,
                    true
                );
            }
            header("location: $php_self");
            break;
        default:
            include 'homepage.dms.php';
            break;
    }
}
/**
 * HTML Template parameters entry list.
 *
 * @param array  $_ERROR
 * @param array  $_CONF
 * @param array  $_NOTE
 * @param string $_TITLE
 * @param string $_MENU
 * @param string $_BODY
 */
$_ERROR = implode('<br>', $_ERROR);
$_WARNING = implode('<br>', $_WARNING);
$_CONF = implode('<br>', $_CONF);
$_NOTE = implode('<br>', $_NOTE);
echo include 'dms.tpl.php';
