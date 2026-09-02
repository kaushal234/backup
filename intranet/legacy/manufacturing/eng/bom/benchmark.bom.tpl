<h2>Benchmark BOM for {$bom->itsID} from company {$bom->itsERP} as of {$bom->itsDate}</h2>


<table>
	<tr>
		<th>Part Number</th>
		<th>Description</th>
		<th>Quantity</th>
		<th>Purchase price</th>
		<th>Purchase Currency</th>
		<th>Buyer</th>
		<th>Main supplier</th>
		<th>Main supplier BU2</th>
		<th>Std cost</th>
		<th>Currency</th>
		<th>Std cost BU2</th>
		<th>Currency BU2</th>	
		<th>Std cost BU2 conv</th>
		<th>Gap</th>
		<th>Gap%</th>	

	</tr>
{foreach name=bom key=key item=line from=$bom->itsBOMAsArray}
	<tr>
	<td>{$line.t_sitm|escape:"htmlall"}	</td>
	<td>{$line.t_dsca}</td>
	<td>{$line.t_qana}</td>
	<td>{$line.t_prip1}</td>
	<td>{$line.t_ccurp1}</td>
	<td>{$line.t_buyr1}</td>
	<td>{$line.t_suno1}</td>
	<td>{$line.t_suno2}</td>
	<td>{$line.t_copr1}</td>
	<td>{$line.t_ccur1}</td>
	<td>{$line.t_copr2}</td>
	<td>{$line.t_ccur2}</td>
	<td>{$line.t_copr3}</td>
	<td>{$line.t_gap1}</td>
	<td>{$line.t_gap2}%</td>
	</tr>
{/foreach}
</table>
