<?php
if(empty($id) OR !is_numeric($id)){
    $DEFAULT_ERROR[]=  "ERROR: no ID set...";
    return;
}
$feedback = new tldCustomerFeedback($id);
if($feedback->isEmpty()){
    $DEFAULT_ERROR[]=  "ERROR: No Customer Feedback#$id found...";
    return;
}

$header = $feedback->getHeader();

$DEFAULT_TITLE .= "\Customer Feedback#$id";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=cust_feedback&m[1]=view&id=$id">General</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cust_feedback&m[1]=view&m[2]=linkCust&id=$id" title="Link to Customer">eCustomer Link</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cust_feedback&m[1]=view&m[2]=logs&id=$id" title="Related Logs">Logs</a>
EOF;

if($user->isInGroup(array("gg_MIS"))) {
    $DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="cust_feedback/cust_feedback_admin.php?mode=record_view&form_type=main_tpl&id=$id">Admin (MIS only)</a>
EOF;
}

switch($m[2]){
case 'linkCust':
    $DEFAULT_TITLE .= "\\eCustomer Link";
    $customerList = tldCustomer::getList("smartyOptions");
    if(empty($header['customer_id'])){
        $color = "#FF5252";
        $link = "No eCustomer Link";
    }else{
        $color = "#6CC071";
        $link = "Linked to eCustomer#".$header['customer_id'];
    }
    $body.=<<<EOF
<table border="0" width="100%" cellspadding="2" cellspacing="4">
  <tr>
    <td align="center" bgcolor="{$color}"><b>Customer Feedback #{$id}</b><br>{$link}</td>
  </tr>
</table>
EOF;
    $form = new HTML_QuickForm('frmCust', 'post');
    $form->addElement(  'hidden', 'm[0]', 'cust_feedback');
    $form->addElement(  'hidden', 'm[1]', 'view');
    $form->addElement(  'hidden', 'm[2]', 'linkCust');
    $form->addElement(  'hidden', 'id',   '$id');
    $form->addElement(  'header', 'head', "eCustomer Link");
    $form->addElement(  'select', 'customer_id', 'Customer', array(""=>"")+$customerList);
    $form->addElement(  'submit', 'btnSubmit', 'Submit');
    $form->setDefaults($header);
    if(!$form->validate()){
        $body .= $form->toHTML();
        break;
    }
    
    $vars = tldUtils::cleanupFormInput($form->exportValues());
    $e = $feedback->update($vars,array('customer_id'));
    if(is_string($e)){
        $DEFAULT_ERROR[] = "ERROR: Could not update the record!<br/>Reason: $e";
        break;
    }else{
        $body = "eCustomer successfully linked!";
        $newHeader = $feedback->getHeader();
        $feedback->addLogEntry($user->getID(), "Customer Feedback linked to#{$newHeader['customer_id']}");
    }
break;
case 'logs':
    $DEFAULT_TITLE .= "\Logs";
    $report = new tldReportColumnar(
        $feedback->getLog(),
        array(
            "xItems"=>array(
                "id"                =>"ID#",
                "date"              =>"Date",
                "poster_fullname"   =>"Poster",
                "module"            =>"Module",
                "comment"           =>"Comment"
            )
        )
    );
    $body.=$report->fetch();
break;
default:
    $body = getGeneralTab();
break;
}