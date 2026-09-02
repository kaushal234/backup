<?php
$DEFAULT_TITLE .= "\ODP";

switch ($m[1] ?? []) {
    case 'reports':
    case 'listing':
    case 'charts':
    case 'matrix':
    default:
        $body = "This page has been migrated and should not be displayed anymore.";
}
