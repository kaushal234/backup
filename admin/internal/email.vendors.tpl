<html>
<head></head>
<body>
	<p><img src="http://www.tld-gse.com/shared/icons/tld-icon.gif" align="right">Please DO NOT reply to this message. This is an automated message.
	Please note that the address to access the vendors site is http://www.tld-gse.com/vendors</p>
	<p>These items are all outstanding items from TLD Purchase order(s) as of <b>{$smarty.now|date_format}</b>.
	You can view this and other information by logging into the TLD Vendor website <b>http://www.tld-gse.com/vendors</b>.
	Your username is <b>'{$vendor.email}'</b>; contact the webmaster if you have forgotten your password.An email will be sent once a week to keep you updated with information regarding TLD.
	If you have any technical queries please contact webmaster@tld-gse.com
	for all other queries please contact your TLD representative at <b>{$vendor.rep_email}</b>.</p>
	<table border="1">
	<tr>
	{foreach name=titles key=field item=title from=$fields}
		<th>{$title}</th>
	{/foreach}
	</tr>
	{foreach name=lines item=row from=$rows}
		<tr>
		{foreach name=fields key=field item=title from=$fields}
			<td>
				{$row.$field}
			</td>
		{/foreach}
		</tr>
	{/foreach}
	</table>
</body>
</html>