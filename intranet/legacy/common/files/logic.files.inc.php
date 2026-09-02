<?php
$DEFAULT_TITLE .= "\Files";
$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=files">Home</a>
EOF;

if ($user->isInGroup('superuser')) {
	$DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="files/files_admin.php">Files Admin</a>
EOF;
}

$MODULE_LIST = array_keys(tldUtils::getModLinks());

switch ($m[1] ?? null) {
	case 'form':
		switch ($m[2] ?? null) {
			case 'editDescription':
				$DEFAULT_MENU = null;
				$DEFAULT_TITLE .= "\Edit file description";

				// Get params
				if (empty($id) || !is_numeric($id)) {
					$DEFAULT_ERROR[] = 'ERROR: Parameter sent empty or invalid';
					break;
				}
				$EMAIL = $user->getEmail();
				$file = new TldModFile($id);
				if ($file->isEmpty()) {
					$DEFAULT_ERROR[] = "ERROR: File #$id could not be found";
					break;
				}
				$header = $file->itsHeader;
				$module = $header['module'];
				$parent_id = $header['parent_id'];
				// Check Poster ID or Superuser
				if ($header['poster'] != $EMAIL && !$user->isInGroup('superuser')) {
					$DEFAULT_ERROR[] = 'ERROR: You do not have permission to edit this file';
					break;
				}

				// Display form
				$form = new HTML_QuickForm('frmEdit', 'post');
				$form->addElement('header', 'title', "Edit file #$id description: ");
				$form->addElement('hidden', 'm[0]', 'files');
				$form->addElement('hidden', 'm[1]', 'form');
				$form->addElement('hidden', 'm[2]', 'editDescription');
				$form->addElement('hidden', 'id', $id);
				$form->addElement('hidden', 'module', $module);
				$form->addElement('hidden', 'parent_id', $parent_id);
				$form->addElement('textarea', 'description', 'File Description', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']);
				$form->addElement('submit', 'btnSubmit', 'Submit');
				$form->addRule('description', 'Required field', 'required');
				$form->setDefaults(['description' => $header['description']]);

				if (!$form->validate()) {
					$body .= $form->toHTML();
					break;
				}

				$vars = tldUtils::cleanupFormInput($form->exportValues());
				$fields = ['description'];
				// Update the Timesheet
				$e = $file->update($vars, $fields);
				if (is_string($e)) {
					$DEFAULT_ERROR[] = "ERROR: Problem updating...<br/>Reason: $e";
					break;
				}
				$body .= 'File Description updated successfully!';

				$parentIdLink = '';
				if ($module !== 'SCM') {
					$url = tldModLink::getURL($module, $parent_id);
					$parentIdLink = '#' . $parent_id;
				} else {
					$links = tldUtils::getModLinks();
					$url = $links[$module];
				}
				$link = "<a href=\"$url\">Click here to go back to $module $parentIdLink</a>";
				$body .= <<<EOF
<p>$link</p>
<br><br><br><br>
EOF;
				break;
			case 'newFile':
				$DEFAULT_MENU = null;
				$DEFAULT_TITLE .= "\Add file";
				// Get params
				$module = strtoupper($_REQUEST['module']);
				$parent_id = $_REQUEST['parent_id'];
				$level = empty($_REQUEST['level']) ? 0 : $_REQUEST['level'];
				// Check module
				if (!in_array($module, $MODULE_LIST) && !$user->isInGroup('superuser')) {
					$DEFAULT_ERROR[] = "ERROR: Module '$module' is not valid";
					break;
				}
				// Check parent_id
				if ((empty($parent_id) || !is_numeric($parent_id)) && $module !== 'SCM') {
					$DEFAULT_ERROR[] = 'ERROR: Parameter parent_id empty or invalid...';
					break;
				}
				$parentIdLink = '';
				if ($module !== 'SCM') {
					$url = tldModLink::getURL($module, $parent_id);
					$parentIdLink = '#' . $parent_id;
				} else {
					$links = tldUtils::getModLinks();
					$url = $links[$module];
				}
				$link = "<a href=\"$url\">Click here to go back to $module $parentIdLink</a>";
				$body .= <<<EOF
			$link
EOF;
				// Display form
				$form = new HTML_QuickForm('frmNew', 'post', '', '', ['enctype' => 'multipart/form-data']);
				$form->addElement('header', 'title', "Submit new file for $module $parentIdLink");
				$form->addElement('hidden', 'm[0]', 'files');
				$form->addElement('hidden', 'm[1]', 'form');
				$form->addElement('hidden', 'm[2]', 'newFile');
				$form->addElement('hidden', 'module', $module);
				$form->addElement('hidden', 'parent_id', $parent_id);
				$form->addElement('hidden', 'level', $level);
				$form->addElement('textarea', 'description', 'File Description (if you don\'t fill it, the filename will be set as description by default)', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']);
				$form->addElement('header', 'titleInfo', 'Warning: Filename length is limited to ' . tldFile::fileNameLengthLimit . ' chars');
				$form->addElement('file', 'file', 'Attachment');
				$form->addElement('submit', 'btnSubmit', 'Submit');
				$form->addRule('file', 'Required field', 'required');

				if (!$form->validate()) {
					$body .= $form->toHTML();
					break;
				}

				$vars = tldUtils::cleanupFormInput($form->exportValues());
				// Check temp file
				$file = $form->getElement('file');
				$file_array = $file->getValue();

				if ($file_array['tmp_name'] == '') {
					$DEFAULT_ERROR[] = "ERROR: You didn't upload a file...";
					break;
				}

                if ('' === $vars['description']) {
                    $vars['description'] = $file_array['name'];
                }
				// Insert mod File
				$e = tldModFile::insert($vars, $file_array);
				if (is_string($e)) {
					$DEFAULT_ERROR[] = "ERROR: There was a problem attaching the file. Reason: $e";
					break;
				}

                $linkAddOtherFile  = "</br><a href=\"$php_self?m[0]=files&m[1]=form&m[2]=newFile&module=$module&parent_id=$parent_id&level=$level\">Add another file</a>";
				$body .= "$linkAddOtherFile<br/>File successfully attached... Click link above to return to document.";
				break;
		}
		break;
	case 'view':
		if (empty($id) || !is_numeric($id)) {
			$DEFAULT_ERROR[] = 'ERROR: Parameter sent empty or invalid';
			break;
		}
		$file = new tldModFile($id);
		if ($file->isEmpty()) {
			$DEFAULT_ERROR[] = "ERROR: File #$id could not be found";
			break;
		}

		$_ACL_DELETE = [
			'PDC' => ['gg_SUPPORT', 'role_RME'],
			'ER' => ['gg_SUPPORT', 'role_RME', 'role_QAM', 'role_QA'],
			'ER_UPGRADE' => ['gg_MIS', 'role_PSM', 'role_PSE', 'role_PSA', 'gg_PARTS', 'gg_SERVICE'],
			'NCR' => ['role_QE', 'role_QAM'],
			'CPA' => ['role_QAM'],
			'SOL' => ['role_SA'],
			'SOR' => ['role_SA'],
			'EAP' => ['role_EM'],
			'CCR' => ['gg_SALES'],
			'COR' => ['gg_SALES'],
			'CSR' => ['gg_SERVICE', 'role_CSM', 'role_CSA'],
			'CUR' => ['gg_SALES'],
			'PN' => ['gg_ENG'],
			'CRAB' => ['role_QE', 'role_QAM'],
			'ESR' => ['gg_TRANSPORT', 'role_PSA'],
			'GWF' => ['gg_MIS'],
			'ISR' => ['gg_PUR'],
			'MIM' => ['gg_SALES'],
			'MISINV_ITM' => ['gg_MIS'],
			'PIP' => ['gg_ENG'],
			'SB' => ['role_PSM', 'role_PSE', 'role_PSA'],
			'SB3' => ['role_PSM', 'role_PSE', 'role_PSA'],
			'SCAR' => ['role_QE', 'role_QAM', 'role_RME'],
			'SCM' => ['gg_SALES'],
			'SFR' => ['gg_SALES'],
			'SPR' => ['gg_PARTS'],
			'SPQ' => ['role_SPM', 'gg_PARTS'],
			'SQE' => ['gg_TRANSPORT'],
			'SR' => ['gg_SERVICE'],
			'TOC' => ['gg_SERVICE','gg_SUPPORT'],
			'TTS' => ['gg_MIS'],
			'VWC' => ['gg_PUR','gg_QUALITY'],
			'WC' => ['gg_SUPPORT'],
			'BP' => ['superuser'],
			'MEAP' => ['role_EM', 'role_ES'],
		];

		$EMAIL = $user->getEmail();
		$header = $file->itsHeader;

        $module = $file->getModule();
        $isGrantedFileDeletePermission = static function (tldModFile $file) use ($user, $module, $_ACL_DELETE): bool {
            if ($user->isInGroup('superuser')) {
                return true;
            }
            if ($user->isInGroup($_ACL_DELETE[$module] ?? [])) {
                return true;
            }
            if ($module === 'GWF') {
                $gwf = new tldGWF($file->getParentID());
                if ($gwf->getAssignor() === $user->getID()) {
                    return true;
                }
            }

            return false;
        };

		$delete_allowed = $_ACL_DELETE[$module];
		$parentIdLink = '';
		if ($module !== 'SCM') {
			$parent_id = $file->getParentID();
			$url = tldModLink::getURL($module, $parent_id);
			$parentIdLink = '#' . $parent_id;
		} else {
			$url = '/en/private/sales_service/service.php?m[0]=scm&m[1]=files';
		}
		$link = "<a href=\"$url\">Click here to go back to $module $parentIdLink</a>";
		$body .= <<<EOF
<p>$link</p>
<br><br><br><br>
EOF;

		switch ($m[2]) {
			case 'out':
                $file->outFile();
				exit;
			case 'delconf':
				$body .= <<<EOF
<a href="$php_self?m[0]=files&m[1]=view&m[2]=del&id=$id">Do you really want to delete this file?</a>
EOF;
				break;
			case 'del':
				if ($isGrantedFileDeletePermission) {
					$e = $file->delete();
					if (is_string($e)) {
						$DEFAULT_ERROR[] = "ERROR: Can not delete file, reason: $e";
						break;
					}
                    $linkAddOtherFile  = "<a href=\"$php_self?m[0]=files&m[1]=form&m[2]=newFile&module=$module&parent_id=$parentIdLink\">Add a file to this module</a>";
					$body .= "$linkAddOtherFile<p>File has now been deleted successfully...</p>";
				} else {
					$DEFAULT_ERROR[] = 'ERROR: You do not have permissions to delete this file.';
				}
				break;
            default:
                $links = [];
                $showDelete = $isGrantedFileDeletePermission($file);
                $showEdit = ($header['poster'] === $EMAIL || $user->isInGroup('superuser')) && $module !== 'ER_UPGRADE';

                if (!$showDelete && !$showEdit) {
                    $file->outFile();
                    exit;
                }
                $links["Download"] = "$php_self?m[0]=files&m[1]=view&m[2]=out&id=$id";

                $body .= <<<EOF
                <h3>File ID#$id</h3>
                <ul>
                    <li><a href="$php_self?m[0]=files&m[1]=view&m[2]=out&id=$id">Download</a></li><br>
EOF;
                if ($showEdit) {
                    $body.= <<<EOF
                    <li><a href="$php_self?m[0]=files&m[1]=form&m[2]=editDescription&id=$id">Edit Description</a></li><br>
EOF;
                }
                if ($showDelete) {
                    $body.= <<<EOF
                    <li><a href="$php_self?m[0]=files&m[1]=view&m[2]=delconf&id=$id">Delete</a></li><br>
EOF;
                }
                $body .= '</ul>';
				break;
		}
		break;
	default:
		$body = $smarty->fetch("$PATH/${m[0]}/homepage.${m[0]}.tpl");
		break;
}
