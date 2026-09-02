<table width="100%" border="1" cellpadding="3" cellspacing="" bordercolor="#000000" bgcolor="#FFFFFF">
<tr><th>Question<br>Number</td>
	<th>Category</td>
	<th>Category2</td>
	<th>Expiration</td>
	<th>Question</td>
	<th>Total<br>Responses</td>
	<th>Total<br>Correct</td>
	<th>Total<br>Correct %</td>
</tr>
{foreach name=outer item=line from=$results}
  <tr>
    <td><a href="{$smarty.server.SCRIPT_NAME}?m[0]=questionaire&m[1]=id={$line.id}">{$line.id}</a></td>
    <td>{$line.category|default:"&nbsp;"}</td>
    <td>{$line.category2|default:"&nbsp;"}</td>
    <td>{$line.expiration|default:"&nbsp;"}</td>
    <td>{$line.question|default:"&nbsp;"}</td>
    <td>{$line.total_responses|default:"&nbsp;"}</td>
    <td>{$line.total_correct|default:"&nbsp;"}</td>
    <td>{$line.total_correct_percentage|default:"&nbsp;"}</td>
  </tr>
{/foreach}
</table>
