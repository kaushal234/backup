<?php

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

    echo "$offset/$total_records - ";
    if ($offset>0){?>
        <a href="<?php echo $PHP_SELF?>?mode=table&offset=0">
            <?php echo prn_icon("start")?></a> <?php
    }
    /*else{
        echo prn_icon("start");
    }*/
    //Print Previous page button
    if (($offset-$max_rows)>=0){?>
        <a href="<?php echo $PHP_SELF?>?mode=table&offset=<?php echo ($offset-$max_rows)?>"><?php echo prn_icon("previous")?></a>
        <?php
    }elseif($offset>0 && $offset<$max_rows){?>
        <a href="<?php echo $PHP_SELF?>?mode=table&offset=<?php echo ($offset-$max_rows)?>"><?php echo prn_icon("previous")?></a>
        <?php
    }
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    /*else{
        echo prn_icon("previous");
    }*/
    //Print quick access number buttons
    if ($total_records > $max_rows*10){
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

    //Print next page button
    if (($offset+$max_rows)<$total_records){?>
    <a href="<?php echo $PHP_SELF?>?mode=table&offset=<?php echo ($offset+$max_rows)?>">
        <?php echo prn_icon("next")?></a><?php
    }
    /*else{
        echo prn_icon("next");
    }*/
    if (($offset+$max_rows)<$total_records){?>
    <a href="<?php echo $PHP_SELF?>?mode=table&offset=<?php echo ($total_records-$max_rows)?>">
        <?php echo prn_icon("end")?></a><?php
    }
    /*else{
        echo prn_icon("end");
    }*/
}


//Function to print a single page from a database
//Argument definitions
//$offset = int, position within list
//$total_records = int, sum of all records within FOUND SET
//$max_rows = int, maximum number of rows to print per page
//			if $max_rows=0 the print the entire list i.e. $max_rows=$total_records
function prn_table($form_array,$offset=0,$max_rows=15){
    global $PHP_SELF,$MY_SESS;
    $table=$form_array[0]["table"];

    $sess_sort=$MY_SESS[$table]["sort"];
    $sess_sort_order=$MY_SESS[$table]["sort_order"];
    $sess_q=$MY_SESS[$table]["q"];
    $sess_adv_search_query=$MY_SESS[$table]["adv_search_query"];

    if($sess_adv_search_query){
        $query_count=$sess_adv_search_query;
    }else{
        $query_count="SELECT * FROM $table $sess_q";
    }
    $result_count=TldDatabase::query($query_count);
    if (!$result_count){
        $MY_SESS[$table]["adv_search_query"]="";
        $MY_SESS[$table]["q"]="";
        ?><p class="alert">DATABASE ERROR:
        <?php echo $query_count?>
        <br><?php echo TldDatabase::error()?>
        <br>PLEASE REPORT THIS TO THE WEBMASTER
        <a href="<?php echo $PHP_SELF?>?mode=reset_all">Click here to reset</a>
        </p>
        <?php
        exit;
    }

    $total_records=TldDatabase::numRows($result_count);
    if ($max_rows==0)$max_rows=$total_records;

    if($sess_adv_search_query){
        $query=$sess_adv_search_query." ORDER BY $sess_sort $sess_sort_order LIMIT $offset,$max_rows";
    }else{
        $query="SELECT * FROM $table $sess_q ORDER BY $sess_sort $sess_sort_order LIMIT $offset,$max_rows";
    }

//	$query.=$form_array[0]["restrict"];
    if($total_records==0){
        ?><p class="alert">No records found in query.</p><?php
    }
    $result=TldDatabase::query($query);
//    echo $query;
    if ($result==0){
        $error= "DATABASE ERROR:";
        $error.= $query;
        $error.="<br>".TldDatabase::error();
        $error.="PLEASE REPORT THIS TO THE WEBMASTER";
        ?>
        <script>
          alert("<?php echo $error?>");
        </script>
        <meta http-equiv="refresh" content="0;URL=<?php echo $PHP_SELF?>?mode=reset_all">
        <?php
    }
    prn_sort_controls($form_array,$current_params);
    prn_nav($offset,$total_records,$max_rows);
    ?>
    <table width="100%" border="1" bordercolor="#FFFFFF" cellspacing="0" cellpadding="1" class="smalltext">
        <tr bgcolor="#3264C8" class="smallwhite"><?php
            for ($i=1; $i<count($form_array);$i++){ //print the title header
                if ($form_array[$i]["table"]<>"true")continue;?>
                <td><?php echo $form_array[$i]["label"]?></td><?php
            };?>
            <td>&nbsp;</td>
        </tr><?php
        //print rows 0 to max_rows
        for ($i=0; $i<$max_rows;$i++){
            $row=TldDatabase::fetchArray($result);
            if (!$row)break;?>
            <tr <?php if (!($i%2))echo "bgcolor=\"#CCCCCC\""?>><?php
            for ($j=1; $j<count($form_array);$j++){//print each cell in a row from 1 to count
                if ($form_array[$j]["table"]<>"true")continue;?>
                <td><?php
                prn_view_object($form_array[$j],$row,"table");?>
                </td><?php
            };?>
            <td>
                <a href="<?php echo $PHP_SELF?>?mode=<?php echo $form_array[0]["mode"]?>&form_type=<?php echo $form_array[0]["form_type"]?>&id=<?php echo $row["id"]?>">
                    <?php echo prn_icon("display")?></a></td>
            </tr><?php
        };?>
    </table><?php
    prn_nav($offset,$total_records,$max_rows);
}

