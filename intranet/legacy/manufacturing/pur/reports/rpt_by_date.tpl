<h3>Report: All open PO line items for ERP {$erp} Vendor {$suno}</h3>

<p>Legend<br>
<img src="/shared/bluesphere/16x16/actions/idea.png"> &nbsp;&nbsp;&nbsp;&nbsp;
<img src="/shared/bluesphere/16x16/apps/kedit.png">FAI required&nbsp;&nbsp;&nbsp;&nbsp;
<img src="/shared/bluesphere/16x16/apps/xcalc.png"> Price Change&nbsp;&nbsp;&nbsp;&nbsp;
<img src="/shared/bluesphere/16x16/actions/mail_reply.png">
</p>
<table width="1024">
<tr bgcolor="#D0D0D0">
{foreach name=titles key=field item=caption from=$fields}
	<td><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=po&field={$field}&order=
	{if $order=="ASC"}
		DESC
	{else}
		ASC
	{/if}
	&erp={$erp}&suno={$suno}">{$caption}</a></td>
{/foreach}
<td><img src="/shared/bluesphere/16x16/actions/idea.png" title="Possible drawing change since PO issue"></td>
<td><img src="/shared/bluesphere/16x16/actions/history_clear.png" title="This item is already late"><br>Late</td>
<td><img src="/shared/bluesphere/16x16/actions/history_clear.png" title="This item WILL be late"><br>Forecasted<br>Late</td>
<td><img src="/shared/bluesphere/16x16/apps/xcalc.png" title="Price Change"></td>
<td><img src="/shared/bluesphere/16x16/apps/kedit.png" title="FAI required"></td>
<td><img src="/shared/bluesphere/16x16/actions/mail_reply.png" title="Replied?"></td>
</tr>

{foreach name=lines item=line from=$pos}
	{if $plain=="YES"}
		<tr>
	{else}
		<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
	{/if}
	<td><a href="{$smarty.server.SCRIPT_NAME}?m[0]=po&m[1]=view&erp={$erp}&id={$line.t_orno}">{$line.t_orno}</a></td>
	<td>{$line.t_pono}</td>
	<td>{$line.t_odat}</td>
	{if $is_edi}
	<td><a href="/en/private/sales_service/sales.php?m[0]=so&m[1]=view&erp={$line.SOERP}&id={$line.SO}#{$line.SOL}">{$line.SO}</a></td>
	<td>{$line.SOL}</td>
	{/if}
	<td><a href="/en/private/manufacturing/eng/dev.php?m[0]=bom&m[1]=view&erp={$erp}&pn={$line.t_item}&date={$line.odat}">{$line.t_item}</a></td>
	<td>{$line.t_dsca}{if $line.t_aitc<>""}<br><b>Vendor PN:&nbsp;{$line.t_aitc}</b>{/if}</td>
	<td>{$line.t_oqua}</td>
	<td>{$line.t_dqua}</td>
	<td>{$line.t_bqua}</td>
	<td>
	{if $line.t_resc<>""}
		<strike>{$line.t_ddta}</strike>
	{else}
		{$line.t_ddta}
	{/if}
	</td>
	<td>{$line.t_resc}</td>
	<td>{$line.t_ddtc}</td>
	<td>{$line.t_ddtb}</td>
	<td>{$line.days_to_del}</td>
	<td>{$line.t_ddts}</td>
	<td>
	{if $line.change=="Y"}
		<img src="/shared/bluesphere/16x16/actions/idea.png" alt="Possible drawing change since PO issue. Pls contact your TLD Rep">
	{else}
		&nbsp;
	{/if}
	</td>
	<td>
	{if $line.late=="Y"}
		<img src="/shared/bluesphere/16x16/actions/history_clear.png" alt="This line item is late">
	{else}
		&nbsp;
	{/if}
	</td>
	<td>
	{if $line.will_be_late=="Y"}
		<img src="/shared/bluesphere/16x16/actions/history_clear.png" alt="This line item will be late">
	{else}
		&nbsp;
	{/if}
	</td>
	<td>
	{if $line.prchange=="Y"}
		<img src="/shared/bluesphere/16x16/apps/xcalc.png" alt="Price Changed from {$line.t_ltpr} to {$line.t_pric}">
	{else}
		&nbsp;
	{/if}
	</td><td>
	{if $line.fai=="Y"}
		<img src="/shared/bluesphere/16x16/apps/kedit.png" alt="FAI Required on the line item">
	{else}
		&nbsp;
	{/if}
	</td>
			<td>{if $line.su_replied=='0' || $line.su_replied=='N'}N{else}&nbsp;{/if}</td>

	</tr>
{/foreach}
</table>