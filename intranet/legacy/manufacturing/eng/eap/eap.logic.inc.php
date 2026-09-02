<?php

use ApiBundle\Client;
use Symfony\Component\HttpClient\Exception\ClientException;

include_once('sales_service.inc.php');
include_once('../kpi/kpi.common.inc.php');
require_once 'HTML/QuickForm/advmultiselect.php';
$JS_INCLUDE=["/shared/javascript/overlib/overlib.js"];
$smarty->assign('js_includes', $JS_INCLUDE);

$DEFAULT_TITLE .= "\EAP";
// Get MOO
$moo_id = tldModule::getMOOIDByModule('eap');
$DEFAULT_MENU .= <<<EOF
	<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="$php_self?m[0]=eap">EAP Home</a>
	&nbsp;|&nbsp;<a href="$php_self?m[0]=eap&m[1]=forms&m[2]=byID">EAP by Number</a>
	&nbsp;|&nbsp;<a href="$php_self?m[0]=eap&m[1]=forms&m[2]=newEAP">Submit EAP</a>
	&nbsp;|&nbsp;<a href="$php_self?m[0]=eap&m[1]=mlList&m[2]=search">Search</a>
	&nbsp;|&nbsp;<a href="$php_self?m[0]=eap&m[1]=mlList&m[2]=byPN">By PN</a>
	&nbsp;|&nbsp;<a href="$php_self?m[0]=eap&m[1]=reports">Reports</a>
	&nbsp;|&nbsp;<a href="/en/private/directory/index.php?m[0]=people&m[1]=view&id=$moo_id">Owner</a>
	&nbsp;|&nbsp;<a href="/en/private/mis/mis.php?m[0]=help&m[1]=dms&id=2048">Help</a>
EOF;
if ($user->isInGroup(['eap', 'gg_mod_eap_admin', 'gg_ENG', 'gg_ADMIN'])) {
    $DEFAULT_MENU .= <<<EOF
	&nbsp;|&nbsp;<a href="./eap/eap_admin.php">Maintain EAPs</a>
EOF;
}

