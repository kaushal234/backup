<?php
ob_start();
?>

<h2><?= sprintf(_("BOM for %s from company %s as of %s"),$sess['bom']->itsID,$sess['bom']->itsERP,$sess['bom']->itsDate) ?></h2>

<p>
<?php
if(!empty($sess['cbom'])):
?>
	SN:<a href="<?= $php_self ?>?m[0]=cbom&m[1]=view&sn=<?= $sess['bom']->itsID ?>&date=<?= $sess['bom']->itsDate ?>"><?= $sess['bom']->itsID ?></a>&nbsp;
<?php
endif;
foreach($sess['history'] as $key=>$val):
	if($key==0):
?>
	<a href="<?= $php_self ?>?m[0]=bom&m[1]=view&history=<?= $key ?>"><?= $val ?></a>
<?php
	else:
?>
	&nbsp;>>&nbsp;<a href="<?= $php_self ?>?m[0]=bom&m[1]=view&history=<?= $key ?>"><?= $val ?></a>
<?php
	endif;
endforeach; ?>
</p>

<table>
<tr>
	<th><?= _("Part Number") ?></th>
	<th><?= _("Position") ?></th>
	<th><?= _("Description (en)") ?></th>
	<th><?= _("Description (alt)") ?></th>
	<th><?= _("Qty") ?></th>
	<th><?= _("UM") ?></th>
	<th><?= _("Status") ?></th>
	<th><?= _("Drawing") ?></th>
	<th><?= _("Rev") ?></th>
	<th><?= _("Code") ?></th>
	<th><?= _("Ext") ?></th>
	<th><?= _("Box#") ?></th>
	<?php if(!empty($sess['er']['id'])):?>
	<th>CRAB</th>
	<?php endif;?>
	<th><?= _("EAP Flag") ?></th>
    <th><?= _("Warehouse") ?></th>
    <th><?= _("Backflush") ?></th>
    <th><?= _("Fantome(Y/N)") ?></th>
</tr>
<?php  foreach($sess['bom']->itsBOMAsArray as $line): ?>
<tr>
	<td>
	    <a href="<?= $php_self ?>?m[0]=bom&m[1]=view&pn=<?= $line['t_sitm'] ?>&date=<?= $sess['bom']->itsDate ?>">
            <?php
                for($i=0;$i< ($line['level'] ?? null);$i++) {
                    echo '&bull;';
                }

                echo '&nbsp;'.$line['t_sitm'];
            ?>
	    </a>
	</td>
	<td>
		<?php  for($i=0; $i < ($line['level'] ?? null); $i++) echo "&nbsp;"; ?>
		<img src="//www.tld-gse.com/shared/branch.gif"> <?= $line['t_pono'] ?>
	</td>
	<td><?= $line['t_dsca']	?></td>
    <td>
        <?= ($sess['bom']->itsERP >= '600' && $line['altdsca'] !== null) ? mb_convert_encoding($line['altdsca'], 'HTML-ENTITIES', 'UTF-8') : $line['altdsca'] ?>
    </td>
	<td><?= $line['t_qana'] ?></td>
	<td><?= $line['t_cuni'] ?></td>
	<td><?php
	if(isset($line['expired']) && !$line['expired']) echo "OK";
	else echo $line['t_exdt'] ?>
	</td>
	<td>
    <!-- revision in the link is used only to invalidate the browser cache when the drawing revision changes. -->
    <a target="_blank" href="<?= $php_self ?>?m[0]=getfile&m[1]=drawing&item=<?= $line['t_sitm'] ?>&revision=<?= $line['t_revi'] ?? '' ?>">
	<img src="/shared/bluesphere/16x16/actions/filesaveas.png" alt="Save file  to your hard disk">
	</a>
	</td>
	<td><?= $line['t_revi'] ?></td>
	<td><?= $line['t_csig_edm'] ?></td>
	<td><?= $line['t_exin'] ?></td>
	<td><?= $line['t_opno'] ?></td>
	<?php if(!empty($sess['er']['id'])):?>
	<td><a href="<?= $php_self?>?m[0]=crab&m[1]=new&pn=<?= $line['t_sitm']?>">CRAB</a></td>
	<?php endif;?>
	<?php if(!empty($line['eap'])):?>
	<td align="center"><a href="<?= "$php_self?m[0]=eap&m[1]=lists&m[2]=byPartNumber&pn={$line['t_sitm']}" ?>" target="_blank"><img src="/shared/icons/signs_symbols/warning.gif" width="18" title="EAP: <?= $line['eap_num']?>"></a></td>
	<?php else:?>
	<td></td>
	<?php endif;?>
    <td><?= $line['t_cwar'] ?></td>
    <td><?= $line['t_bfcp'] ? 'Y' : 'N' ?></td>
    <td><?= $line['t_cpha'] ? 'Y' : 'N' ?></td>
</tr>
<?php  endforeach; ?>
</table>

<?php
return ob_get_clean();
?>
