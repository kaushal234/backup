<?php

use ApiBundle\Client;
use ApiBundle\Iri\Iri;
use Symfony\Component\HttpClient\Exception\ClientException;

$id = trim($id);
if (empty($id) || !is_numeric($id)) {
    $DEFAULT_ERROR[] = 'ERROR: ID sent empty or invalid';

    return;
}
$task = new tldTask($id);
if ($task->isEmpty()) {
    $DEFAULT_ERROR[] = "ERROR: Could not find task #$id";

    return;
}

$DEFAULT_TITLE .= "\Task#$id";
$parentID = $task->getParentID();

// Default access is NONE
$ops = ['comment', 'hours', 'links'];

// Access check based on user
$assignee = new tldUser($task->getAssignee());
$assignor = new tldUser($task->getAssignor());
$assignorID = $assignor->getSupervisor();
if (
    $user->getID() == $task->getAssignee()            // If user is assignee
    || $user->getID() == $task->getAssignor()            // If user is assignor
    || $user->getID() == $assignee->getSupervisor()    // if user is supervisor of the assignee
    || $user->getID() == $assignor->getSupervisor()    // if user is supervisor of the assignor
    || $user->isInGroup(['gg_ADMIN', 'superuser'])
) {
    array_push($ops, 'view', 'comment', 'transfer', 'reply', 'reschedule', 'closeConfirm', 'close', 'edit');
}
if ($user->isInGroup(['gg_MIS'])) {
    array_push($ops, 'category', 'tag', 'untag', 'pause', 'unpause');
}
if (null !== $moduleId = $task->itsHeader['ticket_module_id']) {
    $module = new tldModule($moduleId);
    if (!$module->isEmpty() && $user->getId() === $module->getMOOID()) {
        array_push($ops, 'tag', 'untag');
    }
}
if (($user->getID() == $task->getAssignor()            // If user is Assignor or if user is supervisor of the Assignor
        || $user->getID() == $assignor->getSupervisor() || 'ASO' === $task->getModule()) && $task->isClosed()) {
    array_push($ops, 'reopen');
}
// Access check for sequence
if ($task->isSequence()) {
    global $kernel;
    $kernel->getContainer()->get('request_stack')->getCurrentRequest()->attributes->set('alvest_module', 'SEQ');

    $seq = new tldSEQ($id);
    $current_node = $seq->getCurrentNode();
    $current_node_header = $current_node->getHeader();
    $smarty->assign('current_action', $current_node_header['dsca']);
    $additionalOperations = ['comment', 'transfer', 'reply', 'reschedule', 'closeConfirm', 'close', 'accept'];
    if ($seq->getModule() !== 'BP') {
        $additionalOperations[] = 'reject';
    }
    $additionalOperations[] = 'cancel';

    if (0 === strpos($seq->getTemplate()->getName(), 'hr.') && $user->isInGroup('gg_HR')) {
        $ops[] = 'view';
    }

    $ops = array_merge($ops, $additionalOperations);
    if (empty($ops)) {
        $DEFAULT_ERROR[] = 'ERROR: You do not have permission to access that SEQ task.';

        return 2;
    }
}

// Access check based on module
//give at least readonly access to tasks, UNLESS it is a USER task
$_MODULE = $task->getModule();
$hasMembers = false;
if ($_MODULE) {
    if (tldModMember::hasMembers($parentID, $_MODULE)) {
        $hasMembers = true;
    }
    if ($hasMembers && tldModMember::isMember($_MODULE, $parentID, $user->getID())) {
        array_push($ops, 'view', 'comment');
    }
    $kernel->getContainer()->get('request_stack')->getCurrentRequest()->attributes->set('alvest_module', $_MODULE);
}

$ccList = [];
$bccList = [];

switch ($_MODULE) {
    case 'EAP':
        $eap = new tldEAP($parentID);
        if ($eap->isMember($user->getID())) {
            array_push($ops, 'view', 'comment');
        }
        if ($eap->isPrivate()) {
            if ($user->isInGroupLevel('role_EM', $eap->getERP()) or $user->isInGroupLevel('role_ES', $eap->getERP())) {
                array_push($ops, 'view', 'comment', 'transfer', 'reschedule', 'closeConfirm', 'close', 'hours');
            }
        } else {
            array_push($ops, 'view');
            if ($user->isInGroup(['role_ES', 'role_EM'])) {
                array_push($ops, 'comment', 'transfer', 'reschedule', 'closeConfirm', 'close', 'hours');
            }
        }
        $body .= "<b>EAP# $parentID</b>&nbsp;".$eap->getShortDesc();
        break;
    case 'MEAP':
        $meap = new tldMEAP($parentID);
        if ($meap->isMember($user->getID())) {
            array_push($ops, 'view', 'comment');
        }
        if ($meap->isPrivate()) {
            if ($user->isInGroupLevel('role_EM', $meap->getERP()) || $user->isInGroupLevel('role_ES', $meap->getERP())) {
                array_push($ops, 'view', 'comment', 'transfer', 'reschedule', 'closeConfirm', 'close', 'hours');
            }
        } else {
            array_push($ops, 'view');
            if ($user->isInGroup(['role_ES', 'role_EM'])) {
                array_push($ops, 'comment', 'transfer', 'reschedule', 'closeConfirm', 'close', 'hours');
            }
        }
        $body .= "<b>MEAP# $parentID</b>&nbsp;".$meap->getShortDesc();
        break;
    case 'PDC':
        array_push($ops, 'view');
        $pdc = new tldPDC($parentID);
        if ($user->isInGroup(['role_PSM', 'role_PSE', 'role_PSA']) || $user->getId() == $pdc->getInitiatorID() || $user->isInGroup('demerit')) {
            array_push($ops, 'comment', 'transfer', 'reschedule', 'closeConfirm', 'close');
        }
        $body .= "<b>PDC# $parentID</b>&nbsp;".$pdc->getShortDescription();
        $hasMembers = false;
        break;
    case 'NCR':
        array_push($ops, 'view');
        try {
            $client = $kernel->getContainer()->get(Client::class);
        } catch (Exception $e) {
            $DEFAULT_ERROR[] = 'ERROR: Could not get API client. Reason: ' . $e->getMessage();
            return;
        }

        try {
            $ncr = $client->find('quality/non_conformities', $parentID);
        } catch (Exception $e) {
            $DEFAULT_ERROR[] = 'ERROR: Could not get NCR. Reason : ' .$e->getMessage();
            return;
        }

        if ($user->isInGroup(['role_QAM', 'role_QE', 'ncr'])) {
            array_push($ops, 'comment', 'transfer', 'reschedule', 'closeConfirm', 'close');
        }
        $body .= "<b>NCR# $parentID</b>&nbsp;".$ncr['shortDescription'];
        break;
    case 'SOL':
        array_push($ops, 'view');
        $sol = new tldSOL($parentID);
        if ($user->isInGroupLevel('role_PSA', $sol->getERP()) || $user->isInGroupLevel('role_PSM', $sol->getERP()) || $user->isInGroupLevel('role_PSE', $sol->getERP()) || $user->isInGroupLevel('role_EM', $sol->getERP()) || $user->isInGroupLevel('role_ES', $sol->getERP())) {
            array_push($ops, 'comment', 'transfer', 'reschedule');
        }
        break;
    case 'TTS':
        array_push($ops, 'view', 'ical');
        $tts = new tldTTS($parentID);
        if ($user->isInGroup('gg_MIS') || $user->getId() == $tts->getOwner()) {
            array_push($ops, 'move', 'comment', 'transfer', 'reschedule', 'closeConfirm', 'close', 'addJira');
        }
        $body .= "<b>TTS# $parentID</b>&nbsp;".$tts->getShortDesc();
        break;
    case 'CPA':
        array_push($ops, 'view');
        $cpa = new tldCPA($parentID);
        if ($user->getId() == $cpa->getInitiatorID() || $user->isInGroup('role_QAM')) {
            array_push($ops, 'comment', 'transfer', 'reschedule', 'closeConfirm', 'close');
        }
        $body .= "<b>CPA# $parentID</b>&nbsp;".$cpa->getShortDesc();
        break;
    case 'USER':
        if ($user->isInGroup('gg_MIS') || $user->isInGroup('GG_MIS_SAGE')) {
            array_push($ops, 'move', 'comment', 'transfer', 'reply', 'reschedule', 'closeConfirm', 'close', 'edit');
        }
        if($user->isInGroup('gg_HR')){
            array_push($ops, 'view');
        }
        if (empty($ops)) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permission to access that USER task.';
            //break outer switch
            break;
        }
        break;
    case 'GWF':
        $gwf = new tldGWF($parentID);
        if ($gwf->isMember($user->getID())) {
            array_push($ops, 'view', 'comment');
        }
        if (!$gwf->isPrivate()) {
            array_push($ops, 'view');
        }
        $body .= "<b>GWF# $parentID</b>&nbsp;".$gwf->getDsca();
        break;
    default:
        $class = "tld$_MODULE";
        $method = 'getShortDesc';

        // Hack to get Short Description for any object instantiable and having the required method
        if (class_exists($class) && method_exists($class, $method)) {
            $obj = new $class($parentID);
            $body .= "<b>$_MODULE# $parentID</b>&nbsp;".$obj->$method();
        }

        array_push($ops, 'view');
        break;
}

if ($user->isInGroup(['gg_ADMIN', 'GG_MIS_SAGE', 'GG_MIS'])) {
    array_push($ops, 'view', 'move', 'hours');
}

if (!in_array($m[2], $ops)) {
    $DEFAULT_ERROR[] = 'ERROR: You do not have permission to do that...';

    return;
}

$DEFAULT_MENU .= <<<EOF
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=tasks&m[1]=task&m[2]=view&id=$id">View Task</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=task&m[2]=comment&id=$id">Add Comment/File</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=task&m[2]=transfer&id=$id">Transfer</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=task&m[2]=reply&id=$id">Reply</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=task&m[2]=reschedule&id=$id">Reschedule</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=task&m[2]=hours&id=$id">Estimated Time</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=task&m[2]=ical&id=$id">iCAL</a>
EOF;
if ($user->isInGroup('gg_MIS')) {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=task&m[2]=move&id=$id">Move</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=task&m[2]=category&id=$id">Category</a>
EOF;
}
if (in_array('tag', $ops, true)) {
    if (!$task->isTag()) {
        $DEFAULT_MENU .= <<<EOF
    &nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=task&m[2]=tag&id=$id">TAG</a>
EOF;
    } else {
        $DEFAULT_MENU .= <<<EOF
 | <a href="$php_self?m[0]=tasks&m[1]=task&m[2]=untag&id=$id">UNTAG</a>
EOF;
    }
}
if (($_MODULE === 'TTS' && ($user->isInGroup(['ROLE_CIO', 'superuser']))) || ($_MODULE === 'GWF' && $user->getID() === (new tldGWF($task->getParentID()))->getAssignor())) {
    if (!$task->isPaused()) {
        $DEFAULT_MENU .= <<<EOF
 | <a href="$php_self?m[0]=tasks&m[1]=task&m[2]=pause&id=$id">PAUSE</a>
EOF;
    } else {
        $DEFAULT_MENU .= <<<EOF
 | <a href="$php_self?m[0]=tasks&m[1]=task&m[2]=unpause&id=$id">UNPAUSE</a>
EOF;
    }
    if ($user->isInGroup('gg_MIS')) {
        $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=task&m[2]=addJira&id=$id">Add Jira Issue</a>
EOF;
    }
}
if (($user->getID() == $task->getAssignor()            // If user is Assignor// if user is supervisor of the Assignor
        || $user->getID() == $assignor->getSupervisor() || 'ASO' === $task->getModule()) && $task->isClosed() && !$task->isSequence()) {
    $DEFAULT_MENU .= <<<EOF
 | <a href="$php_self?m[0]=tasks&m[1]=task&m[2]=reopen&id=$id">REOPEN</a>
EOF;
}
if ($task->isSequence()) {
    if ('BP' === $_MODULE) {
        $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=task&m[2]=accept&id=$id">Accept</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=task&m[2]=cancel&id=$id">Reject</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=task&m[2]=links&id=$id">Links</a>
EOF;
    } else {
        $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=task&m[2]=accept&id=$id">Accept</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=task&m[2]=reject&id=$id">Reject</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=task&m[2]=cancel&id=$id">Cancel</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=task&m[2]=links&id=$id">Links</a>
EOF;
    }

    if (($seq->getTemplate()->getName() === 'sales.Customer.Validation') || ($seq->getTemplate()->getName() === 'sales.new.customer')) {
        $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=task&m[2]=cancel&cancelOnlySeq=1&id=$id">Cancel without updating eCustomer</a>
EOF;
    }
} else {
    if ($task->canComplete($user)) {
        $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=task&m[2]=closeConfirm&id=$id">Mark as COMPLETED</a>
EOF;
    } else {
        $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=task&m[2]=reply&id=$id">Mark for REVIEW</a>
EOF;
    }

    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=task&m[2]=edit&id=$id">Edit</a>
EOF;
}

if ($user->isInGroup('tasks')) {
    $DEFAULT_MENU .= <<<EOF

&nbsp;|&nbsp;<a href="/en/private/calendar/tasks/tasks_admin.php?mode=record_view&form_type=main_tpl&id=$id">Admin</a>
EOF;
}

if ($user->isInGroup(['gg_ADMIN', 'gg_ENG', 'role_ENG', 'role_EM'])) {
    $header = $task->getHeader();
    $link = $header['module'];

    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="/en/private/manufacturing/eng/dev.php?m[0]=timekeeping&m[1]=reports&m[2]=timesheets&id=$id&link=Task">Linked Timesheets</a>
EOF;

    if (in_array($link, tldTimekeeping::getLinkList(), true)) {
    $parent_id = $header['parent_id'];
    $user_buid = $user->getBUID();
    $location = tldLocation::getERPByID($user_buid);
        $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="/en/private/manufacturing/eng/dev.php?m[0]=timekeeping&m[1]=form&m[2]=add2&module_id=$parent_id&link=$link&category=Design+Task&location=$location">Create Timesheet</a>
EOF;
    }
}


    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="/en/private/manufacturing/eng/dev.php?m[0]=timekeeping&m[1]=form&m[2]=addmis&module_id=$task->itsID" target="_blank">Create MIS Timesheet</a>
EOF;


$DEFAULT_MENU .= '</p>';
//if single is set then clear the cache
if ($single) {
    $sess['calendar']['tasks'] = [];
}
/**************
 * URLS used to link a task back to the associated module document
 ************/
$smarty->assign('mod_links', tldUtils::getModLinks());
$link = null;
if (!empty($task->getParentID()) && !empty($task->getModule())) {
    // The if check will avoid to call this method in an object context internally.
    $link = tldModLink::getURL($task->getModule(), $task->getParentID());
}
$smarty->assign('link', $link ?: '#');

// Register modifier callback for task descriptions
$smarty->register_modifier('taskDesc', '_taskDescriptionModifierScripts');

