<?php
include_once("common.inc.php");
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

$main_tpl = [
    ["table" => "cpa", "form_type" => "main_tpl", "title" => "Corrective/Preventive Actions (CPA)",
        "cancel" => "table&form_type=", "mode" => "record_view",
        // "child_tables"=>array("cpa_links","cpa_files"),
        "prn_table_menu" => "<a href=\"/en/private/manufacturing/qa/dev.php?m[0]=cpa\">Back to CPA Module</a>&nbsp;|&nbsp;",
        "prn_record_menu" => "<a href=\"/en/private/manufacturing/qa/dev.php?m[0]=cpa&m[1]=view&m[2]=&id={id}\"><b>Back to CPA</b></a>&nbsp;|&nbsp;",
        "email_title" => "CPA ID",
        "default_sort" => "id"],
//start of table definitions
    ["name" => "id", "label" => "ID&nbsp;####", "type" => "primary_key", "table" => "true", "dump" => "true"],
    ["name" => "parent_id", "label" => "Foreign Key", "type" => "foreign_key", "table" => "false"],
    ["name" => "status", "label" => "Status", "type" => "locked_field", "table" => "true", "dump" => "true"],
    ["name" => "proj_leader", "label" => "Project Leader", "type" => "select", "table" => "true", "dump" => "true",
        "select_list" => tldDirectory::getUserList("smartyOptions"), "source" => "smartyOptions"],
    ["name" => "ifactor", "label" => "IFactor", "type" => "select", "table" => "true", "dump" => "true",
        "select_list" => ["1", "10", "100", "1000"]
    ],
    ["name" => "dept", "label" => "Department", "type" => "select", "table" => "true", "dump" => "true",
        "select_list" => tldDepartment::getListAsDepartmentDepartment(), "source" => "smartyOptions"],
    ["name" => "poster", "label" => "Posted By", "type" => "select", "table" => "false", "dump" => "true",
        "select_list" => tldDirectory::getUserList("smartyOptions"), "source" => "smartyOptions"],
    ["name" => "initiator", "label" => "Initiator", "type" => "select", "table" => "false", "dump" => "true",
        "select_list" => tldDirectory::getUserList("smartyOptions"), "source" => "smartyOptions"],
    ["name" => "bu", "label" => "Business Unit", "type" => "lookup", "table" => "true", "dump" => "true",
        "select_query" => "SELECT id,location FROM locations WHERE factory='Y' OR hq='Y' ORDER BY location",
        "select_field_1" => "id", "select_field_2" => "location",
        "select_query_view" => "SELECT * FROM locations WHERE id=",
        "select_field_view" => "location"],
    ["name" => "type", "label" => "CPA Type", "type" => "select", "table" => "false", "dump" => "true",
        "source" => "lists", "list_name" => "list.cpa.type"
    ],
    ["name" => "date", "label" => "Date<br>(yyyy-mm-dd)", "type" => "auto_date", "table" => "true", "dump" => "true"],
    ["name" => "date_target", "label" => "Target Date<br>(yyyy-mm-dd)", "type" => "date", "table" => "true", "dump" => "true"],
    ["name" => "short_desc", "label" => "Short Description", "type" => "text", "table" => "true", "dump" => "true",
        "width" => "50"],
    ["name" => "description", "label" => "Description", "type" => "textarea", "table" => "false", "dump" => "true",
        "textarea_params" => " wrap=\"VIRTUAL\" cols=\"80\" rows=\"10\""],
    ["name" => "resolution", "label" => "Resolution", "type" => "textarea", "table" => "false", "dump" => "true",
        "textarea_params" => " wrap=\"VIRTUAL\" cols=\"80\" rows=\"10\""],
    ["name" => "rejection_reason", "label" => "Implementation Follow-up", "type" => "textarea", "table" => "false", "dump" => "true",
        "textarea_params" => " wrap=\"VIRTUAL\" cols=\"80\" rows=\"10\""],
    ["name" => "picture_filename", "label" => "Picture", "type" => "file_upload", "table" => "false",
        "file_upload_dir" => "cpa"]
];

$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include("header.inc.php");
include("db_common2.inc.php");
if (!$id)$id=0;

if($user->isInGroup(array("gg_ADMIN"))){
	include("db_admin2.inc.php");
}else{
	include("db_readonly2.inc.php");
}
?>