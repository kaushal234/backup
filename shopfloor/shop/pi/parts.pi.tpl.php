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
$( ".TblParts" ).css( "background-color" , "#EEEEEE" );
$( ".TblParts" ).css( "border" , "2px solid #000000" );
</script>

<table border=0 width=100% >
	<thead>
		<tr style="background:#2971a8; color:white;">
			<?php if(!$_SESSION['parts_extended']){  ?>
                <td width=10% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= _('Position') ?></b></td>
				<td width=10% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= _('Item') ?></b></td>
				<td width=20% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= _('Description') ?></b></td>
				<td width=5% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= _('Qty') ?></b></td>
				<td width=5% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= _('Drawing') ?></b></td>
				<td width=5% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= _('Rev') ?></b></td>
				<td width=5% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= _('Delivered') ?></b></td>
                <td width=5% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= _('Cost') ?></b></td>
                <td width=7% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= _('Backflush') ?></b></td>
			<?php } else {  ?>
			    <td width=10% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= _('Position') ?></b></td>
				<td width=14% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= _('Item') ?></b></td>
				<td width=30% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= _('Description') ?></b></td>
				<td width=7% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= _('Qty') ?></b></td>
				<td width=7% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= _('Drawing') ?></b></td>
				<td width=7% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= _('Rev') ?></b></td>
				<td width=7% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= _('Delivered') ?></b></td>
				<td width=7% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= _('Availability') ?></b></td>
				<td width=7% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= _('Expected') ?></b></td>
                <td width=7% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= _('Cost') ?></b></td>
                <td width=7% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= _('Backflush') ?></b></td>
            <?php } ?>
		</tr>
	</thead>
	<tbody>

		<?php foreach($piParts as $key => $piPart): ?>
			<?php $color = ($key%2) ? "#eeeeee" : "#d0d0d0"; ?>
			<?php if ($piPart['item']==$_SESSION['pi_sitm']) { ?>
				<tr style="background:#FFFF80;">
			<?php } else { ?>
	  			<tr style="background:<?= $color ?>">
	  		<?php } ?>
	  		    <td style="height: 40px; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><?= $piPart['position'] ?></td>
	  		    <td style="height: 40px; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;">
	  		    	<a href="/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=parts&sitm=<?= $piPart['item'] ?>">
	  		    		<?= $piPart['item'] ?>
	  		    	</a>
	  		    </td>
	  		    <td style="height: 40px; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;">
                    <?=($_SESSION['pi_erp'] >= '600' && $_SESSION['pi_erp'] <= '900' && $piPart['itemDescription'] !== null) ? mb_convert_encoding($piPart['itemDescription'], 'HTML-ENTITIES', 'UTF-8') : $piPart['itemDescription'] ?>
                </td>
	  			<td style="height: 40px; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><?= $piPart['netQuantity'] ?></td>
	  			<td style="height: 40px; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><a href="/shop/autoselect.php?m[0]=getfile&m[1]=PIdrawing&item=<?= $piPart['item'] ?>&revision=<?= $piPart['revision'] ?>" target="_blank"><img src="/shared/bluesphere/16x16/actions/filesaveas.png" style="width: 50%;"></td>
	  			<td style="height: 40px; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><?= $piPart['revision'] ?></td>
                    <?php if($piPart['actualQuantity']<$piPart['netQuantity']) {  ?>
					<td style="color: red; height: 40px; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><?= $piPart['actualQuantity'] ?></td>
				<?php } else {  ?>
					<td style="height: 40px; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><?= $piPart['actualQuantity'] ?></td>
				<?php }  ?>
	  			<?php if($_SESSION['parts_extended']){  ?>
                    <td style="height: 40px; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;" width=75%><?= $piPart['inventoryOnHand'] ?></td>
					<td style="height: 40px; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><?= $piPart['inventoryOnOrder'] ?></td>
				<?php }  ?>
            <td style="height: 40px; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><?= $piPart['costPrice'] ?></td>
            <td style="height: 40px; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><?= $piPart['reportMaterial'] ?></td>
	  		</tr>
	  	<?php endforeach; ?>
	</tbody>
</table>

<?php
return ob_get_clean();
?>
