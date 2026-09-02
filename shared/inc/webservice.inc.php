<?php
include_once 'erp.inc.php';
include_once 'sales_service.inc.php';

class tldBaanSoapTransaction
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
        return $this->itsHeader['t_iden'];
    }

    public function getProgram()
    {
        return $this->itsHeader['t_prog'];
    }

    public function getKey1()
    {
        return $this->itsHeader['t_key1'];
    }

    public function getKey2()
    {
        return $this->itsHeader['t_key2'];
    }

    public function getERP()
    {
        return $this->itsHeader['t_comp'];
    }

    public function getDate()
    {
        return $this->itsHeader['t_date'];
    }

    public function getTime()
    {
        return $this->itsHeader['t_time'];
    }

    public function getStatus()
    {
        return trim($this->itsHeader['t_stat']);
    }

    public function getXMLFile()
    {
        return $this->itsHeader['t_xml1'];
    }

    public function getResponse()
    {
        return $this->itsHeader['t_mesg'];
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public static function getSELECT(): string
    {
        return <<<EOF
SELECT
    t_iden,
    t_prog,
    t_key1,
    t_key2,
    t_comp,
    t_date,
    t_time,
    t_stat,
    t_xml1,
    t_mesg
EOF;
    }

    public static function getFROM(): string
    {
        return <<<EOF
FROM
    ttctld999500
EOF;
    }

    public function getHeader()
    {
        $rows = self::byConstraints(['t_iden' => $this->itsID]);
        return $rows[0];
    }

    public static function byConstraints($a, $opt = '')
    {
        if (empty($a)) {
            return 'empty parameter';
        }
        $WHERE = is_array($a) ? 'WHERE ' . tldUtils::constructWhere($a) : "WHERE $a";

        $ORDERBY = !empty($opt['orderBy']) ? "ORDER BY {$opt['orderBy']}" : 'ORDER BY t_key1,t_date,t_time';

        // Construct query
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
$WHERE
$ORDERBY
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     * @param string|array $a
     * @return array
     */
    public static function countByProgramStatusByConstraints($a = '1=1')
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        $query = <<<EOF
SELECT
    RTRIM(t_prog) AS t_prog,
    RTRIM(t_stat) AS t_stat,
    COUNT(*) AS num
FROM
    ttctld999500
WHERE
    $WHERE
GROUP BY
    t_stat,
    t_prog
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public function byProgramStatus($prog, $status)
    {
        $a = [];
        if ($prog !== 'ALL') {
            $a['t_prog'] = $prog;
        }
        if ($status !== 'ALL') {
            $a['t_stat'] = $status;
        }
        return self::byConstraints($a);
    }
}
