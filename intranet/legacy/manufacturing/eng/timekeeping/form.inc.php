<?php
require_once('HTML/QuickForm/autocomplete.php');
require_once 'HTML/QuickForm/advmultiselect.php';
include_once('sales_service.inc.php');
include_once('mis.inc.php');

$USER_DASH = new tldUser($ID_DASH);

switch ($m[2]) {
    case 'byNum':
        //Quick Search Tool by Timesheet ID#
        $DEFAULT_TITLE .= "\Timesheet by Number";
        $form = new HTML_QuickForm('frmByNum', 'post');
        $form->addElement('hidden', 'm[0]', 'timekeeping');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('header', 'title', 'Timesheet by number');
        $form->addElement('text', 'id', 'Timesheet#');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $body .= $form->toHTML();
        break;
    case 'addmis':
        // Submit MIS Timesheet
        // Check permissions
//        if (!$user->isInGroup(['gg_ADMIN', 'gg_MIS'])) {
//            $DEFAULT_ERROR[] = 'ERROR: You do not have permission to access this page';
//            break;
//        }
        // Get listing
//        $factoryList = tldLocation::getFactoryList('smartyOptions');
        $erpList = tldLocation::getERPList('smartyOptions');
        $misList = tldGroup::getUserListByMultipleGroup(['gg_MIS'], '', 'smartyOptions');
        $task = new tldTask((int)$module_id);
        if ($task->isEmpty()) break;
        $parentID = $task->getParentID();
        $proj = new tldTTS($parentID);
        $members = $proj->getMembersList($parentID) + $misList;

        // Form
        $DEFAULT_TITLE .= "\Submit a new Timesheet";
        $form = new HTML_QuickForm('frmAddMIS', 'post');
        $form->addElement('hidden', 'm[0]', 'timekeeping');
        $form->addElement('hidden', 'm[1]', 'form');
        $form->addElement('hidden', 'm[2]', 'addmis');
        $form->addElement('hidden', 'link', 'Task');
        $form->addElement('header', 'title', 'Submit MIS Timesheet');
        $form->addElement('select', 'location', 'Company/Location', ['' => ''] + $erpList);
        $form->addElement('select', 'user_id', 'User', ['' => ''] + $members);
        $form->addElement('date', 'dt_open', 'Date', ['format' => 'Y-m-d', 'addEmptyOption' => false, 'minYear' => date('Y') - 1, 'maxYear' => date('Y')]);
        $form->addElement('text', 'module_id', 'Task#');
        $form->addElement('text', 'hours_actual', 'Time (in <b>Hours</b>)');
        $form->addElement('textarea', 'comment', 'Comment', ['rows' => 5, 'cols' => 40]);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $required = ['location', 'user_id', 'dt_open', 'module_id', 'hours_actual'];
        foreach ($required as $key => $field) {
            $form->addRule($field, 'Required', 'required');
        }
        // Set Default
        $buid = $user->getBUID();
        $buid_erp = tldLocation::getERPByID($buid);
        $form->setDefaults([
            'location' => $buid_erp,
            'dt_open' => date('Y-m-d'),
            'user_id' => $ID_DASH,
            'module_id' => $_REQUEST['module_id'],
        ]);

        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }
        // Cleaning data
        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $vars['dt_open'] = implode('-', $vars['dt_open']);
        // Security Checks
        if (!is_numeric($vars['module_id']) || !is_numeric($vars['hours_actual'])) {
            $DEFAULT_ERROR[] = 'ERROR: Task# and Time fields must be numeric!.';
            break;
        }
        $e = tldTimekeeping::insert($vars, 'MIS');
        if (!is_numeric($e)) {
            $DEFAULT_ERROR[] = "INTERNAL ERROR: Timesheet NOT created!<br/>Reason: $e";
            break;
        }
        $body .= <<<EOF
            <script>
                // Prevent form resubmission as we got some duplicating problems.
                // It's maybe cause to user refresh the page or used the back button and resubmit the form.
                if (window.history.replaceState) {
                    window.history.replaceState(null, null, window.location.href);
                }
            </script>
			<br/><br/>Timesheet#$e successfully created! <a href="$php_self?m[0]=timekeeping&m[1]=view&id=$e">Click here to view.</a>
