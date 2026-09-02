<?php
include_once('common.inc.php');
include_once('forms_and_reports.inc.php');
include_once('sales_service.inc.php');

ini_set('error_log', "$CRON_CACHE_DIR/weekly.bat.log");

/**
 * 	LATE TOC NOTIFICATION SCRIPT
 *
 * 	Send notification every sunday
 *  Notify all actors even there is no data
 *
 * 	- ASM + CSM in cc WHEN late > 7 days -> 1 email per ASM
 * 	- EVP and Regional CEO + CSM in cc WHEN late > 14 days -> 1 email per SSO
 * 	- GCOO + GCSM + relevant CSM + EVP + RCEO in cc WHEN late > 21 days -> 1 email
 *
 **/

$STATS = [];
$WHERE = '';
$dt_begin = date('Y-m-d h:i:s A');
$dtFile = date('Ymd');
error_log("RUNNING $PHP_SELF");

// 0 - Common DATA

$SSO = tldLocation::getSalesOrgList('smartyOptionsIDLocation');
$Factory= tldLocation::getFactoryList('smartyOptions');

// 1 - SEND TOC > 7 days per ASM
$STATS[] = $log = '<br><b>SEND TOC > 7 days per ASM</b>';
error_log($log);

$gasm = new tldGroup('role_ASM');
$data = $gasm->getUserlist();
$asms = tldUtils::optionsByKeyValue($data, 'id', 'email');

foreach($asms as $id=>$email){
	$asm = new tldUser($id);
	// Get list of TOC for this ASM
	$rows = _tocByConstraints("days_late > 7 AND crt.sales_rep_id=$id AND crt.erp_location_id=toc.ssoid");
    
    // Prepare email
    // Get list of impacted SSO to copy CSMs in the email
    $sso = [];
    foreach($rows as $row){
        $sso[]=$row['ssoid'];
    }
    $sso = array_unique($sso);
    
    // Get CSM for all impacted SSO
    $CC = [];
    foreach($sso as $ssoid){
        $bu = new tldLocation($ssoid);
        $csm = new tldGroup('ROLE_CSM_TOC_LATE_NOT',$bu->getERP());
        $csm_list = $csm->getUserlist();
        foreach($csm_list as $csm){
            $CC[]=$csm['email'];
        }
    }
    
    $CC = implode(',',array_unique($CC));
    $TO = $email;
    $SUBJECT = 'Late TOC weekly notification for ' . $asm->getFullname();
    $BODY = 'Please follow up these late TOC opened since more than 7 days<br><br>';
    $BODY.= _getListing($rows, 'Late TOC opened > 7 days');
    
    // Send email
    try {
        _sendEmail($TO, $SUBJECT, $BODY, $CC);
        $STATS[] = "<b>Email sent to</b> $TO <br> <b>cc:</b> $CC";
    } catch (Exception $e) {
        $STATS[] = '<b>Email error:</b> ' . $e->getMessage() . " > $SUBJECT";
    }
}

// 2 - SEND TOC > 14 days per SSO
$STATS[] = $log = '<br><b>SEND TOC > 14 days per SSO</b>';
error_log($log);

foreach($SSO as $ssoid=>$location){
	// Initialise var
	$TO = [];
	$CC = [];
	// Get SSO object
	$bu = new tldLocation($ssoid);
	// Get list of TOC for this SSO
	$rows = _tocByConstraints("days_late > 14 AND toc.ssoid=$ssoid");
    
    // Get EVP
    $evp = new tldGroup('role_EVP',$bu->getERP());
    $evp_list = $evp->getUserlist();
    foreach($evp_list as $evp){
        $TO[]=$evp['email'];
    }
    
    // Get Regional CEO
    $ceo = new tldGroup('role_CEO',$bu->getERP());
    $ceo_list = $ceo->getUserlist();
    foreach($ceo_list as $ceo){
        $TO[]=$ceo['email'];
    }
    
    // CC the CSM
    $csm = new tldGroup('ROLE_CSM_TOC_LATE_NOT',$bu->getERP());
    $csm_list = $csm->getUserlist();
    foreach($csm_list as $csm){
        $CC[]=$csm['email'];
    }
    
    // Prepare email
    $TO = implode(',',array_unique($TO));
    $CC = implode(',',array_unique($CC));
    $SUBJECT = "Late TOC weekly notification for $location";
    $BODY = 'Please follow up these late TOC opened since more than 14 days<br><br>';
    $BODY.= _getListing($rows,"Late TOC opened > 14 days for $location");
    
    // Send email
    try {
        _sendEmail($TO, $SUBJECT, $BODY, $CC);
        $STATS[] = "<b>Email sent to</b> $TO <br> <b>cc:</b> $CC";
    } catch (Exception $e) {
        $STATS[] = '<b>Email error:</b> ' . $e->getMessage() . " > $SUBJECT";
    }
}

