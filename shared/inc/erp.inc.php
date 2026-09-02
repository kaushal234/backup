<?php
/**
 * ERP related classes
 *
 * All classes related to ERP connectivity are kept in this file
 *
 * @access public
 * @author Graham K.L. Fong <graham.fong@tld-america.com>
 * @copyright TLD
 *
 * @package ERP
 */

/**
 * Required files
 */
include_once('erp.others.inc.php');
include_once('product_support.inc.php');
include_once('finance.inc.php');
require_once('XML/Unserializer.php');
include_once('Mail/mime.php');
include_once('user.inc.php'); // tldVendor (Vcard)
include_once('calendar.inc.php'); // used by the tldAP. class

/**
 * Controller class for accessing data in arbitrary ERP systems
 *
 * Currently have interface to Baan, Eureka, Aeroxchange erp
 *
 * @package ERP
 */
class tldERP
{
    /** @var string[]  */
    public $theERPS;    //array of allowed erp numbers

    public function __construct()
    {
        $this->theERPS = [
            '200', '250', '300', '310', '330', '400', '410', '420', '500', '510', '520', '540', '560', '570', '600', '610', '620', '640', '660', '680',
        ];
    }

    /**
     * Get an correct erp object for specified company number
     *
     * 800 series is for external erp systems
     *
     * @param integer $erp company number
     */
    public static function getERPOb($erp)
    {
        // For TLD eParts:
        if ($erp >= 9000 && $erp <= 9999) {
            $erp2 = $erp - 9000;
            $erp = 100;
        }
        switch (self::whatERP($erp)) {
            case 'prophet21':
                $result = new erpProphet21($erp);
                break;
            case 'baan':
                $result = new tldBaanERP($erp);
                break;
            case 'aero':
                $result = new erpAero($erp);
                break;
            default:
                $result = '';
        }
        return $result;
    }

    /**
     * Tells what ERP system that corresponds to the company number
     *
     * @param company number $erp
     * @return string
     */
    public static function whatERP($erp)
    {
        switch ($erp) {
            case '200': //actiparts
            case '220': //Power Vamp
            case '250': //Aeros Specialties
            case '251': //Aeros Specialties TEST
            case '300': //tld america
            case '303': //tld america TEST
            case '310': // TLD LAC
            case '320': // TLD NAM
            case '330': //tld america BOEING service contract
            case '400': // TLD WIN / TLD WIC
            case '410': // TLD WIM
            case '420': //canada
            case '403': //test environment
            case '500': //montlouis
            case '510': //dtv
            case '520': //stlin
            case '540': //tld europe SSO
            case '560': //tld meai SSO
            case '570': // LEBRUN
            case '580': // TLD OVH
            case '600': //tld asia SSO
            case '610': // SINGAPORE SSO
            case '620': // GROUP SOURCING
            case '640': // TLD SHA
            case '660': // TLD WUX
            case '680': // SHANGHAI SSO
            case '700': // TLD JST
                return 'baan';
            case '800': //800 series is for external erp systems
                return 'aero';
            case '390': //prod
            case '391': //test
                return 'prophet21';
        }
        return '';

    }

    /**
     * Get array of item data from all erps
     *
     * @return array
     */
    public function getItemData($id)
    {
        $result = [];
        foreach ($this->theERPS as $erpid) {
            $erp = self::getERPOb($erpid);
            $r = $erp->getItemData($id);
            if (!empty($r)) {
                $result[] = $r;
            }
        }
        return $result;
    }

    /**
     * Search cached data for item data
     *
     * @return array db rows
     */
    public static function searchCachedItemDataByPN($ident, $options = '')
    {
        $query = <<<EOF
		SELECT parts.*, locations.region, locations.location
		FROM parts LEFT JOIN locations ON parts.ERP=locations.erp
		WHERE
EOF;
        $ids = (array) $ident;
        $w = [];
        foreach ($ids as $id) {
            if (isset($options['searchType']) && $options['searchType'] === 'exactMatch') {
                $w[] = " RTRIM(ITEM)=RTRIM('$id')";
            } else {
                $w[] = <<<EOF
RTRIM(ITEM)=RTRIM('$id')
OR RTRIM(ITEM) like CONCAT('__', RTRIM('$id'))
OR RTRIM(ITEM) like CONCAT('___', RTRIM('$id'))
EOF;
            }
        }
        $query .= implode(' OR ', $w);
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Search in realtime for item data
     * @param int $id PN
     * @return array db rows
     */
    public static function searchItemDataByPN($id, $options = '')
    {
        $ls = ['300', '400', '410', '420', '500', '520', '540', '570', '600', '620', '640', '680', '660'];
        $result = [];
        foreach ($ls as $erpid) {
            $erp = self::getERPOb($erpid);
            $result[] = $erp->searchItemDataByPN($id, $options);
        }
        return array_merge(...$result);
    }

    /**
     * Checks whether the specified transaction is allowed between the source
     * and destination ERP systems
     *
     * Checks for special transaction type called ALL first. ALL means all transactions
     * are allowed between the two ERPS and in both directions
     *
     * @param int $src source company number
     * @param string $ttyp transaction type
     * @param integer $dest destination company number
     * @return boolean true if transaction is allowed
     */
    public function isTransAllowed($src, $ttyp, $dest)
    {
        //check if ALL transactions are allowed
        $query = <<<EOF
SELECT t1.*
FROM erp_int AS t1, locations as t2, locations as t3
WHERE t1.src=t2.id
    AND t1.dst=t3.id
    AND t2.erp=$src
    AND t1.ttyp='ALL'
    AND t3.erp=$dest
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);
        if (count($rows)) {
            return true;
        }

        $query = <<<EOF
SELECT t1.*
FROM erp_int AS t1, locations as t2, locations as t3
WHERE t1.src=t2.id
    AND t1.dst=t3.id
    AND t2.erp=$src
    AND t1.ttyp='$ttyp'
    AND t3.erp=$dest
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);
        return count($rows) > 0;
    }

    /**
     * Perform a purchase order transaction
     *
     * @param integer $src company number
     * @param array $xml xml structure with document data
     * @param integer $dest company number
     * @param array $opts options parameters
     * @return boolean true if transaction was successful
     */
    public static function transmitPurchaseOrder($src, $xml, $dst, $opts = '')
    {
        $srcERP = self::getERPOb($src);
        $destERP = self::getERPOb($dst);
        // Convert XML to tld array
        $a = $srcERP->outPurchaseOrder($xml);
        // Check if the MSG option is set
        if (isset($opts['MSG'])) {
            return tldERPMSG::insert(
                [
                    'erp_from' => $src,
                    'erp_to' => $dst,
                    'doc_type' => 'SALES ORDER',
                    'status' => 'PENDING',
                    'eparts_order' => $opts['EPARTS_ORDER'],
                    'pono' => $opts['PONO'],
                    'data' => $xml,
                ]
            );
        }
        // Submit to dest erp
        $error = $destERP->inPurchaseOrder($a);
        if ($error === 0) {
            tldArchive::insert($src, 'PURCHASE ORDER', $a['TLDHEADER']['POID'], date('Y-m-d'), '', $dst, $xml, '');
            error_log('transmitPurchaseOrder: order was ok');

            return 0;
        }

        error_log("transmitPurchaseOrder: problem with order. $error");
        return "transmitPurchaseOrder: problem with order. $error";
    }

    /**
     * Perform a sales order transaction
     *
     * @param integer $src company number
     * @param array $xml xml structure with document data
     * @param integer $dest company number
     * @param array $opts options parameters
     * @return mixed boolean true if transaction was successful, string with ERROR
     */
    public function transmitSalesOrder($src, $xml, $dest, $opts = '')
    {
        //		if(!tldERP::isTransAllowed($src, "SalesOrder", $dest)){
        //			return "ERROR: SalesOrder transacation not allowed";
        //		}
        $srcERP = self::getERPOb($src);
        $destERP = self::getERPOb($dest);
        //convert to tld array
        $a = $srcERP->outSalesOrder($xml);
        //submit to dest erp
        $result = $destERP->inSalesOrder($a);
        return is_string($result) ? $result : true;
    }

    /**
     * Perform a sales order acknowledgement transaction
     *
     * @param integer $src company number
     * @param array $xml xml structure with document data
     * @param integer $dest company number
     * @param array $opts options parameters
     * @return mixed boolean true if transaction was successful, string with ERROR
     */
    public static function transmitSalesOrderAck($src, $xml, $dest, $opts = '')
    {
        //		if(!tldERP::isTransAllowed($src, "SalesOrderAck", $dest)){
        //			return "ERROR: SalesOrderAck transacation not allowed";
        //		}
        $srcERP = self::getERPOb($src);
        $destERP = self::getERPOb($dest);
        //convert to tld array
        $a = $srcERP->outSalesOrderAck($xml);
        //get original po from archive
        $arch = new tldArchive($dest);
        //get the last one
        $rows = $arch->byTypeID(
            'PURCHASE ORDER', $a['TLDHEADER']['PONUM']
        );
        if (empty($rows)) {
            return false;
        }
        //submit to dest erp
        $result = $destERP->inSalesOrderAck($a, $rows[0]['xml']);

        if (is_string($result)) {
            $response = $result;
        }
        tldArchive::insert(
            $src, 'SALES ORDER ACK', $a['TLDHEADER']['SONUM'],
            date('Y-m-d'), '', $dest, $xml, $response
        );
        return $response ?: $result;
    }

    /**
     * Perform a purchase order transaction
     *
     * @param integer $src company number
     * @param array $xml xml structure with document data
     * @param integer $dest company number
     * @param array $opts options parameters
     * @return mixed boolean true if transaction was successful, string with ERROR
     */
    public function transmitInvoice($src, $xml, $dest, $opts = '')
    {
        //		if(!tldERP::isTransAllowed($src, "Invoice", $dest)){
        //			return "ERROR: Invoice transacation not allowed";
        //		}
        $srcERP = self::getERPOb($src);
        $destERP = self::getERPOb($dest);
        //convert to tld array
        $a = $srcERP->outInvoice($xml);
        //submit to dest erp
        $result = $destERP->inInvoice($a);
        return is_string($result) ? $result : true;
    }

    /**
     * Perform a purchase order transaction
     *
     * @param integer $src company number
     * @param array $xml xml structure with document data
     * @param integer $dest company number
     * @param array $opts options parameters
     * @return mixed boolean true if transaction was successful, string with ERROR
     */
    public static function transmitDeliveryNote($src, $xml, $dest, $opts = '')
    {
        //		if(!tldERP::isTransAllowed($src, "DeliveryNote", $dest)){
        //			return "ERROR: DeliveryNote transacation not allowed";
        //		}
        $srcERP = self::getERPOb($src);
        $destERP = self::getERPOb($dest);
        //convert to tld array
        $a = $srcERP->outDeliveryNote($xml);
        //submit to dest erp
        //get original po from archive
        $arch = new tldArchive($dest);
        $rows = $arch->byTypeID(
            'PURCHASE ORDER', $a['TLDHEADER']['PONUM']
        );
        //get the last one
        $result = $destERP->inDeliveryNote($a, $rows[0]['xml']);
        //tldArchive::insert($src, 'DeliveryNote', $a['TLDHEADER']['SONUM'],
        //date("Y-m-d"), '',$dst, $xml);
        return is_string($result) ? $result : true;
    }

    /**
     * Get array of partners from xml
     *
     * @param simpleXML $x
     * @return array
     */
    public static function parsePartnersSection($x)
    {
        foreach ($x as $partner) {
            $partners[(string)$partner->PARTNRTYPE] = (array)$partner;
        }
        return $partners;
    }
}


/**
 *    Class for accessing data in the Baan ERP system
 *
 * @package ERP
 */
class tldBaanERP
{
    public $itsERP;        // baan company number
    public $itsHeader;        // List all common data for the company
    //account details in wiseb2b website
    public $theAuthDetails;
    public $theRelations;    //array of relation data

    /**
     * Constructor
     * @param integer $erp company number
     */
    public function __construct($erp, bool $lazy = true)
    {
        $this->itsERP = $erp;
        $this->theAuthDetails = $GLOBALS['cfg']['erp']['baan']['accounts'];
        $this->itsHeader = $lazy ? [] : $this->getHeader();
    }

    /**
     * Get BAAN company header
     * @return array Array of db rows
     */
    public function getHeader()
    {
        if (empty($this->itsERP)) {
            return [];
        }
        $query = <<<EOF
SELECT *
FROM ttccom000300
WHERE t_ncmp=$this->itsERP
EOF;
        return tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public function isEmpty()
    {
        return ($this->theAuthDetails);
    }

    /**
     * Get BAAN SOAP client object
     * @return SoapClient object
     */
    public function getSOAP()
    {
        global $cfg;
        $client = new SoapClient(
            null,
            [
                'location' => $cfg['soap']['location'],
                'uri' => $cfg['soap']['uri'],
                'trace' => 1,
                'keep_alive' => false,
            ]
        );
        return $client;
    }

    /**
     * Get inbound relationship data
     *
     * If src and dst are specified then then only data for that relationship is
     * give, otherwise all relationship info is returned
     *
     * @param int $src source company number
     * @param int $dst destination company number
     * @return array
     */
    public static function getInboundRelation($src = '', $dst = '')
    {
        $a = [
            800 => [
                300 => ['t_cuno' => 280],
                303 => ['t_cuno' => 280],
                390 => ['t_cuno' => 676],
            ],
        ];

        return ($src && $dst) ? $a[$src][$dst] : $a;
    }

    /**
     * Get outbound relationship data
     *
     * Determines how the src company is represented in the dest company
     *
     * If src and dst are specified then then only data for that relationship is
     * give, otherwise all relationship info is returned
     *
     * @param integer $src source company number
     * @param integer $dst destination company number
     * @return array
     */
    public static function getOutboundRelation($src = '', $dst = '')
    {
        $a = [
            300 => [
                280 => ['erp' => 800, 'sender' => 29116, 'receiver' => 29073],
                11219 => ['erp' => 390, 'sender' => 'www.tld-gse.com', 'receiver' => 123456789],
            ],
            303 => [280 => ['erp' => 800, 'sender' => 29116, 'receiver' => 29073]],
        ];

        return ($src && $dst) ? $a[$src][$dst] : $a;

    }

    public function inDeliveryNote($a)
    {

    }

    public function inInvoice($a)
    {

    }

    public function getHost()
    {
        switch ($this->itsERP) {
            //Production environment
            case '250':
            case '300':
            case '400':
            case '410':
            case '420':
            case '500':
            case '510':
            case '520':
            case '540':
            case '570':
            case '600':
            case '700':
                $result = ['ip' => '192.111.1.192', 'port' => '18010'];
                break;
            case '303':
            case '403':
                //Test environment
                $result = ['ip' => '192.111.1.192', 'port' => '18011'];
                break;
            case '620':
            case '640':
            case '660':
            case '680':
                //Chinese production environment
                $result = ['ip' => '192.111.1.192', 'port' => '18012'];
                break;
        }
        return $result;
    }

    /**
     * Input purchase order into erp system
     *
     * @param array $a po in TLD Array format
     * @return mixed string if an error otherwise true
     *
     * Structure of the TLD array
     * <code>
     *    $a = array(
     *        "header"=>array(
     *            "t_cuno"=>"TL0000",
     *            "t_eono"=>"YOYO2",
     *            "t_ddat"=>"2011-04-22",
     *            "t_cdel"=>"FGR",
     *            "t_cfrw"=>"",
     *            "t_scom"=>"",
     *            "t_nama"=>"",
     *            "t_namb"=>"",
     *            "t_namc"=>"",
     *            "t_namd"=>"",
     *            "t_name"=>"",
     *            "t_namf"=>"",
     *            "note"=>"SO de test",
     *            "erp"=>502,
     *            "datetime"=>date('Y-m-d H:i:s')
     *        ),
     *        "lines"=>array(
     *            array(
     *                "t_item"=>"0099-0",
     *                "t_oqua"=>2
     *            )
     *        )
     *    );
     * </code>
     *
     */
    public function inPurchaseOrder($a)
    {
        // Fixup the date for baan input
        if (empty($a['header']['t_odat'])) {
            $a['header']['t_odat'] = date('Y-m-d');
        } else {
            $a['header']['t_odat'] = date('Y-m-d',
                tldUtils::dateToTimestamp($a['header']['t_odat']));
        }
        // Fixup the t_cdel address for baan input
        if (empty($a['header']['deliverAddress']['t_cdel'])) {
            $a['header']['deliverAddress']['t_cdel'] = 'SPECIAL';
        }
        // Check where the PO is coming from
        $src = $a['TLDHEADER']['COMP'];
        // Get inbound relationship data
        $t = self::getInboundRelation($src, $this->itsERP);
        // Convert customer number
        if (isset($t['t_cuno'])) {
            $a['header']['customer']['t_cuno'] = $t['t_cuno'];
        }
        // Get ERP BAAN connector and transmit
        $soap = $this->getSOAP();
        try {
            $r = $soap->postSO($a);
        } catch (Exception $ex) {
            $r = $ex->faultstring;
        }
        // In case of error
        if (is_string($r)) {
            error_log('inPurchaseOrder: ' . $r);
            return $r;
        }

        error_log("inPurchaseOrder: PO sent from $src to ERP" . $this->itsERP);
        return 0;
    }


    public function inSalesOrder($a)
    {

    }

    public function inSalesOrderAck($a)
    {

    }

    public function inItemPriceRequest($a)
    {
        $soap = $this->getSOAP();
        try {
            $r = $soap->getItemPriceRequest($a);
        } catch (Exception $ex) {
            $r = $ex->faultstring;
        }
        // In case of error
        if (!is_numeric($r)) {
            error_log('inItemPriceRequest: ' . $r);
        } else {
            error_log("inItemPriceRequest: Request sent to {$a['erp']} filename: " . $this->itsERP);
        }
        return $r;
    }

    public function inMultiCompItemPriceRequest($a)
    {
        $soap = self::getSOAP();
        try {
            $r = $soap->getMultiCompItemPriceRequest($a);
        } catch (Exception $ex) {
            $r = $ex->faultstring;
        }
        // In case of error
        if (!is_numeric($r)) {
            error_log('inMultiCompItemPriceRequest: ' . $r);
        } else {
            error_log('inMultiCompItemPriceRequest: Request sent');
        }
        return $r;
    }

    /**
     * Convert baan DELIVERY NOTE to TLD array format
     *
     *
     * <code>
     *
     * // xml from baan
     *
     * <?xml version="1.0" encoding ="GB2312"?>
     *<REPORT>
     *<TLDHEADER>
     *<COMP>403</COMP><TYPE>PROCESS_PKG_SLIP</TYPE><LANG>EN</LANG>
     *</TLDHEADER>
     *<PROCESS_PKG_SLIP>
     *<DATAAREA>
     *
     *<PROCESS_PS>
     *<PSORDERHDR>
     *<t_header.text>
     *</t_header.text>
     *<PARTNER> <PARTNERTYPE>CUSTOMER</PARTNERTYPE><ONETIME>0</ONETIME>
     *<t_tcmcs019.dsca>                              </t_tcmcs019.dsca>
     *<NAME index="1">United Parcel Serive               </NAME>
     *<ADDRESS>
     *<ADDRLINE index="1">2455 Michigan Ave             </ADDRLINE>
     *<ADDRLINE index="2">Mobile, AL 36615              </ADDRLINE>
     *<ADDRLINE index="3">                              </ADDRLINE>
     *<ADDRLINE index="4">                              </ADDRLINE>
     *<ADDRLINE index="5">                              </ADDRLINE>
     *<ADDRLINE index="6">                              </ADDRLINE>
     *<ADDRLINE index="7">                              </ADDRLINE>
     *<ADDRLINE index="8">                              </ADDRLINE>
     *</ADDRESS>
     *</PARTNER>
     *
     *<PARTNER> <PARTNERTYPE>SOLDTO</PARTNERTYPE><ONETIME>0</ONETIME>
     *<t_form.text>Delivery Address</t_form.text>
     *<NAME index="1">United Parcel Service              </NAME>
     *<ADDRESS>
     *<ADDRLINE index="1">GSE Automotive                </ADDRLINE>
     *<ADDRLINE index="2">6200 Lockheed                 </ADDRLINE>
     *<ADDRLINE index="3">Anchorage,  AK  99502         </ADDRLINE>
     *<ADDRLINE index="4">                              </ADDRLINE>
     *<ADDRLINE index="5">                              </ADDRLINE>
     *<ADDRLINE index="6">                              </ADDRLINE>
     *</ADDRESS>
     *</PARTNER>
     *
     *<PARTNERID>629</PARTNERID>
     *<DATETIME qualifier="DOCUMENT">11-15-07</DATETIME>
     *<FWDAGENT>                              </FWDAGENT>
     *<PSID>12593</PSID>
     *<SOID>520123</SOID>
     *<DATETIME qualifier="DOCUMENT">11-13-2007</DATETIME>
     *<REFA>reference AAAAA               </REFA>
     *<REFB>reference BBBBB     </REFB>
     *<HEADERTEXT>this is header text
     *           and this is header text line 2.
     *</HEADERTEXT>
     *
     *</PSORDERHDR>
     *<DETAIL>
     *<LINE>
     *<ITEM>1000006</ITEM>
     *<CNTR>   </CNTR>
     *<ORDQTY>1.0000</ORDQTY>
     *<DELQTY>1.0000</DELQTY>
     *<UOM>EA</UOM>
     *<BOQTY></BOQTY>
     *<ITEMX></ITEMX>
     *<DESCRIPTN>COUPLING ASSEMBLY</DESCRIPTN>
     *<ITEMX></ITEMX>
     *<LINETEXT>this is sales order line text
     *          this goes just after the line
     *          it is associated with
     *          and before the next line.
     *</LINETEXT>
     *</LINE>
     *</DETAIL>
     *<PSORDERFTR>
     *<FOOTERTEXT>this is SO footer text
     *           that goes at the bottom of the order
     *</FOOTERTEXT>
     *<DELTERMS>UPS Ground, PPA, FOB Origin            </DELTERMS>
     *<OURTAXID>06-0683261          </OURTAXID>
     *<YOURTAXID>                    </YOURTAXID>
     *</PSORDERFTR>
     *</PROCESS_PS>
     *</DATAAREA>
     *</PROCESS_PKG_SLIP>
     *</REPORT>
     *</code>
     *
     * @param string $xmlstring
     * @return array
     */
    public function outDeliveryNote($xmlstr)
    {
        return tldUtils::convXMLToArray(
            $xmlstr,
            [
                'complexType' => 'array',
                'forceEnum' => ['LINE'],
            ]
        );
    }

    public function outInvoice($xmlstr)
    {
        return tldUtils::convXMLToArray($xmlstr);
    }

    public function outPurchaseOrder($xmlstr)
    {
        return tldUtils::convXMLToArray(
            $xmlstr,
            [
                'complexType' => 'array',
                'forceEnum' => ['LINE'],
            ]
        );
    }

    public function outSalesOrder($xmlstr)
    {
        $xml = new SimpleXMLElement($xmlstr);
        return (array)$xml;
    }

    /**
     * Convert baan sales order acknowledgement to TLD array format
     *
     * <code>
     *
     * // xml coming from baan:
     *
     * <?xml version="1.0" encoding ="GB2312"?>
     *<REPORT>
     *<TLDHEADER>
     *<COMP>403</COMP><TYPE>A</TYPE><LANG>EN</LANG>
     *</TLDHEADER>
     *<PROCESS_SO_ACK>
     *<DATAAREA>
     *<PROCESS_SO>
     *<SOORDERHDR>
     *<t_header.text>
     *</t_header.text>
     *<PARTNER>
     *<PARTNRTYPE>CUSTOMER</PARTNRTYPE>
     *<ONETIME>0</ONETIME>
     *<t_tcmcs019.dsca></t_tcmcs019.dsca>
     *<NAME index="1">United Parcel Serive</NAME>
     *<ADDRESS>
     *<ADDRLINE index="1">2455 Michigan Ave             </ADDRLINE>
     *<ADDRLINE index="2">Mobile, AL 36615              </ADDRLINE>
     *<ADDRLINE index="3">                              </ADDRLINE>
     *<ADDRLINE index="4">                              </ADDRLINE>
     *<ADDRLINE index="5">                              </ADDRLINE>
     *<ADDRLINE index="6">                              </ADDRLINE>
     *<ADDRLINE index="7">                              </ADDRLINE>
     *<ADDRLINE index="8">                              </ADDRLINE>
     *</ADDRESS>
     *</PARTNER>
     *<PARTNER> <PARTNERTYPE>SOLDTO</PARTNERTYPE><ONETIME>0</ONETIME>
     *<t_form.text>Delivery Address</t_form.text>
     *<NAME index="1">United Parcel Service</NAME>
     *<ADDRESS>
     *<ADDRLINE index="1">GSE Automotive                </ADDRLINE>
     *<ADDRLINE index="2">6200 Lockheed                 </ADDRLINE>
     *<ADDRLINE index="3">Anchorage,  AK  99502         </ADDRLINE>
     *<ADDRLINE index="4">                              </ADDRLINE>
     *<ADDRLINE index="5">                              </ADDRLINE>
     *<ADDRLINE index="6">                              </ADDRLINE>
     *</ADDRESS>
     *</PARTNER>
     *<DATETIME qualifier="DOCUMENT">Windsor, CT  06095, 11-13-07</DATETIME>
     *<PARTNERID>629   </PARTNERID>
     *<SOID>520123</SOID>
     *<DATETIME qualifier="DOCUMENT">11-13-2007</DATETIME>
     *<CUSTPO>Cust PO No XYZ-123</CUSTPO>
     *<REFA>reference AAAAA</REFA>
     *<REFB>reference BBBBB</REFB>
     *<HEADERTEXT>this is header text
     *and this is header text line 2.
     *</HEADERTEXT>
     *</SOORDERHDR>
     *<DETAIL>
     *<LINE>
     *<SOLINENUM>1</SOLINENUM>
     *<QUANTITY>1.0000</QUANTITY>
     *<UOM>EA</UOM>
     *<ITEM>1000006</ITEM>
     *<CNTR></CNTR>
     *<PRIC>1139.9400</PRIC>
     *<UOMP>EA</UOMP>
     *<CVAT>Out/</CVAT>
     *<DISC></DISC>
     *<PERC></PERC>
     *<DMTH></DMTH>
     *<ASTRX></ASTRX>
     *<DDTB>11-13-2007</DDTB>
     *<EXTPRICE>1139.94</EXTPRICE>
     *<ITEMX></ITEMX>
     *<DESCRIPTN>COUPLING ASSEMBLY</DESCRIPTN>
     *<LINETEXT>this is sales order line text
     *this goes just after the line
     * it is associated with
     *and before the next line.
     *</LINETEXT>
     *</LINE>
     *<LINE>
     *<SOLINENUM>2</SOLINENUM>
     *<QUANTITY>3.0000</QUANTITY>
     *<UOM>EA</UOM>
     *<ITEM>1000006#2</ITEM>
     *<CNTR></CNTR>
     *<PRIC>1139.9400</PRIC>
     *<UOMP>EA</UOMP>
     *<CVAT>Out/</CVAT>
     *<DISC></DISC>
     *<PERC></PERC>
     *<DMTH></DMTH>
     *<ASTRX></ASTRX>
     *<DDTB>11-13-2007#2</DDTB>
     *<EXTPRICE>1139.94</EXTPRICE>
     *<ITEMX></ITEMX>
     *<DESCRIPTN>COUPLING ASSEMBLY</DESCRIPTN>
     *<LINETEXT>this is sales order line text
     *this goes just after the line
     *it is associated with
     *and before the next line. #2
     *</LINETEXT>
     *</LINE>
     *</DETAIL>
     *<SOORDERFTR>
     *<FOOTERTEXT>this is SO footer text
     *that goes at the bottom of the order
     *</FOOTERTEXT>
     *<CURRENCY>USD</CURRENCY>
     *<GOODS>       1139.94</GOODS>
     *<DISC>          </DISC>
     *<COST>          </COST>
     *<LATE>          </LATE>
     *<CVAT>          </CVAT>
     *<CVA2>          </CVA2>
     *<CVA3>          </CVA3>
     *<INVO>       1139.94</INVO>
     *<CVATPERC2>     </CVATPERC2>
     *<CVATPERC3>     </CVATPERC3>
     *<DELTERMS>UPS Ground</DELTERMS>
     *<PAYTERMS>Net 30 Days</PAYTERMS>
     *</SOORDERFTR>
     *</PROCESS_SO>
     *</DATAAREA></PROCESS_SO_ACK>
     * </REPORT>
     *    </code>
     *
     * @param string $xmlstring
     * @return array
     */
    public function outSalesOrderAck($xmlstr)
    {
        return tldUtils::convXMLToArray($xmlstr,
            ['complexType' => 'array',
                'forceEnum' => ['LINE'],
            ]
        );
    }

    /**
     * Create login xml message
     *
     * @return string
     */
    public function login()
    {
        $account = $this->theAuthDetails[$this->itsERP]['account'];
        $user = $this->theAuthDetails[$this->itsERP]['user'];
        $password = $this->theAuthDetails[$this->itsERP]['pw'];

        $message = <<<EOF
<login type="DETACHABLE">
	<parameters>
		<username>$user</username>
		<password>$password</password>
	</parameters>
</login>
EOF;

        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $host = $this->getHost();
        $e = socket_connect($socket, $host['ip'], $host['port']);
        error_log('Connecting to ' . $host['ip'] . ' ' . $host['port']);
        // If no connection, get error and send notification to MIS
        if (!$e) {
            $msg = 'ERROR socket_connect() >> ' . socket_strerror(socket_last_error($socket));
            error_log($msg);
            tldUtils::emailAttachment(
                'mis@tld-america.com,mis@tld-europe.com,mis@tld-asia.com',
                'noreply@tld-gse.com',
                '[ERROR] Socket connection',
                $msg . '<br>Please check/restart wise server'
            );
        }
        socket_write($socket, $message);
        $response = trim(socket_read($socket, 1024));
        socket_close($socket);
        $x = new simpleXMLElement($response);
        //		$this->parse($response, $vals, $index);
        if ($x->getName() === 'FAULT') {
            error_log($x->dispatcher->message);
            return false;
        } else {
            //		return $vals[$index["TOKEN"][0]]["value"];
            return (string)$x->dispatcher->token;
        }
    }

    /**
     * Method for POSTING data to baan using WISE or SOAP
     *
     * Wise XML:
     * <code>
     *  <request>
     *    <method>com.wiseb2b.xml.CreateOrder</method>
     *    <token>xxxx</token>    <!-- see documentation; retrieved from login method -->
     *    <parameters>
     *        <!-- XML MESSAGE PAYLOAD GOES HERE -->
     *    </parameters>
     *</request>
     *</code>
     *
     * @param string op Name of operation to perform
     * @param string xml Xml of document to send
     *
     * Wise POST:
     * @return string XML returned confirmation from wise
     * or false if operation is not in the allowed list
     *
     * SOAP POST:
     * @return string if error
     */
    public function post($op, $xml = '')
    {
        switch ($op) {
            case 'UPPODDAT':
            case 'TGLAPBLOC':
            case 'TEXT_ADDTRAN':
            case 'TEXT_ADD':
                $smarty = tldUtils::getSmarty('common');
                //do login
                $token = $this->login();
                if (empty($token)) {
                    error_log('ERROR: No token returned.');
                    return false;
                }
                error_log("Got wise token $token");
                $smarty->assign('wiseToken', $token);

                $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
                $host = $this->getHost();
                socket_connect($socket, $host['ip'], $host['port']);
                error_log('Connecting to ' . $host['ip'] . ' ' . $host['port']);
                //set the operation in the message
                $smarty->assign('wiseMethod', $op);
                //add the payload to the message
                $smarty->assign('wiseParameters', $xml);
                //create the message
                $request = $smarty->fetch('erp/wise.xml.message.tpl');
                //send the message
                socket_write($socket, $request);
                //get the response
                $response = trim(socket_read($socket, 1024));
                socket_close($socket);
                $x = new simpleXMLElement($response);
                if ($x) {
                    //there was a fault
                    if ($x->getName() === 'FAULT') {
                        error_log('POST: ' . $response);
                        return FALSE;
                    } else {
                        //no problem
                        return $response;
                    }
                } else {
                    error_log("Could not parse response from Wise..$response");
                    return FALSE;
                }
                break;
        }
        return TRUE;
    }

    /**
     * Parse xml message into two arrays
     *
     * @param string $response XML message to parse
     * @param array &$vals Array to hold values
     * @param array &$index Array to hold index into $vals array
     */
    public function parse($response, &$vals, &$index)
    {
        $p = xml_parser_create();
        xml_parse_into_struct($p, $response, $vals, $index);
        xml_parser_free($p);
    }

    /**
     * Get a wise transaction id number
     *
     * @return string Wise transaction id
     */
    public function postTEXT_ADDTRAN()
    {
        //get a tranid
        $response = $this->post('TEXT_ADDTRAN');
        if ($response == false) {
            return false;
        }

        if ($x = new simpleXMLElement($response)) {
            if ($x->getName() === 'FAULT') {
                error_log($x->processor->message);
            } else {
                return (string)$x->confirm->tranid;
            }
        } else {
            return false;
        }
    }

    /**
     * Add Text
     *
     * @return string XML wise response
     */
    public function postTEXT_ADD($a)
    {
        $smarty = tldUtils::getSmarty('common');
        $smarty->assign('a', $a);
        $xml = $smarty->fetch('erp/wise.xml.TEXT_ADD.tpl');
        $response = $this->post('TEXT_ADD', $xml);
        if ($response == false) {
            return false;
        }
        $x = new simpleXMLElement($response);
        if ($x->getName() === 'FAULT') {
            error_log($x->processor->message);
        } else {
            return (string)$x->confirm->tranid;
        }
    }

    /**
     * Update PO line item delivery dates
     *
     * Updates PO line item delivery dates as specified by an array. Array is subsequently
     * converted to a wise xml message payload using a template before being posted.
     *
     * Need to get a tranid using TEXT_ADDTRAN, add each line using TEXT_ADD then uppodat
     *
     * @param $a array Array structure containing data
     *
     * NOTE: Dates
     * <code>
     * $a = array("orno"=>12345,
     *            "lines"=>array(array("pono"=>"1", "ddtc"=>"20060101", "ddts"=>"20051201"),
     *                            array("pono"=>"2", "ddtc"=>"20060201", "ddts"=>"20051201")
     *                    )
     *        )
     * ddtc = est receipt date at buyer
     * ddts = est ship date from vendor
     * Resulting XML
     *
     * <tranid>12981237831289321987</tranid>
     * <orno>787545</orno>
     * <lines>
     *    <line>
     *        <pono>1</pono>
     *        <ddtc>20060101</ddtc>
     *    </line>
     *    <line>
     *        <pono>2</pono>
     *        <ddtc>20060201</ddtc>
     *    </line>
     * </lines>
     *
     * </code>
     */
    public function postUPPODDTC($a)
    {
        if (empty($a) || empty($a['lines'])) {
            return 'ERROR: Input array was empty';
        }
        error_log('Start of UPPODDTC call');
        //get a transaction id
        $tranid = $this->postTEXT_ADDTRAN();
        if (empty($tranid)) {
            return 'ERROR: Could not get a transaction id';
        }
        error_log("Got tranid $tranid");

        //loop though the lines, cleanup date, add tran text
        foreach ($a['lines'] as $line) {
            if (!empty($line['ddtc'])) {
                $b = [
                    'tranid' => $tranid,
                    'textline' => $line['pono'],
                    'textdata' => str_replace('-', '', $line['ddtc']),
                ];
                $res = $this->postTEXT_ADD($b);
            }
        }

        $c = ['tranid' => $tranid,
            'orno' => $a['orno'],
        ];
        $smarty = tldUtils::getSmarty('common');
        $smarty->assign('a', $c);
        $xml = $smarty->fetch('erp/wise.xml.UPPODDTC.tpl');
        //tell wiseeis to process the lines linked to the given tranid
        $response = $this->post('UPPODDAT', $xml);
        $x = simplexml_load_string($response);
        if (!$x) {
            error_log("Could not parse response from UPPODDTC call...$response");
            return false;
        } else {
            return (string)$x->handler0000->error_msg;
        }

    }

    /**
     * Clear Invoice blocking code in BAAN
     *
     * @param $a array Array structure containing data
     *
     */
    public function postTGLAPBLOC($a)
    {
        if (empty($a)) {
            return 'ERROR: Input array was empty';
        }
        error_log('Start of TGLAPBLOC call');

        //get a transaction id
        $tranid = $this->postTEXT_ADDTRAN();
        $ninv = $a['ninv'];
        $ttyp = $a['ttyp'];

        if (empty($tranid)) {
            return 'ERROR: Could not get a transaction id';
        }
        error_log("Got tranid $tranid");

        $xml = <<<EOF
<tranid>$tranid</tranid>
<ninv>$ninv</ninv>
<ttyp>$ttyp</ttyp>
EOF;

        //tell wiseeis to process the lines linked to the given tranid
        $response = $this->post('TGLAPBLOC', $xml);
        $x = simplexml_load_string($response);
        if (!$x) {
            error_log("Could not parse response from TGLAPBLOC call...$response");
            return false;
        } else {
            return (string)$x->handler0000->error_msg;
        }
    }

    public static function getEDMErp($erp)
    {
        switch ($erp) {
            case '220':
            case '250':
                return $erp;
            default:
                return '400';
        }
    }

    public static function getItemDataEDM($id, $edmErp = '400')
    {
        if (is_array($id)) {
            $WHERE = "edm.t_eitm IN ('" . implode("','", $id) . "')";
        } else {
            $WHERE = "edm.t_eitm='$id' COLLATE SQL_Latin1_General_CP1_CI_AS";
        }

        $query = <<<EOF
        SELECT
            edm.t_eitm AS ITEM,
            edm.t_dsca AS DESCRIPTION,
            edm.t_cuni AS UM
        FROM
            ttiedm010$edmErp AS edm
        WHERE
            $WHERE
EOF;

        $rows = tldUtils::getSqlToAssocArray(
            $query, 'odbc', ['src' => 'baan']);
        if (is_array($id)) {
            return $rows;
        }

        return $rows[0];
    }


    /**
     * Get item data from table ttiitm001
     *
     * @param array|string $id Part number to get item data for
     * @return array Array of db rows
     */
    public function getItemData($id)
    {
        $erp = (int) $this->itsERP;
        if (390 === $erp) {
            // SAGE
            return [];
        }

        if (is_array($id)) {
            $WHERE = "itm.t_item IN ('" . implode("','", $id) . "')";
        } else {
            $WHERE = "itm.t_item='$id' COLLATE SQL_Latin1_General_CP1_CI_AS";
        }
        $query = <<<EOF
SELECT
	'$erp' AS ERP,
    itm.t_item AS ITEM,
    (
    	SELECT
    		edm.t_dsca
    	FROM
    		dbo.ttiedm010$erp AS edm
    	WHERE
    		edm.t_eitm=itm.t_item
    ) AS DESCRIPTION,
    (select TOP 1 t_revi from ttiedm100$erp where t_eitm = itm.t_item order by t_indt DESC) AS rev,
    (select TOP 1 t_dscb from ttiedm010$erp where t_eitm = itm.t_item) AS pmoc,
    itm.t_dsca AS ALT_DESCRIPTION,
    CAST(ROUND(itm.t_wght,2) as DECIMAL(15,2)) AS WT,
    itm.t_cups AS UM,
    (SELECT mcs.t_ccur FROM ttcmcs034$erp AS mcs
    	WHERE mcs.t_cplt='MIP'
    ) AS CURRENCY,
	(SELECT TOP 1 CAST(sls.t_pric AS money)
		FROM dbo.ttdsls032$erp AS sls
		WHERE itm.t_item=sls.t_item AND sls.t_cpls='MIP'
		AND (GETDATE() BETWEEN sls.t_stdt AND sls.t_tdat)
	) AS MIP,
    (SELECT com.t_ccur FROM ttccom000300 AS com
    	WHERE com.t_ncmp=$erp
    ) AS STDCCUR,
    (SELECT dsls.t_csel FROM ttdsls909300 AS dsls
    	WHERE itm.t_item=dsls.t_item
    ) AS MIPPILOT,
	(SELECT dsls.t_mult FROM ttdsls909300 AS dsls
    	WHERE itm.t_item=dsls.t_item
    ) AS MIPMULT,
	(SELECT dsls.t_coef FROM ttdsls909300 AS dsls
    	WHERE itm.t_item=dsls.t_item
    ) AS MIPCOEF,
    CAST(ROUND(itm.t_copr,2) AS money) AS STDCOST,
    itm.t_oltm AS LEAD,
    itm.t_slmp AS RESPCT,
    CAST(itm.t_prip AS money) AS t_prip,
    itm.t_ccur,
    SUBSTRING(convert(varchar, itm.t_ltpp, 120), 0, 11) AS t_ltpp,
    itm.t_sfst,
    t_csig,
    t_txta,
    itm.t_suno,
    (SELECT SUP.t_nama FROM ttccom020$erp AS SUP WHERE SUP.t_suno = itm.t_suno) AS t_nama,
    itm.t_kitm,
    itm.t_citg,
    itm.t_mioq,
    itm.t_reop,
    itm.t_buyr,
    itm.t_blck,
    itm.t_ordr,
    itm.t_quot,
    itm.t_stoc AS stoc,
    itm.t_allo,
    itm.t_ecoq,
    itm.t_ltdt,
    itm.t_lcod,
    itm.t_ctyo,
    itm.t_ccde,
    itm.t_abcc,
    itm.t_oqmf,
    itm.t_maoq,
    itm.t_oint,
    itm.t_sftm,
    itm.t_ltpr,
    CASE itm.t_cpha WHEN 1 then 'Yes' ELSE 'No' END AS t_cpha,
    (SELECT sum(t_stks) from ttdilc101$erp AS inventory LEFT JOIN ttcmcs003$erp AS whs ON inventory.t_cwar=whs.t_cwar WHERE inventory.t_item=itm.t_item and t_nwrh=1) AS t_stks
FROM
	ttiitm001$erp AS itm
WHERE
	$WHERE
EOF;

        $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
        if (is_array($id)) {
            return $rows;
        }

        return $rows[0];
    }

    /**
     * Get TEXT by text id (ctxt)
     * @param $ctxt , text id
     * @param $clan , lang code (1,2,o)
     * @return array
     */
    public static function getTXT($ctxt, $clan = '')
    {
        $WHERE = !empty($clan) ? " AND TXT.t_clan='$clan'" : '';

        $query = <<<EOF
SELECT
	T1.*,
	SUBSTRING(convert(varchar, T1.t_ludt, 120), 0 , 11) AS dt,
	T2.t_clan,
	T2.t_seqe,
	T2.t_text
FROM
	ttttxt002300 T1
	LEFT JOIN ttttxt010300 T2 ON T1.t_ctxt=T2.t_ctxt
WHERE
	T1.t_ctxt='$ctxt'
	$WHERE
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public static function getTXTA($ctxt, $clan = '')
    {
        $WHERE = !empty($clan) ? " AND TXT.t_clan='$clan'" : '';
        $query = <<<EOF
SELECT
	T2.t_text
FROM
	ttttxt002300 T1
	LEFT JOIN ttttxt010300 T2 ON T1.t_ctxt=T2.t_ctxt
WHERE
	T1.t_ctxt='$ctxt'
	$WHERE
EOF;
        return tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
    }
    /**
     * Search item data table for part number
     *
     * @param string $pn part number to search for
     * @return array Array of db rows
     */
    public function searchItemDataByPN($pn, $options = '')
    {
        switch ($this->itsERP) {
            case 520:
            case 540:
                $erp2 = 500;
                break;
            case 680:
            case 620:
                $erp2 = 640;
                break;
            default:
                $erp2 = $this->itsERP;
        }
        $pn = TldDatabase::escape($pn);
        $WHERE = !empty($options['fuzzySearch']) ? " OR itm.t_item like '__$pn' OR itm.t_item like '___$pn'" : '';
        $query = <<<EOF
SELECT
	'$this->itsERP' AS ERP,
    itm.t_item AS ITEM,
    itm.t_dsca AS DESCRIPTION,
    CAST(ROUND(itm.t_wght,2) as DECIMAL(15,2)) AS WT,
    itm.t_cups AS UM,
    (
    	SELECT
    		mcs.t_ccur
    	FROM
    		ttcmcs034$erp2 AS mcs
    	WHERE
    		mcs.t_cplt='MIP'
    ) AS CURRENCY,
	(
		SELECT TOP 1
			CAST(sls.t_pric AS money)
		FROM
			dbo.ttdsls032$this->itsERP AS sls
		WHERE
			itm.t_item=sls.t_item AND
			sls.t_cpls='MIP' AND
			(
				GETDATE() BETWEEN sls.t_stdt AND sls.t_tdat
			)
	) AS MIP,
    (
    	SELECT
    		com.t_ccur
    	FROM
    		ttccom000300 AS com
    	WHERE
    		com.t_ncmp=$this->itsERP
    ) AS STDCCUR,
    CAST(itm.t_copr AS money) AS STDCOST,
    itm.t_oltm AS LEAD,
    itm.t_slmp AS RESPCT
FROM
	dbo.ttiitm001$this->itsERP AS itm
WHERE
	RTRIM(itm.t_item)=RTRIM('$pn')
	$WHERE
ORDER BY
	itm.t_item
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public static function searchAltPN($pn, $mode = '')
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function putTextData($erp, $kwd1 = '', $kwd2 = '', $kwd3 = '', $kwd4 = '')
    {
        //get tranid
        $tranresp = $this->post('TEXT_ADDTRAN');
        $tranxml = simplexml_load_string($tranresp);
        $tranid = $tranxml->confirm->tranid;

        $request = new SimpleXMLElement('<request></request>');

        $request->addChild('method', 'TEXT_PUT');

        $tokenid = $this->login();
        $request->addChild('token', $tokenid);

        $parameters = $request->addChild('parameters');
        $parameters->addChild('tranid', $tranid);
        $parameters->addChild('language', '2');
        $parameters->addChild('kw1', $kwd1);
        $parameters->addChild('kw2', $kwd2);
        $parameters->addChild('kw3', $kwd3);
        $parameters->addChild('kw4', $kwd4);

        echo $request->asXML();
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_connect($socket, $this->theHOST['ip'], $this->theHOST['port']);
        //send the message
        socket_write($socket, $request->asXML());
        //get the response
        $response = trim(socket_read($socket, 1024));
        socket_close($socket);

        return $response;
    }

    /**
     *    returns carrier data for $id, returns ALL if $id not set
     *
     * fields in baan: t_cfrw, t_dsca, t_suno, t_Refcntd, t_Refcntu
     *
     * @param integer id ID of carrier in table
     * @param array option Array of options for query
     *
     * @return array Array of db rows if more than one row returned
     */
    public function getCarrierData($id = '', $option = '')
    {
        $WHERE = '';
        if ($id) {
            $id = TldDatabase::escape($id);
            $WHERE = " WHERE t_cfrw='$id'";
        }
        ////******** IMPORTANT: ALL data comes from shared 300 table for ALL ERPS
        $query = <<<EOF
SELECT *
FROM dbo.ttcmcs080300 AS carrier
$WHERE
ORDER BY t_dsca
EOF;
        $options = array_merge((array)$option, ['src' => 'baan']);
        if ($id) {
            $result = tldUtils::getSqlRowToAssocArray($query, 'odbc', $options);
        } else {
            $result = tldUtils::getSqlToAssocArray($query, 'odbc', $options);

        }
        return $result;
    }

    /**
     * Get inventory data for pn
     *
     * @param Integer id Part number to get inventory data for
     * @return array Array of db rows
     */
    public function getInvData($id, $mode = '')
    {
        if (is_array($id)) {
            $WHERE = "inv.t_item IN ('" . implode("','", $id) . "')";
        } else {
            $WHERE = "inv.t_item='$id'";
        }
        if ($mode === 'byItemOnly') {
            $SELECT = '';
            $GROUPBY = '';
        } else {
            $SELECT = ',wars.t_dsca AS cwar_fullname, wars.t_cwar';
            $GROUPBY = ',wars.t_dsca, wars.t_cwar';
        }

        switch ($this->itsERP) {
            case '300':
                $query = <<<EOF
SELECT RTRIM(inv.t_item) AS item,
    ROUND(SUM(inv.t_stoc),2) AS stoc,
    ROUND(SUM(inv.t_ordr),2) AS ordr,
    ROUND(SUM(inv.t_allo),2) AS allo,
    ROUND(SUM(inv.t_reop),2) AS reop,
    ROUND(SUM(inv.t_stoc-inv.t_allo),2) AS avail,
    CAST (SUM(inv.t_stoc+inv.t_ordr-inv.t_allo) AS decimal(18, 2)) AS econ,
    CAST (SUM(inv.t_sfst) AS decimal(18, 2)) AS sfst
    $SELECT
FROM dbo.ttdinv001$this->itsERP AS inv
    JOIN dbo.ttcmcs003$this->itsERP as wars ON inv.t_cwar=wars.t_cwar and wars.t_nwrh=1
WHERE
	inv.t_cwar<>'DNY'
    AND $WHERE
GROUP BY inv.t_item
    $GROUPBY
EOF;
                break;
            case '640':
                $query = <<<EOF
SELECT RTRIM(inv.t_item) AS item,
    ROUND(SUM(inv.t_stoc),2) AS stoc,
    ROUND(SUM(inv.t_ordr),2) AS ordr,
    ROUND(SUM(inv.t_allo),2) AS allo,
    ROUND(SUM(inv.t_reop),2) AS reop,
    ROUND(SUM(inv.t_stoc-inv.t_allo),2) AS avail,
    CAST (SUM(inv.t_stoc+inv.t_ordr-inv.t_allo) AS decimal(18, 2)) AS econ,
    CAST (SUM(inv.t_sfst) AS decimal(18, 2)) AS sfst
    $SELECT
FROM dbo.ttdinv001$this->itsERP AS inv
    JOIN dbo.ttcmcs003$this->itsERP as wars ON inv.t_cwar=wars.t_cwar and wars.t_nwrh=1
WHERE
    $WHERE
    AND wars.t_cwar NOT IN('SP1','SP2','SP3','SP4','SP5','SP6','SP7','SP8','WAR','GS1')
GROUP BY inv.t_item
    $GROUPBY
EOF;
                break;
            case '420':
                $query = <<<EOF
SELECT RTRIM(inv.t_item) AS item,
    CONVERT(VARCHAR(100), CAST(ROUND(SUM(inv.t_stoc),2) AS DECIMAL(15,0))) AS stoc,
    ROUND(SUM(inv.t_ordr),2) AS ordr,
    ROUND(SUM(inv.t_allo),2) AS allo,
    ROUND(SUM(inv.t_reop),2) AS reop,
    CONVERT(VARCHAR(100), CAST(ROUND(SUM(inv.t_stoc-inv.t_allo),2) AS DECIMAL(15,0))) AS avail,
    CAST (SUM(inv.t_stoc+inv.t_ordr-inv.t_allo) AS decimal(18, 2)) AS econ,
    CAST (SUM(inv.t_sfst) AS decimal(18, 2)) AS sfst
    $SELECT
FROM dbo.ttdinv001$this->itsERP AS inv
    JOIN dbo.ttcmcs003$this->itsERP as wars ON inv.t_cwar=wars.t_cwar and wars.t_nwrh=1
WHERE
    $WHERE
    AND wars.t_cwar IN('RG2', 'S01', 'S02', 'WAC', 'FGS', 'EUR')
GROUP BY inv.t_item
    $GROUPBY
EOF;
                break;
            case '540':
                $query = <<<EOF
SELECT RTRIM(inv.t_item) AS item,
    CONVERT(VARCHAR(100), CAST(ROUND(SUM(inv.t_stoc),2) AS DECIMAL(15,0))) AS stoc,
    ROUND(SUM(inv.t_ordr),2) AS ordr,
    ROUND(SUM(inv.t_allo),2) AS allo,
    ROUND(SUM(inv.t_reop),2) AS reop,
    CONVERT(VARCHAR(100), CAST(ROUND(SUM(inv.t_stoc-inv.t_allo),2) AS DECIMAL(15,0))) AS avail,
    CAST (SUM(inv.t_stoc+inv.t_ordr-inv.t_allo) AS decimal(18, 2)) AS econ,
    CAST (SUM(inv.t_sfst) AS decimal(18, 2)) AS sfst
    $SELECT
FROM dbo.ttdinv001$this->itsERP AS inv
    JOIN dbo.ttcmcs003$this->itsERP as wars ON inv.t_cwar=wars.t_cwar
WHERE
    $WHERE
    AND (wars.t_nwrh=1 OR wars.t_cwar IN('C99'))
GROUP BY inv.t_item
    $GROUPBY
EOF;
                break;
            default:
                $query = <<<EOF
SELECT RTRIM(inv.t_item) AS item,
    CONVERT(VARCHAR(100), CAST(ROUND(SUM(inv.t_stoc),2) AS DECIMAL(15,0))) AS stoc,
    ROUND(SUM(inv.t_ordr),2) AS ordr,
    ROUND(SUM(inv.t_allo),2) AS allo,
    ROUND(SUM(inv.t_reop),2) AS reop,
    CONVERT(VARCHAR(100), CAST(ROUND(SUM(inv.t_stoc-inv.t_allo),2) AS DECIMAL(15,0))) AS avail,
    CAST (SUM(inv.t_stoc+inv.t_ordr-inv.t_allo) AS decimal(18, 2)) AS econ,
    CAST (SUM(inv.t_sfst) AS decimal(18, 2)) AS sfst
    $SELECT
FROM dbo.ttdinv001$this->itsERP AS inv
    JOIN dbo.ttcmcs003$this->itsERP as wars ON inv.t_cwar=wars.t_cwar and wars.t_nwrh=1
WHERE
    $WHERE
GROUP BY inv.t_item
    $GROUPBY
EOF;
                break;
        }

        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public function getInventoryRawDataByConstraints($a = '1=1', $opt = NULL)
    {
        $erp = $this->itsERP;
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "AND $WHERE";
        }
        // Look for options
        $ORDERBY = 'itm.t_item';
        if (!empty($opt['orderBy'])) {
            $ORDERBY = $opt['orderBy'];
        }
        // Query
        $query = <<<EOF
SELECT
    itm.*,
    SUBSTRING(convert(varchar,stock.t_tran,120), 0, 11) AS t_tran,
    stock.t_stks,
    stock.t_cwar AS stock_cwar,
    RTRIM(LTRIM(stock.t_loca)) AS t_loca,
    (stock.t_cwar + char(10)+ stock.t_loca) AS whse_loca,
    stock.t_strs,
    byr.t_info,
    sup.t_nama,
    CAST(itm.t_copr AS MONEY) AS t_copr,
    CAST(itm.t_copr*stock.t_strs AS MONEY) AS total_copr
FROM
    ttiitm001$erp AS itm
    LEFT JOIN ttdilc101$erp AS stock ON stock.t_item=itm.t_item
    LEFT JOIN ttccom001$erp AS byr ON byr.t_emno=itm.t_buyr
    LEFT JOIN ttccom020$erp AS sup ON sup.t_suno=itm.t_suno
WHERE
    stock.t_strs>0
    $WHERE
ORDER BY
    $ORDERBY
EOF;

        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }


    public function getWarehouseDataByConstraints($a = '1=1', $opt = NULL)
    {
        $erp = $this->itsERP;
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = " $WHERE";
        }
        // Look for options
        $ORDERBY = 'itm.t_item';
        if (!empty($opt['orderBy'])) {
            $ORDERBY = $opt['orderBy'];
        }
        // Query
        $query = <<<EOF
SELECT
    itm.*,

    inventory.t_cwar AS stock_cwar,
    inventory.t_loca AS t_wloc,

    byr.t_info,
    sup.t_nama,
    inventory.t_stks,
    CAST(itm.t_copr AS MONEY) AS t_copr,
    CAST(itm.t_copr*inventory.t_stks AS MONEY) AS total_copr
FROM
    ttiitm001$erp AS itm
	LEFT JOIN ttdilc101$erp AS inventory ON inventory.t_item=itm.t_item
    LEFT JOIN ttccom001$erp AS byr ON byr.t_emno=itm.t_buyr
    LEFT JOIN ttccom020$erp AS sup ON sup.t_suno=itm.t_suno

WHERE

    $WHERE
ORDER BY
    $ORDERBY
EOF;

        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     * if id is not given, returns ALL suppliers
     *
     * @param integer id ID of supplier in table ttccom020
     * @param array  mode Mode of search
     *
     * @return array Array of db rows
     */
    public function getSupplierData($id = '', $mode = '', $options = []): array
    {
        $WHERE = '';
        switch ($mode) {
            case 'searchByName':
                if ($id) {
                    $WHERE = " WHERE UPPER(sup.t_nama) LIKE UPPER('$id') + '%'";
                }
                break;
            case 'searchByNameByID':
                if ($id) {
                    $WHERE = <<<EOF
        		 WHERE (( UPPER(sup.t_nama) LIKE '%'+ UPPER('$id') + '%') OR (sup.t_suno LIKE '%'+ UPPER('$id')+ '%'))
EOF;
                }
                break;
            default:
                if ($id) {
                    $WHERE = " WHERE sup.t_suno='$id'";
                }
        }
        $ORDER = $options['orderBy'] ? ' ORDER BY sup.' . $options['orderBy'] : ' ORDER BY sup.t_nama';
        // Handle shared tables
        if (in_array((int) $this->itsERP,  [400, 410], true)) {
            $erp = '300';
        } elseif (in_array((int) $this->itsERP, [500, 520, 540], true)) {
            $erp = '500';
        } elseif (in_array((int) $this->itsERP, [620, 620, 640], true)) {
            $erp = '640';
        }else {
            $erp = $this->itsERP;
        }
        $query = <<<EOF
SELECT sup.*,
RTRIM(t_suno) AS t_suno,
SUBSTRING(t_nama, 0, 1) AS firstChar,
(SELECT RTRIM(T6.t_info) FROM ttccom001$this->itsERP AS T6 WHERE T6.t_emno=sup.t_ccon) AS byr_email
FROM ttccom020$erp AS sup
$WHERE
$ORDER
EOF;
        if (empty($mode) && $id) {
            return tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
        }

        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     * Get Sales Parts KPI
     *
     * @param integer whse ID of whse
     * @param array  mode Mode of KPI
     *
     * @return array Array of db rows
     */
    public static function getSalesPartsKPI($whse, $erp)
    {
        $query = <<<EOF
SELECT SUBSTRING(convert(varchar, t2.t_ddat, 120), 0 , 8) as xval,
	ROUND(sum((CASE WHEN (t1.t_cuqs <> 'EA') THEN 1
			ELSE t2.t_dqua
			END)),0) as TDP,

	ROUND(sum((CASE WHEN (t2.t_ddat = t1.t_odat AND t1.t_cuqs <> 'EA') THEN 1
		WHEN (t2.t_ddat = t1.t_odat AND t1.t_cuqs = 'EA') THEN t2.t_dqua
			ELSE 0
			END))/sum((CASE WHEN (t1.t_cuqs <> 'EA') THEN 1
			ELSE t2.t_dqua
			END))*100,0) as IFR,

	ROUND(sum((CASE WHEN (t1.t_cuqs <> 'EA') THEN DATEDIFF(day, t1.t_odat, t2.t_ddat)
				WHEN (t1.t_cuqs = 'EA') THEN (DATEDIFF(day,t1.t_odat, t2.t_ddat))*t2.t_dqua END))/sum((CASE WHEN (t1.t_cuqs <> 'EA') THEN 1
			ELSE t2.t_dqua
			END)),0) AS AVT,

	ROUND(sum((CASE WHEN t1.t_cuqs <> 'EA' AND DATEDIFF(day, t2.t_ddat, t1.t_odat)> -8 THEN '1'
		WHEN t1.t_cuqs = 'EA' AND DATEDIFF(day, t2.t_ddat, t1.t_odat)> -8 THEN t2.t_dqua
		ELSE NULL END))/sum((CASE WHEN (t1.t_cuqs <> 'EA') THEN 1
			ELSE t2.t_dqua
			END))*100,0) AS WFR

FROM ttdsls041$erp as t1,
	ttdsls045$erp as t2,
	ttdsls040$erp as t3

WHERE DATEDIFF(month, t2.t_ddat, GETDATE()) <= 12
AND   t2.t_orno = t1.t_orno
AND   t2.t_pono = t1.t_pono
AND   t1.t_cwar = '$whse'
AND   t2.t_dqua > 0
AND   t2.t_orno = t3.t_orno
AND   t3.t_scom = 2
AND   t1.t_scom = 2
AND   t3.t_cpay <> 'TPS'
AND   t3.t_cpay <> 'PAI'
AND   t3.t_cpay <> '001'
AND   t3.t_cpay <> 'CPS'

GROUP BY SUBSTRING(convert(varchar, t2.t_ddat, 120), 0 , 8)
ORDER BY SUBSTRING(convert(varchar, t2.t_ddat, 120), 0 , 8)
EOF;

        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);

    }

// debut bbl
    public static function getPartsInventoryValue($comp)
    {
        $query = <<<EOF
SELECT
	locs.location as factories,
	ROUND((T1.sph_net_inv_val)/ (
		(SELECT
			sum(kpis.sph_tot_sls)
		FROM
			locations_kpi_sso AS kpis
		WHERE
			kpis.parent_id=T1.parent_id AND
			PERIOD_DIFF(DATE_FORMAT(CONCAT(T1.ynam,'-',T1.mnam,'-01'), '%Y%m'),
						DATE_FORMAT(CONCAT(kpis.ynam,'-',kpis.mnam,'-01'), '%Y%m') ) between 0 and 2)/90), 0) as yval,
	DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval
FROM
	locations_kpi_sso AS T1 LEFT JOIN locations AS locs ON T1.parent_id=locs.id
WHERE
    locs.erp='$comp'  and
    DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m-%d') >= DATE_SUB(now(), INTERVAL 12 MONTH)
GROUP BY
	factories, xval
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Count total amount of item in stock (gross)
     * @param mixed array or string constraints $a
     * @return array
     */
    public function countInventoryRawMaterialValueByBuyerByWarehouseByConstraints($a = '1=1')
    {
        // Actual ERP#
        $erp = $this->itsERP;
        // Look for constraints
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        $query = <<<EOF
SELECT
    byr.t_info,
    stock.t_cwar,
    CAST(SUM(stock.t_strs*itm.t_copr) AS MONEY) AS num
FROM
    ttiitm001$erp AS itm
    LEFT JOIN ttdilc101$erp AS stock ON stock.t_item=itm.t_item
    LEFT JOIN ttccom001$erp AS byr ON byr.t_emno=itm.t_buyr
WHERE
    stock.t_strs<>0
    AND $WHERE
GROUP BY
    byr.t_info,
    stock.t_cwar
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     * Get customer data
     *
     * if id is not given, returns ALL customers
     *
     * @param integer id ID of customer to get data for
     * @param array mode Array of options to pass to query
     *
     * @return array Array of db rows if more than one row returned
     */
    public function getCustomerData($id = '', $mode = '')
    {
        switch ($this->itsERP) {
            case '300':
            case '400':
            case '410':
                $erp = 300;
                break;
            case '500':
            case '520':
            case '540':
                $erp = 500;
                break;
            default:
                $erp = $this->itsERP;
        }

        $query = <<<EOF
SELECT RTRIM(t_cuno) AS t_cuno,RTRIM(t_nama) AS t_nama,
RTRIM(t_namb) AS t_namb, RTRIM(t_namc) AS t_namc,
RTRIM(t_namd) AS t_namd, RTRIM(t_name) AS t_name,
SUBSTRING(t_nama, 0, 1) AS firstChar
FROM dbo.ttccom010$erp
        WHERE t_cnpa=1
EOF;
        switch ($mode) {
            case 'searchByName':
                if ($id) {
                    $query .= " WHERE t_seak LIKE UPPER('$id') + '%' ORDER BY t_seak";
                }
                $result = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
                break;
            case 'smartyOptions':
                $query = <<<EOF
SELECT RTRIM(t_cuno) AS t_cuno,RTRIM(t_nama) + ' [' + t_cuno + ']' AS t_nama
FROM dbo.ttccom010$erp
ORDER BY t_nama
EOF;
                $result = tldUtils::getSqlToAssocArray($query, 'odbc',
                    ['src' => 'baan', 'smartyOptions' => ['t_cuno', 't_nama']]);
                break;
            case 'internalCustomer':
                $query = <<<EOF
SELECT RTRIM(t_cuno) AS t_cuno,RTRIM(t_nama) + ' [' + t_cuno + ']' AS t_nama
FROM dbo.ttccom010$erp
WHERE t_inrl=1
ORDER BY t_nama
EOF;

                $result = tldUtils::getSqlToAssocArray($query, 'odbc',
                    ['src' => 'baan', 'smartyOptions' => ['t_cuno', 't_nama']]);
                break;
            default:
                if ($id) {
                    $query .= " WHERE rtrim(t_cuno)=$id";
                    $result = tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
                } else {
                    $result = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
                }
        }
        return $result;
    }

    public function whatSUNOTable()
    {
        switch ($this->itsERP) {
            case '400':
            case '410':
                $erp = 300;
                break;
            case '520':
            case '540':
                $erp = 500;
                break;
            default:
                $erp = $this->itsERP;
        }
        return $erp;
    }

    /**
     * Get delivery address data for a particular customer
     *
     * if id is not given, returns ALL addresses for a customer
     *
     * <code>
     * $add = $erp->getDeliveryAddressData($cuno, array("m"=>"byCDEL", "t_cdel"=>"020"));
     * </code>
     * @param int cuno ID of customer to get data for
     * @param array cdel optional delivery address id
     *
     * @return array Array of db rows if more than one row returned
     */
    public function getDeliveryAddressData($cuno, $options = [])
    {
        $erp = $this->itsERP;
        switch ($erp) {
            case 540:
            case 520:
                $com013ERP = 500;
                break;
            default:
                $com013ERP = $erp;
        }
        $query = <<<EOF
SELECT
    t_cuno, t_cdel,
    RTRIM(t_nama) AS t_nama,
    RTRIM(t_namb) AS t_namb,
    RTRIM(t_namc) AS t_namc,
    RTRIM(t_namd) AS t_namd,
    RTRIM(t_name) AS t_name,
    RTRIM(t_namf) AS t_namf,
    RTRIM(t_ccty) AS t_ccty
FROM dbo.ttccom013$com013ERP
WHERE t_cuno='$cuno'
EOF;
        if (!empty($options['m']) && 'byCDEL' === $options['m']) {
            $query .= " AND t_cdel='{$options['t_cdel']}' ORDER BY t_cdel";
            return tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
        }

        if (!empty($options['m']) && 'byCityStateZip' === $options['m']) {
            $query .= " AND LOWER(t_name) like '%'+LOWER('{$options['search']}')+'%'";
        }

        $query .= ' ORDER BY t_cdel';

        return tldUtils::getSqlToAssocArray(
            $query,
            'odbc',
            [
                'src' => 'baan',
            ]
        );
    }

    /**
     * Get delivery address data for a particular customer
     *
     * if id is not given, returns ALL addresses for a customer
     *
     * <code>
     * $add = $erp->getDeliveryAddressData($cuno, array("m"=>"byCDEL", "t_cdel"=>"020"));
     * </code>
     * @param integer cuno ID of customer to get data for
     * @param array cdel optional delivery address id
     *
     * @return array Array of db rows if more than one row returned
     */
    public function getPostalAddressData($cuno, $options = '')
    {
        $erp = $this->itsERP;
        switch ($erp) {
            case 540:
            case 520:
                $com012ERP = 500;
                break;
            default:
                $com012ERP = $erp;
        }
        $query = <<<EOF
SELECT
    t_cuno, t_ccor,
    RTRIM(t_nama) AS t_nama,
    RTRIM(t_namb) AS t_namb,
    RTRIM(t_namc) AS t_namc,
    RTRIM(t_namd) AS t_namd,
    RTRIM(t_name) AS t_name,
    RTRIM(t_namf) AS t_namf,
    RTRIM(t_ccty) AS t_ccty
FROM dbo.ttccom012$com012ERP
WHERE t_cuno='$cuno'
EOF;

        if (!empty($options['m']) && 'byCDEL' === $options['m']) {
            $query .= " AND t_ccor='{$options['t_ccor']}' ORDER BY t_ccor";
            return tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);

        }

        if (!empty($options['m']) && 'byCityStateZip' === $options['m']) {
            $query .= " AND LOWER(t_name) like '%'+LOWER('{$options['search']}')+'%'";
        }
        $query .= ' ORDER BY t_ccor';

        return tldUtils::getSqlToAssocArray(
            $query,
            'odbc',
            [
                'src' => 'baan',
            ]
        );
    }

    /**
     * Get warehouse data
     *
     * @param cwar warehouse code in baan
     *
     * @return array Array of db rows if more than one row returned
     */
    public function getWarehouseData($cwar)
    {
        $erp = $this->itsERP;

        $query = <<<EOF
SELECT *
FROM dbo.ttcmcs003$erp
WHERE t_cwar='$cwar'
EOF;
        return tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     * Get employee data for a particular employee number
     *
     * if id is not given, returns ALL employees
     * <code>array([t_emno]=>employee number,
     *        [t_nama]=>name,
     *        [t_sdte]=>start date,
     *        [t_edte]=>end date
     *            )
     * </code>
     *
     * @param integer cuno ID of customer to get data for
     * @param array cdel optional delivery address id
     *
     * @return array Array of db rows if more than one row returned
     */
    public function getEmployeeData($emno = '')
    {
        $erp = $this->itsERP;
        $query = <<<EOF
SELECT '$this->itsERP' AS ERP,
*
FROM dbo.ttccom001$erp
EOF;
        if ($emno) {
            $query .= " WHERE t_emno='$emno'";
            $result = tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
        } else {
            $result = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
        }
        return $result;
    }
}

/**
 * Interface class for all ERP systems
 *
 * @package ERP
 */
interface basicERP
{
    /**
     * convert specified doc to tldArray format
     *
     * @param string $type
     * @param array $p
     * @return object
     */
    public function factory($type, $p);

    /**
     * Receive a Purchase order in TLD Array format
     *
     * This function converts the input PO array to a SO array then
     * calls postSalesOrder with the SO array
     *
     * @param array $a TLD Array format
     * @param array $p array of parameters need to translate PO to SO
     */
    public function inPurchaseOrder($a);

    /**
     * Convert native xml PO to TLD Array format
     *
     * @param string $xmlstring
     * @return array
     */
    public function outPurchaseOrder($xml);

    /**
     * Receive a Sales order in TLD Array format
     *
     * @param array $a TLD Array format
     * @param string $poxml Associated PO in XML
     * @return boolean
     */
    public function inSalesOrder($a, $poxml);

    /**
     * Receive an SO Acknowledgement to erp system
     *
     * @param array $a
     * @param string $poxml Associated PO in XML
     * @return boolean
     */
    public function inSalesOrderAck($a, $poxml);

    /**
     * Convert native xml SO_ACK to TLD Array format
     *
     * @param string $xml
     * @return array
     */

    public function outSalesOrderAck($xml);

    /**
     * receive an INVOICE in native erp system xml
     *
     * @param array $a
     * @param string $poxml Associated PO in XML
     * @return boolean
     */
    public function inInvoice($a, $poxml);

    /**
     * Convert native xml INVOICE to TLD Array format
     *
     * @param string $xml
     * @return array
     */
    public function outInvoice($xml);

    /**
     * receive a DELIVERY NOTE in native erp system xml
     *
     * @param array $a
     * @param string $poxml Associated PO in XML
     * @return boolean
     */
    public function inDeliveryNote($a, $poxml);

    /**
     * Convert native xml DELIVERY NOTE to TLD Array format
     *
     * @param string $xml
     * @return array
     */
    public function outDeliveryNote($xml);
}

/**
 * Class for communicating documents in CXML format between ERP systems
 *
 * @package ERP
 */
class tldCXML
{
    public function __construct($erp)
    {
        $this->itsERP = $erp;
    }

    public function inPurchaseOrder($a, $auth)
    {
        return include("$GLOBALS[SHARED_PHP_PATH]/erp/cxml.purchaseorder.inc.php");
    }

    /**
     * Get XML document in TLD Array format
     *
     * Automatically detects document type
     *
     * @return array
     */
    public function outPurchaseOrder($xml)
    {
        if (empty($xml)) {
            return [];
        }
        $x = new SimpleXMLElement($xml);
        $orderRequestHeader = $x->Request->OrderRequest->OrderRequestHeader;
        $shipTo = $orderRequestHeader->ShipTo;
        $itemOut = $x->Request->OrderRequest->ItemOut;
        $note = (string)$orderRequestHeader->Comments;
        if (count($orderRequestHeader->Extrinsic) > 0) {
            foreach ($orderRequestHeader->Extrinsic as $Extrinsic) {
                foreach ($Extrinsic->attributes() as $attr => $name) {
                    $note .= "\r\n$name: " . (string)$Extrinsic;
                }
            }
        }
        $a = [
            'TLDHEADER' => [
                'COMP' => $this->itsERP,
                'TYPE' => 'PROCESS_PO',
                'PONUM' => (string)$orderRequestHeader['orderID'],
            ],
            'header' => [
                'customer' => [
                    't_cuno' => $partners['SOLDTO']['PARTNRID'],
                ],
                'note' => $note,
                'custpo' => (string)$orderRequestHeader['orderID'],
                'shipComplete' => 'shipcompleteTEST',
                'deliverAddress' => [
                    'line1' => (string)$shipTo->Address->PostalAddress->Street[0],
                    'line2' => (string)$shipTo->Address->PostalAddress->Street[1],
                    'line3' => (string)$shipTo->Address->PostalAddress->City,
                    'line4' => (string)$shipTo->Address->PostalAddress->State . ' ' .
                        (string)$shipTo->Address->PostalAddress->PostalCode,
                    'line5' => (string)$shipTo->Address->PostalAddress->Country,
                ],
            ],
        ];
        foreach ($itemOut as $i) {
            $a['lines'][(string)$i->ItemID->SupplierPartID] = [
                'product' => (string)$i->ItemID->SupplierPartID,
                'qty' => (string)$i['quantity'],
            ];
        }
        return $a;
    }

    public function inInvoiceDetail($a, $auth)
    {
        return include("$GLOBALS[SHARED_PHP_PATH]/erp/cxml.invoice_detail.inc.php");
    }

    /**
     * Get XML document in TLD Array format
     *
     * Automatically detects document type
     *
     * @return array
     */
    public function outInvoiceDetail($xml)
    {
        if (empty($xml)) {
            return [];
        }
        $x = new SimpleXMLElement($xml);
        $orderRequestHeader = $x->Request->OrderRequest->OrderRequestHeader;
        $shipTo = $orderRequestHeader->ShipTo;
        $itemOut = $x->Request->OrderRequest->ItemOut;
        $a = [
            'TLDHEADER' => [
                'COMP' => $this->itsERP,
                'TYPE' => 'PROCESS_PO',
                'PONUM' => (string)$orderRequestHeader['orderID'],
            ],
            'header' => [
                'customer' => [
                    't_cuno' => $partners['SOLDTO']['PARTNRID'],
                ],
                'custpo' => (string)$orderRequestHeader['orderID'],
                'shipComplete' => 'shipcompleteTEST',
//						"note"		=>$partners['CARRIER']['NAME'],
                'deliverAddress' => [
                    'line1' => (string)$shipTo->Address->PostalAddress->Street[0],
                    'line2' => (string)$shipTo->Address->PostalAddress->Street[1],
                    'line3' => (string)$shipTo->Address->PostalAddress->City,
                    'line4' => (string)$shipTo->Address->PostalAddress->State . ' ' .
                        (string)$shipTo->Address->PostalAddress->PostalCode,
                    'line5' => (string)$shipTo->Address->PostalAddress->Country,
                ],
            ],
        ];
//        error_log(print_r($itemOut));
        foreach ($itemOut as $i) {
            $a['lines'][(string)$i->ItemID->SupplierPartID] = [
                'product' => (string)$i->ItemID->SupplierPartID,
                'qty' => (string)$i['quantity'],
            ];
            error_log(print_r($i));
        }
        return $a;
    }
}


/**
 * Class for accessing archived pdf documents or xml data
 *
 * @package ERP
 */
class tldArchive
{

    public function __construct($erp)
    {
        $this->itsERP = $erp;
    }

    /**
     * Get root of the archive path
     * @return string archive path
     */
    public static function getWebRoot()
    {
        return '/mnt/grpfps10.strs_pdf/ARCHIVE';
    }

    /**
     * Insert a new document into the archive
     *
     * @param integer $erp company number
     * @param string $docType document type to insert
     * @param integer $id reference number of document to insert
     * @param string $date data and time document was inserted into the database
     * @param string $filepath location of file
     * @param string $dst destination company number
     * @param string $xml XML string
     * @param string $response XML response
     * @param string $status status of the record
     * @param string $flowtype Type of the workflow
     *
     * @return mixed int or string if error
     */
    public static function insert($erp, $docType, $id, $date, $filepath, $dst = '', $xml = '', $response = '', $status = '', $flowtype = '', $uid = '')
    {

        $xml = TldDatabase::escape($xml);
        $response = TldDatabase::escape($response);
        $status = TldDatabase::escape($status);
        $flowtype = TldDatabase::escape($flowtype);

        $query = <<<EOF
INSERT INTO erp_archive
SET
    dt = NOW(),
    erp = '$erp',
    uid = '$uid',
    doc_type = '$docType',
    num = '$id',
    dat = '$date',
    filepath = '$filepath',
    dest = '$dst',
    xml = '$xml',
    response = '$response',
    status = '$status',
    flow_type ='$flowtype'
EOF;
        return tldUtils::sqlInsert($query);
    }

    /**
     * Get the doc types within this company
     *
     * @param array $options options passed to the getSqlToAssocArray function
     * @return array
     */
    public function getTypes($options = '')
    {
        $erp = $this->itsERP;
        $query = <<<EOF
SELECT DISTINCT doc_type
FROM erp_archive
WHERE erp=$erp
ORDER by doc_type
EOF;
        return tldUtils::getSqlToAssocArray($query, $options);
    }

    /**
     * Get archived pdfs by type and id
     *
     * Could be multiple rows because of document reprints
     *
     * @param string $doc_type document type to retrieve
     * @param integer $id id of document to rectieve
     * @return array Array of db rows
     */
    public function byTypeID($docType, $id)
    {
        $query = <<<EOF
SELECT *
FROM erp_archive
WHERE erp='$this->itsERP' AND doc_type='$docType'
AND num='$id'
ORDER by dt DESC
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get archived data by constraints
     *
     * @param array $a constraints
     * @return array Array of db rows
     */
    public static function byConstraints($a)
    {
        if (empty($a) || !is_array($a)) {
            return;
        }
        $WHERE = tldUtils::constructWhere($a);
        $query = <<<EOF
SELECT *
FROM erp_archive
WHERE $WHERE
ORDER by dt DESC
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }
}

/**
 * Class for accessing MRP data in ERP system
 * Material Requirement Planning
 * @package ERP
 */
class tldMRP
{

    public function __construct()
    {
    }

    /**
     * Get list of MRP by constraints fields
     * @param int $erp
     * @param mixed string or array $a
     * @return array
     */
    public static function byConstraints($erp, $a = NULL)
    {
        if (empty($erp)) {
            return;
        }
        $WHERE = '';
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "WHERE $WHERE";
        }
        $query = <<<EOF
SELECT
    MRP.*,
    ITM.t_dsca,
    ITM.t_csig,
    SUBSTRING(convert(varchar,MRP.t_podt,120), 0, 11) AS t_podt,
    SUBSTRING(convert(varchar,MRP.t_pddt,120), 0, 11) AS t_pddt,
    (SELECT RTRIM(t6.t_info) FROM ttccom001$erp AS t6
    	WHERE t6.t_emno=MRP.t_buyr
    ) AS byr_email,
    (SELECT TOP 1 AIC.t_aitc FROM ttiitm012$erp AS AIC
    	WHERE AIC.t_item=MRP.t_item AND AIC.t_suno=MRP.t_suno
    	GROUP BY AIC.t_aitc
    ) AS vendor_pn
FROM
    ttimrp021$erp AS MRP
    LEFT JOIN ttiitm001$erp AS ITM ON MRP.t_item=ITM.t_item
$WHERE
ORDER BY MRP.t_podt, MRP.t_suno
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     * Get list of MRP late by constraints
     * @param int $erp
     * @param mixed string or array $a
     * @return array
     */
    public static function byLateByConstraints($erp, $a = NULL)
    {
        $WHERE = ' MRP.t_podt < GETDATE() ';
        if (is_array($a)) {
            $WHERE .= ' AND ' . tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE .= " AND $a";
        }
        return self::byConstraints($erp, $WHERE);
    }

    /**
     * Get list of MRP late by Buyer Email
     * @param int $erp
     * @param string $email
     * @return array
     */
    public static function byLateByBuyerEmail($erp, $email)
    {
        if (empty($email)) {
            return;
        }
        $a = <<<EOF
(SELECT RTRIM(t6.t_info) FROM ttccom001$erp AS t6 WHERE t6.t_emno=MRP.t_buyr)='$email'
EOF;
        return self::byLateByConstraints($erp, $a);
    }

    /**
     * Get list of MRP late by Supplier
     * @param int $erp
     * @param string $suno
     * @return array
     */
    public static function byLateBySuno($erp, $suno)
    {
        if (empty($suno)) {
            return;
        }
        $a = "MRP.t_suno='$suno'";
        return self::byLateByConstraints($erp, $a);
    }

    /**
     * Get list of MRP late by Supplier
     * @param int $erp
     * @param string $suno
     * @return array
     */
    public static function byLateByItem($erp, $item)
    {
        if (empty($item)) {
            return;
        }
        $a = "MRP.t_item='$item'";
        return self::byLateByConstraints($erp, $a);
    }

    /**
     * Get list of MRP within X days by constraints
     * @param int $erp
     * @param int $days
     * @param mixed string or array $a
     * @return array
     */
    public static function byWithinNbDaysByConstraints($erp, $days, $a = NULL)
    {
        if (empty($days)) {
            return;
        }
        $WHERE = " DATEDIFF(DAY, GETDATE(), MRP.t_podt) < $days ";
        if (is_array($a)) {
            $WHERE .= ' AND ' . tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE .= " AND $a";
        }
        return self::byConstraints($erp, $WHERE);
    }

    /**
     * Get list of MRP within X days by buyer email
     * @param int $erp
     * @param int $days
     * @param string $email
     * @return array
     */
    public static function byWithinNbDaysByBuyerEmail($erp, $days, $email)
    {
        if (empty($email)) {
            return;
        }
        $a = <<<EOF
(SELECT RTRIM(t6.t_info) FROM ttccom001$erp AS t6 WHERE t6.t_emno=MRP.t_buyr)='$email'
EOF;
        return self::byWithinNbDaysByConstraints($erp, $days, $a);
    }

    /**
     * Get list of MRP within X days by suno
     * @param int $erp
     * @param int $days
     * @param string $suno
     * @return array
     */
    public static function byWithinNbDaysBySuno($erp, $days, $suno)
    {
        if (empty($suno)) {
            return;
        }
        $a = "MRP.t_suno='$suno'";
        return self::byWithinNbDaysByConstraints($erp, $days, $a);
    }

    /**
     * Get list of MRP within X days by ITEM
     * @param int $erp
     * @param int $days
     * @param string $item
     * @return array
     */
    public static function byWithinNbDaysByItem($erp, $days, $item)
    {
        if (empty($item)) {
            return;
        }
        $a = "MRP.t_item='$item'";
        return self::byWithinNbDaysByConstraints($erp, $days, $a);
    }

    /**
     * WARNING DEPRECATED !!!
     * USE tldMRP::byConstraints() instead
     */
    public static function byVendorERP($vendorid, $erp)
    {
        if (empty($vendorid) || empty($erp)) {
            return;
        }

        switch ($erp) {
            case '620':
                $con = <<<EOF
            (SELECT
               itm.t_item
           FROM
               ttiitm001$erp AS itm
           WHERE itm.t_suno = '$vendorid')
EOF;

                $query = <<<SQL
(SELECT '220' as erp,MRP.t_cwar, MRP.t_item, ITM.t_dsca, MRP.t_oqan, EDM.t_revi AS t_revi,
        SUBSTRING(convert(varchar,MRP.t_podt,120), 0, 11) AS t_podt,
        SUBSTRING(convert(varchar,dateadd(DD,-49,MRP.t_pddt),120), 0, 11) AS t_pddt,
        (SELECT TOP 1 AIC.t_aitc FROM ttiitm012220 AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
 FROM ttimrp021220 AS MRP, ttiitm001220 AS ITM, ttirou102220 AS ROU2, ttirou001220 AS ROU1, ttiedm100220 AS EDM
 WHERE MRP.t_item=ITM.t_item AND MRP.t_suno='TLD006' AND EDM.t_eitm=ITM.t_item AND
     ( (MRP.t_pddt >= EDM.t_indt and MRP.t_pddt < EDM.t_exdt) OR (MRP.t_pddt >= EDM.t_indt and EDM.t_exdt='1753-01-01') )
   AND EDM.t_rele=1 and MRP.t_item in $con
 GROUP BY MRP.t_item, MRP.t_cwar, ITM.t_dsca, MRP.t_oqan,EDM.t_revi, SUBSTRING(convert(varchar,MRP.t_podt,120), 0, 11), SUBSTRING(convert(varchar,dateadd(DD,-49,MRP.t_pddt),120), 0, 11) 
 )
UNION
(SELECT '400' as erp,MRP.t_cwar, MRP.t_item, ITM.t_dsca, MRP.t_oqan, EDM.t_revi AS t_revi,
        SUBSTRING(convert(varchar,MRP.t_podt,120), 0, 11) AS t_podt,
        SUBSTRING(convert(varchar,dateadd(DD,-49,MRP.t_pddt),120), 0, 11) AS t_pddt,
        (SELECT TOP 1 AIC.t_aitc FROM ttiitm012400 AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
 FROM ttimrp021400 AS MRP, ttiitm001400 AS ITM, ttirou102400 AS ROU2, ttirou001400 AS ROU1, ttiedm100400 AS EDM
 WHERE MRP.t_item=ITM.t_item AND MRP.t_suno='90007G' AND EDM.t_eitm=ITM.t_item AND
     ( (MRP.t_pddt >= EDM.t_indt and MRP.t_pddt < EDM.t_exdt) OR (MRP.t_pddt >= EDM.t_indt and EDM.t_exdt='1753-01-01') )
   AND EDM.t_rele=1 and MRP.t_item in $con
 GROUP BY MRP.t_item, MRP.t_cwar, ITM.t_dsca, MRP.t_oqan,EDM.t_revi, SUBSTRING(convert(varchar,MRP.t_podt,120), 0, 11), SUBSTRING(convert(varchar,dateadd(DD,-49,MRP.t_pddt),120), 0, 11) 
 )
UNION
(SELECT '420' as erp, MRP.t_cwar, MRP.t_item, ITM.t_dsca, MRP.t_oqan, EDM.t_revi AS t_revi,
        SUBSTRING(convert(varchar,MRP.t_podt,120), 0, 11) AS t_podt,
        SUBSTRING(convert(varchar,dateadd(DD,-49,MRP.t_pddt),120), 0, 11) AS t_pddt,
        (SELECT TOP 1 AIC.t_aitc FROM ttiitm012420 AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
 FROM ttimrp021420 AS MRP, ttiitm001420 AS ITM, ttirou102420 AS ROU2, ttirou001420 AS ROU1, ttiedm100420 AS EDM
 WHERE MRP.t_item=ITM.t_item AND MRP.t_suno='10721G' AND EDM.t_eitm=ITM.t_item AND
     ( (MRP.t_pddt >= EDM.t_indt and MRP.t_pddt < EDM.t_exdt) OR (MRP.t_pddt >= EDM.t_indt and EDM.t_exdt='1753-01-01') )
   AND EDM.t_rele=1 and MRP.t_item in $con
 GROUP BY MRP.t_item, MRP.t_cwar, ITM.t_dsca, MRP.t_oqan,EDM.t_revi, SUBSTRING(convert(varchar,MRP.t_podt,120), 0, 11), SUBSTRING(convert(varchar,dateadd(DD,-49,MRP.t_pddt),120), 0, 11) 
 )
UNION
(SELECT '500' as erp,MRP.t_cwar, MRP.t_item, ITM.t_dsca, MRP.t_oqan, EDM.t_revi AS t_revi,
        SUBSTRING(convert(varchar,MRP.t_podt,120), 0, 11) AS t_podt,
        SUBSTRING(convert(varchar,dateadd(DD,-49,MRP.t_pddt),120), 0, 11)  AS t_pddt,
        (SELECT TOP 1 AIC.t_aitc FROM ttiitm012500 AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
 FROM ttimrp021500 AS MRP, ttiitm001500 AS ITM, ttirou102500 AS ROU2, ttirou001500 AS ROU1, ttiedm100500 AS EDM
 WHERE MRP.t_item=ITM.t_item AND MRP.t_suno='DE9301' AND EDM.t_eitm=ITM.t_item AND
     ( (MRP.t_pddt >= EDM.t_indt and MRP.t_pddt < EDM.t_exdt) OR (MRP.t_pddt >= EDM.t_indt and EDM.t_exdt='1753-01-01') )
   AND EDM.t_rele=1 and MRP.t_item in $con
 GROUP BY MRP.t_item, MRP.t_cwar, ITM.t_dsca, MRP.t_oqan,EDM.t_revi, SUBSTRING(convert(varchar,MRP.t_podt,120), 0, 11), SUBSTRING(convert(varchar,dateadd(DD,-49,MRP.t_pddt),120), 0, 11) 
)
UNION
(SELECT '520' as erp,MRP.t_cwar, MRP.t_item, ITM.t_dsca, MRP.t_oqan, EDM.t_revi AS t_revi,
        SUBSTRING(convert(varchar,MRP.t_podt,120), 0, 11) AS t_podt,
        SUBSTRING(convert(varchar,dateadd(DD,-49,MRP.t_pddt),120), 0, 11)  AS t_pddt,
        (SELECT TOP 1 AIC.t_aitc FROM ttiitm012520 AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
 FROM ttimrp021520 AS MRP, ttiitm001520 AS ITM, ttirou102520 AS ROU2, ttirou001520 AS ROU1, ttiedm100520 AS EDM
 WHERE MRP.t_item=ITM.t_item AND MRP.t_suno='DE9301' AND EDM.t_eitm=ITM.t_item AND
     ( (MRP.t_pddt >= EDM.t_indt and MRP.t_pddt < EDM.t_exdt) OR (MRP.t_pddt >= EDM.t_indt and EDM.t_exdt='1753-01-01') )
   AND EDM.t_rele=1 and MRP.t_item in $con
 GROUP BY MRP.t_item, MRP.t_cwar, ITM.t_dsca, MRP.t_oqan,EDM.t_revi, SUBSTRING(convert(varchar,MRP.t_podt,120), 0, 11), SUBSTRING(convert(varchar,dateadd(DD,-49,MRP.t_pddt),120), 0, 11) 
 )
 UNION
(SELECT '410' as erp,MRP.t_cwar, MRP.t_item, ITM.t_dsca, MRP.t_oqan, EDM.t_revi AS t_revi, 
       SUBSTRING(convert(varchar,MRP.t_podt,120), 0, 11) AS t_podt, 
       SUBSTRING(convert(varchar,dateadd(DD,-49,MRP.t_pddt),120), 0, 11)  AS t_pddt, 
       (SELECT TOP 1 AIC.t_aitc FROM ttiitm012410 AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn 
FROM ttimrp021410 AS MRP, ttiitm001410 AS ITM, ttirou102410 AS ROU2, ttirou001410 AS ROU1, ttiedm100410 AS EDM 
WHERE MRP.t_item=ITM.t_item AND MRP.t_suno='90007G' AND EDM.t_eitm=ITM.t_item AND 
      ( (MRP.t_pddt >= EDM.t_indt and MRP.t_pddt < EDM.t_exdt) OR (MRP.t_pddt >= EDM.t_indt and EDM.t_exdt='1753-01-01') ) 
  AND EDM.t_rele=1 and MRP.t_item in $con
GROUP BY MRP.t_item, MRP.t_cwar, ITM.t_dsca, MRP.t_oqan,EDM.t_revi, SUBSTRING(convert(varchar,MRP.t_podt,120), 0, 11), SUBSTRING(convert(varchar,dateadd(DD,-49,MRP.t_pddt),120), 0, 11) 
 )
UNION
(SELECT '250' as erp,MRP.t_cwar, MRP.t_item, ITM.t_dsca, MRP.t_oqan, EDM.t_revi AS t_revi, 
       SUBSTRING(convert(varchar,MRP.t_podt,120), 0, 11) AS t_podt, 
       SUBSTRING(convert(varchar,dateadd(DD,-49,MRP.t_pddt),120), 0, 11)  AS t_pddt, 
       (SELECT TOP 1 AIC.t_aitc FROM ttiitm012250 AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn 
FROM ttimrp021250 AS MRP, ttiitm001250 AS ITM, ttirou102250 AS ROU2, ttirou001250 AS ROU1, ttiedm100250 AS EDM 
WHERE MRP.t_item=ITM.t_item AND MRP.t_suno='14866' AND EDM.t_eitm=ITM.t_item AND 
      ( (MRP.t_pddt >= EDM.t_indt and MRP.t_pddt < EDM.t_exdt) OR (MRP.t_pddt >= EDM.t_indt and EDM.t_exdt='1753-01-01') ) 
  AND EDM.t_rele=1 and MRP.t_item in $con
GROUP BY MRP.t_item, MRP.t_cwar, ITM.t_dsca, MRP.t_oqan,EDM.t_revi, SUBSTRING(convert(varchar,MRP.t_podt,120), 0, 11), SUBSTRING(convert(varchar,dateadd(DD,-49,MRP.t_pddt),120), 0, 11)  
 )
UNION
(SELECT '570' as erp,MRP.t_cwar, MRP.t_item, ITM.t_dsca, MRP.t_oqan, EDM.t_revi AS t_revi, 
       SUBSTRING(convert(varchar,MRP.t_podt,120), 0, 11) AS t_podt, 
       SUBSTRING(convert(varchar,dateadd(DD,-49,MRP.t_pddt),120), 0, 11)  AS t_pddt, 
       (SELECT TOP 1 AIC.t_aitc FROM ttiitm012570 AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn 
FROM ttimrp021570 AS MRP, ttiitm001570 AS ITM, ttirou102570 AS ROU2, ttirou001570 AS ROU1, ttiedm100570 AS EDM 
WHERE MRP.t_item=ITM.t_item AND MRP.t_suno='90007G' AND EDM.t_eitm=ITM.t_item AND 
      ( (MRP.t_pddt >= EDM.t_indt and MRP.t_pddt < EDM.t_exdt) OR (MRP.t_pddt >= EDM.t_indt and EDM.t_exdt='1753-01-01') ) 
  AND EDM.t_rele=1 and MRP.t_item in $con
GROUP BY MRP.t_item, MRP.t_cwar, ITM.t_dsca, MRP.t_oqan,EDM.t_revi, SUBSTRING(convert(varchar,MRP.t_podt,120), 0, 11), SUBSTRING(convert(varchar,dateadd(DD,-49,MRP.t_pddt),120), 0, 11)  
 )

SQL;
                $opt1 = 'odbc';
                $opt2 = ['src' => 'baan'];
            break;
            case '400':
            case '410':
            case '420':
            $con = <<<EOF
            (SELECT
               itm.t_item
           FROM
               ttiitm001$erp AS itm
           WHERE itm.t_suno = '$vendorid')
EOF;
            $query = <<<EOF
            (SELECT '500' as erp, MRP.t_cwar, MRP.t_item, ITM.t_dsca, MRP.t_oqan, EDM.t_revi AS t_revi,
        SUBSTRING(convert(varchar,MRP.t_podt,120), 0, 11) AS t_podt,
        SUBSTRING(convert(varchar,dateadd(DD,-49,MRP.t_pddt),120), 0, 11)  AS t_pddt,
        (SELECT TOP 1 AIC.t_aitc FROM ttiitm012500 AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
 FROM ttimrp021500 AS MRP, ttiitm001500 AS ITM, ttirou102500 AS ROU2, ttirou001500 AS ROU1, ttiedm100500 AS EDM
 WHERE MRP.t_item=ITM.t_item AND MRP.t_suno='TL4000' AND EDM.t_eitm=ITM.t_item AND
        ( (MRP.t_pddt >= EDM.t_indt and MRP.t_pddt < EDM.t_exdt) OR (MRP.t_pddt >= EDM.t_indt and EDM.t_exdt='1753-01-01') )
        AND EDM.t_rele=1 and MRP.t_item in $con
 GROUP BY MRP.t_item, MRP.t_cwar, ITM.t_dsca, MRP.t_oqan,EDM.t_revi, SUBSTRING(convert(varchar,MRP.t_podt,120), 0, 11), SUBSTRING(convert(varchar,dateadd(DD,-49,MRP.t_pddt),120), 0, 11) 
 )UNION(
            SELECT
                $erp as erp,
                MRP.t_cwar,
                MRP.t_item,
                ITM.t_dsca,
                MRP.t_oqan,
                EDM.t_revi AS t_revi,
                SUBSTRING(convert(varchar,MRP.t_podt,120), 0, 11) AS t_podt,
                SUBSTRING(convert(varchar,MRP.t_pddt,120), 0, 11) AS t_pddt,
                (SELECT TOP 1 AIC.t_aitc FROM ttiitm012$erp AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
            FROM
                ttimrp021$erp AS MRP,
                ttiitm001$erp AS ITM,
                ttirou102$erp AS ROU2,
                ttirou001$erp AS ROU1,
                ttiedm100$erp AS EDM
            WHERE
                MRP.t_item=ITM.t_item
                AND MRP.t_suno='$vendorid'
                AND EDM.t_eitm=ITM.t_item
                AND (
                    (MRP.t_pddt >= EDM.t_indt and MRP.t_pddt < EDM.t_exdt)
                    OR (MRP.t_pddt >= EDM.t_indt and EDM.t_exdt='1753-01-01')
                ) AND EDM.t_rele=1
            GROUP BY MRP.t_item, MRP.t_cwar, ITM.t_dsca, MRP.t_oqan,EDM.t_revi,
            	SUBSTRING(convert(varchar,MRP.t_podt,120), 0, 11), SUBSTRING(convert(varchar,MRP.t_pddt,120), 0, 11)
 	)
EOF;
            $opt1 = 'odbc';
            $opt2 = ['src' => 'baan'];
            break;
            case '500':
            $con = <<<EOF
            (SELECT
               itm.t_item
           FROM
               ttiitm001$erp AS itm
           WHERE itm.t_suno = '$vendorid')
EOF;
            $query = <<<EOF
            (SELECT '420' as erp, MRP.t_cwar, MRP.t_item, ITM.t_dsca, MRP.t_oqan, EDM.t_revi AS t_revi,
        SUBSTRING(convert(varchar,MRP.t_podt,120), 0, 11) AS t_podt,
        SUBSTRING(convert(varchar,dateadd(DD,-49,MRP.t_pddt),120), 0, 11) as t_pddt, 
        (SELECT TOP 1 AIC.t_aitc FROM ttiitm012420 AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
 FROM ttimrp021420 AS MRP, ttiitm001420 AS ITM, ttirou102420 AS ROU2, ttirou001420 AS ROU1, ttiedm100420 AS EDM
 WHERE MRP.t_item=ITM.t_item AND MRP.t_suno='11568' AND EDM.t_eitm=ITM.t_item AND
        ( (MRP.t_pddt >= EDM.t_indt and MRP.t_pddt < EDM.t_exdt) OR (MRP.t_pddt >= EDM.t_indt and EDM.t_exdt='1753-01-01') )
        AND EDM.t_rele=1 and MRP.t_item in $con
 GROUP BY MRP.t_item, MRP.t_cwar, ITM.t_dsca, MRP.t_oqan,EDM.t_revi, SUBSTRING(convert(varchar,MRP.t_podt,120), 0, 11), SUBSTRING(convert(varchar,dateadd(DD,-49,MRP.t_pddt),120), 0, 11) 
 )UNION(
            SELECT
                $erp as erp,
                MRP.t_cwar,
                MRP.t_item,
                ITM.t_dsca,
                MRP.t_oqan,
                EDM.t_revi AS t_revi,
                SUBSTRING(convert(varchar,MRP.t_podt,120), 0, 11) AS t_podt,
                SUBSTRING(convert(varchar,MRP.t_pddt,120), 0, 11) AS t_pddt,
                (SELECT TOP 1 AIC.t_aitc FROM ttiitm012$erp AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
            FROM
                ttimrp021$erp AS MRP,
                ttiitm001$erp AS ITM,
                ttirou102$erp AS ROU2,
                ttirou001$erp AS ROU1,
                ttiedm100$erp AS EDM
            WHERE
                MRP.t_item=ITM.t_item
                AND MRP.t_suno='$vendorid'
                AND EDM.t_eitm=ITM.t_item
                AND (
                    (MRP.t_pddt >= EDM.t_indt and MRP.t_pddt < EDM.t_exdt)
                    OR (MRP.t_pddt >= EDM.t_indt and EDM.t_exdt='1753-01-01')
                ) AND EDM.t_rele=1
            GROUP BY MRP.t_item, MRP.t_cwar, ITM.t_dsca, MRP.t_oqan,EDM.t_revi,
            	SUBSTRING(convert(varchar,MRP.t_podt,120), 0, 11), SUBSTRING(convert(varchar,MRP.t_pddt,120), 0, 11)
 	)
 	ORDER BY SUBSTRING(convert(varchar,dateadd(DD,-49,MRP.t_pddt),120), 0, 11) ASC
EOF;
            $opt1 = 'odbc';
            $opt2 = ['src' => 'baan'];
            break;
            case '520':
            case '600':
            case '640':
            case '660':
            case '570':
            case '220':
                $query = <<<EOF
            SELECT
                $erp as erp,
                MRP.t_cwar,
                MRP.t_item,
                ITM.t_dsca,
                MRP.t_oqan,
                EDM.t_revi AS t_revi,
                SUBSTRING(convert(varchar,MRP.t_podt,120), 0, 11) AS t_podt,
                SUBSTRING(convert(varchar,MRP.t_pddt,120), 0, 11) AS t_pddt,
                (SELECT TOP 1 AIC.t_aitc FROM ttiitm012$erp AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
            FROM
                ttimrp021$erp AS MRP,
                ttiitm001$erp AS ITM,
                ttirou102$erp AS ROU2,
                ttirou001$erp AS ROU1,
                ttiedm100$erp AS EDM
            WHERE
                MRP.t_item=ITM.t_item
                AND MRP.t_suno='$vendorid'
                AND EDM.t_eitm=ITM.t_item
            	AND (
                  (MRP.t_pddt >= EDM.t_indt and MRP.t_pddt < EDM.t_exdt)
                  OR (MRP.t_pddt >= EDM.t_indt and EDM.t_exdt='1753-01-01')
                ) AND EDM.t_rele=1
            GROUP BY MRP.t_item, MRP.t_cwar, ITM.t_dsca, MRP.t_oqan,EDM.t_revi,
            	SUBSTRING(convert(varchar,MRP.t_podt,120), 0, 11), SUBSTRING(convert(varchar,MRP.t_pddt,120), 0, 11)
            ORDER BY t_podt
EOF;

                $opt1 = 'odbc';
                $opt2 = ['src' => 'baan'];
                break;
            default:
                return;
        }

        return tldUtils::getSqlToAssocArray($query, $opt1, $opt2);
    }

    public static function byVendorERPSubcontract($vendorid, $erp)
    {
        if (empty($vendorid) || empty($erp)) {
            return;
        }

        switch ($erp) {
            case '400':
            case '410':
            case '420':
            case '500':
            case '520':
            case '600':
            case '620':
            case '640':
            case '660':
            case '570':
                $query = <<<EOF
            SELECT
                MRP2.t_cwar,
                MRP2.t_item,
                ITM.t_dsca,
                MRP2.t_oqan,
                SUBSTRING(convert(varchar,MRP2.t_psdt,120), 0, 11) AS t_podt,
                SUBSTRING(convert(varchar,MRP2.t_pfdt,120), 0, 11) AS t_pddt,
                EDM.t_revi AS t_revi
            FROM
                ttimrp020$erp AS MRP2,
                ttiitm001$erp AS ITM,
                ttirou102$erp AS ROU2,
                ttirou001$erp AS ROU1,
                ttiedm100$erp AS EDM
            WHERE
                MRP2.t_item=ITM.t_item
                AND (ROU2.t_mitm=MRP2.t_item AND ROU2.t_opro=MRP2.t_opro)
                AND ROU1.t_cwoc = ROU2.t_cwoc
                AND ROU1.t_suno='$vendorid'
                AND EDM.t_eitm=ITM.t_item
            	AND (
                  (MRP2.t_psdt >= EDM.t_indt and MRP2.t_psdt < EDM.t_exdt)
                  OR (MRP2.t_psdt >= EDM.t_indt and EDM.t_exdt='1753-01-01')
                ) AND EDM.t_rele=1
            GROUP BY MRP2.t_item, MRP2.t_cwar, ITM.t_dsca, MRP2.t_oqan, EDM.t_revi,
                SUBSTRING(convert(varchar,MRP2.t_psdt,120), 0, 11), SUBSTRING(convert(varchar,MRP2.t_pfdt,120), 0, 11)
            ORDER BY t_podt
EOF;
                $opt1 = 'odbc';
                $opt2 = ['src' => 'baan'];
                break;
            default:
                return [];
        }

        return tldUtils::getSqlToAssocArray($query, $opt1, $opt2);
    }


    /**
     * WARNING DEPRECATED !!!
     * USE tldMRP::byConstraints() instead
     */
    public static function countByVendorERP($vendorid, $erp)
    {
        if (empty($vendorid) || empty($erp)) {
            return [];
        }
        switch ($erp) {
            case '620':
                $con = <<<EOF
                (SELECT
                   itm.t_item
               FROM
                   ttiitm001$erp AS itm
               WHERE itm.t_suno = '$vendorid')
EOF;
                $query = <<<EOF
(SELECT
     SUBSTRING(convert(varchar,t_pddt,120), 0, 8) AS pddt_month,
     t_dsca,
     MRP.t_item,
     SUM(MRP.t_oqan) AS count_item,
     (SELECT TOP 1 AIC.t_aitc FROM ttiitm012220 AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
 FROM
     ttimrp021220 AS MRP,
     ttiitm001220 AS ITM
 WHERE
         MRP.t_item=ITM.t_item and MRP.t_item in $con
   AND MRP.t_suno='TLD006'
 GROUP BY MRP.t_item, SUBSTRING(convert(varchar,t_pddt,120), 0, 8), t_dsca
)
UNION
(SELECT
     SUBSTRING(convert(varchar,t_pddt,120), 0, 8) AS pddt_month,
     t_dsca,
     MRP.t_item,
     SUM(MRP.t_oqan) AS count_item,
     (SELECT TOP 1 AIC.t_aitc FROM ttiitm012400 AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
 FROM
     ttimrp021400 AS MRP,
     ttiitm001400 AS ITM
 WHERE
         MRP.t_item=ITM.t_item and MRP.t_item in $con
   AND MRP.t_suno='90007G'
 GROUP BY MRP.t_item, SUBSTRING(convert(varchar,t_pddt,120), 0, 8), t_dsca
)
UNION
(SELECT
     SUBSTRING(convert(varchar,t_pddt,120), 0, 8) AS pddt_month,
     t_dsca,
     MRP.t_item,
     SUM(MRP.t_oqan) AS count_item,
     (SELECT TOP 1 AIC.t_aitc FROM ttiitm012420 AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
 FROM
     ttimrp021420 AS MRP,
     ttiitm001420 AS ITM
 WHERE
         MRP.t_item=ITM.t_item and MRP.t_item in $con
   AND MRP.t_suno='10721G'
 GROUP BY MRP.t_item, SUBSTRING(convert(varchar,t_pddt,120), 0, 8), t_dsca
)
UNION
(SELECT
     SUBSTRING(convert(varchar,t_pddt,120), 0, 8) AS pddt_month,
     t_dsca,
     MRP.t_item,
     SUM(MRP.t_oqan) AS count_item,
     (SELECT TOP 1 AIC.t_aitc FROM ttiitm012500 AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
 FROM
     ttimrp021500 AS MRP,
     ttiitm001500 AS ITM
 WHERE
         MRP.t_item=ITM.t_item and MRP.t_item in $con
   AND MRP.t_suno='DE9301'
 GROUP BY MRP.t_item, SUBSTRING(convert(varchar,t_pddt,120), 0, 8), t_dsca
)
UNION
(SELECT
     SUBSTRING(convert(varchar,t_pddt,120), 0, 8) AS pddt_month,
     t_dsca,
     MRP.t_item,
     SUM(MRP.t_oqan) AS count_item,
     (SELECT TOP 1 AIC.t_aitc FROM ttiitm012520 AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
 FROM
     ttimrp021520 AS MRP,
     ttiitm001520 AS ITM
 WHERE
         MRP.t_item=ITM.t_item and MRP.t_item in $con
   AND MRP.t_suno='DE9301'
 GROUP BY MRP.t_item, SUBSTRING(convert(varchar,t_pddt,120), 0, 8), t_dsca
)
UNION
(SELECT
     SUBSTRING(convert(varchar,t_pddt,120), 0, 8) AS pddt_month,
     t_dsca,
     MRP.t_item,
     SUM(MRP.t_oqan) AS count_item,
     (SELECT TOP 1 AIC.t_aitc FROM ttiitm012410 AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
 FROM
     ttimrp021410 AS MRP,
     ttiitm001410 AS ITM
 WHERE
         MRP.t_item=ITM.t_item and MRP.t_item in $con
   AND MRP.t_suno='90007G'
 GROUP BY MRP.t_item, SUBSTRING(convert(varchar,t_pddt,120), 0, 8), t_dsca
)
UNION
(SELECT
     SUBSTRING(convert(varchar,t_pddt,120), 0, 8) AS pddt_month,
     t_dsca,
     MRP.t_item,
     SUM(MRP.t_oqan) AS count_item,
     (SELECT TOP 1 AIC.t_aitc FROM ttiitm012250 AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
 FROM
     ttimrp021250 AS MRP,
     ttiitm001250 AS ITM
 WHERE
         MRP.t_item=ITM.t_item and MRP.t_item in $con
   AND MRP.t_suno='14866'
 GROUP BY MRP.t_item, SUBSTRING(convert(varchar,t_pddt,120), 0, 8), t_dsca
)
UNION
(SELECT
     SUBSTRING(convert(varchar,t_pddt,120), 0, 8) AS pddt_month,
     t_dsca,
     MRP.t_item,
     SUM(MRP.t_oqan) AS count_item,
     (SELECT TOP 1 AIC.t_aitc FROM ttiitm012570 AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
 FROM
     ttimrp021570 AS MRP,
     ttiitm001570 AS ITM
 WHERE
         MRP.t_item=ITM.t_item and MRP.t_item in $con
   AND MRP.t_suno='90007G'
 GROUP BY MRP.t_item, SUBSTRING(convert(varchar,t_pddt,120), 0, 8), t_dsca
)
EOF;
            $opt1 = 'odbc';
            $opt2 = ['src' => 'baan'];
            break;
            case '400':
                $con = <<<EOF
                (SELECT
                   itm.t_item
               FROM
                   ttiitm001$erp AS itm
               WHERE itm.t_suno = '$vendorid')
EOF;
                $query =<<<EOF
            (SELECT
     SUBSTRING(convert(varchar,t_pddt,120), 0, 8) AS pddt_month,
     ITM.t_dsca,
     MRP.t_item,
     SUM(MRP.t_oqan) AS count_item,
     (SELECT TOP 1 AIC.t_aitc FROM ttiitm012420 AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
 FROM
     ttimrp021420 AS MRP,
     ttiitm001420 AS ITM
 WHERE
         MRP.t_item=ITM.t_item and MRP.t_item in $con
   AND MRP.t_suno='10299'
 GROUP BY MRP.t_item, SUBSTRING(convert(varchar,t_pddt,120), 0, 8), t_dsca
)UNION
(            SELECT
                SUBSTRING(convert(varchar,t_pddt,120), 0, 8) AS pddt_month,
                ITM.t_dsca,
                MRP.t_item,
                SUM(MRP.t_oqan) AS count_item,
                (SELECT TOP 1 AIC.t_aitc FROM ttiitm012$erp AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
            FROM
                ttimrp021$erp AS MRP,
                ttiitm001$erp AS ITM
            WHERE
                MRP.t_item=ITM.t_item
                AND MRP.t_suno='$vendorid' 
            GROUP BY MRP.t_item, SUBSTRING(convert(varchar,t_pddt,120), 0, 8), t_dsca
            )
EOF;
                $opt1 = 'odbc';
                $opt2 = ['src' => 'baan'];
            break;
            case '410':
                $con = <<<EOF
                (SELECT
                   itm.t_item
               FROM
                   ttiitm001$erp AS itm
               WHERE itm.t_suno = '$vendorid')
EOF;
                $query =<<<EOF
            (SELECT
     SUBSTRING(convert(varchar,t_pddt,120), 0, 8) AS pddt_month,
     ITM.t_dsca,
     MRP.t_item,
     SUM(MRP.t_oqan) AS count_item,
     (SELECT TOP 1 AIC.t_aitc FROM ttiitm012420 AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
 FROM
     ttimrp021420 AS MRP,
     ttiitm001420 AS ITM
 WHERE
         MRP.t_item=ITM.t_item and MRP.t_item in $con
   AND MRP.t_suno='12749'
 GROUP BY MRP.t_item, SUBSTRING(convert(varchar,t_pddt,120), 0, 8), t_dsca
)UNION
(            SELECT
                SUBSTRING(convert(varchar,t_pddt,120), 0, 8) AS pddt_month,
                ITM.t_dsca,
                MRP.t_item,
                SUM(MRP.t_oqan) AS count_item,
                (SELECT TOP 1 AIC.t_aitc FROM ttiitm012$erp AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
            FROM
                ttimrp021$erp AS MRP,
                ttiitm001$erp AS ITM
            WHERE
                MRP.t_item=ITM.t_item
                AND MRP.t_suno='$vendorid' 
            GROUP BY MRP.t_item, SUBSTRING(convert(varchar,t_pddt,120), 0, 8), t_dsca
            )
EOF;
                $opt1 = 'odbc';
                $opt2 = ['src' => 'baan'];
            break;
            case '420':
            $con = <<<EOF
                (SELECT
                   itm.t_item
               FROM
                   ttiitm001$erp AS itm
               WHERE itm.t_suno = '$vendorid')
EOF;
            $query =<<<EOF
            (SELECT
     SUBSTRING(convert(varchar,t_pddt,120), 0, 8) AS pddt_month,
     t_dsca,
     MRP.t_item,
     SUM(MRP.t_oqan) AS count_item,
     (SELECT TOP 1 AIC.t_aitc FROM ttiitm012500 AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
 FROM
     ttimrp021500 AS MRP,
     ttiitm001500 AS ITM
 WHERE
         MRP.t_item=ITM.t_item and MRP.t_item in $con
   AND MRP.t_suno='TL4000'
 GROUP BY MRP.t_item, SUBSTRING(convert(varchar,t_pddt,120), 0, 8), t_dsca
)UNION
(            SELECT
                SUBSTRING(convert(varchar,t_pddt,120), 0, 8) AS pddt_month,
                t_dsca,
                MRP.t_item,
                SUM(MRP.t_oqan) AS count_item,
                (SELECT TOP 1 AIC.t_aitc FROM ttiitm012$erp AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
            FROM
                ttimrp021$erp AS MRP,
                ttiitm001$erp AS ITM
            WHERE
                MRP.t_item=ITM.t_item
                AND MRP.t_suno='$vendorid' 
            GROUP BY MRP.t_item, SUBSTRING(convert(varchar,t_pddt,120), 0, 8), t_dsca
            )
EOF;
            $opt1 = 'odbc';
            $opt2 = ['src' => 'baan'];
            break;
            case '500':
                $con = <<<EOF
                (SELECT
                   itm.t_item
               FROM
                   ttiitm001$erp AS itm
               WHERE itm.t_suno = '$vendorid')
EOF;
                $query =<<<EOF
            (SELECT
     SUBSTRING(convert(varchar,t_pddt,120), 0, 8) AS pddt_month,
     ITM.t_dsca,
     MRP.t_item,
     SUM(MRP.t_oqan) AS count_item,
     (SELECT TOP 1 AIC.t_aitc FROM ttiitm012420 AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
 FROM
     ttimrp021420 AS MRP,
     ttiitm001420 AS ITM
 WHERE
         MRP.t_item=ITM.t_item and MRP.t_item in $con
   AND MRP.t_suno='11568'
 GROUP BY MRP.t_item, SUBSTRING(convert(varchar,t_pddt,120), 0, 8), t_dsca
)UNION
(            SELECT
                SUBSTRING(convert(varchar,t_pddt,120), 0, 8) AS pddt_month,
                ITM.t_dsca,
                MRP.t_item,
                SUM(MRP.t_oqan) AS count_item,
                (SELECT TOP 1 AIC.t_aitc FROM ttiitm012$erp AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
            FROM
                ttimrp021$erp AS MRP,
                ttiitm001$erp AS ITM
            WHERE
                MRP.t_item=ITM.t_item
                AND MRP.t_suno='$vendorid' 
            GROUP BY MRP.t_item, SUBSTRING(convert(varchar,t_pddt,120), 0, 8), t_dsca
            )
EOF;
                $opt1 = 'odbc';
                $opt2 = ['src' => 'baan'];
            break;
            case '520':
                $con = <<<EOF
                (SELECT
                   itm.t_item
               FROM
                   ttiitm001$erp AS itm
               WHERE itm.t_suno = '$vendorid')
EOF;
                $query =<<<EOF
            (SELECT
     SUBSTRING(convert(varchar,t_pddt,120), 0, 8) AS pddt_month,
     ITM.t_dsca,
     MRP.t_item,
     SUM(MRP.t_oqan) AS count_item,
     (SELECT TOP 1 AIC.t_aitc FROM ttiitm012420 AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
 FROM
     ttimrp021420 AS MRP,
     ttiitm001420 AS ITM
 WHERE
         MRP.t_item=ITM.t_item and MRP.t_item in $con
   AND MRP.t_suno='11304'
 GROUP BY MRP.t_item, SUBSTRING(convert(varchar,t_pddt,120), 0, 8), t_dsca
)UNION
(            SELECT
                SUBSTRING(convert(varchar,t_pddt,120), 0, 8) AS pddt_month,
                ITM.t_dsca,
                MRP.t_item,
                SUM(MRP.t_oqan) AS count_item,
                (SELECT TOP 1 AIC.t_aitc FROM ttiitm012$erp AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
            FROM
                ttimrp021$erp AS MRP,
                ttiitm001$erp AS ITM
            WHERE
                MRP.t_item=ITM.t_item
                AND MRP.t_suno='$vendorid' 
            GROUP BY MRP.t_item, SUBSTRING(convert(varchar,t_pddt,120), 0, 8), t_dsca
            )
EOF;
                $opt1 = 'odbc';
                $opt2 = ['src' => 'baan'];
            break;
            case '600':
                $con = <<<EOF
                (SELECT
                   itm.t_item
               FROM
                   ttiitm001$erp AS itm
               WHERE itm.t_suno = '$vendorid')
EOF;
                $query =<<<EOF
            (SELECT
     SUBSTRING(convert(varchar,t_pddt,120), 0, 8) AS pddt_month,
     ITM.t_dsca,
     MRP.t_item,
     SUM(MRP.t_oqan) AS count_item,
     (SELECT TOP 1 AIC.t_aitc FROM ttiitm012420 AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
 FROM
     ttimrp021420 AS MRP,
     ttiitm001420 AS ITM
 WHERE
         MRP.t_item=ITM.t_item and MRP.t_item in $con
   AND MRP.t_suno='10721S'
 GROUP BY MRP.t_item, SUBSTRING(convert(varchar,t_pddt,120), 0, 8), t_dsca
)UNION
(            SELECT
                SUBSTRING(convert(varchar,t_pddt,120), 0, 8) AS pddt_month,
                ITM.t_dsca,
                MRP.t_item,
                SUM(MRP.t_oqan) AS count_item,
                (SELECT TOP 1 AIC.t_aitc FROM ttiitm012$erp AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
            FROM
                ttimrp021$erp AS MRP,
                ttiitm001$erp AS ITM
            WHERE
                MRP.t_item=ITM.t_item
                AND MRP.t_suno='$vendorid' 
            GROUP BY MRP.t_item, SUBSTRING(convert(varchar,t_pddt,120), 0, 8), t_dsca
            )
EOF;
                $opt1 = 'odbc';
                $opt2 = ['src' => 'baan'];
                break;
            case '640':
            $con = <<<EOF
                (SELECT
                   itm.t_item
               FROM
                   ttiitm001$erp AS itm
               WHERE itm.t_suno = '$vendorid')
EOF;
            $query =<<<EOF
            (SELECT
     SUBSTRING(convert(varchar,t_pddt,120), 0, 8) AS pddt_month,
     ITM.t_dsca,
     MRP.t_item,
     SUM(MRP.t_oqan) AS count_item,
     (SELECT TOP 1 AIC.t_aitc FROM ttiitm012420 AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
 FROM
     ttimrp021420 AS MRP,
     ttiitm001420 AS ITM
 WHERE
         MRP.t_item=ITM.t_item and MRP.t_item in $con
   AND MRP.t_suno='10721'
 GROUP BY MRP.t_item, SUBSTRING(convert(varchar,t_pddt,120), 0, 8), t_dsca
)UNION
(            SELECT
                SUBSTRING(convert(varchar,t_pddt,120), 0, 8) AS pddt_month,
                ITM.t_dsca,
                MRP.t_item,
                SUM(MRP.t_oqan) AS count_item,
                (SELECT TOP 1 AIC.t_aitc FROM ttiitm012$erp AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
            FROM
                ttimrp021$erp AS MRP,
                ttiitm001$erp AS ITM
            WHERE
                MRP.t_item=ITM.t_item
                AND MRP.t_suno='$vendorid' 
            GROUP BY MRP.t_item, SUBSTRING(convert(varchar,t_pddt,120), 0, 8), t_dsca
            )
EOF;
            $opt1 = 'odbc';
            $opt2 = ['src' => 'baan'];
            break;
            case '660':
                $con = <<<EOF
                (SELECT
                   itm.t_item
               FROM
                   ttiitm001$erp AS itm
               WHERE itm.t_suno = '$vendorid')
EOF;
                $query =<<<EOF
            (SELECT
     SUBSTRING(convert(varchar,t_pddt,120), 0, 8) AS pddt_month,
     ITM.t_dsca,
     MRP.t_item,
     SUM(MRP.t_oqan) AS count_item,
     (SELECT TOP 1 AIC.t_aitc FROM ttiitm012420 AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
 FROM
     ttimrp021420 AS MRP,
     ttiitm001420 AS ITM
 WHERE
         MRP.t_item=ITM.t_item and MRP.t_item in $con
   AND MRP.t_suno='11582'
 GROUP BY MRP.t_item, SUBSTRING(convert(varchar,t_pddt,120), 0, 8), t_dsca
)UNION
(            SELECT
                SUBSTRING(convert(varchar,t_pddt,120), 0, 8) AS pddt_month,
                ITM.t_dsca,
                MRP.t_item,
                SUM(MRP.t_oqan) AS count_item,
                (SELECT TOP 1 AIC.t_aitc FROM ttiitm012$erp AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
            FROM
                ttimrp021$erp AS MRP,
                ttiitm001$erp AS ITM
            WHERE
                MRP.t_item=ITM.t_item
                AND MRP.t_suno='$vendorid' 
            GROUP BY MRP.t_item, SUBSTRING(convert(varchar,t_pddt,120), 0, 8), t_dsca
            )
EOF;
                $opt1 = 'odbc';
                $opt2 = ['src' => 'baan'];
            break;
            case '570':
            case '540':
            $con = <<<EOF
                (SELECT
                   itm.t_item
               FROM
                   ttiitm001$erp AS itm
               WHERE itm.t_suno = '$vendorid')
EOF;
            $query =<<<EOF
            (SELECT
     SUBSTRING(convert(varchar,t_pddt,120), 0, 8) AS pddt_month,
     ITM.t_dsca,
     MRP.t_item,
     SUM(MRP.t_oqan) AS count_item,
     (SELECT TOP 1 AIC.t_aitc FROM ttiitm012420 AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
 FROM
     ttimrp021420 AS MRP,
     ttiitm001420 AS ITM
 WHERE
         MRP.t_item=ITM.t_item and MRP.t_item in $con
   AND MRP.t_suno='10301'
 GROUP BY MRP.t_item, SUBSTRING(convert(varchar,t_pddt,120), 0, 8), t_dsca
)UNION
(            SELECT
                SUBSTRING(convert(varchar,t_pddt,120), 0, 8) AS pddt_month,
                ITM.t_dsca,
                MRP.t_item,
                SUM(MRP.t_oqan) AS count_item,
                (SELECT TOP 1 AIC.t_aitc FROM ttiitm012$erp AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
            FROM
                ttimrp021$erp AS MRP,
                ttiitm001$erp AS ITM
            WHERE
                MRP.t_item=ITM.t_item
                AND MRP.t_suno='$vendorid' 
            GROUP BY MRP.t_item, SUBSTRING(convert(varchar,t_pddt,120), 0, 8), t_dsca
            )
EOF;
            $opt1 = 'odbc';
            $opt2 = ['src' => 'baan'];
            break;
            case '680':
                $con = <<<EOF
                (SELECT
                   itm.t_item
               FROM
                   ttiitm001$erp AS itm
               WHERE itm.t_suno = '$vendorid')
EOF;
                $query =<<<EOF
            (SELECT
     SUBSTRING(convert(varchar,t_pddt,120), 0, 8) AS pddt_month,
     ITM.t_dsca,
     MRP.t_item,
     SUM(MRP.t_oqan) AS count_item,
     (SELECT TOP 1 AIC.t_aitc FROM ttiitm012420 AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
 FROM
     ttimrp021420 AS MRP,
     ttiitm001420 AS ITM
 WHERE
         MRP.t_item=ITM.t_item and MRP.t_item in $con
   AND MRP.t_suno='10300'
 GROUP BY MRP.t_item, SUBSTRING(convert(varchar,t_pddt,120), 0, 8), t_dsca
)UNION
(            SELECT
                SUBSTRING(convert(varchar,t_pddt,120), 0, 8) AS pddt_month,
                ITM.t_dsca,
                MRP.t_item,
                SUM(MRP.t_oqan) AS count_item,
                (SELECT TOP 1 AIC.t_aitc FROM ttiitm012$erp AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
            FROM
                ttimrp021$erp AS MRP,
                ttiitm001$erp AS ITM
            WHERE
                MRP.t_item=ITM.t_item
                AND MRP.t_suno='$vendorid' 
            GROUP BY MRP.t_item, SUBSTRING(convert(varchar,t_pddt,120), 0, 8), t_dsca
            )
EOF;
                $opt1 = 'odbc';
                $opt2 = ['src' => 'baan'];
            break;
            case '300':
                $con = <<<EOF
                (SELECT
                   itm.t_item
               FROM
                   ttiitm001$erp AS itm
               WHERE itm.t_suno = '$vendorid')
EOF;
                $query =<<<EOF
            (SELECT
     SUBSTRING(convert(varchar,t_pddt,120), 0, 8) AS pddt_month,
     ITM.t_dsca,
     MRP.t_item,
     SUM(MRP.t_oqan) AS count_item,
     (SELECT TOP 1 AIC.t_aitc FROM ttiitm012420 AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
 FROM
     ttimrp021420 AS MRP,
     ttiitm001420 AS ITM
 WHERE
         MRP.t_item=ITM.t_item and MRP.t_item in $con
   AND MRP.t_suno='10476'
 GROUP BY MRP.t_item, SUBSTRING(convert(varchar,t_pddt,120), 0, 8), t_dsca
)UNION
(            SELECT
                SUBSTRING(convert(varchar,t_pddt,120), 0, 8) AS pddt_month,
                ITM.t_dsca,
                MRP.t_item,
                SUM(MRP.t_oqan) AS count_item,
                (SELECT TOP 1 AIC.t_aitc FROM ttiitm012$erp AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
            FROM
                ttimrp021$erp AS MRP,
                ttiitm001$erp AS ITM
            WHERE
                MRP.t_item=ITM.t_item
                AND MRP.t_suno='$vendorid' 
            GROUP BY MRP.t_item, SUBSTRING(convert(varchar,t_pddt,120), 0, 8), t_dsca
            )
EOF;
                $opt1 = 'odbc';
                $opt2 = ['src' => 'baan'];
            break;
            case '250':
                $con = <<<EOF
                (SELECT
                   itm.t_item
               FROM
                   ttiitm001$erp AS itm
               WHERE itm.t_suno = '$vendorid')
EOF;
                $query =<<<EOF
            (SELECT
     SUBSTRING(convert(varchar,t_pddt,120), 0, 8) AS pddt_month,
     ITM.t_dsca,
     MRP.t_item,
     SUM(MRP.t_oqan) AS count_item,
     (SELECT TOP 1 AIC.t_aitc FROM ttiitm012420 AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
 FROM
     ttimrp021420 AS MRP,
     ttiitm001420 AS ITM
 WHERE
         MRP.t_item=ITM.t_item and MRP.t_item in $con
   AND MRP.t_suno='12563'
 GROUP BY MRP.t_item, SUBSTRING(convert(varchar,t_pddt,120), 0, 8), t_dsca
)UNION
(            SELECT
                SUBSTRING(convert(varchar,t_pddt,120), 0, 8) AS pddt_month,
                ITM.t_dsca,
                MRP.t_item,
                SUM(MRP.t_oqan) AS count_item,
                (SELECT TOP 1 AIC.t_aitc FROM ttiitm012$erp AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
            FROM
                ttimrp021$erp AS MRP,
                ttiitm001$erp AS ITM
            WHERE
                MRP.t_item=ITM.t_item
                AND MRP.t_suno='$vendorid' 
            GROUP BY MRP.t_item, SUBSTRING(convert(varchar,t_pddt,120), 0, 8), t_dsca
            )
EOF;
                $opt1 = 'odbc';
                $opt2 = ['src' => 'baan'];
            break;
            case '220':
                $query = <<<EOF
            SELECT
                SUBSTRING(convert(varchar,t_pddt,120), 0, 8) AS pddt_month,
                t_dsca,
                MRP.t_item,
                SUM(MRP.t_oqan) AS count_item,
                (SELECT TOP 1 AIC.t_aitc FROM ttiitm012$erp AS AIC WHERE AIC.t_item=MRP.t_item GROUP BY AIC.t_aitc) AS vendor_pn
            FROM
                ttimrp021$erp AS MRP,
                ttiitm001$erp AS ITM
            WHERE
                MRP.t_item=ITM.t_item
                AND MRP.t_suno='$vendorid' 
            GROUP BY MRP.t_item, SUBSTRING(convert(varchar,t_pddt,120), 0, 8), t_dsca
            ORDER BY pddt_month
EOF;
                $opt1 = 'odbc';
                $opt2 = ['src' => 'baan'];
                break;
            default:
                return;
        }

        return tldUtils::getSqlToAssocArray($query, $opt1, $opt2);
    }

    public function countByVendorERPSubcontract($vendorid, $erp)
    {
        if (empty($vendorid) || empty($erp)) {
            return;
        }
        switch ($erp) {
            case '400':
            case '410':
            case '420':
            case '500':
            case '520':
            case '600':
            case '620':
            case '640':
            case '660':
            case '570':
                $query = <<<EOF
            SELECT
                SUBSTRING(convert(varchar,MRP2.t_pfdt,120), 0, 8) AS pddt_month,
                ITM.t_dsca,
                MRP2.t_item,
                SUM(MRP2.t_oqan) AS count_item,
                (SELECT TOP 1 AIC.t_aitc FROM ttiitm012$erp AS AIC WHERE AIC.t_item=MRP2.t_item GROUP BY AIC.t_aitc) AS vendor_pn
            FROM
                ttimrp020$erp AS MRP2,
                ttiitm001$erp AS ITM,
                ttirou102$erp AS ROU2,
                ttirou001$erp AS ROU1
            WHERE
                MRP2.t_item=ITM.t_item
                AND (ROU2.t_mitm=MRP2.t_item AND ROU2.t_opro=MRP2.t_opro)
                AND ROU1.t_cwoc = ROU2.t_cwoc
                AND ROU1.t_suno='$vendorid'
            GROUP BY MRP2.t_item, SUBSTRING(convert(varchar,t_pfdt,120), 0, 8), ITM.t_dsca
            ORDER BY pddt_month
EOF;
                $opt1 = 'odbc';
                $opt2 = ['src' => 'baan'];
                break;
            default:
                return;
        }

        return tldUtils::getSqlToAssocArray($query, $opt1, $opt2);
    }

    /**
     * Get MRP stats by constraints
     * @param int $erp
     * @param mixed array or string $a
     * @return row results
     */
    public static function countStatsByConstraints($erp, $a = NULL)
    {
        if (empty($erp)) {
            return;
        }
        $WHERE = '';
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = " WHERE $WHERE";
        }
        $query = <<<EOF
SELECT
	COUNT(CASE WHEN MRP.t_podt < GETDATE()
		THEN 'Y'
		ELSE null
		END
	) AS late,
	COUNT(CASE WHEN DATEDIFF(DAY, GETDATE(), MRP.t_podt) < 7
		THEN 'Y'
		ELSE NULL
		END
	) AS within_seven_days
FROM
    ttimrp021$erp AS MRP
$WHERE
EOF;
        return tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     * Get MRP stats by supplier number
     * @param int $erp
     * @param string $suno
     * @return row results
     */
    public static function countStatsBySuno($erp, $suno)
    {
        if (empty($suno)) {
            return;
        }
        $a = "MRP.t_suno='$suno'";
        return self::countStatsByConstraints($erp, $a);
    }

    /**
     * Get MRP stats by item
     * @param int $erp
     * @param string $item
     * @return row results
     */
    public static function countStatsByItem($erp, $item)
    {
        if (empty($item)) {
            return;
        }
        $a = "MRP.t_item='$item'";
        return self::countStatsByConstraints($erp, $a);
    }

    /**
     * Get MRP stats by buyer email
     * @param int $erp
     * @param string $email
     * @return row results
     */
    public static function countStatsByBuyerEmail($erp, $email)
    {
        if (empty($email)) {
            return;
        }
        $a = <<<EOF
(SELECT RTRIM(t6.t_info) FROM ttccom001$erp AS t6 WHERE t6.t_emno=MRP.t_buyr)='$email'
EOF;
        return self::countStatsByConstraints($erp, $a);
    }

    public static function PlannedMovement($erp, $item)
    {
        $query = <<<EOF
SELECT
		mrp010.t_item,
		SUBSTRING(convert(varchar,mrp010.t_date,120), 0, 11) AS t_date,
		mrp010.t_qana,
		case mrp010.t_kotr
WHEN '1' THEN '+'
WHEN '2' THEN '-'
WHEN '3' THEN '-'
		END t_tp,
		case mrp010.t_kotr
WHEN '1' THEN 'receipt'
WHEN '2' THEN 'need'
WHEN '3' THEN 'sls'
		END t_trs,
		case mrp010.t_koor
WHEN '1' THEN 'Production order'
WHEN '2' THEN 'Purchase order'
WHEN '3' THEN 'Sales order'
WHEN '4' THEN 'POF-MPS'
WHEN '5' THEN 'POA-MPS'
WHEN '6' THEN 'POF-MRP'
WHEN '7' THEN 'POA-MRP'
WHEN '8' THEN 'POF-PRP'
WHEN '9' THEN 'POA-PRP'
WHEN '10' THEN 'POM-PRP'
WHEN '11' THEN 'MRP sales forecast'
WHEN '12' THEN 'MPS rough material req.'
WHEN '13' THEN 'Sales quotation'
WHEN '14' THEN 'Purchase contract'
WHEN '15' THEN 'Sales contract'
WHEN '16' THEN 'Warehouse order'
WHEN '17' THEN 'Service order'
WHEN '18' THEN 'PRP purchase order'
WHEN '19' THEN 'PRP warehouse order'
WHEN '20' THEN 'ISM warehouse order'
WHEN '21' THEN 'Replenishment order'
WHEN '22' THEN 'DRP replenishment order'
WHEN '23' THEN 'DRP sales forecast'
WHEN '24' THEN 'ECO orders'
WHEN '25' THEN 'MPS interplant order'
WHEN '26' THEN 'Production batch'
WHEN '27' THEN 'Warehouse order'
WHEN '28' THEN 'MPS production batch'
WHEN '29' THEN 'MRP production batch'
END t_koor,
	case mrp010.t_koor
WHEN '1' THEN (SELECT sfc001.t_mitm FROM ttisfc001$erp AS sfc001 WHERE sfc001.t_pdno=mrp010.t_orno )
WHEN '2' THEN (SELECT po.t_suno FROM ttdpur040$erp AS po WHERE po.t_orno=mrp010.t_orno )
WHEN '3' THEN (SELECT so.t_cuno FROM ttdsls040$erp AS so WHERE so.t_orno=mrp010.t_orno )
WHEN '6' THEN (SELECT mrp.t_item FROM ttimrp020$erp AS mrp WHERE mrp.t_orno=mrp010.t_orno )
WHEN '8' THEN (SELECT pcs021.t_item from ttipcs021$erp AS pcs021 WHERE pcs021.t_cprj=mrp010.t_cprj and mrp010.t_item=pcs021.t_item )
ELSE ''
END AS t_alloc,
	case mrp010.t_koor
WHEN '1' THEN (SELECT TOP 1 sls040.t_refb FROM ttdsls041$erp AS sls041,ttdsls040$erp AS sls040 WHERE sls041.t_cprj=mrp010.t_cprj and sls040.t_orno=sls041.t_orno)
WHEN '3' THEN (SELECT TOP 1 sls040.t_refb FROM ttdsls041$erp AS sls041,ttdsls040$erp AS sls040 WHERE sls041.t_cprj=mrp010.t_cprj and sls040.t_orno=sls041.t_orno)
WHEN '8' THEN (SELECT TOP 1 sls040.t_refb FROM ttdsls041$erp AS sls041,ttdsls040$erp AS sls040 WHERE sls041.t_cprj=mrp010.t_cprj and sls040.t_orno=sls041.t_orno)
ELSE ''
END AS t_alloc1,
case mrp010.t_koor
		WHEN '1' THEN (select CONVERT(VARCHAR, stuff((select ',' + CAST(replace(t_text,'<','') AS varchar) from ttttxt010520 where t_ctxt = (SELECT min(t_txta) FROM ttisfc001$erp AS sfc001 WHERE sfc001.t_pdno=mrp010.t_orno) for xml path('')),1,1,'')))
		WHEN '2' THEN (select CONVERT(VARCHAR, stuff((select ',' + CAST(replace(t_text,'<','') AS varchar) from ttttxt010$erp where t_ctxt=(select min(t_txta) from ttdpur041$erp PUR041 where  PUR041.t_orno=mrp010.t_orno and PUR041.t_pono=mrp010.t_pono and PUR041.t_item=mrp010.t_item) for xml path('')),1,1,'')))
		END t_txta,
	case mrp010.t_koor
WHEN '1' THEN
(SELECT top 1 ltc001.t_clot from ttdltc001$erp AS ltc001 WHERE ltc001.t_cprj=mrp010.t_cprj and ltc001.t_cprj<>'' )
WHEN '8' THEN
(SELECT TOP 1 ltc001.t_clot from ttdltc001$erp AS ltc001 WHERE ltc001.t_cprj=mrp010.t_cprj and ltc001.t_cprj<>'' )
ELSE ''
END AS t_sn,
		mrp010.t_orno,
		mrp010.t_pono,
		mrp010.t_cprj,
		(SELECT itm001.t_stoc FROM ttiitm001$erp AS itm001 WHERE itm001.t_item=mrp010.t_item) AS t_stoc,
		(SELECT itm001.t_stmr FROM ttiitm001$erp AS itm001 WHERE itm001.t_item=mrp010.t_item) AS t_stmr
		FROM ttimrp010$erp AS mrp010
		WHERE mrp010.t_date <= dateadd(m, 18, GETDATE()) AND t_koor in (1, 2, 3, 6 , 7 ,8, 11) AND mrp010.t_item ='$item'
		ORDER BY t_date asc
EOF;

        $result = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);

        $invs = new tldBaanERP($erp);
        $stocks = $invs->getInvData($item, 'byItemOnly');
        if (count($stocks)) {
            $stock = $stocks[0]['stoc'];
        }
        foreach ($result as $val => $key) {
            if ($key['t_koor'] === 'Production order') {
                if (empty(trim($key['t_cprj'])) || empty(trim($key['t_alloc1']))) {
                    $item = trim($key['t_alloc']);
                    $query1 = <<<EOF
    				SELECT ITM.t_dsca
    				FROM ttiitm001$erp AS ITM
    				WHERE ITM.t_item='$item'
EOF;
                    $ret = tldUtils::getSqlRowToAssocArray($query1, 'odbc', ['src' => 'baan']);
                    if (is_array($ret)) {
                        foreach ($ret as $v) {
                            $result[$val]['t_alloc'] = $item . ' - ' . $v;
                        }
                    }
                } else {
                    $result[$val]['t_alloc'] = $key['t_alloc1'];
                }
            }
            if (!empty($key['t_cprj']) && $key['t_koor'] === 'POF-PRP') {
                $result[$val]['t_alloc'] = $key['t_alloc1'];
            }
            if ($key['t_koor'] === 'POF-MRP') {
                $item = trim($key['t_alloc']);
                $query1 = <<<EOF
				SELECT ITM.t_dsca
				FROM ttiitm001$erp AS ITM
				WHERE ITM.t_item='$item'
EOF;
                $ret = tldUtils::getSqlRowToAssocArray($query1, 'odbc', ['src' => 'baan']);
                if (is_array($ret)) {
                    foreach ($ret as $v) {
                        $result[$val]['t_alloc'] = $item . ' - ' . $v;
                    }
                }
            }
            if ($key['t_koor'] === 'Sales order') {
                if (empty(trim($key['t_cprj'])) || empty(trim($key['t_alloc1']))) {
                    $suno = trim($key['t_alloc']);
                    $orno = trim($key['t_orno']);
                    $query1 = <<<EOF
    				SELECT cus.t_nama ,T7.t_cuno
    				FROM ttdsls040$erp AS T7
    				LEFT JOIN ttccom010$erp AS cus ON T7.t_cuno=cus.t_cuno
    				WHERE T7.t_cuno LIKE '$suno' AND T7.t_orno='$orno'
EOF;

                    $ret = tldUtils::getSqlToAssocArray($query1, 'odbc', ['src' => 'baan']);
                    if (is_array($ret)) {
                        foreach ($ret as $v) {
                            $result[$val]['t_alloc'] = $v['t_cuno'] . '-' . $v['t_nama'];
                        }
                    }
                } else {
                    $result[$val]['t_alloc'] = $key['t_alloc1'];
                }
            }
            $result[$val]['blan'] = $stock;
            if ($val > 0) {
                if ($key['t_tp'] == '+') {
                    $result[$val]['blan'] = $result[$val - 1]['blan'] + $key['t_qana'];
                }
                if ($key['t_tp'] == '-') {
                    $result[$val]['blan'] = $result[$val - 1]['blan'] - $key['t_qana'];
                }
            } else {
                if ($key['t_tp'] == '+') {
                    $result[$val]['blan'] = $result[$val]['blan'] + $key['t_qana'];
                }
                if ($key['t_tp'] == '-') {
                    $result[$val]['blan'] = $result[$val]['blan'] - $key['t_qana'];
                }
            }
        }
        return $result;
    }

}

/**
 * Class for accessing and manipulating Web Sales Orders
 *
 * @package ERP
 */
class tldWSO
{
    public $itsID;
    public $itsERP;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsDetails = $this->getHeader();
    }

    public function isEmpty()
    {
        return empty($this->itsDetails);
    }

    /**
     * get web sales order header information
     *
     * @return array
     */
    public function getHeader()
    {
        if (empty($this->itsID)) {
            return;
        }
        $query = <<<EOF
SELECT * FROM erp_wso
WHERE id=$this->itsID;
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Create a new web sales order from header
     *
     *
     */
    public function insertHeader($a)
    {
        $query = <<<EOF
INSERT INTO erp_wso
SET
status='PENDING',
t_odat=NOW(),
erp={$a['erp']},
t_cdel={$a['t_cdel']},
t_cuno={$a['t_cuno']},
t_orno='{$a['t_orno']}'
EOF;
    }

    /**
     * get related lines for web sales order
     *
     * @return array
     */
    public function getLines()
    {
        if (empty($this->itsID)) {
            return;
        }
        $query = <<<EOF
	SELECT * FROM erp_wso_lines
	WHERE parent_id=$this->itsID;
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * insert a line to current web sales order
     *
     * @return mixed
     */
    public function insertPart($a)
    {
        $query = <<<EOF
	INSERT INTO erp_wso_lines
	SET
	ITEM='{$a["ITEM"]}',
	DESCRIPTION='{$a["DESCRIPTION"]}',
	manid={$a['manid']},
	docid={$a['docid']},
	t_oqua={$a['t_oqua']}
EOF;
    }

    /**
     * get an array representation of the web sales order
     *
     * @return array
     */
    public function asArray()
    {
        $result['header'] = $this->getHeader();
        $result['lines'] = $this->getParts();
        return $result;
    }

    /**
     * Create a WSO from an array structure, opposite to asArray()
     *
     * @return bool True is insert successful
     */
    public function insertArray($a)
    {

    }
}

/**
 * Class for accessing Invoices in ERP systems
 *
 * @package ERP
 */
class tldINV
{
    public $itsType; //the transaction type
    public $itsID; //Invoice num
    public $itsERP; //erp system id

    public function __construct($id, $erp, $option = '')
    {
        $this->itsERP = $erp;
        if (!empty($option['so']) || !empty($option['type'])) {
            $this->itsID = $id;
            //get type using so#
            if (isset($option['so'])) {
                $this->itsType = $this->getTypeByERP_SO($id, $erp, $option['so']);
            } elseif (isset($option['type'])) {
                $this->itsType = $option['type'];
            }
        } else {
            //here we assume the id is the type plus the number
            //eg CLS12123123 so we split the string
            $this->itsType = substr($id, 0, 3);
            $this->itsID = substr($id, 3);
        }
        $this->itsDetails = $this->getHeader();
    }

    public function getType()
    {
        return $this->itsType;
    }

    public function getID()
    {
        return $this->itsID;
    }

    public function getERP()
    {
        return $this->itsERP;
    }

    public static function getTFERP($erp)
    {
        if (in_array($erp, [500, 520, 540])) {
            return 540;
        }

        return $erp;
    }

    /**
     * get header info of Invoice
     *
     * @return array
     */
    public function getHeader()
    {
        if (empty($this->itsID)) {
            return;
        }
        $tferp = tldINV::getTFERP($this->itsERP);

        switch ($this->itsERP) {
            default:
                $query = <<<EOF
SELECT *,
    SUBSTRING(convert(varchar, t_docd, 120), 0, 11) AS t_docd,
    SUBSTRING(convert(varchar, t_dued, 120), 0, 11) AS t_dued,
    RTRIM(t_cuno) AS t_cuno,
    RTRIM(t_ninv) AS t_ninv,
    CAST(t_amnt AS money) AS t_amnt,
    CAST(t_balc AS money) AS t_balc,
    'status' = CASE WHEN t_balc>0
        THEN 'UNPAID'
        ELSE 'PAID'
    END,
    'days_late' = CASE WHEN t_balc>0 AND t_dued<GETDATE()
    	THEN DATEDIFF(DAY, t_dued, GETDATE())
    	ELSE ''
    END
FROM ttfacr200$tferp
WHERE t_ttyp='$this->itsType'
    AND t_ninv='$this->itsID'
    AND t_tdoc=''
EOF;
                $opt1 = 'odbc';
                $opt2 = ['src' => 'baan'];
        }
        return tldUtils::getSqlRowToAssocArray($query, 'odbc', $opt2);
    }

    /**
     * Get outstanding balance for this invoice
     *
     * @return double
     */

    public function getBalance()
    {
        return $this->itsHeader['t_balc'];
    }

    /**
     * get detail lines of INV
     *
     * @return array return array of invoice lines, empty array if none
     */
    public function getDetail()
    {
        if (empty($this->itsID)) {
            return;
        }
        $erp = $this->itsERP;
        $odat = $this->itsDetails['t_odat'];
        switch ($erp) {
            default:
                $query = <<<EOF
SELECT
    T1.t_orno, T1.t_pono, T1.t_cuno, T1.t_item, ITM.t_dsca,ITM.t_cuqs,
    SUBSTRING(convert(varchar, T1.t_ddat, 120), 0, 11) AS t_ddat,
    T1.t_oqua, T1.t_dqua, T1.t_bqua, T1.t_recq, T1.t_dino, T1.t_invd, T1.t_ttyp,
    T1.t_invn, T1.t_ssls
FROM ttdsls045$erp AS T1
    LEFT JOIN ttiitm001$erp AS ITM ON T1.t_item=ITM.t_item
WHERE
    T1.t_ttyp='$this->itsType' AND T1.t_invn='$this->itsID'
ORDER BY T1.t_orno, T1.t_pono
EOF;

                $opt2 = ['src' => 'baan'];
        }
        return tldUtils::getSqlToAssocArray($query, 'odbc', $opt2);
    }

    /**
     * get customer id number
     *
     * @return integer
     */
    public function getCUNO()
    {
        return $this->itsDetails['t_cuno'];
    }

    /**
     * get invoice balance
     *
     * @return integer
     */
    public function getBal()
    {
        return $this->itsDetails['t_balc'];
    }

    /**
     * Gets invoices by constraints
     *
     * @param int $erp company in baan
     * @param array or string $a constraint
     * @param array $options
     *        --> use option['returnAll'] to get all invoices, by default 50
     * @return array
     */
    public static function byConstraints($erp, $a, $options = '')
    {
        if (empty($erp) || empty($a)) {
            return 'Empty parameter';
        }
        $WHERE = is_array($a) ? tldUtils::constructWhere($a) : $a;

        $SELECT = '';
        if (!$options['returnAll']) {
            $SELECT = 'TOP 50';
        }
        $tferp = self::getTFERP($erp);
        $query = <<<EOF
SELECT
    $SELECT
	inv.*,
	inv.t_ccur,
    inv.t_ttyp + CAST(inv.t_ninv AS CHAR) AS ninv_fullname,
    SUBSTRING(convert(varchar, inv.t_docd, 120), 0, 11) AS t_docd,
    SUBSTRING(convert(varchar, inv.t_dued, 120), 0, 11) AS t_dued,
    RTRIM(inv.t_cuno) AS t_cuno,
    RTRIM(inv.t_ninv) AS t_ninv,
    CAST(inv.t_amnt AS money) AS t_amnt,
    CAST(inv.t_balc AS money) AS t_balc,
    'status' = CASE WHEN inv.t_balc>0
        THEN 'UNPAID'
        ELSE 'PAID'
    END,
    'days_late' = CASE WHEN inv.t_balc>0 AND inv.t_dued<GETDATE()
    	THEN DATEDIFF(DAY, inv.t_dued, GETDATE())
    	ELSE ''
    END,
    so.t_eono,
    so.t_cdel,
    so.t_refa,
    (SELECT TOP 1 t_crte FROM ttccom013$erp AS com WHERE com.t_cuno=so.t_cuno AND com.t_cdel=so.t_cdel) AS t_crte
FROM ttfacr200$tferp AS inv
	JOIN ttdsls040$erp AS so ON so.t_orno=inv.t_orno
WHERE
    inv.t_tdoc=''
    AND $WHERE
ORDER BY
	inv.t_ninv,inv.t_dued,inv.t_cuno
EOF;

        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     * Get invoice by order number
     * @param int $erp
     * @param int $orno
     * @param array $options
     * @return array
     */
    public function byORNO($erp, $orno, $options = '')
    {
        $WHERE = "t_orno=$orno";
        return self::byConstraints($erp, $WHERE, $options);
    }

    /**
     * Get OPEN invoice by constraints
     * @param int $erp
     * @param array or string $a
     * @param array $options
     * @return array
     */
    public static function byOpenByConstraints($erp, $a, $options = '')
    {
        $WHERE = 'inv.t_balc > 0.1';
        if (is_array($a)) {
            $WHERE .= ' AND ' . tldUtils::constructWhere($a);
        } else {
            $WHERE .= " AND $a";
        }
        return self::byConstraints($erp, $WHERE, $options);
    }

    /**
     * Get OPEN invoice by customer number
     * @param int $erp
     * @param string $cuno
     * @param array|string $options
     * @return array
     */
    public static function byOpenByCuno($erp, $cuno, $options = '')
    {
        $WHERE = "inv.t_cuno='$cuno'";
        return self::byOpenByConstraints($erp, $WHERE, $options);
    }

    /**
     * Search invoice on inv# or refR field
     * @param string $id
     * @param int $erp
     * @param array $options
     * @return array
     */
    public static function search($id, $erp, $options = '')
    {
        $tferp = self::getTFERP($erp);
        switch ($erp) {
            default:
                $query = <<<EOF
SELECT
    *,
    SUBSTRING(convert(varchar, t_docd, 120), 0, 11) AS t_docd,
    SUBSTRING(convert(varchar, t_dued, 120), 0, 11) AS t_dued,
    RTRIM(t_cuno) AS t_cuno,
    RTRIM(t_ninv) AS t_ninv
FROM ttfacr200$tferp
WHERE (t_ninv='$id' OR t_refr like '%$id%')
    AND t_tdoc=''
    AND t_orno<>'' AND t_orno<>0
EOF;
                $opt1 = 'odbc';
                $opt2 = ['src' => 'baan'];
        }
        if (isset($options['where'])) {
            $query .= ' AND ' . tldUtils::constructWhere($options['where']);
        }
        return tldUtils::getSqlToAssocArray($query, 'odbc', $opt2);
    }

    /**
     * get transaction type using only so# and inv#
     *
     * @return string
     */
    public function getTypeByERP_SO($inv, $erp, $so)
    {
        $tferp = self::getTFERP($erp);
        switch ($erp) {
            default:
                $query = <<<EOF
SELECT *
FROM ttfacr200$tferp
WHERE t_ninv='$inv'
AND t_orno='$so'
AND t_tdoc=''
EOF;
                $opt2 = ['src' => 'baan'];
        }
        $row = tldUtils::getSqlRowToAssocArray($query, 'odbc', $opt2);

        return $row['t_ttyp'];
    }

    /**
     * Get list of Transaction type code for UNITS orders
     * @param int $erp
     * @return array
     */
    public static function getUnitsTransactionTypeByERP($erp)
    {
        switch ($erp) {
            case '250':
                return ['SLU'];
                break;
            case '300':
                return ['SLU'];
                break;
            case '540':
                return ['CLU'];
                break;
            case '560':
                return ['CLY'];
                break;
            case '600':
                return ['HIA'];
                break;
            case '680':
                return ['SHS'];
                break;
            default:
                return [];
                break;
        }
    }

    /**
     * Get list of Transaction type code for PARTS orders
     * @param int $erp
     * @return array
     */
    public static function getPartsTransactionTypeByERP($erp)
    {
        switch ($erp) {
            case '250':
                return ['SLS'];
                break;
            case '300':
                return ['CAC', 'CAS', 'SCC', 'SLS'];
                break;
            case '540':
                return ['CLS', 'CLG'];
                break;
            case '600':
                return ['HIB'];
                break;
            case '680':
                return ['SSP', 'SPD'];
                break;
            default:
                return [];
                break;
        }
    }

    /**
     * Get list of Transaction type code for REFITS orders
     * @param int $erp
     * @return array
     */
    public static function getRefitsTransactionTypeByERP($erp)
    {
        switch ($erp) {
            case '540':
                return ['CLA'];
                break;
            case '560':
                return ['CLR'];
                break;
            case '600':
                return ['HIF'];
                break;
            default:
                return [];
                break;
        }
    }

    /**
     * Get Invoice statistics by constraints
     * @param int $erp
     * @param array or string $a (optional)
     * @return row of data
     */
    public static function getStatsByConstraints($erp, $a = '')
    {
        // Construct constraints
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "WHERE $WHERE";
        }
        // Transaction type constraints
        $rows = self::getPartsTransactionTypeByERP($erp);
        if (!empty($rows)) {
            $parts_constraints = "inv.t_ttyp IN('" . implode("','", $rows) . "')";
        } else {
            $parts_constraints = '1=0';
        }
        $rows = self::getUnitsTransactionTypeByERP($erp);
        if (!empty($rows)) {
            $units_constraints = "inv.t_ttyp IN('" . implode("','", $rows) . "')";
        } else {
            $units_constraints = '1=0';
        }
        $rows = self::getRefitsTransactionTypeByERP($erp);
        if (!empty($rows)) {
            $refit_constraints = "inv.t_ttyp IN('" . implode("','", $rows) . "')";
        } else {
            $refit_constraints = '1=0';
        }
        // Construct query
        $query = <<<EOF
        SELECT
            inv.t_ccur,
            CONVERT(
              VARCHAR(100),
              CAST(
                SUM(
                  CASE WHEN $parts_constraints
                    THEN inv.t_balc
                    ELSE 0
                  END
                ) AS DECIMAL(15,2))
            ) AS total_parts_balance,
            CONVERT(
              VARCHAR(100),
              CAST(
                SUM(
                  CASE WHEN $units_constraints
                    THEN inv.t_balc
                    ELSE 0
                  END
                ) AS DECIMAL(15,2))
            ) AS total_units_balance,
            CONVERT(
              VARCHAR(100),
              CAST(
                SUM(
                  CASE WHEN $refit_constraints
                    THEN inv.t_balc
                    ELSE 0
                  END
                ) AS DECIMAL(15,2))
            ) AS total_refits_balance,
            CONVERT(
              VARCHAR(100),
              CAST(SUM(inv.t_balc) AS DECIMAL(15,2))
            ) AS total_balance
        FROM ttfacr200$erp AS inv
        $WHERE
        GROUP BY inv.t_ccur
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     * @param array $constraints
     *
     * @return array
     */
    public static function getAeroAvailableItemsReport(array $constraints = [])
    {
        // Allow to associate POs and WOs to the report
        $fieldPO = $constraints['po_field'] ? 'PUR1.t_orno AS PO#,' : '';
        $fieldWO = $constraints['wo_field'] ? 'PROD.t_pdno AS WO#,' : '';
        $joinPO = $constraints['po_field'] ? 'LEFT JOIN ttdpur041250 AS PUR1 ON PUR1.t_item=ITM.t_item' : '';
        $joinWO = $constraints['wo_field'] ? 'LEFT JOIN ttisfc001250 AS PROD ON PROD.t_mitm=ITM.t_item' : '';
        $where = $constraints['wo_field'] ? 'WHERE PROD.t_osta IN (4,5) ' : '';

        foreach ($constraints as $field => $value) {
            if (!empty($value)) {
                if ('item#' === $field) {
                    $where .= !empty($where) ? "AND ITM.t_item like '%$value%' " : "WHERE ITM.t_item like '%$value%' ";
                }
                if ('item_type' === $field) {
                    $where .= !empty($where) ? "AND ITM.t_kitm like '%$value%' " : "WHERE ITM.t_kitm like '%$value%' ";
                }
                if ('signal_code' === $field) {
                    $where .= !empty($where) ? "AND ITM.t_csig like '%$value%' " : "WHERE ITM.t_csig like '%$value%' ";
                }
                if ('zero_cost' === $field && false !== $value) {
                    $where .= !empty($where) ? "AND ITM.t_copr='' " : "WHERE ITM.t_copr='' ";
                }
            }
        }

        $query = <<<SQL
SELECT
$fieldPO
$fieldWO
ITM.t_item AS item#,
ITM.t_kitm AS item_type,
ITM.t_dsca AS item_desc,
ITM.t_sfst AS safety_stock,
ITM.t_stoc AS actual_qty,
ITM.t_allo AS allocated_qty,
(ITM.t_stoc - ITM.t_allo) AS inv_qty,
ITM.t_ordr AS on_order,
ITM.t_copr AS std_cost,
ITM.t_matc AS mat_cost,
ITM.t_oprc AS op_cost,
ITM.t_csig AS signal_code
FROM ttiitm001250 AS ITM
$joinPO
$joinWO
$where
ORDER BY
ITM.t_item
SQL;

        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

}

/**
 * Class for accessing Sales Quotation in ERP systems
 *
 * @package ERP
 */
class tldSQ
{
    public $itsID; //quote num
    public $itsERP; //erp system id

    public function __construct($id, $erp)
    {
        $this->itsID = $id;
        $this->itsERP = $erp;
        $this->itsHeader = $this->getHeader();
    }

    /**
     * get header info of SO
     *
     * @return array
     */
    public function getHeader()
    {
        if (empty($this->itsID)) {
            return;
        }
        $id = $this->itsID;
        $erp = $this->itsERP;

        switch ($erp) {
            default:
                $query = <<<EOF
SELECT
    RTRIM(t_qono) AS t_qono,
    RTRIM(t_cuno) AS t_cuno,
    SUBSTRING(convert(varchar, t_qdat, 120), 0, 11) AS t_qdat,
    t_refa,
    t_refb,
    t_cdel
FROM dbo.ttdsls001$erp
WHERE t_qono='$id'
EOF;
                $opt2 = ['src' => 'baan'];
        }
        return tldUtils::getSqlRowToAssocArray($query, 'odbc', $opt2);
    }

    /**
     * Get quote per customer
     *
     * @return array
     */
    public static function byCustomerERP($cuno, $erp, $options = '')
    {
        $SELECT = '';
        if (is_array($options) && $options['getAll'] == false) {
            $SELECT = ' TOP 200 ';
        }
        switch ($erp) {
            default:
                $query = <<<EOF
SELECT $SELECT
    *,
    RTRIM(t_qono) AS t_qono,
    RTRIM(t_cuno) AS t_cuno,
    SUBSTRING(convert(varchar, sls.t_qdat, 120), 0, 11) AS t_qdat,
    t_refa,
    t_refb,
    t_cdel
FROM dbo.ttdsls001$erp AS sls
WHERE t_cuno='$cuno'
ORDER BY t_qdat DESC
EOF;
                $opt2 = ['src' => 'baan'];
        }
        return tldUtils::getSqlToAssocArray($query, 'odbc', $opt2);
    }
}

/**
 * Class for accessing Sales Order in ERP systems
 *
 * @package ERP
 */
class tldSO
{

    public $itsID; //po num
    public $itsERP; //erp system id

    public function __construct($id, $erp)
    {
        $this->itsID = $id;
        $this->itsERP = $erp;
        $this->itsDetails = $this->getHeader();
    }

    public function getCUNO()
    {
        return $this->itsDetails['t_cuno'];
    }

    public function getCDEL()
    {
        return $this->itsDetails['t_cdel'];
    }

    public function getREFA()
    {
        return $this->itsDetails['t_refa'];
    }

    /**
     * Get header info of SO
     * @return array
     */
    public function getHeader()
    {
        if (empty($this->itsID)) {
            return;
        }
        $id = $this->itsID;
        $erp = $this->itsERP;

        switch ($erp) {
            default:
                $query = <<<EOF
SELECT
    RTRIM(SLS.t_orno) AS t_orno,
    RTRIM(SLS.t_cuno) AS t_cuno,
    RTRIM(SLS.t_eono) AS t_eono,
    SUBSTRING(convert(varchar, SLS.t_odat, 120), 0, 11) AS t_odat,
    SLS.t_refa,
    SLS.t_refb,
    SLS.t_cdel,
    SLS.t_ccur,
    SLS.t_cfrw,
    SLS.t_cpay,
    SLS.t_cdec,
    SLS2.t_invn,
    CASE SUBSTRING(convert(varchar, SLS2.t_invd, 120), 0, 11)
           WHEN '1753-01-01' THEN ''
           ELSE SUBSTRING(convert(varchar, SLS2.t_invd, 120), 0, 11)
    END t_invd
FROM dbo.ttdsls040$erp AS SLS
LEFT JOIN ttdsls045$erp AS SLS2 ON SLS.t_orno = SLS2.t_orno
WHERE SLS.t_orno='$id'

EOF;
                $opt2 = ['src' => 'baan'];
        }
        return tldUtils::getSqlRowToAssocArray($query, 'odbc', $opt2);
    }

    /**
     * Get delivery address
     *
     * @return array
     */
    public function getDeliveryAddress()
    {
        $erp = tldERP::getERPOb($this->itsERP);
        return $erp->getDeliveryAddressData(
            $this->getCUNO(),
            [
                'm' => 'byCDEL',
                't_cdel' => $this->getCDEL(),
            ]
        );
    }

    /**
     * get detail lines of SO
     *
     * @return array return array of sales order lines, empty array if none
     */
    public function getDetail()
    {
        if (empty($this->itsID)) {
            return;
        }
        $id = $this->itsID;
        $erp = $this->itsERP;
        $odat = $this->itsDetails['t_odat'];
        switch ($erp) {
            default:
                $query = <<<EOF
            SELECT T1.t_cuno, T1.t_orno, T1.t_pono, T1.t_srnb, T1.t_item, ITM.t_dsca,ITM.t_wght,ITM.t_dscc,
            (SELECT mcs010.t_dsca FROM ttcmcs010$erp AS mcs010 WHERE mcs010.t_ccty=ITM.t_ctyo) AS t_ctyo,
            ITM.t_ccde,ITM.t_stoc,
            SUBSTRING(convert(varchar, T1.t_ddat, 120), 0, 11) AS t_ddat,
            ITM.t_cuqs, T1.t_oqua, T1.t_dqua, T1.t_bqua, T1.t_recq, T1.t_dino, T1.t_invd, T1.t_ttyp,
            T1.t_invn, T1.t_ssls, CONVERT(char(10), T2.t_ddta, 120) AS t_ddta, T2.t_pric,T2.t_pric*T1.t_oqua AS t_amnt,
            QUO.t_qono
            FROM ttdsls045$erp AS T1
            	LEFT JOIN ttdsls041$erp AS T2 ON T1.t_orno=T2.t_orno  AND T1.t_pono=T2.t_pono
                LEFT JOIN ttiitm001$erp AS ITM ON T1.t_item=ITM.t_item
                LEFT JOIN ttdsls002$erp AS QUO ON T1.t_pono=QUO.t_spon AND T1.t_orno=QUO.t_sorn
            WHERE T1.t_orno='$id'
    ORDER BY T1.t_orno, T1.t_pono, T1.t_srnb
EOF;
                $opt2 = ['src' => 'baan'];
        }
        return tldUtils::getSqlToAssocArray($query, 'odbc', $opt2);
    }

    /**
     * Get open sales orders per customer where ssls status is <7
     *
     * @return array
     */
    public static function byCustomerERP($cuno, $erp, $options = '')
    {
        $WHERE = '';
        if (empty($options['getAll'])) {
            $WHERE = ' AND T2.t_ssls<7';
        }
        switch ($erp) {
            default:
                $query = <<<EOF
				SELECT DISTINCT(T1.t_orno),
				SUBSTRING(convert(varchar, T1.t_odat, 120), 0, 11) AS t_odat,
				T1.t_refa, T1.t_refb,
                $erp AS erp
				FROM ttdsls040$erp AS T1, ttiitm001$erp AS ITM, ttdsls045$erp AS T2
				WHERE T1.t_orno=T2.t_orno AND T2.t_item=ITM.t_item
				AND T1.t_cuno='$cuno'
                $WHERE
			GROUP BY T1.t_orno, T1.t_odat, T1.t_refa, T1.t_refb
			HAVING SUM(T2.t_pric)>0
EOF;
                $opt2 = ['src' => 'baan'];
        }
        return tldUtils::getSqlToAssocArray($query, 'odbc', $opt2);
    }

    /**
     * Get sales order type list by company#
     * @param int $erp
     * @return array
     */
    public static function getTypeListByERP($erp)
    {
        if (empty($erp) || !is_int($erp)) {
            return;
        }
        $query = <<<EOF
SELECT t1.t_cotp
FROM ttcmcs042$erp AS t1
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     * Search SOs
     *
     * @return array array of db rows
     */
    public static function search($id, $erp, $options = '')
    {
        switch ($erp) {
            default:
                $query = <<<EOF
            SELECT RTRIM(sors.t_orno) AS t_orno,
            RTRIM(sors.t_cuno) AS t_cuno,
            SUBSTRING(convert(varchar, sors.t_odat, 120), 0, 11) AS t_odat,
            sors.t_refa AS t_refa,
            sors.t_refb AS t_refb
            FROM dbo.ttdsls040$erp AS sors
            LEFT JOIN dbo.ttdsls041$erp AS sols on sors.t_orno=sols.t_orno
            LEFT JOIN dbo.ttdsls045$erp AS sods on sols.t_orno=sods.t_orno AND sols.t_pono=sods.t_pono
            WHERE (sors.t_orno LIKE '$id' OR sors.t_refa like '$id%' OR sors.t_refb like '$id%' OR sods.t_item like '$id%')
EOF;
                $opt1 = 'odbc';
                $opt2 = ['src' => 'baan'];
        }
        if (isset($options['where'])) {
            $query .= ' AND ' . tldUtils::constructWhere($options['where']);
        }

        return tldUtils::getSqlToAssocArray($query, 'odbc', $opt2);
    }

    /**
     * Convert an arrayPO to arraySO
     *
     * @param mixed $po
     */
    public function fromPO($po)
    {

    }

    public static function byCUNO_OPENPO($erp, $id)
    {
        $a = ['sors.t_cuno' => $id];
        return self::byOpenByConstraints($erp, $a);
    }

    public static function byOpenByConstraints($erp, $a, $opt = '')
    {
        $WHERE = 'sods.t_ssls<>7';
        if (is_array($a)) {
            $WHERE .= ' AND ' . tldUtils::constructWhere($a);
        } else {
            $WHERE .= " AND $a";
        }
        return self::byConstraints($erp, $WHERE, $opt);
    }

    public static function byConstraints($erp, $a, $opt = [])
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        // Look for options ----->
        // By default do not include UNITS
        if (empty($opt['exclude'])) {
            $typeUnits = self::getUnitsOrderTypeByERP($erp);
            $WHERE .= " AND sors.t_cotp NOT IN('" . implode("','", $typeUnits) . "')";
        }
        if (!empty($opt['orderby'])) {
            $ORDERBY = $opt['orderby'];
        } else {
            $ORDERBY = 'sors.t_odat';
        }
        // Construct query
        $query = <<<EOF
SELECT
    sors.t_orno,
    sors.t_refa,
    sors.t_refb,
    sors.t_cuno,
    sors.t_crep,
    sors.t_eono,
    CASE
        WHEN sors.t_odat='1753-01-01 00:00:00.000' THEN ''
        ELSE SUBSTRING(convert(varchar, sors.t_odat, 120), 0, 11)
    END AS t_odat,
    cus.t_nama,
    cus.t_cuno AS customer,
    reps.t_nama AS buyer,
    reps.t_info,
    sods.t_pono,
    sods.t_item,
    sods.t_oqua,
    sods.t_dqua,
    sods.t_bqua,
    CASE
        WHEN sols.t_ddta='1753-01-01 00:00:00.000' THEN ''
        ELSE SUBSTRING(convert(varchar, sols.t_ddta, 120), 0, 11)
    END AS t_ddta,
    sods.t_dino,
    sods.t_ssls,
    sods.t_ttyp + CAST(sods.t_invn AS char) as t_invn,
    itms.t_dsca,
    itms.t_ordr,
    itms.t_stoc,
    itms.t_allo,
    itms.t_csig AS t_csig,
    itms.t_pics,
    itms.t_buyr,
    purs.t_orno AS purs_orno,
    purs.t_pono AS purs_pono,
    purs.t_suno AS purs_suno,
    purs.t_oqua AS purs_oqua,
    purs.t_dqua AS purs_dqua,
    purs.t_bqua AS purs_bqua,
    (select COM020.t_nama from ttccom020$erp COM020 where COM020.t_suno=itms.t_suno) AS sup_name,
    SUBSTRING(convert(varchar, purs.t_ddtb, 120), 0, 11) AS purs_ddtb
FROM
    ttdsls040$erp as sors
    LEFT JOIN ttdsls041$erp as sols on sors.t_orno=sols.t_orno
    LEFT JOIN ttdsls045$erp as sods on sols.t_orno=sods.t_orno
        AND sols.t_pono=sods.t_pono
    LEFT JOIN ttiitm001$erp AS itms ON sods.t_item=itms.t_item
    LEFT JOIN ttccom010$erp AS cus ON sors.t_cuno=cus.t_cuno
    LEFT JOIN ttccom001$erp AS reps on sors.t_crep=reps.t_emno  
    LEFT JOIN ttdpur041$erp AS purs ON sods.t_item=purs.t_item
        AND (
            (purs.t_oqua<>purs.t_dqua AND purs.t_bqua>0)
            OR (purs.t_oqua<>purs.t_dqua AND purs.t_bqua=0)
        ) 
WHERE
    $WHERE
ORDER BY
    $ORDERBY
EOF;

        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public static function getPackingSlipByERPBySO($erp, $a, $opt = '')
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }

        // Construct query
        $query = <<<EOF
SELECT
    distinct sods.t_dino
FROM
    ttdsls040$erp as sors
    LEFT JOIN ttdsls041$erp as sols on sors.t_orno=sols.t_orno
    LEFT JOIN ttdsls045$erp as sods on sols.t_orno=sods.t_orno
        AND sols.t_pono=sods.t_pono
WHERE
    $WHERE
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public function byCUNO_EONO($erp, $cuno, $eono)
    {
        $query = <<<EOF
SELECT
    RTRIM(t_orno) AS t_orno,
    RTRIM(t_cuno) AS t_cuno,
    SUBSTRING(convert(varchar, t_odat, 120), 0, 11) AS t_odat,
    t_refa,
    t_refb,
    t_cdel
FROM
	ttdsls040$erp
WHERE
	t_cuno='$cuno'
	AND t_eono='$eono'
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     * Get list of order type code for UNITS orders
     * @param int $erp
     * @return array
     */
    public static function getUnitsOrderTypeByERP($erp)
    {
        switch ($erp) {
            case '250':
                return ['SN2'];
                break;
            case '300':
                return ['SU1'];
                break;
            case '540':
                return ['SN1'];
                break;
            case '560':
                return ['SN1'];
                break;
            case '600':
                return ['SU1'];
                break;
            case '680':
                return ['SN7'];
                break;
            default:
                return [];
                break;
        }
    }

    /**
     * Get list of order type code for PARTS orders
     * @param int $erp
     * @return array
     */
    public static function getPartsOrderTypeByERP($erp)
    {
        switch ($erp) {
            case '250':
                return ['SN1'];
                break;
            case '300':
                return ['CAC', 'CAS', 'SC1', 'SN1', 'SN2', 'SN3'];
                break;
            case '540':
                return [
                    'SC0', 'SC1', 'SC2', 'SC3', 'SC4', 'SC5', 'SC6', 'SC7', 'SC8', 'SC9',
                    'SN2', 'SN3', 'C01', 'C02', 'C03', 'C04', 'C05', 'C06', 'C07', 'C08', 'C09', 'C10',
                ];
                break;
            case '600':
                return ['SN1'];
                break;
            case '680':
                return ['SN3', 'S10'];
                break;
            default:
                return [];
                break;
        }
    }

    /**
     * Get list of order type code for REFITS orders
     * @param int $erp
     * @return array
     */
    public static function getRefitsOrderTypeByERP($erp)
    {
        switch ($erp) {
            case '300':
                return [];
                break;
            case '540':
                return ['RS1', 'SN4'];
                break;
            case '560':
                return ['RS1'];
                break;
            case '600':
                return ['SV1'];
                break;
            case '680':
                return [];
                break;
            default:
                return [];
                break;
        }
    }

    /**
     * Get Order statistics by constraints
     * @param int $erp
     * @param array or string $a (optional)
     * @return array row of data
     */
    public static function getStatsByConstraints($erp, $a = '')
    {
        $WHERE = is_array($a)  ? tldUtils::constructWhere($a) : $a;
        $rows = self::getPartsOrderTypeByERP($erp);
        if (!empty($rows)) {
            $parts_constraints = "sors.t_cotp IN('" . implode("','", $rows) . "')";
        } else {
            $parts_constraints = '1=0';
        }
        $rows = self::getUnitsOrderTypeByERP($erp);
        if (!empty($rows)) {
            $units_constraints = "sors.t_cotp IN('" . implode("','", $rows) . "')";
        } else {
            $units_constraints = '1=0';
        }
        $rows = self::getRefitsOrderTypeByERP($erp);
        if (!empty($rows)) {
            $refit_constraints = "sors.t_cotp IN('" . implode("','", $rows) . "')";
        } else {
            $refit_constraints = '1=0';
        }
        // Construct query
        $query = <<<EOF
        SELECT
            sors.t_ccur,
            CONVERT(
              VARCHAR(100),
              CAST(
                SUM(
                  CASE WHEN $parts_constraints
                    THEN (sols.t_amta/sols.t_oqua)*(sods.t_oqua-sods.t_dqua)
                    ELSE 0
                  END
                ) AS DECIMAL(15,2))
            ) AS total_parts_balance,
            CONVERT(
              VARCHAR(100),
              CAST(
                SUM(
                  CASE WHEN $units_constraints
                    THEN (sols.t_amta/sols.t_oqua)*(sods.t_oqua-sods.t_dqua)
                    ELSE 0
                  END
                ) AS DECIMAL(15,2))
            ) AS total_units_balance,
            CONVERT(
              VARCHAR(100),
              CAST(
                SUM(
                  CASE WHEN $refit_constraints
                    THEN (sols.t_amta/sols.t_oqua)*(sods.t_oqua-sods.t_dqua)
                    ELSE 0
                  END
                ) AS DECIMAL(15,2))
            ) AS total_refits_balance,
            CONVERT(
              VARCHAR(100),
              CAST(
                SUM((sols.t_amta/sols.t_oqua)*(sods.t_oqua-sods.t_dqua)
              ) AS DECIMAL(15,2))
            ) AS total_balance
        FROM
            ttdsls040$erp AS sors
            LEFT JOIN ttdsls041$erp AS sols ON sors.t_orno=sols.t_orno
            LEFT JOIN ttdsls045$erp AS sods ON sods.t_orno=sols.t_orno
                AND sods.t_pono=sols.t_pono
        WHERE
            $WHERE
        GROUP BY
            sors.t_ccur
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     *
     */
    static function insertPackingSlip()
    {
        //get all lines updated in ttdtld999540 table (custom)
        $query = "SELECT * FROM ttdtld999540 WHERE  t_stat='Update' and t_date >  dateadd(day,-7 ,getdate()) ";
        $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
        //force erp
        foreach ($rows as $key => $val) {
            //check if the orno number exists in the table warranty_tracking in tld database and
            $erp = $val['t_comp'];

            if (tldDINO::isPackingSlipSet($erp, $val['t_orno'])) {
                //get the tracking in erp_dino_trno table in tld database
                // $trackingno = CONCAT(courier,'@',trno)
                $trackingno = tldWC::getTracking($val['t_comp'], $val['t_dino']);
                //update TLD database
                $query = <<<SQL
 				UPDATE warranty_tracking 
 				SET packing_slip={$val['t_dino']}, tracking_no='$trackingno' 
 				WHERE so_no={$val['t_orno']} 
 				AND erp=$erp 
 				ORDER BY date DESC LIMIT 1;
SQL;
                tldUtils::sqlQuery($query);
                //get all information from the table warranty_tracking following erp and orno (order number sales)
                $wcno = tldDINO::getHeader($erp, $val['t_orno']);
                //if wcno different to t_dino add warranty tracking with packing slip = t_dino
                if ($wcno['packing_slip'] != $val['t_dino']) {
                    $query = <<<SQL
					INSERT INTO warranty_tracking 
					SET parent_id={$wcno['parent_id']}, packing_slip={$val['t_dino']}, so_no={$val['t_orno']}, erp=$erp, date=NOW()";
SQL;
                    tldUtils::sqlInsert($query);
                }
                //SO information
                $SOheader = tldSO::getPackingSlipByERPBySO($erp, ['sors.t_orno' => $val['t_orno']]);
                foreach ($SOheader as $k => $v) {
                    $message = '';
                    $val['tracking_no'] = tldWC::getTracking($erp, $v['t_dino']);
                    $wc = new tldWC($wcno['parent_id']);
                    $header = $wc->getHeader();
                    $status = 1;
                    if (!empty($val['tracking_no'])) {
                        $ps = new tldDINO($v['t_dino'], $erp);
                        foreach ($ps->getDetail() as $item) {
                            foreach ($wc->getParts() as $part) {
                                if (trim($item['t_item']) == trim($part['part_number'])) {
                                    $status = tldWC::updatePart($part['id'], ['supply_it' => 'YES'], ['supply_it']);
                                    $message .= <<<HTML
				<br>Please note part#{$item['t_item']} for WC#{$wcno['parent_id']} has been shipped via {$val['tracking_no']}.<br>
HTML;
                                }
                            }
                        }
                    }
                    if (empty($status)) {
                        $message .= <<<HTML
					<br><a href="https://www.tld-gse.com/en/private/product_support/index.ps.php?m[0]=wc&m[1]=view&id={$wcno['parent_id']}">
					Click here to go to WC.
					</a></br>
HTML;
                        $to = [];
                        $partsEmail = '';
                        // --- CSA
                        $sso = tldLocation::getERPByLocation($header['sales_org']);
                        $location = new tldLocation(tldLocation::getIDByERP($sso));
                        $partsEmail = $location->getPartsEmail();

                        switch($sso){

                            case '540':
                                $partsEmail = 'euparts@tld-europe.com';
                                break;
                            case '680':
                                $grp = new tldGroup('role_CSA', $sso);
                                $header['entered_by'] = array_merge($grp->getEmailList(), [$header['entered_by']]);
                                break;
                            default:
                                break;
                        }
                            $e = tldUtils::emailAttachment($header['entered_by'], 'noreply@tld-gse.com', 'Sent Parts Notification - WC#' . $wcno['parent_id'], $message, null, $partsEmail, 'xiangrong.bi@tld-asia.com');
                    }

                    if (is_string($e)) {
                        $DEFAULT_ERROR[] = "ERROR: problem sending email to SPH, error returned was $e";
                        break;
                    }
                }
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "ERROR: Problem adding Tracking number for WC Part...<br/>Reason: $e";
                    break;
                }

                // Logging
                $logs = [];
                $FIELD_DESIGNATION = [
                    'parent_id' => 'WC#',
                    'erp' => 'ERP#',
                    'packing_slip' => 'Packing Slip#',
                    'tracking_no' => 'Tracking#',
                ];
                $fields_to_log = [
                    'parent_id',
                    'erp',
                    'packing_slip',
                    'tracking_no',
                ];
                // Get header
                $part_header = tldWC::getTrackingList($wcno['parent_id']);
                $logs[] = "<li><b>SO</b>: '{$vars['so_no']}'</li>";
                foreach ($fields_to_log as $field) {
                    $logs[] = "<li><b>{$FIELD_DESIGNATION[$field]}</b>: '{$part_header[0][$field]}'</li>";
                }
                $msg = 'Track number created:<br><ul>' . implode('', $logs) . '</ul>';
                $e = $wc->addLogEntry(1826, TldDatabase::escape($msg));
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "ERROR: Problem adding logs...<br/>Reason: $e";
                }
            }
        }
    }

}

/**
 * Class for accessing Purchase Contracts in ERP systems
 *
 * @package ERP
 */
class tldPC
{
    public $itsID; //po num
    public $itsERP; //erp system id

    public function __construct($id, $erp)
    {
        $this->itsID = $id;
        $this->itsERP = $erp;
        $this->itsDetails = $this->getHeader();
    }

    public function isEmpty()
    {
        $header = $this->getHeader();
        $detail = $this->getDetail();
        return empty($header) && empty($detail);
    }

    public function getHeader()
    {
        if (empty($this->itsID)) {
            return;
        }
        $id = $this->itsID;
        $erp = $this->itsERP;

        switch (tldERP::whatERP($erp)) {
            case 'baan':
                $query = <<<EOF
SELECT
    RTRIM(T1.t_cono) AS t_cono,
    RTRIM(T1.t_suno) AS t_suno,
    SUBSTRING(convert(varchar, T1.t_cdat, 120), 0, 11) AS t_cdat,
    T1.t_cwar,
    T1.t_ccon,
    T1.t_cdes,
    T1.t_refe,
    T1.t_ccur,
    (SELECT CASE
            WHEN SUM(T4.t_amta) >100000 THEN '3'
            WHEN SUM(T4.t_amta) >=20000 AND SUM(T4.t_amta) <100000 THEN '2'
            WHEN SUM(T4.t_amta) >=5000 AND SUM(T4.t_amta) <20000 THEN '1'
            WHEN SUM(T4.t_amta) <5000 AND SUM(T4.t_pric)>SUM(ITM1.t_ltpr) THEN '1'
            WHEN SUM(T4.t_amta) <5000 THEN '0'
            ELSE ''
        END
        FROM ttdpur301$erp AS T4 LEFT JOIN ttiitm001$erp AS ITM1 ON T4.t_item=ITM1.t_item
        WHERE T4.t_cono=T1.t_cono
    ) AS pc_level,
    (SELECT SUM(T5.t_amta) FROM ttdpur301$erp AS T5 WHERE T5.t_cono=T1.t_cono) AS total,
    (SELECT RTRIM(T6.t_info) FROM ttccom001$erp AS T6 WHERE T6.t_emno=T1.t_ccon) AS byr_email,
    (SELECT RTRIM(T7.t_nama) FROM ttccom020$erp AS T7 WHERE T7.t_suno=T1.t_suno) AS supplier
FROM
    dbo.ttdpur300$erp AS T1
WHERE
    t_cono='$id'
EOF;
                $opt2 = ['src' => 'baan'];
                break;
            default:
                return;
        }
        return tldUtils::getSqlRowToAssocArray($query, 'odbc', $opt2);
    }

    public function getSUNO()
    {
        return $this->itsDetails['t_suno'];
    }

    /**
     * Get PC log
     * WARNING: As a BAAN document the parent_id is the concataination of ERP# and PC#
     * @return array
     */
    public function getLog()
    {
        return tldModLog::byParent($this->itsERP . $this->itsID, 'PC');
    }

    /**
     * Add a comment to the log
     * WARNING: As a BAAN document the parent_id is the concataination of ERP# and PC#
     * @param int $id user
     * @param string $comment
     * @return boolean
     */
    public function addLogEntry($id, $comment)
    {
        $a['parent_id'] = $this->itsERP . $this->itsID;
        $a['module'] = 'PC';
        $a['poster'] = $id;
        $a['comment'] = $comment;
        return tldModLog::insert($a);
    }

    /**
     * Check PCs for necessary flags
     *
     * @return array
     */
    public function checkPC($erp = '', $a = '')
    {
        if (empty($erp)) {
            $erp = $this->itsERP;
        }
        if (empty($a)) {
            $in = $this->itsID;
        } else {
            $a_sav = $a;
            $a_before = count($a);
            $a = array_filter($a);
            $a_after = count($a);
            if ($a_before != $a_after) {
                tldUtils::emailAttachment(
                    'mis@tld-america.com,mis@tld-europe.com,mis@tld-asia.commis@tld-america.com,mis@tld-europe.com,mis@tld-asia.com',
                    'noreply@tld-gse.com',
                    '[DEV] erp.inc.php - class tldPC - function checkPC: some blank PCs in array',
                    'Some sequences can not be created<br>PCs in array: <br>PC# ' . implode('<br>PC# ', $a_sav)
                );
            }
            $in = implode(',', $a);
        }
        switch ($erp) {
            case '500':
            case '510':
            case '540':
            case '570':
                $levelOne = 2000;
                $levelTwo = 20000;
                $levelThree = 50000;
                break;
            case '520':
                $levelOne = 2000;
                $levelTwo = 10000;
                $levelThree = 50000;
                break;
            case '600':
            case '620':
            case '640':
            case '660':
            case '680':
                $levelOne = 0;
                $levelTwo = 50000;
                $levelThree = 500000;
                break;
            default:
                $levelOne = 5000;
                $levelTwo = 20000;
                $levelThree = 100000;
                break;
        }

        switch (tldERP::whatERP($erp)) {
            case 'baan':
                $query = <<<EOF
SELECT
    RTRIM(T1.t_cono) AS t_cono,
    RTRIM(T1.t_suno) AS t_suno,
    (SELECT RTRIM(T7.t_nama) FROM ttccom020$erp AS T7
        WHERE T7.t_suno=T1.t_suno
    ) AS supplier,
    SUBSTRING(convert(varchar, T1.t_cdat, 120), 0, 11) AS t_cdat,
    T1.t_cwar,
    T1.t_ccon,
    T1.t_cdes,
    T1.t_refe,
    (   SELECT CASE
    WHEN SUM(T4.t_amta)*T1.t_ratp >$levelThree THEN '3'
    WHEN SUM(T4.t_amta)*T1.t_ratp >=$levelTwo AND SUM(T4.t_amta)*T1.t_ratp <$levelThree THEN '2'
    WHEN SUM(T4.t_amta)*T1.t_ratp >=$levelOne AND SUM(T4.t_amta)*T1.t_ratp <$levelTwo THEN '1'
    WHEN SUM(T4.t_amta)*T1.t_ratp <$levelOne AND SUM(T4.t_pric)>SUM(ITM1.t_ltpr) THEN '1'
    WHEN SUM(T4.t_amta)*T1.t_ratp <$levelOne THEN '0'
    ELSE ''
    END
    FROM ttdpur301$erp AS T4 LEFT JOIN ttiitm001$erp AS ITM1 ON T4.t_item=ITM1.t_item
    WHERE T4.t_cono=T1.t_cono
    ) AS pc_level,
    (SELECT SUM(T5.t_amta) FROM ttdpur301$erp AS T5
       WHERE T5.t_cono=T1.t_cono
    ) AS total,
    (SELECT RTRIM(T6.t_info) FROM ttccom001$erp AS T6
       WHERE T6.t_emno=T1.t_ccon
    ) AS byr_email
FROM
    dbo.ttdpur300$erp AS T1
WHERE
    AND t_orno IN ($in)
EOF;
                $opt2 = ['src' => 'baan'];
                break;
        }
        return tldUtils::getSqlToAssocArray($query, 'odbc', $opt2);
    }
}

/**
 * Class for accessing Purchase Orders in ERP systems
 *
 * @package ERP
 */
class tldPO
{
    public $itsID; //po num
    public $itsERP; //erp system id

    public function __construct($id, $erp)
    {
        $this->itsID = $id;
        $this->itsERP = $erp;
        $this->itsDetails = $this->getHeader();
    }

    public function isEmpty()
    {
        $header = $this->getHeader();
        $detail = $this->getDetail();
        return empty($header) && empty($detail);
    }

    public function getSUNO()
    {
        return $this->itsDetails['t_suno'];
    }

    public function getERP()
    {
        return $this->itsERP;
    }

    public function asArray()
    {
        $result['header'] = $this->getHeader();
        $erp = tldERP::getERPOb($this->itsERP);
        $result['header']['cwar_data'] = $erp->getWarehouseData($result['header']['t_cwar']);
        $result['detail'] = $this->getDetail();
        return $result;
    }

    /**
     * Add the PO to the po_exten table.
     *
     * This authorize is display by our Evendor portal
     */
    public function display($id = 0, $erp = 0, $type = '')
    {
        if (empty($id)) {
            if (empty($this->itsID)) {
                return;
            }
            $id = $this->itsID;
        }

        if (empty($erp)) {
            if (empty($this->itsERP)) {
                return;
            }
            $erp = $this->itsERP;
        }

        $id = TldDatabase::escape($id);
        $erp = TldDatabase::escape($erp);
        $type = !empty($type) ? TldDatabase::escape($type) : $type;

        $query = "INSERT IGNORE INTO po_exten SET id='$id', erp='$erp', date_display=NOW(), type='$type'";
        $ret = tldUtils::sqlInsert($query);
        return $ret;
    }

    /**
     * get header info of PO
     *
     * @return array
     */
    public function getHeader()
    {
        if (empty($this->itsID)) {
            return;
        }
        $id = $this->itsID;
        $erp = $this->itsERP;

        switch (tldERP::whatERP($erp)) {
            case 'baan':
                $query = <<<EOF
SELECT
    RTRIM(T1.t_orno) AS t_orno,
    RTRIM(T1.t_suno) AS t_suno,
    SUBSTRING(convert(varchar, T1.t_odat, 120), 0, 11) AS t_odat,
    T1.t_cotp,
    T1.t_cwar,
    T1.t_ccon,
    T1.t_refa,
    T1.t_refb,
    T1.t_ccur,
    T1.t_cpay,
    T1.t_cdec,
    T1.t_ccty,
    (SELECT CASE
            WHEN SUM(T4.t_amta) >100000 THEN '3'
            WHEN SUM(T4.t_amta) >=20000 AND SUM(T4.t_amta) <100000 THEN '2'
            WHEN SUM(T4.t_amta) >=5000 AND SUM(T4.t_amta) <20000 THEN '1'
            WHEN SUM(T4.t_amta) <5000 AND SUM(T4.t_pric)>SUM(ITM1.t_ltpr) THEN '1'
            WHEN SUM(T4.t_amta) <5000 THEN '0'
            ELSE ''
        END
        FROM ttdpur041$erp AS T4 LEFT JOIN ttiitm001$erp AS ITM1 ON T4.t_item=ITM1.t_item
        WHERE T4.t_orno=T1.t_orno
    ) AS po_level,
    (SELECT SUM(T5.t_amta) FROM ttdpur041$erp AS T5 WHERE T5.t_orno=T1.t_orno) AS total,
    (SELECT RTRIM(T6.t_info) FROM ttccom001$erp AS T6 WHERE T6.t_emno=T1.t_ccon) AS byr_email,
    (SELECT RTRIM(T7.t_nama) FROM ttccom020$erp AS T7 WHERE T7.t_suno=T1.t_suno) AS supplier,
    (SELECT RTRIM(T8.t_iscn) FROM ttccom020$erp AS T8 WHERE T8.t_suno=T1.t_suno) AS t_comp
FROM
    dbo.ttdpur040$erp AS T1
WHERE
    t_orno='$id'
EOF;
                $opt2 = ['src' => 'baan'];
                if ($erp == '251') {
                    $opt2 = ['src' => 'baantest'];
                }
                break;
            default:
                return;
        }
        return tldUtils::getSqlRowToAssocArray($query, 'odbc', $opt2);
    }

    public function getBuyerEmail()
    {
        return $this->itsDetails['byr_email'];
    }

    /**
     * Get PO log
     * WARNING: As a BAAN document the parent_id is the concataination of ERP# and PO#
     * @return array
     */
    public function getLog()
    {
        return tldModLog::byParent($this->itsERP . $this->itsID, 'PO');
    }

    /**
     * Add a comment to the log
     * WARNING: As a BAAN document the parent_id is the concataination of ERP# and PO#
     * @param int $id user
     * @param string $comment
     * @return boolean
     */
    public function addLogEntry($id, $comment)
    {
        $a['parent_id'] = $this->itsERP . $this->itsID;
        $a['module'] = 'PO';
        $a['poster'] = $id;
        $a['comment'] = $comment;
        return tldModLog::insert($a);
    }

    /**
     * Get the PRE tax total for the PO
     *
     * @return long
     */
    public function getTotal()
    {
        if (empty($this->itsID)) {
            return;
        }
        $id = $this->itsID;
        $erp = $this->itsERP;

        switch (tldERP::whatERP($erp)) {
            case 'baan':
                $query = <<<EOF
				SELECT 	sum(pur041.t_amta) AS t_total
				FROM ttdpur041$this->itsERP AS pur041
				WHERE pur041.t_orno='$this->itsID'
		GROUP BY pur041.t_orno
EOF;
                $row = tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
                break;
        }
        return $row['t_total'];
    }

    /**
     * Get open lines of PO
     * @return array return array of purchase order lines, empty array if none
     */
    public function getDetail()
    {
        if (empty($this->itsID)) {
            return;
        }
        return tldPOL::byParent($this->itsERP, $this->itsID);
    }

    /**
     * Get all lines of PO to invoice
     * @return array return array of purchase order lines, empty array if none
     */
    public function getDetailOpenMatch()
    {
        if (empty($this->itsID) || empty($this->itsERP)) {
            return;
        }
        return tldPOL::byParentByOpenMatch($this->itsERP, $this->itsID);
    }

    /**
     * Get detail of shipped PO lines
     * @return array return array of purchase order lines, empty array if none
     */
    public function getShippedDetail()
    {
        if (empty($this->itsID) || empty($this->itsERP)) {
            return;
        }
        return tldPOL::byParentShipped($this->itsERP, $this->itsID);
    }
    //STATIC FUNCTIONS

    /**
     * Check pos for necessary flags
     *
     * @return array
     */
    public function checkPO($erp = '', $a = '')
    {
        if (empty($erp)) {
            $erp = $this->itsERP;
        }
        if (empty($a)) {
            $in = $this->itsID;
        } else {
            $a_sav = $a;
            $a_before = count($a);
            $a = array_filter($a);
            $a_after = count($a);
            if ($a_before != $a_after) {
                tldUtils::emailAttachment(
                    'mis@tld-america.com,mis@tld-europe.com,mis@tld-asia.commis@tld-america.com,mis@tld-europe.com,mis@tld-asia.com',
                    'noreply@tld-gse.com',
                    '[DEV] erp.inc.php - class tldPO - function checkPO: some blank POs in array',
                    'Some sequences can not be created<br>POs in array: <br>PO# ' . implode('<br>PO# ', $a_sav)
                );
            }
            $in = implode(',', $a);
        }
        //PS1         PO
        //PCC         cost item
        //PU1         finish unit                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        a.purchase finish unit
        $excludedPoTypes = "'PS1','PCC','PU1'";

        $levelOne = 5000;
        $levelTwo = 20000;
        $levelThree = 100000;
        $levelFour = 1000000004;

        switch ($erp) {
            case '250':
                $levelOne = 10000;
                $levelTwo = 100000;
                $levelThree = 100000000003;
                $excludedPoTypes = "'PCC'";
                break;
            case '251':
                $levelOne = 1000000001;
                $levelTwo = 1000000002;
                $levelThree = 1000000003;
                break;
            case '300':
            case '560':
            case '540':
                $levelOne = 2000;
                $levelTwo = 20000;
                $levelThree = 50000;
                $excludedPoTypes = "'PCC','PU1'";
                break;
            case '500':
            case '510':
            case '520':
            case '570':
            case '220':
                $excludedPoTypes = "'PCC','PU1'";
            case '400':
            case '410':
            case '420':
                $levelOne = 2000;
                $levelTwo = 20000;
                $levelThree = 50000;
                $levelFour = 100000;
                break;
            case '600':
                $levelOne = 20000;
                $levelTwo = 200000;
                $levelThree = 500000;
                break;
            case '660':
            case '620':
            case '640':
            case '680':
                $levelOne = 0;
                $levelTwo = 50000;
                $levelThree = 500000;
                $excludedPoTypes = "'PCC','PU1'";
                break;
        }

        switch (tldERP::whatERP($erp)) {
            case 'baan':
                $query = <<<EOF
SELECT
    RTRIM(T1.t_orno) AS t_orno,
	RTRIM(T1.t_suno) AS t_suno,
	(SELECT RTRIM(T7.t_nama) FROM ttccom020$erp AS T7
	    WHERE T7.t_suno=T1.t_suno
	) AS supplier,
	SUBSTRING(convert(varchar, T1.t_odat, 120), 0, 11) AS t_odat,
	T1.t_cwar,
	T1.t_ccon,
	T1.t_refa,
	T1.t_refb,
	(	SELECT CASE
	WHEN SUM(T4.t_amta)*T1.t_ratp >= $levelFour THEN '4' 
    WHEN SUM(T4.t_amta)*T1.t_ratp >= $levelThree AND SUM(T4.t_amta)*T1.t_ratp < $levelFour THEN '3'
	WHEN SUM(T4.t_amta)*T1.t_ratp >= $levelTwo AND SUM(T4.t_amta)*T1.t_ratp < $levelThree THEN '2'
	WHEN SUM(T4.t_amta)*T1.t_ratp >= $levelOne AND SUM(T4.t_amta)*T1.t_ratp < $levelTwo THEN '1'
	WHEN SUM(T4.t_amta)*T1.t_ratp < $levelOne AND SUM(T4.t_pric)>SUM(ITM1.t_ltpr) THEN '1'
	WHEN SUM(T4.t_amta)*T1.t_ratp < $levelOne THEN '0'
	ELSE ''
	END
	FROM ttdpur041$erp AS T4 LEFT JOIN ttiitm001$erp AS ITM1 ON T4.t_item=ITM1.t_item
	WHERE T4.t_orno=T1.t_orno
	) AS po_level,
	(SELECT SUM(T5.t_amta) FROM ttdpur041$erp AS T5
	   WHERE T5.t_orno=T1.t_orno
	) AS total,
	(SELECT RTRIM(T6.t_info) FROM ttccom001$erp AS T6
	   WHERE T6.t_emno=T1.t_ccon
	) AS byr_email
FROM
    dbo.ttdpur040$erp AS T1
WHERE
    RTRIM(t_cotp) NOT IN ($excludedPoTypes)
    AND t_orno IN ($in)
EOF;
                $opt2 = ['src' => 'baan'];
                if ($erp == '251') {
                    $opt2 = ['src' => 'baantest'];
                }
                break;
        }
        return tldUtils::getSqlToAssocArray($query, 'odbc', $opt2);
    }

    /**
     * get detail lines of PO by ERP
     *
     * @return array return array of purchase order lines, empty array if none
     */
    public static function getDetailByERP($erp, $options = '')
    {
        return tldPOL::byERP($erp, $options);
    }

    public static function countStatsByVendorERP($vendorid, $erp, $options = '')
    {
        $res = [];
        switch (tldERP::whatERP($erp)) {
            case 'baan':
                $startDate = new DateTime('2016-06-22');
                $rows = self::getDetailByVendorERP(
                    $vendorid,
                    $erp,
                    ['mode' => 'byLate']
                );
                $ok_list = tldVendor::checkPODisplay(array_column((array) $rows, 't_orno'), $erp);
                foreach ($rows as $key => $dat) {
                    $date_ordo = new DateTime($dat['t_odat']);
                    if (($startDate < $date_ordo) && (array_search($dat['t_orno'], $ok_list) === FALSE)) {
                        unset($rows[$key]);
                    }
                }
                foreach($rows as $key=>$row){
                    $check = tldVendor::checkPOSequence(trim($row['t_orno']),$erp);
                    if($check !== 'ACCEPTED'){
                        unset($rows[$key]);
                    }
                }
                $res['late'] = count($rows);
                $rows = self::getDetailByVendorERP(
                    $vendorid,
                    $erp,
                    ['mode' => 'byUnconfirmed']
                );
                $ok_list = tldVendor::checkPODisplay(array_column((array) $rows, 't_orno'), $erp);
                foreach ($rows as $key => $dat) {
                    $date_ordo = new DateTime($dat['t_odat']);
                    if (($startDate < $date_ordo) && (array_search($dat['t_orno'], $ok_list) === FALSE)) {
                        unset($rows[$key]);
                    }
                }
                foreach($rows as $key=>$row){
                    $check = tldVendor::checkPOSequence(trim($row['t_orno']),$erp);
                    if($check !== 'ACCEPTED'){
                        unset($rows[$key]);
                    }
                }
                $res['unconfirmed'] = count($rows);
                $rows = (array) self::getDetailByVendorERP(
                    $vendorid,
                    $erp,
                    ['mode' => 'within_seven_days']
                );
                $ok_list = tldVendor::checkPODisplay(array_column($rows, 't_orno'), $erp);
                foreach ($rows as $key => $dat) {
                    $date_ordo = new DateTime($dat['t_odat']);
                    if (($startDate < $date_ordo) && (array_search($dat['t_orno'], $ok_list) === FALSE)) {
                        unset($rows[$key]);
                    }
                }
                foreach($rows as $key=>$row){
                    $check = tldVendor::checkPOSequence(trim($row['t_orno']),$erp);
                    if($check !== 'ACCEPTED'){
                        unset($rows[$key]);
                    }
                }
                $res['within_seven_days'] = count($rows);
                break;
        }
        return $res;
    }

    /**
     * get open po lines by vendor and erp
     *
     * @return array return array of purchase order lines, empty array if none
     */
    public static function getDetailByVendorERP($vendorid, $erp, $options = '')
    {
        return tldPOL::byVendorERP($erp, $vendorid, $options);
    }

    /**
     * Get the OPEN pos
     * Return columns orno, odat, refa, refb
     *
     * @return array
     */
    public static function byVendorErp($vendorid, $erp)
    {
        switch (tldERP::whatERP($erp)) {
            case 'baan':
                $query = <<<EOF
				SELECT DISTINCT(T1.t_orno),
				SUBSTRING(CONVERT(varchar, T1.t_orno), 0, 3) AS idx,
				SUBSTRING(CONVERT(varchar,T1.t_odat,120), 0, 11) AS t_odat,
				t_refa, t_refb,
				'confirmed'=CASE WHEN (SELECT TOP 1 pur041.t_ddtc
				FROM ttdpur041$erp AS pur041
				WHERE pur041.t_orno=T1.t_orno
				ORDER BY pur041.t_ddtc)>'1900-01-01'
				THEN 'Y'
				ELSE ''
				END
				FROM  ttdpur040$erp AS T1,
				ttdpur045$erp AS T2
				WHERE T1.t_orno=T2.t_orno
				AND T2.t_suno='$vendorid'
				AND T2.t_srnb=(select MAX(T3.t_srnb)
				FROM ttdpur045$erp AS T3
			  WHERE T2.t_orno=T3.t_orno AND T2.t_pono=T3.t_pono)
			AND (T2.t_spur<9 OR (T2.t_spur=9 AND T2.t_bqua>0))
			ORDER by T1.t_orno
EOF;
                $opt1 = 'odbc';
                $opt2 = ['src' => 'baan'];
                break;
            default:
                return;
        }

        return tldUtils::getSqlToAssocArray($query, $opt1, $opt2);
    }

    public function getInvoiceNumBySuno($suno, $erp)
    {
        if (empty($suno) || empty($erp)) {
            return 'Empty parameter';
        }
        switch ($erp) {
            case 500:
            case 520:
                $erpedm = 540;
                break;
            default:
                $erpedm = $erp;
                break;
        }
        $query = "SELECT DISTINCT(t1.t_isup) FROM ttfacp200$erpedm AS t1 WHERE t1.t_suno='$suno'";
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     * Convert Fedex XML to TLD XML
     *
     *
     * @return array
     *
     * NOTE:
     * <code>
     * $xmlstring = <<<XML
     * <?xml version = '1.0' standalone = 'no'?>
     * <!DOCTYPE PROCESS_PO_003 SYSTEM "003_process_po_003.dtd">
     * <PROCESS_PO_003>
     * <CNTROLAREA>
     * <BSR>
     * <VERB value="PROCESS">PROCESS</VERB>
     * <NOUN value="PO">PO</NOUN>
     * <REVISION value="003">003</REVISION>
     * </BSR>
     * <SENDER>
     * <LOGICALID>9345232</LOGICALID>
     * <COMPONENT>PURCHASING</COMPONENT>
     * <TASK>POISSUE</TASK>
     * <REFERENCEID>22</REFERENCEID>
     * <CONFIRMATION>0</CONFIRMATION>
     * <LANGUAGE>US</LANGUAGE>
     * <CODEPAGE>UTF8</CODEPAGE>
     * <AUTHID>AEX</AUTHID>
     * </SENDER>
     * <DATETIME qualifier="CREATION">
     * <YEAR>2005</YEAR>
     * <MONTH>12</MONTH>
     * <DAY>30</DAY>
     * <HOUR>07</HOUR>
     * <MINUTE>06</MINUTE>
     * <SECOND>02</SECOND>
     * <SUBSECOND>0000</SUBSECOND>
     * <TIMEZONE>-0800</TIMEZONE>
     * </DATETIME>
     * </CNTROLAREA>
     * <DATAAREA>
     * <PROCESS_PO>
     * <POORDERHDR>
     * <DATETIME qualifier="DOCUMENT">
     * <YEAR>2005</YEAR>
     * <MONTH>12</MONTH>
     * <DAY>30</DAY>
     * <HOUR>07</HOUR>
     * <MINUTE>08</MINUTE>
     * <SECOND>33</SECOND>
     * <SUBSECOND>0000</SUBSECOND>
     * <TIMEZONE>-0800</TIMEZONE>
     * </DATETIME>
     * <OPERAMT qualifier="EXTENDED" type="T">
     * <VALUE>4556</VALUE>
     * <NUMOFDEC>2</NUMOFDEC>
     * <SIGN>+</SIGN>
     * <CURRENCY>USD</CURRENCY>
     * <UOMVALUE>1</UOMVALUE>
     * <UOMNUMDEC>0</UOMNUMDEC>
     * <UOM/>
     * </OPERAMT>
     * <POID>1573</POID>
     * <POTYPE>AutoOrder</POTYPE>
     * <ACKREQUEST>1</ACKREQUEST>
     * <DESCRIPTN>Test Order</DESCRIPTN>
     * <NOTES/>
     * <USERAREA>
     * <EXT_COST_CENTER>144048</EXT_COST_CENTER>
     * <EXT_ACCOUNT_CODE>657600</EXT_ACCOUNT_CODE>
     * <EXT_EMPLOYEE_NUM>404056</EXT_EMPLOYEE_NUM>
     * <EXT_PROJECT_NUM>256466</EXT_PROJECT_NUM>
     * <EXT_LOCATION>466</EXT_LOCATION>
     * <EXT_NON_REV_NUM>1749-8508-1</EXT_NON_REV_NUM>
     * <DATETIME qualifier="NEEDDELV">
     * <YEAR>2006</YEAR>
     * <MONTH>02</MONTH>
     * <DAY>23</DAY>
     * <HOUR>21</HOUR>
     * <MINUTE>59</MINUTE>
     * <SECOND>00</SECOND>
     * <SUBSECOND>0000</SUBSECOND>
     * <TIMEZONE>-0800</TIMEZONE>
     * </DATETIME>
     * <POIDX/>
     * <POSTATUS>Open</POSTATUS>
     * <PAYMMETHOD>
     * <DESCRIPTN>Invoice Account</DESCRIPTN>
     * <TERMID>IA</TERMID>
     * </PAYMMETHOD>
     * </USERAREA>
     * <PARTNER>
     * <NAME index="1">GSE Test Supplier for FedEx</NAME>
     * <ONETIME>0</ONETIME>
     * <PARTNRID>29076</PARTNRID>
     * <PARTNRTYPE>SUPPLIER</PARTNRTYPE>
     * <CURRENCY>USD</CURRENCY>
     * <PARTNRIDX>29076</PARTNRIDX>
     * <USERAREA><SITEID>0</SITEID></USERAREA>
     * </PARTNER>
     * <PARTNER>
     * <NAME index="1">Aeroxchange Ltd.</NAME>
     * <ONETIME>0</ONETIME>
     * <PARTNRID>15561</PARTNRID>
     * <PARTNRTYPE>SOLDTO</PARTNRTYPE>
     * <CURRENCY>USD</CURRENCY>
     * <PARTNRIDX>15561</PARTNRIDX>
     * <CONTACT>
     * <NAME index="1">Nikhil Chandurkar</NAME>
     * <EMAIL>cnikhil@yahoo.com</EMAIL>
     * <FAX index="1"/>
     * <TELEPHONE index="1">9725568531 </TELEPHONE>
     * </CONTACT>
     * </PARTNER>
     * <PARTNER>
     * <NAME index="1">FedEx 2 Day</NAME>
     * <ONETIME>0</ONETIME>
     * <PARTNRID>FE03</PARTNRID>
     * <PARTNRTYPE>CARRIER</PARTNRTYPE>
     * </PARTNER>
     * <PARTNER>
     * <NAME index="1">My Office</NAME>
     * <ONETIME>0</ONETIME>
     * <PARTNRID>34487</PARTNRID>
     * <PARTNRTYPE>BILLTO</PARTNRTYPE>
     * <PARTNRIDX>15561</PARTNRIDX>
     * <ADDRESS>
     * <ADDRLINE index="1">5221 O'Connor</ADDRLINE>
     * <ADDRLINE index="2">Suite 800 E</ADDRLINE>
     * <ADDRLINE index="3"/>
     * <ADDRLINE index="4"/>
     * <CITY>Irving</CITY>
     * <COUNTRY>US</COUNTRY>
     * <POSTALCODE>75039</POSTALCODE>
     * <STATEPROVN>TX</STATEPROVN>
     * </ADDRESS>
     * </PARTNER>
     * </POORDERHDR>
     * <POORDERLIN>
     * <QUANTITY qualifier="ORDERED">
     * <VALUE>1</VALUE>
     * <NUMOFDEC/>
     * <SIGN>+</SIGN>
     * <UOM>EA</UOM>
     * </QUANTITY>
     * <OPERAMT qualifier="UNIT" type="T">
     * <VALUE>4556</VALUE>
     * <NUMOFDEC>2</NUMOFDEC>
     * <SIGN>+</SIGN>
     * <CURRENCY>USD</CURRENCY>
     * <UOMVALUE>1</UOMVALUE>
     * <UOMNUMDEC>0</UOMNUMDEC>
     * <UOM/>
     * </OPERAMT>
     * <POLINENUM>1</POLINENUM>
     * <HAZRDMATL/>
     * <NOTES/>
     * <DESCRIPTN>GSE Test Part</DESCRIPTN>
     * <ITEM/>
     * <ITEMX>GSETESTPART</ITEMX>
     * <USERAREA>
     * <MFRNAME>GSETESTPART</MFRNAME>
     * <MFRNUM>GSETESTPART</MFRNUM>
     * <DATETIME qualifier="NEEDDELV">
     * <YEAR>2006</YEAR>
     * <MONTH>02</MONTH>
     * <DAY>23</DAY>
     * <HOUR>21</HOUR>
     * <MINUTE>59</MINUTE>
     * <SECOND>00</SECOND>
     * <SUBSECOND>0000</SUBSECOND>
     * <TIMEZONE>-0800</TIMEZONE>
     * </DATETIME>
     * </USERAREA>
     * <PARTNER>
     * <NAME index="1">Global</NAME>
     * <ONETIME>0</ONETIME>
     * <PARTNRID>31074</PARTNRID>
     * <PARTNRTYPE>SHIPTO</PARTNRTYPE>
     * <PARTNRIDX>15561</PARTNRIDX>
     * <ADDRESS>
     * <ADDRLINE index="1">5221 N. O'Connor Blvd.</ADDRLINE>
     * <ADDRLINE index="2">Suite 800 E</ADDRLINE>
     * <ADDRLINE index="3"/>
     * <ADDRLINE index="4"/>
     * <CITY>Irving</CITY>
     * <COUNTRY>US</COUNTRY>
     * <POSTALCODE>75039</POSTALCODE>
     * <STATEPROVN>TX</STATEPROVN>
     * </ADDRESS>
     * </PARTNER>
     * </POORDERLIN>
     * </PROCESS_PO>
     * </DATAAREA>
     * </PROCESS_PO_003>
     * XML;
     * </code>
     */
    public function fromCXMLToTLD($xmlstring)
    {

        // create a simple xml element $xml
        $xml = new SimpleXMLElement($xmlstring);

        //-------------- Get data needed -------------------------------------------
        //-Get Delivery Date : convert Delivery date in the good format MM-DD-YYYY in $deldate
        $deldate = $xml->DATAAREA->PROCESS_PO->POORDERHDR->USERAREA->DATETIME->MONTH;
        $deldate .= '-' . $xml->DATAAREA->PROCESS_PO->POORDERHDR->USERAREA->DATETIME->DAY;
        $deldate .= '-' . $xml->DATAAREA->PROCESS_PO->POORDERHDR->USERAREA->DATETIME->YEAR;

        //-Get carrier
        $carrier = $xml->DATAAREA->PROCESS_PO->POORDERHDR->PARTNER[2]->NAME;

        //-Get delivery address
        $delline1 = $xml->DATAAREA->PROCESS_PO->POORDERLIN->PARTNER[0]->ADDRESS->ADDRLINE[1];
        $delline2 = $xml->DATAAREA->PROCESS_PO->POORDERLIN->PARTNER[0]->ADDRESS->ADDRLINE[2];
        $delline3 = $xml->DATAAREA->PROCESS_PO->POORDERLIN->PARTNER[0]->ADDRESS->ADDRLINE[3];
        $delline4 = $xml->DATAAREA->PROCESS_PO->POORDERLIN->PARTNER[0]->ADDRESS->ADDRLINE[4];
        $delline5 = $xml->DATAAREA->PROCESS_PO->POORDERLIN->PARTNER[0]->ADDRESS->CITY .
            ',' . $xml->DATAAREA->PROCESS_PO->POORDERLIN->PARTNER[0]->ADDRESS->STATEPROVN .
            ' ' . $xml->DATAAREA->PROCESS_PO->POORDERLIN->PARTNER[0]->ADDRESS->POSTALCODE;
        $delline6 = $xml->DATAAREA->PROCESS_PO->POORDERLIN->PARTNER[0]->ADDRESS->COUNTRY;

        // creation the array $so that will be sent to template erp/wise.so.tpl
        $so = [
            'header' => [
                'customer' => [
                    't_cuno' => 'fedex',
                ],
                't_odat' => $deldate,
                'carrier' => ['t_cfrw' => $carrier],
                'shipComplete' => 'shipcomplete',
                'deliverAddress' => [
                    'line1' => $delline1,
                    'line2' => $delline2,
                    'line3' => $delline3,
                    'line4' => $delline4,
                    'line5' => $delline5,
                    'line6' => $delline6,
                    't_cdel' => 'shipto_code',
                ],
            ],
        ];

        $smarty = tldUtils::getSmarty('common');
        $smarty->assign('so', $so);
        return $smarty->fetch('erp/wise.so.tpl');
    }

    /**
     * Post PO invoice to BAAN
     * @param array $a
     * @return soapFault on error
     */
    public function postPOInvoice($a)
    {
        $baan = new tldBaanERP($this->itsERP);
        $soap = $baan->getSOAP();
        try {
            $return = $soap->postPOInvoice($a);
        } catch (Exception $ex) {
            return $ex->faultstring;
        }
        return $return;
    }

    /**
     * Post PO line confirmation date to BAAN
     * @param array $a
     * @return soapFault on error
     */
    public function postPOLineConfirmDates($a)
    {
        $baan = new tldBaanERP($this->itsERP);
        $soap = $baan->getSOAP();
        try {
            $return = $soap->postPOLineConfirmDates($a);
        } catch (Exception $ex) {
            return $ex->faultstring;
        }
        return $return;
    }
}

/**
 * Class for PO lines
 *  $this->itsID
 *  $this->itsERP
 */
class tldPOL
{
    //new thresholds for vendor reliability decided by TTS #746984
    const RELIABILITY_LOW_THRESHOLD = -15;
    const RELIABILITY_HIGH_THRESHOLD = 5;

    public function __construct($erp, $id)
    {
        $this->itsID = $id;
        $this->itsERP = $erp;
    }

    public static function countStatsByConstraints($erp, $a = NULL)
    {
        if (empty($erp)) {
            return;
        }
        // Construct constraints
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = " WHERE $WHERE";
        }
        $query = <<<EOF
SELECT
	SUM(T1.t_oqua-T1.t_dqua) AS POs,
	COUNT(CASE WHEN (MRP.t_date < T1.t_ddtc AND MRP.t_date <>'1753-01-01'
		AND T1.t_ddtc<>'1753-01-01 00:00:00.000')
		THEN 'Y'
		ELSE null
		END
	) AS lateVsConfDate,
	COUNT(CASE WHEN (T1.t_ddtc <>'1753-01-01' AND T1.t_ddtc < GETDATE())
		THEN 'Y'
		ELSE null
		END
	) AS late,
	COUNT(CASE WHEN T1.t_ddtc='1753-01-01 00:00:00.000'
		THEN 'Y'
		ELSE null
		END
	) AS unconfirmed,
	COUNT(CASE WHEN DATEDIFF(day, getdate(), T1.t_ddtb) < 7
		THEN 'Y'
		ELSE NULL
		END
	) AS within_seven_days,
	COUNT(CASE WHEN DATEDIFF(day, getdate(), T1.t_ddtb) < 30
		THEN 'Y'
		ELSE NULL
		END
	) AS within_thirty_days,
	COUNT(CASE WHEN DATEDIFF(day, getdate(), T1.t_ddtb) < 90
		THEN 'Y'
		ELSE NULL
		END
	) AS within_ninety_days
FROM
	ttdpur041$erp AS T1
	LEFT JOIN ttimrp030$erp AS MRP
		ON T1.t_orno=MRP.t_orno AND T1.t_pono=MRP.t_pono
	LEFT JOIN ttiedm100400 AS EDM
		ON (T1.t_item=EDM.t_eitm AND T1.t_odat>=EDM.t_indt AND
		(T1.t_odat < EDM.t_exdt OR EDM.t_exdt='1753-01-01'))
	LEFT JOIN ttdpur045$erp AS T2
		ON T1.t_orno=T2.t_orno AND T1.t_pono=T2.t_pono
	LEFT JOIN ttiitm001$erp AS ITM
		ON T1.t_item=ITM.t_item
	LEFT JOIN ttdpur040$erp AS T3
		ON T1.t_orno=T3.t_orno
$WHERE
EOF;
        return tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     * Count stats by constraints for OPEN lines
     * @param int $erp
     * @param array or string $a
     * @return array row
     */
    public static function countOpenStatsByConstraints($erp, $constraints = NULL)
    {
        if (empty($erp)) {
            return;
        }
        // Construct constraints
        if (is_array($constraints)) {
            $WHERE = ' AND ' . tldUtils::constructWhere($constraints);
        } elseif (!empty($constraints)) {
            $WHERE = " AND $constraints";
        }
        $a = <<<EOF
T2.t_srnb=(SELECT MAX(t2.t_srnb)
	FROM ttdpur045$erp AS t2
	WHERE t2.t_orno=T2.t_orno AND t2.t_pono=T2.t_pono)
AND (T2.t_spur<9 OR (T2.t_spur=9 AND T2.t_bqua>0))
AND T1.t_oqua>T1.t_dqua
$WHERE
EOF;
        return self::countStatsByConstraints($erp, $a);
    }

    /**
     * Count stats OPEN lines by buyer email
     * @param int $erp
     * @param string $email
     * @return array row
     */
    public static function countOpenStatsByBuyerEmail($erp, $email)
    {
        $a = <<<EOF
(SELECT RTRIM(t6.t_info) FROM ttccom001$erp AS t6 WHERE t6.t_emno=T3.t_ccon)='$email'
EOF;
        return self::countOpenStatsByConstraints($erp, $a);
    }

    /**
     * Count stats OPEN lines by suno
     * @param int $erp
     * @param string $suno
     * @return array row
     */
    public static function countOpenStatsBySuno($erp, $suno)
    {
        if (empty($suno)) {
            return;
        }
        $a = "T3.t_suno='$suno'";
        return self::countOpenStatsByConstraints($erp, $a);
    }

    /**
     * Count stats OPEN lines by Item
     * @param int $erp
     * @param string $item
     * @return array row
     */
    public static function countOpenStatsByItem($erp, $item)
    {
        if (empty($item)) {
            return;
        }
        $a = "T1.t_item='$item'";
        return self::countOpenStatsByConstraints($erp, $a);
    }

    public static function countOpenStatsByItem2($erp, $item)
    {
        if (empty($item) || empty($erp)) {
            return [];
        }
        $query = <<<EOF
SELECT 
    SUM(T1.t_oqua-T1.t_quap) AS POs
FROM
    ttdpur041$erp AS T1
    LEFT JOIN ttdpur045$erp AS T2
        ON T1.t_orno=T2.t_orno AND T1.t_pono=T2.t_pono
    LEFT JOIN ttiitm001$erp AS ITM
        ON T1.t_item=ITM.t_item
    LEFT JOIN ttdpur040$erp AS T3
        ON T1.t_orno=T3.t_orno
WHERE 
    T2.t_srnb=(SELECT MAX(t2.t_srnb) FROM ttdpur045$erp AS t2 WHERE t2.t_orno=T2.t_orno AND t2.t_pono=T2.t_pono)
    AND (T2.t_spur<9 OR (T2.t_spur=9 AND T2.t_bqua>0))
    AND T1.t_oqua>T1.t_dqua 
    AND T1.t_item='$item'
EOF;
        return tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     * Get PO lines stats delivered by constraints
     * @param int $erp
     * @param mixed array or string $a
     * @return array
     */
    public static function countDeliveryStatsByConstraints($erp, $a = NULL)
    {
        if (empty($erp)) {
            return [];
        }
        // Construct constraints
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "AND $WHERE";
        }
        $query = <<<EOF
SELECT
	SUM(T1.t_dqua) AS total_qty_delivery
FROM
	ttdpur045$erp AS T1
	LEFT JOIN ttdpur040$erp AS T2 ON T1.t_orno=T2.t_orno
WHERE
	T1.t_srnb<>0
	$WHERE
EOF;
        return tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public static function countOrderedByConstraints($erp, $a = NULL)
    {
        if (empty($erp)) {
            return [];
        }
        // Construct constraints
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "AND $WHERE";
        }
        $query = <<<EOF
SELECT
	SUM(T1.t_oqua) AS total_qty_ordered
FROM
	ttdpur041$erp AS T1
LEFT JOIN ttdpur045$erp AS T2 ON T1.t_orno=T2.t_orno AND T1.t_pono=T2.t_pono
WHERE
	T2.t_srnb=(SELECT MAX(T4.t_srnb) FROM ttdpur045$erp AS T4
        WHERE T1.t_orno=T4.t_orno AND T1.t_pono=T4.t_pono)
	$WHERE
EOF;
        return tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     * Count delivered PO lines by period by constraints
     * @param int $erp
     * @param date $start
     * @param date $end
     * @param mixed string or array $a
     * @return array
     */
    public static function countDeliveryStatsByPeriodDeliveredByConstraints($erp, $start, $end, $a = NULL)
    {
        if (empty($erp)) {
            return;
        }
        $WHERE = "T1.t_date BETWEEN '$start' AND '$end'";
        if (is_array($a)) {
            $WHERE .= ' AND ' . tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE .= " AND $a";
        }
        return self::countDeliveryStatsByConstraints($erp, $WHERE);
    }

    public function getPonoByBUByConstraints($erp, $pn, $orno)
    {
        if (!empty($pn)) {
            $WHERE = "WHERE T1.t_item = '$pn'";
        }
        if (!empty($orno)) {
            $WHERE .= " AND T1.t_orno = '$orno'";
        }
        $query = <<<EOF
		SELECT
			T1.t_pono
		FROM ttdpur041$erp AS T1
		$WHERE

EOF;

        $row = tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);

        return $row['t_pono'];
    }

    public static function sumOrderedByPeriodByConstraints($erp, $start, $end, $a = NULL)
    {
        if (empty($erp)) {
            return [];
        }
        // Construct constraints
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "AND $WHERE";
        }
        $query = <<<EOF
SELECT
	CONVERT(DECIMAL(9,2),ROUND(SUM(T3.t_oqua*T3.t_pric*T2.t_ratp)/1000,2)) AS total_ordered_k, T2.t_ccur AS currency
FROM
	ttdpur041$erp AS T3
	LEFT JOIN ttdpur045$erp AS T1 ON T3.t_orno=T1.t_orno AND T3.t_pono=T1.t_pono
	LEFT JOIN ttdpur040$erp AS T2 ON T1.t_orno=T2.t_orno
WHERE
	T1.t_srnb=(SELECT MAX(T4.t_srnb) FROM ttdpur045$erp AS T4
        WHERE T3.t_orno=T4.t_orno AND T3.t_pono=T4.t_pono) AND
	T3.t_odat BETWEEN '$start' AND '$end'
	$WHERE
GROUP BY T2.t_ccur
EOF;
        return tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
    }


    public static function sumDeliveryByPeriodByConstraints($erp, $start, $end, $a = NULL)
    {
        if (empty($erp)) {
            return [];
        }
        // Construct constraints
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "AND $WHERE";
        }
        $query = <<<EOF
		SELECT
		CONVERT(DECIMAL(9,2),ROUND(SUM(T1.t_dqua*T1.t_pric*T2.t_ratp)/1000,2)) AS total_delivered_k
		FROM
		ttdpur045$erp AS T1
		LEFT JOIN ttdpur040$erp AS T2 ON T1.t_orno=T2.t_orno
		WHERE
		T1.t_srnb<>0 AND T1.t_date BETWEEN '$start' AND '$end'
		$WHERE
EOF;
        return tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public static function sumInvoicedByPeriodByConstraints($erp, $start, $end, $a = NULL)
    {
        if (empty($erp)) {
            return [];
        }
        // Construct constraints
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "AND $WHERE";
        }
        $query = <<<EOF
		SELECT
			CONVERT(decimal(9,2),ROUND(SUM(T3.t_amtc*T2.t_ratp)/1000,2)) AS total_invoiced_k
		FROM
			ttdpur046$erp as T3

			LEFT JOIN ttdpur040$erp AS T2 ON T3.t_orno=T2.t_orno
		WHERE
		T3.t_pdat BETWEEN '$start' AND '$end'
		$WHERE
EOF;
        return tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     * Count invoiced PO lines by constraints
     * @param int $erp
     * @param mixed string or array $a
     * @return array
     */
    public static function countInvoiceStatsByConstraints($erp, $a = NULL)
    {
        if (empty($erp)) {
            return;
        }
        // Construct constraints
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = " WHERE $WHERE";
        }
        $query = <<<EOF
SELECT
	CONVERT(DECIMAL(9,2),ROUND(SUM(T1.t_amtc)/1000,2)) AS total_invoiced_k
FROM
	ttdpur046$erp AS T1
	LEFT JOIN ttdpur040$erp AS T2 ON T1.t_orno=T2.t_orno
$WHERE
EOF;
        return tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     * Count invoiced PO lines by period by constraints
     * @param int $erp
     * @param date $start
     * @param date $end
     * @param mixed string or array $a
     * @return array
     */
    public static function countInvoiceStatsByPeriodInvoicedByConstraints($erp, $start, $end, $a = NULL)
    {
        if (empty($erp)) {
            return;
        }
        $WHERE = "T1.t_pdat BETWEEN '$start' AND '$end'";
        if (is_array($a)) {
            $WHERE .= ' AND ' . tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE .= " AND $a";
        }
        return self::countInvoiceStatsByConstraints($erp, $WHERE);
    }

    /**
     *
     * WARNING -> DEPRECADED
     * Pls create new function pointing to the by constraint method
     *
     */
    public static function byParent($erp, $id)
    {
        $query = <<<EOF
SELECT
    DISTINCT(T1.t_pono),
    T1.t_revi,
    RTRIM(T1.t_item) AS t_item,
    (SELECT TOP 1 T4.t_aitc FROM ttiitm012$erp AS T4
        WHERE T4.t_suno = T1.t_suno
        AND T4.t_citt = T1.t_citt
        AND T4.t_item = T1.t_item
    ) AS t_aitc,
    ITM.t_dsca, ITM.t_oltm,
    T1.t_pric, T1.t_cuqp, T1.t_oqua,T1.t_dqua,T1.t_bqua,
    case WHEN (T1.t_dqua=0) THEN T1.t_oqua-T1.t_dqua ELSE T1.t_bqua END tobedel,
    SUBSTRING(convert(varchar, T1.t_ddta, 120), 0, 11) AS t_ddta,
    SUBSTRING(convert(varchar, T1.t_ddtb, 120), 0, 11) AS t_ddtb,
    SUBSTRING(convert(varchar, T1.t_ddtd, 120), 0, 11) AS t_ddtd,
    SUBSTRING(convert(varchar, T1.t_odat, 120), 0, 11) AS t_odat,
    't_ddtc'=CASE WHEN(T1.t_ddtc <> '1753-01-01 00:00:00.000')
        THEN SUBSTRING(convert(varchar, T1.t_ddtc,120), 0, 11)
        ELSE NULL
    END,
    SUBSTRING(convert(varchar, tldpur.t_ddts, 120), 0, 11) AS t_ddts,
    SUBSTRING(convert(varchar, MRP.t_date, 120), 0, 11) AS t_resc,
    CASE MRP2.t_excm
        WHEN '1' THEN 'Order Qty. Too Small'
        WHEN '2' THEN 'Order Qty. Too Large'
        WHEN '3' THEN 'Order Qty. No Multiple'
        WHEN '4' THEN 'No Fixed Order Qty.'
        WHEN '5' THEN 'Order Date Early'
        WHEN '6' THEN 'Planned Start Early'
        WHEN '7' THEN 'Planned Finish Late'
        WHEN '8' THEN 'Supplier Unknown'
        WHEN '9' THEN 'Release Order'
        WHEN '10' THEN 'Release is Late'
        WHEN '11' THEN 'Blocked for Release'
        WHEN '12' THEN 'Cancel'
        WHEN '13' THEN 'RPT Sch. Period Not Found'
        ELSE ''
    END t_excm,
    'change'=CASE
        WHEN (SELECT COUNT(*) FROM ttiedm100400 AS EDM
        WHERE T1.t_item=EDM.t_eitm AND T1.t_odat>=EDM.t_indt AND EDM.t_exdt='1753-01-01')=0 THEN 'Y'
        ELSE ''
    END,
    'fai'=CASE WHEN ((RTRIM(T1.t_citg)='10180' OR T1.t_pric>250)
            AND (SELECT count(*)
                FROM ttdpur041$erp AS pur041
                WHERE
                    pur041.t_odat > DATEADD(YY, -2, GETDATE())
            AND pur041.t_suno = T1.t_suno
            AND pur041.t_item = T1.t_item)<2)
        THEN 'Y'
        ELSE ''
    END,
    'prchange'=CASE WHEN T1.t_pric>ITM.t_ltpr
            THEN 'Y'
            ELSE ''
    END,
    'su_replied'=CASE WHEN T1.t_ddtc<>'1753-01-01 00:00:00.000'
        THEN 'Y'
        ELSE 'N'
    END,
    T1.t_pric,
    ITM.t_ltpr,
    CONVERT(VARCHAR(100), CAST(ITM.t_copr AS DECIMAL(15,2))) AS t_copr,
    RTRIM(ITM.t_csig) AS t_csig,
    'isSignalCodePurBlocked' = CASE WHEN
        (SELECT SCODE.t_blcp FROM ttcmcs018300 AS SCODE WHERE SCODE.t_csig=ITM.t_csig)=2
        THEN 'Y'
        ELSE 'N'
    END,
    CONVERT(VARCHAR(100), CAST(T1.t_amta AS DECIMAL(15,2))) AS t_amta,
    'discount' = CASE
        WHEN T1.t_ldam_1<>0 THEN T1.t_ldam_1
        WHEN T1.t_disc_1<>0 THEN T1.t_disc_1
        ELSE 0
    END,
    T3.t_cotp as t_cotp,
    CONVERT(VARCHAR(100), CAST(ITM.t_prip AS DECIMAL(15,4))) AS t_prip,
    SFC001.t_mitm,
    (SELECT ITM001.t_dsca FROM ttiitm001$erp AS ITM001 WHERE ITM001.t_item=SFC001.t_mitm) AS t_dsca2,
    (SELECT SUBSTRING(convert(varchar, edm100.t_indt, 120), 0, 11) FROM ttiedm100$erp AS edm100 WHERE edm100.t_revi = T1.t_revi and T1.t_item = edm100.t_eitm) AS t_revdate,
    T1.t_pdno,
    TXT.t_text
FROM
	ttdpur045$erp AS T2
    LEFT JOIN ttdpur041$erp AS T1
        ON T1.t_orno=T2.t_orno	AND T1.t_pono=T2.t_pono
	LEFT JOIN ttdpur040$erp AS T3
        ON T1.t_orno=T3.t_orno
	LEFT JOIN dbo.ttimrp030$erp AS MRP
		ON T1.t_orno=MRP.t_orno AND T1.t_pono=MRP.t_pono
    LEFT JOIN dbo.ttimrp031$erp AS MRP2
        ON T1.t_orno=MRP2.t_orno AND T1.t_pono=MRP2.t_pono AND MRP2.t_koor=2
	LEFT JOIN tld..ttdpur041$erp AS tldpur
		ON T1.t_orno=tldpur.t_orno AND T1.t_pono=tldpur.t_pono
	LEFT JOIN dbo.ttiitm001$erp AS ITM
        ON T1.t_item=ITM.t_item
    LEFT JOIN dbo.ttttxt010$erp as TXT
    	ON TXT.t_ctxt=T1.t_txta
		    
    LEFT JOIN dbo.ttisfc001$erp AS SFC001
        ON T1.t_pdno=SFC001.t_pdno
WHERE
	T2.t_srnb=(SELECT MAX(T3.t_srnb) FROM ttdpur045$erp AS T3
        WHERE T2.t_orno=T3.t_orno AND T2.t_pono=T3.t_pono)
	AND (T2.t_spur<9 OR (T2.t_spur=9 AND T2.t_bqua>0))
	AND T2.t_orno='$id' 
    AND (TXT.t_seqe IS NULL OR TXT.t_seqe = 1)
ORDER BY
	T1.t_pono
EOF;

        $opt2 = ['src' => 'baan'];
        return tldUtils::getSqlToAssocArray($query, 'odbc', $opt2);
    }

    public static function checkPNVendor($pn, $vendorid, $erp)
    {
        $query = <<<EOF
			SELECT
				RTRIM(T1.t_item) AS t_item,
				RTRIM(T1.t_suno) AS t_suno
			FROM ttdpur041$erp AS T1
			WHERE t_item = '$pn' AND t_suno = '$vendorid'
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     * Get PO Lines by erp
     *
     * WARNING -> DEPRECADED
     * Pls create new function pointing to the by constraint method
     *
     * @param array $options
     * @return array
     */
    public static function byERP($erp, $options = '')
    {
        $sortField = empty($options['sortField']) ? 't_ddtb' : $options['sortField'];
        $sortOrder = empty($options['sortOrder']) ? 'ASC' : $options['sortOrder'];
        if (isset($options['inList'])) {
            $WHERE = ' AND T1.t_orno IN (' . implode(',', $options['inList']) . ')';
        }
        switch (tldERP::whatERP($erp)) {
            case 'baan':
                $query = <<<EOF
SELECT
    DISTINCT(T1.t_pono) AS t_pono,
    T1.t_revi,
    T1.t_orno AS t_orno,
    RTRIM(T1.t_item) AS t_item,
    ITM.t_dsca AS t_dsca,
    (SELECT T4.t_aitc
    FROM ttiitm012$erp AS T4
    WHERE T4.t_suno = T1.t_suno
    AND T4.t_citt = T1.t_citt
    AND T4.t_item = T1.t_item) AS t_aitc,
    T1.t_pric AS t_pric, T1.t_cuqp AS t_cuqp, T1.t_oqua AS t_oqua,
    T1.t_dqua AS t_dqua, T1.t_bqua AS t_bqua,
    SUBSTRING(convert(varchar,T1.t_ddta,120), 0, 11) AS t_ddta,
    SUBSTRING(convert(varchar,T1.t_ddtb,120), 0, 11) AS t_ddtb,
    SUBSTRING(convert(varchar,T1.t_odat,120), 0, 11) AS t_odat,
    SUBSTRING(convert(varchar,T1.t_ddtc,120), 0, 11) AS t_ddtc,
    SUBSTRING(convert(varchar, tldpur.t_ddts, 120), 0, 11) AS t_ddts,
    CONVERT(varchar(255), tldpur.id) AS tldpolid,
    SUBSTRING(convert(varchar,MRP.t_date,120), 0, 11) AS t_resc,
    'late'=CASE
    WHEN (T1.t_ddtc <>'1753-01-01' AND T1.t_ddtc < GETDATE()) THEN 'Y'
    ELSE ''
    END,
    'change'=CASE
    WHEN (EDM.t_exdt <>'1753-01-01') THEN 'Y'
    ELSE ''
    END,
    'fai'=CASE WHEN ((RTRIM(T1.t_citg)='10180' OR T1.t_pric>250)
    AND(SELECT count(*)
    FROM ttdpur041$erp AS pur041
    WHERE
    pur041.t_odat > DATEADD(YY,-2, GETDATE())
    AND pur041.t_suno=T1.t_suno
    AND pur041.t_item=T1.t_item)<2)
    THEN 'Y'
    ELSE ''
    END,
    'prchange'=CASE WHEN T1.t_pric>ITM.t_ltpr
    THEN 'Y'
    ELSE ''
    END,
    T1.t_pric,
    ITM.t_ltpr,
    CONVERT(VARCHAR(100), CAST(ITM.t_copr AS DECIMAL(15,2))) AS t_copr,
    (	SELECT CASE WHEN SUM(T4.t_amta) <5000 THEN '0'
    WHEN SUM(T4.t_amta) >=5000 AND SUM(T4.t_amta) <20000 THEN '1'
    WHEN SUM(T4.t_amta) >=20000 AND SUM(T4.t_amta) <100000 THEN '2'
    ELSE '3'
    END
    FROM ttdpur041400 AS T4
    WHERE T4.t_orno=T1.t_orno
    ) AS po_level,
    CONVERT(VARCHAR(100), CAST(T1.t_amta AS DECIMAL(15,2))) AS t_amta,
    'discount' = CASE
        WHEN T1.t_ldam_1<>0 THEN T1.t_ldam_1
        WHEN T1.t_disc_1<>0 THEN T1.t_disc_1
        ELSE 0
    END,
    T3.t_cotp as t_cotp
FROM ttdpur041$erp AS T1
    LEFT JOIN ttdpur040$erp AS T3
        ON T1.t_orno=T3.t_orno
    LEFT JOIN ttimrp030$erp AS MRP
        ON T1.t_orno=MRP.t_orno AND T1.t_pono=MRP.t_pono
    LEFT JOIN tld..ttdpur041 AS tldpur
        ON tldpur.erp=$erp AND T1.t_orno=tldpur.t_orno AND T1.t_pono=tldpur.t_pono
    LEFT JOIN ttiedm100400 AS EDM
        ON (T1.t_item=EDM.t_eitm AND T1.t_odat>=EDM.t_indt AND (T1.t_odat < EDM.t_exdt OR EDM.t_exdt='1753-01-01')),
    ttdpur045$erp AS T2, ttiitm001$erp AS ITM
WHERE T1.t_item=ITM.t_item
    AND T1.t_orno=T2.t_orno	AND T1.t_pono=T2.t_pono
    AND T2.t_srnb=(	select MAX(T3.t_srnb)
        FROM ttdpur045$erp AS T3
        WHERE T2.t_orno=T3.t_orno AND T2.t_pono=T3.t_pono)
    AND (T2.t_spur<9 OR (T2.t_spur=9 AND T2.t_bqua>0)) AND T1.t_oqua>T1.t_dqua
    $WHERE
ORDER BY $sortField $sortOrder
EOF;
                //			ORDER BY T1.t_orno, T1.t_pono

                $opt2 = ['src' => 'baan'];
                break;
            default:
                return;
        }
        return tldUtils::getSqlToAssocArray($query, 'odbc', $opt2);
    }

    /**
     * Get PO lines by vendor and erp
     *
     * WARNING -> DEPRECADED
     * Pls create new function pointing to the by constraint method
     *
     * @param integer $vendorid
     * @param integer $erp
     * @param array $options
     * @return array
     */
    public static function byVendorERP($erp, $vendorid, $options = '')
    {
        $sortField = empty($options['sortField']) ? 't_ddtb' : $options['sortField'];
        $sortOrder = empty($options['sortOrder']) ? 'ASC' : $options['sortOrder'];
        switch ($erp) {
            case '520':
            case '540':
                $edmerp = 500;
                break;
            default:
                $edmerp = $erp;
        }
        switch (tldERP::whatERP($erp)) {
            case 'baan':
                switch ($options['mode']) {
                    case 'byLate':
                        $WHERE = " AND (T2.t_spur<9 OR (T2.t_spur=9 AND T2.t_bqua>0)) AND T1.t_oqua>T1.t_dqua AND (T1.t_ddtc <>'1753-01-01' AND T1.t_ddtb < GETDATE())";
                        break;
                    case 'byUnconfirmed':
                        $WHERE = " AND (T2.t_spur<9 OR (T2.t_spur=9 AND T2.t_bqua>0)) AND T1.t_oqua>T1.t_dqua AND T1.t_ddtc='1753-01-01 00:00:00.000'";
                        break;
                    case 'within_seven_days':
                        $WHERE = ' AND (T2.t_spur<9 OR (T2.t_spur=9 AND T2.t_bqua>0)) AND T1.t_oqua>T1.t_dqua AND DATEDIFF(day, getdate(), T1.t_ddtb) < 7';
                        break;
                    case 'within_thirty_days':
                        $WHERE = ' AND (T2.t_spur<9 OR (T2.t_spur=9 AND T2.t_bqua>0)) AND T1.t_oqua>T1.t_dqua AND DATEDIFF(day, getdate(), T1.t_ddtb) < 30';
                        break;
                    case 'within_nienty_days':
                        $WHERE = ' AND (T2.t_spur<9 OR (T2.t_spur=9 AND T2.t_bqua>0)) AND T1.t_oqua>T1.t_dqua AND DATEDIFF(day, getdate(), T1.t_ddtb) < 90';
                        break;
                    case 'openLineItems':
                        $WHERE = ' AND T1.t_oqua>T1.t_dqua AND (T2.t_spur<9 OR (T2.t_spur=9 AND T2.t_bqua>0))';
                        break;
                    case 'closedLineItems':
                        $WHERE = ' AND (T2.t_spur=9 AND T2.t_bqua=0)';
                        break;
                    case 'toInvoice':
                        $WHERE = " AND (SELECT SUM(t2.t_iqan) FROM ttdpur045$erp as t2
					WHERE T2.t_orno=t2.t_orno AND T2.t_pono=t2.t_pono)<T1.t_dqua";
                        break;
                }
                $query = <<<EOF
SELECT
    DISTINCT(T1.t_pono) AS t_pono,
    T1.t_revi, T1.t_quap AS t_quap,
    T1.t_orno AS t_orno,
    ITM.t_eitm,ITM.t_wght,
    RTRIM(T1.t_item) AS t_item,
    (SELECT TOP 1 T4.t_aitc
    FROM ttiitm012$erp AS T4
    WHERE T4.t_suno = T1.t_suno
    AND T4.t_citt = T1.t_citt
    AND T4.t_item = T1.t_item) AS t_aitc,
    ITM.t_dsca AS t_dsca, ITM.t_oltm,
    T1.t_pric AS t_pric, T1.t_cuqp AS t_cuqp, T1.t_oqua AS t_oqua, replace(T3.t_refb,'SO','') AS t_refb,
    T1.t_dqua AS t_dqua, T1.t_bqua AS t_bqua, 'tobedel'=CASE WHEN (T1.t_dqua=0) THEN (T1.t_oqua-T1.t_dqua) ELSE T1.t_bqua END,
    SUBSTRING(convert(varchar,T1.t_ddta,120), 0, 11) AS t_ddta,
    SUBSTRING(convert(varchar,T1.t_ddtb,120), 0, 11) AS t_ddtb,
    SUBSTRING(convert(varchar,T1.t_odat,120), 0, 11) AS t_odat,
    SUBSTRING(convert(varchar,MRP.t_date,120), 0, 11) AS t_resc,
    (SELECT SUBSTRING(convert(varchar, edm100.t_indt, 120), 0, 11) FROM ttiedm100$erp AS edm100 WHERE edm100.t_revi = T1.t_revi and T1.t_item = edm100.t_eitm) AS t_revdate,
    't_ddts' = CASE
        WHEN (SUBSTRING(convert(varchar, tldpur.t_ddts, 120), 0, 11)<>'1753-01-01')
        THEN SUBSTRING(convert(varchar, tldpur.t_ddts, 120), 0, 11)
        ELSE ''
    END,
    'will_be_late'=
    CASE WHEN
        CASE
        WHEN (T1.t_ddtc<>'1753-01-01') THEN SUBSTRING(convert(varchar, T1.t_ddtc, 120), 0, 11)
        ELSE t_ddta
        END >
        CASE
        WHEN (MRP.t_date<>'1753-01-01') THEN SUBSTRING(convert(varchar, MRP.t_date, 120), 0, 11)
        ELSE t_ddta
        END
    THEN 'Y'
    ELSE ''
    END,
    't_ddtc'=CASE
    WHEN (T1.t_ddtc<>'1753-01-01') THEN SUBSTRING(convert(varchar, T1.t_ddtc, 120), 0, 11)
    ELSE ''
    END,
    'days_to_del' =CASE
    WHEN (T2.t_spur=9 AND T2.t_bqua=0) THEN DATEDIFF(day, T1.t_ddtb, SUBSTRING(convert(varchar, T2.t_date, 120), 0, 11))
    ELSE DATEDIFF(day, getdate(), T1.t_ddtb)
    END,
    'rcv_date' =CASE
    WHEN (T2.t_date<>'1753-01-01') THEN SUBSTRING(convert(varchar, T2.t_date, 120), 0, 11)
    ELSE ''
    END,
    'late'=CASE
    WHEN (T1.t_ddtc <>'1753-01-01' AND T1.t_ddtb < GETDATE()) THEN 'Y'
    ELSE ''
    END,
    'change'=CASE
    WHEN (EDM.t_exdt <>'1753-01-01') THEN 'Y'
    ELSE ''
    END,
    (select TOP 1 t_revi from ttiedm100$erp where t_eitm = T1.t_item order by t_indt DESC) AS 'rev',
    'fai'=CASE WHEN ((RTRIM(T1.t_citg)='10180' OR T1.t_pric>250)
        AND (SELECT count(*)
        FROM ttdpur041$erp AS pur041
        WHERE
        pur041.t_odat > DATEADD(YY,-2, GETDATE())
        AND pur041.t_suno=T1.t_suno
        AND pur041.t_item=T1.t_item)<2)
    THEN 'Y'
    ELSE ''
    END,
    'prchange'=CASE WHEN T1.t_pric>ITM.t_ltpr
    THEN 'Y'
    ELSE ''
    END,
    'su_replied'=CASE WHEN T1.t_ddtc<>'1753-01-01 00:00:00.000'
    THEN 'Y'
    ELSE 'N'
    END,
    T1.t_pric,
    ITM.t_ltpr,
    CONVERT(VARCHAR(100), CAST(ITM.t_copr AS DECIMAL(15,2))) AS t_copr,
    CONVERT(VARCHAR(100), CAST(T1.t_amta AS DECIMAL(15,2))) AS t_amta,
    'discount' = CASE
        WHEN T1.t_ldam_1<>0 THEN T1.t_ldam_1
        WHEN T1.t_disc_1<>0 THEN T1.t_disc_1
        ELSE 0
    END,
    RTRIM(TXT.t_text) as t_text,
    T3.t_cotp as t_cotp
FROM ttdpur041$erp AS T1
    LEFT JOIN ttttxt010$erp as TXT ON TXT.t_ctxt=T1.t_txta
    LEFT JOIN ttdpur040$erp AS T3 ON T1.t_orno=T3.t_orno
    LEFT JOIN ttimrp030$erp AS MRP ON T1.t_orno=MRP.t_orno AND T1.t_pono=MRP.t_pono
    LEFT JOIN tld..ttdpur041$erp AS tldpur ON T1.t_orno=tldpur.t_orno AND T1.t_pono=tldpur.t_pono
    LEFT JOIN ttiedm100400 AS EDM ON (T1.t_item=EDM.t_eitm AND T1.t_odat>=EDM.t_indt
        AND (T1.t_odat < EDM.t_exdt OR EDM.t_exdt='1753-01-01') )
    LEFT JOIN ttdpur045$erp AS T2 ON T1.t_orno=T2.t_orno AND T1.t_pono=T2.t_pono,
    ttiitm001$erp AS ITM
WHERE
    T1.t_item=ITM.t_item
    AND T2.t_srnb=(	SELECT MAX(T3.t_srnb) FROM ttdpur045$erp AS T3
        WHERE T2.t_orno=T3.t_orno AND T2.t_pono=T3.t_pono)
    AND T1.t_suno='$vendorid'
$WHERE
ORDER BY
    $sortField $sortOrder
EOF;
                $opt2 = ['src' => 'baan'];
                break;
            default:
                return [];
        }

        return tldUtils::getSqlToAssocArray($query, 'odbc', $opt2);
    }

    /**
     * Function to get FROM sql
     * @param int erp
     * @return string
     */
    public static function getSELECT($erp)
    {
        $reviDate = new DateTime(date('Y-m-d'));
        $reviDate2 = $reviDate->format('Y-m-d');
        return <<<EOF
SELECT
    T1.t_suno AS t_suno,
    SUBSTRING(convert(varchar, T1.t_ddta, 120), 0, 11) AS t_ddta,
    T1.t_orno AS t_orno,
    T1.t_pono AS t_pono,
    RTRIM(T1.t_item) AS t_item,       
    SUP.t_nama AS t_nama,
    T1.t_cvat AS t_cvat,
    (SELECT RTRIM(t6.t_info) FROM ttccom001$erp AS t6
        WHERE t6.t_emno=T3.t_ccon
    ) AS byr_email,
    ITM.t_ctyo,
    T1.t_revi,
    SUBSTRING(convert(varchar,DateAdd(DD,(round(ITM.t_oltm/5,0)*2+ITM.t_oltm),T1.t_odat), 120), 0, 11) AS th_date,
    (SELECT TOP 1 T4.t_aitc FROM ttiitm012$erp AS T4
        WHERE T4.t_suno = T1.t_suno AND T4.t_citt = T1.t_citt AND T4.t_item = T1.t_item
    ) AS t_aitc,
    'inspect'=CASE WHEN  T1.t_qual=1
        THEN 'Y'
        ELSE 'N'
    END,
    ITM.t_txtp,ITM.t_dsca, ITM.t_oltm, T1.t_cuqp, T1.t_oqua, T1.t_dqua, T1.t_bqua,
    CONVERT(VARCHAR(100), CAST(T1.t_amta AS DECIMAL(15,2))) AS t_amta,
    'discount' = CASE
        WHEN T1.t_ldam_1<>0 THEN T1.t_ldam_1
        WHEN T1.t_disc_1<>0 THEN T1.t_disc_1
        ELSE 0
    END,
    CONVERT(VARCHAR(100), CAST(T1.t_pric AS DECIMAL(15,2))) AS t_pric,
    CONVERT(VARCHAR(100), CAST(ITM.t_copr AS DECIMAL(15,2))) AS t_copr,
    't_ddta'=CASE WHEN(T1.t_ddta <> '1753-01-01 00:00:00.000')
        THEN SUBSTRING(convert(varchar, T1.t_ddta,120), 0, 11)
        ELSE NULL
    END,
    't_ddtb'=CASE WHEN(T1.t_ddtb <> '1753-01-01 00:00:00.000')
        THEN SUBSTRING(convert(varchar, T1.t_ddtb,120), 0, 11)
        ELSE NULL
    END,
    't_ddtd'=CASE WHEN(T1.t_ddtd <> '1753-01-01 00:00:00.000')
        THEN SUBSTRING(convert(varchar, T1.t_ddtd,120), 0, 11)
        ELSE NULL
    END,
    SUBSTRING(convert(varchar, T1.t_odat, 120), 0, 11) AS t_odat,
    't_ddtc'=CASE WHEN(T1.t_ddtc <> '1753-01-01 00:00:00.000')
        THEN SUBSTRING(convert(varchar, T1.t_ddtc,120), 0, 11)
        ELSE NULL
    END,
    'late_resc' = CASE WHEN(T1.t_ddtc <> '1753-01-01 00:00:00.000')
        THEN DATEDIFF(DAY, MRP.t_date, T1.t_ddtc)
        ELSE DATEDIFF(DAY, MRP.t_date, GETDATE())
    END,
    SUBSTRING(convert(varchar, tldpur.t_ddts, 120), 0, 11) AS t_ddts,
    SUBSTRING(convert(varchar, MRP.t_date, 120), 0, 11) AS t_resc,
    't_resm'=CASE WHEN MRP.t_resm=3 or MRP2.t_excm=12 THEN 'Cancel'
    		ELSE
    	  		CASE MRP.t_resm
    	  			WHEN 1 THEN 'Reschedule-in'
    	  			WHEN 2 THEN 'Reschedule-out'
    	  		END
    END,
    'change'=CASE
        WHEN (SELECT COUNT(*) FROM ttiedm100400 AS EDM
        WHERE T1.t_item=EDM.t_eitm AND T1.t_odat>=EDM.t_indt AND EDM.t_exdt='1753-01-01')=0 THEN 'Y'
        ELSE ''
    END,
    'fai'=CASE WHEN ((RTRIM(T1.t_citg)='10180' OR T1.t_pric>250)
            AND (SELECT count(*)
                FROM ttdpur041$erp AS pur041
                WHERE
                    pur041.t_odat > DATEADD(YY, -2, GETDATE())
            AND pur041.t_suno = T1.t_suno
            AND pur041.t_item = T1.t_item)<2)
        THEN 'Y'
        ELSE ''
    END,
    'prchange'=CASE WHEN T1.t_pric>ITM.t_ltpr
            THEN 'Y'
            ELSE ''
    END,
    'su_replied'=CASE WHEN T1.t_ddtc<>'1753-01-01 00:00:00.000'
        THEN 'Y'
        ELSE 'N'
    END,
    ITM.t_ltpr as t_ltpr,
    T3.t_cotp as t_cotp,
    ITM.t_csgp as t_csgp,
    SUP.t_cbrn as t_cbrn,
    EMP.t_namb as t_namb,
    T1.t_pric as t_pric,
    T3.t_ccur as t_ccur,
    (select max(EDM100.t_revi) from ttiedm100400 AS EDM100 where EDM100.t_eitm=T1.t_item and EDM100.t_indt<='$reviDate2' and (EDM100.t_exdt>='$reviDate2' or EDM100.t_exdt='01/01/1753')) AS t_rev2,
    CONVERT(VARCHAR(100), CAST(ITM.t_prip AS DECIMAL(15,2))) AS t_prip,
    TXT.t_text

EOF;
    }

    /**
     * Function to get FROM sql
     * @param int erp
     * @return string
     */
    public static function getFROM($erp)
    {
        return <<<EOF
FROM ttdpur041$erp AS T1
    LEFT JOIN dbo.ttimrp030$erp AS MRP
        ON T1.t_orno=MRP.t_orno AND T1.t_pono=MRP.t_pono AND MRP.t_koor=2
    LEFT JOIN dbo.ttimrp031$erp AS MRP2
        ON T1.t_orno=MRP2.t_orno AND T1.t_pono=MRP2.t_pono AND MRP2.t_koor=2
    LEFT JOIN tld..ttdpur041$erp AS tldpur
        ON T1.t_orno=tldpur.t_orno AND T1.t_pono=tldpur.t_pono
    LEFT JOIN dbo.ttiitm001$erp AS ITM
        ON ITM.t_item=T1.t_item
    LEFT JOIN ttdpur040$erp AS T3
        ON T1.t_orno=T3.t_orno
    LEFT JOIN ttccom020$erp AS SUP
        ON T1.t_suno=SUP.t_suno
    LEFT JOIN ttccom001$erp as EMP
    	ON T3.t_ccon=EMP.t_emno
    LEFT JOIN ttttxt010$erp as TXT
    	ON TXT.t_ctxt=T1.t_txta

EOF;
    }

    public static function byConstraints($erp, $a)
    {
        if (empty($erp) || empty($a)) {
            return 'Empty parameter';
        }
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        // Construct query
        $SELECT = self::getSELECT($erp);
        $FROM = self::getFROM($erp);
        $reliabiltyLowThreshold = self::RELIABILITY_LOW_THRESHOLD;
        $reliabiltyHighThreshold = self::RELIABILITY_HIGH_THRESHOLD;
        $query = <<<SQL
$SELECT,
    SUBSTRING(convert(varchar, T2.t_date, 120), 0, 11) AS t_date,
    T2.t_dqua AS receiptqua,
    'late_days'=(CASE WHEN (T1.t_ddtc <> '1753-01-01 00:00:00.000')
    	  THEN DATEDIFF(DAY, T1.t_ddtc, T2.t_date)
    	  ELSE DATEDIFF(DAY, T1.t_ddta, T2.t_date)
    END),
    IIF(DATEDIFF(DAY, IIF(T1.t_ddtd != '1753-01-01', T1.t_ddtd, IIF(T1.t_ddtc != '1753-01-01', T1.t_ddtc, T1.t_ddta)), T2.t_date) BETWEEN $reliabiltyLowThreshold AND $reliabiltyHighThreshold, 'Y', 'N') AS reliability,
    (SELECT CASE WHEN SUM(T3.t_qana) IS NULL THEN 0 ELSE SUM(T3.t_qana) END
    	FROM ttdpur046$erp AS T3
    	WHERE T3.t_orno=T1.t_orno AND T3.t_pono=T1.t_pono
    ) AS 'qty_matched'
$FROM
    LEFT JOIN ttdpur045$erp AS T2
    	ON T2.t_orno=T1.t_orno AND T2.t_pono=T1.t_pono
WHERE
 (TXT.t_seqe IS NULL OR TXT.t_seqe = 1) AND 
	$WHERE
ORDER BY
    T2.t_date DESC
SQL;

        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public static function byOpenByConstraints($erp, $a = NULL)
    {
        if (empty($erp)) {
            return [];
        }
        $WHERE = <<<EOF
T2.t_srnb=(SELECT MAX(T3.t_srnb) FROM ttdpur045$erp AS T3
	WHERE T2.t_orno=T3.t_orno AND T2.t_pono=T3.t_pono)
AND (T2.t_spur<9 OR (T2.t_spur=9 AND T2.t_bqua>0))
AND T1.t_oqua>T1.t_dqua
EOF;
        if (is_array($a)) {
            $WHERE .= ' AND ' . tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE .= " AND $a";
        }
        return self::byConstraints($erp, $WHERE);
    }

    public static function byOpenByLateVsConfDateByConstraints($erp, $a = NULL)
    {
        $WHERE = <<<EOF
(MRP.t_date IS NOT NULL AND MRP.t_date < T1.t_ddtc AND T1.t_ddtc!='1753-01-01 00:00:00.000')
EOF;
        if (is_array($a)) {
            $WHERE .= ' AND ' . tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE .= " AND $a";
        }
        return self::byOpenByConstraints($erp, $WHERE);
    }

    public static function byOpenByLateVsConfDateByBuyerEmail($erp, $email)
    {
        $WHERE = <<<EOF
(SELECT RTRIM(t6.t_info) FROM ttccom001$erp AS t6 WHERE t6.t_emno=T3.t_ccon)='$email'
EOF;
        return self::byOpenByLateVsConfDateByConstraints($erp, $WHERE);
    }

    public static function byOpenByLateVsConfDateBySuno($erp, $suno)
    {
        $WHERE = "T3.t_suno='$suno'";
        return self::byOpenByLateVsConfDateByConstraints($erp, $WHERE);
    }

    public static function byOpenByLateVsConfDateByItem($erp, $item)
    {
        $WHERE = "T1.t_item='$item'";
        return self::byOpenByLateVsConfDateByConstraints($erp, $WHERE);
    }

    public static function byOpenByLateByConstraints($erp, $a = NULL)
    {
        $WHERE = " (T1.t_ddtc <>'1753-01-01' AND T1.t_ddtc<GETDATE()) ";
        if (is_array($a)) {
            $WHERE .= ' AND ' . tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE .= " AND $a";
        }
        return self::byOpenByConstraints($erp, $WHERE);
    }

    public static function byOpenByLateByBuyerEmail($erp, $email)
    {
        $WHERE = <<<EOF
(SELECT RTRIM(t6.t_info) FROM ttccom001$erp AS t6 WHERE t6.t_emno=T3.t_ccon)='$email'
EOF;
        return self::byOpenByLateByConstraints($erp, $WHERE);
    }

    public static function byOpenByLateBySuno($erp, $suno)
    {
        $WHERE = "T3.t_suno='$suno'";
        return self::byOpenByLateByConstraints($erp, $WHERE);
    }

    public static function byOpenByLateByItem($erp, $item)
    {
        $WHERE = "T1.t_item='$item'";
        return self::byOpenByLateByConstraints($erp, $WHERE);
    }

    public static function byOpenByUnconfirmedByConstraints($erp, $a = NULL)
    {
        $WHERE = " T1.t_ddtc='1753-01-01 00:00:00.000' ";
        if (is_array($a)) {
            $WHERE .= ' AND ' . tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE .= " AND $a";
        }
        return self::byOpenByConstraints($erp, $WHERE);
    }

    public static function byOpenByUnconfirmedByBuyerEmail($erp, $email)
    {
        $WHERE = <<<EOF
(SELECT RTRIM(t6.t_info) FROM ttccom001$erp AS t6 WHERE t6.t_emno=T3.t_ccon)='$email'
EOF;
        return self::byOpenByUnconfirmedByConstraints($erp, $WHERE);
    }

    public static function byOpenByUnconfirmedBySuno($erp, $suno)
    {
        $WHERE = "T3.t_suno='$suno'";
        return self::byOpenByUnconfirmedByConstraints($erp, $WHERE);
    }

    public static function byOpenByUnconfirmedByItem($erp, $item)
    {
        $WHERE = "T1.t_item='$item'";
        return self::byOpenByUnconfirmedByConstraints($erp, $WHERE);
    }

    public static function byOpenByWithinNbDaysByConstraints($erp, $days, $a = NULL)
    {
        $WHERE = " DATEDIFF(DAY, GETDATE(), T1.t_ddtb) < $days ";
        if (is_array($a)) {
            $WHERE .= ' AND ' . tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE .= " AND $a";
        }
        return self::byOpenByConstraints($erp, $WHERE);
    }

    public static function byOpenByWithinNbDaysByBuyerEmail($erp, $days, $email)
    {
        $WHERE = <<<EOF
(SELECT RTRIM(t6.t_info) FROM ttccom001$erp AS t6 WHERE t6.t_emno=T3.t_ccon)='$email'
EOF;
        return self::byOpenByWithinNbDaysByConstraints($erp, $days, $WHERE);
    }

    public static function byOpenByWithinNbDaysBySuno($erp, $days, $suno)
    {
        $WHERE = "T3.t_suno='$suno'";
        return self::byOpenByWithinNbDaysByConstraints($erp, $days, $WHERE);
    }

    public static function byOpenByWithinNbDaysByItem($erp, $days, $item)
    {
        $WHERE = "T1.t_item='$item'";
        return self::byOpenByWithinNbDaysByConstraints($erp, $days, $WHERE);
    }

    public static function byParentShipped($erp, $id)
    {
        if (!is_numeric($id) || !is_numeric($erp)) {
            return;
        }
        $a = <<<EOF
T2.t_srnb=(
	SELECT MAX(T3.t_srnb) FROM ttdpur045$erp AS T3
	WHERE T2.t_orno=T3.t_orno AND T2.t_pono=T3.t_pono
	AND T2.t_spur=9 AND T2.t_bqua=0
)
AND T2.t_orno=$id
EOF;
        return self::byConstraints($erp, $a);
    }

    /**
     * Get list of OPEN MATCH po lines
     *
     * OPEN MATCH definition:
     * Qty received not completly matched
     *
     * @param int $erp
     * @param mixed array/string $a
     * @return array
     */
    public static function byOpenMatchByConstraints($erp, $a)
    {
        if (empty($erp) || !is_numeric($erp)) {
            return;
        }
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = " AND $WHERE";
        }
        // Construct query
        $SELECT = self::getSELECT($erp);
        $FROM = self::getFROM($erp);
        $query = <<<EOF
$SELECT,
    (SELECT CASE WHEN SUM(T3.t_qana) IS NULL THEN 0 ELSE SUM(T3.t_qana) END
        FROM ttdpur046$erp AS T3
        WHERE T3.t_orno=T1.t_orno AND T3.t_pono=T1.t_pono
    ) AS 'qty_matched'
$FROM
WHERE
    T1.t_dqua-(SELECT CASE WHEN SUM(T3.t_qana) IS NULL THEN 0 ELSE SUM(T3.t_qana) END
        FROM ttdpur046$erp AS T3 WHERE T3.t_orno=T1.t_orno AND T3.t_pono=T1.t_pono
    ) > 0
    $WHERE
ORDER BY
    T1.t_suno,
    T1.t_orno,
    T1.t_pono
EOF;

        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public static function byParentByOpenMatch($erp, $id)
    {
        if (!is_numeric($id) || !is_numeric($erp)) {
            return;
        }
        $a = "T1.t_orno=$id";
        return self::byOpenMatchByConstraints($erp, $a);
    }

    /**
     * Get list of PO lines received on a specific period by constraints
     * @param integer $erp
     * @param date $start
     * @param date $end
     * @param mixed array or string $a
     * @return array
     */
    public static function byReceiptsPeriodByConstraints($erp, $start, $end, $a = NULL)
    {
        if (empty($erp)) {
            return;
        }
        $WHERE = <<<EOF
T2.t_date BETWEEN '$start' AND '$end'
AND T2.t_cwar<>'   ' AND T2.t_srnb<>0
EOF;
        if (is_array($a)) {
            $WHERE .= ' AND ' . tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE .= " AND $a";
        }
        return self::byConstraints($erp, $WHERE);
    }

    public static function miniReceipts24Month($erp, $pn)
    {
        $query = <<<EOF
SELECT
min(T1.t_pric) as minipric
FROM ttdpur041$erp AS T1
LEFT JOIN ttdpur045$erp AS T2
ON T2.t_orno=T1.t_orno AND T2.t_pono=T1.t_pono
WHERE
T2.t_date>DATEADD(month, -24, GETDATE())
AND T2.t_cwar<>'' AND T2.t_srnb<>0 and T1.t_item='$pn'
EOF;
        return tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
    }


    public static function byReceiptsPeriodByBuyerEmail($erp, $start, $end, $email)
    {
        $WHERE = <<<EOF
(SELECT RTRIM(t6.t_info) FROM ttccom001$erp AS t6 WHERE t6.t_emno=T3.t_ccon)='$email'
EOF;
        return self::byReceiptsPeriodByConstraints($erp, $start, $end, $WHERE);
    }

    public static function byReceiptsPeriodBySuno($erp, $start, $end, $suno)
    {
        $WHERE = "T3.t_suno='$suno'";
        return self::byReceiptsPeriodByConstraints($erp, $start, $end, $WHERE);
    }

    public static function byReceiptsPeriodByItem($erp, $start, $end, $item)
    {
        $WHERE = "T1.t_item='$item'";
        return self::byReceiptsPeriodByConstraints($erp, $start, $end, $WHERE);
    }

    /**
     * PO Line item Confirmation KPI by factory
     *
     * @return array
     */
    public static function confKPIByERP($start, $end)
    {
        $erps = [
            220 => 'TLD PV', 250 => 'AERO Specialties', 300 => 'TLD AME',400 => 'TLD WIN', 410 => 'TLD WIM', 420 => 'TLD SHE',
            500 => 'TLD MTL', 520 => 'TLD STL', 540 => 'TLD EUR', 600 => 'TLD ASI', 620 => 'TLD GST',
            640 => 'TLD SHA', 660 => 'TLD WUX', 680 => 'TLD CHI',
        ];
        $rows = [];
        foreach ($erps as $erp => $factory) {
            $a[] = <<<EOF
(SELECT '$factory' AS erp,
    count(CASE WHEN T1.t_ddtc='1753-01-01' THEN NULL ELSE 1 END) as num_confirmed,
    count(*) AS num_lines_open
FROM ttdpur041$erp AS T1,
    ttdpur045$erp AS T2
WHERE T1.t_orno=T2.t_orno	
    AND T1.t_odat>'$start' AND T1.t_odat<'$end'
    AND T1.t_pono=T2.t_pono
    AND T2.t_srnb=(	select MAX(T3.t_srnb)
        FROM ttdpur045$erp AS T3
        WHERE T2.t_orno=T3.t_orno AND T2.t_pono=T3.t_pono)
    AND (T2.t_spur<9 OR (T2.t_spur=9 AND T2.t_bqua>0)) AND T1.t_oqua>T1.t_dqua
    
)   

EOF;
        }

        $rows = tldUtils::getSqlToAssocArray(implode(' UNION ', $a), 'odbc', ['src' => 'baan']);

        foreach ($rows as $row) {
            $row['pc_confirmed'] = round($row['num_confirmed'] / $row['num_lines_open'], 2) * 100;
            $result[] = $row;
        }
        return $result;
    }

    /**
     * PO Line item Confirmation KPI by vendor
     *
     * @return array
     */
    public static function confKPIByVendor($start, $end, $erp)
    {
        if (in_array($erp, [300, 400, 410, 420, 500, 520, 640, 660])) {
            $query = <<<EOF
SELECT T1.t_suno AS t_suno, SUP.t_nama AS t_nama,
    count(CASE WHEN T1.t_ddtc='1753-01-01' THEN NULL ELSE 1 END) as num_confirmed,
	count(*) AS num_lines_open,
    count(CASE WHEN (DATEDIFF(day,T1.t_odat,getdate()) < 7)
		THEN NULL
		ELSE 1
		END
	) AS num_lines_openinsevendays,
    count(CASE WHEN ((DATEDIFF(day,T1.t_odat,getdate()) > 6) AND T1.t_ddtc<>'1753-01-01 00:00:00.000')
		THEN 1
		ELSE NULL
		END
	) AS num_confirmedinsevendays
FROM ttdpur041$erp AS T1,
    ttdpur045$erp AS T2,
    ttccom020$erp AS SUP
WHERE T1.t_orno=T2.t_orno	AND T1.t_pono=T2.t_pono
    AND T2.t_srnb=(	select MAX(T3.t_srnb)
        FROM ttdpur045$erp AS T3
        WHERE T2.t_orno=T3.t_orno AND T2.t_pono=T3.t_pono)
    AND (T2.t_spur<9 OR (T2.t_spur=9 AND T2.t_bqua>0)) AND T1.t_oqua>T1.t_dqua
    AND T2.t_suno=SUP.t_suno AND T1.t_odat>'$start' AND T1.t_odat<'$end'
GROUP BY SUP.t_nama,T1.t_suno
EOF;
            $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
        } else {
            return;
        }

        if (count($rows) == 0) {
            return;
        }

        if (in_array($erp, [300, 400, 410, 420, 500, 520, 640, 660])) {
            $query1 = <<<EOF
SELECT SUP.t_nama AS t_nama,count(*) AS num_lines_closed
FROM ttdpur041$erp AS T1,
    ttdpur045$erp AS T2,
    ttccom020$erp AS SUP
WHERE T1.t_orno=T2.t_orno	AND T1.t_pono=T2.t_pono
    AND T2.t_srnb=(	select MAX(T3.t_srnb)
        FROM ttdpur045$erp AS T3
        WHERE T2.t_orno=T3.t_orno AND T2.t_pono=T3.t_pono)
    AND T2.t_spur=9 AND T2.t_bqua=0
    AND T2.t_suno=SUP.t_suno AND T1.t_odat>'$start' AND T1.t_odat<'$end'
GROUP BY SUP.t_nama
EOF;
            $rets = tldUtils::getSqlToAssocArray($query1, 'odbc', ['src' => 'baan']);
        } else {
            return;
        }

        foreach ($rows as $row) {
            $row['pc_confirmed'] = round($row['num_confirmed'] / $row['num_lines_open'], 2) * 100;
            if ($row['num_lines_openinsevendays']) {
                $row['pc_confirmedinsevendays'] = round($row['num_confirmedinsevendays'] / $row['num_lines_openinsevendays'], 2) * 100;
            } else {
                $row['pc_confirmedinsevendays'] = '';
            }
            foreach ($rets as $ret) {
                if ($row['t_nama'] === $ret['t_nama']) {
                    $row['pc_closed'] = $ret['num_lines_closed'];
                    $row['pc_confirmed_all'] = round($row['num_confirmed'] / ($row['num_lines_open'] + $ret['num_lines_closed']), 2) * 100;
                }
            }
            $result[] = $row;
        }
        return $result;
    }

    /**
     * PO Line item Confirmation KPI by buyer
     *
     * @return array
     */
    public function confKPIByBuyer($erp, $con)
    {
        if (in_array($erp, [400, 410, 420, 500, 520, 640, 660])) {
            $query = <<<EOF
SELECT
    RTRIM(BYR.t_info) AS byr_email,
    count(CASE WHEN T1.t_ddtc='1753-01-01' THEN NULL ELSE 1 END) as num_confirmed,
    count(*) AS num_lines_open
FROM
    ttdpur040$erp AS PO,
    ttdpur041$erp AS T1,
    ttdpur045$erp AS T2,
    ttccom001$erp AS BYR
WHERE
    PO.t_orno = T1.t_orno
    AND PO.t_ccon = BYR.t_emno
    AND T1.t_orno=T2.t_orno	AND T1.t_pono=T2.t_pono
    AND T2.t_srnb=(	select MAX(T3.t_srnb)
        FROM ttdpur045$erp AS T3
        WHERE T2.t_orno=T3.t_orno AND T2.t_pono=T3.t_pono)
    AND (T2.t_spur<9 OR (T2.t_spur=9 AND T2.t_bqua>0)) AND T1.t_oqua>T1.t_dqua
    AND $con
GROUP BY BYR.t_info
EOF;
            $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
        } else {
            return;
        }

        if (count($rows) == 0) {
            return;
        }
        if (in_array($erp, [400, 410, 420, 500, 520, 640, 660])) {
            $query1 = <<<EOF
SELECT
    RTRIM(BYR.t_info) AS byr_email,
    count(*) AS num_lines_closed
FROM
    ttdpur040$erp AS PO,
    ttdpur041$erp AS T1,
    ttdpur045$erp AS T2,
    ttccom001$erp AS BYR
WHERE
    PO.t_orno = T1.t_orno
    AND PO.t_ccon = BYR.t_emno
    AND T1.t_orno=T2.t_orno	AND T1.t_pono=T2.t_pono
    AND T2.t_srnb=(	select MAX(T3.t_srnb)
        FROM ttdpur045$erp AS T3
        WHERE T2.t_orno=T3.t_orno AND T2.t_pono=T3.t_pono)
    AND T2.t_spur=9 AND T2.t_bqua=0
    AND $con
GROUP BY BYR.t_info
EOF;
            $rets = tldUtils::getSqlToAssocArray($query1, 'odbc', ['src' => 'baan']);
        } else {
            return;
        }

        foreach ($rows as $row) {
            foreach ($rets as $ret) {
                if ($row['byr_email'] === $ret['byr_email']) {
                    $row['pc_closed'] = $ret['num_lines_closed'];
                    $row['pc_confirmed_all'] = round($row['num_confirmed'] / ($row['num_lines_open'] + $ret['num_lines_closed']), 2) * 100;
                }
            }
            $row['pc_confirmed'] = round($row['num_confirmed'] / $row['num_lines_open'], 2) * 100;
            $result[] = $row;
        }
        return $result;
    }


    /**
     * Get count of PO Cancellation lines by level by ERP
     *
     * @return array
     */
    public static function countCancellationByERPPddt()
    {
        $erps = [
            400 => 'TLD WIN', 410 => 'TLD WIM', 420 => 'TLD SHE',
            500 => 'TLD MTL', 520 => 'TLD STL', 540 => 'TLD EUR',
            640 => 'TLD SHA', 660 => 'TLD WUX',
        ];
        $rows = $a = [];
        foreach ($erps as $erp => $factory) {
            $a[] = <<<EOF
SELECT
    '$factory' as location,
    '$erp' as erp,
    (CASE
        WHEN (t1.t_pddt < getdate()) THEN 'Urgent'
        WHEN (t1.t_pddt < (getdate() + 7)) THEN 'Week'
        WHEN (t1.t_pddt < (getdate() + 28)) THEN 'Month'
        ELSE '>Month'
    END) as level,
    count(*) as num
FROM ttimrp031$erp as t1
    LEFT JOIN ttiitm001$erp as t2 ON t1.t_item = t2.t_item
WHERE
    t1.t_excm=12 AND t1.t_koor=2
GROUP BY
    (CASE
        WHEN (t1.t_pddt > 0) THEN '$erp'
        ELSE''
    END),
    (CASE
        WHEN (t1.t_pddt < getdate()) THEN 'Urgent'
        WHEN (t1.t_pddt < (getdate() + 7)) THEN 'Week'
        WHEN (t1.t_pddt < (getdate() + 28)) THEN 'Month'
        ELSE '>Month'
    END)
EOF;
        }
        $rows = tldUtils::getSqlToAssocArray(implode(' UNION ', $a), 'odbc', ['src' => 'baan']);
        return $rows;
    }

    public static function countCancellationByWHSPddtByERP($erp)
    {
        if (empty($erp)) {
            return;
        }
        $query = <<<EOF
SELECT
    count(*) AS num,
    t2.t_cwar AS t_cwar,
    (CASE
        WHEN (t1.t_pddt < getdate()) THEN 'Urgent'
        WHEN (t1.t_pddt < (getdate() + 7)) THEN 'Week'
        WHEN (t1.t_pddt < (getdate() + 28)) THEN 'Month'
        ELSE '>Month'
    END) as level
FROM ttimrp031$erp as t1
    LEFT JOIN ttiitm001$erp as t2 ON t1.t_item = t2.t_item
WHERE
    t1.t_excm = 12
    AND t1.t_koor = 2
GROUP BY
    t2.t_cwar,
    (CASE
        WHEN (t1.t_pddt < getdate()) THEN 'Urgent'
        WHEN (t1.t_pddt < (getdate() + 7)) THEN 'Week'
        WHEN (t1.t_pddt < (getdate() + 28)) THEN 'Month'
        ELSE '>Month'
    END)
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     * Get Value (amount in EUR) count of PO Cancellation lines by level by ERP
     *
     * @return array
     */
    public static function countValueCancellationByERPPddt()
    {
        // Prepare currencies
        $currencies = [
            'USD' => 'USD', 'CAD' => 'CAD',
            'HKD' => 'HKD', 'CNY' => 'RMB',
        ];
        $q = [];
        foreach ($currencies as $currency => $bcurrency) {
            $r = tldForex::getRate($currency, 'EUR');
            $q[] = "WHEN t4.t_ccur = '$bcurrency' THEN ((t3.t_amta/t3.t_oqua)*t1.t_oqan)*$r";
        }
        $case = (implode("\n", $q));
        // Declare all factories ERP
        $erps = [
            400 => 'TLD WIN', 410 => 'TLD WIM', 420 => 'TLD SHE',
            500 => 'TLD MTL', 520 => 'TLD STL',
            640 => 'TLD SHA', 660 => 'TLD WUX',
        ];
        // Get queries
        $rows = [];
        foreach ($erps as $erp => $factory) {
            $a[] = <<<EOF
SELECT
    '$factory' as location,
    '$erp' as erp,
    (CASE
        WHEN (t1.t_pddt < getdate()) THEN 'Urgent'
        WHEN (t1.t_pddt < (getdate() + 7)) THEN 'Week'
        WHEN (t1.t_pddt < (getdate() + 28)) THEN 'Month'
        ELSE '>Month'
    END) as level,
    ROUND(
        SUM(CASE
            $case
            ELSE (t3.t_amta/t3.t_oqua)*t1.t_oqan
        END
    ),0) as num
FROM
    ttimrp031$erp as t1
    LEFT JOIN ttiitm001$erp as t2 ON t1.t_item = t2.t_item
    LEFT JOIN ttdpur041$erp as t3 ON t1.t_orno = t3.t_orno AND t1.t_pono = t3.t_pono
    LEFT JOIN ttdpur040$erp as t4 ON t4.t_orno = t3.t_orno
WHERE
    t1.t_excm=12 AND t1.t_koor=2
GROUP BY
    (CASE
        WHEN (t1.t_pddt > 0) THEN '$erp'
        ELSE''
    END),
    (CASE
        WHEN (t1.t_pddt < getdate()) THEN 'Urgent'
        WHEN (t1.t_pddt < (getdate() + 7)) THEN 'Week'
        WHEN (t1.t_pddt < (getdate() + 28)) THEN 'Month'
        ELSE '>Month'
    END)
EOF;
        }
        $query = implode(' UNION ', $a);
        $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
        return $rows;
    }

    public static function countValueCancellationByWHSPddtByERP($erp)
    {
        // Prepare currencies
        $currencies = [
            'USD' => 'USD', 'CAD' => 'CAD',
            'HKD' => 'HKD', 'CNY' => 'RMB',
        ];
        foreach ($currencies as $currency => $bcurrency) {
            $r = tldForex::getRate($currency, 'EUR');
            $q[] = "WHEN t4.t_ccur = '$bcurrency' THEN ((t3.t_amta/t3.t_oqua)*t1.t_oqan)*$r";
        }
        $case = (implode("\n", $q));
        // Declare all factories ERP
        $erps = [
            400 => 'TLD WIN', 410 => 'TLD WIM', 420 => 'TLD SHE',
            500 => 'TLD MTL', 520 => 'TLD STL',
            640 => 'TLD SHA', 660 => 'TLD WUX',
        ];
        // Get queries
        $rows = [];
        $query = <<<EOF
SELECT
    ROUND(
        SUM(CASE
            $case
            ELSE (t3.t_amta/t3.t_oqua)*t1.t_oqan
        END
    ),0) as num,
    t2.t_cwar AS t_cwar,
    (CASE
        WHEN (t1.t_pddt < getdate()) THEN 'Urgent'
        WHEN (t1.t_pddt < (getdate() + 7)) THEN 'Week'
        WHEN (t1.t_pddt < (getdate() + 28)) THEN 'Month'
        ELSE '>Month'
    END) as level
FROM
    ttimrp031$erp as t1
    LEFT JOIN ttiitm001$erp as t2 ON t1.t_item = t2.t_item
    LEFT JOIN ttdpur041$erp as t3 ON t1.t_orno = t3.t_orno AND t1.t_pono = t3.t_pono
    LEFT JOIN ttdpur040$erp as t4 ON t4.t_orno = t3.t_orno
WHERE
    t1.t_excm=12 AND t1.t_koor=2
GROUP BY
    t2.t_cwar,
    (CASE
        WHEN (t1.t_pddt < getdate()) THEN 'Urgent'
        WHEN (t1.t_pddt < (getdate() + 7)) THEN 'Week'
        WHEN (t1.t_pddt < (getdate() + 28)) THEN 'Month'
        ELSE '>Month'
    END)
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     * Get PO Cancellation lines by level by ERP
     *
     * @param interger $erp
     * @param string $mode
     * @param array $options
     *
     * @return array
     */
    public static function byCancellationByERPPddt($location, $mode, $options = '')
    {
        switch ($mode) {
            case 'Urgent':
                // level 0, urgent !
                $CONSTRAINTS[] = ' t1.t_pddt < getdate() ';
                break;
            case 'Week':
                // level 1, 1 week
                $CONSTRAINTS[] = ' t1.t_pddt > getdate() AND t1.t_pddt < (getdate() + 7) ';
                break;
            case 'Month':
                // level 2, 1 month
                $CONSTRAINTS[] = ' t1.t_pddt > (getdate() + 7) AND t1.t_pddt < (getdate() + 28) ';
                break;
            case '>Month':
                // level 3, more than 1 month
                $CONSTRAINTS[] = ' t1.t_pddt > (getdate() + 28)';
                break;
            default:
                // no constraints
        }

        $WHERE = '';
        if (count($CONSTRAINTS)) {
            $WHERE = ' AND ' . implode(' AND ', $CONSTRAINTS);
        }

        // get the erp number from location name
        $erp = tldLocation::getERPByLocation($location);

        $a = <<<EOF
SELECT
    '$erp' as erp,
    CONVERT(VARCHAR(100), CAST(((t3.t_amta*t1.t_oqan)/t3.t_oqua) AS DECIMAL(15,2))) AS t_amta,
    CONVERT(VARCHAR(100), CAST((t2.t_copr*t1.t_oqan) AS DECIMAL(15,2))) AS t_copr,
    t4.t_ccur,
    t1.t_item,
    t2.t_dsca,
    t1.t_orno,
    t1.t_pono,
    t2.t_cuni,
    t1.t_oqan,
    t4.t_suno,
    SUBSTRING(convert(varchar,t1.t_pddt,120),0,11) as t_pddt,
    t5.t_nama
FROM
    ttimrp031$erp as t1
    LEFT JOIN ttiitm001$erp as t2 ON t1.t_item = t2.t_item
    LEFT JOIN ttdpur041$erp as t3 ON t1.t_orno = t3.t_orno AND t1.t_pono = t3.t_pono
    LEFT JOIN ttdpur040$erp as t4 ON t4.t_orno = t3.t_orno
    LEFT JOIN ttccom001$erp as t5 ON t4.t_ccon = t5.t_emno
WHERE
    AND	t1.t_excm = 12
    AND	t1.t_koor = 2
    $WHERE
EOF;
        $result = tldUtils::getSqlToAssocArray($a, 'odbc', ['src' => 'baan']);
        return $result;
    }

    public static function byCancellationByWHSPddtByERP($erp, $whs, $mode, $options = '')
    {
        switch ($mode) {
            case 'Urgent':
                // level 0, urgent !
                $CONSTRAINTS[] = ' t1.t_pddt < getdate() ';
                break;
            case 'Week':
                // level 1, 1 week
                $CONSTRAINTS[] = ' t1.t_pddt > getdate() AND t1.t_pddt < (getdate() + 7) ';
                break;
            case 'Month':
                // level 2, 1 month
                $CONSTRAINTS[] = ' t1.t_pddt > (getdate() + 7) AND t1.t_pddt < (getdate() + 28) ';
                break;
            case '>Month':
                // level 3, more than 1 month
                $CONSTRAINTS[] = ' t1.t_pddt > (getdate() + 28)';
                break;
        }

        $CONSTRAINTS[] = " t2.t_cwar='$whs'";

        if (count($CONSTRAINTS)) {
            $WHERE = ' AND ' . implode(' AND ', $CONSTRAINTS);
        }
        $query = <<<EOF
SELECT
    '$erp' as erp,
    t2.t_cwar,
    CONVERT(VARCHAR(100), CAST(((t3.t_amta*t1.t_oqan)/t3.t_oqua) AS DECIMAL(15,2))) AS t_amta,
    CONVERT(VARCHAR(100), CAST((t2.t_copr*t1.t_oqan) AS DECIMAL(15,2))) AS t_copr,
    t4.t_ccur,
    t1.t_item,
    t2.t_dsca,
    t1.t_orno,
    t1.t_pono,
    t2.t_cuni,
    t1.t_oqan,
    t4.t_suno,
    SUBSTRING(convert(varchar,t1.t_pddt,120),0,11) as t_pddt,TXT.t_text,
    t5.t_nama
FROM
    ttimrp031$erp as t1
    LEFT JOIN ttiitm001$erp as t2 ON t1.t_item = t2.t_item
    LEFT JOIN ttdpur041$erp as t3 ON t1.t_orno = t3.t_orno AND t1.t_pono = t3.t_pono
    LEFT JOIN ttdpur040$erp as t4 ON t4.t_orno = t3.t_orno
    LEFT JOIN ttccom001$erp as t5 ON t4.t_ccon=t5.t_emno
    LEFT JOIN ttttxt010$erp as TXT
    	ON TXT.t_ctxt=t3.t_txta
WHERE
    t1.t_excm=12
    AND t1.t_koor=2
    $WHERE
ORDER BY
    t1.t_pddt
EOF;

        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public static function byCancellationByWHSByBuyerPddtByERP($erp, $whs, $mode, $buyer_email, $options = '')
    {
        switch ($mode) {
            case 'Urgent':
                // level 0, urgent !
                $CONSTRAINTS[] = ' t1.t_pddt < getdate() ';
                break;
            case 'Week':
                // level 1, 1 week
                $CONSTRAINTS[] = ' t1.t_pddt > getdate() AND t1.t_pddt < (getdate() + 7) ';
                break;
            case 'Month':
                // level 2, 1 month
                $CONSTRAINTS[] = ' t1.t_pddt > (getdate() + 7) AND t1.t_pddt < (getdate() + 28) ';
                break;
            case '>Month':
                // level 3, more than 1 month
                $CONSTRAINTS[] = ' t1.t_pddt > (getdate() + 28)';
                break;
        }

        $CONSTRAINTS[] = " t2.t_cwar='$whs'";
        $CONSTRAINTS[] = " t5.t_info='$buyer_email'";

        if (count($CONSTRAINTS)) {
            $WHERE = ' AND ' . implode(' AND ', $CONSTRAINTS);
        }
        $query = <<<EOF
SELECT
    '$erp' as erp,
    t2.t_cwar,
    CONVERT(VARCHAR(100), CAST(((t3.t_amta*t1.t_oqan)/t3.t_oqua) AS DECIMAL(15,2))) AS t_amta,
    CONVERT(VARCHAR(100), CAST((t2.t_copr*t1.t_oqan) AS DECIMAL(15,2))) AS t_copr,
    t4.t_ccur,
    t1.t_item,
    t2.t_dsca,
    t1.t_orno,
    t1.t_pono,
    t2.t_cuni,
    t1.t_oqan,
    t4.t_suno,
    SUBSTRING(convert(varchar,t1.t_pddt,120),0,11) as t_pddt,
    t5.t_nama,
    txt.t_text
FROM
    ttimrp031$erp as t1
    LEFT JOIN ttiitm001$erp as t2 ON t1.t_item = t2.t_item
    LEFT JOIN ttdpur041$erp as t3 ON t1.t_orno = t3.t_orno AND t1.t_pono = t3.t_pono
    LEFT JOIN ttdpur040$erp as t4 ON t4.t_orno = t3.t_orno
    LEFT JOIN ttccom001$erp as t5 ON t4.t_ccon=t5.t_emno
    LEFT JOIN ttttxt010$erp as txt ON txt.t_ctxt=t3.t_txta
WHERE
    t1.t_excm=12
    AND t1.t_koor=2
    $WHERE
ORDER BY
    t1.t_pddt
EOF;

        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public static function byReschedulingByWHSByBuyerPddtByERP($erp, $whs, $mode, $buyer_email, $options = '')
    {
        switch ($mode) {
            case 'Urgent':
                // level 0, urgent !
                $CONSTRAINTS[] = ' t1.t_pddt < getdate() ';
                break;
            case 'Week':
                // level 1, 1 week
                $CONSTRAINTS[] = ' t1.t_pddt > getdate() AND t1.t_pddt < (getdate() + 7) ';
                break;
            case 'Month':
                // level 2, 1 month
                $CONSTRAINTS[] = ' t1.t_pddt > (getdate() + 7) AND t1.t_pddt < (getdate() + 28) ';
                break;
            case '>Month':
                // level 3, more than 1 month
                $CONSTRAINTS[] = ' t1.t_pddt > (getdate() + 28)';
                break;
        }

        $CONSTRAINTS[] = " t2.t_cwar='$whs'";
        $CONSTRAINTS[] = " t5.t_info='$buyer_email'";

        switch ($options['io']) {
            case 'i':
                $CONSTRAINTS[] = ' t1.t_resm=1 ';
                break;
            case 'o':
                $CONSTRAINTS[] = ' t1.t_resm=2 ';
                break;
        }

        if (count($CONSTRAINTS)) {
            $WHERE = ' AND ' . implode(' AND ', $CONSTRAINTS);
        }
        $query = <<<EOF
SELECT
    '$erp' as erp,
    t2.t_cwar,
    convert(varchar(100), cast(((t3.t_amta*t1.t_oqan)/t3.t_oqua) as decimal(15,2))) as t_amta,
    CONVERT(VARCHAR(100), CAST((t2.t_copr*t1.t_oqan) AS DECIMAL(15,2))) AS t_copr,
    t4.t_ccur,
    t1.t_item,
    t2.t_dsca,
    t1.t_orno,
    t1.t_pono,
    t2.t_cuni,
    t1.t_oqan,
    t4.t_suno,
    (SELECT sup.t_nama FROM ttccom020$erp AS sup
       	WHERE sup.t_suno=t4.t_suno
    ) AS v_nama,
    case t1.t_resm
			WHEN '1' THEN 'Reschedule-in'
			WHEN '2' THEN 'Reschedule-out'
	END AS message,
    DATEDIFF(DAY,t1.t_pddt, t1.t_date) AS day_diff,
	SUBSTRING(convert(varchar,t1.t_date,120),0,11) as t_date,
    SUBSTRING(convert(varchar,t1.t_pddt,120),0,11) as t_pddt,
    t5.t_nama,
    txt.t_text
FROM
    ttimrp030$erp as t1
    LEFT JOIN ttiitm001$erp as t2 ON t1.t_item = t2.t_item
    LEFT JOIN ttdpur041$erp as t3 ON t1.t_orno = t3.t_orno AND t1.t_pono = t3.t_pono
    LEFT JOIN ttdpur040$erp as t4 ON t4.t_orno = t3.t_orno
    LEFT JOIN ttccom001$erp as t5 ON t4.t_ccon=t5.t_emno
    LEFT JOIN ttttxt010$erp as txt ON txt.t_ctxt=t3.t_txta
WHERE
    t1.t_koor=2
    $WHERE
ORDER BY
    t1.t_pddt
EOF;

        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }
    /**
     * ***
     * *****
     * ********
     * *********
     * ********************
     * *****************************
     * ********************************************
     * *****************************************************
     */
    /**
     * Get count of PO Rescheduling lines by level by ERP
     *
     * @return array
     */
    public static function countReschedulingByERPPddt()
    {
        $erps = [
            400 => 'TLD WIN', 410 => 'TLD WIM', 420 => 'TLD SHE',
            500 => 'TLD MTL', 520 => 'TLD STL', 540 => 'TLD EUR',
            640 => 'TLD SHA', 660 => 'TLD WUX',
        ];
        $rows = [];
        foreach ($erps as $erp => $factory) {
            $a[] = <<<EOF
SELECT
    '$factory' as location,
    '$erp' as erp,
    (CASE
        WHEN (t1.t_pddt < getdate()) THEN 'Urgent'
        WHEN (t1.t_pddt < (getdate() + 7)) THEN 'Week'
        WHEN (t1.t_pddt < (getdate() + 28)) THEN 'Month'
        ELSE '>Month'
    END) as level,
    count(*) as num
FROM ttimrp030$erp as t1
    LEFT JOIN ttiitm001$erp as t2 ON t1.t_item = t2.t_item
WHERE
    t1.t_koor=2
GROUP BY
    (CASE
        WHEN (t1.t_pddt > 0) THEN '$erp'
        ELSE''
    END),
    (CASE
        WHEN (t1.t_pddt < getdate()) THEN 'Urgent'
        WHEN (t1.t_pddt < (getdate() + 7)) THEN 'Week'
        WHEN (t1.t_pddt < (getdate() + 28)) THEN 'Month'
        ELSE '>Month'
    END)
EOF;
        }
        $rows = tldUtils::getSqlToAssocArray(implode(' UNION ', $a), 'odbc', ['src' => 'baan']);
        return $rows;
    }

    public static function countReschedulingByWHSPddtByERP($erp, $io = '')
    {
        if (empty($erp)) {
            return;
        }
        switch ($io) {
            case 'i':
                $filter = ' AND t1.t_resm=1 ';
                break;
            case 'o':
                $filter = ' AND t1.t_resm=2 ';
                break;
            default:
                $filter = '';
                break;
        }
        $query = <<<EOF
SELECT
    count(*) AS num,
    t2.t_cwar AS t_cwar,
    (CASE
        WHEN (t1.t_pddt < getdate()) THEN 'Urgent'
        WHEN (t1.t_pddt < (getdate() + 7)) THEN 'Week'
        WHEN (t1.t_pddt < (getdate() + 28)) THEN 'Month'
        ELSE '>Month'
    END) as level
FROM ttimrp030$erp as t1
    LEFT JOIN ttiitm001$erp as t2 ON t1.t_item = t2.t_item
WHERE
	t1.t_koor = 2 $filter
GROUP BY
    t2.t_cwar,
    (CASE
        WHEN (t1.t_pddt < getdate()) THEN 'Urgent'
        WHEN (t1.t_pddt < (getdate() + 7)) THEN 'Week'
        WHEN (t1.t_pddt < (getdate() + 28)) THEN 'Month'
        ELSE '>Month'
    END)
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }


    public static function countReschedulingByWHSPddtByERPByBuyer($erp, $buyer, $io = '')
    {
        if (empty($erp)) {
            return;
        }
        switch ($io) {
            case 'i':
                $filter = ' AND t1.t_resm=1 ';
                break;
            case 'o':
                $filter = ' AND t1.t_resm=2 ';
                break;
            default:
                $filter = '';
                break;
        }
        $query = <<<EOF
SELECT
    count(*) AS num,
    t2.t_cwar AS t_cwar,
    (CASE
        WHEN (t1.t_pddt < getdate()) THEN 'Urgent'
        WHEN (t1.t_pddt < (getdate() + 7)) THEN 'Week'
        WHEN (t1.t_pddt < (getdate() + 28)) THEN 'Month'
        ELSE '>Month'
    END) as level
FROM ttimrp030$erp as t1
    LEFT JOIN ttiitm001$erp as t2 ON t1.t_item = t2.t_item
    LEFT JOIN ttdpur041$erp as t3 ON t1.t_orno = t3.t_orno AND t1.t_pono = t3.t_pono
    LEFT JOIN ttdpur040$erp as t4 ON t4.t_orno = t3.t_orno
    LEFT JOIN ttccom001$erp as t5 ON t4.t_ccon = t5.t_emno
WHERE
	t1.t_koor = 2
	AND
	t5.t_info='$buyer' $filter
GROUP BY
    t2.t_cwar,
    (CASE
        WHEN (t1.t_pddt < getdate()) THEN 'Urgent'
        WHEN (t1.t_pddt < (getdate() + 7)) THEN 'Week'
        WHEN (t1.t_pddt < (getdate() + 28)) THEN 'Month'
        ELSE '>Month'
    END)
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     * Get Value (amount in EUR) count of PO Rescheduling lines by level by ERP
     *
     * @return array
     */
    public static function countValueReschedulingByERPPddt()
    {
        // Prepare currencies
        $currencies = [
            'USD' => 'USD', 'CAD' => 'CAD',
            'HKD' => 'HKD', 'CNY' => 'RMB',
        ];
        foreach ($currencies as $currency => $bcurrency) {
            $r = tldForex::getRate($currency, 'EUR');
            $q[] = "WHEN t4.t_ccur = '$bcurrency' THEN ((t3.t_amta/t3.t_oqua)*t1.t_oqan)*$r";
        }
        $case = (implode("\n", $q));
        // Declare all factories ERP
        $erps = [
            400 => 'TLD WIN', 410 => 'TLD WIM', 420 => 'TLD SHE',
            500 => 'TLD MTL', 520 => 'TLD STL',
            640 => 'TLD SHA', 660 => 'TLD WUX',
        ];
        // Get queries
        $rows = [];
        foreach ($erps as $erp => $factory) {
            $a[] = <<<EOF
SELECT
    '$factory' as location,
    '$erp' as erp,
    (CASE
        WHEN (t1.t_pddt < getdate()) THEN 'Urgent'
        WHEN (t1.t_pddt < (getdate() + 7)) THEN 'Week'
        WHEN (t1.t_pddt < (getdate() + 28)) THEN 'Month'
        ELSE '>Month'
    END) as level,
    ROUND(
        SUM(CASE
            $case
            ELSE (t3.t_amta/t3.t_oqua)*t1.t_oqan
        END
    ),0) as num
FROM
    ttimrp030$erp as t1
    LEFT JOIN ttiitm001$erp as t2 ON t1.t_item = t2.t_item
    LEFT JOIN ttdpur041$erp as t3 ON t1.t_orno = t3.t_orno AND t1.t_pono = t3.t_pono
    LEFT JOIN ttdpur040$erp as t4 ON t4.t_orno = t3.t_orno
WHERE
    t1.t_koor=2
GROUP BY
    (CASE
        WHEN (t1.t_pddt > 0) THEN '$erp'
        ELSE''
    END),
    (CASE
        WHEN (t1.t_pddt < getdate()) THEN 'Urgent'
        WHEN (t1.t_pddt < (getdate() + 7)) THEN 'Week'
        WHEN (t1.t_pddt < (getdate() + 28)) THEN 'Month'
        ELSE '>Month'
    END)
EOF;
        }
        $query = implode(' UNION ', $a);
        $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
        return $rows;
    }

    public static function countValueReschedulingByWHSPddtByERP($erp, $io = '')
    {
        // Prepare currencies
        $currencies = [
            'USD' => 'USD', 'CAD' => 'CAD',
            'HKD' => 'HKD', 'CNY' => 'RMB',
        ];
        foreach ($currencies as $currency => $bcurrency) {
            $r = tldForex::getRate($currency, 'EUR');
            $q[] = "WHEN t4.t_ccur = '$bcurrency' THEN ((t3.t_amta/t3.t_oqua)*t1.t_oqan)*$r";
        }
        $case = (implode("\n", $q));
        // Declare all factories ERP
        $erps = [
            400 => 'TLD WIN', 410 => 'TLD WIM', 420 => 'TLD SHE',
            500 => 'TLD MTL', 520 => 'TLD STL',
            640 => 'TLD SHA', 660 => 'TLD WUX',
        ];
        switch ($io) {
            case 'i':
                $filter = ' AND t1.t_resm=1 ';
                break;
            case 'o':
                $filter = ' AND t1.t_resm=2 ';
                break;
            default:
                $filter = '';
                break;
        }
        // Get queries
        $rows = [];
        $query = <<<EOF
    SELECT
    ROUND(
    SUM(CASE
    $case
    ELSE (t3.t_amta/t3.t_oqua)*t1.t_oqan
    END
    ),0) as num,
    t2.t_cwar AS t_cwar,
    (CASE
    WHEN (t1.t_pddt < getdate()) THEN 'Urgent'
    WHEN (t1.t_pddt < (getdate() + 7)) THEN 'Week'
    WHEN (t1.t_pddt < (getdate() + 28)) THEN 'Month'
    ELSE '>Month'
    END) as level
    FROM
    ttimrp030$erp as t1
    LEFT JOIN ttiitm001$erp as t2 ON t1.t_item = t2.t_item
    LEFT JOIN ttdpur041$erp as t3 ON t1.t_orno = t3.t_orno AND t1.t_pono = t3.t_pono
    LEFT JOIN ttdpur040$erp as t4 ON t4.t_orno = t3.t_orno
    WHERE
    t1.t_koor=2 $filter
    GROUP BY
    t2.t_cwar,
    (CASE
    WHEN (t1.t_pddt < getdate()) THEN 'Urgent'
    WHEN (t1.t_pddt < (getdate() + 7)) THEN 'Week'
    WHEN (t1.t_pddt < (getdate() + 28)) THEN 'Month'
    ELSE '>Month'
    END)
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     * Get PO Rescheduling lines by level by ERP
     *
     * @param interger $erp
     * @param string $mode
     * @param array $options
     *
     * @return array
     */

    public static function byReschedulingByERPPddt($location, $mode, $options = '')
    {
        $CONSTRAINTS = [];
        switch ($mode) {
            case 'Urgent':
                // level 0, urgent !
                $CONSTRAINTS[] = ' t1.t_pddt < getdate() ';
                break;
            case 'Week':
                // level 1, 1 week
                $CONSTRAINTS[] = ' t1.t_pddt > getdate() AND t1.t_pddt < (getdate() + 7) ';
                break;
            case 'Month':
                // level 2, 1 month
                $CONSTRAINTS[] = ' t1.t_pddt > (getdate() + 7) AND t1.t_pddt < (getdate() + 28) ';
                break;
            case '>Month':
                // level 3, more than 1 month
                $CONSTRAINTS[] = ' t1.t_pddt > (getdate() + 28)';
                break;
            default:
                // no constraints
        }

        if (count($CONSTRAINTS)) {
            $WHERE = ' AND ' . implode(' AND ', $CONSTRAINTS);
        }

        // get the erp number from location name
        $erp = tldLocation::getERPByLocation($location);

        $a = <<<EOF
SELECT '$erp' as erp,
	convert(varchar(100), cast(((t3.t_amta*t1.t_oqan)/t3.t_oqua) as decimal(15,2))) as t_amta,
	CONVERT(VARCHAR(100), CAST((t2.t_copr*t1.t_oqan) AS DECIMAL(15,2))) AS t_copr,
	t4.t_ccur,
	t1.t_item,
	t2.t_dsca,
	t1.t_orno,
	t1.t_pono,
	t2.t_cuni,
	t1.t_oqan,
	t4.t_suno,
	(SELECT sup.t_nama FROM ttccom020$erp AS sup
    WHERE sup.t_suno=t4.t_suno
	) AS v_nama,
	DATEDIFF(DAY,t1.t_pddt, t1.t_date) AS day_diff,
	SUBSTRING(convert(varchar,t1.t_date,120),0,11) as t_date,
	case t1.t_resm
		WHEN '1' THEN 'Reschedule-in'
		WHEN '2' THEN 'Reschedule-out'
	END AS message,
	SUBSTRING(convert(varchar,t1.t_pddt,120),0,11) as t_pddt,
	t5.t_nama
FROM
    ttimrp030$erp as t1,
    ttiitm001$erp as t2,
    ttdpur041$erp as t3,
    ttdpur040$erp as t4
    LEFT JOIN ttccom001$erp as t5 ON t4.t_ccon=t5.t_emno
WHERE
   t1.t_item = t2.t_item
	AND	t1.t_koor = 2
	AND t1.t_orno = t3.t_orno
	AND t1.t_pono = t3.t_pono
	AND t3.t_orno = t4.t_orno
	$WHERE
EOF;
        $result = tldUtils::getSqlToAssocArray($a, 'odbc', ['src' => 'baan']);
        return $result;
    }

    public static function byReschedulingByWHSPddtByERP($erp, $whs, $mode, $options = '')
    {
        switch ($mode) {
            case 'Urgent':
                // level 0, urgent !
                $CONSTRAINTS[] = ' t1.t_pddt < getdate() ';
                break;
            case 'Week':
                // level 1, 1 week
                $CONSTRAINTS[] = ' t1.t_pddt > getdate() AND t1.t_pddt < (getdate() + 7) ';
                break;
            case 'Month':
                // level 2, 1 month
                $CONSTRAINTS[] = ' t1.t_pddt > (getdate() + 7) AND t1.t_pddt < (getdate() + 28) ';
                break;
            case '>Month':
                // level 3, more than 1 month
                $CONSTRAINTS[] = ' t1.t_pddt > (getdate() + 28)';
                break;
        }

        $CONSTRAINTS[] = " t2.t_cwar='$whs'";
        switch ($options['io']) {
            case 'i':
                $CONSTRAINTS[] = ' t1.t_resm=1 ';
                break;
            case 'o':
                $CONSTRAINTS[] = ' t1.t_resm=2 ';
                break;
        }

        if (count($CONSTRAINTS)) {
            $WHERE = ' AND ' . implode(' AND ', $CONSTRAINTS);
        }
        $query = <<<EOF
            SELECT
            '$erp' as erp,
            t2.t_cwar,
            convert(varchar(100), cast(((t3.t_amta*t1.t_oqan)/t3.t_oqua) as decimal(15,2))) as t_amta,
            CONVERT(VARCHAR(100), CAST((t2.t_copr*t1.t_oqan) AS DECIMAL(15,2))) AS t_copr,
            t4.t_ccur,
            t1.t_item,
            t2.t_dsca,
            t1.t_orno,
            t1.t_pono,
            t2.t_cuni,
            t1.t_oqan,
            t4.t_suno,TXT.t_text,
    		(SELECT sup.t_nama FROM ttccom020$erp AS sup
       		 WHERE sup.t_suno=t4.t_suno
    		) AS v_nama,
           	DATEDIFF(DAY,t1.t_pddt, t1.t_date) AS day_diff,
			SUBSTRING(convert(varchar,t1.t_date,120),0,11) as t_date,
            case t1.t_resm
				WHEN '1' THEN 'Reschedule-in'
				WHEN '2' THEN 'Reschedule-out'
			END AS message,
            SUBSTRING(convert(varchar,t1.t_pddt,120),0,11) as t_pddt,
            t5.t_nama
            FROM
            ttimrp030$erp as t1
            LEFT JOIN ttiitm001$erp as t2 ON t1.t_item = t2.t_item
            LEFT JOIN ttdpur041$erp as t3 ON t1.t_orno = t3.t_orno AND t1.t_pono = t3.t_pono
            LEFT JOIN ttdpur040$erp as t4 ON t4.t_orno = t3.t_orno
            LEFT JOIN ttccom001$erp as t5 ON t4.t_ccon=t5.t_emno
            LEFT JOIN ttttxt010$erp as TXT
    	ON TXT.t_ctxt=t3.t_txta
            WHERE
            t1.t_koor=2
            $WHERE
            ORDER BY
            t1.t_pddt
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public static function countCancellationByWHSPddtByERPByBuyer($erp, $buyer)
    {
        if (empty($erp)) {
            return;
        }
        $query = <<<EOF
SELECT
    count(*) AS num,
    t2.t_cwar AS t_cwar,
    (CASE
        WHEN (t1.t_pddt < getdate()) THEN 'Urgent'
        WHEN (t1.t_pddt < (getdate() + 7)) THEN 'Week'
        WHEN (t1.t_pddt < (getdate() + 28)) THEN 'Month'
        ELSE '>Month'
    END) as level
FROM ttimrp031$erp as t1
    LEFT JOIN ttiitm001$erp as t2 ON t1.t_item = t2.t_item
    LEFT JOIN ttdpur041$erp as t3 ON t1.t_orno = t3.t_orno AND t1.t_pono = t3.t_pono
    LEFT JOIN ttdpur040$erp as t4 ON t4.t_orno = t3.t_orno
    LEFT JOIN ttccom001$erp as t5 ON t4.t_ccon = t5.t_emno
WHERE
    t1.t_excm = 12
    AND t1.t_koor = 2
    AND
	t5.t_info='$buyer'
GROUP BY
    t2.t_cwar,
    (CASE
        WHEN (t1.t_pddt < getdate()) THEN 'Urgent'
        WHEN (t1.t_pddt < (getdate() + 7)) THEN 'Week'
        WHEN (t1.t_pddt < (getdate() + 28)) THEN 'Month'
        ELSE '>Month'
    END)
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public static function countValueCancellationByWHSPddtByERPByBuyer($erp, $buyer)
    {
        // Prepare currencies
        $currencies = [
            'USD' => 'USD', 'CAD' => 'CAD',
            'HKD' => 'HKD', 'CNY' => 'RMB',
        ];
        $q = [];
        foreach ($currencies as $currency => $bcurrency) {
            $r = tldForex::getRate($currency, 'EUR');
            $q[] = "WHEN t4.t_ccur = '$bcurrency' THEN ((t3.t_amta/t3.t_oqua)*t1.t_oqan)*$r";
        }
        $case = (implode("\n", $q));
        // Declare all factories ERP
        $erps = [
            400 => 'TLD WIN', 410 => 'TLD WIM', 420 => 'TLD SHE',
            500 => 'TLD MTL', 520 => 'TLD STL',
            640 => 'TLD SHA', 660 => 'TLD WUX',
        ];
        // Get queries
        $rows = [];
        $query = <<<EOF
SELECT
    ROUND(
        SUM(CASE
            $case
            ELSE (t3.t_amta/t3.t_oqua)*t1.t_oqan
        END
    ),0) as num,
    t2.t_cwar AS t_cwar,
    (CASE
        WHEN (t1.t_pddt < getdate()) THEN 'Urgent'
        WHEN (t1.t_pddt < (getdate() + 7)) THEN 'Week'
        WHEN (t1.t_pddt < (getdate() + 28)) THEN 'Month'
        ELSE '>Month'
    END) as level
FROM
    ttimrp031$erp as t1
    LEFT JOIN ttiitm001$erp as t2 ON t1.t_item = t2.t_item
    LEFT JOIN ttdpur041$erp as t3 ON t1.t_orno = t3.t_orno AND t1.t_pono = t3.t_pono
    LEFT JOIN ttdpur040$erp as t4 ON t4.t_orno = t3.t_orno
    LEFT JOIN ttccom001$erp as t5 ON t4.t_ccon = t5.t_emno
WHERE
    t1.t_excm=12 AND t1.t_koor=2 AND
	t5.t_info = '$buyer'
GROUP BY
    t2.t_cwar,
    (CASE
        WHEN (t1.t_pddt < getdate()) THEN 'Urgent'
        WHEN (t1.t_pddt < (getdate() + 7)) THEN 'Week'
        WHEN (t1.t_pddt < (getdate() + 28)) THEN 'Month'
        ELSE '>Month'
    END)
EOF;

        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public static function countValueReschedulingByWHSPddtByERPByBuyer($erp, $buyer, $io = '')
    {
        // Prepare currencies
        $currencies = [
            'USD' => 'USD', 'CAD' => 'CAD',
            'HKD' => 'HKD', 'CNY' => 'RMB',
        ];
        foreach ($currencies as $currency => $bcurrency) {
            $r = tldForex::getRate($currency, 'EUR');
            $q[] = "WHEN t4.t_ccur = '$bcurrency' THEN ((t3.t_amta/t3.t_oqua)*t1.t_oqan)*$r";
        }
        $case = (implode("\n", $q));
        // Declare all factories ERP
        $erps = [
            400 => 'TLD WIN', 410 => 'TLD WIM', 420 => 'TLD SHE',
            500 => 'TLD MTL', 520 => 'TLD STL',
            640 => 'TLD SHA', 660 => 'TLD WUX',
        ];
        switch ($io) {
            case 'i':
                $filter = ' AND t1.t_resm=1 ';
                break;
            case 'o':
                $filter = ' AND t1.t_resm=2 ';
                break;
            default:
                $filter = '';
                break;
        }
        // Get queries
        $rows = [];
        $query = <<<EOF
SELECT
    ROUND(
        SUM(CASE
            $case
            ELSE (t3.t_amta/t3.t_oqua)*t1.t_oqan
        END
    ),0) as num,
    t2.t_cwar AS t_cwar,
    (CASE
        WHEN (t1.t_pddt < getdate()) THEN 'Urgent'
        WHEN (t1.t_pddt < (getdate() + 7)) THEN 'Week'
        WHEN (t1.t_pddt < (getdate() + 28)) THEN 'Month'
        ELSE '>Month'
    END) as level
FROM
    ttimrp030$erp as t1
    LEFT JOIN ttiitm001$erp as t2 ON t1.t_item = t2.t_item
    LEFT JOIN ttdpur041$erp as t3 ON t1.t_orno = t3.t_orno AND t1.t_pono = t3.t_pono
    LEFT JOIN ttdpur040$erp as t4 ON t4.t_orno = t3.t_orno
    LEFT JOIN ttccom001$erp as t5 ON t4.t_ccon = t5.t_emno
WHERE
    t1.t_koor=2 AND
	t5.t_info = '$buyer' $filter
GROUP BY
    t2.t_cwar,
    (CASE
        WHEN (t1.t_pddt < getdate()) THEN 'Urgent'
        WHEN (t1.t_pddt < (getdate() + 7)) THEN 'Week'
        WHEN (t1.t_pddt < (getdate() + 28)) THEN 'Month'
        ELSE '>Month'
    END)
EOF;

        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }


}

/**
 * Class for creating and manipulating PO Line in the Baan SQL DB (tld db)
 *
 * @package ERP
 */
class tldPOLtld
{

    public function __construct($erp, $id, $t_pono, $t_orno)
    {
        $this->itsID = $id;
        $this->itsERP = $erp;
        $this->itsPONO = $t_pono;
        $this->itsORNO = $t_orno;
    }

    public function getHeader()
    {
        switch (tldERP::whatERP($this->itsERP)) {
            case 'baan':
                $query = <<<EOF
SELECT *
FROM tld..ttdpur041$this->itsERP
WHERE t_orno=$this->itsORNO
AND t_pono=$this->itsPONO
EOF;
                $opt2 = ['src' => 'baan'];
                break;
        }
        return tldUtils::getSqlRowToAssocArray($query, 'odbc', $opt2);
    }

    /**
     * Update estimate ship date information
     *
     * @param mixed $ddts
     * @return mixed
     */
    public function updateShipdate($ddts)
    {
        switch (tldERP::whatERP($this->itsERP)) {
            case 'baan':
                $query = <<<EOF
IF (EXISTS (SELECT * FROM tld..ttdpur041$this->itsERP AS t1
 		WHERE t_orno=$this->itsORNO
		AND t_pono=$this->itsPONO))
	BEGIN
  		UPDATE tld..ttdpur041$this->itsERP
		SET t_ddts='$ddts'
		WHERE t_orno=$this->itsORNO
		AND t_pono=$this->itsPONO
	END
ELSE
	BEGIN
  		INSERT INTO tld..ttdpur041$this->itsERP (t_orno, t_pono, t_ddts)
  		VALUES ($this->itsORNO, $this->itsPONO, '$ddts')
	END
EOF;
                $opt2 = ['src' => 'baan'];
                break;
        }
        return tldUtils::getSqlToAssocArray($query, 'odbc', $opt2);
    }

}

/**
 * Class for creating and manipulating PO Invoice
 * Submited from eVendors & located in the Baan SQL DB (tld db)
 * @package ERP
 */
class tldPO_INVtld
{

    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public function getFileID()
    {
        return $this->itsHeader['fid'];
    }

    public function getHeader()
    {
        $query = <<<EOF
SELECT *,
    SUBSTRING(CONVERT(VARCHAR, dt, 120), 0, 11) AS dt,
    SUBSTRING(CONVERT(VARCHAR, t_tedt, 120), 0, 11) AS t_tedt
FROM
    tld..po_inv AS po_inv
WHERE
    id=$this->itsID
ORDER BY id
EOF;
        return tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public static function insert($a)
    {
        if (empty($a)) {
            return;
        }
        $query = <<<EOF
INSERT INTO tld..po_inv (parent_id, dt, erp, t_orno, t_odat, t_suno, t_ninv, t_tedt, t_amnt, fid)
VALUES ('{$a['parent_id']}',GETDATE(),'{$a['erp']}','{$a['t_orno']}','{$a['t_odat']}',
'{$a['t_suno']}','{$a['t_ninv']}','{$a['t_tedt']}','{$a['t_amnt']}','{$a['fid']}');
EOF;
        return tldUtils::sqlInsert($query, 'odbc', ['src' => 'baan']);
    }

    public static function byConstraints($a)
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        $query = <<<EOF
SELECT *
FROM tld..po_inv AS po_inv
WHERE $WHERE
ORDER BY t_ninv
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public function getLines()
    {
        return tldPOL_INVtld::byParent($this->itsID);
    }

}

/**
 * Class for creating and manipulating PO LINE Invoice
 * Submited from eVendors & located in the Baan SQL DB (tld db)
 * @package ERP
 */
class tldPOL_INVtld
{

    public function __construct()
    {
    }

    public function getHeader()
    {
    }

    public static function insert($a)
    {
        if (empty($a)) {
            return;
        }
        $query = <<<EOF
INSERT INTO tld..pol_inv (parent_id, t_pono, desca, qty, price, type)
VALUES ('{$a['parent_id']}','{$a['t_pono']}','{$a['desc']}','{$a['qty']}','{$a['price']}','{$a['type']}');
EOF;
        return tldUtils::sqlInsert($query, 'odbc', ['src' => 'baan']);
    }

    public static function byConstraints($a)
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        $query = <<<EOF
SELECT *
FROM tld..pol_inv AS pol_inv
WHERE $WHERE
ORDER BY t_pono
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public static function byParent($pid)
    {
        return self::byConstraints(['parent_id' => $pid]);
    }

    public function getLatestByConstraints($a, $num = 10)
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        $query = <<<EOF
SELECT *
FROM tld..pol_inv AS pol_inv
WHERE $WHERE
ORDER BY t_pono
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }
}

/**
 * Class for creating and manipulating Request for Quotations
 *
 * @package ERP
 */
class tldRFQ
{

    public $itsID;
    public $itsERP;
    public $itsHeader;

    public function __construct($id, $erp)
    {
        $this->itsID = $id;
        $this->itsERP = $erp;
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Check the RFQ whever it is empty or not
     */
    public function isEmpty()
    {
        $header = $this->getHeader();
        $detail = $this->getDetail();
        return empty($header) && empty($detail);
    }

    /**
     * Get RFQ supplier number
     */
    public function getSUNO()
    {
        return $this->itsHeader['t_suno'];
    }

    /**
     * Get RFQ header data
     */
    public function getHeader()
    {
        if (empty($this->itsID)) {
            return;
        }
        $id = $this->itsID;
        $erp = $this->itsERP;
        $query = <<<EOF
		SELECT
			t1.t_qono,
			RTRIM(t1.t_suno) AS t_suno,
			SUBSTRING(convert(varchar,t1.t_rtdt,120), 0, 11) AS t_rtdt,
			t1.t_qspa
		FROM
			dbo.ttdpur001$erp AS t1
		WHERE
			t1.t_qono='$id'
EOF;
        return tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     * Get RFQ lines details
     * @return array of rows
     */
    public function getDetail()
    {
        $id = $this->itsID;
        $erp = $this->itsERP;
        $query = <<<EOF
			SELECT
				LINES.t_pono,
				LINES.t_item,
				SUBSTRING(convert(varchar,LINES.t_qdat,120), 0, 11) AS t_qdat,
				LINES.t_oqua,
				LINES.t_cuqp,
				LINES.t_cwar,
				LINES.t_pric,
				SUBSTRING(convert(varchar,LINES.t_ddat,120), 0, 11) AS t_ddat,
				ITM.t_dsca
			FROM ttdpur002$erp AS LINES,
				ttiitm001$erp AS ITM
			WHERE LINES.t_item=ITM.t_item
				AND LINES.t_qono='$id'
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public static function checkPNVendor($pn, $vendorid, $erp)
    {
        $query = <<<EOF
			SELECT
				RTRIM(LINES.t_item) AS t_item,
				RTRIM(LINES.t_suno) AS t_suno
			FROM ttdpur002$erp AS LINES
			WHERE t_item = '$pn' AND t_suno = '$vendorid'
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }


    /**
     * Get RFQ by constraints
     * @param mixed $erp
     * @param mixed $a
     */
    public static function byConstraints($erp, $a)
    {
        if (empty($erp) || empty($a)) {
            return 'Empty parameter';
        }
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        $query = <<<EOF
		SELECT
			T1.t_qono,
			T1.t_suno,
			SUBSTRING(convert(varchar,T1.t_rtdt,120), 0, 11) AS t_rtdt,
			T1.t_qspa,
			(SELECT RTRIM(t6.t_info) FROM ttccom001$erp AS t6
				WHERE t6.t_emno=T1.t_ccon
			) AS byr_email
		FROM
			dbo.ttdpur001$erp AS T1
		WHERE
			$WHERE
		ORDER BY t_rtdt
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public static function byOpenByConstraints($erp, $a = NULL)
    {
        if (empty($erp)) {
            return 'Empty parameter';
        }
        $WHERE = 'T1.t_qspa<4';
        if (is_array($a)) {
            $WHERE .= ' AND ' . tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE .= " AND $a ";
        }
        return self::byConstraints($erp, $WHERE);
    }

    public static function byOpenByBuyerEmail($erp, $email)
    {
        if (empty($email)) {
            return 'Empty parameter';
        }
        $a = "(SELECT RTRIM(t6.t_info) FROM ttccom001$erp AS t6 WHERE t6.t_emno=T1.t_ccon)='$email'";
        return self::byOpenByConstraints($erp, $a);
    }

    public static function byOpenBySuno($erp, $suno)
    {
        if (empty($suno)) {
            return 'Empty parameter';
        }
        $a = "T1.t_suno='$suno'";
        return self::byOpenByConstraints($erp, $a);
    }

    public static function byOpenByItem($erp, $item)
    {
        if (empty($item)) {
            return 'Empty parameter';
        }
        $a = <<<EOF
(SELECT COUNT(*) FROM ttdpur002$erp AS rfqline WHERE T1.t_qono=rfqline.t_qono AND rfqline.t_item='$item')>0
EOF;
        return self::byOpenByConstraints($erp, $a);
    }

    /**
     * Get RFQ by vendor, by ERP which has not been completely replied
     * @param string $vendorid
     * @param int $erp
     */
    public static function byVendorERP($vendorid, $erp)
    {
        $query = <<<EOF
		SELECT
			t1.t_qono,
			t1.t_suno,
			SUBSTRING(convert(varchar,t1.t_rtdt,120), 0, 11) AS t_rtdt,
			t1.t_qspa,
			(SELECT RTRIM(t6.t_info) FROM ttccom001$erp AS t6
				WHERE t6.t_emno=t1.t_ccon
			) AS byr_email
		FROM
			dbo.ttdpur001$erp AS t1
		WHERE
			t1.t_suno='$vendorid'
			AND (
				SELECT COUNT(t2.t_qono) FROM ttdpur002$erp AS t2
				WHERE t2.t_qono=t1.t_qono
			) > (
				SELECT COUNT(t3.t_qono) FROM ttdpur002$erp AS t3
				WHERE t3.t_pric<>0 AND t3.t_qono=t1.t_qono
			)
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public static function countStatsByConstraints($erp, $a = NULL)
    {
        if (empty($erp)) {
            return;
        }
        // Construct constraints
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = " WHERE $WHERE";
        }
        $query = <<<EOF
SELECT
	COUNT(CASE WHEN T1.t_qspa<4
		THEN 'Y'
		ELSE null
		END
	) AS nb_open
FROM
	dbo.ttdpur001$erp AS T1
$WHERE
EOF;
        return tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public static function countStatsByBuyerEmail($erp, $email)
    {
        if (empty($email)) {
            return;
        }
        $a = "(SELECT RTRIM(t6.t_info) FROM ttccom001$erp AS t6 WHERE t6.t_emno=T1.t_ccon)='$email'";
        return self::countStatsByConstraints($erp, $a);
    }

    public static function countStatsBySuno($erp, $suno)
    {
        if (empty($suno)) {
            return;
        }
        $a = "T1.t_suno='$suno'";
        return self::countStatsByConstraints($erp, $a);
    }

    public static function countStatsByItem($erp, $item)
    {
        if (empty($item)) {
            return;
        }
        $a = "(SELECT COUNT(*) FROM ttdpur002$erp AS rfqline WHERE T1.t_qono=rfqline.t_qono AND rfqline.t_item='$item')>0";
        return self::countStatsByConstraints($erp, $a);
    }
}

class tldERPCustomer
{
    public $itsID; //po num
    public $itsERP; //erp system id

    public function __construct($erp, $id)
    {
        $this->itsID = strtoupper($id);
        $this->itsERP = $erp;
        $this->itsHeader = $this->getHeader();
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    /**
     *
     * @return <type>
     */
    public function getHeader()
    {
        switch ($this->itsERP) {
            case '400':
            case '410':
                $com010ERP = 300;
                $com011ERP = 300;
                break;
            case '500':
            case '520':
            case '540':
                $com010ERP = 500;
                $com011ERP = $this->itsERP;
                break;
            default:
                $com010ERP = $this->itsERP;
                $com011ERP = $this->itsERP;
        }
        //****IMPORTANT table ttccom000300 has to be fixed at 300 because it
        //is shared to all companies
        $query = <<<EOF
SELECT cus.*,
    cusa.t_buin, cusa.t_buor,
    mcs.t_dsca AS cpay_fullname,
    com.t_ccur
FROM ttccom010$com010ERP AS cus
	LEFT JOIN ttccom011$com011ERP AS cusa ON cus.t_cuno=cusa.t_cuno AND cusa.t_comp=$this->itsERP
    LEFT JOIN ttcmcs013$this->itsERP AS mcs ON cus.t_cpay=mcs.t_cpay
    LEFT JOIN ttccom000300 AS com ON com.t_ncmp=$this->itsERP
WHERE cus.t_cuno='$this->itsID'
EOF;
        return tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
    }


    public static function byERPName($erp, $name)
    {
        if (empty($erp) || empty($name)) {
            return;
        }
        switch ($erp) {
            case '400':
            case '410':
                $com010ERP = 300;
                break;
            case '500':
            case '520':
            case '540':
                $com010ERP = 500;
                break;
            default:
                $com010ERP = $erp;
        }
        $name = strtoupper($name);
        $query = <<<EOF
SELECT cus.*
FROM ttccom010$com010ERP AS cus
WHERE UPPER(cus.t_nama) like '%$name%'
ORDER BY cus.t_nama
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     *
     * @return <type> 1 = normal
     * 2 = doubtful (order can be entered but will automatically be blocked)
     * 3 = blocked (cannot enter new order)
     */
    public function getStatus()
    {
        $a[1] = 'NORMAL';
        $a[2] = 'DOUBTFUL (order can be entered but will automatically be blocked)';
        $a[3] = 'BLOCKED (cannot enter new order)';
        return $a[$this->itsHeader['t_cnpa']];
    }

    public function getCurrency()
    {
        return $this->itsHeader['t_ccur'];
    }

    public function getOpenInvoiceBalance()
    {
        return round($this->itsHeader['t_buin'], 2);
    }

    public function getOpenOrderBalance()
    {
        return round($this->itsHeader['t_buor'], 2);
    }

    public function getTermsOfPayment()
    {
        return $this->itsHeader['cpay_fullname'];
    }

    public function getCreditLimit()
    {
        return $this->itsHeader['t_crlr'];
    }

    public function getCDELList()
    {
        return tldCDEL::byERPCUNO($this->itsERP, $this->itsID);
    }

    public function getCCORList()
    {
        return tldCCOR::byERPCUNO($this->itsERP, $this->itsID);
    }

    /**
     * Return all open INV statistics
     * @return array
     */
    public function getOpenInvoiceStats()
    {
        $a = "inv.t_cuno='$this->itsID' AND inv.t_balc>0.1";
        return tldINV::getStatsByConstraints($this->itsERP, $a);
    }

    /**
     * Return all open ORDER statistics
     * @return array
     */
    public function getOpenOrderStats()
    {
        $a = <<<EOF
sods.t_srnb=(SELECT MAX(t_srnb) FROM ttdsls045$this->itsERP
    WHERE t_orno=sols.t_orno AND t_pono=sols.t_pono
) AND sods.t_ssls<>7 AND sors.t_cuno='$this->itsID'
EOF;
        return tldSO::getStatsByConstraints($this->itsERP, $a);
    }

    public static function getCustomerList($sph)
    {
        if (in_array($sph, [520, 540])) {
            $sph = 500;
        }
        switch ($sph) {
            case 500:
            case 300:
            case 600:
            case 640:
            case 680:
                $query = <<<EOF
				SELECT DISTINCT
					RTRIM(t_cuno) AS t_cuno,
					RTRIM(t_nama) AS t_nama
				FROM ttccom010$sph
				ORDER BY t_nama
EOF;
                $opt1 = 'odbc';
                $opt2 = ['src' => 'baan'];
                break;
        }
        return tldUtils::getSqlToAssocArray($query, $opt1, $opt2);
    }

}

class tldCDEL
{
    public $itsID; //del num
    public $itsERP; //erp system id

    public function __construct($erp, $cuno, $id)
    {
        $this->itsID = $id;
        $this->itsCUNO = $cuno;
        $this->itsERP = $erp;
        $this->itsHeader = $this->getHeader();

    }

    public function getHeader()
    {
        switch ($this->itsERP) {
            case '520':
            case '540':
                $erp = 500;
                break;
            default:
                $erp = $this->itsERP;
        }
        $query = <<<EOF
SELECT
    t_cuno, t_cdel,
    RTRIM(t_nama) AS t_nama,
    RTRIM(t_namb) AS t_namb,
    RTRIM(t_namc) AS t_namc,
    RTRIM(t_namd) AS t_namd,
    RTRIM(t_name) AS t_name,
    RTRIM(t_namf) AS t_namf,
    RTRIM(t_ccty) AS t_ccty
FROM dbo.ttccom013$erp
WHERE
    t_cdel='$this->itsID'
    AND t_cuno='$this->itsCUNO'
EOF;
        return tldUtils::getSqlRowToAssocArray(
            $query,
            'odbc',
            [
                'src' => 'baan',
            ]
        );
    }

    public static function byERPCUNO($erp, $cuno)
    {
        switch ($erp) {
            case '520':
            case '540':
                $erp = 500;
                break;
        }
        $query = <<<EOF
SELECT
    t_cuno, t_cdel,
    RTRIM(t_nama) AS t_nama,
    RTRIM(t_namb) AS t_namb,
    RTRIM(t_namc) AS t_namc,
    RTRIM(t_namd) AS t_namd,
    RTRIM(t_name) AS t_name,
    RTRIM(t_namf) AS t_namf,
    RTRIM(t_ccty) AS t_ccty
FROM dbo.ttccom013$erp
WHERE t_cuno='$cuno'
EOF;
        return tldUtils::getSqlToAssocArray(
            $query,
            'odbc',
            [
                'src' => 'baan',
            ]
        );
    }
}

class tldCWAR
{

    public $itsID; //cwar num
    public $itsERP; //erp system id

    public function __construct($erp, $id)
    {
        $this->itsID = $id;
        $this->itsERP = $erp;
        $this->itsHeader = $this->getHeader();

    }

    public function getHeader()
    {
        $query = <<<EOF
SELECT *
FROM dbo.ttcmcs003$this->itsERP
WHERE t_cwar='$this->itsID'
EOF;
        return tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     * Get list of Warehouse by ERP
     * @param int|string $erp
     * @return array
     */
    public static function byERP($erp, $options = '')
    {
        $WHERE = '';
        if (isset($options['showAll']) && $options['showAll'] <> true) {
            $WHERE = 'AND t_nwrh=1';
        }
        $query = <<<EOF
SELECT * FROM dbo.ttcmcs003$erp
WHERE t_cwar NOT LIKE 'ZZZ' $WHERE
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }
}

class tldCCOR
{

    public $itsID;
    public $itsCUNO;
    public $itsERP;

    public function __construct($erp, $id, $cuno)
    {
        $this->itsID = $id;
        $this->itsERP = $erp;
        $this->itsCUNO = $cuno;
        $this->itsHeader = $this->getHeader();

    }

    public function getHeader()
    {
        $query = <<<EOF
SELECT
    t_cuno, t_ccor,
    RTRIM(t_nama) AS t_nama,
    RTRIM(t_namb) AS t_namb,
    RTRIM(t_namc) AS t_namc,
    RTRIM(t_namd) AS t_namd,
    RTRIM(t_name) AS t_name,
    RTRIM(t_namf) AS t_namf,
    RTRIM(t_ccty) AS t_ccty
FROM dbo.ttccom012$this->itsERP
WHERE t_ccor='$this->itsID'
AND t_cuno='$this->itsCUNO'
EOF;
        return tldUtils::getSqlRowToAssocArray(
            $query,
            'odbc',
            [
                'src' => 'baan',
            ]
        );
    }

    public static function byERPCUNO($erp, $cuno)
    {
        $query = <<<EOF
SELECT
    t_cuno, t_ccor,
    RTRIM(t_nama) AS t_nama,
    RTRIM(t_namb) AS t_namb,
    RTRIM(t_namc) AS t_namc,
    RTRIM(t_namd) AS t_namd,
    RTRIM(t_name) AS t_name,
    RTRIM(t_namf) AS t_namf,
    RTRIM(t_ccty) AS t_ccty
FROM dbo.ttccom012$erp
WHERE t_cuno='$cuno'
EOF;
        return tldUtils::getSqlToAssocArray(
            $query,
            'odbc',
            [
                'src' => 'baan',
            ]
        );
    }
}

class tldERPVendor
{

    //new thresholds for vendor reliability decided by TTS #746984
    const RELIABILITY_LOW_THRESHOLD = -15;
    const RELIABILITY_HIGH_THRESHOLD = 5;

    public $itsID;
    public $itsERP;
    public $itsHeader;

    public function __construct($id, $erp)
    {
        $this->itsID = $id;
        $this->itsERP = $erp;
        $this->itsHeader = $this->getHeader();
    }

    public function getHeader(): array
    {
        $erpOb = tldERP::getERPOb($this->itsERP);

        return $erpOb->getSupplierData($this->itsID);
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public function getBuyerEmail()
    {
        return $this->itsHeader['byr_email'];
    }

    public function getName()
    {
        return $this->itsHeader['t_nama'];
    }

    public function getCname()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }
    /**
     * Post invoice to BAAN
     * @param array $a
     * @return soapFault on error
     */
    public function postInvoice($a)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }


    public function getRevenuePastyears($erp, $a)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function getRevenueByConstraints($erp, $a)
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        $sql=<<<SQL
       SELECT t_suno,
       t_nama,
       t_ccon,
       comp_tccur,
       country,
       t_apry,
       ROUND(SUM(priceInDcur), 2) AS totalInDcur
FROM (
         SELECT supplier.t_suno                                                         AS t_suno,
                supplier.t_nama                                                         AS t_nama,
                poinv.t_apry                                                            AS t_apry,
                (SELECT RTRIM(t_info) FROM ttccom001$erp WHERE t_emno = supplier.t_ccon) AS t_ccon,
                comp.t_ccur                                                             AS comp_tccur,
                (SELECT RTRIM(t_dsca) FROM ttcmcs010$erp WHERE t_ccty = supplier.t_ccty) AS country,
                ROUND(CASE
                          WHEN poinv.t_ccur <> comp.t_ccur
                              THEN poinv.t_amtc * (
                              SELECT TOP 1 t_ratp
                              FROM ttcmcs008$erp
                              WHERE t_ccur = poinv.t_ccur
                                AND t_stdt <= poinv.t_pdat
                              ORDER BY t_stdt DESC
                          )
                          ELSE poinv.t_amtc
                          END, 2)
                                                                                        AS priceInDcur
         FROM ttccom020$erp AS supplier
                  LEFT JOIN ttccom000$erp AS comp ON t_ncmp = $erp
                  LEFT JOIN ttdpur046$erp AS poinv on poinv.t_suno = supplier.t_suno
         WHERE $WHERE
     )
         AS revenue
GROUP BY t_suno,
         t_nama,
         t_ccon,
         comp_tccur,
         country,
         t_apry
ORDER BY t_suno
SQL;
        return tldUtils::getSqlToAssocArray($sql, 'odbc', ['src' => 'baan']);
    }

    public static function getActivityByPeriodByConstraints($erp, $periodStart, $periodEnd, $a = '1=1')
    {
        $WHERE = tldUtils::constructWhere($a);
        if (!empty($WHERE)) {
            $WHERE = "AND $WHERE";
        }
        $reliabiltyLowThreshold = self::RELIABILITY_LOW_THRESHOLD;
        $reliabiltyHighThreshold = self::RELIABILITY_HIGH_THRESHOLD;
        $query = <<<EOF
SELECT
    SUP.t_suno AS t_suno,
	(SELECT	RTRIM(T1.t_info)  FROM ttccom001$erp AS T1	WHERE T1.t_emno=SUP.t_ccon) AS t_ccon,
    CASE
        WHEN SUP.t_sust=1 THEN 'Active'
        WHEN SUP.t_sust=2 THEN 'Potential'
        WHEN SUP.t_sust=3 THEN 'Purchase Entry Blocked'
        WHEN SUP.t_sust=4 THEN 'Payments Blocked'
        WHEN SUP.t_sust=5 THEN 'Paym. & Purch. Entr. Bl'
    END AS t_sust,
    SUP.t_cbrn,
    SUP.t_nama AS t_nama,
    CONVERT(VARCHAR(100), CAST(SUM(RECEPT.t_amnt) AS DECIMAL(15,2))) AS revenue,
    PO.t_ccur AS currency,
    ROUND(
        SUM(
            IIF(DATEDIFF(DAY, IIF(POL.t_ddtd != '1753-01-01', POL.t_ddtd, IIF(POL.t_ddtc != '1753-01-01', POL.t_ddtc, POL.t_ddta)), RECEPT.t_date) BETWEEN $reliabiltyLowThreshold AND $reliabiltyHighThreshold, 1, 0)
        )*100/count(*)
    ,2) AS reliability,
    COUNT(RECEPT.t_pono) AS nbLineDelivered,
    SUM(POL.t_dqua) AS total_delivery
FROM
    ttdpur041$erp AS POL
    LEFT JOIN dbo.ttimrp030$erp AS MRP ON POL.t_orno=MRP.t_orno AND POL.t_pono=MRP.t_pono AND MRP.t_koor=2
    LEFT JOIN ttdpur040$erp AS PO ON POL.t_orno=PO.t_orno
    LEFT JOIN ttccom020$erp AS SUP ON POL.t_suno=SUP.t_suno
    LEFT JOIN ttdpur045$erp AS RECEPT ON RECEPT.t_orno=POL.t_orno AND RECEPT.t_pono=POL.t_pono
WHERE
    RECEPT.t_date BETWEEN '$periodStart' AND '$periodEnd'
    AND RECEPT.t_reno>0
    $WHERE
GROUP BY
    SUP.t_suno,SUP.t_sust,SUP.t_cbrn,SUP.t_nama,PO.t_ccur,SUP.t_ccon
EOF;

        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     * Function to get OTDP stats from BAAN
     * @param int $erp company#
     * @param array|null $a constraint
     * @return array
     */
    public static function getOTDPByConstraints($erp, $a = null): array
    {
        if (empty($erp)) {
            return [];
        }
        $WHERE = '';
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "AND $WHERE";
        }
        $reliabiltyLowThreshold = self::RELIABILITY_LOW_THRESHOLD;
        $reliabiltyHighThreshold = self::RELIABILITY_HIGH_THRESHOLD;
        $query = <<<EOF
SELECT
	SUBSTRING(CONVERT(VARCHAR, T1.t_date, 120), 0 , 8) as period,
	COUNT(*) AS total_lines,
	SUM(
        IIF(DATEDIFF(DAY, IIF(T2.t_ddtd != '1753-01-01', T2.t_ddtd, IIF(T2.t_ddtc != '1753-01-01', T2.t_ddtc, T2.t_ddta)), T1.t_date) BETWEEN $reliabiltyLowThreshold AND $reliabiltyHighThreshold, 1, 0)
    ) as reliable_lines,
    ROUND(
        SUM(
            IIF(DATEDIFF(DAY, IIF(T2.t_ddtd != '1753-01-01', T2.t_ddtd, IIF(T2.t_ddtc != '1753-01-01', T2.t_ddtc, T2.t_ddta)), T1.t_date) BETWEEN $reliabiltyLowThreshold AND $reliabiltyHighThreshold, 1, 0)
        )*100/count(*)
	,2) AS reliability
FROM
	ttdpur045$erp AS T1
	JOIN ttdpur041$erp AS T2 ON T2.t_orno = T1.t_orno AND T2.t_pono = T1.t_pono
	JOIN ttdpur040$erp AS T3 ON T3.t_orno=T2.t_orno
WHERE
	T1.t_cwar<>'   '
	AND T1.t_srnb<>0
    $WHERE
GROUP BY SUBSTRING(CONVERT(VARCHAR, T1.t_date, 120), 0 , 8)
ORDER BY period

EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     * Function to get OTDP stats from BAAN
     * @param int $erp company#
     * @param array $a constraint
     * @return array
     */
    public static function getOTDPReliabilityFlexibility($erp, $a = null): array
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function getOTDPByPeriodByConstraints($erp, $start, $end, $a = NULL): array
    {
        $WHERE = "T1.t_date BETWEEN '$start' AND '$end'";
        if (is_array($a)) {
            $WHERE .= ' AND ' . tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE .= " AND $a";
        }
        return self::getOTDPByConstraints($erp, $WHERE);
    }

    public static function getOTDPByPeriodBySuno($erp, $start, $end, $suno): array
    {
        if (empty($suno)) {
            return [];
        }
        $a = "T1.t_suno='$suno'";
        return self::getOTDPByPeriodByConstraints($erp, $start, $end, $a);
    }

    public static function getOTDPByPeriodByBuyerEmail($erp, $start, $end, $email): array
    {
        if (empty($email)) {
            return [];
        }
        $a = "(SELECT RTRIM(t6.t_info) FROM ttccom001$erp AS t6 WHERE t6.t_emno=T3.t_ccon)='$email'";
        return self::getOTDPByPeriodByConstraints($erp, $start, $end, $a);
    }

    public static function getOTDPByPeriodByPart($erp, $start, $end, $pn): array
    {
        if (empty($pn)) {
            return [];
        }
        $a = "T1.t_item='$pn'";
        return self::getOTDPByPeriodByConstraints($erp, $start, $end, $a);
    }
}

/**
 * Class for manipulating and accessing vendor information
 *
 * @package ERP
 */
class tldVendor
{
    public $itsCreationTime;
    public $itsUsername;
    public $itsVendorid;
    public $itsDetails;
    public $itsMenu;
    public $itsBOM;
    public $itsBOMid;
    public $itsPOs;
    public $itsPONumbers;
    public $itsRFQs;
    public $itsRFQNumbers;
    public $itsERP;
    public $itsID;

    public function __construct($id)
    {
        if (is_numeric($id)) {
            $query = "SELECT * FROM vendors WHERE id=$id";
        } else {
            $query = "SELECT * FROM vendors WHERE userid='$id'";
        }
        $rows = tldUtils::getSqlToAssocArray($query);
        if (count($rows) === 1) {
            $this->itsHeader = $rows[0];
            $this->itsUsername = $this->itsHeader['userid'];
            $this->itsID = $this->itsHeader['id'];
            $this->itsVendorid = $this->itsHeader['vendorid'];
            $this->itsERP = $this->itsHeader['erp'];
        }
    }

    public function getHeader()
    {
        return $this->itsHeader;
    }

    public function changePassword($old, $new, $check)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function checkPassword($old, $new, $check)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function checkPODisplay($po, $erp)
    {
        if (empty($po)) {
            return 'PO# invalid or missing';
        }
        if (empty($erp)) {
            return 'ERP invalid or missing';
        }

        $where = '';
        foreach ($po as $key => $p) {
            if ($key === 0) {
                $where = 'id=' . $p;
            }
            else {
                $where .= ' OR id=' . $p;
            }
        }

        $query = "SELECT id FROM po_exten WHERE erp=$erp AND ($where)";
        $rows = tldUtils::getSqlToAssocArray($query);

        return array_column((array) $rows, 'id');
    }

    /** @return string */
    public static function checkPOSequence($po, $erp)
    {
        if (empty($po) || !is_numeric($po)) {
            return 'PO# invalid or missing';
        }
        if (empty($erp) || !is_numeric($erp)) {
            return 'ERP invalid or missing';
        }
        $query = <<<EOF
SELECT tasks.id, tasks.status, tasks_comments.status as approval_status
FROM tasks
LEFT JOIN tasks_comments ON tasks.id = tasks_comments.parent_id AND tasks_comments.step = 0
WHERE tasks.parent_id = $po AND tasks.erp = $erp AND tasks.module = 'SEQ'
ORDER BY tasks.id
EOF;

        $rows = tldUtils::getSqlToAssocArray($query);
        // $allow = "NO SEQ SO NOT ACCEPTED";
        $allow = 'ACCEPTED';
        if (!empty($rows)) {
            // Auto true
            $allow = 'ACCEPTED';
            foreach ($rows as $row) {
                // One of the SEQ is not CLOSED
                if ($row['status'] !== 'CLOSED') {
                    $allow = 'PENDING';
                }
            }
            // Latest SEQ (highest ID) was CANCELLED
            $last = end($rows);
            reset($rows);
            if ($last['status'] === 'CLOSED' && $last['approval_status'] !== 'END') {
                $allow = 'CANCELLED';
            }
        }
        return $allow;
    }

    public function updatePassword($old, $new)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Generic Vendor update method
     * @param $data array of vendor information
     * @param $fields array of vendor fields to update
     * @return string on error
     */
    public function update($data, $fields = '')
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Create a new Vendor SUNO for a Vendor
     * @param array $p
     * @return boolean
     */
    public static function insertSuno($p)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function refresh()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getFullLog()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }


    public function isEmpty()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function isShipper()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function outFile($id)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    //return array of purchase orders, empty array if none
    public function getPONums()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }


    public function getAllPOLines($field = '', $order = 'ASC')
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function outVcard()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }


    //return array of rfq, empty array if none
    public function getRFQNums()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getRFQ($id)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    //return array of rfq, empty array if none
    public function getVWCNums()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getDetails()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getDownloads($id = '')
    {
        if (empty($this->itsID)) {
            return 'Not object context';
        }
        if ($id != '') {
            $id = " AND manuals_downloads.id = $id";
        }
        $query = <<<EOF
SELECT
manuals_downloads.*,
manuals_downloads.id AS dwl_id,
(SELECT CONCAT(firstname,' ',lastname,' - #',id) FROM vendors WHERE vendors.id=manuals_downloads.vendor_id) AS vendor,
service.sn AS er_sn,
service.model AS model,
(SELECT customer_name FROM customers WHERE customers.id=service.customer_id) AS customer,
manuals_downloads.parent_id AS manual_id,
IF(manuals_downloads.downloaded = 0, 'No', 'Yes') AS downloaded, 
IF(service.date_shipped <> '0000-00-00','Yes','No') AS delivered 
FROM manuals_downloads
LEFT JOIN service ON service.id=manuals_downloads.er_id
WHERE manuals_downloads.vendor_id = $this->itsID AND manuals_downloads.hide = 0 $id
ORDER BY manuals_downloads.id
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function getID()
    {
        return $this->itsHeader['id'];
    }

    public function isAuthorized($er_id)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getManualDownloadID($er_id)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function isInGroup($group, $options = '')
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Get Vendor log
     * @return array
     */
    public function getLog()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Add eVendor log
     * @param int $id user
     * @param string $comment
     * @return boolean
     */
    public function addLogEntry($id, $comment)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Get class of vendor
     *
     * A = long leadtime vendor, needs to provide delivery and ship dates
     * B = need to provide delivery date
     *
     * @return mixed
     */
    public function getClass()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getVendorID()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getVendorIDS()
    {
        if (!$this->itsID || !is_numeric($this->itsID)) {
            return [];
        }
        $query = <<<EOF
		SELECT T1.*, T2.company_name
		FROM vendors_suno AS T1 LEFT JOIN locations AS T2 ON T1.erp=T2.erp
		WHERE T1.parent_id=$this->itsID
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get list of linked suno that belongs the vendor
     * @return mixed string error or array
     */
    public function getVendorsSuno()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getVendorsGroups()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function doNotSendPoAnswer($erp, $suno)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Create a new vendor group row in the database
     * @param array $p
     * @return boolean
     */
    public static function addGroup($p)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Get Vendor Group info by ID
     * @param int $id
     * @return row
     */
    public static function getGroupByID($id)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Delete Vendor Group
     * @return array
     */
    public function delGroup($id)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getUserID()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getTLDRepID($constraint = NULL)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getTLDRepEmail($id)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getERP()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getServer()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getEmail()
    {
        return $this->itsHeader['email'];
    }

    public function getFullName()
    {
        return $this->itsHeader['firstname'] . ' ' . $this->itsHeader['lastname'];
    }

    public function getCompanyName()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getAddress()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getPassword()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function toString()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getShipperList()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function getVendorList($erp = '', $order = 'erp,userid', $option = '', $option2 = '')
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function byERPVendorid($erp, $vendorID)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Get list of vendor by ERP and suno to send PO
     * Must have 'sales rep' group and not 'Do not send PO'
     * @param int $erp
     * @param string $vendorID
     * @return array or string error
     **/
    public static function byVendorPO($erp, $vendorID)
    {
        $query = <<<EOF
		SELECT T1.*, T2.*
		FROM vendors AS T1
		LEFT JOIN vendors_suno AS T2 ON T1.id=T2.parent_id
		LEFT JOIN vendors_groups AS T3 ON T2.id=T3.parent_id
		WHERE T2.erp=$erp AND T2.t_suno='$vendorID'
		AND T3.groupid LIKE 'gg_SALES'
		AND T1.enable LIKE 'Y'
		AND T1.id NOT IN(
			SELECT T1.id
			FROM vendors AS T1
			LEFT JOIN vendors_suno AS T2 ON T1.id=T2.parent_id
			LEFT JOIN vendors_groups AS T3 ON T2.id=T3.parent_id
			WHERE T2.erp=$erp AND T2.t_suno='$vendorID'
			AND T3.groupid LIKE 'fl_NO_PO'
		)
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get list of VendorType
     *
     * @return array
     */
    public static function byType($vendorType)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Get vendorType by type with smarty option as ID->comp_email
     * @param $vendorType array
     * @return array of data
     */
    public static function optionsByTypeAsCompanyEmail($vendorType)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Get list of vendor by ERP/suno to send VWC notification
     * Must have 'gg_QA' group and not 'fl_NO_VWC'
     * @param int $erp
     * @param string $suno
     * @return array or string error
     **/
    public static function byVendorVWC($erp, $suno)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Get list of vendor by TLD rep
     * @param int $id
     * @return array of rows
     */
    public static function byRepID($id)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Transfer/update Vendor reps
     * @param int $IDfrom
     * @param int $IDto
     * @return string on error
     */
    public static function transferRepFromTo($IDfrom, $IDto)
    {
        throw new Exception('This should not be used anymore');
    }

    public static function getSELECT(): string
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function getFROM(): string
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function getListByConstraints($a, $opt = NULL)
    {
        // constraints
        if (is_array($a)) {
            $HAVING = tldUtils::constructWhere($a);
        } else {
            $HAVING = $a;
        }
        if (!empty($HAVING)) {
            $HAVING = "HAVING $HAVING";
        }
        // options
        $ORDERBY = 'vendors.id';
        if (!empty($opt['orderBy'])) {
            $ORDERBY = $opt['orderBy'];
        }
        $query = <<<EOF
SELECT
    vendors.*,
    CONCAT(vendors.firstname,' ',vendors.lastname) AS fullname,
    suno.t_suno,
    suno.erp,
    suno.type AS type,
    (SELECT location FROM locations WHERE suno.erp=locations.erp) AS erp_fullname,
    suno.tld_rep_id,
    rep.id AS rep_id,
    rep.email AS rep_email,
    CONCAT(rep.firstname,' ',rep.lastname) AS rep_fullname,
    grp.groupid
FROM vendors
    LEFT JOIN vendors_suno AS suno ON suno.parent_id=vendors.id
    LEFT JOIN people AS rep ON rep.id=suno.tld_rep_id
    LEFT JOIN vendors_groups AS grp ON grp.parent_id=suno.id
$HAVING
ORDER BY $ORDERBY
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }


    /**
     * Get list of vendor by constraints
     * @param array $constraints
     * @param string $vendorID
     * @return array or string error
     **/
    public static function byConstraints($constraints, $orderBy = 'lastname,firstname')
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Notify all contacts linked to a ERP number and Suno
     * @param int $erp
     * @param string $vendorID
     * @param string $message
     * @param string $subject
     * @param mixed array or string $cc
     * @return string error or boolean
     */
    public static function notifyByERPVendor($to, $erp, $vendorID, $message, $subject = '', $cc = '')
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function notifyRep($id, $message, $subject = '', $cc = '')
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function notify($message, $subject = '', $cc = '')
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Search for a vendor
     * @param $a string
     * @return array or string if error
     */
    public static function search($a)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function updateVendorsRepBySupplierBU($bu_erp, $suno, $rep_id)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getUserConnectedByERPByDate($a){
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }
}

/**
 * Class for manipulating and access Vendor Warranty Claims
 *
 * @package ERP
 */
class tldVWC
{
    public $itsID; //vwc num
    public $theStatuses;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    public static function getStatuses()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }
    public static function getStatusList(): array
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function isEmpty()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function asArray()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getHeader()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getERP()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getSupplierERP()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getVendorID()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    function getSupplierContacts()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function getTLDRepID($suno = '', $erp = '')
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    //return array of purchase order lines, empty array if none
    public function getDetail($part_id = '')
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    //DELETE
    public function getNotes()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Create a new vwc and insert into table
     *
     * @param string $module
     * @param integer $parent_id
     * @param string $type
     * @param integer $erp
     * @param string $suno
     * @param integer $entered_by
     * @param integer $assignee
     * @return mixed integer on success with new row id, string with error
     */
    public static function insert($data)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function update($p)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function addPart($p)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function deletePart($part_id)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function updatePart($a, $part_id, $fields = '')
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }


    public function notifyNewAssignee($new, $old)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Attach a note to the VWC  DELETE
     *
     * @return bool
     */
    public function insertNote($poster, $note)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getModule()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getParentID()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getAssignee()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Set the assignee of the VWC
     *
     * @param integer id id of the new assignee
     * @return boolean
     */
    public function setAssignee($id)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Get status of VWC
     *
     * if $a is not empty then return the description of the status
     *
     * @param string $a
     * @return string
     */
    public function getStatus($a = '')
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function getFailureTypeList()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function getModuleList()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function getFailureSystemList()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function getTypeList()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getFollowers()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getType()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Returns whether a SCAR was requested
     *
     * @return string
     */
    public function isScarRequired()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    //get related WC
    public function getRelatedWC()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Get log of comments/history
     *
     * @return array
     */
    public function getLog()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Get full logs of comments/history (public and private)
     *
     * @return array
     */
    public function getFullLog()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getTasks($status = 'ALL')
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Add a comment to the log
     *
     * @param array Array structure containing 'poster' and 'comment'
     *
     * @return boolean
     */
    public function addLogEntry($id, $comment, $level = 0)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function setRequest($opt)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Get related SCAR number
     *
     * @return integer
     */
    public function getSCARNo()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Change status of the VWC
     *
     * If $status is empty then a list of all possible statuses will be returned. List
     * changes according to the current status
     *
     * @return array
     */
    public function changeStatus($status = '', $opt = '')
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function refresh()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function byVendorERP($vendorid, $erpcompany)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function getSELECT(): string
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function getFROM(): string
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function byConstraints($a, $opt = '')
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Get latest VWCs
     *
     * @return array Array of db rows
     */
    public static function byLatest($num = 10)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function byPartNumber($pn)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function byPartNumberBySixMonths($pn)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Get VWCs by parent
     *
     * @return array Array of db rows
     */
    public static function byParent($module, $id)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function byAssignee($id)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getAssignees()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function vwcPast12Month($suno)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function vwcValuePast12Month($suno)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function vwcCountPast12Month($suno,$erp)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Get VWCs by related SN
     *
     * @return array Array of db rows
     */
    public function bySN($sn)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * returns a 2 dim array of counts by erp and status
     *
     * @return array array of db rows
     */
    public static function byERPStatus($erp = '', $status = '', $sort = '')
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function byVendorStatus($vendor = '', $status = '', $sort = '')
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * returns a 2 dim array of counts by assignee and status
     *
     * @return array array of db rows
     */
    public static function byAssigneeStatus($erp, $status = '', $assignee = '', $sort = '')
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * returns a 2 dim array of counts by assignee_title and status
     *
     * @return array array of db rows
     */
    public static function byAssigneeTitleStatus($erp, $status = '', $title = '', $sort = '')
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * returns a 3 dim array of VWC's by erp and status and assignee
     *
     * @return array array of db rows
     */
    public static function byERPStatusAssignee($erp = '', $status = '', $assignee = '', $sort = '')
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function byQuery($params, $sort = '')
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * returns a 2 dim array of counts by buyer and status
     *
     * @return array array of db rows
     */
    public static function countByBuyerStatus($erp)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * returns a 2 dim array of counts by factory and status
     *
     * @return array array of db rows
     */
    public static function countByFactoryStatus($uid = '')
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function countByVendorStatus($suno)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    // Reports
    public static function VWCByConstraints($options = [])
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function report4e($x, $y, $z, $erp, $suno, $lookback = NULL)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function VWCCountByConstraints($x, $y, $options = [])
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function getVendor($vendor, $erp)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function getVendorPN($tld_pn, $erp, $cuno)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function getPartsValue($pn, $erp)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function getPartsSTDCost($pn, $erp)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getBuyerIDByParts()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getBuyerEmail()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getLinksFromHere($type = '')
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getLinksToHere($module = '')
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }
}

/**
 * base class for erp connector
 *
 * NOT FINISHED YET
 *
 * @package ERP
 */
class tldConnector
{
    public function __construct()
    {
        ;
    }

    public function postDoc()
    {
        ;
    }

    public function getDoc()
    {
        ;
    }

    public function getSmarty()
    {
        return tldUtils::getSmarty('common');
    }

    /**
     * Transform xml to array structure
     *
     * @param string xml  String of xml data to transform
     * @param string tpl , name of template to use for transform operation
     * @return array
     */
    public function xmlToArray($xml, $tpl, $options = '')
    {
        ;
    }

    /**
     * Transform array to xml document
     *
     * @param array $a Data array structure
     * @param string $tpl , name of template to use for transform operation
     * @return string
     */
    public function arrayToXML($a, $tpl, $options = '')
    {
        $smarty = $this->getSmarty();
        switch ($tpl) {
            case 'cxml.punchout.cart':
                //$a, the tldCart array
                //must include punchoutLoginRequest
                //****stdClass object***** per XML_unserializer output
                $smarty->assign('cart', $a);
                $result = $smarty->fetch("erp/$tpl.tpl");
                break;
        }
        return $result;
    }
}

/**
 * Delivery note class
 *
 * @package ERP
 */
class tldDINO
{
    public function __construct($id, $erp)
    {
        $this->itsID = $id;
        $this->itsERP = $erp;
    }

    public static function byCustomerERP($cuno, $erp, $options = '')
    {
        if (empty($options['getAll'])) {
            $SELECT = ' DISTINCT TOP 100 ';
        }
        switch ($erp) {
            default:
                $query = <<<EOF
				SELECT
                    $SELECT T1.t_dino,
                    SUBSTRING(convert(varchar, T1.t_ddat, 120), 0, 11) AS t_ddat,
                    T1.t_orno
				FROM ttdsls045$erp AS T1
				WHERE T1.t_cuno='$cuno'
        		ORDER BY T1.t_dino DESC
EOF;
                $opt2 = ['src' => 'baan'];
        }
        return tldUtils::getSqlToAssocArray($query, 'odbc', $opt2);
    }

    public function getDetail()
    {
        switch ($this->itsERP) {
            default:
                $query = <<<EOF
				SELECT
                T1.t_orno, T1.t_pono, T1.t_cuno, T1.t_item, ITM.t_dsca,ITM.t_cuqs,T1.t_pric, T1.t_amnt,
				SUBSTRING(convert(varchar, T1.t_ddat, 120), 0, 11) AS t_ddat,
				T1.t_oqua, T1.t_dqua, T1.t_bqua, T1.t_recq, T1.t_dino, T1.t_invd, T1.t_ttyp,
				T1.t_invn, T1.t_ssls,T1.t_ttyp + CAST(T1.t_invn AS char) as invoice,
				(select min(TLD890.t_dsca) from ttitld890400 TLD890 where TLD890.t_eitm=T1.t_item and TLD890.t_clan like '%CH%') t_dscb
				FROM ttdsls045$this->itsERP AS T1, ttiitm001$this->itsERP AS ITM
				WHERE T1.t_item=ITM.t_item
				AND T1.t_dino='$this->itsID'
		ORDER BY T1.t_pono
EOF;
                $opt2 = ['src' => 'baan'];
        }
        return tldUtils::getSqlToAssocArray($query, 'odbc', $opt2);
    }

    public function getCUNO()
    {
        return $this->itsHeader['t_cuno'];
    }

    public function getTRNO()
    {
        return tldDINOTRNO::byDINO($this->itsERP, $this->itsID);
    }

    public static function isPackingSlipSet($erp, $sono)
    {
        $query = <<<EOF
SELECT *
FROM
    warranty_tracking
WHERE
    erp={$erp} AND
    so_no={$sono}
ORDER BY id DESC
LIMIT 1
EOF;
        $rows = tldUtils::getSqlRowToAssocArray($query);
        return (isset($rows['so_no']) && $rows['packing_slip'] == 0);
    }

    public static function getHeader($erp, $sono)
    {
        $query = <<<EOF
SELECT *
FROM
    warranty_tracking
WHERE
    erp={$erp} AND
    so_no={$sono}
LIMIT 1
EOF;

        return tldUtils::getSqlRowToAssocArray($query);

    }
}

/**
 * Class to link tracking# to packing slip#
 *
 * @package ERP
 */
class tldDINOTRNO
{

    public $itsID;
    public $itsDetails;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsDetails = $this->getHeader();
    }

    /**
     * Get header record
     * @return row
     */
    public function getHeader()
    {
        $query = <<<EOF
SELECT *
FROM erp_dino_trno
WHERE id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Insert a new entry
     * @param int $erp
     * @param int $dino
     * @param string $courier
     * @param string $trno
     * @result int $id inserted or string error
     */
    public static function insert($erp, $dino, $courier, $trno)
    {
        switch ($courier) {
            case 'FED':
                $trnoCut = substr($trno, 22, 12);
                if (false === $trnoCut) {
                    $trnoCut = substr($trno, -15);
                }
                break;
            default:
                $trnoCut = $trno;
        }
        $query = <<<EOF
INSERT INTO erp_dino_trno
SET
    dt = NOW(),
    erp=$erp,
    dino=$dino,
    courier = '$courier',
    trno = '$trnoCut',
    trnoBAK='$trno'
EOF;
        return tldUtils::sqlInsert($query);
    }

    public static function isTrackingSet($erp, $dino)
    {
        $query = <<<EOF
SELECT parent_id
FROM
    warranty_tracking
WHERE
    erp=$erp AND
    packing_slip LIKE '$dino'
EOF;
        $rows = tldUtils::getSqlRowToAssocArray($query);

        return $rows['parent_id'];
    }

    /**
     * Get records by constraints fields
     * @param mixed string or array $a
     * @return array
     */
    public static function byConstraints($a)
    {
        if (empty($a)) {
            return;
        }
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        $query = <<<EOF
SELECT *,
    CASE courier
        WHEN 'UPS' THEN 'upswebsite'
        WHEN 'FED' THEN 'fedex'
    END AS courier_link
FROM erp_dino_trno
WHERE $WHERE
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get records by packing slip#
     * @param int $erp
     * @param int $dino
     * @return array
     */
    public static function byERPDINO($erp, $dino)
    {
        $a = "erp=$erp AND dino=$dino";
        return self::byConstraints($a);
    }

    /**
     * WARNING DEPRECATED !!!
     * Use tldDINOTRNO::byERPDINO instead !!!
     */
    public static function byDINO($erp, $dino)
    {
        $query = <<<EOF
SELECT *,
    CASE courier
        WHEN 'UPS' THEN 'upswebsite'
        WHEN 'FED' THEN 'fedex'
    END AS courier_link
FROM erp_dino_trno
WHERE erp=$erp AND dino='$dino'
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }
}

/**
 * Class for accessing AP Invoices in baan
 * @package ERP
 */
class tldAP
{
    public $itsID;
    public $itsERP;
    public $itsType;
    public $itsHeader;

    public function __construct($id, $erp, $type)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getERP()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getHeader()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function isEmpty()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function isMatched()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getBlocCode()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getLines()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getTPL()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getSEQDefaults()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function byLatestSeqRandom()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function byArchiveNotSequencedByPeriod($erp, $start, $end)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function byLatest($num = 10)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function setBlocCode($a)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function byConstraints($erp, $a, $opt = [])
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getEvendorsTransactionID()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }


    public static function getDoctypeByERP()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

}

/**
 * Class for accessing AP Invoices lines in ERP systems
 *
 * @package ERP
 */
class tldAPL
{

    public function __construct($id, $erp, $type)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function byParent($id, $erp, $type)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function byParents(array $ids, $erp, $type)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    private static function getSharedTable($erp)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }
}

/**
 * Class for accessing AP Approvers in ERP systems
 *
 * @package ERP
 */
class tldAPA
{

    public static function byLatest($count = 10, $erp = NULL)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function getRefByType($erp)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function insert($erp, $type, $approver, $ref_num, $bloc)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }
}

/**
 * Class for accessing Item data in ERP systems
 *
 * @package ERP
 */
class tldITM
{

    public function __construct($id, $erp)
    {
        $this->itsID = $id;
        $this->itsERP = $erp;
        $this->itsHeader = $this->getHeader();
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public function getHeader()
    {
        $query = <<<EOF
SELECT
    itm.*,
    CASE
        WHEN itm.t_bfcp=1 THEN 'YES'
        WHEN itm.t_bfcp=2 THEN 'NO'
    END AS bfcp,
    (SELECT RTRIM(byr.t_info) FROM ttccom001$this->itsERP AS byr 
        WHERE byr.t_emno=itm.t_buyr
    ) AS byr_email,
    (SELECT RTRIM(planner.t_info) FROM ttccom001$this->itsERP AS planner 
        WHERE planner.t_emno=itm.t_cplb
    ) AS planner_email
FROM 
    ttiitm001$this->itsERP AS itm
WHERE
    itm.t_item='$this->itsID'
EOF;
        return tldUtils::getSqlRowToAssocArray(
            $query,
            'odbc',
            ['src' => 'baan']
        );
    }

    public function getMRBStock($pn,$erp){
        switch ($erp) {
            case '220':
                $whse = "'BMR'";
                break;
            case '300':
                $whse = "'DDG','CCA'";
                break;
            case '250':
            case '400':
            case '410':
            case '420':
            case '600':
            case '640':
            case '660':
                $whse = "'MRB'";
                break;
            case '500':
                $whse = "'NCM'";
                break;
            case '520':
                $whse = "'NCS'";
                break;
            case '540':
                $whse = "'SP1'";
                $location ="'ZAP'";
                break;
            case '570':
                $whse = "'QUA'";
                break;
            case '620':
                $whse = "'GS3'";
                break;
            case '680':
                $whse = "'SP4'";
                break;
            default:
                $whse = "'MRB'";
                break;
        }
        $query =<<<EOF
        SELECT ROUND(SUM(inv.t_stoc),2) AS stoc, $whse as whse
        FROM dbo.ttdinv001$erp AS inv
        WHERE
            inv.t_cwar in ($whse)
            AND inv.t_item = '$pn'
        GROUP BY inv.t_item
EOF;
        return tldUtils::getSqlRowToAssocArray(
            $query,
            'odbc',
            ['src' => 'baan']
        );
    }

    public function getNonNetable()
    {
        $query = <<<EOF
SELECT
sum(inventory.t_stks) as nonnet
FROM
    ttiitm001$this->itsERP AS itm
LEFT JOIN ttdilc101$this->itsERP AS inventory ON inventory.t_item=itm.t_item
LEFT JOIN ttcmcs003$this->itsERP AS whs ON inventory.t_cwar=whs.t_cwar
WHERE
    itm.t_item='$this->itsID' AND whs.t_nwrh=1
group by itm.t_item
EOF;
        return tldUtils::getSqlRowToAssocArray(
            $query,
            'odbc',
            ['src' => 'baan']
        );
    }

    public function getHeaderBAK()
    {
        switch ($this->itsERP) {
            case '420':
                $query = <<<EOF
SELECT
    '$this->itsERP' AS ERP,
    itm.t_item AS ITEM,
    itm.t_dsca AS DESCRIPTION,
    CAST(ROUND(itm.t_wght,2) as DECIMAL(15,2)) AS WT,
    itm.t_cuqs AS UM,
    'USD' AS CURRENCY,
    sls.t_pric AS MIP,
    (SELECT t_ccur
        FROM ttccom000$this->itsERP
        WHERE t_ncmp=$this->itsERP
    ) AS STDCCUR,
    itm.t_copr AS STDCOST,
    itm.t_oltm AS LEAD,
    itm.t_slmp AS LSTDT,
    t_purc, t_avpr, t_ltpr,
    t_buyr
FROM dbo.ttiitm001$this->itsERP AS itm LEFT JOIN
    dbo.ttdsls032$this->itsERP AS sls
        ON itm.t_item=sls.t_item AND sls.t_cpls='USD'
WHERE
itm.t_item='$this->itsID'
EOF;
                break;
            case '640':
                $query = <<<EOF
SELECT
    '$this->itsERP' AS ERP,
    itm.t_item AS ITEM,
    itm.t_dsca AS DESCRIPTION,
    CAST(ROUND(itm.t_wght,2) as DECIMAL(15,2)) AS WT,
    itm.t_cuqs AS UM,
    'RMB' AS CURRENCY,
    sls.t_pric AS MIP,
    (SELECT t_ccur
        FROM ttccom000300
        WHERE t_ncmp=$this->itsERP
    ) AS STDCCUR,
    itm.t_copr AS STDCOST,
    itm.t_oltm AS LEAD,
    itm.t_slmp AS LSTDT,
    t_purc, t_avpr, t_ltpr,
    t_buyr
FROM dbo.ttiitm001$this->itsERP AS itm LEFT JOIN
    dbo.ttdsls032$this->itsERP AS sls
        ON itm.t_item=sls.t_item AND sls.t_cpls='RMB'
WHERE
itm.t_item='$this->itsID'
EOF;
                break;
            default:
                $query = <<<EOF
SELECT
    '$this->itsERP' AS ERP,
    itm.t_item AS ITEM,
    itm.t_dsca AS DESCRIPTION,
    CAST(ROUND(itm.t_wght,2) as DECIMAL(15,2)) AS WT,
    itm.t_cuqs AS UM,
    (SELECT t_ccur
        FROM ttccom000$this->itsERP
        WHERE t_ncmp=$this->itsERP
    ) AS CURRENCY,
    itm.t_pris AS MIP,
    (SELECT t_ccur
        FROM ttccom000$this->itsERP
        WHERE t_ncmp=$this->itsERP
    ) AS STDCCUR,
    itm.t_copr AS STDCOST,
    itm.t_oltm AS LEAD,
    itm.t_slmp AS LSTDT,
    t_purc, t_avpr, t_ltpr,
    t_buyr
FROM dbo.ttiitm001$this->itsERP AS itm
WHERE
itm.t_item='$this->itsID'
EOF;
        }
        return tldUtils::getSqlRowToAssocArray(
            $query,
            'odbc',
            ['src' => 'baan']
        );
    }

    /**
     * Sync translation of BAAN items to mysql
     * in parts_trans for the alt desc in manuals
     */
    public static function syncTranslations()
    {
        // Get all translations
        $query = <<<EOF
		SELECT *, ltrim(t_clan) as t_clan
		FROM ttitld890400
		WHERE t_dsca<>''
EOF;
        $rows = tldUtils::getSqlToAssocArray(
            $query, 'odbc', ['src' => 'baan']
        );
        // If no translations, nothing to do...
        if (count($rows) < 1) {
            return;
        }

        // Foreach translations from BAAN, update/add translation to intranet
        foreach ($rows as $row) {
            // Encode description in UTF8
            if ($row['t_clan'] === 'CH') {
                $dsc = iconv('CP936', 'UTF-8', $row['t_dsca']);
            } else {
                $dsc = $row['t_dsca'];
            }
            // Escape
            $row = tldUtils::cleanupFormInput($row);
            $dsc = TldDatabase::escape($dsc);
            // Prepare query
            $query = <<<EOF
		    INSERT INTO parts_trans (dt,t_eitm,t_clan,t_dsca)
		    VALUES (NOW(),'{$row['t_eitm']}','{$row['t_clan']}','$dsc')
			ON DUPLICATE KEY UPDATE t_dsca='$dsc'
EOF;
            // Update desc item
            $e = tldUtils::sqlExecute($query);
            if (is_string($e)) {
                error_log("{$row['t_eitm']} translation in {$row['t_clan']} not done -> $e");
            }
        }
        return;
    }

    /**
     * POST an Item reservation to BAAN
     * @param $erp int
     * @param $a array
     */
    public static function postEdmItemReservation($erp, $a)
    {
        $baan = new tldBaanERP($erp);
        $soap = $baan->getSOAP();
        try {
            $return = $soap->postEdmItemReservation($a);
        } catch (Exception $ex) {
            return $ex->faultstring;
        }
        return $return;
    }

    public function getAlternativeItemByConstraints($a = '1=1')
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        $query = <<<EOF
SELECT *
FROM ttiitm012$this->itsERP
WHERE t_item='$this->itsID'
AND $WHERE
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public function getHeaderWithReadableItemType()
    {
        $data = $this->getHeader();
        switch ($data['t_kitm']) {
            case '1':
                $readableItemType = _('Purchased');
                break;
            case '2':
                $readableItemType = _('Manufactured');
                break;
            case '3':
                $readableItemType = _('Generic');
                break;
            case '4':
                $readableItemType = _('Cost');
                break;
            case '5':
                $readableItemType = _('Service');
                break;
            case '6':
                $readableItemType = _('Subcontracting');
                break;
            default:
                $readableItemType = '';
        }
        $data['t_kitm'] = $readableItemType;
        return $data;
    }

}

/**
 * Class for accessing Interco Shipping Module records
 *
 * @package ERP
 */
class tldISR
{

    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Get ISR header
     * @return row or string error
     */
    public function getHeader()
    {
        if (empty($this->itsID) || !is_numeric($this->itsID)) {
            return 'emtpy or invalid id';
        }
        $query = <<<EOF
            SELECT
            	isr.*,
            	CONCAT(people.firstname,' ',people.lastname) AS poster_fullname,
                bu_from.location AS bu_from_fullname,
                bu_from.erp AS bu_from_erp,
                bu_to.location AS bu_to_fullname,
                bu_to.erp AS bu_to_erp
            FROM isr
            	LEFT JOIN locations AS bu_from ON bu_from.id=isr.bu_from_id
            	LEFT JOIN locations AS bu_to ON bu_to.id=isr.bu_to_id
            	LEFT JOIN people ON people.id=isr.poster_id
            WHERE
            	isr.id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Check if ISR header is empty
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    /**
     * Check if ISR header is empty
     * @return boolean
     */
    public function isFullyDocAttached()
    {
        $docs = ['doc_ship', 'doc_qa', 'doc_inv'];
        foreach ($docs as $doc) {
            if (empty($this->itsHeader[$doc])) {
                return true;
            }
            $file = tldUtils::getPathToUploadFile('isr', $this->itsHeader[$doc]);
            if (!is_file($file)) {
                return true;
            }
        }
        return TRUE;
    }

    /**
     * Get generic module data for the ISR:
     * tasks - links - files - log
     * @return array
     */
    public function getTasks()
    {
        return tldTask::byParent($this->itsID, 'ISR', 'ALL');
    }

    public function getLinks()
    {
        return tldModLink::byParent($this->itsID, 'ISR');
    }

    public function getFiles()
    {
        return tldModFile::byParent($this->itsID, 'ISR');
    }

    public function getLog()
    {
        return tldModLog::byParent($this->itsID, 'ISR');
    }

    /**
     * Add a comment to the log
     * @param array Array structure containing 'poster' and 'comment'
     * @return boolean
     */
    public function addLogEntry($id, $comment)
    {
        if (!is_numeric($id) || empty($comment)) {
            return 'Invalid parameters';
        }
        $a['parent_id'] = $this->itsID;
        $a['module'] = 'ISR';
        $a['poster'] = $id;
        $a['comment'] = $comment;
        return tldModLog::insert($a);
    }

    /**
     * Create a new ISR
     * @param array $p
     * @return int or string error
     */
    public static function insert($p)
    {
        if (empty($p)) {
            return 'Empty parameter';
        }
        $fields = ['poster_id', 'bu_from_id', 'bu_to_id', 'ttype', 'cnum', 'tnum',
            'cuno', 'dt_outb', 'dt_ship', 'dt_eta', 'notes', 'doc_ship', 'doc_qa', 'doc_inv', 'container_type'
        ];
        $query = "INSERT INTO isr SET dt=NOW(),status='PENDING',";
        $query .= tldUtils::getSqlSet($p, $fields);
        return tldUtils::sqlInsert($query);
    }

    /**
     * Update ISR header
     * @param array $p
     * @return id update or string error message
     */
    public function update($p)
    {
        if (empty($this->itsID) || empty($p)) {
            return 'Empty parameter';
        }
        $fields = ['poster_id', 'bu_from_id', 'bu_to_id', 'ttype', 'cnum', 'tnum',
            'cuno', 'dt_outb', 'dt_ship', 'dt_eta', 'notes', 'doc_ship', 'doc_qa', 'doc_inv', 'container_type',
        ];
        // Sanity check
        foreach ($fields as $k => $field) {
            if (!in_array($field, array_keys($p))) {
                unset($fields[$k]);
            }
        }
        $query = 'UPDATE isr SET ';
        $query .= tldUtils::getSqlSet($p, $fields);
        $query .= " WHERE id=$this->itsID LIMIT 1";

        return tldUtils::sqlQuery($query);
    }

    /**
     * Delete ISR record
     * @return string on error
     */
    public function delete()
    {
        if (empty($this->itsID)) {
            return 'Not in object context';
        }
        $query = "DELETE FROM isr WHERE id=$this->itsID LIMIT 1";
        return tldUtils::sqlQuery($query);
    }

    /**
     * Get ISR status
     * @return string
     */
    public function getStatus()
    {
        return $this->itsHeader['status'];
    }

    /**
     * Get ISR allowed status
     * @return string
     */
    public function getAllowedStatus()
    {
        if (empty($this->itsID)) {
            return;
        }
        switch ($this->getStatus()) {
            case 'PENDING':
                return ['IN PROGRESS','CANCELLED'];
                break;
            case 'IN PROGRESS':
                return ['PENDING','CLOSED'];
                break;
            case 'CLOSED':
                return ['IN PROGRESS'];
                break;
            case 'CANCELLED':
                return ['PENDING'];
                break;
        }
        return;
    }

    /**
     * Update ISR status
     * @return string on error
     */
    public function changeStatus($status)
    {
        if (empty($this->itsID)) {
            return 'Not in object context';
        }
        if (!in_array($status, $this->getAllowedStatus())) {
            return 'Status not allowed';
        }
        $query = "UPDATE isr SET status='$status' WHERE id=$this->itsID";
        return tldUtils::sqlQuery($query);
    }

    /**
     * Get ISR line
     * @return array of rows
     */
    public function getLines()
    {
        return tldISRL::byParent($this->itsID);
    }

    /**
     * Generic method to construct SELECT query
     */
    public static function getSELECT(): string
    {
        return <<<EOF
isr.*,
CONCAT(people.firstname,' ',people.lastname) AS poster_fullname,
bu_from.location AS bu_from_fullname,
bu_to.location AS bu_to_fullname
EOF;
    }

    /**
     * Generic method to construct FROM query
     */
    public static function getFROM(): string
    {
        return <<<EOF
isr
LEFT JOIN locations AS bu_from ON bu_from.id=isr.bu_from_id
LEFT JOIN locations AS bu_to ON bu_to.id=isr.bu_to_id
LEFT JOIN people ON people.id=isr.poster_id
EOF;
    }

    /**
     * Get ISR stats by BU from Status
     * @return array
     */
    public static function countByBuFromStatus()
    {
        $query = <<<EOF
    		SELECT
    			isr.status,
    			locations.location,
            	count(*) AS num
            FROM isr
            	LEFT JOIN locations ON isr.bu_from_id=locations.id
            GROUP BY isr.status, location
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get ISR stats by BU to Status
     * @return array
     */
    public static function countByBuToStatus()
    {
        $query = <<<EOF
    		SELECT
    			isr.status,
    			locations.location,
            	count(*) AS num
            FROM isr
            	LEFT JOIN locations ON isr.bu_to_id=locations.id
            GROUP BY isr.status, location
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function countByPNByBuToStatus($bu,$pn)
    {
        $query = <<<EOF
        select 
        isr.*,
        CONCAT(people.firstname,' ',people.lastname) AS poster_fullname,
        bu_from.location AS bu_from_fullname,
        bu_to.location AS bu_to_fullname
        FROM isr
        LEFT JOIN locations AS bu_from ON bu_from.id=isr.bu_from_id
        LEFT JOIN locations AS bu_to ON bu_to.id=isr.bu_to_id
        LEFT JOIN people ON people.id=isr.poster_id
        WHERE status LIKE 'IN PROGRESS' and bu_to.id= $bu
EOF;

        $rows = tldUtils::getSqlToAssocArray($query);
        foreach ($rows as $k=>$item) {
            $isr = new tldISR($item['id']);
            $header = $isr->getHeader();
            foreach($isr->getLines() as $key=>$ps) {
                // Get packing slip info to get SO#
                $dino = new tldDINO($ps['t_dino'], $header['bu_from_erp']);
                $e = new tldBaanERP($header['bu_from_erp']);
                foreach ($dino->getDetail() as $detail) {
                    // Get the item information
                    if(trim($pn) === trim($detail['t_item'])){
                        $i += $detail['t_dqua'];
                    }
                }
            }
        }
        return $i;
    }
    /**
     * Get ISR list by Entity Status
     * @param string from location
     * @param string status
     * @return array
     */
    public static function byBuFromStatus($bu, $status)
    {
        if ($bu !== 'ALL') {
            $a['bu_from_fullname'] = $bu;
        }
        if ($status !== 'ALL') {
            $a['status'] = $status;
        }
        return self::byConstraints($a);
    }

    /**
     * Get ISR list by Entity Status
     * @param string from location
     * @param string status
     * @return array
     */
    public static function byBuToStatus($bu, $status)
    {
        if ($bu !== 'ALL') {
            $a['bu_to_fullname'] = $bu;
        }
        if ($status !== 'ALL') {
            $a['status'] = $status;
        }
        return self::byConstraints($a);
    }

    /**
     * Get ISR records by latest
     * @param int $num
     */
    public static function byLatest($num = 10)
    {
        if (!is_numeric($num)) {
            return 'Invalid parameter';
        }
        $query = <<<EOF
        SELECT
        	isr.*,
        	CONCAT(people.firstname,' ',people.lastname) AS poster_fullname,
            bu_from.location AS bu_from_fullname,
            bu_to.location AS bu_to_fullname
        FROM isr
            LEFT JOIN locations AS bu_from ON bu_from.id=isr.bu_from_id
            LEFT JOIN locations AS bu_to ON bu_to.id=isr.bu_to_id
            LEFT JOIN people ON people.id=isr.poster_id
        ORDER BY id DESC LIMIT $num
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get Late ISR, today against estimate date of arrival
     * @return array
     */
    public static function byLate()
    {
        $a = " status<>'CLOSED' AND DATEDIFF(NOW(),dt_eta)>0 ";
        return self::byConstraints($a);
    }

    /**
     * Get ISR with missing shipping document
     * @return array
     */
    public static function ByMissingDoc()
    {
        $a = " doc_ship='' OR doc_qa='' OR doc_inv='' ";
        return self::byConstraints($a);
    }

    /**
     * Get ISR records by constraints
     * @param array $a
     * @return array or string on error
     */
    public static function byConstraints($a)
    {
        if (is_array($a)) {
            $a = tldUtils::constructWhere($a);
        }
        if (!empty($a)) {
            $WHERE = " HAVING $a";
        }
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
SELECT $SELECT
FROM $FROM
$WHERE
ORDER BY isr.id
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get ISR records by constraints
     * @param array $p
     * @return array or string on error
     */
    public static function search($target)
    {
        if (empty($target)) {
            return;
        }
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
        SELECT $SELECT
        FROM $FROM
        HAVING
        	bu_from_fullname LIKE '%$target%'
        	OR bu_to_fullname LIKE '%$target%'
        	OR cnum LIKE '%$target%'
        	OR tnum LIKE '%$target%'
        	OR cuno LIKE '%$target%'
        ORDER BY isr.id
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get ISR records by constraints
     * @param array $p
     * @return array or string on error
     */
    public static function advSearch($target, $erp,$erpfrom)
    {
        if (empty($target)) {
            return;
        }
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $erpid = tldLocation::getIDByERP($erp);
        $WHERE = '';
        if ($erpfrom !== 'ALL'){
            $erpfromid = tldLocation::getIDByERP($erpfrom);
            $WHERE = ' and bu_from_id = '.$erpfromid;
        }
        if ($erp !== 'ALL'){
            $erpfromid = tldLocation::getIDByERP($erpfrom);
            $WHERE = ' and bu_to_id = '.$erpid;
        }
        $query = <<<EOF
        SELECT $SELECT
        FROM $FROM
        LEFT JOIN isr_lines on isr_lines.parent_id=isr.id
        WHERE isr_lines.t_dino in ($target)  
        $WHERE
        GROUP BY isr.id
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function notify($message, $subject = '', $cc = '', $to = '')
    {
        return tldUtils::emailAttachment('webmaster@tld-gse.com', 'noreply@tld-gse.com', $subject, $message, NULL, $cc, '', '', $to);
    }
}

/**
 * Class for accessing Interco Shipping Module lines records
 * @package ERP
 */
class tldISRL
{

    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Get ISR line header
     * @return row or string error
     */
    public function getHeader()
    {
        if (empty($this->itsID) || !is_numeric($this->itsID)) {
            return 'emtpy or invalid id';
        }
        $query = <<<EOF
        SELECT *
        FROM isr_lines
        WHERE id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Create a new ISR line
     * @param array $p
     * @return int or string error
     */
    public static function insert($p)
    {
        if (empty($p)) {
            return 'Empty parameter';
        }
        $fields = ['parent_id', 't_dino'];
        $query = 'INSERT INTO isr_lines SET ';
        $query .= tldUtils::getSqlSet($p, $fields);
        return tldUtils::sqlInsert($query);
    }

    /**
     * Update ISR line
     * @param array $p
     * @return id update or string error message
     */
    public function update($p)
    {
        if (empty($this->itsID) || empty($p)) {
            return 'Empty parameter';
        }
        $fields = ['parent_id', 't_dino'];
        // Sanity check
        foreach ($fields as $k => $field) {
            if (!in_array($field, array_keys($p))) {
                unset($fields[$k]);
            }
        }
        $query = 'UPDATE isr_lines SET ';
        $query .= tldUtils::getSqlSet($p, $fields);
        $query .= "WHERE id=$this->itsID LIMIT 1";
        return tldUtils::sqlQuery($query);
    }

    /**
     * Delete Line record
     * @return string on error
     */
    public static function delete($lid)
    {
        $query = "DELETE FROM isr_lines WHERE id=$lid LIMIT 1";
        return tldUtils::sqlQuery($query);
    }

    public static function byParent($pid)
    {
        if (empty($pid) || !is_numeric($pid)) {
            return 'Parent id empty or invalid';
        }
        return self::byConstraints(['parent_id' => $pid]);
    }

    public static function byConstraints($a)
    {
        if (is_array($a)) {
            $a = tldUtils::constructWhere($a);
        }
        $WHERE = " HAVING $a";
        $query = <<<EOF
SELECT *
FROM isr_lines
$WHERE
ORDER BY id
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }
}

/**
 *
 * baan inventory location class
 */
class tldILC
{
    public function byERPItem($erp, $item)
    {
        $query = <<<EOF
select *
from ttdilc101$erp
WHERE t_item='$item'
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }
}

/**
 * Class for ERP messages communication
 *
 * @package ERP
 */
class tldERPMSG
{

    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Get ERP message header
     * @return row data
     */
    public function getHeader()
    {
        if (empty($this->itsID)) {
            return;
        }
        $query = <<<EOF
SELECT
	erp_msg.*,
	bu_from.location AS from_fullname,
	bu_to.location AS to_fullname
FROM erp_msg
	LEFT JOIN locations AS bu_from ON bu_from.erp=erp_msg.erp_from
	LEFT JOIN locations AS bu_to ON bu_to.erp=erp_msg.erp_to
WHERE
	erp_msg.id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Get ERP message data
     * @return string
     */
    public function getData()
    {
        return unserialize(base64_decode($this->itsHeader['data']));
    }

    /**
     * Check if ERP message is empty
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    /**
     * Check if ERP message is closed
     * @return boolean
     */
    public function isClosed()
    {
        return $this->itsHeader['status'] === 'CLOSED';
    }

    /**
     * Add log to a msg
     * @param int $id
     * @param string $comment
     * @return string on error
     */
    public function addLogEntry($id, $comment)
    {
        $a['parent_id'] = $this->itsID;
        $a['module'] = 'MSG';
        $a['poster'] = $id;
        $a['comment'] = $comment;
        return tldModLog::insert($a);
    }

    /**
     * Get log MSG
     * @return array of row
     */
    public function getLog()
    {
        return tldModLog::byParent($this->itsID, 'MSG');
    }

    /**
     * Insert ERP message
     * @param array $a
     * @return int on success, string on error
     */
    public static function insert($a)
    {
        if (empty($a)) {
            return;
        }
        $fields = ['parent_id', 'erp_from', 'erp_to', 'doc_type', 'eparts_order', 'pono', 'status'];
        $DATA = base64_encode(serialize($a['data']));
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = <<<EOF
INSERT INTO erp_msg
SET dt=NOW(), data='$DATA', $SET
EOF;
        return tldUtils::sqlInsert($query);
    }

    /**
     * Generic method to update a record
     * @param array $data
     * @param array $fields
     * @return string on error
     */
    public function updateRecord($data, $fields)
    {
        if (empty($this->itsID)) {
            return 'Not object context';
        }
        if (empty($data) || empty($fields)) {
            return 'Parameters invalid';
        }
        // Sanity check regarding fields on data
        foreach ($fields as $field) {
            if (!isset($data[$field])) {
                return "Sanity check failed with field $field";
            }
        }
        // Construct query
        $SET = tldUtils::getSqlSet($data, $fields);
        $query = <<<EOF
            UPDATE erp_msg SET $SET
            WHERE id=$this->itsID
            LIMIT 1
EOF;
        return tldUtils::sqlQuery($query);
    }

    /**
     * Update ERP message status
     * @param string $status
     * @return string in error
     */
    public function changeStatus($status)
    {
        if (empty($status)) {
            return 'Parameters empty';
        }
        if (!in_array($status, ['PENDING', 'CLOSED'])) {
            return 'Status not allowed';
        }
        return $this->updateRecord(
            ['status' => $status],
            ['status']
        );
    }

    /**
     * Get number of MSG by ERP, status
     * @return array
     */
    public static function countByERPStatus()
    {
        $query = <<<EOF
SELECT
	COUNT(*) AS nb,
	bu_to.location AS to_fullname,
	erp_msg.status
FROM erp_msg
	LEFT JOIN locations AS bu_from ON bu_from.erp=erp_msg.erp_from
	LEFT JOIN locations AS bu_to ON bu_to.erp=erp_msg.erp_to
GROUP BY
	to_fullname, status
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get list of MSG by ERP to, status
     * @param string $erp
     * @param string $status
     * @return array of rows
     */
    public static function byERPStatus($erp, $status)
    {
        if ($erp !== 'ALL') {
            $a['to_fullname'] = $erp;
        }
        if ($status !== 'ALL') {
            $a['status'] = $status;
        }
        if (empty($a)) {
            $a = '1=1';
        }
        return self::byConstraints($a);
    }

    /**
     * Get latest MSG
     * @param int $limit
     * @return array of rows
     */
    public static function byLatest($limit = 10)
    {
        if (!is_numeric($limit)) {
            return;
        }
        $query = <<<EOF
SELECT
	erp_msg.*,
	bu_from.location AS from_fullname,
	bu_to.location AS to_fullname
FROM erp_msg
	LEFT JOIN locations AS bu_from ON bu_from.erp=erp_msg.erp_from
	LEFT JOIN locations AS bu_to ON bu_to.erp=erp_msg.erp_to
ORDER BY erp_msg.id DESC
LIMIT $limit
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get ERP message by constraints
     * @param array or string $a
     * @param array $opts
     * @return array of rows
     */
    public static function byConstraints($a, $opts = [])
    {
        if (empty($a)) {
            return;
        }
        // Construct constraints
        $WHERE = 'HAVING ';
        $WHERE .= is_array($a) ? tldUtils::constructWhere($a) : $a;


        // Look for options
        if (empty($opts['orderBy'])) {
            $opts['orderBy'] = 'erp_msg.id, to_fullname, from_fullname';
        }
        $ORDERBY = $opts['orderBy'];
        // Make query
        $query = <<<EOF
SELECT
	erp_msg.*,
	bu_from.location AS from_fullname,
	bu_to.location AS to_fullname
FROM erp_msg
	LEFT JOIN locations AS bu_from ON bu_from.erp=erp_msg.erp_from
	LEFT JOIN locations AS bu_to ON bu_to.erp=erp_msg.erp_to
$WHERE
ORDER BY
	$ORDERBY
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

}


class tldServiceOrder
{

    public $itsID;
    public $itsERP;

    public function __construct($id, $erp)
    {
        $this->itsID = $id;
        $this->itsERP = $erp;
        $this->itsHeader = $this->getHeader();
        $this->trimHeader();
    }

    public function getID()
    {
        return $this->itsID;
    }

    public function getERP()
    {
        return $this->itsERP;
    }

    public function getStatus()
    {
        return $this->itsHeader['t_swor'];
    }

    public function getOrderSeries()
    {
        return $this->itsHeader['orderSeries'];
    }

    public function getOrderSeriesDescription()
    {
        return $this->itsHeader['orderSeriesDesc'];
    }

    public function getInstallationSN()
    {
        return $this->itsHeader['t_cins'];
    }

    public function getHourmeter()
    {
        return $this->itsHeader['t_coun'];
    }

    public function getLocation()
    {
        return $this->itsHeader['t_cloc'];
    }

    public function getPosterUser()
    {
        return $this->itsHeader['t_user'];
    }

    public function getTechEmail()
    {
        return $this->itsHeader['tech_email'];
    }

    public function getWorkDate()
    {
        return $this->itsHeader['dt_work'];
    }

    public function getContactEmail()
    {
        return $this->itsHeader['t_refe'];
    }

    public function getDescription()
    {
        return $this->itsHeader['t_desc'];
    }

    public function getFixTypeDescription()
    {
        return $this->itsHeader['fix_desc'];
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public static function getSELECT($erp)
    {
        return <<<EOF
SELECT
    so.*,
    '$erp' AS erp,
    LEFT(so.t_orno,2) AS orderSeries,
    (SELECT t_dsca FROM ttcmcs047$erp
        WHERE t_ckon=19 AND t_grno=LEFT(so.t_orno,2)
    ) AS orderSeriesDesc,
    (SELECT TOP 1 t_info FROM ttccom001$erp
        WHERE t_emno=so.t_emno
    ) AS tech_email,
    (SELECT TOP 1 t_date FROM ttssma305$erp
        WHERE t_orno=so.t_orno ORDER BY t_date ASC
    ) AS dt_work,
    inst.t_coun,
    job.t_desc AS job_desc,
    (SELECT t_desc FROM ttsspf103$erp
        WHERE t_cfix=so.t_cfix
    ) AS fix_desc,
    (SELECT t_desc FROM ttsspf101$erp
        WHERE t_csym=so.t_csym
    ) AS symptom_desc,
    (SELECT t_desc FROM ttsspf102$erp
        WHERE t_cprl=so.t_cprl
    ) AS problem_desc
EOF;
    }

    public static function getFROM($erp)
    {
        return <<<EOF
FROM
    ttssma301$erp AS so
    LEFT JOIN ttssma102$erp AS inst ON inst.t_cins=so.t_cins
    LEFT JOIN ttssma120$erp AS job ON job.t_cjob=so.t_cjob
EOF;
    }

    public function getHeader()
    {
        $SELECT = self::getSELECT($this->getERP());
        $FROM = self::getFROM($this->getERP());
        $query = "$SELECT $FROM WHERE so.t_orno=$this->itsID";
        return tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public function trimHeader()
    {
        if (!empty($this->itsHeader)) {
            foreach ($this->itsHeader as $key => $val) {
                $this->itsHeader[$key] = trim($val);
            }
        }
    }

    public static function getOrderSeriesList($erp)
    {
        switch ($erp) {
            default:
                $query = "SELECT *,RTRIM(t_dsca) AS t_dsca FROM ttcmcs047$erp WHERE t_ckon=19";
                break;
        }
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public static function getFixList($erp)
    {
        $query = "SELECT * FROM ttsspf103$erp ORDER BY t_desc";
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public static function getFixListAsRefDesc($erp)
    {
        return array_column(self::getFixList($erp), 't_desc', 't_cfix');
    }

    public static function byConstraints($erp, $a, $opt = [])
    {
        $SELECT = self::getSELECT($erp);
        $FROM = self::getFROM($erp);
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        // Options
        if (!empty($opt['orderBy'])) {
            $ORDERBY = $opt['orderBy'];
        } else {
            $ORDERBY = 'so.t_orno';
        }
        $query = <<<EOF
$SELECT
$FROM
WHERE $WHERE
ORDER BY $ORDERBY
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public static function byContractID($erp, $cid)
    {
        $a = "LTRIM(so.t_ccon) LIKE LTRIM('$cid')";
        return self::byConstraints($erp, $a);
    }

    public function getParts()
    {
        switch ($this->itsERP) {
            case 331:
            default:
                $query = <<<EOF
SELECT
    soPart.*,
    itm.t_cuni,
    itm.t_dsca
FROM
    ttdinv700$this->itsERP AS soPart
    LEFT JOIN ttiitm001$this->itsERP AS itm ON itm.t_item=soPart.t_item
WHERE
    soPart.t_koor=17
    AND soPart.t_orno=$this->itsID
EOF;
                break;
        }
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public function getLabours()
    {
        switch ($this->itsERP) {
            case 331:
            default:
                $query = <<<EOF
SELECT
    hra.*,
    SUBSTRING(convert(varchar, hra.t_hrdt, 120), 0, 11) AS t_hrdt
FROM
    ttihra100$this->itsERP AS hra
WHERE
    hra.t_koht=3 AND hra.t_pdno=$this->itsID
ORDER BY
    hra.t_hrdt
EOF;
                break;
        }
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public function getNotesByConstraints($a)
    {
        // Get notes
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        $query = <<<EOF
SELECT
    txtHeader.*,
    SUBSTRING(CONVERT(VARCHAR, txtHeader.t_ludt, 120), 0 , 11) AS dt,
    txtLines.t_text
FROM
    ttttxt002300 AS txtHeader
    LEFT JOIN ttttxt010300 AS txtLines ON txtHeader.t_ctxt=txtLines.t_ctxt AND txtLines.t_clan=txtHeader.t_clan
WHERE
    txtHeader.t_clan LIKE '2' AND
    $WHERE
ORDER BY
    t_ludt
EOF;
        $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
        // Group notes lines
        $result = [];
        $lastPK = '';
        foreach ($rows as $row) {
            $actualPK = $row['t_ctxt'] . $row['t_clan'];
            // if new TXT header, create new entry
            if ($lastPK != $actualPK) {
                $result[$actualPK] = $row;
            } // if same header, concat text
            else {
                $result[$actualPK]['t_text'] .= $row['t_text'];
            }
            // record last key (to trigger header change)
            $lastPK = $row['t_ctxt'] . $row['t_clan'];
        }
        return $result;
    }

    public function getCallNotes()
    {
        return $this->getNotesByConstraints(['txtHeader.t_ctxt' => $this->itsHeader['t_txta']]);
    }

    public function getRepairNotes()
    {
        return $this->getNotesByConstraints(['txtHeader.t_ctxt' => $this->itsHeader['t_tjob']]);
    }

    public function getJobSheetNotes()
    {
        return $this->getNotesByConstraints(['txtHeader.t_ctxt' => $this->itsHeader['t_txtb']]);
    }

    public function getCSRID()
    {
        $rows = tldCSR::byConstraints([
            'sso.erp' => $this->getERP(),
            'csr.module_id' => $this->getID(),
            'csr.module' => 'SRVO',
        ]);
        return $rows[0]['id'];
    }

}

/**
 * Class Maintenance Service Contract
 *
 * @package ERP
 */
class tldSCM
{

    public $itsID;
    public $itsERP;

    public function __construct($id, $erp)
    {
        $this->itsID = $id;
        $this->itsERP = $erp;
        $this->itsHeader = $this->getHeader();
        $this->trimHeader();
    }

    public static function getAllowedERP()
    {
        return [
            //331,  // TLD AME Service (dev)
            330     // TLD AME Service (prod)
        ];
    }

    public function getID()
    {
        return $this->itsID;
    }

    public function getERP()
    {
        return $this->itsERP;
    }

    public function getCuno()
    {
        return $this->itsHeader['t_cuno'];
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public static function getStatusList()
    {
        return [
            1 => 'Quotation',
            2 => 'Contract',
            3 => 'Blocked',
            4 => 'Canceled',
        ];
    }

    public static function getSELECT($erp)
    {
        return <<<EOF
SELECT
    scm.*,
    LTRIM(t_ccon) AS t_ccon,
    '$erp' AS erp,
    CASE
        WHEN t_ctst=1 THEN 'Quotation'
        WHEN t_ctst=2 THEN 'Contract'
        WHEN t_ctst=3 THEN 'Blocked'
        WHEN t_ctst=4 THEN 'Canceled'
    END AS status,
    SUBSTRING(convert(varchar,t_sdat,120), 0, 11) AS t_sdat,
    SUBSTRING(convert(varchar,t_edat,120), 0, 11) AS t_edat
EOF;
    }

    public static function getFROM($erp)
    {
        return <<<EOF
FROM
    ttssma220$erp AS scm
EOF;
    }

    public function getHeader()
    {
        $SELECT = self::getSELECT($this->getERP());
        $FROM = self::getFROM($this->getERP());
        $query = "$SELECT $FROM WHERE LTRIM(scm.t_ccon) LIKE '$this->itsID'";
        return tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public function trimHeader()
    {
        foreach ($this->itsHeader as $key => $val) {
            $this->itsHeader[$key] = trim($val);
        }
    }

    public static function byConstraints($erp, $a, $opt = [])
    {
        $SELECT = self::getSELECT($erp);
        $FROM = self::getFROM($erp);
        $WHERE = is_array($a) ? tldUtils::constructWhere($a) : $a;

        // Options
        $ORDERBY = !empty($opt['orderBy']) ? $opt['orderBy'] : 'scm.t_ccon';

        if (!empty($opt['limit'])) {
            $SELECT = str_replace('SELECT ', "SELECT TOP {$opt['limit']} ,", $SELECT);
        }
        $query = <<<EOF
$SELECT
$FROM
WHERE $WHERE
ORDER BY $ORDERBY
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public static function countByERPStatus()
    {
        $rows = [];
        foreach (self::getAllowedERP() as $erp) {
            $query = <<<EOF
SELECT
    COUNT(*) AS num,
    '$erp' AS erp,
    t_ctst,
    CASE
        WHEN t_ctst=1 THEN 'Quotation'
        WHEN t_ctst=2 THEN 'Contract'
        WHEN t_ctst=3 THEN 'Blocked'
        WHEN t_ctst=4 THEN 'Canceled'
    END AS status
FROM
    ttssma220$erp
GROUP BY
    t_ctst
EOF;
            $rows[] = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
        }
        return array_merge(...$rows);
    }

    public static function byERPStatus($erp, $status)
    {
        $a = <<<EOF
'$status' LIKE (CASE
    WHEN t_ctst=1 THEN 'Quotation'
    WHEN t_ctst=2 THEN 'Contract'
    WHEN t_ctst=3 THEN 'Blocked'
    WHEN t_ctst=4 THEN 'Canceled'
END)
EOF;
        return self::byConstraints($erp, $a);
    }

    public static function getLatestByERP($erp)
    {
        return self::byConstraints($erp, 'scm.t_ctst=2', ['limit' => 5, 'orderBy' => 'scm.t_sdat']);
    }

    public static function getMemberTypeList()
    {
        return [
            'er_status_not' => 'ER Status Notification',
            'er_fleet_not' => 'ER Fleet Daily Notification',
            'tld_service_rep' => 'TLD Service Contract Representative',
            'toc_not_cc' => 'Additional TOC notification recipients (TLD people)',
            'ext_toc_not_cc' => 'Additional TOC notification recipients (Customer contact)',
        ];
    }

    public function addMember($a)
    {
        if (empty($a)) {
            return;
        }
        $a['erp'] = $this->getERP();
        $a['t_ccon'] = $this->getID();
        $fields = ['erp', 't_ccon', 'user_id', 'type'];
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO scm_members SET $SET";
        return tldUtils::sqlInsert($query);
    }

    public function getMember($id)
    {
        $rows = $this->getMembersByConstraints("id=$id");
        return $rows[0];
    }

    public function deleteMember($id)
    {
        $query = "DELETE FROM scm_members WHERE id=$id AND erp={$this->getERP()} AND t_ccon='{$this->getID()}' LIMIT 1";
        return tldUtils::sqlExecute($query);
    }

    public function getMembersByConstraints($a)
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "AND $WHERE";
        }
        if ('' !== $this->getERP()) {
            $WHERE = sprintf('%s AND scm_members.erp=%s ', $WHERE, $this->getERP());
        }
        $query = <<<EOF
SELECT
    *,
    CASE
        WHEN type IN ('er_fleet_not','er_status_not','ext_toc_not_cc') THEN
            (SELECT CONCAT(firstname,' ',lastname) FROM extranet_users WHERE id=scm_members.user_id)
        ELSE
            (SELECT CONCAT(firstname,' ',lastname) FROM people WHERE id=scm_members.user_id)
    END AS user_fullname,
    CASE
        WHEN type IN ('er_fleet_not','er_status_not','ext_toc_not_cc') THEN
            (SELECT email FROM extranet_users WHERE id=scm_members.user_id)
        ELSE
            (SELECT email FROM people WHERE id=scm_members.user_id)
    END AS user_email
FROM
    scm_members
WHERE
    scm_members.t_ccon='{$this->getID()}'
    $WHERE
ORDER BY
    user_fullname
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function getERStatusNotificationMembers()
    {
        return $this->getMembersByConstraints(['type' => 'er_status_not']);
    }

    public function getERFleetDailyNotificationMembers()
    {
        return $this->getMembersByConstraints(['type' => 'er_fleet_not']);
    }

    public function getTLDServiceRepMembers()
    {
        return $this->getMembersByConstraints(['type' => 'tld_service_rep']);
    }

    public function getTOCNotMembers()
    {
        return $this->getMembersByConstraints(['type' => 'toc_not_cc']);
    }

    public function getExternalTOCNotMembers()
    {
        return $this->getMembersByConstraints(['type' => 'ext_toc_not_cc']);
    }

    public static function getSCMModuleFiles()
    {
        return tldModFile::byParent(0, 'SCM');
    }
}


class tldWTT
{

    public $itsERP;

    public function __construct($erp)
    {
        $this->itsERP = $erp;
    }

    public function getList()
    {
        $query = "SELECT t_cwtt,t_dsca FROM ttihra110$this->itsERP ORDER BY t_dsca";
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

}

/**
 * Class for manipulating and accessing eVendor companies information
 *
 * @package ERP
 */
class tldEvendorCompany
{
    private $erp = 0;
    private $suno = '';
    private $infos = [];
    private $id = null;

    /**
     * tldEvendorCompany constructor.
     *
     * @param int $erp
     * @param string $suno
     */
    public function __construct($erp, $suno, $id = null, $lazy = false)
    {
        $this->erp = $erp;
        $this->suno = $suno;
        if (!$lazy) {
            $this->infos = $this->getCompanyClassificationInformation();
            if (empty($this->infos)) {
                throw new Exception('This module has been migrated and this function should not be used anymore');
                $query = <<<SQL
		INSERT INTO supp_classification (t_suno, erp) VALUE ('$this->suno','$this->erp');
SQL;
                tldUtils::sqlQuery($query);
                $this->infos = $this->getCompanyClassificationInformation();
            }
        }

        $this->id = $id ?? $this->infos['id'];
    }

    /**
     * getCompanyClassificationInformation return an assoc array with information
     * or a string if an error occurred
     *
     * @return mixed|string
     */
    public function getCompanyClassificationInformation()
    {
        if (empty($this->infos)) {
            $query = <<<SQL
		SELECT class.id, 
		class.t_suno,
		class.erp,
		class.quality AS 'class_quality',
		class.delivery AS 'class_delivery',
		class.communication AS 'class_communication',
		class.support AS 'class_support',
		class.innovation AS 'class_innovation',
		class.cost AS 'class_cost',
		class.esg AS 'class_esg',
		class.anti_corruption AS 'class_anti_corruption',
		class.last_eval,
		CONCAT(people.firstname, ' ' ,people.lastname) AS last_eval_user,
		master_suno,
		master_erp,
		exper.name AS 'exper_name',
		exper.description AS 'exper_description',
		exper.quality AS 'exper_quality',
		exper.delivery AS 'exper_delivery',
		exper.communication AS 'exper_communication',
		exper.support AS 'exper_support',
		exper.innovation AS 'exper_innovation',
		exper.cost AS 'exper_cost',
		exper.esg AS 'exper_esg',
		exper.anti_corruption AS 'exper_anti_corruption',
		frequency_period,
		rank,
		pur.description AS 'pur_description',
		frequence_ranking,
		approved,
		supp_pur_status_id,
		supp_exper_class_id, 
       class.esg_status,
       class.screening_status,
       class.last_screening_date
		FROM supp_classification AS class
		LEFT JOIN supp_pur_status AS pur ON supp_pur_status_id = pur.id
		LEFT JOIN supp_exper_class AS exper ON supp_exper_class_id = exper.id
		LEFT JOIN people on last_eval_user=people.id
		WHERE erp = '$this->erp' AND t_suno = '$this->suno'
SQL;

            return tldUtils::getSqlRowToAssocArray($query);
        }

        return $this->infos;
    }

    public static function getCompanyListClassificationInformation($list)
    {
        if (empty($list)) {
            return [];
        }
        $in = [];
        foreach ($list as $comp) {
            $in[] = "( erp='{$comp['to_erp']}' AND t_suno='{$comp['to_suno']}' )";
        }
        $in = implode(' OR ', array_unique($in));

        $query = <<<SQL
		SELECT class.id, 
		class.t_suno,
		class.erp,
		class.quality AS 'class_quality',
		class.delivery AS 'class_delivery',
		class.communication AS 'class_communication',
		class.support AS 'class_support',
		class.innovation AS 'class_innovation',
		class.cost AS 'class_cost',
		class.esg AS 'class_esg',
		class.anti_corruption AS 'class_anti_corruption',
		class.last_eval,
		class.esg_status,
		class.screening_status,
		class.last_screening_date,
		CONCAT(people.firstname, ' ' ,people.lastname) AS last_eval_user,
		master_suno,
		master_erp,
		exper.name AS 'exper_name',
		exper.description AS 'exper_description',
		exper.quality AS 'exper_quality',
		exper.delivery AS 'exper_delivery',
		exper.communication AS 'exper_communication',
		exper.support AS 'exper_support',
		exper.innovation AS 'exper_innovation',
		exper.cost AS 'exper_cost',
		exper.esg AS 'exper_esg',
		exper.anti_corruption AS 'exper_anti_corruption',
		frequency_period,
		rank,
		pur.description AS 'pur_description',
		frequence_ranking,
		approved,
		supp_pur_status_id,
		supp_exper_class_id,
		(SELECT comment FROM mod_logs WHERE mod_logs.parent_id=class.id AND mod_logs.module LIKE 'eVendor-C' AND log_num='1' ORDER BY id DESC LIMIT 1) as 'comment'
		FROM supp_classification AS class
		LEFT JOIN supp_pur_status AS pur ON supp_pur_status_id = pur.id
		LEFT JOIN supp_exper_class AS exper ON supp_exper_class_id = exper.id
		LEFT JOIN people on last_eval_user=people.id
		WHERE $in
SQL;
        $result = tldUtils::getSqlToAssocArray($query);
        array_walk($result, static function (&$item, $key) {
            if (!empty($item['last_eval']) && !empty($item['frequency_period']) && !empty($item['frequence_ranking'])) {
                $months = (int)$item['frequency_period'] * (int)$item['frequence_ranking'];
                $date = strtotime($item['last_eval'] . ' + ' . $months . ' months');
                $item['next_eval'] = date('Y-m-d', $date);
                $check_date = $date - strtotime(date('Y-m-d'));
                $item['next_eval_check'] = 0;
                if ($check_date <= 0) {
                    $item['next_eval_check'] = 1;
                }
            }
        });

        foreach ($list as $key=>$comp) {
            $unset = -1;
            foreach ($result as $index=>$info) {
                if ($comp['to_erp'] == $info['erp'] && $comp['to_suno'] == $info['t_suno']) {
                    $list[$key]['info'] = $info;
                    $unset = $index;
                    break;
                }
            }
            if ($unset != -1) {
                unset($result[$unset]);
            }
        }
        return $list;
    }

    /**
     * @param int $erp
     * @param string $suno
     * @param string $order
     * @param string $option
     * @param array $option2
     * @return array
     */
    public static function getCompanyList($erp = 0, $suno = '', $order = 't_suno', $option = '', $option2 = [])
    {
        $where = '';
        if (!empty($suno)) {
            $where .= " AND t_suno='$suno' ";
        }
        if ($erp) {
            $where .= " AND erp=$erp ";
        }
        $query = <<<SQL
		SELECT DISTINCT t_suno, erp, CONCAT(people.firstname, ' ' ,people.lastname) AS rep_name, tld_rep_id, t_nama 
		FROM vendors_suno 
		LEFT JOIN people on tld_rep_id=people.id
        LEFT JOIN vendors on vendors_suno.parent_id=vendors.id
		WHERE t_suno != '' AND enable = 'Y'
		$where
		ORDER BY $order
SQL;
        return tldUtils::getSqlToAssocArray($query, $option, $option2);
    }

    /**
     * @return array
     */
    public static function getExpertLevel()
    {
        $query = <<<SQL
		SELECT *
		FROM supp_exper_class
SQL;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * getReport return an assoc array with information
     * or a string if an error occurred
     *
     * @param array $erpList
     * @return array|mixed
     */
    public static function getReport($erpList)
    {
        $where = [];
        foreach ($erpList as $erp) {
            $where[] = "erp='$erp'";

        }
        $where = 'AND ( ' . implode(' OR ', $where) . ' )';
        $query = <<<SQL
		SELECT DISTINCT t_suno, erp, CONCAT(people.firstname, ' ' ,people.lastname) AS rep_name, t_nama 
		FROM vendors_suno 
		LEFT JOIN people on tld_rep_id=people.id
        LEFT JOIN vendors on vendors_suno.parent_id=vendors.id
		WHERE t_suno != '' AND enable = 'Y'
		$where
		ORDER BY erp, t_suno
SQL;

        $rows = tldUtils::getSqlToAssocArray($query);
        if (empty($rows) || is_string($rows)) {
            return [];
        }
        $rows = self::areSlaves($rows, true);

        $where = [];
        $whereBaan = [];
        foreach ($rows as $row) {
            $where[] = "( erp='{$row['erp']}' AND t_suno='{$row['t_suno']}' )";
            $whereBaan[$row['erp']][] = "'" . $row['t_suno'] . "'";
        }

        $where = implode(' OR ', array_unique($where));

        $query = <<<SQL
		SELECT class.id, 
		class.t_suno,
		class.erp,
		class.quality AS 'class_quality',
		class.delivery AS 'class_delivery',
		class.communication AS 'class_communication',
		class.support AS 'class_support',
		class.innovation AS 'class_innovation',
		class.cost AS 'class_cost',
		class.esg AS 'class_esg',
		class.anti_corruption AS 'class_anti_corruption',
		class.last_eval,
		class.esg_status,
		class.screening_status,
		class.last_screening_date,
		CONCAT(people.firstname, ' ' ,people.lastname) AS last_eval_user,
		master_suno,
		master_erp,
		exper.name AS 'exper_name',
		exper.description AS 'exper_description',
		exper.quality AS 'exper_quality',
		exper.delivery AS 'exper_delivery',
		exper.communication AS 'exper_communication',
		exper.support AS 'exper_support',
		exper.innovation AS 'exper_innovation',
		exper.esg AS 'exper_esg',
		exper.anti_corruption AS 'exper_anti_corruption',
		exper.cost AS 'exper_cost',
		frequency_period,
		rank,
		pur.description AS 'pur_description',
		frequence_ranking,
		approved,
		supp_pur_status_id,
		supp_exper_class_id,
		(SELECT comment FROM mod_logs WHERE mod_logs.parent_id=class.id AND mod_logs.module LIKE 'eVendor-C' AND log_num='1' ORDER BY id DESC LIMIT 1) as 'comment'
		FROM supp_classification AS class
		LEFT JOIN supp_pur_status AS pur ON supp_pur_status_id = pur.id
		LEFT JOIN supp_exper_class AS exper ON supp_exper_class_id = exper.id
		LEFT JOIN people on last_eval_user=people.id
		WHERE $where
SQL;
        $res = tldUtils::getSqlToAssocArray($query);
        $betterResults = [];
        foreach ($res as $item) {
            $betterResults[$item['erp']][$item['t_suno']] = $item;
        }

        $where = str_replace(['erp=', 't_suno='], ['vendor_erp=', 'vendor_id='], $where);
        $query = <<<SQL
        SELECT vendor_id, vendor_erp, COUNT(id) AS total_ncr 
        FROM ncr 
        WHERE ($where) 
        AND ncr.date BETWEEN DATE_SUB(NOW(), INTERVAL 12 MONTH) AND NOW() 
        GROUP BY vendor_id, vendor_erp;
SQL;
        $ncrs = tldUtils::getSqlToAssocArray($query);
        foreach ($ncrs as $ncr) {
            if ($betterResults[$ncr['vendor_erp']][trim($ncr['vendor_id'])] ?? []) {
                $betterResults[$ncr['vendor_erp']][trim($ncr['vendor_id'])]['ncr'] = $ncr['total_ncr'];
            }
        }
        $turnover = $lastYearTurnover = [];
        foreach ($erpList as $erp) {
            $whereBaan[$erp] = implode(',', array_unique($whereBaan[$erp]));
            $constraints = "t_pdat BETWEEN DATEADD(month, -12, GETDATE()) AND GETDATE() AND supplier.t_suno IN ({$whereBaan[$erp]})";
            foreach (tldERPVendor::getRevenueByConstraints($erp, $constraints) as $row) {
                $turnover[$erp][trim($row['t_suno'])] = $row;
            }
            $lastYear = date('Y')-1;
            $constraints = "t_pdat >= '$lastYear-01-01' AND t_pdat <= '$lastYear-12-31'  AND supplier.t_suno IN ({$whereBaan[$erp]})";
            foreach (tldERPVendor::getRevenueByConstraints($erp, $constraints) as $row) {
                $lastYearTurnover[$erp][trim($row['t_suno'])] = $row;
            }
        }
        foreach ($rows as $k => &$row) {
            if ($betterResults[$row['erp']][$row['t_suno']] ?? []) {
                $row = array_merge($row, $betterResults[$row['erp']][$row['t_suno']]);
            }
            if ($turnover[$row['erp']][$row['t_suno']] ?? []) {
                $row['currency'] = $turnover[$row['erp']][$row['t_suno']]['comp_tccur'];
                $row['money_12'] = $turnover[$row['erp']][$row['t_suno']]['totalInDcur'];
                $row['country'] = $turnover[$row['erp']][$row['t_suno']]['country'];
            }
            if ($lastYearTurnover[$row['erp']][$row['t_suno']] ?? []) {
                $row['currency'] = $lastYearTurnover[$row['erp']][$row['t_suno']]['comp_tccur'];
                $row['turnover_previous_year'] = $lastYearTurnover[$row['erp']][$row['t_suno']]['totalInDcur'];
            }
            if (!empty($row['last_eval']) && !empty($row['frequency_period']) && !empty($row['frequence_ranking'])) {
                $months = (int)$row['frequency_period'] * (int)$row['frequence_ranking'];
                $date = new \DateTime("{$row['last_eval']} +$months months");
                $row['next_eval'] = $date->format('Y-m-d');
                $row['next_eval_check'] = $date < new \DateTime();
            }
        }

        return $rows;
    }

    /***
     * @param $erp
     * @return array
     */
    public static function getAnnualReportFiles($erp)
    {
        $query = <<<SQL
SELECT
  mod_files.id,
  mod_files.parent_id,
  mod_files.module,
  mod_files.description,
  mod_files.fid,
  mod_files.level,
  file.dt,
  DATE(file.dt) AS date,
  file.poster AS poster_fullname,
  file.filepath,
  file.size,
  file.filename,
  file.extension,
  concat(firstname,' ',lastname) as poster,
  '$erp' AS erp
FROM mod_files
  LEFT JOIN file ON file.id=mod_files.fid
  LEFT JOIN people on people.id=mod_files.poster
where mod_files.parent_id = '$erp' AND module = 'eVendor-R'
ORDER BY date DESC;
SQL;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * @param array $slave_list
     * @param bool $inv_logic
     * @return mixed
     */
    public static function areSlaves($slave_list, $inv_logic = false)
    {
        $where = [];
        foreach ($slave_list as $slave) {
            $where[] = "(t_suno = '" . $slave['t_suno'] . "' AND erp = '" . $slave['erp'] . "')";
        }
        $where = implode(' OR ', array_unique($where));

        $query = <<<SQL
		SELECT t_suno,
		erp,
		master_suno,
		master_erp
		FROM supp_classification
		WHERE $where
		HAVING master_suno != '' AND master_erp != ''
SQL;

        $res = tldUtils::getSqlToAssocArray($query);
        $check_res = [];
        foreach ($res as $val) {
            $check_res[$val['erp']][$val['t_suno']] = 'Y';
        }
        array_walk($slave_list, static function (&$item, $key, $tab) {
            $inv_logic = $tab[1];
            $tab = $tab[0];
            if (!empty($tab[$item['erp']]) && $tab[$item['erp']][$item['t_suno']] === 'Y') {
                $item['slave'] = true;
                if ($inv_logic) {
                    $item['url'] = '<a href="/en/private/manufacturing/pur/dev.php?m[0]=vendors&m[1]=master&suno=' . $item['t_suno'] . '&erp=' . $item['erp'] . '">No</a>';
                } else {
                    $item['url'] = '<a href="/en/private/manufacturing/pur/dev.php?m[0]=vendors&m[1]=master&suno=' . $item['t_suno'] . '&erp=' . $item['erp'] . '">Yes</a>';
                }
            } else {
                $item['slave'] = false;
                if ($inv_logic) {
                    $item['url'] = '<a href="/en/private/manufacturing/pur/dev.php?m[0]=vendors&m[1]=master&suno=' . $item['t_suno'] . '&erp=' . $item['erp'] . '">Yes</a>';
                } else {
                    $item['url'] = '<a href="/en/private/manufacturing/pur/dev.php?m[0]=vendors&m[1]=master&suno=' . $item['t_suno'] . '&erp=' . $item['erp'] . '">No</a>';
                }
            }
        }, [$check_res, $inv_logic]);

        return $slave_list;
    }

    /**
     * @return int
     */
    public function getErp()
    {
        return $this->erp;
    }

    /**
     * @return string
     */
    public function getSuno()
    {
        return $this->suno;
    }

    /**
     * @return bool
     */
    public function isSlave()
    {
        if (empty($this->infos)) {
            $this->getCompanyClassificationInformation();
        }
        return !empty($this->infos['master_erp']) && !empty($this->infos['master_suno']);
    }

    /**
     * @param int $erp
     * @param string $suno
     * @return string
     */
    public function setSlave($erp, $suno)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');

        if (empty($erp) || empty($suno)) {
            $query = <<<SQL
		UPDATE supp_classification SET master_suno = null, master_erp = null WHERE id = '{$this->id}'
SQL;
        } else {
            $query = <<<SQL
		UPDATE supp_classification SET master_suno = '$suno', master_erp = '$erp' WHERE id = '{$this->id}'
SQL;
        }
        return tldUtils::sqlQuery($query);
    }

    /**
     * @param int $cost
     * @param int $quality
     * @param int $delivery
     * @param int $communication
     * @param int $innovation
     * @param int $support
     * @param int $user_id
     * @return string
     */
    public function updateNotation($cost, $quality, $delivery, $communication, $innovation, $support, $esg, $anti_corruption, $user_id)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * refreshInformation return an Assoc array
     * or a string if an error occurred
     *
     * @return mixed|string
     */
    public function refreshInformation()
    {
        $this->infos = [];
        $this->infos = $this->getCompanyClassificationInformation();
        return $this->infos;
    }

    /**
     * @return array
     */
    public function getPossibleStatus()
    {
        $statuses = [];
        if (empty($this->infos) || empty($this->infos['supp_exper_class_id'])) {
            $statuses = ['Monitored'];
        } else {
            $categories = ['cost', 'quality', 'delivery', 'communication', 'innovation', 'support', 'esg', 'anti_corruption'];
            $badNotesAllowed = 3;
            $badNotesCount = 0;
            foreach ($categories as $category) {
                if (empty($note = $this->infos['class_' . $category])) {
                    $statuses = ['Monitored'];
                    break;
                }
                // ==  because two columns are varchar
                if ($note == 1) {
                    $statuses = ['Locked', 'Suppressed'];
                    break;
                }

                if ($note !== "N/A" && ((int)$note) < $this->infos['exper_' . $category]) {
                    if (in_array($category, ['quality', 'anti_corruption'], true)) {
                        $statuses = ['Restricted'];
                        break;
                    }
                    $badNotesCount++;
                } elseif ($note === "N/A" && $category === 'esg') {
                    --$badNotesAllowed;
                }

            }
            if ([] === $statuses) {
                $statuses = ($badNotesCount >= $badNotesAllowed) ? ['Restricted'] : ['Monitored', 'Unrestricted'];
            }
        }
        return array_filter(self::getPurchaseStatus(), static function ($purchaseStatus) use ($statuses) {
            return in_array($purchaseStatus['rank'], $statuses, true);
        });
    }

    /**
     * @return array
     */
    public static function getPurchaseStatus()
    {
        $query = <<<SQL
		SELECT id, rank, description
		FROM supp_pur_status
SQL;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getESGAntiCorruptionStatusList(): array
    {
        return [
            'To Be Done' => 'To Be Done',
            'On-Going' => 'On-Going',
            'Done' => 'Done',
            'N/A' => 'N/A',
        ];
    }

    /**
     * @param int $exper
     * @param int $rank
     * @param int $user_id
     * @return string
     */
    public function updateInformation($exper, $rank, $esg_status, $screening_status, $lastScreeningDate, $user_id)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');

        $query = <<<SQL
		UPDATE supp_classification SET 
		last_eval=NOW(), 
		last_eval_user=$user_id, 
		supp_pur_status_id=$rank, 
		supp_exper_class_id=$exper, 
		esg_status='$esg_status',
		screening_status='$screening_status',
		last_screening_date='$lastScreeningDate'
		WHERE id = '{$this->id}'
SQL;
        if (!empty($this->infos['supp_pur_status_id']) && $rank > $this->infos['supp_pur_status_id']) {
            $erp = $this->getErp();
            $suno = $this->getSuno();
            $to = [];
            $cc = [];
            $slaveErp = [];
            $e = $this->getSlave();
            if (!is_string($e)) {
                foreach ($e as $slave) {
                    $slaveErp[] = $slave['erp'];
                }
            }
            $vendors = <<<SQL
            SELECT DISTINCT T1.ERP FROM vendors_suno AS T1 WHERE T1.t_suno LIKE '{$suno}'
SQL;
            $vendorerp = tldUtils::getSqlToAssocArray($vendors);
            foreach ( $vendorerp as $v) {
                $grp = new tldGroup('role_MLM', $v['ERP']);
                $to[] = $grp->getEmailList();
                $grp = new tldGroup('role_QAM', $v['ERP']);
                $to[] = $grp->getEmailList();
                $grp = new tldGroup('role_COO', $v['ERP']);
                $to[] = $grp->getEmailList();
            }

            $grp = new tldGroup('role_CEO', $erp);
            $to[] = $grp->getEmailList();
            $grp = new tldGroup('role_GCOO', $erp);
            $to[] = $grp->getEmailList();
            $gcoo = new tldGroup('role_GCOO', 900);
            $to[] = $gcoo->getEmailList();
            $gcmo = new tldGroup('role_CMO', 900);
            $to[] = $gcmo->getEmailList();
            $gcpo = new tldGroup('role_CPO', 900);
            $to[] = $gcpo->getEmailList();

            foreach ($slaveErp as $ccErp) {
                $grp = new tldGroup('role_MLM', $ccErp);
                $cc[] = $grp->getEmailList();
                $grp = new tldGroup('role_QAM', $ccErp);
                $cc[] = $grp->getEmailList();
                $grp = new tldGroup('role_COO', $ccErp);
                $cc[] = $grp->getEmailList();
                $grp = new tldGroup('role_CEO', $ccErp);
                $cc[] = $grp->getEmailList();
                $grp = new tldGroup('role_GCOO', $ccErp);
                $cc[] = $grp->getEmailList();
            }
            $to = array_unique(array_merge(...$to));
            $cc = array_unique(array_merge(...$cc));
            $name = self::getCompanyList($erp, $suno);
            $actualRankDescription = '';
            $toRankDescription = '';
            foreach (self::getPurchaseStatus() as $status) {
                if ($status['id'] == $rank) {
                    $toRankDescription = $status['rank'];
                }
                if ($status['id'] == $this->infos['supp_pur_status_id']) {
                    $actualRankDescription = $status['rank'];
                }
            }
            $user = new tldUser($user_id);
            $subject = 'Supplier "' . $name[0]['t_nama'] . '(#' . $suno . ')" Classification has been decreased.';
            $body = 'Supplier "' . $name[0]['t_nama'] . ' (#' . $suno . ')" Classification has been changed from "' .
                $actualRankDescription . '" to "' . $toRankDescription . '" by "' . $user->getEmail() . '".' .
            "\n\n" . '<a href="https://www.tld-gse.com/en/private/manufacturing/pur/dev.php?m[0]=vendors&m[1]=classification&erp='. $erp .
                '&suno=' . $this->getSuno() . '">Go to classification page</a>';

            tldUtils::emailAttachment($to, 'noreply@tld-gse.com', $subject, nl2br($body), null, $cc);
        }

        return tldUtils::sqlQuery($query);
    }

    /**
     * @return array
     */
    public function getQualificationFiles($ids = [])
    {
        $ids = implode(',', $ids ?: [$this->id]);

        $query = <<<SQL
SELECT
  mod_files.id,
  mod_files.parent_id,
  mod_files.module,
  mod_files.description,
  mod_files.fid,
  mod_files.level,
  mod_files.expiration_date,
  (CASE WHEN (mod_files.level>100 and mod_files.level<1000) THEN 'YES'
      ELSE 'NO'
  END) as hidden,
    (CASE WHEN (mod_files.level>1000) THEN 'NO'
      ELSE 'YES'
  END) as archive,
  (CASE WHEN (mod_files.level=1 or mod_files.level=0 or mod_files.level=101 or mod_files.level=1101) THEN 'Qualification'
	WHEN (mod_files.level=2 or mod_files.level=102 or mod_files.level=1002 or mod_files.level=1102) THEN 'Contracts'
	WHEN (mod_files.level=3 or mod_files.level=103 or mod_files.level=1003 or mod_files.level=1103) THEN 'Prices'
	WHEN (mod_files.level=4 or mod_files.level=104 or mod_files.level=1004 or mod_files.level=1104) THEN 'Minutes of meeting'
	WHEN (mod_files.level=5 or mod_files.level=105 or mod_files.level=1005 or mod_files.level=1105) THEN 'Code Ethic'
	WHEN (mod_files.level=6 or mod_files.level=106 or mod_files.level=1006 or mod_files.level=1106) THEN 'Others'
    WHEN (mod_files.level=7 or mod_files.level=107 or mod_files.level=1007 or mod_files.level=1107) THEN 'ISO9001'
    WHEN (mod_files.level=8 or mod_files.level=108 or mod_files.level=1008 or mod_files.level=1108) THEN 'ISO14001'
    WHEN (mod_files.level=9 or mod_files.level=109 or mod_files.level=1009 or mod_files.level=1109) THEN 'ESG'
  END) AS category,
  file.dt,
  DATE(file.dt) AS date,
  (mod_files.level=9 or mod_files.level=109 or mod_files.level=1009 or mod_files.level=1109) AND (mod_files.expiration_date > NOW() OR (mod_files.expiration_date IS NULL AND TIMESTAMPDIFF(YEAR,file.dt,NOW())<3)) AS isEsgOk,
  file.poster AS poster_fullname,
  file.filepath,
  file.size,
  file.filename,
  file.extension,
  concat(firstname,' ',lastname) as poster
FROM mod_files
LEFT JOIN file ON file.id=mod_files.fid
LEFT JOIN people on people.id=mod_files.poster
WHERE mod_files.parent_id IN ($ids) AND module = 'eVendor-Q'
ORDER BY mod_files.parent_id, date;
SQL;

        $rows = tldUtils::getSqlToAssocArray($query);
        foreach($rows as &$item) {
            $item['level'] = ($item['level']) ? 'Yes' : 'No';
            $item['erp'] = $this->erp;
            $item['suno'] = $this->suno;
        }

        return $rows;
    }

    public function countQualificationfiles(){
        $query =<<<EOF
SELECT
    (CASE WHEN (mod_files.level=1 or mod_files.level=0 or mod_files.level=101 or mod_files.level=1101) THEN 'Qualification'
          WHEN (mod_files.level=2 or mod_files.level=102 or mod_files.level=1002 or mod_files.level=1102) THEN 'Contracts'
          WHEN (mod_files.level=3 or mod_files.level=103 or mod_files.level=1003 or mod_files.level=1103) THEN 'Prices'
          WHEN (mod_files.level=4 or mod_files.level=104 or mod_files.level=1004 or mod_files.level=1104) THEN 'Minutes of meeting'
          WHEN (mod_files.level=5 or mod_files.level=105 or mod_files.level=1005 or mod_files.level=1105) THEN 'Code Ethic'
          WHEN (mod_files.level=6 or mod_files.level=106 or mod_files.level=1006 or mod_files.level=1106) THEN 'Others'
          WHEN (mod_files.level=7 or mod_files.level=107 or mod_files.level=1007 or mod_files.level=1107) THEN 'ISO9001'
          WHEN (mod_files.level=8 or mod_files.level=108 or mod_files.level=1008 or mod_files.level=1108) THEN 'ISO14001'
          WHEN (mod_files.level=9 or mod_files.level=109 or mod_files.level=1009 or mod_files.level=1109) THEN 'ESG'
        END) AS category,
    mod_files.module,
    COUNT(*) as num
FROM mod_files
         LEFT JOIN file ON file.id=mod_files.fid
         LEFT JOIN people on people.id=mod_files.poster
WHERE mod_files.parent_id = '{$this->id}' AND module = 'eVendor-Q'
GROUP BY category,module

EOF;

        return tldUtils::getSqlToAssocArray($query);
    }
    /**
     * @param int $user_id
     * @param $file
     * @param bool $hidden
     * @param $expirationDate
     * @return string
     */
    public function addFile($user_id, $file, $hidden, $expirationDate = null)
    {
        $modfile = [];
        $modfile['parent_id'] = $this->id;
        $modfile['description'] = "$this->suno - $this->erp";
        $modfile['module'] = 'eVendor-Q';
        $modfile['poster'] = $user_id;
        $modfile['level'] = $hidden;
        $modfile['expiration_date'] = $expirationDate;
        return tldModFile::insert($modfile, $file);
    }

    /**
     * @param int $user_id
     * @param string $comment
     * @param int $type
     * @return bool
     */
    public function addLog($user_id, $comment, $type)
    {
        $a = [];
        $a['parent_id'] = $this->id;
        $a['module'] = 'eVendor-C';
        $a['poster'] = $user_id;
        $a['comment'] = $comment;
        $a = tldUtils::cleanupFormInput($a);
        $a['log_num'] = $type;

        return tldModLog::insert($a);
    }

    /**
     * @param int $type
     * @return array
     */
    public function getLog($type)
    {
        $query = <<<SQL
        SELECT
            mod_logs.*, concat(firstname,' ',lastname) as poster_fullname
        FROM mod_logs
            LEFT JOIN people ON mod_logs.poster=people.id
        WHERE
            (mod_logs.parent_id='{$this->id}'
            AND mod_logs.module LIKE 'eVendor-C' AND log_num='$type')
SQL;

        $query .= ' ORDER BY id DESC';

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * @return array
     */
    public function getSlave()
    {
        $query = <<<SQL
SELECT DISTINCT class.t_suno, class.erp, t_nama
FROM supp_classification as class
  LEFT JOIN vendors_suno on vendors_suno.erp=class.erp and vendors_suno.t_suno=class.t_suno
WHERE master_suno = '{$this->suno}' AND master_erp = '{$this->erp}'
ORDER BY 't_suno'
SQL;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getCompanyListWithoutMaster()
    {
        $query = <<<SQL
		SELECT DISTINCT t_suno, erp, CONCAT(people.firstname, ' ' ,people.lastname) AS rep_name, tld_rep_id, t_nama
FROM vendors_suno
    LEFT JOIN people on tld_rep_id=people.id
    LEFT JOIN vendors on vendors_suno.parent_id=vendors.id
WHERE t_suno != '' AND enable = 'Y'
AND (t_suno, erp) NOT IN (select distinct supp.t_suno, supp.erp
                          from supp_classification as supp
                          where supp.master_erp IS NOT NULL AND supp.master_suno IS NOT NULL)
ORDER BY t_suno;
SQL;
        return tldUtils::getSqlToAssocArray($query);
    }

}
