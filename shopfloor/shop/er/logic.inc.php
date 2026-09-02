<?php
include_once("product_support.inc.php");
$DEFAULT_TITLE .= "\ER";

switch($m[1] ?? null){
case "search":
	switch($m[2] ?? null){
		case "bySN":
			if(empty($sn)){
				$DEFAULT_ERROR[] = _("ERROR: Serial number required to do search.");
				break;
			}
			$sn = TldDatabase::escape($sn);
			$id = str_replace("T","",strtoupper($sn));
			$rows = tldEquipment::shopSearch($id);
			if(empty($rows)) $rows = tldEquipment::shopSearch("%$id%");
		break;
	}
	if(empty($rows)){
		$DEFAULT_ERROR[] = sprintf(_("ERROR: No search results for ER '%s'..."),$sn);
		break;
	}
	$report = new tldReportColumnar($rows, array(
			"xItems"=>array(
				"id"	=>_("ER ID#"),
				"sn"	=>_("Serial Number"),
				"type"	=>_("Type"),
				"model"	=>_("Model")
			),
			"links"=>array("id"=>"$php_self?m[0]=er&m[1]=form&m[2]=submitComponentSN&id="),
			"title"=>sprintf(_("Pls select correct Equipment Records - Result for '%s'"),$sn)
		)
	);
	$body .= $report->fetch();
break;
case 'view':
    include_once("er/view.inc.php");
break;
default:
    $form = new HTML_QuickForm('frmER','get','','','',true);
    $form->addElement(	'header', 'title', _('SN Search'));
    $form->addElement(	'hidden', 'm[0]', 'er');
    $form->addElement(	'text', 'id', _('Enter or Scan SN'),
		array("size"=>"20")
	);
    $form->addElement(	'submit', 'btnSubmit', _('Submit'));

    if ($form->validate()){
        $a = tldUtils::cleanupFormInput($form->exportValues());
        $rows = tldEquipment::bySN($a['id']);
        if(count($rows) == 1){
        	$erid = $rows[0]['id'];
        	$body .=<<<EOF
        	<meta HTTP-EQUIV="REFRESH" content="0; url=$php_self?m[0]=er&m[1]=view&id=$erid">
EOF;
    	}elseif(count($rows)==0){
            $DEFAULT_ERROR[] = _("ERROR: No ER found for SN#").$a['id'];
            $body = $form->toHTML();
        }else{
        	$body .=<<<EOF
        	<meta HTTP-EQUIV="REFRESH" content="0; url=$php_self?m[0]=er&m[1]=search&&m[2]=bySN&sn={$a['id']}">
EOF;
        }
    }else{
        $attr['body']['onload'] = "javascript:document.frmER.id.focus();";
        $body = $form->toHtml();
        $rows = tldODP::byERP_SSO($LOCATION['location'] ?? null, 'ALL', 'withoutSSO');
        $report = new tldReportColumnar(
            $rows,
            array(
                "xItems"=>array(
                    "id"			=>_("ID#"),
                    "sn"			=>_("SN#"),
                    "customer_name"	=>_("Customer"),
                    "model"			=>_("Model"),
                    "del_dat"		=>_("Customer EXW Request"),
                    "ddel_est1"		=>_("Factory EXW Promise"),
                    "dgt_rev"		=>_("Estimated GT<br>Date"),
                ),
                "title"=>_("Unshipped Units"),
                "links"=>array(
                    "id"=>"$php_self?m[0]=er&m[1]=view&id="
                )
            )
        );
        $body .= $report->fetch();
    }
}
