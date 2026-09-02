<?php
if(!$user->isInGroup('gg_MIS')){
	echo "You do not have access to this module";
	tldUtils::log_event($GLOBALS['PHP_AUTH_USER'].' blocked, MIS Inventory module');
	exit;
}
include_once("publications.inc.php");
include_once("forms_and_reports.inc.php");
define('FPDF_FONTPATH','fpdf/font/');


$DEFAULT_TITLE .= '\Inventory';
$DEFAULT_MENU .=<<<EOF
	<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="$php_self?m[0]=inv">Home</a>
	&nbsp;|&nbsp;<a href="$php_self?m[0]=inv&m[1]=reports">Reports</a>
	&nbsp;|&nbsp;<a href="/en/private/mis/inv/mis_inv_admin.php">Maintain Inv</a>
EOF;
$icons = array(
			"REGION"		=>"/shared/icons/tld-icon.jpg",
			"LOCATION"		=>"/shared/icons/network/32x32/location.jpg",
			"ROOM"			=>"/shared/icons/network/32x32/room.jpg",
			"DEPT"			=>"/shared/icons/network/32x32/dept.jpg",
			"FAX"			=>"/shared/icons/network/32x32/fax.jpg",
			"DESKTOP"		=>"/shared/icons/network/32x32/desktop.jpg",
			"SERVER"		=>"/shared/icons/network/32x32/server.jpg",
			"LAPTOP"		=>"/shared/icons/network/32x32/laptop.jpg",
			"SOFTWARE"		=>"/shared/icons/network/32x32/software.jpg",
			"HARDWARE"		=>"/shared/icons/network/32x32/hardware.jpg",
			"ACCESSORIES"	=>"/shared/icons/network/32x32/accessories.jpg",
			"PDA"			=>"/shared/icons/network/32x32/pda.jpg",
			"PRINTER"		=>"/shared/icons/network/32x32/printer.jpg",
			"PERSONNEL"		=>"/shared/icons/network/32x32/personnel.jpg",
			"NETWORK"		=>"/shared/icons/network/32x32/network.jpg",
			"SCANNER"		=>"/shared/icons/network/32x32/scanner.jpg",
			"PROJECTOR"		=>"/shared/icons/network/32x32/projector.jpg",
			"MONITOR"		=>"/shared/icons/network/32x32/monitor.jpg",
			"DIGITAL CAM"	=>"/shared/icons/network/32x32/digital_cam.jpg"
		);

