<br>
<?php if ($user->isInGroup(['role_EVP']) && $sb->getStatus() === 'SSD_DECISION'): ?>
  <a href="<?= "$php_self?m[0]=sb&m[1]=view&m[2]=status&m[3]=reject&id=$id" ?>">Click here to Reject this SB</a>
<?php endif; ?>
<br>
<form method="post" id="frm">
    <input type="hidden" name="m[0]" value="sb" />
    <input type="hidden" name="m[1]" value="view" />
    <input type="hidden" name="m[2]" value="summary" />
    <input type="hidden" name="m[3]" value="selection" />
    <input type="hidden" name="m[4]" value="update" />
    <input type="hidden" name="id" value="<?= $id ?>" />
    <table cellpadding="2" class="tld_table" class="sortable">
        <thead>
        <tr>
            <th colspan="7">ER Informations</th>
            <th colspan="2">Parts</th>
            <th colspan="2">Service</th>
        </tr>
        <tr>
            <th>#</th>
            <th>SN#</th>
            <th>Country</th>
            <th>APC</th>
            <th>Customer BUYER</th>
            <th>Customer USER</th>
            <th>Ship date</th>
            <th>Decision</th>
            <th>Selection</th>
            <th>Decision</th>
            <th>Selection</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($SB_LINES as $k=>$SB_LINE):
            $color = ($k%2) ? "#eeeeee" : "#d0d0d0";
            if ($SB_LINE['buyer_customer_name'] === '**DEMO**') {
                $color = '#ff5252';
            }
            ?>

            <tr style="background:<?= $color ?>">
                <td><?= $k+1; ?></td>
                <td><a href="/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view&id=<?= $SB_LINE['er_id'] ?>" target="_blank"><?= $SB_LINE['sn'] ?></a></td>
                <td><?= $SB_LINE['apc_country_name'] ?></td>
                <td><?= $SB_LINE['apc_code'] ?></td>
                <td>
                    <a href="/en/private/sales_service/sales.php?m[0]=customers&m[1]=view&m[2]=contacts&id=<?= $SB_LINE['buyer_customer_id'] ?>"
                       title="Get customer contacts" target="_blank">
                        <?= $SB_LINE['buyer_customer_name'] ?>
                    </a>
                </td>
                <td>
                    <a href="/en/private/sales_service/sales.php?m[0]=customers&m[1]=view&m[2]=contacts&id=<?= $SB_LINE['user_customer_id'] ?>"
                       title="Get customer contacts" target="_blank">
                        <?= $SB_LINE['user_customer_name'] ?>
                    </a>
                </td>
                <td><?= $SB_LINE['date_shipped'] ?></td>
                <td align="center"><?= $SB_LINE['part_decision'] ?></td>
                <td><?= _getSelectInputToHTML(
                        "er[{$SB_LINE['id']}][part_decision]",
                        array(""=>"")+$partsDecisionListAsShortDescShortDesc,
                        array(
                            'default'=>_getDefaultPartValue($SB_LINE),
                            'attr'=>'class="part_decision"'
                        )
                    ) ?>
                </td>
                <td align="center"><?= $SB_LINE['service_decision'] ?></td>
                <td><?= _getSelectInputToHTML(
                        "er[{$SB_LINE['id']}][service_decision]",
                        array(""=>"")+$serviceDecisionListAsShortDescShortDesc,
                        array(
                            'default'=>_getDefaultServiceValue($SB_LINE),
                            'attr'=>'class="service_decision"'
                        )
                    ) ?>
                </td>
            </tr>
        <?php  endforeach; ?>
        </tbody>
        <tfoot>
        <tr>
            <td colspan="7"></td>
            <td colspan="2">Update column <?= _getSelectInputToHTML(
                    "toolPartsAll",
                    array(""=>"")+$partsDecisionListAsShortDescShortDesc,
                    array('attr'=><<<EOF
                	onChange="javascript:$('.part_decision').val($(this).val());"
EOF
                    )
                ) ?>
            </td>
            <td colspan="2">Update column <?= _getSelectInputToHTML(
                    "toolServiceAll",
                    array(""=>"")+$serviceDecisionListAsShortDescShortDesc,
                    array('attr'=><<<EOF
                	onChange="javascript:$('.service_decision').val($(this).val());"
EOF
                    )
                ) ?>
            </td>
        </tr>
        </tfoot>
    </table>
    <p id="buttons"><input type="submit" value="Submit" /> <input type="reset" value="Reset" /></p>
</form>