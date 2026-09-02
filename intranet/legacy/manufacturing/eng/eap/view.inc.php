<?php
use ApiBundle\Client;
use Symfony\Component\HttpClient\Exception\ClientException;

if (empty($id)) {
    $DEFAULT_ERROR[] = 'ERROR: id has not been set...';
    return;
}
$eap = new tldEAP($id);
if ($eap->isEmpty()) {
    $DEFAULT_ERROR[] = "ERROR: No EAP #$id found...";
    return;
}
//check if private and a member
if ($eap->isPrivate() && $eap->isMember($user->getID()) !== true/* AND !$user->isInGroup("superuser")*/) {
    $DEFAULT_ERROR[] = 'ERROR: Only members can access this private EAP';
    return;
}
$header = $eap->getHeader();
$smarty->assign('eap', $header);
$DEFAULT_TITLE .= "&nbsp;#$id";

$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=eap&m[1]=view&id=$id">General</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=eap&m[1]=view&m[2]=hierarchy&id=$id">Hierarchy</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=eap&m[1]=view&m[2]=tasks&id=$id">Tasks</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=eap&m[1]=view&m[2]=bps&id=$id">Processes</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=eap&m[1]=view&m[2]=files&id=$id">Files</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=eap&m[1]=view&m[2]=pns&id=$id">PNs</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=eap&m[1]=view&m[2]=models&id=$id">Models</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=eap&m[1]=view&m[2]=links&id=$id">Links</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=eap&m[1]=view&m[2]=followers&id=$id">Followers</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=eap&m[1]=view&m[2]=log&id=$id">Log</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=eap&m[1]=view&m[2]=selectStatus&id=$id">Change Status</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=eap&m[1]=view&m[2]=reports&m[3]=byActivityDate&id=$id" title="Linked Module Activity Report">Linked Activity</a>
EOF;
if ($user->isInGroup(['gg_ADMIN', 'gg_ENG'])) {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="/en/private/manufacturing/eng/eap/eap_admin.php?mode=record_view&form_type=main_tpl&id=$id">Edit</a>
EOF;
}
if ($user->isInGroup(['gg_ADMIN', 'gg_ENG', 'role_ENG', 'role_EM'])) {
    $location = tldLocation::getERPByLocation($header['location']);
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="/en/private/manufacturing/eng/dev.php?m[0]=timekeeping&m[1]=reports&m[2]=timesheets&id=$id&link=EAP">Linked Timesheets</a>
&nbsp;|&nbsp;<a href="/en/private/manufacturing/eng/dev.php?m[0]=timekeeping&m[1]=form&m[2]=add2&module_id=$id&link=EAP&category=Design+Task&location=$location">Create Timesheet</a>
&nbsp;|&nbsp;<a href="/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=eng.newpartnb&module=EAP&id=$id">Start new part number sequence</a>
&nbsp;|&nbsp;<a href="/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=eng.newpartnbrevision&module=EAP&id=$id">Start new part number revision</a>
EOF;
}

if ($single) {
    unset($sess['eap']['list']);
}
$DEFAULT_MENU .= $smarty->fetch("$PATH/eap/eap.menu.inc.tpl");

// Get action plan log
$ap = $eap->getLog(1);
if (!empty($ap) && !empty($ap[0]['comment'])) {
    $body .= <<<EOF
<h3>Latest Action Plan:</h3>
<p>{$ap[0]['comment']}</p>
<hr/><br/>
EOF;
}

