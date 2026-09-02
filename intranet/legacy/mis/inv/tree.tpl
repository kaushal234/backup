<h3>Browse Inventory</h3>
{if $header<>""}
	<table width="100%">
		<tr bgcolor="#D0D0D0">
			<td rowspan="2" width="50" bgcolor="#FFFFFF">
				{if $icons[$header.type]<>""}
					<img src="{$icons[$header.type]}" alt="{$header.description}">
				{else}
					<img src="/shared/icons/network/32x32/default.jpg" alt="{$header.description}">
				{/if}
			</td>
			<th>ID#<br>
			Parent</th>
			<th>In Date</th>
			<th>Type<br>
			Description</th>
			<th>Serial<br>
			Man. SN</th>
		</tr>
		<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
			<td>{$header.id}<br>
			{$header.parent_id}</td>
			<td>{$header.date}</td>
			<td>{$header.type}<br>
			{$header.description}</td>
			<td>{$header.serial}<br>
			{$header.man_serial}</td>
		</tr>
	</table>
{/if}
{foreach key=key item=item from=$list}
	<table>
		<tr>
		<td width="{$item.level*40}">&nbsp;</td>
			<td>
			<a href="{$smarty.server.SCRIPT_NAME}?m[0]=inv&m[1]=browse&id={$item.id}">
			{if $icons[$item.type]<>""}
				<img src="{$icons[$item.type]}" alt="{$item.type}, {$item.description}">
			{else}
				<img src="/shared/icons/network/32x32/default.jpg" alt="{$item.type}, {$item.description}">
			{/if}
			</a>
			</td><td>
			<b>{$item.serial}</b><br>
			{if $item.man_serial<>""}
				[{$item.man_serial}]
			{/if}
			</td>
			<!--td>
				<img src="/shared/bluesphere/16x16/actions/1rightarrow.png" alt="Go to next level">
			</a>
			</td-->
		</tr>
	</table>
{/foreach}
