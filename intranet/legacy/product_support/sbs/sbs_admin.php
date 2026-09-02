<?php
include_once("common.inc.php");
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

$main_tpl=array(
  array("table"=>"sbs","form_type"=>"main_tpl","title"=>"Service Bulletins","cancel"=>"table&form_type=",
  		"mode"=>"record_view",
		"child_tables"=>array("sbs_lines","sbs_parts","sbs_files"),
		"prn_table_menu"=>"<a href=\"/en/private/product_support/index.ps.php?m[0]=sbs\">Back to Module</a>&nbsp;|&nbsp;<a href=\"/en/private/product_support/index.ps.php?m[0]=sbs&m[1]=help\">Help</a>&nbsp;|&nbsp;",
  		"prn_record_menu"=>"<b><a href=\"/en/private/product_support/index.ps.php?m[0]=sbs&m[1]=view&id={id}\">Back to Module</a>&nbsp;|&nbsp;</b> | ",
  		"default_sort"=>"entered_date"),
//start of table definitions
  array("name"=>"id",				"label"=>"SB&nbsp;#####",			"type"=>"primary_key",	"table"=>"true","dump"=>"true",
  		"dup_exc"=>"clear"),
  array("name"=>"parent_id",		"label"=>"Foreign Key",				"type"=>"foreign_key",	"table"=>"false"),
  array("name"=>"sb_type",			"label"=>"Type",					"type"=>"select",		"table"=>"true","dump"=>"true",
  		"select_list"=>array(	"SERVICE BULLETIN",
								"INFORMATION BULLETIN"
							)
		),
  array("name"=>"factory_sb_number","label"=>"Factory&nbsp;SB#",		"type"=>"text",			"table"=>"false","dump"=>"true"),
//  array("name"=>"brand",				"label"=>"Brand",					"type"=>"select",		"table"=>"true","dump"=>"true",
 // 		"select_list"=>array("","ALBRET","ERMA","TRACMA","ACE","DEVTEC","LANTIS")),
	array("name"=>"factory",		"label"=>"Factory Location",			"type"=>"select_db",	"table"=>"true",
		"select_query"=>"SELECT location FROM locations WHERE factory='Y' ORDER BY location",
		"select_field_1"=>"location","select_field_2"=>"location"),
  array("name"=>"entered_by",		"label"=>"Entered By",				"type"=>"auto_user",	"table"=>"false","dump"=>"true"),
  array("name"=>"entered_date",		"label"=>"Date&nbsp;(yyyy-mm-dd)",	"type"=>"auto_date",	"table"=>"true","dump"=>"true"),
  array("name"=>"title",			"label"=>"Title",					"type"=>"text",			"table"=>"true","dump"=>"true"),
  array("name"=>"description",		"label"=>"Description",				"type"=>"textarea",		"table"=>"false","dump"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"50\" rows=\"7\""),
  array("name"=>"urgency",				"label"=>"Urgency",					"type"=>"select",		"table"=>"true","dump"=>"true",
  		"select_list"=>array(	"SB:COMPULSORY",
								"SB:RECOMMENDED",
								"IB:OPERATION",
								"IB:IMPROVEMENT",
								"IB:MAINTENANCE"
							)
		),
//  array("name"=>"sbs_file",				"label"=>"Filename",				"type"=>"file_upload",	"table"=>"false",
// 		"file_upload_dir"=>"sbs","dup_exc"=>"clear"),
//	array("name"=>"extranet",		"label"=>"On Extranet?",	"type"=>"select",	"table"=>"false",	"dump"=>"true",
//		"select_list"=>array("YES","NO")
//	)
//  array("name"=>"signature",			"label"=>"Signature",				"type"=>"scribble",		"table"=>"false",
//  		"file_upload_dir"=>"sbs","dup_exc"=>"clear")
);
if($user->isInGroup(array("superuser","gg_ADMIN"))){
  $main_tpl[] = array("name"=>"status",			"label"=>"Status",					"type"=>"select",		"table"=>"true","dump"=>"true",
  		"select_list"=>array(	"PENDING", "APPROVAL", "ER_SELECTION", "IMPLEMENTATION", "CLOSED")
		);
}
$sbs_lines_tpl=array(
	array("table"=>"sbs_lines","form_type"=>"sbs_lines_tpl","title"=>"SN ranges covered","cancel"=>"record_view&form_type=main_tpl","mode"=>"form_view"),
	array("name"=>"id",					"label"=>"Primary Key",		"type"=>"primary_key",		"table"=>"false"),
	array("name"=>"parent_id",			"label"=>"Foreign Key",		"type"=>"foreign_key",		"table"=>"false"),
	array("name"=>"model",				"label"=>"Model",			"type"=>"select_db",	"table"=>"true",
	"select_query"=>"SELECT model FROM models WHERE model<>' ALL_MODELS' ORDER BY model",
	"select_field_1"=>"model","select_field_2"=>"model"),
	array("name"=>"sn_from",			"label"=>"From SN",			"type"=>"text",			"table"=>"true",
	"width"=>"7"),
	array("name"=>"sn_to",				"label"=>"To SN",			"type"=>"text",			"table"=>"true",
	"width"=>"7"),
//	array("name"=>"model_range",				"label"=>"Model Range",			"type"=>"text",			"table"=>"true"),
	array("name"=>"sn_list",			"label"=>"SN List",			"type"=>"textarea",		"table"=>"true",
	"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"80\" rows=\"5\"")
);
$sbs_parts_tpl=array(
	array("table"=>"sbs_parts","form_type"=>"sbs_parts_tpl","title"=>"Parts List","cancel"=>"record_view&form_type=main_tpl","mode"=>"form_view"),
	array("name"=>"id",			"label"=>"Primary Key",		"type"=>"primary_key",	"table"=>"false"),
	array("name"=>"parent_id",	"label"=>"Foreign Key",		"type"=>"foreign_key",	"table"=>"false"),
	array("name"=>"pn",	 		"label"=>"Part Number",		"type"=>"text",			"table"=>"true"),
	array("name"=>"dsca", 		"label"=>"Description",		"type"=>"text",			"table"=>"true"),
	array("name"=>"qty",		"label"=>"Qty",				"type"=>"text",			"table"=>"true"),
	array("name"=>"um",			"label"=>"UM",				"type"=>"text",			"table"=>"true")
);

