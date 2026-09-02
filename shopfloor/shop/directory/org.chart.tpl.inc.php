<?php
ob_start();
?>

<h3><?= $ORG_CHART[$erp]['title'] ?></h3>

<?php  foreach($userOrgChart as $key=>$person): ?>
<table>
<?php  if($person['level']<2): ?>
  <tr>
    <td>&nbsp;</td>
    <td>
      <h<?= $person['level']+2 ?>>
      <?= $person['user']['division'] ?>
      </h<?= $person['level']+2 ?>>
    </td>
  </tr>
<?php
endif;
if($person['level']==2):
?>
  <tr>
    <td>&nbsp;</td>
    <td>
      <h<?= $person['level']+2 ?>>
      <?= $person['user']['department'] ?>
      </h<?= $person['level']+2 ?>>
    </td>
  </tr>
<?php  endif; ?>
  <tr>
    <td width="<?= $person['level']*64 ?>">&nbsp;</td>
    <td>
      <a href="<?= "$php_self?m[0]=directory&m[1]=card&id=".$person['user']['id'] ?>">
        <img src="<?php
        if($person['user']['photo']==""): echo "/shared/no_photo.jpg";
        else: echo "$php_self?m[0]=directory&m[1]=card&m[2]=outPhoto&width=64&id=".$person['user']['id'];
        endif; ?>" alt="<?= $person['user']['firstname']." ".$person['user']['lastname'] ?>"
        width="48" border="1"  align="left">
        <b><?= $person['user']['firstname']." ".$person['user']['lastname'] ?></b><br>
        <?= $person['user']['title'] ?><br>
        <?= $person['user']['department'] ?>
      </a>
    </td>
  </tr>
</table>
<?php  endforeach; ?>

<h4>Email List</h4>

<p>
<?php
foreach($userOrgChart as $key=>$person):
	echo $person['user']['email'].", ";
endforeach;
?>
</p>

<?php
return ob_get_clean();
?>
