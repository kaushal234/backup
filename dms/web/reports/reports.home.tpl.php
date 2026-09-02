<?php declare(strict_types=1);
ob_start();
?>

<h3>DMS Reports</h3>

<h4>Columnar Reports</h4>

<ul>
    <li>No columnar yet</li>
</ul>

<h4>Matrix Reports</h4>

<ul>
    <li><a href="<?php echo "$php_self?m[0]=reports&m[1]=byBuDepartment"; ?>">By Business Unit / Department</a></li>
</ul>

<?php
$_BUFF = ob_get_contents();
ob_end_clean();

return $_BUFF;
?>