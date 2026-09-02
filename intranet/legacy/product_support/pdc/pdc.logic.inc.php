<?php
include_once 'calendar.inc.php';
include_once 'sales_service.inc.php';
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/advmultiselect.php';

use ApiBundle\Client;
use AppBundle\Chart\ChartBuilderFactory;
use Symfony\Component\HttpClient\Exception\ClientException;

// Get MOO ID
$moo_id = tldModule::getMOOIDByModule('pdc');
$PATH .= '/pdc';
$DEFAULT_TITLE .= "\PDC Module";
$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=pdc">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pdc&m[1]=forms&m[2]=byNum">By number</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pdc&m[1]=listing&m[2]=search">Search</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pdc&m[1]=forms&m[2]=add">Add</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pdc&m[1]=reports">Reports</a>
&nbsp;|&nbsp;<a href="/en/private/mis/mis.php?m[0]=help&m[1]=dms&id=314">PDC Procedure & Help Page</a>
&nbsp;|&nbsp;<a href="/en/private/directory/index.php?m[0]=people&m[1]=view&id=$moo_id" title="Module Operation Owner">MOO</a>
EOF;

if ($user->isInGroup(['demerit', 'gg_SUPPORT', 'gg_ADMIN'])) {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="pdc/pdc_admin.php">Maintain PDC</a>
EOF;
}