// 3 - SEND TOC > 21 days to the GROUP level
$STATS[] = $log = '<br><b>SEND TOC > 21 days to the GROUP level</b>';
error_log($log);
$rows = _tocByConstraints('days_late > 21',"AND toc.status != 'SUSPENDED' AND toc.unit_operation_status!='MCF' AND toc.ssoid NOT IN(37, 0, 45, 44)");
$rows = array_filter($rows, static function($value) {
    return (int) $value['days_since_last_log'] >  5;
});

// Prepare email
$SUBJECT = 'TOC logs overdues';
$BODY = 'Dear CSMs,<br>See attached the list of machines either NMC or MCP for which no update has been logged for a week or more.<br>
Please, look into those asap this week and update the log, so we can all understand where we stand and what needs to be done to expedite closure<br><br>';
$BODY.= _getListing($rows, '');
$recipients = _getLateTocRecipients($rows);
$TO = $recipients['TO'];
$CC = $recipients['CC'];

// Create XLS
$xItems = [
    'id'                    => 'TOC#',
    'sso_fullname'          => 'SSO',
    'tec_fullname'          => 'Service Technician',
    'status'                => 'Status',
    'sn'                    => 'SN',
    'model'                 => 'Model',
    'unit_operation_status' => 'Operation status',
    'customer_name'         => 'Customer Name',
    'short_desc'            => 'Short Problem Description',
    'dt'                    => 'Claim date',
    'days_late'             => 'Days late',
    'last_log_date'         => 'Last log date',
    'days_since_last_log'   => 'No. Days since last TOC log',
    'airport_code'          => 'APC',
];
$filePath = _generateSpreadsheet($rows, $xItems, str_replace(' ', '_', $SUBJECT) . '_' . $dtFile . '.xls');

// Send email
try {
    _sendEmail($TO, $SUBJECT, $BODY, $CC, $filePath);
    unlink($filePath);
    $STATS[] = "<b>Email sent to</b> $TO <br> <b>cc:</b> $CC";
} catch (Exception $e) {
    $STATS[] = '<b>Email error:</b> ' . $e->getMessage() . " > $SUBJECT";
}

// 4 - SEND TOC Open/In progress with IF >= 100 for PSM, RME, EM, COO & RCOO
$STATS[] = $log = '<br><b>SEND TOC > Open/In progress with IF >= 100 per factory</b>';
error_log($log);

