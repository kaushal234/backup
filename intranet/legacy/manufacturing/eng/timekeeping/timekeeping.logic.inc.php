<?php
include_once('eng.inc.php');
include_once('sales_service.inc.php');
require_once('HTML/QuickForm/autocomplete.php');
require_once 'HTML/QuickForm/advmultiselect.php';

if (!$user->isInGroup(['gg_ADMIN', 'acl_timekeeping', 'role_FC'])) {
	$DEFAULT_ERROR[] = 'ERROR: You do not have permission to access this module';
	return;
}

session_start();
$ID_DASH = $user->getID();
$DEFAULT_TITLE .= "\Timekeeping Module";
$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=timekeeping">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=timekeeping&m[1]=form&m[2]=add" title="Submit a new Timesheet">Submit Timesheet</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=timekeeping&m[1]=form&m[2]=quickadd" title="Submit a new Engineering Task Timesheet">Quick Submit Timesheet</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=timekeeping&m[1]=form&m[2]=byNum" title="Search a Timesheet by number">By Number</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=timekeeping&m[1]=listing&m[2]=search" title="Advanced Search">Search</a>
&nbsp;|&nbsp;<a href="/en/private/mis/mis.php?m[0]=help&m[1]=dms&id=1176">Help Page</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=timekeeping&m[1]=reports" title="Timekeeping Report">Reports</a>
EOF;
if ($user->isInGroup(['gg_ADMIN', 'role_EM', 'role_ES'])) {
	$DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="timekeeping/timekeeping_project_admin.php" title="Maintain Project/type List">Maintain Project/type List</a>
&nbsp;|&nbsp;<a href="timekeeping/timekeeping_admin.php" title="Maintain Timesheets">Maintain</a>
EOF;
}

