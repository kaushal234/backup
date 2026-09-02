<?php
include_once("sales_service.inc.php");
include_once('webservice.inc.php');
include_once("erp.inc.php");

$DEFAULT_TITLE .= "\SRVO Module";
$DEFAULT_MENU.=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=srvo">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=srvo&m[1]=form&m[2]=byNumber">By number</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=srvo&m[1]=reports">Reports</a>
EOF;

switch($m[1]){
case 'form':
    switch($m[2]){
    case 'byNumber':
        $form = new HTML_QuickForm('frm','post');
        $form->addElement(  'hidden', 'm[0]', 'srvo');
        $form->addElement(  'hidden', 'm[1]', 'view');
        $form->addElement(  'header', 'title', 'By Number');
        $form->addElement(  'select', 'erp', 'ERP#', array(''=>'')+tldLocation::getSalesOrgList("smartyOptions"));
        $form->addElement(  'text', 'id', 'SRVO#');
        $form->addElement(  'submit', 'btnSubmit', 'Submit');
        $form->setDefaults(array('erp'=>330));
        $body = $form->toHTML();
    break;
    }
break;
case 'view':
    include('srvo/srvo.view.inc.php');
break;
case 'reports':
    $DEFAULT_TITLE .= "\Reports";
    
    switch($m[2]){
    case 'baanSoapTransAudit':
        $form = new tldMatrix(
			tldBaanSoapTransaction::countByProgramStatusByConstraints("t_prog IN('createCSR','updateCSR')"),
			"t_prog", "t_stat", "num",
			NULL,
			"Baan Soap transactions by Program by Status"
		);
		$body.=$form->fetch();
    break;
    default:
        $body = $smarty->fetch("$PATH/srvo/reports/homepage.reports.tpl");
    break;
    }
break;
case 'listing':
    $xItems = array(
        't_orno'=>'Service Order#',
        'erp'=>'ERP#',
        't_ddt1'=>'Date',
        'orderSeriesDesc'=>'Type',
    	't_swor'=>'Status',
        't_cuno'=>'Cuno#',
        't_cins'=>'Installation',
        't_cloc'=>'Location',
        't_desc'=>'Description',
    );
    
    switch($m[2]){
    
    }
    
    switch($out){
    case 'xls':
        $report = new tldXLS(
            $sess['msc']['listing'],
            array(
                "xItems"=>$sess['msc']['xItems'],
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
<a href="$php_self?m[0]=srvo&m[1]=listing&out=xls">XLS version</a>
EOF;
        $sess['msc']['listing'] = $rows;
        $sess['msc']['xItems'] = $xItems;
        $body.= _getListing($rows, $_title, $xItems);
    break;
    }
break;
default:
    $body.= <<<EOF
<h3>Service Order Module</h3>
<p>Welcome to the SRVO module</p>
EOF;
break;
}


function _getListing($rows, $_title, $xItems=NULL){
	global $php_self;
    if(empty($xItems)){
        $xItems = array(
            't_orno'=>'Service Order#',
            'erp'=>'ERP#',
            't_ddt1'=>'Date',
            'orderSeriesDesc'=>'Type',
        	't_swor'=>'Status',
            't_cuno'=>'Cuno#',
            't_cins'=>'Installation',
            't_cloc'=>'Location',
            't_desc'=>'Description',
        );
    }
    $report = new tldReportColumnar(
        $rows,
        array(
            "xItems"=>$xItems,
            "title"=>$_title,
            "links"=>array(
                "t_orno"=>array(
                	"url"=>"$php_self?m[0]=srvo&m[1]=view",
                    "params"=>array("id"=>"t_orno","erp"=>"erp")
                )
            )
        )
    );
    return $report->fetch();
}

