<?php
include_once("common.inc.php");


$module = tldDatabase::escape($_GET['module']);
$parentId = tldDatabase::escape($_GET['parent_id']);
// Report module tasks
$query= <<<SQL

SELECT
    *,
    (SELECT CONCAT(UPPER(lastname),', ',firstname) FROM people p WHERE p.id=t.assignee) AS assignee_fullname,
    (SELECT CONCAT(UPPER(lastname),', ',firstname) FROM people p WHERE p.id=t.assignor) AS assignor_fullname
FROM tasks t
WHERE module = '$module' AND parent_id = $parentId
ORDER BY id DESC;
SQL;

$tasks = tldUtils::getSqlToAssocArray($query);
?>

<?php if (null != $tasks): ?>
    <div class="table-responsive">
        <table class="footable table table-hover toggle-arrow-tiny report-table" data-page-size=20>
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
            <tfoot>
            <tr>
                <td colspan="12">
                    <p class="text-end">Total number of records: <strong><?= count($tasks) ?></strong></p>
                    <ul class="hide-if-no-paging pagination float-end"></ul>
                </td>
            </tr>
            </tfoot>
        </table>
    </div>
<?php else: ?>
    <div>
        <h3>No records...</h3>
    </div>
<?php endif; ?>
