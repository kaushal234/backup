{literal}
<style type="text/css">
.econ-table thead tr th {
	background: #2971a8;
	color: white;
}
img{
    vertical-align: middle;
}
</style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form');
            if (!form) return;
            form.addEventListener('submit', function(e) {

                if (typeof tinymce !== 'undefined') {
                    tinymce.triggerSave();
                }

                const comment = document.querySelector('textarea[name="comment"]');
                if (!comment || !comment.value.trim()) {
                    alert("Please fill in the comment field.");
                    e.preventDefault();
                }
            });
        });
    </script>
{/literal}
{assign var='header' value=$meap->itsHeader}
<h3>MEAP#{$meap->getID()} Economics{if $edit}: Edit Mode{/if}</h3>
{if $edit}
<form action="{$smarty.server.SCRIPT_NAME}?m[0]=meap&m[1]=view&m[2]=econ&m[3]=edit&id={$meap->getID()}" method="post">
{/if}
<p><strong>Engineering Program Capitalized:</strong> {$meap->getEconCapitalized()}</p>
<p><strong>Program Currency:</strong> {$header.econ_currency}</p>
<p><strong>Purpose:</strong> {$header.purpose}</p>
<p><strong>Cost calculation method:</strong> {$header.cost_calculation_method}</p>
<table {if $width<>'100%'}width="100%"{/if} class="econ-table" cellpadding="8">
<thead>
	<tr>
		<th>NON RECURRENT COSTS</th>
		<th title="Estimated figures and should be compliant with the budget.
Target can only be modified by Executive">TARGET <img src="/shared/bluesphere/16x16/actions/info.png" width="18"></th>
		<th title="Estimated At Completion: by default equal to target.
If deviation to Target, should be updated and is fully logged.">EAC <img src="/shared/bluesphere/16x16/actions/info.png" width="18"></th>
		<th title="Fo be filled manually by EM or project manager with the COO and updated regularly and at each review and is fully logged.
Except Development Hours are automatically filled from timekeeping module.">ACTUAL <img src="/shared/bluesphere/16x16/actions/info.png" width="18"></th>
		<th title="Date of the figures update">DATE OF REPORT <img src="/shared/bluesphere/16x16/actions/info.png" width="18"></th>
	</tr>
