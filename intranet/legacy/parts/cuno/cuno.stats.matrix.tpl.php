<?php
$statsReport = <<<EOF
<style type="text/css">
.matrix_parts{}
.matrix_parts td, .matrix_parts th{ padding:5px;}
.matrix_parts thead tr th{ background:#dedede; text-align:center; font:bold;}
.matrix_parts tbody tr td{ background:#eeeeee; text-align:right;}
.bg-grey{background:#dedede;}
.col{vertical-align:middle;}
</style>

<table class="matrix_parts">
  <thead>
    <tr><th></th><th>CUR</th><th>PARTS</th><th>UNITS</th><th>SERVICES</th><th>TOTAL</th></tr>
  </thead>
  <tbody>
EOF;
        $rows = $cust->getOpenInvoiceStats();
        $td = '<td class="bg-grey col" rowspan="'.count($rows).'">Open Invoice Balance</td>';
        $i=0;
        foreach($rows as $row){
            if($i!=0) $td="";
            $i++;
            $total = $row['total_parts_balance']+$row['total_units_balance']+$row['total_refits_balance'];
            $statsReport.=<<<EOF
    <tr>
      $td
      <td>{$row['t_ccur']}</td>
      <td>{$row['total_parts_balance']}</td>
      <td>{$row['total_units_balance']}</td>
      <td>{$row['total_refits_balance']}</td>
      <td>$total</td>
    </tr>
EOF;
        }
        $rows = $cust->getOpenOrderStats();
        $td = '<td class="bg-grey col" rowspan="'.count($rows).'">Open Order Balance</td>';
        $i=0;
        foreach($rows as $row){
            if($i!=0) $td="";
            $i++;
            $total = $row['total_parts_balance']+$row['total_units_balance']+$row['total_refits_balance'];
            $statsReport.=<<<EOF
    <tr>
      $td
      <td>{$row['t_ccur']}</td>
      <td>{$row['total_parts_balance']}</td>
      <td>{$row['total_units_balance']}</td>
      <td>{$row['total_refits_balance']}</td>
      <td>$total</td>
    </tr>
EOF;
        }
        $statsReport.=<<<EOF
  </tbody>
</table>
EOF;

return $statsReport;
?>