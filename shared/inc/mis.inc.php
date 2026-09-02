<?php
/**
 *    MIS related classes
 *
 * @package MIS
 * @desc All classes related to the MIS are kept in this file
 * @access public
 * @author Graham K.L. Fong <graham.fong@tld-america.com>
 * @copyright TLD
 */

include_once('calendar.inc.php');
include_once('forms_and_reports.inc.php');
include_once('publications.inc.php');

class tldMIS
{
    public const CATEGORIES_BY_SERVICE = [
        'DEV' => [
            'WEBSITE',
            'WEBSITE, Customer ePARTS',
            'WEBSITE, Customer EXTRANET',
            'WEBSITE, DMS',
            'WEBSITE, eQuotes',
            'WEBSITE, eVendor',
            'WEBSITE, INTRANET',
            'WEBSITE, SHOPFLOOR',
        ],
        'INFRA' => [
            'BACKUP',
            'DESK/OFFICE PHONE',
            'EMAIL',
            'HARDWARE',
            'NETWORK',
            'PURCHASING REQUEST',
            'SECURITY',
            'SHOPFLOOR TABLET',
            'SOFTWARE',
            'Solidworks/PDMworks/SeeElec',
        ],
        'ERP' => [
            'BAAN',
            'Birst Reports',
            'CPQ - Infor',
            'ERP',
            'ERP - EAM/SUN',
            'ERP - Infor LN',
            'Factory Track',
        ],
        'NO_MIS_TTS' => [
            '',
            '0',
            'AGILE',
            'CELL PHONE',
            'COMMUNICATION',
            'KELIO',
            'LINK FMS',
            'OTHER',
            'SUPPLIES',
        ],
    ];

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
	public function getHeader()
	{
		$query = "SELECT * FROM mis_tts WHERE id=$this->itsID";

		return tldUtils::getSqlRowToAssocArray($query);
	}

	public function getID()
	{
		return $this->itsID;
	}

	public function getIFactor()
	{
		return $this->itsHeader['ifactor'];
	}

	public function getInitiatorEmail($uid)
	{
		$sender = new tldUser($uid);
		$to[] = $sender->getEmail();

		$owner = new tldUser($this->itsHeader['owner']);
		$to[] = $owner->getEmail();

		$assignee = new tldUser($this->itsHeader['assignee']);
		$to[] = $assignee->getEmail();
		return $to;
	}

	public static function getBuildingListByLocation($id, $format = '')
	{
		$query = "SELECT * FROM mis_inventory_buildings WHERE disable=0 AND location_id=$id ORDER BY name";
		switch ($format) {
			case 'smartyOptions':
				$result = tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['id', 'name']);
				break;
			case 'smartyOptionsLocationLocation':
				$result = tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['name', 'name']);
				break;
			case 'smartyOptionsERPLocation':
				$result = tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['erp', 'name']);
				break;
			default:
				$result = tldUtils::getSqlToAssocArray($query);
		}
		return $result;
	}


	public function notifyInitiator($uid, $message, $subject = '', $cc = [])
	{
		// Get initiator
		$TO = $this->getInitiatorEmail($uid);
		$misGroup = new tldGroup('role_CIO');
		foreach ($misGroup->getEmailList() as $email) {
			$cc[] = $email;
		}
		// Send the notification
		return $this->notify($TO, 'noreply@tld-gse.com', $subject, $message, $cc);
	}

	public function notify($to, $from, $subject, $message, $cc = '')
	{
		$message .= <<<EOF
<p><a href="https://www.tld-gse.com/en/private/mis/mis.php?m[0]=tts&m[1]=view&id=$this->itsID">
Click here to go to MIS Project#$this->itsID.</a></p>
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

	public function setIF($p)
	{

		if (empty($this->itsID)) {
			return;
		}
		$query = <<<EOF
UPDATE mis_tts
SET ifactor= $p
WHERE id=$this->itsID
LIMIT 1
EOF;

		$e = tldUtils::sqlQuery($query);

		return $e;
	}

	public static function insert($p)
	{
		$fields = ['domain', 'category', 'location', 'ifactor', 'eta', 'budget_hours', 'owner', 'assignee', 'problem', 'solution', 'dt_opened'];
		$query = 'INSERT INTO mis_tts SET ';
		$query .= tldUtils::getSqlSet($p, $fields);

		return tldUtils::sqlInsert($query);
	}

	public function addLog($id, $comment)
	{
		$a['parent_id'] = $this->itsID;
		$a['module'] = 'TTS';
		$a['poster'] = $id;
		$a['comment'] = $comment;
		return tldModLog::insert($a);
	}

    public static function getDomain()
    {
        $query = 'SELECT DISTINCT domain FROM mis_tts';

        return tldUtils::getSqlToAssocArray($query, 'smartyOptions', 'domain');
    }
}

/**
 * Class for manipulating MIS Trouble Tickets
 *
 * @package MIS
 */
class tldTTS
{
	public const ASSIGNED_TO_MOO = 'Assigned to MOO';
	public const ASSIGNED_TO_ASSIGNOR = 'Assigned to Assignor';
	public const ASSIGNED_TO_DEV = 'Assigned to a Dev';

	public function __construct($id)
	{
		$this->itsID = $id;
		$this->itsHeader = $this->getHeader();
	}

	/**
	 * Mark the ticket with status CLOSED
	 *
	 * @param integer userid of person closing the ticket
	 * @return string Error message string if any
	 */
	public function close($closer)
	{
		//check if all tasks are closed
		if (count($this->getTasks('openOnly'))) {
			return 'ERROR: Cannot close ticket with OPEN tasks...';
		}
		$id = $this->itsID;
		$this->postComment($closer, 'Ticket closed ' . date('Y-m-d'));
		$query = <<<EOF
		UPDATE mis_tts
		SET status='CLOSED', dt_closed=now()
		WHERE id=$id
		LIMIT 1
EOF;
		return tldUtils::sqlQuery($query);
	}

	/**
	 * Update TTS status
	 * @param string $status
	 * @return string on error
	 */
	public function changeStatus($status)
	{
		if (empty($this->itsID)) {
			return 'Not in object context';
		}
		// Check new status
		$allowed = $this->getStatusAllowed();
		if (!is_array($allowed)) {
			return 'Could not get list of allowed statuses';
		}
		if (!in_array($status, $allowed)) {
			return "Could not change status to $status";
		}
		if ($status === 'PHASE 2') {
			$projinfo = $this->getHeader();
			$DPO = new tldGroup('ROLE_DPO', 900);
			$users = $DPO->getUserlist();
			$parent_id = $this->itsID;
			$module = 'TTS';
			$taskDesc = 'Dear MOO<br><br>You are requested to formally accept the release in production of this MIS project - <b>' . $projinfo['problem'] . '</b><br>Please clearly state your consent (or not) by adding a comment while closing the task<br><br>Thanks & Best Regards';
			$vals = [
				'module' => $module,
				'assignor' => $users[0]['id'],
				'assignee' => $projinfo['owner'],
				'task' => $taskDesc,
				'bu_id' => $projinfo['location'],
				'escalation_trigger' => '30',

			];
			// setup the due date depending on the leadtime

			$vals['due_date'] = ['value' => '0', 'unit' => 'DAY'];
			$task = tldUtils::cleanupFormInput($vals);
			//insert new task
			$error_task = tldTask::insert($parent_id, $task, $module);
			if (!is_numeric($error_task)) {
				error_log("Could not create new task. There was an error processing. The error returned is '$error_task'");
			} else {
				$task = new tldTask($error_task);
				// built the email notification for the newly created task
				$assignee = new tldUser($task->getAssignee());
				$assignor = new tldUser($task->getAssignor());
				$assignee_fullname = $assignee->getFullname();
				$message = <<<EOF
			Task #$error_task has been assigned to $assignee_fullname.\n<br>
			Please log in to the TLD-GSE intranet and go to the calendar module to view your open tasks.\n<br>
			<a href="https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$error_task">

			Click here to go to Task.</a>
		<br>
		Task:<br>
EOF;
				$message .= $task->getTask();
				$subject = "Tasks, New: #$error_task opened for " . $assignee->getFullname() . ' by ' . $assignor->getFullname();
				$task->notifyAssignee($message, $subject);
			}
		}
		if ($status === 'PHASE 4') {
			$projinfo = $this->getHeader();
			$parent_id = $this->itsID;
			$module = 'TTS';
			$taskDesc = 'This task is opened for MOO to check translations in the current project - ' . $projinfo['problem'] . '.<br><br>In case the current DEV was related to a new or an existing Symfony module please make sure translations in FR and ZH and any other requested language have be handled properly before closing this Task.<br><br>Thanks<br>';
			$vals = [
				'module' => $module,
				'assignor' => $projinfo['owner'],
				'assignee' => $projinfo['owner'],
				'task' => $taskDesc,
				'bu_id' => $projinfo['location'],
				'escalation_trigger' => '30',

			];
			// setup the due date depending on the leadtime

			$vals['due_date'] = ['value' => '0', 'unit' => 'DAY'];
			$task = tldUtils::cleanupFormInput($vals);
			//insert new task
			$error_task = tldTask::insert($parent_id, $task, $module);
			if (!is_numeric($error_task)) {
				error_log("Could not create new task. There was an error processing. The error returned is '$error_task'");
			} else {
				$task = new tldTask($error_task);
				// built the email notification for the newly created task
				$assignee = new tldUser($task->getAssignee());
				$assignor = new tldUser($task->getAssignor());
				$assignee_fullname = $assignee->getFullname();
				$message = <<<EOF
			Task #$error_task has been assigned to $assignee_fullname.\n<br>
			Please log in to the TLD-GSE intranet and go to the calendar module to view your open tasks.\n<br>
			<a href="https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$error_task">

			Click here to go to Task.</a>
		<br>
		Task:<br>
EOF;
				$message .= $task->getTask();
				$subject = "Tasks, New: #$error_task opened for " . $assignee->getFullname() . ' by ' . $assignor->getFullname();
				$task->notifyAssignee($message, $subject);
			}
		}
		if ($status === 'CLOSED') {
			$SET = ', dt_closed=NOW()';
		}
		$query = <<<EOF
        UPDATE mis_tts
        SET status=UCASE('$status')
        $SET
        WHERE id=$this->itsID
        LIMIT 1
EOF;
		return tldUtils::sqlQuery($query);
	}

	/**
	 * Get list of allowed status regarding actual status
	 * @return array
	 */
	public function getStatusAllowed()
	{
		if (empty($this->itsID)) {
			return 'Not in object context';
		}
		switch ($this->getStatus()) {
			case 'PHASE 0':
				$result = ['PHASE 1'];
				break;
			case 'PHASE 1':
				$result = ['PHASE 2', 'PHASE 0'];
				break;
			case 'PHASE 2':
				$result = ['PHASE 3', 'PHASE 1'];
				break;
			case 'PHASE 3':
				$result = ['PHASE 4', 'PHASE 2'];
				break;
			case 'PHASE 4':
				$result = ['CLOSED', 'PHASE 3'];
				break;
		}
		return array_combine($result, $result);
	}

	/**
	 * Answers the question whether the ticket is empty i.e. not valid
	 *
	 * @return bool true if ticket is empty
	 */
	public function isEmpty()
	{
		return empty($this->itsHeader);
	}

	public function isQueue()
	{
		return $this->getStatus() === 'QUEUE';
	}

	/**
	 * Returns a db row array containting the header information for current ticket#
	 *
	 * @return array
	 */
	public function getHeader()
	{
		$id = TldDatabase::escape($this->itsID);
		$query = <<<EOF
		SELECT mis_tts.*,
			(TO_DAYS(now())-TO_DAYS(mis_tts.dt_opened)) AS daysOpen,
			(SELECT CONCAT(owner.lastname,', ',owner.firstname)
			FROM people AS owner
			WHERE mis_tts.owner=owner.id
			) AS owner_fullname,
			(SELECT CONCAT(assignee.lastname,', ',assignee.firstname)
			FROM people AS assignee
			WHERE mis_tts.assignee=assignee.id
			) AS assignee_fullname,
			locations.company_name
		FROM mis_tts LEFT JOIN locations ON mis_tts.assignee=locations.id
		WHERE mis_tts.id=$id
EOF;
		return tldUtils::getSqlRowToAssocArray($query);
	}

	/**
	 * Returns ticket information in array representation
	 *
	 * @return array
	 */
	public function asArray()
	{
		$result = $this->getHeader();
		$owner = new tldUser($result['owner']);
		$result['owner'] = $owner->getDetails();
		$assignee = new tldUser($result['assignee']);
		$result['assignee'] = $assignee->getDetails();
		$result['notes'] = $this->getNotes();
		return $result;
	}

	/**
	 * Answers whether the ticket is closed yet
	 *
	 * @return bool
	 */
	public function isClosed()
	{
		return $this->itsHeader['status'] === 'CLOSED';
	}

	/**
	 * Returns db array rows of containing notes associated with ticket
	 *
	 * @return array
	 */
	public function getNotes()
	{
		$id = TldDatabase::escape($this->itsID);
		$query = <<<EOF
		SELECT mis_tts_lines.*,
			CONCAT(p.firstname,' ',p.lastname) AS poster_fullname
		 FROM mis_tts_lines LEFT JOIN people AS p ON mis_tts_lines.poster=p.id
		WHERE mis_tts_lines.parent_id=$id
		ORDER BY mis_tts_lines.id DESC
EOF;
		return tldUtils::getSqlToAssocArray($query);
	}

