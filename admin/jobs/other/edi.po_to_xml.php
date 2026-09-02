<?php
include_once('erp.inc.php');
include_once('Mail.php');
include_once('Mail/mime.php');
include_once('common.inc.php');

ini_set('error_log', "$CRON_CACHE_DIR/edi.log");
ini_set('default_socket_timeout', 600);

$erps = [250, 300, 540, 600, 680];
foreach ($erps as $erp) {
    // Initiate connection with tldBaanSoapServer
    $baan_obj = new tldBaanERP($erp);
    $client = $baan_obj->getSOAP();
    try {
        $data = $client->EDI_createPOToXml($erp);
    } catch (\SoapFault $ex) {
        error_log('BAAN SOAP ERROR: ' . $ex->getMessage());
    }

    if (!empty($data)) {
        //echo '<pre>'.htmlentities(print_r($results,1)).'</pre>';
        foreach ($data as $location => $results) {
            switch ($location) {
                case 'Sage':
                    $_WSDL = 'https://edi2.sageparts.com/webservices/purchaseorders.asmx?WSDL';
                    $_CID = 'TLD';
                    $_MYTYPE = 'PO';
                    $client = _getSOAP($_WSDL);
                    foreach ($results as $result) {
                        try {
                            // Log Xml Request
                            insertEdiLog($result['transactionid'], 'PO', 'cXML', 'OUT', $result['xml'], 'TLD', 'INTERNAL BAAN SOAP SERVER', 'SAGE SOAP SERVER');
                            $r = $client->OrderRequest(['CID' => $_CID, 'MTYPE' => $_MYTYPE, 'cXML' => $result['xml']]);

                        } catch (Exception $e) {
                            error_log($e->faultstring);
                        }
                        // Log Xml Response
                        insertEdiLog($result['transactionid'], 'PO', 'cXML', 'IN', $r->OrderRequestResult, $location, 'SAGE SOAP SERVER', 'EDI PHP');
                    }
                    break;
            }
        }
    } else {
        error_log("No PO to be sent for $erp");
    }
}

function _getSOAP($wsdl)
{
    static $client;

    if (!$client) {
        try {
            $client = new \SoapClient($wsdl);
        } catch (\SoapFault $e) {
            error_log('SOAP-ERROR: ' . $e->getMessage());
            tldUtils::emailAttachment('devteam@tld-america.com', 'noreply@tld-gse.com', '[ADMIN] SOAP', $e->getMessage());
        }
    }

    return $client;
}

function insertEdiLog($transactionId, $transactionType, $messageType, $messageFlow, $messageContent, $sourceName, $from, $to)
{
    $query = <<<EOF
	INSERT INTO edi_logs (
		`transaction_id`,
		`transaction_type`,
		`message_type`,
		`message_flow`,
		`message_content`,
		`source_name`,
		`from`,
		`to`,
		`dt`,
		`message_id`
	)
	SELECT
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		NOW(),
		IFNULL(MAX(message_id), 0) + 1
	FROM
		edi_logs
	LIMIT 1
EOF;
    $query = sprintf($query,
        TldDatabase::escape($transactionId),
        TldDatabase::escape($transactionType),
        TldDatabase::escape($messageType),
        TldDatabase::escape($messageFlow),
        TldDatabase::escape($messageContent),
        TldDatabase::escape($sourceName),
        TldDatabase::escape($from),
        TldDatabase::escape($to)
    );
    return tldUtils::sqlInsert($query);
}
