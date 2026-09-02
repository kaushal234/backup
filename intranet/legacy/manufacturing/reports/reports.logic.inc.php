<?php
$PATH .= "/reports";
$DEFAULT_TITLE .= "\Reports";
include 'sales_service.inc.php';
require_once('HTML/QuickForm/advmultiselect.php');
switch($m[1]){
case 'listing':
	switch($m[2]){
    case 'subassembly':
        $xItems = [
            "t_pdno"        =>"production order number",
            "t_mitm"        =>"item",
            "t_cprj"        =>"project",
            "t_pono"        =>"operation",
            "t_sitm"        =>"item",
            "t_dsca"        =>"Description",
            "t_opno"        =>"Operation",
            "t_cwar"        =>"warehouse",
            "t_stoc"        =>"stock",
            "t_ques"        =>"estimated quantity",
            "t_qucs"        =>"actual quantity",
            "t_cpcs"        =>"actual cost price",
            "t_issu"        =>"issue",
            "t_subd"        =>"subsequent delivery",
            "t_buyr"        =>"buyer",
            "Delta - Ruptures"=>"Delta - Ruptures" ,
            "t_osta"        =>"order status",
            "t_oltm"        =>"order lead time",
            "t_ordr"        =>"inventory on order",
            "t_allo"        =>"allocated inventory",
            "t_oqmf"        =>"order quantity multiple",
            "t_mioq"        =>"minimum order quantity",
            "t_ecoq"        =>"economic order quantity",
            "t_sfst"        =>"safety stock",
            "t_prdt"        =>"production start date",
            "t_dldt"        =>"production delivery date",
        ];
        // Get listing
        $erpList = tldLocation::getERPList("smartyOptions");
        // Get form
        $form = new HTML_QuickForm('frmsubassembly', 'get', "", "", "", true);
        $form->addElement(	'hidden', 'm[0]', 'reports');
        $form->addElement(	'hidden', 'm[1]', 'listing');
        $form->addElement(	'hidden', 'm[2]', 'subassembly');
        $form->addElement(	'header', 'title','Select company:');
        $form->addElement(	'select', 'z',	'Company#',	array(""=>"")+$erpList);
        $form->addElement(	'text',   'from',	'Production Order from');
        $form->addElement(	'text',   'to',	    'Production Order to');
        $form->addElement(	'submit', 'btnSubmit', 'Submit');
        $form->addRule('z', 'This is required', 'required');
        $form->addRule('from', "Must be numeric","numeric");
        $form->addRule('to', "Must be numeric","numeric");
        // Get default ERP for connected user
        $location = new tldLocation($user->getBUID());
        $form->setDefaults(["z"=>$location->getERP()]);
        $form->setDefaults(["from"=>610000]);
        $form->setDefaults(["to"=>680000]);
        if($form->validate()){
            $vars = tldUtils::cleanupFormInput($form->exportValues());

            $query="SELECT  tticst001420.t_pdno, ttisfc001420.t_mitm, tticst001420.t_cprj, tticst001420.t_pono, ";
            $query.=" tticst001420.t_sitm, ttiitm001420.t_dsca, tticst001420.t_opno, tticst001420.t_cwar, ";
            $query.="ttiitm001420.t_stoc, tticst001420.t_ques, tticst001420.t_qucs, tticst001420.t_cpcs, ttisfc001420.t_osta, ttiitm001420.t_oltm, ttiitm001420.t_ordr, ttiitm001420.t_allo, ";
            $query.="tticst001420.t_issu, tticst001420.t_subd, ttiitm001420.t_buyr,  ttiitm001420.t_stoc-tticst001420.t_ques AS 'Delta - Ruptures',ttiitm001420.t_oqmf, ttiitm001420.t_mioq, ttiitm001420.t_ecoq, ttiitm001420.t_sfst,ttisfc001420.t_prdt, ttisfc001420.t_dldt ";
            $query.="FROM baandb.dbo.tticst001{$vars['z']} tticst001420,baandb.dbo.ttiitm001{$vars['z']} ttiitm001420,baandb.dbo.ttisfc001{$vars['z']} ttisfc001420 ";
            $query.="WHERE  tticst001420.t_sitm=ttiitm001420.t_item AND tticst001420.t_pdno=ttisfc001420.t_pdno ";
            $query.="AND tticst001420.t_pdno BETWEEN {$vars['from']} AND {$vars['to']} AND ttisfc001420.t_osta <= 5 ";

            $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);

            $caption = "Sub-assembly report for company {$vars['z']}";
            $report = new tldReportColumnar(
                $rows,
                [
                    'xItems' => $xItems,
                    'links' => '',
                    'title' => $caption,
                    'showNumberOfRows' => true,
                ]
            );
            $body = $report->fetch();
        }
        $body = $form->toHTML();
    break;
    case 'GTQtyReport':
        $kpiFactoryList = tldUtils::optionsByKeyValue(
            tldLocation::byConstraints(['disable' => 0, 'factory' => 'Y', 'hidden' => 0]),
            'id',
            'location'
        );
        $status = array("All", "Free", "Selected");

        // Get form
        $form = new HTML_QuickForm('frmInwardP_List_Shipped', 'get', "", "", "", true);
        $form->addElement(	'hidden', 'm[0]', 'reports');
        $form->addElement(	'hidden', 'm[1]', 'listing');
        $form->addElement(	'hidden', 'm[2]', 'GTQtyReport');
        $form->addElement(	'header', 'title','Select company:');
        $form->addElement('text', 'date', 'Date', ['class' => 'date-picker']);
        $form->addElement(	'select', 	'z',	'Company#',	array(""=>"")+$kpiFactoryList,["id"=>"bu_id"]);
        $form->addElement(	'select', 	's',	'Type',	['estimated'=>'Estimated GT','promised'=>'Promised GT', 'released'=>'Released', '1st'=>'1st GT']);
        $form->addElement('radio', 'unit_type', '', 'All Units [T + P]', 'ALL');
        $form->addElement('radio', 'unit_type', '', 'T Units only', 'T');
        $form->addElement('radio', 'unit_type', '', 'P Units only', 'P');
        $form->addElement(	'submit', 'btnSubmit', 'Submit');
        $form->addRule('z', 'This is required', 'required');
        $form->addRule('s', 'This is required', 'required');
        $form->addRule('date', 'This is required', 'required');
        $form->setDefaults(['unit_type' => 'ALL']);
        if ($form->validate()) {
            $vars = tldUtils::cleanupFormInput($form->exportValues());
            $factoryName = tldLocation::getLocationByID($vars['z']);
            $strdate = str_replace('-','',$vars['date']);
            $factorycondition = " AND man_location LIKE '$factoryName' ";

            $unitTypeCondition = $vars['unit_type'] === 'ALL' ? '' : " AND service.sn LIKE '{$vars['unit_type']}%' ";
            if($vars['s'] === 'estimated'){
                $title = "Estimated GT ER list in {$vars['date']} - $factoryName";
                $query = <<<EOF
                SELECT service.*,t3.ddel_est1 as promiseddate
FROM service
       LEFT JOIN sor_units AS t3 ON t3.id=service.sor_uid
WHERE PERIOD_DIFF('$strdate', DATE_FORMAT(service.dgt_rev,'%Y%m')) >0
  $unitTypeCondition
  AND service.dgt_act='' AND service.dgt_com='' AND service.dyt='' AND service.dgt_rev <>'' AND service.date_shipped IS NULL
  $factorycondition
UNION                
(SELECT service.*,t3.ddel_est1 as promiseddate
FROM service
LEFT JOIN sor_units AS t3 ON t3.id=service.sor_uid
WHERE DATE_FORMAT( service.dgt_rev, '%Y-%m' ) LIKE '{$vars["date"]}' 
$unitTypeCondition
AND ((service.dgt_act='' AND service.dgt_com='' AND service.dyt='') OR (service.dgt_com<>''  AND PERIOD_DIFF('$strdate', DATE_FORMAT(service.dgt_com,'%Y%m')) <=0 ))
$factorycondition)
EOF;

                $item = [

                    "id" => "ER#",
                    "sor_uid" => "SOR#",
                    "sn" => "S/N",
                    "type" => "Type",
                    "model" => "Model",
                    "man_location" => "Manufacture location",
                    "customer_name" => "Customer",
                    "sales_org" => "Sales Orgnization",
                    "promiseddate" => "Promised Date",
                    "dyt" => "Yellow Tag Date",
                    "dgt_rev" => "Estimated Green Tag Date",
                    "dgt_com" => "First Green Tag Date",
                    "dgt_act" => "Actual Green Tag Date",
                    "date_shipped" => "Actual Ship Date",

                ];
            }
            if($vars['s'] === 'promised'){
                $strdate = str_replace('-','',$vars['date']);
                $title = "Factory promised customer date ER list in {$vars['date']} - $factoryName";
                $unitTypeCondition = " AND service.sn LIKE 'T%' ";
                $query = <<<SQL
(
    SELECT service.*, t3.ddel_est1 as promiseddate
    FROM service
    LEFT JOIN sor_units AS t3 ON t3.id = service.sor_uid
    WHERE
      PERIOD_DIFF('$strdate', DATE_FORMAT(t3.ddel_est1,'%Y%m')) > 0 
      $unitTypeCondition
      AND (service.dgt_com = '' OR service.dgt_com >= '{$vars['date']}-01')
      AND (service.date_shipped = '' OR service.date_shipped >= '{$vars['date']}-01')
      $factorycondition
) UNION (
    SELECT service.*, t3.ddel_est1 as promiseddate
    FROM service
    LEFT JOIN sor_units AS t3 ON t3.id=service.sor_uid
    WHERE
      DATE_FORMAT( t3.ddel_est1, '%Y%m' ) LIKE '$strdate'
      $unitTypeCondition
      AND ((service.dgt_com='' AND service.dgt_act = '') OR DATE_FORMAT(service.dgt_com, '%Y%m') LIKE '$strdate')
      $factorycondition
)
SQL;

                $item = [

                    "id" => "ER#",
                    "sor_uid" => "SOR#",
                    "sn" => "S/N",
                    "type" => "Type",
                    "model" => "Model",
                    "man_location" => "Manufacture location",
                    "customer_name" => "Customer",
                    "sales_org" => "Sales Orgnization",
                    "promiseddate" => "Promised Date",
                    "dyt" => "Yellow Tag Date",
                    "dgt_rev" => "Estimated Green Tag Date",
                    "dgt_com" => "First Green Tag Date",
                    "dgt_act" => "Actual Green Tag Date",
                    "date_shipped" => "Actual Ship Date",

                ];
            }
            if($vars['s'] === '1st'){
                $title = "First GT ER list in {$vars['date']} - $factoryName";
                $query = <<<EOF
SELECT service.*,t3.ddel_est1 as promiseddate
FROM service
LEFT JOIN sor_units AS t3 ON t3.id=service.sor_uid
WHERE DATE_FORMAT( dgt_com, '%Y-%m' ) LIKE '{$vars["date"]}' 
$unitTypeCondition
$factorycondition
EOF;

                $item = [

                    "id" => "ER#",
                    "sor_uid" => "SOR#",
                    "sn" => "S/N",
                    "type" => "Type",
                    "model" => "Model",
                    "man_location" => "Manufacture location",
                    "customer_name" => "Customer",
                    "sales_org" => "Sales Orgnization",
                    "promiseddate" => "Promised Date",
                    "dyt" => "Yellow Tag Date",
                    "dgt_rev" => "Estimated Green Tag Date",
                    "dgt_com" => "First Green Tag Date",
                    "dgt_act" => "Actual Green Tag Date",
                    "date_shipped" => "Actual Ship Date",

                ];
            }
            if($vars['s'] === 'released'){
                $specificUnitTypeCondition = str_replace('service.sn', 'qst.unit', $unitTypeCondition);
                $title = "Released ER list in {$vars['date']} - $factoryName";
                $query =<<<SQL
SELECT s.*,max(ans.created_on) as releaseddate,t3.ddel_est1 as promiseddate
from pi_questions_unit qst 
left join service s ON qst.unit = s.sn 
left join pi_crab_eap cr on qst.id = cr.question_id 
left join pi_answers ans on qst.id = ans.parent_id and ans.active = 'Y' 
LEFT JOIN sor_units AS t3 ON t3.id=s.sor_uid
Where qst.t_opno = 990 AND s.man_location LIKE '$factoryName' and qst.active = 'Y' 
$specificUnitTypeCondition
group by qst.unit having max(ans.created_on) LIKE '{$vars["date"]}%' and (count(qst.id) - count(ans.id)) = 0 
SQL;
                $item = [

                    "id" => "ER#",
                    "sor_uid" => "SOR#",
                    "sn" => "S/N",
                    "type" => "Type",
                    "model" => "Model",
                    "man_location" => "Manufacture location",
                    "customer_name" => "Customer",
                    "sales_org" => "Sales Orgnization",
                    "promiseddate" => "Promised Date",
                    "releaseddate" => "Released Date",
                    "dyt" => "Yellow Tag Date",
                    "dgt_rev" => "Estimated Green Tag Date",
                    "dgt_com" => "First Green Tag Date",
                    "dgt_act" => "Actual Green Tag Date",
                    "date_shipped" => "Actual Ship Date",

                ];
            }

            $rows = $query ? tldUtils::getSqlToAssocArray($query) : [];

            $report = new tldReportColumnar(
                $rows,
                [
                    'xItems' => $item,
                    'links' => ['id' => '/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view&id='],
                    'title' => $title,
                    'showNumberOfRows' => true,
                ]
            );
            $body = $report->fetch();
break;
        }
        $js = "<script type='text/javascript'>
$(document).ready(function()  {

    var selector = $('.date-picker');
    selector.datepicker( {
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
        dateFormat: 'yy-mm',
        onClose: function(dateText, inst) { 
            $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
        }
    });

});

</script>

<style type='text/css'>
.ui-datepicker-calendar {
    display: none;
    }
</style>";


        $smarty = tldUtils::getSmarty("intranet");
        $smarty->assign("html_head",$js);
        $body .= $form->toHTML();
    break;
    case 'InwardP_List_Shipped':
        if(!$user->isInGroup(array("role_INWD"))){
            $body= "<font color=red><b>You do not have permissions for this page...</b></font>";
            break;
        }
        $xItems = array(
            "comp"        =>"Company",
            "sn"          =>"ER#",
            "model"       =>"Model",
            "airport_code"=>"Final Destination",
            "ctry_code"   =>"Country",
            "dt_shipped"  =>"Shipped date",
            "t_prno"      =>"Manufacturing project",
            "t_pdno"      =>"Production Order",
            "t_pono"      =>"Position",
            "t_opno"      =>"Operation",
            "t_sitm"      =>"Item",
            "t_dsca"      =>"Description",
            "t_copr"      =>"Std cost price",
            "t_qucs"      =>"Quantity",
            "inco" => "inco term",
//            "selected"=>"Selected"
        );
        // Get listing
        $erpList = tldLocation::getERPList("smartyOptions");
        $status = array("All", "Free", "Selected");
        // Get form
        $form = new HTML_QuickForm('frmInwardP_List_Shipped', 'get', "", "", "", true);
        $form->addElement(	'hidden', 'm[0]', 'reports');
        $form->addElement(	'hidden', 'm[1]', 'listing');
        $form->addElement(	'hidden', 'm[2]', 'InwardP_List_Shipped');
        $form->addElement(	'header', 'title','Select company:');
        $form->addElement(	'date', 	'x',	'From',
            array("format"=>"Y-m-d","minYear"=>date('Y')-2,"maxYear"=>date('Y')+2));
        $form->addElement(	'date', 	'y',	'To',
            array("format"=>"Y-m-d","minYear"=>date('Y')-2,"maxYear"=>date('Y')+2));
        $form->addElement(	'select', 	's',	'Status',	array_combine($status, $status));
        $form->addElement(	'select', 	'z',	'Company#',	array(""=>"")+$erpList);
        $form->addElement(	'submit', 'btnSubmit', 'Submit');
        $form->addRule('z', 'This is required', 'required');
        $form->setDefaults(array("x"=>date('Y-m-d')));
        $form->setDefaults(array("y"=>date('Y-m-d')));
        // Get default ERP for connected user
        $location = new tldLocation($user->getBUID());
        $form->setDefaults(array("z"=>$location->getERP()));
        if($form->validate()){
            $i=0;
            $vars = tldUtils::cleanupFormInput($form->exportValues());
            $vars['from']=implode('-',$vars['x']);
            $vars['to']=implode('-',$vars['y']);
            $query="select sn, S.model, airport_code, t_prno, sor_lines.inco, ";
            $query.="(select erp from locations where location=S.man_location and role='ERP') as comp, ";
            $query.="(select min(ctry_code_2) from airport_codes where airport_code=S.airport_code) as ctry_code, ";
            $query.="(select min(dt_shipped) from esrl where erid=S.id and esrl.parent_id=S.esrid) as dt_shipped ";
            $query.="from service as S ";
            $query.="LEFT JOIN sor_units ON sor_units.id=S.sor_uid ";
            $query.="LEFT JOIN sor_lines ON sor_lines.id=sor_units.parent_id ";
            $query.="where ";
            $query.="(select min(dt_shipped) from esrl where erid=S.id and esrl.parent_id=S.esrid)>='".$vars['from']."' and ";
            $query.="(select min(dt_shipped) from esrl where erid=S.id and esrl.parent_id=S.esrid)<='".$vars['to']."' and ";
            $query.="(select erp from locations where location=S.man_location and role='ERP')=".$vars['z'];
            $rows = tldUtils::getSqlToAssocArray($query);
            $queryItems="select * from inwd_items where comp={$vars['z']} AND inward_proc='YES'";
            $rowsItems = tldUtils::getSqlToAssocArray($queryItems);
            foreach ($rows as $key1 => $reportRow) {
                $queryEML="select t_pdno, t_pono, t_sitm, t_qucs, t_opno, (select t_dsca from ttiitm001{$vars['z']} where t_item=CST001.t_sitm) as t_dsca, (select t_copr from ttiitm001{$vars['z']} where t_item=CST001.t_sitm) as t_copr from tticst001{$vars['z']} CST001 where t_cprj=".$rows[$key1]['t_prno'];
                $rowsEML = tldUtils::getSqlToAssocArray($queryEML, "odbc", array("src"=>"baan"));
                foreach ($rowsEML as $key2 => $value2) {
                    $rowsEML[$key2]['t_sitm']=trim($rowsEML[$key2]['t_sitm']);
                    foreach ($rowsItems as $key3 => $value3) {
                        if ($rowsEML[$key2]['t_sitm']==$rowsItems[$key3]['t_item']) {
                            // Get status
                            $query="select w_date, w_user from inwd_shippings where ";
                            $query.="sn='".$rows[$key1]['sn']."' and ";
                            $query.="t_orno=".$rowsEML[$key2]['t_pdno']." and ";
                            $query.="t_pono=".$rowsEML[$key2]['t_pono'];
                            $rowsSelected = tldUtils::getSqlToAssocArray($query);
                            if ($vars['s'] === 'All' || ($vars['s'] === 'Free' && !isset($rowsSelected[0]['w_date'])) || ($vars['s'] === 'Selected' && isset($rowsSelected[0]['w_date']))   ) {
                                if(!isset($rowsSelected[0]['w_date'])) { $rowsSelected[0]['w_date']='No'; }
                                $resuTmp.=$rowsEML[$key2]['t_sitm']."-".$rowsEML[$key2]['t_qucs']."<br>";
                                $rowsResu[$i]['comp']=$rows[$key1]['comp'];
                                $rowsResu[$i]['sn']=$rows[$key1]['sn'];
                                $rowsResu[$i]['model']=$rows[$key1]['model'];
                                $rowsResu[$i]['inco']=$rows[$key1]['inco'];
                                $rowsResu[$i]['airport_code']=$rows[$key1]['airport_code'];
                                $rowsResu[$i]['ctry_code']=$rows[$key1]['ctry_code'];
                                $rowsResu[$i]['dt_shipped']=$rows[$key1]['dt_shipped'];
                                $rowsResu[$i]['t_prno']=$rows[$key1]['t_prno'];
                                $rowsResu[$i]['t_pdno']=$rowsEML[$key2]['t_pdno'];
                                $rowsResu[$i]['t_pono']=$rowsEML[$key2]['t_pono'];
                                $rowsResu[$i]['t_opno']=$rowsEML[$key2]['t_opno'];
                                $rowsResu[$i]['t_sitm']=$rowsEML[$key2]['t_sitm'];
                                $rowsResu[$i]['t_copr']=$rowsEML[$key2]['t_copr'];
                                $rowsResu[$i]['t_dsca']=$rowsEML[$key2]['t_dsca'];
                                $rowsResu[$i]['t_qucs']=$rowsEML[$key2]['t_qucs'];
//                                $selectedLink="<a href='/en/private/manufacturing/index.php?m[0]=reports&m[1]=listing&m[2]=InwardP_Select_Shipped";
//                                $selectedLink.="&comp=".$rows[$key1]['comp'];
//                                $selectedLink.="&sn=".$rows[$key1]['sn'];
//                                $selectedLink.="&t_pdno=".$rowsEML[$key2]['t_pdno'];
//                                $selectedLink.="&t_pono=".$rowsEML[$key2]['t_pono'];
//                                $selectedLink.="'>".$rowsSelected[0]['w_date']."</a>";
//                                $rowsResu[$i]['selected']=$selectedLink;
                                $i+=1;
                            }
                        }
                    }
                }
            }
            $rows=$rowsResu;
            $caption = "Inward Processing: Shipped items for company {$vars['z']}";
        }
        $body = $form->toHTML();
	break;


	case 'InwardP_Select_Shipped':
	    if(!$user->isInGroup(array("role_INWD"))){
	        $body= "<font color=red><b>You do not have permissions for this page...</b></font>";
	        break;
	    }
	    $rows[0]['comp']=$_GET['comp'];
	    $rows[0]['sn']=$_GET['sn'];
	    $rows[0]['t_pdno']=$_GET['t_pdno'];
	    $rows[0]['t_pono']=$_GET['t_pono'];
	    $queryEML="select t_sitm, t_qucs, t_opno, ";
	    $queryEML.="(select t_dsca from ttiitm001{$rows[0]['comp']} where t_item=CST001.t_sitm) as t_dsca, ";
	    $queryEML.="(select t_cprj from ttisfc001{$rows[0]['comp']} where t_pdno=".$rows[0]['t_pdno'].") as t_cprj ";
	    $queryEML.="from tticst001{$rows[0]['comp']} CST001 where t_pdno=".$rows[0]['t_pdno']." and t_pono=".$rows[0]['t_pono'];
	    $rowsEML = tldUtils::getSqlToAssocArray($queryEML, "odbc", array("src"=>"baan"));
	    $rows[0]['t_sitm']=$rowsEML[0]['t_sitm'];
	    $rows[0]['t_qucs']=$rowsEML[0]['t_qucs'];
	    $rows[0]['t_opno']=$rowsEML[0]['t_opno'];
	    $rows[0]['t_dsca']=$rowsEML[0]['t_dsca'];
	    $rows[0]['t_prno']=$rowsEML[0]['t_cprj'];


	    $querySN="select model, airport_code, ";
	    $querySN.="(select min(ctry_code_2) from airport_codes where airport_code=S.airport_code) as ctry_code, ";
	    $querySN.="(select min(dt_shipped) from esrl where erid=S.id and esrl.parent_id=S.esrid) as dt_shipped ";
	    $querySN.="from service as S ";
	    $querySN.="where sn='".$rows[0]['sn']."'";
	    $rowsSN = tldUtils::getSqlToAssocArray($querySN);
	    $rows[0]['model']=$rowsSN[0]['model'];
	    $rows[0]['airport_code']=$rowsSN[0]['airport_code'];
	    $rows[0]['ctry_code']=$rowsSN[0]['ctry_code'];
	    $rows[0]['dt_shipped']=$rowsSN[0]['dt_shipped'];
	    $xItems = array(
	        "comp"        =>"Company",
	        "sn"          =>"ER#",
	        "model"       =>"Model",
	        "airport_code"=>"Final Destination",
	        "ctry_code"   =>"Country",
	        "dt_shipped"  =>"Shipped date",
	        "t_prno"      =>"Manufacturing project",
	        "t_pdno"      =>"Production Order",
	        "t_pono"      =>"Position",
	        "t_opno"      =>"Operation",
	        "t_sitm"      =>"Item",
	        "t_dsca"      =>"Description",
	        "t_qucs"      =>"Quantity"
	    );
	    $query="select w_date, w_user from inwd_shippings where ";
	    $query.="sn='".$rows[0]['sn']."' and ";
	    $query.="t_orno=".$rows[0]['t_pdno']." and ";
	    $query.="t_pono=".$rows[0]['t_pono'];
	    $rowsSelected = tldUtils::getSqlToAssocArray($query);
	    if(isset($rowsSelected[0]['w_date'])) {
	        $query="delete from inwd_shippings where ";
	        $query.="sn='".$rows[0]['sn']."' and ";
	        $query.="t_orno=".$rows[0]['t_pdno']." and ";
	        $query.="t_pono=".$rows[0]['t_pono'];
	        $rowsTmp = tldUtils::sqlExecute($query);
	        $form = new HTML_QuickForm('frmInwardP_Select_Shipped', 'get', "", "", "", true);
	        $caption = "Shipping un-selected";
	    } else {
	        $query="insert into inwd_shippings (id, comp, sn, t_orno, t_pono, t_item, t_qucs, w_date, w_user) values (";
	        $query.="null, ";
	        $query.=$rows[0]['comp'].", ";
	        $query.="'".$rows[0]['sn']."', ";
	        $query.=$rows[0]['t_pdno'].", ";
	        $query.=$rows[0]['t_pono'].", ";
	        $query.="'".$rows[0]['t_item']."', ";
	        $query.=$rows[0]['t_qucs'].", ";
	        $query.="now(), ";
	        $query.=$user->getID();
	        $query.=") ";
	        $rowsTmp = tldUtils::sqlInsert($query);
	        $form = new HTML_QuickForm('frmInwardP_Select_Shipped', 'get', "", "", "", true);
	        $caption = "Shipping selected";
	    }
	    $body = $form->toHTML();
    break;


    case 'InwardP_List_Receipts':
        if(!$user->isInGroup(array("role_INWD"))){
            $body= "<font color=red><b>You do not have permissions for this page...</b></font>";
            break;
        }
        $xItems = array(
        "comp"  =>"Company",
        "t_item"=>"Item",
        "t_dsca"=>"Description",
        "t_copr"=>"Std cost price",
        "t_orno"=>"PO#",
        "t_pono"=>"Position",
        "t_srnb"=>"Sequence",
        "t_suno"=>"Supplier",
        "t_ccty"=>"Country",
        "t_date"=>"Receipt date",
        "t_reno"=>"Receipt number",
        "t_dino"=>"Packing slip",
        "t_dqua"=>"Quantity",
//        "selected"=>"Selected"
            );
        // Get listing
        $erpList = tldLocation::getERPList("smartyOptions");
        $status = array("All", "Free", "Selected");
        // Get form
        $form = new HTML_QuickForm('frmInwardP_List_Receipts', 'get', "", "", "", true);
        $form->addElement(	'hidden', 'm[0]', 'reports');
        $form->addElement(	'hidden', 'm[1]', 'listing');
        $form->addElement(	'hidden', 'm[2]', 'InwardP_List_Receipts');
        $form->addElement(	'header', 'title','Select company:');
        $form->addElement(	'date', 	'x',	'From',
            array("format"=>"Y-m-d","minYear"=>date('Y')-2,"maxYear"=>date('Y')+2));
        $form->addElement(	'date', 	'y',	'To',
            array("format"=>"Y-m-d","minYear"=>date('Y')-2,"maxYear"=>date('Y')+2));
        $form->addElement(	'select', 	's',	'Status',	array_combine($status, $status));
        $form->addElement(	'select', 	'z',	'Company#',	array(""=>"")+$erpList);
        $form->addElement(	'submit', 'btnSubmit', 'Submit');
        $form->addRule('z', 'This is required', 'required');
        $form->setDefaults(array("x"=>date('Y-m-d')));
        $form->setDefaults(array("y"=>date('Y-m-d')));

        // Get default ERP for connected user
        $location = new tldLocation($user->getBUID());
        $form->setDefaults(array("z"=>$location->getERP()));
        if($form->validate()){
            $vars = tldUtils::cleanupFormInput($form->exportValues());
            $vars['from']=implode('-',$vars['x']);
            $vars['to']=implode('-',$vars['y']);
            $query="select * from inwd_items where comp={$vars['z']} AND inward_proc='YES'";
            $i=0;
            $rows = tldUtils::getSqlToAssocArray($query);
            foreach ($rows as $key1 => $reportRow) {
                // Get item description & cost price
                $query="select t_dsca, t_copr from ttiitm001{$vars['z']} where t_item='".$rows[$key1]['t_item']."'";
                $rowsDsca = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
                $rows[$key1]['t_dsca']=$rowsDsca[0]['t_dsca'];
                $rows[$key1]['t_copr']=$rowsDsca[0]['t_copr'];
                // Get receipts
                $query="select PUR045.t_orno, PUR045.t_pono, PUR045.t_srnb, PUR045.t_suno, cast(PUR045.t_date as DATE) as t_date, PUR045.t_reno, PUR045.t_dino, PUR045.t_dqua, ";
                $query.="(select t_ccty from ttccom020{$vars['z']} where t_suno=PUR045.t_suno) as t_ccty ";
                $query.="from ttdpur045{$vars['z']} PUR045 where 1=1 ";
                $query.="and PUR045.t_item='".$rows[$key1]['t_item']."' ";
                $query.="and PUR045.t_date>='".$vars['from']."' ";
                $query.="and PUR045.t_date<='".$vars['to']."' ";
                $rowsReceipt = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
                foreach ($rowsReceipt as $key2 => $value2) {
                    // Get status
                    $query="select w_date, w_user from inwd_receipts where ";
                    $query.="comp=".$rows[$key1]['comp']." and ";
                    $query.="t_orno=".$rowsReceipt[$key2]['t_orno']." and ";
                    $query.="t_pono=".$rowsReceipt[$key2]['t_pono']." and ";
                    $query.="t_srnb=".$rowsReceipt[$key2]['t_srnb'];
                    $rowsSelected = tldUtils::getSqlToAssocArray($query);
                    if ($vars['s'] === 'All' || ($vars['s'] === 'Free' && !isset($rowsSelected[0]['w_date'])) || ($vars['s'] === 'Selected' && isset($rowsSelected[0]['w_date']))   ) {
                        if(!isset($rowsSelected[0]['w_date'])) { $rowsSelected[0]['w_date']='No'; }
                        $rowsResu[$i]['comp']=$rows[$key1]['comp'];
                        $rowsResu[$i]['t_item']=$rows[$key1]['t_item'];
                        $rowsResu[$i]['t_dsca']=$rows[$key1]['t_dsca'];
                        $rowsResu[$i]['t_copr']=$rows[$key1]['t_copr'];
                        $rowsResu[$i]['t_orno']=$rowsReceipt[$key2]['t_orno'];
                        $rowsResu[$i]['t_pono']=$rowsReceipt[$key2]['t_pono'];
                        $rowsResu[$i]['t_srnb']=$rowsReceipt[$key2]['t_srnb'];
                        $rowsResu[$i]['t_suno']=$rowsReceipt[$key2]['t_suno'];
                        $rowsResu[$i]['t_ccty']=$rowsReceipt[$key2]['t_ccty'];
                        $rowsResu[$i]['t_date']=$rowsReceipt[$key2]['t_date'];
                        $rowsResu[$i]['t_reno']=$rowsReceipt[$key2]['t_reno'];
                        $rowsResu[$i]['t_dino']=$rowsReceipt[$key2]['t_dino'];
                        $rowsResu[$i]['t_dqua']=$rowsReceipt[$key2]['t_dqua'];
//                        $selectedLink="<a href='/en/private/manufacturing/index.php?m[0]=reports&m[1]=listing&m[2]=InwardP_Select_Receipts";
//                        $selectedLink.="&comp=".$rows[$key1]['comp'];
//                        $selectedLink.="&t_orno=".$rowsReceipt[$key2]['t_orno'];
//                        $selectedLink.="&t_pono=".$rowsReceipt[$key2]['t_pono'];
//                        $selectedLink.="&t_srnb=".$rowsReceipt[$key2]['t_srnb'];
//                        $selectedLink.="'>".$rowsSelected[0]['w_date']."</a>";
//                        $rowsResu[$i]['selected']=$selectedLink;
                        $i+=1;
                    }
                }
            }
            $rows=$rowsResu;
            $caption = "Inward Processing: Received items for company {$vars['z']}";
        }
        $body = $form->toHTML();
    break;


    case 'InwardP_Select_Receipts':
        if(!$user->isInGroup(array("role_INWD"))){
            $body= "<font color=red><b>You do not have permissions for this page...</b></font>";
            break;
        }
        $rows[0]['comp']=$_GET['comp'];
        $rows[0]['t_orno']=$_GET['t_orno'];
        $rows[0]['t_pono']=$_GET['t_pono'];
        $rows[0]['t_srnb']=$_GET['t_srnb'];
        $query="select  PUR045.t_item, PUR045.t_suno, cast(PUR045.t_date as DATE) as t_date, PUR045.t_reno, PUR045.t_dino, PUR045.t_dqua, ";
        $query.="(select t_ccty from ttccom020".$rows[0]['comp']." where t_suno=PUR045.t_suno) as t_ccty, ";
        $query.="(select t_dsca from ttiitm001".$rows[0]['comp']." where t_item=PUR045.t_item) as t_dsca ";
        $query.="from ttdpur045".$rows[0]['comp']." PUR045 where 1=1 ";
        $query.="and PUR045.t_orno='".$rows[0]['t_orno']."' ";
        $query.="and PUR045.t_pono='".$rows[0]['t_pono']."' ";
        $query.="and PUR045.t_srnb='".$rows[0]['t_srnb']."' ";
        $rowsReceipt = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
        $rows[0]['t_item']=$rowsReceipt[0]['t_item'];
        $rows[0]['t_dsca']=$rowsReceipt[0]['t_dsca'];
        $rows[0]['t_suno']=$rowsReceipt[0]['t_suno'];
        $rows[0]['t_ccty']=$rowsReceipt[0]['t_ccty'];
        $rows[0]['t_date']=$rowsReceipt[0]['t_date'];
        $rows[0]['t_reno']=$rowsReceipt[0]['t_reno'];
        $rows[0]['t_dino']=$rowsReceipt[0]['t_dino'];
        $rows[0]['t_dqua']=$rowsReceipt[0]['t_dqua'];

        $xItems = array(
            "comp"  =>"Company",
            "t_item"=>"Item",
            "t_dsca"=>"Description",
            "t_orno"=>"PO#",
            "t_pono"=>"Position",
            "t_srnb"=>"Sequence",
            "t_suno"=>"Supplier",
            "t_ccty"=>"Country",
            "t_date"=>"Receipt date",
            "t_reno"=>"Receipt number",
            "t_dino"=>"Packing slip",
            "t_dqua"=>"Quantity"
        );
        $query="select w_date, w_user from inwd_receipts where ";
        $query.="comp=".$rows[0]['comp']." and ";
        $query.="t_orno=".$rows[0]['t_orno']." and ";
        $query.="t_pono=".$rows[0]['t_pono']." and ";
        $query.="t_srnb=".$rows[0]['t_srnb'];
        $rowsSelected = tldUtils::getSqlToAssocArray($query);
        if(isset($rowsSelected[0]['w_date'])) {
            $query="delete from inwd_receipts where ";
            $query.="comp=".$rows[0]['comp']." and ";
            $query.="t_orno=".$rows[0]['t_orno']." and ";
            $query.="t_pono=".$rows[0]['t_pono']." and ";
            $query.="t_srnb=".$rows[0]['t_srnb'];
            $rowsTmp = tldUtils::sqlExecute($query);

            $form = new HTML_QuickForm('frmInwardP_Select_Receipts', 'get', "", "", "", true);
            $caption = "Receipt un-selected";

        } else {
            $query="insert into inwd_receipts (id, comp, t_orno, t_pono, t_srnb, w_date, w_user) values (";
            $query.="null, ";
            $query.=$rows[0]['comp'].", ";
            $query.=$rows[0]['t_orno'].", ";
            $query.=$rows[0]['t_pono'].", ";
            $query.=$rows[0]['t_srnb'].", ";
            $query.="now(), ";
            $query.=$user->getID();
            $query.=") ";
            $rowsTmp = tldUtils::sqlInsert($query);

            $form = new HTML_QuickForm('frmInwardP_Select_Receipts', 'get', "", "", "", true);
            $caption = "Receipt selected";

        }
        $body = $form->toHTML();
    break;


    case 'InwardP_List_Items':
        if(!$user->isInGroup(array("role_INWD"))){
            $body= "<font color=red><b>You do not have permissions for this page...</b></font>";
            break;
        }
        $xItems = array(
        "comp"  =>"Company",
        "t_item"=>"Item",
        "t_dsca"=>"Description"
            );
        // Get listing
        $erpList = tldLocation::getERPList("smartyOptions");
        // Get form
        $form = new HTML_QuickForm('frmInwardP_List_Items', 'get', "", "", "", true);
        $form->addElement(	'hidden', 'm[0]', 'reports');
        $form->addElement(	'hidden', 'm[1]', 'listing');
        $form->addElement(	'hidden', 'm[2]', 'InwardP_List_Items');
        $form->addElement(	'header', 'title','Select company:');
        $form->addElement(	'select', 	'z',	'Company#',	array(""=>"")+$erpList);
        $form->addElement(	'submit', 'btnSubmit', 'Submit');
        $form->addRule('z', 'This is required', 'required');

        // Get default ERP for connected user
        $location = new tldLocation($user->getBUID());
        $form->setDefaults(array("z"=>$location->getERP()));
        if($form->validate()){
            $vars = tldUtils::cleanupFormInput($form->exportValues());
            $query="select * from inwd_items where comp={$vars['z']} AND inward_proc='YES'";
            $rows = tldUtils::getSqlToAssocArray($query);
            foreach ($rows as $key1 => $reportRow) {
                $query="select t_dsca from ttiitm001{$vars['z']} where t_item='".$rows[$key1]['t_item']."'";
                $rowsDsca = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
                $rows[$key1]['t_dsca']=$rowsDsca[0]['t_dsca'];
            }
            $caption = "Inward Processing: Followed items for company {$vars['z']}";
        }
        $body = $form->toHTML();
    break;

case 'Receipts_Report':
	$xItems = array(
			"t_orno"=>"Purchase Order",
			"t_pono"=>"Position",
			"t_srnb"=>"Sequence",
			"t_suno"=>"Supplier",
			"t_nama"=>"Name",
			"t_item"=>"Item",
			"t_dsca"=>"Description",
			"t_date"=>"Receipt Date",
			"t_reno"=>"Receipt Number",
			"t_dino"=>"Packing Slip",
			"t_diqu"=>"Packing Slip Qty",
			"t_dqua"=>"Delivered Qty",
			"t_bqua"=>"Back-Order Qty"
		);
		// Get listing
		$erpList = tldLocation::getERPList("smartyOptions");
		$output = array("PDF", "Screen");

		// Get form
		$form = new HTML_QuickForm('frmReceipts_Report', 'get', "", "", "", true);
		$form->addElement(	'hidden', 'm[0]', 'reports');
		$form->addElement(	'hidden', 'm[1]', 'listing');
		$form->addElement(	'hidden', 'm[2]', 'Receipts_Report');
		$form->addElement(	'header', 'title','Select company:');
		$form->addElement(	'text', 'frm_po', 'PO number:');
		$form->addElement(	'text', 'frm_ps', 'Packing slip:');
		$form->addElement(	'text', 'frm_rn', 'Receipt number:');
		$form->addElement(	'select', 	'z',	'Company#',	array(""=>"")+$erpList);
		$form->addElement(	'select', 'Ou', 'Output', array_combine($output, $output));
		$form->addElement(	'submit', 'btnSubmit', 'Submit');
		$form->addRule('z', 'This is required', 'required');
		$form->setDefaults(array("sup_from"=>"0"));
		$form->setDefaults(array("sup_to"=>"999999"));

		// Get default ERP for connected user
		$location = new tldLocation($user->getBUID());
		$form->setDefaults(array("z"=>$location->getERP()));

		// Select erp 500 if user is linked to fake ERP 510
		$erpReal = $location->getERP();
		if ($erpReal=='510') {
			$form->setDefaults(array("z"=>"500"));
		}

		if($form->validate()){
			$vars = tldUtils::cleanupFormInput($form->exportValues());

			$dateTmp1 = new DateTime(date('Y-m-d'));
			$dateTmp2 =	$dateTmp1->format('Y-m-d');

			// list tablets from inventory
			$query="select top 500 ";
			$query.="PUR045.t_orno, ";
			$query.="PUR045.t_pono, ";
			$query.="PUR045.t_srnb, ";
			$query.="PUR045.t_suno, ";
			$query.="(select COM020.t_nama from ttccom020{$vars['z']} COM020 where COM020.t_suno = PUR045.t_suno) as t_nama, ";
			$query.="PUR045.t_item, ";
			$query.="(select ITM001.t_dsca from ttiitm001{$vars['z']} ITM001 where ITM001.t_item = PUR045.t_item) as t_dsca, ";
			$query.="SUBSTRING(convert(varchar, PUR045.t_date, 120), 0, 11) AS t_date, ";
			$query.="PUR045.t_reno, ";
			$query.="PUR045.t_dino, ";
			$query.="PUR045.t_diqu, ";
			$query.="PUR045.t_dqua, ";
			$query.="PUR045.t_bqua ";
			$query.="from ttdpur045{$vars['z']} PUR045 where t_srnb>0 ";

			if($vars['frm_po']!='') { $query.=" and PUR045.t_orno={$vars['frm_po']}" ; }
			if($vars['frm_ps']!='') { $query.=" and PUR045.t_dino='{$vars['frm_ps']}' "; }
			if($vars['frm_rn']!='') { $query.=" and PUR045.t_reno={$vars['frm_rn']}" ; }



			$rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);

            $caption = "Receipts Report by PO / Packing slip / Receipt Number for company {$vars['z']}";

            if ($vars['Ou'] === 'PDF') {
				$report = new tldReportColumnar($rows,
					array(
						"xItems"=>$xItems,
						"title"=>$caption,
						"links"=>$links
					)
				);
				$html = $report->fetch();

				$pdf = new tldHTML2PDF($html,array('encoding'=>'utf-8', 'margin'=>'10'));
				$pdf->outFile("aaa.pdf");
				exit;
            }
		}//else{
			$body = $form->toHTML();
		//}
	break;

	case 'Manuf_Short_Report_Add_Comment':
	case 'Manuf_Short_Report_Update':
    case 'Manuf_Short_Report':
        $body = "This page has been migrated and should not be displayed anymore";
        break;


	case 'Receipt_Approvals_todo':
		$xItems = array(
			"t_orno"=>"Order",
			"t_pono"=>"Position",
			"t_srnb"=>"Sequence",
			"t_spur"=>"PO line status",
			"t_spu2"=>"Description",
			"t_rev1"=>"PO revision",
			"t_rev2"=>"Item revision",
			"t_suno"=>"Supplier",
			"t_nama"=>"Name",
			"t_item"=>"Item",
			"t_dsca"=>"Description",
			"t_ddtb"=>"Planned Delivery Date",
			"t_oqua"=>"Ordered Qty",
			"t_reno"=>"Receipt Number",
			"t_dino"=>"Packing Slip Number",
			"t_date"=>"Receipt Date",
			"t_dqua"=>"Delivered Qty",
			"t_bqua"=>"Back order Qty",
			"t_quap"=>"Approved Qty",
			"t_quad"=>"Rejected Qty",
			"t_cdis"=>"Reason for Rejection",
			"t_cwar"=>"Default warehouse",
			"t_loca"=>"Default location",
			"t_requ"=>"Requirement (Order # Qty # Requirement Date # Status)",
			//"t_inbd"=>"Inbounded?",
			"t_acti"=>"Action",
			"t_txta"=>"PO line text",
		);

		// Get listing
		$erpList = tldLocation::getERPList("smartyOptions");
		$receiptStatus = array("Not Received", "Received not Approved", "Approved");
		// Get form
		$form = new HTML_QuickForm('frmReceipt_Approvals_todo', 'get', "", "", "", true);
		$form->addElement(	'hidden', 'm[0]', 'reports');
		$form->addElement(	'hidden', 'm[1]', 'listing');
		$form->addElement(	'hidden', 'm[2]', 'Receipt_Approvals_todo');
		$form->addElement(	'header', 'title','Select status and company:');
		$form->addElement(	'select', 'Rs', 'Receipt status', array_combine($receiptStatus, $receiptStatus));
		$form->addElement(	'select', 	'z',	'Company#',
			array(""=>"")+$erpList);
		$form->addElement(	'submit', 'btnSubmit', 'Submit');
		$form->addRule('z', 'This is required', 'required');

		if($form->validate()){
			$vars = tldUtils::cleanupFormInput($form->exportValues());

			$reviDate = new DateTime(date('Y-m-d'));
			$reviDate2 = $reviDate->format('Y-m-d');

			$query="select ";
			$query.="PUR045.t_orno, ";
			$query.="PUR045.t_pono, ";
			$query.="PUR045.t_srnb, ";
			$query.="PUR045.t_suno, ";
			$query.="(select COM020.t_nama from ttccom020{$vars['z']} COM020 where COM020.t_suno=PUR045.t_suno) t_nama, ";
			$query.="PUR045.t_item, ";
			$query.="(select ITM001.t_dsca from ttiitm001{$vars['z']} ITM001 where ITM001.t_item=PUR045.t_item) t_dsca, ";
			$query.="(select PUR041.t_revi from ttdpur041{$vars['z']} PUR041 where PUR041.t_orno=PUR045.t_orno and PUR041.t_pono=PUR045.t_pono) t_rev1, ";
			$query.="(select CONVERT(DATE, PUR041.t_ddtb) from ttdpur041{$vars['z']} PUR041 where PUR041.t_orno=PUR045.t_orno and PUR041.t_pono=PUR045.t_pono) t_ddtb, ";
			$query.="(select PUR041.t_oqua from ttdpur041{$vars['z']} PUR041 where PUR041.t_orno=PUR045.t_orno and PUR041.t_pono=PUR045.t_pono) t_oqua, ";
			$query.="(select max(EDM100.t_revi) from ttiedm100400 EDM100 where EDM100.t_eitm=PUR045.t_item and EDM100.t_indt<='".$reviDate2."' and (EDM100.t_exdt>='".$reviDate2."' or EDM100.t_exdt='01/01/1753')) t_rev2, ";
			$query.="(select ITM001.t_cwar from ttiitm001{$vars['z']} ITM001 where ITM001.t_item=PUR045.t_item) t_cwar, ";
			$query.="   (select min(ILC007.t_loca) from ttdilc007{$vars['z']} ILC007 where ";
			$query.="      ILC007.t_item=PUR045.t_item and ";
			$query.="      ILC007.t_cwar=(select ITM001.t_cwar from ttiitm001{$vars['z']} ITM001 where ITM001.t_item=PUR045.t_item) and ";
			$query.="      ILC007.t_prio=";
			$query.="         (select min(ILC007.t_prio) from ttdilc007{$vars['z']} ILC007 where ";
			$query.="            ILC007.t_item=PUR045.t_item and ";
			$query.="            ILC007.t_cwar=(select ITM001.t_cwar from ttiitm001{$vars['z']} ITM001 where ITM001.t_item=PUR045.t_item) ";
			$query.="   )) t_loca, ";
			$query.="PUR045.t_reno, ";
			$query.="PUR045.t_dino, ";
			$query.="SUBSTRING(convert(varchar, t_date, 120), 0, 11) AS t_date, ";
			$query.="PUR045.t_dqua, ";
			$query.="PUR045.t_bqua, ";
			$query.="PUR045.t_quap, ";
			$query.="PUR045.t_quad, ";
			$query.="PUR045.t_cdis, ";
			$query.="PUR045.t_spur, ";

			$query.="(CASE ";
			$query.="   WHEN PUR045.t_spur=1 THEN 'Print Purchase Orders' ";
			$query.="   WHEN PUR045.t_spur=2 THEN 'Print Goods Received Notes' ";
			$query.="   WHEN PUR045.t_spur=3 THEN 'Maintain Receipts' ";
			$query.="   WHEN PUR045.t_spur=4 THEN 'Print Claims' ";
			$query.="   WHEN PUR045.t_spur=5 THEN 'Maintain Approvals' ";
			$query.="   WHEN PUR045.t_spur=6 THEN 'Print Storage Lists' ";
			$query.="   WHEN PUR045.t_spur=7 THEN 'Print Return Notes' ";
			$query.="   WHEN PUR045.t_spur=8 THEN 'Print Purchase Invoices' ";
			$query.="   WHEN PUR045.t_spur=9 THEN 'Process Delivered Purchase Order' ELSE 'N/A' END ";
			$query.=") as t_spu2, ";

			$query.="(CASE ";
			$query.="   WHEN (select count(*) from ttdilc111{$vars['z']} ILC111 where ILC111.t_orno=PUR045.t_orno and ILC111.t_pono=PUR045.t_pono)>0 THEN 'No' ELSE 'Yes' ";
			$query.="END) as t_inbd, ";

			$query.="(select CAST(min(t_text) AS varchar(240)) from ttttxt010{$vars['z']} where t_ctxt=(select min(t_txta) from ttdpur041{$vars['z']} PUR041 where PUR041.t_orno=PUR045.t_orno and PUR041.t_pono=PUR045.t_pono)) t_txta ";

			$query.="from ttdpur045{$vars['z']} PUR045 where ";

		    if ($vars['Rs'] === "Not Received") {
		    	$query.="PUR045.t_spur>=1 and PUR045.t_spur<=3 and ";
		    	$query.="( ";
		    	$query.="   (PUR045.t_srnb=0 and (select max(PUR045b.t_srnb) t_srnb from ttdpur045{$vars['z']} PUR045b where PUR045b.t_orno=PUR045.t_orno and PUR045b.t_pono=PUR045.t_pono)=0) or ";
		    	$query.="   (PUR045.t_srnb>0 and (select max(PUR045b.t_srnb) t_srnb from ttdpur045{$vars['z']} PUR045b where PUR045b.t_orno=PUR045.t_orno and PUR045b.t_pono=PUR045.t_pono)>0) ";
		    	$query.=") and ";
		    }

		    if ($vars['Rs'] === "Received not Approved") 	{$query.="PUR045.t_spur>=4 and PUR045.t_spur<=5 and PUR045.t_srnb<>0 and ";	}
		    if ($vars['Rs'] === "Approved") 				{$query.="PUR045.t_spur>=6 and PUR045.t_spur<9 and PUR045.t_srnb<>0 and ";	}

			$query.="(select count(*) from ttdpur041{$vars['z']} PUR041 where PUR041.t_orno=PUR045.t_orno and PUR041.t_pono=PUR045.t_pono and PUR041.t_qual=1)>0 ";
			$rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);


			// Add requirement
			foreach ($rows as $key1 => $reportRow) {
				$tmp_item=$rows[$key1][t_item];
				$query2="select INV150.t_orno, INV150.t_pono, INV150.t_ponb, INV150.t_date, INV150.t_koor, INV150.t_qana from ttdinv150{$vars['z']} INV150 where INV150.t_kotr=2 and INV150.t_koor in(1,3) and  INV150.t_item='" . $tmp_item . "'";
				$rows2 = tldUtils::getSqlToAssocArray($query2, "odbc", array("src"=>"baan"));
				$requirement="";
				foreach ($rows2 as $key2 => $value2) {
					// get production order status
					if ($rows2[$key2]['t_koor']==1) {
						$query3="select CASE ";
						$query3.="   WHEN SFC001.t_osta=1 THEN 'Free' ";
						$query3.="   WHEN SFC001.t_osta=2 THEN 'Planned' ";
						$query3.="   WHEN SFC001.t_osta=3 THEN 'Printed' ";
						$query3.="   WHEN SFC001.t_osta=4 THEN 'Released' ";
						$query3.="   WHEN SFC001.t_osta=5 THEN 'Active' ";
						$query3.="   WHEN SFC001.t_osta=6 THEN 'Completed' ELSE 'N/A' ";
						$query3.="END as PDNO_STATUS ";
						$query3.="from ttisfc001{$vars['z']} SFC001 where SFC001.t_pdno='".$rows2[$key2]['t_orno']."' ";
						$rows3 = tldUtils::getSqlRowToAssocArray($query3, 'odbc', ['src' => 'baan']);
					};

					// get sales order status
					if ($rows2[$key2]['t_koor']==3) {
						$query4="select CASE ";
						$query4.="   WHEN min(SLS045.t_ssls)=1 THEN 'Print Order Acknowledgements' ";
						$query4.="   WHEN min(SLS045.t_ssls)=2 THEN 'Print Picking Lists' ";
						$query4.="   WHEN min(SLS045.t_ssls)=3 THEN 'Maintain Deliveries' ";
						$query4.="   WHEN min(SLS045.t_ssls)=4 THEN 'Print Packing Slips' ";
						$query4.="   WHEN min(SLS045.t_ssls)=5 THEN 'Print Bills of Lading' ";
						$query4.="   WHEN min(SLS045.t_ssls)=6 THEN 'Print Sales Invoices' ";
						$query4.="   WHEN min(SLS045.t_ssls)=7 THEN 'Process Delivered Sales Order' ";
						$query4.="   WHEN min(SLS045.t_ssls)=8 THEN 'Generate Outbound Advice' ";
						$query4.="   WHEN min(SLS045.t_ssls)=9 THEN 'Print Proforma Invoices' ";
						$query4.="   WHEN min(SLS045.t_ssls)=10 THEN 'Link Advance Receipts to Pro' ELSE 'N/A' ";
						$query4.="END as SLNO_STATUS ";
						$query4.="from ttdsls045{$vars['z']} SLS045 where SLS045.t_orno='".$rows2[$key2]['t_orno']."' and SLS045.t_pono='".$rows2[$key2]['t_pono']."' " ;
						$rows4 = tldUtils::getSqlRowToAssocArray($query4, 'odbc', ['src' => 'baan']);
					};

					$date_tmp=substr($rows2[$key2]['t_date'],0,10);
					if ($rows2[$key2]['t_koor']==1) { $requirement.="<table border=0><tr><td nowrap>".$rows2[$key2]['t_orno']."(WO) # ".$rows2[$key2]['t_qana']." # ".$date_tmp." # ".$rows3['PDNO_STATUS']."</td></tr></table>"; }
					if ($rows2[$key2]['t_koor']==3) { $requirement.="<table border=0><tr><td nowrap>".$rows2[$key2]['t_orno']."(SO) # ".$rows2[$key2]['t_qana']." # ".$date_tmp." # ".$rows4['SLNO_STATUS']."</td></tr></table>"; }
				}
					$rows[$key1]['t_requ']=$requirement;
					if ($rows[$key1]['t_spur']==5) { $rows[$key1]['t_acti']="TO INSPECT"; }
					if ($rows[$key1]['t_spur']>5) { $rows[$key1]['t_acti']="INSPECTED"; }
			}
            $caption = "Reicept Approvals to do for company {$vars['z']}";
		}//else{
			$body = $form->toHTML();
		//}
	break;
	
	case 'PO_Monitoring':
		$xItems = [
            't_pdno' => 'Production order',
            't_mitm' => 'Manufactured item',
            't_cprj' => 'Project',
            't_dsca' => 'Description',
            't_pono' => 'Position',
            't_sitm' => 'Item',
            't_pics' => 'Floor stock',
            't_dscb' => 'Description',
            't_opno' => 'Operation/Box',
            't_cwar' => 'Warehouse',
            't_stoc' => 'Inventory on hand',
            't_ques' => 'Estimated quantity',
            't_qucs' => 'Actual quantity',
            'delta' => 'stimated-Actual qty',
            't_cpcs' => 'Actual cost price',
            't_issu' => 'Issue',
            't_subd' => 'Subsequent delivery',
            't_loca' => 'Location',
            't_buyr' => 'Buyer',
            't_nama' => 'Name',
            't_suno' => 'Supplier',
            't_namb' => 'Name',
            't_oltm' => 'lead Time',
		];

		// Get listing
		$erpList = tldLocation::getERPList("smartyOptions");

		// Get form
		$form = new HTML_QuickForm('frmPO_Monitoring', 'get', "", "", "", true);
		$form->addElement(	'hidden', 'm[0]', 'reports');
		$form->addElement(	'hidden', 'm[1]', 'listing');
		$form->addElement(	'hidden', 'm[2]', 'PO_Monitoring');
		$form->addElement(	'header', 'title','Select company:');
		$form->addElement(	'text', 'Wo', 'Production order');
		$form->addElement(	'select', 	'z',	'Company#',
			array(""=>"")+$erpList);
		$form->addElement(	'submit', 'btnSubmit', 'Submit');
		$form->addRule('z', 'This is required', 'required');

		// Get default ERP for connected user
		$location = new tldLocation($user->getBUID());
		$form->setDefaults(array("z"=>$location->getERP()));

		if($form->validate()){
			$vars = tldUtils::cleanupFormInput($form->exportValues());

			$query="SELECT ";
			$query.="CST001.t_pdno t_pdno, ";
			$query.="SFC001.t_mitm t_mitm, ";
			$query.="CST001.t_cprj t_cprj, ";
			$query.="PCS021.t_dsca t_dsca, ";
			$query.="CST001.t_pono t_pono, ";
			$query.="CST001.t_sitm t_sitm, ";
			$query.="ITM001.t_pics t_pics, ";
			$query.="ITM001.t_dsca t_dscb, ";
			$query.="ITM001.t_oltm t_oltm, ";
			$query.="CST001.t_opno t_opno, ";
			$query.="CST001.t_cwar t_cwar, ";
			$query.="ITM001.t_stoc t_stoc, ";
			$query.="CST001.t_ques t_ques, ";
			$query.="CST001.t_qucs t_qucs, ";
			$query.="CST001.t_ques - CST001.t_qucs delta, ";
			$query.="convert(varchar(100), cast(CST001.t_cpcs as decimal(15,5))) as t_cpcs, ";
			$query.="CST001.t_issu t_issu, ";
			$query.="CST001.t_subd t_subd, ";
			$query.="(select max(ILC007.t_loca) from ttdilc007{$vars['z']} ILC007 where ILC007.t_item = CST001.t_sitm) t_loca, ";
			$query.="ITM001.t_buyr t_buyr, ";
			$query.="(select COM001.t_nama from ttccom001{$vars['z']} COM001 where COM001.t_emno = ITM001.t_buyr) t_nama, ";
			$query.="ITM001.t_suno t_suno, ";
			$query.="(select COM020.t_nama from ttccom020{$vars['z']} COM020 where COM020.t_suno = ITM001.t_suno) t_namb ";
			$query.="from ";
			$query.="tticst001{$vars['z']} CST001, ";
			$query.="ttiitm001{$vars['z']} ITM001, ";
			$query.="ttipcs021{$vars['z']} PCS021, ";
			$query.="ttisfc001{$vars['z']} SFC001 ";
			$query.="where ";
			$query.="CST001.t_pdno = {$vars['Wo']} and ";
			$query.="ITM001.t_item = CST001.t_sitm and ";
			$query.="PCS021.t_cprj = CST001.t_cprj and ";
			$query.="PCS021.t_item = SFC001.t_mitm and ";
			$query.="SFC001.t_pdno = CST001.t_pdno ";

			$rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
            $caption = "Production Orders Monitoring for company {$vars['z']}";
		}//else{
			$body = $form->toHTML();
		//}
	break;


	case 'PO_Routing_Sheet':
		$xItems = array(
			"comp"=>"company",
			"wono"=>"Work order",
			"acti"=>"Activity"
		);

		// Get listing
		$erpList = tldLocation::getERPList("smartyOptions");
		$activity = array("Soudure / Welding", "Elec.", "Meca", "Montage / Assembly", "Peinture / Paint", "Controle / Test / QA", "Mag / Warehouse", "General");
		$output = array("Screen", "PDF");

		// Get form
		$form = new HTML_QuickForm('frmPO_Routing_Sheet', 'get', "", "", "", true);
		$form->addElement(	'hidden', 'm[0]', 'reports');
		$form->addElement(	'hidden', 'm[1]', 'listing');
		$form->addElement(	'hidden', 'm[2]', 'PO_Routing_Sheet');
		$form->addElement(	'header', 'title','Select Production Order and company:');
		$form->addElement(	'text', 'Wo', 'Production order');
		$form->addElement(	'select', 'Ac', 'Activity', array_combine($activity, $activity));
		$form->addElement(	'select', 	'z',	'Company#',
			array(""=>"")+$erpList);
		$form->addElement(	'select', 'Ou', 'Output', array_combine($output, $output));
		$form->addElement(	'submit', 'btnSubmit', 'Submit');
		$form->addRule('z', 'This is required', 'required');

	   	// Get default ERP for connected user
		$location = new tldLocation($user->getBUID());
		$form->setDefaults(array("z"=>$location->getERP()));

		// Select erp 500 if user is linked to fake ERP 510
		$erpReal = $location->getERP();
		if ($erpReal=='510') {
			$erpReal='500';
			$form->setDefaults(array("z"=>"500"));
		}

		if($form->validate()){
			$vars = tldUtils::cleanupFormInput($form->exportValues());

			// Header: tisfc001
			$query="select t_pdno, t_prdt, t_qrdr, t_qdlv, t_mitm, t_cprj, t_cwar from ttisfc001{$vars['z']} where t_pdno = '{$vars['Wo']}'";
			$rowsTmp1 = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);

			if ($rowsTmp1['0']['t_pdno']=="") {
				$html="Not found...";
			} else {

					// Header: item description itm001
					$query="select t_dsca from ttiitm001{$vars['z']} where t_item = '{$rowsTmp1['0']['t_mitm']}'";
					$rowsTmp2 = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);

					// Header: item description pcs021
					$query="select t_dsca from ttipcs021{$vars['z']} where t_item = '{$rowsTmp1['0']['t_mitm']}' and t_cprj = '{$rowsTmp1['0']['t_cprj']}'";
					$rowsTmp3 = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);

					if ($rowsTmp3['0']['t_dsca']<>"") {
						$tmpDsca=$rowsTmp3['0']['t_dsca'];
					} else {
						$tmpDsca=$rowsTmp2['0']['t_dsca'];
					}

					$html="";
					$html.="<html>";
					$html.="<head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'></head>";
					$html.="<body>";
					$html.="<table border=0>";

					if ($vars['z']=='500' or $vars['z']=='520') {
						$html.="<tr><td style='font-family: arial; font-size: 10pt;'><b>OF									</td><td>:</td><td style='font-family: arial;'><b>".$rowsTmp1['0']['t_pdno']."</td></tr>";
						$html.="<tr><td style='font-family: arial; font-size: 10pt;'><b>Date d&eacute;but planifi&eacute;e	</td><td>:</td><td style='font-family: arial;'><b>".substr($rowsTmp1['0']['t_prdt'],0,10)."</td></tr>";
						$html.="<tr><td style='font-family: arial; font-size: 10pt;'><b>Date fin pr&eacute;vue				</td><td>:</td><td style='font-family: arial;'><b>".substr($rowsTmp1['0']['t_qrdr'],0,10)."</td></tr>";
						$html.="<tr><td style='font-family: arial; font-size: 10pt;'><b>Quantit&eacute; livr&eacute;e		</td><td>:</td><td style='font-family: arial;'><b>".$rowsTmp1['0']['t_qdlv']."</td></tr>";
						$html.="<tr><td style='font-family: arial; font-size: 10pt;'><b>Article								</td><td>:</td><td style='font-family: arial;'><b>".$rowsTmp1['0']['t_mitm']."</td></tr>";
						$html.="<tr><td style='font-family: arial; font-size: 10pt;'><b>Description							</td><td>:</td><td style='font-family: arial;'><b>".$tmpDsca."</td></tr>";
						$html.="<tr><td style='font-family: arial; font-size: 10pt;'><b>Projet								</td><td>:</td><td style='font-family: arial;'><b>".$rowsTmp1['0']['t_cprj']."</td></tr>";
						$html.="<tr><td style='font-family: arial; font-size: 10pt;'><b>Magasin								</td><td>:</td><td style='font-family: arial;'><b>".$rowsTmp1['0']['t_cwar']."</td></tr>";
					} else {
						$html.="<tr><td style='font-family: arial; font-size: 10pt;'><b>Production Order					</td><td>:</td><td style='font-family: arial;'><b>".$rowsTmp1['0']['t_pdno']."</td></tr>";
						$html.="<tr><td style='font-family: arial; font-size: 10pt;'><b>Planned Start Date					</td><td>:</td><td style='font-family: arial;'><b>".substr($rowsTmp1['0']['t_prdt'],0,10)."</td></tr>";
						$html.="<tr><td style='font-family: arial; font-size: 10pt;'><b>Planned End Date					</td><td>:</td><td style='font-family: arial;'><b>".substr($rowsTmp1['0']['t_qrdr'],0,10)."</td></tr>";
						$html.="<tr><td style='font-family: arial; font-size: 10pt;'><b>Delivered quantity					</td><td>:</td><td style='font-family: arial;'><b>".$rowsTmp1['0']['t_qdlv']."</td></tr>";
						$html.="<tr><td style='font-family: arial; font-size: 10pt;'><b>Item								</td><td>:</td><td style='font-family: arial;'><b>".$rowsTmp1['0']['t_mitm']."</td></tr>";
						$html.="<tr><td style='font-family: arial; font-size: 10pt;'><b>Description							</td><td>:</td><td style='font-family: arial;'><b>".$tmpDsca."</td></tr>";
						$html.="<tr><td style='font-family: arial; font-size: 10pt;'><b>Project								</td><td>:</td><td style='font-family: arial;'><b>".$rowsTmp1['0']['t_cprj']."</td></tr>";
						$html.="<tr><td style='font-family: arial; font-size: 10pt;'><b>Warehouse							</td><td>:</td><td style='font-family: arial;'><b>".$rowsTmp1['0']['t_cwar']."</td></tr>";

					}
					$html.="</table>";
					$html.="<hr><br>";
					$html.="<table border=0 width=100%>";


					// List tasks
					$query="select t_pdno, t_opno, t_tano, t_cwoc, t_prtm from ttisfc010{$vars['z']} where t_pdno = {$rowsTmp1['0']['t_pdno']} and ";

					if ($vars['z']=='500') {
						if ($vars['Ac'] === "Elec.") 					{$query.="t_opno in (860)";	}
						if ($vars['Ac'] === "Meca") 					{$query.="t_opno in (840, 870)";	}
						if ($vars['Ac'] === "Montage / Assembly") 		{$query.="t_opno in (830, 840, 860, 870)";	}
						if ($vars['Ac'] === "Peinture / Paint")		{$query.="t_opno in (850, 845)";	}
						if ($vars['Ac'] === "Controle / Test / QA")	{$query.="t_opno in (870, 880)";	}
						if ($vars['Ac'] === "Mag / Warehouse") 		{$query.="t_opno in (799)";	}
						if ($vars['Ac'] === "General") 				{$query.="t_opno in (10, 20, 30, 799, 810, 830, 840, 841, 842, 843, 850, 855, 860, 870, 875, 879, 880)";	}
					}

					if ($vars['z']=='520') {
						if ($vars['Ac'] === "Montage / Assembly") 		{$query.="t_opno in (840, 841, 842, 843, 855, 870, 875)";	}
						if ($vars['Ac'] === "Peinture / Paint")		{$query.="t_opno in (850, 855)";	}
						if ($vars['Ac'] === "Controle / Test / QA")	{$query.="t_opno in (875, 879)";	}
						if ($vars['Ac'] === "Mag / Warehouse") 		{$query.="t_opno in (799)";	}
						if ($vars['Ac'] === "General") 				{$query.="t_opno in (799, 810, 830, 840, 841, 842, 843, 850, 855, 870, 875, 879, 880)";	}
					}

					if ($vars['z']=='640') {
						if ($vars['Ac'] === "Soudure / Welding") 		{$query.="t_tano in (101, 199)";	}
						if ($vars['Ac'] === "Elec.") 					{$query.="t_tano in (501, 502, 599)";	}
						if ($vars['Ac'] === "Meca") 					{$query.="t_tano in (301, 302, 399)";	}
						if ($vars['Ac'] === "Montage / Assembly") 		{$query.="t_tano in (401, 402, 499)";	}
						if ($vars['Ac'] === "Peinture / Paint")		{$query.="t_tano in (201, 299)";	}
						if ($vars['Ac'] === "Controle / Test / QA")	{$query.="t_tano in (801, 802, 899)";	}
						if ($vars['Ac'] === "General") 				{$query.="t_tano in (101, 199, 201, 299, 301, 302, 399, 401, 402, 499, 501, 502, 599, 601, 801, 802, 899)";	}
					}

					if ($vars['z']=='660') {
						if ($vars['Ac'] === "Soudure / Welding") 		{$query.="t_tano in (101, 199)";	}
						if ($vars['Ac'] === "Elec.") 					{$query.="t_tano in (501, 502, 599)";	}
						if ($vars['Ac'] === "Meca") 					{$query.="t_tano in (301, 302, 399)";	}
						if ($vars['Ac'] === "Montage / Assembly") 		{$query.="t_tano in (401, 402, 499)";	}
						if ($vars['Ac'] === "Peinture / Paint")		{$query.="t_tano in (201, 299)";	}
						if ($vars['Ac'] === "Controle / Test / QA")	{$query.="t_tano in 801, 802, 899)"; }
						if ($vars['Ac'] === "General") 				{$query.="t_tano in (101, 199, 201, 299, 301, 302, 399, 401, 402, 499, 501, 502, 599, 601, 801, 802, 899)";	}
					}

					$rowsTmp4 = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);

					foreach ($rowsTmp4 as $key1 => $reportRow) {

						// get task description
						$query="select t_dsca from ttirou003{$vars['z']} where t_tano = '{$rowsTmp4[$key1]['t_tano']}'";
						$rowsTmp5 = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);

						if ($vars['z']=='500' or $vars['z']=='520') {
							$html.="<tr width=100%><td align=right style='font-family: arial; font-size: 10pt;'>&nbsp;<br>&nbsp;<br><b>Op&eacute;ration / T&acirc;che: ".$rowsTmp4[$key1]['t_opno']." / ".$rowsTmp4[$key1]['t_tano'].": ".$rowsTmp5['0']['t_dsca']."<br>&nbsp;<br>&nbsp;<br>&nbsp;</td>";
						} else {
							$html.="<tr width=100%><td align=right style='font-family: arial; font-size: 10pt;'>&nbsp;<br>&nbsp;<br><b>Operation / Task: ".$rowsTmp4[$key1]['t_opno']." / ".$rowsTmp4[$key1]['t_tano'].": ".$rowsTmp5['0']['t_dsca']."<br>&nbsp;<br>&nbsp;<br>&nbsp;</td>";
						}

						// Add barcode
						$image=nothing;
						$Image = new tldBarcode($rowsTmp4[$key1]['t_pdno'].$rowsTmp4[$key1]['t_opno'], 'Code39');
						$html.="<td align=center>".$Image->toHtml()."</td></tr>";
					}
					$html.="</table></body>";
					$html.="</html>";

					if ($html !== "Not found...") {
						if ($vars['Ou'] === 'PDF') {
							$pdf = new tldHTML2PDF($html,array('encoding'=>'utf-8', 'margin'=>'10'));
							$pdf->outFile("aaa.pdf");
							exit;
						}
					}
			} // else if ($rowsTmp1['0']['t_pdno']=="") {
		}//else{
			$body = $form->toHTML();
			if ($vars['Ou'] === 'Screen') {
				$body .= $html;
			} else {
				if ($html === "Not found...") {
					$body .= $html;
				}
			}

		//}
	break;


	case 'HRA_Weekly_report':

		$xItems = array(
			"x_emno"=>"Employee",
			"x_nama"=>"Employee name",
			"x_hrdt"=>"Date time",
			"x_hprod"=>"HPROD",
			"x_himp"=>"HIMP",
			"x_habs"=>"HABS",
			"x_habs_te"=>"HABS TE",
			"x_habs_nte"=>"HABS NTE",
			"x_hrea"=>"TOTAL"
		);

		// Get listing
		$erpList = tldLocation::getERPList("smartyOptions");
		$hourStatus = array("Not Active", "Active", "All");
		$noData = array("No", "Yes");

		// Get form
		$form = new HTML_QuickForm('frmHRA_Weekly_report', 'get', "", "", "", true);
		$form->addElement(	'hidden', 'm[0]', 'reports');
		$form->addElement(	'hidden', 'm[1]', 'listing');
		$form->addElement(	'hidden', 'm[2]', 'HRA_Weekly_report');
		$form->addElement(	'header', 'title','Select Parms:');
		$form->addElement(	'date', 	'x',	'From',
			array("format"=>"Y-m-d","minYear"=>date('Y')-2,"maxYear"=>date('Y')+2));
		$form->addElement(	'date', 	'y',	'To',
			array("format"=>"Y-m-d","minYear"=>date('Y')-2,"maxYear"=>date('Y')+2));
		$form->addElement(	'text', 'Uf', 'User from');
		$form->addElement(	'text', 'Ut', 'User to');
		$form->addElement(	'text', 'Sf', 'Supervisor from');
		$form->addElement(	'text', 'St', 'Supervisor to');
		//$form->addElement(	'select', 'Hs', 'Hour Status', array_combine($hourStatus, $hourStatus));
		//$form->addElement(	'select', 'Nd', 'Display employees with no data', array_combine($noData, $noData));
		$form->addElement(	'select', 	'z',	'Company#',
			array(""=>"")+$erpList);
		$form->addElement(	'submit', 'btnSubmit', 'Submit');
		$form->addRule('z', 'This is required', 'required');
		$form->setDefaults(array("Uf"=>"0"));
		$form->setDefaults(array("Ut"=>"999999"));
		$form->setDefaults(array("Sf"=>"0"));
		$form->setDefaults(array("St"=>"999999"));

		// Get default ERP for connected user
		$location = new tldLocation($user->getBUID());
		$form->setDefaults(array("z"=>$location->getERP()));

		// Select erp 500 if user is linked to fake ERP 510
		$erpReal = $location->getERP();
		if ($erpReal=='510') {
			$erpReal='500';
			$form->setDefaults(array("z"=>"500"));
		}

		// Fill supervisor ID if connected user is supervisor