	/**
	 * Add members to TTS
	 * @param mixed array or $members
	 */
	public function addMember($members)
	{
		if (is_array($members)) {
			foreach ($members AS $member) {
				tldModMember::insert('TTS', $this->itsID, $member);
			}
		} elseif (!empty($members)) {
			tldModMember::insert('TTS', $this->itsID, $members);
		}
	}

	/**
	 * Get list of Members
	 */
	public function getMembers()
	{
		return tldModMember::byParent($this->itsID, 'TTS');
	}

    public static function getMembersList($id, $level = null, $options = 'smartyOptions')
    {
        $WHERE = '';
        if (isset($options['where']) && is_string($options['where'])) {
            $WHERE .= ' AND ' . $options['where'];

        }

        $query = <<<SQL
SELECT
    T1.*,CONCAT(UPPER(T1.lastname),', ',T1.firstname, ' (', T1.email, ')') AS fullname,
    TTS.module,TTS.list_name
FROM mod_lists AS TTS
    LEFT JOIN people AS T1 ON T1.id = TTS.value
WHERE TTS.module='TTS' AND TTS.parent_id='$id' AND list_name='MEMBERS' 
    AND T1.hidden=0 AND T1.disabled='N'
	$WHERE
SQL;
        if (isset($options)) {
            if ((is_array($options) && $options['smartyOptions']) || $options === 'smartyOptions') {
                return tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['id', 'fullname']);
            }

            if (is_array($options) && $options['smartyOptionsTech_name']) {
                return tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['fullname', 'fullname']);
            }
        }
        return tldUtils::getSqlToAssocArray($query);
    }
	/**
	 * Check if user ID is a member of this TTS
	 *
	 * @param int $uid
	 * @return boolean
	 */
	public function isMember($uid)
	{
		if ($this->getAssignor() == $uid) {
			return true;
		}
		return tldModMember::isMember('TTS', $this->itsID, $uid);
	}

	/**
	 * get an array of db rows of related tasks
	 *
	 * @return array
	 */
	public function getTasks($constraints = null)
	{
		// Construct constraints
		if (is_array($constraints)) {
			$WHERE = tldUtils::constructWhere($constraints);
		} elseif (!empty($constraints)) {
			$WHERE = $constraints;
		}
		if (!empty($WHERE)) {
			$WHERE = "AND $WHERE";
		}
		// query
		$query = <<<EOF
SELECT
    tasks.*,
    CONCAT(assignee.lastname,', ',assignee.firstname) as assignee_fullname,
    CONCAT(assignor.lastname,', ',assignor.firstname) as assignor_fullname,
    IF(tasks.due_date<NOW(), 1, 0) AS overdue,
    (SELECT comment FROM tasks_comments
       WHERE parent_id=tasks.id ORDER BY date DESC LIMIT 1
    ) AS lastcomment,
    IFNULL(
        (SELECT value FROM mod_lists WHERE module LIKE 'TASKS' and parent_id=tasks.id AND list_name LIKE 'tts_theme' ORDER BY value LIMIT 1),
        'NO_THEME'
    ) AS tts_theme
FROM
    tasks
    LEFT JOIN people AS assignee ON tasks.assignee=assignee.id
    LEFT JOIN people AS assignor ON tasks.assignor=assignor.id
WHERE
    tasks.parent_id=$this->itsID AND module LIKE 'TTS'
    $WHERE
ORDER BY
    tts_theme, tasks.status DESC, tasks.due_date
EOF;

		return tldUtils::getSqlToAssocArray($query);
	}

	/**
	 * Get linked files to this TTS
	 *
	 * @return array
	 */
	public function getFiles()
	{
		return tldModFile::byParent($this->itsID, 'TTS');
	}

	/**
	 * Get linked log entries
	 * @return array
	 */
	public function getLog()
	{
		return tldModLog::byParent($this->itsID, 'TTS');
	}

	/**
	 * Get status log entries
	 * @return array
	 */
	public function getLogStatus()
	{
		return tldModLog::byParent($this->itsID, 'TTS', 1);
	}

	public function getTimesheets()
	{
		return tldTimekeeping::getTimesheetsByMISProjectByConstraints(['mis_tts.id' => $this->getID()]);
	}

	/**
	 * Get TTS ID
	 * @return int id
	 */
	public function getID()
	{
		return $this->itsID;
	}

	/**
	 * Returns the userid of the ticket owner
	 *
	 * @return integer
	 */
	public function getOwner()
	{
		return $this->itsHeader['owner'];
	}

	/**
	 * Returns the ticket status
	 *
	 * @return string
	 */
	public function getStatus()
	{
		return $this->itsHeader['status'];
	}

	/**
	 * Get the email domain of the owner
	 *
	 * @return string
	 */
	public function getDomain()
	{
		return $this->itsHeader['domain'];
	}

	/**
	 * Returns the userid of the ticket assignee
	 *
	 * @return integer
	 */
	public function getAssignee()
	{
		return $this->itsHeader['assignee'];
	}

	public function getDueDate()
	{
		return $this->itsHeader['eta'];
	}

	public function setDueDatePlusMonths($m)
	{
		if (empty($this->itsID)) {
			return;
		}
		$query = <<<EOF
UPDATE mis_tts
SET eta = date_add(eta, INTERVAL $m MONTH)
WHERE id=$this->itsID
LIMIT 1
EOF;
		$e = tldUtils::sqlQuery($query);
		$this->refresh();
		return $e;
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
		$a['module'] = 'TTS';
		$a['poster'] = $id;
		$a['comment'] = $comment;
		$this->notify(
			$id,
			"New log entry for MIS project#{$this->itsID} - {$this->itsHeader['problem']}",
			"New log entry: <ul><li>$comment</li></ul>"
		);
		return tldModLog::insert($a);
	}

	public function addLogStatus($id, $comment)
	{
		$a['parent_id'] = $this->itsID;
		$a['module'] = 'TTS';
		$a['poster'] = $id;
		$a['comment'] = $comment;
		$a['log_num'] = 1;
		return tldModLog::insert($a);
	}

	public function refresh()
	{
		$this->itsHeader = $this->getHeader();
	}

	/**
	 * Returns the short desc of the ticket
	 *
	 * @return string
	 */
	public function getShortDesc()
	{
		return substr($this->itsHeader['problem'], 0, 1000);
	}

	/**
	 * Send an email message to owner and mis team
	 *
	 * @param ineger $uid userid of the person sending the message
	 * @param string $subject Subject string that appears in the email message
	 * @param string $message Message text
	 * @return bool true on successfully sending email
	 */
	public function notify($uid, $subject = 'No Subject', $message = 'Empty message')
	{
		if (empty($uid)) {
			return 'Sender id not set in tldTTS->notifyAll';
		}
		$id = $this->itsID;
		$sender = new tldUser($uid);
		$to[] = $sender->getEmail();

		$owner = new tldUser($this->getOwner());
		$to[] = $owner->getEmail();

		$assignee = new tldUser($this->getAssignee());
		$to[] = $assignee->getEmail();

		$misGroup = new tldGroup('role_CIO');
		foreach ($misGroup->getEmailList() as $email) {
			$cc[] = $email;
		}
		return tldUtils::emailAttachment(
			implode(',', $to),
			$sender->getEmail(),
			$subject,
			nl2br($message),
			null,
			$cc
		);
	}

	/**
	 * Deletes the current ticket and associated tasks
	 *
	 */
	public function deleteTicket()
	{
		if (empty($this->itsID)) {
			return;
		}
		$e = tldTask::deleteTask('TTS', $this->itsID);
		$query = <<<EOF
			DELETE FROM mis_tts
			WHERE id=$this->itsID
			LIMIT 1
EOF;
		return tldUtils::sqlQuery($query);
	}

	/**
	 * Create a new ticket
	 *
	 * @param integer $owner Userid of the ticket owner
	 * @param string $problem Text string describing the problem being reported
	 * @param string $category Category of the ticket, defaults to OTHER
	 * @return integer Returns the id number of the new ticket
	 */
	public function newTicket($owner, $problem, $category = 'OTHER')
	{
		//select the assignee based on the owner's email domain
		$user = new tldUser($owner);
		list($username, $domain) = preg_split('/@/', $user->getEmail());
		$domain = strtolower($domain);
		switch ($domain) {
			case 'tld-europe.com':
			case 'tld-group.com':
				$assign = ['ERP' => '59',
					'NETWORK' => '246',
					'USER' => '246',
					'TELEPHONY' => '246',
					'EMAIL' => '59',
					'INTRANET' => '246',
					'WEBSITE' => '63',
					'OTHER' => '246',
				];
				break;
			case 'tld-asia.com':
				$assign = ['ERP' => '103',
					'NETWORK' => '103',
					'USER' => '103',
					'TELEPHONY' => '103',
					'EMAIL' => '103',
					'INTRANET' => '103',
					'WEBSITE' => '63',
					'OTHER' => '103',
				];
				break;
			default:
				$assign = ['ERP' => '104',
					'NETWORK' => '418',
					'USER' => '418',
					'TELEPHONY' => '418',
					'EMAIL' => '63',
					'INTRANET' => '63',
					'WEBSITE' => '63',
					'OTHER' => '418',
				];
		}
		$assignee = $assign[$category];

		$owner = TldDatabase::escape($owner);
		$problem2 = TldDatabase::escape($problem);

		$query = <<<EOF
		INSERT INTO mis_tts
		SET status='PHASE 0',
		domain='$domain',
		dt_opened=now(),
		owner='$owner',
		assignee='$assignee',
		problem='$problem2',
		category='$category'
EOF;
		$error = tldUtils::sqlInsert($query);
		$ticket = new tldTTS($error);
		$ticket->postComment($owner, 'Ticket opened ' . date('Y-m-d'));
		$task = ['assignee' => $assignee,
			'assignor' => $owner,
			'task' => "Please initiate troubleshooting for TTS# $error" .
				"<br>$problem",
			'bu_id' => $user->itsDetails['bu_id']
//										,
//						'due_date'	=>array(	'Y'=>date('Y'),
//												'm'=>date('m'),
//												'd'=>date('d')
//										)
		];

		$vals = tldUtils::cleanupFormInput($task);
		$taskid = tldTask::insert($error, $vals, 'TTS');
		return $error;
	}

	/**
	 * Add a comment to this ticket
	 *
	 * @param integer $poster id# of poster
	 * @param string $comment String text of comment to add to ticket
	 * @param string $file Path to file uploaded file after moving it to uploads/mis_tts_lines
	 */
	public function postComment($poster, $comment, $file = '')
	{
		if (empty($comment) || empty($poster)) {
			return 'ERROR: Comment or poster is empty';
		}
		$filename = basename($file);
		$query = 'INSERT INTO mis_tts_lines set parent_id=' . TldDatabase::escape($this->itsId) .
			", date=now(), poster='" . TldDatabase::escape($poster) .
			"', comment='" . TldDatabase::escape($comment) .
			"', filename='" . TldDatabase::escape($filename) . "'";
		$commentid = tldUtils::sqlInsert($query);
		/*		if(is_numeric($commentid)){
                    if($poster <> $this->getOwner()){
                        $status = "PENDING OWNER";
                    }else{
                        $status = "PENDING MIS";
                    }
                    $id = $this->itsId;
                    $query =<<<EOF
                    UPDATE mis_tts
                    SET status='$status'
                    WHERE id=$id
        EOF;
                    tldUtils::sqlQuery($query);
        */
		return $commentid;
//		}
	}

	/**
	 * Get array of tickets grouped by owner id
	 *
	 * @return array
	 */
	public function groupedByOwner()
	{
		$query = <<<EOF
			SELECT mis_tts.*,
				CONCAT(people.firstname,' ',people.lastname) AS owner_fullname
			FROM mis_tts LEFT JOIN people ON mis_tts.owner=people.id
			WHERE category<>'MIS' AND status<>'CLOSED'
			ORDER BY status, id DESC
EOF;
		return tldUtils::getSqlToAssocArray($query);
	}


	/**
	 * Convert tts#s to array of tickets
	 *
	 * @param array $ttNums pass in array of tt nums
	 *
	 * @return array
	 */
	public function getTTNumstoArrays($ttNums)
	{
		if (count($ttNums ?? []) == 0) {
			return [];
		}
		foreach ($ttNums as $ttNum) {
			$ticket = new tldTTS($ttNum['id']);
			$tickets[] = $ticket->asArray();
		}
		return $tickets;
	}

	/**
	 * Get all open tickets
	 *
	 * @param string $category option to get tickets
	 * 1. withNoTasks associated
	 * 2. unassigned tickets
	 * 3. only mis tickets
	 * @return array
	 */
	public function getAllOpenTickets($category = '')
	{
		switch ($category) {
			case 'withNoTasks':
				$query = <<<EOF
			SELECT mis_tts.*
			FROM mis_tts LEFT JOIN tasks ON mis_tts.id=tasks.parent_id
				AND tasks.status='OPEN' AND tasks.module='TTS'
			WHERE mis_tts.status='OPEN'
			AND tasks.id IS NULL
			ORDER BY ifactor DESC
EOF;
				break;
			case 'unassigned':
				$query = "SELECT * FROM mis_tts WHERE status<>'CLOSED' AND assignee='' ORDER BY ifactor DESC";
				break;
			case 'mis':
				$query = "SELECT * FROM mis_tts WHERE status<>'CLOSED' AND category='MIS' ORDER BY ifactor DESC";
				break;
			case 'internal':
				$query = "SELECT * FROM mis_tts WHERE status<>'CLOSED' AND category='INTERNAL' ORDER BY ifactor DESC";
				break;
			default:
				$query = "SELECT * FROM mis_tts WHERE status<>'CLOSED' AND category<>'MIS' ORDER BY ifactor DESC";
		}
		return tldUtils::getSqlToAssocArray($query);
	}

	/**
	 * Get list of queues
	 *
	 * @return array array of db rows
	 */
	public static function getQueueList($domain = '')
	{
		if ($domain) {
			$WHERE = " AND domain='$domain'";
		}
		$query = <<<EOF
		SELECT mis_tts.*
		FROM mis_tts
		WHERE mis_tts.status='QUEUE'
		$WHERE
		ORDER BY domain, category
EOF;
		return tldUtils::getSqlToAssocArray($query);
	}

	/**
	 * Get list of queues
	 *
	 * @return array array of db rows
	 */
	public static function getProjectList($domain = '')
	{
		if ($domain) {
			$WHERE = " AND domain='$domain'";
		}
		$query = <<<EOF
		SELECT mis_tts.*
		FROM mis_tts
		WHERE mis_tts.status NOT IN ('QUEUE','CLOSED')
		$WHERE
		ORDER BY domain, category
EOF;
		return tldUtils::getSqlToAssocArray($query);
	}

	/**
	 * get tickets by owner
	 *
	 * @param integer $id Userid of owner of tickets
	 * @return array
	 */
	public static function byOwner($id)
	{
		$query = <<<EOF
		SELECT mis_tts.*,
			CONCAT(people.firstname,' ',people.lastname) AS owner_fullname
		FROM mis_tts LEFT JOIN people ON mis_tts.owner=people.id
		WHERE status<>'CLOSED'
			AND owner=$id
		ORDER BY ifactor,id DESC
EOF;
		return tldUtils::getSqlToAssocArray($query);
	}

	public static function byModuleStatusAndDeveloper(array $statuses, array $developers, array $moos): array
	{
		$statusSums = array_map(static function (string $status) {
			$alias = str_replace(' ', '_', $status);
			return "SUM(IF(ticket.status = '$status', 1, 0)) as $alias";
		}, $statuses);
		$ifs = ['IF 1' => 1, 'IF 1000' => 1000];
		$ifSums = [];
		foreach ($ifs as $key => $value) {
			$ifSums[] = "SUM(IF(ticket.status !='CLOSED' AND ticket.ifactor = '$value', 1, 0)) as '$key'";
		}
		$categorySums = [];
		foreach (['B', 'C'] as $category) {
			$categorySums[] = "SUM(IF(ticket.status NOT IN ('CLOSED', 'PAUSE') AND ticket.cat = '$category', 1, 0)) as '$category'";
		}

		$sumsSelect = implode(', ', array_merge($statusSums, $ifSums, $categorySums));

		$assignedToMOO = self::ASSIGNED_TO_MOO;
		$assignedToAssignor = self::ASSIGNED_TO_ASSIGNOR;
		$assignedToDev = self::ASSIGNED_TO_DEV;
		$cAssignedToDev = 'C ' . self::ASSIGNED_TO_DEV;

		$whereConditions = [
			sprintf(' module.uid IN (%s)', implode(', ', $developers)),
			sprintf(' module.oid IN (%s)', implode(', ', $moos)),
		];

		$where = implode(' AND ', $whereConditions);

		$query = <<<EOF
		SELECT 
		       module.module, 
		       SUM(IF(ticket.assignee = module.oid && ticket.status != 'CLOSED', 1, 0)) as '$assignedToMOO', 
		       SUM(IF(ticket.assignee = ticket.assignor && ticket.assignee != module.uid && ticket.status != 'CLOSED', 1, 0)) as '$assignedToAssignor', 
		       SUM(IFNULL((
		           SELECT IF(COUNT(*) = 0, 0, 1) 
		           FROM people_groups pg 
		               INNER JOIN people p ON p.email = pg.email 
		           WHERE p.id = ticket.assignee AND pg.group_name='ROLE_DEV' AND ticket.status != 'CLOSED' GROUP BY p.id), 0)) as '$assignedToDev', 
		       SUM(IFNULL((
		           SELECT IF(COUNT(*) = 0, 0, 1) 
		           FROM people_groups pg 
		               INNER JOIN people p ON p.email = pg.email 
		           WHERE p.id = ticket.assignee AND pg.group_name='ROLE_DEV' AND ticket.status != 'CLOSED' AND ticket.cat='C' GROUP BY p.id), 0)) as '$cAssignedToDev', 
		       $sumsSelect
		FROM tasks ticket
			INNER JOIN com_modules module on ticket.ticket_module_id = module.id
		WHERE ticket.ticket_module_id IS NOT NULL AND $where
		GROUP BY module.module
EOF;

		$reportData = ['rows' => [], 'xTotals' => [], 'yTotals' => []];
		foreach (tldUtils::getSqlToAssocArray($query) as $moduleTickets) {
			$reportData['rows'][$moduleTickets['module']] = [];
			foreach ($statuses as $status) {
				$alias = str_replace(' ', '_', $status);
				$reportData['xTotals'][$moduleTickets['module']] = ($reportData['xTotals'][$moduleTickets['module']] ?? 0) + $moduleTickets[$alias];
				$reportData['yTotals'][$status] = ($reportData['yTotals'][$status] ?? 0) + $moduleTickets[$alias];
				$reportData['rows'][$moduleTickets['module']][$status] = ['value' => $moduleTickets[$alias], 'x' => $moduleTickets['module'], 'y' => $status];
			}
			$otherAggregates = array_merge(array_keys($ifs), ['B', 'C'], ['C ' . self::ASSIGNED_TO_DEV, self::ASSIGNED_TO_MOO, self::ASSIGNED_TO_ASSIGNOR, self::ASSIGNED_TO_DEV]);
			foreach ($otherAggregates as $column) {
				$reportData['rows'][$moduleTickets['module']][$column] = ['value' => $moduleTickets[$column], 'x' => $moduleTickets['module'], 'y' => $column];
				$reportData['yTotals'][$column] = ($reportData['yTotals'][$column] ?? 0) + $moduleTickets[$column];
			}
			$reportData['total'] = ($reportData['total'] ?? 0) + $reportData['xTotals'][$moduleTickets['module']];
		}

		return $reportData;
	}

	/**
	 * get tickets by domain, location, category
	 *
	 * Typically used to find who receives tasks based on the location and category
	 *
	 * @param string $domain name of domain to search for
	 * @param string $location name of location to search for
	 * @param string $category name of category to search for
	 * @return array
	 */
	public static function byQuery($options = '', $orderby = '')
	{
		if (is_array($options)) {
			$WHERE = ' WHERE ' . tldUtils::constructWhere($options);
		} elseif (is_string($options)) {
			switch ($options) {
				case 'project_list':
					$WHERE = " WHERE status NOT IN ('CLOSED','QUEUE')";
					break;
			}
		} else {
			$WHERE = " WHERE status<>'CLOSED'";
		}
		if (is_array($orderby)) {
			$ORDERBY = implode(',', $orderby);
		} else {
			$ORDERBY = ' domain, ifactor DESC, eta';
		}

		$query = <<<EOF
		SELECT mis_tts.*,
			(SELECT CONCAT(people.lastname,', ',people.firstname)
				FROM people
				WHERE mis_tts.owner=people.id
				) AS owner_fullname,
			(SELECT CONCAT(people.lastname,', ',people.firstname)
				FROM people
				WHERE mis_tts.assignee=people.id
			) AS assignee_fullname,
			locations.location,
			(SELECT COUNT(*) FROM tasks AS T3
				WHERE T3.module='TTS' AND T3.parent_id=mis_tts.id
				AND status<>'CLOSED'
			) AS numOpenTasks,
			(SELECT ROUND(AVG(DATEDIFF(T4.dt_closed, T4.date)), 2)
				FROM tasks AS T4
				WHERE T4.module='TTS' AND T4.parent_id=mis_tts.id AND T4.status='CLOSED'
			) AS avgDays
		FROM mis_tts LEFT JOIN locations ON mis_tts.location=locations.id
		 $WHERE
		ORDER BY $ORDERBY
EOF;

		return tldUtils::getSqlToAssocArray($query);
	}

	public function byQueue($domain, $category = '')
	{
		$query = <<<EOF
		SELECT mis_tts.*,
			CONCAT(people.firstname,' ',people.lastname) AS owner_fullname
		FROM mis_tts LEFT JOIN people ON mis_tts.owner=people.id
		 WHERE status<>'CLOSED'
			AND domain='$domain' AND category='$category'
EOF;
		return tldUtils::getSqlToAssocArray($query);
	}

	public function byAssignee($id)
	{
		$query = <<<EOF
		SELECT mis_tts.*,
			CONCAT(people.firstname,' ',people.lastname) AS owner_fullname
		FROM mis_tts LEFT JOIN people ON mis_tts.owner=people.id
		 WHERE status<>'CLOSED'
			AND assignee=$id
		ORDER BY ifactor DESC, status ASC, owner ASC, id ASC
EOF;
		return tldUtils::getSqlToAssocArray($query);
	}

	public function byLatest($num = 30)
	{
		if ($num == 0) {
			return;
		}
		$query = <<<EOF
		SELECT mis_tts.*,
			(SELECT CONCAT(people.lastname,', ',people.firstname)
			FROM people
			WHERE mis_tts.owner=people.id
			) AS owner_fullname,
			(SELECT CONCAT(people.lastname,', ',people.firstname)
			FROM people
			WHERE mis_tts.assignee=people.id
			) AS assignee_fullname
		FROM mis_tts
		WHERE status<>'CLOSED' AND status<>'QUEUE'
		ORDER BY id DESC
		LIMIT $num
EOF;
		return tldUtils::getSqlToAssocArray($query);
	}

	public function byLatestComments($num = 30)
	{
		if ($num == 0) {
			return;
		}
		$query = <<<EOF
		select t1.*, t2.*,
			(SELECT CONCAT(people.lastname,', ',people.firstname)
			FROM people
			WHERE mis_tts.owner=people.id
			) AS owner_fullname,
			(SELECT CONCAT(people.lastname,', ',people.firstname)
			FROM people
			WHERE mis_tts.assignee=people.id
			) AS assignee_fullname
		from tasks as t1, tasks_comments as t2
		where t1.id=t2.parent_id
		 AND t1.module='TTS'
		 and t1.status='OPEN'
		order by t2.date desc limit $num
EOF;
		return tldUtils::getSqlToAssocArray($query);
	}

	public function daysTakenByOwner()
	{
		$query = <<<EOF
		SELECT IF(date_closed='0000-00-00',
				sum(TO_DAYS(NOW())-TO_DAYS(dt_opened)),
				sum(TO_DAYS(date_closed)-TO_DAYS(dt_opened))) AS daysTaken,
			email
		FROM mis_tts LEFT JOIN people ON mis_tts.owner=people.id
		WHERE status='CLOSED'
		GROUP BY owner
		ORDER BY email desc
EOF;
		return tldUtils::getSqlToAssocArray($query);
	}

	public function countByOwner()
	{
		$query = <<<EOF
		SELECT COUNT(*), people.email
		FROM mis_tts LEFT JOIN people ON mis_tts.owner=people.id
		GROUP BY owner
		ORDER BY email desc
EOF;
		return tldUtils::getSqlToAssocArray($query);
	}

	public static function countTasksByDomainCategory()
	{
		$query = <<<EOF
		SELECT domain,category, count(*) AS num
		FROM tasks,mis_tts
		WHERE module='TTS' AND tasks.parent_id=mis_tts.id
		AND tasks.status<>'CLOSED'
		GROUP BY domain,category
EOF;
		return tldUtils::getSqlToAssocArray($query);
	}

	public static function tasksByDomainCategory($domain, $category)
	{
		if ($domain !== 'ALL') {
			$where[] = " domain='$domain'";
		}
		if ($category !== 'ALL') {
			$where[] = " category='$category'";
		}
		if (count($where ?? []) > 0) {
			$WHERE = ' AND ' . implode(' AND ', $where);
		}

		$query = <<<EOF
		SELECT tasks.*,
			CONCAT(assignors.lastname,', ',assignors.firstname) as assignor_fullname,
			(SELECT division FROM tld_regions
                WHERE assignors.div_id=tld_regions.id
			) as assignor_division,
			concat(assignees.lastname,', ',assignees.firstname) as assignee_fullname,
			if(due_date<NOW(), 1, 0) AS overdue,
			if(due_date<NOW() && tasks.status<>'CLOSED',
				CONCAT('<img src="/shared/bluesphere/32x32/actions/kill.png" alt="This task is late."><br>',TO_DAYS(now())-TO_DAYS(due_date),' days'),
				null
			) AS overdue_icon
		FROM
			tasks LEFT JOIN people AS assignees ON tasks.assignee=assignees.id
			LEFT JOIN people AS assignors ON tasks.assignor=assignors.id
		,mis_tts
		WHERE module='TTS' AND tasks.parent_id=mis_tts.id
		AND tasks.status<>'CLOSED'
		$WHERE
EOF;
		return tldUtils::getSqlToAssocArray($query);
	}

    public static function numberOpenAndClosedTasksForAPeriod(array $filter)
    {
        $constraints = '';

        if (!empty($filter['domain'])) {
            $constraints = sprintf("AND mis_tts.domain = '%s'", $filter['domain']);
        }

        $query = <<<EOF
            SELECT
                mis_tts.category as category,
                SUM(IF(tasks.date BETWEEN '{$filter['from']}' AND '{$filter['to']}', 1, 0)) AS nb_opened,
                SUM(IF(tasks.dt_closed BETWEEN '{$filter['from']}' AND '{$filter['to']}', 1, 0)) AS nb_closed
            FROM tasks
                     LEFT JOIN mis_tts ON tasks.parent_id=mis_tts.id
            WHERE tasks.module='TTS'
            AND mis_tts.category<>'MIS'
            AND (tasks.date BETWEEN '{$filter['from']}' AND '{$filter['to']}' OR tasks.dt_closed BETWEEN '{$filter['from']}' AND '{$filter['to']}')
            {$constraints}
            GROUP BY mis_tts.category
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function numberOpenTasksSeniority(array $filter)
    {
        $constraints = '';

        if (!empty($filter['domain'])) {
            $constraints = sprintf("AND mis_tts.domain = '%s'", $filter['domain']);
        }

        $query = <<<EOF
            SELECT
                mis_tts.category as category,
                SUM(IF(DATEDIFF(DATE('{$filter['to']}'), tasks.date) < 30, 1, 0)) AS low,
                SUM(IF(DATEDIFF(DATE('{$filter['to']}'), tasks.date) BETWEEN 30 AND 90, 1, 0)) AS medium,
                SUM(IF(DATEDIFF(DATE('{$filter['to']}'), tasks.date) > 90, 1, 0)) AS high,
                SUM(1) AS total
            FROM tasks
            LEFT JOIN mis_tts ON tasks.parent_id=mis_tts.id
            WHERE tasks.module='TTS'
            AND mis_tts.category<>'MIS'
            AND (tasks.dt_closed > '{$filter['to']}' || tasks.dt_closed = '0000-00-00')
            AND tasks.date < '{$filter['to']}'
            {$constraints}
            GROUP BY mis_tts.category
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

	/**
	 * Calculate tasks statistics for MIS QUEUE by year by category per constraints
	 * @param int $year
	 * @param array $a
	 * @param array $opt
	 * @return array
	 */
	public static function getTaskStatsDomainByYearByConstraints($year, $a = '', $opt = '')
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
	mis_tts.category,
	'$year' AS year,
	SUM(
		IF(YEAR(tasks.date)=$year, 1, 0)
	) AS nb_opened,
	SUM(
		IF(YEAR(tasks.dt_closed)=$year, 1, 0)
	) AS nb_closed
FROM
	tasks LEFT JOIN mis_tts ON tasks.parent_id=mis_tts.id
WHERE
	tasks.module='TTS'
	AND mis_tts.category<>'MIS'
    $WHERE
GROUP BY
	mis_tts.category
EOF;
		return tldUtils::getSqlToAssocArray($query);
	}

	public static function countTasksByUserCategoryByConstraints($a = null)
	{
		$WHERE = is_array($a) ? tldUtils::constructWhere($a) : $a;

		if (!empty($WHERE)) {
			$WHERE = "AND $WHERE";
		}
		$query = <<<EOF
SELECT
    COUNT(*) AS num,
    (SELECT CONCAT(firstname,' ',lastname) FROM people
        WHERE id=tasks.assignee
    ) AS user_fullname,
    CASE
        WHEN tasks.status LIKE 'IN PROGRESS' THEN (
            SELECT CASE
                WHEN (tasks.cat='C' AND TIMESTAMPDIFF(DAY,tasks.date,NOW())<=30) THEN CONCAT(tasks.status,' C&lt;=30')
                WHEN (tasks.cat='C' AND TIMESTAMPDIFF(DAY,tasks.date,NOW())<=60) THEN CONCAT(tasks.status,' 30&lt;C&lt;=60')
                WHEN (tasks.cat='C' AND TIMESTAMPDIFF(DAY,tasks.date,NOW())>60) THEN CONCAT(tasks.status,' C&gt;60')
                WHEN tasks.cat<>'' THEN CONCAT(tasks.status,' ',tasks.cat)
                ELSE tasks.status
            END
        )
        WHEN tasks.status LIKE 'OPEN' THEN (
            SELECT CASE
                WHEN TIMESTAMPDIFF(HOUR,tasks.date,NOW())>48 THEN CONCAT(tasks.status,' &gt;48h')
                ELSE tasks.status
            END
        )
        WHEN tasks.status LIKE 'PAUSE' THEN 'PAUSE'
    END AS iStatusCategoryTime
FROM
    tasks
WHERE
    1=1
$WHERE
GROUP BY
    user_fullname, iStatusCategoryTime
EOF;

		return tldUtils::getSqlToAssocArray($query);
	}

	public static function countTasksByUserCategoryByIF($a = null)
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
    COUNT(*) AS num,
    (SELECT CONCAT(firstname,' ',lastname) FROM people
        WHERE id=tasks.assignee
    ) AS user_fullname,
    CASE 
      WHEN tasks.ifactor =1 THEN 'IF=1'
      WHEN tasks.ifactor =10 THEN 'IF=10'
      WHEN tasks.ifactor =100 THEN 'IF=100'
      WHEN tasks.ifactor =1000 THEN 'IF=1000'
    END 
    AS ifactor
FROM
    tasks
WHERE
    tasks.ifactor > 1
$WHERE
GROUP BY
    user_fullname, ifactor
EOF;

		return tldUtils::getSqlToAssocArray($query);
	}

	public static function tasksByConstraints($a, $opt = null)
	{
		if (is_array($a)) {
			$HAVING = tldUtils::constructWhere($a);
		} else {
			$HAVING = $a;
		}
		// Look for Options
		$ORDERBY = 'tasks.id';
		if (!empty($opt['orderBy'])) {
			$ORDERBY = $opt['orderBy'];
		}
		$query = <<<EOF
SELECT
    tasks.*,
    CONCAT(assignee.firstname,' ',assignee.lastname) AS assignee_fullname,
    CONCAT(assignor.firstname,' ',assignor.lastname) AS assignor_fullname,
    IF(tasks.due_date<NOW(), 1, 0) AS overdue,
    IF(tasks.due_date<NOW(),
        ROUND(DATEDIFF(NOW(),tasks.due_date)/7),
        0
    ) AS wks_overdue,
    IF(tasks.due_date<NOW() && tasks.status<>'CLOSED',
        CONCAT('<img src="/shared/bluesphere/32x32/actions/kill.png"><br>',TO_DAYS(NOW())-TO_DAYS(tasks.due_date),' days'),
        NULL
    ) AS overdue_icon,
    DATEDIFF(NOW(), tasks.date) AS nb_days,
    CASE
        WHEN tasks.status LIKE 'IN PROGRESS' THEN (
            SELECT CASE
                WHEN (tasks.cat='C' AND TIMESTAMPDIFF(DAY,tasks.date,NOW())<=30) THEN CONCAT(tasks.status,' C&lt;=30')
                WHEN (tasks.cat='C' AND TIMESTAMPDIFF(DAY,tasks.date,NOW())<=60) THEN CONCAT(tasks.status,' 30&lt;C&lt;=60')
                WHEN (tasks.cat='C' AND TIMESTAMPDIFF(DAY,tasks.date,NOW())>60) THEN CONCAT(tasks.status,' C&gt;60')
                WHEN tasks.cat<>'' THEN CONCAT(tasks.status,' ',tasks.cat)
                ELSE tasks.status
            END
        )
        WHEN tasks.status LIKE 'OPEN' THEN (
            SELECT CASE
                WHEN TIMESTAMPDIFF(HOUR,tasks.date,NOW())>48 THEN CONCAT(tasks.status,' &gt;48h')
                ELSE tasks.status
            END
        )
        WHEN tasks.status LIKE 'PAUSE' THEN 'PAUSE'
    END AS iStatusCategoryTime,
    mis_tts.category AS queue_category,
    mis_tts.domain AS domain,
    (SELECT comment FROM tasks_comments
       WHERE parent_id=tasks.id ORDER BY date DESC LIMIT 1
    ) AS lastcomment
FROM
    tasks
    LEFT JOIN mis_tts ON mis_tts.id=tasks.parent_id AND tasks.module LIKE 'TTS'
    LEFT JOIN people AS assignor ON assignor.id=tasks.assignor
    LEFT JOIN people AS assignee ON assignee.id=tasks.assignee
HAVING
    $HAVING
ORDER BY
    $ORDERBY
EOF;

		return tldUtils::getSqlToAssocArray($query);
	}

	public static function tasksByIF($a, $opt = null)
	{
		if (is_array($a)) {
			$HAVING = tldUtils::constructWhere($a);
		} else {
			$HAVING = $a;
		}
		// Look for Options
		$ORDERBY = 'tasks.id';
		if (!empty($opt['orderBy'])) {
			$ORDERBY = $opt['orderBy'];
		}
		$query = <<<EOF
SELECT
    tasks.*,
    CONCAT(assignee.firstname,' ',assignee.lastname) AS assignee_fullname,
    CONCAT(assignor.firstname,' ',assignor.lastname) AS assignor_fullname,
    IF(tasks.due_date<NOW(), 1, 0) AS overdue,
    IF(tasks.due_date<NOW(),
        ROUND(DATEDIFF(NOW(),tasks.due_date)/7),
        0
    ) AS wks_overdue,
    IF(tasks.due_date<NOW() && tasks.status<>'CLOSED',
        CONCAT('<img src="/shared/bluesphere/32x32/actions/kill.png"><br>',TO_DAYS(NOW())-TO_DAYS(tasks.due_date),' days'),
        NULL
    ) AS overdue_icon,
    CASE 
      WHEN tasks.ifactor =1 THEN 'IF=1'
      WHEN tasks.ifactor =10 THEN 'IF=10'
      WHEN tasks.ifactor =100 THEN 'IF=100'
      WHEN tasks.ifactor =1000 THEN 'IF=1000'
    END 
    AS ifactor,
    mis_tts.category AS queue_category,
    (SELECT comment FROM tasks_comments
       WHERE parent_id=tasks.id ORDER BY date DESC LIMIT 1
    ) AS lastcomment
FROM
    tasks
    LEFT JOIN mis_tts ON mis_tts.id=tasks.parent_id AND tasks.module LIKE 'TTS'
    LEFT JOIN people AS assignor ON assignor.id=tasks.assignor
    LEFT JOIN people AS assignee ON assignee.id=tasks.assignee
HAVING
    $HAVING
ORDER BY
    $ORDERBY
EOF;

		return tldUtils::getSqlToAssocArray($query);
	}
}

