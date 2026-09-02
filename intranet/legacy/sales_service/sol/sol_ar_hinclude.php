<?php
include_once("sales_service.inc.php");

// AR module
$id = $_GET['id'];
$sols = tldSOL::byParent($id);
$esrs = [];
$ers = [];
foreach ($sols as $sol) {
    $units = tldSORUnit::byParent($sol['id']);

    foreach ($units as $unit) {
        if (isset($unit['esrid'])) {
            $esr = new tldESR($unit['esrid']);
            $esrs['esrid'] = $esr->getHeader();
            $esrs['esrid']['lines'] = $esr->getLines();

            $er = new tldEquipment($unit['erid']);
            $ers[] = $er->getHeader();
        }
    }

}

$arch = new tldArchive($_GET['erp']);
$rows = $arch->byTypeID('SALES INVOICE', $_GET['invoice']);

?>

<?php if (is_string($sols)): ?>
    <div>
        <h3>Error while fetching database</h3>
    </div>
<?php elseif (!empty($sols)): ?>
    <div class="row">
        <div class="col-md-6">
            <?php foreach ($rows as $invoice): ?>
                <a class="btn btn-warning" href="/en/private/strs_pdf/archive/<?= $invoice['filepath'] ?>" target="_blank"><i class="fa fa-download"></i>&nbsp;Download Invoice <?= $_GET['invoice'] ?> </a>
            <?php endforeach ?>
        </div>
    </div>
    <hr>

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>SOL Linked</h5>
                    <div class="ibox-tools"><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></div>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="footable table table-hover toggle-arrow-tiny report-table" data-page-size="9999999">
                            <thead>
                            <tr>
                                <th>SOL ID#</th>
                                <th>Status</th>
                                <th>Model</th>
                                <th>Payment Terms</th>
                                <th>Incoterm</th>
                                <th>Qty</th>
                                <th>Qty Assigned</th>
                                <th>Qty Shipped</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($sols as $key => $sol): ?>
                                <tr>
                                    <td><a href="/en/private/sales_service/sales.php?m[0]=sol&m[1]=view&id=<?= $sol['id'] ?>"><?= $sol['id'] ?></a></td>
                                    <td><?= $sol['status'] ?></td>
                                    <td><?= $sol['model'] ?></td>
                                    <td><?= $sol['tpay'] ?></td>
                                    <td><?= $sol['inco'] ?></td>
                                    <td><?= $sol['qty_sou'] ?></td>
                                    <td><?= $sol['qty_alloc'] ?></td>
                                    <td><?= $sol['qty_ship'] ?></td>
                                </tr>
                            <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>ESR Linked</h5>
                    <div class="ibox-tools"><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></div>
                </div>
                <div class="ibox-content">
                    <?php if (!empty($esrs)): ?>
                        <?php foreach ($esrs as $esr): ?>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover association-table" data-page-size="9999999" >
                                            <tbody>
                                                <tr>
                                                    <th>ESR ID#</th>
                                                    <td><a href="/en/private/sales_service/sales.php?m[0]=esr&m[1]=view&id=<?= $esr['id'] ?>"><?= $esr['id'] ?></a></td>
                                                </tr>
                                                <tr>
                                                    <th>Status</th>
                                                    <td><?= $esr['status'] ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Incoterm</th>
                                                    <td><?= $esr['inco'] ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Date Opened</th>
                                                    <td><?= $esr['dt_open'] ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="table-responsive">
                                        <table class="footable table table-hover toggle-arrow-tiny report-table" data-page-size="9999999">
                                            <thead>
                                            <tr>
                                                <th>ER#</th>
                                                <th>Model</th>
                                                <th>SOL Incoterm</th>
                                                <th>ESR Incoterm</th>
                                                <th>Shipped At</th>
                                                <th>Vessel loading date</th>
                                                <th>Estimated date of arrival</th>
                                                <th>Actual date of arrival</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($esr['lines'] as $esrl): ?>
                                                <tr>
                                                    <td><a href="/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view&id=<?= $esrl['erid'] ?>"><?= $esrl['equip_sn'] ?></a></td>
                                                    <td><?= $esrl['model'] ?></td>
                                                    <td><?= $esrl['sol_inco'] ?></td>
                                                    <td><?= $esrl['esr_inco'] ?></td>
                                                    <td><?= $esrl['er_dt_shipped'] ?></td>
                                                    <td><?= $esrl['dt_shipped'] ?></td>
                                                    <td><?= $esrl['dt_estimated'] ?></td>
                                                    <td><?= $esrl['dt_arrived'] ?></td>
                                                </tr>
                                            <?php endforeach ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach ?>
                    <?php else: ?>
                        <div>
                            <h3>No ESR...</h3>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>ER Linked</h5>
                    <div class="ibox-tools"><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></div>
                </div>
                <div class="ibox-content">
                    <?php if (!empty($ers)): ?>
                        <div class="table-responsive">
                            <table class="footable table table-hover toggle-arrow-tiny report-table" data-page-size="9999999">
                                <thead>
                                <tr>
                                    <th>ER#</th>
                                    <th>Model</th>
                                    <th>Commissioning Date</th>
                                    <th>Actual GT Date</th>
                                    <th>First GT Date</th>
                                    <th>Description</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($ers as $er): ?>
                                    <tr>
                                        <td><a href="/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view&id=<?= $er['id'] ?>"><?= $er['sn'] ?></a></td>
                                        <td><?= $er['model'] ?></td>
                                        <td><?= $er['dt_commissioned'] ?></td>
                                        <td><?= $er['dgt_act'] ?></td>
                                        <td><?= $er['dgt_com'] ?></td>
                                        <td><?= mb_convert_encoding($er['options_desc'], 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8') ?></td>
                                    </tr>
                                <?php endforeach ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div>
                            <h3>No ER...</h3>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div>
        <h3>No SOL...</h3>
    </div>
<?php endif; ?>