switch ($m[1]) {
    case 'forms':
        switch ($m[2]) {
            case 'byNum':
                $form = new HTML_QuickForm('frmById');
                $form->addElement('hidden', 'm[0]', 'pdc');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('header', 'title', 'By Number');
                $form->addElement('text', 'id', 'PDC#');
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $body = $form->toHTML();
                break;
            case 'add':
                $DEFAULT_TITLE .= "\Add";
                // Default values
                $defaults['initiator'] = $user->getID();
                $defaults['factory'] = $user->getBUID();
                $defaults['description'] = <<<EOF
                    <p>- Detailed problem description:</p>
                    
                    <p>- Specific circumstances or conditions associated to the problem (e.g. type of operation, environment or ambient, vehicle speed, engaged gear, etc)</p>
                    
                    <p>- Occurrence: how many times have you noticed this problem has occurred, within what timeframe? Across one or several machines (if so how many machines)?</p>
                    
                    <p>- Hypothesis on root cause: Have you been able to reproduce the problem? Any assumption on root cause?</p>
                EOF;
                // Creation requested from TOC module?
                if (!empty($_REQUEST['tocid'])) {
                    global $kernel;

                    try {
                        /** @var Client client */
                        $client = $kernel->getContainer()->get(Client::class);
                        $technicianOnCall = $client->get(sprintf('/service/technician_on_calls/%d', $_REQUEST['tocid']));

                        $indiceFactor = match ($technicianOnCall['indiceFactor']) {
                            'IF 1' => '1',
                            'IF 10' => '10',
                            'IF 100' => '100',
                            'IF 1000' => '1000',
                            default => '1'
                        };

                        $defaults = [
                            'factory' => $technicianOnCall['equipmentRecord']['manufacturerLocation']['legacyId'] ?? null,
                            'ifactor' => $indiceFactor,
                            'product_type' => $technicianOnCall['equipmentRecord']['type'] ?? null,
                            'model' => $technicianOnCall['equipmentRecord']['model'] ?? null,
                            'short_desc' => $technicianOnCall['title'],
                            'description' => $technicianOnCall['description'],
                            'is_ibs' => in_array('toc.tags.ibs', array_column($technicianOnCall['tags'], 'name'), true),
                            'is_ihs' => in_array('toc.tags.ihs', array_column($technicianOnCall['tags'], 'name'), true),
                            'is_link' => in_array('toc.tags.link', array_column($technicianOnCall['tags'], 'name'), true),
                            'is_apu_off' => in_array('toc.tags.apu_off', array_column($technicianOnCall['tags'], 'name'), true),
                            'initiator' => $user->getID(),
                        ];
                    } catch (ClientException $e) {
                        $DEFAULT_ERROR[] = "TOC#$tocid not found";
                    }
                }
                // Listing
                $peopleList = tldDirectory::getUserlist('smartyOptions');
                $categoryList = ['ALL_TYPES' => 'ALL_TYPE'] + tldType::getTypes('en', 'smartyOptions');
                $modelList = tldModel::getList();
                $factoryList = tldLocation::getFactoryList('smartyOptionsIDLocation');
                $statusList = tldPDC::getStatusList();
                $iFactorList = tldPDC::getIFactorList();
                $engGroup = new tldGroup('role_ENG');
                $engineers = tldUtils::optionsByKeyValue($engGroup->getUserlist(), 'id', 'fullname');
                $qamGroup = new tldGroup('role_QAM');
                $qams = tldUtils::optionsByKeyValue($qamGroup->getUserlist(), 'id', 'fullname');

                $assignees = ['' => ''] + array_unique($qams + $engineers);
                sort($assignees);
                // Form
                $form = new HTML_QuickForm('frmNewDemerit', 'post');
                $form->addElement('header', 'title', 'Create PDC');
                $form->addElement('hidden', 'm[0]', 'pdc');
                $form->addElement('hidden', 'm[1]', 'forms');
                $form->addElement('hidden', 'm[2]', 'add');
                $form->addElement('hidden', 'tocid', $_REQUEST['tocid']);
                $form->addElement('select', 'initiator', 'Initiator', $peopleList);
                $form->addElement('select', 'assignee', 'Assignee', $assignees);
                $form->addElement('select', 'factory', 'Location', ['' => ''] + $factoryList);
                $form->addElement('select', 'ifactor', 'Importance Factor', $iFactorList);
                $form->addElement('select', 'product_type', 'Product Type', $categoryList);
                $form->addElement('select', 'model', 'Product Model', $modelList);
                $form->addElement('checkbox', 'is_ibs', 'Involves iBS');
                $form->addElement('checkbox', 'is_ihs', 'Involves iHS/ipHS');
                $form->addElement('checkbox', 'is_link', 'Involves LINK');
                $form->addElement('checkbox', 'is_apu_off', 'Involved APU-OFF');
                $form->addElement('text', 'short_desc', 'Short Description', ['size' => '50']);
                $form->addElement('textarea', 'description', 'Full Description',
                    ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']);
                $form->addElement('file', 'picture_filename', 'Picture');
                // Required
                $requiredFields = ['initiator', 'factory', 'ifactor', 'product_type', 'model', 'short_desc', 'description'];
                foreach ($requiredFields as $field) {
                    $form->addRule($field, 'This is required', 'required');
                }
                $form->setDefaults($defaults);
                $form->addElement('submit', 'btnSubmit', 'Submit', ['class' => 'disablesubmit']);

                if (!$form->validate()) {
                    $body .= $form->toHTML();
                    break;
                }

                $header = tldUtils::cleanupFormInput($form->exportValues());
                // Process the uploaded file if any
                /** @var HTML_QuickForm_file $file */
                $file = $form->getElement('picture_filename');
                if ($file->isUploadedFile()) {
                    $attr = $file->getValue();
                    $destPath = tldPDC::getPathToUploadFile();
                    // Create file name and check if not existing already
                    $filepath = $destPath . time() . $attr['name'];
                    while (file_exists($filepath)) {
                        $filepath = $destPath . time() . $attr['name'];
                    }
                    $header['picture_filename'] = basicFile::cleanupName(basename($filepath));
                    $file->moveUploadedFile($destPath, $header['picture_filename']);
                }
                // Create PDC
                $header['poster'] = $user->getID();
                $error = tldPDC::insert($header);
                if (is_string($error)) {
                    $DEFAULT_ERROR[] = "INTERNAL ERROR: Could not create PDC. Reason: $error";
                    break;
                }

        // Add assignee as follower
        tldModMember::insert('PDC', $error, $header['assignee']);

                $body .= <<<EOF
<p>PDC#$error successfully created! <a href="$php_self?m[0]=pdc&m[1]=view&id=$error">Click here to view...</a></p>
EOF;
                $pdc = new tldPDC($error);
                // Log
                $pdc->addLogEntry($user->getID(), 'PDC created');
                // Notify initiator
                $message = '<p>A new PDC has been created.</p>';
                $pdc->notifyInitiator($message, "New PDC#$error has been created, IF: ".$pdc->getIFactor());
                $body .= <<<EOF
<p>PDC notified to initiator</p>
EOF;
                // Check if comes from toc
                if ($technicianOnCall) {
                    // link PDC to TOC
                    $pdc->addLinkTo('TOC', $technicianOnCall['id']);
                    $body .= <<<EOF
<p>TOC#{$technicianOnCall['id']} linked to PDC</p>
EOF;
}
                break;
        }
        break;
    case 'view':
        include 'pdc/pdc.view.inc.php';
        break;
    case 'reports':
        $DEFAULT_TITLE .= "\Reports";
        switch ($m[2]) {
            case 'fwByProductType':
                // List
                $categoryList = ['ALL_TYPES' => 'ALL_TYPE'] + tldType::getTypes('en', 'smartyOptions');
                // Form
                $form = new HTML_QuickForm('frm', 'post');
                $form->addElement('hidden', 'm[0]', 'pdc');
                $form->addElement('hidden', 'm[1]', 'reports');
                $form->addElement('hidden', 'm[2]', 'fwByProductType');
                $form->addElement('header', 'title', 'Select product category');
                $form->addElement('select', 'category', 'Product category', ['' => ''] + $categoryList);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->addRule('category', 'This is required', 'required');

                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break;
                }

                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $rows = tldPDC::fweightByFieldByConstraints('model', ['product_type' => $vars['category']]);
                // display
                $report = new tldReportColumnar(
                    $rows,
                    [
                        'xItems' => [
                            'model' => 'Model',
                            'total_fweight' => 'Sub total Focus Weight',
                        ],
                        'title' => "Subtotal Focus Weight for '{$vars['category']}' by model",
                    ]
                );
                $body .= $report->fetch();
                break;
            case 'fwByFactory':
                $rows = tldPDC::fweightByFieldByConstraints('factory_fullname');
                // display
                $report = new tldReportColumnar(
                    $rows,
                    [
                        'xItems' => [
                            'factory_fullname' => 'Factory',
                            'total_fweight' => 'Sub total Focus Weight',
                        ],
                        'title' => 'Subtotal Focus Weight by factory',
                    ]
                );
                $body .= $report->fetch();
                break;
            case 'latePdc':
                $pdcStatus = ['' => null, 'PENDING' => 'PENDING', 'ACTION' => 'ACTION', 'INVESTIGATION' => 'INVESTIGATION'];
                $factoryList = ['' => null] + tldLocation::getFactoryList('smartyOptionsIDLocation');
                $form = new HTML_QuickForm('frm', 'post');
                $form->addElement('hidden', 'm[0]', 'pdc');
                $form->addElement('hidden', 'm[1]', 'reports');
                $form->addElement('hidden', 'm[2]', 'latePdc');
                $form->addElement('header', 'title', 'Select Factory and status');
                $form->addElement('select', 'status', 'PDC Status', $pdcStatus);
                $form->addElement('select', 'factory', 'PDC factory', $factoryList);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->addRule('category', 'This is required', 'required');

                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break;
                }

                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $rows = tldPDC::getLatePdc($vars['factory'], $vars['status']);

                $report = new tldReportColumnar(
                    $rows,
                    [
                        'xItems' => [
                            'id' => 'ID',
                            'status' => 'PDC status',
                            'location' => 'Factory',
                            'comment' => 'log comment',
                            'date' => 'log date',
                        ],
                        'title' => 'Late PDC (no update since 30 days)',
                    ]
                );
                $body .= $report->fetch();
                break;
            case 'OpenTasksBySSOERPStatus':
                $DEFAULT_TITLE .= "\Open PDC Tasks";

                $form = new HTML_QuickForm('frm', 'get', "", "", "", true);
                $form->addElement('hidden', 'm[0]', 'pdc');
                $form->addElement('hidden', 'm[1]', 'reports');
                $form->addElement('hidden', 'm[2]', 'OpenTasksBySSOERPStatus');
                $form->addElement('header', 'title', "View PDC Open Tasks by SSO, ERP, Status");
                $form->addElement('select', "factory", 'Factory', ["ALL" => "ALL"]
                    + tldLocation::getFactoryList("smartyOptionsLocationLocation")
                );
                $pdcStatuses = tldPDC::getStatusList();
                $statusForm = $form->addElement(
                    'advmultiselect', 'statuses', null,
                    array_combine($pdcStatuses, $pdcStatuses),
                    [
                        'size' => 6,
                        'class' => 'pool',
                        'style' => 'width:150px;',
                    ]
                );
                $statusForm->setLabel(['Status']);
                $statusForm->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
                $statusForm->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->addRule('factory', 'This is required', 'required');
                $form->addRule('statuses', 'This is required', 'required');
                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break;
                }

                $vals = tldUtils::cleanupFormInput($form->exportValues());
                $rows = tldPDC::byFactoryStatus($vals['factory'], $vals['statuses']);

                foreach ($rows AS $row) {
                    $gantts[$row['id']] = tldTask::getGanttOverview('PDC', $row['id'], "status<>'CLOSED'");
                    $pdc = new tldPDC($row['id']);
                    $meaps = $eaps = $subEaps = [];
                    foreach ($pdc->getLinksFromHere("MEAP") as $link) {
                        $meaps[] = $link['item'];
                    }
                    foreach ($pdc->getLinksToHere("MEAP") as $link) {
                        $meaps[] = $link['parent_id'];
                    }
                    // get all tasks of MEAP and child EAP recursively
                    foreach ($meaps as $module_id) {
                        $gantts[$row['id']] = array_merge($gantts[$row['id']], (array)tldTask::getGanttOverview('MEAP', $module_id, "status<>'CLOSED'"));
                        $meap = new tldMEAP($module_id);
                        $family = $meap->getFamilyTree(true, true);
                        foreach ($family as $mod) {
                            if ($mod['module'] === 'EAP') {
                                $subEaps[] = $mod['id'];
                                $gantts[$row['id']] = array_merge($gantts[$row['id']], (array)tldTask::getGanttOverview('EAP', $mod['id'], "status<>'CLOSED'"));
                            }
                        }
                    }
                    foreach ($pdc->getLinksFromHere("EAP") as $link) {
                        $eaps[] = $link['item'];
                    }
                    foreach ($pdc->getLinksToHere("EAP") as $link) {
                        $eaps[] = $link['parent_id'];
                    }
                    $eaps = array_diff($eaps, $subEaps);
                    foreach ($eaps as $module_id) {
                        $gantts[$row['id']] = array_merge($gantts[$row['id']], (array)tldTask::getGanttOverview('EAP', $module_id, "status<>'CLOSED'"));
                    }
                }

                if (!count($gantts)) {
                    $DEFAULT_ERROR[] = "Unable to find any open tasks";
                    break;
                }
                $DEFAULT_MENU .= <<<EOF
        <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
