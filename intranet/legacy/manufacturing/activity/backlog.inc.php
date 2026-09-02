<?php
switch($m[2]) {
case 'listBySSOERPPeriod':
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

    if($sess_params['location_from'] == 'ALL') {
        $sess_params['location_from'] = $x;
    }

    $location_from = ($sess_params['location_from'] == 'ALL') ? '%' : $sess_params['location_from'];

    $location_from = ($x == 'ALL') ? '%' : $x;

    $period = substr($y, 0, 4)*12+substr($y, 4, 2);
    $query=<<<EOF
        SELECT
bu_from.location AS location_from,
bu_to.location AS location_to,
(SELECT CONCAT(lastname,', ',firstname) FROM people WHERE people.id=sors.asm) AS asm_fullname,
sors.id AS sor_id,
sors.cu_nama,
sors.cu_orno,
sols.ctry,
sols.id AS sol_id,
sols.status AS sol_status,
sols.sls_orno,
sols.model AS sols_model,
(select SUM(batch_qty) from sor_units where parent_id=sols.id) as qty,
(
SELECT ROUND(SUM((IF(trans.ttyp='R', -1, 1))*trans.tval*(if(to_rate.rate is null, 1, to_rate.rate))/
            (if(from_rate.rate is null, 1, from_rate.rate))
            ),
    2)
from sor_tran AS trans
LEFT JOIN erp_forex2 AS from_rate
ON from_rate.nam_year*12+from_rate.nam_month=YEAR(sols.dt_opened)*12+MONTH(sols.dt_opened)-1
AND from_rate.nam_cur=trans.tcur AND from_rate.typ='END'
LEFT JOIN erp_forex2 AS to_rate
ON to_rate.nam_year*12+to_rate.nam_month=YEAR(sols.dt_opened)*12+MONTH(sols.dt_opened)-1
AND to_rate.nam_cur='$DCUR' AND to_rate.typ='END'
WHERE trans.parent_id=sols.id
AND trans.tgrp='ERP'
AND YEAR(trans.dtran)*12+MONTH(trans.dtran) <= $period
AND (
        $period < YEAR(sols.dzk_erp)*12+MONTH(sols.dzk_erp)
OR
sols.dzk_erp='0000-00-00'
)
) as tval_dcur
FROM
sor AS sors
JOIN sor_lines AS sols ON sors.id=sols.parent_id
LEFT JOIN locations AS bu_from ON bu_from.erp=sors.bu
LEFT JOIN locations AS bu_to on bu_to.id=sols.bu
HAVING tval_dcur<>0
AND location_from LIKE '$location_from'
AND location_to LIKE '$location_to'
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
                "sol_status"=>"Status",
                "sls_orno"=>"SSO PO# to Factory",
                "inco"=>"Inco",
                "sols_model"=>"Model Ordered",
                "qty"=>"SOL Qty",
                "tval_dcur"=>"Backlog Amount ($DCUR)"
            ),
            "title"=>"Backlog by SSO $location_from, ERP $location_to, Period $y",
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

    if(empty($x)) {
        $DEFAULT_ERROR[] = "ERROR: SSO was not set...";
        break;
    }
    $sess_params['location_from'] = $x;

    if(empty($y)) {
        $DEFAULT_ERROR[] = "ERROR: Factory was not set...";
        break;
    }
    $sess_params['location_to'] = $y;

    $location_from = ($x == 'ALL') ? '%' : $x;
    $location_to = ($y == 'ALL') ? '%' : $y;
    $query=<<<EOF
        select
bu_from.location AS location_from,
bu_to.location AS location_to,
periods.nam_period AS period,
ROUND(
SUM(
(
IF(trans.ttyp='R', -1, 1)*trans.tval*
(IF(sols.dzk_erp='0000-00-00'
 OR YEAR(sols.dzk_erp)*12+MONTH(sols.dzk_erp) > LEFT(periods.nam_period, 4)*12+SUBSTR(periods.nam_period, 5, 2),
1, 0)
 )*
(if(to_rate.rate is null, 1, to_rate.rate))/
(if(from_rate.rate is null, 1, from_rate.rate))
)
),
2) as tval_dcur
FROM
fin_periods AS periods,
sor AS sors
JOIN sor_lines AS sols ON sors.id=sols.parent_id
JOIN sor_tran AS trans ON sols.id=trans.parent_id
LEFT JOIN locations AS bu_from ON bu_from.id=sors.sso
LEFT JOIN locations AS bu_to ON bu_to.id=sols.bu
LEFT JOIN erp_forex2 AS from_rate
ON from_rate.nam_year*12+from_rate.nam_month=YEAR(trans.dtran)*12+MONTH(trans.dtran)-1
AND from_rate.nam_cur=trans.tcur AND from_rate.typ='END'
LEFT JOIN erp_forex2 AS to_rate
ON to_rate.nam_year*12+to_rate.nam_month=YEAR(trans.dtran)*12+MONTH(trans.dtran)-1
AND to_rate.nam_cur='$DCUR' AND to_rate.typ='END'
WHERE trans.tgrp='ERP'
AND YEAR(trans.dtran)*12+MONTH(trans.dtran) <= LEFT(periods.nam_period, 4)*12+SUBSTR(periods.nam_period, 5, 2)
AND LEFT(periods.nam_period, 4)=${sess_params['year']}
AND LEFT(periods.nam_period, 4)*12+SUBSTR(periods.nam_period, 5, 2) <= YEAR(NOW())*12+MONTH(NOW())
AND bu_from.location LIKE '$location_from'
AND bu_to.location LIKE '$location_to'
GROUP BY location_from, location_to, period
HAVING
tval_dcur<>0
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $form = new tldMatrix(
        $rows,
        "location_from", "period", "tval_dcur",
        "$php_self?m[0]=activity&m[1]=backlog&m[2]=listBySSOERPPeriod",
        "Backlog for SSO ${sess_params['location_from']}, Factory ${sess_params['location_to']} and Year ${sess_params['year']}, ($DCUR)",
        array("doNotShowXTotals"=>true)
    );
    $body .= $form->fetch();
break;
default:
    for($year = date("Y"); $year>(date("Y")-3); $year--) {
        $query=<<<EOF
select DISTINCT loc_to.location
from sor_lines AS sols, sor_tran AS trans, locations AS loc_to
WHERE sols.id=trans.parent_id AND sols.bu=loc_to.id
AND YEAR(sols.dt_opened)=$year
EOF;
        $rows = tldUtils::getSqlToAssocArray($query, "smartyOptions", ['location', 'location']);
        if(count($rows)) {
            $report = new tldHTMLList(
                $rows,
                '',
                "?m[0]=activity&m[1]=backlog&m[2]=sumBySSOERP&x=ALL&year=$year&y=",
                ['title' => sprintf('%d Backlog', $year)]
            );
            $body .= $report->fetch();
        }
    }
}