foreach($Factory as $erp=>$man_location){
	$rows = _tocByConstraints("factory_fullname = '$man_location' AND toc.ifactor > 99");

	if (!$rows) {
	    continue;   
    }

	// --- Get the TO list
	$TO = [];
	// Get COO
	$coo = new tldGroup('role_COO',$erp);
	$coo_list = $coo->getUserlist();
	foreach($coo_list as $coo){
		$TO[]=$coo['email'];
	}
	
	// Get RCOO
	$rcoo = new tldGroup('role_RCOO',$erp);
	$rcoo_list = $rcoo->getUserlist();
	foreach($rcoo_list as $rcoo){
		$TO[]=$rcoo['email'];
	}
	
	// --- Get the CC list
	$CC = [];
	//Laurent Decoux (id=108)
	$e = new tldUser (108);
	$CC[]=$e->getEmail();
	foreach (['role_PSM', 'role_RME', 'role_EM' ,'role_PSE'] as $role) {
        $group = new tldGroup($role, $erp);
        $users = $group->getUserlist();
        foreach($users as $user){
            $CC[]=$user['email'];
        }
    }
    if((int)$erp === 400){
        $CC[]= 'benjamin.althen@tld-america.com';
    }

	// Prepare email
	$TO = implode(',',array_unique($TO));
	$CC = implode(',',array_unique($CC));
	$SUBJECT = "TOC Weekly Notification for $man_location";
	$BODY = "Please follow up these TOC with a IF >= 100 - $man_location Factory<br><br>";
	$BODY.= _getListingIF100($rows,"TOC Open, In Progress for $man_location - IF > 100");

	// Send email
	try {
        _sendEmail($TO, $SUBJECT, $BODY, $CC);
        $STATS[] = "<b>Email sent to</b> $TO <br> <b>cc:</b> $CC";
    } catch (Exception $e) {
        $STATS[] = '<b>Email error:</b> ' . $e->getMessage() . " > $SUBJECT";
    }
}

// 5 - SEND TOC >= IF 100 & Number of days since last external log > 5, to the GROUP level
$STATS[] = $log = '<br><b>SEND TOC >= IF 100 & Number of days since last external log > 5, to the GROUP level</b>';
error_log($log);
$rows = _tocByConstraints('days_since_last_not > 4', "AND toc.ifactor > 99 AND toc.status != 'SUSPENDED'");
$xItems = [
    'id'                    => 'TOC#',
    'sso_fullname'          => 'SSO',
    'tec_fullname'          => 'Service Technician',
    'status'                => 'Status',
    'sn'                    => 'SN',
    'model'                 => 'Model',
    'unit_operation_status' => 'Operation Status',
    'customer_name'         => 'Customer Name',
    'short_desc'            => 'Short Problem Description',
    'dt'                    => 'Claim Date',
    'days_late'             => 'Days late',
    'last_toc_not_date'     => 'Last External Log Entry',
    'days_since_last_not'   => 'No. Days Since Last External Log',
    'airport_code'          => 'APC',
];

// Prepare email
$SUBJECT = 'Late Customer TOC Updates';
$BODY = 'For each TOC below, please update the TOC external log to communicate with customers on status and next steps. As a reminder, for any TOC IF 100 or 1,000, you shall update the customer at least once a week through the external TOC log.<br><br>';
$BODY.= _getListing($rows, 'Late Customer TOC Updates (IF 100 & 1000)', $xItems);
$recipients = _getLateTocRecipients($rows);
$TO = $recipients['TO'];
$CC = $recipients['CC'];
$filePath = _generateSpreadsheet($rows, $xItems, str_replace(' ', '_', $SUBJECT) . '_' . $dtFile . '.xls');
// Send email
try {
    _sendEmail($TO, $SUBJECT, $BODY, $CC,$filePath);
    unlink($filePath);
    $STATS[] = "<b>Email sent to</b> $TO <br> <b>cc:</b> $CC";
} catch (Exception $e) {
    $STATS[] = '<b>Email error:</b> ' . $e->getMessage() . " > $SUBJECT";
}


// 6 - TOCs Outstanding for 60+ Days
$STATS[] = $log = '<br><b>SEND TOC Outstanding for 60+ Days</b>';
error_log($log);
$rows = _tocByConstraints('days_late > 60',  "AND toc.status='IN PROGRESS' AND toc.ssoid not in (44,37)");
$xItems = [
    'id'                    => 'TOC#',
    'sso_fullname'          => 'SSO',
    'tec_fullname'          => 'Service Technician',
    'status'                => 'Status',
    'sn'                    => 'SN',
    'model'                 => 'Model',
    'unit_operation_status' => 'Operation status',
    'ifactor'               => 'IF',
    'customer_name'         => 'Customer Name',
    'short_desc'            => 'Short Problem Description',
    'dt'                    => 'Claim date',
    'airport_code'          => 'APC',
    'days_opened'           => 'Total Days outstanding',
    'days_since_last_log'   => 'No. Days since last TOC log',
];

