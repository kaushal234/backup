<?php
$_output_before = ob_get_contents();
ob_start();
?>

<script type="text/javascript" src="/shared/javascript/jquery/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="/shared/javascript/jquery/jquery-ui-1.10.1.custom.min.js"></script>
<link rel="stylesheet" href="/shared/javascript/jquery/css/smoothness/jquery-ui-1.10.1.custom.min.css">



</script>

<!--< Dashboard 1: PO lines without a quality inspection to perform >-->

<form action="<?= $php_self ?>" method="get" name="frmAPInvoicesCreate" id="frmAPInvoicesCreate">
<input name="_qf__frmAPInvoicesCreate" type="hidden" value="" />
<input name="m[0]" type="hidden" value="reports" />
<input name="m[1]" type="hidden" value="InvCheckRequest" />
<input name="s" type="hidden" value="<?=$vars['s']?>" />
<input name="o" type="hidden" value="<?=$vars['o']?>" />
<input name="r" type="hidden" value="<?=$vars['r']?>" />
<input name="orderStatus" type="hidden" value="<?=$vars['orderStatus']?>" />
<input name="z" type="hidden" value="<?=$vars['z']?>" />

<table width="100%" class="tld_table">
  <tr style="background:#2971a8; color:white;">
    <th colspan=5>Lines with an in-progress Check-Request</th>
  </tr>
  <tr style="background:#2971a8; color:white;">
	<th>Supplier</th>
	<th>Purchase order</th>
	<th>Line</th>
  	<th>Item</th>
    <th>Description</th>
    <th>U/M</th>
    <th>Amount</th>
    <th>Ordered qty</th>
    <th>Invoice Amount</th>
    <th>Invoice Qty</th>
    <th>Supplier Invoice#</th>
    <th>Delivered qty</th>
    <th>Invoiced qty</th>
    <th>Contact name</th>
    <th>Add comment</th>
    <th>Check-Request reason</th>
    <th>Invoice ID</th>
    <th>Last Comment</th>
  </tr>
<?php foreach($rows1 as $key1=>$value1): ?>

<?php if ($rows1[$key1]['reason']!='') { ?>

<?php $color = ($key1%2) ? "#eeeeee" : "#d0d0d0"; ?>
  <tr style="background:<?= $color ?>">
	<td title="Supplier: <?= $rows1[$key1]['w_nama'] ?>"><?= $rows1[$key1]['w_suno'] ?></td>
	<td title="Order type: <?= $rows1[$key1]['w_cotp'] ?> / Order date: <?= $rows1[$key1]['w_odat'] ?>"><?= $rows1[$key1]['w_orno'] ?></td>
	<td><?= $rows1[$key1]['w_pono'] ?></td>
	<td title="Position: <?= $rows1[$key1]['w_pono'] ?>"><?= $rows1[$key1]['w_item'] ?></td>
	<td><?= $rows1[$key1]['w_dsca'] ?></td>
	<td><?= $rows1[$key1]['w_cuqp'] ?></td>
	<td title="Unit price: : <?= $rows1[$key1]['w_pric'] ?>"><?= $rows1[$key1]['w_amta'] ?></td>
	<td><?= $rows1[$key1]['w_oqua'] ?></td>

	<td><?= $rows1[$key1]['t_inmt'] ?></td>
	<td><?= $rows1[$key1]['t_inqt'] ?></td>
	<td><?= $rows1[$key1]['inv_xref'] ?></td>

	<td>
	  <a href="<?= $php_self ?>?_qf__frmAPInvoicesControl_Receipts=&m[0]=reports&m[1]=listing&m[2]=APInvoicesControl_Receipts&z=<?=$vars['z']?>&o=<?= $rows1[$key1]['w_orno'] ?>&linef=<?= $rows1[$key1]['w_pono'] ?>&linet=<?= $rows1[$key1]['w_pono'] ?>">
      <?= $rows1[$key1]['w_dqua_str'] ?>
	  </a>
	</td>
	<td>
	  <a href="<?= $php_self ?>?_qf__frmAPInvoicesControl_Invoices=&m[0]=reports&m[1]=listing&m[2]=APInvoicesControl_Invoices&z=<?=$vars['z']?>&o=<?= $rows1[$key1]['w_orno'] ?>&linef=<?= $rows1[$key1]['w_pono'] ?>&linet=<?= $rows1[$key1]['w_pono'] ?>">
      <?= $rows1[$key1]['w_qana_str'] ?>
	  </a>
	</td>
	<td title="Contact ID: <?= $rows1[$key1]['w_ccon'] ?>"><?= $rows1[$key1]['w_namb'] ?></td>
	<td>
		<img class="addComment" reqErp="<?=$vars['z']?>" reqOrno="<?= $rows1[$key1]['w_orno'] ?>" reqPono="<?= $rows1[$key1]['w_pono'] ?>" reqSuno="<?= $rows1[$key1]['w_suno'] ?>" src="/shared/bluesphere/16x16/actions/viewmag+.png" title="Add" />
	</td>
	<?php if ($rows1[$key1]['reason']!='') { ?>
		<td>&nbsp; <img class="openComment" reqErp="<?=$vars['z']?>" reqOrno="<?= $rows1[$key1]['w_orno'] ?>" reqPono="<?= $rows1[$key1]['w_pono'] ?>" src="/shared/bluesphere/16x16/actions/toggle_log.png" title="Log" />  &nbsp;<?= $rows1[$key1]['reason'] ?> </td>
	<?php } else { ?>
		<td></td>
	<?php } ?>
	<td><a target='_blank' href='/en/private/invoices_po/spool/<?=$vars['z']?>/<?=trim($rows1[$key1]['w_suno']);?>-<?=trim($rows1[$key1]['inv_xref']);?>.jpeg'>Display invoice</a></td>
    <td><?= $rows1[$key1]['last_User'] ?></td>
  </tr>

<?php } ?>