switch ($m[2]) {
    case 'reports':
        switch ($m[3]) {
            case 'byActivityDate':
                include_once('eap/report.activity.inc.php');
                break;
        }
        break;
    case 'hierarchy':
        $DEFAULT_TITLE .= "\Hierarchy";
        $DEFAULT_MENU .= <<<EOF
<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=eap&m[1]=view&m[2]=hierarchy&id=$id">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=eap&m[1]=view&m[2]=hierarchy&m[3]=add&m[4]=parent&id=$id">Link Parent</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=eap&m[1]=view&m[2]=hierarchy&m[3]=add&m[4]=child&id=$id">Add Child</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=eap&m[1]=view&m[2]=hierarchy&m[3]=familyTree&id=$id">Family Tree</a>
EOF;
        $body .= '<br>';
        switch ($m[3]) {
            case 'add':
                if (!$user->isInGroup(['gg_ENG', 'role_ENG', 'role_EM', 'role_ES', 'gg_ADMIN'])) {
                    $DEFAULT_ERROR[] = 'ERROR: You do not have permission for this action';
                    break;
                }
                // Set module options
                switch ($m[4]) {
                    case 'child':
                        $module_options = ['EAP' => 'EAP'];
                        $title = 'Add new child';
                        break;
                    case 'parent':
                        if ($eap->getParentID() > 0) {
                            $DEFAULT_ERROR[] = 'ERROR: Please unlink the current parent module before linking a new parent module';
                            break 2;
                        }
                        $module_options = ['' => '', 'MEAP' => 'MEAP', 'EAP' => 'EAP'];
                        $title = 'Link parent module';
                        break;
                    default:
                        $DEFAULT_ERROR[] = 'ERROR: Please determine weather linking a child or parent';
                        break 2;
                }
                $form = new HTML_QuickForm('frmAddHierarchy', 'get');
                $form->addElement('hidden', 'm[0]', 'eap');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('hidden', 'm[2]', 'hierarchy');
                $form->addElement('hidden', 'm[3]', 'add');
                $form->addElement('hidden', 'm[4]', $m[4]);
                $form->addElement('hidden', 'id', $id);
                $form->addElement('header', 'header', $title);
                $form->addElement('select', 'module', 'Module Type', $module_options);
                $form->addElement('text', 'modid', 'Module ID#');
                $form->addElement('submit', 'btnSubmit', 'Add');
                $form->addRule('module', 'Required', 'required');
                $form->addRule('modid', 'Required', 'required');

                if (!$form->validate()) {
                    $body .= $form->toHTML();
                    break;
                }

                switch ($m[4]) {
                    case 'child':
                        $vars = tldUtils::cleanupFormInput($form->exportValues());
                        // Check if child of itself
                        if ($vars['module'] === 'EAP' && $vars['modid'] == $id) {
                            $DEFAULT_ERROR[] = 'ERROR: Can not set a module as the child of itself';
                            break;
                        }
                        // Check if future child have already a parent
                        $childModule = ($vars['module'] === 'MEAP') ? new tldMEAP($vars['modid']) : new tldEAP($vars['modid']);
                        if ($childModule->isEmpty()) {
                            $DEFAULT_ERROR[] = "ERROR: Child not found.";
                            break;
                        }
                        $childModuleParentID = (int) $childModule->getParentID();
                        if ($childModuleParentID !== 0) {
                            $DEFAULT_ERROR[] = "ERROR: {$childModule->getModule()}#{$childModule->getID()} already has a parent module ({$childModule->getParentModule()}#$childModuleParentID)";
                            break;
                        }

                        $e = $eap->addChild($vars['modid'], $vars['module']);
                        if (is_string($e)) {
                            $DEFAULT_ERROR[] = "ERROR: Child not added, reason: $e";
                            break;
                        }
                        $body .= 'Module added as child successfully!';
                        break;
                    case 'parent':
                        $vars = tldUtils::cleanupFormInput($form->exportValues());
                        // Check if parent of itself
                        if ($vars['module'] === 'EAP' && $vars['modid'] == $id) {
                            $DEFAULT_ERROR[] = 'ERROR: Can not set a module as the parent of itself';
                            break;
                        }
                        $parentModule = ($vars['module'] === 'MEAP') ? new tldMEAP($vars['modid']) : new tldEAP($vars['modid']);
                        if ($parentModule->isEmpty()) {
                            $DEFAULT_ERROR[] = "ERROR: Parent not found.";
                            break;
                        }
                        $e = $parentModule->addChild($id, 'EAP');
                        if (is_string($e)) {
                            $DEFAULT_ERROR[] = "ERROR: Parent not added, reason: $e";
                            break;
                        }
                        $body .= 'Module added as parent successfully!';
                        break;
                }
                break;
            case 'delete':
                if (empty($_GET['modid']) || !is_numeric($_GET['modid']) || !in_array($_GET['module'], ['EAP', 'MEAP'])) {
                    $DEFAULT_ERROR[] = 'ERROR: Module info is missing or invalid...';
                    break;
                }
                if (!$user->isInGroup(['gg_ENG', 'role_ENG', 'role_EM', 'role_ES', 'gg_ADMIN'])) {
                    $DEFAULT_ERROR[] = 'ERROR: You do not have permission for this action';
                    break;
                }
                if (!in_array($m[4], ['child', 'parent'])) {
                    $DEFAULT_ERROR[] = 'ERROR: Please determine weather unlinking a child or parent';
                    break;
                }
                $modid = (int)$_GET['modid'];
                $module = TldDatabase::escape($_GET['module']);
                $linkedMod = ($module === 'MEAP') ? new tldMEAP($modid, true) : new tldEAP($modid, true);

                switch ($m[4]) {
                    case 'child':
                        // Check if it is really its child
                        if ($linkedMod->getParentID() !== $eap->getID() && $module !== $eap->getModule()) {
                            $DEFAULT_ERROR[] = 'ERROR: Module not a child';
                            break;
                        }
                        $e = $eap->deleteChild($modid, $module);
                        if (is_string($e)) {
                            $DEFAULT_ERROR[] = "ERROR: Child not removed, reason: $e";
                            break;
                        }
                        $body .= 'Module is no longer a child';
                        break;
                    case 'parent':
                        // Check if it is really its child
                        if ($eap->getParentID() !== $linkedMod->getID() && $eap->getParentModule() !== $module) {
                            $DEFAULT_ERROR[] = 'ERROR: Module not a parent';
                            break;
                        }
                        $e = $eap->update([
                            'parent_id' => 0,
                            'parent_module' => '',
                        ]);
                        if (is_string($e)) {
                            $DEFAULT_ERROR[] = "ERROR: Parent not removed, reason: $e";
                            break;
                        }
                        $body .= 'Module is no longer a parent';
                        break;
                }
                break;
            case 'familyTree':
                $body .= <<<HTML
<script type="application/javascript">
toggleVisibility = function() {
  document.querySelectorAll('.tree > ul > ul').forEach(function(element) { element.classList.toggle('hidden')  });
}
</script>
<h3>Family tree of EAP#{$id}
<button style="margin-left: 30px;" onclick="document.querySelectorAll('.tree > ul > ul').forEach(function(element) { element.classList.toggle('hidden')  });">Show/Hide first level only</button></h3>
HTML;

                $body .= _drawHierarchyTree();
                break;
        }
        // Get Parent hierarchy
        $parent_id = $eap->getParentID();
        if (!empty($parent_id)) {
            $parent = ($eap->getParentModule() === 'MEAP') ? new tldMEAP($parent_id) : new tldEAP($parent_id);
            $parent_header[0] = $parent->itsHeader ?: ['id' => $parent_id, 'module' => $eap->getParentModule()];
            $parent_header[0]['unlink'] = 'unlink';
            $report = new tldReportColumnar(
                _createHierarchyLinks($parent_header),
                [
                    'xItems' => [
                        'id_html' => 'ID#',
                        'module' => 'Module',
                        'short_desc' => 'Description',
                        'unlink' => 'Remove Parent',
                    ],
                    'title' => "Parent Module of EAP#{$id}",
                    'links' => [
                        'unlink' => [
                            'url' => "$php_self?m[0]=eap&m[1]=view&m[2]=hierarchy&m[3]=delete&m[4]=parent&id=$id",
                            'params' => ['modid' => 'id', 'module' => 'module'],
                            'confirmPopup' => 'Are you sure to remove parent?',
                        ],
                    ],
                ]
            );
            $body .= $report->fetch();
        } else {
            $body .= "<h3>Parent Module of EAP#{$id}</h3>";
            $body .= '<p>No records...</p>';
        }
        // Get child hierarchy
        $childList = $eap->getChildList();
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
                'title' => "Child Modules of EAP#{$id}",
                'links' => [
                    'unlink' => [
                        'url' => "$php_self?m[0]=eap&m[1]=view&m[2]=hierarchy&m[3]=delete&m[4]=child&id=$id",
                        'params' => ['modid' => 'id', 'module' => 'module'],
                        'confirmPopup' => 'Are you sure to remove child?',
                    ],
                ],
            ]
        );
        $body .= $report->fetch();
        break;
    case 'getfile':
        if (empty($id) || empty($fileid)) {
            $DEFAULT_ERROR[] = 'File id not set';
        } else {
            $file = $eap->getFiles($fileid);
            $file = new basicFile(tldUtils::getPathToUploadFile('eap_files', $file['filename']));
            $file->outFile();
            $template = 'NO_TEMPLATE';
        }
        break;
    case 'followers':
        $DEFAULT_TITLE .= "\Followers";
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=eap&m[1]=view&m[2]=followers&id=$id">Home</a>
&nbsp;|&nbsp;<a href="/en/private/common/index.php?m[0]=members&m[1]=new&module=EAP&parent_id=$id">Add Followers</a>
&nbsp;|&nbsp;<a href="/en/private/common/index.php?m[0]=members&m[1]=delete&module=EAP&parent_id=$id">Unsubscribe Followers</a>
EOF;
        $form = new tldReportColumnar(
            $eap->getFollowers(),
            [
                'xItems' => [
                    'id' => 'UID#',
                    'lastname' => 'Lastname',
                    'firstname' => 'Firstname',
                ],
                'title' => 'Followers',
                'links' => [
                    'id' => '/en/private/directory/index.php?m[0]=people&m[1]=view&id=',
                ],
            ]
        );
        $body = $form->fetch();
        break;
    case 'log':
        $DEFAULT_TITLE .= "\Log";
        $DEFAULT_MENU .= <<<EOF
