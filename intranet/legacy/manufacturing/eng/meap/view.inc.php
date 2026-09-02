<?php

use ApiBundle\Client;
use Symfony\Component\HttpClient\Exception\ClientException;

if (empty($id)) {
    $DEFAULT_ERROR[] = 'ERROR: no line id set...';
    return;
}
$meap = new tldMEAP($id);
if ($meap->isEmpty()) {
    $DEFAULT_ERROR[] = "ERROR: No MEAP#$id found...";
    return;
}
//check if confidential and a member
if ($meap->isPrivate() && $meap->isMember($user->getID()) !== true && !$user->isInGroup("ROLE_GCTO")) {
    $DEFAULT_ERROR[] = 'ERROR: Only members can access this confidential MEAP';
    return;
}
$header = $meap->itsHeader;
$smarty->assign('meap', $header);
$DEFAULT_TITLE .= "\MEAP#$id";
$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
<a href="$php_self?m[0]=meap&m[1]=view&id=$id">General</a>
&nbsp;|&nbsp; <a href="$php_self?m[0]=meap&m[1]=view&m[2]=dashboard&id=$id">Dashboard</a>
&nbsp;|&nbsp; <a href="$php_self?m[0]=meap&m[1]=view&m[2]=hierarchy&id=$id">Hierarchy</a>
&nbsp;|&nbsp; <a href="$php_self?m[0]=meap&m[1]=view&m[2]=tasks&id=$id" title="Related tasks">Tasks</a>
&nbsp;|&nbsp; <a href="$php_self?m[0]=meap&m[1]=view&m[2]=members&id=$id">Members</a>
&nbsp;|&nbsp; <a href="$php_self?m[0]=meap&m[1]=view&m[2]=bps&id=$id">Processes</a>
&nbsp;|&nbsp; <a href="$php_self?m[0]=meap&m[1]=view&m[2]=links&id=$id" title="Related links">Links</a>
&nbsp;|&nbsp; <a href="$php_self?m[0]=meap&m[1]=view&m[2]=modOverview&id=$id" title="Overview of tasks in related links">Module Tasks Overview</a>
&nbsp;|&nbsp; <a href="$php_self?m[0]=meap&m[1]=view&m[2]=files&id=$id" title="Files linked to this MEAP">Files</a>
&nbsp;|&nbsp; <a href="$php_self?m[0]=meap&m[1]=view&m[2]=log&id=$id" title="Activity Log">Log</a>
&nbsp;|&nbsp; <a href="$php_self?m[0]=meap&m[1]=view&m[2]=status&id=$id">Change Status</a>
&nbsp;|&nbsp; <a href="$php_self?m[0]=meap&m[1]=view&m[2]=privacy&id=$id">Privacy</a>
&nbsp;|&nbsp; <a href="$php_self?m[0]=meap&m[1]=view&m[2]=econ&id=$id" title="Edit Economics">Economics</a>
&nbsp;|&nbsp; <a href="$php_self?m[0]=meap&m[1]=view&m[2]=project&id=$id" title="Project Goal Changes &amp; Risk Assessments">Goals &amp; Risks</a>
&nbsp;|&nbsp; <a href="$php_self?m[0]=meap&m[1]=view&m[2]=provisional&id=$id" title="Edit Provisional Phase Closure Dates">Provisional Phase Closure</a>
&nbsp;|&nbsp; <a href="$php_self?m[0]=meap&m[1]=view&m[2]=reports&m[3]=byActivityDate&id=$id" title="Linked Module Activity Report">Linked Activity</a>
EOF;
if ($user->isInGroup(['role_EM', 'role_ES'])) {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp; <a href="$php_self?m[0]=meap&m[1]=view&m[2]=batchTasksReschedule&id=$id" title="Reschedule Tasks">Reschedule</a> 
EOF;
}
if ($user->isInGroup(['eap', 'gg_mod_eap_admin', 'gg_ENG', 'gg_ADMIN'])) {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="meap/meap_admin.php?mode=record_view&form_type=main_tpl&id=$id" title="Edit this MEAP">Edit</a>
EOF;
}
$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
EOF;

$erp = $meap->getERP();
$status = ['PROPOSAL', 'GATE_PROPOSAL', 'GATE_0', 'PHASE_0'];

// Get nav list links
$body = $smarty->fetch("$PATH/meap/meap.nav.tpl");
switch ($m[2]) {
    case 'hierarchy':
        $DEFAULT_TITLE .= "\Hierarchy";
        $DEFAULT_MENU .= <<<EOF
<a href="$php_self?m[0]=meap&m[1]=view&m[2]=hierarchy&id=$id">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=meap&m[1]=view&m[2]=hierarchy&m[3]=add&id=$id">Add Child</a>
EOF;
        $body .= '<br>';
        switch ($m[3]) {
            case 'add':
                if (!$user->isInGroup(['gg_ENG', 'role_ENG', 'role_EM', 'role_ES', 'gg_ADMIN', 'role_EVP'])) {
                    $DEFAULT_ERROR[] = 'ERROR: You do not have permission for this action';
                    break;
                }
                $allowedChildModule = ['MEAP' => 'MEAP', 'EAP' => 'EAP'];
                $form = new HTML_QuickForm('frmAddHierarchy', 'get');
                $form->addElement('hidden', 'm[0]', 'meap');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('hidden', 'm[2]', 'hierarchy');
                $form->addElement('hidden', 'm[3]', 'add');
                $form->addElement('hidden', 'id', $id);
                $form->addElement('header', 'header', 'Add new child');
                $form->addElement('select', 'module', 'Module Type', $allowedChildModule);
                $form->addElement('text', 'cid', 'Module ID#');
                $form->addElement('submit', 'btnSubmit', 'Add');
                $form->addRule('module', 'Required', 'required');
                $form->addRule('cid', 'Required', 'required');

                if (!$form->validate()) {
                    $body .= $form->toHTML();
                    break;
                }

                $vars = tldUtils::cleanupFormInput($form->exportValues());
                // Check if child of itself
                if ($vars['module'] === 'MEAP' && $vars['cid'] == $id) {
                    $DEFAULT_ERROR[] = 'ERROR: Can not set a module as the child of itself';
                    break;
                }
                // Check if futur child have already a parent
                $childModule = ($vars['module'] === 'MEAP') ? new tldMEAP($vars['cid']) : new tldEAP($vars['cid']);
                $childModuleParentID = (int) $childModule->getParentID();
                if ($childModuleParentID !== 0) {
                    $DEFAULT_ERROR[] = "ERROR: {$childModule->getModule()}#{$childModule->getID()} already has a parent module ({$childModule->getParentModule()}#{$childModuleParentID})";
                    break;
                }
                $e = $meap->addChild($vars['cid'], $vars['module']);
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "ERROR: Child not added, reason: $e";
                    break;
                }
                $body .= 'Module added as child successfully!';
                break;
            case 'delete':
                if (empty($_GET['cid']) || !is_numeric($_GET['cid'])) {
                    $DEFAULT_ERROR[] = 'ERROR: Module info is missing or invalid...';
                    break;
                }
                if (!$user->isInGroup(['gg_ENG', 'role_ENG', 'role_EM', 'role_ES', 'gg_ADMIN'])) {
                    $DEFAULT_ERROR[] = 'ERROR: You do not have permission for this action';
                    break;
                }
                $cid = (int)$_GET['cid'];
                $mod = TldDatabase::escape($_GET['mod']);
                $modChild = ($mod === 'MEAP') ? new tldMEAP($cid) : new tldEAP($cid);
                if ($modChild->isEmpty()) {
                    $DEFAULT_ERROR[] = 'ERROR: Module not found...';
                    break;
                }
                // Check if it is really its child
                if ($modChild->getParentID() !== $meap->getID() && $modChild->getParentModule() !== $meap->getModule()) {
                    $DEFAULT_ERROR[] = 'ERROR: Module not a child';
                    break;
                }
                $e = $meap->deleteChild($cid, $mod);
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "ERROR: Child not deleted, reason: $e";
                    break;
                }
                $body .= 'Module is no longer a child';
                break;
            default:
                $body .= <<<HTML
<h3>Family tree of MEAP#{$id}
<button style="margin-left: 30px;" onclick="document.querySelectorAll('.tree ul').forEach(function(element) { element.classList.toggle('global-active')  });">Fold/Unfold</button>
<button style="margin-left: 10px;" onclick="this.textContent = 'Hide Closed' === this.textContent  ? 'Show Closed' : 'Hide Closed'; document.querySelectorAll('.tree ul').forEach(function(element) { element.classList.toggle('hide-closed')  });">Hide Closed</button>
</h3>
HTML;

                $body .= _drawHierarchyTree();
                break;
        }
        // Get Parent hierarchy
        $parent_id = $meap->getParentID();
        if (!empty($parent_id)) {
            $parent = ($meap->getParentModule() === 'MEAP') ? new tldMEAP($parent_id) : new tldEAP($parent_id);
            $parent_header[0] = $parent->itsHeader;
            $report = new tldReportColumnar(
                _createHierarchyLinks($parent_header),
                [
                    'xItems' => [
                        'id_html' => 'ID#',
                        'module' => 'Module',
                        'short_desc' => 'Description',
                    ],
                    'title' => "Parent Module of MEAP#{$id}",
                ]
            );
            $body .= $report->fetch();
        } else {
            $body .= "<h3>Parent Module of MEAP#{$id}</h3>";
            $body .= '<p>No records...</p>';
        }
        // Get child hierarchy
        $childList = $meap->getChildList();
        foreach ($childList as $key => $val) {
            $childList[$key] = array_merge($val, ['unlink' => 'unlink']);
        }
        $report = new tldReportColumnar(
            _createHierarchyLinks($childList),
            [
                'xItems' => [
                    'id_html' => 'ID#',
                    'module' => 'Module',
                    'short_desc' => 'Description',
                    'unlink' => 'Remove Child',
                ],
                'title' => "Child Modules of MEAP#{$id}",
                'links' => [
                    'unlink' => [
                        'url' => "$php_self?m[0]=meap&m[1]=view&m[2]=hierarchy&m[3]=delete&id=$id",
                        'params' => ['cid' => 'id', 'mod' => 'module'],
                        'confirmPopup' => 'Are you sure to remove child?',
                    ],
                ],
            ]
        );
        $body .= $report->fetch();
        break;
    case 'members':
        $DEFAULT_MENU .= <<<EOF
