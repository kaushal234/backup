<?php

include_once("common.inc.php");
include_once("calendar.inc.php");
include_once("forms_and_reports.inc.php");

//function to show dialog box ask before deleting
?><script>
function checkanddelete(id,form_type) {
   if(confirm("Are you sure you want to delete?")) {
      window.location = "<?php echo $PHP_SELF;?>?mode=del&id="+id+"&form_type="+form_type;
   }
}
</script>
<?php

//Function to create record view from given table definition array
//If parent table has related child tables then table definition
//must have a table property entry $form_array[0]["child_tables"]
//$id = id for parent record
//$form_array = parent record table definition array
//
function prn_record($id,$form_array){
	global $PHP_SELF,$max_rows,$MY_SESS,$main_tpl;
//	$running=str_replace(".php","",basename($PHP_SELF));
    $form_array[0]["table"] = TldDatabase::escape($form_array[0]["table"]);
    $id = TldDatabase::escape($id);
    $rows = TldDatabase::query("SELECT * FROM ".$form_array[0]["table"]." WHERE id=".$id);
    if(empty($rows)){
        popup_message("CANNOT FIND RECORD ID=".$id);?>
        <meta http-equiv="refresh" content="0;URL=<?php echo $PHP_SELF?>?mode="><?php
        return;
    }
    $row=TldDatabase::fetchArray($rows);
    if(empty($row["id"])){
        popup_message("CANNOT FIND RECORD ID=".$id);?>
        <meta http-equiv="refresh" content="0;URL=<?php echo $PHP_SELF?>?mode="><?php
        return;
    }
	$parent_id=$row["parent_id"];
    $form_type=$form_array[0]["form_type"];
?>
    <h2><?php echo $form_array[0]["title"]?></h2>
    <table>
    <tr class="smalltext">
	<td>
		<?php echo str_replace("{id}",$id,$form_array[0]["prn_record_menu"])?>
		<?php
		if($form_type=="main_tpl"){
			?>
		<a href="<?php echo $PHP_SELF?>?mode=plain_view&form_type=<?php echo $form_type?>&id=<?php echo $id?>" target="blank">Plain View</a> |
		<a href="<?php echo $PHP_SELF?>?mode=duplicate&id=<?php echo $id?>">Duplicate</a> | <?php
		}
		?>
		<a href="<?php echo $PHP_SELF?>?mode=form_edit&form_type=<?php echo $form_type?>&id=<?php echo $id?>">Edit</a> |
		<a href="<?php echo $PHP_SELF?>?mode=form_email&form_type=<?php echo $form_type?>&id=<?php echo $id?>">Email</a> |
		<a href="javascript:checkanddelete('<?php echo $id?>','<?php echo $form_type?>')">Delete</a>
        <p>
    </td>
    <td>
	</td>
    </tr>
    </table>
    <a href="<?php echo $PHP_SELF?>?mode=<?php
	if($form_type=="main_tpl"){
		echo "table&form_type=";
	}else{
		echo "record_view&form_type=main_tpl&id=".$parent_id;
	}
	?>">Up</a> | <a href="javascript:history.back()">Back</a><?php
	prn_form_view($id,$form_array);
    if (isset($form_array[0]["child_tables"])){
        foreach ($form_array[0]["child_tables"] as $child_table_name){
            $child_table_name .= "_tpl";
            global $$child_table_name; // phpcs:ignore
            $child_array=$$child_table_name;
            ?><hr width="650"><?php
            prn_list_add($id,$child_array);
        }
    }
}