$query=<<<EOF
		select top 1 F1.t_emno as emno from ttihra921$erpReal F1 where F1.t_blog='{$user->getBannUserID()}'
EOF;

		$rowsTmp1 = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
		$form->setDefaults(array("Sf"=>$rowsTmp1['0']['emno']));
		$form->setDefaults(array("St"=>$rowsTmp1['0']['emno']));


        if (date("l") === "Monday") {
        	$form->setDefaults(array("x"=>date('Y-m-d')));
        } else {
			$form->setDefaults(array("x"=>date('Y-m-d', strtotime('Last Monday', time()))));
        }
	        if (date("l") === "Sunday") {
        	$form->setDefaults(array("y"=>date('Y-m-d')));
        } else {
			$form->setDefaults(array("y"=>date('Y-m-d', strtotime('Next Sunday', time()))));
        }

		if($form->validate()){
			$vars = tldUtils::cleanupFormInput($form->exportValues());
			$vars['from']=implode('-',$vars['x']);
			$vars['to']=implode('-',$vars['y']);

			$rows = tldUtils::getSqlToAssocArray("EXEC HRA_Cumulated_Week '{$vars['from']}','{$vars['to']}','{$vars['z']}','{$vars['Uf']}','{$vars['Ut']}','{$vars['Sf']}','{$vars['St']}'", "odbc", array("src"=>"baan"));

			$body = '';
            $caption = "Hour accounting detailled report for company {$vars['z']}";


// Employees list
if (1==2){
$tmp_sav_emno='';
$tmp_sav_nama='';
$tot_hrea=0;
foreach ($rows as $key1 => $reportRow) {
	if ($tmp_sav_emno=='') {
		$tmp_sav_emno=$rows[$key1]['x_emno'];
		$tmp_sav_nama=$rows[$key1]['x_nama'];
	}

	$tmp_emno=$rows[$key1]['x_emno'];
	$tmp_nama=$rows[$key1]['x_nama'];
	$tmp_hrea=$rows[$key1]['x_hrea'];

	if ($tmp_sav_emno<>$tmp_emno and $tmp_sav_emno<>0 ) {
		$caption .= "<a>" . $tmp_sav_emno . " - " . $tmp_sav_nama . " (" . $tot_hrea . " hours)</a><br>";
		$tmp_sav_emno=$tmp_emno;
		$tmp_sav_nama=$tmp_nama;
		$tot_hrea=0;
	}

	$tot_hrea+=$tmp_hrea;
}
if ($tmp_sav_emno<>'') {
	$caption .= "<a>" . $tmp_sav_emno . " - " . $tmp_sav_nama . " (" . $tot_hrea . " hours)</a><br>";
}
} // if (1==2){

$caption .= "<br>";

// Employees week
$tmp_sav_emno='';
$tot_hrea=0;
$tot_hprod=0;
$tot_himp=0;
$tot_habs=0;
$tot_habs_te=0;
$tot_habs_nte=0;

foreach ($rows as $key1 => $reportRow) {
	$tmp_emno=$rows[$key1]['x_emno'];
	$tmp_nama=$rows[$key1]['x_nama'];
	$tmp_hrdt=$rows[$key1]['x_hrdt'];
	$tmp_hrea=$rows[$key1]['x_hrea'];
	$tmp_hprod=$rows[$key1]['x_hprod'];
	$tmp_himp=$rows[$key1]['x_himp'];
	$tmp_habs=$rows[$key1]['x_habs'];
	$tmp_habs_te=$rows[$key1]['x_habs_te'];
	$tmp_habs_nte=$rows[$key1]['x_habs_nte'];

	if ($tmp_sav_emno<>'' and $tmp_sav_emno<>$tmp_emno) { // close previous employee
		$caption .= "<tr bgcolor='#2971A8'>";
		$caption .= "<td><font color='white'>TOTAL</font></td>";
		$caption .= "<td><font color='white'>" . $tot_hprod . "</font></td>";
		$caption .= "<td><font color='white'>" . $tot_himp  . "</font></td>";
		$caption .= "<td><font color='white'>" . $tot_habs . "</font></td>";
		$caption .= "<td><font color='white'>" . $tot_habs_te . "</font></td>";
		$caption .= "<td><font color='white'>" . $tot_habs_nte . "</font></td>";
		$caption .= "<td><font color='white'>" . $tot_hrea . "</font></td>";
		$caption .= "</tr>";

		$tot_hprod=0;
		$tot_himp=0;
		$tot_habs=0;
		$tot_habs_te=0;
		$tot_habs_nte=0;
		$tot_hrea=0;

		$caption .= "</tbody>";
		$caption .= "<tfoot></tfoot>";
		$caption .= "</table>";
		$caption .= "</div>";
		$caption .= "</td></tr>";
	} // if ($tmp_sav_emno<>'') { // close previous employee

	if ($tmp_sav_emno<>$tmp_emno) { // header
		$tmp_sav_emno=$tmp_emno;

		$caption .= "<table><tr><td>";
		$caption .= "<div class='columnar'>";
		$caption .= "<table class='sortable' cellpadding='3'>";
		$caption .= "<thead>";
		$caption .= "<tr>";
		$caption .= "<th width=100>Employee</th>";
		$caption .= "<th width=200>Name</th>";
		$caption .= "</tr>";
		$caption .= "</thead>";
		$caption .= "<tbody>";
		$caption .= "<tr bgcolor='#eeeeee'>";
		$caption .= "<td>" . $tmp_emno . "</td>";
		//$caption .= "<td>" . $tmp_nama . "</td>";
		$caption .= "<td>";
		$caption .= "<a href='/en/private/manufacturing/index.php?_qf__frmHRA_Detailed=&m[0]=reports&m[1]=listing&m[2]=HRA_Detailed";
		$caption .= "&x[Y]=" . $vars['x']['Y'];
		$caption .= "&x[m]=" . $vars['x']['m'];
		$caption .= "&x[d]=" . $vars['x']['d'];
		$caption .= "&y[Y]=" . $vars['y']['Y'];
		$caption .= "&y[m]=" . $vars['y']['m'];
		$caption .= "&y[d]=" . $vars['y']['d'];
		$caption .= "&Uf=" . $tmp_emno;
		$caption .= "&Ut=" . $tmp_emno;
		$caption .= "&Sf=0";
		$caption .= "&St=999999";
		$caption .= "&Hs=Not+Active";
		$caption .= "&Nd=No";
		$caption .= "&z=" . $vars['z'] . "'>";

		$caption .= $tmp_nama . "</a></td>";

		$caption .= "</tr>";
		$caption .= "</tbody>";
		$caption .= "<tfoot></tfoot>";
		$caption .= "</table>";
		$caption .= "</div>";
		$caption .= "</td><td>";
		$caption .= "<div class='columnar'>";
		$caption .= "<table class='sortable' cellpadding='3'>";
		$caption .= "<thead>";
		$caption .= "<tr>";
		$caption .= "<th>DAY</th>";
		$caption .= "<th>HPROD</th>";
		$caption .= "<th>HIMP</th>";
		$caption .= "<th>HABS</th>";
		$caption .= "<th>HABS TE</th>";
		$caption .= "<th>HABS NTE</th>";
		$caption .= "<th>TOTAL</th>";
		$caption .= "<th>Message</th>";
		$caption .= "</tr>";
		$caption .= "</thead>";
		$caption .= "<tbody>";
		$caption .= "<tr bgcolor='#eeeeee'>";
	} //if ($tmp_sav_emno<>$tmp_emno) { // header

	// detail
		$tot_hrea+=$tmp_hrea;
		$tot_hprod+=$tmp_hprod;
		$tot_himp+=$tmp_himp;
		$tot_habs+=$tmp_habs;
		$tot_habs_te+=$tmp_habs_te;
		$tot_habs_nte+=$tmp_habs_nte;

		$caption .= "<tr bgcolor='#eeeeee'>";

		// lien d�tail de la journ�e

		$caption .= "<td>";
		$caption .= "<a href='/en/private/manufacturing/index.php?_qf__frmHRA_Detailed=&m[0]=reports&m[1]=listing&m[2]=HRA_Detailed";
		$caption .= "&x[Y]=" . date("Y", strtotime($tmp_hrdt));
		$caption .= "&x[m]=" . date("n", strtotime($tmp_hrdt));
		$caption .= "&x[d]=" . date("j", strtotime($tmp_hrdt));
		$caption .= "&y[Y]=" . date("Y", strtotime($tmp_hrdt));
		$caption .= "&y[m]=" . date("n", strtotime($tmp_hrdt));
		$caption .= "&y[d]=" . date("j", strtotime($tmp_hrdt));
		$caption .= "&Uf=" . $tmp_emno;
		$caption .= "&Ut=" . $tmp_emno;
		$caption .= "&Sf=0";
		$caption .= "&St=999999";
		$caption .= "&Hs=Not+Active";
		$caption .= "&Nd=No";
		$caption .= "&z=" . $vars['z'] . "'>";
        $caption .= $tmp_hrdt  . "&nbsp;" . date("l", strtotime($tmp_hrdt)) . "</a></td>";

		// Fin lien d�tail de la journ�e

		$caption .= "<td>" . $tmp_hprod . "</td>";
		$caption .= "<td>" . $tmp_himp  . "</td>";
		$caption .= "<td>" . $tmp_habs  . "</td>";
		$caption .= "<td>" . $tmp_habs_te  . "</td>";
		$caption .= "<td>" . $tmp_habs_nte  . "</td>";
		$caption .= "<td>" . $tmp_hrea  . "</td>";

		// Hours work center
		$query="select CASE count(distinct t_cwoc) WHEN 0 THEN '' ELSE 'Work center issue' END as msg from ttihra100{$vars['z']} where t_emno = " . $tmp_emno . " and t_cwoc <>(select t_cwoc from ttccom001{$vars['z']} where t_emno = " . $tmp_emno . ") and t_hrdt='" . $tmp_hrdt . "'" ;
		$rowsTmp1 = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
		$caption .= "<td>" . $rowsTmp1['0']['msg']  . "</td>";

		$caption .= "</tr>";

} // foreach

		// end report
		if ($tmp_sav_emno<>'') { // close last employee
			$caption .= "<tr bgcolor='#2971A8'>";
			$caption .= "<td><font color='white'>TOTAL</font></td>";
			$caption .= "<td><font color='white'>" . $tot_hprod . "</font></td>";
			$caption .= "<td><font color='white'>" . $tot_himp  . "</font></td>";
			$caption .= "<td><font color='white'>" . $tot_habs . "</font></td>";
			$caption .= "<td><font color='white'>" . $tot_habs_te . "</font></td>";
			$caption .= "<td><font color='white'>" . $tot_habs_nte . "</font></td>";
			$caption .= "<td><font color='white'>" . $tot_hrea . "</font></td>";
			$caption .= "</tr>";

			$caption .= "</tbody>";
			$caption .= "<tfoot></tfoot>";
			$caption .= "</table>";
			$caption .= "</div>";
			$caption .= "</td></tr>";
			$caption .= "</table>";
		}
		//$rows = array("");
		}  //else{
			$body = $form->toHTML();
		//}
	break;


	case 'HRA_Detailed':
		$xItems = array(
			"w_comp"=>"company",
			"w_year"=>"year",
			"w_week"=>"Week",
			"w_dayn"=>"Day",
			"w_emno"=>"Employee",
			"w_nama"=>"Employee name",
			"w_acti"=>"Employee status",
			"w_supv"=>"Supervisor",
			"w_namb"=>"Supervisor name",
			"w_hrdt"=>"Date time",
			"w_hrea"=>"Actual Man Hours",
			"w_sttm"=>"Start hour",
			"w_entm"=>"End time",
			"w_ckow"=>"Hourly labor type",
			"w_cwoc"=>"Work center",
			"w_htst"=>"Hour status",
			"w_tano"=>"Task",
			"w_dsca"=>"Task description",
			"w_opno"=>"Operation",
			"w_pdno"=>"Order",
			"w_cprj"=>"Project",
			"sn"=>"S/N",
			"model"=>"Model",
			"dgt_act"=>"GT date"
		);

		// Get listing
		$erpList = tldLocation::getERPList("smartyOptions");
		$hourStatus = array("Not Active", "Active", "All");
		$emplStatus = array("Active", "Not Active", "All");
		$taskStatus = array("All", "Productive", "Non-productive");
		$noData = array("No", "Yes");

		// Get form
		$form = new HTML_QuickForm('frmHRA_Detailed', 'get', "", "", "", true);
		$form->addElement(	'hidden', 'm[0]', 'reports');
		$form->addElement(	'hidden', 'm[1]', 'listing');
		$form->addElement(	'hidden', 'm[2]', 'HRA_Detailed');
		$form->addElement(	'header', 'title','Select project and company:');
		$form->addElement(	'date', 	'x',	'From',
			array("format"=>"Y-m-d","minYear"=>date('Y')-4,"maxYear"=>date('Y')+2));
		$form->addElement(	'date', 	'y',	'To',
			array("format"=>"Y-m-d","minYear"=>date('Y')-4,"maxYear"=>date('Y')+2));
		$form->addElement(	'text', 'Uf', 'User from');
		$form->addElement(	'text', 'Ut', 'User to');
		$form->addElement(	'text', 'Sf', 'Supervisor from');
		$form->addElement(	'text', 'St', 'Supervisor to');

		$form->addElement(	'text', 'Pf', 'Project from');
		$form->addElement(	'text', 'Pt', 'Project to');
		$form->addElement(	'text', 'Tf', 'Task from');
		$form->addElement(	'text', 'Tt', 'Task to');

		$form->addElement(	'select', 'Hs', 'Hour Status', array_combine($hourStatus, $hourStatus));
		$form->addElement(	'select', 'Ts', 'Task Status', array_combine($taskStatus, $taskStatus));
		$form->addElement(	'select', 'Nd', 'Display employees with no data', array_combine($noData, $noData));
		$form->addElement(	'select', 'Es', 'Employee Status', array_combine($emplStatus, $emplStatus));
		$form->addElement(	'select', 	'z',	'Company#',
			array(""=>"")+$erpList);
		$form->addElement(	'select', 'Xl', 'Excel file', array_combine($noData, $noData));
		$form->addElement(	'submit', 'btnSubmit', 'Submit');
		$form->addRule('z', 'This is required', 'required');
		$form->setDefaults(array("Uf"=>"0"));
		$form->setDefaults(array("Ut"=>"999999"));
		$form->setDefaults(array("Sf"=>"0"));
		$form->setDefaults(array("St"=>"999999"));
		$form->setDefaults(array("Pf"=>"0"));
		$form->setDefaults(array("Pt"=>"999999"));
		$form->setDefaults(array("Tf"=>"0"));
		$form->setDefaults(array("Tt"=>"9999"));
		$form->setDefaults(array("x"=>date('Y-m-d')));
	   	$form->setDefaults(array("y"=>date('Y-m-d')));

	   	// Get default ERP for connected user
		$location = new tldLocation($user->getBUID());
		$form->setDefaults(array("z"=>$location->getERP()));

		// Select erp 500 if user is linked to fake ERP 510
		$erpReal = $location->getERP();
		if ($erpReal=='510') {
			$erpReal='500';
			$form->setDefaults(array("z"=>"500"));
		}

		// Fill supervisor ID if connected user is supervisor
