<?php
/**
 *    User related classes
 *
 * @package Users and Groups
 * @desc All classes related to the users are kept in this file
 * @access public
 * @author Graham K.L. Fong <graham.fong@tld-america.com>
 * @copyright TLD
 */

use ApiBundle\Client;
use GuzzleHttp\Exception\ClientException;

/**
 * Need these functions
 */
include_once('common.inc.php');
include_once('Mail.php');
include_once('Mail/mime.php');
include_once('erp.inc.php');
include_once('ecommerce.inc.php');

// Hack to support authentication from Apache in CGI mode (PHP-FPM)
if (!isset($GLOBALS['PHP_AUTH_USER']) && isset($_SERVER['HTTP_AUTHORIZATION'])) {
    [$GLOBALS['PHP_AUTH_USER'], $GLOBALS['PHP_AUTH_PW']] = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
    [$_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']] = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
}

/**
 *    returns a tldGenericUser class
 *
 *    Base class for other user classes to extend from
 *
 * @package UsersAndGroups
 */
class tldGenericUser
{
    public $itsDetails;
    public $itsViewableFields;
    public $itsValidity;
    public $PASSWORD_CHANGE_INTERVAL = 60;

    public function isEmpty(): bool
    {
        return empty($this->itsDetails);
    }

    public function isAdmin(): bool
    {
        return $this->itsDetails['perms'] === 'admin';
    }

    public function setUserDetails($query)
    {
        if ($rows = TldDatabase::query($query)) {
            if (TldDatabase::numRows($rows) == 1) {
                $this->itsDetails = TldDatabase::fetchArray($rows);
                $this->itsValidity = 1;
                $this->itsId = $this->itsDetails['id'];
                $this->itsID = $this->itsDetails['id'];
            }
        }
    }

    public function getDetails()
    {
        return $this->itsDetails;
    }

    public function getHeader()
    {
        return $this->itsDetails;
    }

    public function getId()
    {
        return $this->itsDetails['id'];
    }

    public function isValid()
    {
        return $this->itsValidity;
    }

    public function getUserid()
    {
        return 'Generic userid function';
    }

    public function getFullname()
    {
        return 'Generic fullname function';
    }

    public function getFirstname()
    {
        return 'Generic firstname function';
    }

    public function getLastname()
    {
        return 'Generic lastname function';
    }

    public function getUsername()
    {
        return 'Generic User Class';
    }

    public function getTitle()
    {
        return 'Generic User Class';
    }

    public function changePassword($old, $new, $check)
    {
        if (empty($new) || empty($check)) {
            $ret = 'ERROR: One or more fields are blank!';
        } else {
            $error = $this->checkPassword($old, $new, $check);
            if (is_string($error)) {
                $ret = $error;
            } else {
                $ret = $this->updatePassword($old, $new);
            }
        }
        return $ret;
    }

    public function checkPassword($old, $new, $check)
    {
        if ($old <> $this->getPassword()) {
            //Check old password
            $result = 'ERROR: Old password incorrect';
        } elseif ($new <> $check) { //Check both passwords same
            $result = 'ERROR: New password fields do not match.';
        } elseif (strlen($new) < 5) { //Check passwords length
            $result = 'ERROR: New password must be at least 5 characters.';
        } elseif ($this->passwordUsedBefore($new)) {
            //Check password used in last 5 times
            $result = 'ERROR: New password was used within the last 5 password changes.';
        } elseif ($old == $new) {
            $result = 'ERROR: New password is same as old.';
        } elseif ($new == $check) {
            return;
        } else {
            $result = 'ERROR: Unknown error...';
        }
        return $result;
    }

    public function getPassword()
    {
        return 'Generic User Class';
    }

    public function getAddress()
    {
        return 'Generic User Class';
    }

    public function getEmail()
    {
        return 'Generic User Class';
    }

    public function isInGroup($group, $options = [])
    {
        return 'Generic User Class';
    }

    public static function getLanguageList()
    {
        return [
            'en' => 'English',
            'fr' => 'French',
            'es' => 'Spannish',
            'zh' => 'Chinese',
            'ru' => 'Russian',
            'ja' => 'Japanese',
        ];
    }
}


/**
 *    returns a tldUser class
 *
 *    Class for accessing and manipulating TLD Personnel data ONLY
 *
 * @package UsersAndGroups
 */
#[AllowDynamicProperties]
class tldUser extends tldGenericUser
{

    /**
     * User groups.
     *
     * @var array
     */
    protected $Groups = [];

    public $itsViewableFields = [
        'lastname' => 'Lastname',
        'firstname' => 'Firstname',
        'title' => 'Title',
        'phone' => 'Telephone',
        'direct_phone' => 'Direct Line',
        'home_phone' => 'Home',
        'mobile' => 'Mobile',
        'fax' => 'Fax',
        'address' => 'Address',
    ];

    public function __construct($id)
    {
        if (is_numeric($id)) {
            $this->itsId = $id;
            $this->itsID = $id;
            $this->setDetailsById($id);
        } else {
            $this->setDetailsByEmail($id);
        }
    }

    public function getItsPhotoPath()
    {
        return $GLOBALS['UPLOADS_PATH'];
    }

    public function getItsNoPhotoFilePath()
    {
        return $GLOBALS['SHARED_PATH'] . '/no_photo.jpg';
    }

    public function getItsGetPicFilePath()
    {
        return $GLOBALS['SHARED_PATH'] . '/get_pic.php';
    }

    public static function getSELECT(bool $displayEmail = true): string
    {
        $emailRequest = $displayEmail ? ", ' (', people.email, ')'" : "";
        return <<<EOF
SELECT
    people.*,
    CONCAT(people.lastname,', ',people.firstname$emailRequest) AS fullname,
    (SELECT CONCAT(sup.lastname,', ',sup.firstname)
        FROM people AS sup WHERE sup.id=people.reports_to
    ) AS reports_to_fullname,
    regions.division AS division,
    bu.location AS location,
    dpt.dpt AS department,
    fct.dsc AS tld_function
EOF;
    }

    public static function getFROM(): string
    {
        return <<<EOF
FROM
    people
    LEFT JOIN tld_regions AS regions ON regions.id=people.div_id
    LEFT JOIN locations AS bu ON bu.id=people.bu_id
    LEFT JOIN tld_departments AS dpt ON dpt.id=people.dpt_id
    LEFT JOIN tld_functions AS fct ON fct.id=people.fct_id
EOF;
    }

    public function setDetailsById($id)
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = "$SELECT $FROM WHERE people.id=$id";
        $this->setUserDetails($query);
    }

    public function setDetailsByEmail($email)
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = "$SELECT $FROM WHERE people.email='$email'";
        $this->setUserDetails($query);
    }

    public function isEmpty(): bool
    {
        return empty($this->itsDetails);
    }

    public function isEnable()
    {
        if (!$this->isInGroup(['acl_auth_INTRANET']) && !$this->isInGroup(['pi_OPERATOR', 'pi_TESTER'])) {
            return false;
        } else {
            return true;
        }
    }

    public function refreshDetails()
    {
        $this->setDetailsById($this->getID());
    }

    public function logout()
    {
        tldUtils::log_event('Logout ' . $this->getUserid());
    }

    public function getID()
    {
        return $this->itsDetails['id'];
    }

    public function getBannUserID()
    {
        return $this->itsDetails['baan_id'];
    }

    public function getBannEmployeeID()
    {
        return $this->itsDetails['baan_employee_id'];
    }

    public function getRegionID()
    {
        return $this->itsDetails['div_id'];
    }

    public function getBUID()
    {
        return $this->itsDetails['bu_id'];
    }

    public function getBUName()
    {
        return tldLocation::getLocationByID($this->getBUID());
    }

    public function getDepartmentID()
    {
        return $this->itsDetails['dpt_id'];
    }

    public function getUserid()
    {
        return $this->itsDetails['email'];
    }

    public function getEmail()
    {
        return $this->itsDetails['email'];
    }

    public function getDomain()
    {
        [$username, $domain] = explode("@", $this->itsDetails['email']);

        return strtolower($domain);
    }

    public function isInTLDDomain()
    {
        return in_array($this->getDomain(), tldLocation::getLocationEmailDomainsList(), true);
    }

    public function isAgent()
    {
        return $this->getRegionID() == 5;   // Division#5 -> AGENTS
    }

    public function getSupervisor()
    {
        return $this->itsDetails['reports_to'];
    }

    public function getTitle()
    {
        return $this->itsDetails['title'];
    }

    public function getFullname()
    {
        return strtoupper($this->itsDetails['lastname']) . ', ' . $this->itsDetails['firstname'];
    }

    public function getFirstname()
    {
        return $this->itsDetails['firstname'];
    }

    public function getLastname()
    {
        return $this->itsDetails['lastname'];
    }

    /**
     * Get linked log entries
     * @return array
     */
    public function getLog()
    {
        return tldModLog::byParent($this->itsID, 'USER');
    }

    /**
     * Add a comment to the log
     * @param array Array structure containing 'poster' and 'comment'
     * @return boolean
     */
    public function addLogEntry($id, $comment)
    {
        if (empty($this->itsID)) {
            return;
        }
        $a['parent_id'] = $this->itsID;
        $a['module'] = 'USER';
        $a['poster'] = $id;
        $a['comment'] = $comment;
        return tldModLog::insert($a);
    }

    public function getMyOrgChart($maxLevels = '')
    {
        $result[] = ['level' => 0, 'user' => $this->getDetails()];
        $this->getOrgChartRec($result, 0, $maxLevels);
        return $result;
    }

    public function getOrgChartRec(&$result, $level = 0, $maxLevels = 0)
    {
        $level++;
        if (isset($maxLevels) && $level > $maxLevels) {
            return;
        }
        $rows = $this->getSubordinates();
        if (count($rows) === 0) {
            return;
        }
        foreach ($rows as $row) {
            $result[] = ['level' => $level, 'user' => $row];
            $user = new tldUser($row['id']);
            $user->getOrgChartRec($result, $level, $maxLevels);
        }
    }

    public function getSubordinates($options = '')
    {
        $a = ['hidden' => 0, 'disabled' => 'N', 'reports_to' => $this->getID()];
        $rows = self::byConstraints($a);
        if ($options === 'smartyOptions') {
            return array_column($rows, 'fullname', 'id');
        }
        return $rows;
    }

    public static function getAllSubordinates($id = 0)
    {
        $SubordinateArray = [];
        $user = new tldUser($id);
        $Subordinates = $user->getSubordinates('smartyOptions');

        foreach ($Subordinates as $key => $Subordinate) {
            $SubordinateArray[] = $Subordinate;
            $SubordinateArray = array_merge($SubordinateArray, self::getAllSubordinates($key));
        }
        return $SubordinateArray;
    }

    public function getAllSubordinatesID($id = 0)
    {
        $SubordinateArray = [];
        $user = new tldUser($id);
        $Subordinates = $user->getSubordinates('smartyOptions');

        foreach ($Subordinates as $key => $Subordinate) {
            $SubordinateArray[] = $key;
            $SubordinateArray = array_merge($SubordinateArray, $user->getAllSubordinatesID($key));
        }
        return $SubordinateArray;
    }

    /**
     * Get list of users at the same level
     *
     * @return array
     */
    public function getPeers($options = '')
    {
        $a = ['hidden' => 0, 'disabled' => 'N', 'reports_to' => $this->getSupervisor()];
        $rows = self::byConstraints($a);
        if ($options === 'smartyOptions') {
            return array_column($rows, 'fullname', 'id');
        }
        return $rows;
    }

    public function getLinkedUsers(bool $displayEmail = true)
    {
        $emailRequest = $displayEmail ? ", ' (', people.email, ')'" : "";
        $query = <<<EOF
(
    SELECT people.*, CONCAT(people.lastname, ', ', people.firstname$emailRequest) as fullname
    FROM admin_people_links LEFT JOIN people ON admin_people_links.user1_id=people.id
    WHERE user2_id=$this->itsID AND people.hidden=0
)
UNION
(
    SELECT people.*, CONCAT(people.lastname, ', ', people.firstname$emailRequest) as fullname
    FROM admin_people_links LEFT JOIN people ON admin_people_links.user2_id=people.id
    WHERE user1_id=$this->itsID AND people.hidden=0
)
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public function getLinkedUsersID()
    {
        $query = <<<EOF
(SELECT user1_id AS userid FROM admin_people_links WHERE user2_id=$this->itsID)
UNION
(SELECT user2_id AS userid FROM admin_people_links WHERE user1_id=$this->itsID)
EOF;
        $rows = array_column(tldUtils::getSqlToAssocArray($query), 'userid', 'userid');
        return array_unique($rows);
    }

    /**
     * Get user's password
     *
     * @return string
     */
    public function getPassword()
    {
        return stripslashes($this->itsDetails['password']);
    }

    /**
     * Update user's passowrd in equotes system
     *
     * @return string on error
     */
    public function updateEquotesPassword($pw)
    {
        $id = $this->itsDetails['id'];
        if (empty($id)) {
            return 'ERROR: No userid set';
        }
        // Change db connection to equotes
        tldUtils::connectDb('equotes');
        // Update password in eQuotes
        $query = <<<EOF
UPDATE users SET
	Password_user=MD5('$pw')
WHERE
	Code_user=$id
EOF;
        return tldUtils::sqlQuery($query, null, ['src' => 'equotes']);
    }

    /**
     * Update intranet password and record old password in history
     *
     * @return string on error
     */
    public function updatePassword($old, $new)
    {
        $id = $this->itsDetails['id'];
        if (empty($id)) {
            return 'ERROR: No userid set';
        }
        // Update intranet account password
        $query = <<<EOF
UPDATE people SET
	password='$new',
	pass=ENCRYPT('$new')
WHERE
	id=$id AND password='$old'
EOF;
        $e = tldUtils::sqlQuery($query);
        if (is_string($e)) {
            return "error updating intranet password -> $e";
        }
        // Update password in the equotes system
        if ($this->isInGroup('gg_equotes_users')) {
            $e = $this->updateEquotesPassword($new);
            if (is_string($e)) {
                return "error updating equote password -> $e";
            }
        }
        // Send email about password change
        tldUtils::emailAttachment(
            $this->getEmail(),
            'noreply@tld-gse.com',
            'TLD Intranet Password change',
            'This is to confirm that your TLD Intranet password has just been changed.<br>' .
            'If you did not change the password then please notify the webmaster@tld-gse.com'
        );
        // Update password history
        $query = <<<EOF
INSERT INTO people_password_history
SET parent_id=$id, password='$old', change_date=NOW()
EOF;
        $e = tldUtils::sqlInsert($query);
        if (is_string($e)) {
            return "error updating password history -> $e";
        }
        return;
    }

    public function passwordUsedBefore($pass)
    {
        $id = $this->itsDetails['id'];
        $query = <<<EOF
			SELECT change_date, password FROM people_password_history
			WHERE parent_id=$id
			GROUP BY change_date
			ORDER BY id DESC
			LIMIT 5
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);
        if (count($rows)) {
            $i = 0;
            foreach ($rows as $row) {
                if ($i == 5) {
                    return false;
                }
                if ($row['password'] == $pass) {
                    return true;
                }
            }
        }
        return false;
    }

    public function outPhoto()
    {
        $myFile = $this->getPhotoFilename();
        if ($myFile) {
            $myFilepath = $this->getItsPhotoPath() . '/' . $myFile;
        } else {
            $myFilepath = $this->getItsNoPhotoFilePath();
        }
        $myPhoto = new basicFile($myFilepath);
        $myPhoto->outFile();
    }

    public function getPhotoFilename()
    {
        return $this->itsDetails['photo'];
    }

    /**
     * Checks whether user in a particular group
     *
     * - Checks if in group and level if true or false if group and level is a match.
     *
     * @param string|array $group
     * @param $level
     * @return bool true or false
     */
    public function isInGroupLevel($group, $level)
    {
        if (is_string($group)) {
            $group = [$group];
        }
        $levels = [];
        foreach ($group as $g) {
            $levels[] = (array) $this->isInGroup($g);
        }
        $levels = array_unique((array_merge(...$levels)));
        $levels = array_map('strval', $levels);

        return in_array((string)$level, $levels, true) || in_array('9999', $levels, true);
    }

    public function getUserGroups()
    {
        if (!empty($this->Groups)) {
            return $this->Groups;
        }

        $id = $this->getId();
        if ($id === null) {
            return [];
        }
        $query = "SELECT * FROM people_groups WHERE parent_id = $id";
        $this->Groups = tldUtils::getSqlToAssocArray($query);
        return $this->Groups;
    }

    /**
     * Checks whether user in a particular group
     *
     * - Checks if in group then returns level value form table if true.
     * - Returns an array of levels if user belongs to more than one level for a group e.g. used
     * for multiple baan company access for Sales Service/Parts module
     * - Returns zero if not in group
     * - If user is in superuser group then special '9999'
     * @param $options array set "return_rows" to true if you want results return
     * @return int|bool|string|array security level 0-9999
     */
    public function isInGroup($group, $options = '')
    {
        $this->getUserGroups();

        $formatedGroups = [];
        foreach ($this->Groups as $userGroup) {
            $formatedGroups[] = strtolower($userGroup['group_name']);
        }
        if (in_array('superuser', $formatedGroups, true) || ('USER' === $options && in_array('gg_mis', $formatedGroups, true))) {
            return '9999';
        }
        if (is_array($group)) {
            $group = array_map('strtolower', $group);
            $groups = array_intersect($formatedGroups, $group);
            if (!empty($groups)) {
                if (isset($options['return_rows']) && $options['return_rows'] === true) {
                    return $this->Groups;
                }
                return true;
            }
        } else {
            $group = strtolower($group);
            if (in_array($group, $formatedGroups, true)) {
                $levels = [];
                foreach ($this->Groups as $userGroup) {
                    if (strtolower($userGroup['group_name']) === $group) {
                        $levels[] = $userGroup['level'];
                    }
                }
                //if one row is returned then return the security level
                if (count($levels) === 1) {
                    return current($levels);
                }
                return $levels;
            }
        }

        return false;
    }

    public function getGroupsByConstraints($a = '1=1')
    {
        $id = $this->getID();
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
    grp.*,
    grp_select.description,
    bu.id AS bu_id,
    bu.location AS bu_name,
    bu.erp AS bu_erp
FROM people_groups AS grp
    LEFT JOIN people_groups_select AS grp_select ON grp.group_name=grp_select.group_name
    LEFT JOIN locations AS bu ON grp.level=bu.erp AND grp.level<>0
WHERE
    grp.parent_id=$id
    $WHERE
ORDER BY
    grp.group_name,grp.level
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public function getGroups()
    {
        return $this->getGroupsByConstraints();
    }

    public static function getRandomUser()
    {
        $a = "hidden=0 AND disabled='N'";
        $rows = self::byConstraints($a, ['orderBy' => 'RAND()']);
        return $rows[mt_rand(0, count($rows) - 1)];
    }

    /**
     * Function to transfer all IN PROGRESS module elements from a user to someone else
     * Only remove the acl_auth_INTRANET group and set hidden field to 1
     * @param int $from user id
     * @param int $to user id
     * @return string if error
     */
    public static function transferFromTo($from, $to)
    {
        if (!is_numeric($from) || !is_numeric($to)) {
            return 'Invalid parameters';
        }
        // UPDATE Tasks
        $queries[] = "UPDATE tasks SET assignee=$to WHERE assignee=$from AND status<>'CLOSED'";
        $queries[] = "UPDATE tasks SET assignor=$to WHERE assignor=$from AND status<>'CLOSED'";

        // UPDATE ST
        $queries[] = "UPDATE cal_st SET assignee=$to WHERE assignee=$from AND status<>'INACTIVE'";
        $queries[] = "UPDATE cal_st SET assignor=$to WHERE assignor=$from AND status<>'INACTIVE'";

        // UPDATE Queues members in case of MIS member
        $queries[] = "UPDATE mod_lists SET value=$to WHERE value=$from AND module='tts' AND list_name='MEMBERS'";

        // UPDATE USER SEQ
        $queries[] = "UPDATE cal_seq_user_nodes AS un, tasks AS t SET uid = $to WHERE un.parent_id = t.id AND un.uid = $from AND t.cur_step <= un.step AND t.status != 'CLOSED' AND t.seq_mode='USER_LEVEL' AND t.seq='Y'";

        // UPDATE CPA
        $queries[] = "UPDATE cpa SET poster=$to WHERE poster=$from AND status NOT IN('REJECTED','CLOSED')";
        // UPDATE PDCs
        $queries[] = "UPDATE demerit SET poster=$to WHERE poster=$from AND status NOT IN('REJECTED','CLOSED')";
        $queries[] = "UPDATE demerit SET initiator=$to WHERE initiator=$from AND status NOT IN('REJECTED','CLOSED')";
        // UPDATE BP
        $queries[] = "UPDATE cal_bp SET owner=$to WHERE owner=$from AND status<>'CLOSED'";
        // UPDATE MIS tts project
        $queries[] = "UPDATE mis_tts SET owner=$to WHERE owner=$from AND status<>'CLOSED'";
        // UPDATE GWF
        $queries[] = "UPDATE gwf SET assignor=$to WHERE assignor=$from AND status='OPEN'";
        // UPDATE mis TTS assignee and owner
        $queries[] = "UPDATE mis_tts SET owner=$to WHERE owner=$from AND status<>'CLOSED'";
        $queries[] = "UPDATE mis_tts SET assignee=$to WHERE assignee=$from AND status<>'CLOSED'";

        foreach ($queries as $query) {
            error_log("$query\n");
            $res .= tldUtils::sqlQuery($query);
            error_log(' #updated-' . TldDatabase::affectedRows() . "\n");
        }

        // Module functions
        $res .= tldDMS::transferOwnerFromTo($from, $to);
        $res .= tldDMS::transferApproverFromTo($from, $to);

        // SPECIAL CASES ---------------------------------------------------------->

        // GWF - Delete MEMBERS and log into the GWF
        $query = "SELECT * FROM mod_lists WHERE module='GWF' AND list_name='MEMBERS' AND value=$from";
        $rows = tldUtils::getSqlToAssocArray($query);
        foreach ($rows as $row) {
            $gwf = new tldGWF($row['parent_id']);
            $e = tldModMember::delete('GWF', $row['parent_id'], $from);
            if (!is_string($e)) {
                $gwf->addLogEntry($from, 'Members deleted - User account disabled');
            }
        }

        $query_del = [];
        // MIM NOT - Delete subscriptions
        $query_del[] = "DELETE FROM mim_not WHERE mim_not.poster=$from";

        foreach ($query_del as $query) {
            error_log("$query\n");
            $res .= tldUtils::sqlQuery($query);
            error_log(' #deleted-' . TldDatabase::affectedRows() . "\n");
        }

        if (empty($res)) {
            return;
        }
        return $res;
    }

    public function isHidden()
    {
        return $this->itsDetails['hidden'] == 1;
    }

    public function isDisabled()
    {
        return $this->itsDetails['disabled'] === 'Y';
    }

    /**
     * Return user email adressbook from mod_lists
     * @return array
     */
    public function getEmailAdressbook()
    {
        $constraint = ['module' => 'USER', 'parent_id' => $this->getID(), 'list_name' => 'email_adressbook'];
        $data = tldModList::byConstraints($constraint);
        return $data;
    }

    /**
     * Insert or update email adressbook to mod_lists
     * @param array
     * @return int
     */
    public function setEmailAdressbook($tab)
    {
        if (empty($tab)) {
            return;
        }
        $tab = tldUtils::cleanupFormInput($tab) + ['module' => 'USER', 'parent_id' => $this->getID(), 'list_name' => 'email_adressbook'];
        return tldModList::insert($tab);
    }

    private function addToken($a)
    {
        $fields = ['parent_id', 'dt', 'ip', 'env', 'token', 'jwt'];
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO people_tokens SET $SET";
        return tldUtils::sqlInsert($query);
    }

    public static function getTokenInfoByConstraints($a)
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        $query = "SELECT * FROM people_tokens WHERE $WHERE";
        return tldUtils::getSqlToAssocArray($query);
    }

    public function generateToken($jwt)
    {
        $userInfo = tldUtils::getUserAndRequestInfo();
        $token = md5((string)mt_rand() . $userInfo['user_env']);

        $a = [
            'parent_id' => $this->getID(),
            'dt' => time(),
            'ip' => $userInfo['user_ip'],
            'env' => $userInfo['user_env'],
            'token' => $token,
            'jwt' => $jwt,
        ];
        $e = $this->addToken($a);
        if (is_string($e)) {
            return false;
        }

        return $token;
    }

    public static function isTokenValid($token)
    {
        $userInfo = tldUtils::getUserAndRequestInfo();
        $tokenDetails = self::getTokenInfoByConstraints(['token' => $token]);
        // check if exists
        if (empty($tokenDetails[0])) {
            return false;
        }
        // check if same environement
        if ($tokenDetails[0]['env'] <> $userInfo['user_env']) {
            return 'Client environment is different';
        }
        // check if expired
        $now = time();
        $diffTime = $now - $tokenDetails[0]['dt'];
        if ($diffTime > 2) {
            return 'Allowed time to use token is expired';
        }

        $query = sprintf('UPDATE people_tokens SET jwt = null WHERE id = %d', $tokenDetails[0]['id']);
        tldUtils::sqlQuery($query);

        return $tokenDetails[0]['jwt'];
    }

    public static function byConstraints($a, $opts = '')
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
            $ORDERBY = 'ORDER BY fullname';
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

    public function getList()
    {
        return self::byConstraints('1=1');
    }

    public static function byIds($ids)
    {
        $WHERE = sprintf("WHERE people.hidden = 0 AND people.disabled = 'N' AND people.id IN (%s)", implode(', ', $ids));
        $ORDERBY = 'ORDER BY fullname';
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

    public static function byFunctions(array $functionIds): array
    {
        $WHERE = sprintf("WHERE people.hidden = 0 AND people.disabled = 'N' AND fct.id IN (%s)", implode(', ', $functionIds));
        $ORDERBY = 'ORDER BY fullname';
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

    public function getWarehousesByERP($erp)
    {
        static $warehouses = [];
        if (empty($warehouses)) {
            $query = <<<EOF
			SELECT
				RTRIM(pri.t_cwar) AS cwar
			FROM
				ttdinv016300 pri
			WHERE
				pri.t_prio=20 AND pri.t_ncmp =$erp
EOF;
            $wses = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
            foreach ($wses AS $w) {
                $warehouses[$w['cwar']] = $w['cwar'];
            }

        }
        return $warehouses;
    }

    /**
     * retrieves user-related sequences (new / update / delete)
     *
     * @return array
     */
    public function getSequencesHistory()
    {

        $query = <<<SQL
SELECT t.date, tpl.name
FROM tasks AS t
  LEFT JOIN cal_seq_tpl AS tpl ON tpl.id = t.tplno
WHERE t.seq = 'Y'
      AND t.parent_id = {$this->getID()}
      AND module = 'USER'
ORDER BY t.date DESC
SQL;

        $history = tldUtils::getSqlToAssocArray($query);

        return $history;
    }

}


/**
 *    returns a extranetUser class
 *
 *    Class for accessing and manipulating extranet user data ONLY
 *
 * @package UsersAndGroups
 */
class extranetUser extends tldGenericUser
{
    /** @var ePartsUser $eparts */
    public $eparts;
    public $is_login;

    /**
     * @param string userid or int id of extranet account
     */
    public function __construct($id)
    {
        if (is_numeric($id)) {
            $this->itsID = $id;
            $this->itsDetails = $this->getHeader();
        } else {
            $rows = $this->byUserid($id);
            $this->itsDetails = $rows[0];
            $this->itsID = $this->itsDetails['id'];
        }

        $this->itsUserid = $this->itsDetails['userid'];
        $this->customerName = $this->itsDetails['customer_name'];
    }

    /**
     * Get extranet user header
     * @return array
     */
    public function getHeader()
    {
        $query = <<<EOF
	        SELECT *, CONCAT(lastname, ', ', firstname) AS fullname
	        FROM extranet_users
	        WHERE id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function getUserid()
    {
        return $this->itsDetails['userid'];
    }

    public function getID()
    {
        return $this->itsDetails['id'];
    }

    public function getCountry()
    {
        return $this->itsDetails['country'];
    }

    public function getEmail()
    {
        return $this->itsDetails['email'];
    }

    public function getFullname()
    {
        return $this->itsDetails['fullname'];
    }

    public function getFirstname()
    {
        return $this->itsDetails['firstname'];
    }

    public function getLastname()
    {
        return $this->itsDetails['lastname'];
    }

    public function getCustomerName()
    {
        return $this->itsDetails['customer_name'];
    }

    public function getPhone()
    {
        return $this->itsDetails['phone'];
    }

    public function getType()
    {
        return $this->itsDetails['type'];
    }

    public function getTitle()
    {
        return $this->itsDetails['title'];
    }

    public function getPassword()
    {
        return stripslashes($this->itsDetails['password']);
    }

    public function getPreferedLanguage()
    {
        return strtolower($this->itsDetails['lang']);
    }

    public static function getLanguageList()
    {
        return ['en', 'fr', 'es', 'zh', 'ru', 'ja'];
    }

    public static function getLanguageListExtranet()
    {
        return ['fr' => '[FR] Fran&ccedil;ais', 'en' => '[EN] English', 'es' => '[ES] Espa&#241;ol', 'zh' => '[ZH] &#20013;&#25991;', 'ja' => '[JA] &#26085;&#26412;&#20154;'];
    }

    /**
     * Check if ext user empty
     * @return boolean
     */
    public function isEmpty(): bool
    {
        return empty($this->itsDetails);
    }

    public function getTableFields()
    {
        return [
            'type', 'salutation', 'lastname', 'firstname', 'division', 'department', 'title',
            'lang', 'phone', 'direct_phone', 'fax', 'mobile', 'home_phone', 'email', 'perms',
            'address', 'zip_code', 'country', 'shipping_address', 'counter', 'userid', 'password',
            'customer_name', 'cust_carrier_name', 'company_name', 'ship_acct_num', 'enable',
            'requestor_num', 'employe_num', 'note', 'default_erp', 'parts_location_id', 'erp', 'seqid',
        ];
    }

    /**
     * @param $vars
     * @param $poster
     * @param $form
     * The form passed can be a HTML Quickform Element or an array
     *
     * @deprecated
     */
    public static function createFromTOC($vars, $poster, $form)
    {
        if (empty($vars)) {
            return 'Data is missing';
        }

        $data = [];

        global $kernel;

        try {
            $client = $kernel->getContainer()->get(Client::class);
        } catch (Exception $e) {
            $data['error'][] = 'ERROR: Could not get API client.';
            return $data;
        }

        $xuProfilePayload = [
            'jobTitle' => mb_convert_encoding($vars['jobTitle'], 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8'),
            'division' => mb_convert_encoding($vars['division'], 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8'),
            'department' => mb_convert_encoding($vars['department'], 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8'),
        ];

        if (isset($vars['lang'])) {
            $xuProfilePayload['language'] = $vars['lang'];
        }

        try {
            $customer = $client->findOneBy('sales/customers', ['legacyId' => $vars['cuid']]);
            $xuProfilePayload['customer'] = $customer['@id'];
            $xuProfilePayload['company_name'] = $customer['name'];
        } catch (Exception $e) {
            $data['error'][] = 'ERROR: Could not get customer. Reason: ' . $e->getMessage();
            return $data;
        }


        try {
            $country = $client->find('countries', $vars['country']);
            $xuProfilePayload['country'] = $country['@id'];
        } catch (Exception $e) {
            $data['error'][] = 'ERROR: Could not get county. Reason: ' . $e->getMessage();
            return $data;
        }

        // Search if extranet user exist
        try {
            $extranetUser = $client->findOneBy('sales/extranet_users', ['email' => $vars['userid']]);
            $xuFound = true;
        } catch (\RangeException $e) {
            $xuFound = false;
        }

        if ($xuFound && isset($extranetUser)) {
            $data['error'][] = 'ERROR: Email already used for Extranet User #' . $extranetUser['id'];
            $editUrl = $kernel->getContainer()->get('router')->generate('sales_contact_show', [
                'id' => $extranetUser['id'],
            ], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_PATH);

            $data['error'][] = <<<EOF
<p><strong style="color: red">If you need to update the Extranet User, please use the new form</strong> (<a href="$editUrl">click here</a>).</p>
EOF;
            return $data;
        }

        $lastname = mb_convert_encoding($vars['lastname'], 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8');
        $firstname = mb_convert_encoding($vars['firstname'], 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8');
        $username = mb_convert_encoding($vars['userid'], 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8');

        $xuPayload = [
            'lastname' => $lastname,
            'firstname' => $firstname,
            'hidden' => false,
            'email' => $username,
            'extranetUserProfile' => $xuProfilePayload,
            'username' => $username,
            'phones' => [
                [
                    'type' => 'phone',
                    'number' => $vars['phone'],
                ],
            ],
        ];

        try {
            $extranetUser = $client->save('sales/extranet_users', $xuPayload);
        } catch (Exception $e) {
            $data['error'][] = $e->getMessage();
            return $data;
        }

        $data['success'][] = 'Extranet user #' . $extranetUser['legacyId'] . ' created successfully!';
        $data['xu_id'] = $extranetUser['legacyId'];
        $extUser = new extranetUser($extranetUser['legacyId']);
        $extUser->addLogEntry('Extranet User created from TOC');

        // Add CRT and ROLES ---------------->
        try {
            $crt = $client->findOneBy('sales/customer_relationship_teams', ['legacyId' => $vars['crtid']]);
        } catch (Exception $e) {
            $data['error'][] = 'ERROR: Could not get CRT. Reason: ' . $e->getMessage();
            return $data;
        }
        $crt_id = $crt['id'];
        // Add roles to contact
        try {
            $extranetUserGroup = $client->findOneBy('sales/extranet_user_groups', ['name' => 'role_ST']);
        } catch (RangeException $e) {
            $data['error'][] = 'ERROR: Could not get Extranet User Group. Reason: ' . $e->getMessage();
            return $data;
        }

        try {
            $client->findOneBy('sales/extranet_user_acls',
                [
                    'crt.erpLocation' => $crt['erpLocation']['@id'],
                    'crt.customer' => $crt['customer']['@id'],
                    'extranetUser' => $extranetUser['@id'],
                    'extranetUserGroup.name' => 'role_ST',
                ]
            );
            $addRole = false;
        } catch (RangeException $e) {
            // Add the role
            $addRole = true;
        }

        if ($addRole) {
            try {
                $client->save('sales/extranet_user_acls',
                    [
                        'extranetUserGroup' => $extranetUserGroup['@id'],
                        'crt' => $crt['@id'],
                        'extranetUser' => $extranetUser['@id'],
                    ]
                );
            } catch (ClientException $e) {
                $errors = json_decode($e->getResponse()->getBody()->getContents(), true);
                $data['error'][] = 'ERROR: Could not add role_ST for CRT #' . $crt['id'] . '. Reason: ' . $errors['hydra:description'];
                return $data;
            }
            $extUser->addLogEntry("Role role_ST added for CRT#$crt_id");
        }
        return $data;
    }

    public function getFavorites($fav_user = '')
    {
        $query = <<<EOF
		SELECT * FROM extranet_users_fav
		WHERE parent_id=$this->itsID
EOF;
        if ($fav_user) {
            $query .= " AND fav_user='$fav_user'";
        }
        return tldUtils::getSqlToAssocArray($query);
    }

    public function addFavorite($pn, $desc, $fav_user = '')
    {
        $pn = TldDatabase::escape($pn);
        $desc = TldDatabase::escape($desc);
        $fav_user = TldDatabase::escape($fav_user);
        $query = <<<EOF
        INSERT INTO extranet_users_fav
            SET
                parent_id=$this->itsID,
                fav_user='$fav_user',
                fav_pn='$pn',
                fav_dsca='$desc'
EOF;
        $e = tldUtils::sqlInsert($query);
        //if the insert is good then add the customer name link
        if (is_numeric($e)) {
            return $e;
        } else {
            return 'ERROR: Problem creating new favorite';
        }
    }

    public function delFavorite($id, $fav_user = '')
    {
        if (empty($this->itsID) || !is_numeric($id)) {
            return;
        }
        $query = <<<EOF
        DELETE
        FROM extranet_users_fav
        WHERE parent_id=$this->itsID
            AND id=$id
EOF;
        if ($fav_user) {
            $query .= " AND fav_user='$fav_user'";
        }
        return tldUtils::sqlExecute($query);
    }

    /**
     * Get role list public
     * @return array
     */
    public function getPublicRoleList()
    {
        return tldList::optionsByListNameAsListKeyListItem(
            'list.sales.customer.public.roles'
        );
    }

    /**
     * Get restricted role list for PARTS
     * @return array
     */
    public function getRestrictedPartsRoleList()
    {
        return tldList::optionsByListNameAsListKeyListItem(
            'list.sales.customer.restricted.parts.roles'
        );
    }

    /**
     * Get restricted role list for SERVICE
     * @return array
     */
    public function getRestrictedServiceRoleList()
    {
        return tldList::optionsByListNameAsListKeyListItem(
            'list.sales.customer.restricted.service.roles'
        );
    }

    /**
     * Function to get list of CRT and roles
     * @param string $format
     * @return array
     */
    public function getCRTnRoles($format = null, bool $displayEmail = true)
    {
        if (empty($this->itsID)) {
            return 'Not object context';
        }
        $emailRequest = $displayEmail ? ", ' (', people.email, ')'" : "";
        $query = <<<EOF
			SELECT crt.*, crt.id AS crt_id, roles.*,
				cust.customer_name, roles.id AS role_id,
				(SELECT CONCAT(people.firstname,' ',people.lastname$emailRequest)
                	FROM people WHERE crt.sales_rep_id=people.id
                ) AS sales_rep,
				(SELECT CONCAT(people.firstname,' ',people.lastname$emailRequest)
                	FROM people WHERE crt.parts_rep_id=people.id
                ) AS parts_rep,
                (SELECT CONCAT(people.firstname,' ',people.lastname$emailRequest)
                	FROM people WHERE crt.services_rep_id=people.id
                ) AS services_rep,
				(SELECT location FROM locations
					WHERE crt.parts_location_id=locations.id
				) AS parts_location,
				(SELECT location FROM locations
					WHERE crt.services_location_id=locations.id
				) AS services_location,
				(SELECT locations.erp FROM locations
    				WHERE locations.id=crt.erp_location_id
    			) AS erp,
    			(SELECT locations.location FROM locations
    				WHERE locations.id=crt.erp_location_id
    			) AS erp_location
			FROM extranet_users_roles AS roles
				LEFT JOIN customers_crt AS crt ON crt.id=roles.crt_id
				LEFT JOIN customers AS cust ON cust.id=crt.customer_id
			WHERE roles.parent_id={$this->getID()}
			ORDER BY cust.customer_name,erp_location
EOF;
        switch ($format) {
            case 'smartyOptions':
                $rows = tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['customer_id', 'customer_name']);
                break;
            case 'smartyOptionsCRT':
                $rows = tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['crt_id', 'customer_name']);
                break;
            default:
                $rows = tldUtils::getSqlToAssocArray($query);
                break;
        }
        return $rows;
    }

    /**
     * @throws Exception
     * @deprecated
     */
    public function getCRTCustomerName($format = null)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @throws Exception
     * @deprecated
     */
    public function disable()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @throws Exception
     * @deprecated
     */
    public function deleteRoles()
    {
        throw new Exception('This is no longer used');
    }

    public function getRoles()
    {
        if (empty($this->itsID)) {
            return 'Not object context';
        }
        $query = <<<EOF
            SELECT *
            FROM extranet_users_roles AS roles
            WHERE roles.parent_id={$this->getID()}
            GROUP BY role
EOF;

        return $rows = tldUtils::getSqlToAssocArray($query);
    }

    /**
     * @throws Exception
     * @deprecated
     */
    public function getRolesPerCRT($id, $crt_id)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @throws Exception
     * @deprecated
     */
    public function insertRole($data)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @throws Exception
     * @deprecated
     */
    public function updateRole($id, $data)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @throws Exception
     * @deprecated
     */
    public function getRoleById($rid)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @throws Exception
     * @deprecated
     */
    public function delRole($gid)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * Checks whether user in a particular group
     *
     * - Checks if in group
     * - Returns false if not in group
     *
     * @param $options array set "return_rows" to true if you want results return
     */
    public function isInGroup($group, $options = []): bool|array
    {
        if (empty($this->itsID)) {
            return 'Not in object context';
        }
        $groups = implode("','", (array)$group);
        $query = <<<EOF
		SELECT *
		FROM extranet_users_roles
		WHERE parent_id={$this->itsID} AND role IN('$groups')
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);
        if (count($rows)) {
            if (array_key_exists('return_rows', $options)) {
                return $rows;
            } else {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has the role for the specific SSO+Customer
     * @param $ssoid int
     * @param $cuid int
     * @param $role string
     * @return boolean
     */
    public function isInSSOCustomerRole($ssoid, $cuid, $role)
    {
        $query = <<<EOF
SELECT
    roles.*
FROM
    extranet_users_roles AS roles
    LEFT JOIN customers_crt AS crt ON crt.id=roles.crt_id
    LEFT JOIN extranet_users AS users ON roles.parent_id = users.id
WHERE
    roles.parent_id=$this->itsID
    AND crt.erp_location_id=$ssoid
    AND crt.customer_id=$cuid
    AND roles.role LIKE '$role'
    AND users.enable LIKE "Y"
EOF;
        $result = tldUtils::getSqlToAssocArray($query);
        return count($result) ? true : false;
    }

    public function logout()
    {
        tldUtils::log_event('PARTNERS Logout ' . $this->getUserid());
    }

    public function isArchived()
    {
        $id = $this->getID();
        $query = <<<EOF
        SELECT * FROM extranet_users
        WHERE archived=1
        AND id='$id'
EOF;
        return count(tldUtils::getSqlToAssocArray($query)) == 0 ? false : true;
    }

    public function isEnable()
    {
        $id = $this->getID();
        $query = <<<EOF
        SELECT id FROM extranet_users
        WHERE enable='Y'
        AND id='$id'
EOF;
        return count(tldUtils::getSqlToAssocArray($query)) == 0 ? false : true;
    }

    /**
     * Get linked log entries
     * @return array
     */
    public function getLog()
    {
        return tldModLog::byParent($this->itsID, 'XU');
    }

    /**
     * Add a comment to the log
     * @param array Array structure containing 'poster' and 'comment'
     * @return boolean
     */
    public function addLogEntry($comment)
    {
        if (empty($this->itsID)) {
            return;
        }

        global $kernel;

        $client = $kernel->getContainer()->get(Client::class);
        $extranetUser = $client->findOneBy('sales/extranet_users', ['legacyId' => $this->itsID]);

        try {
            $client->post('comments', [
                'json' => [
                    'resource' => $extranetUser['@id'],
                    'message' => $comment,
                ],
            ]);
        } catch (ClientException $e) {
            $errors = json_decode($e->getResponse()->getBody()->getContents(), true);
            return 'ERROR: Could not add a log. Reason: ' . $errors['hydra:description'];
        }
    }

    /**
     * Get list of extranet users
     * @param string $format
     * @return array
     */
    public static function getList($format = '')
    {
        $query = "SELECT *,
            CONCAT(customer_name, ' -->> ', lastname, ', ', firstname) AS fullname
            FROM extranet_users
            ORDER BY fullname";
        switch ($format) {
            case 'smartyOptions':
                $result = tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['id', 'fullname']);
                break;
            default:
                $result = tldUtils::getSqlToAssocArray($query);
                break;
        }
        return $result;
    }

    /**
     * @throws Exception
     * @deprecated
     */
    public static function byLatest($num = 10)
    {
        throw new Exception('This is no longer used');
    }

    public static function byERPCuno($erp, $cuno)
    {
        return self::byConstraints([
            'crt.cuno' => $cuno,
            'crt.erp_location_id' => tldLocation::getIDByERP($erp),
        ]);
    }

    /**
     * @throws Exception
     * @deprecated
     */
    public function byERPCunoRole($erp, $cuno, $role)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * Get list of extranet user by SSO/eCustomer/Role
     * @param int $ssoid
     * @param int $cuid
     * @param string $role
     * @return array
     */
    public static function bySSOCustomerRole($ssoid, $cuid, $role)
    {
        return self::byConstraints([
            'roles.role' => $role,
            'cust.id' => $cuid,
            'crt.erp_location_id' => $ssoid,
        ]);
    }

    /**
     * Get list by customer name
     * @param $customer string
     * @param $option string
     * @return array of rows
     */
    public static function byCustomerName($customer, $options = '')
    {
        $customer = TldDatabase::escape($customer);
        $a = " cust.customer_name='$customer' AND user.archived = 0";
        return self::byConstraints($a, $options);
    }


    public static function archivedByCustomerName($customer, $options = '')
    {
        $customer = TldDatabase::escape($customer);
        $a = " cust.customer_name='$customer' AND user.archived = 1";
        return self::byConstraints($a, $options);
    }

    /**
     * Get list by customer ID
     * @param $cuids array
     * @param $option string
     * @return array of rows
     */
    public static function byCustomerID($cuids, $option = '', $showHidden = true)
    {
        $a = "";
        if (true !== $showHidden) {
            $a = ' user.hidden <> 1';

            if (!empty($cuids)) {
                $a .= ' AND ';
            }
        }

        foreach ($cuids as $key => $cuid) {
            if ($key === 0) {
                $a .= "(";
            }

            $cuid = TldDatabase::escape($cuid);
            $a .= "cust.id = '$cuid'";

            if ($key !== count($cuids) - 1) {
                $a .= ' OR ';
            }

            if ($key === count($cuids) - 1) {
                $a .= ')';
            }

        }

        return self::byConstraints($a, $option);
    }

    /**
     * Get list by customer name with structure ID -> fullname
     * @param $customer string
     * @return array of rows
     */
    public static function optionsbyCustomerNameAsIDFullname($customer)
    {
        $data = self::byCustomerName($customer);
        return array_column($data, 'fullname', 'id');
    }

    /**
     * Get list by customer name with structure ID -> fullname
     * @param $customer string
     * @return array of rows
     */
    public static function optionsbyCustomerIDAsIDFullname($cuid)
    {
        $data = self::byCustomerID([$cuid]);
        return array_column($data, 'fullname', 'id');
    }

    /**
     * Get list by customer name with structure ID -> fullname for multiple customer
     */
    public static function optionsbyCustomerIDsAsIDFullnameAndEmail(array $cuid): array
    {
        if ([] == $cuid) {
            return [];
        }

        $cuid = implode(',', $cuid);

        $data = array_map(static function (array $customer) {
            $customer['select'] = "{$customer['fullname']} ({$customer['email']})";

            return $customer;
        }, self::byConstraints( "cust.id IN ($cuid)"));

        return array_column($data, 'select', 'id');
    }

    /**
     * Get list by account unused
     * @param $option string
     * @return array of rows
     */
    public static function byUnused($options = '')
    {
        $a = ' user.counter=0 OR DATEDIFF(NOW(), user.last)>90 ';
        return self::byConstraints($a, $options);
    }

    public static function byArchived($options = '')
    {
        $a = ' user.archived=1  ';
        return self::byConstraints($a, $options);
    }

    /**
     * Get list of users by userid
     * @return array of rows
     */
    public function byUserid($userid, $options = '')
    {
        $userid = TldDatabase::escape($userid);
        $a = " user.userid LIKE '$userid' ";
        return self::byConstraints($a, $options);
    }

    /**
     * Get user list by constraint
     * @param $a array|string
     * @param $options string
     * @return array
     */
    public static function byConstraints($a, $options = '')
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }

        if (!empty($WHERE)) {
            $WHERE = " WHERE $WHERE ";
        }

        $query = <<<EOF
	    	SELECT user.*, cust.customer_name, roles.role, user.customer_name AS cust_name_local,
	    		CONCAT(lastname, ', ', firstname) AS fullname,
				GROUP_CONCAT(roles.role SEPARATOR ', ') as groups,
				IF(GROUP_CONCAT(roles.role SEPARATOR ', ') LIKE '%acl_EPARTS%', 'Y', 'N') as eparts
	        FROM extranet_users as user
	        	LEFT JOIN extranet_users_roles AS roles ON roles.parent_id=user.id
	        	LEFT JOIN customers_crt AS crt ON roles.crt_id=crt.id
	        	LEFT JOIN customers AS cust ON crt.customer_id=cust.id
	        $WHERE
	        GROUP BY user.id
	        ORDER BY fullname,cust.customer_name,user.userid
EOF;
        if ('smartyOptions' === $options) {
            return tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['id', 'fullname']);
        }

        return tldUtils::getSqlToAssocArray($query);
    }

    public function newEParts()
    {
        $this->eparts = new ePartsUser($this);
    }
}

// Class ePartsUser is not an extended tldGenericUser class
// It is only set and accessed from extranetUser object and
// only lives in the extranet user's session
class ePartsUser
{
    public $extranet;
    public $itsID;
    public $parentID;
    public $itsDetails;
    public $cart;
    public $ERP, $CUNO, $CDEL, $CRT, $CUR;

    public function __construct(extranetUser &$user)
    {
        $this->extranet =& $user;
        $this->parentID = $this->extranet->getID();
        $this->itsDetails = $this->getHeader();
        $this->itsID = $this->itsDetails['id'];
        $this->cart = new tldEpartsCart($this);
        $this->_setDefaults();
    }

    protected function _setDefaults()
    {
        $rows = $this->extranet->getCRTnRoles();
        foreach ($rows AS $crt) {
            if ($crt['role'] === 'acl_EPARTS') {
                $this->ERP = $crt['erp'];
                $this->CUNO = $crt['cuno'];
                $this->CDEL = $crt['cdel'];
                $this->CRT = $crt;
                break;
            }
        }
        if ($this->ERP) {
            $query = <<<EOF
			SELECT com.t_ccur
			FROM ttccom010{$this->ERP} com
			WHERE com.t_cuno='{$this->CUNO}'
EOF;
            $res = tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
            if (array_key_exists('t_ccur', $res)){
                $this->CUR = $res['t_ccur'];
            }
        }
    }

    public function checkRequirements()
    {
        if ($this->isEmpty()) {
            return 'User is not initiated in eParts';
        }
        if (!$this->extranet->isInGroup('acl_EPARTS')) {
            return 'User does not have proper ACL role';
        }
        if ($this->extranet->getType() === 'PUNCHOUT') {
            return 'User is punchout type';
        }
        if (!$this->ERP) {
            return 'ERP is not set';
        }
        if (!$this->CUNO) {
            return 'CUNO is not set';
        }
        if (!$this->CUR) {
            return 'Default currency is not set';
        }
        #if(!$this->CDEL) return 'CDEL is not set;
        $query = <<<EOF
		SELECT COUNT(*) AS cnt
		FROM ttccom010{$this->ERP} com
		WHERE com.t_cuno='{$this->CUNO}'
EOF;
        $res = tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
        if (!$res['cnt']) {
            return "Company {$this->CUNO} does not exist in ERP {$this->ERP}";
        }
        if (!count($this->getDeliveryAddressArray())) {
            return 'There are no delivery addresses for this customer';
        }
        if (!count($this->getPostalAddressArray())) {
            return 'There are no billing addresses for this customer';
        }
        if (!$this->extranet->getEmail()) {
            return 'Email address is not set';
        }
        return true;
    }

    public function refresh()
    {
        $this->itsDetails = $this->getHeader();
    }

    public function getID()
    {
        return $this->itsID;
    }

    public function getParentID()
    {
        return $this->parentID;
    }

    /**
     * Get eParts user header
     * @return array
     */
    public function getHeader()
    {
        $query = <<<EOF
		SELECT *
		FROM eparts_users
		WHERE parent_id={$this->parentID}
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function isEmpty(): bool
    {
        return empty($this->itsDetails);
    }

    public static function insert($parent_id, $vals = [])
    {
        $exu = new extranetUser($parent_id);
        if ($exu->isEmpty()) {
            return 'Invalid extranet user';
        }
        $query = <<<EOF
		INSERT INTO eparts_users
		SET
			parent_id = $parent_id
EOF;
        return tldUtils::sqlInsert($query);
    }

    public function getDeliveryAddressArray()
    {
        if (!is_array($this->itsDeliveryAddressArray)) {
            $this->itsDeliveryAddressArray = [];
            $baanObj = new tldBaanERP($this->ERP);
            $list = $baanObj->getDeliveryAddressData($this->CUNO);
            foreach ($list AS $addr) {
                foreach (['e', 'c', 'a'] AS $k) {
                    $val = iconv('CP936', 'UTF-8', trim($addr["t_nam$k"]));
                    if (!empty($val)) {
                        $addr['array'][] = $val;
                    }
                }
                if (!empty($addr['array'])) {
                    $this->itsDeliveryAddressArray[$addr['t_cdel']] = implode(', ', $addr['array']);
                }
            }
            asort($this->itsDeliveryAddressArray);
        }
        return $this->itsDeliveryAddressArray;
    }

    public function getDeliveryAddress($key = '')
    {
        $address_book = $this->getDeliveryAddressArray();
        if (empty($key)) {
            $key = $this->itsDetails['cdel'];
        }
        return $address_book[str_pad($key, 3)];
    }

    public function setDeliveryAddress($code)
    {
        $res = $this->update([
            'cdel' => $code,
        ]);
        return $res;
    }

    public function getPostalAddressArray()
    {
        if (!is_array($this->itsPostalAddressArray)) {
            $this->itsPostalAddressArray = [];
            $baanObj = new tldBaanERP($this->ERP);
            $list = $baanObj->getPostalAddressData($this->CUNO);

            foreach ($list AS $addr) {
                foreach (['e', 'c', 'a'] AS $k) {
                    $val = iconv('CP936', 'UTF-8', trim($addr["t_nam$k"]));
                    if (!empty($val)) {
                        $addr['array'][] = $val;
                    }
                }
                if (!empty($addr['array'])) {
                    $this->itsPostalAddressArray[$addr['t_ccor']] = implode(', ', $addr['array']);
                }
            }
            asort($this->itsPostalAddressArray);
        }
        return $this->itsPostalAddressArray;
    }

    public function getPostalAddress($key = '')
    {
        $address_book = $this->getPostalAddressArray();
        if (empty($key)) {
            $key = $this->itsDetails['ccor'];
        }
        return $address_book[str_pad($key, 3)];
    }

    public function setPostalAddress($code)
    {
        $res = $this->update([
            'ccor' => $code,
        ]);
        return $res;
    }

    public function getForwardingAgentsArray()
    {
        if (!is_array($this->itsForwardingAgentsArray)) {
            $this->itsForwardingAgentsArray = [];
            $query = <<<EOF
			SELECT
				RTRIM(t_cfrw) AS t_cfrw,
				RTRIM(t_dsca) AS t_dsca
			FROM
				ttcmcs080300
			ORDER BY
				t_dsca
EOF;
            $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
            foreach ($rows AS $row) {
                $this->itsForwardingAgentsArray[$row['cour']] = $row['t_dsca'];
            }
            asort($this->itsForwardingAgentsArray);
        }
        return $this->itsForwardingAgentsArray;
    }

    public function getForwardingAgent($key = '')
    {
        $carriers = $this->getForwardingAgentsArray();
        if (empty($key)) {
            $key = $this->itsDetails['cour'];
        }
        return $carriers[$key];
    }

    public function update($p)
    {
        if (empty($p)) {
            return;
        }
        $fields = [
            'cdel',
            'ccor',
            'cour',
            'csvc',
            'actn',
            'current_cart',
        ];
        $SET = [];
        foreach ($fields AS $field) {
            if (isset($p[$field])) {
                $SET[] = "$field='{$p[$field]}'";
            }
        }
        $SET = implode(', ', $SET);
        $query = <<<EOF
		UPDATE eparts_users
		SET $SET
		WHERE id = {$this->itsID}
EOF;
        return tldUtils::sqlQuery($query);
    }

    public function getInvDetails($pn)
    {
        $warehouses = $this->getAvailableWarehouses();
        if (empty($pn)) {
            return [];
        }
        $details = [];
        $inv = tldERP::getERPOb($this->ERP)->getInvData($pn);
        foreach ($inv AS $v) {
            if (in_array($v['t_cwar'], $warehouses[$this->ERP])) {
                $details['stoc'] += $v['stoc'];
                $details['ordr'] += $v['ordr'];
                $details['allo'] += $v['allo'];
                $details['reop'] += $v['reop'];
                $details['avail'] += $v['avail'];
                $details['econ'] += $v['econ'];
                $details['sfst'] += $v['sfst'];
            }
        }
        return $details;
    }

    public function getPartDetails($pn)
    {
        if (empty($pn)) {
            return [];
        }
        $details = [];
        $erp = tldERP::getERPOb($this->ERP);
        $itmData[] = $erp->getItemData($pn);
        if (!empty($itmData)) {
            foreach ($itmData AS $itm) {
                if (!empty($itm)) {
                    $details[] = array_map('trim', $itm);
                }
            }
        }
        return $details;
    }

    public function getAvailableWarehouses()
    {
        static $warehouses = [];
        if (empty($warehouses)) {
            $query = <<<EOF
			SELECT
				RTRIM(pri.t_cwar) AS cwar,
				RTRIM(pri.t_ncmp) AS ncmp
			FROM
				ttdinv016300 pri
			WHERE
				pri.t_prio=10
EOF;
            $wses = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
            foreach ($wses AS $w) {
                $warehouses[$w['ncmp']][] = $w['cwar'];
            }
        }
        return $warehouses;
    }

    /**
     * @return float
     */
    public function getEpartsCommission()
    {
        $query = <<<SQL
		SELECT * FROM eparts_commissions
		WHERE t_cuno = '$this->CUNO'
		AND erp = $this->ERP;
SQL;
        $commission = tldUtils::getSqlRowToAssocArray($query);

        return empty($commission) ? 0 : floatval($commission['commission']);
    }

    public function getCart()
    {
        return $this->cart;
    }
}

/**
 *    returns a vendorUser class
 *
 *    Class for accessing and manipulating TLD Vendor data ONLY
 *
 * @package UsersAndGroups
 */
class vendorUser extends tldGenericUser
{

    public $PASSWORD_CHANGE_INTERVAL = 180;
    public $itsViewableFields = [
        'userid' => 'Userid',
        'email' => 'Email',
        'erpcompany' => 'ERP Company',
        'vendorid' => 'ERP Vendor ID',
        'company' => 'Company Name',
        'address' => 'Address',
    ];

    public function __construct($userid)
    {
        $this->itsDetails = $this->getHeader($userid);
        $this->itsID = $this->itsDetails['id'];
    }

    public function getHeader($userid = null)
    {
        if (empty($userid)) {
            return;
        }
        if (is_numeric($userid)) {
            $WHERE = "id=$userid";
        } else {
            $userid = TldDatabase::escape($userid);
            $WHERE = "userid='$userid'";
        }
        $query = <<<EOF
SELECT
    *,
    CONCAT(firstname,' ',lastname) AS fullname
FROM
    vendors
WHERE
    $WHERE
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function getDetails()
    {
        return $this->itsDetails;
    }

    public function getID()
    {
        return $this->itsDetails['id'];
    }

    public function getVendorIDS()
    {
        $query = <<<EOF
			SELECT * FROM vendors_suno
			WHERE parent_id=$this->itsID
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public function doNotSendPoAnswer($erp, $suno)
    {
        $query = <<<EOF
            SELECT groups.*, suno.erp, suno.t_suno
            FROM vendors_groups AS groups
            LEFT JOIN vendors_suno AS suno ON suno.id=groups.parent_id
            WHERE suno.parent_id = $this->itsID AND suno.t_suno = '$suno' AND suno.erp = '$erp' AND groups.groupid = 'fl_NO_PO_ANSWER'
EOF;
        $result = tldUtils::getSqlToAssocArray($query);
        if ($result[0]['groupid']) {
            return 1;
        } else {
            return 0;
        }

    }

    public function getVendorID()
    {
        return $this->itsDetails['vendorid'];
    }

    public function getTLDRepID()
    {
        return $this->itsDetails['tld_rep_id'];
    }

    public function getUserid()
    {
        return $this->itsDetails['userid'];
    }

    public function getEmail()
    {
        return $this->itsDetails['email'];
    }

    public function getERP()
    {
        return $this->itsDetails['erpcompany'];
    }

    public function getPassword()
    {
        return $this->itsDetails['password'];
    }

    public function getFullname()
    {
        return $this->itsDetails['fullname'];
    }

    public function getFirstname()
    {
        return $this->itsDetails['firstname'];
    }

    public function getLastname()
    {
        return $this->itsDetails['lastname'];
    }

    /**
     * update password in database and record the old password
     *
     * @return string text error
     */
    public function updatePassword($old, $new)
    {
        $id = $this->getID();
        if (empty($id)) {
            return 'ERROR: No userid set';
        }
        $query = "UPDATE vendors SET password='$new' " .
            "WHERE id=$id AND password='$old'";
        $updateError = tldUtils::sqlQuery($query);
        if (empty($updateError)) {
            mail($this->getEmail(), 'TLD eVendor Password change',
                "This is to confirm that your TLD eVendor password has just been changed.\n\n",
                "From: noreply@tld-gse.com\nReply-To: noreply@tld-gse.com\nX-Mailer: PHP/" .
                phpversion());
        }
        $query = <<<EOF
			INSERT INTO vendors_pwhist
			SET parent_id=$id, password='$old', change_date=now()
EOF;
        $error = tldUtils::sqlInsert($query);
        if (!is_int($error)) {
            $error = 'ERROR: Problem updating password history';
        }
        return $error;
    }

    /**
     * check whether password has been used before in last 5 days/changes
     *
     * @return bool
     */
    public function passwordUsedBefore($pass)
    {
        $id = $this->itsDetails['id'];
        $query = <<<EOF
			SELECT password FROM vendors_pwhist
			WHERE parent_id=$id
			GROUP BY change_date
			ORDER BY id DESC
			LIMIT 5
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);
        if (count($rows)) {
            $i = 0;
            foreach ($rows as $row) {
                if ($i == 5) {
                    return false;
                }
                if ($row['password'] == $pass) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * log user in and set timestamps
     *
     */
    public function login()
    {
        $id = $this->getId();
        $query = <<<EOF
		UPDATE vendors
		SET login_counter=login_counter+1,
			last=current_timestamp(),
			login=current_timestamp()
		WHERE id='$id'
EOF;
        if (tldUtils::sqlQuery($query) <> '') {
            tldUtils::log_event('Counter update error:' . TldDatabase::error());
        }

        tldUtils::log_event('PARTNERS Login ' . $this->getUserid());

        $query = "
		INSERT INTO vendors_login_logs
		SET
		user_id=$id,
		ip_address='" . TldDatabase::escape(tldUtils::getClientIp()) . "',
		dt=NOW(),
		user_agent='" . TldDatabase::escape($_SERVER['HTTP_USER_AGENT']) . "',
		http_referer='" . TldDatabase::escape($_SERVER['HTTP_REFERER']) . "',
		portal=''
        ";
        tldUtils::sqlQuery($query);
    }

    public function logout()
    {
        $id = $this->getId();
        $query = <<<EOF
		UPDATE vendors
		SET login=''
		WHERE id='$id'
EOF;
        if (tldUtils::sqlQuery($query) <> '') {
            tldUtils::log_event('Logout error:' . TldDatabase::error());
        }
        tldUtils::log_event('PARTNERS Logout ' . $this->getUserid());
    }

    public function isEnable()
    {
        $id = $this->getID();
        $query = <<<EOF
        SELECT id FROM vendors
        WHERE enable='Y'
        AND id='$id'
EOF;
        return count(tldUtils::getSqlToAssocArray($query)) == 0 ? false : true;
    }

    public function incCounter()
    {
        if (empty($this->itsID)) {
            return;
        }
        $userid = TldDatabase::escape($this->getUserid());
        $id = TldDatabase::escape($this->itsID);
        $query = <<<EOF
			UPDATE vendors
			SET login_counter=login_counter+1
			WHERE userid='$userid' AND id=$id
EOF;
        return tldUtils::sqlQuery($query);
    }
}

/**
 *    returns a tldPartsUser class
 *
 *    Class for accessing and manipulating TLD Parts website user data ONLY
 *
 * @package UsersAndGroups
 */
class tldPartsUser extends tldGenericUser
{
    public $itsDetails;
    public $itsViewableFields = [
        'lastname' => 'Lastname',
        'firstname' => 'Firstname',
        'division' => 'Division',
        'department' => 'Department',
        'title' => 'Title',
        'shipping_address' => 'Shipping Address',
    ];

    public function __construct($uid)
    {
        $query = "select * from extranet_users where userid='$uid'";
        $this->setUserDetails($query);
    }

    public function getUserid()
    {
        return $this->itsDetails['userid'];
    }

    public function getFullname()
    {
        return $this->itsDetails['firstname'] . ' ' . strtoupper($this->itsDetails['lastname']);
    }

    public function getFirstname()
    {
        return $this->itsDetails['firstname'];
    }

    public function getLastname()
    {
        return $this->itsDetails['lastname'];
    }

    public function isAdmin(): bool
    {
        return $this->itsDetails['perms'] === 'admin';
    }

    public function getDefaultLocation()
    {
        return $this->itsDetails['default_erp'];
    }

    public function getUserLevel()
    {
        return $this->itsDetails['perms'];
    }

    public function getItsGetPicFilePath()
    {
        return 'https://www.tld-gse.com/shared/get_pic.php';
    }

    //getFullname method from tldGenericUser object

    public function getUsername()
    {
        return $this->itsDetails['userid'];
    }

    public function getPassword()
    {
        return $this->itsDetails['password'];
    }

    public function getAddress()
    {
        return $this->itsDetails['address'] ? $this->itsDetails['address'] : 'No address given';
    }

    public function getShippingAddress()
    {
        return $this->itsDetails['shipping_address'] ? $this->itsDetails['shipping_address'] : 'No address given';
    }

    public function getBillingAddress()
    {
        $result = '';
        if ($this->itsDetails['address']) {
            $result .= $this->itsDetails['address'] . '<br>';
        } else {
            $result .= 'No Billing address set.<br>';
        }
        $result .= $this->itsDetails['email'];
        return $result;
    }

    public function getAuthentication($username, $password)
    {
        if (empty($username) || empty($password)) {
            return;
        }
        return ($username == $this->getUsername() && $password == $this->getPassword());
    }
}

/**
 *    returns a tldPartsUserAdmin class
 *
 *    Class for accessing and manipulating TLD Parts website ADMIN user data ONLY
 *
 * @package UsersAndGroups
 */
class tldPartsUserAdmin extends tldPartsUser
{
}

/**
 *    Class for accessing TLD directory information
 * @package UsersAndGroups
 */
class tldDirectory
{

    /**
     * Get SELECT mysql statement
     * @return string
     */
    public static function getSELECT(): string
    {
        return <<<EOF
SELECT
    people.*,
    CONCAT(people.lastname,', ',people.firstname, ' (', people.email, ')') AS fullname,
    (SELECT CONCAT(sup.lastname,', ',sup.firstname, ' (', sup.email, ')')
        FROM people AS sup WHERE sup.id=people.reports_to
    ) AS reports_to_fullname,
    regions.division AS division,
    bu.location AS location,
    dpt.dpt AS department,
    fct.dsc AS tld_function,
    sub_divisions.id AS subdivision_id,
    divisions.id AS division_id,
    regions.id AS region_id
EOF;
    }

    /**
     * Get FROM mysql statement
     * @return string
     */
    public static function getFROM(): string
    {
        return <<<EOF
FROM
    people
    LEFT JOIN tld_regions AS regions ON regions.id=people.div_id
    LEFT JOIN tld_sub_divisions AS sub_divisions ON sub_divisions.id=regions.sub_division_id
    LEFT JOIN tld_divisions AS divisions ON divisions.id=sub_divisions.division_id
    LEFT JOIN locations AS bu ON bu.id=people.bu_id
    LEFT JOIN tld_departments AS dpt ON dpt.id=people.dpt_id
    LEFT JOIN tld_functions AS fct ON fct.id=people.fct_id
EOF;
    }

    /**
     * Get Directory user by constraints
     * @param array $a constraints
     * @param array $opt options
     * @return array of rows
     */
    public static function byConstraints($a, $opt = [])
    {
        if (is_array($a)) {
            $HAVING = 'HAVING ' . tldUtils::constructWhere($a);
        } else {
            $HAVING = "HAVING $a";
        }
        // Look for options
        if (!empty($opt['orderBy'])) {
            $ORDERBY = 'ORDER BY ' . $opt['orderBy'];
        } else {
            $ORDERBY = 'ORDER BY fullname';
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

    /**
     * Get list of NOT HIDDEN directory users
     * @param $option string (used for smarty option -> return ID + fullname)
     * @return array of rows
     */
    public static function getUserlist($option = '', bool $displayEmail = true)
    {
        $emailRequest = $displayEmail ? ", ' (', people.email, ')'" : "";
        $query = <<<EOF
SELECT id, CONCAT(people.lastname,', ',people.firstname$emailRequest) AS fullname
FROM people
WHERE hidden=0 AND disabled='N'
ORDER BY fullname
EOF;
        return tldUtils::getSqlToAssocArray($query, $option, ['id', 'fullname']);
    }

    /**
     * Get list of NOT HIDDEN directory users
     * @param $option string (used for smarty option -> return ID + fullname)
     * @return array of rows
     */
    public static function getUserlistByBusinessUnitPosition($businessUnitId, $positionId, $option = '')
    {
        $query = <<<EOF
SELECT id, CONCAT(lastname,', ',firstname) AS fullname
FROM people
WHERE hidden=0 AND disabled='N'
AND bu_id = $businessUnitId
AND fct_id = $positionId
ORDER BY fullname
EOF;
        return tldUtils::getSqlToAssocArray($query, $option, ['id', 'fullname']);
    }

    public static function getUserIDlist($option = '')
    {
        $query = <<<EOF
SELECT id, CONCAT(lastname,', ',firstname,'(',id,')') AS fullname
FROM people
WHERE hidden=0 AND disabled='N'
ORDER BY fullname
EOF;
        return tldUtils::getSqlToAssocArray($query, $option, ['id', 'fullname']);
    }

    public static function getUserlistByERP($buid = '', $option = '')
    {
        $WHERE = !empty($buid) ? "AND bu_id = $buid" : '';

        $queryuser = <<<EOF
SELECT id, CONCAT(lastname,', ',firstname) AS fullname
FROM people
WHERE hidden=0 AND disabled='N'
$WHERE
ORDER BY fullname
EOF;
        $retuser = tldUtils::getSqlToAssocArray($queryuser, $option, ['id', 'fullname']);
        $queryseq = <<<EOF
SELECT
  people.id,CONCAT(people.lastname,', ',people.firstname, ' [USERSEQ#',tasks.id, ']') AS fullname
FROM tasks
  LEFT JOIN cal_seq_tpl AS tpl ON tasks.tplno=tpl.id
  LEFT JOIN people ON people.id=tasks.parent_id
WHERE
  tasks.tplno IN(23,45, 141) AND (people.hidden =1 OR people.disabled ='Y') AND tasks.status LIKE 'OPEN'
ORDER BY fullname
EOF;

        $retseq = tldUtils::getSqlToAssocArray($queryseq, $option, ['id', 'fullname']);
        return $retuser + $retseq;
    }

    /**
     * Search in directory
     * @param string $target
     * @return array
     */
    public static function search($target)
    {
        $a = <<<EOF
lastname LIKE '$target'
OR firstname LIKE '$target'
OR division LIKE '$target'
OR department LIKE '$target'
OR title LIKE '$target'
OR email LIKE '$target'
EOF;
        return self::byConstraints($a);
    }

}


class tldJuridicalEntity
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

    public function getName()
    {
        return $this->itsHeader['name'];
    }

    public function getAddress()
    {
        return $this->itsHeader['address'];
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public function getHeader()
    {
        $query = 'SELECT * FROM tld_juridical_locations WHERE id=' . $this->itsID;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    public static function byConstraints($a, $opt = '')
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        $query = "SELECT * FROM tld_juridical_locations WHERE $WHERE ORDER BY name";
        return tldUtils::getSqlToAssocArray($query);
    }

    public function byLocationERP($erp)
    {
        switch ($erp) {
            case '310':
                // Special TLD LAC case
                return self::byConstraints('id IN(3,11)'); // TLD America Inc., TLD Japan Co., Ltd
                break;
            case '600':
                // Special TLD ASI case
                return self::byConstraints('id IN(5,9)'); // TLD Asia Ltd, TLD Asia (Singapore) Pte Ltd.
                break;
            default:
                return self::byConstraints("id IN (SELECT juridical_location_id FROM locations WHERE erp=$erp)");
                break;
        }
    }

    public static function getList()
    {
        return self::byConstraints('1=1');
    }

    public function getListAsIdName()
    {
        return array_column(self::getList(), 'name', 'id');
    }
}

/**
 *    returns a tldLocation class
 *
 *    Class for accessing and manipulating TLD location information
 *
 * @package UsersAndGroups
 */
#[AllowDynamicProperties]
class tldLocation
{

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsDetails = $this->getHeader();
    }

    public function getHeader()
    {
        if (!$this->itsID) {
            return [];
        }
        $query = 'SELECT * FROM locations WHERE id=' . $this->itsID;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function getID()
    {
        return $this->itsID;
    }

    public function getERP()
    {
        return $this->itsDetails['erp'];
    }

    public function getShortName()
    {
        return $this->itsDetails['location'];
    }

    public function getBuName()
    {
        return $this->itsDetails['business_unit'];
    }

    public function getEmailDomain()
    {
        return $this->itsDetails['email_domain'];
    }

    public function getServiceEmail()
    {
        return $this->itsDetails['sh_email'];
    }

    public function getPartsEmail()
    {
        return $this->itsDetails['sph_email'];
    }

    public function getRepID()
    {
        return $this->itsDetails['repid'];
    }

    public function isEmpty()
    {
        return empty($this->itsDetails);
    }

    public static function getRegionByBUID($buid)
    {
        if (!is_numeric($buid)) {
            return;
        }
        $id = TldDatabase::escape($buid);
        $query = "SELECT region FROM locations WHERE id=$id";
        $rows = tldUtils::getSqlRowToAssocArray($query);
        return $rows['region'];
    }

    /**
     * Get list of locations where erp field is not empty
     *
     * @return array
     */
    public static function getERPList($format = '')
    {
        $query = 'SELECT * FROM locations WHERE erp!=0 ORDER BY location';
        switch ($format) {
            case 'smartyOptions':
                $result = tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['erp', 'location']);
                break;
            case 'smartyOptionsIDLocation':
                $result = tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['id', 'location']);
                break;
            default:
                $result = tldUtils::getSqlToAssocArray($query);
                break;
        }
        return $result;
    }

    public static function getRegionList($format = '')
    {
        $query = "SELECT DISTINCT(locations.region) FROM locations WHERE region <>'' ORDER BY region";

        return tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['region', 'region']);

    }

    public static function getWarehouseList($format = '')
    {
        $query = "SELECT * FROM locations WHERE disable=0 AND warehouse='Y' ORDER BY erp";
        switch ($format) {
            case 'smartyOptions':
                $result = tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['erp', 'location']);
                break;
            case 'smartyOptionsIDLocation':
                $result = tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['id', 'location']);
                break;
            default:
                $result = tldUtils::getSqlToAssocArray($query);
                break;
        }
        return $result;
    }

    public static function getBAANOwnerList($format = '')
    {
        $query = 'SELECT * FROM ttcmcs022300';
        if ($format === 'smartyOptions') {
            return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan', 'smartyOptions' => ['t_csel', 't_csel']]);
        }

        return tldUtils::getSqlToAssocArray($query);

    }

    /**
     * Get list of locations
     *
     * @return array
     */
    public static function getLocationList($format = '')
    {
        $query = "SELECT * FROM locations WHERE disable=0 AND company_name!='' ORDER BY location";
        switch ($format) {
            case 'smartyOptions':
                $result = tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['id', 'location']);
                break;
            case 'smartyOptionsLocationLocation':
                $result = tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['location', 'location']);
                break;
            case 'smartyOptionsERPLocation':
                $result = tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['erp', 'location']);
                break;
            default:
                $result = tldUtils::getSqlToAssocArray($query);
        }
        return $result;
    }

    public static function getLocationEmailDomainsList()
    {
        return array_merge(array_column(tldUtils::getSqlToAssocArray("SELECT DISTINCT email_domain FROM locations WHERE email_domain!='' AND disable=0 AND hidden=0 ORDER BY email_domain"), 'email_domain'), self::getHardcodedDomainsList());
    }

    private static function getHardcodedDomainsList()
    {
        return ['xops-aero.com', 'tracteasy.com'];
    }

    /**
     * Get list of locations where factory field is set to 'Y''
     *
     * @return array
     */
    public static function getFactoryList($format = '')
    {
        $query = "SELECT * FROM locations WHERE disable=0 AND factory='Y' ORDER BY location";
        switch ($format) {
            case 'smartyOptions':
                $result = tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['erp', 'location']);
                break;
            case 'smartyOptionsIDLocation':
                $result = tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['id', 'location']);
                break;
            case 'smartyOptionsLocationLocation':
                $result = tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['location', 'location']);
                break;
            default:
                $result = tldUtils::getSqlToAssocArray($query);
        }
        return $result;
    }

    public static function getFactorySSO($erp)
    {
        switch ($erp) {
            case 220:
                return 220;
            case 250:
                return 250;
            case 300:
            case 400:
            case 420:
                return 300;
            case 410:
                return 320;
            case 430:
                return 430;
            case 500:
            case 510:
            case 520:
            case 540:
            case 570:
                return 540;
            case 820:
                return 560;
            case 640:
            case 660:
            case 680:
                return 680;
            case 610:
            case 700:
            case 600:
                return 600;
        }
        return false;
    }

    /**
     * Get list of locations where sph field is set to 'Y''
     *
     * @return array
     */
    public static function getSPHList($format = '')
    {
        $query = "SELECT * FROM locations WHERE disable=0 AND sph='Y' ORDER BY erp";
        switch ($format) {
            case 'smartyOptions':
                $result = tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['erp', 'location']);
                break;
            case 'smartyOptionsIDLocation':
                $result = tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['id', 'location']);
                break;
            default:
                $result = tldUtils::getSqlToAssocArray($query);
        }
        return $result;
    }

    /**
     * Get list of Service hub locations where sh field is set to 'Y''
     *
     * @return array
     */
    public static function getServiceHubList($format = '')
    {
        $query = "SELECT * FROM locations WHERE disable=0 AND sh='Y' ORDER BY erp";
        switch ($format) {
            case 'smartyOptions':
                $result = tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['erp', 'location']);
                break;
            case 'smartyOptionsIDLocation':
                $result = tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['id', 'location']);
                break;
            default:
                $result = tldUtils::getSqlToAssocArray($query);
        }
        return $result;
    }

    /**
     * Get list of Sales Organisation
     * @return array
     */
    public static function getSalesOrgList($format = '')
    {
        $query = "SELECT * FROM locations WHERE disable=0 AND role LIKE 'SSO' ORDER BY location";
        switch ($format) {
            case 'smartyOptions':
                $result = tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['erp', 'location']);
                break;
            case 'smartyOptionsIDLocation':
                $result = tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['id', 'location']);
                break;
            case 'smartyOptionsLocationLocation':
                $result = tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['location', 'location']);
                break;
            default:
                $result = tldUtils::getSqlToAssocArray($query);
        }
        return $result;
    }

    public static function getBuList()
    {
        $a = "business_unit<>''";
        return self::byConstraints($a);
    }

    public static function getTimezoneByID($id)
    {
        if (!is_numeric($id)) {
            return;
        }
        $id = TldDatabase::escape($id);
        $query = "SELECT timezone FROM locations WHERE id='$id'";
        $rows = tldUtils::getSqlToAssocArray($query);
        return $rows[0]['timezone'];
    }

    public static function getBuListAsIdLocation()
    {
        return array_column(self::getBuList(), 'location', 'id');
    }

    public static function getBuListAsIdBU()
    {
        return array_column(self::getBuList(), 'location', 'id');
    }

    /**
     * Get list of locations by defined options
     *
     * @return array
     */
    public static function getListByOption($options = [], $format = '')
    {
        $SELECT = (isset($options['select']) && $options['select'] !== '') ? $options['select'] : '*';
        $WHERE = (isset($options['where']) && $options['where'] !== '') ? $options['where'] : "hidden=0 AND company_name<>''";
        $HAVING = (isset($options['having']) && $options['having'] !== '') ? "HAVING {$options['having']}" : '';
        $ORDER = (isset($options['order_by']) && $options['order_by'] !== '') ? $options['order_by'] : 'region, company_name';
        $GROUP = (isset($options['group_by']) && $options['group_by'] !== '') ? "GROUP BY {$options['group_by']}" : '';
        $query = "SELECT $SELECT FROM locations WHERE $WHERE $HAVING ORDER BY $ORDER $GROUP";
        switch ($format) {
            case 'smartyOptions':
                $result = tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['id', 'location']);
                break;
            case 'smartyOptionsLocationLocation':
                $result = tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['location', 'location']);
                break;
            default:
                $result = tldUtils::getSqlToAssocArray($query);
        }
        return $result;
    }

    public static function getIDByERP($erp)
    {
        if (!is_numeric($erp)) {
            return;
        }
        $erp = TldDatabase::escape($erp);
        $query = "SELECT * FROM locations WHERE erp=$erp";
        $rows = tldUtils::getSqlToAssocArray($query);
        //if(count($rows) == 1)
        return $rows[0]['id'];
    }

    /**
     * @return int|string
     */
    public static function getIDByLocation($location)
    {
        if (is_numeric($location)) {
            return;
        }
        $location = TldDatabase::escape($location);
        $query = "SELECT * FROM locations WHERE location='$location'";
        $rows = tldUtils::getSqlToAssocArray($query);
        return $rows[0]['id'];
    }

    public static function getLocationByID($id)
    {
        if (!is_numeric($id)) {
            return;
        }
        $id = TldDatabase::escape($id);
        $query = "SELECT * FROM locations WHERE id='$id'";
        $rows = tldUtils::getSqlToAssocArray($query);
        return $rows[0]['location'];
    }

    public static function getLocationByERP($erp)
    {
        if (!is_numeric($erp)) {
            return;
        }
        $erp = TldDatabase::escape($erp);
        $query = "SELECT * FROM locations WHERE erp='$erp'";
        $rows = tldUtils::getSqlToAssocArray($query);
        return $rows[0]['location'];
    }

    public static function getERPByID($id)
    {
        if (!is_numeric($id)) {
            return;
        }
        $id = TldDatabase::escape($id);
        $query = "SELECT * FROM locations WHERE id=$id";
        $rows = tldUtils::getSqlToAssocArray($query);
        return $rows[0]['erp'];
    }

    public static function getERPByLocation($location)
    {
        if (is_numeric($location)) {
            return;
        }
        $location = TldDatabase::escape($location);
        $query = "SELECT * FROM locations WHERE location='$location'";
        $rows = tldUtils::getSqlToAssocArray($query);
        return $rows[0]['erp'];
    }

    /**
     * Get location by name
     * @param string $name
     * @return row location data
     */
    public static function byLocationName($name)
    {
        $name = TldDatabase::escape($name);
        $query = "SELECT * FROM locations WHERE location='$name'";
        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Get list of locations using network address
     * @param string IP address
     * @return array Array of db rows
     */
    public static function byNetworkAddress($ipAddr)
    {
        require 'Net/IPv4.php';
        $results = [];
        // Get all network address from locations
        $locations = self::byConstraints("fw_inside_network<>''");

        $mtl = null;
        foreach ($locations as $location) {
            if ($location['location'] === 'TLD MTL') {
                $mtl = $location;
                break;
            }
        }
        // foreach of them, check if IP in this network
        if (count($locations)) {
            foreach ($locations as $location) {
                foreach (explode(',', $location['fw_inside_network']) as $locationIP) {
                    if (!Net_IPv4::ipInNetwork($ipAddr, trim($locationIP))) {
                        continue;
                    }
                    $results[] = $location;
                }
            }
        }
        // return location list
        return $results;
    }

    /**
     * Get BUs by outside network address
     * @param string IP address
     * @return array
     */
    public static function byOutsideNetworkAddress($addr)
    {
        $query = "SELECT * FROM locations WHERE fw_outside_ip like '$addr'";
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byRegionID($id)
    {
        return self::byConstraints(['region_id' => $id]);
    }

    public static function byJuridicalEntityID($id)
    {
        return self::byConstraints(['juridical_location_id' => $id]);
    }

    public static function getNameFromErp(...$erps)
    {
        $values = (implode(', ', $erps));

        $query = "SELECT erp, location FROM locations WHERE erp in ($values) ORDER BY location";
        return tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['erp', 'location']);
    }

    /**
     * Get locations by constraints
     * @param $a array|string constraints
     * @param $opt array
     * @return array
     */
    public static function byConstraints($a, $opt = '')
    {
        if (empty($a)) {
            return;
        }
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }

        $query = "SELECT * FROM locations WHERE $WHERE ORDER BY location";
        return tldUtils::getSqlToAssocArray($query);
    }
}

