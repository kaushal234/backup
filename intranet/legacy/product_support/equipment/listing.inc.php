<?php

use ApiBundle\Client;
use Symfony\Component\HttpClient\Exception\ClientException;

include_once('quality.inc.php');

$xItems = [
    'id' => 'ID#',
    'sn' => 'SN#',
    'customer_name' => 'Customer Name',
    'user_customer_display' => 'Customer (END USER)',
    'buyer_customer_display' => 'Customer (BUYER)',
    'maintainer_customer_display' => 'Customer (MAINTAINER)',
    'model' => 'Model',
    'man_location' => 'Man Location',
    'sales_org' => 'SSO',
    'location_short' => 'Location short',
    'airport_code' => 'Airport Code',
    'date_shipped' => 'Ship Date',
    'status' => 'Status',
];

switch ($m[2] ?? null) {
    case 'byState':
        $rows = tldEquipment::byConstraints(['state' => 'RETIRED']);
        $_title = 'Equipment with State RETIRED';
        break;
    case 'byAllCustomerID':
        $x = TldDatabase::escape($x);
        $rows = tldEquipment::byAllCustomerID($x);
        $_title = "Equipment Records For customer#$x";
        break;
    case 'byProblemRecords':
        $query = <<<EOF
SELECT 
    service.id,
    service.sn,
    service.customer_name,
    service.model,
    service.man_location,
    service.sales_org,
    service.location_short,
    service.airport_code,
    service.date_shipped,
    service.status,
    IF(customer_id > 0, c1.customer_name, service.customer_name) AS user_customer_display,
    c2.customer_name AS buyer_customer_display
FROM service
LEFT JOIN customers c1 ON c1.id=service.customer_id
LEFT JOIN customers c2 ON c2.id=service.buyer_customer_id
WHERE (service.sn=''
  OR service.customer_name=''
  OR service.model=''
  OR service.type=''
  OR service.sales_org=''
  OR service.man_location=''
  OR service.location_short=''
  OR service.location_short='TBA'
  OR service.airport_code='')
  AND service.status != 'PENDING'
ORDER BY date_shipped
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);
        $_title = 'Equipment Records with Missing Data';
        break;
    case 'marketShareAnalysis':
        if ( $user->getID() != 108 && !$user->isInGroup('SUPERUSER') ) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permissions...';
            break;
        }
        $form = new HTML_QuickForm('frmByFactgory', 'post');
        $form->addElement('header', 'title', 'Market share analysis by year');
        $form->addElement('hidden', 'm[0]', 'equipment');
        $form->addElement('hidden', 'm[1]', 'listing');
        $form->addElement('hidden', 'm[2]', 'marketShareAnalysis');
        $form->addElement('date', 'year', 'Year', ['format' => 'Y', 'minYear' => date('Y') - 10, 'maxYear' => date('Y') ]);
        $form->addElement('submit', 'btnSubmit', 'Submit');

        if ($form->validate()) {
            $vars = tldUtils::cleanupFormInput($form->exportValues());
            $year = $vars['year']['Y'];
            $_title = 'Market share analysis by year';
            $xItems = [
                'id' => 'ID#',
                'sn' => 'SN#',
                'type' => 'Type',
                'model' => 'Model',
                'date_entered' => 'Date Entered',
                'date_shipped' => 'Date Shipped',
                'dgt_act' => 'Date GT',
                'user_customer_display' => 'Customer (END USER)',
                'buyer_customer_display' => 'Customer (BUYER)',
                'customer_name' => 'Customer Name',
                'ctry' => 'Del Country',
                'airport_code' => 'Airport Code',
                'sales_revenue' => 'Date Revenue SSO',
                'factory_revenue' => 'Date Revenue Factory',
                'sales_org' => 'SSO',
                'man_location' => 'Manufactory Location',
                'year' => 'Year',
            ];
            $query = <<<EOF
        SELECT 
        service.id, service.sn,service.date_shipped,service.type,service.model,service.airport_code,
               service.man_location,service.sales_org,sor_lines.ctry,LEFT(service.date_shipped,4) as year,
            IF(service.customer_id > 0,
               (SELECT cust.customer_name FROM customers AS cust WHERE cust.id=service.customer_id),
               service.customer_name) AS user_customer_display,
            (SELECT cust.customer_name FROM customers AS cust WHERE cust.id=service.buyer_customer_id) AS buyer_customer_display,
            service.customer_name, service.del_ctry, service.date_entered,service.dgt_act,
            (SELECT MAX(dtran)
             FROM sor_tran AS trans
             WHERE trans.parent_id=sor_lines.id AND trans.ttyp='R' and tgrp = 'ERP'
            ) AS factory_revenue,
            (SELECT MAX(dtran)
             FROM sor_tran AS trans
             WHERE trans.parent_id=sor_lines.id AND trans.ttyp='R' and tgrp = 'SSO'
            ) AS sales_revenue
        FROM service
             LEFT JOIN sor_units ON service.sor_uid=sor_units.id
             LEFT JOIN sor_lines ON sor_units.parent_id=sor_lines.id
             LEFT JOIN sor ON sor_lines.parent_id=sor.id
             LEFT JOIN customers ON sor.buyer_customer_id=customers.id
             LEFT JOIN locations AS loc_erp ON sor_lines.bu = loc_erp.id
             LEFT JOIN locations AS loc_sso ON sor.sso = loc_sso.id
        WHERE LEFT(service.date_shipped,4) LIKE '$year'
    order by service.id desc
EOF;
            $rows = tldUtils::getSqlToAssocArray($query);
            $_title = 'Market Share Analysis';
        } else {
            $body = $form->toHTML();
        }
    break;
    case 'byUnassigned':
        $rows = tldEquipment::byUnassigned();
        $_title = 'Equipment not assigned to a Sales Order Record';
        break;
    case 'byUnshipped':
        $rows = tldEquipment::byUnshipped();
        $_title = 'Equipment not Shipped';
        break;
    case 'byFactory':
        $form = new HTML_QuickForm('frmByFactgory', 'post');
        $form->addElement('header', 'title', 'Show equipment records by Factory');
        $form->addElement('hidden', 'm[0]', 'equipment');
        $form->addElement('hidden', 'm[1]', 'listing');
        $form->addElement('hidden', 'm[2]', 'byFactory');
        $form->addElement('hidden', 'm[3]', $m[3]);
        $form->addElement('select', 'factory', 'Factory', ['' => ''] +
            tldEquipment::getUsedFactoryList());
        $form->addElement('submit', 'btnSubmit', 'Submit');

        if ($form->validate()) {
            $_title = 'Equipment Records by Factory';
            $rows = tldEquipment::byConstraints(['man_location' => $factory]);
        } else {
            $body = $form->toHTML();
        }
        break;
    case 'search':
        $DEFAULT_TITLE .= "\Search";
        // Reset session
        $sess['er']['listing'] = null;
        // Get form
        $form = new HTML_QuickForm('frmSearch', 'post');
        $form->addElement('hidden', 'm[0]', 'equipment');
        $form->addElement('hidden', 'm[1]', 'listing');
        $form->addElement('hidden', 'm[2]', 'search');
        $form->addElement('header', 'title', 'Basic Search');
        $form->addElement('text', 'target', 'Search for');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('target', 'Required', 'required');

        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }
        $xItems = [
            'id' => 'ID#',
            'sn' => 'SN#',
            'cust_asset_num' => 'CUSTOMER ASSET #',
            'customer_name' => 'Customer Name',
            'user_customer_display' => 'Customer (END USER)',
            'buyer_customer_display' => 'Customer (BUYER)',
            'model' => 'Model',
            'man_location' => 'Man Location',
            'sales_org' => 'SSO',
            'location_short' => 'Location short',
            'airport_code' => 'Airport Code',
            'date_shipped' => 'Ship Date',
            'status' => 'Status',
            'hours' => 'Hourmeter',
            'dgt_rev' => 'Estimated GT Date',
            'dgt_act' => 'Actual GT date',
            'eng_tier' => 'Emission Rating',
            'dt_commissioned' => 'Commissioning date',
        ];
        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $_title = 'Basic search results';
        $rows = tldEquipment::search('%' . trim($vars['target']) . '%');
        break;
    case 'advSearch':
        $DEFAULT_TITLE .= "\Search";
        // Reset session
        $sess['er']['listing'] = null;
        // Get listing
        $factoryList = tldEquipment::getUsedFactoryList();
        $ssoList = tldLocation::getSalesOrgList('smartyOptionsLocationLocation');
        $customerTypeList = tldList::optionsByListNameAsListItemListItem('list.sales.customer.types');
        $modelList = array_column(tldEquipment::getUsedModels(), 'model', 'model');
        $typeList = array_column(tldUtils::getSqlToAssocArray('SELECT en FROM products_categories ORDER BY en'), 'en', 'en');
        $tierList = tldList::optionsByListNameAsListItemListItem('list.engine.tiers');
        $countryList = tldCountry::optionsAsNameName();
        $statusList = tldEquipment::getStatusList();
        $combModList = array_merge(tldEquipment::getCombinationModeList(), ['PAS & NO ER' => 'PAS & NO ER']);
        $solStatuses = tldSOL::getStatusList();
        $stateList = tldEquipment::getStateList();

        // Get form
        $form = new HTML_QuickForm('frmSearch', 'post');
        $form->addElement('hidden', 'm[0]', 'equipment');
        $form->addElement('hidden', 'm[1]', 'listing');
        $form->addElement('hidden', 'm[2]', 'advSearch');
        $form->addElement('header', 'title', 'Advanced Search');
        $form->addElement('select', 'status', 'Status', ['' => ''] + $statusList);
        $form->addElement('select', 'gtstatus', 'GT Status', ['' => '', 'yes' => 'Green Tagged', 'no' => 'Not Green Tagged']);
        $form->addElement('select', 'ytstatus', 'YT Status', ['' => '', 'yes' => 'Yellow Tagged', 'no' => 'Not Yellow Tagged']);
        $form->addElement('select', 'buyer_customer_id', 'Customer (BUYER)', ['' => ''], ['class' => 'select2-customer', 'style' => 'width: 100%']);
        $form->addElement('select', 'customer_id', 'Customer (END USER)', ['' => ''], ['class' => 'select2-customer', 'style' => 'width: 100%']);
        $form->addElement('select', 'maintainer_customer_id', 'Customer (MAINTAINER)', ['' => ''], ['class' => 'select2-customer', 'style' => 'width: 100%']);
        $form->addElement('select', 'user_customer_type', 'Customer (END USER) Type', ['' => '', 'empty' => 'No Type'] + $customerTypeList);
        $form->addElement('select', 'customer_name', 'Customer Name', ['' => ''], ['class' => 'select2-customer-name', 'style' => 'width: 100%']);

        $modelList = tldEquipment::getUsedModels();
        $list=[];
        foreach ($modelList as $item) {
            $list[$item['model']] = $item['model'];
        }
        $multiSelect =& $form->addElement('advmultiselect', 'models', null,
            $list,
            ['size' => 15,
                'class' => 'pool',
                'style' => 'width:300px;',
            ]
        );
        $multiSelect->setLabel(['Models', 'Model', 'Selected']);
        $multiSelect->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand',
        ]);
        $multiSelect->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);

        $form->addElement('select', 'type', 'Type', ['' => ''] + $typeList);
        $form->addElement('select', 'man_location', 'Factory', ['' => ''] + $factoryList);
        $form->addElement('select', 'sales_org', 'SSO', ['' => ''] + $ssoList);
        $form->addElement('select', 'sso_service', 'SSO Service', ['' => ''] + $ssoList);
        $form->addElement('text', 't_pdno', 'Main Work Order#', ['size' => 52]);
        $form->addElement('text', 't_prno', 'MFG Project#', ['size' => 52]);
        $form->addElement('select', 'eng_tier', 'Emission Rating', ['' => ''] + $tierList);
        $form->addElement('select', 'airport_code', 'Airport Code', ['' => ''], ['class' => 'select2-airports', 'style' => 'width: 100%']);
        $form->addElement('select', 'apc_country', 'Country of APC', ['' => ''] + $countryList);
        $form->addElement('text', 'cust_asset_num', 'Customer Asset#', ['size' => 52]);
        $form->addElement('select', 'comb_mod', 'Combination mode', ['' => ''] + $combModList);
        $form->addElement('text', 'dt_shipped_from', 'Shipped from', ['class' => 'datepicker']);
        $form->addElement('text', 'dt_shipped_to', 'Shipped to', ['class' => 'datepicker']);
        $form->addElement('text', 'options', 'Options', ['size' => 52]);
        $form->addElement('select', 'sol_status', 'SOL status', ['' => ''] + array_combine($solStatuses, $solStatuses));
        $form->addElement('select', 'state', 'State', ['' => ''] + $stateList);
        $form->addElement('checkbox', 'software', 'Include software ?');
        $form->addElement('checkbox', 'light', 'Only light');
        $form->addElement('submit', 'btnSubmit', 'Submit');

        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }
        $vars = tldUtils::cleanupFormInput($form->getSubmitValues());
        $_title = 'Advanced search results';
        $acl_form_fields = [
            'buyer_customer_id', 'customer_id', 'user_customer_type', 'maintainer_customer_id', 'customer_name', 'models', 'type', 'eng_tier', 'status', 'dt_shipped_from', 'dt_shipped_to', 'light',
            'man_location', 'sales_org', 'sso_service', 'airport_code', 't_prno', 't_pdno', 'cust_asset_num', 'del_ctry', 'apc_country', 'comb_mod', 'gtstatus', 'ytstatus', 'options', 'sol_status', 'state'
        ];
        $a = [];
        foreach ($vars as $key => $val) {
            if (empty($val) || !in_array($key, $acl_form_fields, true)) {
                continue;
            }
            switch ($key) {
                case 'gtstatus':
                    $operator = $val === 'yes' ? '!=' : '=';
                    $a[] = " service.dgt_act $operator'0000-00-00' ";
                    break;
                case 'ytstatus':
                    $operator = $val === 'yes' ? '!=' : '=';
                    $a[] = " service.dyt $operator'0000-00-00' ";
                    break;
                case 'comb_mod':
                    $a[] = $val === 'PAS & NO ER' ? " sn LIKE 'P%' AND parent_id = 0 " : " $key='$val' ";
                    break;
                case 'apc_country':
                    $a[] = " '$val' IN(SELECT countries.name FROM airport_codes AS apc LEFT JOIN countries ON countries.iso_code_2=apc.ctry_code_2 WHERE apc.airport_code=service.airport_code)";
                    break;
                case 'status':
                    $a[] = <<<EOF
'$val' LIKE (
CASE
	WHEN (SELECT COUNT(*) FROM csr WHERE csr.parent_id=service.id AND csr.work_type='Commissioning' AND csr.status IN ('COMPLETED', 'CLOSED')) > 0 THEN 'COMMISIONED'
	WHEN ddel_act2<>'0000-00-00' THEN 'DELIVERED'
	WHEN date_shipped != '0000-00-00' THEN 'SHIPPED'
	WHEN date_shipped='0000-00-00' THEN 'IN PRODUCTION'
	ELSE ''
END
)
EOF;
                    break;
                case 'dt_shipped_from':
                    $a[] = " DATEDIFF('$val',date_shipped)<=0 ";
                    break;
                case 'dt_shipped_to':
                    $a[] = " DATEDIFF('$val',date_shipped)>=0 ";
                    break;
                case 'options':
                    $a[] = " options_desc LIKE '%$val%' ";
                    break;
                case 'sol_status':
                    $a[] = " (SELECT sor_lines.status FROM sor_units INNER JOIN sor_lines ON sor_lines.id = sor_units.parent_id WHERE sor_units.id=service.sor_uid) = '$val' ";
                    break;
                case 'models':
                    $decoratedArrayFromVal = array_reduce($val, static function ($key, $value) {
                        $key[] = sprintf("'%s'", $value);

                        return $key;
                    });
                    $commaSeparated = implode(', ', $decoratedArrayFromVal);
                    $a[] = " service.model IN ($commaSeparated) ";
                    break;
                default:
                    $a[] = " $key='$val' ";
                    break;
            }
        }
        if (count($a) < 1) {
            $DEFAULT_ERROR[] = 'ERROR: Not enough constraints to run a safe search...';
            $body = $form->toHTML();
            break;
        }
        $where = implode(' AND ', $a);
        $sql = "SELECT COUNT(*) AS nb FROM service WHERE $where";
        $countResults = tldUtils::getSqlRowToAssocArray($sql);
        $limit = 25000;
        if (isset($var['software']) && $var['software']) {
            $limit = 500;
        }

        if ($countResults['nb'] > $limit) {
            $DEFAULT_ERROR[] = "ERROR: Not enough constraints to run a safe search, more than $limit has been found ({$countResults['nb']})";
            $body = $form->toHTML();
            break;
        }
        $xItems = [
            'id' => 'ID#',
            'sn' => 'SN#',
            'customer_name' => 'Customer Name',
            'user_customer_display' => 'Customer (END USER)',
            'buyer_customer_display' => 'Customer (BUYER)',
            'maintainer_customer_display' => 'Customer (MAINTAINER)',
            'cust_asset_num' => 'Customer Asset#',
            'type' => 'Type',
            'model' => 'Model',
            'man_location' => 'Man Location',
            't_pdno' => 'Main Work Order#',
            't_prno' => 'MFG Project#',
            'sales_org' => 'SSO',
            'sso_service' => 'SSO Service',
            'ddel_est1' => 'Factory promised delivery date',
            'del_dat' => 'Customer requested delivery date',
            'apc_fullname' => 'Location',
            'eng_tier' => 'Emission level',
            'airport_code' => 'Airport Code',
            'date_shipped' => 'Ship Date',
            'dgt_com' => 'First GT Date',
            'dgt_act' => 'Actual GT Date',
            'status' => 'Status',
            'hours' => 'Hourmeter',
            'warranty_conditions' => 'Special Warranty Condition',
            'calculated_date_warranty_end' => 'Warranty End Date',
            'dt_commissioned' => 'Commissioning Date',
            'del_ctry' => 'Country Delivery',
            'sol_id' => 'SOL#',
            'sor_id' => 'SOR#',
            'state' => 'State',
            'light_value' => 'Light',
        ];
        tldUtils::log_event('ER search start');
        $rows = tldEquipment::byConstraints($where);
        // CBOM call fixed BUT
        // this filter never worked, variable $var not exist, it's always false. Right variable is $vars
        // Could be fixed, but for each equiment record found we make a request to the ERP
        // This represent thousands of calls
        if (isset($var['software']) && $var['software']) {
            $xItems['soft'] = 'Software';
            foreach ($rows as $key => $val) {
                $soft = '';
                $eq = new tldEquipment($val['id']);
                $erp = tldLocation::getERPByLocation($eq->itsDetails['man_location']);
                $sn = $eq->itsDetails['t_prno'];

                $container = $kernel->getContainer();
                $client = $container->get(Client::class);
                try {
                    $cbom = $client->get(sprintf('/ion/customized_bill_of_materials/site=%d;project=%s', $erp, $sn));
                    $cbom = new tldCBOM($erp, $date, $sn, ['lang' => $selang], $cbom['items']);

                    $ERP = $cbom->getERP();
                    $date = $cbom->getDATE();
                    if (count($cbom->itsBOMAsArray)) {
                        foreach ($cbom->itsBOMAsArray as $item) {
                            if (in_array(trim($item['t_csig']), ['PRM', 'PRG'])) {
                                $soft .= $item['t_sitm'] . '(' . trim($item['t_dsca']) . ')' . '<br>';
                            }
                        }

                        $rows[$key]['soft'] = $soft;
                    }
                } catch (ClientException $exception) {
                    // do nothing
                }
            }
        }

        tldUtils::log_event('ER search end');
        break;
    case 'byComponent':
        global $kernel;
        try {
            $container = $kernel->getContainer();
            $client = $container->get(Client::class);
        } catch (\Exception $e) {
            $DEFAULT_ERROR[] = 'Client could not be fetched.';
            break;
        }

        $formattedComponents = [];
        try {
            $components = $client->get('equipment_serial_components', ['query' => ['order' => ['name' => 'ASC']]]);
            foreach ($components['hydra:member'] as $component) {
                $formattedComponents[$component['name']] = $component['name'];
            }
        } catch (ClientException $exception) {
            $DEFAULT_ERROR[] = 'Components could not be fetched.';
            break;
        }

        $additionalColumns = [
            'serial_component' => 'Component',
            'serial_model' => 'Component model',
            'serial_brand' => 'Component brand',
            'serial_serial' => 'Component serial',
        ];
        $form = new HTML_QuickForm('frmSearch', 'post');
        $form->addElement('header', 'title', 'Search equipment records by Component Information');
        $form->addElement('hidden', 'm[0]', 'equipment');
        $form->addElement('hidden', 'm[1]', 'listing');
        $form->addElement('hidden', 'm[2]', 'byComponent');
        $form->addElement('select', 'serial_component', 'Component', ['' => ''] + $formattedComponents);
        $form->addElement('text', 'serial_model', 'Component model');
        $form->addElement('text', 'serial_brand', 'Component brand');
        $form->addElement('text', 'serial_serial', 'Component serial');
        $form->addElement('checkbox', 'fms_link', 'For Link FMS ?');
        $form->addElement('submit', 'btnSubmit', 'Submit');

        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }
        $_title = 'Equipment Component search results';
        $vars = tldUtils::cleanupFormInput($form->exportValues());
        if (isset($vars['fms_link'])) {
            $additionalColumns['type'] = 'Type';
            $additionalColumns['calculated_fms_end_use_date'] = 'Calculated FMS End use date';
        }

        $xItems = array_merge($xItems, $additionalColumns);
        $fieldsToSearch = ['serial_component', 'serial_model', 'serial_brand', 'serial_serial'];
        $a = [];
        foreach ($vars as $field => $var) {
            if (empty($var) || !in_array($field, $fieldsToSearch, true)) {
                continue;
            }
            if ($field !== 'serial_component') {
                $var = "%$var%";
            }
            $a[$field] = $var;
        }
        if (count($a) === 0) {
            $DEFAULT_ERROR[] = 'ERROR: Not enough field set to do search...';
            $body = $form->toHTML();
            break;
        }
        $rows = tldEquipment::byComponentConstraints($a);
        break;
    case'byERP_YM':
        $rows = tldGT::byERPYearMonth($x, $y);
        $_title = "GT'd Equipment by Month and Factory - $x - $y";
        break;
    case 'byManualPN':
        $xItems = [
            'id' => 'ID#',
            'sn' => 'SN#',
            'manualid' => 'Manual#',
            'user_customer_display' => 'Customer (END USER)',
            'buyer_customer_display' => 'Customer (BUYER)',
            'model' => 'Model',
            'man_location' => 'Man Location',
            'airport_code' => 'Airport Code',
            'date_shipped' => 'Ship Date',
        ];
        $form = new HTML_QuickForm('frmSearch', 'get');
        $form->addElement('header', 'title', 'Search ER by manual PN');
        $form->addElement('hidden', 'm[0]', 'equipment');
        $form->addElement('hidden', 'm[1]', 'listing');
        $form->addElement('hidden', 'm[2]', 'byManualPN');
        $form->addElement('text', 'pn', 'PN#');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('pn', 'That is required', 'required');

        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $_title = "Equipment by PN manual '{$vars['pn']}'";
        $rows = tldEquipment::byManualPN($vars['pn']);
        break;
    case 'linkFMS':
        $xItems = [
            'parent_id' => 'id',
            'sn' => 'Unit S/N',
            'maintenance_contract_ref' => 'FMS contract',
            'man_location' => 'Manufacturing Location',
            'sales_org' => 'SSO',
            'user_customer_display' => 'Customer (END USER)',
            'buyer_customer_display' => 'Customer (BUYER)',
            'buyer_representative' => 'Buyer Representative',
            'airport_code' => 'Airport Code',
            'apc_country_name' => 'Country of APC',
            'unit_type' => 'Type',
            'unit_model' => 'Model',
            'sim_serial' => 'Sim card S/N',
            'sim_check' => 'Sim card S/N Error',
            'model' => 'Phone number ID',
            'brand' => 'Sim card Brand',
            'obu_serial' => 'OBU Serial',
            'obu_brand' => 'OBU Brand',
            'calculated_fms_end_use_date' => 'FMS end use date',
            'date_shipped' => 'Shipped date',
            'dgt_act' => 'Unit GT date'
        ];

        $customerList = tldCustomer::getList('smartyOptions');
        $asmList = tldGroup::getUserListByMultipleGroup(
            ['role_ASM'],
            null,
            ['smartyOptions' => 1]
        );
        // Get form
        $form = new HTML_QuickForm('frmLinkFMS', 'get');
        $form->addElement('hidden', 'm[0]', 'equipment');
        $form->addElement('hidden', 'm[1]', 'listing');
        $form->addElement('hidden', 'm[2]', 'linkFMS');
        $form->addElement('header', 'title', 'Link ER Search');
        $form->addElement('select', 'man_location', 'Factory', ['' => ''] + tldLocation::getFactoryList('smartyOptionsLocationLocation'));
        $form->addElement('select', 'sales_org', 'SSO', ['' => ''] + tldLocation::getSalesOrgList('smartyOptionsLocationLocation'));
        $form->addElement('select', 'buyer_customer_id', 'Customer (BUYER)', ['' => ''] + $customerList);
        $form->addElement('select', 'main_representative_id', 'BUYER Representative', ['' => ''] + $asmList);
        $form->addElement('select', 'customer_id', 'Customer (END USER)', ['' => ''] + $customerList);
        $form->addElement('select', 'model', 'Model', ['' => ''] + array_column(tldEquipment::getUsedModels(), 'model', 'model'));
        $form->addElement('select', 'type', 'Type', ['' => ''] + array_column(tldUtils::getSqlToAssocArray('SELECT en FROM products_categories ORDER BY en'), 'en', 'en'));
        $form->addElement('select', 'airport_code', 'Airport Code', ['' => ''] + tldAirport::getList());
        $form->addElement('select', 'apc_country', 'Country of APC', ['' => ''] + tldCountry::optionsAsNameName());
        $form->addElement('select', 'del_ctry', 'Delivery Country', ['' => ''] + tldCountry::optionsAsNameName());
        $form->addElement('select', 'sim_status', 'SIM status', ['' => ''] + tldEquipment::getSimStatusList());
        $form->addElement('select', 'sim_check', 'SIM error', ['' => ''] + ['' => '', 'N' => 'No', 'Y' => 'Yes']);
        $form->addElement('select', 'tld_link', 'TLD Link', ['' => ''] + ['' => '', 'N' => 'No', 'Y' => 'Yes']);
        $form->addElement('text', 'fms_end_use_date_from', 'FMS end use date from', ['class' => 'datepicker']);
        $form->addElement('text', 'fms_end_use_date_to', 'FMS end use date to', ['class' => 'datepicker']);
        $form->addElement('select', 'fms_contract', 'FMS Contract', ['' => ''] + ['AES' => 'AES', 'SAS' => 'SAS', 'TAS' => 'TAS', 'XOPS' => 'XOPS']);
        $form->addElement('text', 'shipped_from', 'Shipped from', ['class' => 'datepicker']);
        $form->addElement('text', 'shipped_to', 'Shipped to', ['class' => 'datepicker']);
        $form->addElement('submit', 'btnSubmit', 'Submit');

        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $_title = 'Advanced search results';
        $acl_form_fields = [
            'buyer_customer_id', 'customer_id', 'model', 'type', 'man_location', 'sales_org', 'shipped_from', 'shipped_to',
            'airport_code', 'apc_country', 'sim_status', 'sim_check', 'tld_link', 'fms_end_use_date_from', 'fms_end_use_date_to', 'fms_contract', 'main_representative_id', 'del_ctry'
        ];
        $a = [];
        foreach ($vars as $key => $val) {
            if (empty($val) || !in_array($key, $acl_form_fields, true)) {
                continue;
            }
            switch ($key) {
                case 'apc_country':
                    $a[] = " '$val' IN(SELECT countries.name FROM airport_codes AS apc LEFT JOIN countries ON countries.iso_code_2=apc.ctry_code_2 WHERE apc.airport_code=service.airport_code)";
                    break;
                case 'fms_contract':
                    $a[] = " service.maintenance_contract_ref='$val' ";
                    break;
                case 'fms_end_use_date_from':
                    $a[] = <<<EOF
'$val' <= (
CASE
    WHEN COALESCE(fms_end_use_date , '0000-00-00') != '0000-00-00' THEN fms_end_use_date
    WHEN fms_contract_length > 0 AND dgt_act != '0000-00-00' THEN DATE_ADD(dgt_act, INTERVAL (fms_contract_length + 3) MONTH)
    ELSE '0000-00-00'
END
)
EOF;
                    break;
                case 'fms_end_use_date_to':
                    $a[] = <<<EOF
'$val' >= (
CASE
    WHEN COALESCE(fms_end_use_date , '0000-00-00') != '0000-00-00' THEN fms_end_use_date
    WHEN fms_contract_length > 0 AND dgt_act != '0000-00-00' THEN DATE_ADD(dgt_act, INTERVAL (fms_contract_length + 3) MONTH)
    ELSE '0000-00-00'
END
)
EOF;
                    break;
                case 'sim_check':
                    $a [] = sprintf("%s(REPLACE(service_serials.serial, ' ', '') REGEXP '^\\\\d{20}') ", $val === 'Y' ? 'NOT' : '');
                    break;
                case 'tld_link':
                    $a [] = sprintf('service.tld_link=%d', $val === 'Y' ? 1 : 0);
                    break;
                case 'maintenance_contract_ref':
                    $a[] = <<<EOF
'$val' = ( IF (maintenance_contract_ref = 'XOPS' OR maintenance_contract_ref = 'AES') fms_end_use_date = '9/9/2999' )
EOF;
                    break;
                case 'shipped_from':
                    $a[] = " DATEDIFF('$val',date_shipped)<=0 ";
                    break;
                case 'shipped_to':
                    $a[] = " DATEDIFF('$val',date_shipped)>=0 ";
                    break;
                case 'main_representative_id':
                    $a[] = "customers.asm_id='$val' ";
                    break;
                default:
                    $a[] = " service.$key='$val' ";
                    break;
            }
        }

        if (count($a) < 1) {
            $DEFAULT_ERROR[] = 'ERROR: Not enough constraints to run a safe search...';
            $body = $form->toHTML();
            break;
        }

        $rows = tldEquipment::getFMSSimCardReport(implode(' AND ', $a));
        $reportOptions = [
            'xItems' => $xItems,
            'showNumberOfRows' => true,
            'title' => 'TLD SIM CARD report',
            'links' => [
                'parent_id' => "$php_self?m[0]=equipment&m[1]=view&id=",
            ],
            'stickyHeader' => true,
        ];

        $xItems += [
            'sim_status' => 'SIM Card: Terminate or Pause',
            'sim_ownership' => 'SIM Card: Ownership change to',
        ];
        if ($rows) {
            $smarty->assign('width', '100%');
        }
        break;
    case 'linkFMSSold':
        $xItems = [
            'id' => 'id',
            'sn' => 'S/N',
            'transaction_date' => 'SSO revenue transaction date',
            'dgt_act' => 'Actual GT Date',
            'buyer_customer_display' => 'Customer (BUYER)',
            'user_customer_display' => 'Customer (END USER)',
            'sales_org' => 'SSO',
            'man_location' => 'Factory',
            'sim_card_model' => 'SIM CARD Model',
            'sim_serial' => 'SIM CARD Serial',
            'obu_serial' => 'OBU Serial',
            'calculated_fms_end_use_date' => 'FMS end use date',
        ];

        $customerList = tldCustomer::getList('smartyOptions');
        $_title = 'Units sold with Link by sales date';
        // Get form
        $form = new HTML_QuickForm('linkFMSSold');
        $form->addElement('hidden', 'm[0]', 'equipment');
        $form->addElement('hidden', 'm[1]', 'listing');
        $form->addElement('hidden', 'm[2]', 'linkFMSSold');
        $form->addElement('header', 'title', 'Units sold with Link by sales date');
        $form->addElement('select', 'sales_org', 'SSO', ['' => ''] + tldLocation::getSalesOrgList('smartyOptionsLocationLocation'));
        $form->addElement('select', 'man_location', 'Factory', ['' => ''] + tldLocation::getFactoryList('smartyOptionsLocationLocation'));
        $form->addElement('text', 'sso_transaction_from', 'SSO revenue transaction from', ['class' => 'datepicker']);
        $form->addElement('text', 'sso_transaction_to', 'SSO revenue transaction until', ['class' => 'datepicker']);
        $form->addElement('text', 'fms_end_use_date_from', 'FMS end use date from', ['class' => 'datepicker']);
        $form->addElement('text', 'fms_end_use_date_to', 'FMS end use date to', ['class' => 'datepicker']);
        $form->addRule('sso_transaction_month', 'This is required', 'required');
        $form->addElement('submit', 'btnSubmit', 'Submit');

        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $_title = 'Advanced search results';
        $acl_form_fields = ['man_location', 'sales_org', 'sso_transaction_from', 'sso_transaction_until', 'fms_end_use_date_from', 'fms_end_use_date_to'];
        $a = [];
        foreach ($vars as $key => $val) {
            if (empty($val) || !in_array($key, $acl_form_fields, true)) {
                continue;
            }
            switch ($key) {
                case 'sso_transaction_from':
                    $a[] = " sor_tran.dtran >= '$val' ";
                    break;
                case 'sso_transaction_to':
                    $a[] = " sor_tran.dtran <= '$val' ";
                    break;
                case 'fms_end_use_date_from':
                    $a[] = <<<EOF
'$val' <= (
CASE
    WHEN COALESCE(service.fms_end_use_date , '0000-00-00') != '0000-00-00' THEN service.fms_end_use_date
    WHEN service.fms_contract_length > 0 AND service.dgt_act != '0000-00-00' THEN DATE_ADD(service.dgt_act, INTERVAL (service.fms_contract_length + 3) MONTH)
    ELSE '0000-00-00'
END
)
EOF;
                    break;
                case 'fms_end_use_date_to':
                    $a[] = <<<EOF
'$val' >= (
CASE
    WHEN COALESCE(service.fms_end_use_date , '0000-00-00') != '0000-00-00' THEN service.fms_end_use_date
    WHEN service.fms_contract_length > 0 AND service.dgt_act != '0000-00-00' THEN DATE_ADD(service.dgt_act, INTERVAL (service.fms_contract_length + 3) MONTH)
    ELSE '0000-00-00'
END
)
EOF;
                    break;
                default:
                    $a[] = " service.$key='$val' ";
                    break;
            }
        }

        if (count($a) < 1) {
            $DEFAULT_ERROR[] = 'ERROR: Not enough constraints to run a safe search...';
            $body = $form->toHTML();
            break;
        }

        $rows = tldEquipment::getFMSSoldReport(implode(' AND ', $a));
        break;
}