switch ($m[1]) {
	case 'form':
		include_once('timekeeping/form.inc.php');
		break;
	case 'view':
		include_once('timekeeping/view.inc.php');
		break;
	case 'reports':
		include('timekeeping/timekeeping.reports.inc.php');
		break;
	case 'graphs':
		include('graphs.inc.php');
		break;
	case 'listing':
		$buid = $user->getBUID();
		$buid_erp = tldLocation::getERPByID($buid);
		// Choose Percentage or Hours based on location
		if (!in_array((int)$buid_erp, tldTimekeeping::getErpUsingActualHours(), true)) {
			$actual = 'time_actual';
			$actual_desc = 'Actual Hours (%)';
			$forecast = 'time_forecast';
			$forecast_desc = 'Forecast Hours (%)';
		} else {
			$actual = 'hours_actual';
			$actual_desc = 'Time (in Hours)';
			$forecast = 'hours_forecast';
			$forecast_desc = 'Forecasted Time (in Hours)';
		}
		$xItems = [
			'id' => 'Timesheet#',
			'man_location' => 'Location',
			'user_fullname' => 'User',
			'dt_open' => 'Date',
			'category' => 'Category',
			'product_type' => 'Product Type',
			'project' => 'Project Type',
			'link' => 'Link Type',
			'url' => 'Module ID#',
			$actual => $actual_desc,
			$forecast => $forecast_desc,
			'comment' => 'Comment',
		];
		switch ($m[2]) {
			case 'search':

				$xItems = [
					'id' => 'Timesheet#',
					'man_location' => 'Location',
					'user_fullname' => 'User',
					'dt_open' => 'Date',
					'category' => 'Category',
					'product_type' => 'Product Type',
					'project' => 'Project Type',
					'link' => 'Link Type',
					'url' => 'Module ID#',
					'time_actual' => 'Actual Time (in % of the day)',
					'time_forecast' => 'Forecasted Time (in % of the day)',
					'hours_actual' => 'Actual Time (in Hours)',
					'hours_forecast' => 'Forecasted Time (in Hours)',
					'comment' => 'Comment',
				];
				// Get listing
				$engList = tldGroup::getUserListByMultipleGroup(['acl_timekeeping'], '', 'smartyOptions');
				$factoryList = tldLocation::getFactoryList('smartyOptions');
				$categoryList = tldTimekeeping::getCategoryList();
				$categoryList = array_combine($categoryList, $categoryList);
				$modelList = tldModel::getList() + ['ALL_MODELS' => 'All Models', 'NEW_MODELS' => 'New Product(s)', 'MISC' => 'Miscellaneous/Not listed'];
				$projectType = tldTimekeeping::getProjectList('smartyOptions');
				$linkList = tldTimekeeping::getLinkList();
				$linkList = array_combine($linkList, $linkList);
				// Get form
				$form = new HTML_QuickForm('frmSearch', 'post');
				$form->addElement('hidden', 'm[0]', 'timekeeping');
				$form->addElement('hidden', 'm[1]', 'listing');
				$form->addElement('hidden', 'm[2]', 'search');
				$form->addElement('header', 'title', 'Search Timesheets - WARNING: A good search is a clever search');
				$form->addElement('select', 'location', 'Location', ['' => ''] + $factoryList);
				$form->addElement('select', 'user_id', 'User', ['' => ''] + $engList);
				$form->addElement('text', 'dt_open', 'Date (YYYY-MM-DD)');
				$form->addElement('select', 'category', 'Category', ['' => ''] + $categoryList);
				$form->addElement('select', 'product_type', 'Product Type', ['' => ''] + $modelList);
				$form->addElement('select', 'project', 'Project Type', ['' => ''] + $projectType);
				$form->addElement('select', 'link', 'Link Type', ['' => ''] + $linkList);
				$form->addElement('text', 'module_id', 'Module ID#');
				$form->addElement('submit', 'btnSubmit', 'Submit');

				if (!$form->validate()) {
					$body = $form->toHTML();
					break 2;
				}

				$vars = tldUtils::cleanupFormInput($form->exportValues());
				$acl_form_fields = ['location', 'user_id', 'dt_open', 'category', 'product_type', 'project', 'link', 'module_id'];
				$a = [];
				foreach ($vars as $key => $raw) {
					if (empty($raw) || !in_array($key, $acl_form_fields, true)) {
						continue;
					}
					if ($raw === '%') {
						continue;
					}
					$a[$key] = $raw;
				}
				if (empty($a)) {
					$DEFAULT_ERROR[] = 'ERROR: Not enough constraints to run a safe search...';
					break;
				}
				//Product Type field
				if (!empty($a['product_type'])) {
					$a['product_type'] = "%{$a['product_type']}%";
				}

				$TITLE = 'Search Results';
				$rows = tldTimekeeping::byConstraints($a);
				foreach ($rows as &$row) {
					if ($row['category'] === 'Engineering Task' && (int)$row['module_id'] !== 0) {
						$row['url'] = getModuleLink($row['link'], $row['module_id']);
					} else {
						$row['url'] = $row['module_id'];
					}
				}
				unset($row);
				if (isset($rows)) {
					$report = new tldReportColumnar(
						$rows,
						[
							'xItems' => $xItems,
							'title' => $TITLE,
							'links' => ['id' => "$php_self?m[0]=timekeeping&m[1]=view&id="],
						]
					);
					$body .= $report->fetch();
				}
				break;
		}
		break;
	default:
		$body .= $smarty->fetch("$PATH/timekeeping/homepage.timekeeping.tpl");
		$USER_DASH = new tldUser($ID_DASH);
		if (empty($USER_DASH->itsDetails)) {
			$DEFAULT_ERROR[] = "WARNING: Could not get dashboard, User#$ID_DASH not found...";
		}
		$buid = $USER_DASH->getBUID();
		$buid_erp = tldLocation::getERPByID($buid);
		// Choose Percentage or Hours based on location
		if (!in_array($buid_erp, tldTimekeeping::getErpUsingActualHours(), true)) {
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
			if ($row['category'] === 'Engineering Task' && (int)$row['module_id'] !== 0) {
				$row['url'] = getModuleLink($row['link'], $row['module_id']);
			} else {
				$row['url'] = $row['module_id'];
			}
		}
		unset($row);
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
				'title' => 'Today Schedule (' . $today . ') for ' . $USER_DASH->getFullname(),
				'links' => ['id' => "$php_self?m[0]=timekeeping&m[1]=view&id="],
			]
		);
		$body .= $report2->fetch();
		if (!in_array((int)$buid_erp, tldTimekeeping::getErpUsingActualHours())) {
			//Calculate Daily Hours
			$hours = tldTimekeeping::getDailyHours($ID_DASH, $today, 'time_actual');
			$body .= 'Total Actual Hours (in % of the day) scheduled for today: <b>' . $hours . '%</b>';
		}

        // SSO form SWITCH
        $nbDays = 20;
		$range = [20 => 20, 30 => 30, 50 => 50, 90 => 90];
        $frmSwitch = new HTML_QuickForm('', 'get', '', '', ['class' => 'frmSwitch'], true);
        $frmSwitch->addElement('header', 'timekeeping', 'Visualization length');
        $frmSwitch->addElement('hidden', 'm[0]', 'timekeeping');
        $frmSwitch->addElement('select', 'nbDays', 'Numbers of days', $range, ['onChange' => "javascript:$('form.frmSwitch').submit();"]);
        $frmSwitch->addRule('nbDays', '', 'regex', sprintf('/^(%s)$/', implode(')|(', $range)));
        $frmSwitch->setDefaults(['nbDays' => $nbDays]);
        if ($frmSwitch->validate()) {
            $vars = tldUtils::cleanupFormInput($frmSwitch->exportValues());
            $nbDays = (int)$vars['nbDays'];
        }

        $body .= '<br /><br /><br /><br />' .$frmSwitch->toHTML();

		// Get Last 20 Days Hours
		$factory = tldLocation::getFactoryList('smartyOptions');
		foreach ($factory as $key => $val) {
			if ($key === 0) {
				continue;
			}
			$data = tldTimekeeping::perLastXDays($key, '', $ID_DASH, 'ENG', $nbDays);
			if (count($data) > 0) {
				$reportSchedule = new tldMatrix(
					$data,
					'dt_open', 'user_fullname', 'hours_actual',
					"$php_self?m[0]=timekeeping&m[1]=reports&m[2]=perEngineerPerDay&location=$key&user_id=$ID_DASH&nbDays=$nbDays",
					"<br>Last $nbDays days Schedule in hours in $val",
					['decimals' => 'true', 'xItemsRawOrder' => true]
				);

				$body .= $reportSchedule->fetch();
			}
			$data = tldTimekeeping::perLastXDaysBySubordinates($key, $ID_DASH, 'ENG', $nbDays);
			if (count($data) > 0) {
			    $date = date('Y-m-d');
				$reportCur = new tldMatrix(
					$data,
					'dt_open', 'user_fullname', 'hours_actual',
					"$php_self?m[0]=timekeeping&m[1]=reports&m[2]=perEngineerPerDay&user_id=$ID_DASH&location=$key&nbDays=$nbDays&date=$date",
					"<br>Team members time keeping record in $val (for the last $nbDays days)",
					['decimals' => 'true', 'xItemsRawOrder' => true, 'doNotShowXTotals' => true]
				);

				$body .= $reportCur->fetch();
			}
		}
		break;

}