</thead>
<tbody>
    <tr bgcolor="{cycle values='#eeeeee,#d0d0d0'}">
        <td title="All clocked hours including temporary engineering labor">
        	Development Hours <img src="/shared/bluesphere/16x16/actions/info.png" width="18">
        	{if !$edit}
        	<div style="float:right;font-weight:bold;"><a href="{$smarty.server.SCRIPT_NAME}?m[0]=meap&m[1]=view&m[2]=econ&m[3]=refresh&id={$meap->getID()}" style="color:DarkOrange;">[ REFRESH ]</a></div>
        	{/if}
        </td>
        <td>
        {if $edit}
        	<input name="target_dh" value="{$header.econ_target_dh}" size="10" {if !(( empty($header.econ_target_dh) AND $user->isInGroup(array('role_EM','role_ES','gg_EXCOM')) ) OR ( !empty($header.econ_target_dh) AND ($user->isInGroup(array('gg_EXCOM')) OR ( $user->isInGroup(array('role_EM','role_ES')) AND !in_array($meap->getImportanceFactor(), array(100,1000)) )) ))}disabled="disabled"{/if} />
        {else}
        	{$header.econ_target_dh}
        {/if}
        </td>
        <td>
        {if $edit}
        	<input name="eac_dh" value="{$header.econ_eac_dh}" size="10" {if !$user->isInGroup(array('role_EM','role_ES','role_ENG'))}disabled="disabled"{/if} />
        {else}
        	{$header.econ_eac_dh}
        {/if}
        </td>
        <td>
        	{$header.econ_actual_dh}
        </td>
        <td>
        	{$header.econ_actual_dh_dt}
        </td>
    </tr>
    <tr bgcolor="{cycle values='#eeeeee,#d0d0d0'}">
        <td title="This section is related to outsourced engineering contracted service like out sourced FEA analysis, CE certification by third party, test done in test facility, CAAC certification cost...">
            Subcontracted Engineering Amount <img src="/shared/bluesphere/16x16/actions/info.png" width="18">
        </td>
        <td>
        {if $edit}
        	<input name="target_sea" value="{$header.econ_target_sea}" size="10" {if !(( empty($header.econ_target_sea) AND $user->isInGroup(array('role_EM','role_ES','gg_EXCOM')) ) OR ( !empty($header.econ_target_sea) AND ($user->isInGroup(array('gg_EXCOM')) OR ( $user->isInGroup(array('role_EM','role_ES')) AND !in_array($meap->getImportanceFactor(), array(100,1000)) )) ))}disabled="disabled"{/if} />
        {else}
        	{$header.econ_target_sea}
        {/if}
        </td>
        <td>
        {if $edit}
        	<input name="eac_sea" value="{$header.econ_eac_sea}" size="10" {if !$user->isInGroup(array('role_EM','role_ES','role_ENG'))}disabled="disabled"{/if} />
        {else}
        	{$header.econ_eac_sea}
        {/if}
        </td>
        <td>
        {if $edit}
        	<input name="actual_sea" value="{$header.econ_actual_sea}" size="10" {if !$user->isInGroup(array('role_EM','role_ES','role_COO','role_ENG'))}disabled="disabled"{/if} />
        {else}
        	{$header.econ_actual_sea}
        {/if}
        </td>
        <td>
        	{$header.econ_actual_sea_dt}
        </td>
    </tr>
    <tr bgcolor="{cycle values='#eeeeee,#d0d0d0'}">
        <td title="This section is consolidating cost related to project for special engineering tool, software for programming, data collection.">
            Material &amp; Other Costs <img src="/shared/bluesphere/16x16/actions/info.png" width="18">
        </td>
        <td>
        {if $edit}
        	<input name="target_maoc" value="{$header.econ_target_maoc}" size="10" {if !(( empty($header.econ_target_maoc) AND $user->isInGroup(array('role_EM','role_ES','gg_EXCOM')) ) OR ( !empty($header.econ_target_maoc) AND ($user->isInGroup(array('gg_EXCOM')) OR ( $user->isInGroup(array('role_EM','role_ES')) AND !in_array($meap->getImportanceFactor(), array(100,1000)) )) ))}disabled="disabled"{/if} />
        {else}
        	{$header.econ_target_maoc}
        {/if}
        </td>
        <td>
        {if $edit}
        	<input name="eac_maoc" value="{$header.econ_eac_maoc}" size="10" {if !$user->isInGroup(array('role_EM','role_ES','role_ENG'))}disabled="disabled"{/if} />
        {else}
        	{$header.econ_eac_maoc}
        {/if}
        </td>
        <td>
        {if $edit}
        	<input name="actual_maoc" value="{$header.econ_actual_maoc}" size="10" {if !$user->isInGroup(array('role_EM','role_ES','role_COO','role_ENG'))}disabled="disabled"{/if} />
        {else}
        	{$header.econ_actual_maoc}
        {/if}
        </td>
        <td>
        	{$header.econ_actual_maoc_dt}
        </td>
    </tr>
    <tr bgcolor="{cycle values='#eeeeee,#d0d0d0'}">
        <td title="Case 1: Evolution of exiting product (p.e. engine emission evolution, HMI change, axle change, costred, ...) or new version of an existing product, this is the extra cost of prototype compare to reference model.

