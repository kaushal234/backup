<?php
$DEFAULT_TITLE .= "\CSR Module";
$mooID = tldModule::getMOOIDByModule("CSR");

$DEFAULT_TITLE .= "\CSR Module";
switch ($m[1]) {
    case 'quickEdit':
        $body = 'This page has been migrated and should not be displayed anymore.';
        break;
    case 'forms':
        switch ($m[2]) {
            case 'byNum':
            case 'byOldCSR':
            case 'bySR':
            case 'add':
            case 'add2':
                $body = 'This page has been migrated and should not be displayed anymore.';
                break 2;
        }

        $body = 'This page has been migrated and should not be displayed anymore.';
        break;
    case 'view':
        include('csr/csr.view.inc.php');
        break;
    case 'reports':
        $DEFAULT_TITLE .= "\Reports";
        switch ($m[2]) {
            case 'costByCriteria':
                // Listing
                $moduleList = tldCSR::getModuleList();
                $ssoList = tldLocation::getSalesOrgList("smartyOptionsIDLocation");
                $workTypeList = tldCSR::getWorkTypeList();
                $customerList = tldCustomer::getList("smartyOptions");
                $billToList = tldCSR::getBillToList();
                $curList = tldForex::getCurrencyList();
                // Form
                $form = new HTML_QuickForm('frmByNum', 'post');
                $form->addElement(  'hidden', 'm[0]', 'csr');
                $form->addElement(  'hidden', 'm[1]', 'reports');
                $form->addElement(  'hidden', 'm[2]', 'costByCriteria');
                $form->addElement(  'header', 'title', 'Report criteria');
                $form->addElement(  'header', 'title', 'Select currency to display');
                $form->addElement(  'select', 'cur', 'Currency', array(""=>"")+$curList);
                $form->addElement(  'header', 'title', 'Competed date range selection');
                $form->addElement(  'text', 'dt_completed_from', 'Completed date From', array("class"=>"datepicker"));
                $form->addElement(  'text', 'dt_completed_to', 'Completed date To', array("class"=>"datepicker"));
                $form->addElement(  'header', 'title', 'Close date range selection');
                $form->addElement(  'text', 'dt_from', 'Closed date From', array("class"=>"datepicker"));
                $form->addElement(  'text', 'dt_to', 'Closed date To', array("class"=>"datepicker"));
                $form->addElement(  'header', 'title', 'CSR criteria');
                $form->addElement(  'select', 'sso_id', 'SSO', array(""=>"")+$ssoList);
                $form->addElement(  'select', 'work_type', 'Work type', array(""=>"")+$workTypeList);
                $form->addElement(  'select', 'customer_id', 'USER customer', array(""=>"")+$customerList);
                $form->addElement(  'select', 'module', 'Module', array(""=>"")+$moduleList);
                $form->addElement(  'select', 'bill_to', 'Bill to', array(""=>"")+$billToList);
                $form->addElement(  'submit', 'btnSubmit', 'Submit', array("class"=>"disablesubmit"));
                $form->addRule('cur', 'Required', 'required');
                $form->addRule('sso_id', 'Required', 'required');
                $form->setDefaults(array(
                    'sso_id'=>$user->getBUID(),
                    'cur'=>'USD',
                ));

                if(!$form->validate()){
                    $body = $form->toHTML();
                    break;
                }

                $vars = tldUtils::cleanupFormInput($form->exportValues());

                $a = formConstraints($form, $vars, ['sso_id','work_type','customer_id','module','bill_to','dt_from','dt_to', 'dt_completed_from', 'dt_completed_to'], $DEFAULT_ERROR);

                // Check if enough constraints
                if (empty($a) || !empty($DEFAULT_ERROR)) {
                    $DEFAULT_ERROR[] = "ERROR: Not enough constraints set";
                    $body = $form->toHTML();
                    break;
                }
                $WHERE = implode(' AND ',$a);

                // Get results ----------------------------------->

                $rows = tldCSR::getCostsTotalsByConstraints($vars['cur'],$WHERE);
                if(count($rows)<1){
                    $DEFAULT_ERROR[] = "ERROR: No CSR found...";
                    $body = $form->toHTML();
                    break;
                }
                $sess['csr']['listing'] = $rows;
                header("Location: $php_self?m[0]=csr&m[1]=listing&m[2]=costByCriteria");
                break;
            case 'SBCostByCriteria':
                // Listing
                $moduleList = tldCSR::getModuleList();
                $ssoList = tldLocation::getSalesOrgList("smartyOptionsIDLocation");
                $workTypeList = tldCSR::getWorkTypeList();
                $customerList = tldCustomer::getList("smartyOptions");
                $billToList = tldCSR::getBillToList();
                $curList = tldForex::getCurrencyList();
                // Form
                $form = new HTML_QuickForm('frmByNum', 'post');
                $form->addElement('hidden', 'm[0]', 'csr');
                $form->addElement('hidden', 'm[1]', 'reports');
                $form->addElement('hidden', 'm[2]', 'SBCostByCriteria');
                $form->addElement('header', 'title', 'Report criteria');
                $form->addElement('header', 'title', 'Select currency to display');
                $form->addElement('select', 'cur', 'Currency', ["" => ""] + $curList);
                $form->addElement('header', 'title', 'Competed date range selection');
                $form->addElement('text', 'dt_completed_from', 'Completed date From', array("class"=>"datepicker"));
                $form->addElement('text', 'dt_completed_to', 'Completed date To', array("class"=>"datepicker"));
                $form->addElement('header', 'title', 'Close date range selection');
                $form->addElement('text', 'dt_from', 'Closed date From', ["class" => "datepicker"]);
                $form->addElement('text', 'dt_to', 'Closed date To', ["class" => "datepicker"]);
                $form->addElement('header', 'title', 'CSR criteria');
                $form->addElement('select', 'sso_id', 'SSO', ["" => ""] + $ssoList);
                $form->addElement('select', 'customer_id', 'USER customer', ["" => ""] + $customerList);
                $form->addElement('select', 'bill_to', 'Bill to', ["" => ""] + $billToList);
                $form->addElement('submit', 'btnSubmit', 'Submit', ["class" => "disablesubmit"]);
                $form->addRule('cur', 'Required', 'required');
                $form->addRule('sso_id', 'Required', 'required');
                $form->setDefaults([
                    'sso_id' => $user->getBUID(),
                    'cur' => 'USD',
                ]);

                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break;
                }

                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $a = formConstraints($form, $vars, ['sso_id', 'customer_id','bill_to','dt_from','dt_to', 'dt_completed_from', 'dt_completed_to'], $DEFAULT_ERROR);

                // Check if enough constraints
                if (empty($a) || !empty($DEFAULT_ERROR)) {
                    $DEFAULT_ERROR[] = "ERROR: Not enough constraints set";
                    $body = $form->toHTML();
                    break;
                }
                $WHERE = implode(' AND ', $a);
                $WHERE .= " AND csr.work_type LIKE 'Service Bulletin'";
                // Get results ----------------------------------->
                $opt['where'] = "LEFT JOIN sb ON sb.id=csr.module_id ";
                $opt['select'] = "sb.title, sb.category,sb.labor,sb.nb_tech_needed,(sb.labor*sb.nb_tech_needed) AS total_labor,";
                $rows = tldCSR::getCostsTotalsByConstraints($vars['cur'], $WHERE, $opt);
                if (count($rows) < 1) {
                    $DEFAULT_ERROR[] = "ERROR: No CSR found...";
                    $body = $form->toHTML();
                    break;
                }
                $sess['csr']['listing'] = $rows;
                header("Location: $php_self?m[0]=csr&m[1]=listing&m[2]=SBCostByCriteria");
                break;
            default:
                $body = 'This page has been migrated and should not be displayed anymore.';
                break 2;
        }

        break;
    case 'listing':
        switch ($m[2]) {
            case 'bySSOStatus':
                switch ($m[3]) {
                    case 'update':
                    case 'edit':
                        $body = 'This page has been migrated and should not be displayed anymore.';
                        break;
                }

                $body = 'This page has been migrated and should not be displayed anymore.';
                break;
            case 'byModuleStatus':
            case 'search':
            case 'commissioningResultsByCriteria':
            case 'bySSObyStatusWithPNAndSPR':
                $body = 'This page has been migrated and should not be displayed anymore.';
                break 2;
            case 'costByCriteria':
                $_title = "Cost report by criteria";
                // Default columns
                $xItems = array(
                    "id"=>"CSR#",
                    "dt"=>"Date",
                    "dt_completed"=>"Date Completed",
                    "dt_closed"=>"Date Closed",
                    "sso"=>"SSO",
                    "status"=>"Status",
                    "work_type"=>"Work Type",
                    "tech_fullname"=>"Technician",
                    "user_customer"=>"USER customer",
                    "sn"=>"SN",
                    "apc"=>"APC",
                    "module"=>"From module",
                    "module_id"=>"Module Ref#",
                    "bill_to"=>"Bill to",
                    "bill_instruction"=>"Billing instruction",
                );
                // Add list of possible cost type
                $costTypeList = tldModCost::getCostTypeByModule("CSR");
                foreach($costTypeList as $k=>$costType){
                    $xItems["cost_$k"]=$costType['type'];
                }
                $xItems['erp_inv'] = "ERP Invoice";
                $xItems['short_desc'] = "Short Description";
                $xItems['cost_total']="Total";
                $xItems['cur']="Currency";
                // Assign rows from session
                $rows = $sess['csr']['listing'];

                switch($out){
                    case 'xls':
                        $sess['csr']['xItems']['dt_work'] = 'Work date';
                        $sess['csr']['xItems']['dt_completed'] = "Completed date";
                        $report = new tldXLS(
                            $sess['csr']['listing'],
                            array(
                                "xItems"=>$sess['csr']['xItems'],
                                "showTitles"=>true
                            )
                        );
                        $report->out();
                        exit;
                    default:
                        if(count($rows)<1){
                            $DEFAULT_ERROR[] = "ERROR: No CSR found...";
                            break;
                        }
                        $DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;
<a href="$php_self?m[0]=csr&m[1]=listing&m[2]=costByCriteria&out=xls">XLS version</a>
EOF;
                        $sess['csr']['listing'] = $rows;
                        $sess['csr']['xItems'] = $xItems;
                        unset($xItems['bill_instruction']);
                        $body.= _getListing($rows, $_title, $xItems);
                        break;
                }
            break;
            case 'SBCostByCriteria':
                $_title = "SB Cost report by criteria";
                // Default columns
                $xItems = [
                    "id" => "CSR#",
                    "dt" => "Date",
                    "dt_completed"=>"Date Completed",
                    "dt_closed" => "Date Closed",
                    "sso" => "SSO",
                    "factory"=>"Factory",
                    "status" => "Status",
                    "work_type" => "Work Type",
                    "tech_fullname" => "Technician",
                    "user_customer" => "USER customer",
                    "sn" => "SN",
                    "apc" => "APC",
                    "module" => "From module",
                    "module_id" => "Module Ref#",
                    "title" => "SB Title",
                    "category" => "SB Category",
                    "labor" => "Labor",
                    "nb_tech_needed" => "Number of technican needed",
                    "total_labor" => "Total labor in mintutes",
                    "bill_to" => "Bill to",
                    "bill_instruction"=>"Billing instruction",
                ];
                // Add list of possible cost type
                $costTypeList = tldModCost::getCostTypeByModule("CSR");
                foreach($costTypeList as $k=>$costType){
                    $xItems["cost_$k"]=$costType['type'];
                }
                $xItems['cost_total']="Total";
                $xItems['cur']="Currency";
                // Assign rows from session
                $rows = $sess['csr']['listing'];

                switch($out){
                    case 'xls':
                        $sess['csr']['xItems']['dt_work'] = 'Work date';
                        $sess['csr']['xItems']['dt_completed'] = "Completed date";
                        $report = new tldXLS(
                            $sess['csr']['listing'],
                            array(
                                "xItems"=>$sess['csr']['xItems'],
                                "showTitles"=>true
                            )
                        );
                        $report->out();
                        exit;
                    default:
                        if(count($rows)<1){
                            $DEFAULT_ERROR[] = "ERROR: No CSR found...";
                            break;
                        }
                        $DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;
<a href="$php_self?m[0]=csr&m[1]=listing&m[2]=SBCostByCriteria&out=xls">XLS version</a>
EOF;
                        $sess['csr']['listing'] = $rows;
                        $sess['csr']['xItems'] = $xItems;
                        unset($xItems['bill_instruction']);
                        $body.= _getListing($rows, $_title, $xItems);
                        break;
                }
                break;
        default:
            $body = 'This page has been migrated and should not be displayed anymore.';
            break;
        }
}

