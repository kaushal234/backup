<?php

use ApiBundle\Client;
use ApiBundle\Iri\Iri;

include_once('eng.inc.php');
require_once 'HTML/QuickForm/advmultiselect.php';


global $kernel;
$container = $kernel->getContainer();
try {
    $router = $container->get('router');
    $client = $container->get(Client::class);
    $module = $client->findOneBy('modules', ['name' => 'MEAP']);

    $route = $router->generate('directory_people_show', ['id' => Iri::id($module['operationalOwner']['@id'])]);
} catch (Exception $exception) {
    $DEFAULT_ERROR[] = 'Could not get module.';
    $route = null;
}

$DEFAULT_TITLE .= "\Master EAP";
$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=meap">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=meap&m[1]=form&m[2]=byNum">By number</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=meap&m[1]=form&m[2]=new" title="Submit a new Master EAP">Submit</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=meap&m[1]=listing&m[2]=search">Search</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=meap&m[1]=reports">Reports</a>
&nbsp;|&nbsp;<a href="/en/private/mis/mis.php?m[0]=help&m[1]=dms&id=329">Help</a>
&nbsp;|&nbsp;<a href="/en/private/mis/mis.php?m[0]=help&m[1]=dms&id=328">Other Help</a>
EOF;

if (null !== $route) {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$route">MOO</a>
EOF;
}

if ($user->isInGroup(['eap', 'gg_mod_eap_admin', 'gg_ENG', 'gg_ADMIN'])) {
    $DEFAULT_MENU .= <<<EOF
	&nbsp;|&nbsp;<a href="meap/meap_admin.php">Maintain MEAP</a>
EOF;
}

