{* Previous/Next links ------------> *}
{if isset($smarty.session.sess.calendar) && count($smarty.session.sess.calendar.tasks) > 1}
    {foreach key=key item=item from=$smarty.session.sess.calendar.tasks}
        {if $task.id==$item.id}
            {assign var=current value=$key}
        {/if}
    {/foreach}
    <p style="margin:5px;">
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
    </p>
{/if}

{* Seq view ---------------------------------------------------> *}
{if $task.status=="CLOSED"}
    {assign var="flagColor" value="#cccccc"}
{elseif $task.status=="OPEN"}
    {assign var="flagColor" value="#00FF00"}
    {if $task.days_late > 0}
        {assign var="flagColor" value="#FF0000"}
    {/if}
{/if}

<table width="100%">
    <tr>
        <td width="40%">
            <table width="100%" cellpadding="2">
                <tr>
                    <td bgcolor="{$flagColor}" width="10%">&nbsp;</td>
                    <td width="90%">
                        <h3 style="margin:2px;">SEQUENCE #{$task.id}</h3>
                        <p>
                            <b>Assignor:</b>
                            <a href="/en/private/directory/index.php?m[0]=people&m[1]=view&id={$task.assignor.id}">
                                {$task.assignor.lastname|upper}, {$task.assignor.firstname}
                            </a><br>
                            <b>Assignee:</b>
                            <a href="/en/private/directory/index.php?m[0]=people&m[1]=view&id={$task.assignee.id}">
                                {$task.assignee.lastname|upper}, {$task.assignee.firstname}
                            </a>
                        </p>

                        <p>
                            <b>Status:</b> {$task.status}
                            {if $task.module<>"SEQ"}
                                <br><b>Module:</b>
                                <a href="{if $link}{$link}{else}{$mod_links[$task.module]}{$task.parent_id}{/if}" target="_blank">
                                    {$task.module}#{$task.parent_id}
                                </a>
                            {/if}
                            {if $apiUserId}
                                <br><b>New Intranet Id:</b>
                                <a href="{$mod_links[$task.module]}{$task.parent_id}" target="_blank">
                                    #{$apiUserId}
                                </a>
                            {/if}
                        </p>

                        <p>
                            <b>Open Date:</b> {$task.date}<br>
                            <b>Due Date:</b> {$task.due_date}
                            {if $task.status=='CLOSED'}
                                <br><b>Closed Date:</b> {$task.dt_closed} {$task.hours}
                            {/if}
                        </p>

                        <p>
                            <b>Importance Factor:</b> {$task.ifactor}
                            {if $task.bu_id}<br><b>Location:</b> {$task.bu_fullname}{/if}
                        </p>
                    </td>
                </tr>

                {* Seq Template Description ------------> *}
                <tr>
                    <td colspan="2">
                        <div style="background: #dedede; padding: 2px;">
                            <b>Template:</b> {$task.tpl_fullname}<br>
                            <b>Current Step:</b> {$task.cur_step}

                            {if $current_action<>""}
                                <br>
                                <b>Current & Next Action:</b><br>
                                <span id="action-text" style="
                                    display:inline-block;
                                    max-height:150px;
                                    overflow:hidden;
                                    vertical-align:top;
                                    transition:max-height 0.3s ease;
                                ">
                                    {$current_action|renderHtmlOrNl2br}
                                </span>
                                <a href="javascript:void(0);" id="toggle-action" style="margin-left:5px; color:#0073aa; text-decoration:underline;">
                                    Read more
                                </a>

                            {literal}
                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        var text = document.getElementById('action-text');
                                        var toggle = document.getElementById('toggle-action');
                                        var expanded = false;

                                        if (text.scrollHeight <= 150) {
                                            toggle.style.display = 'none';
                                        }

                                        toggle.addEventListener('click', function() {
                                            expanded = !expanded;
                                            if (expanded) {
                                                text.style.maxHeight = 'none';
                                                toggle.textContent = 'Show less';
                                            } else {
                                                text.style.maxHeight = '150px';
                                                toggle.textContent = 'Read more';
                                            }
                                        });
                                    });
                                </script>
                            {/literal}

                            {/if}

                            {* Seq Misc ------------> *}
                            {if ($task.tplno=="78" || $task.tplno=="22") && $task.params.pn!=""}
                                <br><b>Part#</b> <a href={$partDashboardUrl}>{$task.params.pn}</a>
                            {/if}
                            {if $task.tplno =="24" || $task.tplno =="25"}
                                <br><b>Invoice#</b> {$task.parent_id}
                            {/if}
                            {if $task.tplno =="27" || $task.tplno =="28" || $task.tplno =="29"}
                                <br><b>Item#</b> {$task.parent_id}
                            {/if}
                        </div>

                        {* Seq Description ------------> *}
                        {$task.task|taskDesc|renderHtmlOrNl2br}
                        <br><br><br>
                        {$reportByItem->fetch()}
                        <br>
                        {$reportByParentId->fetch()}
                    </td>
                </tr>
            </table>
        </td>

        {* Seq COMMENTS ------------> *}
        <td width="60%">
            <h3 style="margin:2px;">COMMENTS/NOTES</h3>
            <table width="100%">
                {foreach name=comments item=comment from=$task.comments}
                    <tr>
                        <td width="15" style="display: table-cell; vertical-align: middle;">
                            {if $comment.step>0}<h2>{$comment.step}</h2>{/if}
                        </td>
                        <td>
                            <hr>
                            {if $comment.filename}
                                <a href="/en/private/uploads/tasks_comments/{$comment.filename}"
                                   target="_blank"
                                   title="Click to view attachment: {$comment.filename}">
                                    <img src="/shared/bluesphere/32x32/mimetypes/document.png" align="right">
                                </a>
                            {/if}
                            <b>Date:&nbsp;</b>{$comment.date}&nbsp;&nbsp;
                            <b>Poster:&nbsp;</b>{$comment.poster_fullname}&nbsp;&nbsp;
                            {if $comment.status<>""}
                                <b>Status:&nbsp;</b>{$comment.status}&nbsp;&nbsp;
                            {/if}
                            <br><br>
                            {$comment.comment|renderHtmlOrNl2br}
                        </td>
                    </tr>
                {/foreach}
            </table>
        </td>
    </tr>
</table>
