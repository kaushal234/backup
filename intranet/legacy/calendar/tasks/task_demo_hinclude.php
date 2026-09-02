<?php
include_once("common.inc.php");

$erId = tldDatabase::escape($_GET['er_id']);
// Find tasks linked to the ER when the demo was ACTIVE
$query= <<<SQL

SELECT
    *,
    (SELECT CONCAT(UPPER(lastname),', ',firstname) FROM people p WHERE p.id=t.assignee) AS assignee_fullname,
    (SELECT CONCAT(UPPER(lastname),', ',firstname) FROM people p WHERE p.id=t.assignor) AS assignor_fullname
FROM tasks t
WHERE module = 'ER' AND parent_id = $erId
ORDER BY id DESC;
SQL;

$startDate = $_GET['startDate'];
$endDate = $_GET['endDate'];
$tasks = tldUtils::getSqlToAssocArray($query);

if ($startDate !== ''){
    if ($endDate !== ''){
        $tasks = array_filter($tasks, static function ($task) use ($startDate, $endDate){
            return $task['date']>$startDate && $task['date']<$endDate;
        });
    } else {
        $tasks = array_filter($tasks, static function ($task) use ($startDate) {
            return $task['date'] > $startDate;
        });
    }
}
?>

<?php if (null != $tasks): ?>
    <div class="table-responsive">
        <table class="footable table table-hover toggle-arrow-tiny report-table">
            <thead>
            <tr>
                <th>Task</th>
                <th>Assignor</th>
                <th>Assignee</th>
                <th>Description</th>
                <th>Status</th>
                <th>Due Date</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($tasks as $key => $task): ?>
                <tr>
                    <td><a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=<?= $task['id'] ?>"><?= $task['id'] ?></a></td>
                    <td><?= mb_convert_encoding($task['assignor_fullname'], 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8') ?></td>
                    <td><?= mb_convert_encoding($task['assignee_fullname'], 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8') ?></td>
                    <td><?= mb_convert_encoding($task['task'], 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8') ?></td>
                    <td><?= $task['status'] ?></td>
                    <td><?= $task['due_date'] ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div>
        <h3>No records...</h3>
    </div>
<?php endif; ?>
