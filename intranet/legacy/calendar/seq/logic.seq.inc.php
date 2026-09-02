<?php
include_once('mis.inc.php');
include_once('erp.inc.php');
$DEFAULT_TITLE .= "\Sequences";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=seq">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=seq&m[1]=listing&m[2]=quickedit">Quick SEQ Approval</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=form&m[2]=byNumber">By Num</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=seq&m[1]=listing&m[2]=search">Search</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=seq&m[1]=reports">Reports</a>
EOF;

if($user->isInGroup("superuser")){
	$DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="tasks/tasks_admin.php">SEQ Admin</a>
&nbsp;|&nbsp;<a href="seq/seq_tpl_admin.php">SEQ TPL Admin</a>
EOF;
}

$JS_INCLUDE=array("/shared/javascript/overlib/overlib.js");
$smarty->assign("js_includes",$JS_INCLUDE);

switch($m[1]){
case "new":
    include('new.seq.inc.php');
break;
case "reports":
    switch($m[2]){
    case "countByTplStatus":
        // Filter private sequence template
    	$datas = tldSEQTpl::getSEQTplList();
    	$tplList = array();
    	foreach($datas as $data){
    		if($data['private']=="Y") continue;
    		$tplList[$data['name']] = $data['short_desc'];
    	}
    	// Create definitions
		$cells[0] = "<h3>Definitions</h3>";
		foreach($tplList as $tpl=>$desc){
			$form = new tldOverlib(
				$desc,
				array("linkName"=>$tpl)
			);
			$cells[0] .= $form->fetch()."<br/>";
		}
    	// Create the matrix
    	$form = new tldMatrix(
	        tldSEQ::countByTplStatus(),
	        "status", "name", "num",
	        "$php_self?m[0]=seq&m[1]=listing&m[2]=byTplStatus",
	        "Public sequences by template, by status",
	        array(
	            "yItems"=>array_keys($tplList),
	        	"xItems"=>array("OPEN"),
	        	"doNotShowTotals"=>TRUE
	        )
	    );
		$cells[1] = $form->fetch();
		// Show definitions and matrix
		$report = new tldHTMLTable($cells,
            array(
            	"cols"=>2,
	            "attribs"=>array(
	            	"table"=>"width='100%'",
	            	"tr"=>" bgcolor='#FFFFFF'"
            	)
            )
        );
        $body .= $report->fetch();
    break;
    case 'misInvestmentBudget':
        if (!$user->isInGroup(['gg_MIS', 'superuser', 'gg_ADMIN'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permissions';
            return;
        }

        $businessUnits = array_filter(array_column(tldLocation::getLocationList(), 'business_unit', 'id'));
        $form = new HTML_QuickForm('form', 'post','','','',TRUE);
        $form->addElement(  'hidden', 'm[0]', 'seq');
        $form->addElement(  'hidden', 'm[1]', 'reports');
        $form->addElement(  'hidden', 'm[2]', 'misInvestmentBudget');
        $form->addElement(  'header', 'title', 'Search sequences');
        $buForm = &$form->addElement('advmultiselect', 'businessUnits', null,
            $businessUnits,
            ['size' => 10, 'class' => 'pool', 'style' => 'width:200px;']
        );
        $buForm->setLabel(['BU', '', '']);
        $buForm->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
        $buForm->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
        $form->addElement(  'submit', 'btnSubmit', 'Submit');

        if(!$form->validate()){
            $body .= $form->toHTML();
            break 2;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $rows = tldSEQ::byMisInvestmentBudgetByBusinessUnit($vars['businessUnits'] ?? null);
        $report = new tldReportColumnar(
            $rows,
            [
                'xItems' => [
                    'task' => 'Description',
                    'assignorLocation' => 'BU',
                    'assignorSite' => 'Site',
                    'id' => 'Sequence #',
                    'cur_step' => 'Step',
                    'assignorFullname' => 'Assignor',
                    'assigneeFullname' => 'Assignee',
                    'status' => 'Status',
                ],
                'title' => 'MIS Investment Budget Sequence',
                'links' => [
                    'id' => "$php_self?m[0]=tasks&m[1]=task&m[2]=view&id="
                ]
            ]
        );

        $body .= $form->toHTML();
        $body .= $report->fetch();
        break;

        default:
        $body .= $smarty->fetch("$PATH/seq/reports/homepage.reports.inc.tpl");
    break;
    }
break;
case "listing":
    switch($m[2]){
        case 'quickedit':
            $seq = tldSEQ::getUserQuickEditableSeqSummary($user->getID());
            $report= new tldMatrix(
                $seq,
                "overdue", "short_name", "seq_number",
                "/en/private/calendar/calendar.php?m[0]=seq&m[1]=form&m[2]=quickedit",
                "Sequence Dashboard",
                [
                    'xItems' => ['LATE', 'DUE'],
                    'doNotShowXTotals' => true,
                    'yFieldLink' => 'id',
                ]
            );
            $body.= $report->fetch();
            break 2;

    case "byTplStatus":
    	$datas = tldSEQTpl::getSEQTplList();
    	// Filter private sequence template
    	$tplList = array();
    	foreach($datas as $data){
    		if($data['private']=="Y") continue;
    		$tplList[] = $data['name'];
    	}
    	// Check template if public
    	if(!in_array($y,$tplList)){
    		$DEFAULT_ERROR[] =  "ERROR: $y is private";
    		break;
    	}
    	$x = TldDatabase::escape($x);
    	$y = TldDatabase::escape($y);
    	$rows = tldSEQ::byTplStatus($x,$y);
		$_TITLE = "Sequence by Template $y, Status $x";
    break;
    case "byOpenByHRTemplateByDivision":
        // default constraints
        $a = array("status"=>"OPEN");
        // clean vars
    	$division = TldDatabase::escape($x);
    	$templateDesc = TldDatabase::escape($y);

    	if(isset($_GET['step']) && !empty($_GET['step']) && $_GET['step']<>'ALL')
    	{
    	    $step = TldDatabase::escape($_GET['step']);
    	    $a['cur_step'] = $step;
    	}

        $rows = tldSEQ::byHRTemplateByDivisionByConstraints($templateDesc,$division,$a);
        $_TITLE = "HR Sequence by Template '$y', Division '$x'";
    break;
    case 'search':
        // Listing
        $locationList = tldLocation::getLocationList('smartyOptions');
        $peopleRawList = tldDirectory::byConstraints("lastname<>'' AND firstname<>''");
        $peopleList = array();
        foreach($peopleRawList AS $p){
        	if($p['hidden']) $p['fullname'] .= " [HIDDEN]";
        	$peopleList[$p['id']] = $p['fullname'];
        }
        $stepList = tldSEQTplNode::getStepList();
        $statusList = array_combine(tldTask::getStatusList(),tldTask::getStatusList());
        $moduleList = array_column(tldModule::getList(), 'dsc', 'module');
        $divisions = tldRegion::getListAsIdDivision();
        // Template list mgmt
        $templateList = array();
        //-- Allow all public tpl
        $publicTemplateList = array_column(tldSEQTpl::getPublicList(), 'short_desc', 'id');
        //-- Specifics for private tpl
        $allowedPrivateTemplateList = array();
        $privateTemplateList = tldSEQTpl::getPrivateList();
        foreach($privateTemplateList as $templateVal){
            $tpl = new tldSEQTpl((int)$templateVal['id']);
            $groups = array_column($tpl->getNodes(), 'group_name', 'group_name');
            if($user->isInGroup($groups)){
                $allowedPrivateTemplateList[$tpl->getID()] = $tpl->getShortDesc().' [private]';
            }
        }
        $templateList = $publicTemplateList + $allowedPrivateTemplateList;
        asort($templateList);
        // Form
        $form = new HTML_QuickForm('frmSearch', 'post','','','',TRUE);
        $form->addElement(  'hidden', 'm[0]', 'seq');
        $form->addElement(  'hidden', 'm[1]', 'listing');
        $form->addElement(  'hidden', 'm[2]', 'search');
        $form->addElement(  'header', 'title', "Search sequences");
        $form->addElement(  'select', 'assignee', 'Assignee', array(""=>"")+$peopleList);
        $form->addElement(  'select', 'assignor', 'Assignor', array(""=>"")+$peopleList);
        $form->addElement(  'select', 'assignor_div_id', 'Assignor Division', array(""=>"")+$divisions);
        $form->addElement(  'select', 'status', 'Status', array(""=>"")+$statusList);
        $form->addElement(  'text', 'parent_id', 'Ref#');
        $form->addElement(  'select', 'bu_id', '<a href="#" color="red" title="Mandatory for private templates">*</a>BU', array(""=>"")+$locationList);
        $form->addElement(  'select', 'tplno', 'Template', array(""=>"")+$templateList);
        $form->addElement(  'select', 'cur_step', 'Step#', array(""=>"")+$stepList);
        $form->addElement(  'header', 'title', "Misc");
        $form->addElement(  'date', 'open_start', 'OPEN date from',
            array("format"=>"Ymd", 'addEmptyOption'=>TRUE, "minYear"=>date("Y")-2, "maxYear"=>date("Y")));
        $form->addElement(  'date', 'open_end', 'OPEN date to',
            array("format"=>"Ymd", 'addEmptyOption'=>TRUE, "minYear"=>date("Y")-2, "maxYear"=>date("Y")));
        $form->addElement(  'date', 'close_start', 'CLOSE date from',
            array("format"=>"Ymd", 'addEmptyOption'=>TRUE, "minYear"=>date("Y")-2, "maxYear"=>date("Y")));
        $form->addElement(  'date', 'close_end', 'CLOSE date to',
            array("format"=>"Ymd", 'addEmptyOption'=>TRUE, "minYear"=>date("Y")-2, "maxYear"=>date("Y")));
        // Rules
        $requiredFields = array('tplno');
        foreach($requiredFields as $field){
            $form->addRule($field, 'Required','required');
        }
        $form->setDefaults(array(
        	'tplno'=>$_REQUEST['tplno'],
            'bu_id'=>$user->getBUID(),
        ));
        $form->addElement(  'submit', 'btnSubmit', 'Submit');

        if(!$form->validate()){
            $body .= $form->toHTML();
            break 2;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $fieldToSearch = array(
        	'assignee','assignor','status','parent_id','bu_id','tplno','cur_step',
        	'open_start', 'open_end','close_start','close_end',"assignor_div_id"
        );
        // get Template
        $tpl = new tldSEQTpl((int)$vars['tplno']);
        // if private
        if($tpl->isPrivate() && !$user->isInGroup('gg_ADMIN')){
            $allowed = FALSE;
            // check BU
            if(empty($vars['bu_id'])){
                $DEFAULT_ERROR[] = "WARNING: For private sequence template, please confirm BU";
                $form->addRule('bu_id', 'Required','required');
                $body.= $form->toHTML();
                break 2;
            }
            $erpBu = tldLocation::getERPByID($vars['bu_id']);
            // check permissions based on group steps
            $groups = array_column($tpl->getNodes(), 'group_name', 'group_name');
            foreach($groups as $group){
                if(!$user->isInGroupLevel($group,$erpBu)) continue;
                $allowed = TRUE;
            }
            // Allowed?
            if(!$allowed){
                $DEFAULT_ERROR[] = "ERROR: You do not have permissions to search for this private sequence template in this BU";
                $body .= $form->toHTML();
                break 2;
            }
        }
        // Create constraints
        foreach($fieldToSearch as $key){
            if(empty($vars[$key])) continue;
            $raw = $vars[$key];
            // Construct constraint query
            switch($key){
            case "open_start";
                try{ $date = new DateTime(implode("-",$raw)); }
                catch(Exception $e){ continue 2; }
                $date = $date->format('Y-m-d');
                $a[] = " DATEDIFF('$date',date)<=0 ";
            break;
            case "open_end";
                try{ $date = new DateTime(implode("-",$raw)); }
                catch(Exception $e){ continue 2; }
                $date = $date->format('Y-m-d');
                $a[] = " DATEDIFF('$date',date)>=0 ";
            break;
            case "close_start";
                try{ $date = new DateTime(implode("-",$raw)); }
                catch(Exception $e){ continue 2; }
                $date = $date->format('Y-m-d');
                $a[] = " DATEDIFF('$date',dt_closed)<=0 ";
            break;
            case "close_end";
                try{ $date = new DateTime(implode("-",$raw)); }
                catch(Exception $e){ continue 2; }
                $date = $date->format('Y-m-d');
                $a[] = " DATEDIFF('$date',dt_closed)>=0 ";
            break;
            case "assignor_div_id":
                $raw = TldDatabase::escape($raw);
                $a[] = " assignor_div_id=$raw ";
            break;
            case "task":
                $raw = TldDatabase::escape($raw);
                $a[] = " $key LIKE '%$raw%' ";
            break;
            case "tplno":
                $raw = TldDatabase::escape($raw);
                if(!in_array($raw,array_keys($templateList))){
                    $DEFAULT_ERROR[] = "ERROR: Template selected unknow or restricted";
                    continue 2;
                }
                $a[] = " $key=$raw ";
            break;
            default:
                $raw = TldDatabase::escape($raw);
                if(is_numeric($raw)){
                    $a[] = " $key=$raw ";
                }elseif(is_string($raw)){
                    $a[] = " $key LIKE '$raw' ";
                }
            break;
            }
        }
        // Check constraints
        if(empty($a)){
            $DEFAULT_ERROR[]="ERROR: Not enough constraints to run a safe search...";
            $body .= $form->toHTML();
            break;
        }
        $constraints = implode("AND",$a);
        $rows = tldSEQ::byConstraints($constraints);
        $_TITLE = "Search results";
        if(count($rows)>500){
            $DEFAULT_ERROR[]="ERROR: More than 500 rows found. Please narrow your search with additional filters...";
            $body .= $form->toHTML();
            break 2;
        }
    break;
    }
case "form":
    switch($m[2]) {
        case 'quickedit':
            if (empty($y)) {
                $DEFAULT_ERROR[] = 'No sequence template selected';
                return;
            }
            $tplno = (int)$y;
            if (!in_array($tplno, tldSEQ::getQuickEditableTemplateNumber(), true)) {
                $DEFAULT_ERROR[] = 'Quick edit is not available for this sequence type';
                return;
            }
            $tplno = (int)$y;
            if (!in_array($x, ['ALL', 'LATE', 'DUE'])) {
                $x = 'ALL';
            }
            // Get template to populate the name in the title
            // The variable can't be called $template as it confuses Smarty..
            // I guess it is not so smart after all.
            $tpl = new tldSEQTpl($tplno);

            // Get Template nodes and include diffusion lists
            $nodes = tldSEQTplNode::byParent($tplno);
            foreach($nodes as &$node) {
                $node['accept_list'] = (new tldGroup($node['group_name']))->getUserlist(["smartyOptions" => true]);
                // By default if the user is in the list he's the next assignee, otherwise it is his supervisor
                // Full accept sequence next assignee logic is not implemented to limit the request to the DB
                $node['default_next_assignee'] = $user->getSupervisor();
                if (array_key_exists($user->itsId, $node['accept_list'])) {
                    $node['default_next_assignee'] = $user->getID();
                }
            }

            $constraints[] = "assignee = {$user->getId()}";
            $constraints[] = "status = 'OPEN'";
            $constraints[] = "tplno = $tplno";
            switch($x) {
                case 'LATE':
                    $constraints[] = "due_date < NOW()";
                    break;
                case 'DUE':
                    $constraints[] = "due_date >= NOW()";
                    break;
            }
            $constraints = implode(" AND ", $constraints);

            $sequences = tldSEQ::byConstraints($constraints, ['orderBy' => 'due_date ASC']);

            if(!empty($sequences)) {
                $sequences = tldSEQ::includeComments($sequences, false);

                $erps = array_unique(array_map(static function ($row) {
                    return $row['erp'];
                }, $sequences));
                $specificColumns = [];
                if (in_array((int)$tplno, [22, 78], true)) {
                    $sequences = array_map(static function (array &$sequence) {
                        $params = unserialize(base64_decode($sequence["close_params"]));
                        $sequence['task'] = "<b>Part#</b><a href=\"/en/private/manufacturing/whse/dev.php?m[0]=inv&m[1]=byPNPlanned&erp={$params['erp']}&id={$params['pn']}&btnSubmit=Submit\">{$params['pn']}</a>\n\r\n\r".$sequence['task'];
                        return $sequence;
                    }, $sequences);
                }
            }
            $overlib = $smarty->fetch('overlib.inc.js.tpl');
            $smarty->assign("html_head",$overlib);
            $smarty->assign('sequenceName', $tpl->getShortDesc());
            $smarty->assign('sequenceLastStep', count($nodes));
            $smarty->assign('seqs', $sequences);
            $smarty->assign('nodes', $nodes);
            $smarty->assign('specificColumns', $specificColumns);
            $smarty->assign('transfertUserList', tldDirectory::getUserList('smartyOptions'));
            $smarty->assign("user", $user->itsDetails);
            $smarty->assign("width", "100%");
            $body .= $smarty->fetch("$PATH/seq/edit.seq.tpl");
            break 2;
    }


    // Links here <----

    switch($out){
    default:
        $body.= _getListing($rows,$_TITLE);
    break;
    }
break;
default:
    $body .= <<<EOF
<h3>Sequence Homepage</h3>
<p>Welcome to the Sequences Homepage</p>
<p>For confidentiality reasons, the list of Most Recently Created Sequences has been removed. Thanks for your understanding.</p>
EOF;
break;
}

function _getListing($rows,$title,$xItems=""){
    global $php_self;
    if(empty($xItems)){
        $xItems = _getDefaultListingColumns();
    }
    $report = new tldReportColumnar(
        $rows,
        array(
            "xItems"=>$xItems,
            "title"=>$title,
            "links"=>array(
                "id"=>"$php_self?m[0]=tasks&m[1]=task&m[2]=view&id="
            )
        )
    );
    return $report->fetch();
}

function _getDefaultListingColumns(){
    return array(
        "id"=>"SEQ#",
        "module"=>"Module",
        "parent_id"=>"Ref#",
        "date"=>"Date Opened",
        "status"=>"Status",
        "tpl_short_desc"=>"Template",
        "assignor_fullname"=>"Assignor",
        "assignee_fullname"=>"Assignee",
        "cur_step"=>"Current Step",
        "task"=>"Description"
    );
}