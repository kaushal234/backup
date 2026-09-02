<?php

switch ($m[2]) {
    case 'byNum':
    case 'byAssignee':
    case 'byTechnician':
    case 'byCustomerID':
    case 'byCustomerContactID1':
    case 'byCustomerContactID2':
    case 'surveyResultsDetails':
    case 'new':
    case 'new2':
    case 'new3':
    case 'new4':
    case 'newLink':
        $DEFAULT_MENU = '';
        $DEFAULT_TITLE .= '\New Link';
        // Check module
        $module = strtoupper($module);
        $modules = tldTask::getModuleList();
        if (!in_array($module, $modules, true)) {
            $DEFAULT_ERROR[] = "ERROR: Module '$module' is not valid";
            break;
        }
        $modules = array_intersect($modules, tldTOC::getLinkableModules());
        // Check parent_id
        if (empty($parent_id)) {
            $DEFAULT_ERROR[] = "ERROR: parent_id not set";
            break;
        }
        $url = tldModLink::getURL($module, $parent_id);
        $body .= <<<EOF
		<a href="$url">Click here to go back to $module #$parent_id</a>
EOF;
        $form = new HTML_QuickForm('frmNew', 'post');
        $form->addElement('header', 'title', "Submit new $module Link");
        $form->addElement('hidden', 'm[0]', 'toc');
        $form->addElement('hidden', 'm[1]', 'form');
        $form->addElement('hidden', 'm[2]', 'newLink');
        $form->addElement('hidden', 'module', $module);
        $form->addElement('hidden', 'parent_id', $parent_id);
        $form->addElement('select', 'type', 'Ref Type', $modules);
        $form->addElement('text', 'item', 'Ref#');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule("type", "This is a required field.", "required");
        $form->addRule("item", "This is a required field.", "required");

        if (!$form->validate()) {
            $body .= $form->toHTML();
            break;
        }

        $vals = tldUtils::cleanupFormInput($form->exportValues());
        $error = tldModLink::insert($vals['module'], $vals['parent_id'], $modules[$vals['type']], $vals['item']);
        if (is_string($error)) {
            $DEFAULT_ERROR[] = "ERROR: There was an error adding the new link. Reason: $error";
        }
        $body .= "<p>{$modules[$vals['type']]}#{$vals['item']} linked successfully!</p>";
        break;
    default:
        $body = 'This page has been migrated and should not be used anymore.';
}
