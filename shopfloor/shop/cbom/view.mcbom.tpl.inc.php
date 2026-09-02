<?php
ob_start();
?>

<h2><?= sprintf(_("BOM for %s from company %s as of %s"),$myCBOM->itsID,$myCBOM->itsERP,$myCBOM->itsDate) ?></h2>

<p>
<a href="<?= $php_self ?>?m[0]=bom&m[1]=view&erp=<?= $myCBOM->itsERP ?>&pn=<?= $myCBOM->itsID ?>&multi=TRUE">MULTI Level</a>&nbsp;|&nbsp;
<a href="<?= $php_self ?>?m[0]=bom&m[1]=view&erp=<?= $myCBOM->itsERP ?>&pn=<?= $myCBOM->itsID ?>">SINGLE Level</a></p>
<p>

<table>
<tr style="background:#2971a8;color:white;">
	<th><?= _("Part Number") ?></th>
	<th><?= _("Position") ?></th>
	<th><?= _("Description (en)") ?><br/><?= _("Description (alt)") ?></th>
	<th><?= _("Qty") ?></th>
	<th><?= _("UM") ?></th>
	<th><?= _("Status") ?></th>
	<th><?= _("Drawing") ?></th>
	<th><?= _("Rev") ?></th>
	<th><?= _("Code") ?></th>
    <th><?= _("Fantome(Y/N)") ?></th>
</tr>
<?php
$j=0;
foreach($cbom as $line):
		$j++;
?>
<tr
<?php
if($j%2==1) echo 'style="background:#eeeeee;"';
else echo 'style="background:#d0d0d0;"';
?>
>
	<td>
		<?php  for($i=0;$i<$line['level'];$i++) echo "<font size='+1'>.</font>"; ?>
		<a href="<?= $php_self ?>?m[0]=bom&m[1]=view&erp=<?= $myCBOM->itsERP ?>&pn=<?= $line['t_sitm'] ?>&date=<?= $myCBOM->itsDate ?>">
		<?= $line['t_sitm'] ?>
		</a>
	</td>
	<td>
		<?php  for($i=0;$i<$line['level'];$i++) echo "&nbsp;"; ?>
		<img src="//www.tld-gse.com/shared/branch.gif"> <?= $line['t_pono'] ?>
	</td>
	<td><?= $line['t_dsca'] ?> <?php  if(!empty($line['altdsca'])) echo "<br/> ".($erp >= '600' && $line['altdsca'] !== null) ? mb_convert_encoding($line['altdsca'], 'HTML-ENTITIES', 'UTF-8') : $line['altdsca'] ?></td>
	<td><?= $line['t_qana'] ?></td>
	<td><?= $line['t_cuni'] ?></td>
	<td><?php
		if($line['t_exdt']=="0000-00-00" || $line['t_exdt']=="1753-01-01") echo "OK";
		else sprintf(_("This item was changed %s"),$line['t_exdt']);
		?>
	</td>
    <!-- revision in the link is used only to invalidate the browser cache when the drawing revision changes. -->
    <td><a target="_blank" href="<?= $php_self ?>?m[0]=getfile&m[1]=drawing&item=<?= $line['t_sitm'] ?>&revision=<?= $line['t_revi'] ?? '' ?>">
		<img src="/shared/bluesphere/16x16/actions/filesaveas.png" alt="<?= _("Save file to your hard disk") ?>"></a>
	</td>
	<td><?= $line['t_revi'] ?? '' ?></td>
	<td><?= $line['t_csig_edm'] ?? '' ?></td>
    <td><?= $line['t_cpha'] ?? '' ?></td>
</tr>
<?php
endforeach;
?>
</table>

<?php
return ob_get_clean();
?>
