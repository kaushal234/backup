<?php
ob_start();

$rows = [];
$linkFrom = tldModLink::byParent($ncr['id'], 'NCR');
if (count($linkFrom)) {
    foreach ($linkFrom as $link) {
        $rows[] = [
            'id'         => $link['id'],
            'module'     => $link['type'],
            'module_ref' => $link['item'],
            'dsca'       => $link['dsca'],
        ];
    }
}
$linkTo = tldModLink::byItem($ncr['id'], 'NCR');
if (count($linkFrom)) {
    foreach ($linkTo as $link) {
        $rows[] = [
            'id'         => $link['id'],
            'module'     => $link['module'],
            'module_ref' => $link['parent_id'],
            'dsca'       => $link['dsca'],
        ];
    }
}

?>

<h3><?= _('Module Linked') ?></h3>

<?php  if(empty($rows)): ?>
<p><?= _('No module linked yet'); ?></p>
<?php  else: ?>
<div class="columnar">
    <table class="sortable" cellpadding="3">
        <thead>
            <tr>
                <th title="Sort by Module"><?= _('Module') ?></th>
                <th title="Sort by Ref#"><?= _('Ref#') ?></th>
                <th title="Sort by Description"><?= _('Description') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($rows as $k=>$link):
                $color = $k%1==0 ? '#eeeeee' : '#d0d0d0';
            ?>
            <tr bgcolor="<?= $color ?>">
                <td><?= $link['module'] ?></td>
                <td><?= _getLink($link['module'], $link['module_ref']) ?></td>
                <td><?= $link['dsca'] ?></td>
            </tr>
            <?php  endforeach; ?>
        </tbody>
        <tfoot></tfoot>
    </table>
</div>
<?php  endif; ?>

<?php

function _getLink($module,$moduleRef){
    global $php_self;
    $moduleList = array(
        'ER'=>"$php_self?m[0]=er&m[1]=view&id=",
        'EAP'=>"$php_self?m[0]=eap&m[1]=view&id=",
        'CRAB'=>"$php_self?m[0]=crab&m[1]=view&id=",
        'NCR'=>"$php_self?m[0]=ncr&m[1]=view&id=",
    );
    // if not supported, send id only
    if(!in_array($module, array_keys($moduleList))) return $moduleRef;
    // else the link
    return '<a href="'.$moduleList[$module].$moduleRef.'">'.$moduleRef.'</a>';
}


return ob_get_clean();
?>
