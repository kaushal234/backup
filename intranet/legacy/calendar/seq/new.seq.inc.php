<?php

use ApiBundle\Client;
require_once 'ION/Supplier.php';
require_once 'ION/ItemBySite.php';
require_once 'controller/ItemInformationController.php';

use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

$overlib = $smarty->fetch('overlib.inc.js.tpl');
$overlib .= '<script type="text/javascript">$(function(){$(".overlib").overlib()});</script>';
$autosuggest = $smarty->fetch('autosuggest.inc.js.tpl');
$autosuggest .= '<script type="text/javascript">$(function(){
var uci2=$("[name=doc_type]").autosuggest({message:"Begin to type a portion of the name for auto suggestions",
onChange:function(o){$(uci2.input.display).val($(o.input.display).val());
$(uci2.input.value).val($(o.input.value).val());}});
});</script>';
$smarty->assign('html_head', $overlib . $autosuggest);

$DEFAULT_TITLE .= "\Start Sequence";

include_once 'sales_service.inc.php';

if (empty($m[2])) {
    $DEFAULT_ERROR[] = 'ERROR: no form template selected';
    return;
}

// COMMON Form fields
$form = new HTML_QuickForm('frm', 'post');
$form->addElement('hidden', 'm[0]', 'seq');
$form->addElement('hidden', 'm[1]', 'new');
$form->addElement('hidden', 'm[2]', $m[2]);
$form->addElement('header', 'header', 'Create Sequence');
$form->addElement('textarea', 'task', 'Task',
    ['wrap' => 'VIRTUAL', 'cols' => '100', 'rows' => '20']
);

$p = [];

