<?php

use Alvest\Translator\Loader\FileLoader;
use App\Client\ApiClient;
use App\Event\BaanDatabaseQueryEvent;
use App\Event\Subscriber\BaanDatabaseQueryEventSubscriber;
use App\Pi\User;
use App\Translator;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Processor\WebProcessor;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\Translation\Loader\PoFileLoader;
use Symfony\Component\Translation\Loader\YamlFileLoader;

require __DIR__ . '/../vendor/autoload.php';

include_once("common.inc.php");
include_once("forms_and_reports.inc.php");
include_once("eng.inc.php");
include_once("erp.inc.php");
include_once("HTML/QuickForm.php");
include_once("quality.inc.php");
include_once("sales_service.inc.php");
require_once 'HTML/QuickForm/advmultiselect.php';
include 'session.handler.inc.php';

$fileHandler = new RotatingFileHandler(__DIR__ . '/../var/log/baan-requests.log', 80, Logger::INFO);
$logger = new Logger('baan', [$fileHandler], [new PsrLogMessageProcessor(), new IntrospectionProcessor(Logger::INFO, [], 1), new WebProcessor()]);
TldDatabase::setBaanLogger($logger);

$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__ . '/../.env');

$client = new ApiClient($_ENV['API_URL']);
if (isset($_COOKIE['_jwt'])) {
    $client->setToken($_COOKIE['_jwt']);
}

/** @var ApiClient $client */
if (!$client->isAuthenticated() || (null === $client->getUserId())) {
    $_SESSION['pi_user'] = null;
    $m = ['login'];
} else if (($_SESSION['pi_user'] ?? '') == '') {
    try {
        $user = json_decode($client->request('GET', '/me')->getContent(), true, JSON_THROW_ON_ERROR);
        $_SESSION['pi_user'] = $user['lastname'] . ' ' . $user['firstname'];
        $_SESSION['pi_user_id'] = $user['legacyId'];
        $user = User::fromId($_SESSION['pi_user_id']);
    } catch (ClientException $exception) {
        $_SESSION['pi_user'] = null;
    }
}

if (!isset($_SESSION['sess'])) {
    $_SESSION['sess'] = [];
}
$sess =& $_SESSION['sess'];

$php_self = $_SERVER['PHP_SELF'];

$DEFAULT_ERROR = array();
$DEFAULT_TEMPLATE = "shop.tpl.inc.php";

if (isset($_SESSION['pi_user_id'])) {
    $user = User::fromId($_SESSION['pi_user_id']);
    $locations = tldLocation::byConstraints(['id' => $user->getUser()['bu_id']]);
} else {
    $locations = [];
}

// Case of change request
if (!empty($_REQUEST['changeLocation'])) {
    $newBUID = TldDatabase::escape($_REQUEST['changeLocation']);
    $newBU = new tldLocation($newBUID);
    // ACL
    $actualERP = current($locations)['erp'];
    $ERP_LIST_ACL = array(
        400 => [410],
        410 => [400],
        520 => [500],
        500 => [510, 520, 540],
        540 => [500],
        510 => [500, 520],
    );
    $ERP_LIST_ACL[$actualERP][] = $actualERP;
    $user = isset($_SESSION['pi_user_id']) ? new TldUser($_SESSION['pi_user_id']) : null;
    // Check if location allowed
    if (((int)current($locations)['id']) !== 51 && (!in_array($newBU->getERP(), $ERP_LIST_ACL[$actualERP]) && (null === $user || !$user->isInGroup(['superuser', 'role_CMO', 'gg_HR'], ['return_rows' => false])))) {
        $DEFAULT_ERROR[] = sprintf(
            _("ERROR: You are not allowed to change to %s"),
            $newBU->getShortName()
        );
    } else {
        $sess['location'] = $newBU->itsDetails;
        header('Location: /shop/autoselect.php');
        exit;
    }
}

