<?php
/**
 *    Quality related classes
 *
 * @package   Quality
 * @desc      All classes related to the quality department are kept in this file
 * @access    public
 * @author    Graham K.L. Fong <graham.fong@tld-america.com>
 * @copyright TLD
 */

/**
 * Need these functions
 */
include_once('erp.inc.php');
include_once('user.inc.php');

/**
 * Green tag records
 */
class tldGT
{
    public static function getCumulativeCountByMonth($y = '', $m = '', $t = '')
    {
        if (empty($y)) {
            $y = date('Y');
        }
        if (empty($m)) {
            $m = date('m');
        }
        if (empty($t)) {
            $t = time();
        }

        $query = <<<EOF
            SELECT
                man_location AS y,
                DATE_FORMAT(dgt_act, "%d") AS x,
                COUNT(*) AS num
            FROM
                service
            WHERE
                DATE_FORMAT(dgt_act, '%Y-%m')='$y-$m'
                AND man_location <> ''
            GROUP BY y, x
EOF;

        if (!$rows = tldUtils::getSqlToAssocArray($query)) {
            exit;
        }
        $erps = [];
        foreach ($rows as $row) {
            $erps[$row['y']][$row['x']] = $row['num'];
        }
        foreach ($erps as &$erp) {
            $num = 0;
            for ($i = 1; $i <= date('t', $t) && mktime(0, 0, 0, date('m', $t), $i, date('Y', $t)) <= time(); $i++) {
                $key = sprintf('%02s', $i);
                if (array_key_exists($key, $erp)) {
                    $num = $erp[$key] += $num;
                } else {
                    $erp[$key] = $num;
                }
            }
            ksort($erp);
        }

        return $erps;
    }

