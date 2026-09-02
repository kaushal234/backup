<table width="100%"><tr>
	<td>
	<p>
	<b>Brand: </b>{$header.brand}<br>
	<b>Model: </b>{$header.model}<br>
	<b>Date: </b>{$header.date}<br>
	</p>
	</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td>
	{if $header.description<>""}
	<h4>Description: </h4>
	<p>{$header.description|nl2br}</p>
	{else}
		&nbsp;
	{/if}
	</td>
	<td>
	{if $header.features<>""}
	<h4>Notes: </h4>
	<p>{$header.features|nl2br}</p>
	{else}
		&nbsp;
	{/if}
	</td>
</tr>
</table>