<?php
include_once("erp.inc.php");
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$a = array();
$p = array();

switch(strtolower($courier)){
case 'ups':
    $url = "http://wwwapps.ups.com/etracking/tracking.cgi";
    $a['tracknums_displayed'] = 5;
    $a['TypeOfInquiryNumber'] = 'T';
    $a['HTMLVersion'] = '4.0';
    $a['InquiryNumber1'] = $trno;
break;
case 'fed':
    $url = "http://www.fedex.com/Tracking";
    $a['ascend_header'] = 1;
    $a['clienttype'] = 'dotcom';
    $a['cntry_code'] = 'us';
    $a['language'] = 'english';
    $a['tracknumbers'] = $trno;
break;
case 'dhl':
    $url = "http://www.dhl.com/cgi-bin/tracking.pl";
    $a['TID'] = "CP_ENG";
    $a['FIRST_DB'] = '';
    $a['AWB'] = $trno;
break;
case 'tnt':
    $url = "http://www.tnt.com/webtracker/tracking.do";
    $a['respCountry'] = "us";
    $a["respLang"] = "en";
    $a["navigation"] = 1;
    $a["page"] = 1;
    $a["sourceID"] = 1;
    $a["sourceCountry"] = "ww";
    $a["plazaKey"] = "";
    $a["refs"] = "";
    $a["requesttype"] = "GEN";
    $a["cons"] = $trno;
break;
case 'ocs':
    $a["sURL"] = urlencode("http://www.shipocs.com/");
    $a["noshipmentURI"] = urlencode("http://www.shipocs.com/trackingSupport.asp");
    $a["target"] = urlencode("_self");
    $a["CWBs"] = urlencode($trno);
    $url = "http://www.ocs.co.jp/multitracking/tracking/template/MultiQuery.vm/action/MultiTracking?";
break;
case 'ems':
    $url = "http://www.ems.com.cn/chinese-main.jsp";
break;
case 'awb':
case 'bol':
	$fileID = tldModFile::byParent($id,"TRACKING");
	$fileID = $fileID[0]['id'];
	$file = new tldModFile($fileID);
	$file->outFile();
	exit;
break;
case 'exapa': // for compatibility issue (commit Feb 17th -> to be deleted in a month)
case 'exapaq': // correct courrier name
    $url = "http://e-trace.ils-consult.fr/exa-webtrace/webtrace.aspx";
    $a['cmd'] = 'SDG_MULTI_SEARCH';
    $a['sdgnrs'] = $trno;
break;
default:
    echo "courier not supported";
    exit;
}
foreach($a as $k=>$v){
    $p[] = urlencode($k)."=".urlencode($v);
}
?>
<meta HTTP-EQUIV="REFRESH" content="0; url=<?= $url."?".implode("&", $p)?>">


