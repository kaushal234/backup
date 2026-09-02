<p>We hope you will enjoy this questionnaire that has been designed to be easy and friendly to use.</p>
<p>TLD has developed this E-questionnaire as a good tool to continuously improve the product knowledge of the sales and service team members.</p>
<p>New questions are been uploaded on a regular basis by the Product Support Managers of all TLD factories and we invite you to come and challenge your own knowledge of the TLD products on a regular basis.</p>
<p>Feel free to come and use the tool, TLD does not record electronically your personal success rate to these questions. </p>
<p>TLD only records:
<ul>
<li>the success rate of each question in order to allow the Product Support Managers and COO to better understand how to improve their training.</li>
<li>The number of questions answered by each of the users to monitor the usage of the tool</li>
</ul>
</p>
<p>Any idea for improving the tool or about the questions themselves should be sent to webmaster@tld-gse.com will be welcome.</p>
<h3>Start here...</h3>
<table>
<tr>
	<td><h3>1.</h3></td>
	<td><p><a href="{$smarty.server.SCRIPT_NAME}?m[0]=questionaire&m[1]=start&single=1">Ask me a random question.</a></p></td>
</tr>
<tr>
	<td><h3>2.</h3></td>
	<td>
	<form name="form1" method="post" action="{$smarty.server.SCRIPT_NAME}">
		Ask me
		<input type="hidden" name="m[0]" value="questionaire">
		<input type="hidden" name="m[1]" value="start">
		<select name="n">
			<option selected>5</option>
			<option>10</option>
			<option>15</option>
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
		<input type="hidden" name="m[0]" value="questionaire">
		<input type="hidden" name="m[1]" value="start">
		<input type="hidden" name="m[2]" value="latest">
		<select name="n">
			<option selected>5</option>
			<option>10</option>
			<option>15</option>
		</select>
		latest questions
		<input type="submit" name="Submit" value="Submit">
	</form>
	</td>
</tr>
</table>	