switch ($m[1]) {
    case 'form':
        switch ($m[2]) {
            case 'tasksByUserByMEAP':
                $uid = ($uid > 0) ? (int)$uid : $user->getID();
                $eng = new tldUser($uid);
                $is_eng = $eng->isInGroup(['gg_ENG']);
                $bu = new tldLocation($eng->getBUID());
                $erp = $bu->getERP();
                $engGrp = new tldGroup('gg_ENG', tldLocation::getERPByID($user->getBUID()));
                if (!count($engGrp->getUserlist())) {
                    $engGrp = new tldGroup('gg_ENG');
                }
                $engineers = tldUtils::optionsByKeyValue($engGrp->getUserlist(), 'id', 'fullname');
                $form = new HTML_QuickForm('frmNew', 'post');
                $form->addElement('header', 'title', 'View task list By User By MEAP ');
                $form->addElement('hidden', 'm[0]', 'meap');
                $form->addElement('hidden', 'm[1]', 'form');
                $form->addElement('hidden', 'm[2]', 'tasksByUserByMEAP');
                $form->addElement('text', 'id', 'MEAP#');
                $form->addElement('select', 'uid', 'Engineer', ['' => ''] + $engineers);
                $form->addElement('checkbox', 'csv', 'Format', '.csv');
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->addRule('id', 'Field is numeric', 'numeric');
                $form->addRule('id', 'Required', 'required');
                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break;
                }

                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $title = 'Task list';
                $WHERE = '';
                if ($vars['id']) {
                    $WHERE = " WHERE id={$vars['id']}";
                    $meap = new tldMEAP($vars['id']);
                    $title .= '  MEAP#' . $vars['id'] . '::' . $meap->getShortDesc();
                }

                if (!$assignedMeaps = array_column(tldUtils::getSqlToAssocArray("SELECT id FROM meap $WHERE ORDER BY id"), 'id')) {
                    break;
                }  // exit if no assigned meaps
                $WHERE = '';
                if ($vars['uid']) {
                    $WHERE = " AND assignee={$vars['uid']} ";
                    $poster = new tldUser($vars['uid']);
                    $title .= ' for ' . $poster->getFullname();
                }

                $eaps = $meaps = [];
                foreach ($assignedMeaps as $mid) {
                    $meap = new tldMEAP($mid);
                    if ($meap->isEmpty()) {
                        continue;
                    }
                    $eaps = $meaps = [];
                    foreach ($meap->getFamilyTree(true, true) as $mod) {
                        if ($mod['module'] === 'EAP') {
                            $eaps[] = $mod['id'];
                        }
                        if ($mod['module'] === 'MEAP') {
                            $meaps[] = $mod['id'];
                        }
                    }
                }

                if (!$eaps && !$meaps) {
                    break;
                }

                $ADDITIONAL_WHERE .= ' AND (';
                if ($eaps) {
                    $ADDITIONAL_WHERE .= sprintf(" (parent_id IN (%s) AND  module='EAP')", implode(',', $eaps));
                }
                if ($meaps) {
                    if ($eaps) {
                        $ADDITIONAL_WHERE .= ' OR ';
                    }
                    $ADDITIONAL_WHERE .= sprintf(" (parent_id IN (%s) AND  module='MEAP')", implode(',', $meaps));
                }

                $ADDITIONAL_WHERE .= ') ';

                $query = <<<SQL
                SELECT id, task, due_date
				FROM tasks
				WHERE  status!='CLOSED' $ADDITIONAL_WHERE $WHERE ORDER BY due_date ASC
SQL;
                $a = tldUtils::getSqlToAssocArray($query);

                $report = new tldReportColumnar(
                    $a,
                    [
                        'xItems' => [
                            'id' => 'ID',
                            'task' => 'Task Description',
                            'due_date' => 'Due Date',
                        ],
                        'title' => $title,
                        'sortable' => 'false',
                        'links' => ['id' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id='],
                        'showNumberOfRows' => true,
                    ]
                );
                if ($a) {
                    $xItems = [
                        'id' => 'ID',
                        'task' => 'Task Description',
                        'due_date' => 'Due Date',
                    ];
                    $sess['meap']['list'] = $a;
                    $sess['meap']['xItems'] = $xItems;
                    // Specific csv request from forms field
                    if (!empty($csv)) {
                        $output = 'csv';
                    }

                    if ($output === 'csv') {
                        $report = new tldCSV(
                            $sess['meap']['list'],
                            [
                                'xItems' => $sess['meap']['xItems'],
                                'showTitles' => true,
                            ]
                        );
                        $report->out('meap_tasklist.csv');
                        exit;
                    }
                }

                $body = $report->fetch();
                break;
            case 'byNum':
                $DEFAULT_TITLE .= "\MEAP by Number";
                $form = new HTML_QuickForm('frmByNum', 'post');
                $form->addElement('hidden', 'm[0]', 'meap');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('header', 'title', 'MEAP Number');
                $form->addElement('text', 'id', 'MEAP#');
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $body = $form->toHTML();
                break;
            case 'new':
                $ifAllowed = $approvers = [];
                if ($user->isInGroup(['role_ENG', 'role_EM', 'role_PSM', 'role_COO', 'gg_ADMIN'])) {
                    $ifAllowed['10'] = '(10) BOM and drawing update';
                }
                if ($user->isInGroup(['role_ENG', 'role_EM', 'role_PSM', 'role_COO', 'role_RCEO', 'role_RCOO', 'role_EVP', 'gg_EXCOM'])) {
                    $ifAllowed['100'] = '(100) Current production affected';
                    $ifAllowed['1000'] = '(1000) Safety affected';
                    $ifAllowed['10000'] = '(10000) System development for different product';
                }
                if (empty($ifAllowed)) {
                    $DEFAULT_ERROR[] = 'You do not have the permissions to open new MEAP';
                    break;
                }
                $form = new HTML_QuickForm('frmNew', 'post');
                $form->addElement('header', 'title', 'Submit New MEAP Form');
                $form->addElement('hidden', 'm[0]', 'meap');
                $form->addElement('hidden', 'm[1]', 'form');
                $form->addElement('hidden', 'm[2]', 'new');
                $form->addElement('select', 'proj_leader', 'Project Leader', ['' => ''] + tldGroup::getUserListByMultipleGroup(['gg_ENG', 'role_ENG'], null, 'smartyOptions'));
                $factories = tldUtils::getSqlToAssocArray("SELECT id,location FROM locations WHERE factory='Y' ORDER BY location", 'smartyOptions', ['id', 'location']);
                $form->addElement('select', 'factory', 'Business Unit', ['' => ''] + $factories);
                $form->addElement('select', 'product_type', 'Product Type', ['ALL_TYPES' => 'ALL_TYPE', 'SYSTEM' => 'SYSTEM'] + tldUtils::getSqlToAssocArray('SELECT en FROM products_categories ORDER BY en', 'smartyOptions', 'en'));
                $form->addElement('select', 'ifactor', 'Importance Factor', ['' => ''] + $ifAllowed, ['onChange' => "toggleSisterBusinessUnitDisplay();"]);
                $form->addElement('select', 'model', 'Product Model', ['' => ''] + tldUtils::getSqlToAssocArray('SELECT model FROM models ORDER BY model', 'smartyOptions', 'model'));
                $form->addElement('text', 'prototype', 'or Prototype Name', ['size' => '28']);
                $form->addElement('select', 'econ_capitalized', 'Is Capitalized?', ['' => '', 'Y' => 'Y', 'N' => 'N', 'S' => 'Sold Program']);
                $form->addElement('select', 'econ_currency', 'Currency', ['' => ''] + tldMEAP:: getEconCurrencyList());
                $form->addElement('select', 'type', 'Type', ['' => ''] + array_combine(tldMEAP::getTypes(), tldMEAP::getTypes()), ['onChange' => "updateShortDescriptionSuffix();"]);
                $form->addElement('select', 'purpose', 'Purpose', ['' => ''] + array_combine(tldMEAP::getPurposes(), tldMEAP::getPurposes()));
                $form->addElement('text', 'short_desc', 'Short Description', ['size' => '50']);
                $form->addElement('text', 'parent_meap', 'Parent MEAP#', ['size' => '50']);
                $form->addElement('textarea', 'description', 'Full Description', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']);
                $form->addElement('file', 'up_filename', 'Upload File');
                $form->addElement('select', 'pvt', 'Confidential to members only?', ['' => '', 'Y' => 'Y', 'N' => 'N']);
                $form->addElement('select', 'cost_calculation_method', 'Cost calculation method', array_combine(tldMEAP::getCostsCalculationMethods(), tldMEAP::getCostsCalculationMethods()));
                $ams = $form->addElement('advmultiselect', 'members', null, tldDirectory::getUserlist('smartyOptions'), ['size' => 10, 'class' => 'pool', 'style' => 'width:500px;', ]);
                $ams->setLabel(['Members... (OPTIONAL)', 'Addressbook', 'CC']);
                $ams->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
                $ams->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
                $ams = $form->addElement('advmultiselect', 'factories', null, $factories, ['size' => 10, 'class' => 'pool', 'style' => 'width:200px;', ]);
                $ams->setLabel(['Sister Business Unit...', 'Factories', '']);
                $ams->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
                $ams->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);

                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->setDefaults(['proj_leader' => $user->getId()]);
                $form->addRule('proj_leader', 'This is required', 'required');
                $form->addRule('factory', 'This is required', 'required');
                $form->addRule('ifactor', 'This is required', 'required');
                $form->addRule('short_desc', 'This is required', 'required');
                $form->addRule('description', 'This is required', 'required');
                $form->addRule('pvt', 'This is required', 'required');
                $form->addRule('purpose', 'This is required', 'required');

                $body = <<<HTML
<script type="application/javascript">
    updateShortDescriptionSuffix = function() {
        var types = [];
        var i = 0;
        var length = document.querySelector('select[name="type"]').options.length
        for (i = 0; i < length; i++) {
          if (value = document.querySelector('select[name="type"]').options[i].value) {
            types.push(value + '-')
          }
        }
        
        var newType = document.querySelector('select[name="type"]').value
        shortDescription = document.querySelector('input[name="short_desc"]').value
        if (shortDescription.length !== 0) {
          for (i=0; i<types.length; i++) {
            if (shortDescription.startsWith(types[i])) {
                  return document.querySelector('input[name="short_desc"]').value = shortDescription.replace(types[i], newType+'-')
                }
          }
         
        } 
        document.querySelector('input[name="short_desc"]').value = newType + '-' + document.querySelector('input[name="short_desc"]').value
        
    }

    toggleSisterBusinessUnitDisplay = function() {
        document.querySelector('select[id^="factories-"]').closest('tbody').closest('tr').style.display = ('10000' === document.querySelector('select[name="ifactor"]').value) ? "" : 'none'
    }

    document.addEventListener("DOMContentLoaded", function() {
        toggleSisterBusinessUnitDisplay();
    });
</script>
HTML;

                if (!$form->validate()) {
                    $body .= $form->toHTML();
                    break;
                }

                $header = tldUtils::cleanupFormInput($form->exportValues());
                if ($header['pvt'] === 'Y' && empty($header['members'])) {
                    $DEFAULT_ERROR[] = 'You cannot set to confidential without having members';
                    $body .= $form->toHTML();
                    break;
                }
                if (!empty($header['prototype'])) {
                    $header['model'] = $header['prototype'];
                }
                if (empty($header['model'])) {
                    $DEFAULT_ERROR[] = 'ERROR: model or prototype name is not set...';
                    $body .= $form->toHTML();
                    break;
                }

                if ('10000' !== $header['ifactor']) {
                    $header['factories'] = [];
                } elseif (empty($header['factories'])){
                    $DEFAULT_ERROR[] = 'ERROR: You cannot set IF 10000 without other factories';
                    $body .= $form->toHTML();
                    break;
                }

                if ($header['parent_meap'] ?? null) {
                    $parent = new tldMEAP($header['parent_meap']);
                    if (!$parent->isEmpty())  {
                        if (10000 === (int)$parent->getImportanceFactor()) {
                            $header['ifactor'] = 1000;
                        }

                        $header['parent_module'] = 'MEAP';
                        $header['parent_id'] = $header['parent_meap'];
                    }
                }
                unset($header['parent_meap']);

                //process the upload file, if any
                $file = $form->getElement('up_filename');
                if ($file->isUploadedFile()) {
                    $attr = $file->getValue();
                    $destPath = tldMEAP::getFileUploadDirectory();
                    $filepath = $destPath . '/' . time() . $attr['name'];
                    while (file_exists($filepath)) {
                        $filepath = $destPath . '/' . time() . $attr['name'];
                    }
                    $header['filename'] = basicFile::cleanupName(basename($filepath));
                    $file->moveUploadedFile($destPath, $header['filename']);
                }
                //get text values and save to db
                $header['poster'] = $user->getId();
                $meapId = tldMEAP::insert($header);
                if (!is_numeric($meapId)) {
                    $smarty->assign('error', "Could not create new MEAP. There was an error processing. The error returned is '$meapId'");
                    break;
                }

                $meap = new tldMEAP($meapId);
                $short_desc = $meap->getShortDesc();
                $body = <<<EOF
A new Master EAP has been submitted. <br><br>
<a href="$php_self?m[0]=meap&m[1]=view&id=$meapId">
Click here to see MEAP#$meapId</a>
<hr>
$short_desc
EOF;
                $meap->notifyProjectLeader($body, "New Master EAP #$meapId has been OPENED");
                $meap->addLog($user->getID(), "Master EAP #$meapId OPENED by poster");
        }
        break;
    case 'view':
        include_once('meap/view.inc.php');
        break;
    case 'listing':
        switch ($m[2]) {
            case 'search':
                $DEFAULT_TITLE .= "\MEAP Search";
                // Get lists
                $FACTORIES = tldUtils::getSqlToAssocArray("SELECT id,location FROM locations WHERE factory='Y' ORDER BY location", 'smartyOptions', 'location');
                $STATUS = ['PROPOSAL' => 'PROPOSAL', 'GATE_PROPOSAL' => 'GATE_PROPOSAL', 'PHASE_0' => 'PHASE 0', 'PHASE_1' => 'PHASE 1',
                    'PHASE_2' => 'PHASE 2', 'PHASE_3' => 'PHASE 3', 'PHASE_4' => 'PHASE 4',
                    'SUSPENDED' => 'SUSPENDED', 'REJECTED' => 'REJECTED', 'CLOSED' => 'CLOSED',
                ];
                // Get erp# from gg_ENG role of the user
                $erp_gg_ENG = $user->isInGroup('gg_ENG');
                if (!is_array($erp_gg_ENG)) {
                    $idLocation = tldLocation::getIDByERP($erp_gg_ENG);
                    $factory = new tldLocation($idLocation);
                }
                // Get form
                $form = new HTML_QuickForm('frm', 'get', '', '', '', true);
                $form->addElement('header', 'title', 'Search MEAPs');
                $form->addElement('hidden', 'm[0]', 'meap');
                $form->addElement('hidden', 'm[1]', 'listing');
                $form->addElement('hidden', 'm[2]', 'search');
                $form->addElement('text', 'target', 'Search for...');
                $form->addElement('select', 'status', 'Status', ['ALL' => 'ALL'] + $STATUS);
                $form->addElement('select', 'location', 'Factory', ['ALL' => 'ALL'] + $FACTORIES);
                $form->addElement('select', 'type', 'Type', ['ALL' => 'ALL'] + array_combine(tldMEAP::getTypes(), tldMEAP::getTypes()));
                $form->addElement('select', 'purpose', 'Purpose', ['ALL' => 'ALL'] + array_combine(tldMEAP::getPurposes(), tldMEAP::getPurposes()));
                $form->addElement('select', 'ifactor', 'Importance Factor', ['ALL' => 'ALL', '10' => '10', '100' => '100', '1000' => '1000', '10000' => '10000']);
                $form->addElement('text', 'date_closed_from', 'Closed From', ['class' => 'datepicker']);
                $form->addElement('text', 'date_closed_until', 'Closed Until', ['class' => 'datepicker']);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                if ($factory instanceof tldLocation) {
                    $form->setDefaults(['location' => $factory->getShortName()]);
                }

                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break;
                }

                $vars = tldUtils::cleanupFormInput($form->exportValues());

                if (!empty($vars) && ($vars['target'] !== "" || $vars['date_closed_from'] !== "" || $vars['date_closed_until'] !== "")) {
                    $rows = tldMEAP::search($vars);
                } else {
                    $DEFAULT_ERROR[] = 'ERROR: Complete one of the fields: Search for..., Closed From, Closed Until';
                    $body = $form->toHTML();
                    break;
                }

                $_title = 'Search results';
                break;
            case 'byErpStatus':
                $erp = TldDatabase::escape($y);
                $status = TldDatabase::escape($x);
                $rows = tldMEAP::byERPStatus($erp, $status);
                $title = "MEAP $erp $status";
                break;
        }
        $sess['meap']['list'] = $rows ?? [];

        if (!count($rows ?? [])) {
            $DEFAULT_ERROR[] = 'ERROR: No Master EAPs found...';
            break;
        }

        $report = new tldReportColumnar($rows,
            [
                'xItems' => [
                    'id' => 'MEAP #',
                    'status' => 'Status',
                    'ifactor' => 'IF',
                    'date' => 'Date',
                    'product_type' => 'Type',
                    'model' => 'Model',
                    'short_desc' => 'Short Description',
                ],
                'title' => $TITLE,
                'links' => ['parent_id' => "$php_self?m[0]=meap&m[1]=view&id=", 'id' => "$php_self?m[0]=meap&m[1]=view&id="],
            ]
        );
        $body .= $report->fetch();
        break;
    case 'list':
        switch ($m[2]) {
            case 'latest':
                $sess['meap']['list'] = tldMEAP::byLatest();
                $report = new tldReportColumnar($sess['meap']['list'],
                    [
                        'xItems' => ['id' => 'MEAP #',
                        'status' => 'Status',
                        'date' => 'Date',
                        'product_type' => 'Type',
                        'model' => 'Model',
                        'short_desc' => 'Short Description',
                    ],
                        'links' => ['id' => "$php_self?m[0]=meap&m[1]=view&id="],
                        'title' => 'Recently Added Master EAPs',
                    ]
                );
                $body .= $report->fetch();
                break;
            case 'byStatusFactory':
                $report = new tldMatrix(tldMEAP::countByFactoryStatus(),
                    'status', 'location', 'num',
                    "$php_self?m[0]=meap&m[1]=listing&m[2]=byErpStatus",
                    'MEAP Count by Status, Factory');
                $body .= $report->fetch();
                break;
        }
        break;
    case 'reports':
        $DEFAULT_TITLE .= "\Reports";
        $body .= $smarty->fetch("$PATH/meap/homepage.reports.tpl");
        break;
    case 'capitalized_project':
        include_once 'meap/report.capitalized_project.inc.php';
        break;
    case 'gantt':
        $DEFAULT_TITLE .= "\Gantt";
        $overlib = $smarty->fetch('overlib.inc.js.tpl');
        $overlib .= '<script type="text/javascript">$(function(){$("[data-body]").overlib()});</script>';
        $smarty->assign('html_head', $overlib);
        $smarty->assign('width', '100%');
        $x = str_replace(['GP', 'G0', 'G1', 'G2', 'G3', 'G4'], ['GATE_PROPOSAL', 'GATE_0', 'GATE_1', 'GATE_2', 'GATE_3', 'GATE_4'], $x);
        $rows = _getGanttData(TldDatabase::escape($x), TldDatabase::escape($y), ($z === 'opened'));
        foreach ($rows AS &$row) {
            $row['meap_log_html'] = str_replace("\n", '&lt;br/&gt;&lt;br/&gt;', $row['meap_log']);
            $row['dashboard'] = $row['id'];
        }
        $title = [];
        if (!empty($x) && $x !== 'ALL') {
            $title[] = "Status: $x";
        }
        if (!empty($y) && $y !== 'ALL') {
            $title[] = "Factory: $y";
        }
        $title = (!empty($title)) ? 'by ' . implode(' & ', $title) : '';
        $form = new tldGanttChart('MEAP', $rows, [
            // xItems
            'id' => 'ID',
            'dashboard' => 'View Dashboard',
            'factory_fullname' => 'Factory',
            'ifactor' => 'IF',
            'status' => 'Status',
            'short_desc' => 'Description',
            'econ_target_dh' => 'Budgetted Hours',
            'econ_eac_dh' => 'EAC',
            'econ_actual_dh' => 'Actual Hours',
            'meap_log_html' => 'Log',
            'date' => 'PHASE CLOSURE PLANNING',
        ], [
            // Options
            'title' => "Master EAP Gantt Chart $title",
            'parentAttributes' => [
                'table' => 'border="0"',
            ],
            'sortable' => ['id', 'factory_fullname', 'ifactor', 'status', 'short_desc', 'econ_target_dh', 'econ_eac_dh', 'econ_actual_dh'],
            'links' => [
                'id' => "$php_self?m[0]=meap&m[1]=view&id=",
                'dashboard' => "$php_self?m[0]=meap&m[1]=view&m[2]=dashboard&id=",
            ],
            'groupAttributes' => [
                'factory_fullname' => [
                    // EUR
                    'TLD MTL' => 'style="font-weight:bold;background:#369;border:solid 1px #369;color:#fff;"',
                    'TLD STL' => 'style="font-weight:bold;background:#fff;border:solid 1px #369;color:#369;"',

                    // ASI
                    'TLD SHA' => 'style="font-weight:bold;background:#090;border:solid 1px #090;color:#fff;"',
                    'TLD WUX' => 'style="font-weight:bold;background:#fff;border:solid 1px #090;color:#090;"',

                    // AME & Default
                    'TLD WIN' => 'style="font-weight:bold;background:#666;border:solid 1px #666;color:#fff;"',
                    'TLD SHE' => 'style="font-weight:bold;background:#fff;border:solid 1px #666;color:#;666"',

                    // Default
                    'style="font-weight:bold;background:#609;border:solid 1px #609;color:#fff;"',
                ],
            ],
            'columnSettings' => [
                'id' => [
                    'callback' => 'intval',
                    'title' => 'ID #%d',
                ],
                'econ_target_dh' => [
                    'callback' => 'intval',
                    'title' => 'Budgetted Hours : %d',
                ],
                'econ_actual_dh' => [
                    'callback' => 'intval',
                    'title' => 'Actual Hours : %d',
                ],
                'dashboard' => [
                    'callback' => 'intval',
                    'icon' => 'idea',
                    'useIconHeader' => true,
                    'useIconValue' => true,
                    'title' => 'View dashboard for MEAP#%d',
                ],
                'ifactor' => [
                    'type' => 'ifactor',
                ],
                'short_desc' => [
                    'type' => 'description',
                ],
                'meap_log_html' => [
                    'icon' => 'log',
                    'useIconValue' => true,
                    'cellAttributes' => 'data-body="%s"',
                    'tdTitle' => 'meap_log',
                ],
                'date' => [
                    'type' => 'gantt',
                    'zoom' => 'month',
                    'rangeBack' => 3,
                    'rangeForward' => 11,
                    'subgroup' => true,
                    'subgroupDateFormat' => 'o',
                    'ticDateFormat' => 'M',
                    'displayCallback' => static function ($line, $a, $b) {
                        for ($display = [], $idx = 0; $idx < 5; $idx++) {
                            $pcd = explode('|', $line["pcd{$idx}"]);
                            foreach ($pcd as $key => $date) {
                                if (!$date || !($time = strtotime($date))) {
                                    continue;
                                }
                                if ($time >= $a && $time <= $b) {
                                    $display[] = $key == 1 ? "<span style='color:red;min-width:0;'>{$idx}</span>" : $idx;
                                }
                            }
                        }
                        // Add management key event
                        $events = explode('|', (string) $line['key_evt_mngt']);
                        foreach ($events as $date) {
                            if (!$date || !($time = strtotime($date))) {
                                continue;
                            }
                            if ($time >= $a && $time <= $b) {
                                $display[] = 'K';
                            }
                        }
                        // Add actual key event
                        $events = explode('|', (string) $line['key_evt_act']);
                        foreach ($events as $date) {
                            if (!$date || !($time = strtotime($date))) {
                                continue;
                            }
                            if ($time >= $a && $time <= $b) {
                                $display[] = '<span style="color:red;min-width:0;">K</span>';
                            }
                        }

                        return (!empty($display)) ? implode(',', $display) : '&nbsp;';
                    },
                ],
            ],
        ]);
        $sess['meap']['list'] = $form->itsData;
        $body .= '<br />' . $form->fetch();
        break;
    default:
        $body .= $smarty->fetch("$PATH/meap/homepage.meap.tpl");
        $body .= "<p><a href=\"$php_self?m[0]=meap&m[1]=gantt\">View ALL Gantt Chart</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        $body .= "<a href=\"$php_self?m[0]=meap&m[1]=gantt1\">View All MEAP (including closed MEAP)</a></p>";
        $fn_gate = static function ($row) {
            switch ($row['status']) {
                case 'GATE_PROPOSAL':
                    $row['status'] = 'GP';
                    break;
                case 'GATE_0':
                    $row['status'] = 'G0';
                    break;
                case 'GATE_1':
                    $row['status'] = 'G1';
                    break;
                case 'GATE_2':
                    $row['status'] = 'G2';
                    break;
                case 'GATE_3':
                    $row['status'] = 'G3';
                    break;
                case 'GATE_4':
                    $row['status'] = 'G4';
                    break;
            }
            return $row;
        };

        if ($m[1] === 'gantt1') {
            $form = new tldMatrix(array_map($fn_gate, tldMEAP::countByFactoryStatus($status = 'ALL')),
                'status', 'location', 'num',
                "$php_self?m[0]=meap&m[1]=gantt",
                'MEAP Count by Status, Factory (View Gantt by Status, Factory)',
                ['xItems' => ['PROPOSAL', 'GP', 'SUSPENDED', 'PHASE_0', 'G0', 'PHASE_1', 'G1', 'PHASE_2', 'G2', 'PHASE_3', 'G3', 'PHASE_4', 'G4', 'CLOSED', 'REJECTED'], 'doNotShowXTotals' => true]);
        } else {
            $form = new tldMatrix(array_map($fn_gate, tldMEAP::countByFactoryStatus($status = 'OPENED')),
                'status', 'location', 'num',
                "$php_self?m[0]=meap&m[1]=gantt&z=opened",
                'MEAP Count by Status, Factory (View Gantt by Status, Factory)',
                ['xItems' => ['PROPOSAL', 'GP', 'SUSPENDED', 'PHASE_0', 'G0', 'PHASE_1', 'G1', 'PHASE_2', 'G2', 'PHASE_3', 'G3', 'PHASE_4', 'G4'], 'doNotShowXTotals' => true]);

        }
        $body .= $form->fetch();
}
function _getGeneralTab()
{
    global $header, $meap;

    $report = new tldAssocTable(
        $header,
        [
            'id' => 'MEAP #',
            'status' => 'Status',
            'pvt' => 'Is Confidential?',
            'ifactor' => 'Importance Factor',
            'date' => 'Date Entered',
            'factory_fullname' => 'Factory',
            'product_type' => 'Product Type',
            'short_desc' => 'Short Description',
            'proj_leader_fullname' => 'Project Leader',
            'poster_fullname' => 'Poster',
            'purpose' => 'Purpose',
            'description' => 'Full Description',
            'months_open' => 'Months Open',
        ],
        ['title' => 'Master EAP Details']
    );
    $ret = $report->fetch();

    if (10000 === (int) $meap->getImportanceFactor()) {
        $report = new tldReportColumnar(
            $meap->getInvolvedFactories(),
            [
                'xItems' => [
                    'location' => 'BU',
                ],
                'title' => 'Sister Business units',
            ]
        );
        $ret .= $report->fetch();
    }

    $form = new tldReportColumnar(
        $meap->getEAPSummary(),
        [
            'xItems' => [
                'id' => 'EAP#',
                'status' => 'Status',
                'location' => 'Location',
                'ifactor' => 'iFactor',
                'short_desc' => 'Description',
                'tasks_all' => 'No of Tasks',
                'tasks_open' => 'Tasks Open',
                'tasks_closed' => 'Tasks Closed',
                'tasks_late' => 'Tasks Late',
                'total_est_hours' => 'Total Estimated Time (Hours)',
                'total_open_est_hours' => 'Total Estimated Time on OPEN Tasks (Hours)',
                'total_hours_actual' => 'Total Actual Time (Hours)',
                'bp_open' => 'BPs Open',
                'bp_closed' => 'BPs Closed',
            ],
            'links' => ['id' => "$php_self?m[0]=eap&m[1]=view&id="],
            'title' => 'EAP Task Summary',
        ]
    );
    $ret .= $form->fetch();

    global $kernel;
    $client = $kernel->getContainer()->get(\ApiBundle\Client::class);

    try {
        $response = $client->get('quality/first_article_qualifications', [
            'query' => [
                'meap' => $meap->getID(),
                'order' => ['createdAt' => 'desc'],
            ],
        ]);
    } catch (\Symfony\Component\HttpClient\Exception\ClientException $e) {
        $response = ['hydra:member' => []];
    }

    $router = $kernel->getContainer()->get('router');

    $faqs = array_reduce($response['hydra:member'], static function ($memo, $faq) use ($router) {
        $memo[] = [
            'link' => sprintf('<a href="%s">#%s</a>', $router->generate('first_article_qualifications_show', ['id' => $faq['id']]), $faq['id']),
            'factory' => $faq['location']['name'],
            'created_at' => (new \DateTime($faq['createdAt']))->format('Y-m-d h:i:s'),
        ];
        return $memo;
    }, []);

    $faqsReport = new tldReportColumnar($faqs,
        [
            'xItems' => [
                'link' => 'ID',
                'factory' => 'Factory',
                'created_at' => 'Created At',
            ],
            'title' => 'FAQ list',
        ]
    );
    $ret .= $faqsReport->fetch();

    return $ret;
}

