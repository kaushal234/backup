<?php
ob_start();
?>

<script type="text/javascript" src="/shared/javascript/jquery/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="/shared/javascript/jquery/jquery-ui-1.10.1.custom.min.js"></script>
<link rel="stylesheet" href="/shared/javascript/jquery/css/smoothness/jquery-ui-1.10.1.custom.min.css">

<?php
$body = include("$PATH/header.pi.tpl.php");
$_SESSION['inspectionLine']=$_GET['line'];
$line=$_SESSION['inspectionLine'];
?>

<script type="text/javascript">
$( ".TblInspect" ).css( "background-color" , "#EEEEEE" );
$( ".TblInspect" ).css( "border" , "2px solid #000000" );
</script>

<table border=0 width=100% >
	<thead>
		<tr style="background:#2971a8; color:white;">
			<td width=19% style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= _('Subject') ?></b></td>
			<td width=33% style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= _('Description') ?></b></td>
			<td width=33% style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= _('Value') ?></b></td>
			<td width=5% style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= _('Unit') ?></b></td>
			<td width=5% style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= _('CRABS (Open/All)') ?>?</b></td>
			<td width=5% style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= _('Help')?></b></td>
		</tr>
	</thead>
	<tbody>

		<?php foreach($piQuestions as $key => $piQuestion): ?>
			<?php $color = ($key % 2) ? "#eeeeee" : "#d0d0d0"; ?>
<?php if ($key == $line) { // Line to answer  ?>
	  		<tr style="background:<?= $color ?>">

	  			<td width=19% style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><br><?= $piQuestion['subject'] ?><br><br></td>
	  			<td width=33% style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><br><?= $piQuestion['desc'] ?></td>

	  			<?php if ($key == $line) { // Line to answer  ?>
					<td  width=33% style="vertical-align:bottom; font-size: <?= $_SESSION['pi_font_size'] ?>;" valign="middle">
						<form action="/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=inspection&m[3]=derogation&line=<?= $line ?>" method="post">
							<?= _('Confirm derogation. Fill TASK number') ?>: &nbsp;
			  				<input type=text name='piDerogation'>
			  				<INPUT border=0 src="//www.tld-gse.com/shared/bluesphere/16x16/actions/filesave.png" type=image Value=submit align="middle" >
						</form>
					</td>
  				<?php } else {  // Other lines ?>
		  			<td width=33% style="font-size: <?= $_SESSION['pi_font_size'] ?>;">
	  				<br>
	  				<?php if ($piQuestion['answer']!='') { ?>
	  					<a href="/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=inspection&m[3]=answer&line=<?= $key ?>"><img src='//www.tld-gse.com/shared/bluesphere/16x16/actions/configure.png'></a>
	  					&nbsp;&nbsp;
	  					<?= $piQuestion['answer'] ?>
	  				<?php } else { ?>
	  					<br><a class='openHelp' AttrHelp='<?= $piQuestion['help'] ?>'><img src='//www.tld-gse.com/shared/bluesphere/16x16/actions/viewmag.png'></a>
	  				<?php } ?>
	  			</td>
  				<?php } ?>


	  			<td width=5% style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><br><?= $piQuestion['answer_unit'] ?></td>
	  			<td width=5% style="font-size: <?= $_SESSION['pi_font_size'] ?>;"></td>
	  			<td width=5% style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><br><?= $piQuestion['help'] ?></td>
	  		</tr>
<?php } ?>
	  	<?php endforeach; ?>
	</tbody>
</table>

<script>
$( ".openHelp" ).click(function() {
	varHelp=$( this ).attr('AttrHelp');
	window.open("<?= $SHOPFLOOR_URL ?>/index.php?m%5B0%5D=dms&id="+varHelp);
});
</script>

<?php
return ob_get_clean();
?>
