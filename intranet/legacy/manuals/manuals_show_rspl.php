<?php
//Set arrays/variables
//
include("manuals_diagrams_table_def.inc.php");

include("common.inc.php");

?>
<html>
<head>
<title>RSPL</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="/tld-gse.css" rel="stylesheet" type="text/css">
</head>

<body bgcolor="#FFFFFF" text="#000000">
<?php
prn_rspl_table($id,$group,$lang,$format)
?>
</body>
</html>