// If session is set, take the location in session
if (!empty($sess['location'])) {
    $LOCATION = $sess['location'];
} else {
    // Take default
    if (count($locations) < 1 && $client->isAuthenticated()) {
        $DEFAULT_ERROR[] = _("ERROR: Could not identify location from profile");
    }
    if (count($locations) > 0) {
        $LOCATION = $locations[0];
    }
    // Specifics to SORIGNY location, switch to MTL => MTL PRODUCTION for DTV + MTL location
    if (isset($LOCATION) && $LOCATION['location'] === 'TLD DTV') {
        $location = new tldLocation(21); // MTL
        $LOCATION = $location->itsDetails;
    }

    if (isset($LOCATION) && $LOCATION["erp"] === "0") {
        $DEFAULT_ERROR[] = _("ERROR: Could not identify location from profile");
        $parameters = array_values($m ?? []);
        if (!in_array('logout', $parameters) && !in_array('changeLocation', $parameters)) {
            $m = '';
        }
    }
}
$ERP = $LOCATION["erp"] ?? null;

// Language selection -------------------->

switch ($ERP) {
    case 500:
    case 510:
    case 520:
    case 540:
    case 420:
    case 570:
        $LANG = "FR";
        $language = 'fr_FR.iso88591';
        $charset = 'ISO-8859-1';
        break;
    case 620:
    case 640:
    case 660:
    case 600:
    case 610:
    case 650:
        $LANG = "CH";
        $language = 'zh_CN.gb2312';
        $charset = 'GB2312';
        break;
    case 'EN':
    default:
        $LANG = "EN";
        $language = 'en_US';
        $charset = 'ISO-8859-1';
        break;
}

putenv("LANG=$language");
setlocale(LC_ALL, $language);
bindtextdomain('shopfloor', "$LOCAL_INTRANET_PATH/shop/locale");
bind_textdomain_codeset('shopfloor', $charset);
textdomain('shopfloor');
header("Content-Type:text/html; charset=$charset");

$translatorLocale = strtolower($LANG);
if ($translatorLocale === 'ch') {
    $translatorLocale = 'zh-CN';
}
$translator = new Translator($translatorLocale);
$translator->addLoader('yaml', new YamlFileLoader());
$translator->addLoader('po', new PoFileLoader());
foreach (FileLoader::load() as $file) {
    $pathinfo = pathinfo($file);
    list($domain, $locale) = explode('.', $pathinfo['filename']);
    $translator->addResource($pathinfo['extension'], $file, $locale, $domain);
}

$translator->setCharset($charset);

$dispatcher = new EventDispatcher();
$baanQueryListener = new BaanDatabaseQueryEventSubscriber($session, $translator);

$dispatcher->addSubscriber($baanQueryListener);
TldDatabase::setEventDispatcher($dispatcher);
TldDatabase::setBaanEvent(new BaanDatabaseQueryEvent());

$translate = new tldTranslate($LANG, $charset);

$client->setLocale($translatorLocale);

$DEFAULT_TITLE = _("Shop Floor Homepage");
$DEFAULT_MENU = "
	<a href=\"$php_self\">" . _("Home") . "</a>&nbsp;|&nbsp;
";
if ($ERP == 530) {
    $DEFAULT_MENU .= "
	<a href=\"$php_self?m[0]=changeLocation\">Change Location</a>&nbsp;|&nbsp;
";
}

$DEFAULT_MENU .= "
	<a href=\"$php_self?m[0]=er\">ER</a>&nbsp;|&nbsp;
	<a href=\"$php_self?m[0]=cbom\">CBOM</a>&nbsp;|&nbsp;
	<a href=\"$php_self?m[0]=bom\">BOM</a>&nbsp;|&nbsp;
	<a href=\"$php_self?m[0]=dwg\">" . _("Drawings") . "</a>&nbsp;|&nbsp;
	<a href=\"$php_self?m[0]=ncr\">NCR</a>&nbsp;|&nbsp;
	<a href=\"$php_self?m[0]=crab\">CRAB</a>&nbsp;|&nbsp;
	<a href=\"$php_self?m[0]=eap\">EAP</a>&nbsp;|&nbsp;
	<a href=\"$php_self?m[0]=webcams\">" . _("Shop Cams") . "</a>&nbsp;|&nbsp;
	<a href=\"$php_self?m[0]=help\">" . _("Help") . "</a>&nbsp;|&nbsp;
	<a href=\"$DMS_URL\" target=\"blank\">" . _("DMS") . "</a>&nbsp;|&nbsp;
	<a href='https://tld-group.javelo.io/auth/login/signin' target='_blank'>Javelo</a> |
