<html>
<head>
<title>Document Quick Edit</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="/tld-gse.css" type="text/css">
</head>
<body class="smalltext">
<a name="top"></a>
	<h3>TLD Document# <?php echo $id?></h3>
<?php
include("common.inc.php");
include("manuals_diagrams_table_def.inc.php");
//include_once("pur.inc.php");
$fields=array(	"item"=>		array("title"=>"Item",		"size"=>"3"),
				"pn"=>			array("title"=>"PN",		"size"=>"10"),
				"qty"=>			array("title"=>"Qty",		"size"=>"10"),
				"um"=>			array("title"=>"UM",		"size"=>"10"),
				"en"=>			array("title"=>"English",	"size"=>"30"),
				"fr"=>			array("title"=>"French",	"size"=>"30"),
				"group_p"=>		array("title"=>"P",			"size"=>"3"),
				"group_m"=>		array("title"=>"M",			"size"=>"3"),
				"group_o"=>		array("title"=>"O",			"size"=>"3"),
				"group_c"=>		array("title"=>"C",			"size"=>"3")
				);

switch ($m){
	case 'bom_form':
		?><form action="<?php  echo $_SERVER['PHP_SELF']?>" method="post">
		<select name="erp">
		<option>400</option>
		<option>420</option>
		<option>500</option>
		<option>520</option>
		<option>640</option>
		<option>660</option>
		</select>
		<input type="text" name="n">
		<input type="hidden" name="m" value="bom_insert">
		<input type="submit" name="submit" value="Submit">
		</form><?php
	break;
	case 'bom_insert':
		include_once("eng.inc.php");
		$date = date("Y-m-d");
		$bom = new tldBOM($erp, $n, $date, false);
		$rows = $bom->getBOMList();

		if(count($rows ?? [])){
			$query = "insert into manuals_diag set factory_num='$n',doc_type='PARTS DIAGRAM'";
			TldDatabase::query($query);
			echo TldDatabase::error();
			$newId = TldDatabase::lastInsertId();
			if($newId){
				echo "Searching for parts and descriptions, please wait.<br>";
				foreach($rows as $row){
					$query = "insert into manuals_parts set parent_id='$newId',item='".
							TldDatabase::escape($row["t_pono"])."',pn='".TldDatabase::escape($row["t_sitm"]).
							"',qty='".TldDatabase::escape($row["t_qana"])."',um='".TldDatabase::escape($row["t_cuni"]).
							"',en='".TldDatabase::escape($row["t_dsca"])."'";
					//	echo $query."<br>";
					echo ".";
					TldDatabase::query($query);
					echo TldDatabase::error();
				}
				getQuickEditForm($newId,$fields);
			}else{
				echo "Could not create new record...";
			}
		}else{
			?><p class="alert">No BOM found for <?php  echo $n;?></p>
			<a href="<?php echo $_SERVER['PHP_SELF']?>?m=bom_form">Back</a><?php
		}
	break;
	case 'update':
		$query=<<<EOF
		SELECT *
		 FROM manuals_parts
		WHERE parent_id=$id
		ORDER BY item
EOF;
		$rows=TldDatabase::query($query);
		echo TldDatabase::error();
		if(TldDatabase::numRows($rows)>0){
			while($row=TldDatabase::fetchArray($rows)){
				$update="update manuals_parts set ";
				foreach($fields as $field=>$fielddata){
					$currentField=$$field;
					$update .= $field."='".TldDatabase::escape($currentField[$row["id"]])."',";
				}
				$update = substr($update,0,-1)." WHERE id='".$row["id"]."'";
				TldDatabase::query($update);
//					echo TldDatabase::error();
//					echo $update."<br>";
			}
		}
		?><meta http-equiv="refresh" content="0;URL=/en/private/product_support/publications/documents_admin.php?mode=record_view&form_type=main_tpl&id=<?php echo $id?>"><?php
	break;
	default:
		getQuickEditForm($id,$fields);
}

function getQuickEditForm($id,$fields){
	if(empty($id) || empty($fields)){
		?>id or fields parameters are empty.<?php
		return;
	}
	?>
	<a href="/en/private/product_support/publications/documents_admin.php?mode=record_view&form_type=main_tpl&id=<?php echo $id?>">Cancel</a><?php
	$query=		"SELECT *".
				" FROM manuals_parts".
				" WHERE parent_id=$id".
				" ORDER BY item";
	$rows=TldDatabase::query($query);
	echo TldDatabase::error();
	if(TldDatabase::numRows($rows)>0){
		?>
		<form method="post" action="<?php echo $PHP_SELF?>">
		<input type="hidden" name="id" value="<?php echo $id?>">
		<input type="hidden" name="m" value="update">
		<input type="submit" name="Submit" value="Update">
		<table>
		<tr class="table_title"><?php
		//print table titles
		foreach($fields as $field=>$fielddata){
			?><td><?php echo $fielddata["title"]?></td><?php
		}
		?></tr><?php
		//start of table data
		$i=0;
		while($row=TldDatabase::fetchArray($rows)){
			$i++;
			if($i%2 == 0){
				$bg="#CCCCCC";
			}else{
				$bg="#FFFFFF";
			}
			?>
			<tr bgcolor="<?php echo $bg?>"><?php
			foreach($fields as $field=>$fielddata){
				?><td><?php
				if($field=="en" || $field=="fr"){
					$objName=$field."[".$row["id"]."]";
					?><input type="text" name="<?php echo $objName?>" value="<?php echo htmlentities(stripslashes($row[$field]))?>" size="<?php echo $fielddata["size"]?>"><br><?php
					if($field=="en"){
						$fieldname="t_dsca";
					}else{
						$fieldname="FR";
					}
					//find a match from the manual_parts table
					$rows2 = TldDatabase::query("select * from manuals_parts where pn='".$row["pn"]."' and $field<>'' order by id desc");
					if(TldDatabase::numRows($rows2)){
						$row2 = TldDatabase::fetchArray($rows2);
						?><input name="<?php echo $objName?>" type="radio" value="<?php  echo $row2["$field"]?>">
						<b>From documents database</b><br><?php  echo $row2["$field"]?><br><?php
					}

					//find fuzzy matches from the parts table
					$pn = $row["pn"];
					$query=<<<EOF
						SELECT parts.*,parts_whse.*
						FROM parts,parts_whse
						WHERE
							(parts.item='$pn' OR parts.item='__$pn' OR parts.item='___$pn')
							and parts.WHSE=parts_whse.id
EOF;
//echo $query;
					//echo $query."<br>";(ITEM='$pn' OR ITEM like '__$pn%' OR ITEM like '___$pn%'
					$records = tldUtils::getSqlToAssocArray($query);
					if(count($records ?? [])){
						foreach($records as $record){
							if(!empty($record[$fieldname])){
								?>
								 <input name="<?php  echo $objName?>" type="radio" value="<?php  echo htmlentities(stripslashes($record[$fieldname]))?>">
								<b><?php  echo  $record["ITEM"]?>, <?php  echo $record["WHSE"]?></b><br>
								 <?php echo $record[$fieldname]?><br>
								<?php
							}
						}
					}
				}else{
					?>
					<input type="text" name="<?php echo $field?>[<?php echo $row["id"]?>]" value="<?php echo htmlentities(stripslashes($row[$field]))?>" size="<?php echo $fielddata["size"]?>">
					<?php
				}
				?>
				</td><?php
			}
			?></tr><?php
		}
		?></table>
		<input type="submit" name="Submit" value="Update">
		</form><?php
	}else{
		echo "<br>No documents, did you select a parts diagram document?";
	}
}

?>
</body>
</html>