switch($m[1]){
case 'forms':
	switch($m[2]){
	//add multple lines at once
	case 'addMulti':
		if(empty($id)){
			$smarty->assign("error", "ERROR: no parent id set");
			break;
		}
		switch($m[3]){
		case '1':
			$inv = new tldMISInv($id);
			$header = $inv->getHeader();
			$DEFAULT_TITLE .= "\Add multiple lines Step#1";
			$form = new HTML_QuickForm('frmAddMulti1', 'post');
			$form->addElement(	'hidden', 'm[0]', 'inv');
			$form->addElement(	'hidden', 'm[1]', 'forms');
			$form->addElement(	'hidden', 'm[2]', 'addMulti');
			$form->addElement(	'hidden', 'm[3]', '2');
			$form->addElement(	'hidden', 'id', $id);
			$form->addElement(	'header', 'title', "Add multiple lines to ID# $id ".$header['serial']);
			$form->addElement(	'text', 'qty', 'Quantity');
			$form->addElement(	'submit', 'btnSubmit', 'Submit');

			$form->addRule('num', 'Required', 'required','','client');
			$form->setDefaults(array("qty"=>1));
			$body .= $form->toHTML();
		break;
		case '2':
			$DEFAULT_TITLE .= "\Add multiple lines Step#2";
			$form = new HTML_QuickForm('frmAddMulti2', 'post');
			$form->addElement(	'hidden', 'm[0]', 'inv');
			$form->addElement(	'hidden', 'm[1]', 'forms');
			$form->addElement(	'hidden', 'm[2]', 'addMulti');
			$form->addElement(	'hidden', 'm[3]', '2');
			$form->addElement(	'hidden', 'id', $id);
			$form->addElement(	'hidden', 'parent_id', $id);
			$form->addElement(	'hidden', 'qty', $qty);
			$form->addElement(	'header', 'title', "Add multiple lines to ID# $id ".$header['serial']);
			$form->addElement(	'select',"type", 'Type',
								array(""=>"") + tldList::optionsByListNameAsListItemListItem('list.mis.inv.type'));
			$form->addElement(	'select',"make", 'Make',
								array(""=>"") + tldMISInv::getMakes());
			$form->addElement(	'text', 'model', 'Model');
			$form->addElement(	'text', 'description', 'Description');
			for($i=0; $i < $qty; $i++){
    			$form->addElement(	'header', 'title', "Line# ".($i+1));
				// Getting input to assign manufacturer serial number
				$form->addElement(	'text', "sns[$i]", 'Manufacturer SN #'.($i+1));
				// Getting input to assign TLD serial number
				$form->addElement(	'text', "tldsns[$i]", 'TLD SN #'.($i+1));
    			$form->addRule('tldsns[$i]', 'Required', 'required','','client');
			}
			$form->addElement(	'submit', 'btnSubmit', 'Submit');
			$form->addRule('type', 'Required', 'required','','client');
			$form->addRule('make', 'Required', 'required','','client');
			$form->addRule('model', 'Required', 'required','','client');
			if ($form->validate()){
				# If the form validates then freeze the data
				$form->freeze();
				$vars = $form->exportValues();
                foreach($vars['sns'] as $i=>$mansn){
					if($mansn){
						$p = $vars;
						$p['man_serial'] = $mansn;
						$p['serial'] = $vars['tldsns'][$i];
						tldMISInv::insert($p);
					}
				}
				$body .=<<<EOF
	<a href="$php_self?m[0]=inv&m[1]=browse&id=$id">Back to Parent...</a>
EOF;
			}else{
				$body .= $form->toHTML();
			}
		break;
		}
	break;
	}
break;
case 'tree':
	switch($m[2]){
	case 'myTree':
		$rows = tldMISInv::byQuery(array("man_serial"=>$user->getEmail()));
		if(count($rows)==1){
			$id = $rows[0]['id'];
		}
	break;
	case 'byUser':
		$rows = tldMISInv::byQuery(array("man_serial"=>$email));
		if(count($rows)==1){
			$id = $rows[0]['id'];
		}
	break;
	}

	if($id){
		$inv = new tldMISInv($id);
		$smarty->assign("header", $inv->getHeader());
		$smarty->assign("list", $inv->getTree());
		$smarty->assign("icons", $icons);
		$body .= $smarty->fetch("$PATH/inv/tree.tpl");
	}
break;
case 'browse':
	if(empty($id)) $id=0;
	$inv = new tldMISInv($id);
	$header = $inv->getHeader();
	if(!empty($id)){
	$DEFAULT_MENU.=<<<EOF
	<br>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="$php_self?m[0]=inv&m[1]=browse&id=${header["parent_id"]}">UP</a>
EOF;
	if($tree){
	$DEFAULT_MENU.=<<<EOF
	&nbsp;|&nbsp;<a href="$php_self?m[0]=inv&m[1]=browse&id=$id">Collapse</a>
EOF;
	}else{
	$DEFAULT_MENU.=<<<EOF
	&nbsp;|&nbsp;<a href="$php_self?m[0]=inv&m[1]=browse&id=$id&tree=1">Expand</a>
EOF;
	}
	$DEFAULT_MENU.=<<<EOF
	&nbsp;|&nbsp;<a href="$php_self?m[0]=inv&m[1]=browse&m[2]=label&id=$id">Label</a>
	&nbsp;|&nbsp;<a href="$php_self?m[0]=inv&m[1]=browse&m[2]=childLabels&tree=$tree&id=$id">Child Labels</a>
	&nbsp;|&nbsp;<a href="/en/private/mis/mis.php?m[0]=inventory&m[1]=addHardware">Add Children</a>
	&nbsp;|&nbsp;<a href="/en/private/mis/inv/mis_inv_admin.php?mode=record_view&form_type=main_tpl&id=$id">Edit</a>
EOF;
}
	switch($m[2]){
	case 'label':
		if(!$inv->isEmpty()){
			$inv->outPDFLabel();
			exit;
		}

        $smarty->assign("error", "No id TLD MIS INV id set");
        break;
	case 'childLabels':
		if(!$inv->isEmpty()){
			if($tree){
				tldMISInv::outPDFLabel($inv->getTree());
			}else{
				tldMISInv::outPDFLabel($inv->getChildren());
			}
			exit;
		}else{
			$smarty->assign("error", "No id TLD MIS INV id set");
		}
	break;
	default:
		$smarty->assign("header", $header);
		if($tree){
			$smarty->assign("list", $inv->getTree());
		}else{
			$smarty->assign("list", $inv->getChildren());
		}
		$smarty->assign("icons", $icons);
		$body .= $smarty->fetch("$PATH/inv/tree.tpl");
	}
break;
case 'reports':
	switch($m[2]){
	case 'statsByType':
		$form = new tldReportColumnar(tldMISInv::getStatsByType(),
			array("xItems"=>array(	"type"	=>"Type",
					"num"	=>"Count"
					),
			"title"=>"Count by Type"
				)
			);
		$body .= $form->fetch();
	break;
	case 'byDomainType':
		$_DOMAINS = array("9711"=>"TLD-America.com","9713"=>"TLD-Asia.com","9712"=>"TLD-Europe.com");
		$form = new HTML_QuickForm('frm', 'get');
		$form->addElement(	'header', 'title', 'Inventory by Domain and Type');
		$form->addElement(	'hidden', 'm[0]', 'inv');
		$form->addElement(	'hidden', 'm[1]', 'reports');
		$form->addElement(	'hidden', 'm[2]', 'byDomainType');
		$form->addElement(	'select', 'domain', 'Domain',
							array(""=>"") + $_DOMAINS
							);
		$form->addElement(	'select', 'type', 'Type',
							array(""=>"") + tldList::optionsByListNameAsListItemListItem('list.mis.inv.type')
							);
		$form->addElement(	'submit', 'btnSubmit', 'Submit');
		//set rules
//		$form->addRule("type","Type required","required");
		$form->addRule("domain","Domain required","required");

		if ($form->validate()){
			$inv = new tldMISInv($domain);
			$tree = $inv->getList();
			//if type is selected then filter
			if($type){
				foreach($tree as $node){
					if($node['type']==$type) $result[]=$node;
				}
			}else{
				$result = $tree;
			}
			if(count($result)){
				$DEFAULT_MENU.=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=inv&m[1]=reports&m[2]=byDomainType&multiLabel=1&domain=$domain&type=$type">Print Labels</a>
EOF;
				if($multiLabel){
					tldMISInv::outPDFLabel($result);
					exit;
				}else{
					$form = new tldReportColumnar($result,
							array("xItems"=>array(
									"id"=>"ID#",
									"parent"=>"destination",
									"parent_id"=>"parent_id",
									"date"=>"date",
									"d_wrty"=>"d_wrty",
									"qty"=>"qty",
									"description"=>"description",
                                    "make"=>"Make",
                                    "model"=>"Model",
									"serial"=>"serial",
                                    "man_serial"=>"man_serial",
                                    "type"	=>"Type"
									),
							"title"=>"Inventory ${_DOMAINS[$domain]} - $type",
							"links"=>array("id"=>"$php_self?m[0]=inv&m[1]=browse&id=")
								)
							);
					$body .= $form->fetch();
				}
			}
		}else{
			$body .= $form->toHTML();
		}
	break;
	default:
		$body .= $smarty->fetch("$PATH/inv/reports/homepage.reports.tpl");
	}
break;
default:
	$body .= $smarty->fetch("$PATH/inv/homepage.inv.tpl");
}
?>
