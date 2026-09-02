<?php

use ApiBundle\Client;
use Symfony\Component\HttpClient\Exception\ClientException;

include_once 'publications.inc.php';
include_once 'forms_and_reports.inc.php';
include_once 'zip.inc.php';

ini_set('memory_limit', '768M');
ini_set('max_execution_time', '60');

$DEFAULT_TITLE .= "\Pubs";
$DEFAULT_MENU .= <<<EOF
		<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<a href="$php_self?m[0]=publications">Home</a>
		&nbsp;|&nbsp;<a href="$php_self?m[0]=publications&m[1]=manuals">Manuals</a>
		&nbsp;|&nbsp;<a href="$php_self?m[0]=publications&m[1]=documents">Documents</a>
EOF;

if ($user->isInGroup(['gg_SUPPORT', 'gg_ENG', 'gg_ADMIN'])) {
    $DEFAULT_MENU .= <<<EOF
		&nbsp;|&nbsp;<a href="/en/private/manuals/manuals_edit_documents.php?m=bom_form">Doc from BOM</a>
EOF;
}

tldUtils::logUserAccess();

if (!isset($lang) or !$lang) {
    $lang = 'en';
}
//DOCUMENT FUNCTIONS
switch ($m[1]) {
    case 'file':
        $template = '';
        if (isset($m[2]) && $m[2] === 'diagramImg') {
            $document = new document($_REQUEST['id']);
            $document->outFile();
            exit;
        }
        break;
    case 'manuals':
        $DEFAULT_TITLE .= "\Manuals";
        switch ($m[2]) {
            case 'view':
                if (empty($id) || !is_numeric($id)) {
                    $DEFAULT_ERROR[] = 'ERROR: Data sent empty or invalid!';
                    break;
                }
                $myManual = new manual($id);
                $header = $myManual->itsHeader;
                if (empty($header)) {
                    $DEFAULT_ERROR[] = "ERROR: Manual#$id not found.";
                    break;
                }
                $DEFAULT_TITLE .= "\Manual#$id";
                $DEFAULT_MENU .= <<<EOF
			<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="$php_self?m[0]=publications&m[1]=manuals&m[2]=view&id=$id">Home</a>&nbsp;|&nbsp;
			<a href="$php_self?m[0]=publications&m[1]=manuals&m[2]=view&m[3]=rspl&id=$id">RSPL</a>&nbsp;|&nbsp;
			<a href="$php_self?m[0]=publications&m[1]=manuals&m[2]=view&m[3]=pnref&id=$id">PN Ref</a>&nbsp;|&nbsp;
			<a href="$php_self?m[0]=publications&m[1]=manuals&m[2]=view&m[3]=cdrom&eqid=$eqid&id=$id">CDROM Manual</a>&nbsp;|&nbsp;
EOF;
                $DEFAULT_MENU .= '<a href="/en/private/pickup.php?url='.urlencode("$php_self?m[0]=publications&m[1]=manuals&m[2]=pdf&id=$id").'">Chapter 4 PDF</a>';
                if ($user->isInGroup(['gg_ADMIN', 'role_PSM', 'role_PSE', 'role_PSA', 'role_planner', 'GG_PARTS'])) {
                    $DEFAULT_MENU .= <<<EOF
				&nbsp;|&nbsp;<a href="$php_self?m[0]=publications&m[1]=manuals&m[2]=view&m[3]=evendor&eqid=$eqid&id=$id">eVendor Printing</a>
EOF;
                }
                if ($user->isInGroup(['manuals', 'gg_SUPPORT'])) {
                    $DEFAULT_MENU .= <<<EOF
				&nbsp;|&nbsp;<a href="$php_self?m[0]=publications&m[1]=manuals&m[2]=view&m[3]=doc&m[4]=quickEdit&id=$id" title="Documents quick edit">Quick edit</a>
				&nbsp;|&nbsp;<a href="$php_self?m[0]=publications&m[1]=manuals&m[2]=view&m[3]=log&id=$id">Logs</a>
				&nbsp;|&nbsp;<a href="/en/private/product_support/publications/manuals_admin.php?mode=record_view&form_type=main_tpl&id=$id">Admin</a>
EOF;
                }

                switch ($m[3]) {
                    case 'log':
                        $report = new tldReportColumnar(
                            $myManual->getLog(),
                            [
                                'xItems' => [
                                    'id' => 'ID#',
                                    'date' => 'Date',
                                    'poster_fullname' => 'Poster',
                                    'module' => 'Module',
                                    'comment' => 'Comment',
                                ],
                            ]
                        );
                        $body .= $report->fetch();
                        break;
                    case 'evendor':
                        $DEFAULT_TITLE .= "\eVendor Printing";
                        if (!$user->isInGroup(['gg_ADMIN', 'role_PSM', 'role_PSE', 'role_PSA', 'role_planner', 'GG_PARTS'])) {
                            $DEFAULT_ERROR[] = 'ERROR: You do not have the permission to access this module';
                            break;
                        }
                        $DEFAULT_MENU .= <<<EOF
			<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="$php_self?m[0]=publications&m[1]=manuals&m[2]=view&m[3]=evendor&id=$id&eqid=$eqid">Home</a>&nbsp;|&nbsp;
			<a href="$php_self?m[0]=publications&m[1]=manuals&m[2]=view&m[3]=evendor&m[4]=add&id=$id&eqid=$eqid">Add</a>&nbsp;&nbsp;
EOF;
                        switch ($m[4]) {
                            case 'add':
                                $DEFAULT_TITLE .= "\Add";
                                $erpList = tldLocation::getERPList('smartyOptions');
                                if (!empty($eqid)) {
                                    $eq = new tldEquipment($eqid);
                                    $default_erp = $eq->getFactoryERP();
                                }
                                $form = new HTML_QuickForm('AddManualEvendor', 'post');
                                $form->addElement('hidden', 'm[0]', 'publications');
                                $form->addElement('hidden', 'm[1]', 'manuals');
                                $form->addElement('hidden', 'm[2]', 'view');
                                $form->addElement('hidden', 'm[3]', 'evendor');
                                $form->addElement('hidden', 'm[4]', 'add');
                                $form->addElement('hidden', 'id', $id);
                                $form->addElement('header', 'title', "eVendor Printing for Manual #$id");
                                $form->addElement('text', 'er_id', 'ER #ID');
                                $form->addElement('select', 'erp', 'ERP', ['' => ''] + $erpList);
                                $form->addElement('submit', 'btnSubmit', 'Submit');
                                $form->addRule('erp', 'Required', 'required');
                                $form->addRule('er_id', 'Required', 'required');
                                $form->setDefaults(['er_id' => $eqid, 'erp' => $default_erp]);
                                if (!$form->validate()) {
                                    $body = $form->toHTML();
                                    break 2;
                                }

                                header("Location: $php_self?m[0]=publications&m[1]=manuals&m[2]=view&m[3]=evendor&m[4]=add2&er_id=$er_id&erp=$erp&id=$id");
                                exit;
                                break;
                            case 'add2':
                                $er_id = TldDatabase::escape($_REQUEST['er_id']);
                                $erp = TldDatabase::escape($_REQUEST['erp']);
                                if (empty($erp) || !is_numeric($erp)) {
                                    $DEFAULT_ERROR[] = 'ERROR: ERP invalid or not set';
                                    break;
                                }
                                if (empty($er_id) || !is_numeric($er_id)) {
                                    $DEFAULT_ERROR[] = 'ERROR: ER id sent empty or not valid';
                                    break;
                                }
                                $eq = new tldEquipment($er_id);
                                $header = $eq->getHeader();
                                if ($eq->isEmpty()) {
                                    $DEFAULT_ERROR[] = "ERROR: ER#$er_id not found";
                                    break;
                                }
                                $vendors = [];
                                $vendorsManual = tldVendor::getListByConstraints(['erp' => $erp, 'type' => 'manual_dwl']);
                                foreach ($vendorsManual as $vendor) {
                                    $vendors[$vendor['id']] = $vendor['fullname'].' - Supplier #'.$vendor['t_suno'].' - '.$vendor['erp_fullname'];
                                }
                                $form = new HTML_QuickForm('AddManualEvendor2', 'post');
                                $form->addElement('hidden', 'm[0]', 'publications');
                                $form->addElement('hidden', 'm[1]', 'manuals');
                                $form->addElement('hidden', 'm[2]', 'view');
                                $form->addElement('hidden', 'm[3]', 'evendor');
                                $form->addElement('hidden', 'm[4]', 'add2');
                                $form->addElement('hidden', 'id', $id);
                                $form->addElement('hidden', 'poster_id', $user->getID());
                                $form->addElement('hidden', 'erp', $erp);
                                $form->addElement('hidden', 'er_id', $er_id);
                                $form->addElement('header', 'title', "eVendor Printing for Manual #$id");
                                $form->addElement('text', 'er_display', 'ER #ID', ['disabled' => 'disabled']);
                                $form->addElement('text', 'erp_display', 'ERP', ['disabled' => 'disabled']);
                                $form->addElement('select', 'vendor_id', 'Vendor', ['' => ''] + $vendors);
                                $form->addElement('date', 'dt_delivery', 'Requested Delivery Date', ['format' => 'Y-m-d', 'addEmptyOption' => false, 'minYear' => date('Y'), 'maxYear' => date('Y') + 1]);
                                $form->addElement('text', 'std_manual', 'Standard');
                                $form->addElement('text', 'full_manual', 'Full');
                                $form->addElement('text', 'extra_cd', 'Extra CD-Rom');
                                $form->addElement('text', 'chapter_5', 'Chapter 5 (printed)');
                                $form->addElement('textarea', 'comment', 'Comments', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '4']);
                                $form->addElement('submit', 'btnSubmit', 'Submit');
                                $required = ['vendor_id', 'dt_delivery', 'std_manual', 'full_manual', 'extra_cd', 'chapter_5'];
                                foreach ($required as $key => $field) {
                                    $form->addRule($field, 'Required', 'required');
                                }
                                $form->setDefaults(['er_display' => $er_id, 'erp_display' => tldLocation::getLocationByERP($erp), 'dt_delivery' => date('Y-m-d'), 'std_manual' => '0', 'full_manual' => '0', 'extra_cd' => '0', 'chapter_5' => '0']);
                                if (!$form->validate()) {
                                    $body = $form->toHTML();
                                    break 2;
                                }
                                $vars = tldUtils::cleanupFormInput($form->exportValues());
                                if (!is_numeric($vars['std_manual']) || !is_numeric($vars['full_manual']) || !is_numeric($vars['extra_cd']) || !is_numeric($vars['chapter_5'])) {
                                    $DEFAULT_ERROR[] = 'ERROR: Standard, Full, Extra CD-Rom and Chapter 5 fields have to be numeric';
                                    $body = $form->toHTML();
                                    break;
                                }
                                if ($vars['std_manual'] == '0' && $vars['full_manual'] == 0) {
                                    $DEFAULT_ERROR[] = 'ERROR: Standard and Full fields are both set to 0. You need to request at least 1 type of manual';
                                    $body = $form->toHTML();
                                    break;
                                }
                                $vars['dt_delivery'] = implode('-', $vars['dt_delivery']);
                                // Insert into table
                                $e = $myManual->insertDownload($vars);
                                if (is_string($e)) {
                                    $DEFAULT_ERROR[] = "ERROR: Problem inserting the data...<br/>Reason: $e";
                                    break;
                                }
                                //Email Notification
                                $vendor = new tldVendor($vars['vendor_id']);
                                $message = 'Dear '.$vendor->getFullName().',<br><br>A new manual is available for download and ready to be printed.<br>';
                                $form = new tldReportColumnar(
                                    $vendor->getDownloads($e),
                                    [
                                        'xItems' => [
                                            'er_sn' => 'Unit SN',
                                            'model' => 'Unit Model',
                                            'customer' => 'Customer',
                                            'manual_id' => 'Manual ID#',
                                            'dt_delivery' => 'Expected Delivery Date',
                                            'std_manual' => 'Standard',
                                            'full_manual' => 'Full',
                                            'extra_cd' => 'Extra CD',
                                            'chapter_5' => 'Chapter 5',
                                            'comment' => 'Comment',
                                        ],
                                    ]
                                );
                                $report = $form->fetch();
                                $message .= $report;
                                $message .= "<br><a href='https://www.tld-gse.com/evendors/evendors.php?m[0]=manuals'>Please click here to access the manual</a>";
                                tldUtils::emailAttachment(
                                    $vendor->getEmail(),
                                    'noreply@tld-gse.com',
                                    'Manual Printing',
                                    $message,
                                    null,
                                    $user->getEmail()
                                );
                                //Logging
                                $log = $myManual->addLogEntry(
                                    $user->getID(),
                                    "Manual Download#$e created"
                                );
                                if (is_string($log)) {
                                    $DEFAULT_ERROR[] = "ERROR: Problem adding logs...<br/>Reason: $log";
                                }
                                break;
                            case 'delete':
                                if (!empty($manual_id)) {
                                    $e = manual::deleteDownload($manual_id);
                                    if (is_string($e)) {
                                        $DEFAULT_ERROR[] = "ERROR: Problem deleting Manual Download#$manual_id...<br/>Reason: $e";
                                        break;
                                    }
                                    $log = $myManual->addLogEntry(
                                        $user->getID(),
                                        "Manual Download#$manual_id deleted"
                                    );
                                    if (is_string($log)) {
                                        $DEFAULT_ERROR[] = "ERROR: Problem adding logs...<br/>Reason: $log";
                                    }
                                    $body .= "Manual Download#$manual_id deleted successfully!";
                                }
                                break;
                            case 'reset':
                                if (!empty($manual_id)) {
                                    $e = manual::resetDownload($manual_id);
                                    if (is_string($e)) {
                                        $DEFAULT_ERROR[] = "ERROR: Problem reseting Manual Download#$manual_id...<br/>Reason: $e";
                                        break;
                                    }
                                    $log = $myManual->addLogEntry(
                                        $user->getID(),
                                        "Manual Download#$manual_id hide/download parameters reseted to 0"
                                    );
                                    if (is_string($log)) {
                                        $DEFAULT_ERROR[] = "ERROR: Problem adding logs...<br/>Reason: $log";
                                    }
                                    $body .= "Manual Download#$manual_id reseted successfully!";
                                }
                                break;
                        }
                        $report = new tldReportColumnar(
                            $myManual->getDownloads(),
                            [
                                'xItems' => [
                                    'dwl_id' => 'ID#',
                                    'er_sn' => 'Unit SN',
                                    'model' => 'Unit Model',
                                    'customer' => 'Customer',
                                    'vendor' => 'Vendor',
                                    'manual_id' => 'Manual ID#',
                                    'dt_delivery' => 'Expected Delivery Date',
                                    'std_manual' => 'Standard',
                                    'full_manual' => 'Full',
                                    'extra_cd' => 'Extra CD',
                                    'chapter_5' => 'Chapter 5',
                                    'comment' => 'Comments',
                                    'downloaded' => 'Downloaded?',
                                    'hidden' => 'Hidden by Supplier?',
                                ],
                                'title' => 'Manual eVendor Printing',
                                'functions' => [
                                    'Reset' => [
                                        'url' => "/en/private/product_support/index.ps.php?m[0]=publications&m[1]=manuals&m[2]=view&m[3]=evendor&m[4]=reset&id=$id",
                                        'param' => ['manual_id' => 'dwl_id'],
                                        'confirmPopup' => 'Are you sure you want to reset the hide parameter?',
                                        'img' => '/shared/icons/application/refresh.png',
                                    ],
                                    'Delete' => [
                                        'url' => "/en/private/product_support/index.ps.php?m[0]=publications&m[1]=manuals&m[2]=view&m[3]=evendor&m[4]=delete&id=$id",
                                        'param' => ['manual_id' => 'dwl_id'],
                                        'confirmPopup' => 'Are you sure you want to delete this record?',
                                        'img' => '/shared/icons/application/delete.png',
                                    ],
                                ],
                            ]
                        );
                        $body .= $report->fetch();
                        $DEFAULT_ERROR[] = 'Note: A manual can only be downloaded one time per vendor. Use the Reset button to make a manual downloadable once more.';
                        break;
                    case 'doc':
                        $DEFAULT_TITLE .= "\Doc details";
                        $DEFAULT_MENU .= <<<EOF
				<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<a href="$php_self?m[0]=publications&m[1]=manuals&m[2]=view&m[3]=doc&m[4]=add&id=$id" title="Add document">Add</a>
EOF;
                        switch ($m[4]) {
                            case 'add':
                                $DEFAULT_TITLE .= "\Add Doc";
                                $form = new HTML_QuickForm('SearchDiag', 'post');
                                $form->addElement('hidden', 'm[0]', 'publications');
                                $form->addElement('hidden', 'm[1]', 'manuals');
                                $form->addElement('hidden', 'm[2]', 'view');
                                $form->addElement('hidden', 'm[3]', 'doc');
                                $form->addElement('hidden', 'm[4]', 'add');
                                $form->addElement('hidden', 'id', $id);
                                $form->addElement('header', 'title', 'Search Documents to add');
                                $form->addElement('header', 'title', 'Will show up to 50 records only');
                                $form->addElement('text', 'target', 'Search');
                                $form->addElement('submit', 'btnSubmit', 'Submit');
                                $form->addRule('target', 'Required', 'required');
                                $body = $form->toHTML();
                                if ($form->validate()) {
                                    $vars = $form->exportValues();
                                    $rows = manual::searchDoc($vars['target'], 50);
                                    $report = new tldReportColumnar($rows, ['xItems' =>
                                            [
                                                'id' => 'Click to ADD<br/>Doc#',
                                                'rev' => 'Revision',
                                                'category' => 'Category',
                                                'factory_num' => 'Factory Num',
                                                'endescription' => 'EN Description'],
                                            'title' => 'Documents search result',
                                            'links' => ['id' => "$php_self?m[0]=publications&m[1]=manuals&m[2]=view&m[3]=doc&m[4]=add1&id=$id&docid="],
                                        ]
                                    );
                                    $body .= $report->fetch();
                                }
                                break 2;
                                break;
                            case 'add1':
                                $DEFAULT_TITLE .= "\Add Doc";
                                if (empty($docid) || !is_numeric($docid)) {
                                    $DEFAULT_ERROR[] = 'ERROR: Data sent empty or invalid!';
                                    break;
                                }
                                $e = $myManual->addDoc($docid, null);
                                if (is_string($e)) {
                                    $DEFAULT_ERROR[] = "INTERNAL ERROR: Document not Added!<br/>Reason: $e";
                                } else {
                                    $body = "Document#$docid added successfully to Manual#$id!";
                                }
                                break;
                            case 'quickUpdate':
                                $e = $myManual->updateDocPositions(tldUtils::cleanupFormInput($_POST['item']));
                                if (is_array($e)) {
                                    $DEFAULT_ERROR[] = 'ERROR: Quick edit Documents return some errors listed below:<br/>'.implode('<br/>', $e);
                                } else {
                                    $body = "Documents updated for manual#$id";
                                }
                                break;
                            case 'delete':
                                if (empty($docid) || !is_numeric($docid)) {
                                    $DEFAULT_ERROR[] = 'ERROR: Data sent empty or invalid!';
                                    break;
                                }
                                $e = $myManual->removeDoc($docid);
                                if (is_string($e)) {
                                    $DEFAULT_ERROR[] = "INTERNAL ERROR: Document not deleted!<br/>Reason: $e";
                                } else {
                                    $body = 'Document deleted successfully!';
                                }
                                break;
                        }
                        $smarty->assign('id', $id);
                        $smarty->assign('lines', $myManual->getDoc());
                        $body .= $smarty->fetch("$PATH/publications/manuals.edit.tpl");
                        break;
                    case 'cdrom':

                        $body .= <<<EOF
					<br><font color="#FF0000"> WARNING: You are not supposed to use this downloading function for anything else that providing a supplemental CD Rom manual to the customer of this specific piece of equipment. 
					Downloading full manuals on computers hard drives is strictly prohibited, no matter if they are TLD computers or personal computers.
					By continuing this process, you should also know that a formal notification of your action will be automatically sent to the TLD PSM's and to your supervisor.
					Are you sure you want to proceed?.<br> 
					Please click <a href="/en/private/product_support/publications/zipped.php?id=$id&eqid=$eqid">here</a> to confirm.<br>
EOF;

                        break;
                    case 'pnref':
                        $myManual->outPNRefIndex();
                        exit;
                        break;
                    case 'rspl':
                        $DEFAULT_MENU .= <<<EOF
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=publications&m[1]=manuals&m[2]=view&m[3]=rspl&id=$id">Combined RSPL</a>
(<a href="$php_self?m[0]=publications&m[1]=manuals&m[2]=view&m[3]=rspl&m[5]=pdf&id=$id">PDF</a>&nbsp;|&nbsp;
<a href="/en/private/parts/parts.php?m[0]=inv&m[1]=listing&m[2]=byRSPL&id=$id">Inventory</a>)
&nbsp;|&nbsp;<a href="$php_self?m[0]=publications&m[1]=manuals&m[2]=view&m[3]=rspl&m[4]=p&id=$id">P-RSPL</a>
(<a href="$php_self?m[0]=publications&m[1]=manuals&m[2]=view&m[3]=rspl&m[4]=p&m[5]=pdf&id=$id">PDF</a>&nbsp;|&nbsp;
<a href="/en/private/parts/parts.php?m[0]=inv&m[1]=listing&m[2]=byRSPL&m[3]=p&id=$id">Inventory</a>)
&nbsp;|&nbsp;<a href="$php_self?m[0]=publications&m[1]=manuals&m[2]=view&m[3]=rspl&m[4]=m&id=$id">M-RSPL</a>
(<a href="$php_self?m[0]=publications&m[1]=manuals&m[2]=view&m[3]=rspl&m[4]=m&m[5]=pdf&id=$id">PDF</a>&nbsp;|&nbsp;
<a href="/en/private/parts/parts.php?m[0]=inv&m[1]=listing&m[2]=byRSPL&m[3]=m&id=$id">Inventory</a>)
&nbsp;|&nbsp;<a href="$php_self?m[0]=publications&m[1]=manuals&m[2]=view&m[3]=rspl&m[4]=o&id=$id">O-RSPL</a>
(<a href="$php_self?m[0]=publications&m[1]=manuals&m[2]=view&m[3]=rspl&m[4]=o&m[5]=pdf&id=$id">PDF</a>&nbsp;|&nbsp;
<a href="/en/private/parts/parts.php?m[0]=inv&m[1]=listing&m[2]=byRSPL&m[3]=o&id=$id">Inventory</a>)
&nbsp;|&nbsp;<a href="$php_self?m[0]=publications&m[1]=manuals&m[2]=view&m[3]=rspl&m[4]=c&id=$id">C-RSPL</a>
(<a href="$php_self?m[0]=publications&m[1]=manuals&m[2]=view&m[3]=rspl&m[4]=c&m[5]=pdf&id=$id">PDF</a>&nbsp;|&nbsp;
<a href="/en/private/parts/parts.php?m[0]=inv&m[1]=listing&m[2]=byRSPL&m[3]=c&id=$id">Inventory</a>)
&nbsp;|&nbsp;<a href="$php_self?m[0]=publications&m[1]=manuals&m[2]=view&m[3]=rsplHelp&id=$id">HELP</a>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/en/private/parts/parts.php?m[0]=cart&m[1]=import&m[2]=byRSPL&m[3]=${m[4]}&id=$id">Transfer to cart</a>
EOF;
                        $rows = [];
                        foreach ($myManual->getRSPL($m[4]) as $v) {
                            if (!isset($rows[$v['pn']])) {
                                $rows[$v['pn']] = $v;
                            }
                        }

                        if (!$rows) {
                            $DEFAULT_ERROR[] = "ERROR: No ${m[4]} category RSPL found for this manual";
                            break;
                        }
                        $fields = [
                            'pn' => 'PN',
                            'qty' => 'Qty',
                            'en' => 'Description',
                        ];
                        if (empty($m[4]) || $m[4] === 'p') {
                            $fields['group_p'] = 'P';
                        }
                        if (empty($m[4]) || $m[4] === 'm') {
                            $fields['group_m'] = 'M';
                        }
                        if (empty($m[4]) || $m[4] === 'o') {
                            $fields['group_o'] = 'O';
                        }
                        if (empty($m[4]) || $m[4] === 'c') {
                            $fields['group_c'] = 'C';
                        }
                        $report = new tldReportMultiLevel($rows,
                            ['category'],
                            $fields,
                            ['title' => 'RSPL']
                        );
                        if ($m[5] === 'pdf') {
                            $pdf = new html2pdf($report->fetch());
                            $pdf->outFile();
                            exit;
                        }

                        $body .= $report->fetch();
                        break;
                    case 'rsplHelp':
                        $body .= $smarty->fetch("$PATH/publications/manuals/rspl.help.tpl");
                        break;
                    default:
                        $body .= <<<EOF
				<form action="$php_self">
				<input type="hidden" name="m[0]" value="publications">
				<input type="hidden" name="m[1]" value="manuals">
				<input type="hidden" name="m[2]" value="view">
				<input type="hidden" name="id" value="$id">
				<input type="text" name="target" value="" onfocus="this.value=''">
				<input type="submit" name="submit" value="Find">
				</form>
EOF;
                        $smarty->assign('id', $id);
                        //put manual together
                        $smarty->assign('header', $header);
                        $smarty->assign('docType', $docType);
                        $smarty->assign('docCat', $docCat);
                        $smarty->assign('documents', $myManual->getCategorizedDetails($target));
                        $body .= $smarty->fetch("$PATH/publications/manuals/manual.tpl");
                }
                break;
            case 'form':
                if (empty($id) || !is_numeric($id) || empty($eqid) || !is_numeric($eqid)) {
                    $DEFAULT_ERROR[] = 'ERROR: Data sent empty or invalid!';
                    break;
                }

                $eq = new tldEquipment($eqid);
                if (!empty($_POST) && 'POST' === strtoupper($_SERVER['REQUEST_METHOD'])) {
                    $body = '';
                    $post = $_POST;
                    unset($post['erp'], $post['model'], $post['serialNumber']);

                    // Generate homepage
                    $smarty->assign('model', $model);
                    $smarty->assign('serialNumber', $serialNumber);
                    $image = ($eq->itsDetails['man_location'] === 'AERO Specialties') ? 'aero' : 'tld-2inch';
                    $smarty->assign('image', $image);
                    $html2pdf = new tldHTML2PDF($smarty->fetch("$PATH/publications/manuals/homepage.pdf.manuals.tpl"), ['encoding' => 'utf-8']);
                    $html2pdf->convert();
                    $html2pdf->itsConvertedPdfFile->getFilePath();
                    // Prepend the file to the array so the blank page will be inserted correctly later
                    array_unshift($post, $html2pdf->itsConvertedPdfFile->getFilePath());

                    $files = [];
                    $totalPageNumber = 0;

                    $CACHE_MANUAL_DIR = "$HOME_DIR/cache/current/manuals";
                    $chapter5 = false;

                    $blankFile = '/tmp/blank.pdf';
                    if (!file_exists($blankFile)) {
                        exec("gs -dBATCH -dNOPAUSE -q -dAutoRotatePages=/None -sDEVICE=pdfwrite -sOutputFile=$blankFile ", $output);
                    }

                    foreach ($post as $key => $filename) {
                        // Generate Chapter 4
                        if ($filename === 'CH4-generation') {
                            $filename = "/tmp/chapter4-$id.pdf";
                            $myManual = new manual($id);
                            $pdf = new tldOnlineManualPDF($id, $erp, $myManual->getLang());
                            $pdf->Output($filename);
                        }

                        if (strpos($filename, '..') || !file_exists($filename)) {
                            continue;
                        }

                        if (0 >= $pageNumber = (int)exec("pdftk $filename dump_data | awk '/NumberOfPages/ {print $2}'")) {
                            // Filter out empty pdf
                            continue;
                        }

                        $files[] = $filename;
                        $totalPageNumber += $pageNumber;
                        if (false !== strpos($key, 'Chapter5')) {
                            $chapter5 = true;
                        }

                        if (1 === $pageNumber % 2) {
                            $files[] = $blankFile;
                            ++$totalPageNumber;
                        }
                    }

                    if ($chapter5 && 0 !== $totalPageNumber % 4) {
                        // Chapter 5 is usually printed with two pages per sheets
                        // If the document doesn't contain a multiple of 4 total number of pages, the last document might be printed incorrectly
                        $files[] = $blankFile;
                        $files[] = $blankFile;
                    }

                    $output = [];

                    $resultFile = tempnam($CACHE_MANUAL_DIR, 'gigi').'.pdf';
                    exec("gs -dBATCH -dNOPAUSE -q -dAutoRotatePages=/None -sDEVICE=pdfwrite -sOutputFile=$resultFile ".implode(' ', $files), $output);

                    $file = new basicFile($resultFile);
                    if (!$file->isFile()) {
                        error_log('ERROR: Generated file not found');
                    }

                    $file->outFile();
                    exit;
                }


                $myManual = new manual($id);
                if (!$myManual->itsHeader) {
                    $DEFAULT_ERROR[] = "ERROR: Manual#$id not found.";
                    break;
                }

                $erp = tldLocation::getERPByLocation($eq->itsDetails['man_location']);
                if (null === $erp) {
                    $DEFAULT_ERROR[] = "ERROR: Could not get the ERP from the ER";
                    break;
                }

                $container = $kernel->getContainer();
                $client = $container->get(Client::class);
                try {
                    $signalCodes = ['ESC', 'HSC', 'FLD'];
                    $cbom = $client->get(
                        sprintf('/ion/customized_bill_of_materials/site=%d;project=%s', $erp, $eq->itsDetails['t_prno']),
                        [
                            'query' => [
                                'productSignalCodeFilter' => implode('|', $signalCodes),
                                'productSignalCodeFilterMethod' => 'Equals',
                                'productSignalCodeAttribute' => 'engineeringSignalCode',
                                'itemsSignalCodeFilter' => implode('|', $signalCodes),
                                'itemsSignalCodeFilterMethod' => 'Equals',
                                'itemsSignalCodeAttribute' => 'engineeringSignalCode',
                                'depth' => 20,
                                'flatResult' => 1,
                            ]
                        ]
                    );

                    $tldCBOM = new tldCBOM($eq->getERP(), $date, $eq->itsDetails['t_prno'], ['lang' => $selang], $cbom['items']);
                    $extraSchematics = $tldCBOM->itsBOMAsArray;
                } catch (ClientException $exception) {
                    $DEFAULT_ERROR[] = "ERROR: CBOM not found.";
                    break;
                }

                $details = $myManual->getCategorizedDetails();
                $chapters = $details['MANUAL:SECTION'];
                if ($drivingManual) {
                    $chapters['Chapter 2'] = []; // Still needed to merge schematics
                    unset($chapters['Chapter 3']);
                }

                foreach ($chapters as $c => $chapter) {
                    foreach ($chapter as $key => $document) {
                        $chapters[$c][$key]['filepath'] = document::getFilePath($UPLOADS_PATH, $document['diagram_filename']);
                    }
                }

                if (!$drivingManual) {
                    $chapters['Chapter 4'] = [['endescription' => "CH4, [{$myManual->getLang()}]", 'filepath' => 'CH4-generation']];
                }

                $rc = new tldReleasedController();
                /** @var basicVault $v */
                $v = $rc->theVaultList[$erp];
                if (!$v instanceof basicVault) {
                    $DEFAULT_ERROR[] = "ERROR: Could not get the Vault for ERP $erp";
                    break;
                }

                $schematics = [];
                foreach ($tldCBOM->itsBOMAsArray as $schematic) {
                    $schematics[] = [
                        'signalCode' => $schematic['t_csig'],
                        'endescription' => $schematic['t_dsca'],
                        'item' => $schematic['t_sitm'],
                        'filepath' => $v->getPathToItemFile($schematic['partNumber'], $schematic['t_revi'], 'pdf'),
                    ];
                }

                usort($schematics, static function ($a, $b) use ($signalCodes) {
                    if ($a['signalCode'] === $b['signalCode']) {
                        if (($posA = strpos($a['item'], '-')) && ($posB = strpos($b['item'], '-'))) {
                            return (int)$posA < (int)$posB ? -1 : 1;
                        }

                        return 0;
                    }

                    return (array_search($a['signalCode'], $signalCodes, true) < array_search($b['signalCode'], $signalCodes, true)) ? -1 : 1;
                });
                $chapters['Chapter 2'] = array_merge($chapters['Chapter 2'] ?? [], $schematics);

                ksort($chapters);

                $smarty->assign('drivingManual', $drivingManual);
                $smarty->assign('chapters', $chapters);
                $smarty->assign('model', $eq->itsDetails['model']);
                $smarty->assign('sn', $eq->getSN());
                $body .= $smarty->fetch("$PATH/publications/manuals/form.pdf.manuals.tpl", ['encoding' => 'utf-8']);
                break;
            case 'pdf':
                if (empty($id) || !is_numeric($id)) {
                    $DEFAULT_ERROR[] = 'ERROR: Manual ID sent empty or invalid!';
                    break;
                }

                $myManual = new manual($id);
                $header = $myManual->getHeader();
                if (empty($header)) {
                    $DEFAULT_ERROR[] = "ERROR: Manual#$id not found.";
                    break;
                }
                // ASIA download allowed list per request (see task#83593):
                $gg_ASIA_DOWNLOAD_ALLOWED = [
                    64,    // allen.fu@tld-asia.com
                    954,    // chris.tam@tld-asia.com
                    66,        // alex.lam@tld-asia.com
                    322,    // mei.hou@tld-asia.com
                    774,    // jenny.chen@tld-asia.com
                    758,    // qianmin.tang@tld-asia.com
                    103        // peter.ng@tld-asia.com
                ];
                // only persons from tld-asia.com in the authorized group can download CDROM
                if (in_array((int)$user->itsDetails['location'], [3, 4, 24, 25, 34], true)
                    && !in_array((int)$user->itsId, $gg_ASIA_DOWNLOAD_ALLOWED, true)
                    && !$user->isInGroup(['gg_SUPPORT'])
                ) {
                    error_log('An unauthorized person tried to download a zipped CDROM manual#'.$id.' (ER id#'.$eqid.') :'.$user->itsDetails['email'], 1, 'devteam@tld-america.com');
                    // log the download even in the logs
                    tldUtils::log_event('unauthorized CDROM manual#'.$id.' (ER id#'.$eqid.') Download attempt by: '.$user->itsDetails['email']);
                    $DEFAULT_ERROR[] = "<br><font color=\"#FF0000\">ERROR: you do not have the permissions to access this function.\n";
                    break;
                }
                // Generate and send PDF
                $pdf = new tldOnlineManualPDF($id, $erp, $myManual->getLang());
                $pdf->Output();
                exit;
                break;
            case 'countByModelLang':
                if (!empty($byFamily)) {
                    $mode = 'byFamily';
                    $form = new tldMatrix(manual::countByModelLang($mode, $byFamily),
                        'lang', 'model', 'num',
                        "$php_self?m[0]=publications&m[1]=list&m[2]=byModelLang",
                        'Number of manuals per model family '.$byFamily.', 
								per language', ['doNotShowTotals' => true]
                    );
                    $body .= $form->fetch();
                } else {
                    $form = new tldHTMLList(tldModel::getFamilies(),
                        ['key' => 'Families', 'value' => 'Families'],
                        "$php_self?m[0]=publications&m[1]=manuals&m[2]=${m[2]}&byFamily=",
                        ['title' => 'Please select Model']
                    );
                    $body = $form->fetch();
                }
                break;
            case 'countByModelPublishable':
                if (!empty($byFamily)) {
                    $mode = 'byFamily';
                    $form = new tldMatrix(manual::countByModelPublishable($mode, $byFamily),
                        'publishable', 'model', 'num',
                        "$php_self?m[0]=publications&m[1]=list&m[2]=byModelPublishable",
                        'Number of Publishable manuals per model family '.$byFamily,
                        ['doNotShowTotals' => true]
                    );
                    $body .= $form->fetch();
                } else {
                    $form = new tldHTMLList(tldModel::getFamilies(),
                        ['key' => 'Families', 'value' => 'Families'],
                        "$php_self?m[0]=publications&m[1]=manuals&m[2]=${m[2]}&byFamily=",
                        ['title' => 'Please select Model']
                    );
                    $body = $form->fetch();
                }
                break;
            case 'search':
                switch ($m[3]) {
                    case 'byCustomerPN':
                        $form = new HTML_QuickForm('frmSearchByCustomerPN', 'post');
                        $form->addElement('header', 'title', 'Customer Name and Part Number Cross Reference');
                        $form->addElement('hidden', 'm[0]', 'publications');
                        $form->addElement('hidden', 'm[1]', 'manuals');
                        $form->addElement('hidden', 'm[2]', 'search');
                        $form->addElement('hidden', 'm[3]', 'byCustomerPN');
                        $form->addElement('text', 'cu_nama', 'Customer Name',
                            ['size' => '20']
                        );
                        $form->addElement('text', 'pn', 'Part Number',
                            ['size' => '20']
                        );
                        $form->addElement('submit', 'btnSubmit', 'Submit');
                        $form->addRule('cu_nama', 'This is required', 'required');
                        $form->addRule('pn', 'This is required', 'required');
                        if ($form->validate()) {
                            # If the form validates then freeze the data
                            $form->freeze();
                            $smarty->assign('docs', manual::byCustomerPN($cu_nama, $pn));
                            $smarty->assign('heading', "Results for $cu_nama and $pn from Documents");
                            $body .= $smarty->fetch("$PATH/publications/manuals/search/docs.list.tpl");
                        } else {
                            $body = $form->toHTML();
                        }
                        break;
                    case 'byBrandModel':
                        switch ($m[4]) {
                            case 'brand':
                                if (!$id) {
                                    $DEFAULT_ERROR[] = 'No Brand was selected...';
                                } else {
                                    $form = new tldHTMLList(manual::getModels("brand='$id'"),
                                        ['key' => 'model', 'value' => 'model'],
                                        "$php_self?m[0]=publications&m[1]=manuals&m[2]=search&m[3]=byBrandModel&m[4]=model&id=",
                                        ['title' => 'Step 2: Please select Model of equipment...']
                                    );
                                    $body .= $form->fetch();
                                }
                                break;
                            case 'model':
                                if (!$id) {
                                    $DEFAULT_ERROR[] = 'No Model was selected...';
                                } else {
                                    $rows = manual::getManuals("model='$id'");
                                }
                                break;
                            default:
                                $form = new tldHTMLList(manual::getBrands(),
                                    ['key' => 'brand', 'value' => 'brand'],
                                    "$php_self?m[0]=publications&m[1]=manuals&m[2]=search&m[3]=byBrandModel&m[4]=brand&id=",
                                    ['title' => 'Step 1: Please select Brand of equipment...']
                                );
                                $body .= $form->fetch();
                        }
                        break;
                }
                if (isset($rows) && count($rows)) {
                    $form = new tldReportMultiLevel($rows,
                        ['id'],
                        ['id' => 'Manual#',
                            'date' => 'Date',
                            'brand' => 'Brand',
                            'model' => 'Model',
                            'description' => 'Description',
                            'features' => 'Features',
                        ],
                        ['passField' => 'id',
                            'title' => 'Please select Manual...',
                            'url' => "$php_self?m[0]=publications&m[1]=manuals&m[2]=view&id="]
                    );
                    $body .= $form->fetch();
                }
                break;
            default:
                $body .= $smarty->fetch("$PATH/publications/manuals/homepage.manuals.tpl");
        }
        break;
    case 'documents':
        $DEFAULT_TITLE .= "\Documents";
        switch ($m[2]) {
            case 'viewImage':
                $document = new document($id);
                //get header
                $headerArray = $document->itsHeader;
                if (empty($headerArray)) {
                    return "$id NOT FOUND.";
                }
                $smarty->assign('header', $headerArray);
                $body .= $smarty->fetch("$PATH/publications/documents/document.image.tpl");
                $template = 'intranet.plain.tpl';
                break;
            case 'view':
                if ($id) {
                    $document = new document($id, null, $lang);
                    //get header
                    $headerArray = $document->itsHeader;
                    if ($headerArray['doc_type'] !== 'MANUAL:SECTION') {
                        $DEFAULT_MENU .= <<<EOF
				<br>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<a href="$php_self?m[0]=publications&m[1]=documents&m[2]=pdf&lang=$lang&id=$id">PDF</a>
EOF;
                    }
                    if ($user->isInGroup('gg_SUPPORT')) {
                        $DEFAULT_MENU .= <<<EOF
				&nbsp;|&nbsp;
<a href="/en/private/product_support/publications/documents_admin.php?mode=record_view&form_type=main_tpl&id=$id">Edit</a>
EOF;
                    }
                    if (empty($headerArray)) {
                        return "$id NOT FOUND.";
                    }
                    $smarty->assign('header', $headerArray);
                    $smarty->assign('brand', $REQUEST_VARS['brand']);
                    //get detail
                    $smarty->assign('detail', $document->getDetailArray());
                    $body .= $smarty->fetch("$PATH/publications/documents/document.tpl");
                } else {
                    $DEFAULT_ERROR[] = 'No Document ID set';
                }
                break;
            case 'pdf':
                $pdf = new tldOnlineDocumentPDF($id, $erp, $lang);
                $pdf->Output();
                $template = 'NO_TEMPLATE';
                break;
                break;
            case 'csv':
                $xItems = [
                    'man_id' => 'Manual#',
                    'brand' => 'Brand',
                    'model' => 'Model',
                    'sn' => 'SN#',
                    'cusname' => 'Customer (Name)',
                    'doc_id' => 'Document#',
                    'category' => 'Category',
                    'endescription' => 'Description',
                ];
                $rows = document::findDocsContainingPNbyER($id);
                if ($rows) {
                    $report = new tldCSV(
                        $rows,
                        [
                            'xItems' => $xItems,
                            'title' => "Results for '$id' from Documents",
                        ]
                    );
                    $report->out();
                    exit;
                } else {
                    $DEFAULT_ERROR[] = "ERROR: No entries for $id...";
                }
                break;
            case 'search':
                switch ($m[3]) {
                    case 'byPartNumber':
                        if ($id) {
                            $xItems = [
                                'man_id' => 'Manual#',
                                'brand' => 'Brand',
                                'model' => 'Model',
                                'doc_id' => 'Document#',
                                'category' => 'Category',
                                'endescription' => 'Description',
                            ];
                            $rows = document::findDocsContainingPN($id);
                            if ($rows) {
                                $report = new tldReportColumnar(
                                    $rows,
                                    [
                                        'xItems' => [
                                            'man_id' => 'Manual#',
                                            'brand' => 'Brand',
                                            'model' => 'Model',
                                            'doc_id' => 'Document#',
                                            'category' => 'Category',
                                            'endescription' => 'Description',
                                        ],
                                        'title' => "Results for '$id' from Documents",
                                        'links' => ['man_id' => '/en/private/product_support/index.ps.php?m[0]=publications&m[1]=manuals&m[2]=view&id=',
                                            'doc_id' => '/en/private/product_support/index.ps.php?m[0]=publications&m[1]=documents&m[2]=view&id='],
                                    ]
                                );
                                $body .= $report->fetch();
                            } else {
                                $DEFAULT_ERROR[] = "ERROR: No entries for $id...";
                            }
                        } else {
                            $body .= $smarty->fetch("$PATH/publications/manuals/search/form.search.byPartNumber.tpl");
                        }
                        break;
                    case 'byPartNumberER':
                        if ($id) {
                            $DEFAULT_MENU .= <<<EOF
                        <br/>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<a href="$php_self?m[0]=publications&m[1]=documents&m[2]=csv&lang=$lang&id=$id">CSV</a>
EOF;
                            $xItems = [
                                'man_id' => 'Manual#',
                                'brand' => 'Brand',
                                'model' => 'Model',
                                'sn' => 'SN#',
                                'cusname' => 'Customer (Name)',
                                'doc_id' => 'Document#',
                                'category' => 'Category',
                                'endescription' => 'Description',
                            ];
                            $rows = document::findDocsContainingPNbyER($id);
                            if ($rows) {
                                $report = new tldReportColumnar(
                                    $rows,
                                    [
                                        'xItems' => [
                                            'man_id' => 'Manual#',
                                            'brand' => 'Brand',
                                            'model' => 'Model',
                                            'sn' => 'SN#',
                                            'cusname' => 'Customer (Name)',
                                            'doc_id' => 'Document#',
                                            'category' => 'Category',
                                            'endescription' => 'Description',
                                        ],
                                        'title' => "Results for '$id' from Documents",
                                        'links' => ['man_id' => '/en/private/product_support/index.ps.php?m[0]=publications&m[1]=manuals&m[2]=view&id=',
                                            'sn' => '/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=search&m[2]=bySN&sn=',
                                            'doc_id' => '/en/private/product_support/index.ps.php?m[0]=publications&m[1]=documents&m[2]=view&id='],
                                    ]
                                );
                                $body .= $report->fetch();
                            } else {
                                $DEFAULT_ERROR[] = "ERROR: No entries for $id...";
                            }
                        } else {
                            $body .= $smarty->fetch("$PATH/publications/manuals/search/form.search.byPartNumberER.tpl");
                        }
                        break;
                }
                break;
            default:
                $body .= $smarty->fetch("$PATH/publications/documents/homepage.documents.tpl");
        }
        break;
    case 'search':
        switch ($m[2]) {
            case 'manualID':
                $body .= performSearch($_REQUEST, 'byBrandModel');
                break;
        }
        break;
    case 'list':
        switch ($m[2]) {
            case 'byModelLang':
                $params['model'] = TldDatabase::escape($y);
                $params['lang'] = TldDatabase::escape($x);

                $rows = manual::byQuery($params);
                $body .= _listManuals($rows, 'By Model'.$params['model'].', Language '.$params['lang'].'');
                break;
            case 'byModelPublishable':
                $params['model'] = TldDatabase::escape($y);
                $params['publishable'] = TldDatabase::escape($x);

                $rows = tldEquipment::byConstraints($params);
                $body .= _listERs($rows, 'Publishable By Model'.$params['model'].'');
                break;
        }
        break;
    default:
        $body .= $smarty->fetch("$PATH/publications/homepage.publications.tpl");
}