<?php endforeach; ?>
</table>
</form>

<div id="dlgComment" title="Check-Request comments"></div>


<div id="dlgAddComment" title="Create a new Comment">
<p>All form fields are required.</p>
	<form>
		<fieldset>
			<input type="hidden"  name="dlgFieldHiddenOrno" id="dlgFieldHiddenOrno" value="1">
			<input type="hidden"  name="dlgFieldHiddenPono" id="dlgFieldHiddenPono" value="2">
			<input type="hidden"  name="dlgFieldHiddenSuno" id="dlgFieldHiddenSuno" value="3">
			<label>Comment:</label><br>
			<input type="text" name="dlgFieldComment" id="dlgFieldComment" value=''><br>
		</fieldset>
	</form>
</div>


<script type="text/javascript" language="javascript">

$( "#dlgComment" ).dialog({ autoOpen: false, width: 500, height: 400 });


//Call ajax Comments retrieving
$( ".openComment" ).click(function() {
	rep = msgAjax($( this ).attr("reqErp"), $( this ).attr("reqOrno"), $( this ).attr("reqPono"));
});


$( "#dlgAddComment" ).dialog({
	autoOpen: false,
	width: 500,
	height: 400,
	modal: true,
	buttons: {
		"Create": function() {
			jvComp = $( ".addComment" ).attr("reqErp");
			jvOrno = $( "#dlgFieldHiddenOrno" ).val();
			jvPono = $( "#dlgFieldHiddenPono" ).val();
			jvSuno = $( "#dlgFieldHiddenSuno" ).val();
			jvUscr = "<?= $user->getUserID() ?>";
			jvComm = $( "#dlgFieldComment" ).val();
			//alert(jvComp + " - " + jvOrno + " - " + jvPono + " - " + jvSuno + " - " + jvUscr + " - " + jvComm);

			rep = addCommentAjax(jvComp, jvOrno, jvPono, jvSuno, jvUscr, jvComm);
			$( this ).dialog( "close" );

		},
		"Cancel": function() {
			$( this ).dialog( "close" );
		 }
	}
});


//Call ajax Add Comment
$( ".addComment" ).click(function() {
	$( "#dlgFieldComment" ).val("");

	$( "#dlgFieldHiddenOrno" ).val($( this ).attr("reqOrno"));
	$( "#dlgFieldHiddenPono" ).val($( this ).attr("reqPono"));
	$( "#dlgFieldHiddenSuno" ).val($( this ).attr("reqSuno"));

	$( "#dlgAddComment" ).dialog( "open" );
});



function addCommentAjax(jvComp, jvOrno, jvPono, jvSuno, jvUscr, jvComm)
{
	var xhr=null;
	if (window.XMLHttpRequest) {
		xhr = new XMLHttpRequest();
	}
	else if (window.ActiveXObject) {
		xhr = new ActiveXObject("Microsoft.XMLHTTP");
	}

	xhr.onreadystatechange = function() {
		if (xhr.readyState==4) {
			//str = "order: <b>" + jvOrno + "</b> - Line: <b>" + jvPono + "</b><hr>";
	    	//str = str + xhr.responseText;
			//$( "#dlgAddComment" ).html(str);
			//$( "#dlgAddComment" ).dialog( "open" );
		 }
	};

	varUrl="/en/private/finance/finance.php?m[0]=ap&m[1]=CreateApInv&m[2]=AP_Check_Request_Add_Comments" + "&var[1]=" + jvComp + "&var[2]=" + jvOrno + "&var[3]=" + jvPono + "&var[4]=" + jvSuno + "&var[5]=" + jvUscr + "&var[6]=" + jvComm;
	//alert(varUrl);
	xhr.open("GET", varUrl, false);
	xhr.send(null);
}


function msgAjax(jvComp, jvOrno, jvPono)
{
	var xhr=null;
	if (window.XMLHttpRequest) {
		xhr = new XMLHttpRequest();
	}
	else if (window.ActiveXObject) {
		xhr = new ActiveXObject("Microsoft.XMLHTTP");
	}

	xhr.onreadystatechange = function() {
		if (xhr.readyState==4) {
			str = "order: <b>" + jvOrno + "</b> - Line: <b>" + jvPono + "</b><hr>";
	    	str = str + xhr.responseText;
			$( "#dlgComment" ).html(str);
			$( "#dlgComment" ).dialog( "open" );
		 }
	};

	varUrl="/en/private/finance/finance.php?m[0]=ap&m[1]=CreateApInv&m[2]=AP_Check_Request_Comments" + "&var[1]=" + jvComp + "&var[2]=" + jvOrno + "&var[3]=" + jvPono;
	xhr.open("GET", varUrl, false);
	xhr.send(null);
}



 </script>

<?php
$_output_after = ob_get_contents();
ob_end_clean();
return $_output_before . $_output_after;
?>
