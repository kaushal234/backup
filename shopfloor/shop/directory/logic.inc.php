<?php
include_once("user.inc.php");
include_once("calendar.inc.php");

$DEFAULT_TITLE .= "\ "._("Directory");
$DEFAULT_MENU .="
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href=\"$php_self?m[0]=directory\">"._("Home")."</a>&nbsp;|&nbsp;
	<a href=\"$php_self?m[0]=directory&m[1]=orgChart\">"._("Org Chart")."</a>
";

switch($m[1] ?? null){
	case 'orgChart':
		$DEFAULT_TITLE .= "\ "._("Org Chart");
		// Create Org chart for actual location
		$group = new tldGroup("role_COO",$LOCATION['erp']);
		$listUser = $group->getUserlist();
		if(empty($listUser[0]['id'])){
			$DEFAULT_ERROR[] = sprintf(_("ERROR: No COO founded for location %s"),$LOCATION['location']);
			break;
		}
		$ORG_CHART[$LOCATION['erp']] = array("id"=>$listUser[0]['id'],"erp"=>$LOCATION['erp'],"title"=>$LOCATION['location']);

// TO DELETE -------->

// BY DEFAULT VIEW ORG CHART OF ACTUAL FACTORY FOR NOW
$m[2]="view";
$erp = $LOCATION['erp'];

// <------- TO DELETE

		switch($m[2] ?? null){
			case 'view':
				if(empty($erp)){
					$DEFAULT_ERROR[] = _("ERROR: No ERP# set...");
					break;
				}
				$user = new tldUser($ORG_CHART[$erp]['id']);
				$userOrgChart = $user->getMyOrgChart($ORG_CHART[$erp]['max']);
				$body = include("$PATH/org.chart.tpl.inc.php");
			break;
			default:
				// List all Org chart
				$form = new tldHTMLList($ORG_CHART,
					array(
						"key"=>array("erp"=>"erp"),
						"value"=>array("title")
					),
					"$php_self?m[0]=directory&m[1]=orgChart&m[2]=view",
					array("title"=>_("Select Org Chart location"))
				);
				$body = $form->fetch();
			break;
		}
	break;
	case 'card':
		if(empty($id)){
			$DEFAULT_ERROR[] = _("ERROR: No id set...");
			break;
		}
		$user = new tldUser($id);
		$header = $user->getHeader();

		switch($m[2] ?? null){
			case 'outPhoto':
				$jpg = new tldFileJPG(tldUtils::getPathToUploadFile('photos', $header['photo']));
			    $jpg->outFile('','',array('width'=>$width));
			    exit;
			break;
			default:
				$body = include("$PATH/view.card.tpl.inc.php");
			break;
		}
	break;
	default:
		$body ="<h3>"._("Directory homepage")."</h3>";
		$body.="<p>"._("Welcome to the shopfloor directory homepage")."</p>";
	break;
}