function performSearch($REQUEST_VARS, $searchType)
{
    if (empty($searchType)) {
        return;
    }
    $smarty = getSmarty();
    $REQUEST_VARS = tldUtils::cleanupFormInput($REQUEST_VARS);
    $offset = $REQUEST_VARS['offset'];
    $MAX_ROWS_PER_PAGE = 20;
    if (empty($REQUEST_VARS['offset'])) {
        $offset = 0;
    }

    switch ($searchType) {
        case 'manualsByDocNum':
            $id = TldDatabase::escape($REQUEST_VARS['id']);
            $query = <<<EOF
			SELECT manuals.*
			FROM manuals, manuals_docs
			WHERE manuals.id = manuals_docs.parent_id AND
			manuals_docs.doc_num=$id
EOF;
            $smarty->assign('NEXT_STEP', 'm[0]=publications&m[1]=search&m[2]=manualsByDocNum&id='.urlencode($id));
            $template = 'private/search/manual.list.tpl';
            break;
        case 'byCustomer':
            $customer = TldDatabase::escape($REQUEST_VARS['customer']);
            $query = <<<EOF
			SELECT service. * , service_serials. * 
			FROM service, service_serials
			WHERE customer_name
			LIKE '$customer%' AND (
			service.id = service_serials.parent_id OR service_serials.parent_id IS NULL 
			) AND service_serials.component = 'MANUAL'
EOF;
            $smarty->assign('NEXT_STEP', 'm[0]=publications&m[1]=search&m[2]=byCustomer&customer='.urlencode($REQUEST_VARS['customer']));
            $template = 'private/search/equipment.record.list.tpl';
            break;
        case 'bySerialNumber':
            $sn = TldDatabase::escape(rtrim($REQUEST_VARS['sn']));
            $query = <<<EOF
			SELECT service. * , service_serials. * 
			FROM service, service_serials
			WHERE sn
			LIKE '$sn%' AND (
			service.id = service_serials.parent_id OR service_serials.parent_id IS NULL 
			) AND service_serials.component = 'MANUAL'
			LIMIT 50 
EOF;
            $smarty->assign('NEXT_STEP', 'm[0]=publications&m[1]=search&m[2]=bySerialNumber&sn='.urlencode($REQUEST_VARS['sn']));
            $template = 'private/search/equipment.record.list.tpl';
            break;
    }

    $rows = tldUtils::getSqlToAssocArray($query);
    $smarty->assign('count', count($rows));
    $smarty->assign('offset', $offset);
    $smarty->assign('limit', $MAX_ROWS_PER_PAGE);
    //get detail
    if (is_array($rows)) {
        $smarty->assign('rows', array_slice($rows, $offset, $MAX_ROWS_PER_PAGE));
    }
    return $smarty->fetch($template);
}


function _listManuals($rows, $title = '')
{
    $report = new tldReportColumnar($rows,
        ['xItems' => ['id' => 'Manual#',
            'date' => 'Date',
            'brand' => 'Brand',
            'model' => 'Model',
            'description' => 'Description',
            'features' => 'Features',
            'lang' => 'Language'],
            'links' => ['id' => "$php_self?m[0]=publications&m[1]=manuals&m[2]=view&id="],
            'title' => $title,
        ]
    );
    return $report->fetch();
}

function _listERs($rows, $title = '')
{
    $report = new tldReportColumnar($rows,
        ['xItems' => ['id' => 'Equipment ID#',
            'sn' => 'Equipment SN#',
            'status' => 'Status',
            'type' => 'Type',
            'model' => 'Model',
            'man_location' => 'Manufacturer location',
            'apc_fullname' => 'Airport',
            'location_short' => 'Unit Location Short',
            'sales_org' => 'Sales Organization',
            't_prno' => 'MFG Project#',
            'publishable' => 'CBOM Manual Publishable'],
            'links' => ['id' => "$php_self?m[0]=equipment&m[1]=view&id="],
            'title' => $title,
        ]
    );
    return $report->fetch();
}
