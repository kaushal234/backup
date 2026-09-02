<customer>{$so.header.customer.t_cuno}</customer>
<purchase_order>{$so.header.custpo}</purchase_order>
<deliver_date>{$so.header.t_odat}</deliver_date>
<carrier>{$so.header.carrier.t_cfrw}</carrier>
<shipto_code>{$so.header.deliverAddress.t_cdel}</shipto_code>
<ship_complete>{$so.header.shipComplete}</ship_complete>
<shipto_line1>{$so.header.deliverAddress.line1}</shipto_line1>
<shipto_line2>{$so.header.deliverAddress.line2}</shipto_line2>
<shipto_line3>{$so.header.deliverAddress.line3}</shipto_line3>
<shipto_line4>{$so.header.deliverAddress.line4}</shipto_line4>
<shipto_line5>{$so.header.deliverAddress.line5}</shipto_line5>
<shipto_line6>{$so.header.deliverAddress.line6}</shipto_line6>
<note>{$so.header.note}</note>
<lines>
{foreach key=key item=line from=$so.lines}
	<line><product>{$key}</product><quantity>{$line.qty}</quantity></line>
{/foreach}
</lines>
