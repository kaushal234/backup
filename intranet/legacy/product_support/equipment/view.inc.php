<?php

use ApiBundle\Client;
use Symfony\Component\HttpClient\Exception\ClientException;
use ApiBundle\Http\FileStreamedResponseFactory;
use AppBundle\Manager\AuditLogManager;

include_once 'common.inc.php';
include_once 'publications.inc.php';
include_once 'eng.inc.php';

if (empty($id) || !is_numeric($id)) {
    $DEFAULT_ERROR[] = 'ERROR: ER id sent empty or not valid';
    return;
}
$eq = new tldEquipment($id);
if ($eq->isEmpty()) {
    $DEFAULT_ERROR[] = "ERROR: ER#$id not found";
    return;
}

$erp = $ERP = tldLocation::getERPByLocation($eq->itsDetails['man_location']);
if (empty($erp)) {
    $DEFAULT_ERROR[] = 'ERROR: Could not get correct company number...';
}

$date = $eq->itsDetails['dgt_act'] === '0000-00-00' ? date('Y-m-d') : $eq->itsDetails['dgt_act'];

$header = $eq->itsDetails;
$body = '';
//check if current id is in the sess.equipment.list array i.e. need to destroy memory of list
if (isset($single) && $single) {
    unset($sess['equipment']['list']);
}

$DEFAULT_TITLE .= "\SN:" . $eq->getSN();
$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=equipment&m[1]=view&id=$id" title="General Information">General</a>
&nbsp;|&nbsp;
EOF;


$DEFAULT_MENU .= <<<EOF
<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=serials&id=$id" title="Serial Numbers">SN</a>
EOF;

try {
    /** @var Client $client */
    $client = $kernel->getContainer()->get(Client::class);
} catch (Exception $e) {
    $DEFAULT_ERROR[] = "ERROR: Could not get client. Reason: ".$e->getMessage();
}

try {
    $apiEquipmentRecord = $client->findOneBy('/equipment_records', ['legacyId' => $id]);
} catch (Exception $e) {
    $apiEquipmentRecord = null;
}

$router = $kernel->getContainer()->get('router');
$crabRoute = null !== $apiEquipmentRecord ? $router->generate('crab_home', ['filter_crab[equipmentRecord][value]' => $apiEquipmentRecord['@id']]) : "$php_self?m[0]=equipment&m[1]=view&m[2]=crabs&id=$id";
$odpRoute = null !== $apiEquipmentRecord ? $router->generate('on_time_delivery_planning_show', ['serialNumber' => $apiEquipmentRecord['serialNumber']]) : "$php_self?m[0]=odp&m[1]=listing&m[2]=bySN&x={$eq->getSN()}";

$DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=combination&id=$id">ER Combination</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=cust_details&id=$id" title="Customer Information">Customer</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=sb&id=$id">SB</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=warranty&id=$id" title="Warranty Claims">WC</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=csr&id=$id">CSR</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=toc&id=$id" title="TLD on Call Records">TOC</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=hourmeter&id=$id">Hour meter</a>
&nbsp;|&nbsp;<a href="$crabRoute" title="CRABs">CRAB</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=software&id=$id" title="Software Downloads">Software</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=publications&id=$id" title="Publications">Pubs</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=schematics&id=$id" title="Schematic Diagrams">Schem</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=options&id=$id" title="Customer Selected Options">Opts</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=tasks&id=$id" title="Related tasks">Tasks</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=files&id=$id" title="File Attachments">Files</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=log&id=$id" title="Log">Log</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=link&id=$id" title="Link">Link</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=edit&id=$id">Edit</a>
&nbsp;|&nbsp;<a href="$odpRoute">ODP</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=state&id=$id" title="Change ER#$id State">Change State</a>
&nbsp;|&nbsp;<a href="/en/private/product_support/index.ps.php?m[0]=pdc&m[1]=listing&m[2]=search&model={$eq->getModel()}">PDC</a>
EOF;
if ($user->isInGroupLevel(['role_PSM', 'role_PSE', 'role_PSA', 'role_QAM', 'role_COO', 'role_QE'], $eq->getFactoryERP())) {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=resetYT&id=$id">Reset YT</a>
EOF;
}
if ($user->isInGroupLevel(['role_QAM'], $eq->getFactoryERP()) || $user->isInGroup(['gg_ADMIN', 'role_CMO', 'role_COO'])) {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=updateShipAndGTDates&id=$id">Update GT/Shipping Dates</a>
EOF;
}
$DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=upgrade&id=$id">ER Upgrade</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=tldlink&id=$id">LINK (IoT)</a>
EOF;

if ($user->isInGroupLevel(['role_PSM', 'role_PSE', 'role_PSA'], $eq->getFactoryERP()) || $user->isInGroup(['gg_ADMIN'])) {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=duplicate&id=$id">Duplicate</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=delete&id=$id" title="Delete ER#$id">Delete</a>
EOF;
}
if ($user->isInGroup(['gg_MIS'])) {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="/en/private/product_support/equipment/equipment_admin.php?mode=record_view&form_type=main_tpl&id=$id">Admin</a>
EOF;
}

// Get CBOM link if applicable
if (null !== $erp && !empty($eq->getProject())) {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="/en/private/manufacturing/eng/dev.php?m[0]=cbom&m[1]=view&erp=$ERP&sn=${header['t_prno']}&date=$date"
title="CBOM for this unit">CBOM</a>
EOF;
}

// SOL link if applicable
$sor_lid = $header['sor_lid'];
if ($sor_lid) {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="/en/private/sales_service/sales.php?m[0]=sol&m[1]=view&&m[2]=er&id=$sor_lid"
     title="Go to SOR Line $sor_lid">SOR Line#$sor_lid</a>
EOF;
}

if (!empty($eq->getESRID())) {
    $DEFAULT_MENU .= <<<EOF
<br>&nbsp;|&nbsp;<a href="/en/private/sales_service/sales.php?m[0]=esr&m[1]=view&id={$eq->getESRID()}">ESR active</a>
EOF;
    $tmp_esr = new tldESR($eq->getESRID());
    if ($tmp_esr->itsHeader['ship_auth'] == '0') {
        $body .= '<p style=" font-size: large; color: crimson; "> WARNING : Shipment Authorization is : NO <br />(Please see ESR)</p>';
    }
}

//ESRL Link if applicable
if ($apiEquipmentRecord !== null) {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="/en/private/sales/equipment_shipping_records?serialNumber={$eq->getSN()}" title="Related ESR(s)">Show related ESR(s)</a>
EOF;
    $apiEquipmentRecordId = $apiEquipmentRecord['id'];
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="/en/private/sales/equipment_shipping_records/add/from-equipment-record/$apiEquipmentRecordId" title="Create ESR with ESRL">Create New ESR with ER</a>
EOF;
}

//LINK Synchronization
if (null !== $apiEquipmentRecord) {
    $apiEquipmentRecordId = $apiEquipmentRecord['id'];
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=synchronize&id=$id" title="Synchronize with Link">Synchronize with LINK</a>
EOF;
}

if (count(tldESRL::byERID($id)) > 1) {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="/en/private/sales_service/sales.php?m[0]=esr&m[1]=listing&m[2]=old&er={$eq->getID()}">List all ESR</a>
EOF;
}

$e = tldEquipment::byUnshippedBySN('T' . $id);
if (!empty($e)) {
    $route = $kernel->getContainer()->get('router')->generate('crab_add', ['equipmentRecord' => $id]);
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$route" title="Submit CRAB">Submit CRAB</a>
EOF;
}