function prn_table_TEMP($form_array,$offset=0,$max_rows=15){
    global $PHP_SELF,$MY_SESS;
    $table=$form_array[0]["table"];

    $sess_sort=$MY_SESS[$table]["sort"];
    $sess_sort_order=$MY_SESS[$table]["sort_order"];
    $sess_q=$MY_SESS[$table]["q"];
    $sess_adv_search_query=$MY_SESS[$table]["adv_search_query"];

    if($sess_adv_search_query){
        $query_count=$sess_adv_search_query;
    }else{
        $query_count="SELECT * FROM $table $sess_q";
    }
    $result_count=TldDatabase::query($query_count);
    if (!$result_count){
        echo "DATABASE ERROR:";
        echo $query_count;
        echo "<br>".TldDatabase::error();
        echo "PLEASE REPORT THIS TO THE WEBMASTER";
        ?>
        <!--meta http-equiv="refresh" content="0;URL=/en/private/service"--><?php
        exit;
    }

    $total_records=TldDatabase::numRows($result_count);
    if ($max_rows==0)$max_rows=$total_records;

    if($sess_adv_search_query){
        $query=$sess_adv_search_query." ORDER BY $sess_sort $sess_sort_order LIMIT $offset,$max_rows";
    }else{
        $query="SELECT * FROM $table $sess_q ORDER BY $sess_sort $sess_sort_order LIMIT $offset,$max_rows";
    }

//	$query.=$form_array[0]["restrict"];
    if($total_records==0){
        ?>
        <script>
          alert("No records found in query. Showing all records");
        </script>
        <meta http-equiv="refresh" content="0;URL=<?php echo $PHP_SELF?>?mode=reset_all">
        <?php
    }
    $result=TldDatabase::query($query);
//    echo $query;
    if ($result==0){
        $error= "DATABASE ERROR:";
        $error.= $query;
        $error.="<br>".TldDatabase::error();
        $error.="PLEASE REPORT THIS TO THE WEBMASTER";
        ?>
        <script>
          alert("<?php echo $error?>");
        </script>
        <meta http-equiv="refresh" content="0;URL=<?php echo $PHP_SELF?>?mode=reset_all">
        <?php
    }
    prn_sort_controls($form_array,$current_params);
    prn_nav($offset,$total_records,$max_rows);
    ?>
    <table width="100%" border="1" bordercolor="#FFFFFF" cellspacing="0" cellpadding="1" class="smalltext">
        <tr bgcolor="#3264C8" class="smallwhite"><?php
            for ($i=1; $i<count($form_array);$i++){ //print the title header
                if ($form_array[$i]["table"]=="false")continue;?>
                <td><?php echo $form_array[$i]["label"]?></td><?php
            };?>
            <td>&nbsp;</td>
        </tr><?php
        //print rows 0 to max_rows
        for ($i=0; $i<$max_rows;$i++){
            $row=TldDatabase::fetchArray($result);
            if (!$row)break;?>
            <tr <?php if (!($i%2))echo "bgcolor=\"#CCCCCC\""?>><?php
            for ($j=1; $j<count($form_array);$j++){//print each cell in a row from 1 to count
                if ($form_array[$j]["table"]=="false")continue;?>
                <td><?php
                prn_view_object($form_array[$j],$row,"table");?>
                </td><?php
            };?>
            <td><a href="<?php echo $PHP_SELF?>?mode=<?php echo $form_array[0]["mode"]?>&form_type=<?php echo $form_array[0]["form_type"]?>&id=<?php echo $row["id"]?>">Display</a></td>
            </tr><?php
        };?>
    </table><?php
    prn_nav($offset,$total_records,$max_rows);
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
        if($field_array["dump"]=="true")$line.="\"".$field_array["name"]."\",";
    }
    echo substr($line,0,-1)."\n";
    //Create each line
    while($row = TldDatabase::fetchArray($result)) {
        $line="";
        foreach($form_array as $field_array){
            // Check if we can extract field
            if($field_array["dump"]!="true") continue;
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
    if($num_lines==0)$num_lines=1;
    ?>
    <a href="javascript:history.back()">Back</a>
<form method="post" action="<?php echo $PHP_SELF?>" ENCTYPE="multipart/form-data">
    <input type="submit" name="Submit" value="Submit">
    <input type="reset" name="Submit2" value="Reset">
    <input type="hidden" name="mode" value="form_save">
    <input type="hidden" name="form_type" value="<?php echo $form_array[0]["form_type"]?>">
    <input type="hidden" name="num_lines" value="<?php echo $num_lines?>">
    <br><?php
    for($j=0;$j<$num_lines;$j++){
        ?><b>Line #<?php echo $j+1?></b>
        <input type="hidden" name="parent_id[<?php echo $j?>]" value="<?php echo $id?>">
        <table width="700" border="1" cellspacing="0" cellpadding="1" class="smalltext" bordercolor="#FFFFFF"><?php
            for ($i=1; $i<count($form_array);$i++){
                $var_name=$form_array[$i]["name"];
                $type=$form_array[$i]["type"];
                if ($type=="foreign_key" || $type=="hyperlink" || $type=="col_break")continue;
                ?>
                <tr>
                <td bgcolor="#3264C8" class="smallwhite"><?php echo $form_array[$i]["label"]?></td>
                <td bgcolor="#CCCCCC"><?php prn_add_object($form_array[$i],$j)?></td>
                </tr><?php
            };?>
        </table>
        <?php
    }
    ?>
    <input type="submit" name="Submit" value="Submit">
    <input type="reset" name="Submit2" value="Reset">
    </form><?php
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
            $value = $form_object["add"] ? date("Y-m-d", mktime(0, 0, 0, $month + $form_object["add"]["months"], $day + $form_object["add"]["days"], $year + $form_object["add"]["years"])) : date("Y-m-d");
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
        case 'locked_date':
        case 'warranty_end_date':
            $result=<<<EOF
		<input type="hidden" name="$var_name" value="">
	    &nbsp;
EOF;
            break;
        case 'reg_details':
            $result =<<<EOF
		<input type="hidden" name="$var_name" value="">
	 	&nbsp;
EOF;
            break;
        case 'courier':
        case 'textarea':
            $attributes = $form_object["textarea_params"];
            $result =<<<EOF
		<textarea name="$var_name" $attributes></textarea>
EOF;
            break;
        /*
        EXAMPLE:
            array("name"=>"failure_system",	"label"=>"System",										"type"=>"select",		"table"=>"true",
                "source"=>"lists", "list_name"=>"warranty.failure_system"

        */
        case 'select':
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
        case 'lookup':
        case 'select_db':
            $attrib = $form_object["options"];
            $result =<<<EOF
		<select name="$var_name" $attrib>
	   	<option>&nbsp;</option>
EOF;
            $select_list = TldDatabase::query($form_object["select_query"]);
            while($select_row=TldDatabase::fetchArray($select_list)){
                $result .=
                    "<option value=\"".htmlspecialchars(stripslashes($select_row[$form_object["select_field_1"]]))."\">".
                    htmlspecialchars(stripslashes($select_row[$form_object["select_field_2"]])).
                    "</option>\n";
            }
            $result .= "</select>";
            break;
        case 'multi_select_db':
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
        case 'file_upload':
            $result =<<<EOF
		<input type="file" name="$var_name">
EOF;
            break;
        default:
            $result =<<<EOF
		<input type="${form_object["type"]}" name="$var_name?>" size="${form_object["width"]}">
EOF;
    }
    if($return){
        return $result;
    }else{
        echo $result;
    }
}

