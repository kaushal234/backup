<?php
ob_start();
$root   = dirname(__DIR__, 5); // /srv/alvest-web-portals
$plugin = $root . '/shared/inc/smarty/plugins/modifier.renderHtmlOrNl2br.php';

if (!is_file($plugin)) {
    throw new \RuntimeException("Plugin not found: $plugin");
}
require_once $plugin;
?>

    <script src="/shared/javascript/jquery/jquery-ui-1.10.3.custom/js/jquery-1.9.1.js"></script>
    <script src="/shared/javascript/jquery/jquery-ui-1.10.3.custom/js/jquery-ui-1.10.3.custom.min.js"></script>
    <link rel="stylesheet"
          href="/shared/javascript/jquery/jquery-ui-1.10.3.custom/css/smoothness/jquery-ui-1.10.3.custom.min.css"/>

    <script type="text/javascript">
      $(function () {
        $('#accordion').accordion({
          'collapsible': true,
          'active':        <?= _getAccordionIndexSectionToDisplay() ?>,
          'heightStyle': 'content'
        })
      })
    </script>

    <style>
        .ui-accordion-header {
            font-family: arial, helvetica !important;
            font-weight: bold !important;
            color: #666666 !important;
        }
    </style>

    <!-- PDC HEAD -->

    <hr>
    <h3>PDC# <?= $pdc->getID() ?> - <?= $pdc->getStatus() ?></h3>
<?php if ($pdc->getShortDescription()): ?>
    <p><b><?= $pdc->getShortDescription() ?></b></p>
<?php endif; ?>
    <div style="padding: 5px; max-width: 160px ; background-color: <?= $pdc->itsHeader['is_ready_to_close'] ? '#00af50' : '#ff5252' ?>">READY TO CLOSE : <?= $pdc->itsHeader['is_ready_to_close'] ? 'YES' : 'NO' ?></div>
    <hr>

    <!-- PDC CONTENTS -->

    <table width="100%">
        <tr>

            <!-- PDC CONTENTS - LEFT COLUMN -->

            <td width="65%">

                <!-- PDC Last log -->
                <h3>Last log entry</h3>
                <?php
                $log = $pdc->getLog();
                if (count($log) && !empty($log[0]['comment'])):
                    ?>
                    <p><?= smarty_modifier_renderHtmlOrNl2br($log[0]['comment']) ?></p>
                <?php else: ?>
                    <p>No log entry yet</p>
                <?php endif; ?>

                <!-- MSG info -->
                <?php if ($pdc->getStatus() === 'SUSPENDED'): ?>
                <p class="alert">This PDC was SUSPENDED <?= $pdc->itsHeader['date_suspended'] ?></p>
                    <?php endif; ?>
                    <?php if (!in_array($pdc->getStatus(), ['REJECTED', 'CLOSED'])): ?>
                <h3>
                    <p><abbr title="IF = 1000, the problem is constant and prevents product operation or if it involves personnel or aircraft safety issues.
