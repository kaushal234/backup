<?php
include_once("erp.inc.php");

if(!$user->isInGroup(array("gg_ADMIN", "gg_ACCT","gg_PARTS","gg_SALES","gg_SUPPORT","gg_PUR"))){
	$DEFAULT_ERROR[] = "ERROR: You do not have permissions for this page..";
	return;
}

$DEFAULT_TITLE .= "\Sales Orders";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=so">Home</a>
EOF;

switch($m[1]){
case 'form':
	switch($m[2]){
	case 'search':
		$form = new HTML_QuickForm('search', 'get');
		$form->addElement(	'hidden', 'm[0]', 'so');
		$form->addElement(	'hidden', 'm[1]', 'search');
		$form->addElement(	'header', 'title', "Search Sales Orders");
		$form->addElement(	'text', 'id', 'SO#');
		$form->addElement(	'submit', 'btnSubmit', 'Submit');
		$form->addRule('id','Required','required');
		if ($form->validate()){
			$rows = tldINV::search($t_nso, $user->getERP(),
						 array("where"=>array("t_cuno"=>$user->getCUNO()))
					);
			$report = new tldReportColumnar(	$rows,
					array(	"xItems"=>array("t_nso"	=>"Invoice Number#",
									"t_docd"	=>"Date",
									"t_dued"	=>"Due",
									"t_refr"	=>"Ref R",
									"t_orno"=>"Order#"),
							"title"=>"Invoices",
	//						"links"=>array("t_nso"=>"$php_self?m[0]=so&m[1]=view&id="
							"links"=>array("t_nso"=>array("url"=>"$php_self?m[0]=so&m[1]=view",
														"params"=>array("t_nso"=>"t_nso",
																		"t_orno"=>"t_orno")
														)
											)
						)
					);
			$body .= $report->fetch();
		}else{
			$body = $form->toHTML();
		}
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
    $DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=archive&erp=$erp&doctype=SALES ORDER ACK&id=$id" title="PDF Archive">
<img src="/shared/bluesphere/32x32/mimetypes/pdf.png"></a>
EOF;
	$so = new tldSO($id, $erp);
	$header = $so->getHeader();
	$report = new tldAssocTable($header,
        array(
            "t_orno"	=>"Order Number#",
            "t_odat"	=>"Date",
            "t_cuno"	=>"Customer#",
            "t_refa"	=>"Ref A",
            "t_refb"	=>"Ref B",
        	"t_eono"	=>"Cust PO#"
        ),
        array(
            "title"=>	"Sales Order #$id",
            "doNotShowEmpty"=>true,
            "links"=>array(
                "t_cuno"=>"/en/private/parts/parts.php?m[0]=cuno&m[1]=view&id="
            )
        )
    );
	$cells[] = $report->fetch();

    $t = decodeDocs($header['t_refa']).
        decodeDocs($header['t_refb']);
    $cells[] = $t<>'' ? "<h3>References</h3>".$t : "&nbsp;";
	$report = new tldAssocTable(
        $so->getDeliveryAddress(),
        array(
            "t_nama"=>"Line 1",
            "t_namb"=>"Line 2",
            "t_namc"=>"Line 3",
            "t_namd"=>"Line 4",
            "t_name"=>"Line 5",
            "t_namf"=>"Line 6",
            "t_namg"=>"Line 7",
            "t_ccty"=>"Country"
        ),
        array(
            "title"=>"Delivery Address",
            "doNotShowEmpty"=>true
        )
    );
	$cells[] = $report->fetch();

	$report = new tldHTMLTable(
        $cells,
        array(
            "cols"=>3,
                "attribs"=>array(
                    "table"=>" width='100%'"
            )
        )
    );
	$body .= $report->fetch();
	$report = new tldReportMultilevel(
        $so->getDetail(),
        array("t_pono"),
        array(
            "t_srnb"=>"Sequence",
            "t_item"=>"Part Number",
            "t_dsca"=>"Description",
            "t_cuqs"=>"UM",
            "t_oqua"=>"Qty",
            "t_dqua"=>"Del",
            "t_bqua"=>"Back",
            "t_ssls"=>"Status<br>(7=Shipped)",
            "t_qono"=>"Quote#",
            "t_ddat"=>"Delivery Date",
        	"t_ddta"=>"Plan Delivery Date",
            "t_dino"=>"Packing Slip#",
            "t_ttyp"=>"Inv Type",
            "t_invn"=>"Inv#"
            ),
        array(
            "links"=>array(
                "t_item"=>array(
                    "url"=>"/en/private/parts/parts.php?m[0]=inv&m[1]=view",
                    "params"=>array("id"=>"t_item")
                ),
                "t_invn"=>array(
                    "url"=>"$php_self?m[0]=inv&m[1]=view&erp=$erp",
                    "params"=>array("ttyp"=>"t_ttyp", "id"=>"t_invn")
                ),
                "t_dino"=>array(
                    "url"=>"$php_self?m[0]=ps&m[1]=view&erp=$erp",
                    "params"=>array("id"=>"t_dino")
                ),
                "t_qono"=>array(
                    "url"=>"/en/private/finance/finance.php?m[0]=archive&erp=$erp&doctype=SALES%20QUOTATION",
                    "params"=>array("id"=>"t_qono")
                )
            )
        )
    );
	$body .= $report->fetch();
break;
case 'list':
    switch($m[2]){
    case 'byCustomerERP':
    	if(empty($id) || empty($erp)){
            $DEFAULT_ERROR[] = "ERROR: ERP or Customer number not set";
            break 2;
        }
        $rows = tldSO::byCustomerERP($id, $erp);
    break;
    }
    if(count($rows)){
        $report = new tldReportColumnar(
            $rows,
            array(
                "xItems"=>array(
                    "t_orno"	=>"Order Number#",
                    "t_odat"	=>"Date",
                    "t_refa"	=>"Ref A",
                    "t_refb"	=>"Ref B"
                ),
                "title"=>"Sales Orders, Company# $erp, Customer# $id",
                "links"=>array(
                     "t_orno" =>array(
                        "url" =>"$php_self?m[0]=so&m[1]=view",
                        "params" =>array(
                            "id"=>"t_orno",
                            "erp"=>"erp"
                        )
                    )
                )
            )
        );
        $body .= $report->fetch();
    }else{
        $DEFAULT_ERROR[] = "ERROR: No Sales Orders found for $erp and $id";
    }
break;
default:
    $form = new HTML_QuickForm('frmSOByNum', 'get');
    $form->addElement(	'hidden', 'm[0]', 'so');
    $form->addElement(	'hidden', 'm[1]', 'view');
    $form->addElement(	'header', 'title', "SO by Number");

    $form->addElement(	'select', 'erp', 'BU', tldLocation::getERPList("smartyOptions"));
    $form->addElement(	'text', 'id', 'SO#');
    $form->addElement(	'submit', 'btnSubmit', 'Submit');
    $body .= $form->toHTML();
}

function decodeDocs($str){
    //split on the ;
    $docs = explode(";", $str);
    //split on #
    if(count($docs)==0)return;
    foreach($docs as $doc){
        list($type, $num) = explode("#", $doc);
        if(in_array($type, array("SB","SPR","WC"))){
            $url = tldModLink::getURL($type, $num);
            $r .=<<<EOF
<a href="$url">$type $num</a><br>
EOF;
        }elseif($type=='SN'){
            $r .=<<<EOF
<a href="/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=search&m[2]=bySN&sn=$num">$type $num</a><br>
EOF;
        }
    }
    return $r;
}
?>