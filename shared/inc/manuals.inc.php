<?php
class equipmentManualSession{
	public $itsEquipmentManual;

	function __construct($id){
		if($id){
			$this->itsEquipmentManual = new equipmentManual($id);
		}else{
			return "Manual ID not set!";
		}
	}
	function incHeader(){
		return;
	}
	function incFooter(){
		return;
	}
	function incMenu(){
		return;
	}

	function doThis($m="",$n="",$id=""){
		$result="";
		switch($m){
		case 'dl':
			switch($n){
			case 'doc':
				$result .= $this->itsEquipmentManual->getFileFromDoc($id);
			break;
			default:
				$ob = $this->itsData[$n][$id];
				$result .= $ob->getFile();
			}
		break;
		case 'listing':
			$result .=$this->incHeader();
			$result .=$this->incMenu($n);
			switch($n){
			case 'manual':
				$result .= $this->itsEquipmentManual->show($this->itsLang);
			break;
			default:
				$result .= getRecordList($this->itsData[$n]);
			}
			$result .=$this->incFooter();
		break;
		case 'table':
			$result .=$this->incHeader();
			$result .=$this->incMenu($n);
			switch($n){
				default:
					$result .= getRecordTable($this->itsData[$n]);
			}
			$result .=$this->incFooter();
		break;
		case 'show':
			switch($n){
			default:
				$result .=$this->incHeader();
				$result .=$this->incMenu($n);
				if(isset($this->itsData[$n][$id])){
					$result .= $this->itsData[$n][$id]->showRecord();
				}else{
					$result .= "<p>Error: $id</p>";
				}
			}
			$result .=$this->incFooter();
		break;
		default:
			$result .= $this->incHeader();
			$result .= $this->incMenu($n);
			$result .= $alert;
			$result .=$this->itsEquipmentManual->show();
			$result .=$this->incFooter();
		}
		return $result;
	}
}

//------------------------
class basicManual{
	public $itsAvailLang = array("en","fr");
	public $itsCatTrans = 	array	(
		"BOOM"			=>array("fr"=>"FLECHE"),
		"BODY"			=>array("fr"=>"CORPS"),
		"BRAKING"		=>array("fr"=>"FREINAGE"),
		"BRIDGE"		=>array("fr"=>"PONT"),
		"CHASSIS"		=>array("fr"=>"EQUIPEMENT CHASSIS"),
		"COMPRESSOR"	=>array("fr"=>"COMPRESSEUR"),
		"ELEC CONTROL"	=>array("fr"=>"ELECTRICITE"),
		"ELEVATOR"		=>array("fr"=>"ASCENSEUR"),
		"ENGINE"		=>array("fr"=>"EQUIPEMENT MOTEUR"),
		"GENERATOR"		=>array("fr"=>"G�N�RATEUR"),
		"HYDRAULIC"		=>array("fr"=>"HYDRAULIQUE "),
		"PNEUMATIC"		=>array("fr"=>"PNEUMATIQUE"),
		"REFRIGERATION"	=>array("fr"=>"R�FRIG�RATION"),
		"SUSPENSION"	=>array("fr"=>"SUSPENSION"),
		"STRUCTURAL"	=>array("fr"=>"STRUCTURE"),
		"TRANSMISSION"	=>array("fr"=>"TRANSMISSION")
	);
	public $itsId;
	public $itsCategories;
	public $itsDocs;

	function getCatTrans($cat,$lang){
		if(empty($lang) || $lang == "en"){
			$result = strtoupper($cat);
		}else{
			$result = $this->itsCatTrans[strtoupper($cat)][$lang];
		}
		return $result;
	}

	function show($lang=""){
		if($this->itsId == 0)
			return "<p class=\"alert\">No online manual available for this unit.</p>";
		if(count($this->itsDocs) > 0){
			//Print TOC
			$result .= "<table width=\"650\"><tr><td>\n<h4>Table of Contents</h4><p><ul>";
			foreach($this->itsCategories as $category){
				if(empty($category))continue;
				$result .= "<li><a href=\"#manual_".$category."\">".
				$this->getCatTrans($category,$lang)."</a></li>\n";
			}
			$result .= "</ul>";
			//Print links per category
			foreach($this->itsCategories as $category){
				$result .= "<hr><h5><a name=\"manual_$category\"></a>".$this->getCatTrans($category,$lang)."</h5>\n";
				$result .= $this->listDocsByCategory($category,$lang);
				$result .= "<br><a href=\"#top\">Back to top</a>\n";
			}
			$result .= "</td></tr></table>\n";
		}
		$result .="<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
		$result .="<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
		return $result;
	}