EOF;

        break;
    case 'quickadd':
        // include jquery for js functionality on this form
        $JS_INCLUDE[] = '/include/jquery/jquery.js';
        // Check permissions
        if (!$user->isInGroup(['gg_ADMIN', 'role_EM', 'acl_timekeeping'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permission to access this page';
            break;
        }
        $buid = $user->getBUID();
        if (0 == $location = tldLocation::getERPByID($buid)) {
            $DEFAULT_ERROR[] = "ERROR: The factory you belongs to can't be determined automatically from your profile, you must use the standard submit timesheet tool";
            break;
        }
        $today = date('Y-m-d');
        $categoryList = tldTimekeeping::getCategoryList();
        $categoryList = array_combine($categoryList, $categoryList);
        $linkList = tldTimekeeping::getLinkList();
        $linkList = array_combine($linkList, $linkList);
        $defaulTimesheetNumber = range(1, 10);
        $username = $user->getFullname();
        $factory = tldLocation::getLocationByID($buid);
        $smarty->assign('today', $today);
        $smarty->assign('defaulTimesheetNumber', $defaulTimesheetNumber);
        $smarty->assign('categoryList', $categoryList);
        $smarty->assign('linkList', $linkList);
        $smarty->assign('username', $username);
        $smarty->assign('factory', $factory);
        $smarty->assign('usingActualHours', in_array((int)tldLocation::getERPByID($buid), tldTimekeeping::getErpUsingActualHours(), true));
        $smarty->assign('nextURL', "$php_self?m[0]=timekeeping&m[1]=form&m[2]=quickadd&m[3]=insert");
        $body = $smarty->fetch("$PATH/timekeeping/timekeeping.quickadd.form.tpl");

        switch ($m[3]) {
            case 'insert':
                // Check permissions
                if (!$user->isInGroup(['gg_ADMIN', 'role_EM', 'acl_timekeeping'])) {
                    $DEFAULT_ERROR[] = 'ERROR: You do not have permission to access this page';
                    break;
                }
                $body = '';
                $userid = $user->getID();
                // Insert process
                foreach ($_POST['timesheet'] as $key => $data) {
                    // Secure data
                    $data = tldUtils::cleanupFormInput($data);
                    // Location And Variables
                    $vars = [];

                    // Make sure required data is here
                    if (!empty($data['dt_open'])
                        && (!empty($data['time_actual']) || !empty($data['hours_actual']))
                        && !empty($data['category'])
                        && (($data['category'] !== 'Design Task') || (!empty($data['module_id']) && !empty($data['link'])))
                    ) {
                        $flag_update = 1;
                        // Time
                        if (!in_array((int)$location, tldTimekeeping::getErpUsingActualHours(), true)) {
                            // Check time - 1 <= time >= 100 - No decimals
                            if ($data['time_actual'] < 1 || $data['time_actual'] > 100 || $data['time_actual'] == 0) {
                                $DEFAULT_ERROR[] = "ERROR: You must set a time (% of day) greater than 0 and inferior or equal to 100! No decimals. Timesheet #$key not created";
                                continue;
                            }
                            // Check Total Hours for the day and user selected <= 100
                            $hours_date = tldTimekeeping::getDailyHours($userid, $data['dt_open'], 'time_actual');
                            $total_hours = $hours_date + $data['time_actual'];
                            $max_hours = 100 - $hours_date;
                            $user_selected = str_replace(',', '', $user->getFullname());
                            if ($total_hours > 100) {
                                $DEFAULT_ERROR[] = 'ERROR: Maximum time left for ' . $user_selected . ', ' . $data['dt_open'] . " is $max_hours% - Remember that the total hours scheduled for one day can not exceed 100%. Timesheet #$key not created";
                                continue;
                            }
                            $vars['time_actual'] = $data['time_actual'];
                        } else {
                            if ($data['hours_actual'] < 0 || $data['hours_actual'] == 0 || !is_numeric($data['hours_actual'])) {
                                $DEFAULT_ERROR[] = "ERROR: You must set a time (actual hours) greater than 0. Timesheet #$key not created";
                                continue;
                            }
                            $vars['hours_actual'] = $data['hours_actual'];
                        }
                        // Location
                        $vars['location'] = $location;
                        // User ID
                        $vars['user_id'] = $userid;
                        // Date
                        $date_format = 'Y-m-d';
                        $input = trim($data['dt_open']);
                        $date = strtotime($input);
                        if (date($date_format, $date) !== $input) {
                            $DEFAULT_ERROR[] = "ERROR: Date is not valid. Timesheet #$key not created";
                            continue;
                        }

                        $vars['dt_open'] = $data['dt_open'];
                        // Category
                        $vars['category'] = $data['category'];
                        // Link
                        $vars['link'] = $data['link'];
                        $link = $vars['link'];
                        // Module ID
                        $vars['module_id'] = $data['module_id'];
                        $module_id = $vars['module_id'];
                        // Product/Project
                        if ($link === 'EAP') {
                            $vars['project'] = 'EAP';
                            $auto_models = tldTimekeeping::getModel($module_id, $link);
                            if ($auto_models) {
                                $model = '';
                                foreach ($auto_models as $auto_mod) {
                                    $model .= $auto_mod['model'] . ',';
                                }
                                $vars['product_type'] = trim($model, ',');
                            }
                        }
                        if ($link === 'PDC') {
                            $vars['project'] = 'PDC';
                            $auto_models = tldTimekeeping::getModel($module_id, $link);
                            if ($auto_models) {
                                $vars['product_type'] = $auto_models['model'];
                            }
                        }
                        if ($link === 'SB3') {
                            $vars['project'] = 'SB3';
                            $auto_models = tldTimekeeping::getModel($module_id, $link);
                            if ($auto_models) {
                                $model = [];
                                foreach ($auto_models as $auto_mod) {
                                    $model[] = $auto_mod['model'];
                                }
                                $vars['product_type'] = implode(',', array_unique($model));
                            }
                        }
                        if ($link === 'SOL') {
                            $vars['project'] = 'SOL';
                            $auto_models = tldTimekeeping::getModel($module_id, $link);
                            if ($auto_models) {
                                $vars['product_type'] = $auto_models['model'];
                            }
                        }
                        if ($link === 'GWF') {
                            $vars['project'] = 'GWF';
                            $auto_models = tldTimekeeping::getModel($module_id, $link);
                            if ($auto_models) {
                                $vars['product_type'] = $auto_models['model'];
                            }
                        }
                        if ($link === 'TOC') {
                            $vars['project'] = 'TOC';
                            $auto_models = tldTimekeeping::getModel($module_id, $link);
                            if ($auto_models) {
                                $vars['product_type'] = $auto_models['model'];
                            }
                        }
                        //Check Module ID is numeric
                        if (!is_numeric($module_id) && (!empty($data['module_id']))) {
                            $DEFAULT_ERROR[] = 'ERROR: Module Reference must be numeric!';
                            break;
                        }
                        // Comment
                        if (!empty($data['comment'])) {
                            $vars['comment'] = $data['comment'];
                        }
                        // Insert Timesheet
                        $e = tldTimekeeping::insert($vars, 'ENG');
                        if (!is_numeric($e)) {
                            $DEFAULT_ERROR[] = "INTERNAL ERROR: Timesheet #$key NOT created!<br/>Reason: $e";
                            continue;
                        }
                        $body .= <<<EOF
            <script>
                // Prevent form resubmission as we got some duplicating problems.
                // It's maybe cause to user refresh the page or used the back button and resubmit the form.
                if (window.history.replaceState) {
                    window.history.replaceState(null, null, window.location.href);
                }
            </script>
			<br/><br/>Timesheet#$e successfully created! <a href="$php_self?m[0]=timekeeping&m[1]=view&id=$e">Click here to view.</a>
EOF;

                    }
                }
                if ($flag_update != 1) {
                    $body = 'Not enough data to create Timesheets.';
                }
                break;
        }
        break;
    case 'getShortDesc':
        if ('XMLHttpRequest' === $_SERVER['HTTP_X_REQUESTED_WITH'] ?? null) {
            $module = $_GET['module'] ?? null;
            $id = (int) ($_GET['id'] ?? 0);

            $class = "tld$module";
            if (!class_exists($class)) {
                exit;
            }
            $record = new $class($id);
            foreach(['getShortDesc', 'getShortDescription'] as $method) {
                if (method_exists($class, $method)) {
                    echo $record->$method();
                    exit;
                }
            }
        }
    exit;
    case 'add':
        // STEP 1 - Timesheet Submission - Location, Category, Task linked
        // Check permissions
        if (!$user->isInGroup(['gg_ADMIN', 'role_EM', 'acl_timekeeping'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permission to access this page';
            break;
        }
        $JS_INCLUDE[] = '/include/jquery/jquery.js';
        $body .= <<<'HTML'
<style>
    .js-description-target {
        outline: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        border: 0;
        box-shadow: none !important;
        background-color: transparent;
    }
</style>
<script type="text/javascript">
    $(document).ready(function() {
        let fetchShortDescription = function ($link, $moduleRef) {
            $(document).find('.js-description-target').val('')
    
            let module = $link.val()
            let id = $moduleRef.val()
    
            if (!module || !id) {
                return false
            }
    
            $.get('/en/private/manufacturing/eng/dev.php?m[0]=timekeeping&m[1]=form&m[2]=getShortDesc&module=' + module + '&id=' + id).done(function (data) {
                $(document).find('.js-description-target').val(data)
            })
        }
    
        $('input[name="module_id"]').on('change', function () {
            let $link = $('select[name="'+this.name.replace('module_id', 'link')+'"]')
            let $moduleRef = $(this)
    
            fetchShortDescription($link, $moduleRef)
        })
    
    
        let handleLinkUpdate = function () {
            let $link = $(this)
            let $moduleRef = $('input[name="'+this.name.replace('link', 'module_id')+'"]')
    
            fetchShortDescription($link, $moduleRef)
        }
    
        let $link = $('select[name="link"]')
        $link.on('change', handleLinkUpdate)
        $link.each(handleLinkUpdate)

        let buildOptions = function($element, optionsList, addNaOption = false) {
            let currentValue = $element.val();
            $element.empty();
            optionsList.sort();
            if (addNaOption) {
                optionsList.unshift('N/A');
            }
            $.each(optionsList, function(index, value) {
                $element.append($("<option></option>").attr("value", value).text(value));
            });
            if (optionsList.includes(currentValue)) {
                $element.val(currentValue);
            }
        }
        
        let changeLinkList = function () {
            switch (this.value) {
                case 'Design Task':
                    buildOptions($link, ['MEAP', 'EAP', 'FAQ', 'SOL', 'GWF', 'CPA', 'PIP'])
                    break;
                case 'Training':
                    buildOptions($link, ['AGILE'])
                    break;
                case 'Supplier / Customer Visit':
                    buildOptions($link, ['MEAP', 'EAP', 'PIP', 'PDC', 'SOL'])
                    break;
                case 'Production Support':
                    buildOptions($link, ['NCR', 'FAQ', 'GWF', 'CPA', 'AGILE', 'CRAB'], true)
                    break;
                case 'Field / SPR Support':
                    buildOptions($link, ['PDC', 'SB3', 'TOC', 'CPA', 'AGILE'])
                    break;
                case 'Team Meeting':
                case 'Non Productive Hours':
                case 'Vacation':
                case 'Sick Leave':
                    buildOptions($link, [], true)
                    break;
                default:
                    break;
		    }
	    }
        
        let $category = $('select[name="category"]')
	    $category.on('change', changeLinkList)
	    $category.each(changeLinkList)
    });
</script>
HTML;

        // Get listing
        $factoryList = tldLocation::getFactoryList('smartyOptions');
        $categoryList = tldTimekeeping::getCategoryList();
        $categoryList = array_combine($categoryList, $categoryList);
        $linkList = tldTimekeeping::getLinkList();
        $linkList = array_combine($linkList, $linkList);

        // Form Step 1
        $DEFAULT_TITLE .= "\Submit a new Timesheet";
        $form = new HTML_QuickForm('frmAdd', 'post');
        $form->addElement('hidden', 'm[0]', 'timekeeping');
        $form->addElement('hidden', 'm[1]', 'form');
        $form->addElement('hidden', 'm[2]', 'add');
        $form->addElement('header', 'title', 'STEP 1: Enter Location and Category');
        $form->addElement('select', 'location', 'Company/Location', ['' => ''] + $factoryList);
        $form->addElement('select', 'category', 'Category', ['' => ''] + $categoryList);
        $form->addElement('static', null, null, "<br>Select a Link method and enter its #ID - <font color='red'><b>Only required if Design Task</b></font>");
        $form->addElement('select', 'link', 'Module', $linkList);
        $form->addElement('text', 'module_id', 'ID#');
        $form->addElement('text', 'short_description', 'Short description', ['class' => 'js-description-target', 'disabled' => 'disabled', 'size' => 60]);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $required = ['location', 'category'];
        foreach ($required as $key => $field) {
            $form->addRule($field, 'Required', 'required');
        }
        // Set Default'
        $buid = $user->getBUID();
        $buid_erp = tldLocation::getERPByID($buid);
        $form->setDefaults(['location' => $buid_erp]);
        if (!$form->validate()) {
            $body .= $form->toHTML();
            break;
        }

        header("Location: $php_self?m[0]=timekeeping&m[1]=form&m[2]=add2&location=$location&category=$category&link=$link&module_id=$module_id");
        exit;

    // STEP 2 - Timesheet Submission
    case 'add2':
        // Check permissions
        if (!$user->isInGroup(['gg_ADMIN', 'role_EM', 'acl_timekeeping'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permission to access this page';
            break;
        }
        // Check data from step 1
        $location = TldDatabase::escape($_REQUEST['location']);
        $category = TldDatabase::escape($_REQUEST['category']);
        $link = TldDatabase::escape($_REQUEST['link']);
        $module_id = TldDatabase::escape($_REQUEST['module_id']);
        //Check Location is set and clean
        if (!$location || !in_array((int)$location, [250, 390 /* Sage */, 400, 410, 420, 430, 440, 500, 510, 520, 540, 570, 640, 660, 700, 995 /* Page */], true)) {
            $DEFAULT_ERROR[] = 'ERROR: Location is invalid or not set';
            break;
        }
        //Check Module ID and Link are set if Category = Engineering Task
        if ($category === 'Design Task' && (empty($link) || empty($module_id))) {
            $DEFAULT_ERROR[] = 'ERROR: Link method and Module Reference are mandatory for Design Tasks!';
            break;
        }
        //Check Module ID is numeric
        if ($category === 'Design Task' && !is_numeric($module_id)) {
            $DEFAULT_ERROR[] = 'ERROR: Module Reference must be numeric!';
            break;
        }
        $inErpUsingActualHours = in_array((int)$location, tldTimekeeping::getErpUsingActualHours(), true);
        // Get listing
        $commentList = tldTimekeeping::getOtherList('smartyOptions');
        $engList = tldGroup::getUserListByMultipleGroup(['acl_timekeeping'], "$location", ['smartyOptions' => true]);
        $models = tldCatalogue::getTypeModelList();
        foreach ($models as $model) {
            $modelList[$model['model']] = $model['type'] . '->' . $model['model'];
        }
        $projectType = tldTimekeeping::getProjectList('smartyOptions');
        $factoryList = tldLocation::getFactoryList('smartyOptions');
        $date = date('Y-m-d');
        $hours = tldTimekeeping::getDailyHours($ID_DASH, $date, 'time_actual');
        $hours_WIN = tldTimekeeping::getDailyHours($ID_DASH, $date, 'hours_actual');
        //Auto filling for Model and Project based on Link type
        if ($category === 'Design Task') {
            if ($link === 'EAP') {
                $auto_project = 'EAP';
                $auto_models = tldTimekeeping::getModel($module_id, $link);
                $auto_model = [];
                foreach ($auto_models as $auto_mod) {
                    $auto_model[] = $auto_mod['model'];
                }
            }
            if ($link === 'PDC') {
                $auto_project = 'PDC';
                $auto_model = tldTimekeeping::getModel($module_id, $link);
            }
            if ($link === 'SB3') {
                $auto_project = 'SB3';
                $auto_models = tldTimekeeping::getModel($module_id, $link);
                $auto_model = [];
                foreach ($auto_models as $auto_mod) {
                    $auto_model[] = $auto_mod['model'];
                }
            }
            if ($link === 'SOL') {
                $auto_project = 'SOL';
                $auto_model = tldTimekeeping::getModel($module_id, $link);
            }
            if ($link === 'GWF') {
                $auto_project = 'GWF';
                $auto_model = tldTimekeeping::getModel($module_id, $link);
            }
            if ($link === 'TOC') {
                $auto_project = 'TOC';
                $auto_model = tldTimekeeping::getModel($module_id, $link);
            }
            if ($link === 'CPA') {
                $auto_project = 'CPA';
                $auto_model = 'MISC'; // No models are attached to a CPA
            }
            if ($link === 'NCR') {
                $auto_project = 'NCR';
                $auto_model = 'MISC'; // No models are attached to a SCAR
            }
            if ($link === 'PIP') {
                $auto_project = 'PIP';
                $auto_model = tldTimekeeping::getModel($module_id, $link);
            }
            if ($link === 'MEAP') {
                $auto_project = 'MEAP';
                $auto_model = tldTimekeeping::getModel($module_id, $link);
            }
        }

        // Form step 2
        $DEFAULT_TITLE .= "\Submit a new Timesheet";
        $form2 = new HTML_QuickForm('frmAddTimesheet', 'post');
        $form2->addElement('hidden', 'm[0]', 'timekeeping');
        $form2->addElement('hidden', 'm[1]', 'form');
        $form2->addElement('hidden', 'm[2]', 'add2');
        $form2->addElement('hidden', 'location', $location);
        $form2->addElement('hidden', 'category', $category);
        $form2->addElement('hidden', 'link', $link);
        $form2->addElement('hidden', 'module_id', $module_id);
        $form2->addElement('header', 'title', 'STEP 2: Submit a new Timesheet');
        $form2->addElement('text', 'disabled_location', 'Location', ['disabled' => 'disabled']);
        $form2->addElement('text', 'disabled_category', 'Category', ['disabled' => 'disabled']);
        $form2->addElement('select', 'user_id', 'User', ['' => ''] + $engList);

        $form2->addElement('text', 'dt_open', 'Date', [
            'format' => 'Y-m-d',
            'addEmptyOption' => false,
            'minYear' => date('Y') - 1,
            'maxYear' => date('Y'),
            'class' => 'datepicker',
        ]);

        // Set fields and requirements based on Category
        if ($category === 'Vacation' || $category === 'Sick Leave') {
            $form2->addElement('text', 'dt_to', 'Date to', [
                'format' => 'Y-m-d',
                'addEmptyOption' => false,
                'minYear' => date('Y') - 1,
                'maxYear' => date('Y'),
                'class' => 'datepicker',
            ]);
        }

        if ($category === 'Engineering Task') {
            $form2->addElement('select', 'project', 'Project Type', ['' => ''] + $projectType);
            $ams =& $form2->addElement('advmultiselect', 'product_type', null,
                ['ALL_MODELS' => 'All Models', 'NEW_MODELS' => 'New Product(s)', 'MISC' => 'Miscellaneous/Not listed'] + $modelList,
                ['size' => 15, 'class' => 'pool', 'style' => 'width:380px;']);
            $ams->setLabel(['Product Type', 'Type->Model', 'Affected']);
            $ams->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
            $ams->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
            //$ams->setSelected($auto_model);
            $required = ['product_type', 'project', 'user_id', 'dt_open', 'time_actual'];
        } elseif ($category === 'TLD Manuf. Support' || $category === 'TLD Field Support' || $category === 'TLD Spare Parts Support') {
            $ams =& $form2->addElement('advmultiselect', 'product_type', null,
                ['ALL_MODELS' => 'All Models', 'NEW_MODELS' => 'New Product(s)'] + $modelList,
                ['size' => 15, 'class' => 'pool', 'style' => 'width:380px;']);
            $ams->setLabel(['Product Type', 'Type->Model', 'Affected']);
            $ams->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
            $ams->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);

            $required = ['product_type', 'user_id', 'dt_open', 'time_actual'];
        } else {
            $required = ['user_id', 'dt_open', 'time_actual'];
        }
        if (!$inErpUsingActualHours) {
            $form2->addElement('text', 'time_actual', 'Time (in % of day)');
            $form2->addElement('static', null, null, 'Total Actual Hours scheduled for today (' . $USER_DASH->getFullname() . '): <b>' . $hours . '%</b>');
            if ($user->isInGroup(['gg_ADMIN', 'role_EM'])) {
                $form2->addElement('text', 'time_forecast', 'Forecasted Time (in % of day)');
            }
        } else {
            $form2->addElement('text', 'hours_actual', 'Time (in <b>Hours</b>)');
            $form2->addElement('static', null, null, 'Total Actual Hours scheduled for today (' . $USER_DASH->getFullname() . '): <b>' . $hours_WIN . 'h</b>');
            if ($user->isInGroup(['gg_ADMIN', 'role_EM'])) {
                $form2->addElement('text', 'hours_forecast', 'Forecasted Time (in <b>Hours</b>)');
            }
            $required[] = ('hours_actual');
        }
        $form2->addElement('textarea', 'comment', 'Comment', ['rows' => 5, 'cols' => 40]);
        $form2->addElement('submit', 'btnSubmit', 'Submit');
        if (!$inErpUsingActualHours && $user->isInGroup(['gg_ADMIN', 'role_EM', 'role_ES'])) {
            $form2->addElement('submit', 'btnSubmit', 'Calculate Total Hours');
        }
        // Set defaults
        $form2->setDefaults(['disabled_location' => $factoryList[$location], 'disabled_category' => $category, 'dt_to' => $date, 'dt_open' => $date, 'user_id' => $ID_DASH,
                'project' => $auto_project, 'product_type' => $auto_model]
        );

        foreach ($required as $key => $field) {
            $form2->addRule($field, 'Required', 'required');
        }
        if ($_POST['btnSubmit'] === 'Submit' && $form2->validate()) {
            $vars = tldUtils::cleanupFormInput($form2->exportValues());
            if (!empty($vars['product_type'])) {
                $vars['product_type'] = implode(',', $vars['product_type']);
            }

            if (array_key_exists('dt_to', $vars)) {
                $start = new DateTime($vars['dt_open']);
                $end = new DateTime($vars['dt_to']);
                if ($start > $end) {
                    $DEFAULT_ERROR[] = 'ERROR: Start date can not be greater than the end date';
                    $body .= $form2->toHTML();
                    break;
                }
            }

            switch ($link) {
                case 'EAP':
                    $eap = (new tldEAP($module_id))->getHeader();
                    $closedDate = $eap['dt_closed'] !== '0000-00-00 00:00:00' ? new DateTime($eap['dt_closed']) : null;
                    break;
                case 'MEAP':
                    $meap = (new tldMEAP($module_id))->getHeader();
                    $closedDate = $meap['date_closed'] !== '0000-00-00' ? new DateTime($meap['date_closed']) : null;
                    break;
                case 'PDC':
                    $pdc = (new tldPDC($module_id))->getHeader();
                    $closedDate = $pdc['date_closed'] !== '0000-00-00' ? new DateTime($pdc['date_closed']) : null;
                    break;
                case 'SB3':
                    $sb = (new tldSB3($module_id))->getHeader();
                    $closedDate = $sb['dt_closed'] !== '0000-00-00 00:00:00' ? new DateTime($sb['dt_closed']) : null;
                    break;
                case 'SOL':
                    $sol = (new tldSOL($module_id))->getHeader();
                    $closedDate = $sol['dt_closed'] !== '0000-00-00 00:00:00' ? new DateTime($sol['dt_closed']) : null;
                    break;
                case 'GWF':
                    $gwf = (new tldGWF($module_id))->getHeader();
                    $closedDate = $gwf['dt_closed'] !== '0000-00-00 00:00:00' ? new DateTime($gwf['dt_closed']) : null;
                    break;
                case 'TOC':
                    $toc = (new tldTOC($module_id))->getHeader();
                    $closedDate = $toc['dt_closed'] !== '0000-00-00' ? new DateTime($toc['dt_closed']) : null;
                    break;
                default:
                    $closedDate = null;
            }

            if ($closedDate !== null) {
                $timesheetDate = new DateTime($vars['dt_open']);
                if ($timesheetDate > $closedDate) {
                    $DEFAULT_ERROR[] = sprintf('ERROR: Start date can not be after the closed date of %s %s', $link, $module_id);
                    $body .= $form2->toHTML();
                    break;
                }
            }

            if (!$inErpUsingActualHours) {
                // Check time - 1 <= time >= 100 - No decimals
                if (empty($vars['time_actual']) || $vars['time_actual'] < 1 || $vars['time_actual'] > 100 || $vars['time_actual'] == 0) {
                    $DEFAULT_ERROR[] = 'ERROR: You must set a time (% of day) greater than 0 and inferior or equal to 100! No decimals.';
                    $body .= $form2->toHTML();
                    break;
                }
                // Check Total Hours for the day and user selected <= 100
                $hours_date = tldTimekeeping::getDailyHours($vars['user_id'], $vars['dt_open'], 'time_actual');
                $total_hours = $hours_date + $vars['time_actual'];
                $max_hours = 100 - $hours_date;
                $e = new tldUser($vars['user_id']);
                $user_selected = str_replace(',', '', $e->getFullname());
                if ($total_hours > 100) {
                    $DEFAULT_ERROR[] = 'ERROR: Maximum time left for ' . $user_selected . ', ' . $vars['dt_open'] . " is $max_hours% - Remember that the total hours scheduled for one day can not exceed 100%";
                    $body .= $form2->toHTML();
                    break;
                }
            } elseif (empty($vars['hours_actual']) || $vars['hours_actual'] < 0 || $vars['hours_actual'] == 0 || !is_numeric($vars['hours_actual'])) {
                $DEFAULT_ERROR[] = 'ERROR: You must set a time (actual hours), greater than 0.';
                $body .= $form2->toHTML();
                break;
            }
            $e = tldTimekeeping::insert($vars, 'ENG');
            if (!is_numeric($e)) {
                $DEFAULT_ERROR[] = "INTERNAL ERROR: Timesheet NOT created!<br/>Reason: $e";
                break;
            }
            $body .= <<<EOF
            <script>
                // Prevent form resubmission as we got some duplicating problems.
                // It's maybe cause to user refresh the page or used the back button and resubmit the form.
                if (window.history.replaceState) {
                    window.history.replaceState(null, null, window.location.href);
                }
            </script>
			<br/><br/>Timesheet#$e successfully created! <a href="$php_self?m[0]=timekeeping&m[1]=view&id=$e">Click here to view.</a>
EOF;
        } elseif ($_POST['btnSubmit'] === 'Calculate Total Hours' && $form2->validate() && !in_array($location, tldTimekeeping::getErpUsingActualHours())) {
            $vars = tldUtils::cleanupFormInput($form2->exportValues());

            // Check Total Hours for the day and user selected <= 100
            $hours_date = tldTimekeeping::getDailyHours($vars['user_id'], $vars['dt_open'], 'time_actual');
            $total_hours = $hours_date + $vars['time_actual'];
            $max_hours = 100 - $hours_date;
            $e = new tldUser($vars['user_id']);
            $user_selected = str_replace(',', '', $e->getFullname());
            $DEFAULT_ERROR[] = 'Maximum time left for ' . $user_selected . ', ' . $vars['dt_open'] . " is $max_hours% - Remember that the total hours scheduled for one day can not exceed 100%";
            $body .= $form2->toHTML();
        } else {
            $body .= $form2->toHTML();
            // Choose Percentage or Hours based on location
            if (!$inErpUsingActualHours) {
                $actual = 'time_actual';
                $actual_desc = 'Actual Hours (%)';
            } else {
                $actual = 'hours_actual';
                $actual_desc = 'Time (in Hours)';
            }
            //Get Daily Timesheets
            $today = date('Y-m-d');
            $rows = tldTimekeeping::getDailyTimesheets($ID_DASH, $today, 'ENG');
            // Create module_id link based on the link type
            foreach ($rows as &$row) {
                if ($row['category'] === 'Engineering Task' && !empty($row['module_id'])) {
                    if ($row['link'] === 'Task') {
                        $row['url'] = "<a href='/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=" . $row['module_id'] . "'>" . $row['module_id'] . '</a>';
                    }
                    if ($row['link'] === 'EAP') {
                        $row['url'] = "<a href='/en/private/manufacturing/eng/dev.php?m[0]=eap&m[1]=view&id=" . $row['module_id'] . "'>" . $row['module_id'] . '</a>';
                    }
                    if ($row['link'] === 'PDC') {
                        $row['url'] = "<a href='/en/private/product_support/index.ps.php?m[0]=pdc&m[1]=view&id=" . $row['module_id'] . "'>" . $row['module_id'] . '</a>';
                    }
                    if ($row['link'] === 'ER') {
                        $row['url'] = "<a href='/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view&id=" . $row['module_id'] . "'>" . $row['module_id'] . '</a>';
                    }
                    if ($row['link'] === 'SB') {
                        $row['url'] = "<a href='/en/private/product_support/index.ps.php?m[0]=sbs&m[1]=view&id=" . $row['module_id'] . "'>" . $row['module_id'] . '</a>';
                    }
                    if ($row['link'] === 'SB3') {
                        $row['url'] = "<a href='/en/private/product_support/index.ps.php?m[0]=sb&m[1]=view&id=" . $row['module_id'] . "'>" . $row['module_id'] . '</a>';
                    }
                    if ($row['link'] === 'SOL') {
                        $row['url'] = "<a href='/en/private/sales_service/sales.php?m[0]=sol&m[1]=view&id=" . $row['module_id'] . "'>" . $row['module_id'] . '</a>';
                    }
                    if ($row['link'] === 'GWF') {
                        $row['url'] = "<a href='/en/private/calendar/calendar.php?m[0]=gwf&m[1]=view&id=" . $row['module_id'] . "'>" . $row['module_id'] . '</a>';
                    }
                    if ($row['link'] === 'TOC') {
                        $row['url'] = "<a href='/en/private/sales_service/service.php?m[0]=toc&m[1]=view&id=" . $row['module_id'] . "'>" . $row['module_id'] . '</a>';
                    }
                    if ($row['link'] === 'WC') {
                        $row['url'] = "<a href='/en/private/product_support/index.ps.php?m[0]=wc&m[1]=view&id=" . $row['module_id'] . "'>" . $row['module_id'] . '</a>';
                    }
                } else {
                    $row['url'] = $row['module_id'];
                }
            }
            $report2 = new tldReportColumnar(
                $rows,
                [
                    'xItems' => [
                        'id' => 'Timesheet#',
                        'category' => 'Category',
                        'product_type' => 'Product Type',
                        'link' => 'Link Type',
                        'url' => 'Module ID#',
                        $actual => $actual_desc,

                    ],
                    'title' => 'Your schedule for ' . $today,
                    'links' => ['id' => "$php_self?m[0]=timekeeping&m[1]=view&id="],
                ]
            );
            $body .= '<center>' . $report2->fetch() . '</center>';
        }
        break;
}
