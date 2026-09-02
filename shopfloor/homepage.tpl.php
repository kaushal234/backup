<?php
ob_start();
?>

<p>Welcome to the <?= $LOCATION['location'] ?> local Intranet homepage</p>

<?php  if(in_array($ERP,array(400,410,420))): ?>

<p>
  <a href="file://///callisto/pub_mis/links/"><img src="/shared/icons/tld_windsor_files.jpg" width="150" height="100" border="0"></a>
  <a href="file://///oberon/pub_mis/links/"><img src="/shared/icons/tld_sherbrooke_files.jpg" width="150" height="100" border="0"></a>
</p>

<hr>

<h2>Internal Announcements</h2>

<h3>Windsor - Trimester report - MAY 2016</h3>
<p><a href="reports/TLDWIN_trimesterMeeting_MAY2016.pdf">Click here for Trimester report</a></p>

<h3>Sherbrooke - Trimester report - MAY 2016</h3>
<p><a href="reports/TLD_Canada_communication_2016_May.pdf">Click here for Trimester report</a></p>

<?php  endif; ?>

<?php
return ob_get_clean();
?>
