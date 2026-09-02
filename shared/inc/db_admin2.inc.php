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
include_once("common.inc.php");
include_once("calendar.inc.php");
include_once("forms_and_reports.inc.php");
require_once ("HTML/QuickForm.php");



//function to show dialog box ask before deleting
?><script>
function checkanddelete(id,form_type) {
   if(confirm("Are you sure you want to delete?")) {
      window.location = "<?php echo $PHP_SELF;?>?mode=del&id="+id+"&form_type="+form_type;
   }
}
</script><?php

//Function to create record view from given table definition array
//If parent table has related child tables then table definition
//must have a table property entry $form_array[0]["child_tables"]
//$id = id for parent record
//$form_array = parent record table definition array
//
function prn_record($id,$form_array){
	global $PHP_SELF,$max_rows,$MY_SESS,$main_tpl;
	$row = tldUtils::getSqlRowToAssocArray("SELECT * FROM ".$form_array[0]["table"]." WHERE id=".$id);
    if (empty($row)){
    	$message =<<<EOF
         CANNOT FIND RECORD ID=$id<br>
          <a href="$PHP_SELF?>?mode=">Click here to continue</a>
EOF;
		return $message;
    }
	$parent_id=$row["parent_id"];
	$formSpec = $form_array[0];
    $form_type=$formSpec["form_type"];
	$PRN_RECORD_MENU = str_replace("{id}",$id,$formSpec["prn_record_menu"]);
    $PRN_RECORD_MENU = str_replace("{parent_id}",$parent_id, $PRN_RECORD_MENU);
	//common header
    $result .=<<<EOF
	<p align="center">
		$PRN_RECORD_MENU
EOF;

		if($form_type=="main_tpl"){
			$result .=<<<EOF
		<a href="$PHP_SELF?mode=duplicate&id=$id">Duplicate</a>&nbsp;|&nbsp;
EOF;
		}
    $result .= <<<EOF
        <a href="$PHP_SELF?mode=form_edit&form_type=$form_type&id=$id">Edit</a>&nbsp;|&nbsp;
        <a href="$PHP_SELF?mode=form_email&form_type=$form_type&id=$id">Email</a>
EOF;
    if (!isset($formSpec["delete"]) && $formSpec["delete"] !== false) {
        $result .= <<<EOF
    &nbsp;|&nbsp;<a href = "javascript:checkanddelete('$id','$form_type')" > Delete</a >
EOF;
    }
    $result .= <<<EOF
        <p>
    <p align="center"><a href="$PHP_SELF?mode=
EOF;
	if($form_type=="main_tpl"){
		$result .= "table&form_type=";
	}else{
		$result .= "record_view&form_type=main_tpl&id=".$parent_id;
	}
	$result .=<<<EOF
	">Up</a> | <a href="javascript:history.back()">Back</a></p>
EOF;
	$result .= prn_form_view($id,$form_array);
	//do child tables next
    if (isset($formSpec["child_tables"])){
        foreach ($formSpec["child_tables"] as $child_table_name){
            $tblname = $child_table_name;
            if (is_array($child_table_name)) {
                $tblname = current(array_keys($child_table_name));
                $clauses = current(array_values($child_table_name));
            }
            $child_table_name = $tblname . "_tpl";
            global $$child_table_name; // phpcs:ignore
            $result .= "<hr width=\"650\"><div align=\"center\">";
            $result .= prn_list_add($id, $$child_table_name, $clauses);
			$result .= "</div>";
        }
    }
    return $result;
}