//Function to print control bar above each table
//$form_array = table definition array
//
function prn_sort_controls($form_array){
global $PHP_SELF,$MY_SESS;
$table=$form_array[0]["table"];

$sess_sort_order=$MY_SESS[$table]["sort_order"];
$sess_sort=$MY_SESS[$table]["sort"];
//prepend the PRN_TABLE_MENU
echo $form_array[0]["prn_table_menu"];
?>
		    <b><?php echo $form_array[0]["title"];?></b>
		    | <a href="<?php echo $PHP_SELF?>?mode=form_add&form_type=<?php echo $form_array[0]["form_type"]?>">Add</a>
			| <a href="<?php echo $PHP_SELF?>?mode=form_search&form_type=<?php echo $form_array[0]["form_type"]?>">Detailed Search</a>
			| <a href="<?php echo $PHP_SELF?>?mode=reset_all">Reset</a>
			| <a href="<?php echo $PHP_SELF?>?mode=download_csv&form_type=main_tpl">Download</a>
			| <a href="<?php echo $PHP_SELF?>?mode=dump_list">List All</a>
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

function prn_email_form($id,$form_type){
	global $PHP_SELF,$PHP_AUTH_USER;
    $email_drop_object = array(
        "name"=>"emails[]","label"=>"Email Address","type"=>"select_db","options"=>"size=\"10\" MULTIPLE","table"=>"false",
        "select_query"=>"SELECT CONCAT(lastname,', ',firstname,', ',(SELECT location FROM locations WHERE people.bu_id=locations.id)) AS fullname, email FROM people WHERE hidden<>1 AND disabled='N' ORDER BY fullname",
        "select_field_1"=>"email","select_field_2"=>"fullname"
    );

?>
<SCRIPT LANGUAGE="JavaScript">
// Compare two options within a list by VALUES
function compareOptionValues(a, b)
{
  // Radix 10: for numeric values
  // Radix 36: for alphanumeric values
  var sA = parseInt( a.value, 36 );
  var sB = parseInt( b.value, 36 );
  return sA - sB;
}
// Compare two options within a list by TEXT
function compareOptionText(a, b)
{
  // Radix 10: for numeric values
  // Radix 36: for alphanumeric values
  var sA = parseInt( a.text, 36 );
  var sB = parseInt( b.text, 36 );
  return sA - sB;
}
// Dual list move function
function moveDualList( srcList, destList, moveAll )
{
  // Do nothing if nothing is selected
  if (  ( srcList.selectedIndex == -1 ) && ( moveAll == false )   )
  {
    return;
  }
  newDestList = new Array( destList.options.length );
  var len = 0;
  for( len = 0; len < destList.options.length; len++ )
  {
    if ( destList.options[ len ] != null )
    {
      newDestList[ len ] = new Option( destList.options[ len ].text, destList.options[ len ].value, destList.options[ len ].defaultSelected, destList.options[ len ].selected );
    }
  }
  for( var i = 0; i < srcList.options.length; i++ )
  {
    if ( srcList.options[i] != null && ( srcList.options[i].selected == true || moveAll ) )
    {
       // Statements to perform if option is selected
       // Incorporate into new list
       newDestList[ len ] = new Option( srcList.options[i].text, srcList.options[i].value, srcList.options[i].defaultSelected, srcList.options[i].selected );
       len++;
    }
  }
  // Sort out the new destination list
  //newDestList.sort( compareOptionValues );   // BY VALUES
  //newDestList.sort( compareOptionText );   // BY TEXT
  // Populate the destination with the items from the new array
  for ( var j = 0; j < newDestList.length; j++ )
  {
    if ( newDestList[ j ] != null )
    {
      destList.options[ j ] = newDestList[ j ];
    }
  }
  // Erase source list selected elements
  for( var i = srcList.options.length - 1; i >= 0; i-- )
  {
    if ( srcList.options[i] != null && ( srcList.options[i].selected == true || moveAll ) )
    {
       // Erase Source
       //srcList.options[i].value = "";
       //srcList.options[i].text  = "";
       srcList.options[i]       = null;
    }
  }
} // End of moveDualList()
//  End -->
</script>
<h2>Email Form</h2>
<a href="<?php echo $PHP_SELF?>?mode=record_view&form_type=<?php echo $form_type;?>&id=<?php echo $id?>">Cancel</a>
<form ACTION="<?php echo $PHP_SELF?>" METHOD="POST" name="myForm">
		<input type="hidden" name="mode" value="email">
		<input type="hidden" name="id" value="<?php echo $id?>">
		<input type="hidden" name="form_type" value="<?php echo $form_type;?>">
		<textarea name="message" wrap="VIRTUAL" cols="65" rows="5" align="left">Type your message here:</textarea>
		<br>

		N.B. You may select up to 10 people. A copy will be sent automatically to yourself.<br>
<table border="0">
<tr><td class="table_title">Address book</td><td>&nbsp;</td><td class="table_title">Recipients</td></tr>
<tr>
  <td>
    <select multiple size="10" style="width:220" name="addressbook">
		<?php
		$query="SELECT CONCAT(lastname,', ',firstname,', ',(SELECT location FROM locations WHERE people.bu_id=locations.id)) AS fullname,email FROM people WHERE hidden<>1 AND disabled='N' ORDER BY fullname";
		$rows=TldDatabase::query($query);
		while($row=TldDatabase::fetchArray($rows)){
			?><option value="<?php echo $row["email"]?>"><?php echo $row["fullname"]?></option><?php
		}
		?>
    </select>
  </td>
  <td>
    <input type="button" style="width:90" onclick="moveDualList(this.form.addressbook, this.form['emails[]'], false )" name="Add ->>"  value="Add ->>"><BR>
    <NOBR>
    <input type="button" style="width:90" onclick="moveDualList( this.form['emails[]'], this.form.addressbook,  false )" name="<<- Remove"  value="<<- Remove"><BR>
    <NOBR>
    <br><br><input type="submit" style="width:90" name="Submit" value="Email It">
  </td>
  <td>
    <select multiple size="10" style="width:220" name="emails[]">
    </select>
  </td>
</tr>
</table>
</form>

<?php
}

//Function to retrieve record view from itself into a temp file then
//email as an HTML doc to $AUTHUID assuming $AUTHUID is still
//email address used for login
//
function email_record($emails,$id,$form_array,$from,$message,$subject){
	global $PHP_SELF,$PHP_AUTH_USER;
	if (empty($emails)){

		return "Pls select at least one email address";
	}
	if (!empty($emails)){
		if (count($emails ?? [])>20){
			return "Max. of 20 users per email, message not sent";
		}
		foreach ($emails as $email){
			$recipients .= $email.",";
		}
	}
	$recipients .= $PHP_AUTH_USER;
	echo $recipients;
	$message=nl2br(stripslashes($message));
	$fcontents = getPlainView($id, $form_array, true);
	tldUtils::emailAttachment(
        $recipients,
        $from,
        $form_array[0]["title"]." Record ID:".$id.", ".$subject,
        $message."\n\n".$fcontents
    );

	return "A copy has been sent to the above recipients.";
}

//Function to print a list from a database plus a quick add form at bottom
//Argument definitions
//$id = int, parent id to match
//$form_array = structured array definition for database
//
function prn_list_add($id,$form_array){
	global $PHP_SELF;
	$query="SELECT * FROM ".$form_array[0]["table"]." WHERE parent_id='".$id."'";
	if($form_array[0]["default_sort"]) {
        $query.=" ORDER BY ".$form_array[0]["default_sort"];
    }
	$result = TldDatabase::query($query);
	$num_rows = TldDatabase::numRows($result);
	?>
	<form method="post" action="<?php echo $PHP_SELF?>" ENCTYPE="multipart/form-data">
	<input type="hidden" name="mode" value="form_add">
	<input type="hidden" name="form_type" value="<?php echo $form_array[0]["form_type"]?>">
	<input type="hidden" name="id" value="<?php echo $id?>">
	<b><?php echo $form_array[0]["title"]?></b> | Add
	<input type="text" name="num_lines" size="2" value="1">lines
	<input type="submit" name="Go" value="Go">
	</form>
	<?php
	if ($num_rows==0){
		echo "NO RECORDS";
		return;
	}
	?>
  <table width="700" bgcolor="#CCCCCC" border="1" bordercolor="#FFFFFF" cellspacing="0" cellpadding="1" class="smalltext">
	<tr bgcolor="#3264C8" class="smallwhite"><?php
	  for ($i=1; $i<count($form_array);$i++){
	    if ($form_array[$i]["table"]<>"true")continue;?>
        <td><?php echo $form_array[$i]["label"]?></td><?php
	  }?>
	  <td>&nbsp;</td>
	</tr><?php
	    while($row=TldDatabase::fetchArray($result)){
	    if (!$row)break;?>
    <tr><?php
	  for ($j=1; $j<count($form_array);$j++){
	  if ($form_array[$j]["table"]=="false")continue;?>
        <td><?php
          prn_view_object($form_array[$j],$row);?>
		</td><?php
	  }?>
      <td><a href="<?php echo $PHP_SELF?>?mode=record_view&form_type=<?php echo $form_array[0]["form_type"]?>&id=<?php echo $row["id"]?>">Display</a></td>
	</tr><?php
    }?>
  </table><?php
}

//Function to print a form to edit data in a database
//Argument definitions
//$id = int, id of record to edit
//$form_array = structured array definition for database
//
function prn_form_edit($id,$form_array){
global $PHP_SELF;
$result = TldDatabase::query("SELECT * FROM ".$form_array[0]["table"]." WHERE id=".$id);
$row=TldDatabase::fetchArray($result);
?>
<!--<a href="javascript:history.back()">Back</a>-->
<a href="<?php echo $PHP_SELF?>?mode=record_view&form_type=<?php echo $form_array[0]["form_type"]?>&id=<?php echo $id?>">Cancel</a>
<form method="post" action="<?php echo $PHP_SELF?>" ENCTYPE="multipart/form-data">
<input type="submit" name="Submit" value="Submit">
<input type="reset" name="Submit3" value="Reset">
<input type="hidden" name="mode" value="update">
<input type="hidden" name="form_type" value="<?php echo $form_array[0]["form_type"]?>">
<input type="hidden" name="id[0]" value="<?php echo $row["id"]?>">
<input type="hidden" name="parent_id[0]" value="<?php echo $row["parent_id"]?>">
    <table width="700" border="1" cellspacing="0" cellpadding="1" class="smalltext" bordercolor="#FFFFFF"><?php
          for ($i=1; $i<count($form_array);$i++){
          $var_name=$form_array[$i]["name"];
          $type=$form_array[$i]["type"];
            if ($type=="foreign_key" || $type=="hyperlink" || $type=="col_break")continue;?>
      <tr>
        <td bgcolor="#3264C8" class="smallwhite"><?php echo $form_array[$i]["label"];?></td>
                <td bgcolor="#CCCCCC"><?php
          prn_edit_object($form_array[$i],$row);?>
            </td>
          </tr><?php
      };?>
        </table>
  <input type="submit" name="Submit" value="Submit">
  <input type="reset" name="Submit3" value="Reset">
</form><?php
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
			case 'primary_key':
				echo $value;
			break;
            case 'title':
                echo "&nbsp;";
            break;
            case 'locked_field':
            case 'auto_password':?>
            <input type="hidden" name="<?php echo $var_name?>" value="<?php echo $value?>"><?php
                echo $value;
            break;
            case 'auto_user':?>
            <input type="hidden" name="<?php echo $var_name?>" value="<?php echo $value?>"><?php echo $value;
            break;
            case 'locked_date':
            case 'warranty_end_date':?>
            <input type="hidden" name="<?php echo $var_name?>" value="<?php echo $value?>"><?php
                if (empty($value)){
                  echo "&nbsp;";
                }else{
                   // echo str_replace("-","/",$row[$var_name]);
                    echo $value;
                }
            break;
            case 'reg_details':?>
            <input type="hidden" name="<?php echo $var_name?>" value="<?php echo $value?>"><?php
                if (empty($value)){
                  echo "&nbsp;";
                }else{
                    prn_subrecord($value,$form_object["select_query_view_tpl"]);
                }
            break;
			case 'courier':
            case 'textarea':
                $textareaVal = stripslashes(preg_replace('/<br\s*\/?\s*>/i',"",$value)); ?>
                <textarea name="<?php echo $var_name?>"<?php echo $form_object["textarea_params"]?>><?php echo $textareaVal; ?></textarea><?php
            break;
            case 'select':
				switch($form_object["source"]){
				case 'lists':
					$select_list = tldList::optionsByListNameAsListItemListItem($form_object["list_name"]);
					?>
	                <select name="<?php echo $var_name?>">
	                <option value="<?php echo $value?>"><?php echo $value?></option><?php
	                foreach($select_list as $select_key=>$select_item){
	        	        ?><option value="<?php
	        	        if($select_key){
	        	        	echo $select_key;
	        	        }else{
	        	        	echo $select_item;
	        	        }
	        	        ?>" <?php if ($select_key==$value)echo "selected";?>>
						<?php echo $select_item?></option><?php
	                }
	                ?>
		            </select><?php
				break;
				case 'smartyOptions':
					$select_list = $form_object["select_list"];
					?>
	                <select name="<?php echo $var_name?>">
	                <option value="<?php echo $value?>"><?php echo $value?></option><?php
	        	    foreach($select_list as $select_key=>$select_value){?>
	        	        <option <?php if ($select_value==$value) echo "selected";?> value="<?php echo $select_key?>">
						<?php echo $select_value?></option>
	    	        <?php }?>
		            </select><?php
				break;
				default:
					$select_list = $form_object["select_list"];
					?>
	                <select name="<?php echo $var_name?>">
	                <option value="<?php echo $value?>"><?php echo $value?></option><?php
	        	    for ($j=0; $j<count($select_list);$j++){?>
	        	        <option <?php if ($select_list[$j]==$value)echo "selected";?>>
						<?php echo $select_list[$j]?></option>
	    	        <?php }?>
		            </select><?php
				}
            break;
            case 'lookup':
            case 'select_db':?>
                <select name="<?php echo $var_name?>">
                <option value="<?php if (!empty($value))echo $value?>"><?php if (!empty($value))echo $value?></option><?php
                $select_list = tldUtils::getSqlToAssocArray($form_object["select_query"]);
				if(count($select_list)){
					foreach($select_list as $select_row){?>
						<option value="<?php echo $select_row[$form_object["select_field_1"]]?>" <?php if ($select_row[$form_object["select_field_1"]]==$value)echo "selected";?>>
						<?php echo $select_row[$form_object["select_field_2"]]?></option>
					<?php
					}
				}
                ?></select><?php
            break;
            case 'select_dbTEMP':?>
                <select name="<?php echo $var_name?>">
                <option value="<?php if (!empty($value))echo $value?>"><?php if (!empty($value))echo $value?></option><?php
                $select_list = TldDatabase::query($form_object["select_query"]);
                while($select_row=TldDatabase::fetchArray($select_list)){?>
                    <option value="<?php echo $select_row[$form_object["select_field_1"]]?>" <?php if ($select_row[$form_object["select_field_1"]]==$value)echo "selected";?>>
					<?php echo $select_row[$form_object["select_field_2"]]?></option>
                <?php }?>
                </select><?php
            break;
            case 'hidden':?>
                <input type="hidden" name="<?php echo $var_name?>" value="<?php echo $value?>" size="40"><?php echo $value;
            break;
            case 'file_upload':
				?>
            	<input type="hidden" name="orig_<?php echo $var_name?>" value="<?php echo $value?>">
            	Original filename: <?php echo $value?><br>
				<input type="file" name="<?php echo $var_name?>"><?php
            break;
            default:?>
                <input type="<?php echo $form_object["type"]?>" name="<?php echo $var_name?>" value="<?php echo stripslashes(htmlspecialchars($value))?>" size="<?php echo empty($form_object["width"]) ? 50 : $form_object["width"]?>"><?php
      }
}

