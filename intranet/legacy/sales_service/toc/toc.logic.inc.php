<?php

include_once("sales_service.inc.php");

switch ($m[1]) {
    case 'form':
        include_once("toc/form.inc.php");
        break;
    case 'view':
        include_once("toc/view.inc.php");
        break;
    case 'kpi':
        include_once("toc/kpi.inc.php");
        break;
    case 'pull':
    case 'reports':
        switch ($m[2]) {
            case 'myTOCsParts':
            case 'furtherActionStats':
            case 'byASMSSO':
            case 'byTechSSO':
            case 'bySSOActivityType':
            default:
        }
    case 'listing':
        switch ($m[2]) {
            case 'surveyResultsDetails':
                switch ($m[3]) {
                    case 'bySSOID':
                    case 'byCriteria':
                }
            case 'timeToProcess':
            case 'furtherActionStats':
            case 'bySSOActivityType':
            case 'byAssignee':
            case 'bySSOStatusNotClosed':
            case 'bySSOStatusByASM':
            case 'byTechnician':
            case 'byASMStatusSSO':
            case 'byTechStatus':
                switch ($m[3]) {
                    case 'homepageMatrix':
                }
            case 'byAssigneeStatus':
                switch ($m[3]) {
                    case 'homepageMatrix':
                }
            case 'byTechStatusSSO':
            case 'byFactorySupportRequired':
            case 'bySSOStatus':
            case 'bySSOWF':
            case 'byFactoryWF':
            case 'byFactoryStatus':
            case 'bySSOAndFactoryStatus':
            case 'Search by ER#':
            case 'Search by SN':
            case 'byCustomerID':
            case 'byCustomerContactID':
            case 'Search by Customer Name':
            case 'Search by Contact Name':
            case 'search':
            case 'kpiByPeriod':
                switch ($m[4]) {
                    case 'byCustomerIDSSOID':
                    case 'bySSOID':
                    case 'byTecID':
                    case 'byActivityType':
                    default:
                }
                switch ($m[3]) {
                    case 'nto':
                    case 'tir':
                    case 'tat':
                    case 'tol':
                }
            case 'quickEdit':
            case 'update':
        }

        switch ($m[5]) {
            case 'xls':
            default:
        }
    default:
        $body = 'This page has been migrated and should not be used anymore.';
}
