{* Set column total flag *}
{if array_key_exists('sumTotalsArray', $options) && count($options.sumTotalsArray)>0}
{assign var='showTotals' value=true}
{php}
	$totalsArray=array();
	foreach($this->_tpl_vars['options']['sumTotalsArray'] AS $value) $totalsArray[$value]=0;
{/php}
{else}{assign var='showTotals' value=false}{/if}

{literal}
<style type="text/css">
.tip{
	font_size: 12px;
	margin: 0;
	padding: 0;
	color: gray;
}
.columnar thead tr th {
	background: #2971a8;
	color: white;
	cursor: pointer;
}
.columnar tfoot tr td.value {
	background: #2971a8;
	color: white;
	font-weight: bold;
	border-top: 2px solid #333;
	padding-top: 8px;
	font-size: larger;
}
.columnar tr:nth-child(odd){
	background: #d0d0d0 !important;
}
.columnar tr:nth-child(even){
	background: #eeeeee !important;
}
</style>
{/literal}

{if array_key_exists('stickyHeader', $options) && $options.stickyHeader}
	{literal}
	<style type="text/css">
		table {
			text-align: left;
			position: relative;
		}
		th {
			position: sticky;
			top: 0;
		}
	</style>
	{/literal}
{/if}

{if array_key_exists('title', $options) && $options.title<>""}
	<h3>{$options.title}</h3>
{/if}
{if count($data)<1}
	<p>No records...</p>
{else}
{if array_key_exists('is_pagination', $options) && $options.is_pagination}
	{php}$this->assign('_url', $_SERVER['SCRIPT_NAME'].'?'.http_build_query($_GET + $_POST));{/php}
	{$pagination->getNavLinks($_url)}
{/if}
{if !array_key_exists('sortable', $options) || $options.sortable eq ""}
<p class="tip"><em>Tips: Click headers to sort.</em></p>
{/if}
<div class="columnar">
	<table {if !array_key_exists('sortable', $options) || $options.sortable eq ""}class="sortable"{/if} {if array_key_exists('width', $options) && $options.width<>""}width="{$options.width}"{/if} {if array_key_exists('attribs', $options) && $options.attribs<>""}{$options.attribs}{/if} cellpadding="3">
	{*PRINT THE X AXIS TITLES*}
	<thead>
		<tr>
		{if array_key_exists('showItemNumbers', $options) && $options.showItemNumbers}
		<th>#</th>
		{/if}
		{if array_key_exists('xItems', $options) && is_array($options.xItems)}
			{foreach name=titles key=key item=xItem from=$options.xItems}
			<th {if !array_key_exists('sortable', $options) || $options.sortable eq ""}title="Sort by {$xItem}"{/if}>{$xItem|default:"&nbsp;"}</th>
			{/foreach}
		{else}
			{foreach name=titles key=key item=xItem from=$data[0]}
                <th {if $options.sortable eq ""}title="Sort by {$key}"{/if}>
                    {$key|default:"&nbsp;"}
                </th>
			{/foreach}
		{/if}
		{if array_key_exists('functions', $options) && is_array($options.functions)}
			{foreach name=funcs key=key item=params from=$options.functions}
                <th>
                    {$key|default:"&nbsp;"}
                </th>
			{/foreach}
		{/if}
		</tr>
	</thead>
	<tbody>
		{foreach name=data item=line from=$data}
		<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
			{if array_key_exists('showItemNumbers', $options) && $options.showItemNumbers}
				{if $options.showItemNumbers === 'reversed'}
					<td>{$smarty.foreach.data.total-$smarty.foreach.data.index}</td>
				{else}
					<td>{$smarty.foreach.data.iteration}</td>
				{/if}
			{/if}
			{if array_key_exists('xItems', $options) && is_array($options.xItems)}
				{foreach key=key item=xItem from=$options.xItems}
				<td>
				{if array_key_exists('links', $options) && is_array($options.links) && array_key_exists('onKey', $options.links) && array_key_exists('urlKey', $options.links) && $key === $options.links.onKey}
					{assign var = "urlKey" value = $options.links.urlKey}
					<a href="{$line.$urlKey}"
						{if array_key_exists('target', $options.links)} target="{$options.links.target}"{/if}
					>
						{$line.ncrId}
					</a>
				{elseif array_key_exists('links', $options) && is_array($options.links) && array_key_exists($key, $options.links) && is_array($options.links.$key)}
					<a href="{$options.links.$key.url}
					{if !empty($options.links.$key.params)}
						{foreach key=var item=val from=$options.links.$key.params}
							&{$var}={$line.$val}
						{/foreach}
					{/if}
					{if !empty($options.links.$key.vars)}
						{foreach key=var item=val from=$options.links.$key.vars}
							{$line.$val}
						{/foreach}
					{/if}
					" {if is_array($options.links.$key) && isset($options.links.$key.target) && $options.links.$key.target<>''} target="{$options.links.$key.target}"{/if}
					{if !empty($options.links.$key.confirmPopup)}
						onClick="javascript:return confirm('{$options.links.$key.confirmPopup}');"
					{/if}
					>{$line.$key|default:"&nbsp;"|renderHtmlOrNl2br}</a>
				{elseif array_key_exists('links', $options) && is_array($options.links) && array_key_exists($key, $options.links) && $options.links.$key<>"" and !empty($line.$key)}
					<a href="{$options.links.$key}{$line.$key}"{if is_array($options.links.$key) && isset($options.links.$key.target)} target="{$options.links.$key.target}"{/if}>
					{$line.$key|default:"&nbsp;"|renderHtmlOrNl2br}</a>
				{elseif array_key_exists('cellModifier', $options) && is_array($options.cellModifier) && array_key_exists($key, $options.cellModifier) && $options.cellModifier.$key<>""}
				    {if $options.cellModifier.$key.type eq "simple_link"}
			        <a href="{$options.cellModifier.$key.prefix}{$line.$key}">{$line.$key}</a>
				    {/if}
				{elseif is_array($line) && array_key_exists($key, $line) && empty($line.$key)}
					{if array_key_exists('showzero', $options) && !empty($options.showzero)}
						{$line.$key}
					{else}
      					&nbsp;
      				{/if}
				{else}
					{$line.$key|default:"&nbsp;"|renderHtmlOrNl2br}
				{/if}
				{if $showTotals && array_key_exists('sumTotalsArray', $options) && in_array($key,$options.sumTotalsArray)}
					{php}
						$totalsArray[$this->_tpl_vars['key']] += (float)$this->_tpl_vars['line'][$this->_tpl_vars['key']];
					{/php}
				{/if}
				</td>
				{/foreach}
			{else}
				{foreach name=columns key=key item=xItem from=$line}
				<td>
				{if array_key_exists('links', $options) && array_key_exists($key, $options.links) && is_array($options.links.$key)}
					<a href="{$options.links.$key.url}
					{if !empty($options.links.$key.params)}
						{foreach key=var item=val from=$options.links.$key.params}
							&{$var}={$line.$val}
						{/foreach}
					{/if}
					{if !empty($options.links.$key.vars)}
						{foreach key=var item=val from=$options.links.$key.vars}
							{$line.$val}
						{/foreach}
					{/if}
					" {if is_array($options.links.$key) && isset($options.links.$key.target) && $options.links.$key.target<>''} target="{$options.links.$key.target}"{/if}
					{if !empty($options.links.$key.confirmPopup) }
						onClick="javascript:return confirm('{$options.links.$key.confirmPopup}');"
					{/if}
					> {$line.$key|default:"&nbsp;"|renderHtmlOrNl2br}</a>
				{elseif array_key_exists($links, $options) && array_key_exists($key, $options.$links) && $options.links.$key<>"" and !empty($line.$key)}
					<a href="{$options.links.$key.url}{$line.$key}"{if is_array($options.links.$key) &&  isset($options.links.$key.target) && $options.links.$key.target<>''} target="{$options.links.$key.target}"{/if}>
					{$options.xItem|default:"&nbsp;"|renderHtmlOrNl2br}</a>
				{elseif array_key_exists($key, $line) && empty($line.$key)}
					{if !empty($options.showzero)}
						{$line.$key}
					{else}
      					&nbsp;
      				{/if}
      			{else}
					{$line.$key|default:"&nbsp;"|renderHtmlOrNl2br}
				{/if}
				{if $showTotals && array_key_exists('sumTotalsArray', $options) && in_array($key,$options.sumTotalsArray)}
					{php}
						$totalsArray[$this->_tpl_vars['key']] += (float)$this->_tpl_vars['line'][$this->_tpl_vars['key']];
					{/php}
				{/if}
				</td>
				{/foreach}
			{/if}
            {if array_key_exists('functions', $options) && is_array($options.functions)}
                {foreach name=funcs key=key item=params from=$options.functions}
                    <td align="center">
                      {php}$this->_tpl_vars['bypass'] = array_key_exists('bypassDisplay', $this->_tpl_vars['options']['functions'][$this->_tpl_vars['key']]) && $this->_tpl_vars['options']['functions'][$this->_tpl_vars['key']]['bypassDisplay']($this->_tpl_vars['params'], $this->_tpl_vars['line'] ){/php}
					  {if !$bypass}
                        <a href="{$params.url}{if is_array($options.functions.$key.param)}{foreach key=var item=val from=$options.functions.$key.param}&{$var}={$line.$val}{/foreach}{else}{$line[$params.param]}{/if}"
                          {if isset($options.functions.$key.target) && !empty($options.functions.$key.target)} target="{$options.functions.$key.target}"{/if}
                          {if !empty($options.functions.$key.confirmPopup)}
                              onClick="javascript:return confirm('{$options.functions.$key.confirmPopup}');"
                          {/if}>
                          {if !empty($options.functions.$key.img)}<img src="{$options.functions.$key.img}" alt="{$key}" />{else}{$key}{/if}
					    </a>
                      {/if}
                    </td>
                {/foreach}
            {/if}
		</tr>
		{/foreach}
	</tbody>
	{if $showTotals}
	<tfoot>
		<tr>
		{if array_key_exists('showItemNumbers', $options) && $options.showItemNumbers}
		<td>&nbsp;</td>
		{/if}
		{if array_key_exists('xItems', $options) && is_array($options.xItems)}
			{foreach name=totals key=key item=xItem from=$options.xItems}
				{if array_key_exists('sumTotalsArray', $options) && in_array($key,$options.sumTotalsArray) && array_key_exists('sumTotalsUrl', $options) && $options.sumTotalsUrl == true}
					<td>
						<a target="_blank" href="{$options.links.$key.url}">{php}echo (float)$totalsArray[$this->_tpl_vars['key']];{/php}</a>
					</td>
				{elseif array_key_exists('sumTotalsArray', $options) && in_array($key,$options.sumTotalsArray)}
					<td class="value" title=" Total ">{php}echo (float)$totalsArray[$this->_tpl_vars['key']];{/php}</td>
				{else}
					<td>&nbsp;</td>
				{/if}
			{/foreach}
		{else}
			{foreach name=totals key=key item=xItem from=$data[0]}
				{if array_key_exists('sumTotalsArray', $options) && in_array($key,$options.sumTotalsArray) && array_key_exists('sumTotalsUrl', $options) && $options.sumTotalsUrl == true}
					<td>
						<a target="_blank" href="{$options.links.$key.url}">{php}echo (float)$totalsArray[$this->_tpl_vars['key']];{/php}</a>
					</td>
				{elseif array_key_exists('sumTotalsArray', $options) && in_array($key,$options.sumTotalsArray)}
					<td class="value" title=" Total ">{php}echo (float)$totalsArray[$this->_tpl_vars['key']];{/php}</td>
				{else}
					<td>&nbsp;</td>
				{/if}
			{/foreach}
		{/if}
		{if  array_key_exists('functions', $options) && is_array($options.functions)}
			{foreach name=funcs key=key item=params from=$options.functions}
                <td>&nbsp;</td>
			{/foreach}
		{/if}
		</tr>
	</tfoot>
	{/if}
	</table>
</div>
{if array_key_exists('showNumberOfRows', $options) && $options.showNumberOfRows}
<p style="margin:0;">Total of <strong>{$smarty.foreach.data.total}</strong> rows</p>
{/if}
{/if}
