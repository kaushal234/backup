<?php

declare(strict_types=1);

require_once 'common.inc.php';
require_once 'forms_and_reports.inc.php';
require_once 'user.inc.php';

ini_set('error_log', "$CRON_CACHE_DIR/edi.log");

error_log('RUNNING '.__FILE__);

$erps = [
    250 => tldGroup::getEmailList('gg_edi_notify', '250'),
    300 => 'parts@tld-america.com',
    400 => tldGroup::getEmailList('gg_edi_notify', '400'),
    420 => tldGroup::getEmailList('gg_edi_notify', '420'),
    500 => tldGroup::getEmailList('gg_edi_notify', '500'),
    510 => tldGroup::getEmailList('gg_edi_notify', '510'),
    520 => tldGroup::getEmailList('gg_edi_notify', '520'),
    540 => 'parts@tld-europe.com',
    600 => 'parts@tld-asia.com',
    620 => tldGroup::getEmailList('gg_edi_notify', '620'),
    640 => tldGroup::getEmailList('gg_edi_notify', '640'),
    660 => tldGroup::getEmailList('gg_edi_notify', '660'),
    680 => 'parts.china@tld-asia.com',
];

error_log('Look for edi.notifications.dat');

$notifiedFileData = $CRON_CACHE_DIR.'/edi.notifications.dat';

// Get notification history
$notified = [];
if ($contents = @file_get_contents($notifiedFileData)) {
    $notified = json_decode($contents, true);
}

$runDate = date('Y-m-d');

// Check if file modification time is today
if (!empty($notified) && date('Y-m-d', filemtime($notifiedFileData)) !== $runDate) {
    $notified = [];
}

