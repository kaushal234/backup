<?php
use Symfony\Component\HttpClient\Exception\ClientException;

include_once("sales_service.inc.php");

$sess["bom"] = [];
$sess["history"] = [];

if ('CH' === $LANG) {
	$LANG = 'zh';
}

$erp = $ERP;
$LANG = strtolower($LANG);

$DEFAULT_TITLE .= " \ CBOM";
$DEFAULT_MENU .="
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href=\"$php_self?m[0]=cbom\">"._("Home")." CBOM</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=cbom&m[1]=help\">"._("Help")."</a>
";

if(empty($sn)){
	$DEFAULT_ERROR[] = _("ERROR: No project number entered!")."<br/><a href=\"#\" onClick=\"javascript:history.back();\">"._("Click here to go to the previous page").'</a>';
}
if(empty($date)){
	$date = date('Y-m-d');
}

$dateTime = new \DateTime($date);
$dateTime->setTime(23, 59, 59);

switch($m[1] ?? null){
case 'view':
	$DEFAULT_MENU .="
<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href=\"$php_self?m[0]=cbom&m[1]=view&sn=$sn&date=$date\">CBOM#$sn</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=cbom&m[1]=view&m[2]=multi&sn=$sn&date=$date\">"._("Multi")."</a>&nbsp;|&nbsp;
";
//<a href=\"$php_self?m[0]=cbom&m[1]=view&m[2]=wo&sn=$sn&date=$date\">"._("Work Order")."</a>

	// Always display CBOM menu with icons to get schematics, instructions etc.
	$body = include("$PATH/homepage.cbom.view.tpl.inc.php");

	switch($m[2] ?? null){
		case 'pva':
		case 'wo':
			$body = "This page has not been migrated with LN and should not be displayed anymore.";
			break;
		case 'options':
			$query=<<<EOF
			SELECT id
			FROM service
			WHERE t_prno='{$sn}' AND
			man_location='{$LOCATION['business_unit']}'
	EOF;
			$er=tldUtils::getSqlRowToAssocArray($query);
			$eq = new tldEquipment($er['id']);
			$eqh = $eq->getHeader();
			$intCaty = tldList::optionsByListNameAsListItemListItem("list.sol.caty.int");
			$intOptions = tldSOROpts::byParent($eqh['sor_lid'],array("include"=>$intCaty));
			$report = new tldReportColumnar(
				$intOptions,
				array(
					"xItems"=>array(
						"caty"=>"Category",
						"dsca"=>"Description"
					),
					"title"=>"Internal options",
					"showItemNumbers"=>TRUE
				)
			);
			$body .= $report->fetch();
		break;
		case 'multi':
			try {
				$cbom = $client->request(
					'GET',
					sprintf('/ion/customized-bill-of-materials/multi_level_views/site=%d;project=%s', $erp, $sn),
					[
						'query' => [
							'date' => $dateTime->format(\DateTimeInterface::ATOM),
							'depth' => 20,
							'otherLanguage' => $LANG,
						]
					]
				)->toArray();
			} catch (ClientException $exception) {
				$DEFAULT_ERROR[] = _("ERROR: CBOM not found.");
				break;
			}

			$myCBOM = new tldCBOM($erp, $date, $sn, ['lang' => $LANG], $cbom['items']);
			$cbom = [];
			sortRows($myCBOM->itsBOMAsArray, $cbom);

			$body .= include("$PATH/view.mcbom.tpl.inc.php");
		break;
		case 'ai':
			try {
				$cbom = $client->request(
					'GET',
					sprintf('/ion/customized-bill-of-materials/chapters/site=%d;project=%s', $erp, $sn),
					[
						'query' => [
							'date' => $dateTime->format(\DateTimeInterface::ATOM),
							'signalCodeFilter' => implode('|', ['AIM', 'AIE', 'AIH', 'AIG']),
							'signalCodeFilterMethod' => 'Equals',
							'signalCodeAttribute' => 'engineeringSignalCode',
							'otherLanguage' => $LANG,
						]
					]
				)->toArray();
			} catch (ClientException $exception) {
				$DEFAULT_ERROR[] = _("ERROR: CBOM not found.");
				break;
			}

			$myCBOM = new tldCBOM($erp, $date, $sn, ['lang' => $LANG], $cbom['items']);
			$rows = $myCBOM->itsBOMAsArray;
			$DEFAULT_TITLE .= "\ "._("Assembly Instructions");
			$body .= include("$PATH/view.cbom.tpl.inc.php");
		break;
		case 'schematics':
			try {
				$cbom = $client->request(
					'GET',
					sprintf('/ion/customized-bill-of-materials/chapters/site=%d;project=%s', $erp, $sn),
					[
						'query' => [
							'date' => $dateTime->format(\DateTimeInterface::ATOM),
							'signalCodeFilter' => implode('|', ['ESC', 'HSC', 'PSC', 'BSC', 'FLD', 'RTD', 'PPD', 'PRG', 'PRM', 'GAD']),
							'signalCodeFilterMethod' => 'Equals',
							'signalCodeAttribute' => 'engineeringSignalCode',
							'otherLanguage' => $LANG,
						]
					]
				)->toArray();
			} catch (ClientException $exception) {
				$DEFAULT_ERROR[] = _("ERROR: CBOM not found.");
				break;
			}

			$myCBOM = new tldCBOM($erp, $date, $sn, ['lang' => $LANG], $cbom['items']);
			$rows = $myCBOM->itsBOMAsArray;
			$DEFAULT_TITLE .= "\ "._("Schematics");
			$body .= include("$PATH/view.cbom.tpl.inc.php");
		break;
		case 'chapters':
			try {
				$cbom = $client->request(
					'GET',
					sprintf('/ion/customized-bill-of-materials/chapters/site=%d;project=%s', $erp, $sn),
					[
						'query' => [
							'date' => $dateTime->format(\DateTimeInterface::ATOM),
							'signalCodeFilter' => implode('|', ['CH0', 'CH1', 'CH2', 'CH3', 'CH5']),
							'signalCodeFilterMethod' => 'Equals',
							'signalCodeAttribute' => 'engineeringSignalCode',
							'otherLanguage' => $LANG,
						]
					]
				)->toArray();
			} catch (ClientException $exception) {
				$DEFAULT_ERROR[] = _("ERROR: CBOM not found.");
				break;
			}

			$myCBOM = new tldCBOM($erp, $date, $sn, ['lang' => $LANG], $cbom['items']);
			$rows = $myCBOM->itsBOMAsArray;
			$DEFAULT_TITLE .= "\ "._("Chapters")." 0,1,2,3,5";
			$body .= include("$PATH/view.cbom.tpl.inc.php");
		break;
		case 'materialByCode':
			$form = new HTML_QuickForm('frmCode','post');
			$form->addElement(	'hidden', 'm[0]', 	'cbom');
			$form->addElement(	'hidden', 'm[1]', 	'view');
			$form->addElement(	'hidden', 'm[2]', 	'materialByCode');
			$form->addElement(	'hidden', 'sn', $sn);
			$form->addElement(	'header', 'title',	_('Get Material By Signal Code'));
			$form->addElement(	'text',   'code', 	_('Signal Code'));
			$form->addElement(	'submit', 'btn', 	_('Submit'));
			if ($form->validate()){
				$a = $form->exportValues();
				$signalcode = trim(strtoupper($a['code']));
				$title = sprintf(_("Signal code for CBOM %s from Company %s as of %s"),$sn,$ERP,$date);
				$body .= $form->toHTML();
				$xitems = [
					"t_pono" => "#",
					"t_sitm" => _("PN"),
					"t_csig_edm" => _("Code"),
					"t_dsca" => _("Description") . " (EN)",
					"altdsca" => sprintf(_("Description (%s)"), $LANG),
					"t_qana" => _("Qty"),
					"t_opno" => _("Operation"),
				];

				try {
					$cbom = $client->request(
						'GET',
						sprintf('/ion/manual_customized_bill_of_materials/site=%d;project=%s', $erp, $sn),
						[
							'query' => [
								'date' => $dateTime->format(\DateTimeInterface::ATOM),
								'signalCodeFilter' => $signalcode,
								'signalCodeFilterMethod' => 'Equals',
								'signalCodeAttribute' => 'engineeringSignalCode',
								'otherLanguage' => $LANG,
							]
						]
					)->toArray();
				} catch (ClientException $exception) {
					$DEFAULT_ERROR[] = _("ERROR: CBOM not found.");
					break;
				}

				$myCBOM = new tldCBOM($erp, $date, $sn, ['lang' => $LANG], $cbom['items']);
				$rows = $myCBOM->itsBOMAsArray;

				$report = new tldReportColumnar(
					$rows,
					[
						"xItems" => $xitems,
						"title" => $title,
						"links" => [
							"t_sitm" => "$php_self?m[0]=bom&m[1]=view&erp=$ERP&date=$date&pn="
						]
					]
				);
				$body .= $report->fetch();
			}else{
				$body .= $form->toHTML();
			}
		break;
		case 'docList':
			try {
				$cbom = $client->request(
					'GET',
					sprintf('/ion/customized-bill-of-materials/chapters/site=%d;project=%s', $erp, $sn),
					[
						'query' => [
							'date' => $dateTime->format(\DateTimeInterface::ATOM),
							'signalCodeFilter' => 'VMP',
							'signalCodeFilterMethod' => 'DoesNotEqual',
							'signalCodeAttribute' => 'engineeringSignalCode',
							'otherLanguage' => $LANG,
						]
					]
				)->toArray();
			} catch (ClientException $exception) {
				$DEFAULT_ERROR[] = _("ERROR: CBOM not found.");
				break;
			}

			$myCBOM = new tldCBOM($erp, $date, $sn, ['lang' => $LANG], $cbom['items']);
			$rows = $myCBOM->itsBOMAsArray;

			foreach ($rows as $key => $row) {
				if (empty(trim($row['engineeringSignalCode']))) {
					unset($rows[$key]);
				}
			}
			$DEFAULT_TITLE .= "\ "._("Document List");
			$body .= include("$PATH/view.cbom.tpl.inc.php");
		break;
		default:
			try {
				$cbom = $client->request(
					'GET',
					sprintf('/ion/customized-bill-of-materials/views/site=%d;project=%s', $erp, $sn),
					[
						'query' => [
							'date' => $dateTime->format(\DateTimeInterface::ATOM),
							'otherLanguage' => $LANG,
						]
					]
				)->toArray();
			} catch (ClientException $exception) {
				$DEFAULT_ERROR[] = _("ERROR: CBOM not found.");
				break;
			}

			$myCBOM = new tldCBOM($erp, $date, $sn, ['lang' => $LANG], $cbom['items']);

			if($myCBOM->isEmpty()){
				$DEFAULT_ERROR[] = sprintf(_("ERROR: No CBOM founded for '%s'"),$sn)."<br/><a href=\"#\" onClick=\"javascript:history.go(-1);\">"._("Click here to go to the previous page").'</a>';
				break;
			}
			$body .= _getCBOMReport($myCBOM->itsBOMAsArray, sprintf(_("CBOM for %s from Company %s as of %s"),$sn,$ERP,$date));
	break;
	}
break;
case 'help':
    switch($m[2] ?? null){
    default:
    case 't_csig':
        $report = new tldReportColumnar(
    		tldCBOM::getSignalCodes(),
    		array(
    			"xItems"=>array(
    		        "t_csig_edm"=>_("Code"),
    		        "t_dsca"=>_("Description")
    		    ),
        		"title"=>_("Code")
        	)
    	);
    	$body = $report->fetch();
    break;
    }
break;
default:
	$body = include("$PATH/homepage.cbom.tpl.inc.php");
	$form = new HTML_QuickForm('frmCBOM','get');
	$form->addElement(	'hidden', 'm[0]', 	'cbom');
	$form->addElement(	'hidden', 'm[1]', 	'view');
	$form->addElement(	'header', 'title',	_('Get Customized BOM'));
	$form->addElement(	'text',   'sn', 	_('Project Number'), array('onClick'=>'javscript:if(this.value!="")this.value=""'));
	$form->addElement(	'text',   'date', 	_('Date'));
	$form->addElement(	'advcheckbox', 'm[2]', NULL, _('Check here for Multi Level'), NULL,'multi');
	$form->addElement(	'submit', 'btn', 	_('Show CBOM'));
	$form->setDefaults(array("date"=>date("Y-m-d"),"sn"=>_("PROJECT NUMBER")));
	$body .= $form->toHTML();
break;
}