switch ($m[2] ?? null) {
    case 'state':
        $DEFAULT_TITLE .= "\ER State";
        // look for COMBINED
        $combinedList = [];
        if ($eq->isInCombinationMode()) {
            $combinedList = $eq->getCombinationErList();
        }
        // Form
        $form = new HTML_QuickForm('frmDelete');
        $form->addElement('hidden', 'm[0]', 'equipment');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'state');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', "Choose ER#$id State");
        $form->addElement('select', 'state', 'State', tldEquipment::getStateList());
        $form->addElement('textarea', 'comment', 'Comment', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '7']);
        if (count($combinedList)) {
            $form->addElement('header', 'title', "Combined for ER#$id - Update state also?");
            foreach ($combinedList as $er) {
                $form->addElement('checkbox', "combined[{$er['id']}]", $er['sn'], 'Actual state is ' . $er['state']);
            }
        }
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->setDefaults(['state' => $eq->getState()]);

        if (!$form->validate()) {
            $body .= $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        // Update State
        $e = $eq->setState($vars['state'], $user, $vars['comment']);
        if (is_string($e)) {
            $DEFAULT_ERROR[] = "ERROR: Problem updating state... Reason: $e";
            break;
        }
        $body .= "<br>ER state updated successfully to {$vars['state']}";
        if (!empty($vars['combined'])) {
            foreach ($vars['combined'] as $id => $val) {
                $er = new tldEquipment((int)$id);
                $e = $er->setState($vars['state'], $user, $vars['comment']);
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "ERROR: Problem updating state for {$er->getSN()}. Reason: $e";
                    continue;
                }
                $body .= "<br>Combined {$er->getSN()} state updated successfully to {$vars['state']}";
            }
        }
        break;
    case 'delete':
        $DEFAULT_TITLE .= "\ER Delete";
        if (!$user->isInGroupLevel(['role_PSM', 'role_PSE', 'role_PSA'], $eq->getFactoryERP()) && !$user->isInGroup(['gg_ADMIN'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permission to delete Equipment Records.';
            break;
        }
        $form = new HTML_QuickForm('frmDelete');
        $form->addElement('hidden', 'm[0]', 'equipment');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'delete');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', "Do you want to delete ER#$id ? This is irreversible.");
        $form->addElement('submit', 'btnSubmit', 'Confirm');
        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }

        $e = $eq->delete();
        if (is_string($e)) {
            $DEFAULT_ERROR[] = "ERROR: Problem deleting...<br/>Reason: $e";
            break;
        }
        $log = $eq->addLogEntry(
            $user->getID(),
            "ER#$id deleted"
        );
        if (is_string($log)) {
            $DEFAULT_ERROR[] = "ERROR: Problem adding logs...<br/>Reason: $log";
        }
        $body .= "ER#$id deleted successfully!";
        break;
    case 'duplicate':
        $DEFAULT_TITLE .= "\ER Duplication";
        if (!$user->isInGroupLevel(['role_PSM', 'role_PSE', 'role_PSA'], $eq->getFactoryERP()) && !$user->isInGroup(['gg_ADMIN'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permission to delete Equipment Records.';
            break;
        }
        $form = new HTML_QuickForm('frmDuplicate');
        $form->addElement('hidden', 'm[0]', 'equipment');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'duplicate');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', "Do you want to duplicate ER#$id ?");
        $form->addElement('text', 'number', 'Number of duplicates');
        $form->addRule('number', 'Required', 'required');
        $form->addRule('number', 'Should be numeric', 'numeric');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->setDefaults(['number' => 1]);
        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }
        $vars = tldUtils::cleanupFormInput($form->exportValues());

        $userEmail = $user->getEmail();
        for ($i = 0; $i < (int)$vars['number']; $i++) {
            if (is_string($e = $eq->duplicate($userEmail))) {
                $DEFAULT_ERROR[] = "ERROR: Problem duplicated ER#{$er->itsId}...<br/>Reason: $e";
                continue;
            }

            $DEFAULT_SUCCESS[] = "SUCCESS: <a href=\"$php_self?m[0]=equipment&m[1]=view&id=$e\">ER#$e</a> as been created from ER#{$er->itsId} duplication.";
        }
        break;
    case 'editPAS':
        $DEFAULT_TITLE .= "\PAS Edit";
        if (!$eq->isPAS()) {
            $DEFAULT_ERROR[] = 'ERROR: ER can not be updated here';
            break;
        }
        // Acces check
        $factoryRoles = ['gg_ENG', 'gg_SUPPORT', 'gg_QUALITY', 'gg_SERVICE', 'role_PSM', 'role_PSE', 'role_PSA'];
        $ssoRoles = ['gg_SUPPORT', 'gg_SERVICE', 'gg_PARTS', 'role_ASM', 'role_EVP'];
        $otherRoles = ['gg_ADMIN', 'acl_equipment_admin', 'sales_cust_admin', 'role_CSD', 'role_CMO'];
        if (!$user->isInGroup($otherRoles) && !$user->isInGroupLevel($factoryRoles, $eq->getFactoryERP()) && !$user->isInGroupLevel($ssoRoles, $eq->getSSOERP())) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permission to edit Equipment Records.';
            break;
        }
        // Listing
        $customerRawList = tldCustomer::byType('TLD Manufacturing & Industrial Partners');
        $customerList = ['' => ''] + tldUtils::optionsByKeyValue($customerRawList, 'id', 'customer_name');
        if (empty($customerList)) {
            $DEFAULT_ERROR[] = "WARNING: PAS edition issue. Reason: No customer found with the type 'TLD Manufacturing & Industrial Partners'";
        }
        $_engTierTypes = ['' => ''] + tldList::optionsByListNameAsListItemListItem('list.engine.tiers');
        if (!empty($header['eng_tier']) && !in_array($header['eng_tier'], $_engTierTypes, true)) {
            $_engTierTypes[$header['eng_tier']] = $header['eng_tier'];
        }
        $factoryList = ['' => ''] + tldEquipment::getUsedFactoryList();
        $typeList = ['' => ''] + tldType::getList('smartyOptions_Name');

        // Form
        $form = new HTML_QuickForm('frmAdd');
        $form->addElement('hidden', 'm[0]', 'equipment');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'editPAS');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', "Edit PAS#$id");
        // General info
        $form->addElement('header', 'title', 'General');
        $form->addElement('text', 'esrid', 'ESR ID#');
        $form->addElement('textarea', 'odp_note', 'ODP Comment', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '7']);
        $form->addElement('header', 'title', 'Customer Details');
        $form->addElement('select', 'buyer_customer_id', 'Customer Name (BUYER)', $customerList);
        $form->addElement('text', 'customer_contact', 'Customer Contact');
        $form->addElement('text', 'customer_ref', 'Customer Ref/PO Number');
        $form->addElement('text', 'date_arrived', 'Arrival Date (YYYY-MM-DD)');
        // ER details
        $form->addElement('header', 'title', 'Equipment Details');
        $form->addElement('select', 'type', 'Equipment Type', $typeList);

        $form->addElement('textarea', 'options_desc', 'Description of Options', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '7']);
        $form->addElement('select', 'man_location', 'Manufacturer Location', $factoryList);
        $form->addElement('select', 'eng_tier', 'Emission Rating', $_engTierTypes);
        $form->addElement('text', 'diml', 'Length (mm)');
        $form->addElement('text', 'dimw', 'Width (mm)');
        $form->addElement('text', 'dimh', 'Height (mm)');
        $form->addElement('text', 'dimk', 'Weight (KG)');
        // Manufacturing
        $form->addElement('header', 'title', 'Manufacturing Section');
        if ($user->isInGroupLevel('gg_SUPPORT', $eq->getFactoryERP())) {
            $form->addElement('textarea', 'mfg_comments', 'MFG Comments', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '7']);
            $form->addElement('text', 't_pdno', 'Main Work Order#');
            $form->addElement('text', 't_prno', 'MFG Project Number');
            $form->addElement('text', 'sls_orno', 'MFG SO#');
        } else {
            $form->addElement('textarea', 'mfg_comments', 'MFG Comments', ['disabled' => 'disabled', 'wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '7']);
            $form->addElement('text', 't_pdno', 'Main Work Order#', ['disabled' => 'disabled']);
            $form->addElement('text', 't_prno', 'MFG Project Number', ['disabled' => 'disabled']);
            $form->addElement('text', 'sls_orno', 'MFG SO#', ['disabled' => 'disabled']);
        }
        $form->addElement('submit', 'btnSubmit', 'Submit');
        // Rules & defaults
        $fields = ['man_location', 'buyer_customer_id'];
        foreach ($fields as $field) {
            $form->addRule($field, 'Required', 'required');
        }
        $form->setDefaults($header);

        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }

        $rawVars = $form->exportValues();
        $rawVars['customer_id'] = $rawVars['buyer_customer_id'];
        $rawVars['customer_name'] = $customerList[$rawVars['buyer_customer_id']];
        $vars = tldUtils::cleanupFormInput($rawVars);
        // Fields
        $FIELD_DESIGNATION = [
            'esrid' => 'ESR ID#',
            'odp_note' => 'ODP Comment',
            'sales_org' => 'Sales Organisation',
            'sales_rep' => 'Sales Rep',
            'buyer_customer_id' => 'Customer Name (BUYER)',
            'customer_id' => 'Customer Name (END USER)',
            'customer_name' => 'Customer Name',
            'customer_contact' => 'Customer Contact',
            'customer_ref' => 'Customer Ref/PO Number',
            'date_arrived' => 'Arrival Date',
            'type' => 'Equipment Type',
            'options_desc' => 'Description of Options',
            'man_location' => 'Manufacturer Location',
            'eng_tier' => 'Emission Rating',
            'diml' => 'Length',
            'dimw' => 'Width',
            'dimh' => 'Height',
            'dimk' => 'Weight',
            'mfg_comments' => 'MFG Comments',
            't_prno' => 'MFG Project Number',
            't_pdno' => 'Main Work Order#',
            'sls_orno' => 'MFG SO#',
        ];
        // Create ER
        $e = $eq->update($vars, array_keys($FIELD_DESIGNATION));
        if (is_string($e)) {
            $DEFAULT_ERROR[] = "ERROR: Problem updating ER...<br/>Reason: $e";
            break;
        }
        // check logs
        $updates = [];
        foreach ($FIELD_DESIGNATION as $field => $label) {
            if ($header[$field] == $vars[$field]) {
                continue;
            }
            switch ($field) {
                case 'buyer_customer_id':
                case 'customer_id':
                    $updates[] = "<li>$label from '{$customerList[$header[$field]]}' to '{$customerList[$vars[$field]]}'</li>";
                    break;
                default:
                    $updates[] = "<li>$label from '{$header[$field]}' to '{$vars[$field]}'</li>";
                    break;
            }
        }
        if (!empty($updates)) {
            $log = 'Updated:<ul>' . implode('', $updates) . '</ul>';
            $e = $eq->addLogEntry($user->getID(), TldDatabase::escape($log));
            if (is_string($e)) {
                $DEFAULT_ERROR[] = "ERROR: Problem adding logs...<br/>Reason: $log";
            }
        }
        $body .= "PAS#$id updated successfully!";
        break;
    case 'edit':
        $DEFAULT_TITLE .= "\ER Edit";
        // If PAS, redirect to correct EDIT
        if ($eq->isPAS()) {
            header("location: $php_self?m[0]=equipment&m[1]=view&m[2]=editPAS&id=$id");
            exit;
        }
        // Access check
        $factoryRoles = ['gg_ENG', 'gg_SUPPORT', 'gg_QUALITY', 'gg_SERVICE', 'role_PSM', 'role_PSE', 'role_PSA'];
        $ssoRoles = ['gg_SUPPORT', 'gg_SERVICE', 'gg_PARTS', 'role_ASM', 'role_EVP', 'role_CSM'];
        $otherRoles = ['gg_ADMIN', 'acl_equipment_admin', 'sales_cust_admin', 'role_CSD', 'role_CMO'];
        if (!$user->isInGroup($otherRoles) && !$user->isInGroupLevel($factoryRoles, $eq->getFactoryERP()) && !$user->isInGroupLevel($ssoRoles, $eq->getSSOERP())) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permission to edit Equipment Records.';
            break;
        }
        // Listing
        $_factoryList = ['' => ''] + tldEquipment::getUsedFactoryList();
        $_ctryList = ['To Be Defined' => 'To Be Defined'] + tldCountry::optionsAsNameName();
        $_modelList = ['' => ''] + tldModel::getList();
        $_erTypeList = ['' => ''] + tldType::getList('smartyOptions_Name');
        $_engTierTypes = ['' => ''] + tldList::optionsByListNameAsListItemListItem('list.engine.tiers');
        if (!empty($header['eng_tier']) && !in_array($header['eng_tier'], $_engTierTypes, true)) {
            $_engTierTypes[$header['eng_tier']] = $header['eng_tier'];
        }
        $_salesOrg = ['' => ''] + tldLocation::getSalesOrgList('smartyOptionsLocationLocation');
        $_salesRep = ['' => ''] + tldGroup::getUserListByMultipleGroup(['gg_SALES'], null, ['smartyOptionsTech_name' => true]);
        // Form
        $form = new HTML_QuickForm('frmEdit');
        $form->addElement('hidden', 'm[0]', 'equipment');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'edit');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', "Edit ER#$id");
        $form->addElement('header', 'title', '<b>General</b>');
        $form->addElement('text', 'sn', 'ER SN#', ['disabled' => 'disabled']);
        $form->addElement('text', 'cust_asset_num', 'Customer Asset#');
        $form->addElement('text', 'esrid', 'ESR ID#');
        $form->addElement('text', 'date_entered', 'Date Entered (YYYY-MM-DD)', ['disabled' => 'disabled']);
        $form->addElement('textarea', 'odp_note', 'ODP Comment', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '7']);
        $form->addElement('select', 'sales_org', 'Sales Organisation', $_salesOrg);
        $form->addElement('select', 'sso_service', 'Service SSO', $_salesOrg);
        $form->addElement('select', 'sales_rep', 'Sales Rep', $_salesRep);
        $form->addElement('text', 'cust_equipment_type', 'Customer Equipment Type');
        $form->addElement('header', 'title', '<b>Customer Details</b>');
        $form->addElement('select', 'buyer_customer_id', 'Customer Name (BUYER)', [$header['buyer_customer_id'] => $header['buyer_customer_display'], '' => ''], ['class' => 'select2-customer', 'style' => 'width: 100%']);
        $form->addElement('select', 'customer_id', 'Customer Name (END USER)', [$header['customer_id'] => $header['user_customer_display'], '' => ''], ['class' => 'select2-customer', 'style' => 'width: 100%']);
        $form->addElement('select', 'maintainer_customer_id', 'Customer Name (MAINTAINER)', [$header['maintainer_customer_id'] => $header['maintainer_customer_display'], '' => ''], ['class' => 'select2-customer', 'style' => 'width: 100%']);
        $form->addElement('select', 'customer_name', 'Customer Name', [$header['customer_name'] => $header['customer_name'], '' => ''], ['class' => 'select2-customer-name', 'style' => 'width: 100%']);
        $form->addElement('text', 'customer_name_prev', 'Customer Name Previous');
        $form->addElement('text', 'customer_contact', 'Customer Contact');
        $form->addElement('text', 'customer_ref', 'Customer Ref/PO Number');
        $form->addElement('text', 'agent_name', 'Agent Name');
        $form->addElement('select', 'airport_code', 'Airport Code', [$header['airport_code'] => $header['apc_fullname'], '' => ''], ['class' => 'select2-airports airports-auto-update-country', 'style' => 'width: 100%']);
        $form->addElement('text', 'location_short', 'Location (Short)');
        $form->addElement('textarea', 'delivery_location', 'Delivery Location<br>(3 letter Airportcode, address)', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '7']);
        $form->addElement('select', 'del_ctry', 'Delivered Country', $_ctryList);
        $form->addElement('text', 'date_arrived', 'Arrival Date (YYYY-MM-DD)');
        $form->addElement('header', 'title', '<b>Equipment Details</b>');
        $form->addElement('select', 'type', 'Equipment Type', $_erTypeList);
        $form->addElement('select', 'model', 'Equipment Model', $_modelList);
        $form->addElement('text', 'er_batch_qty', 'Batch Qty');
        $form->addElement('textarea', 'options_desc', 'Description of Options', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '7']);
        $form->addElement('select', 'man_location', 'Manufacturer Location', $_factoryList);
        $form->addElement('select', 'eng_tier', 'Emission Rating', $_engTierTypes);
        $form->addElement('text', 'diml', 'Length (mm)');
        $form->addElement('text', 'dimw', 'Width (mm)');
        $form->addElement('text', 'dimh', 'Height (mm)');
        $form->addElement('text', 'dimk', 'Weight (KG)');
        $form->addElement('header', 'title', '<b>Warranty Details</b>');
        if ($user->isInGroup(['role_PSM', 'role_PSE', 'role_PSA', 'gg_ADMIN'])) {
            $form->addElement('select', 'warranty_length', 'Warranty Length (Months)', tldEquipment::getAllowedWarrantyLength());
            $form->addElement('text', 'warranty_length_hours', 'Warranty Length (Hours)');
            $form->addElement('text', 'date_warranty_end', 'Warranty End Date (YYYY-MM-DD)');
            $form->addElement('textarea', 'warranty_conditions', 'Special Warranty Conditions', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '7']);
        } else {
            $form->addElement('text', 'warranty_length', 'Warranty Length (Months)', ['disabled' => 'disabled']);
            $form->addElement('text', 'warranty_length_hours', 'Warranty Length (Hours)', ['disabled' => 'disabled']);
            $form->addElement('text', 'date_warranty_end', 'Warranty End Date (YYYY-MM-DD)', ['disabled' => 'disabled']);
            $form->addElement('textarea', 'warranty_conditions', 'Special Warranty Conditions', ['disabled' => 'disabled', 'wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '7']);
        }
        $form->addElement('header', 'title', '<b>Manufacturing Section</b>');
        if ($user->isInGroupLevel('gg_SUPPORT', $eq->getFactoryERP()) || $eq->getBuyerCustomerID() == 3588) {
            $form->addElement('textarea', 'mfg_comments', 'MFG Comments', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '7']);
        } else {
            $form->addElement('textarea', 'mfg_comments', 'MFG Comments', ['disabled' => 'disabled', 'wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '7']);
        }
        if ($user->isInGroupLevel(['gg_SUPPORT', 'role_SAM'], $eq->getFactoryERP())) {
            $form->addElement('text', 't_pdno', 'Main Work Order#');
            $form->addElement('text', 't_prno', 'MFG Project Number');
            $form->addElement('text', 'sls_orno', 'MFG SO#');
            $form->addElement('text', 'dt_commissioned', 'Commissioning Date (YYYY-MM-DD)');
        } else {
            $form->addElement('text', 't_pdno', 'Main Work Order#', ['disabled' => 'disabled']);
            $form->addElement('text', 't_prno', 'MFG Project Number', ['disabled' => 'disabled']);
            $form->addElement('text', 'sls_orno', 'MFG SO#', ['disabled' => 'disabled']);
            $form->addElement('text', 'dt_commissioned', 'Commissioning Date (YYYY-MM-DD)', ['disabled' => 'disabled']);
        }
        if ($user->isInGroup(['gg_ACCT', 'role_SA', 'gg_ADMIN'])) {
            $form->addElement('text', 'tranid_sso', 'SSO Transaction#');
            $form->addElement('text', 'tranid_erp', 'ERP Transaction#');
            $form->addElement('text', 'rrd_sso', 'Revenue Recognition Date, SSO (YYYY-MM-DD)');
            $form->addElement('text', 'rrd_erp', 'Revenue Recognition Date, Factory (YYYY-MM-DD)');
        } else {
            $form->addElement('text', 'tranid_sso', 'SSO Transaction#', ['disabled' => 'disabled']);
            $form->addElement('text', 'tranid_erp', 'ERP Transaction#', ['disabled' => 'disabled']);
            $form->addElement('text', 'rrd_sso', 'Revenue Recognition Date, SSO (YYYY-MM-DD)', ['disabled' => 'disabled']);
            $form->addElement('text', 'rrd_erp', 'Revenue Recognition Date, Factory (YYYY-MM-DD)', ['disabled' => 'disabled']);
        }
        if ($user->isInGroup(['gg_ADMIN', 'role_CMO'])) {
            $form->addElement('text', 'date_shipped', 'Actual Shipped Date (YYYY-MM-DD)');
            $form->addElement('text', 'dgt_rev', 'Estimated GT Date (YYYY-MM-DD)');
            $form->addElement('text', 'dgt_act', 'Actual GT Date (YYYY-MM-DD)');
            $form->addElement('text', 'dgt_com', 'First GT Date (YYYY-MM-DD)');
            $form->addElement('text', 'dyt', 'Yellow Tag Date (YYYY-MM-DD)');
        } else {
            $form->addElement('text', 'date_shipped', 'Actual Shipped Date (YYYY-MM-DD)', ['disabled' => 'disabled']);
            $form->addElement('text', 'dgt_rev', 'Estimated GT Date (YYYY-MM-DD)', ['disabled' => 'disabled']);
            $form->addElement('text', 'dgt_act', 'Actual GT Date (YYYY-MM-DD)', ['disabled' => 'disabled']);
            $form->addElement('text', 'dgt_com', 'First GT Date (YYYY-MM-DD)', ['disabled' => 'disabled']);
            $form->addElement('text', 'dyt', 'Yellow Tag Date (YYYY-MM-DD)', ['disabled' => 'disabled']);
        }
        $form->addElement('header', 'title', '<b>Service Section</b>');
        if ($user->isInGroup(['gg_ADMIN', 'role_CSM', 'role_PSA', 'role_PSM', 'role_PSE'])) {
            $form->addElement('text', 'maintenance_contract_erp', 'Service Contract ERP');
            $form->addElement('select', 'maintenance_contract_ref', 'FMS contract', ['' => ''] + tldEquipment::getFmsContractType());
        } else {
            $form->addElement('text', 'maintenance_contract_erp', 'Service Contract ERP', ['disabled' => 'disabled']);
            $form->addElement('text', 'maintenance_contract_ref', 'FMS contract', ['disabled' => 'disabled']);
        }
        $form->addElement('text', 'eam_id', 'EAM Identifier');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $fields = ['sales_org', 'man_location', 'buyer_customer_id', 'del_ctry', 'type', 'model'];
        foreach ($fields as $field) {
            $form->addRule($field, 'Required', 'required');
        }

        $combined = false;
        if ($eq->itsDetails['comb_mod'] === 'ER COMBINED') {
            $combined = true;
        }
        $serialNumber = $eq->itsDetails['sn'];
        $model = $eq->itsDetails['model'];

        $form->setDefaults($header);
        if (!$form->validate()) {
            $js = <<<HTML
<script type="application/javascript">
	document.addEventListener("DOMContentLoaded", function () {
        let combined = $combined
        let serialNumber = '$serialNumber'
        let model = '$model'
        let countrySelect = $('select[name="del_ctry"]');
	    let countryDisplay = $('<span></span>').insertAfter(countrySelect); // Add an element to display the country name instead of countrySelect
	    let errorMessage = $('<div style="color: red; font-size: 0.9em; margin-top: 5px; display: none;"></div>').insertAfter(countrySelect); // Error message added under select
    
	    $('.airports-auto-update-country').on('change', function () {
	    	const selectedData = $(this).select2('data');

	    	if (selectedData.length > 0 && selectedData[0]) {
	    		let airportSelected = !!selectedData[0].id;//  null/undefined/empty

	    		if (airportSelected) {
	    			let countryName = selectedData[0].country_name || 'undefined_country_name';
	    			let countryOptionExists = countrySelect.find('option').filter(function() {
	    				return $(this).text().trim() === countryName || $(this).val() === countryName;
	    			}).length > 0;

	    			if (countryOptionExists) {
	    				countryDisplay.text(countryName).show();
	    				countrySelect.val(countryName).trigger('change'); 
	    				countrySelect.hide();
	    				errorMessage.hide();
	    			} else {
	    				errorMessage.text('Country not found. Please select it manually.').show();
	    				countryDisplay.hide();
	    				countrySelect.show().val('');
	    			}
	    		} else {
	    			countryDisplay.hide();
	    			countrySelect.show().val('');
	    			errorMessage.hide();
	    		}

                if (combined) {
                    alert('This ER is combined with ' + serialNumber + ' / model ' + model +' the APC change will also apply to the combined unit, please confirm');
                }
	    	}
	    });
	});
</script>

HTML;
            $smarty->assign('html_head', $js);
            $body = $form->toHTML();
            break;
        }

        $vars = $form->exportValues(); // Cleanup done before update in function itself!

        // Manually set value after submit because HTML Quickform is checking if value exist in the select.
        // And with select2, the value is not exist because it is now an autocomplete field.
        foreach (['buyer_customer_id', 'customer_id', 'maintainer_customer_id', 'customer_name', 'airport_code'] as $customSelectField) {
            if (!isset($vars[$customSelectField]) && isset($_POST[$customSelectField])) {
                $vars[$customSelectField] = TldDatabase::escape($_POST[$customSelectField]);
            }
        }

        if ($combined) {
            $erCombined = new tldEquipment($eq->itsDetails['parent_id']);
            $erCombined->setAPC($vars['airport_code']);
        }

        if (!is_numeric($vars['er_batch_qty']) || $vars['er_batch_qty'] < 1) {
            $DEFAULT_ERROR[] = 'ERROR: Batch quantity must be at least 1, please try again.';
            $body = $form->toHTML();
            break;
        }
        //check if cprj and pdno linked
        $pdno = trim($vars['t_pdno']);
        $cprj = trim($vars['t_prno']);

        if ($pdno !== '' && $cprj !== '' && ($pdno !== $eq->getWorkOrder() || $cprj !== $eq->getProject())) {
            $container = $kernel->getContainer();
            $client = $container->get(Client::class);
            try {
                $project = $client->get(
                    sprintf('/ion/projects/site=%d;project=%s', $erp, $cprj),
                );
            } catch (ClientException $exception) {
                $DEFAULT_ERROR[] = 'ERROR: PROJECT number not valid';
                $body = $form->toHTML();
                break;
            }

            if (!in_array($pdno, array_column($project['productionOrders'], 'productionOrderIdentifier'))) {
                $DEFAULT_ERROR[] = 'ERROR: WORK ORDER not valid';
                $body = $form->toHTML();
                break;

            }
            //check if it's a PIO unit
            if ($resIsPIO = tldUtils::getSqlRowToAssocArray("SELECT * FROM pi_unit_family WHERE unit = '{$vars['sn']}'")) {
                //then change the work order number in the question unit database
                $res = tldUtils::sqlInsert("UPDATE pi_questions_unit SET t_pdno ='$pdno' ,t_cprj ='$cprj'  WHERE unit='{$vars['sn']}'");
            }
        }

        $fields = [
            'sn',
            'cust_asset_num',
            'esrid',
            'date_entered',
            'odp_note',
            'sales_org',
            'sso_service',
            'sales_rep',
            'cust_equipment_type',
            'buyer_customer_id',
            'customer_id',
            'maintainer_customer_id',
            'customer_name',
            'customer_name_prev',
            'customer_contact',
            'customer_ref',
            'agent_name',
            'airport_code',
            'location_short',
            'delivery_location',
            'del_ctry',
            'date_arrived',
            'type',
            'model',
            'er_batch_qty',
            'options_desc',
            'man_location',
            'eng_tier',
            'diml',
            'dimw',
            'dimh',
            'dimk',
            'warranty_length',
            'date_warranty_end',
            'warranty_conditions',
            'mfg_comments',
            't_prno',
            't_pdno',
            'sls_orno',
            'dt_commissioned',
            'maintenance_contract_erp',
            'maintenance_contract_ref',
            'tranid_sso', 'tranid_erp',
            'rrd_sso', 'rrd_erp',
            'date_shipped',
            'dgt_rev',
            'dgt_act',
            'dgt_com',
            'dyt',
            'eam_id',
        ];

        if ($vars['warranty_length_hours'] ?? null) {
            // Non mandatory field depending on the roles of the user, the field will always be there for new units
            $fields[] = 'warranty_length_hours';
        }
        // Update ER
        $newDateShipped = $_POST['date_shipped'];
        $oldDateShipped = $eq->itsDetails['date_shipped'];
        $e = $eq->updateRecord($vars, $fields);
        if (is_string($e)) {
            $DEFAULT_ERROR[] = "ERROR: Problem updating...<br/>Reason: $e";
            break;
        }
        // Logging changes
        $FIELD_DESIGNATION = [
            'sn' => 'ER SN#',
            'cust_asset_num' => 'Customer Asset#',
            'esrid' => 'ESR ID#',
            'date_entered' => 'Date Entered',
            'odp_note' => 'ODP Comment',
            'sales_org' => 'Sales Organisation',
            'sales_rep' => 'Sales Rep',
            'cust_equipment_type' => 'Customer Equipment Type',
            'buyer_customer_id' => 'Customer Name (BUYER)',
            'customer_id' => 'Customer Name (END USER)',
            'maintainer_customer_id' => 'Customer Name (MAINTAINER)',
            'customer_name' => 'Customer Name',
            'customer_name_prev' => 'Customer Name Previous',
            'customer_contact' => 'Customer Contact',
            'customer_ref' => 'Customer Ref/PO Number',
            'agent_name' => 'Agent Name',
            'airport_code' => 'Airport Code',
            'location_short' => 'Location (Short)',
            'delivery_location' => 'Delivery Location',
            'del_ctry' => 'Delivered Country',
            'date_arrived' => 'Arrival Date',
            'type' => 'Equipment Type',
            'model' => 'Equipment Model',
            'er_batch_qty' => 'Batch Qty',
            'options_desc' => 'Description of Options',
            'man_location' => 'Manufacturer Location',
            'eng_tier' => 'Emission Rating',
            'diml' => 'Length',
            'dimw' => 'Width',
            'dimh' => 'Height',
            'dimk' => 'Weight',
            'warranty_length' => 'Warranty Length (Months)',
            'warranty_length_hours' => 'Warranty Length (Hours)',
            'date_warranty_end' => 'Warranty End Date',
            'warranty_conditions' => 'Special Warranty Conditions',
            'mfg_comments' => 'MFG Comments',
            't_prno' => 'MFG Project Number',
            't_pdno' => 'Main Work Order#',
            'sls_orno' => 'MFG SO#',
            'dt_commissioned' => 'Commissioning Date',
            'maintenance_contract_erp' => 'Service Contract ERP',
            'maintenance_contract_ref' => 'FMS contract',
            'tranid_sso' => 'SSO Transaction#',
            'tranid_erp' => 'ERP Transaction#',
            'rrd_sso' => 'Revenue Recognition Date SSO',
            'rrd_erp' => 'Revenue Recognition Date ERP',
            'date_shipped' => 'Actual Shipped Date',
            'dgt_rev' => 'Estimated GT Date',
            'dgt_act' => 'Actual GT Date',
            'dgt_com' => 'First GT Date',
            'dyt' => 'Yellow Tag Date',
            'eam_id' => 'EAM Identifier',
        ];
        //Get updated header
        $eq_updated = new tldEquipment($id);
        $header_updated = $eq_updated->getHeader();
        $logs = [];
        //Log if updated header <> original header
        $textareaFields = ['options_desc', 'odp_note', 'delivery_location', 'warranty_conditions', 'mfg_comments'];
        foreach ($fields as $field) {
            if ($header_updated[$field] != $header[$field]) {

                $oldValue = $header[$field];
                $newValue = $header_updated[$field];

                if (in_array($field, $textareaFields, true)) {
                    $oldValue = tldUtils::renderHtmlOrNl2br($oldValue);
                    $newValue = tldUtils::renderHtmlOrNl2br($newValue);
                }

                $logs[] = "<li><b>{$FIELD_DESIGNATION[$field]}</b> from '{$oldValue}' to '{$newValue}'</li>";
            }
        }

        if (count($logs)) {
            $msg = 'ER updated:<br><ul>' . implode('', $logs) . '</ul>';
            $e = $eq->addLogEntry(
                $user->getID(),
                TldDatabase::escape($msg)
            );
            if (is_string($e)) {
                $DEFAULT_ERROR[] = "ERROR: Problem adding logs...<br/>Reason: $e";
            }
            $body .= '<br>' . $msg;
        }
        $body .= "ER#$id edited successfully!";
        // ESR auto close check
        $eq->refresh();
        $esr_id = $eq->getESRID();
        if (!empty($esr_id)) {
            $esr = new tldESR($esr_id);
            $change = false;
            if (!$esr->isEmpty() && $esr->getStatus() !== 'CLOSED') {
                if ($esr->itsHeader['inco'] === 'EXW' || $esr->itsHeader['inco'] === 'FCA') {
                    $esrl = $esr->getLines();
                    $close = !empty($esrl);
                    foreach ($esrl as $key => $line) {
                        if ($line['er_dt_shipped'] === '0000-00-00') {
                            $close = false;
                            break;
                        }
                    }
                    if ($close) {
                        $container = $kernel->getContainer();
                        $client = $container->get(Client::class);
                        try {
                            $esrApi = $client->findOneBy('/sales/equipment_shipping_records', ['legacyId' => $esr_id]);
                        } catch (ClientException $exception) {
                            $DEFAULT_ERROR[] = 'ERROR: ESR number not valid';
                            $body = $form->toHTML();
                            break;
                        }

                        try {
                            $client->put(sprintf('/sales/equipment_shipping_records/%d/status', $esrApi->getIriId()), ['json' => ['status' => 'CLOSED']]);
                            $client->save('/comments', ['resource' => $esrApi->getIri(), 'message' => "This ESR has been automatically closed when " . $user->getFullname() . ' updated the shipping date of ER#' . $id]);
                        } catch (ClientException $exception) {
                            $DEFAULT_ERROR[] = sprintf("ERROR: this ER is linked to ESR#%s. Because ESR is not closed, your update in ER was not synchronized with ESR. Status update has failed because %s", $esrApi->getIriId(), $exception->getMessage());
                            $body = $form->toHTML();
                            break;
                        }
                        $change = true;
                    }
                }
                if ($change) {
                    $esr->refresh();
                }
                if (!in_array($esr->getStatus(), ['CLOSED', 'SHIPPED'], true)) {
                    $esrl = $esr->getLines();
                    $close = !empty($esrl);
                    foreach ($esrl as $key => $line) {
                        if ($line['er_dt_shipped'] === '0000-00-00') {
                            $close = false;
                            break;
                        }
                    }
                    if ($close) {
                        try {
                            $esrApi = $client->findOneBy('/sales/equipment_shipping_records', ['legacyId' => $esr_id]);
                        } catch (ClientException $exception) {
                            $DEFAULT_ERROR[] = 'ERROR: ESR number not valid';
                            $body = $form->toHTML();
                            break;
                        }

                        try {
                            $client->put(sprintf('/sales/equipment_shipping_records/%d/status', $esrApi->getIriId()), ['json' => ['status' => 'SHIPPED']]);
                            $client->save('/comments', ['resource' => $esrApi->getIri(), 'message' => "This ESR has been automatically shipped when " . $user->getFullname() . ' updated the shipping date of ER#' . $id]);
                        } catch (ClientException $exception) {
                            $DEFAULT_ERROR[] = sprintf("ERROR: this ER is linked to ESR#%s. Because ESR is not closed, your update in ER was not synchronized with ESR. Status update has failed because %s", $esrApi->getIriId(), $exception->getMessage());
                            $body = $form->toHTML();
                            break;
                        }
                    }
                }
            }
        }
        break;
    case 'combination':
        $DEFAULT_TITLE .= "\ER Combination";
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=combination&id=$id">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=combination&m[3]=add&id=$id">Add</a>
EOF;

        $fieldToSync = [
            'airport_code' => 'Airport',
            'del_ctry' => 'Country, delivery',
            'location_short' => 'Unit Location Short',
            'sales_org' => 'Sales Organization',
            'sales_rep' => 'Sales Rep Email',
            'agent_name' => 'Agent Name',
            'buyer_customer_id' => 'BUYER customer',
            'customer_id' => 'USER customer',
            'customer_name' => 'Customer',
        ];
        $combinationModeList = tldEquipment::getCombinationModeList();

        switch ($m[3]) {
            case 'add':
                $DEFAULT_TITLE .= "\Add";
                $form = new HTML_QuickForm('frm');
                $form->addElement('hidden', 'm[0]', 'equipment');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('hidden', 'm[2]', 'combination');
                $form->addElement('hidden', 'm[3]', 'selectSN');
                $form->addElement('hidden', 'id', $id);
                $form->addElement('header', 'title', 'Select combination');
                $form->addElement('select', 'comb_mod', 'Combination mode', ['' => ''] + $combinationModeList);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->addRule('comb_mod', 'Required', 'required');
                $body = $form->toHTML();
                break;
            case 'selectSN':
                $DEFAULT_TITLE .= "\Add";
                if (!in_array($_REQUEST['comb_mod'], $combinationModeList)) {
                    $DEFAULT_ERROR[] = 'ERROR: Invalid or empty combination mode';
                    break;
                }
                // listing
                switch ($_REQUEST['comb_mod']) {
                    case 'PRE-ASSEMBLY':
                        $pasRawList = tldEquipment::getAvailablePreAssemblyList();
                        $pasList = [];
                        foreach ($pasRawList as $pas) {
                            $pasList[$pas['sn']] = $pas['man_location'] . ', ' . $pas['type'] . ', ' . $pas['sn'] . ' -> ' . $pas['customer_name'];
                        }
                        if (empty($pasList)) {
                            $DEFAULT_ERROR[] = 'WARNING: No PAS available, could not continue';
                            break 2;
                        }
                        break;
                }
                // Form
                $form = new HTML_QuickForm('frm');
                $form->addElement('hidden', 'm[0]', 'equipment');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('hidden', 'm[2]', 'combination');
                $form->addElement('hidden', 'm[3]', 'selectSN');
                $form->addElement('hidden', 'comb_mod', $_REQUEST['comb_mod']);
                $form->addElement('hidden', 'id', $id);
                $form->addElement('header', 'title', 'Select Equipment');
                switch ($_REQUEST['comb_mod']) {
                    case 'PRE-ASSEMBLY':
                        $form->addElement('select', 'sn', 'PAS SN#', ['' => ''] + $pasList);
                        break;
                    case 'ER COMBINED':
                        $form->addElement('text', 'sn', 'ER SN#');
                        break;
                }
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->addRule('sn', 'This is required', 'required');
                $form->addRule('comb_mod', 'Required', 'required');

                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break;
                }

                $vars = tldUtils::cleanupFormInput($form->exportValues());
                // Check ER validity
                $erToComb = tldEquipment::bySN($vars['sn']);
                $erToCombID = $erToComb[0]['id'];
                $erToComb = new tldEquipment((int)$erToCombID);
                if ($erToComb->isEmpty()) {
                    $DEFAULT_ERROR[] = "ERROR: No ER found with SN {$vars['sn']}";
                    $body = $form->toHTML();
                    break;
                }
                // Check combination validity
                if ($erToComb->getID() == $eq->getID()) {
                    $DEFAULT_ERROR[] = 'ERROR: can not combine ER to himself...';
                    $body = $form->toHTML();
                    break;
                }
                // -- Check if combined already
                if ($erToComb->isCombinationCombined() || $erToComb->isCombinationPreAssembly()) {
                    $DEFAULT_ERROR[] = 'ERROR: This equipment is already combined with ER#' . $erToComb->getParentID();
                    $body = $form->toHTML();
                    break;
                }
                // -- Misc
                switch ($vars['comb_mod']) {
                    case 'PRE-ASSEMBLY':
                        if (!$erToComb->isPAS()) {
                            $DEFAULT_ERROR[] = "ERROR: This ER {$vars['sn']} is not a ER PAS";
                            $body = $form->toHTML();
                            break 2;
                        }
                        break;
                    case 'ER COMBINED':
                        if ($erToComb->isPAS()) {
                            $DEFAULT_ERROR[] = "ERROR: This ER {$vars['sn']} is a ER PAS";
                            $body = $form->toHTML();
                            break 2;
                        }
                        break;
                }
                // Add combination
                $e = $eq->addCombination($vars['comb_mod'], $erToCombID);
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "ERROR: Could not add combination. Reason: $e";
                    break;
                }
                $log = "Added ER#$erToCombID ({$vars['sn']}) as {$vars['comb_mod']}";
                $eq->addLogEntry($user->getID(), TldDatabase::escape($log));
                // confirm
                $body .= "<p>ER#$erToCombID added as {$vars['comb_mod']} successfully</p>";

                // Ask synchronise ER DATA for COMBINED ------------>

                switch ($vars['comb_mod']) {
                    case 'ER COMBINED':
                        $cells = [];
                        // Primary
                        $report = new tldAssocTable(
                            $header,
                            [
                                'airport_code' => 'Airport',
                                'del_ctry' => 'Country, delivery',
                                'location_short' => 'Unit Location Short',
                                'sales_org' => 'Sales Organization',
                                'sales_rep' => 'Sales Rep Email',
                                'agent_name' => 'Agent Name',
                                'buyer_customer_display' => 'BUYER customer',
                                'user_customer_display' => 'USER customer',
                                'customer_name' => 'Customer',
                            ],
                            ['title' => "ER#$id {$eq->getSN()} - PRIMARY"]
                        );
                        $cells[] = $report->fetch();
                        // New combined
                        $report = new tldAssocTable(
                            $erToComb->getHeader(),
                            [
                                'airport_code' => 'Airport',
                                'del_ctry' => 'Country, delivery',
                                'location_short' => 'Unit Location Short',
                                'sales_org' => 'Sales Organization',
                                'sales_rep' => 'Sales Rep Email',
                                'agent_name' => 'Agent Name',
                                'buyer_customer_display' => 'BUYER customer',
                                'user_customer_display' => 'USER customer',
                                'customer_name' => 'Customer',
                            ],
                            ['title' => "ER#$erToCombID {$erToComb->getSN()} - SECONDARY"]
                        );
                        $cells[] = $report->fetch();
                        // display
                        $yesLink = "$php_self?m[0]=equipment&m[1]=view&m[2]=combination&m[3]=sync&id=$id&erid=$erToCombID";
                        $noLink = "$php_self?m[0]=equipment&m[1]=view&m[2]=combination&id=$id";
                        $report = new tldHTMLTable(
                            $cells,
                            [
                                'cols' => 2,
                                'attribs' => [
                                    'table' => " width='100%'",
                                    'tr' => " bgcolor='#FFFFFF'",
                                    'td' => " width='50%'",
                                ],
                                'title' => "Synchronise ER#$id informations below for the new combination of ER#$erToCombID ? <a href=\"$yesLink\">YES</a> / <a href=\"$noLink\">NO</a>",
                            ]
                        );
                        $body .= $report->fetch();
                        break;
                }
                break;
            case 'sync':
                // Check params
                if (empty($erid) || !is_numeric($erid)) {
                    $DEFAULT_ERROR[] = 'ERROR: parameters empty or invalid';
                    break;
                }
                $erToSync = new tldEquipment($erid);
                if ($erToSync->isEmpty()) {
                    $DEFAULT_ERROR[] = "ERROR: ER#$erid not found!";
                    break;
                }
                // Sync ER info
                $a = [];
                foreach ($fieldToSync as $field => $label) {
                    $a[$field] = $header[$field];
                }
                $e = $erToSync->update($a);
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "INTERNAL ERROR: Could not synchronise ER info. Reason: $e";
                    break;
                }
                $body .= "<p>ER#$erid informations synchronised!</p>";
                break;
            case 'delete':
                if (empty($erid) || !is_numeric($erid)) {
                    $_DEFAULT_ERROR[] = 'ERROR: Parameters invalid';
                    break;
                }
                // Remove
                $e = $eq->removeCombination($erid);
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "ERROR: Could not remove combination. Reason: $e";
                    break;
                }
                $log = "Removed combination of ER#$erid";
                $eq->addLogEntry($user->getID(), TldDatabase::escape($log));
                // confirm
                $body .= "ER#$erid removed from the combination list";
                break;
        }

        $report = new tldReportColumnar(
            $eq->getCombinationErList(),
            [
                'xItems' => [
                    'id' => 'ID#',
                    'sn' => 'ER SN#',
                    'comb_mod' => 'Combination',
                    'model' => 'Model',
                    'man_location' => 'Factory',
                    'buyer_customer_display' => 'Buyer customer',
                    'dyt' => 'YT Date',
                    'dgt_rev' => 'Estimated GT',
                    'dgt_act' => 'Actual GT Date',
                    'date_shipped' => 'Ship Date',
                ],
                'title' => 'Combination ER list',
                'links' => [
                    'id' => "$php_self?m[0]=equipment&m[1]=view&id=",
                ],
                'functions' => [
                    'Dissociate' => [
                        'url' => "$php_self?m[0]=equipment&m[1]=view&m[2]=combination&m[3]=delete&id=$id",
                        'param' => ['erid' => 'id'],
                        'confirmPopup' => 'Are you sure to remove this combination?',
                    ],
                ],
            ]
        );
        $body .= $report->fetch();

        // refresh header
        $header = $eq->getHeader();
        $smarty->assign('equipment', $header);
        break;
    case 'upgrade':
        $DEFAULT_TITLE .= "\ER Upgrade";
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=upgrade&id=$id">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=upgrade&m[3]=add&id=$id">Add</a>
EOF;
        if ($user->isInGroup(['gg_MIS'])) {
            $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="er_upgrade/er_upgrade_admin.php">Admin</a>
EOF;
        }
        $rows = tldEquipment_Upgrade::getUpgradesByER($id);
        $report = new tldReportColumnar(
            $rows,
            [
                'xItems' => [
                    'id' => 'ID#',
                    'poster_fullname' => 'Poster',
                    'dt_open' => 'Created on',
                    'dt_upgrade' => 'Effective Upgrade Date',
                    'description' => 'Description',
                    'pn' => 'Part Number',
                    'mod_link' => 'File',
                ],
                'links' => [
                    'mod_link' => [
                        'url' => '/en/private/common/index.php?m[0]=files&m[1]=view&id=',
                        'params' => ['id' => 'mod_file'],
                    ],
                    'id' => [
                        'url' => "$php_self?m[0]=er_upgrade&m[1]=view",
                        'params' => ['id' => 'id'],
                    ],
                    'pn' => [
                        'url'    => "/en/private/manufacturing/eng/dev.php?m[0]=bom&m[1]=view&erp=$ERP&btnSubmit=Submit&pn=",
                        'params' => ['pn' => 'pn', 'date' => 'dt_upgrade'],
                    ],
                ],
            ]
        );
        $body = $report->fetch();
        switch ($m[3]) {
            case 'add':
                $DEFAULT_TITLE .= "\Add";
                $form = new HTML_QuickForm('frm');
                $form->addElement('hidden', 'm[0]', 'equipment');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('hidden', 'm[2]', 'upgrade');
                $form->addElement('hidden', 'm[3]', 'add');
                $form->addElement('hidden', 'id', $id);
                $form->addElement('hidden', 'parent_id', $id);
                $form->addElement('header', 'title', 'Add an ER Upgrade');
                $form->addElement('text', 'pn', 'Part Number');
                $form->addElement('date', 'dt_upgrade', 'Effective Upgrade Date', ['format' => 'Y-m-d', 'addEmptyOption' => false, 'minYear' => date('Y') - 3, 'maxYear' => date('Y')]);
                $form->addElement('textarea', 'description', 'Upgrade Description', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']);
                $form->addElement('file', 'file', 'Attachment');
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->addRule('pn', 'Part Number must be alphanumeric', 'regex', '/^[a-zA-Z0-9]*$/');
                $form->setDefaults([
                    'dt_upgrade' => date('Y-m-d'),
                ]);
                $form->addRule('desc', 'This is required', 'required');

                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break;
                }
                $a = tldUtils::cleanupFormInput($form->exportValues());
                $dt_upgrade = vsprintf('%1$04d-%2$02d-%3$02d', $a['dt_upgrade']);
                $a['dt_upgrade'] = $dt_upgrade;
                // Check date
                $dt_upgrade = strtotime($dt_upgrade);
                $dt_er = strtotime($eq->getCreateDate());
                $diff = ($dt_upgrade - $dt_er) / 86400;
                if ($diff < 0) {
                    $DEFAULT_ERROR[] = 'ERROR: Effective Upgrade Date is older than ER creation date!';
                    break;
                }
                $a['poster_id'] = $user->getID();
                $e = tldEquipment_Upgrade::insert($a);
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "ERROR: There was a problem creating this ER Upgrade entry. Reason: $e";
                    break;
                }
                $file = $form->getElement('file');
                $file_array = $file->getValue();
                if ($file_array['tmp_name'] != '') {
                    $vars = [];
                    $vars['module'] = 'ER_UPGRADE';
                    $vars['parent_id'] = $e;
                    $vars['filename'] = $file_array['name'];
                    $vars['poster'] = $user->getID();
                    $x = tldModFile::insert($vars, $file_array);
                    if (is_string($x)) {
                        $DEFAULT_ERROR[] = "ERROR: There was a problem attaching the file. Reason: $x";
                    }
                }
                $body = "ER Upgrade entry #$e created successfully!";
                break;
        }
        break;
    case 'sb':
        $DEFAULT_TITLE .= "\Service Bulletin";
        // SB3
        $sbList = tldSB_line::byConstraints('1=1', ['where' => "sb_lines.er_id = {$eq->getID()} AND sb.id IS NOT NULL"]);
        $report = new tldReportColumnar(
            $sbList,
            [
                'xItems' => [
                    'parent_id' => 'SB#',
                    'sb_status' => 'SB status',
                    'category' => 'Category',
                    'confidential' => 'Confidential',
                    'sb_title' => 'Title',
                    'part_decision' => 'Part decision',
                    'service_decision' => 'Service decision',
                    'spr_id' => 'SPR#',
                    'csr_id' => 'CSR#',
                    'status' => 'ISI Status',
                    'closure_type' => 'Closure Type',
                ],
                'title' => 'SB3 list',
                'links' => [
                    'parent_id' => "$php_self?m[0]=sb&m[1]=view&id=",
                    'spr_id' => '/en/private/parts/parts.php?m[0]=spr&m[1]=view&id=',
                    'csr_id' => '/en/private/sales_service/service.php?m[0]=csr&m[1]=view&id=',
                ],
            ]
        );
        $body .= $report->fetch();

        // CRABS from SB
        $crabs = $eq->getCRABS([
            'opno' => 'Test',
            'dept' => 'Engineering',
            'code' => '303',
            'dsca' => "SB#%",
        ]);
        $report = new tldReportColumnar(
            $crabs,
            [
                'xItems' => [
                    'id' => 'CRAB#',
                    'status' => 'Status',
                    'dt' => 'Entered Date',
                    'pn' => 'Part Number',
                    't_opno' => 'Operation Number',
                    'dsca' => 'Description',
                    'who' => 'Who',
                    'fixer_fullname' => 'Fixed By',
                    'fix_dt' => 'Fixed Date',
                    'insp_fullname' => 'Inspected Name',
                    'insp_dt' => 'Inspected Date',
                    'inspected' => 'STATUS CHANGE',
                ],
                'title' => 'CRABS from SB',
                'links' => [
                    'id' => '/en/private/manufacturing/qa/dev.php?m[0]=crab&m[1]=view&id=',
                ],
            ]
        );
        $body .= $report->fetch();

        // SB1
        $sbs = tldSB::byEquipment($eq->getID());
        _setSPRLink($sbs, $eq->getID());
        $report = new tldReportColumnar(
            $sbs,
            [
                'xItems' => [
                    'id' => 'SB#',
                    'status' => 'Status',
                    'sb_type' => 'Type',
                    'urgency' => 'Urgency',
                    'title' => 'Title',
                    'entered_date' => 'Date',
                    'done' => 'Done',
                    'spr_link' => 'SPR',
                ],
                'title' => 'Old SB1',
                'links' => ['id' => "$php_self?m[0]=sbs&m[1]=view&id="],
            ]
        );
        $body .= $report->fetch();
        break;
    case 'hourmeter':
        $DEFAULT_TITLE .= "\Hour meter";
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=hourmeter&id=$id">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=hourmeter&m[3]=update&id=$id">Update hour meter</a>
EOF;

        switch ($m[3]) {
            case 'update':
                $form = new HTML_QuickForm('frm');
                $form->addElement('hidden', 'm[0]', 'equipment');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('hidden', 'm[2]', 'hourmeter');
                $form->addElement('hidden', 'm[3]', 'update');
                $form->addElement('hidden', 'id', $id);
                $form->addElement('header', 'title', 'Update hour meter');
                $form->addElement('text', 'hourmeter', 'Hour meter<br>(Actual: ' . $eq->getHours() . ')');
                $form->addElement('submit', 'btnSubmit', 'Update');
                $form->addRule('hourmeter', 'This is required', 'required');

                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break;
                }

                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $e = $eq->setHourMeter($vars['hourmeter'], 'ER', $eq->getID());
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "ERROR: Can not update hour meter. Reason: $e";
                    break;
                }
                $eq->addLogEntry($user->getID(), "Hour meter updated to {$vars['hourmeter']}");
                $body = "Hour meter updated successfully to {$vars['hourmeter']}";
                break;
            case 'viewTransaction':
                $DEFAULT_TITLE .= "\Transaction#$tid";

                $transHourHeader = $eq->getHourMeterTransactionByID((int)$tid);
                if (empty($transHourHeader)) {
                    $DEFAULT_ERROR[] = "ERROR: Transaction $tid not found";
                    break;
                }

                switch ($m[4]) {
                    case 'edit':
                        $DEFAULT_TITLE .= "\Edit";

                        $form = new HTML_QuickForm('frm');
                        $form->addElement('hidden', 'm[0]', 'equipment');
                        $form->addElement('hidden', 'm[1]', 'view');
                        $form->addElement('hidden', 'm[2]', 'hourmeter');
                        $form->addElement('hidden', 'm[3]', 'viewTransaction');
                        $form->addElement('hidden', 'm[4]', 'edit');
                        $form->addElement('hidden', 'id', $id);
                        $form->addElement('hidden', 'tid', $tid);
                        $form->addElement('header', 'title', 'Update transaction');
                        $form->addElement('text', 'hourmeter', 'Hourmeter');
                        $form->addElement('submit', 'btnSubmit', 'Update');
                        $form->addRule('hourmeter', 'This is required', 'required');
                        $form->setDefaults(['hourmeter' => $transHourHeader['hourmeter']]);

                        if (!$form->validate()) {
                            $body = $form->toHTML();
                            break;
                        }

                        $vars = tldUtils::cleanupFormInput($form->exportValues());
                        $a = ['hourmeter' => $vars['hourmeter']];
                        $e = $eq->updateHourMeterTransactionByID($tid, $a);
                        if (is_string($e)) {
                            $DEFAULT_ERROR[] = "INTERNAL ERROR: Transaction $tid not updated. Reason: $e";
                            break;
                        }
                        $eq->addLogEntry($user->getID(), "Hour meter transaction#$tid updated from {$transHourHeader['hourmeter']} to {$vars['hourmeter']}");
                        $body .= 'Transaction hour updated successfully';
                        break;
                }
                break;
        }


        $report = new tldReportColumnar(
            $eq->getHourMeterHistory(),
            [
                'xItems' => [
                    'id' => 'Transaction#',
                    'dt' => 'Date',
                    'hourmeter' => 'Hours',
                    'module' => 'Module',
                    'module_id' => 'Module ref#',
                ],
                'title' => 'Hours History',
                'functions' => [
                    'Edit' => [
                        'url' => "$php_self?m[0]=equipment&m[1]=view&m[2]=hourmeter&m[3]=viewTransaction&m[4]=edit&id=$id",
                        'param' => ['tid' => 'id'],
                        'img' => '/shared/icons/miscellaneous/edit.png',
                    ],
                ],
            ]
        );
        $body .= $report->fetch();
        break;
    case 'resetYT':
        if ('0000-00-00' === $eq->getYT()) {
            $DEFAULT_ERROR[] = 'ERROR: ER is not YT';
            break;
        }
        if (!$user->isInGroupLevel(['role_PSM', 'role_PSE', 'role_PSA', 'role_QAM', 'role_COO', 'role_QE'], $eq->getFactoryERP())) {
            $DEFAULT_ERROR[] = 'ERROR: you do not have permissions';
            break;
        }
        // Form
        $form = new HTML_QuickForm('frm');
        $form->addElement('header', 'title', 'Reset Yellow Tag');
        $form->addElement('hidden', 'm[0]', 'equipment');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'resetYT');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('select', 'confirm', 'Confirm', ['' => '', 'Y' => 'I confirm it is an exception with the below reason...']);
        $form->addElement('textarea', 'reason', 'Reason', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('reason', 'This is required', 'required');
        $form->addRule('confirm', 'This is required', 'required');

        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $e = $eq->resetYT($user->getID(), $vars['reason']);
        if (is_string($e)) {
            $DEFAULT_ERROR[] = "ERROR: Can not reset YT. Reason: $e";
            break;
        }
        $body = 'Yellow tag reseted successfully and notification sent to supervisor';
        break;
    case 'updateShipAndGTDates':
        if ($eq->getYT() !== '0000-00-00') {
            $DEFAULT_ERROR[] = 'ERROR: YT must be reset before using this function';
            break;
        }
        if (!$user->isInGroupLevel(['role_QAM'], $eq->getFactoryERP()) && !$user->isInGroup(['gg_ADMIN', 'role_CMO', 'role_COO'])) {
            $DEFAULT_ERROR[] = 'ERROR: you do not have permissions';
            break;
        }
        $form = new HTML_QuickForm('frm');
        $form->addElement('header', 'title', 'Update Dates');
        $form->addElement('hidden', 'm[0]', 'equipment');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'updateShipAndGTDates');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('text', 'date_shipped', 'Actual Shipped Date (YYYY-MM-DD)');
        $form->addElement('text', 'dgt_act', 'Actual GT Date (YYYY-MM-DD)');
        $form->addElement('select', 'confirm', 'Confirm', ['' => '', 'Y' => 'I confirm it is an exception with the below reason...']);
        $form->addElement('textarea', 'reason', 'Reason', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('reason', 'This is required', 'required');
        $form->addRule('confirm', 'This is required', 'required');
        $form->setDefaults($eq->itsDetails);

        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }

        $e = $eq->updateShipAndGTDates($user->getID(), tldUtils::cleanupFormInput($form->exportValues()));
        if (is_string($e)) {
            $DEFAULT_ERROR[] = "ERROR: Can not update the dates. Reason: $e";
            break;
        }
        $body = 'Dates were successfully updated and notification has been sent.';
        break;
    case 'crabs':
        $statusCRABS = ['All' => 'All', 'PENDING' => 'PENDING', 'CLOSED' => 'CLOSED'];
        $stageCRABS = ['All' => 'All', 'Assy' => 'Assy', 'QA' => 'QA', 'Test' => 'Test'];
        $form = new HTML_QuickForm('FrmCrab');
        $form->addElement('header', 'title', 'CRABS Status');
        $form->addElement('hidden', 'm[0]', 'equipment');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'crabs');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('select', 'status', 'Status', $statusCRABS);
        $form->addElement('select', 'opno', 'Stage', $stageCRABS);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->setDefaults(['status' => $status, 'opno' => $opno]);
        $body .= $form->toHTML();

        if ($form->validate()) {
            $vars = ['status' => $status, 'opno' => $opno];
        }

        $body .= _getCrabList($eq->getCRABS($vars));
        if ($eq->hasPreAssemblyER()) {
            foreach ($eq->getCombinationErList('PRE-ASSEMBLY') as $erVal) {
                $pas = new tldEquipment($erVal['id']);
                $body .= _getCrabList($pas->getCRABS($vars), 'CRABs for PAS# ' . $pas->getSN());
            }
        }
        break;
    case 'tasks':
        $DEFAULT_TITLE .= "\Tasks";
        $DEFAULT_MENU .= <<<EOF
    <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=form&m[2]=newTask&module=ER&parent_id=$id">New Task</a>
