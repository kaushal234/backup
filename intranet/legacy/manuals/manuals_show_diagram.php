<?php
//Set arrays/variables
//
include("manuals_diagrams_table_def.inc.php");
include("common.inc.php");
?>
<html>
<head>
<title>Untitled Document</title>
<link rel="stylesheet" href="/tld-gse.css">
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body bgcolor="#FFFFFF" text="#000000">
<?php
if($id){
	prn_diagram($id);
}else{
	echo"No id set!";
}

	?>
</body>
</html>
