<html>
<head>
<title>Product Support</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="/tld-gse.css" type="text/css">
<script>
function showdiagram(id,lang){
	window.open('https://www.tld-gse.com/en/private/manuals/manuals_show_diagram_index.php?m=1&id='+id+'&lang='+lang,'Parts_diagram', 'width=700,height=550,resizable');
}
</script>
</head>
<a name="top"></a><body class="smalltext">

<?php
include("manuals_diagrams_table_def.inc.php");
include("common.inc.php");

show_manual($id,"en");

show_parts_diagrams($id,$add_table,$add_and,$lang);
	?><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
</body>
</html>
