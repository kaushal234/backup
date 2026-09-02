<?php

use ApiBundle\Client;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

$DEFAULT_TITLE .= "\Summary";

// Get the SSO switch system
include 'sb.form.sso.switch.php';
// Include filter form
include 'sb.form.er.filter.php';

switch ($m[3]) {
    case 'implementation':
        $DEFAULT_TITLE .= "\Implementation dashboard";
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sb&m[1]=view&m[2]=summary&m[3]=implementation&out=xls&id=$id">XLS version</a>
EOF;
        // Check status
        if (!$sb->isAnyImplementationStatus() && !$sb->isClosedStatus()) {
            $DEFAULT_ERROR[] = 'ERROR: Status must be (PARTIAL_)IMPLEMENTATION to use this feature';
            break;
        }

        // Check if signed
        if (!$sb->isSignedByStatusBySSOID('SSD_DECISION', $SSO->getID())) {
            $DEFAULT_ERROR[] = 'ERROR: Implementation decision not possible yet.';
            $DEFAULT_ERROR[] = 'SB not signed yet for SSO ' . $SSO->getShortName();
            break;
        }

        // Check if signed
        if (!$sb->isFactoryPartsAvailabilityOk()) {
            $DEFAULT_ERROR[] = 'ERROR: Implementation decision not possible yet.';
            $DEFAULT_ERROR[] = 'Factory has not confirmed enough parts availability.';
            break;
        }

        $smarty->assign('width', 1200);

        // ACL -------------------------------->
        $ssoErp = $SSO->getERP();
        // List of dashboard features acl
        $acl_LOG = false;
        $acl_NOT = false;
        $acl_CSR = false;
        $acl_SPR = false;
        $acl_ISI = false;
        $acl_CUST_DECISION = false;
        $acl_CLOSE = false;
        // Allow features following user profile
        // Superuser and Group CSM
        if ($user->isInGroup(['superuser', 'role_CSD'])) {
            $acl_LOG = true;
            $acl_NOT = true;
            $acl_CSR = true;
            $acl_SPR = true;
            $acl_ISI = true;
            $acl_CUST_DECISION = true;
            $acl_CLOSE = true;
        } // Regional MGMT and Regional Admin
        elseif (
            $user->isInGroupLevel('role_EVP', $ssoErp)
            || $user->isInGroupLevel('role_CSM', $ssoErp)
            || $user->isInGroupLevel('role_CSA', $ssoErp)
        ) {
            $acl_ISI = $user->isInGroupLevel('role_CSM', $ssoErp);
            $acl_LOG = true;
            $acl_NOT = true;
            $acl_CSR = true;
            $acl_SPR = true;
            $acl_CUST_DECISION = true;
            $acl_CLOSE = true;
        } // Service Tech
        elseif ($user->isInGroupLevel('gg_SERVICE', $ssoErp)) {
            $acl_LOG = true;
            $acl_CSR = true;
            $acl_SPR = true;
        } // PSM & SPR
        elseif ($user->isInGroupLevel('gg_PARTS', $ssoErp) || $user->isInGroupLevel('role_PSM', $sb->getFactoryERP()) || $user->isInGroupLevel('role_PSE', $sb->getFactoryERP()) || $user->isInGroupLevel('role_PSA', $sb->getFactoryERP())) {
            $acl_LOG = true;
            $acl_SPR = true;
        }

        // LOGIC -------------------------------->
        if (!empty($m[4]) && !$sb->isAnyImplementationStatus() && !($m[4] === 'CSR' && 'CLOSED' ===$sb->getStatus())) {
            $DEFAULT_ERROR[] = 'ERROR: Status must be (PARTIAL_)IMPLEMENTATION to use this feature';
            break;
        }

        switch ($m[4]) {
            case 'CLOSE':
                // ACL check
                if (!$acl_CLOSE) {
                    $DEFAULT_ERROR[] = 'ERROR: You do not have permissions';
                    break;
                }
                // Check params
                if (empty($_REQUEST['lid']) || !is_numeric($_REQUEST['lid'])) {
                    $DEFAULT_ERROR[] = 'ERROR: Can not close, line parameters empty or invalid';
                    break;
                }
                // Get line
                $line = new tldSB_Line(TldDatabase::escape($_REQUEST['lid']));
                $lid = $line->itsID;
                if ($line->getParentID() !== $sb->getID()) {
                    $DEFAULT_ERROR[] = "ERROR: SB Line#$lid not part of SB#{$sb->getID()}";
                    break;
                }

                if (!$sb->isSignedByStatusBySSOID('SSD_DECISION', $line->getSSOID())) {
                    $DEFAULT_ERROR[] = "ERROR: SB#{$sb->getID()} is not fully signed as 'SSD_DECISION' for SSO {$line->getSSOERP()}.";
                    break;
                }
                // Form
                $form = new HTML_QuickForm('frm', 'post');
                $form->addElement('hidden', 'm[0]', 'sb');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('hidden', 'm[2]', 'summary');
                $form->addElement('hidden', 'm[3]', $m[3]);
                $form->addElement('hidden', 'm[4]', $m[4]);
                $form->addElement('hidden', 'id', $id);
                $form->addElement('hidden', 'lid', $lid);
                $form->addElement('header', 'headform', "Please confirm closure of line#$lid for ER SN# " . $line->getERSN());
                $form->addElement('textarea', 'reason', 'Reason', ['wrap' => 'VIRTUAL', 'cols' => '50', 'rows' => '6']);
                $form->addRule('reason', 'Required', 'required');
                $form->addElement('submit', 'btnSubmit', 'Submit');

                if (!$form->validate()) {
                    $DEFAULT_ERROR[] = 'WARNING: This feature should be used with caution. See your Customer Service Manager for more details.';
                    $body = $form->toHTML();
                    break 2;
                }

                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $e = $line->updateStatus($user->getID(), 'CLOSED', 'closed');
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "INTERNAL ERROR: Could not close line. Reason: $e";
                    break;
                }
                $line->addLogEntry($user->getID(), 'Closure reason: ' . $vars['reason']);
                $DEFAULT_ERROR[] = 'SB line closed successfully!';
                break;
            case 'LOG':
                // ACL check
                if (!$acl_LOG) {
                    $DEFAULT_ERROR[] = 'ERROR: You do not have permissions';
                    break;
                }
                if (empty($_POST['log'])) {
                    $DEFAULT_ERROR[] = 'ERROR: Can not add log, no lines selected';
                    break;
                }
                // Form
                $form = new HTML_QuickForm('frm', 'post');
                $form->addElement('hidden', 'm[0]', 'sb');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('hidden', 'm[2]', 'summary');
                $form->addElement('hidden', 'm[3]', $m[3]);
                $form->addElement('hidden', 'm[4]', $m[4]);
                $form->addElement('hidden', 'id', $id);
                $form->addElement('header', 'headform', 'Add Log');
                $i = 0;
                foreach ($_POST['log'] as $lid) {
                    $line = new tldSB_Line(TldDatabase::escape($lid));
                    $form->addElement('header', 'headform', "ER {$line->getERSN()} {$line->itsHeader['user_customer_name']}");
                    $form->addElement('hidden', 'log[]', $lid);
                    $form->addElement('textarea', "data[$lid]", 'Log',
                        ['wrap' => 'VIRTUAL', 'cols' => '50', 'rows' => '6', 'class' => 'textareaLog', 'id' => "textareaLog$lid"]
                    );
                    $form->addRule("data[$lid]", 'Required', 'required');
                    // add special button to copy
                    if ($i === 0) {
                        $js = <<<HTML
<script type="application/javascript">
    document.addEventListener("DOMContentLoaded", function () {
        $('[name="btnReplicate"]').on('click', function (e) {
            e.preventDefault();
            const firstEditor = tinymce.get(tinymce.editors[0].id);
            if (!firstEditor) return;
            const content = firstEditor.getContent();

            tinymce.editors.forEach((editor, idx) => {
                if (idx > 0) {
                    editor.setContent(content);
                }
            });
        });
    });
</script>
HTML;
                        $form->addElement('button', 'btnReplicate', 'Replicate');
                        $form->addElement('reset', 'btnReset', 'Reset');
                        $smarty->assign('html_head', $js);
                    }
                    $i++;
                }
                $form->addElement('submit', 'btnSubmit', 'Submit');

                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break 2;
                }

                $vars = $form->exportValues();
                $isSignedBySSO = null;
                foreach ($vars['data'] as $lid => $log) {
                    $lid = TldDatabase::escape($lid);
                    $log = TldDatabase::escape($log);
                    $line = new tldSB_Line($lid);
                    if ($isSignedBySSO === null) {
                        $isSignedBySSO = $sb->isSignedByStatusBySSOID('SSD_DECISION', $line->getSSOID());
                    }
                    if (!$isSignedBySSO) {
                        $DEFAULT_ERROR[] = "ERROR: SB#{$sb->getID()} is not fully signed as 'SSD_DECISION' for SSO {$line->getSSOERP()}.";
                        break;
                    }
                    $e = $line->addLogEntry($user->getID(), $log, 1);
                    if (is_string($e)) {
                        $DEFAULT_ERROR[] = "INTERNAL ERROR: Can not add log to line#$lid. Reason: $e";
                        continue;
                    }
                }
                $DEFAULT_ERROR[] = 'LOG creation process completed!';
                break;
            case 'NOT_BYPASS':
                // ACL check
                if (!$acl_NOT) {
                    $DEFAULT_ERROR[] = 'ERROR: You do not have permissions';
                    break;
                }
                $lids = $_REQUEST;
                // Check params
                if (empty($lids['bpnot']) || !is_array($lids['bpnot'])) {
                    $DEFAULT_ERROR[] = 'ERROR: Can not bypass notification, line parameters empty or invalid';
                    break;
                }
                foreach ($lids['bpnot'] as $lid) {
                    // Get line
                    $line = new tldSB_Line(TldDatabase::escape($lid));
                    if ((int)$line->getParentID() !== (int)$sb->getID()) {
                        $DEFAULT_ERROR[] = "ERROR: SB Line#$lid not part of SB#{$sb->getID()}";
                        continue;
                    }
                    if (!$sb->isSignedByStatusBySSOID('SSD_DECISION', $line->getSSOID())) {
                        $DEFAULT_ERROR[] = "ERROR: SB#{$sb->getID()} is not fully signed as 'SSD_DECISION' for SSO {$line->getSSOERP()}.";
                        continue;
                    }
                    // change status
                    $e = $line->updateStatus($user->getID(), null, 'not_bypass');
                    if (is_string($e)) {
                        $DEFAULT_ERROR[] = "INTERNAL ERROR: Can not bypass notification to line#$lid. Reason: $e";
                        continue;
                    }
                    // Log it
                    $line->addLogEntry($user->getID(), 'Notification has been bypassed');
                }
                // Confirm
                $DEFAULT_ERROR[] = 'Notification bypassed successfully!';
                break;
            case 'NOT':
                // ACL check
                if (!$acl_NOT) {
                    $DEFAULT_ERROR[] = 'ERROR: You do not have permissions';
                    break;
                }
                // Rule check
                if ($sb->isConfidential()) {
                    $DEFAULT_ERROR[] = 'ERROR: You can not send NOT when SB is Confidential';
                    break;
                }
                if (empty($_POST['not'])) {
                    $DEFAULT_ERROR[] = 'ERROR: Can not send notification, no lines selected';
                    break;
                }

                // Prepare NOT data -------------------------------------->
                $postGroupedByCustomer = [];
                // Group NOT by Customer
                $isSignedBySSO = null;
                $sbLines = tldSB_Line::byConstraints(sprintf('sb_lines.id IN (%s)', implode(',', $_POST['not'])));
                $sbId = (int)$sb->getID();
                foreach ($sbLines as $line) {
                    if ((int)$line['parent_id'] !== $sbId) {
                        $DEFAULT_ERROR[] = "ERROR: SB Line#{$line['id']} not part of SB#$sbId";
                        if (false !== $key = array_search($line['id'], $_POST['not'], true)) {
                            unset($_POST['not'][$key]);
                        }
                        continue;
                    }

                    // Check if NOT allowed
                    if (true !== $createAllowed = (new tldSB_Line($line['id'], false))->isNOTCreationAllowed()) {
                        $DEFAULT_ERROR[] = "ERROR: SB Line#{$line['id']} not allowed for NOT. Reason: $createAllowed";
                        if (false !== $key = array_search($line['id'], $_POST['not'], true)) {
                            unset($_POST['not'][$key]);
                        }
                        continue;
                    }
                    if (null === $isSignedBySSO) {
                        $isSignedBySSO = $sb->isSignedByStatusBySSOID('SSD_DECISION', $line['sso_id']);
                    }
                    if (!$isSignedBySSO) {
                        $DEFAULT_ERROR[] = "ERROR: SB#{$sb->getID()} is not fully signed as 'SSD_DECISION' for SSO {$line['sso_erp']}.";
                        if (false !== $key = array_search($line['id'], $_POST['not'], true)) {
                            unset($_POST['not'][$key]);
                        }
                        continue;
                    }

                    // Group customer
                    $postGroupedByCustomer[$line['buyer_customer_id']][] = $line;
                }

                // CONSTRUCT NOT Form -------------------------------------->

                // Listing
                $tldPeople = tldDirectory::getUserlist('smartyOptions');
                $EMAIL_OPTION_CONTENT_LIST = tldSB_Line::getDefaultNotificationContent();
                // Form
                $form = new HTML_QuickForm('frmNot', 'post', null, null, null, true);
                $form->addElement('hidden', 'm[0]', 'sb');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('hidden', 'm[2]', 'summary');
                $form->addElement('hidden', 'm[3]', $m[3]);
                $form->addElement('hidden', 'm[4]', $m[4]);
                $form->addElement('hidden', 'id', $id);
                $form::registerRule('checkEmail', 'callback', static function ($value) {
                    return empty($value) || filter_var($value, FILTER_VALIDATE_EMAIL);
                });

                $customerFiles = [];
                foreach ($sb->getCustomerFiles() as $file) {
                    $customerFiles[$file['id']] = $file;
                }
                // One NOT foreach customer
                $form->addElement('submit', 'btnSubmit', 'Submit');
                foreach ($postGroupedByCustomer as $customerID => $lines) {
                    $defaultText = '';
                    $partDecisions = array_unique(array_column($lines, 'part_decision'));
                    $serviceDecisions = array_unique(array_column($lines, 'service_decision'));
                    if (1 === \count($partDecisions) && 1 === \count($serviceDecisions)) {
                        $defaultText = $EMAIL_OPTION_CONTENT_LIST[$partDecisions[0]][$serviceDecisions[0]];
                    }
                    $customer = new tldCustomer($customerID);
                    $extraEndUserCustomers = array_diff(array_unique(array_column($lines, 'user_customer_id')), [$customerID]);
                    $extraMaintainerCustomers = array_diff(array_unique(array_column($lines, 'maintainer_customer_id')), array_merge([$customerID], $extraEndUserCustomers));
                    foreach ($extraMaintainerCustomers as $key => $value) {
                        if (null == $value) {
                            unset($extraMaintainerCustomers[$key]);
                        }
                    }
                    // Listing
                    foreach ($lines as $line) {
                        $form->addElement('hidden', 'not[]', $line['id']);
                        $form->addElement('hidden', "data[$customerID][lid][]", $line['id']);
                        // Prepare list of ER for not content
                        $erList[$customerID][$line['er_id']] = $line;
                    }
                    $contactList = ExtranetUser::optionsbyCustomerIDsAsIDFullnameAndEmail([$customerID]);
                    $endUserContactList = ExtranetUser::optionsbyCustomerIDsAsIDFullnameAndEmail($extraEndUserCustomers);
                    $maintainerContactList = ExtranetUser::optionsbyCustomerIDsAsIDFullnameAndEmail($extraMaintainerCustomers);
                    $contactListDefault = array_column(ExtranetUser::bySSOCustomerRole($SSO->getID(), $customerID, 'NOTIFICATION_SB3'), 'fullname', 'id');
                    $crtRawList = tldCRT::byCustomerIDSSOID($customerID, $SSO->getID());
                    $crtList = [];
                    foreach ($crtRawList as $crt) {
                        $crtList[$crt['id']] = "CRT#{$crt['id']} - {$crt['erp_location']} - {$crt['cuno']} - {$crt['sales_rep']}";
                    }
                    if (empty($crtList)) {
                        $DEFAULT_ERROR[] = 'WARNING: No CRT found for customer ' . $customer->getCustomerName();
                    }
                    // Continue FORM
                    $form->addElement('header', 'headform', 'Send NOT to customer -> ' . $customer->getCustomerName());
                    $form->addElement('text', "data[$customerID][subject]", 'Subject', ['style' => 'width:810px;']);
                    // Body
                    // -- message
                    $form->addElement('textarea', "data[$customerID][body][message]", 'Do you want to add a custom message to the notification?', ['wrap' => 'VIRTUAL', 'cols' => '100', 'rows' => '10']);
                    if ($customerFiles) {
                        $form->addElement('static', 'optionsFile', 'Files attachment', 'You can add files from the below list (optional):');
                        foreach ($customerFiles as $file) {
                            $form->addElement('checkbox', "data[$customerID][body][file][".$file['id']."]", null, sprintf('%s (%s by %s) - %.2f MB', $file['description'], $file['date'], $file['poster'], round($file['size'] / (1024 ** 2), 2)));
                        }
                    }

                    // Extranet
                    $exu =& $form->addElement('advmultiselect', "data[$customerID][to_exu]", null, $contactList, ['size' => 5, 'class' => 'pool', 'style' => 'width:500px;']);
                    $exu->setLabel(['Customer contacts', 'Addressbook', 'Recipients (max 10)']);
                    $exu->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
                    $exu->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
                    if ([] !== $endUserContactList) {
                        $exu =& $form->addElement('advmultiselect', "data[$customerID][to_exu_end_user]", null, $endUserContactList, ['size' => 5, 'class' => 'pool', 'style' => 'width:500px;']);
                        $exu->setLabel(['End user Customer contacts', 'Addressbook', 'Recipients (max 10)']);
                        $exu->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
                        $exu->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
                    }
                    if ([] !== $maintainerContactList) {
                        $exu =& $form->addElement('advmultiselect', "data[$customerID][to_exu_maintainer]", null, $maintainerContactList, ['size' => 5, 'class' => 'pool', 'style' => 'width:500px;']);
                        $exu->setLabel(['Maintainer Customer contacts', 'Addressbook', 'Recipients (max 10)']);
                        $exu->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
                        $exu->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
                    }

                    // Copy me option to CC current user
                    $form->addElement('checkbox', "data[$customerID][copyme]", 'Copy me', "You will be in CC of this customer's notification");
                    // If no extranet user
                    if (!empty($crtList)) {
                        for ($i = 1; $i < 5; $i++) {
                            $form->addElement('static', "data[$customerID][new_exu][$i][title]", "Create contact $i:");
                            $form->addElement('select', "data[$customerID][new_exu][$i][crt_id]", 'CRT', $crtList);
                            $form->addElement('text', "data[$customerID][new_exu][$i][firstname]", 'Firstname');
                            $form->addElement('text', "data[$customerID][new_exu][$i][lastname]", 'Lastname');
                            $form->addElement('text', "data[$customerID][new_exu][$i][division]", 'Division');
                            $form->addElement('text', "data[$customerID][new_exu][$i][department]", 'Department');
                            $form->addElement('text', "data[$customerID][new_exu][$i][jobTitle]", 'Job Title');
                            $form->addElement('text', "data[$customerID][new_exu][$i][email]", 'Email');
                            $form->addRule("data[$customerID][new_exu][$i][email]", 'Not a valid email', 'checkEmail');
                        }
                    }
                    // User tld
                    $tld =& $form->addElement('advmultiselect', "data[$customerID][to_tld]", null, $tldPeople, ['size' => 5, 'class' => 'pool', 'style' => 'width:200px;']);
                    $tld->setLabel(['CC TLD people', 'Contacts', 'CC (max 10)']);
                    $tld->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
                    $tld->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
                    // External contacts
                    for ($i = 1; $i < 5; $i++) {
                        $form->addElement('text', "data[$customerID][to_ext][$i]", "External email $i");
                        $form->addRule("data[$customerID][to_ext][$i]", 'Not a valid email', 'checkEmail');
                    }
                    // Default value
                    $form->setDefaults([
                        "data[$customerID][subject]" => "TLD {$sb->getCategory()} Service Bulletin #$id",
                        "data[$customerID][to_exu]" => array_keys($contactListDefault),
                        "data[$customerID][body][file]" => 'none',
                        "data[$customerID][body][message]" => $defaultText,
                    ]);
                    // Rules
                    $form->addRule("data[$customerID][subject]", 'Required', 'required');
                }
                $form->addElement('submit', 'btnSubmit', 'Submit');

                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break 2;
                }

                // PROCESS NOT VALIDATION -------------------------------------->
                global $kernel;
                try {
                    $client = $kernel->getContainer()->get(Client::class);
                } catch (ServiceNotFoundException $e) {
                    $DEFAULT_ERROR[] = sprintf('ERROR: Could not get Client. Reason: %s', $e->getMessage());
                    break;
                }
                // For each customer
                foreach ($_POST['data'] as $cid => $rawVars) {
                    $customer = new tldCustomer($cid);
                    $line = new tldSB_Line($rawVars['lid'][0]);

                    // 1. Send email
                    $vars = tldUtils::cleanupFormInput($rawVars);
                    // Prepare email
                    $from = $user->getEmail();
                    $subject = $rawVars['subject'];
                    $email_body = tldSB3::constructEmailHeader();
                    $email_file = [];
                    if (!empty($rawVars['body']['file']) && is_array($rawVars['body']['file'])) {
                        foreach (array_keys($rawVars['body']['file']) as $fileId) {
                            $selectedFile = new tldModFile($fileId);
                            $filename = sys_get_temp_dir() . '/' .$selectedFile->itsHeader['filename'];
                            if (!file_exists($filename)) {
                                file_put_contents($filename, file_get_contents($selectedFile->itsHeader['filepath']));
                            }
                            $email_file[] = $filename;
                        }
                    }

                    $body_log = tldSB_Line::getDefaultNotificationHeaderContent($sb->itsHeader);
                    $body_log .= tldSB_Line::getDefaultNotificationERContent($erList[$cid]);
                    $body_log .= '<p>' . stripslashes(mb_convert_encoding($rawVars['body']['message'], 'UTF-8', mb_list_encodings())) . '</p>';
                    $body_log .= $line->getDefaultNotificationFooterContent($user);
                    $email_body .= $body_log;
                    $email_body .= tldSB3::constructEmailFooter();
                    // -- customer contacts
                    $to = $vars['to_exu'] ? array_column(extranetUser::byConstraints(sprintf('user.id IN (%s)', implode(',', array_unique(array_merge($vars['to_exu'], $vars['to_exu_end_user'] ?? [], $vars['to_exu_maintainer'] ?? []))))), 'email') : [];
                    // -- customer contacts added manually
                    if (!empty($vars['new_exu'])) {
                        try {
                            $apiCustomer = $client->findOneBy('sales/customers', ['legacyId' => $cid]);
                            $apiCustomer = $apiCustomer['@id'];
                        } catch (\RangeException $e) {
                            $apiCustomer = null;
                        }

                        try {
                            $xuRole = $client->findOneBy('sales/extranet_user_groups', ['name' => 'role_ST']);
                        } catch (\RangeException $e) {
                            $xuRole = null;
                        }
                    }

                    foreach ($vars['new_exu'] as $k => $var) {
                        if (empty($var['firstname']) || empty($var['lastname']) || empty($var['email'])) {
                            continue;
                        }
                        $cacheHeaders = [
                            'CSA-Disable-Cache' => $cid . $line->getID() . $var['email'],
                        ];
                        $var = tldUtils::cleanupFormInput($var);
                        $var['userid'] = $var['email'];

                        // Add user

                        $extranetUsers = $client->search('sales/extranet_users', ['headers' => $cacheHeaders, 'query' => ['email' => $var['userid']]]);

                        if ($extranetUsers->count()) {
                            $extranetUser = null;
                            // Try to find contact with extranet access
                            foreach ($extranetUsers->all() as $user) {
                                if ($user['disabled'] === false) {
                                    $extranetUser = $user;
                                    break;
                                }
                            }

                            if (!$extranetUser) {
                                // Default to the first one
                                $extranetUser = $extranetUsers->first();
                            }

                            $DEFAULT_ERROR[] = 'ERROR: Email already used for Extranet User #' . $extranetUser['id'];
                            $editUrl = $kernel->getContainer()->get('router')->generate('sales_contact_show', [
                                'id' => $extranetUser['id'],
                            ], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_PATH);

                            $body .= <<<EOF
<p><strong style="color: red">If you need to update the Extranet User, please use the new form</strong> (<a href="$editUrl">click here</a>).</p>
EOF;
                            continue;
                        }

                        $lastname = mb_convert_encoding($var['lastname'], 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8');
                        $firstname = mb_convert_encoding($var['firstname'], 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8');
                        $username = mb_convert_encoding($var['email'], 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8');
                        $division = mb_convert_encoding($var['division'], 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8');
                        $department = mb_convert_encoding($var['department'], 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8');
                        $jobTitle = mb_convert_encoding($var['jobTitle'], 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8');

                        try {
                            $extranetUser = $client->save('sales/extranet_users',
                                [
                                    'email' => $username,
                                    'username' => $username,
                                    'lastname' => $lastname,
                                    'firstname' => $firstname,
                                    'extranetUserProfile' => [
                                        'customer' => $apiCustomer,
                                        'division' => $division,
                                        'department' => $department,
                                        'jobTitle' => $jobTitle,
                                    ],
                                ],
                                ['headers' => $cacheHeaders]
                            );
                        } catch (ClientException $e) {
                            $error = json_decode($e->getResponse()->getContent(), true);
                            $DEFAULT_ERROR[] = 'ERROR: Could not add Extranet User. Reason: ' . $error['hydra:description'];
                            continue;
                        }

                        $ext_user = new extranetUser($extranetUser['legacyId']);
                        $ext_user->addLogEntry('Extranet user created from SB3 system');

                        // Add CRT
                        try {
                            $xuCRT = $client->findOneBy('sales/customer_relationship_teams', ['legacyId' => $var['crt_id']]);
                            $extranetUserAcl = $client->save('sales/extranet_user_acls',
                                [
                                    'extranetUser' => $extranetUser['@id'],
                                    'crt' => $xuCRT['@id'],
                                    'extranetUserGroup' => $xuRole['@id'],
                                ],
                                ['headers' => ['CSA-Disable-Cache' => $extranetUser['@id'] . $var['crt_id'] . $line->getID()]]
                            );
                        } catch (ClientException $e) {
                            $errors = json_decode($e->getResponse()->getContent(), true);
                            $DEFAULT_ERROR[] = 'ERROR: Could not add Extranet User ACL for CRT #' . $xuCRT['id'] . ' Reason : ' . $errors['hydra:description'];
                            // Not a blocking issue
                        }
                        // Add to recipients
                        $to[] = $extranetUser['email'];
                    }
                    if (!count($to)) {
                        $DEFAULT_ERROR[] = "WARNING: Notification not sent to customer {$customer->getCustomerName()}. Reason: No customer contacts selected or added.";
                        continue;
                    }
                    // CC Recipients -- TLD people
                    $cc = [];
                    if (isset($vars['to_tld'])) {
                        $cc = array_column(tldUser::byConstraints(sprintf('people.id IN (%s)', implode(',', $vars['to_tld']))), 'email');
                    }

                    if ($vars['copyme']) {
                        $cc[] = $from;
                    }
                    // -- External People
                    foreach ($vars['to_ext'] as $email) {
                        if (empty($email)) {
                            continue;
                        }
                        $cc[] = $email;
                    }
                    // Clean email data
                    $to = implode(',', array_unique($to));
                    $cc = implode(',', array_unique($cc));

                    // Get email PDF version
                    // --- prepare html
                    $dt = date('Y-m-d H:i:s');
                    $email_info = <<<EOF
<p>Date: $dt<br>From: $from<br>To: $to</p>
<p>Subject: $subject</p>
EOF;
                    $html = str_replace(
                        '<!--#NOT_INFO#-->',
                        $email_info,
                        $email_body
                    );
                    // --- generate from html2pdf lib
                    $html2pdf = new tldHTML2PDF($html, ['encoding' => 'utf-8']);
                    // --- send to tldFile
                    $fid = tldFile::upload(
                        $html2pdf->itsConvertedPdfFile->getFilePath(),
                        'sb_not',
                        $html2pdf->itsConvertedPdfFile->getBasename()
                    );

                    if (is_string($fid)) {
                        return $fid;
                    }
                    // Send email
                    $e = tldUtils::emailAttachment(
                        $to,
                        $from,
                        $subject,
                        $email_body,
                        $email_file,
                        $cc,
                        null,
                        ['charset' => 'utf-8']
                    );

                    // 2. Record NOT

                    // Prepare info
                    $notData = [
                        'uid' => $user->getID(),
                        'recipients' => $to,
                        'subject' => $subject,
                        'email' => TldDatabase::escape(mb_convert_encoding($body_log, 'ISO-8859-1', 'UTF-8')),
                        'fid' => $fid,
                        'cc' => $cc,
                        'bcc' => '',
                    ];
                    // Create NOT for all lines
                    foreach ($postGroupedByCustomer[$cid] as $line) {
                        if (!in_array($line['id'], $vars['lid'], false)) {
                            continue;
                        }
                        // Save DB calls as we already have the data and can populate the objects
                        $sbLine = new tldSB_Line($line['id'], true);
                        $sbLine->itsHeader = $line;
                        $e = $sbLine->createNOT($user->getID(), $notData, true);
                        if (is_string($e)) {
                            $DEFAULT_ERROR[] = "INTERNAL ERROR: Can not add NOT to line#{$line['id']}. Reason: $e";
                            continue;
                        }
                    }
                }
                $DEFAULT_ERROR[] = 'Notification process completed!';
                break;
            case 'SPR':
                // Check if parts selected
                if ([] === ($sbLineIds = $_POST['spr'] ?? [])) {
                    $DEFAULT_ERROR[] = 'ERROR: Can not create SPR, no line selected';
                    break;
                }

                $lines = $sb->getLinesByConstraints(sprintf('sb_lines.id IN (%s)', implode(', ', $sbLineIds)));
                $lines = array_column($lines, null, 'er_id');

                global $kernel;
                $container = $kernel->getContainer();

                $router = $container->get('router');
                $client = $container->get(Client::class);

                try {
                    $equipmentRecords = $client->findBy('equipment_records', ['legacyId' => array_column($lines, 'er_id')]);
                    $factory = $client->findOneBy('locations', ['legacyId' => $sb->getFactoryID()]);
                } catch (\Exception $e) {
                    $DEFAULT_ERROR[] = "ERROR: something went wrong while preparing the SPR creation, please retry or open a ticket";
                    break;
                }

                $equipmentRecordIris = [];
                foreach ($equipmentRecords->getSimpleArrayCopy() as $equipmentRecord) {
                    $equipmentRecordIris[$equipmentRecord['@id']] = $lines[$equipmentRecord['legacyId']]['part_decision'];
                }

                $parts = [];
                foreach ($sb->getParts() as $part) {
                    $parts[$part['pn']] = $part['qty'];
                }

                $url = $router->generate('spare_parts_request_sb_create', [
                    'equipmentRecords' => $equipmentRecordIris,
                    'factory' => $factory['@id'],
                    'parts' => $parts,
                    'sbId' => $sb->getID(),
                    'decision' => $sb->getDefaultPartDecision(),
                    'category' => $sb->getCategory(),
                ]);
                header("Location: $url");
                exit;
            case 'CSR':
                // ACL check
                if (!$acl_CSR) {
                    $DEFAULT_ERROR[] = 'You do not have permissions';
                    break;
                }
                // Check if parts selected
                if (!array_key_exists('csr', $_POST) || null === $_POST['csr'] || count($_POST['csr']) === 0) {
                    $DEFAULT_ERROR[] = 'ERROR: Can not create CSR, no line selected';
                    break;
                }

                // Create CSR ----------------------->
                $isSignedBySSO = null;
                foreach ($_POST['csr'] as $lid) {
                    $line = new tldSB_Line(TldDatabase::escape($lid));
                    if ($line->getParentID() !== $sb->getID()) {
                        $DEFAULT_ERROR[] = "ERROR: SB Line#$lid not part of SB#{$sb->getID()}";
                        continue;
                    }
                    if (null === $isSignedBySSO) {
                        $isSignedBySSO = $sb->isSignedByStatusBySSOID('SSD_DECISION', $line->getSSOID());
                    }
                    if (!$isSignedBySSO) {
                        $DEFAULT_ERROR[] = "ERROR: SB#{$sb->getID()} is not fully signed as 'SSD_DECISION' for SSO {$line->getSSOERP()}.";
                        break;
                    }

                    try {
                        // Create CSR
                        $line->createCSR($user->getID());
                        $DEFAULT_ERROR[] = 'CSR creation process completed!';
                    } catch (\Exception $exception) {
                        $DEFAULT_ERROR[] = sprintf('INTERNAL ERROR: Can not create CSR for ER#%s. Reason: %s', $line->getERSN(), $exception->getMessage());
                        break;
                    }

                }
                break;
            case 'CUST_DECISION':
                // ACL check
                if (!$acl_CUST_DECISION) {
                    $DEFAULT_ERROR[] = 'ERROR: You do not have permissions';
                    break;
                }
                // Check data
                $data = [];
                foreach ($_POST['cust_decision'] as $lid => $vals) {
                    if (empty($vals['part']) && empty($vals['service'])) {
                        continue;
                    }
                    $data[$lid] = $vals;
                }
                if (empty($data)) {
                    $DEFAULT_ERROR[] = 'ERROR: No lines selected to set customer decision';
                    break;
                }
                // Form
                $form = new HTML_QuickForm('frm', 'post');
                $form->addElement('hidden', 'm[0]', 'sb');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('hidden', 'm[2]', 'summary');
                $form->addElement('hidden', 'm[3]', $m[3]);
                $form->addElement('hidden', 'm[4]', $m[4]);
                $form->addElement('hidden', 'id', $id);
                $form->addElement('header', 'headform', 'Set customer decision');
                $i = 0;
                foreach ($_POST['cust_decision'] as $lid => $vals) {
                    $line = new tldSB_Line(TldDatabase::escape($lid));
                    // Check if decision changed
                    if (
                    (string)$line->getCustomerPartDecision() === (string)$vals['part']
                        &&  (string)$line->getCustomerServiceDecision() === (string)$vals['service']
                    ) {
                        continue;
                    }
                    $form->addElement('header', 'headform', "ER {$line->getERSN()} {$line->itsHeader['user_customer_name']}");
                    // Part decision
                    if ($line->isCustomerPartDecisionNeeded()) {
                        $form->addElement('static', "$lid\_partdecisionInfo", 'Customer decision for Part:', $vals['part']);
                        $form->addElement('hidden', "cust_decision[$lid][part]", $vals['part']);
                    }
                    // Service decision
                    if ($line->isCustomerServiceDecisionNeeded()) {
                        $form->addElement('static', "$lid\_servicedecisionInfo", 'Customer decision for Service:', $vals['service']);
                        $form->addElement('hidden', "cust_decision[$lid][service]", $vals['service']);
                    }
                    $form->addElement('textarea', "cust_decision[$lid][reason]", 'Reason',
                        ['wrap' => 'VIRTUAL', 'cols' => '50', 'rows' => '6', 'class' => 'textareaLog', 'id' => "textareaLog$lid"]
                    );
                    $form->addRule("cust_decision[$lid][reason]", 'Required', 'required');
                    // add special button to copy
                    if ($i === 0) {
                        $js = <<<HTML
<script type="application/javascript">
    document.addEventListener("DOMContentLoaded", function () {
        $('[name="btnReplicate"]').on('click', function (e) {
            e.preventDefault();
            const firstEditor = tinymce.get(tinymce.editors[0].id);
            if (!firstEditor) return;
    
            const content = firstEditor.getContent();
    
            tinymce.editors.forEach((editor, idx) => {
                if (idx > 0) {
                    editor.setContent(content);
                }
            });
        });
    });
</script>
HTML;
                        $form->addElement('button', 'btnReplicate', 'Replicate');
                        $form->addElement('reset', 'btnReset', 'Reset');
                        $smarty->assign('html_head', $js);
                    }
                    $i++;
                }
                $form->addElement('submit', 'btnSubmit', 'Submit');

                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break 2;
                }

                $vars = $form->exportValues();
                // Process all lines
                foreach ($vars['cust_decision'] as $lid => $rawVals) {
                    $line = new tldSB_Line(TldDatabase::escape($lid));
                    if ($line->getParentID() !== $sb->getID()) {
                        $DEFAULT_ERROR[] = "ERROR: SB Line#$lid not part of SB#{$sb->getID()}";
                        continue;
                    }
                    // Clean up data post
                    $vals = tldUtils::cleanupFormInput($rawVals);
                    // Set customer decision
                    $e = $line->setCustomerDecision(
                        $user->getID(),
                        [
                            'cust_part_decision' => $vals['part'],
                            'cust_service_decision' => $vals['service'],
                            'reason' => $rawVals['reason'],
                        ]
                    );
                    if (is_string($e)) {
                        $DEFAULT_ERROR[] = "INTERNAL ERROR: Can not set customer decision for ER#{$line->getERSN()}. Reason: $e";
                        continue;
                    }
                }
                $DEFAULT_ERROR[] = 'Customer decision update process completed';
                break;
            case 'ISI':
                // ACL check
                if (!$acl_ISI) {
                    $DEFAULT_ERROR[] = 'ERROR: You do not have permissions';
                    break;
                }

                if (empty($_POST['isi'])) {
                    $DEFAULT_ERROR[] = 'ERROR: Can not use batch update, no lines selected';
                    break;
                }

                $sbLines = tldSB_Line::byConstraints(sprintf('sb_lines.id IN (%s)', implode(',', $_POST['isi'])));
                $sbId = (int)$sb->getID();
                foreach ($sbLines as $line) {
                    if ((int)$line['parent_id'] !== $sbId) {
                        $DEFAULT_ERROR[] = "ERROR: SB Line#{$line['id']} not part of SB#$sbId";
                        if (false !== $key = array_search($line['id'], $_POST['isi'], true)) {
                            unset($_POST['isi'][$key]);
                        }
                        continue;
                    }
                }
                $customerDecisionList = tldSB_Line::getCustomerDecisionList();
                $statusList = tldSB_Line::getStatusList();
                $closureTypeList = tldSB_Line::getClosureTypeList();
                // Form
                $form = new HTML_QuickForm('frmIsi', 'post');
                $form->addElement('hidden', 'm[0]', 'sb');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('hidden', 'm[2]', 'summary');
                $form->addElement('hidden', 'm[3]', $m[3]);
                $form->addElement('hidden', 'm[4]', $m[4]);
                $form->addElement('hidden', 'id', $id);
                $form->addElement('header', 'headform', 'Batch line update');
                if ($user->isInGroupLevel("role_CSM", 900)) {
                    $partDecisionList = array_keys(tldSB_Line::getPartDecisionList());
                    $serviceDecisionList = array_keys(tldSB_Line::getServiceDecisionList());
                    $form->addElement('select', 'part_decision', 'Part Decision', ['' => '', 'Keep unchanged' => 'Keep unchanged'] + array_combine($partDecisionList, $partDecisionList));
                    $form->addElement('select', 'service_decision', 'Service Decision', ['' => '', 'Keep unchanged' => 'Keep unchanged'] + array_combine($serviceDecisionList, $serviceDecisionList));
                    $form->addRule('part_decision', 'Required', 'required');
                    $form->addRule('service_decision', 'Required', 'required');
                }
                $form->addElement('select', 'cust_part_decision', 'Customer Part Decision', ['' => '', 'Keep unchanged' => 'Keep unchanged'] + $customerDecisionList);
                $form->addElement('select', 'cust_service_decision', 'Customer Service Decision', ['' => '', 'Keep unchanged' => 'Keep unchanged'] + $customerDecisionList);
                foreach ($_POST['isi'] as $key => $isi) {
                    $form->addElement('hidden', "isi[$key]", $isi);
                }
                $form->addElement('select', 'status', 'Status', ['' => '', 'Keep unchanged' => 'Keep unchanged'] + $statusList);
                $form->addElement('select', 'closure_type', 'Closure Type', ['' => '', 'Keep unchanged' => 'Keep unchanged'] + $closureTypeList);
                $form->addElement('text', 'dt_cust_to_decide', 'Date Customer to Decide');
                $form->addElement('checkbox', 'change_date', 'I want to update all closure date');
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->addRule('status', 'Required', 'required');
                $form->addRule('cust_part_decision', 'Required', 'required');
                $form->addRule('cust_service_decision', 'Required', 'required');
                $form->addRule('closure_type', 'Required', 'required');

                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break 2;
                }
                // Cleanup form values
                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $fields = array_filter(['cust_part_decision', 'cust_service_decision', 'status', 'closure_type'], static function (string $value) use ($vars) {
                    return $vars[$value] !== 'Keep unchanged';
                });

                if ($vars['change_date'] === "1") {
                    $fields[] .= 'dt_cust_to_decide';
                }

                if ($vars['part_decision'] !== null && $vars['part_decision'] !== 'Keep unchanged') {
                    $fields[] .= 'part_decision';
                }
                if ($vars['service_decision'] !== null && $vars['service_decision'] !== 'Keep unchanged') {
                    $fields[] .= 'service_decision';
                }

                $log = 'SB updated';
                foreach ($fields as $field) {
                    $log .= "<br>{$field}: {$vars[$field]}";
                }
                foreach ($vars['isi'] as $isi) {
                    $line = new tldSB_Line($isi, true);
                    // Update SB Line
                    $e = $line->update($vars, $fields);
                    if (is_string($e)) {
                        $DEFAULT_ERROR[] = "ERROR: Problem updating...<br/>Reason: $e";
                    }
                    $line->addLogEntry($user->getID(), $log);
                }
                break;
        }

        $SB_LINES = _getLinesByConstraints($SB_LINES_CONSTRAINTS);
        $customerServiceRecordsLegacyIds = array_column($SB_LINES, 'csr_id');
        $container = $kernel->getContainer();
        $client = $container->get(Client::class);

        $customerServiceRecordsFromApi = [];
        try {
            $customerServiceRecordsFromApi = $client->findBy(
                '/service/service_bulletin_customer_service_records',
                [
                    'serviceBulletinLegacyId' => $id,
                    'pagination' => 'false',
                ]
            );
        } catch (\Exception $e) {
            $DEFAULT_ERROR[] = "ERROR: something went wrong while retrieve CSR , please retry or open a ticket with the SB #ID and the SSO ";
            break;
        }

        $customerServiceRecordApiByLegacyId = [];

        foreach ($customerServiceRecordsFromApi->all() as $csrApi) {
            $customerServiceRecordApiByLegacyId[(int) $csrApi->legacyId] = [
                'id' => $csrApi->id,
            ];
        }

        foreach ($SB_LINES as $index => $sbLine) {
            $legacyCsrId = (int) $sbLine['csr_id'];

            $csrApi = $customerServiceRecordApiByLegacyId[$legacyCsrId] ?? null;

            $SB_LINES[$index]['api_csr_id'] = $csrApi['id'] ?? null;
        }

        try {
            $sparePartsRequestsFromApi = $client->findBy(
                '/parts/sb_spare_parts_requests',
                [
                    'sbId' => $id,
                    'pagination' => 'false',
                ]
            );
        } catch (\Exception $e) {
            $DEFAULT_ERROR[] = "ERROR: something went wrong while retrieve SPR , please retry or open a ticket with the SB #ID and the SSO.";
            break;
        }

        $sparePartsRequestsApiByLegacyId = [];

        foreach ($sparePartsRequestsFromApi->all() as $sprApi) {
            $sparePartsRequestsApiByLegacyId[(int) $sprApi->legacyId] = [
                'id' => $sprApi->id,
            ];
        }

        foreach ($SB_LINES as $index => $sbLine) {
            $legacySprId = (int) $sbLine['spr_id'];

            $sprApi = $sparePartsRequestsApiByLegacyId[$legacySprId] ?? null;

            $SB_LINES[$index]['api_spr_id'] = $sprApi['id'] ?? null;
        }

        $lineIds = implode(',', array_column($SB_LINES, 'id'));
        if (!empty($lineIds)) {
            $SBLinesNotifications = tldSBNOT::byConstraints("sb_not.parent_id IN ($lineIds)", ['orderBy' => 'sb_not.parent_id, sb_not.id']);
            $SBLinesLogs = tldModLog::byConstraints("parent_id IN ($lineIds) AND module LIKE 'SBL'", ['orderBy' => 'parent_id, id DESC']);

            foreach ($SB_LINES as &$line) {
                $line['notifications'] = [];
                $line['logs'] = [];
                foreach ($SBLinesNotifications as $SBLinesNotification) {
                    if ($line['id'] === $SBLinesNotification['parent_id']) {
                        $line['notifications'][] = $SBLinesNotification;
                    }
                }
                foreach ($SBLinesLogs as $SBLinesLog) {
                    if ($line['id'] === $SBLinesLog['parent_id']) {
                        $line['logs'][] = $SBLinesLog;
                    }
                }
            }
            unset($line);
        }

        if (isset($out) && $out === 'xls') {
            $report = new tldXLS(
                $SB_LINES,
                [
                    'xItems' => [
                        'id' => 'Line#',
                        'sn' => 'SN#',
                        'cust_asset_num' => 'Cust Asset #',
                        'model' => 'Model',
                        'apc_code' => 'APC',
                        'apc_country_name' => 'APC Country',
                        'buyer_customer_name' => 'BUYER customer',
                        'user_customer_name' => 'USER customer',
                        'man_location' => 'Factory',
                        'sso_name' => 'SSO',
                        'date_shipped' => 'Ship date',
                        'part_decision' => 'Part decision',
                        'cust_part_decision' => 'Customer Part decision',
                        'service_decision' => 'Service decision',
                        'cust_service_decision' => 'Customer Service decision',
                        'api_spr_id' => 'SPR#',
                        'spr_status' => 'SPR status',
                        'api_csr_id' => 'CSR#',
                        'csr_status' => 'CSR status',
                        'csr_completion_date' => 'CSR Completion Date',
                        'csr_hourmeter' => 'CSR Hourmeter',
                        'status' => 'Implementation Status',
                        'remediation' => 'Remediation',
                        'closure_type' => 'Closure type',
                    ],
                    'showTitles' => true,
                ]
            );
            $report->out("SB$id\_implementation_" . date('Ymd') . '.xls');
            exit;
        }

        // Limit the number of ER displayed on this page: rendering thousands of rows (with a checkbox/popup/form
        // per line) hangs the browser. Validated by Mathieu SAVARY (MOO) on TTS#54926.
        $SB_LINES_PER_PAGE_OPTIONS = [50, 100, 200];
        if (isset($_REQUEST['items_per_page']) && is_numeric($_REQUEST['items_per_page']) && in_array((int) $_REQUEST['items_per_page'], $SB_LINES_PER_PAGE_OPTIONS, true)) {
            $sess['sb'][$sb->itsID]['items_per_page'] = (int) $_REQUEST['items_per_page'];
        }
        $itemsPerPage = $sess['sb'][$sb->itsID]['items_per_page'] ?? 50;
        $pagination = new tldPagination("sb{$id}_implementation", $SB_LINES, $itemsPerPage);
        $body .= '<div style="margin:10px 0;">'
            . _getItemsPerPageSelector($itemsPerPage, $SB_LINES_PER_PAGE_OPTIONS)
            . '&nbsp;&nbsp;'
            . $pagination->getNavLinks("$php_self?m[0]=sb&m[1]=view&m[2]=summary&m[3]=implementation&id=$id", true, false)
            . '</div>';
        $SB_LINES = $pagination->getData();

        $body .= include 'sb.form.implementation.tpl.php';
        break;
    case 'selection':
        $DEFAULT_TITLE .= "\Decision dashboard";
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sb&m[1]=view&m[2]=summary&m[3]=selection&out=xls&id=$id">XLS version</a>
EOF;
        // Check status
        if (!$sb->isSSDDecisionStatus() && !$sb->isPartialImplementationStatus()) {
            $DEFAULT_ERROR[] = 'ERROR: Status must be SSD_DECISION or PARTIAL_IMPLEMENTATION to use this feature';
            break;
        }
        // Check if signed
        if ($sb->isSignedByStatusBySSOID('SSD_DECISION', $SSO->getID())) {
            $DEFAULT_ERROR[] = 'ERROR: Implementation decision not possible anymore';
            $DEFAULT_ERROR[] = 'SB already signed for SSO ' . $SSO->getShortName();
            break;
        }
        // Check permissions
        if (!$user->isInGroupLevel('role_EVP', $SSO->getERP()) && !$user->isInGroup(['role_CSD'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permissions for SSO ' . $SSO->getShortName();
            break;
        }

        switch ($m[4]) {
            case 'update':
                $previousSbLines = _getLinesByConstraints($SB_LINES_CONSTRAINTS);
                foreach ($previousSbLines as $previousLine){
                    if ($previousLine['part_decision'] === $_POST['er'][(int) $previousLine['id']]['part_decision'] && $previousLine['service_decision'] === $_POST['er'][(int) $previousLine['id']]['service_decision']){
                        unset($_POST['er'][(int) $previousLine['id']]);
                    }
                }
                foreach ($_POST['er'] as $lineID => $postLine) {
                    // Clean data
                    $postLine = tldUtils::cleanupFormInput($postLine);
                    $lineID = TldDatabase::escape($lineID);
                    // Get the line
                    $line = new tldSB_Line($lineID);
                    // Check the SB line
                    if ($line->isEmpty() || $line->getParentID() !== $sb->getID()) {
                        $DEFAULT_ERROR[] = "SB line not found for #$lineID";
                        continue;
                    }
                    // Update decisions
                    $e = $line->update([
                        'part_decision' => $postLine['part_decision'],
                        'service_decision' => $postLine['service_decision'],
                    ]);
                    if (is_string($e)) {
                        $DEFAULT_ERROR[] = "ERROR: ER#{$line->getERSN()} decision not updated. Reason: $e";
                        continue;
                    }
                    // Log decision
                    $log = 'SSD decision updated';
                    if ($postLine['part_decision']) {
                        $log .= "<br>Parts: {$postLine['part_decision']}";
                    }
                    if ($postLine['service_decision']) {
                        $log .= "<br>Service: {$postLine['service_decision']}";
                    }
                    $log = TldDatabase::escape($log);
                    $line->addLogEntry($user->getID(), $log);
                }
                $DEFAULT_ERROR[] = 'SSD decision process done!';
                break;
        }
        // Refresh and display the form
        $SB_LINES = _getLinesByConstraints($SB_LINES_CONSTRAINTS);

        if (isset($out) && $out === 'xls') {
            $report = new tldXLS(
                $SB_LINES,
                [
                    'xItems' => [
                        'id' => 'Line#',
                        'sn' => 'SN#',
                        'model' => 'Model',
                        'apc_code' => 'APC',
                        'apc_country_name' => 'APC Country',
                        'buyer_customer_name' => 'BUYER customer',
                        'user_customer_name' => 'USER customer',
                        'man_location' => 'Factory',
                        'sso_name' => 'SSO',
                        'date_shipped' => 'Ship date',
                        'part_decision' => 'Part decision',
                        'service_decision' => 'Service decision',
                    ],
                    'showTitles' => true,
                ]
            );
            $report->out("SB$id\_decision_" . date('Ymd') . '.xls');
            exit;
        }

        $body .= include 'sb.form_layout.ssd_decision.tpl.php';

        break;
    default:
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sb&m[1]=view&m[2]=summary&out=xls&id=$id">XLS version</a>
EOF;
        if (isset($m[4]) && 'bySSObyStatus' === $m[4]) {
            $x = TldDatabase::escape($x);
            $y = TldDatabase::escape($y);
            $SB_LINES = tldSB_line::bySSOByStatusByParent($y, $x, $sb->itsID);
        } else {
            $SB_LINES = _getLinesByConstraints($SB_LINES_CONSTRAINTS);
        }

        $xItems = [
            'id' => 'Line#',
            'sn' => 'SN#',
            'cust_asset_num' => 'Cust Asset #',
            'model' => 'Model',
            'apc_code' => 'APC',
            'apc_country_name' => 'APC Country',
            'buyer_customer_name' => 'BUYER customer',
            'user_customer_name' => 'USER customer',
            'man_location' => 'Factory',
            'sso_name' => 'SSO',
            'dgt_act' => 'GT date',
            'date_shipped' => 'Ship date',
            'part_decision' => 'Part decision',
            'service_decision' => 'Service decision',
            'status' => 'Status',
            'closure_type' => 'Closure type',
        ];

        switch ($out) {
            case 'xls':
                $report = new tldXLS(
                    $SB_LINES,
                    [
                        'xItems' => $xItems,
                        'showTitles' => true,
                    ]
                );
                $report->out("SB$id\_summary_" . date('Ymd') . '.xls');
                exit;
                break;
            default:
                $reportOptions = [
                    'xItems' => $xItems,
                    'title' => 'Affected ER list',
                    'links' => [
                        'sn' => [
                            'url' => '/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view',
                            'params' => ['id' => 'er_id'],
                            'target' => '_blank',
                        ],
                    ],
                ];

                if (_isModuleAdmin()) {
                    $reportOptions['functions'] = [
                        'Admin' => [
                            'url' => '/en/private/product_support/sb/sb_admin.php?mode=record_view&form_type=sb_lines_tpl',
                            'param' => ['id' => 'id'],
                            'img' => '/shared/icons/application/wrench.png',
                            'target' => '_blank',
                        ],
                    ];
                }

                $report = new tldReportColumnar(
                    $SB_LINES,
                    $reportOptions
                );
                $body .= $report->fetch();
                break;
        }
        break;
}


function _constructQueryFromArrayConstraints($a)
{
    if (empty($a) || !is_array($a)) {
        return $a;
    }
    return implode(' AND ', $a);
}

function _getLinesByConstraints($a)
{
    global $sb;
    if (!empty($a['need_having'])) {
        unset($a['need_having']);

        return $sb->getLinesByConstraints(_constructQueryFromArrayConstraints($a));
    }
    return $sb->getLinesByConstraints('1=1', ['where' => _constructQueryFromArrayConstraints($a)]);
}

function _getItemsPerPageSelector($current, array $options)
{
    global $php_self, $id;
    $selectOptions = '';
    foreach ($options as $option) {
        $selectedAttr = $option === $current ? ' selected="selected"' : '';
        $selectOptions .= "<option value=\"$option\"$selectedAttr>$option</option>";
    }
    return <<<EOF
<form method="get" action="$php_self" style="display:inline;">
    <input type="hidden" name="m[0]" value="sb"/>
    <input type="hidden" name="m[1]" value="view"/>
    <input type="hidden" name="m[2]" value="summary"/>
    <input type="hidden" name="m[3]" value="implementation"/>
    <input type="hidden" name="id" value="$id"/>
    Items per page:
    <select name="items_per_page" onchange="this.form.submit();">
        $selectOptions
    </select>
</form>
EOF;
}

function _isModuleAdmin()
{
    global $user;
    return $user->isInGroup(['superuser', 'role_CSD']);
}
