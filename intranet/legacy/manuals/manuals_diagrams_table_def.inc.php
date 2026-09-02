<?php
//Table definitions for Manuals Diagrams
$main_tpl=array(
//table parameters
  array("table"=>"manuals_diag","form_type"=>"main_tpl","title"=>"Documentation",
  		"cancel"=>"table&form_type=","mode"=>"record_view",
  		"child_tables"=>array("manuals_parts"),
		"prn_table_menu"=>"<a href=\"/en/private/product_support/index.ps.php?m[0]=publications\">Back to module</a>",
  		"prn_record_menu"=>"<a href=\"https://www.tld-gse.com/en/private/manuals/manuals_edit_documents.php?id={id}\">Quick Edit</a> | ".
		"<b>Parts Diagram Preview <a href=\"/en/private/sales_service/publications/publications.php?m[0]=documents&m[1]=view&id={id}\">Online</a></b> | ",
//		"<b><a href=\"https://www.tld-gse.com/en/private/manuals/manuals.pl?m=partsdiagrampdf&id={id}\">PDF File</a></b> | ",
  		"default_sort"=>"id"
		),
//start of table definition
//Parts list SECTION
  array("name"=>"id",			"label"=>"TLD Doc#",		"type"=>"primary_key",	"table"=>"true",
  		"dump"=>"true",	"dup_exc"=>"clear"),
  array("name"=>"parent_id",	"label"=>"Foreign Key",		"type"=>"foreign_key",	"table"=>"false"),
  array("name"=>"factory_num",	"label"=>"Factory Doc#",	"type"=>"text",			"table"=>"true"),
  array("name"=>"rev",			"label"=>"Revision",		"type"=>"text",			"table"=>"true"),
  array("name"=>"category",		"label"=>"Category",		"type"=>"select",		"table"=>"true",
		"select_list"=>array(	"",
								"BOOM",
								"BODY",
								"BRAKING",
								"BRIDGE",
								"CHASSIS",
								"COMPRESSOR",
								"DRIVER CAB",
								"ELEC CONTROL",
								"ELEVATOR",
								"ENGINE",
								"GENERATOR",
								"HITCHING",
								"HYDRAULIC",
								"LABELS",
								"OPTIONS",
								"PLUMBING",
								"PNEUMATIC",
								"REFRIGERATION",
								"SUSPENSION",
								"STRUCTURAL",
								"TRANSMISSION"
							)
		),
  array("name"=>"doc_type",		"label"=>"Doc Type",				"type"=>"select",		"table"=>"true",
  		"select_list"=>array(	"PARTS DIAGRAM",
								"HYD SCHEM",
								"ELEC SCHEM",
								"FLOW SCHEM",
								"MANUAL:TOC",
								"MANUAL:SECTION",
								"MANUAL:APPENDIX",
								"MANUAL:OEM LIT"
							)
		),
  array("name"=>"endescription",		"label"=>"Description",				"type"=>"textarea",		"table"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"40\" rows=\"7\""),
  array("name"=>"ennotes",		"label"=>"Notes",						"type"=>"textarea",		"table"=>"false",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"40\" rows=\"7\""),
  array("name"=>"frdescription",		"label"=>"French Description",	"type"=>"textarea",		"table"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"40\" rows=\"7\""),
  array("name"=>"frnotes",		"label"=>"French Notes",				"type"=>"textarea",		"table"=>"false",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"40\" rows=\"7\""),
  array("name"=>"diagram_filename",		"label"=>"Filename (max 50 chars)",			"type"=>"file_upload",	"table"=>"false",
  		"file_upload_dir"=>"manuals_diagrams")
);

