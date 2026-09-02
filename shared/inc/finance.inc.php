<?php
/**
 *    Classes related to finance module
 *
 * @package FINANCE
 * @desc All classes related to FINANCE module are kept in this file
 * @access public
 * @author Graham K.L. Fong <graham.fong@tld-america.com>
 * @copyright TLD
 */

/**
 * Class for accessing and manipulating forex data from erp_forex table
 * @deprecated
 */
class tldForex
{

    public static function getCurrencyList()
    {
        return tldList::optionsByListNameAsListItemListItem('list.common.currency');
    }

    /**
     * @deprecated
     */
    public static function insert($p)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * Get rate for particular year and month
     *
     * If no year is given then returns last entry found
     *
     * @param string $from
     * @param string $to
     * @param integer $y
     * @param integer $m
     * @return real
     */
    public static function getRate($from, $to, $y = '', $m = '')
    {
        if (empty($y)) {
            $y = date('Y');
        }
        if (empty($m)) {
            $m = date('m');
        }
        $from = strtoupper($from);
        $to = strtoupper($to);
        if ($from === 'RMB') {
            $from = 'CNY';
        }
        if ($to === 'RMB') {
            $to = 'CNY';
        }
        //if from and to are the same then the rate is just 1
        if ($from == $to) {
            return 1;
        }
        $div = 1;

        if ($from === 'EUR') {
            $query = <<<EOF
            SELECT rate
             FROM erp_forex2
             WHERE nam_year='$y' AND nam_month='$m'
             AND nam_cur='$to'
             AND typ='END'
EOF;
            $rows = tldUtils::getSqlToAssocArray($query);
            //can't find the specified period so get the last one instead
            if (count($rows) !== 1) {
                $query = <<<EOF
            SELECT rate
             FROM erp_forex2
             WHERE nam_cur='$to'
             AND typ='END'
             ORDER BY nam_year DESC, nam_month DESC
             LIMIT 1
EOF;
                $rows = tldUtils::getSqlToAssocArray($query);
            }
            return $rows[0]['rate'];
        }
        if ($to === 'EUR') {
            $query = <<<EOF
            SELECT 1/rate AS rate
             FROM erp_forex2
             WHERE nam_year='$y' AND nam_month='$m'
             AND nam_cur='$from'
             AND typ='END'
EOF;
            $rows = tldUtils::getSqlToAssocArray($query);
            //can't find the specified period so get the last one instead
            if (count($rows) !== 1) {
                $query = <<<EOF
            SELECT 1/rate AS rate
             FROM erp_forex2
             WHERE nam_cur='$from'
             AND typ='END'
             ORDER BY nam_year DESC, nam_month DESC
             LIMIT 1
EOF;
                $rows = tldUtils::getSqlToAssocArray($query);
            }
            return $rows[0]['rate'];
        }
        //neither is EUR
        $query = <<<EOF
			select ROUND(t.rate/f.rate, 4) as rate
			from erp_forex2 as f join erp_forex2 as t on f.nam_year=t.nam_year and f.nam_month=t.nam_month and f.typ=t.typ
			and f.nam_cur='$from' and t.nam_cur='$to' and f.typ='END'
			 and f.nam_year=$y and f.nam_month=$m
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);
        //can't find the specified period so get the last one instead
        if (count($rows) !== 1) {
            $query = <<<EOF
			select ROUND(t.rate/f.rate, 4) as rate
			from erp_forex2 as f join erp_forex2 as t on f.nam_year=t.nam_year and f.nam_month=t.nam_month and f.typ=t.typ
			and f.nam_cur='$from' and t.nam_cur='$to' and f.typ='END'
             ORDER BY f.nam_year DESC, f.nam_month DESC
             LIMIT 1
EOF;
            $rows = tldUtils::getSqlToAssocArray($query);
            $query = <<<EOF
			select t.rate,f.rate as rate
			from erp_forex2 as f join erp_forex2 as t on f.nam_year=t.nam_year and f.nam_month=t.nam_month and f.typ=t.typ
			and f.nam_cur='$from' and t.nam_cur='$to' and f.typ='END'
             ORDER BY f.nam_year DESC, f.nam_month DESC

EOF;
            $rows = tldUtils::getSqlToAssocArray($query);
        }
        return $rows[0]['rate'];
    }

    public static function getUSDPerEUR()
    {
        $query = <<<EOF
		SELECT ROUND(rate, 4) AS rate
		FROM erp_forex2
		WHERE
			nam_cur='USD'
			AND typ='TLD'
		ORDER BY nam_year DESC, nam_month DESC
		LIMIT 1
EOF;
        $row = tldUtils::getSqlRowToAssocArray($query);

        return $row['rate'];
    }
}


