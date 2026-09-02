<?php
include_once("sales_service.inc.php");

// TOC module
$id = $_GET['id'];
$startDate = $_GET['startDate'];
$endDate = $_GET['endDate'];

$tocs = tldTOC::byConstraints(["erid"=>$id],["limit"=>5, 'orderBy'=>'id DESC']);

if ($startDate !== ''){
    if ($endDate !== ''){
        $tocs = array_filter($tocs, static function ($toc) use ($startDate, $endDate){
            return $toc['dt']>$startDate && $toc['dt']<$endDate;
        });
    } else {
        $tocs = array_filter($tocs, static function ($toc) use ($startDate) {
            return $toc['dt'] > $startDate;
        });
    }
}

?>
<?php if (is_string($tocs)): ?>
    <div>
        <h3>Error while fetching database</h3>
    </div>
<?php elseif (!empty($tocs)): ?>
    <div class="table-responsive">
        <table class="footable table table-hover toggle-arrow-tiny report-table">
            <thead>
            <tr>
                <th>ID#</th>
                <th>Date opened</th>
                <th>Short Description</th>
                <th>Status</th>
                <th>IF</th>
                <th>Unit Status</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($tocs as $key => $toc): ?>
                <tr>
                    <td><a href="/en/private/sales_service/service.php?m[0]=toc&m[1]=view&id=<?=$toc['id'] ?>"><?= $toc['id'] ?></a></td>
                    <td><?= $toc['dt_open'] ?></td>
                    <td><?= $toc['short_desc'] ?></td>
                    <td><?= $toc['status'] ?></td>
                    <td><?= $toc['ifactor'] ?></td>
                    <td><?= $toc['unit_operation_status'] ?></td>
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