if ('csv' === ($m[3] ?? null)) {
    $report = new tldCSV(
        $sess['er']['listing'],
        [
            'xItems' => $sess['er']['xitems'],
            'showTitles' => true,
        ]
    );
    $report->out();
    exit;
}

if ('xls' === ($m[3] ?? null)) {
    $report = new tldXLS(
        $sess['er']['listing'],
        [
            'xItems' => $sess['er']['xitems'],
            'showTitles' => true,
        ]
    );
    $report->out();
    exit;
}

if (!empty($rows)) {
    $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=equipment&m[1]=listing&m[3]=csv">CSV</a>
&nbsp;|&nbsp;
<a href="$php_self?m[0]=equipment&m[1]=listing&m[3]=xls">XLS</a>
EOF;
    $sess['er']['listing'] = $rows;
    $sess['er']['xitems'] = $xItems;
    $sess['equipment']['list'] = $rows;
} else {
    $sess['er']['listing'] = null;
}
if (!empty($sess['er']['listing'])) {
    $report = new tldReportColumnar(
        $sess['er']['listing'],
        $reportOptions ?? [
            'xItems' => $sess['er']['xitems'],
            'title' => $_title,
            'links' => [
                'id' => "$php_self?m[0]=equipment&m[1]=view&id=",
                'manualid' => "$php_self?m[0]=publications&m[1]=manuals&m[2]=view&id=",
            ],
            'showItemNumbers' => true,
        ]
    );
    $body .= $report->fetch();
    $body .= '<br><b>Total rows = '.count($rows).'</b>';
} elseif (isset($rows)) {
    $body .= 'No records...';
}