// PRE PROCESSING
switch ($m[2]) {
    case 'stamp.request':
        $DEFAULT_TITLE .= ' - stamp request';
        $typeList = ['' => ''] + tldList::optionsByListNameAsListKeyListItem('list.stamp.caty','id');
        $stampList = ['' => '','company stamp'=>'Company stamp','legal representative stamp'=>'Legal representative stamp','Company + Legal representative stamps'=>'Company + Legal representative stamps'];
        // Form
        $form->addElement('text', 'dt_from', 'Required date:', ["class" => "datepicker"]);
        $form->addElement('select', 'bu_id', 'BU', tldLocation::getLocationList('smartyOptions'));
        $form->addElement('select', 'doc_type', 'Document Type', $typeList);
        $form->addElement('select', 'stamp_type', 'Stamp Type', $stampList);
        $form->addElement('text', 'party_name', 'Contractual party offical name#1:', ['size' => 40]);
        $form->addElement('text', 'party_name1', 'Contractual party offical name#2:', ['size' => 40]);
        $form->addElement('header', 'title', "Individual person1#");
        $form->addGroup(
            [
                $form->createElement('radio', "istldempolyee", 'YES', 'Yes', 'YES', ['id' => 'tldempolyee','onclick' => "javascript: if(this.value == 'YES'){ $('#username').removeAttr('hidden'); }"])        ,
                $form->createElement('radio', "istldempolyee", 'NO', 'No', 'NO', ['id' => 'nottldempolyee','onclick' => "javascript: if(this.value == 'NO'){ $('#username').removeAttr('hidden'); }"]),
                $form->createElement('text', 'name', "username", ['id' => 'username']),
            ], 'ind_name1' , 'Is TLD employee?',null
        );
        $form->addElement('header', 'title', "Individual person2#");
        $form->addGroup(
            [
                $form->createElement('radio', "istldempolyee", 'YES', 'Yes', 'YES', ['id' => 'tldempolyee','onclick' => "javascript: if(this.value == 'YES'){ $('#username').removeAttr('hidden'); }"])        ,
                $form->createElement('radio', "istldempolyee", 'NO', 'No', 'NO', ['id' => 'nottldempolyee','onclick' => "javascript: if(this.value == 'NO'){ $('#username').removeAttr('hidden'); }"]),
                $form->createElement('text', 'name', "username", ['id' => 'username']),
            ], 'ind_name2' , 'Is TLD employee?',null
        );
        $form->addElement('header', 'title', "Sales or purchase document");
        $form->addGroup(
            [
                $form->createElement('radio', "pur_sold", 'SALES', 'Sales', 'SALES', ['id' => 'sale']),
                $form->createElement('radio', "pur_sold", 'PURCHASE', 'Purchase', 'PURCHASE', ['id' => 'purchase']),
            ], 'pur_sold' , 'Purchase or Sale?',null
        );
        $form->addElement('text', 'desc_name', 'Description of purchased / sold goods or services:', ['size' => 80]);
        $dtask = <<<EOF

[]Comment:

EOF;

        $form->setDefaults(
            [
                'task' => $dtask,
                'bu_id' => $user->getBUID(),
                'assignee' => $user->getID(),
            ]
        );
        $form->addRule('assignee', 'Required', 'required');
        $form->addRule('bu_id', 'Required', 'required');
        $form->addRule('doc_type', 'Required', 'required');
        $form->addRule('stamp_type', 'Required', 'required');
        $form->addRule('party_name', 'Required', 'required');
        $form->addRule('desc_name', 'Required', 'required');
    break;
    case 'newpayment.request':
        $DEFAULT_TITLE .= "\Payment Request";
        $erp = tldLocation::getERPByID($user->getBUID());
        $erpObj = new tldBaanERP($erp);
        $dptList = tldDepartment::getListAsDepartmentDepartment();
        $buid = ['' => ''] + tldLocation::getLocationList('smartyOptions');
        $cursList = tldList::optionsByListNameAsListItemListItem('list.common.currency');
        $typeList = ['' => ''] + tldList::optionsByListNameAsListItemListItem('list.finance.payment.caty','id');
        $grpList = array_merge(tldGroup::getManagerGroups(), ['seq_investmentbudget.request.level.1']);
        $managerList = tldGroup::getUserListByMultipleGroup($grpList, null, ['smartyOptions' => true]);
        $supplier = new Supplier();
        $form->addElement('header', 'title', 'Payment request form');
        $form->addElement('hidden', 'module', 'SEQ');
        $form->addElement('text', 'dt_from', 'Required date:', ["class" => "datepicker"]);
        $form->addElement('text', 'suno', 'Supplier code:');
        $form->addElement('header', 'title', 'If it is one time supplier, please fill supplier name and bank information!');
        $form->addElement('text', 'sup_name', 'Supplier name(CH):', ['size' => 40]);
        $form->addElement('text', 'bank', 'Bank name:', ['size' => 40]);
        $form->addElement('text', 'bc', 'Bank account:', ['size' => 40]);
        $form->addElement('select', 'bu_id', 'Cost center', $buid, ['id' => 'buid']);
        $form->addElement('select', 'buyer_dpt_id', 'Department', ["" => ""] + $dptList);
        $form->addElement('select', 'type_id', 'Type', ["" => ""] + $typeList);
        $form->addElement('text', 'invoice', 'Invoice No.', ['size' => 40]);
        $form->addElement('text', 'amt', 'Payment Amount', ['size' => 40]);
        $form->addElement('select', 'cur', 'Currency', ['' => ''] + $cursList);
        $form->addElement('text', 'seq', 'Approved SEQ if any', ['size' => 40]);
        $form->addElement('select', 'assignee', 'First approver', $managerList);
        $dtask = <<<EOF

[]Description:

[]Comment: 




EOF;
        $form->addRule('bu_id', 'This is required', 'required');
        $form->setDefaults(
            [
                'task' => nl2br($dtask),
                'bu_id' => $user->getBUID(),
                'assignee' => $user->getID(),
                'cur' => 'CNY',
            ]
        );
        $form->addRule('amt', 'Required', 'required');
        $form->addRule('bu_id', 'Required', 'required');
        $form->addRule('buyer_dpt_id', 'Required', 'required');
        $form->addRule('type_id', 'Required', 'required');
        $form->addRule('cu_ocur', 'Required', 'required');
        $form->addRule('dt_from', 'Required', 'required');
        $form->addRule('invoice', 'Required', 'required');
        $form->addRule('amt', 'Field is numeric', 'numeric');
        $form->addRule('seq', 'Field is numeric', 'numeric');

        break;
    case 'newprepayment.request':
        $DEFAULT_TITLE .= "\Payment Request";
        $erp = tldLocation::getERPByID($user->getBUID());
        $erpObj = new tldBaanERP($erp);
        $dptList = tldDepartment::getListAsDepartmentDepartment();
        $buid = ['' => ''] + tldLocation::getLocationList('smartyOptions');
	    $cursList = tldList::optionsByListNameAsListItemListItem('list.common.currency');
        $typeList = ['' => ''] + tldList::optionsByListNameAsListItemListItem('list.finance.payment.caty','id');
        $grpList = array_merge(tldGroup::getManagerGroups(), ['seq_investmentbudget.request.level.1']);
        $managerList = tldGroup::getUserListByMultipleGroup($grpList, null, ['smartyOptions' => true]);
        $supplier = new Supplier();
        $supplierList = $supplier->getListSuppliersCode();
        $form->addElement('header', 'title', 'Prepayment request form');
        $form->addElement('hidden', 'module', 'SEQ');
        $form->addElement('text', 'dt_from', 'Required date:', ["class" => "datepicker"]);
        $form->addElement('text', 'suno', 'Supplier code:');
        $form->addElement('header', 'title', 'If it is one time supplier, please fill supplier name and bank information!');
        $form->addElement('text', 'sup_name', 'Supplier name(CH):', ['size' => 40]);
        $form->addElement('text', 'bank', 'Bank name:', ['size' => 40]);
        $form->addElement('text', 'bc', 'Bank account:', ['size' => 40]);
        $form->addElement('select', 'bu_id', 'Cost center', $buid, ['id' => 'buid']);
	    $form->addElement('select', 'buyer_dpt_id', 'Department', ["" => ""] + $dptList);
	    $form->addElement('select', 'type_id', 'Type', ["" => ""] + $typeList);
	    $form->addElement('text', 'invoice', 'Invoice No.', ['size' => 40]);
	    $form->addElement('text', 'amt', 'Payment Amount', ['size' => 40]);
	    $form->addElement('select', 'cur', 'Currency', ['' => ''] + $cursList);
        $form->addElement(  'select', 'po', 'With PO or not?', [""=>"","YES" => "YES","NO"=>"NO"],
            array("onchange"=>"javascript: if(this.value == 'NO'){ $('#pono').attr('disabled','disabled'); }else{ $('#pono').removeAttr('disabled'); }"));
        $form->addElement(	'text', 'pono', 'PO#', ['id'=>'pono','disabled'=>'disabled']);
        $form->addElement('select', 'assignee', 'First approver', $managerList);
        $form->addRule('po', 'Required', 'required');
        function checkRef($fields){
            if($fields['po']=='YES' && empty($fields['pono']))
                return array('pono'=>'Make sure to set the PO# if related PO exists');
            return TRUE;
        };
        $form->addFormRule('checkRef');
        $dtask = <<<EOF

[]Description:

[]Comment:



EOF;
        $form->addRule('bu_id', 'This is required', 'required');
        $form->setDefaults(
            [
                'task' => nl2br($dtask),
                'bu_id' => $user->getBUID(),
                'assignee' => $user->getID(),
                'cur' => 'CNY',
            ]
        );
        $form->addRule('amt', 'Required', 'required');
        $form->addRule('bu_id', 'Required', 'required');
	    $form->addRule('buyer_dpt_id', 'Required', 'required');
	    $form->addRule('type_id', 'Required', 'required');
        $form->addRule('cu_ocur', 'Required', 'required');
        $form->addRule('dt_from', 'Required', 'required');
        $form->addRule('amt', 'Field is numeric', 'numeric');

        break;
    case 'seq.remotework.approval':
    case 'seq.remotework.removal':
        $form->removeElement('task');
        $form->_flagSubmitted = true;
        break;
    case 'payment.request':
        $DEFAULT_TITLE .= ' - payment request';
        // Form
        $form->addElement('select', 'bu_id', 'BU', tldLocation::getLocationList('smartyOptions'));
        $grpList = array_merge(tldGroup::getManagerGroups(), ['seq_investmentbudget.request.level.1']);
        $managerList = tldGroup::getUserListByMultipleGroup($grpList, null, ['smartyOptions' => true]);
        $form->addElement('select', 'assignee', 'First approver', $managerList);
        $year = date('Y');
        $dtask = <<<EOF


[]Required date:
[]Supplier code:
[]Supplier name:
[]Benefit Bank A/C:
[]Description:
[]Cost center:
[]Invoice No.:
[]Amount:

[]Comment:

EOF;

        $form->setDefaults(
            [
                'task' => nl2br($dtask),
                'bu_id' => $user->getBUID(),
                'assignee' => $user->getID(),
            ]
        );
        $form->addRule('assignee', 'Required', 'required');
        $form->addRule('bu_id', 'Required', 'required');
    break;
    case 'payment_request_asi.request':
        $DEFAULT_TITLE .= ' - payment ASIA request';
        $erp = tldLocation::getERPByID($user->getBUID());
        $erpObj = new tldBaanERP($erp);
        $dptList = tldDepartment::getListAsDepartmentDepartment();
        $buid = ['' => ''] + tldLocation::getLocationList('smartyOptions');
        $cursList = tldList::optionsByListNameAsListItemListItem('list.common.currency');
        $typeList = ['' => ''] + tldList::optionsByListNameAsListItemListItem('list.finance.payment.caty','id');
        $grpList = array_merge(tldGroup::getManagerGroups(), ['seq_investmentbudget.request.level.1']);
        $managerList = tldGroup::getUserListByMultipleGroup($grpList, null, ['smartyOptions' => true]);
        $supplier = new Supplier();
        $supplierList = $supplier->getListSuppliersCode();
        $form->addElement('header', 'title', 'Payment request form');
        $form->addElement('hidden', 'module', 'SEQ');
        $form->addElement('text', 'dt_from', 'Required date:', ["class" => "datepicker"]);
        $form->addElement('text', 'suno', 'Supplier code:');
        $form->addElement('header', 'title', 'If it is one time supplier, please provide supplier name and bank information including Bank Full Name/Bank account/Bank Address/Swift code in comment');
        $form->addElement('text', 'sup_name', 'Supplier name(CH):', ['size' => 40]);
        $form->addElement('text', 'bank', 'Bank name:', ['size' => 40]);
        $form->addElement('text', 'bc', 'Bank account:', ['size' => 40]);
        $form->addElement('select', 'bu_id', 'Cost center', $buid, ['id' => 'buid']);
        $form->addElement('select', 'buyer_dpt_id', 'Department', ["" => ""] + $dptList);
        $form->addElement('text', 'amt', 'Payment Amount', ['size' => 40]);
        $form->addElement('select', 'cur', 'Currency', ['' => ''] + $cursList);
        $form->addElement('select', 'assignee', 'First approver', $managerList);
        $form->addRule('po', 'Required', 'required');
        function checkRef($fields) {
            return $fields['po'] === 'YES' && empty($fields['pono']) ? ['pono' => 'Make sure to set the PO# if related PO exists'] : true;
        };
        $form->addFormRule('checkRef');
        $dtask = <<<EOF

[]Description:

[]Comment:

EOF;
        $form->addRule('bu_id', 'This is required', 'required');
        $form->setDefaults(
            [
                'task' => nl2br($dtask),
                'bu_id' => $user->getBUID(),
                'assignee' => $user->getID(),
                'cur' => 'CNY',
            ]
        );
        $form->addRule('amt', 'Required', 'required');
        $form->addRule('bu_id', 'Required', 'required');
        $form->addRule('buyer_dpt_id', 'Required', 'required');
        $form->addRule('dt_from', 'Required', 'required');
        $form->addRule('amt', 'Field is numeric', 'numeric');
        break;
    case 'prepayment.request':
        $DEFAULT_TITLE .= ' - prepayment request';
        // Form
        $form->addElement('select', 'bu_id', 'BU', tldLocation::getLocationList('smartyOptions'));
        $grpList = array_merge(tldGroup::getManagerGroups(), ['seq_investmentbudget.request.level.1']);
        $managerList = tldGroup::getUserListByMultipleGroup($grpList, null, ['smartyOptions' => true]);
        $form->addElement('select', 'assignee', 'First approver', $managerList);
        $year = date('Y');
        $dtask = <<<EOF


[]Required date:
[]Supplier code:
[]Supplier name:
[]Benefit Bank A/C:
[]Description:
[]Cost center:
[]Invoice No.:
[]Amount:

[]Comment:

EOF;

        $form->setDefaults(
            [
                'task' => nl2br($dtask),
                'bu_id' => $user->getBUID(),
                'assignee' => $user->getID(),
            ]
        );
        $form->addRule('assignee', 'Required', 'required');
        $form->addRule('bu_id', 'Required', 'required');
        break;
    case 'ap.inv.buyer.approval':
        // Update the task description
        $p['task'] = $_SESSION['seq_body'];
        $form->setDefaults(['task' => $p['task']]);
        break;
    case 'prepayment_request_sage.request':
        $DEFAULT_TITLE .= ' - Prepayment Request Sage';
        $dtask = <<<EOF
Request prepayment

Document required : vendor ID and Name, Invoice or proforma, copy of PO
EOF;
        $form->setDefaults([
            'task' => nl2br($dtask),
            'bu_id' => $user->getBUID(),
        ]);
        break;
    case 'assets.disposal':
        $DEFAULT_TITLE .= ' - Asset Disposal';
        $dtask = <<<EOF
Budgeted: Y/N

Budget Year: 2026

Asset Description:

Asset Number:

Asset Gross value:

Asset Net value:
EOF;
        $form->setDefaults([
            'task' => nl2br($dtask),
            'bu_id' => $user->getBUID(),
        ]);
        break;
    case 'vendor_creation_request_sage.request':
        $DEFAULT_TITLE .= ' - Vendor Creation Request Sage';
        $dtask = <<<EOF
New Vendor Request : New Vendor Form (complete and signed), banking details, business registration document e.g. KBIS, confirmation of trading currency
EOF;
        $form->setDefaults([
            'task' => nl2br($dtask),
            'bu_id' => $user->getBUID(),
        ]);
        break;
    case 'mfg.pur.part.cost_roll_request':
        $form->addElement('select', 'bu_id', 'BU',
            tldLocation::getLocationList('smartyOptions')
        );
        $form->addElement('select', 'assignee', 'First approver',
            tldDirectory::getUserlist('smartyOptions')
        );
        $dtask = <<<EOF
Request for Cost Roll on Existing Part

Part Number:
Part description:
Previous Standard Cost:
Currency:
New Purchase Price:
U/M:
Reason for update:

EOF;
        $form->setDefaults(
            [
                'task' => nl2br($dtask),
                'bu_id' => $user->getBUID(),
                'assignee' => $user->getID(),
            ]
        );
        $form->addRule('assignee', 'Required', 'required');
        $form->addRule('bu_id', 'Required', 'required');
        break;
    case 'investmentbudget.request':
    case 'investmentbudget.request.factory':
        $DEFAULT_TITLE .= ' - Investment Budget Request Sequence';
        // Warning / help message
        $DEFAULT_ERROR[] = 'WARNING: SEQ workflow -> Initiator / COO / CEO / CFO';
        $DEFAULT_ERROR[] = 'To be transferred by CFO to Group CEO if:';
        $DEFAULT_ERROR[] = '- Capex > 25 kUSD';
        $DEFAULT_ERROR[] = '- OR Capex Not budgeted AND Capex > 10 kUSD';
        // Form
        $form->addElement('select', 'bu_id', 'BU', tldLocation::getLocationList('smartyOptions'));
        $grpList = array_merge(tldGroup::getManagerGroups(), ['seq_investmentbudget.request.level.1']);
        $managerList = tldGroup::getUserListByMultipleGroup($grpList, null, ['smartyOptions' => true]);
        $form->addElement('select', 'assignee', 'First approver', $managerList);
        $form->addElement('select', 'mis_or_not', 'Is this sequence IT(MIS) related?', ['' => '', 'Y' => 'Y', 'N' => 'N']);
        $year = date('Y');
        $dtask = <<<EOF
Budgeted: Y/N
Budget Year: $year
Budget Project Number:
Budget Currency: [USD]
Budgeted amount: XXX
Residual amount on budget project : XXX
Quote Currency: [USD]
Quoted amount: YYY"
EOF;

        $save = '
Explanation:

[]Quick Description: X

[]Nature:
    [] Security
    [] Quality
    [] Replacement
    [] Productivity & ROI
    [] Capacity & ROI

[]Comment:';
        $form->setDefaults(
            [
                'task' => nl2br($dtask),
                'bu_id' => $user->getBUID(),
                'assignee' => $user->getID(),
                'mis_or_not' => $mis_or_not,
            ]
        );
        $form->addRule('assignee', 'Required', 'required');
        $form->addRule('bu_id', 'Required', 'required');
        $form->addRule('mis_or_not', 'Required', 'required');
        break;
    case 'nonproductionpurchase.request':
        $DEFAULT_TITLE .= ' - Non-production purchase request';
        // Form
        $form->addElement('select', 'bu_id', 'BU', tldLocation::getLocationList('smartyOptions'));
        $grpList = array_merge(tldGroup::getManagerGroups(), ['seq_investmentbudget.request.level.1']);
        $managerList = tldGroup::getUserListByMultipleGroup($grpList, null, ['smartyOptions' => true]);
        $form->addElement('select', 'assignee', 'First approver', $managerList);
        $year = date('Y');
        $dtask = <<<EOF

[]Proposed quote amount: X (cur)

Explanation:

[]Quick Description: X


[]Comment:

EOF;
        $form->setDefaults(
            [
                'task' => nl2br($dtask),
                'bu_id' => $user->getBUID(),
                'assignee' => $user->getID(),
            ]
        );
        $form->addRule('assignee', 'Required', 'required');
        $form->addRule('bu_id', 'Required', 'required');
        break;
    case 'mfg.pur.slow_moving_inventory':
        $DEFAULT_TITLE .= ' - Slow moving inventory sequence';
        $dtask = <<<EOF
Pls answer the following questions:

Part number :
Description :
PUMP :
Qty in stock :
EOF;
        $form->setDefaults(['task' => nl2br($dtask)]);
        $buid = ['' => ''] + tldLocation::getLocationList('smartyOptions');
        $form->addElement('select', 'bu_id', 'BU', $buid);
        $form->addRule('bu_id', 'Required', 'required');
        break;
    case 'sales.catalogue.datasheet.process':
        $body = 'This page has been migrated and should not be displayed anymore.';
        break;
    case 'sales.survey.campaign.approval':
        $DEFAULT_TITLE .= ' - Campaign Survey Approval';

        if (!isset($survey) && !isset($campaign)) {
            $DEFAULT_ERROR[] = 'No campaign or no survey submitted.';
            $body .= $form->toHTML();
            return;
        }

        global $kernel;

        $form->setAttribute('method', 'GET');
        $form->_submitValues = $_GET;
        $form->_flagSubmitted = true;
        $path = $kernel->getContainer()->get('router')->generate('survey_published_index', [
            'id' => $survey,
            'campaignId' => $campaign,
        ], \Symfony\Component\Routing\Router::ABSOLUTE_URL);
        $form->setDefaults([
            'task' => sprintf('Campaign #%s has to be validated. <a href="%s" target="_blank">Please click here to see it.</a>', $campaign, $path),
        ]);
        break;
    case 'sales.extranetuser.approval':
        $DEFAULT_TITLE .= ' - Extranet Application Approval';
        $DEFAULT_ERROR[] = 'WARNING: Make sure the user was not already created in directory or another new user sequence was created already...';

        if (!isset($extranetUser) || (new extranetUser($extranetUser))->isEmpty()) {
            $DEFAULT_ERROR[] = 'No extranet user submitted or invalid extranet user';
            $body .= $form->toHTML();
            return;
        }

        global $kernel;

        $form->setAttribute('method', 'GET');
        $form->_submitValues = $_GET;
        $form->_flagSubmitted = true;
        $path = $kernel->getContainer()->get('router')->generate('legacy_sales', [
            'm' => ['extranet_user', 'view'],
            'id' => $extranetUser,
        ], \Symfony\Component\Routing\Router::ABSOLUTE_URL);
        $form->setDefaults([
            'task' => sprintf('<a href="%s" target="_blank">Extranet User #%s</a> has to be validated', $path, $extranetUser),
            'header' => 'TLD Partners registration form',
        ]);

        break;
    case 'hr.user.add':
        // check if new people id sent
        if (empty($id) || !is_numeric($id)) {
            $DEFAULT_ERROR[] = 'ERROR: Parameter sent empty or invalid';
            return;
        }
        // check if new people if valid
        $people = new tldUser((int)$id);
        if ($people->isEmpty()) {
            $DEFAULT_ERROR[] = "ERROR: People#$id empty or invalid";
            return;
        }

        // Common Fields description
        $fieldsDescription = [
            'due_date' => 'When',
            'reports_to' => 'Supervisor',
            'div_id' => 'Division',
            'bu_id' => 'Business Unit',
            'dpt_id' => 'Department',
            'fct_id' => 'TLD function',
            'lastname' => 'Lastname',
            'firstname' => 'Firstname',
            'title' => 'Title',
            'lang' => 'Language',
            'contract_type' => 'Contract Type',
            'coefficient' => 'Coefficient',
        ];

        // Common Listing config
        $yesNoList = ['' => '', 'Yes' => 'Yes', 'No' => 'No'];
        $yesNoSimList = ['' => '', 'Yes with SIM card' => 'Yes with SIM card', 'Yes without SIM card' => 'Yes without SIM card', 'No' => 'No'];
        $divisionList = tldRegion::getListAsIdDivision();
        $buList = tldLocation::getBuListAsIdBU();
        $departmentList = tldDepartment::getListAsIdDepartment();
        $functionList = tldFunction::getListAsIdDescription();
        $userList = tldDirectory::getUserList('smartyOptions');
        $userListForCopy = ['' => '', '** NO USER TO COPY **' => '** NO USER TO COPY **'] + array_column(tldDirectory::getUserlistByBusinessUnitPosition($people->getBUID(), $people->itsDetails['fct_id']), 'fullname', 'fullname');
        $ggHR = new tldGroup('gg_HR');
        $hrList = ['' => ''] + $ggHR->getUserlist(['smartyOptions' => true]);

        // Common form config
        $form->removeElement('task', true);
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'header', $people->getFullname() . ' arrival');
        $form->addElement('date', 'due_date', 'When?', ['format' => 'Ymd',
            'addEmptyOption' => true, 'minYear' => date('Y'), 'maxYear' => date('Y') + 2]);
        $form->addElement('select', 'assignee', 'HR Manager', $hrList);
        $form->addElement('header', 'header', 'User access & equipment');
        $form->setDefaults([
            'due_date'=> $people->itsDetails['enable_at']
        ]);

        // Form following TLD function

        switch ($people->itsDetails['fct_id']) {
            // SHOP FLOOR EMPLOYEE
            case 49:
                // Fields
                $shopEmployeeFields = [
                    'acct_network' => 'Network account',
                    'acct_email' => 'Email account',
                    'acct_intranet' => 'Intranet account',
                    'acct_erp' => 'ERP account',
                    'acct_erp_employee_wtt' => 'ERP Working Time',
                    'phone_desk' => 'Desk phone',
                    'phone_cell' => 'Cell phone',
                    'computer' => 'Computer',
                    'assignment_info' => 'If tablet reassignment, TLD PN:',
                    'profileToCopy' => 'User to copy',
                ];
                $fieldsDescription = $fieldsDescription + $shopEmployeeFields;
                // List
                // list of time keeping table of the ERP BU
                include_once 'erp.inc.php';
                $erp = tldLocation::getERPByID((int)$people->itsDetails['bu_id']);
                $wtt = new tldWTT($erp);
                $wttRawList = $wtt->getList();
                $wttList = ['' => ''];
                foreach ($wttRawList as $var) {
                    $wttList[$var['t_dsca'] . " ({$var['t_cwtt']})"] = $var['t_dsca'] . " ({$var['t_cwtt']})";
                }
                $computerTypeList = ['' => '', 'None' => 'None', 'Tablet' => 'New tablet', 'Tablet reassignment' => 'Tablet reassignment'];
                // defaults
                $defaults = [
                    'pio_operator' => 'Yes',
                    'acct_network' => 'No',
                    'acct_intranet' => 'No',
                    'acct_email' => 'No',
                    'acct_erp' => 'No',
                    'phone_desk' => 'No',
                    'phone_cell' => 'No',
                    'computer' => 'Tablet',
                ];
                // Form
                $form->addElement('header', 'PIO', 'PIO');
                $form->addElement('select', 'pio_operator', 'Access rights = Operator ?', $yesNoList);

                // Network
                $form->addElement('header', 'header', 'NETWORK & MISC related');
                $form->addElement('select', 'acct_network', 'Network account', $yesNoList);
                $form->addElement('select', 'acct_email', 'Email account', $yesNoList);
                $form->addElement('select', 'profileToCopy', 'User to copy', $userListForCopy);
                // Web
                $form->addElement('header', 'header', 'WEB related');
                $form->addElement('select', 'acct_intranet', 'Intranet account', $yesNoList);
                // LN
                $form->addElement('header', 'header', 'ERP (LN) related');
                $form->addElement('select', 'acct_erp', 'ERP account', $yesNoList);
                $form->addElement('select', 'acct_erp_employee_wtt', 'Assign working time table', $wttList);
                // Equipment
                $form->addElement('header', 'header', 'Equipment related');
                $form->addElement('select', 'phone_desk', 'Desk phone', $yesNoList);
                $form->addElement('select', 'phone_cell', 'Cell phone', $yesNoList);
                $form->addElement('select', 'computer', 'Computer', $computerTypeList);
                $form->addElement('text', 'assignment_info', 'Reassignment case,<br>Please advise TLD PN');

                if (empty($wttRawList)) {
                    unset($shopEmployeeFields['acct_erp_employee_wtt']);
                }

                // required
                $requiredList = array_keys($shopEmployeeFields);
                unset($requiredList[array_search('assignment_info', $requiredList)]);
                break;
            default:
                $region = new tldRegion($people->getRegionID());
                $divisionId = (int) $region->getDivisionId()['id'];
                $requiredList = [];
                if (2 === $divisionId) {
                    // Web
                    $form->addElement('header', 'header', 'WEB related');
                    $form->addElement('select', 'acct_intranet', 'Intranet account', $yesNoList);
                    break;
                }
                // fields
                $defaultFunctionFields = [
                    'acct_network' => 'Network account',
                    'acct_intranet' => 'Intranet account',
                    'acct_email' => 'Email account',
                    'acct_erp' => 'ERP account',
                    'acct_equote' => 'eQuote(for ASM)',
                    'acct_vpn' => 'VPN',
                    'acct_pdm' => 'PDMWorks(for Engineering Dept)',
                    'phone_desk' => 'Desk phone',
                    'phone_cell' => 'Cell phone',
                    'computer' => 'Computer',
                    'acct_wms' => 'WMS access',
                    'profileToCopy' => 'User to copy',
                ];
                $fieldsDescription = $fieldsDescription + $defaultFunctionFields;
                // listing
                $computerTypeList = ['' => '', 'None' => 'None', 'Laptop' => 'Laptop', 'Thin Client' => 'Thin Client', 'Workstation' => 'Workstation'];
                // Form
                // Network
                $form->addElement('header', 'header', 'NETWORK & MISC related');
                $form->addElement('select', 'acct_network', 'Network account', $yesNoList);
                $form->addElement('select', 'acct_email', 'Email account', $yesNoList);
                $form->addElement('select', 'acct_vpn', 'VPN', $yesNoList);
                $form->addElement('select', 'acct_pdm', 'PDMWorks(for Engineering Dept)', $yesNoList);
                $form->addElement('select', 'profileToCopy', 'User to copy', $userListForCopy);
                // Web
                $form->addElement('header', 'header', 'WEB related');
                $form->addElement('select', 'acct_intranet', 'Intranet account', $yesNoList);
                $form->addElement('select', 'acct_equote', 'eQuote(for ASM)', $yesNoList);
                $form->addElement('select', 'acct_wms', 'WMS access', $yesNoList);
                // ERP
                $form->addElement('header', 'header', 'ERP (LN) related');
                $form->addElement('select', 'acct_erp', 'ERP account', $yesNoList);
                // Equipment
                $form->addElement('header', 'header', 'Equipment related');
                $form->addElement('select', 'phone_desk', 'Desk phone', $yesNoList);
                $form->addElement('select', 'phone_cell', 'Cell phone', $yesNoSimList);
                $form->addElement('select', 'computer', 'Computer', $computerTypeList);
                // required
                $requiredList = array_keys($defaultFunctionFields);
                break;
        }

        $form->setDefaults($defaults);
        $requiredList = array_merge($requiredList, ['due_date', 'assignee']);
        foreach ($requiredList as $required) {
            $form->addRule($required, 'Required', 'required');
        }
        break;
    case 'hr.user.delete':
        $DEFAULT_TITLE .= ' - HR leaving user Sequence';
        $uid = $_REQUEST['uid'];
        if (empty($uid) || !is_numeric($uid)) {
            $DEFAULT_ERROR[] = 'ERROR: Parameter sent empty or invalid, can not retrieve user';
            break;
        }
        $delUser = new tldUser($uid);
        if ($delUser->isEmpty()) {
            $DEFAULT_ERROR[] = "ERROR: User#$uid not found";
            break;
        }
        // Fields
        $fieldsDescription = [
            'due_date' => 'When',
            'email_autoreply' => 'Auto-reply',
            'email_message' => 'Auto-reply message',
            'email_due_date' => 'Auto-reply due date',
            'email_pst' => 'PST action',
            'doc_archive' => 'Archive documents',
        ];
        // Listing
        $yesNoList = ['' => '', 'Yes' => 'Yes', 'No' => 'No'];
        $yearsList = array_map(function ($value) {
            return sprintf('%d years %s', $value, $value === 3 ? '(recommended)' : '');
        }, range(1, 5));

        $yearsList = ['no action' => 'no action'] + array_combine($yearsList, $yearsList);
        $userList = tldDirectory::getUserList('smartyOptions');
        $ggHR = new tldGroup('gg_HR');
        $hrList = ['' => ''] + $ggHR->getUserlist(['smartyOptions' => true]);

        $leavingUser = new tldUser($uid);
        $history = array_unique(array_column($leavingUser->getSequencesHistory(), 'name'));
        $region = new tldRegion($leavingUser->getRegionID());
        $divisionId = (int) $region->getDivisionId()['id'];

        // Form
        $form->addElement('header', 'header', 'Pls answer the following questions...');
        $form->addElement('hidden', 'uid', $uid);
        $form->addElement('date', 'due_date', 'When?', ['format' => 'Ymd', 'addEmptyOption' => true, 'minYear' => date('Y'), 'maxYear' => date('Y') + 2]);
        if (2 === $divisionId || $history === ['hr.user.shopfloor_light.new']
            || $history === ['hr.user.shopfloor_light.new', 'hr.user.shopfloor_light.delete']) {
            $form->addElement('select', 'assignee', 'HR Manager', $hrList);
        }
        $form->addElement('static', 'material', '', 'Please remember to return any IT equipments to your local MIS manager');

        // Email
        $form->addElement('header', 'header', 'Email');
        $form->addElement('select', 'email_autoreply', 'Setup auto-reply?', $yesNoList);
        $form->addElement('static', 'email_message_info', '', 'Please update the following Standard msg you are requesting for Auto-reply (see DMS #<a href="https://dms.tld-group.com/index.php?m[0]=view&id=1551" target="_blank">1551</a>)');
        $form->addElement('textarea', 'email_message', 'If yes, auto-reply message', ['wrap' => 'VIRTUAL', 'cols' => '80', 'rows' => '5']);
        $form->addElement('date', 'email_due_date', 'Auto-reply until...', ['format' => 'Ymd', 'addEmptyOption' => true, 'minYear' => date('Y'), 'maxYear' => date('Y') + 2, 'disabled' => 'disabled']);

        // Archive
        $form->addElement('header', 'header', 'Archive');
        $form->addElement('static', 'email_pst_duration_info', '', 'Please make sure spam emails and intranet notification are excluded from the PST (Spams have no added value and Intranet records are already stored).');
        $form->addElement('select', 'email_pst', 'Action for Outlook file&nbsp;(PST)?', $yearsList);
        $form->addElement('static', 'doc_archive_info', '', 'Archiving data has a cost, please take it into consideration choosing the number of years if any.');
        $form->addElement('select', 'doc_archive', 'Archive documents?', $yearsList);

        // Erp Admin
        $form->addElement('header', 'header', 'Erp Admin');
        $form->addElement('select', 'reassign_task_ln', 'Infor LN task to be reassigned?', $yesNoList,
            array("onchange"=>"javascript: if(this.value == 'Yes'){ $('#reassign_task_ln_to').removeAttr('disabled'); }else{ $('#reassign_task_ln_to').attr('disabled','disabled'); }"));
        $form->addElement('select', 'reassign_task_ln_to', 'LN task to be reassigned for?', ['' => ''] + $userList, ['id'=>'reassign_task_ln_to','disabled'=>'disabled']);

        // Rules
        $form->addGroupRule('due_date', 'Required', 'required');
        $requiredFields = ['assignee', 'email_autoreply', 'email_pst', 'doc_archive', 'reassign_task_ln'];
        foreach ($requiredFields as $field) {
            $form->addRule($field, 'Required', 'required');
        }
        function checkRef($fields){
            if($fields['reassign_task_ln']=='Yes' && empty($fields['reassign_task_ln_to']))
                return array('reassign_task_ln_to'=>'Make sure to set the user to be reassigned for the tasks');
            return TRUE;
        };
        $form->addFormRule('checkRef');

        // Custom the form
        $taskd = <<<EOF
