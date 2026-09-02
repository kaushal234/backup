<?php

switch($m[2]){
case 'bySeqSearch':
    $locations = tldLocation::getLocationList('smartyOptions');
    $datas = tldSEQTpl::getSEQTplList();
    // Check TPL authorized
    $acl_tplno = array();
    foreach($ACL_SEQ_TPL as $tplID=>$groups){
        if($user->isInGroup($groups)){
            $acl_tplno[] = $tplID;
        }
    }
    if(count($acl_tplno)<1){
        $DEFAULT_ERROR[] = "ERROR: Your account do not have permissions to look for any TPL sequences";
        break;
    }
    // Construct TPL list for the form
    foreach($datas as $data){
        if(!in_array($data['id'],$acl_tplno)) continue;
        $tplList[$data['id']] = $data['short_desc'];
    }
    // Get form
    $form = new HTML_QuickForm('frmSearch', 'get','','','',TRUE);
    $form->addElement(  'hidden', 'm[0]', 'tasks');
    $form->addElement(  'hidden', 'm[1]', 'listing');
    $form->addElement(  'hidden', 'm[2]', 'advSearch');
    $form->addElement(  'header', 'title', "Search for sequence");
    $form->addElement(  'text', 'parent_id', 'Ref#');
    $form->addElement(  'select', 'bu_id', 'BU', $locations);
    $form->addElement(  'select', 'tplno', 'Template', $tplList);
    $form->addElement(  'select', 'status', 'Status',
        array(""=>"","OPEN"=>"OPEN","CLOSED"=>"CLOSED"));
    $form->addElement(  'date', 'open_start', 'OPEN date from',
        array("format"=>"Ymd", 'addEmptyOption'=>TRUE, "minYear"=>date("Y")-2, "maxYear"=>date("Y")));
    $form->addElement(  'date', 'open_end', 'OPEN date to',
        array("format"=>"Ymd", 'addEmptyOption'=>TRUE, "minYear"=>date("Y")-2, "maxYear"=>date("Y")));
    $form->addElement(  'submit', 'btnSubmit', 'Submit');
    $form->addElement(  'reset', 'btnReset', 'Reset');
    $form->addRule('bu_id', 'Required','required');
    $form->addRule('tplno', 'Required','required');
    $form->setDefaults(
        array(
            "bu_id"=>$user->getBUID(),
            "tplno"=>$tplno,
            "open_start"=>array("Y"=>date('Y'),"m"=>"01","d"=>"01")
        )
    );
    $body.= $form->toHTML();
break;
case 'advSearch':
    $DEFAULT_TITLE .= "\Task/Sequence search";
    // Get listing
    $locations = tldLocation::getLocationList('smartyOptions');
    $people = array();
    $people_list = tldDirectory::byConstraints("lastname<>'' AND firstname<>''");
    foreach($people_list AS &$p){
    	if($p['hidden']){
    		$p['fullname'] .= " [HIDDEN]";
    	}
    	$people[$p['id']] = $p['fullname'];
    }
    $tpls = array_column(tldSEQTpl::getSEQTplList(), 'name', 'id');
    $steps = array("1"=>"1","2"=>"2","3"=>"3","4"=>"4","5"=>"5");
    $status = array_combine(tldTask::getStatusList(),tldTask::getStatusList());
    $modules = array_column(tldModule::getList(), 'dsc', 'module');
    $divisions = tldRegion::getListAsIdDivision();
    // Get form
    $form = new HTML_QuickForm('frmSearch', 'get','','','',TRUE);
    $form->addElement(  'hidden', 'm[0]', 'tasks');
    $form->addElement(  'hidden', 'm[1]', 'listing');
    $form->addElement(  'hidden', 'm[2]', 'advSearch');
    $form->addElement(  'header', 'title', "Advanced search Tasks/Sequences");
    if($user->isInGroup("gg_MIS")) {
        $form->addElement('select', 'assignee', 'Assignee', array("" => "") + $people);
        $form->addElement('select', 'assignor', 'Assignor', array("" => "") + $people);
        $form->addElement(
            'advmultiselect',
            'assignor_div_id',
            'Assignor Division',
            $divisions,
            ['size' => 6, 'class' => 'pool', 'style' => 'width:380px;']
        );
    }
    $form->addElement(  'select', 'assignee_bu_id', 'Assignee BU', array(""=>"")+$locations);
    $form->addElement(  'select', 'status', 'Status', array(""=>"")+$status);
    $form->addElement(  'text', 'parent_id', 'Ref#');
    $form->addElement(  'select', 'module', 'Module', array(""=>"")+$modules);
    $form->addElement(  'text', 'task', 'Task keyword');
    $form->addElement(  'header', 'title', "If sequence");
    $form->addElement(
        'advmultiselect',
        'tplno',
        'Template',
        $tpls,
        ['size' => 6, 'class' => 'pool', 'style' => 'width:380px;']
    );
    if($user->isInGroup("gg_MIS")) {
        $form->addElement('select', 'bu_id', 'BU', array("" => "") + $locations);
        $form->addElement('select', 'cur_step', 'Step#', array("" => "") + $steps);
    }
    $form->addElement(  'header', 'title', "Misc");
    $form->addElement(  'date', 'open_start', 'OPEN date from',
        array("format"=>"Ymd", 'addEmptyOption'=>TRUE, "minYear"=>date("Y")-2, "maxYear"=>date("Y")));
    $form->addElement(  'date', 'open_end', 'OPEN date to',
        array("format"=>"Ymd", 'addEmptyOption'=>TRUE, "minYear"=>date("Y")-2, "maxYear"=>date("Y")));
    if($user->isInGroup("gg_MIS")) {
    $form->addElement('date', 'close_start', 'CLOSE date from',
        array("format" => "Ymd", 'addEmptyOption' => TRUE, "minYear" => date("Y") - 2, "maxYear" => date("Y")));
    $form->addElement('date', 'close_end', 'CLOSE date to',
        array("format" => "Ymd", 'addEmptyOption' => TRUE, "minYear" => date("Y") - 2, "maxYear" => date("Y")));
    }
    $form->addElement(  'submit', 'btnSubmit', 'Submit');
    $form->addElement(  'reset', 'btnReset', 'Reset');
    $body.= $form->toHTML();
break;
case "byNumber":
    $DEFAULT_TITLE .= "\Task/Sequence by Number";
    $form = new HTML_QuickForm('frmNewTask', 'post');
    $form->addElement(	'hidden', 'm[0]', 'tasks');
    $form->addElement(	'hidden', 'm[1]', 'form');
    $form->addElement(	'hidden', 'm[2]', 'byNumberGet');
    $form->addElement(	'header', 'title', "View task/sequence by Number");
    $form->addElement(	'text', 'id', 'Task#');
    $form->addElement(	'submit', 'btnSubmit', 'Submit');
    $body = $form->toHTML();
break;
case 'byNumberGet':
    if (empty($id)) {
        $DEFAULT_ERROR[] = 'ERROR: TTS number required to do search.';
        break;
    }
    $troubleTicket = new tldTask($id);
    // if only one entry, redirect to general view
    if (!is_string($troubleTicket)) {
        header("Location: $php_self?m[0]=tasks&m[1]=task&&m[2]=view&id=$id");
    }
    break;
case "byUser":
	$DEFAULT_TITLE .= "\Task/Sequence by User";
	$ordinates = tldUser::getAllSubordinates($user->getId());
	$name =array();
	foreach($ordinates as $key=>$value){$name[$value]=$value;}
	// Form
	$form = new HTML_QuickForm('frmNewTask', 'post');
	$form->addElement(	'hidden', 'm[0]', 'sub');
	$form->addElement(	'hidden', 'm[1]', 'subsub');
	$form->addElement(	'hidden', 'single', '1');
	$form->addElement(	'header', 'title', "View task/sequence by User");
	$form->addElement(  'select', 'name', 'User#', array(""=>"")+$name);
	$form->addElement(	'submit', 'btnSubmit', 'Submit');
	$form->addRule('name', 'Required','required');

    if(!$form->validate()) {
        $body .= $form->toHTML();
        break;
    }
break;
case "newTask":
    $DEFAULT_TITLE .= "\New Task";
    // Check module
    if(empty($module)) $module="USER";
    $module = strtoupper($module);
    if(!in_array($module, tldTask::getModuleList(), true)) {
        $DEFAULT_ERROR[] =  "ERROR: Module '$module' is not valid";
        break;
    }
    // Check module record and get module obj record
    switch($module){
    case 'PDC':
        $pdc = new tldPDC((int)$parent_id);
        if($pdc->isEmpty())
        {
            $DEFAULT_ERROR[] = "PDC#$parent_id not found, can not create task";
            break 2;
        }
        if($pdc->isClosed())
        {
            $DEFAULT_ERROR[] = "PDC#$parent_id is CLOSED, can not create task";
            break 2;
        }
        break;
    case 'EAP':
        $eap = new tldEAP((int)$parent_id);
        if($eap->isEmpty())
        {
            $DEFAULT_ERROR[] = "EAP#$parent_id not found, can not create task";
            break 2;
        }
        $status = strtoupper($eap->getStatus());
        if(in_array($status, ['REJECTED', 'CLOSED', 'PENDING', 'IN QUEUE'], true))
        {
            $DEFAULT_ERROR[] = "EAP#$parent_id is $status, can not create task";
            break 2;
        }
        break;
    case 'CPA':
        $cpa = new tldCPA((int)$parent_id);
        if($cpa->isEmpty())
        {
            $DEFAULT_ERROR[] = "CPA#$parent_id not found, can not create task";
            break 2;
        }
        if($cpa->isClosed())
        {
            $DEFAULT_ERROR[] = "CPA#$parent_id is CLOSED, can not create task";
            break 2;
        }
        break;
    case 'TTS':
        $tts = new tldTTS((int)$parent_id);
        $header = $tts->getHeader();
    break;
    case 'GWF':
        $gwf = new tldGWF((int)$parent_id);
        $header = $gwf->getHeader();
        break;
    }

    // Possible Assignee list following the user connected
    $assignees = tldTask::getAssigneesByUser($module, $user->getId());
    // Adjust cc Addressbook for AERO Task module
    $ccAddressList = ('ASO' !== $module) ? tldDirectory::getUserlist('smartyOptions') : tldDirectory::getUserlistByERP($user->getBUID(),'smartyOptions');
    // Form
    $form = new HTML_QuickForm('frmNewTask', 'post');
    $form->addElement(	'hidden', 'm[0]', 'tasks');
    $form->addElement(	'hidden', 'm[1]', 'form');
    $form->addElement(	'hidden', 'm[2]', 'newTask');
    $form->addElement(	'hidden', 'module', $module);
    $form->addElement(	'hidden', 'parent_id', $parent_id);
    $form->addElement(	'header', 'title', "Submit new $module task");
    $form->addElement(	'date', 'due_date', 'Due Date or use below',
        array(	"format"=>"Ymd", "minYear"=>date("Y"), "maxYear"=>date("Y")+2));
    $form->addElement(	'select', 'due_date_value', 'Due date plus (overrides due date above)',
        array(0,1,2,3,4,5,6,7,8,9,10,11,12));
    $form->addElement(	'select', 'due_date_unit', 'Due date unit',
        array("DAY"=>"DAYS","WEEK"=>"WEEKS","MONTH"=>"MONTHS","QUARTER"=>"QUARTERS","YEAR"=>"YEARS"));
    $form->addElement(	'text', 'escalation_trigger', 'Escalation trigger(Days)',array('size'=>10,'maxlength'=>5));
    if($user->isInGroup("gg_MIS")) {
        $form->addElement(	'select', 'assignor', 'Assignor', $assignees);
    }
    if(($user->getId() === $header['owner'] && $module === 'TTS') || ($module === 'GWF' && ($user->getId() === $header['assignor'] || $gwf->isMember($user->getID())))) {
        $ams =& $form->addElement(
            'advmultiselect', 'assignee', null,
            $ccAddressList,
            [
                'size' => 10,
                'class' => 'pool',
                'style' => 'width:500px;'
            ]
        );
        $ams->setLabel(['Assignee (max 15 recipients)... (OPTIONAL)', 'Addressbook', 'Assignee']);
        $form->addRule('assignee', 'Required', 'required');
    } else {
        $form->addElement(	'select', 'assignee', 'Assignee', $assignees);
    }
    $form->addElement(	'text', 'hours', 'Estimated Time of Completion (Hours)');
    $form->addElement(	'textarea', 'task', 'Task',
        array("wrap"=>"VIRTUAL", "cols"=>"60", "rows"=>"8"));
    $ams =& $form->addElement(
        'advmultiselect', 'cc_users', null,
        $ccAddressList,
        [
            'size' => 10,
            'class' => 'pool',
            'style' => 'width:500px;'
        ]
    );
    $ams->setLabel(array('CC others (max 15 recipients)... (OPTIONAL)', 'Addressbook', 'CC'));
    $ams->setButtonAttributes('add',    array('value' => '-->>', 'class' => 'inputCommand'));
    $ams->setButtonAttributes('remove', array('value' => '<<--', 'class' => 'inputCommand'));
    $form->addElement(	'file', 'filename', 'File');
    $form->addElement(	'submit', 'btnSubmit', 'Submit');

    $defaults = [
        "due_date" => ["Y" => date("Y"),
            "m" => date("m"),
            "d" => date("d"),
        ],
        "escalation_trigger" => 30,
        "assignor" => $user->getId(),
        "assignee" => $user->getId(),
    ];
    if ($nextAssignee) {
        $u = new tldUser($nextAssignee);
        if (!$u->isEmpty() && $u->isEnable() && array_key_exists($nextAssignee, $assignees)) {
            $defaults["assignee"] = $nextAssignee;
        }
    }
    if (isset($sess['task_body']) && !empty($sess['task_body'])) {
        $defaults['task'] = stripslashes((string)$sess['task_body']);
        unset($sess['task_body']);
    }
    $form->setDefaults($defaults);
    $form->addRule('task', 'Required','required');
    $form->addRule('escalation_trigger', 'Required','required');
    $form->addRule( 'escalation_trigger', 'Field is numeric', 'numeric');
    $form::registerRule('maxvalue','function','max_value_f');
    $form->addRule('escalation_trigger', "Maximum value for Escalation factor is 60 days as per TLD rules",'maxvalue');

    if(!$form->validate()) {
        $body = $form->toHTML();
        break;
    }

    $vals = tldUtils::cleanupFormInput($form->exportValues());
    // Check if Estimated Time of Completion value is numeric
    if(!empty($vals['hours']) && !is_numeric($vals['hours'])){
    	$DEFAULT_ERROR[] = "ERROR: Estimated Time of Completion entry must be numeric!";
    	$body = $form->toHTML();
    	break;
    }
	// Check nb cc users
    if (is_array($vals['cc_users'])) {
        $numCC = count($vals['cc_users'] ?? []);
        if ($numCC > 15) {
            $DEFAULT_ERROR[] = "ERROR: Cannot cc to more than 15 persons!";
            $body = $form->toHTML();
            break;
        }
    }

    if(!$user->isInGroup("gg_MIS")) {
        $vals["assignor"] = $user->getId();
    }
    $y=$vals["due_date"]["Y"];
	$m=$vals["due_date"]["m"];
	$d=$vals["due_date"]["d"];
        // Check date entries
	if(!checkdate($m, $d, $y)){
    	$DEFAULT_ERROR[] = "ERROR: Invalid due date set! Please check the last day of the chosen month!";
    	$body = $form->toHTML();
    	break;
    }
    if($vals['due_date_value']<>0) {
        $vals['due_date'] = array("value"=>$vals['due_date_value'], "unit"=>$vals['due_date_unit']);
    }elseif(mktime(0,0,0,$vals["due_date"]["m"],$vals["due_date"]["d"],$vals["due_date"]["Y"])-mktime(0,0,0,date("m"),date("d"),date("Y")) < 0){
        $DEFAULT_ERROR[] = "ERROR: Cannot set due date in the past!";
		$body = $form->toHTML();
        break;
    }

    // default values depending module
    switch($module){
    case 'TTS':
        // case of TTS project -> A cat
        if($tts->getStatus()<>'QUEUE'){
            $vals['cat']='A';
        }
    break;
    }

    // Create task
    switch($module){
    case 'PDC':
        $error = $pdc->addStatusTask($vals);
    break;
    case 'CPA':
        $error = $cpa->addStatusTask($vals);
    break;
    case 'TTS':
    case 'GWF':
        $tasks = [];
        foreach ((array) $vals['assignee'] as $id) {
            $vals['assignee'] = $id;
            $tasks[] = tldTask::insert($parent_id, $vals, $module);
        }
        break;
    default:
        $error = tldTask::insert($parent_id, $vals, $module);
    break;
    }
    if((!is_numeric($error) && !empty($error))) {
        $DEFAULT_ERROR[] =  "Could not create new task. There was an error processing. The error returned is '$error'";
        break;
    }

    if ('FAQ' === $module) {
        global $kernel;

        $client = $kernel->getContainer()->get(\ApiBundle\Client::class);

        try {
            $client->post('comments', [
                'json' => [
                    'resource' => '/quality/first_article_qualifications/'.$parent_id,
                    'message' => sprintf('Task #%s created', $error),
                ]
            ]);
        } catch (\Symfony\Component\HttpClient\Exception\ClientException $e) {
            // do nothing
        }
    }

    $tasks = $tasks ?? [$error];

    foreach($tasks as $key => $error) {
        if(!is_numeric($error)) {
            $DEFAULT_ERROR[] =  "Could not create new task. There was an error processing. The error returned is '$error'";
            break;
        }
        $task = new tldTask($error);
        if($numCC > 0){
            foreach($vals['cc_users'] as $userid){
                $ccUser = new tldUser($userid);
                $ccList[] = $ccUser->getEmail();
                if(!in_array($userid, $listCC ?? [])) {
                    $task->addCC($userid);
                }
            }
        }
        //add file if any
        $file = $form->getElement("filename");
        $comment["file_info"] = $file->getValue();
        $comment["poster"] = $user->getId();
        $task->addComment($comment);

        $assignee = new tldUser($task->getAssignee());
        $assignor = new tldUser($task->getAssignor());
        $assignee_fullname = $assignee->getFullname();
        $message =<<<EOF
Task #$error has been assigned to $assignee_fullname.\n<br>
Please log in to the TLD-GSE intranet and go to the calendar module to view your open tasks.\n<br>
<a href="https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$error">
Click here to go to Task.
</a>
EOF;
        $ccList = array();
        switch($module) {
            //if this is linked to a GWF then notify the group members also
            case 'GWF':
                $gwf = new tldGWF($parent_id);
                $members = $gwf->getMembers();
                foreach($members as $member) {
                    $ccList[] = $member['email'];
                }
                break;
        }
        if('PDC' !== $module && tldModMember::hasMembers($parent_id, $module)){
            $members = tldModMember::byParent($parent_id, $module);
            foreach($members as $member) {
                $ccList[] = $member['email'];
            }
        }
        if($numCC > 0){
            foreach($vals['cc_users'] as $userid) {
                $ccUser = new tldUser($userid);
                $ccList[] = $ccUser->getEmail();
            }
            $cclist .= "\ncc: ".implode(',', $ccList);
        }
        $ccList = array_unique($ccList);

        $subject = "Tasks, New: #$error opened for ".$assignee->getFullname()." by ".$assignor->getFullname();
        $task->notifyAssignee($message, $subject, $ccList);

        $task->addComment(array("poster"=>$user->getId(), "comment"=>$subject.$cclist));
        $DEFAULT_ERROR[] =  "Task #$error successfully opened...";
        //notify others depending on the module type
        switch($module) {
            case 'CPA':
                $cpa = new tldCPA($parent_id);
                $taskDetail = $task->getTask();
                $message = <<<EOF
$message <br/><br/>
Task Description:
$taskDetail
<br/>
EOF;
                $cpa->notifyInitiator($message, "New Task Opened for CPA #$parent_id");
                break;
        }
        $sess["calendar"]["tasks"] = array();
        if($module != '' && $parent_id != ''){
            $body .=<<<EOF
<br><a href="$php_self?m[0]=tasks&m[1]=task&m[2]=view&id=$error">Click here to see task #$error.</a> (<a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=form&m[2]=newTask&module=$module&parent_id=$parent_id">Click here to create an additional task for $module#$parent_id.</a>)
EOF;
            $links=tldUtils::getModLinks();
            foreach (['NCR', 'VWC', 'FAQ', 'SOL', 'PDC', 'SB3', 'WC'] as $backToModule) {
                if ($module === $backToModule) {
                    $link = tldModLink::getURL($backToModule, $parent_id);
                    $body .=<<<EOF
<br><a href="$link">Click here to go back to $backToModule#$parent_id.</a>
EOF;
                }
            }
        }else{
            $body .=<<<EOF
<meta http-equiv="refresh" content="0;URL=$php_self?m[0]=tasks&m[1]=task&m[2]=view&id=$error">
<a href="$php_self?m[0]=tasks&m[1]=task&m[2]=view&id=$error">Click here to see task.</a>
EOF;
        }
    }

break;
case 'translationRequest':
    $DEFAULT_TITLE.= "/New Translation Request";

    // Config
    $OWNER = array(
        "en"=>2906,  // CRESPEL Yves
        "fr"=>3311,  // BELHAMOU Danielle
        "de"=>1750,  // WEHNER, Kurt
        "pt"=>585,   // PINTO Renato
        "es"=>1052,  // Isaac ROMERO
    	"ja"=>2720,  // IRIE ARISA
        "zh"=>1195,   // HOU	Mei
        "ru"=>935    // GEVORKOVA Alla
    );
    $LANG = array(
        "en"=>"English",
        "fr"=>"French",
        "de"=>"German",
        "pt"=>"Portuguese",
        "es"=>"Spanish",
        "ja"=>"Japanese",
        "zh"=>"Chinese",
        "ru"=>"Russian"
    );

    switch($m[3]){
    default:
        // clean session
        $sess['task']['translationRequest'] = null;
        // js tool
        $body.= <<<EOF
<script type="text/javascript">
$(function(){
	// do not show button uncheck
	$('#btnUncheck').css('display','none');
	// on button click, check all checkbox
	$('#btnCheck').click(function(){
		$("input:checkbox").prop('checked', true);
		$('#btnUncheck').css('display','block');
		$(this).css('display','none');
	});
	// on button click, uncheck all checkbox
	$('#btnUncheck').click(function(){
		$("input:checkbox").prop('checked', false);
		$('#btnCheck').css('display','block');
		$(this).css('display','none');
	});
});
</script>
EOF;
        // Listing
        $peopleList = tldDirectory::getUserlist("smartyOptions");
        // Form
        $form = new HTML_QuickForm('frmNewTask', 'post');
        $form->addElement(	'hidden', 'm[0]', 'tasks');
        $form->addElement(	'hidden', 'm[1]', 'form');
        $form->addElement(	'hidden', 'm[2]', 'translationRequest');
        $form->addElement(	'hidden', 'm[3]', 'langSelection');
        $form->addElement(	'header', 'title', "Select languages & traductors");
        foreach($LANG as $code=>$lang){
            $a = array();
            $a[] = &$form->createElement('checkbox', "lang[$code][create]", $lang, "$lang ($code)");
            $a[] = &$form->createElement('select', "lang[$code][assignee]", null, $peopleList);
            $form->addGroup($a, null, null, ' -> ');
            $form->setDefaults(array("lang[$code][assignee]"=>$OWNER[$code]));
        }
        $form->addElement(	'button', 'btn', 'Check all', array('id'=>'btnCheck'));
        $form->addElement(	'button', 'btn', 'Uncheck all', array('id'=>'btnUncheck'));
        $form->addElement(	'submit', 'btnSubmit', 'Submit');

        if(!$form->validate()){
            $body.= $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        foreach($vars['lang'] as $code=>$var){
            if(!$var['create']) continue;
            $sess['task']['translationRequest'][$code] = $var['assignee'];
        }
        if(!count($sess['task']['translationRequest'])){
            $DEFAULT_ERROR[] = "ERROR: No language selected, could not continue";
            $body.= $form->toHTML();
            break;
        }
        header("Location: $php_self?m[0]=tasks&m[1]=form&m[2]=translationRequest&m[3]=taskCreation");
    break;
    case 'taskCreation':
        // Check langue selected
        if(!count($sess['task']['translationRequest'])){
            $DEFAULT_ERROR[] = "ERROR: No language selected on previous step or session expired";
            break;
        }
        // get lang list
        $selectedLang = array_intersect_key($LANG,$sess['task']['translationRequest']);
        // Form
        $form = new HTML_QuickForm('frmNewTask', 'post');
        $form->addElement(	'hidden', 'm[0]', 'tasks');
        $form->addElement(	'hidden', 'm[1]', 'form');
        $form->addElement(	'hidden', 'm[2]', 'translationRequest');
        $form->addElement(	'hidden', 'm[3]', 'taskCreation');
        $form->addElement(	'header', 'title', "Submit translation task(s) for ".implode(', ',$selectedLang));
        $form->addElement(	'date', 'due_date', 'Due Date or use below',
            array(	"format"=>"Ymd", "minYear"=>date("Y"), "maxYear"=>date("Y")+2));
        $form->addElement(	'select', 'due_date_value', 'Due date plus (overrides due date above)',
            array(0,1,2,3,4,5,6,7,8,9,10,11,12));
        $form->addElement(	'select', 'due_date_unit', 'Due date unit',
            array("DAY"=>"DAYS","WEEK"=>"WEEKS","MONTH"=>"MONTHS","QUARTER"=>"QUARTERS","YEAR"=>"YEARS"));
        $form->addElement(	'textarea', 'text', 'Text to translate',
            array("wrap"=>"VIRTUAL", "cols"=>"60", "rows"=>"8"));
        $form->addElement(	'file', 'filename', 'File (if any)');
        $form->addElement(	'submit', 'btnSubmit', 'Submit');
        $form->setDefaults(array(
        	"due_date"=>array("Y"=>date("Y"),"m"=>date("m"),"d"=>date("d"))
        ));
        $form->addRule('task', 'Required','required');

        if(!$form->validate()){
            $body .= $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $vals = array();
        if($vars['due_date_value']<>0){
            $vals['due_date'] = array(
            	"value"=>$vars['due_date_value'],
            	"unit"=>$vars['due_date_unit']
            );
        }
        $vals["assignor"] = $user->getID();
        // Foreach lang selected to translate, create task
    	foreach($sess['task']['translationRequest'] as $lang=>$assigneeID){
    	    $vals["assignee"] = (int)$assigneeID;
    	    $vals["task"] = <<<EOF
Please translate attached text into {$LANG[$lang]}

{$vars['text']}
EOF;
    	    $error = tldTask::insert(NULL, $vals, "USER");
            if(!is_numeric($error)) {
                $DEFAULT_ERROR[] =  "ERROR: Can not create task!<br>Reason: $error";
                continue;
            }
            $task = new tldTask($error);
            // Add file if any
            $file = $form->getElement("filename");
            $comment["file_info"] = $file->getValue();
            $comment["poster"] = $user->getId();
            if(!empty($comment["file_info"])){
                $task->addComment($comment);
            }
            // Send notification
            $assignee = new tldUser($task->getAssignee());
            $assignor = new tldUser($task->getAssignor());
            $assignee_fullname = $assignee->getFullname();
            $message =<<<EOF
Task #$error has been assigned to $assignee_fullname.\n<br>
Please log in to the TLD-GSE intranet and go to the calendar module to view your open tasks.\n<br>
<a href="https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$error">
Click here to go to Task.
</a>
EOF;
            $subject = "Tasks, New: #$error opened for ".$assignee->getFullname()." by ".$assignor->getFullname();
            $task->notifyAssignee($message, $subject, NULL);
            // Add comment for creation details
            $task->addComment(
                array(
                	"poster"=>$user->getId(),
                	"comment"=>$subject
                )
            );
            $body.=<<<EOF
<br><a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$error">Task#$error created for {$LANG[$lang]}</a>
EOF;
	    }
	    $body.=<<<EOF
<br><br>Translation request process completed!
EOF;
        // Clear session
        $sess['task']['translationRequest'] = null;
    break;
    }
break;
}


function max_value_f($element_name,$element_value){
    return $element_value <= 60;
}

