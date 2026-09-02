<?php
include("manuals_diagrams_table_def.inc.php");
include("common.inc.php");

?>
<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
<link rel="stylesheet" href="/tld-gse.css">
<title>Parts Book</title>
</head>
<body bgcolor="#FFFFFF" text="#000000">
<?php
if($id){
		$query=	"SELECT DISTINCT manuals_diag.category".
				" FROM manuals,manuals_docs,manuals_diag".
				" WHERE manuals.id=manuals_docs.parent_id AND manuals_docs.doc_num=manuals_diag.id".
				" AND manuals.id=$id AND manuals_diag.doc_type='PARTS DIAGRAM'".
				" GROUP BY manuals_diag.category ORDER BY manuals_diag.category";
		$rows=TldDatabase::query($query);
		//echo $query;
		if(TldDatabase::numRows($rows)){
				?><img src="/shared/icons/tld-icon.jpg" align="right"><h2>Parts Book</h2><?php
			while($row=TldDatabase::fetchArray($rows)){
				$categories[]=$row["category"];
			}
			foreach($categories as $category){
				$query=	"SELECT manuals_diag.*".
						" FROM manuals,manuals_docs,manuals_diag".
						" WHERE manuals.id=manuals_docs.parent_id AND manuals_docs.doc_num=manuals_diag.id".
						" AND manuals.id='$id' AND manuals_diag.doc_type='PARTS DIAGRAM'".
						" AND manuals_diag.category='$category'".
						" ORDER BY manuals_docs.item";
				$rows=TldDatabase::query($query);
				?><p><a name="<?php echo $category?>"></a><?php echo $category?></p>
				<ul><?php
				while($row=TldDatabase::fetchArray($rows)){
					?>
	             <li><?php
			   if($row[$lang."description"]){
			  		echo showit($row[$lang."description"]);
				}else{
					echo showit($row["endescription"]);
				}?>
 (<?php echo showit($row["factory_num"])?>)</li>
	             <?php
				}
				?>
	           </ul>
	           <?php
			if(empty($category))break;
			}
		}else{
			?>
           <p class="alert">Error<?php echo $query?></p>
           <?php
		}
}	?>
	</body>
</html>
