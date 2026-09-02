<?php

$help=array("Metrics Help Page"=>"<p> Metrics definitions </p>",
		
		"Engineering Time spent per Category"=>"<br/><b>What does it measure exactly?</b>
		<p>Measure engineering timesheet hours per category.</p>
		<br/><b>How it is generated?</b><p>Sum of all engineering timesheet hours for a given factory and period of time, grouped by category.
		<br/>   - For WIN/SHE/SHA/WUX: Sum formula based on actual hours
		<br/>   - For other BUs: Sum formula based of hours per day field (timesheets in % of day) </p>",
		
		"Engineering Timesheets spent per Module"=>"<br/><b>What does it measure exactly?</b>
		<p>Measure engineering hours per project type.</p>
		<br/><b>How it is generated?</b><p>Sum of all engineering timesheet hours not in \"Vacation-Sick Leave\", \"Non Productive Hours\" or \"Other\" category for a given factory and period of time, grouped by project.
		<br/>   - For WIN/SHE/SHA/WUX: Sum formula based on actual hours
		<br/>   - For other BUs: Sum formula based of hours per day field (timesheets in % of day) </p>",

        "Engineering Time spent per Category - Factories comparison"=>"<br/><b>What does it measure exactly?</b>
		<p>Measure engineering timesheet % per category per factories.</p>
		<br/><b>How it is generated?</b><p>Sum of all engineering timesheet % for multiple factories and period of time, grouped by category.
		<br/>   - For WIN/SHE/SHA/WUX: Sum formula based on actual hours
		<br/>   - For other BUs: Sum formula based of hours per day field (timesheets in % of day) </p>",

        "Engineering Timesheets spent per Module - Factories comparison"=>"<br/><b>What does it measure exactly?</b>
        <p>Measure engineering % per project type per factories.</p>
        <br/><b>How it is generated?</b><p>Sum of all engineering timesheet % not in \"Vacation-Sick Leave\", \"Non Productive Hours\" or \"Other\" category for multiple factories and period of time, grouped by project.
        <br/>   - For WIN/SHE/SHA/WUX: Sum formula based on actual hours
        <br/>   - For other BUs: Sum formula based of hours per day field (timesheets in % of day) </p>",
);

?>