$query=<<<EOF
		select top 1 F1.t_emno as emno from ttihra921$erpReal F1 where F1.t_blog='{$user->getBannUserID()}'
EOF;

		$rowsTmp1 = tldUtils::getSqlToAssocArray($query, "odbc", array("src"=>"baan"));
		$form->setDefaults(array("Sf"=>$rowsTmp1['0']['emno']));
		$form->setDefaults(array("St"=>$rowsTmp1['0']['emno']));

		if($form->validate()){
			$vars = tldUtils::cleanupFormInput($form->exportValues());
			$vars['from']=implode('-',$vars['x']);
			$vars['to']=implode('-',$vars['y']);
			ini_set("memory_limit","1000M");

			$rows = tldUtils::getSqlToAssocArray("EXEC HRA_Detailed '{$vars['from']}','{$vars['to']}','{$vars['z']}','{$vars['Uf']}','{$vars['Ut']}','{$vars['Sf']}','{$vars['St']}','{$vars['Pf']}','{$vars['Pt']}','{$vars['Hs']}','{$vars['Ts']}','{$vars['Nd']}','{$vars['Es']}','{$vars['Tf']}','{$vars['Tt']}'", "odbc", array("src"=>"baan"));

            $projects = array_filter(array_unique(array_column($rows, 'w_cprj')), function ($p) {
                return $p !== "";
            });

            $query = sprintf("select sn, model, dgt_act, t_prno from service where t_prno IN ('%s') and man_location='%s'", implode("', '", $projects), $erpList[$vars['z']]);
            $results = tldUtils::getSqlToAssocArray($query);
            $ers = [];

            foreach ($results as $er) {
                $ers[(string)trim($er['t_prno'])] = $er;
            }
            foreach ($rows as $key1 => &$row) {
                if (array_key_exists((string)$row['w_cprj'], $ers)) {
                    $row = array_merge($row, $ers[$row['w_cprj']]);
                } else {
                    $row['sn']='';
                    $row['model']='';
                    $row['dgt_act']='';
                }
            }

            $caption = "Hour accounting detailled report for company {$vars['z']}";
            if ($vars['Xl'] === 'Yes') $m[3] = "csv";
		}

        $body = $form->toHTML();
	break;

	case 'HRA_Empl_by_supv':
		$xItems = array(
			"w_supv"=>"Supervisor",
			"w_namb"=>"Supervisor name",
			"w_emno"=>"Employee",
			"w_nama"=>"Employee name",
			"w_cwtt"=>"Working time table",
		    "w_dsca"=>"Description",
		    "w_cwoc"=>"Work center",
			"w_dscb"=>"Description",
			"w_acti"=>"Active"
		);
		// Get listing
		$erpList = tldLocation::getERPList("smartyOptions");
		$emplStatus = array("Active", "Not Active", "All");

		// Get form
		$form = new HTML_QuickForm('frmHRA_Empl_by_supv', 'get', "", "", "", true);
		$form->addElement(	'hidden', 'm[0]', 'reports');
		$form->addElement(	'hidden', 'm[1]', 'listing');
		$form->addElement(	'hidden', 'm[2]', 'HRA_Empl_by_supv');
		$form->addElement(	'header', 'title','Select company:');
		$form->addElement(	'text', 'Sf', 'Supervisor from');
		$form->addElement(	'text', 'St', 'Supervisor to');
		$form->addElement(	'select', 'Es', 'Employee Status', array_combine($emplStatus, $emplStatus));
		$form->addElement(	'select', 	'z',	'Company#',
			array(""=>"")+$erpList);
		$form->addElement(	'submit', 'btnSubmit', 'Submit');
		$form->addRule('z', 'This is required', 'required');

		$form->setDefaults(array("Sf"=>"0"));
		$form->setDefaults(array("St"=>"999999"));

		// Get default ERP for connected user
		$location = new tldLocation($user->getBUID());
		$form->setDefaults(array("z"=>$location->getERP()));

		// Select erp 500 if user is linked to fake ERP 510
		$erpReal = $location->getERP();
		if ($erpReal=='510') {
			$erpReal='500';
			$form->setDefaults(array("z"=>"500"));
		}

		// Fill supervisor ID if connected user is supervisor
