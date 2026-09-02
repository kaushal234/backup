{if count($rows)}
	<h2>CBOM for ERP: {$erp}, Project#: {$sn} as of {if $smarty.now|date_format:"%Y-%m-%d" > $date}<span style="color:#f00">{$date}</span>{else}{$date}{/if}</h2>
	<h2>{$group}</h2>

	<table>
		<tr bgcolor="#d0d0d0">
			<th>Pos Number</th>
			<th>PN</th>
			<th>Code</th>
			<th>EDM Description</th>
			<th>Alternative {$altLang} Description</th>
			<th>Drawing</th>
			<th>Rev</th>
			<th>Effective Date</th>
			<th>PCQ</th>
			<th>ECQ</th>
			<th>QCQ</th>
			<th>IC</th>
			<th>Qty</th>
			<th>PBOM Qty</th>
			<th>Oper</th>
			<th>Extra</th>
			<th>EAP Flag</th>
			<th>PDC Flag</th>
		</tr>
		{foreach key=key item=line from=$rows}
			<tr bgcolor="{if $key % 2 == 1}#d0d0d0{else}#eeeeee{/if}">
				<td>
					{section name=indent start=0 loop=$line.level}
						&nbsp;
					{/section}
					<img src="/shared/branch.gif">{$line.t_pono}</td>
				<td style="white-space: nowrap">
					{section name=indent start=0 loop=$line.level}
						.
					{/section}
					<a href="{$smarty.server.SCRIPT_NAME}?m[0]=bom&m[1]=view&erp={$erp}&project={$line.partNumberProject}&pn={$line.t_sitm}&date={$date}">
						{$line.t_sitm|escape:"htmlall"}
					</a>
				</td>
				<td>{$line.t_csig_edm}</td>
				<td style="white-space: nowrap">{$line.t_dsca}</td>
				<td style="white-space: nowrap">{$line.altdsca}</td>
				<td>
					<a target="_blank" href="{$smarty.server.SCRIPT_NAME}?m[0]=getfile&m[1]=drawing&erp={$erp}&project=&item={$line.t_sitm}&date={$date}">
					<img src="/shared/bluesphere/16x16/actions/filesaveas.png" alt="Save file  to your hard disk"></a>
				</td>
				<td>{$line.t_revi}</td>
				<td>{$line.t_indt}</td>
				<td>{$line.pi_pcq}</td>
				<td>{$line.pi_ecq}</td>
				<td>{$line.pi_qcq}</td>
				<td>{$line.IC}</td>
				<td>{$line.t_qana}</td>
				<td>{$line.productQuantity}</td>
				<td>{$line.t_opno}</td>
				<td>{$line.t_exin}</td>
				<td>
					{if isset($line.eap) && $line.eap == true}
						<a href="{$smarty.server.SCRIPT_NAME}?_qf__frm=&m[0]=eap&m[1]=mlList&m[2]=byPN&pn={$line.t_sitm}&btnSubmit=Submit"  target="_blank">
							<img src="/shared/icons/signs_symbols/warning.gif" width="18">
						</a>
					{/if}
				</td>
				<td>
					{if isset($line.pdc) && $line.pdc == true}
						<a href="/en/private/product_support/index.ps.php?m[0]=pdc&m[1]=listing&m[2]=byPartNumber&pn={$line.t_sitm}"  target="_blank">
							<img src="/shared/icons/signs_symbols/warning.gif" width="18">
						</a>
					{/if}
				</td>
			</tr>
		{/foreach}
	</table>

{else}
	No CBOM found
{/if}