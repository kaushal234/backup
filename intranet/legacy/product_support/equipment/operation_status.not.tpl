<table border="0" cellpadding="5" cellspacing="5" width="800">
	<tr>
		<td style="text-align:center;" colspan="3"><h1>Equipment Status Report</h1></td>
	</tr>
	<tr>
		<td style="text-align:center;font-size:larger;background-color:#2971a8;color:white;" width="33.3%"><strong>Status</strong></td>
		<td style="text-align:center;font-size:larger;background-color:#2971a8;color:white;" width="33.3%"><strong>Station</strong></td>
		<td style="text-align:center;font-size:larger;background-color:#2971a8;color:white;" width="33.3%"><strong>Equipment #</strong></td>
	</tr>
	<tr>
		<td style="text-align:center;font-weight:bold;
		{if $info.status=='FMC'}background-color:green;color:white;
		{elseif $info.status=='NMC'}background-color:red;color:white;
		{elseif $info.status=='PMC'}background-color:yellow;
		{else}background-color:#ccc:{/if}">{$info.status|escape:'html'}</td>
		<td style="text-align:center;">{$er_header.location_short|escape:'html'}</td>
		<td style="text-align:center;">{$er_header.cust_asset_num|escape:'html'}</td>
	</tr>
	<tr>
		<td style="text-align:center;font-size:larger;background-color:#2971a8;color:white;"><strong>Date of Report</strong></td>
		<td style="text-align:center;font-size:larger;background-color:#2971a8;color:white;"><strong>Time of Report</strong></td>
		<td style="text-align:center;font-size:larger;background-color:#2971a8;color:white;"><strong>Estimated Hours to Repair</strong></td>
	</tr>
	<tr>
		<td style="text-align:center;">{$smarty.now|date_format:"%m/%d/%Y"}</td>
		<td style="text-align:center;">{$smarty.now|date_format:"%k:%M"} EST</td>
		<td style="text-align:center;">N/A</td>
	</tr>
	<tr>
		<td style="text-align:center;font-size:larger;background-color:#2971a8;color:white;" colspan="3"><strong>Description of Equipment Issue</strong></td>
	</tr>
	<tr>
		<td colspan="3">{$info.log|escape:'html'}</td>
	</tr>
	<tr>
		<td style="text-align:center;font-size:larger;background-color:#2971a8;color:white;" colspan="3"><strong>Provide a detailed description of the limitations of PMC equipment</strong></td>
	</tr>
	<tr>
		<td colspan="3">{$info.limitations_log|escape:'html'}</td>
	</tr>
	<tr>
		<td style="text-align:center;font-size:larger;background-color:#2971a8;color:white;" colspan="3"><strong>Description of Repairs</strong></td>
	</tr>
	<tr>
		<td colspan="3">{$info.repairs_log|escape:'html'}</td>
	</tr>
	<tr>
		<td style="text-align:center;font-size:larger;background-color:#2971a8;color:white;" colspan="3"><strong>Reporting Technician</strong></td>
	</tr>
	<tr>
		<td style="text-align:center;" colspan="3">{$user->getFullname()|escape:'html'}</td>
	</tr>
</table>
