<?php
if (!$user->isInGroup('role_MISM')) {
    $DEFAULT_ERROR[] = "ERROR: You do not have permissions";

    return;
}

$DEFAULT_TITLE .= '\Administration';
$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=inventory&m[1]=admin">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=inventory&m[1]=admin&m[2]=types">Item types</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=inventory&m[1]=admin&m[2]=brands">Brands</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=inventory&m[1]=admin&m[2]=buildings">Buildings</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=inventory&m[1]=admin&m[2]=labelprint">Label print by patch</a>
EOF;

switch ($m[2]) {
    case 'labelprint':
        switch ($m[3]) {
            case 'print':
                Tld_Mis_Inventory_Item::outPDFLabel($_SESSION['data_print']);
            exit;
        }
        $divisions = tldRegion::getListAsIdDivision();
        $typeList = Tld_Mis_Inventory_ItemType::getListAsIdName();
        $brandList = Tld_Mis_Inventory_ItemBrand::getListAsIdName();
        $stateList = Tld_Mis_Inventory_Item::getStateList();
        $locationList = tldLocation::getLocationList("smartyOptions");
        $erpList = tldLocation::getERPList("smartyOptionsIDLocation");
        $dptList = tldDepartment::getListAsIdDepartment();
        $categoryList = Tld_Mis_Inventory_ItemType::getCategoryList();
        $form = new HTML_QuickForm('frmNew', 'get', '', '', '', true);
        $form->addElement('hidden', 'm[0]', 'inventory');
        $form->addElement('hidden', 'm[1]', 'admin');
        $form->addElement('hidden', 'm[2]', 'labelprint');
        $form->addElement('header', 'title', "List item to print label");
        $form->addElement('select', 'category', 'Category', ["" => ""] + $categoryList);
        $form->addElement('select', 'type_id', 'Type', ["" => ""] + $typeList);
        $form->addElement('select', 'brand_id', 'Brand', ["" => ""] + $brandList);
        $form->addElement('select', 'buyer_bu_id', 'Requestor BU', ["" => ""] + $erpList);
        $form->addElement('select', 'buyer_dpt_id', 'Requestor Department', ["" => ""] + $dptList);
        $form->addElement('text', 'dt_from', 'OPEN date from',
            ["class" => "datepicker"]);
        $form->addElement('text', 'dt_to', 'OPEN date to',
            ["class" => "datepicker"]);
        $form->addElement('text', 'start_id', "Start ID");
        $form->addElement('text', 'end_id', "End ID");
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('start', 'Should be numeric', 'numeric');
        $form->addRule('end', 'Should be numeric', 'numeric');

        if (!$form->validate()) {
            $body .= $form->toHTML();
        }
        if ($user->isInGroup(["gg_MIS"])) {
            $acl_form_fields = [
                "destination_id", "location", "type_id", "brand_id", "state", "buyer_bu_id", "buyer_dpt_id", "hidden", "description", "dt_from", "dt_to", "start_id", "end_id","category",
            ];
        } else {
            $acl_form_fields = ["dt_from", "dt_to"];
        }
        $a = [];
        foreach ($_REQUEST as $key => $raw) {
            if (!in_array($key, $acl_form_fields) || empty($raw)) continue;
            if (in_array($raw, ["%"])) continue;
            if (in_array($key, ["bu_id", "parent_id"])) {
                $key = "T1.$key";
            }

            // Construct constraint query
            switch ($key) {
                case "dt_from";
                    try {
                        $date = new DateTime($raw);
                    } catch (Exception $e) {
                        continue 2;
                    }
                    $date = $date->format('Y-m-d');
                    $a[] = " DATEDIFF('$date',t1.dt)<=0 ";
                    break;
                case "dt_to";
                    try {
                        $date = new DateTime($raw);
                    } catch (Exception $e) {
                        continue 2;
                    }
                    $date = $date->format('Y-m-d');
                    $a[] = " DATEDIFF('$date',t1.dt)>=0 ";
                    break;
                case "start_id";
                    $raw = TldDatabase::escape($raw);
                    $a[] = " t1.id >=$raw ";
                    break;
                case "end_id";
                    $raw = TldDatabase::escape($raw);
                    $a[] = " t1.id <=$raw ";
                    break;
                case "buyer_bu_id":
                    $raw = TldDatabase::escape($raw);
                    $a[] = " t1.buyer_bu_id=$raw ";
                    break;
                case "destination_id":
                    $raw = TldDatabase::escape($raw);
                    $a[] = " t4.destination_id =$raw  ";
                    break;
                case "location":
                    $raw = TldDatabase::escape($raw);
                    $a[] = " t5.name LIKE '%$raw%'  ";
                    break;
                case "category":
                    $raw = TldDatabase::escape($raw);
                    $a[] = " t2.category LIKE '%$raw%'  ";
                    break;
                default:
                    $raw = TldDatabase::escape($raw);
                    if (is_numeric($raw)) {
                        $a[] = " $key=$raw ";
                    } elseif (is_string($raw)) {
                        $a[] = " $key LIKE '$raw' ";
                    }
                    break;
            }
        }
        if (empty($a)) {
            $DEFAULT_ERROR[] = "ERROR: Not enough constraints to run a safe search...";
            break;
        }
        $constraints = implode("AND", $a);
        $rows = Tld_Mis_Inventory_Module::advSearch($constraints);
        $_SESSION['data_print'] = $rows;
        $_TITLE = "Item list";
        if (count($rows) == 0) {
            $DEFAULT_ERROR[] = "ERROR: No matches found...";
            break;
        }
        $form = new tldReportColumnar(
            $rows,
            [
                "xItems" => $xItems,
                "title" => $_title,
                "links" => ["id" => "$php_self?m[0]=inventory&m[1]=view&id="],
            ]
        );
        if($rows){
            $DEFAULT_MENU .= <<<EOF
    		<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="$php_self?m[0]=inventory&m[1]=admin&m[2]=labelprint&m[3]=print">Print Labels</a>
EOF;
        }
        $body .= $form->fetch();
        break;
    break;
    case 'types':
        $DEFAULT_TITLE .= '\Item types';
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=inventory&m[1]=admin&m[2]=types">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=inventory&m[1]=admin&m[2]=types&m[3]=add">Add</a>
EOF;

        // Listing
        $categoryList = Tld_Mis_Inventory_ItemType::getCategoryList();

        switch ($m[3]) {
            case 'add':
                $DEFAULT_TITLE .= '\Add';
                // form
                $form = new HTML_QuickForm('frmNew', 'post');
                $form->addElement('hidden', 'm[0]', 'inventory');
                $form->addElement('hidden', 'm[1]', 'admin');
                $form->addElement('hidden', 'm[2]', 'types');
                $form->addElement('hidden', 'm[3]', 'add');
                $form->addElement('header', 'title', "Create new item type");
                $form->addElement('text', 'name', "Type name");
                $form->addElement('select', 'category', 'Category', ["" => ""] + $categoryList);
                $form->addElement('text', 'short_desc', "Prefix Name(3 letters)", ['size="3"', 'maxlength="3"']);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->addRule('name', 'Required', 'required');
                $form->addRule('category', 'Required', 'required');
                $form->addRule('short_desc', 'Required', 'required');
                $form->applyFilter(['name', 'short_desc'], 'strtoupper');

                if (!$form->validate()) {
                    $body .= $form->toHTML();
                    break;
                }

                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $e = Tld_Mis_Inventory_ItemType::insert($vars);
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "ERROR: Can not add new type. Reason: $e";
                    break;
                }
                $body .= "<p>Item type successfully addded!</p>";
                break;
            case 'view':
                if (!isset($id) || !is_numeric($id)) {
                    $DEFAULT_ERROR[] = "ERROR: Parameters empty or invalid";
                    break;
                }

                $type = new Tld_Mis_Inventory_ItemType($id);
                if ($type->isEmpty()) {
                    $DEFAULT_ERROR[] = "ERROR: Item type #$id not found";
                    break;
                }

                $DEFAULT_TITLE .= "Type#$id";

                switch ($m[4]) {
                    case 'edit':
                        $DEFAULT_TITLE .= '\Edit';
                        // form
                        $form = new HTML_QuickForm('frmNew', 'post');
                        $form->addElement('hidden', 'm[0]', 'inventory');
                        $form->addElement('hidden', 'm[1]', 'admin');
                        $form->addElement('hidden', 'm[2]', 'types');
                        $form->addElement('hidden', 'm[3]', 'view');
                        $form->addElement('hidden', 'm[4]', 'edit');
                        $form->addElement('hidden', 'id', $id);
                        $form->addElement('header', 'title', "Edit item type#$id");
                        $form->addElement('text', 'name', "Type name");
                        $form->addElement('select', 'category', 'Category', ["" => ""] + $categoryList);
                        $form->addElement('text', 'short_desc', "Prefix Name(3 letters)", ['size="3"', 'maxlength="3"']);
                        $form->addElement('submit', 'btnSubmit', 'Submit');
                        $form->addRule('name', 'Required', 'required');
                        $form->addRule('category', 'Required', 'required');
                        $form->addRule('short_desc', 'Required', 'required');
                        $form->applyFilter(['name', 'short_desc'], 'strtoupper');
                        $form->setDefaults($type->header);

                        if (!$form->validate()) {
                            $body .= $form->toHTML();
                            break;
                        }

                        $vars = tldUtils::cleanupFormInput($form->exportValues());
                        $e = $type->update($vars, ['name', 'category', 'short_desc']);
                        if (is_string($e)) {
                            $DEFAULT_ERROR[] = "ERROR: Can not update item type. Reason: $e";
                            break;
                        }
                        $body .= "<p>Item type successfully updated!</p>";
                        break;
                    case 'delete':
                        $DEFAULT_TITLE .= '\Delete';
                        // Check if used
                        $used = false;
                        $nbImpact = Tld_Mis_Inventory_Item::byTypeId($id);
                        if ($nbImpact['num'] > 0) {
                            $used = true;
                        }
                        // listing
                        $replaceTypeList = Tld_Mis_Inventory_ItemType::getListAsIdName();
                        unset($replaceTypeList[$id]); // remove the one we are going to delete
                        // form
                        $form = new HTML_QuickForm('frmNew', 'post');
                        $form->addElement('hidden', 'm[0]', 'inventory');
                        $form->addElement('hidden', 'm[1]', 'admin');
                        $form->addElement('hidden', 'm[2]', 'types');
                        $form->addElement('hidden', 'm[3]', 'view');
                        $form->addElement('hidden', 'm[4]', 'delete');
                        $form->addElement('hidden', 'id', $id);
                        $form->addElement('header', 'title', "WARNING! This type is used ({$nbImpact['num']} items impacted)");
                        $form->addElement('select', 'replace_id', "Replace {$type->header['name']} with", ["" => ""] + $replaceTypeList);
                        $form->addElement('submit', 'btnSubmit', 'Submit');
                        $form->addRule('replace_id', 'Required', 'required');

                        if (!$form->validate() && $used) {
                            $body .= $form->toHTML();
                            break;
                        } elseif ($form->validate() && $used) {
                            // replace items
                            $vars = tldUtils::cleanupFormInput($form->exportValues());
                            $e = Tld_Mis_Inventory_Item::replaceTypeById($id,$vars['replace_id']);
                            if (is_string($e)) {
                                $DEFAULT_ERROR[] = "ERROR: Can not delete item type due to impact update issue. Reason: $e";
                                break;
                            }
                            $body .= "<p>Items impacted successfully updated!</p>";
                        }

                        // finally delete
                        $e = $type->delete();
                        if (is_string($e)) {
                            $DEFAULT_ERROR[] = "ERROR: Can not delete item type. Reason: $e";
                            break;
                        }
                        $body .= "<p>Item type successfully deleted!</p>";
                        break;
                }
                break;
        }

        $report = new tldReportColumnar(
            Tld_Mis_Inventory_ItemType::getList(),
            [
                "xItems" => [
                    'id' => '#',
                    'name' => 'Type Name',
                    'category' => 'Category',
                    'short_desc' => 'Prefix Name',
                ],
                "title" => "Item type list",
                "functions" => [
                    "Edit" => [
                        "url" => "$php_self?m[0]=$m[0]&m[1]=$m[1]&m[2]=$m[2]&m[3]=view&m[4]=edit",
                        "param" => ['id' => 'id'],
                        "img" => "/shared/icons/application/edit.png",
                    ],
                    "Delete" => [
                        "url" => "$php_self?m[0]=$m[0]&m[1]=$m[1]&m[2]=$m[2]&m[3]=view&m[4]=delete",
                        "param" => ['id' => 'id'],
                        "img" => "/shared/icons/application/delete.png",
                        "confirmPopup" => 'Are you sure to delete?',
                    ],
                ],
            ]
        );
        $body .= $report->fetch();
        break;
    case 'brands':
        $DEFAULT_TITLE .= '\Brands';
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=inventory&m[1]=admin&m[2]=brands">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=inventory&m[1]=admin&m[2]=brands&m[3]=add">Add</a>
EOF;

        switch ($m[3]) {
            case 'add':
                $DEFAULT_TITLE .= '\Add';
                // form
                $form = new HTML_QuickForm('frmNew', 'post');
                $form->addElement('hidden', 'm[0]', 'inventory');
                $form->addElement('hidden', 'm[1]', 'admin');
                $form->addElement('hidden', 'm[2]', 'brands');
                $form->addElement('hidden', 'm[3]', 'add');
                $form->addElement('header', 'title', "Create new brand");
                $form->addElement('text', 'name', "Brand name");
                $form->addElement('text', 'support_url', "Support URL, http://");
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->addRule('name', 'Required', 'required');

                if (!$form->validate()) {
                    $body .= $form->toHTML();
                    break;
                }

                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $e = Tld_Mis_Inventory_ItemBrand::insert($vars);
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "ERROR: Can not add new brand. Reason: $e";
                    break;
                }
                $body .= "<p>Brand successfully addded!</p>";
                break;
            case 'view':
                if (!isset($id) || !is_numeric($id)) {
                    $DEFAULT_ERROR[] = "ERROR: Parameters empty or invalid";
                    break;
                }

                $brand = new Tld_Mis_Inventory_ItemBrand($id);
                if ($brand->isEmpty()) {
                    $DEFAULT_ERROR[] = "ERROR: Brand #$id not found";
                    break;
                }

                $DEFAULT_TITLE .= "\Brand#$id";

                switch ($m[4]) {
                    case 'edit':
                        $DEFAULT_TITLE .= '\Edit';
                        // form
                        $form = new HTML_QuickForm('frmNew', 'post');
                        $form->addElement('hidden', 'm[0]', 'inventory');
                        $form->addElement('hidden', 'm[1]', 'admin');
                        $form->addElement('hidden', 'm[2]', 'brands');
                        $form->addElement('hidden', 'm[3]', 'view');
                        $form->addElement('hidden', 'm[4]', 'edit');
                        $form->addElement('hidden', 'id', $id);
                        $form->addElement('header', 'title', "Edit brand #$id");
                        $form->addElement('text', 'name', "Brand name");
                        $form->addElement('text', 'support_url', "Support URL, http://");
                        $form->addElement('submit', 'btnSubmit', 'Submit');
                        $form->addRule('name', 'Required', 'required');
                        $form->setDefaults($brand->header);

                        if (!$form->validate()) {
                            $body .= $form->toHTML();
                            break;
                        }

                        $vars = tldUtils::cleanupFormInput($form->exportValues());
                        $e = $brand->update($vars, ['name', 'support_url']);
                        if (is_string($e)) {
                            $DEFAULT_ERROR[] = "ERROR: Can not update brand. Reason: $e";
                            break;
                        }
                        $body .= "<p>Brand successfully updated!</p>";
                        break;
                    case 'delete':
                        $DEFAULT_TITLE .= '\Delete';
                        // Check if used
                        $used = false;
                        $nbImpact = Tld_Mis_Inventory_Item::byBrandId($id);
                        if ($nbImpact['num'] > 0) {
                            $used = true;
                        }
                        // listing
                        $replaceList = Tld_Mis_Inventory_ItemBrand::getListAsIdName();
                        unset($replaceList[$id]); // remove the one we are going to delete
                        // form
                        $form = new HTML_QuickForm('frmNew', 'post');
                        $form->addElement('hidden', 'm[0]', 'inventory');
                        $form->addElement('hidden', 'm[1]', 'admin');
                        $form->addElement('hidden', 'm[2]', 'brands');
                        $form->addElement('hidden', 'm[3]', 'view');
                        $form->addElement('hidden', 'm[4]', 'delete');
                        $form->addElement('hidden', 'id', $id);
                        $form->addElement('header', 'title', "WARNING! This brand is used ({$nbImpact['num']} items impacted)");
                        $form->addElement('select', 'replace_id', "Replace {$brand->header['name']} with", ["" => ""] + $replaceList);
                        $form->addElement('submit', 'btnSubmit', 'Submit');
                        $form->addRule('replace_id', 'Required', 'required');

                        if (!$form->validate() && $used) {
                            $body .= $form->toHTML();
                            break;
                        } elseif ($form->validate() && $used) {
                            // replace items
                            $vars = tldUtils::cleanupFormInput($form->exportValues());
                            $e = Tld_Mis_Inventory_Item::replaceBrandById($id,$vars['replace_id']);
                            if (is_string($e)) {
                                $DEFAULT_ERROR[] = "ERROR: Can not delete brand due to impact update issue. Reason: $e";
                                break;
                            }
                            $body .= "<p>Items impacted successfully updated!</p>";
                        }

                        // finally delete
                        $e = $brand->delete();
                        if (is_string($e)) {
                            $DEFAULT_ERROR[] = "ERROR: Can not delete item brand. Reason: $e";
                            break;
                        }
                        $body .= "<p>Item brand successfully deleted!</p>";
                        break;
                }
                break;
        }

        $report = new tldReportColumnar(
            Tld_Mis_Inventory_ItemBrand::getList(),
            [
                "xItems" => [
                    'id' => '#',
                    'name' => 'Brand',
                    'support_url' => 'Support URL',
                ],
                "title" => "Item brand list",
                "cellModifier" => [
                    'support_url' => [
                        'type' => 'simple_link',
                        'prefix' => 'http://',
                    ],
                ],
                "functions" => [
                    "Edit" => [
                        "url" => "$php_self?m[0]=$m[0]&m[1]=$m[1]&m[2]=$m[2]&m[3]=view&m[4]=edit",
                        "param" => ['id' => 'id'],
                        "img" => "/shared/icons/application/edit.png",
                    ],
                    "Delete" => [
                        "url" => "$php_self?m[0]=$m[0]&m[1]=$m[1]&m[2]=$m[2]&m[3]=view&m[4]=delete",
                        "param" => ['id' => 'id'],
                        "img" => "/shared/icons/application/delete.png",
                        "confirmPopup" => 'Are you sure to delete?',
                    ],
                ],
            ]
        );
        $body .= $report->fetch();
        break;
    case 'buildings':
        $DEFAULT_TITLE .= '\Buildings';
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=inventory&m[1]=admin&m[2]=buildings">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=inventory&m[1]=admin&m[2]=buildings&m[3]=add">Add</a>
EOF;

        // listing
        $locationList = tldLocation::getLocationList("smartyOptions");

        switch ($m[3]) {
            case 'add':
                $DEFAULT_TITLE .= '\Add';
                // form
                $form = new HTML_QuickForm('frmNew', 'post');
                $form->addElement('hidden', 'm[0]', 'inventory');
                $form->addElement('hidden', 'm[1]', 'admin');
                $form->addElement('hidden', 'm[2]', 'buildings');
                $form->addElement('hidden', 'm[3]', 'add');
                $form->addElement('header', 'title', "Create new building");
                $form->addElement('text', 'name', "Building name");
                $form->addElement('select', 'location_id', 'Location', ["" => ""] + $locationList);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->addRule('name', 'Required', 'required');
                $form->addRule('location_id', 'Required', 'required');

                if (!$form->validate()) {
                    $body .= $form->toHTML();
                    break;
                }

                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $e = Tld_Mis_Inventory_Building::insert($vars);
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "ERROR: Can not add new building. Reason: $e";
                    break;
                }
                $body .= "<p>Building successfully addded!</p>";
                break;
            case 'view':
                if (!isset($id) || !is_numeric($id)) {
                    $DEFAULT_ERROR[] = "ERROR: Parameters empty or invalid";
                    break;
                }

                $building = new Tld_Mis_Inventory_Building($id);
                if ($building->isEmpty()) {
                    $DEFAULT_ERROR[] = "ERROR: Building #$id not found";
                    break;
                }

                $DEFAULT_TITLE .= "\Building#$id";

                switch ($m[4]) {
                    case 'edit':
                        $DEFAULT_TITLE .= '\Edit';
                        // form
                        $form = new HTML_QuickForm('frmNew', 'post');
                        $form->addElement('hidden', 'm[0]', 'inventory');
                        $form->addElement('hidden', 'm[1]', 'admin');
                        $form->addElement('hidden', 'm[2]', 'buildings');
                        $form->addElement('hidden', 'm[3]', 'view');
                        $form->addElement('hidden', 'm[4]', 'edit');
                        $form->addElement('hidden', 'id', $id);
                        $form->addElement('header', 'title', "Edit building #$id");
                        $form->addElement('text', 'name', "Name");
                        $form->addElement('select', 'location_id', 'Location', ["" => ""] + $locationList);
                        $form->addElement('submit', 'btnSubmit', 'Submit');
                        $form->addRule('name', 'Required', 'required');
                        $form->addRule('location_id', 'Required', 'required');
                        $form->setDefaults($building->header);

                        if (!$form->validate()) {
                            $body .= $form->toHTML();
                            break;
                        }

                        $vars = tldUtils::cleanupFormInput($form->exportValues());
                        $e = $building->update($vars, ['name', 'location_id']);
                        if (is_string($e)) {
                            $DEFAULT_ERROR[] = "ERROR: Can not update building. Reason: $e";
                            break;
                        }
                        $body .= "<p>Building successfully updated!</p>";
                        break;
                    case 'delete':
                        $DEFAULT_TITLE .= '\Delete';
                        // Check if used
                        $nbImpact = 0; // count(Tld_Mis_Inventory_Assignement::byConstraints(['destination_type'=>'BUILDING','destination_id'=>$id]));
                        if ($nbImpact > 0) {
                            $link = "$php_self?m[0]=$m[0]&m[1]=$m[1]&m[2]=$m[2]&m[3]=view&m[4]=disable&id=$id";
                            // display error
                            $DEFAULT_ERROR[] = "ERROR: This building is used in $nbImpact assignements";
                            $DEFAULT_ERROR[] = "You should disable this building instead <a href=\"$link\">here</a> if not already done";
                            break;
                        }
                        $e = $building->delete();
                        if (is_string($e)) {
                            $DEFAULT_ERROR[] = "ERROR: Can not delete building. Reason: $e";
                            break;
                        }
                        $body .= "<p>Building successfully deleted!</p>";
                        break;
                    case 'disable':
                        $e = $building->disable();
                        if (is_string($e)) {
                            $DEFAULT_ERROR[] = "ERROR: Can not disable building. Reason: $e";
                            break;
                        }
                        $body .= "<p>Building successfully disabled!</p>";
                        break;
                    case 'enable':
                        $e = $building->enable();
                        if (is_string($e)) {
                            $DEFAULT_ERROR[] = "ERROR: Can not disable building. Reason: $e";
                            break;
                        }
                        $body .= "<p>Building successfully disabled!</p>";
                        break;
                }
        }

        $report = new tldReportColumnar(
            Tld_Mis_Inventory_Building::getList(),
            [
                "xItems" => [
                    'id' => '#',
                    'location' => 'Location',
                    'name' => 'Building',
                    'status' => 'Status',
                ],
                "title" => "Building list",
                "functions" => [
                    "Edit" => [
                        "url" => "$php_self?m[0]=$m[0]&m[1]=$m[1]&m[2]=$m[2]&m[3]=view&m[4]=edit",
                        "param" => ['id' => 'id'],
                        "img" => "/shared/icons/application/edit.png",
                    ],
                    "Enable" => [
                        "url" => "$php_self?m[0]=$m[0]&m[1]=$m[1]&m[2]=$m[2]&m[3]=view&m[4]=enable",
                        "param" => ['id' => 'id'],
                        "img" => "/shared/icons/application/arrow_green.png",
                    ],
                    "Disable" => [
                        "url" => "$php_self?m[0]=$m[0]&m[1]=$m[1]&m[2]=$m[2]&m[3]=view&m[4]=disable",
                        "param" => ['id' => 'id'],
                        "img" => "/shared/icons/application/arrow_red.png",
                    ],
                    "Delete" => [
                        "url" => "$php_self?m[0]=$m[0]&m[1]=$m[1]&m[2]=$m[2]&m[3]=view&m[4]=delete",
                        "param" => ['id' => 'id'],
                        "img" => "/shared/icons/application/delete.png",
                        "confirmPopup" => 'Are you sure to delete?',
                    ],
                ],
            ]
        );
        $body .= $report->fetch();
        break;
}
