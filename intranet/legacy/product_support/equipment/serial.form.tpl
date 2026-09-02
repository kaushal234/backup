<form name="editSerials" method="post" action="{$nextURL}">
	<table class="sortable" cellpadding="3"">
		<thead>
			<tr>
				<th>Component</th>
				<th>Model</th>
				<th>Serial</th>
				<th>Brand</th>
			</tr>
		</thead>
		<tbody>
			{foreach name=serial item=serial from=$serial}
			<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
				<td>
                    <select name="serials[{$serial.id}][component]" width="210" style="width:210px">
                        {foreach from=$componentList key=key item=name}
                            {if $key eq $serial.component}
                                <option label="{$name}" value="{$name}" selected="selected">{$name}</option>
                            {else}
                                <option label="{$name}" value="{$name}">{$name}</option>
                            {/if}
                        {/foreach}
                        {if !in_array($serial.component, $componentListin_array) }
                            <option label="{$serial.component}" value="{$serial.component}" selected="selected">{$serial.component}</option>
                        {/if}
                    </select>
				</td>
				<td><input type="text" name="serials[{$serial.id}][model]" value="{$serial.model}" size="27" /></td>
				<td><input type="text" name="serials[{$serial.id}][serial]" value="{$serial.serial}" size="20" /></td>
				<td><input type="text" name="serials[{$serial.id}][brand]" value="{$serial.brand}" size="20" /></td>
				</tr>
			{/foreach}
		</tbody>
	</table>
	<br/>
	<input type="submit" value="Update Serials" /> <input type="reset" value="Reset" />
</form>