&nbsp;&nbsp;
<a href="$php_self?m[0]=meap&m[1]=view&m[2]=members&id=$id">Members</a>
&nbsp;|&nbsp; <a href="/en/private/common/index.php?m[0]=members&m[1]=new&module=MEAP&parent_id=$id">Add Members</a>
&nbsp;|&nbsp; <a href="/en/private/common/index.php?m[0]=members&m[1]=delete&module=MEAP&parent_id=$id">Delete Members</a>
EOF;
        if ($meap->isPrivate()) {
            $DEFAULT_ERROR[] = 'This MEAP is set to confidential, please be careful when removing members or this module will be locked out to everyone';
        }
        $DEFAULT_TITLE .= "\Members";
        $form = new tldReportColumnar(
            $meap->getMembers(),
            [
                'xItems' => [
                    'id' => 'UID#',
                    'lastname' => 'Lastname',
                    'firstname' => 'Firstname',
                ],
                'title' => 'Members',
                'links' => [
                    'id' => '/en/private/directory/index.php?m[0]=people&m[1]=view&id=',
                ],
            ]
        );
        $body .= $form->fetch();
        break;
    case 'privacy':
        $DEFAULT_TITLE .= "\Privacy";
        if ($user->isInGroupLevel('role_EM', $meap->getERP())) {
            $can_edit = true;
        } else {
            $can_edit = false;
        }
        switch ($m[3]) {
            case 'toggle':
                if ($can_edit) {
                    $pvc = $meap->isPrivate();
                    if (!$pvc && !count($meap->getMembers())) {
                        $DEFAULT_ERROR[] = 'ERROR: You must first assign members before you can set to confidential';
                        break 2;
                    }
                    $meap->setPrivate(!$pvc);
                    $meap->addLog($user->getID(), 'MEAP Set to ' . ($pvc ? 'Not Confidential' : 'Confidential'));
                }
                break;
        }
        if ($meap->isPrivate()) {
            $body .= <<<EOF
<h1>Is Confidential? &nbsp; <span style="color:red;">YES</span></h1>
EOF;
            $edit_txt = 'Non Confidential';
        } else {
            $body .= <<<EOF
<h1>Is Confidential? &nbsp; <span style="color:green;">No</span></h1>
EOF;
            $edit_txt = 'Confidential';
            if ($can_edit && !$meap->isMember($user->getID())) {
                $DEFAULT_ERROR[] = 'WARNING: If you set to confidential, you will no longer have access to this module';
            }
        }
        if ($can_edit) {
            $body .= <<<EOF
<button onclick="location='$php_self?m[0]=meap&m[1]=view&m[2]=privacy&m[3]=toggle&id=$id'">Set to $edit_txt</button>
EOF;
        }
        break;
    case 'getfile':
        if (empty($id) || empty($fileid)) {
            $DEFAULT_ERROR[] = 'File id not set';
            break;
        }

        $file = $meap->getFiles($fileid);
        $file = new basicFile(tldUtils::getPathToUploadFile('meap', $file['filename']));

        if ($meap->isPrivate()) {
            $file->rename(sprintf('%s-confidential', $file->getFileName()));
        }

        $file->outFile();
        $template = 'NO_TEMPLATE';
        break;
    case 'reports':
        if (isset($m[3]) && $m[3] === 'byActivityDate') {
            include_once('meap/report.activity.inc.php');
        }
        break;
    case 'log':
        $DEFAULT_TITLE .= "\Log";
        $DEFAULT_MENU .= <<<EOF
<a href="$php_self?m[0]=meap&m[1]=view&m[2]=log&m[3]=add&id=$id">New Comment</a>
EOF;
        if (isset($m[3]) && 'add' === $m[3]) {
            $form = new HTML_QuickForm('frmAddComment', 'post');
            $form->addElement('header', 'title', 'Add comment');
            $form->addElement('hidden', 'm[0]', 'meap');
            $form->addElement('hidden', 'm[1]', 'view');
            $form->addElement('hidden', 'm[2]', 'log');
            $form->addElement('hidden', 'm[3]', 'add');
            $form->addElement('hidden', 'id', $id);
            $form->addElement('textarea', 'comment', 'Comment', ['wrap' => 'VIRTUAL', 'cols' => '50', 'rows' => '4']);
            $form->addElement('file', 'file', 'Upload File');
            $form->addElement('textarea', 'fileDescription', 'File Description', ['wrap' => 'VIRTUAL', 'cols' => '50']);
            $form->addElement('submit', 'btnSubmit', 'Submit');

            if (!$form->validate()) {
                $body .= $form->toHTML();
                break;
            }

            $a = $form->exportValues();
            $vars = tldUtils::cleanupFormInput($a);

            $file = $form->getElement('file');
            if ($file->getValue()['size'] !== 0) {
                $filePayload = [
                    'owner' => $user->getID(),
                    'module' => 'MEAP',
                    'parent_id' => $id,
                    'filename' => $fileName = ($vars['fileDescription'] !== '' ? $vars['fileDescription'] : $file->getValue()['name']),
                ];

                // Insert mod File
                $fileId = tldModFile::insert($filePayload, $file->getValue());
                if (is_string($fileId)) {
                    $DEFAULT_ERROR[] = "ERROR: There was a problem attaching the file. Reason: $fileId";
                    break;
                }
                $vars['comment'] .= sprintf('<p><a href="%s/en/private/common/index.php?m[0]=files&m[1]=view&m[2]=out&id=%s">Click here to download %s</a></p>',$_SERVER['HTTP_ORIGIN'], $fileId, $fileName);
            }

            $e = $meap->addComment(['poster' => $user->getID(), 'comment' => $vars['comment']]);
            if (!is_string($e)) {
                $body .= 'Comment successfully added!';
            } else {
                $DEFAULT_ERROR[] = "INTERNAL ERROR: Comment not added!<br/>Reason: $e";
            }
        } else {
            $log = $meap->getLog();
            $report = new tldReportColumnar($log,
                ['xItems' => ['id' => 'ID#',
                    'date' => 'Date',
                    'poster_fullname' => 'Poster',
                    'comment' => 'Comment'],
                ]
            );
            $body .= $report->fetch();
        }
        break;
    case 'files':
        $DEFAULT_TITLE .= "\Files";
        $DEFAULT_MENU .= <<<EOF
	<a href="$php_self?m[0]=meap&m[1]=view&m[2]=files&m[3]=confirm&id=$id">Add New File</a>