//Function to print a form to enter data into a database or print a form to do a search on a database
//Argument definitions
//$id = int, parent id to match
//$form_array = structured array definition for database
//$form_mode = "form_save","do_search"
//
function prn_form_search($form_array){
    global $PHP_SELF;
    ?>
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
<form method="post" action="<?php echo $PHP_SELF?>">
    <input type="submit" name="Submit" value="Submit">
    <input type="reset" name="Submit2" value="Reset">
    <input type="hidden" name="mode" value="do_search">
    <input type="hidden" name="form_type" value="<?php echo $form_array[0]["form_type"]?>">
    Logical operator for search: <select name="logic_op">
        <option>AND</option>
        <option>OR</option>
    </select>
    <table width="700" border="1" cellspacing="0" cellpadding="1" class="smalltext" bordercolor="#FFFFFF">
        <?php
        for ($i=1; $i<count($form_array);$i++){
            $var_name=$form_array[$i]["name"];
            $type=$form_array[$i]["type"];
            if ($var_name=="parent_id" || $type=="hyperlink" || $type=="col_break")continue;?>
            <tr>
            <td bgcolor="#3264C8" class="smallwhite"><?php echo $form_array[$i]["label"];?></td>
            <td bgcolor="#CCCCCC"><?php
                if ($type=="title"){
                    echo "<td bgcolor=\"#CCCCCC\">&nbsp;</td>";
                }else{?>
                <select name="dropbox_<?php echo $var_name?>">
                        <option></option>
                        <option>&lt;</option>
                        <option>&gt;</option>
                        <option>&lt;&gt;</option>
                        <option>like</option>
                    </select><?php
                }
                prn_search_object($form_array[$i]);
                ?>
            </td>
            </tr><?php
        };?>
    </table>
    <input type="submit" name="Submit" value="Submit">
    <input type="reset" name="Submit2" value="Reset">
    </form><?php
}

