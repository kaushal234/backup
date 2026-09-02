<?php

$DEFAULT_TITLE .= " \ Reports";
$body = '';

$moos = tldModule::getMOOList();
$developers = (new tldGroup('ROLE_DEV'))->getUserlist('smartyOptions');

$style = ['style' => 'width: 300px;'];

$form = new HTML_QuickForm('ticket_report_filter', 'get', '', null, null, true);
$form->addElement('advmultiselect', "developers", 'Developers', $developers, $style);
$form->addElement('advmultiselect', "moos", 'MOOs', $moos, $style);
$form->addElement('hidden', 'm[0]', 'reports');
$form->addElement('submit', 'btnSubmit', 'Filter');

$developerFilter = array_keys($developers);
$mooFilter = array_keys($moos);
if ($form->validate() && $form->isSubmitted()) {
    $developerFilter = $form->getSubmitValue('developers') ?? $developerFilter;
    $mooFilter = $form->getSubmitValue('moos') ?? $mooFilter;
}
$body.= $form->toHTML();

global $kernel;
$templating = $kernel->getContainer()->get('twig.legacy');

$body.= '<h3>Tickets by module</h3>';
$body.= $templating->render('helper/_matrix_table.html.twig', [
    'data' => tldTTS::byModuleStatusAndDeveloper(['OPEN', 'IN PROGRESS', 'PAUSE'], $developerFilter, $mooFilter),
    'link' => [
        'route' => 'legacy_calendar',
        'additionalParams' => ['m' => ['tasks', 'taskList', 'byIntranetModule'], 'developers' => $developerFilter, 'moos' => $mooFilter],
        'xParam' => 'module',
        'yParam' => 'parameter'
    ],
]);

$body.= <<<EOF
<p>
    <small>CLOSED and PAUSE tickets are excluded from IF, A, B, C and "Assigned to..." columns.</small><br>
    <small>Assigned to a Dev means that the ticket is assigned to a user with the ROLE_DEV.</small>
</p>
EOF;
