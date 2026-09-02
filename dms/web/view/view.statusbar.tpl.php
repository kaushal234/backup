<?php declare(strict_types=1);
ob_start();

function _getHelpWording()
{
    return [
        'revision' => [
            'action' => [
                _('Set or update').' '._('DMS information'),
                _('Set or update').' '._('Coverage'),
                _('Set or update').' '._('Approvers'),
                _('Set or update').' '._('Restrictions'),
                _('Set or update').' '._('Notification'),
            ],
            'nextStep' => [
                _('Change status to').' APPROVAL',
                _('You will be asked to attach document for sequence approval'),
            ],
        ],
        'approval' => [
            'action' => [
                _('Close sequence approval in intranet'),
            ],
            'nextStep' => [
                _('Change status to').' ACTIVE',
                _('You will be asked to attach final documents (editable and final version)'),
            ],
        ],
        'active' => [
            'action' => [
                _('Nothing to do'),
                _('People use final document for the latest revision'),
            ],
            'nextStep' => [
                _('Change status to').' EXPIRED',
                '- '._('Either manually by the owner if needed'),
                '- '._('Or automatically by the system if revision cycle is expired'),
                _('Change status to').' ARCHIVE',
            ],
        ],
        'expired' => [
            'action' => [
                _('People can still use final document but knowing it is expired'),
            ],
            'nextStep' => [
                _('Change status to').' REVISION '._('to start REVISION process'),
                _('Or'),
                _('Change status to').' ARCHIVE '._('if this DMS is no more used'),
            ],
        ],
        'archive' => [
            'action' => [
                _('People can consult archived document from the revision section'),
            ],
            'nextStep' => [
                sprintf(_('No more action possible once status is %s'), 'ARCHIVE'),
            ],
        ],
    ];
}

function _generateHelpText($status)
{
    $helpWording = _getHelpWording();
    $wordingTemplate = '<p><u>'._('Actions').':</u>';
    foreach ($helpWording[$status]['action'] as $action) {
        $wordingTemplate .= "<br>$action";
    }
    $wordingTemplate .= '</p><p><u>'._('Next Step').':</u>';
    foreach ($helpWording[$status]['nextStep'] as $step) {
        $wordingTemplate .= "<br>$step";
    }

    return $wordingTemplate.'</p>';
}

function _getHelpPopup($status)
{
    $popup = new tldOverlib(
        _generateHelpText($status),
        [
            'linkName' => '<img src="/shared/icons/application/help.png" />',
            'CAPTION' => _('Status').' '._('Help'),
            'WIDTH' => 350,
            'OFFSETX' => 20,
        ]
    );

    return $popup->fetch();
}
?>

<style type="text/css">
    #statusBar { text-align:center; }
    .statusItemDirection { color:grey;}
    .statusItem { background-color:#eeeeee;  width:15%;}
    #<?php echo mb_strtolower($dms->getStatus()); ?>{ background-color:#3264C8; color:white; font-weight:bold; }
</style>

<div id="statusBar">
  <table cellspacing="2" cellpadding="2" width="100%" >
    <tr>
      <td id="revision" class="statusItem">REVISION</td>
      <td class="statusItemDirection">>></td>
      <td id="approval" class="statusItem">APPROVAL</td>
      <td class="statusItemDirection">>></td>
      <td id="active" class="statusItem">ACTIVE</td>
      <td class="statusItemDirection">>></td>
      <td id="expired" class="statusItem">EXPIRED</td>
      <td class="statusItemDirection">||</td>
      <td id="archive" class="statusItem">ARCHIVE</td>
    </tr>
    <?php if (_isOwner()) { ?>
    <tr>
      <td><?php echo _getHelpPopup('revision'); ?></td>
      <td></td>
      <td><?php echo _getHelpPopup('approval'); ?></td>
      <td></td>
      <td><?php echo _getHelpPopup('active'); ?></td>
      <td></td>
      <td><?php echo _getHelpPopup('expired'); ?></td>
      <td></td>
      <td><?php echo _getHelpPopup('archive'); ?></td>
    </tr>
    <?php } ?>
  </table>
</div>

<?php
$_BUFF = ob_get_contents();
ob_end_clean();

return $_BUFF;
?>