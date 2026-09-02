<?php

switch($m[1]){
case 'listing':
	switch($m[2]){
	case 'byCREP':
        if(empty($crep)){
            $DEFAULT_ERROR[] = "ERROR: Rep email not set...";
            break;
        }
        $email = strtolower($user->getEmail());
        $query=<<<EOF
select
    sors.t_orno, sors.t_odat, sors.t_cuno, cus.t_nama, sors.t_crep, reps.t_info,
    sols.t_dino, sols.t_invn
from ttdsls040$DEFAULT_ERP as sors
	join ttdsls045$DEFAULT_ERP as sols on sors.t_orno=sols.t_orno
 LEFT JOIN ttccom010$DEFAULT_ERP AS cus on sors.t_cuno=cus.t_cuno
 LEFT JOIN ttccom001$DEFAULT_ERP AS reps on sors.t_crep=reps.t_emno
     WHERE lower(sors.t_info)=$email
order by sors.t_odat DESC
EOF;
	break;
	}
    if(count($rows)){
		$report = new tldReportColumnar(
            $rows,
            array(
                "xItems"=>array(
                    "t_orno"=>"Order#",
                    "t_odat"=>"Order Date",
                    "t_nama"=>"Customer Name",
                    "t_cuno"=>"Customer#",
                    "t_crep"=>"Rep#",
                    "t_info"=>"Rep Email",
                    "t_dino"=>"Packing Slip#",
                    "t_invn"=>"Invoice#",
                ),
                "title"=>"Sales Order List",
                "links"=>array(
                    "t_ninv"=>array(
                        "url"=>"$php_self?m[0]=ps&m[1]=view",
                        "params"=>array(
                            "t_ninv"=>"t_ninv",
                            "t_orno"=>"t_orno"
                        )
                    )
                )
            )
        );
		$body .= $report->fetch();
	}else{
		$DEFAULT_ERROR[] = "ERROR: No Sales Orders found...";
	}

break;
default:
}

?>