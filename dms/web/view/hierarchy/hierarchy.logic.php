<?php

declare(strict_types=1);
$_TITLE .= " \ "._('Hierarchy');
$_MENU .= "
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=hierarchy&id=$id\">"._('Home')."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=hierarchy&m[2]=edit&id=$id\">"._('Edit hierarchy').'</a>
';

switch ($m[2] ?? null) {
    case 'edit':
        $_TITLE .= " \ "._('Edit');
        // Check access
        if (!_isOwner()) {
            $_ERROR[] = _('You do not have permissions to use this feature');
            break;
        }
        $form = new HTML_QuickForm('add', 'post');
        $form->addElement('hidden', 'm[0]', 'view');
        $form->addElement('hidden', 'm[1]', 'hierarchy');
        $form->addElement('hidden', 'm[2]', 'edit');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'frmTitle', sprintf(_('Hierarchy: Set DMS#%s as sub DMS of...'), $id));
        $form->addElement('text', 'parent_id', _('DMS#').'<br>('._('0 to reset').')');
        $form->addRule('parent_id', _('Field is required'), 'required');
        $form->addRule('parent_id', _('Field is numeric'), 'numeric');
        $form->setDefaults($dms->itsHeader);
        $form->addElement('submit', 'btnSubmit', _('Submit'));

        if (!$form->validate()) {
            $_BODY = $form->toHTML();
            $_BODY .= _getFamilyTreeByID($dms);
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        // Check data
        if (!is_numeric($vars['parent_id'])) {
            $_ERROR[] = _('Parameters sent invalid');
            break;
        }
        // Check the future parent DMS
        if ($vars['parent_id'] == $id) {
            $_ERROR[] = _('Can not link DMS to itself');
            break;
        }
        // If not a reset
        if (0 != $vars['parent_id']) {
            // Check if the dms exists
            $dmsParent = new tldDMS($vars['parent_id']);
            if ($dmsParent->isEmpty()) {
                $_ERROR[] = sprintf(_('DMS#%s not found'), $vars['parent_id']);
                break;
            }
            // Check if looping
            $dmsChilds = [$id => ['header' => $dms->itsHeader, 'level' => 0]];
            $dms->getChildListRec($dmsChilds);
            if (in_array($dmsParent->getID(), array_keys($dmsChilds), true)) {
                $_ERROR[] = sprintf(
                    _('DMS#%s already in hierarchy tree as sub item of DMS#%s'),
                    $dmsParent->getID(),
                    $dms->getID()
                );
                break;
            }
            // Check if parent not already in 'family'
            $dmsParentFamily = $dmsParent->getFamilyTree();
            if (count($dmsParentFamily) > 1 && 'Y' != $vars['confirmation']) {
                // Redo form
                $form->removeElement('btnSubmit');
                $form->freeze(['parent_id']);
                $form->addElement('select', 'confirmation', _('Are you sure?'), ['Y' => _('YES')]);
                $form->addRule('confirmation', _('Field is required'), 'required');
                $form->addElement('submit', 'btnSubmit', _('Submit'));
                // Check validation
                $vars = tldUtils::cleanupFormInput($form->exportValues());
                if ('Y' != $vars['confirmation']) {
                    $_NOTE[] = sprintf(_('DMS#%s already in a hierarchy tree'), $vars['parent_id']);
                    $_NOTE[] = _('Please confirm before processing');
                    $_BODY .= _getFamilyTreeByID(
                        $dmsParent,
                        [
                            'addChildPreview' => $dms->getID(),
                            'title' => sprintf(
                                _('Preview of hierarchy tree with DMS# as parent'),
                                $dmsParent->getID()
                            ),
                        ]
                    );
                    $_BODY .= '<br>'.$form->toHTML();
                    break;
                }
            }
        }
        // Do the update
        $e = $dms->update($vars, ['parent_id']);
        if (is_string($e)) {
            $_ERROR[] = _('DMS not updated').'<br>'._('Reason').": $e";
            break;
        }
        // Log
        if ($header['parent_id'] != $vars['parent_id']) {
            _addLog($user->getID(), "Parent hierarchy updated FROM DMS#{$header['parent_id']} TO DMS#{$vars['parent_id']}");
        }
        // Confirm
        if (0 != $vars['parent_id']) {
            $_CONF[] = sprintf(_('DMS#%s successfully linked'), $vars['parent_id']);
        } else {
            $_CONF[] = _('DMS link successfully reseted');
        }
        break;
    default:
        $_BODY .= _getFamilyTreeByID($dms);
        break;
}

function _getFamilyTreeByID($dms, $options = null)
{
    $dmsID = $dms->getID();
    if (!empty($options['title'])) {
        $body = '<h3>'.$options['title'].'</h3>';
    } else {
        $body = '<h3>'._('Hierarchy tree of')." DMS#$dmsID</h3>";
    }
    // Get parent Familly
    $rootParentID = $dms->getRootParentIDFamily();
    $parentDMS = new tldDMS($rootParentID);
    // Get family tree or ROOT parent
    $familyTree = $parentDMS->getFamilyTree();
    // Draw family diagram
    $diagram = '<div style="width:100%;">';
    foreach ($familyTree as $dms_id => $member) {
        // Exclude archived
        if ('ARCHIVE' == $member['header']['status']) {
            continue;
        }
        // look for options
        $optItem = [];
        if ($dms_id == $dms->getID()) {
            $optItem = ['style' => 'background:#dedede;'];
        }
        // Add items
        if (($options['addChildPreview'] ?? null) != $dms_id) {
            $dmsMember = new tldDMS($dms_id);
            $diagram .= _getFamilyTreeItemView($dmsMember, $member, $optItem);
        }
        // Look for options
        // -- if child preview from actual DMS tree...
        if (!empty($options['addChildPreview']) && $dmsID == $dms_id) {
            // Get dms child header
            $otherDMS = new tldDMS($options['addChildPreview']);
            // Add new child item under actual DMS
            $diagram .= _getFamilyTreeItemView(
                $otherDMS,
                ['level' => $member['level'] + 1, 'header' => $otherDMS->itsHeader],
                ['style' => 'background:#FFC2C2;']
            );
        }
    }
    $diagram .= '</div>';
    $body .= $diagram;

    return $body;
}

function _getFamilyTreeItemView($dms, $members, $opt = null)
{
    $dms_id = $dms->getID();
    $levelSeparator = '&nbsp;';
    $levelSeparatorLine = null;
    for ($i = 0; $i < (5 * $members['level']); ++$i) {
        $levelSeparatorLine .= $levelSeparator;
    }
    $style = null;
    if (!empty($opt['style'])) {
        $style .= $opt['style'];
    }
    $colorStyle = 'color:'._getColorPropertyByDMSStatus($dms->getStatus()).';';
    $url = "?m[0]=view&id=$dms_id";

    return <<<EOF
<p style="margin:0;$style">$levelSeparatorLine|-
  <a href="$url" style="$colorStyle">
    DMS#$dms_id - {$members['header']['typeDesc']}, {$members['header']['title']}
  </a>
</p>
EOF;
}

function _getColorPropertyByDMSStatus($status)
{
    switch ($status) {
        case 'ACTIVE':
            return 'green';
            break;
        case 'EXPIRED':
            return 'red';
            break;
        case 'REVISION':
        case 'APPROVAL':
            return 'orange';
            break;
        default:
            return 'black';
            break;
    }
}