switch ($m[2]) {
    case 'ical':
        $cal = new tldCAL(
            $user->getID(),
            'calendar_of_userid' . $user->getID(),
            'TLD Tasks List'
        );
        $cal->addEvent($task->asVTODO());
        $cal->out();
        exit;
    case 'view':
        if ($task->isSequence() && $seq->getModule() === 'BP') {
            $bp = new tldBP($seq->getParentID());
            $eap = $bp->getEAP();

            if (null !== $eap && in_array($eap->getStatus(), ['NOTIFICATION', 'PROPOSED'])) {
                $header = $bp->itsHeader;
                $files = $bp->getFiles();

                $report = new tldAssocTable(
                    $header,
                    [
                        'id' => 'Process #',
                        'status' => 'Status',
                        'module' => 'Module',
                        'parent_id' => 'Ref#',
                        'owner_fullname' => 'Owner',
                        'dt_opened' => 'Date Opened',
                        'short_desc' => 'Short Description',
                        'long_desc' => 'Description',
                        'proposal_solution' => 'Proposal solution',
                        'actions' => 'Actions',
                        'dt_closed' => 'Date closed',
                    ],
                    [
                        'title' => 'General',
                        'links' => [
                            'parent_id' => '/en/private/manufacturing/eng/dev.php?m[0]=eap&m[1]=view&id=',
                        ]
                    ]
                );
                $body .= $report->fetch();

                if (!empty($files)) {
                    $fileReport = new tldReportColumnar(
                        $files,
                        [
                            'xItems' => [
                                'id' => 'ID#',
                                'date' => 'Date',
                                'poster_fullname' => 'Poster',
                                'description' => 'Description',
                                'filename' => 'Filename',
                            ],
                            'title' => 'Attachments',
                            'links' => [
                                'id' => '/en/private/common/index.php?m[0]=files&m[1]=view&id=',
                            ],
                        ]
                    );
                    $body .= $fileReport->fetch();
                }

                $body .= '<hr>';
            }
        }
        $body .= _getView();

        break;
    case 'edit':
        $header = $task->getHeader();
        $form = new HTML_QuickForm('frmNewTask', 'post');
        $form->addElement('hidden', 'm[0]', 'tasks');
        $form->addElement('hidden', 'm[1]', 'task');
        $form->addElement('hidden', 'm[2]', 'edit');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', "Modify Escalation trigger(Days) for Task #$id");
        $form->addElement('text', 'escalation_trigger', 'Escalation trigger(Days)');

        $form->setDefaults(['escalation_trigger' => $header['escalation_trigger']]);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('escalation_trigger', 'This is a required field.', 'required');
        $form->addRule('escalation_trigger', 'Field is numeric', 'numeric');
        $form::registerRule('maxvalue', 'function', 'max_value_f');
        $form->addRule('escalation_trigger', 'Maximum value for Escalation factor is 60 days as per TLD rules', 'maxvalue');
        if (!$form->validate()) {
            $body = $form->toHTML();
            $body .= _getView();
            break;
        }

        $est = $form->exportValues();
        if (isset($est['escalation_trigger']) && !is_numeric($est['escalation_trigger'])) {
            $DEFAULT_ERROR[] = 'ERROR: escalation trigger must be numeric!';
            $body = $form->toHTML();
            break;
        }

        // Edit the estimate time of completion
        $orig_trigger = $header['escalation_trigger'];
        $new_trigger = $est['escalation_trigger'];
        $error = $task->updatetrigger($new_trigger);
        if ($error) {
            $DEFAULT_ERROR[] = "Could not edit the Escalation trigger days for task #$id. There was an error processing. The error returned is '$error'";
            $body .= _getView();
            break;
        }

        $body .= _getView();
        break;
    case 'addJira':
        $header = $task->getHeader();
        $form = new HTML_QuickForm('addJiraIssue', 'post');
        $form->addElement('hidden', 'm[0]', 'tasks');
        $form->addElement('hidden', 'm[1]', 'task');
        $form->addElement('hidden', 'm[2]', 'addJira');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', "Add Jira issue for Task #$id");
        $form->addElement('text', 'jira_issue', 'JIRA Issue');

        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('jira_issue', 'This is a required field.', 'required');
        $form->addRule('jira_issue', 'Field is numeric', 'numeric');
        if (!$form->validate()) {
            $body = $form->toHTML();
            $body .= _getView();
            break;
        }

        $vars = $form->exportValues();

        // Edit the estimate time of completion
        $jiraIssue = (int) $vars['jira_issue'];
        $error = $task->update(['jira_issue' => $jiraIssue]);
        if ($error) {
            $DEFAULT_ERROR[] = "Could not edit the JIRA issue for task #$id. There was an error processing. The error returned is '$error'";
            $body .= _getView();
            break;
        }

        $task->addComment(['poster' => $user->getID(), 'comment' => sprintf('Issue %s added in JIRA, your TTS will be soon investigated.', $vars['jira_issue'])]);

        $body .= _getView();
        break;
    case 'hours':
        $header = $task->getHeader();
        $form = new HTML_QuickForm('frmNewTask', 'post');
        $form->addElement('hidden', 'm[0]', 'tasks');
        $form->addElement('hidden', 'm[1]', 'task');
        $form->addElement('hidden', 'm[2]', 'hours');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', "Modify Estimated Time of Completion (Hours) for Task #$id");
        $form->addElement('text', 'hours', 'Estimated Time of Completion (Hours)');
        $form->addElement('textarea', 'comment', 'Reason for the Est Time of Completion change:',
            ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']
        );
        $form->setDefaults(['hours' => $header['hours']]);
        $ams = &$form->addElement('advmultiselect', 'cc_users', null,
            tldDirectory::getUserlist('smartyOptions'),
            ['size' => 10, 'class' => 'pool', 'style' => 'width:500px;']
        );
        $ams->setLabel(['CC others... (OPTIONAL)', 'Addressbook', 'CC']);
        $ams->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
        $ams->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
        $form->addElement('file', 'file', 'Attachment');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('comment', 'This is a required field.', 'required');

        if (!$form->validate()) {
            $body = $form->toHTML();
            $body .= _getView();
            break;
        }

        $est = $form->exportValues();

        // Check the limit of cc users
        $numCC = count($est['cc_users'] ?? []);
        if ($numCC > 15) {
            $DEFAULT_ERROR[] = 'ERROR: Cannot cc to more than 15 persons!';
            $body = $form->toHTML();
            $smarty->assign('task', $task->asArray());
            $body .= $smarty->fetch("$PATH/tasks/view.task.tpl");
            break;
        }
        if (isset($est['hours']) && !is_numeric($est['hours'])) {
            $DEFAULT_ERROR[] = 'ERROR: Estimated Time of Completion entry must be numeric!';
            $body = $form->toHTML();
            break;
        }

        // Edit the estimate time of completion
        $orig_hours = $header['hours'];
        $new_hours = $est['hours'];
        $error = $task->estimatedHours($new_hours);
        if ($error) {
            $DEFAULT_ERROR[] = "Could not edit the Estimated Time of Completion for task #$id. There was an error processing. The error returned is '$error'";
            $body .= _getView();
            break;
        }

        // Trigger MIS workflow
        $task->triggerMISWorkflow($user);

        $file = $form->getElement('file');
        $est['file_info'] = $file->getValue();
        $assignee = new tldUser($task->getAssignee());
        $assignee_fullname = $assignee->getFullname();
        $DEFAULT_ERROR[] = $subject;

        if ($hasMembers) {
            $members = tldModMember::byParent($parentID, $_MODULE);
            foreach ($members as $member) {
                $ccList[] = $member['email'];
            }
        }

        if ($numCC > 0) {
            foreach ($est['cc_users'] as $userid) {
                $ccUser = new tldUser($userid);
                $ccList[] = $ccUser->getEmail();
                if (!in_array($userid, $listCC)) {
                    $task->addCC($userid);
                }
            }
        }
        $ccList = array_unique($ccList);
        if (count($ccList ?? [])) {
            $comment .= "<br>cc: ";
            $comment .= implode(',', $ccList);
        }

        // Send notification
        $subject = "Tasks, Estimated Time of Completion change: #$id from $orig_hours to $new_hours";
        $message = <<<EOF
$subject<br>
<b>Reason for Estimated Time of Completion change:</b><br>
$comment<br><br>
Please log in to the TLD-GSE intranet and go to the calendar module to view your open tasks.
<a href="http://{$_SERVER['HTTP_HOST']}/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$id">
Click here to go to Task.
</a>
<br>
EOF;
        $task->notifyAssignee($message, $subject, $ccList, $bccList);
        $est['poster'] = $user->getId();
        $est['comment'] = "$subject<br>Reason:$comment";
        $task->addComment($est);
        $body .= _getView();
        break;
    case 'closeConfirm':
    case 'close':
        if ($task->isClosed()) {    //check if task is already closed
            $DEFAULT_ERROR[] = "ERROR: Cannot CLOSE since Task #$id is already closed!";
            break;
        }
        if ($task->isSequence()) {
            $DEFAULT_ERROR[] = 'ERROR: Cannot CLOSE a Sequence directly! Please use accept/reject/cancel features.';
            break;
        }
        if (!$task->canComplete($user)) {
            $DEFAULT_ERROR[] = 'ERROR: Only assignor can close this task. Use the reply or mark for review feature to return the TTS to the assignor.';
            break;
        }
        // Get lists of people ccied along the thread
        $listCC = $task->getCC();
        // Get form
        $form = new HTML_QuickForm('frmNewTask', 'post');
        $form->addElement('hidden', 'm[0]', 'tasks');
        $form->addElement('hidden', 'm[1]', 'task');
        $form->addElement('hidden', 'm[2]', 'close');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', 'Please provide comment for closing..');
        $form->addElement('textarea', 'comment', 'Conclusion',
            ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']
        );
        $form->addElement('text', 'hours', 'Estimate man hours');
        $ams = &$form->addElement('advmultiselect', 'cc_users', null,
            tldDirectory::getUserlist('smartyOptions'),
            [
                'size' => 10,
                'class' => 'pool',
                'style' => 'width:500px;',
            ]
        );
        $ams->setLabel(['CC others... (OPTIONAL)', 'Addressbook', 'CC']);
        $ams->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
        $ams->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
        $form->addElement('file', 'file', 'Attachment');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('comment', 'This is a required field.', 'required');
        $form->setDefaults(['cc_users' => $listCC]);

        if (!$form->validate()) {
            $body = $form->toHTML();
            $body .= _getView();
            break;
        }

        $formdata = $form->exportValues();

        // Check the limit of cc users
        $numCC = count($formdata['cc_users'] ?? []);
        if ($numCC > 15) {
            $DEFAULT_ERROR[] = 'ERROR: Cannot cc to more than 15 persons!';
            $body = $form->toHTML();
            $smarty->assign('task', $task->asArray());
            $body .= $smarty->fetch("$PATH/tasks/view.task.tpl");
            break;
        }

        // Special case for MOM task
        if ($_MODULE === 'MOM') {
            global $kernel;

            try {
                $client = $kernel->getContainer()->get(Client::class);
            } catch (Exception $e) {
                $DEFAULT_ERROR[] = 'ERROR: Could not get API client. Reason: ' . $e->getMessage();
                return;
            }

            try {
                $actions = $client->findBy('minutes_of_meeting/actions', ['task' => $id]);
            } catch (Exception $e) {
                $DEFAULT_ERROR[] = 'ERROR: Could not get Action. Reason : ' . $e->getMessage();
                return;
            }

            if ((0 === $actions->count())) {
                $DEFAULT_ERROR[] = 'ERROR: Could not set actions as completed. Reason: No Actions are not found';
                return;
            }

            try {
                foreach ($actions as $action) {
                    $client->save('minutes_of_meeting/actions', [
                        '@id' => $action['@id'],
                        'completed' => true,
                        'closingComment' => mb_convert_encoding($formdata['comment'], 'UTF-8', mb_list_encodings()),
                    ]);
                }

            } catch (ClientException $e) {
                $errors = json_decode($e->getResponse()->getContent(), true);
                $DEFAULT_ERROR[] = 'ERROR: Could not set actions as completed. Reason: ' . $errors['hydra:description'];
                return;
            }
        }

        if ($e = $task->close($vars)) {
            $DEFAULT_ERROR[] = "ERROR: There was a problem closing Task $id $e";
            $body .= _getView();
            break;
        }

        if ($_MODULE === 'EAP' && $eap !== null) {
            $numberOfOpenedTasks = $eap->getTasks();
            if (!$numberOfOpenedTasks) {
                $modules = tldUtils::getModLinks();
                $body .= <<<EOF
<br />
<h3 class="alert">This was the last task not closed of this EAP, you can <a href="{$modules['EAP']}{$parentID}&m[2]=selectStatus" title="Change EAP#{$parentID} Status"}>change status</a> or <a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=form&m[2]=newTask&module=EAP&parent_id={$parentID}&nextAssignee={$eap->itsHeader['poster']}" title="Open a new task EAP#{$parentID} Status"}>ask the originator to move it forward</a>.</h3>
<br />
EOF;
            }
        }

        $subject = "Tasks, Closed: #$id of " . $task->getModule() . '#' . $task->getParentID() . ' closed by ' . $user->getFullname();
        $DEFAULT_ERROR[] = $subject;
        $header = $task->getHeader();

        // MISC people to notify in cc
        switch ($_MODULE) {
            case 'GWF':
                $gwf = new tldGWF($parentID);
                $members = $gwf->getMembers();
                foreach ($members as $member) {
                    $ccList[] = $member['email'];
                }
                break;
            //for tts tasks, cc all dept personnel
            case 'TTS':
                $_PID = $task->getParentID();
                if ($_PID != 0) {
                    $tts = new tldTTS($_PID);
                    $domain = $tts->getDomain();
                    if ($domain == 'ALL_DOMAINS') {
                        $bccList[] = 'mis@tld-america.com';
                        $bccList[] = 'mis@tld-europe.com';
                        $bccList[] = 'mis@tld-asia.com';
                    } else {
                        $bccList[] = "mis@$domain";
                    }
                    $category = $tts->getHeader();
                    $subject .= ', ' . $category['category'] . ' - ' . $domain;
                }
                break;
            case 'CPA':
                $toCC = tldModMember::byParent($task->getParentID(), 'CPA');
                foreach ($toCC as $member) {
                    $ccList[] = $member['email'];
                }
                break;
        }

        if ($hasMembers) {
            $members = tldModMember::byParent($parentID, $_MODULE);
            foreach ($members as $member) {
                $ccList[] = $member['email'];
            }
        }

        // Finally get emails for each user id
        if (count($formdata['cc_users'] ?? []) > 0) {
            foreach ($formdata['cc_users'] as $userid) {
                $ccUser = new tldUser($userid);
                $ccList[] = $ccUser->getEmail();
            }
        }
        $ccList = array_unique($ccList);
        if (count($ccList ?? [])) {
            $cclist .= "<br>cc: ";
            $cclist .= implode(',', $ccList);
        }

        $file = $form->getElement('file');

        $bcc = sprintf('<br>bcc: %s', implode(', ', $bccList));
        $comment.= $bcc;

    $message = <<<EOF
