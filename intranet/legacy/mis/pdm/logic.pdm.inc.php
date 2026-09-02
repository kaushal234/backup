<?php

$DEFAULT_TITLE .= " \ PDM Username management";

if (!$user->isInGroup(["SUPERUSER"])) {
    $DEFAULT_ERROR[] = "You are not allowed.";
    return;
}

$DEFAULT_TITLE .= " \ Search user in PDM database";

$form = new HTML_QuickForm('frmByNum', 'post');
$form->addElement('text', 'username', 'Username');
$form->addElement('hidden', 'm[0]', 'pdm');

if ($success) {
    $body .= '<p>PDM Username has been updated!</p>';
}

if ($form->isSubmitted() && !isset($form->_elements['new_username'])) {
    $username = TldDatabase::escape($form->getElementValue('username'));

    $query = <<<SQL
SELECT * FROM [CTVAULT].[dbo].[Users] where Username = '$username'
SQL;

    $user = tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'pdm']);

    if ($user) {
        $form->addElement('header', '', sprintf('User found: %s #%s %s', $user['FullName'], $user['UserID'], $user['Email']));
        $form->addElement('hidden', 'user_id', $user['UserID']);
        $form->addElement('text', 'new_username', 'New Username');
        $form->addElement('text', 'new_email', 'New Email');

        if (($userId = TldDatabase::escape($form->getElementValue('user_id'))) && ($newUsername = TldDatabase::escape($form->getElementValue('new_username')))) {

            $set = "Username = '$newUsername'";

            if ($email = TldDatabase::escape($form->getElementValue('new_email'))) {
                $set .= ", Email = '$email'";
            }

            $updateQuery = <<<SQL
UPDATE [CTVAULT].[dbo].[Users] set $set where UserID = $userId
SQL;

            tldUtils::sqlExecute($updateQuery, 'odbc', ['src' => 'pdm']);

            header("location: $php_self?m[0]=pdm&success=true");
        }
    } else {
        $DEFAULT_ERROR[] = sprintf('User %s not found', $username);
    }
}

$form->addElement('submit', 'btnSubmit', 'Submit');
$body .= $form->toHTML();
