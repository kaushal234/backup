<?php

switch ($m[2]) {
    case 'flag':
    case 'survey':
        switch ($m[3]) {
            case 'edit':
        }
    case 'contacts':
        switch ($m[3]) {
            case 'add':
            case 'delete':
        }
    case 'members':
    case 'parts':
    case 'csr':
    case 'edit':
    case 'changeStatus':
    case "tasks":
    case 'partsReceived':
    case 'astArrival':
    case 'astDeparture':
    case 'log':
        include_once("toc/log/log.inc.php");
    case 'files':
        switch ($m[3]) {
            case 'add' :
            case 'download_all_files':
        }
    case 'links':
        switch ($m[3]) {
            case 'add':
            default:
        }
    default:
        $body = 'This page has been migrated and should not be used anymore.';
}