$subject<br>
$comment<br><br>
If you want to see the task, please log in to the TLD-GSE intranet and go to the calendar module to view your open tasks.
<a href="http://{$_SERVER['HTTP_HOST']}/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$id">
Click here to go to Task.
</a>
<br>
EOF;

        $closeComment = [
            'poster' => $user->getId(),
            'comment' => $subject . "<br>" . $formdata['comment'] . "<br>" . $cclist . "<br>" . $bcc,
            'file_info' => $file->getValue(),
        ];

        // Send notification
        $task->addComment($closeComment);
        $task->notifyAssignee($message, $subject, $ccList, $bccList);
        $body .= _getView();
        break;
    case 'reply':
        $body = '';
        if ($task->getAssignor()===$user->getId()){
            $DEFAULT_ERROR[] = "ERROR: Using reply button will let the TTS Assigned to you, please use the transfer button instead Thanks!";
            break;
        }
    case 'transfer':
        $body = '';
        $isAllowedToReschedule = static function (tldUser $user) use ($ops) {
            return in_array('reschedule', $ops) || $user->isInGroup(['gg_MIS']);
        };

        $header = $task->getHeader();
        if ($task->isClosed()) {
            $DEFAULT_ERROR[] = "ERROR: Cannot TRANSFER function since Task #$id is already closed!";
            break;
        }
        if ($task->isPaused()) {
            $DEFAULT_ERROR[] = "ERROR: Cannot TRANSFER function since Task #$id is in PAUSE!";
            break;
        }
        if ($task->isSequence()) {
            $body .= '<p style="color: red; font-weight: bold">WARNING: transferring a sequence does not confer the permission to accept the current step</p>';
        }
        $listCC = $task->getCC();
        $form = new HTML_QuickForm('frmNewTask', 'post');
        $form->addElement('hidden', 'm[0]', 'tasks');
        $form->addElement('hidden', 'm[1]', 'task');
        $form->addElement('hidden', 'm[2]', $m[2]);
        $form->addElement('hidden', 'previous');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', "Transfer Task #$id to..");
        if ($m[2] === 'transfer') {
            $form->addElement('select', 'assignee', 'Assignee', ['' => ''] + tldTask::getAssigneesByUser($task->getModule(), $user->getID()));
        } elseif ($m[2] === 'reply') {
            $form->addElement('hidden', 'assignee', $task->getAssignor());
        }
        $dueDateOptions = ['class' => 'datepicker'];
        if ($isAllowedToReschedule($user) === false) {
            $dueDateOptions['disabled'] = 'disabled';
        }
        $form->addElement('text', 'due_date', 'Due Date', $dueDateOptions);

        $form->addElement('textarea', 'comment', 'Reason for the TRANSFER',
            ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']
        );
        $ams = &$form->addElement('advmultiselect', 'cc_users', null,
            tldDirectory::getUserlist('smartyOptions'),
            [
                'size' => 10,
                'class' => 'pool',
                'style' => 'width:500px;',
            ]
        );
        $ams->setLabel(['CC others... (OPTIONAL)', 'Addressbook', 'CC']);
        $ams->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
        $ams->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
        $form->addElement('file', 'file', 'Attachment');
        $form->addElement('submit', 'btnSubmit', 'Submit', ['class'=>'disablesubmit']);
        $form->addRule('assignee', 'This is a required field.', 'required');
        $form->addRule('comment', 'This is a required field.', 'required');
        $form->setDefaults(['cc_users' => $listCC]);

        $originalDate = $header['due_date'] !== '0000-00-00' ? new DateTime($header['due_date']) : new DateTime();

        $form->setDefaults(['due_date' => $originalDate->format('Y-m-d')]);

        if (!$form->validate()) {
            $body .= $form->toHTML();
            $body .= _getView();
            break;
        }
        $transfer = $form->exportValues();

        $due_date = !empty($transfer['due_date']) ? $transfer['due_date'] : $header['due_date'];

        try {
            $date = new DateTime($due_date);
        } finally {
            $dateTimeErrors = DateTime::getLastErrors();
        }

        if (!empty($dateTimeErrors['warning_count']) || !empty($dateTimeErrors['error_count'])) {
            $DEFAULT_ERROR[] = "The provided date $due_date is invalid";
            $body .= _getView();
            break;
        }

        // User can't reschedule a task assigned to him unless he's the assignor so we need the same check regarding
        // the new assignee before allowing to reschedule
        // @see https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=806024
        $newAssignee = $transfer['assignee'];
        $isAllowedToReschedule = static function (tldUser $user) use ($ops, $task, $newAssignee) {
            return (in_array('reschedule', $ops, true) && ($newAssignee !== $user->getID() || $task->getAssignor() === $user->getID()))
                || $user->isInGroup(['gg_MIS']);
        };

        // Check new due date is not prior to previous due date
        if ($originalDate > new DateTime($due_date) && $isAllowedToReschedule($user) === true) {
            $DEFAULT_ERROR[] = 'ERROR: Rescheduling can not be set to a date prior to the original due date';
            $body .= $form->toHTML();
            $body .= _getView();
            break;
        } elseif ($originalDate != new DateTime($due_date) && $isAllowedToReschedule($user) === true) {
            $resc = [];
            $dateString = $originalDate->format('Y-m-d');
            $subject = "Tasks, Reschedule: #$id from $dateString to $due_date";
            $resc['poster'] = $user->getId();
            $resc['comment'] = $subject;
            $task->addComment($resc);

            // Reschedule the task
            $error = $task->reschedule($due_date);
            if ($error) {
                $DEFAULT_ERROR[] = "Could not RESCHEDULE task #$id. There was an error processing. The error returned is '$error'";
                $body .= _getView();
                break;
            }
        }
        // Check the limit of cc users
        $numCC = count($transfer['cc_users'] ?? []);
        if ($numCC > 15) {
            $DEFAULT_ERROR[] = 'ERROR: Cannot cc to more than 15 persons!';
            $body = $form->toHTML();
            $smarty->assign('task', $task->asArray());
            $body .= $smarty->fetch("$PATH/tasks/view.task.tpl");
            break;
        }

        // Trigger MIS workflow
        $task->triggerMISWorkflow($user);

        $file = $form->getElement('file');
        $transfer['file_info'] = $file->getValue();
        $assignee = new tldUser($transfer['assignee']);
        $assignee_fullname = $assignee->getFullname();
        $DEFAULT_ERROR[] = $subject;
        $ccList = [];

        // MISC people to notify in cc and specific action
        switch ($_MODULE) {
            case 'GWF':
                $gwf = new tldGWF($parentID);
                $members = $gwf->getMembers();
                foreach ($members as $member) {
                    $ccList[] = $member['email'];
                }
                break;
            case 'CPA':
                $toCC = tldModMember::byParent($task->getParentID(), 'CPA');
                foreach ($toCC as $member) {
                    $ccList[] = $member['email'];
                }
                break;
            case 'MOM':
                global $kernel;

                try {
                    $client = $kernel->getContainer()->get(Client::class);
                } catch (Exception $e) {
                    $DEFAULT_ERROR[] = 'ERROR: Could not get API client. Reason: ' . $e->getMessage();
                    return;
                }

                try {
                    $actions = $client->findBy('minutes_of_meeting/actions', ['task' => $id]);
                    $newAssignee = $client->findOneBy('people', ['legacyId' => $assignee->getID()]);
                } catch (Exception $e) {
                    $DEFAULT_ERROR[] = 'ERROR: Could not get Action. Reason : ' .$e->getMessage();
                    return;
                }

                try {
                    foreach ($actions as $action) {
                        $client->save('minutes_of_meeting/actions', [
                            '@id' => $action['@id'],
                            'assignee' => '/people/'.$newAssignee->getIriID(),
                        ]);
                    }
                } catch (ClientException $e) {
                    $errors = json_decode($e->getResponse()->getContent(false), true);
                    $DEFAULT_ERROR[] = 'ERROR: Could not transfer assignee of the MOM#' . (int) basename($action['@id']) . ', Reason: ' . $errors['hydra:description'];
                    return;
                }
                break;
            case 'BP':
                $bp = new tldBP($parentID);
                if (null !== $meap = $bp->getMEAP()) {
                    foreach ($bp->getTasks('ALL') as $member) {
                        $ccList[] = $member['assignee_email'];
                    }
                }
                break;
        }
        $ccList = array_unique($ccList);

        $error = $task->transfer($transfer['assignee']);
        if ($error) {
            $DEFAULT_ERROR[] = "Could not transfer task #$id. There was an error processing. The error returned is '$error'";
            $body .= _getView();
            break;
        }

        if ($hasMembers) {
            $members = tldModMember::byParent($parentID, $_MODULE);
            foreach ($members as $member) {
                $ccList[] = $member['email'];
            }
        }
        $numCC = count($transfer['cc_users'] ?? []);
        if ($numCC > 0) {
            foreach ($transfer['cc_users'] as $userid) {
                $ccUser = new tldUser($userid);
                $ccList[] = $ccUser->getEmail();
            }
        }
        $ccList = array_unique($ccList);
        if (count($ccList ?? [])) {
            $comment .= "<br>cc: ";
            $comment .= implode(',', $ccList);
        }

        // Send notification
        $subject = "Tasks, Transfer: #$id to $assignee_fullname";
        if ($_MODULE == 'TTS') {
            $_PID = $task->getParentID();
            $tts = new tldTTS($_PID);
            $category = $tts->getHeader();
            $domain = $tts->getDomain();
            $subject .= ', ' . $category['category'] . ' - ' . $domain;
        }
        $message = <<<EOF
$subject<br>
<b>Reason for TRANSFERRING:</b><br><hr>
$comment<br>
<hr>
Please log in to the TLD-GSE intranet and go to the calendar module to view your open tasks.
<a href="http://{$_SERVER['HTTP_HOST']}/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$id">
Click here to go to Task.
</a>
<br>
EOF;

        $task->notifyAssignee($message, $subject, $ccList, $bccList);
        $transfer['comment'] = "$subject<br>Reason:$comment<br>" . $cclist;
        $transfer['poster'] = $user->getId();
        $task->addComment($transfer);

        $invoiceGroups = ['role_AP', 'gg_ACCT', 'seq_acct.japan.invoice.approval.step0', 'seq_acct.japan.invoice.approval.step1', 'seq_acct.japan.invoice.approval.evp', 'seq_acct.japan.invoice.approval.coo', 'seq_acct.japan.invoice.approval.ap', 'seq_acct.japan.invoice.approval.ceo', 'seq_acct.invoice.approval_step1', 'role_COO', 'role_CEO', 'superuser', 'acl_doc_NonPOInvoice.approval'];

        if ($task->isSequence() && false !== strpos($task->getTask(), 'http://192.111.1.192/invoices') && !$assignee->isInGroup($invoiceGroups)) {
            $matches = [];
            preg_match('/http:\/\/192\.111\.1\.192\/invoices\/.+\.pdf/m', $task->getTask(), $matches);

            if (!empty($matches)) {
                $filePath = str_replace('http://192.111.1.192/', '/mnt/grpfps10.online_approvals/', $matches[0]);

                $subject .= ' - invoice file';
                $message = <<<EOF
$subject<br>
<p>Since the sequence #$id has been transferred to you and you are not allowed to access the related pdf, please find it attached.</p>
EOF;

                if (file_exists($filePath)) {
                    tldUtils::emailAttachment(
                        $assignee->getEmail(),
                        'noreply@tld-gse.com',
                        $subject,
                        $message,
                        $filePath,
                        $user->getEmail()
                    );
                }
            }
        }

        $body .= _getView();
        break;
    case 'reschedule':
        if ($task->isClosed()) {
            $DEFAULT_ERROR[] = "ERROR: Cannot RESCHEDULE since Task #$id is already closed!";
            break;
        }
        if ($task->isPaused()) {
            $DEFAULT_ERROR[] = "ERROR: Cannot RESCHEDULE function since Task #$id is in PAUSE!";
            break;
        }
        //check if assignee is trying to reschedule

        if (($task->getAssignee() == $user->getID() && $task->getAssignor() != $user->getID()) && !$user->isInGroup(['gg_MIS'])) {
            $DEFAULT_ERROR[] = 'ERROR: You cannot RESCHEDULE an assigned task but you can add a comment requesting the Assignor to reschedule it for you.';
            $body .= _getView();
            break;
        }

        $header = $task->getHeader();
        $form = new HTML_QuickForm('frmNewTask', 'post');
        $form->addElement('hidden', 'm[0]', 'tasks');
        $form->addElement('hidden', 'm[1]', 'task');
        $form->addElement('hidden', 'm[2]', 'reschedule');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', "Reschedule Task #$id to..");
        $form->addElement('date', 'due_date', 'Due Date',
            [
                'format' => 'Ymd',
                'minYear' => date('Y'),
                'maxYear' => date('Y') + 2,]
        );
        $form->addElement('textarea', 'comment', 'Reason for the RESCHEDULING',
            ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']
        );
        $form->setDefaults(
            [
                'due_date' => [
                    'Y' => substr($header['due_date'], 0, 4),
                    'm' => substr($header['due_date'], 5, 2),
                    'd' => substr($header['due_date'], 8, 2),
                ],
            ]
        );
        $ams = &$form->addElement('advmultiselect', 'cc_users', null,
            tldDirectory::getUserlist('smartyOptions'),
            ['size' => 10, 'class' => 'pool', 'style' => 'width:500px;']
        );
        $ams->setLabel(['CC others... (OPTIONAL)', 'Addressbook', 'CC']);
        $ams->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
        $ams->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
        $form->addElement('file', 'file', 'Attachment');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('comment', 'This is a required field.', 'required');

        if (!$form->validate()) {
            $body = $form->toHTML();
            $body .= _getView();
            break;
        }

        $resc = $form->exportValues();

        // Check the limit of cc users
        $numCC = count($resc['cc_users'] ?? []);
        if ($numCC > 15) {
            $DEFAULT_ERROR[] = 'ERROR: Cannot cc to more than 15 persons!';
            $body = $form->toHTML();
            $body .= _getView();
            break;
        }

        if (!checkdate($due_date['m'], $due_date['d'], $due_date['Y'])) {
            $DEFAULT_ERROR[] = 'ERROR: date is not good...';
            break;
        }
        $orig_date = $header['due_date'];
        $due_date = $due_date['Y'] . '-' . $due_date['m'] . '-' . $due_date['d'];
        $new_due_date = new DateTime($due_date);
        $today = new DateTime(date('Y-m-d'));
        // Check if in past
        if ($today > $new_due_date) {
            $DEFAULT_ERROR[] = "ERROR: Rescheduling can not be in past... (Selected $due_date)";
            $body .= $form->toHTML();
            $body .= _getView();
            break;
        }
        // Reschedule the task
        $error = $task->reschedule($due_date);
        if ($error) {
            $DEFAULT_ERROR[] = "Could not RESCHEDULE task #$id. There was an error processing. The error returned is '$error'";
            $body .= _getView();
            break;
        }

        // Trigger MIS workflow
        $task->triggerMISWorkflow($user);

        $file = $form->getElement('file');
        $resc['file_info'] = $file->getValue();
        $assignee = new tldUser($task->getAssignee());
        $assignee_fullname = $assignee->getFullname();
        $DEFAULT_ERROR[] = $subject;

        switch ($_MODULE) {
            case 'GWF':
                $gwf = new tldGWF($parentID);
                $members = $gwf->getMembers();
                foreach ($members as $member) {
                    $ccList[] = $member['email'];
                }
                break;
            case 'CPA':
                $toCC = tldModMember::byParent($task->getParentID(), 'CPA');
                foreach ($toCC as $member) {
                    $ccList[] = $member['email'];
                }
                break;
        }
        if ($hasMembers) {
            $members = tldModMember::byParent($parentID, $_MODULE);
            foreach ($members as $member) {
                $ccList[] = $member['email'];
            }
        }

        $listCC = $task->getCC();
        if ($numCC > 0) {
            foreach ($resc['cc_users'] as $userid) {
                $ccUser = new tldUser($userid);
                $ccList[] = $ccUser->getEmail();
                if (!in_array($userid, $listCC)) {
                    $task->addCC($userid);
                }
            }
        }
        if (count($ccList ?? [])) {
            $ccList = array_unique($ccList);
            $comment .= "<br>cc: ";
            $comment .= implode(',', $ccList);
        }

        // Send notification
        $subject = "Tasks, Reschedule: #$id from $orig_date to $due_date";
        $message = <<<EOF