    public static function byLatest($num = 10)
    {
        $query = <<<EOF
select *
from service as t1
ORDER BY dgt_act DESC
LIMIT $num
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function countERPMonthPast12Months()
    {
        $query = <<<EOF
SELECT t1.man_location AS x,
    DATE_FORMAT(t1.dgt_act,'%Y-%m') as y,
    COUNT(*) AS num
FROM service AS t1
WHERE t1.dgt_act
    AND PERIOD_DIFF(DATE_FORMAT(NOW(),'%Y%m'),DATE_FORMAT(t1.dgt_act,'%Y%m')) <= 12
GROUP BY x, y DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byERPYearMonth($location, $ym)
    {
        $a = [];
        if ($location !== 'ALL') {
            $a['man_location'] = $location;
        }
        if ($ym !== 'ALL') {
            $a['dgt_act_ym'] = $ym;
        }

        return self::byConstraints($a);
    }

    /**
     * get GTs for specific period
     *
     * @param        $start
     * @param        $end
     * @param string $options
     *
     * @return array array of db rows
     */
    public static function byPeriod($start, $end, $options = '')
    {
        $start = TldDatabase::escape($start);
        $end = TldDatabase::escape($end);
        $where = is_array($options) ? ' AND ' .tldUtils::constructWhere($options) : '';
        $query = <<<EOF
        SELECT *,
            DATE_FORMAT(dgt_act,'%Y-%m-%d') AS dgt_act_ymd
        FROM service as t1
        WHERE
        DATE_FORMAT(t1.dgt_act,'%Y-%m-%d') BETWEEN '$start' AND '$end'
        $where
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byConstraints($constraints, $orderBy = 't1.man_location, t1.dgt_act', $options = '')
    {
        $HAVING = tldUtils::constructWhere($constraints);
        $orderBy = TldDatabase::escape($orderBy);
        $query = <<<EOF
        SELECT *,
            DATE_FORMAT(dgt_act,'%Y-%m') AS dgt_act_ym
        FROM service AS t1
EOF;
        if ($HAVING) {
            $query .= " HAVING $HAVING";
        }
        $query .= <<<EOF
            ORDER BY $orderBy
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }
}

/**
 *Creates an NCR object from data already stored in database
 *<code>
 *--
 *-- Table structure for table `ncr`
 *--
 *CREATE TABLE `ncr` (
 *  `id` int(11) NOT NULL auto_increment,
 *  `parent_id` int(11) NOT NULL default '0',
 *  `factory` int(5) NOT NULL default '0',
 *  `process` varchar(30) NOT NULL default '',
 *  `date` datetime NOT NULL default '0000-00-00 00:00:00',
 *  `status` varchar(15) NOT NULL default 'PENDING',
 *  `hours` int(11) NOT NULL default '1',
 *  `reported_by` varchar(30) NOT NULL default '',
 *  `t_emno` int(11) default '0',
 *  `problem` text NOT NULL,
 *  `short_desc` varchar(100) NOT NULL default '',
 *  `solution` text NOT NULL,
 *  `responsible` varchar(10) NOT NULL default '',
 *  `vendor_erp` int(3) default '0',
 *  `vendor_id` varchar(10) NOT NULL default '',
 *  `vendor_name` varchar(50) NOT NULL default '',
 *  `po_num` varchar(20) NOT NULL default '',
 *  `rush` char(1) NOT NULL default 'N',
 *  `charge_vendor` char(1) NOT NULL default 'N',
 *  `photo` varchar(100) NOT NULL default '',
 *  `investigation` text NOT NULL,
 *  `scrap` char(1) NOT NULL default '',
 *  `rework` char(1) NOT NULL default '',
 *  `use_as_is` char(1) NOT NULL default '',
 *  `derogation` char(1) NOT NULL default '',
 *  `return_vendor` char(1) NOT NULL default '',
 *  `vendorid` int(11) NOT NULL default '0',
 *  `charge_vendor4repairs` char(1) NOT NULL default '',
 *  `scar` char(1) NOT NULL default '',
 *  `car` char(1) NOT NULL default '',
 *  `other` char(1) NOT NULL default '',
 *  `comments` text NOT NULL,
 *  `repair_approver` varchar(30) NOT NULL default '',
 *  `repair_sig_date` date NOT NULL default '0000-00-00',
 *  `cost_cur` char(3) NOT NULL default '',
 *  `cost_total` float NOT NULL default '0',
 *  `cost_break` text NOT NULL,
 *  `cost_ref` varchar(10) NOT NULL default '',
 *  `t_ninv` varchar(11) default '',
 *  UNIQUE KEY `id` (`id`)
 *) TYPE=MyISAM
 *</code>
 *
 * @package Quality
 */
class tldNCR
{

    public $itsID;
    public $itsDetails;

    public function __construct($id)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getID()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getFactoryID()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getIFactor()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getModel()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getStatus()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Check if the current NCR object is valid
     *
     * @return boolean
     */
    public function isEmpty()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Check if the NCR is marked as a rush job
     *
     * @return string Y or NULL
     */
    public function isRush()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Get the NCR information as an array structure
     *
     * @return array
     */
    public function asArray()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Get the header information
     *
     * @return array
     */
    public function getHeader()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Get the short_desc field data
     *
     * @return string
     */
    public function getShortDesc()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Vendor id#
     *
     * ID# of vendor from the ERP system
     *
     * @return integer
     */
    public function getVendorID()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Get owner id
     *
     * Get the ID# of the NCR owner, id is in people table
     *
     * @return integer
     */
    public function getOwner()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Get field data from header
     *
     * @param $field
     *
     * @return string
     */
    public function getField($field)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Path of where photo are located
     *
     * @return string path
     */
    public function getPhotoPath()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Check of photo attached
     */
    public function isPhotoEmpty()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Insert & attach the photo to the NCR
     *
     * @param $tempFilePath
     * @param $filename
     *
     * @return string on error
     *
     */
    public function insertPhoto($tempFilePath, $filename)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Get photo file path
     *
     * @return string path
     */
    public function getPhotoFilePath()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Send photo to stdout
     */
    public function outPhoto()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Path of where files are located
     *
     * @return string path
     */
    public function getFilesPath()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Method to attach NCR files
     *
     * @param string description
     * @param array  file info
     *
     * @return string on error
     */
    public function insertFile($description, $file_array)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function delFile($id)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Get related files from ncr_files table
     *
     * @param string $id
     *
     * @return array Returns array of db rows
     */
    public function getFiles($id = '')
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Send file to output buffer directly
     *
     * @param intger $id id of file
     */
    public function outFile($id)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * get an array of db rows of related tasks
     *
     * @return array
     */
    public function getTasks()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getOpenTasks()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getLinks()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Get latest NCRs opened, returns last 20 by default
     *
     * @param int $num , number of rows to return. Default is 20
     *
     * @return array Array of db rows
     */
    public static function getLatest($num = 20)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Get vendors that are used in NCR module
     *
     * @param $options
     * @param $options2
     *
     * @return array
     */
    public static function getVendors($options, $options2)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getModels($mode = '')
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function getStatusList()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Change status of NCR
     *
     * @param $status
     *
     * @return mixed Return value from sqlQuery
     */
    public function changeStatus($status)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Set the vendor for ncr
     *
     * @param $erp
     * @param $vendorid
     *
     * @return string|void
     */
    public function transfer2Vendor($erp, $vendorid)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function refresh()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getParts()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * add a part to the current ncr
     *
     * @param array $a an associative array with keys set to field name and value with data
     *
     * @return mixed|void
     */
    public function addPart($a)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function delPart($id)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function updatePart($id, $a, $fields = '')
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function partHeader($id){
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    //STATIC functions
    public static function insertHeader($p)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function addLogEntry($id, $comment)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function getPathToUploadFile($filename)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }


    public function getLog()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function insertModels($p, $ncrid)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * @param array data values
     * @param array fields
     *
     * @return string on error
     */
    public function update($a, $fields = null)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Get NCRs by owner
     *
     * @param int    $owner  , id of owner to search for
     * @param string $option , options to modify selection criteria
     *
     * @return array Array of db rows
     */
    public function byOwner($owner, $option = '')
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Get List of assignees with open tasks
     *
     * @return array Array of
     */
    public static function getAssignees()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function getDistinctReportedBy()
    {
        $query = <<<EOF
            SELECT DISTINCT CONCAT(people.lastname, ', ', people.firstname) AS reported_by
            FROM ncr
            LEFT JOIN people ON people.id = ncr.t_emno
            WHERE DATE>=DATE_SUB(CURRENT_DATE(), INTERVAL 3 YEAR)
            ORDER BY reported_by
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * return an array of NCR id numbers belonging to a userid number
     *
     * @param        $id
     * @param string $status
     *
     * @return array returns NON CLOSED by default
     */
    public static function byAssignee($id, $status = '')
    {
        if (!is_numeric($id)) {
            $user = new tldUser($id);
            $id = $user->getId();
        }
        switch ($status) {
            case 'CLOSED':
                $where = "AND tasks.status='CLOSED'";
                break;
            default:
                $where = "AND tasks.status='OPEN'";
        }
        $query = <<<EOF
            SELECT DISTINCT ncr.id ,ncr.*,
            CONCAT(people.lastname, ', ', people.firstname) AS reported_by, YEAR(ncr.date) AS ncr_year,locations.location
            FROM ncr 
                LEFT JOIN locations ON ncr.factory=locations.id
                LEFT JOIN people ON people.id = ncr.t_emno,
            tasks
            WHERE tasks.assignee=$id
                AND ncr.id=tasks.parent_id
                AND tasks.module='NCR'
                $where
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     *    Returns array of NCR lines
     *
     * @param string $erp
     * @param string $status
     * @param string|array $sort
     *
     * @return array
     */
    public static function byERPStatus($erp = '', $status = '', $sort = '')
    {
        $where = [];
        if ($erp !== 'ALL') {
            $where[] = " locations.location='$erp'";
        }
        if ($status !== 'ALL') {
            $where[] = " ncr.status='$status'";
        }

        $WHERE = $where ? ' WHERE ' .implode(' AND ', $where) : '';

        $sortBy = ' ORDER BY factory ASC, ncr.id DESC, status DESC';
        if (is_array($sort)) {
            $sortBy = " ORDER BY {$sort['field']}";
            $sortBy .= in_array($sort['dir'], ['ASC', 'DESC'], true) ? " {$sort['dir']}" : ' ASC';
        }

        $query = <<<EOF
            SELECT ncr.*,
                CONCAT(people.lastname, ', ', people.firstname) AS reported_by, 
                YEAR(ncr.date) AS ncr_year, locations.location, group_concat(parts.part_number) AS pn, sum(parts.qty) AS qty
            FROM ncr LEFT JOIN locations ON ncr.factory=locations.id
            LEFT JOIN ncr_parts AS parts ON ncr.id = parts.parent_id
            LEFT JOIN people ON people.id = ncr.t_emno
            $WHERE
            GROUP BY id
            $sortBy
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     *    Returns a 2 dim array of counts by erp and status
     *
     * @param string $uid
     *
     * @return array|void
     */
    public static function countByFactoryStatus($uid = '')
    {
        $WHERE = '';
        if ($uid > 0) {
            $groups = $erps = [];
            $user = new tldUser($uid);
            if ($levels = $user->isInGroup('role_QAM')) {
                if (is_array($levels)) {
                    $erps = $erps + $levels;
                } else {
                    array_push($erps, $levels);
                }
                array_push($groups, 'IN PROGRESS', 'PENDING', 'SUSPENDED');
            }
            if (!$groups && !$erps) {
                return;
            }
            $where = [];
            if ($groups) {
                $where[] = " status IN('".implode('\',\'', $groups)."')";
            }
            if ($erps) {
                $where[] = ' locations.erp IN (' .implode(',', $erps). ')';
            }
            $WHERE = ' ' .implode(' AND ', $where);
        }

        $query = <<<EOF
            SELECT ncr.status,locations.location, count(*) as num
            FROM ncr LEFT JOIN locations ON ncr.factory=locations.id
            WHERE ncr.status <> 'CLOSED' AND ncr.factory>0
            $WHERE
            GROUP BY location,status
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get ncrs by vendor
     *
     * @param        $vendorid
     * @param        $erp
     * @param string $options
     *
     * @return array
     */
    public static function byVendorERP($vendorid, $erp, $options = '')
    {
        $WHERE ='';
        //by default only return the OPEN ones
        if ($vendorid !== 'ALL' && $erp !== 'ALL') {
            $WHERE = " WHERE ncr.vendor_id='$vendorid' AND ncr.vendor_erp='$erp'";
        }

        if ($options['status'] === 'ALL') {
            $WHERE = '';
        } else {
            $WHERE .= " AND ncr.status<>'CLOSED'";
        }
        $query = <<<EOF
            SELECT ncr.*, YEAR(ncr.date) AS ncr_year,locations.location,
            CONCAT(people.lastname, ', ', people.firstname) AS reported_by
            FROM ncr LEFT JOIN locations ON ncr.factory=locations.id
            LEFT JOIN people ON people.id = ncr.t_emno
            $WHERE
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get all NCRs that still need to be back charged
     */
    public static function byBackCharged()
    {
        $query = <<<EOF
            SELECT ncr.*, YEAR(ncr.date) AS ncr_year, locations.location,
            CONCAT(people.lastname, ', ', people.firstname) AS reported_by
            FROM ncr LEFT JOIN locations ON ncr.factory=locations.id
            LEFT JOIN people ON people.id = ncr.t_emno
            WHERE status<>'CLOSED'
            AND responsible='Supplier'
            AND cost_total>0
            AND t_ninv=''
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get all NCRs by Part Number
     *
     * @param $pn
     *
     * @return array
     */
    public static function byPartNumber($pn)
    {
        $query = <<<EOF
            SELECT DISTINCT ncr.id,
                ncr.*,
                CONCAT(people.lastname, ', ', people.firstname) AS reported_by,
                ncr_parts.part_number AS part_number,
                ncr_parts.qty AS qty,
                YEAR(ncr.date) AS ncr_year,
                locations.location,
                ncr_parts.sn
            FROM ncr 
                LEFT JOIN locations ON ncr.factory=locations.id
                LEFT JOIN people ON people.id = ncr.t_emno,
                ncr_parts
            WHERE ncr.id=ncr_parts.parent_id
            AND ncr_parts.part_number='$pn'
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byPartNumberBySixMonths($pn)
    {
        $query = <<<EOF
            SELECT DISTINCT ncr.id,
                ncr.*,
                CONCAT(people.lastname, ', ', people.firstname) AS reported_by,
                ncr_parts.part_number AS part_number,
                ncr_parts.qty AS qty,
                YEAR(ncr.date) AS ncr_year,
                locations.location,
                ncr_parts.sn
            FROM ncr 
                LEFT JOIN locations ON ncr.factory=locations.id
                LEFT JOIN people ON people.id = ncr.t_emno,
                ncr_parts
            WHERE ncr.id=ncr_parts.parent_id
            AND ncr_parts.part_number='$pn'
            AND ncr.date >=DATE_SUB(now() , INTERVAL 6 MONTH)
            ORDER BY ncr.date DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get NCRs by query
     *
     * @param        $params
     * @param string $sort
     *
     * @return array array of db rows
     */
    public static function byQuery($params, $sort = '', array $options = [])
    {
        $sortBy = $sort ? "ORDER BY $sort DESC" : '';

        $GROUPBY = isset($options['groupBy']) ? " GROUP BY {$options['groupBy']} " : '';
        $parms = tldUtils::constructWhere($params);
        $WHERE = $parms ? " WHERE $parms" : '';

        $query = <<<EOF
            SELECT ncr.*, YEAR(ncr.date) AS ncr_year, locations.location,
                   ncrp.part_number AS pn,
                   ncrp.qty AS qty,
                   CONCAT(people.lastname, ', ', people.firstname) AS reported_by
            FROM ncr LEFT JOIN locations ON ncr.factory=locations.id
                     LEFT JOIN ncr_parts AS ncrp ON ncr.id=ncrp.parent_id
                     LEFT JOIN people ON people.id = ncr.t_emno
            $WHERE
            $GROUPBY
            $sortBy
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function bySearch($id)
    {
        if (empty($id)) {
            return;
        }
        $query = <<<EOF
            SELECT DISTINCT ncr.id,
                ncr.*,
                CONCAT(people.lastname, ', ', people.firstname) AS reported_by,
                YEAR(ncr.date) AS ncr_year,
                locations.location,
                ncrp.part_number AS pn,
                ncrp.qty AS qty
            FROM ncr
                LEFT JOIN locations ON ncr.factory=locations.id
                LEFT JOIN ncr_parts AS ncrp ON ncr.id=ncrp.parent_id
                LEFT JOIN people ON people.id = ncr.t_emno
            WHERE
                ncr.id='$id'
                OR ncr.problem like '%$id%'
                OR ncr.solution like '%$id%'
                OR ncr.vendor_name like '%$id%'
                OR ncr.cost_ref like '%$id%'
                OR ncr.investigation like '%$id%'
                OR ncrp.part_number like '%$id%'
                OR ncrp.short_desc like '%$id%'
                OR ncrp.ref_type like '%$id%'
                OR ncrp.ref like '%$id%'
                OR ncrp.sn like '%$id%'
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get most recent NCRs
     *
     * By default, returns only last 10
     *
     * @param int $num Max number of rows to return, default 10
     *
     * @return array Array of db rows
     */
    public static function byLatest($num = 10)
    {
        $query = <<<EOF
            SELECT DISTINCT ncr.id, ncr.*,
            CONCAT(people.lastname, ', ', people.firstname) AS reported_by,
            locations.location
            FROM ncr LEFT JOIN locations ON ncr.factory=locations.id
            LEFT JOIN people ON people.id = ncr.t_emno
            ORDER BY id DESC
            LIMIT $num
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function topTenSuppliers($factory, $start, $end, $num = 10)
    {
        $query = <<<EOF
            SELECT vendor_id, vendor_name, count(*) AS quantity
            FROM ncr 
            WHERE date BETWEEN '$start' AND '{$end} 23:59:59' AND factory = $factory AND vendor_id<>"" AND responsible<>"TLD"
            GROUP BY vendor_id
            ORDER BY quantity DESC
            LIMIT $num
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function topTenPN($factory, $start, $end, $num = 10)
    {
        $query = <<<EOF
            SELECT 
            parts.part_number AS pn,
            parts.short_desc AS pn_desc,
            COUNT(*) AS qty
            FROM ncr
            LEFT JOIN ncr_parts AS parts ON ncr.id = parts.parent_id
            WHERE date BETWEEN '$start' AND '{$end} 23:59:59' AND factory = $factory AND parts.part_number<>"" AND responsible<>"TLD"
            GROUP BY parts.part_number
            ORDER BY qty DESC
            LIMIT $num
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getAll($factory, $start, $end, $status= '', $process='')
    {
        $WHERE = !empty($status) ? " AND ncr.status LIKE '$status' " : '';
        $WHERE .= !empty($process) ? " AND ncr.process LIKE '$process' " : '';
        $query = <<<EOF
            SELECT DATE_FORMAT(log.date, '%Y-%m-%d' ) as closed_date, datediff(log.date,ncr.date) as turn_around,
            ncr.*,
            CONCAT(people.lastname, ', ', people.firstname) AS reported_by,
            loc1.location AS factory_name,
            parts.part_number AS pn,
            parts.qty AS qty,
            parts.short_desc AS pn_desc,
            parts.ref_type AS ref_type,
            parts.ref AS ref,
            parts.sn AS sn,
            IF(ncr.vendor_erp = loc1.erp, loc1.location, ncr.vendor_erp) AS erp_name,
            (SELECT GROUP_CONCAT(DISTINCT model SEPARATOR ',') FROM mod_models WHERE parent_id=ncr.id AND module='NCR' ) AS models,
            DATE_FORMAT( ncr.date, '%Y-%m-%d' ) AS clean_date
            FROM ncr
            LEFT JOIN locations AS loc1 ON ncr.factory=loc1.id
            LEFT JOIN people ON people.id = ncr.t_emno
            LEFT JOIN mod_logs AS log ON log.parent_id=ncr.id AND log.id=(select id from mod_logs where parent_id = log.parent_id  and module like 'NCR' ORDER BY date desc limit 1) AND ncr.status LIKE 'CLOSED' AND log.module LIKE 'NCR'
            LEFT JOIN ncr_parts AS parts ON ncr.id = parts.parent_id
            WHERE ncr.date BETWEEN '$start' AND date_add('$end', interval 1 day) AND ncr.factory = $factory
            $WHERE
            ORDER BY date
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getAllMergePN($factory, $start, $end, $status='',$process='')
    {
        $WHERE = !empty($status) ? " AND ncr.status LIKE '$status'" : '';
        $WHERE .= !empty($process) ? " AND ncr.process LIKE '$process'" : '';
        $query = <<<EOF
            SELECT ncr.*,
            CONCAT(people.lastname, ', ', people.firstname) AS reported_by,
            loc1.location AS factory_name,
            group_concat(parts.part_number) AS pn,
            (SELECT GROUP_CONCAT(DISTINCT model SEPARATOR ',') FROM mod_models WHERE parent_id=ncr.id AND module='NCR' ) AS models,
            IF(ncr.vendor_erp = loc1.erp, loc1.location, ncr.vendor_erp) AS erp_name,
            DATE_FORMAT( date, '%Y-%m-%d' ) AS clean_date
            FROM ncr
            LEFT JOIN locations AS loc1 ON ncr.factory=loc1.id
            LEFT JOIN people ON people.id = ncr.t_emno
            LEFT JOIN ncr_parts AS parts ON ncr.id = parts.parent_id
            WHERE ncr.date BETWEEN '$start' AND date_add('$end', interval 1 day) AND ncr.factory = $factory
            $WHERE
            GROUP BY id
            ORDER BY date
EOF;


        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byDeptByPeriod($factory, $dept, $start, $end)
    {
        $WHERE = !empty($dept) ? " AND dept.id = $dept" : '';

        $query = <<<EOF
            SELECT ncr.*,loc1.location,
            CONCAT(people.lastname, ', ', people.firstname) AS reported_by,
            group_concat(parts.part_number) AS pn,
            (SELECT GROUP_CONCAT(DISTINCT model SEPARATOR ',') FROM mod_models WHERE parent_id=ncr.id AND module='NCR' ) AS models,
            DATE_FORMAT( date, '%Y-%m-%d' ) AS clean_date
            FROM ncr
            LEFT JOIN locations AS loc1 ON ncr.factory=loc1.id
            LEFT JOIN people ON people.id = ncr.t_emno
            LEFT JOIN tld_departments AS dept ON people.dpt_id =dept.id
            LEFT JOIN ncr_parts AS parts ON ncr.id = parts.parent_id
            WHERE ncr.date BETWEEN '$start' AND date_add('$end', interval 1 day) AND ncr.factory = $factory
            $WHERE
            GROUP BY id
            ORDER BY date
EOF;

    return tldUtils::getSqlToAssocArray($query);
    }

    public static function countStatsByConstraints($a)
    {
        if (empty($a)) {
            return;
        }
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        $query = <<<EOF
SELECT
    ROUND(SUM(cost_total)/1000,2) AS total_cost_k,
    COUNT(id) AS total_ncr
FROM ncr
WHERE $WHERE
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public static function countStatsByPeriodByConstraints($start, $end, $constraint)
    {
        if (empty($start) || empty($end) || empty($constraint)) {
            return;
        }
        $a = " date BETWEEN '$start' AND '$end' ";
        if (is_array($constraint)) {
            $a .= ' AND ' .tldUtils::constructWhere($constraint);
        } else {
            $a .= ' AND ' .$constraint;
        }

        return self::countStatsByConstraints($a);
    }

    /**
     * Get NCR performance stats by period by constraints
     *
     * @param int    $buid
     * @param date   $start
     * @param date   $end
     * @param string $mode
     * @param array  $constraints
     *
     * @return row
     *
     * # MODE and constraints associated
     * IF $mode = "byBuyer"
     * ---> $constraints = array('uid'=>USER_ID);
     * IF $mode = "bySupplier"
     * ---> $constraints = array('suno'=>SUPPLIER_ID);
     */
    public static function getPerfStatsByERPByPeriodByConstraints($buid, $start, $end, $mode = null, $constraints = null)
    {
        if (empty($buid) || empty($start) || empty($end)) {
            return;
        }
        $bu = new tldLocation($buid);
        $erp = $bu->getERP();
        // 1 - Generate constraints
        switch ($mode) {
            case 'byBuyer':
                $buyer = new tldUser($constraints['uid']);
                $buyer_id = $buyer->getID();
                $buyer_email = $buyer->getEmail();
                $NCR_CONSTRAINTS = <<<EOF
vendor_erp=$erp AND vendor_id IN (SELECT DISTINCT(t_suno) FROM vendors_suno
WHERE tld_rep_id=$buyer_id AND erp=$erp)
EOF;
                $PO_DEL_CONSTRAINTS = <<<EOF
(SELECT RTRIM(t6.t_info) FROM ttccom001$erp AS t6 WHERE t6.t_emno=T2.t_ccon)='$buyer_email'
EOF;
                $PO_INV_CONSTRAINTS = $PO_DEL_CONSTRAINTS;
                break;
            case 'bySuno':
                $NCR_CONSTRAINTS = "vendor_erp=$erp AND vendor_id='{$constraints['suno']}'";
                $PO_DEL_CONSTRAINTS = "T1.t_suno='{$constraints['suno']}'";
                $PO_INV_CONSTRAINTS = $PO_DEL_CONSTRAINTS;
                break;
            case 'byItem':
                $NCR_CONSTRAINTS = <<<EOF
(SELECT COUNT(*) FROM ncr_parts
    WHERE ncr_parts.parent_id=ncr.id
    AND ncr_parts.part_number='{$constraints['pn']}'
)>0 AND vendor_erp=$erp
EOF;
                $PO_DEL_CONSTRAINTS = <<<EOF
(SELECT COUNT(*) FROM ttdpur041$erp AS polines
    WHERE T1.t_pono=polines.t_pono AND T1.t_orno=polines.t_orno
    AND polines.t_item='{$constraints['pn']}'
)>0
EOF;
                $PO_INV_CONSTRAINTS = $PO_DEL_CONSTRAINTS;
                break;
            case 'byERP':
            default:
                $PO_INV_CONSTRAINTS = null;
                $PO_DEL_CONSTRAINTS = null;
                $NCR_CONSTRAINTS = "vendor_erp=$erp";
                break;
        }
        // 2 - Get stats
        // Get NCR stats
        $ncr_stats = self::countStatsByPeriodByConstraints(
            $start,
            $end,
            $NCR_CONSTRAINTS
        );
        // Get PO Invoice stats
        $po_invoice_stats = tldPOL::countInvoiceStatsByPeriodInvoicedByConstraints(
            $bu->getERP(),
            $start,
            $end,
            $PO_INV_CONSTRAINTS
        );
        // Get PO Invoice stats
        $po_delivery_stats = tldPOL::countDeliveryStatsByPeriodDeliveredByConstraints(
            $bu->getERP(),
            $start,
            $end,
            $PO_DEL_CONSTRAINTS
        );
        // 3 - Calculate
        $result = [];
        // Calculate cost ratio
        if ($po_invoice_stats['total_invoiced_k'] == 0 || $ncr_stats['total_cost_k'] == 0) {
            $result['cost_ratio'] = 0;
        } else {
            $result['cost_ratio'] = round(
                ($ncr_stats['total_cost_k'] * 100) / $po_invoice_stats['total_invoiced_k'],
                2
            );
        }
        // Calculate frequency ratio
        if ($po_delivery_stats['total_qty_delivery'] == 0 || $ncr_stats['total_ncr'] == 0) {
            $result['freq_ratio'] = 0;
        } else {
            $result['freq_ratio'] = round(
                ($ncr_stats['total_ncr'] * 100) / $po_delivery_stats['total_qty_delivery'],
                2
            );
        }

        return $result;
    }

    /**
     * Get NCRs cost Statistics
     *
     * @param int $erp
     * @param int $vendorid
     * @param int $opts to set the range of time
     *
     * @return array Array of db rows
     */
    public static function getCostStatsRatioByVendor($erp, $vendorid, $opts = null)
    {
        if (empty($erp) || empty($vendorid)) {
            return [];
        }
        $erp = TldDatabase::escape($erp);
        $vendorid = TldDatabase::escape($vendorid);
        // If request for a specific date range
        if (!empty($opts['ds']) && !empty($opts['ds'])) {
            $CONSTRAINT_BAAN = " AND t3.t_pdat BETWEEN '{$opts['ds']}' AND '{$opts['de']}' ";
            $CONSTRAINT_MYSQL = " AND date BETWEEN '{$opts['ds']}' AND '{$opts['de']}' ";
            if (!empty($opts['pods']) && !empty($opts['pods'])) {
                $CONSTRAINT_BAAN = " AND t3.t_pdat BETWEEN '{$opts['pods']}' AND '{$opts['pode']}' ";
            }
        } else {
            $CONSTRAINT_BAAN = ' AND DATEDIFF(month, t3.t_pdat, GETDATE()) <= 12 ';
            $CONSTRAINT_MYSQL = " AND period_diff( date_format( NOW(),'%Y%m') , date_format(date,'%Y%m') ) <= 12 ";
        }
        // Get po stats
        $query = <<<EOF
            SELECT
                CONVERT(decimal(9,2),ROUND(SUM(t3.t_qana*t1.t_pric)/1000,2)) AS total_invoiced
            FROM
                ttdpur041$erp as t1
                LEFT JOIN ttdpur045$erp as t2 ON t1.t_orno=t2.t_orno AND t1.t_pono=t2.t_pono
                LEFT JOIN ttdpur046$erp as t3 ON t2.t_orno=t3.t_orno AND t2.t_pono=t3.t_pono
            WHERE
                t3.t_suno = '$vendorid'
                $CONSTRAINT_BAAN
EOF;
        $po = tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
        // Get NCR stats
        $query = <<<EOF
            SELECT
                ROUND(SUM(cost_total)/1000,2) AS total_cost
            FROM ncr
            WHERE
                vendor_erp=$erp
                AND vendor_id LIKE trim('$vendorid')
                $CONSTRAINT_MYSQL
EOF;
        $ncr = tldUtils::getSqlRowToAssocArray($query);
        // Calculate
        if ($po['total_invoiced'] == 0 || $ncr['total_cost'] == 0) {
            return 0;
        }

        return round(($ncr['total_cost'] * 100) / $po['total_invoiced'], 2);
    }

    /**
     * Get NCRs cost Statistics
     *
     * @param int $erp
     * @param int $vendorid
     * @param int $opts to set the range of time
     *
     * @return array Array of db rows
     */
    public static function getFrequencyStatsRatioByVendor($erp, $vendorid, $opts = null)
    {
        if (empty($erp) || empty($vendorid)) {
            return [];
        }
        $erp = TldDatabase::escape($erp);
        $vendorid = TldDatabase::escape($vendorid);
        // If request for a specific date range
        if (!empty($opts['ds']) && !empty($opts['ds'])) {
            $CONSTRAINT_BAAN = " AND t2.t_date BETWEEN '{$opts['ds']}' AND '{$opts['de']}' ";
            $CONSTRAINT_MYSQL = " AND date BETWEEN '{$opts['ds']}' AND '{$opts['de']}' ";
            if (!empty($opts['pods']) && !empty($opts['pods'])) {
                $CONSTRAINT_BAAN = " AND t2.t_date BETWEEN '{$opts['pods']}' AND '{$opts['pode']}' ";
            }
        } else {
            $CONSTRAINT_BAAN = ' AND DATEDIFF(month, t2.t_date, GETDATE()) <= 12 ';
            $CONSTRAINT_MYSQL = " AND period_diff( date_format( NOW(),'%Y%m') , date_format(date,'%Y%m') ) <= 12 ";
        }
        // Get po stats
        $query = <<<EOF
            SELECT
                COUNT(t2.t_dqua) AS total_delivery
            FROM
                ttdpur041$erp as t1
                LEFT JOIN ttdpur045$erp as t2 ON t1.t_orno=t2.t_orno AND t1.t_pono=t2.t_pono
            WHERE
                t2.t_suno = '$vendorid'
                AND t2.t_srnb<>0
                $CONSTRAINT_BAAN
EOF;
        $po = tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
        // Get NCR stats
        $query = <<<EOF
            SELECT
                COUNT(id) AS total_ncr
            FROM ncr
            WHERE
                vendor_erp=$erp
                AND vendor_id LIKE trim('$vendorid')
                $CONSTRAINT_MYSQL
EOF;
        $ncr = tldUtils::getSqlRowToAssocArray($query);
        // Calculate
        if ($po['total_delivery'] == 0 || $ncr['total_ncr'] == 0) {
            return 0;
        }

        return round(($ncr['total_ncr'] * 100) / $po['total_delivery'], 2);
    }

    public function notify($to, $subject, $body, $cc = '', $opt = '')
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getPrintVersion()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

}

/**
 *Creates an NCR object from data already stored in database
 *<code>
 *CREATE TABLE `ncr_parts` (
 *  `id` int(11) NOT NULL auto_increment,
 *  `parent_id` int(11) NOT NULL default '0',
 *  `part_number` varchar(20) NOT NULL default '',
 *  `short_desc` varchar(50) NOT NULL default '',
 *  `qty` int(11) NOT NULL default '0',
 *  `ref_type` varchar(10) NOT NULL default '',
 *  `ref` varchar(20) NOT NULL default '',
 *  `sn` varchar(20) NOT NULL default '',
 *  PRIMARY KEY  (`id`)
 *) TYPE=MyISAM
 *</code>
 *
 * @package Quality
 */
class tldNCRPart
{
    public static function byParent($id)
    {
        $query = <<<EOF
            SELECT *
            FROM ncr_parts
            WHERE parent_id=$id
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getAll(): array
    {
        $query = <<<EOF
        SELECT ncr.date,
            factory.location AS factory_fullname,
            (SELECT GROUP_CONCAT(DISTINCT model SEPARATOR ',')
            FROM mod_models WHERE parent_id=ncr.id AND module='NCR'
            ) AS models,
            ncr_parts.*
        FROM ncr
            JOIN ncr_parts ON ncr.id=ncr_parts.parent_id
            LEFT JOIN locations AS factory ON ncr.factory=factory.id
        ORDER BY part_number
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }
}

/**
 * Class for creating and manipulating CPA documents
 *
 * @package Quality
 */
class tldCPA
{
    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    public function getID()
    {
        return $this->itsID;
    }

    public static function getSELECT(): string
    {
        return <<<EOF
SELECT 
    cpa.*, 
    locations.location as bu_fullname,
    CONCAT(poster.firstname, ' ', poster.lastname) AS poster_fullname,
    CONCAT(initiator.firstname, ' ', initiator.lastname) AS initiator_fullname,
    initiator.email AS initiator_email,
    CONCAT(proj_leader.firstname, ' ', proj_leader.lastname) AS proj_leader_fullname,
    proj_leader.email AS proj_leader_email,
    IF(cpa.status='SUSPENDED',
        ROUND(POW(1.4678, PERIOD_DIFF(DATE_FORMAT(DATE_SUB(date_suspended, INTERVAL days_suspended DAY), '%Y%m'),DATE_FORMAT(date, '%Y%m'))), 0),
        ROUND(POW(1.4678, PERIOD_DIFF(DATE_FORMAT(DATE_SUB(now(), INTERVAL days_suspended DAY), '%Y%m'),DATE_FORMAT(date, '%Y%m'))), 0)
    ) AS dfactor,
    IF(cpa.status='SUSPENDED',
        PERIOD_DIFF(DATE_FORMAT(DATE_SUB(date_suspended, INTERVAL days_suspended DAY), '%Y%m'), DATE_FORMAT(date, '%Y%m')),
        PERIOD_DIFF(DATE_FORMAT(DATE_SUB(now(), INTERVAL days_suspended DAY), '%Y%m'), DATE_FORMAT(date, '%Y%m'))
    ) AS monthsOpen,
    CASE
    WHEN cpa.status='CLOSED'
        THEN final_fweight
    WHEN cpa.status='SUSPENDED'
        THEN ROUND(ifactor * POW(1.4678, PERIOD_DIFF(DATE_FORMAT(DATE_SUB(date_suspended, INTERVAL days_suspended DAY), '%Y%m'),DATE_FORMAT(date, '%Y%m'))), 0)
    ELSE
        ROUND(ifactor * POW(1.4678, PERIOD_DIFF(DATE_FORMAT(DATE_SUB(now(), INTERVAL days_suspended DAY), '%Y%m'),DATE_FORMAT(date, '%Y%m'))), 0)
    END AS fweight
EOF;
    }

    public static function getFROM(): string
    {
        return <<<EOF
FROM 
    cpa
    LEFT JOIN locations ON cpa.bu=locations.id
    LEFT JOIN people AS initiator ON initiator.id=cpa.initiator
    LEFT JOIN people AS poster ON poster.id=cpa.poster
    LEFT JOIN people AS proj_leader ON proj_leader.id=cpa.proj_leader
EOF;
    }

    /**
     * Get CPA header information
     *
     * @return array
     */
    public function getHeader()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = "$SELECT $FROM WHERE cpa.id=$this->itsID";

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Is the CPA empty?
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    /**
     * Get list of attached files
     *
     * @return array Array of db rows of all files if no fileid set, array of info on file
     * specified by fileid
     *
     */
    public function getFiles()
    {
        return tldModFile::byParent($this->itsID, 'CPA');
    }

    public function getFactoryID()
    {
        return $this->itsHeader['bu'];
    }

    public function getFactoryERP()
    {
        return tldLocation::getERPByID($this->itsHeader['bu']);
    }

    public function getFactoryName()
    {
        return $this->itsHeader['bu_fullname'];
    }

    public function getShortDesc()
    {
        return $this->itsHeader['short_desc'];
    }

    public function getStatus()
    {
        return $this->itsHeader['status'];
    }

    public function getFileName()
    {
        return $this->itsHeader['picture_filename'];
    }

    public function getFilePath()
    {
        return self::getPathToUploadFile($this->getFileName());
    }

    public function getIFactor()
    {
        return $this->itsHeader['ifactor'];
    }

    public static function getIFactorList(): array
    {
        return ['1' => '1', '10' => '10', '100' => '100', '1000' => '1000'];
    }

    public function getDFactor()
    {
        return $this->itsHeader['dfactor'];
    }

    public function getFocusWeight()
    {
        return $this->itsHeader['fweight'];
    }

    public function getFinalFocusWeight()
    {
        return $this->itsHeader['final_fweight'];
    }

    public function getLastStatus()
    {
        return $this->itsHeader['last_status'];
    }

    public function getProjectLeaderID()
    {
        return $this->itsHeader['proj_leader'];
    }

    public function getProjectLeaderFullname()
    {
        return $this->itsHeader['proj_leader_fullname'];
    }

    public function getProjectLeaderEmail()
    {
        return $this->itsHeader['proj_leader_email'];
    }

    public function getPosterID()
    {
        return $this->itsHeader['poster'];
    }

    public function getPosterFullname()
    {
        return $this->itsHeader['poster_fullname'];
    }

    public function getInitiatorID()
    {
        return $this->itsHeader['initiator'];
    }

    public function getInitiatorFullname()
    {
        return $this->itsHeader['initiator_fullname'];
    }

    public function getInitiatorEmail()
    {
        return $this->itsHeader['initiator_email'];
    }

    public function getPrintVersion()
    {
        $report = new tldAssocTable(
            $this->getHeader(),
            [
                'id'                   => 'CPA#',
                'date'                 => 'Date',
                'poster_fullname'      => 'Poster',
                'initiator_fullname'   => 'Initiator',
                'proj_leader_fullname' => 'Project Leader',
                'date_target'          => 'Target Date',
                'ifactor'              => 'IF',
                'status'               => 'Status',
                'bu_fullname'          => 'Factory',
                'dept'                 => 'Department',
                'type'                 => 'Type',
                'short_desc'           => 'Short description',
                'description'          => 'Description',
            ],
            [
                'title' => "CPA#{$this->itsID} Details",
                'links' => [
                    'id' => 'https://www.tld-gse.com/en/private/manufacturing/qa/dev.php?m[0]=cpa&m[1]=view&id=',
                ],
            ]
        );

        return $report->fetch();
    }

    public function getTasks()
    {
        return tldTask::byParent($this->itsID, 'CPA', 'ALL');
    }

    public function getStatusTasksByConstraints($a, $opt = [])
    {
        $SELECT = tldTask::getSELECT();
        $FROM = tldTask::getFROM();
        $HAVING = is_array($a) ? tldUtils::constructWhere($a) : $a;
        $HAVING = !empty($HAVING) ? "HAVING $HAVING" : '';
        $GROUPBY = !empty($opt['groupBy']) ? "GROUP BY {$opt['groupBy']}" : '';

        $ORDERBY = 'ORDER BY cpa_status_order ASC, tasks.status DESC';
        if (!empty($opt['orderBy'])) {
            $ORDERBY = "ORDER BY {$opt['orderBy']}";
        }
        // Query tasks
        $query = <<<EOF
$SELECT,
    mk.type AS cpa_status,
    mk.key1 AS cpa_status_order
$FROM
    LEFT JOIN mod_keys AS mk ON mk.parent_id=tasks.id AND mk.module LIKE 'TASK'
WHERE
    tasks.module LIKE 'CPA'
    AND tasks.parent_id=$this->itsID
$GROUPBY
$HAVING
$ORDERBY
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function getLinksFromHere($type = ''): array
    {
        return tldModLink::byParent($this->itsID, 'CPA', $type);
    }

    public function getLinksToHere($module = ''): array
    {
        return tldModLink::byItem($this->itsID, 'CPA', $module);
    }

    public function getFollowers(): array
    {
        return tldModMember::byParent($this->itsID, 'CPA');
    }

    public function refresh()
    {
        $this->itsHeader = $this->getHeader();
    }

    public function isClosed(): bool
    {
        return $this->getStatus() === 'CLOSED';
    }

    public static function getPathToUploadFile($filename = ''): string
    {
        return tldUtils::getPathToUploadFile('cpa', $filename);
    }

    public static function insertHeader($p)
    {
        $fields = [
            'dept',
            'bu',
            'proj_leader',
            'type',
            'last_status',
            'date_target',
            'date_closed',
            'date_suspended',
            'days_suspended',
            'short_desc',
            'description',
            'resolution',
            'rejection_reason',
            'picture_filename',
            'ifactor',
            'final_fweight',
            'poster',
            'initiator',
        ];
        //set defaults
        $query = <<<EOF
        INSERT INTO cpa
        SET date=NOW(), status='PENDING',
EOF;
        $query .= tldUtils::getSqlSet(tldUtils::escapeSQL($p), $fields);

        return tldUtils::sqlInsert($query);
    }

    public function update($a, $fields = '')
    {
        if (empty($a)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "UPDATE cpa SET $SET WHERE id=$this->itsID";

        return tldUtils::sqlQuery($query);
    }

    public function delete()
    {
        $query = "DELETE FROM cpa WHERE id=$this->itsID LIMIT 1";

        return tldUtils::sqlExecute($query);
    }

    public function addModList($a)
    {
        return tldModList::insert(
            [
                'parent_id' => $this->getID(),
                'module'    => 'CPA',
                'list_name' => $a['list_name'],
                'list_key'  => $a['list_key'],
                'list_key2' => $a['list_key2'],
                'value'     => $a['value'],
                'value2'    => $a['value2'],
            ]
        );
    }

    public function getListsByListName($listName): array
    {
        return tldModList::byConstraints(
            [
                'parent_id' => $this->getID(),
                'module'    => 'CPA',
                'list_name' => $listName,
            ]
        );
    }

    /**
     * Save data form submitted
     *
     * @param string $formName
     * @param array  $formData
     *
     * @return string on error
     */
    public function saveFormData($formName, $formData)
    {
        $encodedData = base64_encode(serialize($formData));
        // check if not already created
        $rows = $this->getListsByListName($formName);
        $list = new tldModList((int)$rows[0]['id']);
        // if found, Update
        if (!$list->isEmpty() && $list->getModule() === 'CPA'
            && $list->getParentID() == $this->getID() && $list->getListName() == $formName
        ) {
            $e = $list->update(['value2' => $encodedData]);
        } // else create
        else {
            $e = $this->addModList(
                [
                    'list_name' => $formName,
                    'value2'    => $encodedData,
                ]
            );
        }

        return $e;
    }

    public function getFormDataByName($formName)
    {
        $rows = $this->getListsByListName($formName);

        return (!empty($rows[0]['value2'])) ? unserialize(base64_decode($rows[0]['value2'])) : [];
    }

    public static function getStatusList()
    {
        $statusList = ['PENDING', 'INVESTIGATION', 'ACTION', 'CLOSED', 'SUSPENDED', 'REJECTED'];

        return array_combine($statusList, $statusList);
    }

    public static function getOpenStatusList()
    {
        $statusList = ['PENDING', 'INVESTIGATION', 'ACTION'];

        return array_combine($statusList, $statusList);
    }

    public function isAllowedStatus($uid, $newStatus)
    {
        // Check status validity ------------->
        if (!in_array($newStatus, $this->getAllowedStatus())) {
            return "Status $newStatus not applicable to this module";
        }
        // Check rules ------------->
        // -- Look if same status
        if ($this->getStatus() == $newStatus) {
            return true;
        }
        // -- Look for key data and user roles
        $user = new tldUser($uid);
        switch ($newStatus) {
            case 'INVESTIGATION':
                if (!$user->isInGroup(
                        ['role_QAM', "role_SAM", 'role_QA', 'role_CSD', 'role_GTD', 'role_CIO', 'role_CMO', 'role_CPO', 'role_COO','role_RCOO', 'role_CEO', 'role_PM', 'role_MPE', 'role_MLM', 'role_EM']
                    ) && !$user->isInGroupLevel('role_CFO', 900)
                ) {
                    return 'You do not have permission';
                }
                break;
            case 'ACTION':
                if (!$user->isInGroup(
                        ['role_QAM', "role_SAM", 'role_QA', 'role_COO', 'role_RCOO', 'role_CEO', 'role_CSD', 'role_GTD', 'role_CIO', 'role_CMO', 'role_CPO', 'role_PM', 'role_MPE', 'role_MLM', 'role_EM']
                    ) && !$user->isInGroupLevel('role_CFO', 900)
                ) {
                    return 'You do not have permission';
                }
                if (empty($this->itsHeader['root_cause'])) {
                    return 'Final root cause not set';
                }
                break;
            case 'CLOSED':
                // Exceptional case, can closed directly from PENDING in case of duplicated
                if ($this->getStatus() === 'PENDING') {
                    return true;
                }
                switch ($this->getIFactor()) {
                    case '1':
                    case '10':
                        if (!$user->isInGroup(
                                [
                                    'role_QAM',
                                    "role_SAM",
                                    'role_QA',
                                    'role_COO',
                                    'role_CEO',
                                    'role_CSD',
                                    'role_GTD',
                                    'role_CIO',
                                    'role_CMO',
                                    'role_CPO',
                                    'role_PM',
                                    'role_MPE',
                                    'role_MLM',
                                    'role_EM'
                                ]
                            ) && !$user->isInGroupLevel('role_CFO', 900)
                        ) {
                            return 'You do not have permission';
                        }
                        break;
                    case '100':
                        if (!$user->isInGroupLevel('role_COO', $this->getFactoryERP()) && !$user->isInGroupLevel(
                                'role_CEO',
                                $this->getFactoryERP()
                            ) && !$user->isInGroup(
                                ['role_CSD', 'role_GTD', 'role_CIO', 'role_CMO', 'role_CPO', 'role_PM', 'role_MPE', 'role_MLM', 'role_EM', 'role_QAM', "role_SAM", 'role_QA',]
                            ) && !$user->isInGroupLevel('role_CFO', 900)
                        ) {
                            return 'You do not have permission';
                        }
                        break;
                    case '1000':
                        if (!$user->isInGroupLevel('role_RCOO', $this->getFactoryERP()) && !$user->isInGroupLevel('role_CEO', $this->getFactoryERP()) && !$user->isInGroup(
                                ['role_CSD', 'role_GTD', 'role_CIO', 'role_CMO', 'role_CPO']
                            ) && !$user->isInGroupLevel('role_CFO', 900)
                        ) {
                            return 'You do not have permission';
                        }
                        break;
                }
                if (empty($this->itsHeader['corrective_action'])) {
                    return 'Corrective action not set';
                }
                if (empty($this->itsHeader['preventive_action'])) {
                    return 'Preventive action not set';
                }
                break;
        }
        // -- Look for tasks still open
        switch ($newStatus) {
            case 'SUSPENDED':
            case 'PENDING':
            case 'INVESTIGATION':
                // No problem
                break;
            case 'REJECTED':
            case 'CLOSED':
                // Check ALL tasks
                $tasks = $this->getTasks();
                if (!count($tasks) > 0) {
                    break;
                }
                foreach ($tasks as $task) {
                    if ($task['status'] !== 'CLOSED') {
                        return 'All tasks must be closed';
                    }
                }
                break;
            default:
                // In all other cases, check task linked to the actual CPA status
                $a = "cpa_status LIKE '{$this->getStatus()}' AND status<>'CLOSED'";
                $tasks = $this->getStatusTasksByConstraints($a);
                if (count($tasks) > 0) {
                    return "Tasks for CPA status {$this->getStatus()} are still open";
                }
                break;
        }

        return true;
    }

    public function getAllowedStatus()
    {
        $allowed = [];
        switch ($this->getStatus()) {
            case 'PENDING':
                $allowed = ['PENDING','INVESTIGATION', 'SUSPENDED', 'REJECTED', 'CLOSED'];
                break;
            case 'INVESTIGATION':
                $allowed = ['INVESTIGATION','ACTION', 'SUSPENDED', 'REJECTED'];
                break;
            case 'ACTION':
                $allowed = ['ACTION','CLOSED', 'SUSPENDED', 'REJECTED', 'INVESTIGATION'];
                break;
            case 'SUSPENDED':
                // Get last status
                $lastStatus = $this->getLastStatus();
                if (!empty($lastStatus)) {
                    $allowed[] = $lastStatus;
                }
                // Or REJECT/CLOSED/ACTION/INVESTIGATION
                $allowed = ['ACTION', 'INVESTIGATION', 'CLOSED', 'REJECTED'];
                break;
            case 'REJECTED':
                $allowed = ['PENDING'];
                break;
            case 'CLOSED':
                $allowed = ['PENDING'];
                break;
        }

        return array_combine($allowed, $allowed);
    }

    /**
     * Change status of CPA
     *
     * @param        $uid
     * @param        $newStatus
     * @param array $options
     */
    public function changeStatus($uid, $newStatus, $options = [])
    {
        // Check rules
        if (empty($this->itsID)) {
            return 'ERROR: Cannot change status, CPA ID not set in object.';
        }
        $allowedStatusResponse = $this->isAllowedStatus($uid, $newStatus);
        if (is_string($allowedStatusResponse)) {
            return "Status $newStatus not allowed: $allowedStatusResponse";
        }

        // Change status
        $e = $this->update(['status' => $newStatus]);
        if (is_string($e)) {
            return $e;
        }
        // Record last status
        $this->update(['last_status' => $this->getStatus()]);
        // Log the status change
        $log = "Status changed from {$this->getStatus()} to $newStatus";
        $log = TldDatabase::escape($log);
        $order = ['\r\n', '\n', '\r'];
        $replace = '';
        $reason = str_replace($order, $replace, $options['reason']);
        if (isset($options['reason'])) {
            $log .= "<br>{$options['reason']}";
        }
        $this->addLogEntry($uid, $log);

        // POST actions --->
        switch ($newStatus) {
            case 'PENDING':
            case 'INVESTIGATION':
                if ($this->getStatus() === 'PENDING') {
                    $this->update(
                        [
                            'proj_leader' => $options['proj_leader'],
                            'date_target' => $options['date_target'],
                        ]
                    );
                }
            case 'ACTION':
                // From SUSPENDED to either PENDING or INVESTIGATION or ACTION -> update days_suspended counter
                if ($this->getStatus() === 'SUSPENDED') {
                    $actualSuspendedDate = new DateTime($this->itsHeader['date_suspended']);
                    $today = new DateTime(date('Y-m-d'));
                    $intervalSinceSuspended = $actualSuspendedDate->diff($today);
                    $nbDaysSinceSuspended = $intervalSinceSuspended->format('%a');
                    $this->update(
                        [
                            'date_suspended' => '0000-00-00',
                            'days_suspended' => $nbDaysSinceSuspended + $this->itsHeader['days_suspended'],
                        ]
                    );
                }
                break;
            case 'SUSPENDED':
                // From any OPEN status to SUSPENDED -> set the date_suspended
                if (in_array($this->getStatus(), ['PENDING', 'INVESTIGATION', 'ACTION'])) {
                    $this->update(['date_suspended' => date('Y-m-d')]);
                }
                break;
            case 'CLOSED':
                $this->update(
                    [
                        'final_fweight' => $this->getFocusWeight(),
                        'date_closed'   => date('Y-m-d'),
                        'resolution'    => $options['reason'],
                    ]
                );
                break;
            case 'REJECTED':
                $this->update(
                    [
                        'final_fweight'    => $this->getFocusWeight(),
                        'date_closed'      => date('Y-m-d'),
                        'rejection_reason' => $options['reason'],
                    ]
                );
                break;
        }

        // Notify
        $message = <<<EOF
<p>This is to inform you that the status of CPA#{$this->getID()} changed from <b>{$this->getStatus(
        )}</b> to <b>$newStatus</b>.</p>
<hr>
<p>Last CPA log:<br>{$reason}</p>
EOF;
        $this->refresh(); // Refresh Header
        $CC = $this->getRecipientsByIF();
        // -- look for additional cc in options
        if (count($options['cc'] ?? [])) {
            foreach ($options['cc'] as $email) {
                $CC[] = $email;
            }
        }
        $CC = array_unique($CC);
        // -- notify
        $this->notifyInitiator(
            $message,
            "Status of CPA#$this->itsID is now $newStatus",
            $CC
        );

        return;
    }

    //NOTIFICATION FUNCTIONS
    /**
     * notify initiator and qa people in the erp location
     *
     * @param        $message
     * @param string $subject
     * @param array $cc
     *
     * @return bool
     */
    public function notifyInitiator($message, $subject = '', $cc = [])
    {
        // Initiator
        $initiator = new tldUser($this->itsHeader['initiator']);
        $to[] = $initiator->getEmail();
        // Poster
        $poster = new tldUser($this->itsHeader['poster']);
        $to[] = $poster->getEmail();
        // Quality Manager
        $rows = tldGroup::inGroup('role_QAM', $this->getFactoryERP());
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $cc[] = $row['email'];
            }
        }
        if (empty($subject)) {
            $subject = 'CPA#' .$this->itsID. ' updated';
        }

        //Build and send the email
        return $this->notify(array_unique($to), 'noreply@tld-gse.com', $subject, $message, array_unique($cc));
    }

    public function notify($to, $from, $subject, $message, $cc = '')
    {
        $message .= $this->getPrintVersion();
        $message .= <<<EOF
<p><a href="https://www.tld-gse.com/en/private/manufacturing/qa/dev.php?m[0]=cpa&m[1]=view&id=$this->itsID">
Click here to see CPA#$this->itsID</a></p>
EOF;

        return tldUtils::emailAttachment(
            $to,
            $from,
            $subject,
            $message,
            null,
            $cc
        );
    }

    public function getRecipientsByIF()
    {
        $CC = [];
        if (!empty($this->getProjectLeaderEmail())) {
            $CC[] = $this->getProjectLeaderEmail();
        }
        switch ($this->getIFactor()) {
            case '1000':
                $rceo = new tldGroup('role_CEO', $this->getFactoryERP());
                $CC = array_merge($CC, $rceo->getEmailList());
                $rcoo = new tldGroup('role_RCOO', $this->getFactoryERP());
                $CC = array_merge($CC, $rcoo->getEmailList());
                if ($this->itsHeader['bu'] == 5 && $this->getStatus() === 'CLOSED') {
                    $gcoo = new tldGroup('role_GCOO');
                    $CC = array_merge($CC, $gcoo->getEmailList());
                    $chairman = new tldGroup('role_CHAIRMAN');
                    $CC = array_merge($CC, $chairman->getEmailList());
                }
            case '100':
                $coo = new tldGroup('role_COO', $this->getFactoryERP());
                $CC = array_merge($CC, $coo->getEmailList());
                $cmo = new tldGroup('role_CMO', $this->getFactoryERP());
                $CC = array_merge($CC, $cmo->getEmailList());
                if ($this->itsHeader['bu'] == 5 && $this->getStatus() === 'CLOSED') {
                    $gcoo = new tldGroup('role_GCOO');
                    $CC = array_merge($CC, $gcoo->getEmailList());
                    $chairman = new tldGroup('role_CHAIRMAN');
                    $CC = array_merge($CC, $chairman->getEmailList());
                }
                break;
        }

        return $CC;
    }

    public function addLogEntry($uid, $comment, $num_log = 0)
    {
        return tldModLog::insert(
            [
                'parent_id' => $this->itsID,
                'module'    => 'CPA',
                'poster'    => $uid,
                'comment'   => $comment,
                'log_num'   => $num_log,
            ]
        );
    }

    public function getLog()
    {
        return tldModLog::byParent($this->itsID, 'CPA');
    }

    public function addStatusTask($a, $status = null)
    {
        if (empty($status)) {
            $status = $this->getStatus();
        }
        // Create task
        $taskID = tldTask::insert(
            $this->getID(),
            [
                'erp'      => $this->getFactoryERP(),
                'bu_id'    => $this->getFactoryID(),
                'assignee' => $a['assignee'],
                'assignor' => $a['assignor'],
                'task'     => $a['task'],
                'due_date' => $a['due_date'],
                'escalation_trigger' => $a['escalation_trigger'],
            ],
            'CPA'
        );
        if (is_string($taskID)) {
            return $taskID;
        }
        // Enter mod key to link tasks to status
        // --- get status # to be able to order it
        $statusList = array_keys(self::getStatusList());
        $orderStatus = (int)array_search($status, $statusList);
        // --- create reccrd
        $modkID = tldModKey::insert(
            [
                'parent_id' => $taskID,
                'module'    => 'TASK',
                'type'      => $status,
                'key1'      => $orderStatus,
            ]
        );
        if (is_string($modkID)) {
            error_log("CPA task creation with mod_key error: $modkID");
        }

        return $taskID;
    }

    //static functions
    public static function byLatest($num = 10)
    {
        $query = <<<EOF
        SELECT cpa.*,locations.location
        FROM cpa LEFT JOIN locations ON cpa.bu=locations.id
        ORDER BY cpa.id DESC
        LIMIT $num
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function withoutOpenTask(){
        $query = <<<SQL
        SELECT
          cpa.status,
          cpa.bu,
          locations.location AS factory_fullname,
          COUNT(*) AS num
        FROM
          cpa
            LEFT JOIN locations ON cpa.bu=locations.id
        WHERE (SELECT COUNT(*) FROM tasks WHERE status<>'CLOSED' AND module LIKE 'CPA' AND cpa.id=tasks.parent_id)=0 and status NOT IN('REJECTED','CLOSED')
        GROUP BY
          factory_fullname, status
SQL;

        return tldUtils::getSqlToAssocArray($query);
    }
    /**
     * get count of cpa by erp and status
     *
     * @param int|string $uid ID of user to get records for
     *
     * @return array array of db rows
     */
    public static function countByFactoryStatus($uid = '')
    {
        $WHERE = '';
        if ($uid > 0) {
            $groups = [];
            $erps = [];
            $user = new tldUser($uid);
            if ($levels = $user->isInGroup('role_QAM')) {
                if (is_array($levels)) {
                    $erps = $erps + $levels;
                } else {
                    array_push($erps, $levels);
                }
                array_push($groups, 'IN PROGRESS', 'PENDING', 'SUSPENDED');
            }
            if (!$groups and !$erps) {
                return;
            }
            $where = [];
            if ($groups) {
                $where[] = " status IN('".implode('\',\'', $groups)."')";
            }
            if ($erps) {
                $where[] = ' locations.erp IN (' .implode(',', $erps). ')';
            }
            $WHERE = ' WHERE ' .implode(' AND ', $where);
        }
        $query = <<<EOF
        SELECT status, locations.location, count(*) AS num
        FROM cpa LEFT JOIN locations ON cpa.bu=locations.id
        $WHERE
        GROUP BY location, status
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Count of CPAs by x and y
     *
     * @param        $x
     * @param        $y
     * @param string $options
     *
     * @return array Array of db rows
     */
    public static function countBy($x, $y, $options = '')
    {
        if (empty($x) || empty($y)) {
            return;
        }
        $WHERE = '';
        if (isset($options['byBU'])) {
            $WHERE = " WHERE bu='{$options['byBU']}'";
        }
        if (isset($options['byLocation'])) {
            $WHERE = " WHERE locations.location='{$options['byLocation']}'";
        }
        $query = <<<EOF
        SELECT $x, $y, locations.location, count(*) AS num
        FROM cpa LEFT JOIN locations ON cpa.bu=locations.id
        $WHERE
        GROUP BY $y, $x
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byERPStatus($erp = '', $status = '', $sort = '')
    {
        $where = [];
        if ($erp !== 'ALL') {
            $where[] = " locations.location='$erp'";
        }
        if ($status !== 'ALL') {
            $where[] = " cpa.status='$status'";
        }
        $WHERE = $where ? ' WHERE ' .implode(' AND ', $where) : '';

        $sortBy = ' ORDER BY cpa.id DESC, status DESC';
        if (is_array($sort)) {
            $sortBy = " ORDER BY {$sort['field']}";
            $sortBy .= in_array($sort['dir'], ['ASC', 'DESC']) ? " {$sort['dir']}"  : ' ASC';
        }
        $query = <<<EOF
        SELECT cpa.*, YEAR(cpa.date) AS cpa_year,
            locations.location
        FROM cpa LEFT JOIN locations ON cpa.bu=locations.id
        $WHERE
        $sortBy
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byERPStatusWithoutTask($erp = '', $status = '', $sort = '')
    {
        $where = [];
        if ($erp !== 'ALL') {
            $erp=tldLocation::getIDByLocation($erp);
            $where[] = " locations.id=$erp";
        }
        if ($status !== 'ALL') {
            $where[] = " cpa.status='$status'";
        }
        $WHERE = $where ? ' WHERE ' .implode(' AND ', $where) : '';

        $sortBy = ' ORDER BY cpa.id DESC, status DESC';
        if (is_array($sort)) {
            $sortBy = ' ORDER BY ' .$sort['field'];
            $sortBy .= in_array($sort['dir'], ['ASC', 'DESC']) ? " {$sort['dir']}" : ' ASC';
        }

        $query = <<<EOF
        SELECT cpa.*, YEAR(cpa.date) AS cpa_year,
            locations.location
        FROM cpa LEFT JOIN locations ON cpa.bu=locations.id
        $WHERE
        and (SELECT COUNT(*) FROM tasks WHERE status<>'CLOSED' AND module LIKE 'CPA' AND cpa.id=tasks.parent_id)=0 and cpa.status NOT IN('REJECTED','CLOSED')
        $sortBy
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get CPAs by query
     *
     * @param        $p
     * @param string $sort
     *
     * @return array array of db rows
     */
    public static function byQuery($p, $sort = '')
    {
        $parms = '';
        if ($p[0] !== 'ALL' && $p[1] !== 'ALL') {
            $parms = tldUtils::constructWhere($p);
        }
        $sortBy = $sort ? "ORDER BY $sort" : '';
        $WHERE = $parms ? " WHERE $parms" : '';

        $query = <<<EOF
        SELECT cpa.*, YEAR(cpa.date) AS cpa_year,
            locations.location,concat(people.lastname,', ',people.firstname) as fullname
        FROM cpa 
        LEFT JOIN locations ON cpa.bu=locations.id
        LEFT JOIN people ON cpa.proj_leader=people.id
        $WHERE
        $sortBy
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byConstraints($a)
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        $query = <<<EOF
SELECT
    cpa.*,
    YEAR(cpa.date) AS cpa_year,
    locations.location
FROM cpa
    LEFT JOIN locations ON cpa.bu=locations.id
WHERE
    $WHERE
ORDER BY
    cpa.id
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }
}

/**
 * Class for creating and manipulating SCAR
 *
 * @package Quality
 */
class tldSCAR
{

    public $itsID;
    public $itsHeader;
    const PATH_TO_FILE = 'scar';

    /**
     * @deprecated
     */
    public function __construct($id)
    {
        throw new Exception('This is no longer used');
    }

    /*******************************************
     * GETTERS
     *******************************************/

    /**
     * @deprecated
     */
    public function getID()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getParentID()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getPosterID()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getPosterFullname()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getPosterEmail()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getAssignorEmail()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getLeaderEmail()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getIFactor()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getSupplierBuID()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getSupplierBuERP()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getSupplierBuFullname()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getSupplierRef()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getSupplierName()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getStatus()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getFactoryID()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getFactoryFullname()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getFactoryERP()
    {
        return $this->itsHeader['factory_erp'];
    }

    /**
     * @deprecated
     */
    public function getFileName()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getFilePath()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getShortDescription()
    {
        throw new Exception('This is no longer used');
    }

    /*******************************************
     * CRUD functions
     *******************************************/

    /**
     * @deprecated
     */
    public static function getSELECT(): string
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public static function getFROM(): string
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function refresh()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getHeader()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public static function byConstraints($a, $opt = '')
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public static function insert($a)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function update($a, $fields = '')
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function delete()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function addLogEntry($uid, $comment, $num_log = 0)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getLogs()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function addFollower($uid)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getFollowers()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getTasks()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getFiles($lvl = 0)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function addLinkTo($type, $item)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getLinksFromHere($type = '')
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getLinksToHere($module = '')
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function addPart($pn)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getParts()
    {
        throw new Exception('This is no longer used');
    }

    /*******************************************
     *   STATIC and REFERENCES methods
     ********************************************/

    /**
     * @deprecated
     */
    public static function getPathToFile()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public static function getIFactorList()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public static function getStatusList()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public static function getOpenStatusList()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public static function getClosedStatusList()
    {
        throw new Exception('This is no longer used');
    }

    /*******************************************
     * LOGIC and ACTION methods
     *******************************************/

     /**
      * @deprecated
      */
    public function isEmpty()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function isClosed()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function isCommentAllowed()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function isAllowed($action, $user)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function addComment($a, $file = null)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function notifyComment($a)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getComments($limit = null)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getCommentFiles()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getAllowedStatus()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function isAllowedStatus($uid, $status)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function updateStatus($uid, $newStatus, $opt = '')
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getStatusChangeEmailContentsByStatus($status)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getSupplierContacts()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getFollowerRecipients()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getSupplierRecipients()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getTLDRecipients($notifyceo = true)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function notifySupplier($subject, $body, $cc = [], $notifyceo = true)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function notifyTLD($subject, $body, $notifiyceo=true)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function notify($to, $from, $subject, $body, $cc = '', $bcc = '', $opt = '')
    {
        throw new Exception('This is no longer used');
    }

    /*******************************************
     * STATS and REPORTS methods
     ******************************************
     *
     * @param int    $limit
     * @param string $a
     *
     * @return array
     */

    /**
     * @deprecated
     */
    public static function byLatestByConstraints($limit = 10, $a = '1=1')
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public static function byOpenByPartNumber($pn)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public static function byPartNumber($pn)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public static function byOpenLinkedModule($module, $module_id)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public static function countByFactoryStatusByConstraints($a = null)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public static function countByVendorStatusByConstraints($suno)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public static function countByFactoryStatusBySunoErp($suno, $erp)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public static function countByFactoryStatusBySupplierIdSupplierErp($suno, $erp)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public static function byFactoryStatusByConstraints($factory, $status, $a = null)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public static function byVendorStatusByConstraints($vendor, $status, $a = null)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public static function search($keyword)
    {
        throw new Exception('This is no longer used');
    }

    /*******************************************
     * VIEW methods
     *******************************************/

    /**
     * @deprecated
     */
    public function getPrintVersion()
    {
        throw new Exception('This is no longer used');
    }

}

class tldSCAR_Comment
{

    public $itsID;
    public $itsHeader;
    const UPLOAD_FOLDER = 'scar_comments';

    /**
     * @deprecated
     */
    public function __construct($id)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getID()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getParentID()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getPosterType()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getPosterID()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getPosterEmail()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getPosterFullname()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getComment()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getFileID()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getFileSize()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getFileName()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getFilePath()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function isEmpty()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public static function getSELECT(): string
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public static function getFROM(): string
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getHeader()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public static function byConstraints($a, $opt = '')
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    private static function insert($a)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public static function create($a, $file = null)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function update($a, $fields = '')
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function delete()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public static function byParentID($pid, $opt = null)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function outFile()
    {
        throw new Exception('This is no longer used');
    }

}

/**
 * Class for creating and manipulating CRAB documents
 *
 * @package Quality
 */
class tldCRAB
{

    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    public function getID()
    {
        return $this->itsID;
    }

    public function getFactoryID()
    {
        return $this->itsHeader['buid'];
    }

    public function getDescription()
    {
        return $this->itsHeader['dsca'];
    }

    public function insertPI($a)
    {
        $fields = ['unit', 'comp', 't_cprj', 't_pdno', 't_opno', 't_item', 'model', 'question_id', 'user_id'];
        $query = <<<EOF
INSERT INTO pi_crab_eap
    SET date=NOW(), parent_id=$this->itsID,
EOF;
        $query .= tldUtils::getSqlSet($a, $fields);

        return tldUtils::sqlInsert($query);
    }

    public function getPartNumber()
    {
        return $this->itsHeader['pn'];
    }

    /**
     * Check if the current CRAB object is valid
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    /**
     * Get the header information
     *
     * @return array
     */
    public function getHeader()
    {
        $query = <<<EOF
SELECT crabs.*,
    locations.location AS buid_fullname,
    locations.erp,
    concat(insps.lastname,', ',insps.firstname) AS insp_fullname,
    concat(initiator.lastname, ', ', initiator.firstname) AS initiator_fullname,
    concat(fixer.lastname, ', ', fixer.firstname) AS fixer_fullname,
    lists.list_item AS code_desc,
    er.sn,
    er.model,
    (select mod_logs.comment from mod_logs where mod_logs.module='CRAB' and mod_logs.parent_id=crabs.id and mod_logs.comment LIKE "Inspector's comments:%" LIMIT 1) as inspectlog,
    (select mod_logs.comment from mod_logs where mod_logs.module='CRAB' and mod_logs.parent_id=crabs.id and mod_logs.comment LIKE "Fixed by employee%" LIMIT 1) as fixlog
FROM crabs
    LEFT JOIN people AS insps ON insps.id=crabs.insp_id
    LEFT JOIN people AS initiator ON initiator.id = crabs.init_emno
    LEFT JOIN people AS fixer ON fixer.id = crabs.fix_emno
    LEFT JOIN service AS er ON er.id=crabs.erid
    LEFT JOIN locations ON er.man_location=locations.location
    LEFT JOIN lists ON crabs.code=lists.list_key AND lists.list_name='list.qa.crab.code'
WHERE
  crabs.id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function getStatus()
    {
        return $this->itsHeader['status'];
    }

    public function getERID()
    {
        return $this->itsHeader['erid'];
    }

    public function refresh()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    /**
     * Get associated log entries from mod_log system
     *
     * @return array
     */
    public function getLog()
    {
        return tldModLog::byParent($this->itsID, 'CRAB', [0, 1]);
    }

    public function getFiles($level = 0)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function notifyYT($uid = '', $LANG = '', $charset = '')
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getTasks()
    {
        return tldTask::byParent($this->itsID, 'CRAB', 'ALL');
    }

    public function addLogEntry($id, $comment)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function addLinkTo($type, $item)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getLinksFromHere($type = '')
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getLinksToHere($module = '')
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function insert($p)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function update($data, $fields = '')
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function getSELECT(): string
    {
        return <<<EOF
SELECT
    crabs.*,
    locations.location AS buid_fullname,
    crabs.opno AS code_fullname,
    lists.list_item AS code_desc,
    concat(insps.lastname,', ',insps.firstname) AS insp_fullname,
    concat(initiator.lastname, ', ', initiator.firstname) AS initiator_fullname,
    concat(fixer.lastname, ', ', fixer.firstname) AS fixer_fullname,
    er.sn,
    er.model,
    er.type,
    IF(
       er.model IN('ACE-802-MIL','ACE-814-924','ACE-814-994','ACU-401','ACU-804-MIL','GPU-4000-MIL','MBL-30','PFA-25','PFA-50','PFA-10'),
       'Military',
       'Commerical'
    ) AS product_family,
    (SELECT CONCAT(date,' ', comment) FROM mod_logs WHERE module='CRAB' AND parent_id=crabs.id AND comment LIKE 'Filtering flag set to FILTERED%' LIMIT 1) AS set_flag_log,
    piCrabs.t_opno,
    qst.desc_en,
    qst.desc_fr,
    qst.desc_zh
EOF;
    }

    public static function getFROM(): string
    {
        return <<<EOF
FROM
    crabs
    LEFT JOIN locations ON crabs.buid=locations.id
    LEFT JOIN people AS insps ON insps.id=crabs.insp_id
    LEFT JOIN people AS initiator ON initiator.id = crabs.init_emno
    LEFT JOIN people AS fixer ON fixer.id = crabs.fix_emno
    LEFT JOIN service AS er ON er.id=crabs.erid
    LEFT JOIN lists ON crabs.code=lists.list_key AND lists.list_name='list.qa.crab.code'
    LEFT JOIN pi_crab_eap AS piCrabs ON crabs.id = piCrabs.parent_id
    LEFT JOIN pi_questions_unit AS qst ON piCrabs.question_id = qst.id
EOF;
    }
    /**
     * Get CRABs by part number
     *
     * @param        $params
     * @param string $pn
     *
     * @return array array of db rows
     */
    public static function byPartNumber($pn)
    {
        // parts info
        if (is_string($pn)) {
            return self::byQuery(['pn' => $pn]);
        }
    }

    /**
     * Get CRABs by query
     *
     * @param        $params
     * @param string $sort
     *
     * @return array array of db rows
     */
    public static function byQuery($params, $sort = '')
    {
        if (is_array($params)) {
            $WHERE = tldUtils::constructWhere($params);
        } else {
            $WHERE = $params;
        }
        $SORTBY = 'ORDER BY dt DESC';
        if (!empty($sort)) {
            $SORTBY = "ORDER BY $sort";
        }
        if (!empty($WHERE)) {
            $WHERE = " HAVING $WHERE ";
        }

        $SELECT = self::getSELECT();
        $FROM = self::getFROM();

        $query = <<<EOF
        $SELECT
        $FROM
        $WHERE
        $SORTBY
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public function byERID($id)
    {
        return self::byQuery(['erid' => $id]);
    }

    public function byERIDOpen($id)
    {
        return self::byQuery(
            [
                'erid' => $id,
                'insp_dt' => '0000-00-00 00:00:00',
            ]
        );
    }

    /**
     * List of CRAB by Stage, Factory
     *
     * @param string $erp
     * @param string $opno
     *
     * @return array
     */
    public static function byERPOPNO($erp = '', $opno = '' , $status = '')
    {
        $where = "1=1 ";
        if ($erp !== 'ALL') {
            $a['buid_fullname'] = $erp;
        }
        if ($opno !== 'ALL') {
            $a['code_fullname'] = $opno;
        }
        if (!empty($status) && $status === 'OPEN') {
            $where .= " AND status <> 'CLOSED' ";
        }
        if (!empty($a)) {
            $where .= ' AND ' .tldUtils::constructWhere($a);
        }

        return self::byQuery($where);
    }

    public function setFilteringFlag($statusFlag)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function getFilteringFlag()
    {
        return $this->itsHeader['filtering_flag'];
    }

    public static function getFilteringFlagList()
    {
        return ['TO BE FILTERED', 'FILTERED'];
    }

    public static function countToBeFilteredByFactory()
    {
        $query = <<<EOF
SELECT
    locations.location AS buid_fullname,
    opno,
    COUNT(*) AS num
FROM crabs
LEFT JOIN locations ON crabs.buid=locations.id
WHERE filtering_flag LIKE 'TO BE FILTERED' AND opno in ('QA','Test')
GROUP BY buid_fullname, opno
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byFactoryFilteringFlag($factory, $filteringFlag, $model = ' ALL_MODELS', $type = ' ALL_TYPES')
    {
        if ($factory !== 'ALL') {
            $a[] = "buid_fullname LIKE '$factory'";
        }
        if ($filteringFlag !== 'ALL') {
            $a[] = "crabs.opno = '$filteringFlag'";
        }

        if ($model !== ' ALL_MODELS') {
            $a[] = "er.model LIKE '$model'";
        }
        if ($type !== ' ALL_TYPES') {
            $a[] = "er.type LIKE '$type'";
        }
        $a[] = "crabs.filtering_flag LIKE 'to be FILTERED'";

        return self::byQuery(implode(' AND ', $a));
    }

    /**
     * Get latest SCARs opened, returns last 20 by default
     *
     * @param int $num , number of rows to return. Default is 20
     *
     * @return array Array of db rows
     */
    public static function byLatest($num = 20)
    {
        if ($num == 0) {
            return [];
        }
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
        $SELECT
        $FROM
        ORDER BY crabs.id DESC
        LIMIT $num
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get all CRABs, by factory and period
     *
     * @param string $start , string $end, int $location
     *
     * @param        $end
     * @param        $location
     * @param string $params
     *
     * @return array Array of db rows
     */
    public static function fullReport($start, $end, $location, $params = '')
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        if (!empty($location)) {
            $location = " AND crabs.buid = $location ";
        }
        if (is_array($params)) {
            $HAVING = tldUtils::constructWhere($params);
        } else {
            $HAVING = $params;
        }
        if (!empty($HAVING)) {
            $HAVING = "HAVING $HAVING ";
        }
        $query = <<<EOF
        $SELECT
        ,(SELECT GROUP_CONCAT(concat_ws(',',date,comment) ORDER BY id DESC SEPARATOR '\n') FROM mod_logs WHERE mod_logs.parent_id=crabs.id AND mod_logs.module='CRAB' ) AS comment
        $FROM
        WHERE DATE_FORMAT(crabs.dt,'%Y-%m-%d') BETWEEN '$start' AND '$end' $location
        $HAVING
        ORDER BY crabs.id DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byUnitbyStatus($params = '')
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        if (is_array($params)) {
            $HAVING = tldUtils::constructWhere($params);
        } else {
            $HAVING = $params;
        }
        if (!empty($HAVING)) {
            $HAVING = "HAVING $HAVING ";
        }
        $query = <<<EOF
        $SELECT
        $FROM
        $HAVING
        ORDER BY crabs.id DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Count by Factory, stage
     *
     * @return array
     */
    public function countByERPOPNO()
    {
        $query = <<<EOF
        SELECT opno,
            locations.location,
            count(*) AS num
        FROM crabs
            LEFT JOIN locations ON crabs.buid=locations.id
        WHERE
            PERIOD_DIFF(
                DATE_FORMAT(NOW(),'%Y%m'),
                DATE_FORMAT(dt,'%Y%m')
            ) <= 12
        GROUP BY location, opno
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function countByERPByOPNO()
    {
        $query = <<<EOF
            SELECT locations.business_unit as bu, opno,count(*) as num
            FROM crabs
            LEFT JOIN locations ON locations.id=crabs.buid
            WHERE status <> 'CLOSED'
            GROUP BY locations.business_unit,opno
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function countByModel($factory, $p, $model, $year, $month)
    {
        switch ($p) {
            case 'byDept':
                $SELECT = 'dept AS p';
                break;
            case 'byModel':
                $SELECT = 'model AS p';
                break;
            case 'byFamily':
                $SELECT = "
IF(LEFT(model, LOCATE('-', model)-1)<>'',
    LEFT(model, LOCATE('-', model)-1),
    model
) AS p";
                break;
            case 'byCode':
                $SELECT = 'lists.list_item AS p, code';
                break;
            default:
                return "ERROR: Mode $p not recognized.";
        }
        if ($factory === 'ALL') {
            $factory = '%';
        } else {
            $factory = TldDatabase::escape($factory);
        }
        $MONTH_QUERY = !empty($month) ? "AND DATE_FORMAT(service.dgt_com,'%m') LIKE '$month'" : '';

        if ($model === ' ALL_MODELS') {
            $MODEL_QUERY = " AND model LIKE '%'";
        } else {
            $model = TldDatabase::escape($model);
            $models = explode(',', $model);
            $model = implode("','", $models);
            $MODEL_QUERY = " AND model IN ( '$model' )";
        }
        $stage = '%';
        $query = <<<EOF
        SELECT
            $SELECT,
            count(*) AS num,
            opno as zval
        FROM crabs
            LEFT JOIN lists ON crabs.code=lists.list_key
                AND lists.list_name='list.qa.crab.code'
            LEFT JOIN service ON crabs.erid=service.id
            LEFT JOIN locations ON crabs.buid=locations.id
        WHERE location LIKE '$factory'
            AND opno LIKE '$stage'
            $MODEL_QUERY
            AND DATE_FORMAT(service.dgt_com,'%Y') LIKE '$year'
            $MONTH_QUERY
        GROUP BY zval, p
        ORDER BY CASE
            WHEN zval = 'QA'
            THEN 1
            WHEN zval = 'Test'
            THEN 2
            WHEN zval = 'Assy'
            THEN 3
            ELSE 5
            END, num
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function countByType($factory, $p, $type, $year, $month)
    {
        switch ($p) {
            case 'byDept':
                $SELECT = 'dept AS p';
                break;
            case 'byType':
                $SELECT = 'type AS p';
                break;
            case 'byFamily':
                $SELECT = "
IF(LEFT(model, LOCATE('-', model)-1)<>'',
    LEFT(model, LOCATE('-', model)-1),
    model
) AS p";
                break;
            case 'byCode':
                $SELECT = 'lists.list_item AS p, code';
                break;
            default:
                return "ERROR: Mode $p not recognized.";
        }
        $factory = $factory === 'ALL' ? '%' : TldDatabase::escape($factory);

        $MONTH_QUERY = !empty($month) ? "AND DATE_FORMAT(service.dgt_com,'%m') LIKE '$month'" : '';

        if ($type === ' ALL_TYPES') {
            $TYPE_QUERY = " AND type LIKE '%'";
        } else {
            $type = TldDatabase::escape($type);
            $types = explode(',', $type);
            $type = implode("','", $types);
            $TYPE_QUERY = " AND type IN ( '$type' )";
        }
        $stage = '%';
        $query = <<<EOF
        SELECT
            $SELECT,
            count(*) AS num,
            opno as zval
        FROM crabs
            LEFT JOIN lists ON crabs.code=lists.list_key
                AND lists.list_name='list.qa.crab.code'
            LEFT JOIN service ON crabs.erid=service.id
            LEFT JOIN locations ON crabs.buid=locations.id
        WHERE location LIKE '$factory'
            AND opno LIKE '$stage'
            $TYPE_QUERY
            AND DATE_FORMAT(service.dgt_com,'%Y') LIKE '$year'
            $MONTH_QUERY
        GROUP BY zval, p
        ORDER BY CASE
            WHEN zval = 'QA'
            THEN 1
            WHEN zval = 'Test'
            THEN 2
            WHEN zval = 'Assy'
            THEN 3
            ELSE 5
            END, num
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function countBy($location, $stage, $p)
    {
        switch ($p) {
            case 'byDept':
                $SELECT = 'dept AS p';
                break;
            case 'byModel':
                $SELECT = 'model AS p';
                break;
            case 'byFamily':
                $SELECT = "
IF(LEFT(model, LOCATE('-', model)-1)<>'',
    LEFT(model, LOCATE('-', model)-1),
    model
) AS p";
                break;
            case 'byCode':
                $SELECT = 'lists.list_item AS p';
                break;
            default:
                return "ERROR: Mode $p not recognized.";
        }
        if ($location === 'ALL') {
            $location = '%';
        } else {
            $location = TldDatabase::escape($location);
        }
        if ($stage === 'ALL') {
            $stage = '%';
        } else {
            $stage = TldDatabase::escape($stage);
        }
        $query = <<<EOF
        SELECT
            $SELECT,
            count(*) AS num
        FROM crabs
            LEFT JOIN lists ON crabs.code=lists.list_key
                AND lists.list_name='list.qa.crab.code'
            LEFT JOIN service ON crabs.erid=service.id
            LEFT JOIN locations ON crabs.buid=locations.id
        WHERE location LIKE '$location'
            AND opno LIKE '$stage'
           AND PERIOD_DIFF(DATE_FORMAT(NOW(),'%Y%m'),
           DATE_FORMAT(dt,'%Y%m')) <= 12
        GROUP BY p
        ORDER BY num
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Search method for CRABS
     *
     * @param string $target
     *
     * @return array
     */
    public static function search($target)
    {
        if (empty($target)) {
            return;
        }
        $search_field = ['buid_fullname', 'code_fullname', 'dept', 'code', 'pn'];
        $HAVING = [];
        foreach ($search_field as $field) {
            $HAVING[] = "$field LIKE '$target'";
        }
        $HAVING = implode(' OR ', $HAVING);
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        // Construct Query
        $query = <<<EOF
        $SELECT
        $FROM
        HAVING
            $HAVING
EOF;

        $rows = tldUtils::getSqlToAssocArray($query);
        foreach($rows AS $key=>$row){
            $factory_erp = tldLocation::getERPByID($row['buid']);
            $erp = new tldBaanERP($factory_erp);
            if (!empty($row['init_emno'])){
                $employee = $erp->getEmployeeData($row['init_emno']);
                $rows[$key]['init_name'] = $row['init_emno'].' '.$employee['t_nama'];
            }
            if (!empty($row['fix_emno'])){
                $employee = $erp->getEmployeeData($row['fix_emno']);
                $rows[$key]['fix_name'] = $row['fix_emno'].' '.$employee['t_nama'];
            }
        }
        return $rows;
    }

    public function setFixed($uid)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function setInspected($uid, $emno)
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public function duplicate()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }
}

?>
