<h3>Manufacturing Module Homepage</h3>

<p>Welcome to the Manufacturing Module Homepage.</p>

<h3>TLD ShopCams</h3>

<table width="100%">
    <tr>
        {foreach key=region item=locations from=$cameras}
            <td>
                {foreach key=factory item=files name=locations from=$locations}
                    {if $smarty.foreach.locations.first || $smarty.foreach.locations.iteration % 2 != 0}
                    <table width="100%">
                        <tr>
                    {/if}
                            <td>
                                <h4>{$factory}</h4>
                                {foreach key=file item=title index=index from=$files}
                                    <a href="{$baseUrl}{$file}">
                                        <img src="{$baseUrl}{$file}" width="200">
                                    </a>
                                    <br>
                                    {$title}
                                    <br>
                                {/foreach}
                            </td>
                    {if $smarty.foreach.locations.last || $smarty.foreach.locations.iteration % 2 == 0}
                        </tr>
                    </table>
                    {/if}
                {/foreach}
            </td>
        {/foreach}
    </tr>
</table>