$subject<br>
<b>Reason for RESCHEDULING:<br></b><br>
$comment<br><br>
Please log in to the TLD-GSE intranet and go to the calendar module to view your open tasks.
<a href="http://{$_SERVER['HTTP_HOST']}/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$id">
Click here to go to Task.
</a>
<br>
EOF;
        $task->notifyAssignee($message, $subject, $ccList, $bccList);
        $resc['poster'] = $user->getId();
        $resc['comment'] = "$subject<br>Reason:$comment";
        $task->addComment($resc);
        $body .= _getView();
        break;
    case 'comment':
        if ($task->isClosed()) {
            $DEFAULT_ERROR[] = "ERROR: Cannot ADD COMMENT since Task #$id is already closed!";
            break;
        }
        if ($task->isPaused() && (($_MODULE !== 'TTS' || (!$user->isInGroup(['ROLE_CIO', 'superuser']))) && ($_MODULE !== 'GWF' || $user->getID() !== (new tldGWF($task->getParentID()))->getAssignor()))) {
            $DEFAULT_ERROR[] = "ERROR: You cannot comment in a PAUSED ticket. We will prioritize it and allocate resources whenever possible. No ETA can be provided yet. Sorry for the inconvenience.";
            break;
        }
        // Get lists
        $listCC = $task->getCC();
        // Get form
        $form = new HTML_QuickForm('frmNewComment', 'post');
        $form->addElement('hidden', 'm[0]', 'tasks');
        $form->addElement('hidden', 'm[1]', 'task');
        $form->addElement('hidden', 'm[2]', 'comment');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', 'Submit New Comment Form');
        $form->addElement('textarea', 'comment', 'Comment',
            ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']
        );
        $ams = &$form->addElement('advmultiselect', 'cc_users', null,
            tldDirectory::getUserlist('smartyOptions'),
            ['size' => 10, 'class' => 'pool', 'style' => 'width:500px;']
        );
        $ams->setLabel(['CC others... (OPTIONAL)', 'Addressbook', 'CC']);
        $ams->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
        $ams->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
        $form->addElement('file', 'file', 'Attachment');
        $form->addElement('submit', 'btnSubmit', 'Submit', ['class'=>'disablesubmit']);
        $form->addRule('comment', 'This is a required field.', 'required');
        $form->setDefaults(['cc_users' => $listCC]);

        if (!$form->validate()) {
            $body = $form->toHTML();
            $body .= _getView();
            break;
        }

        $comment = $form->exportValues();

        // Check the limit of cc users
        $numCC = count($comment['cc_users'] ?? []);
        if ($numCC > 15) {
            $DEFAULT_ERROR[] = 'ERROR: Cannot cc to more than 15 persons!';
            $body = $form->toHTML();
            $body .= _getView();
            break;
        }

        if ($task->isClosed()) {
            $DEFAULT_ERROR[] = "ERROR: Could not add comment. Task $taskid in Demerit $id is already closed!";
            $body .= _getView();
            break;
        }

        $file = $form->getElement('file');
        $comment['file_info'] = $file->getValue();
        $comment['poster'] = $user->getId();
        $ccList = [];
        switch ($_MODULE) {
            case 'GWF':
                $gwf = new tldGWF($parentID);
                $members = $gwf->getMembers();
                foreach ($members as $member) {
                    $ccList[] = $member['email'];
                }
                //add the moderator, Task#90870
                $modid = $gwf->getAssignor();
                if ($modid > 0) {
                    $mod = new tldUser($modid);
                    $ccList[] = $mod->getEmail();
                }
                break;
            case 'CPA':
                $toCC = tldModMember::byParent($task->getParentID(), 'CPA');
                foreach ($toCC as $member) {
                    $ccList[] = $member['email'];
                }
                break;
            case 'BP':
                $bp = new tldBP($parentID);
                if (null !== $meap = $bp->getMEAP()) {
                    foreach ($bp->getTasks('ALL') as $member) {
                        $ccList[] = $member['assignee_email'];
                    }
                }
                break;
        }
        if ($hasMembers) {
            $members = tldModMember::byParent($parentID, $_MODULE);
            foreach ($members as $member) {
                $ccList[] = $member['email'];
            }
        }
        if ($numCC > 0) {
            foreach ($comment['cc_users'] as $userid) {
                $ccUser = new tldUser($userid);
                $ccList[] = $ccUser->getEmail();
                if (!in_array($userid, $listCC)) {
                    $task->addCC($userid);
                }
            }
        }
        $ccList = array_unique($ccList);
        if (count($ccList ?? [])) {
            $comment['comment'] .= "<br>cc: " . implode(',', $ccList);
        }

        $error = $task->addComment($comment);
        if (!is_numeric($error)) {
            $message = "Could not create comment. There was an error processing. The error returned is '$error'";
            $DEFAULT_ERROR[] = $message;
            $body .= _getView();
            break;
        }

        // Trigger MIS workflow
        $task->triggerMISWorkflow($user);
        $subject = "Tasks, Comment: #$id by " . $user->getFullname();
        //for tts tasks, cc all dept personnel
        if ($_MODULE == 'TTS') {
            $_PID = $task->getParentID();
            if ($_PID != 0) {
                $tts = new tldTTS($_PID);
                $category = $tts->getHeader();
                $domain = $tts->getDomain();
                if ($domain == 'ALL_DOMAINS') {
                    $bccList[] = 'mis@tld-america.com';
                    $bccList[] = 'mis@tld-europe.com';
                    $bccList[] = 'mis@tld-asia.com';
                } else {
                    $bccList[] = "mis@$domain";
                }
                $subject .= ', ' . $category['category'] . ' - ' . $domain;
                $comment['comment'].= sprintf("<br>bcc: %s", implode(', ', $bccList));
            }
        }

        $message = <<<EOF
${comment['comment']}<br>
<a href="http://{$_SERVER['HTTP_HOST']}/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$id">
Click here to go to Task.
</a>
<br>
EOF;

        // Send notification
        $task->notifyAssignee(
            $message,
            $subject,
            $ccList,
            $bccList
        );
        $body .= _getView();
        break;
    case 'category':
        if (!$user->isInGroup(['gg_MIS'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permissions';
            break;
        }
        if ($task->isPaused()) {
            $DEFAULT_ERROR[] = "ERROR: Cannot change category since Task #$id is in PAUSE!";
            break;
        }

        $category = '';
        if ($task->getParentID() !== null) {
            $tts = new tldTTS($task->getParentID());
            $category = $tts->itsHeader['category'];
        }
        // Get lists
        $listCC = $task->getCC();
        $listModules = tldUtils::optionsByKeyValue(tldModule::getList(), 'id', 'module');
        // Get form
        $form = new HTML_QuickForm('frmNewComment', 'post');
        $form->addElement('hidden', 'm[0]', 'tasks');
        $form->addElement('hidden', 'm[1]', 'task');
        $form->addElement('hidden', 'm[2]', 'category');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', 'Change Task category');
        if ($category === 'WEBSITE, INTRANET') {
            $form->addElement('select', 'module', 'Module', ['' => ''] + $listModules);
        }
        $form->addElement('select', 'cat', 'Category', ['' => '', 'A' => 'A', 'B' => 'B', 'C' => 'C']);
        $form->addElement('textarea', 'comment', 'Comment/Reason', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '6']);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('comment', 'Required field', 'required');
        $form->addRule('cat', 'Required field', 'required');
        $form->addRule('module', 'Required field', 'required');
        $form->setDefaults([
            'cat' => $task->getCategory(),
            'module' => $task->itsHeader['ticket_module_id'],
        ]);

        if (!$form->validate()) {
            $body = $form->toHTML();
            $body .= _getView();
            break;
        }

        $comment = $form->exportValues();
        $comment['poster'] = $user->getId();
        if ($comment['module'] !== $task->itsHeader['ticket_module_id']) {
            $task->update(['ticket_module_id' => $comment['module']]);
            $previousModule = $listModules[$task->itsHeader['ticket_module_id']];
            $newModule = $listModules[$comment['module']];
            $comment['comment'] .= "<br>Module updated from $previousModule to $newModule";
        }
        if ($comment['cat'] !== $task->getCategory()) {
            $e = $task->setCategory($comment['cat']);
            if (is_string($e)) {
                $DEFAULT_ERROR[] = "ERROR: Can not update the category. Reason: $e";
                break;
            }
            $comment['comment'] .= "<br>Category updated from {$task->getCategory()} to {$comment['cat']}";
        }
        $e = $task->addComment($comment);
        if (is_string($e)) {
            $DEFAULT_ERROR[] = "ERROR: Can not add comment. Reason: $e";
            break;
        }
        $body .= _getView();
        break;
    case 'move':
        $form = new HTML_QuickForm('frmTaskMove', 'post');
        $form->addElement('hidden', 'm[0]', 'tasks');
        $form->addElement('hidden', 'm[1]', 'task');
        $form->addElement('hidden', 'm[2]', 'move');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', 'Move Task Form');
        // Queues TTS
        $rows = tldTTS::getQueueList();
        foreach ($rows as $row) {
            $queues[$row['id']] = $row['domain'] . ' --> ' . $row['category'] . " [${row['id']}]";
        }
        // Project TTS
        $rows = tldTTS::getProjectList();
        foreach ($rows as $row) {
            $queues[$row['id']] = 'MIS Project--> ' . $row['domain'] . ' --> ' . substr($row['problem'], 0, 100) . " [${row['id']}]";
        }
        $form->addElement('select', 'spid', 'Select new Queue OR', $queues);
        $form->addElement('text', 'pid', 'type TTS# here');
        // If MIS, to manual records
        if ($user->isInGroup(['gg_MIS'])) {
            $form->addElement('header', 'title2', 'Or move Task to a module record');
            $form->addElement('select', 'module', 'Module', ['' => ''] +
                tldUtils::optionsByKeyValue(tldModule::getList(), 'module', 'module'));
            $form->addElement('text', 'refid', 'Ref#');
        }
        $form->addElement('submit', 'btnSubmit', 'Submit');

        if (!$form->validate()) {
            $body = $form->toHTML();
            $body .= _getView();
            break;
        }

        $p = $form->exportValues();
        $oldPID = $task->getParentID();
        $oldModule = $task->getModule();
        if (!empty($p['module']) && !empty($p['refid'])) {
            if ($p['module'] === 'CPA') {
                $cpa = new tldCPA($p['refid']);
                $cpaStatus = $cpa->getStatus();
                $statusList = array_keys(tldCPA::getStatusList());
                $orderStatus = (int)array_search($cpaStatus, $statusList);
                $modkID = tldModKey::insert(
                    [
                        'parent_id' => $task->itsID,
                        'module'    => 'TASK',
                        'type'      => $cpaStatus,
                        'key1'      => $orderStatus,
                    ]
                );
            }
            $e = $task->move($p['refid'], $p['module']);
            if (is_string($e)) {
                $DEFAULT_ERROR[] = "ERROR: Move error. Reason: $e";
                break;
            }
            // Trigger MIS workflow
            $task->triggerMISWorkflow($user);
            // Add comment to track change
            $task->addComment(
                [
                    'poster' => $user->getId(),
                    'comment' => "Task moved from $oldModule#$oldPID to {$p['module']}#{$p['refid']}",
                ]
            );
        } else {
            $newPID = $p['pid'] ? $p['pid'] : $p['spid'];
            $misTTS = (new tldTTS($p['spid']))->itsHeader;
            $newAssignee = $p['pid'] ? '' : $misTTS['owner'];
            if ($task->move($newPID, 'TTS', $newAssignee) == '') {
                $DEFAULT_ERROR[] = 'Move successful';
                // Trigger MIS workflow
                $task->triggerMISWorkflow($user);
                // Add comment to track change
                $comment = "Task moved from $oldModule#$oldPID to TTS#$newPID";
                $task->addComment(
                    [
                        'poster' => $user->getId(),
                        'comment' => $comment,
                    ]
                );
                if ('' !== $newAssignee) {
                    $people = new tldUser($newAssignee);
                    $peopleName = $people->getFullname();
                    $subject = "Tasks, Transfer: #$task->itsID to $peopleName";
                    $message = <<<EOF
$subject<br>
<b>Reason for TRANSFERRING:</b><br><hr>
$comment<br>
<hr>
Please log in to the TLD-GSE intranet and go to the calendar module to view your open tasks.
<a href="http://{$_SERVER['HTTP_HOST']}/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$task->itsID">
Click here to go to Task.
</a>
<br>
EOF;
                    $task->notifyAssignee($message, $subject);
                }
            }
        }
        $task->refresh();
        $body = _getView();
        break;
    case 'tag':
    case 'untag':
        $if = $m[2] === 'tag' ? 1000 : 1;
        $task->setIFactor($if);
        $task->addComment([
            'poster' => $user->getId(),
            'comment' => "The importance factor of this ticket has been set to $if",
        ]);
        header("Location: $php_self?m[0]=tasks&m[1]=task&&m[2]=view&id=$id");
        break;
    case 'pause':
        if (($_MODULE !== 'TTS' || (!$user->isInGroup(['ROLE_CIO', 'superuser']))) && ($_MODULE !== 'GWF' || $user->getID() !== (new tldGWF($task->getParentID()))->getAssignor())) {
            $DEFAULT_ERROR[] = "ERROR: You do not have permissions to do it.";
            break;
        }
        if ($task->isClosed()) {    //check if task is already closed
            $DEFAULT_ERROR[] = "ERROR: Cannot set to PAUSE since Task #$id is already closed!";
            break;
        }
        if ($task->isPaused()) {    //check if task is already closed
            $DEFAULT_ERROR[] = "ERROR: Cannot set to PAUSE since Task #$id is already in PAUSE!";
            break;
        }
        if ($task->getCategory() == 'C') {    //check if task is in C
            $DEFAULT_ERROR[] = "ERROR: Cannot set to PAUSE since Task #$id is in C category!";
            break;
        }
        if (!in_array($_MODULE, ['TTS', 'GWF'], true)) {
            $DEFAULT_ERROR[] = "ERROR: PAUSE function is for TTS and GWF only!";
            break;
        }
        if ($e = $task->pause()) {
            $DEFAULT_ERROR[] = "ERROR: There was a problem pause Task $id $e";
            $body .= _getView();
            break;
        }

        $subject = "Tasks, Paused: #$id of " . $task->getModule() . '#' . $task->getParentID() . ' paused by ' . $user->getFullname();
        $comment = "The TTS status has been moved to pause. Thanks for your proposal, your request is now listed but not planned. We will prioritize it and allocate resource later on.<br>Thanks for your understanding, MIS";
        $DEFAULT_ERROR[] = $subject;
        $p['comment'] = "Task #$id has been paused by " . $user->getFullname() . "<br>$comment";
        $header = $task->getHeader();
        $message = <<<EOF
$subject<br>
$comment<br><br>
If you want to see the task, please log in to the TLD-GSE intranet and go to the calendar module to view your open tasks.
<a href="http://{$_SERVER['HTTP_HOST']}/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$id">
Click here to go to Task.
</a><br>
EOF;
        // Send notification
        $task->notifyAssignee($message, $subject, $ccList, $bccList);
        $task->addComment($p);
        header("Location: $php_self?m[0]=tasks&m[1]=task&&m[2]=view&id=$id");
        break;
    case 'unpause':
        if (($_MODULE !== 'TTS' || (!$user->isInGroup(['ROLE_CIO', 'superuser']))) && ($_MODULE !== 'GWF' || $user->getID() !== (new tldGWF($task->getParentID()))->getAssignor())) {
            $DEFAULT_ERROR[] = "ERROR: You do not have permissions to do it.";
            break;
        }
        if ($task->isClosed()) {    //check if task is already closed
            $DEFAULT_ERROR[] = "ERROR: Cannot UNPAUSE since Task #$id is already closed!";
            break;
        }
        if (!$task->isPaused()) {
            $DEFAULT_ERROR[] = "ERROR: Cannot UNPAUSE since Task #$id is not in PAUSE!";
            break;
        }
        if ($e = $task->unpause()) {
            $DEFAULT_ERROR[] = "ERROR: There was a problem unpause Task $id $e";
            $body .= _getView();
            break;
        }

        $subject = "Tasks, Unpaused: #$id of " . $task->getModule() . '#' . $task->getParentID() . ' unpaused by ' . $user->getFullname();
        $p['comment'] = "Task #$id has been unpaused by " . $user->getFullname();
        $task->addComment($p);
        $DEFAULT_ERROR[] = $subject;
        $header = $task->getHeader();
        $message = <<<EOF
$subject<br>
$comment<br><br>
If you want to see the task, please log in to the TLD-GSE intranet and go to the calendar module to view your open tasks.
<a href="http://{$_SERVER['HTTP_HOST']}/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$id">
Click here to go to Task.
</a>
<br>
EOF;
        // Send notification
        $task->notifyAssignee($message, $subject, $ccList, $bccList);
        $DEFAULT_ERROR[] = 'Task has been set to unpaused';
        header("Location: $php_self?m[0]=tasks&m[1]=task&&m[2]=view&id=$id");
        $body .= _getView();
        break;
    case 'reopen':
        if (!$task->isClosed()) {    //check if task is already closed
            $DEFAULT_ERROR[] = "ERROR: Cannot REOPEN since Task #$id is not closed!";
            break;
        }
        if ($task->getModule() == 'SEQ') {
            $DEFAULT_ERROR[] = "ERROR: Cannot REOPEN SEQ Task!";
            break;
        }
        if ($task->getField('dt_closed') < date('Y-m-d', strtotime('-30 days'))) {
            $DEFAULT_ERROR[] = "ERROR: Cannot REOPEN Task which was closed more than 30 days!";
            break;
        }
        if (!\in_array($task->getModule(), ['USER', 'VWC'], true)) {
            $module = 'tld' . $task->getModule();
            if (class_exists($module)) {
                try {
                    $instance = new $module($task->getParentID());
                } catch (Exception $e) {
                    if ('NCR' !== $task->getModule()) {
                        $DEFAULT_ERROR[] = 'ERROR: REOPEN function is not available for module ' . $task->getModule();
                        break;
                    }
                    global $kernel;
                    try {
                        $client = $kernel->getContainer()->get(Client::class);
                        $nonConformity = $client->find('quality/non_conformities', $task->getParentID());
                        if (in_array($nonConformity['status'], ["CLOSED", "REJECTED"])) {
                            $DEFAULT_ERROR[] = "ERROR: Cannot reopen task for the closed or rejected module!";
                            break;
                        }
                    } catch (Exception $e) {
                        return 'ERROR: Could not get API. Reason : '.$e->getMessage();
                    }
                }

                if ('NCR' !== $task->getModule()) {
                    if (!method_exists($instance, 'getStatus')) {
                        $DEFAULT_ERROR[] = 'ERROR: REOPEN function is not available for module ' . $task->getModule();
                        break;
                    }

                    if (in_array($instance->getStatus(), ["CLOSED", "REJECTED"])) {
                        $DEFAULT_ERROR[] = "ERROR: Cannot reopen task for the closed or rejected module!";
                        break;
                    }
                }
            }
        }
        // Get form
        $form = new HTML_QuickForm('frmNewComment', 'post');
        $form->addElement('hidden', 'm[0]', 'tasks');
        $form->addElement('hidden', 'm[1]', 'task');
        $form->addElement('hidden', 'm[2]', 'reopen');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', 'Reopen Task');
        $form->addElement('textarea', 'comment', 'Comment/Reason',
            ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '6']);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('comment', 'Required field', 'required');

        if (!$form->validate()) {
            $body = $form->toHTML();
            $body .= _getView();
            break;
        }

        $comment = $form->exportValues();
        $comment['poster'] = $user->getId();
        if ($e = $task->changeStatus('OPEN')) {
            $DEFAULT_ERROR[] = "ERROR: There was a problem reopen Task $id $e";
            $body .= _getView();
            break;
        }
        $subject = "Tasks, Reopened: #$id of ".$task->getModule().'#'.$task->getParentID().' reopened by '.$user->getFullname();
        $comment['comment'] .= "Task #$id has been reopened by ".$user->getFullname();
        $task->refresh();
        $task->addComment($comment);
        $DEFAULT_ERROR[] = $subject;
        $header = $task->getHeader();
        $message = <<<EOF
