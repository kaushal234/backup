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
    $(".TblCrabList").css("border", "2px solid #000000");
</script>

<table border=0 width=100%>
  <thead>
  <tr style="background:#2971a8; color:white;">
    <td width=5% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;">
      <b><?= _('CRAB') ?></b></td>
    <td width=5% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;">
      <b><?= _('Description') ?></b></td>
    <td width=5% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;">
      <b><?= _('Operation') ?></b></td>
    <td width=10% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;">
      <b><?= _('Description') ?></b></td>
    <td width=10% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;">
      <b><?= _('Item') ?></b></td>
    <td width=15% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;">
      <b><?= _('Description') ?></b></td>
    <td width=5% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;">
      <b><?= _('Question') ?></b></td>
    <td width=15% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;">
      <b><?= _('Subject') ?></b></td>
    <td width=15% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;">
      <b><?= _('Description') ?></b></td>
    <td width=5% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;">
      <b><?= _('User') ?></b></td>
    <td width=10% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;">
      <b><?= _('Creation date') ?></b></td>
    <td width=10% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;">
      <b><?= _('Fix date') ?></b></td>
    <td width=10% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;">
      <b><?= _('Inspect date') ?></b></td>
    <td width=5% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;">
      <b><?= _('Status') ?></b></td>
    <td width=5% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;">
      <b><?= _('Pictures') ?></b></td>
  </tr>
  </thead>
  <tbody>
  <?php foreach ($crabs as $key1 => $crab): ?>
      <?php $color = ($key1 % 2) ? "#eeeeee" : "#d0d0d0"; ?>
    <tr style="background:<?= $color ?>">
      <td style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><a target="_blank" href="/shop/autoselect.php?_qf__frmER=&m[0]=crab&m[1]=view&id=<?= $crab['parent_id'] ?>"><?= $crab['parent_id'] ?></a></td>
      <td style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><?= $crab['descCrab'] ?></td>
      <td style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><?= $crab['t_opno'] ?></td>
      <td style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><?= $crab['t_dsca'] ?? null ?></td>
      <td style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><?= $crab['t_item'] ?></td>
      <td style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><?= $crab['t_dscb'] ?></td>
      <td style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><?= $crab['question_id'] ?></td>
      <td style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><?= $crab['subject'] ?></td>
      <td style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><?= $crab['description'] ?></td>
      <td style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><?= $crab['user_id'] ?></td>
      <td style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><?= $crab['dt'] ?></td>
      <td style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><?= $crab['fix_dt'] ?></td>
      <td style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><?= $crab['insp_dt'] ?></td>
        <?php if ($crab['status'] === 'TO-FIX' || $crab['status'] === 'PENDING') : ?>
          <td style="font-size: <?= $_SESSION['pi_font_size'] ?>;">
            <a target="_blank"
               href="/shop/autoselect.php?m[0]=er&m[1]=view&m[2]=crabs&m[3]=fixed&id=<?= $_SESSION['pi_snid'] ?>&crabid=<?= $crab['parent_id'] ?>"><?= $crab['status'] ?></a>
          </td>
        <?php else : ?>
          <td style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><?= $crab['status'] ?></td>
        <?php endif; ?>
      <td style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><?= $crab['images'] ?? null ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>


<?php
return ob_get_clean();
?>
