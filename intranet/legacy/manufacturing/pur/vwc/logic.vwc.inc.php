<?php

use ApiBundle\Client;

include_once("sales_service.inc.php");
$DEFAULT_TITLE .= "\VWC";

switch($m[1]){
    case 'view':
        switch($m[2]){
        case 'scars':
        case 'edit':
        case 'parts':
        case 'followers':
        case 'links':
        case 'tasks':
        case 'log':
        case 'shipping':
        case 'finance':
        case 'statusSelect':
        case 'reopen':
        case 'REWORK':
        case 'SCRAP':
        case 'RETURN_FOR_CREDIT':
        case 'REPLACE':
        case 'VENDOR_TO_RESPOND':
        case 'REVIEW_VENDOR_RESPONSE':
        case 'PENDING':
        case 'CREATE_PO':
        case 'SHIP_TO_VENDOR':
        case 'ISSUE_CREDIT_NOTE':
        case 'REC_FROM_VENDOR':
        case 'ISSUE_DEBIT_NOTE':
        case 'VALIDATE_SCAR':
        case 'QA ANALYSIS':
        case 'CLOSED_RESOLVED':
        case 'CLOSED_LOW_VALUE':
        case 'CLOSED_VENDOR_REJECTED':
        case 'CLOSED_NOT_VENDOR_ISSUE':
        case 'CLOSED':
        default:
            $body = "This page has been migrated and should not be displayed anymore.";
            break;
        }
    break;
    case 'list':
        switch($m[2]) {
            case 'byWC':
            case 'byNCR':
            case 'search':
            case 'forGST':
            case 'byPN':
            case 'byAssigneeStatus':
            case 'byAssigneeTitleStatus':
            case 'byErpStatus':
            case 'vwcPast12Month':
            case 'byVendorStatus':
            case 'byERP_SSO_Assignee':
            case 'byERP':
            case 'byVendor':
            case 'byBuyer':
            case '4E':
            case 'latest':
            case 'byVendorERPOpenStatus':
            case 'download':
            case 'byVendorERP':
            default:
                $body = "This page has been migrated and should not be displayed anymore.";
            break;
        }
    case 'forms':
    case 'transfer':
    case 'email':
    case 'reports':
    default:
        $body = "This page has been migrated and should not be displayed anymore.";
        break;
}
