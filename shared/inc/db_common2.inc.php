<?php
/**
 *	Database related classes
 *
 * @package Database
* @desc All classes related to ERP connectivity are kept in this file
* @access public
* @author Graham K.L. Fong <graham.fong@tld-america.com>
* @copyright TLD
*/

function prn_icon($iconName,$alt=""){
	$iconPath="/shared/bluesphere/";
	$iconSize="16x16/";
	$icons=array("start"=>		array("src"=>"actions/player_start.png",	"alt"=>"Go to first"),
				"previous"=>	array("src"=>"actions/player_rew.png",		"alt"=>"Go to previous"),
				"jump"=>		array("src"=>"actions/player_play.png",		"alt"=>"Jump"),
				"next"=>		array("src"=>"actions/player_fwd.png",		"alt"=>"Go to next"),
				"end"=>			array("src"=>"actions/player_end.png",		"alt"=>"Go to last"),
				"display"=>			array("src"=>"actions/viewmag.png",		"alt"=>"Display record")
	);
	$result = "<img src=\"".$iconPath.$iconSize.$icons[$iconName]["src"]."\" alt=\"";
	if($alt){
		$result .= $alt;
	}else{
		$result .= $icons[$iconName]["alt"];
	}
	$result .= "\">";
	return $result;
}

//Function to print FIRST,PREV,1,2,ETC,NEXT,LAST buttons for navigating a list
//Layout of buttons dependant on length of list
//Argument definitions
//$offset = int, position within list
//$total_records = int, sum of all records within FOUND SET
//$max_rows = int, maximum number of rows to print per page
//$mode = "table","", controls next mode to enter
//
function prn_nav($offset,$total_records,$max_rows) {
    global $PHP_SELF,$toolbar_dir;

    if ($offset>0){
    	$result .= "<a href=\"$PHP_SELF?mode=table&offset=0\">".
    	prn_icon("start")."</a>";
    }
    //Print Previous page button
    if (($offset-$max_rows)>=0){
		$result .= "<a href=\"$PHP_SELF?mode=table&offset=".($offset-$max_rows)."\">".
		prn_icon("previous")."</a>";
    }elseif($offset>0 && $offset<$max_rows){
    	$result .= "<a href=\"$PHP_SELF?mode=table&offset=".($offset-$max_rows)."\">".
    	prn_icon("previous")."</a>";
    }
	$result .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    $result .= "$offset/$total_records";
	$result .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    //Print quick access number buttons
/*    if ($total_records > $max_rows*10){
        //for long lists divide by 10
        for ($i=1;$i<10;$i++){
            $temp_offset=floor($total_records/10);?>
			<a href="<?php echo $PHP_SELF?>?mode=table&offset=<?php echo ($i*$temp_offset)?>"><?php echo prn_icon("jump","Jump to record #".$i*$temp_offset)?></a>
			<?php
        }
    }else{
        //for shorter lists do in multiples of $max_rows
        for ($i=1;$i<10;$i++){
            if($i*$max_rows < $total_records){?>
			<a href="<?php echo $PHP_SELF?>?mode=table&offset=<?php echo ($i*$max_rows)?>"><?php echo prn_icon("jump","Jump to record #".$i*$max_rows)?></a>
			<?php
            }
        }
    }
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
*/
    //Print next page button
    if (($offset+$max_rows)<$total_records){
		$result .= "<a href=\"$PHP_SELF?mode=table&offset=".($offset+$max_rows)."\">".
		prn_icon("next")."</a>";
    }
    if (($offset+$max_rows)<$total_records){
		$result .= "<a href=\"$PHP_SELF?mode=table&offset=".($total_records-$max_rows)."\">".
		prn_icon("end")."</a>";
    }
    return $result;
}