&nbsp;&nbsp; <a href="$php_self?m[0]=eap&m[1]=view&m[2]=log&m[3]=add&id=$id">New Comment</a>
&nbsp;|&nbsp; <a href="$php_self?m[0]=eap&m[1]=view&m[2]=log&m[3]=add&m[4]=ap&id=$id">New Action Plan</a>
EOF;
        switch ($m[3]) {
            case 'add':
                switch ($m[4]) {
                    case 'ap':
                        $form = new HTML_QuickForm('frmAddComment', 'post');
                        $form->addElement('header', 'title', 'Add Action Plan');
                        $form->addElement('hidden', 'm[0]', 'eap');
                        $form->addElement('hidden', 'm[1]', 'view');
                        $form->addElement('hidden', 'm[2]', 'log');
                        $form->addElement('hidden', 'm[3]', 'add');
                        $form->addElement('hidden', 'm[4]', 'ap');
                        $form->addElement('hidden', 'id', $id);
                        $form->addElement('textarea', 'comment', 'Comment',
                            ['wrap' => 'VIRTUAL', 'cols' => '30', 'rows' => '4']);
                        $form->addElement('submit', 'btnSubmit', 'Submit');

                        if ($form->validate()) {
                            $a = $form->exportValues();
                            $vars = tldUtils::cleanupFormInput($a);
                            $e = $eap->addComment(['poster' => $user->getID(), 'comment' => $vars['comment'], 'log_num' => 1]);
                            if (!is_string($e)) {
                                $body .= 'Action plan successfully added!';
                            } else {
                                $DEFAULT_ERROR[] = "INTERNAL ERROR: Action plan not added!<br/>Reason: $e";
                            }
                        } else {
                            $body .= $form->toHTML();
                        }
                        break;
                    default:
                        $form = new HTML_QuickForm('frmAddComment', 'post');
                        $form->addElement('header', 'title', 'Add comment');
                        $form->addElement('hidden', 'm[0]', 'eap');
                        $form->addElement('hidden', 'm[1]', 'view');
                        $form->addElement('hidden', 'm[2]', 'log');
                        $form->addElement('hidden', 'm[3]', 'add');
                        $form->addElement('hidden', 'id', $id);
                        $form->addElement('textarea', 'comment', 'Comment',
                            ['wrap' => 'VIRTUAL', 'cols' => '30', 'rows' => '4']);
                        $form->addElement('submit', 'btnSubmit', 'Submit');

                        if (!$form->validate()) {
                            $body .= $form->toHTML();
                            break;
                        }

                        $a = $form->exportValues();
                        $vars = tldUtils::cleanupFormInput($a);
                        $e = $eap->addComment(['poster' => $user->getID(), 'comment' => $vars['comment']]);
                        if (!is_string($e)) {
                            $body .= 'Comment successfully added!';
                        } else {
                            $DEFAULT_ERROR[] = "INTERNAL ERROR: Comment not added!<br/>Reason: $e";
                        }
                        break;
                }
                break;
            default:
                $report = new tldReportColumnar($eap->getLog(),
                    ['xItems' => ['id' => 'ID#',
                        'date' => 'Date',
                        'poster_fullname' => 'Poster',
                        'comment' => 'Comment',],
                        'title' => 'Log Comments',
                    ]
                );
                $body .= $report->fetch();
                $report = new tldReportColumnar($eap->getLog(1),
                    ['xItems' => ['id' => 'ID#',
                        'date' => 'Date',
                        'poster_fullname' => 'Poster',
                        'comment' => 'Comment',],
                        'title' => 'Action Plan Log',
                    ]
                );
                $body .= $report->fetch();
                break;
        }
        break;
    case 'models':
        if (!empty($m[3]) && $m[3] === 'add') {
            $DEFAULT_TITLE .= "\Add a Model";

            $modelList = [];
            foreach (tldCatalogue::getTypeModelList() as $var) {
                $modelList[$var['model']] = $var['type'] . '->' . $var['model'];
            }
            $form = new HTML_QuickForm('frmAddModel', 'post');
            $form->addElement('header', 'title', 'Add a Model');
            $form->addElement('hidden', 'm[0]', 'eap');
            $form->addElement('hidden', 'm[1]', 'view');
            $form->addElement('hidden', 'm[2]', 'models');
            $form->addElement('hidden', 'm[3]', 'add');
            $form->addElement('hidden', 'id', $id);
            $form->addElement('select', 'model', null,
                ['OTHER' => 'Other / Discontinued'] + $modelList
            );
            $form->addElement('submit', 'btnSubmit', 'Submit');

            if ($form->validate()) {
                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $e = $eap->addModel($vars['model']);
                if (!is_string($e)) {
                    header("Location: /en/private/manufacturing/eng/dev.php?m[0]=eap&m[1]=view&id=$id");
                    exit;
                }
                $DEFAULT_ERROR[] = "ERROR: Problem adding EAP Model...<br/>Reason: $e";
            }
            $body = $form->toHTML();
            break;
        }

        $DEFAULT_TITLE .= "\Models";
        $report = new tldReportColumnar($eap->getModels(),
            ['xItems' => ['type' => 'Type',
                'model' => 'Model'],
                'title' => 'List of Affected Models',
            ]
        );
        $body .= $report->fetch();
        break;
    case 'files':
        $DEFAULT_MENU .= <<<EOF
	&nbsp;&nbsp;
	<a href="$php_self?m[0]=eap&m[1]=view&m[2]=files&m[3]=confirm&id=$id">Add New File</a>