EOF;
                $overlib = $smarty->fetch('overlib.inc.js.tpl');
                $overlib .= '<script type="text/javascript">$(function(){$("[data-body]").overlib()});</script>';
                $smarty->assign("html_head", $overlib);
                $smarty->assign("width", "100%");

                $groups = [];
                foreach ($gantts AS $pdc => $gantt) {
                    // Get PDC Info
                    $pdcHeader = (new tldPDC($pdc))->itsHeader;
                    $model_name = $pdcHeader['model'];
                    $reports = [];
                    $gCnt = count($gantt);
                    if ($gCnt) {
                        // Prepare gantt report
                        $ovd = 0;
                        foreach ($gantt AS &$task) {
                            $task['late_stats'] = "Opened: {$task['date']} Due: {$task['due_date']}";
                            if ($task['overdue']) {
                                $ovd++;
                                $task['late'] = 'OVERDUE';
                                $task['late_stats'] .= " ({$task['days_late']} days late)";
                            } elseif ($task['status'] !== 'CLOSED') {
                                $task['late'] = 'ON TIME';
                                $task['late_stats'] .= " (on time)";
                            } else {
                                $task['late'] = 'N/A';
                                $task['late_stats'] .= " (closed)";
                            }
                            $task['last_ten_comments_html'] = str_replace("\n", "&lt;br/&gt;", htmlentities($task['last_ten_comments']));
                            $task['task_add_comment'] = $task['id'];
                            $task['task_reschedule'] = $task['id'];
                            $task['task_transfer'] = $task['id'];
                            $task['task_close'] = $task['id'];
                            $task['status'] = $pdcHeader['status'];
                            $task['model'] = $model_name;
                        }
                        $form = new tldGanttChart("PDC_OPENTASKS_$pdc", $gantt, [
                            // xItems
                            'id' => 'ID',
                            'assignor_lastname' => 'Assignor',
                            'ifactor' => 'iFactor',
                            'status' => 'Status',
                            'assignee_fullname' => 'Assignee',
                            'task' => 'Task Description',
                            'date' => 'Days Open',
                            'last_ten_comments_html' => 'Log',
                            'late' => 'Late',
                            'task_add_comment' => 'Add Comment',
                            'task_reschedule' => 'Reschedule',
                            'task_transfer' => 'Transfer',
                            'task_close' => 'Close',
                        ], [
                            // Options
                            'parentAttributes' => [
                                'table' => 'border="0"',
                            ],
                            'sortable' => [],
                            'links' => [
                                'id' => "/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=",
                                'task_add_comment' => "/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=comment&id=",
                                'task_reschedule' => "/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=reschedule&id=",
                                'task_transfer' => "/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=transfer&id=",
                                'task_close' => "/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=closeConfirm&id=",
                            ],
                            'groupAttributes' => [
                                'late' => [
                                    'OVERDUE' => 'style="font-weight:bold;background:#900;color:#fff;"',
                                    'ON TIME' => 'style="font-weight:bold;background:#090;color:#fff;"',
                                ],
                            ],
                            'groupHeaders' => [
                                [
                                    'columns' => ['task_add_comment', 'task_reschedule', 'task_transfer', 'task_close'],
                                    'title' => 'Actions',
                                ],
                            ],
                            'columnSettings' => [
                                'id' => [
                                    'callback' => 'intval',
                                    'title' => 'ID #%d',
                                ],
                                'ifactor' => [
                                    'type' => 'ifactor',
                                ],
                                'task' => [
                                    'type' => 'description',
                                ],
                                'last_ten_comments_html' => [
                                    'icon' => 'log',
                                    'useIconValue' => true,
                                    'cellAttributes' => 'data-body="%s"',
                                    'imgTitle' => 'last_ten_comments',
                                    'tdTitle' => 'last_ten_comments',
                                ],
                                'date' => [
                                    'type' => 'gantt',
                                    'zoom' => 'day',
                                    'due' => 'due_date',
                                ],
                                'late' => [
                                    'tdTitle' => 'late_stats',
                                ],
                                'task_add_comment' => [
                                    'callback' => 'intval',
                                    'icon' => 'mail',
                                    'useIconHeader' => true,
                                    'useIconValue' => true,
                                    'title' => "Add New Comment Task#%d",
                                ],
                                'task_reschedule' => [
                                    'callback' => 'intval',
                                    'icon' => 'schedule',
                                    'useIconHeader' => true,
                                    'useIconValue' => true,
                                    'title' => "Reschedule Task#%d",
                                ],
                                'task_transfer' => [
                                    'callback' => 'intval',
                                    'icon' => 'transfer',
                                    'useIconHeader' => true,
                                    'useIconValue' => true,
                                    'title' => "Transfer Task#%d",
                                ],
                                'task_close' => [
                                    'callback' => 'intval',
                                    'icon' => 'close',
                                    'useIconHeader' => true,
                                    'useIconValue' => true,
                                    'title' => "Close Task#%d",
                                ],
                            ],
                        ]);

                        $groups[] = <<<EOF
<h3>PDC#$pdc / $model_name </h3>
<p>
	<b>Open Tasks:</b> $gCnt &nbsp; <b>Overdue:</b> $ovd &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="$php_self?m[0]=pdc&m[1]=view&id=$pdc" title="View PDC#$pdc">View PDC</a>
</p>
{$form->fetch()}
<br/>
EOF;
                        $pdctasks[] = $gantt;
                    }
                }
                $k = 0;
                foreach ($pdctasks as $key => $val) {
                    foreach ($val as $key2 => $val2) {
                        $newhello[$k] = $val2;
                        $k++;
                    }
                    $k++;
                }
                $sess['task']['list'] = $newhello;
                $body .= implode("\n<br/><hr/><br/>\n", $groups);
                break;

            default:
                $body = $smarty->fetch("$PATH/reports/homepage.reports.tpl");
                break;
        }
        break;
    case 'charts':
        switch ($m[2]) {
            case 'historyByFactory':
            case 'historyByFactoryByWeek':
                $form = new tldHTMLList(
                    tldLocation::getFactoryList(),
                    [
                        'key' => ['erp' => 'erp'],
                        'value' => ['location'],
                    ],
                    "$php_self?m[0]=demerits&m[1]=charts&m[2]=${m[2]}",
                    ['title' => 'Please select Factory']
                );
                $body = $form->fetch();
                // Display
                if ($erp) {
                    $body = <<<EOF
<img src="pdc/reports/graphs.php?m[0]=${m[2]}&erp=$erp"><br>
EOF;
                }
                break;
            case 'historyAllFactories':
                $body = <<<EOF
<img src="pdc/reports/graphs.php?m[0]=historyAllFactories"><br>
EOF;
                break;
            case 'openedPerMonth':
                $body = <<<EOF
<img src="pdc/reports/graphs.php?m[0]=openedPerMonth"><br>
EOF;
                break;
        }
        break;
    case 'listing':
        $xItems = [
            'id' => 'PDC #',
            'date' => 'Date',
            'ifactor' => 'IF',
            'fweight' => 'Focus weight',
            'status' => 'Status',
            'factory_fullname' => 'Factory',
            'product_type' => 'Type',
            'model' => 'Model',
            'short_desc' => 'Short Description',
            'date_closed' => 'Date Closed',
            'closure' => 'Closure Comment',
            'assignee_fullname' => 'Assignee',
            'toc_count' => 'TOCs Open',
        ];

        switch ($m[2]) {
            case 'byPartNumber':
                $rows = tldPDC::byPartNumber($pn);
                $rows = tldPDC::getTOCLinksCount($rows);

                $_title = "PDC listing by PN $pn";
                break;
            case 'byFactoryOpenStatusWithNoOpenTasks':
                $factory = TldDatabase::escape($y);
                $status = TldDatabase::escape($x);
                $assignee = TldDatabase::escape($assignee);
                $rows = tldPDC::byFactoryOpenStatusWithNoOpenTasks($factory, $status, $assignee);
                $rows = tldPDC::getTOCLinksCount($rows);

                $_title = "Open PDC listing factory '$factory', status '$status' with no Open tasks";
                break;
            case 'byFollowing':
                $xItems = array_merge(
                    $xItems,
                    [
                        'data_closed' => 'Date Closed',
                    ]
                );
                $factory = TldDatabase::escape($y);
                $status = TldDatabase::escape($x);
                $rows = tldPDC::byFollowers($factory, $status, $user->getID());
                $rows = tldPDC::getTOCLinksCount($rows);

                if ($z === 'opened') {
                    $rows = array_filter($rows, static function ($pdc) {
                        return !in_array($pdc['status'], ['REJECTED', 'CLOSED']);
                    });
                }
                $_title = "PDC listing you followed by factory '$factory', status '$status'";
                break;
            case 'byFactoryStatus':
                $xItems = array_merge(
                    $xItems,
                    [
                        'data_closed' => 'Date Closed',
                    ]
                );
                $factory = TldDatabase::escape($y);
                $status = TldDatabase::escape($x);
                $assignee = TldDatabase::escape($assignee);
                $rows = tldPDC::byFactoryStatusWithoutComments($factory, $status, $assignee);
                $rows = tldPDC::getTOCLinksCount($rows);

                if ($z === 'opened') {
                    $rows = array_filter($rows, static function ($pdc) {
                        return !in_array($pdc['status'], ['REJECTED', 'CLOSED']);
                    });
                }
                $_title = "PDC listing factory '$factory', status '$status'";
                break;
            case 'byFactoryStatusReadyToClose':
                $xItems = array_merge(
                    $xItems,
                    [
                        'data_closed' => 'Date Closed',
                    ]
                );
                $factory = TldDatabase::escape($y);
                $status = TldDatabase::escape($x);
                $assignee = TldDatabase::escape($assignee);
                $rows = tldPDC::byFactoryStatusReadyToClose($factory, $status, $assignee);
                $rows = tldPDC::getTOCLinksCount($rows);

                if ($z === 'opened') {
                    $rows = array_filter($rows, static function ($pdc) {
                        return !in_array($pdc['status'], ['REJECTED', 'CLOSED']);
                    });
                }
                $_title = "PDC listing factory '$factory', status '$status' with ready to close flag";
                break;
            case 'byOpenTaskAssigneeId':
                // List
                $userList = tldUtils::optionsByKeyValue(tldPDC::getOpenTaskAssigneeList(), 'id', 'fullname');
                // Form
                $form = new HTML_QuickForm('frm', 'post');
                $form->addElement('hidden', 'm[0]', 'pdc');
                $form->addElement('hidden', 'm[1]', 'listing');
                $form->addElement('hidden', 'm[2]', 'byOpenTaskAssigneeId');
                $form->addElement('header', 'title', 'Select user');
                $form->addElement('select', 'uid', 'User', ['' => ''] + $userList);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->addRule('uid', 'This is required', 'required');

                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break 2;
                }
                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $rows = tldPDC::byOpenTaskAssigneeID($vars['uid']);
                $rows = tldPDC::getTOCLinksCount($rows);
                break;
            case 'search':
                // Listing
                $userList = tldDirectory::getUserlist('smartyOptions');
                $categoryList = ['ALL_TYPES' => 'ALL_TYPE'] + tldType::getTypes('en', 'smartyOptions');
                $modelList = tldModel::getList();
                $factoryList = tldLocation::getFactoryList('smartyOptionsIDLocation');
                $statusList = tldPDC::getStatusList();
                $iFactorList = tldPDC::getIFactorList();
                // Form
                $form = new HTML_QuickForm('frmNewDemerit', 'post');
                $form->addElement('hidden', 'm[0]', 'pdc');
                $form->addElement('hidden', 'm[1]', 'listing');
                $form->addElement('hidden', 'm[2]', 'search');
                $form->addElement('header', 'title', 'Search');
                $form->addElement('text', 'target', 'Look for', ['size' => '20']);
                $form->addElement('header', 'title', 'Filters');
                $form->addElement('select', 'initiator', 'Initiator', ['' => ''] + $userList);
                $form->addElement('select', 'factory', 'Location', ['' => ''] + $factoryList);
                $form->addElement('select', 'status', 'Status', ['' => ''] + $statusList);
                $form->addElement('select', 'ifactor', 'Importance Factor', ['' => ''] + $iFactorList);
                $form->addElement('select', 'product_type', 'Product Type', ['' => ''] + $categoryList);
                $form->addElement('select', 'model', 'Product Model', ['' => ''] + $modelList);
                $form->addElement('checkbox', 'is_ibs', 'Involves iBS');
                $form->addElement('checkbox', 'is_ihs', 'Involves iHS/ipHS');
                $form->addElement('checkbox', 'is_link', 'Involves LINK');
                $form->addElement('text', 'pn', 'By part#');
                $form->addElement('submit', 'btnSubmit', 'Submit');

                if(isset($_GET['model'])){
                    $form->_submitValues['model'] = $_GET['model'];
                    $form->_flagSubmitted = true;
                }

                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break 2;
                }

                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $constraintsWhere = null;
                $constraints = [' 1=1 '];
                if (!empty($vars['target'])) {
                    $keyword = $vars['target'];
                    $constraintsWhere = " short_desc LIKE '%$keyword%' OR description LIKE '%$keyword%' OR log.comment LIKE '%$keyword%'";
                }
                $searchFieldList = ['initiator', 'factory', 'status', 'ifactor', 'product_type', 'product_type', 'model', 'pn', 'is_ibs', 'is_ihs', 'is_link'];
                // loop fields
                foreach ($searchFieldList as $field) {
                    if (empty($vars[$field])) {
                        continue;
                    }
                    switch ($field) {
                        case 'pn':
                            $constraints[] = " '{$vars[$field]}' IN (SELECT pn FROM mod_parts WHERE module LIKE 'PDC' AND parent_id=pdc.id) ";
                            break;
                        default:
                            $constraints[] = " $field LIKE '{$vars[$field]}' ";
                            break;
                    }
                }
                if (empty($constraintsWhere) && count($constraints) < 2) {
                    $DEFAULT_ERROR[] = 'ERROR: Not enough constraints selected';
                    $body .= $form->toHTML();
                    break 2;
                }
                $rows = tldPDC::byConstraints(implode(' AND ', $constraints), ['where' => $constraintsWhere]);
                if (empty($rows)) {
                    $DEFAULT_ERROR[] = 'No PDC was found with the constraints selected';
                    $body .= $form->toHTML();
                    break 2;
                }
                $rows = tldPDC::getTOCLinksCount($rows);
                $_title = 'Search result';
                $out = 'columnar';
                break;
        }

        if ($rows) {
            $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=pdc&m[1]=listing&out=xls">XLS version</a>
EOF;
            $sess['pdc']['list'] = $rows;
            $sess['pdc']['xItems'] = $xItems;
        }

        switch ($out) {
            case 'xls':
                $report = new tldXLS(
                    $sess['pdc']['list'],
                    [
                        'xItems' => $sess['pdc']['xItems'],
                        'showTitles' => true,
                    ]
                );
                $report->out();
                exit;
                break;
            case 'columnar':
                $report = new tldReportColumnar(
                    $rows,
                    [
                        'xItems' => $xItems,
                        'links' => [
                            'id' => "$php_self?m[0]=pdc&m[1]=view&id=",
                        ],
                        'title' => $_title,
                    ]
                );
                $body .= $report->fetch();
                break;
            default:
                $body .= _getListing($rows, $_title);
                break;
        }
        break;
    case 'kpi':
        global $kernel;
        $container = $kernel->getContainer();
        $chartBuilderFactory = $container->get(ChartBuilderFactory::class);

        $factoryId = TldDatabase::escape($location);
        $results = tldPDC::averagePdcDaysInPending($factoryId);

        $chart = $chartBuilderFactory
            ->getLineChartBuilder()
            ->setTitle('Average number of days of PDC in pending status')
        ;
        foreach ($results as $key => $row) {
            $chart->addPlot(
                $row['zval'],
                $row['xval'],
                (int)$row['yval']
            );
        }
        $chart = json_encode($chart->buildConfig());

        $body .= <<<EOF
