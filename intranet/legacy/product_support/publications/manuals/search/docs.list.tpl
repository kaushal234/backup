{if count($docs)==0}
	<h2>No results found in documents...</h2>
{else}
	<h2>{$heading}</h2>
	<table border="1" cellspacing="0" cellpadding="1" class="smalltext" bordercolor="#FFFFFF" width="100%">
	<tr bgcolor="#D0D0D0">
		<th>Item</th>
		<th>Manual#</th>
		<th>Brand</th>
		<th>Model</th>
		<th>Document#</th>
		<th>Category</th>
		<th>Description</th>
	</tr>
	{foreach name=outer key=key item=doc from=$docs}
	<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
		<td>{$key+1}</td>
		<td><a href="/en/private/product_support/index.ps.php?m[0]=publications&m[1]=manuals&m[2]=view&id={$doc.man_id}&lang=en">
		{$doc.man_id}</a></td>
		<td>{$doc.brand}</td>
		<td>{$doc.model}</td>
		<td><a href="/en/private/product_support/index.ps.php?m[0]=publications&m[1]=documents&m[2]=view&id={$doc.id}&lang=en">
		{$doc.doc_id}</a></td>
		<td>{$doc.category}</td>
		<td>{$doc.endescription}</td>
	</tr>
	{/foreach}
	</table>
{/if}