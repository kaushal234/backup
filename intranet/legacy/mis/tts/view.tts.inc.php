<?php

switch($m[2]){
case 'reports':
case 'members':
case 'changeif':
case 'resc':
case 'changeStatus':
case 'delete':
case 'log':
case 'files':
case 'notes':
case 'conclusion':
case 'timesheets':
case 'tasks':
default:
    $body = 'This page has been migrated and should not be used anymore.';
}