EOF;
        switch ($m[3]) {
            case 'confirm':
                $p = [
                    'm' => ['0' => 'files', '1' => 'form', '2' => 'newFile'],
                    'module' => 'EAP',
                    'parent_id' => $id,
                ];
                $uri = '/en/private/common/index.php?' . http_build_query($p);
                if ($eap->isPrivate()) {
                    // Set confirmation form
                    $form = new HTML_QuickForm('frmConfirm', 'get', null, null, null, true);
                    $form->addElement('hidden', 'm[0]', 'eap');
                    $form->addElement('hidden', 'm[1]', 'view');
                    $form->addElement('hidden', 'm[2]', 'files');
                    $form->addElement('hidden', 'm[3]', 'confirm');
                    $form->addElement('hidden', 'id', $id);
                    $form->addElement('header', 'title', 'Confirm Adding File To Private EAP');
                    $form->addElement('static', null, null, 'This module is set to private!');
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
                        $eap->addComment(['poster' => $user->getID(), 'comment' => 'File upload to private EAP confirmed']);
                        header("Location: {$uri}");
                        exit;
                    }
                    $body .= $form->toHTML();
                } else {
                    header("Location: {$uri}");
                    exit;
                }
                break 2;
        }
        if ($user->isInGroup(['gg_ADMIN', 'gg_SUPERUSER', 'role_EM'])) {
            $URL = '/en/private/common/index.php?m[0]=files&m[1]=view&id=';
        } else {
            $URL = '/en/private/common/index.php?m[0]=files&m[1]=view&m[2]=out&id=';
        }

        global $kernel;
        try {
            $client = $kernel->getContainer()->get(Client::class);
        } catch (Exception $e) {
            $DEFAULT_ERROR[] = 'ERROR: Could not get API client. Reason: ' . $e->getMessage();
            return;
        }

        try {
            $ncrs = $client->findBy('quality/non_conformities', [
                'id' => array_column(tldModLink::byParent($eap->itsID, 'EAP', 'NCR'), 'item'),
                'normalizationGroups' => ['file', 'non_conformity:files']
            ]);
        } catch (Exception $e) {
            $DEFAULT_ERROR[] = 'ERROR: Could not get NCR. Reason: ' . $e->getMessage();
            break;
        }

        $files = [];
        foreach ($ncrs as $ncr) {
            foreach ($ncr->files as $file) {
                if (!$file['public']) {
                    continue;
                }

                $url = $kernel->getContainer()->get('router')->generate('non_conformity_files_show', [
                    'id' => $file['id'],
                    'nonConformityId' => $ncr->id,
                ]);

                $file['url'] = $url;
                $file['poster_fullname'] = sprintf('%s %s', $file['poster']['firstname'], $file['poster']['lastname']);
                $file['ncrId'] = $ncr->id;

                $files[] = $file;
            }
        }

        $DEFAULT_TITLE .= "\Files";
        $form = new tldReportColumnar($eap->getFiles(),
            ['xItems' => ['id' => 'ID#',
                'date' => 'Date',
                'poster_fullname' => 'Poster',
                'description' => 'Description',
                'filename' => 'Filename'],
                'title' => 'File list',
                'links' => ['id' => $URL],
            ]
        );
        $body .= $form->fetch();
        $form = new tldReportColumnar($eap->getFileByTasks('ALL'),
            ['xItems' => ['id' => 'ID#',
                'date' => 'Date',
                'poster_fullname' => 'Poster',
                'filename' => 'Filename'],
                'title' => 'File list in Tasks',
                'links' => ['filename' => '/en/private/uploads/tasks_comments/'],
            ]
        );
        $body .= $form->fetch();
        $bpFiles = [];
        $bpTaskFiles = [];
        foreach ($eap->getBPS('all') as $bp) {
            $bpFiles[] = (new tldBP($bp['id']))->getFiles();
            $bpTaskFiles[] = tldTask::byFileParent($bp['id'], 'BP', 'ALL');
        }
        $bpFiles = array_merge(...$bpFiles);
        $bpTaskFiles = array_merge(...$bpTaskFiles);
        $form = new tldReportColumnar($bpFiles,
            [
                'xItems' => [
                    'id' => 'ID#',
                    'date' => 'Date',
                    'poster_fullname' => 'Poster',
                    'description' => 'Description',
                    'filename' => 'Filename',
                ],
                'title' => 'File list in BP',
                'links' => ['id' => $URL],
            ]
        );
        $body .= $form->fetch();
        $form = new tldReportColumnar($bpTaskFiles,
            ['xItems' => ['id' => 'ID#',
                'date' => 'Date',
                'poster_fullname' => 'Poster',
                'filename' => 'Filename'],
                'title' => 'File list in BP Tasks',
                'links' => ['filename' => '/en/private/uploads/tasks_comments/'],
            ]
        );
        $body .= $form->fetch();

        $form = new tldReportColumnar($files, [
            'xItems' => [
                'ncrId' => 'ID#',
                'createdAt' => 'Date',
                'poster_fullname' => 'Poster',
                'description' => 'Description',
            ],
            'title' => 'File list in NCR',
            'links' => [
                'onKey' => 'ncrId',
                'urlKey' => 'url',
                'target' => '_blank',
            ],
        ]);
        $body .= $form->fetch();
        break;
    case 'pns':
        switch ($m[3]) {
            case 'add':
                $DEFAULT_TITLE .= "\Add a P/N";
                $form = new HTML_QuickForm('frmAddPN', 'post');
                $form->addElement('header', 'title', 'Add a P/N');
                $form->addElement('hidden', 'm[0]', 'eap');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('hidden', 'm[2]', 'pns');
                $form->addElement('hidden', 'm[3]', 'add');
                $form->addElement('hidden', 'id', $id);
                $form->addElement('text', 'pn', 'PN#');
                $form->applyFilter('pn', 'trim');
                $form->addRule('pn', 'Required', 'required');
                $form->addRule('pn', 'Part Number must be alphanumeric', 'regex', '/^[a-zA-Z0-9\-]*$/');
                $form->addElement('submit', 'btnSubmit', 'Submit');

                if ($form->validate()) {
                    $vars = tldUtils::cleanupFormInput($form->exportValues());
                    $e = $eap->addPart($vars['pn']);
                    if (is_string($e)) {
                        $DEFAULT_ERROR[] = "ERROR: Problem adding EAP Part...<br/>Reason: $e";
                        break;
                    }
                    header("Location: /en/private/manufacturing/eng/dev.php?m[0]=eap&m[1]=view&id=$id");
                    exit;
                }
                $body = $form->toHTML();
                break;
            default:
                $DEFAULT_TITLE .= "\PNs";

                $eaplist = [];
                foreach (getPartsList($eap) AS $k => $v) {
                    $eaplist[$k]['pn'] = $v['pn'];
                    $eaplist[$k]['description'] = $v['description'];
                    $eaplist[$k]['eapip'] = tldEAP::countByPartByStatus($id, $v['pn'], ['IN PROGRESS', 'IN QUEUE', 'PENDING']);
                    $eaplist[$k]['eapqty'] = tldEAP::countByPartByStatus($id, $v['pn']);
                    $eaplist[$k]['pdcqty'] = tldPDC::countByPartByStatus($v['pn']);
                }
                if ($date === null) {
                    $date = date('Y-m-d');
                }

                $functions = [
                    'EDM BOM' => [
                        'url' => "/en/private/manufacturing/eng/dev.php?m[0]=bom&m[1]=view&m[2]=edmpdf&erp={$eap->itsHeader['erp']}&date=$date&download&pn=",
                        'param' => 'pn',
                        'img' => '/shared/bluesphere/16x16/actions/filesaveas.png',
                    ],
                ];
                if($user->isInGroup(["ROLE_SEE", "ROLE_PLE", "ROLE_EM"])){
                    $functions['Remove PN'] = [
                        'url' => "/en/private/manufacturing/eng/dev.php?m[0]=eap&m[1]=pn&m[2]=remove&eap={$eap->itsID}&pn=",
                        'param' => [
                            'pn' => 'pn',
                        ],
                        'confirmPopup' => "Are you sure you want to remove this PN ?",
                        'img' => '/shared/icons/miscellaneous/delete.png',
                    ];
                }
                $report = new tldReportColumnar(
                    $eaplist,
                    [
                        'xItems' => [
                            'pn' => 'Part Number',
                            'description' => 'Description',
                            'eapip' => 'EAP IP',
                            'eapqty' => 'EAP',
                            'pdcqty' => 'PDC',
                        ],
                        'title' => 'List of Affected Part Numbers',
                        'links' => [
                            'pn' => "/en/private/manufacturing/eng/dev.php?m[0]=bom&m[1]=view&erp={$eap->itsHeader['erp']}&pn=",
                            'eapqty' => [
                                'url' => '/en/private/manufacturing/eng/dev.php?m[0]=eap&m[1]=mlList&m[2]=byPartByStatus',
                                'params' => [
                                    'pn' => 'pn',
                                ],
                            ],
                            'pdcqty' => [
                                'url' => '/en/private/product_support/index.ps.php?m[0]=pdc&m[1]=listing&m[2]=byPartNumber',
                                'params' => [
                                    'pn' => 'pn',
                                ],
                            ],
                            'eapip' => [
                                'url' => '/en/private/manufacturing/eng/dev.php?m[0]=eap&m[1]=mlList&m[2]=byPartByStatus&status[]=IN PROGRESS&status[]=IN QUEUE&status[]=PENDING',
                                'params' => [
                                    'pn' => 'pn',
                                ],
                            ],
                        ],
                        'functions' => $functions,
                        'showzero' => true,
                    ]
                );
                $body .= $report->fetch();
                break;
        }
        break;
    case 'links':
        $DEFAULT_MENU .= <<<EOF
	&nbsp;&nbsp;
	<a href="/en/private/common/index.php?m[0]=links&m[1]=form&m[2]=newLink&module=EAP&parent_id=$id">Add New Link</a>
	&nbsp;|&nbsp;<a href="/en/private/common/index.php?m[0]=links&m[1]=graphviz&mod=EAP&pid=$id">Map</a>
