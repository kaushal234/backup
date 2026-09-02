<table width="100%" border=0>
  <tr width=100%>
    <td>
      <table width=100%>
        <tr width=100%>
          <td>
						<span align=left style="font-size: small;">
						<?php if ($_SESSION['pi_cprj'] != '') : ?>
              <a target="_blank"
                 href="<?= $php_self ?>?m[0]=cbom&m[1]=view&sn=<?= $_SESSION['pi_cprj'] ?>&date=<?= date('Y-m-d'); ?>&m[2]=&btn=Voir+CBOM">
							<span style='color: #646464;'><?= $translator->trans('shopfloor_header.project', [], 'pio') ?>:</span>&nbsp;
                                <?= $_SESSION['pi_cprj'] ?>
                            </a>
              &nbsp;&nbsp;
            <?php endif; ?>
                <?php if ($_SESSION['pi_sn'] != '') : ?>
                  <a href="<?= $php_self ?>?m[0]=pi&m[1]=form&m[2]=unit">
							<span style='color: #646464;'><?= $translator->trans('shopfloor_header.er', [], 'pio') ?>:</span>&nbsp;
							<?= $_SESSION['pi_sn'] ?>&nbsp;(<?= $_SESSION['pi_family'] ?>)
							</a>
                <?php endif; ?>

                <?php if ($_SESSION['sol_status'] !== 'IN_PROGRESS' && $_SESSION['sol_status'] != '' && ($_SESSION['pi_opno'] == '990' || $_SESSION['pi_opno'] == '995')) : ?>
                  <span style='color: #FF0000; font-weight: bold;'>&nbsp;&nbsp;<?= _('SOL STATUS') ?>:&nbsp;
							<?= $_SESSION['sol_status'] ?>
							</span>
                <?php endif; ?>

						</span>
          </td>
        </tr>
      </table>
    </td>
    <td width=* align=right>
      <table width=100%>
        <tr width=100%>
          <td align=right style="font-size: small;" width=100%>
            <a href="<?= $php_self ?>"><?= $translator->trans('shopfloor_header.go_to_shopfloor', [], 'pio') ?></a>
            &nbsp;|&nbsp;
            <a href="<?= $php_self ?>?m[0]=login&m[1]=logout"><?= $translator->trans('shopfloor_header.disconnect', [], 'pio') ?></a>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr width=100%>
    <td>
			<span align=left style="font-size: small;">
			<?php if ($_SESSION['pi_pdno'] !== '') : ?>
        <span style='color: #646464;'><?= $translator->trans('shopfloor_header.work_order', [], 'pio') ?>:</span>&nbsp;
          <?= $_SESSION['pi_pdno'] ?>&nbsp;&nbsp;(Status: <?= $_SESSION['workOrderStatus'] ?>)
      <?php endif; ?>
          <?php if ($_SESSION['pi_opno'] !== '') : ?>
            <span style='color: #646464;'><?= $translator->trans('shopfloor_header.operation', [], 'pio') ?>:</span>&nbsp;
              <?= $_SESSION['pi_opno'] ?>&nbsp;(<?= $_SESSION['pi_tano_dsca'] ?>)
          <?php endif; ?>
          <?php if (($_SESSION['pi_indirect'] ?? '') !== '') : ?>
            <span style='color: #646464;'><?= $translator->trans('shopfloor_header.task', [], 'pio') ?>:</span>&nbsp;
              <?= $_SESSION['pi_indirect'] . " (" . $_SESSION['pi_tano_dsca'] . ")" ?>
          <?php endif; ?>
          <?php if ($_SESSION['pi_snid'] !== '') : ?>
            <a href="<?= $php_self ?>?m[0]=pi&m[1]=form&m[2]=erFiles"><?= $translator->trans('shopfloor_header.add_file', [], 'pio') ?></a>
          <?php endif; ?>
			</span>
    </td>
    <td width=* align=right>
			<span align=left style="font-size: small;">
			<?php if ($_SESSION['pi_user'] != '') : ?>
        <span style='color: #646464;'><?= $translator->trans('shopfloor_header.connected_as', [], 'pio') ?>&nbsp;<?= $_SESSION['pi_user'] ?></span>
      <?php else : ?>
        Not connected
      <?php endif; ?>
			</span>
    </td>
  </tr>
</table>

<?php include(__DIR__ . '/../session.tpl.php') ?>

<?php if ($m[2] === 'inspection' && $m[3] === 'display' && $_SESSION['pi_opno'] == '999') : ?>
  <br>
  <table width=100% style='border-radius: 20px; border: 2px solid #000000' bgcolor='#C0C0C0'>
    <tr>
      <td width=100% align=center style='vertical-align:middle; font-size: medium;' height=25px>
        </b><a href=\"<?= $php_self ?>?m[0]=pi&m[1]=form&m[2]=info\"><b>CONFIRM P&I GT</b></a>
      </td>
    </tr>
  </table>
<?php endif; ?>

