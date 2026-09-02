<?php
include_once("common.inc.php");

$module = tldDatabase::escape($_GET['module']);
$parentId = tldDatabase::escape($_GET['parent_id']);

$links = array_merge(tldModLink::byParent($parentId, $module), tldModLink::byItem($parentId, $module));

array(
    "xItems"=>array(
        "id"	=>"ID#",
        "type"	=>"Module",
        "item"	=>"Ref#",
        "dsca"  =>"Description"
    ),
    "title"=>"Links",
    "links"=>array("id"=>"/en/private/common/index.php?m[0]=links&m[1]=view&erp=$erp&id=")
);

?>

<?php if (null != $links): ?>
    <div class="table-responsive report-table-with-filter">
        <table class="footable table table-hover toggle-arrow-tiny report-table" data-filter=#filter data-page-size=20>
            <thead>
            <tr>
                <th>Link #ID</th>
                <th>Module</th>
                <th>Ref</th>
                <th>Description</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($links as $key => $link): ?>
                <tr>
                    <td><a href="/en/private/common/index.php?m[0]=links&m[1]=view&erp=&id=<?= $link['id'] ?>"><?= $link['id'] ?></a></td>
                    <td><?= $link['type'] !== $module ? $link['type'] : $link['module'] ?></td>
                    <td><a href="<?=  ($link['type'] === $module && $link['item'] === $parentId)  ? tldModLink::getURL($link['module'], $link['parent_id']) : tldModLink::getURL($link['type'], $link['item']) ?>"><?= ($link['type'] === $module && $link['item'] === $parentId) ? $link['parent_id'] : $link['item'] ?></a></td>
                    <td><?= mb_convert_encoding($link['dsca'], 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8') ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="4">
                    <p class="text-end">Total number of records: <strong><?= count($links) ?></strong></p>
                    <ul class="hide-if-no-paging pagination float-end"></ul>
                </td>
            </tr>
            </tfoot>
        </table>
    </div>
<?php else: ?>
    <div>
        <h3>No records...</h3>
    </div>
<?php endif; ?>
