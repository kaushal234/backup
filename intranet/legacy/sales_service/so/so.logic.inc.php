<?php
include_once("erp.inc.php");

$DEFAULT_TITLE .= "\ERP Sales Orders";
$DEFAULT_MENU.=<<<EOF
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=so">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=so&m[1]=forms&m[2]=byNum">By Number</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=so&m[1]=reports">Reports</a>
EOF;

switch($m[1]){
case 'forms':
    switch($m[2]){
    case 'byNum':
        $DEFAULT_TITLE .= "\Sales Order by Number";
        $form = new HTML_QuickForm('frmByNum', 'get','','','',true);
        $form->addElement(	'hidden', 'm[0]', 'so');
        $form->addElement(	'hidden', 'm[1]', 'view');
        $form->addElement(	'header', 'title', "Sales Order by Number");
        $form->addElement('select','erp','Company Number',
                            tldLocation::getERPList('smartyOptions')
                );
        $form->addElement(	'text', 'id', 'Sales Order#');
        $form->addElement(	'submit', 'btnSubmit', 'Submit');
        $body .= $form->toHTML();
    break;
    }
break;
case 'view':
	if(empty($id)){
		$DEFAULT_ERROR[] = "ERROR: id not set";
		break;
	}
	if(empty($erp)){
		$DEFAULT_ERROR[] = "ERROR: erp not set";
		break;
	}
	$so = new tldSO($id, $erp);
    $header = $so->getHeader();
	if(empty($header)){
		$DEFAULT_ERROR[] = "ERROR: There was a problem with that customer id#";
		break;
	}

    switch($m[2]){
    default:
    	$report = new tldAssocTable($header,
				array("t_orno"	=>"Order Number#",
					"t_odat"	=>"Date",
					"t_refa"	=>"Ref A",
					"t_refb"	=>"Ref B"),
				array("title"=>	"Sales Order #$id", "doNotShowEmpty"=>true)
				);
    	$cells[] = $report->fetch();
        $report = new tldAssocTable($so->getDeliveryAddress(),
					array("t_nama"=>"",
					"t_namb"=>"",
					"t_namc"=>"",
					"t_namd"=>"",
					"t_name"=>"",
					"t_namf"=>"",
					"t_namg"=>"",
					"t_ccty"=>""
					),
					array("title"=>"Delivery Address", "doNotShowEmpty"=>1)
				);
    	$cells[] = $report->fetch();

        $report = new tldHTMLTable($cells,
				array("cols"=>2,
					array("attribs"=>array(
						"table"=>" width='100%'"
						)
					)
				)
			);
    	$body .= $report->fetch();
    	$report = new tldReportMultilevel($so->getDetail(),
				array("t_pono"),
				array("t_srnb"=>"Sequence",
					"t_item"=>"Part Number",
					"t_dsca"=>"Description",
					"t_cuqs"=>"UM",
					"t_oqua"=>"Qty",
					"t_dqua"=>"Del",
					"t_bqua"=>"Back",
					"t_ssls"=>"Status<br>(7=Shipped)",
					"t_ddat"=>"Delivery Date",
					"t_dino"=>"Delivery Note#",
					"t_ttyp"=>"Inv Type",
					"t_invn"=>"Inv#"),
				array(
/*						"links"=>array(
								"t_invn"=>array(
										"url"=>"$php_self?m[0]=inv&m[1]=view",
										//careful with the t_ninv and t_invn below!!!
										"params"=>array("t_ninv"=>"t_invn", "t_orno"=>"t_orno")
										)
								)*/
                )

				);
    	$body .= $report->fetch();
    }
break;
case 'list':
    switch($m[2]){
    case 'byCustomerERP':
        if(empty($id)){
            $DEFAULT_ERROR[] = "ERROR: Customer number not set";
            break;
        }
        if(empty($erp)){
            $DEFAULT_ERROR[] = "ERROR: ERP number not set";
            break;
        }
        $rows = tldSO::byCustomerERP($id, $erp);
    break;
    }
    if(count($rows)){
        $report = new tldReportColumnar(	$rows,
            array(	"xItems"=>array("t_orno"	=>"Order Number#",
                                    "t_odat"	=>"Date",
                                    "t_refa"	=>"Ref A",
                                    "t_refb"	=>"Ref B"),
                    "title"=>"Sales Orders, Company# $erp, Customer# $id",
                     "links"=>array(
                         "t_orno"=>array("url"=>"$php_self?m[0]=so&m[1]=view",
                        "params"=>array("id"=>"t_orno","erp"=>"erp")))
                    )
            );
        $body .= $report->fetch();
    }else{
        $DEFAULT_ERROR[] = "No Sales Orders found for $erp and $id";
    }
break;
case 'reports':
	switch($m[2]){
	case 'unusedAccounts':
		$report = new tldReportColumnar(extranetUser::byUnused(),
				array("xItems"=>array(	"id"=>"ID#",
										"customer_name"=>"Customer Name",
										"userid"=>"Username",
										"lastname"=>"Lastname",
										"firstname"=>"Firstname",
										"counter"=>"Times logged in",
										"last"=>"Lasted logged in"
										),
				"title"=>"Unused Extranet Accounts",
				"links"=>array("id"=>"customers/extranet_users_admin.php?mode=record_view&form_type=main_tpl&id=")
				)
			);
	$body .= $report->fetch();
	break;
	default:
		$body .= $smarty->fetch("$PATH/customers/reports/homepage.reports.tpl");
	}
break;
default:
	$body .= $smarty->fetch("$PATH/so/homepage.so.tpl");
}


?>
