<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Class for communicating with Fedex/Aeroxchange ERP system
 *
 * @package ERP
 */
class erpAero implements basicERP
{
    /*
     *From: 	Darshan Arkalgud <Darshan.Arkalgud@aeroxchange.com>
	 *To: 	Renaud Le Pape <renaud.lepape@tld-america.com>
	 *Cc: 	Graham FONG <graham.fong@tld-group.com>, OnCallMSG <OnCallMSG@aeroxchange.com>, devteam <devteam@tld-america.com>
	 *Subject: 	FW: FW: Connectivity Test
	 *Date: 	Wed, 30 Sep 2009 16:57:22 -0500 (17:57 EDT)
	 *
	 *Renaud,
	 *
	 *Below are the URL’s we have
	 *
     *For Test
     *URL : https://aeroxchangeb2bserver.com:9003/invoke/wm.tn/receive
     *Username : 29116
     *Password : welcome1
     *
     *For Prod
     *URL : https://205.141.200.168:9003/invoke/wm.tn/receive
     *Username : 38406
     *Password : welcome9
     *
     *If you have 2 different systems for test and prod you can configure accordingly.
     *Otherwise you will have to use prod URL.
     *
     *Thanks and Regards,
     *Darshan A N
     *9725568505
     *
     */


    public $theURL = 'https://205.141.200.168:9003/invoke/wm.tn/receive';
    public $theUSERID = '38406';
    public $thePASSWD = 'welcome9';
    public $itsERP; //erp company number

    function __construct($erp)
    {
        $this->itsERP = $erp;
    }

    /**
     * Factory for creating aero objects
     *
     * @param string $type
     * @param array $p
     */
    function factory($type, $p)
    {
        return null;
//        switch ($type) {
//            case 'PURCHASE_ORDER':
//
//                break;
//            case 'SALES_ORDER':
//
//                break;
//            case 'SALES_ORDER_ACK':
//
//                break;
//            case 'INVOICE':
//
//                break;
//        }
//        return $result;
    }

