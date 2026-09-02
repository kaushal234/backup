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
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<script language="JavaScript">
//window.onload = maxWindow;
//function maxWindow(){
	window.moveTo(0,0);
	//if (document.all)
	//{
	  //top.window.resizeTo(screen.availWidth,screen.availHeight);
	  top.window.resizeTo(750,screen.580);
	  top.window.focus();
	//}else if (document.layers||document.getElementById){
	//  if (top.window.outerHeight<screen.availHeight||top.window.outerWidth<screen.availWidth){
	//    top.window.outerHeight = screen.availHeight;
	//    top.window.outerWidth = screen.availWidth;
	//  }
	//}
//}
</script>

<frameset  cols="*" rows="300,*" frameborder="YES">
  <frame name="pictureFrame" scrolling="auto" src="/en/private/manuals/UntitledFrame-2" >
  <frame name="tableFrame" scrolling="auto" src="https://www.tld-parts.com/private/find_part.php?pn=<?php  echo $pn?>">
</frameset>
<noframes><body bgcolor="#FFFFFF" text="#000000">

</body></noframes>
</html>