	function listDocsByCategory($category,$lang){
		foreach($this->itsDocs as $key => $ob){
			$row = $ob->getItsDetails();
			if($row["category"]<>$category)continue;
			$result .= $ob->hyperlink($key,$lang);
		}
		return $result;
	}
	function getFileFromDoc($id){
		$this->itsDocs[$id]->getFile();
	}
}

class equipmentManual extends basicManual{
	function __construct($id){
		$this->itsId = $id;
		$query=	"SELECT manuals_diag.*".
				" FROM manuals_docs,manuals_diag".
				" WHERE manuals_docs.parent_id=$id".
				" AND manuals_docs.doc_num=manuals_diag.id".
				" AND manuals_diag.doc_type like 'MANUAL%'".
				" ORDER BY manuals_diag.category,manuals_diag.endescription";
/*
		$query=	"SELECT manuals_diag.*".
				" FROM manuals,manuals_docs,manuals_diag".
				" WHERE manuals.id=$id AND manuals_docs.parent_id=manuals.id".
				" AND manuals_docs.doc_num=manuals_diag.id".
				" AND manuals_diag.doc_type like 'MANUAL%'".
				" ORDER BY manuals_diag.category,manuals_diag.endescription";
*/
		if($rows=TldDatabase::query($query)){
			if(TldDatabase::numRows($rows) > 0){
				//Create list of categories for TOC
				$this->itsCategories = array();
				while($row=TldDatabase::fetchArray($rows)){
					if(!in_array($row["category"],$this->itsCategories))
						$this->itsCategories[]=strtoupper($row["category"]);
					$this->itsDocs[] = new equipmentManualDoc($row["id"]);
				}
			}
		}
	}
}

class partsManualSession{
	private $itsPartsManual;

	function __construct($id) {
	    $this->itsPartsManual = new partsManual($id);
	}
	function incHeader(){
		return "<html>
		<head>
			<title>Untitled Document</title>
			<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">
			<link rel=\"stylesheet\" href=\"/tld-gse.css\">
		</head>
		<body>";
	}

	function incFooter(){
		return "</body>
		</html>";
	}
	function incMenu(){
		return "";
	}

	function doThis($m="",$n="",$id=""){
		$result="";
		switch($m){
		case 'dl':
			switch($n){
			case 'partsmanualpic':
				$result .= $this->itsPartsManual->getFileFromDoc($id);
			break;
			default:
				$ob = $this->itsData[$n][$id];
				$result .= $ob->getFile();
			}
		break;
		case 'listing':
			$result .=$this->incHeader();
			$result .=$this->incMenu($n);
			switch($n){
			case 'partsmanual':
				$result .= $this->itsPartsManual->show($this->itsLang);
			break;
			default:
				$result .= getRecordList($this->itsData[$n]);
			}
			$result .=$this->incFooter();
		break;
		case 'table':
			$result .=$this->incHeader();
			$result .=$this->incMenu($n);
			switch($n){
				default:
					$result .= getRecordTable($this->itsData[$n]);
			}
			$result .=$this->incFooter();
		break;
		case 'show':
			switch($n){
			case 'partsmanualdoc':
				$result .= $this->itsPartsManual->itsDocs[$id]->showRecord($id);
			break;
			case 'partsmanualmenu':
				$result .= $this->itsPartsManual->itsDocs[$id]->showMenu($id);
			break;
			case 'partsmanualpic':
				$result .= $this->incHeader().
							$this->itsPartsManual->itsDocs[$id]->showPic($id).
							$this->incFooter();
			break;
			case 'partsmanualparts':
				$result .= $this->incHeader();
				$result .= $this->itsPartsManual->itsDocs[$id]->showPartsTable($id);
				$result .= $this->incFooter();
			break;
			default:
				$result .=$this->incHeader();
				$result .=$this->incMenu($n);
				if(isset($this->itsData[$n][$id])){
					$result .= $this->itsData[$n][$id]->showRecord();
				}else{
					$result .= "<p>Error: $id</p>";
				}
				$result .=$this->incFooter();
			}
		break;
		default:
			switch($n){
			case 'en':
				$this->itsLang = "en";
				$alert = "<p class=\"alert\">ENGLISH selected, where available</p>";
			break;
			case 'fr':
				$this->itsLang = "fr";
				$alert = "<p class=\"alert\">FRENCH selected, where available</p>";
			break;
			}

			$result .= $this->incHeader();
			$result .= $this->incMenu($n);
			$result .= $alert;
			$result .=$this->itsPartsManual->show();
			$result .=$this->incFooter();
		}

		return $result;
	}

}

