<?php
$DEFAULT_TITLE .= "\PENDING SOL";

$xItemTrans = [
    'location_from' => 'SSO',
    'location_to' => 'Factory',
    'asm_fullname' => 'ASM',
    'sor_id' => 'SOR ID#',
    'dt_opened' => 'Open Date',
    'buyer_customer_display' => 'Customer (BUYER)',
    'user_customer_display' => 'Customer (END USER)',
    'cu_orno' => 'Customer PO#',
    'ctry' => 'Country',
    'sol_id' => 'SOL ID#',
    'sls_orno' => 'SSO PO# to Factory',
    'inco' => 'Inco',
    'sols_model' => 'Model Ordered',
    'qty' => '# of units in SOL'
    //"notes"			=>"Notes"
];

switch ($m[2]) {
    case 'bySSOStatus':
        $erp = TldDatabase::escape($y);
        $sso = TldDatabase::escape($x);
        $DEFAULT_MENU .= <<<EOF
        <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <a href="$php_self?m[0]=activity2&m[1]=$m[1]&m[2]=$m[2]&m[3]=xls&year=$year&y=$y&x=$x&z=$z">Download XLS</a>&nbsp;|&nbsp;
        <a href="$php_self?m[0]=activity2&m[1]=$m[1]&m[2]=$m[2]&m[3]=fullCSV&y=$y&x=$x&z=$z">Download FULL ACCT CSV</a>
EOF;
        // Constraints
        $a = ['sols.status' => 'PENDING'];
        if ($erp <> 'ALL') {
            $a['bu_to.location'] = $erp;
        }
        if ($sso <> 'ALL') {
            $a['bu_from.location'] = $sso;
        }
        $WHERE = tldUtils::constructWhere($a);
        // Query
        $query = <<<EOF
SELECT
    bu_from.location AS location_from,
    sols.id AS sol_id,
    sols.dt_opened,
    sols.status,
    (SELECT CONCAT(lastname,', ',firstname) 
    	FROM people WHERE people.id=sors.asm
    ) AS asm_fullname,
    (SELECT type FROM customers 
    	WHERE customer_name=sors.cu_nama
    ) AS cu_type,
    sors.cu_nama AS cu_nama,
    (SELECT customers.customer_name FROM customers 
        WHERE customers.id=sors.user_customer_id
    ) AS user_customer_display,
    (SELECT customers.customer_name FROM customers 
        WHERE customers.id=sors.buyer_customer_id
    ) AS buyer_customer_display,
    sols.ctry AS ctry,
    sols.intro_new,
    "" AS continent,
    bu_to.location AS location_to,
    (SELECT cat.en FROM products_categories AS cat
		LEFT JOIN models ON models.parent_id=cat.id
		WHERE models.model=sols.model LIMIT 1
	) AS er_type,
    sors.id AS sor_id,
    sors.cu_orno AS cu_orno,
    sols.sls_orno AS sls_orno,
    sols.inco AS inco,
    sols.inco_loc,
    sols.model AS sols_model,
    (SELECT SUM(batch_qty) from sor_units where parent_id=sols.id) as qty
FROM
    sor AS sors
    JOIN sor_lines AS sols ON sors.id=sols.parent_id
    LEFT JOIN locations AS bu_to on sols.bu = bu_to.id
    LEFT JOIN locations AS bu_from ON sors.bu = bu_from.erp
WHERE $WHERE
ORDER BY location_from, location_to, sol_id
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);
        switch ($m[3]) {
            case 'fullCSV':
                $rowsCSV = [];
                $i = 0;
                foreach ($rows as $row) {
                    $i++;
                    $sol = new tldSOL($row['sol_id']);
                    // Get detailed summary
                    $summary = _getFullSummary($sol);
                    // Merge data
                    $row['level'] = "$i - SOL";
                    $rowsCSV[] = $row + $summary;
                }
                $option = ['xItems' => $xItemsCSV, 'showTitles' => true];
                $report = new tldCSV($rowsCSV, $option);
                $report->out();
                exit;
                break;
            case 'xls':
                $option = ['xItems' => $xItemTrans, 'showTitles' => true];
                $report = new tldXLS($rows, $option);
                $report->out();
                exit;
                break;
            default:
                $report = new tldReportColumnar(
                    $rows,
                    [
                        'xItems' => $xItemTrans,
                        'title' => "PENDING SOL for SSO $x and factory $y ($DCUR)",
                        'links' => [
                            'sor_id' => "$php_self?m[0]=sor&m[1]=view&id=",
                            'sol_id' => "$php_self?m[0]=sol&m[1]=view&id=",
                            'sn' => '/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=search&m[2]=bySN&sn=',
                        ],
                    ]
                );
                $body .= $report->fetch();
                break;
        }
        break;
    default:
        $form = new tldMatrix(
            tldSOL::countBySSOERPConstraints(['sol.status' => 'PENDING']),
            'sso_fullname', 'erp_fullname', 'num',
            "$php_self?m[0]=$m[0]&m[1]=$m[1]&m[2]=bySSOStatus",
            'PENDING SOL Count by Factory, SSO'
        );
        $body = $form->fetch();
        break;
}
