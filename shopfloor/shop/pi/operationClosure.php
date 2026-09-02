<?php

use App\Pi\Utils\OperationController;

require_once 'PHPExcel.php';

global $client, $LANG;
$opController = new OperationController($client);
$opController->set($_SESSION['$firstCrabOpen'] ?? null, $_SESSION['$firstOpNotAns'] ?? null, $_SESSION['$firstDeroMissing'] ?? null);
$message = $opController->operationClosure((int)$opno, $id, $_SESSION['pi_user_id'], $pdno, $LANG);

echo $message;
exit;