class partsManual extends basicManual{
	function __construct($id){
		$this->itsId = $id;
		$query=	"SELECT manuals_diag.*".
				" FROM manuals_docs,manuals_diag".
				" WHERE manuals_docs.parent_id=$id AND manuals_docs.doc_num=manuals_diag.id".
				" AND manuals_diag.doc_type='PARTS DIAGRAM'".
				" ORDER BY manuals_diag.category, manuals_diag.endescription";
		if($rows=TldDatabase::query($query)){
			if(TldDatabase::numRows($rows) > 0){
				//Create list of categories for TOC
				$this->itsCategories = array();
				while($row=TldDatabase::fetchArray($rows)){
					if(!in_array($row["category"],$this->itsCategories))
						$this->itsCategories[]=strtoupper($row["category"]);
					$this->itsDocs[] = new partsManualDoc($row["id"]);
				}
			}
		}
	}
}
//-----------------------

class tldManual extends basicRecordClass{
	public $itsId;
	public $itsDetails;	//row from database
	public $itsFields = array	(	"type"			=>	"Type",
								"model"			=>	"Model",
								"description"	=>	"Description",
								"features"		=>	"Distinct Features"
							);
	public $itsFilename;
	public $itsFilepath;

	function __construct($id){
		$this->itsId = $id;
		$this->itsDetailsQuery = "SELECT * FROM manuals WHERE id=$id";
		$row = $this->getItsDetails();
		$this->itsFilepath = "$GLOBALS[UPLOADS_PATH]/manuals_diagrams/".
							$this->itsFilename;
	}
	function showRecord(){
		$result = extractFromArray($this->getItsDetails(),$this->itsFields,$format);
		return $result."<hr width=\"650\" align=\"left\">\n";
	}

	function getFile(){
		return;
	}

	function hyperlink($index,$lang){
		return;
	}

}



//------------------------
class equipmentManualDoc extends basicRecordClass{
	public $itsId;
	public $itsDetails;	//row from database
	public $itsFields = array	(	"id"			=>	"TLD SB#",
								"factory_num"	=>	"Factory#",
								"category"		=>	"Category",
								"description"	=>	"Description",
								"notes"			=>	"Notes"
							);
	public $itsFilename;
	public $itsFilepath;

	function __construct($id){
		$this->itsId = $id;
		$this->itsDetailsQuery = "SELECT * FROM manuals_diag WHERE id=$id";
		$row = $this->getItsDetails();
		$this->itsFilename = $row["diagram_filename"];
		$this->itsFilepath = "$GLOBALS[UPLOADS_PATH]/manuals_diagrams/".
							$this->itsFilename;
	}
	function showRecord(){
		$result = extractFromArray($this->getItsDetails(),$this->itsFields,$format);
		return $result."<hr width=\"650\" align=\"left\">\n";
	}

	function getFile(){
		if(file_exists($this->itsFilepath)){
			header("Content-type: application/pdf");
			header("Content-Disposition: inline; filename=".$this->itsFilename);
			readfile($this->itsFilepath);
		}
	}

	function hyperlink($index,$lang){
		$row = $this->getItsDetails();
		if(empty($lang))$lang="en";
		if(empty($row[$lang."description"])){
			$description = stripslashes($row["endescription"]);
		}else{
			$description = stripslashes($row[$lang."description"]);
		}
		if(file_exists($this->itsFilepath)){
			$result .= "<a href=\"".$_SERVER['PHP_SELF']."?m=dl&n=doc&id=$index\">";
			$result .= $description;
			$result .= "( ".round(filesize($this->itsFilepath)/1000)
						."Kb)<img src=\"/shared/icons/pdf-icon.gif\" alt=\"";
			$result .= $description;
			$result .= "\"></a>";
		}else{
			$result .= $description." File missing, pls contact Webmaster";
		}
		return $result."<br>\n";
	}
}
//------------------------------------------------------------------------
//	partManualDoc class
class partsManualDoc extends basicRecordClass{
	public $itsId;
	public $itsFields;
	public $itsFilename;
	public $itsFilepath;

	function __construct($id){
		$this->itsId = $id;
		$this->itsDetailsQuery = "SELECT * FROM manuals_diag WHERE id=$id";
		$row = $this->getItsDetails();
		$this->itsFilename = $row["diagram_filename"];
		$this->itsFilepath = "$GLOBALS[UPLOADS_PATH]/manuals_diagrams/".
							$this->itsFilename;
		$this->itsFields =  array	(	"id"			=>	"TLD SB#",
								"factory_num"	=>	"Factory#",
								"category"		=>	"Category",
								"endescription"	=>	"English",
								"frdescription"	=>	"French",
								"ennotes"		=>	"Notes",
								"frnotes"		=>	"French Notes"
							);
	}