$sbs_files_tpl=array(
  array("table"=>"sbs_files","form_type"=>"sbs_files_tpl",
  		"title"=>"Files",
  		"cancel"=>"record_view&form_type=main_tpl","mode"=>"table"),
  array("name"=>"id",			"label"=>"Primary Key",			"type"=>"primary_key",	"table"=>"false"),
  array("name"=>"parent_id",	"label"=>"Foreign Key",			"type"=>"foreign_key",	"table"=>"false"),
  array("name"=>"date",			"label"=>"Date (YYYY-MM-DD)",	"type"=>"auto_date",	"table"=>"true"),
  array("name"=>"description",	"label"=>"File description",	"type"=>"text",			"table"=>"true",
  		"width"=>"25"),
  array("name"=>"filename",		"label"=>"Filename",			"type"=>"file_upload",	"table"=>"true",
  		"file_upload_dir"=>"sbs_files")
);

include("header.inc.php");
//Include common db functions
include("db_common.inc.php");

switch ($mode) {
    case "show_sbs":
    	echo "Looking for service bulletins for model=$model and SN=$sn<br>";
    	if (empty($sn) || empty($model)){
    		echo "ERROR: No params given";
    		exit;
    	}
		$result = tldUtils::getSqlToAssocArray("SELECT sbs_lines.parent_id,sbs_lines.sn_from,sbs_lines.sn_to,sbs_lines.sn_list,sbs.title FROM sbs_lines,sbs WHERE ((sn_from='*' AND sn_to='*') or ('$sn' BETWEEN sn_from AND sn_to) or sn_list like '%$sn%') AND model='$model' AND sbs.id=sbs_lines.parent_id");

		if(count($result)){
?>
		  <table width="700" bgcolor="#CCCCCC" border="1" bordercolor="#FFFFFF" cellspacing="0" cellpadding="1" class="smalltext">
			<tr class="smallwhite"><td>SB#</td><td>SN From</td><td>SN To</td><td>SN List</td><td>Title</td></tr><?php
			foreach($result as $rows){
				?>
				<tr><td>
					<a href="/en/private/service/<?php echo $PHP_SELF?>?mode=record_view&form_type=main_tpl&id=<?php echo $rows["parent_id"]?>"><?php echo $rows["parent_id"]?></a>
				</td>
				<td>
					<?php if($rows["sn_from"]){echo $rows["sn_from"];}else{echo "&nbsp;";}?>
				</td>
				<td>
					<?php if($rows["sn_to"]){echo $rows["sn_to"];}else{echo "&nbsp;";}?>
				</td>
				<td>
					<?php if($rows["sn_list"]){echo $rows["sn_list"];}else{echo "&nbsp;";}?>
				</td>
				<td>
					<?php if($rows["title"]){echo $rows["title"];}else{echo "&nbsp;";}?>
				</td>
				</tr><?php
			}
			?>
		  </table><?php
		  }else{?>
			<script>
	        	alert("No service bulletins found for model and SN.");
	        </script>
	        <meta http-equiv="refresh" content="0;URL=javascript:history.back()"><?php
		  }
		  exit;
    break;

}
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
<link rel="stylesheet" href="/tld-gse.css">
<title>Service Bulletins</title>
</head>
<body class="smalltext">
<div align="center">
<script>
  function popup(filename,title) {
    window.open(filename,title, 'scrollbars=yes,status=no,toolbar=no,menubar=no,width=750,height=500');
  }
</script>

<?php
if($sess_error_message){
?>
<FONT color="#ff0000"><b><?php echo $sess_error_message;
$sess_error_message='';
?></b></font><br>
<?php
}
//Include db functions
if($user->isInGroup(array("superuser"))){
	include("db_admin.inc.php");
}else{
	include("db_readonly.inc.php");
}
?>
  </div>
</body>
</html>
