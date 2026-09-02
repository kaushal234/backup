<?php
//Set arrays/variables
//
include("manuals_diagrams_table_def.inc.php");
include("common.inc.php");
?>
<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="/tld-gse.css" rel="stylesheet" type="text/css">
</head>

<body bgcolor="#FFFFFF" text="#000000" leftmargin="0" topmargin="0">
<?php
	if($id){
		prn_diagram($id,$lang,1);
	}else{
		echo"No id set!";
	}
	?>
</body>
</html>