//Function to print control bar above each table
//$form_array = table definition array
//
function prn_sort_controls($form_array){
	global $PHP_SELF,$MY_SESS;
	$formSpec = $form_array[0];
	$table=$formSpec["table"];
	$form_type = $formSpec["form_type"];
	$sess_sort_order=$MY_SESS[$table]["sort_order"];
	$sess_sort=$MY_SESS[$table]["sort"];
	//prepend the PRN_TABLE_MENU
	$result .= $formSpec["prn_table_menu"]."&nbsp;<b>".$formSpec["title"]."</b>";

	$result .=<<<EOF
&nbsp;|&nbsp;<a href="$PHP_SELF?mode=form_add&form_type=$form_type">Add</a>
&nbsp;|&nbsp;<a href="$PHP_SELF?mode=form_search&form_type=$form_type">Detailed Search</a>
&nbsp;|&nbsp;<a href="$PHP_SELF?mode=reset_all">Reset</a>
&nbsp;|&nbsp;<a href="$PHP_SELF?mode=download_csv&form_type=main_tpl">Download</a>
&nbsp;|&nbsp;<a href="$PHP_SELF?mode=dump_list">List All</a>
<table>
	<tr class="smalltext">
		<td>
		    <form method="get" action="$PHP_SELF">
		    <input type="hidden" name="mode" value="table">
		    <input type="hidden" name="form_type" value="$form_type">
		    <INPUT type="hidden" name="offset" value="0">
		    <select name="sort">
EOF;
    for ($i=1; $i< count($form_array);$i++){
        if ($form_array[$i]["table"]=="false") {
            continue;
        }
        $result .= "<option value=\"".$form_array[$i]["name"]."\"";
        if ($form_array[$i]["name"]==$sess_sort){
        	$result .= "selected";
        }
        $result .= ">".substr($form_array[$i]["label"],0,18)."...</option>";
    }
$result .=<<<EOF
	    </select>
	    <select name="sort_order">
	    <option
EOF;
if ($sess_sort_order=="ASC") {
    $result .= " selected";
}
	$result .= ">ASC</option><option";
	if ($sess_sort_order=="DESC") {
        $result .= " selected";
    }
	$result .=<<<EOF
		>DESC</option>
		</select>
		<input type="submit" name="Submit" value="Sort">
		</form>
		</td>
		<td>
		<form method="get" action="$PHP_SELF">
		&nbsp;|&nbsp;<INPUT type="hidden" name="mode" value="do_quick_search">
		<INPUT type="text" name="search_target" size="10">
        <INPUT type="submit" name="findit" value="Find">
      </form>
    </td>
	</tr>
</table>
EOF;
	return $result;
}

function prn_email_form($id,$form_type){
	global $PHP_SELF,$PHP_AUTH_USER;
	$smarty = tldUtils::getSmarty("common");
	$query=<<<EOF
SELECT email, CONCAT(lastname,',',firstname,',',COALESCE ((SELECT location FROM locations WHERE people.bu_id=locations.id), '')) AS fullname
FROM people WHERE hidden<>1 AND disabled='N' ORDER BY fullname
EOF;
	$smarty->assign("recipients", tldUtils::getSqlToAssocArray($query, "smartyOptions", ['email', 'fullname']));
	$smarty->assign("PHP_SELF", $PHP_SELF);
	$smarty->assign("form_type", $form_type);
	$smarty->assign("id", $id);
	return $smarty->fetch("db/email_form.tpl");
}

//Function to retrieve record view from itself into a temp file then
//email as an HTML doc to $AUTHUID assuming $AUTHUID is still
//email address used for login
//
function email_record($emails, $id, $form_array, $from, $message, $subject){
	global $PHP_SELF,$PHP_AUTH_USER;
	if (empty($emails)){
		return "Pls select at least one email address";
	}
	if (!empty($emails)){
		if (count($emails)>20){
			return "Max. of 20 users per email, message not sent";
		}
		$recipients .= implode(",", $emails);
	}
	$recipients .= ",".$PHP_AUTH_USER;
	$message = nl2br(stripslashes($message));
	$fcontents = getPlainView($id, $form_array, true);

        $result = tldUtils::emailAttachment(
            $recipients,
            $from,
            $form_array[0]["title"]." Record ID:".$id.", ".$subject,
            $message."\n\n".$fcontents
            );
	return $error ? "There was a problem sending the email" :
		"A copy has been sent to the above recipients. $recipients";
}

/**
 * Function to print a list from a database plus a quick add form at bottom
 *
 * Argument definitions
 * @param $id = int, parent id to match
 * @param$form_array = structured array definition for database
*/
function prn_list_add($id,$form_array, $options = []){
	global $PHP_SELF;
    $WHERE = isset($options['where']) ? $options['where'] : '';
	$form_type = $form_array[0]["form_type"];
	$query="SELECT * FROM ".$form_array[0]["table"]." WHERE parent_id='".$id."' $WHERE";
	if($form_array[0]["default_sort"]) {
        $query .= " ORDER BY ".$form_array[0]["default_sort"];
    }
	$rows = tldUtils::getSqlToAssocArray($query);
	$num_rows = count($rows);
	$result .=<<<EOF
	<form method="post" action="$PHP_SELF" ENCTYPE="multipart/form-data">
	<input type="hidden" name="mode" value="form_add">
	<input type="hidden" name="form_type" value="$form_type">
	<input type="hidden" name="id" value="$id">
	<b>
EOF;
	$result .= $form_array[0]["title"];
	$result .=<<<EOF
	</b> | Add
	<input type="text" name="num_lines" size="2" value="1">lines
	<input type="submit" name="Go" value="Go">
	</form>
EOF;
	if ($num_rows==0){
		$result .= "NO RECORDS";
		return $result;
	}
$result .=<<<EOF
  <table width="100%" bgcolor="#CCCCCC" border="1" bordercolor="#FFFFFF" cellspacing="0" cellpadding="1" class="smalltext">
	<tr bgcolor="#3264C8" class="smallwhite">
EOF;
//print the column titles
	  for ($i=1; $i<count($form_array);$i++){
	    if ($form_array[$i]["table"]<>"true") {
            continue;
        }
        $result .= "<td>".$form_array[$i]["label"]."</td>";
	  }
	  $result .= "<td>&nbsp;</td></tr>";
//print the rows
    foreach($rows as $row){
	$result .="<tr>";
	  for ($j=1; $j<count($form_array);$j++){
	  if ($form_array[$j]["table"]<>"true") {
          continue;
      }
        $result .= "<td>".prn_view_object($form_array[$j],$row)."</td>";
	  }
	  $result .=<<<EOF
      <td><a href="$PHP_SELF?mode=record_view&form_type=$form_type&id=${row["id"]}">Display</a></td>
	</tr>
EOF;
    }
  $result .= "</table>";
  return $result;
}

