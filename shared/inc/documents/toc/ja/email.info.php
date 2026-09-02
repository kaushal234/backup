<?php

/**
 * FOOTER email used by <?=$sso['name']?>TOC::constructEmailFooter()
 */

ob_start();
?>
<!-- SECTION DETAILS - TOC -->
<div style="page-break-inside: avoid">
    <div class="item_info_title"><p><b>Your <?=$sso['name']?> On Call</b></p></div>
    <table>
        <tr>
            <td class="item_info_table_label"><?=$sso['name']?> On Call reference (TOC)</td>
            <td class="item_info_table_value"><?=  mb_convert_encoding($tocHeader['id'], 'UTF-8', mb_list_encodings()); ?></td>
        </tr>
        <tr>
            <td class="item_info_table_label">Customer Name</td>
            <td class="item_info_table_value"><?=  mb_convert_encoding($tocHeader['customer_name'], 'UTF-8', mb_list_encodings()); ?></td>
        </tr>
        <tr>
            <td class="item_info_table_label">Customer Contact Name</td>
            <td class="item_info_table_value"><?=  mb_convert_encoding($exHeader['fullname'], 'UTF-8', mb_list_encodings()); ?></td>
        </tr>
        <tr>
            <td class="item_info_table_label">Customer Contact Email</td>
            <td class="item_info_table_value"><?=  mb_convert_encoding($exHeader['email'], 'UTF-8', mb_list_encodings()); ?></td>
        </tr>
        <tr>
            <td class="item_info_table_label">Customer Contact Phone</td>
            <td class="item_info_table_value"><?= mb_convert_encoding($exHeader['phone'], 'UTF-8', mb_list_encodings()); ?></td>
        </tr>
        <tr>
            <td class="item_info_table_label">Short Problem Description</td>
            <td class="item_info_table_value"><?=  mb_convert_encoding($tocHeader['short_desc'], 'UTF-8', mb_list_encodings()); ?></td>
        </tr>
    </table>
</div>
<br>
<!-- SECTION DETAILS - ER -->
<?php  if(!empty($erHeader)) : ?>
    <div style="page-break-inside: avoid">
        <div class="item_info_title"><p><b>Your Equipment</b></p></div>
        <table>
            <tr>
                <td class="item_info_table_label">Type</td>
                <td class="item_info_table_value"><?= mb_convert_encoding($erHeader['type'], 'UTF-8', mb_list_encodings()); ?></td>
            </tr>
            <tr>
                <td class="item_info_table_label">Model</td>
                <td class="item_info_table_value"><?=  mb_convert_encoding($erHeader['model'], 'UTF-8', mb_list_encodings()); ?></td>
            </tr>
            <tr>
                <td class="item_info_table_label">Equipment SN#</td>
                <td class="item_info_table_value"><?=  mb_convert_encoding($erHeader['sn'], 'UTF-8', mb_list_encodings()); ?></td>
            </tr>
            <tr>
                <td class="item_info_table_label">Customer Asset#</td>
                <td class="item_info_table_value"><?= mb_convert_encoding($erHeader['cust_asset_num'], 'UTF-8', mb_list_encodings()); ?></td>
            </tr>
            <tr>
                <td class="item_info_table_label">Airport code</td>
                <td class="item_info_table_value"><?= mb_convert_encoding($erHeader['apc'], 'UTF-8', mb_list_encodings()); ?></td>
            </tr>
            <tr>
                <td class="item_info_table_label">Operation hours</td>
                <td class="item_info_table_value"><?= mb_convert_encoding($erHeader['hours'], 'UTF-8', mb_list_encodings()); ?></td>
            </tr>
        </table>
    </div>
    <br>
<?php  endif; ?>
<!-- SECTION DETAILS - CRT -->
<div style="page-break-inside: avoid">
    <div class="item_info_title"><p><b>Your <?=$sso['name']?> Contacts</b></p></div>
    <table width="100%" cellpadding="10">
        <tr>
            <td align="center" bgcolor="#ffffff" width="20%">
                <div style="background:#f7f7f7; border:1px #d0d0d0 solid; padding:6px;" class="vcard">
                    <p class="item_reps_title">Sales Contact</p>
                    <?php   if($uSales->isValid()): ?>
                        <p><?= mb_convert_encoding($uSales->getFullname(), 'UTF-8', mb_list_encodings()); ?><br/><?= mb_convert_encoding($uSales->getTitle(), 'UTF-8', mb_list_encodings()); ?><br/><a href="mailto:<?= mb_convert_encoding($uSales->getEmail(), 'UTF-8', mb_list_encodings()); ?>"><?= mb_convert_encoding($uSales->getEmail(), 'UTF-8', mb_list_encodings()); ?></a><br/><?= mb_convert_encoding($uSales->itsDetails['direct_phone'], 'UTF-8', mb_list_encodings()); ?></p>
                    <?php   else: ?>
                        <p>No contact assigned yet</p>
                    <?php   endif; ?>
                </div>
            </td>
            <td align="center" bgcolor="#FFFFFF" width="20%">
                <div style="background:#f7f7f7; border:1px #d0d0d0 solid; padding:6px;" class="vcard">
                    <p class="item_reps_title">Service Contact</p>
                    <?php   if($uService->isValid()): ?>
                        <p><?= mb_convert_encoding($uService->getFullname(), 'UTF-8', mb_list_encodings()); ?><br/><?= mb_convert_encoding($uService->getTitle(), 'UTF-8', mb_list_encodings()); ?><br/><a href="mailto:<?= mb_convert_encoding($uService->getEmail(), 'UTF-8', mb_list_encodings()); ?>"><?= mb_convert_encoding($uService->getEmail(), 'UTF-8', mb_list_encodings()); ?></a><br/><?= mb_convert_encoding($uService->itsDetails['direct_phone'], 'UTF-8', mb_list_encodings()); ?></p>
                    <?php   else: ?>
                        <p>No contact assigned yet</p>
                    <?php   endif; ?>
                </div>
            </td>
            <td align="center" bgcolor="#FFFFFF" width="20%">
                <div style="background:#f7f7f7; border:1px #d0d0d0 solid; padding:6px;" class="vcard">
                    <p class="item_reps_title">Spare Parts Contact</p>
                    <?php   if($uParts->isValid()): ?>
                        <p><?= mb_convert_encoding($uParts->getFullname(), 'UTF-8', mb_list_encodings()); ?><br/><?= mb_convert_encoding($uParts->getTitle(), 'UTF-8', mb_list_encodings()); ?><br/><a href="mailto:<?= mb_convert_encoding($uParts->getEmail(), 'UTF-8', mb_list_encodings()); ?>"><?= mb_convert_encoding($uParts->getEmail(), 'UTF-8', mb_list_encodings()); ?></a><br/><?= mb_convert_encoding($uParts->itsDetails['direct_phone'], 'UTF-8', mb_list_encodings()); ?></p>
                    <?php   else: ?>
                        <p>No contact assigned yet</p>
                    <?php   endif; ?>
                </div>
            </td>
        </tr>
    </table>
    <?php
    return ob_get_clean();
    ?>