function getModuleLink($module, $id)
{
	if ('Task' === $module) {
		return "<a href='/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$id'>$id</a>";
	}
	if ('EAP' === $module) {
		return "<a href='/en/private/manufacturing/eng/dev.php?m[0]=eap&m[1]=view&id=$id'>$id</a>";
	}
	if ('PDC' === $module) {
		return "<a href='/en/private/product_support/index.ps.php?m[0]=pdc&m[1]=view&id=$id'>$id</a>";
	}
	if ('ER' === $module) {
		return "<a href='/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view&id=$id'>$id</a>";
	}
	if ('SB' === $module) {
		return "<a href='/en/private/product_support/index.ps.php?m[0]=sbs&m[1]=view&id=$id'>$id</a>";
	}
	if ('SB3' === $module) {
		return "<a href='/en/private/product_support/index.ps.php?m[0]=sb&m[1]=view&id=$id'>$id</a>";
	}
	if ('SOL' === $module) {
		return "<a href='/en/private/sales_service/sales.php?m[0]=sol&m[1]=view&id=$id'>$id</a>";
	}
	if ('GWF' === $module) {
		return "<a href='/en/private/calendar/calendar.php?m[0]=gwf&m[1]=view&id=$id'>$id</a>";
	}
	if ('TOC' === $module) {
		return "<a href='/en/private/sales_service/service.php?m[0]=toc&m[1]=view&id=$id'>$id</a>";
	}
	if ('WC' === $module) {
		return "<a href='/en/private/product_support/index.ps.php?m[0]=wc&m[1]=view&id=$id'>$id</a>";
	}
	if ('PIP' === $module) {
		return "<a href='/en/private/manufacturing/eng/dev.php?m[0]=pip&m[1]=view&id=$id'>$id</a>";
	}
	if ('SCAR' === $module) {
		return "<a href='/en/private/manufacturing/qa/dev.php?m[0]=scar&m[1]=view&id=$id'>$id</a>";
	}
	if ('CPA' === $module) {
		return "<a href='/en/private/manufacturing/qa/dev.php?m[0]=cpa&m[1]=view&id=$id'>$id</a>";
	}
	if ('BP' === $module) {
		return "<a href='/en/private/calendar/calendar.php?m[0]=bp&m[1]=view&id=$id'>$id</a>";
	}
	if ('MANUAL' === $module) {
		return "<a href='/en/private/product_support/index.ps.php?m[0]=publications&m[1]=manuals&m[2]=view&id=$id'>$id</a>";
	}
	if ('MEAP' === $module) {
		return "<a href='/en/private/manufacturing/eng/dev.php?m[0]=meap&m[1]=view&id=$id'>$id</a>";
	}
	if ('FAQ' === $module) {
		return "<a href='/en/private/quality/first-article-qualifications/$id/show'>$id</a>";
	}
	if ('MOM' === $module) {
		return "<a href='/en/private/meetings/$id/show'>$id</a>";
	}
}