$subject<br>
{$comment['comment']}<br>
If you want to see the task, please log in to the TLD-GSE intranet and go to the calendar module to view your open tasks.
<a href="http://{$_SERVER['HTTP_HOST']}/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$id">
Click here to go to Task.
</a>
<br>
EOF;
        // Send notification
        $task->notifyAssignee($message, $subject, $ccList, $bccList);
        $DEFAULT_SUCCESS[] = 'Task has been reopened';
        header("Location: $php_self?m[0]=tasks&m[1]=task&&m[2]=view&id=$id");
        $body .= _getView();
        break;
///////////////////////////////////////////////////////////////////////
//                      SEQUENCE FUNCTIONS                           //
///////////////////////////////////////////////////////////////////////

    case 'accept':
        // Check if sequence is closed
        if ($seq->isClosed()) {
            $DEFAULT_ERROR[] = 'ERROR: Cannot ACCEPT a CLOSED sequence';
            break;
        }
        // Execute PRE script sequence
        $pre_action_script_error = _preSequenceActionScripts($seq);
        if (is_string($pre_action_script_error)) {
            $DEFAULT_ERROR[] = "ERROR: There was a problem running the Pre Sequence Action Script<br/>Reason: $pre_action_script_error";
            break;
        }
        // Get the actual node & assignee list
        $node = $seq->getCurrentNode();
        // If SINGLE LEVEL
        if ($seq->getMode() == 'SINGLE_LEVEL') {
            $canAcceptList = [$seq->itsHeader['assignee_fullname']];
        } // If USER LEVEL or TEMPLATE
        else {
            $canAcceptList = $node->getAssigneeList();
            $nextnode = $seq->getNextNode();
            if ($nextnode !== false) {
                if ($seq->getMode() == 'TEMPLATE' && (int) $node->getType() > 0) {
                    $userassignee = new tldUser($seq->getCurrentAssignor());
                    $super = new tldUser($userassignee->getSupervisor());

                    $canAcceptList = (int) $node->getType() === 1 ? [$super->getID() => $super->getFullname()] + $canAcceptList : [$super->getID() => $super->getFullname()];
                }
            }
        }
        // Check if user allowed to sign SEQ
        $currentStep = $seq->getCurrentStep();
        if (!$seq->canSign($user->getID())) {
            $DEFAULT_ERROR[] = 'ERROR: You are not allowed to ACCEPT this sequence';
            // if not allowed list people who can accept
            $listUser = implode('<br>', $canAcceptList ?? []);
            $body .= <<<EOF
Only the following person(s):<br>$listUser<br>
can accept at this level, please transfer this Sequence back to one in the list.
EOF;
            break;
        }

        // Get the form
        $form = new HTML_QuickForm('frmAccept', 'post');
        $form->addElement('hidden', 'currentStep', $currentStep);
        $form->addElement('hidden', 'm[0]', 'tasks');
        $form->addElement('hidden', 'm[1]', 'task');
        $form->addElement('hidden', 'm[2]', 'accept');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('hidden', 'extras', $extras);
        $form->addElement('header', 'title', "Accept SEQ#$id");
        // If not on last step, display assignee list for next step
        $nextNode = $seq->getNextNode();
        if ($nextNode !== false) {
            $assigneeList = $nextNode->getAssigneeList();
            if ((int) $seq->getTemplate()->itsID === 132 && (int) $nextNode->getStep() === 2) {
                $super = new tldUser($userassignee->getSupervisor());
                $assigneeList = [$super->getID() => $super->getFullname()];
            }
            if ($seq->getMode() == 'TEMPLATE' && $nextNode->getType() > 0) {
                $userassignee = new tldUser($seq->getCurrentAssignor());
                $super = new tldUser($userassignee->getSupervisor());
                $assigneeList = [$super->getID() => $super->getFullname()] + $assigneeList;
            }
            $form->addElement('header', 'stepDsca', <<<EOF
Next Step Description ->
<ul>
  <li>Step #: {$nextNode->itsDetails['step']}</li>
  <li>Action: {$nextNode->itsDetails['dsca']}</li>
</ul>
EOF
            );
            // Check if assignee list is empty
            if (count($assigneeList ?? []) == 0) {
                $error = "ERROR: No assignee users found for step {$nextNode->itsDetails['step']}";
                $DEFAULT_ERROR[] = $error;
                error_log($error.' '.$php_self."<br>");
                break;
            }

            $form->addElement('select', 'assignee', 'Assignee', $assigneeList);
            $form->addRule('assignee', 'This is a required field', 'required');

            $assignee = null;
            if (array_key_exists($user->getID(), $assigneeList)) {
                $assignee = $user->getID();
            } elseif (array_key_exists($user->getSupervisor(), $assigneeList)) {
                $assignee = $user->getSupervisor();
            } elseif ($seq->getMode() !== 'USER_LEVEL') {
                if (array_key_exists($defaultAssignee = $nextNode->getDefaultAssigneeByBU($user->getBUID()), $assigneeList)) {
                    $assignee = $defaultAssignee;
                }
            }

            $assigneeList = ['' => ''] + $assigneeList;

            if (null !== $assignee) {
                $form->setDefaults(['assignee' => $assignee]);
            }

        } else {
            $form->addElement('header', 'stepDsca', 'Last step, will be closed after submit');

        }
        $form->addElement('textarea', 'comment', 'Comment',
            ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']
        );
        $ams = &$form->addElement('advmultiselect', 'cc_users', null,
            tldDirectory::getUserlist('smartyOptions'),
            ['size' => 10, 'class' => 'pool', 'style' => 'width:500px;']
        );
        $ams->setLabel(['CC others... (OPTIONAL)', 'Addressbook', 'CC']);
        $ams->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
        $ams->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
        $form->addElement('file', 'file', 'Attachment');
        $form->addElement('submit', 'btnSubmit', 'Submit', ['class' => 'disablesubmit']);
        // Default values
        $form->setDefaults(['cc_users' => $seq->getCC()]);

        if (!$form->validate()) {
            if (
                $seq->getTemplate() instanceof tldSEQTpl
                && in_array($seq->getTemplate()->getName(), ['hr.user.shopfloor_light.delete', 'hr.user.delete'])
                && new DateTime($seq->itsHeader['due_date']) > new DateTime()
            ) {
                $DEFAULT_ERROR[] = '<b>WARNING: USER IS LEAVING ON ' . $seq->itsHeader['due_date'] . '</b>';
            }
            $body .= $form->toHTML();
            break;
        }

        $vars = $form->exportValues();

        // Avoid sending emails and having the sequence go to the next step on browser refresh 'resend form'
        if ($vars['currentStep'] === $seq->getCurrentStep()) {
            // Check the limit of cc users
            $numCC = count($vars['cc_users'] ?? []);
            $maxCC = 25;
            if ($numCC > $maxCC) {
                $DEFAULT_ERROR[] = "ERROR: Can not copy to more than $maxCC persons!";
                $body = $form->toHTML();
                break;
            }
            // Run the preCloseScript
            $pre_script_error = _preCloseScripts($seq);
            if (is_string($pre_script_error)) {
                $DEFAULT_ERROR[] = "ERROR: There was a problem running the end of sequence Pre-Closing script. Reason: $pre_script_error";
                break;
            }
            // Prepare data
            $vars['poster'] = $user->getID();
            $comment = $vars['comment'];
            // Get the file
            $file = $form->getElement('file');
            $vars['file_info'] = $file->getValue();

            $ccList = [];
            switch ($_MODULE) {
                case 'BP':
                    $bp = new tldBP($parentID);
                    if (null !== $meap = $bp->getMEAP()) {
                        foreach ($bp->getTasks('ALL') as $member) {
                            $ccList[] = $member['assignee_email'];
                        }
                    }
                    break;
            }
            if ($numCC > 0) {
                foreach ($vars['cc_users'] as $userid) {
                    $ccUser = new tldUser($userid);
                    $ccList[] = $ccUser->getEmail();
                    // Add users to the SEQ cc list
                    if (!in_array($userid, $seq->getCC())) {
                        $seq->addCC($userid);
                    }
                }
            }
            $ccList = array_unique($ccList);
            // Add the list ccied in comment task
            if (count($ccList ?? [])) {
                $vars['comment'] .= "<br>cc: " . implode(',', $ccList);
            }
            // ACCEPT the SEQ
            $error = $seq->accept($vars);
            // Do transfer
            // --- if next node, transfer to next assignee
            if ($nextNode !== false) {
                $error .= $seq->transfer($vars['assignee']);
            } else {
                // --- else, if USER_LEVEL or TEMPLATE, transfer to Assignor
                switch ($seq->itsMode) {
                    case 'USER_LEVEL':
                        $error .= $seq->transfer($seq->getAssignor());
                        break;
                    default:
                        if ($seq->getTPLNO() == 0) {
                            // --- SINGLE_LEVEL, transfer to user
                            $error .= $seq->transfer($user->getID());
                        } else {
                            $error .= $seq->transfer($seq->getAssignor());
                        }
                        break;
                }
            }
            if ($error) {
                $DEFAULT_ERROR[] = $error;
                break;
            }
            // Notify Assignee and CC list
            $user_fullname = $user->getFullname();
            $message = <<<EOF
Sequence #$id was ACCEPTED by $user_fullname and now requires your attention.<br>
$comment <br>
<a href="http://{$_SERVER['HTTP_HOST']}/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$id">
Click here to see sequence.</a>
EOF;
            $seq->notifyAssignee(
                $message,
                "Sequence, Step: SEQ#$id requires your attention",
                $ccList,
                $bccList
            );
            // Check if now closed
            if ($seq->isClosed()) {
                $post_script_error = _postCloseScripts($seq);
                if (is_string($post_script_error)) {
                    $DEFAULT_ERROR[] = "ERROR: There was a problem running the end of sequence Post-Closing script, error returned was $post_script_error";
                }
                $DEFAULT_ERROR[] = 'Sequence is now closed.';
                $seq->notifyAssignee(
                    "SEQ#$id CLOSED<br><a href=\"http://{$_SERVER['HTTP_HOST']}/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$id\">Click here to see sequence</a><hr>".
                    $seq->getTask(),
                    "Sequences, Closed: SEQ#$id is now closed."
                );
            }
            // Post action
            $post_action_error = _postSequenceActionScripts($seq);
            if (is_string($post_action_error)) {
                $DEFAULT_ERROR[] = "ERROR: There was a problem running the post action script. Reason: $post_action_error";
            }
        }

        // Refresh SEQ header and view
        $seq->refresh();
        $body .= _getView();
        break;
    case 'reject':
        // Check if sequence is closed
        if ($seq->isClosed()) {
            $DEFAULT_ERROR[] = 'ERROR: Cannot REJECT a CLOSED sequence';
            break;
        }
        // Execute PRE Action script sequence
        $pre_action_script_error = _preSequenceActionScripts($seq);
        if (is_string($pre_action_script_error)) {
            $DEFAULT_ERROR[] = "ERROR: There was a problem running the Pre Sequence Action Script<br/>Reason: $pre_action_script_error";
            break;
        }

        // Get previous node
        $prevNode = $seq->getPreviousNode();
        // If SINGLE LEVEL
        if ($seq->getMode() == 'SINGLE_LEVEL') {
            $canRejectList = [$seq->itsHeader['assignee_fullname']];
        } // If USER LEVEL or TEMPLATE
        else {
            if ($prevNode === false) {
                $DEFAULT_ERROR[] = 'ERROR: Can not REJECT, at beginning of sequence';
                break;
            }
            // Get the actual node & assignee list
            $node = $seq->getCurrentNode();
            $canRejectList = $node->getAssigneeList();
        }

        // Check if user allowed to sign SEQ
        if (!$seq->canSign($user->getID())) {
            $DEFAULT_ERROR[] = 'ERROR: You are not allowed to REJECT this sequence';
            // if not allowed, list people who can reject
            $listUser = implode('<br>', $canRejectList);
            $body .= <<<EOF
Only the following person(s):<br>$listUser<br>
can reject at this level, please transfer this Sequence back to one in the list.
EOF;
            break;
        }

        // Get form
        $form = new HTML_QuickForm('frmReject', 'post');
        $form->addElement('hidden', 'm[0]', 'tasks');
        $form->addElement('hidden', 'm[1]', 'task');
        $form->addElement('hidden', 'm[2]', 'reject');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('hidden', 'extras', $extras);
        $form->addElement('header', 'title', "Reject SEQ#$id");
        // If previous node exists, select assignee
        if ($prevNode !== false) {
            $assignees = $prevNode->getAssigneeList();
            $form->addElement('header', 'stepDsca', <<<EOF
Next Step Description ->
<ul>
  <li>Step #: {$prevNode->itsDetails['step']}</li>
  <li>Action: {$prevNode->itsDetails['dsca']}</li>
</ul>
EOF
            );
            $form->addElement('select', 'assignee', 'Assignee', $assignees);
            $form->addRule('assignee', 'This is a required field.', 'required');
        }
        $form->addElement('textarea', 'comment', 'Reason',
            ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']
        );
        $ams = &$form->addElement('advmultiselect', 'cc_users', null,
            tldDirectory::getUserlist('smartyOptions'),
            ['size' => 10, 'class' => 'pool', 'style' => 'width:500px;']
        );
        $ams->setLabel(['CC others... (OPTIONAL)', 'Addressbook', 'CC']);
        $ams->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
        $ams->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
        $form->addElement('file', 'file', 'Attachment');
        $form->addElement('submit', 'btnSubmit', 'Submit', ['class' => 'disablesubmit']);
        // Default values
        $form->setDefaults(
            [
                'cc_users' => $seq->getCC(),
                'assignee' => $user->getID(),
            ]
        );

        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }

        $vars = $form->exportValues();
        // Check the limit of cc users
        $numCC = count($vars['cc_users'] ?? []);
        if ($numCC > 15) {
            $DEFAULT_ERROR[] = 'ERROR: Can not copy to more than 15 persons!';
            $body = $form->toHTML();
            break;
        }
        // Execute PRE reject script
        $pre_script_error = _preRejectScripts($seq);
        if (is_string($pre_script_error)) {
            $DEFAULT_ERROR[] = "ERROR: There was a problem running the end of sequence Pre-Reject script<br/>Reason: $pre_script_error";
            break;
        }
        // Prepare data
        $vars['poster'] = $user->getID();
        $comment = $vars['comment'];
        // Get the file
        $file = $form->getElement('file');
        $vars['file_info'] = $file->getValue();
        $ccList = [];

        if ($numCC > 0) {
            foreach ($vars['cc_users'] as $userid) {
                $ccUser = new tldUser($userid);
                $ccList[] = $ccUser->getEmail();
                // Add users to the SEQ cc list
                if (!in_array($userid, $seq->getCC())) {
                    $seq->addCC($userid);
                }
            }
        }
        $ccList = array_unique($ccList);
        // Add the list ccied in comment task
        if (count($ccList ?? [])) {
            $vars['comment'] .= "<br>cc: ".implode(',', $ccList);
        }
        // REJECT the SEQ
        $error = $seq->reject($vars);
        // Transfer
        if ($vars['assignee']) {
            $error .= $seq->transfer($vars['assignee']);
        } else {
            $error .= $seq->transfer($seq->getAssignor());
        }
        if ($error) {
            $DEFAULT_ERROR[] = $error;
            break;
        }
        // Notify
        $user_fullname = $user->getFullname();
        $message = <<<EOF
