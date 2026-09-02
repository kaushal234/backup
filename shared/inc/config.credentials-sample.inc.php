<?php
/**
 * Configuration file for global settings
 *
 * @package Configuration
 * @desc All global settings are in this file
 * @access public
 * @copyright TLD
 */

// SMTP servers

$cfg['smtp']['auth'] = false;
$cfg['smtp']['host'] = '';
$cfg['smtp']['port'] = '';
$cfg['smtp']['username'] = '';
$cfg['smtp']['password'] = '';

// Database on web-db-01 server

$cfg['db']["www"]["host"] = "";
$cfg['db']["www"]["db"]   = "";
$cfg['db']["www"]["user"] = "";
$cfg['db']["www"]["pwd"]  = "";
$cfg['db']["www"]["port"] = "";

// EQUOTES Database

$cfg['db']["equotes"]["host"] = "";
$cfg['db']["equotes"]["db"]   = "";
$cfg['db']["equotes"]["user"] = "";
$cfg['db']["equotes"]["pwd"]  = "";
$cfg['db']["equotes"]["port"] = "";

// External website DB

$cfg['db']["wordpress"]["host"] = "";
$cfg['db']["wordpress"]["db"]   = "";
$cfg['db']["wordpress"]["user"] = "";
$cfg['db']["wordpress"]["pwd"]  = "";
$cfg['db']["wordpress"]["port"] = "";

// BAAN Database

$cfg['db']["baan"]["host"] = "";
$cfg['db']["baan"]["port"] = "";
$cfg['db']["baan"]["dsn"]  = "";
$cfg['db']["baan"]["user"] = "";
$cfg['db']["baan"]["pwd"]  = "";

$cfg['db']["baan_tld"]["host"] = "";
$cfg['db']["baan_tld"]["port"] = "";
$cfg['db']["baan_tld"]["dsn"]  = "";
$cfg['db']["baan_tld"]["user"] = "";
$cfg['db']["baan_tld"]["pwd"]  = "";

$cfg['db']["baantest"]["host"] = "";
$cfg['db']["baantest"]["port"] = "";
$cfg['db']["baantest"]["dsn"]  = "";
$cfg['db']["baantest"]["user"] = "";
$cfg['db']["baantest"]["pwd"]  = "";

// Cloud server - OwnCloud DB ---->

$cfg['db']["owncloud"]["host"] = "";
$cfg['db']["owncloud"]["db"]   = "";
$cfg['db']["owncloud"]["user"] = "";
$cfg['db']["owncloud"]["pwd"]  = "";
$cfg['db']["owncloud"]["port"] = "";

// SOAP server configurations

// --> baan soap server
$cfg['soap']['location'] = "";
$cfg['soap']['uri']      = "";

// --> intranet soap server
$cfg['soap']['server']['intranet'] = array(
    'location' => "",
    'uri'      => "",
    'trace'    => 1,
    'login'    => '',
    'password' => '',
);