IF = 100, the problem is systematic/repetitive and does really impact the operation of the product by the customer.
IF = 10, the problem is occasional and does not regularly impact the operation of the product by the customer.
IF = 1, the problem is more a proposed product improvement or something that has no significant impact on product operation by the customer.">IF: </abbr>
                        <?= $pdc->getIFactor() ?>

                    <abbr title="(DF) is equal to (1,4678)N, N being the number of months since the problem has been discovered and recorded.">DF: </abbr>
                        <?= $pdc->getDFactor() ?>

                    <abbr title="(FW) is calculated as follows: FW = IF x DF and measures in fact the severity of the PDC as well as the celerity of each factory to remedy them.">FW: </abbr>
                        <?= $pdc->getFocusWeight() ?>
                        Months Open: <?= $pdc->itsHeader['monthsOpen'] ?>&nbsp;&nbsp;
                        TOCs OPEN: <?= $pdc->itsHeader['toc_count'] ?>&nbsp;&nbsp;
                    </p>
                </h3>
            <?php endif; ?>

                <!-- PDC HEADER -->
                <?= _getView() ?>

                <!-- PDC PARTS -->
                <?= _getPartsView() ?>

                <!-- COUNTERS -->
                <?= _getCountersView() ?>

                <br>

                <!-- PDC WORKFLOW / ACTIVITY -->

                <div id="accordion">
                    <h3>PENDING</h3>
                    <div>
                        <?= _getPendingView() ?>
                    </div>
                    <h3>INVESTIGATION</h3>
                    <div>
                        <?= _getInvestigationView() ?>
                    </div>
                    <h3>ACTION</h3>
                    <div>
                        <?= _getActionView() ?>
                    </div>
                    <h3>CLOSURE</h3>
                    <div>
                        <?= _getClosureView() ?>
                    </div>
                </div>

            </td>

            <td width="5%"></td>

            <!-- PDC CONTENTS - RIGHT COLUMN -->

            <td width="30%" padding="10">

                <!-- PDC HEADER FILE -->

                <p align="right">
                    <?php
                    $fileName = $pdc->getFileName();
                    $file = new basicFile(tldPDC::getPathToUploadFile($fileName));
                    $fileMime = $file->getMimeTypeFromExtension();
                    $fileMime = preg_split('#/#', $fileMime);
                    if (strtolower($fileMime[0]) === 'image'):
                        ?>
                        <a href="/en/private/uploads/demerit/<?= $fileName ?>">
                            <img src="/en/private/uploads/demerit/<?= $fileName ?>" width="250">
                        </a>
                    <?php elseif (!empty($fileName)): ?>
                        <a href="/en/private/uploads/demerit/<?= $fileName ?>">
                            <img src="/shared/bluesphere/64x64/mimetypes/document.png" alt="Download Attachment">
                        </a>
                    <?php endif; ?>
                </p>

                <br>

                <!-- PDC LINKS -->
                <?= _getLinksView() ?>

                <!-- PDC FILES -->
                <?= _getFilesView() ?>
            </td>
        </tr>
    </table>


<?php

function _getView()
{
    global $pdc, $php_self;
    $data = $pdc->itsHeader;
    $data['is_ibs'] = $data['is_ibs'] ? 'Yes' : 'No';
    $data['is_ihs'] = $data['is_ihs'] ? 'Yes' : 'No';
    $data['is_link'] = $data['is_link'] ? 'Yes' : 'No';
    $data['is_apu_off'] = $data['is_apu_off'] ? 'Yes' : 'No';
    $data['is_ready_to_close'] = $data['is_ready_to_close'] ? 'Yes' : 'No';
    
    $report = new tldAssocTable(
        $data,
        [
            'id' => 'PDC#',
            'date' => 'Date',
            'poster_fullname' => 'Poster',
            'initiator_fullname' => 'Initiator',
            'assignee_fullname' => 'Assignee',
            'status' => 'Status',
            'factory_fullname' => 'Factory',
            'product_type' => 'Equipment Type',
            'model' => 'Equipment Model',
            'short_desc' => 'Short description',
            'description' => 'Description',
            'verification_description' => 'Verification',
            'is_ibs' => 'Involves iBS',
            'is_ihs' => 'Involves iHS/ipHS',
            'is_link' => 'Involves LINK',
            'is_apu_off' => 'Involved APU-OFF',
            'is_ready_to_close' => 'Ready to close',
        ],
        [
            'links' => [
                'assignee_fullname' => "$php_self?m[0]=pdc&m[1]=listing&m[2]=byFactoryOpenStatusWithNoOpenTasks&x=ALL&y=ALL&assignee={$pdc->itsHeader['assignee']}&assignee_fullname=",
            ],
        ]
    );
    return $report->fetch();
}

function _getLinksView()
{
    global $pdc;
    $report = new tldReportColumnar(
        $pdc->getLinksFromHere(),
        [
            'xItems' => [
                'id' => 'ID#',
                'type' => 'Module',
                'item' => 'Ref#',
                'dsca' => 'Description',
            ],
            'title' => 'Links FROM Here...',
            'links' => [
                'id' => '/en/private/common/index.php?m[0]=links&m[1]=view&id=',
            ],
        ]
    );
    $body = $report->fetch();
    $report = new tldReportColumnar(
        $pdc->getLinksToHere(),
        [
            'xItems' => [
                'id' => 'ID#',
                'module' => 'Module',
                'parent_id' => 'Ref#',
                'dsca' => 'Description',
            ],
            'title' => 'Links TO Here...',
            'links' => [
                'id' => '/en/private/common/index.php?m[0]=links&m[1]=view&reversed=1&id=',
            ],
        ]
    );
    $body .= $report->fetch();
    return $body;
}