$manuals_parts_tpl=array(
  array("table"=>"manuals_parts","form_type"=>"manuals_parts_tpl",
  		"title"=>"Document parts",
  		"cancel"=>"record_view&form_type=main_tpl","mode"=>"table",
  		"default_sort"=>"item ASC"
		),
  array("name"=>"id",			"label"=>"Primary Key",		"type"=>"primary_key",	"table"=>"false"),
  array("name"=>"parent_id",	"label"=>"Foreign Key",		"type"=>"foreign_key",	"table"=>"false"),
  array("name"=>"item",			"label"=>"Item#",			"type"=>"text",			"table"=>"true"),
  array("name"=>"pn",			"label"=>"PN",				"type"=>"text",			"table"=>"true"),
  array("name"=>"qty",			"label"=>"Quantity",		"type"=>"text",			"table"=>"true"),
  array("name"=>"um",			"label"=>"UM",				"type"=>"text",			"table"=>"true"),
  array("name"=>"en",			"label"=>"English",			"type"=>"text",			"table"=>"true"),
  array("name"=>"fr",			"label"=>"French",			"type"=>"text",			"table"=>"true"),
  array("name"=>"note",			"label"=>"Notes",			"type"=>"text",			"table"=>"false"),
  array("name"=>"group_p",		"label"=>"P Qty",			"type"=>"text",			"table"=>"true"),
  array("name"=>"group_m",		"label"=>"M Qty",			"type"=>"text",			"table"=>"true"),
  array("name"=>"group_o",		"label"=>"O Qty",			"type"=>"text",			"table"=>"true"),
  array("name"=>"group_c",		"label"=>"C Qty",			"type"=>"text",			"table"=>"true")

);


function prn_menu($id,$m,$lang){
	$PHP_SELF = $_SERVER['PHP_SELF'];
	$equip = new tldEquipment($id);
	$erp = $equip->getERP();

	$row=$equip->getHeader();
	$result=<<<EOF
	<table width="100%" bgcolor="#CCCCCC"><tr class="xxsmalltext"><td align="left">
        <a href="$PHP_SELF?m=servicing&id=$id&lang=$lang">Servicing</a> |
	<a href="$PHP_SELF?m=manuals&id=$id&lang=$lang">Manuals</a> |
	<a href="$PHP_SELF?m=schematics&id=$id&lang=$lang">Schematics</a> |
	<a href="$PHP_SELF?m=parts&id=$id&lang=$lang">Parts Diagrams</a> |
	<a href="$PHP_SELF?m=serials&id=$id&lang=$lang">Serials</a> |
	<a href="$PHP_SELF?m=rspl&id=$id&lang=$lang">RSPL</a>
EOF;
	$doclist = $equip->getDocList();
	if(count($doclist ?? []) && $erp){
		$doclistPN = $doclist[0]["mitm"];
		$date = date("Y-m-d");
		$result .=<<<EOF
		&nbsp;|&nbsp;
		<a href="/en/private/manufacturing/eng/dev.php?m[0]=bom&m[1]=view&erp=$erp&pn=$doclistPN&date=$date">
		Document List</a>
EOF;
	}

	$result .=<<<EOF
	</td>
	<td align="right">
EOF;
		if(empty($lang))$lang="en";

		$languages=array("en"=>"English","fr"=>"French");
		$result .= "[";
		foreach ($languages as $langcode => $langname){
			if($langcode==$lang){
				$result .= $languages[$lang]."&nbsp;";
			}else{
				$result .=<<<EOF
				<a href="<?php echo $PHP_SELF?>?m=<?php echo $m?>&id=<?php echo $id?>&lang=<?php echo $langcode?>">
				<?php echo strtoupper($langcode)?>
				</a>&nbsp;
EOF;
			}
		}
	$result .=<<<EOF
	]
	<b>${row["model"]}, SN: ${row["sn"]}, ERP: $erp</b>
	</td>
	</tr></table>
EOF;
	return $result;
}

function prn_manual_page($id,$lang=""){
	$rows=TldDatabase::query("SELECT * FROM manuals_diag WHERE id=$id");
	$row=TldDatabase::fetchArray($rows);
	//Construct diagram
?>
<table width="650">
  <tr><td>
	<img src="/shared/icons/tld-icon.jpg" align="right"><h1><?php echo showit($row["factory_num"])?></h1>
	<?php prn_diagram($id,$lang,0);?>
	<p><?php echo showit($row[$lang."notes"])?></p>
	<h2><?php echo showit($row["factory_num"])?></h2>
	<?php prn_parts_table($id,$lang);?>
	</td></tr>
	</table><?php
}