// Prepare email
$SUBJECT = 'TOCs Outstanding for 60+ Days';
$BODY = 'Dear CSMs,<br>See below the list of TOCs outstanding for over two months. Please prioritize activities to expedite closure and escalate any further issue to management for support.<br><br>';
$BODY.= _getListing($rows, 'TOCs Outstanding for 60+ Days', $xItems);
$recipients = _getLateTocRecipients($rows);
$TO = $recipients['TO'];
$CC = $recipients['CC'];
$filePath = _generateSpreadsheet($rows, $xItems, str_replace(' ', '_', $SUBJECT) . '_' . $dtFile . '.xls');

// Send email
try {
    _sendEmail($TO, $SUBJECT, $BODY, $CC,$filePath);
    unlink($filePath);
    $STATS[] = "<b>Email sent to</b> $TO <br> <b>cc:</b> $CC";
} catch (Exception $e) {
    $STATS[] = '<b>Email error:</b> ' . $e->getMessage() . " > $SUBJECT";
}

// 7 - Morgan Franc Report
$STATS[] = $log = '<br><b>TOCs 100 & 1000 IN PROGRESS (GCSD)</b>';
error_log($log);
$rows = _tocByConstraints('ifactor > 10',  "AND toc.status='IN PROGRESS'");

foreach ($rows as &$row){
    $row['factory_support_flag'] = (int) $row['factory_support_flag'] === 1 ? 'YES' : '';
}
$xItems = [
    'id'                          => 'TOC#',
    'sso_fullname'                => 'SSO',
    'ifactor'                     => 'IF',
    'model'                       => 'Model',
    'factory_fullname'            => 'Factory',
    'unit_operation_status'       => 'Operation status',
    'airport_code'                => 'APC',
    'customer_name'               => 'Customer Name',
    'con_fullname'                => 'Customer Contact',
    'factory_support_flag'        => 'Factory Support',
    'factory_support_given_at'    => 'Factory Support Given At',
    'factory_support_required_at' => 'Factory Support Required At',
    'days_opened'                 => 'Days opened',
];

// Prepare email
$SUBJECT = 'TOCs 100 & 1000 IN PROGRESS';
$BODY = 'TOCs 100 & 1000 IN PROGRESS<br><br>';
$BODY.= _getListing($rows, 'TOCs 100 & 1000 IN PROGRESS', $xItems);
$TO = [];
$CC = [];
$gcsdGroup = new tldGroup('role_CSD',900);
$gcsdList = $gcsdGroup->getUserlist();

foreach($gcsdList as $gcsd) {
    $TO[]=$gcsd['email'];
}

$asmGroup = new tldGroup('role_ASM');
$asmList = $asmGroup->getUserlist();

foreach($asmList as $asm) {
    $TO[]=$asm['email'];
}

$TO = implode(',',array_unique($TO));
$CC = implode(',',array_unique($CC));

$filePath = _generateSpreadsheet($rows, $xItems, str_replace(' ', '_', $SUBJECT) . '_' . $dtFile . '.xls');
// Send email
try {
    _sendEmail($TO, $SUBJECT, $BODY, '',$filePath);
    unlink($filePath);
    $STATS[] = "<b>Email sent to</b> $TO <br>";
} catch (Exception $e) {
    $STATS[] = '<b>Email error:</b> ' . $e->getMessage() . $SUBJECT;
}

// SCRIPT STATS
$dt_end = date('Y-m-d h:i:s A');
error_log("END OF RUNNING $PHP_SELF");
$STATS = implode('<br>',$STATS);
$BODY = <<<EOF
BEGIN AT $dt_begin<br>
FINISHED AT $dt_end<br><br>
$STATS
EOF;

$e = tldUtils::emailAttachment(
    'devteam@tld-america.com',
    'noreply@tld-gse.com',
    "[TLD SCRIPT] STATS of $PHP_SELF",
    $BODY
);