Case 2: New product, this is the extra cost of prototype compare to cost material">
            Prototype Material Costs <img src="/shared/bluesphere/16x16/actions/info.png" width="18">
        </td>
        <td>
        {if $edit}
        	<input name="target_pmc" value="{$header.econ_target_pmc}" size="10" {if !(( empty($header.econ_target_pmc) AND $user->isInGroup(array('role_EM','role_ES','gg_EXCOM')) ) OR ( !empty($header.econ_target_pmc) AND ($user->isInGroup(array('gg_EXCOM')) OR ( $user->isInGroup(array('role_EM','role_ES')) AND !in_array($meap->getImportanceFactor(), array(100,1000)) )) ))}disabled="disabled"{/if} />
        {else}
        	{$header.econ_target_pmc}
        {/if}
        </td>
        <td>
        {if $edit}
        	<input name="eac_pmc" value="{$header.econ_eac_pmc}" size="10" {if !$user->isInGroup(array('role_EM','role_ES','role_ENG'))}disabled="disabled"{/if} />
        {else}
        	{$header.econ_eac_pmc}
        {/if}
        </td>
        <td>
        {if $edit}
        	<input name="actual_pmc" value="{$header.econ_actual_pmc}" size="10" {if !$user->isInGroup(array('role_EM','role_ES','role_COO','role_ENG'))}disabled="disabled"{/if} />
        {else}
        	{$header.econ_actual_pmc}
        {/if}
        </td>
        <td>
        	{$header.econ_actual_pmc_dt}
        </td>
    </tr>
    <tr bgcolor="{cycle values='#eeeeee,#d0d0d0'}">
        <td title="This section is related to labours hours of the prototype spend by production people.">
            Prototype Labor Hours <img src="/shared/bluesphere/16x16/actions/info.png" width="18">
        </td>
        <td>
        {if $edit}
        	<input name="target_plh" value="{$header.econ_target_plh}" size="10" {if !(( empty($header.econ_target_plh) AND $user->isInGroup(array('role_EM','role_ES','gg_EXCOM')) ) OR ( !empty($header.econ_target_plh) AND ($user->isInGroup(array('gg_EXCOM')) OR ( $user->isInGroup(array('role_EM','role_ES')) AND !in_array($meap->getImportanceFactor(), array(100,1000)) )) ))}disabled="disabled"{/if} />
        {else}
        	{$header.econ_target_plh}
        {/if}
        </td>
        <td>
        {if $edit}
        	<input name="eac_plh" value="{$header.econ_eac_plh}" size="10" {if !$user->isInGroup(array('role_EM','role_ES','role_ENG'))}disabled="disabled"{/if} />
        {else}
        	{$header.econ_eac_plh}
        {/if}
        </td>
        <td>
        {if $edit}
        	<input name="actual_plh" value="{$header.econ_actual_plh}" size="10" {if !$user->isInGroup(array('role_EM','role_ES','role_COO','role_ENG'))}disabled="disabled"{/if} />
        {else}
        	{$header.econ_actual_plh}
        {/if}
        </td>
        <td>
        	{$header.econ_actual_plh_dt}
        </td>
    </tr>
</tbody>
<thead>
	<tr>
		<th>RECURRENT COSTS</th>
        <th title="Estimated figures and should be compliant with the budget.
Target can only be modified by Executive">TARGET <img src="/shared/bluesphere/16x16/actions/info.png" width="18"></th>
        <th title="Estimated At Completion: by default equal to target.
If deviation to Target, should be updated and is fully logged.">EAC <img src="/shared/bluesphere/16x16/actions/info.png" width="18"></th>
        <th title="To be filled manually by EM or project manager with the COO and updated regularly and at each review and is fully logged.
Except Development Hours are automatically filled from timekeeping module.">ACTUAL <img src="/shared/bluesphere/16x16/actions/info.png" width="18"></th>
        <th title="Date of the figures update">DATE OF REPORT <img src="/shared/bluesphere/16x16/actions/info.png" width="18"></th>
	</tr>
</thead>
<tbody>
    <tr bgcolor="{cycle values='#eeeeee,#d0d0d0'}">
        <td title="Material Cost should reflect values from DTC (design to cost spreadsheet).
Case 1: Evolution of exiting product (p.e. engine emission evolution, HMI change, axle change, costred, ...), this is the cost difference compare to reference model (adding '-' if saving & '+'
 if cost increase)