function prn_diagram($id,$lang,$online){

	$rows=TldDatabase::query("SELECT * FROM manuals_diag WHERE id=$id");
	$row=TldDatabase::fetchArray($rows);

	$diagram_filename="/en/private/uploads/manuals_diagrams/".$row["diagram_filename"];
	?><p><?php echo stripslashes($row[$lang."description"])?><?php
	if(file_exists($GLOBALS['WEB_ROOT'] .$diagram_filename) && $row["diagram_filename"]<>''){
		$image_details=getimagesize($GLOBALS['WEB_ROOT'].$diagram_filename);
		$imgwidth=$image_details[0];
		$imgheight=$image_details[1];

		if($online){
			?>&nbsp;&nbsp;&nbsp;<img src="/shared/bluesphere/16x16/actions/viewmagplus.png">
			 Click, <img src="/shared/bluesphere/16x16/actions/viewmagminus.png">Shift-Click<br>
			<applet name="imageViewer" code="ImageZoom2.class" width="750" height="250">
			<param name="image" value="<?php echo $diagram_filename?>">
	 		<param name="StartUp" value="w">
			 <param name="PanSpeed" value="10">
			</applet>
			<?php
		}else{
			$xratio=$imgwidth/650;
			$yratio=$imgheight/700;
			if($xratio>=$yratio){
				$imgwidth=round($imgwidth/$xratio);
				$imgheight=round($imgheight/$xratio);
			}else{
				$imgwidth=round($imgwidth/$yratio);
				$imgheight=round($imgheight/$yratio);
			}
			?><img src="<?php echo $diagram_filename?>" width="<?php echo $imgwidth?>" height="<?php echo $imgheight?>"><?php
		}
	}else{
		?><p class="alert">No Picture Available<?php
	}
	?></p><?php
}

