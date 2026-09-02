<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
{if $smarty.session.sess.eap.list !== null && count($smarty.session.sess.eap.list) > 1}
	{foreach key=key item=item from=$smarty.session.sess.eap.list}
		{if $eap.id==$item.id}
			{assign var=current value=$key}
		{/if}
	{/foreach}
	{if $current > 0}
		{assign var=previous value=$current-1}
		<a href="{$smarty.server.SCRIPT_NAME}?m[0]=eap&m[1]=view&m[2]=&id={$smarty.session.sess.eap.list.$previous.id}">
		Prev
		</a>
	{else}
		Prev
	{/if}
	&nbsp;{$current+1}/{$key+1}&nbsp;
	{if $current < count($smarty.session.sess.eap.list)-1}
		{assign var=next value=$current+1}
		<a href="{$smarty.server.SCRIPT_NAME}?m[0]=eap&m[1]=view&m[2]=&id={$smarty.session.sess.eap.list.$next.id}">
		Next
		</a>
	{else}
		Next
	{/if}
	<br>
{/if}