/**
 * Class for manipulating MIS Inventory entries
 *
 * @package MIS
 */
class tldMISInv
{
	/**
	 *
	 */
	public $itsID;
	public $theMAX_TREE_LEVEL = 30;

	public function __construct($id)
	{
		$this->itsID = $id;
		$this->theMAX_TREE_LEVEL = 20;
	}

	public function isEmpty()
	{
		return count($this->getHeader()) ? false : true;
	}

	public function asArray()
	{
		$result = $this->getHeader();
		$result['children'] = $this->getChildren();
		return $result;
	}

	public function getID()
	{
		return $this->itsID;
	}

	public function getHeader()
	{
		$query = <<<EOF
		SELECT * FROM mis_inv
		WHERE id=$this->itsID
EOF;
		return tldUtils::getSqlRowToAssocArray($query);
	}

	public function getChildren()
	{
		return $this->getChildRows($this->itsID);
	}

	public function getChildRows($id)
	{
		$query = <<<EOF
		SELECT * FROM mis_inv
		WHERE parent_id=$id
		ORDER BY type, serial
EOF;
		return tldUtils::getSqlToAssocArray($query);
	}

	public function getFiles()
	{
		return tldModFile::byParent($this->itsID, 'INV');
	}

	/**
	 * Get tree as array
	 *
	 * @param integer $max_level maximum number of levels to go down
	 * @param array $options optional parameters
	 * @return array array of db rows
	 */
	public function getTree()
	{
		$tree = [];
		$this->getTreeRec($tree,
			0);
		return $tree;
	}

