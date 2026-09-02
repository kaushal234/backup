<?php
$main_tpl=array(
  array("table"=>"investors","form_type"=>"main_tpl","title"=>"Investors",
  "cancel"=>"table&form_type=main_tpl","mode"=>"record_view",
  "default_sort"=>"date"),
  array("name"=>"id",			"label"=>"Primary Key",				"type"=>"primary_key",	"table"=>"false"),
  array("name"=>"parent_id",	"label"=>"Foreign Key",				"type"=>"foreign_key",	"table"=>"false"),
  array("name"=>"date",			"label"=>"Date&nbsp;(yyyy-mm-dd)",	"type"=>"auto_date",	"table"=>"true","dump"=>"true"),
  array("name"=>"en_title",		"label"=>"English Title",			"type"=>"text",			"table"=>"true","dump"=>"true"),
  array("name"=>"en",			"label"=>"English Article",			"type"=>"textarea",		"table"=>"false","dump"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"60\" rows=\"10\""),
  array("name"=>"fr_title",		"label"=>"French Title",			"type"=>"text",			"table"=>"false","dump"=>"true"),
  array("name"=>"fr",			"label"=>"French Article",			"type"=>"textarea",		"table"=>"false","dump"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"60\" rows=\"10\""),
  array("name"=>"es_title",		"label"=>"Spanish Title",			"type"=>"text",			"table"=>"false","dump"=>"true"),
  array("name"=>"es",			"label"=>"Spanish Article",			"type"=>"textarea",		"table"=>"false","dump"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"60\" rows=\"10\""),
  array("name"=>"pt_title",		"label"=>"Portugues Title",			"type"=>"text",			"table"=>"false","dump"=>"true"),
  array("name"=>"pt",			"label"=>"Portugues Article",		"type"=>"textarea",		"table"=>"false","dump"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"60\" rows=\"10\"")
);

include("header.inc.php");
//$form_array=${$MY_SESS[$running]["form_type"]};
//Include common db functions
include("db_common.inc.php");

?>
<html>
<head>
	<title>Untitled</title>
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
