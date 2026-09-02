<?php

use App\Manager\FileManager;
use Symfony\Component\HttpClient\Exception\ClientException;

include_once("quality.inc.php");
include_once("sales_service.inc.php");
require_once('HTML/QuickForm/advmultiselect.php');
$DEFAULT_TITLE .= "\CRAB";

/** @var \App\Client\ApiClient $client */
switch($m[1] ?? null){
case 'crabfromsol':
    $list = tldSOL::ERBySSOStatus($LOCATION['location']);
    $sols =[];
    foreach ($list as $sol){
        if($sol['qty']!=0){
            $sols[$sol['id']] = $sol['id'];
        }
    }

    $form = new HTML_QuickForm('frmNewCRAB', 'post');
    $form->addElement(	'hidden', 'm[0]', 'crab');
    $form->addElement(	'hidden', 'm[1]', $m[1]);
    $form->addElement(	'header', 'title', _('Submit New CRAB For ER from SOL'));
    $ams =& $form->addElement('advmultiselect', 'sols', null,
        $sols, array('size'=>15, 'class'=>'pool', 'style'=>'width:280px;')
    );
    $ams->setLabel(array('Affected SOLs', 'SOL in progress', 'Affected'));
    $ams->setButtonAttributes('add', array('value'=>'-->>', 'class'=>'inputCommand'));
    $ams->setButtonAttributes('remove', array('value'=>'<<--', 'class'=>'inputCommand'));
    $form->addElement(	'submit', 'btnSubmit', 'Submit');
    $form->setDefaults($default ?? []);
    // Add rule
    $form->addRule('models', 'Required', 'required');
    if($form->validate()){
        $var = tldUtils::cleanupFormInput($form->exportValues());
        $ers = [];
        foreach ($var['sols'] ?? [] AS $key => $id){
            $ers[] = tldSORUnit::byParentByStatus($id);
        }
        $er_list = [];
        foreach ($ers AS $v => $k){
            foreach($k AS $id => $er){
                $er_list[$er['sn']] = $er['id'];
            }
        }
        $crab_codes = tldList::optionsByListNameAsListKeyListItem("list.qa.crab.code","list_key");
        unset($crab_codes["999"]);
        foreach($crab_codes as $crab_code=>$crab_desc){
            $crab_code_list[$crab_code] = $crab_desc;
        }
        ksort($crab_code_list, SORT_NUMERIC);
        foreach($crab_code_list AS $k => &$v) {
            $v = "$k, $v";
        }
        $crab_depts=tldList::optionsByListNameAsListItemListItem("list.qa.crab.dept");
        foreach($crab_depts as $key=>$value){
            $crab_dept_list[$key] = $value;
        }
        asort($crab_dept_list);
        $stage = [
            "Assy"         => "Assembly",
            "Test"         => "Test",
            "PDI"          => "PDI",
            "PDI-CSC"      => "PDI Customer Scope Change",
            "PDI-SOL"      => "PDI SOL Incomplete",
            "PDI-INTERN" => "PDI - Internal",
        ];

        $form = new HTML_QuickForm('frmNewCRAB', 'post');
        $form->addElement(	'hidden', 'm[0]', 'crab');
        $form->addElement(	'hidden', 'm[1]', $m[1]);
        if(isset($var['sols'])) {
            $form->addElement('hidden', 'sn', $var['sols']);
        }
        $form->addElement(	'hidden', 'buid', $LOCATION['id']);
        $form->addElement(	'header', 'title', _('Step 2 - Submit New CRAB For SOL'));
        $form->addElement(	'select', 'opno', _('Stage'), $stage);
        $form->addElement(	'select', 'dept',_('Department'), $crab_dept_list);
        $form->addElement(	'header', 'title', _('Fill NCR# field for External CRAB'));
        $form->addElement(	'text', 'ncrid', _('NCR#'));
        $form->addElement(	'text', 'pn', _('PN'));
        $form->addElement(	'select', 'code', _('CRAB Code'), ["" => ""]+$crab_code_list);
        $form->addElement(	'textarea', 'dsca', _('Full Description') . '<br>' . _('and Corrective Action'),
            array("wrap"=>"VIRTUAL","cols"=>"30","rows"=>"5")
        );
//     		Get multiple models
        $ams =& $form->addElement('advmultiselect', 'models', null,
            $er_list, array('size'=>15, 'class'=>'pool', 'style'=>'width:280px;')
        );
        $ams->setLabel(array('Models Affected', 'SN->Type->Model', 'Affected'));
        $ams->setButtonAttributes('add', array('value'=>'-->>', 'class'=>'inputCommand'));
        $ams->setButtonAttributes('remove', array('value'=>'<<--', 'class'=>'inputCommand'));
        $form->addElement(  'header', 'fileInfo', _('Upload a Photo'));
        $form->addElement(	'file', 'main_file', _('Main File'));
        $form->addElement(	'textarea', 'file_description', _('Photo Description'),
            array("wrap"=>"VIRTUAL", "cols"=>"40", "rows"=>"8"));
        for ($i = 1; $i < 4; $i++) {
            $form->addElement('file', "file[$i]", _('File') . " $i");
            $form->addElement(	'textarea', "file_description[$i]", _('Photo Description'),
                array("wrap"=>"VIRTUAL", "cols"=>"40", "rows"=>"8"));
        }
        $form->addElement(	'submit', 'btnSubmit', 'Submit');
        $form->setDefaults($default ?? []);
        // Add rule
        $form->addRule('models', 'Required', 'required');
        $form->addRule('code', 'Required', 'required');
        $form->addRule('dsca', 'Required', 'required');


        if($form->validate()) {
            $var = tldUtils::cleanupFormInput($form->exportValues());
            $crabData = [];
            $part = null;
            if (!empty($var['pn'])) {
                try {
                    $item = $client->get(sprintf('ion/item_monologistics/%s',  $var['pn']));
                    $part = [
                        'partNumber' => $var['pn'],
                        'description' => trim(TldDatabase::escape($item['description'])),
                        'unitOfMeasure' => trim(TldDatabase::escape($item['unitOfMeasure'])),
                    ];
                } catch (ClientException $e) {
                    $DEFAULT_ERROR[] = "ERROR: ".$translator->trans('crab.errors.part_not_found', ['%part%' => $var['pn']], 'crab');
                    break;
                }
            }
            if (count($var['models']) > 0) {
                foreach ($var['models'] as $key => $model) {
                    if ($model) {
                        $val = explode("->", $model);
                        $var['erid'] = trim(str_replace('T', '', $val[0]));
                        if ($var['code'] < 100) {
                            $var['code'] = substr($var['code'], 0);
                        }

                        try {
                            $equipmentRecord = $client->findOneBy('/equipment_records', ['legacyId' => $var['erid']]);
                        } catch (Exception $exception) {
                            $DEFAULT_ERROR[] = "_(ERROR: Failed to find equipment record)";
                            break;
                        }

                        try {
                            $department = $client->findOneBy('/quality/crab_departments', ['name' => $var['dept']]);
                        } catch (Exception $exception) {
                            $DEFAULT_ERROR[] = "_(ERROR: Failed to find department)";
                            break;
                        }

                        $nonConformity = null;
                        if (!empty($var['ncrid'])){
                            try {
                                $nonConformity = $client->find('/quality/non_conformities', (int) $var['ncrid']);
                            } catch (ClientException $exception) {
                                $DEFAULT_ERROR[] = "_(ERROR: Failed to find non-conformity)";
                            }
                        }

                        try {
                            $crabCode = $client->findOneBy('/quality/crab_codes', ['code' => (int) $var['code']]);
                        } catch (Exception $exception) {
                            $DEFAULT_ERROR[] = "_(ERROR: Failed to find CRAB code)";
                            break;
                        }

                        $crabData = [
                            'equipmentRecord' => $equipmentRecord['@id'],
                            'description' => mb_convert_encoding($var['dsca'], 'UTF-8', $charset === 'GB2312' ? $charset : 'ISO-8859-1'),
                            'category' => $var['opno'],
                            'department' => $department['@id'],
                            'code' => $crabCode['@id'],
                            'nonConformity' => $nonConformity['@id'],
                            'part' => $part,
                        ];

                        try{
                            $newCrab = $client->save('/quality/crabs', $crabData);
                        } catch (ClientException $exception) {
                            $DEFAULT_ERROR[] = sprintf("ERROR: Fail to save CRAB. %s", $exception->getMessage());
                            break;
                        }

                        //main file
                        $mainFile = $form->getElement('main_file')->getValue();
                        if (!empty($mainFile['tmp_name'])) {
                            $mainFileManager = new FileManager($client);
                            try {
                                $mainFileManager->uploadFile($mainFile, $newCrab, 'main_file');
                            } catch (ClientException $exception) {
                                $DEFAULT_ERROR[] = sprintf("Could not attach file CRAB. Reason: %s", $exception->getMessage());
                                break;
                            }
                        }

                        //files
                        $fileManager = new FileManager($client);
                        for ($i = 1; $i < 4; $i++) {
                            try {
                                $file = $form->getElement("file[$i]");
                                $file_array = $file->getValue();
                                // Check if file uploaded
                                if (empty($file_array['tmp_name'])) {
                                    continue;
                                }
                                $fileManager->uploadFile($file_array, $newCrab);
                            } catch (ClientException $exception) {
                                $DEFAULT_ERROR[] = sprintf("Could not attach file to CRAB. Reason: %s", $exception->getMessage());
                                break;
                            }
                        }
                    }
                    $body = "<a href=\"$php_self?m[0]=crab&m[1]=view&id={$newCrab['legacyId']}\">" . sprintf("View CRAB#%s", $newCrab['legacyId']) . "...</a><br/>";
                }
            }
        }else{
            $body = $form->toHTML();
        }
    }else{
        $body = $form->toHTML();
    }
break;
case 'newCRAB1':
    switch($m[2] ?? null){
        case 'transfer':
            $module = strtoupper($module);
            switch($module){
                case 'NCR':
                    $ncr_id = TldDatabase::escape($ncr_id);
                    $pn = TldDatabase::escape($pn);
                    if(empty($ncr_id) || empty($pn) || !is_numeric($ncr_id)){
                        $DEFAULT_ERROR[]=  "ERROR: Fail to initiate transfer: data is missing or invalid";
                        break;
                    }
                    try {
                        /** @var \App\Client\ApiClient $client */
                        $ncr = $client->find('/quality/non_conformities', $ncr_id, ['query' => ['normalizationGroups' => ['non_conformity:legacy', 'expose_legacy']]]);
                    } catch (ClientException $exception) {
                        $DEFAULT_ERROR[]="ERROR: No NCR #$id found...";
                        break;
                    }
                    $default = array(
                        "dsca"=>$ncr['problem'],
                        "ncrid"=>$ncr['id'],
                        "init_emno"=>$ncr['reporterId'],
                    );
                    $buid = $ncr['location']['legacyId'];
                    break;
            }
            break;
    }
    if ($buid) {
        $erp = tldLocation::getERPByID($buid);
    }
    if (isset($sn)) {
        $rets[0]['clot'] = 'T' . $sn;
    }
    if (isset($pn)) {
        $rows = tldUtils::getSqlToAssocArray("EXEC Search_Tool_WO '{$pn}','All','{$erp}'", "odbc", ["src" => "baan"]);
        if (!count($rows)) {
            $DEFAULT_ERROR[] = "ERROR: No unshipped equipment with PN $pn under ERP#{$erp}";
            break;
        }
        $query = "select t_clot from ttdltc001$erp where t_cprj in (select t_cprj from ttimrp010$erp where t_koor=8 and t_item='$pn') ";
        $rets = tldUtils::getSqlToAssocArray($query, "odbc", ["src" => "baan"]);
        foreach ($rows as $row) {
            $lists[] = tldEquipment::byUnshippedBySN(trim($row['clot']));
        }
    }
    foreach ((array) $rets as $ret) {
        $lists[] = tldEquipment::byUnshippedBySN(trim($ret['clot']));
    }

    $list = [];
    foreach ($lists as $i=>$j){
        foreach ($j as $k=>$v){
            $list[$v['equipment']] = $v['equipment'];
        }
    }
    if(!count($list)){
        $DEFAULT_ERROR[]=  "ERROR: No unshipped equipment with PN $pn under ERP#{$erp}";
        break;
    }

    $crab_codes = tldList::optionsByListNameAsListKeyListItem("list.qa.crab.code","list_key");
    unset($crab_codes["999"]);
    foreach($crab_codes as $crab_code=>$crab_desc){
        $crab_code_list[$crab_code] = $crab_desc;
    }
    ksort($crab_code_list, SORT_NUMERIC);
    foreach($crab_code_list AS $k => &$v) {
        $v = "$k, $v";
    }
    $crab_depts=tldList::optionsByListNameAsListItemListItem("list.qa.crab.dept");
    foreach($crab_depts as $key=>$value){
        $crab_dept_list[$key] = $value;
    }
    asort($crab_dept_list);
    $stage = [
        "Assy"         => "Assembly",
        "Test"         => "Test",
        "PDI"          => "PDI",
        "PDI-CSC"      => "PDI Customer Scope Change",
        "PDI-SOL"      => "PDI SOL Incomplete",
        "PDI-INTERN" => "PDI - Internal",
    ];

    $form = new HTML_QuickForm('frmNewCRAB', 'post');
    $form->addElement(	'hidden', 'm[0]', 'crab');
    $form->addElement(	'hidden', 'm[1]', $m[1]);
    if(isset($sn)) {
        $form->addElement('hidden', 'sn', $sn);
    }
    if(isset($pn)) {
        $form->addElement('hidden', 'pn', $pn);
    }
    $form->addElement(	'hidden', 'buid', $buid);
    $form->addElement(	'header', 'title', sprintf(_('Step 2 - Submit New CRAB For PN %s'), $pn ?: ''));
    $form->addElement(	'select', 'opno', _('Stage'), $stage);
    $form->addElement(	'select', 'dept',_('Department'), $crab_dept_list);
    $form->addElement(	'header', 'title', _('Fill NCR# field for External CRAB'));
    $form->addElement(	'text', 'ncrid', _('NCR#'));
    $form->addElement(	'select', 'code', _('CRAB Code'), ["" => ""]+$crab_code_list);
    $form->addElement(	'textarea', 'dsca', _('Full Description') . '<br>' . _('and Corrective Action'),
        array("wrap"=>"VIRTUAL","cols"=>"30","rows"=>"5")
    );
//     		Get multiple models
    $ams =& $form->addElement('advmultiselect', 'models', null,
        $list, array('size'=>15, 'class'=>'pool', 'style'=>'width:280px;')
    );
    $ams->setLabel(array('Models Affected', 'SN->Type->Model', 'Affected'));
    $ams->setButtonAttributes('add', array('value'=>'-->>', 'class'=>'inputCommand'));
    $ams->setButtonAttributes('remove', array('value'=>'<<--', 'class'=>'inputCommand'));
    $form->addElement(  'header', 'fileInfo', _('Upload a Photo'));
    $form->addElement(	'file', 'file', _('Attachment #1'));
    $form->addElement(	'textarea', 'file_description', _('Photo Description'),
        array("wrap"=>"VIRTUAL", "cols"=>"40", "rows"=>"8"));
    $form->addElement(	'file', 'file2', _('Attachment #2'));
    $form->addElement(	'textarea', 'file_description2', _('Photo Description'),
        array("wrap"=>"VIRTUAL", "cols"=>"40", "rows"=>"8"));
    $form->addElement(	'file', 'file3', _('Attachment #3'));
    $form->addElement(	'textarea', 'file_description3', _('Photo Description'),
        array("wrap"=>"VIRTUAL", "cols"=>"40", "rows"=>"8"));
    $form->addElement(	'submit', 'btnSubmit', 'Submit');
    $form->setDefaults($default ?? '');
    // Add rule
    $form->addRule('models', 'Required', 'required');
    $form->addRule('code', 'Required', 'required');

    if($form->validate()){
        $var = tldUtils::cleanupFormInput($form->exportValues());
        if(count($var['models']) > 0){
            foreach($var['models'] as $key=>$model){
                if($model){
                    $var['init_emno'] = $_SESSION['pi_user_id'];
                    $var['entered_by'] = $_SESSION['pi_user_id'];
                    $val = explode("->",$model);
                    $var['erid'] = trim(str_replace('T','',$val[0]));
                    $e = tldCRAB::insert($var);
                    $er = new tldEquipment($var['erid']);
                    if(is_numeric($e)){
                        $DEFAULT_ERROR[] = sprintf(_("CRAB#%s successfully created..."),$e);
                        $crab = new tldCRAB($e);
                        $file = $form->getElement("file");
                        $file_array = $file->getValue();
                        if ($file_array['tmp_name'] != "") {
                            // Insert mod File
                            $vars = [
                                "parent_id" => $e,
                                "module" => "CRAB",
                                "description" => $a["file_description"],
                                "level" => 1
                            ];
                            $fe = tldModFile::insert($vars, $file_array);
                            if (is_string($fe)) {
                                $DEFAULT_ERROR[] =sprintf(_("ERROR: There was a problem attaching the file. Reason:%s"), $fe);
                            }
                        }
                        // File 2
                        $file2 = $form->getElement("file2");
                        $file_array2 = $file2->getValue();
                        if ($file_array2['tmp_name'] != "") {
                            // Insert mod File
                            $vars = [
                                "parent_id" => $e,
                                "module" => "CRAB",
                                "description" => $a["file_description2"],
                                "level" => 1
                            ];
                            $fe2 = tldModFile::insert($vars, $file_array2);
                            if (is_string($fe2)) {
                                $DEFAULT_ERROR[] = sprintf(_("ERROR: There was a problem attaching the file #2. Reason:%s"), $fe2);
                            }
                        }
                        // File 3
                        $file3 = $form->getElement("file3");
                        $file_array3 = $file3->getValue();
                        if ($file_array3['tmp_name'] != "") {
                            // Insert mod File
                            $vars = [
                                "parent_id" => $e,
                                "module" => "CRAB",
                                "description" => $a["file_description3"],
                                "level" => 1
                            ];
                            $fe3 = tldModFile::insert($vars, $file_array3);
                            if (is_string($fe3)) {
                                $DEFAULT_ERROR[] = sprintf(_("ERROR: There was a problem attaching the file #3. Reason:%s"),$fe3);
                            }
                        }
                        // Cleanup
                        unset($default);
                        if(!empty($var['ncrid'])){
                            $error = tldModLink::insert("CRAB", $e, "NCR", $var['ncrid']);
                            if(is_string($error)){
                                $DEFAULT_ERROR[] = "ERROR: There was an error adding the new link to NCR#{$var['ncrid']}. Reason: $error";
                            }
                        }
                        if($er->itsDetails['dyt']<$er->itsDetails['dgt_act'] && $er->itsDetails['dgt_act']<>'0000-00-00'){
                            $dyt = time();
                            if($dyt < strtotime($er->itsDetails['dgt_act'])){
                                $dyt = strtotime($er->itsDetails['dgt_act']) + 86400;
                            }
                            $dyt = date('Y-m-d',$dyt);
                            $e2 = $er->updateRecord(array('dyt'=>$dyt,'dgt_act'=>'0000-00-00'),array('dyt','dgt_act'));
                            if(!is_string($e2)){
                                $DEFAULT_ERROR[] = sprintf("Unit (SN#%s) has been changed to YELLOW-TAG", $er->getSN());
                                // Add log
                                $MSG = "AUTO UPDATE (CRAB#$e) of YT Date from {$er->itsDetails['dyt']} to $dyt";
                                $log = $er->addLogEntry(0,TldDatabase::escape($MSG));
                                // Notify
                                $crab->notifyYT();
                            }
                        }
                        if ($er->isCombinationPreAssembly()) {
                            $combinationer = new tldEquipment($er->getParentID());
                            if ($combinationer->itsDetails['dgt_act'] !== '0000-00-00' && $combinationer->itsDetails['dyt'] <= $combinationer->itsDetails['dgt_act']) {
                                $dyt = time();
                                if ($dyt < strtotime($combinationer->itsDetails['dgt_act'])) {
                                    $dyt = strtotime($combinationer->itsDetails['dgt_act']) + 86400;
                                }
                                $dyt = date('Y-m-d', $dyt);
                                $e2 = $combinationer->updateRecord(['dyt' => $dyt, 'dgt_act' => '0000-00-00'], ['dyt', 'dgt_act']);
                                if (!is_string($e2)) {
                                    $DEFAULT_ERROR[] = sprintf('Unit (SN#%s) has been changed to YELLOW-TAG', $combinationer->getSN());
                                    // Add log
                                    $MSG = "AUTO UPDATE (CRAB#$e) of YT Date from {$combinationer->itsDetails['dyt']} to $dyt";
                                    $log = $combinationer->addLogEntry(0, TldDatabase::escape($MSG));
                                }
                            }
                        }
                    }else{
                        $DEFAULT_ERROR[] = sprintf(_("ERROR: problem creating CRAB, error was %s"),$e);
                    }
                }
                $body="<a href=\"$php_self?m[0]=crab&m[1]=view&id=$e\">".sprintf("View CRAB#%s",$e)."...</a>";
            }
        }
    }else{
        $body = $form->toHTML();
    }
    break;
case 'new':
    if(!empty($sn)){
        $sess['er']['id']=$sn;
    }
	if(empty($sess['er']['id'])){
        $DEFAULT_ERROR[] = _("ERROR: Equipment SN not set...");
        $body .= "<a href=\"$php_self?m[0]=er\">";
        $body .=_("Please go start from ER section and select your Equipment Record first")."</a>";
		break;
	}

	$er = new tldEquipment($sess['er']['id']);
	if ($er->itsDetails['date_shipped'] <> '0000-00-00') {
		$ds = strtotime($er->itsDetails['date_shipped']);
		$dn = mktime(0, 0, 0, (int) date("n"), (int) date("j"), (int) date("Y"));
		$HRADateTime = new DateTime();
		if (in_array($ERP, ['500', '502', '510', '520', '540'])) {
			$HRADateTime->setTimezone(new DateTimeZone('Europe/Paris'));
        }
		if (in_array($ERP, ['600', '620', '640'])) {
			$HRADateTime->setTimezone(new DateTimeZone('Asia/Shanghai'));
		}
		$HRADate = $HRADateTime->format('Y-m-d');
		if ($dn > $ds || $er->itsDetails['date_shipped'] === $HRADate) {
			$DEFAULT_ERROR[] = _("ERROR: Unable to open CRAB on a shipped unit...");
			break;
		}
	}
    $crab_codes = tldList::optionsByListNameAsListKeyListItem("list.qa.crab.code","list_key");
    unset($crab_codes["999"]);
    foreach($crab_codes as $crab_code=>$crab_desc){
    	$crab_desc=$translate->getDictionary($crab_desc);
    	$crab_code_list[$crab_code] = $crab_desc;
    }
    ksort($crab_code_list, SORT_NUMERIC);
    foreach($crab_code_list AS $k => &$v) {
        $v = "$k, $v";
    }
	$crab_depts=tldList::optionsByListNameAsListItemListItem("list.qa.crab.dept");
    foreach($crab_depts as $key=>$value){
    	$value=$translate->getDictionary($value);
        $crab_dept_list[$key] = $value;
    }
    asort($crab_dept_list);

    $form = new HTML_QuickForm('frmCRAB','','','','',true);
    $form->addElement(	'hidden', 'm[0]', 'crab');
    $form->addElement(	'hidden', 'm[1]', 'new');
    $form->addElement(	'header', 'title', _('Submit CRAB for '));
    if(empty($pn)){
	    $form->addElement(	'text', 'pn', _('PN'));
	}else{
		$form->addElement(	'hidden', 'pn', $pn);
	}
	$form->addElement(	'header', 'title', _('Fill Dept field for Internal CRAB'));
    $form->addElement(	'select', 'opno', _('Stage'),
        [
            "Assy"      => $translate->getDictionary("Assembly"),
            "Test"      => $translate->getDictionary("Test"),
            "QA"        => $translate->getDictionary("Quality Assurance"),
            "PDI"       => $translate->getDictionary("PDI"),
            "PDI-CSC"   => $translate->getDictionary("PDI - Customer Scope Change"),
            "PDI-SOL"   => $translate->getDictionary("PDI - SOL incomplete"),
            "PDI-INTERNAL" => $translate->getDictionary("PDI - Internal")
        ]
    );
    $form->addElement(	'select', 'dept', _('Department'), $crab_dept_list);
    $form->addElement(	'header', 'title', _('Fill NCR# field for External CRAB'));
    $form->addElement(	'text', 'ncrid', _('NCR#'));
    $form->addElement(	'header', 'title', _('FAQ# linked'));
    $form->addElement(	'text', 'faqid', _('FAQ#'));
    $form->addElement(	'header', 'title', sprintf(_('New CRAB for %s'), $pn ?? null));
    $form->addElement(	'select', 'code', _('CRAB Code'), array(""=>"")+$crab_code_list);
    $form->addElement(	'textarea', 'dsca', _('Full Description') . '<br>' . _('and Corrective Action'),
		array("wrap"=>"VIRTUAL","cols"=>"30","rows"=>"5")
	);
    $form->addElement(  'header', 'fileInfo', 'Upload a Photo');
	$form->addElement(	'file', 'main_file', 'Main File');
	$form->addElement(	'textarea', 'file_description[0]', 'Photo Description',
        array("wrap"=>"VIRTUAL", "cols"=>"40", "rows"=>"8"));
    for ($i = 1; $i < 3; $i++) {
        $form->addElement('file', "file[$i]", _('File') . " $i");
        $form->addElement(	'textarea', "file_description[$i]", _('Photo Description'),
            array("wrap"=>"VIRTUAL", "cols"=>"40", "rows"=>"8"));
    }
	if(isset($_SESSION['pi_data'])){

		$form->addElement(  'header', 'PI', 'P&I Information');
		$form->addElement('text','unit', _('Unit'), 				array('disabled'=>'disabled'));
		$form->addElement('text','comp', _('Company'), 				array('disabled'=>'disabled'));
		$form->addElement('text','t_cprj', _('Project'), 			array('disabled'=>'disabled'));
		$form->addElement('text','t_pdno', _('Fabrication Order'), 	array('disabled'=>'disabled'));
		$form->addElement('text','t_opno', _('Operation Number'), 	array('disabled'=>'disabled'));
		$form->addElement('text','t_item', _('Baan PN'), 			array('disabled'=>'disabled'));
		$form->addElement('text','model', _('Model'), 				array('disabled'=>'disabled'));
		$form->addElement('text','question_id', _('Question#'), 	array('disabled'=>'disabled'));
        $form->addElement('text','question_parent_id', _('Parent Question#'), 	array('disabled'=>'disabled'));
        $form->addElement('text','user_id', _('User'), 				array('disabled'=>'disabled'));
		$form->setDefaults([
							'unit'=>$_SESSION['pi_data']['unit'],
							'comp'=>$_SESSION['pi_data']['comp'],
							't_cprj'=>$_SESSION['pi_data']['t_cprj'],
							't_pdno'=>$_SESSION['pi_data']['t_pdno'],
							't_opno'=>$_SESSION['pi_data']['t_opno'],
							't_item'=>$_SESSION['pi_data']['t_item'],
							'model'=>$_SESSION['pi_data']['model'],
							'question_id'=>$_SESSION['pi_data']['question_id'],
                            'question_parent_id'=>$_SESSION['pi_data']['question_parent_id'],
                            'user_id'=>$_SESSION['pi_data']['user_id'],
							]);

		if($_SESSION['pi_data']['t_opno']< 900) { $form->setDefaults(array("opno"=>$translate->getDictionary("Assy"))); }
		if($_SESSION['pi_data']['t_opno']==970) { $form->setDefaults(array("opno"=>$translate->getDictionary("Assy"))); }
 		if($_SESSION['pi_data']['t_opno']==975) { $form->setDefaults(array("opno"=>$translate->getDictionary("Test"))); }
		if($_SESSION['pi_data']['t_opno']==980) { $form->setDefaults(array("opno"=>$translate->getDictionary("Assy"))); }
		if($_SESSION['pi_data']['t_opno']==985) { $form->setDefaults(array("opno"=>$translate->getDictionary("Test"))); }
		if($_SESSION['pi_data']['t_opno']==990) { $form->setDefaults(array("opno"=>$translate->getDictionary("Assy"))); }
		if($_SESSION['pi_data']['t_opno']==995) { $form->setDefaults(array("opno"=>$translate->getDictionary("Assy"))); }
		if($_SESSION['pi_data']['t_opno']==999) { $form->setDefaults(array("opno"=>$translate->getDictionary("QA")));   }
	}

    if (!empty($_SESSION['pi_data']['question_id'])) {
	    $query =<<<SQL
    SELECT id FROM pi_questions_unit WHERE id ={$_SESSION['pi_data']['question_id']} AND answer_type ='Decimal';
SQL;
        if(!empty(tldUtils::getSqlToAssocArray($query))) {
            $form->setDefaults(['code'=>"065"]);
        }

    }

	$form->addElement(	'submit', 'btnSubmit', _('Submit'));
    $form->addRule("code", _("Required field"), "required");
    $form->addRule("dsca", _("Required field"), "required");
    $form->addRule("opno", _("Required field"), "required");
    $form->addRule("dept", _("Required field"), "required");
    if (!$form->validate()){
        $tab = new tldAssocTable(
			$er->getHeader(),
			array(
				"sn"=>			_("Equipment SN#"),
				"status"=>		_("Status"),
				"date_entered"=>_("Date Entered"),
				"type"=>		_("Type"),
				"t_prno"=>		_("Project#"),
				"model"=>		_("Model")
			)
		);
        $body = $tab->fetch();
        $DEFAULT_ERROR[] = _("WARNING: You are about to create a CRAB for this unit...");
		if($er->itsDetails['dyt']<$er->itsDetails['dgt_act'] && $er->itsDetails['dgt_act']<>'0000-00-00'){
	        $DEFAULT_ERROR[] = _("WARNING: This unit is currently GREEN-TAGGED, opening a CRAB will change it to YELLOW-TAG");
		}
        $body .= $form->toHTML();
        break;
    }
    $a = tldUtils::cleanupFormInput($form->exportValues());
    if ($a['code'] < 100) {
        $a['code'] = substr($a['code'], 0);
    }

    try {
        $equipmentRecord = $client->findOneBy('/equipment_records', ['legacyId' => $sess['er']['id']]);
    } catch (ClientException $exception) {
        $DEFAULT_ERROR[] = "_(ERROR: Failed to find equipment record)";
        break;
    }

    try {
        $department = $client->findOneBy('/quality/crab_departments', ['name' => $a['dept']]);
    } catch (ClientException $exception) {
        $DEFAULT_ERROR[] = "_(ERROR: Failed to find department)";
        break;
    }

    $nonConformity = null;
    if (!empty($a['ncrid'])){
        try {
            $nonConformity = $client->find('/quality/non_conformities', (int) $a['ncrid']);
        } catch (ClientException $exception) {
            $DEFAULT_ERROR[] = "_(ERROR: Failed to find non conformity)";
            break;
        }
    }

    try {
        $crabCode = $client->findOneBy('/quality/crab_codes', ['code' => (int) $a['code']]);
    } catch (ClientException $exception) {
        $DEFAULT_ERROR[] = "_(ERROR: Failed to find CRAB code)";
        break;
    }

    //part
    $part = null;
    if (!empty($a['pn'])) {
        try {
            $item = $client->get(sprintf('ion/items/site=%s;item=%s;', $equipmentRecord['manufacturerLocation']['erp'], $a['pn']));

            $part = [
                'partNumber' => $a['pn'],
                'description' => trim(TldDatabase::escape($item['itemDescription'])),
                'unitOfMeasure' => trim(TldDatabase::escape($item['unitOfMeasure'])),
            ];
        } catch (ClientException $e) {
            $DEFAULT_ERROR[] = $e->getMessage();
            $body = $form->toHTML();
            return;
        }
    }

    $firstArticleQualification = null;
    if (!empty($a['faqid'])) {
        try {
            $firstArticleQualification = $client->find('/quality/first_article_qualifications', (int) $a['faqid']);
        } catch (ClientException $exception) {
            $DEFAULT_ERROR[] = "_(ERROR: Failed to find first article qualification)";
            break;
        }
    }

    $crabData = [
        'equipmentRecord' => $equipmentRecord['@id'],
        'description' => mb_convert_encoding($a['dsca'], 'UTF-8', $charset === 'GB2312' ? $charset : 'ISO-8859-1'),
        'category' => $a['opno'],
        'department' => $department['@id'],
        'code' => $crabCode['@id'],
        'nonConformity' => $nonConformity['@id'] ?? null,
        'piQuestionId' => null !== ($a['question_id'] ?? null) ? (int) $a['question_id'] : null,
        'piQuestionParentId' => null !== ($a['question_parent_id'] ?? null) ? (int) $a['question_parent_id'] : null,
        'part' => $part,
        'firstArticleQualification' => $firstArticleQualification['@id'] ?? null,
    ];

    try{
        $newCrab = $client->save('/quality/crabs', $crabData);
    } catch (ClientException $exception) {
        $DEFAULT_ERROR[] = sprintf("ERROR: Fail to save CRAB : data is missing or invalid. %s", $exception->getMessage());
        break;
    }

    if(isset($_SESSION['pi_data'])) {
        $crab = new tldCRAB($newCrab['legacyId']);
        $crabCreated = $crab->insertPI($a);
        if (!is_numeric($crabCreated)) {
            $DEFAULT_ERROR[] = sprintf(_("ERROR: P&I data could not be saved... Reason: CRAB not created"), $crabCreated);
        }
        unset($_SESSION['pi_data']);
    }
    //main file
    $mainFile = $form->getElement('main_file')->getValue();
    $description = $form->getElement("file_description[0]")->getValue() ?? null;
    if (!empty($mainFile['tmp_name'])) {
        $mainFileManager = new FileManager($client);
        try {
            $mainFileManager->uploadFile($mainFile, $newCrab, 'main_file', $description);
        } catch (ClientException $exception) {
            $DEFAULT_ERROR[] = sprintf('Could not attach file CRAB. Reason: %s', $exception->getMessage());
            return;
        }
    }

    //files
    $fileManager = new FileManager($client);
    for ($i = 1; $i < 3; $i++) {
        try {
            $file = $form->getElement("file[$i]");
            $description = $form->getElement("file_description[$i]")->getValue() ?? null;
            $file_array = $file->getValue();
            // Check if file uploaded
            if (empty($file_array['tmp_name'])) {
                continue;
            }
            $fileManager->uploadFile($file_array, $newCrab, 'files', $description);
        } catch (ClientException $exception) {
            $DEFAULT_ERROR[] = sprintf('Could not attach file CRAB. Reason: %s', $exception->getMessage());
            return;
        }
    }

    $body= "<a href=\"$php_self?m[0]=crab&m[1]=view&id={$newCrab['legacyId']}\">".sprintf(_("View CRAB#%s"),$newCrab['legacyId'])."...</a>";


break;
case 'view':
    if(empty($id)){
        $DEFAULT_ERROR[]= _("ERROR: no line id set...");
        break;
    }
    $crab = new tldCRAB($id);
    if($crab->isEmpty()){
        $DEFAULT_ERROR[]= sprintf(_("ERROR: No CRAB#%s found..."),$id);
        break;
    }

    try {
        $apiCrab = $client->findOneBy('/quality/crabs', ['legacyId' => $id]);
        //2 calls have to be made because the first one call collection route and does not display every information
        $itemApiCrab = $client->find('/quality/crabs', $apiCrab['id']);
    } catch (ClientException $exception) {
        $DEFAULT_ERROR[] = sprintf("ERROR: Could not find CRAB# %s" ,$id);
        break;
    }

    $header = $crab->getHeader();
    $DEFAULT_TITLE .= "\CRAB#$id";
    $DEFAULT_MENU .="
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href=\"$php_self?m[0]=crab&m[1]=view&id=$id\">"._("General")."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=crab&m[1]=view&m[2]=log&id=$id\" title=\""._("Activity Log")."\">"._("Log")."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=crab&m[1]=view&m[2]=files&id=$id\" title=\""._("Files")."\">"._("Files")."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=crab&m[1]=view&m[2]=transfer&module=NCR&id=$id\" title=\""._("Transfer to NCR")."\">"._("Tx NCR")."</a>
";
    $erid = $crab->getERID();
    if($erid){
    	$DEFAULT_MENU .="
&nbsp;|&nbsp;<a href=\"$php_self?m[0]=er&m[1]=view&id=$erid\" title=\""._("Go to ERID#")."$erid\">"._("ER")."</a>
";
    }
    $DEFAULT_MENU .="
&nbsp;|&nbsp;<a href=\"$php_self?m[0]=crab&m[1]=view&m[2]=addlog&id=$id\" title=\""._("Add manual comment")."\">"._("Add manual comment")."</a>
";
    switch($m[2] ?? null){
    case 'addlog':
        $form = new HTML_QuickForm('frmNew', 'post');
        $form->addElement('hidden', 'm[0]', 'crab');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'addlog');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('textarea', 'comment',  _('Comment'),
            ["wrap" => "VIRTUAL", "cols" => "60", "rows" => "5"]);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule("comment", _("Required field"), "required");
        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $payload = [
            'resource' => $apiCrab['@id'],
            'message' => sprintf('Comment by SFE : %s', mb_convert_encoding($vars['comment'], 'UTF-8', $charset === 'GB2312' ? $charset : 'ISO-8859-1'))
        ];

        try {
            $client->save('/comments', $payload);
        } catch (ClientException $e) {
            $DEFAULT_ERROR[] = sprintf('Could not create comment. Reason : %s', $e->getMessage());
            break;
        }
        $body = "<p>Comment successfully added!</p>";
    break;
    case 'transfer':
        $module = strtoupper($module);
        $DEFAULT_TITLE .= sprintf(_("\Transfer to %s"), $module);
        switch($module){
            case 'NCR':
                $form = new HTML_QuickForm('frmTransfer', 'post');
                $form->addElement(	'header', 'title', 'Enter an existing NCR ID#');
                $form->addElement(	'hidden', 'm[0]', 'crab');
                $form->addElement(	'hidden', 'm[1]', 'view');
                $form->addElement(	'hidden', 'm[2]', 'transfer');
                $form->addElement(	'hidden', 'module', $module);
                $form->addElement(	'hidden', 'id',  $id);
                $form->addElement(	'text', 'ncr_id', 'NCR#');
                $form->addElement(	'submit', 'btnSubmit', 'Submit');

                if(!$form->validate()){
                    $body = $form->toHTML();
                    $body .= "<br><a href=\"$php_self?m[0]=ncr&m[1]=forms&m[2]=newNCR&m[3]=transfer&module=CRAB&id=$id\" target=\"_blank\">"._("or Click here to create a new NCR")."</a>";
                    break;
                }
                $vars = tldUtils::cleanupFormInput($form->exportValues());
                try {
                    /** @var \App\Client\ApiClient $client */
                    $ncr = $client->find('/quality/non_conformities', $vars['ncr_id'], ['query' => ['normalizationGroups' => ['non_conformity:legacy', 'expose_legacy']]]);
                } catch (ClientException $exception) {
                    $DEFAULT_ERROR[]="ERROR: No NCR #$id found...";
                    break;
                }
                $sess["ncr"]["currentNCR"] = $vars['ncr_id'];
                $sess["ncr"]["pn_crab"] = $header['pn'];
                $sess["ncr"]["id_crab"] = $id;
                // Re-direct to add part

                $body = "
                <br/><meta http-equiv=\"refresh\" content=\"2;URL=$php_self?m[0]=ncr&m[1]=forms&m[2]=addParts\">
                <a href=\"$php_self?m[0]=ncr&m[1]=forms&m[2]=addParts\">If the screen does not refresh automatically, click here.</a>";
                break;
        }
        break;
    case 'log':
        $DEFAULT_TITLE .="\Log";
        $log = $crab->getLog();
        $report = new tldReportColumnar($log,
            array(
                "xItems"=>array(
                    "id"    			=>_("ID#"),
                    "date"      		=>_("Date"),
                    "poster_fullname"   =>_("Poster"),
                    "comment"       	=>_("Comment")
                )
            )
        );
        $body = $report->fetch();
    break;
	case 'files':
		$DEFAULT_TITLE .="\Files";
		$DEFAULT_MENU .="
<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href=\"$php_self?m[0]=crab&m[1]=view&m[2]=files&id=$id\">"._("Home")."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=crab&m[1]=view&m[2]=files&m[3]=add_photo&id=$id\">"._("Upload New Photo")."</a>
";
		switch($m[3] ?? null){
		case 'add_photo':
		    $form = new HTML_QuickForm('frmCRABPhoto','','','','',true);
		    $form->addElement(	'hidden', 'm[0]', 'crab');
		    $form->addElement(	'hidden', 'm[1]', 'view');
		    $form->addElement(	'hidden', 'm[2]', 'files');
		    $form->addElement(	'hidden', 'm[3]', 'add_photo');
		    $form->addElement(	'hidden', 'id', $id);
		    $form->addElement(  'header', 'fileInfo', _('Upload a Photo'));
			$form->addElement(	'file', 'file', _('Attachment'));
			$form->addElement(	'textarea', 'description', _('Photo Description'),
		        array("wrap"=>"VIRTUAL", "cols"=>"40", "rows"=>"8"));
			$form->addElement(	'submit', 'btnSubmit', _('Submit'));
			$form->addRule("description", _("Required field"), "required");
			$form->addRule("file", _("Required field"), "required");
		    if (!$form->validate()){
		        $body = $form->toHTML();
		        break;
		    }
		    $vars = tldUtils::cleanupFormInput($form->exportValues());
            $vars['poster'] = $_SESSION['pi_user_input'];
	        // Check temp file
			$file = $form->getElement("file")->getValue();
            if (!empty($file['tmp_name'])) {
                $fileManager = new FileManager($client);
                try {
                    $fileManager->uploadFile($file, $apiCrab, 'files', $vars['description']);
                } catch (ClientException $exception) {
                    $DEFAULT_ERROR[] = sprintf('Could not attach file CRAB. Reason: %s', $exception->getMessage());
                    return;
                }
            }

            $body = 'File uploaded successfully.';
    	break;
		case 'out':
            $fileManager = new FileManager($client);
		    if(empty($fileid) || !is_numeric($fileid)){
		        $DEFAULT_ERROR[] = "ERROR: Parameter sent empty or invalid";
		        break;
		    }
            echo $fileManager->dowloadFile(sprintf('quality/crabs/%s/files/%s', $itemApiCrab['id'], $fileid));
			exit;
		case 'outMainFile':
            $fileManager = new FileManager($client);
		    if(empty($fileid) || !is_numeric($fileid)){
		        $DEFAULT_ERROR[] = "ERROR: Parameter sent empty or invalid";
		        break;
		    }
            echo $fileManager->dowloadFile(sprintf('quality/crabs/%s/main_file/%s', $itemApiCrab['id'], $fileid));
			exit;
		default:
            $files = $itemApiCrab['files'];
			$report = new tldReportColumnar(
	            $files,
				array(
	                "xItems"=>array(
	                    "id"=>_("ID#"),
	                    "createdAt"=>_("Date"),
	                    "description"=>_("Description"),
	                    "filePath"=>_("Filename")
	                ),
					"links"=>array(
	                    "id"=>"$php_self?m[0]=crab&m[1]=view&m[2]=files&m[3]=out&id=$id&fileid="
	                ),
	                "title"=>_("Files")
				)
			);
			$body = $report->fetch();
			$report = new tldReportColumnar(
	            [$itemApiCrab['mainFile']],
				array(
	                "xItems"=>array(
                        "id"=>_("ID#"),
                        "createdAt"=>_("Date"),
                        "description"=>_("Description"),
                        "filePath"=>_("Filename")
	                ),
					"links"=>array(
	                    "id"=>"$php_self?m[0]=crab&m[1]=view&m[2]=files&m[3]=outMainFile&id=$id&fileid="
	                ),
	                "title"=>_("Photos")
				)
			);
			$body .= $report->fetch();
		break;
		}
	break;
		default:
            if (null === $header['inspectlog']) {
                $header['inspectlog'] = $itemApiCrab['inspectingComments'];
            }
			$report = new tldAssocTable(
				$header,
				[
					"id" => _("CRAB#"),
					"buid_fullname" => _("BU"),
					"pn" => _("Part Number"),
					"dsca" => _("Description"),
					"opno" => _("Stage"),
					"dept" => _("Department"),
					"code" => _("Code"),
				],
				[
					"title" => _("General"),
				]
			);
			$body = $report->fetch();
			$report = new tldAssocTable(
				$header,
				[
					"init_emno" => _("Employee Number, Found By"),
					"initiator_fullname" => _("Found By"),
					"dt" => _("Entered Date"),
				],
				[
					"title" => _("Initiated"),
				]
			);
			$body .= $report->fetch();
			$report = new tldAssocTable(
				$header,
				[
					"fix_emno" => _("Employee Number, Fixed By"),
					"fixer_fullname" => _("Fixed By"),
					"fix_dt" => _("Signed Date"),
					"act" => _("Comments"),
				],
				[
					"title" => _("Fixed"),
				]
			);
			$body .= $report->fetch();
			$report = new tldAssocTable(
				$header,
				[
					"insp_emno" => _("Employee Number, Inspected By"),
					"insp_fullname" => _("Inspected By"),
					"insp_dt" => _("Signed Date"),
					"inspectlog" => _("Comments"),
				],
				[
					"title" => _("Inspected"),
				]
			);
			$body .= $report->fetch();
			$photo = $itemApiCrab['mainFile'];

            if (null !== $photo){
                if (\in_array($photo['extension'], ['png', 'jpg', 'jpeg'], true)) {
                    $body .= "<img src=\"$php_self?m[0]=crab&m[1]=view&m[2]=files&m[3]=outMainFile&id=$id&fileid={$photo['id']}\" style=\"max-width:500px;\"/><br/>";
                }else {
                    $body .= "<a href=\"$php_self?m[0]=crab&m[1]=view&m[2]=files&m[3]=outMainFile&id=$id&fileid={$photo['id']}\" target=\"_blank\">
                      <img src=\"/shared/bluesphere/64x64/mimetypes/document.png\" alt=\"_(\"Download Attachment\")\">
                    </a>";
                }
                if(isset($photo['description'])){
                    $body .= "<div>{$photo['description']}</div><br/><br/>";
                }
            }

            $extraFiles = $itemApiCrab['files'] ?? [];
            if (!empty($extraFiles)) {
                $body .= "<hr/><b>"._("Photos")."</b><br/><br/>";
                $body .= "<div id=\"crab-carousel\" style=\"position:relative;display:inline-block;max-width:500px;\">";
                foreach ($extraFiles as $i => $file) {
                    $isImage = \in_array($file['extension'], ['png', 'jpg', 'jpeg'], true);
                    $fileUrl = "$php_self?m[0]=crab&m[1]=view&m[2]=files&m[3]=out&id=$id&fileid={$file['id']}";
                    $display = $i === 0 ? 'block' : 'none';
                    $body .= "<div class=\"crab-slide\" style=\"display:$display;\">";
                    if ($isImage) {
                        $body .= "<a href=\"$fileUrl\" target=\"_blank\"><img src=\"$fileUrl\" style=\"max-width:500px;\"/></a>";
                    } else {
                        $body .= "<a href=\"$fileUrl\" target=\"_blank\"><img src=\"/shared/bluesphere/64x64/mimetypes/document.png\"/></a>";
                    }
                    if (!empty($file['description'])) {
                        $body .= "<div style=\"text-align:center;margin-top:4px;\">{$file['description']}</div>";
                    }
                    $body .= "<div style=\"text-align:center;color:#888;font-size:0.85em;\">".($i+1)." / ".count($extraFiles)."</div>";
                    $body .= "</div>";
                }
                if (count($extraFiles) > 1) {
                    $body .= "<div style=\"text-align:center;margin-top:8px;\">
                        <button onclick=\"crabCarousel(-1)\" style=\"margin-right:8px;\">&laquo; "._("Prev")."</button>
                        <button onclick=\"crabCarousel(1)\">"._("Next")." &raquo;</button>
                    </div>
                    <script>
                        var _crabIdx = 0;
                        function crabCarousel(dir) {
                            var slides = document.querySelectorAll('#crab-carousel .crab-slide');
                            slides[_crabIdx].style.display = 'none';
                            _crabIdx = (_crabIdx + dir + slides.length) % slides.length;
                            slides[_crabIdx].style.display = 'block';
                        }
                    </script>";
                }
                $body .= "</div><br/>";
            }




    }
break;
case 'image':
    $body = "<img src=\"https://www.tld-gse.com/en/private/common/index.php?m[0]=files&m[1]=view&m[2]=out&id={$id}\" style=\"max-width:1000px;\"/><br/>";
break;
default:
    $body ="
<img src=\"/shared/icons/animals/crab.jpg\" align=\"right\">
"._("To view CRABs linked to a particular unit, please visit the ER link above.");
    // Get form
    $form = new HTML_QuickForm('frmER','get','','','',true);
    $form->addElement(	'header', 'title', _('View Crab by ID'));
    $form->addElement(	'hidden', 'm[0]', 'crab');
    $form->addElement(	'hidden', 'm[1]', 'view');
    $form->addElement(	'text', 'id', _('Enter Crab#'),
		array("size"=>"20")
	);
    $form->addElement(	'submit', 'btnSubmit', _('Submit'));
	$body .= $form->toHTML();
    $body .= "<a href='$php_self?m[0]=crab&m[1]=crabfromsol'>Create CRAB for SOL</a>";
break;
}

?>
