{literal}
<style>
    .sortable { list-style: none; }
    .sortable li { cursor: move}
    .ui-state-highlight { min-height: 40px!important;}
</style>
{/literal}

<h3>{if $drivingManual}Standard{else}Full{/if} manual PDF generation</h3>
<form method="post">
    <input type="hidden" name="erp" value="$erp" />
    <input type="hidden" name="model" value="{$model}" />
    <p>
        <label for="serialNumber"><span style="color: #ff0000">*</span><b>Serial Number (used on the first page):</b></label>
        <br />
        <input type="text" name="serialNumber" value="{$sn}" />
    </p>
    {foreach from=$chapters key=chapter item=elements}
        <input type="checkbox" name="{$chapter|replace:' ':''}" class="toggle-chapter" {if $drivingManual!=true or 'Chapter 5'!=$chapter}checked{/if} >
        <label for="{$chapter|replace:' ':''}"><b>{$chapter|upper}</b>&nbsp;&nbsp;&nbsp;&nbsp;<small>(documents can be sorted by drag and drop)</small></label>
        <ul class="sortable">
            {foreach from=$elements key=key item=element}
                <li>
                    <input type="checkbox" name="{$key}{$chapter|replace:' ':''}" {if $drivingManual!=true or 'Chapter 5'!=$chapter}checked{/if} data-filename="{$element.filepath}" />
                    <label for="{$key}{$chapter|replace:' ':''}">{$element.endescription} {if !$element.filepath}<span style="color: red; font-size: 80%;">&nbsp;&nbsp;(This file is not accessible and will not be added)</span>{/if}</label>
                </li>
            {/foreach}
        </ul>
    {/foreach}
    <button type="submit">Submit</button>
</form>

{literal}
    <script type="text/javascript">
      $(document).ready(function() {
        var $sortable = $('.sortable');
        $sortable.sortable({
          placeholder: "ui-state-highlight"
        });
        $sortable.disableSelection();

        $('.toggle-chapter').change(function() {
          var value = this.checked;
          $(this).next().next().find('input').each(function(){
            $(this).prop('checked', value);
          })
        })

        $("form").submit(function(e)
        {
          $(this).children('.toggle-chapter').attr("disabled", "disabled");
          $(this).find('input:checked[class!="toggle-chapter"]').each(function() {
            $(this).val($(this).data('filename'))
          })

          return true; // ensure form still submits
        });
      });
    </script>
{/literal}
