<td>{$lineItem.item|default:"&nbsp;"}</td>
<td>{$lineItem.id|default:"&nbsp;"}</td>
<td>{$lineItem.factory_num|default:"&nbsp;"}</td>
<td>{$lineItem.rev|default:"&nbsp;"}</td>
<td>
  {$lineItem.endescription|default:"&nbsp;"|nl2br}
  {if $lineItem.frdescription <> ""}
	<br><b>Alt Lang:</b>&nbsp;{$lineItem.frdescription}
  {/if}
  {if $category=='Chapter 0'}
    <br><span style="color:red;font-size:0.8em;text-decoration:italic;">WARNING: If the equipment is combined with other TLD equipement, customer must refer to both manual</span>
  {/if}
</td>
