<?php

if(!isset($_SESSION))
{
    session_start();
}
if (strpos(strtoupper($_SERVER['HTTP_USER_AGENT']), 'ANDROID')) {
    $displayMode='mobile';
} else {
    $displayMode='fixe';
}

$DEFAULT_TITLE .= "\Time Keeping";
$_SESSION['pi_font_size']='medium'; // xx-small -> x-small -> small -> medium -> large -> x-large -> xx-large
$DEFAULT_MENU ="";
$ClockMode='browser'; // browser / apache

$body = "time keeping";
if (($m[1] ?? '') === '') {
    $m[1]='ident';
}
if (($m[2] ?? '') !='') {
    $LOCAT['erp']=$m[2];
} else {
    $LOCAT['erp']=$LOCATION['erp'];
    $m[2]=$LOCATION['erp'];
}

$timeZone = null;
if (($LOCAT['erp'] === '220')) {
    $timeZone = new \DateTimeZone('Europe/London');
}
if (($LOCAT['erp'] === '250')) {
    $timeZone = new \DateTimeZone('America/Boise');
}
if (in_array($LOCAT['erp'], ['500', '510', '520', '530', '540', '570'], true)) {
    $timeZone = new \DateTimeZone('Europe/Paris');
}
if (in_array($LOCAT['erp'], ['600', '620', '640','660'], true)) {
    $timeZone = new \DateTimeZone('Asia/Shanghai');
}
if (($LOCAT['erp'] === '820')) {
    $timeZone = new \DateTimeZone('Asia/Kolkata');
}
if (($LOCAT['erp'] === '430')) {
    $timeZone = new \DateTimeZone('America/Chicago');
}

$localDateTime = (new \DateTime('now', $timeZone));
$HRADate=$localDateTime->format('Y-m-d');
$HRATime=$localDateTime->format('H:i:s');
$_SESSION['dateLocale']=$HRADate;
$_SESSION['timeLocale']=$HRATime;

$body = include("$PATH/ident.timekeeping.tpl.php");
$local_clock_hidden=$_POST['local_clock_hidden'] ?? null;
$_SESSION['local_clock_hidden']=$local_clock_hidden;
//$_SESSION['message'] = null;

if (isset($_POST['tk_user_input'], $_POST['tk_transaction_type_input'], $_POST['tk_task_input'], $_POST['tk_order_input'], $_POST['tk_operation_input'])) {
    $employeeNumber =  $_POST['tk_user_input'] === '' ? null : htmlspecialchars($_POST['tk_user_input']);
    $transactionType = htmlspecialchars($_POST['tk_transaction_type_input']);
    $task = $_POST['tk_task_input'] === '' ? null : htmlspecialchars($_POST['tk_task_input']);
    $orderNumber = $_POST['tk_order_input'] === '' ? null : htmlspecialchars($_POST['tk_order_input']);
    $operationNumber = $_POST['tk_operation_input'] === '' ? null : htmlspecialchars($_POST['tk_operation_input']);
    $comment = $_POST['tk_comment_input'] === '' ? null : htmlspecialchars($_POST['tk_comment_input']);

    // Post Time_keeping manually
    // only in production
    $clientIp=tldUtils::getClientIp();
    if ($clientIp!=="192.168.56.1") {
        // Log transactions
        $query="insert into pi_timekeeping_log (id, comp, int_user, baan_user, t_koot, t_pdno, t_opno, t_tano, created_on, user_agent, remote_addr) values ( NULL, ";
        $query.="'".$LOCAT['erp']."', ";
        $query.="'0', ";
        $query.="'".$employeeNumber."', ";
        $query.="'".$transactionType."', ";
        $query.="'".$orderNumber."', ";
        $query.="'".$operationNumber."', ";
        $query.="'".$task."', ";
        $query.="NOW(), ";
        $query.="'".$_SERVER['HTTP_USER_AGENT']."', ";
        $query.="'".$clientIp."') ";
        tldUtils::sqlInsert($query);

        try {
            $transaction = $client->save('/ion/time_keepings', [
                    'employeeNumber' => $employeeNumber,
                    'transactionType' => $transactionType,
                    'task' => (string) $task,
                    'orderNumber' => $orderNumber,
                    'operationNumber' => (int) $operationNumber,
                    'comment' => (string) $comment,
                ]);
            foreach ($transaction['lines'] as $transaction) {
                $session->getFlashBag()->add('success', $translator->trans('time_keeping.transaction_posted', [], 'time_keeping'));
                if ($transaction['transactionType'] === 'DIRECT') {
                    $session->getFlashBag()->add('success', $translator->trans('time_keeping.success_transaction_direct_message', [
                        '%employeeNumber%' => $transaction['employeeNumber'],
                        '%firstname%' => mb_convert_encoding($transaction['firstname'], $charset, 'UTF-8'),
                        '%lastname%' => mb_convert_encoding($transaction['lastname'], $charset, 'UTF-8'),
                        '%transactionType%' => $transaction['transactionType'],
                        '%productionOrder%' => $transaction['productionOrder'],
                        '%operationNumber%' => $transaction['operationNumber'],
                        '%status%' => $transaction['status'],
                    ], 'time_keeping'));
                }
                if ($transaction['transactionType'] === 'INDIRECT'){
                    $session->getFlashBag()->add('success', $translator->trans('time_keeping.success_transaction_indirect_message', [
                        '%employeeNumber%' => $transaction['employeeNumber'],
                        '%firstname%' => mb_convert_encoding($transaction['firstname'], $charset, 'UTF-8'),
                        '%lastname%' => mb_convert_encoding($transaction['lastname'], $charset, 'UTF-8'),
                        '%transactionType%' => $transaction['transactionType'],
                        '%task%' => $transaction['task'],
                        '%status%' => $transaction['status'],
                    ], 'time_keeping'));
                }
            }

            if ($transactionType === 'END-ACTIVE'){
                unset($_SESSION['pi_opno']);
            }
        } catch (\Exception $exception) {
            error_log(sprintf('Timekeeping POST to ION error: [%s] %s', $exception->getCode(), $exception->getMessage()));
            $session->getFlashBag()->add('error', $translator->trans('time_keeping.error_transaction_message', [], 'time_keeping'));
            $session->getFlashBag()->add('error', $exception->getMessage());
        }
    }
    header("location:/shop/autoselect.php?m[0]=time_keeping");
    exit();
}