Sequence #$id was REJECTED by $user_fullname and now requires your attention.<br>
$comment <br>
<a href="http://{$_SERVER['HTTP_HOST']}/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$id">Click here to see sequence.</a>
EOF;
        $seq->notifyAssignee(
            $message,
            "Sequences, Step: SEQ#$id REJECTED",
            $ccList,
            $bccList
        );
        // Post action
        $post_action_error = _postSequenceActionScripts($seq);
        if (is_string($post_action_error)) {
            $DEFAULT_ERROR[] = "ERROR: There was a problem running the post action script. Reason: $post_action_error";
        }
        // Refresh SEQ header and view
        $seq->refresh();
        $body .= _getView();
        break;
    case 'cancel':
        // Check if sequence is closed
        if ($seq->isClosed()) {
            $DEFAULT_ERROR[] = 'ERROR: Cannot CANCEL a CLOSED sequence';
            break;
        }
        // Execute PRE Action script sequence
        $canCancel = [];
        $pre_action_script_error = _preSequenceActionScripts($seq);
        if (is_string($pre_action_script_error)) {
            $DEFAULT_ERROR[] = "ERROR: There was a problem running the Pre Sequence Action Script<br/>Reason: $pre_action_script_error";
            break;
        }
        // Check permissions to cancel
        // -- Assignor always allowed
        $canCancel[] = $seq->getAssignor();
        // -- Depending of sequence mode
        switch ($seq->getMode()) {
            case 'USER_LEVEL':
                // For user level, anyone in process can cancel
                $nodeList = tldSEQUserNode::byParentID($id);
                foreach ($nodeList as $nodeVal) {
                    $canCancel[] = $nodeVal['uid'];
                }
                $reason = 'Only step approvers can cancel this user level sequence';
                break;
            default:
                // For single level sequence, only assignor (exit right away)
                if ($seq->getTPLNO() == 0) {
                    switch ($_MODULE) {
                        case 'BP':
                            // Allow certain groups to cancel - Task#379417
                            if ($user->isInGroup(['role_EM', 'role_COO', 'role_CEO', 'role_RCOO', 'role_RCEO', 'role_GTD', 'role_GPID', 'role_CFO', 'GG_EXCOM'])) {
                                $canCancel[] = $user->getID();
                            }
                            break;
                        default:
                            $reason = 'Only assignor can cancel this single level sequence';
                            break;
                    }
                    break;
                }
                // Classic sequence, only people in levels 2 and up can cancel
                for ($i = 2; $i < $seq->getNumNodes() + 1; ++$i) {
                    $node = $seq->getNode($i);
                    foreach ($node->getAssigneeList() as $uid => $val) {
                        $canCancel[] = $uid;
                    }
                }
                $reason = 'Only users in steps above level 1 can cancel';
                break;
        }
        if (!in_array($user->getID(), $canCancel) && !$user->isInGroup('superuser')) {
            $DEFAULT_ERROR[] = "ERROR: You are not allowed to CANCEL this sequence<br>Reason: $reason";
            break;
        }

        // Get form
        $form = new HTML_QuickForm('frmCancel', 'post');
        $form->addElement('hidden', 'm[0]', 'tasks');
        $form->addElement('hidden', 'm[1]', 'task');
        $form->addElement('hidden', 'm[2]', 'cancel');
        $form->addElement('hidden', 'id', $id);
        if (($seq->getTemplate()->getName() === 'sales.Customer.Validation' || $seq->getTemplate()->getName() === 'sales.new.customer') && isset($_REQUEST['cancelOnlySeq']) && (bool)$_REQUEST['cancelOnlySeq']) {
            $form->addElement('hidden', 'cancelOnlySeq', true);
        }
        $form->addElement('hidden', 'extras', $extras);
        $form->addElement('header', 'title', 'Cancel');
        $form->addElement('textarea', 'comment', 'Reason',
            ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']);
        $ams = &$form->addElement('advmultiselect', 'cc_users', null,
            tldDirectory::getUserlist('smartyOptions'),
            ['size' => 10, 'class' => 'pool', 'style' => 'width:500px;']
        );
        $ams->setLabel(['CC others... (OPTIONAL)', 'Addressbook', 'CC']);
        $ams->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
        $ams->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
        $form->setDefaults(['cc_users' => $seq->getCC()]);
        $form->addElement('submit', 'btnSubmit', 'Submit', ['class' => 'disablesubmit']);

        if (!$form->validate()) {
            $body = $form->toHTML();
            $body .= _getView();
            break;
        }

        $vars = $form->exportValues();
        $vars['poster'] = $user->getID();
        $comment = $vars['comment'];
        // Check the limit of cc users
        $numCC = count($vars['cc_users'] ?? []);
        if ($numCC > 15) {
            $DEFAULT_ERROR[] = 'ERROR: Can not copy to more than 15 persons!';
            $body = $form->toHTML();
            break;
        }
        // Execute PRE cancellation script
        $pre_script_error = _preCancelScripts($seq);

        if (is_string($pre_script_error)) {
            $DEFAULT_ERROR[] = "ERROR: There was a problem running the end of sequence Pre-Cancel script<br/>Reason: $pre_script_error";
            break;
        }
        $ccList = [];

        if ($numCC > 0) {
            foreach ($vars['cc_users'] as $userid) {
                $ccUser = new tldUser($userid);
                $ccList[] = $ccUser->getEmail();
                // Add users to the SEQ cc list
                if (!in_array($userid, $seq->getCC())) {
                    $seq->addCC($userid);
                }
            }
        }
        if ('BP' === $seq->getModule()) {
            $bp = new tldBP($parentID);
            if (null !== $meap = $bp->getMEAP()) {
                foreach ($bp->getTasks('ALL') as $member) {
                    $ccList[] = $member['assignee_email'];
                }
            }
        }
        $ccList = array_unique($ccList);
        // Add the list ccied in comment task
        if (count($ccList ?? [])) {
            $vars['comment'] .= "<br>cc: ".implode(',', $ccList);
        }
        // CANCEL the sequence
        $error = $seq->cancel($vars);
        if (is_string($error)) {
            $DEFAULT_ERROR[] = $error;
            break;
        }
        $user_fullname = $user->getFullname();
        $message = <<<EOF
Sequence #$id was CANCELLED by $user_fullname.<br>
$comment <br>
<a href="http://{$_SERVER['HTTP_HOST']}/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$id">Click here to see sequence.</a>
EOF;
        $seq->notifyAssignee(
            $message,
            "Sequences, Step: SEQ#$id CANCELLED",
            $ccList,
            $bccList
        );
        // Post action
        $post_action_error = _postSequenceActionScripts($seq, true);
        if (is_string($post_action_error)) {
            $DEFAULT_ERROR[] = "ERROR: There was a problem running the post action script. Reason: $post_action_error";
        }
        // Refresh SEQ header and view
        $seq->refresh();
        $body .= _getView();
        break;
    case 'links' :
        $DEFAULT_MENU.=<<<EOF
	<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="/en/private/common/index.php?m[0]=links&m[1]=form&m[2]=newLink&module=SEQ&parent_id=$id">Add New Link</a>
EOF;
        $DEFAULT_TITLE .= "\Links";
        $report = new tldReportColumnar(tldModLink::byParent($id, 'SEQ'),
            ["xItems" => [
                "id" => "ID#",
                "type" => "Module",
                "item" => "Ref#",
                "dsca" => "Description"
            ],
                "title" => "Links FROM Here...",
                "links" => ["id" => "/en/private/common/index.php?m[0]=links&m[1]=view&m[2]=redirect&erp=$erp&id="],
                "functions" => [
                    "Delete" => [
                        "url" => "/en/private/common/index.php?m[0]=links&m[1]=view&m[2]=delconf&id=",
                        "param" => ['id' => 'id'],
                        "img" => "/shared/icons/application/delete.png",
                    ],
                ],
            ]
        );
        $body .= $report->fetch();
        $report = new tldReportColumnar(tldModLink::byItem($id, 'SEQ'),
            ["xItems" => [
                "id" => "ID#",
                "module" => "Module",
                "parent_id" => "Ref#",
                "dsca" => "Description"
            ],
                "title" => "Links TO Here...",
                "links" => ["id" => "/en/private/common/index.php?m[0]=links&m[1]=view&m[2]=redirect&reversed=1&erp=$erp&id="],
                "functions" => [
                    "Delete" => [
                        "url" => "/en/private/common/index.php?m[0]=files&m[1]=view&m[2]=delconf&id=",
                        "param" => ['id' => 'id'],
                        "img" => "/shared/icons/application/delete.png",
                    ],
                ],
            ]
        );
        $body .= $report->fetch();
    break;
}

