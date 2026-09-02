<table>
<tr>
	<td><h3>1.</h3></td>
	<td><p><a href="{$smarty.server.SCRIPT_NAME}?m[0]=start&n=1">Ask me a random question.</a></p></td>
</tr>
<tr>
	<td><h3>2.</h3></td>
	<td>
	<form name="form1" method="post" action="{$smarty.server.SCRIPT_NAME}">
		Ask me
		<input type="hidden" name="m[0]" value="start">
		<select name="n">
			<option selected>5</option>
			<option>10</option>
			<option>15</option>
			<option>20</option>
		</select> questions related to 
		<select name="equip_cat">
			<option value=''>Any Category</option>
			{html_options options=$equip_cats}
		</select>
		<select name="question_cat">
			<option value=''>Any Category</option>
			{html_options options=$question_cats}
		</select>
		<input type="submit" name="Submit" value="Submit">
	</form>
</tr>
<tr>
	<td><h3>3.</h3></td>
	<td>
	<form name="form1" method="post" action="{$smarty.server.SCRIPT_NAME}">
		Ask me the 
		<input type="hidden" name="m[0]" value="start">
		<input type="hidden" name="m[1]" value="latest">
		<select name="n">
			<option selected>5</option>
			<option>10</option>
			<option>15</option>
			<option>20</option>
		</select>
		latest questions
		<input type="submit" name="Submit" value="Submit">
	</form>
	</td>
</tr>
<tr>
	<td><h3>4.</h3></td>
	<td></td>
</tr>
</table>	