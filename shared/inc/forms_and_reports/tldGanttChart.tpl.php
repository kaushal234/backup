<?php

$icons = [
	'calendar' =>	'/shared/bluesphere/16x16/actions/1day.png',
	'close' =>		'/shared/bluesphere/16x16/actions/no.png',
	'configure' =>	'/shared/bluesphere/16x16/actions/configure.png',
	'idea' =>		'/shared/bluesphere/16x16/actions/idea.png',
	'launch' =>		'/shared/bluesphere/16x16/actions/launch.png',
	'log' =>		'/shared/bluesphere/16x16/actions/toggle_log.png',
	'mail' =>		'/shared/bluesphere/16x16/actions/mail_generic.png',
	'person' =>		'/shared/bluesphere/16x16/apps/personal.png',
	'schedule' =>	'/shared/bluesphere/16x16/actions/appointment.png',
	'todo' =>		'/shared/bluesphere/16x16/actions/newtodo.png',
	'transfer' =>	'/shared/bluesphere/16x16/actions/mail_forward.png',
];
?>

<?php if (!empty($this->itsOrder) && !empty($this->itsData)) : ?>

<style type="text/css">
.gantt {
	width: 100%;
}
.gantt th, .gantt td {
	text-align: center;
}
.gantt th {
	white-space: nowrap;
	padding: 3px 8px;
}
.gantt .grouped th.title {
	text-align: left;
	background: #090;
	color: #fff;
}
.gantt .grouped th.subgroupedtitle {
	text-align: center;
	background: #050;
	color: #fff;
}
.gantt .columned th {
	background: #2971a8;
	color: #fff;
}
.gantt .columned th a {
	color: #ff0;
}
.gantt img {
	vertical-align: middle;
	border: 0px;
}
.gantt tbody tr.even {
	background: #eee;
}
.gantt tbody tr.odd {
	background: #d0d0d0;
}
.gantt tbody td {
	white-space: nowrap;
}
.gantt td.type-description {
	width: 30%;
	text-align: left;
	white-space: normal;
}
.gantt th.type-gantt {
	background: #ffe;
	color: #000;
	padding: 3px;
}
.gantt .type-gantt span {
	min-width: 16px;
	display: inline-block;
}
.gantt .now {
	background: #669 !important;
	color: #fff !important;
}
.gantt .start {
	background: #0f0 !important;
}
.gantt .start-now {
	background: #0c3 !important;
}
.gantt .due {
	background: #f00 !important;
}
.gantt .due-now {
	background: #c03 !important;
}
.gantt .open {
	background: #ff3 !important;
}
.gantt .open-now {
	background: #cccc5c !important;
}
.gantt .type-meter div {
	background: #999;
	width: 100px;
	height: 16px;
}
.gantt .type-meter span {
	width: 10px;
	height: 16px;
	display: inline-block;
}
.gantt .type-meter span.complete {
	background: #0a0;
}
.gantt .type-ifactor {
	font-weight: bold;
	padding-left: 8px;
	padding-right: 8px;
}
.gantt .if-1 {
	background: #ff0;
}
.gantt .if-10 {
	background: #fc0;
}
.gantt .if-100 {
	background: #f90;
	color: #fff;
}
.gantt .if-1000 {
	background: #f00;
	color: #fff;
}
.gantt .if-10000 {
	background: #920000;
	color: #fff;
}
</style>
<?php if ($this->itsOptions['title']): ?>
<h3><?= $this->toHtml($this->itsOptions['title']) ?></h3>
<?php endif; ?>

<table<?= $this->setAttributes(['class'=>'gantt','border'=>0,'cellspacing'=>2,'cellpadding'=>3], $this->strFormat($this->itsOptions['parentAttributes']['table'], $this->itsModule)) ?>>
	<thead<?= $this->setAttributes($this->strFormat($this->itsOptions['parentAttributes']['thead'], $this->itsModule)) ?>>

	<?php if ($this->hasGroupHeaders()): ?>
		<?php for ($h = ($this->hasSubGroupHeaders()) ? 2 : 1, $i = 0; $i < $h; $i++): ?>

		    <tr<?= $this->setAttributes($this->strFormat($this->itsOptions['rowAttributes']['thead'], $this->itsModule), ['class'=>'grouped']) ?>>

			<?php foreach ($this->itsHeaders as $header): ?>
                <?php if ($i > 0 && $header['subgrouped'] && $this->hasSubGroupHeaders()): ?>
                    <?php
                        $field = $header['field'];
                        $settings = $this->itsOptions['columnSettings'][$field];
                    ?>
                    <?php foreach ($settings['subgroups'] as $title => $group): ?>
                        <th<?= $this->setAttributes($this->strFormat($header['attributes'], $this->itsModule), ['colspan'=>count($group),'class'=>'subgroupedtitle']) ?>><?= $this->toHtml($title) ?></th>
                    <?php endforeach;?>

                <?php elseif ($i === 0 && !$header['subgrouped'] && $this->hasSubGroupHeaders()): ?>
			        <th<?= $this->setAttributes($this->strFormat($header['attributes'], $this->itsModule), ['colspan'=>count($header['columns'])]) ?>>&nbsp;</th>
                <?php else: ?>
			        <th<?= $this->setAttributes($this->strFormat($header['attributes'], $this->itsModule), ['colspan'=>count($header['columns']),'class'=>isset($header['title'])?'title':'']) ?>><?= $this->toHtml($header['title']) ?></th>
                <?php endif; ?>
			<?php endforeach; ?>
		</tr>
        <?php endfor; ?>
    <?php endif; ?>
		<tr<?= $this->setAttributes($this->strFormat($this->itsOptions['rowAttributes']['thead'], $this->itsModule), ['class'=>'columned']) ?>>