//Function to save a record to a database from a form
//Argument definitions
//$form_array = structured array definition for database
//$save_mode = "INSERT INTO " by default, "UPDATE "
//
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

	for($j=0;$j<$num_lines;$j++){
	    $query="$save_mode ".$form_array[0]["table"]." SET ";
	    for ($i=1; $i<count($form_array);$i++){
			$fieldDef = $form_array[$i];
			$type = $fieldDef["type"];
	        $var_name = $fieldDef["name"];
			$var_data = TldDatabase::escape($_REQUEST[$var_name][$j]);
			//by pass field assignment if title,hyperlink,col_break or if new record
			//and field has no data in order to get default value from MySQL
	        if ($type=="title" || $type=="hyperlink" || $type=="col_break" || $type=="primary_key") continue;
			switch ($type){
		        //case locked_date:
		    	//case auto_date:
				case 'locked_date':
					//This is for bypassed fields
				break;
				case 'password':
					//check if optional encrypted field is set
					if($fieldDef["encrypted_field"]){
						$query .= $fieldDef["encrypted_field"]."=ENCRYPT('$var_data'),";
					}
		            $query .= "$var_name='$var_data', ";
				break;
		    	case 'date_type':
		    		if (empty($var_data))break;
		            $query .= "$var_name='$var_data', ";
		    	break;
		        case 'warranty_end_date':
		            if (empty($GLOBALS["date_registered"][$j])){
		                $query .= "$var_name= NULL, ";
		            	break;
		            }
		            $query .= "$var_name= DATE_ADD('".$GLOBALS["date_registered"][$j].
							"',INTERVAL ".$GLOBALS["warranty_length"][$j]." MONTH), ";
		    	break;
		    	case 'textarea':
		            $query .= "$var_name='$var_data', ";
		    	break;
				case 'scribble':
		    	case 'file_upload':
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
	        }
	    }

	    //remove trailing comma and space
	    $query = substr($query,0,-2);

        if(trim($save_mode) != "INSERT INTO" && isset($GLOBALS["id"][0])){
            $query .= " WHERE id=".$GLOBALS["id"][0]." LIMIT 1";
        }

	    if (TldDatabase::query($query) ==0 || $query=="$save_mode ".$form_array[0]["table"]." SET "){
	        echo "INSERT NOT DONE<br>, error=".TldDatabase::error()."\n".$query;
	        popup_message("INSERT NOT DONE, error=".TldDatabase::error()."\n".$query);
	    }else{
            if($save_mode == "INSERT INTO"){
                  $id = TldDatabase::lastInsertId();
            }else{
                  $id = $GLOBALS["id"][0];
            }
            if($form_array[0]["form_type"]=="main_tpl") $error=$id;
            tldUtils::log_event($form_array[0]["title"].", INSERT id=".$id);
        }
    }

	return $error;
}

