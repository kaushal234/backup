<p>
{if $title<>''}
	<b>{$title|upper}</b><br>
{/if}
{if $nolink==""}
	<a href="/en/private/directory/index.php?{$next_step}&n[0]={$person.id}&n[1]={$person.lastname}">
{/if}
{if $person.photo <> ""}
	<img src="/en/private/directory/index.php?m[0]=outPhoto&width=128&id={$person.id}">
{else}
	<img src="/shared/no_photo.jpg" width="128">
{/if}
<br>
<b>{$person.lastname|htmlentities}, {$person.firstname|htmlentities}</b>
{if $person.title<>""},<br>{$person.title|htmlentities}{/if}
{if $person.department<>""},<br>{$person.department|htmlentities}{/if}
{if $person.location<>""},<br>{$person.location|htmlentities}{/if}
{if $nolink==""}
	</a>
{/if}
</p>
