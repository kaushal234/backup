<?php
/**
 * Smarty modifier: {$var|renderHtmlOrNl2br}
 *
 * Behavior:
 * - If $var contains HTML tags -> return as-is.
 * - Otherwise -> escape text and apply nl2br.
 *
 */
function smarty_modifier_renderHtmlOrNl2br($s, $charset = 'ISO-8859-1') {

    return tldUtils::renderHtmlOrNl2br($s, $charset = 'ISO-8859-1');

}