/**
 * Function to print a form to edit data in a database
 * Argument definitions
 * $id = int, id of record to edit
 * $form_array = structured array definition for database
*/
function prn_form_edit($id,$form_array){
	global $PHP_SELF;
	$form_type = $form_array[0]["form_type"];
	$row = tldUtils::getSqlRowToAssocArray("SELECT * FROM ".$form_array[0]["table"]." WHERE id=".$id);
	$result .=<<<EOF
<div align="center">
<!--<a href="javascript:history.back()">Back</a>-->
<a href="$PHP_SELF?mode=record_view&form_type=$form_type&id=$id">Cancel</a>
<form method="post" action="$PHP_SELF" ENCTYPE="multipart/form-data">
<input type="submit" name="Submit" value="Submit">
<input type="reset" name="Submit3" value="Reset">
<input type="hidden" name="mode" value="update">
<input type="hidden" name="form_type" value="$form_type">
<input type="hidden" name="id[0]" value="${row["id"]}">
<input type="hidden" name="parent_id[0]" value="${row["parent_id"]}">
    <table width="100%" border="1" cellspacing="0" cellpadding="1" class="smalltext" bordercolor="#FFFFFF">
EOF;
      for ($i=1; $i<count($form_array);$i++){
      	$var_array=$form_array[$i];
	      $var_name=$var_array["name"];
          $type=$var_array["type"];
            if ($type=="foreign_key" || $type=="hyperlink" || $type=="col_break") {
                continue;
            }
          $result .=<<<EOF
          <tr><td bgcolor="#3264C8" class="smallwhite">
          ${var_array["label"]}</td><td bgcolor="#CCCCCC">
EOF;
          $result .= prn_edit_object($var_array,$row)."</td></tr>";
      };
      $result .=<<<EOF
        </table>
  <input type="submit" name="Submit" value="Submit">
  <input type="reset" name="Submit3" value="Reset">
</form>
</div>
EOF;
	return $result;
}

