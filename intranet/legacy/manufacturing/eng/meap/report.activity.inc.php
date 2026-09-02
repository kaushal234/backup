<?php
// Form
$DEFAULT_TITLE .= "\Activity Report";
$form = new HTML_QuickForm('frmReport', 'post');
$form->addElement(	'header', 'title', 'Select Activity Date Range');
$form->addElement(	'hidden', 'm[0]', 'meap');
$form->addElement(	'hidden', 'm[1]', 'view');
$form->addElement(	'hidden', 'm[2]', 'reports');
$form->addElement(	'hidden', 'm[3]', 'byActivityDate');
$form->addElement(	'hidden', 'id', $id);
$form->addElement(	'date', 'starting', 'Between Starting Date', array("format"=>"Ymd","maxYear"=>date("Y")));
$form->addElement(	'date', 'ending', 'And Ending Date', array("format"=>"Ymd","maxYear"=>date("Y")));
$form->addElement(	'submit', 'btnSubmit', ' GO ');
$form->setDefaults(array(
	'starting' => array("d"=>substr($header['date'],8,2),"m"=>substr($header['date'],5,2),"Y"=>substr($header['date'],0,4)),
	'ending' => array("d"=>date("d"),"m"=>date("m"),"Y"=>date("Y")),
));

if ($form->validate()){
	$vars = $form->exportValues();
	$starting = mktime(0,0,0,$vars['starting']["m"],$vars['starting']["d"],$vars['starting']["Y"]);
	$ending = mktime(23,59,59,$vars['ending']["m"],$vars['ending']["d"],$vars['ending']["Y"]);
	$master = array();
	$linked = _getModLinked('MEAP', $id, array('status'=>$header['status'],'description'=>$header['short_desc']));
	if (!empty($linked))
	{
		$master[] = $linked;
	}
	$smarty->assign('meapid', $id);
	$smarty->assign('daterange', date('Y-m-d',$starting).' - '.date('Y-m-d',$ending));
	$body .= $smarty->fetch("manufacturing/eng/meap/report.activity.tpl");
	$body .= _getActivityBlock($master);
}else{
	$body = $form->toHTML();
}


// Functions
function _getModLinked($module, $id, $info, $level = 0)
{
	static $linked = array();

	if (!in_array($id, $linked[$module] ?? []))
	{
		$linked[$module][] = $id;

		$current = array(
			'module' => $module,
			'id' => $id,
			'level' => $level,
		);

		if (!empty($info['status'])) $current['status'] = $info['status'];
		if (!empty($info['description'])) $current['description'] = $info['description'];
		$activities = _getModActivity($module, $id);
		if (!empty($activities)) $current['activity'] = $activities;

		$mods = array();

		if (in_array($module, array('EAP','MEAP','GWF','BP')))
		{
			// Get tasks
			$query=<<<EOF
			SELECT id, 'TASK' AS module, status
			FROM tasks
			WHERE tasks.module='$module' AND tasks.parent_id=$id
			ORDER BY id
EOF;
			$mods = array_merge($mods, tldUtils::getSqlToAssocArray($query));

			// Get logs
			$query=<<<EOF
			SELECT id, 'LOG' AS module
			FROM mod_logs
			WHERE mod_logs.module='$module' AND mod_logs.parent_id=$id
			ORDER BY id
EOF;
			$mods = array_merge($mods, tldUtils::getSqlToAssocArray($query));

			// Get files
			$query=<<<EOF
			SELECT id, 'FILE' AS module
			FROM mod_files
			WHERE mod_files.module='$module' AND mod_files.parent_id=$id
			ORDER BY id
EOF;
			$mods = array_merge($mods, tldUtils::getSqlToAssocArray($query));
		}

		if (in_array($module, array('EAP','MEAP','GWF')))
		{
			// Get linked eaps
			$query=<<<EOF
			SELECT id, 'EAP' AS module, status, short_desc AS description
			FROM eap
			WHERE parent_id=$id
			ORDER BY id
EOF;
			$mods = array_merge($mods, tldUtils::getSqlToAssocArray($query));

			// Get linked meaps
			$query=<<<EOF
			SELECT id, 'EAP' AS module, status, short_desc AS description
			FROM meap
			WHERE parent_id=$id
			ORDER BY id
EOF;
			$mods = array_merge($mods, tldUtils::getSqlToAssocArray($query));

			// Get linked gwfs
			$query=<<<EOF
			SELECT
				id,
				module,
				status,
				description,
				pvt
			FROM
				(
					SELECT
						mod_links.item AS id,
						'GWF' AS module,
						(
							SELECT status
							FROM gwf
							WHERE gwf.id=mod_links.item
						) AS status,
						(
							SELECT dsca
							FROM gwf
							WHERE gwf.id=mod_links.item
						) AS description,
						(
							SELECT pvt
							FROM gwf
							WHERE gwf.id=mod_links.item
						) AS pvt
					FROM
						mod_links
					WHERE
						mod_links.module='$module' AND
						mod_links.parent_id=$id AND
						mod_links.type='GWF'
				UNION
					SELECT
						mod_links.parent_id AS id,
						'GWF' AS module,
						(
							SELECT status
							FROM gwf
							WHERE gwf.id=mod_links.parent_id
						) AS status,
						(
							SELECT dsca
							FROM gwf
							WHERE gwf.id=mod_links.parent_id
						) AS description,
						(
							SELECT pvt
							FROM gwf
							WHERE gwf.id=mod_links.parent_id
						) AS pvt
					FROM
						mod_links
					WHERE
						mod_links.type='$module' AND
						mod_links.item=$id AND
						mod_links.module='GWF'
				) x
			WHERE
				pvt='N'
			ORDER BY
				id
EOF;
			$mods = array_merge($mods, tldUtils::getSqlToAssocArray($query));

			// Get linked bps
			$query=<<<EOF
			SELECT id, 'BP' AS module, status, short_desc AS description
			FROM cal_bp
			WHERE cal_bp.module='$module' AND cal_bp.parent_id=$id
			ORDER BY id
EOF;
			$mods = array_merge($mods, tldUtils::getSqlToAssocArray($query));
		}

		foreach ($mods AS $mod)
		{
			// Go deeper activity linked
			$links = _getModLinked($mod['module'], $mod['id'], $mod, $level + 1);
			if (!empty($links)) $current['linked'][] = $links;
		}

		// If no activity linked
		if (!count($current['activity'] ?? []) AND !count($current['linked'] ?? []))
		{
			return array();
		}

		return $current;
	}

	return array();
}


