{* Current dataset *}
{if isset($smarty.session.sess.equipment.list) && count($smarty.session.sess.equipment.list) > 1}
	{assign var=current value=0}
	{foreach key=key item=item from=$smarty.session.sess.equipment.list}
		{if $equipment.id==$item.id}
			{assign var=current value=$key}
		{/if}
	{/foreach}
	{if $current > 0}
		{assign var=previous value=$current-1}
		<a href="{$smarty.server.SCRIPT_NAME}?m[0]=equipment&m[1]=view&m[2]=&id={$smarty.session.sess.equipment.list.$previous.id}">
		Prev
		</a>
	{else}
		Prev
	{/if}
	&nbsp;{$current+1}/{$key+1}&nbsp;
	{if $current < count($smarty.session.sess.equipment.list)-1}
		{assign var=next value=$current+1}
		<a href="{$smarty.server.SCRIPT_NAME}?m[0]=equipment&m[1]=view&m[2]=&id={$smarty.session.sess.equipment.list.$next.id}">
		Next
		</a>
	{else}
		Next
	{/if}
	<br>
{/if}

<hr>

<table border="0" width="100%" cellspadding="2" cellspacing="4">
  <tr>
    <td align="center" bgcolor="#C5C5C5"><b>ER#{$equipment.id}<br>SN:{$equipment.sn}</b></td>
    <td align="center" bgcolor="#C5C5C5"><b>{$equipment.man_location}</b><br>{$equipment.model}</td>
    <td align="center" bgcolor="#C5C5C5"><b>BUYER:</b> {$equipment.buyer_customer_display}<br><b>END USER:</b> {$equipment.user_customer_display}{if $equipment.maintainer_customer_display}<br><b>MAINTAINER:</b> {$equipment.maintainer_customer_display}{/if}</td>
	<td align="center" bgcolor="#C5C5C5"><b>GT date:</b> {$equipment.dgt_act}<br><b>Ship date:</b> {$equipment.date_shipped}</td>
	<td align="center" bgcolor="#C5C5C5"><b>Combination:</b><br>
	{if $equipment.parent_id != 0}
	  {if $equipment.comb_mod eq "COMBINED"}
		SECONDARY
	  {else}
	    {$equipment.comb_mod}
	  {/if}
	{elseif $equipment.nbCombined > 0 || $equipment.nbPreAssembly > 0}
		PRIMARY
	{else}
		None
	{/if}
	</td>
	<td align="center" bgcolor="{if $equipment.state eq "ACTIVE"}#6CC071{else}#FF5252{/if}"><b>State:</b><br>{$equipment.state}</td>
  </tr>
</table>

<br>
