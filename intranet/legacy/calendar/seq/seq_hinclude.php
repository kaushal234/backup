<?php
include_once("common.inc.php");

// Report Open SEQ eCustomer Approvals
$query= <<<SQL
    SELECT
    *,
    (SELECT customer_name FROM customers c WHERE c.id=t.parent_id) AS customer_name,
    (SELECT CONCAT(UPPER(lastname),', ',firstname) FROM people p WHERE p.id=t.assignee) AS assignee_fullname,
(SELECT CONCAT(UPPER(lastname),', ',firstname) FROM people p WHERE p.id=t.assignor) AS assignor_fullname,
IF(t.due_date<NOW(), ROUND(DATEDIFF(NOW(), t.due_date)/7), 0) as wks_overdue
FROM
tasks t
WHERE
status='OPEN' AND
tplno=42 AND
seq='Y'
ORDER BY
id DESC;
SQL;

$sequences = tldUtils::getSqlToAssocArray($query);
?>

<?php if (null != $sequences): ?>
    <div class="table-responsive report-table-with-filter">
        <input type="text" class="form-control input-sm m-b-xs" id="filter" placeholder="Search in table...">
        <table class="footable table table-hover toggle-arrow-tiny report-table" data-filter=#filter data-page-size=20>
            <thead>
            <tr>
                <th>SEQ</th>
                <th>Customer Name</th>
                <th>Assignor</th>
                <th>Current Assignee</th>
                <th>Current Step</th>
                <th>Due Date</th>
                <th>Week Overdue</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($sequences as $key => $seq): ?>
                <tr>
                    <td><a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=<?= $seq['id'] ?>"><?= $seq['id'] ?></a></td>
                    <td><?= mb_convert_encoding($seq['customer_name'], 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8') ?></td>
                    <td><?= mb_convert_encoding($seq['assignor_fullname'], 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8') ?></td>
                    <td><?= mb_convert_encoding($seq['assignee_fullname'], 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8') ?></td>
                    <td><?= $seq['cur_step'] ?></td>
                    <td><?= $seq['due_date'] ?></td>
                    <td><?= $seq['wks_overdue'] ?></td>
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