<br>
<table border=0 width=100%>
  <tr width=100%>
      <?php if ($_SESSION['pi_opno'] !== "") : ?>
        <td width=14%><a class='MnuOperation' href="<?= $php_self ?>?m[0]=pi&m[1]=form&m[2]=operation">
            <table class='TblOperation' width=100% style='border-radius: 20px; border: 0px solid #000000' bgcolor='#C0C0C0'>
              <tr style='color: blue;'>
                <td width=100% align=center style='vertical-align:middle; font-size: <?= $_SESSION['pi_font_size'] ?>;' height=25px><br><b><?= $translator->trans('shopfloor_menu.operation', [], 'pio') ?>&nbsp;<?= $_SESSION['pi_opno'] ?></b><br><br></td>
              </tr>
            </table>
          </a></td>
      <?php else : ?>
        <td width=14%><a class='MnuOperation' href="<?= $php_self ?>?m[0]=pi&m[1]=form&m[2]=operation">
            <table class='TblOperation' width=100% style='border-radius: 20px; border: 0px solid #000000' bgcolor='#C0C0C0'>
              <tr style='color: blue;'>
                <td width=100% align=center style='vertical-align:middle; font-size: <?= $_SESSION['pi_font_size'] ?>;' height=25px><br><b><?= $translator->trans('shopfloor_menu.operation', [], 'pio') ?></b><br><br></td>
              </tr>
            </table>
          </a></td>
      <?php endif; ?>
    <td width=14%><a class='MnuParts' href="<?= $php_self ?>?m[0]=pi&m[1]=form&m[2]=parts">
        <table class='TblParts' width=100% style='border-radius: 20px; border: 0px solid #000000' bgcolor='#C0C0C0'>
          <tr style='color: blue;'>
            <td width=100% align=center style='vertical-align:middle; font-size: <?= $_SESSION['pi_font_size'] ?>;' height=25px><br><b><?= $translator->trans('shopfloor_menu.parts_availability', [], 'pio') ?></b><br><br></td>
          </tr>
        </table>
      </a></td>
    <td width=14%><a class='MnuDocumentation' href="<?= $php_self ?>?m[0]=pi&m[1]=form&m[2]=documentation">
        <table class='TblDocumentation' width=100% style='border-radius: 20px; border: 0px solid #000000' bgcolor='#C0C0C0'>
          <tr style='color: blue;'>
            <td width=100% align=center style='vertical-align:middle; font-size: <?= $_SESSION['pi_font_size'] ?>;'
                height=25px><br><b><?= $translator->trans('shopfloor_menu.documentation', [], 'pio') ?></b><br><br></td>
          </tr>
        </table>
      </a></td>
    <td width=14%><a class='MnuInspect' href="<?= $php_self ?>?m[0]=pi&m[1]=form&m[2]=inspection&m[3]=display">
        <table class='TblInspect' width=100% style='border-radius: 20px; border: 0px solid #000000' bgcolor='#C0C0C0'>
          <tr style='color: blue;'>
            <td width=100% align=center style='vertical-align:middle; font-size: <?= $_SESSION['pi_font_size'] ?>;' height=25px><br><b><?= $translator->trans('shopfloor_menu.inspection', [], 'pio') ?></b><br><br></td>
          </tr>
        </table>
      </a></td>
    <td width=14%><a class='MnuCrabList' href="<?= $php_self ?>?m[0]=pi&m[1]=form&m[2]=crabs" style='color: blue;'>
        <table class='TblCrabList' width=100% style='border-radius: 20px; border: 0px solid #000000' bgcolor='#C0C0C0'>
          <tr style='color: white;'>
            <td width=100% align=center style='vertical-align:middle; font-size: <?= $_SESSION['pi_font_size'] ?>;'  height=25px><br><b><?= $translator->trans('shopfloor_menu.crab_list', [], 'pio') ?></b><br><br></td>
          </tr>
        </table>
      </a></td>
    <td width=14%><a class='MnuCrab' href="<?= $php_self ?>?m[0]=pi&m[1]=form&m[2]=crab" target="_blank"
                     style='color: red;'>
        <table class='TblCrab' width=100% style='border-radius: 20px; border: 0px solid #000000' bgcolor='#0000FF'>
          <tr style='color: white;'>
            <td width=100% align=center style='vertical-align:middle; font-size: <?= $_SESSION['pi_font_size'] ?>;' height=25px><br><b><?= $translator->trans('shopfloor_menu.new_crab', [], 'pio') ?></b><br><br></td>
          </tr>
        </table>
      </a></td>
      <?php if (($_SESSION['pi_sitm'] ?? '') !== '') : ?>
        <td width=14%><a class='MnuNCR' href="<?= $php_self ?>?m[0]=pi&m[1]=form&m[2]=ncr" style='color: red;'>
            <table class='TblNCR' width=100% style='border-radius: 20px; border: 0px solid #000000' bgcolor='#0000FF'>
              <tr style='color: white;'>
                <td width=100% align=center style='vertical-align:middle; font-size: <?= $_SESSION['pi_font_size'] ?>;' height=25px><br><b><?= _('NCR') ?></b><br><br></td>
              </tr>
            </table>
          </a></td>
      <?php else : ?>
        <td width=14%><a class='MnuNCR' style='color: red;'>
            <table class='TblNCR' width=100% style='border-radius: 20px; border: 0px solid #000000' bgcolor='#0000FF'>
              <tr style='color: white;'>
                <td width=100% align=center style='vertical-align:middle; font-size: <?= $_SESSION['pi_font_size'] ?>;' height=25px><br><b><?= _('NCR') ?></b><br><br></td>
              </tr>
            </table>
          </a></td>
      <?php endif; ?>
      <?php if ($_SESSION['pi_indirect_allowed'] === 'Y') : ?>
        <td width=14% align=center style='vertical-align:middle;'>
          <a href="/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=indirectTransact&m[3]=display"><img src='//www.tld-gse.com/shared/icons/application/Clock.png' width=60></a></td>
      <?php endif; ?>
  </tr>
</table>