//Function to print a form object for an edit form
//Argument definitions
//$form_object = structured array definition for a field in a database
//$row = associative array holding data of record from database
//
function prn_edit_object($form_object,$row){
    $var_name=$form_object["name"];
	$value=$row[$var_name];
	$var_name.="[0]";
	$type=$form_object["type"];
        switch ($type){
			case "primary_key":
				$result = $value;
			break;
            case "title":
                $result = "&nbsp;";
            break;
             case "locked_field":
            case "auto_password":
            	$result =<<<EOF
            	<input type="hidden" name="$var_name" value="$value">
                $value
EOF;
            break;
            case "auto_user":
            	$result =<<<EOF
            <input type="hidden" name="$var_name" value="$value">
            $value
EOF;
            break;
            case "locked_date":
            case "warranty_end_date":
            $result =<<<EOF
            <input type="hidden" name="$var_name" value="$value">
EOF;
                if (empty($value)){
                  $result .= "&nbsp;";
                }else{
                    $result = $value;
                }
            break;
            case "reg_details":
            $result =<<<EOF
            <input type="hidden" name="$var_name" value="$value">
EOF;
                if (empty($value)){
                  $result = "&nbsp;";
                }else{
                   $result = prn_subrecord($value,$form_object["select_query_view_tpl"]);
                }
            break;
			case "courier":
            case "textarea":
            $result =<<<EOF
<textarea name="$var_name" ${form_object["textarea_params"]}>$value</textarea>
EOF;
            break;
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
				if($form_object["source"]=="smartyOptions"){
					$result .=<<<EOF
					<option value="$value">${select_list[$value]}</option>
EOF;
					foreach($select_list as $key=>$value){
					$result .= <<<EOF
					<option value="$key">$value</option>
EOF;
					}
				}else{
					$result .=<<<EOF
					<option value="$value">$value</option>
EOF;
					for ($j=0; $j<count($select_list ?? []);$j++){
	    	            $result .= "<option ";
	    	            if ($select_list[$j]==$value) {
                            $result .= "selected";
                        }
						$result .= ">".$select_list[$j]."</option>";
	    	        }
				}
	            $result .= "</select>";
            break;
            case "lookup":
            case "select_db":
            	$result .=<<<EOF
                <select name="$var_name">
                <option value="
EOF;
           		if (!empty($value)) {
                    $result .= $value;
                }
           		$result .="\">";
           		if (!empty($value)) {
                    $result .= $value."</option>";
                }
                $select_list = tldUtils::getSqlToAssocArray($form_object["select_query"]);
				if(count($select_list)){
					foreach($select_list as $select_row){
						$result .= "<option value=\"".$select_row[$form_object["select_field_1"]]."\"";
						if ($select_row[$form_object["select_field_1"]]==$value) {
                            $result .= "selected";
                        }
						$result .= ">";
						$result .= $select_row[$form_object["select_field_2"]]."</option>";
					}
				}
                $result .= "</select>";
            break;
            case "hidden":
            	$result .=<<<EOF
            	<input type="hidden" name="$var_name" value="$value" size="40">$value
EOF;
            break;
            case "file_upload":
				$result .=<<<EOF
            	<input type="hidden" name="orig_$var_name" value="$value">
            	Original filename: $value<br>
				<input type="file" name="$var_name">
EOF;
            break;
            default:
            $value = stripslashes(htmlspecialchars($value));
            $width = empty($form_object["width"]) ? 50 : $form_object["width"];
            $result .=<<<EOF
                <input type="${form_object["type"]}" name="$var_name" value="$value" size="$width">
EOF;
      }
	return $result;
}

/**
 * Function to save a record to a database from a form
 *
 * @param array $form_array = structured array definition for database
 * @param string $save_mode = "INSERT INTO " by default, "UPDATE "
 * @param integer $num_lines num of records to process if more than one
 */