// Function to print a form by data ID and view object
function prn_form_byID($form_array){
    global $PHP_SELF;
    ?>
    <form method="post" action="<?php echo $PHP_SELF?>">
        <input type="hidden" name="mode" value="record_view">
        <input type="hidden" name="form_type" value="<?= $form_array[0]["form_type"] ?>">
        <table class="smalltext">
            <tr>
                <td class="smalltext">By <?= $form_array[0]["title"] ?># <input type="text" name="id" value="" size="10px;"></td>
                <td class="smalltext"><input type="submit" name="Submit" value="Ok"></td>
            </tr>
        </table>
    </form>
    <?php
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
            echo "&nbsp;";
            break;
        case 'primary_key':?>
            <input type="text" name="<?php echo $var_name?>" value=""> N.B. Only enter the numeric part of ref.<?php
            break;
//  case 'date_type':
        case 'auto_date_temp':?>
            <input type="hidden" name="orig_<?php echo $var_name?>" value=""><?php
            prn_date_field($var_name,"");
            break;
        case 'locked_date_temp':
        case 'warranty_end_date_temp':?>
            <input type="hidden" name="orig_<?php echo $var_name?>" value="<?php echo date("Y-m-d")?>"><?php
            prn_date_field($var_name,date("Y-m-d"));
            break;
        case 'reg_details':?>
            <input type="hidden" name="<?php echo $var_name?>" value=""><?php
            echo "&nbsp;";
            break;
        case 'textarea_bak':?>
        <textarea name="<?php echo $var_name?>" <?php echo $form_object["textarea_params"]?>></textarea><?php
            break;
        case 'lookup':?>
        <select name="<?php echo $var_name?>">
            <option>&nbsp;</option><?php
            $select_list = TldDatabase::query($form_object["select_query"]);
            while($select_row=TldDatabase::fetchArray($select_list)){?>
                <option value="<?php echo htmlspecialchars(stripslashes($select_row[$form_object["select_field_1"]]))?>"><?php echo htmlspecialchars(stripslashes($select_row[$form_object["select_field_2"]]))?></option>
            <?php }?>
            </select><?php
            break;
        case 'multi_select_db':?>
        <select name="<?php echo $var_name?>" multiple>
            <option>&nbsp;</option><?php
            $select_list = TldDatabase::query($form_object["select_query"]);
            while($select_row=TldDatabase::fetchArray($select_list)){?>
                <option value="<?php echo htmlspecialchars(stripslashes($select_row[$form_object["select_field_1"]]))?>"><?php echo htmlspecialchars(stripslashes($select_row[$form_object["select_field_2"]]))?></option>
            <?php }?>
            </select><?php
            break;
        default:?>
            <input type="<?php echo $form_object["type"]?>" name="<?php echo $var_name?>"><?php
    }
}

