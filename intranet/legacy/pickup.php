<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="/tld-gse.css" rel="stylesheet" type="text/css">
</head>
<body>
<h1>Please be patient...</h1>
<img src="/shared/icons/carrier_ani.gif">
<?php
/*switch($m){
	case zipped:
		$url="manuals.pl?m=zipped&id=";
	break;
	case partsdiagrampdf:
		$url="";
	break;
	case partsbookpdf:
		$url="";
	break;
}
*/
?>
<meta http-equiv="refresh" content="5;URL=<?= $_GET['url'] ?>">
 <p>If you are downloading a manual, the manual has to be dynamically created.</p>
<p>Depending on the size of the file it may take minutes to prepare before
  the download starts.</p>
  <p>On some machines, there is a 20 sec lag between the windows flag in the browser stopping before the PDF manual shows up in the browser. Again, please be patient.</p>
<p>Only report to problems to webmaster if you have waited at least 60secs AFTER starting the download or receive an error message.</p>
</body>
</html>