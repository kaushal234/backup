<?php
require_once('HTML/QuickForm/autocomplete.php');
$DEFAULT_TITLE .= "\Reports";

switch ($m[1]) {
	case 'listing':
		switch ($m[2]) {
			case 'MeapTaskList':
				$DEFAULT_TITLE .= "\MEAP Tasks List";
				// Get listing
				$factoryList = tldLocation::getFactoryList('smartyOptionsIDLocation');
				// Get form
				$form = new HTML_QuickForm('frmERP', 'post', '', '', '', true);
				$form->addElement('hidden', 'm[0]', 'reports');
				$form->addElement('hidden', 'm[1]', 'listing');
				$form->addElement('hidden', 'm[2]', 'MeapTaskList2');
				$form->addElement('header', 'title', 'Select Factory:');
				$form->addElement('select', 'factory', 'Factory',
					['' => ''] + $factoryList);
				$form->addElement('submit', 'btnSubmit', 'Submit');
				$form->addRule('factory', 'This is required', 'required');
				$form->setDefaults(['factory' => $user->getBUID()]);
				$body = $form->toHTML();
				break;
			case 'MeapTaskList2':
				$DEFAULT_TITLE .= "\MEAP Tasks List";
				if (empty($factory) || !is_numeric($factory)) {
					$DEFAULT_ERROR[] = 'ERROR: Factory not set...';
					break;
				}
				$xItems = [
					'meap_id' => 'MEAP#',
					'eap_id' => 'EAP#',
					'eap_status' => 'Status',
					'factory' => 'Factory',
					'eap_desc' => 'Description',
					'task_id' => 'Task#',
					'task_dt_open' => 'Date Opened',
					'assignor_fullname' => 'Assignor',
					'assignee_fullname' => 'Assignee',
					'task_status' => 'Task Status',
					'task_desc' => 'Task Desc',
					'est_time' => 'Estimated Time',
					'timekeeping' => 'Timekeeping',
					'due_date' => 'Due Date',
				];
				// Get listing
				$meapList = tldMEAP::getOpenMEAPListByFactory($factory);
				// Get form
				$form = new HTML_QuickForm('frmERP', 'get', '', '', '', true);
				$form->addElement('hidden', 'm[0]', 'reports');
				$form->addElement('hidden', 'm[1]', 'listing');
				$form->addElement('hidden', 'm[2]', 'MeapTaskList2');
				$form->addElement('hidden', 'factory', $factory);
				$form->addElement('header', 'title', 'Select MEAP:');
				$form->addElement('select', 'meap_id', 'MEAP#',
					['' => '', 'ALL' => 'ALL'] + $meapList);
				$form->addElement('submit', 'btnSubmit', 'Submit');
				$form->addRule('meap_id', 'This is required', 'required');

				if ($form->validate()) {
					$res = tldUtils::cleanupFormInput($form->exportValues());
					$meap_list = [];
					if ($res['meap_id'] === 'ALL') {
						$meap_list = array_keys($meapList);
					} else {
						$meap_list[] = $res['meap_id'];
					}
					$rows = [];
					$links = [
						'meap_id' => [
							'url' => '/en/private/manufacturing/eng/dev.php?m[0]=meap&m[1]=view',
							'params' => ['id' => 'meap_id'],
						],
						'eap_id' => [
							'url' => '/en/private/manufacturing/eng/dev.php?m[0]=eap&m[1]=view',
							'params' => ['id' => 'eap_id'],
						],
						'task_id' => [
							'url' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view',
							'params' => ['id' => 'task_id'],
						],
					];
					$caption = 'MEAP#' . $res['meap_id'] . ' Tasks List - ' . tldLocation::getLocationByID($factory);
					foreach ($meap_list as $val) {
						$meap = new tldMEAP($val);
						$task = $meap->getTaskSummary();
						foreach ($task as $i => $iValue) {
							$task[$i]['meap_id'] = $val;
						}
						$rows = array_merge($rows, $task);
					}
				} else {
					$body = $form->toHTML();
				}
				break;
			case 'SEQReport':
				$xItems = [
					'id' => 'Task#',
					'module' => 'Module',
					'status' => 'Status',
					'step' => 'Current Step',
					'due_date' => 'Due',
					'assignor_fullname' => 'Assignor',
					'assignee_fullname' => 'Assignee',
					'task' => 'Task',
					'pn' => 'PN#',
				];
                $mQueryParams = $m;
				if (preg_match('/UID([0-9]+)/', $x, $mQueryParams)) {
					$x = $mQueryParams[1];
					$rows = tldTask::byItemSEQStatusAssignor($x, $y);
					$caption = 'SEQ New Item COUNT by Current Step, Assignor';
				} else {
					if ($x !== 'ALL') {
						$x = tldLocation::getERPByLocation($x);
					}
					$rows = tldTask::byItemSEQStatusLocation($x, $y);
					$caption = 'SEQ New Item COUNT by Current Step, Location';
				}
				$links = [
					'id' => [
						'url' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=',
						'params' => ['id' => 'id'],
					],
				];


				break;
			case 'BoxesReport':
				$xItems = [
					'mitm' => 'main item',
					'dsca' => 'description',
					'item' => 'component',
					'dscb' => 'description',
					'opno' => 'box',
					'qana' => 'quantity',
					'blvl' => 'level'
					//"seqn"=>"sequence"
				];
				// Get listing
				$erpList = tldLocation::getERPList('smartyOptions');
				// Get form
				$form = new HTML_QuickForm('frmBoxesReport', 'get', '', '', '', true);
				$form->addElement('hidden', 'm[0]', 'reports');
				$form->addElement('hidden', 'm[1]', 'listing');
				$form->addElement('hidden', 'm[2]', 'BoxesReport');
				$form->addElement('header', 'title', 'Select project and company:');
				$form->addElement('text', 'Project', 'Project');
				$form->addElement('text', 'CBOM', 'CBOM');
				$form->addElement('select', 'z', 'Company#',
					['' => ''] + $erpList);
				$form->addElement('submit', 'btnSubmit', 'Submit');
				$form->addRule('z', 'This is required', 'required');

				// Get default ERP for connected user
				$location = new tldLocation($user->getBUID());
				$form->setDefaults(['z' => $location->getERP()]);

				if ($form->validate()) {
					$vars = tldUtils::cleanupFormInput($form->exportValues());

					if ($vars['Project'] == '') {
						$vars['Project'] = '0';
					}
					if ($vars['CBOM'] == '') {
						$vars['CBOM'] = '0';
					}

					$rows = tldUtils::getSqlToAssocArray("EXEC Box_Report '{$vars['Project']}','{$vars['CBOM']}','{$vars['z']}'", 'odbc', ['src' => 'baan']);
					$caption = "Boxes report for project {$vars['z']} for company {$vars['z']}";
				} else {
					$body = $form->toHTML();
				}
				break;


			case 'SearchBOMMulti':
				$xItems = [
					'posi' => 'Detailed Level',
					'posi2' => 'Level',
					'sitm' => 'Component',
					'dscb' => 'Description',
					'mitm' => 'Manufactured item',
					'dsca' => 'Description',
					'pono' => 'Position',
					'seqn' => 'Sequence',
					'indt' => 'Effective date',
					'exdt' => 'Expiry date',
					'qana' => 'Quantity',
					'cwar' => 'Warehouse',
					'cpha' => 'Phantom',
				];
				// Get listing
				$erpList = tldLocation::getERPList('smartyOptions');
				// Get form
				$form = new HTML_QuickForm('frmSearchBOMMulti', 'get', '', '', '', true);
				$form->addElement('hidden', 'm[0]', 'reports');
				$form->addElement('hidden', 'm[1]', 'listing');
				$form->addElement('hidden', 'm[2]', 'SearchBOMMulti');
				$form->addElement('header', 'title', 'Select Item and company:');
				$form->addElement('text', 'Item', 'Item');
				$form->addElement('select', 'z', 'Company#', ['' => ''] + $erpList);
				$form->addElement('select', 'Activ', 'Only active components', ['N' => 'N', 'Y' => 'Y']);
				$form->addElement('submit', 'btnSubmit', 'Submit');
				$form->addRule('z', 'This is required', 'required');

				if ($form->validate()) {
					$vars = tldUtils::cleanupFormInput($form->exportValues());

					$rows = tldUtils::getSqlToAssocArray("EXEC Search_Tool_BOMMulti '{$vars['Item']}','{$vars['z']}','{$vars['Activ']}'", 'odbc', ['src' => 'baan']);
					$caption = "Where used item {$vars['Item']} in Multi-level PBOMs for company {$vars['z']}";
                    $today = date('Y-m-d');
					$links = [
						'mitm' => [
							'url' => "/en/private/manufacturing/eng/dev.php?m[0]=bom&m[1]=view&btnSubmit=Submit&erp={$vars['z']}&date=$today",
							'params' => ['pn' => 'mitm'],
						],
					];
				}
				$body = $form->toHTML();
				break;

			case 'SearchMagic':
				$xItems = [
					't_comp' => 'Company',
					't_item' => 'Item',
					't_dsca' => 'Description',
					't_dscb' => 'XREF desc.',
					't_suno' => 'Vendor#',
					't_nama' => 'Name',
					't_kitm' => 'Item Type',
					't_citg' => 'Item Group',
					't_ctyp' => 'Product Type',
					't_csel' => 'Owner',
					't_csig_edm' => 'Signal Code',
					't_engi' => 'Engineer',
					't_cuni' => 'Unit',
					'PMOC' => 'PMOC',
					't_cpha' => 'Phantom',
					't_draw' => 'Drawing',
				];
				// Get form
				$form = new HTML_QuickForm('frmSearchMagic', 'get', '', '', '', true);
				$form->addElement('hidden', 'm[0]', 'reports');
				$form->addElement('hidden', 'm[1]', 'listing');
				$form->addElement('hidden', 'm[2]', 'SearchMagic');
				$form->addElement('header', 'title', 'Select Item and company:');
                $form->addElement('select', 'erp', 'Location', [ "0" => 'ALL'] + tldLocation::getFactoryList("smartyOptions"));
				$form->addElement('text', 'SearchText', 'Search text');
				$form->addElement('text', 'MaxResult', 'Max results');
				$form->addElement('submit', 'btnSubmit', 'Submit');
				$form->addRule('z', 'This is required', 'required');

				$form->setDefaults(
					[
						'MaxResult' => '200',
					]
				);


				if ($form->validate()) {
					$vars = tldUtils::cleanupFormInput($form->exportValues());

					$vars['SearchText'] = '%' . $vars['SearchText'] . '%';

					$rows = tldUtils::getSqlToAssocArray("EXEC Search_Tool_Magic '{$vars['SearchText']}','{$vars['MaxResult']}','{$vars['erp']}' ", 'odbc', ['src' => 'baan']);
					$caption = "Search item containing {$vars['SearchText']} ";
                    $caption.= ($vars['erp'] === "0") ? "for all companies" : "for erp {$vars['erp']}";

					$today = date('Y-m-j');
					$links = [
						't_draw' => [
							'url' => "$php_self?m[0]=getfile&m[1]=drawing&date=$today",
							'params' => [
								'item' => 't_item',
								'erp' => 't_comp',
							],
						],
					];

				}
				$body = $form->toHTML();
				break;

			case 'SearchOutbound':
				$xItems = [
					'orno' => 'Order',
					'koor' => 'Order type',
					'procc' => 'Shipped',
					'pono' => 'Position',
					'item' => 'Item',
					'dsca' => 'Description',
					'qstr' => 'Quantity',
					'cwar' => 'Warehouse',
					'cpha' => 'Phantom',
				];
				// Get listing
				$erpList = tldLocation::getERPList('smartyOptions');
				// Get form
				$form = new HTML_QuickForm('frmSearchOutbound', 'get', '', '', '', true);
				$form->addElement('hidden', 'm[0]', 'reports');
				$form->addElement('hidden', 'm[1]', 'listing');
				$form->addElement('hidden', 'm[2]', 'SearchOutbound');
				$form->addElement('header', 'title', 'Select Item and company:');
				$form->addElement('text', 'Item', 'Item');
				$form->addElement('select', 'z', 'Company#',
					['' => ''] + $erpList);
				$form->addElement('submit', 'btnSubmit', 'Submit');
				$form->addRule('z', 'This is required', 'required');


				if ($form->validate()) {
					$vars = tldUtils::cleanupFormInput($form->exportValues());

					$rows = tldUtils::getSqlToAssocArray("EXEC Search_Tool_Outbound '{$vars['Item']}','{$vars['z']}'", 'odbc', ['src' => 'baan']);
					$caption = "Where used item {$vars['Item']} in Outbound for company {$vars['z']}";
				}//else{
				$body = $form->toHTML();
				//}
				break;

			case 'SearchBOMMono':
				$xItems = [
					'mitm' => 'Manufactured Item',
					'mbom' => 'Link to BOM',
					'reva' => 'Revision',
					'dscb' => 'Description',
					'sitm' => 'Component',
					'revb' => 'Revision',
					'dsca' => 'Description',
					'pono' => 'Position',
					'seqn' => 'Sequence',
					'qana' => 'Net quantity',
					'opno' => 'Operation',
					'indt' => 'Effective date',
					'exdt' => 'Expiry date',
					'cwar' => 'Warehouse',
					'cpha' => 'Phantom',
					'draw' => 'Drawing',
				];
				// Get listing
				$erpList = tldLocation::getERPList('smartyOptions');
				// Get form
				$form = new HTML_QuickForm('frmSearchBOMMono', 'get', '', '', '', true);
				$form->addElement('hidden', 'm[0]', 'reports');
				$form->addElement('hidden', 'm[1]', 'listing');
				$form->addElement('hidden', 'm[2]', 'SearchBOMMono');
				$form->addElement('header', 'title', 'Select Item and company:');
				$form->addElement('text', 'Item', 'Item');
				$form->addElement('select', 'z', 'Company#',
					['' => ''] + $erpList);
				$form->addElement('select', 'Activ', 'Only active components',
					['N' => 'N', 'Y' => 'Y']);
				$form->addElement('submit', 'btnSubmit', 'Submit');
				$form->addRule('z', 'This is required', 'required');

				if ($form->validate()) {
					$vars = tldUtils::cleanupFormInput($form->exportValues());

					$rows = tldUtils::getSqlToAssocArray("EXEC Search_Tool_BOMMono '{$vars['Item']}','{$vars['z']}','{$vars['Activ']}'", 'odbc', ['src' => 'baan']);
					$caption = "Where used item {$vars['Item']} in Mono-level PBOMs for company {$vars['z']}";

					foreach ($rows as &$value) {
						$value['mbom'] = 'Link';
						$value['draw'] = 'Drawing';
					}

					$today = date('Y-m-j');
					$links = [
						'mbom' => [
							'url' => "/en/private/manufacturing/eng/dev.php?m[0]=bom&m[1]=view&erp={$vars['z']}&date=$today&btnSubmit=Submit",
							'params' => [
								'pn' => 'mitm',
							],
						],
						'sitm' => [
							'url' => "/en/private/manufacturing/eng/dev.php?_qf__frmSearchBOMMulti=&m[0]=reports&m[1]=listing&m[2]=SearchBOMMulti&z={$vars['z']}&Activ=N&btnSubmit=Submit",
							'params' => [
								'Item' => 'sitm',
							],
						],
						'draw' => [
							'url' => "$php_self?m[0]=getfile&m[1]=drawing&erp={$vars['z']}&date=$today",
							'params' => [
								'item' => 'mitm',
							]],
					];

				}//else{
				$body = $form->toHTML();
				//}
				break;

			case 'SearchWO':
				$flagSearchWO = 1;
				$xItems = [
					'pdno' => 'Work order',
					'osta' => 'WO status',
					'cprj' => 'Project',
					'psts' => 'Prj status',
					'sdat' => 'Prj start date',
					'mitm' => 'Manufactured item',
					'pono' => 'Position',
					'dsca' => 'Description',
					'revi' => 'Revision',
					'qune' => 'Net quantity',
					'opno' => 'operation',
					'cwar' => 'Warehouse',
					'cpha' => 'Phantom',
					'clot' => 'Equipment record',
					'ship' => 'ER Ship Date',
					'end_user' => 'End User Customer',
					'buyer_user' => 'Buyer Customer',
					'model' => 'Model',

				];
				// Get listing
				$erpList = tldLocation::getERPList('smartyOptions');
				$osta = ['All', 'Free', 'Simulated', 'Active', 'Canceled', 'Finished', 'Closed', 'Archived'];

				// Get form
				$form = new HTML_QuickForm('frmSearchWO', 'get', '', '', '', true);
				$form->addElement('hidden', 'm[0]', 'reports');
				$form->addElement('hidden', 'm[1]', 'listing');
				$form->addElement('hidden', 'm[2]', 'SearchWO');
				$form->addElement('header', 'title', 'Select Item and company:');
				$form->addElement('text', 'Item', 'Item');
				$form->addElement('select', 'Project_Status', 'Project Status', array_combine($osta, $osta));
				$form->addElement('select', 'z', 'Company#', ['' => ''] + $erpList);
				$form->addElement('submit', 'btnSubmit', 'Submit');
				$form->addRule('z', 'This is required', 'required');

				if ($form->validate()) {
					$vars = tldUtils::cleanupFormInput($form->exportValues());

					$rows = tldUtils::getSqlToAssocArray("EXEC Search_Tool_WO '{$vars['Item']}','{$vars['Project_Status']}','{$vars['z']}'", 'odbc', ['src' => 'baan']);

					foreach ($rows as &$value) {
						// Date format
                        $value['sdat'] = substr($value['sdat'], 0, 10);
						if (strpos($value['sdat'], '1900') === 0) {
							$value['sdat'] = '';
						}

						// Add ER ship date
						$query = <<<SQL
    SELECT
        IF(customer_id > 0,(SELECT customers.customer_name FROM customers WHERE customers.id=service.customer_id),	customer_name) AS end_user,
        (SELECT customers.customer_name FROM customers WHERE customers.id=service.buyer_customer_id) AS buyer_user,
        model,
        date_shipped AS ship
    FROM service
    WHERE sn='{$value['clot']}'
SQL;
                        $value = array_merge($value, tldUtils::getSqlRowToAssocArray($query));
					}

					$caption = "Where used item {$vars['Item']} in WO for company {$vars['z']}";
				}//else{
				$links = [
					'clot' => [
						'url' => '/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=search&m[2]=bySN&Submit=Find',
						'params' => ['sn' => 'clot'],
					],
				];
				$body = $form->toHTML();
				//}
				break;

			case 'SearchItem':
				$xItems = [
					't_comp' => 'Company',
					't_item' => 'Item',
					't_dsca' => 'Description',
					't_dscb' => 'XREF desc.',
					't_kitm' => 'Item Type',
					't_citg' => 'Item Group',
					't_ctyp' => 'Product Type',
					't_csel' => 'Owner',
					//"t_csel2"=>"Owner company",
					't_csig_edm' => 'Signal Code',
					't_engi' => 'Engineer',
					't_cuni' => 'Unit',
					'PMOC' => 'PMOC',
					't_stoc' => 'Inventory on hand',
					't_stoc2' => 'Inv. Detail',
					't_cwar' => 'Warehouse',
					't_cpha' => 'Phantom',
					't_draw' => 'Drawing',

				];
				// Get listing
				//$erpList = tldLocation::getFactoryList("smartyOptions");
				$erpList = tldLocation::getERPList('smartyOptions');

				$erpList['ALL'] = 'ALL';
				$erpList['EDM'] = 'EDM';

				$itemtypes = ['', 'Purchased', 'Manufactured', 'Generic', 'Cost', 'Service', 'Subcontracting'];
				$yesno = ['No', 'Yes'];

				// Get form
				$form = new HTML_QuickForm('frmSearchItem', 'get', '', '', '', true);
				$form->addElement('hidden', 'm[0]', 'reports');
				$form->addElement('hidden', 'm[1]', 'listing');
				$form->addElement('hidden', 'm[2]', 'SearchItem');
				$form->addElement('header', 'title', 'Select Description and company:');
				$form->addElement('header', 'comment', 'You can use wildcard % in search fields');
				$form->addElement('select', 'z', 'Company#', ['' => ''] + $erpList);
				$form->addElement('text', 'Item', 'Item');
				$form->addElement('text', 'Dsca', 'Description');
				$form->addElement('text', 'Dscb', 'Xref description');
				$form->addElement('select', 'Item_Type', 'Item Type', array_combine($itemtypes, $itemtypes));
				$form->addElement('text', 'Item_Group', 'Item Group');
				$form->addElement('text', 'Product_Type', 'Product Type');
				$form->addElement('text', 'Owner', 'Owner');
				$form->addElement('text', 'Signal_Code', 'Signal Code');
				$form->addElement('text', 'Engineer', 'Engineer');
				$form->addElement('text', 'Unit', 'Unit');
				$form->addElement('text', 'PMOC', 'PMOC');
				$form->addElement('text', 'MaxResult', 'Max results');

				$form->addElement('submit', 'btnSubmit', 'Submit');
				$form->addRule('z', 'This is required', 'required');


				$MaxResult = (empty($_GET['MaxResult'])) ? '200' : $_GET['pn_to'];
				$form->setDefaults(['MaxResult' => $MaxResult]);


				if ($form->validate()) {
					$vars = tldUtils::cleanupFormInput($form->exportValues());

					// translate item type
					if ($vars['Item_Type'] === 'Purchased') {
						$vars['Item_Type'] = '1';
					}
					if ($vars['Item_Type'] === 'Manufactured') {
						$vars['Item_Type'] = '2';
					}
					if ($vars['Item_Type'] === 'Generic') {
						$vars['Item_Type'] = '3';
					}
					if ($vars['Item_Type'] === 'Cost') {
						$vars['Item_Type'] = '4';
					}
					if ($vars['Item_Type'] === 'Service') {
						$vars['Item_Type'] = '5';
					}
					if ($vars['Item_Type'] === 'Subcontracting') {
						$vars['Item_Type'] = '6';
					}
					$rows = tldUtils::getSqlToAssocArray("EXEC Search_Tool_Item '{$vars['Item']}','{$vars['Dsca']}','{$vars['Dscb']}','{$vars['Item_Type']}','{$vars['Item_Group']}','{$vars['Product_Type']}','{$vars['Owner']}','{$vars['Signal_Code']}','{$vars['Engineer']}','{$vars['Unit']}','{$vars['PMOC']}','{$vars['z']}','{$vars['MaxResult']}','{$vars['Advanced_Search']}'", 'odbc', ['src' => 'baan']);
					$caption = "Item search for company {$vars['z']}";

					$today = date('Y-m-j');

					$links = [
						't_draw' => [
							'url' => "$php_self?m[0]=getfile&m[1]=drawing&date=$today",
							'params' => [
								'item' => 't_item',
								'erp' => 't_csel2',
							],
						],
						't_stoc2' => [
							'url' => "$php_self?m[0]=avail&m[1]=view&erp={$vars['z']}",
							'params' => [
								'item' => 't_item',
							],
						],
						't_dscb' => [
							'url' => "/en/private/manufacturing/pur/dev.php?m[0]=xref&m[1]=searchByAltPNByTLDPN&erpid={$vars['z']}",
							'params' => [
								'pn' => 't_item',
							],
						],
						't_item' => [
							'url' => '/en/private/parts/parts.php?m[0]=inv&m[1]=view',
							'params' => [
								'id' => 't_item',
							],
						],
					];

				}
				$body = $form->toHTML();


				break;

			case 'EdmItmRevi':
				$xItems = [
					'item' => 'Item',
					'dsca' => 'description',
					'revi' => 'EDM revision',
				];
				// Get listing
				$erpList = tldLocation::getERPList('smartyOptions');
				// Get form
				$form = new HTML_QuickForm('frmEdmItmRevi', 'get', '', '', '', true);
				$form->addElement('hidden', 'm[0]', 'reports');
				$form->addElement('hidden', 'm[1]', 'listing');
				$form->addElement('hidden', 'm[2]', 'EdmItmRevi');
				$form->addElement('header', 'title', 'Select company:');
				$form->addElement('select', 'z', 'Company#',
					['' => ''] + $erpList);
				$form->addElement('submit', 'btnSubmit', 'Submit');
				$form->addRule('z', 'This is required', 'required');

				if ($form->validate()) {
					$vars = tldUtils::cleanupFormInput($form->exportValues());


					$query = <<<EOF
select
F1.t_item as item,
F1.t_dsca as dsca,
(select
	F2.t_revi
 from
 	ttiedm100400 F2
  where
  	F2.t_eitm=F1.t_item and
  	F2.t_indt=
  	(select
  		max(F3.t_indt)
  	from
  		ttiedm100400 F3
  	where
  		F3.t_eitm=F1.t_item )) as revi
from
	ttiitm001{$vars['z']} F1
where
	F1.t_item not in (select t_item from ttiedm101{$vars['z']})
and
	F1.t_item in (select t_eitm from ttiedm010{$vars['z']})
EOF;

					$rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
					$caption = "Item revision discrepancies between EDM & ITM for company {$vars['z']}";
				} else {
					$body = $form->toHTML();
				}
				break;
			case 'ItmWODrawing':
				$xItems = [
					'item' => 'Item',
					'dsca' => 'description',
					'revi' => 'EDM revision',
					'product' => 'Product Type',
					'owner' => 'Owner',
					'citg' => 'Item Group',
				];

				$OwnerList = tldLocation::getBAANOwnerList('smartyOptions');
				$ProductList = tldCBOM::getBAANProductTypeList('smartyOptions');
				// Get form
				$form = new HTML_QuickForm('frmEdmItmRevi', 'get', '', '', '', true);
				$form->addElement('hidden', 'm[0]', 'reports');
				$form->addElement('hidden', 'm[1]', 'listing');
				$form->addElement('hidden', 'm[2]', 'ItmWODrawing');
				$form->addElement('header', 'title', 'Select filters:');
				$form->addElement('text', 't_eitm', 'P/N - Assembly P/N');
				$form->addElement('select', 't_ctyp', 'Product Type', ['' => ''] + $ProductList);
				$form->addElement('select', 't_csel', 'Owner', ['' => ''] + $OwnerList);
				$form->addElement('submit', 'btnSubmit', 'Submit');

				if ($form->validate()) {
					$vars = tldUtils::cleanupFormInput($form->exportValues());

					if (empty($vars['t_eitm']) && empty($vars['t_ctyp'])) {
						$DEFAULT_ERROR[] = 'ERROR: Not enough constraints for search!';
						break;
					}

					if (!empty($vars['t_eitm'])) {
						$pn = " and F1.t_eitm LIKE '" . $vars['t_eitm'] . "%'";
					}
					if (!empty($vars['t_ctyp'])) {
						$product = " and F1.t_ctyp LIKE '" . $vars['t_ctyp'] . "'";
					}
					if (!empty($vars['t_csel'])) {
						$owner = " and F1.t_csel = '" . $vars['t_csel'] . "'";
					}

					$query = <<<EOF
SELECT
F1.t_eitm AS item,
F1.t_dsca AS dsca,
F1.t_citg AS citg,
F1.t_ctyp AS product,
F1.t_csel AS owner,
F2.t_revi AS revi
FROM
ttiedm010400 AS F1
LEFT JOIN ttiedm100400 AS F2 ON F2.t_eitm = F1.t_eitm
WHERE
F2.t_exdt = '1753-01-01 00:00:00.000' and
F2.t_cdrw = ' '
$pn
$product
$owner
EOF;

					$rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
					$caption = "Items with missing drawings - Product Type '" . $vars['t_ctyp'] . "'";
					$links = [
						'item' => [
							'url' => '/en/private/manufacturing/eng/dev.php?m[0]=edm&m[1]=view&erp=400&item=',
							'params' => ['item' => 'item'],
						],
					];
				} else {
					$body = $form->toHTML();
				}
				break;
		}

		if (isset($rows, $caption)) {
			$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[3]=xls">XLS version</a>&nbsp;|&nbsp;
<a href="{$_SERVER['REQUEST_URI']}&m[3]=csv">CSV version</a>
EOF;
			switch ($m[3]) {
				case 'xls':
					$report = new tldXLS(
						$rows,
						[
							'xItems' => $xItems,
							'showTitles' => true,
						]
					);
					$report->out();
					exit;
					break;
				case 'csv':
					$report = new tldCSV(
						$rows,
						[
							'xItems' => $xItems,
							'showTitles' => true,
						]
					);
					$report->out();
					exit;
					break;
				default:
					$report = new tldReportColumnar($rows,
						[
							'xItems' => $xItems,
							'title' => $caption,
							'links' => $links,
						]

					);
					$body .= $report->fetch();
					if ($flagSearchWO == 1) {
						$body .= '<br><b>Total rows = ' . count($rows) . '</b>';
					}
					break;
			}
		}
		break;
	default:
		$body = $smarty->fetch("$PATH/reports/homepage.reports.tpl");
		break;
}
