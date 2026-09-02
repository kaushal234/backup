<?php

use App\Manager\FileManager;
use Symfony\Component\HttpClient\Exception\ClientException;
use App\Client\ApiClient;

$DEFAULT_TITLE .= "\NCR";

$DEFAULT_MENU .="
<a href=\"$php_self?m[0]=ncr\">NCR "._("Home")."</a>
&nbsp;|&nbsp;<a href=\"$php_self?m[0]=ncr&m[1]=forms&m[2]=byNumber\">"._("By number")."</a>
&nbsp;|&nbsp;<a href=\"$php_self?m[0]=ncr&m[1]=lists&m[2]=\">"._("Pending")."</a>
&nbsp;|&nbsp;<a href=\"$php_self?m[0]=ncr&m[1]=lists&m[2]=inProgress\">"._("In Progress")."</a>
&nbsp;|&nbsp;<a href=\"$php_self?m[0]=ncr&m[1]=forms&m[2]=newNCR\">"._("Submit")." NCR</a>
";

/** @var ApiClient $client */
$fileManager = new FileManager($client);
switch($m[1] ?? null){
case "file":
	switch($m[2] ?? null){
	case "getPhoto":
		if(empty($ncr)){
			$DEFAULT_ERROR[] = sprintf(_("ERROR: Could not find NCR# %s"),$id);
			break;
		}
        echo $fileManager->dowloadFile(sprintf('quality/non_conformities/%s/main_file/%s', $ncr['id'], $ncr['mainFile']['id']), true, $ncr['mainFile']['filePath']);
		exit;
	}
break;
case "forms":
    switch($m[2] ?? null){
    case "addParts":
        ob_start();
        include_once 'getdescription.tpl';
        $headExtra = ($headExtra ?? '') . ob_get_clean();
        $id = $sess["ncr"]["currentNCR"];
        if (empty($id) || !is_numeric($id)) {
            $DEFAULT_ERROR[] = _("ERROR: no id set for adding parts");
            break;
        }
        try {
            $ncr = $client->find('/quality/non_conformities', $id, ['query' => ['normalizationGroups' => ['non_conformity:legacy']]]);
        } catch (ClientException $exception) {
            $DEFAULT_ERROR[] = _("ERROR: no id set for adding parts");
            break;
        }
        try {
            $factory = $client->find($ncr['location']['@id']);
        } catch (ClientException $exception) {
            $DEFAULT_ERROR[] = _("ERROR: location not found");
            break;
        }

        $form = new HTML_QuickForm('frmNewNCR', 'post');
        $form->addElement('header', 'title', _('Submit New NCR Form'));
        $form->addElement('hidden', 'm[0]', 'ncr');
        $form->addElement('hidden', 'm[1]', 'forms');
        $form->addElement('hidden', 'm[2]', 'addParts');
        $form->addElement('hidden', 'bu', $factory['erp'], ['id' => 'buid']);
        $form->addElement('text', 'part_number', _('Part Number'), ['id' => 'itemno', 'onblur' => 'msgAjax()']);
        $form->addElement('text', 'short_desc', _('Short Description of Part'),
            ['id' => 'itemdesc', 'class' => 'nob', 'disabled' => 'readonly', 'size' => '50']);
        $form->addElement('text', 'qty', _('Quantity'), ["size" => "3"]);
        $form->addElement('select', 'ref_type', _('Ref Type'), [
            '' => '',
            'SN' => _('Serial Number'),
            'WO' => _('Work Order'),
            'PO' => _('Purchase Order'),
            'DO'  => _('Distribution Order'),
            'WHSE' => _('Warehouse'),
            'PARTS' => _('Spare Parts'),
            'PROD' => _('Production'),
            'WELD' => _('Welding Dept'),
            'CUST' => _('Customer'),
            'ER' => _('Unit s/n'),
            'OTHER' => _('Other'),
        ]);
        $form->addElement('text', 'ref', _('Ref#'), ["size" => "20"]);
        $form->addElement('text', 'sn', _('Component SN'), ["size" => "50"]);
        $form->addElement('submit', 'btnSubmit', _('Submit'), ['id' => 'button', 'disabled' => 'disabled']);
        // Set Rules
        $required = array("part_number", "qty", "location_type", "location");
        foreach ($required as $field) {
            $form->addRule($field, _("Field required"), "required");
        }
        $form->addRule("qty", _("Must be numeric"), "numeric");

        if ($form->validate()) {
            $part = tldUtils::cleanupFormInput($form->exportValues());
            $ERP = tldLocation::getERPByID($part['bu']);

            try {
                $item = $client->get(sprintf('ion/items/site=%s;item=%s;', $factory['erp'], trim($part['part_number'])));
            } catch (ClientException $e) {
                $DEFAULT_ERROR[] = $e->getMessage();
                $body = $form->toHTML();
                return;
            }

            $existingParts = [];
            foreach ($ncr['parts'] as $existingPart) {
                $existingParts[] = $existingPart['@id'];
            }

            $existingParts[] = [
                'partNumber' => trim($part['part_number']),
                'description' => trim(TldDatabase::escape($item['itemDescription'])),
                'quantity' => (int) $part['qty'],
                'unitOfMeasure' => trim(TldDatabase::escape($item['unitOfMeasure'])),
                'reference' => $part['ref_type'],
                'referenceNumber' => $part['ref'],
                'serialNumber' => $part['sn'],
            ];

            $payload = [
                '@id' => $ncr['@id'],
                'parts' => $existingParts,
            ];

            try {
                $client->save('/quality/non_conformities', $payload);
            } catch (ClientException $exception) {
                $DEFAULT_ERROR[] = sprintf(_("Could not add part to NCR. There was an error processing.
            The error returned is '%s'"), $exception->getMessage());
            }
        }
        $body = "<br>
        <h3>" . sprintf(_("Parts List for NCR#"), $id) . "</h3>
        <a class=\"button button-blue\" href=\"$php_self?m[0]=ncr&m[1]=view&id=$id\">" . _("I have no more parts to add, complete NCR submission") . "...</a>
";
        $form->setDefaults(["part_number" => "", "short_desc" => "", "qty" => "", "location" => ""]);
        $body .= $form->toHTML();
        $table = new tldReportColumnar(
            $ncr['parts'],
            [
                "xItems" => [
                    "partNumber" => _("Part Number"),
                    "description" => _("Description"),
                    "quantity" => _("Qty"),
                    "reference" => _("Ref Type"),
                    "referenceNumber" => _("Ref#"),
                    "serialNumber" => _("SN")
                ]
            ]
        );
        $body .= $table->fetch();
        break;
    case 'parts.getinfo':
        $body = '';
        try {
            $item = $client->get(sprintf('ion/items/site=%s;item=%s;', $buid, $itemno));
        } catch (ClientException $e) {
            $item = [];
        }

        if (empty($item)) {
            $item['status'] = '0';
            $item['bu'] = $buid;
            $item['itemno'] = $itemno;

            echo json_encode($item);
            exit;
        }

        $item['t_dsca'] = mb_convert_encoding(RTRIM($item['itemDescription']), 'UTF-8', mb_list_encodings());
        $item['status'] = '1';
        header('Content-type: text/json');
        echo json_encode($item);
        exit;

    case "newNCR":
        ob_start();
        include_once 'getdescription.tpl';
        $headExtra = ($headExtra ?? '') . ob_get_clean();
        $transferFromNCR = 0;
        switch ($m[3] ?? null) {
            case 'transfer':
                $module = strtoupper($module);
                switch ($module) {
                    case 'CRAB':
                        $id = TldDatabase::escape($id);
                        if (empty($id) || !is_numeric($id)) {
                            $DEFAULT_ERROR[] = "_(ERROR: Fail to initiate transfer: data is missing or invalid)";
                            break;
                        }
                        $crab = new tldCRAB($id);
                        if ($crab->isEmpty()) {
                            $DEFAULT_ERROR[] = sprintf(_("ERROR: No CRAB#%s found..."), $id);;
                            break;
                        }
                        $crab_header = $crab->getHeader();
                        $default = array(
                            "problem" => $crab_header['dsca'],
                            "t_emno" => $crab_header['init_emno'],
                            "factory" => $crab_header['buid']
                        );
                        $sess["ncr"]["pn_crab"] = $crab_header['pn'];
                        $sess["ncr"]["id_crab"] = $crab_header['id'];
                        $transferFromNCR = 1;
                        break;
                }
                break;
        }
        try {
            // listing
            $modelRawList = tldCatalogue::getTypeModelList(true);
            $modelList = array();
            foreach ($modelRawList as $var) {
                $modelList[$var['model']] = $var['type'] . "->" . $var['model'];
            }
            $factories = array("" => "") + tldLocation::getFactoryList("smartyOptions");
            // forms
            $form = new HTML_QuickForm('frmNewNCR', 'post');
            $form->addElement('header', 'title', _('Submit New NCR Form'));
            $form->addElement('hidden', 'm[0]', 'ncr');
            $form->addElement('hidden', 'm[1]', 'forms');
            $form->addElement('hidden', 'm[2]', 'newNCR');
            $form->addElement('hidden', 'source', $transferFromNCR);
            $form->addElement('hidden', 'id', $sess["ncr"]["id_crab"] ?? null);
            $form->addElement('select', 'factory', _('Business Unit'), $factories, ['id' => 'buid']);
            $form->addElement('select', 'ifactor', _('Important factor'), ["1" => "1", "10" => "10", "100" => "100", "1000" => "1000"]);
            $form->addElement('text', 'hours', _('Time spent (complete hour only)'), ["size" => "5"]);
            $form->addElement('textarea', 'problem', _('Full Description of Problem'),
                ["wrap" => "VIRTUAL", "cols" => "40", "rows" => "8"]);
            $form->addElement('textarea', 'investigation', _('Investigation results'),
                ["wrap" => "VIRTUAL", "cols" => "40", "rows" => "8"]);
            $ams =& $form->addElement('advmultiselect', 'models', null,
                ["OTHER" => "Other / Discontinued", "ENV"=>"NCR Environmental Issue", "SAFETY"=>"NCR Safety"] + $modelList,
                ['size' => 15, 'class' => 'pool', 'style' => 'width:380px;']
            );
            $ams->setLabel(array(_('Affected Models'), 'Type->Model', 'Affected'));
            // NB: 'add'/'remove' are internal keys matched by a switch() in
            // HTML_QuickForm_advmultiselect::setButtonAttributes(), not user-facing
            // labels (those come from 'value' below) - do not translate them,
            // otherwise the switch falls through to a static call on a non-static
            // PEAR method, which is a fatal error under PHP 7+.
            $ams->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
            $ams->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
            $form->addElement('select', 'rush', _('Rush process required?'), [false => "N", true => "Y"]);
            $form->addElement('header', 'title', "Please upload picture before submitting the NCR!");
            $form->addElement('file', 'photo', _('Upload picture file'));
            $form->addElement('header', 'title', _('Upload files') . ' (' . _('additionnal pictures, etc.') . ')');
            for ($i = 1; $i < 5; $i++) {
                $form->addElement('file', "files[$i]", _('File') . " $i");
            }
            $form->addElement('text', 'part_number', _('Part Number'), ['id' => 'itemno', 'onblur' => 'msgAjax()']);
            $form->addElement('text', 'short_desc', _('Short Description of Part'),
                ['id' => 'itemdesc', 'class' => 'nob', 'disabled' => 'readonly', 'size' => '50']);
            $form->addElement('text', 'qty', _('Quantity'), ["size" => "3"]);
            $form->addElement('select', 'ref_type', _('Ref Type'), [
                '' => '',
                'SN' => _('Serial Number'),
                'WO' => _('Work Order'),
                'PO' => _('Purchase Order'),
                'DO' => _('Distribution Order'),
                'WHSE' => _('Warehouse'),
                'PARTS' => _('Spare Parts'),
                'PROD' => _('Production'),
                'WELD' => _('Welding Dept'),
                'CUST' => _('Customer'),
                'ER' => _('Unit s/n'),
                'OTHER' => _('Other'),
            ]);
            $form->addElement('text', 'ref', _('Ref#'), ["size" => "20"]);
            $form->addElement('text', 'sn', _('Component SN'), ["size" => "50"]);

            $form->addElement('submit', 'btnSubmit', _('Submit'), ['id' => 'button', 'disabled' => 'disabled']);
            // Add rule
            $required = array("problem", "models", "part_number", "qty", "location_type", "location","photo");
            foreach ($required as $field) {
                $form->addRule($field, _("Field required"), "required");
            }
            $form->addRule("qty", _("Must be numeric"), "numeric");
            $form->setDefaults(array("factory" => $LOCATION['id']));

            $form->setDefaults($default ?? []);
        } catch (\Throwable $exception) {
            $logger->error('NCR newNCR form failed to build: {message}', [
                'message' => $exception->getMessage(),
                'exception' => $exception,
            ]);
            $DEFAULT_ERROR[] = _("ERROR: Unable to display the New NCR form. Please try again or contact IT support.");
            $body = '';
            break;
        }

        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }

        $header = tldUtils::cleanupFormInput($form->exportValues());
        // We want newline but cleanupFormInput perform a mysqli_real_escape_string, and escape \r\n
        $header['problem'] = addslashes($form->exportValues('problem')['problem']);
        $header['investigation'] = addslashes($form->exportValues('investigation')['investigation']);

        // Convert to UTF8 depending on charset. This fix bug for chinese characters.
        $header = mb_convert_encoding($header, 'UTF-8', $charset === 'GB2312' ? $charset : 'ISO-8859-1');

        $file = $form->getElement("photo");
        $header['photo_info'] = $file->getValue();
        if(empty($header['photo_info']['name'])){
            $DEFAULT_ERROR[] = "ERROR: Please upload picture before submitting the NCR!<br/><input type=button value=back onclick='window.history.go(-1)'>";
            break;
        }
        //set the file attrs
        $file = $form->getElement("photo");
        $header['photo_info'] = $file->getValue();
        $ERP = tldLocation::getERPByID($header['factory']);
        $header['short_desc'] = substr($header['problem'], 0, 99);
        $header["part_number"] = trim($header["part_number"]);

        //Format values for API
        $factory = $client->findOneBy('/locations', ['erp' => $header['factory']]);

        try {
            $reporter = $client->find( '/people', $client->getUserId());
        } catch (\RangeException $e) {
            $DEFAULT_ERROR[] = "Reporter not found.";
            $body = $form->toHTML();
            return;
        }

        $payload = [
            'location' => $factory['@id'],
            'reportedBy' => $reporter['@id'],
            'iFactor' => sprintf('IF%s', $header['ifactor']),
            'hours' => (int) $header['hours'],
            'problem' => $header['problem'],
            'shortDescription' => $header['short_desc'],
            'rush' => (bool) $header['rush'],
            'investigation' => $header['investigation'],
        ];

        if ($header['source']) {
            $payload['crabId'] = (int) $header['source'];
        }

        try {
            $item = $client->get(sprintf('ion/items/site=%s;item=%s;', $factory['erp'], $header['part_number']));
        } catch (ClientException $e) {
            $DEFAULT_ERROR[] = $e->getMessage();
            $body = $form->toHTML();
            return;
        }

        $part = [
            'partNumber' => $header['part_number'],
            'description' => trim(TldDatabase::escape($item['itemDescription'])),
            'quantity' => (int) $header['qty'],
            'unitOfMeasure' => trim(TldDatabase::escape($item['unitOfMeasure'])),
            'reference' => $header['ref_type'],
            'referenceNumber' => $header['ref'],
            'serialNumber' => $header['sn'],
        ];

        if (\in_array('ENV', $header['models'])) {
            $payload['environmentalIssue'] = true;
            unset($header['models']);
        }

        if (\array_key_exists('models', $header) && \in_array('SAFETY', $header['models'])) {
            $payload['safety'] = true;
            unset($header['models']);
        }

        if (\array_key_exists('models', $header) && \in_array('OTHER', $header['models'])) {
            unset($header['models']);
        }

        $products = [];
        if (isset($header['models'])) {
            foreach ($header['models'] as $model) {
                try {
                    $product = $client->findOneBy('/sales/products', ['name' => $model]);
                    $products[] = $product['@id'];
                } catch (\RangeException $e) {
                    $DEFAULT_ERROR[] = "Product not found.";
                    $body = $form->toHTML();
                    return;
                }

            }
        }

        $payload['parts'] = [$part];
        $payload['products'] = $products;

        try {
            $ncr = $client->save('/quality/non_conformities', $payload);
        } catch (ClientException $exception) {
            $DEFAULT_ERROR[] = sprintf(_("Could not create new NCR. There was an error processing.
            The error returned is '%s'"), $exception->getMessage());
            return;
        }

        $fileManager = new FileManager($client);
        try {
            $fileManager->uploadFile($file->getValue(), $ncr, 'main_file');
        } catch (ClientException $exception) {
            $DEFAULT_ERROR[] = sprintf(_("Could not attach file NCR. Reason: '%s'"), $exception->getMessage());
            return;
        }

        for ($i = 1; $i < 5; $i++) {
            try {
                $file = $form->getElement("files[$i]");
                $file_array = $file->getValue();
                // Check if file uploaded
                if (empty($file_array['tmp_name'])) {
                    continue;
                }
                $fileManager->uploadFile($file_array, $ncr);
            } catch (ClientException $exception) {
                $DEFAULT_ERROR[] = sprintf(_("Could not attach file NCR. Reason: '%s'"), $exception->getMessage());
                return;
            }
        }
        // set NCR ID in session var
        $id = $ncr['id'];
        $sess["ncr"]["currentNCR"] = $id;
        tldUtils::log_event("NCR# $id created");
        $body .= "<h2>NCR#$id " . _("created") . "</h2>";

        // Re-direct to add parts
        $body .= "
<br/><meta http-equiv=\"refresh\" content=\"2;URL=$php_self?m[0]=ncr&m[1]=forms&m[2]=addParts\">
<a href=\"$php_self?m[0]=ncr&m[1]=forms&m[2]=addParts\">" . _("If the screen does not refresh automatically, click here") . ".</a>
";
    break;
	case 'byNumber':
		$form = new HTML_QuickForm('frmViewNCR', 'post');
		$form->addElement(	'header', 	'title', 	_('View NCR by number'));
		$form->addElement(	'hidden', 	'm[0]', 	'ncr');
		$form->addElement(	'hidden', 	'm[1]', 	'view');
		$form->addElement(	'hidden', 	'single', 	1);
		$form->addElement(	'text', 	'id', 		'NCR#',
			array("onFocus"=>"javascript:this.value=''"));
		$form->addElement(	'submit', 	'btnSubmit', _('Submit'));
		$form->addRule('id',_("Field required"),"required");
		$body = $form->toHTML();
	break;
    }
break;
case "view":

	if(empty($id) || !is_numeric($id)){
		$DEFAULT_ERROR[] = _("ERROR: NCR# sent empty or invalid");
        break;
	}
    try {
        $ncr = $client->find('/quality/non_conformities', $id, ['query' => ['normalizationGroups' => ['non_conformity:legacy']]]);
    } catch (ClientException $exception) {
        $DEFAULT_ERROR[] = sprintf(_("ERROR: Could not find NCR# %s"),$id);
        break;
    }

    $ncr['problem'] = mb_convert_encoding($ncr['problem'], 'ISO-8859-1', 'UTF-8');
    $ncr['investigation'] = mb_convert_encoding($ncr['investigation'], 'ISO-8859-1', 'UTF-8');
	$DEFAULT_MENU .="
 | <a href=\"$php_self?m[0]=crab&m[1]=newCRAB1&m[2]=transfer&module=NCR&ncr_id=$id&pn={$ncr['parts'][0]['partNumber']}\">"._("Transfer to")." CRAB</a>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href=\"$php_self?m[0]=ncr&m[1]=view&id=$id\">"._("General")."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=ncr&m[1]=view&m[2]=files&id=$id\">"._("Files")."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=ncr&m[1]=view&m[2]=tasks&id=$id\">"._("Tasks")."</a>
";

    $fileManager = new FileManager($client);
    switch($m[2] ?? null){
    case 'outPhoto':
        echo $fileManager->dowloadFile(sprintf('quality/non_conformities/%s/main_file/%s', $ncr['id'], $ncr['mainFile']['id']));
        exit;
    case 'files':
        switch($m[3] ?? null){
        case 'out':
            $fileId = TldDatabase::escape($_REQUEST['fid']);
            echo $fileManager->dowloadFile(sprintf('quality/non_conformities/%s/files/%s', $ncr['id'], $fileId));
            exit;
        default:
            $files = $ncr['files'];
            $body = include("$PATH/view/ncr.files.tpl.php");
        break;
        }
    break;
    case 'tasks':
        $form = new tldReportMultiLevel(
            tldTask::byParent($ncr['id'], 'NCR', 'ALL'),
			array("status", "due_date"),
            array(
            	"id"				=>_("Task#"),
                "status"			=>_("Status"),
                "due_date"			=>_("Due"),
                "task"				=>_("Task"),
                "assignee_fullname"	=>_("Assignee")
            ),
            array(
            	"passField"=>"id",
                "title"=>_("Tasks"),
                "url"=>"$php_self?m[0]=tasks&m[1]=view&id="
            )
        );
        $body = $form->fetch();
	break;
    default:
        $body = include("$PATH/view/ncr.print.tpl.inc.php");
        $responsibles = [];
        foreach($ncr['responsibles'] as $responsible) {
            $responsibles[] = $responsible['name'];
        }
        $ncr['responsibles'] = implode(', ', $responsibles);
		$headerDetail = new tldAssocTable(
		    $ncr,
			array(
				"status"		                    =>_("Status"),
				"createdAt"			                =>_("Date"),
				"reporterId"		                =>_("Reported By Employee ID#"),
				"reportedByFullName"	            =>_("Reported By"),
				"problem"		                    =>_("Problem Description"),
				"investigation"	                    =>_("Investigation"),
				"scrap"			                    =>_("Scrap?"),
				"rework"		                    =>_("TLD Rework?"),
				"useAsIs"		                    =>_("Use as is?"),
				"returnVendor"	                    =>_("Return to vendor?"),
				"chargeVendorForRepair"             =>_("Charge vendor for repairs?"),
				"supplierCorrectiveActionRequest"   =>_("SCAR?"),
				"other"			                    =>_("Other? See comment below"),
				"actionComment"		                =>_("Comments..."),
				"currencyName"		                =>_("Currency"),
				"cost"	                            =>_("Total Cost"),
				"costBreakdown"	                    =>_("Cost Breakdown"),
				"responsibles"		                =>_("Charge Type"),
			),
			array("doNotShowEmpty"=>true)
		);
		$body .= $headerDetail->fetch();
        // Links
        $body.= include("$PATH/view/ncr.links.report.tpl.php");
		// Parts
		$partsList = new tldReportColumnar(
		    $ncr['parts'],
	        array(
				"xItems"=>array(
					"partNumber"	    =>_("PN"),
					"description"	    =>_("Description"),
					"quantity"			=>_("Qty"),
					"reference"		    =>_("Ref Type"),
					"referenceNumber"   =>_("Ref#"),
					"serialNumber"		=>_("SN")
    	        ),
    			"title"=>_("Parts")
			)
		);
		$body .= $partsList->fetch();
	break;
    }
break;
case "lists":
	switch($m[2] ?? null){
	case "inProgress":
        $location = $client->findOneBy('/locations', ['legacyId' => $LOCATION['id']]);
        $rows = $client->findBy('/quality/non_conformities', ['location' => $location['@id'], 'status' => 'IN PROGRESS', 'normalizationGroups' => ['non_conformity:legacy']]);
		$myTitle = _("IN PROGRESS NCRs by Employee#");
	break;
	default:
        $location = $client->findOneBy('/locations', ['legacyId' => $LOCATION['id']]);
        $rows = $client->findBy('/quality/non_conformities', ['location' => $location['@id'], 'status' => 'PENDING', 'normalizationGroups' => ['non_conformity:legacy']]);
		$myTitle = _("PENDING NCRs by Employee#");
	break;
	}
	if(count($rows)){
		$sess["ncr"]["list"] = $rows;
		$form = new tldReportMultiLevel(
		    $rows,
			array("reporterId"),
			array(
                "id"		        =>_("NCR#"),
                "status"	        =>_("Status"),
                "createdAt"		    =>_("Date"),
                "shortDescription"  =>_("Description")
			),
			array(
				"passField"=>"id",
    			"title"=>$myTitle,
    			"url"=>"$php_self?m[0]=ncr&m[1]=view&id="
		    )
		);
		$body = $form->fetch();
	}
break;
default:
	$body = include("$PATH/homepage.ncr.tpl.inc.php");
    $location = $client->findOneBy('/locations', ['legacyId' => $LOCATION['id']]);
    $rows = $client->findBy('/quality/non_conformities', ['location' => $location['@id'], 'status' => 'PENDING', 'normalizationGroups' => ['non_conformity:legacy']]);
	if(count($rows)){
		$sess["ncr"]["list"] = $rows;
		$form = new tldReportMultiLevel(
		    $rows,
			['reporterId'],
			array(
				"id"		        =>_("NCR#"),
				"status"	        =>_("Status"),
                "createdAt"		    =>_("Date"),
				"shortDescription"  =>_("Description")
			),
			array(
    			"passField"=>"id",
    			"title"=>_("PENDING NCRs by Employee#"),
    			"url"=>"$php_self?m[0]=ncr&m[1]=view&id="
			)
		);
		$body .= $form->fetch();
	}
break;
}