switch ($m[1] ?? null) {
    case 'forms':
        switch ($m[2] ?? null) {
            case 'byID':
                $DEFAULT_TITLE .= "\EAP by Number";
                $form = new HTML_QuickForm('frm', 'get', '', '', '', true);
                $form->addElement('hidden', 'm[0]', 'eap');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('hidden', 'single', '1');
                $form->addElement('header', 'title', 'View EAP by Number');
                $form->addElement('text', 'id', 'EAP#');
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $body = $form->toHTML();
                break;
            case 'newEAP':
                $DEFAULT_TITLE .= "\Add";

                // Constants
                $defaultDescription = <<<EOF
<p>- Add item/ajouter composant ### <br>
In BOM/dans nomenclature ###</p>
<p>- Delete item/supprimer composant ### <br>
in BOM/dans nomenclature ###</p>
<p>- Change qty of item/modifier qt� composant ### <br>
In BOM/dans nomenclature ###</p>
<p>- Change Box Number/modifier n� caisse composant ### <br>
In BOM/dans nomenclature ###</p>
<p>- Change Drawing / modifier plan composant ###<br>
in BOM/dans nomenclature ###</p>
EOF;

                // Look for possible parameters ------->

                $formDefaults = [];
                $moduleRecord = null;
                do {
                    // Check params
                    if (empty($from['mod']) || empty($from['id'])) {
                        break;
                    }
                    if (!in_array($from['mod'], ['MEAP', 'EAP', 'NCR', 'CRAB', 'FAQ'])) {
                        break;
                    }
                    if (in_array($from['mod'], ['NCR', 'FAQ'], true)) {
                        global $kernel;
                        try {
                            $client = $kernel->getContainer()->get(Client::class);
                        } catch (Exception $e) {
                            $DEFAULT_ERROR[] = 'ERROR: Could not get API client. Reason: ' . $e->getMessage();
                            return;
                        }

                        try {
                            $endpoint = $from['mod'] === 'NCR' ? 'quality/non_conformities' : 'quality/first_article_qualifications';
                            $moduleRecord = $client->find($endpoint, $from['id']);
                        } catch (Exception $e) {
                            $DEFAULT_ERROR[] = "ERROR: Could not get {$from['mod']}. Reason: " . $e->getMessage();
                            break;
                        }
                    } else {
                        $refClass = new ReflectionClass('tld' . $from['mod']);
                        $moduleRecord = $refClass->newInstance((int)$from['id']);
                    }

                    if (in_array($from['mod'], ['MEAP', 'EAP', 'CRAB']) && $moduleRecord->isEmpty()) {
                        $DEFAULT_ERROR[] = "WARNING: {$from['mod']}#{$from['id']} not found. Some features for EAP creation may not be available";
                        $moduleRecord = null;
                        break;
                    }
                    // Get Defaults
                    switch ($from['mod']) {
                        case 'MEAP':
                            $ifactor = ($moduleRecord->itsHeader['ifactor'] > 1) ? '10+' : 1;
                            $formDefaults = [
                                'factory' => $moduleRecord->itsHeader['factory'],
                                'short_desc' => $moduleRecord->itsHeader['short_desc'],
                                'ifactor' => $ifactor,
                                'models' => (array)$moduleRecord->itsHeader['model'],
                            ];
                            break;
                        case 'EAP':
                            $models = tldUtils::optionsByKeyValue($moduleRecord->getModels(), 'models', 'models');
                            $formDefaults = [
                                'factory' => $moduleRecord->itsHeader['factory'],
                                'short_desc' => $moduleRecord->itsHeader['short_desc'],
                                'ifactor' => $moduleRecord->itsHeader['ifactor'],
                                'models' => $models,
                            ];
                            break;
                        case 'CRAB':
                            $formDefaults = [
                                'ref_type' => 'CRAB',
                                'ref_item' => $moduleRecord->getID(),
                                'factory' => $moduleRecord->getFactoryID(),
                                'ifactor' => 1,
                                'description' => $moduleRecord->getDescription(),
                            ];
                            $formDefaults['pn'][] = $moduleRecord->getPartNumber();
                            break;
                        case 'NCR':
                            $models = [];
                            if (null !== $moduleRecord['mainFile']) {
                                $filename = $moduleRecord['mainFile']['filePath'];
                                $eapFilename = substr($filename, 31);

                                if (!copy("$UPLOADS_PATH/$filename", "$UPLOADS_PATH/eap/$eapFilename")) {
                                    $DEFAULT_ERROR[] = "WARNING: a problem occurred while copying the file";
                                }
                            }
                            foreach ($moduleRecord['products'] AS $key => $model) {
                                $models[] = $model['name'];
                            }
                            $formDefaults = [
                                'ref_type' => 'NCR',
                                'ref_item' => $moduleRecord['id'],
                                'category' => 'Engineering Change',
                                'factory' => $moduleRecord['factory']['legacyId'],
                                'short_desc' => $moduleRecord['shortDescription'],
                                'models' => $models,
                                'ifactor' => 1,
                                'description' => $moduleRecord['problem'],
                                'filename' => $eapFilename,
                            ];
                            foreach ($moduleRecord['parts'] as $part) {
                                $formDefaults['pn'][] = $part['partNumber'];
                            }
                            break;
                        case 'FAQ':
                            $partNumbers = array_map(
                                static fn(array $part) => $part['number'],
                                $moduleRecord['partNumbers'] ?? []
                            );

                            $formDefaults = [
                                'ref_type'    => 'FAQ',
                                'ref_item'    => $moduleRecord['id'],
                                'category'    => 'FAQP',
                                'factory'     => $moduleRecord['location']['legacyId'],
                                'description' => sprintf(
                                    "Part Numbers:<br>%s",
                                    implode('<br>', $partNumbers)
                                ),
                            ];
                            foreach ($partNumbers as $pn) {
                                $formDefaults['pn'][] = $pn;
                            }
                            break;
                    }
                } while (0);

                // Listing ------->

                $factoryList = tldLocation::getFactoryList('smartyOptionsIDLocation');
                $peopleList = tldDirectory::getUserlist('smartyOptions');
                $modelRawList = tldCatalogue::getTypeModelList();
                $modelList = [];
                foreach ($modelRawList as $var) {
                    $modelList[$var['model']] = $var['type'] . '->' . $var['model'];
                }
                $moduleLinks = tldUtils::getModLinks();
                $moduleList = array_combine(array_keys($moduleLinks), array_keys($moduleLinks));
                $assigneeList = tldGroup::getUserListByMultipleGroup(
                    ['gg_ENG', 'role_MLM', 'role_planner', 'role_PSM', 'role_PSE', 'role_PSA'],
                    null,
                    ['smartyOptions' => true]
                );
                $categoryList = tldList::optionsByListNameAsListItemListItem('list.eap.category');
                $phaseList = ['Phase 1' => 'Phase 1', 'Phase 2' => 'Phase 2', 'Phase 3' => 'Phase 3', 'Phase 4' => 'Phase 4'];

                // Form ------->

                $form = new HTML_QuickForm('frmNewEAP', 'post');
                $form->addElement('hidden', 'm[0]', 'eap');
                $form->addElement('hidden', 'm[1]', 'forms');
                $form->addElement('hidden', 'm[2]', 'newEAP');
                $form->addElement('hidden', 'from[mod]', $from['mod']);
                $form->addElement('hidden', 'from[id]', $from['id']);
                $form->addElement('hidden', 'from[cat]', $from['cat']);
                $form->addElement('header', 'title', 'Submit New EAP');
                $form->addElement('select', 'reporter', 'Reported By', ['' => ''] + $peopleList);
                $form->addElement('select', 'factory', 'Business Unit', ['' => ''] + $factoryList);
                $addActionPlan = false;
                switch ($from['cat']) {
                    case 'risk':
                        $form->addElement('static', null, 'Category', 'Risk Assessment');
                        $form->addElement('hidden', 'category', 'Risk Assessment');
                        $form->addElement('select', 'info', 'Critical Phase Change Closure', ['' => ''] + $phaseList);
                        $form->addElement('textarea', 'action_plan', 'Action Plan', ['wrap' => 'VIRTUAL', 'cols' => '60', 'rows' => '14']);
                        break;
                    case 'goal':
                        $form->addElement('static', null, 'Category', 'Project Goal Change');
                        $form->addElement('hidden', 'category', 'Project Goal Change');
                        $form->addElement('textarea', 'action_plan', 'Action Plan', ['wrap' => 'VIRTUAL', 'cols' => '60', 'rows' => '14']);
                        break;
                    default:
                        $form->addElement('select', 'category', 'Category', ['' => ''] + $categoryList);
                        $addActionPlan = true;
                        break;
                }
                $form->addElement('select', 'ifactor', 'Importance Factor', ['' => '', '1' => 'Supported Improvement [1]', '10+' => 'Sub MEAP [10+]']);
                $form->addElement('select', 'assignee', 'Overnight People (only for overnight processing)', ['' => ''] + $assigneeList);
                $form->addElement('select','discipline', 'Discipline', ['' => ''] + tldEAP::getDisciplineList());
                $ams =& $form->addElement('advmultiselect', 'models', null,
                    ['OTHER' => 'Other / Discontinued'] + $modelList,
                    ['size' => 15, 'class' => 'pool', 'style' => 'width:380px;']
                );
                $ams->setLabel(['Models Affected', 'Type->Model', 'Affected']);
                $ams->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
                $ams->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
                $form->addElement('static', null, null, '(if other or discontinued model selected above, please use description section below to describe the unit)');
                for ($i = 0; $i < 10; $i++) {
                    $k = $i + 1;
                    $form->addElement('text', "pn[$i]", "Part Number $k", ['size' => '10']);
                }
                $form->addElement('text', 'short_desc', 'Short Description (English only)', ['size' => '50']);
                $form->addElement('textarea', 'description', 'Full Description', ['wrap' => 'VIRTUAL', 'cols' => '60', 'rows' => '14']);
                if ($addActionPlan) {
                    $form->addElement('textarea', 'action_plan', 'Action Plan', ['wrap' => 'VIRTUAL', 'cols' => '60', 'rows' => '14']);
                }
                
                if ($from['mod'] === 'NCR') {
                    $form->addElement('text', 'filename', 'File', ['id' => 'filename', 'disabled' => 'disabled']);
                    $form->addElement('select', 'newfile', 'Remove NCR picture and upload a new one?',
                        ['N' => 'No', 'Y' => 'Yes',],
                        ['onchange' => "javascript: if(this.value == 'Y'){ $('#filename1').removeAttr('hidden'); $('#filename').attr('hidden','hidden');}else{ $('#filename1').attr('hidden','hidden'); $('#filename').removeAttr('hidden');}"]);
                    $form->addElement('file', 'filename1', 'Attachment', ['id' => 'filename1', 'hidden' => 'hidden']);
                } else {
                    $form->addElement('file', 'filename1', 'Attachment');
                }
                $form->addElement('header', 'title', 'Link another module (OPTIONAL)');
                $form->addElement('select', 'ref_type', 'Ref Type', ['' => ''] + $moduleList);
                $form->addElement('text', 'ref_item', 'Ref#');

                $form->addElement('header', 'title', 'Engineering hours (OPTIONAL)');
                $form->addElement('text', 'expected_hours', 'Expected number of hours');

                $form->addElement('header', 'title', 'Add members (OPTIONAL)');
                $members =& $form->addElement('advmultiselect', 'members', null,
                    $peopleList,
                    ['size' => 10, 'class' => 'pool', 'style' => 'width:200px;']
                );
                $members->setLabel(['Members', 'Addressbook', 'CC']);
                $members->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
                $members->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
                if ((empty($from['mod']) || !\in_array($from['mod'], ['EAP', 'MEAP'], true))) {
                    $form->addElement('header', 'title', 'Link to a parent module (OPTIONAL)');
                    $form->addElement('select', 'parent_type', 'Parent Type', ['' => ''] + ['MEAP' => 'MEAP', 'EAP' => 'EAP']);
                    $form->addElement('text', 'parent_ref', 'Parent#');
                }

                // Required
                $requiredFields = ['reporter', 'factory', 'ifactor', 'short_desc', 'description', 'category', 'action_plan', 'info', 'pn[0]'];
                foreach ($requiredFields as $field) {
                    $form->addRule($field, 'This is required', 'required');
                }
                // Default
                $defaults = array_merge(
                    [
                        'factory' => $user->getBUID(),
                        'reporter' => $user->getID(),
                        'description' => $defaultDescription,
                    ],
                    $formDefaults
                );
                $form->setDefaults($defaults);
                $form->addElement('submit', 'btnSubmit', 'Submit');

                if (!$form->validate()) {
                    $body = $form->isSubmitted() ? $form->toHTML() : mb_convert_encoding($form->toHTML(), 'HTML-ENTITIES', 'UTF-8');
                    break;
                }

                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $vars['pn'] = tldUtils::cleanupFormInput($vars['pn']);
                // Check link ref
                if ($vars['ref_type'] != '' && !($vars['ref_item'] > 0)) {
                    $DEFAULT_ERROR[] = 'ERROR: Must provide a Ref# if selecting a Ref Type';
                    $body = $form->toHTML();
                    break;
                }
                $vars['poster'] = $user->getID();
                // Process file if any
                if ($vars['newfile'] === 'N' && !empty($vars['newfile'])) {
                    $file = $form->getElement('filename');
                    $vars['filename_info'] = $file->getValue();
                } else {
                    $file = $form->getElement('filename1');
                    $vars['filename_info'] = $file->getValue();
                }

                // Create EAP
                $e = tldEAP::insert($vars);
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "INTERNAL ERROR: Can not create EAP. Reason: $e";
                    break;
                }

                if($from['mod'] === 'CRAB'){
                    global $kernel;
                    $client = $kernel->getContainer()->get(\ApiBundle\Client::class);
                    try {
                        $crab = $client->findOneBy('quality/crabs', ['legacyId' => $from['id']]);
                        $response = $client->put(sprintf('quality/crabs/%s', $crab['id']), [
                            'json' => [
                                'eapId' => $e,
                            ]
                        ]);
                    } catch (\Symfony\Component\HttpClient\Exception\ClientException $e) {
                        $response = ['hydra:member' => []];
                    }
                }

                if ($from['mod'] === 'FAQ') {
                    global $kernel;
                    $client = $kernel->getContainer()->get(\ApiBundle\Client::class);
                    try {
                        $client->put(sprintf('quality/first_article_qualifications/%s', $moduleRecord['id']), [
                            'json' => [
                                'eapId' => $e,
                            ],
                        ]);
                    } catch (\Symfony\Component\HttpClient\Exception\ClientException $e) {
                        // silently ignored, same behavior as CRAB
                    }
                }


                $numCC = count($vars['members'] ?? []);
                if ($numCC > 15) {
                    $DEFAULT_ERROR[] = 'ERROR: Cannot cc to more than 15 persons!';
                    $body = $form->toHTML();
                    break;
                }
                $parentModuleName = $from['mod'];
                if (!empty($vars['parent_type']) && !empty($vars['parent_ref']) && (empty($from['mod']) || !\in_array($from['mod'], ['EAP', 'MEAP'], true))) {
                    $refClass = new ReflectionClass('tld' . $vars['parent_type']);
                    $moduleRecord = $refClass->newInstance((int)$vars['parent_ref']);
                    if ($moduleRecord->isEmpty()) {
                        $DEFAULT_ERROR[] = "WARNING: {$vars['parent_ref']}#{$vars['parent_ref']} not found. Parent was not assigned";
                        $moduleRecord = null;
                        break;
                    }
                    $parentModuleName = $vars['parent_type'];
                }

                // Post action if any --->

                $eap = new tldEAP($e);
                    switch ($parentModuleName) {
                        case 'MEAP':
                        case 'EAP':
                            // Hierarchy
                            $moduleRecord->addChild($e, 'EAP');
                            break;
                    }


                // Record action plan if any
                if ($from['cat']) {
                    $eap->addComment([
                        'poster' => $user->getID(),
                        'comment' => $vars['action_plan'],
                        'log_num' => 1,
                    ]);
                }
                $short_desc = $eap->getShortDesc();
                $links = <<<LINKS
<a href="https://www.tld-gse.com/en/private/manufacturing/eng/dev.php?m[0]=eap&m[1]=view&id=$e">Click here to see EAP #$e</a>
%s
LINKS;
                $msg = <<<'EMAIL'
A new PENDING EAP has been submitted.<br><br>
%s
<hr>
%s
EMAIL;
                $ccList = [];
                if ($numCC > 0) {
                    $members = tldUtils::cleanupFormInput($vars['members']);
                    // Add members
                    foreach ($members as $uid) {
                        // Check this member is allowed to be added
                        if (!array_key_exists($uid, $peopleList)) {
                            $DEFAULT_ERROR[] = "ERROR: You are not allowed to {$peopleList[$uid]} as a member";
                            continue;
                        }
                        $error = tldModMember::insert('EAP', $e, $uid);
                        if (is_string($error)) {
                            $DEFAULT_ERROR[] = "ERROR: could not add {$peopleList[$uid]} as a member";
                            continue;
                        }
                        $DEFAULT_ERROR[] = "Successfully added {$peopleList[$uid]} as a member";
                    }
                }
                $ccList = array_column($eap->getFollowers(), 'email');

                // If overnight notify
                if ($vars['assignee']) {
                    $assignee = new tldUser($vars['assignee']);
                    tldUtils::emailAttachment($assignee->getEmail(),'noreply@tld-gse.com', "OVERNIGHT EAP#$e opened", sprintf($msg, '', ''), null, $ccList);
                }
                // link module ref if any
                if ($vars['ref_type'] <> '' && $vars['ref_item']) {
                    $err = tldModLink::insert('EAP', $e, $vars['ref_type'], $vars['ref_item']);
                    if (is_string($err)) {
                        $DEFAULT_ERROR[] = "ERROR: There was an error adding the new link. Reason: $err";
                    }
                }

                // confirmation message
                $linkModuleRecord = $moduleLinks[$from['mod']] . $from['id'];
                $links = sprintf($links, ($moduleRecord) ? "or <a href=\"$linkModuleRecord\">Return to {$from['mod']}#{$from['id']}</a>" : '');
                $body .= sprintf($msg, $links, $short_desc);
                break;
        }
        break;
    case 'view':
        include_once('eap/view.inc.php');
        break;
    case 'mlList':
        $xItems = [
            'id' => 'EAP#',
            'parent_id' => 'Parent#',
            'location' => 'Factory',
            'status' => 'Status',
            'ifactor' => 'iFactor',
            'overnight' => 'OVERNIGHT',
            'nb_tasks' => 'Num tasks',
            'nb_bptasks' => 'Num BP tasks',
            'dt_opened' => 'Date',
            'total_est_hours' => 'Total Estimated Time (Hours)',
            'models' => 'Model',
            'category' => 'Category',
            'short_desc' => 'Short Description',
            'reporter_fullname' => 'Reported By',
        ];
        switch ($m[2]) {
            case 'byPN':
                $DEFAULT_TITLE .= "\EAP by Part Number";
                $form = new HTML_QuickForm('frm', 'get', '', '', '', true);
                $form->addElement('header', 'title', 'Search EAPs by Part Number');
                $form->addElement('hidden', 'm[0]', 'eap');
                $form->addElement('hidden', 'm[1]', 'mlList');
                $form->addElement('hidden', 'm[2]', 'byPN');
                $form->addElement('text', 'pn', 'Look for part number',
                    ['size' => '20']
                );
                $form->addElement('submit', 'btnSubmit', 'Submit');
                if ($form->validate()) {
                    # If the form validates then freeze the data
                    $form->freeze();
                    $rows = tldEAP::byPartNumber(trim($pn ?? null));
                    $_title = "Search results for $pn";
                } else {
                    $body = $form->toHTML();
                }
                break;
            case 'byPoster':
                $DEFAULT_TITLE .= "\EAP by Poster";
                $userList = tldDirectory::getUserlist('smartyOptions');
                $form = new HTML_QuickForm('frm', 'get', '', '', '', true);
                $form->addElement('header', 'title', 'EAPs by Poster');
                $form->addElement('hidden', 'm[0]', 'eap');
                $form->addElement('hidden', 'm[1]', 'mlList');
                $form->addElement('hidden', 'm[2]', 'byPoster');
                $form->addElement('select', 'poster_id', 'Poster', $userList);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->addRule('poster_id', 'required', 'required');
                $form->setDefaults(['poster_id' => $user->getID()]);

                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break;
                }

                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $poster = new tldUser($vars['poster_id']);
                if (empty($poster->itsDetails)) {
                    $DEFAULT_ERROR[] = "Poster#{$vars['poster_id']} not found in system";
                    break;
                }
                $rows = tldEAP::byPosterID($poster->getID());
                $_title = 'EAPs for poster ' . $poster->getFullname();
                break;
            case 'notifiedByBUDate':
                $DEFAULT_TITLE .= "\Notified By Factory, Date";
                $erp_gg_ENG = $user->isInGroup('gg_ENG');
                if (!is_array($erp_gg_ENG)) {
                    $idLocation = tldLocation::getIDByERP($erp_gg_ENG);
                    $factory = new tldLocation($idLocation);
                }
                $form = new HTML_QuickForm('frm', 'get', '', '', '', true);
                $form->addElement('header', 'title', 'Find EAPs Notified By Factory, Date');
                $form->addElement('hidden', 'm[0]', 'eap');
                $form->addElement('hidden', 'm[1]', 'mlList');
                $form->addElement('hidden', 'm[2]', 'notifiedByBUDate');
                $form->addElement('select', 'location', 'Factory', ['' => ''] + tldLocation::getFactoryList('smartyOptionsLocationLocation'));
                $form->addElement('date', 'from', 'From', ['format' => 'Y-m-d', 'minYear' => date('Y') - 3, 'maxYear' => date('Y') + 3, 'addEmptyOption' => true]);
                $form->addElement('date', 'to', 'To', ['format' => 'Y-m-d', 'minYear' => date('Y') - 3, 'maxYear' => date('Y') + 3, 'addEmptyOption' => true]);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->setDefaults([
                    'from' => date('Y-01-01'),
                    'to' => (new \DateTime('first day of january last year'))->format('Y-m-d'),
                ]);
                if (null !== ($factory ?? null)) {
                    $form->setDefaults(['location' => $factory->getShortName()]);
                }
                $form->addRule('location', 'Required', 'required');
                $form->addRule('from', 'Required', 'required');
                $form->addRule('to', 'Required', 'required');

                if ($form->validate()) {
                    $vars = tldUtils::cleanupFormInput($form->exportValues());
                    if (!checkdate($vars['from']['m'], $vars['from']['d'], $vars['from']['Y']) || !checkdate($vars['to']['m'], $vars['to']['d'], $vars['to']['Y'])) {
                        $DEFAULT_ERROR[] = 'ERROR: Please make sure the dates are valid';
                        $body = $form->toHTML();
                        break;
                    }
                    $from = implode('-', $vars['from']);
                    $to = implode('-', $vars['to']);
                    $WHERE = "locations.location='{$vars['location']}' AND cal_bp.dt_opened BETWEEN '$from' AND '$to' AND cal_bp.short_desc REGEXP '^EAP#\\\\d+\\\\: Notification'";
                    $rows = tldEAP::byConstraints($WHERE, ['join' => "INNER JOIN cal_bp ON cal_bp.parent_id=eap.id AND cal_bp.module='EAP'", 'select' => ', cal_bp.dt_opened AS dt_notification, cal_bp.long_desc']);
                    $xItems['dt_notification'] = 'Notification date';
                    $xItems['long_desc'] = 'Notification description';
                } else {
                    $body = $form->toHTML();
                }
                break;
            case 'closedByBUDate':
                $DEFAULT_TITLE .= "\Closed By Factory, Date";
                $erp_gg_ENG = $user->isInGroup('gg_ENG');
                if (!is_array($erp_gg_ENG)) {
                    $idLocation = tldLocation::getIDByERP($erp_gg_ENG);
                    $factory = new tldLocation($idLocation);
                }
                $form = new HTML_QuickForm('frm', 'get', '', '', '', true);
                $form->addElement('header', 'title', 'Find EAPs Closed By Factory, Date');
                $form->addElement('hidden', 'm[0]', 'eap');
                $form->addElement('hidden', 'm[1]', 'mlList');
                $form->addElement('hidden', 'm[2]', 'closedByBUDate');
                $form->addElement('select', 'location', 'Factory', ['' => ''] + tldLocation::getFactoryList('smartyOptionsLocationLocation'));
                $form->addElement('date', 'from', 'From', ['format' => 'Y-m-d', 'minYear' => date('Y') - 3, 'maxYear' => date('Y') + 3, 'addEmptyOption' => true]);
                $form->addElement('date', 'to', 'To', ['format' => 'Y-m-d', 'minYear' => date('Y') - 3, 'maxYear' => date('Y') + 3, 'addEmptyOption' => true]);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->setDefaults([
                    'from' => date('Y-01-01'),
                    'to' => (new \DateTime('first day of january last year'))->format('Y-m-d'),
                ]);
                if (null !== $factory) {
                    $form->setDefaults(['location' => $factory->getShortName()]);
                }
                $form->addRule('location', 'Required', 'required');
                $form->addRule('from', 'Required', 'required');
                $form->addRule('to', 'Required', 'required');

                if ($form->validate()) {
                    $vars = tldUtils::cleanupFormInput($form->exportValues());
                    if (!checkdate($vars['from']['m'], $vars['from']['d'], $vars['from']['Y']) || !checkdate($vars['to']['m'], $vars['to']['d'], $vars['to']['Y'])) {
                        $DEFAULT_ERROR[] = 'ERROR: Please make sure the dates are valid';
                        $body = $form->toHTML();
                        break;
                    }
                    $from = implode('-', $vars['from']);
                    $to = implode('-', $vars['to']);
                    $WHERE = "locations.location='{$vars['location']}' AND eap.status='CLOSED' AND eap.dt_closed BETWEEN '$from' AND '$to'";
                    $rows = tldEAP::byConstraints($WHERE);

                    $xItems['poster_fullname'] = 'Posted By';
                    $xItems['assignee_fullname'] = 'Engineer';
                    $xItems['last_log_entry'] = 'Last Log Entry';
                    $xItems['dt_closed'] = 'Date Closed';
                    foreach ($rows as &$row) {
                        $query = <<<EOF
    			SELECT CONCAT(p.lastname,', ',p.firstname,' ',log.date,':\n',log.comment) AS last_log_entry
    			FROM mod_logs log LEFT JOIN people p ON p.id=log.poster
    			WHERE log.module='EAP' AND log.parent_id={$row['id']}
    			ORDER BY date DESC
    			LIMIT 1
EOF;
                        $r = tldUtils::getSqlRowToAssocArray($query);
                        $row['last_log_entry'] = $r['last_log_entry'];
                        if (!$row['poster_fullname']) {
                            $row['poster_fullname'] = $row['reported_by'];
                        }
                    }
                } else {
                    $body = $form->toHTML();
                }

                break;
            case 'search':
                $DEFAULT_TITLE .= "\EAP Search";
                // Get lists

                $FACTORIES = tldLocation::getFactoryList('smartyOptionsLocationLocation');
                $STATUS = ['PENDING' => 'PENDING', 'IN QUEUE' => 'IN QUEUE', 'IN PROGRESS' => 'IN PROGRESS',
                    'PROPOSED' => 'PROPOSED', 'NOTIFICATION' => 'NOTIFICATION',
                    'REJECTED' => 'REJECTED', 'CLOSED' => 'CLOSED',
                ];
                // Get erp# from gg_ENG role of the user
                $erp_gg_ENG = $user->isInGroup('gg_ENG');
                if (!is_array($erp_gg_ENG)) {
                    $idLocation = tldLocation::getIDByERP($erp_gg_ENG);
                    $factory = new tldLocation($idLocation);
                }
                // Get form
                $form = new HTML_QuickForm('frm', 'get', '', '', '', true);
                $form->addElement('header', 'title', 'Search EAPs');
                $form->addElement('hidden', 'm[0]', 'eap');
                $form->addElement('hidden', 'm[1]', 'mlList');
                $form->addElement('hidden', 'm[2]', 'search');
                $form->addElement('text', 'target', 'Search for...');
                $form->addElement('select', 'status', 'Status', ['' => ''] + $STATUS);
                $form->addElement('select', 'location', 'Factory', ['' => ''] + $FACTORIES);
                if ($factory) {
                    $form->setDefaults(['location' => $factory->getShortName()]);
                }
                $models_list = tldCatalogue::getTypeModelList();
                foreach ($models_list as $item) {
                    $list[$item['model']] = $item['type'] . '->' . $item['model'];
                }
                $ams =& $form->addElement('advmultiselect', 'models', null,
                    ['OTHER' => 'Other / Discontinued'] + $list,
                    ['size' => 15,
                        'class' => 'pool',
                        'style' => 'width:380px;',
                    ]
                );
                $ams->setLabel(['Models', 'Type->Model', 'Selected']);
                $ams->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand',
                ]);
                $ams->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
                $form->addElement('submit', 'btnSubmit', 'Submit');

                if ($form->validate()) {
                    $vars = tldUtils::cleanupFormInput($form->exportValues());
                    if (empty($vars['target']) && empty($vars['status']) && empty($vars['location']) && empty($vars['models'])) {
                        $DEFAULT_ERROR[] = 'ERROR: Require at least one field to search by';
                        $body = $form->toHTML();
                        break;
                    }
                    $rows = tldEAP::search($vars);
                    if (is_string($rows)) {
                        $DEFAULT_ERROR[] = "INTERNAL ERROR: $rows";
                        $body = $form->toHTML();
                        break;
                    }
                    $xItems['models'] = 'Affected Models';
                    $_title = 'Search results';
                } else {
                    $body = $form->toHTML();
                }
                break;
            case 'byPartByStatus':
                $rows = tldEAP::byPartByStatus($pn, $status);
                $_title = "EAPs with PN#.$pn";
                break;
            case 'byNoTasks':
                $rows = tldEAP::byQuery('byNoTasks');
                $_title = 'EAPs with no tasks';
                break;
            case 'byNoParent':
                $rows = tldEAP::byQuery('byNoParent');
                $_title = 'EAPs with no parent';
                break;
            case 'last20':
                $rows = tldEAP::getLatest(20);
                $_title = 'Last 20 EAPs';
                break;
            case 'byFactoryStatus':
                $erp = TldDatabase::escape($y);
                $status = TldDatabase::escape($x);
                $rows = tldEAP::byERPStatus($erp, $status);
                $_title = "EAPs by Factory $erp, Status $status";
                if ($z === 'opened') {
                    $rows = array_filter($rows, static function ($eap) {
                        return !in_array($eap['status'], ['REJECTED', 'CLOSED']);
                    });
                }
                $xItems = [
                    'id' => 'EAP#',
                    'parent_id' => 'Parent#',
                    'location' => 'Factory',
                    'status' => 'Status',
                    'ifactor' => 'iFactor',
                    'overnight' => 'OVERNIGHT',
                    'nb_tasks' => 'Num tasks',
                    'nb_bptasks' => 'Num BP tasks',
                    'latest_bp_duedate' => 'Latest BP due Date',
                    'dt_opened' => 'Date',
                    'total_est_hours' => 'Total Estimated Time (Hours)',
                    'models' => 'Model',
                    'category' => 'Category',
                    'short_desc' => 'Short Description',
                    'reporter_fullname' => 'Reported By',
                ];
                break;
            case 'byFactoryOpenStatusWithNoOpenTasks':
                $factory = TldDatabase::escape($y);
                $status = TldDatabase::escape($x);
                $rows = tldEAP::byFactoryOpenStatusWithNoOpenTasks($factory, $status);
                $_title = "Open EAP listing factory '$factory', status '$status' with no Open tasks";
                $xItems = [
                    'id' => 'EAP#',
                    'parent_id' => 'Parent#',
                    'location' => 'Factory',
                    'status' => 'Status',
                    'ifactor' => 'iFactor',
                    'overnight' => 'OVERNIGHT',
                    'discipline' => 'Discipline',
                    'nb_tasks' => 'Num tasks',
                    'nb_bptasks' => 'Num BP tasks',
                    'latest_bp_duedate' => 'Latest BP due Date',
                    'dt_opened' => 'Date',
                    'total_est_hours' => 'Total Estimated Time (Hours)',
                    'models' => 'Model',
                    'category' => 'Category',
                    'short_desc' => 'Short Description',
                    'reporter_fullname' => 'Reported By',
                ];
                break;
            case 'byModelStatus':
                $model = TldDatabase::escape($y);
                $status = TldDatabase::escape($x);
                $rows = tldEAP::byModelStatus($model, $status);
                $_title = "EAPs by Model $model, Status $status";
                break;
            case 'EAPLinkNCR':
                $_title = "EAP linked to NCR";
                $form = new HTML_QuickForm('frmEAPLinkNCR', 'GET');
                $form->addElement('header', 'title', 'Filter');
                $form->addElement('hidden', 'm[0]', 'eap');
                $form->addElement('hidden', 'm[1]', 'mlList');
                $form->addElement('hidden', 'm[2]', 'EAPLinkNCR');
                $form->addElement('select', 'factory', 'Factory', ['ALL' => 'ALL'] + tldLocation::getFactoryList('smartyOptionsIDLocation'));
                $modelRawList = tldCatalogue::getTypeModelList();
                $modelList = [];
                foreach($modelRawList as $var){
                    $modelList[$var['model']]=$var['type']."->".$var['model'];
                }
                $ams =& $form->addElement(
                    'advmultiselect', 'model', null,
                    $modelList,
                    [
                        'size' => 10,
                        'class' => 'pool',
                        'style' => 'width:300px;'
                    ]
                );
                $ams->setLabel(['Model']);
                $form->addElement('text', 'from', 'From', ['class' => 'datepicker']);
                $form->addElement('text', 'to', 'To', ['class' => 'datepicker']);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->addRule('factory', 'Required', 'required');

                if (!$form->validate()) {
                    $body .= $form->toHTML();
                    break;
                }

                $vars = $form->exportValues();

                $a = "eap.status NOT IN ('CLOSED', 'REJECTED')";
                if ($vars['factory'] !== 'ALL') {
                    $a .= " AND eap.factory = {$vars['factory']}";
                }
                if ($vars['model']) {
                    $model = implode("','",$vars['model']);
                    $a .= " AND mod_models.model IN ('$model')";
                }
                if ($vars['from']) {
                    $a .= " AND eap.dt_opened >= '{$vars['from']}'";
                }
                if ($vars['to']) {
                    $a .= " AND eap.dt_opened <= '{$vars['to']}'";
                }
                $rows = tldEAP::getEAPLinkNCR($a);
                $xItems = [
                    'ncr_id' => 'NCR#',
                    'id' => 'EAP#',
                    'ifactor' => 'iFactor',
                    'status' => 'EAP Status',
                    'short_desc' => 'EAP Description',
                    'mod_model' => 'Affected Model',
                ];
                $reportOptions = [
                    'xItems' => $xItems,
                    'title' => $_title,
                    'links' => [
                        'id' => "$php_self?m[0]=eap&m[1]=view&id=",
                    ],
                ];
                if (!$csv) {
                    foreach ($rows as $key => $row) {
                        $ncrId = explode(',', $row['ncr_id']);
                        $link = [];
                        foreach ($ncrId as $ncr) {
                            $link[] = "<a href='/en/private/manufacturing/qa/dev.php?m[0]=ncr&m[1]=view&id=$ncr'>$ncr</a>";
                        }
                        $rows[$key]['ncr_id'] = implode(', ', $link);
                    }
                }
                break;
        }
        if (count($rows ?? []) ) {
            $sess['eap']['list'] = $rows;
            if ($csv) {
                $template = 'NO_TEMPLATE';
                $xItems['models'] = 'Affected Models';
                $report = new tldCSV(
                    $rows,
                    [
                        'xItems' => $xItems,
                        'showTitles' => true,
                    ]
                );
                $report->out();
            } else {
                $DEFAULT_TITLE .= "\\$_title";
                $DEFAULT_MENU .= <<<EOF
				<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<a href="{$_SERVER['REQUEST_URI']}&csv=1">CSV Version</a>
EOF;

                foreach ($rows as &$row) {
                    $row['parent_module'] = strtolower($row['parent_module']);
                    if ($row['parent_id'] === '0') {
                        $row['parent_id'] = null;
                    }
                }
                $report = new tldReportColumnar(
                    $rows,
                    $reportOptions ?? [
                        'xItems' => $xItems,
                        'title' => $title,
                        'links' => [
                            'id' => "$php_self?m[0]=eap&m[1]=view&id=",
                            'parent_id' => [
                                'url' => "$php_self?",
                                'params' => [
                                    'm[0]' => 'parent_module',
                                    'm[1]=view&id' => 'parent_id',
                                ],
                            ],
                        ],
                        'showzero' => true,
                    ]
                );
                $body = $report->fetch();
                $smarty->assign('width', '100%');
            }
        } elseif (empty($body)) {
            $DEFAULT_ERROR[] = 'ERROR: No rows matched';
        }
        break;
    case 'matrix':
        switch ($m[2]) {
            case 'countByFactoryStatus':
                $form = new tldMatrix(
                    tldEAP::countByFactoryStatus(),
                    'status', 'location', 'num',
                    "$php_self?m[0]=eap&m[1]=mlList&m[2]=byFactoryStatus",
                    'EAP Count by Status, Factory',
                    [
                        'xItems' => [
                            'PENDING',
                            'IN QUEUE',
                            'IN PROGRESS',
                            'PROPOSED',
                            'NOTIFICATION',
                            'CLOSED',
                            'REJECTED',
                        ],
                    ]
                );
                $body = $form->fetch();
                break;
            case 'countByModelStatus':
                $form = new tldMatrix(
                    tldEAP::countByModelStatus(),
                    'status', 'model', 'num',
                    "$php_self?m[0]=eap&m[1]=mlList&m[2]=byModelStatus",
                    'EAP Count by Status, Model',
                    [
                        'xItems' => [
                            'PENDING',
                            'IN QUEUE',
                            'IN PROGRESS',
                            'PROPOSED',
                            'NOTIFICATION',
                            'CLOSED',
                            'REJECTED',
                        ],
                    ]
                );
                $body = $form->fetch();
                break;
        }
        break;
    case 'special':
        switch ($m[2]) {
            case 'BPTasksByFactoryStatus':
                $DEFAULT_TITLE .= "\Open BP Tasks";
                $form = new HTML_QuickForm('frm', 'get', '', '', '', true);
                $form->addElement('hidden', 'm[0]', 'eap');
                $form->addElement('hidden', 'm[1]', 'special');
                $form->addElement('hidden', 'm[2]', 'BPTasksByFactoryStatus');
                $form->addElement('header', 'title', 'View Open BP Tasks by ERP');
                $factories = ['' => ''] + tldLocation::getFactoryList('smartyOptionsLocationLocation');
                $form->addElement('select', 'factory', 'Business Unit', $factories);
                $form->addElement('select', 'status', 'Status', ['' => '',
                    'PENDING' => 'PENDING',
                    'IN QUEUE' => 'IN QUEUE',
                    'IN PROGRESS' => 'IN PROGRESS',
                    'PROPOSED' => 'PROPOSED',
                    'NOTIFICATION' => 'NOTIFICATION',
                ]);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->addRule('factory', 'This is required', 'required');
                $form->addRule('status', 'This is required', 'required');
                $form->setDefaults(['status' => 'NOTIFICATION']);
                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break;
                }
                $erp = TldDatabase::escape($factory);
                $status = TldDatabase::escape($status);
                $eaps = tldEAP::byERPStatus($erp, $status);
                foreach ($eaps AS $eap) {
                    $bps = tldBP::byParent($eap['id'], 'EAP');
                    foreach ($bps AS $bp) {
                        $gantts[$eap['id']][$bp['id']] = tldTask::getGanttOverview('BP', $bp['id'], "status<>'CLOSED'");
                    }
                }

                if (!count($gantts)) {
                    $DEFAULT_ERROR[] = 'Unable to find any open BPs or tasks';
                    break;
                }
                $overlib = $smarty->fetch('overlib.inc.js.tpl');
                $overlib .= '<script type="text/javascript">$(function(){$("[data-body]").overlib()});</script>';
                $smarty->assign('html_head', $overlib);
                $smarty->assign('width', '100%');

                $groups = [];
                foreach ($gantts AS $eap => $bps) {
                    $reports = [];
                    $bpCnt = count($bps);
                    if ($bpCnt) {
                        // Cycle through reports
                        foreach ($bps AS $bp => $gantt) {
                            $gCnt = count($gantt);
                            if ($gCnt) {
                                // Prepare gantt report
                                $ovd = 0;
                                foreach ($gantt AS &$task) {
                                    $task['late_stats'] = "Opened: {$task['date']} Due: {$task['due_date']}";
                                    if ($task['overdue']) {
                                        $ovd++;
                                        $task['late'] = 'OVERDUE';
                                        $task['late_stats'] .= " ({$task['days_late']} days late)";
                                    } elseif ($task['status'] !== 'CLOSED') {
                                        $task['late'] = 'ON TIME';
                                        $task['late_stats'] .= ' (on time)';
                                    } else {
                                        $task['late'] = 'N/A';
                                        $task['late_stats'] .= ' (closed)';
                                    }
                                    $task['last_ten_comments_html'] = str_replace("\n", '&lt;br/&gt;', htmlentities($task['last_ten_comments']));
                                    $task['task_add_comment'] = $task['id'];
                                    $task['task_reschedule'] = $task['id'];
                                    $task['task_transfer'] = $task['id'];
                                    $task['task_close'] = $task['id'];
                                }
                                $form = new tldGanttChart("EAP_BP_$eap", $gantt, [
                                    // xItems
                                    'id' => 'ID',
                                    'assignor_lastname' => 'Assignor',
                                    'ifactor' => 'iFactor',
                                    'status' => 'Status',
                                    'assignee_fullname' => 'Assignee',
                                    'task' => 'Task Description',
                                    'date' => 'Days Open',
                                    'last_ten_comments_html' => 'Log',
                                    'late' => 'Late',
                                    'task_add_comment' => 'Add Comment',
                                    'task_reschedule' => 'Reschedule',
                                    'task_transfer' => 'Transfer',
                                    'task_close' => 'Close',
                                ], [
                                    // Options
                                    'parentAttributes' => [
                                        'table' => 'border="0"',
                                    ],
                                    'sortable' => [],
                                    'links' => [
                                        'id' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=',
                                        'task_add_comment' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=comment&id=',
                                        'task_reschedule' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=reschedule&id=',
                                        'task_transfer' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=transfer&id=',
                                        'task_close' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=closeConfirm&id=',
                                    ],
                                    'groupAttributes' => [
                                        'late' => [
                                            'OVERDUE' => 'style="font-weight:bold;background:#900;color:#fff;"',
                                            'ON TIME' => 'style="font-weight:bold;background:#090;color:#fff;"',
                                        ],
                                    ],
                                    'groupHeaders' => [
                                        [
                                            'columns' => ['task_add_comment', 'task_reschedule', 'task_transfer', 'task_close'],
                                            'title' => 'Actions',
                                        ],
                                    ],
                                    'columnSettings' => [
                                        'id' => [
                                            'callback' => 'intval',
                                            'title' => 'ID #%d',
                                        ],
                                        'ifactor' => [
                                            'type' => 'ifactor',
                                        ],
                                        'task' => [
                                            'type' => 'description',
                                        ],
                                        'last_ten_comments_html' => [
                                            'icon' => 'log',
                                            'useIconValue' => true,
                                            'cellAttributes' => 'data-body="%s"',
                                            'imgTitle' => 'last_ten_comments',
                                            'tdTitle' => 'last_ten_comments',
                                        ],
                                        'date' => [
                                            'type' => 'gantt',
                                            'zoom' => 'day',
                                            'due' => 'due_date',
                                        ],
                                        'late' => [
                                            'tdTitle' => 'late_stats',
                                        ],
                                        'task_add_comment' => [
                                            'callback' => 'intval',
                                            'icon' => 'mail',
                                            'useIconHeader' => true,
                                            'useIconValue' => true,
                                            'title' => 'Add New Comment Task#%d',
                                        ],
                                        'task_reschedule' => [
                                            'callback' => 'intval',
                                            'icon' => 'schedule',
                                            'useIconHeader' => true,
                                            'useIconValue' => true,
                                            'title' => 'Reschedule Task#%d',
                                        ],
                                        'task_transfer' => [
                                            'callback' => 'intval',
                                            'icon' => 'transfer',
                                            'useIconHeader' => true,
                                            'useIconValue' => true,
                                            'title' => 'Transfer Task#%d',
                                        ],
                                        'task_close' => [
                                            'callback' => 'intval',
                                            'icon' => 'close',
                                            'useIconHeader' => true,
                                            'useIconValue' => true,
                                            'title' => 'Close Task#%d',
                                        ],
                                    ],
                                ]);

                                $reports[] = <<<EOF
<h3 style="padding-left:100px;">BP#$bp</h3>
<p style="padding-left:100px;">
	<b>Open Tasks:</b> $gCnt &nbsp; <b>Overdue:</b> $ovd &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="/en/private/calendar/calendar.php?m[0]=bp&m[1]=view&id=$bp" title="View BP#$bp">View BP</a> &nbsp;|&nbsp;
	<a href="/en/private/calendar/calendar.php?m[0]=bp&m[1]=view&m[2]=close&id=$bp" title="Close BP#$bp">Close BP</a>
</p>
{$form->fetch()}
<br/>
EOF;
                            } else {
                                // No open tasks
                                $reports[] = <<<EOF
<h3 style="padding-left:100px;">BP#$bp</h3>
<p style="padding-left:100px;">
	There are no open tasks &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="/en/private/calendar/calendar.php?m[0]=bp&m[1]=view&id=$bp" title="View BP#$bp">View BP</a> &nbsp;|&nbsp;
	<a href="/en/private/calendar/calendar.php?m[0]=bp&m[1]=view&m[2]=close&id=$bp" title="Close BP#$bp">Close BP</a>
</p>
<br/>
EOF;
                            }
                        }
                        $display = implode("\n<p style=\"text-align:center;font-weight:bold;\">&hellip; &nbsp; &hellip; &nbsp; &hellip;</p>\n", $reports);
                        $groups[] = <<<EOF
<h3>EAP#$eap</h3>
<p>
	<b>Open BPs:</b> $bpCnt &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="$php_self?m[0]=eap&m[1]=view&id=$eap" title="View EAP#$eap">View EAP</a> &nbsp;|&nbsp;
	<a href="$php_self?m[0]=eap&m[1]=view&m[2]=selectStatus&id=$eap" title="Change Status EAP#$eap">Change EAP Status</a>
</p>
<br/>
$display
EOF;
                    } else {
                        // No open BPs
                        $groups[] = <<<EOF
<h3>EAP#$eap</h3>
<p>
	There are no open BPs &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="$php_self?m[0]=eap&m[1]=view&id=$eap" title="View EAP#$eap">View EAP</a> &nbsp;|&nbsp;
	<a href="$php_self?m[0]=eap&m[1]=view&m[2]=selectStatus&id=$eap" title="Change Status EAP#$eap">Change EAP Status</a>
	</p>
<br/>
EOF;
                    }
                }
                $body .= implode("\n<br/><hr/><br/>\n", $groups);;
                break;
        }
        break;
    case 'reports':
        $body = $smarty->fetch("$PATH/eap/reports/homepage.reports.tpl");
        break;
    case 'graphic':

        // Create form
        $form = new HTML_QuickForm('frmByNum', 'post');
        $form->addElement('hidden', 'm[0]', 'eap');
        $form->addElement('hidden', 'm[1]', 'graphic');
        $form->addElement('hidden', 'm[2]', $m[2]);

        switch ($m[2]) {
            case 'EAPInProgressByBU':
                $form->addElement('header', 'title', 'EAP In Progress Late By BU');
                $erps = tldUtils::optionsByKeyValue(tldLocation::getLocationList(), 'erp', 'location');
                $form->addElement('select', 'erp', 'Location', ['' => ''] + $erps);
                $form->addRule('erp', 'Required', 'required');
                $form->setDefaults(['erp' => $DEFAULT_ERP]);
                break;
        }

        $form->addElement('submit', 'btnSubmit', 'Submit');


        if (!$form->validate()) {
            $body .= $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());

        $erp = $vars['erp'];
        // Display KPI
        $BUID = tldLocation::getIDByERP($erp);
        $ret = tldUtils::getSqlRowToAssocArray(
            "SELECT period_diff(date_format(NOW(), '%Y%m'), date_format(dt_opened, '%Y%m')) as period
	FROM eap
	WHERE status =  'IN PROGRESS' AND factory = $BUID
	ORDER BY dt_opened ASC limit 1"
        );
        $sd = $ret['period'];

        if (empty($sd)) {
            $DEFAULT_ERROR[] = "No EAP In Progress in $erp";
            $body .= $form->toHTML();
            break;
        }

        $GraphURL = "/en/private/manufacturing/kpi/graphs.php?m[0]=&m[1]=eapQtyInProgressByBU&BUID=$BUID&sd=$sd&ed=$ed";

        $body .= _getKPIgraph(
            $GraphURL,
            'EAP In Progress Late Report',
            $help['EAP In Progress Late Report(Month)']
        );

        break;
    case 'pn':
        switch ($m[2]) {
            case 'remove':
                if(!$user->isInGroup(["ROLE_SEE", "ROLE_PLE", "ROLE_EM"])){
                    $DEFAULT_ERROR[] = 'ERROR: You do not have permissions to remove a PN from EAP';
                } else {
                    $eap = new tldEAP($eap);
                    $eap->removePart($pn);
                    header("Location: {$_SERVER['HTTP_REFERER']}");
                }
        }
    default:
