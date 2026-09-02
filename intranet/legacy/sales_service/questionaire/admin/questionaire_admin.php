<?php
$main_tpl=array(
  array("table"=>"questionaire","form_type"=>"main_tpl","title"=>"TLD Questionaire",
  	"prn_table_menu"=>"<a href=\"/en/private/sales_service/questionaire/admin/index.htm\">Back to module</a>",
  		"prn_record_menu"=>"<a href=\"/en/private/sales_service/questionaire/index.php?m[0]=start&m[1]=specific&id={id}\">Preview</a>&nbsp;|&nbsp;",
  "cancel"=>"table&form_type=main_tpl","mode"=>"record_view",
  "default_sort"=>"id"
),
  array("name"=>"id",		"label"=>"Primary Key",	"type"=>"primary_key","table"=>"true"),
  array("name"=>"parent_id","label"=>"Foreign Key",	"type"=>"foreign_key","table"=>"false"),
  array("name"=>"date",		"label"=>"Date Entered (YYYY-MM-DD)","type"=>"auto_date",	"table"=>"false",
  "dump"=>"true"),
  array("name"=>"expiration","label"=>"Expiration Date (YYYY-MM-DD) Default +24months","type"=>"auto_date",	"table"=>"false",
  "add"=>array("years"=>"","months"=>"24","days"=>""), "dump"=>"true"),
  array("name"=>"author",	"label"=>"Entered By",	"type"=>"auto_user",	"table"=>"false","dump"=>"true",
  		"field"=>"email"),
	array("name"=>"category",	"label"=>"Category", "type"=>"select",		"table"=>"true",
		"source"=>"lists", "list_name"=>"equip_cat"),
	array("name"=>"category2",	"label"=>"Category2", "type"=>"select",		"table"=>"true",
		"source"=>"lists", "list_name"=>"questionaire_cat"),
	array("name"=>"difficulty",	"label"=>"Difficulty", "type"=>"select",		"table"=>"true",
			"select_list"=>array("1","2","3")
		),
  array("name"=>"question",	"label"=>"Question",	"type"=>"textarea","table"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"80\" rows=\"7\""),
  array("name"=>"picture",	"label"=>"Diagram",		"type"=>"file_upload",	"table"=>"false",
		"file_upload_dir"=>"questionaire","dup_exc"=>"clear"),
	array("name"=>"response1",	"label"=>"Response 1",		"type"=>"textarea",	"table"=>"false",
		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"80\" rows=\"7\""),
	array("name"=>"reason1",	"label"=>"Reason 1",		"type"=>"textarea",		"table"=>"false",
		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"80\" rows=\"7\""),
	array("name"=>"response2",	"label"=>"Response 2",		"type"=>"textarea",	"table"=>"false",
		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"80\" rows=\"7\""),
	array("name"=>"reason2",	"label"=>"Reason 2",		"type"=>"textarea",		"table"=>"false",
		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"80\" rows=\"7\""),
  array("name"=>"reason_picture",	"label"=>"Picture for reason",		"type"=>"file_upload",	"table"=>"false",
		"file_upload_dir"=>"questionaire","dup_exc"=>"clear"),
	array("name"=>"answer",	"label"=>"Answer", "type"=>"select",		"table"=>"false",
			"select_list"=>array(	"1","2")
		)
);

include("header.inc.php");
//$form_array=${$MY_SESS[$running]["form_type"]};

//Include common db functions
include("db_common.inc.php");
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);
if(!$user->isInGroup("questionaire")){
	echo "Your are not authorised to access this page";
	exit;
}
?>
<html>
<head>
	<title>Questionaire Admin</title>
<link rel="stylesheet" href="/tld-gse.css">
</head>
<body class="smalltext"><div align="center">
<?php
if (!$id)$id=0;
//Include db functions
include("db_admin.inc.php");

?>
</div>
</body>
</html>
