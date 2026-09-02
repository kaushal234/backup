<?php
ob_start();
?>
<script type="text/javascript" src="/shared/javascript/jquery/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="/shared/javascript/jquery/jquery-ui-1.10.1.custom.min.js"></script>
<link rel="stylesheet" href="/shared/javascript/jquery/css/smoothness/jquery-ui-1.10.1.custom.min.css">

<?php
$body = include("$PATH/header.pi.tpl.php");
?>

<script type="text/javascript">
$( ".TblOperation" ).css( "background-color" , "#EEEEEE" );
$( ".TblOperation" ).css( "border" , "2px solid #000000" );
</script>

<table border=0 width=100% >
	<thead>
		<tr style="background:#2971a8; color:white;">
			<td width=10% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= $translator->trans('shopfloor_columns.operation', [], 'pio') ?></b></td>
			<?php if ($_SESSION['pi_warehouse']) { ?>
				<td width=10% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= $translator->trans('shopfloor_columns.material', [], 'pio') ?></b></td>
			<?php } ?>
			<td width=50% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= $translator->trans('shopfloor_columns.process_description', [], 'pio') ?></b></td>
			<td width=15% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= $translator->trans('shopfloor_columns.inspection_total', [], 'pio') ?></b></td>
			<td width=15% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= $translator->trans('shopfloor_columns.closed_open_all', [], 'pio') ?></b></td>
		</tr>
	</thead>
	<tbody>

		<?php
        $changeColor=true;
        foreach($operations as $operation):
            $changeColor = !$changeColor;
			$color = ($changeColor) ? "#eeeeee" : "#d0d0d0"; ?>
			<?php if ((string)$operation['t_opno'] === $_SESSION['pi_opno']) : ?>
		  		<tr id="operationAnchor" style="background:#FFFF80;">
		  			<td align=center style="height: 40px; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;">
			  			<?= $operation['t_opno'] ?>
		  			</td>
	  		<?php  else : ?>

  				<?php if ($operation['accessible']) : ?>
  					<tr style="background:<?= $color ?>">
			  			<td align=center style="height: 40px; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;">
				  			<a class='disableDoubleClick' href="/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=operation&opno=<?= $operation['t_opno'] ?>&tano=<?= $operation['t_tano'] ?>"><?= $operation['t_opno'] ?></a>
                        </td>
  				<?php  else : ?>
  					<tr style="background:<?= $color ?>">
			  			<td>
				  			<table width=100%>
				  				<tr width=100%>
				  					<td align=left width=33% style="color: red; height: 40px; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;">
				  						<b><?= $operation['t_opno'] ?></b>
				  					</td>
				  					<td align=center width=33% style="height: 40px; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;">
				  						<img src='//www.tld-gse.com/shared/icons/application/black_key.png'  height="30" width="30">
				  					</td>
			  					</tr>
				  			</table>
			  			</td>
  				<?php endif; ?>
	  		<?php endif; ?>

	  			<?php if ($_SESSION['pi_warehouse']) : ?>
					<td style="vertical-align: middle; text-align: center; font-size: <?= $_SESSION['pi_font_size'] ?>;">
						<?php if ($operation['dispo']!='') : ?>
							<?php if (!$operation['warehouseNotified']) : ?>
								<a href="/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=warehouse&opno=<?= $operation['t_opno'] ?>"><img src='//www.tld-gse.com/shared/icons/application/cart.png'></a>
								&nbsp;&nbsp;
								<?= $operation['dispo'] ?>
							<?php endif; ?>
						<?php endif; ?>
					</td>
				<?php endif; ?>

				<td style="vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;">
                    <?= ($_SESSION['pi_erp'] >= '600' && $_SESSION['pi_erp'] <= '900' && $operation['t_dsca'] !== null) ? mb_convert_encoding($operation['t_dsca'], "HTML-ENTITIES", 'UTF-8') : $operation['t_dsca'] ?>
                </td>

	  			<?php // si 0 questions: full green ?>
	  			<?php if ($operation['questions'] === 0) { $progressColor='green'; } ?>
	  			<?php // si > 0 questions et 0 r�ponses: white ?>
	  			<?php if ($operation['questions'] !== 0 && $operation['answered']===0) { $progressColor='white'; } ?>
	  			<?php // si > 0 questions et crabs: red ?>
	  			<?php if ($operation['questions'] !== 0 && (($operation['TO-FIX']+$operation['TO-INSPECT']+$operation['FOR-DEROGATION']) !==0 || $operation['derogationMissing'])) { $progressColor='red'; } ?>
	  			<?php // si r�ponses partielles et pas crabs: black ?>
	  			<?php if ($operation['answered'] !== 0 && ($operation['TO-FIX']+$operation['TO-INSPECT']+$operation['FOR-DEROGATION'])===0 && !$operation['derogationMissing']) { $progressColor='black'; } ?>
	  			<?php // si r�ponses totales et pas crabs: green ?>
	  			<?php if ($operation['questions'] === $operation['answered'] && ($operation['TO-FIX']+$operation['TO-INSPECT']+$operation['FOR-DEROGATION'])===0 && !$operation['derogationMissing']) { $progressColor='green'; } ?>

  				<td style="vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;">
  					<table width=100% >
  						<tr>
  							<td width=70% style="vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;">
		  						<table width=100% style="border:1px solid black;" cellpadding=0 cellspacing=0>
		  							<tr>
		  								<?php if ($operation['questions'] !== 0) : // some questions for this operation ?>
			  								<?php for ($i = 1; $i <= 20; $i++) : ?>
			  									<?php if ($i <= $operation['answered']/$operation['questions']*20) : ?>
			  										<td style="vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;" width=5% bgcolor=<?= $progressColor ?>>&nbsp;</td>
												  <?php else : ?>
													  <td style="vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;" width=5%>&nbsp;</td>
												  <?php endif; ?>
			  								<?php endfor; ?>
			  							<?php else : ?>
			  								<?php for ($i = 1; $i <= 10; $i++) : // No questions for this operation ?>
			  									<td style="vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;" width=10% bgcolor=<?= $progressColor ?>>&nbsp;</td>
											  <?php endfor; ?>
			  							<?php endif; ?>
			  						</tr>
	  							</table>
	  						</td>
  							<td width=30% align=right style="vertical-align: middle;">
  								<div style="vertical-align: middle; font-size: small;">&nbsp;&nbsp;&nbsp;(<?= $operation['questions'] ?>)</div>
  							</td>
	  					</tr>
	  				</table>
	  			</td>

	  			<td style="vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;" align=center>
	  				<?php if ($operation['TO-FIX']+$operation['TO-INSPECT']+$operation['FOR-DEROGATION'] === 0) : ?>
	  					<?= $operation['TO-FIX']+$operation['TO-INSPECT']+$operation['FOR-DEROGATION'] ?>&nbsp;/&nbsp;<?= $operation['CLOSED']+$operation['TO-FIX']+$operation['TO-INSPECT']+$operation['FOR-DEROGATION'] ?>
	  				<?php else : ?>
	  					<a href="<?= $php_self ?>?m[0]=pi&m[1]=form&m[2]=crabs&operation=<?= $operation['t_opno'] ?>">
	  						<?= $operation['TO-FIX']+$operation['TO-INSPECT']+$operation['FOR-DEROGATION'] ?>&nbsp;/&nbsp;<?= $operation['CLOSED']+$operation['TO-FIX']+$operation['TO-INSPECT']+$operation['FOR-DEROGATION'] ?>
	  					</a>
	  				<?php endif; ?>
	  			</td>
	  		</tr>
	  	<?php endforeach; ?>
	</tbody>
</table>

<script type="text/javascript">
    location.href = "#operationAnchor";

    $(document).ready(function () {
        $(".disableDoubleClick").click(function(){
            $(this).hide().after('<span class="imgLoading"><img src="/shared/loading_spinner.gif" alt="Loading..." height=35></span>');
        });
    })
</script>

<?php
return ob_get_clean();
?>
