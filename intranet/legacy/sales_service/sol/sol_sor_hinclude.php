<?php
include_once("sales_service.inc.php");

// SOL module
$id = $_GET['id'];
$sols = tldSOL::byParent($id);

?>

<?php if (is_string($sols)): ?>
    <div>
        <h3>Error while fetching database</h3>
    </div>
<?php elseif (!empty($sols)): ?>
    <div class="input-group search-table">
        <div class="input-group-text"><i class="fa fa-search"></i></div>
        <input type="text" class="form-control" id="filter-sols" placeholder="Search in table...">
    </div>
    <div class="table-responsive">
        <table class="footable table table-hover toggle-arrow-tiny report-table" data-page-size="9999999" data-filter="#filter-sols">
            <thead>
                <tr>
                    <th>Line ID#</th>
                    <th>Status</th>
                    <th>SSO PO# to Factory</th>
                    <th>Factory SO#</th>
                    <th>Model</th>
                    <th>Qty</th>
                    <th>Qty Assigned</th>
                    <th>Qty Shipped</th>
                    <th>Batch Qty</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sols as $key => $sol): ?>
                    <td><a href="/en/private/sales_service/sales.php?m[0]=sol&m[1]=view&id=<?= $sol['id'] ?>"><?= $sol['id'] ?></a></td>
                    <td><?= $sol['status'] ?></td>
                    <td><?= $sol['sls_orno'] ?></td>
                    <td><?= $sol['erp_orno'] ?></td>
                    <td><?= $sol['model'] ?></td>
                    <td><?= $sol['qty_sou'] ?></td>
                    <td><?= $sol['qty_alloc'] ?></td>
                    <td><?= $sol['qty_ship'] ?></td>
                    <td><?= $sol['batch_quantity'] ?></td>
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
