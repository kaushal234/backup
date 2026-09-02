<?php
include("common.inc.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Popup Admin</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="/tld-gse.css" rel="stylesheet" type="text/css">
</head>

<body>
<h2>Popup Notice Admin</h2>
<?php

$popupFilename = "$INTRA_PATH/popup_notice.htm";
$popupDataFilename = "$INTRA_PATH/popup_notice.dat";
$popupTemplateFilename = "$INTRA_PATH/popup_notice.tpl";

switch($m){
	case Enable:
		if(empty($message))break;
		$message = nl2br(stripslashes($message));

		if(file_exists($popupFilename)){
			rename($popupFilename,"$INTRA_PATH/popup_notice.bak");
		}
		//Open datafile
		$fp = fopen($popupDataFilename,"w");
		echo "Writing data file<br>";
		fwrite($fp,$message);
		fclose($fp);
		//Open template
		echo "Reading template file<br>";
		$template = file($popupTemplateFilename);
		//Merge and save html
		$fp = fopen($popupFilename,"w");
		echo "Writing html file<br>";
		foreach($template as $line){
			$line = str_replace("{_MESSAGE_BODY_}",$message,$line);
			fwrite($fp,$line);
		}
		fclose($fp);

	break;
	case Disable:
		if(file_exists($popupFilename)){
			rename($popupFilename,"$INTRA_PATH/popup_notice.bak");
		}
	break;
}
if(file_exists($popupFilename)){
	?><h2>Popup is ENABLED</h2><?php
}else{
	?><h2>Popup is DISABLED</h2><?php

}
?>
<form name="form1" method="post" action="<?php echo $PHP_SELF?>">
  <p>
    <input name="m" type="submit" id="Enable" value="Enable">
    <input name="m" type="submit" id="Disable" value="Disable">
  </p>
  <p>Message </p>
  <p>
    <textarea name="message" cols="80" rows="15"><?php
	if(file_exists($popupDataFilename)){
	echo str_replace("<br />","",implode("",file($popupDataFilename)));
	}?></textarea>
  </p>
</form>
</body>
</html>
