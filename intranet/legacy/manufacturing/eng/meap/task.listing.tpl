{literal}
    <style type="text/css">
        .tip{
            font_size: 12px;
            margin: 0;
            padding: 0;
            color: gray;
        }
        .columnar thead tr th {
            background: #2971a8;
            color: white;
            cursor: pointer;
        }
        .columnar tfoot tr td.value {
            background: #2971a8;
            color: white;
            font-weight: bold;
            border-top: 2px solid #333;
            padding-top: 8px;
            font-size: larger;
        }
    </style>
{/literal}

<h3>{$title|default:"MEAP Task - Edit mode"}</h3>
<form name="meapTaskRescheduler" method="post">
    <input type="hidden" name="m[0]" value="meap" />
    <input type="hidden" name="m[1]" value="view" />
    <input type="hidden" name="m[2]" value="batchTasksReschedule" />
    <input type="hidden" name="id" value="{$id}" />
    <p class="tip"><em>Tips: Click headers to sort.</em></p>
    <table cellpadding="3" class="sortable columnar">
        <thead>
            <tr>
                {foreach name=titles key=key item=item from=$fields}
                    <th>{$item}</th>
                {/foreach}
            </tr>
        </thead>
        <tbody>
            {foreach name=lines key=i item=row from=$rows}
                <tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
                    {foreach name=titles key=key item=item from=$fields}
                        <td>
                            {if $key == "eap_id"}
                                <a href="{$smarty.server.SCRIPT_NAME}?m[0]=eap&m[1]=view&id={$row.$key}">{$row.$key}</a>
                            {elseif $key == "task_id"}
                                <a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id={$row.$key}">{$row.$key}</a>
                            {elseif $key == "due_date"}
                                <input type="text" name="tasks[{$row.task_id}][{$key}]" maxlength="10" size="10" value="{$row.$key}" class="datepicker" />
                            {else}
                                {$row.$key}
                            {/if}
                        </td>
                    {/foreach}
                </tr>
            {/foreach}
        </tbody>
    </table>
    <input type="submit" name="meapTaskRescheduler" value="Submit">
</form>
<br/>
<br/>
<br/>
<br/>
