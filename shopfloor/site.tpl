<html>
  <head>
	<title>{$title}</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link href="/tld-gse.css" rel="stylesheet" type="text/css">
  </head>
  <body bgcolor="#FFFFFF" text="#000000">
    <div align="center">
      <table width="900" bgcolor="#FFFFFF" cellpadding="2">
        <tr width="100%">
          <td>
            <table>
              <tr>
                <td width="10%"><img src="/shared/tld_logos/tld-1inch.jpg" width="100"></td>
                <td width="90%">
                  <h1 style="text-align:center;">
                    <span style="font-size:46px; font-weight:bold; font-family:Arial, Helvetica, sans-serif;">{$location} INTRANET</span>
                  </h1>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr> 
          <td valign="bottom">
            <p class="menu">{$menu}</p>
            <h2>{$title}</h2>
          </td>
        </tr>
        <tr> 
          <td>
    		{if $error<>""}<p class="alert">{$error}</p>{/if}
            <p>{$body}</p>
		  </td>
        </tr>
      </table>
    </div>
  </body>
</html>
