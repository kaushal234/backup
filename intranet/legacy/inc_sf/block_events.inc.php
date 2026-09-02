<?php
include_once("common.inc.php");
$user = new tldUser($_SERVER["PHP_AUTH_USER"]);
$companies = tldUtils::getSqlToAssocArray("SELECT DISTINCT company FROM cal_events WHERE date BETWEEN curdate() AND DATE_ADD(curdate(),INTERVAL 30 DAY) ORDER BY company");

foreach ($companies as $company) : ?>
    <?php $result = tldUtils::getSqlToAssocArray("SELECT * FROM cal_events WHERE company='{$company["company"]}' AND date BETWEEN curdate() AND DATE_ADD(curdate(),INTERVAL 30 DAY) ORDER BY date,company"); ?>
    <h4><?= $company["company"] ?></h4>
    <?php foreach ($result as $row) : ?>
        <p class="col-xs-6"><?= $row["date"]; ?><?php if ($row["end"] <> "0000-00-00") echo " to " . $row["end"]; ?></p>
        <p class="col-xs-6"><?= htmlentities(stripslashes($row["description"])) ?></p>
    <?php endforeach; ?>
<?php endforeach; ?>
    <a href="calendar/calendar.php?m[0]=events" class="btn btn-primary btn-xs m-t-xs">More...</a>
<?php
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);
if ($user->isInGroup("holidays") || $user->isInGroupLevel("holidays", 0)) : ?>
    <a href="calendar/events/holidays_admin.php" class="btn btn-warning btn-xs m-t-xs"><span class="fa fa-pencil-alt"></span>&nbsp;Edit Holidays</a>
<?php endif; ?>
