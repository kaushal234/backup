<?php
ob_start();
?>

    <!-- DISPLAY -->

    <style type="text/css">
        #form_menu {
            position: absolute;
            top: 50%;
            right: 5px;
            border: 2px solid grey;
            z-index: 100;
            background-color: white;
            padding: 10px;
        }

        .tld_table thead tr, .tld_table tfoot tr {
            background: #2971A8;
        }

        .tld_table thead tr th, .tld_table tfoot tr td {
            font-weight: bold;
            color: white;
            text-align: center;
            vertical-align: middle;
        }

        .btnAction {
            cursor: pointer;
            cursor: hand;
        }

        .help {
            cursor: help;
            border-bottom: 1px dashed grey;
        }

    </style>

    <!-- JS TOOLS -->

    <script src="/shared/javascript/jquery/jquery-ui-1.10.3.custom/js/jquery-1.9.1.js"></script>
    <script src="/shared/javascript/jquery/jquery-ui-1.10.3.custom/js/jquery-ui-1.10.3.custom.min.js"></script>
    <link rel="stylesheet"
          href="/shared/javascript/jquery/jquery-ui-1.10.3.custom/css/smoothness/jquery-ui-1.10.3.custom.min.css"/>

    <script type="text/javascript">

        // On load of the form
        $(document).ready(function () {
            // add multiple select on checkbox in table
            const allCheckboxes = document.querySelectorAll('input[type="checkbox"]');
            let lastChecked = null;

            allCheckboxes.forEach((checkbox) => {
                checkbox.addEventListener('click', function (e) {
                    if (e.shiftKey && lastChecked) {
                        // check if box are the same
                        if (this.name === lastChecked.name) {
                            let inBetween = false;
                            allCheckboxes.forEach((box) => {
                                if (box === this || box === lastChecked) {
                                    inBetween = !inBetween;
                                }
                                if (inBetween && box.name === this.name) {
                                    box.checked = lastChecked.checked;
                                }
                            });
                        }
                    }
                    lastChecked = this; // Updates the last checked box
                });
            });

            // Prepare and create popups
            $('.popup').dialog({
                autoOpen: false,
                draggable: false,
                buttons: [{
                    text: "Close", click: function () {
                        $(this).dialog("close");
                    }
                }],
                minWidth: 600,
                maxHeight: 300
            });
        });

        // Tool feature for select ALL
        function switchCheckbox(btnEl, inputName) {
            var el = $('input[name^=' + inputName + ']:checkbox:not(:disabled)');
            if (btnEl.attr('value') == 0) {
                el.prop('checked', false);
                btnEl.attr('value', 1);
                btnEl.html('Check All');
            } else {
                el.prop('checked', true);
                btnEl.attr('value', 0);
                btnEl.html('Uncheck All');
            }
            return;
        }
    </script>
<?php $admin = $user->isInGroup(["superuser"]) || $user->isInGroupLevel("role_CSM", 900); ?>

<?php $csm = _isCSM_AST(); ?>
<?php $firstline = current($SB_LINES);
$csmSso = _isCSM_AST($firstline['sso_erp']);
?>
    <!-- FORM IMPLEMENTATION -->
<?php if ($SB_LINES > 10): ?>
    <div class="tips">
        <strong>Tips:</strong>
        <ul>
            <li>Click on one checkbox, then hold <strong>Shift</strong> and click another to select all checkboxes in between.</li>
            <li>This feature works for checkboxes within the same column.</li>
            <li>If you accidentally select too many, you can uncheck them by repeating the process while holding <strong>Shift</strong>.</li>
            <li>Other columns or checkboxes with a different purpose will not be affected.</li>
            <li><strong>Important:</strong> Please wait for all the lines to load before using this feature. Make sure the table is fully populated with data before interacting with the checkboxes.</li>
        </ul>
    </div>
