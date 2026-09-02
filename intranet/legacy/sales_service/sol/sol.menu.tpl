{if isset($smarty.session.sess.sol.list) && count($smarty.session.sess.sol.list) > 1}
	{foreach key=key item=item from=$smarty.session.sess.sol.list}
		{if $sol.id==$item.id}
			{assign var=current value=$key}
		{/if}
	{/foreach}
	{if $current > 0}
		{assign var=previous value=$current-1}
		<a href="{$smarty.server.SCRIPT_NAME}?m[0]=sol&m[1]=view&m[2]=&id={$smarty.session.sess.sol.list.$previous.id}">
		Prev
		</a>
	{else}
		Prev
	{/if}
	&nbsp;{$current+1}/{$key+1}&nbsp;
	{if $current < count($smarty.session.sess.sol.list)-1}
		{assign var=next value=$current+1}
		<a href="{$smarty.server.SCRIPT_NAME}?m[0]=sol&m[1]=view&m[2]=&id={$smarty.session.sess.sol.list.$next.id}">
		Next
		</a>
	{else}
		Next
	{/if}
	<br>
<hr>
{/if}