";

// Menu management
switch ($ERP) {
    case 900:
    case 500:
    case 520:
    case 540:
        $DEFAULT_MENU .= "<a href=\"https://alvest.kelio.io\" target=\"blank\">Kelio</a>
	&nbsp;|&nbsp;<a href=\"$php_self?m[0]=pi\">P&I</a>
";
        break;
    case 300:

        $DEFAULT_MENU .= "
	&nbsp;|&nbsp;<a href=\"http://jupiter/isosoft\" target=\"blank\">ISOSoft</a>
	&nbsp;|&nbsp;<a href=\"http://jupiter/isosoft/dcpublic.asp\" target=\"blank\">" . _("Procedures") . "</a>
	&nbsp;|&nbsp;<a href=\"http://jupiter/isosoft/dcpublic.asp?topLevel=01186\">MSDS</a>
";
        break;
    case 400:
    case 410:
        $DEFAULT_MENU .= "
    &nbsp;|&nbsp;<a href=\"$php_self?m[0]=pi\">P&I</a>
";
        break;
    case 420:
        $DEFAULT_MENU .= "
	&nbsp;|&nbsp;<a href=\"$php_self?m[0]=directory\">" . _("Directory") . "</a>
";
        break;
}
$DEFAULT_MENU .= "&nbsp;|&nbsp;<a href=\"$php_self?m[0]=dashboard&m[1]=list\">" . _("Dashboard") . "</a>";
$DEFAULT_MENU .= "&nbsp;|&nbsp;<a href=\"$php_self?m[0]=login&m[1]=logout\">" . _("Disconnect") . "</a>";
$DEFAULT_MENU .= "<hr>";

$DEFAULT_BGC = 'white';
if ($ERP == 410) {
    $DEFAULT_BGC = '#B3E5FC';
}

switch ($m[0] ?? null) {
    case 'script' :
        $body = include("pi/operationClosure.php");
        break;
    case 'eap':
    case 'er':
    case 'item':
    case 'cbom':
    case 'crab':
    case 'bom':
    case 'dwg':
    case 'ncr':
    case 'webcams':
    case 'tasks':
    case 'getfile':
    case 'directory':
    case 'help':
    case 'pi':
    case 'dms':
    case 'time_keeping':
    case 'dashboard':
    case 'login':
        $PATH = $m[0];
        include_once($m[0] . "/logic.inc.php");
        break;
    case 'changeLocation':
        // listing
        $locations = tldLocation::getFactoryList("smartyOptionsIDLocation");

        // Specific TLD EUR parts Painting sub contract
        $eurSPH = new tldLocation(7);
        $locations[$eurSPH->getID()] = $eurSPH->getShortName();
        // form
        $form = new HTML_QuickForm('swithLocationForm', 'post');
        $form->addElement('hidden', 'm[0]', '');
        $form->addElement('header', 'title', _('Switch actual location to...'));
        $form->addElement('select', 'changeLocation', 'Location', $locations);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $body = $form->toHTML();
        break;
    case 'switchstaging':
        $DEFAULT_TITLE .= " / " . _("Switch to Staging");
        $COOKIE_NAME = 'ALVEST_STAGING';
        setcookie(
            $COOKIE_NAME,
            $COOKIE_NAME,
            isset($_COOKIE[$COOKIE_NAME]) ? time() - 3600 : time() + 86400,
            '/',
            null,
            false,
            true
        );
        header("location:/shop/autoselect.php");
        exit;
    default:
        $body = include("index.tpl.inc.php");
        break;
}

$_ERROR = implode("<br>", $DEFAULT_ERROR);
$_TITLE = $DEFAULT_TITLE;
$_MENU = $DEFAULT_MENU . (isset($menu) ? $menu : '');
$_BODY = $body ?? '';

if (empty($template)) {
    $template = $DEFAULT_TEMPLATE;
}
if ($template <> "NO_TEMPLATE") {
    echo include($template);
}
