<?php

require_once __DIR__.'/DashboardRouting.php';
require_once __DIR__.'/controller/SelectionController.php';
/**
 * @var Smarty
 */
$smarty = TldUtils::getSmarty("shopfloor");
$routing = new DashboardRouting($_POST, $_GET);

$user = new tldUser($_SESSION['pi_user_id']);
$route = $routing->getRoute();
$selectionController = new SelectionController($smarty,$user);
switch($route){
    case 'selection':
        $selectionController->makeTheSelection();
        $body= $selectionController->render();
        break;
    case 'foreman_report_result':
        $template="NO_TEMPLATE";
        echo $selectionController->getReportResults($routing->getPost());
        break;
    case 'list':
         $selectionController->displayDashboardList();
         $body =$selectionController->render();
         break;
    default:
        echo ("ERROR404");
        break;

}
