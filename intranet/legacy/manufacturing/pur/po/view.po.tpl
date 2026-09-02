<table width="100%">
  <tr>
    <td width="50%">{$header}</td>
    <td width="50%">{$delivery_address}</td>
  </tr>
</table>

<h3>Lines</h3>

<table width="1000" class="tld_table">
  <tr>
    <th>Item#</th>
    <th>Part#</th>
    <th>BOM</th>
    <th>Inv Part<br/>Forecast</th>
    <th>Rev</th>
    <th>Order<br>Qty</th>
    <th>Del<br>Qty</th>
    <th>Back<br>Qty</th>
    <th>UM</th>
    <th>Usage (Past 12 Months)</th>
    <th>Orig<br>Del Date</th>
    <th>Resc<br>Date</th>
    <th>Resc<br>Message</th>
    <th>Confirm<br>Date</th>
    <th>Current<br>Date</th>
    <th>Ship<br>Date</th>
    <th>Days<br>Left</th>
    <th>Order Price</th>
    <th>Current Purchased Price</th>
    <th>STD item Price</th>
    <th>Price<br>Change</th>
    <th>Last item<br>Price</th>
    <th>DWG<br>Change</th>
    <th>FAI</th>
    <th>Supplier<br>Replied</th>
    <th>Rebates<br>Discount</th>
    <th>Line<br>Amount</th>
  </tr>
  <tr> 
    <th colspan="{if $mode=="shipped"}24{else}23{/if}">
    Description &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Vendor PN</th>
  </tr>
  {foreach name=lines item=line from=$detail}
  <tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
    <td>{$line.t_pono}</td>
    <td>
        <a href="/en/private/manufacturing/whse/dev.php?m[0]=inv&m[1]=byPNPlanned&erp={$erp}&id={$line.t_item}&btnSubmit=Submit">
        {$line.t_item}</a>
    </td>
        <td>
        <a href="/en/private/manufacturing/eng/dev.php?m[0]=bom&m[1]=view&erp={$erp}&pn={$line.t_item}&date={$line.odat}">
        <img src="/shared/bluesphere/16x16/actions/viewmag.png"></a>
    </td>
    <td>
        <a href="/en/private/parts/parts.php?m[0]=reports&m[1]=listing&m[2]=InventoryForecastTool2&pn_from={$line.t_item}&pn_to={$line.t_item}&erp={$erp}&no_mvt=Y&tran_detail=Y&incl_soft=Y&validate=TRUE">
        <img src="/shared/bluesphere/16x16/actions/viewmag.png"></a>
    </td>
    <td>{$line.t_revi}</td>
    <td>{$line.t_oqua}</td>
    <td>{$line.t_dqua}</td>
    <td>{$line.t_bqua}</td>
    <td>{$line.t_cuqp}</td>
    <td>{$line.t_uscu}</td>
    <td>{$line.t_ddta}</td>
    <td>{$line.t_resc}</td>
    <td>{$line.t_excm}</td>
    <td>{$line.t_ddtc}</td>
    <td>{$line.t_ddtb}</td>
    <td>{$line.t_ddts}</td>
    <td>{$line.days_to_del}</td>
    <td>{$line.t_pric}</td>
    <td>{$line.t_prip}</td>
    <td>{$line.t_copr}</td>
    <td>{$line.prchange}</td>
    <td>{$line.t_ltpr}</td>
    <td>{$line.change}</td>
    <td>{$line.fai}</td>
    <td>{if $line.su_replied=='0' || $line.su_replied=='N'}N{else}&nbsp;{/if}</td>
    <td>{$line.discount}</td>
    <td>{$line.t_amta}</td>
  </tr>
  <tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
    <td colspan="{if $mode=="shipped"}24{else}23{/if}">
        {$line.t_dsca} {if $line.t_aitc<>''}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <b>Vendor PN:{$line.t_aitc}</b>{/if}
    </td>
  </tr>
{/foreach}
  <tr>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th>Total Order<br>Amount</th>
  </tr>
  <tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td>{$total}</td>
  </tr>
</table>