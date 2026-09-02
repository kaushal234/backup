<?php
include_once('common.inc.php');

$DEFAULT_TITLE .= ' \PIO Family Admin';
$user = new tldUser($_SERVER['PHP_AUTH_USER']);
if (!$user->isInGroup(['superuser'])) {
    $DEFAULT_ERROR[] = 'Sorry you cannot access to this page';
    return;
}

switch ($m[2]) {
    case 'duplicates':
        $DEFAULT_TITLE .= "\Remove Duplicates Questions";
        $form = new HTML_QuickForm('duplicatesForm', 'post');
        $form->addElement('hidden', 'm[0]', 'pi');
        $form->addElement('hidden', 'm[1]', 'troubleshooting');
        $form->addElement('hidden', 'm[2]', 'duplicates');
        $form->addElement('header', 'title', 'Remove Duplicates Questions');
        $form->addElement('text', 'sn', 'Equipment SN');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('sn', 'Required', 'required');
        $body .= $form->toHTML();

        if ($form->validate()) {
            $sn = (tldUtils::cleanupFormInput($form->exportValues()))['sn'];
            $equipmentRecord = tldEquipment::bySN($sn);
            if (empty($equipmentRecord)) {
                $DEFAULT_ERROR[] = 'No equipment found for this SN';
                break;
            }
            $e = tldPI::removeDuplicateQuestions($sn);
            if (is_string($e)){
                $DEFAULT_ERROR[] = sprintf('An SQL error occurred while removing duplicates questions on Unit <b>%s</b> : %s', $sn, $e);
                break;
            }
            $DEFAULT_SUCCESS[] = sprintf('The duplicates questions were successfully removed from unit <b>%s</b>', $sn);
        }
        break;
    case 'insertQuestions':
        $DEFAULT_TITLE .= "\Insert Question on ER";
        $form = new HTML_QuickForm('insertQuestionsForm', 'post');
        $form->addElement('hidden', 'm[0]', 'pi');
        $form->addElement('hidden', 'm[1]', 'troubleshooting');
        $form->addElement('hidden', 'm[2]', 'insertQuestions');
        $form->addElement('header', 'title', 'Insert Question on ER');
        $form->addElement('text', 'sn', 'Equipment SN');
        $form->addElement('text', 'questionId', 'Question Id');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('sn', 'Required', 'required');
        $form->addRule('questionId', 'Required', 'required');
        $body .= $form->toHTML();

        if ($form->validate()) {
            $values = tldUtils::cleanupFormInput($form->exportValues());
            
            $arrayEquipmentRecord = tldEquipment::bySN($values['sn']);
            if (empty($arrayEquipmentRecord)) {
                $DEFAULT_ERROR[] = 'No equipment found for this SN';
                break;
            }
            $equipmentRecord = new tldEquipment((int) current($arrayEquipmentRecord)['id']);
            
            $questionId = $values['questionId'];
            $question = tldUtils::getSqlRowToAssocArray(sprintf('SELECT * from pi_questions WHERE id=%s AND active = "Y"', $questionId));
            if (empty($question)){
                $DEFAULT_ERROR[] = 'Question does exists or is not Active';
                break;
            }
            
            $e = tldPI::insertQuestion($equipmentRecord, $questionId);
            if (is_string($e)){
                $DEFAULT_ERROR[] = sprintf('An SQL error occurred while inserting question <b>#%s</b> on Unit <b>#%s</b> : %s', $questionId, $equipmentRecord->getSN(), $e);
                break;
            }
            $DEFAULT_SUCCESS[] = sprintf('Question  <b>#%s</b> has been successfully inserted on unit <b>#%s</b>', $questionId, $equipmentRecord->getSN());
        }
        break;

    default:
        $body .= <<<HTML
<p>Welcome to the P&I troubleshooting Page</p>
HTML;
        break;
}

$DEFAULT_MENU .= <<<HTML
<br>
<a style="margin-left: 4%" href="$php_self?m[0]=pi&m[1]=troubleshooting">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pi&m[1]=troubleshooting&m[2]=duplicates">Remove Duplicates Questions</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pi&m[1]=troubleshooting&m[2]=insertQuestions">Insert Questions</a>
HTML;
