<?php
$JS_INCLUDE[] = "/include/jquery/jquery.js";

if (!$user->isInGroup(["gg_ADMIN", "gg_PARTS", "role_ASM", "role_SPM", "gg_SERVICE", "role_EVP"])) {
    $DEFAULT_ERROR[] = "ERROR: You do not have permissions";
    return;
}

// Add overlib library for this section (used for Customer listing)
$overlib = $smarty->fetch('overlib.inc.js.tpl');
$overlib .= '<script type="text/javascript">$(function(){$(".overlib").overlib()});</script>';
$autosuggest = $smarty->fetch('autosuggest.inc.js.tpl');
$autosuggest .= '<script type="text/javascript">$(function(){var uci=$("[name=customer_select]").autosuggest({message:"Begin to type a portion of the name for auto suggestions",onChange:function(o){$(uci.input.display).val($(o.input.display).val());$(uci.input.value).val($(o.input.value).val());}});});</script>';
$smarty->assign("html_head", $overlib . $autosuggest);

$smarty->assign("width", 1300);

$DEFAULT_TITLE .= "/Spare Parts Quote";
$DEFAULT_MENU .= <<<EOF
	<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="$php_self?m[0]=spq">Home</a>&nbsp;|&nbsp;
	<a href="$php_self?m[0]=spq&m[1]=form&m[2]=byNum" title="Get SPQ by number">By number</a>&nbsp;|&nbsp;
	<a href="$php_self?m[0]=spq&m[1]=listing&m[2]=search" title="Search for SPQ">Search</a>&nbsp;|&nbsp;
	<a href="$php_self?m[0]=spq&m[1]=form&m[2]=quickedit" title="SPQ Quick Edit by User">Quick Edit by User</a>&nbsp;|&nbsp;
	<a href="$php_self?m[0]=spq&m[1]=reports">Reports</a>&nbsp;|&nbsp;
    <a href="/en/private/mis/mis.php?m[0]=help&m[1]=dms&id=1500">Help Page</a>&nbsp;|&nbsp;
    <a href="/en/private/mis/mis.php?m[0]=help&m[1]=dms&id=1501">User Guide</a>
EOF;

if ($user->isInGroup(["role_SPM", "gg_ADMIN", "role_EVP", "role_CSD"])) {
    $DEFAULT_MENU .= <<<EOF
	&nbsp;|&nbsp;<a href="$php_self?m[0]=spq&m[1]=perProfile">Dashboard per Profile</a>
EOF;
}
if ($user->isInGroup(["gg_ADMIN", "superuser"])) {
    $DEFAULT_MENU .= <<<EOF
	&nbsp;|&nbsp;<a href="spq/spq_admin.php">Maintain SPQ</a>
EOF;
}