	public function getTreeRec(&$tree, $level = 0)
	{
		if ($this->theMAX_TREE_LEVEL && $level > $this->theMAX_TREE_LEVEL) {
			return;
		}
		$level++;
		foreach ($this->getChildren() as $row) {
			$child = new tldMISInv($row['id']);
			$row['level'] = $level;
			$tree[] = $row;
			$child->getTreeRec($tree, $level);
		}
	}

	/**
	 * gets list of all items in tree but unordered
	 */
	public function getList()
	{
		$list = [];
		foreach ($this->getChildren() as $row) {
			$pids[] = $row['id'];
		}
		$this->getListRec($list, $pids);
		return $list;
	}

	public function getListRec(&$list, $ids, $level = 0)
	{
		if (($this->theMAX_TREE_LEVEL && $level > $this->theMAX_TREE_LEVEL)
			|| count($ids ?? []) == 0) {
			return;
		}
		$level++;
		$parentIDS = implode(',', $ids);
		$query = <<<EOF
		SELECT T1.*,'$level' AS level, T2.serial AS parent_serial, REPLACE(T1.description, CHAR(13)+CHAR(10),'<br>') AS description
		FROM mis_inv AS T1 LEFT JOIN mis_inv AS T2 ON T1.parent_id=T2.id
		WHERE T1.parent_id IN ($parentIDS)
		ORDER BY T1.type, T1.serial
EOF;
		$rows = tldUtils::getSqlToAssocArray($query);
		if (count($rows) == 0) {
			return;
		}
		foreach ($rows as $row) {
			$pids[] = $row['id'];
			$list[] = $row;
		}
		$this->getListRec($list, $pids, $level);
	}

