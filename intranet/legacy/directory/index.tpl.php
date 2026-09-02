<?php
ob_start();

// Search -------------------------------->
echo include('people/people.form.search.tpl.php');

// ORG CHART -------------------------------->
echo include('orgChart/orgChart.homepage.tpl.php');

return ob_get_clean();