    /**
     * Post a document to the erp system
     *
     * @param mixed $xml
     * @return simpleXMLElement
     */
    private function post($xml)
    {
        $ch = curl_init($this->theURL);
        if (!$ch) {
            return 'ERROR: cannot open ' . $this->theURL;
        }
        curl_setopt_array(
            $ch,
            [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERPWD => $this->theUSERID . ':' . $this->thePASSWD,
                //	CURLOPT_HEADER => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: text/xml',
                ],
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $xml,
            ]
        );
        $code = curl_exec($ch);
        if (curl_errno($ch)) {
            error_log(
                'erp.inc.php, erpAero->post, ERROR: ' .
                curl_error($ch) . '-' . curl_error($ch)
            );
        }
        error_log($code);
        curl_close($ch);
        $x = simplexml_load_string($code);
        return $x;
    }

    /**
     * receive a DELIVERY NOTE in native erp system xml
     *
     *<code>
     *
     * // Aeroxchange PO
     *
     * <?xml version = '1.0' standalone = 'no'?>
     *<!DOCTYPE PROCESS_PO_003 SYSTEM "003_process_po_003.dtd">
     *<PROCESS_PO_003>
     *  <CNTROLAREA>
     *   <BSR>
     *    <VERB value="PROCESS">PROCESS</VERB>
     *    <NOUN value="PO">PO</NOUN>
     *    <REVISION value="003">003</REVISION>
     *  </BSR>
     *  <SENDER>
     *    <LOGICALID>9345232</LOGICALID>
     *    <COMPONENT>PURCHASING</COMPONENT>
     *    <TASK>POISSUE</TASK>
     *    <REFERENCEID>22</REFERENCEID>
     *    <CONFIRMATION>0</CONFIRMATION>
     *    <LANGUAGE>US</LANGUAGE>
     *    <CODEPAGE>UTF8</CODEPAGE>
     *    <AUTHID>AEX</AUTHID>
     *  </SENDER>
     *  <DATETIME qualifier="CREATION">
     *    <YEAR>2005</YEAR>
     *    <MONTH>12</MONTH>
     *    <DAY>30</DAY>
     *    <HOUR>07</HOUR>
     *    <MINUTE>06</MINUTE>
     *    <SECOND>02</SECOND>
     *    <SUBSECOND>0000</SUBSECOND>
     *    <TIMEZONE>-0800</TIMEZONE>
     *  </DATETIME>
     *</CNTROLAREA>
     *<DATAAREA>
     *  <PROCESS_PO>
     *    <POORDERHDR>
     *     <DATETIME qualifier="DOCUMENT">
     *       <YEAR>2005</YEAR>
     *       <MONTH>12</MONTH>
     *       <DAY>30</DAY>
     *       <HOUR>07</HOUR>
     *       <MINUTE>08</MINUTE>
     *       <SECOND>33</SECOND>
     *       <SUBSECOND>0000</SUBSECOND>
     *       <TIMEZONE>-0800</TIMEZONE>
     *     </DATETIME>
     *     <OPERAMT qualifier="EXTENDED" type="T">
     *       <VALUE>4556</VALUE>
     *   <NUMOFDEC>2</NUMOFDEC>
     *   <SIGN>+</SIGN>
     *   <CURRENCY>USD</CURRENCY>
     *   <UOMVALUE>1</UOMVALUE>
     *   <UOMNUMDEC>0</UOMNUMDEC>
     *   <UOM/>
     * </OPERAMT>
     * <POID>1573</POID>
     * <POTYPE>AutoOrder</POTYPE>
     * <ACKREQUEST>1</ACKREQUEST>
     * <DESCRIPTN>Test Order</DESCRIPTN>
     * <NOTES/>
     * <USERAREA>
     *<EXT_COST_CENTER>144048</EXT_COST_CENTER>
     *<EXT_ACCOUNT_CODE>657600</EXT_ACCOUNT_CODE>
     *<EXT_EMPLOYEE_NUM>404056</EXT_EMPLOYEE_NUM>
     *<EXT_PROJECT_NUM>256466</EXT_PROJECT_NUM>
     *<EXT_LOCATION>466</EXT_LOCATION>
     *<EXT_NON_REV_NUM>1749-8508-1</EXT_NON_REV_NUM>
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
     *  <NAME index="1">GSE Test Supplier for FedEx</NAME>
     *  <ONETIME>0</ONETIME>
     *  <PARTNRID>29076</PARTNRID>
     *  <PARTNRTYPE>SUPPLIER</PARTNRTYPE>
     *  <CURRENCY>USD</CURRENCY>
     *  <PARTNRIDX>29076</PARTNRIDX>
     *  <USERAREA><SITEID>0</SITEID></USERAREA>
     *</PARTNER>
     *<PARTNER>
     *  <NAME index="1">Aeroxchange Ltd.</NAME>
     *  <ONETIME>0</ONETIME>
     *  <PARTNRID>15561</PARTNRID>
     *  <PARTNRTYPE>SOLDTO</PARTNRTYPE>
     *  <CURRENCY>USD</CURRENCY>
     *  <PARTNRIDX>15561</PARTNRIDX>
     *  <CONTACT>
     *    <NAME index="1">Nikhil Chandurkar</NAME>
     *    <EMAIL>cnikhil@yahoo.com</EMAIL>
     *    <FAX index="1"/>
     *    <TELEPHONE index="1">9725568531 </TELEPHONE>
     *  </CONTACT>
     *</PARTNER>
     *<PARTNER>
     *  <NAME index="1">FedEx 2 Day</NAME>
     *  <ONETIME>0</ONETIME>
     *  <PARTNRID>FE03</PARTNRID>
     *  <PARTNRTYPE>CARRIER</PARTNRTYPE>
     *</PARTNER>
     *<PARTNER>
     *<NAME index="1">My Office</NAME>
     *<ONETIME>0</ONETIME>
     *<PARTNRID>34487</PARTNRID>
     *<PARTNRTYPE>BILLTO</PARTNRTYPE>
     *<PARTNRIDX>15561</PARTNRIDX>
     *<ADDRESS>
     *  <ADDRLINE index="1">5221 O'Connor</ADDRLINE>
     *  <ADDRLINE index="2">Suite 800 E</ADDRLINE>
     *    <ADDRLINE index="3"/>
     *    <ADDRLINE index="4"/>
     *    <CITY>Irving</CITY>
     *    <COUNTRY>US</COUNTRY>
     *    <POSTALCODE>75039</POSTALCODE>
     *    <STATEPROVN>TX</STATEPROVN>
     *  </ADDRESS>
     *</PARTNER>
     *</POORDERHDR>
     *<POORDERLIN>
     *<QUANTITY qualifier="ORDERED">
     *  <VALUE>1</VALUE>
     *  <NUMOFDEC/>
     *  <SIGN>+</SIGN>
     *  <UOM>EA</UOM>
     *</QUANTITY>
     *<OPERAMT qualifier="UNIT" type="T">
     *  <VALUE>4556</VALUE>
     *  <NUMOFDEC>2</NUMOFDEC>
     *  <SIGN>+</SIGN>
     *  <CURRENCY>USD</CURRENCY>
     *  <UOMVALUE>1</UOMVALUE>
     *  <UOMNUMDEC>0</UOMNUMDEC>
     *  <UOM/>
     *</OPERAMT>
     *<POLINENUM>1</POLINENUM>
     *<HAZRDMATL/>
     *<NOTES/>
     *<DESCRIPTN>GSE Test Part</DESCRIPTN>
     *<ITEM/>
     *<ITEMX>GSETESTPART</ITEMX>
     *<USERAREA>
     * <MFRNAME>GSETESTPART</MFRNAME>
     * <MFRNUM>GSETESTPART</MFRNUM>
     * <DATETIME qualifier="NEEDDELV">
     * <YEAR>2006</YEAR>
     * <MONTH>02</MONTH>
     *       <DAY>23</DAY>
     *       <HOUR>21</HOUR>
     *       <MINUTE>59</MINUTE>
     *       <SECOND>00</SECOND>
     *       <SUBSECOND>0000</SUBSECOND>
     *       <TIMEZONE>-0800</TIMEZONE>
     *       </DATETIME>
     *       </USERAREA>
     *      <PARTNER>
     *        <NAME index="1">Global</NAME>
     *        <ONETIME>0</ONETIME>
     *        <PARTNRID>31074</PARTNRID>
     *        <PARTNRTYPE>SHIPTO</PARTNRTYPE>
     *        <PARTNRIDX>15561</PARTNRIDX>
     *        <ADDRESS>
     *          <ADDRLINE index="1">5221 N. O'Connor Blvd.</ADDRLINE>
     *          <ADDRLINE index="2">Suite 800 E</ADDRLINE>
     *          <ADDRLINE index="3"/>
     *          <ADDRLINE index="4"/>
     *          <CITY>Irving</CITY>
     *          <COUNTRY>US</COUNTRY>
     *          <POSTALCODE>75039</POSTALCODE>
     *          <STATEPROVN>TX</STATEPROVN>
     *        </ADDRESS>
     *      </PARTNER>
     *    </POORDERLIN>
     *<POORDERLIN>
     *<QUANTITY qualifier="ORDERED">
     *  <VALUE>1</VALUE>
     *  <NUMOFDEC/>
     *  <SIGN>+</SIGN>
     *  <UOM>EA</UOM>
     *</QUANTITY>
     *<OPERAMT qualifier="UNIT" type="T">
     *  <VALUE>4556</VALUE>
     *  <NUMOFDEC>2</NUMOFDEC>
     *  <SIGN>+</SIGN>
     *  <CURRENCY>USD</CURRENCY>
     *  <UOMVALUE>1</UOMVALUE>
     *  <UOMNUMDEC>0</UOMNUMDEC>
     *  <UOM/>
     *</OPERAMT>
     *<POLINENUM>2</POLINENUM>
     *<HAZRDMATL/>
     *<NOTES/>
     *<DESCRIPTN>GSE Test Part2</DESCRIPTN>
     *<ITEM/>
     *<ITEMX>GSETESTPART2</ITEMX>
     *<USERAREA>
     * <MFRNAME>GSETESTPART</MFRNAME>
     * <MFRNUM>GSETESTPART</MFRNUM>
     * <DATETIME qualifier="NEEDDELV">
     * <YEAR>2006</YEAR>
     * <MONTH>02</MONTH>
     *       <DAY>23</DAY>
     *       <HOUR>21</HOUR>
     *       <MINUTE>59</MINUTE>
     *       <SECOND>00</SECOND>
     *       <SUBSECOND>0000</SUBSECOND>
     *       <TIMEZONE>-0800</TIMEZONE>
     *       </DATETIME>
     *       </USERAREA>
     *      <PARTNER>
     *        <NAME index="1">Global</NAME>
     *        <ONETIME>0</ONETIME>
     *        <PARTNRID>31074</PARTNRID>
     *        <PARTNRTYPE>SHIPTO</PARTNRTYPE>
     *        <PARTNRIDX>15561</PARTNRIDX>
     *        <ADDRESS>
     *          <ADDRLINE index="1">5221 N. O'Connor Blvd.</ADDRLINE>
     *          <ADDRLINE index="2">Suite 800 E</ADDRLINE>
     *          <ADDRLINE index="3"/>
     *          <ADDRLINE index="4"/>
     *          <CITY>Irving</CITY>
     *          <COUNTRY>US</COUNTRY>
     *          <POSTALCODE>75039</POSTALCODE>
     *          <STATEPROVN>TX</STATEPROVN>
     *        </ADDRESS>
     *      </PARTNER>
     *    </POORDERLIN>
     *  </PROCESS_PO>
     *</DATAAREA>
     *</PROCESS_PO_003>
     *</code>
     *
     * @param array $a delivery note in TLD Standard array format
     * @param string $poxml original po xml
     * @return array
     */
    function inDeliveryNote($a, $poxml)
    {
        // create $b a simple xml element from aeroxchange $poxml PO xml
        $po = simplexml_load_string($poxml);
        $partners = tldERP::parsePartnersSection($po->DATAAREA->PROCESS_PO->POORDERHDR->PARTNER);

        $ship = new SimpleXMLElement('<AEX_GSEShipNotification xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="AEX_GSEShipNotification.xsd"></AEX_GSEShipNotification>');

        $communicationArea = $ship->addChild('CommunicationArea');
        $communicationArea->addChild('Sender', $partners['SUPPLIER']['PARTNRID']);
        $communicationArea->addChild('Receiver', $partners['SOLDTO']['PARTNRID']);
        $communicationArea->addChild('MessageSeqID', time());
        $communicationArea->addChild('CreationDateTime', date('Y-m-d H:i:s'));

        $ShipNotificationHeader = $ship->addChild('ShipNotificationHeader');
        $ShipNotificationHeader->addChild('RequisitionNumber', $po->CNTROLAREA->SENDER->LOGICALID);
        // LOGICALID from aeroxchange PO
        $ShipNotificationHeader->addChild('PONumber', $po->DATAAREA->PROCESS_PO->POORDERHDR->POID);
        $ShipNotificationHeader->addChild('ShipDate',
            str_replace('-', '/', $a['PSORDERHDR']['DATETIME'][0]));
        // DATETIME[0] from Baan Array (DATETIME[1] is last printed date)
//error_log(print_r($a, true).$debug, 1, "graham.fong@tld-america.com");

        if (count($a['DETAIL']['LINE'])) {
            foreach ($a['DETAIL']['LINE'] as $line) {
                if ($line['ORDQTY'] > 0) {
                    $ShipNotificationDetails = $ship->addChild('ShipNotificationDetails');
                    $ShipNotificationDetails->addChild('SupplierPartNumber', $line['ITEM']);
                    $ShipNotificationDetails->addChild('TrackingNumber', 'NA');
                    $ShipNotificationDetails->addChild('Quantity', number_format($line['ORDQTY']));
                    //need to use number format to get rid of the baan trailing zeros!!
                }
            }
        }

        $r = $this->post($ship->asXML());
        if ($r->ResponseMsg->returnCode == 200 || $r->ResponseMsg->returnCode == 1
            || $r->ResponseMsg->returnCode == 'Successful') {
            $res = true;
        } elseif (count($r)) {
            foreach ($r as $ResponseMsg) {
                $error .= $ResponseMsg->errorMsg . "\n";
            }
        } else {
            $error .= $r->errorMsg;
        }
        tldArchive::insert($a['TLDHEADER']['COMP'], 'DeliveryNote', $a['TLDHEADER']['PONUM'],
            date('Y-m-d'), '', $this->itsERP, $ship->asXML());
        return $res ? true : "ERROR: erpAero, DeliveryNote, error\n $error\n ";
    }

    /**
     * Post a invoice to this erp system
     *
     * @param array $a
     * @param string $poxml original po xml
     */
    function inInvoice($a, $poxml)
    {
        ;
    }

    /**
     * Post a sales order to this erp system
     *
     * @param array $a
     */
    function inPurchaseOrder($a)
    {

        $smarty = tldUtils::getSmarty('common');
        $smarty->assign('a', $a);
        $tldxml = $smarty->fetch('erp/wise.inPO.tpl');

        return $tldxml;
    }

    /**
     * Post a sales order to this erp system
     *
     *
     * @param array $a
     * @param string $poxml original po xml
     */
    function inSalesOrder($a, $poxml)
    {
    }

    /**
     * Post a sales order ack to this erp system
     *
     * <code>
     * //XML returned by function:
     *
     *<AEX_GSEPOAcknowledgment xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
     *xsi:noNamespaceSchemaLocation="C:\repository\wmtn\records\commercial\GSE\AEX_GSEPOAcknowledgment.xsd">
     *    <CommunicationArea>
     *        <Sender>38523</Sender> //from poxml
     *        <Receiver>39721</Receiver> //from poxml
     *        <MessageSeqID>123456</MessageSeqID> //from poxml
     *        <CreationDateTime>2005-12-01 14:41:00</CreationDateTime>
     *    </CommunicationArea>
     *
     *    <POAcknowledgmentHeader>
     *        <PONumber>Cust PO No XYZ-123</PONumber> //from poxml
     *        <MessageTypeIndicator>A</MessageTypeIndicator>
     *    </POAcknowledgmentHeader>
     *
     *    <POAcknowledgementDetails>
     *        <SupplierPartNumber>1000006</SupplierPartNumber> //from soack
     *        <Quantity>1.0000</Quantity> //from soack
     *    </POAcknowledgementDetails>
     *    <POAcknowledgementDetails>
     *        <SupplierPartNumber>1000006#2</SupplierPartNumber> //from soack
     *        <Quantity>3.0000</Quantity> //from soack
     *    </POAcknowledgementDetails>
     *
     *</AEX_GSEPOAcknowledgment>
     * </code>
     *
     *
     * <code>
     * // $a array received from Baan (converted from the Baan XML SOAcknowledgment)
     *
     * array(2) {
     *  ["TLDHEADER"]=>
     *  array(3) {
     *    ["COMP"]=>
     *    string(3) "403"
     *    ["TYPE"]=>
     *    string(1) "A"
     *    ["LANG"]=>
     *    string(2) "EN"
     *  }
     *  ["PROCESS_SO_ACK"]=>
     *  array(1) {
     *    ["DATAAREA"]=>
     *    array(1) {
     *      ["PROCESS_SO"]=>
     *      array(3) {
     *        ["SOORDERHDR"]=>
     *        array(9) {
     *          ["t_header.text"]=>
     *          string(0) ""
     *          ["PARTNER"]=>
     *          array(2) {
     *            [0]=>
     *            array(5) {
     *              ["PARTNRTYPE"]=>
     *              string(8) "CUSTOMER"
     *              ["ONETIME"]=>
     *              string(1) "0"
     *              ["t_tcmcs019.dsca"]=>
     *              string(0) ""
     *              ["NAME"]=>
     *              string(20) "United Parcel Serive"
     *              ["ADDRESS"]=>
     *              array(1) {
     *                ["ADDRLINE"]=>
     *                array(8) {
     *                  [0]=>
     *                  string(17) "2455 Michigan Ave"
     *                  [1]=>
     *                  string(16) "Mobile, AL 36615"
     *                  [2]=>
     *                  string(0) ""
     *                  [3]=>
     *                  string(0) ""
     *                  [4]=>
     *                  string(0) ""
     *                  [5]=>
     *                  string(0) ""
     *                  [6]=>
     *                  string(0) ""
     *                  [7]=>
     *                  string(0) ""
     *                }
     *              }
     *            }
     *            [1]=>
     *            array(5) {
     *              ["PARTNERTYPE"]=>
     *              string(6) "SOLDTO"
     *              ["ONETIME"]=>
     *              string(1) "0"
     *              ["t_form.text"]=>
     *              string(16) "Delivery Address"
     *              ["NAME"]=>
     *              string(21) "United Parcel Service"
     *              ["ADDRESS"]=>
     *              array(1) {
     *                ["ADDRLINE"]=>
     *                array(6) {
     *                  [0]=>
     *                  string(14) "GSE Automotive"
     *                  [1]=>
     *                  string(13) "6200 Lockheed"
     *                  [2]=>
     *                  string(21) "Anchorage,  AK  99502"
     *                  [3]=>
     *                  string(0) ""
     *                  [4]=>
     *                  string(0) ""
     *                  [5]=>
     *                  string(0) ""
     *                }
     *              }
     *            }
     *          }
     *          ["DATETIME"]=>
     *          array(2) {
     *            [0]=>
     *            string(28) "Windsor, CT  06095, 11-13-07"
     *            [1]=>
     *            string(10) "11-13-2007"
     *          }
     *          ["PARTNERID"]=>
     *          string(3) "629"
     *          ["SOID"]=>
     *          string(6) "520123"
     *          ["CUSTPO"]=>
     *          string(18) "Cust PO No XYZ-123"
     *          ["REFA"]=>
     *          string(15) "reference AAAAA"
     *          ["REFB"]=>
     *          string(15) "reference BBBBB"
     *          ["HEADERTEXT"]=>
     *          string(63) "this is header text
     *            and this is header text line 2."
     *        }
     *        ["DETAIL"]=>
     *        array(1) {
     *          ["LINE"]=>
     *          array(2) {
     *            [0]=>
     *            array(17) {
     *              ["SOLINENUM"]=>
     *              string(1) "1"
     *              ["QUANTITY"]=>
     *              string(6) "1.0000"
     *              ["UOM"]=>
     *              string(2) "EA"
     *              ["ITEM"]=>
     *              string(7) "1000006"
     *              ["CNTR"]=>
     *              string(0) ""
     *              ["PRIC"]=>
     *              string(9) "1139.9400"
     *              ["UOMP"]=>
     *              string(2) "EA"
     *              ["CVAT"]=>
     *              string(4) "Out/"
     *              ["DISC"]=>
     *              string(0) ""
     *              ["PERC"]=>
     *              string(0) ""
     *              ["DMTH"]=>
     *              string(0) ""
     *              ["ASTRX"]=>
     *              string(0) ""
     *              ["DDTB"]=>
     *              string(10) "11-13-2007"
     *              ["EXTPRICE"]=>
     *              string(7) "1139.94"
     *              ["ITEMX"]=>
     *              string(0) ""
     *              ["DESCRIPTN"]=>
     *              string(17) "COUPLING ASSEMBLY"
     *              ["LINETEXT"]=>
     *              string(137) "this is sales order line text
     *          this goes just after the line
     *          it is associated with
     *          and before the next line."
     *            }
     *            [1]=>
     *            array(17) {
     *              ["SOLINENUM"]=>
     *              string(1) "2"
     *              ["QUANTITY"]=>
     *              string(6) "3.0000"
     *              ["UOM"]=>
     *              string(2) "EA"
     *              ["ITEM"]=>
     *              string(9) "1000006#2"
     *              ["CNTR"]=>
     *              string(0) ""
     *              ["PRIC"]=>
     *              string(9) "1139.9400"
     *              ["UOMP"]=>
     *              string(2) "EA"
     *              ["CVAT"]=>
     *              string(4) "Out/"
     *              ["DISC"]=>
     *              string(0) ""
     *              ["PERC"]=>
     *              string(0) ""
     *              ["DMTH"]=>
     *              string(0) ""
     *              ["ASTRX"]=>
     *              string(0) ""
     *              ["DDTB"]=>
     *              string(12) "11-13-2007#2"
     *              ["EXTPRICE"]=>
     *              string(7) "1139.94"
     *              ["ITEMX"]=>
     *              string(0) ""
     *              ["DESCRIPTN"]=>
     *              string(17) "COUPLING ASSEMBLY"
     *              ["LINETEXT"]=>
     *              string(140) "this is sales order line text
     *          this goes just after the line
     *          it is associated with
     *          and before the next line. #2"
     *            }
     *          }
     *        }
     *        ["SOORDERFTR"]=>
     *        array(14) {
     *          ["FOOTERTEXT"]=>
     *          string(71) "this is SO footer text
     *            that goes at the bottom of the order"
     *          ["CURRENCY"]=>
     *          string(3) "USD"
     *          ["GOODS"]=>
     *          string(7) "1139.94"
     *          ["DISC"]=>
     *          string(0) ""
     *          ["COST"]=>
     *          string(0) ""
     *          ["LATE"]=>
     *          string(0) ""
     *          ["CVAT"]=>
     *          string(0) ""
     *          ["CVA2"]=>
     *          string(0) ""
     *          ["CVA3"]=>
     *          string(0) ""
     *          ["INVO"]=>
     *          string(7) "1139.94"
     *          ["CVATPERC2"]=>
     *          string(0) ""
     *          ["CVATPERC3"]=>
     *          string(0) ""
     *          ["DELTERMS"]=>
     *          string(10) "UPS Ground"
     *          ["PAYTERMS"]=>
     *          string(11) "Net 30 Days"
     *        }
     *      }
     *    }
     *  }
     *}
     * </code>
     *
     * <code>
     *
     * // Aeroxchange PO
     *
     * <?xml version = '1.0' standalone = 'no'?>
     *<!DOCTYPE PROCESS_PO_003 SYSTEM "003_process_po_003.dtd">
     *<PROCESS_PO_003>
     *  <CNTROLAREA>
     *   <BSR>
     *    <VERB value="PROCESS">PROCESS</VERB>
     *    <NOUN value="PO">PO</NOUN>
     *    <REVISION value="003">003</REVISION>
     *  </BSR>
     *  <SENDER>
     *    <LOGICALID>9345232</LOGICALID>
     *    <COMPONENT>PURCHASING</COMPONENT>
     *    <TASK>POISSUE</TASK>
     *    <REFERENCEID>22</REFERENCEID>
     *    <CONFIRMATION>0</CONFIRMATION>
     *    <LANGUAGE>US</LANGUAGE>
     *    <CODEPAGE>UTF8</CODEPAGE>
     *    <AUTHID>AEX</AUTHID>
     *  </SENDER>
     *  <DATETIME qualifier="CREATION">
     *    <YEAR>2005</YEAR>
     *    <MONTH>12</MONTH>
     *    <DAY>30</DAY>
     *    <HOUR>07</HOUR>
     *    <MINUTE>06</MINUTE>
     *    <SECOND>02</SECOND>
     *    <SUBSECOND>0000</SUBSECOND>
     *    <TIMEZONE>-0800</TIMEZONE>
     *  </DATETIME>
     *</CNTROLAREA>
     *<DATAAREA>
     *  <PROCESS_PO>
     *    <POORDERHDR>
     *     <DATETIME qualifier="DOCUMENT">
     *       <YEAR>2005</YEAR>
     *       <MONTH>12</MONTH>
     *       <DAY>30</DAY>
     *       <HOUR>07</HOUR>
     *       <MINUTE>08</MINUTE>
     *       <SECOND>33</SECOND>
     *       <SUBSECOND>0000</SUBSECOND>
     *       <TIMEZONE>-0800</TIMEZONE>
     *     </DATETIME>
     *     <OPERAMT qualifier="EXTENDED" type="T">
     *       <VALUE>4556</VALUE>
     *   <NUMOFDEC>2</NUMOFDEC>
     *   <SIGN>+</SIGN>
     *   <CURRENCY>USD</CURRENCY>
     *   <UOMVALUE>1</UOMVALUE>
     *   <UOMNUMDEC>0</UOMNUMDEC>
     *   <UOM/>
     * </OPERAMT>
     * <POID>1573</POID>
     * <POTYPE>AutoOrder</POTYPE>
     * <ACKREQUEST>1</ACKREQUEST>
     * <DESCRIPTN>Test Order</DESCRIPTN>
     * <NOTES/>
     * <USERAREA>
     *<EXT_COST_CENTER>144048</EXT_COST_CENTER>
     *<EXT_ACCOUNT_CODE>657600</EXT_ACCOUNT_CODE>
     *<EXT_EMPLOYEE_NUM>404056</EXT_EMPLOYEE_NUM>
     *<EXT_PROJECT_NUM>256466</EXT_PROJECT_NUM>
     *<EXT_LOCATION>466</EXT_LOCATION>
     *<EXT_NON_REV_NUM>1749-8508-1</EXT_NON_REV_NUM>
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
     *  <NAME index="1">GSE Test Supplier for FedEx</NAME>
     *  <ONETIME>0</ONETIME>
     *  <PARTNRID>29076</PARTNRID>
     *  <PARTNRTYPE>SUPPLIER</PARTNRTYPE>
     *  <CURRENCY>USD</CURRENCY>
     *  <PARTNRIDX>29076</PARTNRIDX>
     *  <USERAREA><SITEID>0</SITEID></USERAREA>
     *</PARTNER>
     *<PARTNER>
     *  <NAME index="1">Aeroxchange Ltd.</NAME>
     *  <ONETIME>0</ONETIME>
     *  <PARTNRID>15561</PARTNRID>
     *  <PARTNRTYPE>SOLDTO</PARTNRTYPE>
     *  <CURRENCY>USD</CURRENCY>
     *  <PARTNRIDX>15561</PARTNRIDX>
     *  <CONTACT>
     *    <NAME index="1">Nikhil Chandurkar</NAME>
     *    <EMAIL>cnikhil@yahoo.com</EMAIL>
     *    <FAX index="1"/>
     *    <TELEPHONE index="1">9725568531 </TELEPHONE>
     *  </CONTACT>
     *</PARTNER>
     *<PARTNER>
     *  <NAME index="1">FedEx 2 Day</NAME>
     *  <ONETIME>0</ONETIME>
     *  <PARTNRID>FE03</PARTNRID>
     *  <PARTNRTYPE>CARRIER</PARTNRTYPE>
     *</PARTNER>
     *<PARTNER>
     *<NAME index="1">My Office</NAME>
     *<ONETIME>0</ONETIME>
     *<PARTNRID>34487</PARTNRID>
     *<PARTNRTYPE>BILLTO</PARTNRTYPE>
     *<PARTNRIDX>15561</PARTNRIDX>
     *<ADDRESS>
     *  <ADDRLINE index="1">5221 O'Connor</ADDRLINE>
     *  <ADDRLINE index="2">Suite 800 E</ADDRLINE>
     *    <ADDRLINE index="3"/>
     *    <ADDRLINE index="4"/>
     *    <CITY>Irving</CITY>
     *    <COUNTRY>US</COUNTRY>
     *    <POSTALCODE>75039</POSTALCODE>
     *    <STATEPROVN>TX</STATEPROVN>
     *  </ADDRESS>
     *</PARTNER>
     *</POORDERHDR>
     *<POORDERLIN>
     *<QUANTITY qualifier="ORDERED">
     *  <VALUE>1</VALUE>
     *  <NUMOFDEC/>
     *  <SIGN>+</SIGN>
     *  <UOM>EA</UOM>
     *</QUANTITY>
     *<OPERAMT qualifier="UNIT" type="T">
     *  <VALUE>4556</VALUE>
     *  <NUMOFDEC>2</NUMOFDEC>
     *  <SIGN>+</SIGN>
     *  <CURRENCY>USD</CURRENCY>
     *  <UOMVALUE>1</UOMVALUE>
     *  <UOMNUMDEC>0</UOMNUMDEC>
     *  <UOM/>
     *</OPERAMT>
     *<POLINENUM>1</POLINENUM>
     *<HAZRDMATL/>
     *<NOTES/>
     *<DESCRIPTN>GSE Test Part</DESCRIPTN>
     *<ITEM/>
     *<ITEMX>GSETESTPART</ITEMX>
     *<USERAREA>
     * <MFRNAME>GSETESTPART</MFRNAME>
     * <MFRNUM>GSETESTPART</MFRNUM>
     * <DATETIME qualifier="NEEDDELV">
     * <YEAR>2006</YEAR>
     * <MONTH>02</MONTH>
     *       <DAY>23</DAY>
     *       <HOUR>21</HOUR>
     *       <MINUTE>59</MINUTE>
     *       <SECOND>00</SECOND>
     *       <SUBSECOND>0000</SUBSECOND>
     *       <TIMEZONE>-0800</TIMEZONE>
     *       </DATETIME>
     *       </USERAREA>
     *      <PARTNER>
     *        <NAME index="1">Global</NAME>
     *        <ONETIME>0</ONETIME>
     *        <PARTNRID>31074</PARTNRID>
     *        <PARTNRTYPE>SHIPTO</PARTNRTYPE>
     *        <PARTNRIDX>15561</PARTNRIDX>
     *        <ADDRESS>
     *          <ADDRLINE index="1">5221 N. O'Connor Blvd.</ADDRLINE>
     *          <ADDRLINE index="2">Suite 800 E</ADDRLINE>
     *          <ADDRLINE index="3"/>
     *          <ADDRLINE index="4"/>
     *          <CITY>Irving</CITY>
     *          <COUNTRY>US</COUNTRY>
     *          <POSTALCODE>75039</POSTALCODE>
     *          <STATEPROVN>TX</STATEPROVN>
     *        </ADDRESS>
     *      </PARTNER>
     *    </POORDERLIN>
     *<POORDERLIN>
     *<QUANTITY qualifier="ORDERED">
     *  <VALUE>1</VALUE>
     *  <NUMOFDEC/>
     *  <SIGN>+</SIGN>
     *  <UOM>EA</UOM>
     *</QUANTITY>
     *<OPERAMT qualifier="UNIT" type="T">
     *  <VALUE>4556</VALUE>
     *  <NUMOFDEC>2</NUMOFDEC>
     *  <SIGN>+</SIGN>
     *  <CURRENCY>USD</CURRENCY>
     *  <UOMVALUE>1</UOMVALUE>
     *  <UOMNUMDEC>0</UOMNUMDEC>
     *  <UOM/>
     *</OPERAMT>
     *<POLINENUM>2</POLINENUM>
     *<HAZRDMATL/>
     *<NOTES/>
     *<DESCRIPTN>GSE Test Part2</DESCRIPTN>
     *<ITEM/>
     *<ITEMX>GSETESTPART2</ITEMX>
     *<USERAREA>
     * <MFRNAME>GSETESTPART</MFRNAME>
     * <MFRNUM>GSETESTPART</MFRNUM>
     * <DATETIME qualifier="NEEDDELV">
     * <YEAR>2006</YEAR>
     * <MONTH>02</MONTH>
     *       <DAY>23</DAY>
     *       <HOUR>21</HOUR>
     *       <MINUTE>59</MINUTE>
     *       <SECOND>00</SECOND>
     *       <SUBSECOND>0000</SUBSECOND>
     *       <TIMEZONE>-0800</TIMEZONE>
     *       </DATETIME>
     *       </USERAREA>
     *      <PARTNER>
     *        <NAME index="1">Global</NAME>
     *        <ONETIME>0</ONETIME>
     *        <PARTNRID>31074</PARTNRID>
     *        <PARTNRTYPE>SHIPTO</PARTNRTYPE>
     *        <PARTNRIDX>15561</PARTNRIDX>
     *        <ADDRESS>
     *          <ADDRLINE index="1">5221 N. O'Connor Blvd.</ADDRLINE>
     *          <ADDRLINE index="2">Suite 800 E</ADDRLINE>
     *          <ADDRLINE index="3"/>
     *          <ADDRLINE index="4"/>
     *          <CITY>Irving</CITY>
     *          <COUNTRY>US</COUNTRY>
     *          <POSTALCODE>75039</POSTALCODE>
     *          <STATEPROVN>TX</STATEPROVN>
     *        </ADDRESS>
     *      </PARTNER>
     *    </POORDERLIN>
     *  </PROCESS_PO>
     *</DATAAREA>
     *</PROCESS_PO_003>
     *
     * </code>
     *
     * @param array $a so ack in TLD array format
     * @param string $poxml original po in xml format
     */
    function inSalesOrderAck($a, $poxml)
    {
        $ack = new SimpleXMLElement('<AEX_GSEPOAcknowledgment xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="AEX_GSEPOAcknowledgment.xsd"></AEX_GSEPOAcknowledgment>');

        $po = simplexml_load_string($poxml);
        $partners = tldERP::parsePartnersSection($po->DATAAREA->PROCESS_PO->POORDERHDR->PARTNER);
        //create header
        $communicationArea = $ack->addChild('CommunicationArea');
        $communicationArea->addChild('Sender', $partners['SUPPLIER']['PARTNRID']);
        $communicationArea->addChild('Receiver', $partners['SOLDTO']['PARTNRID']);
        $communicationArea->addChild('MessageSeqID', time());
        $communicationArea->addChild('CreationDateTime', date('Y-m-d H:i:s'));

        $POAcknowledgmentHeader = $ack->addChild('POAcknowledgmentHeader');
        $POAcknowledgmentHeader->addChild('PONumber', $po->DATAAREA->PROCESS_PO->POORDERHDR->POID);
        //create detail
        if (count($a['DETAIL']['LINE'])) {
            foreach ($a['DETAIL']['LINE'] as $line) {
                $b[(int)$line['SOLINENUM']] = $line;
            }
            if (is_array($po->DATAAREA->PROCESS_PO->POORDERLIN)) {
                foreach ($po->DATAAREA->PROCESS_PO->POORDERLIN as $oLine) {
                    if ($b[(int)$oLine->PONO]['QUANTITY'] > 0) {
                        $POAcknowledgmentDetails = $ack->addChild('POAcknowledgmentDetails');
                        $POAcknowledgmentDetails->addChild('SupplierPartNumber', (string)$oLine->ITEMX);
                        //check if superseeded ie pn do not match
                        if ($oLine->ITEMX <> $b[(int)$oLine->PONO]['ITEM']
                            && $b[(int)$oLine->PONO]['ITEM'] <> '') {
                            $POAcknowledgmentDetails->addChild('ReplacementIndicator', '1');
                            $POAcknowledgmentDetails->addChild('ReplacementPartNumber',
                                $b[(int)$oLine->PONO]['ITEM']);
                            $changeFlag = true;
                        }
                        $POAcknowledgmentDetails->addChild('Quantity', (string)$oLine->QUANTITY->VALUE);
                        $POAcknowledgmentDetails->addChild('Cost', $b[(int)$oLine->PONO]['PRIC']);
                    }
                    //check for quantity change
                    if ((int)$oLine->QUANTITY->VALUE <> $b[(int)$oLine->PONO]['QUANTITY']) {
                        $cancelList[(string)$oLine->ITEMX] = (int)$oLine->QUANTITY->VALUE - $b[(int)$oLine->PONO]['QUANTITY'];
                    }
                }
            }
            if (count($cancelList)) {
                //send cancellation message
//				$cancelList['TLDHEADER'] = $a['TLDHEADER'];
                if (!$this->inCancellation($cancelList, $a, $poxml)) {
                    error_log('erpAero, inSalesOrderAck, error, Could not send cancellation message for');
                }
            }
        }
        $POAcknowledgmentHeader->addChild('MessageTypeIndicator', $changeFlag ? 'C' : 'A');

        $res = false;
        $r = $this->post($ack->asXML());
        if ($r->ResponseMsg->returnCode == 200 || $r->ResponseMsg->returnCode == 1 || $r->ResponseMsg->returnCode == 'Successful') {
            $res = true;
        } elseif (is_array($r)) {
            $error = '';
            foreach ($r as $ResponseMsg) {
                $error .= $ResponseMsg->errorMsg . "\n";
            }
        } else {
            $error = $r->errorMsg;
        }
        tldArchive::insert($a['TLDHEADER']['COMP'], 'SalesOrderAck', $a['TLDHEADER']['PONUM'],
            date('Y-m-d'), '',
            $this->itsERP,
            $ack->asXML(),
            $error . 'Cancel List' . print_r($cancelList, true));
        return $res ? true : "ERROR: erpAero, inSalesOrderAck, error\n $error\n ";
    }

    /**
     * Cancel the whole order
     *
     * @param array $a
     * @param string $poxml
     */
    function cancelWholeOrder($src, $num)
    {
        $destERP = tldERP::getERPOb($this->itsERP);
        //get original po from archive
        $arch = new tldArchive($this->itsERP);
        //gets the last one
        $rows = $arch->byTypeID('PurchaseOrder', $num);
        $poxml = $rows[0]['xml'];
        $po = simplexml_load_string($poxml);

        if (count($po->DATAAREA->PROCESS_PO->POORDERLIN)) {
            foreach ($po->DATAAREA->PROCESS_PO->POORDERLIN as $line) {
                $cancelList[(string)$line->ITEMX] = (int)$line->QUANTITY->VALUE;
            }
        } else {
            return false;//no lines to cancel
        }

        $h['TLDHEADER']['DEST'] = $this->itsERP;
        $h['TLDHEADER']['COMP'] = $src;
        $h['TLDHEADER']['PONUM'] = $num;
        $result = $this->inCancellation($cancelList, $h, $poxml);

        if (is_string($result)) {
            $response = $result;
        }
        tldArchive::insert($src, 'CancelWholeOrder', $a['TLDHEADER']['PONUM'],
            date('Y-m-d'), '', $this->itsERP, '', $response);
        return $response ? $response : $result;

    }

    /**
     * Special cancellation message only for Aero
     *
     * @param array $a list of PN to cancel as keys, values are quantity to cancel
     * @param array $header TLD Header info
     * @param string $poxml original po
     * @return boolean
     */
    function inCancellation($a, $header, $poxml)
    {
        if (count($a) == 0 || !is_array($a)) {
            return;
        } //if nothing to cancel then just return!
        error_log('erpAero, inCancellation');
        $cancelMsg = new SimpleXMLElement('<AEX_GSEPOCancellation xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="AEX_GSEPOCancellation.xsd"></AEX_GSEPOCancellation>');

        $po = simplexml_load_string($poxml);
        $partners = tldERP::parsePartnersSection($po->DATAAREA->PROCESS_PO->POORDERHDR->PARTNER);
        $communicationArea = $cancelMsg->addChild('CommunicationArea');
        $communicationArea->addChild('Sender', $partners['SUPPLIER']['PARTNRID']);
        $communicationArea->addChild('Receiver', $partners['SOLDTO']['PARTNRID']);
        $communicationArea->addChild('MessageSeqID', time());
        $communicationArea->addChild('CreationDateTime', date('Y-m-d H:i:s'));

        $POCancellationHeader = $cancelMsg->addChild('POCancellationHeader');
        $POCancellationHeader->addChild('RequisitionNumber', $po->CNTROLAREA->SENDER->LOGICALID);
        $POCancellationHeader->addChild('PONumber', $po->DATAAREA->PROCESS_PO->POORDERHDR->POID);
        if (count($po->DATAAREA->PROCESS_PO->POORDERLIN)) {
            $cancelItems = array_keys($a);
            foreach ($po->DATAAREA->PROCESS_PO->POORDERLIN as $line) {
                $item = (string)$line->ITEMX;
                if (in_array($item, $cancelItems)) {
                    $POCancellationDetails = $cancelMsg->addChild('POCancellationDetails');
                    $POCancellationDetails->addChild('SupplierPartNumber', $item);
                    $POCancellationDetails->addChild('Quantity', $a[$item]);
                    $POCancellationDetails->addChild('ReasonCode', 4);
                }
            }
        }
        $r = $this->post($cancelMsg->asXML());
        tldArchive::insert($header['TLDHEADER']['COMP'], 'CancellationMessage', $header['TLDHEADER']['PONUM'],
            date('Y-m-d'), '', $this->itsERP, $cancelMsg->asXML());
        if ($r->returnCode == 200 || $r->returnCode == 1) {
            return true;
        } elseif (count($r)) {
            $error = '';
            foreach ($r as $ResponseMsg) {
                $error .= $ResponseMsg->errorMsg . "\n";
            }
        } else {
            $error = $r->errorMsg;
        }
        return "ERROR: erpAero, inCancellation, error\n $error\n ";
    }

    /**
     * Get inbound relationship data
     *
     * If src and dst are specified then then only data for that relationship is
     * give, otherwise all relationship info is returned
     *
     * @param integer $src source company number
     * @param integer $dst destination company number
     * @return array
     */
    public static function getInboundRelation($src = '', $dst = '')
    {
        $a = [300 => [800 => ['t_cuno' => 280]]];
        if ($src && $dst) {
            return $a[$src][$dst];
        }

        return $a;
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
    function getOutboundRelation($src = '', $dst = '')
    {
        $a = [300 => [800 => ['sender' => 29116, 'receiver' => 29073]]];
        if ($src && $dst) {
            return $a[$src][$dst];
        }

        return $a;
    }

    /**
     * Get array of address data from xml
     *
     * @param simpleXMLelement $x
     * @return array
     */
    function parseAddressSection($x)
    {
        $ADDRLINE = [];
        if ($x->ADDRLINE) {
            foreach ($x->ADDRLINE as $k => $v) {
                if (!$v->__toString()) {
                    continue;
                }
                $ADDRLINE[] = $v->__toString();
            }
        }
        return [
            'ADDRLINE' => (string)implode(', ', $ADDRLINE),
            'CITY' => (string)$x->CITY,
            'STATEPROVN' => (string)$x->STATEPROVN,
            'POSTALCODE' => (string)$x->POSTALCODE,
            'COUNTRY' => (string)$x->COUNTRY,
        ];
    }

    /**
     * Get array of contact from xml
     *
     * @param simpleXMLelement $x
     * @return array
     */
    public static function parseContactSection($x)
    {
        $contacts = [];
        foreach ($x as $contact) {
            $contacts[] = [
                'NAME' => (string)$contact->NAME,
                'EMAIL' => (string)$contact->EMAIL,
            ];
        }
        return $contacts;
    }

    /**
     * Get array of partners from xml
     *
     * @param simpleXMLelement $x
     * @return array
     */
    public static function parsePartnersSection($x)
    {
        $partners = [];
        foreach ($x as $partner) {
            $a = [
                'NAME' => (string)$partner->NAME,
                'ONETIME' => (string)$partner->ONETIME,
                'PARTNRID' => (string)$partner->PARTNRID,
                'PARTNRTYPE' => (string)$partner->PARTNRTYPE,
                'CURRENCY' => (string)$partner->CURRENCY,
                'PARTNRIDX' => (string)$partner->PARTNRIDX,
            ];
            if (!empty($partner->CONTACT)) {
                $a['CONTACT'] = self::parseContactSection($partner->CONTACT);
            }
            $partners[(string)$partner->PARTNRTYPE] = $a;
        }
        return $partners;
    }

    /**
     * Convert aeroxchange purchase order to TLD array format
     *
     * @param string $xml
     * @return array
     */
    function outPurchaseOrder($xml)
    {
        if (empty($xml)) {
            return [];
        }
        $x = new SimpleXMLElement($xml);
        // Separate all partner info
        $partners = self::parsePartnersSection($x->DATAAREA->PROCESS_PO->POORDERHDR->PARTNER);
        // Get delivery address from first PO line
        $add = self::parseAddressSection($x->DATAAREA->PROCESS_PO->POORDERLIN->PARTNER[0]->ADDRESS);
        // Get some user info who submitted this PO
        $userarea = $x->DATAAREA->PROCESS_PO->POORDERHDR->USERAREA;
        // Creation the SO array that will be sent to TLD connector
        $a = [
            'TLDHEADER' => [
                'COMP' => (string)$this->itsERP,
                'TYPE' => 'PROCESS_PO',
                'PONUM' => (string)$x->DATAAREA->PROCESS_PO->POORDERHDR->POID],
            'header' => [
                'customer' => ['t_cuno' => $partners['SOLDTO']['PARTNRID']],
                'custpo' => (string)$x->DATAAREA->PROCESS_PO->POORDERHDR->POID,
                //"carrier"=>array("t_cfrw"=>$partners['CARRIER']['NAME']),
                'shipComplete' => 'shipcomplete',
                'note' => $partners['CARRIER']['NAME'] . "\n" .
                    'Cost Center:' . (string)$userarea->EXT_COST_CENTER . "\n" .
                    'Account Code:' . (string)$userarea->EXT_ACCOUNT_CODE . "\n" .
                    'Employee Num:' . (string)$userarea->EXT_EMPLOYEE_NUM . "\n" .
                    'Project Num:' . (string)$userarea->EXT_PROJECT_NUM . "\n" .
                    'Location:' . (string)$userarea->EXT_LOCATION . "\n" .
                    'Non Rev Num:' . (string)$userarea->EXT_NON_REV_NUM,
                'deliverAddress' => [
                    'line1' => $add['ADDRLINE'],
                    'line2' => $add['CITY'],
                    'line3' => $add['STATEPROVN'],
                    'line4' => $add['POSTALCODE'],
                    'line5' => $add['COUNTRY'],
                ],
            ],
        ];
        // loop through each line of the PO and add them to the SO array
        foreach ($x->DATAAREA->PROCESS_PO->POORDERLIN as $i) {
            $a['lines'][(string)$i->ITEMX] = [
                'product' => (string)$i->ITEMX,
                'qty' => (string)$i->QUANTITY->VALUE,
            ];
        }
        return $a;
    }

    /**
     * Convert aeroxchange invoice to TLD array format
     *
     * @param string $xmlstring
     * @return array
     */
    function outInvoice($xml)
    {
        return $a;
    }

    /**
     * Convert aeroxchange sales order acknowledgement to TLD array format
     *
     * @param string $xml
     * @return array $a
     */
    function outSalesOrderAck($xml)
    {
        return $a;
    }

    /**
     * Convert aeroxchange DELIVERY NOTE to TLD array format
     *
     * @param string $xmlstring
     * @return array
     */
    function outDeliveryNote($xml)
    {
        return $a;
    }
}

