<?php
$JS_INCLUDE=array("/shared/javascript/overlib/overlib.js");
$smarty->assign("js_includes",$JS_INCLUDE);
include_once("kpi.common.inc.php");

$kpireview = $smarty->fetch("$INTRA_PATH/common/kpireview/kpireview.inc.js.tpl");
$kpireview .= '<script type="text/javascript">$(function(){$("img.kpireview").kpireview()});</script>';
$smarty->assign("html_head",$kpireview);

$DEFAULT_TITLE .= "/KPI";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=kpi">Home</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=kpi&m[1]=history">History</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=kpi&m[1]=help">Help</a>&nbsp;|&nbsp;
<a href="kpi/kpi_admin.php">KPI Admin</a>
EOF;

switch($m[1]){
case 'buid':
    if(empty($buid)){
        $DEFAULT_ERROR[] = "ERROR: BU ID not set...";
        break;
    }
    switch($m[3]){
    case 'history':
        $graphType="history";
        if(isset($_REQUEST['period'])){
            // change the dates to the correct format as otherwise it retur the month like this '5' instead of '05'
            $sess['mfg']['kpi']['period']['ds'] = date("Y-m",
                mktime(0, 0, 0,
                    $_REQUEST["period"]["date_start"]["m"], 1, $_REQUEST["period"]["date_start"]["Y"]
                )
            );
            $sess['mfg']['kpi']['period']['de'] = date("Y-m",
                mktime(0, 0, 0,
                    $_REQUEST["period"]["date_end"]["m"], 1, $_REQUEST["period"]["date_end"]["Y"]
                )
            );
        }
        // Check that end date not before start date
        $start = new DateTime($sess['mfg']['kpi']['period']['ds']."-01");
        $end = new DateTime($sess['mfg']['kpi']['period']['de']."-01");
        if($start > $end){
            $DEFAULT_ERROR[]="ERROR: Start date {$start->format('Y-m')} can not be greater than End date {$end->format('Y-m')}";
            break 2;
        }
    break;
    case 'past12':
    default:
    	$graphType="past12";
    break;
    }
    $KPIMENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=kpi&m[1]=buid&m[2]=delivery&m[3]=${m[3]}&buid=$buid">Deliveries</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=kpi&m[1]=buid&m[2]=inventory&m[3]=${m[3]}&buid=$buid">Inventory</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=kpi&m[1]=buid&m[2]=production&m[3]=${m[3]}&buid=$buid">Production</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=kpi&m[1]=buid&m[2]=quality&m[3]=${m[3]}&buid=$buid">Quality</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=kpi&m[1]=buid&m[2]=vendors&m[3]=${m[3]}&buid=$buid">Vendors</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=kpi&m[1]=buid&m[2]=engineering&m[3]=${m[3]}&buid=$buid">Engineering</a>
EOF;
    $DEFAULT_MENU .= $KPIMENU;
    if($buid <> "ALL"){
        if(!is_numeric($buid)){
            $DEFAULT_ERROR[] = "ERROR: BU ID not valid";
            break;
        }
		$loc = new tldLocation($buid);
		$location = $loc->getHeader();
	}else{
		$location['location']='ALL';
	}

	$DEFAULT_TITLE .= "/${location['location']}";
    switch($m[2]){
    case 'inventory':
        $DEFAULT_TITLE .= "/Inventory";
        include("kpi/inventory.inc.php");
    break;
    case 'production':
        $DEFAULT_TITLE .= "/Production";
        include("kpi/production.inc.php");
    break;
    case 'quality':
        $DEFAULT_TITLE .= "/Quality";
        include("kpi/quality.inc.php");
    break;
    case 'vendors':
        $DEFAULT_TITLE .= "/Vendors";
        include("kpi/vendors.inc.php");
    break;
    case 'engineering':
        $DEFAULT_TITLE .= "/Engineering";
        include("kpi/engineering.inc.php");
    break;
    default:
    case 'delivery':
        $DEFAULT_ERROR[] = "INFO: Delivery KPIs has been migrated ! <a href=\"$php_self?m[0]=kpi2\">Click here to view delivery KPIs</a>";
        $DEFAULT_TITLE .= "/Delivery";
        include("kpi/delivery.inc.php");
    break;
    }
    $body .= "<br><br><br><br>".$KPIMENU;
break;
case 'history':
    $DEFAULT_TITLE .= "\History";
    // Get list of factories
    $factories = array(""=>"","ALL"=>"Benchmark ALL")+tldLocation::getFactoryList('smartyOptionsIDLocation');
    // Construct form
    $form = new HTML_QuickForm('frmHistory');
    $form->addElement(	'hidden', 'm[0]', 'kpi');
    $form->addElement(	'hidden', 'm[1]', 'buid');
    $form->addElement(	'hidden', 'm[3]', 'history');
    $form->addElement(	'header', 'title', "Get KPI Per Period");
    $form->addElement(	'select', 'buid', 'Factory', $factories);
    $groupPeriod[] =& $form->createElement(
        'date', 'date_start', 'Start',
        array(
            "format"=>"Y-m",
            "minYear"=>date("Y")-3,
            "maxYear"=>date("Y")+5
        )
    );
    $groupPeriod[] =& $form->createElement(
        'date', 'date_end', 'End',
        array(
            "format"=>"Y-m",
            "minYear"=>date("Y")-3,
            "maxYear"=>date("Y")+5
        )
    );
    $form->addGroup($groupPeriod, 'period', 'Start: ', 'End: ');
    $form->addElement('submit', 'btnSubmit', 'Submit');
    // set the default values for the form:
    // set the end_date as today
    // set start_date as the same day and month but last year.
    $form->setDefaults(
        array(
            'period'=>array(
                'date_start'=>array(
                    'Y'=>date('Y')-1,
                    'm'=>date('m')
                ),
                'date_end'=>array(
                    'Y'=>date('Y'),
                    'm'=>date('m')
                )
            )
        )
    );
    // define the rules that will apply to the form
    //setup a custom rule to check the Period dates using the local function _checkPeriodDates()
    $form::registerRule('checkPeriodDates', 'callback', '_checkPeriodDates');
    $form->addRule('period', 'incorrect date(s)-> check if enterred dates exist and that Start Date < or =  End Date', 'checkPeriodDates');
    $form->addRule('factories', 'This is required', 'required');
    $body = $form->toHTML();
break;
case 'help':
    $DEFAULT_TITLE .= "\Help";
    $ct=0;
    foreach($help as $helpTitle=>$helpText){
        if($helpTitle!="GPTarget" && $helpTitle!="BUTarget" && $helpTitle!="KPI Help Page"){
            if($ct==0){
                $ct=1;
                $helpBody .= <<<EOF
<h3>$helpTitle</h3>
<p>$helpText</p>
EOF;
            }elseif($ct==1){
                $ct=0;
                $helpBody .= <<<EOF
<b>$helpTitle</b>: $helpText<br>
EOF;
            }
        }elseif($helpTitle=="KPI Help Page"){
            $helpBody .= <<<EOF
<h3>$helpTitle</h3>
    <a href="kpi/help/helppageKPIadmin.xlsx">
			<img src="/shared/icons/excel-icon.gif" height="20" width="20"><b>KPI Admin Help</b></a>
<p>$helpText</p>
EOF;
        }
    }
    $body = $helpBody;
break;
case 'WCdata':
    $manufacturerLocation = TldDatabase::escape($_REQUEST['manufacturerLocation']);
    $location = new tldLocation($manufacturerLocation);
    $rows = tldWC::getDataOfWcKpi($location->getBuName());
    $report = new tldXLS(
        $rows,
        [
            'xItems' => [
                'serial_number' => 'Serial Number',
                'man_location' => 'Manufacturer Location',
                'date_shipped' => 'Date Shipped',
                'model' => 'Model',
                'type' => 'Type',
                'warranty_id' => 'Warranty ID',
                'warranty_status' => 'Warranty Status',
                'claim_date' => 'Warranty Claim Date',
                'toc_activity' => 'TOC activity',
                'lastComment' => 'Last Comment'
            ],
            'showTitles' => true
        ]
    );
    $report->out();
    exit;
break;
default:
    // Get list of factories
    $factories = tldLocation::byConstraints(
        "erp<>'' AND ((factory='Y' AND hidden<>1) OR id=3) AND disable<>1"
    );
	$factories[]=array("location"=>"Benchmark All","id"=>"ALL");
    // List them
	$form = new tldHTMLList(
        $factories,
         array(
             "key"=>array(
                 "buid"=>"id"
             ),
            "value"=>array(
                "location"
            )
         ),
         "$php_self?m[0]=kpi&m[1]=buid&graphType=past12",
         array(
             "title"=>"Please select Factory"
         )
     );
	$body = $form->fetch();
}

// local function used to check the dates inputed in the "new ST" from
function _checkPeriodDates($dt){
	// if start and end date are ok then return true
	if(checkdate($dt["date_start"]["m"],1,$dt["date_start"]["Y"])
	&& checkdate($dt["date_end"]["m"],1,$dt["date_end"]["Y"])
	&& (mktime(0,0,0,$dt["date_end"]["m"],1,$dt["date_end"]["Y"])
		>=mktime(0,0,0,$dt["date_start"]["m"],1,$dt["date_start"]["Y"].""))){
		return true;
	}else return false;
}
?>
