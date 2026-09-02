<?php
include_once("sales_service.inc.php");
include_once("erp.inc.php");

$DEFAULT_TITLE .= "\CRT Module";
$DEFAULT_MENU.=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=crt">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=crt&m[1]=forms&m[2]=byNum">By Number</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=crt&m[1]=forms&m[2]=search">Search</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=crt&m[1]=forms&m[2]=new">New</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=crt&m[1]=reports">Reports</a>
&nbsp;|&nbsp;<a href="/en/private/mis/mis.php?m[0]=help&m[1]=dms&id=746" target="_blank">Help</a>
EOF;

switch($m[1]){
case 'newtask':
    $body = "This page has been migrated and should not be displayed anymore.";
break;
case 'forms':
case 'view':
    $body = "This page has been migrated and should not be displayed anymore.";
break;
case 'reports':
case 'list':
    $body = "This page has been migrated and should not be displayed anymore.";
break;
default:
    $body = "This page has been migrated and should not be displayed anymore.";
break;
}


function _getGeneral(){
	global $header,$id, $smarty;
	$report = new tldAssocTable($header,
		array(
            "id"				=>"ID#",
            "customer_name"		=>"Customer Name",
			"erp"				=>"ERP#",
			"cuno"				=>"ERP Customer#",
            "sales_rep"			=>"Sales rep",
            "parts_rep"			=>"Parts rep",
            "services_rep"		=>"Services Rep",
            "parts_location"	=>"Parts location",
            "services_location"	=>"Services location"
        ),
		array("title"=>"Customer Relationship Team #$id")
	);
	$cells[] = $report->fetch();

    $roles = array("sales_rep_id", "parts_rep_id", "services_rep_id");
	$smarty->assign("next_step","m[0]=card");
    foreach($roles as $role){
        if($header[$role]){
            $repUser = new tldUser($header[$role]);
            $smarty->assign("person", $repUser->getHeader());
            $cells[] = $smarty->fetch("account/view.card.sm.tpl");
        }
    }

    $report = new tldHTMLTable(
        $cells,
        array(
            "cols"=>4,
            "attribs"=>array(
                "table"=>" width='100%'",
                "tr"=>" bgcolor='#FFFFFF'"
            )
        )
    );
    $r .= $report->fetch();
    return $r;
}

function _getListing($rows,$title,$xItems=""){
    if(empty($xItems)){
        $xItems = array(
            "id"				=>"ID#",
            "customer_name"		=>"Customer Name",
			"erp"				=>"ERP#",
			"cuno"				=>"ERP Customer#",
            "sales_rep"			=>"Sales rep",
            "parts_rep"			=>"Parts rep",
            "services_rep"		=>"Services Rep",
            "parts_location"	=>"Parts location",
            "services_location"	=>"Services location"
		);
    }

	$report = new tldReportColumnar(
    	$rows,
		array(
			"xItems"=>$xItems,
            "title"=>$title,
            "links"=>array("id"=>"$php_self?m[0]=crt&m[1]=view&id=")
		)
	);
    return $report->fetch();
}
?>
