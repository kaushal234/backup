<?php
include_once("calendar.inc.php");
include_once("HTML/QuickForm.php");
require_once('HTML/QuickForm/advmultiselect.php');
include_once("forms_and_reports.inc.php");
include_once("quality.inc.php");
include_once("erp.inc.php");
$JS_INCLUDE=array("/shared/javascript/overlib/overlib.js");
$smarty->assign("js_includes",$JS_INCLUDE);
$PATH.="/cpa";
$DEFAULT_TITLE .= "\CPA";
// Get MOO ID
$moo_id = tldModule::getMOOIDByModule("CPA");

$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=cpa">CPA Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cpa&m[1]=forms&m[2]=new">Submit CPA</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cpa&m[1]=forms&m[2]=byID">By Number</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cpa&m[1]=forms&m[2]=search">Search</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cpa&m[1]=reports">Reports</a>
&nbsp;|&nbsp;<a href="/en/private/mis/mis.php?m[0]=help&m[1]=dms&id=1791">Help Page</a>
&nbsp;|&nbsp;<a href="cpa/cpa_admin.php">Maintain CPAs</a>
EOF;

switch($m[1]){
case 'forms':
	switch($m[2]){
	case 'byID':
		$body .= $smarty->fetch("$PATH/forms/form.view.cpa.tpl");
	break;
    case 'search':
        $statusList = tldCPA::getStatusList();
        $iFactorList = tldCPA::getIFactorList();
        $form = new HTML_QuickForm('frmSearchCPA', 'post');
        $form->addElement(  'header', 'title', 'Search CPA');
        $form->addElement(  'hidden', 'm[0]', 'cpa');
        $form->addElement(  'hidden', 'm[1]', 'forms');
        $form->addElement(  'hidden', 'm[2]', 'search');
        $form->addElement(  'select', 'bu', 'Factory',
            tldLocation::getERPList("smartyOptionsIDLocation"));
        $form->addElement(  'select', 'status', 'Status',
            array(""=>"")+$statusList);
        $form->addElement(  'select', 'dept', 'Department',
            array(""=>"")+tldDepartment::getListAsDepartmentDepartment());
        $form->addElement(  'select', 'ifactor', 'Importance Factor', [""=>""]+$iFactorList);
        $form->addElement(  'select', 'initiator', 'Initiator',
            array(""=>"")+tldDirectory::getUserlist("smartyOptions"));
        $form->addElement(  'select', 'type', 'Type',
            array(""=>"")+tldList::optionsByListNameAsListItemListItem('list.cpa.type'));
        $form->addElement(  'text', 'short_desc', 'Short Description', array("size"=>"50"));
        $form->addElement(  'submit', 'btnSubmit', 'Submit');

        if(!$form->validate()){
            $body = $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $a = array();
        $acl_fields = array("bu","status","dept","ifactor","initiator","type","short_desc");
        foreach($vars as $k=>$var){
            if(!in_array($k,$acl_fields) || empty($var)) continue;
            if($k=="short_desc"){
                $var = "%$var%";
            }
            $a[$k]=$var;
        }
        if(count($a)<1){
            $DEFAULT_ERROR[]="ERROR: Not enough fields set to do safe search...";
            break;
        }
        $rows = tldCPA::byConstraints($a);
        $body .= _getListing($rows,"Search results");
    break;
	case 'new':
		$form = new HTML_QuickForm('frmNewCPA', 'post');
		$form->addElement(	'header', 'title', 'Submit New CPA Form');
		$form->addElement(	'hidden', 'm[0]', 'cpa');
		$form->addElement(	'hidden', 'm[1]', 'forms');
		$form->addElement(	'hidden', 'm[2]', 'new');
		$form->addElement(  'select', 'initiator', 'Initiator',
		    tldDirectory::getUserlist("smartyOptions"));
        $form->addElement(  'select', 'bu', 'Factory',
	            array(""=>"")+tldLocation::getERPList("smartyOptionsIDLocation"));
        $form->addElement(	'select', 'ifactor', 'Importance Factor', tldCPA::getIFactorList());
        $ifDef=<<<EOF
<ul>
<li><b>IF = 1</b><br>
1. MINOR process<br>
&nbsp;&nbsp;a. MINOR problem and NO REAL impact on the process<br>
&nbsp;&nbsp;b. Or SIMPLE process improvement<br>
</li>
<li>
<b>IF = 10</b><br>
1. MINOR process<br>
&nbsp;&nbsp;a. OCCASIONAL problem with REAL impact on the process<br>
&nbsp;&nbsp;b. Or SIGNIFICANT process improvement<br>
2. MAJOR process<br>
&nbsp;&nbsp;a. MINOR or OCCASIONNAL problem with OCCASIONAL impact on the process<br>
</li>
<li>
<b>IF = 100</b><br>
1. MINOR process<br>
&nbsp;&nbsp;a. CONSTANT problem that PREVENTS the process from working properly<br>
2. MAJOR process<br>
&nbsp;&nbsp;a. MINOR or OCCASIONNAL problem with REAL impact on the process<br>
&nbsp;&nbsp;b. Or process improvement<br>
3. REMARK/OPPORTUNITY of an ISO or internal Audit.<br>
</li>
<li>
<b>IF = 1000</b><br>
1. MAJOR process<br>
&nbsp;&nbsp;a. CONSTANT problem that PREVENTS the process from working properly<br>
2. NON-CONFORMITY of an ISO or internal Audit.<br>
</li>
</ul>
EOF;
        $popupDef = new tldOverlib($ifDef, array("CAPTION"=>"Importance Factor Definition","WIDTH"=>"500","linkName"=>"Importance Factor Definition"));
        $form->addElement(	'static', null, null,$popupDef->fetch());
        $form->addElement(  'select', 'dept', 'Department',
            array(""=>"")+tldDepartment::getListAsDepartmentDepartment());
        $form->addElement(  'select', 'type', 'Type',
            array(""=>"")+tldList::optionsByListNameAsListItemListItem('list.cpa.type'));
        $form->addElement(	'text', 'short_desc', 'Short Description (English only)',
            array("size"=>"50"));
        $form->addElement(	'textarea', 'description', 'Full Description',
            array("wrap"=>"VIRTUAL", "cols"=>"40", "rows"=>"8"));
        $form->addElement(	'file', 'picture_filename', 'Picture');
		$form->addElement(	'submit', 'btnSubmit', 'Submit');
		// Required
		$requiredFields = array('initiator','bu','ifactor','dept','type','short_desc','description');
		foreach($requiredFields as $field){
		    $form->addRule($field, 'This is required', 'required');
		}
		$form->setDefaults(array("initiator"=>$user->getID(),"bu"=>$user->getBUID()));

		if(!$form->validate()){
            $body = $form->toHTML();
            break;
        }

		$header = $form->exportValues();
		// Process the uploaded file if any
		$file = $form->getElement("picture_filename");
		if($file->isUploadedFile()){
		    $attr = $file->getValue();
		    $destPath = tldCPA::getPathToUploadFile();
		    // Create file name and check if not existing already
		    $filepath = $destPath.time().$attr["name"];
		    while(file_exists($filepath)){
		        $filepath = $destPath.time().$attr["name"];
		    }
		    $header["picture_filename"] = basicFile::cleanupName(basename($filepath));
		    $file->moveUploadedFile($destPath, $header["picture_filename"]);
		}
		// Create CPA
		$header["poster"] = $user->getID();
		$error = tldCPA::insertHeader($header);
		if(is_string($error)){
		    $DEFAULT_ERROR[]="INTERNAL ERROR: Could not create new CPA. Reason: $error";
            break;
        }
		$cpa = new tldCPA($error);
		$short_desc = $cpa->getShortDesc();
		// Log
		$cpa->addLogEntry($user->getID(), "CPA created");
		// Notification
		$msg =<<<EOF
A new PENDING CPA has been submited.<br>
EOF;
        $cpa->notifyInitiator($msg, "New CPA#$error has been opened");
		$body = $msg."<br><a href='https://www.tld-gse.com/en/private/manufacturing/qa/dev.php?m[0]=cpa&m[1]=view&id=$error'>";
	break;
	}
break;
case 'view':
	include("cpa.view.inc.php");
break;
case 'reports':
	$DEFAULT_TITLE .= "\Reports";
	switch($m[2]){
	case 'statusByLocation':
		$form = new tldMatrix(
            tldCPA::countBy('status','location'),
			"status", "location", "num",
			"$php_self?m[0]=cpa&m[1]=reports&m[2]=list&m[3]=byFactoryStatus",
			"PDC Count by Status, Business Unit"
		);
		$body .= $form->fetch();
	break;
	case 'countByDeptStatus':
		$form = new tldMatrix(
            tldCPA::countBy('status','dept'),
			"status", "dept", "num",
			"$php_self?m[0]=cpa&m[1]=reports&m[2]=list&m[3]=byDeptStatus",
			"CPA Count by Department and Status"
		);
		$body .= $form->fetch();
	break;
		case 'list':
			switch($m[3]){
            case 'byFactoryOpenStatusWithNoOpenTasks':
                $rows = tldCPA::byERPStatusWithoutTask($y, $x);
            break;
			case 'byDeptStatus':
				$rows = tldCPA::byQuery(array("dept"=>$y, "status"=>$x));
			break;
			case 'byFactoryStatus':
				$rows = tldCPA::byERPStatus($y, $x);
			break;
			case 'byDeptStatusLocation':
			    // Listing
			    $statusList = array_combine(tldCPA::getStatusList(),tldCPA::getStatusList());
			    // Form
				$form = new HTML_QuickForm('frm', 'get','','','',true);
				$form->addElement(	'hidden', 'm[0]', 'cpa');
				$form->addElement(	'hidden', 'm[1]', 'reports');
				$form->addElement(	'hidden', 'm[2]', 'list');
                $form->addElement('hidden', 'm[3]', 'byDeptStatusLocation');
                $factories = ["" => ""] + tldUtils::getSqlToAssocArray("SELECT id,location FROM locations WHERE factory='Y' OR hq='Y' ORDER BY location", "smartyOptions", ['id', 'location']);
                $form->addElement('select', 'bu', 'Business Unit', $factories);
                $form->addElement('select', 'dept', 'Department', array_merge(["" => ""], tldDepartment::getListAsDepartmentDepartment()));
                $form->addElement('select', 'status', 'Status', ["ALL" => "ALL"] + $statusList);
                $form->addElement('submit', 'btnSubmit', 'Submit');

                if ($form->validate()){
					# If the form validates then freeze the data
					$form->freeze();
					if($dept){
						$_WHERE["dept"]=$dept;
					}
					if($status and $status<>"ALL"){
						$_WHERE["status"]=$status;
					}
					if($bu){
						$_WHERE["bu"]=$bu;
					}
					$rows = tldCPA::byQuery($_WHERE);
				}else{
					$body .= $form->toHTML();
				}
			break;
			}
			if($rows){
				$sess["cpa"]["list"] = $rows;
				$cols = array("id"=>"CPA #",
					"ifactor"=>"IF",
					"status"=>"Status",
					"fullname"=>"Project Leader",
					"date"=>"Date",
					"location"=>"Location",
					"dept"=>"Department",
					"type"=>"Type",
					"short_desc"=>"Short Desc",
					"root_cause"=>"Final Root Cause",
					"corrective_action"=>"Corrective Action",
					"date_closed"=>"Closed At"
					);
				if($csv){
					$template = "NO_TEMPLATE";
					$report = new tldCSV($rows, array("xItems"=>$cols,
										"showTitles"=>true)
										);
					$report->out();
				}else{
					$DEFAULT_MENU .= "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"dev.php?_qf__frm=&m[0]=cpa&m[1]=reports&m[2]=list&m[3]=byDeptStatusLocation&csv=1&bu=$bu&dept=$dept&status=$status&btnSubmit=Submit\">CSV Version</a>";
					$report = new tldReportMultiLevel($rows,
								array("location","status","dept"),
								$cols,
								array("passField"=>"id",
								"title"=>"CPAs",
								"url"=>"$php_self?m[0]=cpa&m[1]=view&id=")
								);
					$body .= $report->fetch();
				}
			}
		break;
		default:
			$body = $smarty->fetch("$PATH/reports/homepage.reports.tpl");
        break;
		}
break;
case 'gantt':
    $DEFAULT_TITLE .= "\Gantt";
    $overlib = $smarty->fetch('overlib.inc.js.tpl');
    $overlib .= '<script type="text/javascript">$(function(){$("[data-body]").overlib()});</script>';
    $smarty->assign("html_head",$overlib);
    $smarty->assign("width", "100%");
    $title = [];
    if($opt=="ALL ACTIVE"){
        $title[]="Status: All Active";
        $rows = _getGanttData('ALL ACTIVE',null,null);
    }else{
        $rows = _getGanttData(TldDatabase::escape($x),TldDatabase::escape($y),TldDatabase::escape($dept));
        if(!empty($x) AND $x!='ALL')$title[]="Status: $x";
        if(!empty($y) AND $y!='ALL')$title[]="Factory: $y";
		if(!empty($dept) AND $dept!='ALL')$title[]="Department: $dept";
    }
    foreach($rows AS &$row){
        $row['cpa_log_html']=str_replace("\n","&lt;br/&gt;&lt;br/&gt;",$row['cpa_log']);
    }
    $title = (!empty($title))?"by ".implode(" & ",$title):"";
	$dptList = tldDepartment::getListAsDepartmentDepartment();
	$cpaStatuses = tldCPA::getStatusList();
	$locations = tldLocation::getBuListAsIdBU();
	$locations = array_combine(array_values($locations), array_values($locations));

	$frmSwitch = new HTML_QuickForm('frmSwitch', 'post', '', '', ['id' => 'frmSwitch']);
	$frmSwitch->addElement('hidden', 'm[0]', 'cpa');
	$frmSwitch->addElement('hidden', 'm[1]', 'gantt');
    $frmSwitch->addElement('header', 'header', 'Department Switch');
	$frmSwitch->addElement('select', 'dept', 'Department', ["" => ""] + $dptList, ['onChange' => "javascript:$('#frmSwitch').submit();"]);
    $frmSwitch->addElement('header', 'header', 'Status Switch');
    $frmSwitch->addElement('select', 'x', 'Status', ['' => '', 'ALL ACTIVE' => 'ALL ACTIVE'] + $cpaStatuses, ['onChange' => "javascript:$('#frmSwitch').submit();"]);
    $frmSwitch->addElement('header', 'header', 'BU Switch');
    $frmSwitch->addElement('select', 'y', 'BU', ["" => ""] + $locations, ['onChange' => "javascript:$('#frmSwitch').submit();"]);
    $form = new tldGanttChart('CPA', $rows, array(
        // xItems
        'id'=>'ID',
        'factory_fullname'=>'Factory',
        'ifactor'=>'IF',
        'status'=>'Status',
        'proj_leader_fullname'=>'Project Leader',
        'short_desc'=>'Description',
        'date_target'=>'Targeted date',
        'task_open'=>'All tasks closed ?',
        'cpa_log_html'=>'Log',
        'date'=>'TARGET CLOSURE PLANNING',
    ), array(
        // Options
        'title'=>"CPA Gantt Chart $title",
        'parentAttributes'=>array(
            'table'=>'border="0"',
        ),
        'sortable'=>array('id','factory_fullname','ifactor','status'),
        'links'=>array(
            'id'=>"$php_self?m[0]=cpa&m[1]=view&id="
        ),
        'groupAttributes' => array(
            'factory_fullname' => array(
                // EUR
                'TLD MTL'=>'style="font-weight:bold;background:#369;border:solid 1px #369;color:#fff;"',
                'TLD STL'=>'style="font-weight:bold;background:#fff;border:solid 1px #369;color:#369;"',

                // ASI
                'TLD SHA'=>'style="font-weight:bold;background:#090;border:solid 1px #090;color:#fff;"',
                'TLD WUX'=>'style="font-weight:bold;background:#fff;border:solid 1px #090;color:#090;"',

                // AME
                'TLD WIN'=>'style="font-weight:bold;background:#666;border:solid 1px #666;color:#fff;"',
                'TLD SHE'=>'style="font-weight:bold;background:#fff;border:solid 1px #666;color:#;666"',

                // Default
                'style="font-weight:bold;background:#609;border:solid 1px #609;color:#fff;"',
            ),
        ),
        'columnSettings'=>array(
            'id'=>array(
                'callback'=>'intval',
                'title'=>'ID #%d',
            ),
            'ifactor'=>array(
                'type'=>'ifactor',
            ),
            'short_desc'=>array(
                'type'=>'description',
            ),
            'cpa_log_html'=>array(
                'icon'=>'log',
                'useIconValue' => true,
                'cellAttributes' => 'data-body="%s"',
                'tdTitle'=>'cpa_log',
                'imgTitle' => 'cpa_log',
            ),
            'date'=>array(
                'type'=>'gantt',
                'zoom'=>'month',
                'rangeBack' => 3,
                'rangeForward' => 11,
                'subgroup'=>true,
                'subgroupDateFormat'=>'o',
                'ticDateFormat'=>'M',
                'displayCallback'=>function($line, $a, $b){
                    if ($line['date_target'] && ($time = strtotime($line['date_target']))):
                    if ($time >= $a AND $time <= $b):
                    $display = "<span style='color:red;'>X</span>";
                    endif;
                    endif;
                    return (!empty($display)) ? $display : '&nbsp;';
                },
            )
        ),
    ));
	$body .= $frmSwitch->toHTML();
    $body .= '<br />'.$form->fetch();
break;
default:
		$body = $smarty->fetch("$PATH/homepage.cpa.tpl");
		$body .= "<a href=\"$php_self?m[0]=cpa&m[1]=gantt&opt=ALL ACTIVE\">View All ACTIVE CPA</a></p>";
		$rows = tldCPA::countBy("status","location");
		$xItems = array("PENDING", "INVESTIGATION", "ACTION", "SUSPENDED", "REJECTED", "CLOSED");
		if(count($rows)){
			$form = new tldMatrix(
                $rows,
				"status", "location", "num",
				"$php_self?m[0]=cpa&m[1]=gantt",
				"CPA Count by Status, Factory",
			    array(
			        "xItems"=>$xItems,
			        "style"=>array(
			            "xItems"=>array()
			        )
			    )
			);
			$body .= $form->fetch();
		}
        $form = new tldMatrix(
            tldCPA::withoutOpenTask(),
            "status", "factory_fullname", "num",
            "$php_self?m[0]=cpa&m[1]=reports&m[2]=list&m[3]=byFactoryOpenStatusWithNoOpenTasks",
            "Open CPA by Factory, Status, with no open tasks",
            [
                "xItems"=>tldCPA::getOpenStatusList()
            ]
        );
        $body .= $form->fetch();
		$sess["cpa"]["list"] = tldCPA::byLatest();
		$report = new tldReportColumnar(
            $sess["cpa"]["list"],
			array(
                "xItems"=>array(
                    "id"=>"CPA #",
                    "ifactor"=>"IF",
					"status"=>"Status",
					"date"=>"Date",
					"location"=>"Factory",
					"dept"=>"Department",
					"type"=>"Type",
					"short_desc"=>"Short Desc"
				),
				"links"=>array("id"=>"$php_self?m[0]=cpa&m[1]=view&id="),
				"title"=>"Recently Added CPAs"
			)
		);
		$body .= $report->fetch();
break;
}

function _getGanttData($status="", $factory="", $dept=""){
   	$factory=strtoupper($factory);
   	$HAVING ='';
   	if(!empty($factory) && $factory<>"ALL"){
   	    $WHERE[]="erp.location='$factory'";
   	}
    if(!empty($dept) && $dept<>"ALL"){
		$WHERE[]="cpa.dept='$dept'";
    }
   	if(!empty($status) && !in_array($status, ['ALL', 'ALL ACTIVE'], true)){
   	    $WHERE[]="cpa.status='$status'";
   	}
   	if($status === "ALL ACTIVE"){
   	    $WHERE[]="cpa.status IN ('PENDING','INVESTIGATION','ACTION','SUSPENDED')";
   	}
   	if(!empty($WHERE)){
   	    $WHERE="WHERE ".implode(" AND ", $WHERE);
   	}
   	$query=<<<EOF
SELECT
	cpa.*,
	erp.location AS factory_fullname,
	erp.erp AS factory_erp,
    (SELECT CONCAT(firstname, ' ',lastname) FROM people WHERE people.id = cpa.proj_leader) AS proj_leader_fullname,
	(SELECT GROUP_CONCAT(CONCAT(date,' - ',comment) ORDER BY mod_logs.id DESC SEPARATOR "\n")
	FROM mod_logs
	WHERE mod_logs.module='CPA'
	AND mod_logs.parent_id=cpa.id
	AND mod_logs.log_num=0
	GROUP BY mod_logs.parent_id
	LIMIT 10) AS cpa_log,
    IF ((SELECT COUNT(*) FROM tasks WHERE tasks.module = 'CPA' AND status<>'CLOSED' AND cpa.id = tasks.parent_id) >= 1,
        'NO',
        'YES'         
    ) AS task_open
FROM
	cpa
	LEFT JOIN locations AS erp ON cpa.bu=erp.id
$WHERE
$HAVING
ORDER BY
	cpa.ifactor DESC,
	cpa.date DESC,
	cpa.id DESC
EOF;

   	$rows = tldUtils::getSqlToAssocArray($query);
   	return $rows;
}

function _getListing($rows,$caption){
    global $php_self;
    $report = new tldReportColumnar(
        $rows,
        array(
            "xItems"=>array(
                "id"=>"CPA #",
                "ifactor"=>"IF",
                "status"=>"Status",
                "date"=>"Date",
                "location"=>"Factory",
                "dept"=>"Department",
                "type"=>"Type",
                "short_desc"=>"Short Desc"
            ),
            "links"=>array("id"=>"$php_self?m[0]=cpa&m[1]=view&id="),
            "title"=>$caption
        )
    );
    return $report->fetch();
}

function _getGeneralPage(){
    global $cpa,$smarty,$PATH;
    $cpa->refresh();
    $view = NULL;
    // Display Next/prev links
    $smarty->assign('cpa',$cpa->itsHeader);
    $view = $smarty->fetch("$PATH/view/cpa.menu.inc.tpl");
    // Template view
    $view.= include("view/cpa.view.tpl.php");
    // Return the complete view
    return $view;
}

function _generateChangeListLog($fields, $DataBefore, $DataAfter){
    $list = array();
    foreach($fields as $field=>$label){
        if(!isset($DataBefore[$field]) || !isset($DataAfter[$field])) continue;
        if($DataBefore[$field]==$DataAfter[$field]) continue;
        $list[]="<li>$label <b>from</b> {$DataBefore[$field]} <b>to</b> {$DataAfter[$field]}</li>";
    }
    // If nothing changed
    if(empty($list)) return FALSE;
    return "<ul>".implode("",$list)."</ul>";
}
