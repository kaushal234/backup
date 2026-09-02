<?php
include_once("sales_service.inc.php");
include_once("calendar.inc.php");

$DEFAULT_TITLE .= "\Customer Communication Records(ALPHA)";
// Get MOO ID
$moo_id = tldModule::getMOOIDByModule('ccr');
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=ccr">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=ccr&m[1]=byID">By number</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=ccr&m[1]=reports">Reports</a>
&nbsp;|&nbsp;<a href="/en/private/directory/index.php?m[0]=people&m[1]=view&id=$moo_id" title="Module Owner">Owner</a>
EOF;
//&nbsp;|&nbsp;<a href="$php_self?m[0]=ccr&m[1]=listing&m[2]=search">Search</a>

if($user->isInGroup(array("gg_ADMIN","gg_PARTS"))){
	$DEFAULT_MENU .=<<<EOF
	&nbsp;|&nbsp;<a href="ccr/ccr_admin.php">Maintain CCR</a>
EOF;
}

switch($m[1]){
case 'byID':
    $form = new HTML_QuickForm('frmByNum', 'post');
    $form->addElement(  'hidden', 'm[0]', 'ccr');
    $form->addElement(  'hidden', 'm[1]', 'view');
    $form->addElement(  'text', 'id', 'CCR#');
    $form->addElement(  'submit', 'btnSubmit', 'Submit');
    $body = $form->toHTML();
break;
case 'view':
    include("view.inc.php");
break;
case 'listing':
    switch($m[2]){
    case 'search':
        $DEFAULT_TITLE .= "\Search";
        $form = new HTML_QuickForm('frmSearch', 'post');
        $form->addElement(  'hidden', 'm[0]', 'ccr');
        $form->addElement(  'hidden', 'm[1]', 'listing');
        $form->addElement(  'hidden', 'm[2]', 'search');
        $form->addElement(  'header', 'title', "Search");
        $form->addElement(  'text', 'phrase', 'Phrase');
        $form->addElement(  'submit', 'btnSubmit', 'Submit');
    //        $form->addRule("phrase", "Required", "required");
        if (!$form->validate()){
            $body .= $form->toHTML();
            break 2;
        }
        $rows = tldCCR::search(tldUtils::cleanupFormInput($form->exportValues()));
    break;
    case 'byTypeStatus':
        $TITLE = "List by Status $x, Type $y";
        $rows = tldCCR::byTypeStatus($y, $x);
    break;
    case 'byCustomer':
        $TITLE = "List by customer";
        $rows = tldCCR::byConstraints(
            array(
                "cuid"=>$id
            )
        );
    break;
    }
	if(count($rows)){
		$report = new tldReportColumnar(
            $rows,
            array(
                "xItems"=>array(
                    "id"=>"CCR#",
                    "status_fullname"=>"Status",
                    "type"=>"CCR Type",
                    "ifactor"=>"Importance Factor",
                    "dt"=>"Entered Date",
                    "cuid_fullname"=>"Customer Name",
                    "exu_id_fullname"=>"Extranet User Name",
                    "dsca"=>"Short Description",
                    "model"=>"Model",
                    "dt_eta"=>"Estimated Completion",
                ),
                "title"=>$TITLE,
                "links"=>array(
                    "id"=>"$php_self?m[0]=ccr&m[1]=view&id="
                )
            )
        );
		$body .= $report->fetch();
	}else{
		$DEFAULT_ERROR[] = "ERROR: No CCRs found...";
	}
break;
default:
	$body .= $smarty->fetch("$PATH/ccr/homepage.ccr.tpl");

    $form = new tldMatrix(
        tldCCR::countByTypeStatus(),
        "status_fullname", "type", "num",
        "$php_self?m[0]=ccr&m[1]=listing&m[2]=byTypeStatus",
        "CCR Count by Status, Type",
        array(
            "xItems"=>array(
                'PENDING','REVIEW',
                'TLD','TLD - LATE',
                'CUSTOMER','CUSTOMER - LATE',
                'CLOSED'
            )
        )
    );
    $body .= $form->fetch();

    $report = new tldReportColumnar(
        tldCCR::byLatest(),
    	array(
    	   "xItems"=>array(
                "id"=>"CCR#",
                "status_fullname"=>"Status",
                "type"=>"CCR Type",
                "ifactor"=>"Importance Factor",
                "dt"=>"Entered Date",
                "cuid_fullname"=>"Customer Name",
                "exu_id_fullname"=>"Extranet User Name",
                "dsca"=>"Short Description",
//                "dscb"=>"Long Description",
                "model"=>"Model",
                "dt_eta"=>"Estimated Completion",
            ),
    		"title"=>"Latest",
    		"links"=>array(
                "id"=>"$php_self?m[0]=ccr&m[1]=view&id="
    		)
		)
	);
	$body .= $report->fetch();
}

