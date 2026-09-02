<div class="container-fluid">
	<div class="row">
		<div class="btn-text col-xs-6 col-md-4 <?php if($SFR_COUNT == 0) echo "disabled"; ?>">
			<form method="post" class="clickable-link" action="<?= "$PHPSELF?page=sfr&action=listing" ?>">
    			<input type="hidden" name="sfr_by_customer" value="<?= $customer_id ?>">
    			<input type="hidden" name="asm_id" value="<?= $ASM_ID ?>">
    			<a href="#" class="square-btn submit-form">SFR<br>
  					<span class="label label-danger label-as-badge"><?= $SFR_COUNT ?></span>
  				</a>
			</form>
		</div>
		<div class="btn-text col-xs-6 col-md-4 <?php if($TOC_COUNT == 0) echo "disabled"; ?>">
			<form method="post" class="clickable-link" action="<?= "$PHPSELF?page=toc&action=listing" ?>">
    			<input type="hidden" name="toc_by_customer" value="<?= $customer_id ?>">
    			<input type="hidden" name="asm_id" value="<?= $ASM_ID ?>">
    			<a href="#" class="square-btn submit-form">TOC<br>
  					<span class="label label-danger label-as-badge"><?= $TOC_COUNT ?></span>
  				</a>
			</form>
		</div>
		<div class="btn-text col-xs-6 col-md-4 <?php if($DELIVERIES_COUNT == 0) echo "disabled"; ?>">
			<form method="post" class="clickable-link" action="<?= "$PHPSELF?page=odp&action=listing" ?>">
    			<input type="hidden" name="odp_by_customer" value="<?= $customer_id ?>">
    			<input type="hidden" name="asm_id" value="<?= $ASM_ID ?>">
    			<a href="#" class="square-btn submit-form">Deliveries<br>
  					<span class="label label-danger label-as-badge"><?= $DELIVERIES_COUNT ?></span>
  				</a>
			</form>
		</div>	
	</div>
</div>

