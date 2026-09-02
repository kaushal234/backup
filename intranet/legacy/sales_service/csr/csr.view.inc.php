<?php
use ApiBundle\Client;

include_once "objects/BillingForm.php";
include_once "objects/ApiLogger.php";

if (empty($id) || !is_numeric($id)) {
    $body = 'This page has been migrated and should not be displayed anymore.';
    return;
}
$csr = new tldCSR($id);
$header = $csr->itsHeader;
if ($csr->isEmpty()) {
    $body = 'This page has been migrated and should not be displayed anymore.';
    return;
}

global $kernel;
$container = $kernel->getContainer();

$client = $container->get(Client::class);
$customerServiceRecord = $client->findOneBy('service/customer_service_records', ['legacyId' => $id]);

$apiId = $customerServiceRecord->getIriId();

$DEFAULT_TITLE .= "\CSR#$apiId";

// Specific ACL check (Baan Service Order)
$msgAccessCheck = _isAllowed($m, $user);
if (is_string($msgAccessCheck)) {
    $DEFAULT_ERROR[] = "WARNING: You do not have permissions. Reason: $msgAccessCheck";
    return;
}

$router = $container->get('router');
$url = $router->generate('customer_service_record_show', ['id' => $customerServiceRecord['id']]);

$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$url}">CSR #{$customerServiceRecord['id']}</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=csr&m[1]=view&m[2]=costs&id=$id">Show Cost</a>
&nbsp;|&nbsp;<a href="/en/private/common/index.php?m[0]=costs&m[1]=add&module=CSR&parent_id=$id">Add Cost</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=csr&m[1]=view&m[2]=edit&id=$id">Edit billing</a>
EOF;