	//static functions
	public static function insert($p)
	{
		$fields = ['parent_id', 'qty', 'description',
			'make', 'model', 'serial', 'man_serial', 'type'];
		$query = <<<EOF
        INSERT INTO mis_inv
        SET date=NOW(),d_wrty=DATE_ADD(NOW(), INTERVAL 1 YEAR),
EOF;
		$query .= tldUtils::getSqlSet($p, $fields);

		return tldUtils::sqlInsert($query);
	}

	public static function byQuery($m)
	{
		if (is_array($m)) {
			$WHERE = ' WHERE ' . tldUtils::constructWhere($m);
		}
		$query = <<<EOF
		SELECT *
		FROM mis_inv
		$WHERE
EOF;
		return tldUtils::getSqlToAssocArray($query);
	}

	public static function getMakes()
	{
		$query = <<<EOF
		SELECT DISTINCT make
		FROM mis_inv
		ORDER BY make
EOF;
		return tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['make', 'make']);
	}

	public static function getStatsByType()
	{
		$query = <<<EOF
		SELECT type,COUNT(*) AS num
		FROM mis_inv
		GROUP BY type
EOF;
		return tldUtils::getSqlToAssocArray($query);
	}

	/**
	 * Get pdf of labels
	 *
	 * @param array $rows if rows is not set then current INV id is used
	 */
	public function outPDFLabel($rows = '')
	{
		if (empty($rows)) {
			$rows[0] = $this->getHeader();
		}
		$pdf = new tldFPDF('P', 'mm', [50.8, 19]);

		$pdf->Open();
		$pdf->SetMargins(0, 0);
		//$pdf->SetLineWidth(48);
		$pdf->SetFont('Arial', '', 7);
		$pdf->SetAutoPageBreak(true, 1);
		$pdf->SetDisplayMode('fullpage', 'single');

		foreach ($rows as $row) {
			//do one label
			$pdf->AddPage();
			$pdf->SetXY(0, 1);
			$pdf->MultiCell(50.8, 3, 'Property of TLD, ' . $row['type'] . "\n" . $row['man_serial'], 0, 'C');
			$pdf->EAN13(2, 7, $row['id'], 8, 0.5);
			$pdf->SetXY(0, 15);
			$pdf->Cell(50.8, 3, $row['id'], 0, 0, 'C');
		}
		$pdf->Output();
	}
}


class Tld_Mis_Inventory_Module
{
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
SELECT DISTINCT t1.id, t1.*, t2.name, t2.category,t4.destination_type, t3.name as brand_name, t4.destination_id as assignee,
CASE t1.hidden
    WHEN 0 THEN 'NO'
    WHEN 1 THEN 'YES'
    WHEN 2 THEN 'IN PROGRESS'
END AS hidden_status,
CASE t1.notify
    WHEN 0 THEN 'NO'
    WHEN 1 THEN 'YES'
END AS notify,
CASE t4.destination_type
    WHEN 'PEOPLE' THEN (SELECT CONCAT(people.firstname,' ',people.lastname) FROM people WHERE people.id = t4.destination_id)
	WHEN 'LOCATION' THEN (SELECT mis_inventory_buildings.name FROM mis_inventory_buildings WHERE mis_inventory_buildings.id = t4.destination_id)
	ELSE NULL
END AS destination,
(SELECT loc.business_unit FROM locations AS loc WHERE t1.buyer_bu_id=loc.id) as buyer_location,
(SELECT department.dpt	FROM tld_departments AS department WHERE t1.buyer_dpt_id=department.id) as buyer_dpt
FROM mis_inventory_items as t1 
LEFT JOIN mis_inventory_item_types as t2 ON t1.type_id=t2.id 
LEFT JOIN mis_inventory_brands as t3 ON t1.brand_id=t3.id 
LEFT JOIN mis_inventory_assignments as t4 ON t1.id=t4.item_id 
LEFT JOIN mis_inventory_buildings as t5 ON t5.id= t4.destination_id 
WHERE $WHERE
EOF;

		return tldUtils::getSqlToAssocArray($query);
	}

	public static function search($target)
	{
		if (empty($target)) {
			return;
		}
		$SELECT = tldISR::getSELECT();
		$FROM = tldISR::getFROM();
		$query = <<<EOF
SELECT DISTINCT t1.id, t1.*, t2.name, t2.category,t4.destination_type, t3.name as brand_name, t4.destination_id as assignee,
CASE t1.hidden
    WHEN 0 THEN 'NO'
    WHEN 1 THEN 'YES'
    WHEN 2 THEN 'IN PROGRESS'
END AS hidden_status,
CASE t4.destination_type
    WHEN 'PEOPLE' THEN (SELECT CONCAT(people.firstname,' ',people.lastname) FROM people WHERE people.id = t4.destination_id)
	WHEN 'LOCATION' THEN (SELECT mis_inventory_buildings.name FROM mis_inventory_buildings WHERE mis_inventory_buildings.id = t4.destination_id)
	ELSE NULL
END AS location,
(SELECT loc.business_unit FROM locations AS loc WHERE t1.buyer_bu_id=loc.id) as buyer_location,
(SELECT department.dpt	FROM tld_departments AS department WHERE t1.buyer_dpt_id=department.id) as buyer_dpt
FROM mis_inventory_items as t1 
LEFT JOIN mis_inventory_item_types as t2 ON t1.type_id=t2.id 
LEFT JOIN mis_inventory_brands as t3 ON t1.brand_id=t3.id 
LEFT JOIN mis_inventory_assignments as t4 ON t1.id=t4.item_id 
LEFT JOIN mis_inventory_buildings as t5 ON t5.id= t4.destination_id 
HAVING
    description LIKE '%$target%'
    OR location LIKE '%$target%'
    OR buyer_location LIKE '%$target%'
    OR buyer_dpt LIKE '%$target%'
    OR name LIKE '%$target%'
    OR tld_sn LIKE '%$target%'
    OR manufacturer_sn LIKE '%$target%'
    OR fixasset_id LIKE '%$target%'
ORDER BY t1.id
EOF;
		return tldUtils::getSqlToAssocArray($query);
	}

	public function getModuleLink()
	{
		global $phpself;
		return "$php_self/en/private/mis/mis.php?m[0]=inventory";
	}

	public static function getItemView(Tld_Mis_Inventory_Item $item)
	{
		$data = $item->header;
		$data['task_id'] = '<a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=' . $data['task_id'] . '">' . $data['task_id'] . '</a>';
		$item = [
			'id' => 'Item#',
			'dt' => 'Date',
			'type_name' => 'Type',
			'brand_name' => 'Brand',
			'model' => 'Model',
			'manufacturer_sn' => 'Manufacturer SN',
			'tld_sn' => 'TLD SN',
			'fixasset_id' => 'Fix Asset ID',
			'state' => 'State',
			'description' => 'Description',
			'dt_warranty_end' => 'End of warranty',
			'buyer_location' => 'Buyer BU',
			'buyer_dpt' => 'Buyer Department',
			'destination_type' => 'Destination Type',
			'destination' => 'Destination',
			'dt_from' => 'Date From',
			'dt_to' => 'Date To',
			'comment' => 'Comment',
			'task_id' => 'Sequence#',
			'hidden_status' => 'Disposed?',
			'notify_status' => 'Warranty Notification?',
		];

		if ($data['type_category'] === 'SOFTWARE') {
			$item = [
				'id' => 'Item#',
				'dt' => 'Date',
				'type_name' => 'Type',
				'brand_name' => 'Brand',
				'manufacturer_sn' => 'Installation Key',
				'tld_sn' => 'TLD SN',
				'fixasset_id' => 'Fix Asset ID',
				'qty' => 'Quantity',
				'state' => 'State',
				'description' => 'Description',
				'dt_warranty_end' => 'End of warranty',
				'buyer_location' => 'Buyer BU',
				'buyer_dpt' => 'Buyer Department',
				'destination_type' => 'Destination Type',
				'destination' => 'Destination',
				'dt_from' => 'Date From',
				'dt_to' => 'Date To',
				'comment' => 'Comment',
				'task_id' => 'Sequence#',
				'hidden_status' => 'Disposed?',
				'notify' => 'Warranty Notification?',
			];
		}
		if ($data['type_category'] === 'ISP') {
			$item = [
				'id' => 'Item#',
				'dt' => 'Date',
				'type_name' => 'Type',
				'brand_name' => 'Brand',
				'bandwidth' => 'Bandwidth',
				'cost' => 'Cost',
				'ipaddress' => 'IP Address',
				'contract' => 'Contract No',
				'hotline' => 'Service Hotline',
				'salecontact' => 'Sales Contact',
				'description' => 'Description',
				'dt_warranty_end' => 'End of warranty',
				'buyer_location' => 'Buyer BU',
				'buyer_dpt' => 'Buyer Department',
				'destination_type' => 'Destination Type',
				'destination' => 'Destination',
				'dt_from' => 'Date From',
				'dt_to' => 'Date To',
				'comment' => 'Comment',
				'task_id' => 'Sequence#',
				'hidden_status' => 'Disposed?',
				'notify' => 'Warranty Notification?',
			];
		}
		$report = new tldAssocTable(
			$data,
			$item,
			['title' => 'Item details',

			]
		);

		return $report->fetch();
	}

	public function getItemListingView($data, $options = [])
	{
		// Manage options
		$reportOptions = [
			'xItems' => [
				'id' => 'Item#',
				'dt' => 'Date',
				'type_name' => 'Type',
				'brand_name' => 'Brand',
				'model' => 'Model',
				'manufacturer_sn' => 'Manufacturer SN',
				'tld_sn' => 'TLD SN',
				'fixasset_id' => 'Fix Asset ID',
				'state' => 'State',
				'description' => 'Description',
				'dt_warranty_end' => 'End of warranty',
				'buyer_location' => 'Buyer BU',
				'buyer_dpt' => 'Buyer Department',
				'hidden_status' => 'Disposed?',
				'notify' => 'Warranty Notification?',
				'assigned' => 'Assigned',
			],
			'title' => 'Item list',
			'links' => [
				'id' => self::getModuleLink() . '&m[1]=view&id=',
			],
		];
		if (!empty($options['xItems']) && is_array($options['xItems'])) {
			$reportOptions['xItems'] = $options['xItems'];
		}
		if (!empty($options['title'])) {
			$reportOptions['title'] = $options['title'];
		}
		// create report
		$report = new tldReportColumnar(
			$data,
			$reportOptions
		);
		return $report->fetch();
	}
}


class Tld_Mis_Inventory_Item
{

	public $id;
	public $header;

	public function __construct($id)
	{
		$this->id = $id;
		$this->header = $this->getHeader();
	}

	public function getID()
	{
		return $this->id;
	}

	public function isEmpty()
	{
		return empty($this->header);
	}

	public function getType()
	{
		return $this->header['type_name'];
	}

	public function outPDFLabel($rows = '')
	{
		if (empty($rows)) {
			$arr =[];
			$arr[0] = $this->getHeader();
			$rows = $arr;
		}
		$pdf = new tldFPDF('P', 'mm', [120.8, 40]);

		$pdf->Open();
		$pdf->SetMargins(0, 0);
		$pdf->SetFont('Arial', '', 10);
		$pdf->SetAutoPageBreak(true, 1);
		$pdf->SetDisplayMode('fullpage', 'single');

		foreach ($rows as $row) {
            $buyer_location = $row['buyer_location'] ?? 'TLD';
			//do one label
			$pdf->AddPage();
			$pdf->SetXY(10, 3);
            $pdf->MultiCell(90.8, 4, "           Property of $buyer_location \n         SN:" . $row['tld_sn'], 0, 'C');
            $pdf->Code128(35, 16, $row['tld_sn'], 50, 10);
			$pdf->Image("$GLOBALS[SHARED_PATH]/icons/tld-icon.jpg",
				4, 1, 8, 5);
			$pdf->SetXY(0, 22);
			if (!empty($row['fixasset_id'])) {
				$pdf->Cell(50.8, 10, "                            Fix-assets# \n " . $row['fixasset_id'], 0, 0, 'C');
				$pdf->Cell(-25, 17, "Date: ".date("Y-m-d"), 0, 0, 'C');
			}

		}
		$pdf->Output();
	}

