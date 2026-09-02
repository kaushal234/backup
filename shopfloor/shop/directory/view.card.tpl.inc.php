<?php
ob_start();
?>

<h3>vCard</h3>

<table border="0" bgcolor="#CCCCCC">
  <tr bgcolor="#FFFFFF">
    <td><img src="/shared/icons/tld-icon.gif"><br><br>
	<?php  if($header['photo']==""): ?>
		<img src="/shared/no_photo.jpg" width="150">
    <?php  else: ?>
		<img src="<?= "$php_self?m[0]=directory&m[1]=card&m[2]=outPhoto&width=128&id=".$header['id'] ?>">
	<?php  endif; ?>
    </td>
    <td>
       <table>
         <tr><td><?= _("Name") ?></td>			<td><b><?= $header['lastname'].", ".$header['firstname'] ?></b></td></tr>
         <tr><td><?= _("Division") ?></td>		<td><b><?= $header['division'] ?></b></td></tr>
         <tr><td><?= _("Buisness Unit") ?></td>	<td><b><?= $header['bu'] ?></b></td></tr>
         <tr><td><?= _("Department") ?></td>	<td><b><?= $header['department'] ?></b></td></tr>
         <tr><td><?= _("Title") ?></td>			<td><b><?= $header['title'] ?></b></td></tr>
         <tr><td><?= _("Email") ?></td>			<td><b><a href="mailto:<?= $header['email'] ?>"><?= $header['email'] ?></a></b></td></tr>
         <tr><td><?= _("Telephone") ?></td>		<td><b><?= $header['phone'] ?></b></td></tr>
         <tr><td><?= _("Direct Line") ?></td>	<td><b><?= $header['direct_phone'] ?></b></td></tr>
         <tr><td><?= _("Home Phone") ?></td>	<td><b><?= $header['home_phone'] ?></b></td></tr>
         <tr><td><?= _("Mobile") ?></td>		<td><b><?= $header['mobile'] ?></b></td></tr>
         <tr><td><?= _("Fax") ?></td>			<td><b><?= $header['fax'] ?></b></td></tr>
         <tr><td><?= _("Address") ?></td>		<td><b><?= nl2br($header['address']) ?></b></td></tr>
       </table>
    </td>
  </tr>
</table>

<?php
return ob_get_clean();
?>
