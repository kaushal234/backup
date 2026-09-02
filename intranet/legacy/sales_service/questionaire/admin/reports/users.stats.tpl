<table width="100%" border="1" cellpadding="2" cellspacing="0" bordercolor="#000000" bgcolor="#FFFFFF">
<tr>
	<th>User</th>
	<th>Total<br>Questions</th>
</tr>
{foreach name=outer item=line from=$results}
  <tr>
    <td>{$line.user|default:"&nbsp;"}</td>
    <td>{$line.total_responses|default:"&nbsp;"}</td>
  </tr>
{/foreach}
</table>