	function showRecord($id = null){
        return <<<HTML
<html>
    <head>
        <title>Product Support</title>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        <link href="/tld-gse.css" rel="stylesheet" type="text/css">
    </head>
    <frameset  cols="*" rows="300,30,*" frameborder="YES">
        <frame name="topFrame" scrolling="auto" src="{$_SERVER['PHP_SELF']}?m=show&n=partsmanualpic&id=$id">
        <frame name="menuFrame" scrolling="auto" src="{$_SERVER['PHP_SELF']}?m=show&n=partsmanualmenu&id=$id">
        <frame name="mainFrame" scrolling="auto" src="{$_SERVER['PHP_SELF']}?m=show&n=partsmanualparts&id=$id">
    </frameset>
</html>
HTML;
	}

	function showMenu($id){
		return "<html><head></head><body leftmargin=\"0\" topmargin=\"0\"><a href=\"".$_SERVER['PHP_SELF']."?m=show&n=partsmanualparts&id=$id\" target=\"mainFrame\">Parts List</a>".
		" | <a href=\"https://www.tld-parts.com/private/view_order.php\" target=\"_parent\">Check Out</a></body></html>";
	}

	function showPic($id){
		if(file_exists($this->itsFilepath) && $this->itsFilename<>""){
//		$result = "<img src=\"".$_SERVER['PHP_SELF']."?m=dl&n=partsmanualpic&id=$id\">";
			$result = "&nbsp;&nbsp;&nbsp;<img src=\"/shared/bluesphere/16x16/actions/viewmagplus.png\">".
			 "Click, <img src=\"/shared/bluesphere/16x16/actions/viewmagminus.png\">Shift-Click<br>".
			"<applet name=\"imageViewer\" code=\"ImageZoom2.class\" width=\"750\" height=\"250\">".
			"<param name=\"image\" value=\"".$_SERVER['PHP_SELF']."?m=dl&n=partsmanualpic&id=$id\">".
	 		"<param name=\"StartUp\" value=\"w\">".
			"<param name=\"PanSpeed\" value=\"10\">".
			"</applet>";
/*			$image_details=getimagesize($this->itsFilepath);
			$imgwidth=$image_details[0];
			$imgheight=$image_details[1];

				$xratio=$imgwidth/650;
				$yratio=$imgheight/700;
				if($xratio>=$yratio){
					$imgwidth=round($imgwidth/$xratio);
					$imgheight=round($imgheight/$xratio);
				}else{
					$imgwidth=round($imgwidth/$yratio);
					$imgheight=round($imgheight/$yratio);
				}
			$result .= "<img src=\"".$_SERVER['PHP_SELF']."?m=dl&n=partsmanualpic&id=$id\" width=\"$imgwidth\" height=\"$imgheight\">";
*/
		}else{
			$result .= "<p class=\"alert\">No Picture Available</p>";
		}
		return $result;
	}

	function showPartsTable(){
		$fields = array(	"item"		=>	"Item",
							"pn"		=>	"Part Number",
							"qty"		=>	"Quantity",
							"en"		=>	"English",
							"fr"		=>	"French",
							"note"		=>	"Note"
						);
		$result .= tldUtils::extractFromArray($this->getItsDetails(),$this->itsFields,$format);
		//$result .= extractFromArray($this->itsParts,$fields,"table");
		$query = "select * from manuals_parts where parent_id=".$this->itsId.
				" order by item";
		if($rows = TldDatabase::query($query)){
			if(TldDatabase::numRows($rows)){
				$result .= "<table><tr bgcolor=\"#3264C8\" class=\"smallwhite\">";
				foreach($fields as $fieldname=>$title){
					$result .= "<td>".stripslashes($fields[$fieldname])."</td>";
				}
				$result .= "<td>&nbsp;</td></tr>";
				while($row = TldDatabase::fetchArray($rows)){
					$result .= "<tr>";
					foreach($fields as $fieldname=>$title){
						$result .= "<td>".stripslashes($row[$fieldname])."</td>";
					}

					$result .= 	"<td><a href=\"https://www.tld-parts.com/private/find_part.php?pn=".
								$row["pn"]."\">Buy</a></td></tr>";
				}
			}else{
				$result .= "No parts";
			}
		}
		return $result."<hr width=\"650\" align=\"center\">\n";
	}

	function hyperlink($index,$lang){
		$row = $this->getItsDetails();
		$result .= "<a href=\"".$_SERVER['PHP_SELF']."?m=show&n=partsmanualdoc&id=$index\" target=\"_blank\">";
		if($row[$lang."description"]){
			$result .= stripslashes($row[$lang."description"]);
		}else{
			$result .= stripslashes($row["endescription"]);
		}
		$result .= "</a>";
		return $result."<br>\n";
	}

	function getFile(){
		header("Content-type: application/jpg");
		header("Content-Disposition: inline; filename=".$this->itsFilename);
		readfile($this->itsFilepath);
	}
}
