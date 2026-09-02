<?php

declare(strict_types=1);
$_TITLE .= " \ "._('Revision');
$_MENU .= "
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=revision&id=$id\">"._('Home').'</a>
';

$revisionList = $dms->getRevisions();

switch ($m[2] ?? null) {
    case 'view':
        if (empty($revid) || !is_numeric($revid)) {
            $_ERROR[] = _('Parameters sent empty or invalid');
            break;
        }
        $rev = new tldDMSRevision($revid);
        if ($rev->isEmpty()) {
            $_ERROR[] = _('Revision not found');
            break;
        }
        $revHeader = $rev->itsHeader;
        $revNumber = $rev->getRevisionNumber();
        // Check revision parent_id
        if ($revHeader['parent_id'] != $dms->getID()) {
            $_ERROR[] = sprintf(_('This revision is not related to DMS#%s'), $dms->getID());
            break;
        }

        $_TITLE .= " \ "._('Revision')."#$revNumber";
        $_MENU .= "
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=revision&m[2]=view&revid=$revid&id=$id\">"._('Home')."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=revision&m[2]=view&m[3]=logs&revid=$revid&id=$id\">"._('Logs')."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=revision&m[2]=view&m[3]=comments&revid=$revid&id=$id\">"._('Next revision comments').'</a>
';

        if (_isOwner()) {
            $_MENU .= "
&nbsp;|&nbsp;<a href=\"$php_self?m[0]=view&m[1]=revision&m[2]=view&m[3]=edit&revid=$revid&id=$id\">"._('Edit revision informations & files').'</a>
';
        }

        switch ($m[3] ?? null) {
            case 'comments':
                $_TITLE .= " \ "._('Next revision comments');
                $_MENU .= "
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=revision&m[2]=view&m[3]=comments&revid=$revid&id=$id\">"._('Home')."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=revision&m[2]=view&m[3]=comments&m[4]=add&revid=$revid&id=$id\">"._('Add').'</a>
';

                switch ($m[4] ?? null) {
                    case 'add':
                        // Check this is the active revision
                        if ($dms->getActiveRevisionID() != $revid) {
                            $_ERROR[] = _('Can add comments only for the actual revision');
                            break;
                        }
                        // Form
                        $form = new HTML_QuickForm('add', 'post');
                        $form->addElement('hidden', 'm[0]', 'view');
                        $form->addElement('hidden', 'm[1]', 'revision');
                        $form->addElement('hidden', 'm[2]', 'view');
                        $form->addElement('hidden', 'm[3]', 'comments');
                        $form->addElement('hidden', 'm[4]', 'add');
                        $form->addElement('hidden', 'revid', $revid);
                        $form->addElement('hidden', 'id', $id);
                        $form->addElement('header', 'frmTitle', _('Add next revision comment'));
                        $form->addElement('textarea', 'log', _('Comment'), ['rows' => 4, 'cols' => 40]);
                        $form->addRule('purpose', _('Field is required'), 'required');
                        $form->addElement('submit', 'btnSubmit', _('Submit'));

                        if (!$form->validate()) {
                            $_BODY .= $form->toHTML();
                            break;
                        }

                        $rawVars = $form->exportValues();
                        $vars = tldUtils::cleanupFormInput($rawVars);
                        if (!_isUserLoggedIn()) {
                            $userid = 0;
                            $userFullname = 'unknow (user not authenticated)';
                        } else {
                            $userid = $user->getID();
                            $userFullname = $user->getFullname();
                        }
                        // Add comment
                        $e = $rev->addComment($userid, $vars['log']);
                        if (is_string($e)) {
                            $_ERROR[] = _('Next revision comment not added').': '.$e;
                            break;
                        }
                        // Notify owner
                        $subject = _('New revision comment added');
                        $message = sprintf(_('New revision comment added by %s'), $userFullname);
                        $message .= '<br>'._('Comment').': '.$rawVars['log'];
                        $message .= "<br><br><a href=\"$DMS_URL/index.php?m[0]=view&m[1]=revision&m[2]=view&m[3]=comments&revid=$revid&id=$id\">"._('Click here to see next revision comments of this DMS revision').'</a>';
                        $dms->notifyOwner($subject, $message);
                        // Confirmation message
                        $_CONF[] = _('Next revision comment successfully added');
                        break;
                }

                $report = new tldReportColumnar(
                    $rev->getComments(),
                    [
                        'xItems' => [
                            'id' => 'ID#',
                            'date' => _('Date'),
                            'poster_fullname' => _('Poster'),
                            'comment' => _('Comment'),
                        ],
                        'title' => _('Next revision comments'),
                    ]
                );
                $_BODY .= $report->fetch();
                break;
            case 'edit':
                // Check access
                if (!_isOwner()) {
                    $_ERROR[] = _('You do not have permissions to use this feature');
                    break;
                }
                // Check revision, must be a new one (under revision/approval)
                $activeRev = $dms->getActiveRevision();
                if ($revHeader['revision'] == $activeRev['revision']) {
                    $_NOTE[] = _('This revision is ACTIVE');
                }
                // Check the sequence if complete
                $seq = new tldSEQ($revHeader['seq_id']);
                if (!$seq->isCompleted()) {
                    $_NOTE[] = _("This revision is still under APPROVAL in SEQ#{$revHeader['seq_id']}");
                    $_BODY .= '<a href="http://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id='.$revHeader['seq_id'].'">'._('Click here to view Sequence')."#{$revHeader['seq_id']}</a>";
                }
                // Get extension list
                $dmsType = new tldDMSType($dms->itsHeader['type_id']);
                $srcAllowedFileExt = tldUtils::optionsByKeyValue($dmsType->getSrcAllowedExtensionList(), 'value', 'value');
                $pubAllowedFileExt = tldUtils::optionsByKeyValue($dmsType->getPubAllowedExtensionList(), 'value', 'value');
                if (empty($pubAllowedFileExt) || empty($srcAllowedFileExt)) {
                    $_ERROR[] = _('Allowed extension list not configured in ADMIN');
                    break;
                }
                // Form
                $form = new HTML_QuickForm('add', 'post');
                $form->addElement('hidden', 'm[0]', 'view');
                $form->addElement('hidden', 'm[1]', 'revision');
                $form->addElement('hidden', 'm[2]', 'view');
                $form->addElement('hidden', 'm[3]', 'edit');
                $form->addElement('hidden', 'revid', $revid);
                $form->addElement('hidden', 'id', $id);
                $form->addElement('header', 'frmTitle', sprintf(_('Attach final documents to revision #%s (Max 32mo for the two files)'), $revNumber));
                // Source
                $actualFileName = (!empty($revHeader['src_filename'])) ? $revHeader['src_filename'] : _('none');
                $actualFileName .= '<br>('._('Allowed extension').': '.implode(' ', $srcAllowedFileExt).')';
                $form->addElement('file', 'src_fid', _('Final Editable Document').'<br><em style="color:grey;">'._('Actual:').$actualFileName.'</em>');
                // Final
                $actualFileName = (!empty($revHeader['pub_filename'])) ? $revHeader['pub_filename'] : _('none');
                $actualFileName .= '<br>('._('Allowed extension').': '.implode(' ', $pubAllowedFileExt).')';
                $form->addElement('file', 'pub_fid', _('Final Document').'<br><em style="color:grey;">'._('Actual:').$actualFileName.'</em>');
                // Purpose
                $form->addElement('textarea', 'purpose', _('Revision purpose'), ['rows' => 4, 'cols' => 40]);
                $form->setDefaults(['purpose' => $revHeader['purpose']]);
                $form->addRule('purpose', _('Field is required'), 'required');
                $form->addElement('submit', 'btnSubmit', _('Submit'));

                if (!$form->validate()) {
                    $_BODY .= $form->toHTML();

                    $javascript = <<<'EOF'
                <script type="text/javascript">
                    $('form#add').on('submit', function(e) {
                        let totalFileSize = 0;
                        $(this).find('input[type="file"]').each(function() {
                            if ($(this).context.files[0]) {
                                totalFileSize += $(this).context.files[0].size;
                            }
                        });

                        if (totalFileSize > 0 && (totalFileSize / 1024 / 1024) > 32) {
                            alert('Total file size exceeds 32Mo');
                            e.stopPropagation();

                            return false;
                        }
                    });
                </script>
EOF;
                    $_BODY .= $javascript;
                    break;
                }

                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $a = [];
                $fileList = [
                    'src_fid' => _('Final Editable Document'),
                    'pub_fid' => _('Final Document'),
                ];
                foreach ($fileList as $field => $fileLabel) {
                    $file = $form->getElement($field);
                    // check uploaded file
                    $file_array = $file->getValue();
                    if (empty($file_array['tmp_name'])) {
                        continue;
                    }
                    if (!is_file($file_array['tmp_name'])) {
                        $_ERROR[] = "$fileLabel: "._('File uploaded not valid');
                        continue;
                    }
                    // Check file type/extention
                    $fileUploadExtension = mb_strtolower(basicFile::getExtensionFromFileName($file_array['name']));
                    switch ($field) {
                        case 'src_fid':
                            $allowedExtensionList = $srcAllowedFileExt;
                            break;
                        case 'pub_fid':
                            $allowedExtensionList = $pubAllowedFileExt;
                            break;
                    }
                    if (!in_array($fileUploadExtension, $allowedExtensionList, true)) {
                        $_ERROR[] = "$fileLabel: "._('File extension not valid').', '.
                            _('only the following extensions are allowed').': '.
                            implode(' ', $allowedExtensionList);
                        continue;
                    }
                    // Upload to tldFile
                    $fileUploadName = "DMS_$id\_rev_$revNumber.$fileUploadExtension";
                    $fid = tldFile::upload(
                        $file_array['tmp_name'],
                        tldDMS::getFilePath(),
                        $fileUploadName
                    );
                    if (is_string($fid)) {
                        $_ERROR[] = "$fileLabel: "._('File not uploaded').': '.$fid;
                        continue;
                    }
                    // Assign revision file id
                    $a[$field] = $fid;
                }
                // look if there is issues with files
                if (!empty($_ERROR)) {
                    $_BODY .= $form->toHTML();
                    break;
                }
                // if ok, update/assign all info
                $a['purpose'] = $vars['purpose'];
                $e = $rev->update($a);
                if (is_string($e)) {
                    $_ERROR[] = _('Revision informations not updated').': '.$e;
                    break;
                }
                // Liste des fields
                $fieldsToUpdate = [
                    'src_fid' => 'Final Editable Document',
                    'pub_fid' => 'Final Document',
                    'purpose' => 'Revision purpose',
                ];
                // Prepare logs
                $flagLog = false;
                $log = 'Revision updated:<br><ul>';
                foreach ($fieldsToUpdate as $field => $label) {
                    // if not change of the field, go to next one
                    if ($revHeader[$field] == $a[$field]) {
                        continue;
                    }
                    // else log
                    switch ($field) {
                        case 'src_fid':
                        case 'pub_fid':
                            if (!isset($a[$field])) {
                                continue 2;
                            }
                            $from = 'file#'.$revHeader[$field];
                            $to = 'file#'.$a[$field];
                            break;
                        default:
                            $from = $revHeader[$field];
                            $to = $a[$field];
                            break;
                    }
                    $log .= "<li>$label <strong>FROM</strong> \'$from\' <strong>TO</strong> \'$to\'";
                    $flagLog = true;
                }
                $log .= '</ul>';
                // Log
                if ($flagLog) {
                    $rev->addLogEntry($user->getID(), $log);
                }
                $_CONF[] = _('Revision update and document attachment process completed');
                // Display revision
                $_BODY .= _getRevisionView();
                break;
            case 'getDraftFile':
                $file = new tldFile($revHeader['src_fid']);
                if (!$file->isFile()) {
                    $_ERROR[] = _('File not found');
                    break;
                }
                $file->download();
                exit;
                break;
            case 'getPubFile':
                $file = new tldFile($revHeader['pub_fid']);
                if (!$file->isFile()) {
                    $_ERROR[] = _('File not found');
                    break;
                }
                $file->download();
                exit;
                break;
            case 'getMergedCoverPageAndPubFile':
                // Check final document
                $publishFile = new tldFile($revHeader['pub_fid']);
                if (!$publishFile->isFile()) {
                    $_ERROR[] = _('Final document not found');
                    break;
                }
                if ('pdf' != mb_strtolower(basicFile::getExtensionFromFileName($publishFile->getOriginalFileName()))) {
                    $_ERROR[] = _('Final document not a PDF, can not merge with cover page.');
                    break;
                }
                // Create cover page in HTML
                $html = _getHTMLCoverPage();
                // Create PDF from HTML
                $html2pdf = new tldHTML2PDF($html);
                // Initialise the merge
                $tool = new tldPDFToolKit();
                // Add cover page
                $e = $tool->addFile($html2pdf->itsConvertedPdfFile->getFilePath());
                if (is_string($e)) {
                    $_ERROR[] = _('Was not able to add cover page')."<br>$e";
                    break;
                }
                // Add final doc
                $publishFileName = $publishFile->getOriginalFileName();
                $publishFile->itsFile->copy("/tmp/$publishFileName");
                $e = $tool->addFile("/tmp/$publishFileName");
                if (is_string($e)) {
                    $_ERROR[] = _('Was not able to add final document')."<br>$e";
                    break;
                }
                $e = $tool->merge();
                if (is_string($e)) {
                    $_ERROR[] = _('An error occured during the merge')."<br>$e";
                    break;
                }
                $tool->out("DMS_$id\_rev_$revNumber.pdf");
                exit;
                break;
            case 'logs':
                $report = new tldReportColumnar(
                    $rev->getLog(),
                    [
                        'xItems' => [
                            'id' => 'ID#',
                            'date' => _('Date'),
                            'poster_fullname' => _('Poster'),
                            'comment' => _('Comment'),
                        ],
                    ]
                );
                $_BODY = $report->fetch();
                break;
            default:
                $_BODY = _getRevisionView();
                break;
        }

        break;
    default:
        // Add info for cover page + final doc
        foreach ($revisionList as $k => $val) {
            $revisionList[$k]['merge_cover_pub'] = _('Get PDF');
        }
        // List revisions
        $report = new tldReportColumnar(
            $revisionList,
            [
                'xItems' => [
                    'revision' => _('Revision'),
                    'dt' => _('Date'),
                    'src_filename' => _('Final Editable document'),
                    'pub_filename' => _('Final document'),
                    'merge_cover_pub' => _('Cover page').' + '._('Final document'),
                    'seq_id' => _('Approval SEQ#'),
                    'status' => _('Approval SEQ').'<br>'._('status'),
                    'purpose' => _('Revision purpose'),
                ],
                'title' => _('DMS Revisions'),
                'links' => [
                    'revision' => [
                        'url' => "$php_self?m[0]=view&m[1]=revision&m[2]=view&id=$id",
                        'params' => ['revid' => 'id'],
                    ],
                    'src_filename' => [
                        'url' => "$php_self?m[0]=view&m[1]=revision&m[2]=view&m[3]=getDraftFile&id=$id",
                        'params' => ['revid' => 'id'],
                    ],
                    'pub_filename' => [
                        'url' => "$php_self?m[0]=view&m[1]=revision&m[2]=view&m[3]=getPubFile&id=$id",
                        'params' => ['revid' => 'id'],
                    ],
                    'seq_id' => [
                        'url' => 'http://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view',
                        'params' => ['id' => 'seq_id'],
                        'target' => '_blank',
                    ],
                    'merge_cover_pub' => [
                        'url' => "$php_self?m[0]=view&m[1]=revision&m[2]=view&m[3]=getMergedCoverPageAndPubFile&id=$id",
                        'params' => ['revid' => 'id'],
                    ],
                ],
            ]
        );
        $_BODY = $report->fetch();
        break;
}