function getGeneralPage(){
    global $header,$ccr;
    $reportHeader = new tldAssocTable(
        $header,
        array(
            "id"=>"CCR#",
            "status_fullname"=>"Status",
            "type"=>"CCR Type",
            "ifactor"=>"Importance Factor",
            "dt"=>"Entered Date",
            "cuid_fullname"=>"Customer Name",
            "exu_id_fullname"=>"Extranet User Name",
            "dt_eta"=>"Estimated Completion",
            "dsca"=>"Short Description",
            "dscb"=>"Long Description",
            "model"=>"Model",
            "email"=>"Main Email",
            "email_cc"=>"Email CC",
            ),
        array(
            "title"=>"General"
        )
    );
    // Get messages
    $report = new tldReportColumnar(
        $ccr->getMessages(),
        array(
            "xItems"=>array(
                "id"    			=>"ID#",
                "date"      		=>"Date",
                "poster_fullname"   =>"Poster",
                "comment"       	=>"Comment"
            ),
            "title"=>"Messages"
        )
    );
    return $reportHeader->fetch().$report->fetch();
}

function _notifyCustomer($subject, $body, $file=""){
	global $ccr,$user;
	// Prepare data
	$fullName = $user->getFullname();
	$date = date("M jS Y");
	// Cut data to display > 80 chars -> Task #164499
	$string = wordwrap($body, 80, '[cut]', 1);
	$exploded_string = explode('[cut]', $string);
	$body = $exploded_string[0];
	// Clean and update text
	$body = mb_convert_encoding($body, 'UTF-8', mb_list_encodings());
	$body = str_replace(array("\r\n","\r","\n"),'<br>',$body);
	// HTML renderer
	$MSG = <<<EOF
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <title>TLD - CCR notification</title>
  </head>
  <body>

	<table>
	  <tr>
	    <td width="100"><img src="/shared/icons/tld-icon.gif" alt="TLD" /></td>
	    <td>Customer Communication Record #$ccr->itsID : {$ccr->itsHeader['dsca']}</td>
	  </tr>
	</table>

	<p>Notification from $fullName on $date</p>

	<p><span style="text-decoration:underline;">Text of notification:</span><br/>$body [...]</p>

	<br/>

	<p>To see the complete notification, please click on this link
	<a href="https://www.tld-gse.com/extranet/extranet.php?m[0]=ccr&m[1]=view&id=$ccr->itsID">CCR#$ccr->itsID</a>
	for Customer access<br/>or this link
	<a href="https://www.tld-gse.com/en/private/sales_service/sales.php?m[0]=ccr&m[1]=view&id=$ccr->itsID">CCR#$ccr->itsID</a>
	for TLD employee access.
	</p>

	<p style="color:gray; font-size:11px;">Warning: This message is only intended to be used for communication
	between TLD and its customers and should not be shared with alternate parties</p>

  </body>
</html>
EOF;
	return $ccr->notifyTeamCCCustomer($subject,$MSG,$file);
}
?>