//Function to print a single page from a database
//Argument definitions
//$offset = int, position within list
//$total_records = int, sum of all records within FOUND SET
//$max_rows = int, maximum number of rows to print per page
//			if $max_rows=0 the print the entire list i.e. $max_rows=$total_records
function prn_table($form_array,$offset=0,$max_rows=15){
    global $PHP_SELF,$MY_SESS;
    $form_vars = $form_array[0];
	$table=$form_vars["table"];

    $sess_sort=$MY_SESS[$table]["sort"];
    $sess_sort_order=$MY_SESS[$table]["sort_order"];
    $sess_q=$MY_SESS[$table]["q"];
    $sess_adv_search_query=$MY_SESS[$table]["adv_search_query"];

    if($sess_adv_search_query){
    	$query_count=$sess_adv_search_query;
        $rows_count=TldDatabase::query($query_count);
        $total_records = TldDatabase::numRows($rows_count);
    }else{
	    $query_count="SELECT COUNT(*) AS total FROM $table $sess_q";
        $rows_count=TldDatabase::query($query_count);
        $row_count = TldDatabase::fetchArray($rows_count);
        $total_records = $row_count['total'];
    }

	if ($total_records==0){
		$MY_SESS[$table]["adv_search_query"]="";
		$MY_SESS[$table]["q"]="";
		$result .=<<<EOF
		<p class="alert">No results from search...
         <a href="$PHP_SELF?mode=reset_all">Click here to reset</a>
		</p>
EOF;
		return $result;
	}
    $query_error = TldDatabase::error();
	if ($query_error){
		$MY_SESS[$table]["adv_search_query"]="";
		$MY_SESS[$table]["q"]="";
		$result .=<<<EOF
		<p class="alert">DATABASE ERROR: $query_count
		<br>$query_error
		<br>PLEASE REPORT THIS TO THE WEBMASTER
         <a href="$PHP_SELF?mode=reset_all">Click here to reset</a>
		</p>
EOF;
		return $result;
	}

//    $total_records=TldDatabase::numRows($result_count);
    if ($max_rows==0) {
        $max_rows = $total_records;
    }

    if($sess_adv_search_query){
    	$query=$sess_adv_search_query." ORDER BY $sess_sort $sess_sort_order LIMIT $offset,$max_rows";
    	$result .=<<<EOF
    	<p class="alert">Advanced Search Mode.</p>
EOF;
    }else{
    	$query="SELECT * FROM $table $sess_q ORDER BY $sess_sort $sess_sort_order LIMIT $offset,$max_rows";
        if($sess_q<>''){
    	$result .=<<<EOF
    	<p class="alert">Detailed Search Mode.</p>
EOF;
        }
    }

    if($total_records==0){
    	$result .=<<<EOF
    	<p class="alert">No records found in query.</p>
EOF;
	}
	$rows = tldUtils::getSqlToAssocArray($query);
	if (!is_array($rows)){
		$result .=<<<EOF
		Query: $query<br>
		DATABASE ERROR is:$rows<br>
		PLEASE REPORT THIS TO THE WEBMASTER
        <a href="$PHP_SELF?mode=reset_all">Click here to reset view</a>
EOF;
		return $result;
	}
    $result .= "<div align=\"center\">".prn_sort_controls($form_array);
    $result .= prn_nav($offset,$total_records,$max_rows)."</div>";
$result .= <<<EOF
<table width="100%" border="1" bordercolor="#FFFFFF" cellspacing="0" cellpadding="1" class="smalltext" align="center">
  <tr bgcolor="#3264C8" class="smallwhite">
EOF;
    for ($i=1; $i<count($form_array);$i++){ //print the title header
        if ($form_array[$i]["table"]<>"true") {
            continue;
        }
        $result .= "<td>".$form_array[$i]["label"]."</td>";
    };
    $result .= "<td>&nbsp;</td></tr>";
    //print rows 0 to max_rows
    for ($i=0; $i<$max_rows;$i++){
        $row=$rows[$i];
        if (!$row) {
            break;
        }
        $result .= "<tr";
        if (!($i%2)) {
            $result .= " bgcolor=\"#CCCCCC\"";
        }
        $result .= ">";
        for ($j=1; $j<count($form_array);$j++){//print each cell in a row from 1 to count
            if ($form_array[$j]["table"]<>"true") {
                continue;
            }
            $result .= "<td>".prn_view_object($form_array[$j], $row, "table")."</td>";
        };
        $result .=<<<EOF
<td>
<a href="$PHP_SELF?mode=${form_vars["mode"]}&form_type=${form_vars["form_type"]}&id=${row["id"]}">
EOF;
        $result .= prn_icon("display")."</a></td></tr>";
    };
   $result .= "</table><div align=\"center\">".prn_nav($offset,$total_records,$max_rows)."</div>";
   return $result;
}

