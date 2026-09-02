<?php
include_once 'common.inc.php';
include_once 'sales_service.inc.php';
include_once("product_support.inc.php");
include_once("forms_and_reports.inc.php");

use ApiBundle\Client;

session_start();
if(!isset($_SESSION['sess'])) $_SESSION['sess'] = null;
$sess =& $_SESSION['sess'];

// Init vars

$PATH = "/mobile";
$TITLE = "Sales App";
$PAGE = $_REQUEST['page'];
$ACTION = $_REQUEST['action'];
$PHPSELF = $_SERVER['PHP_SELF'];
$BODY = $ERROR = $WARNING = $INFO = $SUCCESS = null;

// hack for design limitation (@toto improve that)
$CSS_BODY_CLASS = "other";

// Check user and access

$user = new tldUser($_SERVER["PHP_AUTH_USER"]);

$userGroups = array_column($user->getUserGroups(), 'group_name');

// ASM ID var
$ASM_ID = $user->getID();
$client = $kernel->getContainer()->get(Client::class);

// Manage pages and actions

switch($PAGE)
{
    case 'toc':

        // Listing
        $customerList = tldCustomer::getList("smartyOptions");
        $grpService = new tldGroup("gg_SERVICE");
        $grpServiceAgents = new tldGroup("gg_SERVICE_AGENTS");
        $servicePeopleList = $grpService->getUserlist(array("smartyOptions"=>true))+$grpServiceAgents->getUserlist(array("smartyOptions"=>true));
        $ssoList = tldLocation::getSalesOrgList("smartyOptionsIDLocation");
        $peopleList = array_column(tldDirectory::getUserlist(), 'fullname', 'id');
        $unitOperationStatusList = tldTOC::getUnitOperationStatusList();
        $ifList = tldTOC::getIFList();
        $apcList = tldAirport::getList();

        switch($ACTION)
        {
            case 'create1':
                // Initialisation
                $sess['toc']['add'] = NULL;
                $_ER_FLAG = FALSE;
                $_ERID = NULL;

                // ER Default Data & Check
                $_ERSN = TldDatabase::escape($_POST['er_sn']);
                if(!empty($_ERSN)){
                    $rows = tldEquipment::bySN($_ERSN);
                    $_ERID = $rows[0]['id'];
                    $_ER_FLAG = TRUE;
                }else{
                    $WARNING = "No ER provided, will continue without ER informations";
                }

                if($_ER_FLAG){
                    $er = new tldEquipment((int)$_ERID);
                    if($er->isEmpty()){
                        $WARNING = "ER not found, will continue without ER informations";
                        $_ER_FLAG = FALSE;
                    }elseif($er->isPAS()){
                        $WARNING = "This ER is a PRE-ASSEMBLY, will not use it";
                        $_ER_FLAG = FALSE;
                    }
                }
                $titleER = $_ER_FLAG ? $er->getSN() : 'No info yet';
                $titleAPC = $_ER_FLAG ? $er->getAPC() : 'No info yet';
                $titleHours = $_ER_FLAG ? $er->getHours() : 'No info yet';

                // Form processing
                if($_POST['submit']){

                    $vars = tldUtils::cleanupFormInput($_POST);
                    $vars['postid'] = $user->getID();
                    // Look if third party
                    $vars['third_party'] = 'N';
                    if(!empty($vars['tecid'])){
                        $tecUser = new tldUser($vars['tecid']);
                        if(!$tecUser->isInTLDDomain()){
                            $vars['third_party'] = 'Y';
                        }
                    }
                    if(!empty($vars['assid'])){
                        $assUser = new tldUser($vars['assid']);
                        if(!$assUser->isInTLDDomain()){
                            $vars['third_party'] = 'Y';
                        }
                    }

                    // Check fields @todo until we have solution to do it in JS
                    $warningFields = [];
                    $missingFields = [];
                    $requiredFields = [
                        'ssoid'=>'SSO',
                        'cuid'=>'End User Customer',
                        'short_desc'=>'Problem Description (short)',
                        'ifactor'=>'Importance Factor'
                    ];
                    if($_POST['er_sn']){
                        $requiredFields = array_merge($requiredFields,[
                            'hours'=>'Hours',
                            'apc'=>'Airport Code',
                            'unit_operation_status'=>'Unit Operational Status'
                        ]);
                    }

                    foreach($requiredFields as $field=>$label)
                    {
                        if(empty($vars[$field])){
                            $missingFields[$field] = $label;
                        }
                    }

                    do
                    {
                        if(count($missingFields ?? []) > 0)
                        {
                            $ERROR = "Fields below are mandatory:<br>- ".implode('<br>- ',$missingFields);
                            break;
                        }

                        // Save data
                        $sess['toc']['add'] = $vars;
                        unset($_POST);

                        // Redirect User to Step 2
                        header("Location: $PHPSELF?page=toc&action=create2");
                    }
                    while(0);

                }

                // Display form
                $TITLE = "Create TOC - Step 1";
                $BODY = fetch('templates/toc.create1.tpl.php');
            break;
            case 'create2':
                // Reset messages
                $ERROR = $WARNING = $SUCCESS = null;
                // Check data from Step 1
                if(empty($sess['toc']['add'])){
                    $ERROR .= "STEP 1 is missing or session is expired<br>";
                    break;
                }

                $vars_session = $sess['toc']['add'];

                // Check ER or no ER mode
                $_ER_FLAG = FALSE;
                $er = new tldEquipment((int)$vars_session['erid']);
                if(!$er->isEmpty()) $_ER_FLAG = TRUE;

                // Listing
                // Get all customer info
                $sso = new tldLocation($vars_session['ssoid']);
                $cust = new tldCustomer($vars_session['cuid']);
                $langList = array_combine(extranetUser::getLanguageList(),extranetUser::getLanguageList());
                $extranetUserRawList = extranetUser::byCustomerID([$cust->getID()],"smartyOptions");
                // Update field selection view
                $extranetUserList = array();
                foreach($extranetUserRawList as $extid=>$name){
                    $ext = new extranetUser($extid);
                    $extranetUserList[$extid] = $name;
                    // Add lang if set
                    $lang = $ext->getPreferedLanguage();
                    if(empty($lang)){
                        $lang = "no lang";
                    }
                    $extranetUserList[$extid].= " ($lang)";
                }
                if(empty($extranetUserList)){
                    $WARNING .= "No contact found for customer ".$cust->getCustomerName()."<br>";
                }
                // Look for default additional contacts
                $extranetUserRoleTOCRawList = extranetUser::bySSOCustomerRole($sso->getID(),$cust->getID(),'fl_NOT_TOC');
                $extranetUserRoleTOCList = array_column($extranetUserRoleTOCRawList, 'fullname', 'id');
                // Get CRT listing
                $crtCustomerSSOList = tldCRT::byCustomerIDSSOID($cust->getID(),$vars_session['ssoid']);
                // If no CRT, Create one
                if(empty($crtCustomerSSOList)){
                    // Create new crt
                    $resultCrt = tldCRT::processCreation($cust->getID(), $sso->getID());
                    if(is_string($resultCrt)){
                        $ERROR .= <<<EOF
Could not create automatically missing CRT for eCustomer#{$cust->getID()}. Reason: $resultCrt<br>
EOF;
                        break;
                    }
                    $SUCCESS .= "CRT#$resultCrt created automatically for eCustomer#{$cust->getID()} in SSO {$sso->getShortName()}<br>";
                    // Assign new CRT to the list
                    $newCRT = new tldCRT($resultCrt);
                    $crtCustomerSSOList[] = $newCRT->itsHeader;
                }
                $crtList=array();
                foreach($crtCustomerSSOList as $crt){
                    $crtList[$crt['id']]="CRT#{$crt['id']} - {$crt['erp']} - {$crt['cuno']}";
                }

                // Form processing
                if($_POST['submit']){

                    $missingFields = [];
                    $vars = tldUtils::cleanupFormInput($_POST);
                    // Add Session also
                    $vars = array_merge($vars_session,$vars);
                    // Set erid field in TOC if any
                    if($_ER_FLAG) $vars['erid'] = $er->getID();
                    if($vars['disable_not_value'] == "Y") $vars['disable_not'] = TRUE;

                    do
                    {

                        // Check if contact entered or selected
                        if(empty($vars['conid']) && empty($vars['con']['email'])){
                            $missingFields['conid'] = "Main Customer Contact";
                            $missingFields['con']['email'] = "New Contact Email";
                            $ERROR .= "Please select a customer contact or enter a new contact<br>";
                            break;
                        }
                        // Check if toc not is disabled and reason is correctly set
                        if($vars['disable_not'] && empty($vars['disable_reason'])){
                            $missingFields['disable_reason'] = "Please Justify";
                            $ERROR .= "If you disable the TOC NOT, make sure to justify<br>";
                            break;
                        }

                        // Case of new contact
                        if(empty($vars['conid'])){
                            $con_fields = array("crtid"=>"CRT","lastname"=>"Lastname","firstname"=>"Firstname","email"=>"Email","phone"=>"Phone","lang"=>"Language");
                            foreach($con_fields as $field=>$label){
                                if(empty($vars['con'][$field])){
                                    $missingFields['con'][$field] = $label;
                                    $ERROR .= "For new contact creation, all fields are mandatory -> $label missing<br>";
                                    break 2;
                                }
                            }
                            // Extra fields
                            $vars['con']['customer_name'] = $cust->getCustomerName();
                            $vars['con']['userid'] = $vars['con']['email'];
                            $vars['con']['enable'] = 'N';
                            $vars['con']['cuid'] = $cust->getID();

                            // Insert/Update Extranet user
                            $data = extranetUser::createFromTOC($vars['con'],$user->getID(), $con_fields);

                            // Confirmation messages
                            if($data['success']) $SUCCESS .= implode("<br>",$data['success']);
                            if($data['error']) {
                                $ERROR .= implode("<br>",$data['error']);
                                break;
                            }

                            // Assign the TOC customer contact
                            $vars['conid'] = $data['xu_id'];
                        }else{
                        // Contact was selected
                            $extUser = new extranetUser($vars['conid']);
                            // If Contact has no language, force user to set the language
                            $lang = $extUser->getPreferedLanguage();
                            if(empty($lang) && empty($vars['lang'])){
                                $missingFields['lang'] = "Set Language";
                                $ERROR .= "Contact selected has no language setup, you must choose one to continue<br>";
                                break;
                            }
                        }

                        // Check if there is any file
                        $file = $_FILES["attachment"];
                        if($file['tmp_name']!=""){
                            $fileUpload = $file;
                        }else{
                            $fileUpload = [];
                        }

                        // Create TOC
                        try
                        {
                            $e = tldTOC::create($vars,$fileUpload);
                        }
                        catch (Exception $e)
                        {
                            $ERROR .= "Could not create TOC. Reason: {$e->getMessage()}<br>";
                            break;
                        }
                        // Confirm
                        $toc = new tldTOC($e['tocid']);
                        $SUCCESS .= "<br>TOC#{$toc->getID()} successfully created!";
                        if($e['warning']) $WARNING .= "<br>".implode("<br>",$e['warning']);
                        if($e['error']) $ERROR .= "<br>".implode("<br>",$e['error']);
                        if($e['success']) $SUCCESS .= "<br>".implode("<br>",$e['success']);

                        // Clean session
                        $sess['toc']['add'] = NULL;
                        unset($_POST);
                    }
                    while(0);

                }

                // Display form
                $TITLE = "Create TOC - Step 2";
                $BODY = fetch('templates/toc.create2.tpl.php');
            break;
            case 'listing':
                $constraint = NULL;
                $customer_id = TldDatabase::escape($_POST['toc_by_customer']);
                if ('' !== $asmID = TldDatabase::escape($_POST['asm_id'])) {
                    $ASM_ID = $asmID;
                }

                if(empty($customer_id) OR !is_numeric($customer_id)){
                    $ERROR =  "No Customer ID set...";
                    break;
                }
                $customer = new tldCustomer($customer_id);
                if($customer->isEmpty()){
                    $ERROR =  "No Customer#$customer_id found...";
                    break;
                }
                $customer_name = $customer->getCustomerName();

                $constraint .= " asm = $ASM_ID AND toc.cuid=$customer_id AND (toc.dt_closed='0000-00-00' OR DATEDIFF(toc.dt_closed,NOW()) > -28) ";
                $tocListing = tldTOC::byConstraints($constraint);


                // Display form
                $TITLE = "My TOCs for $customer_name";
                $BODY = fetch('templates/toc.listing.tpl.php');
                break;
            case 'update':
                $toc_id = TldDatabase::escape($_POST['toc_id']);
                if(empty($toc_id) OR !is_numeric($toc_id)){
                    $ERROR =  "No TOC ID set...";
                    break;
                }
                $toc = new tldTOC($toc_id);
                if($toc->isEmpty()){
                    $ERROR =  "No TOC#$toc_id found...";
                    break;
                }

                if($toc->getStatus()=="CLOSED"){
                    $ERROR = "ERROR: Can not add log or sent notification when TOC is CLOSED";
                    break;
                }

                // Header
                $header = $toc->getHeader();
                $erid = $toc->getERID();
				$er =  new tldEquipment($erid);
				$er_header = $er->getHeader();
				$cuid = $toc->getCUID();
				$conid = $toc->getCONID();
				$extUser = new ExtranetUser($conid);
				// Logs
				$logs = $toc->getNotificationFullLog();
				// Titles
				$langNotif = strtoupper($toc->getLangNotification());
				$titleLog = "<b>Language to use:</b> Must be EN only!";
                $titleNot = "{$extUser->getFullname()} <em>({$extUser->getEmail()}) [{$extUser->getPreferedLanguage()}]</em><br><b>Language to use:</b> $langNotif";

                // Form processing
                if($_POST['submit']){

                    $warningFields = [];
                    $missingFields = [];
                    $requiredFields = [];

                    if( empty($_POST['log']) && empty($_POST['not']['comment']))
                    {
                        $warningFields['log']='Internal Log & Communication';
                        $warningFields['not']='Notification Email to Customer';
                    }

                    do
                    {
                        if(count($warningFields ?? []) > 0)
                        {
                            $WARNING = "At least one field below must be set:<br>- ".implode('<br>- ',$warningFields);
                            break;
                        }

                        // TOC LOG if any
                        if(!empty($_POST['log'])){
                            $log = TldDatabase::escape($_POST['log']);
                            try
                            {
                                $e = $toc->addLogEntry($user->getID(), $log, 1);
                            }
                            catch (Exception $e)
                            {
                                $ERROR = "Problem adding TOC log comment... Reason: {$e->getMessage()}";
                                break;
                            }
                            $SUCCESS = "TOC log comment added successfully!";
                        }

                        // TOC NOT customer
                        if(!empty($_POST['not']['comment'])){
                            // Check if customer notification is set
                            if(!$toc->isNotificationEnable()){
                                $DEFAULT_ERROR[] = "ERROR: Notification not sent...<br/>Reason: TOC was not set to notify the customer";
                                break;
                            }

                            // Build email
                            $recipients = explode(',' ,nl2br($_POST['not']['other_recipient']));
                            $comment = stripslashes(mb_convert_encoding(nl2br($_POST['not']['comment']), 'UTF-8', mb_list_encodings()));
                            $email_body = $toc->getInProgressEmailContents($comment);

                            // Send email to customer & log
                            if (!empty($customerFile)) {
                                move_uploaded_file($_FILES['customer_file']['tmp_name'], $_FILES['customer_file']['name']);
                            }
                            try {
                                $e = $toc->notifyCustomer('New notification', $email_body, $_FILES['customer_file'],$comment, $recipients);
                                $body .= '<p>TOC notification sent to customer successfully!</p>';
                            } catch (Exception $e) {
                                $DEFAULT_ERROR[] = "ERROR: Unable to send notification to customer...<br/>Reason: $e";
                            }
                        }

                        // Cleanup
                        unset($_POST);
                    }
                    while(0);
                }

                // Display form
                $TITLE = "Update TOC#$toc_id";
                $BODY = fetch('templates/toc.update.tpl.php');
            break;
            case 'view':
                $toc_id = TldDatabase::escape($_POST['toc_id']);
                if(empty($toc_id) OR !is_numeric($toc_id)){
                    $ERROR =  "No TOC ID set...";
                    break;
                }
                $toc = new tldTOC($toc_id);
                if($toc->isEmpty()){
                    $ERROR =  "No TOC#$toc_id found...";
                    break;
                }

                // Header & Logs
                $header = $toc->getHeader();
                $logs = $toc->getFullLog();

                // Main TOC file
                $fileHTML = NULL;
                $fileInfo = $toc->getMainFile();
                if(!empty($fileInfo)){
                    $file = new BasicFile($fileInfo['filepath']);
                    $typeMime = $file->getMime();
                    $typeMimeSplited = preg_split("#/#", $typeMime);
                    if(strtolower($typeMimeSplited[0])=="image"){
                        $fileHTML = <<<EOF
<a href="/en/private/common/index.php?m[0]=files&m[1]=view&id={$fileInfo['id']}" target=_blank><img src="data:$typeMime;base64,{$file->getBase64()}" width="200" /><br>{$fileInfo['filename']}</a>
EOF;
                    }else{
                        $fileHTML = <<<EOF
<a href="/en/private/common/index.php?m[0]=files&m[1]=view&m[2]=out&id={$fileInfo['id']}"><img src="/shared/bluesphere/64x64/mimetypes/document.png" /><br>{$fileInfo['filename']}</a>
EOF;
                        }
                }else{
                    $fileHTML = "No file";
                }

                // Display form
                $TITLE = "TOC#$toc_id";
                $BODY = fetch('templates/toc.view.tpl.php');
            break;
        }
    break;
    case 'odp':

        switch($ACTION)
        {
            case 'listing':
                $constraint = NULL;
                $customer_id = TldDatabase::escape($_POST['odp_by_customer']);
                if ('' !== $asmID = TldDatabase::escape($_POST['asm_id'])) {
                    $ASM_ID = $asmID;
                }

                if($customer_id){
                    if(empty($customer_id) OR !is_numeric($customer_id)){
                        $ERROR =  "No Customer ID set...";
                        break;
                    }
                    $customer = new tldCustomer($customer_id);
                    if($customer->isEmpty()){
                        $ERROR =  "No Customer#$customer_id found...";
                        break;
                    }
                    $customer_name = $customer->getCustomerName();
                    $title_customer_name = "for $customer_name";
                    $constraint .= " er_buyer_customer_display = '$customer_name' AND ";
                }

                $constraint .= " (er.date_shipped='0000-00-00' OR DATEDIFF(er.date_shipped,NOW()) > -28) AND sor.asm = $ASM_ID";
                $odpListing = tldODP::byConstraints($constraint);


                // Display form
                $TITLE = "My Deliveries $title_customer_name";
                $BODY = fetch('templates/odp.listing.tpl.php');
            break;
            case 'view':
                $er_id = TldDatabase::escape($_POST['er_id']);
                if(empty($er_id) OR !is_numeric($er_id)){
                    $ERROR =  "No ER ID set...";
                    break;
                }
                $er = new tldEquipment($er_id);
                if($er->isEmpty()){
                    $ERROR =  "No ER#$er_id found...";
                    break;
                }

                $odpListing = tldODP::byConstraints("sn = '{$er->getSN()}'");

                // Display form
                $TITLE = "My Deliveries for {$er->getSN()}";
                $BODY = fetch('templates/odp.view.tpl.php');
            break;
        }
    break;
    case 'customers':
        if( !$user->isInGroup(['role_ASM','role_CSM','role_EVP','role_CEO','role_COO','GG_SALES_AGENTS', 'role_VPM', 'role_GCOO','role_GTD', 'role_CSD'])
            && $user->getDomain()!='tld-group.com' ) {
            $ERROR = "You do not have permissions";
            break;
        }
        switch ($ACTION) {
            case 'listing':

                // Listing
                $activeCustomerList = tldCustomer::getActiveListByASM($ASM_ID, "smartyOptions");
                if (empty($activeCustomerList)) {
                    $ERROR = "You do not own any customers with recent activities...";
                    break;
                }
                // Display form
                $TITLE = "My Customers";
                $BODY = fetch('templates/customers.listing.tpl.php');
            break;
            case 'view':
                $customer_id = TldDatabase::escape($_POST['customer_id']);
                $ASM_ID = TldDatabase::escape($_POST['asm_id']);

                if (empty($customer_id) OR !is_numeric($customer_id)) {
                    $ERROR = "No Customer ID set...";
                    break;
                }

                $customer = new tldCustomer($customer_id);
                if ($customer->isEmpty()) {
                    $ERROR = "No Customer#$customer_id found...";
                    break;
                }
                $customer_name = $customer->getCustomerName();

                $SFR_COUNT = 0 + count(tldSFR::byOpenStatusConstraints("(buyer_customer_id = $customer_id OR user_customer_id = $customer_id OR cust_nama = \"$customer_name\") AND sfr.asm_id = $ASM_ID"));
                $TOC_COUNT = 0 + count(tldTOC::byConstraints("asm = $ASM_ID AND toc.cuid=$customer_id AND (toc.dt_closed='0000-00-00' OR DATEDIFF(toc.dt_closed,NOW()) > -28)"));
                $DELIVERIES_COUNT = 0 + count(tldODP::byConstraints("er_buyer_customer_display = \"$customer_name\" AND (er.date_shipped='0000-00-00' OR DATEDIFF(er.date_shipped,NOW()) > -28) AND sor.asm = $ASM_ID"));

                // Display form
                $TITLE = $customer_name;
                $BODY = fetch('templates/customers.view.tpl.php');
            break;
            case 'myASMList':
                // Listing
                $subordinatesList = $user->getSubordinates();
                $asmList = [];
                foreach ($subordinatesList as $subordinate) {
                    $subUser = new tldUser($subordinate['email']);
                    if ($subUser->isInGroup('role_ASM')) {
                        array_push($asmList, $subordinate);
                    }
                }

                if (empty($asmList)) {
                    $ERROR = "You do not own any ASM in your team.";
                    break;
                }
                // Display form
                $TITLE = "My ASM list";
                $BODY = fetch('templates/asm.listing.tpl.php');
            break;
            case 'myASMView':
                $ASM_ID = TldDatabase::escape($_POST['asm_id']);
                $options['constraintsSFR'] = "sfr.cust_nama = customers.customer_name AND
                    sfr.asm_id = $ASM_ID";
                $options['constraintsTOC'] = "toc.status NOT IN ('CLOSED', 'SOLVED')";
                $options['constraintsSOR'] = "sor.status IN ('IN_PROGRESS','PENDING') AND
                    sor.asm = $ASM_ID AND
                    sor.dt_closed='0000-00-00' AND
                    (er.date_shipped='0000-00-00' OR
                    DATEDIFF(er.date_shipped,NOW()) > -28)";
                // Listing
                $activeCustomerList = tldCustomer::getActiveListByASM($ASM_ID, "smartyOptions", $options);

                if (empty($activeCustomerList)) {
                    $ERROR = "You do not manage any customers with recent activities...";
                    break;
                }

                // Display form
                $TITLE = "My Customers";
                $BODY = fetch('templates/customers.listing.tpl.php');
            break;
            case 'SSOList':
                if(!$user->isInGroup(['role_CEO','ROLE_LATE_GT_GCEO', 'role_VPM', 'role_GCOO','role_GTD', 'role_CSD'])
                    && $user->getDomain()!='tld-group.com' ) {
                    $ERROR = "You do not have permissions";
                    break;
                }
                // Listing
                $ssoList = [];

                // If RCEO
                $constraint =
                    in_array('role_RCEO', $userGroups, true) && isset($user->itsDetails['division']) ?
                        sprintf('&businessUnit.region.subDivision.division.name=%s', $user->itsDetails['division']) :
                        ''
                    ;
                $ssoList = $client->get(sprintf('/locations?state.disabled=0&capability.sso=1%s&order[name]=asc', $constraint));
                $ssoList = $ssoList['hydra:member'];

                // Display form
                $TITLE = "SSO list";
                $BODY = fetch('templates/sso.listing.tpl.php');
            break;
            case 'SSOView':
                $ssoId = TldDatabase::escape($_POST['sso_id']);
                $ssoLegacyId = TldDatabase::escape($_POST['sso_legacy_id']);
                // Listing
                $activeCustomerList = $client->get(sprintf('/sales/customers?&active=1&contactPointsBusinessUnits=%s&order[name]=asc', $ssoId));
                $activeCustomerList = $activeCustomerList['hydra:member'];
                if (empty($activeCustomerList)) {
                    $ERROR = "There is not any customer in this SSO...";
                    break;
                }
                // Display form
                $TITLE = sprintf('Customers - %s', $_POST['sso_name']);
                $BODY = fetch('templates/ssoCustomers.listing.tpl.php');
            break;
            case 'listActivesForCustomer':
                $customerId = TldDatabase::escape($_POST['customer_id']);
                $customerLegacyId = TldDatabase::escape($_POST['customer_legacy_id']);
                $ssoLegacyId = TldDatabase::escape($_POST['sso_legacy_id']);
                if(empty($customerId) OR !is_numeric($customerId)){
                    $ERROR =  "No Customer ID set...";
                    break;
                }
                $customer = new tldCustomer($customerLegacyId);
                if($customer->isEmpty()){
                    $ERROR =  "No Customer#$customerId found...";
                    break;
                }
                $customerName = $customer->getCustomerName();
                $sfrListing = $client->get(sprintf('/sales/sales_forecasts?buyer=%s&sso.legacyId=%s', $customerId, $ssoLegacyId));
                $sfrListing = $sfrListing['hydra:member'];
                $tocListing = tldTOC::byConstraints(" toc.cuid=$customerLegacyId AND toc.ssoid = $ssoLegacyId" );
                $deliveriesListing = $client->get(
                    sprintf(
                        '/equipment_records?normalization_groups[]=odp:view&normalization_groups[]=order_factory&notShipped=1&order[id]=DESC&salesOrganisation.legacyId=%s&buyer=%s',
                        $ssoLegacyId,
                        $customerId
                    )
                );
                $deliveriesListing = $deliveriesListing['hydra:member'];

                // Display form
                $TITLE = "$customerName";
                $BODY = fetch('templates/sfrTocDeliveries.listing.tpl.php');
        }
    break;
    case 'sfr':
        switch($ACTION)
        {
            case 'create':
            case 'view':
            case 'update':
            case 'listing':
                $BODY = 'This page has been migrated and should not be displayed anymore';
                break;
        }
    break;
    case 'mim':
        switch($ACTION)
        {
            case 'create':
                $BODY = 'This page has been migrated and should not be displayed anymore.';
            break;
        }
    break;
    default:
        // hack for design limitation (@toto improve that)
        $CSS_BODY_CLASS = "home";
        // Listing
        $asmCustomerList = tldCustomer::getActiveListByASM($ASM_ID, "smartyOptions");
        $BODY = fetch('templates/homepage.tpl.php');
    break;
}

global $kernel;

$ticket = $kernel->getContainer()->get('twig.legacy')->render('partial/_ticket.html.twig');

$HTML = fetch('templates/template.tpl.php');
echo $HTML;


function fetch($path){
    // get globals
    extract($GLOBALS, EXTR_OVERWRITE);
    // create buffer
    ob_start();
    include $path;
    $_output = ob_get_contents();
    ob_end_clean();
    // return result
    return $_output;
}
