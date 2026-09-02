<?php
include_once("sales_service.inc.php");

if(!$user->isInGroup(['role_ASM','role_COO','role_CEO','role_CHAIRMAN','role_CSD', 'ROLE_GTCD', 'gg_EXCOM'])){
    $DEFAULT_ERROR[] = "ERROR: You do not have permissions for this module";
    return;
}

$DEFAULT_TITLE .= "\Customer Feedback";
$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=cust_feedback" title="Customer Feedback Homepage">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cust_feedback&m[1]=byNum" title="By Number">By Number</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cust_feedback&m[1]=search" title="Customer Feedback Search">Search</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cust_feedback&m[1]=latestGROUP" title="Latest Customer Satisfaction Feedback">Latest Customer Satisfaction Feedback</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cust_feedback&m[1]=latestEXT" title="Latest Extranet Feedback">Latest Extranet User Feedback</a>
EOF;
if($user->isInGroup(array("gg_ADMIN","gg_MIS"))){
    $DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="cust_feedback/cust_feedback_admin.php" title="Maintain Customer Feedback">Admin</a>
EOF;
}

switch($m[1]){
case 'view':
    include_once("cust_feedback/view.inc.php");
break;
case 'byNum':
    $DEFAULT_TITLE .= "\By Number";
    $form = new HTML_QuickForm('frmByNum', 'post');
    $form->addElement(	'hidden', 'm[0]', 'cust_feedback');
    $form->addElement(	'hidden', 'm[1]', 'view');
    $form->addElement(	'header', 'title', "Customer Feedback by Number");
    $form->addElement(	'text', 'id', 'ID#');
    $form->addElement(	'submit', 'btnSubmit', 'Submit');
    $body .= $form->toHTML();
break;
case 'search':
    // Get listing
    $customerList = tldCustomer::getList("smartyOptions");
    $sourceList = array("EXTRANET"=>"EXTRANET","TLD-GROUP.COM"=>"TLD-GROUP.COM");
    // Get form
    $form = new HTML_QuickForm('frmSearch', 'post');
    $form->addElement(  'hidden', 'm[0]', 'cust_feedback');
    $form->addElement(  'hidden', 'm[1]', 'search');
    $form->addElement(  'header', 'title', "Search Customer Feedback");;
    $form->addElement(  'select', 	'source', 			'Source',				['' => '']+$sourceList);
    $form->addElement(	'text', 	'subject',		    'Subject');
    $form->addElement(	'text', 	'dt_from',		    'Date From', 		    ['class'=>'datepicker']);
    $form->addElement(	'text', 	'dt_to',		    'Date To', 		        ['class'=>'datepicker']);
    $form->addElement(  'select', 	'customer_id', 		'Customer ID', 		    ['' => '']+$customerList);
    $form->addElement(	'text', 	'customer_name',    'Customer Name');
    $form->addElement(	'text', 	'name',		        'From (Name)');
    $form->addElement(	'text', 	'email',		    'From (Email)');
    $form->addElement(	'text', 	'country',			'Country');
    $form->addElement(	'text', 	'er_sn',			'ER SN#');
    $form->addElement(  'submit', 'btnSubmit', 'Submit');

    if(!$form->validate()){
        $body = $form->toHTML();
        break;
    }

    $vars = tldUtils::cleanupFormInput($form->exportValues());
    $acl_form_fields = ['source', 'subject', 'customer_id', 'dt_from', 'dt_to', 'customer_name', 'name', 'email', 'country', 'er_sn'];
    $a = array();
    foreach($vars as $key=>$raw){
        if(!in_array($key,$acl_form_fields) || empty($raw)) continue;
        if(in_array($raw, ['%'])) continue;
        switch($key){
            case 'dt_from':
                $a[] = sprintf(' dt > "%s"', $vars['dt_from']);
                break;
            case 'dt_to':
                $a[] = sprintf(' dt < "%s"', $vars['dt_to']);
                break;
            case 'name':
            case 'subject':
            case 'email':
                $a[]=" $key LIKE '%$raw%'";
            break;
            default:
                $a[]=" $key='$raw' ";
            break;
        }
    }
    if(empty($a)){
        $DEFAULT_ERROR[]="ERROR: Not enough constraints to run a safe search...";
        $body = $form->toHTML();
        break;
    }
    $where = implode(" AND ",$a);
    $sql = "SELECT COUNT(*) AS nb FROM customer_feedback WHERE $where";
    $countResults = tldUtils::getSqlRowToAssocArray($sql);
    $limit = 500;
    if($countResults['nb']>$limit){
        $DEFAULT_ERROR[]="ERROR: Not enough constraints to run a safe search, more than $limit has been found ({$countResults['nb']})";
        $body = $form->toHTML();
        break;
    }
    $TITLE = "Search Results";
    $rows = tldCustomerFeedback::byConstraints($where);
    if($rows){
        $xItems = array(
            "id"			=>"ID#",
            "source"		=>"Source",
            "subject"       =>"Subject",
            "dt"            =>"Date",
            "customer_name" =>"Customer Name",
            "name"          =>"Fullname",
            "ext_user_id"   =>"Extranet#",
            "customer_fullname"=>"Customer ID#",
            "country"		=>"Country",
            "er_sn"	        =>"ER SN#",
            "message"		=>"Message"
        );
        $report = new tldReportColumnar(
            $rows,
            array(
                "xItems"=>$xItems,
                "title"=>$TITLE,
                "links"	=> array("id"=>"$php_self?m[0]=cust_feedback&m[1]=view&id=",
                                 "ext_user_id"=>"$php_self?m[0]=extranet&m[1]=view&id="
                )
            )
        );
        $body .= $report->fetch();
        $body .= "<br><b>Total rows = ".count($rows)."</b>";
    }else{
        $body .= "No records...";
    }
break;
case 'latestGROUP':
    // Get latest Customer Satisfaction Feedback
    $report = new tldReportColumnar(
        tldCustomerFeedback::byLatest("byGROUP"),
        array(
            "xItems"=>array(
                "id"=>"ID#",
                "subject"=>"Subject",
                "customer_name"=>"Customer",
                "name"=>"Fullname",
                "dt"=>"Date",
                "message"=>"Message"
            ),
            "title"	=> "Latest Customer Satisfaction Feedback",
            "links"	=> array("id"=>"$php_self?m[0]=cust_feedback&m[1]=view&id=")
        )
    );
    $body .= $report->fetch();
break;
case 'latestEXT':
    // Get latest Extranet User Feedback
    $report = new tldReportColumnar(
        tldCustomerFeedback::byLatest("byEXT"),
        array(
            "xItems"=>array(
                "id"=>"ID#",
                "ext_user_id"=>"Extranet#",
                "subject"=>"Subject",
                "customer_name"=>"Customer",
                "name"=>"Fullname",
                "dt"=>"Date",
                "message"=>"Message"
            ),
            "title"	=> "Latest Extranet User Feedback",
            "links"	=> array("id"=>"$php_self?m[0]=cust_feedback&m[1]=view&id=",
                             "ext_user_id"=>"$php_self?m[0]=extranet&m[1]=view&id=")
        )
    );
    $body .= $report->fetch();
break;
default:
    $body .= $smarty->fetch("$PATH/cust_feedback/homepage.cust_feedback.tpl");
    // Get latest
    $report = new tldReportColumnar(
        tldCustomerFeedback::byLatest(),
        array(
            "xItems"=>array(
                "id"=>"ID#",
                "source"=>"Source",
                "subject"=>"Subject",
                "customer_name"=>"Customer",
                "name"=>"Fullname",
                "dt"=>"Date"
            ),
            "title"	=> "Latest Customer Feedback",
            "links"	=> array("id"=>"$php_self?m[0]=cust_feedback&m[1]=view&id=")
        )
    );
    $body .= $report->fetch();
break;
}