<?php
	foreach ($this->itsHeaders as $header) {
		foreach ($header['columns'] as $column) {
			$field = $column['field'];
			$settings = $this->itsOptions['columnSettings'][$field];
			switch ($settings['type']) {
				case 'gantt':
					$class = 'type-gantt';
					$tic = $column['tic'];
					$abs = abs($tic);

					if ($abs === 0) {
						$title = ('day' === $settings['zoom']) ? 'today' : "this {$settings['zoom']}";
						$class .= ' now';
					} elseif ($abs === 1) {
					    if ('day' === $settings['zoom']) {
                            $title =  $tic > 0 ? 'tomorrow' : 'yesterday';
                        } else {
                            $title = ($tic > 0 ? 'next' : 'last') . " {$settings['zoom']}";
                        }
					} else {
						$title = "$abs {$settings['zoom']}s " . (($tic > 0) ? 'from now' : 'ago');
					}
					$offset = $settings['offset'][$tic];
					switch ($settings['zoom']) {
						case 'day':
							$title .= ' ' . $this->strFormat('(%s)',date('m/d', $offset['A']));
						break;
						case 'week':
						case 'month':
							$title .= ' ' . $this->strFormat('(%s - %s)',date('m/d', $offset['A']),date('m/d', $offset['B']));
						break;
					}

?>
			<th<?= $this->setAttributes($this->strFormat($settings['thAttributes'], $this->itsModule), ['class'=>$class,'title'=>$title]) ?>><nobr><?= $this->toHtml($column['title']) ?></nobr></th>
<?php
				break;
				default:
?>
			<th<?= $this->setAttributes($this->strFormat($settings['thAttributes'], $this->itsModule), isset($settings['type']) ? ['class'=>"type-{$settings['type']}"] : '') ?>>
            <?php if (in_array($field, $this->itsOptions['sortable'], true)): ?>
                <a<?= $this->setAttributes(['href'=>"$php_self?" . http_build_query(array_merge($_GET, ['sort'=>$field]))],($settings['useIconHeader'] && isset($icons[$settings['icon']])) ? '' : ['title'=>'click to sort']) ?>>
            <?php endif; ?>
			<?php if($settings['useIconHeader'] && isset($icons[$settings['icon']])): ?>
				<img<?= $this->setAttributes(['src'=>$icons[$settings['icon']],'title'=>$column['title']]) ?> />
			<?php else: ?>
				<nobr><?= $this->toHtml($column['title']) ?></nobr>
			<?php endif; ?>
			<?= in_array($field, $this->itsOptions['sortable'], true) ? '</a>' : '' ?>
			</th>
<?php
				break;
			}
		}
	}
?>
		</tr>
	</thead>
	<tbody<?= $this->setAttributes($this->strFormat($this->itsOptions['parentAttributes']['tbody'], $this->itsModule)) ?>>
	<?php foreach ($this->itsData as $row => $line): ?>
		<tr<?= $this->setAttributes(['class'=>($row % 2 === 0) ? 'odd' : 'even'], $this->strFormat($this->itsOptions['rowAttributes']['tbody'], $row, $this->itsModule)) ?>>
