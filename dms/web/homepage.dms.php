<?php

declare(strict_types=1);

$_BODY .= '<div id="dashboard">';
$_BODY .= '<h3 class="section">'._('Common DMS dashboard').'</h3>';
$cells = [];

// MATRIX by status / type -------------------------->
$vars = tldUtils::cleanupFormInput($_POST);

if (null !== ($vars['dpt_id'] ?? null) && 'All' !== ($vars['dpt_id'] ?? null)) {
    $form = new tldMatrix(
        tldDMS::countByStatusTypeByConstraints('dpt_id = '.$vars['dpt_id']),
        'status', 'type', 'num',
        "$php_self?m[0]=listing&m[1]=byStatusType&dpt=".$vars['dpt_id'],
        _('DMS Count by Status, Type')
    );
} else {
    $form = new tldMatrix(
        tldDMS::countByStatusTypeByConstraints(),
        'status', 'type', 'num',
        "$php_self?m[0]=listing&m[1]=byStatusType",
        _('DMS Count by Status, Type')
    );
}
$cells[0] = $form->fetch();

$form = new HTML_QuickForm('dpt', 'post');
$form->addElement('select', 'dpt_id', _('Department owner'), ['All' => 'All'] + tldDepartment::getListAsIdDepartment());
$form->addElement('submit', 'btnSubmit', _('Submit'));
$form->setDefaults(['dpt_id' => $vars['dpt_id'] ?? null]);
$cells[0] .= $form->toHTML();

// SEARCH FORM -------------------------------------->

$divList = tldRegion::getListAsIdDivision();
$buList = tldLocation::getBuListAsIdBU();
$departmentList = tldDepartment::getListAsIdDepartment();
$typeList = tldDMSType::getList();
$typeListAsIdDesc = tldUtils::optionsByKeyValue($typeList, 'id', 'short_desc');
$systList = tldDMS::getSystemRefList();

$form = new HTML_QuickForm('search', 'post');
$form->addElement('hidden', 'm[0]', 'listing');
$form->addElement('hidden', 'm[1]', 'quickSearch');
// --- by ID
$form->addElement('header', 'frmTitle1', _('Quick access'));
$form->addElement('text', 'id', 'DMS#');
// --- by fields
$form->addElement('header', 'frmTitle2', _('Quick search'));
$form->addElement('select', 'division', _('Division coverage'), ['' => ''] + $divList);
$form->addElement('select', 'bu', _('Business unit coverage'), ['' => ''] + $buList);
$form->addElement('select', 'department', _('Department coverage'), ['' => ''] + $departmentList);
$form->addElement('text', 'title', _('Title'));
$form->addElement('text', 'subject', _('Subject'));
$form->addElement('text', 'keyword', _('Keyword'));
$form->addElement('select', 'type_id', _('Type'), ['' => ''] + $typeListAsIdDesc);
$form->addElement('checkbox', 'iso', _('ISO only?'));
$form->addElement('checkbox', 'archived', _('Show Archived DMS'));
$form->addElement('submit', 'btnSubmit', _('Submit'));

$cells[1] = $form->toHTML();

// DISPLAY COMMON DASHBOARD -------------------------->

$report = new tldHTMLTable(
    $cells,
    [
        'cols' => 2,
        'attribs' => [
            'table' => " width='100%'",
            'tr' => " bgcolor='#FFFFFF'",
        ],
    ]
);
$_BODY .= $report->fetch();

// /////////////////////////////////////
//         PROFILE dashboard         //
// /////////////////////////////////////

$cells = [];

// Determine profile ----------------------------------->

$PROFILE = null;
if (_isUserLoggedIn()) {
    if (!_isTLDUser()) {
        $PROFILE = 'EXTERNAL';
    } else {
        $PROFILE = 'TLD';
    }
}

// Get data by profile ----------------------------------->

switch ($PROFILE) {
    case 'TLD':
        // Get department & BU
        $dptObj = new tldDepartment($user->getDepartmentID());
        $buObj = new tldLocation($user->getBUID());
        // Matrix by status/type for BU/Dpt
        $form = new tldMatrix(
            tldDMS::countByStatusTypeByDptIdBuIdByConstraints(
                $dptObj->getID(),
                $buObj->getID()
            ),
            'status', 'type', 'num',
            "$php_self?m[0]=listing&m[1]=byStatusType&m[2]=byBuDpt&buid={$buObj->getID()}&dptid={$dptObj->getID()}",
            _('List of DMS that concerns my departement, my BU').'<br>'.$dptObj->itsHeader['dpt'].' - '.$buObj->getShortName()
        );
        $cells[0] = $form->fetch();
        // Owner Statistics
        $report = new tldAssocTable(
            tldDMS::getUserStatsByConstraints($user->getID()),
            [
                'num_owner' => _('DMS I own'),
                'num_owner_expired' => _('DMS I own EXPIRED'),
                'num_owner_revision' => _('DMS I own REVISION'),
                'num_owner_approval' => _('DMS I own APPROVAL'),
                'num_owner_expired_1_month' => _('DMS I own to be EXPIRED < 1 month'),
                'num_seq_approval' => _('DMS Sequence to approve'),
                'num_to_know' => _('DMS I must know'),
            ],
            [
                'title' => _('DMS Statistics').'<br>'.$user->getFullname(),
                'links' => [
                    'num_owner' => "$php_self?m[0]=listing&m[1]=byOwner&uid=".$user->getID().'&res=',
                    'num_owner_expired' => "$php_self?m[0]=listing&m[1]=byOwner&m[2]=byStatus&status=EXPIRED&uid=".$user->getID().'&res=',
                    'num_owner_revision' => "$php_self?m[0]=listing&m[1]=byOwner&m[2]=byStatus&status=REVISION&uid=".$user->getID().'&res=',
                    'num_owner_approval' => "$php_self?m[0]=listing&m[1]=byOwner&m[2]=byStatus&status=APPROVAL&uid=".$user->getID().'&res=',
                    'num_owner_expired_1_month' => "$php_self?m[0]=listing&m[1]=byOwner&m[2]=tobeExpired1m&uid=".$user->getID().'&res=',
                    'num_seq_approval' => "$php_self?m[0]=listing&m[1]=byOpenSeqAssignee&uid=".$user->getID().'&res=',
                    'num_to_know' => "$php_self?m[0]=listing&m[1]=byNotificationUserID&uid=".$user->getID().'&res=',
                ],
            ]
        );
        $cells[1] = $report->fetch();
        break;
    case 'EXTERNAL':
        // Do not display a specific dashboard for external people
        break;
}

// Display ----------------------------------->

$_BODY .= '<h3 class="section">'._('My DMS dashboard')." - ($PROFILE profile)</h3>";
$report = new tldHTMLTable(
    $cells,
    [
        'cols' => count($cells),
        'attribs' => [
            'table' => " width='100%'",
            'tr' => " bgcolor='#FFFFFF'",
        ],
    ]
);
$_BODY .= $report->fetch();
// Get latest
$_BODY .= _getListing(
    tldDMS::byLatest(5),
    _('Latest DMS created')
);

$_BODY .= '</div>';
