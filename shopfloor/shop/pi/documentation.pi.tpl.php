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
    $(".TblDocumentation").css("background-color", "#EEEEEE");
    $(".TblDocumentation").css("border", "2px solid #000000");
</script>


<table border=0 width=100%>
  <tr width=100%>
    <td width=21%><a class='MnuInstructions'
                     href="<?= $php_self ?>?m[0]=pi&m[1]=form&m[2]=documentation&m[3]=instructions">
        <table class='TblInstructions' width=100% style='border-radius: 20px; border: 0px solid #000000'
               bgcolor='#C0C0C0'>
          <tr style='color: blue;'>
            <td width=100% align=center style='vertical-align:middle; font-size: <?= $_SESSION['pi_font_size'] ?>;'
                height=25px><br><b><?= _('Assembly drawings') ?></b><br><br></td>
          </tr>
        </table>
      </a></td>
    <td width=5%>&nbsp;</td>
    <td width=21%><a class='MnuSchemes' href="<?= $php_self ?>?m[0]=pi&m[1]=form&m[2]=documentation&m[3]=schemes">
        <table class='TblSchemes' width=100% style='border-radius: 20px; border: 0px solid #000000' bgcolor='#C0C0C0'>
          <tr style='color: blue;'>
            <td width=100% align=center style='vertical-align:middle; font-size: <?= $_SESSION['pi_font_size'] ?>;'
                height=25px><br><b><?= _('Schemes & Progs') ?></b><br><br></td>
          </tr>
        </table>
      </a></td>
    <td width=5%>&nbsp;</td>
    <td width=21%><a class='MnuDocList' href="<?= $php_self ?>?m[0]=pi&m[1]=form&m[2]=documentation&m[3]=docList">
        <table class='TblDocList' width=100% style='border-radius: 20px; border: 0px solid #000000' bgcolor='#C0C0C0'>
          <tr style='color: blue;'>
            <td width=100% align=center style='vertical-align:middle; font-size: <?= $_SESSION['pi_font_size'] ?>;'
                height=25px><br><b><?= _('DOC LIST') ?></b><br><br></td>
          </tr>
        </table>
      </a></td>
    <td width=5%>&nbsp;</td>
    <td width=21%><a class='MnuOptions' href="<?= $php_self ?>?m[0]=pi&m[1]=form&m[2]=documentation&m[3]=options">
        <table class='TblOptions' width=100% style='border-radius: 20px; border: 0px solid #000000' bgcolor='#C0C0C0'>
          <tr style='color: blue;'>
            <td width=100% align=center style='vertical-align:middle; font-size: <?= $_SESSION['pi_font_size'] ?>;'
                height=25px><br><b><?= _('OPTIONS') ?></b><br><br></td>
          </tr>
        </table>
      </a></td>
  </tr>
</table>
<br>


<?php if ($_SESSION['pi_instructions'] == 'options') { ?>
  <script type="text/javascript">
      $(".TblOptions").css("background-color", "#EEEEEE");
      $(".TblOptions").css("border", "2px solid #000000");
  </script>


  <table border=0 width=100%>
      <?php foreach ($options as $key => $option): ?>
          <?php $color = ($key % 2) ? "#eeeeee" : "#d0d0d0"; ?>
        <tr width=100%>
          <td width=100%>
            <table width=100% style='border-radius: 20px;' bgcolor='<?= $color ?>'>
              <tr style='color: blue;'>
                <td width=100% align=center style="font-size: <?= $_SESSION['pi_font_size'] ?>;">
                  <br><?php echo($key + 1); ?>&nbsp;-&nbsp;<?= $option['caty'] ?>
                  &nbsp;-&nbsp;<?= $option['dsca'] ?><br><br>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      <?php endforeach; ?>
  </table>
<?php } ?>


<?php if ($_SESSION['pi_instructions'] == 'docList') { ?>
  <script type="text/javascript">
      $(".TblDocList").css("background-color", "#EEEEEE");
      $(".TblDocList").css("border", "2px solid #000000");
  </script>

  <table border=0 width=100%>
      <?php foreach ($documentationList as $key => $documentation): ?>

          <?php $color = ($key % 2) ? "#eeeeee" : "#d0d0d0"; ?>
        <tr width=100%>
          <td width=100%>
            <a class='openDocList' AttrSitm='<?= $documentation['partNumber'] ?>' AttrRevision='<?= $documentation['engineeringRevision'] ?? '' ?>'>
              <table width=100% style='border-radius: 20px;' bgcolor='<?= $color ?>'>
                <tr style='color: blue;'>
                  <td width=100% align=center style="font-size: <?= $_SESSION['pi_font_size'] ?>;">
                    <br><?=
                      ( !empty($documentation['itemOtherDescription']) && $documentation['itemDescription'] !== $documentation['itemOtherDescription'] ) ? $documentation['itemDescription'] . ' (' . $documentation['itemOtherDescription'] . ')' : $documentation['itemDescription'] ?>
                    &nbsp;-&nbsp;<?= $documentation['partNumber'] ?><?= $documentation['engineeringRevision'] ? '_'.$documentation['engineeringRevision'] : '' ?><br><br>
                  </td>
                </tr>
              </table>
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
  </table>
<?php } ?>


