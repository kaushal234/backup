<?php
include_once("common.inc.php");

// Report Open SEQ eCustomer
$query = <<<SQL
SELECT
*,
(SELECT CONCAT(UPPER(lastname),', ',firstname) FROM people p WHERE p.id=t.assignee) AS assignee_fullname
FROM
tasks t
WHERE
status<>'CLOSED' AND
tplno=56
AND
seq='Y'
ORDER BY
id DESC;
SQL;


$sequences = tldUtils::getSqlToAssocArray($query);
foreach($sequences AS &$sequence){
    $cp = unserialize(base64_decode($sequence['close_params']));
    foreach($cp AS $k => $v){
        $sequence["cp_{$k}"] = $v;
    }
}
?>

<?php if (null != $sequences): ?>
    <div class="table-responsive report-table-with-filter">
        <input type="text" class="form-control input-sm m-b-xs" id="filter" placeholder="Search in table...">
        <table class="footable table table-hover toggle-arrow-tiny report-table" data-filter=#filter data-page-size=20>
            <thead>
            <tr>
                <th>SEQ</th>
                <th>Date Opened</th>
                <th>Assignee</th>
                <th>Current Step</th>
                <th>XU Name</th>
                <th>XU Company</th>
                <th>XU Email</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($sequences as $seq): ?>
                <tr>
                    <td><a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=<?= $seq['id'] ?>"><?= $seq['id'] ?></a></td>
                    <td><?= $seq['date'] ?></td>
                    <td><?= htmlspecialchars($seq['assignee_fullname']) ?></td>
                    <td><?= $seq['cur_step'] ?></td>
                    <td><?= mb_convert_encoding($seq['cp_firstname']. " ".$seq['cp_lastname'], 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8')  ?></td>
                    <td><?= mb_convert_encoding($seq['cp_company_name'], 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8') ?></td>
                    <td><?= $seq['cp_email'] ?></td>
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
