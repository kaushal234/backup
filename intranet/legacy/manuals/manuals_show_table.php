<?php
//Set arrays/variables
//
include("manuals_diagrams_table_def.inc.php");
include("common.inc.php");

$rows=TldDatabase::query("SELECT * FROM manuals_diag WHERE id=$id");
$row=TldDatabase::fetchArray($rows);

?>
<html>
<head>
<title><?php echo $row["factory_num"]?></title>
<link rel="stylesheet" href="/tld-gse.css">
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body bgcolor="#FFFFFF" text="#000000">
<p><?php
if($id){
	prn_parts_table($id,$lang);
}else{
	echo"No id set!";
}

?></p>
</body>
</html>