switch ($m[1]) {
    case 'reports':
        switch ($m[2]) {
            default:
                $body = $smarty->fetch("$PATH/spq/reports/homepage.reports.tpl");
                break;
        }
        break;
    case 'view':
        include("spq.view.inc.php");
        break;
    case 'perProfile':
        if (!$user->isInGroup(["gg_ADMIN", "role_SPM", "role_EVP", "role_CSD"])) {
            $DEFAULT_ERROR[] = "ERROR: You do not have permission to access this page";
            return;
        }
        $PartsGrp = new tldGroup("gg_PARTS");
        $PartsList = tldUtils::optionsByKeyValue($PartsGrp->getUserlistBySSO(tldLocation::getIDByERP($DEFAULT_ERP)), "id", "fullname");
        if (empty($PartsList)) {
            $DEFAULT_ERROR[] = "ERROR: No Parts personnel found for SSO#$DEFAULT_ERP";
            break;
        }
        $form = new HTML_QuickForm('frmSelectUser', 'post');
        $form->addElement('header', 'title', 'Select Spare Parts User');
        $form->addElement('hidden', 'm[0]', 'spq');
        $form->addElement('hidden', 'm[1]', 'perProfile');
        $form->addElement('select', 'uid', 'User', ["" => ""] + $PartsList);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('uid', 'This is required', 'required');

        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        } else {
            $vars = tldUtils::cleanupFormInput($form->exportValues());
            $user_id = TldDatabase::escape($_POST["uid"]);
            header("Location: $php_self?m[0]=spq&user_id=$user_id");
            exit;
        }
        break;
    case 'listing':
        switch ($m[2]) {
            case 'fullReport':
                if (!$user->isInGroup(["gg_ADMIN", "role_SPM", "role_EVP", "role_CSD"])) {
                    $DEFAULT_ERROR[] = "ERROR: You do not have permission to access this report";
                    return;
                }
                switch ($m[3]) {
                    case 'fullCSV':
                        $data = $_SESSION['data_report'];
                        $report = new tldCSV(
                            $data,
                            [
                                "xItems" => [
                                    "id" => "SPQ#",
                                    "sph_fullname" => "SPH",
                                    "customer_name" => "eCustomer",
                                    "status" => "Status",
                                    "poster_fullname" => "Owner",
                                    "qono" => "Qono#",
                                    "qono_val" => "Quote Value",
                                    "baan_so" => "Baan SO#",
                                    "rfq" => "RFQ#",
                                    "request_type" => "Request Format",
                                    "contact_fullname" => "Contact Name",
                                    "contact_email" => "Contact Email",
                                    "dt_received" => "Request Received Date",
                                    "dt_ship" => "Requested Ship Date",
                                    "comment" => "Request description",
                                ],
                                "showTitles" => true,
                            ]
                        );
                        $report->out("spq_report.csv");
                        exit;
                        break;
                    case 'xls':
                        $data = $_SESSION['data_report'];
                        $report = new tldXLS(
                            $data,
                            [
                                "xItems" => [
                                    "id" => "SPQ#",
                                    "sph_fullname" => "SPH",
                                    "customer_name" => "eCustomer",
                                    "status" => "Status",
                                    "poster_fullname" => "Owner",
                                    "qono" => "Qono#",
                                    "qono_val" => "Quote Value",
                                    "baan_so" => "Baan SO#",
                                    "rfq" => "RFQ#",
                                    "request_type" => "Request Format",
                                    "contact_fullname" => "Contact Name",
                                    "contact_email" => "Contact Email",
                                    "dt_received" => "Request Received Date",
                                    "dt_ship" => "Requested Ship Date",
                                    "comment" => "Request description",
                                ],
                                "showTitles" => true,
                            ]
                        );
                        $report->out("spq_report.xls");
                        exit;
                        break;
                }
                //Get listing
                $sphList = tldSPH::getList("smartyOptionsLocationLocation");
                $customerList = tldCustomer::getList("smartyOptions");
                $statusList = tldSPQ::getStatusList();
                //Get form
                $form = new HTML_QuickForm('frmFullReport', 'post');
                $form->addElement('hidden', 'm[0]', 'spq');
                $form->addElement('hidden', 'm[1]', 'listing');
                $form->addElement('hidden', 'm[2]', 'fullReport');
                $form->addElement('header', 'title', 'Select filters:');
                $form->addElement('select', 'sph_fullname', 'SPH', ["" => "", "All" => "All"] + $sphList);
                $form->addElement('text', 'start', 'Start Date', ['class' => 'datepicker']);
                $form->addElement('text', 'end', 'End Date', ['class' => 'datepicker']);
                $form->addElement('select', 'status', 'Status', ["" => ""] + $statusList);
                $form->addElement('select', 'customer', 'Customer', ["" => ""] + $customerList);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->addRule('sph_fullname', 'Required', 'required');
                $form->setDefaults(['start' => date("Y-m-d", strtotime('first day of -3 months')), 'end' => date("Y-m-d")]);
                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break;
                } else {
                    $vars = tldUtils::cleanupFormInput($form->exportValues());
                    $start = $vars['start'];
                    $end = $vars['end'];
                    $sph = $vars['sph_fullname'];
                    $status = $vars['status'];
                    $customer = $vars['customer'];
                    $data = tldSPQ::fullReport($start, $end, $sph, $status, $customer);
                    if (isset($_SESSION['data_report'])) {
                        unset($_SESSION['data_report']);
                    }
                    $_SESSION['data_report'] = $data;
                    if (isset($data)) {
                        $report = new tldReportColumnar(
                            $data,
                            [
                                "xItems" => [
                                    "id" => "SPQ#",
                                    "sph_fullname" => "SPH",
                                    "customer_name" => "eCustomer",
                                    "status" => "Status",
                                    "poster_fullname" => "Owner",
                                    "qono" => "Qono#",
                                    "qono_val" => "Quote Value",
                                    "baan_so" => "Baan SO#",
                                    "rfq" => "RFQ#",
                                    "request_type" => "Request Format",
                                    "contact_fullname" => "Contact Name",
                                    "contact_email" => "Contact Email",
                                    "dt_received" => "Request Received Date",
                                    "dt_ship" => "Requested Ship Date",
                                    "comment" => "Request description",
                                ],
                                "title" => "Full SPQ listing for $sph - Between $start and $end ",
                                "links" => ["id" => "$php_self?m[0]=spq&m[1]=view&id="],
                            ]
                        );
                        $DEFAULT_MENU .= <<<EOF
        <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <a href="$php_self?m[0]=spq&m[1]=listing&m[2]=fullReport&m[3]=xls">Download XLS</a>&nbsp;|&nbsp;
        <a href="$php_self?m[0]=spq&m[1]=listing&m[2]=fullReport&m[3]=fullCSV">Download CSV</a>
EOF;
                        $body .= $report->fetch();
                    }
                }
                break;
            case 'search':
                $DEFAULT_TITLE .= "\Search";
                $sphList = tldSPH::getList("smartyOptionsIDLocation");
                $form = new HTML_QuickForm('frmSPQSearch', 'post');
                $form->addElement('hidden', 'm[0]', 'spq');
                $form->addElement('hidden', 'm[1]', 'listing');
                $form->addElement('hidden', 'm[2]', 'search');
                $form->addElement('header', 'title', 'SPQ Search');
                $form->addElement('select', 'sph_id', 'SPH', ["" => ""] + $sphList);
                $form->addElement('static', null, null,
                    "The <b>Look for</b> field will try to match with the content of: Customer name, Contact name, Status, Qono#, Baan SO#, RFQ#, Request type, Poster name, Contact email");
                $form->addElement('text', 'target', 'Look for', ["size" => "40"]);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->addRule('target', 'This is required', 'required');
                $form->addRule('sph_id', 'This is required', 'required');
                if ($form->validate()) {
                    $vars = tldUtils::cleanupFormInput($form->exportValues());
                    $rows = tldSPQ::search("%{$vars['target']}%", $vars['sph_id']);
                    $caption = "Search result for '{$vars['target']}'";
                } else {
                    $body = $form->toHTML();
                }
                break;
            case 'bySPHStatus':
                if (!$user->isInGroup(["gg_ADMIN", "role_SPM", "role_EVP", "role_CSD"])) {
                    $user_id = $user->getID();
                    $user_title = " - " . $user->getFullname();
                }
                if ($user->isInGroup(["gg_ADMIN", "role_SPM", "role_EVP", "role_CSD"]) && !empty($user_id)) {
                    $user_dashboard = new tldUser($user_id);
                    $user_title = " - " . $user_dashboard->getFullname();
                }
                $rows = tldSPQ::bySPHStatus($y, $x, $user_id);
                $caption = "SPQ with '$x' status for $y SPH $user_title";
                break;
        }
        if (isset($rows, $caption)) {
            $body .= _getListing($rows, $caption);
        }
        break;
    case 'form':
        switch ($m[2]) {
            case 'quickedit':
                $DEFAULT_TITLE .= "\Quick Edit";
                $PartsGrp = new tldGroup("gg_PARTS");
                $PartsList = tldUtils::optionsByKeyValue($PartsGrp->getUserlist(), "id", "fullname");
                $form = new HTML_QuickForm('frmByUser', 'post');
                $form->addElement('hidden', 'm[0]', 'spq');
                $form->addElement('hidden', 'm[1]', 'form');
                $form->addElement('hidden', 'm[2]', 'quickedit2');
                $form->addElement('header', 'title', 'Quick Edit - Select User');
                $form->addElement('select', 'poster_id', 'Owner', ["" => ""] + $PartsList);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->setDefaults(["poster_id" => $user->getID()]);
                $form->addRule('poster_id', 'Required', 'required');
                $body = $form->toHTML();
                break;
            case 'quickedit2':
                $DEFAULT_TITLE .= "\Quick Edit";
                if (empty($poster_id)) {
                    $DEFAULT_ERROR[] = "The user id is missing";
                    break;
                }
                $user_profile = new tldUser($poster_id);
                $username = $user_profile->getFullname();
                $spqList = tldSPQ::getSPQByUser($poster_id, "SUBMITTED FULL");
                if (empty($spqList)) {
                    $DEFAULT_ERROR[] = "No 'SUBMITTED FULL' SPQ for $username";
                    break;
                }
                $statusList = ['SUBMITTED FULL', 'ORDERED FULL', 'ORDERED PARTIAL', 'CANCELLED', 'LOST'];
                $smarty->assign('nextURL', "$php_self?m[0]=spq&m[1]=form&m[2]=quickedit2&m[3]=update&poster_id=$poster_id");
                $smarty->assign('spqList', $spqList);
                $smarty->assign('statusList', $statusList);
                $body .= $smarty->fetch("$PATH/spq/spq.quickedit.tpl");
                switch ($m[3]) {
                    case 'update':
                        $body = "";
                        if (empty($_POST['spq']) || !is_array($_POST['spq'])) {
                            $DEFAULT_ERROR[] = "ERROR: No SPQ data set or data invalid !";
                            break;
                        }
                        foreach ($_POST['spq'] as $spq_id => $data) {
                            if (!is_numeric($spq_id)) {
                                $DEFAULT_ERROR[] = "ERROR: SPQ ID# invalid !";
                                break;
                            }
                            $data = tldUtils::cleanupFormInput($data);
                            $spq = new tldSPQ($spq_id);
                            if ($data['status'] != "SUBMITTED FULL") {
                                $e = $spq->quickClose($data);
                                if (!is_string($e)) {
                                    $spq->addLogEntry($user->getID(), "Quick Edit Closure: " . $data['status']);
                                    $body .= "<br/>SPQ#$spq_id successfully closed!";
                                } else {
                                    $DEFAULT_ERROR[] = "ERROR: Problem closing SPQ#$spq_id...<br/>Reason: $e";
                                }
                            }
                        }
                        $body .= _getListing(tldSPQ::getSPQByUser($poster_id, "SUBMITTED FULL"), "SPQ for $username - SUBMITTED FULL");
                        break;
                }
                break;
            case 'byNum':
                $form = new HTML_QuickForm('frmByNum', 'post');
                $form->addElement('hidden', 'm[0]', 'spq');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('header', 'title', 'SPQ by number');
                $form->addElement('text', 'id', 'SPQ#');
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->addRule('id', 'Required', 'required');
                $body = $form->toHTML();
                break;
            case "newLink":
                $DEFAULT_MENU = '';
                $DEFAULT_TITLE .= '\New Link';
                //check module
                $module = strtoupper($module);
                $modules = tldTask::getModuleList();
                if (!in_array($module, $modules)) {
                    $DEFAULT_ERROR[] = "ERROR: Module '$module' is not valid";
                    break;
                }
                $modules = array_intersect($modules, tldSPQ::getLinkableModules());
                //check parent_id
                if (empty($parent_id)) {
                    $DEFAULT_ERROR[] = "ERROR: parent_id not set";
                    break;
                }
                $url = tldModLink::getURL($module, $parent_id);
                $body .= <<<EOF
		<a href="$url">Click here to go back to $module #$parent_id</a>
EOF;
                $form = new HTML_QuickForm('frmNew', 'post');
                $form->addElement('header', 'title', "Submit new $module Link");
                $form->addElement('hidden', 'm[0]', 'spq');
                $form->addElement('hidden', 'm[1]', 'form');
                $form->addElement('hidden', 'm[2]', 'newLink');
                $form->addElement('hidden', 'module', $module);
                $form->addElement('hidden', 'parent_id', $parent_id);
                $form->addElement('select', 'type', 'Ref Type', $modules);
                $form->addElement('text', 'item', 'Ref#');
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->addRule("type", "This is a required field.", "required");
                $form->addRule("item", "This is a required field.", "required");

                if (!$form->validate()) {
                    $body .= $form->toHTML();
                    break;
                }

                $vals = tldUtils::cleanupFormInput($form->exportValues());
                $error = tldModLink::insert($vals['module'], $vals['parent_id'], $modules[$vals['type']], $vals['item']);
                if (is_string($error)) {
                    $DEFAULT_ERROR[] = "ERROR: There was an error adding the new link. Reason: $error";
                }
                $body .= "<p>{$modules[$vals['type']]}#{$vals['item']} linked successfully!</p>";
                break;
        }
        break;
    default:
        if (!$user->isInGroup(["role_SPM", "gg_ADMIN", "role_EVP", "role_CSD"])) {
            $user_id = $user->getID();
        }
        if (!empty($user_id)) {
            $user_profile = new tldUser($user_id);
            $user_title = " - " . $user_profile->getFullname();
        }
        $body .= $smarty->fetch("$PATH/spq/homepage.spq.tpl");
        $dashboard = new tldMatrix(
            tldSPQ::countBySPHStatus($user_id),
            "status", "sph_fullname", "num",
            "$php_self?m[0]=spq&m[1]=listing&m[2]=bySPHStatus&user_id=$user_id",
            "SPQ Count by Status, SPH $user_title",
            ["xItems" => tldSPQ::getStatusList()]
        );

        $body .= $dashboard->fetch();
        $body .= _getListing(tldSPQ::byLatest($user_id), "Latest SPQ $user_title");
}