// local function containing the Post closing scripts.
// all those actions will be done after the closing of the SEQ
function _postCloseScripts($seq)
{
    /** @var tldSEQ $seq */
    global $user, $extras, $DMS_URL;
    $xtras = unserialize(base64_decode($extras));
    $tpl = $seq->getTemplate();
    $seqTplName = $tpl->getName();
    $p = $seq->getCloseParams();
    $seqid = $seq->itsID;

    switch ($seqTplName) {
        case 'hr.user.new':
        case 'alvest.hr.new.user':
            // Create task to read mis DMS procedure
            $taskDesc = <<<EOF
Welcome to Alvest Group !

Please make sure to read the following IT Procedures and Policies:

- IT TOOLS Fair Usage Policy - DMS219
- ALVEST IT user inception Training - DMS2258
- Authorized Software Policy - DMS275
- MIS Missions and Activities - DMS6222
- Code of Ethics (<a href="$DMS_URL/index.php?m[0]=view&id=236">EN</a> / <a href="$DMS_URL/index.php?m[0]=view&id=238">FR</a> / <a href="$DMS_URL/index.php?m[0]=view&id=833">ZH</a>)

You will find them in our DMS System here: <a href="$DMS_URL/index.php">$DMS_URL/index.php</a>

A link is also present on the left side section of the ALVEST Intranet home page.

Please close this task when all set, we will consider this as your confirmation that those were read and well understood.

Thanks & Best Regards
EOF;
            if (empty($seq->getParentID())) {
                return 'NO User ID found!';
            }
            $userCreated = new tldUser($seq->getParentID());
            $enabledAt = !empty($userCreated->getHeader()['enable_at'])
                ? new DateTime($userCreated->getHeader()['enable_at'])
                : null;

            if (
                $userCreated->isDisabled() &&
                $enabledAt !== null &&
                $enabledAt < new DateTime('today')
            ) {
                return 'Task not created: user is disabled and considered as having left.';
            }

            $userdetail = $userCreated->getDetails();
            if ($userdetail['fct_id'] == 30 || $userdetail['fct_id'] == 49) {
                break;
            }
            $e = tldTask::insert(
                $seq->getParentID(),
                [
                    'assignee' => $seq->getParentID(),
                    'assignor' => $userCreated->getSupervisor(),
                    'task' => TldDatabase::escape($taskDesc),
                ],
                'USER'
            );
            if (is_string($e)) {
                return 'Can not create task to user for reading IT Procedures and Policies';
            }
            // Send notification
            $taskNew = new tldTask($e);
            $assignee = new tldUser($taskNew->getAssignee());
            $assignor = new tldUser($taskNew->getAssignor());
            $subject = "Tasks, New: #$e opened - IT Procedures and Policies";
            $message = <<<EOF
Task #$e has been assigned to {$assignee->getFullname()}.<br>
Please log in to the TLD-GSE intranet and go to the calendar module to view your open tasks.
<a href="https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$e">
Click here to go to Task.
</a>
<br>
EOF;
            $taskNew->notifyAssignee($message, $subject);
            break;
        case 'sales.survey.campaign.approval':
            $seqDetails = $seq->itsHeader;
            global $kernel;
            try {
                $client = $kernel->getContainer()->get(Client::class);
            } catch (Exception $e) {
                return 'ERROR: Could not get API Client. Reason : '.$e->getMessage();
            }

            try {
                $client->request('surveys/campaigns', $seqDetails['parent_id'], 'send', 'PATCH');
            } catch (Exception $e) {
                return 'ERROR: Could not send emails. Reason : '.$e->getMessage();
            }
            break;
        case 'sales.extranetuser.approval':
        case 'sales.extranet.approval':
            // Automate extra fields
            $p['userid'] = $p['email'];
            $p['enable'] = 'Y';
            $p['seqid'] = $seqid;

            global $kernel;

            try {
                $client = $kernel->getContainer()->get(Client::class);
            } catch (Exception $e) {
                return "ERROR: Could not get Client. Reason : ".$e->getMessage();
            }

            try {
                $extranetUser = $client->findOneBy('sales/extranet_users', ['email' => $p['email']]);
            } catch (Exception $e) {
                return "ERROR: Could not get Extranet User. Reason : ".$e->getMessage();
            }

            $xuProfile = $extranetUser['extranetUserProfile'];
            $xuProfile['archived'] = false;
            unset($xuProfile['customer'], $xuProfile['erpLocation'], $xuProfile['country'], $xuProfile['phones']);
            $extranetUser['hidden'] = false;
            $extranetUser['disabled'] = false;
            $extranetUser['extranetUserProfile'] = $xuProfile;

            try {
                $xuFinal = $client->save('sales/extranet_users', $extranetUser);
            } catch (ClientException $e) {
                $errors = json_decode($e->getResponse()->getContent(), true);
                return 'ERROR: Could not edit Extranet User. Reason : '.$errors['hydra:description'];
            }

            $newID = $xuFinal['legacyId'];
            $message .= <<<EOF
"<br>Extranet user has now been created in database. Pls check Customers, CRTs and Roles linked.
<a href="https://www.tld-gse.com/en/private/sales_service/sales.php?m[0]=extranet&m[1]=view&id=$newID">User#$newID</a>
EOF;
            // Notify the assigne regarding result
            $seq->notifyAssignee(
                $message,
                "Partners Site Application - ${p['email']}"
            );
            break;
        case 'odp.lategt.approval.gceo':
        case 'odp.lategt.approval.gcoo':
        case 'odp.lategt.approval.rceo':
            // DEV WARNING - Check logic is DUPLICATED in product_support/odp/odp.logic.inc.php for Quick Edit Actual GT update
            $dgt_act = $p['dgt_act'];
            $er_id = $seq->getParentID();
            $er = new tldEquipment($er_id);
            if ($er->isEmpty()) {
                return "ERROR: Unit {$er->getSN()} not found";
            }

            try {
                $dt = new DateTime($dgt_act);
            } catch (Exception $e) {
                return "ERROR: Unit {$er->getSN()} not updated - Wrong data submitted for Actual GT Date -> {$dgt_act}";
            }
            // Check if all crabs are closed
            if (count(tldCRAB::byQuery("erid=$er_id AND status<>'CLOSED'")) > 0) {
                return "ERROR: Unit {$er->getSN()} not updated - CRAB(s) still not CLOSED";
            }
            // check if GT >= YT
            if ($er->getYT() != '0000-00-00' && (new DateTime($er->getYT()) > $dt)) {
                return "ERROR: Unit {$er->getSN()} not updated - Actual GT Date {$dgt_act} < YT Date {$er->getYT()}";
            }
            // Check if GT < shipped date
            $date_shipped = new DateTime($er->getShipDate());
            $interval = $dt->diff($date_shipped);
            if ($er->getShipDate() != '0000-00-00' && $interval->format('%R') == '-') {
                return "ERROR: Unit {$er->getSN()} not updated - Actual GT Date {$dgt_act} > EXW Ship Date {$er->getShipDate()}";
            }
            // Check if children are GT
            $childrenEquipments = tldEquipment::byConstraints(" service.parent_id=$er_id AND service.dgt_act = '0000:00:00' ");

            if (count($childrenEquipments ?? [])) {
                return "ERROR: Unit {$er->getSN()} has an ER combined which has not been GT.";
            }
            // Prepare Email
            $row = tldODP::byConstraints(['er.id' => $er_id]);
            // SOR# & SOL#
            if ($row[0]['sorid']) {
                $title_sor = " SOR#{$row[0]['sorid']}";
            }
            if ($row[0]['solid']) {
                $title_sol = " SOL#{$row[0]['solid']}";
            }
            $subject = "ODP Update -$title_sor$title_sol SN#{$row[0]['sn']} - Actual GT Change";
            // Customer Name
            if ($row[0]['cu_nama']) {
                $title_cust = " for customer {$row[0]['cu_nama']}";
            }
            // Project# Case
            if ($row[0]['t_prno']) {
                $title_prno = ", Project#: {$row[0]['t_prno']}";
            }
            // Work Order# Case
            if ($row[0]['t_pdno']) {
                $title_pdno = ", Work Order#: {$row[0]['t_pdno']}";
            }
            $msg = <<<EOF
Unit SN <a href="https://www.tld-gse.com/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=search&m[2]=bySN&sn={$row[0]['sn']}">{$row[0]['sn']}</a> {$row[0]['model']}$title_cust$title_prno$title_pdno
<ul>
EOF;

            // Update date
            $e = $er->updateRecord(
                ['dgt_act' => $dgt_act],
                ['dgt_act']
            );
            $er->manageFMSContractInformationAfterGreenTag($user->getID());

            if (is_string($e)) {
                return "INTERNAL ERROR: Unit {$er->getSN()} - Actual GT Date not updated. Reason: $e";
            }
            // Add ER logs
            $log = "Actual GT Date updated from {$er->getGT()} to {$dgt_act}";
            $er->addLogEntry($user->getID(), $log);

            $msg .= "<li>$log</li>{$er->getLinkHtmlInfo()}</ul>";

            // Update first GT date if GT the first time
            if ($er->getFirstGT() == '0000-00-00') {
                $e = $er->updateRecord(
                    ['dgt_com' => $dgt_act],
                    ['dgt_com']
                );
                if (is_string($e)) {
                    return "INTERNAL ERROR: Unit {$er->getSN()} - First GT Date not updated. Reason: $e";
                }
            } else {
                $msg .= "<p>This unit was first GT on the {$er->getFirstGT()}</p>";
            }
            // Notify
            $emaillist = tldODP::getRecipients(['GT_ACT' => true], $row[0]);
            $e = tldUtils::emailAttachment(
                array_unique($emaillist),
                'noreply@tld-gse.com',
                $subject,
                $msg,
                '',
                $cc
            );

            break;
        case 'acct.evp_coo.ap.japan.NonPOInvoice.pre_approved':
        case 'acct.evp_coo_ceo.ap.japan.NonPOInvoice.pre_approved':
        case 'acct.payment.ap.japan.NonPOInvoice.pre_approved':
        case 'acct.evp_coo.ap.japan.NonPOInvoice.approval':
        case 'acct.evp_coo_ceo.ap.japan.NonPOInvoice.approval':

            $TO = $CC = [];
            $TO[] = 'yabekaikei@nifty.com';

            $grp = new tldGroup('seq_acct.japan.invoice.approval.step1');
            foreach ($grp->getEmailList() as $email) {
                $TO[] = $email;
            }

            $grp = new tldGroup('seq_acct.japan.invoice.approval.step0');
            foreach ($grp->getEmailList() as $email) {
                $CC[] = $email;
            }

            if (in_array($seqTplName, ['acct.evp_coo.ap.japan.NonPOInvoice.pre_approved', 'acct.evp_coo_ceo.ap.japan.NonPOInvoice.pre_approved'])) {
                $grp = new tldGroup('seq_acct.japan.invoice.approval.ap');
                foreach ($grp->getEmailList() as $email) {
                    $CC[] = $email;
                }
            }

            $CC[] = 'kevin.camara@tld-america.com';
            #$CC[] = 'devteam@tld-america.com';

            $date = date('l, M jS, Y g:i A T', $p['datetime']);

            $body = <<<EOF
<p>The approval for invoice #{$p['id']} for TLD Japan has been ACCEPTED.  File uploaded on $date</p>
EOF;
            #tldUtils::emailAttachment($to, $from, $subject, $body, $file="", $cc="")

            // Send notification
            tldUtils::emailAttachment(
                $TO,
                'noreply@tld-gse.com',
                'ACCEPTED: '.$p['title'],
                $body,
                $p['file'],
                $CC
            );

            break;
        case 'sales.catalogue.datasheet.process':
            $body = "This page has been migrated and should not be displayed anymore.";
            break;
        case 'sales.new.customer':
            global $sess;
            unset($sess['new_cust_approval_token']);
            include_once 'sales_service.inc.php';
            global $kernel;
            if (!$kernel->getContainer()->get('security.authorization_checker.legacy')->isGranted('FEATURE_CUSTOMER_ADMIN') &&
                !$kernel->getContainer()->get('security.authorization_checker.legacy')->isGranted('FEATURE_CUSTOMER_STATUS')) {
                return 'ERROR: You do not have the permission to access this page.';
                break;
            }
            try {
                /** @var Client $client */
                $client = $kernel->getContainer()->get(Client::class);
            } catch (Exception $e) {
                return "ERROR: Could not get API client. Reason: " . $e->getMessage();
                break;
            }

            try {
                $customer = $client->findOneBy('sales/customers', ['legacyId' => $xtras['cid']]);
                $client->put(sprintf('sales/customers/%d/status', $customer['id']), ['json' => ['status' => 'APPROVED']]);
            } catch (ClientException $e) {
                $errors = json_decode($e->getResponse()->getContent(), true);
                return 'ERROR: Could not update customer. Reason: '.$errors['hydra:description'];
                break;
            }

            break;
        case 'investmentbudget.request':
        case 'investmentbudget.request.factory':
            $CC = [];
            // Get all people posting comments
            $comments = $seq->getComments();
            foreach ($comments as $comment) {
                $poster = new tldUser($comment['poster']);
                $posterEmail = $poster->getEmail();
                if (empty($posterEmail) || in_array($posterEmail, $CC)) {
                    continue;
                }
                $CC[] = $posterEmail;
            }
            // Get all people cc during process
            $ccList = $seq->getCC();
            foreach ($ccList as $cc) {
                $poster = new tldUser($cc);
                $posterEmail = $poster->getEmail();
                if (empty($posterEmail) || in_array($posterEmail, $CC)) {
                    continue;
                }
                $CC[] = $posterEmail;
            }
            // Get AP
            $location = new tldLocation($seq->getBUID());
            if ($location->getERP() !== '0') {
                $ggAP = new tldGroup('role_AP', $location->getERP());
                $ggAP_emails = $ggAP->getEmailList();
                foreach ($ggAP_emails as $ggAP_email) {
                    if (!empty($ggAP_email) && !in_array($ggAP_email, $CC)) {
                        $CC[] = $ggAP_email;
                    }
                }
            }
            // Notify
            $seq->notifyAssignee(
                "SEQ#$seqid CLOSED<br><a href=\"http://{$_SERVER['HTTP_HOST']}/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$seqid\">Click here to see sequence</a><hr>".
                $seq->getTask(),
                "Investment Budget Request Accepted - SEQ#$seqid is now closed",
                $CC
            );
            break;
        case 'hr.user.shopfloor_light.new':
            global $kernel;
            /** @var tldSEQ $seq */
            if ($seq->isCompleted()) {
                // Get Client API
                try {
                    /** @var Client $client */
                    $client = $kernel->getContainer()->get(Client::class);
                } catch (Exception $e) {
                    return "ERROR: Could not get API client. Reason: " . $e->getMessage();
                }
                // Get People entity
                $legacyID = $seq->getParentID();

                try {
                    /** @var \ApiBundle\Model\ApiData $people */
                    $people = $client->findOneBy('people', [
                        'legacyId' => $legacyID,
                        'hidden' => 1,
                        'disabled' => 1,
                    ]);
                } catch (Exception $e) {
                    // Do not remove people not disabled
                    break;
                }

                list($username) = explode('@', $people['username']);

                $payload = [
                    '@id' => $people['@id'],
                    'username' => $username,
                    'hidden' => false,
                    'disabled' => false,
                ];

                $location = $client->findOneBy('locations', ['legacyId' => $seq->itsHeader['bu_id']]);
                // Update people
                try {
                    $client->save('people', $payload);
                } catch (ClientException $e) {
                    $errors = json_decode($e->getResponse()->getContent(), true);
                    return 'ERROR: Could not update user. Reason: '.$errors['hydra:description'];
                }
            }
            break;
        case 'hr.user.shopfloor_light.delete':
            global $kernel;
            /** @var tldSEQ $seq */
            if ($seq->isCompleted()) {
                // Get Client API
                try {
                    /** @var Client $client */
                    $client = $kernel->getContainer()->get(Client::class);
                } catch (Exception $e) {
                    return "ERROR: Could not get API client. Reason: ".$e->getMessage();
                }

                // Get People entity
                $legacyID = $seq->getParentID();

                try {
                    /** @var \ApiBundle\Model\ApiData $people */
                    $people = $client->findOneBy('people', [
                        'legacyId' => $legacyID,
                    ]);
                } catch (Exception $e) {
                    return "ERROR: Could not retrieve user from the system. Reason: " . $e->getMessage();
                    break;
                }

                $payload = [
                    '@id' => $people['@id'],
                    'hidden' => true,
                    'disabled' => true,
                ];
                // Update people
                try {
                    $client->save('people', $payload);
                    $acls = $client->findBy('acls', ['user' => '/people/' . $people->getIriId()]);

                    foreach ($acls as $acl) {
                        $client->remove('acls', $acl->getIriId());
                    }

                } catch (ClientException $e) {
                    $errors = json_decode($e->getResponse()->getContent(), true);
                    return 'ERROR: Could not update user. Reason: ' . $errors['hydra:description'] . 'Please delete user manually !';
                }
            }
            break;
        default:
            // Auto close BP if sequence is part of MEAP/EAP
            if ($seq->getModule() === 'BP') {
                $bp = new tldBP($seq->getParentID());
                if (!$bp->isClosed() && ($bp->getMEAP() || $bp->getEAP()) && !count($bp->getTasks())) {
                    $error = $bp->close();
                    if (is_string($error)) {
                        return $error;
                    }
                }
                // Auto Close EAP if it is the last task of the NOTIFICATION step
                if( $bp->getEAP() !== null
                    && 'NOTIFICATION' ===  $bp->getEAP()->getStatus()
                    && empty($bp->getEAP()->getTasks())
                    && empty($bp->getEAP()->getBPS()))
                {
                    $bp->getEAP()->changeStatus('CLOSED');
                    $bp->getEAP()->addComment(['poster' => $user->getID(), 'comment' => 'CLOSED (Auto)', 'log_num' => 0]);
                }
            }
            break;
    }
}

// local function containing the Pre closing scripts.
// all those actions will be done before the closing action of the SEQ
function _preCloseScripts($seq)
{
    global $extras;
    $xtras = unserialize(base64_decode($extras));
    // Look for mode
    switch ($seq->getMode()) {
        case 'USER_LEVEL':

            break;
        default:

            break;
    }
    $tpl = $seq->getTemplate();
    $seqTplName = $tpl->getName();
    $seqid = $seq->itsID;
    $p = $seq->getCloseParams();

    // Check if actual step of the sequence is the last step just before CLOSURE
    if ($seq->getCurrentStep() != $tpl->getLastNodeStep()) {
        return; // do nothing if not equal
    }

    switch ($seqTplName) {
        case 'mfg.pur.po.5k-20k':
        case 'mfg.pur.po.20k-100k':
        case 'mfg.pur.po.100kplus':
        case 'mfg.pur.po.50k-100k':
        case 'mfg.pur.po.100kplus.rcoo':
        case 'seq.po.sso.1':
        case 'seq.po.sso.2':
        case 'seq.po.sso.3':
        case 'seq.po.factory.1':
        case 'seq.po.factory.2':
        case 'seq.po.factory.3':
        case 'seq.po.factory.4':
            // Get close param data from task
            $erp = $p['erp'];
            $orno = $p['orno'];
            // Get the PO
            $po = new tldPO($orno, $erp);
            if ($po->isEmpty()) {
                return "PO#$orno for $erp not found";
            }
            $suno = (empty($p['suno']) || $p['suno'] === 0 || $p['suno'] === '') ? $po->getSUNO() : $p['suno'];
            if (in_array($suno, ['', 0, null] , true)) {
                return "ERROR: No Supplier number has been found for purchase order $orno for erp $erp";
            }
            // Get list of vendors to notify for this PO
            $vendors = tldVendor::byVendorPO($erp, $suno);
            // Need to add buyer email to TLD Europe PO headers
            $assignor = new tldUser($seq->getAssignor());
            $buyer_email = $assignor->getEmail();
            // Check display for evendors
            $po->display($orno, $erp, 'SEQ');
            // Check if we have vendors
            if (count($vendors ?? []) == 0) {
                return tldUtils::emailAttachment(
                    $buyer_email,
                    'noreply@tld-gse.com',
                    "TLD ePO #$orno vendor# $suno",
                    "This vendor does not have an eVendor account. <br>Seq#$seqid",
                    $p['filepath'],
                    'noreply@tld-gse.com'
                );
            }
            // Else notify vendors
            foreach ($vendors as $vendor) {
                if ('' === trim((string) $vendor['email'])) {
                    continue;
                }
                $_toList[] = $vendor['email'];
            }

            if (empty($_toList)) {
                return "ERROR: No vendor has been found with valid email, email not sent.";
            }

            $_to = implode(',', $_toList);
            // Prepare body email
            $body = <<<EOF
Attached is a copy of our ePO $orno.<br/>
NOTE: If you have previously received a copy of this ePO then this may be an updated version. Please review the ePO to confirm. If item is passed normal lead time please ship to us 2nd day Air.<br/>
Please log into our eVendors website to confirm your delivery dates. <a href="https://evendors.tld-gse.com">See ePO online</a>.
<br/><br/>
Veuillez trouver ci-jointe la commande #$orno.<br/>
NB : Si vous avez d&eacute;j&agrave; re&ccedil;u cette commande, il s'agit peut-&ecirc;tre d'une version mise &agrave; jour. Merci de v&eacute;rifier pour confirmer.<br/>
<a href="https://evendors.tld-gse.com">Cliquez ici pour accuser r&eacute;ception des commandes directement sur le site de TLD</a>
<br/><br/>
&#x9644;&#x4EF6;&#x662F;&#x4E00;&#x4EFD;&#x7535;&#x5B50;&#x8BA2;&#x5355; #$orno <br/>
&#x6CE8;&#x610F;&#xFF1B;&#x5982;&#x679C;&#x4F60;&#x4E4B;&#x524D;&#x6536;&#x5230;&#x4E00;&#x4EFD;&#x8FD9;&#x6837;&#x7684;&#x7535;&#x5B50;&#x8BA2;&#x5355;&#xFF0C; &#x90A3;&#x4E48;&#x8FD9;&#x624D;&#x662F;&#x6700;&#x65B0;&#x7248;&#x672C;&#x7684;&#x8BA2;&#x5355;&#x3002;&#x8BF7;&#x786E;&#x8BA4;&#x6B64;&#x8BA2;&#x5355;&#x3002;<br/>
&#x8BF7;&#x786E;&#x8BA4;&#x4F60;&#x4EEC;&#x7684;&#x4EA4;&#x8D27;&#x671F;&#x901A;&#x8FC7;&#x767B;&#x9646;&#x6211;&#x4EEC;&#x7684;&#x7F51;&#x7AD9;&#x3002;&#x5E76;&#x5728;&#x6211;&#x4EEC;&#x7684;&#x7F51;&#x7AD9;&#x4E0A;&#x67E5;&#x770B;&#x5230;&#x7535;&#x5B50;&#x8BA2;&#x5355;&#x3002;<br/><br/>
Email sent to $_to
<br/>
Seq# $seqid
EOF;
            // Send email
            return tldUtils::emailAttachment(
                $_to,
                $buyer_email,
                "TLD ePO / Commande #$orno vendor# $suno",
                $body,
                $p['filepath'],
                "$buyer_email,noreply@tld-gse.com"
            );
            break;
        case 'sales.Customer.Validation':
            global $kernel;

            try {
                /** @var Client $client */
                $client = $kernel->getContainer()->get(Client::class);
            } catch (Exception $e) {
                return 'ERROR: Could not get API client. Reason: '.$e->getMessage();
            }

            try {
                $apiCustomer = $client->findOneBy('sales/customers', ['legacyId' => $seq->getParentID()]);
            } catch (Exception $e) {
                return 'ERROR: Could not get Customer. Reason: '.$e->getMessage();
            }

            try {
                $client->put(sprintf('sales/customers/%d/status', $apiCustomer['id']), ['json' => ['status' => 'APPROVED']]);
            } catch (ClientException $e) {
                $errors = json_decode($e->getResponse()->getContent(), true);
                return 'ERROR: Could not save Customer. Reason: '.$errors['hydra:description'];
            }

            break;
        case 'sales.demo.approval':
            global $kernel;

            try {
                /** @var Client $client */
                $client = $kernel->getContainer()->get(Client::class);
            } catch (Exception $e) {
                return 'ERROR: Could not get API client. Reason: '.$e->getMessage();
            }

            try {
                $client->put(sprintf('sales/demos/%s/status', $p['id']), ['json' => ['status' => 'APPROVED']]);
            } catch (ClientException $e) {
                $errors = json_decode($e->getResponse()->getContent(), true);
                return 'ERROR: Could not set status as APPROVED. Reason: '.$errors['hydra:description'];
            }
            break;
    }
}