//Function to delete a parent record and all child records
//
function del_record($id,$form_array){
	$table_properties=$form_array[0];
	$table_name=$form_array[0]["table"];
  $upload_dir=$GLOBALS['UPLOADS_PATH'];

	foreach ($form_array as $field_data){
		if ($field_data["type"]=="file_upload"){
			$rows=TldDatabase::query("select * from $table_name where id=$id");
			$row=TldDatabase::fetchArray($rows);
			if ($row[$field_data["name"]])
				unlink("$upload_dir/".$field_data["file_upload_dir"]."/".$row[$field_data["name"]]);
		}
	}
	if(!TldDatabase::query("DELETE FROM $table_name WHERE id=$id LIMIT 1"))
		echo "DELETE FROM $table_name WHERE id=$id NOT DONE<br>";
	if(isset($table_properties["child_tables"])){
	    foreach ($table_properties["child_tables"] as $child_table){
	        if(!TldDatabase::query("DELETE FROM $child_table WHERE parent_id=$id"))
				echo "DELETE FROM $child_table WHERE parent_id=$id NOT DONE<br>";
	    }
	}
	?>
	RECORD DELETED
	<?php
}

//Function to duplicate whole record including child records
//Argument definitions
//$id = id of record to duplicate
//$form_array = table definition array
function duplicate_record($id,$form_array){
	global $USER_PARAMS,$UPLOADS_PATH;

    $table=$form_array[0]["table"];
    $result=TldDatabase::query("SELECT * FROM $table WHERE id=".$id);
    $row = TldDatabase::fetchArray($result);

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
				if(empty($orig_filename))break;
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
							$row[$field_data["name"]]='';
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

    if(TldDatabase::query($query)==0){
        echo "<b>DUPLICATE NOT DONE</b><br>";
        popup_message(TldDatabase::error());
		return -1;
    }else{
        //echo "<b>DUPLICATE SUCCESSFUL</b><br>";
		echo ".";
        $new_id=TldDatabase::lastInsertId();
	    if (isset($form_array[0]["child_tables"])){
	        $child_list=$form_array[0]["child_tables"];
	        foreach ($child_list as $child_table){
	            (duplicate_child_record($id,$new_id,$child_table));
	        }
	    }
	    return $new_id;
	}
}