function sortRows(array &$rows, array &$result)
{
	foreach ($rows as $row) {
		$result[] = $row;

		if (!empty($row['children'])) {
			sortRows($row['children'], $result);
		}
	}
}

function _getCBOMReport($rows, $title){
	global $php_self, $ERP, $date, $LANG;
	foreach ($rows as $key => $row) {
		if (stripos($row['t_dsca'], 'option') !== false || strpos($row['t_dsca'], 'OPT') === 0 ) {
			$rows[$key]['t_dsca'] = "<p><font color='blue'>{$row['t_dsca']}</font></p>";
		}
		// "&revision=' . $row['t_revi']" is included only to invalidate the browser cache when the drawing revision changes.
		$rows[$key]['drawing'] = '<a target="_blank" href="'.$php_self.'?m[0]=getfile&m[1]=drawing&item='.$row['t_sitm'].'&revision=' . $row['t_revi'].'">
<img src="/shared/bluesphere/16x16/actions/filesaveas.png" alt="Save file to your hard disk">
</a>';
	}
	if($LANG<>"EN"){
	    $xitems=array(
	    	"t_pono"	=>"#",
    		"t_sitm"	=>_("PN"),
    		"t_csig_edm"	=>_("Code"),
    		"t_dsca"	=>_("Description")." (EN)",
    		"altdsca"	=> sprintf(_("Description (%s)"),$LANG),
    		"t_qana"	=>_("Qty"),
    		"t_opno"	=>_("Operation"),
    		"drawing"	=>_("Drawing"),
		);
	}
	else{
	    $xitems=array(
	    	"t_pono"	=>"#",
	    	"t_sitm"	=>_("PN"),
	    	"t_csig_edm"	=>_("Code"),
	    	"t_dsca"	=>_("Description"),
	    	"t_qana"	=>_("Qty"),
	    	"t_opno"	=>_("Operation"),
			"drawing"	=>_("Drawing"),
		);
	}
	foreach ($rows as $key => $row) {
		$rows[$key]['altdsca'] = ($ERP >= '600' && $row['altdsca'] !== null) ? mb_convert_encoding($row['altdsca'], 'HTML-ENTITIES', 'UTF-8') : $row['altdsca'];
	}

	$report = new tldReportColumnar(
		$rows,
		array(
			"xItems"=>$xitems,
    		"title"=>$title,
    		"links"=>array(
    			"t_sitm"=>"$php_self?m[0]=bom&m[1]=view&erp=$ERP&date=$date&pn="
			)
    	)
	);
	return $report->fetch();
}

?>