// METHODS
function _tocByConstraints($a, $WHERE = ''){
	if(empty($a)) return;
	$query = <<<EOF
SELECT
	CONCAT(cons.lastname, ', ', cons.firstname) AS con_fullname,
	cus.customer_name,
	cons.phone AS con_phone,
	ers.sn,
	ers.model,
	ers.airport_code,
	IF(ers.man_location IS NULL,
		'NO FACTORY',
		ers.man_location
	) AS factory_fullname,
	IF(locations.location IS NULL,
		'NO SSO',
		locations.location
	) AS sso_fullname,
	toc.*,
	(SELECT CONCAT(lastname,', ',firstname) FROM people
		WHERE people.id=tecid
	) AS tec_fullname,
	(SELECT CONCAT(lastname,', ',firstname) FROM people
	WHERE people.id=assid
	) AS ass_fullname,
	(SELECT CONCAT(lastname,', ',firstname) FROM people
		WHERE people.id=postid
	) AS post_fullname,
	(SELECT fct_tld_mod_toc_getWFByID(toc.id,1.177664)) AS wf,
	(
		(SELECT COUNT(*) FROM mod_links
		WHERE mod_links.type='TOC' AND mod_links.item=toc.id)
		+
		(SELECT COUNT(*) FROM mod_links
		WHERE mod_links.module='TOC' AND mod_links.parent_id=toc.id)
	) AS num_links,
	TIMESTAMPDIFF(DAY,toc.dt,toc.dt_closed) AS days_close,
	DATE(toc.dt) AS dt,
	DATE(toc.dt_closed) AS dt_closed,
	DATEDIFF(NOW(),toc.dt) AS days_late,
	crt.sales_rep_id,
	crt.erp_location_id,
	TIMESTAMPDIFF(DAY,max(toc_not.dt),now()) AS days_since_last_not,
	TIMESTAMPDIFF(DAY,toc.dt,now()) AS days_opened,
    max(toc_not.dt) as last_toc_not_date
FROM toc
	LEFT JOIN locations ON toc.ssoid=locations.id
	LEFT JOIN service AS ers ON toc.erid=ers.id
	LEFT JOIN customers AS cus ON toc.cuid=cus.id
	LEFT JOIN customers_crt AS crt ON crt.customer_id=cus.id
	LEFT JOIN extranet_users AS cons ON toc.conid=cons.id
	LEFT JOIN toc_not ON toc.id=toc_not.parent_id
WHERE
	toc.status NOT IN('SOLVED','CLOSED') $WHERE
GROUP BY toc.id
HAVING $a
ORDER BY sso_fullname,cus.customer_name,toc.dt
EOF;
	$rows = tldUtils::getSqlToAssocArray($query);
	// Get last log
	foreach($rows as $k=>$row){
        $rows[$k]['last_log'] = $rows[$k]['last_log_date'] = '';
        $toc = new tldTOC($row['id']);
        if (!$logs = $toc->getFullLog()) {
            continue;
        }
        $date = new DateTime($logs[0]['date']);
        $rows[$k]['last_log_date'] = $date->format('Y-m-d');
        $rows[$k]['days_since_last_log'] = date_diff($date, new DateTime())->format('%a');
        $rows[$k]['last_log'] = <<<EOF
{$logs[0]['poster_fullname']}
{$logs[0]['module']}:
{$logs[0]['comment']}
EOF;
	}
	return $rows;
}

function _sendEmail($TO, $SUBJECT, $BODY, $CC, $file=null){
	error_log("Sending email to $TO cc $CC");
	if (empty($TO) ) {
	    throw new Exception('Recipients empty');
	}
	return tldUtils::emailAttachment(
		$TO,
		'noreply@tld-gse.com',
		$SUBJECT,
		$BODY,
        $file,
		$CC
	);
}

function _getListingIF100($rows,$title){
	$report = new tldReportColumnar(
		$rows,
		[
            'xItems'   => [
                'id'                    => 'TOC#',
                'ifactor'               => 'IFactor',
                'tec_fullname'          => 'Service Technician',
                'ass_fullname'          => 'Assignee',
                'status'                => 'Status',
                'wf'                    => 'WF',
                'sn'                    => 'SN',
                'model'                 => 'Model',
                'unit_operation_status' => 'Operation status',
                'customer_name'         => 'Customer Name',
                'short_desc'            => 'Short Problem Description',
                'dt'                    => 'Claim date',
                'days_late'             => 'Days late',
                'last_log_date'         => 'Last log date',
                'last_log'              => 'Last Log',
                'airport_code'          => 'APC',
            ],
            'title'    =>$title,
            'links'    => [
                'sn' => 'http://www.tld-gse.com/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=search&m[2]=bySN&sn=',
                'id' => 'http://www.tld-gse.com/en/private/sales_service/service.php?m[0]=toc&m[1]=view&id='
            ],
            'sortable' => 'no'
        ]
	);
	return $report->fetch();
}

