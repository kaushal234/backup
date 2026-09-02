<form name="editVAT" method="post" action="{$nextURL}">
        <table class="sortable" cellpadding="3"">
                <thead>
                        <tr>
                                <th>Order Date</th>
                                <th>Order#</th>
                                <th>Type</th>
                                <th>Pack#</th>
                                <th>Inv#</th>
                                <th>Sale REP</th>
                                <th>Cust#</th>
                                <th>Cust Name</th>
                                <th>Status</th>
                                <th>TODO</th>
                                <th>Comments</th>
                                <th>Ref A</th>
                                <th>Ref B</th>
                                <th>Qty</th>
                                <th>Del</th>
                                <th>Back</th>
                                <th>PN</th>
                                <th>Description</th>
                                <th>OO</th>
                                <th>OH</th>
                                <th>AL</th>
                                <th>Date</th>
                        </tr>
                </thead>
                <tbody>
                        {foreach name=vat item=vat from=$vat}
                        <tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
                                <td>{$vat.t_odat}</td>
                                <td>{$vat.t_orno}</td>
                                <td>{$vat.t_cotp}</td>
                                <td>{$vat.t_dino}</td>
                                <td><input type="text" name="vat[{$vat.t_inv}][id]" value="{$vat.t_inv}" readonly="readonly" autocomplete="off"/>{$vat.t_invn}</td>
                                <td>{$vat.t_info}</td>
                                <td>{$vat.t_cuno}</td>
                                <td>{$vat.t_nama}</td>
                                <td>{$vat.status}</td>
                                <td><select name="vat[{$vat.t_inv}][tobeinvoice]">
                                <option selected="selected" value="{$vat.tobeinvoice}">{$vat.tobeinvoice}</option>
                                <option value="Y">YES</option>
                                <option value="N">NO</option>
                                </select></td>
                                <td><input type="text" name="vat[{$vat.t_inv}][comment]" value="{$vat.comment}" "size="9" /></td>
                                <td>{$vat.t_refa}</td>
                                <td>{$vat.t_refb}</td>
                                <td>{$vat.t_oqua}</td>
                                <td>{$vat.t_dqua}</td>
                                <td>{$vat.t_bqua}</td>
                                <td>{$vat.t_item}</td>
                                <td>{$vat.t_dsca}</td>
                                <td>{$vat.t_ordr}</td>
                                <td>{$vat.t_stoc}</td>
                                <td>{$vat.t_allo}</td>
                                <td>{$vat.trdt}</td>
                                </tr>
                        {/foreach}
                </tbody>
        </table>
        <br/>
        <input type="submit" value="Update Lines" /> <input type="reset" value="Reset" />
</form>
                                                                       
