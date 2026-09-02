<?php
include_once("sales_service.inc.php");

// TOC module
$id = $_GET['id'];
$query= <<<SQL
SELECT warranty.* FROM warranty
LEFT JOIN service ON service.id = warranty.parent_id
WHERE service.id = $id
ORDER BY warranty.id DESC
LIMIT 5;
SQL;
$wcs = tldUtils::getSqlToAssocArray($query);
?>
<?php if (is_string($wcs)): ?>
    <div>
        <h3>Error while fetching database</h3>
    </div>
<?php elseif (!empty($wcs)): ?>
    <div class="table-responsive">
        <table class="footable table table-hover toggle-arrow-tiny report-table">
            <thead>
            <tr>
                <th>ID#</th>
                <th>Claim Date</th>
                <th>Status</th>
                <th>Customer</th>
                <th>Problem Description</th>
                <th>Type</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($wcs as $key => $wc): ?>
                <tr>
                    <td><a href="/en/private/product_support/index.ps.php?m[0]=wc&m[1]=view&id=<?=$wc['id'] ?>"><?= $wc['id'] ?></a></td>
                    <td><?= $wc['claim_date'] ?></td>
                    <td><?= $wc['warranty_status'] ?></td>
                    <td><?= $wc['customer_name'] ?></td>
                    <td><?= $wc['problem_desc'] ?></td>
                    <td><?= $wc['type'] ?></td>
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