//average days open
        $smarty->assign('ado', tldEAP::getADO());
        $body = $smarty->fetch("$PATH/eap/homepage.eap.tpl");

        $form = new tldReportColumnar(
            tldEAP::getStatsByFactory(),
            [
                'xItems' => [
                    'location' => 'BU',
                    'ado' => 'Average Days Open',
                    'ccm' => 'Number Closed This Month',
                    'ovrn_avg_hrs' => 'Avg Hours open for overnight EAPs This month',
                ],
                'title' => 'Stats by BU',
            ]
        );
        $body .= $form->fetch();

        $form = new tldMatrix(
            tldEAP::countByFactoryOpenStatusWithNoOpenTasks(),
            'status', 'factory_fullname', 'num',
            "$php_self?m[0]=eap&m[1]=mlList&m[2]=byFactoryOpenStatusWithNoOpenTasks",
            'Open EAP by Factory, Status, with no open tasks',
            [
                'xItems' => [
                    'PENDING', 'IN QUEUE', 'IN PROGRESS', 'PROPOSED', 'NOTIFICATION',
                ],
            ]
        );
        $body .= $form->fetch();
        $eaps = tldEAP::countByFactoryStatus();

        $statusesList = [
            'PENDING', 'IN QUEUE', 'IN PROGRESS', 'PROPOSED', 'NOTIFICATION', 'CLOSED', 'REJECTED',
        ];
        $form = new tldMatrix(
            array_filter($eaps, static function ($eap) {
                return !in_array($eap['status'], ['REJECTED', 'CLOSED']);
            }),
            'status', 'location', 'num',
            "$php_self?m[0]=eap&m[1]=mlList&m[2]=byFactoryStatus&z=opened",
            'EAP Count by Factory, Status not REJECTED nor CLOSED',
            [
                'xItems' => array_filter($statusesList, static function ($status) {
                    return !in_array($status, ['REJECTED', 'CLOSED']);
                }),
            ]
        );
        $body .= $form->fetch();

        $form = new tldMatrix(
            tldEAP::countByFactoryStatus(),
            'status', 'location', 'num',
            "$php_self?m[0]=eap&m[1]=mlList&m[2]=byFactoryStatus",
            'EAP Count by Status, Factory',
            [
                'xItems' => [
                    'PENDING', 'IN QUEUE', 'IN PROGRESS', 'PROPOSED', 'NOTIFICATION', 'CLOSED', 'REJECTED',
                ],
            ]
        );
        $body .= $form->fetch();
        $form = new tldReportColumnar(
            tldEAP::getLatest(),
            [
                'xItems' => [
                    'id' => 'EAP#',
                    'status' => 'Status',
                    'ifactor' => 'iFactor',
                    'overnight' => 'OVERNIGHT',
                    'dt_opened' => 'Date',
                    'nb_task' => 'BP tasks ?',
                    'short_desc' => 'Short Description',
                ],
                'title' => 'Latest EAPs',
                'links' => ['id' => "$php_self?m[0]=eap&m[1]=view&id="],
                'showzero' => true,
            ]
        );
        $body .= $form->fetch();
}


