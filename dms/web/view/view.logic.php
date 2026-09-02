<?php

declare(strict_types=1);
if (empty($id) || !is_numeric($id)) {
    $_ERROR[] = _('Parameters sent empty or invalid');

    return;
}
$dms = new tldDMS($id);
if ($dms->isEmpty()) {
    $_ERROR[] = _('DMS not found');

    return;
}
$header = $dms->itsHeader;

$_TITLE .= " \ "._('View')." DMS#$id";

// /////////////////////////////////
// Check ACL access for this DMS //
// /////////////////////////////////
$flagACL = false;
$msgACL = _('You do not have permissions to access this DMS');
switch ($header['portal']) {
    case 'INTRANET':
        switch ($header['access_type']) {
            case 'CONFIDENTIAL':
                // BLOCK if not even connected
                if (!_isUserLoggedIn()) {
                    $msgACL = _('You must login with your intranet account to view this DMS');
                    break;
                }
                // GRANT if admin or owner
                if (_isOwner()) {
                    $flagACL = true;
                    break;
                }
                // GRANT if QAM of the owner BU (Task#452440)
                $owner = new tldUser($dms->getOwnerID());
                $erp = tldLocation::getERPByID($owner->getBUID());
                if ($user->isInGroupLevel('role_QAM', $erp)) {
                    $flagACL = true;
                    break;
                }
                // GRANT if dms ACL ok
                $allowedUserList = tldUtils::optionsByKeyValue($dms->getAllowedUserList(), 'id', 'id');
                if (in_array($user->getID(), $allowedUserList, true)) {
                    $flagACL = true;
                    break;
                }
                break;
            case 'PUBLIC':
                $flagACL = true;
                break;
                // If ACCESS TYPE not set
            default:
                if (_isOwner()) {
                    $flagACL = true;
                }
                break;
        }
        break;
    case 'EXTRANET':
    case 'EVENDOR':
        $flagACL = true;
        break;
        // If PORTAL not set
    default:
        if (_isOwner()) {
            $flagACL = true;
        }
        break;
}
// Finnaly decide access
if (!$flagACL) {
    $_ERROR[] = $msgACL;

    return;
}
// ///////////////////////////////

$_MENU .= "
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href=\"$php_self?m[0]=view&id=$id\">"._('Home')."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=coverage&id=$id\">"._('Coverage')."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=revision&id=$id\">"._('Revisions')."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=status&id=$id\">"._('Status')."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=logs&id=$id\">"._('Logs')."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=tasks&id=$id\">"._('Tasks')."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=links&id=$id\">"._('Links')."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=hierarchy&id=$id\">"._('Hierarchy')."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=approver&id=$id\">"._('Approvers')."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=acl&id=$id\">"._('Restrictions')."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=notification&id=$id\">"._('Notification')."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=edit&id=$id\">"._('Edit')."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=transferOwner&id=$id\">"._('Transfer owner')."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=delete&id=$id\">"._('Delete').'</a>&nbsp;|&nbsp;
';

if (_isUserLoggedIn() && _isAdmin()) {
    $_MENU .= "
&nbsp;|&nbsp;<a href=\"/admin/dms_admin.php?mode=record_view&form_type=main_tpl&id=$id\">"._('Admin').'</a>
';
}

