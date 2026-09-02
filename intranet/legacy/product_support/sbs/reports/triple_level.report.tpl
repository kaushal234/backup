{if $rows eq ""}
	<h1>No results found...</h1>
{else}
	{foreach name=first key=firstKey item=firstValue from=$rows}
		<a href="#{$firstKey}">{$firstKey}</a><br>
	{/foreach}
	{foreach name=first key=firstKey item=firstValue from=$rows}
		<a name="{$firstKey}"></a><h3>{$firstKey}</h3>
		{foreach name=second key=secondKey item=secondValue from=$firstValue}
			<h4>&nbsp;&nbsp;&nbsp;{$secondKey}</h4>
			{foreach name=third key=thirdKey item=thirdValue from=$secondValue}
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>SB#{$thirdValue.factory_sb_number}&nbsp;-&nbsp;
				{if $thirdValue.urgency=="SB:COMPULSORY"}
					<span class="alert">COMPULSORY</span>
				{else}
					{$thirdValue.urgency}
				{/if}
				&nbsp;-&nbsp;{$thirdValue.title}</b><br>
				{$thirdValue.description|stripslashes}<br>
			{/foreach}
		{/foreach}
	{/foreach}
{/if}