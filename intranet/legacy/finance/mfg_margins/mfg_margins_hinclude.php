<?php
include_once("finance.inc.php");
include_once("common.inc.php");

// MFG module
$rows = tldMargin::getLatest();
?>

<?php if (is_string($rows)): ?>
    <div>
        <h3>Error while fetching database</h3>
    </div>
<?php elseif (!empty($rows)): ?>
    <div class="table-responsive">
        <table class="footable table table-hover toggle-arrow-tiny report-table">
            <thead>
            <tr>
                <th>Legacy ID#</th>
                <th>ER SN</th>
                <th>SOL#</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Country</th>
                <th>SSO</th>
                <th>Factory</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><a href="/en/private/finance/finance.php?m[0]=mfg_margins&m[1]=view&id=<?= $row['id'] ?>"><?= $row['id'] ?></a></td>
                    <td><a href="/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=search&m[2]=bySN&sn=<?= $row['er_sn'] ?>"><?= $row['er_sn'] ?></a></td>
                    <td><a href="/en/private/sales_service/sales.php?m[0]=sol&m[1]=view&id=<?= $row['sol_id'] ?>"><?= $row['sol_id'] ?></a></td>
                    <td><?= $row['date'] ?></td>
                    <td><?= $row['buyer'] ?></td>
                    <td><?= $row['country'] ?></td>
                    <td><?= $row['sso_fullname'] ?></td>
                    <td><?= $row['erp_fullname'] ?></td>
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