//
//Function to download current found set
//Only fields with "dump" set to true are dumped
//
function download_csv($form_array){
	global $MY_SESS,$PHP_SELF;

	$table=$form_array[0]["table"];
	header("Content-type: application/csv");
	header("Content-Disposition: attachment; filename=".$table.".csv");

	$sess_sort=$MY_SESS[$table]["sort"];
	$sess_sort_order=$MY_SESS[$table]["sort_order"];
	$sess_q=$MY_SESS[$table]["q"];
	$sess_adv_search_query=$MY_SESS[$table]["adv_search_query"];

	if($sess_adv_search_query){
		$query=$sess_adv_search_query." ORDER BY $sess_sort $sess_sort_order";
	}else{
		$query="SELECT * FROM $table $sess_q ORDER BY $sess_sort $sess_sort_order";
	}
	$result=TldDatabase::query($query);
	//Create Title row
	$line="";
	foreach($form_array as $field_array){
		if($field_array["dump"]=="true") {
            $line .= "\"".$field_array["name"]."\",";
        }
	}
	echo substr($line,0,-1)."\n";
	//Create each line
	while($row = TldDatabase::fetchArray($result)) {
		$line="";
		foreach($form_array as $field_array){
		    // Check if we can extract field
		    if($field_array["dump"]!="true") {
                continue;
            }
		    // Add the line
			switch($field_array["type"]){
		    case 'lookup':
		        $rawDatas = TldDatabase::query($field_array["select_query_view"].$row[$field_array["name"]]);
			    $data = array();
		        while($array = TldDatabase::fetchArray($rawDatas)){
					$data[] = $array;
				}
				$line.="\"".preg_replace("/\r\n|\n\r|\n|\r/","",$data[0][$field_array["select_field_view"]])."\",";
		    break;
		    default:
		        $line.="\"".preg_replace("/\r\n|\n\r|\n|\r/","",$row[$field_array["name"]])."\",";
		    break;
			}
		}
		echo substr($line,0,-1)."\n";
	}
}


//Function to print a form to enter data into a database or print a form to do a search on a database
//Argument definitions
//$id = int, parent id to match
//$form_array = structured array definition for database
//$form_mode = "form_save","do_search"
//
function prn_form_add($id,$form_array,$num_lines){
global $PHP_SELF;
$form_vars = $form_array[0];
if($num_lines==0) {
    $num_lines = 1;
}
$result .=<<<EOF
<a href="javascript:history.back()">Back</a>
<form method="post" action="$PHP_SELF" ENCTYPE="multipart/form-data">
<input type="submit" name="Submit" value="Submit">
<input type="reset" name="Submit2" value="Reset">
<input type="hidden" name="mode" value="form_save">
<input type="hidden" name="form_type" value="${form_vars["form_type"]}">
<input type="hidden" name="num_lines" value="$num_lines">
<br>
EOF;
	for($j=0;$j<$num_lines;$j++){
		$result .= "<b>Line #".($j+1)."</b>";
		$result .=<<<EOF
		<input type="hidden" name="parent_id[$j]" value="$id">
		<table width="100%" border="1" cellspacing="0" cellpadding="1" class="smalltext" bordercolor="#FFFFFF">
EOF;
	  for ($i=1; $i<count($form_array);$i++){
	  	$form_field = $form_array[$i];
	  $var_name=$form_field["name"];
	  $type=$form_field["type"];
	  if ($type=="foreign_key" || $type=="hyperlink" || $type=="col_break") {
          continue;
      }
		$result .=<<<EOF
	  <tr>
	    <td bgcolor="#3264C8" class="smallwhite">${form_field["label"]}</td>
	    <td bgcolor="#CCCCCC">
EOF;
	    $result .= prn_add_object($form_field,$j)."</td></tr>";
	  };
		$result .= "</table>";
	}
$result .=<<<EOF
<input type="submit" name="Submit" value="Submit">
<input type="reset" name="Submit2" value="Reset">
</form>
EOF;
	return $result;
}

