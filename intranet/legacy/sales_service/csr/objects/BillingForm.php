<?php

class BillingForm
{
    public static function build(array $csrHeader)
    {
        // Form
        $form = new HTML_QuickForm('frmNew', 'post');
        $form->addElement('hidden', 'm[0]', 'csr');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'edit');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', "Edit CSR#$id");
        $form->addElement('text', 'erp_inv', 'ERP Invoice#');
        $form->addElement('select', 'bill_to', 'Bill to', array("" => "") + tldCSR::getBillToList());
        $form->addElement('textarea', "bill_instruction", 'Billing instruction (if any)', array("wrap" => "VIRTUAL", "cols" => "60", "rows" => "5"));
        $form->addElement('submit', 'btnSubmit', 'Submit');
        // Rules
        $form->setDefaults($csrHeader);

        return $form;
    }
}