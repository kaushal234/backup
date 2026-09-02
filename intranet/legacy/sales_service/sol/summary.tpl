<h3>Summary</h3>
<table>
	<tr><td bgcolor="#3264C8" class="smallwhite">Quantity</td>
	<td bgcolor="#d0d0d0">{$header.qty}</td></tr>
	<tr><td bgcolor="#3264C8" class="smallwhite">Net Selling/Unit</td>
		<td bgcolor="#d0d0d0"><b>{$dcur}</b>&nbsp;{$intTotals.pris_tot}</td></tr>
	<tr><td bgcolor="#3264C8" class="smallwhite">
		Customer discount (Unit Net Selling Price versus Ex-works Price List) %</td><td bgcolor="#eeeeee">{if $intTotals.pris_tot_in_dcur<$intTotals.prip_tot_in_dcur} {$intTotals.prip_tot_in_dcur-$intTotals.pris_tot_in_dcur}{else}No discount{/if}</td></tr>
	<tr><td bgcolor="#3264C8" class="smallwhite">
		&nbsp;</td>
		<td>&nbsp;</td></tr>
	<tr><td bgcolor="#3264C8" class="smallwhite">Published TP/Unit</td>
		<td bgcolor="#eeeeee"><b>{$dcur}</b>&nbsp;{$intTotals.mrsp_tot_in_dcur}</td></tr>
	<tr><td bgcolor="#3264C8" class="smallwhite">&nbsp;</td>
	<td>&nbsp;</td></tr>
	<tr><td bgcolor="#3264C8" class="smallwhite">
		Negotiated TP/Unit</td><td bgcolor="#eeeeee"><b>{$dcur}</b>&nbsp;{$intTotals.pric_tot_in_dcur}</td></tr>
	<tr><td bgcolor="#3264C8" class="smallwhite">
		Factory discount (negotiated TP versus Published TP) %</td><td><b>{$dcur}</b>&nbsp;{if $intTotals.pric_tot_in_dcur<$intTotals.mrsp_tot_in_dcur} {$intTotals.mrsp_tot_in_dcur-$intTotals.pric_tot_in_dcur}&nbsp;({$intTotals.mrsp_tot_in_dcur*100/$intTotals.mrsp_tot_in_dcur-$intTotals.pric_tot_in_dcur*100/$intTotals.mrsp_tot_in_dcur|string_format:"%.2f"}%){else}No discount{/if}</td>
		</tr>
	<tr><td bgcolor="#3264C8" class="smallwhite">&nbsp;</td><td>&nbsp;</td></tr>
	<tr><td bgcolor="#3264C8" class="smallwhite">Margin, Per Unit</td>
		<td bgcolor="#eeeeee">
		{assign var='unit_marg' value=$intTotals.pris_tot_in_dcur+$extTotals.pris_tot_in_dcur-$intTotals.pric_tot_in_dcur-$extTotals.pric_tot_in_dcur}
		{assign var='unit_cost' value=$intTotals.pric_tot_in_dcur+$extTotals.pric_tot_in_dcur}
		<b>{$dcur}</b>&nbsp;{$unit_marg}
		({$unit_marg*100/$unit_cost|string_format:"%.2f"}%)
		</td></tr>
	<tr><td bgcolor="#3264C8" class="smallwhite">Margin, Total</td>
	<td bgcolor="#eeeeee"><b>{$dcur}</b>&nbsp;{$unit_marg*$header.qty}</td></tr>
</table>