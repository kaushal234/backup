<?php
ob_start();
?>
<?php if(!empty($data['er'])) : ?>
                <div style="page-break-inside: avoid">
                <div class="item_info_title"><p><b>Your Equipment</b></p></div>
                <p>This Bulletin is applicable to the following equipment you<b>*</b> currently use (as per our records):</p>
                <table align="center">
                    <tr>
					  <th class="item_info_table_label">TLD Serial Number</th>
					  <th class="item_info_table_label">Customer Asset#</th>
					  <th class="item_info_table_label">Model#</th>
					  <th class="item_info_table_label">Last Known Location</th>
					</tr>
                  <?php  foreach($data['er'] as $er):  ?>
                    <tr>
                        <td class="item_info_table_value">
                            <?= !empty($er['sn'])
                                    ? htmlspecialchars(mb_convert_encoding($er['sn'], 'UTF-8', 'ISO-8859-1'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                                    : 'Unknown' ?>
                        </td>

                        <td class="item_info_table_value">
                            <?= !empty($er['cust_asset_num'])
                                    ? htmlspecialchars(mb_convert_encoding($er['cust_asset_num'], 'UTF-8', 'ISO-8859-1'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                                    : 'Unknown' ?>
                        </td>

                        <td class="item_info_table_value">
                            <?= !empty($er['model'])
                                    ? htmlspecialchars(mb_convert_encoding($er['model'], 'UTF-8', 'ISO-8859-1'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                                    : 'Unknown' ?>
                        </td>

                        <td class="item_info_table_value">
                            <?= htmlspecialchars(mb_convert_encoding($er['apc_code'], 'UTF-8', 'ISO-8859-1'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                            <?= !empty($er['apc_country_name'])
                                    ? ' (' . htmlspecialchars(mb_convert_encoding($er['apc_country_name'], 'UTF-8', 'ISO-8859-1'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ')'
                                    : '' ?>
                        </td>

                    </tr>
                  <?php  endforeach; ?>
                </table>
                </div>
                <p><b>*</b>If you are no longer the owner of this equipment, please let us know.</p>
<?php  endif;

return ob_get_clean();
