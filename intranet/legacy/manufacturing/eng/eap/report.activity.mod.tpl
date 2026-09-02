<div class="mod-block" style="border-left-color:#{cycle values="999,c99,cc9,c9c,ccc,9cc,99c,c9c,9c9"};">
	{if $mod.module == 'EAP'}
	<h3><a href="/en/private/manufacturing/eng/dev.php?m[0]=eap&m[1]=view&id={$mod.id}">{$mod.module} #{$mod.id}</a></h3>
	{elseif $mod.module == 'BP'}
	<h3><a href="/en/private/calendar/calendar.php?m[0]=bp&m[1]=view&id={$mod.id}">{$mod.module} #{$mod.id}</a></h3>
	{elseif $mod.module == 'TASK'}
	<h3><a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id={$mod.id}">{$mod.module} #{$mod.id}</a></h3>
	{elseif $mod.module == 'GWF'}
	<h3><a href="/en/private/calendar/calendar.php?m[0]=gwf&m[1]=view&m[2]=&id={$mod.id}">{$mod.module} #{$mod.id}</a></h3>
	{else}
	<h3>{$mod.module} #{$mod.id}</h3>
	{/if}
	{if $mod.description}
	<p>
		<strong>Description:</strong> {$mod.description|escape}
	</p>
	{/if}
	{if $mod.status}
	<p>
		<strong>Current Status:</strong> {$mod.status}
	</p>
	{/if}
	{if !empty($mod.activity)}
	<div>
		<strong>Recent Activity:</strong>
		{foreach from=$mod.activity item=activity}
		<div class="mod-activity"><strong>{$activity.activity|capitalize}</strong> on {$activity.dt}</div>
			{if $activity.info}
			<div class="mod-comment">
				{$activity.info|escape}
			</div>
			{/if}
		{/foreach}
	</div>
	{/if}
	{if !empty($mod.linked)}
	<p>
		<strong>Linked Mods:</strong>
		<div>%%LINKED_MODS%%</div>
	</p>
	{/if}
</div>