function save_record($form_array,$save_mode="INSERT INTO ",$num_lines=1){
	global $UPLOADS_PATH;
	// Check if form have been displayed entirely in checking fields structure and the fields received...
	$e=check_fields($form_array);
	// Check fields rules
	$e.=check_fields_rule($form_array);
	if(!empty($e)){
		echo '<p style="color:red;">INSERT/UPDATE NOT DONE!<br/><br/>'.$e.'</p>';
		return;
	}
	//if($num_lines==0)$num_lines=1;
	for($j=0;$j<$num_lines;$j++){
	    $query="$save_mode ".$form_array[0]["table"]." SET ";
	    for ($i=1; $i<count($form_array);$i++){
			$fieldDef = $form_array[$i];
			$type = $fieldDef["type"];
	        $var_name = $fieldDef["name"];
			$var_data = TldDatabase::escape($_REQUEST[$var_name][$j]);
			//by pass field assignment if title,hyperlink,col_break or if new record
			//and field has no data in order to get default value from MySQL
	        if ($type=="title" || $type=="hyperlink" || $type=="col_break" || $type=="primary_key") {
                continue;
            }
			switch ($type){
				case 'costnull':
					if ($var_data === ''){
						$query .= "$var_name=null, ";
					}else{
						$query .= "$var_name='$var_data', ";
					}
				break;
		        //case locked_date:
		    	//case auto_date:
		    	case "locked_field":
				case "locked_date":
					//This is for bypassed fields
				break;
				case "password":
					//check if optional encrypted field is set
					if($fieldDef["encrypted_field"]){
						$query .= $fieldDef["encrypted_field"]."=ENCRYPT('$var_data'),";
					}
		            $query .= "$var_name='$var_data', ";
				break;
		    	case "date_type":
		    		if (empty($var_data)) {
                        break;
                    }
		            $query .= "$var_name='$var_data', ";
		    	break;
		        case "warranty_end_date":
		            if (empty($GLOBALS["date_registered"][$j])){
		                $query .= "$var_name= NULL, ";
		            	break;
		            }
		            $query .= "$var_name= DATE_ADD('".$GLOBALS["date_registered"][$j].
							"',INTERVAL ".$GLOBALS["warranty_length"][$j]." MONTH), ";
		    	break;
		    	case "textarea":
		            $query .= "$var_name='$var_data', ";
		    	break;
				case "scribble":
			    case "file_upload":
			        if(!isset($_FILES[$var_name]) || empty($_FILES[$var_name]['name'][$j])){
			            echo 'No file to upload';
			            break;
			        }
			        // Prepare destination
			        $upload_dir=$UPLOADS_PATH."/".$form_array[$i]["file_upload_dir"];
			        // check destination
			        if (!is_dir($upload_dir)){
			            echo "Destination path $upload_dir invalid";
                        continue 2;
			        }
			        // case of update, remove old file
			        if ($GLOBALS["orig_".$var_name][$j]<>""){
			            if(unlink($upload_dir."/".$GLOBALS["orig_".$var_name][$j])){
			                echo "Old file deleted";
			            }else{
			                echo "Could not delete old file";
			            }
			        }
			        // Prepare new filename
			        $timestamp=time();
			        $tempfilename = "$timestamp-".basicFile::cleanupName($_FILES[$var_name]['name'][$j]);
			        $destination = "$upload_dir/$tempfilename";
			        // Copy file
			        if(copy($_FILES[$var_name]['tmp_name'][$j],$destination)){
			            // Complete query to record filename
			            $query .= $var_name."='$tempfilename', ";
			        }else{
			            echo "Could not upload file on server. Destination: $destination";
			        }
		    	break;
		        default:
		           	$query .= "$var_name='$var_data', ";
	           	break;
	        }
	    }

	    //remove trailing comma and space
	    $query = substr($query,0,-2);


	    if( trim($save_mode) != "INSERT INTO" && isset($GLOBALS["id"][0])){
			$query .= " WHERE id=".$GLOBALS["id"][0]." LIMIT 1";
		}


	    if (TldDatabase::query($query) ==0 || $query=="$save_mode ".$form_array[0]["table"]." SET "){
	        echo "INSERT NOT DONE<br>, error=".TldDatabase::error()."\n".$query;
	        popup_message("INSERT NOT DONE, error=".TldDatabase::error()."\n".$query);
	    }else{
	        //echo "INSERT SUCCESSFUL<br>";
			if($save_mode == "INSERT INTO"){
				$id = TldDatabase::lastInsertId();
			}else{
				$id = $GLOBALS["id"][0];
			}
			if($form_array[0]["form_type"]=="main_tpl") {
                $error = $id;
            }
	        tldUtils::log_event($form_array[0]["title"].", INSERT id=".$id);
			//return TldDatabase::lastInsertId();
	    }
	}
	return $error;
}

/**
 * Function to delete a parent record and all child records
 *
 * @param integer $id id of record to delete
 * @param array $form_array Table definition array
 */
function del_record($id, $form_array){
//	if(!is_int($id)) return;
	$table_properties=$form_array[0];
	$table_name=$form_array[0]["table"];
	$upload_dir=$GLOBALS['UPLOADS_PATH'];

	foreach ($form_array as $field_data){
		if ($field_data["type"]=="file_upload"){
			$rows=TldDatabase::query("select * from $table_name where id=$id");
			$row=TldDatabase::fetchArray($rows);
			if ($row[$field_data["name"]]) {
                unlink("$upload_dir/".$field_data["file_upload_dir"]."/".$row[$field_data["name"]]);
            }
			//echo "$upload_dir/".$row[$field_data["name"]];
		}
	}
	if(!TldDatabase::query("DELETE FROM $table_name WHERE id=$id LIMIT 1")) {
        $error .= "DELETE FROM $table_name WHERE id=$id NOT DONE<br>";
    }
	if(isset($table_properties["child_tables"])){
	    foreach ($table_properties["child_tables"] as $child_table){
	        if(!TldDatabase::query("DELETE FROM $child_table WHERE parent_id=$id")) {
                $error .= "DELETE FROM $child_table WHERE parent_id=$id NOT DONE<br>";
            }
	    }
	}
	return $error ? $error : "RECORD DELETED";
}

/**
 * Function to duplicate whole record including child records
 *
 * @param integer $id = id of record to duplicate
 * @param array $form_array = table definition array
 */