	public function isAssigned()
	{
		$query = <<<EOF
SELECT t1.*
FROM mis_inventory_assignments AS t1
WHERE t1.item_id=$this->id
EOF;
		$rows = tldUtils::getSqlToAssocArray($query);
		if (count($rows) > 0) {
			return true;
		}

	}

	public static function getSELECT()
	{
		return <<<EOF
SELECT
    DISTINCT(item.id), item.*,
    UPPER(itemType.name) AS type_name,
    UPPER(itemType.category) AS type_category,
    UPPER(itemBrand.name) AS brand_name,
    buyer_bu.location AS buyer_location,
    buyer_bu.region AS region,
    buyer_dpt.dpt AS buyer_dpt,
	assignment.destination_type,
	IF(assignment.destination_type IS NULL, 'N', 'Y') AS assigned,
	(CASE WHEN assignment.destination_type = 'PEOPLE' THEN (SELECT CONCAT(people.firstname,' ',people.lastname) AS poster_fullname FROM people WHERE people.id=assignment.destination_id)
		  WHEN assignment.destination_type = 'LOCATION' THEN (SELECT mis_inventory_buildings.name AS location FROM mis_inventory_buildings WHERE mis_inventory_buildings.id=assignment.destination_id)
	ELSE null				
	END) AS destination,
	assignment.dt_from,
	assignment.dt_to,
	assignment.comment,
	assignment.task_id,
	assignment.destination_id,
	CASE item.hidden
		WHEN 0 THEN 'NO'
		WHEN 1 THEN 'YES'
		WHEN 2 THEN 'IN PROGRESS'
	END AS hidden_status,
	CASE item.notify
		WHEN 0 THEN 'NO'
		WHEN 1 THEN 'YES'
	END AS notify_status
EOF;
	}

	public static function getFROM()
	{
		return <<<EOF
FROM
    mis_inventory_items AS item
    LEFT JOIN mis_inventory_item_types AS itemType ON itemType.id=item.type_id
    LEFT JOIN mis_inventory_brands AS itemBrand ON itemBrand.id=brand_id
    LEFT JOIN locations AS buyer_bu ON buyer_bu.id=item.buyer_bu_id
    LEFT JOIN tld_departments AS buyer_dpt ON buyer_dpt.id=item.buyer_dpt_id
    LEFT JOIN mis_inventory_assignments AS assignment ON assignment.item_id = item.id
EOF;
	}


	public function getHeader()
	{
		$SELECT = self::getSELECT();
		$FROM = self::getFROM();
		$query = <<<EOF
$SELECT
$FROM
WHERE item.id=$this->id
EOF;

		return tldUtils::getSqlRowToAssocArray($query);
	}

	public function refresh()
	{
		$this->header = $this->getHeader();
	}

