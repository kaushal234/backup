<?php
include_once('common.inc.php');
include_once('mis.inc.php');
include_once('calendar.inc.php');
require_once('HTML/QuickForm.php');
require_once('HTML/QuickForm/advmultiselect.php');

// Get MOO ID
$moo_id = tldModule::getMOOIDByModule('mis');
session_start();
if (!isset($_SESSION['sess'])) {
    $_SESSION['sess'] = null;
}
$sess =& $_SESSION['sess'];
$body = '';
$user = new tldUser($GLOBALS['PHP_AUTH_USER']);

$smarty = tldUtils::getSmarty('intranet');
$php_self = $_SERVER['PHP_SELF'];
$DEFAULT_TEMPLATE = isset($sess['template']) ? $sess['template'] : 'intranet.tpl';
$DEFAULT_ERROR = [];
$PATH = 'mis';

$DEFAULT_TITLE = 'MIS';

$DEFAULT_MENU = <<<EOF
    <tld:include src="mis/_menu.html.twig" />
EOF;

switch ($m[0]) {
    case 'module':
    case 'tts':
    case 'inv':
    case 'inventory':
    case 'dev':
    case 'help':
    case 'grdesc':
    case 'pdm':
    case 'baan_licences':
    case 'reports':
        include($m[0] . '/logic.' . $m[0] . '.inc.php');
        break;
        break;
    default:
        $DEFAULT_TITLE .= '/Homepage';

        // Top MIS Homepage -------------------------------------->

        $cells = [];
        // Link to create a task
        $cells[0] = <<<EOF
<p>
  <u>If you have a request to MIS department:</u><br>
  <a href="/en/private/mis/mis.php?m[0]=tts&m[1]=forms&m[2]=newticket">Please submit a new MIS TASK here</a>
</p>
<p>
  <u>MIS sequences:</u>
  <br><a href="/en/private/calendar/calendar.php?m[0]=seq&amp;m[1]=new&amp;m[2]=hr.user.new">Start NEW user request sequence</a>
  <br>NOTE: User sequence update & delete are only available under the user vcard, section 'User Sequence & tasks'
  <br><a href="/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=investmentbudget.request&mis_or_not=Y">Start an Investment Budget Request Sequence</a>
EOF;
        // Add specific MIS link
        if ($user->isInGroup('gg_MIS')) {
            $cells[0] .= <<<EOF
<br><a href="/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=mis.order.process">Start an MIS Ordering Process Sequence</a>
EOF;
        }
        // Continue with public links
        $cells[0] .= <<<EOF
</p>
<p>
  <u>Check actual Baan licenses usage</u><br>
  <a href="/en/private/mis/mis.php?m[0]=baan_licences">Actual Baan licenses usage</a>
</p>
EOF;
        // MIS Procedure + Baan licenses
        $cells[] = <<<EOF
<a href="/en/private/mis/mis.php?m[0]=help&m[1]=dms&id=6222">
  <img src="/shared/icons/file_icons/256x256/AdobeReader.png" height="100" width="100"><br>
  TLD MIS Management<br/>process description
</a>
EOF;

        $report = new tldHTMLTable(
            $cells,
            [
                'cols' => 3,
                'attribs' => ['table' => " width='100%'", 'tr' => " align='center' bgcolor='#FFFFFF'"],
            ]
        );
        $body .= $report->fetch();

        break;
}

$smarty->assign('menu', $DEFAULT_MENU . (isset($menu) ? $menu : ''));
$smarty->assign('error', implode('<br>', $DEFAULT_ERROR));
$smarty->assign('body', $body);
$smarty->assign('width', '1024px');
if (empty($title)) {
    $title = $DEFAULT_TITLE;
}
$smarty->assign('title', $title);

if (empty($template)) {
    $template = $DEFAULT_TEMPLATE;
}
if ($template !== 'NO_TEMPLATE') {
    $smarty->display($template);
}

