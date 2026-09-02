<?php
if($id){
	include("manuals_diagrams_table_def.inc.php");
include("common.inc.php");
	if($multi){
		$query=	"SELECT DISTINCT manuals_diag.category".
				" FROM service,service_serials,manuals,manuals_docs,manuals_diag".
				" WHERE service.id=service_serials.parent_id AND service_serials.serial=manuals_docs.parent_id".
				" AND manuals_docs.doc_num=manuals_diag.id".
				" AND service.id='$id' AND manuals_diag.doc_type='PARTS DIAGRAM'".
				" GROUP BY manuals_diag.category ORDER BY manuals_diag.category";
		$rows=TldDatabase::query($query);
		//echo $query;
		if(TldDatabase::numRows($rows)){
			while($row=TldDatabase::fetchArray($rows)){
				$categories[]=$row["category"];
			}
			$pages.="'http://temp:temp@www.tld-gse.com/en/private/manuals/manuals_page_pdf_toc.php?id=$id' ";
			foreach($categories as $category){
				$query=	"SELECT manuals_diag.*".
						" FROM service,service_serials,manuals,manuals_docs,manuals_diag".
						" WHERE service.id=service_serials.parent_id AND service_serials.serial=manuals.id".
						" AND manuals.id=manuals_docs.parent_id AND manuals_docs.doc_num=manuals_diag.id".
						" AND service.id='$id' AND manuals_diag.doc_type='PARTS DIAGRAM'".
						" AND manuals_diag.category='$category'".
						" ORDER BY manuals_diag.description";
				$rows=TldDatabase::query($query);
				while($row=TldDatabase::fetchArray($rows)){
					$pages.="'http://temp:temp@www.tld-gse.com/en/private/manuals/manuals_diagrams_view.php?m=1&pdf=1&eqid=".$id."&id=".$row["id"]."' ";
				}
				if(empty($category))break;
			}
		}else{
			echo "Error: no records, pls contact webmaster.";
			exit;
		}
	}else{
		$pages="'http://temp:temp@www.tld-gse.com/en/private/manuals/manuals_diagrams_view.php?m=1&pdf=1&id=$id' ";
	}
	header("Content-Type: application/pdf");
	header("Content-Disposition: attachment;filename=parts_diagram_$id.pdf");
	passthru("/usr/local/bin/htmldoc --webpage -t pdf14 $pages");
}
?>