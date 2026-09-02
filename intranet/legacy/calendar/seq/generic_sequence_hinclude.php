<?php
include_once("common.inc.php");


$module = tldDatabase::escape($_GET['module']);
$parentId = tldDatabase::escape($_GET['parent_id']);
// Report module sequences
$query= <<<SQL

SELECT
    *,
    (SELECT CONCAT(UPPER(lastname),', ',firstname) FROM people p WHERE p.id=t.assignee) AS assignee_fullname,
    (SELECT CONCAT(UPPER(lastname),', ',firstname) FROM people p WHERE p.id=t.assignor) AS assignor_fullname
FROM tasks t
WHERE module = '$module' AND parent_id = $parentId
ORDER BY id DESC;
SQL;

$sequences = tldUtils::getSqlToAssocArray($query);
?>

<?php if (null != $sequences): ?>
    <div class="table-responsive">
        <table class="footable table table-hover toggle-arrow-tiny report-table" data-filter=#filter data-page-size=20>
            <thead>
            <tr>
                <th>Sequence</th>
                <th>Assignor</th>
                <th>Assignee</th>
                <th>Description</th>
                <th>Status</th>
                <th>Due Date</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($sequences as $key => $sequence): ?>
                <tr>
                    <td><a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=<?= $sequence['id'] ?>"><?= $sequence['id'] ?></a></td>
                    <td><?= mb_convert_encoding($sequence['assignor_fullname'], 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8') ?></td>
                    <td><?= mb_convert_encoding($sequence['assignee_fullname'], 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8') ?></td>
                    <td><?= mb_convert_encoding($sequence['task'], 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8') ?></td>
                    <td><?= $sequence['status'] ?></td>
                    <td><?= $sequence['due_date'] ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="12">
                    <p class="text-end">Total number of records: <strong><?= count($sequences) ?></strong></p>
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