//Function to print a form object
//Argument definitions
//$form_object = structured array definition for a field in a database
//$form_mode = "" by default,"do_search"
//$resturn, set to true to return string output
//
function prn_add_object($form_object,$var_index,$return=FALSE){
global $PHP_AUTH_USER;
$var_name=$form_object["name"]."[$var_index]";

switch ($form_object["type"]){
	case "title":
		$result = "&nbsp;";
	break;
	case "locked_field":
		$result = "&nbsp;";
	break;
	case "primary_key":
		$result = "To be assigned";
	break;
	case "auto_date":
		$year =  date("Y");
		$month =  date("m");
		$day =  date("d");
		$value = $form_object["add"] ? date("Y-m-d H:i:s", mktime(0, 0, 0, $month + $form_object["add"]["months"], $day + $form_object["add"]["days"], $year + $form_object["add"]["years"])) : date("Y-m-d H:i:s");
		$result =<<< EOF
		<input type="text" name="$var_name" value="$value">
EOF;
	break;
	case "auto_password":
		$value = time();
		$result =<<<EOF
		<input type="hidden" name="$var_name" value="$value">
		$value
EOF;
	break;
	case "auto_user":
		if($form_object["field"]){
			$myUser = new tldUser($GLOBALS["PHP_AUTH_USER"]);
			$value = $myUser->itsDetails[$form_object["field"]];
		}else{
			$value=$PHP_AUTH_USER;
		}
		$result =<<< EOF
		<input type="hidden" name="$var_name" value="$value">
		$value
EOF;
	break;
	case "locked_date":
	case "warranty_end_date":
		$result=<<<EOF
		<input type="hidden" name="$var_name" value="">
	    &nbsp;
EOF;
	break;
	case "reg_details":
	 	$result =<<<EOF
		<input type="hidden" name="$var_name" value="">
	 	&nbsp;
EOF;
	break;
	case "courier":
	case "textarea":
		$attributes = $form_object["textarea_params"];
		$result =<<<EOF
		<textarea name="$var_name" $attributes></textarea><?php
EOF;
	break;
/*
EXAMPLE:
	array("name"=>"failure_system",	"label"=>"System",										"type"=>"select",		"table"=>"true",
		"source"=>"lists", "list_name"=>"warranty.failure_system"

*/
	case "select":
		switch($form_object["source"]){
		case "lists":
			$listItems = tldList::optionsByListNameAsListItemListItem($form_object["list_name"]);
			foreach($listItems as $listItem){
				$select_list[] = $listItem;
			}
		break;
		default:
			$select_list = $form_object["select_list"];
		}
		$result =<<<EOF
	  <select name="$var_name">
EOF;
		if($form_object["default"]){
		$default = $form_object["default"];
		$result .=<<<EOF
		<option value="$default" selected="selected">$default</option>
EOF;
		}

		if($form_object["source"]=="smartyOptions"){
			foreach($select_list as $key=>$value){
			$result .= <<<EOF
			<option value="$key">$value</option>
EOF;
			}
		}else{
			for ($j=0; $j<count($select_list);$j++){
			$result .= <<<EOF
			<option value="${select_list[$j]}">
			$select_list[$j]
			</option>
EOF;
			}
		}
		$result .= "</select>";
	break;
	case "lookup":
	case "select_db":
		$attrib = $form_object["options"];
		$result .=<<<EOF
		<select name="$var_name" $attrib>
EOF;
		if(!$attrib['required']){
		$result .=<<<EOF
			<option>&nbsp;</option>
EOF;
		}
	$select_list = TldDatabase::query($form_object["select_query"]);
	while($select_row=TldDatabase::fetchArray($select_list)){
		$result .=
		"<option value=\"".htmlspecialchars(stripslashes($select_row[$form_object["select_field_1"]]))."\">".
		htmlspecialchars(stripslashes($select_row[$form_object["select_field_2"]])).
		"</option>\n";
		}
		$result .= "</select>";
	break;
	case "multi_select_db":
		$result =<<<EOF
	   <select name="$var_name" multiple>
	   	<option>&nbsp;</option>
EOF;
	$select_list = TldDatabase::query($form_object["select_query"]);
	while($select_row=TldDatabase::fetchArray($select_list)){
		$result .=
		"<option value=\"".htmlspecialchars(stripslashes($select_row[$form_object["select_field_1"]]))."\">".
		htmlspecialchars(stripslashes($select_row[$form_object["select_field_2"]])).
		"</option>";
		}
		$result .= "</select>";
	break;
	case "file_upload":
		$result =<<<EOF
		<input type="file" name="$var_name">
EOF;
	break;
	default:
		$result =<<<EOF
		<input type="${form_object["type"]}" name="$var_name?>" size="${form_object["width"]}">
EOF;
	}
	return $result;
}

