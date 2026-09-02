<?php
ob_start();
?>

<form method="post" id="frm">
    <input type="hidden" name="m[0]" value="scm" />
    <input type="hidden" name="m[1]" value="view" />
    <input type="hidden" name="m[2]" value="er" />
    <input type="hidden" name="m[3]" value="hourmeterQuickEdit" />
    <input type="hidden" name="frmTrigger" value="1" />
    <input type="hidden" name="id" value="<?= $id ?>" />
    <input type="hidden" name="erp" value="<?= $erp ?>" />
    <table cellpadding="2" class="tld_table sortable">
      <thead>
      	<tr>
          <th>ER#</th>
          <th>SN#</th>
          <th>Customer ER Type</th>
          <th>Customer Asset#</th>
          <th>Last known hourmeter</th>
          <th>Hourmeter review</th>
          <th>Operation status</th>
        </tr>
      </thead>
      <tbody>
        <?php  foreach($erList as $er): ?>
        <tr>
          <td><a href="/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view&id=<?= $er['id'] ?>"><?= $er['id'] ?></a></td>
          <td><?= $er['sn'] ?></td>
          <td><?= $er['cust_equipment_type'] ?></td>
          <td><?= $er['cust_asset_num'] ?></td>
          <td><?= $er['hours'] ?></td>
          <td><input type="text" name="hours[<?= $er['id'] ?>]" value=""/></td>
          <td><?= $er['operation_status'] ?></td>
        </tr>
        <?php  endforeach; ?>
      </tbody>
	</table>
	<p align="right"><input type="submit" name="btnSubmit" value="Submit" /></p>
</form>

<?php
return ob_get_clean();