function getGeneralTab(){
    global $feedback;
    $header = $feedback->getHeader();
    // General tab
    $report = new tldAssocTable(
        $header,
        array(
            "id"=>"ID#",
            "source"=>"Source",
            "subject"=>"Subject",
            "dt"=>"Date",
            "recipients"=>"Recipients",
            "customer_name"=>"Customer Name",
            "ext_user_id"=>"Extranet#",
            "name"=>"Fullname",
            "email"=>"Email",
            "title"=>"Title",
            "country"=>"Country",
            "phone"=>"Phone",
            "er_sn"=>"ER SN#",
            "message"=>"Message"
        ),
        array("title"=>"General View")
    );
    $cells[] = $report->fetch();
    // Customer if applicable
    if(!empty($header['customer_id'])){
        $cu =  new tldCustomer($header['customer_id']);
        $report = new tldAssocTable(
            $cu->getHeader(),
            array(
                "id"=>"Customer ID#",
                "customer_name"=>"Customer Name",
                "customer_short_name"=>"Customer Name (Short)",
                "customer_address"=>"Address",
                "customer_tel"=>"Main Tel#",
                "customer_fax"=>"Main Fax#"
            ),
            array("title"=>"Customer Details")
        );
        $cells[] = $report->fetch();
    }

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
    $body = $report->fetch();
    return $body;
}