/**
 * Class for communicating with Sage/Prophet21 ERP system
 *
 * @package ERP
 */
class erpProphet21 implements basicERP
{
    public $theURL;
    public $itsERP; //erp company number

    function __construct($erp)
    {
        $this->itsERP = $erp;
        switch ($erp) {
            case '390': //PRODUCTION
                $this->theURL = 'http://esage.sageparts.com/autoorder/Receive_CXML.aspx?CID=TLD&MTYPE=PO';
                $this->itsAuth['shared_secret'] = '854F577F90B540EC9AB93F1B6D9E6E4F';
                break;
            case '391': //TEST
                $this->theURL = 'http://192.168.1.100/esage_dev/autoorder/Receive_CXML.aspx?CID=TLD&MTYPE=PO';
                $this->itsAuth['shared_secret'] = '854F577F90B540EC9AB93F1B6D9E6E4F';
                break;
        }
    }

    function getSharedSecret()
    {
        return $this->itsAuth['shared_secret'];
    }

    /**
     * Factory for creating aero objects
     *
     * @param string $type
     * @param array $p
     */
    function factory($type, $p)
    {
        return null;
//        switch ($type) {
//            case 'PURCHASE_ORDER':
//
//                break;
//            case 'SALES_ORDER':
//
//                break;
//            case 'SALES_ORDER_ACK':
//
//                break;
//            case 'INVOICE':
//
//                break;
//        }
//        return $result;
    }

