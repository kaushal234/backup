<h2>BOM for {$bom->itsID} from company {$bom->itsERP} as of {if $smarty.now|date_format:"%Y-%m-%d" > $bom->itsDate}<span style="color:#f00">{$bom->itsDate}</span>{else}{$bom->itsDate}{/if}</h2>
<div style="display: flex; justify-content: center; flex-direction: column; border: 2px solid orange; margin: 1rem auto; padding: 0 1rem; width: fit-content">
	<p style="color: orange"><strong>Warning : </strong>for effective date before 01/01/2023 please refer to the manual of your Equiment Record</p>
	<p style="color: orange; margin-top: 0"><strong>Warning : </strong> Files size limits - 500 MB for download - 100 MB for files in Zip archive</p>
</div>
<table>
	<tr>
		<th>Part Number</th>
		<th>Position</th>
		<th>Description</th>
		<th>Qty</th>
		<th>PBOM Qty</th>
		<th>UM</th>
		<th>Status</th>
		<th>Drawing</th>
		<th>Rev</th>
		<th>Effective Date</th>
		<th>Eitem Code</th>
		<th>Code</th>
		<th>Extra Info</th>
		<th>Box</th>
		<th>P</th><th>M</th><th>O</th><th>C</th>
		<th>Owner</th>
		<th>EAP Flag</th>
		<th>PDC Flag</th>
		<th>NCR</th>

        <th>Revision Desc</th>
        <th>Standard Cost</th>
	</tr>
{foreach name=bom key=key item=line from=$bom->itsBOMAsArray}
	<p>{$line.conflictItemTexts[0].textByLanguage.text}</p>
	<tr><td style="white-space: nowrap">
	{section name=indent start=0 loop=$line.level}
		.
	{/section}
	<a href="{$smarty.server.SCRIPT_NAME}?m[0]=bom&m[1]=view&erp={$bom->itsERP}&pn={$line.t_sitm}&date={$bom->itsDate}">
	{$line.t_sitm|escape:"htmlall"}
	</a>
	</td>
	<td>
		{section name=indent start=0 loop=$line.level}
			&nbsp;
		{/section}
		<img src="/shared/branch.gif">{$line.t_pono}</td>

	<td style="display: flex; white-space: wrap; height: fit-content; min-height: 1.2rem; align-items: center; justify-content: space-between; width: 200px;"
		{if !empty($line.conflictItemTexts) }
			onClick="javascript:$('#comment{$line.id}').dialog('open');"
			onMouseOver="javascript:overlib('{foreach name=lines item=conflictItemText from=$line.conflictItemTexts}{if !empty($conflictItemText.textByLanguage)}{$conflictItemText.textByLanguage.text|regex_replace:'/[\<\>\r\t\n]/':'<br/>'|escape:'quotes'|escape:'htmlall'}{/if}</p>{if !$smarty.foreach.conflictItemTexts.last}<hr>{/if}{/foreach}',
					CAPTION, 'Text#', WIDTH, 400, OFFSETX, 50, VAUTO,
					FGCOLOR, 'white', BGCOLOR, 'gray', TEXTSIZE, 2, CAPTIONSIZE,2);"
			onMouseOut="javascript:nd();"
		{/if}
	>
	{if !empty($line.conflictItemTexts)}
		<img src="/shared/bluesphere/16x16/actions/toggle_log.png"
			 style="height: 1rem; justify-self: baseline"
		/>
		<div class="popup" id="comments{$txta.id}" title="Text Comments" style="display:none;">
            {foreach name=lines item=txta from=$line.txta}

				<p>{$txta.dt} - {$txta.t_ctxt}
				{if !empty($txta.t_text)}
                        {$txta.t_text|escape:'quotes'}
                    {/if}
				</p>
                {if !$smarty.foreach.txta.last}<hr>{/if}
            {/foreach}
		</div>
		<p style="color: red">{$line.t_dsca}</p>
	{else}
		{$line.t_dsca}
	{/if}
	</td>
	<td>{$line.t_qana}</td>
	<td>{$line.productQuantity}</td>
	<td>{$line.t_cuni}</td>
	<td>
	{if !$line.expired}
		OK
	{else}
		This item was changed {$line.t_exdt}
	{/if}
	</td>
		<td>
			<a target="_blank" href="{$smarty.server.SCRIPT_NAME}?m[0]=getfile&m[1]=drawing&erp={$bom->itsERP}&project={$line.partNumberProject}&item={$line.t_sitm}&date={$bom->itsDate}" title="drawing">
				<img src="/shared/bluesphere/16x16/actions/filesaveas.png" alt="Save file to your hard disk">
			</a>
			<a target="_blank" href="{$smarty.server.SCRIPT_NAME}?m[0]=getfile&m[1]=drawing_3d_files&erp={$bom->itsERP}&project={$line.partNumberProject}&item={$line.t_sitm}&date={$bom->itsDate}" title="3D files">
				<svg width="16" height="16" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" fill="#2c5aa0"><path d="M13.535,25L71.464,25L86.464,10L28.535,10Z"/><path d="M75,86.464L90,71.464L90,13.536L75,28.536Z"/><path d="M10,30L70,30L70,90L10,90L10,30Z"/></svg>
			</a>
		</td>
	<td>{$line.t_revi}</td>
	<td>{$line.t_indt}</td>
	<td>{$line.t_csig_edm}</td>
	<td>{$line.t_csig}</td>
	<td>{$line.t_exin}</td>
	<td>{$line.t_opno}</td>
	<td>{if $line.P<>0 || $line.P=="P"}{$line.P}{else}&nbsp;{/if}</td>
	<td>{if $line.M<>0 || $line.M=="M"}{$line.M}{else}&nbsp;{/if}</td>
	<td>{if $line.O<>0 || $line.O=="O"}{$line.O}{else}&nbsp;{/if}</td>
	<td>{if $line.C<>0 || $line.C=="C"}{$line.C}{else}&nbsp;{/if}</td>
	<td>{$line.t_csel}</td>

	<td>
		{if $line.eap == true}
		<a href="{$smarty.server.SCRIPT_NAME}?_qf__frm=&m[0]=eap&m[1]=mlList&m[2]=byPN&pn={$line.t_sitm}&btnSubmit=Submit"  target="_blank">
			<img src="/shared/icons/signs_symbols/warning.gif" width="18" title="EAP: {$line.eap_pn}">
		</a>
		{/if}
	</td>
	<td>
		{if $line.pdc == true}
		<a href="/en/private/product_support/index.ps.php?m[0]=pdc&m[1]=listing&m[2]=byPartNumber&pn={$line.pdc_pn}"  target="_blank">
			<img src="/shared/icons/signs_symbols/warning.gif" width="18" title="PDC: {$line.pdc_id}">
		</a>
		{/if}
	</td>
	<td>
		<a href="{$line.linkNcr}">
			{$line.numberNonConformity}
		</a>
	</td>
	<td>{$line.rev_desc}</td>
	<td>
		<a href="/en/private/parts/parts.php?_qf__frmInventory=&m[0]=inv&m[1]=view&id={$line.t_sitm}" target="_blank">
			<img src="/shared/bluesphere/16x16/actions/viewmag.png" alt="Show Standard Cost"></a>
	</td>
	</tr>
{/foreach}
</table>