&nbsp;|&nbsp; <a href="$php_self?m[0]=meap&m[1]=view&m[2]=files&m[3]=confirm&id=$id&level=1">Add DTC File</a>
&nbsp;|&nbsp; <a href="$php_self?m[0]=meap&m[1]=view&m[2]=files&m[3]=confirm&id=$id&level=2">Add Goal Sheet</a>
&nbsp;|&nbsp; <a href="$php_self?m[0]=meap&m[1]=view&m[2]=files&m[3]=confirm&id=$id&level=3">Add Planning File</a>
EOF;
        switch ($m[3]) {
            case 'confirm':
                $p = [
                    'm' => ['0' => 'files', '1' => 'form', '2' => 'newFile'],
                    'module' => 'MEAP',
                    'parent_id' => $id,
                ];
                if (isset($level)) {
                    $p['level'] = $level;
                }
                $uri = '/en/private/common/index.php?' . http_build_query($p);
                if (!$meap->isPrivate()) {
                    header("Location: {$uri}");
                    exit;
                }

                // Set confirmation form
                $form = new HTML_QuickForm('frmConfirm', 'get', null, null, null, true);
                $form->addElement('hidden', 'm[0]', 'meap');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('hidden', 'm[2]', 'files');
                $form->addElement('hidden', 'm[3]', 'confirm');
                $form->addElement('hidden', 'id', $id);
                if (isset($level)) {
                    $form->addElement('hidden', 'level', $level);
                }
                $form->addElement('header', 'title', 'Confirm Adding File To Confidential MEAP');
                $form->addElement('static', null, null, 'This module is set to confidential!');
                $form->addElement('static', null, null,
                    'Uploading files directly to this module online will<br/>
            not protect the files from being viewed by others<br/>
            in the TLD Group. Please be careful when uploading<br/>
            sensitive materiels. It is recommended to upload to a<br/>
            protected network folder instead.');
                $form->addElement('checkbox', 'cnf', 'I understand');
                $form->addRule('cnf', 'This is required', 'required');
                $form->addElement('submit', 'btnSubmit', 'Continue');
                // Validate submission
                if ($form->validate()) {
                    $meap->addLog($user->getID(), 'File upload to confidential MEAP confirmed');
                    header("Location: {$uri}");
                    exit;
                }
                $body .= $form->toHTML();
                break 2;
        }
        $xItems = [
            'id' => 'ID#',
            'date' => 'Date',
            'description' => 'Description',
            'filename' => 'Filename',
        ];
        $functions = [
            'Download' => [
                'url' => '/en/private/common/index.php?m[0]=files&m[1]=view&m[2]=out&id=',
                'param' => 'id',
            ],
        ];
        if ($user->isInGroup(['gg_ADMIN', 'gg_SUPERUSER', 'role_EM', 'role_ES'])) {
            $functions['Edit'] = [
                'url' => '/en/private/common/index.php?m[0]=files&m[1]=view&id=',
                'param' => 'id',
            ];
        }

        // Files: Level 0=Normal, Level 1=DTC, Level 2=Goal Sheets, Level 3=Planning
        $form = new tldReportColumnar(
            $meap->getFiles(),
            [
                'xItems' => $xItems,
                'title' => 'Normal File List',
                'functions' => $functions,
            ]
        );
        $body .= $form->fetch();
        $form = new tldReportColumnar(
            $meap->getDTCFiles(),
            [
                'xItems' => $xItems,
                'title' => 'DTC File List',
                'functions' => $functions,
            ]
        );
        $body .= $form->fetch();
        $form = new tldReportColumnar(
            $meap->getGoalSheetFiles(),
            [
                'xItems' => $xItems,
                'title' => 'Goal Sheets',
                'functions' => $functions,
            ]
        );
        $body .= $form->fetch();
        $form = new tldReportColumnar(
            $meap->getPlanningFiles(),
            [
                'xItems' => $xItems,
                'title' => 'Planning',
                'functions' => $functions,
            ]
        );
        $body .= $form->fetch();
        $eaps = array_filter($meap->getFamilyTree(true, true), static function (array $module) {
            return 'EAP' === $module['module'];
        });
        $eapFiles = [];
        if (!empty($eaps)) {
            $eapFiles = tldModFile::byConstraints(' mod_files.parent_id IN (' . implode(', ', array_column($eaps, 'id')) . ") AND mod_files.module='EAP' ");
        }

        $form = new tldReportColumnar(
            $eapFiles,
            [
                'xItems' => array_merge($xItems, ['parent_id' => 'EAP number']),
                'title' => 'EAP files',
                'functions' => [
                    'Download' => [
                        'url' => '/en/private/common/index.php?m[0]=files&m[1]=view&m[2]=out&id=',
                        'param' => 'id',
                    ],
                ],
            ]
        );
        $body .= $form->fetch();
        break;
    case 'links':
        $DEFAULT_MENU .= <<<EOF
	<a href="/en/private/common/index.php?m[0]=links&m[1]=form&m[2]=newLink&module=MEAP&parent_id=$id">Add New Link</a>
&nbsp;|&nbsp; <a href="/en/private/common/index.php?m[0]=links&m[1]=graphviz&mod=MEAP&pid=$id">Map</a>
EOF;
        $linksFromHere = tldModLink::byParent($id, 'MEAP');
        global $kernel;
        $client = $kernel->getContainer()->get(Client::class);

        foreach ($linksFromHere as &$link) {
            if ('MOM' === $link['type']) {
                try {
                    $mom = $client->find('minutes_of_meeting/meetings', (int) $link['item']);
                    $link['dsca'] = $mom['title'];
                } catch (ClientException $e) {
                    //do nothing
                }
            }
        }
        $report = new tldReportColumnar(
            $linksFromHere,
            [
                'xItems' => [
                    'id' => 'ID#',
                    'type' => 'Module',
                    'item' => 'Ref#',
                    'dsca' => 'Description',
                ],
                'title' => 'Links',
                'links' => ['id' => "/en/private/common/index.php?m[0]=links&m[1]=view&erp=$erp&id="],
            ]
        );
        $body .= $report->fetch();

        $linksToHere = tldModLink::byItem($id, 'MEAP');
        foreach ($linksToHere as &$link) {
            if ('MOM' === $link['module']) {
                try {
                    $mom = $client->find('minutes_of_meeting/meetings', (int) $link['parent_id']);
                    $link['dsca'] = $mom['title'];
                } catch (ClientException $e) {
                    //do nothing
                }
            }
        }
        $report = new tldReportColumnar(
            $linksToHere,
            [
                'xItems' => [
                    'id' => 'ID#',
                    'module' => 'Module',
                    'parent_id' => 'Ref#',
                    'dsca' => 'Description',
                ],
                'title' => 'Links TO Here...',
                'links' => ['id' => "/en/private/common/index.php?m[0]=links&m[1]=view&reversed=1&erp=$erp&id="],
            ]
        );
        $body .= $report->fetch();
        break;
    case 'bps':
        $DEFAULT_TITLE .= "\Processes";
        $body .= _viewBPSTab($meap);
        break;
    case 'tasks':
        $DEFAULT_TITLE .= "\Tasks";
        if (strtoupper($meap->getStatus()) !== 'CLOSED') {
            $DEFAULT_MENU .= <<<EOF
<a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=form&m[2]=newTask&module=MEAP&parent_id=$id">New Task</a>
EOF;
        }
        $sess['calendar']['tasks'] = $meap->getTasks('ALL');
        $form = new tldReportMultiLevel($sess['calendar']['tasks'],
            ['status', 'due_date'],
            ['id' => 'Task#',
                'status' => 'Status',
                'due_date' => 'Due',
                'task' => 'Task',
                'assignee_fullname' => 'Assignee',
            ],
            ['passField' => 'id',
                'title' => 'Tasks',
                'url' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=']
        );
        $body .= $form->fetch();
        break;
    case 'status':
        include_once('meap/view.status.inc.php');
        break;
    case 'provisional':
        $DEFAULT_MENU .= <<<EOF
	<a href="$php_self?m[0]=meap&m[1]=view&m[2]=provisional&id=$id">Preview</a>
&nbsp;|&nbsp; <a href="$php_self?m[0]=meap&m[1]=view&m[2]=provisional&m[3]=add&id=$id">Add New Milestone</a>
&nbsp;|&nbsp; <a href="$php_self?m[0]=meap&m[1]=view&m[2]=provisional&m[3]=edit&id=$id">Edit Dates</a>
EOF;
        switch ($m[3]) {
            case 'edit':
                // Edit provision dates
                foreach ($meap->getPCD() AS $value) {
                    $pcd[$value['id']] = $value;
                }
                $update = [];
                $msg = '';
                $currentStatus = $meap->getStatus();
                // Original
                foreach ((array)$original AS $pcdid => $dt) {
                    if (
                        (empty($pcd[$pcdid]['original_target']) && !$user->isInGroup(['role_EM', 'role_ES', 'gg_EXCOM'])) ||
                        (!empty($pcd[$pcdid]['original_target']) && !$user->isInGroup(['gg_EXCOM']) && !in_array($currentStatus, $status))
                    ) {
                        continue;
                    }
                    $old = (empty($pcd[$pcdid]['original_target'])) ? 0 : strtotime($pcd[$pcdid]['original_target']);
                    $new = (empty($dt)) ? 0 : strtotime($dt);
                    if ($old == $new) {
                        continue;
                    }
                    $update[$pcdid]['original'] = ($new) ? date('Y-m-d', $new) : '';
                    $msg .= "Original Target Date of {$pcd[$pcdid]['description']} changed from '{$pcd[$pcdid]['original_target']}' to '{$update[$pcdid]['original']}'\n";
                }
                // Management
                foreach ((array)$management AS $pcdid => $dt) {
                    if (!$user->isInGroup(['gg_EXCOM'])) {
                        continue;
                    }
                    $old = (empty($pcd[$pcdid]['management_target'])) ? 0 : strtotime($pcd[$pcdid]['management_target']);
                    $new = (empty($dt)) ? 0 : strtotime($dt);
                    if ($old == $new) {
                        continue;
                    }
                    $update[$pcdid]['management'] = ($new) ? date('Y-m-d', $new) : '';
                    $msg .= "Management Target Date of {$pcd[$pcdid]['description']} changed from '{$pcd[$pcdid]['management_target']}' to '{$update[$pcdid]['management']}'\n";
                }
                // Current
                foreach ((array)$current AS $pcdid => $dt) {
                    if (!$user->isInGroup(['role_EM', 'role_ES', 'role_ENG'])) {
                        continue;
                    }
                    $old = (empty($pcd[$pcdid]['current_target'])) ? 0 : strtotime($pcd[$pcdid]['current_target']);
                    $new = (empty($dt)) ? 0 : strtotime($dt);
                    if ($old == $new) {
                        continue;
                    }
                    $update[$pcdid]['current'] = ($new) ? date('Y-m-d', $new) : '';
                    $msg .= "Current Target Date of {$pcd[$pcdid]['description']} changed from '{$pcd[$pcdid]['current_target']}' to '{$update[$pcdid]['current']}'\n";
                }
                // Actual
                foreach ((array)$actual AS $pcdid => $dt) {
                    if ($pcd[$pcdid]['type'] !== 'MEAP PHASE' && !$user->isInGroup(['role_EM', 'role_ES'])) {
                        continue;
                    }
                    $old = (empty($pcd[$pcdid]['actual_date'])) ? 0 : strtotime($pcd[$pcdid]['actual_date']);
                    $new = (empty($dt)) ? 0 : strtotime($dt);
                    if ($old == $new) {
                        continue;
                    }
                    $update[$pcdid]['actual'] = ($new) ? date('Y-m-d', $new) : '';
                    $msg .= "Actual Closing Date of {$pcd[$pcdid]['description']} changed from '{$pcd[$pcdid]['actual_date']}' to '{$update[$pcdid]['actual']}'\n";
                }

                if (!empty($update)) {
                    if ($comment) {
                        $msg .= "Comment: {$comment}";
                    }
                    $meap->addLog($user->getID(), TldDatabase::escape($msg));
                    foreach ((array)$update AS $pcdid => $array) {
                        foreach ((array)$array AS $target => $date) {
                            $meap->setPCDDate($pcdid, $target, $date);
                        }
                    }
                    $DEFAULT_ERROR[] = 'Dates have been updated';
                    $body .= _getProvisional($meap, $status);
                    break;
                }
                $body .= _getProvisional($meap, $status, true);
                break;
            case 'add':
                $form = new HTML_QuickForm('frmAdd', 'post');
                $form->addElement('hidden', 'm[0]', 'meap');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('hidden', 'm[2]', 'provisional');
                $form->addElement('hidden', 'm[3]', 'add');
                $form->addElement('hidden', 'id', $id);
                $form->addElement('header', 'meap_section', 'Add a New Milestone');
                $form->addElement('text', 'description', 'Short Description');
                $form->addElement('date', 'target', 'Target Close Date',
                    [
                        'format' => 'Ymd',
                        'minYear' => date('Y'),
                        'maxYear' => date('Y') + 5,
                    ]
                );
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->addRule('target', 'This is required', 'required');
                $form->addRule('description', 'This is required', 'required');
                $form->addRule('target', 'This is required', 'required');
                $form->setDefaults(
                    [
                        'target' => [
                            'Y' => date('Y'),
                            'm' => date('m'),
                            'd' => date('d'),
                        ],
                    ]
                );
                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break;
                }
                $vals = tldUtils::cleanupFormInput($form->exportValues());
                $date = vsprintf('%1$04d-%2$02d-%3$02d', $vals['target']);
                $error = $meap->insertPCD('KEY EVENT', $vals['description']);
                if (is_string($error)) {
                    $DEFAULT_ERROR[] = "There was a problem. Returned error was '$error'";
                    break;
                }
                $meap->setPCDDate($error, 'original', $date);
                $meap->addComment(['poster' => $user->getID(),
                    'comment' => "Key event \\'{$vals['description']}\\' created and original target date set to \\'{$date}\\'"]);
                $DEFAULT_ERROR[] = 'Key Event Saved';
                break;
            default:
                $body .= _getProvisional($meap, $status);
                break;
        }
        break;
    case 'econ':
        $DEFAULT_MENU .= <<<EOF
	<a href="$php_self?m[0]=meap&m[1]=view&m[2]=econ&id=$id">Preview</a>
&nbsp;|&nbsp; <a href="$php_self?m[0]=meap&m[1]=view&m[2]=econ&m[3]=edit&id=$id">Update</a>
EOF;
        switch ($m[3]) {
            case 'edit':
                // Edit econ
                if (empty($_POST['submit']) || 'POST' !== strtoupper($_SERVER['REQUEST_METHOD'])) {
                    $body .= _getEconomics($meap, [], true);
                    break;
                }

                $msg = '';
                // Development Hours
                if (
                    (empty($header['econ_target_dh']) && $user->isInGroup(['role_EM', 'role_ES', 'gg_EXCOM']))
                    || (!empty($header['econ_target_dh']) && ($user->isInGroup(['gg_EXCOM']) || ($user->isInGroup(['role_EM', 'role_ES']) && !in_array($meap->getImportanceFactor(), [100, 1000, 10000]))))
                ) {
                    $old = $header['econ_target_dh'];
                    $new = trim($_POST['target_dh']);
                    if ($old !== $new && is_numeric($new)) {
                        $error = $meap->setEcon('target', 'dh', $new);
                        if (is_string($error)) {
                            $DEFAULT_ERROR[] = "The development hours target could not be updated: {$error}";
                        } else {
                            $msg .= "The Development Hours Target has changed from '{$old}' to '{$new}'\n";
                        }
                    }
                }
                if ($user->isInGroup(['role_EM', 'role_ES', 'role_ENG'])) {
                    $old = $header['econ_eac_dh'];
                    $new = trim($_POST['eac_dh']);
                    if ($old !== $new && is_numeric($new)) {
                        $error = $meap->setEcon('eac', 'dh', $new);
                        if (is_string($error)) {
                            $DEFAULT_ERROR[] = "The development hours EAC amount could not be updated: {$error}";
                        } else {
                            $msg .= "The Development Hours EAC Amount has changed from '{$old}' to '{$new}'\n";
                        }
                    }
                }

                // Subcontracted Engineering Amount
                if (
                    (empty($header['econ_target_sea']) && $user->isInGroup(['role_EM', 'role_ES', 'gg_EXCOM']))
                    || (!empty($header['econ_target_sea']) && ($user->isInGroup(['gg_EXCOM']) || ($user->isInGroup(['role_EM', 'role_ES']) && !in_array($meap->getImportanceFactor(), [100, 1000, 10000]))))
                ) {
                    $old = $header['econ_target_sea'];
                    $new = trim($_POST['target_sea']);
                    if ($old !== $new && is_numeric($new)) {
                        $error = $meap->setEcon('target', 'sea', $new);
                        if (is_string($error)) {
                            $DEFAULT_ERROR[] = "The subcontracted engineering target amount could not be updated: {$error}";
                        } else {
                            $msg .= "The Subcontracted Engineering Target Amount has changed from '{$old}' to '{$new}'\n";
                        }
                    }
                }
                if ($user->isInGroup(['role_EM', 'role_ES', 'role_ENG'])) {
                    $old = $header['econ_eac_sea'];
                    $new = trim($_POST['eac_sea']);
                    if ($old !== $new && is_numeric($new)) {
                        $error = $meap->setEcon('eac', 'sea', $new);
                        if (is_string($error)) {
                            $DEFAULT_ERROR[] = "The subcontracted engineering EAC amount could not be updated: {$error}";
                        } else {
                            $msg .= "The Subcontracted Engineering EAC Amount has changed from '{$old}' to '{$new}'\n";
                        }
                    }
                }
                if ($user->isInGroup(['role_EM', 'role_ES', 'role_COO', 'role_ENG'])) {
                    $old = $header['econ_actual_sea'];
                    $new = trim($_POST['actual_sea']);
                    if ($old !== $new && is_numeric($new)) {
                        $error = $meap->setEcon('actual', 'sea', $new);
                        if (is_string($error)) {
                            $DEFAULT_ERROR[] = "The subcontracted engineering actual amount could not be updated: {$error}";
                        } else {
                            $msg .= "The Subcontracted Engineering Actual Amount has changed from '{$old}' to '{$new}'\n";
                        }
                    }
                }

                // Material & Other Costs
                if (
                    (empty($header['econ_target_maoc']) && $user->isInGroup(['role_EM', 'role_ES', 'gg_EXCOM']))
                    || (!empty($header['econ_target_maoc']) && ($user->isInGroup(['gg_EXCOM']) || ($user->isInGroup(['role_EM', 'role_ES']) && !in_array($meap->getImportanceFactor(), [100, 1000, 10000]))))
                ) {
                    $old = $header['econ_target_maoc'];
                    $new = trim($_POST['target_maoc']);
                    if ($old !== $new && is_numeric($new)) {
                        $error = $meap->setEcon('target', 'maoc', $new);
                        if (is_string($error)) {
                            $DEFAULT_ERROR[] = "The other costs target amount could not be updated: {$error}";
                        } else {
                            $msg .= "The Other Costs Target Amount has changed from '{$old}' to '{$new}'\n";
                        }
                    }
                }
                if ($user->isInGroup(['role_EM', 'role_ES', 'role_ENG'])) {
                    $old = $header['econ_eac_maoc'];
                    $new = trim($_POST['eac_maoc']);
                    if ($old !== $new && is_numeric($new)) {
                        $error = $meap->setEcon('eac', 'maoc', $new);
                        if (is_string($error)) {
                            $DEFAULT_ERROR[] = "The other costs EAC amount could not be updated: {$error}";
                        } else {
                            $msg .= "The Other Costs EAC Amount has changed from '{$old}' to '{$new}'\n";
                        }
                    }
                }
                if ($user->isInGroup(['role_EM', 'role_ES', 'role_COO', 'role_ENG'])) {
                    $old = $header['econ_actual_maoc'];
                    $new = trim($_POST['actual_maoc']);
                    if ($old !== $new && is_numeric($new)) {
                        $error = $meap->setEcon('actual', 'maoc', $new);
                        if (is_string($error)) {
                            $DEFAULT_ERROR[] = "The other costs actual amount could not be updated: {$error}";
                        } else {
                            $msg .= "The Other Costs Actual Amount has changed from '{$old}' to '{$new}'\n";
                        }
                    }
                }

                // Prototype Material Costs
                if (
                    (empty($header['econ_target_pmc']) && $user->isInGroup(['role_EM', 'role_ES', 'gg_EXCOM']))
                    || (!empty($header['econ_target_pmc']) && ($user->isInGroup(['gg_EXCOM']) || ($user->isInGroup(['role_EM', 'role_ES']) && !in_array($meap->getImportanceFactor(), [100, 1000, 10000]))))
                ) {
                    $old = $header['econ_target_pmc'];
                    $new = trim($_POST['target_pmc']);
                    if ($old !== $new && is_numeric($new)) {
                        $error = $meap->setEcon('target', 'pmc', $new);
                        if (is_string($error)) {
                            $DEFAULT_ERROR[] = "The prototype material costs target amount could not be updated: {$error}";
                        } else {
                            $msg .= "The Prototype Material Costs Target Amount has changed from '{$old}' to '{$new}'\n";
                        }
                    }
                }
                if ($user->isInGroup(['role_EM', 'role_ES', 'role_ENG'])) {
                    $old = $header['econ_eac_pmc'];
                    $new = trim($_POST['eac_pmc']);
                    if ($old !== $new && is_numeric($new)) {
                        $error = $meap->setEcon('eac', 'pmc', $new);
                        if (is_string($error)) {
                            $DEFAULT_ERROR[] = "The prototype material costs EAC amount could not be updated: {$error}";
                        } else {
                            $msg .= "The Prototype Material Costs EAC Amount has changed from '{$old}' to '{$new}'\n";
                        }
                    }
                }
                if ($user->isInGroup(['role_EM', 'role_ES', 'role_COO', 'role_ENG'])) {
                    $old = $header['econ_actual_pmc'];
                    $new = trim($_POST['actual_pmc']);
                    if ($old !== $new && is_numeric($new)) {
                        $error = $meap->setEcon('actual', 'pmc', $new);
                        if (is_string($error)) {
                            $DEFAULT_ERROR[] = "The prototype material costs actual amount could not be updated: {$error}";
                        } else {
                            $msg .= "The Prototype Material Costs Actual Amount has changed from '{$old}' to '{$new}'\n";
                        }
                    }
                }

                // Prototype Labor Hours
                if (
                    (empty($header['econ_target_plh']) && $user->isInGroup(['role_EM', 'role_ES', 'gg_EXCOM']))
                    || (!empty($header['econ_target_plh']) && ($user->isInGroup(['gg_EXCOM']) || ($user->isInGroup(['role_EM', 'role_ES']) && !in_array($meap->getImportanceFactor(), [100, 1000, 10000]))))
                ) {
                    $old = $header['econ_target_plh'];
                    $new = trim($_POST['target_plh']);
                    if ($old !== $new && is_numeric($new)) {
                        $error = $meap->setEcon('target', 'plh', $new);
                        if (is_string($error)) {
                            $DEFAULT_ERROR[] = "The prototype labor target hours could not be updated: {$error}";
                        } else {
                            $msg .= "The Prototype Labor Target Hours has changed from '{$old}' to '{$new}'\n";
                        }
                    }
                }
                if ($user->isInGroup(['role_EM', 'role_ES', 'role_ENG'])) {
                    $old = $header['econ_eac_plh'];
                    $new = trim($_POST['eac_plh']);
                    if ($old !== $new && is_numeric($new)) {
                        $error = $meap->setEcon('eac', 'plh', $new);
                        if (is_string($error)) {
                            $DEFAULT_ERROR[] = "The prototype labor EAC hours could not be updated: {$error}";
                        } else {
                            $msg .= "The Prototype Labor EAC Hours has changed from '{$old}' to '{$new}'\n";
                        }
                    }
                }
                if ($user->isInGroup(['role_EM', 'role_ES', 'role_COO', 'role_ENG'])) {
                    $old = $header['econ_actual_plh'];
                    $new = trim($_POST['actual_plh']);
                    if ($old !== $new && is_numeric($new)) {
                        $error = $meap->setEcon('actual', 'plh', $new);
                        if (is_string($error)) {
                            $DEFAULT_ERROR[] = "The prototype labor actual hours could not be updated: {$error}";
                        } else {
                            $msg .= "The Prototype Labor Actual Hours has changed from '{$old}' to '{$new}'\n";
                        }
                    }
                }

                // Material
                if (
                    (empty($header['econ_target_material']) && $user->isInGroup(['role_EM', 'role_ES', 'gg_EXCOM']))
                    || (!empty($header['econ_target_material']) && ($user->isInGroup(['gg_EXCOM']) || ($user->isInGroup(['role_EM', 'role_ES']) && !in_array($meap->getImportanceFactor(), [100, 1000, 10000]))))
                ) {
                    $old = $header['econ_target_material'];
                    $new = trim($_POST['target_material']);
                    if ($old !== $new && is_numeric($new)) {
                        $error = $meap->setEcon('target', 'material', $new);
                        if (is_string($error)) {
                            $DEFAULT_ERROR[] = "The material target amount could not be updated: {$error}";
                        } else {
                            $msg .= "The Material Target Amount has changed from '{$old}' to '{$new}'\n";
                        }
                    }
                }
                if ($user->isInGroup(['role_EM', 'role_ES', 'role_ENG'])) {
                    $old = $header['econ_eac_material'];
                    $new = trim($_POST['eac_material']);
                    if ($old !== $new && is_numeric($new)) {
                        $error = $meap->setEcon('eac', 'material', $new);
                        if (is_string($error)) {
                            $DEFAULT_ERROR[] = "The material EAC amount could not be updated: {$error}";
                        } else {
                            $msg .= "The Material EAC Amount has changed from '{$old}' to '{$new}'\n";
                        }
                    }
                }
                if ($user->isInGroup(['role_EM', 'role_ES', 'role_COO', 'role_ENG'])) {
                    $old = $header['econ_actual_material'];
                    $new = trim($_POST['actual_material']);
                    if ($old !== $new && is_numeric($new)) {
                        $error = $meap->setEcon('actual', 'material', $new);
                        if (is_string($error)) {
                            $DEFAULT_ERROR[] = "The material actual amount could not be updated: {$error}";
                        } else {
                            $msg .= "The Material Actual Amount has changed from '{$old}' to '{$new}'\n";
                        }
                    }
                }

                // Target Hours
                if (
                    (empty($header['econ_target_hours']) && $user->isInGroup(['role_EM', 'role_ES', 'gg_EXCOM']))
                    || (!empty($header['econ_target_hours']) && ($user->isInGroup(['gg_EXCOM']) || ($user->isInGroup(['role_EM', 'role_ES']) && !in_array($meap->getImportanceFactor(), [100, 1000, 10000]))))
                ) {
                    $old = $header['econ_target_hours'];
                    $new = trim($_POST['target_hours']);
                    if ($old !== $new && is_numeric($new)) {
                        $error = $meap->setEcon('target', 'hours', $new);
                        if (is_string($error)) {
                            $DEFAULT_ERROR[] = "The target hours could not be updated: {$error}";
                        } else {
                            $msg .= "The Target Hours has changed from '{$old}' to '{$new}'\n";
                        }
                    }
                }
                if ($user->isInGroup(['role_EM', 'role_ES', 'role_ENG'])) {
                    $old = $header['econ_eac_hours'];
                    $new = trim($_POST['eac_hours']);
                    if ($old !== $new && is_numeric($new)) {
                        $error = $meap->setEcon('eac', 'hours', $new);
                        if (is_string($error)) {
                            $DEFAULT_ERROR[] = "The EAC hours could not be updated: {$error}";
                        } else {
                            $msg .= "The EAC Target Hours has changed from '{$old}' to '{$new}'\n";
                        }
                    }
                }
                if ($user->isInGroup(['role_EM', 'role_ES', 'role_COO', 'role_ENG'])) {
                    $old = $header['econ_actual_hours'];
                    $new = trim($_POST['actual_hours']);
                    if ($old !== $new && is_numeric($new)) {
                        $error = $meap->setEcon('actual', 'hours', $new);
                        if (is_string($error)) {
                            $DEFAULT_ERROR[] = "The actual hours could not be updated: {$error}";
                        } else {
                            $msg .= "The Actual Target Hours has changed from '{$old}' to '{$new}'\n";
                        }
                    }
                }

                if ($msg) {
                    if ($comment) {
                        $msg .= "Comment: {$comment}";
                    }
                    $meap->addLog($user->getID(), TldDatabase::escape($msg));
                    $DEFAULT_ERROR[] = 'Economics have been updated';
                }
                $meap->refresh();
                $body .= _getEconomics($meap);
                break;
            case 'refresh':
                $meap->refreshActualDevHours();
                $DEFAULT_ERROR[] = 'The Actual Development Hours Has Been Refreshed';
            // Continue with display
            default:
                $childHeaders = [];
                $children = $meap->getChildrenAndGrandchildrenList();
                foreach ($children as $child){
                    $eap = new tldEAP($child['id']);
                    $childHeaders[$child['id']] = $eap->itsHeader;
                    $level = 1;
                    $childHeaders[$child['id']]['expected_hours'] += $eap->getSumOfHoursOfChildren($eap->getChildList(), $level, 'expected_hours');
                    $childHeaders[$child['id']]['total_hours_actual'] += $eap->getSumOfHoursOfChildren($eap->getChildList(), $level, 'total_hours_actual');
                }
                $body .= _getEconomics($meap, $childHeaders);
                break;
        }
        break;
    case 'risk':
        $DEFAULT_MENU .= <<<EOF
	<a href="/en/private/manufacturing/eng/dev.php?m[0]=eap&m[1]=forms&m[2]=newEAP&from[mod]=MEAP&from[id]=$id&from[cat]=risk">Create New Risk EAP</a>
EOF;
        $body .= _getRiskAssessment();
        break;
    case 'project':
        $DEFAULT_MENU .= <<<EOF
	<a href="/en/private/manufacturing/eng/dev.php?m[0]=eap&m[1]=forms&m[2]=newEAP&from[mod]=MEAP&from[id]=$id&from[cat]=goal">Create New Goal EAP</a>
&nbsp;|&nbsp; <a href="/en/private/manufacturing/eng/dev.php?m[0]=eap&m[1]=forms&m[2]=newEAP&from[mod]=MEAP&from[id]=$id&from[cat]=risk">Create New Risk EAP</a>
EOF;
        $family = [];
        $meap->getChildListRec($family, 0, true);
        $body .= _getProjectGoals($family);
        $body .= _getRiskAssessment($family);
        break;
    case 'dashboard':
        $smarty->assign('width', '100%');
        $body .= "<h1>{$meap->getShortDesc()}</h1>";
        $body .= "<h2>MEAP#$id Dashboard</h2>";
        $cells = [
            _getProvisional($meap, $status) .
            "<br/><div style=\"\"><a href=\"$php_self?m[0]=meap&m[1]=view&m[2]=provisional&m[3]=edit&id=$id\">edit provisional dates</a></div>",
            _getEconomics($meap) .
            "<br/><div style=\"margin-bottom:30px;\"><a href=\"$php_self?m[0]=meap&m[1]=view&m[2]=econ&m[3]=edit&id=$id\">update economics</a></div>",
        ];

        if (10000 === (int) $meap->getImportanceFactor()) {
            $infants = $meap->getInfantEconomicsDashboard();
            $report = new tldReportColumnar(
                $infants,
                [
                    'xItems' => [
                        'id' => 'MEAP#',
                        'short_desc' => 'MEAP#',
                        'status' => 'Current Phase',
                        'key_event_description' => 'Next Key Event',
                        'key_event_management_target' => 'Management Target',
                    ],
                    'links' => [
                        'id' => [
                            'url' => "$php_self?m[0]=meap&m[1]=view&m[2]=dashboard",
                            'params' => ['id' => 'id'],
                        ],
                    ],
                    'sortable' => 'no',
                    'title' => 'Next key Events from infant MEAP',
                ]
            );
            $cells[] = $report->fetch();

            $report = new tldReportColumnar(
                $infants,
                [
                    'xItems' => [
                        'id' => 'MEAP#',
                        'short_desc' => 'MEAP#',
                        'econ_target_dh' => 'Dev hour target',
                        'econ_eac_dh' => 'Dev hour EAC',
                        'econ_actual_dh' => 'Dev hour actual',
                    ],
                    'links' => [
                        'id' => [
                            'url' => "$php_self?m[0]=meap&m[1]=view&m[2]=dashboard",
                            'params' => ['id' => 'id'],
                        ],
                    ],
                    'width' => '554',
                    'sortable' => 'no',
                    'showzero' => true,
                    'sumTotalsArray' => ['econ_target_dh', 'econ_eac_dh','econ_actual_dh'],
                    'title' => 'Engineering program capitalized for infant MEAP',
                ]
            );
            $cells[] = $report->fetch();
        }

        $report = new tldHTMLTable(
            $cells,
            [
                'cols' => 2,
                'attribs' => [
                    'table' => " width='100%'",
                    'tr' => " bgcolor='#FFFFFF'",
                    'td' => " width='50%'",
                ],
            ]
        );
        $body .= $report->fetch();
        $body .= <<<HTML
<div class="js-meap-detail-target" >
    Loading...       
</div>

<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function() {
        var headers = new Headers();
        headers.append('X-Requested-With', 'XMLHttpRequest')
        fetch(window.location.href.replace('dashboard', 'partialDashboard'), {headers: headers}).then(function(response) {
            return response.text()
        })
        .then(function(body) {
            document.querySelector('.js-meap-detail-target').innerHTML = body
            let table = document.querySelector('.js-meap-detail-target table');
            if (table) {
                sorttable.makeSortable(table);
            }
        })
    });
