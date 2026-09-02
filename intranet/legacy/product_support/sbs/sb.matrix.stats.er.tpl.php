<?php
ob_start();
// Get data
$rows = tldSB::countERBySSO();
$rows2 = tldSB::countERBySSO("sb.urgency LIKE 'SB:COMPULSORY'");
?>

<table width="100%">
  <tr bgcolor="#FFFFFF">
    <td>

    <h3>ER Statistics by SSO, SB in IMPLEMENTATION</h3>

    <table class="form-matrix" cellpadding="5">
      <tbody>
        <tr class="section-head">
          <th>&nbsp;</th>
          <th>Affected</th>
          <th>TBD</th>
          <th>Done</th>
          <th>Not Done</th>
          <th>Done (in %)</th>
        </tr>
      <?php
        foreach($rows as $k=>$row):
        $bgcolor = ($k%2==1) ? "#eeeeee" : "#d0d0d0" ;
      ?>
        <tr class="section-body" bgcolor="<?= $bgcolor ?>">
          <th><?= empty($row['sso']) ? "NO SSO" : $row['sso']; ?></th>
          <td><?= $row['affected'] ?></td>
          <td><?= $row['tbd'] ?></td>
          <td><?= $row['done'] ?></td>
          <td>
            <a href="<?= "$php_self?m[0]=sbs&m[1]=listing&m[2]=byERBySSO&m[3]=not_done&status=IMPLEMENTATION&sso={$row['sso']}" ?>">
              <?= $row['not_done'] ?>
            </a>
          </td>
          <?php
            $notDoneRatio = ($row['tbd']==0) ? 0 : round($row['done']/$row['tbd']*100);
            if($notDoneRatio > 75){
                $color = "green";
            }elseif($notDoneRatio >= 50){
                $color = "orange";
            }elseif($notDoneRatio < 50){
                $color = "red";
            }
          ?>
          <td bgcolor="<?= $color ?>"><?= $notDoneRatio ?></td>
        </tr>
      <?php  endforeach; ?>
      </tbody>
    </table>

    </td>
    <td>

        <h3>ER Statistics by SSO, SB:COMPULSORY in IMPLEMENTATION</h3>

    <table class="form-matrix" cellpadding="5">
      <tbody>
        <tr class="section-head">
          <th>&nbsp;</th>
          <th>Affected</th>
          <th>TBD</th>
          <th>Done</th>
          <th>Not Done</th>
          <th>Done (in %)</th>
        </tr>
      <?php
        foreach($rows2 as $k=>$row):
        $bgcolor = ($k%2==1) ? "#eeeeee" : "#d0d0d0" ;
      ?>
        <tr class="section-body" bgcolor="<?= $bgcolor ?>">
          <th><?= empty($row['sso']) ? "NO SSO" : $row['sso']; ?></th>
          <td><?= $row['affected'] ?></td>
          <td><?= $row['tbd'] ?></td>
          <td><?= $row['done'] ?></td>
          <td>
            <a href="<?= "$php_self?m[0]=sbs&m[1]=listing&m[2]=byERBySSO&m[3]=not_done&status=IMPLEMENTATION&sso={$row['sso']}&urgency=SB:COMPULSORY" ?>">
              <?= $row['not_done'] ?>
            </a>
          </td>
          <?php
            $notDoneRatio = ($row['tbd']==0) ? 0 : round($row['done']/$row['tbd']*100);
            if($notDoneRatio > 75){
                $color = "green";
            }elseif($notDoneRatio > 50){
                $color = "orange";
            }elseif($notDoneRatio < 50){
                $color = "red";
            }
          ?>
          <td bgcolor="<?= $color ?>"><?= $notDoneRatio ?></td>
        </tr>
      <?php  endforeach; ?>
      </tbody>
    </table>

    </td>
  </tr>
</table>

<?php
return ob_get_clean();