function _preCancelScripts($seq)
{
    global $extras, $user, $kernel;
    $xtras = unserialize(base64_decode($extras));
    // Look for mode
    switch ($seq->getMode()) {
        case 'USER_LEVEL':
            // By module
            switch ($seq->getModule()) {
                case 'DMS':
                    include_once("dms.inc.php");
                    $dms = new tldDMS($seq->getParentID());
                    if ($dms->isEmpty()) {
                        return "DMS#{$seq->getParentID()} not found";
                    }
                    // Log cancel
                    $dms->addLogEntry($user->getID(), "SEQ#{$seq->itsID} cancelled");
                    // if cancel, dms back to REVISION
                    $e = $dms->updateStatus('REVISION', $user->getID());
                    if (is_string($e)) {
                        return "Can not change DMS#{$seq->getParentID()} status: $e";
                    }
                    // remove revision creation linked to this sequence
                    $e = $dms->deleteApprovalRevision();
                    if (is_string($e)) {
                        return "Can not delete approval revision linked to this sequence: $e";
                    }
                    break;
            }
            break;
        default:
            $tpl = $seq->getTemplate();
            $seqTplName = $tpl->getName();
            $seqid = $seq->itsID;
            $p = $seq->getCloseParams();

            // Look by template
            switch ($seqTplName) {
                case 'sales.new.customer':
                    global $sess;
                    unset($sess['new_cust_approval_token']);
                    break;
                case 'hr.user.new':
                case 'hr.user.shopfloor_light.new':
                case 'hr.user.new.Sageparts':
                    // Get Client API
                    try {
                        $client = $kernel->getContainer()->get(Client::class);
                    } catch (Exception $e) {
                        return 'ERROR: Could not get API client. Reason: '.$e->getMessage();
                    }
                    // Get People entity
                    $legacyID = $seq->getParentID();

                    try {
                        $people = $client->findOneBy('people', [
                            'legacyId' => $legacyID,
                            'hidden' => 1,
                            'disabled' => 1,
                        ]);
                    } catch (Exception $e) {
                        // Do not remove people not disabled
                        break;
                    }

                    $parts = explode('/', $people['@id']);
                    $id = array_pop($parts);

                    // Delete people
                    try {
                        $client->remove('people', $id);
                    } catch (Exception $e) {
                        return 'ERROR: Could not delete user. Reason: '.$e->getMessage();
                    }
                    break;
                case 'sales.extranet.approval':
                case 'sales.extranetuser.approval':
                    global $kernel;
                    try {
                        $client = $kernel->getContainer()->get(Client::class);
                    } catch (Exception $e) {
                        return 'ERROR: Could not get API client. Reason: '.$e->getMessage();
                    }

                    try {
                        $extranetUser = $client->findOneBy('sales/extranet_users', ['email' => $p['email']]);
                    } catch (RangeException $e) {
                        break;
                    }

                    try {
                        $client->request('sales/extranet_users', $extranetUser['id'], 'disable_account', 'PUT');
                    } catch (ClientException $e) {
                        $errors = json_decode($e->getResponse()->getContent(), true);
                        return 'ERROR: Could not disable Extranet User. Reason: '.$errors['hydra:description'];
                    }
                    break;
                case 'sales.Customer.Validation':
                    if (isset($_REQUEST['cancelOnlySeq']) && (bool)$_REQUEST['cancelOnlySeq']) {
                        break;
                    }
                    global $kernel;

                    /** @var Client $client */
                    $client = $kernel->getContainer()->get(Client::class);
                    $apiCustomer = $client->findOneBy('sales/customers', ['legacyId' => $seq->getParentID()]);

                    try {
                        $client->put(sprintf('%s/status', $apiCustomer['@id']), ['json' => ['status' => 'NOT APPROVED']]);
                    } catch (ClientException $e) {
                        $errors = json_decode($e->getResponse()->getContent(), true);
                        return 'ERROR: Could not update status. Reason: '.$errors['hydra:description'];
                    }

                    break;
                case 'sales.demo.approval':

                    try {
                        /** @var Client $client */
                        $client = $kernel->getContainer()->get(Client::class);
                    } catch (Exception $e) {
                        return 'ERROR: Could not get API client. Reason: '.$e->getMessage();
                    }

                    try {
                        $client->put(sprintf('sales/demos/%s/status', $p['id']), ['json' => ['status' => 'REJECTED']]);
                    } catch (ClientException $e) {
                        $errors = json_decode($e->getResponse()->getContent(), true);
                        return 'ERROR: Could not update Demo. Reason: '.$errors['hydra:description'];
                    }

                    break;
            }
            break;
    }
}

function _preRejectScripts($seq)
{
    global $extras;
    $xtras = unserialize(base64_decode($extras));
    // Look for mode
    switch ($seq->getMode()) {
        case 'USER_LEVEL':

        default:
            $tpl = $seq->getTemplate();
            $seqTplName = $tpl->getName();
            $seqid = $seq->itsID;
            $p = $seq->getCloseParams();
            // Look for template
            switch ($seqTplName) {
                case 'sales.demo.approval':
                    if ($seq->getCurrentStep() === "2") {
                        global $kernel;

                        try {
                            $client = $kernel->getContainer()->get(Client::class);
                        } catch (Exception $e) {
                            return 'ERROR: Could not get API Client. Reason : '.$e->getMessage();
                        }

                        try {
                            $client->put(sprintf('sales/demos/%s/status', $p['id']), ['json' => ['status' => 'PENDING']]);
                        } catch (ClientException $e) {
                            $errors = json_decode($e->getResponse()->getContent(), true);
                            return 'ERROR: Could not Update DEMO status. Reason : '.$errors['hydra:description'];
                        }
                    }
                    break;
                case 'sales.new.customer':
                    global $sess;
                    unset($sess['new_cust_approval_token']);
                    break;
            }
            break;
    }
}

function _preSequenceActionScripts($seq)
{
    global $extras;
    $xtras = unserialize(base64_decode($extras));
    // Look for mode
    switch ($seq->getMode()) {
        case 'USER_LEVEL':

            break;
        default:
            $tpl = $seq->getTemplate();
            $seqTplName = $tpl->getName();
            $seqid = $seq->itsID;
            $p = $seq->getCloseParams();

            switch ($seqTplName) {

                case 'sales.new.customer':
                    global $sess, $canCancel, $m;
                    if (isset($_REQUEST['cancelOnlySeq']) && (bool)$_REQUEST['cancelOnlySeq']) {
                        break;
                    }
                    if (is_array($xtras) and !empty($sess['new_cust_approval_token']) and !empty($xtras['token']) and $sess['new_cust_approval_token'] == $xtras['token']) {
                        // Allow to continue
                        if (empty($xtras['cid'])) {
                            return 'Missing required customer ID';
                        }
                    } else {
                        unset($sess['new_cust_approval_token']);

                        return 'Actions directly from this sequence is not allowed, use links in the task message body instead';
                    }
                    if ($m[2] == 'cancel') {
                        $canCancel[] = $seq->getAssignor();
                        $canCancel[] = $seq->getAssignee();
                    }
                    break;
            }
            break;
    }
}

/**
 * @param tldSEQ $seq
 * @param bool $cancelled
 */
function _postSequenceActionScripts($seq, $cancelled = false)
{
    global $extras, $user;
    $xtras = unserialize(base64_decode($extras));
    // Look for mode
    switch ($seq->getMode()) {
        case 'USER_LEVEL':

            break;
        default:
            $id = $seq->itsID;
            $seqTpl = $seq->getTemplate();
            $seqTplName = $seqTpl->getName();
            $seqParams = $seq->getCloseParams();

            switch ($seqTplName) {
                case 'sales.demo.approval':
                    if (true === $cancelled) {
                        break;
                    }

                    global $kernel;
                    try {
                        $client = $kernel->getContainer()->get(Client::class);
                    } catch (Exception $e) {
                        return 'ERROR: Could not get API Client. Reason : ' . $e->getMessage();
                    }

                    $approver = $client->findOneBy('people', ['legacyId' => $user->getID()]);

                    $demoIri = sprintf('/sales/demos/%d', $seqParams['id']);

                    try {
                        $demo = $client->find('sales/demos', $seqParams['id']);
                        if ($demo["status"] !== 'REJECTED') {
                            $approvers = array_reduce($demo['approvers'], function ($memo, $people) {
                                $memo[] = $people['@id'];

                                return $memo;
                            }, []);

                            $approvers[] = $approver['@id'];

                            $client->save('sales/demos', [
                                '@id' => $demoIri,
                                'approvers' => $approvers,
                            ]);
                        }
                    } catch (ClientException $e) {
                        $errors = json_decode($e->getResponse()->getContent(), true);
                        return 'ERROR: Could not add approver to Demo. Reason: '.$errors['hydra:description'];
                    }


                    if ($seq->getCurrentStep() === "2") {


                        try {
                            $client->put(sprintf('sales/demos/%s/status', $seqParams['id']), ['json' => ['status' => 'SUBMITTED']]);
                        } catch (ClientException $e) {
                            $errors = json_decode($e->getResponse()->getContent(), true);
                            return 'ERROR: Could not Update DEMO status. Reason : '.$errors['hydra:description'];
                        }
                    }

                    break;
                case 'mis.investmentbudget.request':
                case 'mis.investmentbudget.request.leb':
                    $seq->refresh();
                    if ($seq->isClosed()) {
                        return;
                    }
                    $bu = new tldLocation($seq->getBUID());
                    $mism = new tldGroup('role_MISM', $bu->getERP());
                    $na = new tldGroup('role_NA', $bu->getERP());
                    $emails = array_merge($mism->getEmailList(), $na->getEmailList());
                    $maxNode = $seqTpl->getNumNodes();
                    $taskDetail = tldUtils::renderHtmlOrNl2br($seq->getTask());
                    $message = <<<EOF
Sequence #$id was updated and is at step {$seq->itsHeader['cur_step']}/$maxNode<br>
<a href="http://{$_SERVER['HTTP_HOST']}/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$id">Click here to see sequence.</a><br>
$taskDetail
EOF;

                    return tldUtils::emailAttachment(
                        array_unique($emails),
                        'noreply@tld-gse.com',
                        "SEQ#$id, MIS investmentbudget request, {$bu->getShortName()} (step {$seq->itsHeader['cur_step']}/$maxNode)",
                        $message
                    );
                    break;
            }
            break;
    }

    if ($cancelled && 'BP' === $seq->getModule()) {
        $bp = new tldBP($seq->getParentId());
        if (null !== $bp->getMEAP()) {
            foreach ($bp->getTasks() as $linkedTask) {
                (new tldSEQ($linkedTask['id']))->cancel(['comment' => "AUTO CANCELLATION DUE TO SEQ#{$seq->itsID}<br><br>Closing BP and moving back in MEAP status"]);
            }
            $bp->close(true);
        }
    }
}

function _taskDescriptionModifierScripts($string)
{
    global $task, $xtras;
    // For SEQUENCES
    if ($task->isSequence()) {
        global $seq;
        $xtras = unserialize(base64_decode($extras ?? ''));
        // Look for mode
        switch ($seq->getMode()) {
            case 'USER_LEVEL':

                break;
            default:
                $tpl = $seq->getTemplate();
                $seqTplName = $tpl->getName();
                $seqid = $seq->itsID;
                $p = $seq->getCloseParams();

                switch ($seqTplName) {
                    case 'sales.new.customer':
                        $string = <<<EOF
$string
<br/>
<p><strong style="background:#900;color:#fff;padding:5px;">You must use the following link to accept, reject and edit new customer information:</strong><br/>
<a href="/en/private/sales_service/sales.php?m[0]=customers&m[1]=approval&m[2]=process&seqid=$seqid">Click here to continue</a></p><br/><br/>
EOF;
                        break;
                }
                break;
        }
    } // For TASKS
    else {
    }

    return $string;
}

function _getView()
{
    global $task;
    if ($task->isSequence()) {
        return _getSequenceView();
    }

    return _getTaskView();
}

function _getTaskView()
{
    global $smarty, $task, $PATH;
    $task = $task->asArray();
    $task['assignee']['lastname'] = html_entity_decode($task['assignee']['lastname']);
    $task['assignee']['firstname'] = html_entity_decode($task['assignee']['firstname']);
    $task['assignor']['lastname']= html_entity_decode($task['assignor']['lastname']);
    $task['assignor']['firstname']= html_entity_decode($task['assignor']['firstname']);
    $smarty->assign('task', $task);

    if (null !== $moduleId = $task['ticket_module_id']) {
        $module = new tldModule($moduleId);
        if (!$module->isEmpty()) {
            $smarty->assign('module', $module->itsHeader);
        }
    }

    return $smarty->fetch("$PATH/tasks/view.task.tpl");
}

function _getSequenceView()
{
    global $smarty, $seq, $PATH;
    $current_node = $seq->getCurrentNode();
    $current_node_header = $current_node->getHeader();
    $action = $current_node_header['dsca'];
    $reportByParentId = new tldReportColumnar(tldModLink::byParent($seq->itsID, 'SEQ'),
        ["xItems" => [
            "id" => "ID#",
            "type" => "Module",
            "item" => "Ref#",
            "dsca" => "Description"
        ],
            "title" => "Links FROM Here...",
            "links" => ["id" => "/en/private/common/index.php?m[0]=links&m[1]=view&m[2]=redirect&id="],
        ]
    );
    $reportByItem = new tldReportColumnar(tldModLink::byItem($seq->itsID, 'SEQ'),
        ["xItems" => [
            "id" => "ID#",
            "module" => "Module",
            "parent_id" => "Ref#",
            "dsca" => "Description"
        ],
            "title" => "Links TO Here...",
            "links" => ["id" => "/en/private/common/index.php?m[0]=links&m[1]=view&m[2]=redirect&reversed=1&id="],
        ]
    );

    $next_node = $seq->getNextNode();
    if ($next_node !== false) {
        $next_node_header = $next_node->getHeader();
        $action .= '<h3>Next Action:</h3>' . $next_node_header['dsca'];
    }

    global $kernel;

    if (str_contains($seq->getHeader()['tpl_fullname'], 'hr.user')
        && tldUser::byConstraints(['id'=>$seq->getParentID()]) !== []
    ) {
        /** @var Client $client */
        $client = $kernel->getContainer()->get(Client::class);
        $apiUserId = $client->findOneBy('people', ['legacyId' => $seq->getParentID()])->id;
    }

    if (in_array($seq->getTPLNO(), [22, 78]) && isset($seq->asArray()['params']['pn'])) {
        $partDashboardUrl = $kernel->getContainer()->get('router')->generate(
            'parts_dashboard_view', [
            'partNumber' => $seq->asArray()['params']['pn'],
        ],
            \Symfony\Component\Routing\Router::ABSOLUTE_URL
        );
    }
    
    $smarty->assign('task', $seq->asArray());
    $smarty->assign('reportByItem', $reportByItem);
    $smarty->assign('reportByParentId', $reportByParentId);
    $smarty->assign('partDashboardUrl', $partDashboardUrl ?? '');
    $smarty->assign('apiUserId', (isset($apiUserId) ? $apiUserId : null));
    $smarty->assign('current_action', $action);

    return $smarty->fetch("$PATH/seq/view.seq.tpl");
}

function max_value_f($element_name, $element_value)
{
    return $element_value <= 60;
}