//Function to print a form to enter data into a database or print a form to do a search on a database
//Argument definitions
//$id = int, parent id to match
//$form_array = structured array definition for database
//$form_mode = "form_save","do_search"
//
function prn_form_search($form_array){
global $PHP_SELF;
$form_vars = $form_array[0];
$result .=<<<EOF
<div align="center">
<a href="javascript:history.back()">Back</a>
<h2>Advanced Search</h2>
<p><font color="#FF0000">Select the required comparison operator</font><font color="#FF0000">
  next to fields you want to search in.<br>
  '&lt;' less than, '&gt;' greater than, '&lt;&gt;' not equal, 'like' partial
  string comparison <br>
  Searches are exact unless you use the wildcard symbol.<br>
  Use '%' as a wildcard.</font></p>
<p><font color="#FF0000">EXAMPLE: </font><font color="#FF0000">Searching for 'like
  %828%' matches anything CONTAINING the sequence '828'. It will match 828-STD,
  828-WID, 828-SUP.<br>
  &quot;like ACE%&quot; will match everything BEGINNING with 'ACE' only i.e. ACE-802,
  ACE-804, etc.<br>
  <br>
  </font> </p>
<form method="post" action="$PHP_SELF">
<input type="submit" name="Submit" value="Submit">
<input type="reset" name="Submit2" value="Reset">
<input type="hidden" name="mode" value="do_search">
<input type="hidden" name="form_type" value="${form_vars["form_type"]}">
Logical operator for search: <select name="logic_op">
	<option>AND</option>
	<option>OR</option>
</select>
<table width="100%" border="1" cellspacing="0" cellpadding="1" class="smalltext" bordercolor="#FFFFFF" align="center">
EOF;
  for ($i=1; $i<count($form_array);$i++){
  	$field_vars=$form_array[$i];
  $type=$field_vars["type"];
  $var_name = $field_vars["name"];
  if ($var_name=="parent_id" || $type=="hyperlink" || $type=="col_break") {
      continue;
  }
	$result .=<<<EOF
  <tr>
    <td bgcolor="#3264C8" class="smallwhite">${field_vars["label"]}</td>
    <td bgcolor="#CCCCCC">
EOF;
    if ($type=="title"){
		$result .= "<td bgcolor=\"#CCCCCC\">&nbsp;</td>";
	}else{
		$result .=<<<EOF
		<select name="dropbox_$var_name">
			<option></option>
			<option><</option>
			<option>></option>
			<option><></option>
			<option>like</option>
		</select>
EOF;
	}
	$result .= prn_search_object($field_vars)."</td></tr>";
  };
  $result .=<<<EOF
</table>
<input type="submit" name="Submit" value="Submit">
<input type="reset" name="Submit2" value="Reset">
</form>
</div>
EOF;
	return $result;
}

