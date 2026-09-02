{* Current dataset *}
{if isset($smarty.session.sess.wc.list) && count($smarty.session.sess.wc.list) > 1}
	{foreach key=key item=item from=$smarty.session.sess.wc.list}
		{if $wc.id==$item.id}
			{assign var=current value=$key}
		{/if}
	{/foreach}
	{if $current > 0}
		{assign var=previous value=$current-1}
		<a href="{$smarty.server.SCRIPT_NAME}?m[0]=wc&m[1]=view&m[2]=&id={$smarty.session.sess.wc.list.$previous.id}">
		Prev
		</a>
	{else}
		Prev
	{/if}
	&nbsp;{$current+1}/{$key+1}&nbsp;
	{if $current < count($smarty.session.sess.wc.list)-1}
		{assign var=next value=$current+1}
		<a href="{$smarty.server.SCRIPT_NAME}?m[0]=wc&m[1]=view&m[2]=&id={$smarty.session.sess.wc.list.$next.id}">
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
    <td align="center" bgcolor="#C5C5C5"><b>WC#</b>{$wc.id}<br/>{$wc.customer_name}</td>
    <td align="center" bgcolor="#2971A8" style="color:white"><b>WC Status</b><br/>{$wc.warranty_status}</td>
    <td align="center" bgcolor="#2971A8" style="color:white"><b>ER Model:</b> {$wc.model}<br><b>SN:</b> {$wc.serial_number}</td>
    <td align="center" bgcolor="#FFCF77"><b>Critical PN failing</b><br/>{$wc.part_failing}</td>
    <td align="center" bgcolor="#C5C5C5"><b>Problem Description</b><br/>{$wc.problem_desc|stripcslashes|nl2br}</td>
  </tr>
</table>
<hr>