Any information you may want to add?
EOF;
        $supervisor = new tldUser($delUser->getSupervisor());
        $autoreplyMessage = <<<EOF
M/Ms {$delUser->getFullname()} is no longer working for TLD, for any request please contact M/Ms {$supervisor->getFullname()}
Thanks and Best Regards
EOF;

        if ($form->isSubmitted() && $form->validate() && $form->getElementValue('email_autoreply')[0] === 'No') {
            unset($fieldsDescription['email_message']);
        }

        $form->setDefaults([
            'due_date' => date('Y-m-d'),
            'task' => $taskd,
            'email_message' => $autoreplyMessage,
        ]);
        break;
    case 'hr.user.update':
        $DEFAULT_TITLE .= ' - HR change user Sequence';
        $uid = $_REQUEST['uid'];
        if (empty($uid) || !is_numeric($uid)) {
            $DEFAULT_ERROR[] = 'ERROR: Parameter sent empty or invalid, can not retrieve user';
            break;
        }
        $changeUser = new tldUser($uid);
        if ($changeUser->isEmpty()) {
            $DEFAULT_ERROR[] = "ERROR: User#$uid not found";
            break;
        }
        // Fields
        $fieldsDescription = [
            'due_date' => 'When',
            'div_id' => 'Division',
            'bu_id' => 'Business Unit',
            'dpt_id' => 'Department',
            'fct_id' => 'TLD function',
            'title' => 'Title',
            'reports_to' => 'Supervisor',
        ];
        // Listing
        $computerTypeList = ['' => '', 'None' => 'None', 'Laptop' => 'Laptop', 'Thin Client' => 'Thin Client', 'Workstation' => 'Workstation'];
        $yesNoList = ['' => '', 'Yes' => 'Yes', 'No' => 'No'];
        $divisionList = tldRegion::getListAsIdDivision();
        $buList = tldLocation::getBuListAsIdBU();
        $departmentList = tldDepartment::getListAsIdDepartment();
        $functionList = tldFunction::getListAsIdDescription();
        $userList = tldDirectory::getUserList('smartyOptions');
        $ggHR = new tldGroup('gg_HR');
        $hrList = ['' => ''] + $ggHR->getUserlist(['smartyOptions' => true]);
        // Form
        $form->removeElement('task', true);
        $form->addElement('header', 'header', 'Pls answer the following questions...');
        $form->addElement('hidden', 'uid', $uid);
        $form->addElement('date', 'due_date', 'When?', ['format' => 'Ymd',
            'addEmptyOption' => true, 'minYear' => date('Y'), 'maxYear' => date('Y') + 2]);
        $form->addElement('select', 'assignee', 'HR Manager', $hrList);
        // user info
        $form->addElement('header', 'header', 'User information (if applicable)');
        $form->addElement('select', 'div_id', 'Division', ['' => ''] + $divisionList);
        $form->addElement('static', 'actual_div', 'Actual', $changeUser->itsDetails['division']);
        $form->addElement('select', 'bu_id', 'Business Unit', ['' => ''] + $buList);
        $form->addElement('static', 'actual_bu', 'Actual', $changeUser->itsDetails['location']);
        $form->addElement('select', 'dpt_id', 'Department', ['' => ''] + $departmentList);
        $form->addElement('static', 'actual_dpt', 'Actual', $changeUser->itsDetails['department']);
        $form->addElement('select', 'fct_id', 'TLD System Function', ['' => ''] + $functionList);
        $form->addElement('static', 'actual_fct', 'Actual', $changeUser->itsDetails['tld_function']);
        $form->addElement('text', 'title', 'Job title');
        $form->addElement('static', 'actual_title', 'Actual', $changeUser->itsDetails['title']);
        $form->addElement('select', 'reports_to', 'Supervisor', ['' => ''] + $userList);
        $form->addElement('static', 'actual_title', 'Actual', $changeUser->itsDetails['reports_to_fullname']);
        // Acces & equipment
        $form->addElement('header', 'header', 'User access & IT equipments');
        $form->addElement('static', 'note', 'NOTE:', '<span style="color: red;">Please provide an existing user name from which we can copy profile permissions (if applicable)</span>');
        $form->addElement('textarea', 'details', 'Request change (if any)',
            ['wrap' => 'VIRTUAL', 'cols' => '80', 'rows' => '11']);
        // Rules
        $form->addGroupRule('due_date', 'Required', 'required');
        $form->addRule('assignee', 'Required', 'required');
        // Defaults
        $dtask = <<<EOF
