<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<p><img src="tld-icon.gif" width="72" height="46">
</p>
<p>Printable Version</p>
{if count($sections)}
	<ul>
	{foreach key=key item=item from=$sections}
		<li><a href="{$item.diagram_filename}">{$item.endescription}/{$item.frdescription}</a></li>
	{/foreach}
	</ul>
{/if}
<p><a href="MANUAL%20INDEX.html">Interactive Version</a></p>
</body>
</html>
