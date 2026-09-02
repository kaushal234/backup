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
</style>
  <script src="/shared/javascript/jquery/jquery-ui-1.10.3.custom/js/jquery-ui-1.10.3.custom.min.js"></script>
  <link rel="stylesheet" href="/shared/javascript/jquery/jquery-ui-1.10.3.custom/css/smoothness/jquery-ui-1.10.3.custom.min.css"/>
  <script type="text/javascript">
    $(document).ready(function () {
      'use strict';

      $("#processing").dialog({
        autoOpen: false,
        closeOnEscape: false,
        draggable: false,
        resizable: false,
        dialogClass: 'fixed-dialog'
      });


      $('.popup').dialog({
        autoOpen: false,
        draggable: false,
        buttons: [{
          text: "Close", click: function () {
            $(this).dialog("close");
          }
        }],
        minWidth: 600,
        maxHeight: 300
      })
      $(document).ajaxStop(function() {
        $('#processing').dialog('close');
        $('.overlay').hide();
      });

    });
  </script>

{/literal}

<h3>{$title}</h3>

<div class="columnar">
  <table class="sortable">
    <thead>
      <tr>
        <th>Date</th>
        <th>PN#</th>
        <th>TP</th>
        <th>Transaction Type</th>
        <th>Order Type</th>
        <th>Warehouse</th>
        <th>Name</th>
        <th>SN</th>
        <th>Quantity</th>
        <th>Order#</th>
        <th>Position#</th>
        <th>TEXT</th>
        <th>Project#</th>
        <th>Login Code</th>
        <th>Inventory on hand after trans.</th>
        <th>QA text</th>
      </tr>
    </thead>
    <tbody>
    {foreach from=$rows item=row name=toclist}
      <tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
        <td>{$row.t_trdt}</td>
        <td>{$row.t_item}</td>
        <td>{$row.tp}</td>
        <td>{$row.t_kost}</td>
        <td>{$row.t_koor}</td>
        <td>{$row.t_cwar}</td>
        <td>{$row.t_alloc}</td>
        <td>{$row.sn}</td>
        <td>{$row.t_quan}</td>
        <td>
            {$row.t_orno}
        </td>
        <td>{$row.t_pono}</td>
        <td>{if !empty($row.t_txta) }
            <img src="/shared/bluesphere/16x16/actions/toggle_log.png" onClick="javascript:$('#com{$row.t_orno}').dialog('open');"
                 onMouseOver="javascript:overlib('{$row.t_txta|regex_replace:'/[\r\t\n]/':'<br/>'|escape:'quotes'|escape:'htmlall'}<br/></p><hr>',
                         CAPTION, '{$row.t_koor}#', WIDTH, 400, OFFSETX, 50, VAUTO,
                         FGCOLOR, 'white', BGCOLOR, 'gray', TEXTSIZE, 2, CAPTIONSIZE,2);"
                 onMouseOut="javascript:nd();"/>
            <div class="popup" id="com{$row.t_orno}" title="{$row.t_koor}#{$row.t_orno}" style="display:none;">
                 {$row.t_txta}
            </div>
            {else}&nbsp;{/if}
        </td>

        <td>{$row.t_cprj}</td>
        <td>{$row.t_logn}</td>
        <td>{$row.t_stoc}</td>
        <td>{$row.qa_text}</td>
    {/foreach}
    </tbody>
  </table>
</div>