//Argument definitions
function duplicate_record($id,$form_array){
	global $USER_PARAMS,$UPLOADS_PATH;

    $table=$form_array[0]["table"];
    $result=TldDatabase::query("SELECT * FROM $table WHERE id=".$id);
    $row=TldDatabase::fetchArray($result);
    foreach ($row as $key => &$data) {
        $row[$key] = str_replace("'", "''", $data);
    }
	foreach ($form_array as $field_data){
    	switch ($field_data["type"]){
			case 'primary_key':
				$row[$field_data["name"]]='';
			break;
			case 'auto_user':
				$row[$field_data["name"]]=$USER_PARAMS["email"];
			break;
			case 'file_upload':
	    		$timestamp=time();
				$orig_filename=$row[$field_data["name"]];
				if(empty($orig_filename)) {
                    break;
                }
		    	$upload_dir=$UPLOADS_PATH."/".$field_data["file_upload_dir"];
		    	if (!is_dir($upload_dir)){
					echo "Destination path $upload_dir invalid";
				continue 2;
		    	}
				$filename=strstr($orig_filename,"-");

	    		$tempfilename=$timestamp.$filename;
	    		copy("$upload_dir/$orig_filename","$upload_dir/$tempfilename");
				$row[$field_data["name"]]=$tempfilename;
			break;
			default:
				if(isset($field_data["dup_exc"])){
					switch($field_data["dup_exc"]){
						case 'clear':
							//$row[$field_data["name"]]='';
						break;
						default:
							$row[$field_data["name"]]=$field_data["dup_exc"];
					}
				}
		}
    }

    for ($i=1; $i<count($form_array);$i++){
    	if($form_array[$i]['name']<>'bypass'){
			$fields[] = $form_array[$i]['name'];
    	}
	}

    $query="INSERT INTO $table SET ".tldUtils::getSqlSet($row, $fields);
    if (TldDatabase::query($query)==0){
        return "<b>DUPLICATE NOT DONE</b><br>SQL ERROR was ".TldDatabase::error();
    }else{
        $new_id=TldDatabase::lastInsertId();
	    if (isset($form_array[0]["child_tables"])){
	        $child_list=$form_array[0]["child_tables"];
	        foreach ($child_list as $child_table){
                if (is_string($child_table)) {
                    duplicate_child_record($id,$new_id,$child_table);
                }
	        }
	    }
	    return $new_id;
	}
}

/**
 * Function to duplicate all child records given a parent id
 *
 * @param integer $id = foreign id
 * @param integer $new_id = id of newly created duplicate parent record
 * @param string $table = name of table to do the duplication in
*/
function duplicate_child_record($id,$new_id,$table){
    // get all child records
    $result = TldDatabase::query("SELECT * FROM $table WHERE parent_id=$id");
    if($result === false){
        popup_message(TldDatabase::error());
        return;
    }
    // duplicate each rows
    while($row = TldDatabase::fetchArray($result, MYSQLI_ASSOC) ){
        $row["id"]='';
        $row["parent_id"]=$new_id;
        $SET = tldUtils::getSqlSet(tldUtils::cleanupFormInput($row));
        $query="INSERT INTO $table SET $SET";
        if(TldDatabase::query($query) === false){
            popup_message(TldDatabase::error());
        }
    }
}

 /**
 * Function to check permissions for performing operations
 *
 * Argument definitions
 * @param int $perms_required = permission level require to perform the
 * @param string $table = name of table to check perms for
 * @param string $AUTH_USER userid of user to check perms for
 */
function check_perms($perms_require, $table, $AUTH_USER){
	$user = new tldUser($AUTH_USER);
	if($user->isInGroup("superuser")) {
        return true;
    }
    return $user->isInGroup($table) < $perms_require ? false : true;
}

 /**
 * Function to check permissions for performing operations
 *
 * Argument definitions
 * @param int $perms_required = permission level require to perform the
 * @param string $table = name of table to check perms for
 * @param string $AUTH_USER userid of user to check perms for
 */
function check_fields($form_array){
	$field_sent = array_keys($GLOBALS["_REQUEST"]);
	unset($form_array[0]);
	$missing_field = NULL;
	foreach($form_array as $field){
		if(!in_array($field["name"],$field_sent) && $field["name"]!="bypass"
		&& $field["type"]!="file_upload" && $field["type"]!="locked_field"
		&& $field["type"]!="locked_date"){
			// For the id when adding
			if($GLOBALS["_REQUEST"]["mode"]=="form_save" && $field["type"]=="primary_key") {
                continue;
            }
			$missing_field[]=$field["name"];
		}
	}
	if(!empty($missing_field)) {
        return "ERROR: Make sure the form have been totally displayed, missing field(s):<br/>".implode(", ", $missing_field);
    }
    else {
        return;
    }
}

/**
 * Function to check fields rule
 *
 * Argument definitions
 * @param $form_array - structure of the form
 * @return string error or TRUE
 */