//Function to create the record view for emailing
//Removes the quick add forms at bottom of page by using function
//prn_list() instead of prn_list_add()
//
function prn_email_view($id,$form_array,$weblink=1){
global $PHP_SELF,$max_rows,$sess_show;
$result = TldDatabase::query("SELECT * FROM ".$form_array[0]["table"]." WHERE id=".$id);
if (empty($result)){
    echo "CANNOT FIND RECORD ID=".$id;?>
    <meta http-equiv="refresh" content="0;URL=<?php echo $PHP_SELF?>?mode="><?php
}
?>
    <h3><?php echo $form_array[0]["title"]?></h3>
<?php
if ($weblink){?>
    <a href="https://www.tld-gse.com<?php echo $PHP_SELF?>?mode=record_view&form_type=<?php echo $form_array[0]["form_type"]?>&id=<?php echo $id?>">Go to this record on the website</a><p>
    <?php
    }else{
        ?><b><?php echo "Close this window when finished"?></b><?php
    }
    prn_plain_form_view($id,$form_array);
    if (isset($form_array[0]["child_tables"])){
        echo "<hr>";
        foreach ($form_array[0]["child_tables"] as $child_table){
            $child_table .= "_tpl";
            global $$child_table; // phpcs:ignore
            $child_table_params=${$child_table}[0];
            $query = "SELECT * FROM ".$child_table_params["table"]." WHERE parent_id='$id'";
            if($child_table_params["default_sort"])
                $query .= " order by ".$child_table_params["default_sort"];
            $result = TldDatabase::query($query);
//			echo $query;
            if(TldDatabase::numRows($result)){
                ?>
                <b><?php echo $child_table_params["title"]?></b><?php
                prn_plain_list($$child_table,$result);
            }
        }
    }
    }

    //Function to print a record for viewing
    //Argument definitions
    //$id = int, id of record to view
    //$form_array = structured array definition for database
    //
    function prn_plain_form_view($id,$form_array){
    global $PHP_SELF;

    $query = "SELECT * FROM ".$form_array[0]["table"]." WHERE id=".$id;
    if($form_array[0]["default_sort"]){
        $query .= " order by ".$form_array[0]["default_sort"];
    }
    $result = TldDatabase::query($query);
    $row=TldDatabase::fetchArray($result);
    ?>
<table width="700" class="xsmalltext"><?php
$col_count=1;
?>
<tr><td width="50%" valign="top">
        <table width="100%" border="1" cellspacing="0" cellpadding="0" class="smalltext"><?php
            for ($i=1; $i<count($form_array);$i++){
                $var_name=$form_array[$i]["name"];
                if ($var_name=="parent_id")continue;
                if ($form_array[$i]["type"]=="col_break"){
                    switch($col_count){
                        case 1:
                            echo "</table></td><td width=\"50%\" valign=\"top\"><table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\" class=\"xsmalltext\">";
                            break;
                        case 2:
                            echo "</table></td></tr><tr><td width=\"50%\" valign=\"top\"><table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\" class=\"xsmalltext\">";
                            $col_count=0;
                    }
                    $col_count +=1;
                    continue;
                }
                ?><tr><td><font size="-1"><?php echo $form_array[$i]["label"]?></font></td>
                <td><font size="-1"><?php echo prn_view_object($form_array[$i],$row)?></font></td></tr><?php
            }?>
        </table>
    </td>
</tr>
</table><?php
}

//Function to print a PLAIN list from a database
//Argument definitions
//$id = int, parent id to match
//$form_array = structured array definition for database
//
//function prn_list($id,$form_array){
function prn_plain_list($form_array,$result){
    global $PHP_SELF;
//$result = TldDatabase::query("SELECT * FROM ".$form_array[0]["table"]." WHERE parent_id='$id'");
    $num_rows=TldDatabase::numRows($result);
    ?>
    <table width="700" border="1" cellspacing="0" cellpadding="0" class="xsmalltext">
    <tr class="xsmalltext"><?php
    for ($i=1; $i<count($form_array);$i++){
        if ($form_array[$i]["table"]=="false")continue;?>
        <td><font size="-1"><?php echo $form_array[$i]["label"]?></font></td><?php
    };?>
    </tr><?php
    while($row=TldDatabase::fetchArray($result)){
        if (!$row)break;?>
        <tr class="xsmalltext"><?php
        for ($j=1; $j<count($form_array);$j++){
            if ($form_array[$j]["table"]=="false")continue;?>
            <td><font size="-1"><?php prn_view_object($form_array[$j],$row)?></font></td><?php
        }?>
        </tr><?php
    }?>
    </table><?php
}

//Function to print a list from a database
//Argument definitions
//$id = int, parent id to match
//$form_array = structured array definition for database
//
//function prn_list($id,$form_array){
function prn_list($form_array,$result){
    global $PHP_SELF;
//$result = TldDatabase::query("SELECT * FROM ".$form_array[0]["table"]." WHERE parent_id='$id'");
    $num_rows=TldDatabase::numRows($result);
    ?>
    <table width="700" border="1" cellspacing="0" cellpadding="1" class="smalltext">
    <tr bgcolor="#3264E8"><?php
    for ($i=1; $i<count($form_array);$i++){
        if ($form_array[$i]["table"]<>"true")continue;?>
        <td><?php echo $form_array[$i]["label"]?></td><?php
    };?>
    </tr><?php
    while($row=TldDatabase::fetchArray($result)){
        if (!$row)break;?>
        <tr bgcolor="#CCCCCC"><?php
        for ($j=1; $j<count($form_array);$j++){
            if ($form_array[$j]["table"]<>"true")continue;?>
            <td><?php
            prn_view_object($form_array[$j],$row);?>
            </td><?php
        }?>
        </tr><?php
    }?>
    </table><?php
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
    $result = TldDatabase::query("SELECT * FROM ".$form_array[0]["table"]." WHERE id=".$id);
    $row=TldDatabase::fetchArray($result);
// Add the by Number search form
    prn_form_byID($form_array)
    ?>
    <table width="650" border="1" cellspacing="0" cellpadding="1" class="smalltext" bordercolor="#FFFFFF"><?php
    for ($i=1; $i<count($form_array);$i++){
        $var_name=$form_array[$i]["name"];
        if ($var_name=="parent_id" || $form_array[$i]["type"]=="col_break")continue;?>
        <tr>
            <td bgcolor="#3264C8" class="smallwhite"><?php echo $form_array[$i]["label"];?></td>
            <td bgcolor="#CCCCCC"><?php
                prn_view_object($form_array[$i],$row);
                ?>
            </td>
        </tr>
    <?php }?>
    </table><?php
}