EOF;
        $sess['calendar']['tasks'] = array_merge((array)$eq->getTasks(), (array)$eq->getSeqs());
        $data = [];
        $body .= _getTaskList(array_merge((array)$eq->getTasks(), (array)$eq->getSeqs()));
        if ($eq->hasPreAssemblyER()) {
            foreach ($eq->getCombinationErList('PRE-ASSEMBLY') as $erVal) {
                $pas = new tldEquipment($erVal['id']);
                $body .= _getTaskList(array_merge((array)$pas->getTasks($vars), (array)$pas->getSeqs($vars)), 'Tasks for PAS# ' . $pas->getSN());
            }
        }
        break;
    case 'warranty':
        $DEFAULT_TITLE .= "\Warranty";

        $cells = [];
        // Warranty info
        $form = new tldAssocTable(
            $eq->getWarrantyDetails(),
            [
                'date_shipped' => 'Date Shipped',
                'warranty_length' => 'Warranty Length in Months',
                'warranty_length_hours' => 'Warranty Length in Hours',
                'warranty_end_date' => 'Warranty End Date',
                'is_under_warranty' => 'Warranty Status',
                'warranty_conditions' => 'Special Warranty Conditions',
            ],
            ['title' => 'Warranty Details']
        );
        $cells[] = $form->fetch();
        // Case of COMBINED
        if ($eq->hasCombinedER()) {
            $report = new tldReportColumnar(
                $eq->getCombinationErList(),
                [
                    'xItems' => [
                        'sn' => 'SN#',
                        'date_shipped' => 'Date Shipped',
                        'warranty_length' => 'Warranty Length in Months',
                        'warranty_end_date' => 'Warranty End Date',
                        'is_under_warranty' => 'Warranty Status',
                        'warranty_conditions' => 'Special Warranty Conditions',
                    ],
                    'links' => [
                        'sn' => [
                            'url' => "$php_self?m[0]=equipment&m[1]=view&m[2]=warranty",
                            'params' => ['id' => 'id'],
                        ],
                    ],
                    'title' => 'Warranty details of ER COMBINED',
                ]
            );
            $cells[] = $report->fetch();
        }
        // Display warranty conditions
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

        // List WC attached to the ER

        $report = new tldReportColumnar(
            tldWC::bySN($eq->getSN()),
            [
                'xItems' => [
                    'id' => 'WC#',
                    'warranty_status' => 'Status',
                    'claim_date' => 'Date',
                    'work_date' => 'Work Date',
                    'hours' => 'Hours',
                    'problem_desc' => 'Problem',
                    'return_parts' => 'Return Parts?',
                ],
                'title' => 'Warranty Claims',
                'links' => ['id' => '/en/private/product_support/index.ps.php?m[0]=wc&m[1]=view&id='],
            ]
        );
        $body .= $report->fetch();
        break;
    case 'cust_details':
        $DEFAULT_TITLE .= "\Customer";
        $form = new tldAssocTable($header,
            ['customer_name' => 'Customer Name',
                'customer_contact' => 'Customer Contact',
                'customer_ref' => 'Customer Ref',
                'airport_code' => 'Airport Code',
                'location_short' => 'Location',
                'delivery_location' => 'Delivered Location',
                'customer_name_prev' => 'Previous Customer Name',
            ],
            ['title' => 'End User Customer Details',
                'links' => ['user_customer_id' => '/en/private/sales_service/sales.php?m[0]=customers&m[1]=view&id=']]
        );
        $body .= $form->fetch();

        $customersMaintainer = new tldCustomer($header['maintainer_customer_id']);
        $report = new tldAssocTable(
            $customersMaintainer->getHeader(),
            [
                'id' => 'ID#',
                'customer_name' => 'Customer Name',
                'customer_address' => 'Address',
                'customer_tel' => 'Main Tel',
                'customer_fax' => 'Main Fax',
                'type' => 'Customer Type',
                'url' => 'Website',
                'asm_fullname' => 'ASM',
            ],
            ['title' => "Maintainer Details<br/>{$header['user_customer_display']}",
                'links' => ['id' => '/en/private/sales_service/sales.php?m[0]=customers&m[1]=view&id=']]
        );
        $cells[] = $report->fetch();

        $custUser = new tldCustomer($header['customer_id']);
        $report = new tldAssocTable(
            $custUser->getHeader(),
            [
                'id' => 'ID#',
                'customer_name' => 'Customer Name',
                'customer_address' => 'Address',
                'customer_tel' => 'Main Tel',
                'customer_fax' => 'Main Fax',
                'type' => 'Customer Type',
                'url' => 'Website',
                'asm_fullname' => 'ASM',
            ],
            ['title' => "END USER Customer Details<br/>{$header['user_customer_display']}",
                'links' => ['id' => '/en/private/sales_service/sales.php?m[0]=customers&m[1]=view&id=']]
        );
        $cells[] = $report->fetch();

        $custBuyer = new tldCustomer($header['buyer_customer_id']);
        $report = new tldAssocTable(
            $custBuyer->getHeader(),
            [
                'id' => 'ID#',
                'customer_name' => 'Customer Name',
                'customer_address' => 'Address',
                'customer_tel' => 'Main Tel',
                'customer_fax' => 'Main Fax',
                'type' => 'Customer Type',
                'url' => 'Website',
                'asm_fullname' => 'ASM',
            ],
            ['title' => "BUYER Customer Details<br/>{$header['buyer_customer_display']}",
                'links' => ['id' => '/en/private/sales_service/sales.php?m[0]=customers&m[1]=view&id=']]
        );
        $cells[] = $report->fetch();

        $report = new tldHTMLTable(
            $cells,
            [
                'cols' => 3,
                'attribs' => ['table' => " width='100%'", 'tr' => " bgcolor='#FFFFFF'"],
                'title' => '',
            ]
        );
        $body .= $report->fetch();
        break;
    case 'serials':
        $body .= <<<EOF
            <p style="color:red;">
                This page has been migrated and should not be displayed anymore
            </p>
