<?php
/**
 * Configuration file for global settings
 *
 * @package Configuration
 * @desc All global settings are in this file
 * @access public
 * @copyright TLD
 */

trigger_error('This configuration file should not be used. Please update your script to use config.inc.php.', E_USER_NOTICE);

// FOLDER PATH

$HOME_DIR = "/var/www";
$WEB_ROOT = "/var/www/www.tld-gse.com/current";
$INTRA_PATH = "/var/www/alvest-web-portals/current/intranet/legacy";
$ACTIPARTS_PATH = "$HOME_DIR/www.actiparts.com/current";
$SHARED_PATH = "/var/www/alvest-web-portals/current/shared/resources";
$SHARED_PHP_PATH = "/var/www/alvest-web-portals/current/shared/inc";
$SHARED_LIB_PATH = "/var/www/alvest-web-portals/current/shared/lib";
$UPLOADS_PATH = "$INTRA_PATH/uploads";
$EVENDORS_PATH = "/var/www/alvest-web-portals/current/evendors";
$CRON_CACHE_DIR = "/var/www/cache";
$LOCAL_INTRANET_PATH = "/var/www/alvest-web-portals/current/shopfloor";
$TLD_GROUP_PATH = "$HOME_DIR/www.tld-group.com/current";
$EXTRANET_PATH = "/var/www/alvest-web-portals/current/extranet";
$ONLINE_APPROVAL_PATH = "/mnt/grpfps10.online_approvals";
$SALES_APP_PATH = $UPLOADS_PATH."/salesAppFolder/";
$OWNCLOUD_DEV_PATH = "/var/www/html/owncloud/data/salesapp_dev/files";
$OWNCLOUD_PROD_PATH = "/var/www/html/owncloud/data/salesapp/files";
$DMS_PATH = "/var/www/alvest-web-portals/current/dms/web";

// PORTAL DIR

$PORTAL_DIR = array(
    "INTRANET"=>$INTRA_PATH,
    "EVENDORS"=>$EVENDORS_PATH,
    "EXTRANET"=>$EXTRANET_PATH,
    "ADMIN"=>"/var/www/alvest-web-portals/current/admin",
    "EXTERNAL"=>$WEB_ROOT,
    "SHOPFLOOR"=>"$LOCAL_INTRANET_PATH/shop",
    "DMS"=>$DMS_PATH,
);

// SMTP servers

$cfg['smtp']['host'] = '192.111.1.190';

/**
 * Database on web-db-01 server
 */
$cfg['db']["www"]["host"] = "web-db-01.tld-america.com";
$cfg['db']["www"]["db"]   = "tld";
$cfg['db']["www"]["user"] = "wwwlegacy";
$cfg['db']["www"]["pwd"]  = "xBb]mr8sZ0A+";
$cfg['db']["www"]["port"] = "3306";

// EQUOTES Database

$cfg['db']["equotes"]["host"] = "192.111.1.201";
$cfg['db']["equotes"]["db"]   = "finance";
$cfg['db']["equotes"]["user"] = "tldgseaccount";
$cfg['db']["equotes"]["pwd"]  = "equotes";
$cfg['db']["equotes"]["port"] = "3306";

// External website DB

$cfg['db']["wordpress"]["host"] = "webdbprod1.tld-america.com";
$cfg['db']["wordpress"]["db"]   = "wordpress";
$cfg['db']["wordpress"]["user"] = "wordpress";
$cfg['db']["wordpress"]["pwd"]  = "m8:VWPfUM33JJpeF";
$cfg['db']["wordpress"]["port"] = "3306";

// BAAN Database - 192.111.1.191

$cfg['db']["baan"]["host"] = "192.111.1.192";
$cfg['db']["baan"]["port"] = "56988";
$cfg['db']["baan"]["dsn"]  = "baan";
$cfg['db']["baan"]["user"] = "read";
$cfg['db']["baan"]["pwd"]  = "tired";

$cfg['db']["baan_tld"]["host"] = "192.111.1.192";
$cfg['db']["baan_tld"]["port"] = "1433";
$cfg['db']["baan_tld"]["dsn"]  = "baan_tld";
$cfg['db']["baan_tld"]["user"] = "read";
$cfg['db']["baan_tld"]["pwd"]  = "tired";

$cfg['db']["baantest"]["host"] = "192.111.1.192";
$cfg['db']["baantest"]["port"] = "58700";
$cfg['db']["baantest"]["dsn"]  = "baantest";
$cfg['db']["baantest"]["user"] = "read";
$cfg['db']["baantest"]["pwd"]  = "tired";

// Cloud server - OwnCloud DB ---->

$cfg['db']["owncloud"]["host"] = "172.16.10.121";
$cfg['db']["owncloud"]["db"]   = "owncloud";
$cfg['db']["owncloud"]["user"] = "owncloud";
$cfg['db']["owncloud"]["pwd"]  = "d2:PfUs2Tp$4Tt";
$cfg['db']["owncloud"]["port"] = "3306";

// OLD BAAN connector - WISE configuration - OBSOLETE

$cfg['erp']['baan']['accounts'] = array(
	200 => array("user"=>"wise200", 	"pw"=>"tldwise200"),
	300 => array("user"=>"wise300", 	"pw"=>"tldwise300"),
	303 => array("user"=>"wise303", 	"pw"=>"tldwise303"),
	400 => array("user"=>"wise400",		"pw"=>"tldwise400"),
	403 => array("user"=>"wise403",		"pw"=>"tldwise403"),
	420 => array("user"=>"wise420", 	"pw"=>"tldwise420"),
	500 => array("user"=>"wise500", 	"pw"=>"tldwise500"),
	520 => array("user"=>"wise520", 	"pw"=>"tldwise520"),
	540 => array("user"=>"wise540", 	"pw"=>"tldwise540"),
	600 => array("user"=>"wise600", 	"pw"=>"tldwise600"),
	640 => array("user"=>"wise640", 	"pw"=>"tldwise640"),
	660 => array("user"=>"wise660", 	"pw"=>"tldwise660"),
	700 => array("user"=>"wise700", 	"pw"=>"tldwise700")
);

// SOAP server configurations

// --> baan soap server
$cfg['soap']['location'] = "http://192.111.1.192/soap/server.php";
$cfg['soap']['uri']      = "urn://www.tld-gse.com/soap";

// --> intranet soap server
$cfg['soap']['server']['intranet'] = array(
    'location' => "http://www.tld-gse.com/en/private/soap/server.php",
    'uri'      => "urn://www.tld-gse.com/en/private/soap",
    'trace'    => 1,
    'login'    => 'temp',                 // temp user login
    'password' => 'SAolXFp9t6LQXMEWmhRB', // temp user password
);
