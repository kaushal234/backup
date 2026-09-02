<?php
include_once("common.inc.php");

$options = [];
foreach (['module', 'ssd'] as $option) {
    if ('' !== ($value = tldDatabase::escape($_GET[$option]))) {
        $options[$option] = $value;
    }
}

$andWhere = '';
if (isset($_GET['assignees'])) {
    $andWhere = sprintf('AND assignee IN (%s)', implode(', ', $_GET['assignees']));
    unset($options['assignee']);
}

$or = '';
if (isset($options['ssd'])) {
    $or = sprintf('AND (%s)', tldUtils::constructWhere(['assignee' => $options['ssd'], 'assignee.reports_to' => $options['ssd']], 'OR'));
    unset($options['ssd']);
}

$where = tldUtils::constructWhere($options);

// Report module tasks
$query= <<<SQL
SELECT
    t.*,
    (CONCAT(UPPER(assignee.lastname),', ',assignee.firstname)) AS assignee_fullname,
    (CONCAT(UPPER(assignor.lastname),', ',assignor.firstname)) AS assignor_fullname
FROM tasks t
LEFT JOIN people assignee ON t.assignee = assignee.id
LEFT JOIN people assignor ON t.assignee = assignor.id
WHERE $where AND (t.status = 'OPEN' OR t.status='IN PROGRESS') $andWhere $or
ORDER BY t.id DESC;
SQL;

$tasks = tldUtils::getSqlToAssocArray($query);
?>

<?php if (null != $tasks): ?>
    <div class="table-responsive">
        <table class="footable table table-hover toggle-arrow-tiny report-table" data-filter=#filter data-page-size=20>
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
