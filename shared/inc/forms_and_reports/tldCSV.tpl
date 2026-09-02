{if count($data)>0}
{*PRINT THE X AXIS TITLES*}
{if is_array($xItems)}
{foreach name=titles key=key item=xItem from=$xItems}'{$xItem|default:"&nbsp;"|escape}',{/foreach}
{else}
{foreach name=titles key=key item=xItem from=$data[0]}'{$key|default:"&nbsp;"|escape}',{/foreach}
{/if}

{foreach name=data item=line from=$data}
{if is_array($xItems)}
{foreach key=key item=xItem from=$xItems}'{$line.$key|default:"&nbsp;"|escape}',{/foreach}
{else}
{foreach name=columns key=key item=xItem from=$line}'{$xItem|default:"&nbsp;"|escape}',{/foreach}	
{/if}

{/foreach}
{/if}