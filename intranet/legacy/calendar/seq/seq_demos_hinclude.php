<?php
include_once("common.inc.php");

// Report Open SEQ Demos
$query = <<<SQL
SELECT
*,
(SELECT CONCAT(UPPER(lastname),', ',firstname) FROM people p WHERE p.id=t.assignee) AS assignee_fullname
FROM
tasks t
WHERE
status<>'CLOSED' AND
tplno=75
AND
seq='Y'
ORDER BY
id DESC;
SQL;


$sequences = tldUtils::getSqlToAssocArray($query);
foreach($sequences AS &$sequence){
    $params = unserialize(base64_decode($sequence['close_params']));
    unset($params['assignee'], $params['assignor']);
    $cp = array_map('htmlspecialchars', $params);
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
                <th>Customer</th>
                <th>Product</th>
                <th>SSO</th>
                <th>ASM</th>
                <th>Country</th>
                <th>Current Step</th>

            </tr>
            </thead>
            <tbody>
            <?php foreach ($sequences as $seq): ?>
                <tr>
                    <td><a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=<?= $seq['id'] ?>"><?= $seq['id'] ?></a></td>
                    <td><?= $seq['date'] ?></td>
                    <td><?= mb_convert_encoding($seq['assignee_fullname'], 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8') ?></td>
                    <td><?= mb_convert_encoding($seq['cp_customer'], 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8')  ?></td>
                    <td><?= $seq['cp_product'] ?></td>
                    <td><?= $seq['cp_sso'] ?></td>
                    <td><?= mb_convert_encoding($seq['cp_asm'], 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8') ?></td>
                    <td><?= $seq['cp_country'] ?></td>
                    <td><?= $seq['cur_step'] ?></td>
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