$query=<<<EOF
		select top 1 F1.t_emno as emno from ttihra921$erpReal F1 where F1.t_blog='{$user->getBannUserID()}'
EOF;
		$rowsTmp1 = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
		$form->setDefaults(array("Sf"=>$rowsTmp1['0']['emno']));
		$form->setDefaults(array("St"=>$rowsTmp1['0']['emno']));

		if($form->validate()){
			$vars = tldUtils::cleanupFormInput($form->exportValues());

			$rows = tldUtils::getSqlToAssocArray("EXEC HRA_Empl_by_supv '{$vars['Sf']}', '{$vars['St']}', '{$vars['Es']}','{$vars['z']}'", "odbc", array("src"=>"baan"));
            $caption = "Employees by supervisor for company {$vars['z']}";
		}//else{
			$body = $form->toHTML();
		//}
	break;

	case 'HRA_Tasks_list':
		$xItems = array(
			"w_tano"=>"Task",
			"w_dsca"=>"Description",
			"w_ckot"=>"Task type",
			"w_ckow"=>"Hourly Labor Type",
			"w_cwoc"=>"Work center"
		);
		// Get listing
		$erpList = tldLocation::getERPList("smartyOptions");
		$taskType = array("All", "Indirect", "Machine", "Non-machine", "Absence");

		// Get form
		$form = new HTML_QuickForm('frmHRA_Tasks_list', 'get', "", "", "", true);
		$form->addElement(	'hidden', 'm[0]', 'reports');
		$form->addElement(	'hidden', 'm[1]', 'listing');
		$form->addElement(	'hidden', 'm[2]', 'HRA_Tasks_list');
		$form->addElement(	'header', 'title','Select company:');
		$form->addElement(	'select', 'Ts', 'Task Type', array_combine($taskType, $taskType));
		$form->addElement(	'select', 	'z',	'Company#',
			array(""=>"")+$erpList);
		$form->addElement(	'submit', 'btnSubmit', 'Submit');
		$form->addRule('z', 'This is required', 'required');

		// Get default ERP for connected user
		$location = new tldLocation($user->getBUID());
		$form->setDefaults(array("z"=>$location->getERP()));

		// Select erp 500 if user is linked to fake ERP 510
		$erpReal = $location->getERP();
		if ($erpReal=='510') {
			$form->setDefaults(array("z"=>"500"));
		}

		if($form->validate()){
			$vars = tldUtils::cleanupFormInput($form->exportValues());

			$rows = tldUtils::getSqlToAssocArray("EXEC HRA_Tasks_list '{$vars['Ts']}', '{$vars['z']}'", "odbc", array("src"=>"baan"));
            $caption = "Tasks list for company {$vars['z']}";
		}//else{
			$body = $form->toHTML();
		//}
	break;
    case 'aeroProductionReport':
        if (!$user->isInGroupLevel('gg_ADMIN',250) && !$user->isInGroupLevel('gg_PARTS',250)
            && !$user->isInGroupLevel('gg_PUR',250) && !$user->isInGroupLevel('gg_SALES',250) && !$user->isInGroup('SUPERUSER')) {
            $DEFAULT_ERROR[] = "ERROR: You do not have permissions to access this page.";
            break;
        }

        switch($m[3]) {
            case 'xls':
                $data = $_SESSION['data_report'];
                $fields = $_SESSION['fields'];
                $reportXLS = new tldXLS(
                    $data,
                    [
                        "xItems" => $fields,
                        "showTitles" => true
                    ]
                );
                $reportXLS->out("AERO_Production_Report.xls");
                exit;
                break;
        }

        // Define a date interval
        $begin = new \DateTime('first day of 12 months ago');
        $end = new \DateTime('next month');
        $interval = new DateInterval('P1M');
        $dateInterval = new DatePeriod($begin, $interval, $end);
        foreach ($dateInterval as $period) {
            $datePeriod[] = $period->format('Y-m');
        }

        // Obtain every Sales REP from baan for AERO ERP
        $query = <<<EOF
