<?php
include("manuals_diagrams_table_def.inc.php");
include("common.inc.php");
$MAX_RECORDS = 50;

?>
<html>
<head>
<title>Product Support</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="/tld-gse.css" type="text/css">
<script>
function showdiagram(id,lang){
	window.open('https://www.tld-gse.com/en/private/manuals/manuals_show_diagram_index.php?m=1&id='+id+'&lang='+lang,'Parts_diagram', 'width=700,height=550,resizable');
}
</script>
</head>
<body>
<a name="top"></a><body class="smalltext">
	<form method="post" action="<?php echo $PHP_SELF?>">
	<b>Search for docs to add (will show up to <?php echo $MAX_RECORDS?> records only):</b>
		<input type="hidden" name="m" value="search">
		<input type="hidden" name="id" value="<?php echo $id?>">
		<input type="hidden" name="lang" value="<?php echo $lang?>">
		<input type="text" name="target" value="<?php echo str_replace("%"," ",$target)?>" size="10">
		<input type="submit" name="Submit" value="Go">
	</form>

<?php
switch ($m){
	case 'search':
		?><a href="<?php echo $PHP_SELF?>?id=<?php echo $id?>">Cancel</a><?php
		if($target){
			$query=<<<EOF
			SELECT * FROM manuals_diag
			 WHERE
			 id like '%$target%'
			 OR factory_num like '%$target%'
			 OR endescription like '%$target%'
			 OR category like '%$target%'
			 ORDER BY category,endescription limit 0,$MAX_RECORDS
EOF;
			$rows=TldDatabase::query($query);
			$i=0;
//			if(TldDatabase::numRows($rows) > $MAX_RECORDS)echo "Too many matches, showing first $MAX_RECORDS";
			?><table>
			<tr class="table_title"><td>#</td><td>TLD&nbsp;DOC#</td><td>Category</td><td>Factory Num</td><td>Description</td><td>&nbsp;</td></tr><?php
			while($row=TldDatabase::fetchArray($rows)){
				if($i%2==1){
					$bgcolor="#CCCCCC";
				}else{
					$bgcolor="#FFFFFF";
				}
				?><tr bgcolor="<?php echo $bgcolor?>">
					<td><?php  echo $i?></td>
					<td><?php  echo showit($row["id"])?></td>
					<td><?php  echo showit($row["category"])?></td>
					<td><?php  echo showit($row["factory_num"])?></td>
					<td><?php  echo showit($row["endescription"])?></td>
					<td><a href="<?php echo $PHP_SELF?>?m=add&id=<?php echo $id?>&doc_id=<?php echo $row["id"]?>">ADD</a></td>
				</tr><?php
				$i++;
			}
			?></table><?php
		}
	break;
	default:
		switch ($m){
		case add:
			TldDatabase::query("INSERT INTO manuals_docs set parent_id=$id,doc_num=$doc_id");
		break;
		case del:
			TldDatabase::query("DELETE FROM manuals_docs WHERE id=$doc_id");
		break;
		case update:
			if($id){
				foreach($item as $line_id => $item_no){
					TldDatabase::query("UPDATE manuals_docs set item='$item_no' WHERE id=$line_id AND parent_id=$id");
				}
			}
		break;
		}
		?>
	<h3>Manual TLD#<?php echo $id?></h3>
	<a href="/en/private/product_support/publications/manuals_admin.php?mode=record_view&form_type=main_tpl&id=<?php echo $id?>">Back to Manuals</a>
	<?php
	$query=		"SELECT manuals_docs.item,manuals_docs.id AS doc_id,manuals_diag.id,manuals_diag.factory_num,manuals_diag.endescription,manuals_diag.frdescription".
				" FROM manuals,manuals_docs,manuals_diag".
				" WHERE manuals.id=manuals_docs.parent_id".
				" AND manuals_docs.doc_num=manuals_diag.id".
				" AND manuals.id=$id".
				" ORDER BY manuals_docs.item";
	$rows=TldDatabase::query($query);
	if(TldDatabase::numRows($rows)>0){
		?>
		<form method="post" action="<?php echo $PHP_SELF?>">
		<input type="hidden" name="id" value="<?php echo $id?>">
		<input type="hidden" name="m" value="update">
		<input type="submit" name="Submit" value="Update">
		<table>
		<tr class="table_title"><td>Item</td><td>TLD Doc#</td><td>Factory Num</td><td>Description</td><td>&nbsp;</td></tr><?php
		$i=0;
		while($row=TldDatabase::fetchArray($rows)){
			$i++;
			if($i%2 == 1){
				$bg="#CCCCCC";
			}else{
				$bg="#FFFFFF";
			}
			?><tr bgcolor="<?php echo $bg?>">
				<td><input type="text" name="item[<?php echo $row["doc_id"]?>]" value="<?php echo $row["item"]?>" size="3"></td>
			<td><?php echo showit($row["id"])?></td>
			<td><?php echo showit($row["factory_num"])?></td>
			<td>EN: <?php echo showit($row["endescription"])?><br>
				FR: <i><?php echo showit($row["frdescription"])?></i></td>
			<td><a href="<?php echo $PHP_SELF?>?m=del&id=<?php echo $id?>&doc_id=<?php echo $row["doc_id"]?>">DEL</a></td>
			</tr><?php
		}
		?></table>
		</form><?php
	}else{
		echo "No documents";
	}
}
?>
</body>
</html>
