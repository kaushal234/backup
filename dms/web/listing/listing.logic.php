<?php

declare(strict_types=1);
$_TITLE .= " \ "._('Listing');
$_MENU .= '';

// To assign
$data = null;
$title = null;
$xItems = [
    'id' => _('DMS#'),
    'parent_id' => _('Parent DMS#'),
    'dt' => _('Date creation'),
    'dt_act' => _('Last activation'),
    'ownerFullname' => _('Owner'),
    'title' => _('Title'),
    'subject' => _('Subject'),
    'lang' => _('Language'),
    'status' => _('Status'),
    'typeDesc' => _('Type'),
    'periodicity' => _('Revision periodicity'),
    'sysref' => _('System reference'),
    'revision' => _('Revision'),
];

switch ($m[1] ?? null) {
    case 'quickSearch':
        $vars = tldUtils::cleanupFormInput($_POST);
        // Redirect if direct access
        if (!empty($vars['id'])) {
            header("Location: $php_self?m[0]=view&id=".$vars['id']);
            exit;
        }
        // List of fields
        $searchFields = [
            'division', 'bu', 'department', 'title', 'subject', 'type_id', 'iso', 'keyword',
        ];
        // Construct constraints
        $a = [];
        if (!isset($vars['archived']) || 0 === $vars['archived']) {
            $a[] = "dms.status NOT IN ('ARCHIVE')";
        }
        foreach ($searchFields as $searchField) {
            if (empty($vars[$searchField])) {
                continue;
            }
            $val = $vars[$searchField];
            switch ($searchField) {
                case 'division':
                    $a[] = <<<EOF
$val IN (
    SELECT bu.parent_id FROM tld_regions_locations AS bu
    WHERE bu.buid IN (SELECT buid FROM dms_bu WHERE parent_id=dms.id)
)
EOF;
                    break;
                case 'bu':
                    $a[] = "$val IN (SELECT buid FROM dms_bu WHERE parent_id=dms.id)";
                    break;
                case 'department':
                    $a[] = "$val IN (SELECT dptid FROM dms_department WHERE parent_id=dms.id)";
                    break;
                case 'title':
                case 'subject':
                    $a[] = "$searchField LIKE '%$val%'";
                    break;
                case 'iso':
                    if (0 != $vars[$searchField]) {
                        $a[] = "sysref LIKE 'ISO:9001'";
                    }
                    break;
                case 'keyword':
                    $keyword = '%'.$vars['keyword'].'%';
                    $b = [
                        'title' => $keyword,
                        'subject' => $keyword,
                        'status' => $keyword,
                        'ownerFullname' => $keyword,
                        'typeDesc' => $keyword,
                        'sysref' => $keyword,
                        'description' => $keyword,
                    ];
                    $a[] = '('.tldUtils::constructWhere($b, 'OR').')';
                    break;
                default:
                    $a[] = "$searchField='$val'";
                    break;
            }
        }
        // Check nb constraints
        if (!count($a)) {
            $_ERROR[] = _('No constraints set, can not do search');
            break;
        }
        $WHERE = implode(' AND ', $a);
        // Display results
        $data = tldDMS::byConstraints($WHERE);
        $title = _('Quick search results');
        break;
    case 'byBuDepartment':
        $constraints = null;
        $x = TldDatabase::escape($x); // BU
        $y = TldDatabase::escape($y); // DPT
        $title = sprintf(_('Listing by Business Unit %s by Department %s'), $x, $y);
        $data = tldDMS::byBuDepartmentByConstraints($x, $y, $constraints);
        break;
    case 'byStatusType':
        $constraints = null;
        if (null !== ($dpt ?? null)) {
            $constraints = "dpt_id = $dpt";
        }
        $x = TldDatabase::escape($x); // status
        $y = TldDatabase::escape($y); // type

        $title = sprintf(_('Listing by status %s by type %s'), $x, $y);

        switch ($m[2] ?? null) {
            case 'byBuDpt':
                $buid = TldDatabase::escape($buid);
                $dptid = TldDatabase::escape($dptid);
                $constraints = <<<EOF
$dptid IN (SELECT dptid FROM dms_department WHERE parent_id=dms.id)
AND $buid IN (SELECT buid FROM dms_bu WHERE parent_id=dms.id)
EOF;
                switch ($m[3] ?? null) {
                    case 'bySysRef':
                        $ref = TldDatabase::escape($ref);
                        $constraints .= " AND sysref='$ref'";
                        break;
                }
                break;
            default:
                switch ($m[3] ?? null) {
                    case 'bySysRef':
                        $ref = TldDatabase::escape($ref);
                        $constraints = ['sysref' => $ref];
                        break;
                }
                break;
        }
        $data = tldDMS::byStatusTypeByConstraints($x, $y, $constraints);
        break;
    case 'byOwner':
        $constraints = null;
        $uid = TldDatabase::escape($uid);
        $owner = new tldUser($uid);
        $title = sprintf(_('Listing by owner %s'), $owner->getFullname());

        switch ($m[2] ?? null) {
            case 'byStatus':
                $status = TldDatabase::escape($status);
                $constraints = ['status' => $status];
                $title .= sprintf(_(', status %s'), $status);
                switch ($m[3] ?? null) {
                    case 'bySysRef':
                        $ref = TldDatabase::escape($ref);
                        $constraints[] = ['sysref' => $ref];
                        break;
                }
                break;
            case 'tobeExpired1m':
                $constraints = <<<'EOF'
status LIKE 'ACTIVE' AND PERIOD_DIFF(
      DATE_FORMAT(DATE_ADD(dt_act, INTERVAL periodicity MONTH),'%Y%m'),
      DATE_FORMAT(NOW(),'%Y%m')
    )<1
EOF;
                $title .= _(', EXPIRED in the coming month');
                switch ($m[3] ?? null) {
                    case 'bySysRef':
                        $ref = TldDatabase::escape($ref);
                        $constraints .= " AND sysref='$ref'";
                        break;
                }
                break;
            default:
                $constraints = "dms.status != 'ARCHIVE'";
                switch ($m[3] ?? null) {
                    case 'bySysRef':
                        $ref = TldDatabase::escape($ref);
                        $constraints = ['sysref' => $ref];
                        break;
                }
                break;
        }
        $data = tldDMS::byOwnerIdByConstraints($uid, $constraints);
        break;
    case 'byOpenSeqAssignee':
        $constraints = null;
        $uid = TldDatabase::escape($uid);
        $assignee = new tldUser($uid);
        $title = sprintf(_('Listing by OPEN sequence with assignee %s'), $assignee->getFullname());

        switch ($m[3] ?? null) {
            case 'bySysRef':
                $ref = TldDatabase::escape($ref);
                $constraints = ['sysref' => $ref];
                break;
        }
        $data = tldDMS::byOpenSeqAssigneeIdByConstraints($uid, $constraints);
        break;
    case 'byNotificationUserID':
        $constraints = null;
        $uid = TldDatabase::escape($uid);
        $assignee = new tldUser($uid);
        $title = sprintf(_('Listing that must be known by %s'), $assignee->getFullname());

        switch ($m[3] ?? null) {
            case 'bySysRef':
                $ref = TldDatabase::escape($ref);
                $constraints = ['sysref' => $ref];
                break;
        }
        $data = tldDMS::byNotificationUserIdByConstraints($uid, $constraints);
        break;
}

if (!empty($data)) {
    $sess['dms']['list'] = $data;
    $_MENU .= "
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href=\"$php_self?m[0]=listing&out=xls\">"._('Download as XLS').'</a>
';
}

// Finaly display report

switch ($out ?? null) {
    case 'xls':
        $report = new tldXLS(
            $sess['dms']['list'],
            [
                'xItems' => $xItems,
                'showTitles' => true,
            ]
        );
        $sess['dms']['list'] = null;
        $report->out();
        exit;
    default:
        $_BODY .= _getListing($data, $title, $xItems);
        break;
}