class tldSPH
{
    public $itsID;
    public $itsDetails;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsDetails = $this->getHeader();
    }

    public static function getSELECT(): string
    {
        return <<<EOF
SELECT
    sph.*,
    locations.erp AS erp,
    locations.parts_tel AS parts_tel,
    locations.parts_fax AS parts_fax,
    locations.sph_email AS sph_email
EOF;
    }

    public static function getFROM(): string
    {
        return <<<EOF
FROM locations_sph AS sph
LEFT JOIN locations ON locations.id=sph.location_id
EOF;
    }

    public function getHeader()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = "$SELECT $FROM WHERE sph.id=$this->itsID";
        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function getID()
    {
        return $this->itsID;
    }

    public function getIDByBUID($id)
    {
        if (!is_numeric($id)) {
            return;
        }
        $id = TldDatabase::escape($id);
        $query = "SELECT * FROM locations_sph WHERE location_id='$id'";
        $rows = tldUtils::getSqlToAssocArray($query);
        return $rows[0]['id'];
    }

    public function getLocationID($id = '')
    {
        if (empty($id)) {
            return $this->itsDetails['location_id'];
        }

        $sph = new tldSPH($id);
        $header = $sph->itsDetails;

        return $header['location_id'];
    }

    public static function getERP($id = '')
    {
        $sph = new tldSPH($id);
        $header = $sph->itsDetails;

        return $header['erp'];
    }

    public function getName()
    {
        return $this->itsDetails['name'];
    }

    public function getLocationByID($id)
    {
        if (!is_numeric($id)) {
            return;
        }
        $id = TldDatabase::escape($id);
        $query = "SELECT * FROM locations_sph WHERE id='$id'";
        $rows = tldUtils::getSqlToAssocArray($query);
        return $rows[0]['name'];
    }

    public function getPartsEmail()
    {
        return $this->itsDetails['sph_email'];
    }

    public static function getList($format = '')
    {
        $query = 'SELECT * FROM locations_sph ORDER BY name';
        switch ($format) {
            case 'smartyOptionsERPLocation':
                $result = tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['erp', 'name']);
                break;
            case 'smartyOptionsIDLocation':
                $result = tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['id', 'name']);
                break;
            case 'smartyOptionsLocationLocation':
                $result = tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['name', 'name']);
                break;
            default:
                $result = tldUtils::getSqlToAssocArray($query);
        }
        return $result;
    }
}


