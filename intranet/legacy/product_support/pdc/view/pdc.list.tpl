<h3>{$title|default:"PDC"}</h3>

{if count($list)>0}
<table class="sortable" width="100%">
    <thead>
      <tr>
          <th>ID#</th>
          <th>Ready to close</th>
          <th>Status</th>
          <th>iFactor</th>
          <th>Focus Weight</th>
          <th>Months Open</th>
          <th>Date</th>
          <th>Date Closed</th>
          <th>Factory</th>
          <th>Type</th>
          <th>Model</th>
          <th>Description</th>
          <th>Assignee</th>
          <th>TOCs Open</th>
      </tr>
    </thead>
    <tbody>
    {foreach name=list item=pdc from=$list}
      {if $pdc.fweight > 10 && $pdc.fweight <100}
        {assign var="lineColour" value="#FFCC00"}
      {elseif $pdc.fweight >= 100 && $pdc.fweight <1000}
        {assign var="lineColour" value=#FF6600"}
      {elseif $pdc.fweight >= 1000}
        {assign var="lineColour" value="#FF0000"}
      {else}
        {assign var="lineColour" value=""}
      {/if}
      <tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
        <td><a href="{$next_step}{$pdc.id}">{$pdc.id}</a></td>
        <td>{if $pdc.is_ready_to_close}YES{else}NO{/if}</td>
        <td>{$pdc.status}</td>
        <td>{$pdc.ifactor}</td>
        <td{if $lineColour<>""} bgcolor="{$lineColour}"{/if}><b>{$pdc.fweight}</b></td>
        <td>{$pdc.monthsOpen}</td>
        <td style="white-space:nowrap;"> {$pdc.date}</td>
        <td style="white-space:nowrap;"> {$pdc.date_closed}</td>
        <td style="white-space:nowrap;"> {$pdc.factory_fullname}</td>
        <td>{$pdc.product_type}</td>
        <td>{$pdc.model}</td>
        <td>{$pdc.short_desc}</td>
        <td>{$pdc.assignee_fullname}</td>
        <td>{$pdc.toc_count}</td>
      </tr>
    {/foreach}
    </tbody>
</table>

{if $showTotal eq 'Y'}<h3>Total PDC: {$smarty.foreach.list.total}</h3>{/if}
{else}
<p>No records...</p>
{/if}