    /**
     * Post a document to the erp system
     *
     * @param mixed $xml
     * @return simpleXMLElement
     */
    function post($xml)
    {
        $ch = curl_init($this->theURL);
        if (!$ch) {
            return 'ERROR: cannot open ' . $this->theURL;
        }
        curl_setopt_array($ch,
            [CURLOPT_SSL_VERIFYPEER => false,
//                    CURLOPT_USERPWD => $this->theUSERID.":".$this->thePASSWD,
                //										CURLOPT_HEADER => true,
                CURLOPT_HTTPHEADER => ['Content-Type: text/xml'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $xml,
            ]
        );

        $code = curl_exec($ch);
        if (curl_errno($ch)) {
            error_log('erp.inc.php, erpProphet21->post, ERROR: ' .
                curl_error($ch) . '-' . curl_error($ch));
        }
        error_log($code);
        curl_close($ch);
        $x = simplexml_load_string($code);
        return $x->Response->Status['code'] == 200 ? 0 : $x->Response->Status['text'];
    }

    /**
     * receive a DELIVERY NOTE in native erp system xml
     *
     *
     * @param array $a delivery note in TLD Standard array format
     * @param string $poxml original po xml
     * @return array
     */
    function inDeliveryNote($a, $poxml)
    {
    }

    /**
     * Post a invoice to this erp system
     *
     * @param array $a
     * @param string $poxml original po xml
     */
    function inInvoice($a, $poxml)
    {
        ;
    }

    /**
     * Post a sales order to this erp system
     *
     * @param array $a
     */
    function inPurchaseOrder($a)
    {
        $cxml = new tldCXML($this->itsERP);
        $xml = $cxml->inPurchaseOrder($a, $this->itsAuth);
        $e = $this->post($xml);
        return $e;
    }

    /**
     * Post a sales order to this erp system
     *
     *
     * @param array $a
     * @param string $poxml original po xml
     */
    function inSalesOrder($a, $poxml)
    {
        ;
    }

    /**
     * Post a sales order ack to this erp system
     *
     *
     * @param array $a so ack in TLD array format
     * @param string $poxml original po in xml format
     */
    function inSalesOrderAck($a, $poxml)
    {
    }

    /**
     * Get inbound relationship data
     *
     * If src and dst are specified then then only data for that relationship is
     * give, otherwise all relationship info is returned
     *
     * @param integer $src source company number
     * @param integer $dst destination company number
     * @return array
     */
    public static function getInboundRelation($src = '', $dst = '')
    {
        $a[300][390] = ['shared_secret' => 280];//example
        if ($src && $dst) {
            return $a[$src][$dst];
        }

        return $a;
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
    function getOutboundRelation($src = '', $dst = '')
    {
        $a[300][800] = ['sender' => 29116, 'receiver' => 29073];//example
        if ($src && $dst) {
            return $a[$src][$dst];
        } else {
            return $a;
        }
    }

    /**
     * Convert aeroxchange purchase order to TLD array format
     *
     * @param string $xml
     * @return array
     */
    function outPurchaseOrder($xml)
    {
        if (empty($xml)) {
            return [];
        }
        $cxml = new tldCXML($this->itsERP);
        $a = $cxml->outPurchaseOrder($xml);
        return $a;
    }


    /**
     * Convert aeroxchange invoice to TLD array format
     *
     * @param string $xmlstring
     * @return array
     */
    function outInvoice($xml)
    {
        return $a;
    }

    /**
     * Convert aeroxchange sales order acknowledgement to TLD array format
     *
     * @param string $xml
     * @return array $a
     */
    function outSalesOrderAck($xml)
    {
        return $a;
    }

    /**
     * Convert aeroxchange DELIVERY NOTE to TLD array format
     *
     * @param string $xmlstring
     * @return array
     */
    function outDeliveryNote($xml)
    {
        return $a;
    }
}

class SageAPI
{
    // Settings:

    // DEV Environment:
    #protected $_SharedSecret = 'A1D295D9-D7A1-4D7D-A75C-8285D951D87C'; // DEV
    #protected $_WSDL = 'http://edi.sageparts.com/Webservices_DEV/priceandavailability.asmx?WSDL'; // DEV

    // PROD Environment:
    protected $_SharedSecret = '10DFB4EE-E777-41B3-A02D-C67A3CDCE661'; // PROD
    protected $_WSDL = 'https://edi2.sageparts.com/webservices/priceandavailability.asmx?WSDL'; // PROD

    // GLOBAL Environment:
    protected $_MTYPE = 'PnA';
    protected $_CID = 'TLD';


    // Public Methods
    public function getPriceAndAvailability($items, $price = 'NO')
    {
        $date = date('c');
        $xml = <<<EOF
<?xml version="1.0" encoding="utf-8"?>
<PriceAndAvailability payloadID="201305101513504024886@Lan.com" timestamp="2013-05-10T15:13:56-04:00">
  <Header>
    <From>
      <Credential domain="TLD.com">
        <Identity>support@TLD.com</Identity>
      </Credential>
    </From>
    <To>
      <Credential domain="">
        <Identity></Identity>
      </Credential>
    </To>
    <Sender>
      <Credential domain="TLD.com">
        <Identity></Identity>
        <SharedSecret>{$this->_SharedSecret}</SharedSecret>
        <UserAgent>Administrator</UserAgent>
      </Credential>
    </Sender>
  </Header>
  <ItemInquiryRequest PriceItems="$price">
EOF;

        foreach ((array)$items AS $i => $item) {
            $line = $i + 1;
            $xml .= <<<EOF
    <Item qty="1" unit="EACH" lineNumber="$line">
      <MasterID><![CDATA[$item]]></MasterID>
    </Item>
EOF;
        }

        $xml .= <<<EOF
  </ItemInquiryRequest>
</PriceAndAvailability>\n\n
EOF;
        $soap = $this->_getSOAP();
        try {
            $r = $soap->PriceAndAvailabilityRequest($this->_getRequestParameters($xml));
        } catch (Exception $e) {
            $r = $e->faultstring;
        }
        return tldUtils::convXMLToArray($r->PriceAndAvailabilityRequestResult, ['complexType' => 'array', 'parseAttributes' => true]);
    }


    // Helpers
    protected function _getRequestParameters($xml)
    {
        return ['CID' => $this->_CID, 'MTYPE' => $this->_MTYPE, 'cXML' => $xml];
    }

    protected function _getSOAP()
    {
        static $client;
        if (!$client) {
            $client = new SoapClient($this->_WSDL);
        }
        return $client;
    }

    public static function getLocation($loc)
    {
        static $locations = [
            '100001' => 'Farmingdale',
            '104081' => 'LAX2',
            '105102' => 'ORD',
            '105651' => 'GILROY',
            '112811' => 'ATL',
            '115139' => 'DFW',
            '115589' => 'LAX1',
            '115886' => 'YYZ',
            '115887' => 'YUL',
            '115888' => 'YVR',
            '122958' => 'HKG',
            '125772' => 'JFK',
            '126415' => 'AMS',
            '127647' => 'CDG',
            '131093' => 'MEM2',
            '132143' => 'LGA',
            '133018' => 'MEM1',
            '133020' => 'DTW',
            '133022' => 'MSP',
            '133751' => 'PrestonUK',
            '133773' => 'LGWUK1',
            '133776' => 'LGWUK2',
            '133780' => 'LHR',
            '133784' => 'MANUK2',
            '133788' => 'MANUK1',
            '138533' => 'MIA2',
            '138534' => 'MIA1',
        ];
        return (isset($locations[$loc])) ? $locations[$loc] : $loc;
    }
}

