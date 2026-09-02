<h2>BOM for {$bom->product} from company {$bom->site} as of {if $smarty.now|date_format:"%Y-%m-%d" > $date}<span style="color:#f00">{$date}</span>{else}{$date}{/if}</h2>
<table>
	<colgroup>
		<col span="12" />
		{foreach name=bom key=key item=line from=$otherERPs}
			<col span="6" style="{if ($key+1) % 2}background-color: lightgrey;{/if}"></col>
		{/foreach}
	</colgroup>
	<tr>
		<th colspan="12" style="text-align: center; border: 1px solid black;">{$erpNames.$mainErp}</th>
		{foreach name=bom key=key item=line from=$otherERPs}
			<th colspan="6" style="text-align: center; border: 1px solid black;">{$erpNames.$line}</th>
		{/foreach}
	</tr>
	<tr>
		<th>Part Number</th>
		<th>Level</th>
		<th>Supply Source</th>
		<th>Description</th>
		<th>Quantity</th>
		<th>Purchase Price</th>
		<th>Purchase Currency</th>
		<th>Buyer</th>
		<th>Lead Time</th>
		<th>Main Supplier</th>
		<th>Std Cost</th>
		<th>Std Cost Currency</th>
		{foreach name=bom key=key item=line from=$otherERPs}
			<th>Main Supplier</th>
			<th>Purchase Price</th>
			<th>Std Cost</th>
			<th>Std Cost Main Currency</th>
			<th>Gap</th>
			<th>Gap %</th>
		{/foreach}
	</tr>
{foreach name=bom key=key item=line from=$bom}
	<tr>
		<td style="white-space: nowrap">
			{section name=indent start=0 loop=$line->level}.{/section}
			<a href="{$smarty.server.SCRIPT_NAME}?m[0]=bom&m[1]=view&erp={$line->site}&pn={$line->product}&date={$date}">
				{$line->partNumber|escape:"htmlall"}
			</a>
		</td>
		<td>
			{section name=indent start=0 loop=$line->level}
				&nbsp;
			{/section}
			<img src="/shared/branch.gif">{$line->level}
		</td>
		<td>{$line->supplySource}</td>
		<td>{$line->itemDescription}</td>
		<td>{$line->quantity}</td>
		<td>{$line->mainPurchasing->price}</td>
		<td>{$line->mainPurchasing->currency}</td>
		<td>{$line->mainPurchasing->buyer}</td>
		<td>{$line->mainPurchasing->leadtTime}</td>
		<td>{$line->mainPurchasing->mainSupplier}</td>
		<td>{$line->mainPurchasing->standardCost}</td>
		<td>{$line->mainPurchasing->costCurrency}</td>
		{foreach name=bom key=key item=purchasingBySite from=$line->purchasingBySites}
			<td>{$purchasingBySite->mainSupplier}</td>
			<td>{$purchasingBySite->priceMainCurrency}</td>
			<td>{$purchasingBySite->standardCost}&nbsp;{$line->mainPurchasing->currency}</td>
			<td>{$purchasingBySite->standardCostMainCurrency}</td>
			<td>{$purchasingBySite->gap}</td>
			<td>{$purchasingBySite->gapPercent}%</td>
		{/foreach}
	</tr>
{/foreach}
</table>
