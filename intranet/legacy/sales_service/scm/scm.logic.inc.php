<?php
include_once("sales_service.inc.php");
include_once("product_support.inc.php");
include_once("erp.inc.php");

$DEFAULT_TITLE .= "\SCM";
$DEFAULT_MENU.=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=scm">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=scm&m[1]=form&m[2]=byNumber">By number</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=scm&m[1]=reports">Reports</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=scm&m[1]=files">Files</a>
EOF;

switch($m[1]){
case 'form':
    switch($m[2]){
    case 'byNumber':
        $form = new HTML_QuickForm('frm','post');
        $form->addElement(  'hidden', 'm[0]', 'scm');
        $form->addElement(  'hidden', 'm[1]', 'view');
        $form->addElement(  'header', 'title', 'By Number');
        $form->addElement(  'select', 'erp', 'ERP#', array(''=>'')+tldLocation::getSalesOrgList("smartyOptions"));
        $form->addElement(  'text', 'id', 'SCM#');
        $form->addElement(  'submit', 'btnSubmit', 'Submit');
        $form->setDefaults(array('erp'=>330));
        $body = $form->toHTML();
    break;
    }
break;
case 'view':
    include('scm/scm.view.inc.php');
break;
case 'reports':
    $DEFAULT_TITLE .= "\Reports";
    switch($m[2]){
    case 'tocNotClosedWithClosedSRVO':
        $form = new HTML_QuickForm('frm','post');
        $form->addElement(  'hidden', 'm[0]', 'scm');
        $form->addElement(  'hidden', 'm[1]', 'reports');
        $form->addElement(  'hidden', 'm[2]', 'tocNotClosedWithClosedSRVO');
        $form->addElement(  'header', 'title', 'Select ERP');
        $form->addElement(  'select', 'erp', 'ERP#', array(''=>'')+tldLocation::getSalesOrgList("smartyOptions"));
        $form->addRule('erp', 'This is required', 'required');
        $form->setDefaults(array('erp'=>330));
        $form->addElement(	'submit', 'btnSubmit', 'Submit');
        
        if(!$form->validate()){
            $body = $form->toHTML();
            break;
        }
        
        $vars = tldUtils::cleanupFormInput($form->exportValues());
        // Get list of TOC not closed with CSR SRVO
        $constraints = "ssoerp={$vars['erp']} and status<>'CLOSED' AND srvo_id>0";
        $extraSelect = <<<EOF
(SELECT module_id FROM csr
	LEFT JOIN mod_links ON mod_links.type LIKE 'CSR' AND mod_links.item=csr.id
	WHERE mod_links.module LIKE 'TOC' AND mod_links.parent_id=toc.id AND csr.module LIKE 'SRVO'
) AS srvo_id
EOF;
        $rows = tldTOC::byConstraints(
            $constraints,
            array(
                'showAllStatuses'=>TRUE,
                'select'=>$extraSelect
            )
        );
        // foreach TOC check SRVO status
        foreach($rows as $k=>&$row){
            $srvo = new tldServiceOrder((int)$row['srvo_id'],(int)$vars['erp']);
            // if not close, remove!
            if($srvo->getStatus()<5) unset($rows[$k]);
            // add srvo status
            $row['srvo_status'] = $srvo->getStatus();
        }
        
        // Display listing
        $report = new tldReportColumnar(
            $rows,
            array(
                "xItems"=>array(
                    'id'=>'TOC#',
                    'dt'=>'Date',
                    'sso_fullname'=>'SSO',
                    'status'=>'Status',
                    'sn'=>'ER SN',
                    'cust_asset_num'=>'ER Asset',
                    'srvo_id'=>'SRVO#',
                    'srvo_status'=>'SRVO status',
                ),
                "title"=>"TOC not CLOSED with CLOSED SRVO",
                "links"=>array(
                    "id"=>"$php_self?m[0]=toc&m[1]=view&id=",
                    "srvo_id"=>array(
                    	"url"=>"$php_self?m[0]=srvo&m[1]=view",
                        "params"=>array("id"=>"srvo_id","erp"=>"ssoerp")
                    )
                )
            )
        );
        $body = $report->fetch();
    break;
   	case 'hourmeter':
        $form = new HTML_QuickForm('frm','post');
        $form->addElement(  'hidden', 'm[0]', 'scm');
        $form->addElement(  'hidden', 'm[1]', 'reports');
        $form->addElement(  'hidden', 'm[2]', 'hourmeter');
        $form->addElement(  'header', 'title', 'Hourmeter Report By Date');
        $form->addElement(  'select', 'erp', 'ERP#', array(''=>'')+tldLocation::getSalesOrgList("smartyOptions"));
		$form->addElement(	'date', 	'x',	'From',	
			array("format"=>"Ymd","minYear"=>'2014',"maxYear"=>date('Y')));
		$form->addElement(	'date', 	'y',	'To',
			array("format"=>"Ymd","minYear"=>'2014',"maxYear"=>date('Y')));
		$form->addElement(	'submit', 'btnSubmit', 'Submit');
		$form->addRule('erp', 'This is required', 'required');
		$form->addRule('x', 'This is required', 'required');
		$form->addRule('y', 'This is required', 'required');
		$form->setDefaults(array("x"=>date("Y-m-1")));
		$form->setDefaults(array("y"=>date("Y-m-d")));
        $form->setDefaults(array('erp'=>330));
        $body = $form->toHTML();
        if($form->validate()){
			$vars = tldUtils::cleanupFormInput($form->exportValues());
			$vars['from']=implode('-',$vars['x']);
			$vars['to']=implode('-',$vars['y']);
			$nl = '<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ';
        	$query=<<<EOF
        	SELECT
        		s.*,
        		CONCAT('SCM #',maintenance_contract_ref) AS scm_display_id,
        		CONCAT(
        			'SN #: ',s.sn,
        			'{$nl}',
        			'Cust Asset #: ',s.cust_asset_num,
        			'{$nl}',
        			'Unit Location: ',s.location_short,
        			'{$nl}',
        			'Num of Readings'
        		) AS sn_display,
        		sh.hourmeter,
        		sh.dt,
        		CONCAT(sh.module,IF(sh.module_id,CONCAT(' #',sh.module_id),'')) AS module_display
        	FROM
        		service s
        		LEFT JOIN service_hourmeter sh ON sh.parent_id=s.id
        	WHERE
        		s.maintenance_contract_erp = '{$vars['erp']}' AND
        		s.maintenance_contract_ref > '' AND
        		sh.dt BETWEEN '{$vars['from']}' AND '{$vars['to']}'
        	ORDER BY
        		s.maintenance_contract_ref ASC,
        		s.sn ASC,
        		sh.dt DESC
EOF;
        	$rows = tldUtils::getSqlToAssocArray($query);
        	$form = new tldReportMultiLevel(
        		$rows,
        		array('scm_display_id','sn_display'),
        		array(
        			'hourmeter' => 'Hourmeter Reading',
        			'dt' => 'Recorded When',
        			'module_display' => 'From Module'
        		),
        		array(
        			'title' => "Hourmeter Logged Between {$vars['from']} and {$vars['to']} For ERP #{$vars['erp']}",
        			'showNumberOfRowsByLevel' => array(1)
        		)
        	);
        	$body .= $form->fetch();
        }
   	break;
    default:
        $body = $smarty->fetch("$PATH/scm/reports/homepage.reports.tpl");
    break;
    }
break;
case 'listing':
    $xItems = array(
        "t_ccon"=>"SCM#",
        "erp"=>"ERP#",
        "t_desc"=>"Description",
        "t_cuno"=>"Cuno",
        "t_refa"=>"Ref A",
        "status"=>"Status",
        "t_sdat"=>"Effective date",
        "t_edat"=>"Expiry date",
    );
    
    switch($m[2]){
    case 'byERPStatus':
        $status = TldDatabase::escape($x);
        $erp = TldDatabase::escape($y);
        $rows = tldSCM::byERPStatus($erp,$status);
        $_title = "SCM by ERP $erp, status $status";
    break;
    }
    
    
    switch($out){
    case 'xls':
        $report = new tldXLS(
            $sess['scm']['listing'],
            array(
                "xItems"=>$sess['scm']['xItems'],
                "showTitles"=>true
            )
        );
        $report->out();
        exit;
    break;
    default:
        if(count($rows)<1){
            $DEFAULT_ERROR[] = "ERROR: No Contracts found...";
            break;
        }
        $DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=scm&m[1]=listing&out=xls">XLS version</a>
EOF;
        $sess['scm']['listing'] = $rows;
        $sess['scm']['xItems'] = $xItems;
        $body.= _getListing($rows, $_title, $xItems);
    break;
    }
break;
case 'files':
    $DEFAULT_TITLE .="\Files";
    $DEFAULT_MENU.=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/en/private/common/index.php?m[0]=files&m[1]=form&m[2]=newFile&module=SCM">Add New File</a>
EOF;
    $report = new tldReportColumnar(
        tldSCM::getSCMModuleFiles(),
        [
            "xItems"=>[
                "id"=>"File ID",
                "date"=>"Date",
                "description"=>"Description",
                "filename"=>"Filename"
            ],
            "links"=>[
                "id"=>"/en/private/common/index.php?m[0]=files&m[1]=view&id="
            ]
        ]
    );
    $body .= $report->fetch();
break;
default:
    $body.= <<<EOF
<h3>Service Contract Module</h3>
<p>Welcome to the Service Contract module</p>
EOF;
    // By ERP status
    $form = new tldMatrix(
        tldSCM::countByERPStatus(),
        "status","erp","num",
        "$php_self?m[0]=scm&m[1]=listing&m[2]=byERPStatus",
        "SCM Count by BAAN ERP# by Status",
        array(
            "doNotLinkXTotals"=>TRUE,
        	"doNotLinkYTotals"=>TRUE,
            "xItems"=>tldSCM::getStatusList()
        )
    );
    $body.=$form->fetch();
    // Lastest Contract
    foreach(tldSCM::getAllowedERP() as $erp){
        $body.=_getListing(
            tldSCM::getLatestByERP($erp),
            "Latest contract for ERP# $erp"
        );
    }
    
break;
}


function _getListing($rows, $_title, $xItems=NULL){
	global $php_self;
    if(empty($xItems)){
        $xItems = array(
            "t_ccon"=>"SCM#",
            "erp"=>"ERP#",
            "t_desc"=>"Description",
            "t_cuno"=>"Cuno",
            "t_refa"=>"Ref A",
            "status"=>"Status",
            "t_sdat"=>"Effective date",
            "t_edat"=>"Expiry date",
        );
    }
    $report = new tldReportColumnar(
        $rows,
        array(
            "xItems"=>$xItems,
            "title"=>$_title,
            "links"=>array(
                "t_ccon"=>array(
                	"url"=>"$php_self?m[0]=scm&m[1]=view",
                    "params"=>array("id"=>"t_ccon","erp"=>"erp")
                )
            )
        )
    );
    return $report->fetch();
}