EOF;
        break;
    case 'software':
        $DEFAULT_TITLE .= "\Software";

        $body .= <<<EOF
<p style="color:red;">This page displays latest revision of the part numbers listed below.<br />
For unit specific revision at delivery, please see CBOM.</p>
EOF;

        $container = $kernel->getContainer();
        $client = $container->get(Client::class);
        try {
            $cbom = $client->get(
                sprintf('/ion/customized-bill-of-materials/chapters/site=%d;project=%s', $erp, $header['t_prno']),
                [
                    'query' => [
                        'productSignalCodeFilter' => implode('|', ['PRM', 'PRG']),
                        'productSignalCodeFilterMethod' => 'Equals',
                        'productSignalCodeAttribute' => 'engineeringSignalCode',
                        'signalCodeFilter' => implode('|', ['PRM', 'PRG']),
                        'signalCodeFilterMethod' => 'Equals',
                        'signalCodeAttribute' => 'engineeringSignalCode',
                    ]
                ]
            );

            $tldCBOM = new tldCBOM($erp, $date, $header['t_prno'], ['lang' => $selang], $cbom['items']);
        } catch (ClientException $exception) {
            $DEFAULT_ERROR[] = "ERROR: CBOM not found.";
            break;
        }
        $reports = $tldCBOM->itsBOMAsArray;
        $today = date('Y-m-d');
        $report = new tldReportColumnar(
            $reports,
            [
                'xItems' => [
                    't_sitm' => 'Item',
                    't_csig_edm' => 'Code',
                    't_dsca' => 'Description',
                ],
                'title' => 'Software',
                'links' => ['t_sitm' => "/en/private/manufacturing/eng/dev.php?m[0]=getfile&m[1]=drawing&erp=$ERP&date=$today&item="],
            ]
        );
        $body .= $report->fetch();
        break;
    case 'files':
        $DEFAULT_TITLE .= "\ER Files";
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=files&m[3]=TLDFile&id=$id">Add TLD File</a>
EOF;
        $flagAccess = false;
        if ($user->isInGroup(['gg_ADMIN']) || $user->isInGroupLevel(['gg_SUPPORT', 'role_ASM', 'role_CSM', 'role_QAM', 'ROLE_QA'], $eq->getSSOERP()) || $user->isInGroupLevel(['role_RME', 'gg_SUPPORT', 'role_QAM', 'ROLE_QA'], $eq->getFactoryERP())) {
            $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=files&m[3]=customerFile&id=$id">Add Customer File</a>
EOF;
            $flagAccess = true;
        }
        switch ($m[3]) {
            case 'out':
                $e = tldEquipment::outCustomerFile($file_id);
                break;
            case 'delete':
                if (!$flagAccess) {
                    $DEFAULT_ERROR[] = 'ERROR: You do not have the permission to delete a Customer File';
                    break;
                }
                if ($file_id) {
                    $e = tldEquipment::deleteFile($file_id);
                    if (is_string($e)) {
                        $DEFAULT_ERROR[] = "ERROR: Problem deleting Customer File#$file_id...<br/>Reason: $e";
                        break;
                    }
                    $log = $eq->addLogEntry(
                        $user->getID(),
                        "Customer File#$file_id: $file_name deleted"
                    );
                    if (is_string($log)) {
                        $DEFAULT_ERROR[] = "ERROR: Problem adding logs...<br/>Reason: $log";
                    }
                    $body .= "Customer File#$file_id deleted successfully!";
                }
                break;
            case 'customerFile':
                if (!$flagAccess) {
                    $DEFAULT_ERROR[] = 'ERROR: You do not have the permission to add a Customer File';
                    break;
                }
                $DEFAULT_TITLE .= "\Add a Customer File";
                $form = new HTML_QuickForm('frmAddcustFile');
                $form->addElement('header', 'title', 'Add a Customer File');
                $form->addElement('hidden', 'm[0]', 'equipment');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('hidden', 'm[2]', 'files');
                $form->addElement('hidden', 'm[3]', 'customerFile');
                $form->addElement('hidden', 'id', $id);
                $form->addElement('textarea', 'description', 'File Description', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '7']);
                $form->addElement('file', 'file', 'Customer File');
                $required = ['date', 'description', 'file'];
                foreach ($required as $key => $field) {
                    $form->addRule($field, 'Required', 'required');
                }
                $form->setDefaults(['date' => date('Y-m-d')]);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break 2;
                }
                $vars = tldUtils::cleanupFormInput($form->exportValues());
                //File Management
                $file = $form->getElement('file');
                $file_array = $file->getValue();
                if ($file_array['tmp_name'] != '') {
                    $vars['filename'] = $file_array['name'];
                    $e = $eq->insertFile($file_array['tmp_name'], $vars);
                    if (is_string($e)) {
                        $DEFAULT_ERROR[] = "ERROR: Problem adding the Customer File in the database...<br/> $e";
                        break;
                    }
                    $service_file = $eq->getFiles($e);
                    $log = $eq->addLogEntry(
                        $user->getID(),
                        "Customer File#$e: " . $service_file['filename'] . ' uploaded'
                    );
                    if (is_string($log)) {
                        $DEFAULT_ERROR[] = "ERROR: Problem adding logs...<br/>Reason: $log";
                    }
                    $body .= "Customer File#$e uploaded successfully!";
                }
                break;
            case 'TLDFile':
                $DEFAULT_TITLE .= "\Add a TLD File";
                $form = new HTML_QuickForm('frmAddTLDFile');
                $form->addElement('header', 'title', sprintf("Add a TLD File(s) (max total size %s)", ini_get('upload_max_filesize')));
                $form->addElement('hidden', 'm[0]', 'equipment');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('hidden', 'm[2]', 'files');
                $form->addElement('hidden', 'm[3]', 'TLDFile');
                $form->addElement('hidden', 'id', $id);
                for ($i = 1; $i < 10; $i++) {
                    $form->addElement('textarea', "description[$i]", 'File Description', ['wrap' => 'VIRTUAL', 'cols' => '60', 'rows' => '4']);
                    $form->addElement('file', "file[$i]", 'TLD File');
                }
                $form->setDefaults(['date' => date('Y-m-d')]);
                $form->addElement('header', 'title', '<br>');
                $form->addElement('submit', 'btnSubmit', 'Submit Files');
                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break 2;
                }
                $vars = tldUtils::cleanupFormInput($form->exportValues());
                //File Management
                for ($i = 1; $i < 10; $i++) {
                    $file = $form->getElement("file[$i]");
                    $file_array = $file->getValue();
                    if ($file_array['tmp_name'] != '') {
                        $vars['filename'] = $file_array['name'];
                        $e = tldModFile::insert(
                            [
                                'module' => 'ER',
                                'parent_id' => $id,
                                'description' => $vars['description'][$i],
                                'filename' => $vars['filename'],
                                'poster' => $user->getID(),
                            ],
                            $file_array
                        );

                        if (is_string($e)) {
                            $DEFAULT_ERROR[] = "ERROR: Problem adding the TLD File in the database...<br/> $e";
                            break;
                        }
                        $body .= "TLD File#$e uploaded successfully!";
                    }
                }
                break;
        }
        // display
        $body .= _getFileView($eq);
        break;
    case 'schematics':
        $DEFAULT_TITLE .= "\Schematics";
        if ($dwg) {
            $rows = $eq->getSchematics();
            $_PASS = [];
            foreach ($rows as $row) {
                if (trim($row['serial']) === $dwg) {
                    $_PASS = $row;
                    break;
                }
            }

            if ([] !== $_PASS) {
                try {
                    $container = $kernel->getContainer();
                    $client = $container->get(Client::class);
                    $fileStremResponse = new FileStreamedResponseFactory($client);
                    $response = $fileStremResponse->create(
                        sprintf('ion/bill-of-materials/drawings/site=%d;project=;product=%s', $erp, $dwg)
                    );

                    $response->send();
                    exit;
                } catch (ClientException $exception) {
                    $DEFAULT_ERROR[] = "ERROR: Drawing not found.";
                }
            } else {
                $DEFAULT_ERROR[] = "ERROR: Drawing not found.";
            }
        } else {
            $body .= <<<EOF
<p style="color:red;">This page displays the latest revision of the part numbers listed below.<br />
For unit specific revision at delivery, please see CBOM.</p>
EOF;

            $schematics = $eq->getSchematics();
            $report = new tldReportColumnar(
                $schematics,
                [
                    'xItems' => [
                        'component' => 'Component',
                        'model' => 'Model',
                        'serial' => 'Schematic Drawing#',
                        'brand' => 'Brand',
                    ],
                    'title' => 'Schematic Diagrams',
                    'links' => ['serial' => "$php_self?m[0]=equipment&m[1]=view&m[2]=schematics&id=$id&dwg="],
                ]
            );
            $body .= $report->fetch();

            $schematics = array_column($schematics, 'serial');

            $container = $kernel->getContainer();
            $client = $container->get(Client::class);
            try {
                $cbom = $client->get(
                    sprintf('/ion/customized_bill_of_materials/site=%d;project=%s', $erp, $header['t_prno']),
                    [
                        'query' => [
                            'productSignalCodeFilter' => implode('|', ['ESC', 'HSC', 'BSC', 'FLD']),
                            'productSignalCodeFilterMethod' => 'Equals',
                            'productSignalCodeAttribute' => 'engineeringSignalCode',
                            'itemsSignalCodeFilter' => implode('|', ['ESC', 'HSC', 'BSC', 'FLD']),
                            'itemsSignalCodeFilterMethod' => 'Equals',
                            'itemsSignalCodeAttribute' => 'engineeringSignalCode',
                            'depth' => 20,
                            'flatResult' => 1,
                        ]
                    ]
                );

                $tldCBOM = new tldCBOM($erp, $date, $header['t_prno'], ['lang' => $selang ?? null], $cbom['items']);
                $extraSchematics = $tldCBOM->itsBOMAsArray;
            } catch (ClientException $exception) {
                $DEFAULT_ERROR[] = "ERROR: CBOM not found.";
                break;
            }
            $report = new tldReportColumnar(
                $extraSchematics,
                [
                    'xItems' => [
                        't_dsca' => 'Description',
                        't_sitm' => 'Schematic Drawing#',
                    ],
                    'title' => 'Extra Schematic Diagrams',
                    'links' => ['t_sitm' => "/en/private/manufacturing/eng/dev.php?m[0]=getfile&m[1]=drawing&erp=$ERP&date=$date&item="],
                ]
            );
            $body .= $report->fetch();


        }
        break;
    case 'tldlink':
        $msg = null;
        if (!empty($_POST) && !$user->isInGroup(['role_PSM', 'role_PSA', 'role_PSE', 'role_ENG', 'role_EM', 'role_QAM'])) {
            $DEFAULT_ERROR[] = 'You are not allowed to modify this value.';
        } elseif (!empty($_POST) && isset($_POST['tld_link']) && $eq->isTLDLink() !== (bool)$_POST['tld_link']) {
            $eq->update(['tld_link' => TldDatabase::escape($_POST['tld_link'])]);
            $eq->refresh();
            $msg = "ER updated:<br><ul><li>tld_link updated to '{$_POST['tld_link']}'</li></ul>";
        }

        $form = new HTML_QuickForm('frmUpdateLink', 'post', "$php_self?m[0]=equipment&m[1]=view&m[2]=tldlink&id=$id");
        $form->addElement('hidden', 'm[0]', 'equipment');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'tldlink');
        $form->addElement('hidden', 'id', $id);

        $form->addElement('text', 'fms_end_use_date', 'FMS end use date', ['class' => 'datepicker', 'style' => 'min-width:400px;']);
        $form->addElement('select', 'sim_status', 'SIM status', tldEquipment::getSimStatusList());
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('comment', 'Required', 'required');
        $details = $eq->itsDetails;
        if ($details['fms_end_use_date'] === '0000-00-00') {
            // Unsetting so we can see the placeholder
            unset($details['fms_end_use_date']);
        }
        $form->setDefaults($details);

        if ($form->validate() && empty($DEFAULT_ERROR)) {
            $vars = tldUtils::cleanupFormInput($form->exportValues());
            if ($eq->itsDetails['sim_status'] !== $vars['sim_status']) {
                $eq->update(['sim_status' => $vars['sim_status']]);
                $msg = "ER updated:<br><ul><li><b>SIM Status</b> updated to '{$vars['sim_status']}'</li></ul>";
            }
            if ($eq->getFMSEndUseDate() !== (empty($vars['fms_end_use_date']) ? '0000-00-00' : $vars['fms_end_use_date'])) {
                $newDate = trim($vars['fms_end_use_date']);
                $eq->update(['fms_end_use_date' => empty($newDate) ? null : $newDate]);
                $msg .= "ER updated:<br><ul><li><b>FMS end use date</b> updated to '{$vars['fms_end_use_date']}'</li></ul>";
            }

            $eq->refresh();
        }
        $form = $form->toHtml();

        // Done in JS so it is always up to date aven after updating the form
        $js = <<<'JS'