function prn_parts_table($id,$lang){
if(empty($lang))$lang="en";
?>
<table width="650" border="0" cellpadding="1" cellspacing="1" bgcolor="#999999">
  <tr bgcolor="#FFFFFF">
    <td><b>Item</b></td>
    <td><b>Part#</b></td>
    <td><b>Qty</b></td>
    <td><b>Description, Note</b></td>
    <td><b>A</b></td>
    <td><b>B</b></td>
    <td><b>C</b></td>
	<td>Buy</td>
  </tr><?php
	$parts=TldDatabase::query("SELECT * FROM manuals_parts WHERE parent_id=$id ORDER BY item");
	while($part=TldDatabase::fetchArray($parts)){
?>
  <tr bgcolor="#FFFFFF">
    <td><?php echo showit($part["item"])?></td>
    <td><?php echo showit($part["pn"])?></td>
    <td><?php echo showit($part["qty"])?></td>
    <td><b><?php
	if($part[$lang]){
		echo showit($part[$lang]);
	}else{
		echo showit($part["en"]);
	}
	?></b>
		<?php
	if($part[$lang]){
		echo showit($part[$lang."note"]);
	}else{
		echo showit($part["note"]);
	}
	?>
    </td>
    <td><?php echo showit($part["group_a"])?></td>
    <td><?php echo showit($part["group_b"])?></td>
    <td><?php echo showit($part["group_c"])?></td>
	<td><a href="https://www.tld-parts.com/private/find_part.php?pn=<?php  echo $part["pn"]?>">Buy</a></td>
  </tr>
  <?php
	echo "\n";
	}
	?>
</table>
<?php
}
function prn_rspl_table($id,$group,$lang,$format=""){
	if(empty($lang))$lang="en";
	if(empty($group) || !in_array($group,array("p","m","o","c"))){
		?>Invalid group id<?php
		return 0;
	}
	$query=		"SELECT manuals_parts.* FROM manuals_docs,manuals_diag,manuals_parts".
				" WHERE manuals_docs.doc_num=manuals_diag.id AND manuals_diag.id=manuals_parts.parent_id".
				" AND manuals_docs.parent_id=$id and manuals_parts.group_$group>0".
				" ORDER BY manuals_parts.pn";
	$parts=TldDatabase::query($query);
	if(TldDatabase::numRows($parts) < 1){
		?><p class="alert">No Recommended Spare Parts for Group <?php
		echo strtoupper($group);
		?></p><?php
		return 0;
	}
	switch($format){
		case csv:
			echo "Group ".strtoupper($group)." RSPL\nItem,Part#,Qty,Description,Note\n";
			while($part=TldDatabase::fetchArray($parts)){
				echo $part["item"].",".$part["pn"].",".$part["group_$group"].",";
				if($part[$lang]){
					echo $part[$lang];
				}else{
					echo $part["en"];
				}
				echo ",";
				if($part[$lang]){
					echo $part[$lang."note"];
				}else{
					echo $part["note"];
				}
				echo "\n";
			}
		break;
		case links:
			?><h3>Group <?php echo strtoupper($group)?> RSPL</h3>
			<a href="<?php echo $PHP_SELF?>?dl=2&id=<?php echo $id?>&group=<?php echo $group?>&lang=<?php echo $lang?>">
			<img src="/shared/icons/pdf-icon.gif"></a> |
			<a href="<?php echo $PHP_SELF?>?dl=2&id=<?php echo $id?>&group=<?php echo $group?>&lang=<?php echo $lang?>&format=csv">
			<img src="/shared/icons/excel-icon.gif"></a>
			<?php
		break;
		default:
			?><h3>Group <?php echo strtoupper($group)?> RSPL</h3>
			<table width="650" border="0" cellpadding="1" cellspacing="1" bgcolor="#999999">
			  <tr bgcolor="#FFFFFF">
				<td><b>Item</b></td>
				<td><b>Part#</b></td>
				<td><b>Qty</b></td>
				<td><b>Description, Note</b></td>
			  </tr>
				<?php
				while($part=TldDatabase::fetchArray($parts)){
				?>
			  <tr bgcolor="#FFFFFF">
				<td><?php echo showit($part["item"])?></td><td><?php echo showit($part["pn"])?></td>
				<td><?php echo showit($part["group_$group"])?></td><td><b><?php
				if($part[$lang]){
					echo showit($part[$lang]);
				}else{
					echo showit($part["en"]);
				}
				?></b><?php
				if($part[$lang]){
					echo showit($part[$lang."note"]);
				}else{
					echo showit($part["note"]);
				}
				?>
					</td>
				  </tr>
				  <?php
				echo "\n";
				}
				?>
			</table>
			<?php
	}
	return 1;
}

function get_unit_data($id){
	$query="SELECT * FROM service WHERE id=$id";
	$rows=TldDatabase::query($query);
	$row=TldDatabase::fetchArray($rows);
	return $row;
}

function get_manual_id($id){
	//Get equipments manual number
	$query=	"SELECT * FROM service,service_serials".
			" WHERE service.id=service_serials.parent_id".
			" AND service.id=$id AND service_serials.component='MANUAL'";
	if($rows=TldDatabase::query($query))
	{
		$row=TldDatabase::fetchArray($rows);
		$manual_id=$row["serial"];
	}
	return $manual_id;
}

function show_manual($id,$lang){
	if(empty($id))return;
	if(empty($lang))$lang="en";
	/////////////////////////////////////////////////////////////////////////////
	//OPERATOR'S MANUAL
	$query=	"SELECT DISTINCT manuals_diag.category".
		" FROM manuals,manuals_docs,manuals_diag".
		" WHERE manuals.id=$id AND manuals_docs.parent_id=manuals.id AND manuals_docs.doc_num=manuals_diag.id".
		" AND manuals_diag.doc_type like 'MANUAL%'".
		" GROUP BY manuals_diag.category ORDER BY manuals_diag.category";
	$rows=TldDatabase::query($query);
	if(TldDatabase::numRows($rows) > 0){
		//Create list of categories for TOC
		while($row=TldDatabase::fetchArray($rows)){
			$categories[]=$row["category"];
		}
		//Print TOC
		?><h4>Table of Contents</h4><p><ul><?php
		foreach($categories as $category){
			if(empty($category))continue;
			?><li><a href="#manual_<?php echo $category;?>"><?php
			echo $category;
			?></a></li><?php
		}
		?></ul><?php
		foreach($categories as $category){
			?><hr><h5><a name="manual_<?php echo $category?>"></a><?php echo $category?></h5><?php
			$query=	"SELECT manuals_docs.*,manuals_diag.*".
					" FROM manuals,manuals_docs,manuals_diag".
					" WHERE manuals.id=$id AND manuals_docs.parent_id=manuals.id AND manuals_docs.doc_num=manuals_diag.id".
					" AND manuals_diag.doc_type like 'MANUAL%' AND manuals_diag.category='$category'".
					" ORDER BY manuals_diag.".$lang."description";
			//					echo $query;
			list_manuals_docs($query,$lang);
			?><a href="#top">Back to top</a><?php
		}
	}
}