</script>
HTML;
        break;
    case 'partialDashboard':
        // Ajax Request
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 'XMLHttpRequest' === $_SERVER['HTTP_X_REQUESTED_WITH']) {
            $DEFAULT_TEMPLATE = 'empty.tpl';
            $DEFAULT_MENU = '';
            $body = '<br/><hr/>';
        } else {
            $body .= '<br/><hr/>';
        }
        $dtc = $meap->getFiles(1);
        $body .= '<h3>Current DTC: &nbsp; ';
        if (count($dtc)) {
            $f = end($dtc);
            $body .= "<a href=\"/en/private/common/index.php?m[0]=files&m[1]=view&m[2]=out&id={$f['id']}\" title=\"Download file: {$f['filename']}\"><img src=\"/shared/bluesphere/32x32/mimetypes/document.png\" style=\"vertical-align:middle;\" /></a>";
        } else {
            $body .= 'N/A';
        }
        $body .= '</h3>';
        $body .= '<hr/>';
        $family = [];
        $meap->getChildListRec($family, 0, true);
        $body .= _getRiskAssessment($family);
        $body .= '<br/><hr/>';

        $gs = $meap->getFiles(2);
        $body .= '<h3>Current Goal Sheet: &nbsp; ';
        if (count($gs)) {
            $f = end($gs);
            $body .= "<a href=\"/en/private/common/index.php?m[0]=files&m[1]=view&m[2]=out&id={$f['id']}\" title=\"Download file: {$f['filename']}\"><img src=\"/shared/bluesphere/32x32/mimetypes/document.png\" style=\"vertical-align:middle;\" /></a>";
        } else {
            $body .= 'N/A';
        }
        $body .= '</h3>';
        $body .= '<h3>Original Goal Sheet: &nbsp; ';
        if (count($gs) > 1) {
            $f = $gs[0];
            $body .= "<a href=\"/en/private/common/index.php?m[0]=files&m[1]=view&m[2]=out&id={$f['id']}\" title=\"Download file: {$f['filename']}\"><img src=\"/shared/bluesphere/32x32/mimetypes/document.png\" style=\"vertical-align:middle;\" /></a>";
        } else {
            $body .= 'N/A';
        }
        $body .= '</h3>';
        $body .= '<hr/>';

        $planning = $meap->getFiles(3);
        $body .= '<h3>Latest Planning: &nbsp; ';
        if (count($planning)) {
            $f = end($planning);
            $body .= "<a href=\"/en/private/common/index.php?m[0]=files&m[1]=view&m[2]=out&id={$f['id']}\" title=\"Download file: {$f['filename']}\"><img src=\"/shared/bluesphere/32x32/mimetypes/document.png\" style=\"vertical-align:middle;\" /></a>";
        } else {
            $body .= 'N/A';
        }
        $body .= '</h3>';
        $body .= '<hr/>';

        $body .= _getProjectGoals($family);
        break;
    case 'modOverview':
        $DEFAULT_TITLE .= "\Module Tasks Overview";
        $t1 = ($f1) ? 0 : 1;
        $a1 = ($f1) ? 'Hide' : 'Show';
        $t2 = ($f2) ? 0 : 1;
        $a2 = ($f2) ? 'Hide' : 'Show';
        $DEFAULT_MENU .= <<<EOF
	<a href="$php_self?m[0]=meap&m[1]=view&m[2]=modOverview&id=$id&f1=$t1&f2=$f2">$a1 Closed EAPs (toggle)</a>