//Function to duplicate all child records given a parent id
//$id = foreign id
//$new_id = id of newly created duplicate parent record
//$table = name of table to do the duplication in
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

//
//PALM FUNCTIONS
//


//Function to create the record view for PALMs
//Removes the quick add forms at bottom of page by using function
//prn_list() instead of prn_list_add()
//
function prn_palm_view($id,$form_array){
    global $PHP_SELF,$max_rows,$sess_show;
    $result = TldDatabase::query("SELECT * FROM ".$form_array[0]["table"]." WHERE id=".$id);
    //echo "SELECT * FROM ".$form_array[0]["table"]." WHERE id=".$id;
    if (empty($result)){
          echo "CANNOT FIND RECORD ID=".$id;
          exit;
    }
    ?><div align="left">
    <h3><?php echo $form_array[0]["title"]?></h3><p><?php
	prn_palm_list($form_array,$result);

    if (isset($form_array[0]["child_tables"])){
	    foreach ($form_array[0]["child_tables"] as $child_table){
	        $child_table .= "_tpl";
	        global $$child_table; // phpcs:ignore
	        $child_table_params=${$child_table}[0];
			$result = TldDatabase::query("SELECT * FROM ".$child_table_params["table"]." WHERE parent_id='$id'");
			if(TldDatabase::numRows($result)>0){
				?>
		        <h4><?php echo $child_table_params["title"]?></h4>
				<?php prn_palm_list($$child_table,$result);
			}
	    }
    }
	?></div><?php
}