<script>
    $(document).ready(function() {
      $("input[name='fms_end_use_date']").attr('placeholder', 'Calculated date is %s');
    });
</script>
JS;
        $body .= sprintf($js, strtolower($eq->itsDetails['calculated_fms_end_use_date']));

        if (null !== $msg) {
            $e = $eq->addLogEntry($user->getID(), TldDatabase::escape($msg));
            if (is_string($e)) {
                $DEFAULT_ERROR[] = "ERROR: Problem adding logs...<br/>Reason: $e";
            }
        }

        $DEFAULT_TITLE .= "\LINK (IoT)";

        $color = $eq->isTLDLink() ? '#6cc071' : '#ff5252';
        $content = $eq->isTLDLink() ? 'YES' : 'NO';
        $value = sprintf('%b', !$eq->isTLDLink());
        $simColor = $eq->isSimActive() ? '#6cc071' : '#ff5252';
        $url = sprintf('https://www.linkfms.com/fms/html5/index.html#/EquipmentResourceTable/table?cqf={"$F":"plateNumber","$O":"equals","$V":"%s"}&goToEditIfSingleEntity', $eq->getSN());

        $body .= <<<EOF
    <table border="0" cellspadding="2" cellspacing="4" cellpadding="10">
        <tr>
            <td align="center" bgcolor="$color" width="80px">
                <form method="post" action="$php_self?m[0]=equipment&m[1]=view&m[2]=tldlink&id=$id">
                    <input type="hidden" name="tld_link" value="$value">
                    <button type="submit" name="submit_param" style="background:none;border:none;padding:0;cursor:pointer;outline:none;" onmouseover='this.style.textDecoration="underline"' onmouseout='this.style.textDecoration="none"'>
                        <b>LINK :</b><br>$content
                    </button>
                </form>
            </td>
            <td align="center" bgcolor="$simColor">
                <b>SIM CARD :</b><br>{$eq->getSimStatus()}
            </td>
            <td align="center">
				<a href='{$url}'>
					<img src="/shared/images/link-logo.jpg" alt="TLD-Link" title="TLD-Link" width="80px" /><br/><span>Cockpit Access</span>
				</a>
            </td>
            <td>
                $form
            </td>
        </tr>
    </table>