<br/><br/>
<div id="container-1" style="width:100%; height:400px;"></div>
<script>
	$(document).ready(function() {
	  $('#container-1').highcharts($chart);
});
</script>
EOF;

        $results = tldPDC::quantityPdcCreatedByMonth($factoryId);

        $actualMonth = null;
        $actualYear = null;
        foreach ($results as $key => $row) {
            if (null === $actualMonth) {
                $actualMonth = (int) (new \DateTime($row['xval']))->format('m');
                continue;
            }

            $actualYear = $actualMonth === 12 ? (int) (new \DateTime($row['xval']))->format('Y') + 1 : (int) (new \DateTime($row['xval']))->format('Y');
            $actualMonth = $actualMonth === 12 ? 1 : $actualMonth + 1;

            if ($actualMonth === (int) (new \DateTime($row['xval']))->format('m')) {
                $actualMonth = (int) (new \DateTime($row['xval']))->format('m');
                continue;
            }

            $results[] = [
                'xval' => (new \DateTime(sprintf('%d-%d', $actualYear, $actualMonth)))->format('Y-m'),
                'yval' => 0,
                'zval' => (new tldLocation($location))->getBuName(),
            ];

            $actualMonth = (int) (new \DateTime($row['xval']))->format('m');
        }

        $chart = $chartBuilderFactory
            ->getLineChartBuilder()
            ->setTitle('Quanty of PDC created / month')
        ;
        foreach ($results as $key => $row) {
            $chart->addPlot(
                $row['zval'],
                $row['xval'],
                (int)$row['yval']
            );
        }
        $chart = json_encode($chart->buildConfig());

        $body .= <<<EOF
