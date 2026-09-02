<h2>Sales and Service Reports Homepage</h2>

<p>Welcome to the Sales and Service Reports Homepage</p>

{if true === $aero}
<ul>
    <li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=aeroSalesReport">AERO Sales Order Dashboard</a></li>
</ul>
{/if}