function prn_view_object($form_object,$row,$mode=""){
    global $PHP_SELF,$PHP_AUTH_USER,$UPLOADS_PATH;

    $var_name=$form_object["name"];
    if ($row[$var_name]=='' && $form_object["type"]<>"hyperlink"){
        echo "&nbsp;";
        return;
    }

    switch ($form_object["type"]){
        case 'primary_key':
            echo $form_object["prefix"].$row[$var_name];
            break;
        case 'title':
            echo "&nbsp;";
            break;
        case 'hyperlink':
            echo $form_object["hyperlink"];
            break;
        case 'auto_date':
        case 'date_type':
            echo str_replace("-","&ndash;",$row[$var_name]);
            break;
        case 'locked_date':
        case 'warranty_end_date':
            echo str_replace("-","&ndash;",$row[$var_name]);
            break;
        case 'reg_details':
            prn_subrecord($row[$var_name],$form_object["select_query_view_tpl"]);
            break;
        case 'lookup':
            if (!$select_list = TldDatabase::query($form_object["select_query_view"]."'".$row[$var_name]."'")){
                echo $row[$var_name]." NOT FOUND";
                break;
            }
            $select_row=TldDatabase::fetchArray($select_list);
            if ($mode=="table"){
                echo $select_row[$form_object["select_field_view"]];
            }elseif(isset($form_object["select_query_view_tpl"])){
                prn_subrecord($row[$var_name],$form_object["select_query_view_tpl"]);
            }else{
                echo stripslashes($select_row[$form_object["select_field_view"]]);
            }
            break;
        case 'file_upload':
            $serverFilePath = $UPLOADS_PATH.'/'.$form_object["file_upload_dir"]."/".$row[$var_name];
            $filepath="/en/private/uploads/".$form_object["file_upload_dir"]."/".$row[$var_name];
            $file_upload_type=$form_object["file_upload_type"];

            switch($file_upload_type){
                case 'inline_img':
                    ?><img src="<?php echo $filepath?>" <?php echo $form_object["attribs"]?>><?php
                    break;
                default:
                    //options
                    //unprotected, no permission checks
                    //default is protected
                    if(file_exists($serverFilePath)){
                        $link = "<a href=\"$filepath\"><img src=\"/shared/bluesphere/16x16/actions/filesaveas.png\"".
                            " title=\"Click to download, [".round($serverFilePath/1024,2)."kb, ".
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
//									echo $query;
                            $doCheck=TldDatabase::numRows(TldDatabase::query("SELECT * FROM $file_upload_table WHERE parent_id=".$row["id"]));
                            if($rows = TldDatabase::query($query)){
                                if(TldDatabase::numRows($rows) == 1 || $doCheck == 0){
                                    echo $link;
                                }
                            }
                        }else{
                            echo $link;
                        }
                    }else{
                        ?><p class="alert"><?php echo $row[$var_name]?>: File not found</p><?php
                    }
            }
            break;
        case "courier":
            //UPS,FED,DHL
            include_once('forms_and_reports.inc.php');
            $lines = tldCourier::parseMultiple($row[$var_name]);
            foreach($lines as $line){
                $form = new tldCourier($line['courier']);
                echo $form->fetch($line['trackNum']);
            }
            break;
        case 'courierTEMP':
            //UPS,FED,DHL
            $lines = explode("\n",$row[$var_name]);
            foreach($lines as $line){
                if(empty($line))continue;
                $line=getAlphaNumericOnly($line);
                $courier = strtolower(substr($line,0,3));
                switch($courier){
                    case 'wat':
                        $trackNum = substr($line,-9);
                        ?>
                        <FORM ACTION="http://www.watkins.com/OnlineTools/ShipmentTracking/default.asp" METHOD="POST">
                            <?php echo $line;?>
                            <INPUT TYPE="hidden" NAME="Level" VALUE="1">
                            <INPUT TYPE="HIDDEN" NAME="NumberType" VALUE="PRO">
                            <INPUT TYPE="HIDDEN" NAME="TrackingNumber0" VALUE="<?php echo $trackNum?>">
                            <input class="xsmalltext" type="submit" name="track" value="WATKINS">
                        </FORM>
                        <?php
                        break;
                    case 'ups':
                        $trackNum = substr($line,-18);
                        ?>
                        <FORM ACTION="http://wwwapps.ups.com/etracking/tracking.cgi" METHOD="GET">
                            <?php echo $line;?>
                            <INPUT TYPE="HIDDEN" NAME="tracknums_displayed" VALUE="5">
                            <INPUT TYPE="HIDDEN" NAME="TypeOfInquiryNumber" VALUE="T">
                            <INPUT TYPE="HIDDEN" NAME="HTMLVersion" VALUE="4.0">
                            <INPUT TYPE="hidden" NAME="InquiryNumber1" value="<?php echo $trackNum?>">
                            <input class="xsmalltext" type="submit" name="track" value="UPS">
                        </FORM>
                        <?php
                        break;
                    case 'fed':
                        $trackNum = substr($line,-12);
                        ?>
                        <FORM NAME="tracking" ACTION="http://www.fedex.com/cgi-bin/tracking" method="GET">
                            <?php echo $line;?>
                            <INPUT TYPE="HIDDEN" NAME="action" VALUE="track">
                            <INPUT TYPE="HIDDEN" NAME="language" VALUE="english">
                            <INPUT TYPE="HIDDEN" NAME="cntry_code" VALUE="us">
                            <INPUT TYPE="HIDDEN" NAME="initial" VALUE="x">
                            <INPUT TYPE="hidden" NAME="tracknumbers" value="<?php echo $trackNum?>">
                            <INPUT class="xsmalltext" TYPE=SUBMIT VALUE="FED">
                        </FORM>
                        <?php
                        break;
                    case 'dhl':
                        $trackNum = substr($line,-10);
                        ?>
                        <form method="get" action="http://www.dhl.com/cgi-bin/tracking.pl">
                            <?php echo $line;?>
                            <input type="hidden" name="TID" value="CP_ENG">
                            <input type="hidden" name="FIRST_DB">
                            <input type="hidden" name="AWB" value="<?php echo $trackNum?>">
                            <input class="xsmalltext" type="submit" value="DHL">
                        </form>
                        <?php
                        break;
                    case 'bax':
                        $trackNum = substr($line,-8);
                        ?>
                        <form method="get" action="http://www.baxglobal.com/Tracking/TrackResult.aspx">
                            <?php echo $line;?>
                            <input TYPE="hidden" NAME="pg" VALUE="/Tracking/default.aspx">
                            <input TYPE="hidden" NAME="dst" VALUE="">
                            <input TYPE="hidden" NAME="org" VALUE="">
                            <input TYPE="hidden" NAME="trackby" VALUE="H">
                            <input type="hidden" name="trackbyno" size="7" maxlength="12" value="<?php echo $trackNum?>">
                            <input class="xsmalltext" type="submit" value="BAX">
                        </form>
                        <?php
                        echo "";
                        break;
                    default:
                        echo $line."<p class=\"alert\">Warning: Cannot identify courier. Must start with ups, fed, dhl or bax.</p>";
                }
            }
            break;
        case 'password':
            echo "***********";
            break;
        default:
            echo stripslashes(nl2br($row[$var_name]));
    }
}