switch ($m[1] ?? null) {
    case 'status':
    case 'acl':
    case 'approver':
    case 'revision':
    case 'coverage':
    case 'hierarchy':
    case 'notification':
        $_PATH .= "/{$m[1]}";
        include "$_PATH/{$m[1]}.logic.php";
        break;
    case 'tasks':
        $_TITLE .= " \ "._('Tasks');
        $_MENU .= "
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=tasks&id=$id\">"._('Home')."</a>&nbsp;|&nbsp;
<a href=\"http://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=form&m[2]=newTask&module=DMS&parent_id=$id\">"._('Add Task').'</a>
';

        $dmsTasksComments = array_reduce($dms->getTasks(), static function ($memo, $task) {
            foreach ($task['comments'] as $comment) {
                if ('' !== $comment['filename']) {
                    $memo[] = [
                        'parent_id' => $comment['parent_id'],
                        'filename' => $comment['filename'],
                    ];
                }
            }

            return $memo;
        }, []);

        $openedTasks = array_filter($dms->getTasks(), static function ($task) {
            return 'OPEN' === $task['status'];
        });
        $closedTasks = array_filter($dms->getTasks(), static function ($task) {
            return 'CLOSED' === $task['status'];
        });

        $reportOpenTasks = new tldReportColumnar(
            $openedTasks,
            [
                'xItems' => [
                    'id' => _('Task#'),
                    'status' => _('Status'),
                    'due_date' => _('Due'),
                    'task' => _('Task'),
                    'assignee_fullname' => _('Assignee'),
                ],
                'title' => _('DMS Tasks (OPENED)'),
                'links' => [
                    'id' => [
                        'url' => 'http://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=',
                        'target' => '_blank',
                        'params' => ['id' => 'id'],
                    ],
                ],
            ]
        );

        $reportClosedTasks = new tldReportColumnar(
            $closedTasks,
            [
                'xItems' => [
                    'id' => _('Task#'),
                    'status' => _('Status'),
                    'due_date' => _('Due'),
                    'task' => _('Task'),
                    'assignee_fullname' => _('Assignee'),
                ],
                'title' => _('DMS Tasks (CLOSED)'),
                'links' => [
                    'id' => [
                        'url' => 'http://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=',
                        'target' => '_blank',
                        'params' => ['id' => 'id'],
                    ],
                ],
            ]
        );

        $filesReport = new tldReportColumnar(
            $dmsTasksComments,
            [
                'xItems' => [
                    'parent_id' => _('Task#'),
                    'filename' => _('Filename'),
                ],
                'title' => _('DMS Files by Tasks'),
                'links' => ['filename' => 'https://www.tld-gse.com/en/private/uploads/tasks_comments/'],
            ]
        );

        $_BODY .= $reportOpenTasks->fetch();
        $_BODY .= $reportClosedTasks->fetch();
        $_BODY .= $filesReport->fetch();
        break;
    case 'links':
        $_TITLE .= " \ "._('Links');
        $_MENU .= "
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=links&id=$id\">"._('Home')."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=links&m[2]=add&id=$id\">"._('Add links').'</a>
';

        switch ($m[2] ?? null) {
            case 'add':
                $_TITLE .= " \ "._('Add');
                // Check access
                if (!_isOwner()) {
                    $_ERROR[] = _('You do not have permissions to use this feature');
                    break;
                }
                $form = new HTML_QuickForm('add', 'post');
                $form->addElement('hidden', 'm[0]', 'view');
                $form->addElement('hidden', 'm[1]', 'links');
                $form->addElement('hidden', 'm[2]', 'add');
                $form->addElement('hidden', 'id', $id);
                $form->addElement('header', 'frmTitle', sprintf(_('Link DMS#%s with other DMS'), $id));
                $form->addElement('text', 'dms_id', _('DMS#'));
                $form->addRule('dms_id', _('Field is required'), 'required');
                $form->addRule('dms_id', _('Field is numeric'), 'numeric');
                $form->addElement('submit', 'btnSubmit', _('Submit'));

                if (!$form->validate()) {
                    $_BODY = $form->toHTML();
                    break;
                }

                $vars = tldUtils::cleanupFormInput($form->exportValues());
                // Check parent DMS
                if (!is_numeric($vars['dms_id']) || 0 == $vars['dms_id']) {
                    $_ERROR[] = _('Parameters sent invalid');
                    break;
                }
                $dmsLinked = new tldDMS($vars['dms_id']);
                if ($dmsLinked->isEmpty()) {
                    $_ERROR[] = sprintf(_('DMS#%s not found'), $vars['dms_id']);
                    break;
                }
                // Create the link
                $e = $dms->addLink('DMS', $dmsLinked->getID());
                if (is_string($e)) {
                    $_ERROR[] = _('DMS not linked').'<br>'._('Reason').": $e";
                    break;
                }
                // Log
                _addLog($user->getID(), 'DMS#'.$dmsLinked->getID().' linked');
                $_CONF[] = sprintf(_('DMS#%s successfully linked'), $vars['dms_id']);
                break;
            case 'unlink':
                // Check access
                if (!_isOwner()) {
                    $_ERROR[] = _('You do not have permissions to use this feature');
                    break;
                }
                // do checks
                if (empty($lid) || !is_numeric($lid)) {
                    $_ERROR[] = _('Parameters sent invalid or empty');
                    break;
                }
                $link = new tldModLink($lid);
                if ($link->isEmpty()) {
                    $_ERROR[] = _('DMS link not found');
                    break;
                }
                if ($link->itsHeader['parent_id'] != $dms->getID() || 'DMS' != $link->itsHeader['module']) {
                    $_ERROR[] = sprintf(_('DMS link not attached to DMS#%s'), $dms->getID());
                    break;
                }
                // if ok, delete the link
                $e = tldModLink::delete($lid);
                if (is_string($e)) {
                    $_ERROR[] = _('DMS not unlinked').'<br>'._('Reason').": $e";
                    break;
                }
                // Log
                _addLog($user->getID(), "DMS#$lid unlinked");
                $_CONF[] = sprintf(_('DMS#%s successfully unlinked'), $link->itsHeader['item']);
                break;
        }

        $report = new tldReportColumnar(
            $dms->getLinks(),
            [
                'xItems' => [
                    'type' => _('Module'),
                    'item' => _('DMS#'),
                    'dsca' => _('Description'),
                    'id' => _('Unlink'),
                ],
                'title' => _('DMS Links'),
                'links' => [
                    'id' => "$php_self?m[0]=view&m[1]=links&m[2]=unlink&id=$id&lid=",
                    'item' => "$php_self?m[0]=view&id=",
                ],
            ]
        );
        $_BODY .= $report->fetch();
        break;
    case 'logs':
        $report = new tldReportColumnar(
            $dms->getLog(),
            [
                'xItems' => [
                    'id' => 'ID#',
                    'date' => _('Date'),
                    'poster_fullname' => _('Poster'),
                    'comment' => _('Comment'),
                ],
            ]
        );
        $_BODY = $report->fetch();
        break;
    case 'edit':
        $_TITLE .= " \ "._('Edit');
        // Check access
        if (!_isOwner()) {
            $_ERROR[] = _('You do not have permissions to use this feature');
            break;
        }
        // Check status
        if (!_isInRevision()) {
            $_ERROR[] = _('Can not update if DMS status is not in REVISION');
            break;
        }
        // Get listing
        $peopleList = tldDirectory::getUserlist('smartyOptions');
        $typeList = tldUtils::optionsByKeyValue(tldDMSType::getList(), 'id', 'short_desc');
        $langList = tldDMS::getLangList();
        $systList = tldDMS::getSystemRefList();
        $periodicityList = tldDMS::getPeriodicityList();
        $yesNoList = ['' => '', 'Y' => 'Yes', 'N' => 'No'];
        // Get form
        $form = new HTML_QuickForm('add', 'post');
        $form->addElement('hidden', 'm[0]', 'view');
        $form->addElement('hidden', 'm[1]', 'edit');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'frmTitle', _('Edit')." DMS#$id");
        $form->addElement('text', 'title', _('Title'));
        $form->addElement('text', 'subject', _('Subject').'<br><em>('.
            _('Could be the name of a previously not DMS controlled document').')</em>');
        $form->addElement('textarea', 'description', _('Description'),
            ['wrap' => 'VIRTUAL', 'cols' => '60', 'rows' => '8']);
        $form->addElement('select', 'owner_id', _('Owner'), ['' => ''] + $peopleList);
        $form->addElement('select', 'lang', _('Language'), ['' => ''] + $langList);
        $form->addElement('select', 'type_id', _('Type'), ['' => ''] + $typeList,
            ['onChange' => '
            javascript:$.ajax({
                type:"POST",
                url: "ajax.php?m[0]=getPeriodicityByDmsTypeID",
                data:"id="+$(this).val(),
                success: function(data){
                    $(\'#periodicity\').val(data);
                }
            });',
            ]
        );
        $form->addElement('select', 'periodicity', _('Revision periodicity').'<br>'._('(in month)'),
            $periodicityList,
            ['id' => 'periodicity']
        );
        $form->addElement('select', 'sysref', _('System REF'), ['' => ''] + $systList);
        // Apply rules
        $fieldsRequired = [
            'title', 'subject', 'description', 'owner_id',
            'periodicity', 'lang', 'type_id', 'sysref',
        ];
        foreach ($fieldsRequired as $field) {
            $form->addRule($field, _('Field is required'), 'required');
        }
        // Set Defaults
        $form->setDefaults($dms->itsHeader);
        // Buttons
        $form->addElement('reset', 'btnReset', _('Reset'));
        $form->addElement('submit', 'btnSubmit', _('Submit'));

        if (!$form->validate()) {
            $_BODY = $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $fieldsToUpdate = [
            'title' => 'Title',
            'subject' => 'Subject',
            'description' => 'Description',
            'owner_id' => 'Owner',
            'lang' => 'Language',
            'type_id' => 'Type',
            'sysref' => 'System REF',
            'periodicity' => 'Revision periodicity',
        ];
        $e = $dms->update($vars, array_keys($fieldsToUpdate));
        if (is_string($e)) {
            $_ERROR[] = _('DMS not updated').'<br>'._('Reason').": $e";
            break;
        }
        $_CONF[] = _('DMS successfully updated');
        // Prepare logs
        $flagLog = false;
        $log = 'DMS updated:<br><ul>';
        $header = tldUtils::cleanupFormInput($header);
        foreach ($fieldsToUpdate as $field => $label) {
            // if not change of the field, go to next one
            if ($header[$field] == $vars[$field]) {
                continue;
            }
            // else log
            switch ($field) {
                case 'type_id':
                    $from = $header['typeDesc'];
                    $to = $typeList[$vars[$field]];
                    break;
                case 'owner_id':
                    $from = $header['ownerFullname'];
                    $to = $peopleList[$vars[$field]];
                    break;
                default:
                    $from = $header[$field];
                    $to = $vars[$field];
                    break;
            }
            $log .= "<li>$label <strong>FROM</strong> \'$from\' <strong>TO</strong> \'$to\'</li>";
            $flagLog = true;
        }
        $log .= '</ul>';
        // Log
        if ($flagLog) {
            _addLog($user->getID(), $log);
        }
        // Refresh and view
        $dms->refresh();
        $_BODY = _getGeneralView();
        break;
    case 'transferOwner':
        $_TITLE .= " \ "._('Transfer owner');
        // look for access
        $access = false;
        $owner = new tldUser($dms->getOwnerID());
        $ownerSupervisorID = $owner->getSupervisor();
        if (_isOwner()) {
            $access = true;
        } elseif ($ownerSupervisorID == $user->getID()) {
            $access = true;
        } elseif ($user->isInGroup(['role_QAM']) && 'ISO:9001' == $dms->getSystemRef()) {
            $access = true;
        }
        // Check access
        if (!$access) {
            $_ERROR[] = _('You do not have permissions to use this feature');
            break;
        }
        // Get listing
        $peopleList = tldDirectory::getUserlist('smartyOptions');
        // Get form
        $form = new HTML_QuickForm('add', 'post');
        $form->addElement('hidden', 'm[0]', 'view');
        $form->addElement('hidden', 'm[1]', 'transferOwner');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'frmTitle', _('Transfer owner'));
        $form->addElement('select', 'owner_id', _('To'), ['' => ''] + $peopleList);
        $form->addRule($field ?? null, _('Field is required'), 'required');
        $form->addElement('submit', 'btnSubmit', _('Submit'));

        if (!$form->validate()) {
            $_BODY = $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $e = $dms->update($vars, ['owner_id']);
        if (is_string($e)) {
            $_ERROR[] = _('DMS not updated').'<br>'._('Reason').": $e";
            break;
        }
        $_CONF[] = _('DMS successfully updated');
        // log
        $ownerNew = new tldUser($vars['owner_id']);
        $log = <<<EOF
DMS updated:<br><ul>
<li>Owner <strong>FROM</strong> {$owner->getFullname()} <strong>TO</strong> {$ownerNew->getFullname()}</li>
</ul>
EOF;
        _addLog($user->getID(), $log);
        $body = <<<EOF
<p>The ownership of the DMS <b>{$dms->getTitle()}</b> has been transfered from <b>{$owner->getFullname()} </b> to <b>{$ownerNew->getFullname()}</b>.</p>
<hr>
EOF;
        // Refresh and view
        $dms->refresh();
        $dms->notifyOwner('DMS#'.$dms->getID().', transfer of ownership', $body);
        $_BODY = _getGeneralView();
        break;
    case 'delete':
        $_TITLE .= " \ "._('Delete');
        // Check access
        $access = false;
        if (!_isAdmin() && !_isQAM()) {
            $_ERROR[] = _('You do not have permissions to use this feature');
            break;
        }
        // Form confirmation
        $form = new HTML_QuickForm('runScript', 'post');
        $form->addElement('hidden', 'm[0]', 'view');
        $form->addElement('hidden', 'm[1]', 'delete');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', _('Are you sure to delete?'));
        $form->addElement('header', 'title2', _('The owner will be notified'));
        $form->addElement('select', 'conf', _('Confirm?'), ['' => '', 'Y' => _('Yes')]);
        $form->addRule('conf', _('Field is required'), 'required');
        $form->addElement('submit', 'btnSubmit', _('Submit'));

        if (!$form->validate()) {
            $_BODY = $form->toHTML();
            break;
        }

        // Delete
        $e = $dms->delete();
        if (is_string($e)) {
            $_ERROR[] = _('DMS deletation error:').'<br>'._('Reason').": $e";
            break;
        }
        // Notify owner
        $body = <<<'EOF'
<p>This notification has been sent to you because you are the owner of the DMS below.</p>
<p>This DMS is now deleted</p>
EOF;
        $dms->notifyOwner('Deleted by '.$user->getFullname(), $body);
        $_BODY .= _('Owner notified');
        $_CONF[] = _('Deletation process completed');
        break;
    case 'getActivePubFile':
        try {
            $file = $dms->getActiveRevisionFile();
            $extension = $file->getExtension();
            $file->download(sprintf('DMS#%s_%s.%s', $dms->itsHeader['id'], $dms->itsHeader['title'], $extension), 'CONFIDENTIAL' === $dms->itsHeader['access_type']);
            exit;
        } catch (Exception $e) {
            $_ERROR[] = _('DMS file download error:').' '.$e->getMessage();
            break;
        }
        break;
    default:
        $_BODY = _getGeneralView();
        break;
}

function _getGeneralView()
{
    global $dms,$php_self;
    // Status bar
    $body = include 'view.statusbar.tpl.php';
    // General tab
    $report = new tldAssocTable(
        $dms->itsHeader,
        [
            'id' => _('DMS#'),
            'parent_id' => _('Parent').' DMS#',
            'dt' => _('Date creation'),
            'dt_act' => _('Last activation'),
            'ownerFullname' => _('Owner'),
            'title' => _('Title'),
            'subject' => _('Subject'),
            'description' => _('Description'),
            'lang' => _('Language'),
            'status' => _('Status'),
            'typeDesc' => _('Type'),
            'periodicity' => _('Revision periodicity'),
            'sysref' => _('System reference'),
            'revision' => _('Revision'),
        ],
        [
            'title' => _('General information'),
            'links' => [
                'parent_id' => "$php_self?m[0]=view&id=",
            ],
        ]
    );
    $cells[] = $report->fetch();
    // Revision file if any
    $revActive = $dms->getActiveRevision();
    $wording = _('Download Final Document');
    $icon = _getIconPath(basicFile::getExtensionFromFileName($revActive['pub_filename'] ?? ''));
    $link = $dms->getActivePubFileLink();
    if (0 != ($revActive['pub_fid'] ?? null) && 'ARCHIVE' != $dms->getStatus()) {
        $cells[] = <<<EOF
<p align="center">
  <a href="$link">
    <img src="$icon" width="100" height="100" alt="$wording" />
    <br/>$wording
  </a>
</p>
EOF;
    }
    // Next revision comments
    if (0 != $dms->getActiveRevisionID()) {
        $rev = $dms->getActiveRevisionObj();
        // Add link to add new comments
        $linkRevisionComments = "<p><a href=\"$php_self?m[0]=view&m[1]=revision&m[2]=view&m[3]=comments&m[4]=add&revid=$rev->itsID&id=$dms->itsID\">"._('Add comment or suggestion for next revision').'</a></p>';
        // List of comments
        $report = new tldReportColumnar(
            $rev->getComments(),
            [
                'xItems' => [
                    'date' => _('Date'),
                    'poster_fullname' => _('Poster'),
                    'comment' => _('Comment'),
                ],
                'title' => _('Next revision comments'),
            ]
        );
        $cells[] = $linkRevisionComments.$report->fetch();
    }
    // Arrange and display reports on screen
    $report = new tldHTMLTable(
        $cells,
        [
            'cols' => 2,
            'attribs' => [
                'table' => " width='100%'",
                'tr' => " bgcolor='#FFFFFF'",
            ],
        ]
    );
    $body .= $report->fetch();

    return $body;
}

function _getUserListReport($data, $title = '')
{
    global $php_self;
    if (empty($title)) {
        $title = _('User list');
    }
    $report = new tldReportColumnar(
        $data,
        [
            'xItems' => [
                'fullname' => 'Fullname',
                'email' => 'Email',
                'division' => 'Division/Region',
                'location' => 'Business Unit',
                'department' => 'Department',
                'tld_function' => 'TLD function',
            ],
            'title' => $title,
        ]
    );

    return $report->fetch();
}

function _addLog($uid, $log)
{
    global $dms;
    // Check if very first creation
    $revList = $dms->getRevisions();
    if (0 == count($revList)) {
        return;
    }

    // Else need to log
    return $dms->addLogEntry($uid, $log);
}

function _isInRevision()
{
    global $dms;

    return 'REVISION' == $dms->getStatus();
}

function _getIconPath($ext)
{
    return tldMimeFile::getIconPathByExtension($ext);
}
