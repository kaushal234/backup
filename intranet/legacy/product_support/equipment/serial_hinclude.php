<?php

include_once("common.inc.php");

$equipmentRecordId = tldDatabase::escape($_GET['id']);
$equipmentRecord = new tldEquipment($equipmentRecordId);
?>

<?php if ($equipmentRecord->hasPreAssemblyER()): ?>
    <?php foreach ($equipmentRecord->getCombinationErList('PRE-ASSEMBLY') as $erVal): ?>
        <div class="row">
            <div class="col-md-12">
                <div>
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5>Component Serial Numbers for PAS #<?= $erVal['id']?></h5>
                            <div class="ibox-tools">
                                <a class="collapse-link">
                                    <i class="fa fa-chevron-up"></i>
                                </a>
                            </div>
                        </div>
                        <div class="ibox-content">
                            <div class="table-responsive report-table-with-filter">
                                <table class="footable table table-hover toggle-arrow-tiny report-table"
                                       data-filter=#filter data-page-size=20>
                                    <thead>
                                    <tr>
                                        <th>Component</th>
                                        <th>Model</th>
                                        <th>Serial</th>
                                        <th>Brand</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $pas = new tldEquipment($erVal['id']); ?>
                                    <?php $serials = $pas->getSerials() ?>
                                    <?php foreach ($serials as $serial): ?>
                                        <tr>
                                            <td><?= $serial['component'] ?></td>
                                            <td><?= $serial['model'] ?></td>
                                            <td><?= $serial['serial'] ?></td>
                                            <td><?= $serial['brand'] ?></td>
                                        </tr>
                                    <?php endforeach ?>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach ?>
<?php endif; ?>