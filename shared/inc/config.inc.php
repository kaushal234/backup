<?php
/**
 * Configuration file for global settings
 *
 * @package Configuration
 * @desc All global settings are in this file
 * @access public
 * @copyright TLD
 */

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
$SALES_APP_PATH = $UPLOADS_PATH."/salesAppFolder";
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
