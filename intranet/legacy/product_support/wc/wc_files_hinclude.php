<?php
include_once("sales_service.inc.php");

// WC module
$id = $_GET['warrantyClaimId'];
$vendorWarrantyClaimId = $_GET['vendorWarrantyClaimId'];
$query= <<<SQL
SELECT wf.* FROM warranty_files wf
WHERE wf.parent_id = $id
SQL;
$files = tldUtils::getSqlToAssocArray($query);
?>
<?php if (is_string($files)): ?>
    <div>
        <h3>Error while fetching database</h3>
    </div>
<?php elseif (!empty($files)): ?>
    <div class="table-responsive">
        <table class="footable table table-hover toggle-arrow-tiny report-table">
            <thead>
            <tr>
                <th>ID#</th>
                <th>Filename</th>
                <th>Uploaded At</th>
                <th>Description</th>
                <th>Public</th>
                <th>Change Visibility</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($files as $key => $file): ?>
                <tr>
                    <td><a href="/en/private/product_support/index.ps.php?m[0]=wc&m[1]=view&m[2]=files&m[3]=outFile&id=<?=$id ?>&fileid=<?=$file['id'] ?>"><?= $file['id'] ?></a></td>
                    <td><?= $file['filename'] ?></td>
                    <td><?= $file['date'] ?></td>
                    <td><?= $file['description'] ?></td>
                    <td><span class="d-none"><?= $file['public'] ?></span><?php if ($file['public']): ?><i class="fa fa-check text-info"></i><?php else: ?><i class="fa fa-times text-danger"><?php endif?></td>
                    <td><a href="/en/private/product_support/index.ps.php?m[0]=wc&m[1]=view&m[2]=files&m[3]=changeVisibility&id=<?=$id ?>&file_id=<?=$file['id'] ?>&vendorWarrantyClaimId=<?=$vendorWarrantyClaimId ?>"><span class="btn btn-warning btn-xs"><i class="fa fa-edit fa-fw"></i></span></a></td>
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
