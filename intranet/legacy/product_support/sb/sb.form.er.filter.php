<?php

// ER FILTER ------------------------------------------------->

$FILTER_CONSTRAINTS = null;

// Create constraints from SSO switch
if (is_a($SSO, 'tldLocation') && !$SSO->isEmpty()) {
    $FILTER_CONSTRAINTS[] = " sso.id={$SSO->itsID}";
}
// Constraints for Listing
$constraintSelectForm = _constructQueryFromArrayConstraints($FILTER_CONSTRAINTS);

// Listing
$FILTER_CSRList = tldCSR::getStatusList();
$FILTER_SPRList = tldSPR::getStatusList();
$FILTER_BuyerCustomerList = $sb->getImpactedBuyerCustomerByConstraints($constraintSelectForm);
$FILTER_UserCustomerList = $sb->getImpactedUserCustomerByConstraints($constraintSelectForm);
$FILTER_APCList = $sb->getImpactedAPCByConstraints($constraintSelectForm);
$FILTER_CountryList = $sb->getImpactedCountryByConstraints($constraintSelectForm);
$FILTER_yesNoList = ['' => '', 'Y' => 'Y', 'N' => 'N'];
$FILTER_isiList = tldSB_Line::getStatusList();

// Form
$formFilter = new HTML_QuickForm('frmFilters');
$formFilter->addElement('hidden', 'm[0]', 'sb');
$formFilter->addElement('hidden', 'm[1]', 'view');
$formFilter->addElement('hidden', 'm[2]', 'summary');
$formFilter->addElement('hidden', 'm[3]', $m[3]);
$formFilter->addElement('hidden', 'id', $id);
$formFilter->addElement('header', 'headform', 'Filter form');
$formFilter->addElement('select', 'user_customer_id', 'User customer', ['' => ''] + $FILTER_UserCustomerList);
$formFilter->addElement('select', 'buyer_customer_id', 'Buyer customer', ['' => ''] + $FILTER_BuyerCustomerList);
$formFilter->addElement('select', 'apc_country_name', 'APC country', ['' => ''] + $FILTER_CountryList);
$formFilter->addElement('select', 'apc_code', 'APC', ['' => ''] + $FILTER_APCList);
if (isset($m[3]) && $m[3] === 'selection') {
    $formFilter->addElement('select', 'part_decision', 'Parts undecided', $FILTER_yesNoList);
    $formFilter->addElement('select', 'service_decision', 'Service undecided', $FILTER_yesNoList);
} else {
    $formFilter->addElement('select', 'status', 'ISI', ['' => ''] + $FILTER_isiList);
    $formFilter->addElement('text', 'shipped_from', 'Shipped from', ['class' => 'datepicker']);
    $formFilter->addElement('text', 'shipped_to', 'Shipped to', ['class' => 'datepicker']);
}
$formFilter->addElement('advmultiselect', 'spr_status', 'SPR Status', $FILTER_SPRList,
    [
        'size' => 3,
        'class' => 'pool',
        'style' => 'width:120px;',
    ]
);
$formFilter->addElement('advmultiselect', 'csr_status', 'CSR Status', $FILTER_CSRList,
    [
        'size' => 4,
        'class' => 'pool',
        'style' => 'width:120px;',
    ]
);
$formFilter->addElement('submit', 'btnSubmit', 'Submit');
$formFilter->addElement('reset', 'btnReset', 'Reset');

// On validation -------------------->

if ($formFilter->validate()) {
    $vars = tldUtils::cleanupFormInput($formFilter->exportValues());
    // Clean unused vars
    unset($vars['id'], $vars['m'], $vars['btnReset'], $vars['btnSubmit']);
    // get constraints and add it to the SB LINE CONSTRAINTS
    $constraintsForm = _getConstraintsFromFilterData($vars);
    $SB_LINES_CONSTRAINTS = array_merge((array)$SB_LINES_CONSTRAINTS, (array)$constraintsForm);
    // assign submitted vars as default to the form
    $formFilter->setDefaults($vars);
    // record filters in session
    $sess['sb'][$sb->itsID]['filters'] = $vars;
} // If filters was applied before
elseif (!empty($sess['sb'][$sb->itsID]['filters'])) {
    // Clean data from session
    $vars = tldUtils::cleanupFormInput($sess['sb'][$sb->itsID]['filters']);
    // Get constraints
    $constraintsSession = _getConstraintsFromFilterData($vars);
    $SB_LINES_CONSTRAINTS = array_merge((array)$SB_LINES_CONSTRAINTS, (array)$constraintsSession);
    // assign submitted vars as default to the form
    $formFilter->setDefaults($vars);
}

// Display filter form ----------->

$FilterStatus = _isFilterActive() ? 'Active' : 'Not used';


$body .= <<<EOF
<br>
<a href="#" onclick="javascript:$('#filter').toggle();">Show/hide FILTER FORM</a> (status: $FilterStatus)
<div id="filter" style="display:none;">
{$formFilter->toHTML()}
</div>
<br>
<br>
EOF;

// FUNCTIONS ------------------->

function _getConstraintsFromFilterData($vars)
{
    $constraints = [];
    $filters = ['spr_status' => 'spr.status', 'csr_status' => 'csr.status', 'user_customer_id' => 'er.customer_id', 'buyer_customer_id' => 'er.buyer_customer_id', 'apc_country_name' => 'apc_country_name', 'apc_code' => 'er.airport_code', 'part_decision' => 'part_decision', 'service_decision' => 'service_decision', 'status' => 'sb_lines.status', 'shipped_from' => 'er.date_shipped', 'shipped_to' => 'er.date_shipped'];
    // Look for filter constraints to apply
    foreach ($filters as $filter => $column) {
        if (empty($vars[$filter])) {
            continue;
        }
        switch ($filter) {
            case 'part_decision':
            case 'service_decision':
                $constraints[] = $vars[$filter] === 'Y' ? "$column LIKE ''" : "$column NOT LIKE ''";
                break;
            case 'shipped_from':
                $constraints[] = "DATEDIFF($column,'{$vars[$filter]}')>=0";
                break;
            case 'shipped_to':
                $constraints[] = "DATEDIFF($column,'{$vars[$filter]}')<=0";
                break;
            case 'apc_country_name':
                // Hack to inform that we can't replace the HAVING clause by a WHERE
                $constraints['need_having'] = true;
                $constraints[] = "$column LIKE '{$vars[$filter]}'";
                break;
            case 'spr_status':
            case 'csr_status':
                $csrSpr = implode("','", $vars[$filter]);
                $constraints[] = "$column IN('{$csrSpr}')";
                break;
            default:
                $constraints[] = "$column LIKE '{$vars[$filter]}'";
                break;
        }
    }
    return $constraints;
}

function _isFilterActive()
{
    global $sess, $sb;
    $filters = $sess['sb'][$sb->itsID]['filters'];

    return (bool)array_filter($filters ?? []);
}
