<?php
if(empty($id) || !is_numeric($id) || empty($erp) || !is_numeric($erp)){
    $DEFAULT_ERROR[] = "ERROR: Data sent empty or invalid...";
    return;
}
$rfq = new tldRFQ($id, $erp);
if($rfq->isEmpty()){
    $DEFAULT_ERROR[] = "ERROR: RFQ#$id not found...";
    return;
}
$suno = $rfq->getSUNO();
$header = $rfq->getHeader();

$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=rfq&m[1]=view&erp=$erp&id=$id">Home</a>&nbsp;|&nbsp;
<a href="/en/private/finance/finance.php?m[0]=archive&doctype=PURCHASE INQUIRY&erp=$erp&id=$id" title="Get PDF archive">PDF</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=rfq&m[1]=listing&m[2]=bySunoERP&erp=$erp&suno=$suno">Vendor $suno RFQ List</a>
EOF;

switch($m[2]){
default:
    $report = new tldAssocTable(
        $header,
        array(
            "t_qono"=>"RFQ#",
            "t_suno"=>"Supplier#",
            "t_rtdt"=>"Return Date",
            "t_qspa"=>"Status"
        ),
        array("title"=>"RFQ #$id")
    );
    $body .= $report->fetch();
    $report = new tldReportColumnar(
        $rfq->getDetail(),
        array(
            "xItems"=>array(
                "t_pono"=>"Position",
                "t_item"=>"Part Number",
                "t_dsca"=>"Description",
                "t_qdat"=>"Date",
                "t_oqua"=>"Qty",
                "t_cuqp"=>"UM",
                "t_cwar"=>"Warehouse",
                "t_ddat"=>"Requested Delivery Date"
            ),
            "links"=>array(
                "t_item"=>"/en/private/manufacturing/eng/dev.php?m[0]=bom&m[1]=view&erp=$erp&date=".date("Y-m-d").'&pn='
            )
        )
    );
    $body .= $report->fetch();
break;
}
?>