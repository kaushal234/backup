<?php
$DEFAULT_TITLE .= "\\Vendors";

switch ($m[1]) {
    case 'updateRep':
    case 'view':
    case 'byNumber':
    case 'search':
    case 'selectVendor':
    case 'qualification':
    case 'classification':
    case 'master':
    case 'updatenotation':
    case 'comp-dashboard':
    case 'comp-report':
    case 'reports':
    case 'listing':
    case 'charts':
    default:
    $body .= "This page has been migrated and should not be displayed anymore.";
}

