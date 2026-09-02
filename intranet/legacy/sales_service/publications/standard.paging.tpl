{if $offset >= $limit}
	<a href="{$smarty.server.SCRIPT_NAME}?{$NEXT_STEP}&offset={$offset-$limit}">
	Previous</a>
{else}
	Previous
{/if}
&nbsp;|&nbsp;
{$offset}&nbsp;/&nbsp;{$count}
&nbsp;|&nbsp;
{if $offset + $limit < $count}
	<a href="{$smarty.server.SCRIPT_NAME}?{$NEXT_STEP}&offset={$offset+$limit}">
	Next</a>
{else}
	Next
{/if}
