<?php

$DEFAULT_TITLE .= "\CRABS";


switch ($m[1]) {
	case 'graph':
    case 'reports':
    case 'report':
    case 'fullreport':
    case 'listing':
        $body = "This page has been migrated and should not be displayed anymore.";
		break;
	case 'view':
		switch ($m[2]) {
            case 'duplicate':
            case 'transfer':
			case 'filteringFlag':
			case 'inspected':
			case 'tasks':
			case 'log':
			case 'files':
			case 'links':
			default:
                $body = "This page has been migrated and should not be displayed anymore.";
                break;
		}
		break;
	case 'form':
		switch ($m[2]) {
			case 'newCRABPDI':
			case 'newCRAB':
			case 'newCRAB1':
			case 'byNum':
			case 'byNCR':
			case 'search':
                $body = "This page has been migrated and should not be displayed anymore.";
                break;
		}
		break;
	default:
        $body = "This page has been migrated and should not be displayed anymore.";
}