<br/><br/>
<div id="container-2" style="width:100%; height:400px;"></div>
<script>
	$(document).ready(function() {
	  $('#container-2').highcharts($chart);
});
</script>
EOF;

        $results = tldPDC::averagePdcDaysInPending($factoryId);
    break;
    default:
        $body = <<<EOF
<p>Welcome to the PDC module</p>
EOF;

        $statusesList = tldPDC::getStatusList();
        if ($user->isInGroup(['gg_ENG', 'role_ENG', 'gg_ADMIN'])) {
            $assigneeID = $user->getID();
            $assigneeFullname = $user->getFullname();
            $constraints = 'demerit.assignee =' . $assigneeID;
            $pdcs = tldPDC::countByFactoryStatus($constraints);
            $pdcAssigneeReport = new tldMatrix(
                array_filter($pdcs, static function ($pdc) use ($assigneeID) {
                    return !in_array($pdc['status'], ['REJECTED', 'CLOSED']);
                }),
                'status', 'factory_fullname', 'num',
                "$php_self?m[0]=pdc&m[1]=listing&m[2]=byFactoryStatus&z=opened&assignee=$assigneeID",
                'PDC Count by Factory, Status not REJECTED nor CLOSED assigned to ' . $assigneeFullname,
                [
                    'xItems' => array_filter($statusesList, static function ($status) {
                        return !in_array($status, ['REJECTED', 'CLOSED']);
                    }),
                ]
            );
            $body .= $pdcAssigneeReport->fetch();
        }

        // List PDC with open action for the user
        $rows = tldPDC::byOpenTaskAssigneeID($user->getID());
        $body .= _getListing($rows, 'PDC with OPEN actions for you', ['showTotal' => 'no',]);
        // List OPEN PDC initiated by the user
        $rows = tldPDC::byOpenByInitiatorID($user->getID());
        $body .= _getListing($rows, 'Open PDC initiated by you', ['showTotal' => 'no']);

        $pdcsFiltered = tldPDC::countByFactoryStatus("status NOT IN('REJECTED','CLOSED')");
        // Matrix by factory status
        $form = new tldMatrix(
            $pdcsFiltered,
            'status', 'factory_fullname', 'num',
            "$php_self?m[0]=pdc&m[1]=listing&m[2]=byFactoryStatus&z=opened",
            'PDC Count by Factory, Status not REJECTED nor CLOSED',
            [
                'xItems' => array_diff($statusesList, ['REJECTED', 'CLOSED']),
            ]
        );
        $body .= $form->fetch();

        $pdcsAllReadyToClose = tldPDC::countByFactoryStatus('is_ready_to_close=1 AND status NOT IN ("REJECTED", "CLOSED")');
        // Matrix by factory status
        $form = new tldMatrix(
            $pdcsAllReadyToClose,
            'status', 'factory_fullname', 'num',
            "$php_self?m[0]=pdc&m[1]=listing&m[2]=byFactoryStatusReadyToClose",
            'PDC Count by Factory, Status with ready to close flag',
            [
                'xItems' => tldPDC::getStatusListForFlag(),
            ]
        );
        $body .= $form->fetch();

        // Matrix by factory status
        $form = new tldMatrix(
            tldPDC::countByFactoryOpenStatusWithNoOpenTasks(),
            'status', 'factory_fullname', 'num',
            "$php_self?m[0]=pdc&m[1]=listing&m[2]=byFactoryOpenStatusWithNoOpenTasks",
            'Open PDC by Factory, Status, with no open tasks',
            [
                'xItems' => tldPDC::getOpenStatusList(),
            ]
        );
        $body .= $form->fetch();

        $pdcsFollowed = tldPDC::countByFactoryStatusFollowed($user->getID());
        // Matrix by factory status
        $form = new tldMatrix(
            $pdcsFollowed,
            'status', 'factory_fullname', 'num',
            "$php_self?m[0]=pdc&m[1]=listing&m[2]=byFollowing",
            'PDC Count you follow by Factory, Status',
            [
                'xItems' => $statusesList,
            ]
        );

        $pdcsFiltered = tldPDC::countByFactoryStatus("status NOT IN('REJECTED','CLOSED')");
        $body .= $form->fetch();

        $pdcsAll = tldPDC::countByFactoryStatus();
        // Matrix by factory status
        $form = new tldMatrix(
            $pdcsAll,
            'status', 'factory_fullname', 'num',
            "$php_self?m[0]=pdc&m[1]=listing&m[2]=byFactoryStatus",
            'PDC Count by Factory, Status',
            [
                'xItems' => $statusesList,
            ]
        );
        $body .= $form->fetch();

        // Latest
        $report = new tldReportColumnar(
            tldPDC::byLatest(),
            [
                'xItems' => [
                    'id' => 'PDC #',
                    'status' => 'Status',
                    'date' => 'Date',
                    'factory_fullname' => 'Factory',
                    'product_type' => 'Type',
                    'model' => 'Model',
                    'short_desc' => 'Short Description',
                ],
                'links' => [
                    'id' => "$php_self?m[0]=pdc&m[1]=view&id=",
                ],
                'title' => 'Latest PDC',
            ]
        );
        $body .= $report->fetch();
        break;
}


function _getListing($rows, $title = null, $opt = null)
{
    global $smarty, $php_self, $PATH;
    $smarty->assign('list', $rows);
    $smarty->assign('title', $title);
    $link = empty($opt['link']) ? "$php_self?m[0]=pdc&m[1]=view&id=" : $opt['link'];
    $smarty->assign('next_step', $link);
    $showTotal = empty($opt['showTotal']) ? 'Y' : 'N';
    $smarty->assign('showTotal', $showTotal);
    return $smarty->fetch("$PATH/view/pdc.list.tpl");
}
