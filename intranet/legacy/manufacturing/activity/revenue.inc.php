<?php
$DEFAULT_TITLE .= "\Revenue";

//override $FIELDS to make a little specification
$FIELDS["period"]= "Revenue Month";

switch($m[2]) {
case 'listBySSOERPPeriod':
    $DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=activity&m[1]=$m[1]&m[2]=$m[2]&m[3]=xls&year=$year&y=$y&x=$x&z=$z">Download XLS</a>
EOF;
    $sess_params = &$sess['manufacturing']['activity'];

    if(empty($sess_params['year'])) {
        $DEFAULT_ERROR[] = "ERROR: Year was not set in session...";
        break;
    }
    $year = ($sess_params['year'] == 'ALL') ? '%' : $sess_params['year'];

    if(empty($sess_params['location_to'])) {
        $DEFAULT_ERROR[] = "ERROR: ERP was not set in session...";
        break;
    }
    $location_to = ($sess_params['location_to'] == 'ALL') ? '%' : $sess_params['location_to'];

    $sess_params['location_from'] = $y;

    $location_from = ($sess_params['location_from'] == 'ALL') ? '%' : $sess_params['location_from'];

    $period = ($x == 'ALL') ? '%' : $x;
    $query=<<<EOF
SELECT
    bu_from.location AS location_from,
    bu_to.location AS location_to,
    (SELECT CONCAT(lastname,', ',firstname) FROM people WHERE people.id=sors.asm
    ) AS asm_fullname,
    sors.id AS sor_id,
    sors.cu_nama,
    sors.cu_orno,
    sols.ctry,
    sols.id AS sol_id,
    sols.sls_orno,
    DATE_FORMAT(trans.dtran, '%Y%m') AS period,
    sols.model AS sols_model,
    (select SUM(batch_qty) from sor_units where parent_id=sols.id
    ) as qty,
    trans.tcur AS tcur,
    trans.tval AS tval,
    from_rate.rate AS rate_from,
    to_rate.rate AS rate_to,
    ROUND((if(to_rate.rate is null, 1, to_rate.rate))/
    (if(from_rate.rate is null, 1, from_rate.rate)),
    4)  AS rate_cross,
    ROUND(trans.tval*(if(to_rate.rate is null, 1, to_rate.rate))/
    (if(from_rate.rate is null, 1, from_rate.rate)),
    2)  AS tval_dcur,
    trans.notes
FROM
    sor AS sors
    JOIN sor_lines AS sols ON sors.id=sols.parent_id
    JOIN sor_tran AS trans ON sols.id=trans.parent_id
    LEFT JOIN locations AS bu_to on sols.bu = bu_to.id
    LEFT JOIN locations AS bu_from ON sors.sso = bu_from.id
    LEFT JOIN erp_forex2 AS from_rate
        ON from_rate.nam_year*12+from_rate.nam_month=YEAR(trans.dtran)*12+MONTH(trans.dtran)-1
        AND from_rate.nam_cur=trans.tcur AND from_rate.typ='END'
    LEFT JOIN erp_forex2 AS to_rate
        ON to_rate.nam_year*12+to_rate.nam_month=YEAR(trans.dtran)*12+MONTH(trans.dtran)-1
        AND to_rate.nam_cur='$DCUR' AND to_rate.typ='END'
WHERE
    trans.ttyp='R'
    AND trans.tgrp='ERP'
HAVING
    location_from LIKE '$location_from'
    AND location_to LIKE '$location_to'
    AND LEFT(period, 4) LIKE '$year'
    AND period LIKE '$period'
ORDER BY
    location_from, location_to, period
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $report = new tldReportColumnar(
        $rows,
        array(
            "xItems"=>array(
                "location_from"=>"SSO",
                "location_to"=>"Factory",
                "asm_fullname"=>"ASM",
                "sor_id"=>"SOR ID#",
                "cu_nama"=>"Customer Name",
                "cu_orno"=>"Customer PO#",
                "ctry"=>"Country",
                "sol_id"=>"SOL ID#",
                "sls_orno"=>"SSO PO# to Factory",
                "inco"=>"Inco",
                "period"=>"Revenue Month",
                "sols_model"=>"Model Ordered",
                "tcur"=>"Unit Revenue Currency",
                "tval"=>"Unit Revenue Amount",
                "rate_cross"=>"Cross Rate",
                "tval_dcur"=>"Unit Revenue Amount ($DCUR)",
                "notes"=>"Notes"
            ),
            "title"=>"Revenue for SSO ${sess_params['location_from']}, Factory ${location_to} and Year ${year}, ($DCUR)",
            "links"=>array(
                "sor_id"=>"/en/private/sales_service/sales.php?m[0]=sor&m[1]=view&id=",
                "sol_id"=>"/en/private/sales_service/sales.php?m[0]=sol&m[1]=view&id=",
                "sn"=>"/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=search&m[2]=bySN&sn="
            )
        )
    );
    $body .= $report->fetch();
break;
case 'sumBySSOERP':
    $sess_params = &$sess['manufacturing']['activity'];
    if(empty($year)) {
        $DEFAULT_ERROR[] = "ERROR: Year was not set...";
        break;
    }
    $sess_params['year'] = $year;

    if(empty($y)) {
        $DEFAULT_ERROR[] = "ERROR: SSO was not set...";
        break;
    }
    $sess_params['location_from'] = $y;

    if(empty($x)) {
        $DEFAULT_ERROR[] = "ERROR: Factory was not set...";
        break;
    }
    $sess_params['location_to'] = $x;

    if($x == 'ALL'){
        $location_to = '%';
    }else{
        $location_to = $x;
    }
    if($y == 'ALL'){
        $location_from = '%';
    }else{
        $location_from = $y;
    }
    if($x == 'ALL' && $y == 'ALL'){
        $GROUP = "location_from, period";
    }elseif($x <> 'ALL' && $y == 'ALL'){
        $GROUP = "location_from, location_to, period";
    }elseif($x == 'ALL' && $y <> 'ALL'){
        $GROUP = "location_from, location_to, period";
    }else{
        $GROUP = "location_from, location_to, period";
    }

    $query =<<<EOF
select
    bu_from.location AS location_from,
    bu_to.location AS location_to,
    date_format(dtran, '%Y%m')as period,
    ROUND(SUM(trans.tval*(if(to_rate.rate is null, 1, to_rate.rate))/
            (if(from_rate.rate is null, 1, from_rate.rate))
            ),
    2) as tval_dcur
from
    sor AS sors
    join sor_lines as sols on sors.id=sols.parent_id
    join sor_tran AS trans on sols.id=trans.parent_id
    LEFT JOIN locations AS bu_to on sols.bu = bu_to.id
    LEFT JOIN locations AS bu_from ON sors.sso = bu_from.id
    LEFT JOIN erp_forex2 AS from_rate
        ON from_rate.nam_year*12+from_rate.nam_month=YEAR(trans.dtran)*12+MONTH(trans.dtran)-1
        AND from_rate.nam_cur=trans.tcur AND from_rate.typ='END'
    LEFT JOIN erp_forex2 AS to_rate
        ON to_rate.nam_year*12+to_rate.nam_month=YEAR(trans.dtran)*12+MONTH(trans.dtran)-1
        AND to_rate.nam_cur='$DCUR' AND to_rate.typ='END'
where
    trans.tgrp='ERP'
    AND trans.ttyp='R'
    AND YEAR(trans.dtran)=$year
GROUP BY
    $GROUP
HAVING
    location_from LIKE '$location_from'
    AND location_to LIKE '$location_to'
    AND LEFT(period, 4)='$year'
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $form = new tldMatrix(
        $rows,
        "period", "location_from", "tval_dcur",
        "$php_self?m[0]=activity&m[1]=revenue&m[2]=listBySSOERPPeriod",
        "Revenue for SSO ${sess_params['location_from']}, Factory ${sess_params['location_to']} and Year ${sess_params['year']}, ($DCUR)"
    );
    $body .= $form->fetch();
break;
default:
    $year = date('Y');
    $query = getRevenueQuery($DCUR, $year);
    $rows = tldUtils::getSqlToAssocArray($query);
    $form = new tldMatrix(
        $rows,
        "location_to", "location_from", "tval_dcur",
        "$php_self?m[0]=activity&m[1]=revenue&m[2]=sumBySSOERP&year=$year",
        "YTD Revenue by ALL SSO, ALL Factory for $year, in $DCUR"
    );
    $body .= $form->fetch();

    $query = getRevenueQuery($DCUR, $year-1);
    $rows = tldUtils::getSqlToAssocArray($query);
    $form = new tldMatrix(
        $rows,
        "location_to", "location_from", "tval_dcur",
        "$php_self?m[0]=activity&m[1]=revenue&m[2]=sumBySSOERP&year=".($year-1),
        "YTD Revenue by ALL SSO, ALL Factory for ".($year-1).", in $DCUR"
    );
    $body .= $form->fetch();
}