/**
 * @param tldMEAP $meap
 */
function _viewBPSTab($meap)
{
    $form = new tldReportColumnar(
        $meap->getBPS('ALL'),
        [
            'xItems' => [
                'id' => 'BP#',
                'status' => 'Status',
                'dt_opened' => 'Date Opened',
                'dt_closed' => 'Date Closed',
                'short_desc' => 'Description',
            ],
            'title' => 'Processes',
            'links' => ['id' => '/en/private/calendar/calendar.php?m[0]=bp&m[1]=view&id='],
        ]
    );
    return $form->fetch();
}

function _getGanttData($status = '', $factory = '', $open_only = false)
{
    $factory = strtoupper($factory);
    if (!empty($factory) && $factory !== 'ALL') {
        $WHERE[] = "(erp.location='$factory' OR l.location='$factory')";
    }
    if (!empty($status) && $status !== 'ALL') {
        $WHERE[] = "meap.status='$status'";
    }
    if ($open_only) {
        $WHERE[] = "UPPER(meap.status) NOT IN('CLOSED','REJECTED')";
    }
    if (!empty($WHERE)) {
        $WHERE = 'WHERE ' . implode(' AND ', $WHERE);
    }
    $query = <<<EOF
SELECT
	meap.*,
	erp.location AS factory_fullname,
	erp.erp AS factory_erp,
	(SELECT GROUP_CONCAT(CONCAT(date,' - ',comment) ORDER BY mod_logs.id DESC SEPARATOR "\n")
	FROM mod_logs
	WHERE mod_logs.module='MEAP'
	AND mod_logs.parent_id=meap.id
	AND mod_logs.log_num=0
	GROUP BY mod_logs.parent_id
	LIMIT 10) AS meap_log,
	(SELECT CONCAT_WS('|',IFNULL(pcd.management_target,''),IFNULL(pcd.actual_date,''))
	FROM meap_pcd pcd
	WHERE pcd.type='MEAP PHASE' AND pcd.description='PHASE 0 CLOSURE' AND pcd.parent_id=meap.id
	LIMIT 1) AS pcd0,
	(SELECT CONCAT_WS('|',IFNULL(pcd.management_target,''),IFNULL(pcd.actual_date,''))
	FROM meap_pcd pcd
	WHERE pcd.type='MEAP PHASE' AND pcd.description='PHASE 1 CLOSURE' AND pcd.parent_id=meap.id
	LIMIT 1) AS pcd1,
	(SELECT CONCAT_WS('|',IFNULL(pcd.management_target,''),IFNULL(pcd.actual_date,''))
	FROM meap_pcd pcd
	WHERE pcd.type='MEAP PHASE' AND pcd.description='PHASE 2 CLOSURE' AND pcd.parent_id=meap.id
	LIMIT 1) AS pcd2,
	(SELECT CONCAT_WS('|',IFNULL(pcd.management_target,''),IFNULL(pcd.actual_date,''))
	FROM meap_pcd pcd
	WHERE pcd.type='MEAP PHASE' AND pcd.description='PHASE 3 CLOSURE' AND pcd.parent_id=meap.id
	LIMIT 1) AS pcd3,
	(SELECT CONCAT_WS('|',IFNULL(pcd.management_target,''),IFNULL(pcd.actual_date,''))
	FROM meap_pcd pcd
	WHERE pcd.type='MEAP PHASE' AND pcd.description='PHASE 4 CLOSURE' AND pcd.parent_id=meap.id
	LIMIT 1) AS pcd4,
         (SELECT GROUP_CONCAT(pcd.management_target SEPARATOR '|') FROM meap_pcd pcd WHERE type = 'KEY EVENT' AND pcd.parent_id = meap.id) as key_evt_mngt,
         (SELECT GROUP_CONCAT(pcd.actual_date SEPARATOR '|') FROM meap_pcd pcd WHERE type = 'KEY EVENT' AND pcd.parent_id = meap.id) as key_evt_act

FROM
	meap
	LEFT JOIN locations AS erp ON meap.factory=erp.id
	LEFT JOIN meap_factories mf on meap.id = mf.parent_id
    LEFT JOIN locations l on mf.factory_id = l.id
$WHERE
GROUP BY meap.id
ORDER BY
	meap.ifactor DESC,
	meap.date DESC,
	meap.id DESC
EOF;
    return tldUtils::getSqlToAssocArray($query);
}

function _getProvisional($meap, $status, $edit = false)
{
    global $smarty, $user, $sess;
    $smarty->assign('edit', $edit);
    $smarty->assign('user', $user);
    $smarty->assign('meap', $meap);
    $smarty->assign('status', $status);
    return $smarty->fetch('manufacturing/eng/meap/provisional.edit.tpl');
}

function _getEconomics($meap, $childHeaders = [], $edit = false)
{
    global $smarty, $user, $sess;
    $smarty->assign('edit', $edit);
    $smarty->assign('user', $user);
    $smarty->assign('meap', $meap);
    $smarty->assign('displayEngineeringHours', !empty($childHeaders));
    if (!empty($childHeaders)) {
        $smarty->assign('childHeaders', $childHeaders);
    }
    return $smarty->fetch('manufacturing/eng/meap/econ.edit.tpl');
}