//Function to print a record for viewing from a related database
//Argument definitions
//$id = int, id of record to view
//$form_array = structured array definition for database
//
function prn_subrecord($id,$form_array){
    global $PHP_SELF;
    $result = TldDatabase::query("SELECT * FROM ".$form_array[0]["table"]." WHERE id=".$id);
    $row=TldDatabase::fetchArray($result);
    for ($i=1; $i<count($form_array);$i++){
        $var_name=$form_array[$i]["name"];
        //if ($var_name=="id" || $var_name=="parent_id")continue;
        if ($var_name=="parent_id" || empty($row[$var_name]))continue;
//		echo $form_array[$i]["label"]." : ";
        switch ($form_array[$i]["type"]){
            case 'auto_date':
            case 'date_type':
                //                echo str_replace("-","/",$row[$var_name]);
                echo $row[$var_name];
                break;
            case 'lookup':
                $select_list = TldDatabase::query($form_array[$i]["select_query_view"].$row[$var_name]);
                $select_row=TldDatabase::fetchArray($select_list);
                //echo $select_row[$form_array[$i]["select_field_view"]];
                //echo ${$form_array[$i]["select_query_view_tpl"]};
                if (isset($form_array[$i]["select_query_view_tpl"]))prn_form_view($row[$var_name],${$form_array[$i]["select_query_view_tpl"]});
                break;
            default:
                echo $row[$var_name];
        }?>
        <br>
        <?php
    }
}