function _getEAPMenu($eap)
{
    global $smarty, $PATH;
    $id = $eap->getID();
    $DEFAULT_MENU .= <<<EOF
	<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="$php_self?m[0]=eap&m[1]=view&id=$id">General</a>
EOF;
    if ($eap->getStatus() !== 'PENDING') {
        $DEFAULT_MENU .= <<<EOF
	&nbsp;|&nbsp;<a href="$php_self?m[0]=eap&m[1]=view&m[2]=tasks&id=$id">Tasks</a>
	&nbsp;|&nbsp;<a href="$php_self?m[0]=eap&m[1]=view&m[2]=files&id=$id">Files</a>
	&nbsp;|&nbsp;<a href="$php_self?m[0]=eap&m[1]=view&m[2]=links&id=$id">Links</a>
	&nbsp;|&nbsp;<a href="$php_self?m[0]=eap&m[1]=view&m[2]=log&id=$id">Log</a>
EOF;
    }
    $DEFAULT_MENU .= <<<EOF
	&nbsp;|&nbsp;<a href="$php_self?m[0]=eap&m[1]=view&m[2]=selectStatus&id=$id">Change Status</a>
	&nbsp;|&nbsp;<a href="/en/private/manufacturing/eng/eap/eap_admin.php?mode=record_view&form_type=main_tpl&id=$id">Edit</a>
EOF;
    $DEFAULT_MENU .= $smarty->fetch("$PATH/eap/eap.menu.inc.tpl");
    return $DEFAULT_MENU;
}