//Function to print a form object for searches
//Argument definitions
//$form_object = structured array definition for a field in a database
//$form_mode = "" by default,"do_search"
//
function prn_search_object($form_object){
global $PHP_AUTH_USER;
$var_name=$form_object["name"];
  switch ($form_object["type"]){
  case 'title':
    $result .= "&nbsp;";
  break;
  case 'primary_key':
  		$result .=<<<EOF
  		<input type="text" name="$var_name" value=""> N.B. Only enter the numeric part of ref.
EOF;
  break;
  case "reg_details":
	$result .=<<<EOF
    <input type="hidden" name="$var_name" value="">
EOF;
    $result .= "&nbsp;";
  break;
  case "lookup":
	$result .=<<<EOF
     <select name="$var_name">
     	<option>&nbsp;</option>
EOF;
		$rows = tldUtils::getSqlToAssocArray($form_object["select_query"]);
		foreach($rows as $row){
			$result .= "<option value=\"".htmlspecialchars(stripslashes($row[$form_object["select_field_1"]]))."\">".
htmlspecialchars(stripslashes($row[$form_object["select_field_2"]]))."</option>";
		}
	$result .= "</select>";
  break;
  default:
    $result .=<<<EOF
    <input type="${form_object["type"]}" name="$var_name">
EOF;
  }
  return $result;
}


//Function to print a list from a database
//Argument definitions
//$id = int, parent id to match
//$form_array = structured array definition for database
//
//function prn_list($id,$form_array){
function prn_list($form_array, $result){
	global $PHP_SELF;
	//$result = TldDatabase::query("SELECT * FROM ".$form_array[0]["table"]." WHERE parent_id='$id'");
	$num_rows = TldDatabase::numRows($result);
	$body .=<<<EOF
  <table width="100%" border="1" cellspacing="0" cellpadding="1" class="smalltext">
	<tr>
EOF;
	  for ($i=1; $i<count($form_array);$i++){
	    if ($form_array[$i]["table"]<>"true") {
            continue;
        }
        $body .= "<td bgcolor=\"#3264C8\" class=\"smallwhite\">".$form_array[$i]["label"]."</td>";
	  };
	  $body .= "</tr>";
    while($row=TldDatabase::fetchArray($result)){
	   if (!$row) {
           break;
       }
    	$body .= "<tr bgcolor=\"#CCCCCC\">";
	  for ($j=1; $j<count($form_array);$j++){
	  if ($form_array[$j]["table"]<>"true") {
          continue;
      }
	  	$body .= "<td>".prn_view_object($form_array[$j],$row)."</td>";
	  }
	$body .= "</tr>";
	}
  	$body .= "</table>";
  	return $body;
}

function getPlainView($id, $form_array, $weblink=1){
    global $PHP_SELF,$max_rows,$sess_show;
    $row = tldUtils::getSqlRowToAssocArray("SELECT * FROM ".$form_array[0]["table"]." WHERE id=".$id);
    if (empty($row)){
          return  "CANNOT FIND RECORD ID=".$id;
    }
    $form_data = $form_array[0];
    if ($weblink){
	    $result .=<<<EOF
	    <a href="https://www.tld-gse.com$PHP_SELF?mode=record_view&form_type=${form_data["form_type"]}&id=$id">
	    Go to this record on the website</a><p>
EOF;
    }else{
    	$result .= "<b>Close this window when finished</b>";
    }
    array_shift($form_array);
    foreach($form_array as $field){
    	$fields[$field["name"]] = $field["label"];
    }
    $form = new tldAssocTable($row, $fields);
    $result .= $form->fetch();
    if (isset($form_data["child_tables"])){
    	$result .= "<hr>";
	    foreach ($form_data["child_tables"] as $child_table){
        	$fields=array();
	        $child_table .= "_tpl";
	        global $$child_table; // phpcs:ignore
	        $child_table_params=${$child_table}[0];
			$query = "SELECT * FROM ".$child_table_params["table"]." WHERE parent_id='$id'";
			if($child_table_params["default_sort"]) {
                $query .= " order by ".$child_table_params["default_sort"];
            }
			$rows = tldUtils::getSqlToAssocArray($query);
			if(count($rows)){
	        	$result .= "<b>${child_table_params["title"]}</b>";
			    array_shift($$child_table);
			    foreach($$child_table as $field){
   			 		if($field["table"]=="true") {
                        $fields[$field["name"]] = $field["label"];
                    }
    			}
	        	$form = new tldReportColumnar($rows, array("xItems"=>$fields));
	        	$result .= $form->fetch();
//	        	prn_plain_list($$child_table,$result);
	        }
	    }
    }
    return $result;
}

