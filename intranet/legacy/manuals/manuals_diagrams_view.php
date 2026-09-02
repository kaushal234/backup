<?php
//Set arrays/variables
//
include("manuals_diagrams_table_def.inc.php");
include("common.inc.php");

if($eqid){
	$unit_data=get_unit_data($eqid);
	$title= $unit_data["model"].", SN:".$unit_data["sn"];
}
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
<link rel="stylesheet" href="/tld-gse.css">
<title><?php if($title)echo $title.","?> Part Diagram</title>
</head>
<body class="smalltext">
<?php
switch ($m){
	case 1:
		if(empty($pdf)){
		 ?><a href="manuals_page_pdf.php?id=<?php echo $id?>" target="blank">
		 Download PDF version<img src="/shared/icons/pdf-icon.jpg" alt="<?php echo showit($row["description"])?>">
		 </a><?php
		}
		if($id){
			prn_manual_page($id,$lang);
		}else{
			echo "No Diagram id set!";
		}
	break;
	default:
	?>
  <form name="form1" method="post" action="<?php echo $PHP_SELF?>">
  	<input type="hidden" name="m" value="1">
    Diagram ID: <input type="text" name="id">
    <input type="submit" name="Submit" value="Submit">
  </form>
	<?php
}
?>
<p class="xxsmalltext">Copyright <?php echo date("Y")?> TLD, Printed <?php echo date ("l dS of F Y h:i:s A")?> EST</p>
</body>
</html>
