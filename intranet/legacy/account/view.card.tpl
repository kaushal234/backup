<table border="0" bgcolor="#CCCCCC">
<tr bgcolor="#FFFFFF">
	<td>
	<img src="/shared/logos/alvest_logo.jpg"><br><br>
	{if $person.photo==""}
		<img src="/shared/no_photo.jpg" width="150">
	{else}
		<a href="/en/private/directory/index.php?m[0]=outPhoto&width=512&id={$person.id}">
		<img src="/en/private/directory/index.php?m[0]=outPhoto&width=128&id={$person.id}">
		</a>
	{/if}
	</td>
	<td>
		<table>
			<tr><td>Name</td>	<td><b>{$person.lastname}, {$person.firstname}</b></td></tr>
			<tr><td>Alias / Nickname</td>	<td><b>{$person.nickname}</b></td></tr>
			<tr><td>Division / Region</td>	<td><b>{$person.division}</b></td></tr>
			<tr><td>Business Unit</td><td><b>{$person.location}</b></td></tr>
			<tr><td>Department</td>	<td><b>{$person.department}</b></td></tr>
			<tr><td>TLD Function</td><td><b>{$person.tld_function}</b></td></tr>
			<tr><td>Title</td>		<td><b>{$person.title}</b></td></tr>
			<tr><td>Email</td>		<td><b><a href="mailto:{$person.email}">{$person.email}</a></b></td></tr>
			<tr><td>Telephone</td>	<td><b>{$person.phone}</b></td></tr>
			<tr><td>Direct Line</td><td><b>{$person.direct_phone}</b></td></tr>
			<tr><td>Home Phone</td>	<td><b>{$person.home_phone}</b></td></tr>
			<tr><td>Mobile</td>		<td><b>{$person.mobile}</b></td></tr>
			<tr><td>Fax</td>		<td><b>{$person.fax}</b></td></tr>
			<tr><td>Address</td>	<td><b>{$person.address|nl2br}</b></td></tr>
			<tr><td><img src="/shared/icons/idcard-icon-sm.jpg"></td>
			<td><a href="/en/private/directory/index.php?m[0]=people&m[1]=view&m[2]=outVcard&id={$person.id}">Download Vcard</a></td></tr>
		</table>
	</td>
</tr>
</table>