function getPartsList($eap)
{
    $partsList = $eap->getPartsList();

    try {
        global $kernel;
        $client = $kernel->getContainer()->get(Client::class);
        $partNumbers = array_map(
            static function ($pn) {
                $pn = (string) $pn;

                // Ensure valid UTF-8: an invalid byte makes json_encode() return false
                // in the HTTP client caching layer, which then breaks hash().
                return mb_check_encoding($pn, 'UTF-8') ? $pn : mb_convert_encoding($pn, 'UTF-8', 'UTF-8');
            },
            array_column($partsList, 'pn') ?? []
        );
        $itemsDescription = $client->get(
            sprintf('/ion/engineering_item_descriptions'),
            ['query' => ['itemsList' => implode('|', $partNumbers)]]
        );
    } catch (ClientException $exception) {
        global $DEFAULT_ERROR;
        $DEFAULT_ERROR[] = 'INTERNAL ERROR: Could not find parts on ERP.';

        return [];
    }

    return $eap->setPartsListDescription($partsList, $itemsDescription['hydra:member']);
}

function _viewGeneralTab($eap)
{
    /** @var tldEAP $eap */
    $header = $eap->getHeader();
    global $smarty, $PATH;
    // header template
    $smarty->assign('eap', $header);
    // General view
    $cells = [];
    $form = new tldAssocTable(
        $header,
        [
            'status' => 'Status',
            'location' => 'Factory',
            'dt_opened' => 'Date Opened',
            'overnight' => 'Overnight Processing Required?',
            'reporter_fullname' => 'Reported By (from web)',
            'reported_by' => 'Reported By (from shop)',
            't_emno' => 'Employee Number',
            'poster_fullname' => 'Poster',
            'assignee_fullname' => 'Engineer',
            'short_desc' => 'Short Description',
            'description' => 'Description',
            'action_plan' => 'Action plan',
            'category' => 'Category',
            'ifactor' => 'iFactor',
            'currency' => 'Currency',
            'cost' => 'Cost',
            'total_est_hours' => 'Total Estimated Time (Hours)',
            'total_hours_actual' => 'Total Actual Time (Hours)',
            'expected_hours' => 'Expected Engineering Time (Hours)',
        ],
        [
            'title' => 'General',
        ]
    );
    $cells[0] = $form->fetch();
    $log = $eap->getLog();
    $report = new tldReportColumnar(
        array_slice($log, 0, 3),
        [
            'xItems' => [
                'id' => 'ID#',
                'date' => 'Date',
                'poster_fullname' => 'Poster',
                'comment' => 'Comment',
            ],
            [
                'title' => 'Last 3 comments',
            ],
        ]
    );
    $cells[0] .= '<h3>Log for EAP#' . $header['id'] . '</h3>';
    $cells[0] .= $report->fetch();
    // And cost conclusions
    $form = new tldAssocTable(
        $header,
        [
            'currency' => 'Currency',
            'cost' => 'Cost',
        ],
        [
            'title' => 'Conclusion',
        ]
    );
    $cells[0] .= $form->fetch();
    $form = new tldReportColumnar(
        $eap->getEAPSummary(),
        [
            'xItems' => [
                'id' => 'EAP#',
                'status' => 'Status',
                'location' => 'Location',
                'ifactor' => 'iFactor',
                'short_desc' => 'Description',
                'tasks_all' => 'No of Tasks',
                'tasks_open' => 'Tasks Open',
                'tasks_closed' => 'Tasks Closed',
                'tasks_late' => 'Tasks Late',
                'total_est_hours' => 'Total Estimated Time (Hours)',
                'total_open_est_hours' => 'Total Estimated Time on OPEN Tasks (Hours)',
                'bp_open' => 'BPs Open',
                'bp_closed' => 'BPs Closed',
            ],
            'links' => ['id' => "$php_self?m[0]=eap&m[1]=view&id="],
            'title' => 'EAP Task Summary',
        ]
    );
    $cells[0] .= $form->fetch();
    // Photo/File
    $cells[1] .= $smarty->fetch("$PATH/eap/eap.general.tpl");
    // PN & model info
    $pn = $eaplist = [];

    foreach (getPartsList($eap) AS $k => $v) {
        $eaplist[$k]['pn'] = $v['pn'];
        $eaplist[$k]['description'] = $v['description'];
        $eaplist[$k]['eapqty'] = tldEAP::countByPartByStatus($header['id'], $v['pn']);
        $eaplist[$k]['eapip'] = tldEAP::countByPartByStatus($header['id'], $v['pn'], ['IN PROGRESS', 'IN QUEUE ', 'PENDING']);
        $eaplist[$k]['pdcqty'] = tldPDC::countByPartByStatus($v['pn']);
        $pn[] = $v['pn'];
    }
    if ($date === null) {
        $date = date('Y-m-d');
    }
    $pn = implode("','", array_map([TldDatabase::class, 'escape'], $pn));
    $query = <<<SQL
SELECT COUNT(DISTINCT(question_id)) AS nb, pn.t_item
FROM
  pi_questions AS qst
  LEFT JOIN
  pi_questions_pn_xref AS pn ON question_id = qst.id
  LEFT JOIN
  pi_unit_family AS model ON model.family = qst.model
  LEFT JOIN
  service AS er ON er.sn = model.unit
WHERE pn.t_item in ('$pn')
GROUP BY pn.t_item;
SQL;
    $pio = tldUtils::getSqlToAssocArray($query);
    $pio_nb = [];
    foreach ($pio as $val) {
        $pio_nb[$val['t_item']] = $val['nb'];
    }
    foreach ($eaplist as $key => $val) {
        if (!empty($pio_nb[$val['pn']])) {
            $form = new HTML_QuickForm('frmHoursAmount', 'post', '/en/private/manufacturing/index.php');
            $form->addElement('hidden', 'm[0]', 'pi');
            $form->addElement('hidden', 'm[1]', 'reports');
            $form->addElement('hidden', 'm[2]', 'piByFilter');
            $form->addElement('submit', 'btnSubmit', $pio_nb[$val['pn']], 'style="background:none;border:none;padding:0;cursor:pointer;outline:none;" onmouseover=\'this.style.textDecoration="underline"\' onmouseout=\'this.style.textDecoration="none"\'');
            $form->addElement('hidden', 't_item', $val['pn']);
            $eaplist[$key]['pio'] = str_replace("\n", '', $form->toHtml());
        } else {
            $eaplist[$key]['pio'] = 0;
        }
    }

    $report = new tldReportColumnar(
        $eaplist,
        [
            'xItems' => [
                'pn' => 'Part Number',
                'description' => 'Description',
                'eapip' => 'EAP IP',
                'eapqty' => 'EAP',
                'pdcqty' => 'PDC',
                'pio' => 'PIO',
            ],
            'title' => 'List of Affected Part Numbers',
            'links' => [
                'pn' => "/en/private/manufacturing/eng/dev.php?m[0]=bom&m[1]=view&erp={$eap->itsHeader['erp']}&pn=",
                'eapqty' => [
                    'url' => '/en/private/manufacturing/eng/dev.php?m[0]=eap&m[1]=mlList&m[2]=byPartByStatus',
                    'params' => [
                        'pn' => 'pn',
                    ],
                ],
                'pdcqty' => [
                    'url' => '/en/private/product_support/index.ps.php?m[0]=pdc&m[1]=listing&m[2]=byPartNumber',
                    'params' => [
                        'pn' => 'pn',
                    ],
                ],
                'eapip' => [
                    'url' => '/en/private/manufacturing/eng/dev.php?m[0]=eap&m[1]=mlList&m[2]=byPartByStatus&status[]=IN PROGRESS&status[]=IN QUEUE&status[]=PENDING',
                    'params' => [
                        'pn' => 'pn',
                    ],
                ],
            ],
            'functions' => [
                'EDM BOM' => [
                    'url' => "/en/private/manufacturing/eng/dev.php?m[0]=bom&m[1]=view&m[2]=edmpdf&erp={$eap->itsHeader['erp']}&date=$date&download&pn=",
                    'param' => 'pn',
                    'img' => '/shared/bluesphere/16x16/actions/filesaveas.png',
                ],
                'DRAWING' => [
                    'url' => "/en/private/manufacturing/eng/dev.php?m[0]=getfile&m[1]=drawing&erp={$eap->itsHeader['erp']}&date=$date&item=",
                    'param' => 'pn',
                    'img' => '/shared/bluesphere/16x16/actions/filesaveas.png',
                ],
            ],
            'showzero' => true,
            'sortable' => 'no',
        ]
    );
    $cells[1] .= $report->fetch();
    $cells[1] .= "<a href='/en/private/manufacturing/eng/dev.php?m[0]=eap&m[1]=view&m[2]=pns&m[3]=add&id={$eap->getID()}'>Add</a>";
    $report = new tldReportColumnar(
        $eap->getModels(),
        [
            'xItems' => [
                'type' => 'Type',
                'model' => 'Model',
            ],
            'title' => 'List of Affected Models',
        ]
    );
    $cells[1] .= $report->fetch();
    $cells[1] .= "<a href='/en/private/manufacturing/eng/dev.php?m[0]=eap&m[1]=view&m[2]=models&m[3]=add&id={$eap->getID()}'>Add</a>";
    $parent_id = $eap->getParentID();
    if (!empty($parent_id)) {
        $parent = ($eap->getParentModule() === 'MEAP') ? new tldMEAP($parent_id) : new tldEAP($parent_id);
        $parent_header[0] = $parent->itsHeader;
        $report = new tldReportColumnar(
            _createHierarchyLinks($parent_header),
            [
                'xItems' => [
                    'id_html' => 'ID#',
                    'module' => 'Module',
                    'short_desc' => 'Description',
                ],
                'title' => 'Parent Module of EAP#' . $header['id'],
                '',
            ]
        );
        $cells[1] .= $report->fetch();
    } else {
        $cells[1] .= '<h3>Parent Module of EAP#' . $header['id'] . '</h3>';
        $cells[1] .= '<p>No records...</p>';
    }
    // Get child hierarchy
    $childList = $eap->getChildList();

    $report = new tldReportColumnar(
        _createHierarchyLinks($childList),
        [
            'xItems' => [
                'id_html' => 'ID#',
                'module' => 'Module',
                'short_desc' => 'Description',
            ],
            'title' => 'Child Modules of EAP#' . $header['id'],
            '',
        ]
    );
    $cells[1] .= $report->fetch();
    $eapId = $header['id'];
    $query = <<<SQL
SELECT t1.*, tasks.status, CONCAT(people.firstname, ' ', people.lastname) As assignee, CONCAT('SEQ', tasks.id, ', ', task) As description
FROM mod_links AS t1
INNER JOIN tasks ON t1.item = tasks.id
INNER JOIN people ON people.id = tasks.assignee
WHERE t1.parent_id IN ($eapId) AND t1.module='EAP' AND t1.type = 'SEQ'
ORDER BY id DESC
SQL;

    $reportSequences = new tldReportColumnar(
        tldUtils::getSqlToAssocArray($query),
        [
            'xItems' => [
                'id' => 'ID#',
                'type' => 'Module',
                'item' => 'Ref#',
                'status' => 'Status',
                'assignee' => 'Assignee',
                'description' => 'Description',
            ],
            'title' => 'Sequences Links...',
            'links' => ['id' => "/en/private/common/index.php?m[0]=links&m[1]=view&erp=$erp&id="],
        ]
    );
    $cells[1] .= $reportSequences->fetch();
    
    $report = new tldReportColumnar(
        tldModLink::byParent($header['id'], 'EAP',"","","SEQ"),
        [
            'xItems' => [
                'id' => 'ID#',
                'type' => 'Module',
                'item' => 'Ref#',
                'dsca' => 'Description',
            ],
            'title' => 'Other Links...',
            'links' => ['id' => "/en/private/common/index.php?m[0]=links&m[1]=view&erp=$erp&id="],
        ]
    );
    $cells[1] .= $report->fetch();

    $report = new tldReportColumnar(
        tldModLink::byItem($header['id'], 'EAP'),
        [
            'xItems' => [
                'id' => 'ID#',
                'module' => 'Module',
                'parent_id' => 'Ref#',
                'dsca' => 'Description',
            ],
            'title' => 'Links TO Here...',
            'links' => ['id' => "/en/private/common/index.php?m[0]=links&m[1]=view&reversed=1&erp=$erp&id="],
        ]
    );

    $cells[1] .= $report->fetch();


    global $kernel;

    $client = $kernel->getContainer()->get(\ApiBundle\Client::class);

    try {
        $response = $client->get('quality/first_article_qualifications', [
            'query' => [
                'eap' => $eap->getID(),
                'order' => ['createdAt' => 'desc'],
            ],
        ]);
    } catch (\Symfony\Component\HttpClient\Exception\ClientException $e) {
        $response = ['hydra:member' => []];
    }

    $router = $kernel->getContainer()->get('router');

    $faqs = array_reduce($response['hydra:member'], static function ($memo, $faq) use ($router) {
        $memo[] = [
            'link' => sprintf('<a href="%s">#%s</a>', $router->generate('first_article_qualifications_show', ['id' => $faq['id']]), $faq['id']),
            'factory' => $faq['location']['name'],
            'created_at' => (new \DateTime($faq['createdAt']))->format('Y-m-d h:i:s'),
        ];
        return $memo;
    }, []);

    $faqsReport = new tldReportColumnar($faqs,
        [
            'xItems' => [
                'link' => 'ID',
                'factory' => 'Factory',
                'created_at' => 'Created At',
            ],
            'title' => 'FAQ list',
        ]
    );
    $cells[1] .= $faqsReport->fetch();

    $form = new tldReportColumnar($eap->getFiles(),
        ['xItems' => ['id' => 'ID#',
            'date' => 'Date',
            'poster_fullname' => 'Poster',
            'description' => 'Description',
            'filename' => 'Filename'],
            'title' => 'File list',
            'links' => ['id' => '/en/private/common/index.php?m[0]=files&m[1]=view&m[2]=out&id='],
        ]
    );
    $form1 = new tldReportColumnar($eap->getFileByTasks('ALL'),
        ['xItems' => ['id' => 'ID#',
            'date' => 'Date',
            'poster_fullname' => 'Poster',
            'filename' => 'Filename'],
            'title' => 'File list in Tasks',
            'links' => ['filename' => '/en/private/uploads/tasks_comments/'],
        ]
    );
    $cells[1] .= $form->fetch();
    $cells[1] .= $form1->fetch();
    // Display general and pn/model info
    $report = new tldHTMLTable(
        $cells,
        [
            'cols' => 2,
            'attribs' => ['table' => " width='100%'", 'tr' => " bgcolor='#FFFFFF'"],
        ]
    );
    $result .= $report->fetch();

    return $result;
}

