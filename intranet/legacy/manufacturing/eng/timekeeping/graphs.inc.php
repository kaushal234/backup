<?php
include_once('Image/Graph.php');
$JS_INCLUDE=["/shared/javascript/overlib/overlib.js"];
$smarty->assign("js_includes", $JS_INCLUDE);
include_once("graphs.definition.inc.php");

switch ($m[2]) {
	case 'perCategoryForm':
		$DEFAULT_TITLE .= "Graph - Engineering Time spent per category";
		$defaultParameters = ['hours' => '8', 'start' => date("Y-m-d", strtotime('first day of -3 months')), 'end' => date("Y-m-d", strtotime('last day of last month'))];
		// Listing
		$factoryList = tldLocation::getFactoryList("smartyOptions");
		$form = new HTML_QuickForm('frmCat', 'post');
		$form->addElement('header', 'title', "Engineering Time spent per Category");
		$form->addElement('hidden', 'm[0]', 'timekeeping');
		$form->addElement('hidden', 'm[1]', 'graphs');
		$form->addElement('hidden', 'm[2]', 'perCategoryForm');
		$form->addElement('select', 'factory', 'Factory', ["" => ""] + $factoryList);
		$form->addElement('text', 'hours', 'Hours per day');
		$form->addElement('date', 'start', 'Start Date', ["format" => "Y-m-d", 'addEmptyOption' => false, "minYear" => date("Y") - 3, "maxYear" => date("Y")]);
		$form->addElement('date', 'end', 'End Date', ["format" => "Y-m-d", 'addEmptyOption' => false, "minYear" => date("Y") - 3, "maxYear" => date("Y")]);
		$form->setDefaults($defaultParameters);
		$form->addRule('hours', 'Required', 'required');
		$form->addRule('factory', 'Required', 'required');
		$form->addElement('submit', 'btnSubmit', 'Submit');

		if ($_GET['factory']) {
			$form->setDefaults($defaultParameters + ['factory' => $_GET['factory']]);
		}

		if (!$form->validate()) {
			$body .= $form->toHTML();
			break;
		} else {
			$vars = tldUtils::cleanupFormInput($form->exportValues());
			$factory = $vars['factory'];
			$start = vsprintf('%1$04d-%2$02d-%3$02d', $vars['start']);
			$end = vsprintf('%1$04d-%2$02d-%3$02d', $vars['end']);
			$hours = $vars['hours'];
			$factory_name = TldLocation::getLocationByERP($factory);

			if (!in_array($factory, tldTimekeeping::getErpUsingActualHours())) {
				if (empty($hours)) {
					$hours = "8";
				}
				$time = "ROUND(SUM( time_actual )*$hours/100, 1) AS yval";
			} else {
				$time = "ROUND(SUM( hours_actual ), 1) AS yval";
			}

			$query = <<<EOF

SELECT
locations.location AS zval,
category AS xval,
$time
FROM timekeeping
LEFT JOIN locations ON locations.erp = timekeeping.location
WHERE tld_dpt = 'ENG' AND DATE_FORMAT(dt_open,'%Y-%m-%d') BETWEEN '$start' AND '$end' AND timekeeping.location = $factory
GROUP BY xval
EOF;

			$rows = tldUtils::getSqlToAssocArray($query);
			$total = array_sum(array_map('intval', array_column($rows, 'yval')));
			$graph = new tldGraph();

			$barDataSerie = [];
			$lineDataSerie = [];
			foreach ($rows as $row) {
				$barDataSerie[] = (int) $row['yval'];
				$lineDataSerie[] = $total > 0 ? (int) round($row['yval'] / $total * 100) : 0;
			}

			$graph->setXAxisCategories(array_column($rows, 'xval'));
			$graph->addBar($barDataSerie, ['name' => 'Hours', 'yAxis' => 0, 'pointPadding' => 0.1, 'groupPadding' => 0.1]);
			$graph->addLine($lineDataSerie, ['name' => '%', 'yAxis' => 1]);

			$graph->setTitle("Engineering Time (Hours) spent per Category for $factory_name - $start to $end - Total is $total hours");
			$graph->addYAxisOptions([
				[
					'title' => ['text' => 'hours'],
					'min' => 0,
				],
				[
					'title' => ['text' => '%'],
					'opposite' => true,
					'min' => 0,
					'max' => 100,
				]
			]);

			$body .= $graph->fetch();
			$_TITLE = "Engineering Time spent per Category";
			$popupDef = new tldOverlib($help[$_TITLE], ["CAPTION" => $_TITLE, "WIDTH" => "500", "linkName" => $_TITLE]);
			$body .= $popupDef->fetch();
		}
		break;
	case 'perProjectForm':
		$DEFAULT_TITLE .= "Graph - Engineering Timesheets spent per Module";
		$defaultParameters = ['hours' => '8', 'start' => date("Y-m-d", strtotime('first day of -3 months')), 'end' => date("Y-m-d", strtotime('last day of last month'))];
		// Listing
		$factoryList = tldLocation::getFactoryList("smartyOptions");
		$form = new HTML_QuickForm('frmCat', 'post');
		$form->addElement('header', 'title', "Engineering Timesheets spent per Module");
		$form->addElement('hidden', 'm[0]', 'timekeeping');
		$form->addElement('hidden', 'm[1]', 'graphs');
		$form->addElement('hidden', 'm[2]', 'perProjectForm');
		$form->addElement('select', 'factory', 'Factory', ["" => ""] + $factoryList);
		$form->addElement('text', 'hours', 'Hours per day');
		$form->addElement('date', 'start', 'Start Date', ["format" => "Y-m-d", 'addEmptyOption' => false, "minYear" => date("Y") - 3, "maxYear" => date("Y")]);
		$form->addElement('date', 'end', 'End Date', ["format" => "Y-m-d", 'addEmptyOption' => false, "minYear" => date("Y") - 3, "maxYear" => date("Y")]);
		$form->setDefaults($defaultParameters);
		$form->addRule('hours', 'Required', 'required');
		$form->addRule('factory', 'Required', 'required');
		$form->addElement('submit', 'btnSubmit', 'Submit');

		if ($_GET['factory']) {
			$form->setDefaults($defaultParameters + ['factory' => $_GET['factory']]);
		}

		if (!$form->validate()) {
			$body .= $form->toHTML();
			break;
		} else {
			$vars = tldUtils::cleanupFormInput($form->exportValues());
			$factory = $vars['factory'];
			$start = vsprintf('%1$04d-%2$02d-%3$02d', $vars['start']);
			$end = vsprintf('%1$04d-%2$02d-%3$02d', $vars['end']);
			$hours = $vars['hours'];
			$factory_name = TldLocation::getLocationByERP($factory);

			if (!in_array((int)$factory, tldTimekeeping::getErpUsingActualHours(), true)) {
				if (empty($hours)) {
					$hours = "8";
				}
				$time = "ROUND(SUM( time_actual )*$hours/100, 1) AS yval";
			} else {
				$time = "ROUND(SUM( hours_actual ), 1) AS yval";
			}

			$query = <<<EOF

SELECT
locations.location AS zval,
IF(project != '', project, IF(link in ('EAP', 'MEAP', 'BP', 'SOL', 'SFR'), link, 'N/A'))    AS xval,
$time
FROM timekeeping
LEFT JOIN locations ON locations.erp = timekeeping.location
WHERE tld_dpt = 'ENG' AND DATE_FORMAT(dt_open,'%Y-%m-%d') BETWEEN '$start' AND '$end' AND timekeeping.location = $factory
AND timekeeping.category NOT IN ('Vacation-Sick Leave', 'Non Productive Hours', 'Other')
GROUP BY xval
EOF;

			$rows = tldUtils::getSqlToAssocArray($query);

			$data = tldTimekeeping::getMEAPHours($factory, (int) $hours, [], 'ALL', $start, $end, null, true);
			$timeCorrection = [
				'meap1-10' => 0,
				'meap100' => 0,
				'meap1000' => 0,
				'meap10000' => 0,
				'sol' => 0,
			];
			foreach ($data as $meap) {
				switch (true) {
					case 100 > (int)$meap['ifactor']:
						$timeCorrection['meap1-10'] += $meap['hours_actual'];
						break;
					case mb_stripos($meap['meap_short_desc'], 'cust') === 0:
						$timeCorrection['sol'] += $meap['hours_actual'];
						break;
					case 10000 === (int)$meap['ifactor']:
						$timeCorrection['meap10000'] += $meap['hours_actual'];
						break;
					case 1000 === (int)$meap['ifactor']:
						$timeCorrection['meap1000'] += $meap['hours_actual'];
						break;
					default:
						$timeCorrection['meap100'] += $meap['hours_actual'];
						break;
				}
			}

			$meapDirectHours = array_sum(array_column(tldTimekeeping::getMEAPHours($factory, (int) $hours, [], 'ALL', $start, $end, null, true, true), 'hours_actual'));

			$rows[] = [
				'xval' => "MEAP\n1-10",
				'yval' => $timeCorrection['meap1-10'],
				'zval' => $rows[0]['zval'],
			];
			$rows[] = [
				'xval' => "MEAP\n100",
				'yval' => $timeCorrection['meap100'],
				'zval' => $rows[0]['zval'],
			];
			$rows[] = [
				'xval' => "MEAP\n1000",
				'yval' => $timeCorrection['meap1000'],
				'zval' => $rows[0]['zval'],
			];
			$rows[] = [
				'xval' => "MEAP\n10000",
				'yval' => $timeCorrection['meap10000'],
				'zval' => $rows[0]['zval'],
			];

			$data = [];
			foreach ($rows as $index => $row) {
				if ($row['xval'] === 'EAP') {
					$data[$index]['yval'] = round($row['yval'] + $meapDirectHours - ($timeCorrection['meap10000'] + $timeCorrection['meap1000'] + $timeCorrection['meap100']  + $timeCorrection['meap1-10'] + $timeCorrection['sol']), 2);
				} elseif ($row['xval'] === 'SOL') {
					$data[$index]['yval'] += $timeCorrection['sol'];
				} elseif ($row['xval'] === 'MEAP') {
					continue;
				} else {
					$data[$index]['yval'] = $row['yval'];
				}
				$data[$index]['xval'] = $row['xval'];
				$data[$index]['zval'] = $row['zval'];
			}

			$total = array_sum(array_map('intval', array_column($data, 'yval')));

			$graph = new tldGraph();

			$barDataSerie = [];
			$lineDataSerie = [];
			foreach ($data as $row) {
				$barDataSerie[] = (int) $row['yval'];
				$lineDataSerie[] = $total > 0 ? (int) round($row['yval'] / $total * 100) : 0;
			}

			$graph->setXAxisCategories(array_column($data, 'xval'));
			$graph->addBar($barDataSerie, ['name' => 'Hours', 'yAxis' => 0, 'pointPadding' => 0.1, 'groupPadding' => 0.1]);
			$graph->addLine($lineDataSerie, ['name' => '%', 'yAxis' => 1]);
			$graph->setTitle("Engineering Timesheets (Hours) spent per Module for $factory_name - $start to $end - Total is $total hours");
			$graph->addYAxisOptions([
				[
					'title' => ['text' => 'hours'],
					'min' => 0,
				],
				[
					'title' => ['text' => '%'],
					'opposite' => true,
					'min' => 0,
					'max' => 100,
				]
			]);
			$body .= $graph->fetch();

			$_TITLE = "Engineering Timesheets spent per Module";
			$popupDef = new tldOverlib($help[$_TITLE], ["CAPTION" => $_TITLE, "WIDTH" => "500", "linkName" => $_TITLE]);
			$body .= $popupDef->fetch();
			break;
			}
	case 'perCategoryFormFactoryComparison':
		$DEFAULT_TITLE .= "Graph - Engineering Time spent per category - Factories comparison";
		$defaultParameters = ['hours' => '8', 'start' => date("Y-m-d", strtotime('first day of -3 months')), 'end' => date("Y-m-d", strtotime('last day of last month'))];
		// Listing
		$factoryList = tldLocation::getFactoryList("smartyOptions");
		$form = new HTML_QuickForm('frmCat', 'post');
		$form->addElement('header', 'title', "Engineering Time spent per Category - Factories comparison");
		$form->addElement('hidden', 'm[0]', 'timekeeping');
		$form->addElement('hidden', 'm[1]', 'graphs');
		$form->addElement('hidden', 'm[2]', 'perCategoryFormFactoryComparison');
		$ams = $form->addElement('advmultiselect', 'factories', null, $factoryList, ['size' => 10, 'class' => 'pool', 'style' => 'width:200px;', ]);
		$form->addElement('text', 'hours', 'Hours per day');
		$form->addElement('date', 'start', 'Start Date', ["format" => "Y-m-d", 'addEmptyOption' => false, "minYear" => date("Y") - 3, "maxYear" => date("Y")]);
		$form->addElement('date', 'end', 'End Date', ["format" => "Y-m-d", 'addEmptyOption' => false, "minYear" => date("Y") - 3, "maxYear" => date("Y")]);
		$form->setDefaults($defaultParameters);
		$form->addRule('hours', 'Required', 'required');
		$form->addRule('factory', 'Required', 'required');
		$form->addElement('submit', 'btnSubmit', 'Submit');

		if (!$form->validate()) {
			$body .= $form->toHTML();
			break;
		} else {
			$vars = tldUtils::cleanupFormInput($form->exportValues());
			$factories = $vars['factories'];
			$start = vsprintf('%1$04d-%2$02d-%3$02d', $vars['start']);
			$end = vsprintf('%1$04d-%2$02d-%3$02d', $vars['end']);
			$hours = $vars['hours'];

			$normalFactories = [];
			$differentFactories = [];
			foreach ($factories as $factory) {
				if (in_array($factory, tldTimekeeping::getErpUsingActualHours())) {
					$differentFactories[] = $factory;
				} else {
					$normalFactories[] = $factory;
				}
			}

			if (empty($hours)) {
				$hours = "8";
			}
			$time = "ROUND(SUM( time_actual )*$hours/100, 1) AS yval";
			$factoryClause = implode("', '", $normalFactories);
			$query = <<<EOF
SELECT
locations.location AS zval,
category AS xval,
$time
FROM timekeeping
LEFT JOIN locations ON locations.erp = timekeeping.location
WHERE tld_dpt = 'ENG' AND DATE_FORMAT(dt_open,'%Y-%m-%d') BETWEEN '$start' AND '$end' AND timekeeping.location IN ('$factoryClause')
GROUP BY xval, zval
EOF;
			$normalRows = tldUtils::getSqlToAssocArray($query);


			$factoryClause = implode("', '", $differentFactories);
			$query = <<<EOF
SELECT
locations.location AS zval,
category AS xval,
ROUND(SUM( hours_actual ), 1) AS yval
FROM timekeeping
LEFT JOIN locations ON locations.erp = timekeeping.location
WHERE tld_dpt = 'ENG' AND DATE_FORMAT(dt_open,'%Y-%m-%d') BETWEEN '$start' AND '$end' AND timekeeping.location IN ('$factoryClause')
GROUP BY xval, zval
EOF;
			$differentRows = tldUtils::getSqlToAssocArray($query);

			$rows = [...$normalRows, ...$differentRows];

			$graph = new tldGraph();
			$graph->setTitle("Engineering Time (%) spent per Category - Factories comparison - $start to $end");
			$categories = array_values(array_unique(array_column($rows, 'xval')));
			$graph->setXAxisCategories($categories);
			$graph->addYAxisOptions([
				[
					'title' => ['text' => '%'],
					'opposite' => true,
					'min' => 0,
					'max' => 100,
				]
			]);
			$graph->setXAxisTitle('Categories');
			foreach ($factories as $factory) {
				$factoryName = TldLocation::getLocationByERP($factory);
				$values = [];
				foreach ($categories as $category) {
					$value = 0;
					foreach ($rows as $row) {
						if ($row['xval'] === $category && $row['zval'] === $factoryName) {
							$value = (float) $row['yval'];
							break;
						}
					}
					$values[] = $value;
				}
				$total = array_sum($values);
				$values = array_map(function($hits) use ($total) {
					return $hits > 0 ? round($hits / $total * 100, 1) : null;
				}, $values);
				$graph->addBar($values, ['name' => $factoryName]);
			}
			$body .= $graph->fetch();
			$_TITLE = "Engineering Time spent per Category - Factories comparison";
			$popupDef = new tldOverlib($help[$_TITLE], ["CAPTION" => $_TITLE, "WIDTH" => "500", "linkName" => $_TITLE]);
			$body .= $popupDef->fetch();
		}
		break;
	case 'perProjectFormFactoryComparison':
		$DEFAULT_TITLE .= "Graph - Engineering Time spent per module - Factories comparison";
		$defaultParameters = ['hours' => '8', 'start' => date("Y-m-d", strtotime('first day of -3 months')), 'end' => date("Y-m-d", strtotime('last day of last month'))];
		// Listing
		$factoryList = tldLocation::getFactoryList("smartyOptions");
		$form = new HTML_QuickForm('frmCat', 'post');
		$form->addElement('header', 'title', "Engineering Time spent per module - Factories comparison");
		$form->addElement('hidden', 'm[0]', 'timekeeping');
		$form->addElement('hidden', 'm[1]', 'graphs');
		$form->addElement('hidden', 'm[2]', 'perProjectFormFactoryComparison');
		$ams = $form->addElement('advmultiselect', 'factories', null, $factoryList, ['size' => 10, 'class' => 'pool', 'style' => 'width:200px;', ]);
		$form->addElement('text', 'hours', 'Hours per day');
		$form->addElement('date', 'start', 'Start Date', ["format" => "Y-m-d", 'addEmptyOption' => false, "minYear" => date("Y") - 3, "maxYear" => date("Y")]);
		$form->addElement('date', 'end', 'End Date', ["format" => "Y-m-d", 'addEmptyOption' => false, "minYear" => date("Y") - 3, "maxYear" => date("Y")]);
		$form->setDefaults($defaultParameters);
		$form->addRule('hours', 'Required', 'required');
		$form->addRule('factory', 'Required', 'required');
		$form->addElement('submit', 'btnSubmit', 'Submit');

		if (!$form->validate()) {
			$body .= $form->toHTML();
			break;
		} else {
			$vars = tldUtils::cleanupFormInput($form->exportValues());
			$factories = $vars['factories'];
			$start = vsprintf('%1$04d-%2$02d-%3$02d', $vars['start']);
			$end = vsprintf('%1$04d-%2$02d-%3$02d', $vars['end']);
			$hours = $vars['hours'];

			$allRows = [];
			foreach ($factories as $factory) {
				if (!in_array((int)$factory, tldTimekeeping::getErpUsingActualHours(), true)) {
					if (empty($hours)) {
						$hours = "8";
					}
					$time = "ROUND(SUM( time_actual )*$hours/100, 1) AS yval";
				} else {
					$time = "ROUND(SUM( hours_actual ), 1) AS yval";
				}

				$query = <<<EOF
SELECT
locations.location AS zval,
IF(project != '', project, IF(link in ('EAP', 'MEAP', 'BP', 'SOL', 'SFR'), link, 'N/A'))    AS xval,
$time
FROM timekeeping
LEFT JOIN locations ON locations.erp = timekeeping.location
WHERE tld_dpt = 'ENG' AND DATE_FORMAT(dt_open,'%Y-%m-%d') BETWEEN '$start' AND '$end' AND timekeeping.location = $factory
				AND timekeeping.category NOT IN ('Vacation-Sick Leave', 'Non Productive Hours', 'Other')
GROUP BY xval
EOF;
				$rows = tldUtils::getSqlToAssocArray($query);
				$data = tldTimekeeping::getMEAPHours($factory, (int) $hours, [], 'ALL', $start, $end, null, true);
				$timeCorrection = [
					'meap1-10' => 0,
					'meap100' => 0,
					'meap1000' => 0,
					'meap10000' => 0,
					'sol' => 0,
				];
				foreach ($data as $meap) {
					switch (true) {
						case 100 > (int)$meap['ifactor']:
							$timeCorrection['meap1-10'] += $meap['hours_actual'];
							break;
						case mb_stripos($meap['meap_short_desc'], 'cust') === 0:
							$timeCorrection['sol'] += $meap['hours_actual'];
							break;
						case 10000 === (int)$meap['ifactor']:
							$timeCorrection['meap10000'] += $meap['hours_actual'];
							break;
						case 1000 === (int)$meap['ifactor']:
							$timeCorrection['meap1000'] += $meap['hours_actual'];
							break;
						default:
							$timeCorrection['meap100'] += $meap['hours_actual'];
							break;
					}
				}

				$meapDirectHours = array_sum(array_column(tldTimekeeping::getMEAPHours($factory, (int) $hours, [], 'ALL', $start, $end, null, true, true), 'hours_actual'));

				$rows[] = [
					'xval' => "MEAP\n1-10",
					'yval' => $timeCorrection['meap1-10'],
					'zval' => $rows[0]['zval'],
				];
				$rows[] = [
					'xval' => "MEAP\n100",
					'yval' => $timeCorrection['meap100'],
					'zval' => $rows[0]['zval'],
				];
				$rows[] = [
					'xval' => "MEAP\n1000",
					'yval' => $timeCorrection['meap1000'],
					'zval' => $rows[0]['zval'],
				];
				$rows[] = [
					'xval' => "MEAP\n10000",
					'yval' => $timeCorrection['meap10000'],
					'zval' => $rows[0]['zval'],
				];

				$total = 0;
				$data = [];
				foreach ($rows as $index => $row) {
					if ($row['xval'] === 'EAP') {
						$data[$index]['yval'] = round($row['yval'] + $meapDirectHours - ($timeCorrection['meap10000'] + $timeCorrection['meap1000'] + $timeCorrection['meap100']  + $timeCorrection['meap1-10'] + $timeCorrection['sol']), 2);
					} elseif ($row['xval'] === 'SOL') {
						$data[$index]['yval'] += $timeCorrection['sol'];
					} elseif ($row['xval'] === 'MEAP') {
						continue;
					} else {
						$data[$index]['yval'] = $row['yval'];
					}
					$data[$index]['xval'] = $row['xval'];
					$data[$index]['zval'] = $row['zval'];
					$total = $total + $row['yval'];
				}
				$allRows = array_merge($allRows, $data);
			}

			$graph = new tldGraph();
			$graph->setTitle("Engineering Time (%) spent per module - Factories comparison - $start to $end");
			$categories = array_values(array_unique(array_column($rows, 'xval')));
			$graph->setXAxisCategories($categories);
			$graph->addYAxisOptions([
				[
					'title' => ['text' => '%'],
					'opposite' => true,
					'min' => 0,
					'max' => 100,
				]
			]);
			$graph->setXAxisTitle('Categories');
			foreach ($factories as $factory) {
				$factoryName = TldLocation::getLocationByERP($factory);
				$values = [];
				foreach ($categories as $category) {
					$value = 0;
					foreach ($allRows as $row) {
						if ($row['xval'] === $category && $row['zval'] === $factoryName) {
							$value = (float) $row['yval'];
							break;
						}
					}
					$values[] = $value;
				}
				$total = array_sum($values);
				$values = array_map(function($hits) use ($total) {
					return $hits > 0 ? round($hits / $total * 100, 1) : null;
				}, $values);
				$graph->addBar($values, ['name' => $factoryName]);
			}
			$body .= $graph->fetch();
			$_TITLE = "Engineering Timesheets spent per Module - Factories comparison";
			$popupDef = new tldOverlib($help[$_TITLE], ["CAPTION" => $_TITLE, "WIDTH" => "500", "linkName" => $_TITLE]);
			$body .= $popupDef->fetch();
		}
		break;
}
?>
