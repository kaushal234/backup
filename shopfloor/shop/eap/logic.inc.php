<?php
include_once("sales_service.inc.php");
require_once('HTML/QuickForm/advmultiselect.php');

$DEFAULT_TITLE .= "\EAP";
$DEFAULT_MENU .="
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href=\"$php_self?m[0]=eap\">EAP "._("Home")."</a>
&nbsp;|&nbsp;<a href=\"$php_self?m[0]=eap&m[1]=forms&m[2]=byID\">"._("By number")."</a>
&nbsp;|&nbsp;<a href=\"$php_self?m[0]=eap&m[1]=lists&m[2]=byEmployee\">"._("By employee")."</a>
&nbsp;|&nbsp;<a href=\"$php_self?m[0]=eap&m[1]=lists&m[2]=search\">"._("Search")."</a>
";

switch($m[1] ?? null){
case "forms":
	switch($m[2] ?? null){
	case "byID":
		$DEFAULT_TITLE .= _("\EAP by Number");
		$form = new HTML_QuickForm('frm', 'post');
		$form->addElement(	'hidden', 'm[0]', 'eap');
		$form->addElement(	'hidden', 'm[1]', 'view');
		$form->addElement(	'hidden', 'single', '1');
		$form->addElement(	'header', 'title', _("View EAP by Number"));
		$form->addElement(	'text', 'id', _('EAP#'));
		$form->addElement(	'submit', 'btnSubmit', _('Submit'));
		$body = $form->toHTML();
	break;
	}
break;
case "view":
	$eap = new tldEAP($id);
	if($eap->isEmpty()){
		$DEFAULT_ERROR[] = sprintf(_("ERROR: Could not find EAP#%s"),$id);
		break;
	}
	$header = $eap->getHeader();

	$DEFAULT_MENU .="
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href=\"$php_self?m[0]=eap&m[1]=view&id=$id\">"._("General")."</a>
";

	switch($m[2] ?? null){
	default:
		$form = new tldAssocTable(
			$header,
			array(
				"status"			=>_("Status"),
				"location"			=>_("Factory"),
				"dt_opened"			=>_("Date Opened"),
				"pn"				=>_("Top Part Number"),
				"overnight"			=>_("Overnight Processing Required?"),
				"reporter_fullname"	=>_("Reported By (from web)"),
				"reported_by"		=>_("Reported By (from shop)"),
				"t_emno"			=>_("Employee Number"),
				"poster_fullname"	=>_("Poster"),
				"short_desc"		=>_("Short Description"),
				"description"		=>_("Description"),
				"category"			=>_("Category"),
				"ifactor"			=>_("iFactor")
			),
			array("title"=>"EAP #$id")
		);
		$body .= $form->fetch();
		$status = $eap->getStatus();
		if($status=="NOTIFICATION" || $status=="CLOSED"){
			$fields = array("action_plan"=>_("Action Plan"), "currency"=>_("Currency"),	"cost"=>_("Cost"));
			$form = new tldAssocTable($header, $fields, array("title"=>_("Conclusion")));
			$body .= $form->fetch();
		}
        // Models
        $report = new tldReportColumnar(
            $eap->getModels(),
            array(
                "xItems"=>array(
                    "type"=>"Type",
                    "model"=>"Model"
                ),
                "title"=>"List of Affected Models"
            )
        );
        $body .= $report->fetch();
	break;
	}
break;
case "lists":
	switch($m[2] ?? null){
	case 'search':
		$DEFAULT_TITLE .= _("\Search EAP");
		$form = new HTML_QuickForm('frm', 'post');
		$form->addElement(	'hidden', 'm[0]', 'eap');
		$form->addElement(	'hidden', 'm[1]', 'lists');
		$form->addElement(	'hidden', 'm[2]', 'search');
		$form->addElement(	'header', 'title', _("Search EAP"));
		$form->addElement(	'text', 'target', _('Search for...'));
		$form->addElement(	'submit', 'btnSubmit', _('Submit'));
		$form->addRule('target','Required','required');
		if ($form->validate()){
			$form->freeze();
			$p =  tldUtils::cleanupFormInput($form->exportValues());
			$rows = tldEAP::search($p);
		}else{
			$body = $form->toHTML();
		}
	break;
	case "byEmployee":
		$DEFAULT_TITLE .= _("\EAP by Employee");
		$form = new HTML_QuickForm('frm', 'post');
		$form->addElement(	'hidden', 'm[0]', 'eap');
		$form->addElement(	'hidden', 'm[1]', 'lists');
		$form->addElement(	'hidden', 'm[2]', 'byEmployee');
		$form->addElement(	'hidden', 'single', '1');
		$form->addElement(	'header', 'title', _("View EAP by Employee"));
		$form->addElement(	'text', 'id', _('Employee#'));
		$form->addElement(	'submit', 'btnSubmit', _('Submit'));
		$form->addRule('id','Required','required');

		if ($form->validate()){
			$form->freeze();
			$p = $form->exportValues();
			$rows = tldEAP::byEmno($p['id'],
				array(
					'mode'=>'byOpenStatusERP',
					'erp'=>$LOCATION['erp']
				)
			);
			$myTitle = _("EAPs by Employee#");
		}else{
			$body = $form->toHTML();
		}
	break;
	case "byPartNumber":
	    $pn = tldDatabase::escape($_GET['pn']);
	    $rows = tldEAP::byOpenPartNumber($pn);
	    $myTitle = sprintf($pn,_("Open EAPs for Part Number %s"));
    break;
	case "inProgress":
		$rows = tldEAP::byERPStatus($LOCATION["location"], "IN PROGRESS");
		$myTitle = _("IN PROGRESS EAPs by Employee#");
	break;
	default:
		$rows = tldEAP::byERPStatus($LOCATION["location"], "PENDING");
		$myTitle = _("PENDING EAPs by Employee#");
	}
	if(count($rows ?? [])){
		$sess["ncr"]["list"] = $rows;
		$form = new tldReportMultiLevel($rows,
				array("location","t_emno"),
				array(
					"id"		=>_("EAP#"),
					"status"	=>_("Status"),
					"location"	=>_("Factory"),
					"dt_opened"	=>_("Date"),
					"short_desc"=>_("Description")
				),
				array("passField"=>"id",
					"title"=>$myTitle,
					"url"=>"$php_self?m[0]=eap&m[1]=view&id=")
		);
		$body .= $form->fetch();
	}
break;
default:
	$body = include("$PATH/homepage.eap.tpl.inc.php");
	$form = new tldReportColumnar(tldEAP::byQuery(array("status"=>"PENDING", "eap.factory"=>$LOCATION['id'])),
			array("xItems"=>array(
					"id"			=>_("EAP#"),
					"location"		=>_("BU"),
					"status"		=>_("Status"),
					"overnight"		=>_("OVERNIGHT"),
					"dt_opened"		=>_("Date"),
					"short_desc"	=>_("Short Description")
				),
				"title"=>_("PENDING EAPs"),
				"links"=>array("id"=>"$php_self?m[0]=eap&m[1]=view&id=")
				)
			);
	$body .= $form->fetch();
break;
}
