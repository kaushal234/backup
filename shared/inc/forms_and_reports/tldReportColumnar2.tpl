{if count($data)>0}
	<h3>{$options.title}</h3>
	<table cellpadding="3">
	{*PRINT THE X AXIS TITLES*}
	<tr>
	{if $options.showItemNumbers}
	<th>#</th>
	{/if}
	{if is_array($options.xItems)}
		{foreach name=titles key=key item=xItem from=$options.xItems}
		<th>{$xItem|default:"&nbsp;"}</th>
		{/foreach}
	{else}
		{foreach name=titles key=key item=xItem from=$data[0]}
		<th>{$key|default:"&nbsp;"}</th>
		{/foreach}
	{/if}
	</tr>
	
	{foreach name=data item=line from=$data}
	<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
		{if $options.showItemNumbers}
		<td>{$smarty.foreach.data.iteration}</td>
		{/if}
		{if is_array($options.xItems)}
			{foreach key=key item=xItem from=$options.xItems}
			<td>
			{if $options.links.$key<>""}
				<a href="{$options.links.$key}{$line.$key}">{$line.$key|default:"&nbsp;"}</a>
			{else}
				{$line.$key|default:"&nbsp;"}				
			{/if}
			</td>
			{/foreach}
		{else}
			{foreach name=columns key=key item=xItem from=$line}
			<td>
			{if $options.links.$key<>""}
				<a href="{$options.links.$key.url}{$line.$key}">{$options.xItem|default:"&nbsp;"}</a>
			{else}
				{$options.xItem|default:"&nbsp;"}
			{/if}
			</td>
			{/foreach}	
		{/if}
	</tr>
	{/foreach}
	</table>
{/if}