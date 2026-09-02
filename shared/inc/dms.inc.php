<?php

/**
 * Need these functions
 */
include_once('common.inc.php');

class tldDMS
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
     * Get owner ID
     * @return int
     */
    public function getOwnerID()
    {
        return $this->itsHeader['owner_id'];
    }

    /**
     * Get the portal access
     * @return string
     */
    public function getPortal()
    {
        return $this->itsHeader['portal'];
    }

    /**
     * Get the access type
     * @return string
     */
    public function getAccessType()
    {
        return $this->itsHeader['access_type'];
    }

    /**
     * Get the System reference used
     * @return string
     */
    public function getSystemRef()
    {
        return $this->itsHeader['sysref'];
    }

    public function getTitle()
    {
        return $this->itsHeader['title'];
    }

    public function getTypeID()
    {
        return $this->itsHeader['type_id'];
    }

    public function getTypeDescription()
    {
        return $this->itsHeader['typeDesc'];
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
     * Refresh DMS header info
     */
    public function refresh()
    {
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Get SELECT mysql statement
     * @return string
     */
    public static function getSELECT(): string
    {
        return <<<EOF
SELECT
    dms.*,
    dms_type.code,
    dms_type.short_desc AS typeDesc,
    dms_type.cycle,
    dms_revision.dt AS dt_rev,
    dms_revision.revision,
    dms_revision.src_fid,
    dms_revision.pub_fid,
    dms_revision.seq_id,
    dms_revision.purpose,
    CONCAT(people.firstname,' ',people.lastname) AS ownerFullname,
    people.dpt_id AS dpt_id,
    owner_location.location AS owner_bu_name
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
    dms
    LEFT JOIN dms_type ON dms.type_id=dms_type.id
    LEFT JOIN dms_revision ON dms.rev_id=dms_revision.id
    LEFT JOIN people ON dms.owner_id=people.id
    LEFT JOIN locations AS owner_location ON people.bu_id=owner_location.id
    LEFT JOIN tld_regions AS regions ON regions.id=people.div_id
    LEFT JOIN tld_sub_divisions AS sub_divisions ON sub_divisions.id=regions.sub_division_id
    LEFT JOIN tld_divisions AS divisions ON divisions.id=sub_divisions.division_id
EOF;
    }

    /**
     * Get header info
     * @return array
     */
    public function getHeader()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
WHERE dms.id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Get status list
     * @return array
     */
    public static function getStatusList()
    {
        return ['REVISION', 'APPROVAL', 'ACTIVE', 'EXPIRED', 'ARCHIVE'];
    }

    /**
     * Get DMS status
     * @return string
     */
    public function getStatus()
    {
        return $this->itsHeader['status'];
    }

    /**
     * Get allowed status list from actual status
     * @return array
     */
    public function getAllowedStatus()
    {
        $allowedStatus = [];
        switch ($this->getStatus()) {
            case 'REVISION':
                return ['APPROVAL', 'ACTIVE'];
                break;
            case 'APPROVAL':
                return ['ACTIVE', 'REVISION'];
                break;
            case 'ACTIVE':
                return ['EXPIRED', 'ARCHIVE'];
                break;
            case 'EXPIRED':
                return ['REVISION', 'ARCHIVE'];
                break;
        }
        return $allowedStatus;
    }

    /**
     * Set the DMS status
     */
    private function setStatus($newStatus)
    {
        return $this->update(['status' => $newStatus]);
    }

    /**
     * Method to use to update DMS status
     * Include additional actions regarding the status
     * @param string $newStatus
     * @param int $uid
     * @return string on error
     */
    public function updateStatus($newStatus, $uid, $log = null)
    {
        global $DMS_URL;
        if (!in_array($newStatus, $this->getAllowedStatus()) || empty($newStatus)) {
            return "Status '$newStatus' not allowed";
        }
        $e = $this->setStatus($newStatus);
        if (is_string($e)) {
            return $e;
        }
        // Add log in all cases
        $log = empty($log) ? $newStatus : "$newStatus<br>$log";
        $this->addLogEntry($uid, $log);
        // For notifications, prepare data
        $dmsURL = "$DMS_URL/index.php?m[0]=view&id=" . $this->itsID;
        $subject = sprintf(_('Status changed from %s to %s'), $this->getStatus(), $newStatus);
        $body = <<<EOF
<p>This Document is notified to yosu because you belong to the notification list.</p>
<p>DMS#{$this->itsID} has been set to $newStatus.</p>
<p>If you need more information about this document please <a href="$dmsURL">click here to view</a></p>
<p><i>Note: This DMS may be confidential. You might be required to login with your intranet account before to access it.</i></p>
EOF;

        // Specific actions on status change
        switch ($newStatus) {
            case 'REVISION':
                // Notify
                $body = <<<EOF
<p>This Document is back to review.</p>
<p>If you have any comments or suggestions, please place them in the comment section on the document DMS home page as soon as possible.</p>
<p><a href="$dmsURL">View DMS#{$this->itsID}</a></p>
<p><i>Note: This DMS may be confidential. You might be required to login with your intranet account before to access it.</i></p>
EOF;
                $this->notifyOwner($subject, $body);
                break;
            case 'ACTIVE':
                // Update field last activation date
                $dt = date('Y-m-d h:i:s');
                if ($this->getStatus() === 'REVISION') {
                    $this->update(['dt_act' => $dt]);
                    break;
                }
                // Set the revision
                $revInfo = $this->getApprovalRevision();
                $rev = new tldDMSRevision($revInfo['id']);
                if ($rev->isEmpty()) {
                    $e .= _('Can not found approved revision');
                    break;
                }
                $this->update(['dt_act' => $dt, 'rev_id' => $rev->getID()]);
                // Notify
                $body = <<<EOF
<p>This Document is notified to you.</p>
<p>As you belong to the notification list, please read or read again this revision document by clicking on the link below.</p>
<p><a href="$dmsURL">View DMS#{$this->itsID}</a></p>
<p><i>Note: This DMS may be confidential. You might be required to login with your intranet account before to access it.</i></p>
<p>Revision purpose:<br/>{$rev->itsHeader['purpose']}</p>
EOF;
                $this->notifyAll($subject, $body);
                // Log recipients notification to the revision
                $recipients = $this->getRecipientsNotification();
                $log = 'ACTIVE revision notification sent to:<br>' . implode(',', $recipients);
                $rev->addLogEntry($uid, TldDatabase::escape($log), 1);
                break;
            case 'EXPIRED':
            case 'ARCHIVE':
                // Notify owner & supervisor
                $owner = new tldUser($this->getOwnerID());
                $supervisor = new tldUser($owner->getSupervisor());
                /* Comment this section because of ticket 589380 but pretty sure they are gonna ask it back
                $this->notifyOwner($subject,$body,array($supervisor->getEmail()));*/
                // Get actual revision
                $rev = new tldDMSRevision($this->itsHeader['rev_id']);
                // Log notification to the revision
                $recipients = [$supervisor->getEmail(), $owner->getEmail()];
                $log = "$newStatus revision notification NOT sent to:<br>" . implode(',', $recipients);
                $rev->addLogEntry($uid, TldDatabase::escape($log));
                break;
        }
        // refresh
        $this->refresh();
        // return error if any
        if (!empty($e)) {
            return $e;
        }
        return;
    }

    /**
     * @param array $a
     * @return int|string|void
     */
    public static function insert($a)
    {
        if (empty($a)) {
            return;
        }

        $a = array_merge([
            'portal' => 'TLD',
            'access_type' => 'PUBLIC',
        ], $a);

        $fields = ['parent_id', 'dt_act', 'owner_id', 'title', 'subject', 'description', 'periodicity', 'lang', 'type_id', 'sysref', 'portal', 'access_type', 'rev_id'];

        $SET = tldUtils::getSqlSet($a, $fields);

        $query = <<<SQL
INSERT INTO dms SET dt=NOW(), status='REVISION', $SET
SQL;

        return tldUtils::sqlInsert($query);
    }

    /**
     * Update DMS record info
     * @param array $a
     * @param array $fields
     * @return string on error
     */
    public function update($a, $fields = '')
    {
        if (empty($a)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "UPDATE dms SET $SET WHERE id=$this->itsID";
        return tldUtils::sqlQuery($query);
    }

    /**
     * Delete all DMS info
     * @return string on error
     */
    public function delete()
    {
        // Check if no hard link
        if (count($this->getDependency())) {
            return 'There is still dependency to manage by MIS';
        }
        // log
        tldUtils::log_event("DMS#{$this->itsID} DELETE PROCESS");
        // Delete ACL
        $e = $this->deleteAllAclRules();
        // Delete Approver
        $e .= $this->deleteApprovers();
        // Delete Revisions
        $revList = $this->getRevisions();
        foreach ($revList as $revVal) {
            $rev = new tldDMSRevision($revVal['id']);
            $e .= $rev->delete();
        }
        // Delete Coverages
        // --- department
        $e .= $this->deleteDepartments();
        // --- BU
        $e .= $this->deleteBuList();
        // Delete DMS
        $query = "DELETE FROM dms WHERE id=$this->itsID";
        $e .= tldUtils::sqlExecute($query);
        // Return if error
        if (!empty($e)) {
            return $e;
        }
        // End log
        tldUtils::log_event("DMS#{$this->itsID} DELETE PROCESS FINISHED");
        return;
    }

    public static function downloadByPortal($id, $portal, $filenameFromDMS, $user)
    {
        if (empty($id)) {
            return 'ERROR: ID is missing';
        }

        if (empty($portal)) {
            return 'ERROR: No portal dimension';
        }

        $dms = new tldDMS($id);
        if ($dms->isEmpty()) {
            return 'ERROR: No records found';
        }

        if (is_array($portal)) {
            if (!in_array($dms->getPortal(), $portal, true)) {
                return 'ERROR: No records found';
            }
        } else if ($dms->getPortal() !== $portal) {
            return 'ERROR: No records found';
        }

        // same logic than in DMS portal
        if ($dms->getAccessType() !== 'PUBLIC') {
            $owner = new tldUser($dms->getOwnerID());
            $erp = tldLocation::getERPByID($owner->getBUID());
            $allowedUserList = tldUtils::optionsByKeyValue($dms->getAllowedUserList(),"id","id");

            if (($user !== $owner) && (!$user->isInGroupLevel("role_QAM",$erp)) && (!in_array($user->getID(), $allowedUserList))){
                return 'ERROR: No records found or access denied';
            }
        }

        $revActual = $dms->getActiveRevisionObj();
        if ((int)$revActual->getPubFileID() === 0) {
            return 'ERROR: Document not available yet';
        }

        $file = new tldFile($revActual->getPubFileID());
        if (!$file->isFile()) {
            return 'File not found';
        }

        $file->download(true === $filenameFromDMS ? sprintf('%s.%s', $dms->getTitle(), $file->getExtension()) : null);
    }

    /**
     * Get DMS by constraints
     * @param array $a constraints
     * @param array $opt options
     * @return array of rows
     */
    public static function byConstraints($a, $opt = [])
    {
        if (empty($a)) {
            return 'empty parameter';
        }
        $HAVING = is_array($a) ? 'HAVING ' . tldUtils::constructWhere($a) : "HAVING $a";
        $WHERE = (!empty($opt['where'])) ? 'WHERE ' . $opt['where'] : '';
        $ORDERBY = !empty($opt['orderBy']) ? "ORDER BY {$opt['orderBy']}" : 'ORDER BY id,title,sysref';
        $LIMIT = !empty($opt['limit']) ? "LIMIT {$opt['limit']}" : '';

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

    public static function byExternalPortalByConstraints($portal, $a)
    {
        $WHERE = [];
        // Default constraints by portal
        $WHERE[] = <<<EOF
dms.status<>'ARCHIVE'
AND (
    dms.status='ACTIVE'
    OR (dms.rev_id<>0 AND dms_revision.revision>=1)
)
EOF;
        switch ($portal) {
            case 'EXTRANET':
                $WHERE[] = "portal LIKE 'EXTRANET'";
                break;
            case 'EVENDOR':
                $WHERE[] = "portal LIKE 'EVENDOR'";
                break;
            default:
                return;
                break;
        }
        // Add constraints from params
        if (is_array($a)) {
            $WHERE[] = tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE[] = $a;
        }
        return self::byConstraints(implode(' AND ', $WHERE));
    }

    /**
     * Get list of DMS by Owner ID
     * @param int $uid
     * @param mixed string array $a
     * @return array
     */
    public static function byOwnerIdByConstraints($uid, $a = '')
    {
        $WHERE = "owner_id=$uid";
        // Check other constraints
        if (is_array($a)) {
            $WHERE .= ' AND ' . tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE .= " AND $a";
        }
        return self::byConstraints($WHERE);
    }

    public static function byOpenSeqAssigneeIdByConstraints($uid, $a = '')
    {
        $WHERE = <<<EOF
dms.id IN (SELECT parent_id FROM tasks 
    WHERE seq='Y' AND module LIKE 'DMS' AND status<>'CLOSED' 
    AND assignee=$uid
)
EOF;
        // Check other constraints
        if (is_array($a)) {
            $WHERE .= ' AND ' . tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE .= " AND $a";
        }
        return self::byConstraints($WHERE);
    }

    public static function byNotificationUserIdByConstraints($uid, $a = '')
    {
        $WHERE = <<<EOF
$uid IN (
    SELECT people.id FROM people 
    WHERE people.hidden=0 AND people.disabled='N' AND EXISTS(
        SELECT mod_org.id
        FROM mod_org
        WHERE
            mod_org.parent_id = dms.id
            AND module LIKE 'DMS'
            AND name LIKE 'notification'
            AND IF( mod_org.division_id=0, 1=1, mod_org.division_id=divisions.id )
            AND IF( mod_org.subdivision_id=0, 1=1, mod_org.subdivision_id=sub_divisions.id )
            AND IF( mod_org.region_id=0, 1=1, mod_org.region_id=regions.id )
            AND IF( mod_org.bu_id=0, 1=1, mod_org.bu_id=people.bu_id )
            AND IF( mod_org.dpt_id=0, 1=1, mod_org.dpt_id=people.dpt_id )
            AND IF( mod_org.fct_id=0, 1=1, mod_org.fct_id=people.fct_id )
            AND dms.status != 'ARCHIVE'
        )
)
EOF;
        // Check other constraints
        if (is_array($a)) {
            $WHERE .= ' AND ' . tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE .= " AND $a";
        }
        return self::byConstraints($WHERE);
    }

    /**
     * Transfer owner to another user
     * @param int $from userid
     * @param int $to userid
     * @return string on error
     */
    public static function transferOwnerFromTo($from, $to)
    {
        $query = "UPDATE dms SET owner_id=$to WHERE owner_id=$from";
        return tldUtils::sqlQuery($query);
    }

    public static function transferApproverFromTo($from, $to)
    {
        $query = "UPDATE dms_approver SET uid=$to WHERE uid=$from";
        return tldUtils::sqlQuery($query);
    }

    /**
     * Get Parent Family ID
     * @return int
     */
    public function getRootParentIDFamily()
    {
        if (empty($this->itsID)) {
            return null;
        }
        // Get first parent
        $parent_id = $this->getParentID();
        if ($parent_id == 0) {
            return $this->getID();
        }
        // if first parent found, loop and search parent until we found the last one
        while ($parent_id <> 0) {
            $dms = new tldDMS($parent_id);
            if ($dms->isEmpty()) {
                break;
            }
            $parent_id = $dms->getParentID();
        }
        return $dms->getID();
    }

    /**
     * Get list of DMS record in family tree
     * @return array of rows
     */
    public function getFamilyTree()
    {
        if (empty($this->itsID)) {
            return [];
        }
        // Get root parent of the family
        $rootParentID = $this->getRootParentIDFamily();
        $parent = new tldDMS($rootParentID);
        $result[$parent->getID()] = ['header' => $parent->itsHeader, 'level' => 0];
        // Loop until we found all childs
        $parent->getChildListRec($result);
        return $result;
    }

    /**
     * Get child tree recursively
     * @param array $result (passed by reference)
     */
    public function getChildListRec(&$result, $level = 0)
    {
        if (empty($this->itsID)) {
            return [];
        }
        $level++;
        $rows = $this->getChildList();
        if (count($rows) == 0) {
            return;
        }
        foreach ($rows as $row) {
            $result[$row['id']] = ['header' => $row, 'level' => $level];
            $dms = new tldDMS($row['id']);
            $dms->getChildListRec($result, $level);
        }
    }

    /**
     * Get list of child
     * @return array of rows
     */
    public function getChildList()
    {
        if (empty($this->itsID)) {
            return;
        }
        $a = ['parent_id' => $this->itsID];
        return self::byConstraints($a);
    }

    /**
     * Check if a specific customer ID is part of the family
     * @param int $cid
     * @return boolean
     */
    public function isInFamily($cid)
    {
        $listFamilyID = array_keys($this->getCustomerFamilyTree());
        if (in_array($cid, $listFamilyID)) {
            return true;
        }
        return false;
    }

    /**
     * Get list of recipients for DMS notifications
     * @return array
     */
    public function getRecipientsNotification()
    {
        $to = [];
        // Add owner
        $owner = new tldUser($this->getOwnerID());
        $to[] = $owner->getEmail();
        // Add from notification rules
        $notUserList = $this->getNotificationUserList();
        foreach ($notUserList as $notUser) {
            if (empty($notUser['email']) || in_array($notUser['email'], $to)) {
                continue;
            }
            $to[] = $notUser['email'];
        }
        return $to;
    }

    /**
     * Notify all people concerned
     * @param string $subject
     * @param string $body
     */
    public function notifyAll($subject, $body)
    {
        $recipientsGroups = array_chunk($this->getRecipientsNotification(), 100);
        $body .= $this->getPrintVersion();
        $subject = "DMS#{$this->itsID} - {$this->itsHeader['subject']} - $subject";

        foreach ($recipientsGroups as $recipients) {
            $this->sendEmail($recipients, $subject, $body);
        }
    }

    /**
     * Notify owner
     * @param string $subject
     * @param string $body
     */
    public function notifyOwner($subject, $body, $cc = null)
    {
        $owner = new tldUser($this->getOwnerID());
        $to = $owner->getEmail();
        $body .= $this->getPrintVersion();
        $subject = "DMS#{$this->itsID} - {$this->itsHeader['subject']} - $subject";
        return $this->sendEmail($to, $subject, $body, null, $cc);
    }

    /**
     * Generic method to send email notification
     * @param string $to
     * @param string $subject
     * @param string $body
     * @param string $file
     * @param string $cc
     */
    public function sendEmail($to, $subject, $body, $file = '', $cc = '', $bcc = '')
    {
        return tldUtils::emailAttachment(
            $to,
            'noreply@tld-gse.com',
            $subject,
            $body,
            $file,
            $cc,
            $bcc
        );
    }

    /**
     * Get the generic DMS report view
     * @return string
     */
    public function getPrintVersion()
    {
        global $DMS_URL;
        $report = new tldAssocTable(
            $this->getHeader(),
            [
                'id' => _('DMS#'),
                'parent_id' => _('Parent') . ' DMS#',
                'dt' => _('Date creation'),
                'dt_act' => _('Last activation'),
                'ownerFullname' => _('Owner'),
                'title' => _('Title'),
                'subject' => _('Subject'),
                'description' => _('Description'),
                'lang' => _('Language'),
                'status' => _('Status'),
                'typeDesc' => _('Type'),
                'periodicity' => _('Revision periodicity'),
                'sysref' => _('System reference'),
                'revision' => _('Revision'),
            ],
            [
                'title' => _('DMS General information'),
                'links' => [
                    'id' => "$DMS_URL/index.php?m[0]=view&id=",
                    'parent_id' => "$DMS_URL/index.php?m[0]=view&id=",
                ],
            ]
        );
        return $report->fetch();
    }

    /**
     * Count records by Status by Type using constraints if needed
     * @param mixed array string $a
     * @return array
     */
    public static function countByStatusTypeByConstraints($a = '')
    {
        $HAVING = '';
        if (is_array($a)) {
            $HAVING = 'WHERE ' . tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $HAVING = "WHERE $a";
        }
        $query = <<<EOF
SELECT
    COUNT(dms.id) AS num,
    dms.status AS status,
    dms_type.short_desc AS type
FROM
    dms
    LEFT JOIN dms_type ON dms.type_id=dms_type.id
    LEFT JOIN people ON dms.owner_id = people.id
$HAVING
GROUP BY
    dms.status,
    dms.type_id
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Count records by Status by Type, by BU & DPT id, using constraints if needed
     * @param int $dptid
     * @param int $buid
     * @param mixed array string $a
     * @return array
     */
    public static function countByStatusTypeByDptIdBuIdByConstraints($dptid, $buid, $a = '')
    {
        $WHERE = '';
        if (is_array($a)) {
            $WHERE .= ' AND ' . tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE .= " AND $a";
        }
        $a = <<<EOF
$dptid IN (SELECT dptid FROM dms_department WHERE parent_id=dms.id)
AND $buid IN (SELECT buid FROM dms_bu WHERE parent_id=dms.id)
$WHERE
EOF;
        return self::countByStatusTypeByConstraints($a);
    }

    /**
     * Get list by Status by Type using constraints if needed
     * @param string $status
     * @param string $type
     * @param mixed array string $a
     * @return array
     */
    public static function byStatusTypeByConstraints($status, $type, $a = '')
    {
        $WHERE = [];
        if ($status !== 'ALL') {
            $WHERE[] = "status LIKE '$status'";
        }
        if ($type !== 'ALL') {
            $WHERE[] = "typeDesc LIKE '$type'";
        }
        $WHERE = $WHERE ? implode(' AND ', $WHERE) : '1=1';

        if (is_array($a)) {
            $WHERE .= ' AND ' . tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE .= " AND $a";
        }
        return self::byConstraints($WHERE);
    }

    public static function countByBuDepartmentByConstraints($a = '')
    {
        $HAVING = '';
        if (is_array($a)) {
            $HAVING = 'WHERE ' . tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $HAVING = "WHERE $a";
        }
        $query = <<<EOF
SELECT
    COUNT(dms.id) AS num,
    bu.location AS bu,
    dpt.dpt
FROM
    dms
    LEFT JOIN dms_bu ON dms.id=dms_bu.parent_id
    LEFT JOIN locations AS bu ON dms_bu.buid=bu.id
    LEFT JOIN dms_department ON dms.id=dms_department.parent_id
    LEFT JOIN tld_departments AS dpt ON dms_department.dptid=dpt.id
$HAVING
GROUP BY
    bu.location,
    dpt.dpt
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byBuDepartmentByConstraints($bu, $dpt, $a = '')
    {
        $WHERE = [];
        if ($bu !== 'ALL') {
            $WHERE[] = <<<EOF
'$bu' IN(
    SELECT bu.location FROM locations AS bu 
    LEFT JOIN dms_bu ON dms_bu.buid=bu.id
    WHERE dms.id=dms_bu.parent_id
)
EOF;
        }
        if ($dpt !== 'ALL') {
            $WHERE[] = <<<EOF
'$dpt' IN(
    SELECT dpt FROM tld_departments AS dpt 
    LEFT JOIN dms_department ON dms_department.dptid=dpt.id
    WHERE dms.id=dms_department.parent_id
)
EOF;
        }
        $WHERE = $WHERE ? implode(' AND ', $WHERE) : '1=1';

        if (is_array($a)) {
            $WHERE .= ' AND ' . tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE .= " AND $a";
        }
        return self::byConstraints($WHERE);
    }

    public static function byLatest($num = 10)
    {
        return self::byConstraints(
            '1=1',
            [
                'limit' => $num,
                'orderBy' => 'id DESC',
            ]
        );
    }

    /**
     * Get DMS statistic for a specific user
     * @param int $uid user id
     * @param mixed string or array $a constraints
     * @return row
     */
    public static function getUserStatsByConstraints($uid, $a = null)
    {
        $WHERE = '';
        if (is_array($a)) {
            $WHERE = 'AND ' . tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE = "AND $a";
        }
        $query = <<<EOF
SELECT
    COUNT(*) AS num_owner,
    SUM(IF(status LIKE 'EXPIRED',1,0)) AS num_owner_expired,
    SUM(IF(status LIKE 'REVISION',1,0)) AS num_owner_revision,
    SUM(IF(status LIKE 'APPROVAL',1,0)) AS num_owner_approval,
    SUM(IF(status LIKE 'ACTIVE' AND 
        PERIOD_DIFF( DATE_FORMAT(DATE_ADD(dt_act, INTERVAL periodicity MONTH),'%Y%m'), DATE_FORMAT(NOW(),'%Y%m') )<1,
        1,0)
    ) AS num_owner_expired_1_month,
    (SELECT COUNT(*) FROM tasks 
        WHERE seq='Y' AND status<>'CLOSED' AND module LIKE 'DMS'
        AND assignee=$uid
    ) AS num_seq_approval,
    (SELECT COUNT(*) FROM dms AS dms2 WHERE
        $uid IN (
            SELECT people.id FROM people 
            LEFT JOIN tld_regions AS regions ON regions.id=people.div_id
            LEFT JOIN tld_sub_divisions AS sub_divisions ON sub_divisions.id=regions.sub_division_id
            LEFT JOIN tld_divisions AS divisions ON divisions.id=sub_divisions.division_id
            WHERE people.hidden=0 AND people.disabled='N' AND EXISTS(
                SELECT mod_org.id
                FROM mod_org
                WHERE
                    mod_org.parent_id = dms2.id
                    AND module LIKE 'DMS'
                    AND name LIKE 'notification'
                    AND IF( mod_org.division_id=0, 1=1, mod_org.division_id=divisions.id )
                    AND IF( mod_org.subdivision_id=0, 1=1, mod_org.subdivision_id=sub_divisions.id )
                    AND IF( mod_org.region_id=0, 1=1, mod_org.region_id=regions.id )
                    AND IF( mod_org.bu_id=0, 1=1, mod_org.bu_id=people.bu_id )
                    AND IF( mod_org.dpt_id=0, 1=1, mod_org.dpt_id=people.dpt_id )
                    AND IF( mod_org.fct_id=0, 1=1, mod_org.fct_id=people.fct_id )
                )
        )
        $WHERE
    ) AS num_to_know
FROM
    dms
WHERE
    owner_id=$uid
    AND status != 'ARCHIVE' 
    $WHERE
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }


    /********************************
     *          MOD MODULES
     *******************************/

    /**
     * Get associated log entries from mod_log system
     * @param int $num_log
     * @return array
     */
    public function getLog($num_log = 0)
    {
        return tldModLog::byParent($this->itsID, 'DMS', $num_log);
    }

    /**
     * Add log entries into mod_log system
     * @param int $uid
     * @param string $uid
     * @param int $num_log
     * @return int or string on error
     */
    public function addLogEntry($uid, $comment, $num_log = 0)
    {
        $a = [
            'parent_id' => $this->itsID,
            'module' => 'DMS',
            'poster' => $uid,
            'comment' => $comment,
            'log_num' => $num_log,
        ];
        return tldModLog::insert($a);
    }

    /**
     * Add links attached to the DMS
     * @param string module
     * @param int ref ID
     * @return int or string on error
     */
    public function addLink($module, $refID)
    {
        return tldModLink::insert(
            'DMS',
            $this->itsID,
            $module,
            $refID
        );
    }

    /**
     * Get links attached to the DMS
     * @return array
     */
    public function getLinks()
    {
        return tldModLink::byParent($this->itsID, 'DMS');
    }

    /**
     * Get Tasks attached to the DMS
     * @return array
     */
    public function getTasks()
    {
        return tldTask::byParent($this->itsID, 'DMS', 'ALL');
    }

    /**
     * Add dependency
     * @param string $portal
     * @param string $location
     * @return mixed string/int
     */
    public function addDependency($portal, $location)
    {
        return tldModList::insert([
            'parent_id' => $this->itsID,
            'module' => 'DMS',
            'list_name' => 'dependency',
            'list_key' => $portal,
            'value' => $location,
        ]);
    }

    /**
     * Get list of dependency
     * @return array
     */
    public function getDependency()
    {
        return tldModList::byParent($this->itsID, 'DMS', ['list_name' => 'dependency']);
    }

    /**
     * Delete dependency
     * @param integer tldModList ID
     * @return string on error
     */
    public function deleteDependency($lid)
    {
        return tldModList::delete($lid);
    }
    /********************************
     *      DMS FIXED LISTING
     *******************************/

    /**
     * Get list of DMS system reference
     * @return array
     */
    public static function getSystemRefList()
    {
        return [
            'ISO:9001' => 'ISO:9001',
            'ISO:14001 & ISO:45001' => 'ISO:14001 & ISO:45001',
            'ISO:27001' => 'ISO:27001',
            'NOT ISO 9001/14001/45001/27001' => 'NOT ISO 9001/14001/45001/27001',
        ];
    }

    /**
     * Get list of language
     * @return array
     */
    public static function getLangList()
    {
        return [
            'en' => _('English'),
            'fr' => _('French'),
            'zh' => _('Chinese'),
            'en-fr' => _('English') . ' & ' . _('French'),
            'en-zh' => _('English') . ' & ' . _('Chinese'),
            'fr-zh' => _('French') . ' & ' . _('Chinese'),
            'en-fr-zh' => _('English') . ' & ' . _('French') . ' & ' . _('Chinese'),
            'ge' => _('German'),
            'jp' => _('Japanese'),
            'pt' => _('Portuguese'),
            'ru' => _('Russian'),
            'sp' => _('Spanish'),
        ];
    }

    public static function getSalesMaterialLangList()
    {
        return [
            'zh' => _('Chinese'),
            'en' => _('English'),
            'fr' => _('French'),
            'ge' => _('German'),
            'jp' => _('Japanese'),
            'pt' => _('Portuguese'),
            'ru' => _('Russian'),
            'sp' => _('Spanish'),
        ];
    }

    /**
     * Get list of portals
     * @return array
     */
    public static function getPortalList()
    {
        return [
            'INTRANET' => _('INTRANET'),
            'EXTRANET' => _('EXTRANET'),
            'EVENDOR' => _('EVENDOR'),
        ];
    }

    /**
     * Get list of access type
     * @return array
     */
    public static function getAccessTypeList()
    {
        return [
            'CONFIDENTIAL' => _('CONFIDENTIAL'),
            'PUBLIC' => _('PUBLIC'),
        ];
    }

    /**
     * Get list of month Periodicity
     * @return array
     */
    public static function getPeriodicityList()
    {
        return [
            1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6,
            7 => 7, 8 => 8, 9 => 9, 10 => 10, 11 => 11, 12 => 12,
            24 => 24, 36 => 36];
    }

    /**
     * Return the document path location
     * @return string
     */
    public static function getFilePath()
    {
        return 'dms';
    }

    /**************************************
     *      DMS linked info functions
     **************************************/

    /**
     * Add BU
     * @param array $a
     * @return int or string on error
     */
    public function addBu($a)
    {
        $a['parent_id'] = $this->itsID;
        return tldDMSBu::insert($a);
    }

    /**
     * Delete BU by ID
     * @param int $lid
     * @return string on error
     */
    public function deleteBuByID($lid)
    {
        $bu = new tldDMSBu($lid);
        if ($bu->itsHeader['parent_id'] <> $this->itsID) {
            return _('This BU is not attached to this DMS');
        }
        return $bu->delete();
    }

    public function deleteBuList()
    {
        $buList = $this->getBu();
        $e = null;
        foreach ($buList as $buVal) {
            $e .= $this->deleteBuByID($buVal['id']);
        }
        if (!empty($e)) {
            return $e;
        }
    }

    /**
     * Get BU list
     * @return array
     */
    public function getBu()
    {
        return tldDMSBu::byParent($this->itsID);
    }

    /**
     * Add approver step
     * @param array $a
     * @return int or string on error
     */
    public function addApprover($a)
    {
        $a['parent_id'] = $this->itsID;
        return tldDMSApprover::insert($a);
    }

    /**
     * Delete approver by ID
     * @param int $aid
     * @return string on error
     */
    public function deleteApproverByID($aid)
    {
        $app = new tldDMSApprover($aid);
        if ($app->itsHeader['parent_id'] <> $this->itsID) {
            return _('This approver step is not attached to this DMS');
        }
        return $app->delete();
    }

    /**
     * Delete all DMS approvers
     * @return string on error
     */
    public function deleteApprovers()
    {
        return tldDMSApprover::deleteByParentID($this->itsID);
    }

    /**
     * Get approver list
     * @return array
     */
    public function getApprover()
    {
        return tldDMSApprover::byParent($this->itsID);
    }

    /**
     * Add department
     * @param array $a
     * @return int or string on error
     */
    public function addDepartment($a)
    {
        $a['parent_id'] = $this->itsID;
        return tldDMSDepartment::insert($a);
    }

    /**
     * Delete department by id
     * @param int $did
     * @return string on error
     */
    public function deleteDepartmentByID($did)
    {
        $dpt = new tldDMSDepartment($did);
        if ($dpt->itsHeader['parent_id'] <> $this->itsID) {
            return _('This department is not attached to this DMS');
        }
        return $dpt->delete();
    }

    /**
     * Function to delete All departments attached
     * @return string on error
     */
    public function deleteDepartments()
    {
        $dptList = $this->getDepartment();
        $e = null;
        foreach ($dptList as $dptVal) {
            $e .= $this->deleteDepartmentByID($dptVal['id']);
        }
        if (!empty($e)) {
            return $e;
        }
    }

    /**
     * Get department list
     * @return array
     */
    public function getDepartment()
    {
        return tldDMSDepartment::byParent($this->itsID);
    }

    /**
     * Add acl rule
     * @param array $a
     * @return int or string on error
     */
    public function addAclRule($a)
    {
        $a['parent_id'] = $this->itsID;
        $a['module'] = 'DMS';
        $a['name'] = 'acl';
        return tldModOrg::insert($a);
    }

    /**
     * Delete acl rule by ID
     * @param int $aid
     * @return string on error
     */
    public function deleteAclRuleByID($aid)
    {
        $acl = new tldModOrg($aid);
        if ($acl->getParentID() <> $this->itsID || $acl->getModule() !== 'DMS') {
            return _('This record is not attached to this DMS');
        }
        return $acl->delete();
    }

    /**
     * Delete all acl rule attached
     * @return string on error
     */
    public function deleteAllAclRules()
    {
        $rules = $this->getAclRules();
        $e = null;
        foreach ($rules as $rule) {
            $acl = new tldModOrg($rule['id']);
            $e .= $acl->delete();
        }
        if (!empty($e)) {
            return $e;
        }
    }

    /**
     * Get list of acl rules
     * @return array
     */
    public function getAclRules()
    {
        return tldModOrg::byParentModuleName($this->itsID, 'DMS', 'acl');
    }

    /**
     * Get list of user allowed by ACL
     * @return array
     */
    public function getAllowedUserList()
    {
        return tldModOrg::getUserListByParentModuleName($this->itsID, 'DMS', 'acl');
    }

    /**
     * Add notification rule
     * @param array $a
     * @return int or string on error
     */
    public function addNotificationRule($a)
    {
        $a['parent_id'] = $this->itsID;
        $a['module'] = 'DMS';
        $a['name'] = 'notification';
        return tldModOrg::insert($a);
    }

    /**
     * Delete notification rule
     * @param int $nid
     * @return string on error
     */
    public function deleteNotificationRuleByID($nid)
    {
        $not = new tldModOrg($nid);
        if ($not->getParentID() <> $this->itsID || $not->getModule() !== 'DMS') {
            return _('This record is not attached to this DMS');
        }
        return $not->delete();
    }

    /**
     * Delete all notification rule attached
     * @return string on error
     */
    public function deleteAllNotificationRules()
    {
        $rules = $this->getNotificationRules();
        $e = null;
        foreach ($rules as $rule) {
            $not = new tldModOrg($rule['id']);
            $e .= $not->delete();
        }
        if (!empty($e)) {
            return $e;
        }
    }

    /**
     * Get list of notification rules
     * @return array
     */
    public function getNotificationRules()
    {
        return tldModOrg::byParentModuleName($this->itsID, 'DMS', 'notification');
    }

    /**
     * Get list of user notified by notification rules
     * @return array
     */
    public function getNotificationUserList()
    {
        return tldModOrg::getUserListByParentModuleName($this->itsID, 'DMS', 'notification');
    }

    /**
     * Get list of revisions
     * @return array rows
     */
    public function getRevisions()
    {
        return tldDMSRevision::byParent($this->itsID);
    }

    /**
     * Get the Next revision number
     * @return int
     */
    public function getNextRevisionNumber()
    {
        $revision = tldDMSRevision::getMaxRevisionNumberByParent($this->itsID);
        return (empty($revision)) ? 1 : $revision + 1;
    }

    /**
     * Add new DMS revision
     * @param array $a
     * @return string on error
     */
    public function addRevision($a)
    {
        $a['parent_id'] = $this->itsID;
        return tldDMSRevision::insert($a);
    }

    /**
     * Assign the revision ID of the DMS
     * @param int $revid
     * @return string on error
     */
    public function setRevisionID($revid)
    {
        return $this->update(['rev_id' => $revid]);
    }

    /**
     * Get actual revision ID
     * @return array rows
     */
    public function getActiveRevisionID()
    {
        return $this->itsHeader['rev_id'];
    }

    /**
     * Get actual revision header info
     * @return array rows
     */
    public function getActiveRevision()
    {
        $rev = $this->getActiveRevisionObj();
        return $rev->itsHeader;
    }

    /**
     * Get actual revision object
     * @return tldDMSRevision
     */
    public function getActiveRevisionObj()
    {
        return new tldDMSRevision($this->itsHeader['rev_id']);
    }

    /**
     * Get active revision file object
     * @return tldFile instance or exception on error
     */
    public function getActiveRevisionFile()
    {
        $rev = $this->getActiveRevisionObj();
        if ($rev->isEmpty()) {
            throw new Exception('No active revision');
        }
        $file = new tldFile($rev->getPubFileID());
        if (!$file->isFile()) {
            throw new Exception('No file found for this active revision');
        }
        return $file;
    }

    /**
     * Get approval revision (active revision + 1)
     * @return array
     */
    public function getApprovalRevision()
    {
        $a = <<<EOF
revision={$this->itsHeader['revision']}+1
AND parent_id=$this->itsID
EOF;
        $rev = tldDMSRevision::byConstraints($a);
        return $rev[0] ?? [];
    }

    public function deleteApprovalRevision()
    {
        $revApproval = $this->getApprovalRevision();
        $rev = new tldDMSRevision($revApproval['id']);
        if ($rev->isEmpty()) {
            return 'No approval revision found';
        }
        return $rev->delete();
    }

    public function checkExpired()
    {
        error_log('BEGIN - tldDMS::checkExpired()');
        // Get list of DMS that should be expired
        $a = <<<EOF
dms.status LIKE 'ACTIVE' AND DATE_ADD(dms.dt_act, INTERVAL dms.periodicity MONTH) < NOW()
EOF;
        $dmsList = self::byConstraints($a);
        // Any results?
        if (count($dmsList) === 0) {
            error_log('No records found');
            return 0;
        }
        // Set Expired
        foreach ($dmsList as $dmsInfo) {
            $dms = new tldDMS($dmsInfo['id']);
            $e = $dms->updateStatus('EXPIRED', 0);
            $id = $dms->getID();
            if (is_string($e)) {
                error_log('DMS#' . $id . " status error: $e");
                continue;
            }
            error_log('DMS#' . $id . ' status set to EXPIRED');
            $message = <<<EOF
            'DMS#'.$id.'has been EXPIRED. Please update the DMS, if you want to ACTIVE it'
            <br><br><br>
EOF;
            $dms->notifyOwner('is EXPIRED', $message, []);
        }

        return count($dmsList);
    }

    public function getActivePubFileLink()
    {
        global $DMS_URL;
        return "$DMS_URL/index.php?m[0]=view&m[1]=getActivePubFile&id=$this->itsID";
    }

}


class tldDMSRevision
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

    public function getPubFileID()
    {
        return $this->itsHeader['pub_fid'];
    }

    public function getSrcFileID()
    {
        return $this->itsHeader['src_fid'];
    }

    /**
     * Get Revision number
     * @return int
     */
    public function getRevisionNumber()
    {
        return $this->itsHeader['revision'];
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
    dms_revision.*,
    tasks.status,
    tasks.cur_step,
    tasks.assignor,
    tasks.assignee,
    (SELECT CONCAT(lastname,', ',firstname) FROM people 
        WHERE people.id=tasks.assignor
    ) AS assignor_fullname,
    (SELECT CONCAT(lastname,', ',firstname) FROM people 
        WHERE people.id=tasks.assignee
    ) AS assignee_fullname,
    file_src.filename AS src_filename,
    file_pub.filename AS pub_filename
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
    dms_revision
    LEFT JOIN file AS file_src ON file_src.id=dms_revision.src_fid
    LEFT JOIN file AS file_pub ON file_pub.id=dms_revision.pub_fid
    LEFT JOIN tasks ON tasks.id=dms_revision.seq_id
EOF;
    }

    /**
     * Get Revision header
     * @return row
     */
    public function getHeader()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
WHERE dms_revision.id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Insert new DMS revision
     * @param array $a
     * @return int or string on error
     */
    public static function insert($a)
    {
        if (empty($a)) {
            return;
        }
        $fields = [
            'parent_id', 'revision', 'src_fid', 'pub_fid', 'seq_id', 'purpose',
        ];
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO dms_revision SET dt=NOW(), $SET";
        return tldUtils::sqlInsert($query);
    }

    /**
     * Update DMS revision
     * @param array $a
     * @param array $fields (optional)
     * @return int or string on error
     */
    public function update($a, $fields = '')
    {
        if (empty($a)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "UPDATE dms_revision SET $SET WHERE id=$this->itsID";
        return tldUtils::sqlQuery($query);
    }

    /**
     * Delete DMS revision
     * @return string on error
     */
    public function delete()
    {
        // Archived files
        $e = null;
        if ($this->itsHeader['src_fid'] <> 0) {
            $srcFile = new tldFile($this->itsHeader['src_fid']);
            $e = $srcFile->delete();
        }
        if ($this->itsHeader['pub_fid'] <> 0) {
            $pubFile = new tldFile($this->itsHeader['pub_fid']);
            $e .= $pubFile->delete();
        }
        // Look for sequence
        if (!empty($this->itsHeader['seq_id'])) {
            $seq = new tldSEQ($this->itsHeader['seq_id']);
            if (!$seq->isClosed()) {
                $e .= $seq->cancel(['comment' => 'Current DMS revision has been cancelled']);
            }
        }
        // Delete revision
        $query = "DELETE FROM dms_revision WHERE id=$this->itsID";
        $e .= tldUtils::sqlExecute($query);
        // return
        if (!empty($e)) {
            return $e;
        }
    }

    /**
     * Get listing by constraints
     * @param array $a
     * @param array $opt
     * @return array
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
            $ORDERBY = 'ORDER BY revision DESC, dt DESC';
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
     * By parent ID
     * @param int $pid
     * @return string on error
     */
    public static function byParent($pid)
    {
        return self::byConstraints(['parent_id' => $pid]);
    }

    public static function getMaxRevisionNumberByParent($pid)
    {
        $query = "SELECT MAX(revision) AS num FROM dms_revision WHERE parent_id=$pid";
        $row = tldUtils::getSqlRowToAssocArray($query);
        return $row['num'];
    }

    /**
     * Get associated log entries from mod_log system
     * @param int $num_log
     * @return array
     */
    public function getLog($numlog = '')
    {
        $WHERE = !empty($numlog) ? "AND log_num=$numlog" : '';

        $a = "mod_logs.parent_id = $this->itsID AND module LIKE 'DMSREV' $WHERE";
        return tldModLog::byConstraints($a);
    }

    /**
     * Get Log for Active NOT
     * @return array
     */
    public function getActiveNotificationLog()
    {
        $a = "mod_logs.parent_id = $this->itsID AND module LIKE 'DMSREV' AND log_num=1";
        $rows = tldModLog::byConstraints($a);
        return $rows[0];
    }

    /**
     * Add log entries into mod_log system
     * @param int $uid
     * @param string $uid
     * @param int $num_log
     * @return int or string on error
     */
    public function addLogEntry($uid, $comment, $num_log = 0)
    {
        $a = [
            'parent_id' => $this->itsID,
            'module' => 'DMSREV',
            'poster' => $uid,
            'comment' => $comment,
            'log_num' => $num_log,
        ];
        return tldModLog::insert($a);
    }

    /**
     * Function to get next revision comments
     * @return array
     */
    public function getComments()
    {
        return $this->getLog(10);
    }

    /**
     * Function to add next revision comments
     * @param int $uid
     * @param string $comment
     * @return mixed string on error
     */
    public function addComment($uid, $comment)
    {
        return $this->addLogEntry($uid, $comment, 10);
    }

}

class tldDMSBu
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
SELECT
    dms_bu.*,
    locations.erp AS erp,
    locations.location AS location
EOF;
    }

    /**
     * Get FROM mysql statement
     * @return string
     */
    public static function getFROM(): string
    {
        return <<<EOF
FROM dms_bu
    LEFT JOIN locations ON locations.id=dms_bu.buid
EOF;
    }

    /**
     * Get location header
     * @return row
     */
    public function getHeader()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
WHERE dms_bu.id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Insert new DMS location
     * @param array $a
     * @return int or string on error
     */
    public static function insert($a)
    {
        if (empty($a)) {
            return;
        }
        $fields = ['parent_id', 'buid'];
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO dms_bu SET $SET";
        return tldUtils::sqlInsert($query);
    }

    /**
     * Update DMS location
     * @param array $a
     * @param array $fields (optional)
     * @return int or string on error
     */
    public function update($a, $fields = '')
    {
        if (empty($a)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "UPDATE dms_bu SET $SET WHERE id=$this->itsID";
        return tldUtils::sqlQuery($query);
    }

    /**
     * Delete DMS location
     * @return string on error
     */
    public function delete()
    {
        $query = "DELETE FROM dms_bu WHERE id=$this->itsID";
        return tldUtils::sqlQuery($query);
    }

    /**
     * Get Location by constraints
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
            $ORDERBY = 'ORDER BY location';
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
     * By parent ID
     * @param int $pid
     * @return string on error
     */
    public static function byParent($pid)
    {
        return self::byConstraints(['parent_id' => $pid]);
    }

}

class tldDMSDepartment
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
SELECT
    dms_department.*,
    tld_departments.dpt AS department
EOF;
    }

    /**
     * Get FROM mysql statement
     * @return string
     */
    public static function getFROM(): string
    {
        return <<<EOF
FROM dms_department 
    LEFT JOIN tld_departments ON tld_departments.id=dms_department.dptid
EOF;
    }

    /**
     * Get Department header
     * @return row
     */
    public function getHeader()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
WHERE dms_department.id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Insert new DMS department
     * @param array $a
     * @return int or string on error
     */
    public static function insert($a)
    {
        if (empty($a)) {
            return;
        }
        $fields = ['parent_id', 'dptid'];
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO dms_department SET $SET";
        return tldUtils::sqlInsert($query);
    }

    /**
     * Update DMS department
     * @param array $a
     * @param array $fields (optional)
     * @return int or string on error
     */
    public function update($a, $fields = '')
    {
        if (empty($a)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "UPDATE dms_department SET $SET WHERE id=$this->itsID";
        return tldUtils::sqlQuery($query);
    }

    /**
     * Delete DMS department
     * @return string on error
     */
    public function delete()
    {
        $query = "DELETE FROM dms_department WHERE id=$this->itsID";
        return tldUtils::sqlQuery($query);
    }

    /**
     * Get Department by constraints
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
            $ORDERBY = 'ORDER BY department';
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
     * By parent ID
     * @param int $pid
     * @return string on error
     */
    public static function byParent($pid)
    {
        return self::byConstraints(['parent_id' => $pid]);
    }

}

class tldDMSType
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
SELECT *,
    (SELECT GROUP_CONCAT(value ORDER BY value DESC SEPARATOR ' ') FROM mod_lists
        WHERE parent_id=dms_type.id AND module='DMS_TYPE' AND list_name='file.extension.src'
        GROUP BY parent_id
    ) AS srcFileExtList,
    (SELECT GROUP_CONCAT(value ORDER BY value DESC SEPARATOR ' ') FROM mod_lists
        WHERE parent_id=dms_type.id AND module='DMS_TYPE' AND list_name='file.extension.pub'
        GROUP BY parent_id
    ) AS pubFileExtList
EOF;
    }

    /**
     * Get FROM mysql statement
     * @return string
     */
    public static function getFROM(): string
    {
        return <<<EOF
FROM dms_type 
EOF;
    }

    /**
     * Get Type header
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
     * Insert new DMS type
     * @param array $a
     * @return int or string on error
     */
    public static function insert($a)
    {
        if (empty($a)) {
            return;
        }
        $fields = ['parent_id', 'code', 'short_desc', 'cycle'];
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO dms_type SET $SET";
        return tldUtils::sqlInsert($query);
    }

    /**
     * Update DMS type
     * @param array $a
     * @param array $fields (optional)
     * @return int or string on error
     */
    public function update($a, $fields = '')
    {
        if (empty($a)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "UPDATE dms_type SET $SET WHERE id=$this->itsID";
        return tldUtils::sqlQuery($query);
    }

    /**
     * Delete DMS type
     * @return string on error
     */
    public function delete()
    {
        $query = "DELETE FROM dms_type WHERE id=$this->itsID";
        return tldUtils::sqlQuery($query);
    }

    /**
     * Get Type list by constraints
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
            $ORDERBY = 'ORDER BY code';
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

    public function getSrcAllowedExtensionList()
    {
        return tldModList::byConstraints(
            [
                'parent_id' => $this->itsID,
                'module' => 'DMS_TYPE',
                'list_name' => 'file.extension.src',
            ]
        );
    }

    public function getPubAllowedExtensionList()
    {
        return tldModList::byConstraints(
            [
                'parent_id' => $this->itsID,
                'module' => 'DMS_TYPE',
                'list_name' => 'file.extension.pub',
            ]
        );
    }

    public function addSrcExtension($ext)
    {
        $a = [
            'parent_id' => $this->itsID,
            'module' => 'DMS_TYPE',
            'list_name' => 'file.extension.src',
            'value' => $ext,
        ];
        return tldModList::insert($a);
    }

    public function addPubExtension($ext)
    {
        $a = [
            'parent_id' => $this->itsID,
            'module' => 'DMS_TYPE',
            'list_name' => 'file.extension.pub',
            'value' => $ext,
        ];
        return tldModList::insert($a);
    }

    public function delSrcExtension($ext)
    {
        $query = <<<EOF
DELETE FROM mod_lists
WHERE parent_id=$this->itsID AND module='DMS_TYPE' 
AND list_name='file.extension.src' AND value='$ext'
LIMIT 1
EOF;
        return tldUtils::sqlQuery($query);
    }

    public function delPubExtension($ext)
    {
        $query = <<<EOF
DELETE FROM mod_lists
WHERE parent_id=$this->itsID AND module='DMS_TYPE' 
AND list_name='file.extension.pub' AND value='$ext'
LIMIT 1
EOF;
        return tldUtils::sqlQuery($query);
    }

}

class tldDMSApprover
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
SELECT dms_approver.*,
    CONCAT(people.firstname,' ',people.lastname) AS approverFullname
EOF;
    }

    /**
     * Get FROM mysql statement
     * @return string
     */
    public static function getFROM(): string
    {
        return <<<EOF
FROM dms_approver
    LEFT JOIN people ON dms_approver.uid=people.id
EOF;
    }

    /**
     * Get Approver header
     * @return row
     */
    public function getHeader()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
WHERE dms_approver.id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Insert new DMS approver
     * @param array $a
     * @return int or string on error
     */
    public static function insert($a)
    {
        if (empty($a)) {
            return;
        }
        $fields = ['parent_id', 'step', 'uid'];
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO dms_approver SET $SET";
        return tldUtils::sqlInsert($query);
    }

    /**
     * Update DMS approver
     * @param array $a
     * @param array $fields (optional)
     * @return int or string on error
     */
    public function update($a, $fields = '')
    {
        if (empty($a)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "UPDATE dms_approver SET $SET WHERE id=$this->itsID";
        return tldUtils::sqlQuery($query);
    }

    /**
     * Delete DMS approver
     * @return string on error
     */
    public function delete()
    {
        $query = "DELETE FROM dms_approver WHERE id=$this->itsID";
        return tldUtils::sqlQuery($query);
    }

    /**
     * Delete All DMS approver from DMS ID
     * @return string on error
     */
    public static function deleteByParentID($pid)
    {
        $query = "DELETE FROM dms_approver WHERE parent_id=$pid";
        return tldUtils::sqlQuery($query);
    }

    /**
     * Get Approver list by constraints
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
            $ORDERBY = 'ORDER BY step';
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
     * By parent ID
     * @param int $pid
     * @return string on error
     */
    public static function byParent($pid)
    {
        return self::byConstraints(['parent_id' => $pid]);
    }

}


class tldDMS_ApplicationController
{

    public $itsUserProfile;
    public $itsAction;
    public $itsParameters;

    public function __construct($user = '', $action = '', $params = '')
    {
        $this->itsUserProfile = new tldDMS_ApplicationUserProfile($user);
        $this->itsAction = $action;
        $this->itsParameters = $params;
    }

    public function addLog($comment)
    {
        $info = tldUtils::getUserAndRequestInfo();
        $query = <<<EOF
INSERT INTO logs VALUES (
    '','',{$info['user_login']},NOW(),{$info['user_ip']},
    'DMS - From {$info['portal']} portal: $comment'
)
EOF;
        return tldUtils::sqlInsert($query);
    }

}

class tldDMS_ApplicationUserProfile
{

    public $itsUser;
    public $itsProfile;

    public function __construct($user = '')
    {
        $this->itsUser = $user;
        $this->itsProfile = $this->identifyUserProfile();
    }

    public function isEmpty()
    {
        return empty($this->itsProfile);
    }

    public function isValid()
    {
        return in_array(
            $this->itsProfile,
            array_keys($this->getRegisteredProfiles())
        );
    }

    public function getProfile()
    {
        return $this->itsProfile;
    }

    public function getProfileDescription()
    {
        $list = $this->getRegisteredProfiles();
        return $list[$this->itsProfile];
    }

    public function getRegisteredProfiles()
    {
        return [
            'ADMIN' => 'Authenticated TLD user with ALL credentials',
            'TLD INTRANET' => 'Authenticated intranet user and TLD personnal',
            'INTRANET' => 'Authenticated intranet user and not TLD personnal',
            'PUBLIC' => 'Not authenticated, TLD internal network user',
            'EXTRANET' => 'Authenticated Customer Extranet user',
            'EVENDOR' => 'Authenticated Supplier eVendor user',
        ];
    }

    public function identifyUserProfile()
    {
        $userClass = get_class($this->itsUser);
        $profile = null;
        switch ($userClass) {
            case 'tldUser':
                if ($user->isInGroup('superuser')) {
                    $profile = 'ADMIN';
                } elseif ($user->isInGroup('acl_auth_INTRANET')) {
                    if ($user->isInTLDDomain()) {
                        $profile = 'TLD INTRANET';
                    } else {
                        $profile = 'INTRANET';
                    }
                }
                break;
            case 'extranetUser':
                if ($user->itsDetails['enable'] === 'Y') {
                    $profile = 'EXTRANET';
                }
                break;
            case 'vendorUser':
                if ($user->itsDetails['enable'] === 'Y') {
                    $profile = 'EVENDOR';
                }
                break;
        }
        // if not authenticated
        if (empty($profile)) {
            // Check portal access request
            $info = tldUtils::getUserAndRequestInfo();
            switch ($info['portal']) {
                case 'DMS':
                    $profile = 'PUBLIC';
                    break;
            }
            // else it is not valid user, profile still null
        }
        return $profile;
    }

}
