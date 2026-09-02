<form method="post" action="{$smarty.server.SCRIPT_NAME}">
<input type="hidden" name="m[0]" value="questionaire">
<input type="hidden" name="m[1]" value="{$next_step}">
<input type="hidden" name="id" value="{$question.id}">
{if $single==1}
	<input type="hidden" name="single" value="1">
{/if}
<table>
<tr>
	<td colspan="2"><h3>{$question.category} {$question.category2} Question {$question.id}</h3></td>
</tr>
<tr>
	<td colspan="2">{$question.question|nl2br}</td>
</tr>
{if $question.picture<>""}
	<tr>
		<td colspan="2"><img src="/en/private/uploads/questionaire/{$question.picture}" width="600"></td>
	</tr>
{/if}
{if $question.response1<>""}
	<tr>
		<td width="20"><input type="radio" name="response" value="1" checked>
		</td>
		<td><h3>Answer 1: </h3>
		{$question.response1|nl2br}</td>
	</tr>
{/if}
{if $question.response2<>""}
	<tr>
		<td width="20">
		<input type="radio" name="response" value="2">
		</td>
		<td><h3>Answer 2: </h3>
		{$question.response2|nl2br}</td>
	</tr>
{/if}
{if $question.response3<>""}
	<tr>
		<td width="20">
		<input type="radio" name="response" value="3">
		</td>
		<td><h3>Answer 3: </h3>{$question.response3|nl2br}</td>
	</tr>
{/if}
{if $question.response4<>""}
	<tr>
		<td width="20">
		<input type="radio" name="response" value="4">
		</td>
		<td><h3>Answer 4: </h3>{$question.response4|nl2br}</td>
	</tr>
{/if}
</table>
  <input type="submit" name="Submit" value="Submit">
</form>