&nbsp;|&nbsp; <a href="$php_self?m[0]=meap&m[1]=view&m[2]=modOverview&id=$id&f1=$f1&f2=$t2">$a2 Closed TASKS (toggle)</a>
EOF;
        $overlib = $smarty->fetch('overlib.inc.js.tpl');
        $overlib .= '<script type="text/javascript">$(function(){$("[data-body]").overlib()});</script>';
        $smarty->assign('html_head', $overlib);
        $smarty->assign('width', '100%');
        $links = [];
        // Get child links
        $mods = [];
        $meap->getChildListRec($mods, 0 , true);
        foreach ($mods AS $mod) {
            if (!array_key_exists($mod['module'], $links)){
                $links[$mod['module']] = [];
            }
            if (!in_array($mod['id'], $links[$mod['module']])) {
                $links[$mod['module']][] = $mod['id'];
            }
        }
        // Get mod links
        $mods = tldModLink::byParent($id, 'MEAP');
        foreach ($mods AS $mod) {
            if (!in_array($mod['item'], $links[$mod['type']] ?? [], true)) {
                $links[$mod['type']][] = $mod['item'];
            }
        }
        $mods = tldModLink::byItem($id, 'MEAP');
        foreach ($mods AS $mod) {
            if (!in_array($mod['parent_id'], $links[$mod['module']] ?? [], true)) {
                $links[$mod['module']][] = $mod['parent_id'];
            }
        }
        foreach ($links AS $module => $group) {
            foreach ($group AS $link) {
                if ($rows = tldTask::getGanttOverview($module, $link, !$f2 ? "tasks.status='OPEN'" : '')) {
                    $desc = '';
                    $_title = "$module#$link";
                    if ($module === 'EAP') {
                        $tmp = new tldEAP($link);
                        if (!$f1 && in_array($tmp->getStatus(), ['CLOSED', 'REJECTED'], true)) {
                            continue;
                        }
                        $desc = "<p>CURRENT STATUS: {$tmp->getStatus()}</p><p>SHORT DESCRIPTION: {$tmp->getShortDesc()}</p>";
                        $_title = "<a href=\"/en/private/manufacturing/eng/dev.php?m[0]=eap&m[1]=view&id=$link\">$module#$link</a>";
                    }

                    $cnt = count($rows);
                    $ovd = 0;
                    foreach ($rows AS &$row) {
                        $row['late_stats'] = "Opened: {$row['date']} Due: {$row['due_date']}";
                        if ($row['overdue']) {
                            $ovd++;
                            $row['late'] = 'OVERDUE';
                            $row['late_stats'] .= " ({$row['days_late']} days late)";
                        } elseif ($row['status'] !== 'CLOSED') {
                            $row['late'] = 'ON TIME';
                            $row['late_stats'] .= ' (on time)';
                        } else {
                            $row['late'] = 'N/A';
                            $row['late_stats'] .= ' (closed)';
                        }
                        $row['last_ten_comments_html'] = str_replace("\n", '&lt;br/&gt;', htmlentities($row['last_ten_comments']));
                        $row['task_add_comment'] = $row['id'];
                        $row['task_reschedule'] = $row['id'];
                        $row['task_transfer'] = $row['id'];
                        $row['task_close'] = $row['id'];
                    }
                    $form = new tldGanttChart("MEAP_OVERVIEW_$module$id", $rows, [
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
                            'id' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=',
                            'task_add_comment' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=comment&id=',
                            'task_reschedule' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=reschedule&id=',
                            'task_transfer' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=transfer&id=',
                            'task_close' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=closeConfirm&id=',
                        ],
                        'groupAttributes' => [
                            'late' => [
                                'OVERDUE' => 'style="font-weight:bold;background:#900;color:#fff;"',
                                'ON TIME' => 'style="font-weight:bold;background:#090;color:#fff;"',
                                #'style="verticle-align:middle;"',
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
                                'title' => 'Add New Comment Task#%d',
                            ],
                            'task_reschedule' => [
                                'callback' => 'intval',
                                'icon' => 'schedule',
                                'useIconHeader' => true,
                                'useIconValue' => true,
                                'title' => 'Reschedule Task#%d',
                            ],
                            'task_transfer' => [
                                'callback' => 'intval',
                                'icon' => 'transfer',
                                'useIconHeader' => true,
                                'useIconValue' => true,
                                'title' => 'Transfer Task#%d',
                            ],
                            'task_close' => [
                                'callback' => 'intval',
                                'icon' => 'close',
                                'useIconHeader' => true,
                                'useIconValue' => true,
                                'title' => 'Close Task#%d',
                            ],
                        ],
                    ]);
                    $blocks[] = <<<EOF
<h3>$_title</h3>$desc
<p><b>Tasks Total:</b> $cnt &nbsp; <b>Overdue:</b> $ovd</p>
{$form->fetch()}
&nbsp; + <a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=form&m[2]=newTask&module=$module&parent_id=$link">Add a new task to $module#$link</a>
<br/>
EOF;
                }
            }
        }
        $body .= implode("\n<br/><hr/><br/>\n", $blocks ?? []);
        break;
    case 'batchTasksReschedule':
        $DEFAULT_TITLE .= "\MEAP#$id Reschedule Tasks";
        $smarty->assign('width', '100%');
        if (!$user->isInGroup(['role_EM', 'role_ES'])) {
            $DEFAULT_ERROR[] = 'You do not have the permissions to reschedule tasks by batch';
            break;
        }
        if (!$id) {
            $DEFAULT_ERROR[] = 'The MEAP number is unknown';
            break;
        }
        $meap = new tldMEAP($id);
        $tasks = $meap->getTaskSummary();
        $openedTasks = array_filter($tasks, static function ($task) {
            return $task['task_status'] !== 'CLOSED';
        });
        $fields = [
            'eap_id' => 'EAP#',
            'eap_status' => 'Status',
            'factory' => 'Location',
            'eap_desc' => 'EAP Description',
            'task_id' => 'Task#',
            'task_dt_open' => 'Date Opened',
            'assignor_fullname' => 'Assignor',
            'assignee_fullname' => 'Assignee',
            'task_status' => 'Task Status',
            'task_desc' => 'Task Description',
            'est_time' => 'Estimated Time',
            'timekeeping' => 'Timekeeping',
            'due_date' => 'Due Date',
        ];
        $smarty->assign('id', $id);
        $smarty->assign('fields', $fields);
        $smarty->assign('rows', $openedTasks);
        $body .= $smarty->fetch("$PATH/meap/task.listing.tpl");

        $form = new HTML_QuickForm('frmTaskRescheduler', 'post');
        $form->addElement('hidden', 'm[0]', 'meap');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'batchTasksReschedule');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', 'MEAP Task Rescheduler');
        $form->addElement('text', 'ddiff', 'Delay project by (days) :', [], 'Delay project by :');
        $form->addElement('text', 'due_date', 'Set all Due Dates To :', ['class' => 'datepicker']);
        $form->addElement('textarea', 'reason', 'Reschedule Reason :', ['cols' => 40, 'rows' => 10]);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        //set rules
        $form->addRule('reason', 'Required', 'required');
        $form->setDefaultS(['ddiff' => 0]);

        //Form posted from the template
        if (array_key_exists('meapTaskRescheduler', $_POST)) {
            foreach ($openedTasks as $task) {
                $dueDate = isset($_POST['tasks'][$task['task_id']]['due_date']) ? $_POST['tasks'][$task['task_id']]['due_date'] : null;
                if ($dueDate !== null && $dueDate !== $task['due_date']) {
                    $newDueDate = DateTime::createFromFormat('Y-m-d', $dueDate);
                    $errors = DateTime::getLastErrors();
                    if (!empty($errors['warning_count']) || !empty($errors['error_count'])) {
                        $DEFAULT_ERROR[] = "ERROR: The date requested for task#{$task['task_id']} doesn't exists. (Selected $dueDate)";
                        continue;
                    }
                    if ((new DateTime()) > $newDueDate) {
                        $DEFAULT_ERROR[] = "ERROR: Rescheduling can not be in past... (Selected {$task['due_date']} for task#{$task['task_id']})";
                        continue;
                    }
                    $comment = [
                        'poster' => $user->getId(),
                        'comment' => "Tasks Rescheduled to the {$newDueDate->format('Y-m-d')} from MEAP batch rescheduler.\n\n",
                    ];
                    $t = new tldTask($task['task_id']);
                    $t->reschedule($newDueDate->format('Y-m-d'));
                    $t->addComment($comment);
                    $DEFAULT_SUCCESS[] = "The Task#{$task['task_id']} have successfully been rescheduled";
                }
            }
            $body .= $form->toHTML();
            break;
        }

        if (!$form->validate()) {
            $body .= $form->toHTML();
            break;
        }

        $vars = $form->exportValues();

        if (empty($vars['ddiff']) && empty($vars['due_date'])) {
            $DEFAULT_ERROR[] = 'ERROR: You must either select a new date or a number of days.';
            $body .= $form->toHTML();
            break;
        }

        // Rescheduling by Numbers of days as the preference
        if (!empty($vars['ddiff'])) {
            $vars['ddiff'] = (int)$vars['ddiff'];
            if ($vars['ddiff'] < 0) {
                $DEFAULT_ERROR[] = 'ERROR: The number of days must be a positive integer.';
                $body .= $form->toHTML();
                break;
            }
            $comment = [
                'poster' => $user->getId(),
                'comment' => "MEAP Rescheduled of {$vars['ddiff']} days\n\n" . $vars['reason'],
            ];
            foreach ($openedTasks as $task) {
                $newDueDate = new DateTime($task['due_date']);
                $newDueDate->add(new DateInterval("P{$vars['ddiff']}D"));
                $t = new tldTask($task['task_id']);
                $t->reschedule($newDueDate->format('Y-m-d'));
                $t->addComment($comment);
            }
        } else {
            // Rescheduling to the same date
            $newDueDate = DateTime::createFromFormat('Y-m-d', $vars['due_date']);
            $errors = DateTime::getLastErrors();
            if (!empty($errors['warning_count']) || !empty($errors['error_count'])) {
                $DEFAULT_ERROR[] = "ERROR: This date doesn't exists. (Selected {$vars['due_date']})";
                $body .= $form->toHTML();
                break;
            }
            if ((new DateTime()) > $newDueDate) {
                $DEFAULT_ERROR[] = "ERROR: Rescheduling can not be in past... (Selected {$vars['due_date']})";
                $body .= $form->toHTML();
                break;
            }
            $comment = [
                'poster' => $user->getId(),
                'comment' => "MEAP Rescheduled to the {$newDueDate->format('Y-m-d')} \n\n" . $vars['reason'],
            ];
            foreach ($openedTasks as $task) {
                $t = new tldTask($task['task_id']);
                $t->reschedule($newDueDate->format('Y-m-d'));
                $t->addComment($comment);
            }
        }
        $DEFAULT_SUCCESS[] = 'The Tasks have successfully been rescheduled';
        $body .= 'The Tasks have successfully been rescheduled';
        break;
    case 'allBP':
        $DEFAULT_MENU .= <<<EOF
