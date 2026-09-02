<?php
include_once 'common.inc.php';
include_once 'erp.inc.php';
error_log('RUNNING OUTBOUND SCRIPT');

$now = new \DateTimeImmutable('1 hour ago');
$start = $now->setTime((int)$now->format('H'), 0);
$end = $start->modify('+1 hour');

// outbound message/s from baan/tld table
$query = <<<EOF
SELECT t1.*
FROM tld..tldQueue as t1
WHERE dtcr >= '{$start->format('Y-m-d H:i:s')}' AND dtcr < '{$end->format('Y-m-d H:i:s')}'
EOF;
//echo $query;
$rows = tldUtils::getSqlToAssocArray(
    $query,
    'odbc',
    [
        'src' => 'baan',
    ]
);
foreach ($rows as $row) {
    $src = $row['erp'];
    $dest = $row['erp'];
    switch (trim($row['acta'])) {
        case 'CancelOrder':
            $r = tldBaanERP::getOutboundRelation(
                $row['erp'],
                $row['t_cuno']
            );
            error_log("Cancel Order...$src - " . $row['num'] . " - $dest\n");
            $destERP = tldERP::getERPOb($dest);
            //get original po
            $destERP->cancelWholeOrder($src, $row['num']);
            //send the message
            break;
        case 'EDIERROR':
            error_log("EDI Error...$src - " . $row['num'] . " -> $dest cuno->" . $row['t_cuno'] . "\n");
            error_log("EDI Error...$src - " . $row['num'] . " -> $dest cuno->" . $row['t_cuno'] . "\n",
                1,
                'mis@tld-america.com,gary.edbrooke@tld-america.com,yoann.pelette@tld-europe.com,guchenyang@tld-asia.com'
            );
            break;
    }
}


////////////////////////////////////
// Process outbound XML docs from erp_archive.//
////////////////////////////////////
$query = <<<EOF
SELECT *
FROM erp_archive
WHERE dt >= '{$start->format('Y-m-d H:i:s')}' AND dt < '{$end->format('Y-m-d H:i:s')}'
    AND
    (
    (doc_type='PURCHASE ORDER' AND dest IN (390))
    OR (doc_type='SALES ORDER ACK' AND dest IN (800))
    OR (doc_type='PACKING SLIP' AND dest IN (800))
    )
EOF;
$rows = tldUtils::getSqlToAssocArray($query);

foreach ($rows as $row) {
    $ERROR = '';
    switch ($row['doc_type']) {
        case 'SALES ORDER ACK':
            error_log(date('c') . " : Processing SOACK#${row['num']} ${row['erp']} ${row['dest']}\r\n");
            $r = tldERP::transmitSalesOrderAck(
                $row['erp'],
                $row['xml'],
                $row['dest']
            );
            $ERROR .= is_bool($r) && $r ? "SO ACK sent ${row['erp']} ${row['dest']}\r\n" : "$r \r\n";

            error_log($ERROR . date('c') . " : END of Processing SOACK SO#${row['num']}\r\n");
            break;
        case 'PACKING SLIP':
            error_log(date('c') . " : Processing DN#${row['num']} ${row['erp']} ${row['dest']}\r\n");
            $r = tldERP::transmitDeliveryNote(
                $row['erp'],
                $row['xml'],
                $row['dest']
            );
            $ERROR .= is_bool($r) && $r ? "DN sent ${row['erp']} ${row['dest']}\r\n" : "$r \r\n";

            error_log($ERROR . date('c') . " : END of Processing DN SO#${row['num']}\r\n");
            break;
        case 'PURCHASE ORDER':
            error_log(date('c') . " : Processing PO#${row['num']} ${row['erp']} ${row['dest']}\r\n");
            $r = tldERP::transmitPurchaseOrder(
                $row['erp'],
                $row['xml'],
                341
//                $row['dest']
            );
            $ERROR .= is_bool($r) && $r ? "PO sent to ${row['erp']} ${row['dest']}\r\n" : "$r \r\n";

            error_log($ERROR . date('c') . " : END of Processing PO${row['num']}\r\n");
            break;
    }
}

