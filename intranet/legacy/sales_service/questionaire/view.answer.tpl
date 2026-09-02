{if $answeredCorrectly == 1}
	<h2>CORRECT ANSWER</h2>
{else}
	<h2>INCORRECT ANSWER</h2>
{/if}
{if $response == 1}
	{$question.reason1|nl2br}
{elseif $response == 2}
	{$question.reason2|nl2br}
{/if}
{if $question.reason_picture<>""}
	<img src="/en/private/uploads/questionaire/{$question.reason_picture}" width="700">
{/if}
{if $single==1}
	<br><a href="{$smarty.server.SCRIPT_NAME}?m[0]=questionaire&m[1]=start&single=1">Click here to try another random question.</a>
{/if}
{if $numQuestions > 0}
	<br>
	<a href="{$smarty.server.SCRIPT_NAME}?m[0]=questionaire&m[1]=question">NEXT QUESTION, {$numQuestions} more to go >>></a>
{/if}