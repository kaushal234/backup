{if count($cart.lines)==0}
	<h2>No items in cart...</h2>
{else}
    <h3>Cart Contents</h3>
	<table border="1" cellspacing="0" cellpadding="1" class="smalltext" bordercolor="#FFFFFF" width="800">
	<tr bgcolor="#D0D0D0">
		<th>Item</th>
		<th>PN</th>
		<th>EDM Description</th>
		<th>ALT Description</th>
		<th>ITM Description</th>
		<th>Qty</th>
		<th>TOT Actual Stock</th>
		<th>P</th>
		<th>M</th>
		<th>O</th>
		<th>C</th>
		<th>UM</th>
		<th>Signal Code</th>
		<th>CUR</th>
		<th>MIP</th>
		<th>TOT STK</th>
{foreach name=whs key=key item=wh from=$whs[$DEFAULT_ERP]}
        <th>{$wh}</th>
{/foreach}
{*		<th>Photo</th> *}
	</tr>
	<!-- Cart Lines -->
	{assign var=i value=1}
	{foreach name=outer key=key item=line from=$cart.lines}
	<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
		<td>{$i}
		{assign var=i value=$i+1}
		</td>
		<td><a href="{$smarty.server.SCRIPT_NAME}?m[0]=inv&m[1]=view&id={$key}">{$key}</a></td>
		<td>{$line.item.DESCRIPTION}</td>
		<td>{$line.item.OTHER_DESCRIPTION}</td>
		<td>{$line.item.ALT_DESCRIPTION}</td>
		<td>{$line.qty}</td>
		<td>{$line.item.stoc}</td>
		<td>{if strpos($line.item.pmoc,"P")!==false}P{else}&nbsp;{/if}</td>
		<td>{if strpos($line.item.pmoc,"M")!==false}M{else}&nbsp;{/if}</td>
		<td>{if strpos($line.item.pmoc,"O")!==false}O{else}&nbsp;{/if}</td>
		<td>{if strpos($line.item.pmoc,"C")!==false}C{else}&nbsp;{/if}</td>
		<td>{$line.item.UM}</td>
		<td>{$line.item.t_csig}</td>
		<td>{$extrarows.$DEFAULT_ERP.$key.cur}</td>
		<td>{$extrarows.$DEFAULT_ERP.$key.mip}</td>
		<td>{$extrarows.$DEFAULT_ERP.$key.reop}
{if $line.qty > $extrarows.$DEFAULT_ERP.$key.reop}
    {if $extrarows.$DEFAULT_ERP.$key.reop ==0}<img src="/shared/bluesphere/22x22/actions/stop_hand.png" title="WARNING: Not enough stock">
    {else}<img src="/shared/bluesphere/22x22/actions/stop_hand_yellow.png" title="WARNING: Not enough stock">
    {/if}  
{/if}
</td>
{foreach name=whs item=wh from=$whs.$DEFAULT_ERP}
		<td>{$extrarows.$DEFAULT_ERP.$key.inv.$wh}</td>
{/foreach}
		<td>
{*        <td>
            <a href="{$smarty.server.SCRIPT_NAME}?m[0]=inv&m[1]=view&id={$key}">
            <img src="/en/private/manufacturing/eng/dev.php?m[0]=getfile&m[1]=photo&erp={$DEFAULT_ERP}&new_width=128&item={$key}" title="{$key}">
            </a>
        </td>
*}	</tr>
	{/foreach}
	<!-- Cart Lines -->
	</table>
	</form>
{/if}