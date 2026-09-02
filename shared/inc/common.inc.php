<?php

/**
 * @desc Utility classes are kept in this file
 * @access public
 * @author Graham K.L. Fong <graham.fong@tld-america.com>
 * @copyright TLD
 * @package Utilities
 */

define('PAGE_PARSE_START', microtime(true));
define('TLD_DEBUG', isset($_COOKIE['TLD_DEBUG']) && $_COOKIE['TLD_DEBUG']);
define('INTERCEPT_EMAIL', isset($_SERVER['INTERCEPT_EMAIL']) && $_SERVER['INTERCEPT_EMAIL']);

// By default, PHP sets the charset to be UTF-8
// If we want to set another charset for chinese, we need to do it through this setting, too
ini_set('default_charset', 'ISO-8859-1');

if (!defined('PXM_REG_GLOB')) {
    function addslashes_recursive($value)
    {
        if (is_array($value)) {
            foreach ($value as $index => $val) {
                $value[$index] = addslashes_recursive($val);
            }

            return $value;
        }

        return addslashes($value);
    }

    /**
     * Do not use. This is just in case we need to remove slashes, little by little
     */
    function stripslashes_recursive($value)
    {
        if (is_array($value)) {
            foreach ($value as $index => $val) {
                $value[$index] = stripslashes_recursive($val);
            }
            return $value;
        }

        return stripslashes($value);
    }

    foreach (array_merge($_ENV, $_GET, $_POST, $_COOKIE, $_SERVER) as $sensiolegacykey => $sensiolegacyval) {
        global $$sensiolegacykey; // phpcs:ignore
        $$sensiolegacykey = addslashes_recursive($sensiolegacyval);
    }

    foreach ($_POST as $sensiolegacykey => $sensiolegacyval) {
        $_POST[$sensiolegacykey] = addslashes_recursive($sensiolegacyval);
    }
    foreach ($_GET as $sensiolegacykey => $sensiolegacyval) {
        $_GET[$sensiolegacykey] = addslashes_recursive($sensiolegacyval);
    }
    foreach ($_COOKIE as $sensiolegacykey => $sensiolegacyval) {
        $_COOKIE[$sensiolegacykey] = addslashes_recursive($sensiolegacyval);
    }

    define('PXM_REG_GLOB', 1);
}

/**
 * Need these functions
 */
require_once 'config.inc.php';
require_once 'config.portals.inc.php';
require_once 'config.credentials.inc.php';
include_once 'Smarty.class.php';
include_once 'user.inc.php';
include_once 'XML/Unserializer.php';
include_once 'XML/Serializer.php';
include_once 'Image/GraphViz.php';

TldDatabase::configure('tld', $cfg['db']['www']);

if (isset($kernel)) {
    if ($kernel->getContainer()->has('monolog.logger.legacy_database')) {
        TldDatabase::setLogger($kernel->getContainer()->get('monolog.logger.legacy_database'));
    }
    if ($kernel->getContainer()->has('monolog.logger.baan_database')) {
        TldDatabase::setBaanLogger($kernel->getContainer()->get('monolog.logger.baan_database'));
    }
    if ($kernel->getContainer()->has('event_dispatcher')) {
        TldDatabase::setEventDispatcher($kernel->getContainer()->get('event_dispatcher'));
    }
}

/**
 * Common utility functions
 *
 * @package Utilities
 */
class tldUtils
{
    public $theFileUploadDir = '/en/private/uploads';
    public $HOMEDIR = '/home/www';


    public static function getColorNamesTPGTGraphs()
    {
        return [
            'darkcyan' => [0, 139, 139],
            'darkred' => [139, 0, 0],
            'violet' => [238, 130, 238],
            'black' => [0, 0, 0],
            'orange' => [255, 165, 0],
            'darkgray' => [169, 169, 169],
        ];
    }

    /**
     * Get an array list of RGB values by colour name
     *
     * This list is used in php image functions or pear modules to make it
     * easy to select colours without thinking about the RGB numbers
     *
     * @return array
     */
    public static function getColorNames()
    {
        return [
            'aliceblue' => [240, 248, 255],
            'antiquewhite' => [250, 235, 215],
            'aqua' => [0, 255, 255],
            'aquamarine' => [127, 255, 212],
            'azure' => [240, 255, 255],
            'beige' => [245, 245, 220],
            'bisque' => [255, 228, 196],
            'black' => [0, 0, 0],
            'blanchedalmond' => [255, 235, 205],
            'blue' => [0, 0, 255],
            'blueviolet' => [138, 43, 226],
            'brown' => [165, 42, 42],
            'burlywood' => [222, 184, 135],
            'cadetblue' => [95, 158, 160],
            'chartreuse' => [127, 255, 0],
            'chocolate' => [210, 105, 30],
            'coral' => [255, 127, 80],
            'cornflowerblue' => [100, 149, 237],
            'cornsilk' => [255, 248, 220],
            'crimson' => [220, 20, 60],
            'cyan' => [0, 255, 255],
            'darkblue' => [0, 0, 13],
            'darkcyan' => [0, 139, 139],
            'darkgoldenrod' => [184, 134, 11],
            'darkgray' => [169, 169, 169],
            'darkgreen' => [0, 100, 0],
            'darkkhaki' => [189, 183, 107],
            'darkmagenta' => [139, 0, 139],
            'darkolivegreen' => [85, 107, 47],
            'darkorange' => [255, 140, 0],
            'darkorchid' => [153, 50, 204],
            'darkred' => [139, 0, 0],
            'darksalmon' => [233, 150, 122],
            'darkseagreen' => [143, 188, 143],
            'darkslateblue' => [72, 61, 139],
            'darkslategray' => [47, 79, 79],
            'darkturquoise' => [0, 206, 209],
            'darkviolet' => [148, 0, 211],
            'deeppink' => [255, 20, 147],
            'deepskyblue' => [0, 191, 255],
            'dimgray' => [105, 105, 105],
            'dodgerblue' => [30, 144, 255],
            'firebrick' => [178, 34, 34],
            'floralwhite' => [255, 250, 240],
            'forestgreen' => [34, 139, 34],
            'fuchsia' => [255, 0, 255],
            'gainsboro' => [220, 220, 220],
            'ghostwhite' => [248, 248, 255],
            'gold' => [255, 215, 0],
            'goldenrod' => [218, 165, 32],
            'gray' => [128, 128, 128],
            'green' => [0, 128, 0],
            'greenyellow' => [173, 255, 47],
            'honeydew' => [240, 255, 240],
            'hotpink' => [255, 105, 180],
            'indianred' => [205, 92, 92],
            'indigo' => [75, 0, 130],
            'ivory' => [255, 255, 240],
            'khaki' => [240, 230, 140],
            'lavender' => [230, 230, 250],
            'lavenderblush' => [255, 240, 245],
            'lawngreen' => [124, 252, 0],
            'lemonchiffon' => [255, 250, 205],
            'lightblue' => [173, 216, 230],
            'lightcoral' => [240, 128, 128],
            'lightcyan' => [224, 255, 255],
            'lightgoldenrodyellow' => [250, 250, 210],
            'lightgreen' => [144, 238, 144],
            'lightgrey' => [211, 211, 211],
            'lightpink' => [255, 182, 193],
            'lightsalmon' => [255, 160, 122],
            'lightseagreen' => [32, 178, 170],
            'lightskyblue' => [135, 206, 250],
            'lightslategray' => [119, 136, 153],
            'lightsteelblue' => [176, 196, 222],
            'lightyellow' => [255, 255, 224],
            'lime' => [0, 255, 0],
            'limegreen' => [50, 205, 50],
            'linen' => [250, 240, 230],
            'magenta' => [255, 0, 255],
            'maroon' => [128, 0, 0],
            'mediumaquamarine' => [102, 205, 170],
            'mediumblue' => [0, 0, 205],
            'mediumorchid' => [186, 85, 211],
            'mediumpurple' => [147, 112, 219],
            'mediumseagreen' => [60, 179, 113],
            'mediumslateblue' => [123, 104, 238],
            'mediumspringgreen' => [0, 250, 154],
            'mediumturquoise' => [72, 209, 204],
            'mediumvioletred' => [199, 21, 133],
            'midnightblue' => [25, 25, 112],
            'mintcream' => [245, 255, 250],
            'mistyrose' => [255, 228, 225],
            'moccasin' => [255, 228, 181],
            'navajowhite' => [255, 222, 173],
            'navy' => [0, 0, 128],
            'oldlace' => [253, 245, 230],
            'olive' => [128, 128, 0],
            'olivedrab' => [107, 142, 35],
            'orange' => [255, 165, 0],
            'orangered' => [255, 69, 0],
            'orchid' => [218, 112, 214],
            'palegoldenrod' => [238, 232, 170],
            'palegreen' => [152, 251, 152],
            'paleturquoise' => [175, 238, 238],
            'palevioletred' => [219, 112, 147],
            'papayawhip' => [255, 239, 213],
            'peachpuff' => [255, 218, 185],
            'peru' => [205, 133, 63],
            'pink' => [255, 192, 203],
            'plum' => [221, 160, 221],
            'powderblue' => [176, 224, 230],
            'purple' => [128, 0, 128],
            'red' => [255, 0, 0],
            'rosybrown' => [188, 143, 143],
            'royalblue' => [65, 105, 225],
            'saddlebrown' => [139, 69, 19],
            'salmon' => [250, 128, 114],
            'sandybrown' => [244, 164, 96],
            'seagreen' => [46, 139, 87],
            'seashell' => [255, 245, 238],
            'sienna' => [160, 82, 45],
            'silver' => [192, 192, 192],
            'skyblue' => [135, 206, 235],
            'slateblue' => [106, 90, 205],
            'slategray' => [112, 128, 144],
            'snow' => [255, 250, 250],
            'springgreen' => [0, 255, 127],
            'steelblue' => [70, 130, 180],
            'tan' => [210, 180, 140],
            'teal' => [0, 128, 128],
            'thistle' => [216, 191, 216],
            'tomato' => [255, 99, 71],
            'turquoise' => [64, 224, 208],
            'violet' => [238, 130, 238],
            'wheat' => [245, 222, 179],
            'white' => [255, 255, 255],
            'whitesmoke' => [245, 245, 245],
            'yellow' => [255, 255, 0],
            'yellowgreen' => [154, 205, 50],
        ];
    }

    /**
     * Escape parameters that go into a sql query
     *
     *
     * @param mixed $p
     */
    public static function escapeSQL($p)
    {
        foreach ($p as $key => $value) {
            if (!is_array($value)) {
                $res[$key] = TldDatabase::escape($value);
            }
        }
        return $res;
    }

    /**
     * Make connection to a database
     * Uses config.global.inc.php to get $cfg
     * @param string name of database system to connect to
     */
    public static function connectDb($id = '')
    {
        global $cfg;

        switch ($id) {
            case 'baan':
                return self::connectODBC($cfg['db']['baan']);
                break;
            case 'baan_tld':
                return self::connectODBC($cfg['db']['baan_tld']);
                break;
            case 'baantest':
                return self::connectODBC($cfg['db']['baantest']);
                break;
            case 'equotes':
            case 'wordpress':
                TldDatabase::configure($id, $cfg['db'][$id]);
                break;
            case 'tld':
            default:
                TldDatabase::configure('tld', $cfg['db']['www']);
                break;
        }
        return null;
    }

