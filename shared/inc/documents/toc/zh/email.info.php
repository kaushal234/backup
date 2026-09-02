<?php

/**
 * FOOTER email used by <?=$sso['name']?>TOC::constructEmailFooter()
 */

ob_start();
?>
                <!-- SECTION DETAILS - TOC -->
                <div style="page-break-inside: avoid">
                <div class="item_info_title"><p><b>&#24744;&#30340;&#38382;&#39064;&#21453;&#39304;&#35760;&#24405;</b></p></div>
                <table>
                  <tr>
                    <td class="item_info_table_label">&#38382;&#39064;&#21453;&#39304;&#35760;&#24405;(TOC)</td>
                    <td class="item_info_table_value"><?=  mb_convert_encoding($tocHeader['id'], 'UTF-8', mb_list_encodings()); ?></td>
                  </tr>
                  <tr>
                    <td class="item_info_table_label">&#23458;&#25143;&#21517;&#31216;</td>
                    <td class="item_info_table_value"><?=  mb_convert_encoding($tocHeader['customer_name'], 'UTF-8', mb_list_encodings()); ?></td>
                  </tr>
                  <tr>
                    <td class="item_info_table_label">&#32852;&#31995;&#20154;</td>
                    <td class="item_info_table_value"><?=  mb_convert_encoding($exHeader['fullname'], 'UTF-8', mb_list_encodings()); ?></td>
                  </tr>
                  <tr>
                    <td class="item_info_table_label">&#32852;&#31995;&#20154;&#37038;&#20214;</td>
                    <td class="item_info_table_value"><?=  mb_convert_encoding($exHeader['email'], 'UTF-8', mb_list_encodings()); ?></td>
                  </tr>
                  <tr>
                    <td class="item_info_table_label">&#32852;&#31995;&#20154;&#30005;&#35805;</td>
                    <td class="item_info_table_value"><?= mb_convert_encoding($exHeader['phone'], 'UTF-8', mb_list_encodings()); ?></td>
                  </tr>
                  <tr>
                    <td class="item_info_table_label">&#38382;&#39064;&#31616;&#36848;</td>
                    <td class="item_info_table_value"><?=  mb_convert_encoding($tocHeader['short_desc'], 'UTF-8', mb_list_encodings()); ?></td>
                  </tr>
                </table>
                </div>
                <br>
                <?php  if(!empty($erHeader)) : ?>
                <!-- SECTION DETAILS - ER -->
                <div style="page-break-inside: avoid">
                <div class="item_info_title"><p><b>&#24744;&#30340;&#35774;&#22791;</b></p></div>
                <table>
                  <tr>
                    <td class="item_info_table_label">&#36710;&#31181;</td>
                    <td class="item_info_table_value"><?= mb_convert_encoding($erHeader['zh'], 'UTF-8', mb_list_encodings()); ?></td>
                  </tr>
                  <tr>
                    <td class="item_info_table_label">&#36710;&#22411;</td>
                    <td class="item_info_table_value"><?=  mb_convert_encoding($erHeader['model'], 'UTF-8', mb_list_encodings()); ?></td>
                  </tr>
                  <tr>
                    <td class="item_info_table_label">&#35774;&#22791;&#24207;&#21015;&#21495;</td>
                    <td class="item_info_table_value"><?=  mb_convert_encoding($erHeader['sn'], 'UTF-8', mb_list_encodings()); ?></td>
                  </tr>
                  <tr>
                    <td class="item_info_table_label">&#23458;&#25143;&#36164;&#20135;&#32534;&#21495;</td>
                    <td class="item_info_table_value"><?= mb_convert_encoding($erHeader['cust_asset_num'], 'UTF-8', mb_list_encodings()); ?></td>
                  </tr>
                  <tr>
                    <td class="item_info_table_label">&#26426;&#22330;&#20195;&#30721;</td>
                    <td class="item_info_table_value"><?= mb_convert_encoding($erHeader['apc'], 'UTF-8', mb_list_encodings()); ?></td>
                  </tr>
                  <tr>
                    <td class="item_info_table_label">&#24037;&#20316;&#23567;&#26102;</td>
                    <td class="item_info_table_value"><?= mb_convert_encoding($erHeader['hours'], 'UTF-8', mb_list_encodings()); ?></td>
                  </tr>
                </table>
                </div>
                <br>
                <?php  endif; ?>
                <!-- SECTION DETAILS - CRT -->
                <div style="page-break-inside: avoid">
                <div class="item_info_title"><p><b>&#24744;&#30340;<?=$sso['name']?>&#32852;&#31995;&#20154;</b></p></div>
                <table width="100%" cellpadding="10">
                  <tr>
                    <td align="center" bgcolor="#ffffff" width="20%">
                      <div style="background:#f7f7f7; border:1px #d0d0d0 solid; padding:6px;" class="vcard">
                        <p class="item_reps_title">&#38144;&#21806;&#32852;&#31995;&#20154;</p>
                        <?php   if($uSales->isValid()): ?>
                            <p><?= mb_convert_encoding($uSales->getFullname(), 'UTF-8', mb_list_encodings()); ?><br/><?= mb_convert_encoding($uSales->getTitle(), 'UTF-8', mb_list_encodings()); ?><br/><a href="mailto:<?= mb_convert_encoding($uSales->getEmail(), 'UTF-8', mb_list_encodings()); ?>"><?= mb_convert_encoding($uSales->getEmail(), 'UTF-8', mb_list_encodings()); ?></a><br/><?= mb_convert_encoding($uSales->itsDetails['direct_phone'], 'UTF-8', mb_list_encodings()); ?></p>
                        <?php   else: ?>
                        <p>No contact assigned yet</p>
                        <?php   endif; ?>
                      </div>
                    </td>
                    <td align="center" bgcolor="#FFFFFF" width="20%">
                      <div style="background:#f7f7f7; border:1px #d0d0d0 solid; padding:6px;" class="vcard">
                        <p class="item_reps_title">&#21806;&#21518;&#26381;&#21153;&#32852;&#31995;&#20154;</p>
                        <?php   if($uService->isValid()): ?>
                            <p><?= mb_convert_encoding($uService->getFullname(), 'UTF-8', mb_list_encodings()); ?><br/><?= mb_convert_encoding($uService->getTitle(), 'UTF-8', mb_list_encodings()); ?><br/><a href="mailto:<?= mb_convert_encoding($uService->getEmail(), 'UTF-8', mb_list_encodings()); ?>"><?= mb_convert_encoding($uService->getEmail(), 'UTF-8', mb_list_encodings()); ?></a><br/><?= mb_convert_encoding($uService->itsDetails['direct_phone'], 'UTF-8', mb_list_encodings()); ?></p>
                        <?php   else: ?>
                        <p>No contact assigned yet</p>
                        <?php   endif; ?>
                      </div>
                    </td>
                    <td align="center" bgcolor="#FFFFFF" width="20%">
                      <div style="background:#f7f7f7; border:1px #d0d0d0 solid; padding:6px;" class="vcard">
                        <p class="item_reps_title">&#38646;&#20214;&#20379;&#24212;&#32852;&#31995;&#20154;</p>
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