/**
 * Class for accessing webcam data
 *
 * @param string
 * @package Utils
 */
class tldWebCam
{
    public const BASE_URL = '/en/private/uploads/';

    public static function getCamList()
    {
        return [
            'America' => [
                'Windsor' => [
                    'webcam_shared/WIN/MIL_SHOP/MIL_SHOP.jpg' => 'MIL SHOP',
                    'webcam_shared/WIN/GPU_SHOP/GPU_SHOP.jpg' => 'GPU SHOP',
                    'webcam_shared/WIN/GPU_production_line/GPU_production_line.jpg' => 'GPU production line',
                    'webcam_shared/WIN/ACU_SHOP/ACU_SHOP.jpg' => 'ACU SHOP',
                ],
                'Sherbrooke' => [
                    'webcam_shared/SHE/SHOP01/SHOP01.jpg' => 'RANGER ASSEMBLY AREA',
                    'webcam_shared/SHE/SHOP02/SHOP02.jpg' => 'SHOP 2',
                    'webcam_shared/SHE/SHOP03/SHOP03.jpg' => 'SHOP 3',
                ],
                'Aero Specialties' => [
                    'webcam_shared/AERO/JETGO1/JETGO1.jpg' => 'JETGO1',
                    'webcam_shared/AERO/RnR_SHOP/RnR_SHOP.jpg' => 'RnR Shop',
                    'webcam_shared/AERO/LWC_SHOP/LWC_SHOP.jpg' => 'LWC Shop',
                    'webcam_shared/AERO/HPU_SHOP/HPU_SHOP.jpg' => 'HPU Shop',
                    'webcam_shared/AERO/Machine_SHOP/Machine_SHOP.jpg' => 'Machine Shop',
                ],
            ],
            'Asia' => [
                'Shanghai' => [
                    'webcam/SHA_SHOP_05.jpg' => 'SHOP 2',
                    'webcam/SHA_SHOP_06.jpg' => 'SHOP 3',
                    'webcam/SHA_SHOP_07.jpg' => 'SHOP 4',
                ],
                'Wuxi' => [
                    'webcam/WUX_SHOP_01.jpg' => 'SHOP 01',
                    'webcam/WUX_SHOP_04.jpg' => 'SHOP 04',
                    'webcam/WUX_SHOP_05.jpg' => 'SHOP 05',
                    'webcam/WUX_SHOP_06.jpg' => 'SHOP 06',
                    'webcam/WUX_SHOP_07.jpg' => 'SHOP 07',
                    'webcam/WUX_SHOP_08.jpg' => 'SHOP 08',
                    'webcam/WUX_SHOP_09.jpg' => 'SHOP 09',
                    'webcam/WUX_SHOP_10.jpg' => 'SHOP 10',
                ],
            ],
            'Europe' => [
                'Montlouis' => [
                    'webcam/MTL_SHOP_B1.jpg' => 'Shop B1',
                    'webcam/MTL_SHOP_B2.jpg' => 'Shop B2',
                ],
                'Saint-Lin' => [
                    'webcam/STL_SHOP_ABS.jpg' => 'TRANSPORTERS',
                    'webcam/STL_SHOP_NBL.jpg' => 'ABS',
                    'webcam/STL_SHOP_NBL_2.jpg' => 'Shop NBL 2',
                    'webcam/STL_SHOP_JET-16.jpg' => 'Shop JET-16',
                    'webcam/STL_SHOP_WHSE.jpg' => 'WAREHOUSE',
                ],
                'Sorigny' => [
                    'webcam/SOR_01.jpg' => 'Camera 1',
                    'webcam/SOR_02.jpg' => 'Camera 2',
                    'webcam/SOR_03.jpg' => 'Camera 3',
                    'webcam/SOR_04.jpg' => 'Camera 4',
                    'webcam/SOR_05.jpg' => 'SOR 3',
                ],
                'Frameries' => [
                    'webcam/LEB_ATELIER_1.jpg' => 'Atelier 1',
                    'webcam/LEB_ATELIER_2.jpg' => 'Atelier 2',
                ],
                'Kempston' => [
                    'webcam/KEM_ATELIER_1.jpg' => 'Camera 1',
                    'webcam/KEM_ATELIER_2.jpg' => 'Camera 2',
                    'webcam/KEM_ATELIER_3.jpg' => 'Camera 3',
                ],
            ],
            'India' => [
                'Bengaluru' => [
                    'webcam/MAI_SHOP_01.jpg' => 'SHOP 1',
                    'webcam/MAI_SHOP_02.jpg' => 'SHOP 2',
                    'webcam/MAI_SHOP_03.jpg' => 'SHOP 3',
                    'webcam/MAI_SHOP_04.jpg' => 'SHOP 4',
                    'webcam/MAI_SHOP_05.jpg' => 'SHOP 5',
                    'webcam/MAI_SHOP_06.jpg' => 'SHOP 6',
                    'webcam/MAI_SHOP_07.jpg' => 'SHOP 7',
                    'webcam/MAI_SHOP_08.jpg' => 'SHOP 8',
                ],
            ],
        ];
    }

