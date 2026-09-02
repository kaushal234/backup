<h3>Task#{$task.id}</h3>

{if isset($smarty.session.sess.calendar) && count($smarty.session.sess.calendar.tasks) > 1}
	{foreach key=key item=item from=$smarty.session.sess.calendar.tasks}
		{if $task.id==$item.id}
			{assign var=current value=$key}
		{/if}
	{/foreach}
	{if $current > 0}
		{assign var=previous value=$current-1}
		<a href="{$smarty.server.SCRIPT_NAME}?m[0]=tasks&m[1]=task&m[2]=view&id={$smarty.session.sess.calendar.tasks.$previous.id}">
		Prev
		</a>
	{else}
		Prev
	{/if}
	&nbsp;{$current+1}/{$key+1}&nbsp;
	{if $current < count($smarty.session.sess.calendar.tasks)-1}
		{assign var=next value=$current+1}
		<a href="{$smarty.server.SCRIPT_NAME}?m[0]=tasks&m[1]=task&m[2]=view&id={$smarty.session.sess.calendar.tasks.$next.id}">
		Next
		</a>
	{else}
		Next
	{/if}
	<br>
{/if}

{if $task<>""}
	<table width="100%">
		<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
		{if $task.status=="CLOSED"}
			<td colspan="2">
		{else}
            {assign var="flagColour" value="#00FF00"}
			{if $task.status=="PAUSE"}
				{assign var="flagColour" value="#FFD60F"}
			{elseif $task.days_late > 0}
                {assign var="flagColour" value="#FF0000"}
			{/if}
			<td bgcolor="{$flagColour}" align="right" width="20">&nbsp;
			</td>
			<td>
		{/if}
		{if $currentUser.id==$task.assignee && $task.status=="OPEN"}
			<span class="alert"><b>THIS ACTION REQUIRES YOUR ATTENTION</b></span><br>
		{/if}
		<a name="#task{$task.id}"></a>
		<b>Status:</b>&nbsp;{$task.status}
		{if $task.status=='CLOSED'}
			@{$task.dt_closed}&nbsp;&nbsp;
		<b>Hours:</b>&nbsp;{$task.hours}
		{/if}&nbsp;&nbsp;
		<b>Task ID#:</b>&nbsp;{$task.id}&nbsp;&nbsp;
		{if $task.module<>"USER" && $task.module<>"SEQ"}
		<b>Module:</b>&nbsp;
			<a href="{$link}" title="This TASK is linked to a document in another module, click here to view...">
			{$task.module}#{$task.parent_id}
			</a>
		{/if}
		<br>

		<b>Date:</b>&nbsp;{$task.date}
		<br><b>Assignor:</b>&nbsp;&nbsp;
		<a href="/en/private/directory/index.php?m[0]=people&m[1]=view&id={$task.assignor.id}">
			<span style="text-transform: uppercase">{$task.assignor.lastname}</span>, {$task.assignor.firstname}
		</a> <span style='color:grey;'>(#{$task.assignor.id})</span> &nbsp;&nbsp;<b>BU:</b>&nbsp;{$task.buname}
		&nbsp;&nbsp;
		<b>Due Date:</b>&nbsp;{$task.due_date}
		<br>
		<b>Assignee:</b>&nbsp;&nbsp;
		<a href="/en/private/directory/index.php?m[0]=people&m[1]=view&id={$task.assignee.id}">
			<span style="text-transform: uppercase">{$task.assignee.lastname}</span>, {$task.assignee.firstname}</a> <span style='color:grey;'>(#{$task.assignee.id})</span> &nbsp;&nbsp;
		<b>Est Completion Date:</b>&nbsp;{$task.dcomp}&nbsp;&nbsp;
		<b>Escalation Trigger(Days):</b>&nbsp;{$task.escalation_trigger}&nbsp;&nbsp;
		<b>Category:</b>&nbsp;{$task.category}&nbsp;&nbsp;
		{if $task.cat<>""}
			<b>Category:</b>&nbsp;{$task.cat}&nbsp;&nbsp;
		{/if}
		{if $task.module=="EAP"}
			<b>Est Time of Completion (Hours):</b>&nbsp;{$task.hours}
		{/if}
		{if $task.module=="MEAP"}
			<b>Est Time of Completion (Hours):</b>&nbsp;{$task.hours}
		{/if}
		<b>Ifactor:</b>&nbsp;{$task.ifactor}&nbsp;&nbsp;
		{if $module}
			<br>
			<b>Ticket Module:</b> {$module.module}&nbsp;
			<b>MIS Owner:</b> <a href="/en/private/directory/index.php?m[0]=people&m[1]=view&id={$module.uid}">{$module.uid_fullname}</a>&nbsp;
			<b>MOO:</b> <a href="/en/private/directory/index.php?m[0]=people&m[1]=view&id={$module.oid}">{$module.oid_fullname}</a>&nbsp;
			<b><abbr title="Module Key User">MKU:</abbr></b> <a href="/en/private/directory/index.php?m[0]=people&m[1]=view&id={$module.key_user_id}">{$module.key_user_fullname}</a>&nbsp;
			<b>DMS Procedure:</b> {if $module.user_guide_id !== '0'}<a href="/en/private/mis/mis.php?m[0]=help&m[1]=dms&id={$module.user_guide_id}">{$module.user_guide_id}</a>{else}<span class="alert">missing</span>{/if}&nbsp;
			<b>DMS Help:</b> {if $module.help_page_id !== '0'}<a href="/en/private/mis/mis.php?m[0]=help&m[1]=dms&id={$module.help_page_id}">{$module.help_page_id}</a>{else}<span class="alert">missing</span>{/if}&nbsp;
			<b>Jira Issue:</b> {if $task.jira_issue !== null}{$task.jira_issue}{else}<span class="alert">missing</span>{/if}&nbsp;
			<br>
		{/if}
		<hr>
		{$task.task|stripslashes|renderHtmlOrNl2br}
		<br>
		</td>
		</tr>
		{* Print comments for task, if any *}
		{if count($task.comments)}
			<tr><td colspan="3"><b>COMMENTS/NOTES</b><br>
			<table width="100%">
			{foreach name=comments item=comment from=$task.comments}
			<tr>
				<td width="20">
				{if $comment.step>0}
					<h2>{$comment.step}</h2>
				{/if}
				</td>
				<td {if strpos($comment.comment, 'automatically rescheduled') || strpos($comment.comment, 'AUTO ESCALATED')}style="background: orange" {/if}>
				{if $comment.filename}
<a href="/en/private/uploads/tasks_comments/{$comment.filename}" target="_blank" title="Click to view attachment: {$comment.filename}"><img src="/shared/bluesphere/32x32/mimetypes/document.png" align="right"></a>
				{/if}
				<b>Date:&nbsp;</b>{$comment.date}&nbsp;&nbsp;
				<b>Poster:&nbsp;</b>{$comment.poster_fullname}&nbsp;&nbsp;
				{if $comment.status<>""}
					<b>Status:&nbsp;</b>{$comment.status}&nbsp;&nbsp;
				{/if}
				<br>
				{$comment.comment|stripslashes|renderHtmlOrNl2br}
				</td>
				</tr>
				<tr>
				<td colspan="2"><hr></td>
			</tr>
			{/foreach}
			</table>

			</td></tr>
			<tr><td colspan="2"><br></td></tr>
		{/if}
	</table>
{/if}
{literal}
<script>
	$(document).ready(function() {
		$("p").css({"margin":0});
	});
</script>
{/literal}
