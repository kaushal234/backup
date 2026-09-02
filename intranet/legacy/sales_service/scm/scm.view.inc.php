<?php
if(empty($id) || empty($erp)){
    $DEFAULT_ERROR[] = "ERROR: parameters empty or invalid";
    return;
}
$id = TldDatabase::escape($id);
$erp = TldDatabase::escape($erp);
$scm = new tldSCM($id,$erp);
if($scm->isEmpty()){
    $DEFAULT_ERROR[] = "ERROR: SCM# $id in ERP $erp not found";
    return;
}

$DEFAULT_TITLE .= "\SCM#$id ($erp)";
$DEFAULT_MENU.=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=scm&m[1]=view&erp=$erp&id=$id">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=scm&m[1]=view&m[2]=er&erp=$erp&id=$id">ER fleet</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=scm&m[1]=view&m[2]=service&erp=$erp&id=$id">Service</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=scm&m[1]=view&m[2]=members&erp=$erp&id=$id">Members</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=scm&m[1]=view&m[2]=reports&erp=$erp&id=$id">Reports</a>
EOF;

switch($m[2]){
case 'er':
    $DEFAULT_TITLE .= "\ER fleet";
    $DEFAULT_MENU.=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=scm&m[1]=view&m[2]=er&erp=$erp&id=$id">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=scm&m[1]=view&m[2]=er&m[3]=hourmeterQuickEdit&erp=$erp&id=$id">Hourmeter review</a>
EOF;

    // Construct global ER constraints
    $ER_CONSTRAINTS = <<<EOF
maintenance_contract_ref LIKE '{$scm->getID()}'
AND maintenance_contract_erp LIKE '{$scm->getERP()}'
EOF;

    switch($m[3]){
    case 'hourmeterQuickEdit':
        // Get ER list
        $ER_CONSTRAINTS .= " AND operation_status NOT LIKE 'RETIRED' ";
        $erRawList = tldEquipment::byConstraints($ER_CONSTRAINTS);

        $erList = array();
        foreach($erRawList as $val){
            $erList[$val['id']]=$val;
        }
        // Display form
        if(empty($_REQUEST['frmTrigger'])){
            $body.=include('scm.form.er_hourmeter.inc.php');
            break;
        }

        foreach($_REQUEST['hours'] as $erid=>$hours){
            // Check if ER part of the list
            if(!in_array($erid,array_keys($erList))) continue;
            if(empty($hours)) continue;
            // Get ER & check validity
            $er = new tldEquipment($erid);
            if($er->isEmpty()) continue;
            // Update transaction
            $e = $er->setHourMeter(TldDatabase::escape($hours),"SCM");
            if(is_string($e)){
                $DEFAULT_ERROR[]="ERROR: ER#$erid hourmeter not updated using '$hours'. Reason: $e";
                continue;
            }
            $body.="<br>ER#$erid hourmeter updated to $hours";
        }
        $body.="<br><br>Hourmeter review completed";
    break;
    case 'listing':
        switch($m[4]){
        case 'byOperationStatusCustomerType':
            $status = TldDatabase::escape($x);
            $erType = TldDatabase::escape($y);
            $rows = tldEquipment::byOperationStatusCustomerTypeByConstraints($status,$erType,$ER_CONSTRAINTS);
            $_title = "ER by operation status $status, ER type $erType";
        break;
        }
        // Results
        switch($out){
        default:
            $body.=_getERListing($rows,$_title);
        break;
        }
    break;
    default:
        $form = new tldMatrix(
            tldEquipment::countByOperationStatusCustomerTypeByConstraints($ER_CONSTRAINTS),
            "operation_status","cust_equipment_type","num",
            "$php_self?m[0]=scm&m[1]=view&m[2]=er&m[3]=listing&m[4]=byOperationStatusCustomerType&erp=$erp&id=$id",
            "ER fleet by Operation Status by Customer equipment type",
            array(
    			"xItems"=>array('FMC','PMC','NMC','RETIRED'),
    			"style"=>array(
    				"xItems"=>array(
    					'FMC'=>"background:green;color:white;font-weight:bold;",
    					'PMC'=>"background:orange;color:white;font-weight:bold;",
    					'NMC'=>"background:red;color:white;font-weight:bold;",
    					'RETIRED'=>"background:grey;color:black;font-weight:bold;",
    				)
    			)
    		)
        );
        $body.=$form->fetch();
    break;
    }
break;
case 'service':
    $DEFAULT_TITLE .= "\Service";
    $DEFAULT_MENU.=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=scm&m[1]=view&m[2]=service&erp=$erp&id=$id">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=scm&m[1]=view&m[2]=service&m[3]=listing&m[4]=search&erp=$erp&id=$id">Search</a>
EOF;

    // Construct global CSR constraints
    $ssoid = tldLocation::getIDByERP($erp);
    // -- Get all baan SO from actual contract
    $soRawList = tldServiceOrder::byContractID($erp,$scm->getID());
    $soList = array_column($soRawList, 't_orno', 't_orno');
    $IN = implode("','",$soList);
    // -- Constraint query
    $CSR_CONSTRAINTS = <<<EOF
csr.sso_id=$ssoid
AND csr.module LIKE 'SRVO'
AND csr.module_id IN('$IN')
EOF;
    // Remove aliases from constant CSR constraints
    $csrConstraintsWithoutAliases = str_replace('csr.', '', $CSR_CONSTRAINTS);

    switch($m[3]){
    case 'listing':
        switch($m[4]){
        case 'search':
            $DEFAULT_TITLE .= "\Search";
            // Listing
            $workTypeRawList = tldServiceOrder::getOrderSeriesList($erp);
            $workTypeList = array_column($workTypeRawList, 't_dsca', 't_dsca');
            $apcList = tldAirport::getList();
            $statusList = tldCSR::getStatusList();
            // Form
            $form = new HTML_QuickForm('frm','post');
            $form->addElement(  'hidden', 'm[0]', 'scm');
            $form->addElement(  'hidden', 'm[1]', 'view');
            $form->addElement(  'hidden', 'm[2]', 'service');
            $form->addElement(  'hidden', 'm[3]', 'listing');
            $form->addElement(  'hidden', 'm[4]', 'search');
            $form->addElement(  'hidden', 'erp', $erp);
            $form->addElement(  'hidden', 'id', $id);
            $form->addElement(  'header', 'title', 'Search CSR');
            $form->addElement(  'select', 'status', 'Status', array(""=>"")+$statusList);
            $form->addElement(  'text', 'sn', 'SN#');
            $form->addElement(  'text', 'cust_asset_num', 'Customer Asset#');
            $form->addElement(  'select', 'work_type', 'Work type', array(""=>"")+$workTypeList);
            $form->addElement(  'select', 'apc', 'ER Airport Code', array(""=>"")+$apcList);
            $form->addElement(  'text', 'module_id', 'SRVO#');
            $form->addElement(  'text', 'dt_from', 'Date From<br>YYYY-mm-dd', array("class"=>"datepicker"));
            $form->addElement(  'text', 'dt_to', 'Date To<br>YYYY-mm-dd', array("class"=>"datepicker"));
            $form->addElement(  'submit', 'btnSubmit', 'Submit');

            if(!$form->validate()){
                $body = $form->toHTML();
                break 2;
            }

            $vars = tldUtils::cleanupFormInput($form->exportValues());
            // Construct constraints
            $searchConstraints = array();
            foreach($vars as $field=>$val){
                if(empty($val)) continue;
                switch($field){
                case 'status':
                case 'sn':
                case 'cust_asset_num':
                case 'work_type':
                case 'apc':
                case 'module_id':
                    $searchConstraints[]="csr.$field LIKE '$val'";
                break;
                case 'dt_from':
                    $searchConstraints[]="DATEDIFF(dt,'$val')>=0";
                break;
                case 'dt_to':
                    $searchConstraints[]="DATEDIFF(dt,'$val')<=0";
                break;
                }
            }
            if(count($searchConstraints)<1){
                $DEFAULT_ERROR[]="ERROR: Not enough constraints set";
                $body = $form->toHTML();
                break 2;
            }
            $searchConstraints = implode(" AND ",$searchConstraints);
            $rows = tldCSR::byConstraints("$CSR_CONSTRAINTS AND $searchConstraints");
            $_title = "Search results";
        break;
        case 'byStatusWorkType':
            $status = TldDatabase::escape($x);
            $workType = TldDatabase::escape($y);
            $rows = tldCSR::byStatusWorkTypeByConstraints($status, $workType, $CSR_CONSTRAINTS);
            $_title = "CSR by status $status, work type $workType";
        break;
        }
        // Results
        switch($out){
        default:
            $body.=_getCSRListing($rows,$_title);
        break;
        }
    break;
    default:
        // Count matrix
        $form = new tldMatrix(
            tldCSR::countByStatusWorkTypeByConstraints($csrConstraintsWithoutAliases),
            "status","work_type","num",
            "$php_self?m[0]=scm&m[1]=view&m[2]=service&m[3]=listing&m[4]=byStatusWorkType&erp=$erp&id=$id",
            "CSR by status by work type",
            array(
                "xItems"=>tldCSR::getStatusList()
            )
        );
        $body.=$form->fetch();
        // Latest
        $body.=_getCSRListing(
            tldCSR::byConstraints($CSR_CONSTRAINTS, array('limit'=>5,'orderBy'=>'id DESC')),
            "Latest CSR"
        );
    break;
    }
break;
case 'members':
    $DEFAULT_TITLE .= "\Members";
    $DEFAULT_MENU.=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=scm&m[1]=view&m[2]=members&erp=$erp&id=$id">Home</a>
EOF;

    $typeMemberList = tldSCM::getMemberTypeList();
    foreach($typeMemberList as $memberType=>$memberDesc){
        $DEFAULT_MENU.=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=scm&m[1]=view&m[2]=members&m[3]=add&type=$memberType&erp=$erp&id=$id">Add $memberDesc member</a>
EOF;
    }


    switch($m[3]){
    case 'add':
        // Check access
        if(!$user->isInGroupLevel("role_CSM",$erp) && !$user->isInGroupLevel("role_CSA",$erp) && !$user->isInGroup(array("superuser"))){
            $DEFAULT_ERROR[]="ERROR: You do not have permissions, only CSM & SA are allowed";
            break;
        }
        // listing
        $userList = array();
        switch($type){
        case 'tld_service_rep':
        case 'toc_not_cc':
            $userList = tldDirectory::getUserList("smartyOptions");
        break;
        case 'er_status_not':
        case 'er_fleet_not':
        case 'ext_toc_not_cc':
            $extUserList = extranetUser::byERPCuno($scm->getERP(),$scm->getCuno());
            foreach($extUserList as $extUser){
                $userList[$extUser['id']]="{$extUser['fullname']} ({$extUser['email']})";
            }
        break;
        }
        // Form
        $form = new HTML_QuickForm('frm','post');
        $form->addElement(  'hidden', 'm[0]', 'scm');
        $form->addElement(  'hidden', 'm[1]', 'view');
        $form->addElement(  'hidden', 'm[2]', 'members');
        $form->addElement(  'hidden', 'm[3]', 'add');
        $form->addElement(  'hidden', 'type', $type);
        $form->addElement(  'hidden', 'erp', $erp);
        $form->addElement(  'hidden', 'id', $id);
        $form->addElement(  'header', 'title', 'Add Member for '.$typeMemberList[$type]);
        $form->addElement(  'select', 'user_id', 'User', array(''=>'')+$userList);
        $form->addElement(  'submit', 'btnSubmit', 'Submit');
        $form->addRule('user_id','Required','required');

        if(!$form->validate()){
            $body = $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        // Add member
        $e = $scm->addMember(array(
            'user_id'=>$vars['user_id'],
        	'type'=>$vars['type']
        ));
        if(is_string($e)){
            $DEFAULT_ERROR[]="INTERNAL ERROR: Member not added. Reason: $e";
            break;
        }
        $body.= "Member added successfully";
    break;
    case 'delete':
        if(empty($uid) || !is_numeric($uid)){
            $DEFAULT_ERROR[]="ERROR: Parameters sent empty or invalid";
            break;
        }
        $memberHeader = $scm->getMember($uid);
        if(empty($memberHeader)){
            $DEFAULT_ERROR[]="ERROR: Member#$uid not found";
            break;
        }
        if($memberHeader['erp']<>$scm->getERP() || $memberHeader['t_ccon']<>$scm->getID()){
            $DEFAULT_ERROR[]="ERROR: Member#$uid ({$memberHeader['user_fullname']}) not linked to this contract";
            break;
        }
        $e = $scm->deleteMember($uid);
        if(is_string($e)){
            $DEFAULT_ERROR[]="INTERNAL ERROR: Member not deleted. Reason: $e";
            break;
        }
        $body.= "Member deleted successfully";
    break;
    }

    $cells = array();
    $cells[] = _getMemberList(
        $scm->getERStatusNotificationMembers(),
        $typeMemberList['er_status_not'],
        array(
            "links"=>array('user_id'=>"/en/private/sales_service/sales.php?m[0]=extranet&m[1]=view&id=")
        )
    );
    $cells[] = _getMemberList(
        $scm->getERFleetDailyNotificationMembers(),
        $typeMemberList['er_fleet_not'],
        array(
            "links"=>array('user_id'=>"/en/private/sales_service/sales.php?m[0]=extranet&m[1]=view&id=")
        )
    );
    $cells[] = _getMemberList(
        $scm->getTLDServiceRepMembers(),
        $typeMemberList['tld_service_rep'],
        array(
            "links"=>array('user_id'=>'/en/private/directory/index.php?m[0]=people&m[1]=view&id=')
        )
    );
    $cells[] = _getMemberList(
        $scm->getTOCNotMembers(),
        $typeMemberList['toc_not_cc'],
        array(
            "links"=>array('user_id'=>'/en/private/directory/index.php?m[0]=people&m[1]=view&id=')
        )
    );
    $cells[] = _getMemberList(
        $scm->getExternalTOCNotMembers(),
        $typeMemberList['ext_toc_not_cc'],
        array(
            "links"=>array('user_id'=>'/en/private/directory/index.php?m[0]=people&m[1]=view&id=')
        )
    );
    // Display both members list
    $report = new tldHTMLTable(
    	$cells,
		array(
			"cols"=>2,
			"attribs"=>array(
				"table"=>" width='100%'",
				"tr"=>" bgcolor='#FFFFFF'"
			)
		)
	);
	$body.= $report->fetch();
break;
case 'reports':
    $DEFAULT_TITLE .= "\Reports";

    switch($m[3]){
    case 'inventory':
        // Listing
        $warehouseList = array_column(tldCWAR::byERP($erp), 't_cwar', 't_cwar');
        // Form
        $form = new HTML_QuickForm('frm','post');
        $form->addElement(  'hidden', 'm[0]', 'scm');
        $form->addElement(  'hidden', 'm[1]', 'view');
        $form->addElement(  'hidden', 'm[2]', 'reports');
        $form->addElement(  'hidden', 'm[3]', 'inventory');
        $form->addElement(  'hidden', 'erp', $erp);
        $form->addElement(  'hidden', 'id', $id);
        $form->addElement(  'header', 'title', 'Inventory by warehouse');
        $form->addElement(  'select', 'cwar', 'Warehouse', array('ALL'=>'ALL')+$warehouseList);
        $form->addElement(  'submit', 'btnSubmit', 'Submit');
        $body = $form->toHTML();

        if(!$form->validate()) break 2;

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        // constraints
        $a = '';
        if($vars['cwar']<>'ALL'){
            $a = array('stock.t_cwar'=>$vars['cwar']);
        }
        // get data
        $baan = new tldBaanERP($erp);
        $rows = $baan->getInventoryRawDataByConstraints($a);
        // prepare fields to show
        $_xItems = array(
            't_item'=>'Part Number',
            't_dsca'=>'Description',
            'stock_cwar'=>'Warehouse',
            't_loca'=>'Location',
            't_stks'=>'Qty on hand',
            't_cuni'=>'UM'
        );
        // Get additional info for each items
        foreach($rows as &$row){
            $itm = new tldITM($row['t_item'],$erp);
            // Add customer OEM part info
            $altPnInfo = $itm->getAlternativeItemByConstraints(array(
            	't_citt'=>'CUS',
            	't_cuno'=>$scm->getCuno()
            ));
            $_xItems['t_csel']='OEM Code';
            $_xItems['t_aitc']='OEM Part Number';
            $row['t_aitc']=$altPnInfo[0]['t_aitc'];
        }
        $_title = "Inventory for {$vars['cwar']} warehouse in ERP# $erp the ".date('Y-m-d')." at ".date('H:i:s');
    break;
    case 'fullAdvReport':
        // Listing
        $workTypeRawList = tldServiceOrder::getOrderSeriesList($erp);
        $workTypeList = array_column($workTypeRawList, 't_dsca', 't_dsca');
        $apcList = tldAirport::getList();
        $statusList = tldCSR::getStatusList();
        $fixTypeList = tldServiceOrder::getFixListAsRefDesc($erp);
        // Form
        $form = new HTML_QuickForm('frm1','post',null,null,null,TRUE);
        $form->addElement(  'hidden', 'm[0]', 'scm');
        $form->addElement(  'hidden', 'm[1]', 'view');
        $form->addElement(  'hidden', 'm[2]', 'reports');
        $form->addElement(  'hidden', 'm[3]', 'fullAdvReport');
        $form->addElement(  'hidden', 'erp', $erp);
        $form->addElement(  'hidden', 'id', $id);
        // Common filters
        $form->addElement(  'header', 'title', 'Common Service Filter');
        $form->addElement(  'text', 'csr_id', 'CSR#');
        $form->addElement(  'text', 'module_id', 'SRVO#');
        $form->addElement(  'select', 'status', 'Status', array(""=>"")+$statusList);
        $form->addElement(  'select', 'work_type', 'Work type', array(""=>"")+$workTypeList);
        $form->addElement(  'select', 't_cfix', 'Fix type', array(""=>"")+$fixTypeList);
        $form->addElement(  'text', 'sn', 'SN#');
        $form->addElement(  'text', 'cust_asset_num', 'Customer Asset#');
        $form->addElement(  'select', 'apc', 'ER Airport Code', array(""=>"")+$apcList);
        $form->addElement(  'text', 'dt_from', 'Date From<br>YYYY-mm-dd', array("class"=>"datepicker"));
        $form->addElement(  'text', 'dt_to', 'Date To<br>YYYY-mm-dd', array("class"=>"datepicker"));
        // Type of report
        $form->addElement('header', 'title', 'Select type of report');
        $form->addElement('radio', "report_type", NULL, 'Service only', 'service');
        $form->addElement('radio', "report_type", NULL, 'Service & Labours', 'labour');
        $form->addElement('radio', "report_type", NULL, 'Service & Parts', 'parts');
        $form->addElement(  'submit', 'btnSubmit', 'Submit');
        // Required
        $requiredFields = array('report_type');
        foreach($requiredFields as $field){
            $form->addRule($field,'Required','required');
        }

        if(!$form->validate()){
            $body = $form->toHTML();
            break 2;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());

        // Get SRVO from Filters & actual contract --------------------------->

        $SRVO_CONSTRAINTS = NULL;
        // Actual contract
        $SRVO_CONSTRAINTS = <<<EOF
LTRIM(so.t_ccon) LIKE '$id'
EOF;
        // Looking at filters
        foreach($vars as $field=>$val){
            if(empty($val)) continue;
            switch($field){
            case 't_cfix':
                $SRVO_CONSTRAINTS.= " AND so.t_cfix=$val ";
            break;
            }
        }
        $srvo_rows = tldServiceOrder::byConstraints($erp,$SRVO_CONSTRAINTS);

        // Get CSR --------------------------->

        $CSR_CONSTRAINTS = NULL;
        // Get sso
        $ssoid = tldLocation::getIDByERP($erp);
        // Get service order t_orno
        $soList = array_column($srvo_rows, 't_orno', 't_orno');
        $IN = implode("','",$soList);
        // -- Constraint query
        $CSR_CONSTRAINTS = <<<EOF
csr.sso_id=$ssoid
AND csr.module LIKE 'SRVO'
AND csr.module_id IN('$IN')
EOF;
        // Form Filter
        $searchConstraints = array();
        foreach($vars as $field=>$val){
            if(empty($val)) continue;
            switch($field){
            case 'csr_id':
               $CSR_CONSTRAINTS.=" AND csr.id=$val";
            break;
            case 'status':
            case 'sn':
            case 'cust_asset_num':
            case 'work_type':
            case 'apc':
            case 'module_id':
                $CSR_CONSTRAINTS.=" AND csr.$field LIKE '$val'";
            break;
            case 'dt_from':
                $CSR_CONSTRAINTS.=" AND DATEDIFF(dt,'$val')>=0";
            break;
            case 'dt_to':
                $CSR_CONSTRAINTS.=" AND DATEDIFF(dt,'$val')<=0";
            break;
            }
        }
        $rows = tldCSR::byConstraints($CSR_CONSTRAINTS);

        // Get report per type of report request --------------------------->

        $_title = "Report result";

        switch($vars['report_type']){
        case 'service':
            foreach($rows as &$row){
                $so = new tldServiceOrder($row['module_id'],$erp);
                $row["fix_desc"]=$so->getFixTypeDescription();
            }
            // Columns
            $_xItems = array(
                "module_id"=>"SRVO#",
                "dt"=>"Date",
                "sso"=>"SSO",
                "status"=>"Status",
                "work_type"=>"Work Type",
                "short_desc"=>"Short description",
                "sn"=>"SN",
                "cust_asset_num"=>"Customer Asset#",
                "model"=>"Model",
                "apc"=>"APC",
                "user_customer"=>"USER customer",
            	"fix_desc"=>"Fix type",
                "id"=>"CSR#",
            );
            $_links = array(
                "id"=>array(
                    "url"=>"$php_self?m[0]=csr&m[1]=view&id=",
            		"params"=>array('id'=>'id'),
                    "target"=>'_blank'
                ),
            	"module_id"=>array(
                    "url"=>"$php_self?m[0]=srvo&m[1]=view",
            		"params"=>array('id'=>'module_id','erp'=>'sso_erp'),
                    "target"=>'_blank'
                ),
                "sn"=>array(
                    "url"=>"/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view",
            		"params"=>array('id'=>'parent_id'),
                    "target"=>'_blank'
                ),
            );
        break;
        case 'labour':
            // Get labour info
            $rowsBefore = $rows;
            $rows = array();
            foreach($rowsBefore as $k=>$row){
                $so = new tldServiceOrder($row['module_id'],$erp);
                if($so->isEmpty()) continue;
                $soLabours = $so->getLabours();
                foreach($soLabours as $soLabour){
                    $rows[]=array_merge($soLabour,$row);
                }
            }
            // Report options
            $_xItems = array(
                "id"=>"CSR#",
                "sn"=>"SN",
                "cust_asset_num"=>"Customer Asset#",
                "module_id"=>"SRVO#",
                "t_hrea"=>"Hours",
                "t_emno"=>"Employee"
            );
            $_links = array(
                "id"=>array(
                    "url"=>"$php_self?m[0]=csr&m[1]=view&id=",
            		"params"=>array('id'=>'id'),
                    "target"=>'_blank'
                ),
            	"module_id"=>array(
                    "url"=>"$php_self?m[0]=srvo&m[1]=view",
            		"params"=>array('id'=>'module_id','erp'=>'sso_erp'),
                    "target"=>'_blank'
                ),
                "sn"=>array(
                    "url"=>"/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view",
            		"params"=>array('id'=>'parent_id'),
                    "target"=>'_blank'
                ),
            );
        break;
        case 'parts':
            // Get parts info
            $rowsBefore = $rows;
            $rows = array();
            foreach($rowsBefore as $k=>$row){
                $so = new tldServiceOrder($row['module_id'],$erp);
                if($so->isEmpty()) continue;
                $soParts = $so->getParts();
                foreach($soParts as $sopart){
                    $rows[]=array_merge($sopart,$row);
                }
            }
            // Report options
            $_xItems = array(
                "id"=>"CSR#",
                "sn"=>"SN",
                "cust_asset_num"=>"Customer Asset#",
                "module_id"=>"SRVO#",
                "t_item"=>"Part Number",
                "t_quan"=>"Quantity",
                "t_cuni"=>"UM"
            );
            $_links = array(
                "id"=>array(
                    "url"=>"$php_self?m[0]=csr&m[1]=view&id=",
            		"params"=>array('id'=>'id'),
                    "target"=>'_blank'
                ),
            	"module_id"=>array(
                    "url"=>"$php_self?m[0]=srvo&m[1]=view",
            		"params"=>array('id'=>'module_id','erp'=>'sso_erp'),
                    "target"=>'_blank'
                ),
                "sn"=>array(
                    "url"=>"/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view",
            		"params"=>array('id'=>'parent_id'),
                    "target"=>'_blank'
                ),
            );
        break;
        }
    break;
    case 'operationStatusByPeriod':
        // Form
        $form = new HTML_QuickForm('frm1','post',null,null,null,TRUE);
        $form->addElement(  'hidden', 'm[0]', 'scm');
        $form->addElement(  'hidden', 'm[1]', 'view');
        $form->addElement(  'hidden', 'm[2]', 'reports');
        $form->addElement(  'hidden', 'm[3]', 'operationStatusByPeriod');
        $form->addElement(  'hidden', 'erp', $erp);
        $form->addElement(  'hidden', 'id', $id);
        $form->addElement(  'header', 'title', 'Operation Status History filters');
        $form->addElement(  'text', 'dt_from', 'Date From<br>YYYY-mm-dd', array("class"=>"datepicker"));
        $form->addElement(  'text', 'dt_to', 'Date To<br>YYYY-mm-dd', array("class"=>"datepicker"));
        $form->addElement(  'submit', 'btnSubmit', 'Submit');
        $form->addRule('dt_from','Required','required');
        $form->addRule('dt_to','Required','required');
        $form->setDefaults(array(
        	'dt_from'=>date('Y-m-').'01',
        	'dt_to'=>date('Y-m-t'),
        ));

        if(!$form->validate()){
            $body = $form->toHTML();
            break 2;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $a = <<<EOF
maintenance_contract_ref LIKE '$id'
AND maintenance_contract_erp=$erp
AND DATEDIFF(dt,'{$vars['dt_from']}')>=0
AND DATEDIFF(dt,'{$vars['dt_to']}')<=0
EOF;
        $rows = tldEquipment::getOperationStatusTransactionsByConstraints($a);
        // Report options
        $_title = "Operation status history of SCM#$id in $erp from {$vars['dt_from']} to {$vars['dt_to']}";
        $_xItems = array(
            "id"=>"ER#",
            "sn"=>"SN",
            "cust_asset_num"=>"Customer Asset#",
            "status"=>"Status",
            "dt"=>"Date",
            "poster_fullnmae"=>"Poster",
            "log"=>"Description issue",
            "limitations_log"=>"PMC Limitations",
            "repairs_log"=>"Repairs",
        	"fmc_log"=>"FMC Comment",
        );
        $_links = array(
            "id"=>array(
                "url"=>"/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view",
        		"params"=>array('id'=>'id'),
                "target"=>'_blank'
            ),
        );
    break;
    case 'capabilityStatsByPeriod':
        // Form
        $form = new HTML_QuickForm('frm1','post',null,null,null,TRUE);
        $form->addElement(  'hidden', 'm[0]', 'scm');
        $form->addElement(  'hidden', 'm[1]', 'view');
        $form->addElement(  'hidden', 'm[2]', 'reports');
        $form->addElement(  'hidden', 'm[3]', 'capabilityStatsByPeriod');
        $form->addElement(  'hidden', 'erp', $erp);
        $form->addElement(  'hidden', 'id', $id);
        $form->addElement(  'header', 'title', 'Capability statistics by period');
        $form->addElement(  'text', 'dt_from', 'Date From<br>YYYY-mm-dd', array("class"=>"datepicker"));
        $form->addElement(  'text', 'dt_to', 'Date To<br>YYYY-mm-dd', array("class"=>"datepicker"));
        $form->addElement(  'submit', 'btnSubmit', 'Submit');
        $form->addRule('dt_from','Required','required');
        $form->addRule('dt_to','Required','required');
        $form->setDefaults(array(
        	'dt_from'=>date('Y-m-').'01',
        	'dt_to'=>date('Y-m-t'),
        ));

        if(!$form->validate()){
            $body = $form->toHTML();
            break 2;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $a = <<<EOF
maintenance_contract_ref LIKE '$id'
AND maintenance_contract_erp=$erp
EOF;
        $rows = tldEquipment::getCapabilityStatsByPeriodByConstraints($vars['dt_from'],$vars['dt_to'],$a);
        // Report options
        $_title = "ER fleet capability statistics of SCM#$id in $erp from {$vars['dt_from']} to {$vars['dt_to']}";
        $_xItems = array(
            "id"=>"ER#",
            "sn"=>"SN",
            "cust_asset_num"=>"Customer Asset#",
            "start_period"=>"Start Period",
        	"end_period"=>"End Period",
            "total_period"=>"Total Period (hours)",
            "total_fmc"=>"Total FMC (hours)",
            "total_pmc"=>"Total PMC (hours)",
        	"total_nmc"=>"Total NMC (hours)",
        );
        $_links = array(
            "id"=>array(
                "url"=>"/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view",
        		"params"=>array('id'=>'id'),
                "target"=>'_blank'
            ),
        );
    break;
    case 'hourmeterByPeriod':
        $form = new HTML_QuickForm('frm','post');
        $form->addElement(  'hidden', 'm[0]', 'scm');
        $form->addElement(  'hidden', 'm[1]', 'view');
        $form->addElement(  'hidden', 'm[2]', 'reports');
        $form->addElement(  'hidden', 'm[3]', 'hourmeterByPeriod');
        $form->addElement(  'hidden', 'erp', $erp);
        $form->addElement(  'hidden', 'id', $id);
        $form->addElement(  'header', 'title', 'Hourmeter Report By Period');
		$form->addElement(	'date', 	'x',	'From',
			array("format"=>"Ymd","minYear"=>'2014',"maxYear"=>date('Y')));
		$form->addElement(	'date', 	'y',	'To',
			array("format"=>"Ymd","minYear"=>'2014',"maxYear"=>date('Y')));
		$form->addElement(	'submit', 'btnSubmit', 'Submit');
        // rules & default values
		$form->addRule('x', 'This is required', 'required');
		$form->addRule('y', 'This is required', 'required');
		$form->setDefaults(array(
			"x"=>date("Y-m-1"),
			"y"=>date("Y-m-d")
	    ));

        if(!$form->validate()){
            $body = $form->toHTML();
            break 2;
        }

		$vars = tldUtils::cleanupFormInput($form->exportValues());
		$vars['from']=implode('-',$vars['x']);
		$vars['to']=implode('-',$vars['y']);
		// Get data
		$a = <<<EOF
maintenance_contract_ref LIKE '{$scm->getID()}'
AND maintenance_contract_erp LIKE '{$scm->getERP()}'
AND hourTrans_dt BETWEEN '{$vars['from']}' AND '{$vars['to']}'
EOF;
		$rows = tldEquipment::getHourMeterTransactionsByConstraints($a);
		// Report options
		$out = 'multi';
        $_title = "Hourmeter logged for ER contracted in SCM#$id company $erp from {$vars['from']} to {$vars['to']}";
        $_levelItems = array('sn');
        $_xItems = array(
    		"id"=>"ER#",
            "sn"=>"SN#",
    		"cust_asset_num"=>"Customer Asset#",
            "cust_equipment_type"=>"Customer ER Type",
        	"model"			=>"Model",
        	"man_location"	=>"Man Location",
        	"sales_org"     =>"SSO",
        	"airport_code"	=>"Airport Code",
            'hourTrans_hourmeter'=>'Hourmeter Reading',
			'hourTrans_dt'=>'Recorded When',
			'hourTrans_module'=>'From Module'
        );
        $_links = array(
            "id"=>array(
                "url"=>"/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view",
        		"params"=>array('id'=>'id'),
                "target"=>'_blank'
            ),
        );
    break;
    case 'display':
        // to bypass default
    break;
    default:
        $body = <<<EOF
<h3>SCM#$id reports</h3>
<ul>
  <li><a href="$php_self?m[0]=scm&m[1]=view&m[2]=reports&m[3]=fullAdvReport&erp=$erp&id=$id">Full Advanced Report system</a></li>
  <li><a href="$php_self?m[0]=scm&m[1]=view&m[2]=reports&m[3]=inventory&erp=$erp&id=$id">Inventory by Warehouse</a></li>
  <li><a href="$php_self?m[0]=scm&m[1]=view&m[2]=reports&m[3]=capabilityStatsByPeriod&erp=$erp&id=$id">ER fleet capability statistics by Period</a></li>
  <li><a href="$php_self?m[0]=scm&m[1]=view&m[2]=reports&m[3]=operationStatusByPeriod&erp=$erp&id=$id">ER fleet Operation status history</a></li>
  <li><a href="$php_self?m[0]=scm&m[1]=view&m[2]=reports&m[3]=hourmeterByPeriod&erp=$erp&id=$id">Hourmeter report by period</a></li>
</ul>
EOF;
        break 2;
    break;
    }

    // Reports display -------------------->

    switch($out){
    case 'xls':
        $report = new tldXLS(
            $sess['reports']['rows'],
            array(
                "xItems"=>$sess['reports']['xItems'],
                "showTitles"=>true
            )
        );
        $report->out();
        exit;
    break;
    case 'multi':
        $DEFAULT_MENU.=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=scm&m[1]=view&m[2]=reports&m[3]=display&erp=$erp&id=$id&out=xls">XLS</a>
EOF;
        // Record data & options in session
        $sess['reports']['rows'] = $rows;
        $sess['reports']['xItems'] = $_xItems;
        $sess['reports']['title'] = $_title;
        $sess['reports']['links'] = $_links;

        $form = new tldReportMultiLevel(
    		$rows,
    		$_levelItems,
    		$_xItems,
    		array(
    			'title'=>$_title,
    		    "links"=>$_links
    		)
    	);
    	$body .= $form->fetch();
    break;
    default:
        $DEFAULT_MENU.=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=scm&m[1]=view&m[2]=reports&m[3]=display&erp=$erp&id=$id&out=xls">XLS</a>
EOF;
        // Record data & options in session
        $sess['reports']['rows'] = $rows;
        $sess['reports']['xItems'] = $_xItems;
        $sess['reports']['title'] = $_title;
        $sess['reports']['links'] = $_links;
        // Display
        $report = new tldReportColumnar(
            $rows,
            array(
                "xItems"=>$_xItems,
                "title"=>$_title,
        		"links"=>$_links
            )
        );
        $body = $report->fetch();
    break;
    }
break;
default:
    $report = new tldAssocTable(
    	$scm->getHeader(),
        array(
            "t_ccon"=>"SCM#",
            "erp"=>"ERP#",
            "t_desc"=>"Description",
            "t_cuno"=>"Cuno",
            "t_refa"=>"Ref A",
            "status"=>"Status",
        	"t_ctpc"=>"Contract type",
            "t_sdat"=>"Effective date",
            "t_edat"=>"Expiry date",
            "t_clan"=>"Language",
        ),
		array("title"=>"Service Contract details")
	);
	$body = $report->fetch();
break;
}



function _getCSRListing($rows,$title){
    global $php_self;
    $report = new tldReportColumnar(
        $rows,
        array(
            "xItems"=>array(
                "module_id"=>"SRVO#",
                "dt"=>"Date",
                "sso"=>"SSO",
                "status"=>"Status",
                "work_type"=>"Work Type",
                "short_desc"=>"Short description",
                "sn"=>"SN",
                "cust_asset_num"=>"Customer Asset#",
                "model"=>"Model",
                "apc"=>"APC",
                "user_customer"=>"USER customer",
                "id"=>"CSR#",
            ),
            "title"=>$title,
            "links"=>array(
                "id"=>array(
                    "url"=>"$php_self?m[0]=csr&m[1]=view&id=",
            		"params"=>array('id'=>'id'),
                    "target"=>'_blank'
                ),
            	"module_id"=>array(
                    "url"=>"$php_self?m[0]=srvo&m[1]=view",
            		"params"=>array('id'=>'module_id','erp'=>'sso_erp'),
                    "target"=>'_blank'
                ),
                "sn"=>array(
                    "url"=>"/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view",
            		"params"=>array('id'=>'parent_id'),
                    "target"=>'_blank'
                ),
            )
        )
    );
    return $report->fetch();
}

function _getERListing($rows,$title){
    global $php_self;
    $report = new tldReportColumnar(
        $rows,
        array(
            "xItems"=>array(
                "id"=>"ER#",
                "sn"=>"SN#",
        		"cust_asset_num"=>"Customer Asset#",
                "user_customer_display"=>"Customer (END USER)",
                "buyer_customer_display"=>"Customer (BUYER)",
                "cust_equipment_type"=>"Customer ER Type",
            	"model"			=>"Model",
            	"man_location"	=>"Man Location",
            	"sales_org"     =>"SSO",
            	"airport_code"	=>"Airport Code",
            	"operation_status"	=>"Operation status",
                "maintenance_contract_ref"=>"MSC#",
            ),
            "title"=>$title,
            "links"=>array(
                "id"=>array(
                    "url"=>"/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view",
            		"params"=>array('id'=>'id'),
                    "target"=>'_blank'
                ),
            ),
            "functions"=>array(
                "Create TOC"=>array(
                    "url"=>"/en/private/sales_service/service.php?m[0]=toc&m[1]=form&m[2]=new2",
                    "param"=>array('erid'=>'id'),
                    "target"=>'_blank'
                )
            )
        )
    );
    return $report->fetch();
}

function _getMemberList($rows,$title,$opts=""){
    global $php_self,$erp,$id;
    $report = new tldReportColumnar(
        $rows,
        array(
            "xItems"=>array(
                'user_id'=>'Contact#',
                'user_fullname'=>'Contact name',
                'user_email'=>'Email'
            ),
            "title"=>$title,
            "links"=>$opts['links'],
            "functions"=>array(
                "Delete"=>array(
                    "url"=>"$php_self?m[0]=scm&m[1]=view&m[2]=members&m[3]=delete&erp=$erp&id=$id",
                    "param"=>array('uid'=>'id'),
                    "confirmPopup"=>"Are you sure to delete?",
                    "img"=>"/shared/icons/miscellaneous/delete.png"
                )
            )
        )
    );
    return $report->fetch();
}