class tldMargin
{
    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Get header information
     *
     * @return array
     */
    public function getHeader()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
            $SELECT
            $FROM
            WHERE mfg_margins.id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    public static function getLatest()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
            $SELECT
            $FROM
            ORDER BY id DESC
            LIMIT 10
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Generic method by constraints
     * @param mixed string or array $constraints
     * @param array $opt
     * @return array
     */
    public static function byConstraints($constraints, $opt = [])
    {
        $HAVING = is_array($constraints) ? tldUtils::constructWhere($constraints) : $constraints;
        if (!empty($HAVING)) {
            $HAVING = "HAVING $HAVING";
        }
        // Look for options
        $ORDERBY = !empty($opt['orderBy']) ? TldDatabase::escape($opt['orderBy']) : 'id DESC';

        $SELECT = self::getSELECT();
        $SELECT .= !empty($opt['select']) ? ", {$opt['select']}" : '';
        $FROM = self::getFROM();

        $query = <<<EOF
            $SELECT
            $FROM
            WHERE 1=1
            $HAVING
            ORDER BY $ORDERBY
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Generic method to get the SELECT query part
     * @return string
     */
    public static function getSELECT(): string
    {
        return <<<EOF
SELECT
    mfg_margins.*,
    loc_sso.location AS sso_fullname,
    loc_erp.location AS erp_fullname,
    loc_erp.id AS erp_id,
    customers.customer_name AS buyer,
    sor_lines.ctry AS country,
    sor_lines.model AS model,
    sor_lines.id AS sol_id,
    sor_lines.factory_margin AS est_dir_margin_per,
    service.sn AS er_sn,
    CONCAT(year, '-', LPAD(MONTH, 2, '0')) AS date
EOF;
    }

    /**
     * Generic method to get the FROM query part
     * @return string
     */
    public static function getFROM(): string
    {
        return <<<EOF
FROM mfg_margins
    LEFT JOIN service ON mfg_margins.er_id=service.id
    LEFT JOIN sor_units ON service.sor_uid=sor_units.id
    LEFT JOIN sor_lines ON sor_units.parent_id=sor_lines.id
    LEFT JOIN sor ON sor_lines.parent_id=sor.id
    LEFT JOIN customers ON sor.buyer_customer_id=customers.id
    LEFT JOIN locations AS loc_erp ON sor_lines.bu = loc_erp.id
    LEFT JOIN locations AS loc_sso ON sor.sso = loc_sso.id
EOF;
    }

    public static function getMarginsByPeriod($a)
    {
        $HAVING = is_array($a) ? tldUtils::constructWhere($a) : $a;

        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
            $SELECT
            $FROM
            WHERE 1=1
            HAVING $HAVING
            ORDER BY mfg_margins.year, mfg_margins.month, mfg_margins.id
EOF;

        $rows = tldUtils::getSqlToAssocArray($query);
        foreach ($rows as &$row) {
            $sol = new tldSOL($row['sol_id']);
            $summary = $sol->getSummary();
            $row['discf_pc'] = $summary['discf_pc'];
            $row['dir_margin'] = ROUND(($row['factory_rev'] - $row['act_lab_cost'] - $row['act_mat'] - $row['act_other_mat'] - $row['act_other_dir_cost']), 2);
            $row['dir_margin_per'] = ROUND(($row['dir_margin'] / $row['factory_rev']) * 100, 2);
            $row['std_dir_margin_per'] = ROUND((($row['factory_rev'] - $row['std_lab_cost'] - $row['std_mat'] - $row['std_other_mat'] - $row['std_other_dir_cost']) / $row['factory_rev']) * 100, 2);
        }
        return $rows;
    }

    public static function getVariancesByPeriod($a)
    {
        $HAVING = is_array($a) ? tldUtils::constructWhere($a) : $a;

        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
            $SELECT
            $FROM
            WHERE 1=1
            HAVING $HAVING
            ORDER BY mfg_margins.year, mfg_margins.month, mfg_margins.id
EOF;

        $rows = tldUtils::getSqlToAssocArray($query);
        $data = [];
        $i = 1;
        foreach ($rows as $row) {
            $variance = tldMargin::getVariances($row['id']);
            if (!empty($variance)) {
                $data[$i] = $variance;
                ++$i;
            }
        }
        return $data;
    }

    public function marginDif2percent($val)
    {
        return $val <= -2 || $val >= 2;
    }

    public static function getVariances($id)
    {
        $mfg_margin = new tldMargin($id);
        $header = $mfg_margin->itsHeader;
        $margins = $mfg_margin->getMargins($header['sol_id']);
        $std = $margins['std_dir_margin_per'];
        $act = $margins['dir_margin_per'];
        $proj = $header['est_dir_margin_per'];
        if ((!$mfg_margin->marginDif2percent($std - $act) || !$mfg_margin->marginDif2percent($std - $proj)) && (!$mfg_margin->marginDif2percent($std - $act) || !$mfg_margin->marginDif2percent($act - $proj)) && (!$mfg_margin->marginDif2percent($std - $proj) || !$mfg_margin->marginDif2percent($act - $proj))) {
            return;
        }

        $data['id'] = $header['id'];
        $data['er_sn'] = $header['er_sn'];
        $data['sol_id'] = $header['sol_id'];
        $data['sso_fullname'] = $header['sso_fullname'];
        $data['erp_fullname'] = $header['erp_fullname'];
        $data['buyer'] = $header['buyer'];
        $data['model'] = $header['model'];
        $data['std_margin'] = $std;
        $data['act_margin'] = $act;
        $data['proj_margin'] = $proj;
        $data['date'] = $header['month'] . '-' . $header['year'];
        return $data;
    }

    public function getMargins($sol_id)
    {
        $query = <<<EOF
            SELECT mfg_margins.*
            FROM mfg_margins
            WHERE mfg_margins.id=$this->itsID
EOF;
        $rows = tldUtils::getSqlRowToAssocArray($query);
        $sol = new tldSOL($sol_id);
        $summary = $sol->getSummary();
        $rows['discf_pc'] = $summary['discf_pc'];
        try {
            $rows['dir_margin'] = ROUND(($rows['factory_rev'] - $rows['act_lab_cost'] - $rows['act_mat'] - $rows['act_other_mat'] - $rows['act_other_dir_cost']), 2);
        } catch (DivisionByZeroError $error) {
            $rows['dir_margin'] = 0;
        }

        try {
            $rows['dir_margin_per'] = ROUND(($rows['dir_margin'] / $rows['factory_rev']) * 100, 2);
        } catch (DivisionByZeroError $error) {
            $rows['dir_margin_per'] = 0;
        }

        try {
            $rows['group_margin_per'] = ROUND((($rows['dir_margin_per'] / 100) + ($summary['marg_unit_pc'] / 100) * (1 - ($rows['dir_margin_per'] / 100))) * 100, 2);
        } catch (DivisionByZeroError $error) {
            $rows['group_margin_per'] = 0;
        }

        try {
            $rows['std_dir_margin_per'] = ROUND((($rows['factory_rev'] - $rows['std_lab_cost'] - $rows['std_mat'] - $rows['std_other_mat'] - $rows['std_other_dir_cost']) / $rows['factory_rev']) * 100, 2);
        } catch (DivisionByZeroError $error) {
            $rows['std_dir_margin_per'] = 0;
        }

        return $rows;
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public static function getDcurByER($er_id)
    {
        $queryCur = <<<EOF
SELECT loc_erp.dcur AS dcur
FROM service
LEFT JOIN sor_units ON service.sor_uid=sor_units.id
LEFT JOIN sor_lines ON sor_units.parent_id=sor_lines.id
LEFT JOIN locations AS loc_erp ON sor_lines.bu = loc_erp.id
WHERE service.id = $er_id
EOF;
        $cur = tldUtils::getSqlRowToAssocArray($queryCur);

        return $cur['dcur'];
    }


    public static function insert($p, $option = '')
    {
        $fields = ['er_id', 'month', 'year', 'factory_rev', 'std_hour', 'act_hour', 'std_lab_cost', 'act_lab_cost', 'std_mat', 'act_mat',
            'std_other_mat', 'act_other_mat', 'std_other_dir_cost', 'act_other_dir_cost', 'comment'];
        $query = "INSERT INTO mfg_margins SET $option ";
        $query .= tldUtils::getSqlSet($p, $fields);
        return tldUtils::sqlInsert($query);
    }

    public function update($data, $fields = '')
    {
        if (empty($this->itsID)) {
            return 'Not object context!';
        }
        $SET = tldUtils::getSqlSet($data, $fields);
        $query = "UPDATE mfg_margins SET $SET WHERE id=$this->itsID LIMIT 1";
        return tldUtils::sqlQuery($query);
    }

    public function addLogEntry($id, $comment, $num_log = 0)
    {
        $a['parent_id'] = $this->itsID;
        $a['module'] = 'margins';
        $a['poster'] = $id;
        $a['comment'] = $comment;
        $a['log_num'] = $num_log;
        return tldModLog::insert($a);
    }

    public function checkER($er_id)
    {
        $query = <<<EOF
            SELECT er_id
            FROM mfg_margins
            WHERE er_id = $er_id
EOF;

        $er_id = tldUtils::getSqlToAssocArray($query);
        return $er_id[0]['er_id'];
    }

    public static function getIDByER($er_id)
    {
        $query = <<<EOF
            SELECT id
            FROM mfg_margins
            WHERE er_id = $er_id
EOF;

        $id = tldUtils::getSqlToAssocArray($query);
        return $id[0]['id'];
    }

    public function getFullLog()
    {
        $query = <<<EOF
        SELECT
            mod_logs.*, concat(a.lastname,', ',a.firstname) as poster_fullname
        FROM mod_logs
            LEFT JOIN people AS a ON mod_logs.poster=a.id
        WHERE
            (mod_logs.parent_id=$this->itsID
            AND mod_logs.module LIKE 'margins' AND log_num<>10)
EOF;

        $query .= ' ORDER BY id DESC';
        return tldUtils::getSqlToAssocArray($query);
    }
}

class tldVAT
{
    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Get header information
     *
     * @return array
     */
    public function getHeader()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
            $SELECT
            $FROM
            WHERE vat.id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    public static function getLatest()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
                $SELECT
                $FROM
            LIMIT 10
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Generic method by constraints
     * @param mixed string or array $constraints
     * @param array $opt
     * @return array
     */
    public static function byConstraints($constraints, $opt = [])
    {
        // Construct constraints
        if (is_array($constraints)) {
            $HAVING = tldUtils::constructWhere($constraints);
        } else {
            $HAVING = $constraints;
        }
        if (!empty($HAVING)) {
            $HAVING = "HAVING $HAVING";
        }
        // Look for options
        if (isset($opt['orderBy'])) {
            $ORDERBY = tldUtils::escapeSQL(["orderBy" => $opt['orderBy']])["orderBy"];
        } else {
            $ORDERBY = 'id DESC';
        }
        if (isset($opt['select'])) {
            $SELECT_extra = ',' . $opt['select'];
        }
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();

        $query = <<<EOF
                $SELECT
                $SELECT_extra
                        $FROM
                        WHERE 1=1
                        $HAVING
                        ORDER BY $ORDERBY
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Generic method to get the SELECT query part
     * @return string
     */
    public static function getSELECT(): string
    {
        return <<<EOF
                        SELECT
                        vat.*, (vat.amount+vat.tax) AS total
EOF;
    }

    /**
     * Generic method to get the FROM query part
     * @return string
     */
    public static function getFROM(): string
    {
        return <<<EOF
                                FROM vat

EOF;
    }

    public function getVATByPeriod($a)
    {
        $HAVING = is_array($a) ? tldUtils::constructWhere($a) : $a;

        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
                $SELECT
            $FROM
            WHERE 1=1
            HAVING $HAVING
            ORDER BY vat_date
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function getVATInvoice($vid)
    {
        $query = <<<EOF
            SELECT vat.*
            FROM vat
            WHERE vat.id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public function getVATByInvoice($id)
    {
        $query = <<<EOF
                SELECT vat.*
                FROM vat
WHERE invoice_no = $id
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function checkVAT($vat_no)
    {
        $query = <<<EOF
            SELECT invoice_no
            FROM vat
            WHERE invoice_no = '$vat_no'
EOF;
        $VAT_id = tldUtils::getSqlToAssocArray($query);
        return $VAT_id[0]['invoice_no'];
    }

    public static function checkInvoice($invoice_no)
    {
        $query = <<<EOF
            SELECT invoice_no
            FROM vat
            WHERE invoice_no = '$invoice_no'
EOF;
        $VAT_id = tldUtils::getSqlToAssocArray($query);
        return $VAT_id[0]['invoice_no'];
    }

    public static function insert($p, $option = '')
    {
        $fields = ['vat_date', 'vat_no', 'cu_name', 'amount', 'tax', 'gt_no', 'cu_code', 'invoice_no', 'remark'];
        $query = "INSERT INTO vat SET $option ";
        $query .= tldUtils::getSqlSet($p, $fields);
        return tldUtils::sqlInsert($query);
    }

    public static function insertRelationship($p, $option = '')
    {
        $fields = ['invoice_no', 'gt_no'];
        $query = "INSERT INTO vat_line SET $option ";
        $query .= tldUtils::getSqlSet($p, $fields);
        return tldUtils::sqlInsert($query);
    }

    public function update($data, $fields = [])
    {
        if (empty($this->itsID)) {
            return 'Not object context!';
        }
        $SET = tldUtils::getSqlSet($data, $fields);
        $query = "UPDATE vat SET $SET WHERE id=$this->itsID LIMIT 1";
        return tldUtils::sqlQuery($query);
    }

    public function addLogEntry($id, $comment, $num_log = 0)
    {
        $a['parent_id'] = $this->itsID;
        $a['module'] = 'vat';
        $a['poster'] = $id;
        $a['comment'] = $comment;
        $a['log_num'] = $num_log;
        return tldModLog::insert($a);
    }

    public static function insertComments($data, $fields = '')
    {
        $SET = tldUtils::getSqlSet($data, $fields);
        $query = "REPLACE INTO vat_comments SET date= now(),$SET ";

        return tldUtils::sqlQuery($query);
    }
}