function check_fields_rule($form_array){
	$field_sent = array_keys($GLOBALS["_REQUEST"]);
	unset($form_array[0]);
	$missing_field = NULL;
	foreach($form_array as $field){
		// Check if there is a rule on this field
		if(empty($field["rule"])) {
            continue;
        }
		if(empty($GLOBALS["_REQUEST"][$field["name"]]) && $field["rule"]!='required') {
            continue;
        }
		// Check exception
		if(in_array($field["name"],$field_sent) && $field["name"]!="bypass"
		&& !in_array($field["type"],array("file_upload","locked_field","locked_date"))){
			// For the id when adding
			if($GLOBALS["_REQUEST"]["mode"]=="form_save" && $field["type"]=="primary_key") {
                continue;
            }
			switch($field["rule"]){
				case 'decimal':
					$regexp = "/^[0-9]{0,6}?(\.[0-9]{0,2}){0,1}$/";
					if(!preg_match($regexp, $GLOBALS["_REQUEST"][$field["name"]][0])) {
                        $rule_field[] = $field["label"]." must be decimal";
                    }
				break;
				case 'numeric':
					if(!is_numeric($GLOBALS["_REQUEST"][$field["name"]][0])) {
                        $rule_field[] = $field["label"]." must be numeric";
                    }
				break;
				case 'required':
					if(empty($GLOBALS["_REQUEST"][$field["name"]][0])) {
                        $rule_field[] = $field["label"]." is required";
                    }
				break;
				case 'date':
					$regexp = "/^[12]{1}[0-9]{3}-(1[0-2]{1}|0[1-9]{1})-(3[0-1]{1}|[1-2]{1}[0-9]{1}|0[1-9]{1})$/";
					if(!preg_match($regexp, $GLOBALS["_REQUEST"][$field["name"]][0])) {
                        $rule_field[] = $field["label"]." must be a valid date (YYYY-MM-DD, from 1000-01-01 to 2999-12-31)";
                    }
				break;
			}
		}
	}
	if(!empty($rule_field)) {
        return "ERROR: ".implode("<br/>ERROR: ", $rule_field);
    }
    else {
        return;
    }
}

//Set a few defaults just in case
if(empty($max_rows)) {
    $max_rows = 15;
}
if(empty($offset)) {
    $offset = 0;
}
$main_table=$main_tpl[0]["table"];

