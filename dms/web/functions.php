<?php

declare(strict_types=1);

function _isUserLoggedIn()
{
    global $user;
    if (!is_a($user, 'tldUser') || empty($user)) {
        return false;
    }
    if (!$user->isInGroup(['acl_auth_INTRANET'])) {
        return false;
    }

    return true;
}

function _isTLDUser()
{
    global $user;
    if (!_isUserLoggedIn()) {
        return false;
    }
    if (!$user->isInTLDDomain()) {
        return false;
    }

    return true;
}

function _isOwner()
{
    global $user,$dms;
    if (!_isTLDUser()) {
        return false;
    }
    if ($user->getID() != $dms->getOwnerID() && !_isAdmin()) {
        return false;
    }

    return true;
}

function _isQAM()
{
    global $user;
    if (!_isTLDUser()) {
        return false;
    }
    if (!$user->isInGroup(['role_QAM'])) {
        return false;
    }

    return true;
}

function _isMIS()
{
    global $user;
    if (!_isUserLoggedIn()) {
        return false;
    }
    if (!$user->isInGroup(['gg_MIS', 'superuser'])) {
        return false;
    }

    return true;
}

function _isAdmin()
{
    global $user;
    if (!_isUserLoggedIn()) {
        return false;
    }
    if (!$user->isInGroup(['superuser'])) {
        return false;
    }

    return true;
}

function _getListing($data, $title, $xItems = '')
{
    global $php_self;
    if (empty($xItems)) {
        $xItems = [
            'id' => _('DMS#'),
            'parent_id' => _('Parent DMS#'),
            'dt' => _('Date creation'),
            'ownerFullname' => _('Owner'),
            'owner_bu_name' => sprintf('%s (%s)', _('Business Unit'), _('Owner')),
            'title' => _('Title'),
            'subject' => _('Subject'),
            'lang' => _('Language'),
            'status' => _('Status'),
            'typeDesc' => _('Type'),
            'sysref' => _('System reference'),
            'revision' => _('Revision'),
        ];
    }
    $report = new tldReportColumnar(
        $data,
        [
            'xItems' => $xItems,
            'title' => $title,
            'links' => [
                'id' => "$php_self?m[0]=view&id=",
                'parent_id' => "$php_self?m[0]=view&id=",
            ],
            'showNumberOfRows' => true,
        ]
    );

    return $report->fetch();
}