EOF;

        $fmsContractTable = new tldAssocTable(
            $eq->itsDetails,
            ['maintenance_contract_ref' => 'FMS contract'],
        );

        $body .= $fmsContractTable->fetch();

        $report = new tldReportColumnar(
            $eq->getTldLinkComponents(),
            [
                'xItems' => [
                    'component' => 'Component',
                    'model' => 'Model',
                    'serial' => 'Serial',
                    'brand' => 'Brand',
                ],
                'title' => 'Link components',
            ]
        );
        $body .= $report->fetch();

        break;
    case 'options':
        $DEFAULT_TITLE .= "\SOL Options";
        $intOptions = $extOptions = [];
        if (!empty ($sor_lid)) {
            $intCaty = tldSOL::getInternalCategoriesList();
            $intOptions = tldSOROpts::byParent($sor_lid, ['include' => $intCaty]);
            $extCaty = tldSOL::getExternalCategoriesList();
            $extOptions = tldSOROpts::byParent($sor_lid, ['include' => $extCaty]);
        }

        $report = new tldReportColumnar(
            $intOptions,
            [
                'xItems' => [
                    'caty' => 'Category',
                    'dsca' => 'Description',
                ],
                'title' => 'Internal options',
                'showItemNumbers' => true,
            ]
        );
        $body .= $report->fetch();

        $report = new tldReportColumnar(
            $extOptions,
            [
                'xItems' => [
                    'caty' => 'Category',
                    'dsca' => 'Description',
                ],
                'title' => 'External options',
                'showItemNumbers' => true,
            ]
        );
        $body .= $report->fetch();
        break;
    case 'srs':
        $DEFAULT_TITLE .= "\Service Records";
        $DEFAULT_ERROR[] = 'WARNING: SR module is disabled and will be removed.';
        $DEFAULT_ERROR[] = 'If you try to get SR info, you will be redirected to the corresponding CSR';
        $report = new tldReportColumnar(
            tldSR::byParent($eq->getID()),
            [
                'xItems' => [
                    'id' => 'TLD SR#',
                    'sr_status' => 'Status',
                    'date_entered' => 'Date Entered',
                    'date' => 'Work Date',
                    'hourmeter' => 'Hours',
                    'work_type' => 'Work Type',
                    'reason' => 'Reason for work/SB#',
                    'technician' => 'TLD Technician',
                ],
                'title' => 'Service History',
                'links' => ['id' => '/en/private/sales_service/service.php?m[0]=csr&m[1]=forms&m[2]=byOldSrNum&sr_id='],
            ]
        );
        $body .= $report->fetch();
        break;
    case 'csr':
        $DEFAULT_TITLE .= "\CSR";

        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="equipment/commissioning_sheet.docx">Onsite Commissioning Sheet</a>