    public static function getRandomPic()
    {
        $regions = self::getCamList();
        $region = array_rand($regions);
        $locations = $regions[$region];
        $location = array_rand($locations);
        $files = $locations[$location];
        $file = array_rand($files);

        return ['location' => $location, 'file' => self::BASE_URL.$file, 'title' => $locations[$location][$file]];
    }
}

/**
 *    returns a tldGroup class
 *
 *    Class for accessing and manipulating TLD user group information
 *
 * @package UsersAndGroups
 */
class tldGroup
{

    /**
     * Constructor
     * @param string $groupName name of group
     * @param integer $level optional level/erp number
     */
    public function __construct($groupName, $level = null, $divisions = [])
    {
        $this->itsGroupName = $groupName;
        $this->itsLevel = $level;
        $this->divisions = $divisions;
    }

    /**
     * Get list of users in group by SSO
     *
     * @return array of DB rows
     */
    public function getUserlistBySSO($sso, $options = '', bool $displayEmail = true)
    {
        if (empty($this->itsGroupName)) {
            return;
        }
        $emailRequest = $displayEmail ? "' (', T1.email, ')'" : '';
        $query = <<<EOF
        SELECT T1.*, CONCAT(UPPER(T1.lastname), ', ', T1.firstname, $emailRequest) AS fullname
        FROM people as T1, people_groups as T2
        WHERE T1.id=T2.parent_id
            AND T2.group_name='$this->itsGroupName'
            AND T1.bu_id=$sso
            AND T1.hidden=0
EOF;
        if ($this->itsLevel) {
            $query .= " AND T2.level='" . $this->itsLevel . "'";
        }
        $query .= ' ORDER BY fullname';
        if (isset($options['smartyOptions'])) {
            return tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['id', 'fullname']);
        }

