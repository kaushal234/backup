{* Variable list

fields, array of fieldnames or array of fieldname/title
list, array or array lines
NEXT_STEP, url of page to send key value
options:
	title
	no_title
*}

{if isset($options.title) || (isset($options.no_title) && $options.no_title!=true)}
	<h3>{$options.title|default:"Please make selection below"}</h3>
{/if}

{if  isset($options.show_no_data_error) && $options.show_no_data_error==true && count($list)==0}
	<p class="alert">No results...</p>
{/if}

<ul>
{if is_array($fields)}
	{foreach name=lines item=item from=$list}
		<li>
		<a href="{$NEXT_STEP}
		{if is_array($fields.key)}
			{foreach key=varname item=varvalue from=$fields.key}
				&{$varname|escape:"url"}={$item.$varvalue|escape:"url"}
			{/foreach}
		{else}
			{$item[$fields.key]|escape:"url"}
		{/if}
		">
		{if is_array($fields.value)}
			{foreach item=value from=$fields.value}
				{$item.$value}&nbsp;
			{/foreach}
		{else}
			{$item[$fields.value]}
		{/if}
		</a></li>
	{/foreach}
{else}
	{foreach name=lines key=key item=item from=$list}
		<li>
			<a href="{$NEXT_STEP}{$key|escape:"url"}">{$item}</a>
		</li>
	{/foreach}
{/if}
</ul>