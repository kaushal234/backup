{literal}
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_jumpMenu(targ,selObj,restore){ //v3.0
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
//-->
</script>
{/literal}
<form name="form1{$suffix}">
  <select name="menu1{$suffix}" onChange="MM_jumpMenu('self',this,0)">
		{if count($index)}
			Index
			{foreach item=item from=$index}
				<option value="#{$item|trim}{$suffix}">{$item}</option>
			{/foreach}
		{/if}
    </select>
</form>

