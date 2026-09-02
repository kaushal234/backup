<?php

use Symfony\Component\HttpClient\Exception\ClientException;

if(empty($id)){
    $rows = tldEquipment::bySN($sn);
    $num = count($rows);
    if($num==1){
        $id = $rows[0]['id'];
    }elseif($num>1){
        $report = new tldReportColumnar($rows, array(
                "title"=>_("Pls select correct Equipment Records"),
                "xItems"=>array(
                    "id"    =>_("ER ID#"),
                    "sn"    =>_("Serial Number"),
                    "type"  =>_("Type"),
                    "model" =>_("Model")),
                "links"=>array("id"=>"$php_self?m[0]=er&m[1]=view&id=")
            )
        );
        $body .= $report->fetch();
        return;
    }else{
        $DEFAULT_ERROR[] = sprintf(_("ERROR: no ERs found for sn '%s'"),$sn);
        return;
    }
}

$er = new tldEquipment($id);
$header = $er->getHeader();
if(empty($header)){
    $DEFAULT_ERROR[] = _("ERROR: Equipment record $id not found");
    return;
}
$sess['er']['id'] = $id;

$DEFAULT_TITLE .= "\SN:".$er->getSN();
$DEFAULT_MENU.="
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href=\"$php_self?m[0]=er&m[1]=view&id=$id\" title=\"General Information\">"._("General")."</a>
&nbsp;|&nbsp;<a href=\"$php_self?m[0]=er&m[1]=view&m[2]=files&id=$id\" title=\"Files\">Files</a>
&nbsp;|&nbsp;<a href=\"$php_self?m[0]=er&m[1]=view&m[2]=crabs&id=$id\" title=\"CRABs\">CRAB</a>
&nbsp;|&nbsp;<a href=\"$php_self?m[0]=er&m[1]=view&m[2]=sn&id=$id\" title=\"Component SN\">"._("Component List")."</a>
";

