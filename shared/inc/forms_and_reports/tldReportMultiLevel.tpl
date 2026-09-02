{foreach name=ncr_list key=key item=row from=$rows}
	<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
	<td width="{$level*20}">
	{if $options.passField<>""}
		<a href="{$options.url}{$row[$options.passField]}">
		<img src="/shared/bluesphere/16x16/actions/viewmag.png" alt="Select {$row[$options.passField]}"></a>
	{/if}
	</td>
	{foreach name=cols key=field item=label from=$columns}
		<td>
		{if isset($options.links.$field) && is_array($options.links.$field)}
				<a href="{$options.links.$field.url}
				{foreach key=var item=val from=$options.links.$field.params}
					&{$var}={$row.$val}
				{/foreach}
				"
				{if !empty($options.links.$field.linkOptions)}
					 {$options.links.$field.linkOptions}
				{/if}>
				{$row.$field}</a>
		{elseif !empty($options.links.$field) && !empty($row.$field)}
			<a href="{$options.links.$field}{$row.$field}">{$row.$field}</a>
		{else}
			{$row.$field|default:"&nbsp;"}
		{/if}
		</td>
	{/foreach}
        {if array_key_exists('functions', $options) && is_array($options.functions)}
            {foreach name=funcs key=key item=params from=$options.functions}
                {strip}<td style="text-align: center">
						<a href="{$params.url}{if is_array($options.functions.$key.param)}
                        	{foreach key=var item=val from=$options.functions.$key.param}&{$var}={$row.$val}{/foreach}
                        	{else}{$row[$params.param]}
                        	{/if}"
                            	{if $options.functions.$key.target<>''} target="{$options.functions.$key.target}"{/if}
                                {if !empty($options.functions.$key.confirmPopup)}
							onClick="javascript:return confirm('{$options.functions.$key.confirmPopup}');"
                                {/if}>
                            {if !empty($options.functions.$key.img)}<img src="{$options.functions.$key.img}" alt="{$key}" />{else}{$key}{/if}
						</a>
				</td>{/strip}
            {/foreach}
        {/if}
	</tr>
{/foreach}
{if !empty($options.showNumberOfRows) }
	<tr><td colspan="{$smarty.foreach.cols.iteration+1}">Number of rows: {$smarty.foreach.ncr_list.iteration}</td></tr>
{/if}