<a href="/en/private/manufacturing/eng/dev.php?m[0]=meap&m[1]=view&m[2]=allBP&m[3]=csv&id=$id">csv</a>
EOF;

        $rows = $meap->getFamilyTree(true, true);
        foreach ($rows as $row) {
            if ($row['module'] === 'EAP') {
                $eapIds[] = $row['id'];
            } else {
                $meapIds[] = $row['id'];
            }
        }
        $xItems = [
            'id' => 'BP#',
            'module' => 'Module',
            'status' => 'Status',
            'dt_opened' => 'Date Opened',
            'dt_closed' => 'Date Closed',
            'short_desc' => 'Short Description',
            'long_desc' => 'Long Description',
        ];
        $allBP = tldMEAP::getAllBPs($eapIds, $meapIds);
        switch ($m[3]) {
            case 'csv':
                $report = new tldCSV(
                    $allBP,
                    [
                        'xItems' => $xItems,
                        "showTitles" => TRUE
                    ]
                );
                $report->out();
                exit;
        }
        $report = new tldReportColumnar(
            $allBP,
            [
                'xItems' => $xItems,
                'title' => 'All BP',
                'links' => ['id' => '/en/private/calendar/calendar.php?m[0]=bp&m[1]=view&id='],
            ]
        );
        $body .= $report->fetch();
        break;
    default:
        $DEFAULT_MENU .= <<<EOF
