<?php

declare(strict_types=1);
$_TITLE .= " \ "._('Reports');
$_MENU .= '';

switch ($m[1] ?? null) {
    case 'byBuDepartment':
        $form = new tldMatrix(
            tldDMS::countByBuDepartmentByConstraints($a ?? ''),
            'bu', 'dpt', 'num',
            "$php_self?m[0]=listing&m[1]=byBuDepartment",
            _('DMS Count by Business Unit, Department'),
            ['doNotShowTotals' => true]
        );
        $_BODY .= $form->fetch();
        break;
    default:
        $_BODY = include "$_PATH/reports.home.tpl.php";
        break;
}