function _getListing($rows,$title, $xItems = []){
    if (empty($xItems)) {
        $xItems = [
            'id'                    => 'TOC#',
            'sso_fullname'          => 'SSO',
            'tec_fullname'          => 'Service Technician',
            'status'                => 'Status',
            'sn'                    => 'SN',
            'model'                 => 'Model',
            'unit_operation_status' => 'Operation status',
            'customer_name'         => 'Customer Name',
            'short_desc'            => 'Short Problem Description',
            'dt'                    => 'Claim date',
            'days_late'             => 'Days late',
            'last_log_date'         => 'Last log date',
            'days_since_last_log'   => 'No. Days since last TOC log',
            'airport_code'          => 'APC',
        ];
    }

    $report = new tldReportColumnar(
        $rows,
        [
            'xItems'   => $xItems,
            'title'    => $title,
            'links'    => [
                'id' => 'http://www.tld-gse.com/en/private/sales_service/service.php?m[0]=toc&m[1]=view&id='
            ],
            'sortable' => 'no'
        ]
    );
	return $report->fetch();
}

function _generateSpreadsheet($rows, $xItems, $filename){
    foreach ($rows as  &$tocLine) {
        $tocLine['id'] = '=Hyperlink("https://www.tld-gse.com/en/private/sales_service/service.php?m[0]=toc&m[1]=view&id=' . $tocLine['id'] .'","' . $tocLine['id'] . '")';
    }
    unset($tocLine);
    
    $xlsFile = new tldXLS(
        $rows,
        [
            'xItems' => $xItems, 
            'showTitles' => true
        ]
    );

    $filePath = sys_get_temp_dir() . '/' . $filename;
    $xlsFile->save($filePath); 
    return $filePath;
}

// Email 3, 5 & 6
function _getLateTocRecipients($rows){
    // --- Get the TO list
    $TO = [];
    $fullname = [];
    // Get GCOO
    $coo = new tldGroup('role_COO',900);
    $coo_list = $coo->getUserlist();
    foreach($coo_list as $coo){
        $TO[]=$coo['email'];
        $fullname[]=$coo['fullname'];
    }
    // Get GCSM
    $csm = new tldGroup('ROLE_CSM_TOC_LATE_NOT',900);
    $csm_list = $csm->getUserlist();
    foreach($csm_list as $csm){
        $TO[]=$csm['email'];
        $fullname[]=$csm['fullname'];
    }
    // --- Get the CC list
    // Get list of impacted SSO
    $sso = [];
    foreach($rows as $row){
        $sso[]=$row['ssoid'];
    }
    $sso = array_unique($sso);
    // Get all impacted SSO roles in cc
    $CC = [];
    foreach($sso as $ssoid){
        $bu = new tldLocation($ssoid);
        // Get CSM
        $csm = new tldGroup('ROLE_CSM_TOC_LATE_NOT',$bu->getERP());
        $csm_list = $csm->getUserlist();
        foreach($csm_list as $csm){
            $CC[]=$csm['email'];
        }
        // Get EVP
        $evp = new tldGroup('role_EVP',$bu->getERP());
        $evp_list = $evp->getUserlist();
        foreach($evp_list as $evp){
            $CC[]=$evp['email'];
        }
        // Get CEO
        $ceo = new tldGroup('role_CEO',$bu->getERP());
        $ceo_list = $ceo->getUserlist();
        foreach($ceo_list as $ceo){
            $CC[]=$ceo['email'];
        }
    }

    return [
        'TO' => implode(',',array_unique($TO)),
        'CC' => implode(',',array_unique($CC))
    ];
}