Case 2: New product, this is cost of production unit">
            Material <img src="/shared/bluesphere/16x16/actions/info.png" width="18">
        </td>
        <td>
        {if $edit}
        	<input name="target_material" value="{$header.econ_target_material}" size="10" {if !(( empty($header.econ_target_material) AND $user->isInGroup(array('role_EM','role_ES','gg_EXCOM')) ) OR ( !empty($header.econ_target_material) AND ($user->isInGroup(array('gg_EXCOM')) OR ( $user->isInGroup(array('role_EM','role_ES')) AND !in_array($meap->getImportanceFactor(), array(100,1000)) )) ))}disabled="disabled"{/if} />
        {else}
        	{$header.econ_target_material}
        {/if}
        </td>
        <td>
        {if $edit}
        	<input name="eac_material" value="{$header.econ_eac_material}" size="10" {if !$user->isInGroup(array('role_EM','role_ES','role_ENG'))}disabled="disabled"{/if} />
        {else}
        	{$header.econ_eac_material}
        {/if}
        </td>
        <td>
        {if $edit}
        	<input name="actual_material" value="{$header.econ_actual_material}" size="10" {if !$user->isInGroup(array('role_EM','role_ES','role_COO','role_ENG'))}disabled="disabled"{/if} />
        {else}
        	{$header.econ_actual_material}
        {/if}
        </td>
        <td>
        	{$header.econ_actual_material_dt}
        </td>
    </tr>
    <tr bgcolor="{cycle values='#eeeeee,#d0d0d0'}">
        <td title="This section is related to labour time in hours for the production unit. It may remain an estimate until the end of the project as full production rate may not be reached at Phase 4 closure">
            Target Hours <img src="/shared/bluesphere/16x16/actions/info.png" width="18">
        </td>
        <td>
        {if $edit}
        	<input name="target_hours" value="{$header.econ_target_hours}" size="10" {if !(( empty($header.econ_target_hours) AND $user->isInGroup(array('role_EM','role_ES','gg_EXCOM')) ) OR ( !empty($header.econ_target_hours) AND ($user->isInGroup(array('gg_EXCOM')) OR ( $user->isInGroup(array('role_EM','role_ES')) AND !in_array($meap->getImportanceFactor(), array(100,1000)) )) ))}disabled="disabled"{/if} />
        {else}
        	{$header.econ_target_hours}
        {/if}
        </td>
        <td>
        {if $edit}
        	<input name="eac_hours" value="{$header.econ_eac_hours}" size="10" {if !$user->isInGroup(array('role_EM','role_ES','role_ENG'))}disabled="disabled"{/if} />
        {else}
        	{$header.econ_eac_hours}
        {/if}
        </td>
        <td>
        {if $edit}
        	<input name="actual_hours" value="{$header.econ_actual_hours}" size="10" {if !$user->isInGroup(array('role_EM','role_ES','role_COO','role_ENG'))}disabled="disabled"{/if} />
        {else}
        	{$header.econ_actual_hours}
        {/if}
        </td>
        <td>
        	{$header.econ_actual_hours_dt}
        </td>
    </tr>
</tbody>
</table>
{if $edit}
    <strong>Edit Comments:</strong><br/>
    <textarea name="comment" cols="40" rows="5">{$comment}</textarea><br/>
    <input type="submit" name="submit" value="Update" />
    </form>
{/if}
{if $displayEngineeringHours}
    <br><hr>
    <p><strong>Engineering hours : Expected VS Actual number of hours :</strong></p>

    <table {if $width<>'100%'}width="100%"{/if} class="econ-table" cellpadding="8">
        <thead>
            <tr>
                <th>ID#</th>
                <th>Module</th>
                <th>Description</th>
                <th title="This number comes from the EAP creation form. It is the sum of the expected hours of this EAP and all its children EAP.">
                    EXPECTED <img src="/shared/bluesphere/16x16/actions/info.png" width="18">
                </th>
                <th title="This sum comes from the Timekeeping module. This is the total of timesheets linked to this EAP/MEAP.">
                    ACTUAL <img src="/shared/bluesphere/16x16/actions/info.png" width="18">
                </th>
            </tr>
        </thead>
        <tbody>
            {foreach name=titles key=key item=item from=$childHeaders}
                {if $item.expected_hours > 0}
                    <tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
                        <td><a href="{$php_self}?m[0]=eap&m[1]=view&id={$item.id}">{$item.id}</a></td>
                        <td>{$item.module}</td>
                        <td>{$item.short_desc}</td>
                        <td>{$item.expected_hours}</td>
                        <td>{$item.total_hours_actual}</td>
                    </tr>
                {/if}
            {/foreach}
        </tbody>
    </table>
{/if}
