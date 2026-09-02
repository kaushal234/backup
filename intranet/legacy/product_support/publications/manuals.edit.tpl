
{if !empty($lines)}
<form method="post" action="/en/private/product_support/index.ps.php">
	<input type="hidden" name="m[0]" value="publications">
	<input type="hidden" name="m[1]" value="manuals">
	<input type="hidden" name="m[2]" value="view">
	<input type="hidden" name="m[3]" value="doc">
	<input type="hidden" name="m[4]" value="quickUpdate">
	<input type="hidden" name="id" value="{$id}">
	<br/>
	<table cellpadding="3" class="tld_table">
		<tr>
			<th>TLD Doc#</th>
			<th>Position</th>
			<th>Factory Num</th>
			<th>Description</th>
			<th><div align="center">Delete</div></th>
		</tr>
		
	{foreach name=lines key=key item=line from=$lines}
		<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
			<td>{$line.doc_id}</td>
			<td><input type="text" name="item[{$line.id}]" value="{$line.item}" style="width:50px;"></td>
			<td>{$line.factory_num}</td>
			<td>{$line.endescription}<br/>{$line.frdescription}</td>
			<td><div align="center">
				<a href="/en/private/product_support/index.ps.php?m[0]=publications&m[1]=manuals&m[2]=view&m[3]=doc&m[4]=delete&id={$id}&docid={$line.id}" 
				title="Click to delete" onClick="javascript:return confirm('Are you sure to remove Doc#{$line.doc_id} from this manual?');">
				<img src="/shared/icons/miscellaneous/delete.png" alt="Delete"/></div>
			</td>
		</tr>
	{/foreach}
	</table>
	<input type="submit" name="Submit" value="Update">
</form>
{else}
	<p>No records...</p>
{/if}