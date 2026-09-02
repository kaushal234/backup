{if count($cart.lines)==0}
	<h2>No items in cart...</h2>
{else}
	<form method="post" action="{$smarty.server.SCRIPT_NAME}">
	<input type="hidden" name="m[0]" value="cart">
	<input type="hidden" name="m[1]" value="update">
	<table border="1" cellspacing="0" cellpadding="1" class="smalltext" bordercolor="#FFFFFF" width="800">
	<tr bgcolor="#D0D0D0">
		<th>Item</th>
		<th>PN</td>
		<th>Description</td>
		<th>Qty</th>
	</tr>
	<!-- Cart Lines -->
	{assign var=i value=1}
	{foreach name=outer key=key item=line from=$cart.lines}
	<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
		<td>{$i}
		{assign var=i value=$i+1}
		</td>
		<td>{$line.item.ITEM}</td>
		<td>{$line.item.DESCRIPTION}</td>
		<td><input type="text" name="qty[{$key}]" value="{$line.qty}" size="3"></td>
	</tr>
	{/foreach}
	<!-- Cart Lines -->
	<tr>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>
		<input type="submit" name="submit" value="Save changes">
	</td>
	</tr>
	</table>
	</form>
{/if}