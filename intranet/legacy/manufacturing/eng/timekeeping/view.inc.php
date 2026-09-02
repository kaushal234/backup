<?php
if (empty($id) || !is_numeric($id)) {
    $DEFAULT_ERROR[] = 'ERROR: no ID set...';
    return;
}
$timekeeping = new tldTimekeeping($id);
if ($timekeeping->isEmpty()) {
    $DEFAULT_ERROR[] = "ERROR: No Timesheet#$id found...";
    return;
}

$header = $timekeeping->getHeader();
$location = $header['location'];
$DEFAULT_TITLE .= "\Timesheet#$id";
$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=timekeeping&m[1]=view&id=$id">General</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=timekeeping&m[1]=view&m[2]=edit&id=$id" title="Edit Timesheet#$id">Edit</a>
EOF;
if ($user->isInGroup(['gg_ADMIN', 'role_EM', 'role_ES'])) {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=timekeeping&m[1]=view&m[2]=delete&id=$id" onclick="return confirm('Are you sure?');" title="Delete Timesheet#$id">Delete</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=timekeeping&m[1]=view&m[2]=log&id=$id" title="Activity Logs for #$id">Log</a>
EOF;
}
if ($user->isInGroup(['gg_ADMIN'])) {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="/en/private/manufacturing/eng/timekeeping/timekeeping_admin.php?mode=record_view&form_type=main_tpl&id=$id">Admin</a>
EOF;
}

