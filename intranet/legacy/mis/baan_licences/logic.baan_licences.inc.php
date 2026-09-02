<?php

$DEFAULT_TITLE .= " \ BAAN LICENCES USAGE";

$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=baan_licences">Home</a>
EOF;
if ($user->isInGroup(['SUPERUSER', 'gg_EXCOM'])) {
    $DEFAULT_MENU .= <<<EOF
 | <a href="$php_self?m[0]=baan_licences&m[1]=reports">Reports</a>
EOF;
}

if (isset($m[1]) && $m[1] === 'reports') {
    $DEFAULT_TITLE .= ' REPORT';
    if (!$user->isInGroup(['SUPERUSER', 'gg_EXCOM'])) {
        $DEFAULT_ERROR[] = 'You are not allowed.';
        return;
    }

    $timePattern = '^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$';
    $form = new HTML_QuickForm('baan_licences usage', 'post');
    $form->addElement('hidden', 'm[0]', 'baan_licences');
    $form->addElement('hidden', 'm[1]', 'reports');
    $form->addElement('text', 'day', 'Day', ['class' => 'datepicker']);
    $form->addElement('text', 'hours_from', 'Hours from (00:00)', ['pattern' => $timePattern]);
    $form->addElement('text', 'hours_to', 'Hours to (23:59)', ['pattern' => $timePattern]);
    $form->addElement('text', 'UsageSuperiorTo', 'Minimun of licence used');
    $form->addElement('checkbox', 'xls', 'xls');
    $today = new \DateTime();
    $form->setDefaults([
        'day' => $today->format('Y-m-d'),
        'hours_from' => '00:00',
        'hours_to' => $today->format('H:i'),
        'UsageSuperiorTo' => 0,
    ]);
    $form->addRule('day', 'Required', 'required');
    $form->addRule('hours_from', 'Required', 'required');
    $form->addRule('hours_to', 'Required', 'required');
    $form->addRule('UsageSuperiorTo', 'Required', 'required');

    $form->addElement('submit', 'btnSubmit', 'Submit');
    $body .= $form->toHTML();

    if ($form->isSubmitted()) {
        $submittedValues = TldUtils::cleanupFormInput($form->exportValues());
        $dateTimeFrom = \DateTime::createFromFormat('Y-m-d H:i:s', $submittedValues['day'] . ' ' . $submittedValues['hours_from'] . ':00');
        $dateTimeTo = \DateTime::createFromFormat('Y-m-d H:i:s', $submittedValues['day'] . ' ' . $submittedValues['hours_to'] . ':59');

        if ($dateTimeTo === false || $dateTimeFrom === false) {
            $DEFAULT_ERROR[] = 'ERROR with the submitted values';
        } else {
            $query = <<<SQL
                SELECT erpl.*, peo.email FROM erp_licences AS erpl
                LEFT JOIN people AS peo ON erpl.baan_id = peo.baan_id        
                WHERE erpl.dt >= '{$dateTimeFrom->format('Y-m-d H:i:s')}' and erpl.dt <= '{$dateTimeTo->format('Y-m-d H:i:s')}'
SQL;
            if (is_numeric($UsageSuperiorTo = $submittedValues['UsageSuperiorTo'])) {
                $query .= <<<SQL
                    AND licences >= $UsageSuperiorTo
SQL;

            }
            $baanLicencesUsage = tldUtils::getSqlToAssocArray($query);
            $columns = [
                'id' => 'id',
                'licences' => 'licences',
                'baan_id' => 'baan_id',
                'baan_comp' => 'baan_comp',
                'department' => 'department',
                'dt' => 'dt',
                'desktop' => 'desktop',
                'process_id' => 'process_id',
                'client_id' => 'client_id',
                'email' => 'email',
            ];
            if ($submittedValues['xls']) {
                (new tldXLS($baanLicencesUsage, ['xItems' => $columns, 'showTitles' => true]))->out();
                exit;
            }
            $report = new tldReportColumnar($baanLicencesUsage,
                [
                    'xItems' => $columns,
                    'title' => 'BAAN LICENCES USAGE',
                ]
            );
            $body .= $report->fetch();
        }
    }
    return;
}

$report = new tldAssocTable(
    tldUtils::getSqlRowToAssocArray('SELECT COUNT(*) AS cnt, dt FROM erp_licences where dt = (SELECT dt FROM erp_licences ORDER by id DESC LIMIT 1)'),
    ['cnt' => 'Number of licence used', 'dt' => 'Last record'],
    ['title' => 'Baan licences usage']
);
$body .= $report->fetch();

$report = new tldReportColumnar(
    tldUtils::getSqlToAssocArray('SELECT baan_id, COUNT(*) AS cnt FROM erp_licences where dt = (SELECT dt FROM erp_licences ORDER by id DESC LIMIT 1) GROUP BY baan_id HAVING cnt > 1'),
    [
        'xItems' => [
            'baan_id' => 'Account',
            'cnt' => 'Licence Number',
        ],
        'title' => 'Multiple licences:',
    ]
);
$body .= $report->fetch();
