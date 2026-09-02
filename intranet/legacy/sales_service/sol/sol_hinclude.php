<?php
include_once("sales_service.inc.php");

// SOL module
$id = $_GET['id'];
$sols = tldSOL::byAllCustomerID($id, ["limit" => 5, 'orderBy' => 'dt_opened DESC']);
?>
<?php if (is_string($sols)): ?>
    <div>
        <h3>Error while fetching database</h3>
    </div>
<?php elseif (!empty($sols)): ?>
    <div class="table-responsive">
        <table class="footable table table-hover toggle-arrow-tiny report-table">
            <thead>
            <tr>
                <th>SOR</th>
                <th>SOL</th>
                <th>Date opened</th>
                <th>SSO</th>
                <th>Status</th>
                <th>Factory</th>
                <th>Model</th>
                <th>Quantity Ordered</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($sols as $key => $sol): ?>
                <tr>
                    <td><a href="/en/private/sales_service/sales.php?m[0]=sor&m[1]=view&id=<?= $sol['parent_id'] ?>"><?= $sol['parent_id'] ?></a></td>
                    <td><a href="/en/private/sales_service/sales.php?m[0]=sol&m[1]=view&id=<?= $sol['id'] ?>"><?= $sol['id'] ?></a></td>
                    <td><?= $sol['dt_opened'] ?></td>
                    <td><?= $sol['sso_fullname'] ?></td>
                    <td><?= $sol['status'] ?></td>
                    <td><?= $sol['erp_fullname'] ?></td>
                    <td><?= $sol['model'] ?></td>
                    <td><?= $sol['qty_sou'] ?></td>
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