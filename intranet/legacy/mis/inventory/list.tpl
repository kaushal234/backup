{literal}
<style type="text/css">
#SFR_report table thead th{
	color: white;
	background: #2a70ab;
}
</style>
{/literal}


<br>
{php}$this->assign('_url', $_SERVER['SCRIPT_NAME'].'?'.http_build_query($_POST + $_GET));{/php}

<div id="SFR_report">
	{$pagination->getNavLinks($_url)}
	<table class="sortable">
		<thead>
			<tr>
				<th>ID#</th>
				<th>Date</th>
				<th>Type</th>
				<th>Brand</th>
				<th>Model</th>
				<th>Manufacturer SN</th>
				<th>TLD SN</th>
				<th>State</th>
				<th>Description</th>
				<th>End of Warranty</th>
				<th>Requestor BU</th>
				<th>Requestor Dept</th>
				<th>Disposed?</th>
				<th>Assigned?</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$pagination->getData() item=row}
			<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
				<td>
			    	<a href="{$smarty.server.SCRIPT_NAME}?m[0]=inventory&m[1]=view&id={$row.id}">{$row.id|default:"&nbsp;"}</a>
				</td>
				<td title="Date">
			    	{$row.dt|default:"&nbsp;"}
				</td>
				<td title="Type">
					{$row.type_name|default:"&nbsp;"}
				</td>
				<td title="Brand">
					{$row.brand_name|default:"&nbsp;"}
				</td>
				<td title="Model">
					{$row.model|default:"&nbsp;"}
				</td>
				<td title="SN">
					{$row.manufacturer_sn|default:"&nbsp;"}
				</td>
				<td title="SN">
					{$row.tld_sn|default:"&nbsp;"}
				</td>
				<td title="State">
					{$row.state|default:"&nbsp;"}
				</td>
				<td title="Description">
					{$row.description|default:"&nbsp;"}
				</td>
				<td title="Warranty">
					{$row.dt_warranty_end|default:"&nbsp;"}
				</td>
				<td title="Requestor">
					{$row.buyer_location|default:"&nbsp;"}
				</td>
				<td title="Requestor Dept">
					{$row.buyer_dpt|default:"&nbsp;"}
				</td>
				<td title="Disposed?">
					{$row.hidden_status|default:"&nbsp;"}
				</td>
				<td title="Assigned?">
					{$row.assigned|default:"&nbsp;"}
				</td>
				{/foreach}
		</tbody>
	</table>
</div>