function show_parts_diagrams($man_id, $add_table, $add_and, $lang){
	if(empty($lang))$lang="en";
	$text = array	(
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
					"TRANSMISSION"	=>array("fr"=>"TRANSMISSION"),
					"LABELS"		=>array("fr"=>"LABELS"),
					"DRIVER CAB"	=>array("fr"=>"DRIVER CAB"),
					"OPTIONS"	=>array("fr"=>"OPTIONS")
					)

	?><p><?php
	/////////////////////////////////////////////////////////////////////////////
	//PARTS DIAGRAMS

	//Select all distint document categories for this manual
	$query=	"SELECT DISTINCT manuals_diag.category".
			" FROM manuals,manuals_docs,manuals_diag".$add_table.
			" WHERE manuals.id=$man_id AND manuals_docs.parent_id=manuals.id AND manuals_docs.doc_num=manuals_diag.id".
			" AND manuals_diag.doc_type='PARTS DIAGRAM'".
			$add_and.
			" GROUP BY manuals_diag.category ORDER BY manuals_diag.category";
//echo $query;
	$rows=TldDatabase::query($query);
	if(TldDatabase::numRows($rows)){
			?>
           <!--a href="manuals_page_pdf.php?multi=<?php echo $man_id?>&lang=<?php echo $lang?>"-->
           <!-- a href="pickup.php?url=<?php echo urlencode("manuals.pl?m=partsbookpdf&id=$man_id&lang=$lang")?>"-->
           <a href="pickup.php?url=<?php echo urlencode("/en/private/sales_service/publications/publications.php?m[0]=manuals&m[1]=pdf&id=$man_id&lang=$lang")?>">
		   <img src="/shared/icons/pdf-icon.gif" alt="Download Parts Book"> Parts book</a><br>
		   Click blue link to see a page online.<br>
		   Click the <img src="/shared/icons/pdf-icon.gif" alt="Download Parts Book"> to download a page in PDF.<?php

		//Create list of categories for TOC
		while($row=TldDatabase::fetchArray($rows)){
			$categories[]=$row["category"];
		}
		//Print TOC
		?><h4>Table of Contents</h4><p><ul><?php
		foreach($categories as $category){
			if(empty($category))continue;
			?><li><a href="#<?php echo $category;?>"><?php
			if($lang == "en" || (empty($text[$category][$lang]) && $lang<>"en")){
				echo $category;
			}else{
				echo $text[$category][$lang];
			}
			?></a></li><?php
		}
		?></ul></p><br>
		<?php
		foreach($categories as $category){
			$query=	"SELECT DISTINCT manuals_diag.id,manuals_diag.*".
					" FROM manuals,manuals_docs,manuals_diag".$add_table.
					" WHERE manuals.id=manuals_docs.parent_id AND manuals_docs.doc_num=manuals_diag.id".
					" AND manuals.id=$man_id AND manuals_diag.doc_type='PARTS DIAGRAM'".
					" AND manuals_diag.category='$category'".
					$add_and.
					" ORDER BY manuals_docs.item";

/*			$query=	"SELECT DISTINCT manuals_diag.id,manuals_diag.*".
					" FROM service,service_serials,manuals,manuals_docs,manuals_diag".$add_table.
					" WHERE service.id=service_serials.parent_id AND service_serials.serial=manuals.id".
					" AND manuals.id=manuals_docs.parent_id AND manuals_docs.doc_num=manuals_diag.id".
					" AND service.id='$id' AND manuals_diag.doc_type='PARTS DIAGRAM'".
					" AND manuals_diag.category='$category'".
					$add_and.
					" ORDER BY manuals_docs.item";
*/
			$rows=TldDatabase::query($query);
			?><hr><h5><a name="<?php echo $category?>"></a><?php
			if($lang == "en"){
				echo $category;
			}else{
				echo $text[$category][$lang];
			}
			?></h5>
			<p><ul><?php
			while($row=TldDatabase::fetchArray($rows)){
				?>
             <li>
			 <a href="javascript:void(0)" onclick="javascript:window.open('/en/private/product_support/index.ps.php?m[0]=publications&m[1]=documents&m[2]=view&id=<?php echo $row["id"]?>&lang=<?php echo $lang?>','Parts_diagram', 'scrollbars=yes')">

               <!--a href="javascript:showdiagram(<?php echo $row["id"]?>,'<?php echo $lang?>')"--><?php
			   if($row[$lang."description"]){
			  		echo showit($row[$lang."description"]);
				}else{
					echo showit($row["endescription"]);
				}
			   ?> (<?php echo showit($row["factory_num"])?>)
			   </a>
				|
				<a href="/en/private/sales_service/publications/publications.php?m[0]=documents&m[1]=pdf&id=<?php echo $row["id"]?>">
				<!--a href="manuals.pl?m=partsdiagrampdf&id=<?php echo $row["id"]?>&lang=<?php echo $lang?>"--->
				<!--a href="manuals_page_pdf.php?id=<?php echo $row["id"]?>&lang=<?php echo $lang?>"-->
				<img src="/shared/icons/pdf-icon.gif" alt="<?php
			   if($row[$lang."description"]){
			  		echo showit($row[$lang."description"]);
				}else{
					echo showit($row["endescription"]);
				}
				?>"></a>
             </li>
             <?php
			}
			?>
           </ul>
           <a href="#top">Back to top</a></p><?php
		//if(empty($category))break;
		}
	}else{
		?>
          <p class="alert">No Parts Diagrams available online for this unit</p>
          <?php
	}
}


