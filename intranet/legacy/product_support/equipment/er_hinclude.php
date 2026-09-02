<?php
include_once("product_support.inc.php");

// ER module
$id = $_GET['id'];
$er_constraints = "(buyer_customer_id=$id OR customer_id=$id) AND date_shipped<>'0000-00-00'";
$ers = tldEquipment::byConstraints($er_constraints, ["limit" => 5, "orderBy" =>"date_shipped DESC"]);

?>

<?php if (is_string($ers)): ?>
    <div>
        <h3>Error while fetching database</h3>
    </div>
<?php elseif (!empty($ers)): ?>
    <div class="table-responsive">
        <table class="footable table table-hover toggle-arrow-tiny report-table">
            <thead>
            <tr>
                <th>SN</th>
                <th>Factory</th>
                <th>User Customer</th>
                <th>Buyer Customer</th>
                <th>Country, Delivery</th>
                <th>Model</th>
                <th>Airport Code</th>
                <th>Ship Date</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($ers as $key => $er): ?>
                <tr>
                    <td><a href="/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view&id=<?= $er['id'] ?>"><?= $er['sn'] ?></a></td>
                    <td><?= $er['man_location'] ?></td>
                    <td><?= $er['user_customer_display'] ?></td>
                    <td><?= $er['buyer_customer_display'] ?></td>
                    <td><?= $er['del_ctry'] ?></td>
                    <td><?= $er['model'] ?></td>
                    <td><?= $er['airport_code'] ?></td>
                    <td><?= $er['date_shipped'] ?></td>
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