if($header['t_prno'])
$DEFAULT_MENU.=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=cbom&m[1]=view&sn={$header['t_prno']}" title="CBOM">CBOM</a>
EOF;
if ($header['date_shipped'] === '0000-00-00') {
$DEFAULT_MENU.= "&nbsp;|&nbsp;<a href=\"$php_self?m[0]=crab&m[1]=new&sn=$id\" title=\"Submit CRAB without PN\">"._("Submit CRAB without PN")."</a>";
}
$body = '';
switch($m[2] ?? null){
    case 'files':
        $DEFAULT_TITLE .= "\ER Files";
        switch ($m[3] ?? null) {
            case 'out':
                $file = new tldModFile($fileId);
                $file->outFile();
                exit;
        }
        // display
        $body .= _getFileView($er);
        break;
case 'crabs':
    switch($m[3] ?? null){
    case 'fixed':
        $crab = new tldCRAB($crabid);

        try {
            $apiCrab = $client->findOneBy('/quality/crabs', ['legacyId' => $crabid]);
        } catch (ClientException $exception) {
            $DEFAULT_ERROR[] = 'CRAB not found from API';
        }

        if (!empty($apiCrab) && null !== $apiCrab['derogation']) {
            $DEFAULT_ERROR[] = _("ERROR: CRAB derogated, no need to fix");
            $body .= getCRABGeneralPage();
            break;
        }

        $tasks = tldTask::byParent($crabid, 'CRAB', 'OPEN');
        if(count($tasks) > 0){
            $DEFAULT_ERROR[] = _("ERROR: There is open task link to CRAB, please close the task link to the CRAB...");
            $body .= getCRABGeneralPage();
            break;
        }
        if($crab->itsHeader['fix_dt'] <> "0000-00-00 00:00:00"){

            $DEFAULT_ERROR[] = _("ERROR: CRAB already fixed...");
            $body .= getCRABGeneralPage();
            break;
        }
        if($crab->itsHeader['erid'] <> $id){
            $DEFAULT_ERROR[] = _("ERROR: CRAB ID and ER ID are not related...");
            $body .= getCRABGeneralPage();
            break;
        }
        $form = new HTML_QuickForm('frmBySN', 'post');
        $form->addElement(  'hidden', 'm[0]', 'er');
        $form->addElement(  'hidden', 'm[1]', 'view');
        $form->addElement(  'hidden', 'm[2]', 'crabs');
        $form->addElement(  'hidden', 'm[3]', 'fixed');
        $form->addElement(  'hidden', 'id', $id);
        $form->addElement(  'hidden', 'crabid', $crabid);
        $form->addElement(  'header', 'title', sprintf(_("CRAB#%s Fixed Signoff"),$crabid));
        $form->addElement(  'text', 'emno', _("Employee Number, Fixed By"));
        $form->addElement(  'textarea', 'log_comment', 'Comments',
                            array("wrap"=>"VIRTUAL", "cols"=>"40", "rows"=>"8")
        );
        $form->addElement(  'submit', 'btnSubmit', _('Submit'));
        $form->addRule("emno", _("Required field"), "required");
        $form->addRule("log_comment", _("Required field"), "required");
        $form->applyFilter(array('log_comment'), 'trim');
        $form->setDefaults(['emno' => $client->getUserId()]);
        if ($form->validate()){
            $a = $form->exportValues();

            try {
                $employee = $client->get(sprintf('/people/%s', $a['emno']));
            } catch (ClientException $e) {
                $DEFAULT_ERROR[] = $e->getMessage();
                $body = $form->toHTML();
                return;
            }

            try {
                $crabFixed = $client->findOneBy('/quality/crabs', ['legacyId' => $a['crabid']]);
            } catch (ClientException $exception) {
                $DEFAULT_ERROR[] = "_(ERROR: Failed to find crab)";
                break;
            }

            $payload = [
                'fixingComments' => mb_convert_encoding($a['log_comment'], 'UTF-8', $charset === 'GB2312' ? $charset : 'ISO-8859-1'),
                '@id' => $crabFixed['@id'],
                'status' => 'TO-INSPECT',
            ];

            try {
                $setCrab = $client->save('/quality/crabs/', $payload);
            } catch (ClientException $exception) {
                $DEFAULT_ERROR[] = sprintf('ERROR: Fail to fix CRAB : %s', $exception->getMessage());
            }

        }else{
            $body = $form->toHTML();
        }
        $body .= getCRABGeneralPage();
    break;
    default:
    	$body = getCRABGeneralPage();
    }
break;
case 'sn';
    // this part create the component list
    $DEFAULT_TITLE .= "\/"._("Serial Numbers");
    $report = new tldReportColumnar(
        $er->getSerials(),
        array(
            "xItems"=>array(
                "component" =>_("Component"),
                "brand"     =>_("Brand"),
                "model"     =>_("Model"),
                "serial"    =>_("Serial"),
            ),
            "title"=>_("Component Serial Numbers")
            )
        );
    $body = $report->fetch();
    break;
default:
    $DEFAULT_ERROR[] = _("NOTE: To create a CRAB, use the CBOM to navigate to the affected part and hit the CRAB link next to the part.");
    $body = getGeneralPage();
}

function getCRABGeneralPage(){
	global $er, $php_self, $id;
	$report = new tldReportColumnar(
		$er->getCRABS(),
		array(
			"xItems"=>array(
				"id"=>			_("CRAB#"),
				"dt"=>			_("Entered Date"),
				"pn"=>			_("Part Number"),
				"dsca"=>		_("Description"),
				"act"=>			_("Corrective Action"),
				"opno"=>		_("Stage"),
				"who"=>			_("Who"),
				"dept"=>		_("Department"),
				"code"=>		_("Code"),
				"init_emno"=>	_("Employee Number, Found By"),
				"initiator_fullname"=>	_("Found By"),
				"fix_emno"=>	_("Employee Number, Fixed By"),
				"fixer_fullname"=>	_("Fixed By"),
				"fix_dt"=>		_("Signed Date")
			),
			"title"=>_("CRABs"),
			"links"=>array(
				"id"=>"$php_self?m[0]=crab&m[1]=view&id="
			),
			"functions"=>array(
				"Fixed"=>array(
					"url"=>"$php_self?m[0]=er&m[1]=view&m[2]=crabs&m[3]=fixed&id=$id&crabid=",
					"param"=>"id"
				)
			)
		)
	);
    return $report->fetch();
}