function _getModActivity($mod, $id)
{
	switch ($mod)
	{
		case 'EAP':
			$query=<<<EOF
			SELECT dt_opened AS opened, dt_closed AS closed
			FROM eap
			WHERE id=$id
EOF;
			return _activityArray(tldUtils::getSqlToAssocArray($query));
		break;
		case 'MEAP':
			$query=<<<EOF
			SELECT date AS opened, date_closed AS closed, date_suspended AS suspended
			FROM meap
			WHERE id=$id
EOF;
			return _activityArray(tldUtils::getSqlToAssocArray($query));
		break;
		case 'GWF':
			$query=<<<EOF
			SELECT date AS opened, dt_closed AS closed, d_escal AS escalated
			FROM gwf
			WHERE id=$id
EOF;
			return _activityArray(tldUtils::getSqlToAssocArray($query));
		break;
		case 'BP':
			$query=<<<EOF
			SELECT dt_opened AS opened, dt_closed AS closed
			FROM cal_bp
			WHERE id=$id
EOF;
			return _activityArray(tldUtils::getSqlToAssocArray($query));
		case 'TASK':
			$query=<<<EOF
			SELECT date AS opened, dt_closed AS closed, d_escal AS escalated
			FROM tasks
			WHERE id=$id
EOF;
			$tasks = tldUtils::getSqlToAssocArray($query);
			$query=<<<EOF
			SELECT c.date AS posted, CONCAT(UPPER(p.lastname),', ',p.firstname,' ',c.date,': ',c.comment) AS info
			FROM tasks_comments c LEFT JOIN people p ON p.id=c.poster
			WHERE c.parent_id=$id
			ORDER BY c.date
EOF;
			$comments = tldUtils::getSqlToAssocArray($query);
			return _activityArray(array_merge($tasks, $comments));
		break;
		case 'FILE':
			$query=<<<EOF
			SELECT date AS posted, description AS info
			FROM mod_files
			WHERE id=$id
EOF;
			return _activityArray(tldUtils::getSqlToAssocArray($query));
		case 'LOG':
			$query=<<<EOF
			SELECT l.date AS posted, CONCAT(UPPER(p.lastname),', ',p.firstname,' ',l.date,': ',l.comment) AS info
			FROM mod_logs l LEFT JOIN people p ON p.id=l.poster
			WHERE l.id=$id
EOF;
			return _activityArray(tldUtils::getSqlToAssocArray($query));
		break;
	}

	return array();
}


function _activityArray($res)
{
	global $starting, $ending;

	$array = array();

	foreach (array('opened','posted','escalated','suspended','closed') AS $activity)
	{
		foreach ($res AS $row)
		{
			foreach ($row AS $key => $date)
			{
				if ($key === $activity AND strtotime($date) >= $starting AND strtotime($date) <= $ending)
				{
					$a = array(
						'activity' => $activity,
						'dt' => $date,
					);
					if (!empty($row['info']))
					{
						$a['info'] = trim($row['info']);
					}
					$array[] = $a;
				}
			}
		}
	}

	return $array;
}


function _getActivityBlock($linked)
{
	global $smarty;
	$block = '';
	if (!empty($linked))
	{
		foreach ($linked AS $mod)
		{
			$smarty->assign('mod', $mod);
			$html = $smarty->fetch("manufacturing/eng/meap/report.activity.mod.tpl");
			$block .= str_replace('%%LINKED_MODS%%', _getActivityBlock($mod['linked']), $html);
		}
	}
	return $block;
}