function _getRevisionView()
{
    global $rev;
    $report = new tldAssocTable(
        $rev->getHeader(),
        [
            'revision' => _('Revision'),
            'dt' => _('Date'),
            'src_filename' => _('Final Editable Document'),
            'pub_filename' => _('Final Document'),
            'seq_id' => _('Approval SEQ#'),
            'status' => _('Approval SEQ status'),
            'purpose' => _('Revision purpose'),
        ],
        [
            'title' => _('Revision').'#'.$rev->getRevisionNumber(),
        ]
    );

    return $report->fetch();
}

function _getHTMLCoverPage()
{
    global $rev,$dms,$_CHARSET,$revisionList,$user,$DMS_URL;
    $revHeader = $rev->itsHeader;
    $revNumber = $revHeader['revision'];
    // Get DMS header
    $header = new tldAssocTable(
        $dms->itsHeader,
        [
            'id' => _('DMS#'),
            'parent_id' => _('Parent').' DMS#',
            'dt' => _('Date creation'),
            'ownerFullname' => _('Owner'),
            'title' => _('Title'),
            'subject' => _('Subject'),
            'description' => _('Description'),
            'lang' => _('Language'),
            'status' => _('Status'),
            'typeDesc' => _('Type'),
            'periodicity' => _('Revision periodicity'),
            'sysref' => _('System reference'),
            'portal' => _('Portal'),
            'access_type' => _('Access type'),
        ],
        [
            'title' => _('DMS information'),
        ]
    );
    // Get coverage
    $coverage = null;
    $cells = [];
    $reportBu = new tldReportColumnar(
        $dms->getBu(),
        [
            'xItems' => [
                'location' => _('Business Unit'),
            ],
            'showItemNumbers' => true,
            'sortable' => 'no',
            'title' => _('Business unit coverage'),
        ]
    );
    $cells[] = $reportBu->fetch();
    $reportDpt = new tldReportColumnar(
        $dms->getDepartment(),
        [
            'xItems' => [
                'department' => _('Department'),
            ],
            'showItemNumbers' => true,
            'sortable' => 'no',
            'title' => _('Department coverage'),
        ]
    );
    $cells[] = $reportDpt->fetch();
    $coverage = new tldHTMLTable(
        $cells,
        [
            'cols' => 2,
            'attribs' => ['table' => "width='100%'"],
        ]
    );
    // Get revision history
    $revision = new tldReportColumnar(
        $revisionList,
        [
            'xItems' => [
                'revision' => _('Revision'),
                'dt' => _('Date'),
                'seq_id' => _('Approval SEQ#'),
                'status' => _('Approval SEQ status'),
                'purpose' => _('Revision purpose'),
            ],
            'title' => _('DMS Revision history'),
            'sortable' => 'no',
        ]
    );
    // Get Sequence# and validation approvers
    $query = <<<EOF
SELECT *,
(SELECT CONCAT(people.firstname, ', ', people.lastname) FROM people
     WHERE id=tasks_comments.poster
) AS poster_fullname
FROM tasks_comments
WHERE parent_id = {$revHeader['seq_id']}
AND `status` LIKE 'ACCEPT'
AND date = (SELECT MAX( t2.date )
    FROM tasks_comments AS t2
    WHERE t2.parent_id = {$revHeader['seq_id']}
    AND t2.status LIKE 'ACCEPT'
    AND t2.step = tasks_comments.step
)
EOF;
    $seqSteps = tldUtils::getSqlToAssocArray($query);
    $sequence = new tldReportColumnar(
        $seqSteps,
        [
            'xItems' => [
                'step' => _('Step'),
                'date' => _('Accept Date'),
                'poster_fullname' => _('Approver'),
            ],
            'title' => sprintf(_('Revision %s Sequence approval'), $revNumber),
            'sortable' => 'no',
        ]
    );
    // List of people notified
    $logRecipientsTitle = sprintf(_('Revision %s notification recipients'), $revNumber);
    $logRecipientsEntry = $rev->getActiveNotificationLog();
    $logRecipients = (empty($logRecipientsEntry['comment'])) ? _('No log found') : $logRecipientsEntry['comment'];
    $recipients = <<<EOF
      <h3>$logRecipientsTitle</h3>
      <p>$logRecipients</p>
EOF;
    // Info of cover page creation
    if (_isUserLoggedIn()) {
        $fullname = $user->getFullname();
    } else {
        $fullname = 'unauthenticated user';
    }
    $coverPageInfo = sprintf(_('Generated from DMS portal the %s by %s'), date('Y-m-d'), $fullname);
    // Create HTML
    $html = <<<EOF
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <title>DMS#{$dms->getID()} - Cover page - Revision $revNumber</title>
    <meta http-equiv="Content-Type" content="text/html; charset=$_CHARSET">
    <style>
body { text-align:center; }
table { margin:auto; }
h3 { border-bottom:1px solid grey; }
.footer { color:grey; font-size:8px; text-align:center; }
    </style>
  </head>
  <body width:100%;>
    <h1>DMS#{$dms->getID()} - Revision $revNumber</h1>
    <div style="page-break-inside: avoid">
    {$header->fetch()}
    </div>
    <div style="page-break-inside: avoid">
    {$coverage->fetch()}
    </div>
    <div style="page-break-inside: avoid">
    {$revision->fetch()}
    </div>
    <div style="page-break-inside: avoid">
    {$sequence->fetch()}
    </div>
    <div style="page-break-inside: avoid">
    $recipients
    </div>
    <br>
    <div class="footer">
      <p>$coverPageInfo<br>$DMS_URL</p>
    </div>
  </body>
</html>
EOF;

    return $html;
}
