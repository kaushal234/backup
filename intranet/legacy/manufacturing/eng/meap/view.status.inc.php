<?php

if (!$user->isInGroup(['gg_ENG', 'gg_ADMIN'])) {
    $DEFAULT_ERROR[] = 'ERROR: You do not have permission to change the status of MEAPs';
    return;
}

$result = $meap->changeStatus();
if (!is_array($result)) {
    $DEFAULT_ERROR[] = "ERROR: {$result}";
    return;
}
$allowed = $result['allowed'];

switch ($m[3]) {
    case 'GATE_PROPOSAL':
    case 'GATE_0':
    case 'GATE_1':
    case 'GATE_2':
    case 'GATE_3':
    case 'GATE_4':
        $openBP = true;
    case 'PROPOSAL':
    case 'PROPOSAL':
    case 'SUSPENDED':
    case 'PHASE_0':
    case 'PHASE_1':
    case 'PHASE_2':
    case 'PHASE_3':
    case 'PHASE_4':
    case 'REJECTED':
    case 'CLOSED':
        if (!array_key_exists($m[3], $allowed)) {
            $DEFAULT_ERROR[] = "ERROR: Changing MEAP to {$m[3]} from current status is not allowed";
            break;
        }
        if ($meap->getStatus() === $m[3] || in_array($m[3], ['REJECTED', 'CLOSED'])) {
            // Set log only
            $openBP = false;
        }
        // Get mandatory approvers
        if ($openBP) {
            $approvers = [];
            foreach ($meap->getApprovers($m[3]) as $approver) {
                $approvers[$approver['id']] = $approver['fullname'];
            }
            asort($approvers);
        }
        // Create form
        $form = new HTML_QuickForm('frmChangeStatus', 'post');
        $form->addElement('hidden', 'm[0]', 'meap');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'status');
        $form->addElement('hidden', 'm[3]', $m[3]);
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'meap_header', "MEAP {$m[3]}");
        $form->addElement('textarea', 'description', 'Description',
            ['wrap' => 'VIRTUAL', 'cols' => '60', 'rows' => '8']
        );
        if ($openBP) {
            $form->addElement('file', 'file', 'File');
            $form->addElement('header', 'approvers_header', 'Approvers');
            if ($approvers) {
                $form->addElement('static', 'approvers_mandatory', 'Mandatory Approvers', implode('<br/>', $approvers));
            }
            $ams =& $form->addElement('advmultiselect', 'others', null,
                array_diff_key(tldDirectory::getUserlist('smartyOptions'), $approvers),
                ['size' => 10, 'class' => 'pool', 'style' => 'width:200px;']
            );
            $ams->setLabel(['Other Approvers', '', 'Selected']);
            $ams->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
            $ams->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
        }
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('description', 'This is required', 'required');
        // Validate
        if (!$form->validate()) {

            $body = "<h3>Current Status: {$meap->getStatus()}</h3>";
            $body .= $form->toHTML();
            if ('GATE_0' === $m[3] && 1000 <= $meap->getImportanceFactor()) {
                $body .= <<<HTML
<script>
    alert('For MEAP IF 1000 and 10000, a formal and dedicated meeting with the approvers is mandatory for Gate 0 approval.')
</script>
HTML;

            }
            break;
        }

        // Process
        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $change = $meap->changeStatus($m[3]);
        if (is_string($change)) {
            $DEFAULT_ERROR[] = "ERRROR: There was a problem with updating the MEAP status, returned error was $change";
            break;
        }
        $log = $meap->addComment(['poster' => $user->getID(), 'comment' => "MEAP Status moved to {$m[3]}\n\n{$vars['description']}"]);
        if (is_string($log)) {
            $DEFAULT_ERROR[] = "INTERNAL ERROR: Comment not added!<br/>Reason: {$log}";
            break;
        }
        if ($openBP) {
            // Create BP
            $file = $form->getElement('file');
            $vals = [
                'owner' => $user->getID(),
                'short_desc' => TldDatabase::escape("MEAP #{$id}: {$m[3]} - {$header['short_desc']}"),
                'long_desc' => $vars['description'],
                'file_info' => $file->getValue(),
            ];
            $bpid = tldBP::insert($id, $vals, 'MEAP');
            if (!is_numeric($bpid)) {
                $DEFAULT_ERROR[] = "ERRROR: There was a problem creating the BP, returned error was $bpid";
                break;
            }
            //insert file
            if ($vals['file_info']['tmp_name']) {
                $fe = tldModFile::insert([
                    'module' => 'BP',
                    'parent_id' => $bpid,
                    'description' => "File attachment for BP #$bpid MEAP #{$id} {$m[3]}",
                    'filename' => $vals['file_info']['name'],
                    'poster' => $user->getID(),
                ], $vals['file_info']);
                if (is_string($fe)) {
                    $DEFAULT_ERROR[] = "ERROR: Could not attach file to BP<br>Reason: $fe";
                }
            }
            $bp = new tldBP($bpid);
            $bp->addLogEntry($user->getID(), "BP CREATED for MEAP#{$id} {$m[3]}");
            // Open task
            $text = <<<EOF
MEAP #{$id} {$m[3]} requires your approval.
{$description}

{$header['description']}
EOF;
            $link = <<<EOF
<a href="https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=bp&m[1]=view&id={$bpid}">
See BP #{$bpid} {$m[3]} Online</a>
EOF;
            // add others
            foreach ($others as $other) {
                $approvers[$other] = $other;
            }
            foreach (array_keys($approvers) as $assignee) {
                $task = [
                    'assignee' => $assignee,
                    'assignor' => $user->getID(),
                    'task' => TldDatabase::escape($text),
                    'due_date' => ['value' => 14, 'unit' => 'DAY'],
                    'seq' => 'Y',
                    'tplno' => 0,
                    'bu_id' => $header['factory'],
                ];
                $taskid = $bp->addTask($task);
                if (is_numeric($taskid)) {
                    $task = new tldTask($taskid);
                    $task->notifyAssignee("{$text} {$link}", $vals['short_desc']);
                } else {
                    $DEFAULT_ERROR[] = "ERROR: There was a problem creating task: {$taskid}";
                }
            }
            $text = <<<EOF
MEAP #{$id} {$m[3]} enters approval.
{$description}

{$header['description']}
EOF;
            $meap->notifyGate("$text $link");
        }
        $DEFAULT_SUCCESS[] = 'Update completed';
        $body = "<h3>Current Status: {$meap->getStatus()}</h3>";
        break;
    default:
        if (empty($allowed)) {
            $DEFAULT_ERROR[] = "WARNING: Cannot change status with MEAP in current status: {$meap->getStatus()}";
            break;
        }
        $form = new tldHTMLList(
            $allowed,
            'list_item',
            "$php_self?m[0]=meap&m[1]=view&m[2]=status&id={$id}&m[3]=",
            ['title' => 'Please select new MEAP status.']
        );
        $body .= $form->fetch();
        if ($result['message'] ?? null) {
            $body .= <<<HTML
<div><p style="color:#FF0000">{$result['message']}</p></div>
HTML;
        }
        break;
}
