<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>{$title}</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
	<p><img src="tld-icon.gif" width="72" height="46"></p>
	<h1>Main Menu</h1>
{if count($sections)}
	<h2>Printable Version:</h2>
	<ul>
	{foreach key=key item=item from=$sections}
		<li><a href="{$item.filepath}">{$item.endescription}/{$item.frdescription}</a></li>
	{/foreach}
	</ul>
	<hr>
{/if}
{if count($schematics) and $schematics[0]<>""}
	<h2>Printable Schematics:</h2>
	<ul>
	{foreach item=item from=$schematics}
		<li><a href="{$item.file}">{$item.label}</a></li>
	{/foreach}
	</ul>
	<hr>
{/if}
	<h2>Interactive Version:</h2>
	<ul>
		<li><a href="MANUAL%20INDEX.html">Interactive Version</a></li>
	</ul>
</body>
</html>
