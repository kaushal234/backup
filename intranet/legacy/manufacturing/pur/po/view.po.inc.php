<?php
if(empty($id) || !is_numeric($id) || empty($erp) || !is_numeric($erp)){
    $DEFAULT_ERROR[] = "ERROR: Data sent empty or invalid...";
    return;
}
$po = new tldPO($id, $erp);
if($po->isEmpty()){
    $DEFAULT_ERROR[] = "ERROR: Could not retrieve the PO#$id for $erp";
    return;
}
$DEFAULT_TITLE.= "\PO#$id";
$DEFAULT_MENU .=<<<EOF
<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=po&m[1]=view&id=$id&erp=$erp">Default view</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=po&m[1]=view&m[2]=shipped&id=$id&erp=$erp">Shipped view</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=po&m[1]=view&m[2]=log&id=$id&erp=$erp">Log</a>&nbsp;|&nbsp;
<a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=listing&m[2]=byRefNum&id=$id">SEQ approval</a>
EOF;
    
switch($m[2]){
case 'shipped':
    $smarty->assign("erp", $erp);
    $body .= _getDisplayPo($po,"shipped");
break;
case 'log':
    $report = new tldReportColumnar(
        $po->getLog(),
        array(
            "xItems"=>array(
                "id"=>"ID#",
                "date"=>"Date",
                "comment"=>"Comment"
            )
        )
    );
    $body = $report->fetch();
break;
default:
    $smarty->assign("erp", $erp);
    $body .= _getDisplayPo($po);
break;
}

?>