//Function to print a record for viewing
//Argument definitions
//$id = int, id of record to view
//$form_array = structured array definition for database
//
function prn_form_view($id,$form_array){
	global $PHP_SELF;
	$row = tldUtils::getSqlRowToAssocArray("SELECT * FROM ".$form_array[0]["table"]." WHERE id=".$id);
	$result .=<<<EOF
    <table width="100%" border="1" cellspacing="0" cellpadding="1" class="smalltext" bordercolor="#FFFFFF" align="center">
EOF;
    for ($i=1; $i<count($form_array);$i++){
    	$field_array = $form_array[$i];
	    $var_name = $field_array["name"];
        if ($var_name=="parent_id" || $field_array["type"]=="col_break") {
            continue;
        }
	$result .=<<<EOF
      <tr>
        <td bgcolor="#3264C8" class="smallwhite">${field_array["label"]}</td>
        <td bgcolor="#CCCCCC">
EOF;
        $result .= prn_view_object($field_array,$row);
        $result .=<<<EOF
            </td>
          </tr>
EOF;
    }
    return $result."</table>";
}

function prn_view_object($form_object,$row,$mode=""){
global $PHP_SELF,$PHP_AUTH_USER,$UPLOADS_PATH;
    $var_name=$form_object["name"];
//    if (empty($row[$var_name]) && $form_object["type"]<>"hyperlink"){
    if ($row[$var_name]=='' && $form_object["type"]<>"hyperlink"){
    	return "&nbsp;";
    }
    switch ($form_object["type"]){
        case "primary_key":
            $result .= $form_object["prefix"].$row[$var_name];
        break;
        case "title":
            $result .=  "&nbsp;";
        break;
        case "hyperlink":
        	$result .=  $form_object["hyperlink"];
        break;
        case "auto_date":
        case "date_type":
        case "locked_date":
        case "warranty_end_date":
             $result .= str_replace("-","&ndash;",$row[$var_name]);
//             $result .=  $row[$var_name];
//        }
        break;
        case "reg_details":
            $result .= prn_subrecord($row[$var_name],$form_object["select_query_view_tpl"]);
        break;
        case "lookup":
            //$select_list = TldDatabase::query($form_object["select_query_view"].$row[$var_name]);
//			echo $form_object["select_query_view"].$row[$var_name];
            if (!$select_list = TldDatabase::query($form_object["select_query_view"]."'".$row[$var_name]."'")){
//            if (!$select_list = TldDatabase::query($form_object["select_query_view"].$form_object["select_field_1"])){
            	$result .=  $row[$var_name]." NOT FOUND";
                break;
            }
            $select_row=TldDatabase::fetchArray($select_list);
            if ($mode=="table"){
                $result .=  $select_row[$form_object["select_field_view"]];
            }elseif(isset($form_object["select_query_view_tpl"])){
                $result .= prn_subrecord($row[$var_name],$form_object["select_query_view_tpl"]);
            }else{
                $result .=  stripslashes($select_row[$form_object["select_field_view"]]);
            }
        break;
        case 'select':
        	if($form_object["source"]=="smartyOptions"){
				$result .= $form_object['select_list'][$row[$var_name]];
			}else{
        		$result .= $row[$var_name];
			}
        break;
		case "file_upload":
		    $serverFilePath = $UPLOADS_PATH.'/'.$form_object["file_upload_dir"]."/".$row[$var_name];
			$filepath="/en/private/uploads/".$form_object["file_upload_dir"]."/".$row[$var_name];
			$file_upload_type=$form_object["file_upload_type"];
			switch($file_upload_type){
				case "inline_img":
					$result .= "<img src=\"$filepath\" ${form_object["attribs"]}>";
				break;
				default:
					//options
					//unprotected, no permission checks
					//default is protected
					if(file_exists($serverFilePath)){
						$link = "<a href=\"$filepath\"><img src=\"/shared/bluesphere/16x16/actions/filesaveas.png\"".
								" title=\"Click to download, [".round(filesize($serverFilePath)/1024,2)."kb, ".
								date("Y-m-d",filemtime($serverFilePath))."]\"></a>";
						$file_upload_table=$form_object["file_upload_table"];
						if($file_upload_table){
							//options:
							//if file_upload_table is set the protection is turned on
							//file_upload_table, must have field 'access' in the related table
							//
							$query="SELECT $file_upload_table.* FROM $file_upload_table,people".
									" WHERE $file_upload_table.access=people.id AND".
									" $file_upload_table.parent_id=".$row["id"].
									" AND people.email='$PHP_AUTH_USER'";
							$doCheck=TldDatabase::numRows(TldDatabase::query("SELECT * FROM $file_upload_table WHERE parent_id=".$row["id"]));
							if($rows = TldDatabase::query($query)){
								if(TldDatabase::numRows($rows) == 1 || $doCheck == 0){
									$result .=  $link;
								}
							}
						}else{
							$result .=  $link;
						}
					}else{
						$result .= "<p class=\"alert\">${row[$var_name]}: File not found</p>";
					}
			}
		break;
		case "courier":
		//UPS,FED,DHL
			include_once('forms_and_reports.inc.php');
			$lines = tldCourier::parseMultiple($row[$var_name]);
			foreach($lines as $line){
				$form = new tldCourier($line['courier']);
				$result .= $form->fetch($line['trackNum']);
			}
		break;
		case "password":
			$result .= "***********";
		break;
        default:
            $result .= stripslashes(nl2br($row[$var_name]));
        }
	return $result;
}

