{literal}
<style type="text/css">
.provisional-table thead tr th {
	background: #2971a8;
	color: white;
}
.sortable thead tr th {
	cursor: pointer;
}
</style>
{/literal}
<h3>MEAP#{$meap->getID()} Provisional Dates{if $edit}: Edit Mode{/if}</h3>
{if $edit}
<form action="{$smarty.server.SCRIPT_NAME}?m[0]=meap&m[1]=view&m[2]=provisional&m[3]=edit&id={$meap->getID()}" method="post">
{/if}
<table class="provisional-table{if !$edit} sortable{/if}" {if $width<>'100%'}width="100%"{/if} cellpadding="3">
<thead>
	<tr>
		<th>MILESTONE</th>
		<th>DESCRIPTION</th>
		<th>ORIGINAL TARGET</th>
		<th>MANAGEMENT TARGET</th>
		<th>CURRENT PROJECT TARGET</th>
		<th>ACTUAL CLOSURE DATE</th>
	</tr>
</thead>
<tbody>
{foreach item=pcd from=$meap->PCD()}
    <tr bgcolor="{cycle values='#eeeeee,#d0d0d0'}">
        <td>{$pcd.type|escape:'html'}</td>
        <td>{$pcd.description|escape:'html'}</td>
        <td>
        {if $edit}
        	<input name="original[{$pcd.id}]" value="{$pcd.original_target}" size="10" class="datepicker" {if (empty($pcd.original_target) AND !$user->isInGroup(array('role_EM','role_ES','gg_EXCOM'))) OR (!empty($pcd.original_target) AND !$user->isInGroup(array('gg_EXCOM')) AND !in_array($meap->getStatus(), $status))}disabled="disabled" {/if}/>
        {else}
        	{$pcd.original_target}
        {/if}
        </td>
        <td>
        {if $edit}
        	<input name="management[{$pcd.id}]" value="{$pcd.management_target}" size="10" class="datepicker" {if !$user->isInGroup(array('gg_EXCOM'))}disabled="disabled" {/if}/>
        {else}
        	{$pcd.management_target}
        {/if}
        </td>
        <td>
        {if $edit}
        	<input name="current[{$pcd.id}]" value="{$pcd.current_target}" size="10" class="datepicker" {if !$user->isInGroup(array('role_EM','role_ES','role_ENG'))}disabled="disabled" {/if}/>
        {else}
        	{$pcd.current_target}
        {/if}
        </td>
        <td>
        {if $pcd.type<>'MEAP PHASE' AND $edit}
        	<input name="actual[{$pcd.id}]" value="{$pcd.actual_date}" size="10" class="datepicker" {if !$user->isInGroup(array('role_EM','role_ES'))}disabled="disabled" {/if}/>
        {else}
        	{$pcd.actual_date}
        {/if}
        </td>
    </tr>
{/foreach}
</tbody>
</table>
{if $edit}
<strong>Edit Comments:</strong><br/>
<textarea name="comment" cols="40" rows="5">{$comment|escape:'html'}</textarea><br/>
<input type="submit" name="submit" value="Update" />
</form>
{/if}