EOF;
        try {
            $user = $client->get('/me');
            $positionCode = $user['position']['code'];
            $authorizedPosition = ['CSM', 'DPM', 'CSTL', 'CSS', 'AST', 'GCSD'];

            if(\in_array($positionCode, $authorizedPosition, true)){
                $DEFAULT_MENU .= <<<EOF
                &nbsp;|&nbsp;
                <a href="/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view&m[2]=csr&m[3]=create_commissioning_csr&id=$id">Make Commissioning Record via CSR</a>
                &nbsp;
                EOF;
            }
        }
        catch (Exception $e) {
            $body .=  '<span style="color: red">' . $e->getMessage() . '</span>';
        }

        switch ($m[3]){
            case 'create_commissioning_csr':
                try {
                    $airport = $client->findOneBy('airports', ['code' => $eq->itsDetails['airport_code']]);
                    $airportIri = $airport->getIri();
                }
                catch (Exception $e){
                    $airportIri = null;
                }

                try {
                    $equipmentRecord = $client->findOneBy('equipment_records', ['legacyId' => $eq->getID()]);

                    $response = $client->post('/service/commissioning_customer_service_records', [
                        'json' => [
                            "title" => "Commissioning",
                            "equipmentRecord"=> $equipmentRecord->getIri(),
                            "description"=> "",
                            "airport"=> $airportIri,
                            "leader" => $user['@id'],
                            "plannedAt" => (new DateTime())->format("Y-m-d H:i:s"),
                        ]
                    ]);
                    header("location: /en/private/service/customer-service-records/{$response['id']}/show");
                }
                catch (Exception $e){
                    $body .=  '<span style="color: red">' . $e->getMessage() . '</span>';
                }
        }
        $report = new tldReportColumnar(
            tldCSR::byParent($eq->getID()),
            [
                'xItems' => [
                    'id' => 'CSR#',
                    'dt' => 'Date',
                    'sso' => 'SSO',
                    'status' => 'Status',
                    'work_type' => 'Work Type',
                    'tech_fullname' => 'Technician',
                    'short_desc' => 'Short description',
                    'apc' => 'APC',
                    'user_customer' => 'USER customer',
                    'module' => 'From module',
                ],
                'title' => $_title,
                'links' => [
                    'id' => '/en/private/sales_service/service.php?m[0]=csr&m[1]=view&id=',
                ],
            ]
        );
        $body .= $report->fetch();
        break;
    case 'toc':
        global $kernle;
        $client = $kernel->getContainer()->get(Client::class);

        try {
            $apiEquipmentRecord = $client->findOneBy('equipment_records', [
                'legacyId' => $eq->getID(),
            ]);
        } catch (ClientException) {
            $apiEquipmentRecord = null;
            $DEFAULT_ERROR[] = 'ERROR: Could not find Equipment Record on API database';
        }

        $DEFAULT_TITLE .= "\TOC Records";

        if ($apiEquipmentRecord) {
            $router = $kernel->getContainer()->get('router');

            $DEFAULT_MENU .= <<<EOF
                <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <a href="{$router->generate('technician_on_calls_write', ['equipmentRecordId' => $apiEquipmentRecord->getIriId()])}">Create TOC</a>
            EOF;
        }

        $rows = tldEquipment_Upgrade::getUpgradesByER($id);
        $report = new tldReportColumnar(
            $rows,
            [
                'xItems' => [
                    'id' => 'ID#',
                    'poster_fullname' => 'Poster',
                    'dt_open' => 'Created on',
                    'dt_upgrade' => 'Effective Upgrade Date',
                    'description' => 'Description',
                    'mod_link' => 'File',
                ],
                'title' => 'ER Upgrade Records',
                'links' => [
                    'mod_link' => [
                        'url' => '/en/private/common/index.php?m[0]=files&m[1]=view&id=',
                        'params' => ['id' => 'mod_file'],
                    ],
                    'id' => [
                        'url' => "$php_self?m[0]=er_upgrade&m[1]=view",
                        'params' => ['id' => 'id'],
                    ],
                ],
            ]
        );
        $body .= $report->fetch();
        $tocs = tldTOC::byConstraints(['erid' => $id], ['showAllStatuses' => true, 'orderByRaw' => "FIELD(toc.status, 'IN PROGRESS', 'SUSPENDED', 'SOLVED', 'CLOSED'), wf DESC, toc.dt DESC"]);
        if ([] === $tocs) {
            $DEFAULT_ERROR[] = 'No service records...';
            break;
        }
        $report = new tldReportColumnar(
            $tocs,
            [
                'xItems' => [
                    'id' => 'TOC#',
                    'sso_fullname' => 'SSO',
                    'status' => 'Status',
                    'dt' => 'Date Opened',
                    'dt_closed' => 'Date Closed',
                    'sn' => 'SN',
                    'tec_fullname' => 'Service Technician',
                    'customer_name' => 'Customer Name',
                    'con_fullname' => 'Contact Name',
                    'ifactor' => 'IF',
                    'prob_dsca' => 'Customer Problem Description',
                    'disp_tec' => 'Dispatch Tech?',
                    'hours' => 'Equipment Hours',
                    'unit_operation_status' => 'Unit operational status',
                ],
                'title' => 'TOCs',
                'links' => [
                    'id' => '/en/private/sales_service/service.php?m[0]=toc&m[1]=view&id='],
            ]
        );
        $body .= $report->fetch();
        break;
    case 'publications':
        include 'equipment/view.publications.inc.php';
        break;
    case 'log':
        $DEFAULT_TITLE .= "\Log";
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=log&m[3]=add&id=$id">Add log comment</a>
EOF;

        switch ($m[3]) {
            case 'add':
                $form = new HTML_QuickForm('frmAddComment', 'post');
                $form->addElement('header', 'title', 'Add comment');
                $form->addElement('hidden', 'm[0]', 'equipment');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('hidden', 'm[2]', 'log');
                $form->addElement('hidden', 'm[3]', 'add');
                $form->addElement('hidden', 'id', $id);
                $form->addElement('textarea', 'comment', 'Comment',
                    ['cols' => '35', 'rows' => '6']);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->addRule('comment', 'Required', 'required');

                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break;
                }

                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $e = $eq->addLogEntry($user->getID(), $vars['comment']);
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "ERROR: Problem adding comment...<br/>Reason: $e";
                }
                $body .= 'Comment successfully added!';
                break;
        }
        $body .= _getLogView($eq);
        break;
    case 'link':
        $report = new tldReportColumnar(
            tldModLink::byParent($id, 'ER'),
            [
                'xItems' => [
                    'id' => 'ID#',
                    'type' => 'Module',
                    'item' => 'Ref#',
                    'dsca' => 'Description',
                ],
                'title' => 'Links FROM Here...',
                'links' => ['id' => '/en/private/common/index.php?m[0]=links&m[1]=view&m[2]=redirect&id='],
            ]
        );
        $body .= $report->fetch();
        $report = new tldReportColumnar(
            tldModLink::byItem($id, 'ER'),
            [
                'xItems' => [
                    'id' => 'ID#',
                    'module' => 'Module',
                    'parent_id' => 'Ref#',
                    'dsca' => 'Description',
                ],
                'title' => 'Links TO Here...',
                'links' => ['id' => "/en/private/common/index.php?m[0]=links&m[1]=view&m[2]=redirect&reversed=1&erp=$erp&id="],
            ]
        );
        $body .= $report->fetch();
        break;
    case 'synchronize':
        $payload = [
            'legacyId' => (int) $eq->getID(),
            'serialNumber' => $eq->getSN(),
            'status' => $eq->getStatus(),
            'customerSerialNumber' => $eq->itsDetails['cust_asset_num'],
            'actualGreenTagDate' => $eq->itsDetails['dgt_act'],
        ];

        try {
            $product = $client->findOneBy('sales/products', ['name' => $eq->getModel()]);
            $payload['product'] = $product['@id'];

            $salesOrganization = $client->findOneBy('locations', ['legacyId' => $eq->getSSOID()]);
            $payload['salesOrganization'] = $salesOrganization['@id'];

            if (null !== $eq->itsDetails['eng_tier'] && '' !== $eq->itsDetails['eng_tier']) {
                $emissionRating = $client->findOneBy('emission_ratings', ['name' => $eq->itsDetails['eng_tier']]);
                $payload['emissionRating'] = $emissionRating['@id'];
            }

            if (null !== $eq->getAirport() && '' !== $eq->getAirport()) {
                $airport = $client->findOneBy('airports', ['code' => $eq->getAirport()]);
                $payload['airport'] = $airport['@id'];
            }

            $client->request('equipment_records', $apiEquipmentRecord['id'], 'link_synchronization', 'PUT', ['json' => $payload]);
        } catch (ClientException $exception) {
            $DEFAULT_ERROR[] = 'ERROR: Something went wrong synchronizing ER with Link. Reason: ' . $exception->getMessage();
            break;
        }

        $DEFAULT_SUCCESS[] = 'Equipment Record synchronized successfully with Link.';
    default:
        $wcHeader = $eq->getWarrantyDetails();
        switch ($wcHeader['is_under_warranty']) {
            case 'EFFECTIVE':
                $body .= <<<EOF
    <br><font color="#00FF00">This unit is still under warranty.
    <img src="/shared/bluesphere/22x22/actions/button_ok.png"></font>
EOF;
                break;
            case 'NOT SHIPPED':
                $body .= <<<EOF
    <br><font color="#FF0000">Not shipped yet.</font>
EOF;
                break;
            case 'MAYBE EXPIRED':
                $body .= <<<EOF
    <p style="color:orange;">WARNING: This unit is maybe not under warranty, please check points below:<br/>
    - < 50KW : hour meter warranty is 2000<br/>
    - > 50KW : hour meter warranty is 3000<br/>
    - Special warranty not empty
    </p>
EOF;
                break;
            default:
                $body .= <<<EOF
    <font color="#FF0000">WARNING:Warranty expired
    <img src="/shared/bluesphere/22x22/actions/button_cancel.png"></font>
EOF;
        }
        $sol = new tldSOL($header['sor_lid']);
        if (!$sol->isEmpty() && $sol->hasMissingExportLicence()) {
            $body .= <<<HTML
    <div style="color: red">SOL is Missing Export Licence.</div>
HTML;
        }
        switch ($m[2]) {
            case 'isAvailableForSale':
                if (($user->isInGroup(['gg_ADMIN']) || $user->isInGroupLevel(['role_PSM', 'role_PSE', 'role_PSA', 'role_COO'], $eq->getFactoryERP()) || $user->isInGroupLevel(['role_SAM', 'role_COO', 'role_EVP'], $eq->getSSOERP())) && !$sor_lid) {
                    $e = $eq->setAvailableForSale($user->getID());
                    if (is_string($e)) {
                        $DEFAULT_ERROR[] = "ERROR: There was a problem to make this unit available. Message was $e";
                    } else {
                        $eq->refresh();
                        $header = $eq->getHeader();
                        //send notification to all EVPS
                        tldGroup::emailMultipleGroups([
                            220 => ['role_PSM', 'role_PSE', 'role_PSA'],
                            250 => ['role_EVP', 'role_PSM', 'role_PSE', 'role_PSA'],
                            300 => ['role_EVP', 'role_PSM', 'role_PSE', 'role_PSA'],
                            310 => ['role_EVP', 'role_PSM', 'role_PSE', 'role_PSA'],
                            320 => ['role_EVP'],
                            400 => ['role_PSM', 'role_PSE', 'role_PSA'],
                            420 => ['role_PSM', 'role_PSE', 'role_PSA'],
                            510 => ['role_PSM', 'role_PSE', 'role_PSA'],
                            520 => ['role_PSM', 'role_PSE', 'role_PSA'],
                            540 => ['role_EVP', 'role_PSM', 'role_PSE', 'role_PSA'],
                            570 => ['role_PSM', 'role_PSE', 'role_PSA'],
                            600 => ['role_EVP'],
                            640 => ['role_EVP', 'role_PSM', 'role_PSE', 'role_PSA'],
                            660 => ['role_PSM', 'role_PSE', 'role_PSA'],
                            680 => ['role_EVP'],
                            700 => ['role_PSM', 'role_PSE', 'role_PSA'],
                            900 => ['role_ITD', 'role_COO'],
                        ],
                            $user->getEmail(),
                            "Unit ${header['sn']} ${header['model']} ${header['man_location']} ${header['customer_name']} ${header['apc_fullname']} ${header['sales_org']} has been made available for sale",
                            'Unit ' . $header['sn'] . ' has been made available for sale by ' .
                            $user->getFullname() .
                            getGeneralPage($apiEquipmentRecord)
                        );
                    }
                } else {
                    $DEFAULT_ERROR[] = 'ERROR: You do not have permission to make units available.';
                    if ($sor_lid) {
                        $DEFAULT_ERROR[] = '(Due to it linked to a SOL)';

                    }
                }
                break;
        }
        if ($eq->isAvailableForSale()) {
            $body .= <<<EOF
        <br><font color="#00FF00">This unit is available for immediate sale
        <img src="/shared/bluesphere/22x22/actions/button_ok.png">
        </font>
EOF;
        } else {
            $body .= <<<EOF
        <br><a href="$php_self?m[0]=equipment&m[1]=view&m[2]=isAvailableForSale&id=$id"
        onClick="return window.confirm('Are you sure you want to do this and that you understand what you are doing?');">
        Mark this unit as AVAILABLE FOR SALE</a>
EOF;
        }
        $body .= getGeneralPage($apiEquipmentRecord);
        break;
}

$smarty->assign('equipment', $eq->getHeader());
$body = $smarty->fetch("$PATH/equipment/equipment.menu.tpl") . $body;


