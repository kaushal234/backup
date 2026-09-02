<?php

declare(strict_types=1);

namespace App\Config;

class Globals
{
    public function __construct()
    {
        global $sess;
        global $user;
        global $php_self;
        global $_ERROR;
        global $_WARNING;
        global $_NOTE;
        global $_CONF;
        global $_TITLE;
        global $_PATH;
        global $_MENU;
        global $_BODY;
        global $JS_INCLUDE;
        global $_LANG;
        global $_CHARSET;

        $php_self = $_SERVER['PHP_SELF'];
        $user = null;

        $_ERROR = [];
        $_WARNING = [];
        $_NOTE = [];
        $_CONF = [];
        $_TITLE = 'DMS';
        $_PATH = $GLOBALS['DMS_PATH'];
        $_MENU = null;
        $_BODY = null;

        $JS_INCLUDE[] = '/shared/js/jquery-1.3.2.min.js';

        $_LANG = null;
        $_CHARSET = null;
    }
}