foreach ($erps as $erp => $email) {
    $constraints = '';
    if (!empty($notified[$erp])) {
        $constraints = "AND tcedi702.t_orno NOT IN('".implode("','", $notified[$erp])."')";
    }

    $query = <<<SQL
	SELECT DISTINCT
		tcedi702.t_orno,
		tcedi702.t_rcvt,
		RTRIM(tcedi702.t_reno) AS t_reno,
		RTRIM(tcedi702.t_msno) AS t_msno,
		tcedi702.t_rcvd,
        RTRIM(tcedi702.t_koor) AS t_koor,
		'Sales Order' t_ckon,
		CONVERT(VARCHAR(10), tcedi702.t_rcvd, 120) t_rcvd,
		(
			SELECT TOP 1 RTRIM(tdsls050.t_user)
			FROM ttdsls050$erp tdsls050
			WHERE tdsls050.t_orno = tcedi702.t_orno
			ORDER BY tdsls050.t_trdt, tdsls050.t_trtm
		) t_user,
		RTRIM(tdsls040.t_refa) AS t_refa
	FROM ttcedi702$erp tcedi702
    INNER JOIN ttdsls045$erp tdsls045 ON tcedi702.t_orno = tdsls045.t_orno
    INNER JOIN ttdsls040$erp tdsls040 ON tcedi702.t_orno = tdsls040.t_orno
	WHERE
		CONVERT(VARCHAR(10), tcedi702.t_rcvd, 120) = '$runDate' AND
		tcedi702.t_ckon = 3 AND
		tdsls045.t_ssls = 1 AND
		tcedi702.t_koor IN ('PN1','PD1','PN2', 'PW1', 'PD2', 'PN3', 'PN4', 'PN7')
		$constraints
	ORDER BY
		tcedi702.t_rcvd,
		tcedi702.t_rcvt
SQL;

    $orders = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    error_log("New orders for $erp?");
    if (empty($orders)) {
        continue;
    }

    error_log('Yes, processing order notifications...');
    $cc = $ccSage = $ordersSage = $ordersP21 = [];
    foreach ($orders as $key => &$order) {
        // Special cases
        if (680 === $erp && 'PN7' === $order['t_koor']) {
            $ordersP21[] = $orders;
            unset($orders[$key]);
            continue;
        }
        // Track order already notified
        $notified[$erp][] = $order['t_orno'];
        $rcvt = str_pad((string) $order['t_rcvt'], 4, '0', STR_PAD_LEFT);
        $order['t_rcvt'] = substr($rcvt, 0, 2).':'.substr($rcvt, 2, 2);
        // get to for cc
        $people = tldUtils::getSqlRowToAssocArray("SELECT email FROM people WHERE baan_id='{$order['t_user']}' LIMIT 1");
        if (!empty($people['email'])) {
            $cc[] = $people['email'];
        } elseif (!isset($order['t_reno']) || false === strpos($order['t_reno'], 'WEB')) {
            $cc[] = 'gary.edbrooke@tld-america.com';
            $cc[] = 'william.coley@tld-america.com';
        }

        if ('job000' === $order['t_user'] && in_array($erp, [250, 300, 540, 600, 680], true) && 0 !== strpos($order['t_reno'], 'WEB')) {
            $ordersSage[] = $order;
            unset($orders[$key]);
            $ccSage[] = $order['t_refa'];
            if (250 === $erp || 300 === $erp) {
                $ccSage[] = 'rgrijalva@sageparts.com';
            } elseif (600 === $erp || 680 === $erp) {
                $ccSage[] = 'DGoedel@sageparts.com';
            }
        }
    }

    // P21 orders notification
    if ([] !== $ordersP21) {
        $formP21 = new tldReportColumnar(
            $ordersP21,
            [
                'xItems' => [
                    't_ckon' => 'Type',
                    't_orno' => 'Sales Order Number',
                    't_rcvd' => 'Date Received',
                    't_rcvt' => 'Time Received',
                    't_reno' => 'Relation',
                    't_msno' => 'Order Reference',
                    't_user' => 'User',
                ],
                'title' => "$erp EDI Sales Orders Notification",
                'sortable' => true,
                'showItemNumbers' => true,
            ]
        );
        echo $report = $formP21->fetch();
        echo '<br>';

        error_log("Sending P21 TO llu@sageparts.com) for $erp");
        tldUtils::emailAttachment('llu@sageparts.com', 'noreply@tld-gse.com', "$erp EDI Sales Orders Notification", $report, null, 'rleung2@sageparts.com');
    }

    // Sage orders notification
    if ([] !== $ordersSage) {
        $formSage = new tldReportColumnar(
            $ordersSage,
            [
                'xItems' => [
                    't_ckon' => 'Type',
                    't_orno' => 'Sales Order Number',
                    't_rcvd' => 'Date Received',
                    't_rcvt' => 'Time Received',
                    't_reno' => 'Relation',
                    't_msno' => 'Order Reference',
                    't_user' => 'User',
                ],
                'title' => "$erp EDI Sales Orders Notification",
                'sortable' => true,
                'showItemNumbers' => true,
            ]
        );
        echo $report = $formSage->fetch();
        echo '<br>';

        error_log('Sending Sage TO '.(is_array($email) ? implode(',', $email) : $email)." for $erp");
        tldUtils::emailAttachment($email, 'noreply@tld-gse.com', "$erp EDI Sales Orders Notification", $report, null, array_unique($ccSage));
    }
    if ([] !== $orders) {
        $form = new tldReportColumnar(
            $orders,
            [
                'xItems' => [
                    't_ckon' => 'Type',
                    't_orno' => 'Sales Order Number',
                    't_rcvd' => 'Date Received',
                    't_rcvt' => 'Time Received',
                    't_reno' => 'Relation',
                    't_msno' => 'Order Reference',
                    't_user' => 'User',
                    't_refa' => 'Ref A',
                ],
                'title' => "$erp EDI Sales Orders Notification",
                'sortable' => true,
                'showItemNumbers' => true,
                'links' => [
                    't_orno' => "http://www.tld-gse.com/en/private/parts/parts.php?m[1]=bySearch&erp=$erp&orno=",
                ],
            ]
        );
        echo $report = $form->fetch();

        error_log('Sending TO '.(is_array($email) ? implode(',', $email) : $email)." for $erp");
        tldUtils::emailAttachment($email, 'noreply@tld-gse.com', "$erp EDI Sales Orders Notification", $report, null, array_unique($cc));
    }
}

// Store notification history
file_put_contents($CRON_CACHE_DIR.'/edi.notifications.dat', json_encode($notified));

error_log('END OF '.__FILE__);