switch ($m[2]) {
    case 'log':
        $report = new tldReportColumnar(
            $timekeeping->getFullLog(),
            [
                'xItems' => [
                    'id' => 'ID#',
                    'date' => 'Date',
                    'poster_fullname' => 'Poster',
                    'module' => 'Module',
                    'comment' => 'Comment',
                ],
            ]
        );
        $body .= $report->fetch();
        break;
    case 'delete':
        // Check permissions
        if (!$user->isInGroup(['gg_ADMIN', 'role_EM', 'role_ES'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permission to access this page';
            break;
        }
        if (!empty($id) && is_numeric($id)) {
            $del = tldTimekeeping::delete($id);
            if (is_string($del)) {
                $DEFAULT_ERROR[] = "INTERNAL ERROR: Timesheet not deleted!<br/>Reason: $del";
            } else {
                $body .= "Timesheet #$id deleted successfully!";
            }
        } else {
            $DEFAULT_ERROR[] = 'ERROR: Timesheet ID# empty or invalid!';
        }
        break;
    case 'edit':
        $JS_INCLUDE[] = '/include/jquery/jquery.js';
        // Check permissions
        $user_id = $user->getID();
        if (!$user->isInGroup(['gg_ADMIN', 'role_EM', 'role_ES']) && !$timekeeping->editPermission($user_id)) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permission to access this page. The timesheet you are trying to edit is either registered to a different user or is more than 15 days old.';
            break;
        }
        // MIS Case
        if ($header['tld_dpt'] === 'MIS') {
            $DEFAULT_ERROR[] = 'ERROR: This is a MIS timesheet. Please use the Admin module for editing!';
            break;
        }

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
        $engList = tldGroup::getUserListByMultipleGroup(['acl_timekeeping'], '', 'smartyOptions');
        $factoryList = tldLocation::getFactoryList('smartyOptions');
        $categoryList = tldTimekeeping::getCategoryList();
        $categoryList = array_combine($categoryList, $categoryList);
        $modelList = tldModel::getList();
        $projectType = tldTimekeeping::getProjectList('smartyOptions');
        $linkList = tldTimekeeping::getLinkList();
        $linkList = array_combine($linkList, $linkList);
        // Form
        $form = new HTML_QuickForm('frmEdit', 'post');
        $form->addElement('hidden', 'm[0]', 'timekeeping');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'edit');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', "Edit Timesheet#$id");
        $form->addElement('select', 'location', 'Location', ['' => ''] + $factoryList);
        $form->addElement('select', 'user_id', 'User', ['' => ''] + $engList);
        $form->addElement('date', 'dt_open', 'Date', ['format' => 'Y-m-d', 'addEmptyOption' => false, 'minYear' => date('Y') - 2, 'maxYear' => date('Y') + 1]);
        $form->addElement('select', 'category', 'Category', $categoryList);
        $form->addElement('textarea', 'product_type', 'Product Type', ['rows' => 5, 'cols' => 20]);
        $form->addElement('select', 'project', 'Project Type', ['' => ''] + $projectType);
        $form->addElement('select', 'link', 'Module', $linkList);
        $form->addElement('text', 'module_id', 'ID#');
        if (!in_array((int)$location, tldTimekeeping::getErpUsingActualHours(), true)) {
            $form->addElement('text', 'time_actual', 'Actual Hours (in % of the day)');
            if ($user->isInGroup(['gg_ADMIN', 'role_EM'])) {
                $form->addElement('text', 'time_forecast', 'Forecasted Hours (in % of the day)');
            }
            $required = ['location', 'user_id', 'dt_open', 'category', 'module_id', 'time_actual'];
        } else {
            $form->addElement('text', 'hours_actual', 'Time (in <b>Hours</b>)');
            if ($user->isInGroup(['gg_ADMIN', 'role_EM'])) {
                $form->addElement('text', 'hours_forecast', 'Forecasted Time (in <b>Hours</b>)');
            }
            $required = ['location', 'user_id', 'dt_open', 'category', 'module_id', 'hours_actual'];
        }
        $form->addElement('textarea', 'comment', 'Comment', ['rows' => 5, 'cols' => 40]);
        $form->addElement('submit', 'btnSubmit', 'Submit');

        // Set required
        foreach ($required as $key => $field) {
            $form->addRule($field, 'Required', 'required');
        }

        $form->setDefaults($header);

        if (!$form->validate()) {
            $body .= $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $vars['dt_open'] = implode('-', $vars['dt_open']);
        $fields = ['location', 'user_id', 'dt_open', 'category', 'product_type', 'project', 'link', 'module_id', 'time_actual', 'time_forecast',
            'hours_actual', 'hours_forecast', 'comment'];
        if (!in_array((int)$location, tldTimekeeping::getErpUsingActualHours(), true)) {
            // Check time - 1 <= time >= 100 - No decimals
            if (empty($vars['time_actual']) || $vars['time_actual'] < 1 || $vars['time_actual'] > 100 || $vars['time_actual'] == 0) {
                $DEFAULT_ERROR[] = 'ERROR: You must set a time (actual hours) greater than 0 and inferior or equal to 100! No decimals.';
                $body .= $form->toHTML();
                break;
            }
            // Check Total Hours for the day < 100
            $hours_date = tldTimekeeping::getDailyHours($vars['user_id'], $vars['dt_open'], 'time_actual');
            $total_hours = $hours_date - $header['time_actual'] + $vars['time_actual'];
            $max_hours = 100 - $hours_date;
            $e = new tldUser($vars['user_id']);
            $user_selected = str_replace(',', '', $e->getFullname());
            if ($total_hours > 100) {
                $DEFAULT_ERROR[] = 'ERROR: Maximum time left for user ' . $user_selected . ', ' . $vars['dt_open'] . " is $max_hours% - Remember that the total hours scheduled for one day can not exceed 100%";
                $body .= $form->toHTML();
                break;
            }
        } elseif (empty($vars['hours_actual']) || $vars['hours_actual'] < 0 || $vars['hours_actual'] == 0 || !is_numeric($vars['hours_actual'])) {
            $DEFAULT_ERROR[] = 'ERROR: You must set a time (actual hours), greater than 0.';
            $body .= $form->toHTML();
            break;
        }
        // Update the Timesheet
        $e = $timekeeping->update($vars, $fields);
        if (is_string($e)) {
            $DEFAULT_ERROR[] = "ERROR: Problem updating...<br/>Reason: $e";
            break;
        }
        $FIELD_DESIGNATION = [
            'location' => 'Location',
            'user_id' => 'User',
            'dt_open' => 'Date',
            'category' => 'Category',
            'product_type' => 'Product Type',
            'project' => 'Project Type',
            'link' => 'Link Type',
            'module_id' => 'Module ID#',
            'time_actual' => 'Time (% of day)',
            'time_forecast' => 'Forecasted Time (% of day)',
            'hours_actual' => 'Time (Hours)',
            'hours_forecast' => 'Forecasted Time (Hours)',
            'comment' => 'Comment',

        ];
        //Get updated header
        $timekeeping_updated = new tldTimekeeping($id);
        $header_updated = $timekeeping_updated->getHeader();
        $logs = [];
        $fields_to_log = ['location', 'user_id', 'dt_open', 'category', 'product_type', 'project', 'link', 'module_id', 'time_actual', 'time_forecast', 'hours_actual', 'hours_forecast', 'comment'];
        //Log if updated header <> original header
        foreach ($fields_to_log as $field) {
            if ($header_updated[$field] != $header[$field]) {
                $logs[] = "<li><b>{$FIELD_DESIGNATION[$field]}</b> from '{$header[$field]}' to '{$header_updated[$field]}'</li>";
            }
        }
        if (count($logs)) {
            $msg = 'Timesheet updated:<br><ul>' . implode('', $logs) . '</ul>';
            $e = $timekeeping->addLogEntry(
                $user->getID(),
                TldDatabase::escape($msg)
            );
            if (is_string($e)) {
                $DEFAULT_ERROR[] = "ERROR: Problem adding logs...<br/>Reason: $e";
            }
            $body .= '<br>' . $msg;
        }
        $body .= 'Timesheet updated successfully!';
        $body .= getGeneralTab();
        break;
    default:
        $body .= getGeneralTab();
        break;
}
