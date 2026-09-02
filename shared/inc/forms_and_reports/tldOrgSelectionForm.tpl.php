<?php
ob_start();

// Listing
$divisionList = tldDivision::getList();
$subDivisionList = tldSubDivision::getList();
$regionList = tldRegion::getListAsIdDivision();
$buList = tldLocation::getBuListAsIdBU();
$departmentList = tldDepartment::getListAsIdDepartment();
$functionLevelList = tldFunction::getLevelList();
$functionList = tldFunction::getList();
?>

<script type="text/javascript">

    let displayEntityList = function (event) {
        let level;
        let subLevelId;
        let departmentId;
        //hide all subLevel division/subdivision/region/bu Select
        document.querySelectorAll('.alvest-entities-list').forEach(function (e) {
            e.style.display = 'none'
        })

        Object.entries(orgSelectIdFilters).forEach(function([index, element]) {
            // level selected
            if (index === 'level'){
                level = $(element).find(":selected").val();
                //show subLevel concerned by level selected
                document.querySelectorAll('#alvest-' + level + '-cell, #alvest-' + level + '-title').forEach(function (e) {
                    e.style.display = 'table-cell'
                })
            }
            // on which level get subLevel selected
            if (index === level){
                subLevelId = $(element).find(":selected").val() === '' ? null : $(element).find(":selected").val();
            }
            // department selected
            if (index === 'department'){
                departmentId = $(element).find(":selected").val() === undefined ? null : $(element).find(":selected").val();
            }
        });

        // filter position by level/subLevel/department
        $.ajax({
            type:"POST",
            url: "ajax.php?m[0]=getPositionsByDepartment",
            data: {"level" : level, "subLevelId" : subLevelId, "departmentId" : departmentId},
            dataType: 'json',
            success: function(data){
                let idFiltered = data;
                let optionFunctionSelect = $('#function option');
                optionFunctionSelect.hide();
                $(optionFunctionSelect).each(function() {
                    const optionId = $(this).attr("value");
                    if (idFiltered.includes(optionId)) {
                        $(this).show();
                    }
                });
            }
        })

    }

    $(document).ready(function() {
        $(Object.values(orgSelectIdFilters).join(', ')).on('change',displayEntityList);
    });
    let orgSelectIdFilters = {'level' : '#alvest-entity-type', 'division' : '#alvest-division-cell', 'subdivision' : '#alvest-subdivision-cell', 'region' : '#alvest-region-cell', 'bu' : '#alvest-bu-cell', 'department' : '#department'};
    document.addEventListener("DOMContentLoaded", displayEntityList);

</script>

<p style="white-space: nowrap; background-color: #CCCCCC; padding: 1px;"><b><?= $_FORM_OPTIONS['title'] ?></b></p>

<form method="post" action="<?= $_FORM_OPTIONS['link'] ?>" id="frm">
<input type="hidden" name="frmSubmit" value="1" />
<table cellpadding="2" class="tld_table">
  <thead>
    <tr bgcolor="#2971A8">
      <th align="center"><?= _("Selection Level") ?></th>
      <th align="center" class="alvest-entities-list" id="alvest-division-title"><?= _("Division") ?></th>
      <th align="center" class="alvest-entities-list" id="alvest-subdivision-title"><?= _("Subdivision") ?></th>
      <th align="center" class="alvest-entities-list" id="alvest-region-title"><?= _("Region") ?></th>
      <th align="center" class="alvest-entities-list" id="alvest-bu-title"><?= _("Business unit") ?></th>
      <th align="center"><?= _("Department") ?></th>
      <th align="center"><?= _("Alvest position") ?></th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>
        <select name="type" id="alvest-entity-type">
            <option value="division"><?= _("Division") ?></option>
            <option value="subdivision"><?= _("Subdivision") ?></option>
            <option value="region"><?= _("Region") ?></option>
            <option value="bu" selected="selected"><?= _("Business unit") ?></option>
        </select>
      </td>
      <td class="alvest-entities-list" id="alvest-division-cell">
        <select name="division[]">
          <option data-division-value="0"></option>
        <?php  foreach($divisionList as $id=> $name): ?>
          <option class="division" id="division[<?= $id ?>]" value="<?= $id ?>" >
            <?= $name ?>
          </option>
        <?php  endforeach; ?>
        </select>
      </td>
      <td class="alvest-entities-list" id="alvest-subdivision-cell">
        <select name="subdivision[]">
          <option data-subdivision-value="0"></option>
        <?php  foreach($subDivisionList as $id=> $name): ?>
          <option class="subdivision" id="subdivision[<?= $id ?>]" value="<?= $id ?>" >
            <?= $name ?>
          </option>
        <?php  endforeach; ?>
        </select>
      </td>
      <td class="alvest-entities-list" id="alvest-region-cell">
        <select name="region[]">
          <option data-region-value="0"></option>
        <?php  foreach($regionList as $id=> $name): ?>
          <option class="region" id="region[<?= $id ?>]" value="<?= $id ?>" >
            <?= $name ?>
          </option>
        <?php  endforeach; ?>
        </select>
      </td>
      <td class="alvest-entities-list" id="alvest-bu-cell">
        <select name="bu[]" id="bu">
          <option></option>
        <?php  foreach($buList as $id=>$name): ?>
          <option class="bu" id="bu[<?= $id ?>]" value="<?= $id ?>"><?= $name ?></option>
        <?php  endforeach; ?>
        </select>
      </td>
      <td>
        <select name="department[]" size="10" id="department">
                <option data-department-value="0"></option>
        <?php  foreach($departmentList as $depID=>$DepartmentName): ?>
          <option class="department" id="department[<?= $depID ?>]" value="<?= $depID ?>"><?= $DepartmentName ?></option>
        <?php  endforeach; ?>
        </select>
      </td>
      <td>
        <select name="function[]" multiple="multiple" size="10" id="function">
        <?php  foreach($functionLevelList as $k=>$level): ?>
          <optgroup class="functionGrp" id="functionGrp[<?= $level ?>]"
          ondblClick="$(this).children().attr('selected','selected');"
          label="<?= $level ?>">
          <?php  foreach($functionList as $function): ?>
            <?php  if($function['level']!=$level) continue; ?>
            <option class="function" id="function[<?= $function['id'] ?>]" value="<?= $function['id'] ?>">
              <?= $function['dsc'] ?> (<?= $function['code'] ?>)
            </option>
          <?php  endforeach; ?>
          </optgroup>
        <?php  endforeach; ?>
        </select>
      </td>
    </tr>
    <tr>
      <td colspan="3"></td>
      <td>
        <p align="right">
          <input type="submit" value="<?= _("Submit") ?>" />
        </p>
      </td>
    </tr>
  </tbody>
</table>
</form>

<?php
return ob_get_clean();