<a href="/en/private/manufacturing/eng/dev.php?m[0]=eap&m[1]=forms&m[2]=newEAP&from[mod]=MEAP&from[id]=$id">Create &amp; Link New EAP</a>
&nbsp;|&nbsp; <a href="/en/private/manufacturing/eng/dev.php?m[0]=meap&m[1]=view&m[2]=allBP&id=$id">All BPs</a>
EOF;
        $body .= _getGeneralTab();
}

// Functions
function _createHierarchyLinks($rows)
{
    global $php_self;
    foreach ($rows AS &$row) {
        $mod = strtolower($row['module']);
        $row['id_html'] = "<a href='{$php_self}?m[0]={$mod}&m[1]=view&id={$row['id']}'>{$row['id']}</a>";
    }
    return $rows;
}

function _drawHierarchyTree(array $tree = null, $parentId = 0, $parentModule = '')
{
    global $meap, $php_self;
    $html = '';
    $isFirst = false;
    if (null === $tree) {
        // First run through, get array
        $tree = $meap->getFamilyTree(false);
        $isFirst = true;
        $html .= <<<HTML
<style type="text/css">    
    ul.tree, ul.tree ul {
        list-style-type: none;
        list-style-image: none;
        margin:0;
        padding:0;
    }
    ul.tree ul {
        padding-left: 3em;
        background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAAKAQMAAABPHKYJAAAAA1BMVEWIiIhYZW6zAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH1ggGExMZBky19AAAAAtJREFUCNdjYMAEAAAUAAHlhrBKAAAAAElFTkSuQmCC) repeat-y;
    }
    ul.tree ul:last-child {
        background: none;
    }
    ul.tree li {
        margin:0;
        padding: 0 1em;
        background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAgAAAAUAQMAAACK1e4oAAAABlBMVEUAAwCIiIgd2JB2AAAAAXRSTlMAQObYZgAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB9YIBhQIJYVaFGwAAAARSURBVAjXY2hgQIf/GTDFGgDSkwqATqpCHAAAAABJRU5ErkJggg==) no-repeat;
        line-height: 20px;
        font-weight: bold;
    }
    ul.tree li:nth-last-of-type(1) {
        background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAgAAAAUAQMAAACK1e4oAAAABlBMVEUAAwCIiIgd2JB2AAAAAXRSTlMAQObYZgAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB9YIBhQIIhs+gc8AAAAQSURBVAjXY2hgQIf/GbAAAKCTBYBUjWvCAAAAAElFTkSuQmCC) no-repeat;
    }
    ul.tree .hidden {
        display: none;
    }
    
    
     ul.tree .caret {
      cursor: pointer;
      user-select: none;
    }

    ul.tree .caret::after {
      content: url(/shared/bluesphere/16x16/actions/1rightarrow.png);
      display: inline-block;
      margin-left: 1em;
    }

    ul.tree .caret-down::after {
      transform: rotate(90deg);

    }

    ul.tree .nested .nested {
      display: none;
    }

    ul.tree .nested .active {
      display: block;
    }

    ul.tree .nested .global-active {
      display: block!important;
    }

    ul.tree .hide-closed .module-closed {
      display: none!important;
    }
    
     ul.tree .global-active .caret::after {
      transform: rotate(90deg);
    }