function _getGeneralTab()
{
    global $spq, $header, $DEFAULT_MENU, $id;
    if (!empty($header['qono'])) {
        // Get the archive
        $arch = new tldArchive(tldSPH::getERP($header['sph_id']));
        $rows = $arch->byTypeID("SALES QUOTATION", $header['qono']);
        if (count($rows)) {
            $qono_link = "<a href='/en/private/strs_pdf/archive/" . $rows[0]['filepath'] . "'>Click here to download the Quotation #" . $header['qono'];
        }
    }
    $general = new tldAssocTable($header, [
        "id" => "SPQ#",
        "dt_open" => "Date Opened",
        "poster_fullname" => "Owner",
        "sph_fullname" => "SPH",
        "status" => "Status",
        "qono" => "Baan Quotation Number (Qono#)",
        "qono_val" => "Quote Value",
        "baan_so" => "Baan SO#",
        "rfq" => "RFQ#",
        "dt_received" => "Request Received Date",
        "dt_ship" => "Requested Ship Date",
        "customer_name" => "eCustomer",
        "request_type" => "Request Format",
        "comment" => "Request description",
    ],
        [
            "title" => "General"]
    );
    if ($header['contact_id'] != 0) {
        $color = "#6CC071";
        $spqid = $header['id'];
        $status = $header['status'];
        $contact_link = "Linked to Contact#" . $header['contact_id'];
        $contact = new tldAssocTable(
            $spq->getContact($header['contact_id']),
            [
                "id" => "Ext User#",
                "lastname" => "Lastname",
                "firstname" => "Firstname",
                "division" => "Division",
                "department" => "Department",
                "title" => "Title",
                "phone" => "Phone",
                "direct_phone" => "Direct phone",
                "fax" => "Fax",
                "mobile" => "Mobile",
                "email" => "Email",
                "lang" => "Prefered Language",
            ],
            ["title" => "Contact Details"]
        );
    } else {
        $color = "#FF5252";
        $spqid = $header['id'];
        $status = $header['status'];
        $contact_link = "LINK IS MISSING";
        $contact = new tldAssocTable(
            $header,
            [
                "contact_id" => "Contact#",
            ],
            ["title" => "Contact Details"]
        );
    }
    $table = <<<EOF
<table border="0" width="100%" cellspadding="2" cellspacing="4">
  <tr>
    <td align="center" bgcolor="{$color}"><b>Spare Parts Quote #{$spqid}</b><br><b>Status:</b> {$status}<br><b>Contact:</b> {$contact_link}</td>
  </tr>
</table>
<table width="100%">
  <tr>
    <td>{$general->fetch()}</td>
    <td>{$contact->fetch()}</td>
  </tr>
</table>
$qono_link
EOF;
    return $table;
}

function _getListing($rows, $title)
{
    global $php_self;
    $report = new tldReportColumnar(
        $rows,
        [
            "xItems" => [
                "id" => "SPQ#",
                "sph_fullname" => "SPH",
                "poster_fullname" => "Owner",
                "customer_name" => "Customer Name",
                "dt_received" => "Request Received Date",
                "status" => "Status",
                "qono" => "Qono#",
                "qono_val" => "Quote Value",
                "baan_so" => "Baan SO#",
                "rfq" => "RFQ#"],
            "title" => $title,
            "links" => [
                "id" => "$php_self?m[0]=spq&m[1]=view&id=",
            ],
        ]
    );
    return $report->fetch();
}
