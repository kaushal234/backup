<?php
/**
 *    Product Support related classes
 *
 * @package   Support
 * @desc      All classes related to the Product Support department are kept in this file
 * @access    public
 * @author    Graham K.L. Fong <graham.fong@tld-america.com>
 * @copyright TLD
 */

/**
 * Need these functions
 */

use ApiBundle\Client;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpClient\Exception\ClientException;
use Shared\Provider\Common\AirportProvider;
use Shared\Provider\Manufacturing\EquipmentRecordProvider;
use Shared\Persister\Service\CustomerServiceRecordPersister;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

include_once 'common.inc.php';
include_once 'calendar.inc.php';
include_once 'HTML/QuickForm.php';
include_once 'eng.inc.php'; //needed for tldEquipment->geCBOM()
include_once 'quality.inc.php'; //needed for tldEquipment->geCRABS()

class tldPDC
{

    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    /*******************************************
     * GETTERS
     *******************************************/

    public function getID()
    {
        return $this->itsID;
    }

    public function getParentID()
    {
        return $this->itsHeader['parent_id'];
    }

    public function getFactoryID()
    {
        return $this->itsHeader['factory'];
    }

    public function getFactoryName()
    {
        return $this->itsHeader['factory_fullname'];
    }

    public function getFactoryERP()
    {
        return $this->itsHeader['factory_erp'];
    }

    public function getAssignee()
    {
        return $this->itsHeader['assignee'];
    }

    public function getProductType()
    {
        return $this->itsHeader['product_type'];
    }

    public function getModel()
    {
        return $this->itsHeader['model'];
    }

    public function getLastStatus()
    {
        return $this->itsHeader['last_status'];
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

    public function getShortDescription()
    {
        return $this->itsHeader['short_desc'];
    }

    /*******************************************
     * CRUD functions
     *******************************************/

    public static function getSELECT(): string
    {
        return <<<EOF
SELECT
    pdc.*,
    locations.location AS factory_fullname,
    locations.erp AS factory_erp,
    CONCAT(poster.firstname, ' ', poster.lastname) AS poster_fullname,
    CONCAT(initiator.firstname, ' ', initiator.lastname) AS initiator_fullname,
    CONCAT(assignee.firstname, ' ', assignee.lastname) AS assignee_fullname,
    initiator.email AS initiator_email,
    IF(pdc.status='SUSPENDED',
        PERIOD_DIFF(DATE_FORMAT(DATE_SUB(date_suspended, INTERVAL days_suspended DAY), '%Y%m'), DATE_FORMAT(pdc.date, '%Y%m')),
        PERIOD_DIFF(DATE_FORMAT(DATE_SUB(now(), INTERVAL days_suspended DAY), '%Y%m'), DATE_FORMAT(pdc.date, '%Y%m'))
    ) AS monthsOpen,
    IF(pdc.status='SUSPENDED',
        ROUND(POW(1.4678, PERIOD_DIFF(DATE_FORMAT(DATE_SUB(date_suspended, INTERVAL days_suspended DAY), '%Y%m'),DATE_FORMAT(pdc.date, '%Y%m'))), 0),
        ROUND(POW(1.4678, PERIOD_DIFF(DATE_FORMAT(DATE_SUB(now(), INTERVAL days_suspended DAY), '%Y%m'),DATE_FORMAT(pdc.date, '%Y%m'))), 0)
    ) AS dfactor,
    CASE
        WHEN pdc.status='CLOSED'
            THEN final_fweight
        WHEN pdc.status='SUSPENDED'
            THEN ROUND(ifactor * POW(1.4678, PERIOD_DIFF(DATE_FORMAT(DATE_SUB(date_suspended, INTERVAL days_suspended DAY), '%Y%m'),DATE_FORMAT(pdc.date, '%Y%m'))), 0)
        ELSE
            ROUND(ifactor * POW(1.4678, PERIOD_DIFF(DATE_FORMAT(DATE_SUB(now(), INTERVAL days_suspended DAY), '%Y%m'),DATE_FORMAT(pdc.date, '%Y%m'))), 0)
    END AS fweight,
    log.comment AS closure
EOF;
    }

    public static function getFROM(): string
    {
        return <<<EOF
FROM
    demerit AS pdc
    LEFT JOIN locations ON locations.id=pdc.factory
    LEFT JOIN people AS initiator ON initiator.id=pdc.initiator
    LEFT JOIN people AS poster ON poster.id=pdc.poster
    LEFT JOIN people AS assignee ON assignee.id=pdc.assignee
    LEFT JOIN mod_logs AS log ON log.parent_id=pdc.id AND log.id=(select id from mod_logs where parent_id = log.parent_id  and module like 'PDC' ORDER BY date desc limit 1) AND pdc.status LIKE 'CLOSED' AND log.module LIKE 'PDC'

EOF;
    }

    public function getHeader()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = "$SELECT $FROM WHERE pdc.id=$this->itsID";
        $result = tldUtils::getSqlRowToAssocArray($query);
        return [] === $result ? $result : current(self::getTOCLinksCount([$result]));
    }

    public function refresh()
    {
        $this->itsHeader = $this->getHeader();
    }

    public static function byConstraintsFollowers($a, $userId, $opt = [])
    {
        $WHERE = $LIMIT = '';
        // Look for constraints
        $HAVING = is_array($a) ? 'HAVING ' . tldUtils::constructWhere($a) : "HAVING $a";

        // Look for options
        if (!empty($opt['where'])) {
            if (is_array($opt['where'])) {
                $WHERE = 'WHERE ' . tldUtils::constructWhere($opt['where']);
            } else {
                $WHERE = "WHERE {$opt['where']}";
            }
        }
        if (!empty($opt['limit'])) {
            $LIMIT = 'LIMIT ' . $opt['limit'];
        }
        $ORDERBY = !empty($opt['orderBy']) ? 'ORDER BY ' . $opt['orderBy'] : 'ORDER BY fweight DESC';

        // Construct query
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $FROM .= " INNER JOIN mod_lists ON mod_lists.module = 'PDC' AND mod_lists.list_name = 'MEMBERS' AND mod_lists.parent_id = pdc.id AND mod_lists.value='$userId'";
        $query = <<<EOF
$SELECT
$FROM
$WHERE
$HAVING
$ORDERBY
$LIMIT
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byConstraints($a, $opt = [])
    {
        $WHERE = $LIMIT = '';
        // Look for constraints
        $HAVING = is_array($a) ? 'HAVING ' . tldUtils::constructWhere($a) : "HAVING $a";

        // Look for options
        if (!empty($opt['where'])) {
            if (is_array($opt['where'])) {
                $WHERE = 'WHERE ' . tldUtils::constructWhere($opt['where']);
            } else {
                $WHERE = "WHERE {$opt['where']}";
            }
        }
        if (!empty($opt['limit'])) {
            $LIMIT = 'LIMIT ' . $opt['limit'];
        }
        $ORDERBY = !empty($opt['orderBy']) ? 'ORDER BY ' . $opt['orderBy'] : 'ORDER BY fweight DESC';

        // Construct query
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
$WHERE
$HAVING
$ORDERBY
$LIMIT
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function insert($a)
    {
        if (empty($a)) {
            return;
        }
        $fields = [
            'assignee',
            'factory',
            'product_type',
            'model',
            'date_closed',
            'date_suspended',
            'days_suspended',
            'short_desc',
            'description',
            'resolution',
            'picture_filename',
            'ifactor',
            'final_fweight',
            'poster',
            'initiator',
            'is_ibs',
            'is_ihs',
            'is_link',
            'is_apu_off',
        ];
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO demerit SET date=NOW(),status='PENDING',last_status='PENDING', status_updated_at=NOW(), $SET";

        return tldUtils::sqlInsert($query);
    }

    public function update($a, $fields = '')
    {
        if (empty($a)) {
            return;
        }
        if (isset($a['status'])){
            $a['status_updated_at'] = date('Y-m-d h:i:s');
        }
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "UPDATE demerit SET $SET WHERE id=$this->itsID";

        return tldUtils::sqlQuery($query);
    }

    public function delete()
    {
        $query = "DELETE FROM demerit WHERE id=$this->itsID LIMIT 1";

        return tldUtils::sqlExecute($query);
    }

    /*******************************************
     * COMMON MOD methods
     *******************************************/

    public function addLogEntry($uid, $comment, $num_log = 0)
    {
        return tldModLog::insert(
            [
                'parent_id' => $this->itsID,
                'module' => 'PDC',
                'poster' => $uid,
                'comment' => $comment,
                'log_num' => $num_log,
            ]
        );
    }

    public function getLog()
    {
        return tldModLog::byParent($this->itsID, 'PDC');
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
                'erp' => $this->getFactoryERP(),
                'bu_id' => $this->getFactoryID(),
                'assignee' => $a['assignee'],
                'assignor' => $a['assignor'],
                'task' => $a['task'],
                'due_date' => $a['due_date'],
                'escalation_trigger' => $a['escalation_trigger'],
            ],
            'PDC'
        );
        if (is_string($taskID)) {
            return $taskID;
        }
        // Enter mod key to link tasks to status
        // --- get status # to be able to order it
        $statusList = array_keys(self::getStatusList());
        $orderStatus = (int)array_search($status, $statusList);
        // --- create record
        $modkID = tldModKey::insert(
            [
                'parent_id' => $taskID,
                'module' => 'TASK',
                'type' => $status,
                'key1' => $orderStatus,
            ]
        );
        if (is_string($modkID)) {
            error_log("PDC task creation with mod_key error: $modkID");
        }

        return $taskID;
    }

    public function getTasks()
    {
        return tldTask::byParent($this->itsID, 'PDC', 'ALL');
    }

    public function getStatusTasksByConstraints($a, $opt = [])
    {
        // Get primary tasks query parts
        $SELECT = tldTask::getSELECT();
        $FROM = tldTask::getFROM();
        // Look for constraints
        if (is_array($a)) {
            $HAVING = tldUtils::constructWhere($a);
        } else {
            $HAVING = $a;
        }
        if (!empty($HAVING)) {
            $HAVING = "HAVING $HAVING";
        }
        // look for options
        $GROUPBY = !empty($opt['groupBy']) ? "GROUP BY {$opt['groupBy']}" : '';
        $ORDERBY = 'ORDER BY pdc_status_order ASC, tasks.status DESC';
        if (!empty($opt['orderBy'])) {
            $ORDERBY = "ORDER BY {$opt['orderBy']}";
        }
        // Query tasks
        $query = <<<EOF
$SELECT,
    mk.type AS pdc_status,
    mk.key1 AS pdc_status_order
$FROM
    LEFT JOIN mod_keys AS mk ON mk.parent_id=tasks.id AND mk.module LIKE 'TASK'
WHERE
    tasks.module LIKE 'PDC'
    AND tasks.parent_id=$this->itsID
$GROUPBY
$HAVING
$ORDERBY
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function getFiles($lvl = 0)
    {
        return tldModFile::byParent($this->itsID, 'PDC', $lvl);
    }

    public function addLinkTo($type, $item)
    {
        return tldModLink::insert('PDC', $this->itsID, $type, $item);
    }

    public function getLinksFromHere($type = '')
    {
        return tldModLink::byParent($this->itsID, 'PDC', $type);
    }

    public function getLinksToHere($module = '')
    {
        return tldModLink::byItem($this->itsID, 'PDC', $module);
    }

    public function getParts()
    {
        return tldModParts::byParent($this->itsID, 'PDC');
    }

    public function getFollowers()
    {
        return tldModMember::byParent($this->itsID, 'PDC');
    }

    public function addModList($a)
    {
        return tldModList::insert(
            [
                'parent_id' => $this->getID(),
                'module' => 'PDC',
                'list_name' => $a['list_name'],
                'list_key' => $a['list_key'],
                'list_key2' => $a['list_key2'],
                'value' => $a['value'],
                'value2' => $a['value2'],
            ]
        );
    }

    public static function countByPartByStatus($pn, $status = '')
    {
        $status = TldDatabase::escape($status);
        $WHERE = $status ? "AND t1.status LIKE '$status'" : '';
        $pn = TldDatabase::escape($pn);

        $query = <<<EOF
		SELECT count(*) AS qty
			FROM demerit AS t1
			JOIN mod_parts AS t2 ON t1.id=t2.parent_id
		WHERE t2.pn LIKE '$pn' AND t2.module='PDC'
		$WHERE
EOF;
        $rows = tldUtils::getSqlRowToAssocArray($query);

        return $rows['qty'] ?? '0';
    }

    public static function byPartNumber($pn, $options = [])
    {
        if (empty($pn)) {
            return [];
        }
        $operator = 'LIKE';
        if (is_array($pn)) {
            $operator = 'IN';
            $pn = implode("','", $pn);
        }

        $WHERE = isset($options['EXTRA_WHERE']) ? $options['EXTRA_WHERE'] : '';

        $query = <<<EOF
		SELECT
		pdc.*,
    t2.pn, 
    locations.location AS factory_fullname,
    locations.erp AS factory_erp,
    CONCAT(poster.firstname, ' ', poster.lastname) AS poster_fullname,
    CONCAT(initiator.firstname, ' ', initiator.lastname) AS initiator_fullname,
    initiator.email AS initiator_email,
    IF(pdc.status='SUSPENDED',
        PERIOD_DIFF(DATE_FORMAT(DATE_SUB(date_suspended, INTERVAL days_suspended DAY), '%Y%m'), DATE_FORMAT(date, '%Y%m')),
        PERIOD_DIFF(DATE_FORMAT(DATE_SUB(now(), INTERVAL days_suspended DAY), '%Y%m'), DATE_FORMAT(date, '%Y%m'))
    ) AS monthsOpen,
    IF(pdc.status='SUSPENDED',
        ROUND(POW(1.4678, PERIOD_DIFF(DATE_FORMAT(DATE_SUB(date_suspended, INTERVAL days_suspended DAY), '%Y%m'),DATE_FORMAT(date, '%Y%m'))), 0),
        ROUND(POW(1.4678, PERIOD_DIFF(DATE_FORMAT(DATE_SUB(now(), INTERVAL days_suspended DAY), '%Y%m'),DATE_FORMAT(date, '%Y%m'))), 0)
    ) AS dfactor,
    CASE
        WHEN pdc.status='CLOSED'
            THEN final_fweight
        WHEN pdc.status='SUSPENDED'
            THEN ROUND(ifactor * POW(1.4678, PERIOD_DIFF(DATE_FORMAT(DATE_SUB(date_suspended, INTERVAL days_suspended DAY), '%Y%m'),DATE_FORMAT(date, '%Y%m'))), 0)
        ELSE
            ROUND(ifactor * POW(1.4678, PERIOD_DIFF(DATE_FORMAT(DATE_SUB(now(), INTERVAL days_suspended DAY), '%Y%m'),DATE_FORMAT(date, '%Y%m'))), 0)
    END AS fweight,
    (
        SELECT COUNT(*) 
        FROM mod_links link 
        LEFT JOIN toc AS toc1 ON link.item = toc1.id
        LEFT JOIN toc AS toc2 ON link.item = toc2.id
        WHERE 
        ((link.module='PDC' AND link.type='TOC' AND link.parent_id=pdc.id) 
        OR (link.type='PDC' AND link.module='TOC'  AND link.item=pdc.id))
        AND toc1.status IN ('SUSPENDED', 'IN_PROGRESS')
        AND toc2.status IN ('SUSPENDED', 'IN_PROGRESS')
    ) AS tocs_count
    FROM demerit AS pdc
    LEFT JOIN locations ON locations.id=pdc.factory
    LEFT JOIN people AS initiator ON initiator.id=pdc.initiator
    LEFT JOIN people AS poster ON poster.id=pdc.poster
	LEFT JOIN mod_parts AS t2 ON pdc.id=t2.parent_id
    
		WHERE t2.pn $operator ('$pn') AND t2.module='PDC'
		$WHERE
		
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getTOCLinksCount(array $pdcs)
    {
        if ([] === $pdcs) {
            return $pdcs;
        }

        $ids = array_column($pdcs, 'id');
        $links = array_merge(tldModLink::byItem($ids, 'PDC', 'TOC'), tldModLink::byParent($ids, 'PDC', 'TOC'));
        $linkedTOCsFiltered = array_filter($links, static function($toc) {
            return strpos($toc['dsca'], 'IN PROGRESS') || strpos($toc['dsca'], 'SUSPENDED');
        });

        $pdcs = array_map(static function ($pdc) {
            $pdc['toc_count'] = 0;
            return $pdc;
        }, $pdcs);

        $pdcs = array_combine(array_column($pdcs, 'id'), $pdcs);
        foreach ($linkedTOCsFiltered as $link) {
            $pdcId = $link['module'] === 'PDC' ? $link['parent_id'] : $link['item'];
            $pdcs[$pdcId]['toc_count']++;
        }

        return $pdcs;
    }

    public function getModuleLinkToPdc(string $status = ''): array
    {
        $data = [];
        if (in_array($status, ['INVESTIGATION', ''], true)) {
            $warrantys[] = array_column($this->getLinksFromHere('WC'), 'item');
            $warrantys[] = array_column($this->getLinksToHere('WC'), 'parent_id');

            $TOCs[] = array_column($this->getLinksFromHere('TOC'), 'item');
            $TOCs[] = array_column($this->getLinksToHere('TOC'), 'parent_id');

            foreach (array_merge(...$warrantys) as $warrantyId) {
                $wc = new tldWC($warrantyId);
                $wcTasks = (array)$wc->getTasks();
                if (empty($wcTasks)) {
                    continue;
                }
                foreach ($wcTasks as $wcTask) {
                    $wcTask['pdc_status'] = 'INVESTIGATION';
                    $data[] = $wcTask;
                }
            }

            foreach (array_merge(...$TOCs) as $TOCid) {
                $toc = new tldTOC($TOCid);
                $tocTasks = (array)$toc->getTasks();
                if (empty($tocTasks)) {
                    continue;
                }
                foreach ($tocTasks as $tocTask) {
                    $tocTask['pdc_status'] = 'INVESTIGATION';
                    $data[] = $tocTask;
                }
            }
        }

        if (in_array($status, ['ACTION', ''], true)) {
            $EAPs[] = array_column($this->getLinksFromHere('EAP'), 'item');
            $EAPs[] = array_column($this->getLinksToHere('EAP'), 'parent_id');

            $SBs[] = array_column($this->getLinksFromHere('SB3'), 'item');
            $SBs[] = array_column($this->getLinksToHere('SB3'), 'parent_id');

            foreach (array_merge(...$SBs) as $SB) {
                $sb = new tldSB3($SB);
                $sbTasks = (array)$sb->getTasks();
                if (empty($sbTasks)) {
                    continue;
                }
                foreach ($sbTasks as $sbTask) {
                    $sbTask['pdc_status'] = 'ACTION';
                    $data[] = $sbTask;
                }
            }

            foreach (array_merge(...$EAPs) as $EAP) {
                $eap = new tldEAP($EAP);
                $eapTasks = (array)$eap->getTasks();
                if (empty($eapTasks)) {
                    continue;
                }
                foreach ($eapTasks as $eapTask) {
                    $eapTask['pdc_status'] = 'ACTION';
                    $data[] = $eapTask;
                }
            }
        }

        return $data;
    }

    public function getCountModuleLink(): array
    {
        return [
            ['module' => 'WC', 'count' => count(array_merge($this->getLinksToHere('WC'), $this->getLinksFromHere('WC')))],
            ['module' => 'TOC', 'count' => count(array_merge($this->getLinksToHere('TOC'), $this->getLinksFromHere('TOC')))],
            ['module' => 'SN', 'count' => count(array_merge($this->getLinksToHere('SN'), $this->getLinksFromHere('SN')))],
        ];
    }

    public function getListsByListName($listName)
    {
        return tldModList::byConstraints(
            [
                'parent_id' => $this->getID(),
                'module' => 'PDC',
                'list_name' => $listName,
            ]
        );
    }

    /*******************************************
     *   REFERENCE methods
     ********************************************/

    public static function getStatusList()
    {
        $statusList = ['PENDING', 'INVESTIGATION', 'ACTION', 'SUSPENDED', 'REJECTED', 'CLOSED'];

        return array_combine($statusList, $statusList);
    }

    public static function getStatusListForFlag()
    {
        $statusList = ['PENDING', 'INVESTIGATION', 'ACTION', 'SUSPENDED'];

        return array_combine($statusList, $statusList);
    }

    public static function getOpenStatusList()
    {
        $statusList = ['PENDING', 'INVESTIGATION', 'ACTION', 'SUSPENDED'];

        return array_combine($statusList, $statusList);
    }

    public static function getIFactorList()
    {
        return ['1' => '1', '10' => '10', '100' => '100', '1000' => '1000'];
    }

    public static function getPathToUploadFile($filename = null)
    {
        return tldUtils::getPathToUploadFile('demerit', $filename);
    }

    /*******************************************
     * LOGIC and ACTION methods
     *******************************************/

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public function isClosed()
    {
        return $this->getStatus() === 'CLOSED';
    }

    public function isAllowedStatus($newStatus)
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
        // -- Look for key data
        switch ($newStatus) {
            case 'INVESTIGATION':
                if (empty($this->itsHeader['containment_action'])) {
                    return 'Containment action not set';
                }
                break;
            case 'ACTION':
                if (empty($this->itsHeader['containment_action'])) {
                    return 'Containment action not set';
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
                if (!count($tasks)) {
                    break;
                }
                foreach ($tasks as $task) {
                    if ($task['status'] !== 'CLOSED') {
                        return 'All tasks must be closed';
                    }
                }
                break;
            default:
                // In all other cases, check task linked to the actual PDC status
                $a = "pdc_status LIKE '{$this->getStatus()}' AND status!='CLOSED'";
                $tasks = $this->getStatusTasksByConstraints($a);
                if (count($tasks)) {
                    return "Tasks for PDC status {$this->getStatus()} are still open";
                }
                break;
        }

        return true;
    }

    public function getAllowedStatus()
    {
        return self::getStatusList();
        $allowed = [];
        switch ($this->getStatus()) {
            case 'PENDING':
                $allowed = ['PENDING', 'INVESTIGATION', 'SUSPENDED', 'REJECTED', 'CLOSED'];
                break;
            case 'INVESTIGATION':
                $allowed = ['INVESTIGATION', 'ACTION', 'SUSPENDED', 'REJECTED'];
                break;
            case 'ACTION':
                $allowed = ['ACTION', 'INVESTIGATION', 'SUSPENDED', 'REJECTED', 'CLOSED'];
                break;
            case 'SUSPENDED':
                // Get last status
                $lastStatus = $this->getLastStatus();
                if (!empty($lastStatus)) {
                    $allowed[] = $lastStatus;
                }
                // Or REJECT
                $allowed[] = 'REJECTED';
                break;
            case 'REJECTED':
                $allowed = ['PENDING'];
                break;
            case 'CLOSED':
                $allowed = ['PENDING'];
                break;
        }

        return $allowed;
    }

    public function updateStatus($uid, $newStatus, $options = null)
    {
        // Check rules
        $allowedStatusResponse = $this->isAllowedStatus($newStatus);
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
        $this->update(['is_ready_to_close' => $options['is_ready_to_close']]);
        // Log the status change
        $log = "Status changed from {$this->getStatus()} to $newStatus";
        $log = TldDatabase::escape($log);
        $order = ['\r\n', '\n', '\r'];
        $replace = '';
        $reason = str_replace($order, $replace, $options['reason']);
        if (isset($options['reason'])) {
            $log .= "<br>{$reason}";
        }
        $this->addLogEntry($uid, $log);
        // Notify initiator
        $message = <<<EOF
<p>This is to inform you that the status of PDC#{$this->getID()} changed from <b>{$this->getStatus()}</b> to <b>$newStatus</b>.</p>
<p>Last PDC log:<br>{$reason}</p>
EOF;

        // -- default cc recipients
        $CC = $this->getStatusChangeEmailRecipientList($newStatus);
        // -- look for additional cc in options
        if (count($options['cc'] ?? [])) {
            foreach ($options['cc'] as $email) {
                $CC[] = $email;
            }
        }

        // POST actions --->
        switch ($newStatus) {
            case 'PENDING':
            case 'INVESTIGATION':
            case 'ACTION':
                #When status is set to INVESTIGATION, and the model's family of PDC has multiple manufacturing factories,
                #then notify EM, PSM and RME of these manufacturing factories (except the ones from the factory of PDC). See TTS 6320/33583
                if ($newStatus === 'INVESTIGATION') {
                    global $kernel;
                    try {
                        $container = $kernel->getContainer();
                        $client = $container->get(Client::class);

                        if (($model = $this->getModel()) !== 'ALL_MODELS') {
                            $apiModel = $client->findOneBy('sales/products', ['name' => $model, 'normalization_groups' => ['catalogue_family_manufacturing']]);
                            $family = $apiModel['family'];
                            if (count($family['manufacturingFactories']) > 1) {
                                foreach($family['manufacturingFactories'] as $manufacturingFactory) {
                                    if ((int) $manufacturingFactory['erp'] === (int) $this->getFactoryERP()) {
                                        continue;
                                    }
                                    foreach (['role_EM', 'role_PSM', 'role_RME'] as $group) {
                                        foreach ((new tldGroup($group, $manufacturingFactory['erp']))->getUserlist() as $user) {
                                            tldModMember::insert('PDC', $this->getID(), $user['id']);
                                            $CC[] = $user['email'];
                                        }
                                    }
                                }
                            }
                        }
                    } catch (ClientException $exception) {
                        //do nothing, status is already changed
                    }
                }

                // From SUSPENDED to either PENDING or INVESTIGATION or ACTION -> update days_suspended counter
                if ($this->getStatus() === 'SUSPENDED') {
                    $actualSuspendedDate = new DateTime($this->itsHeader['date_suspended']);
                    $today = new DateTime(date('Y-m-d'));
                    $intervalSinceSuspended = $actualSuspendedDate->diff($today);
                    $nbDaysSinceSuspended = $intervalSinceSuspended->format('%a');
                    $this->update(
                        [
                            'date_suspended' => '0000-00-00',
                            'days_suspended' => (int) $nbDaysSinceSuspended + (int) $this->itsHeader['days_suspended'],
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
                        'date_closed' => date('Y-m-d'),
                        'resolution' => $options['reason'],
                    ]
                );
                break;
            case 'REJECTED':
                $this->update(
                    [
                        'final_fweight' => $this->getFocusWeight(),
                        'date_closed' => date('Y-m-d'),
                        'rejection_reason' => $options['reason'],
                    ]
                );
                break;
        }

        // -- notify
        $this->refresh();
        $CC = array_values($CC);
        $this->notifyInitiator(
            $message,
            "PDC#{$this->getID()} - $newStatus - {$this->getFactoryName()} - {$this->getModel()}, Age: {$this->itsHeader['monthsOpen']} months",
            $CC
        );

    }

    public function isAllowedToBeClosed(int $id): array
    {
        $query1 = <<<EOF
    SELECT
    links.id,
    links.module AS module,
    links.parent_id AS module_id,
    CASE links.module
        WHEN 'EAP' THEN (SELECT eap.status FROM eap WHERE links.parent_id=eap.id)
        WHEN 'TOC' THEN (SELECT toc.status  FROM toc WHERE links.parent_id=toc.id)
        WHEN 'SCAR' THEN (SELECT scar.status  FROM scar WHERE links.parent_id=scar.id)
        END AS status
FROM
    mod_links links
WHERE
    links.item = $id AND links.type = 'PDC'
EOF;

        $query2 = <<<EOF
    SELECT
    links.id,
    links.type AS module,
    links.item AS module_id,
    CASE links.type
        WHEN 'EAP' THEN (SELECT eap.status  FROM eap WHERE links.item=eap.id)
        WHEN 'TOC' THEN (SELECT toc.status  FROM toc WHERE links.item=toc.id)
        WHEN 'SCAR' THEN (SELECT scar.status  FROM scar WHERE links.item=scar.id)
        END AS status
FROM
    mod_links links
WHERE
    links.parent_id = $id AND links.module = 'PDC'
EOF;

        $errors = [];
        foreach (array_merge(tldUtils::getSqlToAssocArray($query1), tldUtils::getSqlToAssocArray($query2)) as $row) {
            switch ($row['module']) {
                case 'TOC':
                    if (($toc = new tldTOC($row['id'])) && $toc->isEmpty()) {
                        continue 2;
                    }
                    if (!in_array($row['status'], ['SOLVED', 'CLOSED'], true)) {
                        $errors[] = "TOC#{$row['module_id']} is not SOLVED or CLOSED (actual status: {$row['status']})";
                    }
                    break;
                case 'SCAR':
                    global $kernel;
                    $client = $kernel->getContainer()->get(Client::class);
                    try {
                        $scar = $client->find('quality/supplier_corrective_action_requests', $row['module_id']);
                    } catch (\Exception $exception) {
                        if ($exception->getCode() === Response::HTTP_NOT_FOUND){
                            continue 2;
                        }
                        $errors[] = sprintf("Error when closing the PDC with linked SCAR #%s", $row['module_id']);
                        error_log(sprintf("linked SCAR# error when updating status of PDC : %s", $exception->getMessage()));
                    }

                    if ((isset($scar)) && (!in_array(($scar['status'] ?? ''), ['COMMERCIAL AGREEMENT', 'CLOSED'], true))) {
                        $errors[] = "SCAR#{$scar['id']} is not COMMERCIAL AGREEMENT (actual status: {$scar['status']})";
                    }
                    break;
                case 'EAP':
                    if (($eap = new tldEAP($row['id'])) && $eap->isEmpty()) {
                        continue 2;
                    }
                    if ($row['status'] !== 'CLOSED' || ($row['status'] === 'IN PROGRESS' && $this->getIFactor() !== 1)) {
                        $errors[] = "EAP#{$row['module_id']} is not CLOSED (actual status: {$row['status']})";
                    }
                    break;
                default:
                    break;
            }
        }

        return $errors;
    }

    /**
     * Save data form submitted
     *
     * @param string $formName
     * @param array $formData
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
        if (!$list->isEmpty() && $list->getModule() === 'PDC'
            && $list->getParentID() == $this->getID() && $list->getListName() == $formName
        ) {
            $e = $list->update(['value2' => $encodedData]);
        } // else create
        else {
            $e = $this->addModList(
                [
                    'list_name' => $formName,
                    'value2' => $encodedData,
                ]
            );
        }

        return $e;
    }

    public function getFormDataByName($formName)
    {
        $rows = $this->getListsByListName($formName);
        return !empty($rows[0]['value2']) ? unserialize(base64_decode($rows[0]['value2'])) : [];
    }

    public function getStatusChangeEmailRecipientList($status = null)
    {
        $cc = [];

        // Factory notification :
        foreach (['role_PSM', 'role_RME', 'role_QAM', 'role_COO'] as $groupName) {
            $group = new tldGroup($groupName, $this->getFactoryERP());
            $cc[] = $group->getEmailList();
        }

        // Region notification :
        $factoryPdc = new tldLocation($this->getFactoryID());

        $regionPdc = new tldRegion($factoryPdc->itsDetails['region_id']);
        // Name of the region is on the 'division' key
        $regionName = $regionPdc->itsHeader['division'];

        $acls = tldGroup::getApiAclsByLocationNamesAndGroupNames([$regionName], ['ROLE_RCEO', 'ROLE_RCOO']);
        $userInApiGroups = array_column($acls ?? [], 'user');

        $cc[] = array_column($userInApiGroups, 'email');

        // Division Notification
        foreach (['role_GCTO', 'role_GPID', 'role_COO', 'role_CEO', 'role_CMO'] as $groupName) {
            $alvestGroup = new tldGroup($groupName, 900);
            $cc[] = $alvestGroup->getEmailList();
            $gseGroup = new tldGroup($groupName, 997);
            $cc[] = $gseGroup->getEmailList();
        }

        if ($status === 'CLOSED') {
            $groupCsm = new tldGroup('role_CSM', null, ['GSE (TLD & AERO)', 'ALVEST PARTS & ACCESSORIES']);
            $cc[] = $groupCsm->getEmailList();

            $groupChairman = new tldGroup('role_CEO', 997);
            $cc[] = $groupChairman->getEmailList();
        }

        // return all recipients
        return array_merge(... $cc);
    }

    public function getFollowersRecipients()
    {
        $recipients = [];
        foreach ($this->getFollowers() as $follower) {
            if (empty($follower['email'])) {
                continue;
            }
            $recipients[] = $follower['email'];
        }

        return $recipients;
    }

    public function notifyInitiator($message, $subject = '', $cc = [], int $erpFactory = null)
    {
        // Get initiator
        $TO = $this->getInitiatorEmail();

        $additionalCc = [];

        $erpFactory = $erpFactory ?? $this->getFactoryERP();

        foreach (['role_QAM', 'role_PSM', 'role_PSE', 'role_PSA', 'role_RME', 'ROLE_QA', 'role_CMO', 'role_COO', 'ROLE_RCEO', 'role_RCOO', 'role_GCTO', 'role_GPID'] as $groupName) {
            $group = new tldGroup($groupName, $erpFactory);
            $additionalCc[] = $group->getEmailList();
        }
        if ($this->itsHeader['is_ibs'] === '1') {
            foreach (['SRME_IBS', 'SPSM_IBS'] as $groupName) {
                $group = new tldGroup($groupName);
                $additionalCc[] = $group->getEmailList();
            }
        }

        if ($this->itsHeader['is_link'] === '1') {
            foreach (['SRME_LINK', 'SPSM_LINK'] as $groupName) {
                $group = new tldGroup($groupName);
                $additionalCc[] = $group->getEmailList();
            }
        }

        $additionalCc = array_merge(...$additionalCc);

        // Send the notification
        return $this->notify($TO, 'noreply@tld-gse.com', $subject, $message, array_unique(array_filter(array_merge($cc, $additionalCc))));
    }

    public function notify($to, $from, $subject, $message, $cc = '')
    {
        $message .= <<<EOF
<p><a href="https://www.tld-gse.com/en/private/product_support/index.ps.php?m[0]=pdc&m[1]=view&id=$this->itsID">
Click here to go to PDC#$this->itsID.</a></p>
EOF;
        $message .= $this->getPrintVersion();

        return tldUtils::emailAttachment(
            $to,
            $from,
            $subject,
            $message,
            null,
            $cc
        );
    }

    /*******************************************
     * STATS and REPORTS methods
     *******************************************/

    public static function search($val)
    {
        $constraint = <<<EOF
pdc.id='$val'
OR pdc.short_desc like '%$val%'
OR pdc.description like '%$val%'
OR pdc.model like '%$val%'
OR pdc.status like '%$val%'
OR pdc.product_type like '%$val%'
EOF;

        return self::byConstraints($constraint);
    }

    public static function byOpenByConstraints($constraints, $opt = [])
    {
        $WHERE = "pdc.status!='CLOSED'";
        if (is_array($constraints)) {
            $a = tldUtils::constructWhere($constraints);
        } else {
            $a = $constraints;
        }
        if (!empty($a)) {
            $WHERE .= " AND $a";
        }

        return self::byConstraints($WHERE, $opt);
    }

    public static function byOpenByInitiatorID($uid)
    {
        return self::byOpenByConstraints(['initiator' => $uid]);
    }

    public static function byOpenTaskAssigneeID($uid)
    {
        $a = <<<EOF
$uid IN(
    SELECT assignee FROM tasks
    WHERE tasks.parent_id=pdc.id AND tasks.module LIKE 'PDC'
        AND tasks.status!='CLOSED'
)
EOF;

        return self::byConstraints($a);
    }

    public static function byLatest($limit = 10)
    {
        return self::byConstraints('1=1', ['limit' => $limit, 'orderBy' => 'id DESC']);
    }

    public static function countByFactoryStatusByConstraints($constraints = '1=1')
    {
        $WHERE = '';
        if (is_array($constraints)) {
            $WHERE = tldUtils::constructWhere($constraints);
        } elseif (!empty($constraints)) {
            $WHERE = $constraints;
        }
        if (!empty($WHERE)) {
            $WHERE = "WHERE $WHERE";
        }
        $query = <<<EOF
SELECT
    demerit.status,
    demerit.factory,
    locations.location AS factory_fullname,
    COUNT(*) AS num
FROM
    demerit
    LEFT JOIN locations ON demerit.factory=locations.id
$WHERE
GROUP BY
    factory_fullname, status
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function countByFactoryStatus($constraints = '1=1')
    {
        return self::countByFactoryStatusByConstraints($constraints);
    }

    public static function countByFactoryStatusFollowed($userId, $constraints = '1=1')
    {
        $WHERE = '';
        if (is_array($constraints)) {
            $WHERE = tldUtils::constructWhere($constraints);
        } elseif (!empty($constraints)) {
            $WHERE = $constraints;
        }
        if (!empty($WHERE)) {
            $WHERE = "WHERE $WHERE";
        }
        $query = <<<EOF
SELECT
    demerit.status,
    demerit.factory,
    locations.location AS factory_fullname,
    COUNT(*) AS num
FROM
    demerit
    LEFT JOIN locations ON demerit.factory=locations.id
    INNER JOIN mod_lists ON mod_lists.module = 'PDC' AND mod_lists.list_name = 'MEMBERS' AND mod_lists.parent_id = demerit.id AND mod_lists.value = '$userId'
$WHERE
GROUP BY
    factory_fullname, status
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function countByFactoryOpenStatusWithNoOpenTasks()
    {
        $a = [
            "status NOT IN('REJECTED','CLOSED')",
            "(SELECT COUNT(*) FROM tasks WHERE status!='CLOSED' AND module LIKE 'PDC' AND demerit.id=tasks.parent_id)=0",
        ];

        return self::countByFactoryStatusByConstraints(implode(' AND ', $a));
    }

    public static function byFollowers($factory, $status, $userId)
     {
        $a = ['1=1'];
        if ($factory !== 'ALL') {
            $a[] = "factory_fullname LIKE '$factory'";
        }
        if (is_string($status) && $status !== 'ALL') {
            $a[] = "status LIKE '$status'";
        }
        if (is_array($status)) {
            $a[] = sprintf("status IN ('%s')", implode("', '" , $status));
        }

        return self::byConstraintsFollowers(implode(' AND ', $a), $userId);
    }

    public static function byFactoryStatus($factory, $status, $assignee = 'ALL')
    {
        $a = ['1=1'];
        $assignee = ($assignee === '') ? 'ALL' : $assignee;
        if ($assignee !== 'ALL') {
            $a[] = "assignee = $assignee";
        }
        if ($factory !== 'ALL') {
            $a[] = "factory_fullname LIKE '$factory'";
        }
        if (is_string($status) && $status !== 'ALL') {
            $a[] = "status LIKE '$status'";
        }
        if (is_array($status)) {
            $a[] = sprintf("status IN ('%s')", implode("', '" , $status));
        }

        return self::byConstraints(implode(' AND ', $a));
    }

    public static function byFactoryStatusReadyToClose($factory, $status, $assignee = 'ALL')
    {
        $a = ['is_ready_to_close=1'];
        $assignee = ($assignee === '') ? 'ALL' : $assignee;
        if ($assignee !== 'ALL') {
            $a[] = "assignee = $assignee";
        }
        if ($factory !== 'ALL') {
            $a[] = "factory_fullname LIKE '$factory'";
        }
        if (is_string($status) && $status !== 'ALL') {
            $a[] = "status LIKE '$status'";
        }
        if (is_array($status)) {
            $a[] = sprintf("status IN ('%s')", implode("', '" , $status));
        }

        return self::byConstraints(implode(' AND ', $a));
    }

    public static function byFactoryStatusWithoutComments($factory, $status, $assignee = 'ALL')
    {
        $a = ['1=1'];
        $assignee = ($assignee === '') ? 'ALL' : $assignee;
        if ($assignee !== 'ALL') {
            $a[] = "assignee = $assignee";
        }
        if ($factory !== 'ALL') {
            $a[] = "factory_fullname LIKE '$factory'";
        }
        if (is_string($status) && $status !== 'ALL') {
            $a[] = "status LIKE '$status'";
        }
        if (is_array($status)) {
            $a[] = sprintf("status IN ('%s')", implode("', '" , $status));
        }

        $a = implode(' AND ', $a);

        $WHERE = $LIMIT = '';
        // Look for constraints
        $HAVING = is_array($a) ? 'HAVING ' . tldUtils::constructWhere($a) : "HAVING $a";

        $ORDERBY = 'ORDER BY fweight DESC';

        // Construct query
        $SELECT = <<<EOF
SELECT
    pdc.*,
    locations.location AS factory_fullname,
    locations.erp AS factory_erp,
    CONCAT(poster.firstname, ' ', poster.lastname) AS poster_fullname,
    CONCAT(initiator.firstname, ' ', initiator.lastname) AS initiator_fullname,
    CONCAT(assignee.firstname, ' ', assignee.lastname) AS assignee_fullname,
    initiator.email AS initiator_email,
    IF(pdc.status='SUSPENDED',
        PERIOD_DIFF(DATE_FORMAT(DATE_SUB(date_suspended, INTERVAL days_suspended DAY), '%Y%m'), DATE_FORMAT(pdc.date, '%Y%m')),
        PERIOD_DIFF(DATE_FORMAT(DATE_SUB(now(), INTERVAL days_suspended DAY), '%Y%m'), DATE_FORMAT(pdc.date, '%Y%m'))
    ) AS monthsOpen,
    IF(pdc.status='SUSPENDED',
        ROUND(POW(1.4678, PERIOD_DIFF(DATE_FORMAT(DATE_SUB(date_suspended, INTERVAL days_suspended DAY), '%Y%m'),DATE_FORMAT(pdc.date, '%Y%m'))), 0),
        ROUND(POW(1.4678, PERIOD_DIFF(DATE_FORMAT(DATE_SUB(now(), INTERVAL days_suspended DAY), '%Y%m'),DATE_FORMAT(pdc.date, '%Y%m'))), 0)
    ) AS dfactor,
    CASE
        WHEN pdc.status='CLOSED'
            THEN final_fweight
        WHEN pdc.status='SUSPENDED'
            THEN ROUND(ifactor * POW(1.4678, PERIOD_DIFF(DATE_FORMAT(DATE_SUB(date_suspended, INTERVAL days_suspended DAY), '%Y%m'),DATE_FORMAT(pdc.date, '%Y%m'))), 0)
        ELSE
            ROUND(ifactor * POW(1.4678, PERIOD_DIFF(DATE_FORMAT(DATE_SUB(now(), INTERVAL days_suspended DAY), '%Y%m'),DATE_FORMAT(pdc.date, '%Y%m'))), 0)
    END AS fweight
EOF;
        $FROM = <<<EOF
FROM
    demerit AS pdc
    LEFT JOIN locations ON locations.id=pdc.factory
    LEFT JOIN people AS initiator ON initiator.id=pdc.initiator
    LEFT JOIN people AS poster ON poster.id=pdc.poster
    LEFT JOIN people AS assignee ON assignee.id=pdc.assignee
EOF;
        $query = <<<EOF
$SELECT
$FROM
$WHERE
$HAVING
$ORDERBY
$LIMIT
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byFactoryOpenStatusWithNoOpenTasks($factory, $status, $assignee = 'ALL')
    {
        $a = [
            "status NOT IN('REJECTED','CLOSED')",
            "(SELECT COUNT(*) FROM tasks WHERE status!='CLOSED' AND module LIKE 'PDC' AND pdc.id=tasks.parent_id)=0",
        ];
        if ($factory !== 'ALL') {
            $a[] = "factory_fullname LIKE '$factory'";
        }
        if ($status !== 'ALL') {
            $a[] = "status LIKE '$status'";
        }
        $assignee = ($assignee === '') ? 'ALL' : $assignee;
        if ($assignee !== 'ALL') {
            $a[] = "assignee = $assignee";
        }

        return self::byConstraints(implode(' AND ', $a));
    }

    public static function getOpenTaskAssigneeList(bool $displayEmail = true)
    {
        $emailRequest = $displayEmail ? ", ' (', people.email, ')'" : "";
        $query = <<<EOF
SELECT *, CONCAT(firstname, ' ',lastname$emailRequest) AS fullname FROM people
WHERE id IN(SELECT assignee FROM tasks WHERE status!='CLOSED' and module LIKE 'PDC')
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function fweightByFieldByConstraints($field, $a = '1=1')
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $WHERE = is_array($a) ? tldUtils::constructWhere($a) : $a;
        $WHERE = !empty($WHERE) ? "AND $WHERE" : '';

        $query = <<<EOF
$SELECT,
    SUM(ROUND(
        CASE
            WHEN pdc.status='CLOSED'
                THEN final_fweight
            WHEN pdc.status='SUSPENDED'
                THEN ROUND(ifactor * POW(1.4678, PERIOD_DIFF(DATE_FORMAT(DATE_SUB(date_suspended, INTERVAL days_suspended DAY), '%Y%m'),DATE_FORMAT(pdc.date, '%Y%m'))), 0)
            ELSE
                ROUND(ifactor * POW(1.4678, PERIOD_DIFF(DATE_FORMAT(DATE_SUB(now(), INTERVAL days_suspended DAY), '%Y%m'),DATE_FORMAT(pdc.date, '%Y%m'))), 0)
        END,
        2
    )) AS total_fweight
$FROM
WHERE
    pdc.status NOT IN('CLOSED','REJECTED')
    $WHERE
GROUP BY
    $field
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getLatePdc($factory, $status)
    {
        $where = '';
        if ($factory !== null) {
            $where = 'AND pdc.factory = '.$factory;
        }
        if ($status !== null) {
            $where .= " AND pdc.status = '$status'";
        }

        $query = <<<EOF
SELECT
    pdc.id,
    pdc.status,
    location.location,
    log.comment,
    log.date
FROM
    demerit as pdc
LEFT JOIN mod_logs AS log ON log.parent_id=pdc.id AND log.module='PDC'
LEFT JOIN locations AS location ON location.id = pdc.factory
WHERE
    pdc.status NOT IN ('REJECTED', 'CLOSED', 'SUSPENDED')
    AND log.date IN (SELECT MAX(log.date) FROM mod_logs log WHERE log.parent_id=pdc.id AND log.module='PDC')
    AND DATEDIFF(DATE_FORMAT(NOW(), '%Y-%m-%d'), DATE_FORMAT(log.date, '%Y-%m-%d')) > 30
    $where
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Update all OPEN PDC focus weight history
     */
    public static function updateFocusWeightHistory()
    {
        $query = <<<EOF
INSERT INTO demerit_history (parent_id, date, fweight)
SELECT id, NOW(), IF(
    status='SUSPENDED',
    ROUND(ifactor * POW(1.4678, PERIOD_DIFF(DATE_FORMAT(DATE_SUB(date_suspended, INTERVAL days_suspended DAY), '%Y%m'),DATE_FORMAT(date, '%Y%m'))), 0),
    ROUND(ifactor * POW(1.4678, PERIOD_DIFF(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL days_suspended DAY), '%Y%m'),DATE_FORMAT(date, '%Y%m'))), 0)
) AS fweight
FROM demerit WHERE status NOT IN('CLOSED','REJECTED')
EOF;

        return tldUtils::sqlQuery($query);
    }

    /**
     * Update Demerit In Progress in the Mod_list table used by the KPI "PDC In Progress"
     */
    public static function updateKPIInProgress()
    {
        $query = <<<EOF
SELECT factory, COUNT(*) AS num
FROM demerit
WHERE status IN ('INVESTIGATION','ACTION')
GROUP BY factory
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);
        if (count($rows)) {
            foreach ($rows as $row) {
                $a = [
                    'parent_id' => 0,
                    'module' => 'PDC',
                    'list_name' => 'INPROGRESS',
                    'list_key' => $row['factory'],
                    'list_key2' => date('Y-m'),
                    'value' => $row['num'],
                ];
                $e = tldModList::insert($a);
                if (is_string($e)) {
                    error_log("ERROR: tldPDC::updateKPIInProgress(), could not insert -> $e");
                }
            }
        }

        return;
    }

    public static function averagePdcDaysInPending(string $factoryId): array
    {
        $query = <<<EOF
SELECT
    ROUND(
        SUM(DATEDIFF( DATE_FORMAT(mod_logs.date, '%Y-%m-%d'),DATE(demerit.date)))/COUNT(*)
        ) AS yval,
    DATE_FORMAT(demerit.date, '%Y-%m') AS xval,
    locations.location AS zval
FROM mod_logs
    LEFT JOIN demerit ON mod_logs.parent_id = demerit.id
    LEFT JOIN locations on locations.id = demerit.factory
WHERE module = 'PDC'
    AND PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(demerit.date, '%Y%m')) BETWEEN 0 AND 12 
    AND (mod_logs.comment like '%Status changed from PENDING to %')
    AND demerit.factory = $factoryId
group by xval
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function quantityPdcCreatedByMonth(string $factoryId): array
    {
        $query = <<<EOF
SELECT
    COUNT(*) as yval,
    DATE_FORMAT(demerit.date, '%Y-%m') AS xval,
    locations.location AS zval
FROM demerit
    LEFT JOIN locations on locations.id = demerit.factory
WHERE 
    PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(demerit.date, '%Y%m')) BETWEEN 0 AND 12 
    AND demerit.factory = $factoryId
group by xval
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getDemeritHistory($type = '', $param = '')
    {
        switch ($type) {
            case 'factory':
                $query = <<<EOF
SELECT DATE_FORMAT(T1.date, '%Y-%m') AS month_name,
    ROUND(SUM(fweight)/COUNT(DISTINCT T1.date),0) as fweight
FROM demerit_history AS T1 LEFT JOIN demerit ON demerit.id=T1.parent_id
    LEFT JOIN locations ON locations.id=demerit.factory
WHERE locations.erp=$param
    AND PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(T1.date, '%Y%m')) < 12
GROUP BY month_name
EOF;
                break;
            case 'factoryByWeek':
                $query = <<<EOF
SELECT DATE_FORMAT(T1.date, '%Y-%v') as week_name,
    SUM(fweight)/count(distinct T1.date) as fweight
FROM demerit_history AS T1 LEFT JOIN demerit ON demerit.id=T1.parent_id
    LEFT JOIN locations ON locations.id=demerit.factory
WHERE locations.erp=$param
    AND PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(T1.date, '%Y%m')) < 6
GROUP BY week_name
EOF;
                break;
            case 'previous12months':
                $query = <<<EOF
SELECT month(T1.date)  AS month_name,
    sum(fweight)/count(distinct T1.date)  AS fweight
FROM demerit_history AS T1
WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(T1.date, '%Y%m')) < 12
GROUP BY month_name
EOF;
                break;
            default:
                $query = <<<EOF
SELECT date, AVG(SUM(fweight)) as fweight
FROM demerit_history
GROUP BY date
EOF;
        }
        $rows = tldUtils::getSqlToAssocArray($query);

        return count($rows) ? $rows : [];
    }

    /*******************************************
     * VIEW methods
     *******************************************/

    public function getPrintVersion()
    {
        $report = new tldAssocTable(
            $this->getHeader(),
            [
                'id' => 'PDC#',
                'date' => 'Date',
                'poster_fullname' => 'Poster',
                'initiator_fullname' => 'Initiator',
                'date' => 'Date',
                'status' => 'Status',
                'factory_fullname' => 'Factory',
                'product_type' => 'Equipment Type',
                'model' => 'Equipment Mdel',
                'ifactor' => 'IF',
                'fweight' => 'FW',
                'short_desc' => 'Short description',
                'description' => 'Description',
                'containment_action' => 'Containment actions',
                'root_cause' => 'Final root cause',
                'corrective_action' => 'Corrective action',
                'preventive_action' => 'Preventive action',
                'resolution' => 'Resolution',
            ],
            [
                'title' => "PDC#{$this->itsID} Details",
                'links' => [
                    'id' => 'https://www.tld-gse.com/en/private/product_support/index.ps.php?m[0]=pdc&m[1]=view&id=',
                ],
            ]
        );

        return $report->fetch();
    }

}

class tldPI
{

    /**
     * @var int
     */
    public $itsID;

    /**
     * @var array
     */
    private $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    public function getID()
    {
        return $this->itsID;
    }

    public function refresh()
    {
        $this->itsHeader = $this->getHeader();
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public static function getUnits()
    {
        // LN units. Could be replace by a call to get all units
        $units = [
            'ANG' => 'ANG',
            'BOX' => 'BOX',
            'BTL' => 'BTL',
            'CM' => 'CM',
            'CS' => 'CS',
            'EA' => 'EA',
            'EA1' => 'EA1',
            'FOZ' => 'FOZ',
            'FT' => 'FT',
            'FT1' => 'FT1',
            'FT2' => 'FT2',
            'GAL' => 'GAL',
            'GR' => 'GR',
            'HR' => 'HR',
            'IN' => 'IN',
            'KG' => 'KG',
            'LB' => 'LB',
            'LIT' => 'LIT',
            'MI' => 'MI',
            'ML' => 'ML',
            'MTR' => 'MTR',
            'OZ' => 'OZ',
            'PA' => 'PA',
            'PCS' => 'PCS',
            'PK' => 'PK',
            'PR' => 'PR',
            'PT' => 'PT',
            'QT' => 'QT',
            'RL' => 'RL',
            'RL1' => 'RL1',
            'S' => 'S',
            'SET' => 'SET',
            'SF' => 'SF',
            'SHT' => 'SHT',
            'SI' => 'SI',
            'SM' => 'SM',
            'TB' => 'TB',
            'WIP' => 'WIP',
            ];

        $units += [
            'm' => 'm',//metre
            'mm' => 'mm',
            'l' => 'l',//litre
            'ml' => 'ml',
            'm3' => 'm3',
            'cm3' => 'cm3',
            'dm3' => 'dm3',
            'j' => 'j',//day
            'h' => 'h',//hour
            'min' => 'min',//minute
            's' => 's',//seconde
            't' => 't',//tonne
            'kg' => 'kg',
            'gramme' => 'g',
            'mg' => 'mg',
            'V' => 'V',//volt
            'W' => 'W',//Watt
            'A' => 'A',//ampere
            'mA' => 'mA',
            'T' => 'T',//Tesla
            '&#8486;' => '&#8486;',//ohm
            'M&#8486;' => 'M&#8486;',
            'm&#8486;' => 'm&#8486;',
            '&mu;&#8486;' => '&mu;&#8486;',//microohm
            'A.h' => 'A.h',
            'mAh' => 'mAh',
            '&deg;C' => '&deg;C',
            '&deg;F' => '&deg;F',
            'bar' => 'bar',
            'km/h' => 'km/h',
            'm/s' => 'm/s',
            'mm/s' => 'mm/s',
            'mph' => 'mph',
            'rpm' => 'rpm',
            'N' => 'N',//Newton
            'daN' => 'daN',
            'N.m' => 'N.m',
            'daNm' => 'daNm',
            'dm3/s' => 'dm3/s',
            'l/s' => 'l/s',
            '&mu;m' => '&mu;m',
            'mm/h' => 'mm/h',
            '&#37;' => '&#37;',//µ
            'Hz' => 'Hz',//Hertz
            'kHz' => 'kHz',
            'kVA' => 'kVA',//kilo volt amps
            'deg' => 'deg',
            'm/min' => 'm/min',
            'dbA' => 'dbA',
            'psi' => 'psi',
            'in.H2O'=> 'in.H2O',
            'lb/cu.ft'=> 'lb/cu.ft',
            'kg/m3'=> 'kg/m3',
            'lb/min'=> 'lb/min',
            'kg/s'=> 'kg/s',
            'kBtu/h'=> 'kBtu/h',
            'RT'=> 'RT',
            'kW'=> 'kW',
            'in.Hg'=> 'in.Hg',
            'mVAC' => 'mVAC',
            'mV' => 'mV',
            'PF' => 'PF',
            'AAC' => 'AAC',
            'VAC' => 'VAC',
            'THDi' => 'THDi',
            'VDC' => 'VDC',
            'ADC' => 'ADC',
            'm/s&sup2;' => 'm/s&sup2;'
        ];
        \ksort($units, SORT_STRING|SORT_FLAG_CASE);
        return $units;
    }

    public static function getPiFamilies($options = [])
    {
        if ($WHERE = tldUtils::constructWhere($options)) {
            $WHERE = ' WHERE ' . $WHERE;
        }

        $query = <<<SQL
SELECT family FROM pi_family
$WHERE
ORDER BY family ASC
SQL;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function getFiles()
    {
        $file = [];
        if ($this->itsHeader['attachment_en'] != '') {
            $file[] = $this->itsHeader['attachment_en'];
        }
        if ($this->itsHeader['attachment_fr'] != '') {
            $file[] = $this->itsHeader['attachment_fr'];
        }
        if ($this->itsHeader['attachment_zh'] != '') {
            $file[] = $this->itsHeader['attachment_zh'];
        }
        $files = implode(',', $file);
        $query = <<<EOF
SELECT * FROM file WHERE id IN ($files) ORDER BY id
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function verifyPN($pn)
    {
        $query = <<<SQL
(SELECT t_eitm FROM ttiedm010400 WHERE t_eitm='$pn')
UNION (SELECT t_eitm FROM ttiedm010220 WHERE t_eitm='$pn')
UNION (SELECT t_eitm FROM ttiedm010250 WHERE t_eitm='$pn')
SQL;

        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public static function factoriesMapping(): array
    {
        return [
            ['code' => 'mtl', 'name' => 'TLD MTL', 'erp' => 500,'edmErp' => 400],
            ['code' => 'sha', 'name' => 'TLD SHA', 'erp' => 640,'edmErp' => 400],
            ['code' => 'she', 'name' => 'TLD SHE', 'erp' => 420,'edmErp' => 400],
            ['code' => 'sor', 'name' => 'TLD DTV', 'erp' => 510,'edmErp' => 400],
            ['code' => 'stl', 'name' => 'TLD STL', 'erp' => 520,'edmErp' => 400],
            ['code' => 'win', 'name' => 'TLD WIN', 'erp' => 400,'edmErp' => 400],
            ['code' => 'wim', 'name' => 'TLD WIM', 'erp' => 410,'edmErp' => 400],
            ['code' => 'wux', 'name' => 'TLD WUX', 'erp' => 660,'edmErp' => 400],
            ['code' => 'leb', 'name' => 'TLD LEB', 'erp' => 570,'edmErp' => 400],
            ['code' => 'aer', 'name' => 'AERO Specialties', 'erp' => 250, 'edmErp' => 250],
            ['code' => 'pow', 'name' => 'TLD PV', 'erp' => 220, 'edmErp' => 220],
            ['code' => 'mai', 'name' => 'TLD MNI', 'erp' => 820, 'edmErp' => 820],
            ['code' => 'wol', 'name' => 'TLD WOL', 'erp' => 430, 'edmErp' => 430],
        ];
    }

    public static function getFactoriesList(): array
    {
        return array_column(self::factoriesMapping(), 'name', 'code');
    }

    public static function getOperation($opno)
    {
        $query = <<<SQL
(SELECT DISTINCT(t_opno) FROM ttirou102220 WHERE t_opno LIKE '$opno')
UNION
(SELECT DISTINCT(t_opno) FROM ttirou102250 WHERE t_opno LIKE '$opno')
UNION
(SELECT DISTINCT(t_opno) FROM ttirou102400 WHERE t_opno LIKE '$opno')
UNION
(SELECT DISTINCT(t_opno) FROM ttirou102410 WHERE t_opno LIKE '$opno')
UNION
(SELECT DISTINCT(t_opno) FROM ttirou102420 WHERE t_opno LIKE '$opno')
UNION
(SELECT DISTINCT(t_opno) FROM ttirou102500 WHERE t_opno LIKE '$opno')
UNION
(SELECT DISTINCT(t_opno) FROM ttirou102510 WHERE t_opno LIKE '$opno')
UNION
(SELECT DISTINCT(t_opno) FROM ttirou102520 WHERE t_opno LIKE '$opno')
UNION
(SELECT DISTINCT(t_opno) FROM ttirou102540 WHERE t_opno LIKE '$opno')
UNION
(SELECT DISTINCT(t_opno) FROM ttirou102570 WHERE t_opno LIKE '$opno')
UNION
(SELECT DISTINCT(t_opno) FROM ttirou102640 WHERE t_opno LIKE '$opno')
UNION
(SELECT DISTINCT(t_opno) FROM ttirou102660 WHERE t_opno LIKE '$opno')
SQL;

        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public static function byFilters($constraints, $erp)
    {
        // Construct constraints
        if (is_array($constraints)) {
            if ($constraints['t_item']) {
                $constraints['pn.t_item'] = $constraints['t_item'];
                unset($constraints['t_item']);
            }
            $sn = null;
            if ($constraints['sn'] ?? null) {
                $sn = $constraints['sn'];
                unset($constraints['sn']);
            }
            if ($factories = $constraints['factories'] ?? false) {
                $factoriesConstraint = '';
                foreach ($factories as $factory) {
                    $factoriesConstraint = sprintf('%s AND %s >= 1', $factoriesConstraint, $factory);
                }
                unset($constraints['factories']);
            }
            $where = tldUtils::constructWhere($constraints);
            if (null !== $sn) {
                $where .= " AND pi.id IN (SELECT parent_id FROM pi_questions_unit WHERE unit = '$sn')";
            }

            if (false !== $factories) {
                $where = sprintf('%s %s', $where, $factoriesConstraint);
            }
        } else {
            $where = $constraints;
        }
        if (!empty($where)) {
            $where = "AND $where";
        }

        $query = <<<SQL
            SELECT pi.*,pn.t_item as item
            FROM pi_questions AS pi
            LEFT JOIN pi_questions_pn_xref AS pn
            ON pi.id = pn.question_id
            WHERE 1=1
            $where
            GROUP BY pi.id
            ORDER BY t_opno, pn.t_item, model, position, pi.id;
SQL;
        $questions = tldUtils::getSqlToAssocArray($query);

//        $operation = array_unique(array_column($questions,'t_opno'));
//        $operation = trim(implode("','", array_unique($operation)));
//
//        $getDesc = <<<SQL
//            SELECT Crou.t_opno, Crou.t_tano , LTRIM(RTRIM(t_dsca)) as t_dsca FROM ttipcs023$erp AS Crou
//            LEFT JOIN ttirou003$erp AS task ON Crou.t_tano=task.t_tano
//            WHERE Crou.t_opno IN('$operation')
//            GROUP BY Crou.t_opno, Crou.t_tano , LTRIM(RTRIM(t_dsca))
//            ORDER BY Crou.t_opno;
//SQL;

        return ['questions' => $questions, 'descriptions' => []];
    }

    public function getHeader()
    {
        $query = <<<SQL
SELECT
    pi.*,
    CONCAT(people.firstname,' ',people.lastname) AS user_fullname,
    pn.t_item AS item
FROM pi_questions AS pi
LEFT JOIN people ON people.id = pi.entered_by
LEFT JOIN pi_questions_pn_xref AS pn ON pn.question_id = pi.id 
WHERE
    pi.id = $this->itsID
SQL;

        $result = tldUtils::getSqlToAssocArray($query);

        $question = current($result);
        $question['items'] = [];

        foreach ($result as $q) {
            $question['items'][] = $q['item'];
        }

        unset($question['item']);

        return $question;

    }

    public static function insert($a)
    {
        $fields = [
            'parent_id',
            't_opno',
            't_item',
            'model',
            'owner',
            'mtl',
            'sha',
            'she',
            'sor',
            'stl',
            'win',
            'wim',
            'wux',
            'leb',
            'aer',
            'pow',
            'mai',
            'wol',
            'subject_en',
            'subject_fr',
            'subject_zh',
            'position',
            'desc_en',
            'desc_fr',
            'desc_zh',
            'help_en',
            'help_fr',
            'help_zh',
            'attachment_en',
            'attachment_fr',
            'attachment_zh',
            'answer_type',
            'answer_unit',
            'component_sn',
            'match_list',
            'answer_max',
            'answer_min',
            'non_conformity',
            'entered_by',
            'updated_by',
            'create_mode',
            'dt_validity',
            'gt1',
            'gt3',
            'active',
        ];

        $items = $a['items'];
        $a['t_item'] = implode(',', $a['items']);
        $query = 'INSERT INTO pi_questions SET created_on=NOW(), ' . tldUtils::getSqlSet($a, $fields);
        $id1 = tldUtils::sqlInsert($query);


        foreach ($items as $item) {
            $item = TldDatabase::escape($item);
            $query = "INSERT INTO pi_questions_pn_xref VALUES (DEFAULT, $id1,'$item')";
            tldUtils::sqlInsert($query);
        }

        $a['t_item'] = implode(',', $items);

        $query = 'INSERT INTO pi_questions_logs SET ' . tldUtils::getSqlSet($a, $fields);
        $id2 = tldUtils::sqlInsert($query);

        $user = new tldUser($GLOBALS['PHP_AUTH_USER']);
        $query = "update pi_questions_logs set parent_id='" . $id1 . "' ,updated_on=now(), create_mode='Insert', updated_by='" . $user->getID() . "' where id=" . $id2;
        $id3 = tldUtils::sqlQuery($query);

        return $id1;
    }

    public static function delete($a, $r)
    {
        $fields = 'parent_id, t_opno, t_item, model, owner, mtl, sha, she, sor, stl, win, wux, leb, aer, pow, mai, wol, subject_en, subject_fr, subject_zh, position, desc_en, ';
        $fields .= 'desc_fr, desc_zh, help_en, help_fr, help_zh, attachment_en, attachment_fr, attachment_zh, answer_type, answer_unit, component_sn, match_list, ';
        $fields .= 'answer_max, answer_min, non_conformity, entered_by, updated_by, create_mode, gt1, gt3, active';

        $query = 'INSERT INTO pi_questions_logs (' . $fields . ') select ' . $fields . ' from pi_questions where id=' . $a;
        $id1 = tldUtils::sqlInsert($query);

        $user = new tldUser($GLOBALS['PHP_AUTH_USER']);
        $query = "update pi_questions_logs set parent_id='" . $a . "' ,updated_on=now(), create_mode='Delete', updated_by='" . $user->getID() . "', reason='" . $r . "' where id=" . $id1;
        $id2 = tldUtils::sqlQuery($query);

        $query = 'delete from pi_questions where id=' . $a;
        $id1 = tldUtils::sqlExecute($query);

        $query = "DELETE FROM pi_questions_pn_xref WHERE question_id = $a";
        tldUtils::sqlExecute($query);

        return $id1;
    }

    public function update($data, $fields = [])
    {
        if (empty($this->itsID)) {
            return 'Not object context!';
        }

        $data['t_item'] = implode(',', $data['items']);

        $SET = tldUtils::getSqlSet($data, $fields);
        $query = "UPDATE pi_questions SET updated_on=NOW(), $SET WHERE id=$this->itsID LIMIT 1;";
        $id1 = tldUtils::sqlQuery($query);

        $query = "DELETE FROM pi_questions_pn_xref WHERE question_id = $this->itsID;";
        tldUtils::sqlQuery($query);

        foreach ($data['items'] as $item) {
            $item = TldDatabase::escape($item);
            $query = "INSERT INTO pi_questions_pn_xref VALUES (DEFAULT, $this->itsID, '$item');";
            tldUtils::sqlInsert($query);
        }

        $fieldsList = '';
        foreach ($fields as $key1 => $value1) {
            $fieldsList .= $fields[$key1] . ', ';
        }
        $fieldsList = substr($fieldsList, 0, -2);

        $query = 'insert into pi_questions_logs (id, parent_id, ' . $fieldsList . ") select 'NULL', '" . $this->itsID . "', " . $fieldsList . ' from pi_questions where id=' . $this->itsID;
        $id2 = tldUtils::sqlInsert($query);

        $user = new tldUser($GLOBALS['PHP_AUTH_USER']);
        $query = "update pi_questions_logs set parent_id='" . $this->itsID . "' ,updated_on=now(), create_mode='Update', updated_by='" . $user->getID() . "' where id=" . $id2;
        $id3 = tldUtils::sqlQuery($query);

	    // Update attachment on pi_questions_unit
	    $attachments = [
		    'attachment_en' => $data['attachment_en'],
		    'attachment_fr' => $data['attachment_fr'],
		    'attachment_zh' => $data['attachment_zh'],
	    ];

	    $SET = tldUtils::getSqlSet($attachments);
	    $query = <<<EOF
UPDATE pi_questions_unit SET $SET WHERE parent_id=$this->itsID;
EOF;
	    tldUtils::sqlQuery($query);

        return $id1;
    }

    public static function questionsByConstraints($a, $opt = [''])
    {
        $WHERE = is_array($a) ? tldUtils::constructWhere($a) : $a;

        if (!empty($WHERE)) {
            $WHERE = "WHERE $WHERE";
        }

        // Look for options
        $SELECT = !empty($opt['select']) ? 'SELECT ' . $opt['select'] : 'SELECT * ';
        $ORDERBY = !empty($opt['orderBy']) ? 'ORDER BY ' . $opt['orderBy'] : '';
        $LIMIT = !empty($opt['limit']) ? 'LIMIT ' . $opt['limit'] : '';
        $GROUPBY = !empty($opt['groupBy']) ? 'GROUP BY ' . $opt['groupBy'] : '';

        $query = <<<SQL
$SELECT
FROM pi_questions piq
$WHERE
$GROUPBY
$ORDERBY
$LIMIT
SQL;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * @param $sn string The serial number of the unit you want to clean
     * @return string | null
     */
    public static function removeDuplicateQuestions($sn) {
        $query = <<<SQL
UPDATE pi_questions_unit
SET active = 'N'
WHERE unit = '{$sn}'
AND id NOT IN
  (
      SELECT MIN(id)
      FROM pi_questions_unit
      WHERE unit = '{$sn}' AND active = 'Y'
      GROUP BY parent_id, t_item
  )
AND id NOT IN
  (
      SELECT parent_id
      FROM pi_answers
  );
SQL;
        return tldUtils::sqlQuery($query);
    }

    public static function insertQuestion(tldEquipment $er, $questionId) {
        $sn = $er->getSN();
        $query = <<<SQL
INSERT INTO pi_questions_unit (parent_id,
unit,
comp,
t_cprj,
t_pdno,
t_opno,
t_item,
model,
owner,
mtl, sha, she, sor, stl, win, wim, wux, leb, aer, pow, mai, wol
subject_en, subject_fr, subject_zh,
desc_en, desc_fr, desc_zh,
position,
help_en, help_fr, help_zh,
attachment_en, attachment_fr, attachment_zh,
answer_type,
answer_unit,
component_sn,
match_list,
answer_max,
answer_min,
non_conformity,
created_on,
entered_by,
updated_on,
updated_by,
create_mode,
gt1,
gt3,
active,
dt_validity, dt_expiration, alert)
    (SELECT id/*parent_id*/,
    '{$sn}' /*unit*/,
    {$er->getFactoryERP()} /*comp*/,
    (SELECT service.t_prno FROM service where sn = '{$sn}'),
    (SELECT service.t_pdno FROM service where sn = '{$sn}'),
    t_opno,
    t_item,
    model,
    owner,
    mtl,
    sha,
    she,
    sor,
    stl,
    win,
    wim,
    wux,
    leb,
    aer,
    pow,
    mai, 
    wol,
    subject_en,
    subject_fr,
    subject_zh,
    desc_en,
    desc_fr,
    desc_zh,
    position,
    help_en,
    help_fr,
    help_zh,
    attachment_en,
    attachment_fr,
    attachment_zh,
    answer_type,
    answer_unit,
    component_sn,
    match_list,
    answer_max,
    answer_min,
    non_conformity,
    created_on,
    0 /*entered_by*/ ,
    '0000-00-00' /*updated_on*/ ,
    0 /*updated_by*/,
    'insertApp' /*create_mode*/,
    gt1,
    gt3,
    active,
    '0000-00-00' /*dt_validity*/,
    '0000-00-00' /*dt_expiration*/,
    '' /*alert*/
     FROM pi_questions
     WHERE id = $questionId);
SQL;

        return tldUtils::sqlQuery($query);
    }

    public static function getEmailTemplateForInsertedQuestion(array $CQs): string
    {
        if (empty($CQs)){
            return "No Questions inserted";
        }
        $factoryList = tldPI::getFactoriesList();

        $visu = '<table border="1" cellspacing="0" cellpadding="5" style="border-collapse: collapse; width: 100%;">';
        $visu .= '<thead>';
        $visu .= '<tr style="background-color: #2971A8; color: white; text-align: center;">';
            $visu .= '<th>ID#</th>';
            $visu .= '<th>Operation</th>';
            $visu .= '<th>P/N</th>';
            $visu .= '<th>Model</th>';
            $visu .= '<th>Owner</th>';
            foreach ($factoryList as $code => $name) {
                $visu .= '<th>'.$code.'</th>';
            }
            $visu .= '<th>Position</th>';

            $visu .= '<th>Subject (EN)</th>';
            $visu .= '<th>Subject (FR)</th>';
            $visu .= '<th>Subject (ZH)</th>';

            $visu .= '<th>Description (EN)</th>';
            $visu .= '<th>Description (FR)</th>';
            $visu .= '<th>Description (ZH)</th>';

        $visu .= '</tr>';
        $visu .= '</thead>';

        $visu .= '<tbody>';
        foreach ($CQs as $CQ){

            $visu .= '<tr style="text-align: center;">';
            $visu .= '<td><a href="www.tld-gse.com/en/private/manufacturing/index.php?m[0]=pi&m[1]=view&m[2]=edit&id='.$CQ['parent_id'].'">'.$CQ['parent_id'].'</a></td>';
            $visu .= '<td>'.$CQ['t_opno']       .'</td>';
                $visu .= '<td>'.$CQ['t_item']       .'</td>';
                $visu .= '<td>'.$CQ['model']        .'</td>';
                $visu .= '<td>'.$CQ['owner']        .'</td>';
                foreach (tldPI::getFactoriesList() as $code => $name) {
                    $visu .= '<td>'.$CQ[$code].'</td>';
                }
                $visu .= '<td>'.$CQ['position'].'</td>';
                $visu .= '<td>'.$CQ['subject_en'].'</td>';
                $visu .= '<td>'.$CQ['subject_fr'].'</td>';
                $visu .= '<td>'.$CQ['subject_zh'].'</td>';
                $visu .= '<td style="font-size: 12px;">'.$CQ['desc_en'].'</td>';
                $visu .= '<td style="font-size: 12px;">'.$CQ['desc_fr'].'</td>';
                $visu .= '<td style="font-size: 12px;">'.$CQ['desc_zh'].'</td>';
            $visu .= '</tr>';
        }
        $visu .= '</tbody>';

        $visu .='</table>';

        return $visu;
    }

    public static function notifyOnInsertQuestionByXLSX(array $models, array $piQuestionLogsId, $user)
    {
        if (empty($models) || empty($piQuestionLogsId)) {
            return;
        }

        // we retrieve all the questions_log
        $piQuestionLogs = "'".implode("','", $piQuestionLogsId)."'";
        $query = "
        SELECT pl.*
        FROM pi_questions_logs pl
        WHERE id IN ($piQuestionLogs)
    ";
        $piQuestionLogs = tldUtils::getSqlToAssocArray($query);

        // we retrieve the factories linked to the models
        $models = "'".implode("','", array_unique($models))."'";
        $query = "select factory, family from pi_family_matrix where family IN ({$models})";
        $factoryModelLinks = tldUtils::getSqlToAssocArray($query);
        $questionsByFactory = [];

        // Create a correspondence between factory and model (family)
        $factoriesByModel = [];
        foreach ($factoryModelLinks as $link) {
            $factoriesByModel[$link['family']][] = $link['factory'];
        }

        // Browse question logs and group by factory
        foreach ($piQuestionLogs as $questionLog) {
            $model = $questionLog['model'];
            $questionLogId = $questionLog['id'];

            if (isset($factoriesByModel[$model])) {
                foreach ($factoriesByModel[$model] as $factory) {
                    $questionsByFactory[$factory][$questionLogId] = $questionLog;
                }
            }
            //needed for the CMO
            $questionsByFactory["ALVEST"][$questionLogId] = $questionLog;
        }

        // By factory we want to send the inserted questions by email
        foreach ($questionsByFactory as $factoryName => $questionLogs) {
            $models = [];
            foreach ($questionLogs as $questionLog) {
                $models[] = $questionLog['model'];
            }

            $body = tldPi::getEmailTemplateForInsertedQuestion($questionLogs);

            $modelsImplode = implode(", ", array_unique($models));
            $subject = "CQ injection with XLSX file  ".$modelsImplode.' ('.$user->getFullname().')';

            $email2 = "<html><body>";
            $email2 .= $subject.'<br><br>';
            $email2 .= $body;
            $email2 .= "</body></html>";
            $assignee = tldPi::getAssigneeForQuestionInserted($factoryName, $user, $questionLogs);
            tldUtils::emailAttachment($assignee,'noreply@tld-gse.com',$subject,$email2);

        }
    }

    public static function getAssigneeForQuestionInserted(string $factory, $user, array $questionLogs): array
    {
        // Notify involved people
        $owners = [];
        foreach ($questionLogs as $questionLog) {
            $owners[] = $questionLog['owner'];
        }
        if (!empty(array_intersect($owners, ['QCQ', 'PCQ', 'ECQ']))) {
            $functionList="'QAM', 'MPE', 'CMO'";
        }

        if (!empty(array_intersect($owners, ['ECQ']))) {
            $functionList.= ", 'EM'";
        }

        $query = "SELECT lastname, firstname, locations.location, email from people 
                  LEFT JOIN tld_functions AS fct ON fct.id=people.fct_id 
                  LEFT JOIN locations ON locations.id=people.bu_id 
                  where people.hidden!=1 AND people.disabled='N' and fct.code in (".$functionList.") and locations.location = '".$factory."'";

        foreach ($questionLogs as $questionLog) {
            if (!empty(array_intersect($owners, ['ECQ']))) {
                $query .= " union select lastname, firstname, locations.location, email from people 
                            LEFT JOIN locations ON locations.id=people.bu_id 
                            where people.hidden!=1 and locations.location = '" . $factory . "'
                             and people.id in (select parent_id from people_groups where group_name='pi_PILOT_" . $questionLog['model'] . "') ";
            }
        }
        $query .= " GROUP BY people.id";
        $assignees = tldUtils::getSqlToAssocArray($query);

        $location = tldLocation::byLocationName($factory);
        $mpe = new tldGroup('ROLE_MPE', $location['erp']);
        $assigneeList[] = $mpe->getEmailList();

        $assigneeList = array_column($assignees, 'email');
        $assigneeList[] = $user->getEmail();

        return $assigneeList;
    }

}

class tldWC
{

    public $itsID;
    public $itsFileDir = 'warranty_files';

    const NOTIFICATION_LOG = 'Parts notification sent to';

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    public function getID()
    {
        return $this->itsID;
    }

    public function getParentID()
    {
        return $this->itsHeader['parent_id'];
    }

    public function getFactoryID()
    {
        return $this->itsHeader['factory_id'];
    }

    public function getERSN()
    {
        return $this->itsHeader['serial_number'];
    }

    public function getERType()
    {
        return $this->itsHeader['type'];
    }

    public function getERModel()
    {
        return $this->itsHeader['model'];
    }

    public function refresh()
    {
        $this->itsHeader = $this->getHeader();
    }

    public function isEmpty()
    {
        $header = $this->getHeader();
        $detail = $this->getDetail();

        return empty($header) && empty($detail);
    }

    public function setERHourMeter($hours)
    {
        $er = new tldEquipment($this->getParentID());
        if ($er->isEmpty()) {
            return "ER#{$this->getParentID()} not found";
        }

        return $er->setHourMeter($hours, 'WC', $this->getID());
    }

    public function setFilteringFlag($statusFlag, $category = null)
    {
        return $this->update(
            [
                'filtering_flag' => $statusFlag,
                'dt_filtering_flag' => date('Y-m-d'),
                'category' => $category,
            ]
        );
    }

    /**
     * Get header information for this warranty claim
     *
     * @return array
     */
    public function getHeader()
    {
        $query = <<<EOF
SELECT
    CASE WHEN warranty_status='DISPUTE' THEN 'REJECTED'
    ELSE warranty_status
    END as wc_status,
    wc.*,
    locations.id AS factory_id
FROM warranty AS wc
    LEFT JOIN locations ON locations.location=wc.man_location
WHERE
    wc.id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public static function insert($a)
    {
        $fields = [
            'parent_id',
            'warranty_status',
            'entered_by',
            'claimant_details',
            'warranty_details',
            'customer_name',
            'claim_date',
            'type',
            'model',
            'man_location',
            'sales_org',
            'serial_number',
            'equipment_location',
            'hours',
            'problem_desc',
            'technician',
            'extranet_prob_desc',
            'failure_code1',
            'failure_code2',
            'intervention',
            'est_man_hours',
            'service_comments',
            'part_failing',
            'er_operation_status',
        ];
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO warranty SET filtering_flag='TO BE FILTERED',$SET";

        return tldUtils::sqlInsert($query);
    }

    public static function getTracking($erp, $packingslip)
    {
        $query = <<<EOF
SELECT
    CONCAT(courier,'@',trno) as tracking_no
FROM
    erp_dino_trno
WHERE
    erp =$erp AND dino LIKE '$packingslip'
ORDER BY id DESC
EOF;

        $rows = tldUtils::getSqlRowToAssocArray($query);

        return $rows['tracking_no'];
    }

    public static function getTrackingList($id)
    {
        $query = <<<EOF
SELECT
    *
FROM
    warranty_tracking
WHERE
    parent_id=$id
ORDER BY id DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function insertFile($a)
    {
        $fields = [
            'parent_id',
            'date',
            'description',
            'filename',
        ];
        $query = <<<EOF
INSERT INTO warranty_files
    SET
EOF;
        $query .= tldUtils::getSqlSet($a, $fields);

        return tldUtils::sqlInsert($query);
    }

    /**
     * Generic WC update method
     *
     * @param $data   array of WC datas
     * @param $fields array of WC fields to update
     *
     * @return string on error
     */
    public function update($data, $fields = '')
    {
        if (empty($this->itsID)) {
            return 'Not object context!';
        }
        $SET = tldUtils::getSqlSet($data, $fields);

        $query = "UPDATE warranty SET $SET WHERE id={$this->itsID} LIMIT 1";

        return tldUtils::sqlQuery($query);
    }

    public static function updatePart($id, $data, $fields = '')
    {
        if (empty($id)) {
            return 'Not object context!';
        }
        $SET = tldUtils::getSqlSet($data, $fields);
        $query = "UPDATE warranty_parts SET $SET WHERE id=$id LIMIT 1";

        return tldUtils::sqlQuery($query);
    }

    /**
     * Generic VWC update method
     *
     * @param $data   array of sfr datas
     * @param $fields array of sfr fields
     *
     * @return string error or int
     */
    public function updateRecord($data, $fields)
    {
        if (empty($this->itsID)) {
            return 'Not object context!';
        }
        if (empty($data) || empty($fields)) {
            return 'Parameters invalid!';
        }
        // Sanity check regarding fields on data
        foreach ($fields as $field) {
            if (!isset($data[$field])) {
                return "Sanity check failed with field $field";
            }
        }
        // Construct query
        $SET = tldUtils::getSqlSet($data, $fields);
        $query = <<<EOF
            UPDATE warranty SET $SET
            WHERE id=$this->itsID
            LIMIT 1
EOF;

        return tldUtils::sqlQuery($query);
    }

    public function psmUpdate($data)
    {
        $fields = ['prod_man_comments'];

        return $this->updateRecord($data, $fields);
    }

    public function getTocID()
    {
        $query = "SELECT id FROM toc WHERE warranty_id = {$this->getID()} ";

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function addLogEntry($id, $comment)
    {
        $a['parent_id'] = $this->itsID;
        $a['module'] = 'WC';
        $a['poster'] = $id;
        $a['comment'] = $comment;

        return tldModLog::insert($a);
    }

    public function getLog()
    {
        return tldModLog::byParent($this->itsID, 'WC');
    }

    public function addLinkTo($module, $mid)
    {
        return tldModLink::insert('WC', $this->itsID, $module, $mid);
    }

    public function getLinksFromHere($module = '')
    {
        return tldModLink::byParent($this->itsID, 'WC', $module);
    }

    public function getLinksToHere($module = '')
    {
        return tldModLink::byItem($this->itsID, 'WC', $module);
    }

    public function getStatus()
    {
        return $this->itsHeader['warranty_status'];
    }

    public function getFilteringFlag()
    {
        return $this->itsHeader['filtering_flag'];
    }

    public static function getFilteringFlagList(): array
    {
        return ['TO BE FILTERED', 'FILTERED'];
    }

    public static function getStatusList(): array
    {
        return ['ACCEPTED', 'CONDITIONAL', 'DISPUTE', 'PENDING', 'REJECTED', 'SALES CONCESSION'];
    }

    public static function getOpenStatusList(): array
    {
        return ['PENDING', 'CONDITIONAL', 'DISPUTE'];
    }

    public static function getClosedStatusList(): array
    {
        return ['ACCEPTED', 'REJECTED', 'SALES CONCESSION'];
    }

    public function isClosed()
    {
        return in_array($this->getStatus(), self::getClosedStatusList(), true);
    }

    public function getStatusAllowed(?int $uid = null)
    {
        switch ($this->getStatus()) {
            case 'CONDITIONAL':
            case 'DISPUTE' :
            case 'PENDING':
                return ['ACCEPTED', 'REJECTED', 'SALES CONCESSION', 'CONDITIONAL'];
            case 'REJECTED':
                if (null !== $uid && (new tldUser($uid))->isInGroup(['role_PSM', 'role_PSE', 'role_COO', 'gg_ADMIN'])) {
                    return ['ACCEPTED', 'SALES CONCESSION', 'PENDING'];
                }
                return ['ACCEPTED', 'SALES CONCESSION'];
            case 'ACCEPTED' :
                if (null !== $uid && (new tldUser($uid))->isInGroup(['role_PSM', 'role_PSE', 'role_COO', 'gg_ADMIN'])) {
                    return ['PENDING'];
                }
                return [];
        }

        return;
    }

    public function changeStatus($status, $uid)
    {
        if (empty($this->itsID)) {
            return 'Not in object context';
        }
        // Check status
        $allowed = $this->getStatusAllowed($uid);
        if (!in_array($status, (array)$allowed)) {
            return "Status $status not allowed";
        }
        // Get user data
        $user = new tldUser($uid);
        if (!$user->isValid()) {
            return "User ID $uid not found";
        }
        $email = $user->getEmail();
        $query = <<<EOF
        UPDATE
        	warranty
        SET
        	warranty_status='$status',
        	prod_man_accept_date=CURDATE(),
        	prod_man_accept_user='$email'
        WHERE
        	id=$this->itsID
EOF;

        return tldUtils::sqlQuery($query);
    }

    /**
     * return array of parts, empty array if none
     *
     * @return array
     */
    public function getDetail()
    {
        $query = <<<EOF
SELECT *, id AS wcpart_id
FROM warranty_parts
WHERE parent_id=$this->itsID
ORDER BY id
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get related tasks to wc
     *
     * @param string $status
     *
     * @return array
     */
    public function getTasks($status = 'ALL')
    {
        if (!in_array($status, ['ALL', 'OPEN'])) {
            return;
        }

        return tldTask::byParent($this->itsID, 'WC', $status);
    }

    /**
     * return a single part
     *
     * @return array
     */
    public static function getPartByID($id)
    {
        $query = <<<EOF
SELECT *
FROM warranty_parts
WHERE id=$id
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get list of parts being claimed
     *
     * @return array
     */
    public function getParts()
    {
        return $this->getDetail();
    }

    /**
     * Get the customer name of wc
     *
     * @return string
     */
    public function getCustomerName()
    {
        return $this->itsHeader['customer_name'];
    }

    /**
     * Get distinct list of factories from wc system
     *
     * @return array
     */
    public static function getFactoryList()
    {
        $query = <<<EOF
SELECT DISTINCT(man_location)
FROM warranty
ORDER BY man_location
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function deleteFile($id)
    {
        $id = (int)$id;
        $query = <<<EOF
        DELETE FROM warranty_files
        WHERE id = $id
EOF;
        tldUtils::sqlQuery($query);
    }

    /**
     * Get related files from mod_files system
     *
     * @return array
     */
    public function getFiles()
    {
        $query = <<<EOF
SELECT *
FROM warranty_files
WHERE parent_id=$this->itsID
ORDER BY id
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getFileByID($id)
    {
        $query = <<<EOF
SELECT *
FROM warranty_files
WHERE id=$id
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public static function updateVisibilityOfFileByID($id)
    {
        $query = <<<EOF
UPDATE warranty_files 
    SET public = CASE
        WHEN public = 1
        THEN public = 0
        ELSE 1
    END
WHERE id=$id
EOF;

        return tldUtils::sqlQuery($query);
    }


    public function getLinks()
    {
        return tldModLink::byParent($this->itsID, 'WC');
    }

    /**
     * Send file to output buffer directly
     *
     * @param integer $id id of file in mod_files
     */
    public function outFile($id)
    {
        $files = $this->getFiles();
        foreach ($files as $file) {
            if ($file['id'] == $id) {
                $myFile = new basicFile(tldUtils::getPathToUploadFile($this->itsFileDir, $file['filename']));
                $myFile->outFile();
            }
        }
    }

    //reports

    /**
     * Get count of wc by period
     *
     * @param string $startDate
     * @param string $endDate
     * @param string $groupBy
     *
     * @return array
     */
    public static function getCountByPeriod($startDate = '', $endDate = '', $groupBy = '')
    {
        $query = <<<EOF
SELECT COUNT(*),
    month(claim_date) AS claim_month,
    year(claim_date) AS claim_year
FROM warranty
WHERE claim_date BETWEEN $startDate AND $endDate
GROUP BY $groupBy
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Process conditionnal WC
     * If Past 2 months:
     * -> Update to REJECTED
     * -> Send notification to creator and factory
     * -> cc CSM and EVP
     */
    public static function emailCondPast2Months()
    {
        $query = <<<EOF
SELECT * FROM warranty
WHERE
    warranty_status='CONDITIONAL'
  AND claim_date > '2005-09-01'
  AND PERIOD_DIFF(
      DATE_FORMAT(NOW(),'%Y%m'),
      DATE_FORMAT((
        SELECT MAX(date)
        FROM mod_logs
        WHERE mod_logs.parent_id = warranty.id
          AND module='WC'
          AND comment LIKE 'CONDITIONAL%'
      ),'%Y%m')
      ) >= 2
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);
        if (!count($rows)) {
            return;
        }
        // Process list of WC
        foreach ($rows as $row) {
            $id = $row['id'];
            // Update status
            $query = "UPDATE warranty SET warranty_status='REJECTED' WHERE id=$id LIMIT 1";
            tldUtils::sqlQuery($query);
            // Create a log
            $warranty= new tldWC($id);
            $warranty->addLogEntry(0, 'WC was automatically put back to REJECTED as it has been outstanding as CONDITIONAL, and WC is open since 2 months');
            // Send notification
            $subject = "Conditional Warranty #$id is more than 2 months old";
            $message = <<<EOF
<p>TLD Warranty Claim #$id has a CONDITIONAL status and is more than 2 months old.<br>
Persuant to TLD Group Rules (excerpt given below), this warranty will be automatically marked as REJECTED.</p>
<p><a href="https://www.tld-gse.com/en/private/product_support/wc/wc_admin.php?mode=record_view&form_type=main_tpl&id=$id">
Click here to see warranty online.</a>
</p>
<p>"When a WC has been declared CONDITIONAL, it becomes the responsibility of the Sales Entity
to either immediately challenge this status, in case it would be unreasonable, or to provide back the
requested part or information in a 2 months timeframe. After these 2 months, in absence of proper
feedback from the Sales Entity , the WC status is automatically changed to REJECTED with email
warning to the WC originator and to the PSM. In this case, the factory is entitled to invoice to the
Sales entity all direct costs related to this WC incurred by the factory."</p>
EOF;
            $to = [$row['entered_by'], $row['prod_man_accept_user']];
            $ssoERP = tldLocation::getERPByLocation($row['sales_org']);
            $warrantyFactory = tldLocation::getERPByID($warranty->itsHeader['factory_id']);
            $ccRaw = tldGroup::getUserListByMultipleGroup(['role_EVP', 'role_CSM'], $ssoERP);
            $psm = tldGroup::getUserListByMultipleGroup(['role_PSM'], $warrantyFactory);
            $cc = [];
            foreach (array_merge($ccRaw, $psm)  as $val) {
                $cc[] = $val['email'];
            }
            tldUtils::emailAttachment(
                $to,
                'noreply@tld-gse.com',
                $subject,
                $message,
                null,
                $cc
            );
        }
    }

    /**
     * Process conditionnal WC
     * If 6 weeks past due:
     * -> Send notification to creator and factory
     * -> cc CSM and EVP
     */
    public static function emailCondPast6Weeks()
    {
        $query = <<<EOF
SELECT * FROM warranty
WHERE
    warranty_status='CONDITIONAL'
    AND claim_date > '2005-09-01'
    AND DATEDIFF(DATE_FORMAT(NOW(),'%Y%m%d'),DATE_FORMAT(claim_date,'%Y%m%d')) = 45
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);
        if (!count($rows)) {
            return;
        }
        // Process list of WC
        foreach ($rows as $row) {
            $id = $row['id'];
            // Send notification
            $subject = "Conditional Warranty #$id is more than 6 weeks old";
            $message = <<<EOF
<p>TLD Warranty Claim #$id has a CONDITIONAL status and is more than 6 weeks old.<br>
Persuant to TLD Group Rules (excerpt given below), this warranty will be automatically marked as REJECTED within the next 2 Weeks.</p>
<p><a href="https://www.tld-gse.com/en/private/product_support/wc/wc_admin.php?mode=record_view&form_type=main_tpl&id=$id">
Click here to see warranty online.</a>
</p>
<p>"When a WC has been declared CONDITIONAL, it becomes the responsibility of the Sales Entity
to either immediately challenge this status, in case it would be unreasonable, or to provide back the
requested part or information in a 2 months timeframe. After these 2 months, in absence of proper
feedback from the Sales Entity , the WC status is automatically changed to REJECTED with email
warning to the WC originator and to the PSM. In this case, the factory is entitled to invoice to the
Sales entity all direct costs related to this WC incurred by the factory."</p>
EOF;
            $to = [$row['entered_by'], $row['prod_man_accept_user']];
            $ssoERP = tldLocation::getERPByLocation($row['sales_org']);
            $ccRaw = tldGroup::getUserListByMultipleGroup(['role_EVP', 'role_CSM'], $ssoERP);
            $cc = [];
            foreach ($ccRaw as $val) {
                $cc[] = $val['email'];
            }
            tldUtils::emailAttachment(
                $to,
                'noreply@tld-gse.com',
                $subject,
                $message,
                null,
                $cc
            );
        }
    }

    /**
     * Process dispute WC
     * If Past 2 months:
     * -> Update to ACCEPTED
     * -> Send notification to creator and factory
     * -> cc CSM and EVP
     */
    public static function emailDisputePast2Months()
    {
        $query = <<<EOF
SELECT * FROM warranty
WHERE
    warranty_status='DISPUTE'
    AND PERIOD_DIFF(
        DATE_FORMAT(NOW(),'%Y%m'),
        DATE_FORMAT(claim_date,'%Y%m')
    ) > 2
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);
        if (!count($rows)) {
            return;
        }

        // Send notification
        foreach ($rows as $row) {
            $id = $row['id'];
            $wc = new tldWC($id);
            // Update status
            $wc->update(['warranty_status' => 'ACCEPTED']);
            $subject = "Dispute Warranty #$id is more than 2 months old";
            $message = <<<EOF
<p>TLD Warranty Claim #$id has a DISPUTE status and is more than 2 months old.<br>
Persuant to TLD Group Rules (excerpt given below), this warranty will be automatically marked as DISPUTE.</p>
<p><a href="https://www.tld-gse.com/en/private/product_support/index.ps.php?m[0]=wc&m[1]=view&id=$id">
Click here to see warranty online.</a>
</p>
<p>"When a WC has been declared DISPUTE, it becomes the responsibility of the Sales Entity
to either immediately challenge this status, in case it would be unreasonable, or to provide back the
requested part or information in a 2 months timeframe. After these 2 months, in absence of proper
feedback from the Sales Entity , the WC status is automatically changed to ACCEPTED with email
warning to the WC originator and to the PSM. In this case, the factory is entitled to invoice to the
Sales entity all direct costs related to this WC incurred by the factory."</p>
EOF;
            $to = [$row['entered_by'], $row['prod_man_accept_user']];
            $ssoERP = tldLocation::getERPByLocation($row['sales_org']);
            $ccRaw = tldGroup::getUserListByMultipleGroup(['role_EVP', 'role_CSM'], $ssoERP);
            $cc[] = array_column($ccRaw, 'email');

            tldUtils::emailAttachment(
                $to,
                'noreply@tld-gse.com',
                $subject,
                $message,
                null,
                $cc
            );
        }
    }

    /**
     * Process dispute WC
     * If 6 weeks past due:
     * -> Send notification to creator and factory
     * -> cc CSM and EVP
     */
    public static function emailDisputePast6Weeks()
    {
        $query = <<<EOF
SELECT * FROM warranty
WHERE
    warranty_status='DISPUTE'
    AND DATEDIFF(DATE_FORMAT(NOW(),'%Y%m%d'),DATE_FORMAT(claim_date,'%Y%m%d')) = 45
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);
        if (!count($rows)) {
            return;
        }
        // Process list of WC
        foreach ($rows as $row) {
            $id = $row['id'];
            // Send notification
            $subject = "Dispute Warranty #$id is more than 6 weeks old";
            $message = <<<EOF
<p>TLD Warranty Claim #$id has a DISPUTE status and is more than 6 weeks old.<br>
Persuant to TLD Group Rules (excerpt given below), this warranty will be automatically marked as ACCEPTED within the next 2 Weeks.</p>
<p><a href="https://www.tld-gse.com/en/private/product_support/index.ps.php?m[0]=wc&m[1]=view&id=$id">
Click here to see warranty online.</a>
</p>
<p>"When a WC has been declared DISPUTE, it becomes the responsibility of the Sales Entity
to either immediately challenge this status, in case it would be unreasonable, or to provide back the
requested part or information in a 2 months timeframe. After these 2 months, in absence of proper
feedback from the Sales Entity , the WC status is automatically changed to ACCEPTED with email
warning to the WC originator and to the PSM. In this case, the factory is entitled to invoice to the
Sales entity all direct costs related to this WC incurred by the factory."</p>
EOF;
            $to = [$row['entered_by'], $row['prod_man_accept_user']];
            $ssoERP = tldLocation::getERPByLocation($row['sales_org']);
            $ccRaw = tldGroup::getUserListByMultipleGroup(['role_EVP', 'role_CSM'], $ssoERP);
            $cc[] = array_column($ccRaw, 'email');

            tldUtils::emailAttachment(
                $to,
                'noreply@tld-gse.com',
                $subject,
                $message,
                null,
                $cc
            );
        }
    }

    public static function countToBeFilteredByFactory()
    {
        $query = <<<EOF
SELECT
    man_location,
    filtering_flag,
    COUNT(*) AS num
FROM
    warranty
WHERE
    filtering_flag LIKE 'TO BE FILTERED'
GROUP BY
    man_location,
    filtering_flag
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byFactoryFilteringFlag($factory, $filteringFlag, $model = ' ALL_MODELS', $status = 'ALL')
    {
        $a[] = '1=1';
        if ($factory !== 'ALL') {
            $a[] = "warranty.man_location LIKE '$factory'";
        }
        if ($filteringFlag !== 'ALL') {
            $a[] = "warranty.filtering_flag LIKE '$filteringFlag'";
        }
        if ($model !== ' ALL_MODELS') {
            $a[] = "warranty.model LIKE '$model'";
        }

        if ($status !== 'ALL') {
            $a[] = "warranty.warranty_status LIKE '$status'";
        }
        return self::byConstraints(implode(' AND ', $a));
    }

    /**
     * Get count of warranties by man_location and warranty_status
     *
     * @return array returns a 2 dim array of counts by erp and status
     */
    public static function countByFactoryStatus($scope = '')
    {
        $query = <<<EOF
SELECT man_location, warranty_status, count(*) as num
FROM warranty
GROUP BY man_location,warranty_status
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function countBySsoBystatus()
    {
        $query = <<<EOF
SELECT sales_org, warranty_status, count(*) as num
FROM warranty
WHERE warranty_status != 'ACCEPTED' AND sales_org != ''
GROUP BY sales_org,warranty_status
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get count of warranties by status and per ASM
     *
     * @return array returns a 1 dim array of counts by status
     */
    public static function countByERPStatusbyASM($asm, $sso)
    {
        if (!empty($sso)) {
            $ASMGrp = new tldGroup('role_ASM');
            $ASMList = array_column($ASMGrp->getUserlistBySSO($sso), 'id', 'id');
            $SalesAgentGrp = new tldGroup('gg_SALES_AGENTS');
            $SalesAgentList = array_column($SalesAgentGrp->getUserlistBySSO($sso), 'id', 'id');
            $list = implode(',', $ASMList + $SalesAgentList);
            $WHERE = " customers.asm_id IN ($list) ";
        } else {
            $WHERE = " customers.asm_id=$asm ";
        }
        $query = <<<EOF
SELECT
    man_location, warranty_status, count(*) as num
FROM warranty
    LEFT JOIN customers ON customers.customer_name=warranty.customer_name
WHERE $WHERE AND PERIOD_DIFF(DATE_FORMAT(NOW(),'%Y%m'),DATE_FORMAT(claim_date,'%Y%m')) BETWEEN 0 AND 12 AND man_location != ''
GROUP BY man_location, warranty_status
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get count of warranties by status and by SSO
     *
     * @return array returns a 1 dim array of counts by status
     */
    public function countBySSOStatus()
    {
        $query = <<<EOF
SELECT
    sales_org,
    warranty_status,
    count(*) as num
FROM warranty
WHERE warranty_status!='ACCEPTED'
GROUP BY sales_org, warranty_status
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get count of warranties by equipment_location and warranty_status
     *
     * @return array returns a 2 dim array of counts by equipment_location and status
     */
    public static function countByLocationStatus($options = '')
    {
        if (is_array($options)) {
            $WHERE = ' AND ' . tldUtils::constructWhere($options);
        } else {
            $WHERE = " WHERE $options";
        }

        $query = <<<EOF
SELECT
CASE WHEN airport_code='' THEN ' NO AIRPORT CODE'
    WHEN  airport_code IS NULL THEN ' NO AIRPORT CODE'
    ELSE airport_code
END as ap_code,
CASE WHEN warranty_status='DISPUTE' THEN 'REJECTED'
    ELSE warranty_status
END as wc_status,
count(*) as num
FROM warranty LEFT JOIN service on warranty.serial_number = service.sn
    $WHERE
GROUP BY ap_code, wc_status
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get warranties by man_location and warranty_status
     *
     * @return array returns array of demerit lines
     */
    public static function byERPStatus($erp = '', $status = '', $sortBy = '')
    {
        $where = '';
        if ($status !== 'ALL') {
            $where .= " AND warranty_status='$status' ";
        }
        if ($erp !== 'ALL') {
            $where .= " AND man_location='$erp' ";
        }
        if ($sortBy) {
            $sortBy = 'ORDER BY ' . TldDatabase::escape($sortBy) . ' ASC';
        } else {
            $sortBy = 'ORDER BY id DESC';
        }
        $query = <<<EOF
SELECT *
FROM warranty
WHERE 1
    $where
    $sortBy
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get warranties by ASM and warranty_status
     *
     * @return array returns array of demerit lines
     */
    public static function byERPStatusByASM($factory, $status, $asm, $sso)
    {
        $where = '';
        if (!empty($sso)) {
            $ASMGrp = new tldGroup('role_ASM');
            $ASMList = array_column($ASMGrp->getUserlistBySSO($sso), 'id', 'id');
            $list = implode(',', $ASMList);
            $ASM = " customers.asm_id IN ($list) ";
        } else {
            $ASM = " customers.asm_id=$asm ";
        }
        if ($factory !== 'ALL') {
            $where .= " AND man_location='$factory' ";
        }
        if ($status !== 'ALL') {
            $where .= " AND warranty_status='$status' ";
        }

        $query = <<<EOF
SELECT warranty.*
FROM warranty
    LEFT JOIN customers ON customers.customer_name=warranty.customer_name
WHERE $ASM AND PERIOD_DIFF(DATE_FORMAT(NOW(),'%Y%m'),DATE_FORMAT(claim_date,'%Y%m')) BETWEEN 0 AND 12
$where
ORDER BY warranty.id DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }


    public static function getConstraintsForPN(array $constraints)
    {
        $WHERE = '';
        if (is_array($constraints['models'])) {
            $WHERE .= sprintf(' AND service.model IN ("%s")', implode('", "', $constraints['models']));
        }

        if (is_array($constraints['types'])) {
            $WHERE .= sprintf(' AND service.type IN ("%s")', implode('", "', $constraints['types']));
        }

        if (is_array($constraints['factories'])) {
            $WHERE .= sprintf(' AND warranty.man_location IN ("%s")', implode('", "', $constraints['factories']));
        }

        if (isset($constraints['partNumbers'])) {
            if(is_string($constraints['partNumbers'])){
                $WHERE .= sprintf(' AND part_number LIKE ("%s")', $constraints['partNumbers']);
            }else{
                $WHERE .= sprintf(' AND part_number IN ("%s")', implode('", "', $constraints['partNumbers']));
            }
        }

        if (isset($constraints['groupBy'])) {
            $GROUP_BY = sprintf(' GROUP BY %s', $constraints['groupBy']);
        }

        $start = $constraints['start'];
        $end = $constraints['end'];

        $CONSTRAINTS = <<<EOF
FROM warranty
LEFT JOIN warranty_parts ON warranty.id = warranty_parts.parent_id
LEFT JOIN service ON warranty.parent_id = service.id
LEFT JOIN mod_links link ON warranty.id = link.parent_id AND link.module = 'WC' AND link.type = 'PDC'
LEFT JOIN demerit pdc ON link.item = pdc.id
WHERE warranty_parts.part_number != '' AND warranty.claim_date >='$start' AND warranty.claim_date <='$end'
AND warranty_status NOT IN ('REJECTED', 'SALES CONCESSION')
$WHERE
$GROUP_BY
EOF;

        return $CONSTRAINTS;
    }

    public static function byConstraintsTopPN(array $constraints)
    {
        $CONSTRAINTS = self::getConstraintsForPN($constraints);
        $query = <<<EOF
SELECT
    warranty_parts.part_number,
    warranty_parts.part_description,
    warranty.id,
    DATE_FORMAT(warranty.claim_date,'%Y-%m-%d') AS claim_date,
    warranty.man_location,
    warranty.sales_org,
    warranty.warranty_status,
    warranty.customer_name,
    service.type,
    service.model,
    service.sn,
    warranty.hours,
    warranty.problem_desc,
    DATE_FORMAT(service.dgt_act,'%Y-%m-%d') AS actual_gt_date,
    DATE_FORMAT(service.date_shipped,'%Y-%m-%d') AS date_shipped,
    pdc.status as pdc_status,
    pdc.id as pdc_id
$CONSTRAINTS
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byConstraintsTopPNCount(array $constraints)
    {
        $CONSTRAINTS = self::getConstraintsForPN($constraints);
        $limit = $constraints['top'];

        $query = <<<EOF
SELECT
    COUNT(*) AS pn_count,
    warranty_parts.part_number,
    warranty_parts.part_description
$CONSTRAINTS
GROUP BY warranty_parts.part_number
ORDER BY pn_count DESC
LIMIT $limit;
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function WCProcessTimeAfterCreation(string $startDate, string $endDate, string $factory)
    {
        $query = <<<EOF
SELECT
    COUNT(*) AS nb_warranty,
    WEEKOFYEAR(warranty.claim_date) AS week,
    AVG(DATEDIFF(warranty.prod_man_accept_date, warranty.claim_date)) AS days_to_process
FROM
    warranty
WHERE warranty.claim_date >= '$startDate' AND warranty.claim_date <= '$endDate' AND warranty.man_location = '$factory'
GROUP BY
    WEEKOFYEAR(warranty.claim_date) ASC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byConstraintsCount(array $constraints)
    {
        $CONSTRAINTS = self::getConstraintsForPN($constraints);

        $query = <<<EOF
SELECT COUNT(*) AS count 
$CONSTRAINTS
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    public static function WCPartCalculation($sso, $start, $end, $bu, $status, $option)
    {
        $WHERE = '';
        if (!empty($bu)) {
            $WHERE .= " AND warranty.man_location='$bu' ";
        }
        if (!empty($status)) {
            $WHERE .= " AND warranty.warranty_status='$status' ";
        }

        $GROUPBY = !empty($option) ? ' GROUP BY warranty.id' : '';

        $query = <<<EOF
SELECT warranty.*,
locations.erp,
warranty_parts.part_number,
warranty_parts.quantity
FROM warranty
LEFT JOIN warranty_parts ON warranty.id = warranty_parts.parent_id
LEFT JOIN locations ON warranty.sales_org = locations.location
WHERE warranty.sales_org = '$sso' AND DATE_FORMAT(prod_man_accept_date,'%Y-%m-%d') BETWEEN '$start' AND '$end'
$WHERE
$GROUPBY
ORDER BY warranty.id DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function WCStatusReport($sso, $start, $end, $bu, $status)
    {
        $WHERE = '';
        if (!empty($bu)) {
            $WHERE .= " AND warranty.man_location='$bu' ";
        }
        if (!empty($status)) {
            $WHERE .= " AND warranty.warranty_status='$status' ";
        }

        $query = <<<EOF
SELECT warranty.id,
       warranty.sales_org,
       warranty.man_location,
       warranty.customer_name,
       warranty.warranty_status,
       warranty.prod_man_accept_date,
       warranty_parts.part_number,
       warranty_parts.part_description,
       warranty_parts.quantity,
       warranty.parts_order_ref,
       locations.location,
       warranty_parts.part_number,
       warranty_parts.quantity,
       warranty_tracking. packing_slip,
       warranty_tracking.so_no,
       warranty_tracking.tracking_no
FROM warranty
LEFT JOIN warranty_parts ON warranty.id = warranty_parts.parent_id
LEFT JOIN locations ON warranty.sales_org = locations.location
LEFT JOIN warranty_tracking ON warranty.id = warranty_tracking.parent_id
WHERE warranty.sales_org = '$sso' AND DATE_FORMAT(prod_man_accept_date,'%Y-%m-%d') BETWEEN '$start' AND '$end'
$WHERE
ORDER BY warranty.id DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get warranties by ASM and warranty_status
     *
     * @return array returns array of demerit lines
     */
    public static function bySSOStatus($sso, $status)
    {
        $WHERE = '';
        if ($sso !== 'ALL') {
            $WHERE .= " AND sales_org='$sso' ";
        }
        if ($status !== 'ALL') {
            $WHERE .= " AND warranty_status='$status' ";
        }

        $query = <<<EOF
SELECT *
FROM warranty
WHERE warranty_status!='ACCEPTED'
$WHERE
ORDER BY id DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getSELECT(): string
    {
        return <<<EOF
SELECT
    wc.*,
    er.airport_code
EOF;
    }

    public static function getFROM(): string
    {
        return <<<EOF
FROM warranty AS wc
    LEFT JOIN service AS er ON wc.serial_number=er.sn
EOF;
    }

    /**
     * Get warranties by specified constraints
     *
     * @param $constraints
     * @param string $orderBy
     *
     * @return array returns array of demerit lines
     */
    public static function byConstraints($constraints, $orderBy = 'warranty.id')
    {
        $airportCode = $constraints['airport_code'] ?? null;
        $warrantyStatus = $constraints['wc_status'] ?? null;
        $customerName = $constraints['warranty.customer_name'] ?? null;

        $where = [];
        if (is_array($constraints)) {
            if ('ALL' !== $airportCode && ' NO AIRPORT CODE' !== $airportCode) {
                $where[] .= "airport_code LIKE '$airportCode'";
            } elseif (' NO AIRPORT CODE' === $airportCode) {
                $where[] .= "airport_code IS NULL OR airport_code LIKE ''";
            }
            if ('REJECTED' === $warrantyStatus) {
                $where[] .= "warranty_status IN ('DISPUTE', 'REJECTED')";
            } elseif('ALL' !== $warrantyStatus) {
                $where[] .= "warranty_status LIKE '$warrantyStatus'";
            }

            $where[] .= "warranty.customer_name LIKE '$customerName'";
            if (!empty($where)) {
                $where = implode(' AND ', $where);
            }
        } else {
            $where = $constraints;
        }

        $orderBy = TldDatabase::escape($orderBy);
        $query = <<<EOF
SELECT
CASE WHEN warranty_status='DISPUTE' THEN 'REJECTED'
    ELSE warranty_status
END as wc_status,
    warranty.*,
    service.airport_code
FROM
    warranty
LEFT JOIN service on warranty.serial_number = service.sn
WHERE $where
ORDER BY $orderBy
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get count of warranties by sales_org and claim year
     *
     * @return array returns a 2 dim array of counts by sales org and year
     */
    public static function countBySalesOrgYear($scope = '')
    {
        $query = <<<EOF
SELECT sales_org, YEAR(claim_date) AS claim_year, count(*) AS num
FROM warranty
GROUP BY sales_org, claim_year
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * get WCs for specific period
     *
     * @return array array of db rows
     */
    public static function byWCFiltering($start, $end, $options = '')
    {
        $where = '';
        $start = TldDatabase::escape($start);
        $end = TldDatabase::escape($end);
        switch ($options['compareDate']) {
            case 'service_accept_date':
            case 'prod_man_accept_date':
                $date = 't1.' . $options['compareDate'];
                break;
            default:
                if (is_array($options)) {
                    $where = ' AND ' . tldUtils::constructWhere($options);
                }
                $date = 't1.claim_date';
        }
        $query = <<<EOF
SELECT t1.*, er.date_shipped, log.comment, link.type AS module, link.item,er.sn,er.dgt_act, 
    PERIOD_DIFF(DATE_FORMAT(t1.claim_date,'%Y%m'),DATE_FORMAT(er.date_shipped,'%Y%m')) AS month_age
FROM warranty AS t1
  LEFT JOIN service AS er ON er.sn = t1.serial_number
  LEFT JOIN mod_logs AS log ON log.parent_id = t1.id
                               AND log.id=(select id from mod_logs where parent_id = log.parent_id  and module like 'WC' AND comment LIKE 'Filtering flag set to FILTERED%' ORDER BY date desc limit 1)
                               AND t1.filtering_flag LIKE 'FILTERED' AND log.module LIKE 'WC' 
  LEFT JOIN mod_links AS link ON link.parent_id = t1.id AND link.module like 'WC' AND t1.filtering_flag LIKE 'FILTERED'
WHERE
    $date BETWEEN '$start' AND '$end'
    $where
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byPeriod($start, $end, $options = '')
    {
        $where = '';
        $start = TldDatabase::escape($start);
        $end = TldDatabase::escape($end);
        switch ($options['compareDate']) {
            case 'service_accept_date':
            case 'prod_man_accept_date':
                $date = 't1.' . $options['compareDate'];
                break;
            default:
                if (is_array($options)) {
                    $where = ' AND ' . tldUtils::constructWhere($options);
                }
                $date = 't1.claim_date';
        }
        $query = <<<EOF
SELECT t1.*, er.date_shipped, 
    PERIOD_DIFF(DATE_FORMAT(t1.claim_date,'%Y%m'),DATE_FORMAT(er.date_shipped,'%Y%m')) AS month_age 
FROM warranty AS t1 
  LEFT JOIN service AS er ON er.sn = t1.serial_number
WHERE
    $date BETWEEN '$start' AND '$end'
    $where
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * get WCs for specific customer
     *
     * @return array array of db rows
     */
    public function byCustomer($customer, $options = '')
    {
        $customer = TldDatabase::escape($customer);
        $where = is_array($options) ? ' AND ' . tldUtils::constructWhere($options) : '';
        $query = <<<EOF
SELECT * FROM warranty
WHERE
customer_name='$customer'
    $where
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * get WCs for specific initiator (called entered_by)
     *
     * @return array array of db rows
     */
    public static function byInitiator($initiator)
    {
        $query = <<<EOF
SELECT * FROM warranty
WHERE
entered_by='$initiator'
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byPartNumberByConstraints($pn, $a = null)
    {
        $WHERE = '';
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = " AND $WHERE";
        }
        $query = <<<EOF
SELECT
    warranty.*,
    warranty_parts.supply_it,
    warranty_parts.part_number,
    warranty_parts.part_description,
    warranty_parts.brand,
    warranty_parts.quantity as part_quantity,
    warranty_parts.qty_in,
    warranty_parts.d_in,
    warranty_parts.um,
    warranty_parts.failure_type,
    warranty_parts.failure_system,
    warranty_parts.sn
FROM warranty
    LEFT JOIN warranty_parts ON warranty.id=warranty_parts.parent_id
WHERE
    warranty_parts.part_number='$pn'
    $WHERE
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function WCCountByPN($pn)
    {
        $query = <<<EOF
SELECT count(part_number) AS count
FROM warranty
    JOIN warranty_parts ON warranty.id=warranty_parts.parent_id
WHERE
    part_number='$pn' AND warranty.warranty_status NOT IN ("REJECTED","SALES CONCESSION")
GROUP BY part_number
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function WCCountByPNByPast12Month($pn)
    {
        $query = <<<EOF
SELECT count(part_number) AS count
FROM warranty
    JOIN warranty_parts ON warranty.id=warranty_parts.parent_id
WHERE
    part_number='$pn' AND warranty.warranty_status NOT IN ("REJECTED","SALES CONCESSION")
   	AND PERIOD_DIFF(DATE_FORMAT(NOW(),'%Y%m'),DATE_FORMAT(warranty.claim_date,'%Y%m')) BETWEEN 0 AND 12
GROUP BY part_number
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * get WCs for equipment sn
     *
     * @return array array of db rows
     */
    public static function bySN($sn, $options = '', $afterGT = false)
    {
        $WHERE = '';
        $LEFTJOIN = '';

        if ($options === 'restricted') {
            $LEFTJOIN .= "LEFT JOIN toc ON toc.warranty_id = warranty.id";
            $WHERE .= " AND ((toc.activity_type != 'Commissioning' AND toc.notification != 'N') OR toc.id IS NULL)";
        }

        if ($afterGT === true) {
            $LEFTJOIN .= " LEFT JOIN service ON service.sn = warranty.serial_number";
            $WHERE .= " AND warranty.claim_date > service.dgt_act";
        }

        $query = <<<EOF
SELECT * FROM warranty
$LEFTJOIN
WHERE
serial_number='$sn'
    $WHERE
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get latest wc
     *
     * @param integer $num
     *
     * @return array
     */
    public static function byLatest($num = 10, $options = '')
    {

        if (is_array($options)) {
            $WHERE = ' AND ' . tldUtils::constructWhere($options);
        } else {
            if (!empty($options)) {
                $WHERE = "WHERE $options";
            } else {
                $WHERE = '';
            }
        }
        $query = <<<EOF
SELECT 
    CASE WHEN warranty_status='DISPUTE' THEN 'REJECTED'
        ELSE warranty_status
    END as wc_status,
    warranty.*,
    service.airport_code
FROM
    warranty
LEFT JOIN service on warranty.serial_number = service.sn
$WHERE
ORDER BY id DESC
LIMIT $num
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Search WCs
     *
     * @param string $id
     *
     * @return array
     */
    public static function search($id)
    {
        $query = <<<EOF
SELECT
	DISTINCT warranty.id,
	warranty.*,
	PERIOD_DIFF(
		DATE_FORMAT(claim_date,'%Y%m'),
		DATE_FORMAT((SELECT er.date_shipped FROM service AS er WHERE er.sn=warranty.serial_number LIMIT 1),'%Y%m')
	) AS month_age
FROM warranty w
	LEFT JOIN warranty_parts AS parts ON warranty.id=parts.parent_id
WHERE
    warranty.id='$id'
    OR w.warranty_status like '$id'
    OR w.entered_by like '$id'
    OR w.laimant_details like '$id'
    OR w.customer_name like '$id'
    OR w.type like '$id'
    OR w.model like '$id'
    OR w.man_location like '$id'
    OR w.sales_org like '$id'
    OR w.serial_number like '$id'
    OR w.equipment_location like '$id'
    OR w.problem_desc like '$id'
    OR w.extranet_prob_desc like '$id'
    OR w.service_comments like '$id'
    OR w.parts_order_ref like '$id'
    OR parts.part_number like '$id'
    OR parts.part_description like '$id'
    OR parts.sn like '$id'
    OR w.parts_courier like '$id'
    OR w.prod_man_comments like '$id'
ORDER BY id DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function advSearch($a)
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
    DISTINCT warranty.id,
    warranty.*,
    parts.part_description AS part_description,
    parts.part_number AS part_number,
    parts.quantity AS part_quantity,
    (SELECT er.dgt_act FROM service AS er WHERE er.sn=warranty.serial_number LIMIT 1) AS gt_date,
    (SELECT er.date_shipped FROM service AS er WHERE er.sn=warranty.serial_number LIMIT 1) AS ship_date,
	PERIOD_DIFF(
		DATE_FORMAT(claim_date,'%Y%m'),
		DATE_FORMAT((SELECT er.date_shipped FROM service AS er WHERE er.sn=warranty.serial_number LIMIT 1),'%Y%m')
	) AS month_age
FROM warranty
	LEFT JOIN warranty_parts AS parts ON warranty.id=parts.parent_id
	LEFT JOIN service AS er ON warranty.serial_number = er.sn
WHERE
    $WHERE
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Query WCs using array of constraints
     *
     * @param array $m
     *
     * @return array
     */
    public static function byQuery($m)
    {
        $WHERE = '';
        if (is_array($m)) {
            $WHERE = ' WHERE ' . tldUtils::constructWhere($m);
        }
        $query = <<<EOF
SELECT *
FROM warranty
    $WHERE
        ORDER BY claim_date
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byCustomerWC(tldCustomer $customer, $option)
    {
        $WHERE = '';
        switch ($option) {
            case 'end_user':
                $WHERE = "LEFT JOIN service AS er ON wc.parent_id=er.id 
                    WHERE er.customer_id='{$customer->getID()}'";
            break;
            case 'buyer':
                $WHERE = "LEFT JOIN service AS er ON wc.parent_id=er.id 
                    WHERE er.buyer_customer_id='{$customer->getID()}'";
            break;
            case 'wcCustomer':
                $WHERE = "WHERE wc.customer_name = '{$customer->getCustomerName()}'";
            break;
        }

        $query = <<<EOF
SELECT wc.*,
    parts.part_number AS parts_number,
	parts.part_description AS parts_description,
	parts.quantity AS parts_quantity
FROM warranty AS wc
    LEFT JOIN warranty_parts AS parts ON wc.id=parts.parent_id
    $WHERE
        ORDER BY claim_date
EOF;


        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getPartsListByConstraints($a)
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        $query = <<<EOF
SELECT
	wc.*,
	(SELECT er.airport_code FROM service AS er WHERE er.sn=wc.serial_number LIMIT 1) AS airport_code,
	(SELECT er.date_shipped FROM service AS er WHERE er.sn=wc.serial_number LIMIT 1) AS ship_date,
	PERIOD_DIFF(
		DATE_FORMAT(wc.claim_date,'%Y%m'),
		DATE_FORMAT((SELECT er.date_shipped FROM service AS er WHERE er.sn=wc.serial_number LIMIT 1),'%Y%m')
	) AS month_age,
	DATEDIFF(
		DATE_FORMAT(wc.claim_date,'%Y%m%d'),
		DATE_FORMAT((SELECT er.date_shipped FROM service AS er WHERE er.sn=wc.serial_number LIMIT 1),'%Y%m%d')
	) AS num_of_days,
	parts.part_number AS parts_number,
	parts.part_description AS parts_description,
	parts.quantity AS parts_quantity,
	parts.qty_in AS parts_qty_in,
	parts.d_in,
	concat(people.lastname,', ',people.firstname) AS poster_fullname
FROM
	warranty AS wc
	LEFT JOIN warranty_parts AS parts ON wc.id=parts.parent_id
	LEFT JOIN people ON wc.entered_by=people.email
WHERE
	$WHERE
ORDER BY
	wc.claim_date
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * WCs using array of constraints
     *
     * @param array $m
     *
     * @return array
     */
    public function getPrintVersion()
    {
        $items = $this->getHeader();

        // Get linked TOC
        $linksTo = $this->getLinksToHere();
        $linksTo = array_filter($linksTo, function ($link) {
            return 'TOC' === $link['module'];
        });

        $items['toc'] = $linksTo[0]['parent_id'];
        $items['prod_man_comments'] = str_replace(["\\r\\n", "\r\n", "\r", "\n"], '<br/>', $items['prod_man_comments']);
        $general = new tldAssocTable(
            $items,
            [
                'id' => 'WC#',
                'toc' => 'TOC#',
                'claim_date' => 'Claim Date',
                'warranty_status' => 'Status',
                'entered_by' => 'Entered By',
                'claimant_details' => 'Claimant Details',
                'warranty_details' => 'Warranty Details',
                'customer_name' => 'Customer Name',
                'serial_number' => 'ER S/N',
                'type' => 'Type',
                'model' => 'Model',
                'man_location' => 'Manufacturer Location',
                'sales_org' => 'Sales Organization',
                'serial_number' => 'Serial Number',
                'equipment_location' => 'Equipment Location',
                'hours' => 'Hourmeter',
                'prod_man_comments' => 'Comments',
                'problem_desc' => 'Problem Description',
                'extranet_prob_desc' => 'Extranet Problem Description',
                'est_man_hours' => 'Estimate Labour (Hrs)',
                'technician' => 'Technician',
                'technician_cost_te' => 'T&E Cost',
                'technician_cost_labour' => 'Labour Cost',
                'parts_cost' => 'Parts Cost',
                'note_cost' => 'Cost Note',
                'intervention' => 'TLD Personnel Required?',
                'service_date_delivery' => 'Work Date',
                'service_comments' => 'Service Comments',
                'service_ship_inst' => 'Shipping Instructions',
                'parts_order_ref' => 'Parts Order Ref',
                'parts_date_delivery' => 'Est. Delivery Date',
                'parts_courier' => 'Courier Name & Tracking',
                'part_return_date' => 'Part Return Date',
                'part_return_address' => 'Parts Return Address',
            ],
            [
                'title' => 'General',
                'links' => [
                    'id' => 'https://www.tld-gse.com/en/private/product_support/index.ps.php?m[0]=wc&m[1]=view&id=',
                    'toc' => 'https://www.tld-gse.com/en/private/sales_service/service.php?m[0]=toc&m[1]=view&id=',
                ],
            ]
        );
        // Parts
        $parts = new tldReportColumnar(
            $this->getParts(),
            [
                'xItems' => [
                    'id' => 'ID#',
                    'supply_it' => 'Track(Y/N)',
                    'notes' => 'Note',
                    'brand' => 'Part Brand',
                    'part_number' => 'PN',
                    'part_description' => 'Description',
                    'sn' => 'SN',
                    'failure_type' => 'Failure Type',
                    'failure_system' => 'Failure System',
                    'um' => 'UM',
                    'quantity' => 'Qty Shipped',
                    'qty_in' => 'Qty Returned',
                    'd_in' => 'Date Returned',
                ],
                'title' => 'Parts',
            ]
        );

        // Return
        return $general->fetch() . $parts->fetch();
    }

    public function getRelatedData()
    {
        if (empty($this->itsID)) {
            return 'Not in object context';
        }
        $data = [];
        $data[0]['desc'] = 'Opened Tasks';
        $data[0]['count'] = count($this->getTasks($status = 'OPEN'));
        $data[0]['link'] = '&m[2]=tasks';
        $data[1]['desc'] = 'Parts';
        $data[1]['count'] = count($this->getParts());
        $data[1]['link'] = '&m[2]=parts';
        $data[2]['desc'] = 'Files';
        $data[2]['count'] = count($this->getFiles());
        $data[2]['link'] = '&m[2]=files';
        $data[3]['desc'] = 'VWC';

        global $kernel;
        $client = $kernel->getContainer()->get(Client::class);
        try {
            $vwcs = $client->findBy('purchasing/wc_vendor_warranty_claims', ['warrantyClaimId' => $this->getID()]);
            $count = count($vwcs);
        } catch (\Exception $e) {
            $count = 0;
        }

        $data[3]['count'] = $count;
        $data[3]['link'] = '&m[2]=vwc';
        $data[4]['desc'] = 'Links';
        $data[4]['count'] = count($this->getLinksFromHere()) + count($this->getLinksToHere());
        $data[4]['link'] = '&m[2]=links';

        return $data;
    }

    public function getDefaultEmailSubject()
    {
        return "WC#{$this->getID()}, {$this->getCustomerName()}, {$this->getERModel()}, {$this->getERSN()}";
    }

    /**
     * Generic method to send out email with WC print version
     *
     * @param string $from
     * @param mixed string array $to
     * @param string $subject
     * @param string $body
     * @param mixed string array $cc
     */
    public function sendEmail($to, $from, $subject, $body, $cc = '')
    {
        $body .= $this->getPrintVersion();
        return tldUtils::emailAttachment(
            $to,
            $from,
            $subject,
            $body,
            null,
            $cc
        );
    }

    /**
     * Get MTBF KPI by constraints for a specific month period
     *
     * @param date $period_Ymd
     * @param array $a
     *
     * @return array
     */
    public static function getMTBFPeriodByConstraints($period_Ymd, $a = null, $group_type = false)
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "AND $WHERE";
        }
        $GROUP = $group_type ? 'type' : 'period';
        $date = new DateTime($period_Ymd);
        $period = $date->format('Y-m-d');
        $query = <<<EOF
SELECT
    DATE_FORMAT('$period', '%Y-%m') AS period,
    IF(
        ROUND(
            SUM(DATEDIFF(LAST_DAY('$period'),er.date_shipped))/
            SUM((SELECT COUNT(*) FROM warranty
                WHERE serial_number=er.sn
                AND warranty_status NOT IN('SALES CONCESSION','REJECTED')
                AND DATEDIFF(LAST_DAY('$period'),claim_date)>=0
                AND DATEDIFF(claim_date,er.date_shipped)>=0)
            ),1
        ) IS NULL,
        0,
        ROUND(
            SUM(DATEDIFF(LAST_DAY('$period'),er.date_shipped))/
            SUM((SELECT COUNT(*) FROM warranty 
                WHERE serial_number=er.sn
                AND warranty_status NOT IN('SALES CONCESSION','REJECTED')
                AND DATEDIFF(LAST_DAY('$period'),claim_date)>=0
                AND DATEDIFF(claim_date,er.date_shipped)>=0)
            ),1
        )
    ) AS val,
    type
FROM
    service AS er
WHERE
    er.sn REGEXP '^(T|L)[0-9]+$' 
    AND er.light = 0
    AND PERIOD_DIFF(
        DATE_FORMAT(LAST_DAY('$period'),'%Y%m'),
        DATE_FORMAT(er.date_shipped, '%Y%m')
    ) BETWEEN 0 AND 24
    $WHERE
GROUP BY
	$GROUP
EOF;
        if ($group_type) {
            return tldUtils::getSqlToAssocArray($query);
        }

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public static function byMTBFPeriodByConstraints($period_Ymd, $a = null)
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "AND $WHERE";
        }
        $date = new DateTime($period_Ymd);
        $period = $date->format('Y-m-d');
        $query = <<<EOF
SELECT
    DATE_FORMAT('$period', '%Y-%m') AS period,
    er.id AS er_id,
    er.sn,
    er.type,
    er.model,
    er.man_location,
    er.date_shipped,
    wc.id,
    wc.claim_date,
    wc.warranty_status,
    DATEDIFF(LAST_DAY('$period'),er.date_shipped) AS days_shipped_end_period
FROM
    service AS er
    LEFT JOIN warranty AS wc ON wc.serial_number=er.sn
WHERE
    PERIOD_DIFF(
        DATE_FORMAT(LAST_DAY('$period'),'%Y%m'),
        DATE_FORMAT(er.date_shipped, '%Y%m')
    ) BETWEEN 0 AND 24
    AND wc.warranty_status NOT IN('SALES CONCESSION','REJECTED')
    AND DATEDIFF(LAST_DAY('$period'),wc.claim_date)>=0
    AND DATEDIFF(wc.claim_date,er.date_shipped)>=0
    $WHERE
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byMTBFPeriod($period_Ymd, $a = null)
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "AND $WHERE";
        }
        $date = new DateTime($period_Ymd);
        $period = $date->format('Y-m-d');
        $query = <<<EOF
SELECT
DATE_FORMAT('$period', '%Y-%m') AS period,
    er.id AS er_id,
    er.sn,
    CONCAT('#',group_concat(wc.id SEPARATOR ',#')) AS wcno,
    count(wc.id) AS wcqty,
(DATEDIFF(LAST_DAY('$period'),er.date_shipped)) AS life,
    wc.id,
    wc.claim_date,
    wc.warranty_status,
    er.model,
    er.man_location,
    er.date_shipped,
	er.type,
	er.hours,
	user_customer.customer_name AS customer_name	
FROM
    service AS er
LEFT JOIN warranty AS wc ON wc.serial_number=er.sn
LEFT JOIN customers AS user_customer ON user_customer.id=er.customer_id
WHERE
    PERIOD_DIFF(
        DATE_FORMAT(LAST_DAY('$period'),'%Y%m'),
        DATE_FORMAT(er.date_shipped, '%Y%m')
    ) BETWEEN 0 AND 24 
$WHERE
GROUP BY er.id
ORDER BY er.date_shipped
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get ATFF KPI by constraints for a specific month period
     *
     * @param date $period_Ymd
     * @param array $a
     *
     * @return array
     */
    public static function getATFFPeriodByConstraints($period_Ymd, $a = null, $group_type = false)
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "AND $WHERE";
        }
        $GROUP = $group_type ? 'type' : 'period';
        $date = new DateTime($period_Ymd);
        $period = $date->format('Y-m-d');
        $query = <<<EOF
SELECT
    DATE_FORMAT('$period', '%Y-%m') AS period,
    ROUND(AVG(DATEDIFF(first_claim_date, date_shipped)), 1) AS val,
    type
FROM (
    SELECT
        er.sn,
        er.date_shipped,
        er.type,
        MIN(w.claim_date) AS first_claim_date
    FROM
        service AS er
    LEFT JOIN warranty w ON w.serial_number = er.sn
        AND w.warranty_status NOT IN('SALES CONCESSION','REJECTED')
        AND DATEDIFF(LAST_DAY('$period'), w.claim_date) >= 0
        AND DATEDIFF(w.claim_date, er.date_shipped) >= 0
    LEFT JOIN toc ON toc.warranty_id = w.id
    WHERE
        er.sn REGEXP '^(T|L)[0-9]+$'
        AND er.light = 0
        AND PERIOD_DIFF(
            DATE_FORMAT(LAST_DAY('$period'),'%Y%m'),
            DATE_FORMAT(er.date_shipped, '%Y%m')
        ) BETWEEN 0 AND 24
        AND (toc.activity_type != 'Commissioning' OR toc.id IS NULL)
        $WHERE
    GROUP BY
        er.sn
) AS first_claims
WHERE
    first_claim_date IS NOT NULL
GROUP BY
    $GROUP
HAVING val IS NOT NULL
EOF;
        if ($group_type) {
            return tldUtils::getSqlToAssocArray($query);
        }

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public static function getWCCountByConstraints($period_Ymd, $a = null, $group_type = false)
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "AND $WHERE";
        }
        $GROUP = $group_type ? 'type' : 'period';
        $date = new DateTime($period_Ymd);
        $period = $date->format('Y-m-d');
        $query = <<<EOF
SELECT
    DATE_FORMAT('$period', '%Y-%m') AS period,
	(
        SUM(
           (SELECT COUNT(*) FROM warranty 
           LEFT JOIN toc ON warranty.id = toc.warranty_id
           WHERE serial_number=er.sn
                AND warranty_status NOT IN('SALES CONCESSION','REJECTED')
                AND DATEDIFF(LAST_DAY('$period'),claim_date)>=0
                AND DATEDIFF(claim_date,er.date_shipped)>=0
                AND (toc.activity_type != 'Commissioning' OR toc.id IS NULL)
               )
           )
	) AS val,
	type
FROM
    service AS er
WHERE
    er.sn REGEXP '^(T)[0-9]+$' AND
    er.light = 0 AND
    PERIOD_DIFF(
        DATE_FORMAT(LAST_DAY('$period'),'%Y%m'),
        DATE_FORMAT(er.date_shipped, '%Y%m')
    ) BETWEEN 0 AND 24
    
    $WHERE
GROUP BY
	$GROUP
EOF;
        if ($group_type) {
            return tldUtils::getSqlToAssocArray($query);
        }

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public static function getERCountByConstraints($period_Ymd, $a = null, $group_type = false)
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "AND $WHERE";
        }
        $GROUP = $group_type ? 'type' : 'period';
        $date = new DateTime($period_Ymd);
        $period = $date->format('Y-m-d');
        $query = <<<EOF
SELECT
    DATE_FORMAT('$period', '%Y-%m') AS period,
	count(*) AS val,
	type
FROM
    service AS er
WHERE
    er.sn REGEXP '^(T|L)[0-9]+$' AND
    er.light = 0 AND
    PERIOD_DIFF(
        DATE_FORMAT(LAST_DAY('$period'),'%Y%m'),
        DATE_FORMAT(er.date_shipped, '%Y%m')
    ) BETWEEN 0 AND 24
    $WHERE
GROUP BY
	$GROUP
EOF;
        if ($group_type) {
            return tldUtils::getSqlToAssocArray($query);
        }

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public static function byATFFByPeriodByConstraints($period_Ymd, $a = null)
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "AND $WHERE";
        }
        $date = new DateTime($period_Ymd);
        $period = $date->format('Y-m-d');
        $query = <<<EOF
SELECT
    DATE_FORMAT('$period', '%Y-%m') AS period,
    er.id AS er_id,
    er.sn,
    er.model,
    er.man_location,
    er.date_shipped,
    wc.id,
    wc.claim_date,
    wc.warranty_status,
    DATEDIFF(wc.claim_date,er.date_shipped) AS wc_diff_er_shipped
FROM
    service AS er
    LEFT JOIN warranty AS wc ON wc.serial_number=er.sn
WHERE
    PERIOD_DIFF(
        DATE_FORMAT(LAST_DAY('$period'),'%Y%m'),
        DATE_FORMAT(er.date_shipped, '%Y%m')
    ) BETWEEN 0 AND 24
    AND wc.id = (
        SELECT id FROM warranty WHERE serial_number=er.sn
            AND warranty_status NOT IN('SALES CONCESSION','REJECTED')
            AND DATEDIFF(LAST_DAY('$period'),claim_date)>=0
            AND DATEDIFF(claim_date,er.date_shipped)>=0
        ORDER BY claim_date
        LIMIT 1
    )
    $WHERE
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get AVGWC KPI by constraints for a specific month period
     *
     * @param date $period_Ymd
     * @param array $a
     *
     * @return array
     */
    public static function getAVGWCPeriodByConstraints($period_Ymd, $a = null, $group_type = false)
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "AND $WHERE";
        }
        $GROUP = $group_type ? 'type' : 'period';
        $date = new DateTime($period_Ymd);
        $period = $date->format('Y-m-d');
        $query = <<<EOF
SELECT
    DATE_FORMAT('$period', '%Y-%m') AS period,
    (
        ROUND(
            SUM(
               (SELECT COUNT(*) FROM warranty 
               LEFT JOIN toc ON warranty.id = toc.warranty_id
               WHERE serial_number=er.sn
                    AND warranty_status NOT IN('SALES CONCESSION','REJECTED')
                    AND DATEDIFF(LAST_DAY('$period'),claim_date)>=0
                    AND DATEDIFF(claim_date,er.date_shipped)>=0
                    AND (toc.activity_type != 'Commissioning' OR toc.id IS NULL)
                   )
               ) / COUNT(*)
               ,2
        )
    ) AS val,
    type
FROM
    service AS er
WHERE
    er.sn REGEXP '^(T|L)[0-9]+$' AND
    er.light = 0 AND
    PERIOD_DIFF(
        DATE_FORMAT(LAST_DAY('$period'),'%Y%m'),
        DATE_FORMAT(er.date_shipped, '%Y%m')
    ) BETWEEN 0 AND 24
    $WHERE
GROUP BY
    $GROUP
EOF;
        if ($group_type) {
            return tldUtils::getSqlToAssocArray($query);
        }

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Get MNOWC3 KPI by constraints for a specific month period
     *
     * @param date $period_Ymd
     * @param array $a
     *
     * @return array
     */
    public static function getMNOWC3PeriodByConstraints($period_Ymd, $a = null, $group_type = false)
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "AND $WHERE";
        }
        $GROUP = $group_type ? 'type' : 'period';
        $date = new DateTime($period_Ymd);
        $period = $date->format('Y-m-d');
        $query = <<<EOF
SELECT
    DATE_FORMAT('$period', '%Y-%m') AS period,
	(
		ROUND(
    		SUM(
        		IF(
            		(SELECT MIN(claim_date) FROM warranty 
            		LEFT JOIN toc ON toc.warranty_id = warranty.id
            		WHERE serial_number=er.sn
                        AND warranty_status NOT IN('SALES CONCESSION','REJECTED')
            			AND DATEDIFF(LAST_DAY('$period'),claim_date)>=0
            			AND DATEDIFF(claim_date,er.date_shipped)>=0
            		    AND (toc.activity_type != 'Commissioning' OR toc.id IS NULL)
            		) IS NULL
            		OR
            		DATEDIFF(
                        (SELECT MIN(claim_date) FROM warranty 
                        LEFT JOIN toc ON toc.warranty_id = warranty.id
                        WHERE serial_number=er.sn
                            AND warranty_status NOT IN('SALES CONCESSION','REJECTED')
                            AND DATEDIFF(LAST_DAY('$period'),claim_date)>=0
                            AND DATEDIFF(claim_date,er.date_shipped)>=0
                            AND (toc.activity_type != 'Commissioning' OR toc.id IS NULL)
                         ),
            			er.date_shipped
            		) > 90,
                    1,
                    0
            	)
        	) / COUNT(*)*100
        	,2
		)
	) AS val,
	type
FROM
    service AS er
WHERE
    er.sn REGEXP '^(T|L)[0-9]+$' 
    AND er.light = 0
    AND PERIOD_DIFF(
        DATE_FORMAT(LAST_DAY('$period'),'%Y%m'),
        DATE_FORMAT(er.date_shipped, '%Y%m')
    ) BETWEEN 0 AND 24
    $WHERE
GROUP BY
	$GROUP
EOF;
        if ($group_type) {
            return tldUtils::getSqlToAssocArray($query);
        }

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Get AVG # of WC KPI by unit(s) by constraints for a specific month period
     *
     * @param $from
     * @param $until
     * @param array $constraints
     * @param bool $oneUnit
     *
     * @return array
     */
    public static function getAverageWCByPeriodByConstraints(\DateTime $from, \DateTime $until, array $constraints = [], $oneUnit = false)
    {
        $where = '';
        $interval = new DateInterval('P1M');
        $dateRange = new DatePeriod($from, $interval, $until);

        foreach ($constraints as $field => $value) {
            if (!empty($value) && 'id' !== $field) {
                $where .= sprintf('AND er.%s = "%s" ', $field, $value);
            }
            // Make function available for multiple or single unit
            if ('id' === $field && true === $oneUnit) {
                $where .= "AND er.sn like '%$value%' ";
            }
        }
        if (true !== $oneUnit) {
            $where .= "AND er.sn REGEXP '^(T|L)[0-9]+$' ";
        }

        $query = <<<EOF
SELECT
  DATE_FORMAT(w.claim_date, '%Y-%m') AS month,
  ROUND(COUNT(DISTINCT w.id) / COUNT(DISTINCT er.sn), 2) AS avg_wc,
  COUNT(DISTINCT w.id) AS nb_wc,
  COUNT(DISTINCT er.sn) AS nb_sn
FROM
  service AS er
LEFT JOIN warranty as w
  ON er.sn = w.serial_number
WHERE
  w.warranty_status NOT IN('SALES CONCESSION','REJECTED')
  AND PERIOD_DIFF(
      DATE_FORMAT(w.claim_date,'%Y-%m'),
      DATE_FORMAT(er.date_shipped, '%Y-%m')
  ) BETWEEN 0 AND 24
  AND PERIOD_DIFF(DATE_FORMAT(NOW(),'%Y-%m'), DATE_FORMAT(w.claim_date,'%Y-%m')) < 12
  AND w.claim_date >= "{$from->format('Y-m-d')}"
  AND w.claim_date <= "{$until->format('Y-m-d')}"
  $where
GROUP BY month
EOF;

        $results = tldUtils::getSqlToAssocArray($query);

        $indexedResults = [];
        foreach ($results as $result) {
            $indexedResults[$result['month']] = $result;
        }

        foreach ($dateRange as $date) {
            $month = $date->format('Y-m');
            if (!array_key_exists($month, $indexedResults)) {
                $indexedResults[$month] = [
                    'month' => $month,
                    'avg_wc' => 0,
                    'nb_wc' => 0,
                    'nb_sn' => 0,
                ];
            }
        }
        ksort($indexedResults);

        return $indexedResults;
    }

    /**
     * Get Mean Time Between Failures KPI by unit(s) by constraints
     *
     * @param $date
     * @param array $constraints
     * @param bool $oneUnit
     *
     * @return array
     */
    public static function getMTBFByPeriodByConstraints(\DateTime $date, array $constraints = [], $oneUnit = false)
    {
        $where = '';
        $period = $date->format('Y-m');
        foreach ($constraints as $field => $value) {
            if (!empty($value) && 'id' !== $field) {
                $where .= sprintf('AND er.%s = "%s" ', $field, $value);
            }
            // Make function available for multiple or single unit
            if ('id' === $field && true === $oneUnit) {
                $where .= "AND er.sn like '%$value%' ";
            }
        }
        if (true !== $oneUnit) {
            $where .= "AND er.sn REGEXP '^(T|L)[0-9]+$' ";
        }

        $query = <<<EOF
SELECT 
ROUND(SUM(average) / COUNT(DISTINCT id), 2) as val,
DATE_FORMAT(date_shipped, '%Y-%m') as month 
FROM (
  SELECT
    er.id,
    er.sn,
    er.date_shipped,
    IF(
        COUNT(w.id) <= 1,
          NULL,
        DATEDIFF(MAX(w.claim_date), MIN(w.claim_date)) / (COUNT(w.id) - 1)
    ) AS average,
    DATEDIFF(MAX(w.claim_date), MIN(w.claim_date)),
    COUNT(w.id)
  FROM service AS er LEFT JOIN warranty AS w ON er.sn = w.serial_number
  WHERE er.date_shipped like '%$period%'
  $where
  GROUP BY er.sn
) results
WHERE average IS NOT NULL
GROUP BY month
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Get Average Time at First Failure KPI by units by constraints
     *
     * @param $date
     * @param array $constraints
     *
     * @return array
     */
    public static function getATFFByMonthByConstraints(\DateTime $date, array $constraints = [])
    {
        $where = '';
        $period = $date->format('Y-m');
        foreach ($constraints as $field => $value) {
            if (!empty($value) && 'id' !== $field) {
                $where .= sprintf('AND er.%s = "%s" ', $field, $value);
            }
        }

        $query = <<<EOF
SELECT
  ROUND(SUM(average) / COUNT(DISTINCT id), 2) as val,
  DATE_FORMAT(date_shipped, '%Y-%m') as month
FROM (
 SELECT
   er.id,
   er.sn,
   er.date_shipped,
   IF(
       COUNT(w.id) <= 0,
       NULL,
       DATEDIFF(MIN(w.claim_date), er.date_shipped) / (COUNT(w.id))
   ) AS average,
   DATEDIFF(MIN(w.claim_date), er.date_shipped) as date_diff,
   COUNT(w.id)
 FROM service AS er LEFT JOIN warranty AS w ON er.sn = w.serial_number
 WHERE er.date_shipped like '%$period%'
 $where
 GROUP BY er.sn
) results
WHERE average IS NOT NULL
GROUP BY DATE_FORMAT(date_shipped, '%Y-%m')
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Get Time at First Failure KPI for single unit by constraints
     *
     * @param $date
     * @param array $constraints
     * @param string $sn
     *
     * @return array
     */
    public static function getTFFByMonthByConstraints(\DateTime $date, string $sn, array $constraints = [])
    {
        $where = '';
        $period = $date->format('Y-m');
        foreach ($constraints as $field => $value) {
            if (!empty($value) && 'id' !== $field) {
                $where .= sprintf('AND er.%s = "%s" ', $field, $value);
            }
        }

        $query = <<<EOF
SELECT
  IF(
      COUNT(w.id) = 0,
      NULL,
      ROUND(DATEDIFF(MIN(w.claim_date), er.date_shipped), 1)
  ) AS val,
  DATE_FORMAT(er.date_shipped, '%Y-%m') as month
FROM service AS er
  LEFT JOIN warranty AS w ON er.sn = w.serial_number
WHERE er.date_shipped like '%$period%'
AND er.sn like '%$sn%'
$where
GROUP BY month
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Get % Machines with NO Failures KPI by units by constraints
     *
     * @param $from
     * @param $until
     * @param array $constraints
     *
     * @return array
     */
    public static function getPercentMachineWithNoFailByPeriodByConstraints(\DateTime $from, \DateTime $until, array $constraints = [])
    {
        $where = '';

        foreach ($constraints as $field => $value) {
            if (!empty($value) && 'id' !== $field) {
                if (!empty($where)) {
                    $where .= sprintf('AND er.%s = "%s" ', $field, $value);
                } else {
                    $where = sprintf('WHERE er.%s = "%s" ', $field, $value);
                }
            }
        }

        $query = <<<EOF
SELECT 
  ROUND(SUM(number) / COUNT(DISTINCT id)*100, 2) as val,
  DATE_FORMAT(date_shipped, '%Y-%m') as month
FROM (
  SELECT
    er.id,
    er.sn,
    er.date_shipped,
    IF(
        (MIN(w.claim_date)) IS NULL
        OR
        DATEDIFF(MIN(w.claim_date),er.date_shipped) > 90,
        1,
        0
    ) AS number,
    DATEDIFF(MAX(w.claim_date), MIN(w.claim_date)),
    COUNT(w.id)
  FROM service AS er LEFT JOIN warranty AS w ON er.sn = w.serial_number
  $where
  GROUP BY er.sn
  ) results
WHERE number IS NOT NULL 
AND DATE_FORMAT(date_shipped, '%Y-%m') 
  BETWEEN "{$from->format('Y-m')}" AND "{$until->format('Y-m')}"
GROUP BY month
EOF;

        $results = tldUtils::getSqlToAssocArray($query);

        $indexedResults = [];
        foreach ($results as $result) {
            $indexedResults[$result['month']] = $result;
        }

        return $indexedResults;
    }

    public static function getDataOfWcKpi(string $manufacturerLocation) :array
    {
        $today = (new DateTime(date('Y-m-d')))->format('Y-m-d');

        $query = <<<EOF
SELECT
    er.sn as serial_number,
    er.man_location,
    er.model,
    er.type,
    DATE_FORMAT(er.date_shipped, '%Y%m') as date_shipped,
    IF(er.light, 'yes', 'no') as is_light,
    wc.id as warranty_id,
    wc.warranty_status,
    wc.claim_date,
    IF(toc.id IS NOT NULL, toc.activity_type, '') as toc_activity,
    (SELECT mod_logs.comment FROM mod_logs WHERE mod_logs.module = 'TOC' AND mod_logs.parent_id = toc.id ORDER BY mod_logs.id DESC LIMIT 1) as lastComment
FROM
    service AS er
LEFT JOIN warranty wc ON er.sn = wc.serial_number
    AND wc.warranty_status NOT IN('SALES CONCESSION','REJECTED')
    AND DATEDIFF(LAST_DAY('$today'), wc.claim_date) >= 0
    AND DATEDIFF(wc.claim_date, er.date_shipped) >= 0
LEFT JOIN toc ON wc.id = toc.warranty_id
WHERE
    er.sn REGEXP '^(T|L)[0-9]+$'
    AND er.light = 0
    AND PERIOD_DIFF(
            DATE_FORMAT(LAST_DAY('$today'),'%Y%m'),
            DATE_FORMAT(er.date_shipped, '%Y%m')
    ) BETWEEN 0 AND 24
    AND er.man_location= '$manufacturerLocation'
    AND (toc.activity_type != 'Commissioning' OR toc.id IS NULL)
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getEquipmentsByConstraints($a, $opt = [])
    {
        $SELECT = '';
        $WHERE = is_array($a) ? tldUtils::constructWhere($a) : $a;

        if (!empty($WHERE)) {
            $WHERE = "WHERE $WHERE";
        }
        // Options
        $ORDERBY = !empty($opt['orderBy']) ? 'ORDER BY ' . $opt['orderBy'] : 'ORDER BY wc.claim_date';

        if (!empty($opt['select'])) {
            $SELECT = $opt['select'] . ',';
        }
        // Query
        $query = <<<EOF
SELECT
    $SELECT
    wc.*,
    er.airport_code,
    er.date_shipped
FROM warranty AS wc
    LEFT JOIN service AS er ON wc.serial_number=er.sn
$WHERE
$ORDERBY
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getOpenTaskByFactoryID($buid)
    {
        return tldTask::byConstraints(
            <<<EOF
T1.module LIKE 'wc'
AND T1.status!='CLOSED'
AND T1.parent_id IN(
    SELECT id FROM warranty
    WHERE man_location LIKE (
        SELECT location FROM locations WHERE id=$buid
    )
)
EOF
        );
    }

    public static function getIdsByPDC($PDCId) : array
    {
        $WCByPDCId = <<<SQL
SELECT
    CASE
        WHEN type = 'WC' THEN mod_links.item
        ELSE mod_links.parent_id
    END AS wc_id
FROM mod_links
WHERE (item = $PDCId AND type = 'PDC' AND module = 'WC')
OR (parent_id = $PDCId AND module = 'PDC' AND type = 'WC');
SQL;
        return tldUtils::getSqlToAssocArray($WCByPDCId, 'smartyOptions', ['wc_id', 'wc_id']);
    }
}

/**
 * Class for accessing and manipulating Service Bulletin
 * SB module
 *
 * @package Support
 */
class tldSB
{

    public $itsDetails;
    public $itsFileDir = 'sbs';
    public $itsAttachmentDir = 'sbs_files';

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsDetails = $this->getHeader();
    }

    public function getID()
    {
        return $this->itsID;
    }

    public function isEmpty()
    {
        $header = $this->getHeader();
        $detail = $this->getDetail();

        return empty($header) && empty($detail);
    }

    public static function getSELECT(): string
    {
        return <<<EOF
SELECT sbs.*,
  IF((select COUNT(*)
    FROM cal_bp AS bps LEFT JOIN tasks ON bps.id=tasks.parent_id AND tasks.module='BP'
    WHERE bps.module='SB' AND bps.parent_id=sbs.id
      AND tasks.status!='CLOSED' AND bps.status!='CLOSED') > 0,
      CONCAT(sbs.status, ' (BP)'),
      sbs.status
   ) AS vstatus
EOF;
    }

    public static function getFROM(): string
    {
        return <<<EOF
FROM sbs
EOF;
    }

    public function changeStatus($status, $uid, $options = '')
    {
        if (empty($this->itsID)) {
            return false;
        }
        $SET = '';
        $header = $this->getHeader();
        switch ($status) {
            case 'APPROVAL':
                //check if there are linked ers first
                $rows = $this->getAffectedEquipment();
                if (!count($rows)) {
                    return 'ERROR: Cannot change status until affected equipment list is created.';
                }
                break;
            case 'IMPLEMENTATION':
                if ($header['urgency'] === 'SB:COMPULSORY' &&
                    $header['numAffected'] > $header['num_tbd']
                ) {
                    return 'ERROR: Not all SSO responses have been receieved.';
                }
                break;
            case 'CLOSED':
                $SET = ', dt_closed=NOW()';
        }

        $allowed = $this->getStatusAllowed();
        if (!is_array($allowed)) {
            return $allowed;
        }

        $query = <<<EOF
UPDATE sbs
SET status=UCASE('$status')
    $SET
WHERE id=$this->itsID
LIMIT 1
EOF;
        $res = tldUtils::sqlQuery($query);
    }

    public function getStatusAllowed()
    {
        $res = [];
        $curStatus = $this->getStatus();
        //current status
        switch ($curStatus) {
            case 'PENDING':
                $res = ['fwd' => 'APPROVAL'];
                break;
            case 'APPROVAL':
                if ($this->getUrgency() === 'SB:COMPULSORY') {
                    $res = ['fwd' => 'IMPLEMENTATION'];
                } else {
                    $res = ['fwd' => 'ER_SELECTION'];
                }
                break;
            case 'ER_SELECTION':
                $res = ['fwd' => 'IMPLEMENTATION'];
                break;
            case 'IMPLEMENTATION':
                $res = ['fwd' => 'CLOSED'];
                break;
        }

        return $res;
    }

    /**
     * Get header information for this SB
     *
     * @return array
     */
    public function getHeader()
    {
        $id = $this->itsID;
        if (empty($id) || !is_numeric($id)) {
            return [];
        }
        $query = <<<EOF
SELECT sbs.*,
  IF((select COUNT(*)
    FROM cal_bp AS bps LEFT JOIN tasks ON bps.id=tasks.parent_id AND tasks.module='BP'
    WHERE bps.module='SB' AND bps.parent_id=sbs.id
      AND tasks.status!='CLOSED' AND bps.status!='CLOSED') > 0,
      CONCAT(sbs.status, ' (BP)'),
      sbs.status
   ) AS vstatus,
    (CASE sbs.urgency
    WHEN 'SB:COMPULSORY' THEN 'Mandatory Service Bulletin'
    WHEN 'SB:RECOMMENDED' THEN 'Recommended Service Bulletin'
    WHEN 'IB:IMPROVEMENT' THEN 'Improvement Informational Bulletin'
    WHEN 'IB:OPERATION' THEN 'Operational Informational Bulletin'
    ELSE ''
    END
    ) AS urgency_fullname,
    (SELECT COUNT(*)
    FROM mod_links
    WHERE mod_links.parent_id=sbs.id AND mod_links.module='SB'
    AND mod_links.type='ER'
    ) AS numAffected,
    (SELECT count(*)
    FROM mod_lists AS t2
    WHERE t2.module='SB' AND t2.parent_id=sbs.id AND t2.list_name='DONE'
    ) AS numDone,
    IF(sbs.urgency='SB:COMPULSORY',
            (SELECT COUNT(*)
            FROM mod_links
            WHERE mod_links.parent_id=sbs.id AND mod_links.module='SB'
        AND mod_links.type='ER'
            )
            ,(SELECT COUNT(*) FROM mod_lists as T3
            WHERE T3.module='SB' AND T3.parent_id=$id
            AND T3.list_name='TBD' AND T3.value LIKE 'Y'
            )
    ) AS num_tbd
FROM sbs
WHERE id=$id
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Get affected equipment range lists
     *
     * @return array
     */
    public function getDetail()
    {
        if (!$this->itsID || !is_numeric($this->itsID)) {
            return [];
        }
        $query = <<<EOF
SELECT * FROM sbs_lines
WHERE parent_id=$this->itsID
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get parts list
     *
     * @return array
     */
    public function getPartsList()
    {
        $query = <<<EOF
SELECT * FROM sbs_parts
WHERE parent_id=$this->itsID
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get linked files
     *
     * @param integer $id
     *
     * @return array string on error
     */
    public function getFiles($id = '')
    {
        $WHERE = is_numeric($id) ? " AND id=$id" : '';

        $query = <<<EOF
SELECT * FROM sbs_files
WHERE parent_id=$this->itsID
$WHERE
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function getLinksFromHere($type = '')
    {
        return tldModLink::byParent($this->itsID, 'SB', $type);
    }

    public function getLinksToHere($module = '')
    {
        return tldModLink::byItem($this->itsID, 'SB', $module);
    }

    /**
     * Get urgency field
     *
     * @return string
     */
    public function getUrgency()
    {
        return $this->itsDetails['urgency'];
    }

    /**
     * Get SB status field
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->itsDetails['status'];
    }

    /**
     * Get SB type
     *
     * @return mixed
     */
    public function getType()
    {
        return $this->itsDetails['sb_type'];
    }

    /**
     * Get SB title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->itsDetails['title'];
    }

    /**
     * alias for short desc
     *
     * @return string
     */
    public function getShortDesc()
    {
        return $this->getTitle();
    }

    /**
     * Send requested file to std out
     *
     */
    public function outFile()
    {
        $myFile = new basicFile(
            tldUtils::getPathToUploadFile(
                $this->itsFileDir,
                $this->itsDetails['sbs_file']
            )
        );
        $myFile->outFile();
    }

    /**
     * Send
     *
     * @param mixed $id
     */
    public function outAttachment($id)
    {
        $files = $this->getFiles($id);
        if (count($files) != 1) {
            return;
        }
        $myFile = new basicFile(
            tldUtils::getPathToUploadFile(
                $this->itsAttachmentDir,
                $files[0]['filename']
            )
        );
        $myFile->outFile();
    }

    /**
     * Get last 10 sbs
     *
     * @return array array of db rows
     */
    public static function getLatest($num = 10)
    {
        if ($num < 1) {
            $num = 1;
        }
        $query = <<<EOF
SELECT *,
  IF((select COUNT(*)
    FROM cal_bp AS bps LEFT JOIN tasks ON bps.id=tasks.parent_id AND tasks.module='BP'
    WHERE bps.module='SB' AND bps.parent_id=sbs.id
      AND tasks.status!='CLOSED' AND bps.status!='CLOSED') > 0,
      CONCAT(sbs.status, ' (BP)'),
      sbs.status
   ) AS vstatus,
    (SELECT COUNT(*)
    FROM mod_links
    WHERE mod_links.parent_id=sbs.id AND mod_links.module='SB'
        AND mod_links.type='ER'
    ) AS numAffected,
    (SELECT count(*)
    FROM mod_lists AS t2
    WHERE t2.module='SB' AND t2.parent_id=sbs.id AND t2.list_name='DONE'
    ) AS numDone,
    IF(sbs.urgency='SB:COMPULSORY',
        (SELECT COUNT(*)
        FROM mod_links
        WHERE mod_links.parent_id=sbs.id AND mod_links.module='SB'
            AND mod_links.type='ER'
        ),
        (SELECT COUNT(*) FROM mod_lists as T3
      WHERE T3.module='SB' AND T3.parent_id=id
        AND T3.list_name='TBD' AND T3.value IN ('Y','N')
      )
    ) AS num_tbd
FROM sbs
WHERE status!='PENDING'
ORDER BY id DESC
LIMIT $num
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get log of comments/history
     *
     * @return array
     */
    public function getLog()
    {
        return tldModLog::byParent($this->itsID, 'SB');
    }

    /**
     * Get a list of all equipment that this SB applies to
     *
     * @return array
     */
    public function getAffectedEquipment($where = '')
    {
        if (!empty($where)) {
            $WHERE = tldUtils::constructWhere($where);
        }
        $query = <<<EOF
SELECT er.*,
    IF( sb.urgency LIKE 'SB:COMPULSORY',
        'Y',
        (CASE
            (SELECT T3.value FROM mod_lists as T3
            WHERE T3.module='SB' AND T3.parent_id=sb.id
            AND T3.list_name='TBD' AND T3.list_key=er.id LIMIT 1)
        WHEN 'Y' THEN 'Y'
        WHEN 'N' THEN 'N'
        ELSE '?'
        END)
    ) AS tbd,
    IF((SELECT count(*) FROM mod_lists AS t3
        WHERE t3.module='SB' AND t3.parent_id=sb.id
        AND t3.list_name='DONE' AND t3.value=t2.item) > 0,
    'Y', 'N') AS done
FROM sbs AS sb
    JOIN mod_links AS t2 ON t2.parent_id=sb.id AND t2.module='SB' AND t2.type='ER'
    JOIN service AS er ON t2.item=er.id
WHERE sb.id=$this->itsID
EOF;
        if (!empty($WHERE)) {
            $query .= " HAVING $WHERE";
        }
        $query .= ' ORDER BY er.sales_org, er.customer_name, er.airport_code';

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get a list of all equipment that this SB applies to
     *
     * @param array|string $constraint
     * @param string Summary type
     *
     * @return array
     */
    public function getSummary($constraint = '', $summaryType = '')
    {
        $WHERE = !empty($constraint) ? ' AND ' . tldUtils::constructWhere($constraint) : '';

        switch ($summaryType) {
            case 'SPR':
                $SELECT = <<<EOF
spr.id AS spr_id,
spr.status AS spr_status
EOF;
                $FROM = <<<EOF
LEFT JOIN (mod_links AS sb_spr, mod_links AS spr_er, spr)
ON (
        (sb_spr.module='SB' AND sb_spr.parent_id=sb.id
        AND sb_spr.type='SPR' AND spr_er.module=sb_spr.type
        AND spr_er.parent_id=sb_spr.item AND spr_er.type='ER'
        AND spr_er.item=er.id AND spr.id=spr_er.parent_id)
    OR
        (sb_spr.parent_id=spr.id AND sb_spr.module='SPR'
        AND sb_spr.type='SB' AND sb_spr.item=sb.id
        AND spr_er.module=sb_spr.module AND spr_er.parent_id=spr.id
        AND spr_er.type='ER' AND spr_er.item=er.id)
)
EOF;
                break;
            case 'CSR':
                $SELECT = <<<EOF
csr.id AS csr_id,
csr.status AS csr_status
EOF;
                $FROM = <<<EOF
LEFT JOIN csr ON csr.module LIKE 'SB' AND csr.module_id=sb.id AND csr.parent_id=er.id
EOF;
                break;
            case 'NOT':
                $SELECT = <<<EOF
log.id AS log_id,
log.date AS log_dt
EOF;
                $FROM = <<<EOF
LEFT JOIN (mod_logs AS log, mod_links AS log_er)
ON (
        (log.module='SB' AND log.parent_id=sb.id
        AND log_er.module='LOG' AND log_er.parent_id=log.id
        AND log_er.type='ER' AND log_er.item=er.id)
    OR
        (log.module='SB' AND log.parent_id=sb.id
        AND log_er.module='LOG' AND log_er.parent_id=log.id
        AND log_er.type='ER' AND log_er.item=er.id)
)
EOF;
                break;
            default:
                $SELECT = <<<EOF
log.id AS log_id,
log.date AS log_dt,
csr.id AS csr_id,
csr.status AS csr_status,
spr.id AS spr_id,
spr.status AS spr_status
EOF;
                $FROM = <<<EOF
LEFT JOIN csr ON csr.module LIKE 'SB' AND csr.module_id=sb.id AND csr.parent_id=er.id
LEFT JOIN (mod_links AS sb_spr, mod_links AS spr_er, spr)
    ON (
            (sb_spr.module='SB' AND sb_spr.parent_id=sb.id
            AND sb_spr.type='SPR' AND spr_er.module=sb_spr.type
            AND spr_er.parent_id=sb_spr.item AND spr_er.type='ER'
            AND spr_er.item=er.id AND spr.id=spr_er.parent_id)
        OR
            (sb_spr.parent_id=spr.id AND sb_spr.module='SPR'
            AND sb_spr.type='SB' AND sb_spr.item=sb.id
            AND spr_er.module=sb_spr.module AND spr_er.parent_id=spr.id
            AND spr_er.type='ER' AND spr_er.item=er.id)
    )
LEFT JOIN (mod_logs AS log, mod_links AS log_er)
    ON (
            (log.module='SB' AND log.parent_id=sb.id
            AND log_er.module='LOG' AND log_er.parent_id=log.id
            AND log_er.type='ER' AND log_er.item=er.id)
        OR
            (log.module='SB' AND log.parent_id=sb.id
            AND log_er.module='LOG' AND log_er.parent_id=log.id
            AND log_er.type='ER' AND log_er.item=er.id)
)
EOF;
        }
        $query = <<<EOF
SELECT
    sb.id AS sb_id,
    sb.entered_date,
    sb.urgency AS urgency,
    er.*,
    (CASE
        (SELECT value FROM mod_lists as T3
         WHERE T3.module='SB' AND T3.parent_id=sb.id
         AND T3.list_name='TBD' AND T3.list_key=sb.id LIMIT 1
        )
        WHEN 'Y' THEN 'Y'
        WHEN 'N' THEN 'N'
        ELSE '?'
    END
    ) AS tbd,
    IF((SELECT count(*) FROM mod_lists AS t3
        WHERE t3.module='SB' AND t3.parent_id=sb.id
        AND t3.list_name='DONE' AND t3.value=t2.item) > 0, 'Y', 'N'
    ) AS done,
    $SELECT
FROM
    sbs as sb
    LEFT JOIN mod_links AS t2 ON t2.parent_id=sb.id AND t2.module='SB' AND t2.type='ER'
    LEFT JOIN service as er ON t2.item=er.id
    $FROM
WHERE sb.id=$this->itsID
    $WHERE
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     *Get a list of affected customers
     *
     * @return array
     */
    public function getAffectedCustomers()
    {
        $query = <<<EOF
SELECT t1.customer_name,
    COUNT(*) AS num_cust_er_affected,
    COUNT(
    IF((SELECT count(*) FROM mod_lists AS t3
    WHERE t3.module='SB' AND t3.parent_id=t2.parent_id
        AND t3.list_name='DONE' AND t3.value=t2.item) > 0,
    1, NULL)
    ) AS num_done,
COUNT(
IF(
(SELECT COUNT(*) FROM mod_lists as T3
  WHERE T3.module='SB' AND T3.parent_id=t2.parent_id
    AND T3.list_name='TBD' AND T3.value='Y' AND T3.list_key=t1.id
  )>0,
'Y', NULL)
) AS num_tbd,
(SELECT COUNT(DISTINCT t4.id)
    FROM extranet_users t4
        LEFT JOIN extranet_users_cust_link t5 ON t4.id=t5.parent_id
        LEFT JOIN extranet_cust_ref t6 ON t5.cust_ref_id=t6.id
WHERE t6.customer_name=t1.customer_name
) AS num_online
FROM service AS t1, mod_links AS t2
WHERE
    t2.parent_id=$this->itsID AND t2.module='SB'
    AND t2.type='ER' AND t2.item=t1.id
GROUP BY t1.customer_name
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     *Get a list of affected SSOs
     *
     * @return array
     */
    public function getAffectedSSO()
    {
        $query = <<<EOF
SELECT t1.sales_org,
    COUNT(*) AS sso_er_affected,
    COUNT(
    IF((SELECT count(*) FROM mod_lists AS t3
    WHERE t3.module='SB' AND t3.parent_id=$this->itsID
        AND t3.list_name='DONE' AND t3.value=t2.item) > 0,
    'Y', NULL)
    ) AS num_done
FROM service AS t1, mod_links AS t2
WHERE
    t2.parent_id=$this->itsID AND t2.module='SB'
    AND t2.type='ER' AND t2.item=t1.id
GROUP BY t1.sales_org
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function getOfflineCustomers()
    {
        $query = <<<EOF
SELECT distinct t1.customer_name
FROM mod_links AS t2, service AS t1
    LEFT JOIN extranet_cust_ref t3 ON t1.customer_name=t3.customer_name
WHERE
    t2.parent_id=$this->itsID AND t2.module='SB'
    AND t2.type='ER' AND t2.item=t1.id
    AND t3.id is null
ORDER BY t1.customer_name
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Add a comment to the log
     *
     * @param array Array structure containing 'poster' and 'comment'
     *
     * @return boolean
     */
    public function addLogEntry($id, $comment)
    {
        $a['parent_id'] = $this->itsID;
        $a['module'] = 'SB';
        $a['poster'] = $id;
        $a['comment'] = $comment;

        return tldModLog::insert($a);
    }

    public function addLinkTo($type, $item)
    {
        return tldModLink::insert('SB', $this->itsID, $type, $item);
    }

    /**
     * Add an er id to the list of To Be Done equipment
     *
     * @param mixed $erid if integer then only add one, if array, iterates array and adds all
     *                    in array
     */
    public function addTBD($erid, $tbd)
    {
        if (!is_numeric($erid)) {
            return 'ERROR: Need an integer';
        }
        $rows = $this->isInTBD($erid);
        if (count($rows)) {
            return $this->updateTBD($erid, $tbd);
        }
        $a['parent_id'] = $this->itsID;
        $a['module'] = 'SB';
        $a['list_name'] = 'TBD';
        $a['list_key'] = $erid;
        $a['value'] = $tbd;
        return tldModList::insert($a);
    }

    /**
     * Check if this erid is in the tbd list already
     *
     * @param integer $erid
     *
     * @return mixed
     */
    public function isInTBD($erid)
    {
        if (empty($erid)) {
            return;
        }

        return tldModList::byConstraints(
            [
                'parent_id' => $this->itsID,
                'module' => 'SB',
                'list_name' => 'TBD',
                'list_key' => $erid,
            ]
        );
    }

    /**
     * Update the mod_list with new TBD value
     *
     * @param integer $erid
     * @param string $tbd
     *
     * @return mixed string on error
     */
    public function updateTBD($erid, $tbd)
    {
        $rows = $this->isInTBD($erid);
        if (!count($rows)) {
            return "ERROR: no TBD rows found in mod list for $erid";
        }
        $row = $rows[0];
        //if no change then just return
        if ($row['value'] === $tbd) {
            return;
        }
        $m = new tldModList($row['id']);

        return $m->update(
            [
                'list_name' => $row['list_name'],
                'list_key' => $row['list_key'],
                'value' => $tbd,
            ]
        );
    }

    /**
     * Remove an er id from the list of To Be Done equipment
     *
     * @param mixed $erid if integer then only remove one, if array, iterates array and removes all
     *                    in array
     */
    public function removeTBD($erid)
    {
        if (!is_array($erid) && !is_numeric($erid)) {
            return 'ERROR: Need an integer or an array';
        }

        if (is_array($erid)) {
            $ids = $erid;
        } else {
            $ids[] = $erid;
        }
        foreach ($ids as $id) {
            $rows = $this->isInTBD($erid);
            if (count($rows)) {
                foreach ($rows as $rows) {
                    tldModList::delete($rows['id']);
                }
            }
        }
    }

    /**
     * Add an er id to the list of Done equipment
     *
     * @param mixed $erid if integer then only add one, if array, iterates array and adds all
     *                    in array
     */
    public function addDone($erid)
    {
        if (!is_numeric($erid)) {
            return 'ERROR: Need an integer';
        }
        $a['parent_id'] = $this->itsID;
        $a['module'] = 'SB';
        $a['list_name'] = 'DONE';
        $a['value'] = $erid;
        $error = tldModList::insert($a);
        if (!is_numeric($error)) {
            return $error;
        }
    }

    /**
     * Check if this erid is in the Done list already
     *
     * @param integer $erid
     *
     * @return mixed
     */
    public function isInDone($erid)
    {
        if (empty($erid)) {
            return;
        }

        return tldModList::byConstraints(
            [
                'parent_id' => $this->itsID,
                'module' => 'SB',
                'list_name' => 'DONE',
                'value' => $erid,
            ]
        );
    }

    /**
     * Remove an er id from the list of Done equipment
     *
     * @param mixed $erid if integer then only remove one, if array, iterates array and removes all
     *                    in array
     */
    public function removeDone($erid)
    {
        if (!is_array($erid) && !is_numeric($erid)) {
            return 'ERROR: Need an integer or an array';
        }

        if (is_array($erid)) {
            $ids = $erid;
        } else {
            $ids[] = $erid;
        }
        foreach ($ids as $id) {
            $rows = $this->isInDone($id);
            if (count($rows)) {
                foreach ($rows as $row) {
                    tldModList::delete($row['id']);
                }
            }
        }
    }

    public function refresh()
    {
        $this->itsHeader = $this->getHeader();
    }

    public function getTasks()
    {
        return tldTask::byParent($this->itsID, 'SB', 'ALL');
    }

    /**
     * Get related BPs to this SB
     *
     * @return array
     */
    public function getBPS($mode = '')
    {
        return tldBP::byParent($this->itsID, 'SB', $mode);
    }

    /**
     * Clear the list of affected equipment from mod_links
     *
     * @param mixed $id
     *
     * @return mixed
     */
    public function clearAffectedList($id)
    {
        //need to provide the id as a precaution
        if ($id != $this->itsID) {
            return "ERROR: Cannot clear TBD list, id's do not match...";
        }
        $rows = tldModLink::byParent($this->itsID, 'SB', 'ER');
        if (!count($rows)) {
            return;
        }
        $res = '';
        foreach ($rows as $row) {
            $res .= tldModLink::delete($row['id']);
        }

        return $res;
    }

    /**
     * Gernerate and save the list of affected equipment to mod_links
     *
     * @param mixed $id
     *
     * @return mixed
     */
    public function generateAffectedList($id)
    {
        //need to provide the id as a precaution
        if ($id != $this->itsID) {
            return "ERROR: Cannot generate TBD list, id's do not match...";
        }
        $query = <<<EOF
SELECT DISTINCT service.id AS erid
FROM service, sbs_lines
WHERE sbs_lines.parent_id=$this->itsID
    AND ((
        (service.sn BETWEEN sn_from AND sn_to AND service.model=sbs_lines.model)
        OR (sn_from = '*' AND sn_to = '*' AND service.model=sbs_lines.model)
        )
    OR sn_list REGEXP CONCAT('(^|[^a-zA-Z0-9])',service.sn,'($|[^a-zA-Z0-9])')
    )
    AND service.sn!=''
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);
        if (!count($rows)) {
            return;
        }
        $res = null;
        foreach ($rows as $row) {
            $e = tldModLink::insert('SB', $this->itsID, 'ER', $row['erid']);
            if (!is_numeric($e)) {
                $res .= $e;
            }
        }

        return $res;
    }

    /**
     * Get sbs data and count for all or sbs $id
     *
     */
    public static function getNumAffected($id = '')
    {
        $WHERE = '';
        if ($id) {
            $WHERE = " WHERE sbs.id=$id";
        }
        $query = <<<EOF
SELECT sbs.*,
    (SELECT COUNT(*)
    FROM mod_links
    WHERE mod_links.parent_id=sbs.id AND mod_links.module='SB'
        AND mod_links.type='ER'
    ) AS numAffected,
    (SELECT count(*)
    FROM mod_lists AS t2
    WHERE t2.module='SB' AND t2.parent_id=sbs.id AND t2.list_name='DONE'
    ) AS numDone
FROM sbs
    $WHERE
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get SB Statistics
     *
     * @param array $option keys=>by_month
     *
     * @return array
     */
    public static function getSBSStats($option = '')
    {
        $where = '';
        switch ($option['mode']) {
            //compulsory sbs for past 12 months, if factory not specified then returns for all factories
            case 'byCompulsoryFactoryPast12Months':
                $where = <<<EOF
urgency='SB:COMPULSORY'
AND PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(entered_date, '%Y%m')) < 12
EOF;
                if (isset($option['factory'])) {
                    $optionFactory = $option["factory"];
                    $where .= " AND factory='{$optionFactory}'";
                }
                break;
            case 'byRecommendedFactoryPast12Months':
                $where = <<<EOF
urgency='SB:RECOMMENDED'
AND PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(entered_date, '%Y%m')) < 12
EOF;
                if (isset($option['factory'])) {
                    $optionFactory = $option["factory"];
                    $where .= " AND factory='{$optionFactory}'";
                }
                break;
            case 'byFactory':
                if (isset($option['factory'])) {
                    $optionFactory = $option["factory"];
                    $where .= "factory='{$optionFactory}'";
                }
                break;
        }
        $WHERE = $where ? " WHERE $where" : '';


        $query = <<<EOF
SELECT DISTINCT(sbs.id), sbs.*,
    IF((select COUNT(*)
        FROM cal_bp AS bps LEFT JOIN tasks ON bps.id=tasks.parent_id AND tasks.module='BP'
        WHERE bps.module='SB' AND bps.parent_id=sbs.id
          AND tasks.status!='CLOSED' AND bps.status!='CLOSED') > 0,
          CONCAT(sbs.status, ' (BP)'),
          sbs.status
    ) AS vstatus,
    PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(entered_date, '%Y%m')) AS monthsPast,
    DATE_FORMAT(entered_date, '%Y%m') AS ym,
    (SELECT COUNT(*)
    FROM mod_links
    WHERE mod_links.parent_id=sbs.id AND mod_links.module='SB'
         AND mod_links.type='ER'
    ) AS numAffected,
    (SELECT count(*)
    FROM mod_lists AS t2
    WHERE t2.module='SB' AND t2.parent_id=sbs.id AND t2.list_name='DONE'
    ) AS numDone,
    IF(	sbs.urgency='SB:COMPULSORY',
        'N/A',
        (SELECT COUNT(*) FROM mod_lists as T3
          WHERE T3.module='SB' AND T3.parent_id=sbs.id
            AND T3.list_name='TBD' AND T3.value IN ('Y','N')
        )
    ) AS num_tbd
FROM sbs
    $WHERE
ORDER BY ym
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function getSBSWithoutFactory()
    {
        $query = <<<EOF
SELECT *
FROM sbs
WHERE LENGTH(factory) < 2
ORDER BY id DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get cpen comulsory sbs
     *
     */
    public function getOpenCompulsoryAffected($id = '')
    {
        if ($id) {
            $WHERE = " AND sbs_lines.parent_id=$id";
        }
        $query = <<<EOF
SELECT sbs.*,
  IF((select COUNT(*)
    FROM cal_bp AS bps LEFT JOIN tasks ON bps.id=tasks.parent_id AND tasks.module='BP'
    WHERE bps.module='SB' AND bps.parent_id=sbs.id
      AND tasks.status!='CLOSED' AND bps.status!='CLOSED') > 0,
      CONCAT(sbs.status, ' (BP)'),
      sbs.status
   ) AS vstatus,
    (SELECT COUNT(*)
    FROM mod_links
    WHERE mod_links.parent_id=sbs.id AND mod_links.module='SB'
        AND mod_links.type='ER'
    ) AS numAffected,
    (SELECT count(*)
    FROM mod_lists AS t2
    WHERE t2.module='SB' AND t2.parent_id=sbs.id AND t2.list_name='DONE'
    ) AS numDone
FROM sbs
WHERE
    sbs.urgency='SB:COMPULSORY'
    AND sbs.status='OPEN'
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function getOpenCompulsoryDone($id = '')
    {
        if ($id) {
            $WHERE = " AND sbs_lines.parent_id=$id";
        }
        $query = <<<EOF
SELECT sbs.*,sbs_lines.parent_id AS sbsID,
     count(DISTINCT service.id) as numDone
FROM sbs,sbs_lines,service, service_lines
WHERE
    sbs.id=sbs_lines.parent_id
    AND sbs.id=mod_links.parent_id AND mod_links.module='SB'
    AND mod_links.type='ER' AND mod_links.item=service.id
    AND service_lines.reason=sbs_lines.parent_id
    AND service_lines.work_type='BULLETIN'
    AND sbs.urgency='SB:COMPULSORY'
    AND sbs.status='OPEN'
GROUP  BY sbs_lines.parent_id
ORDER BY sbs_lines.parent_id
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function getOpenCompulsoryStats()
    {
        $affected = self::getOpenCompulsoryAffected();
        $done = self::getOpenCompulsoryDone();
        $result = [];
        foreach ($affected as $sb) {
            $result[$sb['sbsID']] = $sb;
        }
        foreach ($done as $sb) {
            $result[$sb['sbsID']]['numDone'] = $sb['numDone'];
        }

        return $result;
    }

    //STATIC FUNCTIONS

    /**
     * Get all customer related COMPULSORY SB AND extranet='yes' AND status='OPEN' by default
     * Options 'status' =>'all' returns all SB, not just
     *
     * @return array array of db rows
     */
    public static function byCustomer($cust, $options = '')
    {
        return self::createList(" UPPER(service.customer_name)=UPPER('$cust')", $options);
    }

    public static function byEquipment($id, $options = [])
    {
        $WHERE = $options['criteria'] ? ' AND ' . tldUtils::constructWhere($options['criteria']) : '';

        $query = <<<EOF
SELECT DISTINCT(sbs.id), sbs.*,
    IF((SELECT count(*) FROM mod_lists AS t3
    WHERE t3.module='SB' AND t3.parent_id=sbs.id
        AND t3.list_name='DONE' AND t3.value=$id) > 0,
    'Y', 'N') AS done
FROM mod_links,sbs
WHERE
    sbs.id=mod_links.parent_id AND mod_links.module='SB'
    AND mod_links.type='ER' AND mod_links.item=$id
    $WHERE
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getEquipmentByConstraints($a)
    {
        if (empty($a)) {
            return;
        }
        $WHERE = is_array($a) ? tldUtils::constructWhere($a) : $a;

        $query = <<<EOF
SELECT
    sb.id,
    sb.status,
    sb.sb_type,
    sb.urgency,
    sb.factory,
    sb.title,
    sb.entered_date,
    er.id AS erid,
    er.sn,
    er.model,
    er.airport_code AS apc,
    er.sales_org AS sso,
    er.customer_id,
    (SELECT customer_name FROM customers
        WHERE id=er.customer_id
    ) AS user_customer_display,
    er.buyer_customer_id,
    (SELECT customer_name FROM customers
        WHERE id=er.buyer_customer_id
    ) AS buyer_customer_display,
    IF(
        sb.urgency LIKE 'SB:COMPULSORY','Y',
        IF(
            (SELECT T3.value FROM mod_lists AS T3
                WHERE T3.module='SB' AND T3.parent_id=sb.id
                AND T3.list_name='TBD' AND T3.list_key=er.id
                LIMIT 1
            )!='Y',
            'N',
            'Y'
        )
    ) AS isTBD,
    IF(
        (SELECT t3.id FROM mod_lists AS t3
            WHERE t3.module='SB' AND t3.parent_id=sb.id
            AND t3.list_name='DONE' AND t3.value=t2.item
            LIMIT 1
        ) IS NOT NULL,
        'Y',
        'N'
    ) AS isDone
FROM
    sbs AS sb
    JOIN mod_links AS t2 ON t2.parent_id = sb.id
        AND t2.module='SB' AND t2.type='ER'
    JOIN service AS er ON t2.item = er.id
HAVING
    $WHERE
ORDER BY
    apc,user_customer_display,model
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * SB list of all applicable to a customer
     *
     * @param string $cust This is the customer name string to search for
     * @param array   or string constrainsts
     * @param string $option Use at the end of query. ex LIMIT 0,10 (null by default)
     */
    public static function byCustomerViewable($cust, $where = '', $option = '')
    {
        if (empty($cust)) {
            return;
        }
        $cust = TldDatabase::escape($cust);
        $WHERE = is_array($where) ? tldUtils::constructWhere($where) : $where;

        if (!empty($WHERE)) {
            $WHERE = " AND $WHERE ";
        }
        $OPTION = !empty($option) ? TldDatabase::escape($option) : '';

        $query = <<<EOF
(
SELECT DISTINCT(sbs.id) AS order_by, sbs.*,
COUNT(mod_lists.id) AS numAffected,
(SELECT COUNT(DISTINCT(t0.value)) FROM mod_lists as t0,service as t1 LEFT JOIN customers ON customers.id = t1.customer_id
    WHERE t0.parent_id=sbs.id AND t0.module LIKE 'SB' AND list_name LIKE 'DONE'
    AND t0.value=t1.id AND (UPPER(t1.customer_name)=UPPER('$cust') OR UPPER(customers.customer_name)=UPPER('$cust'))
)AS numDone
FROM service, mod_lists,sbs, customers
WHERE sbs.id=mod_lists.parent_id
    AND mod_lists.module='SB' AND mod_lists.list_name='TBD' AND mod_lists.list_key=service.id
    AND mod_lists.value='Y'
    AND (UPPER(service.customer_name)=UPPER('$cust') OR (customers.id = service.customer_id AND UPPER(customers.customer_name)=UPPER('$cust')))
    AND sbs.status IN("IMPLEMENTATION","CLOSED")
	$WHERE
GROUP BY sbs.id
)
UNION
(
SELECT DISTINCT(sbs.id) AS order_by, sbs.*,
COUNT(mod_links.id) AS numAffected,
(SELECT COUNT(DISTINCT(t0.value)) FROM mod_lists as t0,service as t1 LEFT JOIN customers ON customers.id = t1.customer_id
    WHERE t0.parent_id=sbs.id AND t0.module LIKE 'SB' AND list_name LIKE 'DONE'
    AND t0.value=t1.id AND (UPPER(t1.customer_name)=UPPER('$cust') OR UPPER(customers.customer_name)=UPPER('$cust'))
)AS numDone
FROM service, mod_links,sbs, customers
WHERE sbs.id=mod_links.parent_id
    AND mod_links.module='SB' AND mod_links.type='ER' AND mod_links.item=service.id
    AND sbs.urgency='SB:COMPULSORY'
    AND (UPPER(service.customer_name)=UPPER('$cust') OR (customers.id = service.customer_id AND UPPER(customers.customer_name)=UPPER('$cust')))
    AND sbs.status IN("IMPLEMENTATION","CLOSED")
	$WHERE
GROUP BY sbs.id
)
ORDER BY order_by
	$OPTION
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Search SBs by key fields
     *
     * @param array $a
     *
     * @return array
     */
    public static function search($a)
    {
        $query = <<<EOF
SELECT DISTINCT t1.id, t1.*,
  IF((select COUNT(*)
    FROM cal_bp AS bps LEFT JOIN tasks ON bps.id=tasks.parent_id AND tasks.module='BP'
    WHERE bps.module='SB' AND bps.parent_id=t1.id
      AND tasks.status!='CLOSED' AND bps.status!='CLOSED') > 0,
      CONCAT(t1.status, ' (BP)'),
      t1.status
   ) AS vstatus
FROM sbs as t1 LEFT JOIN sbs_lines as t2 ON t1.id=t2.parent_id
WHERE t1.status like '$a'
    OR t1.sb_type like '$a'
    OR t1.factory_sb_number like '$a'
    OR t1.title like '$a'
    OR t1.description like '$a'
    OR t1.urgency like '$a'
    OR t1.brand like '$a'
    OR t1.factory like '$a'
    OR t2.model like '$a'
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     *
     * @param $constraints
     * @param $options
     *
     * @return mixed
     */
    public static function byConstraints($constraints, $options = '')
    {
        $where = '';
        if (empty($options['orderBy'])) {
            $orderBy = 'factory, vstatus';
        } else {
            $orderBy = TldDatabase::escape($options['orderBy']);
        }
        if (is_array($constraints)) {
            $where = tldUtils::constructWhere($constraints);
        }
        $query = <<<EOF
SELECT
    sbs.*,
    IF((select COUNT(*)
        FROM cal_bp AS bps LEFT JOIN tasks ON bps.id=tasks.parent_id AND tasks.module='BP'
        WHERE bps.module='SB' AND bps.parent_id=sbs.id
          AND tasks.status!='CLOSED' AND bps.status!='CLOSED') > 0,
          CONCAT(sbs.status, ' (BP)'),
          sbs.status
    ) AS vstatus
FROM sbs
EOF;
        if ($where) {
            $query .= " HAVING $where";
        }
        $query .= <<<EOF
ORDER BY $orderBy
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     *
     * @param $mode
     * @param $options
     *
     * @return mixed
     */
    public static function countByERPStatus()
    {
        $query = <<<EOF
SELECT factory,
    IF((SELECT COUNT(*) FROM cal_bp AS bps
        LEFT JOIN tasks ON bps.id=tasks.parent_id AND tasks.module='BP'
        WHERE bps.module LIKE 'SB'
            AND bps.parent_id=sbs.id AND bps.status!='CLOSED'
            AND tasks.status!='CLOSED'
        ) > 0,
        CONCAT(sbs.status, ' (BP)'),
        sbs.status
    ) AS vstatus,
    count(*) AS num
FROM sbs
GROUP BY factory, vstatus
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function countERBySSO($a = '')
    {
        $WHERE = '';
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "AND $WHERE";
        }
        $query = <<<EOF
SELECT
    er.sales_org AS sso,
    COUNT(*) AS affected,
    SUM(
        IF(
            sb.urgency LIKE 'SB:COMPULSORY', 1,
            IF(
                (SELECT T3.value FROM mod_lists AS T3
                    WHERE T3.module='SB' AND T3.parent_id=sb.id
                    AND T3.list_name='TBD' AND T3.list_key=er.id
                    LIMIT 1
                )!='Y',
                0,
                1
            )
        )
    ) AS tbd,
    SUM(
        IF(
            (SELECT t3.id FROM mod_lists AS t3
                WHERE t3.module='SB' AND t3.parent_id=sb.id
                AND t3.list_name='DONE' AND t3.value=t2.item
            ) IS NOT NULL,
            1,
            0
        )
    ) AS done,
    (
        SUM(
            IF(
                sb.urgency LIKE 'SB:COMPULSORY', 1,
                IF(
                    (SELECT T3.value FROM mod_lists AS T3
                        WHERE T3.module='SB' AND T3.parent_id=sb.id
                        AND T3.list_name='TBD' AND T3.list_key=er.id
                        LIMIT 1
                    )!='Y',
                    0,
                    1
                )
            )
        )
        -
        SUM(
            IF(
                (SELECT t3.id FROM mod_lists AS t3
                    WHERE t3.module='SB' AND t3.parent_id=sb.id
                    AND t3.list_name='DONE' AND t3.value=t2.item
                ) IS NOT NULL,
                1,
                0
            )
        )
    ) AS not_done
FROM
    sbs AS sb
    JOIN mod_links AS t2 ON t2.parent_id = sb.id
        AND t2.module='SB' AND t2.type='ER'
    JOIN service AS er ON t2.item = er.id
WHERE
    sb.status LIKE 'IMPLEMENTATION'
    $WHERE
GROUP BY
    sso
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function countBySSOStatus()
    {
        $query = <<<EOF
SELECT
    IF(
        (SELECT COUNT(*) FROM cal_bp AS bps
            LEFT JOIN tasks ON bps.id=tasks.parent_id AND tasks.module='BP'
            WHERE bps.module LIKE 'SB' AND bps.parent_id=sbs.id
                AND bps.status!='CLOSED' AND tasks.status!='CLOSED') > 0,
        CONCAT(sbs.status, ' (BP)'),
        sbs.status
    ) AS vstatus,
    ers.sales_org,
    COUNT(*) AS num
FROM
    sbs
    JOIN mod_links AS sbs_ers ON sbs.id=sbs_ers.parent_id
        AND sbs_ers.module='SB' AND sbs_ers.type='ER'
    JOIN service AS ers ON sbs_ers.item=ers.id
GROUP BY
    vstatus, ers.sales_org
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     *
     * @param $sso
     * @param $erp
     * @param $options
     *
     * @return mixed
     */
    public static function bySSOStatus($sso, $status, $options = '')
    {
        $a = [];
        if ($sso !== 'ALL') {
            $a['sales_org'] = $sso;
        }
        if ($status !== 'ALL') {
            $a['vstatus'] = $status;
        }
        $HAVING = $a ? 'HAVING ' . tldUtils::constructWhere($a) : '';

        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT,
    ers.id AS er_id,
    ers.sn AS er_sn,
    ers.sales_org
$FROM
    JOIN mod_links AS sbs_ers ON sbs.id=sbs_ers.parent_id
        AND sbs_ers.module='SB' AND sbs_ers.type='ER'
    JOIN service AS ers ON sbs_ers.item=ers.id
$HAVING
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     *
     * @param $sso
     * @param $erp
     * @param $options
     *
     * @return mixed
     */
    public static function byERPStatus($erp, $status, $options = '')
    {
        $a = [];
        if ($erp !== 'ALL') {
            $a['factory'] = $erp;
        }
        if ($status !== 'ALL') {
            $a['vstatus'] = $status;
        }

        if (isset($options['orderBy'])) {
            $opts['orderBy'] = $options['orderBy'];
        }

        return self::byConstraints($a, $opts);
    }

    public static function byQuery($a, $opt = '')
    {
        $WHERE = is_array($a) ? ' WHERE ' . tldUtils::constructWhere($a, $opt) : '';
        $query = <<<EOF
		SELECT * FROM sbs
            $WHERE
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get SB count by status, severity for a specific customer
     *
     * @param string $customer name
     *
     * @return array
     */
    public function countByCustomerSeverityStatus($cust)
    {
        $query = <<<EOF
(
SELECT sbs.urgency, sbs.status, COUNT(DISTINCT(sbs.id)) AS num
FROM service, mod_links,sbs
WHERE sbs.id=mod_links.parent_id
    AND mod_links.module='SB' AND mod_links.type='ER' AND mod_links.item=service.id
    AND sbs.urgency='SB:COMPULSORY'
    AND UPPER(service.customer_name)=UPPER('$cust')
    AND sbs.status IN("IMPLEMENTATION","CLOSED")
GROUP BY sbs.urgency, sbs.status
)
UNION
(
SELECT sbs.urgency, sbs.status, COUNT(DISTINCT(sbs.id)) AS num
FROM service, mod_lists,sbs
WHERE sbs.id=mod_lists.parent_id
    AND mod_lists.module='SB' AND mod_lists.list_name='TBD' AND mod_lists.list_key=service.id
    AND mod_lists.value='Y'
    AND UPPER(service.customer_name)=UPPER('$cust')
    AND sbs.status IN("IMPLEMENTATION","CLOSED")
GROUP BY sbs.urgency, sbs.status
)
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get SB list by status, severity for a specific customer
     *
     * @param string $status
     * @param string $urgency
     * @param string $customer name
     *
     * @return array of SB
     */
    public function byCustomerSeverityStatus($status, $urgency, $cust)
    {
        $a = [];
        if ($urgency !== 'ALL') {
            $a['sbs.urgency'] = TldDatabase::escape($urgency);
        }
        if ($status !== 'ALL') {
            $a['sbs.status'] = TldDatabase::escape($status);
        }
        $WHERE = $a ? tldUtils::constructWhere($a) : '';

        return self::byCustomerViewable($cust, $WHERE);
    }

    public static function createList($WHERE, $options = '')
    {
        if (isset($options['criteria'])) {
            $WHERE .= ' AND ' . tldUtils::constructWhere($options['criteria']);
        }
        if ($WHERE) {
            $WHERE = " AND $WHERE";
        }
        $query = <<<EOF
(
SELECT DISTINCT(sbs.id) AS order_by, sbs.*,
IF((select COUNT(*)
FROM cal_bp AS bps LEFT JOIN tasks ON bps.id=tasks.parent_id AND tasks.module='BP'
WHERE bps.module='SB' AND bps.parent_id=sbs.id
    AND tasks.status!='CLOSED' AND bps.status!='CLOSED') > 0,
CONCAT(sbs.status, ' (BP)'),
sbs.status
) AS vstatus,
COUNT(mod_lists.id) AS numAffected,
(SELECT COUNT(DISTINCT(t0.value)) FROM mod_lists as t0,service as t1
    WHERE t0.parent_id=sbs.id AND t0.module LIKE 'SB' AND list_name LIKE 'DONE'
    AND t0.value=t1.id
)AS numDone
FROM service, mod_lists, sbs
WHERE sbs.id=mod_lists.parent_id
    AND mod_lists.module='SB' AND mod_lists.list_name='TBD'
    AND mod_lists.list_key=service.id AND mod_lists.value='Y'
$WHERE
GROUP BY sbs.id
)
UNION
(
SELECT DISTINCT(sbs.id) AS order_by, sbs.*,
IF((select COUNT(*)
FROM cal_bp AS bps LEFT JOIN tasks ON bps.id=tasks.parent_id AND tasks.module='BP'
WHERE bps.module='SB' AND bps.parent_id=sbs.id
    AND tasks.status!='CLOSED' AND bps.status!='CLOSED') > 0,
CONCAT(sbs.status, ' (BP)'),
sbs.status
) AS vstatus,
COUNT(mod_links.id) AS numAffected,
(SELECT COUNT(DISTINCT(t0.value)) FROM mod_lists as t0,service as t1
    WHERE t0.parent_id=sbs.id AND t0.module LIKE 'SB' AND list_name LIKE 'DONE'
    AND t0.value=t1.id
)AS numDone
FROM service, mod_links,sbs
WHERE sbs.id=mod_links.parent_id
    AND mod_links.module='SB' AND mod_links.type='ER' AND mod_links.item=service.id
    AND sbs.urgency='SB:COMPULSORY'
$WHERE
GROUP BY sbs.id
)
ORDER BY order_by
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }
}

/**
 * Class for handling NTO information
 *
 * @package Support
 */
class tldNTO
{
    public $itsID;
    public $itsDetails;
    public $theUploadFileDir = 'nto';

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsDetails = tldUtils::getSqlRowToAssocArray("select * from nto where id=$id");
    }

    public function getFilename()
    {
        throw new \Exception('This module has been migrated, this function should not be used anymore');
    }

    public function getFile()
    {
        throw new \Exception('This module has been migrated, this function should not be used anymore');
    }

    public function outFile()
    {
        throw new \Exception('This module has been migrated, this function should not be used anymore');
    }

    public function getNTOListByModel($model)
    {
        throw new \Exception('This module has been migrated, this function should not be used anymore');
    }

    public static function getMatrix()
    {
        throw new \Exception('This module has been migrated, this function should not be used anymore');
    }
}

/**
 * Class for manipulating equipment types from products_categories table
 *
 * @package Support
 */
class tldType
{
    public function __construct($id)
    {
        if (is_numeric($id)) {
            $this->itsID = $id;
        } else {
            $row = $this->byName($id);
            $this->itsID = $row['id'];
        }
        $this->itsDetails = $this->getHeader();
    }

    public function getHeader()
    {
        $query = <<<EOF
SELECT *
FROM products_categories
WHERE id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function getName($lang = '')
    {
        if (empty($lang)) {
            $lang = 'en';
        }

        return $this->itsDetails[$lang];
    }

    public function byName($name)
    {
        $query = <<<EOF
SELECT *
FROM products_categories
WHERE en='$name'
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public static function getTypes($lang = 'en', $options = '')
    {
        if (empty($lang)) {
            $lang = 'en';
        }
        $query = <<<EOF
SELECT $lang
FROM products_categories
ORDER BY $lang
EOF;

        return tldUtils::getSqlToAssocArray($query, $options, [$lang, $lang]);
    }

    public static function getList($option = '')
    {
        $query = <<<EOF
SELECT id, en
FROM products_categories
ORDER BY en
EOF;
        switch ($option) {
            case 'smartyOptions_Name':
                return tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['en', 'en']);
            default:
                return tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['id', 'en']);
        }
    }

    /**
     * Get the type by model name
     *
     * @param $model string name
     *
     * @return array
     */
    public static function byModel($model)
    {
        if (empty($model)) {
            return [];
        }
        $query = <<<EOF
SELECT t2.* FROM models AS t1
JOIN products_categories AS t2 ON t1.parent_id=t2.id
WHERE t1.model LIKE '$model'
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }
}

/**
 * Class for creating and manipulating models
 *
 * @package Support
 */
class tldModel
{
    public $itsDetails;
    public $itsID;

    public function __construct($id)
    {
        if (is_numeric($id)) {
            $this->itsID = $id;
        } else {
            $row = $this->byModel($id);
            $this->itsID = $row[0]['id'];
        }

        $this->itsDetails = $this->getHeader();
    }

    public function getName()
    {
        return $this->itsDetails['model'];
    }

    public function getERP()
    {
        return $this->itsDetails['erpid'];
    }

    /**
     * Method to get model header information
     */
    public function getHeader(): array
    {
        if (empty($this->itsID)) {
            return [];
        }
        $query = <<<EOF
			SELECT * FROM models
			WHERE id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Method to get the list of model in english with option smartyOptions
     */
    public static function getList(): array
    {
        return array_column(self::getModels(), 'model', 'model');
    }

    /**
     * Method to get list of Models
     *
     * @param $lang    string 'en' by default
     * @param $options string query options
     */
    public static function getModels($lang = 'en', $options = ''): array
    {
        $query = <<<EOF
			SELECT model FROM models ORDER BY model
EOF;

        return tldUtils::getSqlToAssocArray($query, $options);
    }

    public function getModelsByERP($erp, $lang = 'en', $options = ''): array
    {
        $query = <<<EOF
			SELECT model FROM models WHERE erpid=$erp ORDER BY model
EOF;

        return tldUtils::getSqlToAssocArray($query, $options);
    }

    /**
     * Method to get model families
     */
    public static function getFamilies(): array
    {
        $query = <<<EOF
			SELECT SUBSTRING_INDEX(model, '-', 1) as Families
			FROM models GROUP BY Families
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Method to get list of models by model name
     *
     * @param $model string
     */
    public function byModel($model): array
    {
        $model = TldDatabase::escape(stripslashes($model));
        $query = <<<EOF
			SELECT * FROM models
			WHERE model='$model'
			ORDER BY model
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get names of models owned by a customer
     *
     * @param $custname string
     * @param $options  string for smartyOptions
     *
     * @return array
     */
    public function byCustomer($custname, $options = ''): array
    {
        $query = <<<EOF
			SELECT
				distinct service.model,
				models.*
			FROM service
				LEFT JOIN models ON service.model=models.model
			WHERE
				service.customer_name='$custname'
			ORDER BY service.model
EOF;
        if ($options === 'smartyOptions') {
            return tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['model', 'model']);
        }

        return tldUtils::getSqlToAssocArray($query, $options);
    }

    /**
     * @deprecated
     */
    public function updateAndTranfer($oldModel, $newModel)
    {
        throw new Exception('This is not used anymore');
    }
}

/**
 *    Class for handling equipment information
 *
 * @package Support
 */
class tldEquipment
{

    public $itsId;
    public $itsID;
    public $itsERP;
    /**
     * @var array
     */
    public $itsDetails;

    public function __construct($id, $lazy = false)
    {
        $this->itsId = $id; //for backwards compatibility
        $this->itsID = $id;
        $this->itsDetails = $lazy ? [] : $this->getHeader();

        if (!$lazy) {
            $model = new tldModel($this->itsDetails['model']);
            $this->itsERP = $model->getERP();
        }
    }

    public function getID()
    {
        return $this->itsDetails['id'];
    }

    public function getParentID()
    {
        return $this->itsDetails['parent_id'];
    }

    public function isEmpty()
    {
        return empty($this->itsDetails);
    }

    public function getERP()
    {
        return $this->itsERP;
    }

    public function getESRID()
    {
        return $this->itsDetails['esr_id'];
    }

    public function getCreateDate()
    {
        return $this->itsDetails['date_entered'];
    }

    public function getFactory()
    {
        return $this->itsDetails['man_location'];
    }

    public static function getUsedFactoryList()
    {
        $query = "SELECT DISTINCT(man_location) FROM service WHERE man_location!='' ORDER BY man_location";

        return tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['man_location', 'man_location']);
    }

    public function getFactoryID()
    {
        return tldLocation::getIDByLocation($this->getFactory());
    }

    public function getFactoryERP()
    {
        return tldLocation::getERPByLocation($this->getFactory());
    }

    public function getSN()
    {
        return $this->itsDetails['sn'];
    }

    public function getHours()
    {
        return $this->itsDetails['hours'];
    }

    public function getCustomerName()
    {
        return $this->itsDetails['user_customer_display'];
    }

    public function getBuyerCustomerID()
    {
        return $this->itsDetails['buyer_customer_id'];
    }

    public function getMaintainerCustomerID()
    {
        return $this->itsDetails['maintainer_customer_id'];
    }

    public function getUserCustomerID()
    {
        return $this->itsDetails['customer_id'];
    }

    public function getSSO()
    {
        return $this->itsDetails['sales_org'];
    }

    public function getSSOService()
    {
        return $this->itsDetails['sso_service'];
    }

    public function getWorkOrder()
    {
        return $this->itsDetails['t_pdno'];
    }

    public function getProject()
    {
        return $this->itsDetails['t_prno'];
    }

    public function getSSOID()
    {
        return tldLocation::getIDByLocation($this->getSSO());
    }

    public function getSSOERP()
    {
        return tldLocation::getERPByLocation($this->getSSO());
    }

    public function getAPC()
    {
        return $this->itsDetails['airport_code'];
    }

    public function getAirport()
    {
        return $this->itsDetails['airport_code'];
    }

    public function getModel()
    {
        return $this->itsDetails['model'];
    }

    public function refresh()
    {
        $this->itsDetails = $this->getHeader();
    }

    public function getServiceContractID()
    {
        return $this->itsDetails['maintenance_contract_ref'];
    }

    public function getServiceContractERP()
    {
        return $this->itsDetails['maintenance_contract_erp'];
    }

    public function isServiceContracted()
    {
        return !empty($this->itsDetails['maintenance_contract_ref']);
    }

    public function getGT()
    {
        return $this->itsDetails['dgt_act'];
    }

    public function isGT()
    {
        return !$this->isYT() && $this->itsDetails['dgt_act'] != '0000-00-00';
    }

    public function getYT()
    {
        return $this->itsDetails['dyt'];
    }

    public function getCombinationMode()
    {
        return $this->itsDetails['comb_mod'];
    }

    public function getShipDate()
    {
        return $this->itsDetails['date_shipped'];
    }
    function getCommissioningDate()
    {
        return $this->itsDetails['dt_commissioned'];
    }

    public function getFirstGT()
    {
        return $this->itsDetails['dgt_com'];
    }

    public function isYT()
    {
        // Check if shipped
        if ($this->getShipDate() != '0000-00-00') {
            return false;
        }
        // Check YT if set
        if ($this->getYT() === '0000-00-00') {
            return false;
        }
        // Check interval with GT
        $yt = new DateTime($this->getYT());
        $gt = new DateTime($this->getGT());

        return $yt >= $gt;
    }

    public function isTLDLink()
    {
        return (bool)$this->itsDetails['tld_link'];
    }

    public function isSimActive()
    {
        return 'ACTIVE' === $this->itsDetails['sim_status'];
    }

    public function getSimStatus()
    {
        return $this->itsDetails['sim_status'];
    }

    public function getFMSContractLength()
    {
        return $this->itsDetails['fms_contract_length'];
    }

    public function isLight()
    {
        return (bool)$this->itsDetails['light'];
    }

    public function getFMSEndUseDate()
    {
        return $this->itsDetails['fms_end_use_date'] ?: '0000-00-00';
    }

    public function getCalculatedFMSEndUseDate(): string
    {
        return $this->itsDetails['calculated_fms_end_use_date'];
    }

    public function insertSN($p)
    {
        if (empty($this->itsID)) {
            return 'Not object context';
        }
        $fields = ['component', 'model', 'serial', 'brand'];
        $query = <<<EOF
INSERT INTO service_serials
SET parent_id = $this->itsID,
EOF;
        $query .= tldUtils::getSqlSet($p, $fields);

        return tldUtils::sqlInsert($query);
    }

    public function insertFile($tmpFile, $p)
    {
        if (empty($this->itsID)) {
            return 'Not object context';
        }
        if (empty($tmpFile)) {
            return 'File is missing';
        }
        $timestamp = time();
        $upload_dir = $GLOBALS['UPLOADS_PATH'] . '/service_files';
        $filename = "$timestamp-" . basicFile::cleanupName($p['filename']);
        $copy = copy($tmpFile, "$upload_dir/$filename");
        if (!$copy) {
            return 'Could not upload file';
        }
        $fields = ['description'];
        $query = <<<EOF
INSERT INTO service_files
SET parent_id = $this->itsID, filename = '$filename', date = NOW(),
EOF;
        $query .= tldUtils::getSqlSet($p, $fields);

        return tldUtils::sqlInsert($query);
    }

    private static function insert($p)
    {
        // Prepare fields
        $fields = [
            'parent_id',
            'sor_lid',
            'esrid',
            'type',
            'model',
            'eng_tier',
            'comb_mod',
            'sn',
            'cust_asset_num',
            'entered_by',
            'man_location',
            'customer_name',
            'customer_contact',
            'customer_ref',
            'agent_name',
            'location_short',
            'airport_code',
            'delivery_location',
            'del_ctry',
            'warranty_length',
            'warranty_length_hours',
            'warranty_conditions',
            'options_desc',
            'sales_org',
            'sso_service',
            'sls_orno',
            'sales_rep',
            'cust_equipment_type',
            'dt_commissioned',
            'date_shipped',
            'date_arrived',
            'date_warranty_end',
            'mfg_comments',
            't_prno',
            't_pdno',
            'dgt_est',
            'dgt_com',
            'dgt_rev',
            'ddel_est2',
            'odp_note',
            'dgt_act',
            'ddel_act2',
            'ddel_act',
            'er_batch_qty',
            'diml',
            'dimw',
            'dimh',
            'dimk',
            'light',
            'fms_contract_length',
            'fms_end_use_date',
        ];
        if (!empty($p['customer_id'])) {
            $fields[] = 'customer_id';
        }
        if (!empty($p['buyer_customer_id'])) {
            $fields[] = 'buyer_customer_id';
        }
        if (!empty($p['maintainer_customer_id'])) {
            $fields[] = 'maintainer_customer_id';
        }
        if (empty($p['er_batch_qty'])) {
            $p['er_batch_qty'] = 1;
        }

        if (!empty($p['maintenance_contract_ref'])) {
            $fields[] = 'maintenance_contract_ref';
        }

        $model = new tldModel($p['model']);
        $p['light'] = empty($model->itsDetails) ? 0 : (int)$model->itsDetails['light'];

        $SET = tldUtils::getSqlSet($p, $fields);
        $query = <<<EOF
INSERT INTO service SET status='PENDING', date_entered=NOW(), state='ACTIVE', $SET
EOF;

        return tldUtils::sqlInsert($query);
    }

    public static function create($p)
    {
        $erid = self::insert($p);
        if (is_string($erid)) {
            return $erid;
        }
        // Generate SN
        $serialNumber = sprintf('%s%s', (isset($p['sn']) && 'P' === substr($p['sn'], 0, 1) ? 'P' : 'T'), $erid);
        $query = "UPDATE service SET sn='$serialNumber', t_prno='$serialNumber' WHERE id=$erid";
        tldUtils::sqlQuery($query);

        // Return ID
        return $erid;
    }

    public static function createPAS($p)
    {
        $erid = self::insert($p);
        if (is_string($erid)) {
            return $erid;
        }
        // Generate SN
        $serialNumber = "P$erid";
        $query = "UPDATE service SET sn='$serialNumber', t_prno='$serialNumber' WHERE id=$erid";
        tldUtils::sqlQuery($query);

        // Return ID
        return $erid;
    }

    public function duplicate($userEmail)
    {
        if (empty($this->itsID)) {
            return 'INTERNAL ERROR: could not duplicate Equipment Record, internal identifier is undefined.';
        }

        $defaultValues = [
            'parent_id' => 0,
            'sor_lid' => 0,
            'sor_uid' => 0,
            'esrid' => 0,
            'cust_asset_num' => '',
            'dt_commissioned' => '0000-00-00',
            'date_entered' => date('Y-m-d'),
            'date_shipped' => '0000-00-00',
            'date_arrived' => '0000-00-00',
            't_prno' => '',
            't_pdno' => '',
            'dgt_est' => '0000-00-00',
            'dgt_com' => '0000-00-00',
            'dgt_rev' => '0000-00-00',
            'ddel_est2' => '0000-00-00',
            'dgt_act' => '0000-00-00',
            'ddel_act2' => '0000-00-00',
            'ddel_act' => '0000-00-00',
            'fsm_end_use_date' => '0000-00-00',
            'entered_by' => $userEmail,
        ];

        return self::create(array_merge($this->itsDetails, $defaultValues));
    }

    public function isPAS()
    {
        return preg_match('#^P[0-9]+$#', $this->getSN());
    }

    public function isSOLLinked()
    {
        $query = <<<EOF
        SELECT t2.id as solid
        FROM service as t4
        LEFT JOIN sor_units AS t3 ON t3.id=t4.sor_uid
        LEFT JOIN sor_lines AS t2 ON t2.id=t3.parent_id
        WHERE t4.id = {$this->itsID}
EOF;
        $result = tldUtils::getSqlRowToAssocArray($query);

        return !empty($result['solid']);
    }

    /**
     * Generic method to update ER data
     * Difference with tldEquipment::update() is mainly that the data will be sanitized using tldUtils::cleanupFormInput
     *
     * @param array $a
     * @param array $fields
     *
     * @return string on error
     */
    public function updateRecord($a, $fields)
    {
        $erId = $this->itsID;
        if (empty($erId)) {
            return 'Not object context';
        }
        if (empty($a) || empty($fields)) {
            return 'Empty parameter';
        }
        // Sanity check regarding fields on data
        foreach ($fields as $field) {
            if (!isset($a[$field])) {
                return "Sanity check failed with field $field";
            }
        }

        // Automatically copy the ER Light flag from the model
        if (in_array('model', $fields, true)) {
            $model = new tldModel($a['model']);
            $a['light'] = empty($model->itsDetails) ? 0 : (int)$model->itsDetails['light'];
            $fields[] = 'light';
        }

        // Construct query
        $SET = tldUtils::getSqlSet(tldUtils::cleanupFormInput($a), $fields);
        $query = "UPDATE service SET $SET WHERE id=" . $this->itsID;

        return tldUtils::sqlQuery($query);
    }

    public function delete()
    {
        $query = "DELETE FROM service WHERE id=$this->itsID LIMIT 1";

        return tldUtils::sqlExecute($query);
    }

    public static function deleteSN($sn_id)
    {
        if (empty($sn_id) || !is_numeric($sn_id)) {
            return 'SN ID is not set or not valid!';
        }
        $query = "DELETE FROM service_serials WHERE id=$sn_id LIMIT 1";

        return tldUtils::sqlExecute($query);
    }

    public static function deleteFile($file_id)
    {
        if (empty($file_id) || !is_numeric($file_id)) {
            return 'File ID is not set or not valid!';
        }
        $queryFile = "SELECT filename FROM service_files WHERE id=$file_id";
        $file = tldUtils::getSqlRowToAssocArray($queryFile);
        if (unlink($GLOBALS['UPLOADS_PATH'] . '/service_files/' . $file['filename'])) {
            $query = "DELETE FROM service_files WHERE id=$file_id LIMIT 1";

            return tldUtils::sqlExecute($query);
        }

        return 'Could not delete the file physically';
    }

    public static function outCustomerFile($file_id)
    {
        if (empty($file_id) || !is_numeric($file_id)) {
            return 'File ID is not set or not valid!';
        }
        // get file path
        $queryFile = "SELECT filename FROM service_files WHERE id=$file_id";
        $fileVars = tldUtils::getSqlRowToAssocArray($queryFile);
        $filePath = $GLOBALS['UPLOADS_PATH'] . '/service_files/' . $fileVars['filename'];
        // create file instance
        $file = new basicFile($filePath);
        $file->out($fileVars['filename'], true);
        exit;
    }

    public function update($a, $fields = [])
    {
        if (empty($a)) {
            return 'Empty data';
        }

        // Automatically copy the ER Light flag from the model
        if (isset($a['model'])) {
            $model = new tldModel($a['model']);
            $a['light'] = empty($model->itsDetails) ? 0 : (int)$model->itsDetails['light'];
            if (is_array($fields) && in_array('model', $fields, true)) {
                $fields[] = 'light';
            }
        }
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "UPDATE service SET $SET WHERE id=" . $this->itsID;

        return tldUtils::sqlQuery($query);
    }

    public static function updateSN($a = '', $fields = '', $sn_id = '')
    {
        if (empty($a) || empty($sn_id)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "UPDATE service_serials SET $SET WHERE id=$sn_id";

        return tldUtils::sqlQuery($query);
    }

    /**
     * Check if this Equipment is Publishable
     *
     * @return bool
     */
    public function isPublishable()
    {
        //get the ER Id from header
        $erid = $this->itsDetails['id'];

        $query = <<<EOF
SELECT publishable
FROM service
WHERE id=$erid
EOF;
        $result = tldUtils::getSqlRowToAssocArray($query);

        return $result['publishable'] === 'Y';
    }

    /**
     * set Equipment to Publishable
     *
     * @return bool
     */
    public function setPublishable()
    {
        $query = "UPDATE service SET publishable = 'Y' WHERE id={$this->itsId}";

        return tldUtils::sqlQuery($query);
    }

    public function isCustomerFullyAffected()
    {
        return !empty($this->itsDetails['buyer_customer_id']) && !empty($this->itsDetails['customer_id']);
    }

    public function isAvailableForSale()
    {
        return ($this->itsDetails['user_customer_display'] === '**AVAILABLE FOR SALE**');
    }

    public function setAvailableForSale($uid)
    {
        if (empty($this->itsId)) {
            return 'ERROR: no ERID set';
        }
        if (empty($uid)) {
            return 'ERROR: need userid of person setting availability.';
        }
        $query = <<<EOF
		UPDATE service SET customer_name='**AVAILABLE FOR SALE**',customer_id=(SELECT customers.id FROM customers WHERE customers.customer_name LIKE '**AVAILABLE FOR SALE**' LIMIT 1),
		buyer_customer_id=(SELECT customers.id FROM customers WHERE customers.customer_name LIKE '**AVAILABLE FOR SALE**' LIMIT 1)
		WHERE id=$this->itsId
		LIMIT 1
EOF;
        $e = tldUtils::sqlQuery($query);
        if (empty($e)) {
            $this->addLogEntry(
                $uid,
                'Customer name changed from ' . $this->itsDetails['user_customer_display']
            );
        } else {
            return "ERROR: could not update ER, message was $e";
        }
    }

    /*
     * Returns a tldCBOM object if possible
     *
     * Needs to have the man_location set and translatable from the text to
     * a company number and also a project number set, and of course, it has
     * to exist in baan!!
     *
     *
     */
    public function getCBOM()
    {
        throw new \Exception('This is no longer used');
    }

    public static function getSELECT(): string
    {
        return <<<EOF
SELECT
    er.*,
    pcs.zh,
    pcs.fr,
    (SELECT COUNT(*) FROM service WHERE parent_id=er.id AND comb_mod LIKE 'ER COMBINED') AS nbCombined,
    (SELECT COUNT(*) FROM service WHERE parent_id=er.id AND comb_mod LIKE 'PRE-ASSEMBLY') AS nbPreAssembly,
    IF(er.customer_id > 0,
        (SELECT customers.customer_name FROM customers WHERE customers.id=er.customer_id),
        customer_name
    ) AS user_customer_display,
    (SELECT customers.customer_name FROM customers
        WHERE customers.id=er.buyer_customer_id
    ) AS buyer_customer_display,
    (SELECT customers.customer_name FROM customers
        WHERE customers.id=er.maintainer_customer_id
    ) AS maintainer_customer_display,
        (SELECT CONCAT(apc.airport_code,', ',COALESCE(countries.name, 'no_country'), ', ',apc.city_name,', ',apc.airport_name)
        FROM airport_codes AS apc LEFT JOIN countries ON countries.iso_code_2=apc.ctry_code_2
        WHERE apc.airport_code=er.airport_code LIMIT 1
    ) AS apc_fullname,
    (SELECT countries.name
        FROM airport_codes AS apc LEFT JOIN countries ON countries.iso_code_2=apc.ctry_code_2
        WHERE apc.airport_code=er.airport_code LIMIT 1
    ) AS apc_country,
    (SELECT parent_id FROM sor_units
        WHERE id=sor_uid
    ) AS sor_lid,
    er.esrid AS esr_id,
    CASE
        WHEN (SELECT COUNT(*) FROM csr WHERE csr.parent_id=er.id AND csr.work_type='Commissioning' AND csr.status IN ('COMPLETED', 'CLOSED')) > 0 THEN 'COMMISSIONED'
        WHEN date_shipped != '0000-00-00' THEN 'SHIPPED'
        WHEN date_shipped='0000-00-00' THEN 'IN PRODUCTION'
        ELSE ''
    END AS status,
    CASE
        WHEN COALESCE(fms_end_use_date , '0000-00-00') != '0000-00-00' THEN fms_end_use_date
        WHEN fms_contract_length > 0 AND dgt_act != '0000-00-00' THEN DATE_ADD(dgt_act, INTERVAL (fms_contract_length + 3) MONTH)
        WHEN fms_contract_length > 0 THEN 'Undefined now as unit is not GT'
        ELSE 'Not relevant, contract length is 0'
    END AS calculated_fms_end_use_date
EOF;
    }

    public static function getFROM(): string
    {
        return <<<EOF
FROM
    service AS er
    LEFT JOIN products_categories AS pcs ON pcs.en=er.type
EOF;
    }

    public function getHeader()
    {
        if (!$this->itsId) {
            return [];
        }

        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = "$SELECT $FROM WHERE er.id=$this->itsId";

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public static function getStatusList()
    {
        return [
            'COMMISIONED' => 'COMMISIONED',
            'DELIVERED' => 'DELIVERED',
            'SHIPPED' => 'SHIPPED',
            'IN PRODUCTION' => 'IN_PRODUCTION',
        ];
    }

    public static function getSimStatusList()
    {
        return [
            'INACTIVE' => 'INACTIVE',
            'ACTIVE' => 'ACTIVE',
            'PAUSE' => 'PAUSE'
        ];
    }

    public function getStatus()
    {
        return $this->itsDetails['status'];
    }

    /**
     * Get Warranty ER information and status
     *
     * @return array
     */
    public function getWarrantyDetails()
    {
        $query = <<<EOF
SELECT
	id, sn, date_shipped, warranty_length, warranty_length_hours,
	DATE_ADD( date_shipped, INTERVAL warranty_length MONTH ) AS warranty_end_date,
	CASE
		WHEN date_shipped='0000-00-00' OR date_shipped IS NULL
		THEN "NOT SHIPPED"
	    WHEN DATE_ADD( date_shipped, INTERVAL warranty_length MONTH ) > NOW( )
	    	AND hours < 2000 AND warranty_conditions=''
		THEN "EFFECTIVE"
	    WHEN DATE_ADD( date_shipped, INTERVAL warranty_length MONTH ) > NOW( )
	        AND (hours BETWEEN 2000 AND 3000 OR (hours < 2000
	        AND warranty_conditions!=''))
		THEN "MAYBE EXPIRED"
	    ELSE "EXPIRED"
	END
	AS is_under_warranty,
	warranty_conditions
FROM service
WHERE id=$this->itsId
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function getASMEmail()
    {
        $query = <<<EOF
SELECT people.email as asm_email
FROM
	service AS t4
    LEFT JOIN sor_units AS t3 ON t3.id=t4.sor_uid
    LEFT JOIN sor_lines AS t2 ON t2.id=t3.parent_id
    LEFT JOIN sor AS t1 ON t1.id=t2.parent_id
    LEFT JOIN people ON people.id = t1.asm
WHERE t4.id=$this->itsId
EOF;
        $result = tldUtils::getSqlRowToAssocArray($query);

        return $result['asm_email'];
    }

    /**
     * Get list of ER components
     *
     * @return array
     */
    public function getSerials($id = '')
    {
        if ($id != '' && is_numeric($id)) {
            $id = " AND id = $id";
        }
        $query = <<<EOF
SELECT * FROM service_serials
WHERE parent_id = $this->itsId $id
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getUsedModels()
    {
        $query = 'SELECT DISTINCT(model) FROM service ORDER BY model';

        return tldUtils::getSqlToAssocArray($query);
    }

    public function addSerial($a)
    {
        if (empty($a)) {
            return;
        }
        $query = <<<EOF
INSERT INTO service_serials
SET parent_id=$this->itsId,
EOF;
        $query .= tldUtils::getSqlSet(
            $a,
            [
                'component',
                'model',
                'serial',
                'brand',
            ]
        );

        return tldUtils::sqlInsert($query);
    }

    /**
     * Check if the ER is in combination (either PASS or COMBINED)
     *
     * @return boolean
     */
    public function isInCombinationMode()
    {
        if ($this->isCombinationCombined() || $this->isCombinationPreAssembly()) {
            return true;
        }
        if ($this->hasCombinedER() || $this->hasPreAssemblyER()) {
            return true;
        }

        return false;
    }

    /**
     * Know if combined to a PRIMARY ER
     *
     * @return boolean
     */
    public function isCombinationCombined()
    {
        return $this->getCombinationMode() === 'ER COMBINED';
    }

    public function isCombinationPreAssembly()
    {
        return $this->getCombinationMode() === 'PRE-ASSEMBLY';
    }

    /**
     * Know if at least one ER is combined
     *
     * @return boolean
     */
    public function hasCombinedER()
    {
        return $this->itsDetails['nbCombined'] > 0;
    }

    public function hasPreAssemblyER()
    {
        return $this->itsDetails['nbPreAssembly'] > 0;
    }

    /**
     * Get the list of combined ER
     *
     * @return array
     */
    public function getCombinationErList($combMode = null)
    {
        $a = ['parent_id' => $this->itsID];
        if (!empty($combMode)) {
            $a['comb_mod'] = $combMode;
        }

        return self::byConstraints($a);
    }

    public static function getAvailablePreAssemblyList()
    {
        return self::byConstraints("sn REGEXP '^P[0-9]+$' AND comb_mod LIKE ''");
    }

    /**
     * Get list of combination mode
     *
     * @return array
     */
    public static function getCombinationModeList()
    {
        return [
            'ER COMBINED' => 'ER COMBINED',
            'PRE-ASSEMBLY' => 'PRE-ASSEMBLY',
        ];
    }

    /**
     * Add a combination ER
     *
     * @param string $mode
     * @param int $erid
     *
     * @return mixed
     */
    public function addCombination($mode, $erid)
    {
        $SET = tldUtils::getSqlSet(['parent_id' => $this->itsId, 'comb_mod' => $mode]);
        $query = "UPDATE service SET $SET WHERE id=$erid";

        return tldUtils::sqlQuery($query);
    }

    /**
     * Dissociate a combined ER
     *
     * @param int $erid
     *
     * @return mixed
     */
    public function removeCombination($erid)
    {
        $SET = tldUtils::getSqlSet(['parent_id' => 0, 'comb_mod' => '']);
        $query = "UPDATE service SET $SET WHERE id=$erid AND parent_id=$this->itsId";

        return tldUtils::sqlQuery($query);
    }

    /**
     * Function to add a Warranty claim to the Equipment
     *
     * @param array $a not compulsory (used to get details from TOC)
     *
     * @return mixed int or string error
     */
    public function addWC($a = null)
    {
        if (is_array($a) && !empty($a)) {
            $a += $this->getHeader();
        } else {
            $a = $this->getHeader();
        }

        $warrantyAcceptedByFactory = 'N';
        if (null !== $this->getSOR_UID()) {
            $sorUnit = new tldSORUnit($this->getSOR_UID());
            if (null !== $sorUnit->getParentID()) {
                $sol = new tldSOL($sorUnit->getParentID());
                $warrantyAcceptedByFactory = $sol->itsHeader['conf_wrty_erp'];
            }
        }

        $p['parent_id'] = $a['id'];
        $p['warranty_status'] = 'PENDING';
        $p['entered_by'] = $GLOBALS['PHP_AUTH_USER'];
        $p['claim_date'] = date('Y-m-d');
        $p['warranty_details'] = <<<EOF
<p>
    SHIPPED: {$a['date_shipped']},
</p>
<p>
    WARRANTY LEN: {$a['warranty_length']},
</p>
<p>
    WARRANTY END: {$a['date_warranty_end']}
</p>
<p>
    <b>Special Warranty Conditions:</b><br>
    {$a['warranty_conditions']}
</p>
<p>
    <b>Warranty Conditions accepted by factory? </b>$warrantyAcceptedByFactory
</p>
EOF;
        $p['customer_name'] = $a['user_customer_display'];
        $p['equipment_location'] = $a['delivery_location'];
        $p['type'] = $a['type'];
        $p['model'] = $a['model'];
        $p['man_location'] = $a['man_location'];
        $p['sales_org'] = $a['sales_org'];
        $p['serial_number'] = $a['sn'];
        $p['problem_desc'] = $a['problem_desc'];
        $p['hours'] = $a['hours'];
        $p['er_operation_status'] = $a['er_operation_status'];
        $p['technician'] = $a['technician'];
        $p = tldUtils::cleanupFormInput($p);

        return tldWC::insert($p);
    }

    /**
     * Set the hourmeter of the ER
     *
     * @param int $hours
     * @param string $module (optional)
     * @param int|string $module_id (optional)
     *
     * @return mixed values int/string error
     */
    public function setHourMeter($hours, $module = '', $module_id = '')
    {
        if (empty($this->itsId)) {
            return 'Not object context';
        }
        // Check data validity
        if (empty($hours) || !is_numeric($hours) || $hours < 0) {
            return 'Hour meter null or invalid';
        }

        if ('' === $module) {
            return 'Module not set';
        }

        if ('SCM' === $module) {
            return 'SCM Hour meter should not be used anymore';
        }

        // Do nothing if lower
        if ($hours < $this->getHours()) {
            return 'Value lower than actual hour meter';
        }

        //Get API ER
        global $kernel;
        try {
            $client = $kernel->getContainer()->get(Client::class);
        } catch (Exception $e) {
            return 'ERROR: Could not get API client. Reason: ' . $e->getMessage();
        }

        try {
            $equipmentRecord = $client->findOneBy('/equipment_records', ['legacyId' => $this->itsId]);
        } catch (ClientException $exception) {
            return "_(ERROR: Failed to find equipment record)";
        }

        // Create hour meter in API (this will update ER hour meter)
        switch ($module) {
            case 'CSR':
                throw new \Exception('Should not exist anymore !');
                break;
            case 'WC':
                $url = 'support/equipment_record/warranty_claim_service_record_hour_meter_transactions';
                $key = 'warrantyClaimLegacyId';
                break;
            case 'TOC':
                $url = 'support/equipment_record/toc_hour_meter_transactions';
                $key = 'tocLegacyId';
                break;
            default:
                $url = 'support/equipment_record/equipment_record_hour_meter_transactions';
                $key = null;
        }

        $payload = [
            'hourMeter' => (int) $hours,
            'module' => $module,
            'equipmentRecord' => $equipmentRecord['@id'],
        ];

        if (null !== $key) {
            $payload = [...$payload, $key => (int) $module_id];
        }

        try {
            $client->save($url, $payload);
        } catch (ClientException $exception) {
            return $exception->getMessage();
        }

        // done!
        return;
    }

    /**
     * Function to track hourmeter transactions
     *
     * @param array $a
     *
     * @return string on error
     */
    public function addHourMeterTransaction($a)
    {
        if (empty($a)) {
            return 'empty parameters';
        }
        $fields = ['parent_id', 'hourmeter', 'module', 'module_id'];
        $a['parent_id'] = $this->getID();
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO service_hourmeter SET dt=NOW(), $SET";

        return tldUtils::sqlInsert($query);
    }

    public function getHourMeterTransactionByID($tid)
    {
        return tldUtils::getSqlRowToAssocArray("SELECT * FROM service_hourmeter WHERE id=$tid");
    }

    public function updateHourMeterTransactionByID($tid, $a)
    {
        $SET = tldUtils::getSqlSet($a);
        $query = "UPDATE service_hourmeter SET $SET WHERE id=$tid";

        return tldUtils::sqlQuery($query);
    }

    public static function getHourMeterTransactionsByConstraints($a)
    {
        if (is_array($a)) {
            $HAVING = tldUtils::constructWhere($a);
        } else {
            $HAVING = $a;
        }
        if (!empty($a)) {
            $HAVING = "HAVING $HAVING";
        }
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT,
    hm.id AS hourTrans_id,
    hm.dt AS hourTrans_dt,
    hm.hourmeter AS hourTrans_hourmeter,
    hm.module AS hourTrans_module,
    hm.module_id AS hourTrans_module_id
$FROM
    LEFT JOIN service_hourmeter AS hm ON hm.parent_id=er.id
$HAVING
ORDER BY
    hourTrans_id DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get ER hourmeter history
     *
     * @return array data
     */
    public function getHourMeterHistory()
    {
        $query = "SELECT * FROM service_hourmeter WHERE parent_id={$this->getID()} ORDER BY id DESC";

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Update Airport code of the equipment
     *
     * @param string $apc
     *
     * @return string on error
     */
    public function setAPC($apc)
    {
        if (empty($this->itsId)) {
            return 'Not object context';
        }
        if (empty($apc)) {
            return 'APC empty';
        }

        $country = tldCountry::getCountryNameByAirportCode($apc);
        
        return $this->updateRecord(
            ['airport_code' => $apc, 'del_ctry' => empty($country) ? '' : $country[$apc]],
            ['airport_code', 'del_ctry']
        );
    }

    /**
     * Update Delivery location
     *
     * @param string $location
     *
     * @return string on error
     */
    public function setDeliveryLocation($location)
    {
        return self::updateRecord(
            ['delivery_location' => $location],
            ['delivery_location']
        );
    }

    public function setCommissioningDate($date)
    {
        return self::updateRecord(
            ['dt_commissioned' => $date],
            ['dt_commissioned']
        );
    }

    public function setProjectNumber($project)
    {
        return self::updateRecord(
            ['t_prno' => $project],
            ['t_prno']
        );
    }


    public function setCustomerAssetNumber($asset)
    {
        return self::updateRecord(
            ['cust_asset_num' => $asset],
            ['cust_asset_num']
        );
    }

    public function setYT($date)
    {
        return self::updateRecord(
            ['dyt' => $date],
            ['dyt']
        );
    }

    public function resetYT($uid, $reason)
    {
        $e = $this->setYT('0000-00-00');
        if (is_string($e)) {
            return $e;
        }
        // Add log
        $comment = <<<EOF
YT reset
Reason: $reason
EOF;
        $this->addLogEntry($uid, $comment);
        // Send notification
        $user = new tldUser($uid);
        $erp = tldLocation::getERPByID($user->getBUID());
        $sso = $this->getSSOERP();
        $message = <<<EOF
<p>ER#{$this->getSN()} YT has been reset by {$user->getFullname()}</p>
<p>Reason:<br>$reason</p>
<p><a href="https://www.tld-gse.com/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view&id={$this->getID()}">Click here to see ER#{$this->getID()}</a></p>
EOF;

        $recipients = [
            $erp => [
                'role_QE',
                'role_PM',
                'role_QAM',
                'role_PSM',
                'role_PSE',
                'role_EM',
                'role_COO',
                'role_FC',
                'role_MLM',
                'role_planner',
            ],
        ];
        if ($this->isSOLLinked()) {
            $recipients[$sso] = ['role_SA', 'role_TM'];
        }

        return tldGroup::emailMultipleGroups(
            $recipients,
            'noreply@tld-gse.com',
            "ER#{$this->getSN()} YT reset",
            $message,
            ['cc' => [$this->getASMEmail(), $user->getEmail()]]
        );
    }

    public function manageFMSContractInformationAfterGreenTag($uid)
    {
        $hasSimCard = $hasObu = false;
        foreach ($this->getTldLinkComponents() as $component) {
            if (!$hasSimCard && $component === 'SIM CARD, LINK') {
                $hasSimCard = true;
                continue;
            }

            if (!$hasObu && $component === 'OBU, LINK') {
                $hasObu = true;
            }
        }

        if ($hasSimCard && $hasObu){
            $fmsValues = ['sim_status' => 'ACTIVE', 'tld_link' => 1] ;
            $comment = <<<EOF
FMS information update
SIM Status set to 'ACTIVE'
TLD Link set to 'YES'
EOF;
            if (in_array($this->getServiceContractID(), ['AES', 'XOPS', 'SAS', 'TAS'], true)) {
                $fmsValues['fms_end_use_date'] = '2999-09-09';
                $comment .= "FMS end use date set to '{$fmsValues['fms_end_use_date']}'";
            }
            $this->update($fmsValues,array_keys($fmsValues));
            $this->addLogEntry($uid, $comment);
        }
    }

    public function updateShipAndGTDates($uid, $p)
    {
        if ($this->getYT() !== '0000-00-00') {
            return 'YT must be reset before updating Shipped and GT dates';
        }

        // TTS#56305: block Green Tag while the linked SOL Engineering Flag is active
        if (!empty($p['dgt_act']) && '0000-00-00' !== $p['dgt_act'] && null !== $this->getSOR_UID()) {
            $sorUnit = new tldSORUnit($this->getSOR_UID());
            if (null !== $sorUnit->getParentID()) {
                $sol = new tldSOL($sorUnit->getParentID());
                if ($sol->hasEngineeringFlag()) {
                    return 'Green Tag is not allowed while the Engineering Flag is active on the linked SOL';
                }
            }
        }

        $fields = ['date_shipped', 'dgt_act'];
        $p['date_shipped'] = empty($p['date_shipped']) ? '0000-00-00' : $p['date_shipped'];
        $p['dgt_act'] = empty($p['dgt_act']) ? '0000-00-00' : $p['dgt_act'];
        // Add log
        $comment = <<<EOF
GT/Shipped manual date update
Reason: {$p['reason']}
GT Date: from "{$this->getGT()}" to "{$p['dgt_act']}"
Shipped Date: from "{$this->getShipDate()}" to "{$p['date_shipped']}"
EOF;
        if ('0000-00-00' === $this->getFirstGT() && '0000-00-00' !== $p['dgt_act']) {
            $fields[] = 'dgt_com';
            $p['dgt_com'] = $p['dgt_act'];
            $comment .= "First GT Date: from '{$this->getFirstGT()}' to '{$p['dgt_com']}'";
        }

        $this->updateRecord($p, $fields);
        $this->manageFMSContractInformationAfterGreenTag($uid);
        $this->addLogEntry($uid, $comment);
        // Send notification
        $user = new tldUser($uid);
        $erp = $this->getFactoryERP();
        $message = <<<EOF
<p>ER#{$this->getSN()} GT/Shipped date manualy updated by {$user->getFullname()}</p>
<p>GT Date: from '{$this->getGT()}' to '{$p['dgt_act']}'</p>
<p>Shipped Date: from '{$this->getShipDate()}' to '{$p['date_shipped']}'</p>
<p>Reason:<br>{$p['reason']}</p>
<p><a href="https://www.tld-gse.com/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view&id={$this->getID()}">Click here to see ER#{$this->getID()}</a></p>
EOF;

        $recipients = [
            $erp => [
                'role_PSM',
                'role_PSE',
                'role_PSA',
                'role_COO',
                'role_QA',
                'role_QE',
                'role_PM'
            ],
        ];
        return tldGroup::emailMultipleGroups(
            $recipients,
            'noreply@tld-gse.com',
            "ER#{$this->getSN()} GT/Shipped date updated manualy by the QAM",
            $message,
            ['cc' => [$user->getEmail()]]
        );
    }

    public function resetActualGreenTagDate(): ?string {
        return $this->updateRecord(['dgt_act' => '0000-00-00'], ['dgt_act']);
    }
    public function getState()
    {
        return $this->itsDetails['state'];
    }

    public static function getStateList()
    {
        return ['ACTIVE' => 'ACTIVE', 'RETIRED' => 'RETIRED'];
    }

    public function setState($state, $user, $comment = '')
    {
        // Check state
        if (!in_array($state, self::getStateList(), true)) {
            return 'Invalid ER state';
        }
        // Update state
        $e = $this->update(['state' => $state, 'operation_status' => $state]);
        if (is_string($e)) {
            return $e;
        }
        // Log
        $log = TldDatabase::escape("ER state updated to '$state'");
        if (!empty($comment)) {
            $log .= "<br>$comment";
        }
        $this->addLogEntry($user->getID(), $log);

        return $e;
    }

    public function setYellowTag($dtYTEntered, $field)
    {

        try {
            $dyt = new DateTime($dtYTEntered);
        } finally {
            $dateTimeErrors = DateTime::getLastErrors();
        }

        if (!empty($dateTimeErrors['warning_count']) || !empty($dateTimeErrors['error_count'])) {
            $error[] = "ERROR 2 : Unit {$this->getSN()} - Wrong data submitted for {$field} -> {$dtYTEntered}";
            return $error;
        }
        // Reset is forbidden
        if ($dtYTEntered === '0000-00-00') {
            $error[] = "ERROR: Unit {$this->getSN()} - Reset of YT is not permitted, you need to GT (after YT) if you need to ship the unit";
            return $error;
        }
        // Check if Ship date is null
        if ($this->getShipDate() !== '0000-00-00') {
            $error[] = "ERROR: Unit {$this->getSN()} - Already shipped the {$this->getShipDate()}, can not YT";
            return $error;
        }
        // Check if new YT < old YT
        $dyt_old = new DateTime($this->getYT());
        if ($dyt < $dyt_old) {
            $error[] = "ERROR: Unit {$this->getSN()} - New {$field} {$dtYTEntered} can not be before actual {$field} {$this->getYT()}";
            return $error;
        }
        // Update date
        $errorUpdate = $this->setYT($dtYTEntered);

        if (is_string($errorUpdate)) {
            $error[] = "INTERNAL ERROR: Unit {$this->getSN()} - {$field} not updated. Reason: $errorUpdate";
            return $error;
        }

        // Reset Actual GT Date
        $e = $this->resetActualGreenTagDate();
        if(is_string($e)){
            $error[] = sprintf('An error occurred while resetting Actual GT date. Reason: %s', $e);
            return $error;
        }
        return [];
    }

    public function setGreenTagDt($dtGTEntered, $userId, $odp)
    {
        try {
            $dgt = new DateTime($dtGTEntered);
        } finally {
            $dateTimeErrors = DateTime::getLastErrors();
        }
        $formattedGt = $dgt->format('Y-m-d');
        if (!empty($dateTimeErrors['warning_count']) || !empty($dateTimeErrors['error_count'])) {
            $error[] = "ERROR 2 : Unit {$this->getSN()} - Wrong data submitted for GT -> {$dtGTEntered}";
            return $error;
        }
        if ($odp['nb_crabs'] > 0) {
            $error[] = "ERROR: Unit {$this->getSn()} - {$odp['nb_crabs']} CRAB(s) still not CLOSED";
            return $error;
        }

        // check if GT >= YT
        if ($this->getYT() !== '0000-00-00' && (new DateTime($this->getYT()) > $dgt)) {
            $error[] = "ERROR: Unit {$this->getSn()} - GT  {$formattedGt} < YT  {$this->getYT()}";
            return $error;
        }
        // Check if GT < shipped date
        $date_shipped = new DateTime($this->getShipDate());
        $interval = $dgt->diff($date_shipped);
        if ($this->getShipDate() !== '0000-00-00' && $interval->format('%R') === '-') {
            $error[] = "ERROR: Unit {$this->getSn()} - GT  {$formattedGt} > shipped date {$this->getShipDate()}";
            return $error;
        }
        // ***************** Special cases: last 3 days of month **************************
        if ($odp['dgt_com'] === '0000-00-00') { // No revalidation
            $userList = [];
            $lastDay = $dgt->format('Y-m-t');
            $lastDt = new DateTime($lastDay);
            $lastDt->modify('-1 day');
            $lastDay1 = $lastDt->format('Y-m-d');
            $lastDt->modify('-1 day');
            $lastDay2 = $lastDt->format('Y-m-d');
            // Last day of month - GCEO
            if (in_array($formattedGt, [$lastDay, $lastDay1, $lastDay2])) {
                $seqTpl = null;
                switch ($formattedGt) {
                    case $lastDay:
                        $seqTpl = 53;                                   // odp.lategt.approval.gceo
                        // Special CC
                        $gcoo = new tldGroup('role_GCOO', 900);
                        $gcooUsers = $gcoo->getUserList();
                        foreach ($gcooUsers as $usr) {
                            $userList[] = $usr['id'];
                        }
                        $rceo = new tldGroup('role_CEO', $odp['bu']);
                        $rceoUsers = $rceo->getUserList();
                        foreach ($rceoUsers as $usr) {
                            $userList[] = $usr['id'];
                        }
                        break;
                    case $lastDay1:
                        $seqTpl = 54;                                   // odp.lategt.approval.gcoo
                        // Special CC
                        $rceo = new tldGroup('role_CEO', $odp['bu']);
                        $rceoUsers = $rceo->getUserList();
                        foreach ($rceoUsers as $usr) {
                            $userList[] = $usr['id'];
                        }
                        break;
                    case $lastDay2:
                        $seqTpl = 55;
                        break;
                    default:
                        break;

                }
                $task = <<<EOF
    Late GT Request for Unit <a href=\'https://www.tld-gse.com/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view&id={$this->getID()}\'>{$this->getSN()}</a>, {$odp['erp_fullname']} for {$odp['sso_fullname']}, {$odp['model']} for {$odp['sor_buyer_customer_display']} 
    <br>
    Actual GT date requested: {$formattedGt}
    Factory EXW Promise is: {$odp['ddel_est1']}
    Estimated GT Date is: {$odp['dgt_rev']}
EOF;
                $seqMsg = tldSEQ::insert(
                    $this->getID(),
                    [
                        'assignor' => $userId,
                        'assignee' => $userId,
                        'due_date' => ['value' => 1, 'unit' => 'DAY'],
                        'task' => TldDatabase::escape($task),
                        'close_params' => ['dgt_act' => $formattedGt],
                    ],
                    $seqTpl
                );
                if (is_string($seqMsg)) {
                    $error[] = "ERROR: Unit {$this->getSN()} - actual GT date: Can not create new Late GT approval sequence<br>Reason: $seqMsg";
                    return $error;
                }

                $error[] = "WARNING: Unit {$this->getSN()} - actual GT date: special approval process is required to GT a Unit in the last 3 days of the month.<br>
                                       <a href=\"https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$seqMsg\">SEQ#$seqMsg</a> has been created.";
                $seq = new tldSEQ($seqMsg);
                // Notify
                $seq->notifyAssignee(
                    "\n<br><a href=\"https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$seqMsg\">See SEQ#$seqMsg on website</a>",
                    "Late GT Request for Unit {$this->getsn()} - SEQ#$seqMsg"
                );
                // List personnel to be CC in SEQ
                if (!empty($row['solid'])) {
                    $erp = tldLocation::getERPByLocation($odp['man_location']);
                    $coo = new tldGroup('role_COO', $erp);
                    $cooUsers = $coo->getUserList();
                    foreach ($cooUsers as $usr) {
                        $userList[] = $usr['id'];
                    }
                    $qams = new tldGroup('role_QAM', $erp);
                    $qamsUsers = $qams->getUserList();
                    foreach ($qamsUsers as $usr) {
                        $userList[] = $usr['id'];
                    }
                    $psm = new tldGroup('role_PSM', $erp);
                    $psmUsers = $psm->getUserList();
                    foreach ($psmUsers as $usr) {
                        $userList[] = $usr['id'];
                    }
                    $pse = new tldGroup('role_PSE', $erp);
                    $pseUsers = $pse->getUserList();
                    foreach ($pseUsers as $usr) {
                        $userList[] = $usr['id'];
                    }
                    $prodM = new tldGroup('role_PM', $erp);
                    $prodUsers = $prodM->getUserList();
                    foreach ($prodUsers as $usr) {
                        $userList[] = $usr['id'];
                    }
                    $proQE = new tldGroup('role_QE', $erp);
                    $prodUsers = $proQE->getUserList();
                    foreach ($prodUsers as $usr) {
                        $userList[] = $usr['id'];
                    }
                    $proQA = new tldGroup('ROLE_QA', $erp);
                    $prodUsers = $proQA->getUserList();
                    foreach ($prodUsers as $usr) {
                        $userList[] = $usr['id'];
                    }
                    // Add default CC to SEQ
                    $userList = array_unique($userList);
                    foreach ($userList as $usr) {
                        $seq->addCC($usr);
                    }
                }
                return $seqMsg;
            }
        }
        // Update date

        $e = $this->updateRecord(
            ['dgt_act' => $formattedGt],
            ['dgt_act']
        );

        if (is_string($e)) {
            $this->addLogEntry( $userId, sprintf('INTERNAL ERROR:  Actual GT date not updated. Reason: %s', $e));
        } else {
            $this->addLogEntry( $userId,  sprintf('Actual GT date updated through P&I to %s', $formattedGt));
        }

        $this->manageFMSContractInformationAfterGreenTag($userId);

        // Update first commited GT date if GT the first time
        if ($odp['dgt_com'] === '0000-00-00') {
            $e = $this->updateRecord(
                ['dgt_com' => $formattedGt],
                ['dgt_com']
            );
            if (is_string($e)) {
                $this->addLogEntry( $userId, sprintf('INTERNAL ERROR: First GT Date not updated. Reason: %s',$e));
            } else {
                $this->addLogEntry( $userId,  sprintf('First GT Date updated through P&I to %s', $formattedGt));
            }
        }
    }

    public static function getConsumedPartsReport(int $erp, int $workOrder): array {
        $query = <<<SQL
SELECT
    cst001.t_pdno,
    sfc001.t_mitm,
    cst001.t_cprj,
    pcs021.t_dsca prjdsca,
    cst001.t_pono,
    cst001.t_sitm,
    itm001.t_pics,
    itm001.t_dsca,
    cst001.t_opno,
    cst001.t_cwar,
    itm001.t_stoc,
    cst001.t_ques,
    cst001.t_qucs,
    cst001.t_ques - cst001.t_qucs as "Delta",
    cst001.t_cpcs,
    cst001.t_issu,
    cst001.t_subd,
    CASE cst001.t_bfls WHEN '2' THEN 'No' ELSE 'Yes' END as t_bfls,
    (SELECT TOP 1 t_loca FROM ttdilc101$erp ilc101 WHERE ilc101.t_cwar=cst001.t_cwar AND ilc101.t_item=cst001.t_sitm) as t_loca
FROM tticst001$erp cst001,
    ttiitm001$erp itm001,
    ttipcs021$erp pcs021,
    ttisfc001$erp sfc001
WHERE cst001.t_sitm=itm001.t_item AND pcs021.t_cprj=cst001.t_cprj AND cst001.t_pdno=sfc001.t_pdno AND sfc001.t_mitm=pcs021.t_item
AND cst001.t_pdno = {$workOrder} AND itm001.t_pics <> 1
ORDER BY 5,6
SQL;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public function generatePDF($questionsAnswers, $pdno)
    {
        $dtGenPDF = (new DateTime())->format('Y-m-d');

        $html = '<!DOCTYPE HTML>';
        $html .= '<html>';
        $html .= '<head>';
        $html .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
        $html .= '</head>';
        $html .= '<body>';
        $html .= '<style type="text/css">';
        $html .= 'table { page-break-inside:avoid }';
        //$html.='tr    { page-break-inside:avoid; page-break-after:auto }';
        $html .= 'tr    { page-break-inside:avoid; }';
        //$html.='thead { display:table-header-group }';
        //$html.='tfoot { display:table-footer-group }';
        $html .= '</style>';

        $html .= '<table border=0>';
        $html .= '  <tr><td colspan=2 align=center style="background:#2971a8; color:white;"><b>P&I REPORT</b></td></tr>';
        $html .= '  <tr><td style="background:#2971a8; color:white;">ER</td><td style="background:#eeeeee;">' . $this->getSN() . '</td></tr>';
        $html .= '  <tr><td style="background:#2971a8; color:white;">Project</td><td style="background:#d0d0d0;">' . $this->itsDetails['t_prno'] . '</td></tr>';
        $html .= '  <tr><td style="background:#2971a8; color:white;">Production Order</td><td style="background:#eeeeee;">' . $pdno . '</td></tr>';
        $html .= '  <tr><td style="background:#2971a8; color:white;">Document date</td><td style="background:#d0d0d0;">' . $dtGenPDF . '</td></tr>';
        $html .= '</table><br><br>';

        $openTable = false;
        $operation = null;
        $subject = null;
        $lig = 0;
        foreach ($questionsAnswers as $key1 => $value1) {
            // New operation
            if ($questionsAnswers[$key1]['t_opno'] != $operation) {
                //for encoding problems
                //transform some value in html-entities
                $questionsAnswers[$key1]['op_desc'] = mb_convert_encoding($questionsAnswers[$key1]['op_desc'], 'UTF-8', 'HTML-ENTITIES');
                $operation = $questionsAnswers[$key1]['t_opno'];
                $subject = null;
                if ($openTable == true) {
                    $html .= '</table>';
                    $openTable = false;
                }

                $html .= '<br><br><b><u>Operation ' . $questionsAnswers[$key1]['t_opno'] . ': ' . mb_convert_encoding($questionsAnswers[$key1]['op_desc'], 'HTML-ENTITIES', 'UTF-8') . '</u></b><br>';
            }

            // New subject
            if ($questionsAnswers[$key1]['subject'] != $subject) {
                //for encoding problems
                //transform some value in html-entities
                $questionsAnswers[$key1]['subject'] = mb_convert_encoding($questionsAnswers[$key1]['subject'], 'UTF-8', 'HTML-ENTITIES');

                $subject = $questionsAnswers[$key1]['subject'];
                if ($openTable) {
                    $html .= '</table><br>';
                }
                $openTable = true;
                $html .= '<table border=0 width=100%>';
                $html .= '  <thead>';
                $html .= '    <tr width=100% style="background:#2971a8; color:white;">';
                $html .= '      <td width=55%>&nbsp;' . mb_convert_encoding($questionsAnswers[$key1]['subject'], 'HTML-ENTITIES', 'UTF-8') . '</td>';
                $html .= '      <td width=15%>Answer</td>';
                $html .= '      <td width=10%>Unit</td>';
                $html .= '      <td width=20%>User</td>';
                $html .= '    </tr>';
                $html .= '  </thead>';

            }
            if ($questionsAnswers[$key1]['answer_type'] === 'S/N') {
                $answer = <<<HTML
                <table width=100%>
                <tr width=100%><td width=100%><b>Component:&nbsp;{$questionsAnswers[$key1]['answerComponent']}</b></td></tr>
                <tr width=100%><td width=100%>Model:&nbsp;{$questionsAnswers[$key1]['answerModel']}</td></tr>
                <tr width=100%><td width=100%>Serial:&nbsp;{$questionsAnswers[$key1]['answerSerial']}</td></tr>
                <tr width=100%><td width=100%>Brand:&nbsp;{$questionsAnswers[$key1]['answerBrand']}</td></tr>
                </table>
HTML;
                $questionsAnswers[$key1]['answer'] = $answer;
            }

            //some information are not correctly encoding in DB
            //So we must be sur all data are in html-entities.
            $questionsAnswers[$key1]['desc'] = mb_convert_encoding($questionsAnswers[$key1]['desc'] ?? '', 'UTF-8', 'HTML-ENTITIES');
            $questionsAnswers[$key1]['answer'] = mb_convert_encoding($questionsAnswers[$key1]['answer'] ?? '', 'UTF-8', 'HTML-ENTITIES');
            $questionsAnswers[$key1]['answer_unit'] = mb_convert_encoding($questionsAnswers[$key1]['answer_unit'] ?? '', 'UTF-8', 'HTML-ENTITIES');
            $questionsAnswers[$key1]['answer_name'] = mb_convert_encoding($questionsAnswers[$key1]['answer_name'] ?? '', 'UTF-8', 'HTML-ENTITIES');
            // Display line
            ++$lig;
            $color = ($lig % 2) ? '#eeeeee' : '#d0d0d0';

            $html .= '<tbody>';
            $html .= '  <tr style="background:' . $color . ';">';
            $html .= '    <td>&nbsp;' . mb_convert_encoding($questionsAnswers[$key1]['desc'], 'HTML-ENTITIES', 'UTF-8') . '</td>';
            if ($questionsAnswers[$key1]['answer_type'] === 'S/N') {
                $html .= '    <td>&nbsp;' . $questionsAnswers[$key1]['answer'] . '</td>';
            } else {
                $html .= '    <td>&nbsp;' . mb_convert_encoding($questionsAnswers[$key1]['answer'], 'HTML-ENTITIES', 'UTF-8') . '</td>';
            }
            $html .= '    <td>&nbsp;' . mb_convert_encoding($questionsAnswers[$key1]['answer_unit'], 'HTML-ENTITIES', 'UTF-8') . '</td>';
            $html .= '    <td>&nbsp;' . mb_convert_encoding($questionsAnswers[$key1]['answer_name'], 'HTML-ENTITIES', 'UTF-8') . '</td>';
            $html .= '  </tr>';
            $html .= '</tbody>';

        }
        if ($openTable) {
            $html .= '</table>';
        }
        $html .= '</body></html>';

        return new tldHTML2PDF($html, ['encoding' => 'utf-8', 'margin' => '10']);
    }

    /**
     * Get log of comments/history
     *
     * @return array
     */
    public function getLog()
    {
        return tldModLog::byParent($this->itsId, 'ER');
    }

    public function getCRABS($opt = null)
    {
        if (!empty($opt) && is_array($opt)) {
            if ($opt['status'] === 'All') {
                unset($opt['status']);
            }
            if ($opt['opno'] === 'All') {
                unset($opt['opno']);
            }
            $params = ['erid' => $this->itsID] + $opt;

            return tldCRAB::byQuery($params);
        }

        return tldCRAB::byQuery(['erid' => $this->itsID]);
    }

    public function addLogEntry($id, $comment)
    {
        $a['parent_id'] = $this->itsId;
        $a['module'] = 'ER';
        $a['poster'] = $id;
        $a['comment'] = $comment;

        return tldModLog::insert($a);
    }

    /**
     * Get list of files
     *
     * If id option is specified then only return that row with id, if not found empty array
     * is returned
     *
     */
    public function getFiles($id = '')
    {
        $WHERE = $id ? " AND id=$id" : '';

        $query = <<<EOF
SELECT * FROM service_files
WHERE parent_id = $this->itsId
    $WHERE
EOF;
        return $id ? tldUtils::getSqlRowToAssocArray($query) : tldUtils::getSqlToAssocArray($query);
    }

    public function outFile($id)
    {
        if (empty($id)) {
            return;
        }
        $row = $this->getFiles($id);
        if (!is_array($row)) {
            return;
        }

        $file = new basicFile(tldUtils::getPathToUploadFile('mod_files', $row['filename']));
        $file->outFile();
    }

    public function getDocList()
    {
        $query = <<<EOF
SELECT bom.* FROM service,bomcust,bom
WHERE
    service.sn=bomcust.t_cprj
    AND service.id='$this->itsId'
    AND bomcust.erp=bom.erp
    AND bomcust.t_sitm=bom.t_mitm
    AND bomcust.t_sitm like '101-%'
    AND bom.t_exdt='0000-00-00'
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function getSchematics()
    {
        $query = <<<EOF
SELECT * FROM service_serials
WHERE
    (component like '%schem%' OR (component like '%diag%' AND component NOT LIKE '%diagn%') OR component='menu tree')
    AND parent_id = $this->itsId 
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public function getTldLinkComponents()
    {
        $query = <<<SQL
SELECT * FROM service_serials
WHERE component like '%, %LINK' AND parent_id = $this->itsId 
SQL;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function getManuals()
    {
        $query = <<<EOF
SELECT
    manuals.*
FROM
    service_serials, manuals
WHERE
    service_serials.serial = manuals.id
    AND service_serials.component = 'MANUAL'
    AND service_serials.parent_id = $this->itsId
ORDER BY
    manuals.date
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function setSORLID($id)
    {
        if (empty($this->itsId)) {
            return false;
        }
        $query = <<<EOF
UPDATE service SET sor_lid=$id
WHERE id=$this->itsId
LIMIT 1
EOF;

        return tldUtils::sqlQuery($query);
    }

    public function getSOR_UID()
    {
        return $this->itsDetails['sor_uid'];
    }

    public function setSOR_UID($id)
    {
        if (empty($this->itsId)) {
            return 'ERROR: No ID set';
        }
        if (!$id) {
            $id = 'NULL';
        }
        $query = <<<EOF
UPDATE service SET sor_uid=$id
WHERE id=$this->itsId
LIMIT 1
EOF;

        return tldUtils::sqlQuery($query);
    }

    public function setESRID($id)
    {
        if (empty($this->itsId)) {
            return false;
        }
        $query = <<<EOF
UPDATE service SET esrid=$id
WHERE id=$this->itsId
LIMIT 1
EOF;

        return tldUtils::sqlQuery($query);
    }

    /**
     * Set the transaction to the ER
     *
     * @param integer $id transaction id
     * @param string $type type of the transaction
     *
     * @return mixed integer or string error
     */
    public function setTranID($id, $type)
    {
        if (empty($this->itsId)) {
            return 'Not object context';
        }
        if (empty($id) || !is_numeric($id)) {
            return 'Parameter(s) incorrect';
        }
        $query = '';
        switch ($type) {
            case 'SSO':
                $query = "UPDATE service SET tranid_sso=$id WHERE id=$this->itsId LIMIT 1";
                break;
            case 'ERP':
                $query = "UPDATE service SET tranid_erp=$id WHERE id=$this->itsId LIMIT 1";
                break;
        }

        return tldUtils::sqlQuery($query);
    }

    /**
     * Set the Revenue Recognition Date to the ER
     *
     * @param string RRD date $dt
     * @param string $type type of the transaction
     *
     * @return mixed integer or string error
     */
    public function setRRD($dt, $type)
    {
        if (empty($this->itsId)) {
            return 'Not object context';
        }
        if (empty($dt) || empty($type)) {
            return 'Parameter(s) empty';
        }
        switch ($type) {
            case 'SSO':
                $query = "UPDATE service SET rrd_sso='$dt' WHERE id=$this->itsId LIMIT 1";
                break;
            case 'ERP':
                $query = "UPDATE service SET rrd_erp='$dt' WHERE id=$this->itsId LIMIT 1";
                break;
            default:
                return 'Transaction type invalid';
                break;
        }

        return tldUtils::sqlQuery($query);
    }

    //public functions
    public static function getCustomerList()
    {
        $query = <<<EOF
SELECT distinct(customer_name),UPPER(LEFT(customer_name, 1)) AS firstChar
FROM service
WHERE customer_name!=''
ORDER BY customer_name
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function getTypeList()
    {
        $query = <<<EOF
SELECT type, count(*)
FROM service
GROUP BY type
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * get units not shipped yet
     */
    public static function getODP()
    {
        $query = <<<EOF
SELECT t1.*, t2.*,
	IF(t1.customer_id > 0,
	(SELECT customers.customer_name FROM customers WHERE customers.id=t1.customer_id),
	t1.customer_name) AS user_customer_display,
	(SELECT customers.customer_name FROM customers WHERE customers.id=t1.buyer_customer_id) AS buyer_customer_display
FROM service AS t1 LEFT JOIN sor_units AS t2 ON t1.sor_uid=t2.id
WHERE date_shipped='0000-00-00'
ORDER BY man_location, sales_org, customer_name, model
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get related tasks to ER
     *
     * @param string $status
     *
     * @return array
     */
    public function getTasks($status = 'ALL')
    {
        if (!in_array($status, ['ALL', 'OPEN'])) {
            return [];
        }

        return tldTask::byParent($this->itsID, 'ER', $status);
    }

    public function getSeqs($status = 'ALL')
    {
        if (!in_array($status, ['ALL', 'OPEN'])) {
            return [];
        }

        return tldTask::byParent($this->itsID, 'SEQ', $status, 'tplno IN (53,54,55)');
    }

    public function countODPByERPSalesorg()
    {
        $query = <<<EOF
SELECT man_location, sales_org, COUNT(*) AS num
FROM service
WHERE date_shipped='0000-00-00'
GROUP BY man_location, sales_org
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function getStatusTransactions()
    {
        if (empty($this->itsId)) {
            return 'Not object context';
        }
        $query = <<<EOF
    	SELECT s.*, CONCAT(p.firstname,' ',UPPER(p.lastname)) AS who_fullname
    	FROM service_strans s
    	LEFT JOIN people p ON p.id=s.who
    	WHERE s.parent_id={$this->itsId}
    	ORDER BY s.id DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getInventory($factory = '', $status = '', $type = '', $light = '', $yellowTag = '', $linkSol = '')
    {
        if (!empty($factory)) {
            $factory = " AND man_location = '$factory' ";
        }
        if (!empty($status)) {
            $status = "HAVING status = '$status'";
        }
        if (!empty($type)) {
            $type = "AND service.type = '$type'";
        }
        if (isset($light)) {
            $light = "AND service.light = '$light'";
        }
        if (isset($yellowTag)) {
            $yellowTag = "AND service.dyt != '0000-00-00'";
        }
        if (isset($linkSol)) {
            $linkSol = "AND service.sor_uid IS NOT NULL";
        }

        $query = <<<EOF
SELECT
man_location,
light,
sn,
model,
location_short,
mfg_comments,
service.id,
dgt_rev,
dgt_com,
CASE
    WHEN customers.customer_name = '**AVAILABLE FOR SALE**' THEN 'Available for Sale'
    WHEN customers.customer_name = '**DEMO**' THEN 'Demo'
    WHEN customers.customer_name = '**PROTO**' THEN 'Proto'
    WHEN customers.customer_name = '**STOCK**' THEN 'Stock'
END AS status,
(SELECT customer_name FROM customers WHERE customers.id = service.customer_id) AS customer,
CASE
	WHEN (SELECT COUNT(*) FROM csr WHERE csr.parent_id=service.id AND csr.work_type='Commissioning' AND csr.status IN ('COMPLETED', 'CLOSED')) > 0 THEN 'COMMISIONED'
	WHEN service.date_shipped != '0000-00-00' THEN 'SHIPPED'
	WHEN service.date_shipped='0000-00-00' THEN 'IN PRODUCTION'
	ELSE ''
END AS er_status
FROM service
LEFT JOIN customers ON customers.id = service.buyer_customer_id
WHERE customers.customer_name IN('**PROTO**','**DEMO**','**STOCK**','**AVAILABLE FOR SALE**')  $factory $type $yellowTag $linkSol
$status $light
ORDER BY service.id DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function inventoryCountByFactoryStatus($light)
    {
        $query = <<<EOF
SELECT
    CASE
    WHEN customers.customer_name = '**AVAILABLE FOR SALE**' THEN 'Available for Sale'
    WHEN customers.customer_name = '**DEMO**' THEN 'Demo'
    WHEN customers.customer_name = '**PROTO**' THEN 'Proto'
    WHEN customers.customer_name = '**STOCK**' THEN 'Stock'
END AS inv_status,
    man_location,
    COUNT(*) AS num
FROM service
LEFT JOIN customers ON customers.id = service.buyer_customer_id
WHERE customers.customer_name IN('**PROTO**','**DEMO**','**STOCK**','**AVAILABLE FOR SALE**') AND service.light = $light AND man_location NOT in ('MONTPELLIER','TLD TWN')
    GROUP BY inv_status, man_location
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function inventoryCountByFactoryStatusWithSOL($light)
    {
        $query = <<<EOF
SELECT
    CASE
    WHEN customers.customer_name = '**AVAILABLE FOR SALE**' THEN 'Available for Sale'
    WHEN customers.customer_name = '**DEMO**' THEN 'Demo'
    WHEN customers.customer_name = '**PROTO**' THEN 'Proto'
    WHEN customers.customer_name = '**STOCK**' THEN 'Stock'
END AS inv_status,
    man_location,
    COUNT(*) AS num
FROM service
LEFT JOIN customers ON customers.id = service.buyer_customer_id
WHERE customers.customer_name IN('**PROTO**','**DEMO**','**STOCK**','**AVAILABLE FOR SALE**')
  AND service.light = $light
  AND man_location NOT in ('MONTPELLIER','TLD TWN')
  AND service.sor_uid IS NOT NULL
GROUP BY inv_status, man_location
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function inventoryCountByFactoryStatusYellowTag($light)
    {
        $query = <<<EOF
SELECT
    CASE
    WHEN customers.customer_name = '**AVAILABLE FOR SALE**' THEN 'Available for Sale'
    WHEN customers.customer_name = '**DEMO**' THEN 'Demo'
    WHEN customers.customer_name = '**PROTO**' THEN 'Proto'
    WHEN customers.customer_name = '**STOCK**' THEN 'Stock'
END AS inv_status,
    man_location,
    COUNT(*) AS num
FROM service
LEFT JOIN customers ON customers.id = service.buyer_customer_id
WHERE 
    customers.customer_name IN('**PROTO**','**DEMO**','**STOCK**','**AVAILABLE FOR SALE**')
    AND service.light = $light
    AND man_location NOT in ('MONTPELLIER','TLD TWN')
    AND service.dyt != '0000-00-00'
GROUP BY inv_status, man_location
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getUnitOperationStatusList()
    {
        return [
            'MCF' => 'Mission Capable Fully (MCF)',
            'MCP' => 'Mission Capable Partially (MCP)',
            'NMC' => 'Non Mission Capable (NMC)',
        ];
    }

    public static function getFmsContractType(): array
    {
        return [
            '' => '',
            'AES' => 'AES',
            'XOPS' => 'XOPS',
            'TAS' => 'TAS',
            'SAS' => 'SAS',
        ];
    }

    public static function getWarrantyCategories(): array
    {
        return [
            '' => '',
            'Design/Engineering' => 'Design/Engineering',
            'Manufacturing/Production' => 'Manufacturing/Production',
            'Supplier quality' => 'Supplier quality',
            'Operation issue' => 'Operation issue',
            'Shipping issue' => 'Shipping issue',
            'Maintenance issue' => 'Maintenance issue',
        ];
    }

    public function getOperationStatus()
    {
        return $this->itsDetails['operation_status'];
    }

    public function setOperationStatus($who, $status, $log, $limitations_log, $repairs_log, $fmc_log)
    {
        // Set status
        $e = $this->updateRecord(
            ['operation_status' => $status],
            ['operation_status']
        );
        if (is_string($e)) {
            return $e;
        }
        // Add transaction
        $query = <<<EOF
    	INSERT INTO service_strans
    	SET who='{$who}', status='{$status}', log='{$log}', limitations_log='{$limitations_log}', repairs_log='{$repairs_log}',
    	fmc_log='{$fmc_log}', dt=NOW(), parent_id={$this->itsId}
EOF;

        return tldUtils::sqlInsert($query);
    }

    public static function getProjectedMarginsNoGT($factory)
    {
        if (empty($factory)) {
            return 'Factory ID is missing';
        }
        $factory = " AND sor_lines.bu = $factory";
        $query = <<<EOF
		SELECT
		service.sn,
		service.model,
		(SELECT DATE_FORMAT(mod_logs.date,'%Y-%m-%d') FROM mod_logs WHERE mod_logs.module = 'SOL' and mod_logs.parent_id = sor_lines.id AND comment like 'IN_PROGRESS%' LIMIT 1) AS dt_psm_approval,
		sor_lines.id AS sol_id,
		(SELECT location FROM locations WHERE locations.id = sor.sso) AS sso_fullname,
    	(SELECT customers.customer_name FROM customers
        	WHERE customers.id=service.buyer_customer_id
    	) AS buyer_customer_display,
		sor_lines.factory_margin AS projected_margin
		FROM service
		LEFT JOIN sor_units ON service.sor_uid = sor_units.id
		LEFT JOIN sor_lines ON sor_units.parent_id = sor_lines.id
		LEFT JOIN sor ON sor_lines.parent_id = sor.id
		WHERE service.sor_uid IS NOT NULL AND (service.dgt_rev = '0000-00-00' OR (DATE_FORMAT(NOW(),'%Y%m') <= (DATE_FORMAT(dgt_rev,'%Y%m')))) $factory
		ORDER BY sn

EOF;
        $rows = tldUtils::getSqlToAssocArray($query);
        foreach ($rows as &$row) {
            $sol = new tldSOL($row['sol_id']);
            $summary = $sol->getSummary();
            $row['discc_pc'] = $summary['discc_pc'];
            $row['discf_pc'] = $summary['discf_pc'];
            $row['pris_tp_in_dcur'] = $summary['pris_tp_in_dcur'];
            $row['dcur'] = $sol->getDCUR();
        }

        return $rows;
    }

    public function countByCustTypeLocation($cu_nama = '')
    {
        $WHERE = $cu_nama ? " WHERE t1.customer_name='$cu_nama'" : '';

        $query = <<<EOF
SELECT t1.location_short, t1.type, count(*) AS num
FROM service as t1
$WHERE
GROUP BY t1.location_short, t1.type
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function countByOperationStatusCustomerTypeByConstraints($a = null)
    {
        $WHERE = is_array($a) ? tldUtils::constructWhere($a) : $a;
        if (!empty($WHERE)) {
            $WHERE = "WHERE $WHERE";
        }
        $query = <<<EOF
SELECT
    operation_status,
    cust_equipment_type,
    COUNT(*) AS num
FROM
    service AS er
$WHERE
GROUP BY
    operation_status,
    cust_equipment_type
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byOperationStatusCustomerTypeByConstraints($status, $erType, $a = null)
    {
        $WHERE = [];
        if ($erType !== 'ALL') {
            $WHERE[] = "cust_equipment_type LIKE '$erType'";
        }
        if ($status !== 'ALL') {
            $WHERE[] = "operation_status LIKE '$status'";
        }
        // construct constraints if applicable
        if (is_array($a)) {
            $WHERE[] = tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE[] = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = implode(' AND ', $WHERE);
        }

        return self::byConstraints($WHERE);
    }

    public static function getOperationStatusTransactionsByConstraints($a, $opt = [])
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "WHERE $WHERE";
        }
        // Look for options
        $ORDERBY = 'sn,dt';
        if (!empty($opt['orderBy'])) {
            $ORDERBY = $opt['orderBy'];
        }
        // Construct query
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT,
    opStatus.who,
    (SELECT CONCAT(firstname,' ',lastname)
        FROM people WHERE id=opStatus.who
    ) AS poster_fullname,
    opStatus.dt,
    opStatus.status,
    opStatus.log,
    opStatus.limitations_log,
    opStatus.repairs_log,
    opStatus.fmc_log
$FROM
    LEFT JOIN service_strans AS opStatus ON opStatus.parent_id=er.id
$WHERE
ORDER BY
    $ORDERBY
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getCapabilityStatsByPeriodByConstraints($from, $to, $a, $opt = '')
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "WHERE $WHERE";
        }
        $query = <<<EOF
SELECT
    er.*,
    '$from' AS start_period,
    '$to' AS end_period,
    TIMESTAMPDIFF(
        HOUR,
        '$from',
        '$to'
    ) AS total_period,
    'NO_CALCULATION_YET' AS total_fmc,
    'NO_CALCULATION_YET' AS total_pmc,
    'NO_CALCULATION_YET' AS total_nmc
FROM
    service AS er
$WHERE
GROUP BY
    er.id
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getConsumedPartsReportItems(): array{
        return [
            't_pdno' => 'Production order',
            't_mitm' => 'Item',
            't_cprj' => 'Project',
            'prjdsca' => 'Project Desc.',
            't_pono' => 'Position',
            't_sitm' => 'Item',
            't_pics' => 'Floor stock',
            't_dsca' => 'Description',
            't_opno' => 'Operation',
            't_cwar' => 'Warehouse',
            't_bfls' => 'Backflush',
            't_stoc' => 'Inventory on Hand',
            't_ques' => 'Estimated qty',
            't_qucs' => 'Actual qty',
            'Delta' => 'Delta',
            't_cpcs' => 'Actual cost price',
            't_issu' => 'Issue',
            't_subd' => 'Subsequent Delivery',
            't_qty' => 'Comment',
            't_loca' => 'WHSE location',
        ];
    }

    //returns array of equipment rows for customer_name
    public static function byCustomer($id, $orderBy = 'sn')
    {
        $id = TldDatabase::escape($id);
        if (ctype_digit($id)) {
            $condition = "customer_id=$id";
        } else {
            $condition = "customer_name='$id'";
        }
        $orderBy = TldDatabase::escape($orderBy);

        $query = <<<EOF
SELECT *,
	IF(customer_id > 0,
	(SELECT customers.customer_name FROM customers WHERE customers.id=service.customer_id),
	customer_name) AS user_customer_display,
	(SELECT customers.customer_name FROM customers WHERE customers.id=service.buyer_customer_id) AS buyer_customer_display
FROM service
WHERE $condition
ORDER BY $orderBy
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get list of ER by BUYER customer
     *
     * @param int $id customer id
     * @param array|string $opt option see byConstraints()
     *
     * @return array
     */
    public function byBuyerCustomerID($id, $opt = [])
    {
        $a = ['buyer_customer_id' => $id];

        return self::byConstraints($a, $opt);
    }

    /**
     * Get list of ER by USER & BUYER customer
     *
     * @param int $id customer id
     * @param array|string $opt option see byConstraints()
     *
     * @return array
     */
    public static function byAllCustomerID($id, $opt = [])
    {
        $a = "buyer_customer_id=$id OR customer_id=$id";

        return self::byConstraints($a, $opt);
    }

    public static function byLatest($num = 10, $opt = [])
    {
        switch ($opt['mode']) {
            case 'shipped':
                $WHERE = " WHERE date_shipped != '0000-00-00' ORDER BY date_shipped DESC";
                break;
            default:
                $WHERE = ' ORDER BY id DESC';
        }
        $query = <<<EOF
SELECT *,
	IF(customer_id > 0,
	(SELECT customers.customer_name FROM customers WHERE customers.id=service.customer_id),
	customer_name) AS user_customer_display,
	(SELECT customers.customer_name FROM customers WHERE customers.id=service.buyer_customer_id) AS buyer_customer_display,
	(SELECT customers.customer_name FROM customers WHERE customers.id=service.maintainer_customer_id) AS maintainer_customer_display
FROM service
$WHERE
LIMIT $num
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get list of equipment linked to an SOR Line ID
     *
     * @param integer $id
     *
     * @return array
     */
    public static function bySORLine($id)
    {
        if (empty($id)) {
            return;
        }

        $query = <<<EOF
SELECT t2.*,
	IF(t2.customer_id > 0,
	(SELECT customers.customer_name FROM customers WHERE customers.id=t2.customer_id),
	t2.customer_name) AS user_customer_display,
	(SELECT customers.customer_name FROM customers WHERE customers.id=t2.buyer_customer_id) AS buyer_customer_display
FROM sor_units AS t1, service AS t2
WHERE t1.parent_id=$id AND t1.id=t2.sor_uid
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function getInco($id)
    {
        if (empty($id)) {
            return;
        }

        $query = <<<EOF
SELECT sor_lines.inco
FROM service
LEFT JOIN sor_units ON service.sor_uid = sor_units.id
LEFT JOIN sor_lines ON sor_units.parent_id = sor_lines.id
WHERE service.id=$id
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);

        return $rows[0];
    }

    public static function byESR($id)
    {
        if (empty($id)) {
            return;
        }

        return self::byConstraints(['esrid' => $id]);
    }

    public static function bySORTran($tgrp, $id)
    {
        if (empty($id)) {
            return;
        }
        if ($tgrp === 'ERP') {
            return self::byConstraints(['tranid_erp' => $id]);
        }

        if ($tgrp === 'SSO') {
            return self::byConstraints(['tranid_sso' => $id]);
        }
    }

    /**
     * Get list of equipment linked to an SOR UNIT id
     *
     * @param integer $id
     *
     * @return array
     */
    public static function bySORUnit($id)
    {
        if (empty($id)) {
            return;
        }

        return self::byConstraints(['sor_uid' => $id]);
    }

    /**
     * Search an equipment with his serial number
     *
     * @param string $sn
     *
     * @return array
     */
    public static function bySN($sn)
    {
        if (empty($sn)) {
            return [];
        }
        $sn = TldDatabase::escape($sn);

        return self::byConstraints(['sn' => $sn]);
    }

    /**
     * Search an equipment by serial number
     *
     * @param array $sns
     *
     * @return array
     */
    public static function bySNs(array $sns)
    {
        if (empty($sns)) {
            return [];
        }

        return self::byConstraints("sn IN ('" . implode("','", $sns) . "')");
    }

    /**
     * returns array of equipment rows by field
     *
     * @param array or string $a constraints
     * @param array $opt options
     *                    [orderBy] => string query clause ORDER BY
     *                    [limit] => int query clause LIMIT
     *
     * @return array
     */
    public static function byConstraints($a, $opt = [])
    {
        $HAVING = is_array($a) ? tldUtils::constructWhere($a) : $a;

        if (!empty($HAVING)) {
            $HAVING = "HAVING $HAVING";
        }
        // Look for options
        $ORDERBY = !empty($opt['orderBy']) ? $opt['orderBy'] : 'sn';
        $LIMIT = !empty($opt['limit']) ? 'LIMIT ' . $opt['limit'] : '';
        $GROUPBY = !empty($opt['groupBy']) ? 'GROUP BY ' . $opt['groupBy'] : '';

        // Construct query
        $query = <<<EOF
SELECT
    service.*, t3.ddel_est1, t3.del_dat,sol.id AS sol_id, sol.parent_id AS sor_id,
    DATE_ADD( date_shipped, INTERVAL service.warranty_length MONTH ) AS calculated_date_warranty_end,
    IF(customer_id>0,
	   (SELECT customers.customer_name FROM customers WHERE customers.id=service.customer_id),
	   customer_name
    ) AS user_customer_display,
    (SELECT customers.customer_name FROM customers
        WHERE customers.id=service.buyer_customer_id
    ) AS buyer_customer_display,
    (SELECT customers.customer_name FROM customers
        WHERE customers.id=service.maintainer_customer_id
    ) AS maintainer_customer_display,
    (SELECT customers.type FROM customers
        WHERE customers.id=service.customer_id
    ) AS user_customer_type,
	(SELECT CONCAT(apc.airport_code,', ',countries.name,', ',apc.city_name,', ',apc.airport_name)
        FROM airport_codes AS apc LEFT JOIN countries ON countries.iso_code_2=apc.ctry_code_2
        WHERE apc.airport_code=service.airport_code LIMIT 1
    ) AS apc_fullname,
    CASE
        WHEN (SELECT COUNT(*) FROM csr WHERE csr.parent_id=service.id AND csr.work_type='Commissioning' AND csr.status IN ('COMPLETED', 'CLOSED')) > 0 THEN 'COMMISIONED'
        WHEN ddel_act2!='0000-00-00' THEN 'DELIVERED'
        WHEN date_shipped != '0000-00-00' THEN 'SHIPPED'
        WHEN date_shipped='0000-00-00' THEN 'IN PRODUCTION'
        ELSE ''
    END AS status,
    CASE
        WHEN service.light IS TRUE THEN 'YES'
        ELSE 'NO'
    END AS light_value
FROM service
LEFT JOIN sor_units AS t3 ON t3.id = service.sor_uid
LEFT JOIN sor_lines AS sol ON t3.parent_id=sol.id
$GROUPBY
$HAVING
ORDER BY $ORDERBY
$LIMIT
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * @return array
     */
    public static function byUnassigned()
    {
        $query = <<<EOF
SELECT *,
	IF(customer_id > 0,
	(SELECT customers.customer_name FROM customers WHERE customers.id=service.customer_id),
	customer_name) AS user_customer_display,
	(SELECT customers.customer_name FROM customers WHERE customers.id=service.buyer_customer_id) AS buyer_customer_display
FROM service
WHERE sor_uid IS NULL AND date_shipped='0000-00-00'
ORDER BY man_location, type, model
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * get units not shipped yet ie ship date is null
     *
     * @return array array of db rows
     */
    public static function byUnshipped($id = null)
    {
        $WHERE = null === $id ? '' : " AND id='$id'";
        $query = <<<EOF
SELECT *,
	IF(customer_id > 0,
	(SELECT customers.customer_name FROM customers WHERE customers.id=service.customer_id),
	customer_name) AS user_customer_display,
	(SELECT customers.customer_name FROM customers WHERE customers.id=service.buyer_customer_id) AS buyer_customer_display
FROM service
WHERE date_shipped='0000-00-00'
$WHERE
ORDER BY man_location, type, model
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byUnshippedBySN($sn)
    {
        $query = <<<EOF
SELECT concat_ws('->',sn,type,model) as equipment
FROM service
WHERE date_shipped='0000-00-00' AND sn='$sn' AND sn!=''
ORDER BY type, model
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /***
     * Get the number of unshipped ER by SOO
     *
     * @return array
     */
    public static function numberUnshippedBySSO()
    {
        $query = <<<SQL
SELECT service.sales_org AS location_sso, 'GT/YT Not Shipped' AS status, count(*) AS num
FROM service
WHERE (service.dyt != '0000-00-00' OR service.dgt_com != '0000-00-00') AND service.date_shipped = '0000-00-00' AND service.sales_org != ''
AND service.customer_name NOT LIKE "%AVAILABLE FOR SALE%" AND service.buyer_customer_id != 1087 AND service.customer_id != 1087
GROUP BY location_sso;
SQL;
        return tldUtils::getSqlToAssocArray($query);
    }

    /***
     * Get the unshipped ER by SOO
     *
     * @param null $sso
     * @return array
     */
    public static function unshippedBySSO($sso = null)
    {
        $WHERE = '';
        if ($sso !== null) {
            $WHERE = "AND service.sales_org = '$sso'";
        }
        $query = <<<SQL
SELECT *,
	IF(customer_id > 0,
	(SELECT customers.customer_name FROM customers WHERE customers.id=service.customer_id),
	customer_name) AS user_customer_display,
	(SELECT customers.customer_name FROM customers WHERE customers.id=service.buyer_customer_id) AS buyer_customer_display
FROM service
WHERE (service.dyt != '0000-00-00' OR service.dgt_com != '0000-00-00') AND service.date_shipped = '0000-00-00' AND service.sales_org != '' 
AND service.customer_name NOT LIKE "%AVAILABLE FOR SALE%" AND service.buyer_customer_id != 1087 AND service.customer_id != 1087
$WHERE 
ORDER BY id ASC;
SQL;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * get list of equipment available for assignment to an SOR
     *
     * @param int SOR line id you want to assign ER to
     *
     * @return array array of db rows
     */
    public static function bySimilarModelUnallocated($id)
    {
        if (empty($id)) {
            return;
        }
        $query = <<<EOF
select t2.*,
	IF(t2.customer_id > 0,
	(SELECT customers.customer_name FROM customers WHERE customers.id=t2.customer_id),
	t2.customer_name) AS user_customer_display,
	(SELECT customers.customer_name FROM customers WHERE customers.id=t2.buyer_customer_id) AS buyer_customer_display
from service as t2, sor_lines as t1
where t2.model like concat(substr(t1.model, 1, IF(LENGTH(t2.model)>3, 5, 3)),'%')
    AND t2.sor_uid IS NULL
    AND t2.date_shipped='0000-00-00' and t1.id=$id
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function bySimilarModelUnshipped($id)
    {
        if (empty($id)) {
            return;
        }
        $query = <<<EOF
select t2.*,
	IF(t2.customer_id > 0,
	(SELECT customers.customer_name FROM customers WHERE customers.id=t2.customer_id),
	t2.customer_name) AS user_customer_display,
	(SELECT customers.customer_name FROM customers WHERE customers.id=t2.buyer_customer_id) AS buyer_customer_display
from service as t2, sor_lines as t1
where t2.model like concat(substr(t1.model, 1, IF(LENGTH(t2.model)>3, 5, 3)),'%')
AND t2.sor_uid IS NOT NULL AND t2.date_shipped='0000-00-00' and t1.id=$id
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function bySimilarModelDemo($id)
    {
        if (empty($id)) {
            return;
        }
        $query = <<<EOF
SELECT t2.*,
	IF(t2.customer_id > 0,
	(SELECT customers.customer_name FROM customers WHERE customers.id=t2.customer_id),
	t2.customer_name) AS user_customer_display,
	(SELECT customers.customer_name FROM customers WHERE customers.id=t2.buyer_customer_id) AS buyer_customer_display
FROM service as t2, sor_lines as t1
WHERE t2.model like concat(substr(t1.model, 1, IF(LENGTH(t2.model)>3, 5, 3)),'%')
AND t2.customer_name='**DEMO**' and t1.id=$id
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byManualPN($pn)
    {
        if (empty($pn)) {
            return;
        }
        $query = <<<EOF
SELECT
	er.*,
	IF(er.customer_id > 0,
	(SELECT customers.customer_name FROM customers WHERE customers.id=er.customer_id),
	er.customer_name) AS user_customer_display,
	(SELECT customers.customer_name FROM customers WHERE customers.id=er.buyer_customer_id) AS buyer_customer_display,
	manuals.id AS manualid
FROM service AS er
    LEFT JOIN service_serials AS serial ON serial.parent_id=er.id
    LEFT JOIN manuals ON manuals.id=serial.serial AND serial.component LIKE 'MANUAL'
    LEFT JOIN manuals_docs AS doc ON doc.parent_id=manuals.id
    LEFT JOIN manuals_diag AS diag ON diag.id=doc.doc_num
WHERE
	diag.factory_num LIKE '$pn'
GROUP BY er.id
ORDER BY er.id
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function search($a)
    {
        if (empty($a)) {
            return;
        }
        $fields = [
            'type',
            'model',
            'sn',
            'cust_asset_num',
            'entered_by',
            'man_location',
            'customer_name',
            'customer_contact',
            'customer_ref',
            'agent_name',
            'location_short',
            'airport_code',
            'delivery_location',
            'sales_org',
            'sales_rep',
            't_prno',
            't_pdno',
            'options_desc',
        ];
        $displayedFields = [
            'id',
            'sn',
            'customer_name',
            'model',
            'man_location',
            'sales_org',
            'location_short',
            'airport_code',
            'date_shipped',
            'hours',
            'dgt_rev',
            'dgt_act',
            'entered_by',
            'eng_tier',
            'cust_asset_num',
            'dt_commissioned',
        ];
        $constraints = [];
        foreach ($fields as $field) {
            $constraints[$field] = $a;
        }
        $where = tldUtils::constructWhere($constraints, 'OR');
        $fieldsToSelect = implode(',', $displayedFields);
        $query = <<<EOF
SELECT $fieldsToSelect,
	IF(customer_id > 0,
	(SELECT customers.customer_name FROM customers WHERE customers.id=service.customer_id),
	customer_name) AS user_customer_display,
	(SELECT customers.customer_name FROM customers WHERE customers.id=service.buyer_customer_id) AS buyer_customer_display,
    CASE
        WHEN (SELECT COUNT(*) FROM csr WHERE csr.parent_id=service.id AND csr.work_type='Commissioning' AND csr.status IN ('COMPLETED', 'CLOSED')) > 0 THEN 'COMMISIONED'
        WHEN service.ddel_act2!='0000-00-00' THEN 'DELIVERED'
        WHEN service.date_shipped != '0000-00-00' THEN 'SHIPPED'
        WHEN service.date_shipped='0000-00-00' THEN 'IN PRODUCTION'
        ELSE ''
    END AS status
FROM service
WHERE $where
ORDER BY date_shipped
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get list of equipment by SN or Project number
     * @return array array of db rows
     */
    public static function shopSearch($a)
    {
        if (empty($a)) {
            return [];
        }
        $a = TldDatabase::escape($a);
        $query = <<<EOF
SELECT
    distinct er.id,
    er.*,
	IF(er.customer_id > 0,
	(SELECT customers.customer_name FROM customers WHERE customers.id=er.customer_id),
	er.customer_name) AS user_customer_display,
	(SELECT customers.customer_name FROM customers WHERE customers.id=er.buyer_customer_id) AS buyer_customer_display
FROM service AS er
    LEFT JOIN service_serials AS serials ON serials.parent_id=er.id
    LEFT JOIN manuals ON manuals.id=serials.serial
WHERE
    er.id='$a'
    OR er.sn LIKE '$a'
    OR er.t_prno='$a'
ORDER BY er.sn
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byComponentConstraints($a)
    {
        if (is_array($a)) {
            $HAVING = tldUtils::constructWhere($a);
        } else {
            $HAVING = $a;
        }
        $displayedFields = [
            'T1.id',
            'T1.sn',
            'T1.customer_name',
            'T1.model',
            'T1.man_location',
            'T1.sales_org',
            'T1.location_short',
            'T1.airport_code',
            'T1.date_shipped',
            'T1.hours',
            'T1.dgt_rev',
            'T1.dgt_act',
            'T1.entered_by',
            'T1.status',
            'T1.type',
        ];

        $fieldsToSelect = implode(',', $displayedFields);
        $query = <<<EOF
SELECT $fieldsToSelect,
	IF(T1.customer_id > 0,
    	(SELECT customers.customer_name FROM customers WHERE customers.id=T1.customer_id),
    	T1.customer_name
    ) AS user_customer_display,
	(SELECT customers.customer_name FROM customers
		WHERE customers.id=T1.buyer_customer_id
	) AS buyer_customer_display,
	T2.component AS serial_component,
	T2.model AS serial_model,
	T2.brand AS serial_brand,
	T2.serial AS serial_serial,
    CASE
        WHEN COALESCE(fms_end_use_date , '0000-00-00') != '0000-00-00' THEN fms_end_use_date
        WHEN fms_contract_length > 0 AND dgt_act != '0000-00-00' THEN DATE_ADD(dgt_act, INTERVAL (fms_contract_length + 3) MONTH)
        WHEN fms_contract_length > 0 THEN 'Undefined now as unit is not GT'
        ELSE 'Not relevant, contract length is 0'
    END AS calculated_fms_end_use_date
FROM
	service AS T1
	LEFT JOIN service_serials AS T2 ON T1.id=T2.parent_id
HAVING $HAVING
ORDER BY T1.sn
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get all customer information from a equipment ID,
     * used to put these information on a new CSR or SPR
     * (can be used as static function)
     *
     * @param integer $erid
     *
     * @return array
     */
    public function getCustomerInfoFromER($erid)
    {
        if (empty($erid)) {
            return;
        }
        $query = <<<EOF
SELECT
	IF(er.customer_id > 0,
	(SELECT customers.customer_name FROM customers WHERE customers.id=er.customer_id),
	er.customer_name) AS user_customer_display,
	(SELECT customers.customer_name FROM customers WHERE customers.id=er.buyer_customer_id) AS buyer_customer_display,
	er.customer_id,
	er.buyer_customer_id,
    er.customer_name AS cust_nama,
    er.customer_contact AS cust_cona,
    er.airport_code AS apc,
    cu.customer_tel AS cust_tela,
    cu.customer_fax AS cust_telb
FROM
    service AS er
    LEFT JOIN customers AS cu ON er.customer_id=cu.id
WHERE
    er.id=$erid
EOF;
        $data = tldUtils::getSqlToAssocArray($query);

        return $data[0];
    }

    public static function byPSPConstraints($constraints, $orderBy = 'del_dat')
    {
        if (empty($constraints)) {
            return;
        }
        if ($constraints === 'listAll') {
            $WHERE = '';
        } else {
            $WHERE = 'AND ' . tldUtils::constructWhere($constraints);
        }
        $ORDERBY = TldDatabase::escape($orderBy);

        $query = <<<EOF
SELECT er.*,
	IF(er.customer_id > 0,
	(SELECT customers.customer_name FROM customers WHERE customers.id=er.customer_id),
	er.customer_name) AS user_customer_display,
	(SELECT customers.customer_name FROM customers WHERE customers.id=er.buyer_customer_id) AS buyer_customer_display,
    sol.inco,
    sol.inco_loc,
    unit.del_dat
FROM
    service AS er JOIN sor_units as unit ON er.sor_uid=unit.id
    JOIN sor_lines AS sol ON unit.parent_id=sol.id
    JOIN sor ON sol.parent_id=sor.id
WHERE
    sol.inco NOT LIKE 'EXW'
    AND er.sor_uid IS NOT NULL
    AND er.date_shipped IS NULL
    $WHERE
ORDER BY
    $ORDERBY
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * @param DateTime $start
     * @param DateTime $end
     *
     * @return array
     */
    public static function getCountOnTimeAndAllGTByFactory(\DateTime $start, \DateTime $end)
    {
        $start->modify('first day of ' . $start->format('Y-m'));
        $end->modify('last day of ' . $end->format('Y-m'));

        $query = <<<SQL
SELECT
  t1.man_location as factory,
  COUNT(DATEDIFF(t1.dgt_com, t2.ddel_est1) <= 4 OR NULL) as ontime_gt,
  COUNT(*) as all_gt
FROM service as t1, sor_units as t2
WHERE t1.sor_uid = t2.id
  AND t1.dgt_com >= "{$start->format('Y-m-d')}" AND t1.dgt_com < "{$end->format('Y-m-d')}"
GROUP BY t1.man_location
SQL;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * @param DateTime $start
     * @param DateTime $end
     *
     * @return array
     */
    public static function getCycleTimePerEquipmentRecord(\DateTime $start, \DateTime $end)
    {
        $start->modify('first day of ' . $start->format('Y-m'));
        $end->modify('last day of ' . $end->format('Y-m'));

        $query = <<<SQL
SELECT
  query_970.man_location AS factory,
  query_970.t_prno,
  query_970.model,
  query_970.sn,
  query_970.dgt_act AS 'GT date',
  MIN(answers.created_on) AS op_500,
  query_970.op_970,
  DATEDIFF(query_970.op_970, MIN(answers.created_on)) AS 'days'
FROM (
       SELECT service.t_prno,
              service.sn,
              service.dgt_act,
              service.model,
              MAX(answers.created_on) AS op_970,
              service.man_location
       FROM service
              LEFT JOIN pi_questions_unit questions ON questions.unit = service.sn
              LEFT JOIN pi_answers answers ON answers.parent_id = questions.id
       WHERE questions.t_opno = 970 AND questions.active='Y' AND answers.active = 'Y'
       GROUP BY service.sn
       HAVING MAX(answers.created_on) BETWEEN "{$start->format('Y-m-d')} 00:00:00" AND "{$end->format('Y-m-d')} 23:59:59"
          AND COUNT(DISTINCT answers.id) = COUNT(DISTINCT questions.id)
     ) AS query_970
       LEFT JOIN pi_questions_unit questions ON questions.unit = query_970.sn
       LEFT JOIN pi_answers answers ON answers.parent_id = questions.id
WHERE questions.t_opno > 500
  AND questions.t_opno < 969
GROUP BY query_970.sn
ORDER BY factory ASC, op_970 DESC;
SQL;
        return tldUtils::getSqlToAssocArray($query);
    }

    public function isBlackCat(){
        $query = <<<SQL
SELECT COUNT(*) FROM service s LEFT JOIN toc t on s.id=t.erid WHERE t.activity_type='Troubleshooting' AND s.id={$this->itsID} AND s.dt_commissioned > DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
SQL;
        $numberOfTocLastYear = tldUtils::getSqlRowToAssocArray($query);
        $numberOfTocLastYear = (int) current($numberOfTocLastYear);
        $commissioningDate = new \DateTime($this->getCommissioningDate());
        return $numberOfTocLastYear >=10 || (($commissioningDate > new \DateTime('-1 year')) && $numberOfTocLastYear > 6 && (365 * $numberOfTocLastYear / (int) $commissioningDate->diff(new \DateTime())->format('%r%a')) >= 10);
    }

    public function getLinkHtmlInfo() {
        $isLink = ((bool) $this->isTLDLink()) ? 'YES' : 'NO';
        return <<<EOF
<li>LINK FMS Contract
    <ul>
        <li>Contract FMS : {$this->getServiceContractID()}</li>
        <li>LINK Status : {$isLink}</li>
        <li>SIM CARD Status : {$this->getSimStatus()}</li>
        <li>FMS End use date : {$this->getCalculatedFMSEndUseDate()}</li>
    </ul>
</li>
EOF;
    }

    public static function getAllowedWarrantyLength(): array
    {
        $wcLength = [24, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36,48, 50, 66, 60,72,84,96, 108, 120];

        return array_combine($wcLength, $wcLength);
    }

    public static function getFMSSimCardReport(string $WHERE = '1=1'): array
    {
        $query = <<<SQL
SELECT
    service.id,
    service.sn,
    service.maintenance_contract_ref,
    service.man_location,
    service.sales_org,
    customers.customer_name AS buyer_customer_display,
    (SELECT CONCAT(people.lastname, ' ', people.firstname) FROM people  WHERE people.id = customers.asm_id ) AS buyer_representative,
    IF(service.customer_id > 0, customers.customer_name, service.customer_name) AS user_customer_display,
    service.airport_code,
    service.type AS unit_type,
    service.model AS unit_model,
    service.date_shipped,
    service.dgt_act,
    countries.name AS apc_country_name,
    CASE
        WHEN COALESCE(fms_end_use_date , '0000-00-00') != '0000-00-00' THEN fms_end_use_date
        WHEN fms_contract_length > 0 AND dgt_act != '0000-00-00' THEN DATE_ADD(dgt_act, INTERVAL (fms_contract_length + 3) MONTH)
        WHEN fms_contract_length > 0 THEN 'Undefined now as unit is not GT'
        ELSE 'Not relevant, contract length is 0'
    END AS calculated_fms_end_use_date,
    service_serials.*,
    REPLACE(service_serials.serial, ' ', '') AS sim_serial,
    IF(REPLACE(service_serials.serial, ' ', '') REGEXP '^\\\\d{20}', 'OK, 20 digits', 'Error') as sim_check,
    (SELECT GROUP_CONCAT(serial ORDER BY id SEPARATOR '\n') FROM service_serials s2 WHERE s2.parent_id=service_serials.parent_id AND s2.component = 'OBU, LINK') AS obu_serial,
    (SELECT GROUP_CONCAT(brand ORDER BY id SEPARATOR '\n') FROM service_serials s3 WHERE s3.parent_id=service_serials.parent_id AND s3.component = 'OBU, LINK') AS obu_brand
FROM service_serials
INNER JOIN service ON service.id = service_serials.parent_id
LEFT JOIN customers ON customers.id = service.buyer_customer_id
LEFT JOIN airport_codes ON airport_codes.airport_code = service.airport_code
LEFT JOIN countries ON countries.iso_code_2 = airport_codes.ctry_code_2
WHERE component = 'SIM CARD, LINK' AND $WHERE
SQL;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getFMSSoldReport(string $WHERE = '1=1'): array
    {
        $query = <<<SQL
SELECT
    service.id,
    service.sn,
    DATE_FORMAT(sor_tran.dtran, '%Y-%m') AS transaction_date,
    service.dgt_act,
    service.man_location,
    service.sales_org,
    (SELECT customers.customer_name FROM customers WHERE customers.id = service.buyer_customer_id) AS buyer_customer_display,
    IF(service.customer_id > 0, (SELECT customers.customer_name FROM customers WHERE customers.id = service.customer_id), customer_name) AS user_customer_display,
    (SELECT GROUP_CONCAT(REPLACE(service_serials.serial, ' ', '') ORDER BY id SEPARATOR '\n') FROM service_serials WHERE service_serials.parent_id=service.id AND service_serials.component = 'SIM CARD, LINK') AS sim_serial,
    (SELECT GROUP_CONCAT(REPLACE(service_serials.model, ' ', '') ORDER BY id SEPARATOR '\n') FROM service_serials WHERE service_serials.parent_id=service.id AND service_serials.component = 'SIM CARD, LINK') AS sim_card_model,
    (SELECT GROUP_CONCAT(serial ORDER BY id SEPARATOR '\n') FROM service_serials WHERE service_serials.parent_id=service.id AND service_serials.component = 'OBU, LINK') AS obu_serial,
    CASE
        WHEN COALESCE(fms_end_use_date , '0000-00-00') != '0000-00-00' THEN fms_end_use_date
        WHEN fms_contract_length > 0 AND dgt_act != '0000-00-00' THEN DATE_ADD(dgt_act, INTERVAL (fms_contract_length + 3) MONTH)
        WHEN fms_contract_length > 0 THEN 'Undefined now as unit is not GT'
        ELSE 'Not relevant, contract length is 0'
        END AS calculated_fms_end_use_date
FROM service
INNER JOIN sor_tran on service.tranid_sso = sor_tran.id
INNER JOIN service_serials ON service.id = service_serials.parent_id AND service_serials.component IN ('OBU, LINK', 'SIM CARD, LINK')
WHERE service.dgt_act != '0000-00-00' AND (COALESCE(fms_end_use_date , '0000-00-00') != '0000-00-00' OR fms_contract_length > 0) AND $WHERE
GROUP BY service.id
SQL;

        return tldUtils::getSqlToAssocArray($query);
    }
}

class tldEquipment_Upgrade
{

    public $itsID;
    /**
     * @var array
     */
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
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
            WHERE service_upgrades.id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public static function getSELECT(): string
    {
        return <<<EOF
SELECT service_upgrades.*,
    CONCAT(people.firstname,' ',people.lastname) AS poster_fullname
EOF;
    }

    public static function getFROM(): string
    {
        return <<<EOF
FROM service_upgrades
    LEFT JOIN people ON service_upgrades.poster_id = people.id
EOF;
    }

    public static function getUpgradesByER($er_id)
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
            $SELECT
            $FROM
            WHERE service_upgrades.parent_id = $er_id
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);
        foreach ($rows as &$row) {
            $fileID = tldModFile::byParent($row['id'], 'ER_UPGRADE');
            if ($fileID) {
                $row['mod_file'] = $fileID[0]['id'];
                $row['mod_link'] = 'Link';
            }
        }

        return $rows;
    }

    public static function insert($p)
    {
        $fields = ['parent_id', 'poster_id', 'dt_upgrade', 'description','pn'];
        $query = <<<EOF
INSERT INTO service_upgrades
SET dt_open=NOW(),
EOF;
        $query .= tldUtils::getSqlSet($p, $fields);

        return tldUtils::sqlInsert($query);
    }

    public function update($data, $fields = '')
    {
        if (empty($this->itsID)) {
            return 'Not object context!';
        }
        $SET = tldUtils::getSqlSet($data, $fields);
        $query = "UPDATE service_upgrades SET $SET WHERE id=$this->itsID LIMIT 1";

        return tldUtils::sqlQuery($query);
    }

    public static function getLatest()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
            $SELECT
            $FROM
            ORDER BY dt_open DESC, id DESC
            LIMIT 10
EOF;

        $rows = tldUtils::getSqlToAssocArray($query);
        foreach ($rows as &$row) {
            $fileID = tldModFile::byParent($row['id'], 'ER_UPGRADE');
            if ($fileID) {
                $row['mod_file'] = $fileID[0]['id'];
                $row['mod_link'] = 'Link';
            }
        }

        return $rows;
    }

    public function addLogEntry($id, $comment, $num_log = 0)
    {
        $a['parent_id'] = $this->itsID;
        $a['module'] = 'er_upgr';
        $a['poster'] = $id;
        $a['comment'] = $comment;
        $a['log_num'] = $num_log;

        return tldModLog::insert($a);
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
            AND mod_logs.module LIKE 'er_upgr' AND log_num!=10)
EOF;

        $query .= ' ORDER BY id DESC';

        return tldUtils::getSqlToAssocArray($query);
    }
}

class tldSBNOT
{

    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    /**
     * @return int
     */
    public function getID()
    {
        return $this->itsID;
    }

    /**
     * @return int
     */
    public function getParentID()
    {
        return $this->itsHeader['parent_id'];
    }

    /**
     * @return int
     */
    public function getFileID()
    {
        return $this->itsHeader['fid'];
    }

    /**
     * Check if empty
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    /**
     * Get SELECT mysql statement
     *
     * @return string
     */
    public static function getSELECT(): string
    {
        return <<<EOF
SELECT
    sb_not.*,
    file.filename,
    CONCAT(people.firstname,' ',people.lastname) AS poster_fullname,
    people.email AS poster_email
EOF;
    }

    /**
     * Get FROM mysql statement
     *
     * @return string
     */
    public static function getFROM(): string
    {
        return <<<EOF
FROM
    sb_not
    LEFT JOIN file ON file.id=sb_not.fid
    LEFT JOIN people ON people.id=sb_not.uid
EOF;
    }

    /**
     * Get header
     *
     * @return row
     */
    public function getHeader()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
WHERE sb_not.id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Insert new NOT
     *
     * @param array $a
     *
     * @return int or string on error
     */
    public static function insert($a)
    {
        if (empty($a)) {
            return;
        }
        $fields = ['parent_id', 'uid', 'recipients', 'subject', 'email', 'cc', 'bcc', 'fid'];
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO sb_not SET dt=NOW(),$SET";

        return tldUtils::sqlInsert($query);
    }

    /**
     * Update NOT
     *
     * @param array $a
     * @param array $fields (optional)
     *
     * @return int or string on error
     */
    public function update($a, $fields = '')
    {
        if (empty($a)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "UPDATE sb_not SET $SET WHERE id=$this->itsID";

        return tldUtils::sqlQuery($query);
    }

    /**
     * Delete NOT
     *
     * @return string on error
     */
    public function delete()
    {
        $query = "DELETE FROM sb_not WHERE id=$this->itsID LIMIT 1";

        return tldUtils::sqlQuery($query);
    }

    /**
     * Get NOT by constraints
     *
     * @param array|string $a constraints
     * @param array $opt options
     *
     * @return array of rows
     */
    public static function byConstraints($a, $opt = [])
    {
        if (empty($a)) {
            return 'empty parameter';
        }
        $WHERE = is_array($a) ? 'WHERE ' . tldUtils::constructWhere($a) : $WHERE = "WHERE $a";
        // Look for options
        $ORDERBY = 'ORDER BY id';
        if (!empty($opt['orderBy'])) {
            $ORDERBY = 'ORDER BY ' . $opt['orderBy'];
        }
        // Construct query
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
$WHERE
$ORDERBY
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * By parent ID
     *
     * @param int $pid
     *
     * @return array|string on error
     */
    public static function byParent($pid)
    {
        return self::byConstraints("sb_not.parent_id='$pid'");
    }

}

/**
 * Class for accessing and manipulating service_lines table data
 *
 * @package Support
 */
class tldSR
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

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public function getHeader()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = "$SELECT $FROM WHERE sr.id=$this->itsID";

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public static function getSELECT(): string
    {
        return <<<EOF
SELECT
    sr.*,
    er.sn,
    er.model,
    er.customer_id,
    (SELECT customer_name FROM customers
        WHERE id=er.customer_id
    ) AS user_customer,
    er.man_location,
    er.sales_org
EOF;
    }

    public static function getFROM(): string
    {
        return <<<EOF
FROM service_lines AS sr
    LEFT JOIN service AS er ON er.id=sr.parent_id
EOF;
    }

    public static function insert($a)
    {
        $fields = [
            'parent_id',
            'sr_status',
            'sr_location',
            'date_entered',
            'date',
            'hourmeter',
            'work_type',
            'description',
            'extranet_desc',
            'technician',
            'factory_technician',
            'technician_hours',
            'technician_cost_te',
            'technician_cost_manhours',
            'cost_parts',
            'reason',
        ];
        $query = 'INSERT INTO service_lines SET';
        $query .= tldUtils::getSqlSet($a, $fields);

        return tldUtils::sqlInsert($query);
    }

    public function update($data, $fields = '')
    {
        if (empty($this->itsID)) {
            return 'Not object context!';
        }
        $SET = tldUtils::getSqlSet($data, $fields);
        $query = "UPDATE service_lines SET $SET WHERE id=$this->itsID LIMIT 1";

        return tldUtils::sqlQuery($query);
    }

    public function addLogEntry($id, $comment)
    {
        $a['parent_id'] = $this->itsID;
        $a['module'] = 'SR';
        $a['poster'] = $id;
        $a['comment'] = $comment;

        return tldModLog::insert($a);
    }

    public function getLog()
    {
        return tldModLog::byParent($this->itsID, 'SR');
    }

    public function getFiles()
    {
        return tldModFile::byParent($this->itsID, 'SR');
    }

    public function getLinksFromHere($type = '')
    {
        return tldModLink::byParent($this->itsID, 'SR', $type);
    }

    public function getLinksToHere($module = '')
    {
        return tldModLink::byItem($this->itsID, 'SR', $module);
    }

    public function getStatus()
    {
        return $this->itsHeader['sr_status'];
    }

    public function getOpenStatusList()
    {
        return ['NEW', 'Approved', 'Not Approved', 'To Be Invoiced', 'Invoiced'];
    }

    public function getClosedStatusList()
    {
        return ['Complete'];
    }

    public function isClosed()
    {
        return in_array($this->getStatus(), $this->getClosedStatusList());
    }

    public function getStatusAllowed()
    {
        switch ($this->getStatus()) {
            case 'Complete':
                return;
                break;
            default:
                return [
                    'NEW',
                    'Approved',
                    'Not Approved',
                    'To Be Invoiced',
                    'Invoiced',
                    'Complete',
                ];
                break;
        }

        return;
    }

    public function changeStatus($status)
    {
        if (empty($this->itsID)) {
            return 'Not in object context';
        }
        $allowed = $this->getStatusAllowed();
        if (!in_array($status, (array)$allowed)) {
            return "Status $status not allowed";
        }

        return $this->update(['sr_status' => $status]);
    }

    public function getParts()
    {
        if (empty($this->itsID)) {
            return;
        }
        $query = <<<EOF
SELECT * FROM sr_parts
WHERE parent_id=$this->itsID
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byConstraints($a, $opt = [])
    {
        if (empty($a)) {
            return 'empty parameter';
        }
        // Look for constraints
        if (is_array($a)) {
            $HAVING = 'HAVING ' . tldUtils::constructWhere($a);
        } else {
            $HAVING = "HAVING $a";
        }
        // Look for options
        if (!empty($opt['orderBy'])) {
            $ORDERBY = 'ORDER BY ' . $opt['orderBy'];
        } else {
            $ORDERBY = 'ORDER BY sr.id DESC';
        }
        // Construct query
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
$HAVING
$ORDERBY
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byParent($pid)
    {
        $a = ['parent_id' => $pid];

        return self::byConstraints($a);
    }

    public static function byLatest($num = 10)
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = "$SELECT $FROM ORDER BY id DESC LIMIT $num";

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function search($m)
    {
        $fields = [
            'sr_status',
            'sn',
            'model',
            'user_customer',
            'sr_location',
            'work_type',
            'description',
            'extranet_desc',
            'technician',
            'factory_technician',
            'reason',
        ];
        $a = [];
        foreach ($fields as $field) {
            $a[$field] = $m;
        }
        $a = tldUtils::constructWhere($a, 'OR');

        return self::byConstraints($a);
    }

    /**
     * Search SR by part number in sr_parts table
     *
     * @param string $pn
     *
     * @return array
     */
    public function byPart($pn)
    {
        $a = <<<EOF
(SELECT COUNT(*) FROM sr_parts WHERE sr_parts.parent_id=sr.id
AND (sr_parts.item LIKE '$pn' OR sr_parts.dsca LIKE '%$pn%')) > 0
EOF;

        return self::byConstraints($a);
    }

    public function countBySSOWorkTypeByConstraints($a = '')
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "WHERE $WHERE";
        }
        $query = <<<EOF
SELECT er.sales_org, sr.work_type, COUNT(*) AS num
FROM service_lines AS sr
LEFT JOIN service AS er ON er.id=sr.parent_id
$WHERE
GROUP BY er.sales_org, sr.work_type
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get number of commissioned SR by SSO and ERP for a particular year
     *
     * @param $year int
     *
     * @return array of rows
     */
    public function countByCommissionedYearSSOERP($year)
    {
        $year = TldDatabase::escape($year);
        $query = <<<EOF
			SELECT
				er.man_location,
				er.sales_org,
				count(*) as srnum
			FROM service_lines AS sr
				LEFT JOIN service AS er ON er.id=sr.parent_id
			WHERE sr.work_type='Commissioning'
				AND YEAR(sr.date_entered)=$year
			GROUP BY sales_org, man_location
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get list of commissioned SR by SSO and ERP for a particular year
     *
     * @param $year int year
     * @param $sso  string sales org
     * @param $erp  string manufacture
     *
     * @return array of rows
     */
    public function byCommisionedYearSSOERP($year, $sso, $erp)
    {
        $a = "sr.work_type='Commissioning' AND YEAR(sr.date_entered)=$year";
        if ($sso !== 'ALL') {
            $a .= " AND er.sales_org='$sso'";
        }
        if ($erp !== 'ALL') {
            $a .= " AND er.man_location='$erp'";
        }

        return self::byConstraints($a);
    }

    /**
     * Could SR records by sso and by Status
     *
     * @return array
     */
    public function countBySSOStatus()
    {
        $query = <<<EOF
SELECT
    sr.sr_status AS status,
    er.sales_org AS sso,
    count(*) as num
FROM service_lines AS sr
    LEFT JOIN service AS er ON er.id=sr.parent_id
GROUP BY
    sr.sr_status, er.sales_org
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * By sso and by Status
     *
     * @param $sso    string
     * @param $status string
     *
     * @return array
     */
    public function bySSOStatus($sso, $status)
    {
        $a = [];
        if ($sso !== 'ALL') {
            $a['er.sales_org'] = $sso;
        }
        if ($status !== 'ALL') {
            $a['sr.sr_status'] = $status;
        }

        return self::byConstraints($a);
    }

    /**
     * Get Print Version of SR
     *
     * @return string html
     */
    public function getPrintVersion()
    {
        if (empty($this->itsID)) {
            return;
        }
        $general = new tldAssocTable(
            $this->itsHeader,
            [
                'id' => 'SR#',
                'sr_status' => 'Status',
                'sn' => 'Equipment Serial Number',
                'model' => 'Equipment Model',
                'user_customer' => 'Customer Name',
                'cu_contact' => 'Service Requested By',
                'sr_location' => 'Service Location',
                'date_entered' => 'Date of Service',
                'work_type' => 'Work Type',
                'reason' => 'Reason for Service',
                'description' => 'Service Description',
                'extranet_desc' => 'Customer Viewable Description',
                'hourmeter' => 'Hourmeter Reading',
                'technician' => 'Technician',
                'technician_hours' => 'Labour Hours',
                'technician_cost_te' => 'T&E Host',
                'technician_cost_manhours' => 'Labour Cost',
                'cost_parts' => 'Parts Cost',
                'cost_notes' => 'Cost Notes',
            ],
            ['title' => 'Service Record #' . $this->itsID]
        );

        return $general->fetch();
    }

}

/**
 * Class for accessing and manipulating odp table data
 *
 * @package Support
 */
class tldODP
{
    /**
     * returns a 2 dim array of counts by erp and status per ASM
     *
     * @return array array of db rows
     */
    public static function countByERPStatusByASM($asm, $sso)
    {
        if (!empty($sso)) {
            $ASMGrp = new tldGroup('role_ASM');
            $ASMList = array_column($ASMGrp->getUserlistBySSO($sso), 'id', 'id');
            $SalesAgentGrp = new tldGroup('gg_SALES_AGENTS');
            $SalesAgentList = array_column($SalesAgentGrp->getUserlistBySSO($sso), 'id', 'id');
            $list = implode(',', $ASMList + $SalesAgentList);
            $WHERE = " t1.asm IN ($list) ";
        } else {
            $WHERE = " t1.asm=$asm ";
        }
        $query = <<<EOF
SELECT
if(t4.man_location like '',
    'NO_ERP',
 (t4.man_location)
 ) AS erp_fullname,
CASE
WHEN t3.ddel_est1 = t3.del_dat THEN "ON TIME"
WHEN t3.ddel_est1 > t3.del_dat THEN "DELAYED"
WHEN t3.ddel_est1 < t3.del_dat THEN "EARLY"
END AS delivery,
count(*) AS num
FROM
    service AS t4
    LEFT JOIN sor_units AS t3 ON t3.id=t4.sor_uid
    LEFT JOIN sor_lines AS t2 ON t2.id=t3.parent_id
    LEFT JOIN sor AS t1 ON t1.id=t2.parent_id
WHERE $WHERE AND (DATE_FORMAT(NOW(),'%Y%m%d') < DATE_FORMAT(t3.del_dat,'%Y%m%d') OR DATE_FORMAT(NOW(),'%Y%m%d') < DATE_FORMAT(t3.ddel_est1,'%Y%m%d'))
GROUP BY erp_fullname, delivery
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function countBySSOStatus()
    {

        $query = <<<EOF
SELECT
 t5.location AS sso_fullname,
 if(t4.status = '',
    'NO_STATUS',
 t4.status
 ) AS status,
count(*) AS num
FROM
    service AS t4
    LEFT JOIN sor_units AS t3 ON t3.id=t4.sor_uid
    LEFT JOIN sor_lines AS t2 ON t2.id=t3.parent_id
    LEFT JOIN sor AS t1 ON t1.id=t2.parent_id
    LEFT JOIN locations AS t5 ON t1.bu=t5.erp
WHERE t5.location IS NOT NULL
GROUP BY sso_fullname, status
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * returns a 2 dim array of counts by erp and status
     *
     * @return array array of db rows
     */
    public static function countByERP_SSO($c = '', $options = false)
    {
        if ($options === true) {
            $WHERE = '1=1';
        } else {
            $WHERE = "t4.date_shipped='0000-00-00'";
        }

        if (is_array($c)) {
            $WHERE .= ' AND ' . tldUtils::constructWhere($c);
        }

        $query = <<<EOF
SELECT
if(t1.id is null,
    'NO_SSO',
 (select location from locations where erp=t1.bu)
 ) AS sso_fullname,
 if(t4.man_location='',
    'NO_FACTORY',
    t4.man_location) as erp_fullname,
count(*) AS num
FROM
    service AS t4 LEFT JOIN sor_units AS t3 ON t3.id=t4.sor_uid
    LEFT JOIN sor_lines AS t2 ON t2.id=t3.parent_id
    LEFT JOIN sor AS t1 ON t1.id=t2.parent_id
WHERE $WHERE
GROUP BY sso_fullname, erp_fullname
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * get odp by sales org and status, per ASM
     *
     * @param string $sso
     * @param string $status
     * @param int $asm
     *
     * @return array
     */
    public static function byERPStatusByASM($erp, $delivery, $asm, $sso)
    {
        $WHERE = " HAVING t1.asm=$asm ";
        if (!empty($sso)) {
            $ASMGrp = new tldGroup('role_ASM');
            $ASMList = array_column($ASMGrp->getUserlistBySSO($sso), 'id', 'id');
            $list = implode(',', $ASMList);
            $WHERE = " HAVING t1.asm IN ($list) ";
        }

        $WHERE .= " AND (DATE_FORMAT(NOW(),'%Y%m%d') < DATE_FORMAT(t3.del_dat,'%Y%m%d') OR DATE_FORMAT(NOW(),'%Y%m%d') < DATE_FORMAT(t3.ddel_est1,'%Y%m%d')) ";
        if ($erp !== 'ALL') {
            $WHERE .= " AND erp_fullname LIKE '$erp' ";
        }

        if ($delivery === 'EARLY') {
            $WHERE .= ' AND t3.ddel_est1 < t3.del_dat ';
        } elseif ($delivery === 'ON TIME') {
            $WHERE .= ' AND t3.ddel_est1 = t3.del_dat ';
        } elseif ($delivery === 'DELAYED') {
            $WHERE .= ' AND t3.ddel_est1 > t3.del_dat ';
        }

        $query = <<<EOF
SELECT
    t1.cu_nama,
    (SELECT MIN(date) FROM mod_logs
        WHERE module='SOL' AND parent_id=t2.id
        AND comment LIKE '%PRINT_FACTORY_SO_ACK%'
    ) as dpo_ack,
    if(t4.status = '',
    'NO_STATUS',
    t4.status
    ) AS status,
     if(t4.man_location like '',
    'NO_ERP',
    (t4.man_location)
    ) AS erp_fullname,
    if((select count(*) from locations where erp=t1.bu)=0,
    'NO_SSO',
    (select location from locations where erp=t1.bu)
    ) AS sso_fullname,
    if(t4.man_location='',
    'NO_FACTORY',
    t4.man_location) as erp_fullname,
    (SELECT concat(firstname, ', ', lastname)
    FROM people WHERE id=t1.asm
    ) AS asm_fullname,
    (SELECT IF(SUM(trans.tval) <= 0, '', FORMAT(SUM(trans.tval),2))
    FROM sor_tran AS trans
    WHERE trans.parent_id=t2.id AND trans.ttyp='R'
    ) AS trans_total,
    (SELECT count(*)
    FROM crabs
    WHERE erid=t4.id
        AND status!='CLOSED'
    ) AS nb_crabs,
    (SELECT GROUP_CONCAT(trans.nref SEPARATOR ', ')
    FROM sor_tran AS trans
    WHERE trans.parent_id=t2.id AND trans.nref!=''
    ) AS sso_invoice,
    (SELECT sor_tran.nref FROM sor_tran WHERE t4.tranid_sso=sor_tran.id) AS 'direct_sso_invoice',
    (SELECT sor_tran.nref FROM sor_tran WHERE t4.tranid_erp=sor_tran.id) AS 'direct_erp_invoice',
    CONCAT(RTRIM(SUBSTRING_INDEX(t2.tpay, ' ', 12)), '...') AS payment_terms,
    IF(t4.customer_id > 0,
    (SELECT cust.customer_name FROM customers AS cust WHERE cust.id=t4.customer_id),
    t4.customer_name) AS er_user_customer_display,
    (SELECT cust.customer_name FROM customers AS cust WHERE cust.id=t4.buyer_customer_id) AS er_buyer_customer_display,
    IF(t1.dt_closed > '0000-00-00 00:00:00',t1.cu_nama,
    (SELECT cust.customer_name FROM customers AS cust WHERE cust.id=t1.user_customer_id)) AS sor_user_customer_display,
    (SELECT cust.customer_name FROM customers AS cust WHERE cust.id=t1.buyer_customer_id) AS sor_buyer_customer_display,
    t1.orno, t1.cu_nama, t1.bu, t1.asm, t1.id AS sorid, t2.inco, t2.inco_loc,
    t2.id AS solid, t2.sls_orno AS sso_po, t2.parts_inc, t2.conf_cis,
    t3.id,t3.short_desc, t3.long_desc, t3.del_dat, t3.ddel_est1, t3.dgt_est,
    IF(t4.dgt_act='0000-00-00' and  NOW() > t3.ddel_est1, 'Y', 'N') AS del_late,
    t1.cu_orno, t2.tpay, t2.conf_sls, t4.*
FROM
    service AS t4
    LEFT JOIN sor_units AS t3 ON t3.id=t4.sor_uid
    LEFT JOIN sor_lines AS t2 ON t2.id=t3.parent_id
    LEFT JOIN sor AS t1 ON t1.id=t2.parent_id
$WHERE
ORDER BY
    t4.dgt_act DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * get odp by factory and sales org
     *
     * @param string $erp
     * @param string $sso
     *
     * @return array
     */
    public static function byERP_SSO($erp, $sso, $mode = '')
    {
        $constraints = ['state' => 'ACTIVE'];
        if ($erp !== 'ALL') {
            $constraints['erp_fullname'] = $erp;
        }
        if ($sso !== 'ALL') {
            $constraints['sso_fullname'] = $sso;
        }

        return self::byQuery($constraints, $mode);
    }

    public static function bySOL($solid, $mode = '')
    {
        return self::byQuery(['solid' => $solid], $mode);
    }

    public static function byERP_SSO_CU($erp, $sso, $cu, $mode = '')
    {
        $constraints = ['state' => 'ACTIVE'];
        if ($erp !== 'ALL') {
            $constraints['erp_fullname'] = $erp;
        }
        if ($sso !== 'ALL') {
            $constraints['sso_fullname'] = $sso;
        }
        if ($cu !== 'ALL') {
            $constraints['cu_nama'] = $cu;
        }

        return self::byQuery($constraints, $mode);
    }

    /**
     * get odp by factory and ASM
     *
     * @param string $erp
     * @param string $asmid
     *
     * @return array
     */
    public static function byERP_ASM($erp, $asmid, $mode = '')
    {
        $constraints = ['state' => 'ACTIVE'];
        if ($erp !== 'ALL') {
            $constraints['erp_fullname'] = $erp;
        }
        if ($asmid !== 'ALL') {
            $constraints['asm'] = $asmid;
        }

        return self::byQuery($constraints, $mode);
    }

    /**
     * get odp by factory and customer name
     *
     * @param string $erp
     * @param string $cu_nama
     *
     * @return array
     */
    public static function byERP_CU($erp, $cu_nama, $mode = '')
    {
        $constraints = ['state' => 'ACTIVE'];
        if ($erp !== 'ALL') {
            $constraints['erp_fullname'] = $erp;
        }
        if ($cu_nama !== 'ALL') {
            $constraints['cu_nama'] = $cu_nama;
        }

        return self::byQuery($constraints, $mode);
    }

    /**
     * get all late GT ODP
     *
     * @param string $erp
     * @param string $date
     *
     * @return array
     */
    public static function byLateGT_ERP($erp, $date)
    {
        if (empty($date)) {
            return [];
        }
        $constraints = [
            'state' => 'ACTIVE',
            'date' => $date,
        ];

        if ($erp !== 'ALL') {
            $constraints['erp_fullname'] = $erp;
        }

        return self::byQuery($constraints, 'lateGT');
    }

    public static function byConstraints($a, $opt = [])
    {
        $HAVING = is_array($a) ? tldUtils::constructWhere($a) : $a;
        $HAVING = !empty($HAVING) ? "HAVING $HAVING" : '';
        $ORDERBY = !empty($opt['orderBy']) ? $opt['orderBy'] : 'dgt_act';

        $query = <<<EOF
SELECT
    er.*,
    IF(sso.location!='',sso.location,'NO_SSO') AS sso_fullname,
    IF(er.man_location!='',er.man_location,'NO_FACTORY') AS erp_fullname,
    (SELECT COUNT(*) FROM crabs
        WHERE erid=er.id AND status!='CLOSED'
    ) AS nb_crabs,
    (SELECT COUNT(*) FROM tasks 
        WHERE tasks.parent_id=er.id AND tasks.status = 'OPEN' AND tasks.tplno IN ('53','54','55')
    ) AS nb_lategt_seq,
    IF(er.customer_id>0,
        (SELECT cust.customer_name FROM customers AS cust WHERE cust.id=er.customer_id),
        er.customer_name
    ) AS er_user_customer_display,
    (SELECT cust.customer_name FROM customers AS cust
        WHERE cust.id=sor.buyer_customer_id
    ) AS er_buyer_customer_display,
    sor_units.id AS soruid,
    sor_units.short_desc,
    sor_units.long_desc,
    sor_units.del_dat,
    sor_units.ddel_est1,
    sor_units.dgt_est,
    sor_units.sleep_com,
    sor_units.sleep_com_sso_id,
    IF(sor_units.sleep_com_sso_id > 0, (SELECT location FROM locations WHERE id=sor_units.sleep_com_sso_id ),'') as sleep_com_sso_name,
    IF(er.dgt_act='0000-00-00' AND  NOW()>sor_units.ddel_est1, 'Y', 'N') AS del_late,
    sol.id AS solid,
    sol.tpay,
    sol.tpay AS payment_terms,
    CONCAT(RTRIM(SUBSTRING_INDEX(sol.tpay, ' ', 12)), '...') AS short_payment_terms,
    sol.conf_sls,
    sol.inco,
    sol.inco_loc,
    sol.sls_orno AS sso_po,
    sol.parts_inc,
    sol.conf_cis,
    (SELECT MIN(date) FROM mod_logs
        WHERE module='SOL' AND parent_id=sol.id
        AND comment LIKE '%PRINT_FACTORY_SO_ACK%'
    ) as dpo_ack,
    (SELECT IF( SUM(trans.tval)<=0, '', FORMAT(SUM(trans.tval),2) )
        FROM sor_tran AS trans WHERE trans.parent_id=sol.id AND trans.ttyp='R'
    ) AS trans_total,
    (SELECT GROUP_CONCAT(trans.nref SEPARATOR ', ')
        FROM sor_tran AS trans WHERE trans.parent_id=sol.id AND trans.nref!=''
    ) AS sso_invoice,
    (SELECT sor_tran.nref FROM sor_tran WHERE er.tranid_sso=sor_tran.id) AS 'direct_sso_invoice',
    (SELECT sor_tran.nref FROM sor_tran WHERE er.tranid_erp=sor_tran.id) AS 'direct_erp_invoice',
    sor.id AS sorid,
    IF(sor.id IS NULL AND er.light, er.orno, sor.orno) as orno,
    sor.cu_nama,
    sor.bu,
    sor.asm,
    sor.cu_orno,
    (SELECT CONCAT(firstname,' ',lastname)
        FROM people WHERE id=sor.asm
    ) AS asm_fullname,
    IF(sor.dt_closed > '0000-00-00 00:00:00',
        sor.cu_nama,
        (SELECT cust.customer_name FROM customers AS cust WHERE cust.id=sor.user_customer_id)
    ) AS sor_user_customer_display,
    (SELECT cust.customer_name FROM customers AS cust
        WHERE cust.id=sor.buyer_customer_id
    ) AS sor_buyer_customer_display,
    esrl.dt_shipped AS esr_dt_shipped,
    esrl.dt_estimated AS esr_dt_estimated,
    esrl.dt_arrived AS esr_dt_arrived,
    sol.status AS sol_status
FROM
    service AS er
    LEFT JOIN esrl ON esrl.erid = er.id AND esrl.parent_id = er.esrid
    LEFT JOIN sor_units ON sor_units.id=er.sor_uid
    LEFT JOIN sor_lines AS sol ON sol.id=sor_units.parent_id
    LEFT JOIN sor ON sor.id=sol.parent_id
    LEFT JOIN locations AS sso ON sso.erp=sor.bu
$HAVING
ORDER BY
    $ORDERBY
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get odp lines using an array of constraints
     *
     * @param array $a key and value pairs of constraints to use in tldUtils::constructWhere function
     *
     * @return array
     */
    public static function byQuery($a, $mode = '')
    {
        $SELECT = $WHERE = '';
        $CONSTRAINTS = [];
        if (is_array($a)) {
            switch ($mode) {
                case 'lateGT':
                    $WHERE = ' WHERE ';
                    if (!empty($a['erp_fullname'])) {
                        $WHERE .= "t4.man_location LIKE '{$a['erp_fullname']}' AND ";
                    }
                    $WHERE .= <<<SQL
        t4.dyt = '0000-00-00' AND
        (
          (MONTH('{$a['date']}') = MONTH(t3.ddel_est1) AND YEAR('{$a['date']}') = YEAR(t3.ddel_est1)
             AND (
               (t4.dgt_act IS NOT NULL AND t4.dgt_act > t3.ddel_est1)
                 OR
               (t4.dgt_act IS NULL AND CURRENT_DATE > t3.ddel_est1)
               )
              )
            OR (t3.ddel_est1 != '0000-00-00' AND '{$a['date']}' > t3.ddel_est1 AND t4.dgt_act IS NULL)
          )
SQL;

                    break;
                case 'byGTmonth':
                    //return only first GT
                    $WHERE = ' WHERE MONTH(t4.dgt_com)=' . $a['date']['m'] . ' AND	YEAR(t4.dgt_com)=' . $a['date']['Y'] . ' ';
                    if ($a['erp'] !== 'ALL') {
                        $WHERE .= "AND t4.man_location LIKE '" . $a['erp'] . "' AND t4.sn NOT LIKE 'P%' ";
                    }
                    break;
                case 'byGTperiod':
                    $WHERE = " WHERE DATEDIFF(t4.dgt_com,'{$a['date']['start']}')>=0
                AND DATEDIFF(t4.dgt_com,'{$a['date']['end']}')<=0 ";
                    if ($a['erp'] !== 'ALL') {
                        $WHERE .= "AND t4.man_location LIKE '" . $a['erp'] . "' ";
                    }
                    break;
                case 'byOnTimeMonth':
                    $WHERE = " WHERE DATEDIFF(t4.dgt_com, DATE_ADD(t3.ddel_est1, interval 4 day))<=0
                AND MONTH(t4.dgt_com)={$a['date']['m']} AND	YEAR(t4.dgt_com)={$a['date']['Y']} ";
                    if ($a['erp'] !== 'ALL') {
                        $WHERE .= "AND t4.man_location LIKE '" . $a['erp'] . "' ";
                    }
                    break;
                case 'byOnTimePeriod':
                    $WHERE = " WHERE DATEDIFF(t4.dgt_com, DATE_ADD(t3.ddel_est1, interval 4 day))<=0
                AND DATEDIFF(t4.dgt_com,'{$a['date']['start']}')>=0
                AND DATEDIFF(t4.dgt_com,'{$a['date']['end']}')<=0 ";
                    if ($a['erp'] !== 'ALL') {
                        $WHERE .= "AND t4.man_location LIKE '" . $a['erp'] . "' ";
                    }
                    break;
                case 'sn_in':
                    $CONSTRAINTS[] = " t4.sn IN ('" . implode("','", $a) . "')";
                    break;
                default:
                    $CONSTRAINTS[] = tldUtils::constructWhere($a);
            }
        }
        switch ($mode) {
            case 'byGTmonth':
            case 'byOnTimeMonth':
            case 'byGTperiod':
            case 'byOnTimePeriod':
            case 'bySN': // WARNING only for PSM
            case 'byERP_SSO_CU_FULL':
            case 'lateGT':
                break;
            case 'num_late':
                $CONSTRAINTS[] = " t4.dgt_act='0000-00-00' AND DATEDIFF(NOW(), t3.ddel_est1) > 0 AND t1.id is not null";
                break;
            case 'num_backlog':
                $CONSTRAINTS[] = " t4.dgt_act='0000-00-00' AND t3.id IS NOT NULL";
                break;
            case 'num_near_gt':
                $CONSTRAINTS[] = " DATEDIFF(NOW(), t4.dgt_rev) between -14 and 0 AND t4.dgt_act='0000-00-00' AND t4.date_shipped='0000-00-00'";
                break;
            case 'num_just_shipped':
                $CONSTRAINTS[] = ' DATEDIFF(NOW(), t4.date_shipped) between 0 and 14 ';
                break;
            case 'num_just_gtd':
                $CONSTRAINTS[] = ' DATEDIFF(NOW(), t4.dgt_act) between 0 and 14';
                break;
            case 'num_near_due':
                $CONSTRAINTS[] = ' DATEDIFF(esrl.dt_pick_up, NOW()) between 0 and 14 or null';
                break;
            case 'num_gtns':
                $CONSTRAINTS[] = " (DATEDIFF(t4.dgt_act,t4.dyt)>=0 OR DATEDIFF(t4.dgt_act,t4.dyt) IS NULL)
				AND t4.dgt_act!='0000-00-00' AND t4.date_shipped='0000-00-00'";
                break;
            case 'num_ytns':
                $CONSTRAINTS[] = " t4.dyt > t4.dgt_act AND t4.dyt!='0000-00-00' AND t4.date_shipped='0000-00-00'";
                break;
            case 'num_afs':
                $CONSTRAINTS[] = " t4.customer_name IN ('**AVAILABLE FOR SALE**', 'TLD EUROPE', 'TLD AMERICA', 'TLD ASIA') AND t4.date_shipped='0000-00-00'";
                break;
            case 'bySSOByFactoryByDatePeriod':
            case 'byCustomerAndChildren':
                $WHERE = 'WHERE ' . $a;
                break;
            case 'swissport':
                $SELECT = <<<EOF
(SELECT CONCAT(postal_code, ', ', city, ', ',  country) FROM locations WHERE business_unit=t4.man_location AND locations.factory='Y' AND public=1) AS origin,
CONCAT(diml, ' x ', dimw, ' x ',  dimh, ' @ ', dimk) AS dimensions,
(SELECT inco from esr WHERE id = t4.esrid) AS esr_inco,
CASE
	WHEN (SELECT COUNT(*) FROM csr WHERE csr.parent_id=t4.id AND csr.work_type='Commissioning' AND csr.status LIKE 'CLOSED') > 0 THEN 'Equipment Delivered on site'
	WHEN esrl.dt_arrived != '0000-00-00' AND (DATEDIFF(NOW(), esrl.dt_arrived) > 0) THEN 'Equipment Delivered on site'
	WHEN esrl.dt_pick_up != '0000-00-00' AND (DATEDIFF(NOW(), esrl.dt_pick_up) > 0) THEN 'Equipment Left Factory'
	WHEN t4.dgt_com != '0000-00-00' AND (DATEDIFF(NOW(), t4.dgt_com)) > 0  THEN 'Equipment Produced'
	ELSE 'Equipment In Production'
END AS swissport_status, 
IF(esrl.dt_arrived!='0000-00-00', esrl.dt_arrived, esrl.dt_estimated) AS date_arrival,
(SELECT COUNT(*) FROM sor_units WHERE t2.id=sor_units.parent_id) AS quantity,
EOF;

                $WHERE = 'WHERE ' . $a;
                break;
            default:
                $CONSTRAINTS[] = " t4.date_shipped='0000-00-00'";
        }

        if (isset($a['state']) && $a['state'] !== null) {
            $CONSTRAINTS[] = "state = '{$a['state']}'";
        }

        $HAVING = $CONSTRAINTS ? ' HAVING ' . implode(' AND ', $CONSTRAINTS) : '';

        $query = <<<EOF
SELECT $SELECT
    t1.id as sor_id,
    t1.cu_nama,  t4.esrid, esr.ship_auth AS esr_ship_auth,
    (SELECT MIN(date) FROM mod_logs WHERE module='SOL' AND parent_id=t2.id AND comment LIKE '%PRINT_FACTORY_SO_ACK%') as dpo_ack,
    if((select count(*) from locations where erp=t1.bu AND role='SSO')=0, 'NO_SSO', (select location from locations where erp=t1.bu AND role='SSO')) AS sso_fullname,
    if(t4.man_location='', 'NO_FACTORY', t4.man_location) as erp_fullname,
    (SELECT concat(firstname, ', ', lastname) FROM people WHERE id=t1.asm) AS asm_fullname,
    (SELECT IF(SUM(trans.tval) <= 0, '', FORMAT(SUM(trans.tval),2)) FROM sor_tran AS trans WHERE trans.parent_id=t2.id AND trans.ttyp='R') AS trans_total,
    (SELECT count(*) FROM crabs WHERE erid=t4.id AND status!='CLOSED') AS nb_crabs,
    (SELECT COUNT(*) FROM tasks WHERE tasks.parent_id=t4.id AND tasks.status='OPEN' AND tasks.tplno IN ('53','54','55')) AS nb_lategt_seq,
    (SELECT GROUP_CONCAT(trans.nref SEPARATOR ', ') FROM sor_tran AS trans WHERE trans.parent_id=t2.id AND trans.nref!='') AS sso_invoice,
    (SELECT sor_tran.nref FROM sor_tran WHERE t4.tranid_sso=sor_tran.id) AS 'direct_sso_invoice',
    (SELECT sor_tran.nref FROM sor_tran WHERE t4.tranid_erp=sor_tran.id) AS 'direct_erp_invoice',
    CONCAT(RTRIM(SUBSTRING_INDEX(t2.tpay, ' ', 12)), '...') AS short_payment_terms,
    t2.tpay AS payment_terms,
	IF(t4.customer_id > 0, (SELECT cust.customer_name FROM customers AS cust WHERE cust.id=t4.customer_id), t4.customer_name) AS er_user_customer_display,
	(SELECT cust.customer_name FROM customers AS cust WHERE cust.id=t4.buyer_customer_id) AS er_buyer_customer_display,
    IF(t1.dt_closed > '0000-00-00 00:00:00',t1.cu_nama, (SELECT cust.customer_name FROM customers AS cust WHERE cust.id=t1.user_customer_id)) AS sor_user_customer_display,
	(SELECT cust.customer_name FROM customers AS cust WHERE cust.id=t1.buyer_customer_id) AS sor_buyer_customer_display,
    t1.cu_nama, t1.bu, t1.asm, t1.id AS sorid, t2.inco, t2.inco_loc,
    t2.id AS solid, t2.sls_orno AS sso_po, t2.parts_inc, t2.conf_cis,
    t3.id,t3.short_desc, t3.long_desc, t3.del_dat, t3.ddel_est1, t3.dgt_est,
	IF(t3.commissioning = 1, 'Yes', 'No') AS sor_unit_commissioning,
    IF(t4.dgt_act='0000-00-00' and  NOW() > t3.ddel_est1, 'Y', 'N') AS del_late,
    t1.cu_orno, t2.tpay, t2.conf_sls, t4.*,
    esrl.dt_shipped AS esr_dt_shipped,
    esrl.dt_estimated AS esr_dt_estimated,
    esrl.dt_arrived AS esr_dt_arrived,
    esrl.dt_pick_up,
    t2.status AS sol_status,
    t4.airport_code AS airport_code,
    t4.dt_commissioned,
    IF(csr.id IS NULL, 'N/A', DATE_FORMAT(csr.dt_sche, '%Y-%m-%d')) AS csr_schedule_date,
    (SELECT IF(esr.ship_auth, 'Yes', 'No') from esr where esr.id=t4.esrid) AS auth,
    IF(t2.id IS NULL AND t4.light, t4.sls_orno, t2.erp_orno) AS sls_orno,
    IF(t1.id IS NULL AND t4.light, t4.orno, t1.orno)
FROM
	service AS t4
	LEFT JOIN sor_units AS t3 ON t3.id=t4.sor_uid
	LEFT JOIN esrl ON esrl.erid = t4.id AND esrl.parent_id = t4.esrid
    LEFT JOIN sor_lines AS t2 ON t2.id=t3.parent_id
    LEFT JOIN sor AS t1 ON t1.id=t2.parent_id
    LEFT JOIN esr ON esrl.parent_id = esr.id
	LEFT JOIN csr ON csr.parent_id = t4.id
$WHERE
$HAVING
ORDER BY
    t4.dgt_act DESC
EOF;

        if ($mode === 'withoutSSO') {
            // Split the SQL query into individual lines
            $lines = explode("\n", $query);
            $cleaned = [];
            $removing = false;

            foreach ($lines as $line) {
                // Check if the current line contains the problematic subquery with multiple lines
                if (!$removing && stripos($line, 'AS sso_fullname') !== false) {
                    // Start skipping lines that are part of this SELECT expression
                    $removing = true;
                }

                if (!$removing) {
                    $cleaned[] = $line; // Keep this line
                }

                // Stop skipping once we reach the end of the SQL expression (assumed by trailing comma)
                if ($removing && preg_match('/,\s*$/', trim($line))) {
                    $removing = false;
                }
            }

            // Rebuild the query without the skipped lines
            $query = implode("\n", $cleaned);
        }

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Update the ODP dates
     *
     * @param array $lines
     */
    public function updateDates($lines)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * Get stats for Ontime Delivery Performance
     *
     * @param integer $erp erp number or none for all factories
     */
    public static function statsOTDP($options = '')
    {
        if (isset($options['man_location'])) {
            $WHERE[] = "t1.man_location='{$options['man_location']}'";
        }
        if (isset($options['byMonth'])) {
            $WHERE[] = "YEAR(t1.dgt_com)={$options['byMonth']['year']}
            AND MONTH(t1.dgt_com)={$options['byMonth']['month']}";
        } elseif (isset($options['byPeriod'])) {
            $WHERE[] = "t1.dgt_com BETWEEN '{$options['byPeriod']['start']}' AND '{$options['byPeriod']['end']}'";
        } else {
            $WHERE[] = 'YEAR(t1.dgt_com)=YEAR(NOW()) AND MONTH(t1.dgt_com)=MONTH(NOW())';
        }

        if (isset($options['state']) && $options['state'] !== null) {
            $WHERE[] = "state = '{$options['state']}'";
        }

        if (!empty($WHERE)) {
            $WHERE = implode('AND ', $WHERE);
        }

        //4 => a unit with less than 4days late is on time
        $query = <<<EOF
SELECT
    t1.man_location,
    count(*) AS num_gt,
    count(DATEDIFF(t1.dgt_com, DATE_ADD(t2.ddel_est1, interval 4 day))<=0 or null) as num_ontime,
    ROUND(count(DATEDIFF(t1.dgt_com, DATE_ADD(t2.ddel_est1, interval 4 day))<=0 or null)*100/count(*)) as num_ontime_pc,
    ROUND(avg(
            CASE
            WHEN t1.dgt_com!='0000-00-00' THEN
                 if(DATEDIFF(t1.dgt_com,  t2.ddel_est1) > 4,
					DATEDIFF(t1.dgt_com, t2.ddel_est1),
                0)
            ELSE
                DATEDIFF(NOW(), t2.ddel_est1)
            END
        )
    ) as avg_days_late
FROM service AS t1
LEFT JOIN sor_units AS t2 ON t1.sor_uid = t2.id
WHERE t1.sn NOT LIKE 'P%' AND $WHERE
GROUP BY t1.man_location
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get stats for Ontime Delivery Performance for the past 12 Months
     *
     * @param integer $erp erp number or none for all factories
     */
    public static function statsOTDPPast12Months($options = '')
    {
        $WHERE = '';
        if (isset($options['past12Months'])) {
            $WHERE .= " PERIOD_DIFF(DATE_FORMAT(NOW(),'%Y%m'),DATE_FORMAT(t1.dgt_com,'%Y%m')) <= 12";
        } else {
            $WHERE .= ' YEAR(t1.dgt_com)=YEAR(NOW()) and MONTH(t1.dgt_com)=MONTH(NOW())';
        }
        if (isset($options['man_location'])) {
            $WHERE .= " AND t1.man_location='" . $options['man_location'] . "'";
        }
        if (isset($options['byMonth'])) {
            $WHERE .= ' AND YEAR(t1.dgt_com)=' . $options['byMonth']['year'] .
                ' AND MONTH(t1.dgt_com)=' . $options['byMonth']['month'];
        }

        $query = <<<EOF
select t1.dgt_com,
DATE_FORMAT(t1.dgt_com, '%Y-%m') AS otdp_date,
count(*) AS num_gt,
count(DATEDIFF(t1.dgt_com, t2.ddel_est1)<=0 or null) as num_ontime,
ROUND(count(DATEDIFF(t1.dgt_com, t2.ddel_est1)<=0 or null)*100/count(*)) as num_ontime_pc,
ROUND(
    avg(
        CASE
        WHEN t1.dgt_com!='0000-00-00' THEN
            if(DATEDIFF(t1.dgt_com, t2.ddel_est1) > 0,
            DATEDIFF(t1.dgt_com, t2.ddel_est1),
            0)
        ELSE
            DATEDIFF(NOW(), t2.ddel_est1)
        END
        )
    ) as avg_days_late
FROM service as t1, sor_units AS t2
WHERE t1.sor_uid=t2.id
    $WHERE
group by otdp_date
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function auditNoManualEstimatedGT2WeekByFactory($factory)
    {

        $query = <<<EOF
SELECT er.*, sol.id AS solid,
	IF(er.customer_id > 0,
	(SELECT customers.customer_name FROM customers WHERE customers.id=er.customer_id),
	customer_name) AS user_customer_display,
	(SELECT customers.customer_name FROM customers WHERE customers.id=er.buyer_customer_id) AS buyer_customer_display
FROM service AS er
LEFT JOIN esrl ON esrl.erid = er.id AND esrl.parent_id = er.esrid
LEFT JOIN sor_units ON sor_units.id=er.sor_uid
LEFT JOIN sor_lines AS sol ON sol.id=sor_units.parent_id
LEFT JOIN sor ON sor.id=sol.parent_id
LEFT JOIN locations AS sso ON sso.erp=sor.bu
WHERE er.date_shipped='0000-00-00' AND DATE_FORMAT(er.dgt_rev,'%Y%m%d') > DATE_ADD(now(), INTERVAL -1 MONTH) AND DATE_FORMAT(er.dgt_rev,'%Y%m%d') < DATE_FORMAT( date_add(now(), INTERVAL 14 day),'%Y%m%d') AND er.dgt_rev!='0000-00-00' AND er.man_location LIKE '$factory' AND er.status!=""
ORDER BY er.dgt_rev
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);
        $units = [];
        foreach ($rows as $row) {
            $eq = new tldEquipment($row['id']);
            $manuals = $eq->getManuals();
            if (!count($manuals)) {
                $units[] = $row;
            }
        }

        return $units;
    }

    /**
     * Get ODP RECIPIENTS
     *
     * @param bool $FLAG = odp modification (["SHIPDATE","ODP_NOTE","GT_EST","GT_ACT","YT",])
     * @param array $odp :   odp updated
     *
     *
     * @return array|string
     */
    public static function getRecipients($flag, $odp)
    {
        if (!is_array($flag) || empty($flag)) {
            return 'Flag is empty or invalid!';
        }
        if (!is_array($odp) || empty($odp)) {
            return 'Data empty or invalid!';
        }

        //groups to notify
        $groupsERP = [];
        $groupsSSO = [];

        $emailList = [];
        $sso = $odp['bu'];
        $erp = tldLocation::getERPByLocation($odp['man_location']);

        if ($sso !== null) {
            $groupsSSO = array_merge($groupsSSO, ['role_SA', 'role_TM']);
            if (isset($flag['SHIPDATE']) && $flag['SHIPDATE']) {
                $groupsShipDate = [
                    'role_SPM',
                    'role_CSM',
                    'role_CSA',
                ];
                $groupsSSO = array_merge($groupsSSO, $groupsShipDate);
            }
            if (isset($flag['ODP_NOTE']) && $flag['ODP_NOTE']) {
                $groupsODPNote = [
                    'role_PM',
                    'role_MLM',
                    'role_PSM',
                    'role_PSE',
                    'role_SPM',
                    'role_EM',
                    'role_planner',
                ];
                $groupsSSO = array_merge($groupsSSO, $groupsODPNote);
            }
            $emailList = array_merge($emailList, tldGroup::getUserListByMultipleGroup($groupsSSO, $sso));
        }

        if ($erp !== null) {
            if (isset($flag['GT_EST']) && $flag['GT_EST']) {
                $groupsERP = array_merge($groupsERP, ['gg_SUPPORT']);
            }
            if (isset($flag['GT_EST_MGT']) && $flag['GT_EST_MGT']) {
                $groupsMGT = [
                    'role_COO',
                    'role_SSD',
                    'role_GCOO',
                ];
                $groupsERP = array_merge($groupsERP, $groupsMGT);
            }
            if ((isset($flag['GT_ACT']) && $flag['GT_ACT']) || (isset($flag['YT']) && $flag['YT'])) {
                $groupsGTYT = [
                    'role_COO',
                    'role_QE',
                    'role_QAM',
                    'role_PSM',
                    'role_PSE',
                    'role_PSA',
                    'role_planner',
                    'role_PM',
                    'role_PS',
                    'role_MLM',
                    'role_FC',
                    'role_EM',
                    'role_WS',
                    'role_EINVOICING',
                    'role_planner',
                ];
                $groupsERP = array_merge($groupsERP, $groupsGTYT);
            }
            $emailList = array_merge($emailList, tldGroup::getUserListByMultipleGroup($groupsERP, $erp));
        }

        if ($odp['asm']) {
            $asm = new tldUser($odp['asm']);
            $emailList[] = ['email' => $asm->getEmail()];
        }

        global $kernel;
        if (null !== $kernel) {
            try {

                /** @var Client $client */
                $client = $kernel->getContainer()->get(Client::class);
                $order = $client->findOneBy('sales/orders', ['legacyId' => $odp['sor_id']]);
                $parts = explode('/', $order['buyer']['@id']);
                $orderId = array_pop($parts);
                $customer = $client->find('sales/customers', $orderId);
                if ($customer['mainSalesRepresentative']['asm']['email'] ?? null) {
                    $emailList[] = ['email' => $customer['mainSalesRepresentative']['asm']['email']];
                }
                foreach($customer['secondarySalesRepresentatives'] as $rep) {
                    if ($rep['asm']['email'] ?? null) {
                        $emailList[] = ['email' => $rep['asm']['email']];
                    }
                }
            } catch (Exception $e) {
                // do nothing
            }
        }


        return array_unique(array_column($emailList, 'email'));
    }

    public static function getStats($opts = '')
    {
        $c = [];
        $WHERE = $HAVING = '';
        if (isset($opts['asmid'])) {
            $c[] = ' t1.asm=' . $opts['asmid'];
        }
        if (isset($opts['sso'])) {
            $c[] = ' t1.bu=' . $opts['sso'];
        }
        if (isset($opts['cu_nama'])) {
            $c[] = " t1.cu_nama='" . $opts['cu_nama'] . "'";
        }
        if (empty($opts)) {
            $HAVING = <<<EOF
HAVING num_backlog > 0
OR num_just_gtd > 0
OR num_near_gt > 0
OR num_near_due > 0
OR num_late > 0
OR num_just_shipped > 0
OR num_gtns > 0
OR num_ytns > 0
EOF;
        }
        if ($c) {
            $WHERE = ' WHERE ' . implode(' AND ', $c);
        }
        $query = <<<EOF
SELECT
	t4.man_location,t4.id as esrid,
	COUNT((t4.dgt_act='0000-00-00' AND t1.id is not null) or null) as num_backlog,
	COUNT(DATEDIFF(NOW(), t4.dgt_act) between 0 and 14 or null) as num_just_gtd,
	COUNT(DATEDIFF(t4.dgt_rev, NOW()) between 0 AND 14 AND t4.dgt_act='0000-00-00' or null) as num_near_gt,
	COUNT(DATEDIFF(esrl.dt_pick_up, NOW()) between 0 AND 14 or null) as num_near_due,
	COUNT(DATEDIFF(NOW(), t3.ddel_est1) > 0 AND t4.dgt_act='0000-00-00' or null) as num_late,
	COUNT(DATEDIFF(NOW(), t4.date_shipped) between 0 and 14 or null) as num_just_shipped,
	COUNT((DATEDIFF(t4.dgt_act,t4.dyt)>0 OR DATEDIFF(t4.dgt_act,t4.dyt) IS NULL)
		AND t4.dgt_act!='0000-00-00' AND t4.date_shipped='0000-00-00' OR NULL) AS num_gtns,
	COUNT(t4.dyt >= t4.dgt_act AND t4.dyt!='0000-00-00' AND t4.date_shipped='0000-00-00' OR NULL) AS num_ytns,
    (SELECT COUNT(distinct id) FROM service s WHERE s.customer_name IN ('**AVAILABLE FOR SALE**', 'TLD EUROPE', 'TLD AMERICA', 'TLD ASIA') AND s.date_shipped='0000-00-00' AND t4.man_location=s.man_location) AS num_afs
FROM service as t4 LEFT JOIN sor_units as t3 ON t3.id=t4.sor_uid
LEFT JOIN esrl ON t4.id = esrl.erid AND t4.esrid = esrl.parent_id
LEFT JOIN sor_lines as t2 ON t2.id=t3.parent_id
LEFT JOIN sor as t1 ON t1.id=t2.parent_id
    $WHERE
GROUP BY man_location
    $HAVING
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getYellowTagEROpenCrabsCountByFactory(): array
    {
        $query = <<<EOF
SELECT 
       er.man_location, 
       count(*) AS crabs
FROM crabs  
    LEFT JOIN service AS er ON er.id = crabs.erid 
WHERE er.dgt_act < er.dyt AND er.dyt IS NOT NULL AND crabs.status != 'CLOSED'
GROUP BY er.man_location
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getYellowTagEROpenCrabsByFactory(string $factory): array
    {
        $query = <<<EOF
SELECT 
    er.man_location AS factory,
    er.sales_org AS sso,
    er.customer_name AS buyer,
    customers.customer_name AS user,
    er.model,
    er.sn,
    er.dgt_act AS actual_gt_date,
    er.dgt_com AS first_gt_date,
    er.dyt AS yellow_date,
    crabs.id AS crab_id,
    crabs.dsca AS crab_description
FROM crabs  
    LEFT JOIN service AS er ON er.id = crabs.erid 
    LEFT JOIN customers ON customers.id = er.customer_id
WHERE 
    er.dgt_act < er.dyt 
    AND er.dyt IS NOT NULL 
    AND crabs.status != 'CLOSED'
    AND er.man_location = '$factory'
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function kpi_otdpGTvsPromise_ByPeriodByFactory($from, $to, $factory)
    {
        $WHERE = '';
        if ($factory !== 'ALL') {
            $WHERE = "AND ER.man_location LIKE '$factory'";
        }
        $query = <<<EOF
SELECT
    ER.man_location AS factory,
    DATE_FORMAT(ER.dgt_com, '%Y-%m') AS month,
    ROUND(
        COUNT(DATEDIFF(ER.dgt_com, sor_units.ddel_est1)<=4 OR NULL)*100/COUNT(*)
    ) AS val
FROM
    service AS ER
    LEFT JOIN sor_units ON ER.sor_uid=sor_units.id
WHERE
    PERIOD_DIFF(DATE_FORMAT(ER.dgt_com,'%Y%m'),DATE_FORMAT('$from','%Y%m')) >= 0
    AND PERIOD_DIFF(DATE_FORMAT(ER.dgt_com,'%Y%m'),DATE_FORMAT('$to','%Y%m')) <= 0
    AND sor_units.id IS NOT NULL
    $WHERE   
GROUP BY
    factory, month
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function kpi_GTEndOfMonth_ByPeriodByFactory($dayNumber, $from, $to, $factory)
    {
        $WHERE = '';
        if ($factory !== 'ALL') {
            $WHERE = "AND ER.man_location LIKE '$factory'";
        }
        $query = <<<EOF
SELECT
    ER.man_location AS factory,
    DATE_FORMAT(ER.dgt_com, '%Y-%m') AS month,
    ROUND(
        (SELECT COUNT(*) FROM service AS t2
            WHERE YEAR(t2.dgt_com)=YEAR(ER.dgt_com)
            AND MONTH(t2.dgt_com)=MONTH(ER.dgt_com)
            AND t2.man_location=ER.man_location 
            AND DAY(t2.dgt_com)>DAY(DATE_ADD(LAST_DAY(t2.dgt_com), INTERVAL -'$dayNumber' DAY))
        )*100/COUNT(*)
    ) AS val
FROM
    service AS ER
WHERE
    PERIOD_DIFF(DATE_FORMAT(ER.dgt_com,'%Y%m'),DATE_FORMAT('$from','%Y%m')) >= 0
    AND PERIOD_DIFF(DATE_FORMAT(ER.dgt_com,'%Y%m'),DATE_FORMAT('$to','%Y%m')) <= 0
    $WHERE
GROUP BY
    factory, month
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function kpi_otdpAVGdaysLate_ByPeriodByFactory($from, $to, $factory)
    {
        $WHERE = '';
        if ($factory !== 'ALL') {
            $WHERE = "AND ER.man_location LIKE '$factory'";
        }
        $query = <<<EOF
SELECT
    ER.man_location AS factory,
    DATE_FORMAT(ER.dgt_com, '%Y-%m') AS month,
    ROUND(
        AVG(
            CASE WHEN ER.dgt_com!='0000-00-00' THEN
                IF(DATEDIFF(ER.dgt_com, sor_units.ddel_est1) > 0, DATEDIFF(ER.dgt_com, sor_units.ddel_est1), 0)
            ELSE
                DATEDIFF(NOW(), sor_units.ddel_est1)
            END
        )
    ) AS val
FROM
    service AS ER
    LEFT JOIN sor_units ON ER.sor_uid=sor_units.id
WHERE
    PERIOD_DIFF(DATE_FORMAT(ER.dgt_com,'%Y%m'),DATE_FORMAT('$from','%Y%m')) >= 0
    AND PERIOD_DIFF(DATE_FORMAT(ER.dgt_com,'%Y%m'),DATE_FORMAT('$to','%Y%m')) <= 0
    AND sor_units.id IS NOT NULL
    $WHERE
GROUP BY
    factory, month
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }
}

/**
 * Class for SB module
 *
 * @package Support
 */
class tldSB3
{

    public $itsID;
    public $itsHeader;
    const CSM_APPROVAL_REMINDER = 10;
    const CSM_APPROVAL_TIMER = 21;
    const SSD_DECISION_TIMER = 60;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    /*******************************************
     * GETTERS
     *******************************************/

    public function getID()
    {
        return $this->itsID;
    }

    public function getParentID()
    {
        return $this->itsHeader['parent_id'];
    }

    public function getPosterID()
    {
        return $this->itsHeader['poster_id'];
    }

    public function getFactory()
    {
        return $this->itsHeader['factory'];
    }

    public function getFactoryID()
    {
        return $this->itsHeader['bu_id'];
    }

    public function getFactoryERP()
    {
        return $this->itsHeader['factory_erp'];
    }

    public function getStatus()
    {
        return $this->itsHeader['status'];
    }

    public function getCategory()
    {
        return $this->itsHeader['category'];
    }

    public function getIFactor()
    {
        return $this->itsHeader['ifactor'];
    }

    /*******************************************
     * CRUD functions
     *******************************************/

    public static function getSELECT(): string
    {
        return <<<EOF
SELECT
    sb.*,
    factory.location AS factory,
    factory.erp AS factory_erp,
    CONCAT(poster.firstname, ' ', poster.lastname) AS poster_fullname
EOF;
    }

    public static function getFROM(): string
    {
        return <<<EOF
FROM
    sb
    LEFT JOIN locations AS factory ON factory.id=sb.bu_id
    LEFT JOIN people AS poster ON poster.id=sb.poster_id

EOF;
    }

    public function getHeader()
    {
        if (!$this->itsID || !is_numeric($this->itsID)) {
            return [];
        }

        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
WHERE sb.id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public static function byConstraints($a, $options = '')
    {
        if (empty($a)) {
            $a = '1=1';
        }
        $WHERE = is_array($a) ? 'WHERE ' . tldUtils::constructWhere($a) : "WHERE $a";
        $LIMIT = !empty($options['limit']) ? "LIMIT {$options['limit']}" : '';
        $ORDERBY = !empty($options['orderBy']) ? "ORDER BY {$options['orderBy']}" : 'ORDER BY id';

        // Construct query
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
$WHERE
$ORDERBY
$LIMIT
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function insert($a)
    {
        if (empty($a)) {
            return;
        }
        $fields = [
            'poster_id',
            'bu_id',
            'category',
            'category_reason',
            'type',
            'ifactor',
            'confidential',
            'title',
            'description',
            'labor',
            'nb_tech_needed',
            'parts_needed',
        ];
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO sb SET dt=NOW(), status='PENDING', $SET";

        return tldUtils::sqlInsert($query);
    }

    public function update($a, $fields = '')
    {
        if (empty($a)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "UPDATE sb SET $SET WHERE id=$this->itsID";

        return tldUtils::sqlQuery($query);
    }

    public function delete()
    {
        $query = "DELETE FROM sb WHERE id=$this->itsID LIMIT 1";

        return tldUtils::sqlExecute($query);
    }

    /*******************************************
     * COMMON MOD methods
     *******************************************/

    public function addLogEntry($uid, $comment, $num_log = 0)
    {
        return tldModLog::insert(
            [
                'parent_id' => $this->itsID,
                'module' => 'SB3',
                'poster' => $uid,
                'comment' => $comment,
                'log_num' => $num_log,
            ]
        );
    }

    public function addSignLogEntry($uid, $comment, $parentId,$num_log = 0)
    {
        return tldModLog::insert(
            [
                'parent_id' => $parentId,
                'module' => 'SB3SIGN',
                'poster' => $uid,
                'comment' => $comment,
                'log_num' => $num_log,
            ]
        );
    }

    public function getLateSsdSignature($MODE = '')
    {
        $dt_field = '';
        if (empty($MODE)) {
            return 'Mode is empty';
        }
        if ($MODE === 'CSM_APPROVAL') {
            $dt_field = 'sb.dt_ssd_approval';
        }
        if ($MODE === 'SSD_DECISION') {
            $dt_field = 'sb.dt_ssd_decision';
        }
        $query = <<<EOF
SELECT
	sb.id,
    sb.dt,
    CONCAT(poster.firstname, ' ', poster.lastname) AS poster_fullname,
    factory.location AS factory,
    sb.status,
    sb.category,
    sb.confidential,
    sb.ifactor,
    sb.title,
    DATEDIFF(NOW(),$dt_field) AS late_days
FROM sb
    LEFT JOIN sb_signature AS sgn ON sgn.parent_id=sb.id
    LEFT JOIN locations AS factory ON factory.id=sb.bu_id
    LEFT JOIN people AS poster ON poster.id=sb.poster_id
WHERE
    sgn.user_id=0 AND sb.status = '$MODE'
    AND DATEDIFF(NOW(),$dt_field)>7
GROUP BY sb.id
ORDER BY factory ASC, ifactor DESC, late_days DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function getLog()
    {
        return tldModLog::byParent($this->itsID, 'SB3');
    }

    public function addTask($a)
    {
        return tldTask::insert($this->itsID, $a, 'SB3');
    }

    public function getTasks()
    {
        return tldTask::byParent($this->itsID, 'SB3', 'ALL');
    }

    public function getTitle()
    {
        return $this->itsHeader['title'];
    }

    public function getShortDesc()
    {
        return $this->getTitle();
    }

    public function addFile($a, $file_array)
    {
        $a['parent_id'] = $this->itsID;
        $a['module'] = 'SB3';

        return tldModFile::insert($a, $file_array);
    }

    public function getFiles($lvl = 0)
    {
        return tldModFile::byParent($this->itsID, 'SB3', $lvl);
    }

    public function getCustomerFiles()
    {
        return $this->getFiles(0);
    }

    public function getTLDFiles()
    {
        return $this->getFiles(1);
    }

    public function addLinkTo($module, $parent_id)
    {
        return tldModLink::insert($module, $parent_id, 'SB3', $this->itsID);
    }

    public function addLinkFrom($type, $item)
    {
        return tldModLink::insert('SB3', $this->itsID, $type, $item);
    }

    public function getLinksFromHere($type = '')
    {
        return tldModLink::byParent($this->itsID, 'SB3', $type);
    }

    public function getLinksToHere($module = '')
    {
        return tldModLink::byItem($this->itsID, 'SB3', $module);
    }

    public function getParts()
    {
        return tldModParts::byParent($this->itsID, 'SB3');
    }

    /*******************************************
     *   STATIC and REFERENCE methods
     ********************************************/

    public static function getCategoryList()
    {
        return [
            'COMPULSORY' => 'COMPULSORY',
            'RECOMMENDED' => 'RECOMMENDED',
            'INFORMATION' => 'INFORMATION',
        ];
    }

    public static function getTypeList()
    {
        return [
            'OPERATION' => 'OPERATION',
            'IMPROVEMENT' => 'IMPROVEMENT',
            'MAINTENANCE' => 'MAINTENANCE',
        ];
    }

    public static function getIFactorList()
    {
        return [1 => 1, 10 => 10, 100 => 100, 1000 => 1000];
    }

    public static function getPartsAvailabilityChoicesList()
    {
        return ['Not enough stock' => 'Not enough stock', 'Enough stock' => 'Enough stock', 'No parts needed' => 'No parts needed'];
    }

    public static function getStatusList()
    {
        return [
            'PENDING' => 'PENDING',
            'CSM_APPROVAL' => 'CSM_APPROVAL',
            'SSD_DECISION' => 'SSD_DECISION',
            'PARTIAL_IMPLEMENTATION' => 'PARTIAL_IMPLEMENTATION',
            'IMPLEMENTATION' => 'IMPLEMENTATION',
            'CLOSED' => 'CLOSED',
            'CANCELLED' => 'CANCELLED',
        ];
    }

    public static function getOpenStatusList()
    {
        return ['PENDING', 'CSM_APPROVAL', 'SSD_DECISION', 'PARTIAL_IMPLEMENTATION', 'IMPLEMENTATION'];
    }

    public static function getClosedStatusList()
    {
        return ['CLOSED', 'CANCELLED'];
    }

    public function getDefaultPartDecision()
    {
        switch ($this->getCategory()) {
            case 'COMPULSORY':
                return 'A';
            case 'RECOMMENDED':
                if ($this->isConfidential()) {
                    return 'D';
                }

                return 'B';
            case 'INFORMATION':
                if ($this->isConfidential()) {
                    return 'D';
                }

                return 'C';
        }
    }

    public function getDefaultServiceDecision()
    {
        switch ($this->getCategory()) {
            case 'COMPULSORY':
                return '1';
            case 'RECOMMENDED':
                if ($this->isConfidential()) {
                    return '4';
                }

                return '3';
            case 'INFORMATION':
                return '4';
        }
    }

    public static function getAllowedSignatureStatuses($signatureStatus)
    {
        $status = [
            'CSM_APPROVAL' => ['CSM_APPROVAL'],
            'SSD_DECISION' => ['SSD_DECISION', 'PARTIAL_IMPLEMENTATION'],
        ];

        return array_key_exists($signatureStatus, $status) ? $status[$signatureStatus] : [];
    }

    /*******************************************
     * LOGIC and ACTION methods
     *******************************************/

    public function refresh()
    {
        $this->itsHeader = $this->getHeader();
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public function isConfidential()
    {
        return $this->itsHeader['confidential'] === 'Y';
    }

    public function isCompulsory()
    {
        return $this->itsHeader['category'] === 'COMPULSORY';
    }

    public function isPartsNeeded()
    {
        return $this->itsHeader['parts_needed'] === 'Y';
    }

    public function isPartsNotNeeded()
    {
        return $this->itsHeader['parts_needed'] === 'N';
    }

    public function isPendingStatus()
    {
        return $this->itsHeader['status'] === 'PENDING';
    }

    public function isSSDDecisionStatus()
    {
        return $this->itsHeader['status'] === 'SSD_DECISION';
    }

    public function isPartialImplementationStatus()
    {
        return $this->itsHeader['status'] === 'PARTIAL_IMPLEMENTATION';
    }

    public function isImplementationStatus()
    {
        return $this->itsHeader['status'] === 'IMPLEMENTATION';
    }

    public function isAnyImplementationStatus()
    {
        return in_array($this->itsHeader['status'], ['PARTIAL_IMPLEMENTATION', 'IMPLEMENTATION'], true);
    }

    public function isClosedStatus()
    {
        return in_array($this->itsHeader['status'], self::getClosedStatusList(), true);
    }

    public function isFactoryPartsAvailabilityOk()
    {
        return  'Not enough stock' !== $this->itsHeader['factory_part_availability_status'] || 'INFORMATION' === $this->itsHeader['category'];
    }

    public function getAllowedStatus($options = [])
    {
        switch ($this->itsHeader['status']) {
            case 'PENDING':
                // Check all ER are generated
                $erList = $this->getLinesByConstraints();
                if (!$erList) {
                    return 'ER list not generated from coverage';
                }
                // Check ER info
                if (!array_key_exists('unset_gt', $options) && $e = $this->checkAffectedERValidy()) {
                    $errorER = implode('<br>', $e);

                    return "Some affected ER have missing info:<br>$errorER";
                }

                // Else can go PSM approval
                return ['CSM_APPROVAL'];
            case 'CSM_APPROVAL':
                // Special Case for Rejecting back to PENDING
                if (!empty($options['reject'])) {
                    return ['PENDING'];
                }
                // Check if all SSO signed
                if (!$this->isFullySignedByStatus('CSM_APPROVAL')) {
                    return 'CSM_APPROVAL not fully signed by SSD';
                }

                return ['SSD_DECISION'];
            case 'SSD_DECISION':
                // Check if all SSO signed
                if ($this->isFullySignedByStatus('SSD_DECISION')) {
                    return ['IMPLEMENTATION'];
                }

                // Special Case for Rejecting back to PENDING
                if (!empty($options['reject'])) {
                    return ['PENDING'];
                }

                if (empty($options['sso']) || !$this->isSignedByStatusBySSOID('SSD_DECISION', $options['sso'])) {
                    return 'SSD_DECISION not signed by EVP';
                }

                return ['PARTIAL_IMPLEMENTATION'];
            case 'PARTIAL_IMPLEMENTATION':
                // Check if all SSO signed
                if (!$this->isFullySignedByStatus('SSD_DECISION')) {
                    return ['PARTIAL_IMPLEMENTATION'];
                }

                return ['IMPLEMENTATION'];
            case 'IMPLEMENTATION':
                // Check if all ER Done
                $erListNotDone = $this->getLinesByConstraints('1=1', ['where' => "sb_lines.status!='CLOSED'"]);
                if (count($erListNotDone)) {
                    return 'There is still some ER in implementation';
                }

                return ['CLOSED'];
            default:
                return 'No status change for ' . $this->getStatus();
        }
    }

    public function updateStatus($uid, $newStatus, $options = [])
    {
        $message = null;
        $allowedStatus = $this->getAllowedStatus($options);
        if (is_string($allowedStatus) || !in_array($newStatus, $allowedStatus, true)) {
            return "Status $newStatus not allowed";
        }
        $previousStatus = $this->getStatus();
        // Update the SB status
        $e = $this->update(['status' => $newStatus]);
        if (is_string($e)) {
            return $e;
        }
        // Log
        if (isset($options['comment'])) {
            $message = '<br>' . $options['comment'];
        }

        if ($newStatus !== $this->getStatus()) {
            $this->addLogEntry($uid, "$newStatus$message");
        }
        // POST actions
        switch ($newStatus) {
            case 'PENDING':
                if (!empty($options['reject'])) {
                    $this->resetSignatureList('CSM_APPROVAL');
                }
                // Notify PSM
                $this->notifyTLD(
                    array_merge($this->getPSERecipients(), $this->getPSMRecipients()),
                    'noreply@tld-gse.com',
                    "SB#$this->itsID {$this->itsHeader['factory']} $newStatus",
                    "SB#$this->itsID was rejected and is now back in $newStatus status.<br>Reason:$message<br>Please review and take action accordingly.<br>"
                );
                break;
            case 'CSM_APPROVAL':
                // Prepare signature
                $this->generateSignatureList($newStatus);
                // Record date
                $this->update(['dt_ssd_approval' => date('Y-m-d H:i:s')]);
                // Notify EVP
                $this->notifyTLD(
                    $this->getCSMRecipients(),
                    'noreply@tld-gse.com',
                    "SB#$this->itsID {$this->itsHeader['factory']} $newStatus",
                    "SB#$this->itsID is now in $newStatus status.<br>Please approve this SB for your SSO following TLD SB Procedure",
                    $this->getPSMRecipients()
                );
                $isFactoryPartsAvailabilityOk = $this->isFactoryPartsAvailabilityOk();
                $this->addLogEntry($uid, sprintf('Parts availability was %s', $isFactoryPartsAvailabilityOk ? 'OK' : 'KO'));
                if (!$isFactoryPartsAvailabilityOk) {
                    $this->addLogEntry($uid, 'Generating Task');
                    $this->generatePartsAvailabilityTask($uid, $options['parts_availability_estimated_date'] ?? null);
                }
                break;
            case 'SSD_DECISION':
                // Prepare signature
                $this->generateSignatureList($newStatus);
                // Record date
                $this->update(['dt_ssd_decision' => date('Y-m-d H:i:s')]);
                // Create Task to SSD
                // $this->generateDecisionTasks();
                // Notify SSD
                $this->notifyTLD(
                    $this->getEVPRecipients(),
                    'noreply@tld-gse.com',
                    "SB#$this->itsID {$this->itsHeader['factory']} $newStatus",
                    "SB#$this->itsID is now in $newStatus status.<br>Please make a decision for affected units for your SSO following TLD SB Procedure",
                    $this->getPSMRecipients()
                );
                break;
            case 'PARTIAL_IMPLEMENTATION':
                // Notify Service Administration
                $this->notifyTLD(
                    $this->getServiceAdminRecipients($options['sso']),
                    'noreply@tld-gse.com',
                    "SB#$this->itsID {$this->itsHeader['factory']} $newStatus",
                    "SB#$this->itsID is now in $newStatus status.<br>Please implement this SB for your SSO following TLD SB Procedure",
                    $this->getPSMRecipients()
                );

                // Set Initial ISI status
                $lines = $this->getLinesByConstraints('1=1', ['where' => "sso.id={$options['sso']}"]);
                foreach ($lines as $k => $vals) {
                    $line = new tldSB_Line($vals['id']);
                    $line->updateStatus(0);
                }
                break;
            case 'IMPLEMENTATION':
                // Record date
                $this->update(['dt_implementation' => date('Y-m-d H:i:s')]);

                // Set Initial ISI status for all line not done already
                $lines = $this->getLinesByConstraints('1=1', ['where' => "sb_lines.status = ''"]);
                $impactedSso = [];
                foreach ($lines as $k => $vals) {
                    $line = new tldSB_Line($vals['id']);
                    if (!in_array($line->getSSOID(), $impactedSso, true)) {
                        $impactedSso[] = $line->getSSOID();
                    }
                    $line->updateStatus(0);
                }
                // This hack is to allow a smooth transition between the old version and the new version
                // where we want to make sure that if some lines were not initialized, they will be, and the Service admin will be notified
                // At some point this foreach should be dropped and will avoid PSM to be notified several times
                // Notify Service Administration
                foreach ($impactedSso as $salesOrg) {
                    $emailResp = $this->notifyTLD(
                        $this->getServiceAdminRecipients($salesOrg),
                        'noreply@tld-gse.com',
                        "SB#$this->itsID {$this->itsHeader['factory']} $newStatus",
                        "SB#$this->itsID is now in $newStatus status.<br>Please implement this SB for your SSO following TLD SB Procedure",
                        $this->getPSMRecipients()
                    );
                }
                break;
            case 'CLOSED':
                $emailResp = $this->notifyTLD(
                    $this->getPSMRecipients(),
                    'noreply@tld-gse.com',
                    "SB#$this->itsID {$this->itsHeader['factory']} $newStatus",
                    "SB#$this->itsID is now $newStatus."
                );
                break;
        }

        return $e;
    }

    public function getCoverageList()
    {
        return tldSB_Coverage::byParentID($this->itsID);
    }

    public function addCoverage($a)
    {
        $a['parent_id'] = $this->itsID;

        return tldSB_Coverage::insert($a);
    }

    public function getERListFromCoverage()
    {
        // Get coverage
        $coverageList = $this->getCoverageList();
        if (empty($coverageList)) {
            return 'No coverage setup';
        }
        // Generate query constraints to get list of ER
        $constraints = [];
        $coverageFields = ['model', 'sn_from', 'sn_to', 'sn_list'];
        foreach ($coverageList as $coverageVars) {
            $lineConstraint = [];
            foreach ($coverageFields as $coverageField) {
                if (empty($coverageVars[$coverageField])) {
                    continue;
                }
                switch ($coverageField) {
                    case 'model':
                        $val = trim($coverageVars[$coverageField]);
                        $lineConstraint[] = " model LIKE '$val' ";
                        break;
                    case 'sn_from':
                        $from = trim($coverageVars['sn_from']);
                        $to = trim($coverageVars['sn_to']);
                        // SN are stored as VARCHAR (letter prefix + number), so a plain
                        // "sn BETWEEN '$from' AND '$to'" compares them lexically. That breaks
                        // as soon as the numeric part changes width (e.g. T99999 -> T100000):
                        // ranges crossing that boundary return nothing. Match the non-digit
                        // prefix as text and compare the numeric part as a number instead.
                        $lineConstraint[] = "
                            REGEXP_REPLACE(sn, '[0-9]+$', '') = REGEXP_REPLACE('$from', '[0-9]+$', '')
                            AND CAST(REGEXP_REPLACE(sn, '^[^0-9]+', '') AS UNSIGNED)
                                BETWEEN CAST(REGEXP_REPLACE('$from', '^[^0-9]+', '') AS UNSIGNED)
                                    AND CAST(REGEXP_REPLACE('$to', '^[^0-9]+', '') AS UNSIGNED)
                        ";
                        break;
                    case 'sn_list':
                        $val = trim($coverageVars[$coverageField]);
                        // Expect spaces or return of line to separate different SN
                        $snList = preg_split("/([\t]|[\n]|[\r]|[\s])+/", $val);
                        $lineConstraint[] = " sn IN('" . implode("','", $snList) . "') ";
                        break;
                }
            }
            $lineConstraint = implode(' AND ', $lineConstraint);
            $constraints[] = "($lineConstraint)";
        }
        $WHERE = implode(' OR ', $constraints);
        // Exclude bad SN & ER PAS
        $WHERE .= " AND TRIM(sn) NOT LIKE '' AND sn NOT REGEXP '^P[0-9]+$'";

        return tldEquipment::byConstraints($WHERE, ['groupBy' => 'service.id']);
    }

    public function generateLines($uid = '', $options = [])
    {
        $erList = $this->getERListFromCoverage();
        global $kernel;
        try {
            $client = $kernel->getContainer()->get(Client::class);
            $container = $kernel->getContainer();
            $router = $container->get('router');
        } catch (Exception $e) {
            $DEFAULT_ERROR[] = 'ERROR: Could not get API client. Reason: ' . $e->getMessage();
            return;
        }

        foreach ($erList as $er) {
            // Open CRAB if unit not shipped - Code 303 CBOM Update
            if ($er['date_shipped'] === '0000-00-00' && in_array($er['sn'], $options, true)) {
                $er = new tldEquipment($er['id']);

                try {
                    $equipmentRecord = $client->findOneBy('/equipment_records', ['legacyId' => $er->itsId]);
                } catch (ClientException $exception) {
                    $DEFAULT_ERROR[] = "_(ERROR: Failed to find equipment record)";
                }

                try {
                    $crabCode = $client->findOneBy('/quality/crab_codes', ['code' => '19']);
                } catch (ClientException $exception) {
                    $DEFAULT_ERROR[] = "_(ERROR: Failed to find CRAB code)";
                }

                try {
                    $department = $client->findOneBy('/quality/crab_departments', ['name' => 'Engineering']);
                } catch (ClientException $exception) {
                    $DEFAULT_ERROR[] = "_(ERROR: Failed to find department)";
                }

                $data = [
                    'equipmentRecord' => $equipmentRecord['@id'],
                    'code' => $crabCode['@id'],
                    'category' => 'Test',
                    'description' => mb_convert_encoding(
                        sprintf("SB#%s - %s", $this->getID(), $this->getTitle()),
                        'UTF-8',
                        'ASCII,ISO-8859-1,CP936,UTF-8'
                    ),
                    'department' => $department['@id'],
                ];

                $newCrab = null;

                try {
                    $newCrab = $client->save('/quality/crabs', $data);
                } catch (ClientException $exception) {
                    error_log("ERROR: CRAB for ER#{$er->getID()} not created");
                }

                if ($newCrab === null) {
                    continue;
                }

                $dataLog = [
                    'message' => sprintf("Created from SB#%s", $this->getID()),
                    'resource' => $newCrab['@id']
                ];

                try {
                    $log = $client->save('/comments', $dataLog);
                } catch (ClientException $exception) {
                    error_log("ERROR: Log for CRAB#{$newCrab['id']} not created");
                }
            } else {
                // Only link to SB if shipped
                $this->addLine($er['id']);
            }
        }

        return;
    }

    public function addLine($erid)
    {
        $existing = tldUtils::getSqlRowToAssocArray(
            "SELECT id FROM sb_lines WHERE parent_id={$this->itsID} AND er_id=$erid LIMIT 1"
        );
        if (!empty($existing)) {
            return;
        }

        return tldSB_Line::insert(
            [
                'parent_id' => $this->itsID,
                'er_id' => $erid,
            ]
        );
    }

    public function resetLines()
    {
        return tldSB_Line::deleteByParent($this->itsID);
    }

    /**
     * Check validity of affected ER
     *
     * @return array
     */
    public function checkAffectedERValidy()
    {
        $erList = $this->getLinesByConstraints();
        $issues = [];
        foreach ($erList as $erVal) {
            $er = new tldEquipment($erVal['er_id']);
            // Check if ER PASS
            if ($er->isPAS()) {
                $issues[] = "ER#{$er->getID()} SN#{$er->getSN()} is a PRE-ASSEMBLY";
            }
            // Check if GT
            if (!$er->isGT()) {
                $issues[] = "ER#{$er->getID()} SN#{$er->getSN()} is not GT";
            }
            // Check customer linked
            if (!$er->isCustomerFullyAffected()) {
                $issues[] = "ER#{$er->getID()} SN#{$er->getSN()} not fully affected to a customer";
            }
            // Check SSO
            $sso = $er->getSSO();
            if (empty($sso)) {
                $issues[] = "ER#{$er->getID()} SN#{$er->getSN()} not linked to a SSO";
            }
        }

        return $issues;
    }

    public static function getVisibilityConstraintsByCustomerID($cuid)
    {
        // Based on ER CUSTOMER BUYER, USER AND MAINTAINER
        $constraints = <<<EOF
(($cuid IN(
    SELECT er.buyer_customer_id
    FROM service AS er LEFT JOIN sb_lines ON sb_lines.er_id=er.id
    WHERE sb_lines.parent_id=sb.id
)
AND sb.status IN('PARTIAL_IMPLEMENTATION', 'IMPLEMENTATION','CLOSED')
AND sb.confidential!='Y') OR
($cuid IN(
    SELECT er.customer_id
    FROM service AS er LEFT JOIN sb_lines ON sb_lines.er_id=er.id
    WHERE sb_lines.parent_id=sb.id
)
AND sb.status IN('PARTIAL_IMPLEMENTATION', 'IMPLEMENTATION','CLOSED')
AND sb.confidential!='Y') OR
($cuid IN(
    SELECT er.maintainer_customer_id
    FROM service AS er LEFT JOIN sb_lines ON sb_lines.er_id=er.id
    WHERE sb_lines.parent_id=sb.id
)
AND sb.status IN('PARTIAL_IMPLEMENTATION', 'IMPLEMENTATION','CLOSED')
AND sb.confidential!='Y')
AND sb.id IN (
    SELECT DISTINCT sb.id
    FROM sb
    INNER JOIN sb_lines
        ON sb_lines.parent_id = sb.id
    WHERE sb_lines.status IN ('CUSTOMER_TO_DECIDE', 'TLD_TO_SHIP', 'TLD_TO_IMPLEMENT')
    )
)
EOF;

        // Return constraints
        return $constraints;
    }

    public function getLinesByConstraints($a = '1=1', $options = null)
    {
        if (empty($a)) {
            $a = '1=1';
        }
        $constraints = is_array($a) ? tldUtils::constructWhere($a) : $a;
        // optimisation for SB line query
        $whereOptions = " sb_lines.parent_id=$this->itsID ";
        if (!empty($options['where'])) {
            $options['where'] .= " AND $whereOptions";
        } else {
            $options['where'] = $whereOptions;
        }

        return tldSB_Line::byConstraints($constraints, $options);
    }

    public function isFullySelected($ssoid = 'ALL')
    {
        $a = <<<EOF
sb_lines.parent_id=$this->itsID
AND (sb_lines.part_decision LIKE '' OR sb_lines.service_decision LIKE '')
EOF;
        if ($ssoid !== 'ALL') {
            $a .= " AND sso.id=$ssoid ";
        }
        $rows = $this->getLinesByConstraints('1=1', ['where' => $a]);

        return !count($rows);
    }

    public function generateSignatureList($status)
    {
        // check if there is already signature for this status
        $this->resetSignatureList($status);
        // get list of sso impact
        $ssoList = $this->getImpactedSSOByConstraints();
        // Generate list of signature
        foreach ($ssoList as $ssoid => $name) {
            tldSB_Signature::insert(
                [
                    'parent_id' => $this->itsID,
                    'sso_id' => $ssoid,
                    'status' => $status,
                ]
            );
        }
    }

    public function resetSignatureList($status)
    {
        $actualSignatures = $this->getSignatureListByStatus($status);
        if (count($actualSignatures)) {
            tldSB_Signature::deleteByParentByStatus($this->itsID, $status);
        }
    }

    public function getSignatureListByStatus($status)
    {
        return tldSB_Signature::byConstraints(
            [
                'parent_id' => $this->itsID,
                'status' => $status,
            ]
        );
    }

    public function isFullySignedByStatus($status)
    {
        $rows = tldSB_Signature::byConstraints(
            [
                'parent_id' => $this->itsID,
                'status' => $status,
                'user_id' => 0,
            ]
        );

        return !count($rows);
    }

    public function isSignedByStatusBySSOID($status, $ssoid)
    {
        $rows = tldSB_Signature::byConstraints(
            [
                'parent_id' => $this->itsID,
                'status' => $status,
                'user_id' => 0,
                'sso_id' => $ssoid,
            ]
        );

        return !$rows;
    }

    /**
     * @deprecated no used anymore, was asked to be removed in TTS797580
     * https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=797850
     */
    public function generateDecisionTasks()
    {
        global $INTRANET_URL;
        // Task details
        $taskEscalationTrigger = 45;
        $urlDecision = "$INTRANET_URL/product_support/index.ps.php?m[0]=sb&m[1]=view&m[2]=summary&m[3]=selection&id={$this->itsID}";
        $urlSignature = "$INTRANET_URL/product_support/index.ps.php?m[0]=sb&m[1]=view&m[2]=signature&id={$this->itsID}";
        $taskDescription = <<<body
SB#{$this->itsID} is now SSD_DECISION

Please <a href="$urlDecision">click here to make your decision</a> for ER impacted in your SSO
Then <a href="$urlSignature">sign here for your SSO</a>
body;
        // Get All SSD concerned
        $ssdList = $this->getImpactedSSD();
        // Create Task for each SSD
        foreach ($ssdList as $ssd) {
            // Create task
            $e = $this->addTask(
                [
                    'assignee' => $ssd['id'],
                    'assignor' => $ssd['id'],
                    'task' => $taskDescription,
                    'escalation_trigger' => $taskEscalationTrigger,
                ]
            );
            // if ok notify
            if (!is_string($e)) {
                $task = new tldTask($e);
                $message = <<<EOF
                New SB in SSD_DECISION, please follow task description<br>
                <a href="https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$e">
                    Click here to go to Task.
            </a><br>
EOF;
                $task->notifyAssignee(
                    $message,
                    "Task#$e created for SB#{$this->itsID} and require your attention"
                );
            }
        }

        return;
    }

    public function generatePartsAvailabilityTask($uid, $dueDate = null)
    {
        if ('INFORMATION' === $category = $this->getCategory()) {
            return;
        }

        $taskEscalationTrigger = 45;
        if ('COMPULSORY' === $category) {
            $taskEscalationTrigger = 7;
        } elseif ('RECOMMENDED' === $category) {
            $taskEscalationTrigger = 15;
        }
        $data = [
            'assignee' => $uid,
            'assignor' => $uid,
            'task' => "SB#{$this->itsID} is waiting parts availability confirmation before IMPLEMENTATION.",
            'escalation_trigger' => $taskEscalationTrigger,
        ];

        if ($dueDate) {
            ['year' => $year, 'month' => $month, 'day' => $day] = date_parse_from_format('Y-m-d', $dueDate);
            if ($year && $month && $day) {
                $data['due_date'] = ['Y' => $year, 'm' => $month, 'd' => $day];
                $data['task'] = "SB#{$this->itsID} is waiting parts availability confirmation before IMPLEMENTATION and parts are expected by $dueDate.";
            }
        }

        $e = $this->addTask($data);
        if (!is_string($e)) {
            $task = new tldTask($e);
            $message = <<<EOF
                SB#{$this->itsID} is waiting parts availability confirmation before IMPLEMENTATION.<br>
                
                <a href="https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$e">
                    Click here to go to Task.
            </a><br>
EOF;
            $task->notifyAssignee(
                $message,
                "Task#$e created for SB#{$this->itsID} and require your attention",
                $this->getCSMRecipients()
            );
        }
    }

    public function duplicate($uid)
    {
        // Create new SB
        $a = $this->getHeader();
        $a['poster_id'] = $uid;
        $e = self::insert($a);
        if (is_string($e)) {
            return $e;
        }
        $sb = new tldSB3($e);
        // Copy Files
        foreach ($this->getCustomerFiles() as $file) {
            $file_array = [
                'tmp_name' => $file['filepath'],
                'name' => $file['filename'],
            ];
            $sb->addFile(tldUtils::cleanupFormInput($file), $file_array);
        }
        foreach ($this->getTLDFiles() as $file) {
            $file_array = [
                'tmp_name' => $file['filepath'],
                'name' => $file['filename'],
            ];
            $sb->addFile(tldUtils::cleanupFormInput($file), $file_array);
        }
        // Copy Links
        foreach ($this->getLinksFromHere() as $link) {
            $sb->addLinkFrom($link['type'], $link['item']);
        }
        foreach ($this->getLinksToHere() as $link) {
            $sb->addLinkTo($link['module'], $link['parent_id']);
        }
        // Copy Parts
        foreach ($this->getParts() as $part) {
            $part['parent_id'] = $e;
            tldModParts::insert(tldUtils::cleanupFormInput($part));
        }
        // Log
        $sb->addLogEntry($uid, "Created from duplication of SB#$this->itsID");

        // Return new SB ID
        return $e;
    }

    /**
     * Generic method to send email notification
     *
     * @param string $to
     * @param string $from
     * @param string $subject
     * @param string $body
     * @param string $file
     * @param string $cc
     * @param array $opt
     */
    public static function sendEmail($to, $from, $subject, $body, $file = '', $cc = '', $bcc = '', $opt = [])
    {
        return tldUtils::emailAttachment(
            $to,
            $from,
            $subject,
            $body,
            $file,
            $cc,
            $bcc,
            $opt
        );
    }

    public function notifyTLD($to, $from, $subject, $body, $cc = '', $options = [])
    {
        $body .= <<<EOF
<p><a href="https://www.tld-gse.com/en/private/product_support/index.ps.php?m[0]=sb&m[1]=view&id=$this->itsID">Click here to view SB#$this->itsID</a></p>
EOF;
        if (!array_key_exists('noPrintedVersion', $options) || $options['noPrintedVersion'] !== true) {
            $body .= $this->getPrintVersion();
        }

        return self::sendEmail($to, $from, $subject, $body, '', $cc);
    }

    public function notifyTeamCSM()
    {
        $to = $this->getCSMRecipients();

        $subordinates = [];
        foreach ($this->getImpactedCSM() as $csm) {
            $manager = new tldUser($csm['id']);
            $subordinates[] = $manager->getSubordinates();
        }

        $cc = array_unique(array_column(array_merge(...$subordinates), 'email'));
        $from = 'noreply@tld-gse.com';
        $subject = sprintf('SB %s fully APPROVED by SSO', $this->getId());
        $body = 'This SB was approved by all SSO. It is now ready for NOTIFICATION.';
        return $this->notifyTLD($to, $from, $subject, $body, $cc);
    }

    public function getAffectedERPList($ssoId = null)
    {
        $erpList = [];
        $a = '1=1';
        if (!empty($ssoId) && is_numeric($ssoId)) {
            $a = "sso.id = $ssoId";
        }
        $ssoList = $this->getImpactedSSOByConstraints($a);
        foreach ($ssoList as $ssoID => $ssoName) {
            $sso = new tldLocation($ssoID);
            $erpList[] = $sso->getERP();
        }

        return array_unique($erpList);
    }

    public function getPSMRecipients()
    {
        $grp = new tldGroup('role_PSM', $this->getFactoryERP());

        return $grp->getEmailList();
    }

    public function getPSERecipients()
    {
        $grp = new tldGroup('role_PSE', $this->getFactoryERP());

        return $grp->getEmailList();
    }

    public function getImpactedSSD($sso = null)
    {
        $evpList = [];
        $erpList = $this->getAffectedERPList($sso);
        foreach ($erpList as $erp) {
            $evpGrp = new tldGroup('role_EVP', $erp);
            $evpList[] = $evpGrp->getUserlist();
        }

        return array_merge(...$evpList);
    }

    public function getImpactedCSM($ssoId = null)
    {
        $csmList = [];
        $erpList = $this->getAffectedERPList($ssoId);
        foreach ($erpList as $erp) {
            $csmGrp = new tldGroup('role_CSM', $erp);
            $csmList[] = $csmGrp->getUserlist();
        }

        return array_merge(...$csmList);
    }

    public function getEVPRecipients()
    {
        return array_unique(array_column($this->getImpactedSSD(), 'email'));
    }

    public function getCSMRecipients()
    {
        return array_unique(array_column($this->getImpactedCSM(), 'email'));
    }

    public function getServiceAdminRecipients($ssoId = null)
    {
        $csmList = [];
        $erpList = $this->getAffectedERPList($ssoId);
        foreach ($erpList as $erp) {
            $csmGrp = new tldGroup('role_CSM', $erp);
            $csmList[] = $csmGrp->getEmailList();
            $csaGrp = new tldGroup('role_CSA', $erp);
            $csmList[] = $csaGrp->getEmailList();
        }

        return array_unique(array_merge(...$csmList));
    }

    // Days left before the pending signature is auto-signed by checkCSM_APPROVAL()/checkSSD_DECISION(); null when the status has no auto-sign timer.
    public static function getDaysUntilAutosign(array $row): ?int
    {
        if ($row['status'] === 'CSM_APPROVAL') {
            $dt = $row['dt_ssd_approval'];
            $timer = self::CSM_APPROVAL_TIMER;
        } elseif ($row['status'] === 'SSD_DECISION') {
            $dt = $row['dt_ssd_decision'];
            $timer = self::SSD_DECISION_TIMER;
        } else {
            return null;
        }
        $ts = strtotime($dt);
        if ($ts === false) {
            return null;
        }

        return max(0, $timer - (int) floor((strtotime('today') - $ts) / 86400));
    }

    public static function checkCSM_APPROVAL()
    {
        error_log('SB - BEGIN of CSM_APPROVAL check script');

        $nbDaysTrigger = self::CSM_APPROVAL_TIMER;
        $nbDaysReminder = self::CSM_APPROVAL_REMINDER;

        // 1 - Get SB data to process ------------>
        $query = <<<EOF
SELECT
    sb.*,
    (SELECT location FROM locations
        WHERE locations.id=sb.bu_id
    ) AS factory,
    sso.id AS sso_id,
    sso.erp AS sso_erp,
    DATE_FORMAT(sb.dt_ssd_approval,'%Y-%m-%d') AS dt_ssd_approval,
    DATEDIFF(NOW(),sb.dt_ssd_approval) AS diff_in_days,
    DATE_FORMAT(
        DATE_ADD(sb.dt_ssd_approval,INTERVAL $nbDaysTrigger DAY),
        '%Y-%m-%d'
    ) AS dt_expired
FROM sb
    LEFT JOIN sb_signature ON sb_signature.parent_id=sb.id
    LEFT JOIN locations AS sso ON sso.id = sb_signature.sso_id
WHERE
    sb.status LIKE 'CSM_APPROVAL'
    AND sb_signature.status LIKE 'CSM_APPROVAL'
    AND sb_signature.user_id=0
    AND DATEDIFF(NOW(),sb.dt_ssd_approval) IN($nbDaysReminder,$nbDaysTrigger)
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);
        if (!$rows) {
            return 'No SB found';
        }
        // Split results
        $sbBySSONotSignedReminder = [];
        $sbNotSignedExpired = [];
        foreach ($rows as $row) {
            // Reminder
            if ((int)$row['diff_in_days'] === $nbDaysReminder) {
                $sbBySSONotSignedReminder[$row['sso_id']][] = $row;
            }
            // Expired (group by SB)
            if ((int)$row['diff_in_days'] === $nbDaysTrigger) {
                $sbNotSignedExpired[$row['id']] = $row;
            }
        }

        // 2 - Notify and remind CSM to sign SB ------------>
        foreach ($sbBySSONotSignedReminder as $ssoid => $sbList) {
            // Prepare email
            // -- get list of concerned SB
            $report = new tldReportColumnar(
                $sbList,
                [
                    'xItems' => [
                        'id' => 'SB#',
                        'factory' => 'Factory',
                        'status' => 'Status',
                        'category' => 'Category',
                        'confidential' => 'Confidential?',
                        'dt_ssd_approval' => 'CSM_APPROVAL Date',
                        'dt_expired' => 'CSM_APPROVAL Expiration date',
                        'title' => 'Title',
                    ],
                    'title' => 'SB waiting for CSM signatures',
                    'sortable' => 'no',
                    'links' => [
                        'id' => 'https://www.tld-gse.com/en/private/product_support/index.ps.php?m[0]=sb&m[1]=view&id=',
                    ],
                ]
            );
            $sso = new tldLocation($ssoid);
            $csmGrp = new tldGroup('role_CSM', $sso->getERP());
            $to = $csmGrp->getEmailList();
            // -- email contents
            $subject = "SB reminder for CSM_APPROVAL status for {$sso->getShortName()}";
            $email_body = <<<EOF
<p>Dear Customer Service Manager,</p>
<p>This notification has been sent to reminds you that below SB(s) are waiting for your approval.</p>
<p><a href="https://www.tld-gse.com/en/private/product_support/index.ps.php?m[0]=sb">Click here to access SB module</a></p>
{$report->fetch()}
EOF;
            // Send email
            self::sendEmail($to, 'noreply@tld-gse.com', $subject, $email_body);
        }

        // 3 - Auto sign expired SB ------------>
        foreach ($sbNotSignedExpired as $sbid => $header) {
            $sb = new tldSB3($sbid);
            // log expiration
            $sb->addLogEntry(0, 'CSM_APPROVAL expired');
            // Check all missing signatures
            $signaturesNotSigned = tldSB_Signature::byConstraints(
                [
                    'parent_id' => $sbid,
                    'user_id' => 0,
                ]
            );
            // Sign them
            foreach ($signaturesNotSigned as $signatureHeader) {
                $signature = new tldSB_Signature($signatureHeader['id']);
                $e = $signature->update(
                    [
                        'user_id' => 1826, // System user
                        'dt' => date('Y-m-d H:i:s'),
                    ]
                );
                if (is_string($e)) {
                    error_log($e);
                }

                $to = [];
                $sso = new tldLocation($signature->getSSOID());
                $csmGrp = new tldGroup('role_CSM', $sso->getERP());
                $to[] = $csmGrp->getEmailList();
                $ssdGrp = new tldGroup('role_EVP', $sso->getERP());
                $to[] = $ssdGrp->getEmailList();
                $csdGrp = new tldGroup('role_CSD');
                $to[] = $csdGrp->getEmailList();
                // -- email contents
                $subject = "SB#$sbid CSM_APPROVAL was auto-signed for {$sso->getShortName()}";
                $email_body = <<<EOF
<p><a href="https://www.tld-gse.com/en/private/product_support/index.ps.php?m[0]=sb&m[1]=view&id=$sbid">Click here to access SB module (SB#$sbid)</a></p>
EOF;
                // Send email
                if ($to) {
                    self::sendEmail(array_merge(...$to), 'noreply@tld-gse.com', $subject, $email_body);
                }
            }
            // Change SB status automatically
            $newStatus = end($sb->getAllowedStatus());
            $e = $sb->updateStatus(0, $newStatus);
            if (is_string($e)) {
                error_log($e);
            }
        }
        error_log('SB - END of CSM_APPROVAL check script');

        return 'CSM_APPROVAL script completed';
    }

    public static function checkSSD_DECISION()
    {
        error_log('SB - BEGIN of SSD_DECISION check script');

        $nbDaysTrigger = self::SSD_DECISION_TIMER;

        $query = <<<EOF
SELECT
    sb.*,
    (SELECT location FROM locations
        WHERE locations.id=sb.bu_id
    ) AS factory,
    sso.id AS sso_id,
    sso.erp AS sso_erp,
    DATE_FORMAT(sb.dt_ssd_decision,'%Y-%m-%d') AS dt_ssd_decision,
    DATEDIFF(NOW(),sb.dt_ssd_decision) AS diff_in_days,
    DATE_FORMAT(
        DATE_ADD(sb.dt_ssd_decision,INTERVAL $nbDaysTrigger DAY),
        '%Y-%m-%d'
    ) AS dt_expired
FROM sb
    LEFT JOIN sb_signature ON sb_signature.parent_id=sb.id
    LEFT JOIN locations AS sso ON sso.id = sb_signature.sso_id
WHERE
    (sb.status LIKE 'SSD_DECISION')
    AND sb_signature.status LIKE 'SSD_DECISION'
    AND sb_signature.user_id=0
    AND DATEDIFF(NOW(),sb.dt_ssd_decision) >= $nbDaysTrigger
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);
        if (!$rows) {
            return 'No SB found';
        }

        // Auto sign expired SB ------------>
        foreach ($rows as $header) {
            $sb = new tldSB3($header['id']);
            // log expiration
            $sb->addLogEntry(0, 'SSD_DECISION expired');
            // Check all missing signatures
            $signaturesNotSigned = tldSB_Signature::byConstraints(
                [
                    'parent_id' => $sb->getID(),
                    'user_id' => 0,
                ]
            );
            foreach ($signaturesNotSigned as $signatureHeader) {
                $signature = new tldSB_Signature($signatureHeader['id']);
                $e = $signature->update(
                    [
                        'user_id' => 1826, // System user
                        'dt' => date('Y-m-d H:i:s'),
                    ]
                );
                if (is_string($e)) {
                    error_log($e);
                }

                $to = [];
                $sso = new tldLocation($signature->getSSOID());
                $ssdGrp = new tldGroup('role_EVP', $sso->getERP());
                $to[] = $ssdGrp->getEmailList();
                $csdGrp = new tldGroup('role_CSD');
                $to[] = $csdGrp->getEmailList();
                $csmGrp = new tldGroup('role_CSM', $sso->getERP());
                $to[] = $csmGrp->getEmailList();
                $subject = "SB#{$header['id']} SSD_DECISION was auto-signed for {$sso->getShortName()}";
                $email_body = <<<EOF
<p><a href="https://www.tld-gse.com/en/private/product_support/index.ps.php?m[0]=sb&m[1]=view&id={$header['id']}">Click here to access SB module (SB#{$header['id']})</a></p>
EOF;
                if ($to) {
                    self::sendEmail(array_merge(...$to), 'noreply@tld-gse.com', $subject, $email_body);
                }
            }
            // Change SB status automatically
            $allowedStatus = $sb->getAllowedStatus();
            if (is_string($allowedStatus)) {
                error_log($allowedStatus);
                continue;
            }

            $e = $sb->updateStatus(0, end($allowedStatus));
            if (is_string($e)) {
                error_log($e);
            }
        }
        error_log('SB - END of SSD_DECISION check script');

        return 'SSD_DECISION script completed';
    }

    public static function autocloseSBWithClosedLines()
    {
        error_log('SB - BEGIN of Autoclose SB with closed lines script');
        $query = <<<EOF
SELECT
    sb.id,
    COUNT(DISTINCT line.status) as distinct_status,
    line.status
FROM
    sb
LEFT JOIN sb_lines line
ON line.parent_id = sb.id
WHERE 
    sb.status = "IMPLEMENTATION"
GROUP BY sb.id
HAVING line.status = 'CLOSED'
AND distinct_status = 1
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);
        foreach ($rows as $row) {
            $sb = new tldSB3($row['id']);
            $message = "SB - Autoclose SB {$row['id']}";
            $e = $sb->updateStatus(0, 'CLOSED');
            if (is_string($e)) {
                $message = $e;
            }
            error_log($message);
            $sb = null;
        }
        error_log('SB - END of Autoclose SB with closed lines script');
    }

    public static function constructEmailFooter()
    {
        return include 'documents/sb/email.footer.php';
    }

    public static function constructEmailHeader()
    {
        return include 'documents/sb/email.header.php';
    }

    /*******************************************
     * STATS and REPORTS methods
     *******************************************/

    public static function byLatest($limit = 10)
    {
        return self::byConstraints('1=1', ['limit' => $limit, 'orderBy' => 'id DESC']);
    }

    public static function countByFactoryStatusByConstraints($a = null)
    {
        $HAVING = '';
        if (is_array($a)) {
            $HAVING = 'HAVING ' . tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $HAVING = "HAVING $a";
        }
        // Construct query
        $FROM = self::getFROM();
        $query = <<<EOF
SELECT
    COUNT(*) AS num,
    factory.location AS factory,
    status
$FROM
$HAVING
GROUP BY sb.bu_id, sb.status
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function countLinesPerISIImplementation($a = null)
    {
        $WHERE = '';
        if (is_array($a)) {
            $WHERE = 'WHERE ' . tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE = "WHERE $a";
        }

        $query = <<<SQL
SELECT
      sb.id,
      sb.dt,
      CONCAT(people.firstname, ' ', people.lastname) as poster,
      er.man_location,
      sb.status,
      sb.category,
      sb.confidential,
      sb.ifactor,
      sb.title,
      sb.description,
      sb.dt_implementation,
      SUM(IF(sb_lines.status='TLD_TO_NOTIFY', 1, 0)) AS TLD_TO_NOTIFY,
      SUM(IF(sb_lines.status='CUSTOMER_TO_DECIDE', 1, 0)) AS CUSTOMER_TO_DECIDE,
      SUM(IF(sb_lines.status='TLD_TO_SHIP', 1, 0)) AS TLD_TO_SHIP,
      SUM(IF(sb_lines.status='TLD_TO_IMPLEMENT', 1, 0)) AS TLD_TO_IMPLEMENT,
      SUM(IF(sb_lines.status='CLOSED', 1, 0)) AS CLOSED,
      COUNT(DISTINCT sb_lines.id) AS total
FROM
    sb
    LEFT JOIN people ON sb.poster_id=people.id
    LEFT JOIN sb_lines ON sb.id=sb_lines.parent_id
    LEFT JOIN service AS er ON er.id=sb_lines.er_id
    $WHERE
GROUP BY sb.id
SQL;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byFactoryStatusByConstraints($factory, $status, $a = null)
    {
        $WHERE = [];
        if ($factory !== 'ALL') {
            $WHERE[] = "factory.location LIKE '$factory'";
        }
        if ($status !== 'ALL') {
            $WHERE[] = "sb.status LIKE '$status'";
        }
        // Additional constraints
        if (is_array($a)) {
            $WHERE[] = tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE[] = $a;
        }

        return self::byConstraints(implode(' AND ', $WHERE));
    }

    public static function countByCategoryStatusByConstraints($a = null)
    {
        $WHERE = '';
        if (is_array($a)) {
            $WHERE = 'WHERE ' . tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE = "WHERE $a";
        }
        // Construct query
        $FROM = self::getFROM();
        $query = <<<EOF
SELECT
    COUNT(*) AS num,
    category,
    status
$FROM
$WHERE
GROUP BY category, status
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byCategoryStatusByConstraints($category, $status, $a = null)
    {
        if ($category !== 'ALL') {
            $WHERE[] = "sb.category LIKE '$category'";
        }
        if ($status !== 'ALL') {
            $WHERE[] = "sb.status LIKE '$status'";
        }
        // Additional constraints
        if (is_array($a)) {
            $WHERE[] = tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE[] = $a;
        }

        return self::byConstraints(implode(' AND ', $WHERE));
    }

    public static function countAwaitingSignatureByCategoryStatusByConstraints($a = null)
    {
        $WHERE = '';
        if (is_array($a)) {
            $WHERE = ' AND ' . tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE = " AND $a";
        }
        $FROM = self::getFROM();
        $query = <<<EOF
SELECT
    COUNT(*) AS num,
    sb.category,
    sb.status
$FROM
    LEFT JOIN sb_signature AS sgn ON sgn.parent_id=sb.id
WHERE
    sgn.user_id=0 AND sb.status IN ('CSM_APPROVAL','SSD_DECISION', 'PARTIAL_IMPLEMENTATION')
    $WHERE
GROUP BY
    category, status
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byAwaitingSignatureByCategoryStatusByConstraints($category, $status, $a = null)
    {
        $WHERE[] = <<<EOF
sb.status IN('CSM_APPROVAL','SSD_DECISION', 'PARTIAL_IMPLEMENTATION')
AND sb.id IN(SELECT parent_id FROM sb_signature WHERE user_id=0)
EOF;
        if ($category !== 'ALL') {
            $WHERE[] = "sb.category LIKE '$category'";
        }
        if ($status !== 'ALL') {
            $WHERE[] = "sb.status LIKE '$status'";
        }
        // Additional constraints
        if (is_array($a)) {
            $WHERE[] = tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE[] = $a;
        }

        return self::byConstraints(implode(' AND ', $WHERE));
    }

    public static function getSBMonthlyReport(array $a, string $decision)
    {
        $decision = $decision === 'SSD_DECISION' ? 'sb.dt_ssd_decision' : 'sb.dt_ssd_approval';
        $WHERE = implode(' AND ', $a);
        $query = <<<EOF
SELECT COUNT(DISTINCT sb.id) as total,
       COUNT(DISTINCT IF(sb.category = 'COMPULSORY', sb.id, NULL)) as total_compulsory,
       SUM(IF(sb.category = 'COMPULSORY', 1, 0)) as total_compulsory_line,
       DATE_FORMAT($decision, '%Y-%m') as date
FROM sb
         INNER JOIN sb_lines ON sb_lines.parent_id=sb.id
         INNER JOIN service ON sb_lines.er_id=service.id
WHERE $decision != '0000-00-00' AND $WHERE
GROUP BY DATE_FORMAT($decision, '%Y-%m')
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getSBByDate(array $a, string $decision, string $type)
    {
        $decision = $decision === 'SSD_DECISION' ? 'sb.dt_ssd_decision' : 'sb.dt_ssd_approval';
        $WHERE = implode(' AND ', $a);

        if ($type === 'Total SB') {
            $query = <<<EOF
SELECT sb.id, sb.status, sb.description, $decision,
       DATE_FORMAT($decision, '%Y-%m') as date
FROM sb
WHERE $decision != '0000-00-00' AND $WHERE
ORDER BY DATE_FORMAT($decision, '%Y-%m')
EOF;
        } else if ($type === 'Total Compulsory SB') {
            $query = <<<EOF
SELECT sb.id, sb.status, sb.description, $decision,
       DATE_FORMAT($decision, '%Y-%m') as date
FROM sb
WHERE $decision != '0000-00-00' AND sb.category = 'COMPULSORY' AND $WHERE
ORDER BY DATE_FORMAT($decision, '%Y-%m')
EOF;
        } else if ($type === 'Total Compulsory SB Lines') {
            $query = <<<EOF
SELECT sb.id, sb.status, sb.description, $decision, sb_lines.id as lineId, sb_lines.status as statusLine, sb_lines.er_id, service.customer_name,
       DATE_FORMAT($decision, '%Y-%m') as date
FROM sb
         INNER JOIN sb_lines ON sb_lines.parent_id=sb.id
         INNER JOIN service ON sb_lines.er_id=service.id
WHERE $decision != '0000-00-00' AND sb.category = 'COMPULSORY' AND $WHERE
ORDER BY DATE_FORMAT($decision, '%Y-%m')
EOF;
        }

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function checkEcustERMultipleSSO()
    {
        $date = (new DateTime('-1 day'))->format('Y-m-d');
        $rows = tldSB_Line::byConstraints('1=1', ['where' => " sb_lines.status != 'CLOSED' AND DATE_FORMAT('sb.dt_ssd_approval', '%Y-%m-%d') = '$date' AND sb.category = 'COMPULSORY'"]);
        $ecustList = [];
        $ecustParentList = [];
        foreach ($rows as $row) {
            if (!($ecustParentList[$row['buyer_customer_id']] ?? null)) {
                $ecustParentList[$row['buyer_customer_id']] = (new tldCustomer($row['buyer_customer_id']))->getParentCustomerIDFamily();
            }
            $ecustList[$ecustParentList[$row['buyer_customer_id']]][$row['parent_id']][] = $row;
        }
        foreach ($ecustList as $ecust => $sb) {
            foreach ($sb as $sbLines) {
                $SSOList = array_unique(array_column($sbLines, 'sso_name'));

                if (count($SSOList) > 1) {
                    $cust = new tldCustomer($ecust);
                    if (0 === $asmId = (int) $cust->getAsmID()) {
                        continue 2;
                    }
                    $sbId = key($sb);
                    $asm = new tldUser($asmId);
                    (new tldSB3($sbId))->notifyTLD(
                        $asm->getEmail(),
                        'noreply@tld-gse.com',
                        "SB#$sbId have ERs in many SSO",
                        "Please be aware that the SB#$sbId regarding your ecust {$sbLines[0]['buyer_customer_name']} is going to be implemented accross multiple SSOs. You might want to check with the CSMs concerned that the implementation is synchronized.",
                        "morgan.franc@tld-group.com"
                    );
                }
            }
        }
    }

    public function getImpactedSSOByConstraints($a = '1=1')
    {
        $WHERE = is_array($a) ? tldUtils::constructWhere($a) : $a;
        $WHERE .= " AND sb_lines.parent_id=$this->itsID ";
        $FROM = tldSB_Line::getFROM();
        $query = <<<EOF
SELECT
    DISTINCT sso.id AS sso_id,
    sso.location AS sso_name
$FROM
WHERE $WHERE
ORDER BY sso_name
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);

        // Get clean array
        return array_column($rows, 'sso_name', 'sso_id');
    }

    public function getImpactedBuyerCustomerByConstraints($a)
    {
        if (empty($a)) {
            $a = '1=1';
        }
        if (is_array($a)) {
            $a = tldUtils::constructWhere($a);
        }
        $a .= " AND sb_lines.parent_id=$this->itsID ";

        return array_column(
            tldSB_Line::byConstraints(
                '1=1',
                [
                    'where' => $a,
                    'groupBy' => 'buyer_customer_id',
                    'orderBy' => 'buyer_customer_name',
                ]
            ),
            'buyer_customer_name',
            'buyer_customer_id'
        );
    }

    public function getImpactedUserCustomerByConstraints($a)
    {
        if (empty($a)) {
            $a = '1=1';
        }
        if (is_array($a)) {
            $a = tldUtils::constructWhere($a);
        }
        $a .= " AND sb_lines.parent_id=$this->itsID ";

        return array_column(
            tldSB_Line::byConstraints(
                '1=1',
                [
                    'where' => $a,
                    'groupBy' => 'user_customer_id',
                    'orderBy' => 'user_customer_name',
                    ]
            ),
            'user_customer_name',
            'user_customer_id'
        );
    }

    public function getImpactedAPCByConstraints($a)
    {
        if (empty($a)) {
            $a = '1=1';
        }
        if (is_array($a)) {
            $a = tldUtils::constructWhere($a);
        }
        $a .= " AND sb_lines.parent_id=$this->itsID ";

        return array_column(
            tldSB_Line::byConstraints(
                '1=1',
                [
                    'where' => $a,
                    'groupBy' => 'apc_code',
                    'orderBy' => 'apc_code',
                ]
            ),
            'apc_code',
            'apc_code'
        );
    }

    public function getImpactedCountryByConstraints($a)
    {
        if (empty($a)) {
            $a = '1=1';
        }
        if (is_array($a)) {
            $a = tldUtils::constructWhere($a);
        }
        $a .= " AND sb_lines.parent_id=$this->itsID ";

        return array_column(
            tldSB_Line::byConstraints(
                '1=1',
                [
                    'where' => $a,
                    'groupBy' => 'apc_country_name',
                    'orderBy' => 'apc_country_name',
                ]
            ),
            'apc_country_name',
            'apc_country_name'
        );
    }

    /*******************************************
     * VIEW methods
     *******************************************/

    public function getPrintVersion()
    {
        $report = new tldAssocTable(
            $this->getHeader(),
            [
                'id' => 'SB#',
                'dt' => 'Date',
                'poster_fullname' => 'Poster',
                'factory' => 'Factory',
                'status' => 'Status',
                'category' => 'Category',
                'type' => 'Type',
                'confidential' => 'Confidential',
                'ifactor' => 'IFactor',
                'title' => 'Title',
                'description' => 'Description',
                'labor' => 'Labor (in minutes)',
                'nb_tech_needed' => 'Technician needed',
                'parts_needed' => 'Parts needed',
            ],
            ['title' => "SB#$this->itsID Details"]
        );

        return $report->fetch();
    }

    public function getFieldDescriptionList()
    {
        return [
            'id' => 'SB#',
            'dt' => 'Date',
            'poster_fullname' => 'Poster',
            'factory' => 'Factory',
            'status' => 'Status',
            'category' => 'Category',
            'type' => 'Type',
            'confidential' => 'Confidential',
            'ifactor' => 'IFactor',
            'title' => 'Title',
            'description' => 'Description',
            'labor' => 'Labor (in minutes)',
            'nb_tech_needed' => 'Technician needed',
            'parts_needed' => 'Parts needed',
        ];
    }

}

class tldSB_Line
{

    public $itsID;
    public $itsHeader;
    const CUSTOMER_TO_DECIDE_TIMER = 120;

    public function __construct($id, bool $lazy = false)
    {
        $this->itsID = $id;
        $this->itsHeader = $lazy ? [] : $this->getHeader();
    }

    /*******************************************
     * GETTERS
     *******************************************/

    public function getID()
    {
        return $this->itsID;
    }

    public function getParentID()
    {
        return $this->itsHeader['parent_id'];
    }

    public function getStatus()
    {
        return $this->itsHeader['status'];
    }

    public function getSSOID()
    {
        return $this->itsHeader['sso_id'];
    }

    public function getSSOERP()
    {
        return $this->itsHeader['sso_erp'];
    }

    public function getERID()
    {
        return $this->itsHeader['er_id'];
    }

    public function getERSN()
    {
        return $this->itsHeader['sn'];
    }

    public function getERBuyerCustomerID()
    {
        return $this->itsHeader['buyer_customer_id'];
    }

    public function getEREndUserCustomerID()
    {
        return $this->itsHeader['user_customer_id'];
    }

    public function getPartDecision()
    {
        return $this->itsHeader['part_decision'];
    }

    public function getCustomerPartDecision()
    {
        return $this->itsHeader['cust_part_decision'];
    }

    public function getSPRID()
    {
        return $this->itsHeader['spr_id'];
    }

    public function getServiceDecision()
    {
        return $this->itsHeader['service_decision'];
    }

    public function getCustomerServiceDecision()
    {
        return $this->itsHeader['cust_service_decision'];
    }

    public function getCSRID()
    {
        return $this->itsHeader['csr_id'];
    }

    public function getCategory()
    {
        return $this->itsHeader['category'];
    }

    public function getClosureType()
    {
        return $this->itsHeader['closure_type'];
    }

    public function getCustomerToDecideStatusDate()
    {
        return $this->itsHeader['dt_cust_to_decide'];
    }

    /*******************************************
     * CRUD methods
     *******************************************/

    public static function getSELECT(): string
    {
        return <<<EOF
SELECT
    sb_lines.*,
    CASE
        WHEN sb_lines.part_decision!='' AND sb_lines.service_decision!='' AND (SELECT COUNT(*) > 0 FROM sb_signature WHERE sb_signature.parent_id = sb.id  AND sb_signature.sso_id = sso.id AND sb_signature.user_id != 0 and sb_signature.status = 'SSD_DECISION') THEN 'DONE'
        ELSE 'NOT_DONE'
    END AS ssd_decision_status,
    spr.status AS spr_status,
    csr.status AS csr_status,
    csr.dt_completed AS csr_completion_date,
    csr.hourmeter AS csr_hourmeter,
    CASE
        WHEN csr.tech_id=0 OR csr.tech_id IS NULL THEN 'NO_TECH'
        ELSE (SELECT CONCAT(firstname,' ',lastname) FROM people WHERE id=csr.tech_id)
    END AS csr_tech_fullname,
    er.sn,
    er.cust_asset_num,
    er.model,
    er.hours,
    er.airport_code AS apc_code,
    (SELECT apc_ctry.name
        FROM airport_codes AS apc
        LEFT JOIN countries AS apc_ctry ON apc_ctry.iso_code_2=apc.ctry_code_2
        WHERE apc.airport_code=er.airport_code AND apc.type LIKE 'Airport'
        GROUP BY apc.ctry_code_2
        LIMIT 1
    ) AS apc_country_name,
    er.man_location AS factory,
    DATE_ADD( er.date_shipped, INTERVAL er.warranty_length MONTH ) AS warranty_end_date,    
    er.buyer_customer_id AS buyer_customer_id,
    er.customer_id AS user_customer_id,
    er.maintainer_customer_id AS maintainer_customer_id,
    buyer_customer.customer_name AS buyer_customer_name,
    user_customer.customer_name AS user_customer_name,
    er.sso_service AS sso_name,
    er.sales_org AS sales_org,
    sso.id AS sso_id,
    sso.erp AS sso_erp,
    sso.sh_tel AS sso_service_tel,
    sso.sh_email AS sso_service_email,
    er.man_location,
    er.date_shipped,
    sb.status AS sb_status,
    sb.category,
    sb.confidential,
    sb.title AS sb_title,
    sb.description AS sb_description,
    sb.labor,
    sb.dt_implementation,
    (CASE
        WHEN part_decision LIKE 'A' AND service_decision LIKE '1' THEN
            CASE
                WHEN sb_lines.status LIKE 'CLOSED' THEN 'CLOSED'
                ELSE 'TLD'
            END
        WHEN part_decision LIKE 'A' THEN
            CASE
                WHEN sb_lines.status LIKE 'CLOSED' AND closure_type NOT LIKE 'REGULAR' THEN 'CUSTOMER'
                WHEN sb_lines.status LIKE 'CLOSED' THEN 'CLOSED'
                ELSE 'TLD'
            END
        WHEN part_decision IN ('B','C','D') THEN
            CASE
                WHEN sb_lines.status LIKE 'CLOSED' AND closure_type NOT LIKE 'REGULAR' THEN 'CUSTOMER'
                WHEN sb_lines.status LIKE 'CLOSED' THEN 'CLOSED'
                WHEN sb_lines.status IN ('TLD_TO_NOTIFY','TLD_TO_IMPLEMENT') THEN 'TLD'
                ELSE 'CUSTOMER'
            END
        ELSE ''
    END) AS remediation
EOF;
    }

    public static function getFROM(): string
    {
        return <<<EOF
FROM
    sb_lines
    LEFT JOIN sb ON sb.id=sb_lines.parent_id
    LEFT JOIN service AS er ON er.id=sb_lines.er_id
    LEFT JOIN locations AS sso ON sso.location=er.sso_service
    LEFT JOIN customers AS buyer_customer ON buyer_customer.id=er.buyer_customer_id
    LEFT JOIN customers AS user_customer ON user_customer.id=er.customer_id
    LEFT JOIN spr ON spr.id=sb_lines.spr_id
    LEFT JOIN csr ON csr.id=sb_lines.csr_id
EOF;
    }

    public function getHeader()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
WHERE sb_lines.id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public static function byConstraints($a, $options = '', $afterGT = false)
    {
        if (empty($a)) {
            $a = '1=1';
        }
        $HAVING = is_array($a) ? 'HAVING ' . tldUtils::constructWhere($a) : "HAVING $a";

        // Look for options
        // where
        $WHERE = '';
        if (!empty($options['where'])) {
            $WHERE = is_array($options['where']) ? 'WHERE ' . tldUtils::constructWhere($options['where']) : "WHERE {$options['where']}";
        }
        $GROUPBY = !empty($options['groupBy']) ? 'GROUP BY ' . $options['groupBy'] : '';
        $ORDERBY = !empty($options['orderBy']) ? 'ORDER BY ' . $options['orderBy'] : 'ORDER BY parent_id';

        // Construct query
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();

        $query = <<<EOF
$SELECT
$FROM
$WHERE
$GROUPBY
$HAVING
$ORDERBY
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getVisibilityConstraintsByCustomerID($cuid)
    {
        // Based on ER CUSTOMER BUYER & not confidential
        return <<<EOF
(er.buyer_customer_id= $cuid OR er.customer_id = $cuid OR er.maintainer_customer_id = $cuid)
AND sb.confidential!='Y'
EOF;
    }

    public static function byCSRID($csr_id)
    {
        return self::byConstraints('1=1', ['where' => "sb_lines.csr_id = $csr_id"]);
    }

    public static function bySPRID($spr_id)
    {
        return self::byConstraints('1=1', ['where' => "sb_lines.spr_id = $spr_id"]);
    }

    public static function insert($a)
    {
        if (empty($a)) {
            return;
        }
        $fields = [
            'parent_id',
            'er_id',
            'status',
            'closure_type',
            'dt_cust_to_decide',
            'part_decision',
            'cust_part_decision',
            'spr_id',
            'service_decision',
            'cust_service_decision',
            'csr_id',
        ];
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO sb_lines SET $SET";

        return tldUtils::sqlInsert($query);
    }

    public function update($a, $fields = '')
    {
        if (empty($a)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "UPDATE sb_lines SET $SET WHERE id=$this->itsID";

        return tldUtils::sqlQuery($query);
    }

    public function delete()
    {
        $query = "DELETE FROM sb_lines WHERE id=$this->itsID LIMIT 1";

        return tldUtils::sqlExecute($query);
    }

    public static function deleteByParent($pid)
    {
        $query = "DELETE FROM sb_lines WHERE parent_id=$pid";

        return tldUtils::sqlExecute($query);
    }

    /*******************************************
     * COMMON MOD methods
     *******************************************/

    public function addLogEntry($uid, $comment, $num_log = 0)
    {
        return tldModLog::insert(
            [
                'parent_id' => $this->itsID,
                'module' => 'SBL',
                'poster' => $uid,
                'comment' => $comment,
                'log_num' => $num_log,
            ]
        );
    }

    public function getLog()
    {
        return tldModLog::byConstraints("parent_id=$this->itsID AND module LIKE 'SBL'");
    }

    public function addNOT($a)
    {
        $a['parent_id'] = $this->itsID;

        return tldSBNOT::insert($a);
    }

    public function getNOT()
    {
        return tldSBNOT::byParent($this->itsID);
    }

    /*******************************************
     *   STATIC and REFERENCE methods
     ********************************************/

    public static function getPartDecisionList()
    {
        return [
            'A' => 'TLD will ship the parts to customer free of charge',
            'B' => 'TLD will ship the parts to customer free of charge upon customer request only',
            'C' => 'TLD will ship the parts and invoice the customer, upon customer request',
            'D' => 'TLD will not ship any parts as none are needed for implementation',
        ];
    }

    public static function getServiceDecisionList()
    {
        return [
            '1' => 'TLD will do the remediation work free of charge',
            '2' => 'TLD will do the remediation work free of charge upon customer request only',
            '3' => 'TLD will do the remediation work and invoice for it, upon customer request',
            '4' => 'Customer is expected to implement this bulletin',
        ];
    }

    public static function getCustomerDecisionList()
    {
        return [
            'Y' => 'Yes',
            'N' => 'No',
            '?' => 'No response',
        ];
    }

    public static function getStatusList()
    {
        return array_merge(self::getOpenStatusList(), self::getClosedStatusList());
    }

    public static function getOpenStatusList()
    {
        return [
            'TLD_TO_NOTIFY' => 'TLD_TO_NOTIFY',
            'CUSTOMER_TO_DECIDE' => 'CUSTOMER_TO_DECIDE',
            'TLD_TO_SHIP' => 'TLD_TO_SHIP',
            'TLD_TO_IMPLEMENT' => 'TLD_TO_IMPLEMENT',
        ];
    }

    public static function getClosedStatusList()
    {
        return [
            'CLOSED' => 'CLOSED',
        ];
    }

    public static function getClosureTypeList()
    {
        return [
            'REGULAR' => 'REGULAR',
            'NO_RESPONSE' => 'NO_RESPONSE',
        ];
    }

    /*******************************************
     * LOGIC and ACTION methods
     *******************************************/

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public function isConfidential()
    {
        return $this->itsHeader['confidential'] === 'Y';
    }

    public function isClosed(array $sb = [])
    {
        if (!empty($sb)) {
            return in_array($sb['status'], self::getClosedStatusList(), true);
        }

        return in_array($this->getStatus(), self::getClosedStatusList(), true);
    }

    /*
     * $sb var is here to be used when called statically
     */
    public function isCustomerPartDecisionNeeded(array $sb = [])
    {
        if (!empty($sb)) {
            return in_array($sb['part_decision'], ['B', 'C']);
        }

        return in_array($this->getPartDecision(), ['B', 'C']);
    }

    /*
     * $sb var is here to be used when called statically
     */
    public function isCustomerServiceDecisionNeeded(array $sb = [])
    {
        if (!empty($sb)) {
            return in_array((int)$sb['service_decision'], [2, 3], true);
        }

        return in_array((int)$this->getServiceDecision(), [2, 3], true);
    }

    public function isCustomerDecisionFullySet()
    {
        return (!$this->isCustomerPartDecisionNeeded() || !empty($this->getCustomerPartDecision())) && (!$this->isCustomerServiceDecisionNeeded() || !empty($this->getCustomerServiceDecision()));
    }

    public function getAllowedStatus($force = null, $uid = null)
    {
        if ($force === 'closed' && $uid !== null) {
            $user = new tldUser($uid);
            if ($user->isInGroup('role_EVP')) {
                return ['CLOSED'];
            }
        }

        $result = [];
        switch ($this->getStatus()) {
            // No status yet (before implementation)
            case '':
                // if NOT confidential
                if (!$this->isConfidential()) {
                    // TLD_TO_NOTIFY for ALL cases
                    $result = ['TLD_TO_NOTIFY'];
                    break;
                }
                // If confidential /!\
                // Part decision over rule
                switch ($this->getPartDecision()) {
                    case 'A':
                        $result = ['TLD_TO_SHIP'];
                        break;
                    case 'D':
                        // Then service decision
                        switch ($this->getServiceDecision()) {
                            case '1':
                                $result = ['TLD_TO_IMPLEMENT'];
                                break;
                            case '4':
                                $result = ['CLOSED'];
                                break;
                        }
                        break;
                }
                break;
            case 'TLD_TO_NOTIFY':
                // Check if NOT created if Force flag not enable
                if ($force !== 'not_bypass') {
                    $notList = $this->getNOT();
                    if (!count($notList)) {
                        return 'Customer not notified yet using NOT';
                    }
                }
                // If customer decision needed
                if (
                    $this->isCustomerPartDecisionNeeded()
                    || $this->isCustomerServiceDecisionNeeded()
                ) {
                    $result = ['CUSTOMER_TO_DECIDE'];
                    break;
                }
                // Begin by part decision
                switch ($this->getPartDecision()) {
                    case 'A':
                        $result = ['TLD_TO_SHIP'];
                        break;
                    case 'B':
                    case 'C':
                        $result = ['CUSTOMER_TO_DECIDE'];
                        break;
                    case 'D':
                        // Then service decision
                        switch ($this->getServiceDecision()) {
                            case '1':
                                $result = ['TLD_TO_IMPLEMENT'];
                                break;
                            case '2':
                            case '3':
                                $result = ['CUSTOMER_TO_DECIDE'];
                                break;
                            case '4':
                                $result = ['CLOSED'];
                                break;
                        }
                        break;
                }
                break;
            case 'CUSTOMER_TO_DECIDE':
                // Check that all decision are fully set
                if (!$this->isCustomerDecisionFullySet()) {
                    return 'Customer decision is not fully completed';
                }
                // Look for Parts first
                switch ($this->getPartDecision()) {
                    case 'A':
                        $result = ['TLD_TO_SHIP'];
                        break 2;
                        break;
                    case 'B':
                    case 'C':
                        $customerPartDecision = $this->getCustomerPartDecision();
                        // If customer ok
                        if ($customerPartDecision === 'Y') {
                            $result = ['TLD_TO_SHIP'];
                            break 2;
                        }
                        break;
                }
                // Then look for Service
                switch ($this->getServiceDecision()) {
                    case '1':
                        $result = ['TLD_TO_IMPLEMENT'];
                        break;
                    case '2':
                    case '3':
                        $customerServiceDecision = $this->getCustomerServiceDecision();
                        // If customer ok
                        if ($customerServiceDecision === 'Y') {
                            $result = ['TLD_TO_IMPLEMENT'];
                            break 2;
                        }
                        // In other cases
                        $result = ['CLOSED'];
                        break;
                    case '4':
                        $result = ['CLOSED'];
                        break;
                }
                break;
            case 'TLD_TO_SHIP':
                // Check SPR record linked & SPR is closed
                if ($this->getSPRID() == 0) {
                    return 'No SPR found';
                }
                // Check SPR is closed
                $spr = new tldSPR($this->getSPRID());
                if (!$spr->isClosed()) {
                    return "SPR#{$this->getSPRID()} not CLOSED";
                }
                // Look for service decision
                switch ($this->getServiceDecision()) {
                    case '1':
                        $result = ['TLD_TO_IMPLEMENT'];
                        break;
                    case '2':
                    case '3':
                        $customerServiceDecision = $this->getCustomerServiceDecision();
                        // If customer ok
                        if ($customerServiceDecision === 'Y') {
                            $result = ['TLD_TO_IMPLEMENT'];
                            break 2;
                        }
                        // In other cases
                        $result = ['CLOSED'];
                        break;
                    case '4':
                        $result = ['CLOSED'];
                        break;
                }
                break;
            case 'TLD_TO_IMPLEMENT':
                // Check CSR record linked & closed
                if ($this->getCSRID() == 0) {
                    return 'No CSR found';
                }
                // Check CSR is CLOSED or COMPLETED
                $csr = new tldCSR($this->getCSRID());
                if ($csr->isEmpty()) {
                    return 'No CSR found';
                }
                if (!in_array($csr->getStatus(), ['COMPLETED', 'CLOSED'])) {
                    return "CSR#{$this->getCSRID()} not COMPLETED or CLOSED";
                }
                // if ok
                $result = ['CLOSED'];
                break;
        }
        // Check result
        if (empty($result)) {
            return 'No status allowed found to change ISI';
        }

        return $result;
    }

    public function updateStatus($uid, $newStatus = null, $force = null)
    {
        $allowedStatus = $this->getAllowedStatus($force, $uid);
        if (empty($allowedStatus)) {
            return "Status $newStatus not allowed";
        }

        if (is_string($allowedStatus)) {
            return $allowedStatus;
        }

        $newStatus = $newStatus ?: $allowedStatus[0];

        if (!in_array($newStatus, $allowedStatus, true)) {
            return "Status $newStatus not allowed";
        }

        $e = $this->update(['status' => $newStatus]);
        if (is_string($e)) {
            return $e;
        }
        // Log
        $this->addLogEntry($uid, $newStatus);
        // do actions?...
        switch ($newStatus) {
            case 'CUSTOMER_TO_DECIDE':
                $this->setDateCustomerToDecide(date('Y-m-d'));
                break;
            case 'CLOSED':
                $this->setClosureType();
                break;
        }

        return;
    }

    public function setDateCustomerToDecide($date)
    {
        return $this->update(['dt_cust_to_decide' => $date]);
    }

    public function setClosureType()
    {
        $closeType = 'REGULAR';
        if ($this->getCustomerPartDecision() === '?' || $this->getCustomerServiceDecision() === '?') {
            $closeType = 'NO_RESPONSE';
        }

        return $this->update(['closure_type' => $closeType]);
    }

    public static function isTriggerStatusChangeAllowed($module, $recordID)
    {
        // Check records linked
        switch ($module) {
            case 'CSR':
                $lines = self::byCSRID($recordID);
                if (empty($lines)) {
                    return;
                }
                // 1 CSR = 1 SBL
                $line = new tldSB_Line($lines[0]['id']);
                // Check ISI
                if (!in_array($line->getStatus(), ['TLD_TO_IMPLEMENT', 'CLOSED'])) {
                    return "Can not implement SB3#{$line->getParentID()} for ER SN {$line->getERSN()} if ISI is not TLD_TO_IMPLEMENT or CLOSED";
                }
                break;
        }

        return;
    }

    public static function triggerStatusChange($module, $recordID)
    {
        // Check records linked
        switch ($module) {
            case 'SPR':
                $lines = self::bySPRID($recordID);
                break;
            case 'CSR':
                $lines = self::byCSRID($recordID);
                break;
            case 'SBL': // by SB line ID
                $lines = self::byConstraints('1=1', ['where' => "sb_lines.id = $recordID"]);
                break;
        }
        if (empty($lines)) {
            return;
        }
        // If so, trigger ISI workflow
        foreach ($lines as $lineVars) {
            $line = new tldSB_Line($lineVars['id']);
            $line->updateStatus(0);
        }
    }

    public function setDecision($parts = null, $service = null)
    {
        $a = [];
        if (!empty($parts) && array_key_exists((string)$parts, self::getPartDecisionList())) {
            $a['part_decision'] = $parts;
        }
        if (!empty($service) && array_key_exists((string)$service, self::getPartDecisionList())) {
            $a['service_decision'] = $service;
        }

        return $this->update($a);
    }

    public function setCustomerDecision($uid, $a)
    {
        // Check ISI
        if ($this->getStatus() !== 'CUSTOMER_TO_DECIDE') {
            return 'Customer decision only available when status is CUSTOMER_TO_DECIDE';
        }
        // Check validity
        // -- Part decision
        if (
            $this->isCustomerPartDecisionNeeded()
            && !in_array($a['cust_part_decision'], ['', 'N', 'Y', '?'], false)
        ) {
            return 'Customer part decision invalid';
        }
        // -- Service decision
        if (
            $this->isCustomerServiceDecisionNeeded()
            && !in_array($a['cust_service_decision'], ['', 'N', 'Y', '?'], false)
        ) {
            return 'Customer service decision invalid';
        }
        // Update decision
        $e = $this->update($a, ['cust_part_decision', 'cust_service_decision']);
        if (is_string($e)) {
            return $e;
        }
        // Add log
        $logPart = 'Customer decision for Part: ';
        $logPart .= $this->isCustomerPartDecisionNeeded() ? $a['cust_part_decision'] : 'N/A';

        $logServ = 'Customer decision for Service: ';
        $logServ .= $this->isCustomerServiceDecisionNeeded() ? $a['cust_service_decision'] : 'N/A';

        $log = <<<EOF
$logPart
$logServ
Reason: {$a['reason']}
EOF;
        $this->addLogEntry($uid, TldDatabase::escape($log));
        // Trigger status change
        self::triggerStatusChange('SBL', $this->itsID);

        return;
    }

    public function isSPRCreationAllowed(array $sb = [])
    {
        if (!empty($sb)) {
            if ($sb['spr_id'] != 0) {
                return 'SPR already created';
            }
            if ($sb['part_decision'] === 'D') {
                return 'Part decision is D';
            }
            $customerDecision = $sb['cust_part_decision'];
            if (
                in_array($sb['part_decision'], ['B', 'C'])
                && in_array($customerDecision, ['N', '', '?'])
            ) {
                return "Customer decision is $customerDecision";
            }
            if (!in_array($sb['status'], ['TLD_TO_SHIP', 'TLD_TO_IMPLEMENT'])) {
                return 'Status not TLD_TO_SHIP or TLD_TO_IMPLEMENT';
            }

            return true;
        }
        if ($this->getSPRID() != 0) {
            return 'SPR already created';
        }
        if ($this->getPartDecision() === 'D') {
            return 'Part decision is D';
        }
        $customerDecision = $this->getCustomerPartDecision();
        if (
            in_array($this->getPartDecision(), ['B', 'C'])
            && in_array($customerDecision, ['N', '', '?'])
        ) {
            return "Customer decision is $customerDecision";
        }
        if (!in_array($this->getStatus(), ['TLD_TO_SHIP', 'TLD_TO_IMPLEMENT'])) {
            return 'Status not TLD_TO_SHIP or TLD_TO_IMPLEMENT';
        }

        return true;
    }

    public function createSPR($uid, $partsQty)
    {
        throw new Exception('This page has been migrated and should not be used anymore');
    }

    public function setSPR($sprID, $uid)
    {
        // Rule check
        $allowed = $this->isSPRCreationAllowed();
        if ($allowed !== true) {
            return $allowed;
        }
        // Assign SPR ID
        $e = $this->update(['spr_id' => $sprID]);
        if (is_string($e)) {
            return $e;
        }
        // Log creation
        $this->addLogEntry($uid, "SPR#$sprID created");
        // Create LINK to ER
        tldModLink::insert('SPR', $sprID, 'ER', $this->getERID());
        // Trigger status move
        if ($this->getStatus() === 'CUSTOMER_TO_DECIDE') {
            // try to update status
            $this->updateStatus($uid);
        }

        return;
    }

    public function isCSRCreationAllowed(array $sbLine = [])
    {
        if (!empty($sbLine)) {
            if ($sbLine['csr_id'] != 0) {
                return 'CSR already created';
            }

            if ('CLOSED' === $sbLine['status'] && 'CANCELLED' !== $sbLine['sb_status']) {
                return true;
            }

            if ($sbLine['service_decision'] == '4') {
                return 'Service decision is 4';
            }
            $customerDecision = $sbLine['cust_service_decision'];
            if (
                in_array($sbLine['service_decision'], ['2', '3'])
                && in_array($customerDecision, ['N', '', '?'])
            ) {
                return "Customer decision is $customerDecision";
            }
            if (!in_array($sbLine['status'], ['TLD_TO_SHIP', 'TLD_TO_IMPLEMENT'])) {
                return 'Status not TLD_TO_SHIP or TLD_TO_IMPLEMENT';
            }

            return true;
        }
        if ($this->getCSRID() != 0) {
            return 'CSR already created';
        }

        if ('CLOSED' === $this->getStatus() && 'CANCELLED' !== $this->itsHeader['sb_status']) {
            return true;
        }

        if ($this->getServiceDecision() == '4') {
            return 'Service decision is 4';
        }
        $customerDecision = $this->getCustomerServiceDecision();
        if (
            in_array($this->getServiceDecision(), ['2', '3'])
            && in_array($customerDecision, ['N', '', '?'])
        ) {
            return "Customer decision is $customerDecision";
        }
        if (!in_array($this->getStatus(), ['TLD_TO_SHIP', 'TLD_TO_IMPLEMENT'])) {
            return 'Status not TLD_TO_SHIP or TLD_TO_IMPLEMENT';
        }

        return true;
    }

    public function createCSR($uid)
    {
        // Rule check
        $allowed = $this->isCSRCreationAllowed();
        if ($allowed !== true) {
            return $allowed;
        }

        $equipmentRecordProvider = new EquipmentRecordProvider();
        $equipmentRecord = $equipmentRecordProvider->findByLegacyId($this->getERID());

        $airportProvider = new AirportProvider();
        $airport = $airportProvider->findByCode($this->itsHeader['apc_code']);

        $data = [
            'equipmentRecord' => $equipmentRecord->iri,
            'airport' => $airport->iri,
            'hourmeter' => (int) $this->itsHeader['hours'],
            'title' => htmlspecialchars(mb_convert_encoding($this->itsHeader['sb_title'], 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8')),
            'description' => htmlspecialchars(mb_convert_encoding($this->itsHeader['sb_description'], 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8')),
            'serviceBulletinLegacyId' => (int) $this->getParentID(),
        ];

        $persister = new CustomerServiceRecordPersister();
        $csr = $persister->post($data);

        // Log creation
        $this->addLogEntry($uid, sprintf('CSR#%d created', $csr->id));
        // Update line
        //TODO reset CSR ID to SB Lines
//        $this->update(['csr_id' => $csrID]);
        // Trigger status move
        if ('CUSTOMER_TO_DECIDE' === $this->getStatus()) {
            $this->updateStatus($uid);
        }

        // Return the CSR#
        return $csr;
    }

    /*
     * $sb var is here to be used when called statically
     */
    public function isNOTCreationAllowed(array $sbLine = [])
    {
        if (!empty($sbLine)) {
            if ($sbLine['confidential'] === 'Y') {
                return 'SB is confidential';
            }
            if ($sbLine['status'] !== 'TLD_TO_NOTIFY') {
                return 'Notification was bypassed or already sent';
            }

            return true;
        }
        if ($this->isConfidential()) {
            return 'SB is confidential';
        }
        if ($this->getStatus() !== 'TLD_TO_NOTIFY') {
            return 'Notification was bypassed or already sent';
        }

        return true;
    }

    public function createNOT($uid, $a, $byPassChecks)
    {
        // Rule check
        $allowed = $byPassChecks ?: $this->isNOTCreationAllowed();
        if ($allowed !== true) {
            return $allowed;
        }
        // Create NOT
        $e = $this->addNOT($a);
        if (is_string($e)) {
            return $e;
        }
        // Log creation
        $this->addLogEntry($uid, 'Notification sent to customer');
        // Trigger status move
        if ('TLD_TO_NOTIFY' === $this->getStatus()) {
            $this->updateStatus($uid);
        }

        return $e;
    }

    /**
     * $data array SB headers
     */
    public static function getDefaultNotificationHeaderContent(array $data = [])
    {
        return include 'documents/sb/en/email.implementation.header.tpl.php';
    }

    public static function getDefaultNotificationERContent($erList)
    {
        $notificationContent = null;
        $data['er'] = $erList;
        $notificationContent = include 'documents/sb/en/email.implementation.er.tpl.php';

        return $notificationContent;
    }

    public static function getDefaultNotificationContent()
    {
        return [
            'A' => [
                1 => <<<EOF
The required parts for this Service Bulletin will be shipped free of charge. Please check the following list of Serial numbers and Airport codes to confirm the delivery address and contact for the shipment.
Our Customer Service will contact you shortly to organize the implementation of this Service Bulletin, which will be free of charge.
EOF,
                2 => <<<EOF
The required parts for this Service Bulletin will be shipped free of charge. Please check the following list of Serial numbers and Airport codes to confirm the delivery address and contact for the shipment.
We recommend you to carry out the implementation of this Service Bulletin as soon as possible. Our Customer Service is available to support you in case you need TLD's assistance.
EOF,
                3 => <<<EOF
The required parts for this Service Bulletin will be shipped free of charge. Please check the following list of Serial numbers and Airport codes to confirm the delivery address and contact for the shipment.
We recommend you to carry out the implementation of this Service Bulletin as soon as the parts have been delivered. If you need TLD's assistance, please contact our Customer Service to obtain a detailed quote for the implementation.
EOF,
                4 => <<<EOF
The required parts for this Service Bulletin will be shipped free of charge. Please check the following list of Serial numbers and Airport codes to confirm the delivery address and contact for the shipment.
We recommend you to carry out the implementation of this Service Bulletin as soon as the parts have been delivered. This could be done by your team or one of your approved service providers and should not require any TLD assistance.
EOF,
            ],
            'B' => [
                1 => <<<EOF
Please confirm your interest in implementing this Service Bulletin and if you wish to receive the necessary parts, free of charge for units under warranty. For Units out of warranty period, do not hesitate to get in touch with your regional Spare Parts department to obtain further price and delivery details. Our Customer Service will then contact you to organize the implementation of this Service Bulletin, which will be free of charge.
EOF,
                2 => <<<EOF
Please confirm your interest in implementing this Service Bulletin and if you wish to receive the necessary parts, free of charge for units under warranty. For Units out of warranty period, do not hesitate to get in touch with your regional Spare Parts department to obtain further price and delivery details.
We recommend you to carry out the implementation of this Service Bulletin upon arrival of the parts. Our Customer Service is available to support you in case you need TLD's assistance.
EOF,
                3 => <<<EOF
Please confirm your interest in implementing this Service Bulletin and if you wish to receive the necessary parts, free of charge for units under warranty. For Units out of warranty period, do not hesitate to get in touch with your regional Spare Parts department to obtain further price and delivery details.
We recommend you to carry out the implementation of this Service Bulletin upon arrival of the parts. If you need TLD's assistance, please contact our Customer Service to obtain a detailed quote for the implementation.
EOF,
                4 => <<<EOF
Please confirm your interest in implementing this Service Bulletin to receive the necessary parts free of charge for units under warranty. For Units out of warranty period, do not hesitate to get in touch with your regional Spare Parts department to obtain further price and delivery details.
We recommend you to carry out the implementation of this Service Bulletin upon arrival of the parts. This could be done by your team or one of your approved service providers and should not require any TLD assistance.
EOF,
            ],
            'C' => [
                1 => <<<EOF
The necessary parts for this Service Bulletin are sold by our regional TLD spare parts hubs. Please do not hesitate to get in touch with your preferred TLD contact to obtain further price and delivery details. In case you decide to implement this Service Bulletin, our Customer Service will contact you to schedule its implementation free of charge.
EOF,
                2 => <<<EOF
The necessary parts for this Service Bulletin are sold by our regional TLD spare parts hubs. Please do not hesitate to get in touch with your preferred TLD contact to obtain further price and delivery details. In case you decide to implement this Service Bulletin, our Customer Service would be able to assist you with its implementation, if needed.
EOF,
                3 => <<<EOF
The necessary parts for this Service Bulletin are sold by our regional TLD spare parts hubs. Please do not hesitate to get in touch with your preferred TLD contact to obtain further price and delivery details, regarding both the parts and the implementation of this Service Bulletin.
EOF,
                4 => <<<EOF
The necessary parts for this Service Bulletin are sold by our regional TLD spare parts hubs. Please do not hesitate to get in touch with your preferred TLD contact to obtain further price and delivery details. The implementation of this Service Bulletin could be done by your team or one of your approved service providers and should not require any TLD assistance.
EOF,
            ],
            'D' => [
                1 => <<<EOF
This Service Bulletin does not require spare-parts and our Customer Service will contact you shortly to organize the implementation of this Service Bulletin, which will be free of charge.
EOF,
                2 => <<<EOF
This Service Bulletin does not require spare-parts and we recommend you to carry out the implementation of this Service Bulletin as soon as possible. Our Customer Service is available to support you in case you need TLD's assistance.
EOF,
                3 => <<<EOF
This Service Bulletin does not require spare-parts and we recommend you to carry out its implementation. If you need TLD's assistance, please contact our Customer Service to obtain a detailed quote for the implementation.
EOF,
                4 => <<<EOF
This Service Bulletin does not require spare-parts and its implementation could be done by your team or one of your approved service providers and should not require any TLD assistance.
EOF,
            ],
        ];
    }

    public function getDefaultNotificationFooterContent($user)
    {
        $notificationContent = null;
        $data = $this->itsHeader;
        $data['user'] = $user->itsDetails;
        $notificationContent = include 'documents/sb/en/email.implementation.footer.tpl.php';

        return $notificationContent;
    }

    public static function checkCUSTOMER_TO_DECIDE()
    {
        error_log('SB - BEGIN of CUSTOMER_TO_DECIDE check script');
        $nbDaysExpired = self::CUSTOMER_TO_DECIDE_TIMER;
        $a = <<<EOF
sb_lines.status LIKE 'CUSTOMER_TO_DECIDE'
AND DATEDIFF(NOW(), sb_lines.dt_cust_to_decide)>=$nbDaysExpired
EOF;
        $rows = self::byConstraints('1=1', ['where' => $a]);
        if (!count($rows)) {
            return 'No lines found';
        }
        foreach ($rows as $row) {
            $line = new tldSB_Line($row['id']);
            $a = ['reason' => 'CUSTOMER_TO_DECIDE expired'];
            // Check decisions
            $partDecision = $line->getCustomerPartDecision();
            if ($line->isCustomerPartDecisionNeeded()) {
                $a['cust_part_decision'] = empty($partDecision) ? '?' : $partDecision;
            }
            // set customer SERVICE decisions automatically
            $serviceDecision = $line->getCustomerServiceDecision();
            if ($line->isCustomerServiceDecisionNeeded()) {
                $a['cust_service_decision'] = empty($serviceDecision) ? '?' : $serviceDecision;
            }
            // Set decisions automatically
            $line->setCustomerDecision(0, $a);
        }
        error_log('SB - END of CUSTOMER_TO_DECIDE check script');

        return 'CUSTOMER_TO_DECIDE script completed';
    }

    /************************************************
     *  STATISTICS & LISTING METHOD
     ***********************************************/

    public static function countBySSOByStatusByConstraints($a = '1=1')
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "WHERE $WHERE";
        }
        $FROM = self::getFROM();
        $query = <<<EOF
SELECT
    sb_lines.status,
    er.sso_service AS sso_service_name,
    COUNT(*) AS num
$FROM
$WHERE
GROUP BY
    er.sso_service, sb_lines.status
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function countBySSOByStatusByParentID($pid)
    {
        return self::countBySSOByStatusByConstraints(['sb_lines.parent_id' => $pid]);
    }

    public static function bySSOByStatusByConstraints($sso, $status, $a = '1=1')
    {
        $WHERE = [];
        if ($sso !== 'ALL') {
            $WHERE[] = "er.sso_service LIKE '$sso'";
        }
        if ($status !== 'ALL') {
            $WHERE[] = "sb_lines.status LIKE '$status'";
        }
        // Constraints
        if (is_array($a)) {
            $WHERE[] = tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE[] = $a;
        }

        return self::byConstraints('1=1', ['where' => implode(' AND ', $WHERE)]);
    }

    public static function bySSOByStatusByParent($sso, $status, $pid)
    {
        return self::bySSOByStatusByConstraints($sso, $status, ['sb_lines.parent_id' => $pid]);
    }

    public static function countByStatusByAirportByConstraints($a = '1=1')
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "WHERE $WHERE";
        }
        $FROM = self::getFROM();
        $query = <<<EOF
SELECT
    sb_lines.status,
    er.airport_code AS apc_code,
    COUNT(*) AS num
$FROM
$WHERE
GROUP BY
    sb_lines.status, er.airport_code
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public function countByStatusByAirportByParent($pid)
    {
        return self::countByStatusByAirportByConstraints(['sb_lines.parent_id' => $pid]);
    }

    public function byStatusByAirportByConstraints($status, $apc, $a = '1=1')
    {
        $WHERE = [];
        if ($status !== 'ALL') {
            $WHERE[] = "sb_lines.status LIKE '$status'";
        }
        if ($apc !== 'ALL') {
            $WHERE[] = "er.airport_code LIKE '$apc'";
        }
        // Constraints
        if (is_array($a)) {
            $WHERE[] = tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE[] = $a;
        }

        return self::byConstraints('1=1', ['where' => implode(' AND ', $WHERE)]);
    }

    public static function countByStatusByCategoryByConstraints($a = '1=1')
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "WHERE $WHERE";
        }
        $FROM = self::getFROM();
        $query = <<<EOF
SELECT
    sb_lines.status,
    sb.category,
    COUNT(*) AS num
$FROM
$WHERE
GROUP BY
    sb.category, sb_lines.status
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byStatusByCategoryByConstraints($status, $category, $a = '1=1')
    {
        $WHERE = [];
        if ($status !== 'ALL' && $status !== 'TOTAL_OPEN') {
            $WHERE[] = "sb_lines.status LIKE '$status'";
        }
        if ($category !== 'ALL') {
            $WHERE[] = "sb.category LIKE '$category'";
        }

        if ($status === 'TOTAL_OPEN') {
            $WHERE[] = "sb_lines.status != 'CLOSED'";
            $WHERE[] = "sb_lines.status != ''";
        }

        // Constraints
        if (is_array($a)) {
            $WHERE[] = tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE[] = $a;
        }

        return self::byConstraints('1=1', ['where' => implode(' AND ', $WHERE)]);
    }

    public static function countEstimatedOperationHoursByStatusByCategoryByConstraints($a = '1=1')
    {
        $WHERE = is_array($a) ? tldUtils::constructWhere($a) : $a;
        if (!empty($WHERE)) {
            $WHERE = "AND $WHERE";
        }
        $FROM = self::getFROM();
        $query = <<<EOF
SELECT
    sb_lines.status,
    sb.category,
    SUM(sb.labor)/60 AS num
$FROM
WHERE
    sb_lines.service_decision!=4
    AND sb_lines.cust_service_decision NOT IN ('?','N')
    $WHERE
GROUP BY
    sb.category, sb_lines.status
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function countEstimatedOperationHoursByStatusByAPCByConstraints($a = '1=1')
    {
        $WHERE = is_array($a) ? tldUtils::constructWhere($a) : $a;
        if (!empty($WHERE)) {
            $WHERE = "AND $WHERE";
        }
        $FROM = self::getFROM();
        $query = <<<EOF
SELECT
    sb_lines.status,
    er.airport_code AS apc_code,
    SUM(sb.labor)/60 AS num
$FROM
WHERE
    sb_lines.service_decision!=4
    AND sb_lines.cust_service_decision NOT IN('?','N')
    $WHERE
GROUP BY
    apc_code, sb_lines.status
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byEstimatedOperationHoursByStatusAPCByConstraints($status, $apc, $a = '1=1')
    {
        $WHERE[] = 'sb_lines.service_decision!=4';
        $WHERE[] = "sb_lines.cust_service_decision NOT IN ('?','N')";
        if ($status !== 'ALL') {
            $WHERE[] = "sb_lines.status LIKE '$status'";
        }
        if ($apc !== 'ALL') {
            $WHERE[] = "er.airport_code LIKE '$apc'";
        }
        // Constraints
        if (is_array($a)) {
            $WHERE[] = tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE[] = $a;
        }

        return self::byConstraints('1=1', ['where' => implode(' AND ', $WHERE)]);
    }

    public static function countByCategoryTechByConstraints($a = '1=1')
    {
        $WHERE = is_array($a) ? tldUtils::constructWhere($a) : $a;
        if (!empty($WHERE)) {
            $WHERE = "WHERE $WHERE";
        }
        $FROM = self::getFROM();
        $query = <<<EOF
SELECT
    CASE
        WHEN csr.tech_id=0 OR csr.tech_id IS NULL THEN 'NO_TECH'
        ELSE (SELECT CONCAT(firstname,' ',lastname) FROM people WHERE id=csr.tech_id)
    END AS csr_tech_fullname,
    sb.category,
    COUNT(*) AS num
$FROM
$WHERE
GROUP BY
    sb.category, csr_tech_fullname
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byCategoryTechByConstraints($category, $tech, $a = '1=1')
    {
        $WHERE = [];
        if ($category !== 'ALL') {
            $WHERE[] = $category !== 'ALL' ? "sb.category LIKE '$category'" : [];
        }
        $HAVING = $tech !== 'ALL' ? "csr_tech_fullname LIKE '$tech'" : '1=1';

        // Constraints
        if (\is_array($a)) {
            $WHERE[] = tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE[] = $a;
        }

        return self::byConstraints($HAVING, ['where' => implode(' AND ', $WHERE)]);
    }

    public static function countByCategoryDecisionByConstraints($a = '1=1')
    {
        $WHERE = is_array($a) ? tldUtils::constructWhere($a) : $a;
        if (!empty($WHERE)) {
            $WHERE = "WHERE $WHERE";
        }
        $FROM = self::getFROM();
        $query = <<<SQL
SELECT
    CASE        
        WHEN sb_lines.part_decision!='' AND sb_lines.service_decision!='' AND (SELECT COUNT(*) > 0 FROM sb_signature WHERE sb_signature.parent_id = sb.id  AND sb_signature.sso_id = sso.id AND sb_signature.user_id != 0 and sb_signature.status = 'SSD_DECISION')
     THEN 'DONE'
        ELSE 'NOT_DONE'
    END AS ssd_decision_status,
    sb.category,
    COUNT(*) AS num
$FROM
$WHERE
GROUP BY
    sb.category, ssd_decision_status
SQL;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byCategoryDecisionByConstraints($category, $decision, $a = '1=1')
    {
        $WHERE = [];
        if ($category !== 'ALL') {
            $WHERE[] = "sb.category LIKE '$category'";
        }

        $HAVING = ($decision !== 'ALL') ? "ssd_decision_status LIKE '$decision'" : '1=1';

        // Constraints
        if (is_array($a)) {
            $WHERE[] = tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE[] = $a;
        }

        return self::byConstraints($HAVING, ['where' => implode(' AND ', $WHERE)]);
    }

    public static function getStatsBySBID($sbid)
    {
        $query = <<<sql
SELECT
    COUNT(sb_lines.id) AS nb_er_impacted,
    SUM(IF(sb_lines.part_decision LIKE 'A',1,0)) AS parts_qty_needed,
    SUM(IF(sb_lines.part_decision IN ('B','C'),1,0)) AS parts_qty_possibly_needed,
    SUM(IF(sb_lines.service_decision=1,1,0)) AS nb_service_needed,
    SUM(IF(sb_lines.service_decision IN (2,3),1,0)) AS nb_service_possibly_needed
FROM
    sb_lines
WHERE
    sb_lines.parent_id=$sbid
sql;

        return tldUtils::getSqlRowToAssocArray($query);
    }

}

class tldSB_Signature
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

    public function getParentID()
    {
        return $this->itsHeader['parent_id'];
    }

    public function getStatus()
    {
        return $this->itsHeader['status'];
    }

    public function getSSOID()
    {
        return $this->itsHeader['sso_id'];
    }

    public function getUserID()
    {
        return $this->itsHeader['user_id'];
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public static function getSELECT(): string
    {
        return <<<EOF
SELECT
    sb_signature.*,
    CONCAT(signatory.firstname, ' ', signatory.lastname) AS signatory_fullname,
    sso.location AS sso_name,
    (SELECT GROUP_CONCAT(comment SEPARATOR '\n\n') FROM mod_logs WHERE mod_logs.parent_id = sb_signature.id and module = 'SB3SIGN') as logs
EOF;
    }

    public static function getFROM(): string
    {
        return <<<EOF
FROM
    sb_signature
    LEFT JOIN people AS signatory ON signatory.id=sb_signature.user_id
    LEFT JOIN locations AS sso ON sso.id=sb_signature.sso_id
EOF;
    }

    public function getHeader()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
WHERE sb_signature.id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public static function byConstraints($a, $opt = [])
    {
        if (empty($a)) {
            return 'empty parameter';
        }
        // Look for constraints
        $HAVING = is_array($a) ? 'HAVING ' . tldUtils::constructWhere($a) : "HAVING $a";

        // Look for options
        $ORDERBY = !empty($opt['orderBy']) ? "ORDER BY {$opt['orderBy']}" : 'ORDER BY id';

        // Construct query
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
$HAVING
$ORDERBY
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function insert($a)
    {
        if (empty($a)) {
            return;
        }
        $fields = ['parent_id', 'dt', 'user_id', 'sso_id', 'status'];
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO sb_signature SET $SET";

        return tldUtils::sqlInsert($query);
    }

    public function update($a, $fields = '')
    {
        if (empty($a)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "UPDATE sb_signature SET $SET WHERE id=$this->itsID";

        return tldUtils::sqlQuery($query);
    }

    public function delete()
    {
        $query = "DELETE FROM sb_signature WHERE id=$this->itsID LIMIT 1";

        return tldUtils::sqlExecute($query);
    }

    public static function deleteByParentByStatus($parentId, $status)
    {
        $query = "DELETE FROM sb_signature WHERE parent_id=$parentId AND status LIKE '$status'";

        return tldUtils::sqlExecute($query);
    }

}

class tldSB_Coverage
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

    public function getParentID()
    {
        return $this->itsHeader['parent_id'];
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public static function getSELECT(): string
    {
        return <<<EOF
SELECT
    *
EOF;
    }

    public static function getFROM(): string
    {
        return <<<EOF
FROM
    sb_coverage
EOF;
    }

    public function getHeader()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
WHERE sb_coverage.id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public static function byConstraints($a, $opt = [])
    {
        if (empty($a)) {
            return 'empty parameter';
        }
        // Look for constraints
        if (is_array($a)) {
            $HAVING = 'HAVING ' . tldUtils::constructWhere($a);
        } else {
            $HAVING = "HAVING $a";
        }
        // Look for options
        if (!empty($opt['orderBy'])) {
            $ORDERBY = 'ORDER BY ' . $opt['orderBy'];
        } else {
            $ORDERBY = 'ORDER BY id';
        }
        // Construct query
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
$HAVING
$ORDERBY
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function insert($a)
    {
        if (empty($a)) {
            return;
        }
        $fields = [
            'parent_id',
            'model',
            'sn_from',
            'sn_to',
            'sn_list',
        ];
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO sb_coverage SET $SET";

        return tldUtils::sqlInsert($query);
    }

    public function update($a, $fields = '')
    {
        if (empty($a)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "UPDATE sb_coverage SET $SET WHERE id=$this->itsID";

        return tldUtils::sqlQuery($query);
    }

    public function delete()
    {
        $query = "DELETE FROM sb_coverage WHERE id=$this->itsID LIMIT 1";

        return tldUtils::sqlExecute($query);
    }

    public static function byParentID($pid)
    {
        return self::byConstraints(['parent_id' => $pid]);
    }

}
