{literal}
<style type="text/css">
	td { font-size: 10pt; font-family: Arial, Helvetica, sans-serif}
	.smallwhite { color: #FFFFFF; background-color: #3264C8 }
</style>
{/literal}

<h3>{$title}</h3>

{if isset( $options.no_table) && $options.no_table==true}
  {foreach key=key item=item from=$fields}
	{if isset($options.doNotShowEmpty) && $options.doNotShowEmpty!="" && (null !== $data.$key && trim($data.$key)=="" || $data.$key=="0000-00-00")}
	{else}
	  {$item|renderHtmlOrNl2br|stripslashes}:&nbsp;&nbsp;&nbsp;&nbsp;{if !empty($options.links.$key)}<a href="{if $options.links.$key|is_array}{$options.links.$key.url}{else}{$options.links.$key}{$data.$key}{/if}">{$data.$key|renderHtmlOrNl2br|stripslashes}</a>{else}{$data.$key|renderHtmlOrNl2br|stripslashes}<br>{/if}
	{/if}
  {/foreach}
{else}
<table>
  {foreach key=key item=item from=$fields}
	{if !empty($options.doNotShowEmpty) && (null !== $data.$key && trim($data.$key)=="" || $data.$key=="0000-00-00")}
	  <!-- tr><td></td><td></td></tr-->
	{else}
	  <tr>
	  {if isset($options.plain) && $options.plain=="tld"}
		<td style="background-color=#2971a8" class="smallwhite">
	  {elseif !empty($options.plain)}
		  <td>
	  {else}
		<td style="background-color=#3264C8" class="smallwhite">
	  {/if}
	  {if $item=="---spacer---"}
	    &nbsp;</td><td>&nbsp;
	  {else}
	    {$item|renderHtmlOrNl2br|stripslashes}</td><td bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
		{if !empty($options.links[$key])}
		  <a href="{if $options.links.$key|is_array}{$options.links.$key.url}{else}{$options.links.$key}{$data.$key}{/if}">{$data[$key]|renderHtmlOrNl2br}</a>
		{elseif isset($data[$key])}
		  {$data[$key]|renderHtmlOrNl2br|stripslashes}
		{/if}
	  {/if}
	    </td>
	  </tr>
	{/if}
  {/foreach}
</table>
{/if}