	public static function byConstraints($a, $opt = [])
	{
		if (empty($a)) {
			return 'empty parameter';
		}
		// Look for constraints
		if (is_array($a)) {
//			$HAVING = 'HAVING ' . tldUtils::constructWhere($a);
            $HAVING = implode('AND ', $a);
		} else {
			$HAVING = "HAVING $a";
		}
		// Look for options
		if (isset($opt['LIMIT']) && !empty($opt['LIMIT'])) {
			$LIMIT = "LIMIT {$opt['LIMIT']}";
		}
		if (!empty($opt['orderBy'])) {
			$ORDERBY = 'ORDER BY ' . $opt['orderBy'];
		} else {
			$ORDERBY = 'ORDER BY item.id DESC';
		}
		// Construct query
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

	public static function updateStatus($a, $id)
	{
		if (empty($a)) {
			return;
		}
		$SET = tldUtils::getSqlSet($a);
		$query = "UPDATE mis_inventory_items SET $SET WHERE id=$id";

		return tldUtils::sqlQuery($query);
	}

	static function checkExpired()
	{
		error_log('BEGIN - Tld_Mis_Inventory_Item::checkExpired()');
		// Get list of DMS that should be expired
		$a = <<<EOF
(item.state LIKE 'GOOD' OR item.state LIKE 'UNDER CONTRACT') AND item.dt_warranty_end < NOW()
EOF;
		$itemList = self::byConstraints($a);
		// Any results?
		if (count($itemList) > 0) {
			// Set Expired
			foreach ($itemList as $item) {
				$items = new Tld_Mis_Inventory_Item($item['id']);
				if ($item['type_category'] === 'HARDWARE') {
					$e = self::updateStatus(['state' => 'WARRANTY EXPIRED'], $item['id']);
				} else {
					$e = self::updateStatus(['state' => 'EXPIRED'], $item['id']);
				}
				if (is_string($e)) {
					error_log('Item#' . $items->getID() . " status error: $e");
				} else {
					error_log('Item#' . $items->getID() . ' status set to EXPIRED');
				}
			}
		} else {
			error_log('No records found');
		}
		error_log('END - Tld_Mis_Inventory_Item::checkExpired()');
		return count($itemList);
	}

	public function assign($a)
	{
		$query = <<<EOF
		INSERT INTO mis_inventory_assignments
		SET item_id=${a['item_id']},
			poster_id='${a['poster_id']}',
			dt=NOW(),
			dt_from='${a['dt_from']}',
			dt_to='${a['dt_to']}',
			destination_type='${a['destination_type']}',
			destination_id='${a['destination_id']}',
			comment='${a['comment']}',
			task_id='${a['task_id']}'
EOF;
		return tldUtils::sqlInsert($query);
	}

	public function reassign($a, $fields = '')
	{
		if (empty($a)) {
			return;
		}
		$SET = tldUtils::getSqlSet($a, $fields);
		$query = "UPDATE mis_inventory_assignments SET $SET WHERE item_id=$this->id";

		return tldUtils::sqlQuery($query);
	}

	public static function insert($a)
	{
		if (empty($a)) {
			return;
		}
		$fields = [
			'dt_warranty_end', 'type_id', 'description', 'brand_id', 'model',
			'manufacturer_sn', 'tld_sn', 'fixasset_id', 'state', 'buyer_bu_id', 'buyer_dpt_id', 'qty', 'notify', 'cost', 'bandwidth', 'salecontact', 'contract', 'ipaddress', 'hotline',
		];
		$SET = tldUtils::getSqlSet($a, $fields);
		$now = date('Y-m-d');
		$query = "INSERT INTO mis_inventory_items SET hidden=0,dt='$now',$SET";

		return tldUtils::sqlInsert($query);
	}

	public static function unassign($id)
	{
		if (empty($id)) {
			return 'Not in object context';
		}
		$query = "DELETE FROM mis_inventory_assignments WHERE item_id=$id LIMIT 1";

		return tldUtils::sqlQuery($query);
	}

	public function update($a, $fields = '')
	{
		if (empty($a)) {
			return;
		}
		$SET = tldUtils::getSqlSet($a, $fields);
		$query = "UPDATE mis_inventory_items SET $SET WHERE id=$this->id";

		return tldUtils::sqlQuery($query);
	}

	public function delete()
	{
		$query = "DELETE FROM mis_inventory_items WHERE id=$this->id";
		return tldUtils::sqlExecute($query);
	}

	public static function getStateList($category = null)
	{
		if ($category === 'HARDWARE') {
			return [
				'GOOD' => 'GOOD',
				'USABLE' => 'USABLE',
				'TO BE REPAIRED' => 'TO BE REPAIRED',
			];
		}
		return [
			'UNDER CONTRACT' => 'UNDER CONTRACT',
			'EXPIRED' => 'EXPIRED',
			'UNLIMITED' => 'UNLIMITED',
		];
	}

	public function getLatest($num = 50)
	{
		return static::byConstraints('1=1', ['LIMIT' => $num]);
	}

	public static function getFields()
	{
		return [
			'id' => 'Item#',
			'dt' => 'Date',
			'type_name' => 'Type',
			'brand_name' => 'Brand',
			'model' => 'Model',
			'manufacturer_sn' => 'Manufacturer SN',
			'tld_sn' => 'TLD SN',
			'fixasset_id' => 'Fix Asset ID',
			'state' => 'State',
			'description' => 'Description',
			'dt_warranty_end' => 'End of warranty',
			'buyer_location' => 'Buyer BU',
			'buyer_dpt' => 'Buyer Department',
			'hidden_status' => 'Disposed?',
			'notify' => 'Warranty Notification?',
		];
	}

	/*********************************
	 * COMMON MODULES
	 *********************************/

	public function addLogEntry($uid, $comment, $num_log = 0)
	{
		return tldModLog::insert([
			'parent_id' => $this->id,
			'module' => 'MISINV_ITM',
			'poster' => $uid,
			'comment' => $comment,
			'log_num' => $num_log,
		]);
	}

	public function getLogs()
	{
		return tldModLog::byParent($this->id, 'MISINV_ITM');
	}

	public function getFiles($lvl = 0)
	{
		return tldModFile::byParent($this->id, 'MISINV_ITM', $lvl);
	}

	public function getLinksFromHere($type = '')
	{
		return tldModLink::byParent($this->id, 'MISINV_ITM', $type);
	}

	public function getLinkedTasks()
	{
		return self::getLinksFromHere('TASK');
	}

	public function addTaskLink($taskId)
	{
		return tldModLink::insert('MISINV_ITM', $this->id, 'TASK', $taskId);
	}

	public static function getTabletsByBU($erp)
	{
		$SELECT = self::getSELECT();
		$FROM = self::getFROM();
		$query = <<<EOF
$SELECT
$FROM
WHERE item.buyer_bu_id=$erp and itemType.name LIKE 'SHOPFLOOR TABLET'
EOF;

		return tldUtils::getSqlToAssocArray($query);
	}

	public static function getLicenseByBU($erp)
	{
		$SELECT = self::getSELECT();
		$FROM = self::getFROM();
		$query = <<<EOF
$SELECT
$FROM
WHERE item.buyer_bu_id=$erp and item.hidden =0 AND itemType.category LIKE 'SOFTWARE'
EOF;

		return tldUtils::getSqlToAssocArray($query);
	}

	public static function getAssignedActiveComputersByBU($erp)
	{
		$SELECT = self::getSELECT();
		$FROM = self::getFROM();
		$query = <<<EOF
$SELECT
$FROM
WHERE item.buyer_bu_id=$erp and itemType.name in ('SHOPFLOOR TABLET','LAPTOP','WORKSTATION','THINCLIENT','DESKTOP','TABLET') AND item.hidden =0 AND assignment.destination_id NOT LIKE ''
EOF;

		return tldUtils::getSqlToAssocArray($query);
	}

	public static function getItemsOutOfWarranty($a)
	{
		if (is_array($a)) {
			$WHERE = 'WHERE ' . tldUtils::constructWhere($a);
		} else {
			$WHERE = "WHERE $a";
		}
		$SELECT = self::getSELECT();
		$FROM = self::getFROM();
		$query = <<<EOF
$SELECT
$FROM
$WHERE 
		AND DATEDIFF(item.dt_warranty_end,NOW()) < 0 AND item.hidden =0
EOF;

		return tldUtils::getSqlToAssocArray($query);
	}

	public static function getItemsDisposed($a)
	{
		if (is_array($a)) {
			$WHERE = 'WHERE ' . tldUtils::constructWhere($a);
		} else {
			$WHERE = "WHERE $a";
		}
		$SELECT = self::getSELECT();
		$FROM = self::getFROM();
		$query = <<<EOF
$SELECT
$FROM
$WHERE 
		AND item.hidden =1 
EOF;

		return tldUtils::getSqlToAssocArray($query);
	}

	public static function getDevicesByWarrantyEnd($warrantyendvalue, $warrantyendunit, $bu)
	{

		$SELECT = self::getSELECT();
		$FROM = self::getFROM();
		$query = <<<EOF
$SELECT
$FROM
WHERE item.dt_warranty_end < DATE_ADD(NOW() , INTERVAL $warrantyendvalue $warrantyendunit) AND item.dt_warranty_end > NOW() AND item.buyer_bu_id=$bu
EOF;

		return tldUtils::getSqlToAssocArray($query);
	}

	public static function getDevicesWithOpenTask($bu)
	{
		$SELECT = self::getSELECT();
		$FROM = self::getFROM();
		$query = <<<EOF
$SELECT
$FROM
LEFT JOIN tasks ON tasks.id = assignment.task_id
WHERE item.buyer_bu_id=$bu AND assignment.task_id<>0 AND tasks.status NOT LIKE 'CLOSED'
EOF;

		return tldUtils::getSqlToAssocArray($query);
	}

	public static function getDevicesBeforeDateTo($bu, $day)
	{
		$SELECT = self::getSELECT();
		$FROM = self::getFROM();
		if (!empty($bu)) {
			$WHERE = ' AND item.buyer_bu_id=' . $bu;
		}
		$query = <<<EOF
$SELECT
$FROM
WHERE assignment.dt_to < DATE_ADD(NOW() , INTERVAL $day DAY) AND assignment.dt_to <> '0000-00-00' AND item.hidden =0 
$WHERE
EOF;

		return tldUtils::getSqlToAssocArray($query);
	}

	public static function getUnassignedDevices($bu)
	{
		$SELECT = self::getSELECT();
		$FROM = self::getFROM();
		$query = <<<EOF
$SELECT
$FROM
WHERE item.buyer_bu_id=$bu AND assignment.destination_type is NULL AND item.hidden = 'NO'
EOF;

		return tldUtils::getSqlToAssocArray($query);
	}

	public static function byBrandId($id)
	{

		$query = <<<EOF
SELECT count(itemBrand.id) AS num
FROM mis_inventory_brands AS itemBrand, mis_inventory_items AS item WHERE itemBrand.id=item.brand_id 
AND itemBrand.id = $id
GROUP BY itemBrand.id
EOF;
		return tldUtils::getSqlRowToAssocArray($query);
	}

	public static function replaceBrandById($preid, $newid)
	{
		$query = <<<EOF
UPDATE mis_inventory_items AS item SET item.brand_id = $newid WHERE item.brand_id = $preid 
EOF;
		return tldUtils::sqlQuery($query);
	}

	public static function byTypeId($id)
	{

		$query = <<<EOF
SELECT count(itemType.id) AS num
FROM mis_inventory_items AS item, mis_inventory_item_types AS itemType WHERE itemType.id=item.type_id 
AND itemType.id = $id
GROUP BY itemType.id
EOF;
		return tldUtils::getSqlRowToAssocArray($query);
	}

	public static function replaceTypeById($preid, $newid)
	{
		$query = <<<EOF
UPDATE mis_inventory_items AS item SET item.type_id = $newid WHERE item.type_id = $preid 
EOF;
		return tldUtils::sqlQuery($query);
	}

	public static function getCategory($id)
	{
		$query = <<<EOF
SELECT mis_inventory_item_types.category 
FROM mis_inventory_items
LEFT JOIN mis_inventory_item_types ON mis_inventory_items.type_id = mis_inventory_item_types.id
WHERE mis_inventory_items.id = $id 
EOF;
		$category = tldUtils::getSqlRowToAssocArray($query);
		return $category['category'];
	}

	public static function itemByBUByType()
	{

		$FROM = self::getFROM();
		$query = <<<EOF
SELECT
    COUNT(*) AS qty,
    buyer_bu.location AS buyer_location,
    UPPER(itemType.name) AS type_name
$FROM
WHERE item.hidden =0
GROUP BY buyer_location,type_name
EOF;

		return tldUtils::getSqlToAssocArray($query);
	}

	public static function itemByDeptByType($a)
	{

		$FROM = self::getFROM();
		$query = <<<EOF
SELECT
    COUNT(*) AS qty,
    buyer_dpt.dpt AS buyer_dpt,
    UPPER(itemType.name) AS type_name
$FROM
WHERE buyer_bu.id = $a AND item.hidden =0 AND itemType.category LIKE 'HARDWARE'
GROUP BY buyer_dpt,type_name
EOF;

		return tldUtils::getSqlToAssocArray($query);
	}

	public static function itemByRegionByDeptByType($a)
	{

		$FROM = self::getFROM();
		$query = <<<EOF
SELECT
    COUNT(*) AS qty,
    buyer_dpt.dpt AS buyer_dpt,
    UPPER(itemType.name) AS type_name
$FROM
WHERE buyer_bu.region LIKE '$a' AND item.hidden =0 AND itemType.category LIKE 'HARDWARE'
GROUP BY buyer_dpt,type_name
EOF;

		return tldUtils::getSqlToAssocArray($query);
	}

	public static function listItemWithContractByRegionByDept($a)
	{
		$SELECT = self::getSELECT();
		$FROM = self::getFROM();
		$query = <<<EOF
$SELECT,mis_inventory_contracts.end_dt,mis_inventory_contracts.start_dt
$FROM
LEFT JOIN mis_inventory_contracts ON item.id = mis_inventory_contracts.parent_id 
WHERE $a AND mis_inventory_contracts.id>0
EOF;

		return tldUtils::getSqlToAssocArray($query);

	}

	public static function countItemByRegionByDeptByType($a)
	{

		$FROM = self::getFROM();
		$query = <<<EOF
SELECT
    COUNT(*) AS qty,
    buyer_dpt.dpt AS buyer_dpt,
    UPPER(itemType.name) AS type_name
$FROM
WHERE $a
GROUP BY buyer_dpt,type_name
EOF;

		return tldUtils::getSqlToAssocArray($query);
	}

	public static function itemByRegionByType($a)
	{

		$FROM = self::getFROM();
		$query = <<<EOF
SELECT
    COUNT(*) AS qty,
    (CASE 
    WHEN buyer_bu.region = 'ASIA' THEN 'APAC'
    WHEN buyer_bu.region = 'EUROPE' THEN 'EMEAI'
    WHEN buyer_bu.region = 'NORTH AMERICA' THEN 'NALA'
    WHEN buyer_bu.region = 'SOUTH AMERICA' THEN 'NALA'
    END) AS regional,
    UPPER(itemType.name) AS type_name
$FROM
WHERE item.hidden =0 
GROUP BY regional,type_name
EOF;

		return tldUtils::getSqlToAssocArray($query);
	}

	public static function licenseByBUByType($con)
	{

		$FROM = self::getFROM();
		$query = <<<EOF
SELECT
    COUNT(*) AS qty,
    buyer_dpt.dpt AS buyer_dept,
    UPPER(itemType.name) AS type_name
$FROM
WHERE itemType.category LIKE 'SOFTWARE' AND item.buyer_bu_id = $con
GROUP BY buyer_dept,type_name
EOF;

		return tldUtils::getSqlToAssocArray($query);
	}

	public function getContract()
	{
		$query = <<<EOF
SELECT mis_inventory_contracts.* 
FROM mis_inventory_items
LEFT JOIN mis_inventory_contracts ON mis_inventory_items.id = mis_inventory_contracts.parent_id
WHERE mis_inventory_items.id = $this->id 
ORDER BY mis_inventory_contracts.id DESC 
LIMIT 1
EOF;

		return tldUtils::getSqlRowToAssocArray($query);
	}

	public static function checkContractExpired()
	{
		$query = <<<EOF
SELECT mis_inventory_items.*,
  (CASE 
    WHEN DATE_FORMAT(mis_inventory_contracts.end_dt, '%Y-%m-%d') = DATE_FORMAT(DATE_ADD(now(), INTERVAL 15 DAY), '%Y-%m-%d')    THEN '15 Days'
    WHEN DATE_FORMAT(mis_inventory_contracts.end_dt, '%Y-%m-%d') = DATE_FORMAT(DATE_ADD(now(), INTERVAL 1 MONTH), '%Y-%m-%d')    THEN 'One Months'
    WHEN DATE_FORMAT(mis_inventory_contracts.end_dt, '%Y-%m-%d') = DATE_FORMAT(DATE_ADD(now(), INTERVAL 2 MONTH), '%Y-%m-%d')    THEN 'Two Month'
  END) AS interval_period
FROM mis_inventory_contracts
LEFT JOIN mis_inventory_items ON mis_inventory_items.id = mis_inventory_contracts.parent_id
LEFT JOIN locations AS buyer_bu ON buyer_bu.id=mis_inventory_items.buyer_bu_id
WHERE DATE_FORMAT(mis_inventory_contracts.end_dt, '%Y-%m-%d') = DATE_FORMAT(DATE_ADD(now(), INTERVAL 2 MONTH), '%Y-%m-%d') OR DATE_FORMAT(mis_inventory_contracts.end_dt, '%Y-%m-%d') = DATE_FORMAT(DATE_ADD(now(), INTERVAL 1 MONTH), '%Y-%m-%d') OR DATE_FORMAT(mis_inventory_contracts.end_dt, '%Y-%m-%d') = DATE_FORMAT(DATE_ADD(now(), INTERVAL 15 DAY), '%Y-%m-%d')
EOF;

		$rows = tldUtils::getSqlToAssocArray($query);
		foreach ($rows AS $key => $row) {
			$contracts[$row['buyer_bu_id']][$row['interval_period']][$key] = $row;
		}

		$locations = tldLocation::getLocationList();
		foreach ($locations as $location) {
			if (empty($contracts[$location['id']])) {
				continue;
			}
			$query = <<<EOF
SELECT
    T1.id,T1.email
FROM people as T1
    LEFT JOIN people_groups AS T2 ON T1.id=T2.parent_id
WHERE
    (T2.group_name LIKE 'ROLE_NA' OR T2.group_name LIKE 'ROLE_MISM')
	AND T1.hidden=0 
	AND T1.bu_id = {$location['id']}
EOF;

			$receipts = tldUtils::getSqlToAssocArray($query);
			$to = array_column($receipts, 'email');
			$to = implode(', ', array_unique($to));

			foreach ($contracts[$location['id']] AS $id => $contract) {
				$subject = "Item contract will be expired in $id";
				$report = new tldReportColumnar(
					$contract,
					[
						'xItems' => [
							'id' => 'ID',
							'dt' => 'Date',
							'type_name' => 'Type',
							'brand_name' => 'Brand',
							'model' => 'Model',
							'manufacturer_sn' => 'Manufacturer SN',
							'tld_sn' => 'TLD SN',
							'fixasset_id' => 'Fix Asset ID',
							'state' => 'State',
							'description' => 'Description',
							'dt_warranty_end' => 'End of warranty',
							'buyer_location' => 'Requestor BU',
							'buyer_dpt' => 'Requestor Department',
							'destination' => 'Destination',
							'hidden_status' => 'Disposed?',
						],
						'sortable' => 'FALSE',
						'title' => $subject,
						'links' => ['id' => 'https://www.tld-gse.com/en/private/mis/mis.php?m[0]=inventory&m[1]=view&id='],
					]
				);
				$body .= $report->fetch();
			}

			tldUtils::emailAttachment(
				$to,
				'noreply@tld-gse.com',
				'MIS inventory contract expire reminder',
				$body,
				null,
				null,
				null
			);
			$body = '';
			$to = '';
		}
	}

	public static function checkWarrantyExpired()
	{
		$query = <<<EOF
SELECT mis_inventory_items.*,
CASE mis_inventory_items.notify
    WHEN 0 THEN 'NO'
    WHEN 1 THEN 'YES'
END AS notification,
  (CASE 
    WHEN DATE_FORMAT(mis_inventory_items.dt_warranty_end, '%Y-%m-%d') = DATE_FORMAT(DATE_ADD(now(), INTERVAL 15 DAY), '%Y-%m-%d')    THEN '15 Days'
    WHEN DATE_FORMAT(mis_inventory_items.dt_warranty_end, '%Y-%m-%d') = DATE_FORMAT(DATE_ADD(now(), INTERVAL 1 MONTH), '%Y-%m-%d')    THEN 'One Months'
    WHEN DATE_FORMAT(mis_inventory_items.dt_warranty_end, '%Y-%m-%d') = DATE_FORMAT(DATE_ADD(now(), INTERVAL 2 MONTH), '%Y-%m-%d')    THEN 'Two Month'
  END) AS interval_period
FROM mis_inventory_items 
LEFT JOIN mis_inventory_item_types ON mis_inventory_items.type_id = mis_inventory_item_types.id
LEFT JOIN locations AS buyer_bu ON buyer_bu.id=mis_inventory_items.buyer_bu_id
WHERE DATE_FORMAT(mis_inventory_items.dt_warranty_end, '%Y-%m-%d') = DATE_FORMAT(DATE_ADD(now(), INTERVAL 2 MONTH), '%Y-%m-%d') OR DATE_FORMAT(mis_inventory_items.dt_warranty_end, '%Y-%m-%d') = DATE_FORMAT(DATE_ADD(now(), INTERVAL 1 MONTH), '%Y-%m-%d') OR DATE_FORMAT(mis_inventory_items.dt_warranty_end, '%Y-%m-%d') = DATE_FORMAT(DATE_ADD(now(), INTERVAL 15 DAY), '%Y-%m-%d')
AND mis_inventory_item_types.category LIKE 'HARDWARE' AND mis_inventory_items.notify =1
EOF;

		$rows = tldUtils::getSqlToAssocArray($query);
		foreach ($rows AS $key => $row) {
			$warranty[$row['buyer_bu_id']][$row['interval_period']][$key] = $row;
		}

		$locations = tldLocation::getLocationList();
		foreach ($locations as $location) {
			if (empty($warranty[$location['id']])) {
				continue;
			}
			$query = <<<EOF
SELECT
    T1.id,T1.email
FROM people as T1
    LEFT JOIN people_groups AS T2 ON T1.id=T2.parent_id
WHERE
    (T2.group_name LIKE 'ROLE_NA' OR T2.group_name LIKE 'ROLE_MISM')
	AND T1.hidden=0 
	AND T1.bu_id = {$location['id']}
EOF;

			$receipts = tldUtils::getSqlToAssocArray($query);
			$to = array_column($receipts, 'email');
			$to = implode(', ', array_unique($to));
			foreach ($warranty[$location['id']] AS $id => $warranty) {
				$subject = "Item warranty will be expired in $id";
				$report = new tldReportColumnar(
					$warranty,
					[
						'xItems' => [
							'id' => 'ID',
							'dt' => 'Date',
							'type_name' => 'Type',
							'brand_name' => 'Brand',
							'model' => 'Model',
							'manufacturer_sn' => 'Manufacturer SN',
							'tld_sn' => 'TLD SN',
							'fixasset_id' => 'Fix Asset ID',
							'state' => 'State',
							'description' => 'Description',
							'dt_warranty_end' => 'End of warranty',
							'buyer_location' => 'Requestor BU',
							'buyer_dpt' => 'Requestor Department',
							'destination' => 'Destination',
							'hidden_status' => 'Disposed?',
							'notification' => 'Warranty Notification?',
						],
						'sortable' => 'FALSE',
						'title' => $subject,
						'links' => ['id' => 'https://www.tld-gse.com/en/private/mis/mis.php?m[0]=inventory&m[1]=view&id='],
					]
				);
				$body .= $report->fetch();
			}

			tldUtils::emailAttachment(
				$to,
				'noreply@tld-gse.com',
				'MIS inventory warranty expire reminder',
				$body,
				null,
				null,
				null
			);
			$body = '';
			$to = '';
		}
	}
}

class Tld_Mis_Inventory_ItemType
{