<?php
		foreach ($this->itsHeaders as $header):
			foreach ($header['columns'] as $column):
				$field = $column['field'];
				$settings = $this->itsOptions['columnSettings'][$field];
				$value = $line[$field];
				$html = '';

				if (!empty($this->itsOptions['links'][$field]) && !empty($value)) {
					$link = $this->itsOptions['links'][$field];
					if (is_array($link)) {
						$url = $link['url'];
                        $paramsUrl = [];
                        if(!empty($link['params'])) {
                            foreach($link['params'] as $paramUrl => $columnValKey) {
                                $paramsUrl[$paramUrl] = $line[$columnValKey];
                            }
                        }
                        $url.= (!empty($paramsUrl)) ? '&'.http_build_query($paramsUrl) : '';
						$html .= '<a' . $this->setAttributes($link['options'], ['href'=>$url]) . '>';
					} else {
						$html .= '<a href="' . $link . urlencode($value) . '">';
					}
				}

				if ($settings['useIconValue'] && isset($icons[$settings['icon']])) {
					if ($settings['imgTitle']) {
						$title = $line[$settings['imgTitle']];
					} elseif ($settings['title']) {
						$title = $this->strFormat($settings['title'], $value, $row, $this->itsModule);
					}
					$html .= '<img' . $this->setAttributes(['src'=>$icons[$settings['icon']]], $this->strFormat($settings['imgAttributes'], $value, $row, $this->itsModule), isset($title) ? ['title'=>$title] : '') . ' />';
				}

				$tdAttributes = ['class'=>'type-' . (!empty($settings['type']) ? $settings['type'] : 'text')];

				if ($settings['tdTitle']) {
					$tdAttributes['title'] = $line[$settings['tdTitle']];
				} elseif ($settings['title']) {
					$tdAttributes['title'] = $this->strFormat($settings['title'], $value, $row, $this->itsModule);
				}

				if (isset($this->itsOptions['groupAttributes'][$field])) {
					if (array_key_exists($value, $this->itsOptions['groupAttributes'][$field])) {
						$tdAttributes = array_merge($tdAttributes, $this->attrArray($this->itsOptions['groupAttributes'][$field][$value]));
					} elseif (isset($this->itsOptions['groupAttributes'][$field][0])) {
						$tdAttributes = array_merge($tdAttributes, $this->attrArray($this->itsOptions['groupAttributes'][$field][0]));
					}
				}

				switch ($settings['type']) {
					case 'gantt':
						$today = $this->date;

						$unix = strtotime($value);
						$start = mktime(0,0,0,date('m', $unix),date('d', $unix),date('Y', $unix));

                        $due = 0;
						if ($settings['due']) {
							$unix = strtotime($line[$settings['due']]);
							$due = mktime(0,0,0,date('m', $unix),date('d', $unix),date('Y', $unix));
						}

						$offsetA = $settings['offset'][$column['tic']]['A'];
						$offsetB = $settings['offset'][$column['tic']]['B'];

						switch (true) {
							case ($this->between($today, $offsetA, $offsetB) AND $this->between($start, $offsetA, $offsetB)):
								$class = 'start-now';
								$title = 'started on ' . date('m/d/Y', $start);
							break;
							case ($this->between($start, $offsetA, $offsetB)):
								$class = 'start';
								$title = 'started on ' . date('m/d/Y', $start);
							break;
							case ($due > 0 AND $this->between($today, $offsetA, $offsetB) AND $this->between($due, $offsetA, $offsetB)):
								$class = 'due-now';
								$title = 'due on ' . date('m/d/Y', $due);
							break;
							case ($due > 0 AND $this->between($due, $offsetA, $offsetB)):
								$class = 'due';
								$title = 'due on ' . date('m/d/Y', $due);
							break;
							case ($due > 0 AND $this->between($today, $offsetA, $offsetB) AND $this->between($offsetA, $start, $due)):
								$class = 'open-now';
								$title = 'currently open';
							break;
							case ($due > 0 AND $this->between($offsetA, $start, $due)):
								$class = 'open';
								$title = 'open';
							break;
							case ($this->between($today, $offsetA, $offsetB)):
								$class = 'now';
								$title = '';
							break;
							default:
								$class = '';
								$title = '';
							break;
						}

						$tdAttributes['class'] .= " $class";
						$tdAttributes['title'] = $title;

						$html .= '<span>';
						$html .= is_callable($settings['displayCallback']) ? call_user_func($settings['displayCallback'], $line, $offsetA, $offsetB) : '&nbsp;';
						$html .= '</span>';
					break;
					case 'meter':
                        $value = $value < 0 ? 0 : min(100, round($value));
						$complete = round($value,-1);
						$meter = [];
						for ($i = 0; $i < 100; $i += 10) {
                            $meter[] = $complete > $i;
                        }

						$html .= '<div' . $this->setAttributes(['title'=>"$value% {$column['title']}"]) . '>';
						foreach ($meter as $m) {
                            $html .= '<span' . $this->setAttributes(['class' => $m ? 'complete' : 'incomplete']) . '></span>';
                        }
                        $html .= '</div>';
					    break;
					case 'ifactor':
						$value = in_array((int) $value, [1, 10, 100, 1000, 10000], true) ? $value : 0;
						$tdAttributes['class'] .= " if-$value";
						$html .= $value;
					    break;
					default:
						// Type text or description
                        $html .= !$settings['useIconValue'] || !isset($icons[$settings['icon']]) ? trim($value) : '';
					    break;
				}

				$html .= !empty($this->itsOptions['links'][$field]) && !empty($value) ? '</a>' : '';
?>
			<td<?= $this->setAttributes($this->strFormat($settings['cellAttributes'], $value, $row, $this->itsModule), $tdAttributes) ?>><?= $html ?></td>

			<?php endforeach; ?>
        <?php endforeach; ?>
		</tr>
	<?php endforeach; ?>

	</tbody>
</table>

<?php else : ?>
<div>No Data</div>
<?php endif; ?>
