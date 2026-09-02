{if count($data)==0}
	<h1>No results found...</h1>
{else}
	{if $pageURL<>""}
		{if $offset >= $limit}
			<a href="{$pageURL}offset={$offset-$limit}">
			Previous</a>
		{else}
			Previous
		{/if}
		&nbsp;|&nbsp;
		{$offset}&nbsp;/&nbsp;{$count}
		&nbsp;|&nbsp;
		{if $offset + $limit < $count}
			<a href="{$pageURL}offset={$offset+$limit}">
			Next</a>
		{else}
			Next
		{/if}
	{/if}

	<table border="1" cellspacing="0" cellpadding="1" class="smalltext" bordercolor="#FFFFFF">
	<tr>
	{if $lineURL<>""}
		<th>&nbsp;</th>
	{/if}
	{foreach name=labels key=fieldname item=label from=$fields}
		<th>{$label}</th>
	{/foreach}
	</tr>

	{foreach name=table key=key item=line from=$data}
	<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
		{if $lineURL<>""}
			<td>
			<a href="{$lineURL}{$key}">View</a>
			</td>
		{/if}
		{foreach name=labels key=fieldname item=label from=$fields}
			<td>{$line.$fieldname}</td>
		{/foreach}
	</tr>
	{/foreach}
	</table>
{/if}