switch ($m[2]) {
    case 'survey':
        $DEFAULT_TITLE .= "\Survey";

        switch ($m[3]) {
            case 'edit':
                $body = 'This page has been migrated and should not be displayed anymore.';
                break 2;
        }

        $body = 'This page has been migrated and should not be displayed anymore.';
        break;
    case 'duplicate':
    case 'members':
    case 'delete':
    case 'status':
    case "tasks":
    case 'log':
        $DEFAULT_TITLE .= "\Logs";

        switch ($m[3]) {
            case 'add':
                $body = 'This page has been migrated and should not be displayed anymore.';
                break 2;
        }

        $body = 'This page has been migrated and should not be displayed anymore.';
        break;
    case 'files':
        $DEFAULT_TITLE .= "\Files";

        switch ($m[3]) {
            case 'download_all':
                $body = 'This page has been migrated and should not be displayed anymore.';
                break 2;
            default:
                $body = 'This page has been migrated and should not be displayed anymore.';
                break 2;
        }

        $body = 'This page has been migrated and should not be displayed anymore.';
        break;
    case 'links':
        $body = 'This page has been migrated and should not be displayed anymore.';
        break;
    case 'parts':
        $body = 'This page has been migrated and should not be displayed anymore.';
        break;
    case 'edit':
        $DEFAULT_TITLE .= "\Edit";

        $form = BillingForm::build($header);

        if (!$form->validate()) {
            $body .= $form->toHTML();
            break;
        }

        $e = $csr->update(
            tldUtils::cleanupFormInput($form->exportValues()),
            ['erp_inv', 'bill_to', 'bill_instruction']
        );
        if (is_string($e)) {
            $DEFAULT_ERROR[] = "ERROR: Problem updating...<br/>Reason: $e";
            break;
        }

        // Log
        ApiLogger::log($csr, $customerServiceRecord);

        header("Location: $php_self?m[0]=csr&m[1]=view&m[2]=costs&id=$id");
        exit;
    case 'costs':
        $DEFAULT_TITLE .= "\Costs";
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=csr&m[1]=view&m[2]=costs&out=xls&id=$id">XLS</a>
EOF;

        // Currency handling ------------------------->

        $curList = tldForex::getCurrencyList();
        if (!empty($_GET['dcur']) && in_array($_GET['dcur'], $curList)) {
            $sess['dcur'] = $_GET['dcur'];
        }
        if (empty($sess['dcur'])) {
            $sess['dcur'] = 'USD';
        }
        // Currency selection
        $form = new HTML_QuickForm('frmNew', 'get');
        $form->addElement('hidden', 'm[0]', 'csr');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'costs');
        $form->addElement('hidden', 'id', $id);
        $form->addElement(
            'select', 'dcur', 'Change Default Currency',
            array('' => '') + tldForex::getCurrencyList(),
            array("onChange" => "javascript:this.form.submit();")
        );
        $body .= $form->toHTML();
        $cells = [];

        // Cost summary --------------------------->

        $report = new tldReportColumnar(
            $csr->getTotalCostsByType($sess['dcur']),
            array(
                "xItems" => array(
                    "type" => "Type",
                    "dcur" => "Currency",
                    "price_dcur" => "Total Price in Currency",
                ),
                "title" => "Costs Summary in " . $sess['dcur'],
                "sumTotalsArray" => array("total_dcur" => "price_dcur")
            )
        );
        $cells[] = $report->fetch();

        $form = new tldAssocTable(
            $header,
            [
                'erp_inv' => 'ERP Invoice#',
                'bill_to' => 'Bill to',
                'bill_instruction' => 'Billing instruction',

            ],
            ['title' => 'Billing information']
        );
        $cells[] = $form->fetch();

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

        // Costs Details --------------------------->

        $costsDetails = $csr->getCosts($sess['dcur']);
        $xItems = array(
            "poster_fullname" => "Poster",
            "user_fullname" => "User concerned",
            "date" => "Cost date",
            "type" => "Type",
            "description" => "Description",
            "cur" => "Currency",
            "price" => "Price",
            "dcur" => "Default currency",
            "price_dcur" => "Price in Default currency*",
        );
        $report = new tldReportColumnar(
            $costsDetails,
            array(
                "xItems" => $xItems,
                "title" => "Costs Details",
                "functions" => array(
                    "Edit" => array(
                        "url" => "/en/private/common/index.php?m[0]=costs&m[1]=view&m[2]=edit",
                        "param" => array("id" => "id"),
                        "img" => "/shared/icons/miscellaneous/edit.png"
                    ),
                    "Delete" => array(
                        "url" => "/en/private/common/index.php?m[0]=costs&m[1]=view&m[2]=delete",
                        "param" => array("id" => "id"),
                        "confirmPopup" => "Are you sure to delete?",
                        "img" => "/shared/icons/miscellaneous/delete.png"
                    )
                ),
                "showItemNumbers" => true
            )
        );
        $body .= $report->fetch();
        $body .= "<p><i>* Prices in default currency is calculated from intranet FOREX table<i></p>";

        // XLS extraction --------------------------->

        if ($_REQUEST['out'] == "xls") {
            $report = new tldXLS(
                $costsDetails,
                array(
                    "xItems" => $xItems,
                    "showTitles" => true
                )
            );
            $report->out("Costs_of_CSR_$id.xls");
            exit;
        }
        break;
    case 'faq':
        $body = 'This page has been migrated and should not be displayed anymore.';
        break;
    case 'labors':
        $body = 'This page has been migrated and should not be displayed anymore.';
        break;
    default:
        $body = 'This page has been migrated and should not be displayed anymore.';
        break;
}

function _isAllowed()
{
    global $csr, $m, $user;
    // check access per action
    switch ($m[2]) {
        case 'delete':
            // if BAAN service order
            if ($csr->getModule() == 'SRVO') {
                // Make sure the SRVO is deleted
                $srvo = new tldServiceOrder($csr->getModuleID(), $csr->getSSOERP());
                if (!$srvo->isEmpty()) {
                    return 'This CSR is piloted by the BAAN Service Order ' . $csr->getModuleID(
                        ) . '. Please delete it first.';
                }
            }
            break;
        case 'edit':
            // if BAAN service order, allow only CSM
            switch ($csr->getModule()) {
                case 'SRVO':
                    if (!$user->isInGroupLevel('role_CSM', $csr->getSSOERP())) {
                        return 'Only CSM can edit CSR information when piloted by a BAAN Service Order';
                    }
                    break;
            }
            break;
        case 'status':
            // if BAAN service order, allow only CSM
            switch ($csr->getModule()) {
                case 'SRVO':
                    if (!$user->isInGroupLevel('role_CSM', $csr->getSSOERP())) {
                        return 'Only CSM can change CSR status piloted by a BAAN Service Order';
                    }
                    break;
            }
            break;
    }

    return true;
}
