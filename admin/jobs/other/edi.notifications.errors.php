<?php
// Get includes
require_once 'common.inc.php';
require_once 'erp.inc.php';
require_once 'forms_and_reports.inc.php';

ini_set('error_log', "$CRON_CACHE_DIR/hourly.bat.log");
error_log("RUNNING {$PHP_SELF} by user " . get_current_user());

// Define who gets notifications (between erp range)
$who = [
    [
        'between' => [200, 499],
        'email' => ['gary.edbrooke@tld-america.com', 'william.coley@tld-america.com'],
    ],
    [
        'between' => [500, 599],
        'email' => ['aymeric.demerindol@tld-europe.com', 'parts@tld-europe.com', 'gary.edbrooke@tld-america.com', 'william.coley@tld-america.com'],
    ],
    [
        'between' => [600, 799],
        'email' => ['chenyang.gu@tld-asia.com', 'gary.edbrooke@tld-america.com', 'william.coley@tld-america.com'],
    ],
];

$erps = (new tldERP())->theERPS;
sort($erps);

// Iterate ERPs
foreach ($erps as $erp) {
    $email = null;
    foreach ($who as $available) {
        if ($erp >= min($available['between']) && $erp <= max($available['between'])) {
            $email = $available['email'];
            break;
        }
    }
    if (!$email) {
        continue;
    }

    $query = <<<EOF
SELECT
	tcedi750.t_btno,
	tcedi750.t_brec,
	tcedi750.t_reno,
	tcedi750.t_mess,
	tcedi750.t_ckon,
	tcedi750.t_eono
FROM
	ttcedi750{$erp} tcedi750
WHERE tcedi750.t_pflg=1 AND tcedi750.t_aflg=2
EOF;

    $errors = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);

    if (!$errors) {
        continue;
    }

    //Flag for INCL. SAGE
    $title = "{$erp} EDI ERROR Notification";
    foreach ($errors as $error) {
        if (stripos($error['t_reno'], 'sage') !== 0) {
            continue;
        }

        $title .= ' - incl. SAGE';
        $email = array_merge((array) $email, ['parts@tld-america.com ']);
        break;
    }
    // Create report
    $form = new tldReportColumnar(
        $errors,
        [
            'xItems' => [
                't_btno' => 'Batch Number',
                't_brec' => 'Batch Record Number',
                't_reno' => 'Relation',
                't_mess' => 'EDI Message',
                't_ckon' => 'Type of Number',
                't_eono' => 'Order Reference',
            ],
            'title' => $title,
            'sortable' => true,
        ]
    );
    echo $report = $form->fetch();

    // Send notification
    tldUtils::emailAttachment(
        $email,
        'noreply@tld-gse.com',
        $title,
        $report
    );
}

