<p>Welcome to the Sales and Service Module</p>
<table width="100%">
<tr><td>
<h3>Find Equipment</h3>
	<form action="/en/private/product_support/index.ps.php" method="post" id="frmSearch" name="frmSearch">
		<input type="hidden" name="m[0]" value="equipment">
		<input type="hidden" name="m[1]" value="listing">
		<input type="hidden" name="m[2]" value="search">
		<input name="target" type="text" value="Basic search" onfocus="this.value=''">
		<input name="Submit" type="submit" value="Find">
	</form>
	<p>
	<b>OR</b>
	<br><br><br>
	<a href="/en/private/product_support/equipment/equipment_admin.php">Click here to list all equipment</a>
	</p>
</td>
<td>
<h3>Find Parts</h3>
<form action="/en/private/parts/parts.php" method="get">
	<input type="hidden" name="m[0]" value="inv">
	<input type="hidden" name="m[1]" value="view">
	<input type="text" name="id" value="PN" onfocus="this.value=''">
	<input type="submit" name="submit" value="submit">
</form>
</td>
<td>
<h3>Related Service Information Shortcuts<br>
(in Product Support Module)</h3>
	<ul>
	<li><a href="/en/private/product_support/index.ps.php?m[0]=equipment">Equipment</a></li>
	<li><a href="/en/private/product_support/index.ps.php?m[0]=wc">Warranty</a></li>
	<li><a href="/en/private/product_support/index.ps.php?m[0]=wc&m[1]=form&m[2]=byNum">Warranty, by Number</a></li>
	<li><a href="/en/private/product_support/index.ps.php?m[0]=sb">Service Bulletins</a></li>
	<li><a href="/en/private/product_support/index.ps.php?m[0]=publications">Publications</a></li>
	<li><a href="{$spq_link}">SPQ</a></li>
	</ul>
</td>
</tr>
</table>

