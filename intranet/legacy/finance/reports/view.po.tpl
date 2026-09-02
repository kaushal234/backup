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
    <th colspan="{if $mode=="shipped"}24{else}23{/if}">Description &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Vendor PN</th>
    <th>Desc. in CH</th>
    <th>Weight</th>
    <th>Size</th>
    <th>Qty</th>
    <th>Order Price</th>
    <th>Country of Orignal</th>
    <th>HS Code</th>
    <th>HS Code dsca.</th>
  </tr>

  {foreach name=lines item=line from=$detail}
  <tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
    <td>{$line.t_pono}</td>
    <td>
      <a href="/en/private/manufacturing/whse/dev.php?m[0]=inv&m[1]=byPNPlanned&erp={$erp}&id={$line.t_item}&btnSubmit=Submit">
        {$line.t_item}</a>
    </td>
    <td colspan="{if $mode=="shipped"}24{else}23{/if}">
      {$line.t_dsca} {if $line.t_aitc<>''}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <b>Vendor PN:{$line.t_aitc}</b>{/if}
    </td>
    <td>{$line.t_dscb}</td>
    <td>{$line.t_wght}</td>
    <td>{$line.t_dscc}</td>
    <td>{$line.t_oqua}</td>
    <td>{$line.t_pric}</td>
    <td>{$line.t_ctyo}</td>
    <td>{$line.t_ccde}</td>
    <td>{$line.t_dssh}</td>
  </tr>
{/foreach}
  <tr>
    <th></th>
    <th></th>
    <th></th>
    <th></th>


    <th>Total Order<br>Amount</th>
  </tr>
  <tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
    <td></td>
    <td></td>
    <td width="80"></td>
    <td width="80"></td>

    <td width="120">{$total}</td>
  </tr>
</table>
