<?php
require_once 'HTML/QuickForm/advmultiselect.php';
include_once('calendar.inc.php');

$DEFAULT_TITLE .= "\Emails";
$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=emails">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=emails&m[1]=newEmail">Send Email</a>
EOF;

switch ($m[1]) {
	case 'newEmail':
		$DEFAULT_MENU = '';
		$DEFAULT_TITLE .= "\New Email";
		// File size limit
		$FILE_SIZE_LIMIT = 2 * pow(1024, 2); // 2 MB
		$smarty->assign('FILE_SIZE_LIMIT', $FILE_SIZE_LIMIT);
		// Default form values
		$_FORM_DEFAULTS = [];
		if (empty($module) || empty($id) || !is_numeric($id)) {
			$DEFAULT_ERROR[] = 'WARNING: Data sent empty or invalid ! No print version will be added to the email...';
		} else {
			include('form.default.inc.php');
			$url = tldModLink::getURL(strtoupper($module), $id);
			$body .= '<p><a href="' . $url . '">Click here to go back to ' . strtoupper($module) . ' #' . $id . '</a></p>';
		}
		// Include JS function to add external recipient on input to[2]
		$body .= $smarty->fetch("$PATH/emails/form.script.inc.tpl");
		// Create the form
		$form = new HTML_QuickForm('frmNewEmail', 'post');
		$form->addElement('header', 'title', 'Email content');
		$form->addElement('hidden', 'm[0]', 'emails');
		$form->addElement('hidden', 'm[1]', 'newEmail');
		$form->addElement('hidden', 'module', $module);
		$form->addElement('hidden', 'id', $id);
		// Get the email list of all tld people
		$SQL = "SELECT CONCAT(lastname,', ',firstname) AS fullname,email FROM people WHERE email!='' AND hidden=0 ORDER BY fullname";
		$tldEmails = tldUtils::getSqlToAssocArray($SQL, 'smartyOptions', ['email', 'fullname']);
		// Add form inputs (email content)
		if ($user->isInGroup('superuser')) {
			$form->addElement('select', 'from', 'From', ['' => ''] + $tldEmails);
		}
		$form->addElement('text', 'subject', 'Subject', ['size' => 79, 'id' => 'subject']);
		$form->addElement('textarea', 'msg', 'Message', ['cols' => 60, 'rows' => 10, 'id' => 'msg']);
		// File attachment
		$maxFileSizeInMB = round($FILE_SIZE_LIMIT / pow(1024, 2));
		$form->addElement('header', 'title', "File attachment (size limit: $maxFileSizeInMB MB)");
		$form->addElement('file', 'file', 'Attachment', ['onchange' => 'javascript:checkFileSize(this);']);
		// External emails
		$form->addElement('header', 'title', 'External Recipient(s)');
		$form->addElement('textarea', 'to[2]', 'External Recipient:', [
				'size' => 79, 'id' => 'to[2]', 'cols' => 60, 'rows' => 4,
				'style' => 'background:#dedede;', 'disabled' => 'disabled']
		);
		$form->addElement('text', 'ExtName', 'Recipient Name:', ['size' => 28, 'id' => 'ExtName']);
		$form->addElement('text', 'ExtEmail', 'Recipient Email:', ['size' => 28, 'id' => 'ExtEmail']);
		$form->addElement('button', 'AddExtEmail', 'Add External Email', ['onClick' => 'javascript:addRecipient();']);
		$form->addElement('button', 'delExtEmail', 'Reset', ['onClick' => 'javascript:delRecipient();']);
		// TLD emails
		$form->addElement('header', 'title', 'TLD and Adressbook Recipient(s)');
		$ams =& $form->addElement('advmultiselect', 'to[0]', null, $tldEmails,
			['size' => 10, 'class' => 'pool', 'style' => 'width:250px;']
		);
		$ams->setLabel(['To <em>(max 10 recipients)...</em>', 'TLD Addressbook', 'To']);
		$ams->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
		$ams->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
		// TLD BUs emails
		$locations = tldLocation::getLocationList('smartyOptions');
		$locationEmails = [];
		foreach ($locations as $lid => $lname) {
			$location = new tldLocation($lid);
			$locationPartEmail = $location->getPartsEmail();
			if (!empty($locationPartEmail)) {
				$locationEmails[$locationPartEmail] = $location->getShortName() . " ($locationPartEmail)";
			}
			$locationServiceEmail = $location->getServiceEmail();
			if (!empty($locationServiceEmail)) {
				$locationEmails[$locationServiceEmail] = $location->getShortName() . " ($locationServiceEmail)";
			}
		}
		$locationEmails['euparts@tld-europe.com'] = 'TLD EUR (euparts@tld-europe.com)';
		$cms =& $form->addElement('advmultiselect', 'to[3]', null, $locationEmails,
			['size' => 10, 'class' => 'pool', 'style' => 'width:250px;']
		);
		$cms->setLabel(['To <em>(max 10 recipients)...</em>', 'TLD BU & Department emails', 'To']);
		$cms->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
		$cms->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
		// Historical emails
		$HistEmails = $user->getEmailAdressbook();
		foreach ($HistEmails as $email) {
			$adressbook[$email['value']] = $email['list_key'] . ' (' . $email['value'] . ')';
		}
		$bms =& $form->addElement('advmultiselect', 'to[1]', null, $adressbook,
			['size' => 10, 'class' => 'pool', 'style' => 'width:250px;']
		);
		$bms->setLabel(['To <em>(max 10 recipients)...</em>', 'Personnal Addressbook', 'To']);
		$bms->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
		$bms->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
		// Add Button and set Rules
		$form->addElement('submit', 'btnSubmit', 'Send Email', ['onClick' => 'javascript:unsetDisable(\'to[2]\')']);
		$form->addRule('subject', 'Subject required', 'required');
		$form->addRule('msg', 'Email content required', 'required');
		$form->addRule('from', 'Required', 'required');
		// Set defaults values
		$form->setDefaults(['from' => $user->getEmail()] + $_FORM_DEFAULTS);

		if (!$form->validate()) {
			$body .= $form->toHTML();
			break;
		}

		$DEFAULT_ERROR = []; // Initialize error msg
		$data = $form->getSubmitValues();
		$to = null;

		// 1 -> Get and check contacts to add into the personnal adressbook
		if (!empty($data['to'][2])) {
			$ExtRecipients = explode('><', substr(substr($data['to'][2], 1), 0, -1));
			foreach ($ExtRecipients as $val) {
				$contact = explode(';', $val);
				// Get all recipients from External emails added manualy
				$to .= ',' . $contact[1];
				// Check duplicate email before to add in adressbook
				if (!is_array($adressbook) || !array_key_exists($contact[1], $adressbook)) {
					$newEmail['list_key'] = $contact[0];
					$newEmail['value'] = $contact[1];
					$a = $user->setEmailAdressbook($newEmail); // put contacts on adressbook
					if (is_numeric($a)) {
						$body .= "New recipient '" . $newEmail['list_key'] . "' with email '" . $newEmail['value'] . ' added in adressbook<br/>';
					}
				} else {
					$DEFAULT_ERROR[] = "WARNING: Recipient '" . $contact[0] . "' with email '" . $contact[1] . ' NOT added in adressbook. REASON: Duplicate email';
				}
			}
		}

		// 2 -> Select the right expeditor
		if (empty($data['from'])) {
			$data['from'] = $user->getEmail();
		}

		// 3 -> Get all recipients
		if (!empty($data['to'][0])) {
			foreach ($data['to'][0] as $email) $to .= ',' . $email;
		} // from TLD adressbook
		if (!empty($data['to'][1])) {
			foreach ($data['to'][1] as $email) $to .= ',' . $email;
		} // from Personnal adressbook
		if (!empty($data['to'][3])) {
			foreach ($data['to'][3] as $email) $to .= ',' . $email;
		} // from TLD BU & Department emails
		$data['to'] = substr($to, 1);        // clean recipient
		$data['to'] .= ',' . $data['from']; // copy sender to be sure the email have been sent

		// 4 -> Get printVersion if exists and concat with msg
		$emailBody = nl2br($data['msg']);
		if (!empty($document)) {
			$emailBody .= $document;
		}

		// 5 -> Get file if any
		$formFile = $form->getElement('file');
		$formFileValues = $formFile->getValue();
		$tmpFilePath = $formFileValues['tmp_name'];
		// if file uploaded
		if (!empty($tmpFilePath)) {
			// look for size limit
			if ($formFileValues['size'] > $FILE_SIZE_LIMIT) {
				$tmpFileSizeInMB = round($formFileValues['size'] / pow(1024, 2), 2);
				$DEFAULT_ERROR[] = "ERROR: File attachment too big! Size limit is $maxFileSizeInMB MB, your file is $tmpFileSizeInMB MB";
				unlink($tmpFilePath); // remove the file from the server
				$body .= $form->toHTML();
				break;
			}
			// rename file
			$newFilePath = "/tmp/{$formFileValues['name']}";
			rename($tmpFilePath, $newFilePath);
		}

		// 6 -> Send the email
		$e = tldUtils::emailAttachment(
			$data['to'],
			$data['from'],
			$data['subject'],
			$emailBody,
			$newFilePath
		);
		if ($e) {
			$body .= '<p>Email has been SENT successfully !</p>';
			$DEFAULT_ERROR = [];
		} else {
			$DEFAULT_ERROR[] = 'ERROR: An error occurred, email not sent...';
		}

		// 7 - Add log
		$emailLog = <<<EOF
Email sent by {$user->getFullname()}

From: {$data['from']}
To: {$data['to']}
Subject: {$data['subject']}
Attachment: {$file['name']}

{$data['msg']}
EOF;

		switch ($module) {
			case 'toc':
                $id = tldModFile::insert([
                    'module' => 'TOC',
                    'parent_id' => $moduleObj->getId(),
                    'level' => 0,
                    'description' => 'File uploaded through email function',
                ], ['tmp_name' => $newFilePath] + $formFileValues);
                if (!is_string($id)) {
                    $emailLog.= sprintf('<br><br><a href="/en/private/common/index.php?m[0]=files&m[1]=view&id=%s">Download attachment</a>', $id);
                }
				$moduleObj->addLogEntry($user->getID(), TldDatabase::escape($emailLog));
				break;
			default:
				tldModLog::insert([
					'parent_id' => $id,
					'module' => strtoupper($module),
					'poster' => $user->getID(),
					'comment' => TldDatabase::escape($emailLog),
					'log_num' => 0,
				]);
				break;
		}

		// Clean up file from TMP folder
		if (!empty($newFilePath)) {
			unlink($newFilePath);
		}
		break;
	default:
		$body = <<<EOF
<h3>Module Emails</h3>
<p>Welcome to module Emails</p>
EOF;
		break;
}
