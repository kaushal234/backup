{if !empty($data)}
<h3>WC - For Units Shipped In The Past 2 Years</h3>
<table cellpadding="3">
	<tr>
		<td>&nbsp;</td>
		<th style="background:#2971a8;color:#fff;">Goal</th>
		{foreach name=periods item=period from=$periods}
		<th style="background:#2971a8;color:#fff;">{$period.Ym}</th>
		{/foreach}
	</tr>
	{foreach name=data key=group item=reports from=$data}
	<tr>
		<th style="background:#2971a8;color:#fff;">{$group|escape}</th>
		<td>&nbsp;</td>
		{section name=period loop=$periods}
		<td>&nbsp;</td>
		{/section}
	</tr>
	{foreach name=reports key=report item=line from=$reports}
	<tr>
		<td>{$settings.$report.title|escape}</td>
		<td style="color:#090;font-weight:bold;text-align:center;">{$targets.$group.$report|escape}</td>
		{foreach name=periods item=period from=$periods}
		<td style="text-align:center;"
		{if $settings.$report.threshhold == '+' && (in_array($group, $types) || $report == 'MNOWC3')}
			{if ($line[$period.Ym] === null)}
			bgcolor="#ffffff"
			{elseif ($line[$period.Ym]>=1.1*$targets.$group.$report && $targets.$group.$report!=null)}
				bgcolor="#ff6666"
			{elseif ($line[$period.Ym]>=$targets.$group.$report && $targets.$group.$report!=null)}
				bgcolor="#ffff66"
			{else}
				bgcolor="#99ff66"
			{/if}
		{elseif $settings.$report.threshhold == '-' && (in_array($group, $types) || $report == 'MNOWC3')}
			{if ($line[$period.Ym] === null)}
			bgcolor="#ffffff"
			{elseif ($line[$period.Ym]>=$targets.$group.$report)}
				bgcolor="#99ff66"
			{elseif ($line[$period.Ym]>=0.9*$targets.$group.$report)}
				bgcolor="#ffff66"
			{else}
				bgcolor="#ff6666"
			{/if}
		{/if}
		>{$line[$period.Ym]|escape}</td>
		{/foreach}
	{/foreach}
	{/foreach}
</table>
<a href="/en/private/manufacturing/index.php?m[0]=kpi&m[1]=WCdata&manufacturerLocation={$manufacturerLocation}">Download Data</a>
{else}
There is no data
{/if}
