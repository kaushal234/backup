<?php
//Function to create record view from given table definition array
//If parent table has related child tables then table definition
//must have a table property entry $form_array[0]["child_tables"]
//$id = id for parent record
//$form_array = parent record table definition array
//
function prn_record($id,$form_array){
global $PHP_SELF,$MY_SESS;
    $result = TldDatabase::query("SELECT * FROM ".$form_array[0]["table"]." WHERE id=".$id);
//    echo "SELECT * FROM ".$form_array[0]["table"]." WHERE id=".$id;
    ?>
    <h2><?php echo $form_array[0]["title"]?></h2>
	<?php echo str_replace("{id}",$id,$form_array[0]["prn_record_menu"])?>
	<a href="<?php echo $PHP_SELF?>?mode=plain_view&form_type=<?php echo $form_type?>&id=<?php echo $id?>" target="blank">Print Version</a>
	 | <a href="<?php echo $PHP_SELF?>?mode=<?php echo $form_array[0]["cancel"]?>">Previous Screen</a>
	<?php
    prn_form_view($id,$form_array);
    if (isset($form_array[0]["child_tables"])){
        foreach ($form_array[0]["child_tables"] as $child_table_name){
            $child_template_name = $child_table_name."_tpl";
            global $$child_template_name; // phpcs:ignore
            $child_array=$$child_template_name;
			$result = TldDatabase::query("SELECT * FROM $child_table_name WHERE parent_id='$id'");
			if(TldDatabase::numRows($result)){
				?>
	        	<b><?php echo $child_array[0]["title"]?></b><?php
	        	prn_list($child_array,$result);
	        }
        }
    }
}

function prn_sort_controls($form_array){
global $PHP_SELF,$MY_SESS;
$table=$form_array[0]["table"];
$sess_sort_order=$MY_SESS[$table]["sort_order"];
$sess_sort=$MY_SESS[$table]["sort"];
?>
<b><?php echo $form_array[0]["title"];?></b> |
<?php
if(isset($form_array[0]["table_menu"])){
	echo $form_array[0]["table_menu"]."&nbsp;|&nbsp;";
}
?>
<a href="<?php echo $PHP_SELF?>?mode=form_search&form_type=<?php echo $form_array[0]["form_type"]?>">Detailed Search</a> |
<a href="<?php echo $PHP_SELF?>?mode=reset_all">Reset</a> |
<a href="<?php echo $PHP_SELF?>?mode=dump_list">List All</a>
<table>
	<tr class="smalltext">
		<td>
    <form method="get" action="<?php echo $PHP_SELF?>">
    <input type="hidden" name="mode" value="table">
    <input type="hidden" name="form_type" value="<?php echo $form_array[0]["form_type"]?>">
    <INPUT type="hidden" name="offset" value="0">
    <select name="sort"><?php
    for ($i=1; $i< count($form_array);$i++){
        if ($form_array[$i]["table"]=="false")continue;?>
        <option value="<?php echo $form_array[$i]["name"]?>" <?php if ($form_array[$i]["name"]==$sess_sort){echo "selected";}?>><?php echo substr($form_array[$i]["label"],0,18)."...";?></option><?php
    }?>
    </select>
    <select name="sort_order">
    <option <?php if ($sess_sort_order=="ASC")echo "selected"?>>ASC</option>
    <option <?php if ($sess_sort_order=="DESC")echo "selected"?>>DESC</option>
    </select>
    <input type="submit" name="Submit" value="Sort">
    </form>
    	</td>
		<td>
    <form method="get" action="<?php echo $PHP_SELF?>">
    	 | <INPUT type="hidden" name="mode" value="do_quick_search">
    	<INPUT type="text" name="search_target" size="10">
    	<INPUT type="submit" name="findit" value="Find">
      </form>
		</td>
    </tr>
</table><?php
}
$main_table=$main_tpl[0]["table"];

switch ($mode){
    case 'record_view':
        if (isset($id)) $MY_SESS[$running]["current_id"]=$id;
        prn_record($MY_SESS[$running]["current_id"],$form_array);
    break;
    case 'form_view':
        prn_form_view($id,$form_array);
    break;
    case 'form_search':
        prn_form_search($form_array);
    break;
    case 'do_search':
        //$sess_q=construct_where($form_array);
        $MY_SESS[$running]["q"]=construct_where($form_array);
        if($add_q){
			$MY_SESS[$running]["q"]=str_replace("WHERE","WHERE(",$MY_SESS[$running]["q"]);
        	$MY_SESS[$running]["q"].=") AND ".$add_q;
        }
        prn_table($main_tpl,0,$max_rows);
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
	case 'do_related_search':
		$search_target=str_replace(' ','%',$search_target);
		$MY_SESS[$running]["adv_search_query"]="SELECT distinct $main_table.id,$main_table.* FROM $related_table,$main_table WHERE (";
		$related_table_tpl=$related_table."_tpl";
		$MY_SESS[$running]["adv_search_query"].=prepend_table_name($$related_table_tpl,$search_target);
		$MY_SESS[$running]["adv_search_query"]=$MY_SESS[$running]["adv_search_query"].") AND $main_table.id=$related_table.parent_id";
		//echo "query=".$MY_SESS[$running]["adv_search_query"];
        prn_table($main_tpl,0,$max_rows);
	break;
    case 'table':
        prn_table($form_array,$MY_SESS[$running]["offset"],$max_rows);
    break;
    case 'email_view':
        prn_email_view($MY_SESS[$running]["current_id"],$form_array);
    break;
    case 'plain_view':
        prn_email_view($MY_SESS[$running]["current_id"],$form_array,0);
    break;
    case 'email':
        email_record($PHP_AUTH_USER,$MY_SESS[$running]["current_id"],$form_array);
    break;
    case 'dump_list':
    	prn_table($main_tpl,0,0,$MY_SESS[$running]);
	break;
    case 'reset_all':
    	$MY_SESS[$running]=array();
    	?>
        <meta http-equiv="refresh" content="0;URL=<?php echo $PHP_SELF?>?mode=table"><?php
    break;
    default:
        prn_table($form_array,$MY_SESS[$running]["offset"],$max_rows,$MY_SESS[$running]);
}
