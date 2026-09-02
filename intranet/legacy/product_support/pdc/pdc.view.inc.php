<?php

use ApiBundle\Client;
use Symfony\Component\HttpClient\Exception\ClientException;
if (empty($id) || !is_numeric($id)) {
    $DEFAULT_ERROR[] = 'ERROR: Parameter sent empty or invalid...';
    return;
}

$pdc = new tldPDC($id);
$header = $pdc->itsHeader;
if ($pdc->isEmpty()) {
    $DEFAULT_ERROR[] = "ERROR: No PDC#$id found...";
    return;
}

$DEFAULT_TITLE .= "\PDC#$id";
$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=pdc&m[1]=view&id=$id">General</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pdc&m[1]=view&m[2]=edit&id=$id">Edit</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pdc&m[1]=view&m[2]=containmentAction&id=$id">Containment actions</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pdc&m[1]=view&m[2]=rootCause&id=$id">Final root cause</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pdc&m[1]=view&m[2]=correctiveAction&id=$id">Corrective actions</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pdc&m[1]=view&m[2]=preventiveAction&id=$id">Preventive actions</a>
 | <a href="$php_self?m[0]=pdc&m[1]=view&m[2]=verificationOfEffectiveness&id=$id">Verification of Effectiveness</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pdc&m[1]=view&m[2]=log&id=$id">Logs</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pdc&m[1]=view&m[2]=followers&id=$id">Followers</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pdc&m[1]=view&m[2]=tasks&id=$id">Tasks</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pdc&m[1]=view&m[2]=files&id=$id">Files</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pdc&m[1]=view&m[2]=parts&id=$id">Parts</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pdc&m[1]=view&m[2]=links&id=$id">Links</a>
&nbsp;|&nbsp;<a href="/en/private/common/index.php?m[0]=emails&m[1]=newEmail&module=pdc&id=$id">Email</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pdc&m[1]=view&m[2]=status&id=$id">Status</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pdc&m[1]=view&m[2]=delete&id=$id">Delete</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pdc&m[1]=view&m[2]=sheet&id=$id">PDC sheet</a>
EOF;

if ($user->isInGroup(['demerit', 'gg_SUPPORT', 'gg_ADMIN'])) {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="pdc/pdc_admin.php?mode=record_view&form_type=main_tpl&id=$id">Admin</a>
EOF;
}

global $kernel;
try {
    $client = $kernel->getContainer()->get(Client::class);
} catch (Exception $e) {
    $DEFAULT_ERROR[] = 'ERROR: Could not get API client. Reason: ' . $e->getMessage();
    return;
}

