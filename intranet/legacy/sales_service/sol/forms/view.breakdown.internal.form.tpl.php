<?php

// listing
$internalCategoryList = tldSOL::getInternalCategoriesList();
$currencyList = array_keys($sol->getCURS());

// form fields --->

$form->addElement('header', 'title', 'Internal Item Form');
$form->addElement('select', 'caty', 'Category', ['' => ''] + $internalCategoryList);
$form->addElement('text', 'dsca', 'Description', ['size' => 30]);
// -- Published TP
$mrspGRP = [];
$mrspGRP[] =& $form->createElement('select', 'imrsp_cur', 'Currency', $currencyList);
$mrspGRP[] =& $form->createElement('text', 'mrsp', 'Published TP');
$field_mrspGRP = &$form->addGroup($mrspGRP, null, 'Published TP', '&nbsp;');
// -- Negotiated TP
$pricGRP = [];
$pricGRP[] =& $form->createElement('select', 'ipric_cur', 'Currency', $currencyList);
$pricGRP[] =& $form->createElement('text', 'pric', 'Negotiated TP');
$field_pricGRP = &$form->addGroup($pricGRP, null, 'Negotiated TP', '&nbsp;');
// -- Published Sales Price => DISABLED see Task#547778
// -- Actual Sales Price
$prisGRP = [];
$prisGRP[] =& $form->createElement('select', 'ipris_cur', 'Currency', $currencyList);
$prisGRP[] =& $form->createElement('text', 'pris', 'Actual Sales Price');
$field_prisGRP = &$form->addGroup($prisGRP, null, 'Actual Sales Price', '&nbsp;');

// Required
$form->addRule('caty', 'Required', 'required');
$form->addGroupRule(
    $field_mrspGRP->getName(),
    [
        'imrsp_cur' => [['Required', 'required']],
        'mrsp' => [['Required', 'required']],
    ]
);
$form->addGroupRule(
    $field_pripGRP->getName(),
    [
        'prip_cur' => [['Required', 'required']],
        'prip' => [['Required', 'required']],
    ]
);

$form->addElement('submit', 'btnSubmit', 'Submit');