SELECT t_nama as sales_rep
FROM ttccom001250
EOF;
        $salesRep = tldUtils::getSqlToAssocArray($query, "odbc", ["src" => "baan"]);

        foreach ($salesRep as $value) {
            $salesRepList[] = $value['sales_rep'];
        }

        // Generate the statuses as we cant get them from BaaN
        $statusSO = [
            0 => "Release Installment Line",
            1 => "Print Order Ack/Project Required",
            3 => "Maintain Deliveries",
            4 => "Print Packing Slips",
            6 => "Print Sales Invoices",
            7 => "Closed Sales Order"
        ];
        // Same for the WO Status
        $statusWO = [
            1 => "Free",
            2 => "Planned",
            3 => "Documents Printed",
            4 => "Released",
            5 => "Active",
            6 => "Completed",
            7 => "Closed",
            8 => "Archived",
            9 => "Cancelled"
        ];

        $form = new HTML_QuickForm('frmProdAero', 'post');
        $form->addElement('header', 'title', 'Sorting Options:');
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'listing');
        $form->addElement('hidden', 'm[2]', 'aeroProductionReport');
        $form->addElement('select', 'start', 'From', array_combine($datePeriod, $datePeriod));
        $form->addElement('select', 'end', 'To', array_combine($datePeriod, $datePeriod));
        $form->addElement('select', 't_nama', 'Sales Rep', ["" => "ALL"] + array_combine($salesRepList, $salesRepList));
        $form->addElement('text', 'so_number', 'SO #');
        $form->addElement('text', 'wo_number', 'WO #');
        $form->addElement('text', 't_cprj', 'Project #');
        $form->addElement('text', 't_clot', 'Serial #');
        $form->addElement('text', 'customer_name', 'Customer Name');
        $form->addElement('select', 't_ssls', 'Sales Order Status', ["" => "ALL"] + $statusSO);
        $form->addElement('select', 't_osta', 'Work Order Status', ["" => "ALL"] + $statusWO);
        $form->addElement('checkbox', 'open_sls', 'Open Sales Orders (only for SO and WO Report)');
        $form->addElement('checkbox', 'only_wo', 'Only Work Orders Report');
        $form->addElement('checkbox', 'open_wo', 'Open Work Orders (only for WO Report)');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->setDefaults(['end' => end($datePeriod)]);

        if (!$form->validate()) {
            $body = $form->toHtml();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $vars['customer_name'] = strtoupper($vars['customer_name']);
        $vars['open_sls'] = $vars['open_sls'] ? true : false;
        $vars['open_wo'] = $vars['open_wo'] ? true : false;
        $onlyWO = $vars['only_wo'] ? true : false;

        // Check if we want only Open SO
        if (false !== $vars['open_sls']) {
            unset($vars['t_ssls']);
        }

        // Check if we want only the WO
        if (false !== $onlyWO) {
            unset($vars['only_WO'], $vars['so_number'], $vars['t_ssls']);
            // Adapt the report fields
            $fields = [
                "project#" => "Project#",
                "WO#" => "Work Order#",
                "item#" => "Part#",
                "item_desc" => "Item Description",
                "del_date" => "Delivery Date",
                "warehouse_code" => "Warehouse Code",
                "WO_status" => "WO Status",
                "qty_ordered" => "Quantity on Order"
            ];
        } else {
            $fields = [
                "SO#" => "Sales Order#",
                "project#" => "Project#",
                "WO#" => "Work Order#",
                "customer_name" => "Customer Name",
                "del_city" => "Delivery City/State",
                "sales_rep" => "Sales Representative",
                "item#" => "Part#",
                "item_desc" => "Item Description",
                "del_date" => "Delivery Date",
                "warehouse_code" => "Warehouse Code",
                "SO_status" => "SO Status",
                "task" => "Create Task",
                "task_view" => "View Task",
            ];
        }

        $from = '';
        $until = '';
        // Set the period if Serial# is not specified
        if (empty($vars['t_clot'])) {
            $from = $vars['start'];
            $until = $vars['end'];
        }
        unset($vars['m'], $vars['btnSubmit'], $vars['start'], $vars['end']);
        $rows = tldSOL::getWorkOrdersAeroReport($from, $until, $vars, $onlyWO);
        // For XLS Download
        $_SESSION['data_report'] = $rows;

        foreach ($rows as $field => $value) {
            $rows[$field]['SO_status'] = $statusSO[$value['SO_status']];
            $rows[$field]['WO_status'] = $statusWO[$value['WO_status']];
            $rows[$field]['task'] = "Create new task";
            $task = tldTask::byParent($value['SO#'], 'ASO');
            if (!empty($task)) {
                $rows[$field]['task_view'] = "View task";
                $rows[$field]['task_id'] = $task[0]['id'];
            } else {
                $rows[$field]['task'] = "Create new task";
            }
        }

        $report = new tldReportColumnar(
            $rows,
            [
                "xItems" => $fields,
                "links" => [
                    "SO#" => [
                        "url" => "https://www.tld-gse.com/en/private/finance/finance.php?m[0]=so&m[1]=view&erp=250",
                        "params" => ["id" => "SO#"]
                    ],
                    "item#" => [
                        "url" => "https://www.tld-gse.com/en/private/parts/parts.php?m[0]=inv&m[1]=view",
                        "params" => ["id" => "item#"]
                    ],
                    "task" => [
                        "url" => "https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=form&m[2]=newTask",
                        "params" => ["id" => "SO#"]
                    ],
                    "task_view" => [
                        "url" => "https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view",
                        "params" => ["id" => "task_id"]
                    ],
                ],
                "title" => "Work Orders by SO#",
            ]
        );

        // For XLS Download
        $_SESSION['fields'] = $fields;
        $DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=reports&m[1]=listing&m[2]=aeroProductionReport&m[3]=xls">Download XLS</a>&nbsp;
EOF;
        $body .= $report->fetch();
    break;
    case 'aeroPNQtyAndOnOrder':
        if (!$user->isInGroupLevel('gg_ADMIN',250) && !$user->isInGroupLevel('gg_PARTS',250)
            && !$user->isInGroupLevel('gg_PUR',250) && !$user->isInGroupLevel('gg_SALES',250) && !$user->isInGroup('SUPERUSER')) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permissions to access this page.';
            break;
        }

        switch($m[3]) {
            case 'xls':
                $actualData = $_SESSION['data_report'];
                $fields = $_SESSION['fields'];
                $reportXLS = new tldXLS(
                    $actualData,
                    [
                        'xItems' => $fields,
                        'showTitles' => true
                    ]
                );
                $reportXLS->out("AERO_Available_Item.xls");
                exit;
                break;
        }

        // Generate the statuses as we cant get them from BaaN
        $itemStatus = [
            1 => 'Purchased',
            2 => 'Manufactured',
            3 => 'Generic',
            4 => 'Cost',
            5 => 'Service',
            6 => 'Subcontracting'
        ];

        $form = new HTML_QuickForm('frmInvAero', 'post');
        $form->addElement('header', 'title', 'Sorting Options:');
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'listing');
        $form->addElement('hidden', 'm[2]', 'aeroPNQtyAndOnOrder');
        $form->addElement('text', 'item#', 'Item #');
        $form->addElement('text', 'signal_code', 'Signal Code');
        $form->addElement('select', 'item_type', 'Item Type', ["" => "ALL"] + $itemStatus);
        $form->addElement('checkbox', 'po_field', 'Display PO#');
        $form->addElement('checkbox', 'wo_field', 'Display WO#');
        $form->addElement('checkbox', 'zero_cost', 'Zero Cost Items Report');
        $form->addElement('submit', 'btnSubmit', 'Submit');

        if (!$form->validate()) {
            $body = $form->toHtml();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $vars['po_field'] = (bool)$vars['po_field'];
        $vars['wo_field'] = (bool)$vars['wo_field'];
        $vars['zero_cost'] = (bool)$vars['zero_cost'];
        unset($vars['m'], $vars['btnSubmit']);
        $rows = tldINV::getAeroAvailableItemsReport($vars);

        // Define the fields
        $fields = [
            'item#' => 'Part#',
            'item_type' => 'Item Type',
            'item_desc' => 'Description',
            'safety_stock' => 'Safety Stock',
            'actual_qty' => 'Actual Quantity',
            'allocated_qty' => 'Allocated Quantity',
            'inv_qty' => 'Subtracted Quantity',
            'signal_code' => 'Signal Code',
            'on_order' => 'On Order',
            'std_cost' => 'Standard Cost',
            'mat_cost' => 'Material Cost',
            'op_cost' => 'Operation Cost',
        ];

        if (true === $vars['po_field']) {
            $fields['PO#'] = 'Purchase Order';
        }
        if (true === $vars['wo_field']) {
            $fields['WO#'] = 'Production Order';
        }
        foreach ($rows as $field => $value) {
            $rows[$field]['item_type'] = $itemStatus[$value['item_type']];
        }

        $report = new tldReportColumnar(
            $rows,
            [
                'xItems' => $fields,
                'links' => [
                    'PO#' => [
                        'url' => '/en/private/manufacturing/pur/dev.php?m[0]=po&m[1]=view&erp=250',
                        'params' => ['id' => 'PO#']
                    ],
                    'item#' => [
                        'url' => '/en/private/parts/parts.php?m[0]=inv&m[1]=view',
                        'params' => ['id' => 'item#']
                    ],
                ],
                'title' => 'AERO Inventory - Available Items',
            ]
        );

        // For XLS Download
        $_SESSION['fields'] = $fields;
        $_SESSION['data_report'] = $rows;
        $DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=reports&m[1]=listing&m[2]=aeroPNQtyAndOnOrder&m[3]=xls">Download XLS</a>&nbsp;
EOF;
        $body .= $report->fetch();
    break;

    case 'onTimeAndAllGTByFactory':
        switch($m[3]) {
            case 'xls':
                $data = $_SESSION['data_report'];
                $reportXLS = new tldXLS(
                    $data,
                    [
                        'xItems' => [
                            'factory'   => 'Factory',
                            'ontime_gt' => 'On time GT',
                            'all_gt'    => 'All GT'
                        ],
                        'showTitles' => true
                    ]
                );
                $reportXLS->out('TLD_Count_On_Time_All_GT.xls');
                exit;
                break;
        }

        $form = new HTML_QuickForm('frmMan', 'post');
        $form->addElement('header', 'title', 'Sorting Options:');
        $form->addElement('hidden', 'm[0]', 'reports');
        $form->addElement('hidden', 'm[1]', 'listing');
        $form->addElement('hidden', 'm[2]', 'onTimeAndAllGTByFactory');
        $form->addElement('date', 'date_start', 'From:',
            [
                'format'  => 'Y-m',
                'minYear' => date('Y') - 3,
                'maxYear' => date('Y')
            ]);
        $form->addElement('date', 'date_end', 'To:',
            [
                'format'  => 'Y-m',
                'minYear' => date('Y') - 3,
                'maxYear' => date('Y')
            ]);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->setDefaults(['date_start' => date('Y-01')]);
        $form->setDefaults(['date_end' => date('Y-m')]);

        if (!$form->validate()) {
            $body = $form->toHtml();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $dateFrom = new DateTime(implode('-', $vars['date_start']));
        $dateTo = new DateTime(implode('-', $vars['date_end']));
        $rows = tldEquipment::getCountOnTimeAndAllGTByFactory($dateFrom, $dateTo);
        // For XLS Download
        $_SESSION['data_report'] = $rows;

        $report = new tldReportColumnar(
            $rows,
            [
                'xItems' => [
                    'factory'   => 'Factory',
                    'ontime_gt' => 'On time GT',
                    'all_gt'    => 'All GT'
                ],
                'title' => 'Count On Time And ALL GT by Factory',
            ]
        );

        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=reports&m[1]=listing&m[2]=onTimeAndAllGTByFactory&m[3]=xls">Download XLS</a>&nbsp;
EOF;
        $body .= $report->fetch();

        break;

        case 'cycleTime':
            $xItemsCycleTime =  [
                'factory'   => 'Factory',
                't_prno'    => 'T Project Number',
                'model'     => 'Model',
                'sn'        => 'Serial Number',
                'GT date'   => 'GT Date',
                'op_500'    => 'Op 500',
                'op_970'    => 'Op 970',
                'days'      => 'Days',
            ];
            switch($m[3]) {
                case 'xls':
                    $data = $_SESSION['data_report'];
                    $reportXLS = new tldXLS(
                        $data,
                        [
                            'xItems' => $xItemsCycleTime,
                            'showTitles' => true
                        ]
                    );
                    $reportXLS->out('TLD_Cycle_Time_Per_ER.xls');
                    exit;
                    break;
            }

            $form = new HTML_QuickForm('frmMan', 'post');
            $form->addElement('header', 'title', 'Sorting Options:');
            $form->addElement('hidden', 'm[0]', 'reports');
            $form->addElement('hidden', 'm[1]', 'listing');
            $form->addElement('hidden', 'm[2]', 'cycleTime');
            $form->addElement('date', 'date_start', 'From:',
                [
                    'format'  => 'Y-m',
                    'minYear' => date('Y') - 3,
                    'maxYear' => date('Y')
                ]);
            $form->addElement('date', 'date_end', 'To:',
                [
                    'format'  => 'Y-m',
                    'minYear' => date('Y') - 3,
                    'maxYear' => date('Y')
                ]);
            $form->addElement('submit', 'btnSubmit', 'Submit');
            $form->setDefaults(['date_start' => date('Y-01')]);
            $form->setDefaults(['date_end' => date('Y-m')]);

            if (!$form->validate()) {
                $body = $form->toHtml();
                break;
            }

            $vars = tldUtils::cleanupFormInput($form->exportValues());
            $dateFrom = new DateTime(implode('-', $vars['date_start']));
            $dateTo = new DateTime(implode('-', $vars['date_end']));
            $rows = tldEquipment::getCycleTimePerEquipmentRecord($dateFrom, $dateTo);
            // For XLS Download
            $_SESSION['data_report'] = $rows;

            $report = new tldReportColumnar(
                $rows,
                [
                    'xItems' => $xItemsCycleTime,
                    'title' => 'Cycle Time per ER',
                ]
            );

            $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=reports&m[1]=listing&m[2]=cycleTime&m[3]=xls">Download XLS</a>&nbsp;
EOF;
            $body .= $report->fetch();

            break;
	}

	if(isset($rows,$caption)){
		$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER["REQUEST_URI"]}&m[3]=xls">XLS version</a>&nbsp;|&nbsp;
<a href="{$_SERVER["REQUEST_URI"]}&m[3]=csv">CSV version</a>
EOF;
	    switch($m[3]){
        case 'xls':
            $report = new tldXLS(
            	$rows,
            	array(
            		"xItems"=>$xItems,
            		"showTitles"=>TRUE
            	)
            );
    		$report->out();
    		exit;
        break;
        case 'csv':
            $report = new tldCSV(
            	$rows,
            	array(
            		"xItems"=>$xItems,
            		"showTitles"=>TRUE
            	)
            );
    		$report->out();
    		exit;
        break;
        default:

			$report = new tldReportColumnar($rows,
				array(
					"xItems"=>$xItems,
					"title"=>$caption,
					"links"=>$links
				)

			);

            $body .= $report->fetch();

		break;
        }
	}
break;
default:
    $smarty->assign('SHOPFLOOR_URL', $SHOPFLOOR_URL);
	$body = $smarty->fetch("$PATH/homepage.reports.tpl");
break;
}
?>
