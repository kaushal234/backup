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
			<td width=19% style="vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;">
			  <table width=100%>
			  	<tr style="background:#2971a8; color:white;">
			  		<td width=30% style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= _('Subject') ?></b></td>
			  		<td width=70% align=right style="font-size: <?= $_SESSION['pi_font_size'] ?>;">
			  			<select name='QuestionsGroup'>
			  				<?= $_SESSION['QuestionsGroup'] ?>
			  			</select>
			  		</td>
			  	</tr>
			  </table>
			</td>
			<td width=33% style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= _('Description') ?></b></td>
			<td width=33% style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= _('Value') ?></b></td>
			<td width=5% style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= _('Unit') ?></b></td>
			<td width=5% style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= _('CRABS (Open/All)') ?>?</b></td>
			<td width=5% style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= _('Help')?></b></td>
		</tr>
	</thead>
	<tbody>

		<?php 	$lig=0;
				foreach($piQuestions as $key => $piQuestion):
				$lig+=1;
				$colorLum = ($lig%2) ? "dark" : "light";
				if ($piQuestion['answer']=='') {
					if ($colorLum=="dark") { $color="#d0d0d0"; } else { $color="#eeeeee"; } // grey
				}
				if ($piQuestion['answer']!='') {
					if ($piQuestion['toleranceMsg']=='') {
						if ($colorLum=="dark") { $color="#48ff48"; } else { $color="#aaffaa"; } // green
					} else {
						if ($colorLum=="dark") { $color="#ff6666"; } else { $color="#ffb3b3"; } // red
					}
				} ?>

	  		<tr height=100% style="background:<?= $color ?>">
	  			<td width=19% style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><br><?= $piQuestion['subject'] ?><br><br></td>
	  			<td width=33% style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><br><?= $piQuestion['desc'] ?></td>

	  			<?php if ($key==$line) { // Line to answer  ?>
					<td height=100% width=48% colspan=4 style="vertical-align:bottom; font-size: <?= $_SESSION['pi_font_size'] ?>;" valign="middle">
						<form action="/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=inspection&m[3]=saveAlert&line=<?= $line ?>" method="post">
							<input type=text name='piAlert' size=10 style="width:400px; height:40px;">
							&nbsp;&nbsp;
							<INPUT border=0 src="//www.tld-gse.com/shared/bluesphere/16x16/actions/apply.png" type=image Value=submit align="middle">
						</form>
					</td>
  				<?php } else {  // Other lines ?>
		  			<td width=33% style="font-size: <?= $_SESSION['pi_font_size'] ?>;">
	  				<br>&nbsp;&nbsp;
	  				<?php if ($piQuestion['non_conformity']!="YES") {?>
	  					<a href="/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=inspection&m[3]=answer&line=<?= $key ?>"><img src='//www.tld-gse.com/shared/bluesphere/16x16/actions/configure.png' width='25'></a>
	  				<?php } ?>
	  				<?php if ($piQuestion['answer']!='') { ?>
	  					&nbsp;&nbsp;<?= $piQuestion['answer'] ?>
	  				<?php } ?>
	  				<?php if ($piQuestion['toleranceMsg']!='') { ?>
						<br><b><?= $piQuestion['toleranceMsg'] ?></b>
					<?php } ?>
	  			</td>
	  			<td width=5% style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><br><?= $piQuestion['answer_unit'] ?></td>
	  			<td width=5% style="font-size: <?= $_SESSION['pi_font_size'] ?>;" align=center><?= $piQuestion['openCRABS'] ?>&nbsp;/&nbsp;<?= $piQuestion['CRABS'] ?></td>
	  			<td width=5% style="font-size: <?= $_SESSION['pi_font_size'] ?>;">
	  				<?php if ($piQuestion['help_fr']!='') { ?>
	  					<br><a class='openHelp' AttrHelp='<?= $piQuestion['help_fr'] ?>'><img src='//www.tld-gse.com/shared/bluesphere/16x16/actions/viewmag.png'></a>
	  				<?php } ?>
  				</td>
  				<?php } ?>
	  		</tr>
	  	<?php endforeach; ?>
	</tbody>
</table>


<?php
return ob_get_clean();
?>