        return tldUtils::getSqlToAssocArray($query);
    }


    /**
     * Get list of users in group
     * @return array of DB rows
     */
    public function getUserlist($options = '', bool $displayEmail = true)
    {
        if (empty($this->itsGroupName)) {
            return;
        }
        $emailRequest = $displayEmail ? ", ' (', T1.email, ')'" : "";
        $query = <<<EOF
SELECT
    T1.*,
    CONCAT(UPPER(T1.lastname), ', ', T1.firstname$emailRequest) AS fullname,
    fct.dsc AS function_dsc
FROM people as T1
    LEFT JOIN people_groups AS T2 ON T1.id=T2.parent_id
    LEFT JOIN tld_functions AS fct ON fct.id=T1.fct_id
WHERE
    T2.group_name='$this->itsGroupName'
	AND T1.disabled='N'
EOF;
        if ($this->itsLevel) {
            $query .= " AND T2.level='" . $this->itsLevel . "'";
        }
        $query .= ' ORDER BY fullname';
        if (isset($options) && ($options === 'smartyOptions' || (is_array($options) && isset($options['smartyOptions'])))) {
            return tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['id', 'fullname']);
        }

        return tldUtils::getSqlToAssocArray($query);
    }

    public function email($message, $subject, $options = '')
    {
        return self::emailGroup($this->itsGroupName, $this->itsLevel, $subject, $message, $options = '');
    }
    //STATIC

    /**
     * Send an email message to all in the group
     *
     * @param string $group_name
     * @param integer $level
     * @param string $subject
     * @param string $message
     * @param array $options
     * @return boolean
     */
    public static function emailGroup($group_name, $level, $subject, $message, $options = [])
    {
        $users = self::inGroup($group_name, $level);
        if (count($users) == 0) {
            return;
        }
#		$crlf = "\n";
        $to = '';
        foreach ($users as $user) {
            $to .= $user['email'] . ',';
        }
        if (empty($options['from'])) {
            $options['from'] = 'noreply@tld-gse.com';
        }

        $cc = $bcc = $file = '';
        if (!empty($options['cc']) && is_array($options['cc'])) {
            $cc = array_diff($options['cc'], $to);
            unset($options['cc']);
        }
        if (!empty($options['bcc']) && is_array($options['bcc'])) {
            $bcc = array_diff($options['bcc'], $options['cc'], $to);
            unset($options['bcc']);
        }
        if (isset($options['files'])) {
            $file = $options['files'];
            unset($options['files']);
        }

        return tldUtils::emailAttachment($to, $options['from'], $subject, $message, $file, $cc, $bcc, $options);
    }

    /**
     *
     * @param array $to associative array, key being the company erp number
     * @param string $from
     * @param string $subject
     * @param string $message
     * @param array $options =array("cc"=>array(email to cc))
     * @return boolean
     */
    public static function emailMultipleGroups($to, $from = 'webmaster@tld-gse.com', $subject = 'No subject', $message = 'No message', $options = [])
    {
        if (empty($to)) {
            return false;
        }

        $emailList = [[]];
        foreach ($to as $level => $groups) {
            foreach ($groups as $group) {
                if ($level === 'GLOBAL') {
                    $emailList[] = (new tldGroup($group))->getEmailList();
                } else {
                    $emailList[] = (new tldGroup($group, $level))->getEmailList();
                }
            }
        }
        $emailList = array_merge(...$emailList);

        if (!$emailList) {
            return false;
        }
        $emailList = array_unique($emailList);

        $cc = $bcc = $file = '';
        if (!empty($options['cc']) && is_array($options['cc'])) {
            $cc = array_diff($options['cc'], $emailList);
            unset($options['cc']);
        }
        if (!empty($options['bcc']) && is_array($options['bcc'])) {
            $bcc = array_diff($options['bcc'], $options['cc'], $emailList);
            unset($options['bcc']);
        }
        if (isset($options['files'])) {
            $file = $options['files'];
            unset($options['files']);
        }

        return tldUtils::emailAttachment($emailList, $from, $subject, $message, $file, $cc, $bcc, $options);
    }

    /**
     * Get list of users in multiple group
     * @param array $group
     * @param int $level
     * @param array $options
     * @return array of DB rows
     */
    public static function getUserListByMultipleGroup($group, $level = null, $options = null)
    {
        $WHERE = '';
        if (isset($options['where']) && is_string($options['where'])) {
            $WHERE .= ' AND ' . $options['where'];

        }
        if (!is_array($group)) {
            return;
        }
        $list = implode("','", $group);
        $query = <<<SQL
SELECT
    DISTINCT(T1.id),
    T1.*,
    T2.group_name,
    CONCAT(UPPER(lastname), ', ', firstname) AS fullname,
    fct.dsc AS function_dsc
FROM
    people AS T1
    LEFT JOIN people_groups AS T2 ON T1.id=T2.parent_id
    LEFT JOIN tld_functions AS fct ON fct.id=T1.fct_id
WHERE
    T2.group_name IN('$list')
    AND T1.disabled="N"
	$WHERE
SQL;
        if ($level) {
            $query .= " AND T2.level='" . $level . "'";
        }
        $query .= ' ORDER BY fullname';
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
     * Get list of users in a group
     *
     * @return array people in group called $group
     */
    public static function inGroup($group, $level = null, $divisions = [])
    {
        $SELECT = tldUser::getSELECT();
        $FROM = tldUser::getFROM();
        // Constraints
        $group = TldDatabase::escape($group);
        $WHERE = $level !== null ? ' AND people_groups.level=' . $level : '';

        if (!empty($divisions)) {
            $WHERE = ' AND tld_divisions.name IN ("' . implode('", "', $divisions)  .'")';
        }

        $query = <<<EOF
$SELECT,
    people_groups.level,
    people_groups.group_name
$FROM
    LEFT JOIN people_groups ON people.id=people_groups.parent_id
    LEFT JOIN locations ON locations.id = people.bu_id
    LEFT JOIN tld_regions ON locations.region_id = tld_regions.id
    LEFT JOIN tld_sub_divisions ON tld_sub_divisions.id = tld_regions.sub_division_id
    LEFT JOIN tld_divisions ON tld_divisions.id = tld_sub_divisions.division_id
WHERE
	people_groups.group_name='$group'
    AND people.disabled = 'N'
	$WHERE
ORDER BY
    people_groups.level,
    people.lastname,
    people.firstname
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get email list as array
     *
     * @return array
     */
    public function getEmailList($group = '', $level = '', $divisions = [])
    {
        $result = [];
        if ($group && $level) {
            $names = self::inGroup($group, $level, $divisions);
        } else {
            $names = self::inGroup($this->itsGroupName, $this->itsLevel, $this->divisions);
        }

        foreach ($names as $name) {
            if (!empty($name['email'])) {
                $result[] = $name['email'];
            }
        }

        return array_unique($result);
    }

    /**
     * Get list of groups in system
     *
     * Option: get only groups that are being used, set $used=true
     *
     * @return array Array of db rows
     */
    public static function getGroups($used = false)
    {
        if ($used) {
            //return only the groups that are used in the people tables
            $query = <<<EOF
			SELECT DISTINCT group_name
			FROM people_groups
			ORDER BY group_name
EOF;
        } else {
            $query = <<<EOF
			SELECT *
			FROM people_groups_select
			ORDER BY group_name
EOF;
        }
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get list of Managers groups
     * @return array
     */
    public static function getManagerGroups()
    {
        return [
            'gg_ADMIN', 'superuser', 'role_CEO', 'role_CFO', 'role_CHAIRMAN', 'role_CIO', 'role_COO',
            'role_CSM', 'role_EM', 'role_EVP', 'role_GTD', 'role_ITD', 'role_MLM', 'role_PM', 'role_PSM',
            'role_QAM', 'role_SPM', 'role_TM', 'role_GCH',
        ];
    }

    /**
     * Get Api Acls
     * Only way to get group for a Region or a Division
     */
    public static function getApiAclsByLocationNamesAndGroupNames(array $locationNames, array $groupNames): array
    {
        global $kernel;

        try {
            $client = $kernel->getContainer()->get(Client::class);
            $acls = $client->get('acls', [
                'query' => [
                    'group.name' => $groupNames,
                    'location.name' => $locationNames,
                ],
            ]);
        } catch (Exception $e) {
            return [];
        }

        return $acls['hydra:member'];
    }

}

/**
 * Create a vCard file for download
 *
 * $vCard = new vCard($lang,$download_dir);
 * $vCard->setLastName('Mustermann');
 * ...
 * $vCard->outputFile('vcf');
 *
 * Tested with WAMP (XP-SP1/1.3.24/4.0.4/4.3.0)
 * Last change: 2002-12-27
 *
 * @desc Create a vCard file for download
 * @access public
 * @author Michael Wimmer <flaimo@gmx.net>
 * @copyright Michael Wimmer
 * @link http://www.flaimo.com/  flaimo.com
 * @package vCard
 * @version 1.002
 */

/**
 * Class for creating and manipulating vcards
 *
 * @package vCard
 */
class vCard
{

    /*-------------------*/
    /* V A R I A B L E S */
    /*-------------------*/

    /**
     * Output string to be written in the vCard file
     *
     * @desc Output string to be written in the vCard file
     * @var string
     * @access private
     */
    public $output;

    /**
     * Format of the output (vcf)
     *
     * @desc Format of the output (vcf)
     * @var string
     * @access private
     */
    public $output_format;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $first_name;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $middle_name;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $last_name;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $edu_title;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $addon;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $nickname;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $company;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $organisation;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $department;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $job_title;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $note;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $tel_work1_voice;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $tel_work2_voice;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $tel_home1_voice;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $tel_home2_voice;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $tel_cell_voice;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $tel_car_voice;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $tel_pager_voice;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $tel_additional;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $tel_work_fax;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $tel_home_fax;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $tel_isdn;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $tel_preferred;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $tel_telex;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $work_street;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $work_zip;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $work_city;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $work_region;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $work_country;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $home_street;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $home_zip;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $home_city;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $home_region;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $home_country;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $postal_street;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $postal_zip;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $postal_city;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $postal_region;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $postal_country;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $url_work;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $role;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $birthday;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $email;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $rev;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $lang;

    /**
     * No information available
     *
     * @desc No information available
     * @var string
     * @access private
     */
    public $photo;


    /*-----------------------*/
    /* C O N S T R U C T O R */
    /*-----------------------*/

    /**
     * Constructor
     *
     * Only job is to set all the variablesnames
     *
     * @desc Constructor
     * @param (string) $downloaddir
     * @param (string) $lang
     * @return (void)
     * @access private
     * @since 1.000 - 2002/10/10
     */
    public function __construct($downloaddir = '', $lang = '')
    {
        $this->download_dir = (string)((trim($downloaddir) !== '') ? $downloaddir : 'vcarddownload');
        $this->card_filename = (string)time() . '.vcf';
        $this->rev = (string)date('Ymd\THi00\Z', time());
        $this->setLanguage($lang);

        if ($this->checkDownloadDir() == false) {
            die('error creating download directory');
        } // end if
    } // end function


    /*-------------------*/
    /* F U N C T I O N S */
    /*-------------------*/

    /**
     * Checks if the download directory exists, else trys to create it
     *
     * @desc Checks if the download directory exists, else trys to create it
     * @return (boolean)
     * @access private
     * @since 1.000 - 2002/10/10
     */
    public function checkDownloadDir()
    {
        if (!is_dir($this->download_dir)) {
            return (boolean)((!mkdir($this->download_dir, 0700)) ? false : true);
        } else {
            return (boolean)true;
        } // end if
    } // end function

    /**
     * Set Language (iso code) for the Strings in the vCard file
     *
     * @desc
     * @param (string) $isocode
     * @return (void)
     * @access private
     * @since 1.000 - 2002/10/10
     */
    public function setLanguage($isocode = '')
    {
        $this->lang = (string)(($this->isValidLanguageCode($isocode) == true) ? ';LANGUAGE=' . $isocode : '');
    } // end function

    /**
     * Encodes a string for QUOTE-PRINTABLE
     *
     * @desc Encodes a string for QUOTE-PRINTABLE
     * @param (string) $quotprint  String to be encoded
     * @return (string)  Encodes string
     * @access private
     * @since 1.000 - 2002/10/20
     * @author Harald Huemer <harald.huemer@liwest.at>
     */
    public function quotedPrintableEncode($quotprint)
    {
        /*
		//beim Mac Umlaute nicht kodieren !!!! sonst Fehler beim Import
		if ($progid == 3)
		  {
		  $quotprintenc = preg_replace("~([\x01-\x1F\x3D\x7F-\xBF])~e", "sprintf('=%02X', ord('\\1'))", $quotprint);
		  return($quotprintenc);
		  }
		//bei Windows und Linux alle Sonderzeichen kodieren
		else
		  {*/
        $quotprint = (string)str_replace('\r\n', chr(13) . chr(10), $quotprint);
        $quotprint = (string)str_replace('\n', chr(13) . chr(10), $quotprint);
        $quotprint = (string)preg_replace_callback(
            "~([\x01-\x1F\x3D\x7F-\xFF])~",
            function ($matches) {
                return sprintf('=%02X', ord($matches[1]));
            },
            $quotprint);
        $quotprint = (string)str_replace('\=0D=0A', '=0D=0A', $quotprint);
        return (string)$quotprint;
    } // end function

    /**
     * Checks if a given string is a valid iso-language-code
     *
     * @desc Checks if a given string is a valid iso-language-code
     * @param (string) $code  String that should validated
     * @return (boolean) $isvalid  If string is valid or not
     * @access private
     * @since 1.000 - 2002/10/20
     */
    public function isValidLanguageCode($code)
    { // PHP5: protected
        $isvalid = (boolean)false;
        if (preg_match('(^([a-z]{2})$|^([a-z]{2}_[a-z]{2})$|^([a-z]{2}-[a-z]{2})$)', trim($code)) > 0) {
            $isvalid = (boolean)true;
        } // end if
        return (boolean)$isvalid;
    } // end function


    /**
     * Set the persons first name
     *
     * @desc Set the persons first name
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setFirstName($input)
    {
        $this->first_name = (string)$input;
    } // end function

    /**
     * Set the persons middle name(s)
     *
     * @desc Set the persons middle name(s)
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setMiddleName($input)
    {
        $this->middle_name = (string)$input;
    } // end function

    /**
     * Set the persons last name
     *
     * @desc Set the persons last name
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setLastName($input)
    {
        $this->last_name = (string)$input;
    } // end function

    /**
     * Set the persons title (Doctor,...)
     *
     * @desc Set the persons title (Doctor,...)
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setEducationTitle($input)
    {
        $this->edu_title = (string)$input;
    } // end function

    /**
     * Set the persons addon (jun., sen.,...)
     *
     * @desc Set the persons addon (jun., sen.,...)
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setAddon($input)
    {
        $this->addon = (string)$input;
    } // end function

    /**
     * Set the persons nickname
     *
     * @desc Set the persons nickname
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setNickname($input)
    {
        $this->nickname = (string)$input;
    } // end function

    /**
     * Set the company name for which the person works for
     *
     * @desc Set the company name for which the person works for
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setCompany($input)
    {
        $this->company = (string)$input;
    } // end function

    /**
     * Set the organisations name for which the person works for
     *
     * @desc Set the organisations name for which the person works for
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setOrganisation($input)
    {
        $this->organisation = (string)$input;
    } // end function

    /**
     * Set the department name of company for which the person works for
     *
     * @desc Set the department name of company for which the person works for
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setDepartment($input)
    {
        $this->department = (string)$input;
    } // end function

    /**
     * Set the persons job title
     *
     * @desc Set the persons job title
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setJobTitle($input)
    {
        $this->job_title = (string)$input;
    } // end function

    /**
     * Set additional notes for that person
     *
     * @desc Set additional notes for that person
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setNote($input)
    {
        $this->note = (string)$input;
    } // end function

    /**
     * Set telephone number (Work 1)
     *
     * @desc Set telephone number (Work 1)
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setTelephoneWork1($input)
    {
        $this->tel_work1_voice = (string)$input;
    } // end function

    /**
     * Set telephone number (Work 2)
     *
     * @desc Set telephone number (Work 2)
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setTelephoneWork2($input)
    {
        $this->tel_work2_voice = (string)$input;
    } // end function

    /**
     * Set telephone number (Home 1)
     *
     * @desc Set telephone number (Home 1)
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setTelephoneHome1($input)
    {
        $this->tel_home1_voice = (string)$input;
    } // end function

    /**
     * Set telephone number (Home 2)
     *
     * @desc Set telephone number (Home 2)
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setTelephoneHome2($input)
    {
        $this->tel_home2_voice = (string)$input;
    } // end function

    /**
     * Set cellphone number
     *
     * @desc Set cellphone number
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setCellphone($input)
    {
        $this->tel_cell_voice = (string)$input;
    } // end function


    /**
     * Set carphone number
     *
     * @desc Set carphone number
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setCarphone($input)
    {
        $this->tel_car_voice = (string)$input;
    } // end function

    /**
     * Set pager number
     *
     * @desc Set pager number
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setPager($input)
    {
        $this->tel_pager_voice = (string)$input;
    } // end function

    /**
     * Set additional phone number
     *
     * @desc Set additional phone number
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setAdditionalTelephone($input)
    {
        $this->tel_additional = (string)$input;
    } // end function

    /**
     * Set fax number (Work)
     *
     * @desc Set fax number (Work)
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setFaxWork($input)
    {
        $this->tel_work_fax = (string)$input;
    } // end function

    /**
     * Set fax number (Home)
     *
     * @desc Set fax number (Home)
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setFaxHome($input)
    {
        $this->tel_work_home = (string)$input;
    } // end function


    /**
     * Set ISDN (phone) number
     *
     * @desc Set ISDN (phone) number
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setISDN($input)
    {
        $this->tel_isdn = (string)$input;
    } // end function

    /**
     * Set preferred phone number
     *
     * @desc Set preferred phone number
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setPreferredTelephone($input)
    {
        $this->tel_preferred = (string)$input;
    } // end function

    /**
     * Set telex number
     *
     * @desc Set telex number
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setTelex($input)
    {
        $this->tel_telex = (string)$input;
    } // end function


    /**
     * Set streetname (Work Address)
     *
     * @desc Set streetname (Work Address)
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setWorkStreet($input)
    {
        $this->work_street = (string)$input;
    } // end function

    /**
     * Set ZIP code (Work Address)
     *
     * @desc Set ZIP code (Work Address)
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setWorkZIP($input)
    {
        $this->work_zip = (string)$input;
    } // end function

    /**
     * Set city (Work Address)
     *
     * @desc Set city (Work Address)
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setWorkCity($input)
    {
        $this->work_city = (string)$input;
    } // end function

    /**
     * Set region (Work Address)
     *
     * @desc Set region (Work Address)
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setWorkRegion($input)
    {
        $this->work_region = (string)$input;
    } // end function

    /**
     * Set country (Work Address)
     *
     * @desc Set country (Work Address)
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setWorkCountry($input)
    {
        $this->work_country = (string)$input;
    } // end function


    /**
     * Set streetname (Home Address)
     *
     * @desc Set streetname (Home Address)
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setHomeStreet($input)
    {
        $this->home_street = (string)$input;
    } // end function

    /**
     * Set ZIP code (Home Address)
     *
     * @desc Set ZIP code (Home Address)
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setHomeZIP($input)
    {
        $this->home_zip = (string)$input;
    } // end function

    /**
     * Set city (Home Address)
     *
     * @desc Set city (Home Address)
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setHomeCity($input)
    {
        $this->home_city = (string)$input;
    } // end function

    /**
     * Set region (Home Address)
     *
     * @desc Set region (Home Address)
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setHomeRegion($input)
    {
        $this->home_region = (string)$input;
    } // end function

    /**
     * Set country (Home Address)
     *
     * @desc Set country (Home Address)
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setHomeCountry($input)
    {
        $this->home_country = (string)$input;
    } // end function


    /**
     * Set streetname (Postal Address)
     *
     * @desc Set streetname (Postal Address)
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setPostalStreet($input)
    {
        $this->postal_street = (string)$input;
    } // end function

    /**
     * Set ZIP code (Postal Address)
     *
     * @desc Set ZIP code (Postal Address)
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setPostalZIP($input)
    {
        $this->postal_zip = (string)$input;
    } // end function

    /**
     * Set city (Postal Address)
     *
     * @desc Set city (Postal Address)
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setPostalCity($input)
    {
        $this->postal_city = (string)$input;
    } // end function

    /**
     * Set region (Postal Address)
     *
     * @desc Set region (Postal Address)
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setPostalRegion($input)
    {
        $this->postal_region = (string)$input;
    } // end function

    /**
     * Set country (Postal Address)
     *
     * @desc Set country (Postal Address)
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setPostalCountry($input)
    {
        $this->postal_country = (string)$input;
    } // end function


    /**
     * Set URL (Work)
     *
     * @desc Set URL (Work)
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setURLWork($input)
    {
        $this->url_work = (string)$input;
    } // end function

    /**
     * Set role (Student,...)
     *
     * @desc Set role (Student,...)
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setRole($input)
    {
        $this->role = (string)$input;
    } // end function


    /**
     * Set birthday
     *
     * @desc Set birthday
     * @param (int) $timestamp
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setBirthday($timestamp)
    {
        $this->birthday = (int)date('Ymd', $timestamp);
    } // end function


    /**
     * Set eMail address
     *
     * @desc Set eMail address
     * @param (string) $input
     * @return (void)
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function setEMail($input)
    {
        $this->email = (string)$input;
    } // end function

    /**
     * Generates the string to be written in the file later on
     *
     * @desc Generates the string to be written in the file later on
     * @param (string) $format  vcf
     * @see getCardOutput(), writeCardFile()
     * @access public
     * @since 1.000 - 2002/10/10
     */


    public static function vCardView($id, $fields, $portal = '')
    {
        if (empty($id) || !is_numeric($id)) {
            return 'ERROR: ID sent empty or invalid';
        }
        $user = new tldUser($id);
        $details = $user->itsDetails;
        if (empty($fields)) {
            return 'ERROR: Choice of fields to display are empty';
        }
        foreach ($fields as $field) {
            ${$field} = $details[$field] ?? null;
            if ($field === 'name') {
                $name = $details['lastname'] . ', ' . $details['firstname'];
            }
        }
        $DEFAULT_VIEW = '';
        switch ($portal) {
            case 'evendors':
                $DEFAULT_VIEW .= '
                    <table border="0" bgcolor="#CCCCCC">
                    <tr bgcolor="#FFFFFF">
                    <td>';

                if (!isset($photo) || $photo == '') {
                    $DEFAULT_VIEW .= '
                        <img src="/shared/no_photo.jpg" width="64">
                        </td>
                        <td>
                        <table>';
                } else {
                    $DEFAULT_VIEW .= '
                        <img src="https://www.tld-gse.com/shared/get_pic.php?m=user&id=' . $id . '&lastname=' . $lastname . '" width="64"></td>
                        </td>
                        <td>
                        <table>';
                }
                if (!empty($lastname)) {
                    $DEFAULT_VIEW .= '<tr><td>' . _('Lastname') . '</td><td><b>' . $lastname . '</b></td></tr>';
                }
                if (!empty($firstname)) {
                    $DEFAULT_VIEW .= '<tr><td>' . _('Firstname') . '</td><td><b>' . $firstname . '</b></td></tr>';
                }
                if (!empty($name)) {
                    $DEFAULT_VIEW .= '<tr><td>' . _('Name') . '</td><td><b>' . $name . '</b></td></tr>';
                }
                if (!empty($nickname)) {
                    $DEFAULT_VIEW .= '<tr><td>' . _('Alias / Nickname') . '</td><td><b>' . $nickname . '</b></td></tr>';
                }
                if (!empty($division)) {
                    $DEFAULT_VIEW .= '<tr><td>' . _('Division / Region') . '</td><td><b>' . $division . '</b></td></tr>';
                }
                if (!empty($location)) {
                    $DEFAULT_VIEW .= '<tr><td>' . _('Business Unit') . '</td><td><b>' . $location . '</b></td></tr>';
                }
                if (!empty($department)) {
                    $DEFAULT_VIEW .= '<tr><td>' . _('Department') . '</td><td><b>' . $department . '</b></td></tr>';
                }
                if (!empty($tld_function)) {
                    $DEFAULT_VIEW .= '<tr><td>' . _('TLD Function') . '</td><td><b>' . $tld_function . '</b></td></tr>';
                }
                if (!empty($title)) {
                    $DEFAULT_VIEW .= '<tr><td>' . _('Title') . '</td><td><b>' . $title . '</b></td></tr>';
                }
                if (!empty($email)) {
                    $DEFAULT_VIEW .= '<tr><td>' . _('Email') . '</td><td><b><a href="mailto:' . $email . '">' . $email . '</a></b></td></tr>';
                }
                if (!empty($phone)) {
                    $DEFAULT_VIEW .= '<tr><td>' . _('Telephone') . '</td><td><b>' . $phone . '</b></td></tr>';
                }
                if (!empty($direct_phone)) {
                    $DEFAULT_VIEW .= '<tr><td>' . _('Direct Line') . '</td><td><b>' . $direct_phone . '</b></td></tr>';
                }
                if (!empty($home_phone)) {
                    $DEFAULT_VIEW .= '<tr><td>' . _('Home Phone') . '</td><td><b>' . $home_phone . '</b></td></tr>';
                }
                if (!empty($mobile)) {
                    $DEFAULT_VIEW .= '<tr><td>' . _('Mobile') . '</td><td><b>' . $mobile . '</b></td></tr>';
                }
                if (!empty($fax)) {
                    $DEFAULT_VIEW .= '<tr><td>' . _('Fax') . '</td><td><b>' . $fax . '</b></td></tr>';
                }
                if (!empty($address)) {
                    $DEFAULT_VIEW .= '<tr><td>' . _('Address') . '</td><td><b>' . $address . '</b></td></tr>';
                }


                $DEFAULT_VIEW .= '
                    </table>
                    </td>
                    </tr>
                    </table>';
                break;
            case 'extranet':
                $DEFAULT_VIEW .= '
                    <table>
                    <tr>';

                if (!isset($photo) || $photo == '') {
                    $DEFAULT_VIEW .= '
                            <td style="vertical-align:top;">
                            <img src="/shared/no_photo.jpg" width="80">
                            </td>
                            <td>';
                } else {
                    $DEFAULT_VIEW .= '
                            <td style="vertical-align:top;">
                            <img src="https://www.tld-gse.com/shared/get_pic.php?m=user&id=' . $id . '&lastname=' . $lastname . '" width="80">
                            </td>
                            <td>';
                }
                if (!empty($lastname)) {
                    $DEFAULT_VIEW .= $lastname . '<br/>';
                }
                if (!empty($firstname)) {
                    $DEFAULT_VIEW .= $firstname . '<br/>';
                }
                if (!empty($name)) {
                    $DEFAULT_VIEW .= '<strong>' . $name . '</strong><br/>';
                }
                if (!empty($nickname)) {
                    $DEFAULT_VIEW .= $nickname . '<br/>';
                }
                if (!empty($division)) {
                    $DEFAULT_VIEW .= $division . '<br/>';
                }
                if (!empty($location)) {
                    $DEFAULT_VIEW .= $location . '<br/>';
                }
                if (!empty($department)) {
                    $DEFAULT_VIEW .= $department . '<br/>';
                }
                if (!empty($tld_function)) {
                    $DEFAULT_VIEW .= $tld_function . '<br/>';
                }
                if (!empty($title)) {
                    $DEFAULT_VIEW .= $title . '<br/>';
                }
                if (!empty($email)) {
                    $DEFAULT_VIEW .= '<a href="mailto:' . $email . '">' . $email . '</a><br/>';
                }
                if (!empty($phone)) {
                    $DEFAULT_VIEW .= $phone . '<br/>';
                }
                if (!empty($direct_phone)) {
                    $DEFAULT_VIEW .= $direct_phone . '<br/>';
                }
                if (!empty($home_phone)) {
                    $DEFAULT_VIEW .= $home_phone . '<br/>';
                }
                if (!empty($mobile)) {
                    $DEFAULT_VIEW .= $mobile . '<br/>';
                }
                if (!empty($fax)) {
                    $DEFAULT_VIEW .= $fax . '<br/>';
                }
                if (!empty($address)) {
                    $DEFAULT_VIEW .= $address;
                }

                $DEFAULT_VIEW .= '
                        </td>
                        </tr>
                        </table>';
                break;
            default:
                $DEFAULT_VIEW .= '
                    <table border="0" bgcolor="#CCCCCC">
                    <tr bgcolor="#FFFFFF">
                    <td><img src="/shared/icons/tld-icon.gif"><br><br>';

                if (!isset($photo) || $photo == '') {
                    $DEFAULT_VIEW .= '
                        <img src="/shared/no_photo.jpg" width="150">
                        </td>
                        <td>
                        <table>';
                } else {
                    $DEFAULT_VIEW .= '
                        <a href="/en/private/directory/index.php?m[0]=outPhoto&width=512&id=' . $id . '">
                        <img src="/en/private/directory/index.php?m[0]=outPhoto&width=128&id=' . $id . '">
                        </a>
                        </td>
                        <td>
                        <table>';
                }
                if (!empty($lastname)) {
                    $DEFAULT_VIEW .= '<tr><td>Lastname</td><td><b>' . $lastname . '</b></td></tr>';
                }
                if (!empty($firstname)) {
                    $DEFAULT_VIEW .= '<tr><td>Firstname</td><td><b>' . $firstname . '</b></td></tr>';
                }
                if (!empty($name)) {
                    $DEFAULT_VIEW .= '<tr><td>Name</td><td><b>' . $name . '</b></td></tr>';
                }
                if (!empty($nickname)) {
                    $DEFAULT_VIEW .= '<tr><td>Alias / Nickname</td><td><b>' . $nickname . '</b></td></tr>';
                }
                if (!empty($division)) {
                    $DEFAULT_VIEW .= '<tr><td>Division / Region</td><td><b>' . $division . '</b></td></tr>';
                }
                if (!empty($location)) {
                    $DEFAULT_VIEW .= '<tr><td>Business Unit</td><td><b>' . $location . '</b></td></tr>';
                }
                if (!empty($department)) {
                    $DEFAULT_VIEW .= '<tr><td>Department</td><td><b>' . $department . '</b></td></tr>';
                }
                if (!empty($tld_function)) {
                    $DEFAULT_VIEW .= '<tr><td>TLD Function</td><td><b>' . $tld_function . '</b></td></tr>';
                }
                if (!empty($title)) {
                    $DEFAULT_VIEW .= '<tr><td>Title</td><td><b>' . $title . '</b></td></tr>';
                }
                if (!empty($email)) {
                    $DEFAULT_VIEW .= '<tr><td>Email</td><td><b><a href="mailto:' . $email . '">' . $email . '</a></b></td></tr>';
                }
                if (!empty($phone)) {
                    $DEFAULT_VIEW .= '<tr><td>Telephone</td><td><b>' . $phone . '</b></td></tr>';
                }
                if (!empty($direct_phone)) {
                    $DEFAULT_VIEW .= '<tr><td>Direct Line</td><td><b>' . $direct_phone . '</b></td></tr>';
                }
                if (!empty($home_phone)) {
                    $DEFAULT_VIEW .= '<tr><td>Home Phone</td><td><b>' . $home_phone . '</b></td></tr>';
                }
                if (!empty($mobile)) {
                    $DEFAULT_VIEW .= '<tr><td>Mobile</td><td><b>' . $mobile . '</b></td></tr>';
                }
                if (!empty($fax)) {
                    $DEFAULT_VIEW .= '<tr><td>Fax</td><td><b>' . $fax . '</b></td></tr>';
                }
                if (!empty($address)) {
                    $DEFAULT_VIEW .= '<tr><td>Address</td><td><b>' . $address . '</b></td></tr>';
                }

                $DEFAULT_VIEW .= '
                    <tr><td><img src="/shared/icons/idcard-icon-sm.jpg"></td>
                    <td><a href="/en/private/directory/index.php?m[0]=people&m[1]=view&m[2]=outVcard&id=' . $id . '">Download Vcard</a></td></tr>
                    </table
                    </td>
                    </tr>
                    </table>';
        }

        return $DEFAULT_VIEW;
    }

    public function generateCardOutput($format)
    {
        $this->output_format = (string)$format;
        if ($this->output_format === 'vcf') {
            $this->output = (string)"BEGIN:VCARD\r\n";
            $this->output .= (string)"VERSION:2.1\r\n";
            $this->output .= (string)'N;ENCODING=QUOTED-PRINTABLE:' . $this->quotedPrintableEncode($this->last_name . ';' . $this->first_name . ';' . $this->addon) . "\r\n";
            $this->output .= (string)'FN;ENCODING=QUOTED-PRINTABLE:' . $this->quotedPrintableEncode($this->first_name . ' ' . $this->last_name . ' ' . $this->addon) . "\r\n";
            if (trim($this->nickname) !== '') {
                $this->output .= (string)'NICKNAME;ENCODING=QUOTED-PRINTABLE:' . $this->quotedPrintableEncode($this->nickname) . "\r\n";
            } // end if
            $this->output .= (string)'ORG' . $this->lang . ';ENCODING=QUOTED-PRINTABLE:' . $this->quotedPrintableEncode($this->organisation) . ';' . $this->quotedPrintableEncode($this->department) . "\r\n";
            if (trim($this->job_title) !== '') {
                $this->output .= (string)'TITLE' . $this->lang . ';ENCODING=QUOTED-PRINTABLE:' . $this->quotedPrintableEncode($this->job_title) . "\r\n";
            } // end if
            if (trim($this->note) !== '') {
                $this->output .= (string)'NOTE' . $this->lang . ';ENCODING=QUOTED-PRINTABLE:' . $this->quotedPrintableEncode($this->note) . "\r\n";
            } // end if
            if (trim($this->tel_work1_voice) !== '') {
                $this->output .= (string)'TEL;WORK;VOICE:' . $this->tel_work1_voice . "\r\n";
            } // end if
            if (trim($this->tel_work2_voice) !== '') {
                $this->output .= (string)'TEL;WORK;VOICE:' . $this->tel_work1_voice . "\r\n";
            } // end if
            if (trim($this->tel_home1_voice) !== '') {
                $this->output .= (string)'TEL;HOME;VOICE:' . $this->tel_home1_voice . "\r\n";
            } // end if
            if (trim($this->tel_cell_voice) !== '') {
                $this->output .= (string)'TEL;CELL;VOICE:' . $this->tel_cell_voice . "\r\n";
            } // end if
            if (trim($this->tel_car_voice) !== '') {
                $this->output .= (string)'TEL;CAR;VOICE:' . $this->tel_car_voice . "\r\n";
            } // end if
            if (trim($this->tel_additional) !== '') {
                $this->output .= (string)'TEL;VOICE:' . $this->tel_additional . "\r\n";
            } // end if
            if (trim($this->tel_pager_voice) !== '') {
                $this->output .= (string)'TEL;PAGER;VOICE:' . $this->tel_pager_voice . "\r\n";
            } // end if
            if (trim($this->tel_work_fax) !== '') {
                $this->output .= (string)'TEL;WORK;FAX:' . $this->tel_work_fax . "\r\n";
            } // end if
            if (trim($this->tel_home_fax) !== '') {
                $this->output .= (string)'TEL;HOME;FAX:' . $this->tel_home_fax . "\r\n";
            } // end if
            if (trim($this->tel_home2_voice) !== '') {
                $this->output .= (string)'TEL;HOME:' . $this->tel_home2_voice . "\r\n";
            } // end if
            if (trim($this->tel_isdn) !== '') {
                $this->output .= (string)'TEL;ISDN:' . $this->tel_isdn . "\r\n";
            } // end if
            if (trim($this->tel_preferred) !== '') {
                $this->output .= (string)'TEL;PREF:' . $this->tel_preferred . "\r\n";
            } // end if
            $this->output .= (string)'ADR;WORK:;' . $this->company . ';' . $this->work_street . ';' . $this->work_city . ';' . $this->work_region . ';' . $this->work_zip . ';' . $this->work_country . "\r\n";
            $this->output .= (string)'LABEL;WORK;ENCODING=QUOTED-PRINTABLE:' . $this->quotedPrintableEncode($this->company) . '=0D=0A' . $this->quotedPrintableEncode($this->work_street) . '=0D=0A' . $this->quotedPrintableEncode($this->work_city) . ', ' . $this->quotedPrintableEncode($this->work_region) . ' ' . $this->quotedPrintableEncode($this->work_zip) . '=0D=0A' . $this->quotedPrintableEncode($this->work_country) . "\r\n";
            $this->output .= (string)'ADR;HOME:;' . $this->home_street . ';' . $this->home_city . ';' . $this->home_region . ';' . $this->home_zip . ';' . $this->home_country . "\r\n";
            $this->output .= (string)'LABEL;HOME;ENCODING=QUOTED-PRINTABLE:' . $this->quotedPrintableEncode($this->home_street) . '=0D=0A' . $this->quotedPrintableEncode($this->home_city) . ', ' . $this->quotedPrintableEncode($this->home_region) . ' ' . $this->quotedPrintableEncode($this->home_zip) . '=0D=0A' . $this->quotedPrintableEncode($this->home_country) . "\r\n";
            $this->output .= (string)'ADR;POSTAL:;' . $this->postal_street . ';' . $this->postal_city . ';' . $this->postal_region . ';' . $this->postal_zip . ';' . $this->postal_country . "\r\n";
            $this->output .= (string)'LABEL;POSTAL;ENCODING=QUOTED-PRINTABLE:' . $this->quotedPrintableEncode($this->postal_street) . '=0D=0A' . $this->quotedPrintableEncode($this->postal_city) . ', ' . $this->quotedPrintableEncode($this->postal_region) . ' ' . $this->quotedPrintableEncode($this->postal_zip) . '=0D=0A' . $this->quotedPrintableEncode($this->postal_country) . "\r\n";
            if (trim($this->url_work) !== '') {
                $this->output .= (string)'URL;WORK:' . $this->url_work . "\r\n";
            } // end if
            if (trim($this->role) !== '') {
                $this->output .= (string)'ROLE' . $this->lang . ':' . $this->role . "\r\n";
            } // end if
            if (trim($this->birthday) !== '') {
                $this->output .= (string)'BDAY:' . $this->birthday . "\r\n";
            } // end if
            if (trim($this->email) !== '') {
                $this->output .= (string)'EMAIL;PREF;INTERNET:' . $this->email . "\r\n";
            } // end if
            if (trim($this->tel_telex) !== '') {
                $this->output .= (string)'EMAIL;TLX:' . $this->tel_telex . "\r\n";
            } // end if
            if (trim($this->photo) !== '') {
                $this->output .= (string)"PHOTO;JPEG;ENCODING=BASE64:\r\n" . $this->photo . "\r\n\r\n";
            } // end if

            $this->output .= (string)'REV:' . $this->rev . "\r\n";
            $this->output .= (string)"END:VCARD\r\n";
        } // end if output_format == 'vcf'
    } // end function

    /**
     * Loads the string into the variable if it hasn't been set before
     *
     * @desc Loads the string into the variable if it hasn't been set before
     * @param (string) $format  only vcf so far
     * @return (string) $output
     * @see generateCardOutput(), writeCardFile()
     * @access public
     * @since 1.000 - 2002/10/10
     */
    public function getCardOutput($format)
    {
        if (!isset($this->output) || $this->output_format != $format) {
            $this->generateCardOutput($format);
        } // end if
        return (string)$this->output;
    } // end function

    /**
     * Writes the string into the file and saves it to the download directory
     *
     * @desc Writes the string into the file and saves it to the download directory
     * @return (void)
     * @see generateCardOutput(), getCardOutput()
     * @access public
     * @since 1.000 - 2002/10/10
     */
    public function writeCardFile()
    {
        if (!isset($this->output)) {
            $this->generateCardOutput();
        } // end if
        $handle = fopen($this->download_dir . '/' . $this->card_filename, 'w');
        fputs($handle, $this->output);
        fclose($handle);
        $this->deleteOldFiles(30);
        if (isset($handle)) {
            unset($handle);
        }
    } // end function


    /**
     * Sends the right header information and outputs the generated content to
     * the browser
     *
     * @desc Sends the right header information
     * @param (string) $format  only vcf so far
     * @return (void)
     * @see getCardOutput()
     * @access public
     * @since 1.02 - 2002/12/23
     */
    public function outputFile($format = 'vcf')
    {
        if ($format === 'vcf') {
            header('Content-Type: text/x-vcard');
//			header("Content-Disposition: attachment; filename=vCard_" . date('Y-m-d_H-m-s') . ".vcf");
            header('Content-Disposition: inline; filename="vCard_' . $this->last_name . ',' . $this->first_name . '.vcf"');
            echo $this->getCardOutput('vcf');
        } // end if
    } // end function


    /**
     * Writes the string into the file and saves it to the download directory
     *
     * @desc Writes the string into the file and saves it to the download directory
     * @param (int) $time  Minimum age of the files (in seconds) before files get deleted
     * @return (void)
     * @see writeCardFile()
     * @access private
     * @since 1.000 - 2002/10/20
     */
    public function deleteOldFiles($time = 300)
    {
        if (!is_int($time) || $time < 1) {
            $time = (int)300;
        } // end if
        $handle = opendir($this->download_dir);
        while ($file = readdir($handle)) {
            if (!preg_match("^\.{1,2}$", $file) && !is_dir($this->download_dir . '/' . $file) && preg_match("/\.vcf/i", $file) && ((time() - filemtime($this->download_dir . '/' . $file)) > $time)) {
                unlink($this->download_dir . '/' . $file);
            } // end if
        } // end while
        closedir($handle);
        if (isset($handle)) {
            unset($handle);
        }
        if (isset($file)) {
            unset($file);
        }
    } // end function

    /**
     * Returns the full path to the saved file where it can be downloaded.
     *
     * Can be used for "header(Location:..."
     *
     * @desc Returns the full path to the saved file where it can be downloaded.
     * @return (string)  Full http path
     * @access public
     * @since 1.000 - 2002/10/20
     */
    public function getCardFilePath()
    {
        $path_parts = pathinfo($_SERVER['SCRIPT_NAME']);
        $port = (string)(($_SERVER['SERVER_PORT'] != 80) ? ':' . $_SERVER['SERVER_PORT'] : '');
        return (string)'http://' . $_SERVER['SERVER_NAME'] . $port . $path_parts['dirname'] . '/' . $this->download_dir . '/' . $this->card_filename;
    } // end function
} // end class vCard


/**
 * Class for accessing and manipulating TLD departments information
 * @package UsersAndGroups
 */
class tldDepartment
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
     * Check if empty
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    /**
     * Get SELECT mysql statement
     * @return string
     */
    public static function getSELECT(): string
    {
        return <<<EOF
SELECT *
EOF;
    }

    /**
     * Get FROM mysql statement
     * @return string
     */
    public static function getFROM(): string
    {
        return <<<EOF
FROM tld_departments
EOF;
    }

    /**
     * Get header
     * @return row
     */
    public function getHeader()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
WHERE id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Insert new department
     * @param array $a
     * @return int or string on error
     */
    public static function insert($a)
    {
        throw new Exception('This page has been migrated and should not be used anymore');
    }

    /**
     * Update department
     * @param array $a
     * @param array $fields (optional)
     * @return int or string on error
     */
    public function update($a, $fields = '')
    {
        throw new Exception('This page has been migrated and should not be used anymore');
    }

    /**
     * Delete department
     * @return string on error
     */
    public function delete()
    {
        throw new Exception('This page has been migrated and should not be used anymore');
    }

    /**
     * Get department by constraints
     * @param array $a constraints
     * @param array $opt options
     * @return array of rows
     */
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
            $ORDERBY = 'ORDER BY dpt';
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

    public static function getList()
    {
        return self::byConstraints('1=1');
    }

    public static function getListAsIdDepartment()
    {
        return array_column(self::getList(), 'dpt', 'id');
    }

    public static function getListAsDepartmentDepartment()
    {
        return array_column(self::getList(), 'dpt', 'dpt');
    }

    public function isUsed()
    {
        $queries = [];
        $queries[] = "SELECT * FROM people WHERE dpt_id=$this->itsID";
        foreach ($queries as $query) {
            $data = tldUtils::getSqlToAssocArray($query);
            if (count($data)) {
                return true;
            }
        }
        return false;
    }

    public function transfer($fromDepartment, $toDepartment)
    {
        throw new Exception('This page has been migrated and should not be used anymore');
    }

}

/**
 * Class for accessing and manipulating TLD User Function information
 * @package UsersAndGroups
 */
class tldFunction
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
     * Check if empty
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    /**
     * Get SELECT mysql statement
     * @return string
     */
    public static function getSELECT(): string
    {
        return <<<EOF
SELECT *
EOF;
    }

    /**
     * Get list of level function
     * @return array
     */
    public static function getLevelList(): array
    {
        return [
            'ALVEST STEERING COMMITTEE' => 'ALVEST STEERING COMMITTEE',
            'EXECUTIVES' => 'EXECUTIVES',
            'MANAGERS' => 'MANAGERS',
            'SUPERVISORS' => 'SUPERVISORS',
            'OTHER EMPLOYEES WITHOUT DIRECT REPORT' => 'OTHER EMPLOYEES WITHOUT DIRECT REPORT',
        ];
    }

    /**
     * Get FROM mysql statement
     * @return string
     */
    public static function getFROM(): string
    {
        return <<<EOF
FROM tld_functions
EOF;
    }

    /**
     * Get header
     * @return row
     */
    public function getHeader()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
WHERE id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Insert new function
     * @param array $a
     * @return int or string on error
     */
    public static function insert($a)
    {
        throw new Exception('This page has been migrated and should not be used anymore');
    }

    /**
     * Update function
     * @param array $a
     * @param array $fields (optional)
     * @return int or string on error
     */
    public function update($a, $fields = '')
    {
        throw new Exception('This page has been migrated and should not be used anymore');
    }

    /**
     * Delete function
     * @return string on error
     */
    public function delete()
    {
        throw new Exception('This page has been migrated and should not be used anymore');
    }

    /**
     * Get function by constraints
     * @param array $a constraints
     * @param array $opt options
     * @return array of rows
     */
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
            $ORDERBY = 'ORDER BY dsc';
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
        if ('smartyOptions' === $opt) {
            return tldUtils::getSqlToAssocArray($query, $opt, ['code', 'dsc']);
        }

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get list of Function by code
     * @param string $code
     * @return array
     */
    public function byCode($code)
    {
        $a = ['code' => $code];
        return self::byConstraints($a);
    }

    /**
     * Get list of Function by level
     * @param string $code
     * @return array
     */
    public function byLevel($level)
    {
        $a = ['level' => $level];
        return self::byConstraints($a);
    }

    /**
     * Get list of all Function
     * @return array
     */
    public static function getList($option = '')
    {
        return self::byConstraints('1=1', $option);
    }

    public static function getListAsIdDescription()
    {
        return array_column(self::getList(), 'dsc', 'id');
    }

    public static function getListAsCodeDescription()
    {
        return array_column(self::getList(), 'dsc', 'code');
    }

    public static function getListAsLevelDescription()
    {
        return array_column(self::getList(), 'dsc', 'level');
    }

    /**
     * Check if the function is used
     * @return bool
     */
    public function isUsed()
    {
        $queries = [];
        $queries[] = "SELECT * FROM people WHERE fct_id=$this->itsID";
        foreach ($queries as $query) {
            $data = tldUtils::getSqlToAssocArray($query);
            if (count($data)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get list of all Function by Level/subLevel/departement
     */
    public static function getFunctionListByLevelSubLevelAndDepartment(string $level, ?int $subLevelId, ?int $departmentId)
    {
        $WHERE = [];
        $WHERE[] = " people.hidden = 0 AND people.disabled = 'N' ";
        $JOIN = ' LEFT JOIN people ON fct.id = people.fct_id ';

        if ($departmentId !== null) {
            $WHERE[] = "people.dpt_id = $departmentId";
        }

        if ($subLevelId !== null) {
            if ($level === 'bu') {
                $WHERE[] = "people.bu_id = $subLevelId";
            } elseif ($level === 'division') {
                $JOIN .= ' LEFT JOIN locations ON people.bu_id = locations.id ';
                $JOIN .= ' LEFT JOIN tld_regions ON locations.region_id = tld_regions.id ';
                $JOIN .= ' LEFT JOIN tld_sub_divisions ON tld_regions.sub_division_id = tld_sub_divisions.id ';
                $WHERE[] = "tld_sub_divisions.division_id = $subLevelId";
            } elseif ($level === 'region') {
                $WHERE[] = "people.div_id = $subLevelId";
            } elseif ($level === 'subdivision') {
                $JOIN .= ' LEFT JOIN locations ON people.bu_id = locations.id ';
                $JOIN .= ' LEFT JOIN tld_regions ON locations.region_id = tld_regions.id ';
                $WHERE[] = "tld_regions.sub_division_id = $subLevelId";
            }
        }

        $whereClause = '';
        if (!empty($WHERE)) {
            $whereClause = ' WHERE ' . implode(' AND ', $WHERE);
        }

        $query = "SELECT DISTINCT fct.id
              FROM tld_functions AS fct
              $JOIN
              $whereClause";

        return json_encode(array_column(tldUtils::getSqlToAssocArray($query), 'id'), JSON_THROW_ON_ERROR );
    }

    /**
     * Update/transfer ALL people function field from one function to another
     */
    public function transfer($fromID, $toID)
    {
        throw new Exception('This is no longer used');
    }

    public static function getUserlist($code, $options = '', $property = 'fullname')
    {
        $query = <<<EOF
        SELECT
    people.*,
    CONCAT(lastname,', ',firstname) AS fullname,
    fct.code
FROM
    people
    LEFT JOIN tld_functions AS fct ON fct.id=people.fct_id
WHERE people.hidden=0 AND fct.code LIKE '$code'
ORDER BY fullname
EOF;
        if ($options === 'smartyOptions') {
            return tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['id', $property]);
        }

        return tldUtils::getSqlToAssocArray($query);
    }

}

/**
 * Class for accessing and manipulating TLD Division information
 * @package UsersAndGroups
 */
class tldRegion
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

    public function getDivision()
    {
        return $this->itsHeader['division'];
    }

    public function getRepID()
    {
        return $this->itsHeader['repid'];
    }

    /**
     * Check if empty
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    /**
     * Get SELECT mysql statement
     * @return string
     */
    public static function getSELECT(): string
    {
        return <<<EOF
SELECT
    *,
    (SELECT CONCAT(firstname,', ',lastname) FROM people
        WHERE id=tld_regions.repid
    ) AS rep_fullname
EOF;
    }

    /**
     * Get FROM mysql statement
     * @return string
     */
    public static function getFROM(): string
    {
        return <<<EOF
FROM tld_regions
EOF;
    }

    /**
     * Get header
     * @return row
     */
    public function getHeader()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
WHERE id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Insert new division
     * @param array $a
     * @return int or string on error
     */
    public static function insert($a)
    {
        throw new Exception('This page has been migrated and should not be used anymore');
    }

    /**
     * Update division
     * @param array $a
     * @param array $fields (optional)
     * @return int or string on error
     */
    public function update($a, $fields = '')
    {
        throw new Exception('This page has been migrated and should not be used anymore');
    }

    /**
     * Delete division
     * @return string on error
     */
    public function delete()
    {
        throw new Exception('This page has been migrated and should not be used anymore');
    }

    /**
     * Get division by constraints
     * @param array $a constraints
     * @param array $opt options
     * @return array of rows
     */
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
            $ORDERBY = 'ORDER BY division';
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

    public function getDivisionId()
    {
        $FROM = self::getFROM();
        $query = <<<EOF
SELECT tld_divisions.id
$FROM
LEFT JOIN tld_sub_divisions ON tld_regions.sub_division_id = tld_sub_divisions.id
LEFT JOIN tld_divisions ON tld_sub_divisions.division_id = tld_divisions.id
WHERE tld_regions.id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public static function getList()
    {
        return self::byConstraints('1=1');
    }

    public static function getListAsIdDivision()
    {
        return array_column(self::getList(), 'division', 'id');
    }

    public static function getListAsDivisionDivision()
    {
        return array_column(self::getList(), 'division', 'division');
    }

    public function getLocations()
    {
        return tldLocation::byRegionID($this->itsID);
    }

    public function getLocationsAsIdLocation()
    {
        return array_column($this->getLocations(), 'location', 'id');
    }

}

class tldSubDivision
{
    public static function getList()
    {
        return array_column(tldUtils::getSqlToAssocArray('SELECT id, name FROM `tld_sub_divisions`'), 'name', 'id');
    }
}

class tldDivision
{
    public static function getList()
    {
        return array_column(tldUtils::getSqlToAssocArray('SELECT id, name FROM `tld_divisions`'), 'name', 'id');
    }
}