//Function to construct a WHERE clause based on search form
//Argument definitions
//$form_array = structured array definition for database
//
function construct_where($form_array){
    for ($i=1; $i<count($form_array);$i++){
    //echo $i;
    $var_name=$form_array[$i]["name"];
    if (empty($GLOBALS["dropbox_".$var_name])) {
        continue;
    }
    $field_op=$GLOBALS["dropbox_".$var_name];
    switch ($form_array[$i]["type"]){
        default:
            $where .= $var_name." $field_op '".stripslashes($GLOBALS[$var_name])."' ".$GLOBALS["logic_op"]." ";
        }
    }
    $oplength=strlen($GLOBALS["logic_op"])+2;
    if ($where) {
        return " WHERE ".substr($where, 0, -$oplength);
    }
}

//Function to construct a WHERE clause based on QUICK search form
//Argument definitions
//$form_array = structured array definition for database
////$search_target = what you're looking for
function construct_quick_where($form_array,$search_target){
	$search_target=str_replace(' ','%',$search_target);
    for ($i=1; $i<count($form_array);$i++){
    	$row_object=$form_array[$i];
    	$var_name=$row_object["name"];
    	if ($var_name=="bypass") {
            continue;
        }

    	$table_name=$form_array[0]["table"];
        $where .= " $table_name.$var_name like '%$search_target%' OR ";
    }
    if ($where) {
        return " WHERE ".substr($where, 0, -4);
    }
}

function prepend_table_name($form_array,$target){
   	//Extract table name
   	$table_name=$form_array[0]["table"];
    for ($i=1; $i<count($form_array);$i++){
    	//Extract field name
		$field_name=$form_array[$i]["name"];
		if($field_name=="bypass" || $field_name=="id" || $field_name=="parent_id") {
            continue;
        }
		$where.="$table_name.$field_name like '%$target%' OR ";
	}
    if ($where) {
        return substr($where, 0, -4);
    }
}

//Function to make pop up message in browser
//$message=message to display
//
function popup_message($message){
	?>
    <script>
    alert("<?php echo $message?>");
    </script><?php
}


function getAlphaNumericOnly($aString){
	for($i=0;$i<strlen($aString);$i++){
		$aChar=substr($aString,$i,1);
		$asciiChar=ord($aChar);
		if(($asciiChar>47 && $aciiChar<58)||
			($asciiChar>64 && $aciiChar<91)||
			($asciiChar>96 && $aciiChar<123)){
			$result.= $aChar;
		}
	}
	return $result;
}

switch($mode){
	case 'download_csv':
		download_csv($form_array);
		exit;
}
