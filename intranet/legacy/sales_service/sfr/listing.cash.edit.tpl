{if $typeCashForecast <> ''}
<h3>SFR cash forecast - {$typeCashForecast}</h3>
{else}
<h3>{$title|default:'SFR Cash Forecast'}</h3>
{/if}

<table>
	<tr>
		<th>Success% = Customer Purchase % x TLD Success %</th>
		<th bgcolor="#FF0000">Success% < 25%</th>
		<th bgcolor="#FFFF00">Success% between 25% and 60%</th>
		<th bgcolor="#00FF00">Success% > 60%</th>
	</tr>
</table>

<br>

{if $mode eq "form"}<form action="{$url}" method="post">{/if}
<p class="tip"><em>Tips: Click headers to sort.</em></p>
<table class="tld_table sortable" cellpadding="2">
	<thead>
		<tr>
			<th title="Sort by Customer Name">Line</th>
			<th>SFR ID#</th>
			<th>Customer USER</br>Country<br>APC</th>
			<th>Status</th>
			<th>Factory</th>
			<th>Model and Qty</th>
			<th>ASM Fullname</th>
			<th>ASM Log</th>
			<th>PSM log</th>
			<th>Customer<br/>Purchase %</th>
			<th>TLD<br/>Success %</th>
			<th>Total<br/>Success %</th>
			<th>Year, Month</th>
			{foreach from=$months item=month}
			<th>SSO</th>
			<th>ERP</th>
			{/foreach}
			<th>&nbsp;</th>
		</tr>
		<tr>
			<th colspan="13"></th>
			{foreach from=$months item=month}
			<th colspan="2">{$month}</th>
			{/foreach}
			<th>&gt;&gt;</th>
		</tr>
	</thead>
	{foreach name=lines item=row from=$rows}
		{if $row.dt_cfsso != "0000-00-00" && $row.dt_cfsso < $today}
			<tr bgcolor="#F22613">
        {else}
    <tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
        {/if}
        <td>{$smarty.foreach.lines.iteration}</td>
        <td><a href="{$smarty.server.SCRIPT_NAME}?m[0]=sfr&m[1]=view&id={$row.id}">{$row.id}</a></td>
		<td>
	    	<b>{$row.user_display|default:"&nbsp;"}</b><br>
		    {$row.cust_ctry|default:"&nbsp;"}<br>
		    {$row.apc|default:"&nbsp;"}<br><br>
		    BUYER: {$row.buyer_display}<br>
            {if $row.third_party_display != "NO THIRD PARTY"}
				THIRD PARTY: {$row.third_party_display}
            {/if}
		</td>
        <td>{$row.status}</td>
        <td>{$row.erp_fullname}</td>
        <td>{$row.model} x{$row.qty}</td>
        <td title="ASM">
			{$row.asm_fullname|default:"&nbsp;"}
			{if $row.idle=="Y"}<img src="/shared/bluesphere/16x16/actions/history_clear.png" title="No recent updates">{/if}
		</td>
		<td>
		   	{if count($logs[$row.id]) > 0}
		       <a href="{$smarty.server.SCRIPT_NAME}?m[0]=sfr&m[1]=view&m[2]=log&id={$row.id}">
		       <img src="/shared/bluesphere/16x16/actions/toggle_log.png" class="overlib" data-header="SFR# {$row.id} ASM Log" data-body="{foreach from=$logs[$row.id] item=log}&lt;p&gt;&lt;em&gt;{$log.date}&lt;/em&gt;&lt;br/&gt;{$log.comment|escape:'htmlall'|regex_replace:'/[\r\t\n]/':'&lt;br/&gt;'}&lt;/p&gt;{/foreach}" />
		   	   </a>
		   	{else}
		       &nbsp;
		   	{/if}
		</td>
		<td>
			{if count($logs1[$row.id]) > 0}
				<a href="{$smarty.server.SCRIPT_NAME}?m[0]=sfr&m[1]=view&m[2]=log&id={$row.id}">
		        <img src="/shared/bluesphere/16x16/actions/toggle_log.png" class="overlib" data-header="SFR# {$row.id} PSM Log" data-body="{foreach from=$logs1[$row.id] item=log1}&lt;p&gt;&lt;em&gt;{$log1.date}&lt;/em&gt;&lt;br/&gt;{$log1.comment|escape:'htmlall'|regex_replace:'/[\r\t\n]/':'&lt;br/&gt;'}&lt;/p&gt;{/foreach}" />
		   	   </a>
		   	{else}
		       &nbsp;
		   	{/if}
		</td>
		<td>{$row.cust_pur_pc}</td>
		<td>{$row.tld_succ_pc}</td>
		<td bgcolor="{if $row.tot_succ_pc < 25}#FF0000{elseif $row.tot_succ_pc >= 25 && $row.tot_succ_pc <= 60}#FFFF00{elseif $row.tot_succ_pc > 60}#00FF00{/if}">
			{$row.tot_succ_pc}
		</td>
		<td>{$row.year_id}-{$row.month_id}</td>
		{foreach from=$months key=month item=label}
		<td title="{$label}" {if $typeCashForecast eq "SSO" || $mode neq "form"}bgcolor="#FFFFFF"{/if}>
			<input type="radio" name="SSO[{$row.id}]" value="{$month}" {if $month eq $row.dt_cfsso}checked="yes"{/if} {if $typeCashForecast neq "SSO" || $mode neq "form"}disabled="disabled"{/if} />
		</td>
		<td title="{$label}" {if $typeCashForecast eq "ERP"}bgcolor="#FFFFFF"{/if}>
			<input type="radio" name="ERP[{$row.id}]" value="{$month}" {if $month eq $row.dt_cferp}checked="yes"{/if} {if $typeCashForecast neq "ERP" || $mode neq "form"}disabled="disabled"{/if} />
		</td>
		{/foreach}
		<td>&nbsp;</td>
	</tr>
	{/foreach}
</table>
{if $mode eq "form"}
<input type="submit" name="submit" value="Submit">
<input type="reset" name="reset" value="Reset">
</form>
{/if}