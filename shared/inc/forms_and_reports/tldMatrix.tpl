<h3>{if isset($title)}{$title}{/if}</h3>

{if !isset($data) || count($data)==0}
	<p>No results...</p>
{else}
	{assign var="stickyLeft" value=false}
	{assign var="stickyContainerSize" value="1200px"}
	{assign var="stickyLeftBackgroundColor" value="#CCFF00"}
	{assign var="stickyLeftColor" value="black"}
	{if isset($options.stickyLeft) && $options.stickyLeft == true}
		{assign var="stickyLeft" value=true}
	{/if}
	{if isset($options.stickyContainerSize)}
		{assign var="stickyContainerSize" value=$options.stickyContainerSize}
	{/if}
	{if isset($options.stickyLeftBackgroundColor)}
		{assign var="stickyLeftBackgroundColor" value=$options.stickyLeftBackgroundColor}
	{/if}
	{if isset($options.stickyLeftColor)}
		{assign var="stickyLeftColor" value=$options.stickyLeftColor}
	{/if}

	{if $stickyLeft === true}
	<div style="width: {$stickyContainerSize}; overflow-x: scroll; margin-bottom: 10px;">
	{/if}

	<table cellpadding="5" class="form-matrix">
	{*PRINT THE X AXIS TITLES*}
	<tr class="section-head">
		<th class="col-left" {if $stickyLeft === true}style="position: sticky; left: 0; background-color: {$stickyLeftBackgroundColor}; color: {$stickyLeftColor}; z-index: 1;"{/if}>&nbsp;</th>
		{foreach item=xItem from=$xItems name=head}
		<th class="col-{$smarty.foreach.head.iteration}"{if isset($options.style.xItems)} style="{$options.style.xItems.$xItem}"{/if}>
		  {$xItem}
		</th>
		{/foreach}
		{if isset($options.doNotShowYTotals) && $options.doNotShowYTotals == false}
		<th bgcolor="#CCFF00" class="col-right">Totals</th>
		{/if}
	</tr>

	{foreach item=yItem from=$yItems}
	<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}" class="section-body">
		{*PRINT THE Y AXIS TITLES*}
		<th class="col-left" style="{if isset($options.style.yItems)}{$options.style.yItems.$yItem}{/if} {if $stickyLeft === true}position: sticky; left: 0; background-color: {$stickyLeftBackgroundColor}; color: {$stickyLeftColor};{/if}">
		  {$yItem}
		</th>
		{*POPULATE THE DATA*}
		{foreach item=xItem from=$xItems name=body}
		<td align="right" class="col-{$smarty.foreach.body.iteration}">
			{if isset($url) AND $url<>"" && isset($data[$xItem][$yItem]) && null !== $xItemsLink[$xItem]  && isset($data[$xItem][$yItem]) && $data[$xItem][$yItem] !== ""}<a href="{$url}&x={$xItemsLink[$xItem]|urlencode}&y={$yItemsLink[$yItem]|urlencode}">{/if}
			{if isset($options.showZeros) && $options.showZeros == true}
				{if isset($data[$xItem][$yItem])}{$data[$xItem][$yItem]}{else}0{/if}
			{else}
				{if isset($data[$xItem][$yItem])}{$data[$xItem][$yItem]}{else}&nbsp;{/if}
			{/if}
			{if isset($url) && $url<>"" && isset($data[$xItem][$yItem]) && $data[$xItem][$yItem]<>""}</a>{/if}
		</td>
		{/foreach}

		{if !isset($options.doNotShowYTotals) || $options.doNotShowYTotals == false}
		{*PRINT THE Y AXIS TOTALS*}
		<td bgcolor="#CCFF00" align="right" class="col-right">
			{if isset($url) AND $url<>"" AND null !== $yItemsLink[$yItem] AND (!isset($options.doNotLinkYTotals) || $options.doNotLinkYTotals <> true)}<a href="{$url}&x=ALL&y={$yItemsLink[$yItem]|urlencode}">{/if}
			{$totals.$yItem.Total|default:"&nbsp;"}
			{if isset($url) AND $url<>"" AND (!isset($options.doNotLinkYTotals) || $options.doNotLinkYTotals <> true)}</a>{/if}
		</td>
		{/if}
	</tr>
	{/foreach}

	{if !isset($options.doNotShowXTotals) || $options.doNotShowXTotals <> true}
	{* PRINT THE X AXIS TOTALS*}
	<tr bgcolor="#CCFF00" class="section-foot">
		<th class="col-left" {if $stickyLeft === true}style="position: sticky; left: 0; background-color: {$stickyLeftBackgroundColor}; color: {$stickyLeftColor};"{/if}>Totals</th>
		{foreach item=xItem from=$xItems name=foot}
		<td align="right" class="col-{$smarty.foreach.foot.iteration}">
			{if isset($url) AND $url<>"" AND null !== $xItemsLink[$xItem] AND (!isset($options.doNotLinkXTotals) || $options.doNotLinkXTotals <> true)}<a href="{$url}&x={$xItemsLink[$xItem]|urlencode}&y=ALL">{/if}
			{if isset($totals.Total.$xItem)}{$totals.Total.$xItem}{/if}
			{if isset($url) AND $url<>"" AND (!isset($options.doNotLinkXTotals) || $options.doNotLinkXTotals <> true)}</a>{/if}
		</td>
		{/foreach}
		{if !isset($options.doNotShowYTotals) || $options.doNotShowYTotals <> true}
		<td align="right" class="col-right">
			{if isset($url) AND $url<>"" AND ((!isset($options.doNotLinkXTotals) || $options.doNotLinkXTotals<>true) OR (!isset($options.doNotLinkYTotals) || $options.doNotLinkYTotals<>true))}<a href="{$url}&x=ALL&y=ALL">{/if}
			{if isset($totals.Total.Total)}{$totals.Total.Total}{/if}
			{if isset($url) AND $url<>"" AND ((!isset($options.doNotLinkXTotals) || $options.doNotLinkXTotals<>true) OR (!isset($options.doNotLinkYTotals) || $options.doNotLinkYTotals<>true))}</a>{/if}
		</td>
		{/if}
	</tr>
	{/if}
	</table>


	{if $stickyLeft === true}
	</div>
	{/if}
{/if}
