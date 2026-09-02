<?php
$DEFAULT_TITLE .= "\Status";

// Permissions  -------------------------------->
/** @var tldSB3 $sb */
switch ($actualStatus = $sb->getStatus()) {
    case 'PENDING':
        if (!$user->isInGroup(['role_PSM', 'role_PSE', 'role_PSA', 'gg_SUPPORT'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permissions';
            return;
        }

        switch ($sb->getCategory()) {
            case 'COMPULSORY':
                $psmConfirmationMessage = 'PSM confirms parts availability to minimum 50% of ERs';
                break;
            case 'RECOMMENDED':
                $psmConfirmationMessage = 'PSM confirms parts availability to minimum 20% of ERs';
                break;
            default:
                $psmConfirmationMessage = '';
        }

        break;
    case 'IMPLEMENTATION':
    case 'PARTIAL_IMPLEMENTATION':
        if (!$user->isInGroup(['role_PSM', 'role_PSE', 'role_PSA'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permissions';
            return;
        }
        break;
}

// Special Case when Rejected back to PENDING
$options = [];
if (isset($m[3]) && $m[3] === 'reject') {
    if (!$user->isInGroup(['role_CSM', 'role_EVP'])) {
        $DEFAULT_ERROR[] = 'ERROR: You do not have permissions';
        return;
    }

    $requiredRoleByStatus = [
        'CSM_APPROVAL' => 'role_CSM',
        'SSD_DECISION' => 'role_EVP',
    ];

    $status = $sb->getStatus();
    $requiredRole = $requiredRoleByStatus[$status] ?? null;

    if ($requiredRole !== null && !$user->isInGroup([$requiredRole])) {
        $DEFAULT_ERROR[] = "ERROR: You can only REJECT a SB while in {$status} status";
        return;
    }
    $options = ['reject' => true];
}

//option set to disable security for non-GT ERs
$options['unset_gt'] = true;

// Listing
if (is_string($statusList = $sb->getAllowedStatus($options))) {
    $DEFAULT_ERROR[] = 'ERROR: No allowed status found with actual status';
    $DEFAULT_ERROR[] = "Reason: $statusList";
    return;
}

// Form ---------------------------------------------------------->

$form = new HTML_QuickForm('frmNew', 'post');
$form->addElement('hidden', 'm[0]', 'sb');
$form->addElement('hidden', 'm[1]', 'view');
$form->addElement('hidden', 'm[2]', 'status');
$form->addElement('hidden', 'm[3]', $m[3]);
$form->addElement('hidden', 'id', $id);
$form->addElement('header', 'title', 'Update SB status');
if ('PENDING' === $actualStatus && $psmConfirmationMessage) {
    $form->addElement('select', 'factory_part_availability_status', $psmConfirmationMessage, ['' => ''] + tldSB3::getPartsAvailabilityChoicesList(), ['class' => 'js-parts-status', 'style' => 'min-width: 250px']);
    $form->addRule('factory_part_availability_status', 'Required', 'required');
    $form->addElement('text', 'parts_availability_estimated_date', 'Estimated parts availability date', ['class' => 'datepicker js-parts-date']);
    $form->addElement('textarea', 'category_reason', "Further indications for CSMs<br>(SB category rationale, ER coverage, etc.)<br>Not viewed by customers", ['wrap' => 'VIRTUAL', 'cols' => '30', 'rows' => '3']);
    $form->setDefaults(['category_reason' => $sb->itsHeader['category_reason']]);
}
$form->addElement('select', 'status', 'Status', array_combine($statusList, $statusList));
if (isset($options['reject']) && $options['reject']) {
    $form->addElement('textarea', 'comment', 'Comments', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']);
    $form->addRule('comment', 'Required', 'required');
}
$form->addRule('status', 'Required', 'required');
$form->addElement('submit', 'btnSubmit', 'Submit');
$body .= <<<JS
<script>
$(document).ready(function() {
    function updateForm() {
        if ('Not enough stock' === $('.js-parts-status').val()) {
          $('.js-parts-date').closest('tr').show()
          return
        }
        
        $('.js-parts-date').val('')
        $('.js-parts-date').closest('tr').hide()
    }
    
     $('.js-parts-status').change(updateForm)
    updateForm()
});
</script>
JS;

if (!$form->validate()) {
    $body .= $form->toHTML();
    return;
}

$vars = tldUtils::cleanupFormInput($form->exportValues());

if ($vars['category_reason'] ?? null) {
    $sb->update($vars, ['category_reason']);
}
if ('Not enough stock' === $vars['factory_part_availability_status']) {
    if (!($vars['parts_availability_estimated_date'] ?? null)) {
        $DEFAULT_ERROR[] = 'You need to enter an estimated date for parts availability.';
        $body .= $form->toHTML();
        return;

    }
    $DEFAULT_ERROR[] = 'Per DMS 888, PSM needs to ensure appropriate parts inventory level prior to releasing the SB, a reminder task has been opened.';
    $sb->update($vars, ['factory_part_availability_status']);
    $options['parts_availability_estimated_date'] = $vars['parts_availability_estimated_date'];
}
$sb->refresh();

$options['comment'] = $vars['comment'];
// Change status
$e = $sb->updateStatus($user->getID(), $vars['status'], $options);
if (is_string($e)) {
    $DEFAULT_ERROR[] = "ERROR: Problem updating...<br/>Reason: $e";
    return;
}
// Confirmation message
$body .= "<br>Status updated successfully to {$vars['status']}";
$body .= _getGeneralView();
