<h2>Directory Reports Homepage</h2>

<p>Welcome to the Directory Reports Homepage</p>


<h3>Cleanup reports</h3>

<ul>
  <li>
    <a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=listing&m[2]=userCleanUp&m[3]=division">
  	Active user with no TLD division set</a>
  </li>
  <li>
    <a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=listing&m[2]=userCleanUp&m[3]=bu">
    Active user with no TLD BU set or BU that do not match with the correct division</a>
  </li>
  <li>
    <a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=listing&m[2]=userCleanUp&m[3]=department">
    Active user with no TLD department set</a>
  </li>
  <li>
    <a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=listing&m[2]=userCleanUp&m[3]=function">
    Active user with no TLD function set</a>
  </li>
  <li>
    <a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=full_search">
    List all user by division, BU, department and function</a>
  </li>
</ul>