<?php
ob_start();
$COOKIE_NAME = 'ALVEST_STAGING';
?>
<html>
	<head>
		<title><?= $DEFAULT_TITLE ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        <link href="/tld-gse.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="/shop/css/style.css">
		<script type="text/javascript" src="/shared/javascript/tld/tld.table.sorttable.js"></script>
        <script type="text/javascript" src="/shared/javascript/tld/qfamsHandler-min.js"></script>
        <?= $headExtra ?? '' ?>
    </head>
<body bgcolor="#FFFFFF" text="#000000">

<div align="center">
    <?php if(isset($_COOKIE[$COOKIE_NAME])): ?>
        <div class="staging-warning navbar navbar-default navbar-fixed-top">
            You are on a test environment: the data you see here might be outdated and your actions will not have any impact on the production data.<a href="<?= $php_self ?>?m[0]=switchstaging" name="staging link" title="<?php echo _('Staging'); ?>"><?php echo _('Click here to go back to the Prod environment'); ?></a>
        </div>
    <?php endif; ?>
	<table width="990" bgcolor="<?= $DEFAULT_BGC ?>">
		<?php if (($m[0] ?? null) !=='pi' && ($m[0] ?? null) !=='time_keeping') { ?>
			<tr>
				<td width="600">
	                <h3>
                        <?php if (isset($LOCATION)): ?>
                            <a href="<?= "$php_self?m[0]=changeLocation" ?>">
                                <?= $LOCATION["location"] ?></a> |
                        <?php endif; ?>
	                  <?= $_TITLE ?> | <a href="<?= "$php_self?m[0]=switchstaging" ?>">Go to test environment</a>
                    </h3>
				</td>
				<td width="300">
					<form action="<?= $php_self ?>?m[0]=er&m[1]=view" method="post">
						<p>
							<?= _("Project")."# "._("or")." SN#"; ?>: <input type="text" name="sn" value="" size="8">
							<input type="submit" value="<?= _("Search"); ?>" style="border:1px solid #CCC;">
						</p>
					</form>
				</td>
				<td width="60" align="center">
                    <?php if (isset($LOCATION)): ?>
                        <a href="<?= $php_self ?>?m[0]=time_keeping&m[2]=<?= $LOCATION['erp'] ?>"><img src='//www.tld-gse.com/shared/icons/application/Clock.png' width=45></a>
                    <?php endif; ?>
				</td>
			</tr>
		<?php } ?>
		<tr>
			<td colspan="3">
				<p class="smalltext"><?= $_MENU ?></p>
                <?php if ($session->getFlashBag()->has('baan.queries')) { ?>
                    <?php foreach ($session->getFlashBag()->get('baan.queries') as $message) { ?>
                        <div style="color: orange; border: 1px solid orange; padding: 5px; margin: 5px">
                            <?php echo $message ?>
                        </div>
                    <?php } ?>
                <?php } ?>
				<?php
				if($_ERROR):
					echo '<p class="alert">'.$_ERROR.'</p>';
				endif;
				echo $_BODY;
				?>
			</td>
		</tr>
	</table>
</div>

</body>
</html>
<?php
return ob_get_clean();
?>
