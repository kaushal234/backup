<div class="container">
    <div id="TabContainer">
        <?php if ($sfrListing != null) { ?>
            <h2 class="text-center">SFRs</h2>
            <table class="table table-striped tablebleble">
                <thead>
                <tr>
                    <th class="primary">SFR#</th>
                    <th class="primary">Model</th>
                    <th class="primary">Qty</th>
                    <th class="primary">Pur/TLD%</th>
                    <th class="primary">Status</th>
                    <th class="primary">Est Date</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($sfrListing as $sfr): ?>
                    <tr class='clickable-row-sfr-toc-odp' data-module='sfr' data-url='<?= $PHPSELF ?>' data-id='<?= $sfr['id'] ?>'>
                        <td><?= $sfr['id'] ?></td>
                        <td><?= $sfr['product']['name'] ?></td>
                        <td><?= $sfr['quantity'] ?></td>
                        <td><?= $sfr['customerSuccessPercentage'] ?>/<?= $sfr['successPercentage'] ?></td>
                        <td><?= $sfr['status'] ?></td>
                        <td><?= $estimatedDate = (new Datetime($sfr['estimatedSaleDate']))->format('Y-m'); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php } ?>
        <?php if ($tocListing != null) { ?>
            <h2 class="text-center">TOCs</h2>
            <table class="table table-striped tablebleble">
                <thead>
                <tr>
                    <th class="primary">TOC#</th>
                    <th class="primary">Status</th>
                    <th class="primary">Unit Status</th>
                    <th class="primary">Model</th>
                    <th class="primary">Airport Code</th>
                    <th class="primary">Short Problem Description</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($tocListing as $toc):?>
                    <tr class='clickable-row-sfr-toc-odp' data-module='toc' data-url='<?= $PHPSELF ?>' data-id='<?= $toc['id'] ?>'>
                        <td><?= $toc['id'] ?></td>
                        <td><?= $toc['status'] ?></td>
                        <td><?= $toc['unit_operation_status'] ?></td>
                        <td><?= $toc['model'] ?></td>
                        <td><?= $toc['apc'] ?></td>
                        <td><?= $toc['short_desc'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php } ?>
        <?php if ($deliveriesListing != null) { ?>
            <h2 class="text-center">Deliveries</h2>
            <table class="table table-striped tablebleble scrollable">
                <thead>
                <tr>
                    <th class="primary">Factory</th>
                    <th class="primary">Buyer</th>
                    <th class="primary">Equipment Type</th>
                    <th class="primary">Unit SN</th>
                    <th class="primary">Requested Delivery Date</th>
                    <th class="primary">Promised Delivery Date</th>
                    <th class="primary">Estimated GT Date</th>
                    <th class="primary">Actual GT Date</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($deliveriesListing as $delivery):?>
                    <tr class='clickable-row-sfr-toc-odp' data-module='odp' data-url='<?= $PHPSELF ?>' data-id='<?= $delivery['id'] ?>'>
                        <td><?= $delivery['manufacturerLocation']['name'] ?></td>
                        <td><?= $delivery['buyer']['name'] ?></td>
                        <td><?= $delivery['model'] ?></td>
                        <td><?= $delivery['serialNumber'] ?></td>
                        <td><?= $requestedDeliveryDate = isset($delivery['orderFactory']['requestedDeliveryDate']) ? (new DateTime($delivery['orderFactory']['requestedDeliveryDate']))->format('Y-m-d') : null ?></td>
                        <td><?= $promisedDeliveryDate = isset($delivery['orderFactory']['factoryPromisedDeliveryDate']) ? (new DateTime($delivery['orderFactory']['factoryPromisedDeliveryDate']))->format('Y-m-d') : null ?></td>
                        <td><?= $estimatedGreenTagDate = isset($delivery['estimatedGreenTagDate']) ? (new DateTime($delivery['estimatedGreenTagDate']))->format('Y-m-d') : null ?></td>
                        <td><?= $greenTagDate = isset($delivery['greenTagDate']) ? (new DateTime($delivery['greenTagDate']))->format('Y-m-d') : null ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php } ?>
    </div>
</div>
<!-- Modal -->
<div class="modal fade bottom-left" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"></h4>
            </div>
            <div class="modal-body">
                <form method="post" id="link-sfr-toc-odp" action="">
                    <input type="hidden" name="" class="hiddenId" value=""/>
                    <button type="submit" class="btn btn-primary btn-lg btn-block">View</button>
                </form>
            </div>
        </div>
    </div>
</div>
