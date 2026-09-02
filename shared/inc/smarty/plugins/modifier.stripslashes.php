<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty strip_tags modifier plugin
 *
 * Type:     modifier<br>
 * Name:     stripslashes<br>
 * Purpose:  strip slashes from text
 * @link http://smarty.php.net/
 * @author Julien Lambert
 * @param string
 * @return string
 */


function smarty_modifier_stripslashes($string){
    return stripslashes($string);
}

?>