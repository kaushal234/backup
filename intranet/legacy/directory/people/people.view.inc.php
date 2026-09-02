<?php

use ApiBundle\Client;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;

if (empty($id) || !is_numeric($id)) {
    $DEFAULT_ERROR[] = "ERROR: Parameters sent empty or invalid";
    return;
}
$people = new tldUser($id);
$header = $people->itsDetails;
if ($people->isEmpty()) {
    $DEFAULT_ERROR[] = "ERROR: User not found";
    return;
}
$supid = $people->getSupervisor();

$DEFAULT_TITLE .= "\\" . $people->getFullname();
$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=people&m[1]=view&id=$id">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=people&m[1]=view&m[2]=outVcard&id=$id">Vcard</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=people&m[1]=view&m[2]=photo&id=$id">Photo</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=people&m[1]=view&m[2]=seqAndUserTask&id=$id">User Sequence & tasks</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=people&m[1]=view&id={$supid}">Supervisor</a>
EOF;

$subordinates = $people->getSubordinates();
if (!empty($subordinates)) {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=people&m[1]=view&m[2]=subordinates&id={$id}">Team members</a>
EOF;
}
if ($user->isInGroup(array("gg_MIS", "gg_ADMIN"))) {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="/en/private/mis/mis.php?m[0]=inv&m[1]=tree&m[2]=byUser&email={$people->getEmail()}">IT Equipment</a>
&nbsp;|&nbsp;<a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=taskList&m[2]=byUser&userid=$id">Tasks</a>
EOF;
}
if ($user->isInGroup(array("gg_ADMIN", "role_SA", "role_EVP")) || $people->getSupervisor() == $user->getID()) {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="/en/private/sales_service/sales.php?m[0]=sfr&m[1]=home&m[2]=asmDashboard&asm_id=$id">SFR</a>
EOF;
}
if ($user->isInGroup(array("gg_MIS", "gg_HR")) || $user->getID() == $supid) {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=people&m[1]=view&m[2]=edit&id=$id">Edit</a>
EOF;
}
if ($user->isInGroup(array("superuser"))) {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=people&m[1]=view&m[2]=groups&id=$id">Groups</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=people&m[1]=view&m[2]=log&id=$id">Logs</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=people&m[1]=view&m[2]=duplicate&id=$id">Duplicate</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=people&m[1]=view&m[2]=transferDisable&id=$id">Transfer & disable</a>
EOF;
}

