<?php

include_once("finance.inc.php");
include_once("common.inc.php");
include_once("sales_service.inc.php");

// MFG module
$id = $_GET['id'];
$margin = new tldMargin($id);
$data = $margin->itsHeader;
$factory_details = $margin->getMargins($data['sol_id'])
?>

<?php if (is_string($data)): ?>
    <div>
        <h3>Error while fetching database</h3>
    </div>
<?php elseif (!empty($data)): ?>
<div class="row">
    <div class="col-md-6">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>SOL Details</h5>
                <div class="ibox-tools">
                    <a class="collapse-link">
                        <i class="fa fa-chevron-up"></i>
                    </a>
                </div>
            </div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover association-table">
                        <tbody>
                        <tr>
                            <th>SOL#</th>
                            <td><a href="/en/private/sales_service/sales.php?m[0]=sol&m[1]=view&id=<?= $data['sol_id'] ?>"><?= $data['sol_id'] ?></a></td>
                        </tr>
                        <tr>
                            <th>Customer</th>
                            <td><?= $data['buyer'] ?></td>
                        </tr>
                        <tr>
                            <th>Country</th>
                            <td><?= $data['country'] ?></td>
                        </tr>
                        <tr>
                            <th>SSO</th>
                            <td><?= $data['sso_fullname'] ?></td>
                        </tr>
                        <tr>
                            <th>Factory</th>
                            <td><?= $data['erp_fullname'] ?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Margin Calculations</h5>
                <div class="ibox-tools">
                    <a class="collapse-link">
                        <i class="fa fa-chevron-up"></i>
                    </a>
                </div>
            </div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover association-table">
                        <tbody>
                        <tr>
                            <th>Factory Discount (%)</th>
                            <td><?= $factory_details['discf_pc'] ?></td>
                        </tr>
                        <tr>
                            <th>Actual Direct Margin</th>
                            <td><?= $factory_details['dir_margin'] ?></td>
                        </tr>
                        <tr>
                            <th>Actual Direct Margin (%)</th>
                            <td><?= $factory_details['dir_margin_per'] ?></td>
                        </tr>
                        <tr>
                            <th>Standard Direct Margin (%)</th>
                            <td><?= $factory_details['std_dir_margin_per'] ?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
    <div>
        <h3>No records...</h3>
    </div>
<?php endif; ?>