</style>

<script >
    document.addEventListener("DOMContentLoaded", function() {
        var toggler = document.getElementsByClassName("caret");
        var i = 0;
        var elementToRemove = [];
        for (i = 0; i < toggler.length; i++) {
          if ('UL' !== (toggler[i].parentNode.nextElementSibling && toggler[i].parentNode.nextElementSibling.tagName)) {
            elementToRemove.push(toggler[i])
          }
        }
         elementToRemove.forEach(function (el) {
          el.remove()
         })

        toggler = document.getElementsByClassName("caret");

        for (i = 0; i < toggler.length; i++) {
            toggler[i].addEventListener("click", function() {
               this.parentNode.nextElementSibling.classList.toggle("active");
               this.classList.toggle("caret-down");
            });
        } 
    });
</script>

HTML;
    }

    $children = array_filter($tree, static function ($var) use ($parentId, $parentModule) {
        return ((int) $var['parent_id'] === (int) $parentId && $var['parent_module'] ===  $parentModule);
    });
    if ($children) {
        $html .= sprintf('<ul class="%s">', $isFirst ? 'tree' : 'nested');
        foreach ($children AS $child) {
            $style = $child['module'] === 'MEAP' && $child['id'] === $meap->getID() ? 'style="background-color:#dedede;"' : '';
            $linkStyle = $class = '';
            if (in_array($child['data']['status'], ['CLOSED', 'REJECTED'], true)) {
                $linkStyle = 'style="color: #7D7878"';
                $class = 'class="module-closed"';
            }
            $url = "$php_self?m[0]=" . strtolower($child['module']) . "&m[1]=view&id={$child['id']}";
            $caret = $isFirst ? '' : '<span class="caret"><span>';

            $html .= <<<HTML
<li {$style} {$class}><a href="$url" $linkStyle>{$child['module']} #{$child['id']} - IF  {$child['data']['ifactor']} : {$child['data']['short_desc']}</a>$caret</li>
HTML;
            $html .= _drawHierarchyTree($tree, $child['id'], $child['module']);
        }
        $html .= "</ul>";
    }


    return $html;
}

function _getRiskAssessment($family = [])
{
    global $meap, $php_self;
    $eaps = [];
    if (!$family) {
        $meap->getChildListRec($family, 0, true);
    }
    foreach ($family AS $member) {
        // Check if EAP is 'Risk Assessment' category typ
        if ($member['module'] !== 'EAP' || $member['data']['category'] !== 'Risk Assessment') {
            continue;
        }
        // Temp array
        $eap = $member['data'];
        // Get last log comment
        $query = <<<EOF
	    SELECT
	    	comment
	    FROM
	    	mod_logs
	    WHERE
	    	module='{$member['module']}' AND
	    	parent_id={$member['id']} AND
	    	log_num=1
	    ORDER BY
	    	id DESC
	    LIMIT 1
EOF;
        $res = tldUtils::getSqlRowToAssocArray($query);
        $eap['action'] = $res['comment'];
        // Insert array values
        $eaps[] = $eap;
    }
    // Create report
    $form = new tldReportColumnar(
        $eaps,
        [
            'xItems' => [
                'id' => 'EAP#',
                'short_desc' => 'Short Description',
                'status' => 'Status',
                'info' => 'Critical Phase Change Closure',
                'action' => 'Current Action Plan',
            ],
            'name' => 'risk_assesment_eap',
            'title' => 'Risk Assesment EAPs',
            'links' => ['id' => "$php_self?m[0]=eap&m[1]=view&id="],
        ]
    );
    return $form->fetch();
}

function _getProjectGoals($family = [])
{
    global $meap, $php_self;
    $eaps = [];
    if (!$family) {
        $meap->getChildListRec($family, 0, true);
    }
    foreach ($family AS $member) {
        // Check if EAP is 'Project Goal Change' category type
        if ($member['module'] !== 'EAP' || $member['data']['category'] !== 'Project Goal Change') {
            continue;
        }

        // Temp array
        $eap = $member['data'];
        // Get last log comment
        $query = <<<EOF
	    SELECT
	    	comment
	    FROM
	    	mod_logs
	    WHERE
	    	module='{$member['module']}' AND
	    	parent_id={$member['id']} AND
	    	log_num=1
	    ORDER BY
	    	id DESC
	    LIMIT 1
EOF;
        $res = tldUtils::getSqlRowToAssocArray($query);
        $eap['action'] = $res['comment'];
        // Insert array values
        $eaps[] = $eap;
    }
    // Create report
    $form = new tldReportColumnar(
        $eaps,
        [
            'xItems' => [
                'id' => 'EAP#',
                'short_desc' => 'Short Description',
                'status' => 'Status',
                'action' => 'Current Action Plan',
            ],
            'title' => 'Project Goal Change EAPs',
            'links' => ['id' => "$php_self?m[0]=eap&m[1]=view&id="],
        ]
    );
    return $form->fetch();
}