switch ($m[2]) {
    case 'seqAndUserTask':
        $DEFAULT_TITLE .= "\Sequences & User Tasks";
        // Sequence links
        $body .= <<<EOF
<p>Create user request sequence for {$people->getFullname()}</p>
<ul>
	<li><a href="/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=hr.user.update&uid=$id">Start UPDATE user request sequence</a> (IT systems profile)</li>
	<li><a href="/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=hr.user.delete&uid=$id">Start LEAVING user request sequence</a> (IT systems profile)</li>
    <li><a href="/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=seq.remotework.approval&uid=$id">ALVEST - Remote Working - IT approval process</a></li>
	<li><a href="/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=seq.remotework.removal&uid=$id">ALVEST - Remote Working - IT removal notification</a></li>
</ul>
EOF;
        if ($user->isInGroup(array("gg_MIS", "gg_HR"))) {
            $body .= <<<EOF
<p style="color: orange;">In case of missing HR SEQ or issue during creation process, you can still process a sequence <a href="/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=hr.user.add&id=$id">HR NEW user request sequence</a></p>
EOF;
        }

        $cells = array();
        // LEFT column -> Sequence user
        $report = new tldReportColumnar(
            tldSEQ::byConstraints(array("parent_id" => $id, "module" => "USER")),
            array(
                "xItems" => array(
                    "id" => "SEQ#",
                    "status" => "Status",
                    "cur_step" => "Current Step",
                    "date" => "Date Opened",
                    "dt_closed" => "Date Closed",
                    "assignor_fullname" => "Assignor",
                    "assignee_fullname" => "Assignee",
                    "task" => "Task"
                ),
                "title" => "User Sequences",
                "links" => array(
                    "id" => "/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id="
                )
            )
        );
        $cells[0] .= $report->fetch();
        // RIGHT column -> USER tasks
        $report = new tldReportColumnar(
            tldTask::byConstraints("T1.parent_id=$id AND T1.module LIKE 'USER' AND T1.seq<>'Y'"),
            array(
                "xItems" => array(
                    "id" => "Task#",
                    "status" => "Status",
                    "date" => "Date Opened",
                    "dt_closed" => "Date Closed",
                    "assignor_fullname" => "Assignor",
                    "assignee_fullname" => "Assignee",
                    "task" => "Task"
                ),
                "title" => "User Tasks",
                "links" => array(
                    "id" => "/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id="
                )
            )
        );
        $cells[1] .= $report->fetch();
        // Display
        $report = new tldHTMLTable(
            $cells,
            array(
                "cols" => 2,
                "attribs" => array(
                    "table" => " width='100%'",
                    "tr" => " bgcolor='#FFFFFF'",
                    "td" => 'width="50%"'
                )
            )
        );
        $body .= $report->fetch();
        break;
    case 'transferDisable':
        $DEFAULT_TITLE .= "\Transfer and disable";
        if (!$user->isInGroup(array("superuser", "gg_HR"))) {
            $DEFAULT_ERROR[] = "You do not have permissions for this page..";
            break;
        }

        // Get Client API
        try {
            /** @var Client $client */
            $client = $kernel->getContainer()->get(Client::class);
        } catch (Exception $e) {
            $DEFAULT_ERROR[] = "ERROR: Could not get API client. Reason: " . $e->getMessage();
            break;
        }

        // Includes
        include_once("erp.inc.php");
        include_once("sales_service.inc.php");
        // Get listing
        $assignees = tldDirectory::getUserlist("smartyOptions");
        $delUserGroups = tldUtils::optionsByKeyValue(
            $people->getGroups(),
            "group_name",
            "group_name"
        );
        // Get form
        $formDefault = array();
        $form = new HTML_QuickForm('frmByNum', 'post');
        $form->addElement('hidden', 'm[0]', 'people');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'transferDisable');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', "General transfer");
        $form->addElement('select', 'transferTo', 'Transfer all documents to...', $assignees);
        $formDefault["transferTo"] = $people->getSupervisor();
        // Case of ASM
        $count = count(tldCRT::bySalesRepID($people->getID()));
        if ($count > 0) {
            $form->addElement('header', 'title', "$count records found for CRT Sales Reps:");
            $form->addElement('select', 'salesRep', 'Transfer Sales reps to...', $assignees);
            $formDefault["salesRep"] = $people->getSupervisor();
            $form->addRule('salesRep', 'Required', 'required');
        }
        $count = count(tldCustomer::byAsmID($people->getID()));
        if ($count > 0) {
            $form->addElement('header', 'title', "$count records found for eCustomers ASM:");
            $form->addElement('select', 'customerAsm', 'Transfer ASM to...', $assignees);
            $formDefault["customerAsm"] = $people->getSupervisor();
            $form->addRule('customerAsm', 'Required', 'required');
        }
        // Case of Parts
        $count = count(tldCRT::byPartsRepID($people->getID()));
        if ($count > 0) {
            $form->addElement('header', 'title', "$count records found for CRT Parts Reps:");
            $form->addElement('select', 'partsRep', 'Transfer Parts reps to...', $assignees);
            $formDefault["partsRep"] = $people->getSupervisor();
            $form->addRule('partsRep', 'Required', 'required');
        }
        // Case of Service
        $count = count(tldCRT::byServicesRepID($people->getID()));
        if ($count > 0) {
            $form->addElement('header', 'title', "$count records found for CRT Services Reps:");
            $form->addElement('select', 'servicesRep', 'Transfer Services reps to...', $assignees);
            $formDefault["servicesRep"] = $people->getSupervisor();
            $form->addRule('servicesRep', 'Required', 'required');
        }
        // Case of IT equipment
        $count = count(Tld_Mis_Inventory_Item::byConstraints("destination_type = 'PEOPLE' AND destination_id =" . $people->getID()));
        $seq = tldSEQ::byConstraints(["parent_id" => $people->getID(), "module" => "USER"], ['limit' => 1, 'orderBy' => 'tasks.id DESC']);
        if ($count > 0 && $seq[0]['tplno'] == 47) {
            $form->addElement('header', 'title', "$count IT equipments linked:");
            $form->addElement('select', 'unassign', 'Unassign from user', ["" => "", "Y" => "Y"]);
            $form->addRule('unassign', 'Required', 'required');
        }
        // Case of CSR Tech
        $countCSR = count(TldCSR::byConstraints("csr.tech_id = {$people->getID()} AND csr.status NOT IN ('COMPLETED', 'CLOSED')"));
        if ($countCSR > 0) {
            $form->addElement('header', 'title', "$countCSR CSR linked as technician:");
            $form->addElement('select', 'csrLinkedTech', 'Transfer CSR Technician to...', $assignees);
            $formDefault['csrLinkedTech'] = $people->getSupervisor();
            $form->addRule('csrLinkedTech', 'Required', 'required');
        }
        // Case of TOC Assignee
        $countTOC = count(TldTOC::byConstraints("assid = {$people->getID()} AND status NOT IN ('SOLVED', 'CLOSED')"));
        if ($countTOC > 0) {
            $form->addElement('header', 'title', "$countTOC TOC linked as assignee:");
            $form->addElement('select', 'tocLinkedAssignee', 'Transfer TOC Assignee to...', $assignees);
            $formDefault['tocLinkedAssignee'] = $people->getSupervisor();
            $form->addRule('tocLinkedAssignee', 'Required', 'required');
        }
        // Case of TOC Tech
        $countTOC = count(TldTOC::byConstraints("tecid = {$people->getID()} AND status NOT IN ('SOLVED', 'CLOSED')"));
        if ($countTOC > 0) {
            $form->addElement('header', 'title', "$countTOC TOC linked as technician:");
            $form->addElement('select', 'tocLinkedTech', 'Transfer TOC Technician to...', $assignees);
            $formDefault['tocLinkedTech'] = $people->getSupervisor();
            $form->addRule('tocLinkedTech', 'Required', 'required');
        }
        // Confirmation
        $form->addElement('header', 'title', "Transfer and Disable, You are about to transfer all documents related to this user and disable this user.");
        $form->addElement('select', "conf", 'Select Y to confirm.', array("" => "", "Y" => "Y"));
        $form->setDefaults($formDefault);
        $form->addRule("conf", 'Required', 'required');
        $form->addRule("transferTo", 'Required', 'required');
        $form->addElement('submit', 'btnSubmit', 'Submit');

        if (!$form->validate()) {
            $body .= $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());

        /////////////////////////////////
        //        TRANSFER STEPS       //
        /////////////////////////////////

        $target = $client->findOneBy('people', ['legacyId' => $vars["transferTo"]]);
        $payload = [
            'target' => $target['@id'],
        ];

        // eCustomer ASM
        if (!empty($vars["customerAsm"])) {
            $ASMTarget = $client->findOneBy('people', ['legacyId' => $vars["customerAsm"]]);
            $payload['asmTarget'] = $ASMTarget['@id'];
        }

        // Transfer CRT reps
        if (!empty($vars["salesRep"])) {
            $CRTSalesRepTarget = $client->findOneBy('people', ['legacyId' => $vars["salesRep"]]);
            $payload['salesRepTarget'] = $CRTSalesRepTarget['@id'];
        }

        if (!empty($vars["partsRep"])) {
            $CRTPartsRepTarget = $client->findOneBy('people', ['legacyId' => $vars["partsRep"]]);
            $payload['partsRepTarget'] = $CRTPartsRepTarget['@id'];
        }

        if (!empty($vars["servicesRep"])) {
            $CRTServiceRepTarget = $client->findOneBy('people', ['legacyId' => $vars["servicesRep"]]);
            $payload['serviceRepTarget'] = $CRTServiceRepTarget['@id'];
        }

        $peopleToTransfer = $client->findOneBy('people', ['legacyId' => $id]);
        try {
            $client->request('people', $peopleToTransfer->getIriId(), 'transfer', 'PUT', [
                'json' => $payload
            ]);
        } catch (Exception $e) {
            $DEFAULT_ERROR[] = "ERROR: Could not update migrated resources for {$peopleToTransfer['firstname']} {$peopleToTransfer['lastname']}. Reason: " . $e->getMessage();
        }
        // Unassign IT equipment
        if (!empty($vars["unassign"])) {
            $rows = Tld_Mis_Inventory_Item::byConstraints("destination_type = 'PEOPLE' AND destination_id =" . $vars["id"]);
            foreach ($rows as $row) {
                Tld_Mis_Inventory_Item::unassign($row['id']);
            }
            if (!is_string($e)) {
                $DEFAULT_ERROR[] = "IT equipments successfully unassigned!";
            } else {
                $DEFAULT_ERROR[] = "ERROR: IT equipments unassign error. Reason:$e";
                break;
            }
        }
        // Transfer CSR Tech
        if (!empty($vars['csrLinkedTech'])) {
            $rows = TldCSR::byConstraints("csr.tech_id = {$people->getID()} AND csr.status NOT IN ('COMPLETED', 'CLOSED')");
            $reassignCSR = '';

            foreach ($rows as $row) {
                $reassignCSR = (new tldCSR($row['id']))->update(['tech_id' => $vars['transferTo']]);
            }
            if (is_string($reassignCSR)) {
                $DEFAULT_ERROR[] = "ERROR: Could not transfer CSR Technician. Reason: {$reassignCSR}";
                break;
            }
            $body .= '<p>CSR Technician transferred successfully!</p>';
        }
        // Transfer TOC Assignee
        if (!empty($vars['tocLinkedAssignee'])) {
            $rows = TldTOC::byConstraints("assid = {$people->getID()} AND status NOT IN ('SOLVED', 'CLOSED')");
            $reassignTOC = '';

            foreach ($rows as $row) {
                $reassignTOC = (new tldTOC($row['id']))->update(['assid' => $vars['transferTo']]);
            }
            if (is_string($reassignTOC)) {
                $DEFAULT_ERROR[] = "ERROR: Could not transfer TOC Assignee. Reason: {$reassignTOC}";
                break;
            }
            $body .= '<p>TOC assignee transferred successfully!</p>';
        }
        // Transfer TOC Tech
        if (!empty($vars['tocLinkedTech'])) {
            $rows = TldTOC::byConstraints("tecid = {$people->getID()} AND status NOT IN ('SOLVED', 'CLOSED')");
            $reassignTOC = '';

            foreach ($rows as $row) {
                $reassignTOC = (new tldTOC($row['id']))->update(['tecid' => $vars['transferTo']]);
            }
            if (is_string($reassignTOC)) {
                $DEFAULT_ERROR[] = "ERROR: Could not transfer TOC Technician. Reason: {$reassignTOC}";
                break;
            }
            $body .= '<p>TOC technician transferred successfully!</p>';
        }
        // Transfer general stuff
        $e = tldUser::transferFromTo($vars["id"], $vars["transferTo"]);
        if (!is_string($e)) {
            $DEFAULT_ERROR[] = "User's records successfully transferred!";
        } else {
            $DEFAULT_ERROR[] = "ERROR: User's records transfer error. Reason:$e";
            break;
        }

        // Confirmation
        $body .= "The user has been successfully deactivated and will be hidden in 3 months.";
        break;
    case 'photo':
        $DEFAULT_TITLE .= "\Photo";
        $body .= include('people.view.photo.tpl.php');
        break;
    default:
        $body = 'This page has been migrated and should not be displayed anymore.';
        break;
}