function getGeneralTab()
{
	global $timekeeping;
	$header = $timekeeping->getHeader();

	// Create module_id link based on the link type
    $header['url'] = $header['module_id'];
    if ($header['category'] === 'Engineering Task' && (int)$header['module_id'] !== 0) {
		$header['url'] = getModuleLink($header['link'], $header['module_id']);
	}

	// General tab
	if ('MIS' !== $header['tld_dpt'] && !in_array((int)$header['location'], tldTimekeeping::getErpUsingActualHours(), true)) {
		$actual = 'time_actual';
		$actual_desc = 'Actual Hours (in % of the day)';
		$forecast = 'time_forecast';
		$forecast_desc = 'Forecasted Hours (in % of the day)';
	} else {
		$actual = 'hours_actual';
		$actual_desc = 'Time (in Hours)';
		$forecast = 'hours_forecast';
		$forecast_desc = 'Forecasted Time (in Hours)';
	}

	$fields = [
		'id' => 'Timesheet#',
		'man_location' => 'Location',
		'user_fullname' => 'User',
		'dt_open' => 'Date',
		'category' => 'Category',
		'product_type' => 'Product Type',
		'project' => 'Project Type',
		'link' => 'Link Type',
		'url' => 'Module ID#',
		'eap' => 'EAP#',
		$actual => $actual_desc,
		$forecast => $forecast_desc,
		'comment' => 'Comment',
	];

	if ($header['link'] !== 'Task' || $header['project'] !== 'EAP') {
		unset($fields['eap']);
	}
	$report = new tldAssocTable($header, $fields, ['title' => 'Timesheet Details']);
	// return general tab
	return $report->fetch();
}