function _getListing($rows, $_title, $xItems=NULL){
    global $php_self;
    if(empty($xItems)){
        $xItems = array(
            "id"=>"CSR#",
            "dt"=>"Date",
            "sso"=>"SSO",
            "status"=>"Status",
            "work_type"=>"Work Type",
            "tech_fullname"=>"Technician",
            "send_tech"=>"Send Tech?",
            "dt_sche"=>"Scheduled Date",
            "dt_work"=>"Work date",
            "short_desc"=>"Short description",
            "sn"=>"SN",
            "model"=>"Model",
            "apc"=>"APC",
            "user_customer"=>"USER customer",
            "module"=>"From module"
        );
    }
    $report = new tldReportColumnar(
        $rows,
        array(
            "xItems"=>$xItems,
            "title"=>$_title,
            "links"=>array(
                "id"=>"$php_self?m[0]=csr&m[1]=view&id="
            ),
            "showzero"=>TRUE
        )
    );
    return $report->fetch();
}

function formConstraints(HTML_QuickForm $form, array $vars, array $searchFields, array &$DEFAULT_ERROR)
{
    $a = [];
    // Prepare constraints
    foreach ($searchFields as $field) {
        if (empty($vars[$field])) {
            continue;
        }

        switch($field){
            case 'dt_completed_from':
                // check date
                try{
                    $date = new DateTime($vars[$field]);
                }catch(Exception $e){
                    $DEFAULT_ERROR[] = "ERROR: Completed Date from invalid for {$vars[$field]}";
                    $body = $form->toHTML();
                    break;
                }
                // constraint
                $a[] = "DATEDIFF(dt_completed,'{$vars[$field]}')>=0";
                break;
            case 'dt_completed_to':
                // check date
                try{
                    $date = new DateTime($vars[$field]);
                }catch(Exception $e){
                    $DEFAULT_ERROR[] = "ERROR: Completed Date to invalid for {$vars[$field]}";
                    $body = $form->toHTML();
                    break;
                }
                // constraint
                $a[] = "DATEDIFF(dt_completed,'{$vars[$field]}')<=0";
                break;
            case 'dt_from':
                // check date
                try{
                    $date = new DateTime($vars[$field]);
                }catch(Exception $e){
                    $DEFAULT_ERROR[] = "ERROR: Close Date from invalid for {$vars[$field]}";
                    $body = $form->toHTML();
                    break;
                }
                // constraint
                $a[] = "DATEDIFF(dt_closed,'{$vars[$field]}')>=0";
                break;
            case 'dt_to':
                // check date
                try{
                    $date = new DateTime($vars[$field]);
                }catch(Exception $e){
                    $DEFAULT_ERROR[] = "ERROR: Close Date to invalid for {$vars[$field]}";
                    $body = $form->toHTML();
                    break;
                }
                // constraint
                $a[] = "DATEDIFF(dt_closed,'{$vars[$field]}')<=0";
                break;
            default:
                $a[] = "$field LIKE '{$vars[$field]}'";
                break;
        }
    }

    if ((empty($vars['dt_from']) && empty($vars['dt_to'])) && (empty($vars['dt_completed_from']) && empty($vars['dt_completed_to']))) {
        $DEFAULT_ERROR[] = "ERROR: You must select at least one range date";
    }

    return $a;
}

