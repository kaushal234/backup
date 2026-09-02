<!DOCTYPE html>
<html>
	<head>
		<title>{$title}</title>
		{if isset($lang) and $lang=="utf8"}
		<link rel="stylesheet" type="text/css" href="/shared/css/tld-gsezh.css">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		{elseif isset($lang) and $lang=="zh"}
		<link rel="stylesheet" type="text/css" href="/shared/css/tld-gsezh.css">
		<meta http-equiv="Content-Type" content="text/html; charset=gb2312">
		{else}
		<link rel="stylesheet" type="text/css" href="/shared/css/tld-gse.css">
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		{/if}
		<!-- JQUERY tools -->
        <script src="/shared/javascript/jquery/jquery-1.9.1.min.js"></script>
        <script src="/shared/javascript/jquery/jquery-ui-1.10.1.custom.min.js"></script>
        <link rel="stylesheet" href="/shared/javascript/jquery/css/smoothness/jquery-ui-1.10.1.custom.min.css" />
        <!-- TinyMCE -->
        <script src="/shared/javascript/tinymce/tinymce.min.js"></script>
        <!-- TLD js & css -->
        <script src="/shared/javascript/tld/tld.form.js"></script>
    	<script type="text/javascript" src="/shared/javascript/tld/tld.table.sorttable.js"></script>
		<script type="text/javascript" src="/shared/javascript/tld/qfamsHandler-min.js"></script>
		<link href="/en/private/print.css" rel="stylesheet" type="text/css" media="print">
		<link rel="stylesheet" type="text/css" href="/shared/css/matrix.css">
		<!-- Highcharts & TLD graph -->
		<script src="/shared/javascript/highcharts/highcharts.js"></script>
		<script src="/shared/javascript/highcharts/highcharts-more.js"></script>
		<script src="/shared/javascript/tld/tld.graph.js"></script>
		<link rel="stylesheet" type="text/css" href="/shared/javascript/select2/css/select2.min.css">
		<script src="/shared/javascript/select2/select2.min.js" charset="UTF-8"></script>
		<script src="/shared/javascript/textarea/textarea.js" charset="UTF-8"></script>
		{if isset($js_includes)}
			{section name=i loop=$js_includes}
				<script type="text/javascript" src="{$js_includes[i]}"></script>
			{/section}
		{/if}
		{if isset($html_head)}
			{$html_head}
		{/if}

	</head>
  <body style="z-index:0;">

  <tld:include src="layout/top_message.html.twig" />

  {include file='intranet.header.tpl'}

  {if isset($html_body_before)}
	  {$html_body_before}
  {/if}

  <div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000; overflow:auto;"></div>

	<table width="{if isset($width)}{$width}{else}1024{/if}" border="0" align="center">
	  {if !isset($do_not_show_body_header) or $do_not_show_body_header neq 1}
	  <tr>
		<td>
		  <div id="title">
		    <p><strong><font size="4" face="Arial, Helvetica, sans-serif">{$title}</font></strong></p>
		  </div>
		  <div id="menu">
		    <p>{if isset($menu)}{$menu}{/if}</p>
		  </div>
		</td>
	  </tr>
	  {/if}
	  <tr>
		<td colspan="2">
		  <div id="page">
		    {if isset($error) and $error<>""}<p class="alert">{$error}</p>{/if}
		    {if isset($success) and $success<>""}<p class="success">{$success}</p>{/if}
			  {if isset($body)}{$body}{/if}
	      </div>
	      <div id="footer">
			<p class="xxsmalltext">{if isset($footer)}{$footer}{/if}</p>
		  </div>
		</td>
	  </tr>
	</table>

	  {if isset($html_body_after)}
		  {$html_body_after}
	  {/if}

	<tld:include src="partial/_ticket.html.twig" />
    <!-- Parse Time: {php}echo microtime(true)-(float)PAGE_PARSE_START;{/php} -->

  </body>
</html>
