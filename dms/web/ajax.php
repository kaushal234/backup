<?php

declare(strict_types=1);
include_once 'common.inc.php';
include_once 'dms.inc.php';
include_once 'user.inc.php';

switch ($m[0] ?? null) {
    case 'getPeriodicityByDmsTypeID':
        $typeList = tldDMSType::getList();
        $typeListAsIdCycle = tldUtils::optionsByKeyValue($typeList, 'id', 'cycle');
        echo $typeListAsIdCycle[$_POST['id']];
        break;
    case 'getPositionsByDepartment':
        $functionListByDepartment = tldFunction::getFunctionListByLevelSubLevelAndDepartment($_POST['level'], '' !== $_POST['subLevelId'] ? (int) $_POST['subLevelId'] : null, '' !== $_POST['departmentId'] ? (int) $_POST['departmentId'] : null);
        echo $functionListByDepartment;
        break;
}