function getRevenueQuery($DCUR, $year){
    $query=<<<EOF
select
    bu_from.location AS location_from,
    bu_to.location AS location_to,
    ROUND(SUM(trans.tval*(if(to_rate.rate is null, 1, to_rate.rate))/
    (if(from_rate.rate is null, 1, from_rate.rate))), 2
    ) as tval_dcur
from
    sor AS sors
    join sor_lines as sols on sors.id=sols.parent_id
    join sor_tran AS trans on sols.id=trans.parent_id
    LEFT JOIN locations AS bu_to on sols.bu = bu_to.id
    LEFT JOIN locations AS bu_from ON sors.sso = bu_from.id
    LEFT JOIN erp_forex2 AS from_rate
        ON from_rate.nam_year*12+from_rate.nam_month=YEAR(trans.dtran)*12+MONTH(trans.dtran)-1
        AND from_rate.nam_cur=trans.tcur AND from_rate.typ='END'
    LEFT JOIN erp_forex2 AS to_rate
        ON to_rate.nam_year*12+to_rate.nam_month=YEAR(trans.dtran)*12+MONTH(trans.dtran)-1
        AND to_rate.nam_cur='$DCUR' AND to_rate.typ='END'
where
    trans.tgrp='ERP'
    AND trans.ttyp='R'
    AND YEAR(trans.dtran)=$year
GROUP BY
    location_from, location_to
EOF;
    return $query;
}

?>
