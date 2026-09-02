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

<div style="font-size: <?= $_SESSION['pi_font_size'] ?>;">

<hr>
<form name='warehouseNotification' action="/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=warehouseNotification&opno=<?= $_GET['opno'] ?>" method="post">
<table border=0>
	<tr>
		<td>
			<table border=0>
				<thead>
					<tr style="background:#2971a8; color:white;">
						<td style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><?= _('Availability rate') ?></td>
					</tr>
				</thead>
				<tbody>
					<?php if ($_SESSION['pi_dispo']<100) { $color='red'; } else { $color='#eeeeee'; } ?>
			  		<tr style="background:<?= $color ?>;">
			  			<td style="font-size: <?= $_SESSION['pi_font_size'] ?>; text-align: center;">
			  				<?= $_SESSION['pi_dispo'] ?>%
				  		</td>
				  	</tr>
				</tbody>
			</table>
		</td>
		<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>

		<td>
			<table border=0>
				<thead>
					<tr style="background:#2971a8; color:white;">
						<td style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><?= _('Due date') ?>:</td>
						<td style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><?= _('Slot') ?>:</td>
					</tr>
				</thead>
				<tbody>
			  		<tr style="background:#eeeeee;">
			  			<td style="font-size: <?= $_SESSION['pi_font_size'] ?>;">
                            <input type="date" id="dueDate" name="piWareDelay" value="<?= $today = date('Y-m-d') ?>" min="<?= $today ?>" max="<?= date('Y-m-d', strtotime('+ 10 days')) ?>">
                        </td>
			  			<td style="font-size: <?= $_SESSION['pi_font_size'] ?>;">
                            <input type="text" id="slot" name="slot" value="">
                        </td>
				  	</tr>
				</tbody>
			</table>
		</td>
		<td>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		</td>
		<td>
			<span onclick="forms['warehouseNotification'].submit();"><a href='#'>
			<table style='border-radius: 20px; border: 0px solid #000000' bgcolor='#C0C0C0'>
				<tr>
					<td align=center style='vertical-align:middle; font-size: <?= $_SESSION['pi_font_size'] ?>;' height=25px>
						<br>
			      		&nbsp;&nbsp;<b><span style='color: blue;'><?= _('Send notification') ?>: <?= _('Operation') ?> <?= $_GET['opno'] ?></span></b>&nbsp;&nbsp;
			      		<br><br>
			    	</td>
			    </tr>
			</table>
			</a></span>
		</td>
	</tr>
</table>
</form>

<hr>

<table border=0 width=100%>
	<thead>
		<tr style="background:#2971a8; color:white;">
			<td width=15% style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><?= _('Item') ?></td>
			<td width=55% style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><?= _('Description') ?></td>
			<td width=15% style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><?= _('Requierement') ?></td>
			<td width=15% style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><?= _('On hand inventory') ?></td>
		</tr>
	</thead>
	<tbody>
		<?php foreach($piParts as $key=>$part): ?>
			<?php $color = ($key%2) ? "#eeeeee" : "#d0d0d0"; ?>
            <tr style="background:<?=$color ?>">
                <td style="font-size: <?= $_SESSION['pi_font_size'] ?>;">
                    <?= $part['item'] ?>
                </td>
                <td style="font-size: <?= $_SESSION['pi_font_size'] ?>;">
                    <?= $part['itemDescription'] ?>
                </td>
                <?php if($part['netQuantity']>$part['inventoryOnHand']) { $color='red'; } ?>
                <td style="font-size: <?= $_SESSION['pi_font_size'] ?>; background:<?=$color ?>;">
                    <?= $part['netQuantity'] ?>
                </td>
                <td style="font-size: <?= $_SESSION['pi_font_size'] ?>; background:<?=$color ?>;">
                    <?= $part['inventoryOnHand'] ?>
                </td>
			  	</tr>
	  	<?php endforeach; ?>
	</tbody>
</table>

</div>


<?php
return ob_get_clean();
?>
