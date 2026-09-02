<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<form name="listDrawings" method="get" action="<?php echo $_SERVER['PHP_SELF']?>">
<input type="text" name="target">
<input type="submit" name="Find">
</form>

<?php
include("common.inc.php");
include("vault.inc.php");
if($GLOBALS["target"]){
	$myVaultController = new tldVaultController();
	$myFiles = $myVaultController->getSearchListUNC(400,$GLOBALS["target"]);
	foreach($myFiles as $myFile){
		echo "<a href=\"$myFile\">$myFile</a><br>";
	}
}
//$dir = "\\\\mercury\\released";
/*$target="103004";
$folder=substr($target,0,3);
$subFolder=substr($target,0,4);

$dir="/mnt/mercury.vault/$folder/$subFolder";
echo $dir;
// Open a known directory, and proceed to read its contents
if (is_dir($dir)) {
   if ($dh = opendir($dir)) {
       while (($file = readdir($dh)) !== false) {
           if(substr($file,0,strlen($target)) == $target)
			   echo "<a href=\"file://mercury/vault/$folder/$subFolder/$file\">$file</a><br>";
       }
       closedir($dh);
   }
}
*/
?>
</body>
</html>
