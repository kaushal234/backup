<?php
include_once("common.inc.php");
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);
tldUtils::logUserAccess();

$main_tpl = [
//table parameters
    ["table" => "manuals_diag", "form_type" => "main_tpl", "title" => "Documentation",
        "cancel" => "table&form_type=", "mode" => "record_view",
        "child_tables" => ["manuals_parts"],
        "prn_table_menu" => "<a href=\"/en/private/product_support/index.ps.php?m[0]=publications\">Back to module</a>",
        "prn_record_menu" => "<a href=\"/en/private/product_support/index.ps.php?m[0]=publications&m[1]=documents&m[2]=view&id={id}\"><b>Back To module</b></a> | ".
            "<a href=\"https://www.tld-gse.com/en/private/manuals/manuals_edit_documents.php?id={id}\">Quick Edit</a> | ",
        "default_sort" => "id",
    ],
//start of table definition
//Parts list SECTION
    ["name" => "id", "label" => "TLD Doc#", "type" => "primary_key", "table" => "true",
        "dump" => "true", "dup_exc" => "clear"],
    ["name" => "parent_id", "label" => "Foreign Key", "type" => "foreign_key", "table" => "false"],
    ["name" => "dt_created", "label" => "Date (yyyy-mm-dd)", "type" => "auto_date", "table" => "true", "dump" => "true"],
    ["name" => "factory_num", "label" => "Factory Doc#", "type" => "text", "table" => "true"],
    ["name" => "rev", "label" => "Revision", "type" => "text", "table" => "true"],
    ["name" => "category", "label" => "Category", "type" => "select", "table" => "true",
        "select_list" => [
            "",
            "Body-Chassis",
            "Covers and Panels",
            "Boom",
            "Bridge",
            "Elevator",
            "Lifting-Scissors System",
            "Power Plant",
            "Hydraulic System",
            "Pneumatic System",
            "Refrigeration System",
            "Electrical System",
            "Suspension, Tires and Brakes",
            "User Interfaces and Cab",
            "Accessories and Options",
            "Chapter 0",
            "Chapter 1",
            "Chapter 2",
            "Chapter 3",
            "Chapter 4",
            "Chapter 5",
        ],
    ],
    ["name" => "doc_type", "label" => "Doc Type", "type" => "select", "table" => "true",
        "select_list" => ["PARTS DIAGRAM",
            "HYD SCHEM",
            "ELEC SCHEM",
            "FLOW SCHEM",
            "MANUAL:TOC",
            "MANUAL:SECTION",
            "MANUAL:APPENDIX",
            "MANUAL:OEM LIT",
        ],
    ],
    ["name" => "endescription", "label" => "Description", "type" => "textarea", "table" => "true",
        "textarea_params" => " wrap=\"VIRTUAL\" cols=\"40\" rows=\"7\""],
    ["name" => "ennotes", "label" => "Notes", "type" => "textarea", "table" => "false",
        "textarea_params" => " wrap=\"VIRTUAL\" cols=\"40\" rows=\"7\""],
    ["name" => "frdescription", "label" => "French Description", "type" => "textarea", "table" => "true",
        "textarea_params" => " wrap=\"VIRTUAL\" cols=\"40\" rows=\"7\""],
    ["name" => "frnotes", "label" => "French Notes", "type" => "textarea", "table" => "false",
        "textarea_params" => " wrap=\"VIRTUAL\" cols=\"40\" rows=\"7\""],
    ["name" => "diagram_filename",
        "label" => "File
		    <ul>
				<li>The Filename should not exceed 50 chars</li>
				<li>The Pictures should not exceed 200k</li>
				<li>The Pictures should in JPG format</li>
				<li>The Pictures Formating shall be Portrait if possible</li>
				<li>The Pictures Height should be superior to its width</li>
				<li>The Pictures Width should not exceed 640px</li>
				<li>The Pictures Height should not exceed 820px</li>
				<li>The Chapters must be in PDF format</li>
				<li>The Chapters size should not exceed 18Mb</li>
			</ul>",
        "type" => "file_upload", "table" => "false", "file_upload_dir" => "manuals_diagrams"],
];

$manuals_parts_tpl = [
    ["table" => "manuals_parts", "form_type" => "manuals_parts_tpl",
        "title" => "Document parts",
        "cancel" => "record_view&form_type=main_tpl", "mode" => "table",
        "default_sort" => "item ASC",
    ],
    ["name" => "id", "label" => "Primary Key", "type" => "primary_key", "table" => "false"],
    ["name" => "parent_id", "label" => "Foreign Key", "type" => "foreign_key", "table" => "false"],
    ["name" => "item", "label" => "Item#", "type" => "text", "table" => "true"],
    ["name" => "pn", "label" => "PN", "type" => "text", "table" => "true"],
    ["name" => "qty", "label" => "Quantity", "type" => "text", "table" => "true"],
    ["name" => "um", "label" => "UM", "type" => "text", "table" => "true"],
    ["name" => "en", "label" => "English", "type" => "text", "table" => "true"],
    ["name" => "fr", "label" => "French", "type" => "text", "table" => "true"],
    ["name" => "note", "label" => "Notes", "type" => "text", "table" => "false"],
    ["name" => "group_p", "label" => "P Qty", "type" => "text", "table" => "true"],
    ["name" => "group_m", "label" => "M Qty", "type" => "text", "table" => "true"],
    ["name" => "group_o", "label" => "O Qty", "type" => "text", "table" => "true"],
    ["name" => "group_c", "label" => "C Qty", "type" => "text", "table" => "true"],
];


$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include("header.inc.php");
include("db_common2.inc.php");
if (!$id) {
    $id = 0;
}
//Include db functions
if ($user->isInGroup(["manuals", "gg_SUPPORT", "gg_ENG", "gg_ADMIN"])) {
    include("db_admin2.inc.php");
} else {
    include("db_readonly2.inc.php");
}