//Function to print a list from a database for PALM view
//Argument definitions
//$id = int, parent id to match
//$form_array = structured array definition for database
//
//function prn_list($id,$form_array){
function prn_palm_list($form_array,$result){
global $PHP_SELF;
//	$result = TldDatabase::query("SELECT * FROM ".$form_array[0]["table"]." WHERE id='$id'");
	while($row=TldDatabase::fetchArray($result)){
	   	for ($i=1; $i<count($form_array);$i++){
			if($form_array[$i]["name"]=="parent_id")continue;
	   		if ($form_array[$i]["name"]=="bypass"){
				if($form_array[$i]["type"]=="col_break")continue;
	   			?><hr><b><i><?php echo $form_array[$i]["label"]?></i></b><br><?php
	   		}else{
	   			?><b><?php echo $form_array[$i]["label"]?></b><?php
		   		echo " : ".stripslashes($row[$form_array[$i]["name"]])?><br><?php
			}
		}
	?><hr><?php
	}
}

//Debugging function
//Print out all $_POST
function dump_post_vars(){
	foreach($_POST as $key => $val) {
	    echo "$key => $val<br>";
	}
}
//Function to check permissions for performing operations
//Argument definitions
//$perms_required = permission level require to perform the
//$perms = permission level of current user
//
function check_perms($perms_require,$table,$AUTH_USER){
	$myUser = new tldUser($AUTH_USER);
	if($myUser->isInGroup("superuser"))
		return 1;
	$perms = $myUser->isInGroup($table);
    if ($perms < $perms_require){
		?>
        <script>
        alert("You don't have authorization to perform the function");
        </script>
        <meta http-equiv="refresh" content="0;URL=javascript:history.back()"><?php
        return 0;
    }else{
        return 1;
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
function check_fields($form_array){
	$field_sent = array_keys($GLOBALS["_REQUEST"]);
	unset($form_array[0]);
	$missing_field = NULL;
	foreach($form_array as $field){
		if(!in_array($field["name"],$field_sent) && $field["name"]!="bypass"
		&& $field["type"]!="file_upload" && $field["type"]!="locked_field"
		&& $field["type"]!="locked_date"){
			// For the id when adding
			if($GLOBALS["_REQUEST"]["mode"]=="form_save" && $field["type"]=="primary_key") continue;
			$missing_field[]=$field["name"];
		}
	}
	if(!empty($missing_field))
		return "ERROR: Make sure the form have been totally displayed, missing field(s):<br/>".implode(", ",$missing_field);
    else return;
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
		if(empty($field["rule"])) continue;
		if(empty($GLOBALS["_REQUEST"][$field["name"]]) && $field["rule"]!='required') continue;
		// Check exception
		if(in_array($field["name"],$field_sent) && $field["name"]!="bypass"
		&& !in_array($field["type"],array("file_upload","locked_field","locked_date"))){
			// For the id when adding
			if($GLOBALS["_REQUEST"]["mode"]=="form_save" && $field["type"]=="primary_key") continue;
			switch($field["rule"]){
				case 'decimal':
					$regexp = "/^[0-9]{0,6}?(\.[0-9]{0,2}){0,1}$/";
					if(!preg_match($regexp, $GLOBALS["_REQUEST"][$field["name"]][0]))
						$rule_field[]=$field["label"]." must be decimal";
				break;
				case 'numeric':
					if(!is_numeric($GLOBALS["_REQUEST"][$field["name"]][0]))
						$rule_field[]=$field["label"]." must be numeric";
				break;
				case 'required':
					if(empty($GLOBALS["_REQUEST"][$field["name"]][0]))
						$rule_field[]=$field["label"]." is required";
				break;
				case 'date':
					$regexp = "/^[12]{1}[0-9]{3}-(1[0-2]{1}|0[1-9]{1})-(3[0-1]{1}|[1-2]{1}[0-9]{1}|0[1-9]{1})$/";
					if(!preg_match($regexp, $GLOBALS["_REQUEST"][$field["name"]][0]))
						$rule_field[]=$field["label"]." must be a valid date (YYYY-MM-DD, from 1000-01-01 to 2999-12-31)";
				break;
			}
		}
	}
	if(!empty($rule_field))
		return "ERROR: ".implode("<br/>ERROR: ",$rule_field);
    else return;
}


//Set a few defaults just in case
if(empty($max_rows))$max_rows=15;
if(empty($offset))$offset=0;
$main_table=$main_tpl[0]["table"];
$user = new tldUser($GLOBALS['PHP_AUTH_USER']);
$USER_PARAMS = $user->getHeader();

switch ($mode) {
    case 'record_view':
        if ($form_type=="main_tpl"){
            $MY_SESS[$main_table]["current_id"]=$id;
        }
		if($id==0){
			prn_record($MY_SESS[$main_table]["current_id"],$form_array);
		}else{
			prn_record($id,$form_array);
		}
    break;
    case 'form_add':
		if($num_lines>30){
			echo"<p class=\"warning\">Max of 30 lines only</p>";
			$num_lines=30;
		}
        prn_form_add($id,$form_array,$num_lines);
    break;
    case 'form_edit':
        tldUtils::log_event($form_array[0]["title"].", form, EDIT=".$id);
        prn_form_edit($id,$form_array);
    break;
    case 'form_view':
        prn_form_view($id,$form_array);
    break;
    case 'form_search':
        prn_form_search($form_array);
    break;
	case 'form_email':
		prn_email_form($id,$form_type);
	break;
    case 'do_search':
        $MY_SESS[$running]["q"] = construct_where($form_array);
        prn_table($main_tpl,0,$max_rows);
    break;
    case 'do_quick_search':
        $MY_SESS[$running]["q"]=construct_quick_where($form_array,$search_target);
        prn_table($main_tpl,0,$max_rows);
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
    case 'form_save':
        $return=save_record($form_array,"INSERT INTO",$num_lines);
        tldUtils::log_event($form_array[0]["title"].", ADD id=".$return);
        if($return==0){
            //return=0 means did not insert
            if ($form_type=="main_tpl"){ //If it's a main tpl then go back to table view
                 prn_table($form_array,$MY_SESS[$running]["offset"],$max_rows);
            }else{
                prn_record($MY_SESS[$main_table]["current_id"],$main_tpl);
            }
        }else{
            if ($form_type=="main_tpl"){
                $MY_SESS[$main_table]["current_id"]=$return;
            }
            prn_record($MY_SESS[$main_table]["current_id"],$main_tpl);
        }
    break;
    case 'update':
        save_record($form_array,"UPDATE IGNORE",1);
        tldUtils::log_event($form_array[0]["title"].", UPDATE id=".$id[0]);
        prn_record($id[0],$form_array);
    break;
    case 'duplicate':
        $return=duplicate_record($id,$form_array);
        if($return>0){
            $MY_SESS[$running]["current_id"]=$return;
        }else{
            popup_message("There was a problem duplicating, pls contact webmaster.");
        }
        tldUtils::log_event($form_array[0]["title"].", DUPLICATE id=".$return);
        prn_record($MY_SESS[$main_table]["current_id"],$main_tpl);
    break;
    case 'del':
		$tableDef = $$form_type;
		$query="SELECT * FROM ".$tableDef[0]["table"]." WHERE id=$id";
		$rows=TldDatabase::query($query);
		if(TldDatabase::numRows($rows)==1){
	       	tldUtils::log_event($form_array[0]["title"].", DELETE id=".$id);
	      	del_record($id,$form_array);
			if($form_type=="main_tpl"){
				$MY_SESS[$running]["offset"]=0;
				prn_table($main_tpl,0,$max_rows);
			}else{
				$row=TldDatabase::fetchArray($rows);
		       	prn_record($row["parent_id"],$main_tpl);
			}
		}else{
			echo "Error: id is not unique or does not exist";
		}
    break;
    case 'email_view':
        prn_email_view($id,$form_array);
    break;
    case 'plain_view':
        prn_email_view($id,$form_array,0);
    break;
    case 'palm_view':
        prn_palm_view($id,$form_array);
    break;
    case 'email':
        echo "<br><font color=\"#FF0000\">".email_record($emails,$id,$form_array,$PHP_AUTH_USER,$message,$subject)."</font>";
        prn_record($id,$form_array);
    break;
    case 'dump_list':
    	prn_table($main_tpl,0,0,$MY_SESS[$running]);
    break;
    case 'reset_all':
    	$MY_SESS[$running]=array();
    	?><meta http-equiv="refresh" content="0;URL=<?php echo $PHP_SELF?>?mode=table"><?php
    break;
    default:
        prn_table($form_array,0,$max_rows);
}
