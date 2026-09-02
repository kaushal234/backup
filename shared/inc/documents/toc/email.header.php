<?php

/**
 * Header email used by tldTOC::constructEmailHeader()
 */

return <<<EOF
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style type="text/css">
body, td, th{
    font-family: Helvetica, Arial, sans-serif;
    font-size: 13px;
    line-height: 21px;
    color: #666;
}
body{
    background-color: $backgroundFrame;
    padding:0;
}

small { line-height:14px; }
a, a:visited {text-decoration:none;}
a:hover {color:#FF9933;}
.vcard { font-size: 10px; }
.terms { font-size: 10px; }
.smallwhite {
    color: white;
}
.item_info_title {
    background: #2971a8;
    color: white;
    font-size:15px;
    text-align: center;
}
.item_reps_title {
    font-size:17px;
    text-align: center;
}
.item_info_table_label {
    background: #e0e0e0;
    padding: 3px;
}
.item_info_table_value {
    background: #f4f4f4;
    padding: 3px;
}
.item_dear {
    font-weight:normal;
    font-size:21px;
    color:#606060;
    margin: 0,0,4px,0;
    padding: 0px;
}
.survey_msg {
    color: red;
}
    </style>
  </head>
  <body>
    <table width="100%" align="center" cellpadding="0" cellspacing="0">
      <tr>
        <td bgcolor="#f5f5f5">
          <br>
          <table width="670" cellspacing="0" cellpadding="0" align="center">
            <tr>
              <td width="670" bgcolor="#FFFFFF"  style="border-bottom:1px grey dashed;">
                <table width="650" cellspacing="0" cellpadding="20">
                  <tr>
                    <td bgcolor="#FFFFFF" style="background-color: #FFFFFF;">
                      <img src="https://www.tld-gse.com/shared/tld_logos/{$sso['logo']}" alt="{$sso['name']}" width="{$sso['width']}" height="{$sso['height']}" >
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td bgcolor="#f5f5f5">
          <table width="670" cellspacing="0" cellpadding="20" align="center">
            <tr>
              <td align="left" valign="top" bgcolor="#FFFFFF">
                <!--#NOT_INFO#-->
                
EOF;

?>
