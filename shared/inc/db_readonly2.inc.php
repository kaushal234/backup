<?php
//Function to create record view from given table definition array
//If parent table has related child tables then table definition
//must have a table property entry $form_array[0]["child_tables"]
//$id = id for parent record
//$form_array = parent record table definition array
//
function prn_record($id, $form_array){
global $PHP_SELF, $MY_SESS;
    $formSpec = $form_array[0];

    $result .= '<h2>'.${$formSpec["title"]}.'</h2>';
	$result .= str_replace("{id}", $id, $formSpec ["prn_record_menu"]);
	$result .=<<<EOF
	 <a href="$PHP_SELF?mode=${formSpec["cancel"]}">Previous Screen</a>
EOF;
	$result .= prn_form_view($id, $form_array);

	if (isset($formSpec ["child_tables"])){
		foreach ($formSpec ["child_tables"] as $child_table_name){
			$child_template_name = $child_table_name."_tpl";
            global $$child_template_name; // phpcs:ignore
            $child_array = $$child_template_name;
			$rows = TldDatabase::query("SELECT * FROM $child_table_name WHERE parent_id=$id");
			if(TldDatabase::numRows($rows)){
				$result .= '<b>'.$child_array[0]["title"].'</b>'.
	        				prn_list($child_array, $rows);
	        }
        }
    }

	return $result;
}

function prn_sort_controls($form_array){
	global $PHP_SELF,$MY_SESS;
    $formSpec = $form_array[0];
	$table=$form_array[0]["table"];
	$sess_sort_order=$MY_SESS[$table]["sort_order"];
	$sess_sort=$MY_SESS[$table]["sort"];
	$result .= '<b>'.$form_array[0]["title"].'</b>&nbsp;|&nbsp;';
	if(isset($formSpec["table_menu"])){
		$result .= $formSpec["table_menu"].'&nbsp;|&nbsp;';
	}
	$result .=<<<EOF
<a href="$PHP_SELF?mode=form_search&form_type=${formSpec["form_type"]}">Detailed Search</a>&nbsp;|&nbsp;
<a href="$PHP_SELF?mode=reset_all">Reset</a>&nbsp;|&nbsp;
<a href="$PHP_SELF?mode=dump_list">List All</a>
<table>
	<tr class="smalltext">
		<td>
    <form method="get" action="$PHP_SELF">
    <input type="hidden" name="mode" value="table">
    <input type="hidden" name="form_type" value="${formSpec["form_type"]}">
    <INPUT type="hidden" name="offset" value="0">
    <select name="sort">
EOF;
    for ($i=1; $i< count($form_array);$i++){
        if ($form_array[$i]["table"]=="false") {
            continue;
        }
        $result .= "<option value=\"".$form_array[$i]["name"]."\"";
		if ($form_array[$i]["name"]==$sess_sort){
			$result .= " selected";
		}
		$result .=">";
		$result .= substr($form_array[$i]["label"],0,18)."...</option>";
    }
	$result .=<<<EOF
    </select>
    <select name="sort_order">
    <option
EOF;
	if ($sess_sort_order=="ASC") {
        $result .= "\"selected\"";
    }
	$result .=">ASC</option><option ";
	if ($sess_sort_order=="DESC") {
        $result .= "\"selected\"";
    }
	$result .=<<<EOF
	>DESC</option>
    </select>
    <input type="submit" name="Submit" value="Sort">
    </form>
    	</td>
		<td>
    <form method="get" action="$PHP_SELF">
    	 | <INPUT type="hidden" name="mode" value="do_quick_search">
    	<INPUT type="text" name="search_target" size="10">
    	<INPUT type="submit" name="findit" value="Find">
      </form>
		</td>
    </tr>
</table>
EOF;
	return $result;
}