Network account: 
Intranet account: 
Email account: 
ERP account: 
Equote(for ASM): 
VPN: 
PDMWorks(for Engineering Dept): 
Landline: 
Cell phone: 
Computer: 
WMS access:
EOF;
        $form->setDefaults([
            'due_date' => date('Y-m-d'),
            'details' => nl2br($dtask),
        ]);
        break;
    case 'eng.newpartnbrevision':
    case 'eng.newpartnb':
        $DEFAULT_TITLE .= ' - New Part Number';
        $dtask = <<<EOF
CRITIQUE :								YES / NO 
PN :									######### _ A
Description :								############
Reference (WO/ER/PROJECT/PDP/OPERATION NUMBER) :		        ############
Qty per unit :								############
Item unit in BAAN (to be validated with purchasing dptmt) :		############


1)	RELEASED :			                MANUFACTURED / PURCHASED
2)	APPLICATION :					STANDARD / OPTION
3)	REQUIRED P&I ECQ UPDATE :			YES / NO 
4)	REQUIRED P&I PCQ UPDATE :			YES / NO 
5)	REQUIRED P&I QCQ UPDATE :			YES / NO
6)	EXPECTED PROTOTYPE COST			        ###### EUR / USD / RMB
7)	EXPECTED COST FOR SERIAL PRODUCTION :	        ###### EUR / USD / RMB
8)	PIPO BOM OR PN NUMBER :				###################
9)	REQUIRED DELIVERY INSPECTION :			YES
10)	TECHNICAL EXPECTATION FOR FAQ :		        ###################
EOF;
        if ($m[2] === 'eng.newpartnbrevision') {
            $DEFAULT_TITLE .= ' Revision';
            $dtask = <<<EOF
CRITIQUE : YES / NO
PN : ######### _--> Rev from ## to ##
Description : ############
Product family : ############
Qty per unit : ############
Item unit in BAAN (to be validated with purchasing dptmt) : ############

1) RELEASED : MANUFACTURED / PURCHASED
2) APPLICATION : STANDARD / OPTION
3) REQUIRED P&I ECQ UPDATE : YES / NO
4) REQUIRED P&I PCQ UPDATE : YES / NO
5) REQUIRED P&I QCQ UPDATE : YES / NO
6) EXPECTED PROTOTYPE COST ###### EUR / USD / RMB
7) EXPECTED COST FOR SERIAL PRODUCTION : ###### EUR / USD / RMB
10) TECHNICAL EXPECTATION FOR FAQ : #############

BOM revision (Manufactured Item) :

> Modified quantity
- P/N qty "X" --> "x" ALREADY OUTBOUNDED or TO BE OUTBOUNDED ON WO "XXXXXX" / PIO OPERATION : "x"

> Parts / BOMs to be added
- P/N qty "X" --> "x" ALREADY OUTBOUNDED or TO BE OUTBOUNDED ON WO "XXXXXX" / PIO OPERATION : "x"

> Parts / BOMs to be removed
- P/N --> TO BE DISCARDED (riblon) or PARTS TO BE KEPT FOR OTHER APPLICATION

Part revision (Purchased Item) :

To be applied on :
- GT tractors : YES / NO
- Tractors in production : YES / NO
- Inventory : YES / NO , if YES please specify the qty
- On Order : YES / NO
EOF;
        }
        $buid = ['' => ''] + tldLocation::getERPList('smartyOptions');
        $form->addElement('checkbox', "pmoc['P']", 'P', 'select');
        $form->addElement('checkbox', "pmoc['M']", 'M', 'select');
        $form->addElement('checkbox', "pmoc['O']", 'O', 'select');
        $form->addElement('checkbox', "pmoc['C']", 'C', 'select');
        $form->addElement('select', 'erp', 'BU', $buid);
        $form->addElement('text', 'pn', 'Part#');
        if (!($module === '')) {
            $form->addElement('hidden', 'modfrom', $module);
        }
        if ($id) {
            $form->addElement('hidden', 'id', $id);
        }
        $form->setDefaults(
            [
                'task' => nl2br($dtask),
                'due_date' => ['Y' => date('Y'), 'm' => date('m'), 'd' => date('d')],
                'pn' => $_GET['pn'],
                'erp' => $_GET['bu_id'],
            ]
        );
        $form->addElement('checkbox', 'faq', 'Open a FAQ?', '');

        $form->addRule('erp', 'This is required', 'required');
        $form->addRule('pn', 'This is required', 'required');
        break;

    case 'acct.invadjust':
        $DEFAULT_TITLE .= ' - Inventory Adjustment Request';
        $dtask = <<<EOF
