<?php
include_once("common.inc.php");

// Report Open SEQ eCustomer
$id = $_GET['id'];
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
tplno IN (42, 71) AND
seq='Y' AND
parent_id=$id
ORDER BY id DESC;
SQL;

$sequence = tldUtils::getSqlToAssocArray($query);

$types = [
    '42' => 'CREATION',
    '71' => 'VALIDATION',
];

?>

<?php if (null != $sequence): ?>
    <div class="table-responsive">
        <table class="footable table table-hover toggle-arrow-tiny report-table">
            <thead>
            <tr>
                <th>SEQ</th>
                <th>Customer Name</th>
                <th>Type</th>
                <th>Current Step</th>
                <th>Due Date</th>
                <th>Week Overdue</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($sequence as $key => $seq): ?>
                <tr>
                    <td><a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=<?= $seq['id'] ?>"><?= $seq['id'] ?></a></td>
                    <td><?= $seq['customer_name'] ?></td>
                    <td><?= $types[$seq['tplno']]  ?></td>
                    <td><?= $seq['cur_step'] ?></td>
                    <td><?= $seq['due_date'] ?></td>
                    <td><?= $seq['wks_overdue'] ?></td>
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
