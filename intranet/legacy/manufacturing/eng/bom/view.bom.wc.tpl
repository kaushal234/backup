<h2>Warranty Claims count for {$bom->itsID} from company {$bom->itsERP} as of {$bom->itsDate}</h2>
<table>
	<tr>
		<th>Part Number</th>
		<th>Position</th>
		<th>Description</th>
		<th>WC Count</th>
		<th>Qty</th>
		<th>UM</th>
		<th>Status</th>
		<th>Drawing</th>
		<th>Rev</th>
		<th>Effective Date</th>
		<th>Code</th>
		<th>Extra Info</th>
		<th>Owner</th>
	</tr>
{foreach name=bom key=key item=line from=$bom_wc}
	<tr><td>
	{section name=indent start=0 loop=$line.level}
		.
	{/section}
	<a href="{$smarty.server.SCRIPT_NAME}?m[0]=bom&m[1]=view&erp={$bom->itsERP}&pn={$line.t_sitm}&date={$bom->itsDate}">
	{$line.t_sitm|escape:"htmlall"}
	</a>
	</td>
	<td>
		{section name=indent start=0 loop=$line.level}
			&nbsp;
		{/section}
		<img src="/shared/branch.gif">{$line.t_pono}</td>
	<td>{$line.t_dsca}</td>
	<td align="center">
	{if $line.wccount != ""}
		<a href="/en/private/product_support/index.ps.php?m[0]=wc&m[1]=listing&m[2]=byPN&pn={$line.t_sitm}">
		{$line.wccount}
	{else}
		0
	{/if}
	</td>
	<td>{$line.t_qana}</td>
	<td>{$line.t_cuni}</td>
	<td>
	{if $line.t_exdt=="0000-00-00" OR $line.t_exdt=="1753-01-01"}
		OK
	{else}
		This item was changed {$line.t_exdt}
	{/if}
	</td>
	<td>
		<a target="_blank" href="{$smarty.server.SCRIPT_NAME}?m[0]=getfile&m[1]=drawing&erp={$bom->itsERP}&item={$line.t_sitm}&date={$bom->itsDate}">
		<img src="/shared/bluesphere/16x16/actions/filesaveas.png" alt="Save file  to your hard disk"></a>
	</td>
	<td>{$line.t_revi}</td>
	<td>{$line.t_indt}</td>
	<td>{$line.t_csig_edm}</td>
	<td>{$line.t_exin}</td>
	<td>{$line.t_csel}</td>
	</tr>
{/foreach}
</table>