function _viewBPSTab($eap)
{
    $bps = $eap->getBPS('ALL');
    $i = 1;
    if (array_key_exists('assigneeExists', $eap->itsHeader) && $eap->itsHeader['assigneeExists'] === false) {
        $body .= "<p style='color:#ff0000'><strong>Process automatically closed as no people have been selected</strong></p>";
    }
    foreach ($bps as $bp) {
        $form = new tldReportColumnar(
            [$bp],
            [
                'xItems' => [
                    'id' => 'BP#',
                    'status' => 'Status',
                    'dt_opened' => 'Date Opened',
                    'dt_closed' => 'Date Closed',
                    'short_desc' => 'Description',
                ],
                'title' => "Process #$i",
                'links' => ['id' => '/en/private/calendar/calendar.php?m[0]=bp&m[1]=view&id='],
            ]
        );
        $body .= $form->fetch();
        $bp = new tldBP($bp['id']);
        $bpTasks = $bp->getTasks('ALL');
        $form = new tldReportColumnar(
            $bpTasks,
            [
                'xItems' => [
                    'id' => 'Task#',
                    'status' => 'Status',
                    'due_date' => 'Due',
                    'assignee_fullname' => 'Assignee',
                    'lastcomment' => 'Last Comment',
                ],
                'title' => 'Tasks',
                'links' => ['id' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id='],

            ]
        );
        $body .= $form->fetch();
        $body .= '<br>';
        $i = $i + 1;
    }
    return $body;
}

function _getKPIgraph($GraphURL, $_TITLE, $groupTarget)
{
    global $help;
    $body = <<<EOF
<br><br><img src="$GraphURL"><br/>
EOF;
    $groupTargetText = '';
    $popupDef = new tldOverlib(
        $help[$_TITLE] . $groupTargetText,
        [
            'CAPTION' => $_TITLE,
            'WIDTH' => '500',
            'linkName' => $_TITLE,
        ]
    );
    $body .= $popupDef->fetch();
    return $body;
}
