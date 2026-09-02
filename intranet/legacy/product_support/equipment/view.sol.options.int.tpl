<h3>{$title|default:"Options"}</h3>
<table>
	{foreach name=lines key=key item=line from=$lines}
		<tr bgcolor="#eeeeee">
		<td>{$line.dsca}</td>
		</tr>
	{/foreach}
</table>