EOF;
        $report = new tldReportColumnar(
            tldModLink::byParent($id, 'EAP'),
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
        $report = new tldReportColumnar(
            tldModLink::byItem($id, 'EAP'),
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
        $body .= _viewBPSTab($eap);
        break;
    case 'tasks':
        $DEFAULT_TITLE .= "\Tasks";
        if (!in_array(strtoupper($eap->getStatus()), ['REJECTED', 'CLOSED', 'PENDING', 'IN QUEUE'], true)) {
            $DEFAULT_MENU .= <<<EOF
&nbsp;&nbsp;
<a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=form&m[2]=newTask&module=EAP&parent_id=$id">New Task</a>
EOF;
        }
        $sess['calendar']['tasks'] = $eap->getTasks('ALL');
        $form = new tldReportMultiLevel($sess['calendar']['tasks'],
            ['status', 'due_date'],
            ['id' => 'Task#',
                'status' => 'Status',
                'hours' => 'Estimated Time',
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
    case 'selectStatus':
        $allowed = $eap->changeStatus();
        if (empty($allowed)) {
            $DEFAULT_ERROR[] = 'WARNING: Cannot change status with EAP in current status:' . $eap->getStatus();
        } else {
            $form = new tldHTMLList($allowed,
                'list_item',
                "$php_self?m[0]=eap&m[1]=view&id=$id&m[2]=",
                ['title' => 'Please select new EAP status.']
            );
            $body .= $form->fetch();
        }
        break;
    case 'PROPOSED':
        $allowed = $eap->changeStatus();
        if (!array_key_exists('PROPOSED', $allowed)) {
            $DEFAULT_ERROR[] = 'ERROR: Changing to PROPOSED from current status is not allowed';
            break;
        }
        if (!$user->isInGroup(['eap', 'gg_mod_eap_admin', 'gg_ENG', 'gg_ADMIN'])) {
            $DEFAULT_ERROR[] = 'You do not have permission to change the status of EAPs';
            break;
        }
        /*		$approvers["role_MLM"] = array("text"=>"EAP #$id requires your approval.
        For parts where �Disposition code� shows:
        - B: Please indicate as comment Estimated Cost for stock modification or rework
        - C: Please confirm as comment, vendor agreement
        - D: Please indicate as comment Qty on hand and value
        - E: Please confirm as comment communication with Purchasing and Price

        Also confirm effectivity S/N or date.");
        */
        //list of what roles need to approve
        $approvers['role_PSM'] = ['text' => "EAP #$id requires your approval. See comment in the proposal Form."];
        $approvers['role_MLM'] = ['text' => "EAP #$id requires your approval.		
Suggested disposition action:
- Effective from /à appliquer à partir de l'OF ###
- Following depletion of existing stock / à appliquer après épuisement des stocks
- Following depletion of existing stock and orders in process / à appliquer après épuisement des stocks et des commandes en cours
- Immediate by modification of existing stock & orders / stocks et commandes en cours à modifier 

IF ACCEPTED, PLEASE INDICATE AS COMMENT THE EFFECTIVE DISPOSITION PLAN YOU INTEND TO PUT IN PLACE."];

        $approvers['role_PM'] = ['text' => "EAP #$id requires your approval.
See comment in the proposal Form."];
        $approvers['role_SPM'] = ['text' => "EAP #$id requires your approval.
See comment in the proposal Form."];
        $approvers['role_COO'] = ['text' => "EAP #$id requires your approval.
Proposed Cost impact is above the current limit in the factory
or
Proposed Modification require Compulsory SB"];
        $roles = array_keys($approvers);
        $default_text = $eap->itsHeader['description'];
        $eaplist = [];
        $defaultProposalSolution = '';
        foreach (getPartsList($eap) AS $part) {
            $defaultProposalSolution .= sprintf("%s %s\n", $part['pn'], $part['description']);
        }
        if (!empty($eap->itsHeader['action_plan'])) {
            $defaultProposalSolution .= <<<EOF
---
{$eap->itsHeader['action_plan']}
EOF;
        }
        $defaultActions = <<<EOF
ENGINEERING DEPT
Update BOMSTRUCT &Configurator
Update PIO question
Other BU to contact
Update manual
Update hydraulic/electric schematic

LOGISTIC DEPT
Model:
Option? YES NO
Rework inventory required (buyer to manage)? YES NO
How to rework parts:
NCR rework opened for parts on the shop floor # :
Update existing POs with new rev? YES NO
Cancel existing POs? YES NO
Prototype only? YES NO
PNs WITHOUT FAQ inspection:
Obsolete parts (PIPO):
    Old PNs &old QTY vs New PNs & New QTY
Scrap inventory : Suggested / Yes / No - PNs

PRODUCTION DEPT
Update Production w/ new assembly drawing
Update PIO question

PRODUCTION SUPPORT DEPT
Unit performance / spec sheet changed

SPARE PARTS DEPT
RSPL revision (PMOC)

SERVICE DEPT
Commissioning procedure changes?
Maintenance changes?

COST IMPACT
Cost analysis, Up or Down?
EOF;

        $form = new HTML_QuickForm('frm', 'post');
        $form->addElement('hidden', 'm[0]', 'eap');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'PROPOSED');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'eap_section', 'EAP Proposal');
        $form->addElement('textarea', 'long_desc', 'Proposal description',
            ['wrap' => 'VIRTUAL', 'cols' => '90', 'rows' => '24']
        );
        $form->addElement('textarea', 'proposal_solution', 'Proposal solution',
            ['wrap' => 'VIRTUAL', 'cols' => '90', 'rows' => '24']
        );
        $form->addElement('textarea', 'actions', 'Actions',
            ['wrap' => 'VIRTUAL', 'cols' => '90', 'rows' => '24']
        );
        $form->addElement('file', 'file', 'File');
        $form->addElement('checkbox', 'mating_parts', 'Are the mating parts affected ?', 'I Confirm That I checked the impact');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addElement('header', 'misc_header', 'Misc Section');
        $ams_others =& $form->addElement('advmultiselect', 'others', null,
            tldDirectory::getUserlist('smartyOptions'),
            ['size' => 10, 'class' => 'pool', 'style' => 'width:200px;']
        );
        $ams_others->setLabel(['Other users', '', 'Selected']);
        $ams_others->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
        $ams_others->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
        $form->addElement('textarea', 'others-text', 'Message',
            ['wrap' => 'VIRTUAL', 'cols' => '60', 'rows' => '8']
        );
        $form->setDefaults([
            'long_desc' => $default_text,
            'proposal_solution' => $defaultProposalSolution,
            'actions' => $defaultActions,
            'others-text' => "EAP #$id requires your approval. See comment in the proposal Form."
        ]);
        foreach ($roles as $role) {
            $grp = new tldGroup($role);
            $form->addElement('header', "$role_header", "$role Section (ctrl click to select multiple recipients)");
            $list = $grp->getUserlist(['smartyOptions' => true]);
            //keep the recipient field list nice and neat
            $size = count($list ?? []);
            if ($size > 10) {
                $size = 10;
            }

            $var = "ams_{$role}";
            $$var =& $form->addElement('advmultiselect', $role, null,
                $list,
                ['size' => $size, 'class' => 'pool', 'style' => 'width:200px;']
            );
            $$var->setLabel([$role, '', 'Selected']);
            $$var->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
            $$var->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
            $form->addElement('textarea', "$role-text", 'Message',
                ['wrap' => 'VIRTUAL', 'cols' => '60', 'rows' => '8']
            );
            $form->setDefaults(["$role-text" => $approvers[$role]['text']]);
        }
        $form->addElement('submit', 'btnSubmit', 'Submit');

        $form->addRule('long_desc', 'Description is required', 'required');
        $form->addRule('proposal_solution', 'Proposal solution is required', 'required');
        $form->addRule('actions', 'Actions is required', 'required');
        $form->addRule('mating_parts', 'Required', 'required');
        if ($form->validate()) {
            $vals = tldUtils::cleanupFormInput($form->exportValues());
            $file = $form->getElement('file');
            $vals['file_info'] = $file->getValue();
            $vals['owner'] = $user->getID();
            $vals['short_desc'] = "EAP#$id: Proposal";
            $bpid = tldBP::insert($id, $vals, 'EAP');
            if (!is_numeric($bpid)) {
                $DEFAULT_ERROR[] = "ERRROR: There was a problem creating the BP, returned error was $bpid";
                break;
            }
            //insert file
            if ($vals['file_info']['tmp_name']) {
                $fe = tldModFile::insert([
                    'module' => 'BP',
                    'parent_id' => $bpid,
                    'description' => "File attachment for BP# $bpid EAP Proposal",
                    'filename' => $vals['file_info']['name'],
                    'poster' => $user->getID(),
                ], $vals['file_info']);
                if (is_string($fe)) {
                    $DEFAULT_ERROR[] = "ERROR: Could not attach file to BP<br>Reason: $fe";
                }
            }
            $bp = new tldBP($bpid);
            $bp->addLogEntry($user->getID(), "BP CREATED for EAP#$id");
            $eap->addComment(['poster' => $user->getID(),
                'comment' => "Proposal BP#$bpid created"]);
            $link = <<<EOF
<a href="https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=bp&m[1]=view&id=$bpid">
See EAP #$id Proposal Online</a>
EOF;
            $assigneeExists = false;
            foreach ($roles as $role) {
                if (count($vals[$role] ?? [])) {
                    $assigneeExists = true;
                    foreach ($vals[$role] as $assignee) {
                        $task = ['assignee' => $assignee,
                            'assignor' => $user->getID(),
                            'task' => sprintf("%s\n\n%s", TldDatabase::escape($eap->itsHeader['short_desc']), $vals["$role-text"]),
                            'due_date' => ['value' => 14, 'unit' => 'DAY'],
                            'seq' => 'Y',
                            'tplno' => 0,
                            'bu_id' => $user->itsDetails['bu_id'],
                        ];
                        $taskid = $bp->addTask($task);
                        if (is_numeric($taskid)) {
                            $task = new tldTask($taskid);
                            $task->notifyAssignee($vals["$role-text"] .
                                $link,
                                "EAP #$id Proposal Review");
                        } else {
                            $DEFAULT_ERROR[] = "ERROR: There was a problem creating tasks for $role...";
                        }
                    }
                }
            }
            //process the others
            if (count($vals['others'] ?? [])) {
                $assigneeExists = true;
                foreach ($vals['others'] as $other) {
                    $task = ['assignee' => $other,
                        'assignor' => $user->getID(),
                        'task' => sprintf("%s\n\n%s", TldDatabase::escape($eap->itsHeader['short_desc']), $vals["others-text"]),
                        'due_date' => ['value' => 14, 'unit' => 'DAY'],
                        'seq' => 'Y',
                        'tplno' => 0,
                        'bu_id' => $user->itsDetails['bu_id'],
                    ];
                    $taskid = $bp->addTask($task);
                    if (is_numeric($taskid)) {
                        $task = new tldTask($taskid);
                        $task->notifyAssignee($vals['others-text'] .
                            $link,
                            "EAP #$id Proposal Review");
                    } else {
                        $DEFAULT_ERROR[] = 'ERROR: There was a problem creating tasks for Others...';
                    }
                }
            }
            //change the eap status
            if ($error = $eap->changeStatus('PROPOSED')) {
                $DEFAULT_ERROR[] = "There was a problem changing the status. Returned error was '$error'";
                break;
            }
            $eap->addComment(['poster' => $user->getID(),
                'comment' => 'PROPOSED']);
            if ($assigneeExists === false) {
                $bp->close();
                $eap->itsHeader['assigneeExists'] = $assigneeExists;
            }

            $message = <<<EOF
<a href="https://www.tld-gse.com/en/private/manufacturing/eng/dev.php?m[0]=eap&m[1]=view&id=$id">
Click here to see EAP #$id</a>
EOF;
            $eap->notifyInitiator($message, "EAP #$id PROPOSED", $eap->getCCList());
            $body .= _viewBPSTab($eap);
        } else {
            $body .= $form->toHTML();
        }
        break;
    case 'NOTIFICATION':
        $allowed = $eap->changeStatus();
        if (!array_key_exists('NOTIFICATION', $allowed)) {
            $DEFAULT_ERROR[] = 'ERROR: Changing to NOTIFICATION from current status is not allowed';
            break;
        }
        //check if there are any open BPs still that are not from a previous notification
        $bps = array_filter($eap->getBPS(), function ($bp) {
            return false === strpos($bp['short_desc'], 'Notification');
        });

        if ($eap->getStatus() !== 'NOTIFICATION' && count($bps ?? [])) {
            $DEFAULT_ERROR[] = 'ERROR: Can not perform NOTIFICATION with processes still pending.';
            break;
        }
        if (!$user->isInGroup(['eap', 'gg_mod_eap_admin', 'gg_ENG', 'gg_ADMIN'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permission to change the status of EAPs';
            break;
        }

        $PItext = <<<EOF
P&I updates, please create a task to MPE & QAM:

- Compulsory task for New options, prototypes and impacts on unit performances

- Optional task for updates and evolutions, or duplications of existing features.

EOF;

        //list of what roles need to approve
        $approvers['role_QAM'] = ['text' => $PItext];
        $approvers['role_PSM'] = ['text' => ''];
        $approvers['role_MLM'] = ['text' => ''];
        $approvers['role_PM'] = ['text' => ''];
        $approvers['role_COO'] = ['text' => ''];
        $approvers['role_SPM'] = ['text' => ''];
        $approvers['role_CSM'] = ['text' => ''];
        $roles = array_keys($approvers);

        $form = new HTML_QuickForm('frm', 'post');
        $form->addElement('hidden', 'm[0]', 'eap');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'NOTIFICATION');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'eap_section', 'EAP Notification');
        $form->addElement('textarea', 'long_desc', 'Solution description',
            ['wrap' => 'VIRTUAL', 'cols' => '90', 'rows' => '24']
        );
        $form->addElement('textarea', 'proposal_solution', 'Notification solution',
            ['wrap' => 'VIRTUAL', 'cols' => '90', 'rows' => '24']
        );
        $form->addElement('textarea', 'actions', 'Actions',
            ['wrap' => 'VIRTUAL', 'cols' => '90', 'rows' => '24']
        );
        $form->addElement('file', 'file', 'File');
        $form->addElement('text', 'currency', 'Currency');
        $form->addElement('text', 'cost', 'Cost');

        $form->addElement('checkbox', 'faq', 'Open a FAQ?');

        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addElement('header', 'misc_header', 'Misc Section');
        $ams_others =& $form->addElement('advmultiselect', 'others', null,
            tldDirectory::getUserlist('smartyOptions'),
            ['size' => 10, 'class' => 'pool', 'style' => 'width:200px;']
        );
        $ams_others->setLabel(['Other users', '', 'Selected']);
        $ams_others->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
        $ams_others->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
        $form->addElement('textarea', 'others-text', 'Message',
            ['wrap' => 'VIRTUAL', 'cols' => '60', 'rows' => '8']
        );

        $form->setDefaults([
            'others-text' => "EAP #{$eap->itsID} is notified to you. See above the actions assigned to you."
        ]);

        foreach ($roles as $role) {
            $grp = new tldGroup($role);
            $list = $grp->getUserlist(['smartyOptions' => true]);

            $header = "$role Section (shift click to select multiple recipients)";
            $label = [$role, '', 'Selected'];

            if ($role === 'role_QAM') {
                $header = "$role and MPE Section (shift click to select multiple recipients)";
                $label = ["$role and MPE", '', 'Selected'];
                $list += tldFunction::getUserlist('MPE', 'smartyOptions');
                asort($list);
            }
            $form->addElement('header', "$role_header", $header);
            //keep the recipient field list nice and neat
            $size = count($list ?? []);
            if ($size > 10) {
                $size = 10;
            }

            $var = "ams_{$role}";
            $$var =& $form->addElement('advmultiselect', $role, null,
                $list,
                ['size' => $size, 'class' => 'pool', 'style' => 'width:200px;']
            );
            $$var->setLabel($label);
            $$var->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
            $$var->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
            $form->addElement('textarea', "$role-text", 'Message',
                ['wrap' => 'VIRTUAL', 'cols' => '60', 'rows' => '8']
            );
            $DEFAULTS["$role-text"] = $approvers[$role]['text'];
        }
        $form->addElement('submit', 'btnSubmit', 'Submit');

        $form->addRule('long_desc', 'Description is required', 'required');
        $form->addRule('proposal_solution', 'Notification solution is required', 'required');
        $form->addRule('actions', 'Actions is required', 'required');

        //find last proposal
        $props = $eap->getBPS('ALL');
        if (count($props ?? [])) {
            foreach ($props as $prop) {
                if (substr_count($prop['short_desc'], 'Proposal')) {
                    $DEFAULTS['long_desc'] = $prop['long_desc'];
                    $DEFAULTS['proposal_solution'] = $prop['proposal_solution'];
                    $DEFAULTS['actions'] = $prop['actions'];
                    break;
                }
            }
        }
        $form->setDefaults($DEFAULTS);
        if ($form->validate()) {
            $vals = tldUtils::cleanupFormInput($form->exportValues());
            $file = $form->getElement('file');
            $vals['file_info'] = $file->getValue();
            $vals['owner'] = $user->getID();
            $vals['short_desc'] = "EAP#$id: Notification";
            $bpid = tldBP::insert($id, $vals, 'EAP');
            if (!is_numeric($bpid)) {
                $DEFAULT_ERROR[] = "ERRROR: There was a problem creating the BP, returned error was $bpid";
                break;
            }
            //insert file
            if ($vals['file_info']['tmp_name']) {
                $fe = tldModFile::insert([
                    'module' => 'BP',
                    'parent_id' => $bpid,
                    'description' => "File attachment for BP# $bpid EAP Proposal",
                    'filename' => $vals['file_info']['name'],
                    'poster' => $user->getID(),
                ], $vals['file_info']);
                if (is_string($fe)) {
                    $DEFAULT_ERROR[] = "ERROR: Could not attach file to BP<br>Reason: $fe";
                }
            }
            $bp = new tldBP($bpid);
            $bp->addLogEntry($user->getID(), "BP CREATED for EAP#$id");
            $eap->addComment(['poster' => $user->getID(),
                'comment' => "Proposal BP#$bpid created"]);
            $link = <<<EOF
<a href="https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=bp&m[1]=view&id=$bpid">See EAP #$id Notification  Online</a>
EOF;
            $assigneeExists = false;
            foreach ($roles as $role) {
                if (count($vals[$role] ?? [])) {
                    $assigneeExists = true;
                    foreach ($vals[$role] as $assignee) {
                        $task = ['assignee' => $assignee,
                            'assignor' => $user->getID(),
                            'task' => sprintf("%s\n\n%s", $eap->itsHeader['short_desc'], $vals["$role-text"]),
                            'due_date' => ['value' => 14, 'unit' => 'DAY'],
                            'seq' => 'Y',
                            'tplno' => 0,
                            'bu_id' => $user->itsDetails['bu_id'],
                        ];
                        $taskid = $bp->addTask($task);
                        if (is_numeric($taskid)) {
                            $task = new tldTask($taskid);
                            $task->notifyAssignee('EAP notification requires your review<br>' .
                                $vals["$role-text"] .
                                $link,
                                "EAP#$id notification Review");
                        }
                    }
                }
            }
            if (count($vals['others'] ?? [])) {
                $assigneeExists = true;
                foreach ($vals['others'] as $other) {
                    $task = ['assignee' => $other,
                        'assignor' => $user->getID(),
                        'task' => sprintf("%s\n\n%s", $eap->itsHeader['short_desc'], $vals['others-text']),
                        'due_date' => ['value' => 14, 'unit' => 'DAY'],
                        'seq' => 'Y',
                        'tplno' => 0,
                        'bu_id' => $user->itsDetails['bu_id'],
                    ];
                    $taskid = $bp->addTask($task);
                    if (is_numeric($taskid)) {
                        $task = new tldTask($taskid);
                        $task->notifyAssignee('EAP notification requires your review<br>' .
                            $vals['others-text'] .
                            $link,
                            "EAP#$id notification Review");
                    }
                }
            }
            //change the eap status
            if ($error = $eap->changeStatus('NOTIFICATION')) {
                $DEFAULT_ERROR[] = "There was a problem changing the status. Returned error was '$error'";
                break;
            }
            $eap->addComment(['poster' => $user->getID(),
                'comment' => 'NOTIFICATION']);
            if ($assigneeExists === false) {
                $bp->close();
                $eap->itsHeader['assigneeExists'] = $assigneeExists;
            }

            $message = <<<EOF
<a href="https://www.tld-gse.com/en/private/manufacturing/eng/dev.php?m[0]=eap&m[1]=view&id=$id">
Click here to see EAP #$id</a>
EOF;
            $eap->notifyInitiator($message, "EAP #$id NOTIFICATION", $eap->getCCList());

            if ((bool)$vals['faq'] === true) {
                global $kernel;
                $container = $kernel->getContainer();

                $partsList = $eap->getPartsList();
                $url = $container->get('router')->generate('first_article_qualifications_add', [
                    'eap' => $id,
                    'meap' => $eap->getParentModule() === 'MEAP' ? $eap->getParentID() : null,
                    'erp' => $eap->getERP(),
                ]);

                header("Location: $url");
                return;
            }

            $body .= _viewBPSTab($eap);
        } else {
            $body .= $form->toHTML();
        }
        break;
    case 'IN PROGRESS':
        if (!$user->isInGroup(['eap', 'gg_mod_eap_admin', 'gg_ENG', 'gg_ADMIN'])) {
            $DEFAULT_ERROR[] = 'You do not have permission to change the status of EAPs';
            break;
        }
        if ($error = $eap->changeStatus('IN PROGRESS')) {
            $DEFAULT_ERROR[] = "There was a problem changing the status. Returned error was '$error'";
            break;
        }
        $eap->addComment(['poster' => $user->getID(),
            'comment' => 'IN PROGRESS']);
        $message = <<<EOF
<a href="https://www.tld-gse.com/en/private/manufacturing/eng/dev.php?m[0]=eap&m[1]=view&id=$id">
Click here to see EAP #$id</a>
EOF;
        $eap->notifyInitiator($message, "EAP #$id IN PROGRESS", $eap->getCCList());
        $body .= _viewGeneralTab($eap);
        break;
    case 'IN QUEUE':
        if (!$user->isInGroup(['eap', 'gg_mod_eap_admin', 'gg_ENG', 'gg_ADMIN'])) {
            $DEFAULT_ERROR[] = 'You do not have permission to change the status of EAPs';
            break;
        }
        if ($error = $eap->changeStatus('IN QUEUE')) {
            $DEFAULT_ERROR[] = "There was a problem changing the status. Returned error was '$error'";
            break;
        }
        $eap->addComment(['poster' => $user->getID(), 'comment' => 'IN QUEUE']);
        $message = <<<EOF
<a href="https://www.tld-gse.com/en/private/manufacturing/eng/dev.php?m[0]=eap&m[1]=view&id=$id">
Click here to see EAP #$id</a>
EOF;
        $eap->notifyInitiator($message, "EAP #$id IN QUEUE", $eap->getCCList());
        $body .= _viewGeneralTab($eap);
        break;
    case 'REJECTED':
        if (!$user->isInGroup(['eap', 'gg_mod_eap_admin', 'gg_ENG', 'gg_ADMIN'])) {
            $DEFAULT_ERROR[] = 'You do not have permission to change the status of EAPs';
            break;
        }
        if (!$user->isInGroup(['role_EM'])) {
            $DEFAULT_ERROR[] = 'Only EM can reject an EAP';
            break;
        }
        $form = new HTML_QuickForm('frmREJECTED', 'post');
        $form->addElement('hidden', 'm[0]', 'eap');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'REJECTED');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'close', 'REJECTED');
        $form->addElement('textarea', 'comment', 'Comment',
            ['wrap' => 'VIRTUAL', 'cols' => '60', 'rows' => '8']
        );
        $form->addElement('submit', 'btnSubmit', 'Submit');

        if ($form->validate()) {
            $vals = tldUtils::cleanupFormInput($form->exportValues());
            if ($error = $eap->changeStatus('REJECTED')) {
                $DEFAULT_ERROR[] = "There was a problem changing the status. Returned error was '$error'";
                break;
            }
            $eap->addComment(['poster' => $user->getID(),
                'comment' => "REJECTED\n" . $vals['comment']]);
            $message = <<<EOF
<a href="https://www.tld-gse.com/en/private/manufacturing/eng/dev.php?m[0]=eap&m[1]=view&id=$id">
Click here to see EAP #$id</a>
EOF;
            $eap->notifyInitiator($message, "EAP #$id was REJECTED", $eap->getCCList());
            $body .= _viewGeneralTab($eap);
        } else {
            $body .= $form->toHTML();
        }
        break;
    case 'CLOSED':
        if ($eap->getStatus() !== 'NOTIFICATION') {
            $DEFAULT_ERROR[] = 'ERROR: Can not CLOSE EAP unless at NOTIFICATION status';
            break;
        }
        if (!$user->isInGroup(['eap', 'gg_mod_eap_admin', 'gg_ENG', 'gg_ADMIN'])) {
            $DEFAULT_ERROR[] = 'You do not have permission to change the status of EAPs';
            break;
        }
        //check if there are any open BPs still
        if (count($eap->getBPS())) {
            $DEFAULT_ERROR[] = 'ERROR: Can not CLOSE EAP with Process(es) still pending.';
            break;
        }
        // Check if there are any opened tasks
        if (count($eap->getTasks())) {
            $DEFAULT_ERROR[] = 'ERROR: Can not CLOSE EAP with Task(s) still pending.';
            break;
        }

        $form = new HTML_QuickForm('frmCLOSED', 'post');
        $form->addElement('hidden', 'm[0]', 'eap');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'CLOSED');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'close', 'Close');
        $form->addElement('textarea', 'comment', 'Comment',
            ['wrap' => 'VIRTUAL', 'cols' => '90', 'rows' => '24']
        );
        $form->addElement('submit', 'btnSubmit', 'Submit');
        //find last propsal
        $props = $eap->getBPS('ALL');
        if (count($props)) {
            foreach ($props as $prop) {
                if (substr_count($prop['short_desc'], 'Notification')) {
                    $DEFAULTS['comment'] = $prop['long_desc'];
                    break;
                }
            }
        }
        $form->setDefaults($DEFAULTS);
        if ($form->validate()) {
            $vals = tldUtils::cleanupFormInput($form->exportValues());
            $error = $eap->changeStatus('CLOSED');
            if ($error) {
                $DEFAULT_ERROR[] = "There was a problem changing the status. Returned error was '$error'";
                break;
            }
            $eap->addComment(['poster' => $user->getID(),
                'comment' => "CLOSED\n" . $vals['comment']]);
            $message = <<<EOF
<a href="https://www.tld-gse.com/en/private/manufacturing/eng/dev.php?m[0]=eap&m[1]=view&id=$id">
Click here to see EAP #$id</a>
EOF;
            $eap->notifyInitiator($message, "EAP #$id CLOSED", $eap->getCCList());
            $body .= _viewGeneralTab($eap);
        } else {
            $body .= $form->toHTML();
        }
        break;
    default:
        $DEFAULT_MENU .= <<<EOF
<a href="/en/private/manufacturing/eng/dev.php?m[0]=eap&m[1]=forms&m[2]=newEAP&from[mod]=EAP&from[id]=$id">Create &amp; Link New EAP</a>
EOF;
        $form = new HTML_QuickForm('frm', 'get', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'eap');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'single', '1');
        $form->addElement('header', 'title', 'View EAP by Number');
        $form->addElement('text', 'id', 'EAP#');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $body .= $form->toHTML();
        $body .= _viewGeneralTab($eap);
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
    global $eap, $php_self;

    $html = '';
    $isFirst = false;
    if (!isset($tree)) {
        // First run through, get array
        $tree = $eap->getFamilyTree();
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
</style>
HTML;
    }
    $children = array_filter($tree, static function ($var) use ($parentId, $parentModule) {
        return ((int) $var['parent_id'] === (int) $parentId && $var['parent_module'] ===  $parentModule);
    });
    $html .= '<ul' . ($isFirst ? ' class="tree"' : '') . ">\n";
    foreach ($children AS $child) {
        $style = $child['module'] === 'EAP' && $child['id'] == $eap->getID() ? ' style="background-color:#dedede;"' : '';
        $linkStyle = in_array($child['data']['status'], ['CLOSED', 'REJECTED'], true) ?  ' style="color: #7D7878"': '';
        $url = "$php_self?m[0]=" . strtolower($child['module']) . "&m[1]=view&id={$child['id']}";
        $html .= "<li{$style}><a href=\"{$url}\"{$linkStyle}>{$child['module']} #{$child['id']} - IF  {$child['data']['ifactor']} : {$child['data']['short_desc']}</a></li>\n";
        $html .= _drawHierarchyTree($tree, $child['id'], $child['module']);
    }
    $html .= "</ul>\n";

    return $html;
}
