<?php
/**
 *    Calendar related classes
 *
 * @package   Calendar
 * @desc      All classes related to the calendars are kept in this file
 * @access    public
 * @author    Graham K.L. Fong <graham.fong@tld-america.com>
 * @copyright TLD
 */

/**
 * need these functions
 */
require_once('iCalcreator.class.php');

class tldCAL
{
    private $itsICAL;

    public function __construct($uid, $name, $desc)
    {
        $this->itsICAL = new vcalendar();
        $this->itsICAL->setConfig('unique_id', $uid);
        $this->itsICAL->setProperty('method', 'PUBLISH');
        $this->itsICAL->setProperty('x-wr-calname', $name);
        $this->itsICAL->setProperty('X-WR-CALDESC', $desc);
    }

    public function addEvent($ev)
    {
        $this->itsICAL->setComponent($ev);
    }

    public function out()
    {
        $this->itsICAL->returnCalendar();
    }
}

/**
 * Creates a tldTask object
 *
 * @package Calendar
 */
class tldTask
{

    public const NEED_ASSIGNOR_FOR_COMPLETION = ['CPA'];

    public const CATEGORY_CONCERNED_BY_BUG_FIX = [
				"WEBSITE" => "WEBSITE",
				"WEBSITE, Customer ePARTS" => "WEBSITE, Customer ePARTS",
				"WEBSITE, Customer EXTRANET" => "WEBSITE, Customer EXTRANET",
				"WEBSITE, DMS" => "WEBSITE, DMS",
				"WEBSITE, eQuotes" => "WEBSITE, eQuotes",
				"WEBSITE, eVendor" => "WEBSITE, eVendor",
				"WEBSITE, INTRANET" => "WEBSITE, INTRANET",
				"WEBSITE, SHOPFLOOR" => "WEBSITE, SHOPFLOOR",
                "Solidworks/PDMworks/SeeElec" => "Solidworks/PDMworks/SeeElec"
			];

    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->refresh();
    }

    /**
     * Refresh the header property
     *
     * @return null
     */
    public function refresh()
    {
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Is task empty?
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    /**
     * Is this a sequence?
     *
     * @return bool
     */
    public function isSequence()
    {
        return $this->itsHeader['seq'] === 'Y';
    }

    /**
     * Get task represented as an array
     *
     * @return array
     */
    public function asArray()
    {
        $result = $this->getHeader();
        $assignor = new tldUser($result['assignor']);
        $result['assignor'] = $assignor->getDetails();
        $assignee = new tldUser($result['assignee']);
        $result['assignee'] = $assignee->getDetails();
        $result['comments'] = $this->getComments();
        $result['params'] = $this->getCloseParams();

        return $result;
    }

    /*
     * Set the importance factor
     */
    public function setIFactor($if)
    {
        if (!in_array((int) $if, [1, 10, 100, 1000], true)) {
            return 'ERROR: iFactor must be either 1, 10, 100 or 1000 only...';
        }
        if (empty($this->itsID)) {
            return 'ERROR: Task ID not set in setIFactor...';
        }
        $query = <<<EOF
UPDATE tasks
SET ifactor=$if
WHERE id=$this->itsID
EOF;

        return tldUtils::sqlQuery($query);
    }

    public function getIFactor()
    {
        return $this->itsHeader['ifactor'];
    }

    /*
     * Set the estimated completion date
     */

    public function setDComp($dcomp)
    {
        if (empty($this->itsID)) {
            return 'ERROR: Task ID not set in setDCOMP...';
        }
        $query = <<<EOF
select
    datediff('$dcomp', due_date) as due_date_diff,
    datediff('$dcomp', NOW()) as dcomp_diff
from tasks
WHERE id=$this->itsID
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);
        if ($rows[0]['due_date_diff'] > 0) {
            return "ERROR: Completion date cannot be after due date...task#$this->itsID";
        }
        if ($rows[0]['dcomp_diff'] < 0) {
            return "ERROR: Completion date cannot be before today...task#$this->itsID";
        }
        $query = <<<EOF
UPDATE tasks
SET dcomp='$dcomp'
WHERE id=$this->itsID
EOF;

        return tldUtils::sqlQuery($query);
    }

    public function getDComp()
    {
        return $this->itsHeader['dcomp'];
    }

    public function asVTODO()
    {
        $a = $this->asArray();
        $vevent = new vtodo();
        $vevent->setProperty(
            'due',
            $a['due_date'],
            [
                'VALUE' => 'DATE',
            ]
        );
        // alt. date format, now for an all-day event
        $vevent->setProperty('organizer', $a['assignor']['email']);
        $vevent->setProperty('summary', 'TLD Tasks#' .$this->itsID);
        $vevent->setProperty('description', $a['task']);

        return $vevent;
    }

    /***********************************************
     *  Class GETTERS
     * *********************************************/

    /**
     * Get full text description of task
     *
     * @return string
     */
    public function getTask()
    {
        return $this->itsHeader['task'];
    }

    /**
     * Get erp company number from erp field
     *
     * @return integer
     */
    public function getERP()
    {
        return $this->itsHeader['erp'];
    }

    /**
     * Get BU ID from bu_id field
     *
     * @return integer
     */
    public function getBUID()
    {
        return $this->itsHeader['bu_id'];
    }

    /**
     * Get UNserialized params for close script
     *
     * @return mixed
     */
    public function getCloseParams()
    {
        return unserialize(base64_decode($this->itsHeader['close_params']));
    }

    /**
     * Get array of valid module names
     *
     * @return array
     */
    public static function getModuleList()
    {
        return array_keys(tldUtils::getModLinks());
    }

    /**
     * Get module name that task is linked to
     *
     * @return string
     */
    public function getModule()
    {
        return strtoupper($this->itsHeader['module']);
    }

    /**
     * Get module link that task is linked to
     *
     * @return string
     */
    public function getModuleLink()
    {
        $moduleList = tldUtils::getModLinks();
        $moduleTask = strtoupper($this->itsHeader['module']);

        return $moduleList[$moduleTask];
    }

    /**
     * Get id number of parent record
     *
     * @return int
     */
    public function getParentID()
    {
        return $this->itsHeader['parent_id'];
    }

    /**
     * Get id number of assignee
     *
     * @return int
     */
    public function getAssignee()
    {
        return $this->itsHeader['assignee'];
    }

    /**
     * Get id number of assignor
     *
     * @return int
     */
    public function getAssignor()
    {
        return $this->itsHeader['assignor'];
    }

    /**
     * Get sequence template number
     *
     * @return int
     */
    public function getTPLNo()
    {
        return $this->itsHeader['tplno'];
    }

    /**
     * Get Task category
     *
     * @return char
     */
    public function getCategory()
    {
        return $this->itsHeader['cat'];
    }

    /**
     * Get value of field from header data
     *
     * @param string $field Name of field to retrieve from header
     *
     * @return mixed
     */
    public function getField($field)
    {
        return $this->itsHeader[$field];
    }

    /***********************************************
     *  Class SETTERS
     * *********************************************/

    /**
     * Set Task category
     *
     * @param string A/B/C
     *
     * @return mixed string on error
     */
    public function setCategory($cat)
    {
        return $this->update(['cat' => $cat]);
    }

    /***********************************************
     *  Class Methods
     * *********************************************/

    public static function getSELECT(): string
    {
        return <<<EOF
SELECT
    tasks.*,
    concat(assignee.lastname,', ',assignee.firstname) AS assignee_fullname,
    concat(assignor.lastname,', ',assignor.firstname) AS assignor_fullname,
    IF(tasks.due_date<NOW(), 1, 0) AS overdue,
    IF(tasks.due_date<NOW(), ROUND(DATEDIFF(NOW(), tasks.due_date)/7), 0) AS wks_overdue
EOF;
    }

    public static function getFROM(): string
    {
        return <<<EOF
FROM
    tasks
    LEFT JOIN people AS assignee ON tasks.assignee=assignee.id
    LEFT JOIN people AS assignor ON tasks.assignor=assignor.id
EOF;
    }

    /**
     * Get header data as a db row
     *
     * @return array
     */
    public function getHeader()
    {
        $query = <<<EOF
SELECT
    tasks.*,

    (SELECT CONCAT(lastname,', ',firstname) FROM people WHERE people.id=assignee) AS assignee_fullname,
    (SELECT CONCAT(lastname,', ',firstname) FROM people WHERE people.id=assignor) AS assignor_fullname,
    TO_DAYS(now())-TO_DAYS(due_date) AS days_late,
    cal_seq_tpl.name AS tpl_fullname,
    (SELECT location FROM locations WHERE id=tasks.bu_id) AS bu_fullname,
    (SELECT location FROM locations WHERE locations.id = (
                                    SELECT people.bu_id
                                    FROM people
                                    WHERE people.id = tasks.assignor )
    ) AS buname
FROM
    tasks
    LEFT JOIN cal_seq_tpl ON tasks.tplno=cal_seq_tpl.id
WHERE
    tasks.id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Get comments as an array of db rows
     *
     * @return array
     */
    public function getComments()
    {
        return tldTaskComment::byParent($this->itsID);
    }

    public function getFileByComments()
    {
        return tldTaskComment::byFileByParent($this->itsID);
    }

    /**
     * Get assignees based on module and user as an array of db rows
     *
     * @param string $userid Username of assignee
     * @param string $module Name of module to look for
     *
     * @return array
     */
    public static function getAssigneesByUser($module = '', $userid = null): array
    {
        switch (strtoupper($module)) {
            case 'USER':
                $user = new tldUser($userid);
                // if user in gg_admin group display the whole user list
                if ($user->isInGroup(['gg_ADMIN'])) {
                    $grp = new tldGroup('ACL_AUTH_INTRANET');
                    $result = $grp->getUserList('smartyOptions');
                    break;
                }
                // if user in gg_mis group display the whole gg_mis user list
                if ($user->isInGroup(['GG_MIS'])) {
                    $grp = new tldGroup('GG_MIS');
                    $result = $grp->getUserlist('smartyOptions');
                    break;
                }

                if ($user->isInGroup('role_ASM')) {
                    // if user in role_ASM display all technician in local region
                    $grp = new tldGroup('gg_SERVICE', tldLocation::getERPByID($user->getBUID()));
                    $result = $user->getSubordinates('smartyOptions') + $grp->getUserlist(['smartyOptions' => true]) +
                        $user->getPeers('smartyOptions');
                    $supervisor = new tldUser($user->getSupervisor());
                    $result[$supervisor->getId()] = $supervisor->getFullname();
                    asort($result);
                } else {

                    // else only display the subordonates and peers
                    $result = $user->getSubordinates('smartyOptions') +
                        $user->getPeers('smartyOptions');
                    $supervisor = new tldUser($user->getSupervisor());
                    $result[$supervisor->getId()] = $supervisor->getFullname();
                    asort($result);
                }
                break;
            case 'ASO':
                $groupsSSO = ['gg_ACCT', 'gg_PARTS', 'gg_PUR', 'gg_SALES'];
                $usersSSO = tldGroup::getUserListByMultipleGroup($groupsSSO, 250, 'smartyOptions');
                $result = $usersSSO;
                break;
            default:
                $grp = new tldGroup('ACL_AUTH_INTRANET');
                $result = $grp->getUserList('smartyOptions');
                break;
        }

        return $result;
    }

    /**
     * Get assignees of OPEN tasks as an array of db rows
     *
     * @param array|string $options Associative array of restrictions
     *
     * @return array
     */
    public static function getAssignees($options = '', bool $displayEmail = true)
    {
        $emailRequest = $displayEmail ? ", ' (', people.email, ')'" : "";
        $WHERE = $options ? ' AND ' .tldUtils::constructWhere($options) : '';

        $query = <<<EOF
        SELECT DISTINCT people.id,
            CONCAT(people.lastname,', ',people.firstname$emailRequest) as fullname,
            people.*,
            COUNT(if(due_date<NOW(),1,null)) AS overdueCount,
            COUNT(*) as taskCount
        FROM people, tasks
        WHERE people.id=tasks.assignee
            AND tasks.status<>'CLOSED'
        $WHERE
        GROUP BY fullname
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Add user that have been cc along the task
     *
     * @param int $uid user id
     *
     * @return int id or string error
     */
    public function addCC($uid)
    {
        if (empty($this->itsID) || empty($uid) || !is_numeric($uid)) {
            return;
        }
        $a = [
            'parent_id' => $this->itsID,
            'module' => 'TASK',
            'list_name' => 'task.cc',
            'value' => $uid,
        ];

        return tldModList::insert($a);
    }

    /**
     * Returns people that have been cc along the task
     *
     * @return array array or db array rows
     */
    public function getCC()
    {
        if (empty($this->itsID)) {
            return [];
        }
        $opts = ['list_name' => 'task.cc', 'mode' => 'smartyOptions', 'fields' => 'value', 'smartyFields'=> 'value'];

        return tldModList::byParent($this->itsID, 'TASK', $opts);
    }

    /**
     * Returns assignee's tasks
     *
     * db rows from task table by week, month or specific period
     *
     * @param integer $id ID of assignee
     * @param string  $m  Mode
     * @param string  $n  optional paramters
     *
     * @return array array or db array rows
     */
    public static function byAssignee($id, $m = '', $n = '')
    {
        if (empty($id)) {
            return;
        }
        $WHERE = " WHERE tasks.status<>'CLOSED' AND tasks.assignee=$id";
        switch (strtolower($m)) {
            case 'module':
                $WHERE .= " AND module='$n' ";
                break;
            case 'week':
                ;
                break;
            case 'month':
                ;
                break;
            case 'specific':
                $start = TldDatabase::escape($n['start']);
                $end = TldDatabase::escape($n['end']);
                $WHERE .= " AND due_date BETWEEN '$start' AND '$end' ";
                break;
        }
        $query = <<<EOF
        SELECT tasks.*,
            concat(assignees.lastname,', ',assignees.firstname) as assignee_fullname,
            concat(assignors.lastname,', ',assignors.firstname) as assignor_fullname,
            if(due_date<NOW(), 1, 0) AS overdue,
            IF(due_date<NOW(), ROUND(DATEDIFF(now(), due_date)/7), 0) as wks_overdue,
            if(due_date<NOW() && status<>'CLOSED',
                CONCAT(TO_DAYS(now())-TO_DAYS(due_date), '<img src="/shared/bluesphere/32x32/actions/kill.png" alt="This task is late."><br>'),
                0
                ) AS overdue_icon,
            (SELECT tasks_comments.comment FROM tasks_comments WHERE tasks_comments.parent_id=tasks.id ORDER BY tasks_comments.date desc LIMIT 1) AS lastcomment
        FROM
            tasks LEFT JOIN people AS assignees ON tasks.assignee=assignees.id
            LEFT JOIN people AS assignors ON tasks.assignor=assignors.id
        $WHERE
        ORDER BY assignee_fullname, assignor_fullname
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * @param int $mooId
     * @param bool $includeClosed
     * @return array
     */
    public static function byMOO($mooId, $includeClosed = false)
    {
        $userModules = tldModule::byConstraints(['oid' => $mooId]);

        $tasks = [];
        foreach ($userModules as $module) {
            $constraint = "task LIKE '%module</b>: {$module['module']}%'";
            if ($includeClosed === false) {
                $constraint.= " AND status != 'CLOSED'";
            }
            $tasks[] = self::byConstraints($constraint);
        }

        return array_merge(...$tasks);
    }

    /**
     * Returns assignor's tasks
     *
     * db rows from task table by week, month or specific period
     *
     * @param integer $id ID of assignor
     * @param string  $m  Mode
     * @param string  $n  optional parameters
     *
     * @return array array or db array rows
     */
    public static function byAssignor($id, $m = '', $n = '')
    {
        if (empty($id)) {
            return;
        }
        $a = [
            "tasks.status<>'CLOSED'",
            "tasks.assignor=$id",
        ];
        switch (strtolower($m)) {
            case 'module':
                $a[] = tldUtils::constructWhere($n);
                break;
            case 'week':
            case 'month':
                break;
            case 'specific':
                $start = TldDatabase::escape($n['start']);
                $end = TldDatabase::escape($n['end']);
                $a[] = " AND due_date BETWEEN '$start' AND '$end' ";
                break;
        }

        $WHERE = ' WHERE ' .implode(' AND ', $a);

        $query = <<<EOF
        SELECT tasks.*,
            concat(assignees.lastname,', ',assignees.firstname) as assignee_fullname,
            concat(assignors.lastname,', ',assignors.firstname) as assignor_fullname,
            if(due_date<NOW(), 1, 0) AS overdue,
            IF(due_date<NOW(), ROUND(DATEDIFF(now(), due_date)/7), 0) as wks_overdue,
            if(due_date<NOW() && status<>'CLOSED',
                    CONCAT(TO_DAYS(now())-TO_DAYS(due_date), '<img src="/shared/bluesphere/32x32/actions/kill.png" alt="This task is late."><br>'),
                0
                ) AS overdue_icon,
            (SELECT tasks_comments.comment FROM tasks_comments WHERE tasks_comments.parent_id=tasks.id ORDER BY tasks_comments.date desc LIMIT 1) AS lastcomment
        FROM
            tasks LEFT JOIN people AS assignees ON tasks.assignee=assignees.id
            LEFT JOIN people AS assignors ON tasks.assignor=assignors.id
        $WHERE
        AND task NOT LIKE '%Please remember to do your weekly laptop computer data backup during lunch time on FRIDAY%'
        ORDER BY assignee_fullname
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byUser($uid)
    {
        $query = <<<EOF
        SELECT tasks.*,
            CONCAT(assignees.lastname,', ',assignees.firstname) as assignee_fullname,
            CONCAT(assignors.lastname,', ',assignors.firstname) as assignor_fullname,
            (SELECT division FROM tld_regions
                WHERE tld_regions.id=assignors.div_id
            ) AS assignor_division,
            (SELECT location FROM locations WHERE locations.id =(SELECT bu_id FROM people WHERE people.id=tasks.assignor)) AS assignor_BU,
            if(due_date<NOW(), 1, 0) AS overdue,
            IF(due_date<NOW(), ROUND(DATEDIFF(now(), due_date)/7), 0) as wks_overdue,
            if(due_date<NOW() && status<>'CLOSED',
                CONCAT(TO_DAYS(now())-TO_DAYS(due_date), '<img src="/shared/bluesphere/32x32/actions/kill.png" alt="This task is late."><br>'),
                0
            ) AS overdue_icon,
            (SELECT mis_tts.category FROM mis_tts
                 WHERE mis_tts.id=tasks.parent_id AND tasks.module LIKE 'TTS'
             ) AS queue_category,
            (SELECT tasks_comments.comment FROM tasks_comments WHERE tasks_comments.parent_id=tasks.id ORDER BY tasks_comments.date desc LIMIT 1) AS lastcomment
        FROM
            tasks
            LEFT JOIN people AS assignees ON tasks.assignee=assignees.id
            LEFT JOIN people AS assignors ON tasks.assignor=assignors.id
        WHERE
            tasks.status<>'CLOSED'
            AND (tasks.assignor=$uid OR tasks.assignee=$uid)
        ORDER BY
            tasks.id DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * get all tasks by related document and module
     * if id is set to 'ALL' then all tasks for specifid module will be returned.
     * Must specify module KEY
     *
     * @param integer $id     Parent ID
     * @param string  $mode   Mode
     * @param string  $module Module name of task to look for
     * @param string  $opt    option for additional filtering
     *
     * @return array array of db rows
     */
    public static function byParent($id, $module = '', $mode = '', $opt = '')
    {
        if (strtoupper($module) !== 'ALL' && !in_array(
                strtoupper($module),
                self::getModuleList()
            )
        ) {
            error_log("tldTask: Module $module not allowed.");

            return;
        }
        $WHERE = '';
        if ($id !== 'ALL') {
            $WHERE .= "tasks.parent_id=$id ";
        }

        if (strtoupper($module) !== 'ALL') {
            if ($WHERE) {
                $WHERE .= ' AND ';
            }
            $WHERE .= "	tasks.module='$module'";
        }
        if (strtoupper($mode) !== 'ALL') {
            if ($WHERE) {
                $WHERE .= ' AND ';
            }
            $WHERE .= " tasks.status<>'CLOSED'";
        }
        if (!empty($opt)) {
            if ($WHERE) {
                $WHERE .= ' AND ';
            }
            $WHERE .= " $opt";
        }
        $query = <<<EOF
        SELECT tasks.*, concat(a.lastname,', ',a.firstname) as assignee_fullname,
            concat(b.lastname,', ',b.firstname) as assignor_fullname,
            a.email as assignee_email,
            b.email as assignor_email,
            TO_DAYS(now())-TO_DAYS(due_date) AS days_late,
            if(due_date<NOW(), 1, 0) AS overdue,
            if(due_date<NOW() && status<>'CLOSED',
                CONCAT('<img src="/shared/bluesphere/32x32/actions/kill.png" alt="This task is late."><br>',TO_DAYS(now())-TO_DAYS(due_date),' days'),
                null) AS overdue_icon,
            (SELECT comment FROM tasks_comments
            WHERE parent_id=tasks.id ORDER BY date DESC LIMIT 1
            ) AS lastcomment
        FROM tasks LEFT JOIN people AS a ON tasks.assignee=a.id
        LEFT JOIN people AS b ON tasks.assignor=b.id
        WHERE
            $WHERE
        ORDER BY status DESC, due_date ASC
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);
        $result = [];
        foreach ($rows as $row) {
            $task = new tldTask($row['id']);
            $row['comments'] = $task->getComments();
            $result[] = $row;
        }

        return $result;
    }

    public static function byFileParent($id, $module = '', $mode = '')
    {
        if (strtoupper($module) !== 'ALL' && !in_array(
                strtoupper($module),
                self::getModuleList()
            )
        ) {
            error_log("tldTask: Module $module not allowed.");

            return;
        }
        $WHERE = '';
        if ($id !== 'ALL') {
            $WHERE .= "tasks.parent_id=$id ";
        }

        if (strtoupper($module) !== 'ALL') {
            if ($WHERE) {
                $WHERE .= ' AND ';
            }
            $WHERE .= "	tasks.module='$module'";
        }
        if (strtoupper($mode) !== 'ALL') {
            if ($WHERE) {
                $WHERE .= ' AND ';
            }
            $WHERE .= " tasks.status<>'CLOSED'";
        }
        $query = <<<EOF
        SELECT tasks.*, concat(a.lastname,', ',a.firstname) as assignee_fullname,
            concat(b.lastname,', ',b.firstname) as assignor_fullname,
            TO_DAYS(now())-TO_DAYS(due_date) AS days_late,
            if(due_date<NOW(), 1, 0) AS overdue,
            if(due_date<NOW() && status<>'CLOSED',
                CONCAT('<img src="/shared/bluesphere/32x32/actions/kill.png" alt="This task is late."><br>',TO_DAYS(now())-TO_DAYS(due_date),' days'),
                null) AS overdue_icon
        FROM tasks LEFT JOIN people AS a ON tasks.assignee=a.id
        LEFT JOIN people AS b ON tasks.assignor=b.id
        WHERE
            $WHERE
        ORDER BY status DESC, due_date ASC
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);
        $ret = $re = [];
        foreach ($rows as $row) {
            $task = new tldTask($row['id']);
            $ret[] = $task->getFileByComments();
        }
        foreach ($ret as $index => $value) {
            foreach ($value as $key => $val) {
                $re[] = $val;
            }
        }

        return $re;
    }

    /**
     * Get list of delinquent tasks
     *
     * Gets tasks that are over 60 days over due and have not been escalated for 60 days
     *
     * @return array of db rows
     */
    public static function byDelinquent()
    {
        $query = <<<EOF
        SELECT
            T1.*,
            CONCAT(a.lastname,', ',a.firstname) as assignee_fullname,
            a.email AS assignee_email,
            b.id AS supervisor,
            concat(b.lastname,', ',b.firstname) as supervisor_fullname
        FROM
            tasks AS T1,
            people AS a,
            people AS b
        WHERE
            T1.assignee=a.id
            AND a.reports_to=b.id
            AND T1.status<>'CLOSED' AND T1.status<>'PAUSE' AND (T1.module != 'TTS' AND T1.module != 'TOC')
            # 0='' returns true, therefore tasks with an escalation_trigger of 0 are escalated 60 days after
            AND TO_DAYS(NOW())-TO_DAYS(T1.due_date) > IF(T1.escalation_trigger='',60,T1.escalation_trigger)
            AND IF(T1.d_escal='0000-00-00 00:00:00',
                TO_DAYS(NOW())-TO_DAYS(T1.due_date),
                TO_DAYS(NOW())-TO_DAYS(T1.d_escal)) > IF(T1.escalation_trigger='',60,T1.escalation_trigger)
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get a list of late tasks grouped by assignee and module
     *
     * Used to create matrix report
     *
     * @return array array of db rows
     */
    public static function qryLateByAssigneeModule()
    {
        $query = <<<EOF
SELECT tasks.*,
    (SELECT concat(people.lastname,', ',people.firstname) FROM people WHERE people.id=assignee)
     AS assignee_fullname
FROM tasks
WHERE status<>'CLOSED' AND NOW()>due_date
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get count of tasks per month for a particular period
     *
     * @param string $start start date in mySQL format
     * @param string $end   end date in mySQL format
     *
     * @return array array of db rows
     */
    public static function countByMonthModule($start = '', $end = '')
    {
//		$WHERE = " and to_days(date)>to_days(NOW())-365";
        $query = <<<EOF
select DATE_FORMAT(dt_closed, '%Y-%m') as month_closed,module,count(*) as num
from tasks
where  dt_closed<>'0000-00-00'
and to_days(date)>to_days(NOW())-365
group by monthname(dt_closed), module
order by dt_closed
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function countByWeeksOverdue($assignee = '')
    {
        $WHERE = is_numeric($assignee) ? " AND assignee=$assignee" : '';

        $query = <<<EOF
        SELECT assignee,
                if(datediff(NOW(), due_date)>0,
                52*YEAR(NOW())+WEEK(NOW())-52*YEAR(due_date)-WEEK(due_date),
              0) AS wk,
              count(*) AS cnt
        FROM tasks
        WHERE status<>'CLOSED'
            $WHERE
        GROUP BY assignee,wk
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get count of tasks per month for a particular period per modules not migrated
     *
     * @param string $assignee
     *
     * @return array array of db rows
     *
     */
    public static function countByModuleNotMigrated($assignee = '')
    {
        $WHERE = is_numeric($assignee) ? " AND tasks.assignee=$assignee " : '';
        $query = <<<EOF
SELECT
    COUNT(*) AS num,
    tasks.module,
    IF(tasks.due_date < NOW(), 'LATE', 'DUE') AS overdue
FROM tasks
LEFT JOIN mis_tts ON tasks.parent_id = mis_tts.id
WHERE tasks.status != 'CLOSED'
    AND tasks.module != 'MIS'
    AND tasks.module != 'TOC'
    AND (
        tasks.module != 'TTS'
        OR mis_tts.status = 'QUEUE'
    )
    $WHERE
GROUP BY tasks.module, overdue
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byWeeksOverdue($assignee = '', $wk = '')
    {
        $WHERE = '';
        if ($assignee) {
            $WHERE .= " AND assignee=$assignee";
        }
        if ($wk !== '') {
            $WHERE .= " HAVING wk=$wk";
        }
        $query = <<<EOF
        SELECT *,
            if(datediff(NOW(), due_date)>0,
                52*YEAR(NOW())+WEEK(NOW())-52*YEAR(due_date)-WEEK(due_date),
              0) AS wk,
            (SELECT CONCAT(firstname,' ',lastname) FROM people WHERE people.id=assignee) AS assignee_fullname,
            (SELECT CONCAT(firstname,' ',lastname) FROM people WHERE people.id=assignor) AS assignor_fullname
        FROM tasks
        WHERE status<>'CLOSED'
        $WHERE
        ORDER BY due_date ASC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get latest tasks
     *
     * Gets tasks latest tasks added
     *
     * @param int    $n
     * @param string|array $options
     * @param string $operand
     *
     * @return array of db rows
     *
     */
    public static function byLatest($n = 10, $options = '', $operand = '')
    {
        $WHERE = is_array($options) ? ' AND ' .tldUtils::constructWhere($options, $operand) : '';

        $query = <<<EOF
        SELECT T1.*,
            concat(a.lastname,', ',a.firstname) as assignee_fullname,
            b.id AS supervisor,
            concat(b.lastname,', ',b.firstname) as supervisor_fullname
        FROM tasks AS T1 LEFT JOIN people AS a ON T1.assignee=a.id
        LEFT JOIN people AS b ON a.reports_to=b.id
        WHERE T1.status<>'CLOSED'
        $WHERE
        ORDER BY id DESC
        LIMIT $n
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byPN($pn, $n = 10)
    {
        $query = <<<EOF
        SELECT T1.*,
            concat(a.lastname,', ',a.firstname) as assignee_fullname,
            (SELECT CONCAT(lastname,', ',firstname) FROM people WHERE people.id=assignor
            ) AS assignor_fullname,
            b.id AS supervisor,
            concat(b.lastname,', ',b.firstname) as supervisor_fullname,
            seq.key2 as pn
        FROM tasks AS T1
        LEFT JOIN people AS a ON T1.assignee=a.id
        LEFT JOIN people AS b ON a.reports_to=b.id
        LEFT JOIN mod_keys as seq ON T1.id=seq.parent_id AND seq.module LIKE 'SEQ' AND seq.type LIKE 'eng.newpartnb%'
        WHERE seq.key2 LIKE '$pn%'
        ORDER BY T1.date DESC
        LIMIT $n
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byItemSEQStatusLocation($location, $step)
    {
        $HAVING = "HAVING T1.tplno=22 AND T1.status <> 'CLOSED' AND T1.erp !=0 ";
        if ($location !== 'ALL') {
            $HAVING .= " AND T1.erp=$location";
        }
        if ($step !== 'ALL') {
            $HAVING .= " AND step='$step'";
        }

        $query = <<<EOF
        SELECT T1.*,
            concat(a.lastname,', ',a.firstname) as assignee_fullname,
            (SELECT CONCAT(lastname,', ',firstname) FROM people WHERE people.id=assignor
            ) AS assignor_fullname,
            b.id AS supervisor,
            concat(b.lastname,', ',b.firstname) as supervisor_fullname,
            seq.key2 as pn,
            CONCAT('Step ',T1.cur_step) AS step
        FROM tasks AS T1
        LEFT JOIN people AS a ON T1.assignee=a.id
        LEFT JOIN people AS b ON a.reports_to=b.id
        LEFT JOIN mod_keys as seq ON T1.id=seq.parent_id
        $HAVING
        ORDER BY T1.date DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byItemSEQStatusAssignor($uid, $step)
    {
        $HAVING = "HAVING T1.tplno=22 AND T1.status <> 'CLOSED' AND T1.erp !=0 ";
        if ($uid !== 'ALL') {
            $HAVING .= " AND T1.assignor=$uid";
        }
        if ($step !== 'ALL') {
            $HAVING .= " AND step='$step'";
        }

        $query = <<<EOF
        SELECT T1.*,
            concat(a.lastname,', ',a.firstname) as assignee_fullname,
            (SELECT CONCAT(lastname,', ',firstname) FROM people WHERE people.id=assignor
            ) AS assignor_fullname,
            b.id AS supervisor,
            concat(b.lastname,', ',b.firstname) as supervisor_fullname,
            seq.key2 as pn,
            CONCAT('Step ',T1.cur_step) AS step
        FROM tasks AS T1
        LEFT JOIN people AS a ON T1.assignee=a.id
        LEFT JOIN people AS b ON a.reports_to=b.id
        LEFT JOIN mod_keys as seq ON T1.id=seq.parent_id
        $HAVING
        ORDER BY T1.date DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function countByNewItemSEQ($options = '')
    {
        $buid = 'AND T1.erp!=0';
        if (isset($options['erp']) && $options['erp'] > 0) {
            $buid = "AND T1.erp={$options['erp']}";
        }

        $query = <<<EOF
        SELECT loc.location AS location,
        CONCAT('Step ',T1.cur_step) AS step,
        count(*) AS num
        FROM tasks AS T1
        LEFT JOIN locations AS loc ON T1.erp=loc.erp
        WHERE T1.tplno=22 AND T1.status != 'CLOSED' $buid
        GROUP BY loc.location, T1.cur_step
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byRecentlyClosed($uid, $n = 10, $m = '')
    {
        $WHERE = '';
        if ($uid) {
            $WHERE .= " AND (assignee=$uid OR assignor=$uid)";
        }
        if (is_array($m)) {
            $WHERE .= ' AND ' .tldUtils::constructWhere($m);
        }
        $query = <<<EOF
        SELECT T1.*,
            concat(a.lastname,', ',a.firstname) as assignee_fullname,
            (SELECT CONCAT(lastname,', ',firstname) FROM people WHERE people.id=assignor
            ) AS assignor_fullname,
            b.id AS supervisor,
            concat(b.lastname,', ',b.firstname) as supervisor_fullname
        FROM tasks AS T1 LEFT JOIN people AS a ON T1.assignee=a.id
        LEFT JOIN people AS b ON a.reports_to=b.id
        WHERE T1.status='CLOSED'
        $WHERE
        ORDER BY dt_closed DESC
        LIMIT $n
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byUserbyModule($uid, $module = '', $category = '')
    {
        $WHERE = '';
        if ($uid) {
            $WHERE .= " AND T1.assignee=$uid";
        }
        if ($module) {
            $WHERE .= " AND module='$module'";
        }
        switch ($category) {
            case 'LATE':
                $WHERE .= ' AND T1.due_date<NOW()';
                break;
            case 'DUE':
                $WHERE .= ' AND T1.due_date>NOW()';
                break;
        }

        $query = <<<EOF
        SELECT T1.*,
            concat(a.lastname,', ',a.firstname) as assignee_fullname,
            (SELECT CONCAT(lastname,', ',firstname) FROM people WHERE people.id=assignor
            ) AS assignor_fullname,
            b.id AS supervisor,
            concat(b.lastname,', ',b.firstname) as supervisor_fullname
        FROM tasks AS T1 LEFT JOIN people AS a ON T1.assignee=a.id
        LEFT JOIN people AS b ON a.reports_to=b.id
        WHERE T1.status<>'CLOSED'
        $WHERE;
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byUserbyModulesNotMigrated($uid, $modules = [], $category = '')
    {
        $WHERE = '';
        if ($uid) {
            $WHERE .= " AND T1.assignee=$uid";
        }
        if (count($modules) > 0) {
            $inlineModules = "'" . implode("','", $modules) . "'";
            $WHERE .= " AND module IN ($inlineModules)";
        }
        switch ($category) {
            case 'LATE':
                $WHERE .= ' AND T1.due_date<NOW()';
                break;
            case 'DUE':
                $WHERE .= ' AND T1.due_date>NOW()';
                break;
        }

        $query = <<<EOF
        SELECT T1.*,
            concat(a.lastname,', ',a.firstname) as assignee_fullname,
            (SELECT CONCAT(lastname,', ',firstname) FROM people WHERE people.id=assignor
            ) AS assignor_fullname,
            b.id AS supervisor,
            concat(b.lastname,', ',b.firstname) as supervisor_fullname
        FROM tasks AS T1 LEFT JOIN people AS a ON T1.assignee=a.id
        LEFT JOIN mis_tts ON T1.parent_id = mis_tts.id
        LEFT JOIN people AS b ON a.reports_to=b.id
        WHERE T1.status != 'CLOSED'
        AND T1.module != 'MIS'
        AND (
            T1.module != 'TTS'
            OR mis_tts.status = 'QUEUE'
        )
        $WHERE;
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byConstraints($a, $orderBy = '')
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        // options
        if (empty($orderBy)) {
            $ORDERBY = 'T1.assignee, T1.due_date';
        } else {
            $ORDERBY = $orderBy;
        }
        $query = <<<EOF
SELECT
    T1.*,
    concat(a.lastname,', ',a.firstname) as assignee_fullname,
    concat(b.lastname,', ',b.firstname) as assignor_fullname,
    IF(T1.due_date<NOW(), 1, 0) AS overdue,
    IF(T1.due_date<NOW(), ROUND(DATEDIFF(NOW(), T1.due_date)/7), 0) as wks_overdue,
    IF(T1.due_date<NOW() && T1.status<>'CLOSED',
        CONCAT('<img src="/shared/bluesphere/32x32/actions/kill.png" alt="This task is late."><br>',TO_DAYS(now())-TO_DAYS(T1.due_date),' days'),
        NULL
    ) AS overdue_icon,
    (SELECT comment FROM tasks_comments
       WHERE parent_id=T1.id ORDER BY date DESC LIMIT 1
    ) AS lastcomment,
    (SELECT date FROM tasks_comments
       WHERE parent_id=T1.id ORDER BY date DESC LIMIT 1
    ) AS last_comment_date
FROM
    tasks AS T1
    LEFT JOIN people AS a ON T1.assignee=a.id
    LEFT JOIN people AS b ON T1.assignor=b.id
    LEFT JOIN com_modules AS m ON T1.ticket_module_id=m.id
WHERE
    $WHERE
ORDER BY
    $ORDERBY
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byOpenByConstraints($a, $orderBy = '')
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        $WHERE .= " AND T1.status<>'CLOSED' ";

        return self::byConstraints($WHERE, $orderBy);
    }

    public static function getProjectOpenTasksByConstraints($a)
    {
        $WHERE = <<<EOF
            $a
            AND T1.status LIKE 'OPEN'
            AND T1.module LIKE 'TTS'
            AND T1.parent_id IN (SELECT id FROM mis_tts WHERE status<>'QUEUE')
EOF;

        return self::byConstraints($WHERE);
    }

    public static function getNoneProjectOpenTasksByConstraints($a)
    {
        $WHERE = <<<EOF
            $a
            AND T1.status LIKE 'OPEN'
            AND (
                T1.module<>'TTS'
                OR (T1.module LIKE 'TTS' AND (
                    T1.parent_id=0
                    OR T1.parent_id IN (SELECT id FROM mis_tts WHERE status LIKE 'QUEUE'))
                )
            )
EOF;

        return self::byConstraints($WHERE);
    }

    /**
     * Get latest tasks
     *
     * Gets tasks latest tasks added
     *
     * @param string $m
     * @param string $orderby
     * @param string $operand
     *
     * @return array of db rows
     *
     */
    public static function byQuery($m = '', $orderby = '', $operand = '')
    {
        $WHERE = is_array($m) ? tldUtils::constructWhere($m, $operand) : '';
        $ORDERBY = is_array($orderby) ? implode(',', $orderby) : ' assignee, due_date';

        $query = <<<EOF
        SELECT T1.*,
            concat(a.lastname,', ',a.firstname) as assignee_fullname,
            concat(b.lastname,', ',b.firstname) as assignor_fullname
        FROM tasks AS T1 LEFT JOIN people AS a ON T1.assignee=a.id
        LEFT JOIN people AS b ON T1.assignor=b.id
        WHERE $WHERE
        ORDER BY $ORDERBY
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byClosedTTSByPeriod($start, $end, $m = '')
    {
        $WHERE = is_array($m) ? tldUtils::constructWhere($m) : '';

        $query = <<<EOF
        SELECT T1.*,
            concat(a.lastname,', ',a.firstname) as assignee_fullname,
            concat(b.lastname,', ',b.firstname) as assignor_fullname,
            bu.location AS bu_fullname
        FROM tasks AS T1 LEFT JOIN people AS a ON T1.assignee=a.id
        LEFT JOIN people AS b ON T1.assignor=b.id
        LEFT JOIN locations AS bu ON bu.id=b.bu_id
        WHERE dt_closed >='$start' AND dt_closed <= date_add('$end', interval 1 day)
        AND $WHERE
        ORDER BY assignor
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function searchByUser($uid, $search, $options)
    {
        $additionalFilter = '';
        if (!empty($options['seq'])) {
            $additionalFilter .= " AND seq='Y' ";
        }
        if (!empty($options['opened'])) {
            $additionalFilter .= " AND status != 'CLOSED' ";
        }
        // Fetch all tasks id where the description or the poster's comment include the searched term
        $query = <<<SQL
        SELECT DISTINCT tasks_comments.parent_id
FROM tasks_comments
LEFT JOIN tasks ON tasks.id = tasks_comments.parent_id
WHERE tasks_comments.poster = $uid AND (tasks_comments.comment LIKE '%$search%' OR tasks.task LIKE '%$search%');
SQL;
        $ownComments = array_column(tldUtils::getSqlToAssocArray($query), 'parent_id');

        // Fetch all tasks id where user is assignee or assignor the description include the searched term
        $query = <<<SQL
SELECT id
FROM tasks
WHERE (assignor = $uid OR assignee = $uid) AND task LIKE '%$search%'
$additionalFilter
SQL;
        $ownTasks = array_column(tldUtils::getSqlToAssocArray($query), 'id');

        // Fetch all tasks id where user is assignee or assignor to search in all comments in the next query
        $query = <<<SQL
SELECT id FROM tasks
WHERE (assignor = $uid OR assignee = $uid)
$additionalFilter
SQL;
        $ids = implode(',', array_column(tldUtils::getSqlToAssocArray($query), 'id'));

        $query = <<<SQL
SELECT DISTINCT parent_id
FROM tasks_comments
WHERE parent_id IN ($ids) AND (tasks_comments.comment LIKE '%$search%');
SQL;
        $comments = $ids ? array_column(tldUtils::getSqlToAssocArray($query), 'parent_id') : [];

        $ids = implode(',',array_unique(array_merge($ownComments, $ownTasks, $comments)));
        $query = <<<SQL
SELECT
    t.id,
    t.module,
    t.parent_id,
    t.cat,
    t.status,
    t.date,
    t.dt_closed,
    t.due_date,
    CONCAT(a.lastname, ', ', a.firstname) as assignee_fullname,
    CONCAT(b.lastname, ', ', b.firstname) as assignor_fullname,
    t.task
FROM tasks AS t
LEFT JOIN people AS a ON t.assignee = a.id
LEFT JOIN people AS b ON t.assignor = b.id
WHERE t.id IN ($ids)
$additionalFilter
SQL;
        return $ids ? tldUtils::getSqlToAssocArray($query) : [];
    }

    public static function search($a)
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
    T1.id,
    T1.*,
    CONCAT(a.lastname,', ',a.firstname) as assignee_fullname,
    CONCAT(b.lastname,', ',b.firstname) as assignor_fullname,
    b.email as assignor_email
FROM tasks AS T1
    LEFT JOIN people AS a ON T1.assignee=a.id
    LEFT JOIN people AS b ON T1.assignor=b.id
WHERE
    $WHERE
GROUP BY
    T1.id
LIMIT 500
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Is the task closed?
     *
     * @return bool
     */
    public function isClosed()
    {
        return $this->getField('status') === 'CLOSED';
    }

    /**
     * Is the task paused?
     *
     * @return bool
     */
    public function isPaused()
    {
        return $this->getField('status') === 'PAUSE';
    }
    /**
     * Is the task tag?
     *
     * @return bool
     */
    public function isTag()
    {
        return $this->getField('ifactor') == 1000;
    }

    public function close($v = '')
    {
        $SET = ($v['hours'] ?? null) ? ', hours=' .(integer)$v['hours'] : '';

        $query = <<<EOF
        UPDATE tasks
        SET status='CLOSED',
            dt_closed=now()
            $SET
        WHERE id=$this->itsID
EOF;

        return tldUtils::sqlQuery($query);
    }

    public function pause()
    {
        $query = <<<EOF
        UPDATE tasks
        SET status='PAUSE'
        WHERE id=$this->itsID
EOF;

        return tldUtils::sqlQuery($query);
    }

    public function unpause()
    {
        $query = <<<EOF
        UPDATE tasks
        SET status='IN PROGRESS'
        WHERE id=$this->itsID
EOF;

        return tldUtils::sqlQuery($query);
    }

    public function getStatus()
    {
        return $this->getField('status');
    }

    public static function getStatusList()
    {
        return array_merge(self::getOpenStatusList(), self::getClosedStatus());
    }

    public static function getOpenStatusList()
    {
        return ['OPEN', 'IN PROGRESS', 'ACCEPT', 'REJECT', 'PAUSE'];
    }

    public static function getClosedStatus()
    {
        return ['CANCEL', 'CLOSED'];
    }

    /**
     * Change status of task
     *
     * @param string $status string text of status to change to
     *
     * @return string on error
     */
    public function changeStatus($status)
    {
        if (!in_array($status, self::getStatusList(), true)) {
            return false;
        }
        if (in_array($status, self::getClosedStatus(), true)) {
            return $this->close();
        }
        $query = <<<EOF
UPDATE tasks
SET status='$status'
WHERE id=$this->itsID
EOF;

        return tldUtils::sqlQuery($query);
    }

    /**
     * Add comment to task
     *
     * see notes for insertComment in tldTaskComment object
     *
     * @param string $p comment to add to task
     *
     * @return bool
     */
    public function addComment($p)
    {
        if ($this->isClosed()) {
            return 'ERROR: Cannot ADD COMMENT to a closed task.';
        }

        return tldTaskComment::insertComment($this->itsID, $p);
    }

    /**
     * Create a new task in the database and return new ID#
     *
     * input array
     * <code>
     * array("assignee"=>"task assignee id number",
     *        "assignor"=>"assignor id number",
     *        "task"=>"text describing the task",
     *        "due_date"=>array(    "Y"=>"year in 4 digits",
     *                            "m"=>"2 digit month",
     *                            "d"=>"2 digit day"
     *                        ),
     *        "bu_id"=>"buid of the user"
     *            )
     * </code>
     *
     * @param integer $parent_id ID of document task is attached to
     * @param array   $vals      vals Array of values from an html quickforms object
     * @param string  $module    Name of module linked document is located in : PDC,NCR,USER
     *
     * @return integer        ID of new task in tasks table
     */
    public static function insert($parent_id, $vals, $module)
    {
        if (!in_array($module, self::getModuleList(), true)) {
            return "Module '$module' not registered";
        }
        //get text values and save to db
        $y = $vals['due_date']['Y'] ?? 0;
        $m = $vals['due_date']['m'] ?? 0;
        $d = $vals['due_date']['d'] ?? 0;
        // Check date entries
        $SET = '';
        if (checkdate($m, $d, $y)) {
            $SET .= ",due_date= '$y-$m-$d'";
        } elseif (is_array($vals['due_date'])) {
            $SET .= ',due_date= DATE_ADD(NOW(), INTERVAL ' .$vals['due_date']['value']. ' ' .$vals['due_date']['unit']. ')';
        } else {
            $SET .= ',due_date= DATE_ADD(NOW(), INTERVAL 14 DAY)';
        }
        // Prepare and encode close parameters
        if ($vals['close_params'] ?? null) {
            $close_params = base64_encode(serialize($vals['close_params']));
            $SET .= ",close_params	= '$close_params'";
        }
        if (null !== ($vals['ticket_module_id'] ?? null)) {
            $SET .= ',ticket_module_id = '.$vals['ticket_module_id'];
        }
        foreach (['erp', 'bu_id', 'assignee', 'assignor', 'hours', 'task', 'seq', 'seq_mode', 'tplno', 'cur_step', 'cat', 'escalation_trigger', 'reason'] as $field) {
            if (!isset($vals[$field])) {
                $vals[$field] = null;
            }
        }

        $vals["task"] = str_replace('"', "'", $vals["task"]);

        $query = <<<EOF
            INSERT INTO tasks
            SET
                parent_id		= '$parent_id',
                module			= '$module',
                status			= 'OPEN',
                date			= NOW(),
                erp				= '{$vals["erp"]}',
                bu_id			= '{$vals["bu_id"]}',
                assignee		= '{$vals["assignee"]}',
                assignor		= '{$vals["assignor"]}',
                hours           = '{$vals["hours"]}',
                task			= "{$vals["task"]}",
                seq				= '{$vals["seq"]}',
                seq_mode        = '{$vals["seq_mode"]}',
                tplno			= '{$vals["tplno"]}',
                cur_step		= '{$vals["cur_step"]}',
                cat             = '{$vals["cat"]}',
                escalation_trigger	= '{$vals["escalation_trigger"]}',
                reason          = '{$vals["reason"]}' 
                $SET
EOF;
        return tldUtils::sqlInsert($query);
    }

    /**
     * Function to update task field
     *
     * @param              $a      array
     * @param array|string $fields array
     *
     * @return string on error
     */
    public function update($a, $fields = '')
    {
        if (empty($a)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "UPDATE tasks SET $SET WHERE id=$this->itsID";

        return tldUtils::sqlQuery($query);
    }

    /**
     * Delete tasks related to module and parent_id
     *
     * @param string  $module name of module
     * @param integer $id     key of related parent document
     *
     * @return string on error
     */
    public static function deleteTask($module, $id)
    {
        if (empty($module) || empty($id)) {
            return;
        }
        if ($module === 'SEQ' || $module === 'USER') {
            $query = <<<EOF
            DELETE FROM tasks
            WHERE module='$module' AND id=$id
EOF;
        } else {
            $query = <<<EOF
            DELETE FROM tasks
            WHERE module='$module' AND parent_id=$id
EOF;
        }

        return tldUtils::sqlQuery($query);
    }

    /**
     * Move the task to a new parent document or TTS queue
     *
     * @param integer    $parent_id parent id# of new document to link to
     * @param string     $module    Name of module that new parent is in
     * @param int|string $assignee  id of assignee to move task to
     *
     * @return bool
     */
    public function move($parent_id, $module, $assignee = '')
    {
        if (empty($this->itsID)) {
            return;
        }
        $SET = $assignee ? "assignee=$assignee," : '';

        $query = <<<EOF
        UPDATE tasks
        SET $SET
        parent_id=$parent_id,
        module='$module'
        WHERE id=$this->itsID
EOF;

        return tldUtils::sqlQuery($query);
    }

    /**
     *  transfer task to new user $id
     *
     * @param integer $id ID of user to transfer the task to
     *
     * @return mixed Returns result from sqlQuery function
     */
    public function transfer($id)
    {
        if (empty($this->itsID)) {
            return 'ERROR: Could not transfer. No parent id set.';
        }
        if ($this->isClosed()) {
            return 'ERROR: Cannot TRANSFER a closed task.';
        }
        if (empty($id)) {
            return 'ERROR: Could not transfer. Transferee not set.';
        }

        global $user;

        $dueDate = new \DateTime($this->itsHeader['due_date']);
        $escalationTrigger = $this->itsHeader['escalation_trigger'] ?: 60;
        $escalationDate = clone $dueDate;
        $escalationDate->modify("+ $escalationTrigger days");

        // prevent an unfair auto escalation in the night after the transfer to someone else
        if ($user && $id !== $this->getAssignee() && new DateTime() >= $escalationDate) {
            $previousDueDate = $dueDate->format('Y-m-d');
            $newDueDate = $dueDate->add(new DateInterval('P1D'))->format('Y-m-d');
            $this->reschedule($newDueDate);
            $this->addComment([
                'poster' => $user->getId(),
                'comment' => sprintf('Due date has been automatically rescheduled from %s to %s to prevent auto escalation for the new assignee', $previousDueDate, $newDueDate)
            ]);
        }

        $id = TldDatabase::escape($id);
        $query = <<<EOF
            UPDATE tasks
            SET assignee=$id
            WHERE id=$this->itsID
EOF;
        $result = tldUtils::sqlQuery($query);
        $this->refresh();

        return $result;
    }

    /**
     *  escalate task to new assignee's manager
     *
     * @return mixed Returns result from sqlQuery function
     */
    public function escalate()
    {
        if (empty($this->itsID)) {
            return 'ERROR: Could not escalate. No parent id set.';
        }
        if ($this->isClosed()) {
            return 'ERROR: Cannot escalate a closed task.';
        }
        $assignee = new tldUser($this->getAssignee());
        $supervisor = $assignee->getSupervisor();
        if (empty($supervisor)) {
            return 'ERROR: Could not escalate. Supervisor not set.';
        }
        $query = <<<EOF
            UPDATE tasks
            SET assignee=$supervisor,
            d_escal=NOW()
            WHERE id=$this->itsID
EOF;
        $result = tldUtils::sqlQuery($query);
        $this->refresh();

        return $result;
    }

    /**
     * reschedule task to new date
     *
     * @param string date Date task is to be rescheduled to in mySQL date format
     *
     * @return mixed Returns result from sqlQuery function
     */
    public function reschedule($date)
    {
        if ($this->isClosed()) {
            return 'ERROR: Cannot RESCHEDULE a closed task.';
        }
        if (empty($this->itsID)) {
            return 'ERROR: Could not transfer. No parent id set.';
        }
        if (empty($date)) {
            return 'ERROR: Could not transfer. New DATE not set.';
        }
        $date = TldDatabase::escape($date);
        $query = <<<EOF
            UPDATE tasks
            SET due_date='$date'
            WHERE id=$this->itsID
EOF;
        $result = tldUtils::sqlQuery($query);
        $this->refresh();

        return $result;
    }

    public function estimatedHours($hours)
    {
        if ($this->isClosed()) {
            return 'ERROR: Cannot RESCHEDULE a closed task.';
        }
        if (empty($this->itsID)) {
            return 'ERROR: Could not transfer. No parent id set.';
        }
        if (empty($hours)) {
            return 'ERROR: Could not change Estimated Time of Completion. New time not set.';
        }
        $hours = TldDatabase::escape($hours);
        $query = <<<EOF
            UPDATE tasks
            SET hours='$hours'
            WHERE id=$this->itsID
EOF;
        $result = tldUtils::sqlQuery($query);
        $this->refresh();

        return $result;
    }

    public function updatetrigger($days)
    {
        if ($this->isClosed()) {
            return 'ERROR: Cannot RESCHEDULE a closed task.';
        }
        if (empty($this->itsID)) {
            return 'ERROR: Could not transfer. No parent id set.';
        }
        if (empty($days)) {
            return 'ERROR: Could not change scalation trigger of Completion. New time not set.';
        }
        $days = TldDatabase::escape($days);
        $query = <<<EOF
            UPDATE tasks
            SET escalation_trigger='$days'
            WHERE id=$this->itsID
EOF;
        $result = tldUtils::sqlQuery($query);
        $this->refresh();

        return $result;
    }

    public function triggerMISWorkflow($user)
    {
        // if user not MIS, do nothing
        if (!$user->isInGroup(['gg_MIS'])) {
            return;
        }
        // if task is sequence, do nothing
        if ($this->isSequence()) {
            return;
        }
        // if task is OPEN, update auto to IN_PROGRESS
        if ($this->getStatus() === 'OPEN') {
            return $this->changeStatus('IN PROGRESS');
        }

        return;
    }

    //NOTIFICATION FUNCTIONS

    /**
     * Send email to the assignee
     *
     * By default also cc's the assignor the same message
     *
     * @param        array  cc Array of emails to cc email to
     *
     * @param string $subject
     * @param string $cc
     * @param array $bcc
     *
     * @return bool
     */
    public function notifyAssignee($message, $subject = '', $cc = '', array $bcc = [])
    {
        $assignee = new tldUser($this->getAssignee());
        $assignor = new tldUser($this->getAssignor());
        if (empty($subject)) {
            $subject = 'Task# ' .$this->itsID. ' updated';
        }
        if (is_array($cc)) {
            $cc = implode(',', $cc);
        }
        if (!empty($cc)) {
            $cc = ",$cc";
        }
        // Add common info from task to the message
        $this->refresh();
        $header = tldUtils::renderHtmlOrNl2br($this->getTask());
        $body = <<<EOF
$message <br/>
Task Description:
$header
EOF;
        $module = $this->getModule();
        $parentID = $this->getParentID();
        if (!empty($module) && !empty($parentID) && $module !== 'SEQ') {
            switch (true) {
                case 'DMS' === $module:
                    $link = $this->getModuleLink().$parentID;
                    break;
                case in_array($module,tldModLink::getMigratedModules()):
                    $link = 'https://www.tld-gse.com' .$this->getModuleLink().$parentID.'/show';
                    break;

                default:
                    $link = 'https://www.tld-gse.com' .$this->getModuleLink().$parentID;
                    break;
            }
            $body .= <<<EOF
<br/><br/>Module: <a href="$link">$module#$parentID</a>
EOF;
        }

        return tldUtils::emailAttachment(
            $assignee->getEmail(),
            'noreply@tld-gse.com',
            $subject,
            $body,
            null,
            $assignor->getEmail().$cc,
            $bcc
        );
    }

    /**
     * Emails notifications
     *
     * Emails the assignee and cc's assignor 7 days before due date, on the due date
     * and every 7 days after due date.
     *
     * After due date, the assignee's suprvisor is also cc'd
     *
     * @param string url Optional url to link to in email message
     *
     * @return boolean Result from mail function
     */
    public function emailNotifications(
        $url = 'https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view'
    ) {
        $query = <<<EOF
        SELECT TO_DAYS(due_date)-TO_DAYS(now()) AS daysDue,
            tasks.*
        FROM tasks
        HAVING daysDue%7 = 0
            AND daysDue <= 7
            AND tasks.status<>'CLOSED'
EOF;
        $tasks = tldUtils::getSqlToAssocArray($query);
        if (count($tasks)) {
            foreach ($tasks as $task) {
                $assignee = new tldUser($task['assignee']);
                $assignee_fullname = $assignee->getFullname();
                $assignor = new tldUser($task['assignor']);
                $assignor_email = $assignor->getEmail();
                $message2 = '';
                $cc = '';
                if ($task['daysDue'] == 7) {
                    $message2 .= 'DUE IN 7 DAYS. ';
                } elseif ($task['daysDue'] == 0) {
                    $message2 .= 'DUE TODAY. ';
                } elseif ($task['daysDue'] < 0 && $task['daysDue'] % 7 == 0) {
                    $message2 .= ($task['daysDue'] * -1). ' DAYS OVER DUE.';
                    //get supervisor info
                    $supervisor = new tldUser($assignee->getSupervisor());
                    $cc = ',' .$supervisor->getEmail();
                }
                $message = <<<EOF
                <p>This is to notify you that {$task['module']} task #{$task['id']} which is assigned to $assignee_fullname,  is
                <b>$message2</b></p>
                <a href="$url&id={$task['id']}">Click here to see task.</a>
                <hr>
                <p>{$task['task']}</p>
EOF;
                tldUtils::emailAttachment(
                    $assignee->getEmail(),
                    'noreply@tld-gse.com',
                    "{$task['module']} Task #{$task['id']} - $message2 ",
                    $message,
                    null,
                    $assignor_email.$cc
                );
            }
        }
    }

    /**
     * Notify all assignees of delinquent tasks
     *
     * @return null
     */
    public static function emailNotifyDelinquent()
    {
        $rows = self::byDelinquent();
        if (count($rows) == 0) {
            return;
        }
        echo 'Number of delinquent tasks - ' .count($rows)."\n";
        foreach ($rows as $row) {
            echo $row['id']. ' - ' .$row['assignee']. ' - ' .$row['supervisor'];
            echo "\n";
            $id = $row['id'];
            $task = new tldTask($id);
            $error = $task->escalate();
            if ($error) {
                echo "Could not escalate task #$id. There was an error processing. The error returned is '$error'\n";
                continue;
            }
            $subject = "Task #$id has been AUTO ESCALATED from {$row['assignee_fullname']} to ".$row['supervisor_fullname'];
            echo "$subject\n";
            $message = <<<EOF
            $subject\n<br>
            <b>This task has been AUTOMATICALLY ESCALATED:</b>
            This task belonging to {$row['assignee_fullname']} is over {$row['escalation_trigger']} days late and has been escalated to you for handling.
            <hr>
            Please log in to the TLD-GSE intranet and go to the calendar module to view your open tasks.\n<br>
            <a href="https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$id">
            Click here to go to Task.
            </a><br><br>
            <b>Task:</b><br>
EOF;
            $message .= $task->getTask();
            $task->addComment(['poster' => $row['assignee'], 'comment' => "$subject"]);
            $task->notifyAssignee($message, $subject, $row['assignee_email']);
        }
    }

    public static function autoReschedulePausedTTS()
    {
        $query = <<<EOF
            UPDATE tasks
            SET due_date=NOW()
            WHERE status LIKE 'PAUSE' AND due_date<NOW()
EOF;
        tldUtils::sqlQuery($query);
    }

    /**
     * Get count of tasks per module
     *
     * @return array Array of db rows
     */
    public static function getStatsByModule()
    {
        $query = <<<EOF
        SELECT DISTINCT T1.module,
          COUNT(*) AS totalNum,
          (SELECT COUNT(*) FROM tasks AS T2 WHERE T2.module=T1.module AND status<>'CLOSED') AS numOpen,
          (SELECT ROUND(AVG(DATEDIFF(T4.dt_closed, T4.date)), 2)
            FROM tasks AS T4
            WHERE T4.module=T1.module AND T4.status='CLOSED') AS avgDays
        FROM tasks AS T1
        GROUP BY T1.module
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function getForecastByUserid($id)
    {
        if (empty($id)) {
            return;
        }
        $query = <<<EOF
        select
    people.lastname AS assignee_lastname,
    assignee,
    module,
    COUNT(IF(DATEDIFF(due_date, NOW()) < 0, 1, NULL)) AS late,
    COUNT(IF(DATEDIFF(due_date, NOW()) >= 0 AND DATEDIFF(due_date, NOW()) < 7, 1, NULL)) AS due_in_7,
    COUNT(IF(DATEDIFF(due_date, NOW()) >= 7 AND DATEDIFF(due_date, NOW()) < 14, 1, NULL)) AS due_in_14,
    COUNT(IF(DATEDIFF(due_date, NOW()) >= 14 AND DATEDIFF(due_date, NOW()) < 30, 1, NULL)) AS due_in_30,
    COUNT(IF(DATEDIFF(due_date, NOW()) >= 30 AND DATEDIFF(due_date, NOW()) < 60, 1, NULL)) AS due_in_60,
    COUNT(IF(DATEDIFF(due_date, NOW()) >= 60, 1, NULL)) AS due_after_60
from tasks LEFT JOIN people ON tasks.assignee=people.id
where
 status<>'CLOSED'
 and assignee=$id
GROUP BY assignee, module
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * @param array|string $constraints
     */
    public static function getGanttOverview($module = '', $parent_id = '', $constraints = '')
    {
        $WHERE = [];
        if ($module) {
            $WHERE[] = "tasks.module='$module'";
        }
        if ($parent_id) {
            $WHERE[] = "tasks.parent_id=$parent_id";
        }
        if (!empty($constraints)) {
            if (is_array($constraints)) {
                $WHERE[] = tldUtils::constructWhere($constraints);
            } else {
                $WHERE[] = $constraints;
            }
        }
        $WHERE = implode(' AND ', $WHERE);
        $query = <<<EOF
        SELECT
            tasks.*,
            (
                SELECT
                    CONCAT(UPPER(lastname),', ',firstname)
                FROM
                    people
                WHERE
                    people.id=tasks.assignor
            ) AS assignor_fullname,
            (
                SELECT
                    UPPER(lastname)
                FROM
                    people
                WHERE
                    people.id=tasks.assignor
            ) AS assignor_lastname,
            (
                SELECT
                    CONCAT(UPPER(lastname),', ',firstname)
                FROM
                    people
                WHERE
                    people.id=tasks.assignee
            ) AS assignee_fullname,
            (
                SELECT
                    UPPER(lastname)
                FROM
                    people
                WHERE
                    people.id=tasks.assignee
            ) AS assignee_lastname,
            (
                SELECT
                    location
                FROM
                    locations
                WHERE
                    locations.id=tasks.bu_id
            ) AS bu_fullname,
            (
                SELECT
                    name
                FROM
                    cal_seq_tpl
                WHERE
                    cal_seq_tpl.id=tasks.tplno
            ) AS tpl_fullname,
            IF(tasks.due_date<NOW() AND tasks.status<>'CLOSED', TO_DAYS(NOW())-TO_DAYS(tasks.due_date), 0) AS days_late,
            IF(tasks.due_date<NOW() AND tasks.status<>'CLOSED', ROUND(DATEDIFF(NOW(), tasks.due_date)/7), 0) AS wks_overdue,
            IF(tasks.due_date<NOW() AND tasks.status<>'CLOSED', 1, 0) AS overdue,
            (
                SELECT
                    GROUP_CONCAT(
                        CONCAT(
                            tasks_comments.date,
                            ' by ',
                            (
                                SELECT
                                    CONCAT(UPPER(lastname),', ',firstname)
                                FROM
                                    people
                                WHERE
                                    people.id=tasks_comments.poster
                            ),
                            "\n",
                            tasks_comments.comment
                        )
                        ORDER BY tasks_comments.id DESC
                        SEPARATOR "\n\n"
                    )
                FROM
                    tasks_comments
                WHERE
                    tasks_comments.parent_id=tasks.id
                LIMIT
                    10
            ) AS last_ten_comments
        FROM
            tasks
        WHERE
            $WHERE
        ORDER BY
            tasks.date, tasks.id
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public function requireAssignorCompletion(): bool
    {
        return in_array($this->itsHeader['module'], self::NEED_ASSIGNOR_FOR_COMPLETION, true);
    }

    public function canComplete($user): bool
    {
        if (!$this->requireAssignorCompletion()) {
            return true;
        }

        if ($user->isInGroup("superuser")) {
            return true;
        }

        if ($user->itsId === $this->getAssignor()) {
            return true;
        }

        return false;
    }
}

/**
 * Class for comments linked to tldTasks
 *
 * <code>
 * CREATE TABLE `tasks_comments` (
 *   `id` int(11) NOT NULL auto_increment,
 *   `parent_id` int(11) NOT NULL default '0',
 *   `step` int(11) default '0',
 *   `status` varchar(10) NOT NULL default '',
 *   `date` datetime NOT NULL default '0000-00-00 00:00:00',
 *   `poster` int(11) NOT NULL default '0',
 *   `comment` text NOT NULL,
 *   `filename` varchar(100) NOT NULL default '',
 *   PRIMARY KEY  (`id`)
 * ) TYPE=MyISAM
 * </code>
 *
 * @package Calendar
 */
class tldTaskComment
{
    /**
     * Id of task_comment in the tasks_comments tables
     *
     * @var int
     */
    public $itsID;
    /**
     * Path to where attachments will be saved
     *
     * @var string
     */
    public $itPath;
    /**
     * Header information for this comment
     *
     * @return array
     */

	/**
	 * Constructor for tldTaskComment
	 *
	 * @param integer $id ID of task comment in database
	 *
	 * @return tldTaskComment
	 */
    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsDetails = $this->getHeader($id);
    }

    /**
     * Get the header data for this comment from tasks_comments table
     *
     * @return array Returns a db row from tasks_comments table with all header information
     */
    public function getHeader()
    {
        $query = <<<EOF
        SELECT * FROM task_comments
        WHERE id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Insert a comment linked to a particular task
     *
     * @static
     *
     * @param int   $parent_id Parent id of tldTask that this comment is to link to
     * @param array $p         associative array containing the values to be inserted
     *                         valid keys are
     *                         "step"=>"step in sequence, ONLY FOR SEQUENCES"
     *                         "status"=>"status of step, ONLY FOR SEQUENCES"
     *                         "poster"=>"id number of person posting the comment"
     *                         "comment"=>"the actual comment to be posted"
     *                         "file_info"=>"html_quickform file info for attachment" e.g.
     *                         <code>
     *                         array(5) {
     *                         ["name"]=>
     *                         string(35) "2005-11-11, CAPEX, QC TS server.pdf"
     *                         ["type"]=>
     *                         string(15) "application/pdf"
     *                         ["tmp_name"]=>
     *                         string(14) "/tmp/php5mZAhg"
     *                         ["error"]=>
     *                         int(0)
     *                         ["size"]=>
     *                         int(37638)
     *                         )
     *                         </code>
     *
     * @return int On success, returns the auto inc id# from table insert
     */
    public static function insertComment($parent_id, $p)
    {
        if (empty($parent_id)) {
            $error = 'ERROR: no parent id set for comment insert.';
            error_log($error);

            return $error;
        }
        //process file, if any
        $filename = '';
        if (!empty($p['file_info']['tmp_name'])) {
            $file = new basicFile($p['file_info']['tmp_name']);
            $filename = date('Gis').basicFile::cleanupName($parent_id. '_' .$p['file_info']['name']);
            $file->copyFile(tldUtils::getPathToUploadFile('tasks_comments', $filename));
        }
        $p = tldUtils::cleanupFormInput($p);
        $insert = [];
        foreach (['status', 'step', 'poster', 'comment'] AS $allowed) {
            if (isset($p[$allowed])) {
                $insert[] = "$allowed='{$p[$allowed]}'";
            }
        }
        $insert = implode(',', $insert).',';
        $query = <<<EOF
            INSERT INTO tasks_comments
            SET
                parent_id		= '$parent_id',
                date			= now(),
                $insert
                filename		= '$filename'
EOF;

        return tldUtils::sqlInsert($query);
    }

    /**
     * Get comments as an array of db rows for a particular task
     *
     * @param integer $parent_id ID of task to get comments for
     *
     * @static
     * @return array
     */
    public function getComments($parent_id)
    {
        $query = <<<EOF
        SELECT tasks_comments.*,
        (SELECT CONCAT(lastname,', ',firstname) FROM people WHERE people.id=poster) AS poster_fullname
        FROM tasks_comments
        WHERE tasks_comments.parent_id=$parent_id
        ORDER BY tasks_comments.id DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byParent($pid)
    {
        $query = <<<EOF
        SELECT tasks_comments.*,
            (SELECT CONCAT(lastname,', ',firstname) FROM people WHERE people.id=poster) AS poster_fullname
        FROM tasks_comments
        WHERE tasks_comments.parent_id=$pid
        ORDER BY tasks_comments.id DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byFileByParent($pid)
    {
        $query = <<<EOF
        SELECT tasks_comments.*,
            (SELECT CONCAT(lastname,', ',firstname) FROM people WHERE people.id=poster) AS poster_fullname
        FROM tasks_comments
        WHERE tasks_comments.parent_id=$pid AND LENGTH(tasks_comments.filename)>0
        ORDER BY tasks_comments.id DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get comments from past 7 days for particular user
     *
     * @param integer $uid userid numbers
     *
     * @param int     $days
     *
     * @return array
     */
    public function byUserRecent($uid, $days = 14)
    {
        $query = <<<EOF
            select t1.task,t2.*,
                (SELECT CONCAT(lastname,', ',firstname) FROM people WHERE people.id=poster) AS poster_fullname
            from tasks AS t1 LEFT JOIN tasks_comments AS t2 ON t1.id=t2.parent_id
            where
              (t1.assignee=63 or t1.assignor=63)
              and t1.status='OPEN'
            and datediff(NOW(), t2.date) < $days
            ORDER by t2.parent_id, t2.id DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }
}

/**
 * Class for creating and manipulating Work Group Tasks
 *
 * @package Calendar
 */
class tldGWF
{
    public $itsID;        //id of GWF in database
    public $itsHeader;    //Header information from database

	/**
	 * Constructor
	 *
	 * @param integer $id id of GWF in database
	 *
	 * @return tldGWF
	 */
    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Is the GWF object valid?
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    /**
     * Get the header information for the GWF from the database
     *
     * @return array
     */
    public function getHeader()
    {
        $query = <<<EOF
        SELECT *,
            (SELECT concat(people.lastname,', ',people.firstname)
            FROM people WHERE people.id=gwf.assignor) AS assignor_fullname,
            (SELECT mod_logs.comment
            FROM mod_logs WHERE mod_logs.parent_id=$this->itsID AND mod_logs.module = 'GWF'
            ORDER BY id DESC LIMIT 1) AS last_comment,
            (SELECT location FROM locations WHERE gwf.bu=locations.id) AS bu_fullname,
            ROUND(IF(dt_closed LIKE '0000%',
                DATEDIFF(NOW(), date),
                DATEDIFF(dt_closed, date)
            )/7) AS wks_open
        FROM gwf
        WHERE id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Get the conclusion comment enter for the GWF when closed, from the database
     *
     * @return array Array
     */
    public function getConclusion()
    {
        $query = <<<EOF
        SELECT comment,
            (SELECT concat(people.lastname,', ',people.firstname)
            FROM people WHERE people.id=mod_logs.poster) AS poster_fullname
        FROM mod_logs
        WHERE parent_id=$this->itsID
        AND module = "GWF"
        AND comment LIKE "CLOSED%"
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Get current status of the GWF
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->itsHeader['status'];
    }

    /**
     * Get assignor of the GWF
     *
     * @return string
     */
    public function getAssignor()
    {
        return $this->itsHeader['assignor'];
    }

    /**
     * Get Short Description of the GWF
     *
     * @return string
     */
    public function getDsca()
    {
        return $this->itsHeader['dsca'];
    }

    /**
     * Get all related tasks to the GWF
     *
     * @return array Array of db rows
     */
    public function getTasks()
    {
        return tldTask::byParent($this->itsID, 'GWF', 'ALL');
    }

    public function getOpenTasks()
    {
        return tldTask::byParent($this->itsID, 'GWF', '');
    }

    /**
     * Get all related files to the GWF
     *
     * @return array Array of db rows
     */
    public function getFiles()
    {
        return tldModFile::byParent($this->itsID, 'GWF');
    }

    public function getFileByTasks($mode = '')
    {
        return tldTask::byFileParent($this->itsID, 'GWF', $mode);
    }

    /**
     * Get all related links to the GWF
     *
     * @return array Array of db rows
     */
    public function getLinks()
    {
        return tldModLink::byParent($this->itsID, 'GWF');
    }

    public function isPrivate()
    {
        return $this->itsHeader['pvt'] === 'Y';
    }

    /**
     * Get all related logs to the GWF
     *
     * @return array Array of db rows
     */
    public function getLogs()
    {
        return tldModLog::byParent($this->itsID, 'GWF');
    }

    /**
     * Add a comment to the log
     *
     * @param $id
     * @param $comment
     *
     * @return bool
     *
     */
    public function addLogEntry($id, $comment)
    {
        $a['parent_id'] = $this->itsID;
        $a['module'] = 'GWF';
        $a['poster'] = $id;
        $a['comment'] = $comment;

        return tldModLog::insert($a);
    }

    /**
     * Change status of GWF
     *
     * @param string $status string text of status to change to
     *
     * @return string on error
     */
    public function changeStatus($status = '')
    {
        if (empty($this->itsID)) {
            return false;
        }
        //normalize status
        $list = ['CLOSED', 'OPEN'];
        //if no status given then return all available statuses
        if (empty($status)) {
            return $list;
        }
        $status = strtoupper($status);
        if (!in_array($status, $list)) {
            return 'Status not allowed';
        }
        $SET = $status === 'CLOSED' ? ',dt_closed=NOW()' : '';

        $query = <<<EOF
            UPDATE gwf
            SET status='$status'
            $SET
            WHERE id=$this->itsID
EOF;

        return tldUtils::sqlQuery($query);
    }

    public function disablePicture()
    {
        $query = <<<EOF
            UPDATE gwf
            SET picture_filename=''
            WHERE id=$this->itsID
EOF;

        return tldUtils::sqlQuery($query);
    }

    public function getMembers()
    {
        return tldModMember::byParent($this->itsID, 'GWF');
    }

    /**
     * Check if user with UID is a member of this GWF
     */
    public function isMember($uid)
    {
        if ($this->getAssignor() == $uid) {
            return true;
        }

        return tldModMember::isMember('GWF', $this->itsID, $uid);
    }

	/**
	 * Insert a new GWF into database
	 *
	 * @param array $a parameters for inserting into database table
	 *                 <code>
	 *                 array("ctg"=>"gwf category", "model"=>"machine model", "type"=>"machine type",
	 *                 "bu"=>"business unit", "assignor"=>"id of person creating the gwf",
	 *                 "dsca"=>"short description of gwf goal", "dscb"=>"full description of GWF goal",
	 *                 "dest"=>"estimated completion date")
	 *                 </code>
	 *
	 * @return mixed
	 */
    public static function insert($a)
    {
        if (count($a['dest'])) {
            $a['dest'] = implode('-', $a['dest']);
        }
        $fields = [
            'pvt',
            'ctg',
            'ifactor',
            'model',
            'type',
            'bu',
            'assignor',
            'dsca',
            'dscb',
            'dest',
            'kwd1',
            'kwd2',
            'kwd3',
            'kwd4',
            'kwd5',
            'payload',
            'payload_class',
        ];
        //set defaults
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = <<<EOF
        INSERT INTO gwf
        SET
        $SET
EOF;
        $id = tldUtils::sqlInsert($query);
        if (is_numeric($id) && count($a['members'] ?? [])) {
            $gwf = new tldGWF($id);
            $gwf->addMember($a['members']);
        }

        return $id;
    }

    public function addMember($members)
    {
        if (is_array($members)) {
            foreach ($members AS $member) {
                tldModMember::insert('GWF', $this->itsID, $member);
            }
        } elseif (!empty($members)) {
            tldModMember::insert('GWF', $this->itsID, $members);
        }
    }

    /**
     * Get all GWF by assignor
     *
     * @param integer $id id of assignor to look for
     *
     * @return array Array of db rows
     */
    public static function byAssignor($id)
    {
        $query = <<<EOF
        SELECT *,
            (SELECT concat(people.lastname,', ',people.firstname)
            FROM people WHERE people.id=gwf.assignor) AS assignor_fullname,
            ROUND(IF(dt_closed LIKE '0000%',
                DATEDIFF(NOW(), date),
                DATEDIFF(dt_closed, date)
            )/7) AS wks_open
        FROM gwf
        WHERE assignor=$id
        ORDER BY id DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function countByCategoryLocation($opt = [])
    {
        $WHERE = !isset($opt['includeClosed']) ? "WHERE status !='CLOSED'" : '';

        $query = <<<EOF
            select ctg, locations.location, count(*) as num
            from gwf LEFT JOIN locations ON gwf.bu=locations.id
            $WHERE
            group by ctg, locations.location
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byCategoryLocation($ctg, $location, $opt = [])
    {
        $a = [];
        $WHERE = !$opt['includeClosed'] ? " AND status!='CLOSED' " : '';

        if ($ctg !== 'ALL') {
            $a['ctg'] = $ctg;
        }
        if ($location !== 'ALL') {
            $a['location'] = $location;
        }
        $w = tldUtils::constructWhere($a);
        $WHERE .= $w ? " AND $w" : '';

        $query = <<<EOF
            SELECT gwf.*,
                (SELECT concat(people.lastname,', ',people.firstname)
                FROM people WHERE people.id=gwf.assignor) AS assignor_fullname,
                locations.location AS bu_fullname,
                ROUND(IF(dt_closed LIKE '0000%',
                    DATEDIFF(NOW(), date),
                    DATEDIFF(dt_closed, date)
                )/7) AS wks_open,
                (
                    SELECT count(tasks.id)
                    FROM tasks 
                    WHERE tasks.parent_id=gwf.id
                    AND tasks.module LIKE 'GWF'
                    AND tasks.status NOT LIKE 'CLOSED'
                ) AS nb_tasks,
                (
                    SELECT mod_logs.comment
                    FROM mod_logs WHERE mod_logs.parent_id=gwf.id AND mod_logs.module = 'GWF'
                    ORDER BY id DESC LIMIT 1
                ) AS last_comment
            FROM gwf LEFT JOIN locations ON gwf.bu=locations.id
            WHERE 1=1
            $WHERE
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function bySingleKeyword($keyword)
    {
        $a = [];
        for ($i = 1; $i < 6; $i++) {
            $a["kwd$i"] = empty($keyword) ? '%' : str_replace(' ', '%', $keyword);
        }

        return self::byConstraints($a, ['op' => 'OR', 'orderBy' => ' status DESC, id']);
    }

    /**
     * find gwfs by description
     *
     * @param string $desc
     *
     * @return array
     */
    public static function byDescription($desc)
    {
        $constraints = ['dsca' => $desc];

        return self::byConstraints($constraints);
    }

    /**
     * returns array of rows by field
     *
     * @param        $constraints
     * @param string $options
     *
     * @return array
     */
    public static function byConstraints($constraints, $options = [])
    {
        $where = is_array($constraints) ? tldUtils::constructWhere($constraints, $options['op']) : $constraints;
        $orderBy = isset($options['orderBy']) ? TldDatabase::escape($options['orderBy']) : 'gwf.id';

        $query = <<<EOF
        SELECT gwf.*,
            (SELECT concat(people.lastname,', ',people.firstname)
            FROM people WHERE people.id=gwf.assignor) AS assignor_fullname,
            (
                SELECT count(tasks.id)
                FROM tasks 
                WHERE tasks.parent_id=gwf.id
                AND tasks.module LIKE 'GWF'
                AND tasks.status NOT LIKE 'CLOSED'
            ) AS nb_tasks,
            (
                SELECT mod_logs.comment
                FROM mod_logs WHERE mod_logs.parent_id=gwf.id AND mod_logs.module = 'GWF'
                ORDER BY id DESC LIMIT 1
            ) AS last_comment,
            locations.location AS bu_fullname,
            ROUND(IF(dt_closed LIKE '0000%',
                DATEDIFF(NOW(), date),
                DATEDIFF(dt_closed, date)
            )/7) AS wks_open
        FROM gwf LEFT JOIN locations ON gwf.bu=locations.id
EOF;
        if ($where) {
            $query .= " WHERE $where";
        }
        $query .= <<<EOF
        ORDER BY $orderBy
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function byDelinquent()
    {
        return self::byConstraints(
            "
(SELECT COUNT(*) FROM tasks WHERE tasks.module='GWF'
    AND tasks.parent_id=gwf.id AND tasks.status LIKE 'OPEN'
)=0
AND gwf.status LIKE 'OPEN'
        "
        );
    }

    public function pauseAllTasks()
    {
        $query = <<<EOF
            UPDATE tasks
            SET status = 'PAUSE'
            WHERE parent_id = $this->itsID AND module = 'GWF' AND status != 'CLOSED'
EOF;

        return tldUtils::sqlQuery($query);
    }

    public function duplicate(int $userid)
    {
        $a = $this->getHeader();
        if (is_array($a['dest']) === false) {
            $a['dest'] = [$a['dest']];
        }
        $a['assignor'] = $userid;
        $e = self::insert($a);
        if (is_string($e)) {
            return $e;
        }
        $gwf = new tldGWF($e);

        foreach ($this->getMembers() as $member) {
            $gwf->addMember($member['id']);
        }

        // Log
        $gwf->addLogEntry($userid, "Created from duplication of GWF#$this->itsID");

        return $e;
    }

}

/**
 * Class for creating and manipulating business processes
 *
 * @package Calendar
 */
class tldBP
{
    public $itsID;
    /** @var array  */
    public $itsHeader;

    /**
     * @param $id
     */
    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Get header information from database
     *
     * @return array Single array
     */
    public function getHeader()
    {
        $query = <<<EOF
        SELECT cal_bp.*,
        (SELECT CONCAT(people.lastname,', ',people.firstname) FROM people WHERE people.id=cal_bp.owner) AS owner_fullname
        FROM cal_bp
        WHERE id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Get current status of BP
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->itsHeader['status'];
    }

    /**
     * Get open tasks for this process
     *
     * @param string $mode
     *
     * @return array Array of db rows
     */
    public function getTasks($mode = '')
    {
        return tldTask::byParent($this->itsID, 'BP', $mode);
    }

    /**
     * @return tldEAP|null
     */
    public function getEAP()
    {
        return $this->itsHeader['module'] === 'EAP' ? new tldEAP($this->itsHeader['parent_id']) : null;
    }

    /**
     * @return tldMEAP|null
     */
    public function getMEAP()
    {
        return $this->itsHeader['module'] === 'MEAP' ? new tldMEAP($this->itsHeader['parent_id']) : null;
    }

    /**
     * Get list of related files from mod_files system
     *
     * @return array
     */
    public function getFiles()
    {
        return tldModFile::byParent($this->itsID, 'BP');
    }

    /**
     * Is the BP closed yet?
     *
     * @return boolean
     */
    public function isClosed()
    {
        return $this->itsHeader['status'] === 'CLOSED';
    }

    /**
     * Get linked log entries
     *
     * @return array
     */
    public function getLog()
    {
        return tldModLog::byParent($this->itsID, 'BP');
    }

    /**
     * Add a comment to the log
     *
     * @param $id
     * @param $comment
     *
     * @return bool
     *
     */
    public function addLogEntry($id, $comment)
    {
        $a['parent_id'] = $this->itsID;
        $a['module'] = 'BP';
        $a['poster'] = $id;
        $a['comment'] = $comment;

        return tldModLog::insert($a);
    }

    /**
     * Attempt to close the bp
     *
     * @return mixed String on error
     */
    public function close($cancel = false)
    {
        if ($this->isClosed()) {
            return 'ERROR: BP is already closed';
        }
        if (empty($this->itsID)) {
            return 'ERROR: No id set for this BP';
        }
        if (count($this->getTasks())) {
            return 'ERROR: Cannot close a BP with open tasks/sequences';
        }

        $query = <<<EOF
        UPDATE cal_bp
        SET status='CLOSED',
        dt_closed=NOW()
        WHERE id=$this->itsID
EOF;
        $error = tldUtils::sqlQuery($query);
        $this->itsHeader = $this->getHeader();
        // Run closing methods:
        if ($this->itsHeader['module'] === 'MEAP') {
            include_once 'eng.inc.php';
            $meap = new tldMEAP($this->itsHeader['parent_id']);
            $meap->runBPClosingAction($cancel);
        }

        return $error;
    }

    /**
     * Create a new BP in the database
     *
     * @param integer $parent_id id of the parent document bp is linked to
     * @param array   $p         array of parameters to be inserted into the database
     * @param string  $module    name of module that parent document is located in
     *
     * @return integer id of newly created bp
     */
    public static function insert($parent_id, $p, $module)
    {
        $query = <<<EOF
        INSERT INTO cal_bp
        SET
            parent_id		  = '$parent_id',
            module			  = '$module',
            status			  = 'OPEN',
            owner			  = '{$p["owner"]}',
            dt_opened		  = now(),
            short_desc		  = '{$p["short_desc"]}',
            long_desc		  = '{$p["long_desc"]}',
            proposal_solution = '{$p["proposal_solution"]}',
            actions		      = '{$p["actions"]}'
EOF;

        return tldUtils::sqlInsert($query);
    }

    /**
     * Attempt to transfer the bp owner to a new one
     *
     * @param $uid
     *
     * @return mixed String on error
     */
    public function transfert($uid)
    {
        if (empty($uid) && !is_numeric($uid)) {
            return 'Owner ID not valid';
        }
        if ($this->isClosed()) {
            return 'BP is already closed';
        }
        if (empty($this->itsID)) {
            return 'No id set for this BP';
        }
        $query = "UPDATE cal_bp SET owner=$uid WHERE id=$this->itsID";
        $error = tldUtils::sqlQuery($query);
        $this->itsHeader = $this->getHeader();

        return $error;
    }

    /**
     * Add a task to this bp
     *
     * @param array $vals array of values passed onto the tldTask class
     *
     * @return integer ID of newly created task
     */
    public function addTask($vals)
    {
        return tldTask::insert($this->itsID, $vals, 'BP');
    }

    /**
     * Add a SEQ to this bp
     *
     * @param array $vals  values passed into the tldSEQ class
     * @param int   $tplno , seq template ID to use
     *
     * @return integer ID of newly created task
     */
    public function addSEQ($vals, $tplno)
    {
        return tldSEQ::insert($this->itsID, $vals, $tplno, 'BP');
    }

    /**
     * Get most recent processes
     *
     * @param int $n Optional max number of rows to return, defaults to 10
     *
     * @return array Array of db rows
     */
    public static function byLatest($n = 10)
    {
        $query = <<<EOF
        SELECT *
        FROM cal_bp
        ORDER BY id DESC
        LIMIT $n
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get all bps for a particular parent document
     *
     * @param integer $id     id of parent document
     * @param string  $module name of module that parent document is located
     * @param string  $mode   controls the scope of rows to return, by default returns on active BPs
     *
     * @return array Array of db rows
     */
    public static function byParent($id, $module, $mode = '')
    {
        $WHERE = $mode === '' ? " AND status!='CLOSED'" : '';

        $query = <<<EOF
        SELECT *
        FROM cal_bp
        WHERE parent_id=$id
        AND module='$module'
        $WHERE
        ORDER BY status DESC, id DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }
}

/**
 * Class for creating and manipulating sequences
 *
 * Extends tldTask and uses the same db table with added fields
 *
 * @package Calendar
 */
class tldSEQ extends tldTask
{

    public $itsMode;

    public function __construct($id)
    {
        parent::__construct($id);
        $this->itsMode = $this->getMode();
    }

    /**
     * Get expiration date
     *
     * @return string
     */
    public function getExpiration()
    {
        return $this->itsHeader['expiration'];
    }

    /**
     * Answer if sequence is complete
     *
     * @return bool
     */
    public function isCompleted()
    {
        switch ($this->itsMode) {
            case 'USER_LEVEL':
                return $this->getCurrentStep() > $this->getNumNodes();
                break;
            case 'SINGLE_LEVEL':
                $nodes = $this->getNodes("tasks_comments.status IN('ACCEPT','CANCEL')");

                return count($nodes) >= 1 ;
                break;
            default:
                $tpl = $this->getTemplate();
                if ($tpl->isParallel()) {
                    $rows = $tpl->getNodes();
                    if (count($rows)) {
                        $groups = [];
                        foreach ($rows as $row) {
                            $groups[] = $row['group_name'];
                        }
                    } else {
                        return true;
                    }
                } else {
                    return $this->getCurrentStep() > $tpl->getNumNodes();
                }
                break;
        }
    }

    /**
     * Get current step
     *
     * @return string
     */
    public function getCurrentStep()
    {
        return $this->itsHeader['cur_step'];
    }

    /**
     * Get current node object
     *
     * @return tldSEQTplNode or tldSEQUserNode object
     */
    public function getCurrentNode()
    {
        switch ($this->itsMode) {
            case 'USER_LEVEL':
                $vals = tldSEQUserNode::byConstraints(
                    ['parent_id' => $this->itsID, 'step' => $this->getCurrentStep()]
                );
                $nodeObj = new tldSEQUserNode($vals[0]['id']);
                break;
            default:
                $tpl = $this->getTemplate();
                $nodeObj = $tpl->getNode($this->getCurrentStep());
                break;
        }

        return $nodeObj;
    }

    public function getCurrentAssignor()
    {
        return $this->itsHeader['assignor'];
    }

    /**
     * Get previous node object
     *
     * @return tldSEQTplNode object
     */
    public function getPreviousNode()
    {
        switch ($this->itsMode) {
            case 'USER_LEVEL':
                if ($this->getCurrentStep() == 1) {
                    return false;
                }
                $vals = tldSEQUserNode::byConstraints(
                    ['parent_id' => $this->itsID, 'step' => $this->getCurrentStep() - 1]
                );
                $nodeObj = new tldSEQUserNode($vals[0]['id']);
                break;
            default:
                if ($this->getCurrentStep() == 1 || $this->getTPLNO() == 0) {
                    return false;
                }
                $tpl = $this->getTemplate();
                $nodeObj = $tpl->getNode($this->getCurrentStep() - 1);
                break;
        }

        return $nodeObj;
    }

    /**
     * Get next node object
     *
     * @return tldSEQTplNode object
     */
    public function getNextNode()
    {
        switch ($this->itsMode) {
            case 'USER_LEVEL':
                if ($this->getCurrentStep() == $this->getNumNodes()) {
                    return false;
                }
                $vals = tldSEQUserNode::byConstraints(
                    ['parent_id' => $this->itsID, 'step' => $this->getCurrentStep() + 1]
                );
                $nodeObj = new tldSEQUserNode($vals[0]['id']);
                break;
            default:
                if ($this->getCurrentStep() == $this->getNumNodes() || $this->getTPLNO() == 0) {
                    return false;
                }
                $tpl = $this->getTemplate();
                $nodeObj = $tpl->getNode($this->getCurrentStep() + 1);
                break;
        }

        return $nodeObj;
    }

    /**
     * Get node object at particular step
     *
     * @param $step
     *
     * @return tldSEQTplNode object
     */
    public function getNode($step)
    {
        switch ($this->itsMode) {
            case 'USER_LEVEL':
                $vals = tldSEQUserNode::byConstraints(
                    ['parent_id' => $this->itsID, 'step' => $step]
                );
                $nodeObj = new tldSEQUserNode($vals[0]['id']);
                break;
            default:
                $tpl = $this->getTemplate();
                $nodeObj = $tpl->getNode($step);
                break;
        }

        return $nodeObj;
    }

    /**
     * Get max number nodes
     *
     * @return integer
     */
    public function getNumNodes()
    {
        switch ($this->itsMode) {
            case 'USER_LEVEL':
                $numNodes = count(tldSEQUserNode::byParentID($this->itsID));
                break;
            default:
                $tpl = $this->getTemplate();
                $numNodes = $tpl->getNumNodes();
                break;
        }

        return $numNodes;
    }

    /**
     * Check if a user has rights to accept a sequence
     *
     * @param integer $userid id of user to check permissions for
     *
     * @return boolean
     */
    public function canSign($userid)
    {
        $curNode = $this->getCurrentNode();
        switch ($this->itsMode) {
            case 'USER_LEVEL':
                return $curNode->getUserID() == $userid;
                break;
            default:
                $user = new tldUser($userid);
                $assignee = new tldUser($this->getAssignee());
                // Case of SINGLE_LEVEL
                if ($this->getTPLNO() == 0) {
                    return $user->getID() === $assignee->getID();
                }
                // Case of TEMPLATE
                $curNodeGroup = $curNode->getGroupName();

                $template = tldSEQTplNode::byParent($this->getTPLNO(),$this->getCurrentStep());

                If((int) $template[0]['allow_supervisor'] === 1){
                    $assignor = new tldUser($this->getCurrentAssignor());
                    return ($user->isInGroup([$curNodeGroup]) || $user->getID() === $assignor->getSupervisor());
                }elseif((int) $template[0]['allow_supervisor'] === 2) {
                    $assignor = new tldUser($this->getCurrentAssignor());
                    return $user->getID() === $assignor->getSupervisor();
                }else {
                    return $user->isInGroup([$curNodeGroup], $this->getModule()) || $user->getID() === $assignee->getSupervisor();
                }
                break;
        }
    }

    /**
     * Get template number
     *
     * @return int
     */
    public function getTPLNO()
    {
        return $this->itsHeader['tplno'];
    }

    /**
     * Get template object
     *
     * @return int
     */
    public function getTemplate()
    {
        return new tldSEQTpl($this->itsHeader['tplno']);
    }

    public static function getModeList(): array
    {
        return ['TEMPLATE', 'SINGLE_LEVEL', 'USER_LEVEL'];
    }

    public function getMode()
    {
        return $this->itsHeader['seq_mode'];
    }

    public function addSeqUserNode($a)
    {
        if ($this->itsMode !== 'USER_LEVEL') {
            return 'Not a user level sequence';
        }
        $a['parent_id'] = $this->itsID;

        return tldSEQUserNode::insert($a);
    }

    /**
     * Insert a new SEQ
     *
     * Calls insert in parent class
     *
     * To add a file, need array key 'file_info' per tldComment constructor
     * <code>
     * array(5) {
     *   ["name"]=>
     *   string(35) "2005-11-11, CAPEX, QC TS server.pdf"
     *   ["tmp_name"]=>
     *   string(14) "/tmp/php5mZAhg"
     * )
     * </code>
     *
     * @param int    $parent_id
     * @param array  $vals
     * @param string $tplno
     * @param string $module
     *
     * @return int ID Number of new sequence
     */
    public static function insert($parent_id, $vals, $tplno, $module = 'SEQ')
    {
        // Prepare data
        if (empty($vals['seq_mode'])) {
            $vals['seq_mode'] = !empty($tplno) ? 'TEMPLATE' : 'SINGLE_LEVEL';
        }
        // Check mode entered
        if (!in_array($vals['seq_mode'], self::getModeList(), true)) {
            return "Sequence mode {$vals['seq_mode']} unknown";
        }
        $vals['tplno'] = $tplno;
        $vals['cur_step'] = 1;
        $vals['seq'] = 'Y';
        // Create sequence
        $result = parent::insert($parent_id, $vals, $module);
        if (is_string($result)) {
            return $result;
        }
        $seq = new tldSEQ($result);
        // Check mode
        switch ($seq->itsMode) {
            case 'USER_LEVEL':
                // if USER_LEVEL, add steps approver
                foreach ($vals['steps'] as $step) {
                    $e = $seq->addSeqUserNode($step);
                }
                break;
        }
        // Add initial comment line
        $a = [
            'status' => 'START',
            'step' => 1,
            'poster' => $vals['assignee'],
            'comment' => "Start of sequence\n".$vals['comment'],
            'file_info' => $vals['file_info'],
        ];
        $error = $seq->addComment($a);

        return $result;
    }

    /**
     * Increment step
     *
     * Closes sequence if at last step of sequence
     *
     * @return string if error
     */
    public function incStep()
    {
        if (empty($this->itsID)) {
            return 'ERROR: No sequence id set. (tldSEQ:incStep)';
        }
        $query = <<<EOF
        UPDATE tasks
        SET cur_step=cur_step+1
        WHERE id=$this->itsID
EOF;
        tldUtils::sqlQuery($query);
        //refresh the header
        $this->itsHeader = $this->getHeader();
        //check if at penultimate step
        $result = null;
        if ($this->isCompleted()) {
            $result = $this->close();
        }

        return $result;
    }

	/**
	 * Advance the seq to the next step
	 *
	 * @param integer $a array per tldTask->addComment
	 *
	 * @return bool|string
	 */
    public function accept($a)
    {
        if ($this->isClosed()) {
            return 'ERROR: Cannot ACCEPT a sequence that is CLOSED';
        }
        $a['status'] = 'ACCEPT';
        $a['step'] = $this->getCurrentStep();
        $error = $this->addComment($a);
        $result = null;
        if (!is_numeric($error)) {
            $result = $error;
        }
        $error = $this->incStep();
        // Check if completed
        if ($this->isCompleted()) {
            $b = [
                'status' => 'END',
                'poster' => $a['poster'],
                'comment' => 'End of sequence',
            ];
            $error .= $this->addComment($b);
        }

        if (!is_numeric($error)) {
            $result .= $error;
        }

        return $result;
    }

    /**
     * Backup the seq to the previous step
     *
     * @param array $a array of parameters to set in the comment
     *
     * @return bool|string
     *
     */
    public function reject($a)
    {
        if ($this->isClosed()) {
            return 'ERROR: Cannot REJECT a sequence that is CLOSED';
        }
        $a['status'] = 'REJECT';
        $a['step'] = $this->getCurrentStep();
        $error = $this->addComment($a);
        $result = null;
        if (!is_numeric($error)) {
            $result = $error;
        }
        // Check mode
        switch ($this->itsMode) {
            case 'USER_LEVEL':
                $error = $this->decStep();
                if ($error) {
                    $result .= $error;
                }
                break;
            default:
                if ($this->getTPLNO() == 0) {
                    $result .= $this->changeStatus('REJECT');
                } else {
                    $error = $this->decStep();
                    if ($error) {
                        $result .= $error;
                    }
                }
                break;
        }

        return $result;
    }

    /**
     * Cancel the SEQ
     *
     * @param $a
     *
     * @return bool
     */
    public function cancel($a)
    {
        if ($this->isClosed()) {
            return 'ERROR: Cannot CANCEL a sequence that is CLOSED';
        }
        $a['status'] = 'CANCEL';
        $a['step'] = $this->getCurrentStep();
        $error = $this->addComment($a);
        $result = null;
        if (!is_numeric($error)) {
            $result = $error;
        }
        $error = $this->close();
        if ($error) {
            $result .= $error;
        }

        return $result;
    }

    /**
     * Decrement step
     *
     * @return string if error
     */
    public function decStep()
    {
        if ($this->isClosed()) {
            return 'ERROR: Sequence closed. (tldSEQ:decStep)';
        }
        if (empty($this->itsID)) {
            return 'ERROR: No sequence id set.  (tldSEQ:decStep)';
        }
        if ($this->getCurrentStep() <= 1) {
            return 'ERROR: At beginning of Sequence';
        }
        $query = <<<EOF
        UPDATE tasks
        SET cur_step=cur_step-1
        WHERE id=$this->itsID
EOF;

        return tldUtils::sqlQuery($query);
    }

    /**
     * Get processed nodes
     * Gets the comments/nodes that have a step number
     *
     * @param string $a
     *
     * @return array array of db rows
     *
     */
    public function getNodes($a = '')
    {
        $WHERE = is_array($a) ? tldUtils::constructWhere($a) : $a;
        $WHERE = !empty($WHERE) ? "AND $WHERE" : '';

        $query = <<<EOF
        SELECT
            tasks_comments.*,
            (SELECT CONCAT(people.firstname, ', ', people.lastname) FROM people
                 WHERE id=tasks_comments.poster
            ) AS poster_fullname
        FROM
            tasks_comments
        WHERE
            tasks_comments.parent_id=$this->itsID
            AND tasks_comments.status<>''
            $WHERE
        ORDER BY
            tasks_comments.id DESC,
            tasks_comments.step,
            tasks_comments.date DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function countByTplStatus()
    {
        $query = <<<EOF
        SELECT
            status,
            tpl.name,
            COUNT(*) AS num
        FROM tasks
            LEFT JOIN cal_seq_tpl AS tpl ON tasks.tplno=tpl.id
        WHERE
            tasks.tplno<>0
        GROUP BY status,tpl.name
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function countByHRTemplateByDivisionByConstraints($a)
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "AND $WHERE";
        }
        $query = <<<EOF
        SELECT
            tpl.short_desc AS template,
            (SELECT division FROM tld_regions
                WHERE tld_regions.id=people.div_id
            ) AS division,
            COUNT(*) AS num
        FROM tasks
            LEFT JOIN cal_seq_tpl AS tpl ON tasks.tplno=tpl.id
            LEFT JOIN people ON people.id=tasks.parent_id
        WHERE
            tplno IN(23,45,47,69,76,77, 109, 110, 136,141)
            $WHERE
        GROUP BY
            template,
            division
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byHRTemplateByDivisionByConstraints($templateDesc, $division, $a = null)
    {
        $WHERE = <<<EOF
tplno IN(23,45,47,69,76,77,109,110,136,141)
EOF;
        if ($templateDesc !== 'ALL') {
            $WHERE .= " AND tpl_short_desc LIKE '$templateDesc' ";
        }
        if ($division !== 'ALL') {
            $WHERE .= <<<EOF
AND '$division' LIKE (
    SELECT division FROM tld_regions
    LEFT JOIN people ON tld_regions.id=people.div_id
    WHERE people.id=tasks.parent_id
)
EOF;
        }
        if (is_array($a)) {
            $a = tldUtils::constructWhere($a);
        }
        if (!empty($a)) {
            $WHERE .= " AND $a ";
        }

        return self::byConstraints($WHERE);
    }

    public static function byMisInvestmentBudgetByBusinessUnit(?array $businessUnits): array
    {
        $WHERE = '';
        if ($businessUnits !== null) {
            $businessUnits = implode(', ', $businessUnits);
            $WHERE = "AND assignor.bu_id IN ($businessUnits)";
        }

        $query = <<<EOF
        SELECT tasks.*,
            CONCAT(assignor.firstname,' ',assignor.lastname) AS assignorFullname,
            CONCAT(assignee.firstname,' ',assignee.lastname) AS assigneeFullname,
            l.location AS assignorLocation, 
            l.erp AS assignorSite
        FROM tasks
            LEFT JOIN cal_seq_tpl ON tasks.tplno=cal_seq_tpl.id
            LEFT JOIN people AS assignor ON tasks.assignor=assignor.id
            LEFT JOIN people AS assignee ON tasks.assignee=assignee.id
            LEFT JOIN locations l ON assignor.bu_id = l.id
        WHERE
            tasks.tplno IN ('46', '82')
            AND tasks.status != 'CLOSED' 
            $WHERE
        ORDER BY 
            tasks.id DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get sequences by parent id
     *
     * @param int $id
     *
     * @return array Array of db rows
     */
    public static function byParent($id, $module = '', $mode = '', $opt = '')
    {
        return parent::byParent($id, 'SEQ', 'ALL');
    }

    /**
     * Tells whether an active (OPEN or IN PROGRESS) sequence already exists
     * for the given user (parent_id) and template (tplno).
     * Return the id of the first active (OPEN or IN PROGRESS) sequence
     * matching the given user and template, or null when none exists.
     */
    public static function getActiveSequenceIdForTemplate(int $parentId, int $tplno): ?int
    {
        if ($parentId <= 0 || $tplno <= 0) {
            return null;
        }

        $query = <<<EOF
        SELECT id
        FROM tasks
        WHERE seq = 'Y'
          AND parent_id = $parentId
          AND tplno = $tplno
          AND status IN ('OPEN', 'IN PROGRESS')
        ORDER BY id ASC
        LIMIT 1
EOF;

        $result = tldUtils::getSqlRowToAssocArray($query);

        return empty($result['id']) ? null : (int) $result['id'];
    }

    /**
     * Get sequences by template and by status
     *
     * @param $x
     * @param $y
     *
     * @return array or string error
     */
    public static function byTplStatus($x, $y)
    {
        $a =[];
        if ($x !== 'ALL') {
            $a['status'] = TldDatabase::escape($x);
        }
        if ($y !== 'ALL') {
            $a['tpl_name'] = TldDatabase::escape($y);
        }

        return self::byConstraints($a);
    }

    /**
     * Get most recent nodes
     *
     * @param int $n Optional max number of rows to return
     *
     * @return array Array of db rows
     */
    public static function byLatest($n = 10, $options = '', $operand = '')
    {
        return self::byConstraints(null, ['limit' => $n, 'orderBy' => 'tasks.id DESC']);
    }

    /**
     * Get SEQ by constraints
     * BE CARREFULL WITH PRIVATE SEQ !!!
     *
     * @param       $constraints
     * @param array $options
     *
     * @return array Array of db rows
     */
    public static function byConstraints($constraints, $options = [])
    {
        $HAVING = is_array($constraints) ? tldUtils::constructWhere($constraints) : $constraints;
        $HAVING = !empty($HAVING) ? " HAVING $HAVING " : '';
        $ORDERBY = !empty($options['orderBy']) ? $options['orderBy'] : 'tasks.id';
        $LIMIT = !empty($options['limit']) ? "LIMIT {$options['limit']}" : '';

        $query = <<<EOF
        SELECT tasks.*,
            cal_seq_tpl.short_desc AS tpl_short_desc,
            cal_seq_tpl.name AS tpl_name,
            (SELECT CONCAT(firstname,' ',lastname) FROM people
                WHERE tasks.assignee=id
            ) AS assignee_fullname,
            CONCAT(assignor.firstname,' ',assignor.lastname) AS assignor_fullname,
            assignor.div_id AS assignor_div_id,
            (SELECT tasks_comments.status FROM tasks_comments
                WHERE tasks_comments.parent_id=tasks.id AND tasks_comments.step<>0
                ORDER BY tasks_comments.date DESC LIMIT 1
            ) AS last_step_status
        FROM tasks
            LEFT JOIN cal_seq_tpl ON tasks.tplno=cal_seq_tpl.id
            LEFT JOIN people AS assignor ON tasks.assignor=assignor.id
        WHERE
            tasks.tplno<>0
        $HAVING
        ORDER BY $ORDERBY
        $LIMIT
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getQuickEditableTemplateNumber()
    {
       return array_merge(self::getStandardSeqTemplateNumber(), self::getPaymentSequenceTemplateNumber());
    }

    public static function getStandardSeqTemplateNumber()
    {
        // 22	eng.mfg.pur.acct.new_part_number_routing - New Part number routing Seq
        // 27	mlm.cfo.inventory_adjustment_routing - Inventory adjustment routing sequence (under $1000)
        // 28	mlm.coo.cfo.inventory_adjustment_routing - Inventory adjustment routing sequence (between $1000 and $5000)
        // 29 - mlm.coo.ceo.cfo.inventory_adjustment_routing - Inventory adjustment routing sequence (above $5000)
        // 53 - odp.lategt.approval.gceo - Late GT Approval (last day of month)
        // 54 - odp.lategt.approval.gcoo - Late GT Approval (last day of month -1)
        // 55 - odp.lategt.approval.rceo - Late GT Approval (last day of month -2)
        // 62 - Positive_inventory_adjustment_MLM - Positive inventory adjustment
        // 67 - Loan of parts >1000(USD/EUR) or major component foreman.mln.coo.inventory_adjustment_routing - value>1000 or major component foreman.mln.coo.inventory_adjustment_routing
        // 68 - Loan of parts <1000(USD/EUR) GL.foreman.mlm.inventory_adjustment_routing - value<1000 foreman.mlm.inventory_adjustment_routing
        // 78 - eng.mfg.pur.acct.new_part_number_revision - New Part number revision Seq
        // 126 - SEQ requestion self attestation related to code of ethic adhesion every year
        // 130 - SEQ requestion self attestation related to code of ethic adhesion every 3 years
        // 132 - Annual revision of intranet profile information
        // 133 - Annual revision of intranet profile information light

        return [22, 27, 28, 29, 53, 54, 55, 62, 67, 68, 78, 126, 130, 132, 133];
    }

    public static function getApSeqTemplateNumber()
    {
        throw new Exception('This module has been migrated and this function should not be used anymore');
    }

    public static function getPaymentSequenceTemplateNumber()
    {
        // 105 - Payment request
        // 106 - Prepayment request approval SEQ - Prepayment request approval SEQ
        // 111 - newpayment.request (amount >50K) Please manually transfer to CEO
        // 112 - newpayment.request (10K < amount < 50K)
        // 113 - Without PO prepayment request approval SEQ (amount < 10K) - New prepayment request approval SEQ (amount < 10K)
        // 114 - newprepayment.request - New prepayment request approval SEQ without PO(amount >50K)
        // 115 - newpoprepayment.request(amount > 50K and amount < 500K) - New prepayment request approval SEQ (amount > 50K and amount < 500K)
        // 116 - newpoprepayment.request(amount < 10K) - New prepayment request approval SEQ (amount < 10K)
        // 117 - PO prepayment request approval SEQ (amount > 500K) - PO prepayment request approval SEQ (amount > 500K)
        // 124 - New payment request approval SEQ (amount < 10K)
        // 125 - without PO prepayment request approval SEQ (10K< amount < 50K) - New prepayment request approval SEQ (10K< amount < 50K)
        // 139 - Payment_request_ASI

        return [105, 106, 111, 112, 113, 114, 115, 116, 117, 124, 125, 139];
    }

    public static function getSequencesByERP($sequences, $erp) {
        return array_filter($sequences, static function ($row) use ($erp) {
            return $row['erp'] === $erp;
        });
    }

    public static function getSequencesId(array $sequences) {
        return array_map(static function ($row) {
            return $row['id'];
        }, $sequences);
    }

    public static function includeLastComments(array $sequences, bool $displayEmail = true)
    {
        $ids = implode(', ', self::getSequencesId($sequences));
        $emailRequest = $displayEmail ? ", ' (', people.email, ')'" : "";
        $query = <<<SQL
            SELECT
              com.parent_id,
              com.date,
              CONCAT(p.lastname, ' ', p.firstname$displayEmail) AS fullname,
              com.status,
              com.comment
            FROM tasks_comments AS com
            LEFT JOIN tasks_comments AS com2 ON com.parent_id = com2.parent_id AND com.date < com2.date
            LEFT JOIN people AS p ON p.id = com.poster
            WHERE com.parent_id IN ($ids) AND com2.id IS NULL;
SQL;
        $lastComments = tldUtils::getSqlToAssocArray($query);
        // Adding the comment directly in the sequences
        foreach ($sequences as &$sequence) {
            $sequence['last_comment'] = current(array_filter($lastComments, static function ($comment) use ($sequence) {
                    return $comment['parent_id'] === $sequence['id'];
                })
            );
        }
        return $sequences;
    }

    public static function includeComments(array $sequences, bool $displayEmail = true)
    {
        $ids = implode(', ', self::getSequencesId($sequences));
        $emailRequest = $displayEmail ? ", ' (', people.email, ')'" : "";
        $query = <<<SQL
            SELECT
              com.parent_id,
              com.date,
              CONCAT(p.lastname, ' ', p.firstname$emailRequest) AS fullname,
              com.status,
              com.comment
            FROM tasks_comments AS com
            LEFT JOIN people AS p ON p.id = com.poster
            WHERE com.parent_id IN ($ids)
            ORDER BY com.date DESC;
SQL;
        $comments = tldUtils::getSqlToAssocArray($query);
        // Adding the comments directly in the sequences
        foreach ($sequences as &$sequence) {
            $sequence['comments'] = array_filter($comments, static function ($comment) use ($sequence) {
                return $comment['parent_id'] === $sequence['id'];
            });
        }
        return $sequences;
    }

    public static function includePOs(array $sequences, array $erps)
    {
        $pos = [];
        foreach ($erps as $erp) {
            $sequencesByErp = self::getSequencesByERP($sequences, $erp);
            $poIds = array_map(static function ($row) { return $row['parent_id']; }, $sequencesByErp);

            $poIds = implode(', ', $poIds);
            $query = <<<SQL
                            SELECT
                              RTRIM(pur040.t_orno) AS t_orno,
                              RTRIM(pur040.t_suno) AS t_suno,
                              pur040.t_ccur,
                              (SELECT SUM(pur041.t_amta) FROM ttdpur041$erp AS pur041 WHERE pur041.t_orno=pur040.t_orno) AS t_amnt,
                              CASE WHEN (SELECT Top 1 T1.t_orno from ttdpur041$erp AS T1, ttiitm001$erp AS ITM WHERE pur040.t_orno =T1.t_orno AND ITM.t_item=T1.t_item and T1.t_pric>ITM.t_ltpr) is NOT NULL
                              THEN 'Y'
                              ELSE 'N'
                              END AS change
                            FROM
                              dbo.ttdpur040$erp AS pur040
                            WHERE
                              t_orno IN ($poIds)
SQL;
            $pos[$erp] = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
        }

        foreach ($sequences as &$sequence) {
            if ($pos[$sequence['erp']] === null) {
                continue;
            }

            $sequencePos = array_filter($pos[$sequence['erp']], static function ($po) use ($sequence) {
                return $sequence['parent_id'] === $po['t_orno'];
            });

            if (empty($sequencePos)) {
                continue;
            }

            $sequence = array_merge(
                $sequence,
                current($sequencePos)
            );
        }

        return $sequences;
    }

    public static function getUserQuickEditableSeqSummary($userId)
    {
        $AllowedTplNo = implode(', ', self::getQuickEditableTemplateNumber());
        $query = <<<SQL
                    SELECT
                        COUNT(*) AS seq_number,
                        tpl.id,
                        tpl.short_desc AS short_name,
                        IF(tasks.due_date<NOW(),"LATE", "DUE") as overdue
                    FROM tasks
                    LEFT JOIN cal_seq_tpl AS tpl ON tasks.tplno=tpl.id
                    WHERE
                        tasks.module in ('SEQ', 'USER')
                        AND tasks.status = 'OPEN'
                        AND tasks.tplno IN ($AllowedTplNo)
                        AND tasks.assignee = $userId
                    GROUP BY name, overdue
SQL;

        return tldUtils::getSqlToAssocArray($query);
    }
}

/**
 * Class to create and manipulate Sequence Templates
 *
 * <code>
 * CREATE TABLE `cal_seq_tpl` (
 *   `id` int(11) NOT NULL auto_increment,
 *   `parent_id` int(11) NOT NULL default '0',
 *   `name` varchar(100) NOT NULL default '',
 *   `short_desc` varchar(200) NOT NULL default '',
 *   `private` char(1) NOT NULL default 'N',
 *   UNIQUE KEY `id` (`id`)
 * ) TYPE=MyISAM
 * </code>
 *
 * @package Calendar
 */
class tldSEQTpl
{
    public $itsID;
    public $itsHeader;

    /**
     * Class constructor
     *
     * @param $id
     *
     * @return tldSEQTpl
     */
    public function __construct($id)
    {
        if (!is_numeric($id)) {
            $row = self::byName($id);
            $id = $row['id'];
        }
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    public function getID()
    {
        return $this->itsID;
    }

    /**
     * Get template header information from database
     *
     * @return array Array from table
     */
    public function getHeader()
    {
        if (empty($this->itsID)) {
            return;
        }
        $query = <<<EOF
        SELECT *
        FROM cal_seq_tpl
        WHERE id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Get name of sequence template
     *
     * @return string
     */
    public function getName()
    {
        return $this->itsHeader['name'];
    }

    /**
     * Get short description of sequence
     *
     * @return string
     */
    public function getShortDesc()
    {
        return $this->itsHeader['short_desc'];
    }

    public function getEscalationTrigger()
    {
        return $this->itsHeader['def_escalation_trigger'];
    }

    /**
     * Tell whether sequence is private or not
     *
     * @return boolean
     */
    public function isPrivate()
    {
        return $this->itsHeader['private'] === 'Y';
    }

    /**
     * Tell if sequence is parallel
     *
     * @return boolean
     */
    public function isParallel()
    {
        return $this->itsHeader['parallel'] === 'Y';
    }

    /**
     * Get a list of all assignees at all nodes
     *
     * @return array Array of userids
     */
    public function getAssigneeList()
    {
        $rows = $this->getNodes();
        $result = [];
        foreach ($rows as $row) {
            $node = new tldSEQTplNode($row['id']);
            $result += $node->getAssigneeList();
        }

        return $result;
    }

    /**
     * Get node information from database
     *
     * @return array Array of db rows from table
     */
    public function getNodes()
    {
        return tldSEQTplNode::byParent($this->itsID);
    }

    /**
     * Get node at step $step
     *
     * @param $step
     *
     * @return array
     */
    public function getNode($step)
    {
        $rows = tldSEQTplNode::byParent($this->itsID, $step);

        return new tldSEQTplNode($rows[0]['id']);
    }

    /**
     * Get total number of nodes in sequence
     *
     * @return int
     */
    public function getNumNodes()
    {
        $nodes = $this->getNodes();
        if (is_countable($nodes)) {
            return count($nodes);
        }

        return 0;
    }

    /**
     * Return the last node step number for the this seq tpl
     *
     * @return int
     */
    public function getLastNodeStep()
    {
        $query = <<<EOF
        SELECT MAX(step) AS step
        FROM cal_seq_tpl_nodes
        WHERE parent_id=$this->itsID
EOF;
        $node = tldUtils::getSqlRowToAssocArray($query);

        return $node['step'];
    }

    /*
     * Get sequence template using the name only
     *
     * @return array Should be single db row
     */
    public static function byName($name)
    {
        $query = <<<EOF
        SELECT *
        FROM cal_seq_tpl
        WHERE name='$name'
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /*
     * Get sequence template list
     *
     * @param string option set to smartyOptions to get id and title only
     * @return array db rows
     */
    public static function getSEQTplList($option = '')
    {
        if ($option === 'smartyOptions') {
            $fields = 'id, name';
        } else {
            $fields = ' * ';
        }
        $query = <<<EOF
        SELECT $fields
        FROM cal_seq_tpl
        ORDER BY name
EOF;

        return tldUtils::getSqlToAssocArray($query, $option);
    }

    public static function getPublicList()
    {
        return self::byConstraints(['private' => 'N']);
    }

    public static function getPrivateList()
    {
        return self::byConstraints(['private' => 'Y']);
    }

    public static function byConstraints($a = '1=1')
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        $query = <<<EOF
SELECT *
FROM cal_seq_tpl
WHERE $WHERE
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }
}

/**
 * Class for creating and manipulating sequence template nodes
 *
 * <code>
 * CREATE TABLE `cal_seq_tpl_nodes` (
 *   `id` int(11) NOT NULL auto_increment,
 *   `parent_id` int(11) NOT NULL default '0',
 *   `group_name` varchar(50) NOT NULL default '',
 *   `step` int(11) NOT NULL default '1',
 *   `days_to_do` int(11) NOT NULL default '14',
 *   PRIMARY KEY  (`id`)
 * ) TYPE=MyISAM
 * </code>
 *
 * @package Calendar
 */
class tldSEQTplNode
{

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsDetails = $this->getHeader();
    }

    /**
     * Check if empty
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->itsDetails);
    }

    /**
     * Get node information
     *
     * @return array Array from table
     */
    public function getHeader()
    {
        if (empty($this->itsID)) {
            return;
        }
        $query = <<<EOF
        SELECT *
        FROM cal_seq_tpl_nodes
        WHERE id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Get the group name of people allowed to approve the node
     *
     * @return string
     */
    public function getGroupName()
    {
        return $this->itsDetails['group_name'];
    }

    /**
     * Get the step number of this node
     *
     * @return string
     */
    public function getStep()
    {
        return $this->itsDetails['step'];
    }

    /**
     * Get the assignee list for current node
     *
     * @return array Array of db rows
     */
    public function getAssigneeList()
    {
        $grp = new tldGroup($this->getGroupName());

        return $grp->getUserlist(['smartyOptions' => true]);
    }

    public function getDefaultAssigneeByBU($buid)
    {
        $grp = new tldGroup($this->getGroupName());
        $list = $grp->getUserlistBySSO($buid, ['smartyOptions' => true]);
        $keys = array_keys($list);

        return $keys[0];
    }

    /**
     * Get list of nodes using parent id
     *
     * @param        integer Optional step number to retrieve
     *
     * @param string $step
     *
     * @return array array of db rows
     */
    public static function byParent($id, $step = '')
    {
        if (empty($id)) {
            return;
        }
        $WHERE = is_numeric($step) ? " AND step=$step" : '';

        $query = <<<EOF
        SELECT *
        FROM cal_seq_tpl_nodes
        WHERE parent_id=$id
        $WHERE
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function getType()
    {
        return $this->itsDetails['allow_supervisor'];
    }

    public static function getMaxStepNumber()
    {
        $query = 'SELECT MAX(step) AS maxStepNum FROM cal_seq_tpl_nodes';
        $row = tldUtils::getSqlRowToAssocArray($query);

        return $row['maxStepNum'];
    }

    public static function getStepList()
    {
        $maxStepNum = self::getMaxStepNumber();
        $stepList = [];
        for ($i = 1; $i <= $maxStepNum; $i++) {
            $stepList[$i] = $i;
        }

        return $stepList;
    }
}

/**
 * Class for creating and manipulating sequence nodes
 * Only used for SEQ USER_LEVEL mode
 *
 * @package Calendar
 */
class tldSEQUserNode
{

    public $itsID;
    public $itsDetails;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsDetails = $this->getHeader();
    }

    /**
     * Get ID reccord
     *
     * @return int
     */
    public function getID()
    {
        return $this->itsID;
    }

    /**
     * Get user ID step
     *
     * @return int
     */
    public function getUserID()
    {
        return $this->itsDetails['uid'];
    }

    /**
     * Check if empty
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->itsDetails);
    }

    /**
     * Get assigne list (only one user)
     *
     * @return array
     */
    public function getAssigneeList()
    {
        return [$this->itsDetails['uid'] => $this->itsDetails['fullname']];
    }

    /**
     * Get node step
     *
     * @return int
     */
    public function getStep()
    {
        return $this->itsDetails['step'];
    }

    /**
     * Get node step
     *
     * @return int
     */
    public function getAssigneeFullname()
    {
        return $this->itsDetails['fullname'];
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
    node.*,
    CONCAT(people.lastname, ', ', people.firstname, ' (', people.email, ')') AS fullname
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
FROM cal_seq_user_nodes AS node
    LEFT JOIN people ON people.id=node.uid
EOF;
    }

    /**
     * Get header
     *
     * @return row
     */
    public function getHeader()
    {
        if (!$this->itsID) {
            return [];
        }
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
WHERE node.id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Insert new node
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
        $fields = ['parent_id', 'uid', 'step', 'days_to_do', 'dsca'];
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO cal_seq_user_nodes SET $SET";

        return tldUtils::sqlInsert($query);
    }

    /**
     * Update node
     *
     * @param array        $a
     * @param array|string $fields (optional)
     *
     * @return int or string on error
     */
    public function update($a, $fields = '')
    {
        if (empty($a)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "UPDATE cal_seq_user_nodes SET $SET WHERE id=$this->itsID";

        return tldUtils::sqlQuery($query);
    }

    /**
     * Delete node
     *
     * @return string on error
     */
    public function delete()
    {
        $query = "DELETE FROM cal_seq_user_nodes WHERE id=$this->itsID";

        return tldUtils::sqlQuery($query);
    }

    /**
     * Get node by constraints
     *
     * @param array        $a   constraints
     * @param array|string $opt options
     *
     * @return array of rows
     */
    public static function byConstraints($a, $opt = [])
    {
        if (empty($a)) {
            return 'empty parameter';
        }

        $HAVING = is_array($a) ? 'HAVING ' .tldUtils::constructWhere($a) :"HAVING $a";

        $ORDERBY = 'ORDER BY step';
        if (!empty($opt['orderBy'])) {
            $ORDERBY = 'ORDER BY ' .$opt['orderBy'];
        }
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

    /**
     * Get node by parent
     *
     * @param int $pid
     *
     * @return array of rows
     */
    public static function byParentID($pid)
    {
        $a = ['parent_id' => $pid];

        return self::byConstraints($a);
    }

}

/**
 * Class for calendar events
 *
 * <code>
 * CREATE TABLE `cal_events` (
 *   `id` int(11) NOT NULL auto_increment,
 *   `parent_id` int(11) NOT NULL default '0',
 *   `company` varchar(20) default NULL,
 *   `date` date default '0000-00-00',
 *   `day` int(2) NOT NULL default '0',
 *   `month` int(2) NOT NULL default '0',
 *   `end` date NOT NULL default '0000-00-00',
 *   `description` varchar(50) default NULL,
 *   PRIMARY KEY  (`id`)
 * ) TYPE=MyISAM
 * </code>
 *
 * @package Calendar
 */
class tldEvents
{
    public $itsCurrentYear;

    /**
     * Get a list of months in words
     *
     * @return array
     */
    public static function getMonths()
    {
        return [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December',
        ];
    }

    /**
     * Get list of events by year
     *
     * Returns events for current year by default
     *
     * @param string $year
     *
     * @return array
     */
    public static function byYear($year = '')
    {
        if (empty($year)) {
            $year = date('Y');
        }
        $query = <<<EOF
SELECT *,
    DATE_FORMAT(date, '%M') AS display_month,
    YEAR(date) AS display_year
FROM cal_events
WHERE $year >= YEAR(date) AND $year <= YEAR(end)
ORDER BY date,company
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get list of events by Month and Year
     *
     * @param string $month
     * @param string $year
     *
     * @return array
     */
    public static function byMonth($month = '', $year = '')
    {
        if (empty($year)) {
            $year = date('Y');
        }
        if (empty($month)) {
            $month = date('m');
        }
        if ($month < 1 || $month > 12) {
            return;
        }
        $period = $year * 12 + $month;
        $query = <<<EOF
SELECT *,
    DATE_FORMAT(date, '%M') AS display_month,
    YEAR(date) AS display_year,
    DAYOFMONTH(date) as display_day
FROM cal_events
WHERE
    $period >= YEAR(date)*12+MONTH(date)
    AND $period <= YEAR(end)*12+MONTH(end)
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get events by date
     *
     * Returns using current date by default
     *
     * @param string $date
     *
     * @return array
     */
    public static function byDate($date = '')
    {
        if (empty($date)) {
            $date = date('Y-m-d');
        }
        $query = <<<EOF
        SELECT *,DATE_FORMAT(date, '%M') AS display_month,
        YEAR(date) AS display_year,
        DAYOFMONTH(date) as display_day
        FROM cal_events
        WHERE date='$date'
        ORDER BY company
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }
}

/**
 * Class for news events
 *
 * <code>
 * CREATE TABLE `internal_news` (
 *   `id` int(10) NOT NULL auto_increment,
 *   `parent_id` int(11) NOT NULL default '0',
 *   `title` varchar(200) NOT NULL default '',
 *   `en` text,
 *   `date` date NOT NULL default '0000-00-00',
 *   `cat` mediumint(9) NOT NULL default '1',
 *   `extranet` char(3) NOT NULL default 'NO',
 *   PRIMARY KEY  (`id`)
 * ) TYPE=MyISAM
 * </code>
 *
 * @package Calendar
 */
class tldNews
{
    public $itsID; //id of news article

	/**
	 * Class constructor
	 *
	 * @param integer $id id of news article in table
	 *
	 * @return tldNews
	 */
    public function __construct($id)
    {
        $this->itsID = $id;
    }

    /**
     * Get news article header information
     *
     * @return array
     */
    public function getHeader()
    {
        $query = <<<EOF
        SELECT * FROM internal_news
        WHERE id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    //static functions
    /**
     * get news articles by period
     *
     * @param string       $start   Start date
     * @param string       $end     End date
     * @param array|string $options Array of optional parameters
     *
     * @return array Array of db rows
     */
    public function byPeriod($start, $end, $options = '')
    {
        $WHERE = " date BETWEEN '$start' AND '$end'";
        if (isset($options['extranet'])) {
            $WHERE .= " AND extranet='YES'";
        }
        $query = <<<EOF
        SELECT *, YEAR(date) AS year,
        MONTH(date) AS month, MONTHNAME(date) AS month_name,
        DAYOFMONTH(date) as day
        FROM internal_news
        WHERE $WHERE
        ORDER BY date DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }
}

/**
 * Class for creating and manipulating Scheduled Tasks
 *
 *--
 *-- Table structure for table `cal_st`
 *--
 *
 *CREATE TABLE `cal_st` (
 *`id` int(11) NOT NULL auto_increment,
 * `parent_id` int(11) NOT NULL default '0',
 * `type` varchar(25) NOT NULL COMMENT 'Type of TS event',
 * `module` varchar(10) NOT NULL COMMENT 'module',
 * `bu_id` int(3) NOT NULL COMMENT 'bu id#',
 * `assignor` int(11) NOT NULL COMMENT 'scheduled task owner',
 * `assignee` int(11) NOT NULL COMMENT 'scheduled task assignee',
 * `date_start` date default '0000-00-00',
 * `date_end` date NOT NULL default '0000-00-00',
 * `description` varchar(50) default NULL,
 * `repd_unit` varchar(5) NOT NULL COMMENT 'repetition unit',
 * `repd_val` int(5) NOT NULL COMMENT 'repetition value',
 * `leadtime_value` int(5) NOT NULL COMMENT 'leadtime_value',
 * `leadtime_unit` varchar(5) NOT NULL COMMENT 'leadtime_unit',
 * `action` varchar(20) NOT NULL COMMENT 'what action?',
 * PRIMARY KEY  (`id`),
 * KEY `bu_id` (`bu_id`),
 * KEY `start` (`date_start`)
 *) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=367 ;
 *
 * @package Calendar
 */
class tldST
{

    public $itsID;        //id of st in database
    public $itsHeader;    //Header information from database

	/**
	 * Constructor
	 *
	 * @param integer $id id of ST in database
	 *
	 * @return tldST
	 */
    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Insert a new ST into database
     *
     * @param array $a parameters for inserting into database table
     *
     * @return integer $id id of the row inserted
     */
    public static function insert($a)
    {
        // build schedule
        $a['repd_unit'] = $a['every']['repd_unit'];
        $a['repd_val'] = $a['every']['repd_val'];
        // build lead time
        $a['leadtime_value'] = $a['leadtime']['leadtime_value'];
        $a['leadtime_unit'] = $a['leadtime']['leadtime_unit'];
        // set the fields
        $fields = [
            'parent_id',
            'bu_id',
            'type',
            'module',
            'assignor',
            'assignee',
            'date_start',
            'date_end',
            'description',
            'repd_unit',
            'repd_val',
            'leadtime_value',
            'leadtime_unit',
            'action',
            'escalation_trigger',
            'referencetype',
            'reference',
            'auto_close',
        ];
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO cal_st SET $SET";

        return tldUtils::sqlInsert($query);
    }

    public static function countByBUByType(){
        $query = <<<EOF
            select cal_st.type, locations.location AS bu_fullname, count(*) AS num 
            from cal_st 
            LEFT JOIN locations ON locations.id=cal_st.bu_id
            where status like 'ACTIVE'
            group by locations.location, cal_st.type
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byTypeByBU($type,$bu){
        if ($bu !== 'ALL') {
            $a[] = "locations.location LIKE '$bu'";
        }
        if ($type !== 'ALL') {
            $a[] = "cal_st.type LIKE '$type'";
        }
        $where = implode(' AND ', $a);
        $query =<<<EOF
            select cal_st.*, locations.location AS bu_fullname,
            CONCAT(assignor.firstname,' ',assignor.lastname) AS assignor_fullname,
			CONCAT(assignee.firstname,' ',assignee.lastname) AS assignee_fullname 
            from cal_st 
            LEFT JOIN locations ON locations.id=cal_st.bu_id
            LEFT JOIN people AS assignee ON cal_st.assignee=assignee.id
            LEFT JOIN people AS assignor ON cal_st.assignor=assignor.id
            where status like 'ACTIVE' and 
            $where
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }
    /**
     * Get the header information for the ST from the database
     *
     * @return array Array
     */
    public function getHeader()
    {

        $query = <<<EOF
        SELECT *,
            (SELECT concat(people.lastname,', ',people.firstname)
            FROM people WHERE people.id=cal_st.assignor) AS assignor_fullname,
            (SELECT concat(people.lastname,', ',people.firstname)
            FROM people WHERE people.id=cal_st.assignee) AS assignee_fullname,
            (SELECT location
            FROM locations WHERE locations.id=cal_st.bu_id) AS bu_fullname
        FROM cal_st
        WHERE id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Get assignor of the ST
     *
     * @return string
     */
    public function getAssignor()
    {
        return $this->itsHeader['assignor'];
    }

    /**
     * Get Status of the ST
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->itsHeader['status'];
    }

    /**
     * Get assignor of the ST
     *
     * @return string
     */
    public function getAssignee()
    {
        return $this->itsHeader['assignee'];
    }

    /**
     * Get STs for specified Assignor
     *
     * @param integer $uid Assignor id
     *
     * @param string  $opts
     *
     * @return array
     */
    public static function byAssignor($uid, $opts = '')
    {
        $WHERE =  !$opts['includeInactive'] ? "AND status!='INACTIVE'" : '';
        $query = <<<EOF
            SELECT *,
                (SELECT concat(people.lastname,', ',people.firstname)
                FROM people WHERE people.id=cal_st.assignor) AS assignor_fullname,
                (SELECT concat(people.lastname,', ',people.firstname)
                FROM people WHERE people.id=cal_st.assignee) AS assignee_fullname,
                (SELECT location
                FROM locations WHERE locations.id=cal_st.bu_id) AS bu_fullname
            FROM cal_st
            WHERE assignor=$uid
            $WHERE
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Send email to the assignee
     *
     * By default also cc's the assignor the same message
     *
     * @param        array  cc Array of emails to cc email to
     *
     * @param string $subject
     * @param string|array $cc
     *
     * @return bool
     */
    public function notifyAssignee($message, $subject = '', $cc = '')
    {
        $assignee = new tldUser($this->getAssignee());
        $assignor = new tldUser($this->getAssignor());
        if (empty($subject)) {
            $subject = 'Scheduled Task# ' .$this->itsID. ' update';
        }
        if (is_array($cc)) {
            $cc = implode(',', $cc);
        }
        if (!empty($cc)) {
            $cc = ",$cc";
        }

        return tldUtils::emailAttachment(
            $assignee->getEmail(),
            'noreply@tld-gse.com',
            $subject,
            $message,
            null,
            $assignor->getEmail().$cc
        );
    }

    public static function byConstraints($a, $opt = [])
    {
        $WHERE = is_array($a) ? tldUtils::constructWhere($a) : $a;

        if (!empty($WHERE)) {
            $WHERE = "WHERE $WHERE";
        }

        $ORDERBY = !empty($opt['orderby']) ? $opt['orderby'] : 'id DESC';

        $query = <<<EOF
SELECT
    cal_st.*,
    locations.location AS bu_fullname,
    (SELECT concat(people.lastname,', ',people.firstname) FROM people
        WHERE people.id=cal_st.assignor
    ) AS assignor_fullname,
    (SELECT concat(people.lastname,', ',people.firstname) FROM people
        WHERE people.id=cal_st.assignee
    ) AS assignee_fullname,
    tld_departments.dpt AS assigneeDepartment,
    tld_regions.division AS assigneeRegion,
    assigneeLocation.business_unit AS assigneeBU,
    assignee.email AS assigneeEmail,
    CONCAT(cal_st.leadtime_value, ' ', cal_st.leadtime_unit) AS leadtime,
    CONCAT(cal_st.repd_val, ' ', cal_st.repd_unit) AS periodicity,
    CONCAT(cal_st.reference, ' ', cal_st.referencetype) AS ref,
    IF(cal_st.auto_close, 'Y', 'N') AS autoClose
FROM cal_st
    LEFT JOIN locations ON locations.id=cal_st.bu_id
    LEFT JOIN people AS assignee ON cal_st.assignee = assignee.id
    LEFT JOIN locations AS assigneeLocation ON assigneeLocation.id = assignee.bu_id
    LEFT JOIN tld_regions ON tld_regions.id = assigneeLocation.region_id
    LEFT JOIN tld_departments ON tld_departments.id = assignee.dpt_id
$WHERE
ORDER BY
    $ORDERBY
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get STs for specified Assignee
     *
     * @param integer $uid Assignee id
     *
     * @param string  $opts
     *
     * @return array
     */
    public static function byAssignee($uid, $opts = '')
    {
        $WHERE = !$opts['includeInactive'] ? "AND status!='INACTIVE'" : '';
        $query = <<<EOF
            SELECT *,
                (SELECT concat(people.lastname,', ',people.firstname)
                FROM people WHERE people.id=cal_st.assignor) AS assignor_fullname,
                (SELECT concat(people.lastname,', ',people.firstname)
                FROM people WHERE people.id=cal_st.assignee) AS assignee_fullname,
                (SELECT location
                FROM locations WHERE locations.id=cal_st.bu_id) AS bu_fullname
            FROM cal_st
            WHERE assignee=$uid
            $WHERE
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get STs for specified user
     *
     * @param integer $uid user id
     *
     * @param string  $opts
     *
     * @return array
     */
    public static function byUser($uid, $opts = '')
    {
        $WHERE = !$opts['includeInactive'] ? "AND status!='INACTIVE'" : '';

        $query = <<<EOF
            SELECT *,
                (SELECT concat(people.lastname,', ',people.firstname)
                FROM people WHERE people.id=cal_st.assignor) AS assignor_fullname,
                (SELECT concat(people.lastname,', ',people.firstname)
                FROM people WHERE people.id=cal_st.assignee) AS assignee_fullname,
                (SELECT location
                FROM locations WHERE locations.id=cal_st.bu_id) AS bu_fullname
            FROM cal_st
            WHERE (assignee=$uid OR assignor=$uid)
            $WHERE
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

	/**
	 * Get latest 10 st
	 *
	 * @param integer $num number of rows to return
	 *
	 * @return array
	 */
    public static function byLatest($num = 10)
    {
        $query = <<<EOF
        SELECT *,
            (SELECT concat(people.lastname,', ',people.firstname)
            FROM people WHERE people.id=cal_st.assignor) AS assignor_fullname,
            (SELECT concat(people.lastname,', ',people.firstname)
            FROM people WHERE people.id=cal_st.assignee) AS assignee_fullname,
            (SELECT location
            FROM locations WHERE locations.id=cal_st.bu_id) AS bu_fullname
        FROM cal_st
        ORDER BY id DESC
        LIMIT $num
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Query ST using array of constraints
     *
     * @param array $m
     *
     * @return array
     */
    public static function byQuery($m)
    {
        if (is_array($m)) {
            $WHERE = ' WHERE ' .tldUtils::constructWhere($m);
        }
        $query = <<<EOF
        SELECT *,
            (SELECT concat(people.lastname,', ',people.firstname)
            FROM people WHERE people.id=cal_st.assignor) AS assignor_fullname,
            (SELECT concat(people.lastname,', ',people.firstname)
            FROM people WHERE people.id=cal_st.assignee) AS assignee_fullname,
            (SELECT location
            FROM locations WHERE locations.id=cal_st.bu_id) AS bu_fullname
        FROM cal_st
        $WHERE
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Query ST using using the ID
     *
     * @param array $id
     *
     * @return array
     */
    public function byID($id)
    {

        $query = <<<EOF
        SELECT *,
            (SELECT concat(people.lastname,', ',people.firstname)
            FROM people WHERE people.id=cal_st.assignor) AS assignor_fullname,
            (SELECT concat(people.lastname,', ',people.firstname)
            FROM people WHERE people.id=cal_st.assignee) AS assignee_fullname,
            (SELECT location
            FROM locations WHERE locations.id=cal_st.bu_id) AS bu_fullname
        FROM cal_st
        WHERE cal_st.id=$id
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Is the ST object valid?
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    /**
     * Get all related tasks to the ST
     *
     * @return array Array of db rows
     */
    public function getTasks()
    {
        return tldTask::byParent($this->itsID, 'ST', 'ALL');
    }

    public static function byDMS($dms, $scheduledTaskId, $start, $end, $status, $options = '')
    {

        $dmsList =  implode(',', $dms);
        $andWhere = " AND dms.id IN ($dmsList)";

        if ($scheduledTaskId !== '') {
            $andWhere .= " AND tasks.parent_id= $scheduledTaskId";
        }

        if (is_array($status)) {
            $status = implode("', '", $status);
        }

        $query = <<<SQL
            SELECT
                tasks.id,
                tasks.date,
                tasks.dt_closed,
                tasks.due_date,
                tasks.escalation_trigger,
                concat(assignee.lastname,', ',assignee.firstname) as assignee_fullname,
                concat(assignor.lastname,', ',assignor.firstname) as assignor_fullname,
                tasks.status,
                tasks.closed_not_done
            FROM tasks
                LEFT JOIN people AS assignee ON tasks.assignee=assignee.id
                LEFT JOIN people AS assignor ON tasks.assignor=assignor.id
                INNER JOIN cal_st ON tasks.parent_id = cal_st.id
                INNER JOIN dms ON (cal_st.reference = dms.id AND referencetype = 'DMS')
            WHERE tasks.module = 'ST'
              AND tasks.status IN ('$status')
              AND DATE_FORMAT(tasks.date,'%Y-%m-%d') >= '$start'
              AND DATE_FORMAT(tasks.date,'%Y-%m-%d') <= '$end'
              $andWhere
              $options
            ORDER BY status DESC, due_date ASC
        SQL;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function getOpenTasks()
    {
        return tldTask::byParent($this->itsID, 'ST', 'OPEN');
    }
    /**
     * Get all related files to the ST
     *
     * @return array Array of db rows
     */
    public function getFiles()
    {
        return tldModFile::byParent($this->itsID, 'ST');
    }

    /**
     * Get all related links to the ST
     *
     * @return array Array of db rows
     */
    public function getLinks()
    {
        return tldModLink::byParent($this->itsID, 'ST');
    }

    /**
     * Change status of the ST
     *
     * @param string $status string text of status to change to
     *
     * @return string on error
     */
    public function changeStatus($status)
    {
        if (!in_array($status, ['INACTIVE', 'ACTIVE'])) {
            return false;
        }

        $query = <<<EOF
        UPDATE cal_st
        SET status="$status",
            dt_status_changed=now()
        WHERE id=$this->itsID
EOF;

        return tldUtils::sqlQuery($query);
    }

    /**
     * Reassign the ST to a new assignee.
     *
     * @param int $assigneeId id of the person the ST is reassigned to
     *
     * @return string on error
     */
    public function reassign($assigneeId)
    {
        $assigneeId = (int) $assigneeId;
        if (empty($this->itsID) || $assigneeId <= 0) {
            return false;
        }

        $query = <<<EOF
        UPDATE cal_st
        SET assignee=$assigneeId
        WHERE id={$this->itsID}
EOF;

        return tldUtils::sqlQuery($query);
    }

    /**
     * Refresh the header information in the object variable
     *
     */
    public function refresh()
    {
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Create task of the current ST ID
     */
    public function doTask()
    {
        $rows = self::getSTsDueForTask(null, $this->itsID);
        foreach ($rows AS $row) {
            self::createTask($row);
        }
    }

    /**
     * Create tasks for all ST
     *
     * @param string $setdate optional Run date of the ST formatted to SQL date type
     */
    public static function doTasks($setdate = null)
    {
        ini_set('max_execution_time', 0);
        error_log('Get STs due for task');
        $rows = self::getSTsDueForTask($setdate);
        foreach ($rows AS $row) {
            error_log('Try to create task for ST#'.$row['id']);

            try {
                if (true === (bool) $row['auto_close']) {
                    foreach ((new tldST($row['id']))->getOpenTasks() as $openTask) {
                        $task = new tldTask($openTask['id']);

                        $comment = 'The <a href="https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=' . $openTask['id'] . '">task#' . $openTask['id'] . '</a> has been closed as "not done" since a new occurrence is opened and the previous action was not done within the defined period. You can still comment that the action is completed with delay within the new scheduled task. This automatic action has been required by the ST owner during the ST creation for reporting purposes.';
                        $subject = 'Task#' . $openTask['id'] . ' automatically closed as not done';

                        $task->addComment(['comment' => $comment]);
                        $task->notifyAssignee($comment, $subject);
                        $task->update(['closed_not_done' => 1]);
                        $task->close();
                    }
                }

                self::createTask($row);

            } catch (\Exception $e) {
                error_log('Error when generating ST#' . $row['id'] . ' : ' . $e->getMessage());

                if (!empty($row['assignor_email'])) {
                    $subject = 'Task creation failed';
                    $message = 'An error occured when creating a task for ST#' . $row['id'] . ".\n\nError message : " . $e->getMessage();

                    tldUtils::emailAttachment(
                        $row['assignor_email'],
                        'noreply@tld-gse.com',
                        $subject,
                        $message,
                        null,
                        'jonathan.duparo@alvest.fr'
                    );
                }

                continue;
            }
        }
    }

    /**
     * Get rows of ST by date and/or ID
     *
     * @param string $setdate optional Run date of the ST formatted to SQL date type
     * @param int    $id      optional ST ID to run
     *
     * @return array ST rows ready to open for tasks
     */
    public static function getSTsDueForTask($setdate = null, $id = null)
    {
        if (empty($setdate)) {
            $setdate = date('Y-m-d');
        }
        $WHERE = !empty($id) ? "AND cal_st.id=$id" : '';
        $query = <<<EOF
SELECT
    *,
    (SELECT CONCAT(people.lastname,', ',people.firstname) FROM people
        WHERE people.id=cal_st.assignor
    ) AS assignor_fullname,
    (SELECT CONCAT(people.lastname,', ',people.firstname)
        FROM people WHERE people.id=cal_st.assignee
    ) AS assignee_fullname,
    (SELECT location FROM locations
        WHERE locations.id=cal_st.bu_id
    ) AS bu_fullname,
    (SELECT COUNT(*) FROM tasks
        WHERE cal_st.id=tasks.parent_id AND status<>'CLOSED'
    ) AS nb_open_tasks,
    (SELECT email FROM people 
        WHERE people.id=cal_st.assignor
    ) AS assignor_email
FROM
    cal_st
WHERE
    status LIKE 'ACTIVE'
    AND '$setdate' BETWEEN date_start AND date_end
    AND 1 = (
        CASE
            WHEN repd_unit LIKE 'YEAR' THEN
                IF(
                    (YEAR('$setdate')-YEAR(date_start)) MOD repd_val=0
                    AND MONTH('$setdate')=MONTH(date_start)
                    AND DAYOFMONTH('$setdate')=DAYOFMONTH(date_start),
                    1, 0
                )
            WHEN repd_unit LIKE 'MONTH' THEN
                IF(
                    PERIOD_DIFF(DATE_FORMAT('$setdate','%Y%m'),DATE_FORMAT(date_start,'%Y%m')) MOD repd_val=0
                    AND DAYOFMONTH('$setdate')=DAYOFMONTH(date_start),
                    1, 0
                )
            WHEN repd_unit LIKE 'WEEK' THEN
                IF(
                    (DATEDIFF('$setdate',date_start)/7) MOD repd_val=0,
                    1, 0
                )
            WHEN repd_unit LIKE 'DAY' THEN
                IF(
                    DATEDIFF('$setdate',date_start) MOD repd_val=0,
                    1, 0
                )
            ELSE 0
        END
    )
    $WHERE
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Open task for current row
     *
     * @param array $row Result array from getSTsDueForTask to open
     */
    public static function createTask($row)
    {
        if (empty($row)) {
            return;
        }

        // if no module definded, return.
        if (empty($row['module'])) {
            error_log('ERROR: No module specified in ST#' .$row['id']);

            return;
        }

        // if module specidied is not in the tasks "authorized" modules return.
        if (!in_array($row['module'], tldTask::getModuleList())) {
            error_log('ERROR: Module ' .$row['module']. ' is not valid');

            return;
        }

        //set the var needed to insert the new task
        $parent_id = $row['id'];
        $module = $row['module'];

        if ($row['referencetype'] === 'GWF' && ($gwf = new tldGWF($row['reference'])) && !$gwf->isEmpty()) {
            $module = 'GWF';
            $parent_id = $row['reference'];
        }

        //setup the description
        $taskDesc = 'A new task was auto-generated from ST#' .$row['id'].
            "\n\n Type: ".$row['type'].
            "\n Location: ".$row['bu_fullname'].
            "\n Description: \n".$row['description'];

        $vals = [
            'module' => $row['module'],
            'assignor' => $row['assignor'],
            'assignee' => $row['assignee'],
            'task' => $taskDesc,
            'bu_id' => $row['bu_id'],
            'escalation_trigger' => $row['escalation_trigger'],
        ];
        // setup the due date depending on the leadtime
        if ($row['leadtime_value'] != 0) {
            $vals['due_date'] = ['value' => $row['leadtime_value'], 'unit' => $row['leadtime_unit']];
        } elseif ($row['leadtime_value'] == 0 || $row['leadtime_value'] == null) {
            $vals['due_date'] = ['value' => '0', 'unit' => 'DAY'];
        }

        $task = tldUtils::cleanupFormInput($vals);
        //insert new task
        $error_task = tldTask::insert($parent_id, $task, $module);
        if (!is_numeric($error_task)) {
            error_log("Could not create new task. There was an error processing. The error returned is '$error_task'");
            return;
        }
        $task = new tldTask($error_task);

        // built the email notification for the newly created task
        $assignee = new tldUser($task->getAssignee());
        $assignor = new tldUser($task->getAssignor());
        $assignee_fullname = $assignee->getFullname();
        $message = <<<EOF
            Task #$error_task has been assigned to $assignee_fullname.\n<br>
            Please log in to the TLD-GSE intranet and go to the calendar module to view your open tasks.\n<br>
            <a href="https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$error_task">
            Click here to go to Task.
            </a>
            <br>
            Task:<br>
EOF;

        // make and send the email notification of the newly created task to the assignee.
        $message .= $task->getTask();
        $subject = "Tasks, New: #$error_task opened for $assignee_fullname by " .$assignor->getFullname();
        $task->notifyAssignee($message, $subject);

        error_log("Task #$error_task successfully opened from ST# ".$row['id']);
    }

    public function getLinksFromHere($type = '')
    {
        return tldModLink::byParent($this->itsID, 'ST', $type);
    }

    public function getLinksToHere($module = '')
    {
        return tldModLink::byItem($this->itsID, 'ST', $module);
    }

    public static function getScheduledTasksDMSReport(array $a, string $erp = '', string $status = '', string $start = '', string $end = '') : array
    {
        $dmsList = implode(',', $a);
        $where = [];

        if ($erp !== 'ALL') {
            $where[] = " cal_st.bu_id=$erp ";
        }
        if ($status !== 'ALL') {
            $where[] = " cal_st.status='$status' ";
        }

        if (!empty($start)) {
            $where[] = " DATE_FORMAT(tasks.date,'%Y-%m-%d') >= '$start' ";
        }
        if (!empty($end)) {
            $where[] = " DATE_FORMAT(tasks.date,'%Y-%m-%d') <= '$end'  ";
        }

        $WHERE = $where ? ' AND ' .implode(' AND ', $where) : '';

        $query = <<<SQL
SELECT
       dms.id AS dms_id,
       CONCAT(cal_st.referencetype, ' ', cal_st.reference) AS dms,
       dms.parent_id AS parent,
       cal_st.id AS st,
       cal_st.description AS st_description,
       tasks.status,
       COUNT(CASE WHEN tasks.status = 'CLOSED' THEN 1 END) AS closed,
       COUNT(CASE WHEN tasks.status IN ('OPEN', 'IN PROGRESS') THEN 1 END) AS open,
       COUNT(CASE WHEN (tasks.status IN ('OPEN', 'IN PROGRESS') AND TO_DAYS(now()) - TO_DAYS(tasks.due_date) < 0) THEN 1 ELSE NULL END) AS not_due,
       COUNT(CASE WHEN (tasks.status IN ('OPEN', 'IN PROGRESS') AND TO_DAYS(now()) - TO_DAYS(tasks.due_date) > 0) THEN 1 ELSE NULL END) AS due,
       COUNT(CASE WHEN tasks.status = 'CLOSED' AND closed_not_done = 1 THEN 1 END) AS not_done,
       locations.location as assignee_bu 
FROM tasks
INNER JOIN cal_st ON tasks.parent_id = cal_st.id
INNER JOIN dms ON (cal_st.reference = dms.id AND referencetype = 'DMS')
LEFT JOIN people ON cal_st.assignee = people.id
LEFT JOIN locations ON people.bu_id = locations.id
WHERE dms.id IN ($dmsList)
AND tasks.module = 'ST'
$WHERE
GROUP BY cal_st.id;
SQL;
        return tldUtils::getSqlToAssocArray($query);
    }

}

class tldSRM
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

    /*******************************************
     * CRUD functions
     *******************************************/

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
    srm
EOF;
    }

    public function getHeader()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
WHERE srm.id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public static function byConstraints($a, $opt = [])
    {
        if (empty($a)) {
            return 'empty parameter';
        }

        $HAVING = is_array($a) ? 'HAVING ' .tldUtils::constructWhere($a) : "HAVING $a";
        $LIMIT = !empty($opt['limit']) ? "LIMIT  {$opt['limit']}" : '';
        $ORDERBY = !empty($opt['orderBy']) ? "ORDER BY {$opt['orderBy']}" : 'ORDER BY id';

        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
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
        $fields = _FIELDS_ARRAY_;
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO srm SET $SET";

        return tldUtils::sqlInsert($query);
    }

    public function update($a, $fields = '')
    {
        if (empty($a)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "UPDATE srm SET $SET WHERE id=$this->itsID";

        return tldUtils::sqlQuery($query);
    }

    public function delete()
    {
        $query = "DELETE FROM srm WHERE id=$this->itsID";

        return tldUtils::sqlExecute($query);
    }

    /*******************************************
     * COMMON MOD methods
     ******************************************
     *
     * @param     $uid
     * @param     $comment
     * @param int $num_log
     *
     * @return bool
     */

    public function addLogEntry($uid, $comment, $num_log = 0)
    {
        return tldModLog::insert(
            [
                'parent_id' => $this->itsID,
                'module'    => 'SRM',
                'poster'    => $uid,
                'comment'   => $comment,
                'log_num'   => $num_log,
            ]
        );
    }

    public function getLog()
    {
        return tldModLog::byParent($this->itsID, 'SRM');
    }

    /*******************************************
     *   STATIC and REFERENCES methods
     ********************************************/

    public static function getStatusList()
    {
        return [
            'BOOKED',
            'CANCELED',
        ];
    }

    /*******************************************
     * LOGIC and ACTION methods
     *******************************************/

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public function getAllowedStatus()
    {
        $result = [];
        switch ($this->getStatus()) {
            case 'BOOKED':
                $result = ['CANCELED'];
                break;
        }

        return $result;
    }

    public function updateStatus($uid, $newStatus)
    {
        $allowedStatus = $this->getAllowedStatus();
        if (empty($allowedStatus)) {
            return "Status $newStatus not allowed";
        }
        $e = $this->update(['status' => $newStatus]);
        if (is_string($e)) {
            return $e;
        }
        // do actions?
        // ...
        // Add log
        $this->addLogEntry($uid, $newStatus);

        return;
    }

    public static function getItemList()
    {
        return self::getItemsByConstraints('1=1');
    }

    public static function getItemsByConstraints($a, $opt = '')
    {
        // Look for constraints
        if (is_array($a)) {
            $HAVING = 'HAVING ' .tldUtils::constructWhere($a);
        } else {
            $HAVING = "HAVING $a";
        }
        $query = <<<EOF
SELECT * FROM srm_item $HAVING
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function sendEmail($to, $from, $subject, $body, $cc = '')
    {
        return tldUtils::emailAttachment(
            $to,
            $from,
            $subject,
            $body,
            null,
            $cc
        );
    }

    public function notifyOwner()
    {
    }

    /*******************************************
     * STATS and REPORTS methods
     ******************************************
     *
     * @param int $limit
     *
     * @return array
     */

    public static function byLatest($limit = 10)
    {
        return self::byConstraints('1=1', ['limit' => $limit, 'orderBy' => 'id DESC']);
    }

    /*******************************************
     * VIEW methods
     *******************************************/

    public function getGeneralView()
    {
        $report = new tldAssocTable(
            $this->itsHeader,
            [],
            ['title' => "SRM#$this->itsID Details"]
        );

        return $report->fetch();
    }

    public function getPrintVersion()
    {
        return $this->getGeneralView();
    }

}