//Function to print a date input field in the form day/month/yr
//Argument definitions
//$var_name = name of variable
//$timestamp = time to display in Unix timestamp format
//
function prn_date_field($var_name,$mysql_date){
    $year= substr ($mysql_date, 0, 4);
    $month= substr ($mysql_date, 5, 2);
    $day= substr ($mysql_date, 8, 2);
//echo $year."<br>";
//echo $month."<br>";
//echo $day."<br>";
    ?>
    <select name="year_<?php echo $var_name?>">
        <option>&nbsp;</option><?php
        for ($j=1980;$j<2010;$j++){?>
            <option <?php if ($j==$year)echo "selected"?>><?php echo $j?></option><?php
        }?>
    </select>
    <select name="month_<?php echo $var_name?>"><?php
        $twelve=array("","01","02","03","04","05","06","07","08","09","10","11","12");
        foreach ($twelve as $j){?>
            <option <?php if ($j==$month)echo "selected"?>><?php echo $j?></option><?php
        }?>
    </select>
    <select name="day_<?php echo $var_name?>"><?php
    foreach ($twelve as $j){?>
        <option <?php if ($j==$day)echo "selected"?>><?php echo $j?></option><?php
    }
    for ($j=13;$j<32;$j++){?>
        <option <?php if ($j==$day)echo "selected"?>><?php echo $j?></option><?php
    }?>
    </select><?php
}

//Function to construct a WHERE clause based on search form
//Argument definitions
//$form_array = structured array definition for database
//
function construct_where($form_array){
    for ($i=1; $i<count($form_array);$i++){
        //echo $i;
        $var_name=$form_array[$i]["name"];
        if (empty($GLOBALS["dropbox_".$var_name]))continue;
        $field_op=$GLOBALS["dropbox_".$var_name];
        switch ($form_array[$i]["type"]){
            //case 'locked_date':
            //case 'auto_date':
            case 'date_type_temp':
                if(checkdate($GLOBALS["month_".$var_name],$GLOBALS["day_".$var_name],$GLOBALS["year_".$var_name])){
                    $where .= $var_name." $field_op '".$GLOBALS["year_".$var_name]."-".$GLOBALS["month_".$var_name]."-".$GLOBALS["day_".$var_name]."' ".$GLOBALS["logic_op"]." ";
                }else{
                    echo "Date is bad!";
                }
                break;
//        case 'lookup':
//        case 'select_db':
//            $where .= $var_name."='".stripslashes($GLOBALS[$var_name])."' OR ";
//        break;
            default:
                $where .= $var_name." $field_op '".stripslashes($GLOBALS[$var_name])."' ".$GLOBALS["logic_op"]." ";
            //echo $var_name."<p>";
        }
    }
    $oplength=strlen($GLOBALS["logic_op"])+2;
    if ($where)
        return " WHERE ".substr($where,0,-$oplength);
}

//Function to construct a WHERE clause based on QUICK search form
//Argument definitions
//$form_array = structured array definition for database
////$search_target = what you're looking for
function construct_quick_where($form_array,$search_target){
//	construct_where_each_field($form_array,$search_target,$from,$tables,$where,$ands);
//$tables.=$form_array[0]["table"].",";
//$from.=$form_array[0]["table"].".*,";
    $search_target=str_replace(' ','%',$search_target);
    for ($i=1; $i<count($form_array);$i++){
        $row_object=$form_array[$i];
        $var_name=$row_object["name"];
        if ($var_name=="bypass") continue;

        $table_name=$form_array[0]["table"];
        $where .= " $table_name.$var_name like '%$search_target%' OR ";
    }
    if ($where) return " WHERE ".substr($where,0,-4);
    //echo $tables." WHERE ".substr($where,0,-4);
}

function construct_quick_where_temp($form_array,$search_target){
    $search_target=str_replace(' ','%',$search_target);
    $where=prepend_table_name($form_array,$search_target);
    if(isset($child_table)){
        $child_table .= "_tpl";
        global $$child_table; // phpcs:ignore
        $where.=prepend_table_name($$child_table,$search_target);
    }
    if ($where) return " WHERE ".substr($where,0,-4);
    //echo $tables." WHERE ".substr($where,0,-4);
}

function prepend_table_name($form_array,$target){
    //Extract table name
    $table_name=$form_array[0]["table"];
    for ($i=1; $i<count($form_array);$i++){
        //Extract field name
        $field_name=$form_array[$i]["name"];
        if($field_name=="bypass" || $field_name=="id" || $field_name=="parent_id")continue;
        $where.="$table_name.$field_name like '%$target%' OR ";
    }
    if ($where) return substr($where,0,-4);
}

//Function to make pop up message in browser
//$message=message to display
//
function popup_message($message){?>
    <script>
      alert("<?php echo $message?>");
    </script><?php
}

function getFileDetails($file){
    return 0;
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