	public $id;
	public $header;

	public function __construct($id)
	{
		$this->id = $id;
		$this->header = $this->getHeader();
	}

	public function getId()
	{
		return $this->id;
	}

	public static function getCategoryList(): array
	{
		return ['HARDWARE' => 'HARDWARE', 'SOFTWARE' => 'SOFTWARE', 'SERVER' => 'SERVER', 'ISP' => 'INTERNET LINE'];
	}

	public function isEmpty()
	{
		return empty($this->header);
	}

	public static function getSELECT()
	{
		return <<<EOF
SELECT
    id,
    UPPER(name) AS name,
    UPPER(category) AS category,
    short_desc,
    length
EOF;
	}

	public static function getFROM()
	{
		return <<<EOF
FROM
    mis_inventory_item_types
EOF;
	}

	public function getHeader()
	{
		$SELECT = self::getSELECT();
		$FROM = self::getFROM();
		$query = <<<EOF
$SELECT
$FROM
WHERE mis_inventory_item_types.id=$this->id
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
			$ORDERBY = 'ORDER BY name';
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
			'name',
			'category',
			'short_desc',
		];
		$SET = tldUtils::getSqlSet($a, $fields);
		$query = "INSERT INTO mis_inventory_item_types SET $SET";

		return tldUtils::sqlInsert($query);
	}

	public function update($a, $fields = '')
	{
		if (empty($a)) {
			return;
		}
		$SET = tldUtils::getSqlSet($a, $fields);
		$query = "UPDATE mis_inventory_item_types SET $SET WHERE id=$this->id";
		return tldUtils::sqlQuery($query);
	}

	public function delete()
	{
		$query = "DELETE FROM mis_inventory_item_types WHERE id=$this->id";
		return tldUtils::sqlExecute($query);
	}

	public static function getList($type = '')
	{
		if ($type === 'addSoftware') {
			return self::byConstraints(['category' => 'SOFTWARE']);
		}
		if ($type === 'addHardware') {
			return self::byConstraints(['category' => 'HardWARE']);
		}
		if ($type === 'addISP') {
			return self::byConstraints(['category' => 'ISP']);
		}

		return self::byConstraints('1=1');
	}

	public static function getListAsIdName($type = '')
	{
		return array_column(self::getList($type), 'name', 'id');
	}

	public static function getServerCAT()
	{
		$query = "SELECT  UPPER(name) AS name, UPPER(category) AS category, short_desc, length FROM mis_inventory_item_types HAVING category like 'SERVER' ORDER BY name";

		return tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['short_desc', 'name']);
	}

}


class Tld_Mis_Inventory_Building
{

	public $id;
	public $header;

	public function __construct($id)
	{
		$this->id = $id;
		$this->header = $this->getHeader();
	}

	public function getId()
	{
		return $this->id;
	}

	public function isEmpty()
	{
		return empty($this->header);
	}

	public static function getSELECT()
	{
		return <<<EOF
SELECT
    buildings.id,
    buildings.location_id,
    UPPER(buildings.name) AS name,
    buildings.disable,
    locations.location,
    IF(buildings.disable=1, 'DISABLED', 'ENABLED') AS status
EOF;
	}

	public static function getFROM()
	{
		return <<<EOF
FROM
    mis_inventory_buildings AS buildings
    LEFT JOIN locations ON buildings.location_id=locations.id
EOF;
	}

	public function getHeader()
	{
		$SELECT = self::getSELECT();
		$FROM = self::getFROM();
		$query = <<<EOF
$SELECT
$FROM
WHERE buildings.id=$this->id
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
			$ORDERBY = 'ORDER BY name';
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
			'name',
			'location_id',
		];
		$SET = tldUtils::getSqlSet($a, $fields);
		$query = "INSERT INTO mis_inventory_buildings SET disable=0,$SET";
		return tldUtils::sqlInsert($query);
	}

	public function update($a, $fields = '')
	{
		if (empty($a)) {
			return;
		}
		$SET = tldUtils::getSqlSet($a, $fields);
		$query = "UPDATE mis_inventory_buildings SET $SET WHERE id=$this->id";
		return tldUtils::sqlQuery($query);
	}

	public function delete()
	{
		$query = "DELETE FROM mis_inventory_buildings WHERE id=$this->id";
		return tldUtils::sqlExecute($query);
	}

	public function disable()
	{
		if ($this->header['disable'] == 1) {
			return 'Record already disabled';
		}
		return $this->update(['disable' => 1]);
	}

	public function enable()
	{
		if ($this->header['disable'] == 0) {
			return 'Record already enabled';
		}
		return $this->update(['disable' => 0]);
	}

	public function getList()
	{
		return self::byConstraints('1=1');
	}

	public function getEnableList()
	{
		return self::byConstraints('disable<>1');
	}

	public function getEnableListAsIdName()
	{
		return array_column(self::getEnableList(), 'name', 'id');
	}

	public function getEnableListByLocationId($id)
	{
		return self::byConstraints(['location_id' => $id, 'disable' => 0]);
	}

}


class Tld_Mis_Inventory_ItemBrand
{

	public $id;
	public $header;

	public function __construct($id)
	{
		$this->id = $id;
		$this->header = $this->getHeader();
	}

	public function getId()
	{
		return $this->id;
	}

	public function isEmpty()
	{
		return empty($this->header);
	}

	public static function getSELECT()
	{
		return <<<EOF
SELECT
id,
UPPER(name) AS name,
UPPER(support_url) AS support_url,
disable
EOF;
	}

	public static function getFROM()
	{
		return <<<EOF
FROM
    mis_inventory_brands
EOF;
	}

	public function getHeader()
	{
		$SELECT = self::getSELECT();
		$FROM = self::getFROM();
		$query = <<<EOF
$SELECT
$FROM
WHERE mis_inventory_brands.id=$this->id
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
			$ORDERBY = 'ORDER BY name';
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
			'name',
			'support_url',
		];
		$SET = tldUtils::getSqlSet($a, $fields);
		$query = "INSERT INTO mis_inventory_brands SET $SET";
		return tldUtils::sqlInsert($query);
	}

	public function update($a, $fields = '')
	{
		if (empty($a)) {
			return;
		}
		$SET = tldUtils::getSqlSet($a, $fields);
		$query = "UPDATE mis_inventory_brands SET $SET WHERE id=$this->id";
		return tldUtils::sqlQuery($query);
	}

	public function delete()
	{
		$query = "DELETE FROM mis_inventory_brands WHERE id=$this->id";
		return tldUtils::sqlExecute($query);
	}

	public static function getList()
	{
		return self::byConstraints('1=1');
	}

	public static function getListAsIdName()
	{
		return array_column(self::getList(), 'name', 'id');
	}

}


class Tld_Mis_Inventory_Contract
{
	public $itsID;
	public $itsHeader;
	public $itsAttachmentDir = 'mis_inventory_contracts';

	public function __construct($id)
	{
		$this->itsID = $id;
		$this->itsHeader = $this->getHeader();
	}

	/**
	 * Get mod_file ID
	 * @return integer
	 */
	public function getID()
	{
		return $this->itsID;
	}

	/**
	 * Is this contract_file empty?
	 * @return boolean
	 */
	public function isEmpty()
	{
		return empty($this->itsHeader);
	}

	/**
	 * Get tldFile ID
	 * @return integer
	 */
	public function getContractID()
	{
		return $this->itsHeader['fid'];
	}

	/**
	 * Get filename
	 * @return string
	 */
	public function getFilename()
	{
		return $this->itsHeader['filename'];
	}

	/**
	 * Get module name file is linked to
	 * @return string
	 */
	public function getModule()
	{
		return $this->itsHeader['module'];
	}

	/**
	 * Get id of document file is linked to
	 * @return integer
	 */
	public function getParentID()
	{
		return $this->itsHeader['parent_id'];
	}

	/**
	 * Get level of document file
	 * @return integer
	 */
	public function getLevel()
	{
		return $this->itsHeader['level'];
	}

	/**
	 * Get mod_file header
	 * @return array
	 */
	public function getHeader()
	{
		$SELECT = self::getSELECT();
		$FROM = self::getFROM();
		$query = <<<EOF
$SELECT
$FROM
WHERE mis_inventory_contracts.parent_id=$this->itsID
EOF;
		return tldUtils::getSqlRowToAssocArray($query);
	}

	/**
	 * Get SELECT query part
	 * @return string
	 */
	public static function getSELECT()
	{
		return <<<EOF
SELECT *
EOF;
	}

	/**
	 * Get FROM query part
	 * @return string
	 */
	public static function getFROM()
	{
		return <<<EOF
FROM mis_inventory_contracts
EOF;
	}

	/**
	 * Send file to stdout
	 * @return html header and file contents flow
	 */
	public static function outAttachment($id)
	{
		$files = Tld_Mis_Inventory_Contract::getFiles($id);

		if (count($files) <> 1) {
			return;
		}

		$myFile = new basicFile(
			tldUtils::getPathToUploadFile(
				'mis_inventory_contracts',
				$files[0]['filename']
			)
		);
		$myFile->outFile();
	}


	public static function getFiles($id = '')
	{
		if (is_numeric($id)) {
			$WHERE .= " AND id=$id";
		}
		$query = <<<EOF
SELECT * FROM mis_inventory_contracts
WHERE 1=1
$WHERE
EOF;

		return tldUtils::getSqlToAssocArray($query);
	}

	public static function deleteFile($file_id)
	{
		if (empty($file_id) || !is_numeric($file_id)) {
			return 'File ID is not set or not valid!';
		}
		$queryFile = "SELECT filename FROM mis_inventory_contracts WHERE id=$file_id";
		$file = tldUtils::getSqlRowToAssocArray($queryFile);
		if (unlink($GLOBALS['UPLOADS_PATH'] . '/mis_inventory_contracts/' . $file['filename'])) {
			$query = "DELETE FROM mis_inventory_contracts WHERE id=$file_id LIMIT 1";

			return tldUtils::sqlExecute($query);
		}

		return 'Could not delete the file physically';
	}

	/**
	 * Insert and link file to a record module
	 * @param $a array
	 * @param $file_array array
	 * @return string on error, integer on success
	 */
	public static function insert($a)
	{

		$fields = ['parent_id', 'poster', 'start_dt', 'end_dt', 'account', 'support', 'link', 'filename'];
		$SET = tldUtils::getSqlSet($a, $fields);
		$query = "INSERT INTO mis_inventory_contracts SET $SET, date=NOW()";

		return tldUtils::sqlInsert($query);
	}


	/**
	 * Generic ModFile update method
	 * @param $data array of datas
	 * @param $fields array of ModFile fields to update
	 * @return string on error
	 */
	public function update($data, $fields = '')
	{
		if (empty($this->itsID)) {
			return 'Not object context!';
		}
		$SET = tldUtils::getSqlSet($data, $fields);
		$query = "UPDATE mis_inventory_contracts SET $SET WHERE id=$this->itsID LIMIT 1";
		return tldUtils::sqlQuery($query);
	}

	/**
	 * Delete the current mod_file + file FS
	 * @return boolean
	 */
	public function delete()
	{
		if (empty($this->itsID)) {
			return;
		}
		$query = "DELETE FROM mod_contracts WHERE id=$this->itsID LIMIT 1";
		$e = tldUtils::sqlQuery($query);
		if (is_string($e)) {
			return 'Can not delete mod contracts entry';
		}
		$contract = new tldContract($this->getContractID());
		$e = $contract->delete();
		return $e;
	}

	/**
	 * Get mod file list by Constraints
	 * @param $a array
	 * @param $opt array
	 * @return array
	 */
	public static function byConstraints($a, $opt = '')
	{
		if (is_array($a)) {
			$HAVING = tldUtils::constructWhere($a);
		} else {
			$HAVING = $a;
		}
		// look for constraints
		if (!empty($HAVING)) {
			$HAVING = " HAVING $HAVING ";
		}
		$SELECT = self::getSELECT();
		$FROM = self::getFROM();
		$query = <<<EOF
$SELECT
$FROM
$HAVING
ORDER BY date
EOF;
		return tldUtils::getSqlToAssocArray($query);
	}

	/**
	 * Get file entries by parent_id
	 * @param int $parent_id
	 * @return array of db rows
	 */
	public static function byParent($parent_id)
	{
		$a['parent_id'] = $parent_id;

		return self::byConstraints($a);
	}
}