function getGeneralPage(){
    global $header;
    $form = new tldAssocTable($header,
        array(	"id"				=>_("Equipment ID#"),
                "sn"				=>_("Equipment SN#"),
                "status"			=>_("Status"),
                "date_entered"		=>_("Date Entered"),
                "esrid"				=>_("ESR ID#"),
                "cust_asset_num"	=>_("Customer Asset#"),
                "type"				=>_("Type"),
                "model"				=>_("Model"),
        		"er_batch_qty"		=>_("Batch Qty"),
        		"hours"				=>_("Hour meter"),
                "man_location"		=>_("Manufacturer location"),
                "apc_fullname"		=>_("Airport"),
                "location_short"	=>_("Unit Location Short"),
                "options_desc"		=>_("Description of Installed Options"),
                "sor_lid"			=>_("SOR Line#"),
                "sor_uid"			=>_("SOR Unit ID#"),
                "sales_org"			=>_("Sales Organization"),
                "sales_rep"			=>_("Sales Rep Email"),
                "agent_name"		=>_("Agent Name"),
                "mfg_comments"		=>_("MFG Comments"),
                "t_pdno"			=>_("Main Work Order#"),
                "t_prno"			=>_("MFG Project#"),
                "sls_orno"			=>_("MFG SO#"),
                "dgt_rev"			=>_("Estimated Green Tag Date"),
                "dgt_act"			=>_("Actual Green Tag Date"),
                "date_shipped"		=>_("Actual Ship Date"),
                "rrd_sso"			=>_("Date, Revenue Recognition, SSO"),
                "rrd_erp"			=>_("Date, Revenue Recognition, Factory"),
        		"publishable"		=>_("CBOM Manual Publishable"),
        		"diml"				=>_("Length (in mm)"),
				"dimw"				=>_("Width (in mm)"),
				"dimh"				=>_("Height (in mm)"),
				"dimk"				=>_("Weight (in Kg)")
            ),
            array("title"=>_("General"))
        );
     return $form->fetch();
}

function _getFileView(tldEquipment $eq)
{
    $body = "<h3>Files of ER# {$eq->getSN()}</h3>";
    $fileList = _getFileList($eq);
    $body .= "<table><tr><td width=\"50%\">$fileList</td></tr></table>";
    if ($eq->hasPreAssemblyER()) {
        foreach ($eq->getCombinationErList('PRE-ASSEMBLY') as $erVal) {
            $pas = new tldEquipment($erVal['id']);
            $body .= "<h3>Files of PAS# {$pas->getSN()}</h3>";
            $fileList = _getFileList($pas);
            $body .= "<table><tr><td width=\"50%\">$fileList</td></tr></table>";
        }
    }
    return $body;
}

function _getFileList(tldEquipment $er)
{
    global $php_self;

    $URL = "$php_self?m[0]=er&m[1]=view&m[2]=files&m[3]=out&id=$er->itsId&fileId=";
    // TLD file list of the ER
    $tldFileList = new tldReportColumnar(
        tldModFile::byParent($er->getID(), 'ER'),
        [
            'xItems' => [
                'id' => 'ID#',
                'date' => 'Date',
                'description' => 'Description',
                'filename' => 'Filename',
            ],
            'title' => 'TLD file list',
            'links' => ['id' => $URL],
        ]
    );
    return $tldFileList->fetch();
}
?>