function create_and_query($tpl,$target){
	while($field=next($tpl)){
		//echo $field["name"];
		$restriction.=" OR ".$tpl[0]["table"].".".$field["name"]." like '%".$target."%'";
	}
	return $restriction;
}

function list_manuals_docs($query,$lang){
	if(empty($lang))$lang="en";
	$rows=TldDatabase::query($query);
	if(TldDatabase::numRows($rows)){
		?><ul><?php
		while($row=TldDatabase::fetchArray($rows)){
			$file="/manuals_diagrams/".$row["diagram_filename"];
			?><li><?php
			if(file_exists($GLOBALS['UPLOADS_PATH'].$file)){
			?>
			<a href="javascript:void(0)" onclick="javascript:window.open('/en/private/uploads<?= $file ?>','Closeup', 'scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,resizable=yes,width=640,height=480')"><?php
			   if($row[$lang."description"]){
			  		echo showit($row[$lang."description"]);
				}else{
					echo showit($row["endescription"]);
				}
			?>
	          ( <?php echo round(filesize($GLOBALS['UPLOADS_PATH'].$file)/1000)?>Kb)
			  <img src="/shared/icons/pdf-icon.gif" alt="<?php
			   if($row[$lang."description"]){
			  		echo showit($row[$lang."description"]);
				}else{
					echo showit($row["endescription"]);
				}
			  ?>"></a><?php
			}else{
				?>File missing, pls contact Webmaster<?php
			}?></li><?php
		}
		?></ul><?php
	}else{
		?>
      <p class="alert">No manual sections available online for this unit</p>
      <?php
	}
}

function showit($string){
	if(empty($string)){
		return "&nbsp;";
	}else{
		return nl2br(htmlspecialchars(stripslashes($string)));
	}
}
?>