switch ($mode){
    case 'record_view':
        if ($form_type=="main_tpl"){
            $MY_SESS[$main_table]["current_id"]=$id;
        }
		if($id==0){
			$body = prn_record($MY_SESS[$main_table]["current_id"],$form_array);
		}else{
			$body = prn_record($id, $form_array);
		}
    break;
    case 'form_view':
        $body = prn_form_view($id,$form_array);
    break;
    case 'form_search':
        $body = prn_form_search($form_array);
    break;
    case 'do_search':
        //$sess_q=construct_where($form_array);
        $MY_SESS[$running]["q"]=construct_where($form_array);
        if($add_q){
			$MY_SESS[$running]["q"]=str_replace("WHERE","WHERE(",$MY_SESS[$running]["q"]);
        	$MY_SESS[$running]["q"].=") AND ".$add_q;
        }
        $body = prn_table($main_tpl,0,$max_rows);
    break;
    case 'do_quick_search':
        $MY_SESS[$running]["q"]=construct_quick_where($form_array,$search_target);
        $MY_SESS[$running]["offset"]=0;
        if($add_q){
			$MY_SESS[$running]["q"]=str_replace("WHERE","WHERE(",$MY_SESS[$running]["q"]);
        	$MY_SESS[$running]["q"].=") AND ".$add_q;
        }
		?>
        <meta http-equiv="refresh" content="0;URL=<?php echo $PHP_SELF?>?mode=table&form_type=main_tpl">
        <a href="<?php echo $PHP_SELF?>?mode=table&form_type=main_tpl">Continue</a><?php
    break;
    case 'table':
        $body = prn_table($form_array,$MY_SESS[$running]["offset"],$max_rows);
    break;
    case 'email_view':
        $body = prn_email_view($MY_SESS[$running]["current_id"],$form_array);
    break;
    case 'plain_view':
        $body = prn_email_view($MY_SESS[$running]["current_id"],$form_array,0);
    break;
    case 'email':
        $body .= "<br><font color=\"#FF0000\">".email_record($emails,$id,$form_array,$PHP_AUTH_USER,$message,$subject)."</font>";
        $body .= prn_record($id,$form_array);
    break;
    case 'dump_list':
    $body = 	prn_table($main_tpl,0,0,$MY_SESS[$running]);
	break;
    case 'reset_all':
    	$MY_SESS[$running]=array();
    	$body =<<<EOF
        <meta http-equiv="refresh" content="0;URL=$PHP_SELF?mode=table">
EOF;
    break;
    default:
        $body = prn_table($form_array,$MY_SESS[$running]["offset"],$max_rows,$MY_SESS[$running]);
}

session_start();
if(!isset($_SESSION['sess'])) {
    $_SESSION['sess'] = null;
}
$sess =& $_SESSION['sess'];

//*******************
//need to set $SMARTY_LOCATION and $DEFAULT_TEMPLATE in the including script
//*********************
if(empty($SMARTY_LOCATION) || empty($DEFAULT_TEMPLATE)){
	echo "ERROR: SMARTY_LOCATION or DEFAULT_TEMPATE not set...";
	exit;
}
$smarty = tldUtils::getSmarty($SMARTY_LOCATION);

$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

if($lang){
	$smarty->assign("lang", $lang);
}
//$smarty->debugging=true;

$php_self = $_SERVER['PHP_SELF'];

//set the following in the config file instead
//$PATH = "calendar";
$DEFAULT_TITLE = $form_array[0]["title"];
$DEFAULT_FOOTER = "TLDDB V2.0";

$smarty->assign("width", "100%");
$smarty->assign("menu",$DEFAULT_MENU.(isset($menu) ? $menu : ''));
$smarty->assign("body",$body);
$smarty->assign("footer", $DEFAULT_FOOTER);
if(empty($tpl_title)) {
    $tpl_title = $DEFAULT_TITLE;
}

$smarty->assign("title",$tpl_title);

if(empty($template)) {
    $template = $DEFAULT_TEMPLATE;
}
if($template<>"NO_TEMPLATE") {
    $smarty->display($template);
}
exit;
