{if $pg_object->itsDisplay eq "less"}
<a href="{$pg_url}page=first"><img src="/shared/bluesphere/16x16/actions/player_start.png" style="vertical-align:middle;" alt="Go to first"></a>
<a href="{$pg_url}page=prev"><img src="/shared/bluesphere/16x16/actions/player_rew.png" style="vertical-align:middle;" alt="Go to previous"></a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$pg_object->getCntString()}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$pg_url}page=next"><img src="/shared/bluesphere/16x16/actions/player_fwd.png" style="vertical-align:middle;" alt="Go to next"></a>
<a href="{$pg_url}page=last"><img src="/shared/bluesphere/16x16/actions/player_end.png" style="vertical-align:middle;" alt="Go to last"></a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	{if $pg_options.show_jumpto}
	<form action="{$pg_url}" method="post" style="display:inline;">jump to page: <input type="text" size="2" name="page" style="vertical-align:middle;text-align:center;" /></form>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	{/if}
{/if}
{if $pg_object->itsDisplay eq "all"}
	{assign var="pg_toggle" value="less"}
{else}
	{assign var="pg_toggle" value="all"}
{/if}
{if $pg_options.show_toggle}
(<a href="{$pg_url}page={$pg_toggle}">show {$pg_toggle}</a>)
{/if}