switch ($mode) {
    case "record_view":
        if ($form_type=="main_tpl"){
            $MY_SESS[$main_table]["current_id"]=$id;
        }
		if($id==0){
			$body = prn_record($MY_SESS[$main_table]["current_id"],$form_array);
		}else{
			$body = prn_record($id,$form_array);
		}
    break;
    case "form_add":
		if($num_lines>10){
			$body .= "<p class=\"warning\">Max of 10 lines only</p>";
			$num_lines=10;
		}
 //       if(check_perms(1,$main_table,$PHP_AUTH_USER))
			$body = prn_form_add($id,$form_array,$num_lines);
    break;
    case "form_edit":
 //       if(check_perms(1,$main_table,$PHP_AUTH_USER)){
			$body = prn_form_edit($id,$form_array);
//	        tldUtils::log_event($form_array[0]["title"].", form, EDIT=".$id);
			//		}
    break;
    case "form_view":
        $body = prn_form_view($id,$form_array);
    break;
    case "form_search":
        $body = prn_form_search($form_array);
    break;
	case "form_email":
		$body = prn_email_form($id,$form_type);
	break;
    case "do_search":
        $MY_SESS[$running]["q"] = construct_where($form_array);
        $body = prn_table($main_tpl, 0, $max_rows);
    break;
    case "do_quick_search":
        $MY_SESS[$running]["q"]=construct_quick_where($form_array, $search_target);
        $body = prn_table($main_tpl, 0, $max_rows);
    break;
	case "do_related_search":
		$search_target=str_replace(' ','%',$search_target);
		$MY_SESS[$running]["adv_search_query"]="SELECT distinct $main_table.id,$main_table.* FROM $related_table,$main_table WHERE (";
		$related_table_tpl=$related_table."_tpl";
		$MY_SESS[$running]["adv_search_query"].=prepend_table_name($$related_table_tpl,$search_target);
		$MY_SESS[$running]["adv_search_query"]=$MY_SESS[$running]["adv_search_query"].") AND $main_table.id=$related_table.parent_id";
		//echo "query=".$MY_SESS[$running]["adv_search_query"];
        $body = prn_table($main_tpl,0,$max_rows);
	break;
    case "table":
        $body = prn_table($form_array,$MY_SESS[$running]["offset"],$max_rows);
    break;
    case "form_save":
//        if(check_perms(1,$main_table,$PHP_AUTH_USER)){
            $return=save_record($form_array,"INSERT INTO",$num_lines);
            tldUtils::log_event($form_array[0]["title"].", ADD id=".$return);
			if($return==0){
				//return=0 means did not insert
	            if ($form_type=="main_tpl"){ //If it's a main tpl then go back to table view
	   			     $body = prn_table($form_array,$MY_SESS[$running]["offset"],$max_rows);
			    }else{
					$body = prn_record($MY_SESS[$main_table]["current_id"],$main_tpl);
				}
			}else{
	            if ($form_type=="main_tpl"){
					$MY_SESS[$main_table]["current_id"]=$return;
				}
			    $body = prn_record($MY_SESS[$main_table]["current_id"],$main_tpl);
			}
//        }
    break;
    case "update":
//        if(check_perms(1,$main_table,$PHP_AUTH_USER)){
            save_record($form_array,"UPDATE IGNORE",1);
            tldUtils::log_event($form_array[0]["title"].", UPDATE id=".$id[0]);
//		    prn_record($MY_SESS[$main_table]["current_id"],$main_tpl);
		    $body = prn_record($id[0],$form_array);
//        }
    break;
    case "duplicate":
//        if(check_perms(1,$main_table,$PHP_AUTH_USER)){
			$return=duplicate_record($id,$form_array);
			if($return>0){
				$MY_SESS[$running]["current_id"]=$return;
			}else{
				popup_message("There was a problem duplicating, pls contact webmaster.$return");
			}
            tldUtils::log_event($form_array[0]["title"].", DUPLICATE id=".$return);
            $body = prn_record($MY_SESS[$main_table]["current_id"],$main_tpl);
//        }
    break;
    case "del":
//        if(!check_perms(2,$main_table,$PHP_AUTH_USER))
//			break;
		$tableDef = $$form_type;
		$query="SELECT * FROM ".$tableDef[0]["table"]." WHERE id=$id";
		$rows=TldDatabase::query($query);
		if(TldDatabase::numRows($rows)==1){
	       	tldUtils::log_event($form_array[0]["title"].", DELETE id=".$id);
	      	del_record($id,$form_array);
			if($form_type=="main_tpl"){
				$MY_SESS[$running]["offset"]=0;
				$body = prn_table($main_tpl,0,$max_rows);
			}else{
				$row=TldDatabase::fetchArray($rows);
		       	$body = prn_record($row["parent_id"],$main_tpl);
			}
		}else{
			$body .= "Error: id is not unique or does not exist";
		}
    break;
    case "email_view":
        $body = getPlainView($id,$form_array);
    break;
    case "plain_view":
        $body = getPlainView($id,$form_array,0);
    break;
    case "email":
    	$body .= "<br><font color=\"#FF0000\">".
        	email_record($emails, $id, $form_array, $PHP_AUTH_USER, $message, $subject).
        	"</font>";
		$body .= prn_record($id,$form_array);
    break;
    case "dump_list":
    	$body = prn_table($main_tpl, 0, 0, $MY_SESS[$running]);
    break;
    case "reset_all":
    	$MY_SESS[$running]=array();
    	$body =<<<EOF
    	<meta http-equiv="refresh" content="0;URL=$PHP_SELF?mode=table">
EOF;
    break;
    default:
        $body = prn_table($form_array, 0, $max_rows);
}

session_start();
if(!isset($_SESSION['sess'])) {
    $_SESSION['sess'] = null;
}
$sess =& $_SESSION['sess'];

$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

//*******************
//need to set $SMARTY_LOCATION and $DEFAULT_TEMPLATE in the including script
//*********************
if(empty($SMARTY_LOCATION) || empty($DEFAULT_TEMPLATE)){
	echo "ERROR: SMARTY_LOCATION or DEFAULT_TEMPATE not set...";
	exit;
}
$smarty = tldUtils::getSmarty($SMARTY_LOCATION);

if($lang){
	$smarty->assign("lang", $lang);
}
//$smarty->debugging=true;

$php_self = $_SERVER['PHP_SELF'];

$DEFAULT_TITLE = $form_array[0]["title"];
$DEFAULT_FOOTER = "TLDDB V2.0";

$smarty->assign("width", "100%");
$smarty->assign("menu",$DEFAULT_MENU.(isset($menu) ? $menu : ''));
if(count($DEFAULT_ERROR ?? [])){
    $smarty->assign("error", implode("<br>", $DEFAULT_ERROR));
}
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