<?php endif; ?>
    <form method="post" id="frm">
        <input type="hidden" name="m[0]" value="sb"/>
        <input type="hidden" name="m[1]" value="view"/>
        <input type="hidden" name="m[2]" value="summary"/>
        <input type="hidden" name="m[3]" value="implementation"/>
        <input type="hidden" name="id" value="<?= $id ?>"/>
        <table cellpadding="2" class="tld_table" class="sortable">
            <thead>
            <tr>
                <th colspan="5">ER<br>Informations</th>
                <th colspan="2">TLD<br>Decision</th>
                <th colspan="2">Customer<br>Decision</th>
                <th colspan="2">Logs</th>
                <th colspan="2">NOT</th>
                <th colspan="2">SPR</th>
                <th colspan="4">CSR</th>
                <th></th>
                <th colspan="2"></th>
                <?php if ($csmSso || $admin): ?>
                    <th></th>
                    <th></th><?php endif; ?>
                <?php if ($admin): ?>
                    <th></th><?php endif; ?>
            </tr>
            <tr>
                <th>#</th>
                <th>SN#</th>
                <th>Customer BUYER</th>
                <th>Customer USER</th>
                <th>APC</th>
                <th>Parts</th>
                <th>Service</th>
                <th>Parts</th>
                <th>Service</th>
                <th>Info</th>
                <th>Add</th>
                <th>Info</th>
                <th>Add</th>
                <th>Info</th>
                <th>Add</th>
                <th>Info</th>
                <th>Hourmeter</th>
                <th>Completion Date</th>
                <th>Add</th>
                <th></th>
                <th>ISI</th>
                <th>Remediation</th>
                <?php if ($csmSso || $admin): ?>
                    <th></th>
                    <th></th><?php endif; ?>
                <?php if ($admin): ?>
                    <th></th><?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($SB_LINES as $k => $SB_LINE):
                $color = ($k % 2) ? "#eeeeee" : "#d0d0d0";
                if ($SB_LINE['buyer_customer_name'] === '**DEMO**') {
                    $color = '#ff5252';
                }
                ?>
                <tr style="background:<?= $color ?>" id="<?= $SB_LINE['id'] ?>">
                    <td title="<?= $SB_LINE['id'] ?>"><?= $k + 1; ?></td>
                    <td>
                        <a href="/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view&id=<?= $SB_LINE['er_id'] ?>"
                           target="_blank"><?= $SB_LINE['sn'] ?></a>
                        <?php if ($SB_LINE['sales_org'] !== $SB_LINE['sso_name']): ?>
                            <?= $SB_LINE['sales_org'] ?>
                        <?php endif; ?>
                        <?php if (isset($SB_LINE['cust_asset_num']) && $SB_LINE['cust_asset_num'] !== ''): ?>
                            <?= "(".$SB_LINE['cust_asset_num'].")" ?>
                        <?php endif; ?>
                    </td>
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
                    <td><?= $SB_LINE['apc_code'] ?></td>
                    <td align="center"><?= _getDecisionView($SB_LINE, 'part') ?></td>
                    <td align="center"><?= _getDecisionView($SB_LINE, 'service') ?></td>
                    <td align="center"><?= _getCustomerDecisionView($SB_LINE, 'part') ?></td>
                    <td align="center"><?= _getCustomerDecisionView($SB_LINE, 'service') ?></td>
                    <td align="center"><?= _getLOGView($SB_LINE) ?></td>
                    <td align="center"><?= _getLOGForm($SB_LINE) ?></td>
                    <td align="center"><?= _getNOTView($SB_LINE) ?></td>
                    <td align="center"><?= _getNOTForm($SB_LINE) ?></td>
                    <td align="center"><?= _getSPRView($SB_LINE) ?></td>
                    <td align="center"><?= _getSPRForm($SB_LINE) ?></td>
                    <td align="center"><?= _getCSRView($SB_LINE) ?></td>
                    <td><?= $SB_LINE['csr_hourmeter'] ?></td>
                    <td><?= '0000-00-00 00:00:00' === $SB_LINE['csr_completion_date'] ? '0000-00-00' : (new \DateTime($SB_LINE['csr_completion_date']))->format('Y-m-d') ?></td>
                    <td align="center"><?= _getCSRForm($SB_LINE) ?></td>
                    <td align="center"><?= _getCloseForm($SB_LINE) ?></td>
                    <td align="center"><?= _getISIView($SB_LINE) ?></td>
                    <td align="center"><?= $SB_LINE['remediation'] ?></td>
                    <?php if ($csmSso || $admin): ?>
                        <td align="center"><?= _getCSMView($SB_LINE) ?></td>
                        <td align="center"><?= _getISIForm($SB_LINE) ?></td>
                    <?php endif; ?>
                    <?php if ($admin): ?>
                        <td align="center"><?= _getAdminView($SB_LINE) ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="7">Columns Tools -></td>
                <td>
                    <?= _getCustomerDecisionSelectInputForm(
                        'CustomerPartDecisionSelectTool',
                        ['attr' => ' onchange="javascript:
          	    	$(\'.cust_part_decision:not(:disabled)\').val($(this).val());"
      	    	']
                    )
                    ?>
                </td>
                <td>
                    <?= _getCustomerDecisionSelectInputForm(
                        'CustomerServiceDecisionSelectTool',
                        ['attr' => ' onchange="javascript:
          	    	$(\'.cust_service_decision:not(:disabled)\').val($(this).val());"
      	    	']
                    )
                    ?>
                </td>
                <td colspan="2">
                    <button type="button" value="1" onClick="javascript:switchCheckbox($(this),'log');">Check All
                    </button>
                </td>
                <td colspan="2">
                    <button type="button" value="1" onClick="javascript:switchCheckbox($(this),'not');">Check All
                    </button>
                </td>
                <td colspan="2">
                    <button type="button" value="1" onClick="javascript:switchCheckbox($(this),'spr');">Check All
                    </button>
                </td>
                <td colspan="4">
                    <button type="button" value="1" onClick="javascript:switchCheckbox($(this),'csr');">Check All
                    </button>
                </td>
                <td></td>
                <td></td>
                <td></td>
                <?php if ($csmSso || $admin): ?>
                    <td></td>
                    <td colspan="1">
                        <button type="button" value="1" onClick="javascript:switchCheckbox($(this),'isi');">Check All
                        </button>
                    </td><?php endif; ?>
                <?php if ($admin): ?>
                    <td></td><?php endif; ?>
            </tr>
            <tr>
                <td colspan="7">Submit buttons -></td>
                <td colspan="2">
                    <input type="submit" name="m[4]" value="Set Decision" onclick="this.value='CUST_DECISION'"/>
                </td>
                <td colspan="2">
                    <input type="submit" name="m[4]" value="Add LOG" onclick="this.value='LOG'"/>
                </td>
                <td colspan="2">
                    <input type="submit" name="m[4]" value="Add NOT" onclick="this.value='NOT'"/>
                    <input type="submit" name="m[4]" value="BYPASS NOT" onclick="
                            if(confirm('Are you sure to bypass the SB notification ?')) {
                                this.value='NOT_BYPASS';
                            }"
                    />
                </td>
                <td colspan="2">
                    <input type="submit" name="m[4]" value="Add SPR" onclick="this.value='SPR'"/>
                </td>
                <td colspan="4">
                    <input type="submit" name="m[4]" value="Add CSR" onclick="this.value='CSR'"/>
                </td>
                <td></td>
                <td></td>
                <td></td>
                <?php if ($csm): ?>
                    <td></td>
                <td colspan="1">
                    <input type="submit" name="m[4]" value="Change ISI" onclick="this.value='ISI'"/>
                </td><?php endif; ?>
                <?php if ($admin): ?>
                    <td></td><?php endif; ?>
            </tr>
            </tfoot>
        </table>
    </form>

    <div id="definitions">
        <p>
        <h4>Definitions</h4>
        <u>Icons</u><br>
        <ul>
            <li><img src="/shared/bluesphere/16x16/actions/mail_delete.png" alt="Bypass Notification"/> Bypass
                notification and move to TLD_TO_SHIP status
            </li>
            <li><img src="/shared/bluesphere/16x16/actions/toggle_log_red.png" alt="Line Logs"/> Display logs for this
                SB Line. Logs contain status changes and custom notes added by users
            </li>
            <li><img src="/shared/bluesphere/16x16/actions/mail_find.png" alt="Display Notification"/> Display
                notification sent to customer
            </li>
            <li><img src="/shared/icons/application/stop.png" alt="Close Line"/> Close the line prematurely. Status will
                change to CLOSED. This feature should be used with caution
            </li>
            <?php if ($csm): ?>
                <li><img src="/shared/bluesphere/16x16/actions/edit.png" alt="Edit Line"/> Manual Edit of the Line. This
                    feature should be used with caution
                </li>
            <?php endif; ?>
        </ul>
        </p>
    </div>

<?php

// Functions ----------------------------------------------------->

// Global

function _isCSM_AST($level = null)
{
    global $user;

    if ($level !== null) {
        return $user->isInGroup(["superuser"]) || $user->isInGroupLevel("role_CSM", $level) || $user->isInGroup(["role_CSA", $level]) || $user->isInGroup(["role_AST", $level]);
    }
    return $user->isInGroup(["superuser", "role_CSM", "role_CSA", 'role_AST']);
}

function _getModuleRecordLink($moduleCode)
{
    $moduleLinks = tldUtils::getModLinks();
    return $moduleLinks[$moduleCode];
}

function _getDecisionList($type)
{
    switch ($type) {
        case 'part':
            return tldSB_Line::getPartDecisionList();
            break;
        case 'service':
            return tldSB_Line::getServiceDecisionList();
            break;
    }
}

function _getCustomerDecisionSelectInputForm($inputName, $options = NULL)
{
    $values = array_keys(tldSB_Line::getCustomerDecisionList());
    $values = array_combine($values, $values);
    $selectOptions = '<option value=""></option>';
    foreach ($values as $value) {
        if ($value == $options['default']) {
            $attr = ' selected="selected"';
        }
        $selectOptions .= "<option value=\"$value\" $attr>$value</option>";
        // reset option attr
        $attr = NULL;
    }
    return <<<EOF
<select name="$inputName" {$options['attr']}>
    $selectOptions
</select>
EOF;
}

// Views

function _getDecisionView($SB_LINE, $type)
{
    switch ($type) {
        case 'part':
            $decision = $SB_LINE['part_decision'];
            break;
        case 'service':
            $decision = $SB_LINE['service_decision'];
            break;
    }
    $descriptionList = _getDecisionList($type);
    $description = $descriptionList[$decision];
    return <<<EOF
<span class="help" title="$description">$decision</span>
EOF;
}

function _getCustomerDecisionView($SB_LINE, $type)
{
    global $acl_CUST_DECISION;
    // Drill down by decision type
    switch ($type) {
        case 'service':
            // inform that it is not applicable
            $html = "N/A";
            if ((new tldSB_Line($SB_LINE['id'], true))->isCustomerServiceDecisionNeeded($SB_LINE)) {
                // Rules
                if (
                    !$acl_CUST_DECISION
                    || $SB_LINE['status'] != 'CUSTOMER_TO_DECIDE'
                ) {
                    $attr = ' disabled="disabled"';
                }
                // Get default value
                $default = $SB_LINE['cust_service_decision'];
                // Get input form
                $html = _getCustomerDecisionSelectInputForm(
                    "cust_decision[{$SB_LINE['id']}][$type]",
                    [
                        'attr' => $attr . ' class="cust_' . $type . '_decision"',
                        'default' => $default,
                    ]
                );
            }
            break;
        case 'part':
            // inform that it is not applicable
            $html = "N/A";
            if ((new tldSB_Line($SB_LINE['id'], true))->isCustomerPartDecisionNeeded($SB_LINE)) {
                // Rules
                if (
                    !$acl_CUST_DECISION
                    || $SB_LINE['status'] !== 'CUSTOMER_TO_DECIDE'
                ) {
                    $attr = ' disabled="disabled"';
                }
                // Get default value
                $default = $SB_LINE['cust_part_decision'];
                // Get input form
                $html = _getCustomerDecisionSelectInputForm(
                    "cust_decision[{$SB_LINE['id']}][$type]",
                    [
                        'attr' => $attr . ' class="cust_' . $type . '_decision"',
                        'default' => $default,
                    ]
                );
            }
            break;
    }
    // Return html
    return $html;
}

function _getLOGView($SB_LINE)
{
    // prepare options
    $flagUserManualLogEntry = false;
    // loop
    $data = null;
    foreach ($SB_LINE['logs'] as $log) {
        $logComment = nl2br($log['comment']);
        $data .= <<<EOF
<strong>{$log['date']} {$log['poster_fullname']}</strong>:<br>$logComment<br><br>
EOF;
        // look for flag
        if ($log['log_num'] == 1) {
            $flagUserManualLogEntry = true;
        }
    }
    if (empty($data)) {
        return;
    }
    // look for options
    $icon = 'toggle_log.png';
    if ($flagUserManualLogEntry) {
        $icon = 'toggle_log_red.png';
    }
    return <<<EOF
<img src="/shared/bluesphere/16x16/actions/$icon" class="btnAction" alt="LOG" title="Display log events of this line" onClick="javascript:$('#log{$SB_LINE['id']}').dialog('open');" />
<div class="popup" id="log{$SB_LINE['id']}" title="SB#{$SB_LINE['parent_id']} ER#{$SB_LINE['sn']} Logs" style="display:none;">
  <p>$data</p>
</div>
EOF;
}

function _getNOTView($SB_LINE)
{
    global $php_self;
    if (empty($SB_LINE['notifications']) && $SB_LINE['status'] === 'TLD_TO_NOTIFY') {
        return <<<EOF
<a href="#" onClick="javascript:
	if(confirm('Are you sure to bypass the SB notification for ER SN {$SB_LINE['sn']}?'))
	{
		document.location.href='$php_self?m[0]=sb&m[1]=view&m[2]=summary&m[3]=implementation&m[4]=NOT_BYPASS&bpnot[]={$SB_LINE['id']}&id={$SB_LINE['parent_id']}';
    }
">
  <img src="/shared/bluesphere/16x16/actions/mail_delete.png" alt="Bypass NOT" />
</a>
<input type="checkbox" name="bpnot[]" value="{$SB_LINE['id']}">
EOF;
    }
    $data = NULL;
    foreach ($SB_LINE['notifications'] as $not) {
        $to = str_replace(",", ",<br>", $not['recipients']);
        $data .= <<<EOF
<strong>Date: {$not['dt']}</strong><br>
From: {$not['poster_email']}<br>
To: {$to}<br>
Subject: {$not['subject']}<br><br>
EOF;
    }
    if (empty($data)) {
        return;
    }
    return <<<EOF
<img src="/shared/bluesphere/16x16/actions/mail_find.png" class="btnAction" alt="NOT" title="Display NOT sent to customer" onClick="javascript:$('#not{$SB_LINE['id']}').dialog('open');" />
<div class="popup" id="not{$SB_LINE['id']}" title="SB#{$SB_LINE['parent_id']} ER#{$SB_LINE['sn']} NOT" style="display:none;">
  <p>$data</p>
  <p><a href="$php_self?m[0]=sb&m[1]=view&m[2]=lines&m[3]=NOT&lid={$SB_LINE['id']}&id={$SB_LINE['parent_id']}" style="color:blue;" target="_blank">See all notifications details...</a></p>
</div>
EOF;
}

function _getSPRView($SB_LINE)
{
    if ($SB_LINE['spr_id'] == 0) {
        return;
    }
    $link = _getModuleRecordLink('SPR') . $SB_LINE['spr_id'];
    return <<<EOF
<a href="$link" target="_blank">SPR#{$SB_LINE['api_spr_id']}</a><br>
{$SB_LINE['spr_status']}<br>
EOF;
}

function _getCSRView($SB_LINE)
{
    if ($SB_LINE['api_csr_id'] == 0) {
        return;
    }
    $link = _getModuleRecordLink('CSR') . $SB_LINE['api_csr_id'];
    return <<<EOF
<a href="$link/show" target="_blank">CSR#{$SB_LINE['api_csr_id']}</a><br>
{$SB_LINE['csr_status']}<br>
EOF;
}

// Forms

function _getLOGForm($SB_LINE)
{
    global $acl_LOG;
    // Rules
    if (!$acl_LOG) {
        $disableOption = ' disabled="disabled"';
    }
    // Return html
    return <<<EOF
<input type="checkbox" name="log[]" value="{$SB_LINE['id']}"$disableOption>
EOF;
}

function _getISIForm($SB_LINE)
{
    // Return html
    return <<<EOF
<input type="checkbox" name="isi[]" value="{$SB_LINE['id']}">
EOF;
}

function _getNOTForm($SB_LINE)
{
    global $acl_NOT;
    // Rules
    if (!$acl_NOT || (new tldSB_Line($SB_LINE['id'], true))->isNOTCreationAllowed($SB_LINE) !== true
    ) {
        $disableOption = ' disabled="disabled"';
    }
    // html attributes
    $class = $SB_LINE['part_decision'] . $SB_LINE['service_decision'];
    // Return html
    return <<<EOF
<input type="checkbox" class="$class" name="not[]" value="{$SB_LINE['id']}"$disableOption>
EOF;
}

function _getSPRForm($SB_LINE)
{
    global $acl_SPR;
    // Rules
    if (
        !$acl_SPR
        || (new tldSB_Line($SB_LINE['id'], true))->isSPRCreationAllowed($SB_LINE) !== true
    ) {
        $disableOption = ' disabled="disabled"';
    }
    // Return html
    return <<<EOF
<input type="checkbox" name="spr[]" value="{$SB_LINE['id']}"$disableOption>
EOF;
}

function _getCSRForm($SB_LINE)
{
    global $acl_CSR;
    $reason = '';
    // Rules
    if (
        !$acl_CSR
        || ($reason = (new tldSB_Line($SB_LINE['id'], true))->isCSRCreationAllowed($SB_LINE)) !== TRUE
    ) {
        $reason = !$acl_CSR ? 'no permission' : $reason;
        return <<<EOF
<p>$reason</p>
EOF;
    }
    // Return html
    return <<<EOF
<input type="checkbox" name="csr[]" value="{$SB_LINE['id']}">
EOF;
}

function _getCloseForm($SB_LINE)
{
    global $php_self, $acl_CLOSE;

    if (!(new tldSB_Line($SB_LINE['id'], true))->isClosed($SB_LINE) && $acl_CLOSE) {
        return <<<EOF
<a href="$php_self?m[0]=sb&m[1]=view&m[2]=summary&m[3]=implementation&m[4]=CLOSE&id={$SB_LINE['parent_id']}&lid={$SB_LINE['id']}" title="Close this line">
  <img src="/shared/icons/application/stop.png" alt="Close" />
</a>
EOF;
    }
}

function _getISIView($SB_LINE)
{
    // Extra info
    switch ($SB_LINE['status']) {
        case 'CUSTOMER_TO_DECIDE':
            $dateDecide = new DateTime($SB_LINE['dt_cust_to_decide']);
            $interval = 'P' . tldSB_Line::CUSTOMER_TO_DECIDE_TIMER . 'D';
            $dateDecide->add(new DateInterval($interval));
            $extra_info = "Will expire {$dateDecide->format('Y-m-d')}";
            break;
        case 'CLOSED':
            $extra_info = "Closure type: {$SB_LINE['closure_type']}";
            break;
    }
    if (!empty($extra_info)) {
        $attr = ' style="border-bottom: 1px grey dashed; cursor: help;"';
    }
    return <<<EOF
<span title="$extra_info"$attr>{$SB_LINE['status']}</span>
EOF;
}

function _getAdminView($SB_LINE)
{
    return <<<EOF
<a href="/en/private/product_support/sb/sb_admin.php?mode=record_view&form_type=sb_lines_tpl&id={$SB_LINE['id']}" title="Manage Line in admin" target="_blank">
  <img src="/shared/icons/application/wrench.png" alt="Admin" />
</a>
EOF;
}

function _getCSMView($SB_LINE)
{
    return <<<EOF
<a href="$php_self?m[0]=sb&m[1]=view&m[2]=lines&m[3]=edit&id={$SB_LINE['parent_id']}&lid={$SB_LINE['id']}" title="Edit Line">
  <img src="/shared/bluesphere/16x16/actions/edit.png" alt="Edit" />
</a>
EOF;
}


// End of Functions ----------------------------------------------------->

return ob_get_clean();
