<?php
$DEFAULT_TITLE .= "\Planning";

switch($m[1]) {
case 'reports':

    // Selection of BU ERP --------------------------->

    $erpList = tldLocation::getERPList('smartyOptions');
    // Form selection
    $form = new HTML_QuickForm('frmByNum');
    $form->addElement(	'hidden', 'm[0]', $m[0]);
    $form->addElement(	'hidden', 'm[1]', $m[1]);
    $form->addElement(	'hidden', 'm[2]', $m[2]);
    $form->addElement(	'header', 'header', 'Select Location');
    $form->addElement(	'select', 'erp', 'Location', $erpList);
    $form->addElement(	'select', 'out', 'Ouput', array('xls'=>'XLS','csv'=>'CSV'));
    $form->addElement(	'submit', 'btnSubmit', 'Submit');
    $form->addRule('erp','Required','required');
    $form->setDefaults(array('erp'=>tldLocation::getERPByID($user->getBUID())));

    // Direct access with parameter in url
    if(!empty($_REQUEST['erp']) && in_array($_REQUEST['erp'],array_keys($erpList))){
        $ERP = $_REQUEST['erp'];
    }
    // Parameter handled by form
    elseif($form->validate()){
        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $ERP = $vars['erp'];
    }else{
        $body = $form->toHTML();
        break;
    }

    // Get data --------------------------->

    switch($m[2]){
    case 'planning_of':
        $query=<<<EOF
SELECT
	rtrim(pcs.t_dscb) AS t_dscb,
    rtrim(pcs.t_dscc) AS t_dscc,
    rtrim(pcs.t_cprj) AS t_cprj,
    pcs.t_dsca,
    rtrim(pcs.t_seak) AS t_seak,
	rtrim(sfc.t_mitm) AS t_mitm,
	pcs021.t_dscb AS pcs021_t_dscb,
    convert(varchar, sfc.t_prdt, 111) AS t_prdt,
    convert(varchar, sfc.t_dldt, 111) AS t_dldt,
    sfc.t_pdno,
	CASE
		WHEN (sfc.t_rwko=1 OR sfc.t_mitm LIKE 'COMP%') THEN sfc.t_qrdr/10
		ELSE sfc.t_qrdr
	END AS t_qrdr,
	CASE WHEN (sfc.t_rwko=1 OR sfc.t_mitm LIKE 'COMP%') THEN sfc.t_qdlv/10 ELSE sfc.t_qdlv END AS t_qdlv,
	CASE sfc.t_comp WHEN 1 THEN 0 ELSE 1 END AS t_comp,
	rtrim(pcs021.t_dscc) AS t_dscc21,
	rtrim(pcs021.t_dscd) AS t_dscd21
FROM
	ttipcs020$ERP AS pcs
	JOIN ttipcs021$ERP AS pcs021 ON pcs.t_cprj=pcs021.t_cprj
	JOIN ttisfc001$ERP AS sfc ON pcs021.t_cprj=sfc.t_cprj AND pcs021.t_item=sfc.t_mitm
WHERE
    sfc.t_osta < 6
    AND pcs.t_cprj<>''
    AND (pcs.t_cprj < 960000 OR pcs.t_cprj >= 970000)
EOF;

        $xItems = array(
            "t_dscc21"=>"Prenre le deuxieme champ",
            "t_dscd21"=>"Prender troisieme champ",
            "t_cprj"=>"Project Number",
            "t_dsca"=>"Designation du projet",
            "t_seak"=>"Search Key",
            "t_mitm"=>"Part Number",
            "pcs021_t_dscb"=>"Revision",
            "t_prdt"=>"Planned Date",
            "t_dldt"=>"Delivery Date",
            "t_pdno"=>"Production Order",
            "t_qrdr"=>"Order Qty",
            "t_qdlv"=>"Del Qty",
            "t_comp"=>"Completed"
        );
        $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    break;
    case 'planning_pof_prp':
        $query=<<<EOF
SELECT
	distinct pcs.t_cprj,
    pcs.t_dscb,
    pcs.t_dscc,
    pcs.t_dsca,
    rtrim(pcs.t_seak) AS t_seak,
	rtrim(pcs5.t_item) AS t_item,
	pcs0.t_dscb AS pcs021_t_dscb,
    convert(varchar, pcs5.t_psdt, 111) AS t_psdt,
    convert(varchar, pcs5.t_pfdt, 111) AS t_pfdt,
    pcs5.t_orno, pcs5.t_oqan,
    pcs0.t_qpnt, pcs0.t_dscd,
    pcs0.t_dscc as t_dscc21, pcs0.t_dscd as t_dscd21
FROM
	ttipcs020$ERP AS pcs
	JOIN ttipcs021$ERP AS pcs0 ON pcs.t_cprj=pcs0.t_cprj
	JOIN ttipcs510$ERP AS pcs5 ON pcs0.t_cprj=pcs5.t_cprj AND pcs0.t_item=pcs5.t_item
WHERE
    pcs.t_cprj<>'' AND pcs.t_psts = 3
    AND (pcs.t_cprj < 960000 OR pcs.t_cprj >= 970000)
EOF;
        $xItems = array(
        	"t_dscc21"=>"Prenre le deuxieme champ",
            "t_dscd21"=>"Prender troisieme champ",
            "t_cprj"=>"Project Number",
            "t_dsca"=>"Designation du projet",
            "t_seak"=>"Search Key",
            "t_item"=>"Part Number",
            "pcs021_t_dscb"=>"Revision",
            "t_psdt"=>"Debut Date",
            "t_pfdt"=>"GT Date",
            "t_orno"=>"WO Number",
            "t_oqan"=>"Qty",
            "t_qpnt"=>"qpnt",
            "t_dscd"=>"dscd"
        );
        $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    break;
    case 'planning_pof_mrp':
        $query=<<<EOF
SELECT
	rtrim(itm.t_seak) AS t_seak,
    rtrim(itm.t_seab) AS t_seab,
    itm.t_dscb,
    itm.t_dsca,
    itm.t_dscc,
	rtrim(mrp.t_item) AS t_item,
    convert(varchar, mrp.t_psdt, 111) AS t_psdt,
    convert(varchar, mrp.t_pfdt, 111) AS t_pfdt,
    mrp.t_orno, mrp.t_oqan,
    itm.t_qpnt, itm.t_dscd
FROM
	ttimrp020$ERP AS mrp
    JOIN ttiitm001$ERP AS itm ON mrp.t_item=itm.t_item
WHERE
	itm.t_item LIKE 'PDP%'
EOF;
        $xItems = array(
            "t_seak"=>"Search Key",
            "t_seab"=>"Search Key B",
            "t_dscb"=>"dscb",
            "t_dsca"=>"Designation du projet",
            "t_dscc"=>"Designation",
            "t_item"=>"Part Number",
            "t_psdt"=>"Debut Date",
            "t_pfdt"=>"GT Date",
            "t_orno"=>"WO Number",
            "t_oqan"=>"Qty",
            "t_qpnt"=>"Qty Realise",
            "t_dscd"=>"Sortie"
        );
        $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    break;
    }

    // Display report -------------------------------->

    switch($out){
    case "csv":
        $report = new tldCSV(
            $rows,
            array(
            	"xItems"=>$xItems,
            	"showTitles"=>true
            )
        );
        $report->out();
        exit;
    break;
    default:
        $report = new tldXLS(
            $rows,
            array(
            	"xItems"=>$xItems,
            	"showTitles"=>true
            )
        );
        $report->out();
        exit;
    break;
    }
break;
default:
    $body .= $smarty->fetch("$PATH/planning/homepage.planning.tpl");
break;
}

?>