    /**
     * make a connection to an ODBC database
     *
     * @param array $cfg array containing parameters for connecting to the odbc database
     */
    public static function connectODBC($cfg): ?PDO
    {
        try {
            return new PDO(
                sprintf('dblib:host=%s:%s;dbname=baandb;charset=CP936;', $cfg['host'], $cfg['port']),
                $cfg['user'],
                $cfg['pwd'],
                [
                    PDO::ATTR_TIMEOUT => 300,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (\Exception $exception) {
            tldDatabase::log('error', 'Error while opening SQL connection on "{connection}": {message}', [
                'connection' => $cfg['host'],
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    /**
     * Get smarty object for given site
     *
     * @param string $site name of site to create smarty template for
     * @return Smarty
     */
    public static function getSmarty($site = '')
    {
        $cfg = $GLOBALS['cfg'];

        $smarty = new Smarty;
        $smarty->register_modifier('stripslashes', 'stripslashes');

        switch ($site) {
            case 'intranet':
                $path = "$GLOBALS[INTRA_PATH]";
                break;
            case 'common':
                $path = "$GLOBALS[SHARED_PHP_PATH]";
                break;
            case 'shopfloor':
                $path = "$GLOBALS[LOCAL_INTRANET_PATH]";
                break;
            case 'tldgse':
                $path = "$GLOBALS[WEB_ROOT]";
                $smarty->assign('myUtils', new self());
                break;
            case 'actiparts':
                $path = "$GLOBALS[ACTIPARTS_PATH]";
                break;
            default:
                $path = $site;
        }

        $smarty->template_dir = "$path/";
        $smarty->compile_dir = "$path/templates_c/";
        $smarty->config_dir = "$path/configs/";
        $smarty->cache_dir = "$path/cache/";
        //assign common options
        $smarty->assign('radios_yesno', ['NO' => 'NO', 'YES' => 'YES']);
        return $smarty;
    }

    /**
     * Get the translation for the item and lang
     *
     * @param listname The name of the tldList to use
     * @param lang The code of the language to retrieve the translation in e.g. en, fr, etc
     *
     * @return string
     */
    public function translate($listname, $lang)
    {
        if (empty($lang)) {
            $lang = 'en';
        }
        $listname = TldDatabase::escape($listname);
        $query = <<<EOF
			SELECT list_item
			FROM lists
			WHERE list_name='$listname'
				AND list_key='$lang'
EOF;
        $row = self::getSqlRowToAssocArray($query);
        //get the en version if can't find selected language
        if (empty($row['list_item'])) {
            $query = <<<EOF
			SELECT list_item
			FROM lists
			WHERE list_name='$listname'
				AND list_key='en'
EOF;
            $row = self::getSqlRowToAssocArray($query);
        }
        return $row['list_item'];
    }

    public function setLocaleRegion($lang = 'en')
    {
        $langs = [
            'en' => 'en_US',
            'fr' => 'fr_FR',
            'es' => 'es_SP',
            'de' => 'de_DE',
            'pt' => 'pt_PT',
            'zh' => 'zh_CN',
            'ja' => 'ja_JP',
            'ru' => 'ru_RU'
        ];
        setlocale(LC_ALL, $langs[$lang]);
    }

    /**
     * convert xml to an array structure
     *
     * Can't use simplexml and casting to convert because it
     * is not recursive!
     * CANNOT DO THIS YET!!
     * <code>
     *    public function convXMLToArrayTEMP($xml, $options = ""){
     *        $sxml = new SimpleXMLElement($xml);
     *        return (array) $sxml;
     *    }
     * </code>
     *        *
     * @param mixed $a XML string or data array
     * @return string if error
     */
    public static function convXMLToArray($xml, $options = '')
    {
        if (empty($options)) {
            $options = ['complexType' => 'array'];
        }
        $dexml = new XML_Unserializer($options);
        $status = $dexml->unserialize($xml);
        if ((new PEAR)->isError($status)) {
            return $status->getMessage();
        }
        return $dexml->getUnserializedData();
    }

    public function convArrayToXML($a)
    {
        $options = [
            XML_SERIALIZER_OPTION_INDENT => '    ',
            XML_SERIALIZER_OPTION_RETURN_RESULT => true,
        ];
        $serializer = new XML_Serializer($options);
        return $serializer->serialize($a);
    }

    /**
     * Convert 2006-01-01 date to a timestamp format
     *
     * @return integer
     */
    public static function dateToTimestamp($date)
    {
        //m,d,y
        $bits = explode('-', $date);
        if (count($bits) <> 3) {
            return;
        }
        return mktime(0, 0, 0, $bits[1], $bits[2], $bits[0]);
    }

    public static function isohtmlspecialchars($str)
    {
        return htmlspecialchars($str, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1');
    }


    public static function removeAccent($str)
    {
        return preg_replace('/&(.)(.*?);/', '$1', htmlentities($str));
    }

    /**
     * check if email valid
     * @param string $email
     * @return boolean on error
     */
    public function isEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     *    Create and send an email with an
     *
     * @return boolean
     */
    public static function emailAttachment($to, $from, $subject, $body, $file = '', $cc = '', $Bcc = '', $options = [], $replyTo = null)
    {
        global $cfg;
        // Filter options
        $options = array_filter((array) $options);

        // Create mail
        $crlf = "\n";
        $mime = new Mail_mime($crlf);
        $mime->setHTMLBody($body);
        // Look for files
        if (!empty($file)) {
            if (is_array($file)) {
                foreach ($file as $f) {
                    $mime->addAttachment($f, 'application/unknown');
                }
            } else {
                $mime->addAttachment($file, 'application/unknown');
            }
        }
        // Set email charset defaults
        $charset = [
            'head_charset' => 'ISO-8859-1',
            'text_charset' => 'ISO-8859-1',
            'html_charset' => 'ISO-8859-1',
        ];
        // Override ALL charsets
        if (isset($options['charset'])) {
            foreach ($charset as &$value) {
                $value = $options['charset'];
            }
        }
        // Merge override charsets
        $charset = array_merge($charset, $options);
        $body = $mime->get(
            [
                'head_charset' => $charset['head_charset'],
                'text_charset' => $charset['text_charset'],
                'html_charset' => $charset['html_charset'],
            ]
        );
        // Manage recipients
        // -- check from
        if (empty($from)) {
            $from = 'noreply@tld-gse.com';
        }

        // -- to string
        $TO = is_array($to) ? implode(',', $to) : $to;
        $CC = is_array($cc) ? implode(',', $cc) : $cc;
        $BCC = is_array($Bcc) ? implode(',', $Bcc) : $Bcc;
        $recipients = $TO;
        $recipients = !empty($CC) ? $recipients . ',' . $CC : $recipients;
        $recipients = !empty($BCC) ? $recipients . ',' . $BCC : $recipients;
        // Prepare header email
        $header = (array) ($options['headers'] ?? []) + [
            'To' => $TO,
            'From' => $from,
            'Reply-To' => null !== $replyTo ? $replyTo : $from,
            'Cc' => $CC,
            'Bcc' => $BCC,
            'Subject' => $subject,
        ];
        // Configuration of the mail server to use
        $MailFactoryType = 'smtp';

        $MailFactoryOpts = [
            'auth' => $cfg['smtp']['auth'] ?? '',
            'username' => $cfg['smtp']['username'] ?? '',
            'password' => $cfg['smtp']['password'] ?? '',
            'host' => $cfg['smtp']['host'] ?? '',
            'port' => $cfg['smtp']['port'] ?? '',
        ];

        if (TLD_DEBUG || (isset($options) && is_array($options) && (($options['debug'] ?? false) === true))) {
            $MailFactoryOpts['debug'] = true;
            echo 'Function args: ' . json_encode(func_get_args()); // phpcs:ignore
            echo 'Mail header: ' . json_encode($header);
        }
        // Check if in DEV
        if (INTERCEPT_EMAIL) {
            $clientIp = self::getClientIp();
            $header['To'] = 'devteam@tld-america.com';
            $header['Cc'] = null;
            $header['Bcc'] = null;
            $header['Subject'] = "(DEV) $subject";
            $recipients = 'devteam@tld-america.com';
            $body .= <<<EOF
<br/><br/><br/>
<div>
	<strong>To:</strong> $TO<br/>
	<strong>Cc:</strong> $CC<br/>
	<strong>Bcc:</strong> $BCC<br/>
	<strong>IP:</strong> {$clientIp}
</div>
EOF;
            $MailFactoryType = 'sendmail';
            $MailFactoryOpts = null;
        }
        // Set header email
        $headers = $mime->headers($header);

        // Use SMTP server from config
        $mail = &Mail::factory($MailFactoryType, $MailFactoryOpts);
        // Send email
        $response = $mail->send($recipients, $headers, $body);
        if ((new PEAR)->isError($response) && strpos($response->getMessage(), 'Client does not have permissions to send as this sender') !== false) {
            error_log(sprintf('The from address is invalid: %s (subject: "%s")', $header['From'], $subject));
            $header['Reply-To'] = $header['From'];
            $header['From'] = 'noreply@tld-gse.com';
            $headers = $mime->headers($header, true);
            $response = $mail->send($recipients, $headers, $body);
        }
        if ((new PEAR)->isError($response)) {
            error_log(sprintf('ERROR sending email (subject: "%s"). Reason: %s. Recipients: %s. Headers: %s',
                $subject,
                $response->getMessage(),
                $recipients,
                json_encode($headers, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            ));
        }
        return $response;
    }

    /**
     * Sanitize each value in the input array
     *
     * @return array
     */
    public static function cleanupFormInput($vars)
    {
        $result = [];
        if (!is_array($vars)) {
            return $result;
        }

        foreach ($vars as $key => $value) {
            $result[$key] = is_array($value) ? $value : TldDatabase::escape($value);
        }

        return $result;
    }

    /**
     * Check whether an email address is legit
     *
     * @param string $email_address email address to check
     * @return boolean
     */
    public function validate_email($email_address)
    {
        return preg_match("/^[A-Z0-9._%-]+@[A-Z0-9._%-]+\.[A-Z]{2,6}$/i", $email_address);
    }

    /**
     * Make a log of event in the log table
     *
     * @param string $comment comment to add to table
     * @return integer id of newly created log entry in table, if successful
     */
    public static function log_event($comment)
    {
        $userInfo = self::getUserAndRequestInfo();
        $userid = $_SERVER['REMOTE_USER'] ?? null;
        if (empty($userid)) {
            $userid = $userInfo['user_login'];
        }
        $ip = self::getClientIp();
        if (empty($ip)) {
            $userid = $userInfo['user_ip'];
        }
        $comment = TldDatabase::escape($comment);
        $query = "INSERT INTO logs VALUES ('','','$userid',NOW(),'$ip','$comment')";
        return self::sqlInsert($query);
    }

    /**
     * perform db insert query
     *
     * @param string $query sql insert to perform
     * @param option $option - db type (mysql or odbc)
     * @param option2 $option2 [src] - db connection name
     * @return mixed returns an integer if insert is successful, string on error
     */
    public static function sqlInsert($query, $option = '', $option2 = '')
    {
        $src = (is_array($option2) && isset($option2['src'])) ? $option2['src'] : '';
        switch ($option) {
            case 'odbc':
            case 'mssql':
                switch ($src) {
                    case 'baan':
                        $cfg = $GLOBALS['cfg']['db']['baan'];
                        break;
                    case 'baantest':
                        $cfg = $GLOBALS['cfg']['db']['baantest'];
                        break;
                    default:
                        return;
                }
                $connection = self::connectODBC($cfg);
                $stmt = $connection->prepare($query);
                $stmt->execute();

                return (int) $connection->lastInsertId();
            default:
                $result = TldDatabase::query($query, $src);
                if ($result) {
                    return TldDatabase::lastInsertId($src);
                }

                return TldDatabase::error($src);
        }
    }

    /**
     * perform db query
     *
     * @param string $query sql insert to perform
     * @param string|array $option - db type (mysql or odbc)
     * @param string|array $option2 [src] - db connection name
     * @return string on error
     */
    public static function sqlQuery($query, $option = '', $option2 = '')
    {
        $src = is_array($option2) && isset($option2['src']) ? $option2['src'] : '';

        if (!TldDatabase::query($query, $src)) {
            return TldDatabase::error($src);
        }
    }

    /**
     * Execute an sql query
     *
     * @param string $query
     * @param array|string $option
     * @param array|string $option2
     *        "src"=>source of data
     *        "smartyOptions"=>true or false(default)
     */
    public static function sqlExecute($query, $option = '', $option2 = '')
    {
        $src = (is_array($option2) && isset($option2['src'])) ? $option2['src'] : '';
        switch ($option) {
            case 'odbc':
            case 'mssql':
                switch ($src) {
                    case 'baan':
                        $cfg = $GLOBALS['cfg']['db']['baan'];
                        break;
                    case 'baantest':
                        $cfg = $GLOBALS['cfg']['db']['baantest'];
                        break;
                    case 'pdm':
                        $cfg = $GLOBALS['cfg']['db']['pdm'];
                        break;
                    default:
                        return;
                }
                $connection = self::connectODBC($cfg);
                $stmt = $connection->prepare($query);
                $stmt->execute();
                break;
            default:
                TldDatabase::query($query, $src);
        }
    }

    /**
     * Create sql set statements from arrays
     *
     * @param array $a associative array of data with table field names as keys
     * @param array $fields single array of field names to create set statement for
     * @return string
     */
    public static function getSqlSet($a, $fields = [])
    {
        if (empty($a) || !is_array($a)) {
            return;
        }
        if (empty($fields) || !is_array($fields)) {
            $fields = array_keys($a);
        }
        $result = ' ';
        foreach ($fields as $field) {
            $result .= "$field = '" .(  addslashes($a[$field]) ?? '') . "',";
        }
        return substr($result, 0, -1);
    }

    /**
     * Create a sql where statement from arrays
     *
     * @param string|array $options associative array of data with table field names as keys
     * @param string $operand operation to bind the conditions with either AND or OR
     * @return string
     */
    public static function constructWhere($options, $operand = 'AND')
    {
        // Check SQL operator validity
        if (!in_array(strtoupper($operand), ['AND', 'OR'])) {
            $operand = 'AND';
        }
        // If condition is string or empty -> return exact same value
        if (empty($options) || is_string($options)) {
            return $options;
        }
        // If it is not an array -> stop now return nothing
        if (!is_array($options)) {
            return;
        }
        // Else construct the sql where statement and return result
        $WHERE = '';
        foreach ($options as $key => $value) {
            $WHERE .= TldDatabase::escape($key) . " like '" . TldDatabase::escape($value) . "' $operand ";
        }
        return substr($WHERE, 0, -strlen($operand) - 2);
    }

    /**
     * Get db rows from a database connection
     *
     * Example odbc, baan connection
     * <code>
     *    $result = tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
     * </code>
     *
     * @param string $query The sql query to perform
     * @param array|string $option
     * @param array|string $option2
     *        ["src"] = source of data
     *        ["smartyOptions"] = true, false(default)
     * @return array of arrays
     */
    public static function getSqlToAssocArray($query, $option = '', $option2 = ''): array
    {
        $smartyOption = false;
        $src = (is_array($option2) && isset($option2['src'])) ? $option2['src'] : '';
        switch ($option) {
            case 'odbc':
            case 'mssql':
                switch ($src) {
                    case 'baan':
                        $cfg = $GLOBALS['cfg']['db']['baan'];
                        break;
                    case 'baantest':
                        $cfg = $GLOBALS['cfg']['db']['baantest'];
                        break;
                    case 'baan_tld':
                        $cfg = $GLOBALS['cfg']['db']['baan_tld'];
                        break;
                    case 'pdm':
                        $cfg = $GLOBALS['cfg']['db']['pdm'];
                        break;
                    default:
                        return [];
                }

                $conn = self::connectODBC($cfg);

                if (null !== $stopwatch = self::getStopWatch()) {
                    $stopwatch->start('database_query');
                }

                try {
                    $result = $conn->query($query);
                    $event = null !== $stopwatch ? $stopwatch->stop('database_query') : null;
                    $duration = $event ? $event->getDuration() : 0;
                    tldDataBase::log('info', 'Running SQL query ('.$duration.' .ms) "{query}" on connection "{connection}"', [
                        'query' => str_replace(["\n", "\t"], ' ', $query),
                        'connection' => $src,
                        'time' => $duration,
                ]);
                } catch (PDOException $exception) {
                    $event = null !== $stopwatch ? $stopwatch->stop('database_query') : null;
                    tldDataBase::log('error', 'Error while running SQL query "{query}" on connection "{connection}": {error}', [
                        'query' => str_replace(["\n", "\t"], ' ', $query),
                        'connection' => $src,
                        'error' => $exception->getMessage(),
                        'time' => $event ? $event->getDuration() : 0,
                    ]);
                    return [];
                }

                $rows = $result->fetchAll();

                if ($option2['smartyOptions'] ?? null) {
                    $smartyOption = true;
                    $smartyFields = $option2['smartyOptions'];
                }
                break;
            default:
                $rows = [];
                if ($dataset = TldDatabase::query($query, $src)) {
                    if (TldDatabase::numRows($dataset) === 0) {
                        return $rows;
                    }
                    while ($row = TldDatabase::fetchArray($dataset, MYSQLI_ASSOC)) {
                        $rows[] = $row;
                    }
                    if ($option === 'smartyOptions') {
                        $smartyOption = true;
                        $smartyFields = $option2;
                    }
                    // free result
                    TldDatabase::free($dataset);
                }
                break;
        }

        if (!$smartyOption) {
            return $rows ?? [];
        }
        //for returning info in Smarty options format
        //option2 can be an array of two fields to use, element 0 is key, element 1 is value
        $result = [];
        if (is_array($smartyFields)) {
            $result = array_column($rows, $smartyFields[1], $smartyFields[0]);
        } elseif (is_string($smartyFields) && !empty($smartyFields)) {
            $result = array_column($rows, $smartyFields, $smartyFields);
        }

        return $result;
    }

    /**
     * Get the first row in the result set
     *
     * @param string $query sql query to perform
     * @param array|string $option
     * @param array|string $option2
     *
     */
    public static function getSqlRowToAssocArray($query, $option = '', $option2 = ''): array
    {
        $rows = self::getSqlToAssocArray($query, $option, $option2);

        return $rows[0] ?? [];
    }

    /**
     * Transform an array to key value array
     * @param array $array
     * @param string $key
     * @param string $value
     * @return array
     */
    public static function optionsByKeyValue($array, $key, $value)
    {
        return array_column((array) $array, $value, $key);
    }

    /**
     * Get the absolute path to a file in the upload folder
     *
     * @param string $dir
     * @param string $filename
     * @return string
     */
    public static function getPathToUploadFile($dir, $filename): string
    {
        return $GLOBALS['UPLOADS_PATH'] . '/' . $dir . '/' . $filename;
    }

    /**
     * Get the absolute path to a file in the upload folder
     *
     * @param string $dir
     * @param string $filename
     * @return string
     */

    //====================================================
    //Utility functions
    public static function getRecordList($array)
    {
        $result = "<table width=\"650\"><tr><td>\n";
        $result .= self::extractHyperlinksFromObjects($array);
        $result .= "</td></tr></table>\n";
        return $result;
    }

    public static function extractHyperlinksFromObjects($obs)
    {
        $result = '';
        if (count($obs) == 0) {
            $result .= "<p class=\"alert\">No data to show</p>\n";
        } else {
            //$result = "<table width=\"650\"><tr><td>\n";
            foreach ($obs as $key => $ob) {
                $result .= $ob->hyperlink($key) . "<br>\n";
            }
            //$result .= "</td></tr></table>\n";
        }
        return $result;
    }

    public static function getRecordTable($array)
    {
        $result = '';
        if (count($array)) {
            foreach ($array as $ob) {
                $temp[] = $ob->getItsDetails();
            }
            $result .= self::extractFromArray($temp, $array[0]->itsFields, 'table');
        } else {
            $result .= 'No data available';
        }
        return $result;
    }

    public static function extractFromArray($array, $fields, $format = '')
    {
        if (count($array) == 0) {
            return;
        }
        $result = '';
        $bgcolor = ['#FFFFFF', '#CCCCCC'];
        switch ($format) {
            case 'table':
                //2 dimensions
                //Create table and header row
                $result = '<table width="650"><tr bgcolor="#3264C8" class="smallwhite">';
                foreach ($fields as $fieldName => $fieldLabel) {
                    $result .= "<td><b>$fieldLabel</b></td>";
                }
                $result .= "</tr>\n";
                //print each row fo data
                $i = 0;
                foreach ($array as $row) {
                    $result .= '<tr bgcolor="' . $bgcolor[$i % 2] . '">';
                    foreach ($fields as $fieldName => $fieldLabel) {
                        $result .= '<td>' . stripslashes($row[$fieldName]) . '</td>';
                    }
                    $result .= "</tr>\n";
                    $i++;
                }
                $result .= "</table>\n";
                break;
            case 'plain':
                foreach ($array as $row) {
                    foreach ($fields as $fieldName => $fieldLabel) {
                        $result .= '<b>' . $fieldLabel . '</b> - ' . $row[$fieldName] . "\n<br>";
                    }
                    $result .= "\n<br>";
                }
                break;
            case 'csv':
                break;
            default:
                //1 column
                //single record
                //Create table and header row
                $result = '<table width="650">';
                //print each row fo data
                $i = 1;
                foreach ($fields as $fieldName => $fieldLabel) {
                    $result .= '<tr bgcolor="' . $bgcolor[$i % 2] . '"><td><b>' . strtoupper($fieldLabel) . '</b></td><td>' .
                        stripslashes($array[$fieldName]) . "</td></tr>\n";
                    $i++;
                }
                $result .= "</table>\n";
        }
        return $result;
    }

    /**
     * Strip out all except alpha numeric characters
     *
     * @param string $aString
     * @return string
     */
    public static function getAlphaNumericOnly($aString)
    {
        $result = '';
        for ($i = 0, $iMax = strlen($aString); $i < $iMax; $i++) {
            $aChar = $aString[$i];
            $asciiChar = ord($aChar);
            if (($asciiChar > 47 && $asciiChar < 58) ||
                ($asciiChar > 64 && $asciiChar < 91) ||
                ($asciiChar > 96 && $asciiChar < 123)
            ) {
                $result .= $aChar;
            }
        }

        return $result;
    }

    /**
     * Get array of all known modules and the base url to each document
     *
     * @return array
     */
    public static function getModLinks()
    {
        global $DMS_URL;
        return [
            'AR' => '/en/private/finance/invoice-records/',
            'ASO' => '/en/private/finance/finance.php?m[0]=so&m[1]=view&erp=250&id=',
            'ATM' => '/en/private/human-resources/trainings/',
            'BP' => '/en/private/calendar/calendar.php?m[0]=bp&m[1]=view&id=',
            'CL' => '/en/private/quality/cleanliness/',
            'CPA' => '/en/private/manufacturing/qa/dev.php?m[0]=cpa&m[1]=view&id=',
            'CCR' => '/en/private/sales_service/sales.php?m[0]=ccr&m[1]=view&id=',
            'COR' => '/en/private/sales_service/sales.php?m[0]=cor&m[1]=view&id=',
            'CRT' => '/en/private/sales_service/sales.php?m[0]=crt&m[1]=view&id=',
            'CRAB' => '/en/private/manufacturing/qa/dev.php?m[0]=crab&m[1]=view&id=',
            'CSR' => '/en/private/service/customer-service-records/',
            'ECUST' => '/en/private/sales_service/sales.php?m[0]=customers&m[1]=view&id=',
            'CUR' => '/en/private/sales_service/sales.php?m[0]=cur&m[1]=view&id=',
            'DEMO' => '/en/private/sales/demos/',
            'DEROGATION' => '/en/private/quality/derogations/',
            'DMS' => "$DMS_URL/index.php?m[0]=view&id=",
            'EAP' => '/en/private/manufacturing/eng/dev.php?m[0]=eap&m[1]=view&id=',
            'ER' => '/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view&id=',
            'ER_UPGRADE' => '/en/private/product_support/index.ps.php?m[0]=er_upgrade&m[1]=view&id=',
            'ESR' => '/en/private/sales_service/sales.php?m[0]=esr&m[1]=view&id=',
            'GWF' => '/en/private/calendar/calendar.php?m[0]=gwf&m[1]=view&id=',
            'FAQ' => '/en/private/quality/first-article-qualifications/',
            'FCR' => '/en/private/sales_service/sales.php?m[0]=fcr&m[1]=view&id=',
            'INV' => '/en/private/mis/mis.php?m[0]=inv&m[1]=browse&id=',
            'IINV' => '/en/private/mis/mis.php?m[0]=inventory&m[1]=view&id=',
            'INN' => '/en/private/internal_news/internal_news.php?mode=record_view&form_type=main_tpl&id=',
            'ISR' => '/en/private/manufacturing/pur/dev.php?m[0]=isr&m[1]=view&id=',
            'JOB' => '/en/private/directory/index.php?m[0]=jobs&m[1]=view&id=',
            'MEAP' => '/en/private/manufacturing/eng/dev.php?m[0]=meap&m[1]=view&id=',
            'MIM2' => '/en/private/sales/market-intelligences/',
            'MIM' => '/en/private/sales_service/sales.php?m[0]=mim&m[1]=view&id=',
            'MISINV_ITM' => '/en/private/mis/mis.php?m[0]=inventory&m[1]=view&id=',
            'MOD' => '/en/private/mis/mis.php?m[0]=module&m[1]=view&id=',
            'MOM' => '/en/private/meetings/',
            'NCR' => '/en/private/manufacturing/qa/dev.php?m[0]=ncr&m[1]=view&id=',
            'PDC' => '/en/private/product_support/index.ps.php?m[0]=pdc&m[1]=view&id=',
            'PIO' => '/en/private/manufacturing/index.php?m[0]=pi&m[1]=view&id=',
            'PIP' => '/en/private/manufacturing/eng/dev.php?m[0]=pip&m[1]=view&id=',
            'QHSE' => '/en/private/quality/qhse_progress/',
            'SCAR' => '/en/private/manufacturing/qa/dev.php?m[0]=scar&m[1]=view&id=',
            'SB3' => '/en/private/product_support/index.ps.php?m[0]=sb&m[1]=view&id=',
            'SB' => '/en/private/product_support/index.ps.php?m[0]=sbs&m[1]=view&id=',
            'SCM' => '/en/private/sales_service/service.php?m[0]=scm&m[1]=files',
            'SEQ' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=',
            'SFR2' => '/en/private/sales/sales-forecasts/',
            'SFR' => '/en/private/sales_service/sales.php?m[0]=sfr&m[1]=view&id=',
            'SOR2' => '/en/private/sales/orders/',
            'SOR' => '/en/private/sales_service/sales.php?m[0]=sor&m[1]=view&id=',
            'SOL' => '/en/private/sales_service/sales.php?m[0]=sol&m[1]=view&id=',
            'SPQ' => '/en/private/parts/parts.php?m[0]=spq&m[1]=view&id=',
            'SPQ2' => '/en/private/parts/spq/quotations/',
            'SPR' => '/en/private/parts/parts.php?m[0]=spr&m[1]=view&id=',
            'SPR2' => '/en/private/parts/spare-parts-requests/',
            'SQE' => '/en/private/sales_service/sales.php?m[0]=sqe&m[1]=view&id=',
            'SQR' => '/en/private/sales_service/sales.php?m[0]=sqr&m[1]=view&id=',
            'SQRL' => '/en/private/sales_service/sales.php?m[0]=sqrl&m[1]=view&id=',
            'SR' => '/en/private/sales_service/service.php?m[0]=sr&m[1]=view&id=',
            'ST' => '/en/private/calendar/calendar.php?m[0]=st&m[1]=view&id=',
            'TASK' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=',
            'TASK2' => '/en/private/tasks/',
            'TOC' => '/en/private/service/technician-on-calls/',
            'TTS' => '/en/private/mis/mis.php?m[0]=tts&m[1]=view&id=',
            'TTS2' => '/en/private/mis/trouble-tickets/',
            'TRACKING' => '/en/private/finance/finance.php?m[0]=ps&id=',
            'USER' => '/en/private/directory/index.php?m[0]=people&m[1]=view&id=',
            'VU' => '/en/private/manufacturing/pur/dev.php?m[0]=vendors&m[1]=view&id=',
            'VWC' => '/en/private/manufacturing/pur/dev.php?m[0]=vwc&m[1]=view&id=',
            'WC' => '/en/private/product_support/index.ps.php?m[0]=wc&m[1]=view&id=',
            'XU' => '/en/private/sales_service/sales.php?m[0]=extranet&m[1]=view&id=',
            'MIS' => '/en/private/mis/projects/',
            'GUEST USER' => '/en/private/mis/guest-users/',
        ];
    }

    // DO NOT DELETE - Statistiques access ------>
    public static function logUserAccess()
    {
        global $_SERVER, $user;
        // Split URL to figure out module
        $uri_array = parse_url(urldecode($_SERVER['REQUEST_URI']));
        $request = preg_split("#(m\[[0-9]+\]=)|((\w|-)+=(\w|-)+)|(\&)#", $uri_array['query'] ?? '', 0, PREG_SPLIT_NO_EMPTY);
        // Create array data
        $LOG_DATA = [
            'user_id' => $user->getID(),
            'user_email' => $_SERVER['PHP_AUTH_USER'],
            'user_env' => $_SERVER['HTTP_USER_AGENT'],
            'user_ip' => self::getClientIp(),
            'request_type' => $_SERVER['REQUEST_METHOD'],
            'request_uri' => $_SERVER['REQUEST_URI'],
            'request_m0' => isset($request[0]) ? $request[0] : '',
            'request_m1' => isset($request[1]) ? $request[1] : '',
            'request_m2' => isset($request[2]) ? $request[2] : '',
            'request_m3' => isset($request[3]) ? $request[3] : '',
            'request_m4' => isset($request[4]) ? $request[4] : '',
            'request_m5' => isset($request[5]) ? $request[5] : '',
        ];
        $SET = self::getSqlSet(self::cleanupFormInput($LOG_DATA));
        $query = "INSERT INTO stats_access_log SET dt=NOW(),$SET";
        $e = self::sqlInsert($query);
    }

    public function getPortalList()
    {
        global $PORTAL_DIR;
        $portalList = array_keys($PORTAL_DIR);
        return array_combine($portalList, $portalList);
    }

    public static function getPortal()
    {
        global $PORTAL_DIR, $_SERVER;
        foreach ($PORTAL_DIR as $portal => $dir) {
            if (stripos($_SERVER['SCRIPT_FILENAME'], $dir) === 0) {
                return $portal;
            }
        }
    }

    /**
     * Extract the real IP address of the client.
     * Since there might be proxies between the client and this server
     * the 'REMOTE_ADDR' value might not be the true client IP.
     * If proxies were involved, we can rely on HTTP_X_FORWARDED_FOR
     *
     * https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Forwarded-For
     *
     * @return string
     */
    public static function getClientIp()
    {
        global $_SERVER;

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $forwardedIps = array_values(
                array_filter(
                    explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])
                )
            );
            return end($forwardedIps);
        }

        return $_SERVER['REMOTE_ADDR'];
    }

    public static function getUserAndRequestInfo()
    {
        global $_SERVER, $user;
        // retrieve user info
        if (is_object($user)) {
            $uid = $user->getID();
            $userid = $user->getUserid();
        }
        // Identify portal
        $portal = self::getPortal();
        // Return all info
        return [
            'user_id' => $uid ?? null,
            'user_login' => $userid ?? null,
            'user_env' => $_SERVER['HTTP_USER_AGENT'],
            'user_ip' => self::getClientIp(),
            'request_time' => date('Y-m-d H:m:i'),
            'request_type' => $_SERVER['REQUEST_METHOD'],
            'request_uri' => $_SERVER['REQUEST_URI'],
            'portal' => $portal,
        ];
    }

    /**
     * @return \Symfony\Component\Stopwatch\Stopwatch|null
     */
    public static function getStopWatch()
    {
        if (class_exists('Symfony\Component\Stopwatch\Stopwatch', false)) {
            return new \Symfony\Component\Stopwatch\Stopwatch(true);
        }

        return null;
    }
    
    /**
     *  Behavior:
     *  - If $var contains HTML tags -> return as-is.
     *  - Otherwise -> escape text and apply nl2br.
     * @param $s
     * @param $charset
     * @return mixed|string
     */
    public static function renderHtmlOrNl2br($s, $charset = 'ISO-8859-1')
    {
        if ($s === null || $s === '') {
            return '';
        }

        $pattern = '/<\/?\s*(?:p|br|a|strong|b|em|u|s|ul|ol|li|h[1-6]|blockquote|pre|code|div|span|hr|table|thead|tbody|tfoot|tr|td|th|img)\b[^>]*>/iu';

        $converted = mb_convert_encoding($s, 'UTF-8', $charset);
        $hasHtml   = (preg_match($pattern, $converted) === 1);

        if ($hasHtml) {
            // When the returned HTML is injected into a JavaScript string (e.g. overlib),
            // raw line breaks (\r, \n) would break the JS syntax.
            // Removing them has no impact on HTML rendering and prevents JS errors.
            return str_replace(
                ["\r\n", "\r", "\n", '\\r\\n', '\\r', '\\n'],
                '',
                $s
            );
        }
        // Plain-text path: escape then convert newlines to <br>
        $escaped = htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, $charset, false);
        return nl2br($escaped, false); // false => <br> (not <br />)
    }

}

/**
 * Basic webpage class
 *
 * @package Utilities
 */
class basicHTMLPage
{
    public $itsHTMLTitle;

    public function getHeader()
    {
        return "<html>\n" .
            '<head><title>' . $this->itsHTMLTitle . "</title>\n" .
            "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">\n" .
            "<link href=\"/tld-gse.css\" rel=\"stylesheet\" type=\"text/css\">\n" .
            "</head><body>\n";
    }

    public function getMenu()
    {
        return '';
    }

    public function getFooter()
    {
        return '</body></html>';
    }
}

/**
 * Basic file class
 *
 * Functions to manipulate files on the server
 *
 * WARNING -> once in PHP 5.3, need to update some functions using PECL Fileinfo
 * http://www.php.net/manual/en/ref.fileinfo.php
 *
 * @package Utilities
 */
class basicFile
{

    /** @var string  */
    public $itsFilepath;

    /**
     * Constructor
     * @param string $filepath
     */
    public function __construct($filepath)
    {
        $this->itsFilepath = $filepath;
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->itsFilepath = null;
    }

    /**
     * Check if it is a valid file
     * @return boolean
     */
    public function isFile()
    {
        return is_file($this->itsFilepath);
    }

    /**
     * Check if file exists
     * @return boolean
     */
    public function isFileExists()
    {
        return !(null === $this->itsFilepath) && file_exists($this->itsFilepath);
    }

    /**
     * Check if file is readable
     * @return boolean
     */
    public function isReadable()
    {
        return is_readable($this->itsFilepath);
    }

    /**
     * Check if file is writable
     * @return boolean
     */
    public function isWritable()
    {
        return is_writable($this->itsFilepath);
    }

    /**
     * Check if file can be executed
     * @return boolean
     */
    public function isExecutable()
    {
        return is_executable($this->itsFilepath);
    }

    /**
     * Get the file MIME
     * @return string
     */
    public function getMime()
    {
        return mime_content_type($this->itsFilepath);
    }

    /**
     * Get the file type from MIME
     * @return string
     */
    public function getType()
    {
        $mime = explode('/', $this->getMime());
        return $mime[0];
    }

    /**
     * Get the file size
     * @param string $um
     * @return float
     */
    public function getSize($um = '')
    {
        $size = filesize($this->itsFilepath);
        $um = strtoupper($um);
        switch ($um) {
            case 'MB':
                $umDivision = 1024 ** 2;
                break;
            case 'KB':
                $umDivision = 1024;
                break;
            default:
                $umDivision = 1;
                break;
        }
        return round($size / $umDivision, 2);
    }

    /**
     * Get the file directory
     * @return string
     */
    public function getPath()
    {
        $pathinfo = pathinfo($this->itsFilepath);
        return $pathinfo['dirname'] ?? null;
    }

    /**
     * Get the file name (name + extension)
     * @return string
     */
    public function getBasename()
    {
        $pathinfo = pathinfo($this->itsFilepath);
        return $pathinfo['basename'] ?? null;
    }

    /**
     * Get the file name (name WITHOUT extension)
     * @return string
     */
    public function getFileName()
    {
        $pathinfo = pathinfo($this->itsFilepath);
        return $pathinfo['filename'] ?? null;
    }

    /**
     * Get the file extension using pathinfo()
     * @return string
     */
    public function getFileExtension()
    {
        $pathinfo = pathinfo($this->itsFilepath);
        return $pathinfo['extension'] ?? null;
    }

    /**
     * Get the file extension from the file name
     * @return string
     */
    public static function getExtensionFromFileName($fileName)
    {
        $array = explode('.', $fileName);
        return end($array);
    }

    /**
     * Get the complete file path
     * @return string
     */
    public function getFilePath()
    {
        return $this->itsFilepath;
    }

    /**
     * Get file contents
     * @return string
     */
    public function getContents()
    {
        return file_get_contents($this->itsFilepath);
    }

    /**
     * Get MD5 of the file
     * @return string
     */
    public function getMD5()
    {
        return md5_file($this->itsFilepath);
    }

    /**
     * Get file content in base64
     * @return string
     */
    public function getBase64()
    {
        return base64_encode($this->getContents());
    }

    /**
     * Rename the file to a new name
     * @param string $newFileName
     * @return boolean
     */
    public function rename($newFileName)
    {
        if (is_file($this->getPath() . '/' . $newFileName)) {
            return false;
        }
        $newPathFile = $this->getPath() . '/' . $newFileName;
        $e = rename($this->itsFilepath, $newPathFile);
        if ($e) {
            $this->itsFilepath = $newPathFile;
        }
        return $e;
    }

    /**
     * Copy the file to a new file path
     * @param string $destFilePath (path + filename)
     * @return boolean
     */
    public function copy($destFilePath)
    {
        if (is_file($destFilePath)) {
            return false;
        }
        return copy($this->itsFilepath, $destFilePath);
    }

    /**
     * Move the file to a new file path
     * @param string $destFilePath (path + filename)
     * @return boolean
     */
    public function move($destFilePath)
    {
        if (is_file($destFilePath)) {
            return false;
        }
        return rename($this->itsFilepath, $destFilePath);
    }

    /**
     * Delete the file completely
     * @return boolean
     */
    public function delete()
    {
        return unlink($this->itsFilepath);
    }

    /**
     * Get MIME from file extension
     * @return string
     */
    public function getMimeTypeFromExtension()
    {
        $query = "SELECT * FROM mimetypes WHERE ext LIKE '{$this->getFileExtension()}'";
        $row = tldUtils::getSQLRowToAssocArray($query);
        if (empty($row['mimetype'])) {
            return 'application/force-download';
        }
        return $row['mimetype'];
    }

    /**
     * Send HTTP header to force download
     * @param string $inline (option to modify the way user will get/open the file)
     * @return string
     */
    public function sendHTTPFileHeader($newFileName = '', $inline = false)
    {
        // look for parameters
        $fileName = $this->getBasename();
        if (!empty($newFileName)) {
            $fileName = $newFileName;
        }
        $contentDisposition = 'attachment';
        if ($inline) {
            $contentDisposition = 'inline';
        }
        $mime = $this->getMimeTypeFromExtension();
        // Send header to browser client
        header("Content-Type: $mime");
        header("Content-Disposition: $contentDisposition; filename=\"$fileName\"");
        header('Pragma: public');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Length: ' . $this->getSize());
    }

    /**
     * Send the file to broswer
     * @param string $inline (option to modify the way user will get/open the file)
     * @return string
     */
    public function out($newFileName = '', $inline = '')
    {
        $this->sendHTTPFileHeader($newFileName, $inline);
        echo $this->getContents();
    }

    /**
     * Clean file name
     * @param string $filename
     * @param int $maxLen
     * @return string
     */
    public static function cleanupName($filename, $maxLen = 50)
    {
        $result = '';
        $len = strlen($filename);
        if ($len > $maxLen) {
            $filename = substr($filename, $len - $maxLen);
        }
        for ($myCharIndex = strlen($filename) - 1; $myCharIndex >= 0; $myCharIndex--) {
            $myChar = $filename[$myCharIndex];
            $asciiVal = ord($myChar);
            if (($asciiVal > 47 && $asciiVal < 58) ||
                ($asciiVal > 64 && $asciiVal < 91) ||
                ($asciiVal > 96 && $asciiVal < 123) ||
                $asciiVal == 46
            ) {
                $result = $myChar . $result;
            }
        }
        return $result;
    }

    public static function generateFilename()
    {
        return md5(time() . mt_rand());
    }

    /**
     * ----------------------------------------------------
     *          FOLLOWING methods are DEPRECATED !!!
     * ----------------------------------------------------
     *
     * @todo migrate following functions to above functions in logics
     * @todo delete old basicFile methods
     *
     */

    /**
     * DEPRECATED
     */
    public function getFile()
    {
        return file_get_contents($this->itsFilepath);
    }

    /**
     * DEPRECATED
     */
    public static function getMediaType($media)
    {

        $pictureTypes = ['jpg', 'png', 'gif'];
        $videoTypes = ['avi', 'wmv', 'mov', 'mpeg'];
        $docTypes = ['doc', 'xls', 'ppt', 'pdf', 'txt'];
        $archiveTypes = ['zip', 'rar'];

        if (in_array(strtolower(substr($media, -3)), $pictureTypes, true)) {
            return 'picture';
        }
        if (in_array(strtolower(substr($media, -3)), $videoTypes, true)) {
            return 'video';
        }
        if (in_array(strtolower(substr($media, -3)), $docTypes, true)) {
            return 'document';
        }
        if (in_array(strtolower(substr($media, -3)), $archiveTypes, true)) {
            return 'archive';
        }

        return 'unknown';
    }

    /**
     * DEPRECATED
     */
    public function copyFile($dest)
    {
        $result = false;
        if ($this->fileExists()) {
            $result = !file_exists($dest) && copy($this->itsFilepath, $dest);
        }

        return $result;
    }

    /**
     * DEPRECATED
     */
    public function outFile($repName = '', $inline = '')
    {
        if ($this->fileExists()) {
            $this->sendHTTPFileHeader($repName, $inline);
            echo $this->getfile();
        } else {
            echo 'No file available.';
            return false;
        }
    }

    /**
     * DEPRECATED
     */
    public function fileExists()
    {
        return file_exists($this->itsFilepath ?? '');
    }
    // <--- END of deprecated methods
}

/**
 * TLD file class
 * -> centralised class file to handle security and access log
 * @package Utilities
 */
class tldFile
{

    public $itsID;
    public $itsHeader;
    public $itsFile;
    const fileNameLengthLimit = 100;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
        $this->itsFile = new basicFile($this->getFilepath());
    }

    /**
     * Get the file ID
     * @return int
     */
    public function getID()
    {
        return $this->itsID;
    }

    /**
     * Get the file path
     * @return string
     */
    public function getFilepath()
    {
        return $this->itsHeader['filepath'] ?? null;
    }

    /**
     * Get the original file name
     * @return string
     */
    public function getOriginalFileName()
    {
        return $this->itsHeader['filename'];
    }

    public function getExtension()
    {
        return $this->itsHeader['extension'];
    }

    /**
     * Check if the file entry exists
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    /**
     * Check if the physical file exists
     * @return boolean
     */
    public function isFile()
    {
        return $this->itsFile->isFile();
    }

    /**
     * Get file entry header
     * @return array row
     */
    public function getHeader()
    {
        $query = "SELECT * FROM file WHERE id=$this->itsID";
        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Add file log event
     * @param string $action (type of action: OUT/DELETE)
     */
    private function addLog($action)
    {
        $requestInfo = tldUtils::getUserAndRequestInfo();
        $a = [
            'parent_id' => $this->itsID,
            'portal' => $requestInfo['portal'],
            'userid' => $requestInfo['user_login'],
            'ip' => $requestInfo['user_ip'],
            'env' => $requestInfo['user_env'],
            'uri' => $requestInfo['request_uri'],
            'action' => $action,
        ];
        $SET = tldUtils::getSqlSet(tldUtils::cleanupFormInput($a));
        $query = "INSERT INTO file_log SET dt=NOW(), $SET";
        return tldUtils::sqlInsert($query);
    }

    /**
     * Get file log event
     * @return array
     */
    public function getLog()
    {
        $query = "SELECT * FROM file_log WHERE parent_id=$this->itsID";
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Insert file entry and info in DB
     * @param array
     * @return string on error, int on success
     */
    private static function insert($a)
    {
        $SET = tldUtils::getSqlSet($a);
        $query = "INSERT INTO file SET dt=NOW(), $SET";
        return tldUtils::sqlInsert($query);
    }

    /**
     * Upload file to server
     * WARNING -> ALL UPLOAD FILE ARE AND MUST BE UNDER UPLOAD FOLDER !!!
     * @param string $srcFilepath (temp or file location)
     * @param string $dirDestination (directory under UPLOAD dir)
     * @param string $originalFilename (original file name)
     * @return string on error
     */
    public static function upload($srcFilepath, $dirDestination, $originalFilename)
    {
        // Check original file name length
        $fileNameLengthLimit = self::fileNameLengthLimit;
        if (strlen($originalFilename) > $fileNameLengthLimit) {
            return "File name Length too long (limit is $fileNameLengthLimit chars)";
        }
        // Check directory
        if (empty($dirDestination)) {
            return 'Destination directory empty';
        }
        $destPath = $GLOBALS['UPLOADS_PATH'] . '/' . $dirDestination;
        if (!is_dir($destPath)) {
            return sprintf("Destination directory '%s' not valid", $destPath);
        }
        // Check file source
        $uploadFile = new basicFile($srcFilepath);
        if (!$uploadFile->isFile()) {
            return 'Source file not found';
        }
        $newFilename = basicFile::generateFilename();
        // Copy file to destination
        $newFilepath = $destPath . '/' . $newFilename;
        if (!$uploadFile->copy($newFilepath)) {
            return 'Can not copy uploaded file to destination';
        }
        // if ok, finally record in DB
        $originalExtension = explode('.', $originalFilename);
        $originalExtension = $originalExtension[count($originalExtension) - 1];
        $requestInfo = tldUtils::getUserAndRequestInfo();
        $a = [
            'portal' => $requestInfo['portal'],
            'poster' => $requestInfo['user_login'],
            'filepath' => $newFilepath,
            'md5' => $uploadFile->getMD5(),
            'mime' => $uploadFile->getMime(),
            'extension' => $originalExtension,
            'size' => $uploadFile->getSize(),
            'filename' => $originalFilename,
        ];
        return self::insert(tldUtils::cleanupFormInput($a));
    }

    /**
     * Archive file DB info
     * @return string on error, int on success
     */
    private function archive()
    {
        $query = "INSERT INTO file_archive SELECT * FROM file WHERE id=$this->itsID LIMIT 1";
        return tldUtils::sqlInsert($query);
    }

    /**
     * Delete file entry table
     * Archive file info
     * Remove file from FS
     * @return boolean
     */
    public function delete()
    {
        // Delete the file from FS
        if (!$this->itsFile->delete()) {
            return 'Can not physically delete file';
        }
        // Log action
        $this->addLog('DELETE');
        // Archive file DB info
        $this->archive();
        // Delete file DB info
        $query = "DELETE FROM file WHERE id=$this->itsID LIMIT 1";
        return tldUtils::sqlExecute($query);
    }

    /**
     * Download the file
     * @return httpd response containing the file
     */
    public function download($filename = '', bool $confidential = false)
    {
        if ($this->isFile()) {
            $this->addLog('OUT');
            $outFileName = $this->getOriginalFileName();
            if (!empty($filename)) {
                $outFileName = $filename;
            }

            if ($confidential) {
                $outFileName = sprintf('confidential-%s', $outFileName);
            }

            $this->itsFile->out($outFileName);
        } else {
            echo 'File not found...';
        }
    }

    /**
     * Search file by Constraints
     * @param array $a
     * @param array $opt
     * @return array of rows
     */
    public static function byConstraints($a, $opt = '')
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        // look for constraints
        if (!empty($WHERE)) {
            $WHERE = " WHERE $WHERE ";
        }
        // Query the data
        $query = <<<EOF
        SELECT * FROM file $WHERE ORDER BY id
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }
}

class tldFileArchive
{

    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Get the file ID
     * @return int
     */
    public function getID()
    {
        return $this->itsID;
    }

    /**
     * Check if the file entry exists
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    /**
     * Get file archive entry header
     * @return array row
     */
    public function getHeader()
    {
        $query = "SELECT * FROM file_archive WHERE id=$this->itsID";
        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Get list of archived file by constraints
     * @param array $a
     * @param array $opt
     * @return array of rows
     */
    public static function byConstraints($a, $opt)
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        // look for constraints
        if (!empty($WHERE)) {
            $WHERE = " WHERE $WHERE ";
        }
        // Query the data
        $query = <<<EOF
        SELECT * FROM file_archive $WHERE ORDER BY id
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }
}

class tldFileImage extends basicFile
{

    public function __construct($filepath)
    {
        parent::__construct($filepath);
    }

    public function toHtml($htmlOptions = '')
    {
        return <<<EOF
<img src="data:image/{$this->getExtensionFromFileName('')};base64,{$this->getBase64()}" $htmlOptions>
EOF;
    }
}

class tldFileJPG extends basicFile
{
    public function outFile($repName = '', $inline = '', $options = '')
    {
        if (!file_exists($this->itsFilepath)) {
            return;
        }

        header('Content-Type: image/jpeg');

        if (!in_array($options['width'], [1024, 512, 256, 128, 64])) {
            parent::outFile($repName, $inline);
            return;
        }

        [$width, $height] = getimagesize($this->itsFilepath);
        $new_width = $options['width'];
        $new_height = $new_width * $height / $width;
        $image = imagecreatefromjpeg($this->itsFilepath);
        if ($image) {
            $new_image = imagecreatetruecolor((int) $new_width, (int) $new_height);
            imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            imagejpeg($new_image, null, 100);
        }
    }
}


class tldFileZIP extends basicFile
{
    public $itsFilelist;

    public function __construct($filepath)
    {
        parent::__construct($filepath);
        $this->itsFilepath = $filepath;
    }

    function addFile($filepath)
    {
        if (file_exists($filepath)) {
            $this->itsFilelist[] = $filepath;
        }
    }

    function out($newFileName = '', $inline = '')
    {
        shell_exec("zip -q -j $this->itsFilepath '" . implode("' '", $this->itsFilelist) . "'");
        $this->outFile($newFileName);
    }
}

class tldFileCSV extends basicFile
{
    /** @var array */
    public $itsDetails = [];

    public function __construct($filepath)
    {
        parent::__construct($filepath);
        $this->itsDetails = $this->asArray();
    }

    public function asArray(): array
    {
        $result = [];
        //		ini_set('auto_detect_line_endings', true);
        $handle = fopen($this->itsFilepath, 'r');
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $result[] = $data;
        }
        fclose($handle);

        return $result;
    }

    public function getColumn($x): ?array
    {
        $result = [];
        if (count($this->itsDetails) === 0) {
            return null;
        }
        foreach ($this->itsDetails as $row) {
            $result[] = $row[$x];
        }
        return $result;
    }
}

/**
 * Class for accessing links for modules
 *
 * @package Utilities
 */
class tldModLink
{

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    /**
     * Get row information from table
     *
     * @return array
     */
    public function getHeader()
    {
        $query = <<<EOF
		SELECT * FROM mod_links
		WHERE id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Get id of current link
     *
     * @return integer
     */
    public function getID()
    {
        return $this->itsID;
    }

    /**
     * Get name of module link points to
     *
     * @return string
     */
    public function getModule()
    {
        return $this->itsHeader['module'];
    }

    /**
     * Get parent id link points to
     *
     * @return integer
     */
    public function getParentID()
    {
        return $this->itsHeader['parent_id'];
    }

    /**
     * Get parent id link points to
     *
     * @return integer
     */
    public function getItem()
    {
        return $this->itsHeader['item'];
    }

    /**
     * Get parent id link points to
     *
     * @return integer
     */
    public function getType()
    {
        return $this->itsHeader['type'];
    }

    /**
     * Insert a link into the database
     * @param $a array
     * <code>
     * array("parent_id"=>ID number of parent,
     *        "module"=>name of associated module,
     *        "type"=>module to link to,
     *        "item"=>ref of link
     * )
     * </code>
     *
     * @return boolean
     */
    public static function insert($module, $parent_id, $type, $item)
    {
        global $DEFAULT_ERROR;

        // Check for existing duplicate before inserting
        $checkQuery = <<<EOF
    SELECT id FROM mod_links
    WHERE parent_id = $parent_id
      AND module = '$module'
      AND type = '$type'
      AND item = '$item'
    LIMIT 1
EOF;

        $existing = tldUtils::getSqlRowToAssocArray($checkQuery);

        if (!empty($existing)) {
            $DEFAULT_ERROR[] = "WARNING: This link already exists and was not duplicated.";
            return false;
        }

        $query = <<<EOF
    INSERT INTO mod_links
    SET parent_id=$parent_id,
        module='$module',
        type='$type',
        item='$item'
EOF;

        return tldUtils::sqlInsert($query);
    }

    /**
     * Delete this particular mod_link line
     *
     * return mixed
     */
    public static function delete($id)
    {
        if (empty($id)) {
            return;
        }
        $query = <<<EOF
		DELETE FROM mod_links
		WHERE id=$id
		LIMIT 1
EOF;
        return tldUtils::sqlQuery($query);
    }

    /**
     * Get link entries by parent_id
     *
     * @return array of db rows
     */
    public static function byParent($parent_id, $module = '', $type = '', $item = '', $typeIsNot = ""): array
    {
        if (!empty($parent_id) && !is_array($parent_id)) {
            $parent_id = [$parent_id];
        }

        $WHERE = '';
        if (count(is_countable($parent_id) ? $parent_id : []) > 0) {
            $WHERE = sprintf("parent_id IN (%s)", implode(', ', $parent_id));
        }

        if ($type) {
            $WHERE = $WHERE === '' ? sprintf("%s type='%s'", $WHERE, $type) : sprintf("%s AND type='%s'", $WHERE, $type);
        }
        if ($module) {
            $WHERE = $WHERE === '' ? sprintf("%s module='%s'", $WHERE, $module) : sprintf("%s AND module='%s'", $WHERE, $module);
        }

        if ($typeIsNot) {
            $WHERE = $WHERE === '' ? sprintf("%s type<>'%s'", $WHERE, $typeIsNot) : sprintf("%s AND type<>'%s'", $WHERE, $typeIsNot);
        }
        if ($item) {
            $WHERE = $WHERE === '' ? sprintf("%s item='%s'", $WHERE, $item) : sprintf("%s AND item='%s'", $WHERE, $item);
        }
        $query = <<<EOF
		SELECT t1.*,
            CASE type
            WHEN 'BP' THEN (SELECT CONCAT('BP', cal_bp.id, ', ', short_desc)
                            FROM cal_bp WHERE t1.item=cal_bp.id)
            WHEN 'CPA' THEN (SELECT CONCAT('CPA', cpa.id, ', ', short_desc)
                            FROM cpa WHERE t1.item=cpa.id)
            WHEN 'CSR' THEN (SELECT CONCAT('CSR#', csr.id, ', ', status, ', ', short_desc)
                            FROM csr WHERE t1.item=csr.id)
            WHEN 'DMS' THEN (SELECT CONCAT('DMS',dms.id,', ',dms.title,', ',dms.subject)
                            FROM dms WHERE t1.item=dms.id)
            WHEN 'EAP' THEN (SELECT CONCAT('EAP', eap.id, ', ', short_desc, ', ', status)
                            FROM eap WHERE t1.item=eap.id)
            WHEN 'MEAP' THEN (SELECT CONCAT('MEAP', meap.id, ', ', short_desc)
                            FROM meap WHERE t1.item=meap.id)
            WHEN 'ER' THEN (SELECT CONCAT('ER', service.id, ', ', sn, ', ', model, ', ', customer_name)
                            FROM service WHERE t1.item=service.id)
            WHEN 'GWF' THEN (SELECT CONCAT('GWF', gwf.id, ', ', dsca, ' - ',gwf.status)
                            FROM gwf WHERE t1.item=gwf.id)
            WHEN 'PDC' THEN (SELECT CONCAT('PDC', demerit.id, ', ', short_desc, ', ', status)
                            FROM demerit WHERE t1.item=demerit.id)
            WHEN 'SB' THEN (SELECT CONCAT('SB ', sbs.id,
                             ',', status, ', ', sb_type, ', ', factory)
                             FROM sbs WHERE t1.item=sbs.id)
            WHEN 'SB3' THEN (SELECT CONCAT('SB3 ', sb.id,
                             ', ', status, ', ', category, ', ', title)
                             FROM sb WHERE t1.item=sb.id)
            WHEN 'SEQ' THEN (SELECT CONCAT('SEQ', tasks.id, ', ', task)
                            FROM tasks WHERE t1.item=tasks.id)
            WHEN 'SFR' THEN (SELECT CONCAT('SFR', sfr.id, ', ', qty, ', ', model, ', ', cust_nama)
                            FROM sfr WHERE t1.item=sfr.id)
            WHEN 'SOR' THEN (SELECT CONCAT('SOR', sor.id, ', ', status, ', ', cu_nama)
                             FROM sor WHERE t1.item=sor.id)
            WHEN 'SOL' THEN (SELECT CONCAT('SOL', sor_lines.id, ', ', status, ', ', model)
                            FROM sor_lines WHERE t1.item=sor_lines.id)
            WHEN 'SPR' THEN (SELECT CONCAT('SPR', spr.id, ', ', status, ', ', cust_nama)
                            FROM spr WHERE t1.item=spr.id)
            WHEN 'TTS' THEN (SELECT CONCAT('TTS', mis_tts.id, ', ', problem)
                            FROM mis_tts WHERE t1.item=mis_tts.id)
            WHEN 'TASK' THEN (SELECT CONCAT('TASK', tasks.id, ', ', tasks.task)
                            FROM tasks WHERE t1.item=tasks.id)
            WHEN 'USER' THEN (SELECT CONCAT('USER', tasks.id, ', ', task)
                            FROM tasks WHERE t1.item=tasks.id)
            WHEN 'WC' THEN (SELECT CONCAT('WC', warranty.id, ', ', problem_desc, ', ', warranty_status)
                            FROM warranty WHERE t1.item=warranty.id)
            WHEN 'TOC' THEN (SELECT CONCAT('TOC', toc.id, ', ', toc.short_desc, ', ', status)
                            FROM toc WHERE t1.item=toc.id)
            WHEN 'ST' THEN (SELECT SUBSTR(cal_st.description, 1, 50)
                            FROM cal_st WHERE t1.item=cal_st.id)
            WHEN 'PIP' THEN (SELECT CONCAT('PIP', pip.id, ', ', short_desc, ', ', status)
                        FROM pip WHERE t1.item=pip.id)
            ELSE
            	''
            END AS dsca
		FROM mod_links AS t1
		WHERE 
		$WHERE
        HAVING dsca IS NOT null
		ORDER BY id DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get link entries by item
     *
     * @return array of db rows
     */
    public static function byItem($item, $type = '', $module = '')
    {
        if (!empty($item) && !is_array($item)) {
            $item = [$item];
        }

        $WHERE = '';
        if (count(is_countable($item) ? $item : []) > 0) {
            $WHERE = sprintf("item IN (%s)", implode(', ', $item));
        }

        if ($type) {
            $WHERE = $WHERE === '' ? sprintf("%s type='%s'", $WHERE, $type) : sprintf("%s AND type='%s'", $WHERE, $type);
        }
        if ($module) {
            $WHERE = $WHERE === '' ? sprintf("%s module='%s'", $WHERE, $module) : sprintf("%s AND module='%s'", $WHERE, $module);
        }
        $query = <<<EOF
		SELECT t1.*,
            CASE module
            WHEN 'BP' THEN (SELECT CONCAT('BP', cal_bp.id, ', ', short_desc)
                            FROM cal_bp WHERE t1.parent_id=cal_bp.id)
            WHEN 'CPA' THEN (SELECT CONCAT('CPA', cpa.id, ', ', short_desc)
                            FROM cpa WHERE t1.parent_id=cpa.id)
            WHEN 'CSR' THEN (SELECT CONCAT('CSR#', csr.id, ', ', status, ', ', short_desc)
                            FROM csr WHERE t1.parent_id=csr.id)
            WHEN 'DMS' THEN (SELECT CONCAT('DMS',dms.id,', ',dms.title,', ',dms.subject)
                            FROM dms WHERE t1.parent_id=dms.id)
            WHEN 'EAP' THEN (SELECT CONCAT('EAP', eap.id, ', ', short_desc, ', ', status)
                            FROM eap WHERE t1.parent_id=eap.id)
            WHEN 'MEAP' THEN (SELECT CONCAT('MEAP', meap.id, ', ', short_desc)
                            FROM meap WHERE t1.parent_id=meap.id)
            WHEN 'ER' THEN (SELECT CONCAT('ER', service.id, ', ', sn, ', ', model, ', ', customer_name)
                            FROM service WHERE t1.parent_id=service.id)
            WHEN 'GWF' THEN (SELECT CONCAT('GWF', gwf.id, ', ', dsca)
                            FROM gwf WHERE t1.parent_id=gwf.id)
            WHEN 'PDC' THEN (SELECT CONCAT('PDC', demerit.id, ', ', short_desc, ', ', status)
                            FROM demerit WHERE t1.parent_id=demerit.id)
            WHEN 'SB' THEN (SELECT CONCAT('SB ', sbs.id,
                             ',', status, ', ', sb_type, ', ', factory)
                             FROM sbs WHERE t1.parent_id=sbs.id)
            WHEN 'SB3' THEN (SELECT CONCAT('SB3 ', sb.id,
                             ', ', status, ', ', category, ', ', title)
                             FROM sb WHERE t1.parent_id=sb.id)
            WHEN 'SEQ' THEN (SELECT CONCAT('SEQ', tasks.id, ', ', task)
                            FROM tasks WHERE t1.parent_id=tasks.id)
            WHEN 'SFR' THEN (SELECT CONCAT('SFR', sfr.id, ', ', qty, ', ', model, ', ', cust_nama)
                            FROM sfr WHERE t1.parent_id=sfr.id)
            WHEN 'SOR' THEN (SELECT CONCAT('SOR', sor.id, ', ', status, ', ', cu_nama)
                             FROM sor WHERE t1.parent_id=sor.id)
            WHEN 'SOL' THEN (SELECT CONCAT('SOL', sor_lines.id, ', ', status, ', ', model)
                            FROM sor_lines WHERE t1.parent_id=sor_lines.id)
            WHEN 'SPR' THEN (SELECT CONCAT('SPR', spr.id, ', ', status, ', ', cust_nama)
                            FROM spr WHERE t1.parent_id=spr.id)
            WHEN 'ST' THEN (SELECT SUBSTR(cal_st.description, 1, 50)
                            FROM cal_st WHERE t1.parent_id=cal_st.id)
            WHEN 'TTS' THEN (SELECT CONCAT('TTS', mis_tts.id, ', ', problem)
                            FROM mis_tts WHERE t1.parent_id=mis_tts.id)
            WHEN 'TASK' THEN (SELECT CONCAT('TASK', tasks.id, ', ', tasks.task)
                            FROM tasks WHERE t1.item=tasks.id)
            WHEN 'USER' THEN (SELECT CONCAT('USER', tasks.id, ', ', task)
                            FROM tasks WHERE t1.parent_id=tasks.id)
            WHEN 'WC' THEN (SELECT CONCAT('WC', warranty.id, ', ', problem_desc, ', ', warranty_status)
                            FROM warranty WHERE t1.parent_id=warranty.id)
            WHEN 'TOC' THEN (SELECT CONCAT('TOC', toc.id, ', ', toc.short_desc, ', ', status)
                            FROM toc WHERE t1.parent_id=toc.id)
            ELSE
            	''
            END AS dsca
		FROM mod_links AS t1
		WHERE 
		$WHERE
		ORDER BY id DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get url to base document using module name and parent id
     *
     * @param string $module name of module
     * @param integer $parent_id id of linked documnet
     * @return string
     */
    public static function getURL($module = '', $parent_id = '')
    {
        $links = tldUtils::getModLinks();
        $link = $links[$module];
        if ($link) {
            $urlSuffix = in_array($module, self::getMigratedModules(), true) ? '/show' : '';

            return $link . $parent_id . $urlSuffix;
        }
    }

    /**
     * Return the list of modules where the link needs to be url . id . '/show"
     */
    public static function getMigratedModules()
    {
        return [
            'SPR2',
            'SPQ2',
            'FAQ',
            'DEMO',
            'DEROGATION',
            'SFR2',
            'SOR2',
            'MIM2',
            'TTS2',
            'TASK2',
            'MOM',
            'ATM',
            'AR',
            'MIS',
            'CSR',
            'GUEST USER',
            'TOC',
        ];
    }

    public function getReversedURL()
    {
        $links = tldUtils::getModLinks();
        $link = $links[$this->getModule()];
        if ($link) {
            $urlSuffix = in_array($this->getModule(), self::getMigratedModules(), true) ? '/show' : '';

            return $link . $this->getParentID() . $urlSuffix;
        }
    }
}

/**
 * Class for accessing logs for modules
 *
 * @package Utilities
 */
class tldModLog
{
    public $itsID;
    /**
     * @var mixed
     */
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    public function getHeader()
    {
        $query = <<<EOF
		SELECT mod_logs.*,
            CONCAT(u.lastname,', ',u.firstname) as poster_fullname
        FROM mod_logs LEFT JOIN people AS u ON mod_logs.poster=u.id
		WHERE mod_logs.id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function getCommentByID($id)
    {
        $query = <<<EOF
        SELECT mod_logs.comment
        FROM mod_logs
        WHERE mod_logs.id=$id AND mod_logs.log_num <> 10
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Get id of current link
     * @return integer
     */
    public function getID()
    {
        return $this->itsID;
    }

    public function addLinkTo($type, $item)
    {
        if (empty($this->itsID)) {
            return;
        }
        return tldModLink::insert('LOG', $this->itsID, $type, $item);
    }

    public function getLinksFromHere($type = '')
    {
        return tldModLink::byParent($this->itsID, 'LOG', $type);
    }

    public function getLinksToHere($module = '')
    {
        return tldModLink::byItem($this->itsID, 'LOG', $module);
    }

    /**
     * Get name of module link points to
     * @return string
     */
    public function getModule()
    {
        return $this->itsHeader['module'];
    }

    /**
     * Get parent id link points to
     * @return integer
     */
    public function getParentID()
    {
        return $this->itsHeader['parent_id'];
    }

    /**
     * Insert a comment into the log
     * @param $a array
     *
     * <code>
     * array("parent_id"=>ID number of parent,
     *        "module"=>name of associated module,
     *        "poster"=>id number of person posting comment,
     *        "comment"=>text of comment to post,
     *        "log_num"=>key of the log to get different logs for the same object
     * )
     * </code>
     *
     * @return boolean
     */
    public static function insert($a)
    {
        $a['log_num'] = empty($a['log_num']) ? 0 : (int) $a['log_num'];
        $query = <<<EOF
		INSERT INTO mod_logs
		SET parent_id={$a['parent_id']},
			module='{$a["module"]}',
			date=NOW(),
			poster='{$a["poster"]}',
			comment='{$a["comment"]}',
			log_num={$a['log_num']}
EOF;
        return tldUtils::sqlInsert($query);
    }

    /**
     * Generic Log update method
     *
     * @param $data array of log datas
     *
     * @return string on error
     */
    public function update(array $data)
    {
        if (empty($this->itsID)) {
            return 'Not object context!';
        }
        $SET = tldUtils::getSqlSet($data);
        $query = "UPDATE mod_logs SET $SET WHERE id=$this->itsID LIMIT 1";

        return tldUtils::sqlQuery($query);
    }

    /**
     * Get log entries by parent_id
     * @param $log_num int|array the log level or an array of requested log levels
     * @return array of db rows
     */
    public static function byParent($parent_id, $module = '', $log_num = 0)
    {
        $WHERE = '';
        if ($module) {
            $WHERE = " AND module='$module'";
        }
        if (is_array($log_num)) {
            $log_num = implode(' OR log_num=', $log_num);
        }
        $query = <<<EOF
        SELECT mod_logs.*, CONCAT(a.lastname,', ',a.firstname) as poster_fullname
        FROM mod_logs LEFT JOIN people AS a ON mod_logs.poster=a.id
        WHERE mod_logs.parent_id=$parent_id
        $WHERE AND (log_num=$log_num)
        ORDER BY id DESC
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get log entries by parent_id
     * works for an array of parent_id
     * @param $log_num int|array the log level or an array of requested log levels
     * @return array of db rows
     */
    public static function byParents($parent_id, $module = '', $log_num = 0)
    {
        if (is_array($parent_id)) {
            $parents = implode(' OR mod_logs.parent_id=', $parent_id);
        }
        if (is_array($log_num)) {
            $log_num = implode(' OR log_num=', $log_num);
        }
        if ($module) {
            $WHERE = " AND module='$module'";
        }
        $query = <<<EOF
		SELECT mod_logs.*, concat(a.lastname,', ',a.firstname) as poster_fullname
		FROM mod_logs LEFT JOIN people AS a ON mod_logs.poster=a.id
		WHERE (mod_logs.parent_id=$parents)
		$WHERE AND (log_num=$log_num)
		ORDER BY mod_logs.parent_id, id DESC
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byConstraints($a, $opt = null)
    {
        $WHERE = "WHERE $a";
        if (is_array($a)) {
            $WHERE = 'WHERE ' . tldUtils::constructWhere($a);
        }
        // Look for options
        $ORDERBY = 'ORDER BY id DESC';
        if (!empty($opt['orderBy'])) {
            $ORDERBY = 'ORDER BY ' . $opt['orderBy'];
        }

        $LIMIT = isset($opt['limit']) ? 'LIMIT ' . $opt['limit'] : '';
        $OFFSET = isset($opt['offset']) ? 'OFFSET ' . $opt['offset'] : '';

        $query = <<<EOF
SELECT
    mod_logs.*,
    (SELECT CONCAT(lastname,', ',firstname) FROM people
        WHERE mod_logs.poster=people.id
    ) AS poster_fullname
FROM
    mod_logs
$WHERE
$ORDERBY
$LIMIT
$OFFSET
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }
}

/**
 * Class for accessing lists for modules
 * @package Utilities
 */
class tldModList
{
    /**
     * Id of row in table
     *
     * @var integer
     */
    public $itsID;
    /**
     * Row information from table
     *
     * @var array
     */
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }


    public function getID()
    {
        return $this->itsID;
    }

    public function getParentID()
    {
        return $this->itsHeader['parent_id'];
    }

    public function getModule()
    {
        return $this->itsHeader['module'];
    }

    public function getListName()
    {
        return $this->itsHeader['list_name'];
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    /**
     * Get the row information from the table
     *
     * @return array
     */
    public function getHeader()
    {
        $query = <<<EOF
		SELECT *
		FROM mod_lists
		WHERE id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Insert a key value pair into the lists table
     * @param $a array
     * @return int or string on error
     */
    public static function insert($a)
    {
        $fields = ['parent_id', 'module', 'list_name', 'list_key', 'list_key2', 'value', 'value2'];
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO mod_lists SET $SET";
        return tldUtils::sqlInsert($query);
    }

    /**
     * Update this particular modlist line
     *
     * Only updates the fields list_name, list_key and value
     *
     * @param array $a associative array with the data in key=>value pairs.
     * @return mixed
     */
    public function update($a, $fields = '')
    {
        if (empty($this->itsID)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "UPDATE mod_lists SET $SET WHERE id=$this->itsID LIMIT 1";
        return tldUtils::sqlQuery($query);
    }

    /**
     * Delete this particular modlist line
     *
     * return mixed
     */
    public static function delete($id)
    {
        if (empty($id)) {
            return;
        }
        $query = <<<EOF
		DELETE FROM mod_lists
		WHERE id=$id
		LIMIT 1
EOF;
        return tldUtils::sqlQuery($query);
    }

    /**
     * Get lists entries by parent_id
     *
     * @return array of db rows
     */
    public static function byParent($parent_id, $module, $opts = '')
    {
        $fields = ($opts['fields'] ?? '') ?: '*';
        $mode = ($opts['mode'] ?? '') ?: '';

        //by list name
        if (isset($opts['list_name'])) {
            $w[] = " list_name='" . $opts['list_name'] . "' ";
        }

        //by key
        if (isset($opts['list_key'])) {
            $w[] = " list_key='" . $opts['list_key'] . "' ";
        }
        //by value
        if (isset($opts['value'])) {
            $w[] = " value='" . $opts['value'] . "' ";
        }
        $WHERE = '';
        if (count($w)) {
            $WHERE = ' AND ' . implode(' AND ', $w);
        }

        $query = <<<EOF
		SELECT $fields
		FROM mod_lists
		WHERE
			module='$module' AND parent_id='$parent_id'
			$WHERE
		ORDER BY list_name, list_key, value
EOF;

        return tldUtils::getSqlToAssocArray($query, $mode, $opts['smartyFields'] ?? '');
    }

    public static function byConstraints($a): array
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        $query = <<<EOF
		SELECT *
		FROM mod_lists
		WHERE $WHERE
		ORDER BY list_name, list_key, value
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * As smartyOptions
     */
    public static function byListName($parent_id, $module, $list_name)
    {
        return self::byParent(
            $parent_id,
            $module,
            [
                'list_name' => $list_name,
                'mode' => 'smartyOptions',
                'fields' => 'list_key, value',
                'smartyFields'=> ['list_key', 'value'],
            ]
        );
    }

    /**
     * As rows
     *
     * @param integer $parent_id
     * @param string $module
     * @param string $list_name
     * @return array
     */
    public static function byListNameAsRows($parent_id, $module, $list_name)
    {
        return self::byParent(
            $parent_id,
            $module,
            ['list_name' => $list_name]
        );
    }

    /**
     * Get the list as an associative array
     *
     * @param integer $parent_id
     * @param string $module
     * @param string $list_name
     * @param string $list_key
     * @return array
     */
    public static function byListKey($parent_id, $module, $list_name, $list_key)
    {
        return self::byParent(
            $parent_id,
            $module,
            [
                'list_name' => $list_name,
                'list_key' => $list_key,
                'mode' => 'smartyOptions',
                'fields' => 'value',
                'smartyFields' => 'value',
            ]
        );
    }
}

/**
 * Class for accessing Keys for modules
 *
 * @package Utilities
 */
class tldModKey
{
    /**
     * Id of row in table
     *
     * @var integer
     */
    public $itsID;
    /**
     * Row information from table
     *
     * @var array
     */
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Get the row information from the table
     *
     * @return array
     */
    public function getHeader()
    {
        $query = <<<EOF
		SELECT *
		FROM mod_keys
		WHERE id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Insert into the mod_keys table
     *
     * <code>
     * array("parent_id"=>ID number of parent,
     *        "module"=>name of associated module,
     *        "type"=>type,
     *        "key2"=>key1,
     *        "key2"=>key2,
     *        "key3"=>key3,
     *        "key4"=>key4,
     *        "key5"=>key5
     * )
     * </code>
     *
     * @param $a array
     *
     * @return boolean
     */
    public static function insert($a)
    {
        $query = <<<EOF
		INSERT INTO mod_keys
		SET parent_id={$a['parent_id']},
			module='{$a["module"]}',
			type='{$a["type"]}',
			key1='{a$["key1"]}',
			key2='{$a["key2"]}',
			key3='{$a["key3"]}',
			key4='{$a["key4"]}',
			key5='{$a["key5"]}'
EOF;
        return tldUtils::sqlInsert($query);
    }

    /**
     * Update this particular mod_keys line
     *
     * Only updates the type and keys
     *
     * @param array $a associative array with the data in key=>value pairs.
     * @return mixed
     */
    public function update($a)
    {
        if (empty($this->itsID)) {
            return;
        }
        $query = <<<EOF
		UPDATE mod_keys
		SET type='{$a["type"]}',
			key1='{$a["key1"]}',
			key2='{$a["key2"]}',
			key3='{$a["key3"]}',
			key4='{$a["key4"]}',
			key5='{$a["key5"]}'
		WHERE id=$this->itsID
		LIMIT 1
EOF;
        return tldUtils::sqlQuery($query);
    }

    /**
     * Delete this particular mod_keys line
     *
     * return mixed
     */
    public function delete($id)
    {
        if (empty($id)) {
            return;
        }
        $query = <<<EOF
		DELETE FROM mod_keys
		WHERE id=$id
		LIMIT 1
EOF;
        return tldUtils::sqlQuery($query);
    }

    /**
     * Get lists entries by parent_id
     * @param int $parent_id
     * @param string $module
     * @param array $opts
     * @return array of db rows
     */
    public static function byParent($parent_id, $module, $opts = '')
    {
        $FIELDS = $opts['fields'] ?: '*';
        $mode = $opts['mode'] ?: '';
        // By type
        if (isset($opts['type'])) {
            $w[] = " type='" . $opts['type'] . "' ";
        }
        // By keys - 5 keys (24 August 2010)
        for ($k = 1; $k <= 5; $k++) {
            if (isset($opts["key$k"])) {
                $w[] = " key$k='{$opts["key$k"]}' ";
            }
        }

        $WHERE = '';
        if (count($w)) {
            $WHERE = ' AND ' . implode(' AND ', $w);
        }

        $query = <<<EOF
		SELECT $FIELDS
		FROM mod_lists
		WHERE
			module='$module' AND parent_id='$parent_id'
			$WHERE
		ORDER BY type
EOF;
        return tldUtils::getSqlToAssocArray($query, $mode);
    }
}

/**
 * Class for accessing member lists for modules
 *
 * @package Utilities
 */
class tldModMember
{
    /**
     * Get lists entries by parent_id
     *
     * @return array of db rows
     */
    public static function byParent($parent_id, $module, $opts = ''): array
    {
        $rows = tldModList::byListNameAsRows($parent_id, $module, 'MEMBERS');
        if (count($rows) === 0) {
            return [];
        }
        $r = [];
        foreach ($rows as $row) {
            $user = new tldUser($row['value']);
            if (!$user->isEmpty() && $user->isEnable() && !$user->isDisabled()) {
                $r[] = $user->getHeader();
            }
        }
        return $r;
    }

    public static function hasMembers($parent_id, $module)
    {
        $rows = tldModList::byListNameAsRows($parent_id, $module, 'MEMBERS');

        return count($rows) > 0;
    }

    /**
     * Check if user with UID is a member of this GWF
     *
     * @param mixed $uid
     * @return mixed
     */
    public static function isMember($module, $pid, $uid)
    {
        if (empty($module) || empty($pid) || empty($uid)) {
            return 'ERROR: one or more parameters in tldMember::isMember were empty';
        }
        $rows = tldModList::byListNameAsRows($pid, $module, 'MEMBERS');
        if (count($rows) === 0) {
            return false;
        }
        foreach ($rows as $row) {
            if ($uid == $row['value']) {
                return true;
            }
        }
        return false;
    }

    /**
     * Add a member to the gwf
     *
     * @param integer $uid
     * @return integer id of mod_list item
     */
    public static function insert($module, $pid, $uid)
    {
        if (empty($module) || empty($pid) || empty($uid)) {
            return 'ERROR: one or more parameters in tldMember::insert were empty';
        }
        //check if the user is already a member
        if (self::isMember($module, $pid, $uid)) {
            return;
        }

        //member doesn't exist so add
        return tldModList::insert(
            [
                'parent_id' => $pid,
                'module' => $module,
                'list_name' => 'MEMBERS',
                'value' => $uid,
            ]
        );
    }

    /**
     * Remove a member from the GWF
     *
     * @param integer $cuser userid doing the deleting
     * @param integer $uid userid to delete
     * @return
     */
    public static function delete($module, $pid, $uid)
    {
        if (empty($module) || empty($pid) || empty($uid)) {
            return 'ERROR: one or more parameters in tldMember::delete were empty';
        }
        $rows = tldModList::byParent(
            $pid,
            $module,
            [
                'list_name' => 'MEMBERS',
                'value' => $uid,
            ]
        );
        if (count($rows) == 0) {
            return;
        }

        //member exists so remove
        foreach ($rows as $row) {
            $error = tldModList::delete($row['id']);
        }
        return $error;
    }
}

/**
 * Class for accessing files for modules
 *
 * @package Utilities
 */
class tldModFile
{

    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Get mod_file ID
     * @return integer
     */
    public function getID()
    {
        return $this->itsID;
    }

    /**
     * Is this mod_file empty?
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    /**
     * Get tldFile ID
     * @return integer
     */
    public function getFileID()
    {
        return $this->itsHeader['fid'];
    }

    /**
     * Get filename
     * @return string
     */
    public function getFilename()
    {
        return $this->itsHeader['filename'];
    }

    /**
     * Get module name file is linked to
     * @return string
     */
    public function getModule()
    {
        return $this->itsHeader['module'];
    }

    /**
     * Get id of document file is linked to
     * @return integer
     */
    public function getParentID()
    {
        return $this->itsHeader['parent_id'];
    }

    /**
     * Get level of document file
     * @return integer
     */
    public function getLevel()
    {
        return $this->itsHeader['level'];
    }

    /**
     * Get mod_file header
     * @return array
     */
    public function getHeader()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
WHERE mod_files.id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Get SELECT query part
     * @return string
     */
    public static function getSELECT(): string
    {
        return <<<EOF
SELECT
    mod_files.id,
    mod_files.parent_id,
    mod_files.module,
    mod_files.description,
    mod_files.fid,
    mod_files.level,
    file.dt,
    DATE(file.dt) AS date,
    file.portal,
    file.poster,
    file.poster AS poster_fullname,
    file.filepath,
    file.size,
    file.filename,
    file.extension
EOF;
    }

    /**
     * Get FROM query part
     * @return string
     */
    public static function getFROM(): string
    {
        return <<<EOF
FROM mod_files
    LEFT JOIN file ON file.id=mod_files.fid
EOF;
    }

    /**
     * Send file to stdout
     * @return html header and file contents flow
     */
    public function outFile()
    {
        $file = new tldFile($this->getFileID());
        $confidentials = [
            'MEAP' => 'isPrivate',
            'GWF' => 'isPrivate',
        ];

        $module = $this->getModule();

        $confidential = false;
        if (\array_key_exists($module, $confidentials)) {
            $parentId = $this->getParentID();
            $class = sprintf('tld%s', $module);
            $object = new $class($parentId);

            $method = $confidentials[$module];

            if ($object->{$method}()) {
                $confidential = true;
            }
        }

        $file->download('', $confidential);
    }

    /**
     * Insert and link file to a record module
     * @param $a array
     * @param $file_array array
     * <code>
     * $a = array(
     *  "parent_id"    => ID number of module record,
     *    "module"       => Name of associated module,
     *    "description"  => Description of file
     * )
     * $file_array = array(
     *
     *
     * )
     * </code>
     * @return string|int
     */
    public static function insert($a, $file_array)
    {
        $a = tldUtils::cleanupFormInput($a);
        // Insert file in tldFile and FS
        $fid = tldFile::upload(
            $file_array['tmp_name'],
            'mod_files',
            $file_array['name']
        );
        if (is_string($fid)) {
            return $fid;
        }
        // Add the file ID to modfile data
        $a['fid'] = $fid;
        // If ok, insert data in mod_file
        $fields = ['parent_id', 'module', 'poster', 'description', 'filename', 'fid', 'level'];
        if ($a['expiration_date'] ?? null)  {
            $fields[] = 'expiration_date';
        }
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO mod_files SET $SET, date=NOW()";
        return tldUtils::sqlInsert($query);
    }


    /**
     * Generic ModFile update method
     * @param $data array of datas
     * @param $fields array of ModFile fields to update
     * @return string on error
     */
    public function update($data, $fields = '')
    {
        if (empty($this->itsID)) {
            return 'Not object context!';
        }
        $SET = tldUtils::getSqlSet($data, $fields);
        $query = "UPDATE mod_files SET $SET WHERE id=$this->itsID LIMIT 1";
        return tldUtils::sqlQuery($query);
    }

    /**
     * Delete the current mod_file + file FS
     * @return boolean
     */
    public function delete()
    {
        if (empty($this->itsID)) {
            return;
        }
        $query = "DELETE FROM mod_files WHERE id=$this->itsID LIMIT 1";
        $e = tldUtils::sqlQuery($query);
        if (is_string($e)) {
            return 'Can not delete mod files entry';
        }
        $file = new tldFile($this->getFileID());
        $e = $file->delete();
        return $e;
    }

    /**
     * Get mod file list by Constraints
     * @param $a array|string
     * @return array
     */
    public static function byConstraints($a)
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        // look for constraints
        if (!empty($WHERE)) {
            $WHERE = " WHERE $WHERE ";
        }
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
$WHERE
ORDER BY date
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get file entries by parent_id
     * @param $parent_id int
     * @param $module string
     * @param $level int|null
     * @return array of db rows
     */
    public static function byParent($parent_id, $module = '', $level = 0)
    {
        $a['mod_files.parent_id'] = $parent_id;

        if (null !== $level) {
            $a['level'] = $level;
        }
        if ($module) {
            $a['module'] = $module;
        }
        return self::byConstraints($a);
    }
}

/**
 * Class for accessing generic model data
 *
 * @package Utilities
 */
class tldModModel
{

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    /**
     * Get row information from table
     *
     * @return array
     */
    public function getHeader()
    {
        $query = <<<EOF
		SELECT * FROM mod_models
		WHERE id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Get id of current link
     *
     * @return integer
     */
    public function getID()
    {
        return $this->itsID;
    }

    /**
     * Get name of module link points to
     *
     * @return string
     */
    public function getModule()
    {
        return $this->itsHeader['module'];
    }

    /**
     * Get parent id link points to
     *
     * @return integer
     */
    public function getParentID()
    {
        return $this->itsHeader['parent_id'];
    }

    /**
     * Insert a new mod_model into the database
     *
     * @param string $module
     * @param integer $parent_id
     * @param string $model
     * @return boolean
     */

    public static function insert($module, $parent_id, $model)
    {
        $query = <<<EOF
		INSERT INTO mod_models (parent_id, module, type, model)
		SELECT $parent_id,'$module',T2.en, '$model'
		FROM models AS T1 LEFT JOIN products_categories AS T2 ON T1.parent_id=T2.id
		WHERE T1.model='$model'
EOF;

        return tldUtils::sqlInsert($query);
    }

    /**
     * Get file entries by parent_id
     *
     * @return array of db rows
     */
    public static function byParent($parent_id, $module = '', $mode = '')
    {
        if ($module) {
            $WHERE = " AND module='$module'";
        }
        $query = <<<EOF
		SELECT *
		FROM mod_models
		WHERE parent_id=$parent_id
		$WHERE
		ORDER BY id DESC
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public function delete($id)
    {
        if (empty($id)) {
            return;
        }
        $query = <<<EOF
		DELETE FROM mod_models
		WHERE id=$id
		LIMIT 1
EOF;
        return tldUtils::sqlQuery($query);
    }
}

class tldModKPI
{

    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Get the row information from the table
     * @return array
     */
    public function getHeader()
    {
        $query = <<<EOF
		SELECT *, CONCAT(y,m) AS period
		FROM mod_kpi
		WHERE id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Insert KPI entry
     * @param $a array
     * @return mixed int or string on error
     */
    public static function insert($a)
    {
        $fields = ['parent_id', 'module', 'key1', 'key2', 'key3', 'y', 'm', 'name', 'val', 'comments'];
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO mod_kpi SET $SET";
        return tldUtils::sqlInsert($query);
    }

    /**
     * Update KPI entry
     * @param $a array
     * @return mixed
     */
    public function update($a)
    {
        if (empty($this->itsID) || empty($a)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a);
        $query = "UPDATE mod_kpi SET $SET WHERE id=$this->itsID";
        return tldUtils::sqlQuery($query);
    }

    /**
     * Delete this particular modlist line
     * @param $id int
     * @return mixed
     */
    public function delete($id = '')
    {
        if (empty($id) && empty($this->itsID)) {
            return;
        }
        if (empty($id)) {
            $id = $this->itsID;
        }
        $query = "DELETE FROM mod_kpi WHERE id=$id LIMIT 1";
        return tldUtils::sqlQuery($query);
    }

    /**
     * Get KPI data by constraints
     * @param mixed string or array $a
     * @return array
     */
    public static function byConstraints($a)
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        $query = <<<EOF
		SELECT *, CONCAT(y,m) AS period
		FROM mod_kpi
		HAVING $WHERE
		ORDER BY period
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get KPI data by constraints
     * @param mixed string or array $a
     * @return array
     */
    public static function byConstraintsTPGT($a)
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        $query = <<<EOF
        SELECT *, DATE_FORMAT( key2, '%Y%m%d') AS nam_period, key3 AS cur
        FROM mod_kpi
        HAVING $WHERE
        ORDER BY key2
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get KPI data by period by constraints
     * @param $start int Ym period
     * @param $end int Ym period
     * @param mixed string or array $a
     * @return array
     */
    public function byPeriodConstraints($start, $end, $a)
    {
        if (empty($start) || empty($end)) {
            return;
        }
        $WHERE = "period BETWEEN $start AND $end";
        if (is_array($a)) {
            $WHERE .= ' AND ' . tldUtils::constructWhere($a);
        } else {
            $WHERE .= " AND $a";
        }
        return self::byConstraints($WHERE);
    }

    public static function getPeriodList($start, $end)
    {
        if (empty($start) || empty($end)) {
            return;
        }
        $query = <<<EOF
        SELECT nam_period AS period
        FROM fin_periods
        WHERE nam_period BETWEEN $start AND $end
        ORDER BY nam_period
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byParent($pid, $module, $name = null)
    {
        return self::byConstraints([
            'parent_id' => $pid,
            'module' => $module,
            'name' => $name,
            'key1 NOT' => 'solIncomplete',
        ]);
    }
}

class tldModKPIReview
{
    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    public function getHeader()
    {
        $query = <<<EOF
		SELECT *
		FROM mod_kpireview
		WHERE id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public function getID()
    {
        return $this->itsID;
    }

    public static function insert($a)
    {
        $fields = ['name_id', 'xval', 'zval', 'last_yval'];
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO mod_kpireview SET $SET";
        return tldUtils::sqlInsert($query);
    }

    public function update($a)
    {
        if (empty($this->itsID) || empty($a)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a);
        $query = "UPDATE mod_kpireview SET $SET WHERE id=$this->itsID";
        return tldUtils::sqlQuery($query);
    }

    public function delete($id = '')
    {
        if (empty($id) && empty($this->itsID)) {
            return;
        }
        if (empty($id)) {
            $id = $this->itsID;
        }
        $query = "DELETE FROM mod_kpireview WHERE id=$id LIMIT 1";
        return tldUtils::sqlQuery($query);
    }

    public function insertComment($user, $comment)
    {
        return tldModKPIReviewComments::insert([
            'parent_id' => $this->itsID,
            'poster' => $user,
            'comment' => $comment,
        ]);
    }

    public function getComments($a = '')
    {
        return tldModKPIReviewComments::byParent($this->itsID, $a);
    }

    public static function byConstraints($a)
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        $query = <<<EOF
		SELECT *
		FROM mod_kpireview
		WHERE $WHERE
		ORDER BY xval
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public function byNameID($name_id, $a = '')
    {
        if (empty($name_id)) {
            return [];
        }
        $WHERE = "name_id LIKE '$name_id'";
        if (!empty($a)) {
            if (is_array($a)) {
                $WHERE .= ' AND ' . tldUtils::constructWhere($a);
            } else {
                $WHERE .= " AND $a";
            }
        }
        return self::byConstraints($WHERE);
    }
}

class tldModKPIReviewComments
{
    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    public function getHeader()
    {
        $query = <<<EOF
		SELECT c.*,
		CONCAT(UPPER(p.lastname),', ',p.firstname) AS fullname
		FROM mod_kpireview_comments c
		LEFT JOIN people p ON p.id=c.poster
		WHERE c.id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public function getID()
    {
        return $this->itsID;
    }

    public static function insert($a)
    {
        $fields = ['parent_id', 'poster', 'comment'];
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO mod_kpireview_comments SET $SET, dt=NOW()";
        return tldUtils::sqlInsert($query);
    }

    public function update($a)
    {
        if (empty($this->itsID) || empty($a)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a);
        $query = "UPDATE mod_kpireview_comments SET $SET, updated=NOW() WHERE id=$this->itsID";
        return tldUtils::sqlQuery($query);
    }

    public function delete($id = '')
    {
        if (empty($id) && empty($this->itsID)) {
            return;
        }
        if (empty($id)) {
            $id = $this->itsID;
        }
        $query = "DELETE FROM mod_kpireview_comments WHERE id=$id LIMIT 1";
        return tldUtils::sqlQuery($query);
    }

    public static function byConstraints($a)
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        $query = <<<EOF
		SELECT c.*,
		CONCAT(UPPER(p.lastname),', ',p.firstname) AS fullname
		FROM mod_kpireview_comments c
		LEFT JOIN people p ON p.id=c.poster
		WHERE $WHERE
		ORDER BY dt
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byParent($id, $a = '')
    {
        if ($id <= 0) {
            return [];
        }
        $WHERE = "c.parent_id=$id";
        if (!empty($a)) {
            if (is_array($a)) {
                $WHERE .= ' AND ' . tldUtils::constructWhere($a);
            } else {
                $WHERE .= " AND $a";
            }
        }
        return self::byConstraints($WHERE);
    }
}

/**
 * TLD Evenlog interface class
 *
 * @package Common
 */
class tldEventLog
{
    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    public function getHeader()
    {
        $query = <<<EOF
		select *
		from logs
		WHERE id=$this->itsID
EOF;
    }

    public static function getLast($constraints = '')
    {
        $where = tldUtils::constructWhere($constraints);
        $WHERE = $where ? " WHERE $where" : '';

        $query = <<<EOF
		select *
		from logs
		$WHERE
		order by id desc
		limit 1
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    public static function byConstraints($constraints, $orderBy = 'T1.id', $options = '')
    {
        $where = tldUtils::constructWhere($constraints);
        $orderBy = TldDatabase::escape($orderBy);
        if (!$options['showAll']) {
            if ($where) {
                $where .= ' AND ';
            }
            $where .= " status<>'CLOSED'";
        }
        $query = <<<EOF
		SELECT T1.*, T2.location
		FROM sor AS T1 LEFT JOIN locations AS T2 ON T1.bu=T2.id
EOF;
        if ($where) {
            $query .= " WHERE $where";
        }
        $query .= <<<EOF
		ORDER BY $orderBy
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }
}


/**
 *
 * Class for port data
 * @package Common
 */
class tldPort
{

    private $itsID;
    private $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    public function getHeader()
    {
        if (empty($this->itsID)) {
            return;
        }
        $query = <<<EOF
        SELECT
            ptc.*,
            CONCAT(
                port_code,', ',
                IF(countries.name IS NULL, '', CONCAT(countries.name,', ')),
                port_name
            ) as fullname,
            countries.name AS ctry_name
        FROM
            port_codes AS ptc
            LEFT JOIN countries ON countries.iso_code_2=ptc.ctry_code_2
        WHERE
            ptc.id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    public static function getListFullname()
    {
        return array_column(
            self::byConstraints(),
            'fullname',
            'fullname'
        );
    }

    public static function byConstraints($a = '', $opt = [])
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = " WHERE $WHERE ";
        }
        // Options
        if (!empty($opt['orderBy'])) {
            $ORDERBY = $opt['orderBy'];
        } else {
            $ORDERBY = 'fullname';
        }
        // Query
        $query = <<<EOF
        SELECT
            ptc.*,
            CONCAT(
                port_code,', ',
                IF(countries.name IS NULL, '', CONCAT(countries.name,', ')),
                port_name
            ) as fullname,
            countries.name AS ctry_name
        FROM
            port_codes AS ptc
            LEFT JOIN countries ON countries.iso_code_2=ptc.ctry_code_2
            $WHERE
        ORDER BY
            $ORDERBY
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }
}

/**
 * Class for airport data
 * @package Common
 */
class tldAirport
{

    private $itsID;
    private $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    public function getHeader()
    {
        if (empty($this->itsID)) {
            return;
        }
        $query = <<<EOF
		SELECT
            apc.*,
            CONCAT(
                airport_code,', ',
                IF(countries.name IS NULL, '', CONCAT(countries.name,', ')),
                city_name,', ',airport_name
            ) as fullname,
            countries.name AS ctry_name
        FROM
            airport_codes AS apc
            LEFT JOIN countries ON countries.iso_code_2=apc.ctry_code_2
        WHERE
            apc.id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }


    public static function getListFullname()
    {
        return array_column(
            self::byConstraints(),
            'fullname',
            'fullname'
        );
    }

    public static function getList()
    {
        return array_column(
            self::byConstraints(),
            'fullname',
            'airport_code'
        );
    }

    public static function search($name, $orderBy = 'airport_codes.airport_name', $orderDir = 'ASC', $maxResult = 20)
    {
        $target = TldDatabase::escape($name);

        $whereClause = <<<EOF
    airport_codes.airport_name LIKE '%$name%'
    OR airport_codes.airport_code LIKE '%$name%'
    OR countries.name LIKE '%$name%'
    AND airport_codes.type = 'Airport'
    ORDER BY $orderBy $orderDir
EOF;
        if (strlen($name) <= 3) {
            $whereClause = <<<EOF
    airport_codes.airport_code LIKE '%$name%'
    AND airport_codes.type = 'Airport'
    ORDER BY airport_codes.airport_code $orderDir
EOF;
        }

        $query = <<<EOF
            SELECT airport_codes.id, airport_codes.airport_code, airport_codes.airport_name, countries.name AS country_name
            FROM airport_codes
            LEFT JOIN countries ON airport_codes.ctry_code_2=countries.iso_code_2
            WHERE $whereClause
            LIMIT $maxResult
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * @param string|array $a
     * @param string|array $opt
     * @return array
     */
    public static function byConstraints($a = '', $opt = [])
    {
        $WHERE = is_array($a) ? tldUtils::constructWhere($a) : $a;

        if (!empty($WHERE)) {
            $WHERE = " AND $WHERE ";
        }
        // Options
        $ORDERBY = 'fullname';
        if (!empty($opt['orderBy'])) {
            $ORDERBY = $opt['orderBy'];
        }
        $query = <<<EOF
        SELECT
            apc.*,
            CONCAT(
                airport_code,', ',
                IF(countries.name IS NULL, '', CONCAT(countries.name,', ')),
                city_name,', ',airport_name
            ) as fullname,
            countries.name AS ctry_name
        FROM
            airport_codes AS apc
            LEFT JOIN countries ON countries.iso_code_2=apc.ctry_code_2
        WHERE
            apc.type LIKE 'Airport'
            $WHERE
        ORDER BY
            $ORDERBY
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }
}

/**
 * Class for Modules data
 * @package Common
 */
class tldModule
{

    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        if (!is_numeric($id)) {
            return;
        }
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Get Module ID
     */
    public function getID()
    {
        return $this->itsHeader['id'];
    }

    /**
     * Get Module owner ID
     */
    public function getMOOID()
    {
        return $this->itsHeader['oid'];
    }

    /**
     * Get Module key user ID
     */
    public function getKeyUserID()
    {
        return $this->itsHeader['key_user_id'];
    }

    /**
     * Get MIS owner ID
     */
    public function getMISID()
    {
        return $this->itsHeader['uid'];
    }

    /**
     * Get Module code
     */
    public function getModule()
    {
        return $this->itsHeader['module'];
    }

    /**
     * Get Module name
     */
    public function getFullname()
    {
        return $this->itsHeader['dsc'];
    }

    public function getUserGuideID()
    {
        return $this->itsHeader['user_guide_id'];
    }

    public function getHelpPageID()
    {
        return $this->itsHeader['help_page_id'];
    }

    /**
     * Get header data of the module
     */
    public function getHeader()
    {
        $query = <<<EOF
        SELECT com_modules.*,
        	CASE WHEN com_modules.migrated = 1 THEN 'Y' ELSE 'N' END AS migrated,
            CONCAT(owner.firstname,' ',owner.lastname) AS oid_fullname,
            CONCAT(assignee.firstname,' ',assignee.lastname) AS uid_fullname,
            CONCAT(key_user.firstname,' ',key_user.lastname) AS key_user_fullname,
            guide.title AS guide_title,
            guide.status AS guide_status,
            help.title AS help_title,
            help.status AS help_status
        FROM com_modules
            LEFT JOIN people AS owner ON owner.id=com_modules.oid
            LEFT JOIN people AS key_user ON key_user.id=com_modules.key_user_id
            LEFT JOIN people AS assignee ON assignee.id=com_modules.uid
            LEFT JOIN dms AS guide ON guide.id=com_modules.user_guide_id
            LEFT JOIN dms AS help ON help.id=com_modules.help_page_id
        WHERE
            com_modules.id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Check if module exists
     */
    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public function update($data, $fields = '')
    {
        $SET = tldUtils::getSqlSet($data, $fields);
        $query = "UPDATE com_modules SET $SET WHERE id=$this->itsID LIMIT 1";
        return tldUtils::sqlQuery($query);
    }

    /**
     * Add log entry
     * @param int $id poster
     * @param string $comment
     * @return mixed int or string on error
     */
    public function addLogEntry($id, $comment, $log_num = 0)
    {
        $a['parent_id'] = $this->itsID;
        $a['module'] = 'MOD';
        $a['poster'] = $id;
        $a['comment'] = $comment;
        $a['log_num'] = $log_num;
        return tldModLog::insert($a);
    }

    /**
     * Get log
     * @return array
     */
    public function getLog()
    {
        return tldModLog::byParent($this->itsID, 'MOD');
    }

    /**
     * Add list entry for list of roles impacted in module
     * @param string $name
     * @param string $key
     * @param mixed $value
     */
    public function addListEntry($name, $key, $value)
    {
        $a['parent_id'] = $this->itsID;
        $a['module'] = 'MOD';
        $a['list_name'] = $name;
        $a['list_key'] = $key;
        $a['value'] = $value;
        return tldModList::insert($a);
    }

    /**
     * Add impacted role to the module
     * @param string $role
     */
    public function addImpactedRole($role)
    {
        return self::addListEntry('list.mis.module.roles', null, $role);
    }

    /**
     * Get impacted role to the module
     * @return string $role
     */
    public function getImpactedRoleList()
    {
        if (empty($this->itsID)) {
            return;
        }
        $a = [
            'parent_id' => $this->itsID,
            'module' => 'MOD',
            'list_name' => 'list.mis.module.roles',
        ];
        return tldModList::byConstraints($a);
    }

    public function getImpactedRoleListOptionsByIDRole()
    {
        return array_column($this->getImpactedRoleList(), 'value', 'id');
    }

    public function getImpactedRoleListOptionsByRoleRole()
    {
        return array_column($this->getImpactedRoleList(), 'value', 'value');
    }

    /**
     * Get list of people emails attached to impacted role of the module
     * @return array
     */
    public function getImpactedRoleEmailList()
    {
        $emailList = [];
        $roleList = $this->getImpactedRoleListOptionsByIDRole();
        foreach ($roleList as $role) {
            $grp = new tldGroup($role);
            foreach ($grp->getEmailList() as $email) {
                if (in_array($email, $emailList)) {
                    continue;
                }
                if (empty($email)) {
                    continue;
                }
                $emailList[] = $email;
            }
        }
        return array_unique($emailList);
    }

    /**
     * Get list of all modules
     * @param $smartyOptions
     * @return array of rows
     */
    public static function getList($smartyOptions = '')
    {
        $query = <<<EOF
		SELECT com_modules.*,
            CASE WHEN com_modules.migrated = 1 THEN 'Y' ELSE 'N' END AS migrated,
			CONCAT(owner.firstname,' ',owner.lastname) AS oid_fullname,
			CONCAT(assignee.firstname,' ',assignee.lastname) AS uid_fullname
		FROM com_modules
			LEFT JOIN people AS owner ON owner.id=com_modules.oid
			LEFT JOIN people AS assignee ON assignee.id=com_modules.uid
		ORDER BY module
EOF;
        switch ($smartyOptions) {
            case 'smartyOptionsModuleDesc':
                return tldUtils::getSqlToAssocArray(
                    $query,
                    'smartyOptions',
                    ['module', 'dsc']
                );
            default:
                return tldUtils::getSqlToAssocArray($query);
        }
    }

    public static function getMOOList(): array
    {
        $query = <<<EOF
		SELECT oid, CONCAT(owner.lastname, ', ', owner.firstname, ' (', GROUP_CONCAT(DISTINCT module SEPARATOR ', '), ')') AS fullname
		FROM com_modules
			LEFT JOIN people AS owner ON owner.id=com_modules.oid
		GROUP BY oid
		ORDER BY fullname
EOF;
        return tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['oid', 'fullname']);
    }

    /**
     * Get modules list by constraints
     * @param $a array or string of constraints
     * @param $orderBy string
     * @return array of rows
     */
    public static function byConstraints($a, $orderBy = 'module')
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }

        if (!empty($WHERE)) {
            $WHERE = " HAVING $WHERE ";
        }
        $ORDERBY = TldDatabase::escape($orderBy);

        $query = <<<EOF
		SELECT com_modules.*,
			CASE WHEN com_modules.migrated = 1 THEN 'Y' ELSE 'N' END AS migrated,
			CONCAT(owner.firstname,' ',owner.lastname) AS oid_fullname,
			CONCAT(assignee.firstname,' ',assignee.lastname) AS uid_fullname
		FROM com_modules
			LEFT JOIN people AS owner ON owner.id=com_modules.oid
			LEFT JOIN people AS assignee ON assignee.id=com_modules.uid
		$WHERE
		ORDER BY $ORDERBY
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get MOO ID by module
     * @param $module string
     * @return int|null
     */
    public static function getMOOIDByModule($module)
    {
        $rows = self::byConstraints(['module' => $module]);
        return $rows[0]['oid'] ?? null;
    }

    /**
     * Get MIS Owner ID by module
     * @param $module string
     * @return int|null
     */
    public static function getMisOwnerIdByModule($module)
    {
        $rows = self::byConstraints(['module' => $module]);
        return $rows[0]['uid'] ?? null;
    }
    /**
     * Get Key User ID by module
     * @param $module string
     * @return int|null
     */
    public static function getKeyUserIdByModule($module)
    {
        $rows = self::byConstraints(['module' => $module]);
        return $rows[0]['key_user_id'] ?? null;
    }
}

/**
 * Class for Lists data
 * @package Common
 */
class tldList
{

    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    public function __destruct()
    { }

    /**
     * Get header record
     * @return row
     */
    public function getHeader()
    {
        $query = <<<EOF
		SELECT *
		FROM lists
		WHERE id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Insert a new entry list
     * @param $a array
     * @return int or string if error
     */
    public static function insert($a)
    {
        $fields = ['parent_id', 'list_name', 'list_key', 'list_item'];
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = <<<EOF
			INSERT INTO lists
			SET $SET
EOF;
        return tldUtils::sqlInsert($query);
    }

    /**
     * Update this particular list line
     * @param array $a associative array
     * @return mixed string or boolean
     */
    public function update($a)
    {
        if (empty($this->itsID)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a);
        $query = <<<EOF
			UPDATE lists
			SET $SET
			WHERE id=$this->itsID
			LIMIT 1
EOF;
        return tldUtils::sqlQuery($query);
    }

    /**
     * Delete this particular list line
     * @return mixed
     */
    public function delete($id)
    {
        if (empty($id)) {
            return;
        }
        $query = <<<EOF
		DELETE FROM lists
		WHERE id=$id
		LIMIT 1
EOF;
        return tldUtils::sqlQuery($query);
    }

    /**
     * Get lists by constraints
     * @param $a mixed string or array constraints
     * @param $orderBy string
     */
    public static function byConstraints($a, $orderBy = 'list_item')
    {
        if (empty($a)) {
            return;
        }
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        $orderBy = TldDatabase::escape($orderBy);

        $query = <<<EOF
		SELECT *
		FROM lists
		WHERE $WHERE
		ORDER BY $orderBy
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get lists by list name
     * @param $list_name string
     * @return array of data
     */
    public static function byListName($list_name, $option)
    {
        $list_name = TldDatabase::escape($list_name);
        $option = TldDatabase::escape($option);
        $a = ['list_name' => $list_name];
        return self::byConstraints($a, $option);
    }

    public static function getListItemByKey($list_name, $list_key)
    {
        $list_name = TldDatabase::escape($list_name);
        $list_key = TldDatabase::escape($list_key);

        $query = <<<EOF
		SELECT list_item
		FROM lists
		WHERE list_name='$list_name'
				AND list_key='$list_key'
EOF;
        $row = tldUtils::getSqlRowToAssocArray($query);
        return $row['list_item'];
    }

    /**
     * Get lists by list name with smarty option as ListItem->ListItem
     * @param $list_name string
     * @return array of data
     */
    public static function optionsByListNameAsListItemListItem($list_name, $option = 'list_item')
    {
        $data = self::byListName($list_name, $option);
        return array_column($data, 'list_item', 'list_item');
    }

    /**
     * Get lists by list name with smarty option as ListKey->ListItem
     * @param $list_name string
     * @return array of data
     */
    public static function optionsByListNameAsListKeyListItem($list_name, $option = 'list_item')
    {
        $data = self::byListName($list_name, $option);
        return array_column($data, 'list_item', 'list_key');
    }

    /**
     * Get lists by list name with smarty option as ListKey->ListItem
     * @param $list_name string
     * @return array of data
     */
    public static function optionsByListNameAsListKeyListKey($list_name, $option = 'list_item')
    {
        $data = self::byListName($list_name, $option);
        return array_column($data, 'list_key', 'list_key');
    }

    /**
     * Get available language options from baan
     * @return array of data
     */
    public static function getBaanLanguages()
    {
        $query = <<<EOF
		SELECT DISTINCT RTRIM(LTRIM(t_clan)) AS clan, RTRIM(LTRIM(t_dsca)) AS dsca
		FROM ttcmcs046300
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan', 'smartyOptions' => ['clan', 'dsca']]);
    }
}

/**
 * Class for Lists data
 * @package Common
 */
class tldCountry
{

    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        if (is_string($id)) {
            $data = self::byName($id);
            $this->itsID = $data['id'];
        } else {
            $this->itsID = $id;
        }
        $this->itsHeader = $this->getHeader();
    }

    public function __destruct()
    { }

    /**
     * Get country header
     * @return row
     */
    public function getHeader()
    {
        $query = <<<EOF
		SELECT *
		FROM countries
		WHERE id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Insert a new country
     * @param $a array
     * @return int or string if error
     */
    public static function insert($a)
    {
        $fields = ['parent_id', 'name', 'iso_code_1', 'iso_code_2'];
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = <<<EOF
			INSERT INTO countries
			SET $SET
EOF;
        return tldUtils::sqlInsert($query);
    }

    /**
     * Update this particular country
     * @param array $a associative array
     * @return mixed string or boolean
     */
    public function update($a)
    {
        if (empty($this->itsID)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a);
        $query = <<<EOF
			UPDATE countries
			SET $SET
			WHERE id=$this->itsID
			LIMIT 1
EOF;
        return tldUtils::sqlQuery($query);
    }

    /**
     * Delete this particular country
     * @return mixed
     */
    public function delete($id)
    {
        if (empty($id)) {
            return;
        }
        $query = <<<EOF
		DELETE FROM countries
		WHERE id=$id
		LIMIT 1
EOF;
        return tldUtils::sqlQuery($query);
    }

    /**
     * Get country by name
     * @param string $name
     * @return array of data
     */
    public function byName($name)
    {
        $name = TldDatabase::escape($name);
        $data = self::byConstraints(['name' => $name]);
        return $data[0];
    }

    /**
     * Get country by name with option AS name->name
     * @return array of data
     */
    public static function optionsAsNameName()
    {
        $data = self::byConstraints();
        return array_column($data, 'name', 'name');
    }

    /**
     * Get country by name with option AS id->name
     * @return array of data
     */
    public static function optionsAsIDName()
    {
        $data = self::byConstraints();
        return array_column($data, 'name', 'id');
    }


    /**
     * @param string|null $airportCode
     * @return array
     * return array of [ airport_code => country_name ], or []
     */
    public static function getCountryNameByAirportCode(?string $airportCode = null): array
    {
        if ($airportCode === ""){
            return [];
        }

        $query = "
        SELECT countries.name, apc.airport_code
        FROM countries
        INNER JOIN airport_codes apc ON apc.ctry_code_2 = countries.iso_code_2
        WHERE apc.type = 'Airport'
    ";
        if ($airportCode !== null) {
            $query .= " AND apc.airport_code = '".$airportCode."'";
        }
        $query .= " GROUP BY apc.airport_code ";

        $result = tldUtils::getSqlToAssocArray($query);

        $apcCodeCountryName = [];
        foreach ($result as $row) {
            $apcCodeCountryName[$row['airport_code']] = $row['name'];
        }

        return $apcCodeCountryName;
    }

    /**
     * Get countries by constraints
     * @param $a mixed string or array constraints
     * @param $orderBy string
     * @return array
     */
    public static function byConstraints($a = '', $orderBy = 'name')
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = " WHERE $WHERE ";
        }
        $orderBy = TldDatabase::escape($orderBy);

        $query = <<<EOF
		SELECT *
		FROM countries
		$WHERE
		ORDER BY $orderBy
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public function getRegionList()
    {
        $query = 'SELECT distinct region FROM countries';
        return tldUtils::getSqlToAssocArray($query);
    }

    public function getSubRegionList()
    {
        $query = 'SELECT distinct sub_region FROM countries';
        return tldUtils::getSqlToAssocArray($query);
    }

    public function getSubRegionListByRegion($region)
    {
        $query = "SELECT distinct sub_region FROM countries WHERE region LIKE '$region'";
        return tldUtils::getSqlToAssocArray($query);
    }

    public function getListByRegion($region)
    {
        return self::byConstraints(['region' => $region]);
    }

    public function getListBySubRegion($subRegion)
    {
        return self::byConstraints(['sub_region' => $subRegion]);
    }
}

/**
 * Class for accessing TLD Org info for modules
 * @package Utilities
 */
class tldModOrg
{

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    public function getID()
    {
        return $this->itsID;
    }

    public function getParentID()
    {
        return $this->itsHeader['parent_id'];
    }

    public function getModule()
    {
        return $this->itsHeader['module'];
    }

    public function getName()
    {
        return $this->itsHeader['name'];
    }

    public static function getSELECT(): string
    {
        return <<<EOF
SELECT
    mod_org.*,
    regions.division AS region,
    sub_divisions.name AS subdivision,
    divisions.name AS division,
    bu.location,
    departments.dpt AS department,
    fct.dsc AS function_dsc
EOF;
    }

    public static function getFROM(): string
    {
        return <<<EOF
FROM mod_org
    LEFT JOIN tld_divisions AS divisions ON divisions.id=mod_org.division_id
    LEFT JOIN tld_sub_divisions AS sub_divisions ON sub_divisions.id=mod_org.subdivision_id
    LEFT JOIN tld_regions AS regions ON regions.id=mod_org.region_id
    LEFT JOIN locations AS bu ON bu.id=mod_org.bu_id
    LEFT JOIN tld_departments AS departments ON departments.id=mod_org.dpt_id
    LEFT JOIN tld_functions AS fct ON fct.id=mod_org.fct_id
EOF;
    }

    public function getHeader()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
WHERE mod_org.id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Insert new record
     * @param array $a
     * @return int or string on error
     */
    public static function insert($a)
    {
        if (empty($a)) {
            return;
        }
        $fields = ['parent_id', 'module', 'name', 'division_id', 'subdivision_id', 'region_id', 'bu_id', 'dpt_id', 'fct_id'];
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO mod_org SET $SET";
        return tldUtils::sqlInsert($query);
    }

    /**
     * Update record
     * @param array $a
     * @param array $fields (optional)
     * @return int or string on error
     */
    public function update($a, $fields = '')
    {
        if (empty($a)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "UPDATE mod_org SET $SET WHERE id=$this->itsID";
        return tldUtils::sqlQuery($query);
    }

    /**
     * Delete record
     * @return string on error
     */
    public function delete()
    {
        $query = "DELETE FROM mod_org WHERE id=$this->itsID LIMIT 1";
        return tldUtils::sqlQuery($query);
    }

    /**
     * Get record by constraints
     * @param array $a constraints
     * @param array $opt options
     * @return array of rows
     */
    public static function byConstraints($a, $opt = [])
    {
        if (empty($a)) {
            return 'empty parameter';
        }
        // Look for constraints
        if (is_array($a)) {
            $HAVING = 'HAVING ' . tldUtils::constructWhere($a);
        } else {
            $HAVING = "HAVING $a";
        }
        // Look for options
        if (!empty($opt['orderBy'])) {
            $ORDERBY = 'ORDER BY ' . $opt['orderBy'];
        } else {
            $ORDERBY = 'ORDER BY id';
        }
        // Construct query
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
$HAVING
$ORDERBY
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get list by parent
     * @param int parentID
     * @param string module
     * @param string name
     * @return array
     */
    public static function byParentModuleName($pid, $module, $name = '')
    {
        $a = [
            'parent_id' => $pid,
            'module' => $module,
            'mod_org.name' => $name,
        ];
        return self::byConstraints($a);
    }

    /**
     * Get list of user people from ACL list constraints
     * @param int parentID
     * @param string module
     * @param string name
     * @return array
     */
    public static function getUserListByParentModuleName($pid, $module, $name = '')
    {
        $a = <<<EOF
people.hidden=0 AND people.disabled='N'
AND EXISTS(
    SELECT mod_org.id
    FROM mod_org
    WHERE
        mod_org.parent_id = $pid
        AND module LIKE '$module'
        AND name LIKE '$name'
        AND IF( mod_org.division_id=0, 1=1, mod_org.division_id=divisions.id )
        AND IF( mod_org.subdivision_id=0, 1=1, mod_org.subdivision_id=sub_divisions.id )
        AND IF( mod_org.region_id=0, 1=1, mod_org.region_id=people.div_id )
        AND IF( mod_org.bu_id=0, 1=1, mod_org.bu_id=people.bu_id )
        AND IF( mod_org.dpt_id=0, 1=1, mod_org.dpt_id=people.dpt_id )
        AND IF( mod_org.fct_id=0, 1=1, mod_org.fct_id=people.fct_id )
)
EOF;
        return tldDirectory::byConstraints($a);
    }
}

class tldMimeFile
{

    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    /**
     * @return int
     */
    public function getID()
    {
        return $this->itsID;
    }

    /**
     * Check if empty
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    /**
     * Get SELECT mysql statement
     * @return string
     */
    public static function getSELECT(): string
    {
        return <<<EOF
SELECT *
EOF;
    }

    /**
     * Get FROM mysql statement
     * @return string
     */
    public static function getFROM(): string
    {
        return <<<EOF
FROM mimetypes
EOF;
    }

    /**
     * Get header
     * @return array
     */
    public function getHeader()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
WHERE id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Insert new entry
     * @param array $a
     * @return int or string on error
     */
    public static function insert($a)
    {
        if (empty($a)) {
            return;
        }
        $fields = ['ext', 'mimetype', 'icon_path'];
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO mimetypes SET $SET";
        return tldUtils::sqlInsert($query);
    }

    /**
     * Update entry
     * @param array $a
     * @param array $fields (optional)
     * @return int or string on error
     */
    public function update($a, $fields = '')
    {
        if (empty($a)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "UPDATE mimetypes SET $SET WHERE id=$this->itsID";
        return tldUtils::sqlQuery($query);
    }

    /**
     * Delete entry
     * @return string on error
     */
    public function delete()
    {
        $query = "DELETE FROM mimetypes WHERE id=$this->itsID LIMIT 1";
        return tldUtils::sqlQuery($query);
    }

    /**
     * Get Type list by constraints
     * @param array $a constraints
     * @param array $opt options
     * @return array of rows
     */
    public static function byConstraints($a, $opt = [])
    {
        if (empty($a)) {
            return 'empty parameter';
        }
        // Look for constraints
        if (is_array($a)) {
            $HAVING = 'HAVING ' . tldUtils::constructWhere($a);
        } else {
            $HAVING = "HAVING $a";
        }
        // Look for options
        if (!empty($opt['orderBy'])) {
            $ORDERBY = 'ORDER BY ' . $opt['orderBy'];
        } else {
            $ORDERBY = 'ORDER BY ext';
        }
        // Construct query
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
$HAVING
$ORDERBY
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public function getList()
    {
        return self::byConstraints('1=1');
    }

    public static function getIconPathByExtension($ext)
    {
        $default = '/shared/icons/file_icons/256x256/Unknow.png';
        $data = self::byConstraints(['ext' => $ext]);
        return empty($data[0]['icon_path']) ? $default : $data[0]['icon_path'];
    }
}

class tldTranslate
{
    public $itsLang;
    public $itsEncoding;

    static $Languages = [
        'en' => 'English',
        'fr' => 'French',
        'de' => 'German',
        'pt' => 'Portuguese',
        'es' => 'Spanish',
        'ja' => 'Japanese',
        'zh' => 'Chinese',
        'ru' => 'Russian',
    ];

    static $Encodings = [
        'ISO-8859-1',
        'UTF-8',
        'GB2312',
    ];

    public function __construct($lang = 'en', $encoding = 'ISO-8859-1')
    {
        $this->setLang($lang);
        $this->setEncoding($encoding);
    }

    public function setLang($lang)
    {
        $lang = strtolower($lang);
        if ($lang == 'ch') {
            $lang = 'zh';
        }
        $this->itsLang = strtolower($lang);
    }

    public function setEncoding($encoding)
    {
        $this->itsEncoding = $encoding;
    }

    public function getDictionary($string)
    {
        $lookup = TldDatabase::escape($string);
        $query = <<<EOF
		SELECT
			*
		FROM
			translations
		WHERE
			en = '$lookup'
EOF;
        $res = tldUtils::getSqlRowToAssocArray($query);
        $text = array_key_exists($this->itsLang, $res) ? trim($res[$this->itsLang]) : '';
        if (0 === strlen($text)) {
            $text = trim($string);
        }
        return html_entity_decode($text, ENT_QUOTES | ENT_HTML401, $this->itsEncoding);
    }
}


class tldModCost
{

    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    public function getID()
    {
        return $this->itsID;
    }

    public static function getSELECT($dcur = 'USD')
    {
        return <<<EOF
SELECT
    mod_costs.*,
    CONCAT(poster.lastname,', ',poster.firstname) AS poster_fullname,
    CONCAT(user.lastname,', ',user.firstname) AS user_fullname,
    '$dcur' AS dcur,
    ROUND(
        price*(
            IF(tcur.rate IS NULL, 1, tcur.rate)/
            IF(fcur.rate IS NULL, 1, fcur.rate)
        ),2
    ) AS price_dcur
EOF;
    }

    public static function getFROM($dcur = 'USD')
    {
        return <<<EOF
FROM
    mod_costs
    LEFT JOIN people AS poster ON poster.id=mod_costs.poster
    LEFT JOIN people AS user ON user.id=mod_costs.uid
    LEFT JOIN erp_forex2 AS fcur ON PERIOD_DIFF(
        DATE_FORMAT(CONCAT(fcur.nam_year, '-', fcur.nam_month, '-01'), '%Y%m'),
        PERIOD_ADD(DATE_FORMAT(mod_costs.date, '%Y%m'), -1)
    ) = 0 AND fcur.nam_cur=mod_costs.cur AND fcur.typ='END'
    LEFT JOIN erp_forex2 AS tcur ON PERIOD_DIFF(
        DATE_FORMAT(CONCAT(tcur.nam_year, '-', tcur.nam_month, '-01'), '%Y%m'),
        PERIOD_ADD(DATE_FORMAT(mod_costs.date, '%Y%m'), -1)
    ) = 0 AND tcur.nam_cur='$dcur' AND tcur.typ='END'
EOF;
    }

    public function getHeader($dcur = 'USD')
    {
        $SELECT = self::getSELECT($dcur);
        $FROM = self::getFROM($dcur);
        $query = <<<EOF
$SELECT
$FROM
WHERE mod_costs.id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public function getModule()
    {
        return $this->itsHeader['module'];
    }

    public function getParentID()
    {
        return $this->itsHeader['parent_id'];
    }

    public static function byConstraints($a, $opt = [])
    {
        if (empty($a)) {
            return 'empty parameter';
        }
        // Look for constraints
        if (is_array($a)) {
            $WHERE = 'WHERE ' . tldUtils::constructWhere($a);
        } else {
            $WHERE = "WHERE $a";
        }
        // Look for options
        if (!empty($opt['orderBy'])) {
            $ORDERBY = 'ORDER BY ' . $opt['orderBy'];
        } else {
            $ORDERBY = 'ORDER BY type,id';
        }
        $DCUR = 'USD';
        if (!empty($opt['dcur'])) {
            $DCUR = $opt['dcur'];
        }
        // Construct query
        $SELECT = self::getSELECT($DCUR);
        $FROM = self::getFROM($DCUR);
        $query = <<<EOF
$SELECT
$FROM
$WHERE
$ORDERBY
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byParent($pid, $module, $dcur = 'USD')
    {
        return self::byConstraints(
            [
                'mod_costs.parent_id' => $pid,
                'module' => $module,
            ],
            [
                'dcur' => $dcur,
            ]
        );
    }

    /**
     * Return Total amount grouped by Type of cost
     * @param int $pid
     * @param string $module
     * @param string $dcur
     * @return array
     */
    public static function totalsByParent($pid, $module, $dcur = 'USD')
    {
        $FROM = self::getFROM($dcur);
        $query = <<<EOF
SELECT
    mod_costs.parent_id,
    mod_costs.module,
    mod_costs.type,
    '$dcur' AS dcur,
    SUM(
      ROUND(
        price*(
          IF(tcur.rate IS NULL, 1, tcur.rate)/
          IF(fcur.rate IS NULL, 1, fcur.rate)
        ),2
      )
    ) AS price_dcur
$FROM
WHERE
    mod_costs.module LIKE '$module'
    AND mod_costs.parent_id=$pid
GROUP BY
    mod_costs.type
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function insert($a)
    {
        if (empty($a)) {
            return;
        }
        $fields = [
            'parent_id', 'module', 'type', 'description', 'um', 'qty', 'cur', 'price', 'date', 'poster', 'uid',
        ];
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO mod_costs SET date_open=NOW(), $SET";
        return tldUtils::sqlInsert($query);
    }

    public function update($a, $fields = '')
    {
        if (empty($a)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "UPDATE mod_costs SET $SET WHERE id=$this->itsID";
        return tldUtils::sqlQuery($query);
    }

    public function delete()
    {
        $query = "DELETE FROM mod_costs WHERE id=$this->itsID LIMIT 1";
        return tldUtils::sqlExecute($query);
    }

    public static function getCostTypeList()
    {
        $query = 'SELECT * FROM mod_costs_type GROUP BY type';
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getCostTypeListAsTypeType()
    {
        return array_column(
            self::getCostTypeList(),
            'type',
            'type'
        );
    }

    public static function getCostTypeByModule($module)
    {
        $query = "SELECT * FROM mod_costs_type WHERE module LIKE '$module'";
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getCostTypeByModuleAsTypeType($module)
    {
        return array_column(
            self::getCostTypeByModule($module),
            'type',
            'type'
        );
    }

    /********************************************************
     *  MOD COST - ACL Managment functions
     ********************************************************/

    public static function getModuleACL()
    {
        return [
            // Add your module here... --->
            'ESR' => [
                'add' => ['role_SA'],
                'view' => ['acl_auth_INTRANET'],
                'edit' => ['role_SA'],
                'delete' => ['role_SA'],
            ],
            // DO NOT UPDATE BELOW !!!
            /*"MODULE"=>array(
                "add"=>array("acl_auth_INTRANET"),
                "view"=>array("acl_auth_INTRANET"),
                "edit"=>array("acl_auth_INTRANET"),
                "delete"=>array("acl_auth_INTRANET"),
            ),*/
            'default' => [
                'add' => ['acl_auth_INTRANET'],      // Open to all
                'view' => ['acl_auth_INTRANET'],     // Open to all
                'edit' => ['acl_auth_INTRANET'],     // Open to all
                'delete' => ['acl_auth_INTRANET'],   // Open to all
            ],
        ];
    }

    public static function isModuleValid($module)
    {
        // Check module
        if (!in_array($module, array_keys(tldUtils::getModLinks()))) {
            return false;
        }
        // Check cost types
        $costTypeList = self::getCostTypeByModuleAsTypeType($module);
        if (empty($costTypeList)) {
            return false;
        }
        return true;
    }

    public static function isAllowed(tldUser $user, $module, $action)
    {
        $ACL = self::getModuleACL();
        $acl_action = $ACL['default'];
        if (array_key_exists($module, $ACL)) {
            $acl_action = $ACL[$module];
        }
        return $user->isInGroup($acl_action[$action]);
    }
}


class tldModFAQ
{

    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    public function getID()
    {
        return $this->itsID;
    }

    public static function getSELECT(): string
    {
        return <<<EOF
SELECT
    *
EOF;
    }

    public static function getFROM(): string
    {
        return <<<EOF
FROM
    mod_faq
EOF;
    }

    public function getHeader()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
WHERE id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public function getModule()
    {
        return $this->itsHeader['module'];
    }

    public function getParentID()
    {
        return $this->itsHeader['parent_id'];
    }

    public static function byConstraints($a, $opt = [])
    {
        if (empty($a)) {
            return 'empty parameter';
        }
        // Look for constraints
        if (is_array($a)) {
            $HAVING = 'HAVING ' . tldUtils::constructWhere($a);
        } else {
            $HAVING = "HAVING $a";
        }
        // Look for options
        if (!empty($opt['orderBy'])) {
            $ORDERBY = 'ORDER BY ' . $opt['orderBy'];
        } else {
            $ORDERBY = 'ORDER BY id';
        }
        // Construct query
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
$HAVING
$ORDERBY
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byParent($pid, $module)
    {
        return self::byConstraints(
            [
                'parent_id' => $pid,
                'module' => $module,
            ]
        );
    }

    public static function insert($a)
    {
        if (empty($a)) {
            return;
        }
        $fields = [
            'parent_id', 'module', 'symptom', 'problem', 'solution',
        ];
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO mod_faq SET $SET";
        return tldUtils::sqlInsert($query);
    }

    public function update($a, $fields = '')
    {
        if (empty($a)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "UPDATE mod_faq SET $SET WHERE id=$this->itsID";
        return tldUtils::sqlQuery($query);
    }

    public function delete()
    {
        $query = "DELETE FROM mod_faq WHERE id=$this->itsID LIMIT 1";
        return tldUtils::sqlExecute($query);
    }
}


class tldModParts
{

    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    public function getID()
    {
        return $this->itsID;
    }

    public static function getSELECT(): string
    {
        return <<<EOF
SELECT
    *
EOF;
    }

    public static function getFROM(): string
    {
        return <<<EOF
FROM
    mod_parts
EOF;
    }

    public function getHeader()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
WHERE id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public function getModule()
    {
        return $this->itsHeader['module'];
    }

    public function getParentID()
    {
        return $this->itsHeader['parent_id'];
    }

    public static function byConstraints($a, $opt = '')
    {
        if (empty($a)) {
            return 'empty parameter';
        }
        // Look for constraints
        if (is_array($a)) {
            $HAVING = 'HAVING ' . tldUtils::constructWhere($a);
        } else {
            $HAVING = "HAVING $a";
        }
        // Look for options
        if (!empty($opt['orderBy'])) {
            $ORDERBY = 'ORDER BY ' . $opt['orderBy'];
        } else {
            $ORDERBY = 'ORDER BY id';
        }
        // Construct query
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
$HAVING
$ORDERBY
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byConstraintsWithModuleData($constraints, string $module, array $moduleFields = ['id'], array $opt = []): array
    {
        if (empty($constraints)) {
            return [];
        }

        $module = strtoupper($module);
        $moduleKey = strtolower($module);
        $moduleTable = $module === 'PDC' ? 'demerit' : $moduleKey;
        $having = is_array($constraints) ? 'HAVING ' . tldUtils::constructWhere($constraints) : "HAVING $constraints";
        $orderBy = 'ORDER BY ' . ($opt['orderBy'] ?? 'mod_parts.id');
        $moduleFieldsSql = implode(",\n    ", array_map(
            static fn(string $field): string => "module_data.$field AS {$moduleKey}_$field",
            $moduleFields
        ));

        $query = <<<EOF
SELECT
    mod_parts.*,
    $moduleFieldsSql
FROM
    mod_parts
LEFT JOIN $moduleTable AS module_data ON module_data.id = mod_parts.parent_id
$having
AND mod_parts.module like '$module'
$orderBy
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byParent($pid, $module)
    {
        return self::byConstraints(
            [
                'parent_id' => $pid,
                'module' => $module,
            ]
        );
    }

    public static function insert($a)
    {
        if (empty($a)) {
            return;
        }
        $fields = [
            'parent_id', 'module', 'pn', 'dsc', 'qty',
        ];
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO mod_parts SET $SET";
        return tldUtils::sqlInsert($query);
    }

    public function update($a, $fields = '')
    {
        if (empty($a)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "UPDATE mod_parts SET $SET WHERE id=$this->itsID";
        return tldUtils::sqlQuery($query);
    }

    public function delete()
    {
        $query = "DELETE FROM mod_parts WHERE id=$this->itsID LIMIT 1";
        return tldUtils::sqlExecute($query);
    }
}


class tldModNOT
{

    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    public function getID()
    {
        return $this->itsID;
    }

    public function getModule()
    {
        return $this->itsHeader['module'];
    }

    public function getParentID()
    {
        return $this->itsHeader['parent_id'];
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public static function getSELECT(): string
    {
        return <<<EOF
SELECT
    mod_not.*,
    CONCAT(people.firstname,', ',people.lastname) AS poster_fullname
EOF;
    }

    public static function getFROM(): string
    {
        return <<<EOF
FROM
    mod_not
    LEFT JOIN people ON people.id=mod_not.uid
EOF;
    }

    public function getHeader()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
WHERE mod_not.id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    public static function byConstraints($a, $opt = [])
    {
        if (empty($a)) {
            return 'empty parameter';
        }
        // Look for constraints
        if (is_array($a)) {
            $HAVING = 'HAVING ' . tldUtils::constructWhere($a);
        } else {
            $HAVING = "HAVING $a";
        }
        // Look for options
        if (!empty($opt['orderBy'])) {
            $ORDERBY = 'ORDER BY ' . $opt['orderBy'];
        } else {
            $ORDERBY = 'ORDER BY id';
        }
        // Construct query
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
$HAVING
$ORDERBY
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function insert($a)
    {
        if (empty($a)) {
            return;
        }
        $fields = ['parent_id', 'module', 'uid', 'email_from', 'email_to', 'email_cc', 'email_bcc', 'email_subject', 'email_body'];
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO mod_not SET dt=NOW(), $SET";
        return tldUtils::sqlInsert($query);
    }

    public function update($a, $fields = '')
    {
        if (empty($a)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "UPDATE mod_not SET $SET WHERE id=$this->itsID";
        return tldUtils::sqlQuery($query);
    }

    public function delete()
    {
        $query = "DELETE FROM mod_not WHERE id=$this->itsID LIMIT 1";
        return tldUtils::sqlExecute($query);
    }

    public static function byParent($pid, $module)
    {
        return self::byConstraints(['parent_id' => $pid, 'module' => $module]);
    }
}


class tldModLabor
{

    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    public function getID()
    {
        return $this->itsID;
    }

    public function getModule()
    {
        return $this->itsHeader['module'];
    }

    public function getParentID()
    {
        return $this->itsHeader['parent_id'];
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public static function getSELECT(): string
    {
        return <<<EOF
SELECT
    mod_labors.*,
    CONCAT(poster.lastname,', ',poster.firstname) AS poster_fullname,
    CONCAT(user.lastname,', ',user.firstname) AS user_fullname
EOF;
    }

    public static function getFROM(): string
    {
        return <<<EOF
FROM
    mod_labors
    LEFT JOIN people AS poster ON poster.id=mod_labors.poster_id
    LEFT JOIN people AS user ON user.id=mod_labors.user_id
EOF;
    }

    public function getHeader()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
WHERE mod_labors.id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }


    public static function byConstraints($a, $opt = '')
    {
        if (empty($a)) {
            return 'empty parameter';
        }
        // Look for constraints
        if (is_array($a)) {
            $HAVING = 'HAVING ' . tldUtils::constructWhere($a);
        } else {
            $HAVING = "HAVING $a";
        }
        // Look for options
        if (!empty($opt['orderBy'])) {
            $ORDERBY = 'ORDER BY ' . $opt['orderBy'];
        } else {
            $ORDERBY = 'ORDER BY dt_work';
        }
        // Construct query
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
$HAVING
$ORDERBY
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byParent($pid, $module)
    {
        return self::byConstraints([
            'parent_id' => $pid,
            'module' => $module,
        ]);
    }

    public static function insert($a)
    {
        if (empty($a)) {
            return;
        }
        $fields = [
            'parent_id', 'module', 'poster_id', 'user_id', 'dt_work', 'description', 'nb_hours',
        ];
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO mod_labors SET dt_open=NOW(), $SET";
        return tldUtils::sqlInsert($query);
    }

    public function update($a, $fields = '')
    {
        if (empty($a)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "UPDATE mod_labors SET $SET WHERE id=$this->itsID";
        return tldUtils::sqlQuery($query);
    }

    public function delete()
    {
        $query = "DELETE FROM mod_labors WHERE id=$this->itsID LIMIT 1";
        return tldUtils::sqlExecute($query);
    }

    /********************************************************
     *  ACL Managment functions
     ********************************************************/

    public static function getModuleACL()
    {
        return [
            // Add your module here... --->

            // DO NOT UPDATE BELOW !!! --->
            /*"MODULE_EXAMPLE"=>array(
                "add"=>array("acl_auth_INTRANET"),
                "view"=>array("acl_auth_INTRANET"),
                "edit"=>array("acl_auth_INTRANET"),
                "delete"=>array("acl_auth_INTRANET"),
            ),*/
            'default' => [
                'add' => ['acl_auth_INTRANET'],      // Open to all
                'view' => ['acl_auth_INTRANET'],     // Open to all
                'edit' => ['acl_auth_INTRANET'],     // Open to all
                'delete' => ['acl_auth_INTRANET'],   // Open to all
            ],
        ];
    }

    public function isModuleValid($module)
    {
        if (!in_array($module, array_keys(tldUtils::getModLinks()))) {
            return false;
        }
    }

    public static function isAllowed(tldUser $user, $module, $action)
    {
        $ACL = self::getModuleACL();
        $acl_action = $ACL['default'];
        if (array_key_exists($module, $ACL)) {
            $acl_action = $ACL[$module];
        }
        return $user->isInGroup($acl_action[$action]);
    }

    /********************************************************
     *  Report view
     ********************************************************/

    public static function getListingDefaultColumns()
    {
        return [
            'dt_open' => 'Date posted',
            'poster_fullname' => 'Poster',
            'dt_work' => 'Date work',
            'user_fullname' => 'User concerned',
            'description' => 'Description',
            'nb_hours' => 'Nb Hours',
        ];
    }

    public static function getListing($rows, $title, $opts = '')
    {
        $report = new tldReportColumnar(
            $rows,
            [
                'xItems' => self::getListingDefaultColumns(),
                'title' => $title,
                'functions' => [
                    'Edit' => [
                        'url' => '/en/private/common/index.php?m[0]=labors&m[1]=view&m[2]=edit',
                        'param' => ['id' => 'id'],
                        'img' => '/shared/icons/miscellaneous/edit.png',
                    ],
                    'Delete' => [
                        'url' => '/en/private/common/index.php?m[0]=labors&m[1]=view&m[2]=delete',
                        'param' => ['id' => 'id'],
                        'confirmPopup' => 'Are you sure to delete?',
                        'img' => '/shared/icons/miscellaneous/delete.png',
                    ],
                ],
                'showItemNumbers' => true,
            ]
        );
        return $report->fetch();
    }
}

class tldGroupRole
{

    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        if (!is_numeric($id)) {
            return;
        }
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Get header data of the module
     */
    public function getHeader()
    {
        $query = <<<EOF
		SELECT *
		FROM people_groups_select

		WHERE id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Check if module exists
     */
    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    /**
     * Get Module ID
     */
    public function getID()
    {
        return $this->itsHeader['id'];
    }

    /**
     * Get Full record
     * @return array of rows
     */
    public static function getList()
    {
        $query = <<<EOF
            SELECT *
            FROM people_groups_select
            GROUP BY id
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }
}

class tldMysqlIterator implements Iterator
{

    private $res;
    private $nbRowsFromRes;
    private $position = 1;

    public function __construct($res)
    {
        $this->res = $res;
        $this->nbRowsFromRes = TldDatabase::numRows($this->res);
    }

    #[\ReturnTypeWillChange]
    public function rewind()
    {
        return false;
    }

    public function current(): mixed
    {
        return $this->res->fetch_assoc();
    }

    public function key(): mixed
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function valid(): bool
    {
        return $this->position <= $this->nbRowsFromRes;
    }
}

/**
 * Token Access System
 *
 * System to associate a token + email to a ressource
 *
 * @package Utilities
 */
class tldTAS
{

    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    public function getID()
    {
        return $this->itsID;
    }

    public function getParentID()
    {
        return $this->itsHeader['parent_id'];
    }

    public function getToken()
    {
        return $this->itsHeader['token'];
    }

    public function getRessource()
    {
        return $this->itsHeader['ressource'];
    }

    public function getRessourceRef()
    {
        return $this->itsHeader['ressource_ref'];
    }

    public function getEmail()
    {
        return $this->itsHeader['email'];
    }

    public function getName()
    {
        return $this->itsHeader['name'];
    }

    public function getStatus()
    {
        return $this->itsHeader['status'];
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public static function getSELECT(): string
    {
        return <<<EOF
SELECT
    *
EOF;
    }

    public static function getFROM(): string
    {
        return <<<EOF
FROM
    tas
EOF;
    }

    public function getHeader()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
WHERE tas.id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    public static function byConstraints($a, $opt = [])
    {
        if (empty($a)) {
            return 'empty parameter';
        }
        // Look for constraints
        if (is_array($a)) {
            $HAVING = 'HAVING ' . tldUtils::constructWhere($a);
        } else {
            $HAVING = "HAVING $a";
        }
        // Look for options
        if (!empty($opt['orderBy'])) {
            $ORDERBY = 'ORDER BY ' . $opt['orderBy'];
        } else {
            $ORDERBY = 'ORDER BY id';
        }
        // Construct query
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
$HAVING
$ORDERBY
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function insert($a)
    {
        if (empty($a)) {
            return;
        }
        $fields = ['parent_id', 'email', 'token', 'ressource', 'ressource_ref', 'name', 'status'];
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO tas SET dt=NOW(),$SET";
        return tldUtils::sqlInsert($query);
    }

    public function update($a, $fields = '')
    {
        if (empty($a)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "UPDATE tas SET $SET WHERE id=$this->itsID";
        return tldUtils::sqlQuery($query);
    }

    public function delete()
    {
        $query = "DELETE FROM tas WHERE id=$this->itsID LIMIT 1";
        return tldUtils::sqlExecute($query);
    }

    /**********************************************
     *              ACTION METHOD
     *********************************************/

    public static function getInstanceByToken($token)
    {
        $rows = self::byConstraints(['token' => $token]);
        return new tldTAS((int) $rows[0]['id']);
    }

    public static function generateToken()
    {
        return md5((string) mt_rand() . (string) microtime());
    }

    public static function create($a)
    {
        $a['token'] = self::generateToken();
        $a['status'] = 'ACTIVE';
        return self::insert($a);
    }

    public static function getStatusList()
    {
        return [
            'ACTIVE' => 'ACTIVE',
            'CLOSED' => 'CLOSED',
        ];
    }

    public function setStatus($status)
    {
        if (!in_array($status, self::getStatusList())) {
            return 'invalid status';
        }
        return $this->update(['status' => $status]);
    }

    public function isActive()
    {
        return $this->getStatus() === 'ACTIVE';
    }

    public function isValid()
    {
        if ($this->isEmpty()) {
            return false;
        }
        if (!$this->isActive()) {
            return false;
        }
        return true;
    }

    public function isValidByToken($token)
    {
        $tas = self::getInstanceByToken($token);
        return $tas->isValid();
    }

    public function isValidByTokenEmail($token, $email)
    {
        $rows = self::byConstraints(['token' => $token, 'email' => $email]);
        if (!count($rows)) {
            return false;
        }
        $tas = new tldTAS((int) $rows[0]['id']);
        return $tas->isValid();
    }

    public static function doCloseByRessourceRef($ressource, $ref)
    {
        $query = "UPDATE tas SET status='CLOSED' WHERE ressource LIKE '$ressource' AND ressource_ref LIKE '$ref'";
        return tldUtils::sqlQuery($query);
    }

    public function addLogAction($action)
    {
        global $_SERVER;
        $a = tldUtils::cleanupFormInput([
            'parent_id' => $this->getID(),
            'ip' => tldUtils::getClientIp(),
            'action' => $action,
            'server_env' => json_encode($_SERVER),
        ]);
        $SET = tldUtils::getSqlSet($a);
        $query = "INSERT INTO tas_log SET dt=NOW(),$SET";
        return tldUtils::sqlInsert($query);
    }
}


class TldDatabase
{
    private static $connections = [];
    private static $configs = [];
    public static $defaultConnection = 'tld';
    private static $isShutdownFunctionRegistered = false;
    private static $logger;
    private static $baanLogger;
    /** @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface */
    private static $eventDispatcher;
    private static $baanEvent = null;

    public static function setLogger($logger)
    {
        self::$logger = $logger;
    }

    public static function setBaanLogger($baanLogger)
    {
        self::$baanLogger = $baanLogger;
    }

    public static function setEventDispatcher($eventDispatcher)
    {
        self::$eventDispatcher = $eventDispatcher;
    }

    public static function setBaanEvent($event)
    {
        self::$baanEvent = $event;
    }

    public static function log($level, $message, array $context = [])
    {
        if ('baan' === ($context['connection'] ?? null) && self::$baanLogger) {
            self::$baanLogger->log($level, $message, $context);
            if (null !== self::$eventDispatcher) {
                self::$eventDispatcher->dispatch(self::$baanEvent ?? new \AppBundle\Event\BaanDatabaseQueryEvent());
            }
        } elseif (self::$logger) {
            self::$logger->log($level, $message, $context);
        }
    }

    public static function configure($connectionName, array $config)
    {
        self::$configs[$connectionName] = $config;

        if (!self::$isShutdownFunctionRegistered) {
            register_shutdown_function(['TldDatabase', 'disconnect']);
        }
    }

    public static function getConnection($name = null)
    {
        if (null === $name || '' === $name) {
            $name = self::$defaultConnection;
        }

        if (!isset(self::$connections[$name])) {
            if (!isset(self::$configs[$name])) {
                throw new \Exception(sprintf('Error: No configuration for connection "%s"', $name));
            }

            $config = self::$configs[$name];

            self::$connections[$name] = new mysqli(
                $config['host'],
                $config['user'],
                $config['pwd'],
                $config['db'],
                $config['port']
            );
        }

        return self::$connections[$name];
    }

    public static function escape($str)
    {
        $connection = self::getConnection();

        return $connection->real_escape_string($str ?? '');
    }

    public static function query($query, $connectionName = null)
    {
        $connection = self::getConnection($connectionName);

        if (null !== $stopwatch = tldUtils::getStopWatch()) {
            $stopwatch->start('database_query');
        }
        $result = $connection->query($query);
        $event = null !== $stopwatch ? $stopwatch->stop('database_query') : null;
        $duration  = $event ? $event->getDuration() : 0;
        if ($result) {
            self::log('info', 'Running SQL query ('.$duration.' .ms) "{query}" on connection "{connection}"', [
                'query' => str_replace(["\n", "\t"], ' ', $query),
                'connection' => $connectionName ?: self::$defaultConnection,
                'time' => $duration,
            ]);
        } else {
            self::log('error', 'Error while running SQL query "{query}" on connection "{connection}": {error}', [
                'query' => str_replace(["\n", "\t"], ' ', $query),
                'connection' => $connectionName ?: self::$defaultConnection,
                'error' => $connection->error,
                'time' => $duration,
            ]);
        }

        return $result;
    }

    public static function fetchArray(mysqli_result $result, $type = MYSQLI_BOTH)
    {
        return $result->fetch_array($type);
    }

    public static function numRows(mysqli_result $result): int
    {
        return $result->num_rows;
    }

    public static function fetchField(mysqli_result $result)
    {
        return $result->fetch_field();
    }

    public static function numFields(mysqli_result $result)
    {
        return $result->field_count;
    }

    public static function affectedRows($connectionName = null)
    {
        $connection = self::getConnection($connectionName);

        return $connection->affected_rows;
    }

    public static function free(mysqli_result $result)
    {
        return $result->free();
    }

    public static function error($connectionName = null)
    {
        $connection = self::getConnection($connectionName);

        return $connection->error;
    }

    public static function lastInsertId($connectionName = null)
    {
        $connection = self::getConnection($connectionName);

        return $connection->insert_id;
    }

    public static function disconnect()
    {
        foreach (array_keys(self::$connections) as $name) {
            self::$connections[$name]->close();

            unset(self::$connections[$name]);
        }
    }
}