Please Insert comment here...
EOF;
        // TODO : replace by ION call when BDE created
        $reason_code = [
            '(---) UNKNOWN' => 'UNKNOWN',
            '004' =>  'R&D testing parts',
            '018' =>  'Sample to inventory',
            '019' =>  'SC Sample to inventory',
            'AFPT' =>  'Inv. adj. for production tool',
            'AFS' =>  'AFTERS SALES SERVICE TOOL',
            'B4W' =>  'Anonymous WIP BaaN IV',
            'BAC' =>  'Backflush Item Adjustment',
            'BATIM' =>  'BATIMENT AMELIORATION',
            'COLOR' =>  'PN COLOR CHANGE',
            'CON' =>  'MAUVAIS FACTEUR CONVERSION',
            'CWO' =>  'Inv. adj. for close WO',
            'EOB' =>  'Inv. adj. Error On Outbound',
            'FGA' =>  'Finish Goods Adjustment',
            'KBN' =>  'Kanban',
            'LIN' =>  'Transfer of back flush items',
            'LIQ' =>  'Inv Adj for OIL, FUEL...',
            'LOS' =>  'Inv. adj. for lost/found',
            'MIG' =>  'Inventory Migration',
            'NCR' =>  'NCR Replacement',
            'NCV' =>  'Item used for version adjustme',
            'OUR' =>  'Stock used for of our own',
            'PNCHAN' =>  'CHANGE PN',
            'PNO' =>  'production not outbound',
            'R&D' => 'Use for R&D',
            'RAC' =>  'Racks',
            'REF' =>  'Item Recoding',
            'REFURB' =>  'REFURB PARTS',
            'SCR' =>  'Inv. adj. for scrap',
            'SCR' =>  'RE SCRAP REVISION CHANGE',
            'SIB' =>  'Obsolete Scrap with audit',
            'SMV' =>  'SLOW MOVING',
            'SOB' =>  'Obsolete Scrap',
            'UNI' =>  'Inv. adj. corr. unit mesure',
            'VMP' =>  'Transfer to VMP',
            'VWC' =>  'Vendor Warranty Claim',
            'WC' =>  'Warranty Claim part replaced',
        ];
        $erp = tldLocation::getERPByID($user->getBUID());
        $form->addElement('hidden', 'erp', $erp);
        $form->addElement('text', 'item', 'Item #');
        $form->addElement('text', 'itemdesc', 'Item description');
        $form->addElement('text', 'qty', 'Quantity');
        $form->addElement('text', 'location', 'Whse Location');
        $buid = ['' => ''] + tldLocation::getLocationList('smartyOptions');
        $form->addElement('select', 'bu_id', 'BU', $buid);
        $form->addElement('select', 'reason_code', 'Reason', $reason_code);
        $form->addRule('item', 'This is required', 'required');
        $form->addRule('itemdesc', 'This is required', 'required');
        $form->addRule('qty', 'This is required', 'required');
        $form->addRule('location', 'This is required', 'required');
        $form->addRule('bu_id', 'This is required', 'required');
        $form->setDefaults(
            [
                'task' => nl2br($dtask),
                'due_date' => [
                    'Y' => date('Y'),
                    'm' => date('m'),
                    'd' => date('d'),
                ],
            ]
        );
        break;
    case 'acct.loanpart':
        $DEFAULT_TITLE .= "\Inventory adjustment";
        $buid = ['' => ''] + tldLocation::getLocationList('smartyOptions');
        // Limit the list to 10
        for ($i = 1; $i <= 10; $i++) {
            $nb[$i] = $i;
        }
        // Get form
        $form = new HTML_QuickForm('frmNewSFR', 'post');
        $form->addElement('hidden', 'm[0]', 'seq');
        $form->addElement('hidden', 'm[1]', 'new');
        $form->addElement('hidden', 'm[2]', 'acct.loanparts');
        $form->addElement('header', 'title', 'Step 1 - Select number of parts lines');
        $form->addElement('select', 'nb_parts', 'Number of Parts Lines', $nb);
        $form->addElement('select', 'bu_id', 'BU', $buid, ['id' => 'buid']);
        $form->addRule('bu_id', 'This is required', 'required');
        $form->addElement('submit', 'btnSubmit', 'Continue');
        $form->setDefaults(['nb_parts' => 1, 'bu_id' => $user->getBUID()]);
        if (!$form->validate()) {
            $body .= $form->toHTML();

            return;
        }
        break;
    case 'acct.negativeadj':
        $DEFAULT_TITLE .= "\Inventory adjustment";
        $buid = ['' => ''] + tldLocation::getLocationList('smartyOptions');
        // Limit the list to 10
        for ($i = 1; $i <= 10; $i++) {
            $nb[$i] = $i;
        }
        // Get form
        $form = new HTML_QuickForm('frmNewSFR', 'post');
        $form->addElement('hidden', 'm[0]', 'seq');
        $form->addElement('hidden', 'm[1]', 'new');
        $form->addElement('hidden', 'm[2]', 'acct.negativeadj1');
        $form->addElement('header', 'title', 'Step 1 - Select number of parts lines');
        $form->addElement('select', 'nb_parts', 'Number of Parts Lines', $nb);
        $form->addElement('select', 'bu_id', 'BU', $buid, ['id' => 'buid']);
        $form->addRule('bu_id', 'This is required', 'required');
        $form->addElement('submit', 'btnSubmit', 'Continue');
        $form->setDefaults(['nb_parts' => 1, 'bu_id' => $user->getBUID()]);
        if (!$form->validate()) {
            $body .= $form->toHTML();

            return;
        }
        break;
    case 'acct.positiveadj':
        $DEFAULT_TITLE .= "\Inventory adjustment";
        // Limit the list to 10
        for ($i = 1; $i <= 10; $i++) {
            $nb[$i] = $i;
        }
        // Get form
        $buid = ['' => ''] + tldLocation::getLocationList('smartyOptions');
        $form = new HTML_QuickForm('frmNewSFR', 'post');
        $form->addElement('hidden', 'm[0]', 'seq');
        $form->addElement('hidden', 'm[1]', 'new');
        $form->addElement('hidden', 'm[2]', 'acct.positiveadj1');
        $form->addElement('header', 'title', 'Step 1 - Select number of parts lines');
        $form->addElement('select', 'nb_parts', 'Number of Parts Lines', $nb);
        $form->addElement('select', 'bu_id', 'BU', $buid, ['id' => 'buid']);
        $form->addRule('bu_id', 'This is required', 'required');
        $form->addElement('submit', 'btnSubmit', 'Continue');
        $form->setDefaults(['nb_parts' => 1, 'bu_id' => $user->getBUID()]);
        if (!$form->validate()) {
            $body .= $form->toHTML();

            return;
        }
        break;
    case 'acct.positiveadj1':
        $reason_code = ['Part found on the floor' => 'Part found on the floor',
            'Part return to inventory' => 'Part return to inventory',
        ];
        $DEFAULT_TITLE .= ' - Positive Inventory Adjustment Request';
        $dtask = 'Please Insert comment here...';
        $erp = tldLocation::getERPByID($bu_id);
        $form->addElement('hidden', 'erp', $erp);
        $form->addElement('hidden', 'nb_parts', $nb_parts);
        $form->addElement('hidden', 'bu_id', $bu_id, ['id' => 'buid']);
        for ($i = 1; $i <= $nb_parts; $i++) {
            $form->addElement('text', "package[$i][item]", 'Item #', ['id' => 'itemno' . $i, 'onblur' => "msgAjax('" . $i . "')"]);
            $form->addElement('text', '', 'Item DESC', ['id' => 'itemdesc' . $i, 'class' => 'nob', 'disabled' => 'readonly']);
            $form->addElement('text', '', 'Item STDcost', ['id' => 'itemstdcost' . $i, 'class' => 'nob', 'disabled' => 'readonly']);
            $form->addElement('text', "package[$i][qty]", 'Quantity');
            $form->addElement('text', "package[$i][location]", 'Whse Location');
            $form->addElement('select', "package[$i][reason_code]", 'Reason', $reason_code);
            $form->addRule('item', 'This is required', 'required');
            $form->addRule('qty', 'This is required', 'required');
            $form->addRule('location', 'This is required', 'required');
            $form->setDefaults(
                [
                    'task' => $dtask,
                    'due_date' => [
                        'Y' => date('Y'),
                        'm' => date('m'),
                        'd' => date('d'),
                    ],
                ]
            );
            $requireds = ["package[$i][item]", "package[$i][qty]", "package[$i][location]"];
            foreach ($requireds as $required) $form->addRule($required, 'Required', 'required');
            $form->addRule("package[$i][qty]", 'Should be numeric', 'numeric');
            $form->setDefaults(["package[$i][due_date]" => [
                'Y' => date('Y'),
                'm' => date('m'),
                'd' => date('d'),
            ],
            ]);
        }

        include_once 'seq_adj_ajax.tpl';
        break;
    case 'acct.loanparts':
        $DEFAULT_TITLE .= ' - Loan Parts Request';
        $dtask = 'Please Insert comment here...';
        $erp = tldLocation::getERPByID($bu_id);
        $form->addElement('hidden', 'erp', $erp);
        $form->addElement('hidden', 'nb_parts', $nb_parts);
        $form->addElement('hidden', 'bu_id', $bu_id, ['id' => 'buid']);
        for ($i = 1; $i <= $nb_parts; $i++) {
            $form->addElement('text', "package[$i][item]", 'Item #', ['id' => 'itemno' . $i, 'onblur' => "msgAjax('" . $i . "')"]);
            $form->addElement('text', '', 'Item DESC', ['id' => 'itemdesc' . $i, 'class' => 'nob', 'disabled' => 'readonly']);
            $form->addElement('text', '', 'Item STDcost', ['id' => 'itemstdcost' . $i, 'class' => 'nob', 'disabled' => 'readonly']);
            $form->addElement('text', "package[$i][qty]", 'Quantity');
            $form->addElement('text', "package[$i][location]", 'Whse Location');
            $form->addRule('item', 'This is required', 'required');
            $form->addRule('qty', 'This is required', 'required');
            $form->addRule('location', 'This is required', 'required');
            $form->setDefaults(
                [
                    'task' => $dtask,
                    'due_date' => [
                        'Y' => date('Y'),
                        'm' => date('m'),
                        'd' => date('d'),
                    ],
                ]
            );
            $requireds = ["package[$i][item]", "package[$i][qty]", "package[$i][location]"];
            foreach ($requireds as $required) $form->addRule($required, 'Required', 'required');
            $form->addRule("package[$i][qty]", 'Should be numeric', 'numeric');
            $form->setDefaults(["package[$i][due_date]" => [
                'Y' => date('Y'),
                'm' => date('m'),
                'd' => date('d'),
            ],
            ]);
        }

        include_once 'seq_adj_ajax.tpl';
        break;

    case 'acct.positiveadj.getinfo':
        $controller = new ItemInformationController();
        $response = $controller->index();
        $response->send();
        exit;
    case 'acct.negativeadj1':
        $reason_code = ['Part broken on the floor / scrapped(incl. MRB)' => 'Part broken on the floor / scrapped(incl. MRB)',
            'Part obsolete (change of index by engineering etc.)' => 'Part obsolete (change of index by engineering etc.)',
            'Inventory cleaning for transfer to VMI' => 'Inventory cleaning for transfer to VMI',
            'Parts lost on the floor (parts already outbounded to the unit then lost...)' => 'Parts lost on the floor (parts already outbounded to the unit then lost...)',
        ];
        $DEFAULT_TITLE .= ' - Negative Inventory Adjustment Request';
        $dtask = 'Please Insert comment here...';
        $erp = tldLocation::getERPByID($bu_id);
        $form->addElement('hidden', 'erp', $erp);
        $form->addElement('hidden', 'nb_parts', $nb_parts);
        $form->addElement('hidden', 'bu_id', $bu_id, ['id' => 'buid']);
        for ($i = 1; $i <= $nb_parts; $i++) {
            $form->addElement('text', "package[$i][item]", 'Item #', ['id' => 'itemno' . $i, 'onblur' => "msgAjax('" . $i . "')"]);
            $form->addElement('text', '', 'Item DESC', ['id' => 'itemdesc' . $i, 'class' => 'nob', 'disabled' => 'readonly']);
            $form->addElement('text', '', 'Item STDcost', ['id' => 'itemstdcost' . $i, 'class' => 'nob', 'disabled' => 'readonly']);
            $form->addElement('text', "package[$i][qty]", 'Quantity');
            $form->addElement('text', "package[$i][location]", 'Whse Location');
            $form->addElement('select', "package[$i][reason_code]", 'Reason', $reason_code);
            $form->addRule('item', 'This is required', 'required');
            $form->addRule('qty', 'This is required', 'required');
            $form->addRule('location', 'This is required', 'required');
            $form->setDefaults(
                [
                    'task' => $dtask,
                    'due_date' => [
                        'Y' => date('Y'),
                        'm' => date('m'),
                        'd' => date('d'),
                    ],
                ]
            );
            $requireds = ["package[$i][item]", "package[$i][qty]", "package[$i][location]"];
            foreach ($requireds as $required) $form->addRule($required, 'Required', 'required');
            $form->addRule("package[$i][qty]", 'Should be numeric', 'numeric');
            $form->setDefaults(["package[$i][due_date]" => [
                'Y' => date('Y'),
                'm' => date('m'),
                'd' => date('d'),
            ],
            ]);
        }

        include_once 'seq_adj_ajax.tpl';
        break;
    case 'acct.cyclecount':
        $DEFAULT_TITLE .= ' - Cycle count Request';
        $dtask = 'Please Insert comment here...';
        $erp = tldLocation::getERPByID($user->getBUID());
        $form->addElement('hidden', 'erp', $erp);
        $buid = ['' => ''] + tldLocation::getLocationList('smartyOptions');
        $form->addElement('select', 'bu_id', 'BU', $buid);
        $form->addElement('date', 'due_date', 'When?', ['format' => 'Ymd', 'addEmptyOption' => true, 'minYear' => date('Y'), 'maxYear' => date('Y') + 2]);
        $form->setDefaults(
            [
                'task' => $dtask,
                'due_date' => [
                    'Y' => date('Y'),
                    'm' => date('m'),
                    'd' => date('d'),
                ],
            ]
        );
        break;
    case 'acct.outbound':
        $DEFAULT_TITLE .= ' - Outbound not on WO Request';
        $dtask = 'Please Insert comment here...';
        $erp = tldLocation::getERPByID($user->getBUID());
        $buid = ['' => ''] + tldLocation::getLocationList('smartyOptions');
        $form->addElement('select', 'bu_id', 'BU', $buid);
        $form->addElement('hidden', 'erp', $erp);
        $reason_code = ['Dedicated WH - not outbounded but moved from one warehouse to another' => 'Dedicated WH - not outbounded but moved from one warehouse to another',
            'PO to be launched via dedicated SEQ' => 'PO to be launched via dedicated SEQ',
            'Dedicated WH - not outbounded but moved from one warehouse to another' => 'Dedicated WH - not outbounded but moved from one warehouse to another',
            'Parts lost on the floor (parts already outbounded to the unit then lost...)' => 'Parts lost on the floor (parts already outbounded to the unit then lost...)',
        ];
        $form->addElement('select', 'reason_code', 'Reason', $reason_code);
        $form->setDefaults(
            [
                'task' => $dtask,
                'due_date' => [
                    'Y' => date('Y'),
                    'm' => date('m'),
                    'd' => date('d'),
                ],
            ]
        );
        break;
    case 'sales.new.customer.process':
        $DEFAULT_TITLE .= ' - New eCustomer';
        if (!$user->isInGroup(['sales_cust_admin', 'superuser'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permission for this task';
            break;
        }
        include_once 'sales_service.inc.php';
        // List
        $ctryList = tldCountry::optionsAsIDName();
        // Form
        $dtask = 'Approval For New eCustomer';
        $form->addElement('hidden', 'auto', $auto);
        $form->addElement('text', 'customer_name', 'Company Name, LONG (SHORT)', ['size' => '63']);
        $form->addElement('textarea', 'customer_address', 'Company Address',
            ['wrap' => 'VIRTUAL', 'cols' => '50', 'rows' => '5']);
        $form->addElement('select', 'ctry_id', 'Country', ['' => ''] + $ctryList);
        $form->addElement('text', 'customer_tel', 'Company Tel', ['size' => '63']);
        $form->addElement('text', 'customer_fax', 'Company Fax', ['size' => '63']);
        $form->addElement('select', 'type', 'Type', ['' => ''] + tldList::optionsByListNameAsListItemListItem('list.sales.customer.types'));
        $form->addElement('text', 'url', 'Website', ['size' => '63']);
        $form->addElement('select', 'asm_id', 'TLD Rep', ['' => ''] + tldGroup::getUserListByMultipleGroup(['role_ASM', 'role_SA', 'gg_SALES', 'gg_SALES_AGENTS', 'gg_SERVICE'], null, ['smartyOptions' => 1]));
        if (isset($auto) AND is_array($a = unserialize(base64_decode($auto)))) {
            // See if SEQ has already been started
            if (tldCustomer::isSequenceStarted($a['id'])) {
                $DEFAULT_ERROR[] = 'This Sequence Request Has Already Been Handled';
                break;
            }
            global $body;
            $body .= <<<EOF
		<h2 style="color:#090;">This Sequence Will Automatically Update {$a['m']} Module Once Approved</h2>
EOF;
            $form->addElement('hidden', 'parent_id', $a['id']);
            $form->setDefaults(['customer_name' => $a['v']]);
        }
        $form->addRule('customer_name', 'This is required', 'required');
        $form->applyFilter('customer_name', 'strtoupper');
        $form->setDefaults([
                'task' => $dtask,
                'due_date' => ['Y' => date('Y'), 'm' => date('m'), 'd' => date('d')]]
        );
        break;
    case 'sales.eparts.approval':
    case 'sales.eparts.approval.2':
        $DEFAULT_TITLE .= ' - eParts Approval';
        // Validate if able to proceed
        if (empty($xuid)) {
            $DEFAULT_ERROR[] = 'Extranet User ID Not Set';
            break;
        }
        $xu = new extranetUser($xuid);
        if ($xu->isEmpty()) {
            $DEFAULT_ERROR[] = 'Invalid Extranet User';
            break;
        }
        if ($xu->itsDetails['hidden'] OR $xu->itsDetails['enable'] <> 'Y') {
            $DEFAULT_ERROR[] = 'Extranet User Is Not Enabled';
            break;
        }
        if ($xu->getType() <> '') {
            $DEFAULT_ERROR[] = "Unable To Allow eParts Access To {$xu->getType()} User Type";
            break;
        }
        $xu->newEParts();
        if ($xu->eparts->checkRequirements() === true) {
            $DEFAULT_ERROR[] = 'Extranet User Already Has eParts Enabled';
            break;
        }
        // Proceed with sequence form
        $form->addElement('hidden', 'xuid', $xuid);
        break;
    case 'sales.new.customer.request':
        $DEFAULT_TITLE .= ' - New eCustomer Request';
        $dtask = <<<EOF
Requesting for new eCustomer to be entered into the TLD Intranet database.

Please provide the following info:

Company Name: 

Address: 

Country: 

Other Info: 

EOF;
        $custadmin = new tldGroup('sales_cust_admin');
        $form->addElement('select', 'sendto', 'Select Customer Admin', ['' => ''] + $custadmin->getUserlist(['smartyOptions' => true]));
        $form->addRule('sendto', 'This is required', 'required');
        $form->setDefaults([
            'task' => nl2br($dtask),
            'due_date' => ['Y' => date('Y'), 'm' => date('m'), 'd' => date('d')],
        ]);
        break;
    case 'sage.invoice.approval':
        $form->addElement('text', 'subject', 'Short description', ['size' => '30']);
        $form->addElement('select', 'assignee', 'Assignee', ['' => ''] + tldDirectory::getUserlistByERP(tldLocation::getIDByERP('390'), 'smartyOptions'));
        $form->addRule('assignee', 'Required', 'required');
        break;
}
$form->addElement('file', 'file', 'Attachment');
$form->setDefaults(['buid' => $user->getBUID()]);
$form->addElement('submit', 'btnSubmit', 'Submit');

if (!$form->validate()) {
    $body .= $form->toHTML();
    return;
}

$p = tldUtils::cleanupFormInput($form->exportValues());
// POST PROCESSING
/** @var \App\Client\ApiClient $client */
switch ($m[2]) {
    case 'stamp.request':
        $p['assignor'] = $user->getID();
        $p['assignee'] = $user->getID();
        $type = tldList::getListItemByKey('list.stamp.caty', $p['doc_type']);
        if($p['doc_type']>100 and $p['doc_type']<200) {
            $p['seqTpl'] = 118;
        }elseif($p['doc_type']>200 and $p['doc_type']<300) {
            $p['seqTpl'] = 119;
        }elseif($p['doc_type']>300 and $p['doc_type']<400) {
                $p['seqTpl'] = 120;
        }elseif($p['doc_type']>400 and $p['doc_type']<500) {
                $p['seqTpl'] = 121;
        }elseif($p['doc_type']>500 and $p['doc_type']<600) {
                $p['seqTpl'] = 122;
        }elseif($p['doc_type']>600 and $p['doc_type']<700) {
                $p['seqTpl'] = 123;
        }elseif($p['doc_type']>700 and $p['doc_type']<800) {
                $p['seqTpl'] = 131;
        }elseif($p['doc_type']>800 and $p['doc_type']<900) {
                $p['seqTpl'] = 134;
        }elseif($p['doc_type']>900 and $p['doc_type']<1000) {
                $p['seqTpl'] = 135;
        }
        $p['module'] = 'SEQ';
        $p['task'] .= <<<EOF
    <br/>
    Required date: <b>{$p['dt_from']}</b><br>
    Document Type: <b>{$type}</b><br>
    Stamp Type: <b>{$p['stamp_type']}</b><br>
    
    Contractual party offical name#1: <b>{$p['party_name']}</b><br>
    Contractual party offical name#2: <b>{$p['party_name1']}</b><br>
    
    
    Individual person name#1: <b>{$p['ind_name1']['name']}</b><br>   TLD employee: <b>{$p['ind_name1']['istldempolyee']}</b><br>
    Individual person name#2: <b>{$p['ind_name2']['name']}</b><br>   TLD employee: <b>{$p['ind_name2']['istldempolyee']}</b><br>
    
    Purchased or Sold: <b>{$p['pur_sold']['pur_sold']}</b><br>
    
    Description: <b>{$p['desc_name']}</b>
EOF;
    break;
    case 'newpayment.request':
        $p['assignor'] = $user->getID();
        $p['module'] = 'SEQ';
        $sess['encoding'] = 'UTF8';
        // get the location's ERP#
        $inv_erp = tldLocation::getERPByID($p['bu_id']);
        $loc = tldLocation::getLocationByERP($inv_erp);
        $supname = null;
        if ($p['suno'] !== null && "" !== $p['suno']){
            try {
                $sup = new Supplier();
                $supname = $sup->getSupplierByCode($p['suno'])['name'];
            } catch (NotFoundHttpException $exception) {
                $DEFAULT_ERROR[] = 'Supplier not found';
            }
        }
        if ($p['cur'] !== 'CNY'){
            if ($p['cur'] !== 'EUR'){
                $rate = tldForex::getRate('EUR',$p['cur']);
            }else{
                $rate = 1;
            }
            $CNYrate = tldForex::getRate('EUR', 'CNY');
            $orgamt = $p['amt'];
            $p['amt'] = $p['amt'] / $rate * $CNYrate;
        }


        $p['task'] .= <<<EOF
    <br/>
    Required date: {$p['dt_from']} <br>
    Supplier code#: {$p['suno']} <br>
    Supplier name(EN): {$supname} <br>
    Supplier name(CH): {$p['sup_name']} <br>
    Bank name: {$p['bank']} <br>
    Bank A/C: {$p['bc']} <br>
    Invoice#: {$p['invoice']} <br>
    Currency: {$p['cur']} $orgamt <br>
    Total Amount(RMB): {$p['amt']} <br>
    Cost center: {$loc} <br>
    Department: {$p['buyer_dpt_id']} <br>
    Type: {$p['type_id']} <br>
EOF;
        $p['task'] .= <<<EOF
<br/>
        Linked approved SEQ#:  <a href=\"https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id={$p['seq']}\">{$p['seq']}</a>
EOF;
        if (abs($amt) < 10000) {
            $p['seqTpl'] = 124;
        } elseif (10000 <= abs($amt) && abs($amt) <= 50000) {
            $p['seqTpl'] = 112;
        } elseif (50000 < abs($amt)) {
            $p['seqTpl'] = 111;
        }

        break;
    case 'newprepayment.request':
        $p['assignor'] = $user->getID();
        $p['module'] = 'SEQ';
        $sess['encoding'] = 'UTF8';
        // get the location's ERP#
        $inv_erp = tldLocation::getERPByID($p['bu_id']);
        $loc = tldLocation::getLocationByERP($inv_erp);
        $supname = null;
        if ($p['suno'] !== null){
            $sup = new Supplier();
            try {
                $supname = $sup->getSupplierByCode($p['suno'])['name'];
            } catch (NotFoundHttpException $e) {
                $DEFAULT_ERROR[] = 'Supplier not found';
            }
        }

        if ($p['cur'] !== 'CNY'){
            if ($p['cur'] !== 'EUR'){
                $rate = tldForex::getRate('EUR',$p['cur']);
            }else{
                $rate = 1;
            }
            $CNYrate = tldForex::getRate('EUR', 'CNY');
            $orgamt = $p['amt'];
            $p['amt'] = $p['amt'] / $rate * $CNYrate;
        }
//    if ($p['type_id']==='25.SH-PDI(&#21378;&#26816;&#36153;)'){
//        $p['task'] .="<h3><font color='#FFFF00'>PDI approval SEQ</font></h3>";
//    }

        $p['task'] .= <<<EOF
    <br/>
    Required date: {$p['dt_from']} <br>
    Supplier code#: {$p['suno']} <br>
    Supplier name(EN): {$supname} <br>
    Supplier name(CH): {$p['sup_name']} <br>
    Bank name: {$p['bank']} <br>
    Bank account: {$p['bc']} <br>
    Invoice#: {$p['invoice']} <br>
    Currency: {$p['cur']} $orgamt <br>
    Total Amount(RMB): {$p['amt']} <br>
    Cost center: {$loc} <br>
    Department: {$p['buyer_dpt_id']} <br>
    Type: {$p['type_id']} <br>
    With PO or not: {$p['po']} <br>
    PO: {$p['pono']} <br>
EOF;
        $level1 = 10000;
        $level2 = 50000;
        $level3 = 500000;

        if ($p['po'] === 'YES'){
            if (abs($amt) < $level2) {
                $p['seqTpl'] = 116;
            } elseif ($level1 <= abs($amt) && abs($amt) < $level3) {
                $p['seqTpl'] = 115;
            } elseif ($level3 < abs($amt)) {
                $p['seqTpl'] = 117;
            }
        }else{
            if (abs($amt) < $level1) {
                $p['seqTpl'] = 113;
            }elseif ($level1 <= abs($amt) && abs($amt) < $level2) {
                $p['seqTpl'] = 125;
            } elseif ($level2 <= abs($amt)) {
                $p['seqTpl'] = 114;
            }
        }

        break;
    case 'payment_request_asi.request':
        $p['assignor'] = $user->getID();
        $p['module'] = 'SEQ';
        $sess['encoding'] = 'UTF8';
        // get the location's ERP#
        $inv_erp = tldLocation::getERPByID($p['bu_id']);
        $loc = tldLocation::getLocationByERP($inv_erp);

        $supname = null;
        if ($p['suno'] !== ''){
            $sup = new Supplier();
            try {
                $supname = $sup->getSupplierByCode($p['suno'])['name'];
            } catch (NotFoundHttpException $e) {
                $DEFAULT_ERROR[] = 'Supplier not found';
            }
        }

        if ($p['cur'] !== 'CNY'){
            if ($p['cur'] !== 'EUR'){
                $rate = tldForex::getRate('EUR',$p['cur']);
            }else{
                $rate = 1;
            }
            $CNYrate = tldForex::getRate('EUR', 'CNY');
            $orgamt = $p['amt'];
            $p['amt'] = $p['amt'] / $rate * $CNYrate;
        }

        $p['task'] .= <<<EOF
    <br/>
    Required date: {$p['dt_from']} <br>
    Supplier code#: {$p['suno']} <br>
    Supplier name(EN): {$supname} <br>
    Supplier name(CH): {$p['sup_name']} <br>
    Bank name: {$p['bank']} <br>
    Bank account: {$p['bc']} <br>
    Currency: {$p['cur']} $orgamt <br>
    Cost center: {$loc} <br>
    Department: {$p['buyer_dpt_id']} <br>
EOF;

        $p['seqTpl'] = 139;
        break;
    case 'prepayment_request_sage.request':
        $p['assignor'] = $user->getID();
        $p['assignee'] = $user->getID();
        $p['module'] = 'SEQ';
        $p['seqTpl'] = 143;
        break;
    case 'vendor_creation_request_sage.request':
        $p['assignor'] = $user->getID();
        $p['assignee'] = $user->getID();
        $p['module'] = 'SEQ';
        $p['seqTpl'] = 142;
        break;
    case 'ap.inv.buyer.approval':
        $p['assignor'] = $user->getID();
        $p['task'] = <<<EOF
<a href="/en/private/invoices_po/spool/$_SESSION[bu_id]/$_SESSION[seq_file]">(Click here to see invoice)</a>
{$p['task']}
EOF;

        // Get assignee
        $query = 'select t_info from ttccom001' . $_SESSION['bu_id'] . ' where t_emno=' . $_SESSION['buyer_id'];
        $rows1 = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
        $query = "select id from people where email='" . $rows1[0][t_info] . "'";
        $rows2 = tldUtils::getSqlToAssocArray($query);
        $p['assignee'] = $rows2[0][id];
        $p['module'] = 'SEQ';
        $erp = $p['bu_id'];
        break;
    case 'mfg.pur.part.cost_roll_request':
        $p['assignor'] = $user->getID();
        $p['seqTpl'] = 43;
        $p['module'] = 'SEQ';
        break;
    case 'assets.disposal':
        $p['assignor'] = $user->getID();
        $p['seqTpl'] = 145;
        $p['module'] = 'SEQ';
    case 'mfg.pur.slow_moving_inventory':
        $p['assignor'] = $user->getID();
        $p['assignee'] = $user->getID();
        $p['seqTpl'] = 30;
        $p['module'] = 'SEQ';
        $p['task'] .= <<<EOF
<br/>
BU:  $bu_id
EOF;
        break;
    case 'hr.user.add':

        // merge step1 & step2 data
        $p = $people->itsDetails + $p;
        $region = new tldRegion($people->getRegionID());
        $divisionId = (int) $region->getDivisionId()['id'];
        if (
            $p['tld_function'] === 'SHOP FLOOR EMPLOYEE'
            && $p['pio_operator'] === 'Yes'
            && $p['acct_network'] === 'No'
            && $p['acct_email'] === 'No'
            && $p['acct_intranet'] === 'No'
            && $p['acct_erp'] === 'No'
            && $p['phone_desk'] === 'No'
            && $p['phone_cell'] === 'No'
        ) {
            $p['seqTpl'] = tldSEQTpl::byName('hr.user.shopfloor_light.new')['id'];
        } elseif (2 === $divisionId) {
            $p['seqTpl'] = tldSEQTpl::byName('hr.user.new.Sageparts')['id'];
        } else {
            $p['seqTpl'] = 141;
        }

        // Prepare Seq data
        $p['module'] = 'USER';
        $p['assignor'] = $user->getID();
        // SEQ creation
        $p['parent_id'] = $people->itsID;
        // Generate task description
        $taskDescription = 'Please handle this HR user sequence with informations provided below:<br><br>';
        foreach ($fieldsDescription as $field => $description) {
            switch ($field) {
                case 'reports_to':
                    $taskDescription .= "<br>$description: {$userList[$p[$field]]}";
                    break;
                case 'div_id':
                    $taskDescription .= "<br>$description: {$divisionList[$p[$field]]}";
                    break;
                case 'bu_id':
                    $taskDescription .= "<br>$description: {$buList[$p[$field]]}";
                    break;
                case 'dpt_id':
                    $taskDescription .= "<br>$description: {$departmentList[$p[$field]]}";
                    break;
                case 'fct_id':
                    $taskDescription .= "<br>$description: {$functionList[$p[$field]]}";
                    break;
                case 'due_date':
                    $originalDueDate = implode('-', $p['due_date']);
                    $taskDescription .= "<br>$description: $originalDueDate";
                    break;
                default;
                    $taskDescription .= "<br>$description: {$p[$field]}";
                    break;
            }
        }

        $p['task'] = TldDatabase::escape($taskDescription);
        break;
    case 'hr.user.delete':
        $leavingUser = new tldUser($p['uid']);
        $history = array_unique(array_column($leavingUser->getSequencesHistory(), 'name'));

        $people = new tldUser($p['uid']);
        $peopleDetails = $people->itsDetails;
        $region = new tldRegion($people->getRegionID());
        $divisionId = (int) $region->getDivisionId()['id'];

        $isFinanceRelated = false;
        try {
            $client = $kernel->getContainer()->get(Client::class);
        } catch (Exception $e) {
            $DEFAULT_ERROR[] = 'ERROR: Could not get API client. Reason: ' . $e->getMessage();
            return;
        }

        try {
            $positionsClassification = $client->findBy('position_classifications', [
                'businessUnit.legacyId' => $people->getHeader()['bu_id'],
                'positions.description' => $people->getHeader()['tld_function'],
                'positionCategory.name' => ['Finance & Accounting', 'General Management'],
            ]);
            $isFinanceRelated = count($positionsClassification) !== 0;
        } catch (Exception $e) {
            $DEFAULT_ERROR[] = 'ERROR: Could not get Position classification. Reason : ' .$e->getMessage();
            return;
        }

        if (2 === $divisionId) {
            $p['seqTpl'] = tldSEQTpl::byName('hr.user.delete.SageParts')['id'];
        } else {
            if ($history === ['hr.user.shopfloor_light.new']
                || $history === ['hr.user.shopfloor_light.new', 'hr.user.shopfloor_light.delete']) {
                $p['seqTpl'] = tldSEQTpl::byName('hr.user.shopfloor_light.delete')['id'];
            } elseif(!$isFinanceRelated) {
                $p['seqTpl'] = tldSEQTpl::byName('hr.user.delete.light')['id'];
                $p['assignee'] = $leavingUser->getSupervisor();
                $m[2] = 'hr.user.delete.light';
            } else {
                $p['seqTpl'] = tldSEQTpl::byName('hr.user.delete')['id'];
                $p['assignee'] = $leavingUser->getSupervisor();
            }
        }

        $p['module'] = 'USER';
        $p['assignor'] = $user->getID();
        $p['parent_id'] = $p['uid'];

        $dueDate = new DateTime(implode('-', $p['due_date']));
        if ($dueDate < new DateTime('today')) {
            $p['due_date'] = [
                'Y' => date('Y'),
                'm' => date('m'),
                'd' => date('d'),
            ];
        }

        $taskDescription = '<br><br>Please handle this HR user sequence with informations provided below:<br>';
        foreach ($fieldsDescription as $field => $description) {
            switch ($field) {
                case 'due_date':
                case 'email_due_date':
                    if (!empty($p[$field]['Y'])) {
                        $originalDueDate = implode('-', $p[$field]);
                    } else {
                        $originalDueDate = 'None';
                    }
                    $taskDescription .= "<br>$description: $originalDueDate";
                    break;
                default;
                    $taskDescription .= "<br>$description: {$p[$field]}";
                    break;
            }
        }

        $taskInformation = sprintf('User %s - %s - %s is leaving \n\n', $delUser->getFullname(), $delUser->itsDetails['location'], $delUser->itsDetails['tld_function']);
        $p['task'] = $taskInformation . $p['task'];
        $p['task'] .= $taskDescription;
        // MIS info
        $p['task'] .= <<<EOF
<br><br><u>*** MIS Reminder:</u> Check Org charts, external website and anywhere else that would need to be updated!
EOF;
        // ERP admin info

        $userLeaving = $delUser->getFullname();
        $adminInfo = <<<EOF
<br><br><u>*** ERP Reminder:</u> No tasks/WF to reassign from {$userLeaving}!
EOF;
        if ($p['reassign_task_ln'] === "Yes") {
            $reassignedUser = $userList[(int) $p['reassign_task_ln_to']];
            $adminInfo = <<<EOF
<br><br><u>*** ERP Reminder:</u> Please reassign INFOR tasks/WF from {$userLeaving} to {$reassignedUser}!
EOF;
        }

        $p['task'] .= $adminInfo;

        break;
    case 'hr.user.update':
        $p['seqTpl'] = 45;

        if ($changeUser->itsDetails['location'] === 'AGSA' || $people->itsDetails['location'] === 'ACCESSORIES' || strpos($changeUser->itsDetails['location'], 'SAGE') === 0) {
            $p['seqTpl'] = tldSEQTpl::byName('hr.user.update.Sageparts')['id'];
        }

        $p['module'] = 'USER';
        $p['assignor'] = $user->getID();
        $p['parent_id'] = $p['uid'];
        $taskDescription = "Please advise changes requested for {$changeUser->getFullname()}<br><br>";
        foreach ($fieldsDescription as $field => $description) {
            switch ($field) {
                case 'reports_to':
                    $taskDescription .= "<br>$description: {$userList[$p[$field]]}";
                    break;
                case 'div_id':
                    $taskDescription .= "<br>$description: {$divisionList[$p[$field]]}";
                    break;
                case 'bu_id':
                    $taskDescription .= "<br>$description: {$buList[$p[$field]]}";
                    break;
                case 'dpt_id':
                    $taskDescription .= "<br>$description: {$departmentList[$p[$field]]}";
                    break;
                case 'fct_id':
                    $taskDescription .= "<br>$description: {$functionList[$p[$field]]}";
                    break;
                case 'due_date':
                    $originalDueDate = implode('-', $p[$field]);
                    $taskDescription .= "<br>$description: $originalDueDate";
                    break;
                default;
                    $taskDescription .= "<br>$description: {$p[$field]}";
                    break;
            }
        }
        $taskDescription = <<<EOF
$taskDescription
<br>{$p['details']}
<br><br>*** IT Reminder: Check Org charts, external website and anywhere else that would need to be updated!
EOF;
        $p['task'] = $taskDescription;
        break;
    case 'eng.newpartnbrevision':
    case 'eng.newpartnb':
        $p['assignor'] = $user->getID();
        $p['assignee'] = $user->getID();
        $p['seqTpl'] = $m[2] === 'eng.newpartnbrevision' ? 78 : 22;
        $p['module'] = 'SEQ';
        $item = $p['pn'];
        $erp = $p['erp'];
        $pmoc = $p['pmoc'];
        $faq = $p['faq'] ?: false;
        $p['close_params'] = ['pn' => $item, 'erp' => $erp];
        // Update the task description
        $current_date = date('Y-m-d');
        $p['task'] = <<<EOF
<a target='_blank' href='https://www.tld-gse.com/en/private/manufacturing/eng/dev.php?m[0]=getfile&m[1]=drawing&erp=$erp&item=$item&date=$current_date'>(Click here to see drawing)</a>
{$p['task']}
EOF;
        break;
    case 'ap.inv.buyer.approval':
        $p['assignor'] = $user->getID();

        break;
    case 'acct.loanparts':
        $p['assignor'] = $user->getID();
        $criticalitem = false;
        switch ($p['erp']) {
            case 500:
            case 520:
            case 540:
                $assigneeErp = 540;
                break;
            case 600:
            case 610:
            case 620:
            case 640:
            case 650:
            case 660:
            case 680:
            case 700:
                $assigneeErp = 640;
                break;
            default:
                $assigneeErp = 400;
        }
        $p['assignee'] = $user->getID();
        $p['module'] = 'SEQ';

        $inv_erp = tldLocation::getERPByID($p['bu_id']);

        for ($i = 1; $i <= $p['nb_parts']; $i++) {
            try {
                $itemBySite = new ItemBySite();
                $row = $itemBySite->getItem((int) $inv_erp, $package[$i]['item']);
            } catch (ClientException) {
                $DEFAULT_ERROR[] = "Item $item Not Existing in Company $inv_erp";
                return;
            }
            $adj_amt = $row['t_copr'] * $package[$i]['qty'];
            $tot_amt += $row['t_copr'] * $package[$i]['qty'];
            if ($row['t_crmp'] === 1) {
                $criticalitem = true;
            };
            $row['t_dsca'] = TldDatabase::escape($row['t_dsca']);
            $p['task'] .= <<<EOF
    <br/>
    Adjustment details:
    Item#:  <a href=\"https://www.tld-gse.com/en/private/manufacturing/whse/dev.php?m[0]=inv&m[1]=byPNPlanned&erp=$inv_erp&id={$package[$i]['item']}&btnSubmit=Submit\">{$package[$i]['item']}</a>
    Item description: {$row['t_dsca']}
    Qty:  {$package[$i]['qty']}
    Total Adjustment Amount:  $adj_amt
    BU:  $inv_erp
    Location:  {$package[$i]['location']}
EOF;
        }
        if (in_array($inv_erp, ['640', '660', '680'])) {
            $level1 = 10000;
        }
        elseif (in_array($inv_erp, ['820', '830'])) {
            $level1 = 100000;
        } else {
            $level1 = 1000;
        }
        if (abs($tot_amt) < $level1 && !$criticalitem) {
            $p['seqTpl'] = 68;
        } else {
            $p['seqTpl'] = 67;
        }

        $p['close_params'] = [
            'item' => $item,
            'qty' => $item_qty,
            'adj_amt' => $adj_amt,
            'erp' => $inv_erp,
            'location' => $p['location'],
        ];
        break;
    case 'acct.positiveadj1':
        $p['assignor'] = $user->getID();
        switch ($p['erp']) {
            case 500:
            case 520:
            case 540:
                $assigneeErp = 540;
                break;
            case 600:
            case 610:
            case 620:
            case 640:
            case 650:
            case 660:
            case 680:
            case 700:
                $assigneeErp = 640;
                break;
            default:
                $assigneeErp = 400;
        }
        $p['assignee'] = $user->getID();
        $p['module'] = 'SEQ';

        // Template to be determinated by calcualtion :
        // will require:
        // ERP, qty and item number to go get the item total cost in baan, then multiply it by the qty to get the total cost for the request
        // then using the total from the calculation we can choose the right template :
        // tlp#27 under $1000
        // tlp#28 betwee $1000 and $5000
        // tlp#29 above $5000
        // $p['seqTpl'] = 22; or 78;

        // get the location's ERP#

        $inv_erp = tldLocation::getERPByID($p['bu_id']);

        for ($i = 1; $i <= $p['nb_parts']; $i++) {
            try {
                $itemBySite = new ItemBySite();
                $row = $itemBySite->getItem((int) $inv_erp, $package[$i]['item']);
            } catch (ClientException) {
                $DEFAULT_ERROR[] = "Item $item Not Existing in Company $inv_erp";
                return;
            }
            $adj_amt = $row['t_copr'] * $package[$i]['qty'];
            $tot_amt += $row['t_copr'] * $package[$i]['qty'];
            $itemdesc = tldDatabase::escape($row['t_dsca']);
            $p['task'] .= <<<EOF
    <br/>
    Adjustment details:
    Item#:  <a href=\"https://www.tld-gse.com/en/private/manufacturing/whse/dev.php?m[0]=inv&m[1]=byPNPlanned&erp=$inv_erp&id={$package[$i]['item']}&btnSubmit=Submit\">{$package[$i]['item']}</a>
    Item description: $itemdesc
    Qty:  {$package[$i]['qty']}
    Total Adjustment Amount:  $adj_amt
    BU:  $inv_erp
    Location:  {$package[$i]['location']}
    Reason Code:  {$package[$i]['reason_code']}
EOF;
        }
        if (in_array($inv_erp, ['640', '660', '680'])) {
            $level1 = 1000;
            $level2 = 10000;
        } else {
            $level1 = 100;
            $level2 = 1000;
        }
        if (abs($tot_amt) < $level1) {
            $p['seqTpl'] = 58;
        } else {
            $p['seqTpl'] = 62;
        }

        $nodes = tldSEQTplNode::byParent($p['seqTpl']);
        if ($nodes[0]['allow_supervisor'] == 2) {
            $p['assignee'] = $user->getSupervisor();
        }

        $p['close_params'] = [
            'item' => $item,
            'qty' => $item_qty,
            'adj_amt' => $adj_amt,
            'erp' => $inv_erp,
            'location' => $p['location'],
            'reason_code' => $p['reason_code'],
        ];
        break;
    case 'acct.negativeadj1':
        $p['assignor'] = $user->getID();
        $criticalitem = false;
        switch ($p['erp']) {
            case 500:
            case 520:
            case 540:
                $assigneeErp = 540;
                break;
            case 600:
            case 610:
            case 620:
            case 640:
            case 650:
            case 660:
            case 680:
            case 700:
                $assigneeErp = 640;
                break;
            default:
                $assigneeErp = 400;
        }
        $p['assignee'] = $user->getID();
        $p['module'] = 'SEQ';

        // Template to be determinated by calcualtion :
        // will require:
        // ERP, qty and item number to go get the item total cost in baan, then multiply it by the qty to get the total cost for the request
        // then using the total from the calculation we can choose the right template :
        // tpl#63 under $50
        // tpl#64 betwee $50 and $250
        // tpl#65 betwee $250 and $1000
        // tpl above $1000
        // $p['seqTpl'] = 66;


        // get the location's ERP#

        $inv_erp = tldLocation::getERPByID($p['bu_id']);

        for ($i = 1; $i <= $p['nb_parts']; $i++) {
            try {
                $itemBySite = new ItemBySite();
                $row = $itemBySite->getItem((int) $inv_erp, $package[$i]['item']);
            } catch (ClientException) {
                $DEFAULT_ERROR[] = "Item $item Not Existing in Company $inv_erp";
                return;
            }
            $adj_amt = $row['t_copr'] * $package[$i]['qty'];
            $tot_amt += $row['t_copr'] * $package[$i]['qty'];
            if ($row['t_crmp'] === 1) {
                $criticalitem = true;
            };
            $itemdesc = tldDatabase::escape($row['t_dsca']);
            $p['task'] .= <<<EOF
    <br/>
    Adjustment details:
    Item#:  <a href=\"https://www.tld-gse.com/en/private/manufacturing/whse/dev.php?m[0]=inv&m[1]=byPNPlanned&erp=$inv_erp&id={$package[$i]['item']}&btnSubmit=Submit\">{$package[$i]['item']}</a>
    Item description: $itemdesc
    Qty:  {$package[$i]['qty']}
    Total Adjustment Amount:  $adj_amt
    BU:  $inv_erp
    Location:  {$package[$i]['location']}
    Reason Code:  {$package[$i]['reason_code']}
EOF;
        }

        if (in_array($inv_erp, ['640', '660', '680'])) {
            $level1 = 500;
            $level2 = 2500;
            $level3 = 10000;
        } else {
            $level1 = 50;
            $level2 = 250;
            $level3 = 1000;
        }
        if (abs($tot_amt) < $level1 && !$criticalitem) {
            $p['seqTpl'] = 63;
        } elseif ($level1 < abs($tot_amt) && abs($tot_amt) < $level2 && !$criticalitem) {
            $p['seqTpl'] = 64;
        } elseif ($level2 < abs($tot_amt) && abs($tot_amt) < $level3 && !$criticalitem) {
            $p['seqTpl'] = 65;
        } else {
            $p['seqTpl'] = 66;
        }

        if (in_array($inv_erp, ['640', '660', '680'])) {
            $level1 = 500;
            $level2 = 10000;
        } else {
            $level1 = 50;
            $level2 = 1000;
        }

        $p['close_params'] = [
            'item' => $item,
            'qty' => $item_qty,
            'adj_amt' => $adj_amt,
            'erp' => $inv_erp,
            'location' => $p['location'],
            'reason_code' => $p['reason_code'],
        ];
        break;
    case 'acct.cyclecount':
        $p['assignor'] = $user->getID();
        $p['assignee'] = $user->getID();
        $p['seqTpl'] = 57;
        $p['module'] = 'SEQ';
        break;
    case 'acct.outbound':
        $p['assignor'] = $user->getID();
        $p['assignee'] = $user->getID();
        $p['seqTpl'] = 60;
        $p['module'] = 'SEQ';
        break;
    case 'acct.invadjust':
        $p['assignor'] = $user->getID();
        switch ($p['erp']) {
            case 500:
            case 520:
            case 540:
                $assigneeErp = 540;
                break;
            case 600:
            case 610:
            case 620:
            case 640:
            case 650:
            case 660:
            case 680:
            case 700:
                $assigneeErp = 640;
                break;
            default:
                $assigneeErp = 400;
        }
        $newAssignee = tldGroup::getUserListByMultipleGroup(['role_CA'], $assigneeErp);
        $p['assignee'] = (int)$newAssignee[0]['id'];
        $p['module'] = 'SEQ';

        // Template to be determinated by calcualtion :
        // will require:
        // ERP, qty and item number to go get the item total cost in baan, then multiply it by the qty to get the total cost for the request
        // then using the total from the calculation we can choose the right template :
        // tlp#27 under $1000
        // tlp#28 betwee $1000 and $5000
        // tlp#29 above $5000
        // $p['seqTpl'] = 22 or 78;

        $item_qty = $p['qty'];

        // get the location's ERP#

        $inv_erp = tldLocation::getERPByID($p['bu_id']);
        try {
            $itemBySite = new ItemBySite();
            $row = $itemBySite->getItem((int) $inv_erp, $item);
        } catch (ClientException) {
            $DEFAULT_ERROR[] = "Item $item Not Existing in Company $inv_erp";
            return;
        }
        $adj_amt = $row['t_copr'] * $item_qty;
        $$itemdesc = $row['t_dsca'];

        $p['task'] .= <<<EOF
<br/>
Adjustment details:
Item#:  <a href=\"https://www.tld-gse.com/en/private/manufacturing/whse/dev.php?m[0]=inv&m[1]=byPNPlanned&erp=$inv_erp&id=$item&btnSubmit=Submit\">$item</a>
Item description: $itemdesc 
Qty:  $item_qty 
Total Adjustment Amount:  $adj_amt 
BU:  $inv_erp 
Location:  {$p['location']} 
Reason Code:  {$p['reason_code']} 
EOF;

        $level1 = 1000;
        $level2 = 5000;

        if (abs($adj_amt) < $level1) {
            $p['seqTpl'] = 27;
        } elseif ($level1 < abs($adj_amt) && abs($adj_amt) < $level2) {
            $p['seqTpl'] = 28;
        } else {
            $p['seqTpl'] = 29;
        }

        $p['close_params'] = [
            'item' => $item,
            'qty' => $item_qty,
            'adj_amt' => $adj_amt,
            'erp' => $inv_erp,
            'location' => $p['location'],
            'reason_code' => $p['reason_code'],
        ];

        break;
    case 'sales.catalogue.datasheet.process':
        $body = 'This page has been migrated and should not be displayed anymore.';
        break;
    case 'sales.new.customer.process':
        $p['assignor'] = $user->getID();
        $p['assignee'] = $user->getID();
        $p['seqTpl'] = 34;
        $p['module'] = 'SEQ';
        $p['close_params'] = [
            'customer_name' => $p['customer_name'],
            'customer_address' => $p['customer_address'],
            'ctry_id' => $p['ctry_id'],
            'customer_tel' => $p['customer_tel'],
            'customer_fax' => $p['customer_fax'],
            'type' => $p['type'],
            'url' => $p['url'],
            'asm_id' => $p['asm_id'],
            'auto' => $p['auto'],
        ];
        if ($p['asm_id'] > 0) {
            $asm = new tldUser($p['asm_id']);
            $asm = $asm->getFullname();
        } else {
            $asm = 'N/A';
        }
        $p['task'] .= <<<EOF
<br/><br/>
<strong>New customer details:</strong><br/>
Customer Name:  {$p['customer_name']}<br/>
Customer Address:  {$p['customer_address']}<br/>
Customer Country: {$ctryList[$p['ctry_id']]}<br/>
Customer Tele:  {$p['customer_tel']}<br/>
Customer Fax:  {$p['customer_fax']}<br/>
Customer Type:  {$p['type']}<br/>
Customer Website:  {$p['url']}<br/>
TLD Rep:  $asm<br/>
EOF;
        if (isset($p['auto']) AND is_array($a = unserialize(base64_decode($p['auto'])))) {
            $p['task'] .= <<<EOF
<br/>
This Sequence Will Automatically Update {$a['m']} Module Once Approved
EOF;
        }
        break;
    case 'sales.new.customer.request':
        $p['assignor'] = $user->getID();
        $p['assignee'] = $p['sendto'];
        $p['seqTpl'] = 0;
        $p['module'] = 'SEQ';
        break;
    case 'sales.eparts.approval':
        $p['assignor'] = $user->getID();
        $p['assignee'] = 49;  // DELAITE Jean-Paul
        $p['seqTpl'] = 44;
        $p['module'] = 'SEQ';
        $p['close_params'] = [
            'xuid' => $xu->getID(),
        ];
        $p['task'] .= <<<EOF
<br/><br/>
<strong>Extranet User Details:</strong>
Customer Name:  {$xu->getCustomerName()}
User Name:  {$xu->getFullname()}
Email Address:  {$xu->getEmail()}
Userid:  {$xu->getUserid()}
User ID#:  {$xu->getID()}
EOF;
        break;
    case 'sales.eparts.approval.2':
        $crts = $xu->getCRTnRoles();

        $assignee = 49;  // DELAITE Jean-Paul
        foreach ($crts as $crt) {
            if ($crt['parts_rep_id'] !== 0) {
                $partRep = new tldUser($crt['parts_rep_id']);
                $grpSPM = new tldGroup('role_SPM', tldLocation::getERPByID($partRep->getBUID()));
                $spms = $grpSPM->getUserlist();
                if (!empty($spms)) {
                    $assignee = $spms[0]['id'];
                    break;
                }
            }
        }
        $p['assignor'] = $user->getID();
        $p['assignee'] = $assignee;
        $p['seqTpl'] = 73;
        $p['module'] = 'SEQ';
        $p['close_params'] = [
            'xuid' => $xu->getID(),
        ];
        $p['task'] .= <<<EOF
<br/><br/>
<strong>Extranet User Details:</strong>
Customer Name:  {$xu->getCustomerName()}
User Name:  {$xu->getFullname()}
Email Address:  {$xu->getEmail()}
Userid:  {$xu->getUserid()}
User ID#:  {$xu->getID()}
EOF;
        break;
    case 'sales.survey.campaign.approval':
        $p['assignor'] = $user->getID();
        $p['assignee'] = $user->getID();
        $p['parent_id'] = $campaign;
        $p['module'] = 'SEQ';
        $p['seqTpl'] = 72;

        // Create the sequence -------------------------------------------->
        $seqTplbyName = tldSEQTpl::byName($m[2]);
        $seqTpl = new tldSEQTpl($seqTplbyName['id']);

        $data = [
            'assignor' => $p['assignor'],
            'assignee' => $p['assignee'],
            'task' => $p['task'],
        ];

        $error = tldSEQ::insert(
            $p['parent_id'],
            $data,
            $p['seqTpl'],
            $p['module']
        );

        if (!is_numeric($error)) {
            $DEFAULT_ERROR[] = "ERROR: Could not create SEQ.<br/>Reason: $error";
            return;
        }

        global $kernel;
        $url = $kernel->getContainer()->get('router')->generate(
            'survey_published_index', [
            'id' => $survey,
            'campaignId' => $campaign,
            'seqId' => $error,
        ], \Symfony\Component\Routing\Router::ABSOLUTE_URL
        );
        header("Location: $url");
        exit;
    case 'sales.extranetuser.approval':
        throw new Exception('This is no longer used');
    case 'investmentbudget.request':
    case 'investmentbudget.request.factory':
        $p['assignor'] = $user->getID();
        if ($p['mis_or_not'] == 'Y') {
            $templateName = 'mis.investmentbudget.request';
            $p['seqTpl'] = tldSEQTpl::byName($templateName)['id'];
        } else {
            $p['seqTpl'] = tldSEQTpl::byName(tldDatabase::escape($m[2]))['id'];
        }
        $p['module'] = 'SEQ';
        break;
    case 'mis.order.process':
        $p['assignor'] = $user->getID();
        $p['assignee'] = $user->getID();
        $p['seqTpl'] = 48;
        $p['module'] = 'SEQ';
        break;
    case 'nonproductionpurchase.request':
        $p['assignor'] = $user->getID();
        $p['assignee'] = $user->getID();
        $p['seqTpl'] = 49;
        $p['module'] = 'SEQ';
        break;
    case 'payment.request':
        $p['assignor'] = $user->getID();
        $p['seqTpl'] = 105;
        $p['module'] = 'SEQ';
        break;
    case 'prepayment.request':
        $p['assignor'] = $user->getID();
        $p['seqTpl'] = 106;
        $p['module'] = 'SEQ';
        break;
    case 'sales.Customer.Validation':
        $p['assignor'] = $user->getID();
        $p['assignee'] = $user->getID();
        $p['seqTpl'] = 71;
        $p['module'] = 'SEQ';
        $p['parent_id'] = $customer;
        break;
    case 'sage.invoice.approval':
        $p['assignor'] = $user->getID();
        $p['module'] = 'SEQ';
        $p['seqTpl'] = 80;
        break;
    case 'seq.remotework.approval':
        $uid = $_REQUEST['uid'];
        if (empty($uid) || !is_numeric($uid)) {
            $DEFAULT_ERROR[] = 'ERROR: Parameter sent empty or invalid, can not retrieve user';
            break;
        }
        $remoteUser = new tldUser($uid);
        if ($remoteUser->isEmpty()) {
            $DEFAULT_ERROR[] = "ERROR: User#$uid not found";
            break;
        }

        $p['parent_id'] = $uid;
        $p['assignor'] = $user->getID();
        $p['assignee'] = $remoteUser->getSupervisor();
        $p['module'] = 'USER';
        $p['seqTpl'] = 109;

        break;
    case 'seq.remotework.removal':
        $uid = $_REQUEST['uid'];
        if (empty($uid) || !is_numeric($uid)) {
            $DEFAULT_ERROR[] = 'ERROR: Parameter sent empty or invalid, can not retrieve user';
            break;
        }
        $remoteUser = new tldUser($uid);
        if ($remoteUser->isEmpty()) {
            $DEFAULT_ERROR[] = "ERROR: User#$uid not found";
            break;
        }

        $p['parent_id'] = $uid;
        $p['assignor'] = $user->getID();
        $p['assignee'] = $remoteUser->getSupervisor();
        $p['module'] = 'USER';
        $p['seqTpl'] = 110;

        break;
    case 'it.purchase_request':
        $p['assignee'] = $user->getSupervisor();
        $p['assignor'] = $user->getID();
        $p['seqTpl'] = 138;
        $p['module'] = 'SEQ';
        break;
    case 'compliance.iso27001.major.ncr':
        $p['assignee'] = $user->getSupervisor();
        $p['assignor'] = $user->getID();
        $p['seqTpl'] = 140;
        $p['module'] = 'SEQ';
        break;
}

// Create the sequence -------------------------------------------->
$seqTplbyName = tldSEQTpl::byName($m[2]);
$seqTpl = new tldSEQTpl($seqTplbyName['id']);
$file = $form->getElement('file');
$data = [
    'assignor' => $p['assignor'],
    'assignee' => $p['assignee'],
    'bu_id' => $p['bu_id'],
    'due_date' => $p['due_date'],
    'escalation_trigger' => (int)$seqTpl->getEscalationTrigger(),
    'task' => $p['task'],
    'file_info' => $file->getValue(),
    'close_params' => $p['close_params'],
];

if (isset($m[2]) && ($m[2] === 'eng.newpartnb' || $m[2] === 'eng.newpartrevision') && isset($erp)) {
    unset($data['bu_id']);
    $data['erp'] = $erp;
}

// Prevent duplicate active HR user sequences (TTS #40173)
$hrUserTemplateNames = [
    'alvest.hr.new.user', 'hr.user.new', 'hr.user.new.Sageparts',
    'hr.user.shopfloor_light.new', 'hr.user.update', 'hr.user.update.Sageparts',
    'hr.user.delete', 'hr.user.delete.light', 'hr.user.delete.SageParts',
    'hr.user.shopfloor_light.delete',
];
$hrUserTemplateIds = array_filter(array_map(
    static function ($name) {
        return (int) (tldSEQTpl::byName($name)['id'] ?? 0);
    },
    $hrUserTemplateNames
));

if (
    ($p['module'] ?? '') === 'USER'
    && in_array((int) $p['seqTpl'], $hrUserTemplateIds, true)
    && ($existingSeqId = tldSEQ::getActiveSequenceIdForTemplate((int) $p['parent_id'], (int) $p['seqTpl'])) !== null
) {
    $existingSeqUrl = sprintf(
        '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=%d',
        $existingSeqId
    );
    $DEFAULT_ERROR[] = sprintf(
        'ERROR: An active sequence (<a href="%s">#%d</a>) with the same template already exists for this user. Please close the existing sequence before creating a new one.',
        $existingSeqUrl,
        $existingSeqId
    );
    $body .= $form->toHTML();

    return;
}

$error = tldSEQ::insert(
    $p['parent_id'],
    $data,
    $p['seqTpl'],
    $p['module']
);

if (!is_numeric($error)) {
    $DEFAULT_ERROR[] = "ERROR: Could not create SEQ.<br/>Reason: $error";
    return;
}

switch ($m[2]) {
    case 'eng.newpartnbrevision':
    case 'eng.newpartnb':
        // For reports matter, insert a record in the mod_keys table
        // - key1 => ERP
        // - key2 => PN
        $e = tldModKey::insert([
            'parent_id' => $error,
            'module' => 'SEQ',
            'type' => $m[2],
            'key1' => $erp,
            'key2' => $item,
        ]);

        if ($p['id'] && $p['modfrom'] != '') {
            $seqLink = tldModLink::insert($p['modfrom'], $p['id'], 'SEQ', $error);
            if (is_string($seqLink)) {
                $DEFAULT_ERROR[] = "ERROR: There was an error adding the new link. Reason: $seqLink";
            }
        }
        break;
    case 'ap.inv.buyer.approval':
        // Save sequence number
        $query = 'update ap_invoice_header set seq=' . $error . ' where t_inno=' . $_SESSION['inno'];
        $rowsHeader = tldUtils::sqlExecute($query);
        break;
    case 'hr.user.delete':
    case 'hr.user.delete.light':
        global $kernel;
        $client = $kernel->getContainer()->get(Client::class);

        try {
            $people = $client->findOneBy('people', ['legacyId' => $p['uid']]);
            $date = \DateTime::createFromFormat(
                'Y-n-j',
                $p['due_date']['Y'].'-'.$p['due_date']['m'].'-'.$p['due_date']['d']
            );
            if ($date){
                $formatedDate = $date->format('Y-m-d');
                $response = $client->put(
                    sprintf('people/%s/update_planned_disable_date', $people['id']),
                    [
                        'json' => [
                            'plannedDisableAt' => $formatedDate,
                        ]
                    ]
                );
            }
        } catch (Exception $error) {
            $errorMessage = $error->getMessage();
            $ADDITIONAL_ERROR[] = "ERROR: Sequence is created, but not possible to set the schedule disable date, please create TTS with a screenshot of the page. Reason: $errorMessage";
        }
        break;
}

$seq = new tldSEQ($error);
$tpl = $seq->getTemplate();

$message = <<<EOF
<a href="https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&single=1&id=$error">
Sequence #$error requires attention.</a>
EOF;
if ($p['pmoc']) {
    $loc = tldLocation::getLocationByERP($p['erp']);
    foreach ($p['pmoc'] AS $k => $v) $pmoc .= $k;
    $pmoc = str_replace('Array', '', $pmoc);
    if (in_array($p['erp'], [500, 510, 520, 540, 560])) {
        $sph = 540;
    }
    if (in_array($p['erp'], [620, 640, 660, 680])) {
        $sph = 680;
    }
    if (in_array($p['erp'], [600, 610])) {
        $sph = 600;
    }
    if (in_array($p['erp'], [300, 310, 330, 400])) {
        $sph = 300;
    }
    $gg = new tldGROUP('role_SPM', $sph);
    $ccList[] = $gg->getEmailList();
    $ccList = array_unique($ccList[0]);
    $msg = <<<EOF
A new {$pmoc} item {$p['pn']} has been created by {$loc}.<br><br>
Please check if this component needs to be sourced and put in inventory.<br><br>{$p['task']}
EOF;
    $assignee = new tldUser($user->getID());
    tldUtils::emailAttachment($assignee->getEmail(), 'noreply@tld-gse.com', "SEQ#$error: " . $tpl->getName() . ' - PMOC notification', $msg, null, $ccList);
}
$subject = "SEQ#$error: {$tpl->getName()}";
if (isset($p['subject'])) {
    $subject.= ' '.$p['subject'];
}
$seq->notifyAssignee($message, $subject);

if ((bool)$faq === true) {
    global $kernel;

    $container = $kernel->getContainer();

    $url = $container->get('router')->generate('first_article_qualifications_add', [
        'sequenceId' => $error,
        'erp' => $p['erp'],
        'partNumbers' => [$item],
    ]);

    header("Location: $url");
    return;
}
$body .= <<<EOF
<br/><br/>
<a href="$php_self?m[0]=tasks&m[1]=task&m[2]=view&id=$error">SEQ#$error successfully created. Click here to see task.</a>
EOF;
if (!empty($ADDITIONAL_ERROR)) {
    $body .= '<br/>' . implode('<br/>', $ADDITIONAL_ERROR);
}
?>
