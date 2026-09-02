<?php
include_once("common.inc.php");

//Init fin_period with year and month period names
for($y = 2000; $y <= 2030; $y++){
    for($m = 1; $m <= 12; $m++){
        $query = "INSERT INTO fin_periods set nam_period=$y".str_pad($m, 2, '0', STR_PAD_LEFT);
        echo "$query\n";
        //tldUtils::sqlInsert($query);
    }
}
?>