<?php if ($_SESSION['pi_instructions'] == 'schemes') { ?>
  <script type="text/javascript">
      $(".TblSchemes").css("background-color", "#EEEEEE");
      $(".TblSchemes").css("border", "2px solid #000000");
  </script>
  <table border=0 width=100%>
      <?php foreach ($schemes as $key => $schematic): ?>
          <?php $color = ($key % 2) ? "#eeeeee" : "#d0d0d0"; ?>
        <tr width=100%>
          <td width=100%>
            <a class='openInstructions' AttrSitm='<?= $schematic['partNumber'] ?>' AttrRevision='<?= $schematic['engineeringRevision'] ?? '' ?>'>
              <table width=100% style='border-radius: 20px;' bgcolor='<?= $color ?>'>
                <tr style='color: blue;'>
                  <td width=100% align=center style="font-size: <?= $_SESSION['pi_font_size'] ?>;">
                    <br><?=
                      ( !empty($schematic['itemOtherDescription']) && $schematic['itemDescription'] !== $schematic['itemOtherDescription'] ) ? $schematic['itemDescription'] . ' (' . $schematic['itemOtherDescription'] . ')' : $schematic['itemDescription'] ?>
                    &nbsp;-&nbsp;<?= $schematic['partNumber'] ?><?= $schematic['engineeringRevision'] ? '_'.$schematic['engineeringRevision'] : '' ?><br><br>
                  </td>
                </tr>
              </table>
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
  </table>
<?php } ?>


<?php if ($_SESSION['pi_instructions'] == 'instructions') { ?>
  <script type="text/javascript">
      $(".TblInstructions").css("background-color", "#EEEEEE");
      $(".TblInstructions").css("border", "2px solid #000000");
  </script>

    <?php if ($_SESSION['pi_opno'] != '') { ?>
    <table border=0 width=100%>
        <?php foreach ($instructions as $key => $instruction): ?>
            <?php $color = ($key % 2) ? "#eeeeee" : "#d0d0d0"; ?>
            <?php if (!empty($instruction['itemSignalCode'])) { ?>
              <tr width=100%>
                <td width=100%>
                  <a class='openInstructions' AttrSitm='<?= $instruction['partNumber'] ?>' AttrRevision='<?= $instruction['engineeringRevision'] ?? '' ?>'>
                    <table width=100% style='border-radius: 20px;' bgcolor='<?= $color ?>'>
                      <tr style='color: blue;'>
                        <td width=100% align=center style="font-size: <?= $_SESSION['pi_font_size'] ?>;">
                          <br>op&nbsp;<?= $instruction['operation'] ?> :&nbsp;<?=
                            ( !empty($instruction['itemOtherDescription']) && $instruction['itemDescription'] !== $instruction['itemOtherDescription'] ) ? $instruction['itemDescription'] . ' (' . $instruction['itemOtherDescription'] . ')' : $instruction['itemDescription'] ?>
                          &nbsp;-&nbsp;<?= $instruction['partNumber']?><?= $instruction['engineeringRevision'] ? '_'.$instruction['engineeringRevision'] : '' ?> <br><br>
                        </td>
                      </tr>
                    </table>
                  </a>
                </td>
              </tr>
            <?php } ?>
        <?php endforeach; ?>
    </table>
    <?php } ?>
<?php } ?>


<script type="text/javascript">

    $(".openDocList, .openInstructions").click(function () {
        varSitm = $(this).attr('AttrSitm');
        varRevision = $(this).attr('AttrRevision');

        // revision in the link is used only to invalidate the browser cache when the drawing revision changes.
        window.open("/shop/autoselect.php?m[0]=getfile&m[1]=PIdrawing&item=" + varSitm + "&revision=" + varRevision);
    });

</script>


<?php
return ob_get_clean();
?>
