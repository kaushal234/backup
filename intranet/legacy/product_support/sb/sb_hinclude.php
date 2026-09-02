<?php
include_once("sales_service.inc.php");

// TOC module
$id = $_GET['id'];
$query= <<<SQL
SELECT sb.* FROM sb
LEFT JOIN sb_lines ON sb_lines.parent_id = sb.id
WHERE sb_lines.er_id = $id
GROUP BY sb.id
ORDER BY sb.id DESC
LIMIT 5;
SQL;

$sbs = tldUtils::getSqlToAssocArray($query);
?>
<?php if (is_string($sbs)): ?>
    <div>
        <h3>Error while fetching database</h3>
    </div>
<?php elseif (!empty($sbs)): ?>
    <div class="table-responsive">
        <table class="footable table table-hover toggle-arrow-tiny report-table">
            <thead>
            <tr>
                <th>ID#</th>
                <th>Date opened</th>
                <th>Title</th>
                <th>Description</th>
                <th>Status</th>
                <th>IF</th>
                <th>Category</th>
                <th>Type</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($sbs as $key => $sb): ?>
                <tr>
                    <td><a href="/en/private/product_support/index.php?m[0]=sb&m[1]=view&id=<?=$sb['id'] ?>"><?= $sb['id'] ?></a></td>
                    <td><?= $sb['dt'] ?></td>
                    <td><?= $sb['title'] ?></td>
                    <td><?= $sb['description'] ?></td>
                    <td><?= $sb['status'] ?></td>
                    <td><?= $sb['ifactor'] ?></td>
                    <td><?= $sb['category'] ?></td>
                    <td><?= $sb['type'] ?></td>
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