switch ($m[2]) {
    case 'preventiveAction':
        $DEFAULT_TITLE .= "\Preventive Action";

        // Check status
        if ($pdc->getStatus() !== 'ACTION') {
            $DEFAULT_ERROR[] = 'ERROR: Can not set preventive action, PDC must be in status ACTION';
            break;
        }

        // Form
        $form = new HTML_QuickForm('frmNew', 'post');
        $form->addElement('hidden', 'm[0]', 'pdc');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'preventiveAction');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title1', 'Is there any further actions to prevent such problem?');
        $form->addElement('textarea', 'field1', '', ['wrap' => 'VIRTUAL', 'cols' => '50', 'rows' => '6']);
        $form->addElement('header', 'title2', 'Is there any further action to detect such similar problem in the future, including updating FAQ standard procedures for similar parts?');
        $form->addElement('textarea', 'field2', '', ['wrap' => 'VIRTUAL', 'cols' => '50', 'rows' => '6']);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('field1', 'Required', 'required');
        $form->addRule('field2', 'Required', 'required');
        $form->setDefaults($pdc->getFormDataByName('preventiveActionForm'));

        if ($form->validate()) {
            $vars = $form->exportValues();
            try {
                foreach (['field1', 'field2'] as $key) {
                    $value = $vars[$key];
                    $translatedField = $client->post('/deepl/translate', ['json' => ['message' => $value, 'module' => 'PDC', 'moduleId' => (int) $pdc->getID()], 'query' => ['createLog' => false]]);
                    $vars[$key] = sprintf('%s (original message: %s)', $translatedField['translatedMessage'], $vars[$key]);
                }
            } catch (ClientException $exception) {
                $DEFAULT_ERROR[] = "ERROR: Text could not be translated.";
                break;
            }

            $formated = <<<EOF
____________________________________________________
<p>Is there any further actions to prevent such problem?</p>
____________________________________________________
{$vars['field1']}
____________________________________________________
<p>Is there any further action to detect such similar problem in the future, including updating FAQ standard procedures for similar parts?</p>
____________________________________________________
{$vars['field2']}
EOF;
            // record data in field
            $formated = TldDatabase::escape($formated);
            $e = $pdc->update(['preventive_action' => $formated]);
            if (is_string($e)) {
                $DEFAULT_ERROR[] = "INTERNAL ERROR: Can not update PDC. Reason: $e";
                break;
            }
            // record form data submitted
            $formData = tldUtils::cleanupFormInput($vars);
            $pdc->saveFormData('preventiveActionForm', $formData);
            // refresh to apply update
            $pdc->refresh();
        }

        // Display info + form ----->
        $report = new tldAssocTable(
            $pdc->itsHeader,
            [
                'preventive_action' => 'Preventive action',
            ]
        );
        $body = <<<EOF
{$report->fetch()}
<br>
{$form->toHTML()}
EOF;
        break;
    case 'correctiveAction':
        $DEFAULT_TITLE .= "\Corrective Action";

        // Check status
        if ($pdc->getStatus() !== 'ACTION') {
            $DEFAULT_ERROR[] = 'ERROR: Can not use corrective action form, PDC must be in status ACTION';
            break;
        }

        switch ($m[3]) {
            case 'submit':

                $vars = $_POST['actions'];
                if (isset($_POST['btn'])) {
                    $btnWho = key($_POST['btn']);
                    $btnK = key($_POST['btn'][$btnWho]);
                    $sess['task_body'] = isset($vars[$btnWho][$btnK]['ref']) ? $vars[$btnWho][$btnK]['ref'] : '';
                    header("Location: /en/private/calendar/calendar.php?m[0]=tasks&m[1]=form&m[2]=newTask&module=PDC&parent_id=$id");
                    exit;
                }

                $rows = [];
                $formated = null;
                // prepare data
                foreach ($vars as $who => $actions) {
                    $tempTxt = null;
                    foreach ($actions as $k => $vals) {
                        // Check if data for this action
                        if (empty($vals['ref'])) {
                            continue;
                        }
                        // keep valid data with same form structure to record later
                        $rows[$who][(int)$k] = $vals;
                        // prepare formated text

                        try {
                            $value = $vals['ref'];
                            $translatedField = $client->post('/deepl/translate', ['json' => ['message' => $value, 'module' => 'PDC', 'moduleId' => (int) $pdc->getID()], 'query' => ['createLog' => false]]);
                            $vals['ref'] = sprintf('%s (original message: %s)', $translatedField['translatedMessage'], $vals['ref']);
                        } catch (ClientException $exception) {
                            $DEFAULT_ERROR[] = "ERROR: Text could not be translated.";
                            break;
                        }
                        $tempTxt .= <<<EOF
<br>
{$vals['action']}: {$vals['ref']}
<br>
EOF;
                    }
                    if (empty($tempTxt)) {
                        continue;
                    }
                    $formated .= <<<EOF
____________________________________________________

<p>$who</p>
____________________________________________________

<p>$tempTxt</p>
EOF;
                }
                // check if one line at least entered
                if (!$rows) {
                    $DEFAULT_ERROR[] = 'ERROR: No actions has been selected, make sure to set all informations';
                    break;
                }
                // record data in field
                $formated = TldDatabase::escape($formated);
                $e = $pdc->update(['corrective_action' => $formated]);
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "INTERNAL ERROR: Can not update PDC. Reason: $e";
                    break;
                }
                // record form data submitted
                $pdc->saveFormData('correctiveActionsForm', $rows);
                // refresh PDC
                $pdc->refresh();
                break;
        }

        // Display info and form ------>

        $report = new tldAssocTable(
            $pdc->itsHeader,
            [
                'corrective_action' => 'Corrective action',
            ],
            [
                'title' => 'Actual Corrective action',
            ]
        );
        $body = $report->fetch();
        $FORM_DEFAULT = $pdc->getFormDataByName('correctiveActionsForm');
        $body .= include 'form/form.corrective_action.tpl.php';
        break;
    case 'rootCause':
        $DEFAULT_TITLE .= "\Final root cause";

        // Check status
        if ($pdc->getStatus() !== 'INVESTIGATION') {
            $DEFAULT_ERROR[] = 'ERROR: Can not set root cause, PDC must be in status INVESTIGATION';
            break;
        }

        // Form
        $form = new HTML_QuickForm('frmNew', 'post');
        $form->addElement('hidden', 'm[0]', 'pdc');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'rootCause');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', 'Why problem appeared?');
        $form->addElement('header', 'title2', '<em>Please list all potential root causes: All related document should be attach in files section (fishbone...)</em>');
        $form->addElement('textarea', 'field1', 'Final root cause', ['wrap' => 'VIRTUAL', 'cols' => '50', 'rows' => '6']);
        $form->addElement('textarea', 'field2', 'Why it hasn\'t been detected?', ['wrap' => 'VIRTUAL', 'cols' => '50', 'rows' => '6']);
        $form->addElement('textarea', 'field3', 'Any further containment actions?', ['wrap' => 'VIRTUAL', 'cols' => '50', 'rows' => '6']);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('field1', 'Required', 'required');
        $form->addRule('field2', 'Required', 'required');
        $form->addRule('field3', 'Required', 'required');
        $form->setDefaults($pdc->getFormDataByName('rootCauseForm'));

        if ($form->validate()) {
            $vars = $form->exportValues();

            try {
                foreach (['field1', 'field2', 'field3'] as $key) {
                    $value = $vars[$key];
                    $translatedField = $client->post('/deepl/translate', ['json' => ['message' => $value, 'module' => 'PDC', 'moduleId' => (int) $pdc->getID()], 'query' => ['createLog' => false]]);
                    $vars[$key] = sprintf('%s (original message: %s)', $translatedField['translatedMessage'], $vars[$key]);
                }
            } catch (ClientException $exception) {
                $DEFAULT_ERROR[] = "ERROR: Text could not be translated.";
                break;
            }

            $formated = <<<EOF
____________________________________________________
<p>Final root cause</p>
____________________________________________________
{$vars['field1']}
____________________________________________________
<p>Why it hasn't been detected?</p>
____________________________________________________
{$vars['field2']}
____________________________________________________
<p>Any further containment actions?</p>
____________________________________________________
<br>
{$vars['field3']}
EOF;
            // record data in field
            $formated = TldDatabase::escape($formated);
            $e = $pdc->update(['root_cause' => $formated]);
            if (is_string($e)) {
                $DEFAULT_ERROR[] = "INTERNAL ERROR: Can not update PDC. Reason: $e";
                break;
            }
            // record form data submitted
            $formData = tldUtils::cleanupFormInput($vars);
            $pdc->saveFormData('rootCauseForm', $formData);
            // refresh to apply update
            $pdc->refresh();
        }

        // Display info + form ----->

        $report = new tldAssocTable(
            $pdc->itsHeader,
            [
                'root_cause' => 'Final root cause',
            ]
        );
        $body = <<<EOF
{$report->fetch()}
<br>
{$form->toHTML()}
EOF;
        break;
    case 'containmentAction':
        $DEFAULT_TITLE .= "\Containment actions";

        // Check status
        if (!in_array($pdc->getStatus(), ['PENDING', 'INVESTIGATION'])) {
            $DEFAULT_ERROR[] = 'ERROR: Can not use containment action form, PDC must be in status PENDING or INVESTIGATION';
            break;
        }

        switch ($m[3]) {
            case 'submit':
                $vars = $_POST['actions'];

                if (isset($_POST['btn'])) {
                    $btnKSubject = key($_POST['btn']);
                    $btnVAction = key($_POST['btn'][$btnKSubject]);
                    $sess['task_body'] = isset($vars[$btnKSubject][$btnVAction]) ? $vars[$btnKSubject][$btnVAction] : '';
                    header("Location: /en/private/calendar/calendar.php?m[0]=tasks&m[1]=form&m[2]=newTask&module=PDC&parent_id=$id");
                    exit;
                }
                $rows = [];
                $formated = null;
                // prepare data
                foreach ($vars as $lineForm => $vals) {
                    // check if there is actions for this subject
                    $flag = false;
                    foreach ($vals as $action => $ref) {
                        if ($action === 'subject' || empty($ref)) {
                            continue;
                        }
                        $flag = true;
                    }
                    if (!$flag) {
                        continue;
                    }
                    // else process
                    $rows[] = $vals;
                    $formated .= <<<EOF
____________________________________________________

<p>{$vals['subject']}</p>
____________________________________________________
<br>

EOF;
                    $tmpFormated = null;
                    foreach ($vals as $action => $ref) {
                        if ($action === 'subject' || empty($ref)) {
                            continue;
                        }
                        try {
                            $value = $ref;
                            $translatedField = $client->post('/deepl/translate', ['json' => ['message' => $value, 'module' => 'PDC', 'moduleId' => (int) $pdc->getID()], 'query' => ['createLog' => false]]);
                            $ref = sprintf('%s (original message: %s)', $translatedField['translatedMessage'], $ref);
                        } catch (ClientException $exception) {
                            $DEFAULT_ERROR[] = "ERROR: Text could not be translated.";
                            break;
                        }
                        $tmpFormated .= <<<EOF
<br>
<p>$action: $ref</p>
<br>
EOF;
                    }
                    $formated .= $tmpFormated;

                }

                if ('NewTask' === $_POST['btnSubmit']) {
                    $sess['task_body'] = $formated;
                    header("Location: /en/private/calendar/calendar.php?m[0]=tasks&m[1]=form&m[2]=newTask&module=PDC&parent_id=$id");
                    exit;
                }

                // check if one line at least entered
                if (!$rows) {
                    $DEFAULT_ERROR[] = 'ERROR: No actions has been selected, make sure to set all informations';
                    break;
                }
                // record data in field
                $formated = TldDatabase::escape($formated);
                $e = $pdc->update(['containment_action' => $formated]);
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "INTERNAL ERROR: Can not update PDC. Reason: $e";
                    break;
                }
                // record form data submitted
                $formData = tldUtils::cleanupFormInput($vars);
                $pdc->saveFormData('containmentActionsForm', $formData);
                // refresh PDC
                $pdc->refresh();
                break;
        }

        // Display Info + form --------->

        // Info
        $report = new tldAssocTable(
            $pdc->itsHeader,
            [
                'containment_action' => 'Containment action',
            ]
        );
        // Form
        $FORM_DEFAULT = $pdc->getFormDataByName('containmentActionsForm');
        $form = include 'form/form.containment_action.tpl.php';
        // Display
        $body = <<<EOF
{$report->fetch()}
<br>
$form
EOF;
        break;
    case 'verificationOfEffectiveness':
        if (!$user->isInGroup(['demerit', 'gg_SUPPORT', 'gg_ADMIN', 'role_CSD'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permissions';
            break;
        }

        $DEFAULT_TITLE.= '\Verification of Effectiveness';


        $noYes = ['' => '',  'No' => 'No', 'Yes' => 'Yes'];
        $form = new HTML_QuickForm('VerificationEffectiveness', 'post');
        $form->addElement(  'hidden', 'm[0]', 'pdc');
        $form->addElement(  'hidden', 'm[1]', 'view');
        $form->addElement(  'hidden', 'm[2]', 'verificationOfEffectiveness');
        $form->addElement(  'hidden', 'id',   $id);
        $form->addElement(  'header', 'title', 'Verification of effectiveness');
        $form->addElement(	'textarea', 'verification_description', 'Description',	['wrap'=>'VIRTUAL', 'cols'=>'80', 'rows'=>'5']);
        $form->addElement(	'select', 'sb_required', 'Is an SB required to close this PDC', $noYes, ['class' => 'sb_required']);
        $form->addElement(	'select', 'sb_created_confirm', '<span class="sb_created_confirm_question">Do you confirm that the SB documentation has already been fully created</span>', $noYes, ['class' => 'sb_created_confirm'] );
        $form->addElement(  'submit', 'btnSubmit', 'Submit');
        $form->addRule('description', 'Required', 'required');
        $form->addRule('sb_required', 'Required', 'required');
        $form->setDefaults(['verification_description' => $pdc->getHeader()['verification_description']]);

        $script= <<<HTML
<script type="text/javascript">

		$(document).ready(function () {
			$('.sb_created_confirm').hide();
			var required = $('.sb_required');
			required.change(function() {
			    if (required.val() == 'Yes') {
				    $('.sb_created_confirm').show();
				    $('.sb_created_confirm_question').show();
				    $('.sb_created_confirm').attr('required', '');
				    $(".sb_created_confirm_question").before('<span style="color:red" class="asterisk">*</span>');
				} else {
			        $('.sb_created_confirm').hide();
                    $('.sb_created_confirm_question').hide();
                    $('.asterisk').hide();
			        $('.sb_created_confirm').removeAttr('required');
				}
			});
        });
	
</script>
HTML;

        if(!$form->validate()){
            $body = $form->toHTML();
            $body .= $script;
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        try {
            $value = $vars['verification_description'];
            $translatedField = $client->post('/deepl/translate', ['json' => ['message' => $value, 'module' => 'PDC', 'moduleId' => (int) $pdc->getID()], 'query' => ['createLog' => false]]);
            $vars['verification_description'] = sprintf('%s (original message: %s)', $translatedField['translatedMessage'], $vars['verification_description']);
        } catch (ClientException $exception) {
            $DEFAULT_ERROR[] = "ERROR: Text could not be translated.";
            break;
        }
        
        $e = $pdc->update(['verification_description' => $vars['verification_description']], ['verification_description']);
        if(is_string($e)){
            $DEFAULT_ERROR[] = 'ERROR: Problem updating...<br/>Reason: $e';
            break;
        }

        $comment = <<<EOF
Verification of effectiveness: {$vars['verification_description']} </b><br>
Is an SB required to close this PDC: {$vars['sb_required']} </b><br>
EOF;
        $comment .= $vars['sb_required'] === 'Yes' ? 'The SB documentation has already been fully created: '.$vars['sb_created_confirm'] : '';

        if ($vars['sb_required'] === 'Yes' && $vars['sb_created_confirm'] === 'Yes') {
            $productSupportManagers = [];
            foreach ($pdc->getFollowers() as $follower) {
                $userFollower = new tldUser($follower['id']);
                if (false !== $userFollower->isInGroup('role_PSM')) {
                    $productSupportManagers[] = $follower['id'];
                }
            }

            if (($assignor = $pdc->getAssignee()) === null) {
                $groupPSM = new tldGroup('role_PSM');
                $PSMs = $groupPSM->getUserlist();
                $factoryId = $pdc->getFactoryID();
                $PSMofPdcFactory = array_filter($PSMs, static function ($psm) use ($factoryId) {
                    return $psm['bu_id'] === $factoryId;
                });
                $assignor = $PSMofPdcFactory[0];
            }

            foreach ($productSupportManagers as $manager) {
                $taskData = [
                    'assignee' => $manager,
                    'assignor' => $assignor,
                    'due_date' => ['value' => 1, 'unit' => 'MONTH'],
                    'escalation_trigger' => 30,
                    'task'=> <<<EOF
<p>Dear PSM,</p>
<p>You were a follower of this PDC# {$pdc->getID()}. By closing this TASK, you acknowledge that an SB will have to be implemented as a corrective action for that PDC# {$pdc->getID()}. Please consider implementing that SB on any applicable RANGER products your Factory manufactured.</p>
EOF
                ];
                tldTask::insert($pdc->getID(), $taskData, 'PDC');
            }
        }

        $pdc->addLogEntry($user->getID(),$comment);

        $body.= '<br/>PDC updated successfully!';
        $body.= _getGeneralView();

        break;
    case 'edit':
        $DEFAULT_TITLE .= "\Edit";
        if (!$user->isInGroup(['demerit', 'gg_SUPPORT', 'gg_ADMIN', 'role_CSD'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permissions';
            break;
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

        // Form
        $form = new HTML_QuickForm('frmNew', 'post');
        $form->addElement('hidden', 'm[0]', 'pdc');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'edit');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', "Edit PDC#$id");
        $form->addElement('select', 'initiator', 'Initiator', $peopleList);
        $form->addElement('select', 'assignee', 'Assignee', ['' => ''] + array_unique($qams + $engineers));
        $form->addElement('select', 'factory', 'Location', $factoryList);
        $form->addElement('select', 'ifactor', 'Importance Factor', $iFactorList);
        $form->addElement('select', 'product_type', 'Product Type', $categoryList);
        $form->addElement('select', 'model', 'Product Model', $modelList);
        $form->addElement('checkbox', 'is_ibs', 'Involves iBS');
        $form->addElement('checkbox', 'is_ihs', 'Involves iHS/ipHS');
        $form->addElement('checkbox', 'is_link', 'Involves LINK');
        $form->addElement('text', 'short_desc', 'Short Description', ['size' => '50']);
        $form->addElement('textarea', 'description', 'Full Description',
            ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']);
        $form->addElement('header', 'title2', "Picture file - Actual: <em>{$header['picture_filename']}</em>");
        $form->addElement('file', 'picture_filename', 'Change picture?');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $requiredList = ['initiator', 'factory', 'ifactor', 'product_type', 'model', 'short_desc', 'description'];
        foreach ($requiredList as $required) {
            $form->addRule($required, 'Required', 'required');
        }
        $form->setDefaults($header);

        if (!$form->validate()) {
            $body .= $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $fields = [
            'assignee' => 'Assignee',
            'initiator' => 'Initiator',
            'factory' => 'Factory',
            'ifactor' => 'IF',
            'product_type' => 'Product Type',
            'model' => 'Product Model',
            'short_desc' => 'Short Description',
            'description' => 'Description',
            'picture_filename' => 'Picture file',
            'is_ibs' => 'Involves iBS',
            'is_ihs' => 'Involves iHS/ipHS',
            'is_link' => 'Involves LINK',
        ];
        // Change picture if any
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
            $vars['picture_filename'] = basicFile::cleanupName(basename($filepath));
            $file->moveUploadedFile($destPath, $vars['picture_filename']);
        } else {
            unset($fields['picture_filename']);
        }
        // Update PDC
        $e = $pdc->update($vars, array_keys($fields));
        if (is_string($e)) {
            $DEFAULT_ERROR[] = "ERROR: Problem updating...<br/>Reason: $e";
            break;
        }

        if ('' !== $vars['factory'] && $header['factory'] !== $vars['factory']) {
            $newLocation = new tldLocation($vars['factory']);
            $message = "<p>A new PDC has been assigned to {$newLocation->getBuName()} .</p>";
            $pdc->notifyInitiator(
                $message,
                "A new PDC has been assigned to {$newLocation->getBuName()}, IF: ".$pdc->getIFactor(),
                [],
                $newLocation->getERP()
            );
        }

        // Add assignee as follower
        tldModMember::insert('PDC', $id, $vars['assignee']);

        $body .= '<br/>PDC updated successfully!';
        // Log changes
        unset($fields['factory'], $fields['initiator'], $fields['assignee']);
        $fields['factory_fullname'] = 'Factory';
        $fields['initiator_fullname'] = 'Initiator';
        $fields['assignee_fullname'] = 'Assignee';
        $changeLog = _generateChangeListLog($fields, $header, $pdc->getHeader());
        $logMsg = "PDC updated:<br>$changeLog";
        $pdc->addLogEntry($user->getID(), TldDatabase::escape($logMsg));
        // Confirm changes
        $body .= $changeLog;
        $body .= _getGeneralView();
        break;
    case 'log':
        $DEFAULT_TITLE .= "\Logs";
        $report = new tldReportColumnar(
            $pdc->getLog(),
            [
                'xItems' => [
                    'id' => 'ID#',
                    'date' => 'Date',
                    'poster_fullname' => 'Poster',
                    'comment' => 'Comment',
                ],
            ]
        );
        $body .= $report->fetch();
        break;
    case 'followers':
        $DEFAULT_TITLE .= "\Followers";
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=pdc&m[1]=view&m[2]=followers&id=$id">Home</a>
&nbsp;|&nbsp;<a href="/en/private/common/index.php?m[0]=members&m[1]=new&module=PDC&parent_id=$id">Add Followers</a>
&nbsp;|&nbsp;<a href="/en/private/common/index.php?m[0]=members&m[1]=delete&module=PDC&parent_id=$id">Unsubscribe Followers</a>
EOF;
        $form = new tldReportColumnar(
            $pdc->getFollowers(),
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
    case 'tasks':
        $DEFAULT_TITLE .= "\Tasks";

        if (!$pdc->isClosed()) {
            $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=form&m[2]=newTask&module=PDC&parent_id=$id">New Task</a>
EOF;
        }

        $sess['calendar']['tasks'] = $pdc->getStatusTasksByConstraints('1=1');
        $form = new tldReportMultiLevel(
            array_merge($sess['calendar']['tasks'], $pdc->getModuleLinkToPdc()),
            ['pdc_status', 'status'],
            [
                'id' => 'Task#',
                'status' => 'Status',
                'module' => 'Module',
                'due_date' => 'Due',
                'task' => 'Task',
                'assignor_fullname' => 'Assignor',
                'assignee_fullname' => 'Assignee',
            ],
            [
                'passField' => 'id',
                'title' => 'Tasks by PDC status',
                'url' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=',
            ]
        );
        $body .= $form->fetch();
        break;
    case 'files':
        $DEFAULT_TITLE .= "\Files";
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=pdc&m[1]=view&m[2]=files&m[3]=add&id=$id">Add File</a>
EOF;

        switch ($m[3]) {
            case 'add' :
                $form = new HTML_QuickForm('frmAddFIle', 'post');
                $form->addElement('hidden', 'm[0]', 'pdc');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('hidden', 'm[2]', 'files');
                $form->addElement('hidden', 'm[3]', 'add');
                $form->addElement('hidden', 'id', $id);
                $form->addElement('header', 'titleInfo', 'Warning: Description is required for each file. Filename length is limited to ' . tldFile::fileNameLengthLimit . ' chars.');

                for ($i = 0 ; $i < 5; $i++) {
                    $form->addElement('textarea', "description[$i]", 'File Description', ['wrap' => 'VIRTUAL', 'cols' => '60', 'rows' => '4']);
                    $form->addElement('file', "files[$i]", 'Attachment');
                }
                $form->addElement('submit', 'btnSubmit', 'Submit');

                if (!$form->validate()) {
                    $body .= $form->toHTML();
                    break 2;
                }

                $vars = tldUtils::cleanupFormInput($form->exportValues());
                for ($i = 0; $i < 5; $i++) {
                    $file = $form->getElement("files[$i]");
                    $fileArray = $file->getValue();

                    // Message for file import errors
                    if (UPLOAD_ERR_INI_SIZE === $fileArray['error'] || UPLOAD_ERR_FORM_SIZE === $fileArray['error']) {
                        $DEFAULT_ERROR[] = sprintf(
                            "%s : File %s is too large (max %s per file and %s for all)",
                            $fileArray['name'],
                            $fileArray['name'],
                            ini_get('upload_max_filesize'),
                            ini_get('post_max_size')
                        );
                    }

                    // for other errors, just skip
                    if (!$fileArray['tmp_name']) {
                        continue;
                    }

                    // Validate form data
                    if (empty($vars['description'][$i])) {
                        $DEFAULT_ERROR[] = "{$fileArray['name']} : Description is mandatory for {$fileArray['name']} file<br/>";
                        continue;
                    }

                    $vars['filename'] = $fileArray['name'];
                    $e = tldModFile::insert(
                        [
                            'module' => 'PDC',
                            'parent_id' => $id,
                            'description' => $vars['description'][$i],
                            'filename' => $vars['filename'],
                            'poster' => $user->getId(),
                        ],
                        $fileArray
                    );

                    if (is_string($e)) {
                        $DEFAULT_ERROR[] = "{$fileArray['name']} : Problem adding the TLD File in the database...<br/> $e";
                        break;
                    }
                    $DEFAULT_SUCCESS[] = "{$fileArray['name']} : File#$e uploaded and successfully attached to PDC#$id.<br/>";
                }
                break;
        }

        $files = [];
        foreach ($pdc->getFiles() as $file) {
            $thumbnail = null;
            if (in_array(strtolower($file['extension']), ['jpg', 'jpeg', 'png', 'gif'], true)) {
                $thumbnail = sprintf('<img src="/en/private/common/index.php?m[0]=files&m[1]=view&m[2]=out&id=%s" style="max-width: 40px">', $file['id']);
            }
            $files[] = ['thumbnail' => $thumbnail] + $file;
        }
        // TLD files
        $report = new tldReportColumnar(
            $files,
            [
                'xItems' => [
                    'id' => 'File ID',
                    'date' => 'Date',
                    'description' => 'Description',
                    'thumbnail' => 'Thumbnail',
                    'filename' => 'Filename',
                ],
                'links' => [
                    'id' => '/en/private/common/index.php?m[0]=files&m[1]=view&m[2]=out&id=',
                ],
                'functions' => [
                    'Delete' => [
                        'url' => '/en/private/common/index.php?m[0]=files&m[1]=view&m[2]=del',
                        'param' => ['id' => 'id'],
                        'confirmPopup' => 'Are you sure to delete?',
                        'img' => '/shared/icons/miscellaneous/delete.png',
                    ],
                ],
                'title' => 'PDC Files',
            ]
        );
        $body .= $report->fetch();
        break;

    case 'parts':
        $DEFAULT_TITLE .= "\Parts";
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/en/private/common/index.php?m[0]=parts&m[1]=add&module=PDC&parent_id=$id">Add Parts</a>
EOF;
        $report = new tldReportColumnar(
            $pdc->getParts(),
            [
                'xItems' => [
                    'pn' => 'Part Number',
                    'dsc' => 'Description',
                ],
                'title' => 'PDC Parts',
                'functions' => [
                    'File' => [
                        'url' => "/en/private/manufacturing/eng/dev.php?m[0]=getfile&m[1]=drawing&erp={$pdc->itsHeader['factory_erp']}&date=" . date('Y-m-d'),
                        'param' => ['item' => 'pn'],
                        'img' => '/shared/bluesphere/16x16/actions/filesaveas.png',
                    ],
                    'Edit' => [
                        'url' => '/en/private/common/index.php?m[0]=parts&m[1]=view&m[2]=edit',
                        'param' => ['id' => 'id'],
                        'img' => '/shared/icons/miscellaneous/edit.png',
                    ],
                    'Delete' => [
                        'url' => '/en/private/common/index.php?m[0]=parts&m[1]=view&m[2]=delete&conf=Y',
                        'param' => ['id' => 'id'],
                        'confirmPopup' => 'Are you sure to delete?',
                        'img' => '/shared/icons/miscellaneous/delete.png',
                    ],
                ],
                'links' => [
                    'pn' => "/en/private/manufacturing/eng/dev.php?m[0]=bom&m[1]=view&erp={$pdc->itsHeader['factory_erp']}&pn=",
                ],
                'showItemNumbers' => true,
            ]
        );
        $body .= $report->fetch();
        break;
    case 'links':
        $DEFAULT_MENU .= <<<EOF
    <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="/en/private/common/index.php?m[0]=links&m[1]=form&m[2]=newLink&module=PDC&parent_id=$id">New Link</a>
EOF;
        $report = new tldReportColumnar(
            $pdc->getLinksFromHere(),
            [
                'xItems' => [
                    'id' => 'ID#',
                    'type' => 'Module',
                    'item' => 'Ref#',
                    'dsca' => 'Description',
                ],
                'title' => 'Links FROM Here...',
                'links' => [
                    'id' => '/en/private/common/index.php?m[0]=links&m[1]=view&id=',
                ],
            ]
        );
        $body .= $report->fetch();
        $report = new tldReportColumnar(
            $pdc->getLinksToHere(),
            [
                'xItems' => [
                    'id' => 'ID#',
                    'module' => 'Module',
                    'parent_id' => 'Ref#',
                    'dsca' => 'Description',
                ],
                'title' => 'Links TO Here...',
                'links' => [
                    'id' => '/en/private/common/index.php?m[0]=links&m[1]=view&reversed=1&id=',
                ],
            ]
        );
        $body .= $report->fetch();
        break;
    case 'status':
        if (!$user->isInGroup(['gg_ADMIN', 'role_PSM', 'role_PSE', 'role_PSA', 'role_QAM', 'role_EM', 'role_RME']) && $pdc->itsHeader['assignee'] !== $user->getID()) {
            $DEFAULT_ERROR[] = 'You do not have permission to change PDC status';
            break;
        }
        // Listing
        $statusList = $pdc->getAllowedStatus();
        if (empty($statusList)) {
            $DEFAULT_ERROR[] = 'ERROR: No allowed status found with actual status';
            break;
        }

        /**
         * This workflow got two fixes because we do not know business logic. And we let the MOO decide himself how
         * to manage this worklow.
         * If we got another TTS on this, REWORK SOLUTION
         * @see TTS#7778 TTS#9808 TTS#11541
         */
        $currentStatus = $pdc->getStatus();

        // Group for $limitedTransitions: 'role_EM', 'role_RME' TTS#3652
        $limitedTransitions = [
            'PENDING' => ['INVESTIGATION'],
            'INVESTIGATION' => ['ACTION', 'INVESTIGATION'],
            'ACTION' => ['ACTION']
        ];

        $fullTransitionGroups = ['gg_ADMIN', 'role_PSM', 'role_PSE', 'role_PSA', 'role_QAM'];
        if (!$user->isInGroup($fullTransitionGroups)){
            $statusList = $limitedTransitions[$currentStatus] ?? [];
        }

        if (empty($statusList)) {
            $DEFAULT_ERROR[] = 'You do not have permission to change PDC with status : '.$currentStatus;
            break;
        }

        if (in_array('CLOSED', $statusList, true) && (string) $pdc->itsHeader['verification_description'] === ''){
            unset ($statusList['CLOSED']);
            $DEFAULT_ERROR[] = 'To close PDC, verification of effectiveness must be completed';
        }
        $peopleList = tldDirectory::getUserlist('smartyOptions');
        $members = $pdc->getFollowers();
        $pdcLastLog = $pdc->getLog()[0];
        // Form
        $form = new HTML_QuickForm('frmNew', 'post');
        $form->addElement('hidden', 'm[0]', 'pdc');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'status');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', 'Update status');
        if ($user->isInGroup(['gg_ADMIN', 'role_PSM', 'role_PSE', 'role_PSA', 'role_QAM', 'role_EM', 'role_RME'])) {
            $form->addElement('select', 'status', 'Status',
                array_combine($statusList, $statusList));
            $form->addRule('status', 'Required', 'required');
        } else {
            $form->addElement('select', 'status', 'Status',
                array_combine($statusList, $statusList), ['disabled' => 'disabled']);
        }
        $form->addElement('textarea', 'reason', 'Reason',
            ['wrap' => 'VIRTUAL', 'cols' => '100', 'rows' => '20']);
        $form->setDefaults(['reason' => $pdcLastLog['comment']]);
        $form->addElement('checkbox', 'is_ready_to_close', 'Ready to close');
        $form->setDefaults(['is_ready_to_close' => $pdc->itsHeader['is_ready_to_close']]);
        $ams =& $form->addElement('advmultiselect', 'userids', null,
            $peopleList,
            [
                'size' => 10,
                'class' => 'pool',
                'style' => 'width:500px;',
            ]
        );
        $ams->setLabel(['CC others (max 20 recipients)... (OPTIONAL)', 'Addressbook', 'CC']);
        $ams->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
        $ams->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
        // Default values
        $defaultMembers = [];
        foreach ($members as $member) {
            $defaultMembers[] = $member['id'];
        }
        $form->setDefaults(['userids' => $defaultMembers, 'status' => $pdc->getStatus()]);
        // rules
        $form->addRule('reason', 'Required', 'required');
        $form->addElement('submit', 'btnSubmit', 'Submit');

        if (!$form->validate()) {
            $body .= $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());

        $vars['is_ready_to_close'] = $vars['is_ready_to_close'] ?? '0';

        if ($vars['status'] === 'CLOSED' && !empty($errors = $pdc->isAllowedToBeClosed($pdc->getID()))) {
            foreach ($errors as $error) {
                $DEFAULT_ERROR[] = $error;
            }
            break;
        }

        if ($userHasLimitedTransition && !in_array($vars['status'], $limitedTransitions[$currentStatus] ?? [], true)) {
            $DEFAULT_ERROR[] = 'You are not allowed to change status to : '.$vars['status'];
            break;
        }

        $form->setDefaults($vars);
        // Look for options
        $options = [];
        // Record reason
        $options['reason'] = $vars['reason'];
        $options['is_ready_to_close'] = $vars['is_ready_to_close'];
        // Check options for notification
        if (count($userids ?? [])) {
            foreach ($userids as $userid) {
                $cc_user = new tldUser($userid);
                $options['cc'][] = $cc_user->getEmail();
            }
        }
        // Change status
        $e = $pdc->updateStatus($user->getID(), $vars['status'], $options);
        if (is_string($e)) {
            $DEFAULT_ERROR[] = "ERROR: Problem updating...<br/>Reason: $e";
            break;
        }
        // Confirmation message
        $body .= "<br>PDC status updated successfully to {$vars['status']}!";
        $pdc->refresh();
        $body .= _getGeneralView();
        break;
    case 'delete':
        if (!$user->isInGroup(['superuser']) && $user->getID() <> $moo_id) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permissions';
            break;
        }
        $form = new HTML_QuickForm('frmNew', 'post');
        $form->addElement('hidden', 'm[0]', 'pdc');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'delete');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', "Delete PDC#$id ?");
        $form->addElement('select', 'confirm', 'Do you confirm?',
            ['' => '', 'Y' => 'Yes, I confirm']);
        $form->addRule('confirm', 'Required', 'required');
        $form->addElement('submit', 'btnSubmit', 'Submit');

        if (!$form->validate()) {
            $body .= $form->toHTML();
            break;
        }

        $e = $pdc->delete();
        if (is_string($e)) {
            $DEFAULT_ERROR[] = "ERROR: PDC not deleted. Reason: $e";
            break;
        }
        $pdc->addLogEntry($user->getID(), 'PDC deleted');
        $body = "PDC#$id deleted successfully!";
        break;
    case 'sheet':
        // Prepare the HTML view
        $viewHTML = null;
        // PDC info
        $pdcInfo = new tldAssocTable(
            $pdc->itsHeader,
            [
                'id' => 'PDC#',
                'date' => 'Date',
                'poster_fullname' => 'Poster',
                'initiator_fullname' => 'Initiator',
                'date' => 'Date',
                'status' => 'Status',
                'factory_fullname' => 'Factory',
                'product_type' => 'Equipment Type',
                'model' => 'Equipment Mdel',
                'short_desc' => 'Short description',
                'description' => 'Description',
            ]
        );
        $pdcParts = new tldReportColumnar(
            $pdc->getParts(),
            [
                'xItems' => [
                    'pn' => 'Part Number',
                    'dsc' => 'Description',
                ],
                'title' => 'Parts',
                'sortable' => 'no',
            ]
        );
        // PDC main file
        $pdcFileName = $pdc->getFileName();
        $pdcImg = null;
        $pdcMainFile = new basicFile($pdc->getFilePath());
        if ($pdcMainFile->isFileExists()) {
            $typeMime = $pdcMainFile->getMimeTypeFromExtension();
            $typeMimeSplited = preg_split('#/#', $typeMime);
            if (strtolower($typeMimeSplited[0]) === 'image') {
                $pdcImg = <<<EOF
<img src="data:$typeMime;base64,{$pdcMainFile->getBase64()}" width="250" />
EOF;
            } else {
                $pdcImg = "<p><em>File: $pdcFileName</em></p>";
            }
        } else {
            $pdcImg = '<p><em>File: none</em></p>';
        }

        // investigation
        $investigation = null;
        $investigationInfo = new tldAssocTable(
            $pdc->itsHeader,
            [
                'containment_action' => 'Containment actions',
                'root_cause' => 'Final root cause',
            ]
        );
        $investigation .= $investigationInfo->fetch();
        $taskInvestigation = new tldReportColumnar(
            $pdc->getStatusTasksByConstraints(['pdc_status' => 'INVESTIGATION']),
            [
                'xItems' => [
                    'id' => 'Task#',
                    'status' => 'Status',
                    'due_date' => 'Due date',
                    'task' => 'Task',
                    'assignee_fullname' => 'Assignee',
                ],
                'title' => 'Investigation tasks',
                'sortable' => 'no',
            ]
        );
        $investigation .= $taskInvestigation->fetch();
        // action
        $action = null;
        $actionInfo = new tldAssocTable(
            $pdc->itsHeader,
            [
                'corrective_action' => 'Corrective action',
                'preventive_action' => 'Preventive action',
            ]
        );
        $action .= $actionInfo->fetch();
        $taskAction = new tldReportColumnar(
            $pdc->getStatusTasksByConstraints(['pdc_status' => 'ACTION']),
            [
                'xItems' => [
                    'id' => 'Task#',
                    'status' => 'Status',
                    'due_date' => 'Due date',
                    'task' => 'Task',
                    'assignee_fullname' => 'Assignee',
                ],
                'title' => 'Action tasks',
                'sortable' => 'no',
            ]
        );
        $action .= $taskAction->fetch();
        // closure
        $closure = null;
        if ($pdc->getStatus() === 'CLOSED') {
            $closureInfo = new tldAssocTable(
                $pdc->itsHeader,
                [
                    'date_closed' => 'Closed date',
                    'final_fweight' => 'Final FW',
                    'resolution' => 'Resolution',
                ]
            );
            $closure .= $closureInfo->fetch();
        } elseif ($pdc->getStatus() === 'REJECTED') {
            $closureInfo = new tldAssocTable(
                $pdc->itsHeader,
                [
                    'rejection_reason' => 'Reason for Rejecting',
                ]
            );
            $closure .= $closureInfo->fetch();
        } else {
            $closure .= '<p>Not closed yet</p>';
        }
        $generationDateInfo = date('Y-m-d (H:i)');
        // HTML
        $viewHTML = <<<EOF
<html>
  <head>
    <style>
body { font-family: arial; }
#header { border: 1px solid black; padding: 10px; text-align: center;}
#footer p { font-size: 8px; text-align: center; color: grey; margin-top: 10px;}
h2 { font-size: 16px; border-bottom: 1px solid black; padding: 5px;}
h3 { font-size: 13px;}
td, th, p { font-size: 10px;}

    </style>
  </head>
  <body>
  	<!-- HEADER -->
    <div id="header">
      <h1>PDC#{$pdc->getID()}</h1>
      <p>{$pdc->getShortDescription()}</p>
    </div>
	<!-- PDC INFO -->
	<h2>DETAILS</h2>
    <table width="100%" padding="5%">
	  <tr>
	    <td width="55%">
          {$pdcInfo->fetch()}
          {$pdcParts->fetch()}
	    </td>
	    <td width="35%">$pdcImg</td>
      </tr>
    </table>
	<!-- PDC INVESTIGATION -->
	<div style="page-break-inside:avoid;">
	  <h2>INVESTIGATION</h2>
	    $investigation
    </div>
	<!-- PDC ACTION -->
	<div style="page-break-inside:avoid;">
	  <h2>ACTION</h2>
	    $action
    </div>
	<!-- PDC CLOSURE -->
	<div style="page-break-inside:avoid;">
	  <h2>CLOSURE</h2>
	    $closure
    </div>
    <!-- FOOTER -->
    <br>
    <div id="footer">
      <p>Generated $generationDateInfo by {$user->getFullname()}</p>
    </div>
  </body>
</html>
EOF;
        // Transform and send in PDF
        $pdf = new tldHTML2PDF($viewHTML);
        $pdf->outFile("PDC#$id.pdf");
        break;
    default:
        $body .= _getGeneralView();
        break;
}


function _getGeneralView()
{
    global $pdc, $smarty, $PATH;
    $view = null;
    // Display Next/prev links
    $smarty->assign('pdc', $pdc->itsHeader);
    $view = $smarty->fetch("$PATH/view/pdc.menu.tpl");
    // Template view
    $view .= include 'view/pdc.view.tpl.php';
    // Return the complete view
    return $view;
}

function _generateChangeListLog($fields, $DataBefore, $DataAfter)
{
    $list = [];
    foreach ($fields as $field => $label) {
        if (!isset($DataBefore[$field]) || !isset($DataAfter[$field])) {
            continue;
        }
        if ($DataBefore[$field] == $DataAfter[$field]) {
            continue;
        }
        $list[] = "<li>$label <b>from</b> {$DataBefore[$field]} <b>to</b> {$DataAfter[$field]}</li>";
    }
    // if nothing changed
    if (empty($list)) {
        return false;
    }
    return '<ul>' . implode('', $list) . '</ul>';
}
