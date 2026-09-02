<table width="100%" border="1" cellpadding="3" cellspacing="" bordercolor="#000000" bgcolor="#FFFFFF">
<tr>
	<th>Author</td>
	<th>Number of Questions</td>
</tr>
{foreach name=outer item=line from=$results}
  <tr>
    <td>{$line.author|default:"&nbsp;"}</td>
    <td>{$line.questionCount|default:"&nbsp;"}</td>
  </tr>
{/foreach}
</table>
