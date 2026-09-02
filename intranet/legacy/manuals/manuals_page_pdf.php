<?php
if($id || $multi){
//$id is manual page id#
//$multi is manual id#
	include("manuals_diagrams_table_def.inc.php");
include("common.inc.php");
	if($multi){
		$query=	"SELECT DISTINCT manuals_diag.category".
				" FROM manuals,manuals_docs,manuals_diag".
				" WHERE manuals.id=manuals_docs.parent_id".
				" AND manuals_docs.doc_num=manuals_diag.id".
				" AND manuals.id=$multi AND manuals_diag.doc_type='PARTS DIAGRAM'".
				" GROUP BY manuals_diag.category ORDER BY manuals_diag.category";
		$rows=TldDatabase::query($query);
		//echo $query;
		if(TldDatabase::numRows($rows)){
			while($row=TldDatabase::fetchArray($rows)){
				$categories[]=$row["category"];
			}
			$pages.="'http://temp:temp@www.tld-gse.com/en/private/manuals/manuals_page_pdf_toc.php?id=$multi&lang=$lang' ";
			//NOTE THE SPACE AT END OF $PAGES. DOESN'T WORK WITHOUT IT!!!!!!!!!!!!!!!!!!!!!!!!!!!
			//echo $pages;
			foreach($categories as $category){
				$query=	"SELECT manuals_diag.*".
						" FROM manuals,manuals_docs,manuals_diag".
						" WHERE manuals.id=manuals_docs.parent_id".
						" AND manuals_docs.doc_num=manuals_diag.id".
						" AND manuals.id=$multi AND manuals_diag.doc_type='PARTS DIAGRAM'".
						" AND manuals_diag.category='$category'".
						" ORDER BY manuals_docs.item ASC";
				$rows=TldDatabase::query($query);
				while($row=TldDatabase::fetchArray($rows)){
					$pages.="'http://temp:temp@www.tld-gse.com/en/private/manuals/manuals_diagrams_view.php?m=1&pdf=1&lang=$lang&id=".$row["id"]."' ";
				}
				//if(empty($category))break;
				//echo $pages;
			}
			$filename="parts_book_".$multi."_$lang";
		}else{
			echo "Error: no records, pls contact webmaster.";
			exit;
		}
	}else{
		$pages="'http://temp:temp@www.tld-gse.com/en/private/manuals/manuals_diagrams_view.php?m=1&pdf=1&id=$id&lang=$lang' ";
		$filename="parts_diagram_".$id."_$lang";
	}
	header("Content-Type: application/pdf");
//	header("Content-Disposition: attachment;filename=$filename.pdf");
	header("Content-Disposition: inline;filename=$filename.pdf");
	passthru("/usr/local/bin/htmldoc --webpage -t pdf14 $pages");
}
?>