function _getFilesView()
{
    global $pdc;
    $report = new tldReportColumnar(
        $pdc->getFiles(),
        [
            'xItems' => [
                'id' => 'File#',
                'date' => 'Date',
                'description' => 'File Description',
            ],
            'links' => [
                'id' => '/en/private/common/index.php?m[0]=files&m[1]=view&m[2]=out&id=',
            ],
            'title' => 'Files',
        ]
    );
    return $report->fetch();
}

function _getPartsView()
{
    global $pdc;
    $report = new tldReportColumnar(
        $pdc->getParts(),
        [
            'xItems' => [
                'pn' => 'Part Number',
                'dsc' => 'Description',
            ],
            'title' => 'PDC Parts',
            'functions' => [
                'File' => [
                    'url' => "/en/private/manufacturing/eng/dev.php?m[0]=getfile&m[1]=drawing&erp={$pdc->itsHeader['factory_erp']}&date=&date=" . date('Y-m-d'),
                    'param' => ['item' => 'pn'],
                    'img' => '/shared/bluesphere/16x16/actions/filesaveas.png',
                ],
            ],
            'links' => [
                'pn' => "/en/private/manufacturing/eng/dev.php?m[0]=bom&m[1]=view&erp={$pdc->itsHeader['factory_erp']}&pn=",
            ],
            'showItemNumbers' => true,
        ]
    );
    return $report->fetch();
}

function _getPendingView()
{
    global $pdc;
    $body = null;
    $report = new tldAssocTable(
        $pdc->itsHeader,
        [
            'containment_action' => 'Containment actions',
        ]
    );
    $body .= $report->fetch();
    $body .= _getTasksViewByStatus('PENDING');
    return $body;
}

function _getInvestigationView()
{
    global $pdc;
    $body = null;

    $report = new tldAssocTable(
        $pdc->itsHeader,
        [
            'root_cause' => 'Final root cause',
        ]
    );
    $body .= $report->fetch();
    $body .= _getTasksViewByStatus('INVESTIGATION');
    return $body;
}

function _getActionView()
{
    global $pdc;
    $body = null;
    $report = new tldAssocTable(
        $pdc->itsHeader,
        [
            'corrective_action' => 'Corrective action',
            'preventive_action' => 'Preventive action',
        ]
    );
    $body .= $report->fetch();
    $body .= _getTasksViewByStatus('ACTION');
    return $body;
}

function _getClosureView()
{
    global $pdc;
    $body = null;
    if ($pdc->getStatus() === 'CLOSED') {
        $report = new tldAssocTable(
            $pdc->itsHeader,
            [
                'date_closed' => 'Closed date',
                'final_fweight' => 'Final FW',
                'resolution' => 'Resolution',
            ]
        );
        $body = $report->fetch();
    } elseif ($pdc->getStatus() === 'REJECTED') {
        $report = new tldAssocTable(
            $pdc->itsHeader,
            [
                'rejection_reason' => 'Reason for Rejecting',
            ]
        );
        $body = $report->fetch();
    } else {
        $body = '<p>Not closed yet</p>';
    }
    return $body;
}

function _getTasksViewByStatus($status)
{
    global $pdc;

    $data = $pdc->getModuleLinkToPdc($status);
    $statusTitle = ucfirst(strtolower($status));
    $report = new tldReportColumnar(
        array_merge($pdc->getStatusTasksByConstraints(['pdc_status' => $status]), $data),
        [
            'xItems' => [
                'id' => 'Task#',
                'status' => 'Status',
                'due_date' => 'Due date',
                'module' => 'Module',
                'task' => 'Task',
                'assignee_fullname' => 'Assignee',
            ],
            'links' => [
                'id' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=',
            ],
            'title' => "$statusTitle tasks",
        ]
    );
    return $report->fetch();
}

function _getAccordionIndexSectionToDisplay()
{
    global $pdc;
    switch ($pdc->getStatus()) {
        case 'INVESTIGATION':
            return 0;
        case 'ACTION':
            return 1;
        case 'CLOSED':
        case 'REJECTED':
            return 2;
    }
    return 'false';
}

function _getCountersView()
{
    global $pdc;
    $report = new tldReportColumnar(
        $pdc->getCountModuleLink(),
        [
            'xItems' => [
                'module' => 'Module',
                'count' => 'Count',
            ],
            'title' => 'Counters',
        ]
    );
    return $report->fetch();

}


// Handle buffer
return ob_get_clean();
