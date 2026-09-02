{if $rows eq ""}
	<h1>No results found...</h1>
{else}
	{foreach name=outer key=outKey item=outValue from=$rows}
		<h4>{$outKey}</h4>
		{foreach name=inner key=inKey item=inValue from=$outValue}
			&nbsp;&nbsp;&nbsp;<b>SB#{$inValue.factory_sb_number}&nbsp;-&nbsp;
			{if $inValue.urgency=="COMPULSORY"}
				<span class="alert">COMPULSORY</span>
			{else}
				{$inValue.urgency}
			{/if}
			&nbsp;-&nbsp;{$inValue.title}</b><br>
			{$inValue.description|stripslashes}<br>
		{/foreach}
	{/foreach}
{/if}