function getGeneralPage($apiEquipmentRecord)
{
    global $user, $eq, $php_self, $kernel;
    $client = $kernel->getContainer()->get(Client::class);

    try {
        $response = $client->get('equipment_records', ['query' => [
            'serialNumber' => $eq->getSN(),
            'normalization_groups' => ['odp:view']
        ]]);
    } catch (ClientException) {
        $response = ['hydra:member' => []];
    }

    $pdi = $response['hydra:member'][0]['lastPreDeliveryInspection'];

    $header = $eq->getHeader();
    $cells = [];
    // LEFT VIEW
    // -- General
    $header['light'] = !empty($header['light']) ? 'Y' : 'N';
    $header['pdi_date'] = $pdi['plannedAt'] ? (new \DateTime($pdi['plannedAt']))->format('Y-m-d') : '';
    $header['pdi_status'] = $pdi['status'] ?? '';

    $auditLogManager = $kernel->getContainer()->get(AuditLogManager::class);
    $timeNonMissionCapability = $auditLogManager->time('technician_on_call', 'unitOperationalStatus', ['equipmentRecord' => $apiEquipmentRecord['id']], 'NMC');
    $header['time_NMC'] = $timeNonMissionCapability->days;

    $form = new tldAssocTable(
        $header,
        [
            'id' => 'Equipment ID#',
            'sn' => 'Equipment SN#',
            'status' => 'Status',
            'esr_id' => 'ESR ID#',
            'cust_asset_num' => 'Customer Asset#',
            'type' => 'Type',
            'model' => 'Model',
            'eng_tier' => 'Emission Rating',
            'er_batch_qty' => 'Batch Qty',
            'hours' => 'Hour meter',
            'man_location' => 'Manufacturer location',
            'apc_fullname' => 'Airport',
            'del_ctry' => 'Country, delivery',
            'location_short' => 'Unit Location Short',
            'options_desc' => 'Description of Installed Options',
            'sor_lid' => 'SOR Line#',
            'pdi_date' => 'PDI Date',
            'pdi_status' => 'PDI Status',
            'sor_uid' => 'SOR Unit ID#',
            'sales_org' => 'Sales Organization',
            'sso_service' => 'Service SSO',
            'sales_rep' => 'Sales Rep Email',
            'agent_name' => 'Agent Name',
            'mfg_comments' => 'MFG Comments',
            't_pdno' => 'Main Work Order#',
            't_prno' => 'MFG Project#',
            'sls_orno' => 'MFG SO#',
            'dt_commissioned' => 'Commissioning Date',
            'dyt' => 'Yellow Tag Date',
            'dgt_rev' => 'Estimated Green Tag Date',
            'dgt_com' => 'First Green Tag Date',
            'dgt_act' => 'Actual Green Tag Date',
            'date_shipped' => 'Actual Ship Date',
            'rrd_sso' => 'Date, Revenue Recognition, SSO',
            'rrd_erp' => 'Date, Revenue Recognition, Factory',
            'promised_cbom_date' => 'Promised CBOM Date',
            'last_cbom_update_date' => 'Last CBOM update Date',
            'publishable' => 'CBOM Manual Publishable',
            'diml' => 'Length (in mm)',
            'dimw' => 'Width (in mm)',
            'dimh' => 'Height (in mm)',
            'dimk' => 'Weight (in Kg)',
            'light' => 'ER Light ?',
            'maintenance_contract_ref' => 'FMS contract',
            'maintenance_contract_erp' => 'Service Contract ERP',
            'time_NMC' => 'Time NMC (days)',
        ],
        ['title' => 'GENERAL']
    );
    $cells[] = $form->fetch();
    // RIGHT VIEW
    // -- if secondary or pre assembly
    if ($eq->hasCombinedER() || $eq->hasPreAssemblyER()) {
        $report = new tldReportColumnar(
            $eq->getCombinationErList(),
            [
                'xItems' => [
                    'id' => 'ID#',
                    'sn' => 'SN#',
                    'comb_mod' => 'Mode',
                    'model' => 'Model',
                    'man_location' => 'Factory',
                    'dgt_act' => 'GT Date',
                    'date_shipped' => 'Ship Date',
                ],
                'links' => [
                    'id' => "$php_self?m[0]=equipment&m[1]=view&id=",
                ],
            ]
        );
        $combinedView = $report->fetch();
    } elseif ($eq->isCombinationCombined()) {
        $link = "$php_self?m[0]=equipment&m[1]=view&id=" . $eq->getParentID();
        $combinedView = <<<EOF
<p style="color:red;">This ER is combined as Secondary with <a href="$link" target="_blank">ER#{$eq->getParentID()}</a></p>
EOF;
    } elseif ($eq->isCombinationPreAssembly()) {
        $link = "$php_self?m[0]=equipment&m[1]=view&id=" . $eq->getParentID();
        $combinedView = <<<EOF
<p style="color:red;">This ER is PRE-ASSEMBLY of <a href="$link" target="_blank">ER#{$eq->getParentID()}</a></p>
EOF;
    } else {
        $combinedView = '<p>None</p>';
    }
    $cells[] = '<h3>ER COMBINATION</h3>' . $combinedView;
    //FINANCIALS (EXCOM only)
    if ($user->isInGroup(['gg_MIS', 'gg_EXCOM', 'ROLE_FAM']) && $header['sor_lid']) {
        $sol = new tldSOL($header['sor_lid']);
        $solHeader = $sol->itsHeader;
        $data = $sol->getSummary();
        $data['factory_margin'] = $solHeader['factory_margin'];
        $data['group_factory_margin'] = round(($data['marg_unit_pc'] / 100 + (1 - $data['marg_unit_pc'] / 100) * ($data['factory_margin'] / 100)) * 100, 2);
        $data['cur_prin'] = $sol->getDCUR();
        $data['cur_tp'] = $sol->getDCUR();

        $mfgID = tldMargin::getIDByER($header['id']);
        if (!empty($mfgID)) {
            $mfg_margin = new tldMargin($mfgID);
            $margins = $mfg_margin->getMargins($header['sor_lid']);
            $data['std_hour'] = $margins['std_hour'];
            $data['act_hour'] = $margins['act_hour'];
            $data['std_dir_margin_per'] = $margins['std_dir_margin_per'];
            $data['dir_margin'] = $margins['dir_margin'];
            $data['dir_margin_per'] = $margins['dir_margin_per'];
            $data['group_margin_per'] = $margins['group_margin_per'];
        }
        $report = new tldAssocTable(
            $data,
            [
                'pris_unit' => 'Unit Gross Selling Price',
                'prin_unit' => 'Unit Net Selling Price',
                'cur_prin' => 'Unit Net Selling Price Currency',
                'pris_tp_default_dcur' => 'Negotiated TP',
                'cur_tp' => 'Negotiated TP Currency',
                'discc_pc' => 'Customer Discount (%)',
                'discf_pc' => 'Factory Discount (%)',
                'marg_unit' => 'Sales Margin',
                'marg_unit_pc' => 'Sales Margin (%)',
                'factory_margin' => 'Projected Factory Margin (%)',
                'std_dir_margin_per' => 'Standard Factory Margin (%)',
                'dir_margin_per' => 'Actual Factory Margin (%)',
                'dir_margin' => 'Actual Factory Margin',
                'std_hour' => 'Standard Labour Hours',
                'act_hour' => 'Actual Labour Hours',
                'group_margin_per' => 'Actual Group Margin (%)',
                'group_factory_margin' => 'Projected Group Margin (%)',
            ],
            ['title' => 'FINANCIALS']
        );
        $cells[] = $report->fetch();
    }

    global $kernel;

    $client = $kernel->getContainer()->get(Client::class);

    try {
        $response = $client->get('quality/first_article_qualifications', [
            'query' => [
                'equipmentRecords.serialNumber' => $eq->getSN(),
                'order' => ['createdAt' => 'desc'],
            ],
        ]);
    } catch (\Symfony\Component\HttpClient\Exception\ClientException $e) {
        $response = ['hydra:member' => []];
    }

    $router = $kernel->getContainer()->get('router');

    $faqs = array_reduce($response['hydra:member'], static function ($memo, $faq) use ($router) {
        $memo[] = [
            'link' => sprintf('<a href="%s">#%s</a>', $router->generate('first_article_qualifications_show', ['id' => $faq['id']]), $faq['id']),
            'factory' => $faq['location']['name'],
            'created_at' => (new \DateTime($faq['createdAt']))->format('Y-m-d h:i:s'),
        ];
        return $memo;
    }, []);

    $faqsReport = new tldReportColumnar($faqs,
        [
            'xItems' => [
                'link' => 'ID',
                'factory' => 'Factory',
                'created_at' => 'Created At',
            ],
            'title' => 'FAQ list',
        ]
    );
    $cells[] = $faqsReport->fetch();

    $linkData = [
        'tld_link' => $header['tld_link'] ? 'Y' : 'N',
        'sim_status' => $header['sim_status'],
        'fms_contract_length' => $header['fms_contract_length'],
        'calculated_fms_end_use_date' => $header['calculated_fms_end_use_date'],
    ];

    $report = new tldAssocTable(
        $linkData,
        [
            'tld_link' => 'Link active',
            'sim_status' => 'Sim status',
            'fms_contract_length' => 'FMS Contract Length',
            'calculated_fms_end_use_date' => 'Calculated FMS end use date',
        ],
        ['title' => 'LINK information',]
    );
    $cells[] = $report->fetch();

    // DISPLAY
    $report = new tldHTMLTable(
        $cells,
        [
            'cols' => 2,
            'attribs' => [
                'table' => " width='100%'",
                'tr' => " bgcolor='#FFFFFF'",
                'td' => " width='50%'",
            ],
        ]
    );
    $body .= $report->fetch();


    return $body;
}

function _getPubsSubMenus()
{
    global $user;
    global $eq;
    global $id;
    global $ERP;
    global $header;
    global $date;
    if (!$user->isInGroupLevel(['gg_SUPPORT', 'gg_ENG'], $eq->getFactoryERP()) || !$eq->isPublishable()) {
        return '';
    }
    return <<<EOF
			&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=publications&m[3]=publish&id=$id&erp=$ERP&sn=${header['t_prno']}&date=$date"
				title="Publish manual from CBOM to Online Manuals System">Publish Manual</a>
EOF;
}

function _arrayMSort($array, $cols)
{
    $colarr = [];
    foreach ($cols as $col => $order) {
        $colarr[$col] = [];
        foreach ($array as $k => $row) {
            $colarr[$col]['_' . $k] = strtolower($row[$col]);
        }
    }
    $eval = 'array_multisort(';
    foreach ($cols as $col => $order) {
        $eval .= '$colarr[\'' . $col . '\'],' . $order . ',';
    }
    $eval = substr($eval, 0, -1) . ');';
    eval($eval);
    $ret = [];
    foreach ($colarr as $col => $arr) {
        foreach ($arr as $k => $v) {
            $k = substr($k, 1);
            if (!isset($ret[$k])) {
                $ret[$k] = $array[$k];
            }
            $ret[$k][$col] = $array[$k][$col];
        }
    }
    return $ret;
}

function _setSPRLink(&$rows, $erid)
{
    foreach ($rows AS &$row) {
        if ($row['spr_link']) {
            continue;
        }
        $sprs = tldModLink::byParent($row['id'], 'SB', 'SPR');
        foreach ($sprs AS $spr) {
            if ($row['spr_link']) {
                continue;
            }
            $ers = tldModLink::byParent($spr['item'], 'SPR', 'ER');
            foreach ($ers AS $er) {
                if ($row['spr_link']) {
                    continue;
                }
                if ($er['item'] == $erid) {
                    $row['spr_link'] = "<a href=\"/en/private/parts/parts.php?m[0]=spr&m[1]=view&id={$spr['item']}\">SPR{$spr['item']}</a>";
                }
            }
        }
    }
}

function _getCrabStep(array $line = [])
{
    if ($line['fix_dt'] === '0000-00-00 00:00:00') {
        return 'TO FIX';
    }
    if ($line['insp_dt'] === '0000-00-00 00:00:00') {
        return 'TO INSPECT';
    }

    return 'INSPECTED';
}

function _getCrabList($rows, $title = 'CRABs')
{

    foreach ($rows as &$line) {
        $line['inspected'] = "<a href=\"/en/private/manufacturing/qa/dev.php?m[0]=crab&m[1]=view&m[2]=inspected&id={$line['id']}\">" . _getCrabStep($line) . '</a>';
    }

    switch ($rows[0]['buid_fullname']) {
        case 'TLD MTL':
        case 'TLD STL':
        case 'TLD DTV':
            $langDesc = 'desc_fr';
            break;
        case 'TLD SHA':
        case 'TLD WUX':
            $langDesc = 'desc_zh';
            break;
        default:
            $langDesc = 'desc_en';
            break;
    }

    $report = new tldReportColumnar(
        $rows,
        [
            'xItems' => [
                'id' => 'CRAB#',
                'buid_fullname' => 'BU',
                'status' => 'Status',
                'dt' => 'Entered Date',
                'pn' => 'Part Number',
                't_opno' => 'Operation Number',
                $langDesc => 'Question Description',
                'dsca' => 'Description',
                'opno' => 'Stage',
                'dept' => 'Department',
                'code' => 'Code',
                'init_emno' => 'Found By (ID)',
                'initiator_fullname' => 'Found By',
                'fix_emno' => 'Fixed By (ID)',
                'fixer_fullname' => 'Fixed By',
                'fix_dt' => 'Fixed Date',
                'insp_emno' => 'Inspected By',
                'insp_fullname' => 'Inspected Name',
                'insp_dt' => 'Inspected Date',
                'inspected' => 'STATUS CHANGE',
            ],
            'title' => $title,
            'links' => [
                'id' => '/en/private/manufacturing/qa/dev.php?m[0]=crab&m[1]=view&id=',
            ],
        ]
    );
    return $report->fetch();
}

function _getTaskList($rows, $title = 'Tasks')
{
    $form = new tldReportMultiLevel(
        $rows,
        ['status', 'due_date'],
        [
            'id' => 'Task#',
            'status' => 'Status',
            'due_date' => 'Due',
            'task' => 'Task',
            'assignee_fullname' => 'Assignee',
        ],
        [
            'passField' => 'id',
            'title' => $title,
            'url' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=',
        ]
    );
    return $form->fetch();
}

function _getFileList(tldEquipment $er)
{
    global $user;
    // ACL to manage TLD files
    if ($user->isInGroup(['gg_ADMIN', 'gg_SUPPORT', 'role_RME', 'role_QAM', 'role_QA'])) {
        $URL = '/en/private/common/index.php?m[0]=files&m[1]=view&id=';
    } else {
        $URL = '/en/private/common/index.php?m[0]=files&m[1]=view&m[2]=out&id=';
    }
    // TLD file list of the ER
    $tldFileList = new tldReportColumnar(
        tldModFile::byParent($er->getID(), 'ER'),
        [
            'xItems' => [
                'id' => 'ID#',
                'date' => 'Date',
                'description' => 'Description',
                'filename' => 'Filename',
            ],
            'title' => 'TLD file list',
            'links' => ['id' => $URL],
        ]
    );
    return $tldFileList->fetch();
}


function _getCustomerFileList(tldEquipment $er)
{
    $id = $er->getID();
    $cuFileList = new tldReportColumnar(
        $er->getFiles(),
        [
            'xItems' => [
                'id' => 'ID#',
                'date' => 'Date',
                'description' => 'Description',
                'filename' => 'Filename',
            ],
            'title' => 'Customer file list',
            'links' => ['id' => "/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view&m[2]=files&m[3]=out&id=$id&file_id="],
            'functions' => [
                'Delete' => [
                    'url' => "/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view&m[2]=files&m[3]=delete&id=$id",
                    'param' => ['file_id' => 'id', 'file_name' => 'filename'],
                    'confirmPopup' => 'Are you sure to delete?',
                    'img' => '/shared/icons/application/delete.png',
                ],
            ],
        ]
    );
    return $cuFileList->fetch();
}

function _getFileView(tldEquipment $eq)
{
    $body = "<h3>Files of ER# {$eq->getSN()}</h3>";
    $fileList = _getFileList($eq);
    $customerFileList = _getCustomerFileList($eq);
    $body .= "<table><tr><td width=\"50%\">$fileList</td><td>$customerFileList</td></tr></table>";
    if ($eq->hasPreAssemblyER()) {
        foreach ($eq->getCombinationErList('PRE-ASSEMBLY') as $erVal) {
            $pas = new tldEquipment($erVal['id']);
            $body .= "<h3>Files of PAS# {$pas->getSN()}</h3>";
            $fileList = _getFileList($pas);
            $customerFileList = _getCustomerFileList($pas);
            $body .= "<table><tr><td width=\"50%\">$fileList</td><td>$customerFileList</td></tr></table>";
        }
    }
    return $body;
}

function _getLogList(tldEquipment $eq)
{
    $report = new tldReportColumnar(
        $eq->getLog(),
        [
            'xItems' => [
                'id' => 'ID#',
                'date' => 'Date',
                'poster_fullname' => 'Poster',
                'comment' => 'Comment',
            ],
            'title' => 'Log for ' . $eq->getSN(),
        ]
    );
    return $report->fetch();
}

function _getLogView(tldEquipment $eq)
{
    $body = _getLogList($eq);
    if ($eq->hasPreAssemblyER()) {
        foreach ($eq->getCombinationErList('PRE-ASSEMBLY') as $erVal) {
            $pas = new tldEquipment($erVal['id']);
            $body .= _getLogList($pas);
        }
    }
    return $body;
}
