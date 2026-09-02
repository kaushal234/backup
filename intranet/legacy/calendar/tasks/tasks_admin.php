<?php
include("common.inc.php");
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

if (!$user->isInGroup("superuser")) {
    echo "ERROR: You do not have permission to access this page.";
    exit;
}

$main_tpl = [
    ["table" => "tasks", "form_type" => "main_tpl", "title" => "Tasks",
        "cancel" => "table&form_type=main_tpl", "mode" => "record_view",
        "prn_table_menu" => "<a href=\"/en/private/calendar/calendar.php?m[0]=tasks&m[1]=taskList\">Back to Module</a>",
        "prn_record_menu" => "<a href=\"/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id={id}\">Back to Task</a>&nbsp;|&nbsp;",
        "child_tables" => ["tasks_comments"],
        "default_sort" => "id"],
    ["name" => "id", "label" => "Primary Key", "type" => "primary_key", "table" => "true"],
    ["name" => "parent_id", "label" => "Ref#", "type" => "text", "table" => "true"],
    ["name" => "module", "label" => "Module", "type" => "select", "table" => "true", "dump" => "true",
        "select_list" => array_keys(tldModule::getList('smartyOptionsModuleDesc'))],
    ["name" => "tplno", "label" => "Seq Template", "type" => "lookup", "table" => "true", "dump" => "true",
        "select_query" => "SELECT name,id FROM cal_seq_tpl ORDER BY name",
        "select_field_1" => "id", "select_field_2" => "name",
        "select_query_view" => "SELECT name,id FROM cal_seq_tpl WHERE id=",
        "select_field_view" => "name"],
    ["name" => "seq", "label" => "Seq?", "type" => "text", "table" => "false", "dump" => "true"],
    ["name" => "cur_step", "label" => "Seq Step", "type" => "text", "table" => "false", "dump" => "true"],
    ["name" => "status", "label" => "Status", "type" => "select", "table" => "true", "dump" => "true",
        "select_list" => ["OPEN", "CLOSED"]],
    ["name" => "ifactor", "label" => "Importance Factor", "type" => "select", "table" => "true", "dump" => "true",
        "select_list" => ["1" => "1", "10" => "10", "100" => "100", "1000" => "1000"],
        "source" => "smartyOptions"],
    ["name" => "date", "label" => "Date<br>(yyyy-mm-dd)", "type" => "auto_date", "table" => "true", "dump" => "true"],
    ["name" => "due_date", "label" => "Due Date (YYYY-MM-DD)", "type" => "auto_date", "table" => "true"],
    ["name" => "d_escal", "label" => "Date Escalated (YYYY-MM-DD)", "type" => "date", "table" => "true"],
    ["name" => "dt_closed", "label" => "Date Closed (YYYY-MM-DD)", "type" => "date", "table" => "true"],
    ["name" => "hours", "label" => "Hours", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "escalation_trigger", "label" => "Escalation_trigger(Days)", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "assignor", "label" => "Assignor", "type" => "select", "table" => "true", "dump" => "true",
        "select_list" => tldDirectory::getUserList("smartyOptions"), "source" => "smartyOptions"],
    ["name" => "assignee", "label" => "Assignee", "type" => "select", "table" => "true", "dump" => "true",
        "select_list" => tldDirectory::getUserList("smartyOptions"), "source" => "smartyOptions"],
    ["name" => "task", "label" => "Task", "type" => "textarea", "table" => "false",
        "textarea_params" => " wrap=\"VIRTUAL\" cols=\"40\" rows=\"7\""],
    ["name" => "cat", "label" => "Category", "type" => "select", "table" => "false", "dump" => "true",
        "select_list" => ["A", "B", "C"]],
    ["name" => "bu_id", "label" => "BU", "type" => "select", "table" => "false", "dump" => "true",
        "select_list" => tldLocation::getLocationList("smartyOptions"), "source" => "smartyOptions"],
];

$tasks_comments_tpl = [
    ["table" => "tasks_comments", "form_type" => "tasks_comments_tpl",
        "title" => "Tasks Comments", "cancel" => "record_view&form_type=main_tpl",
        "mode" => "form_view"],
    ["name" => "id", "label" => "Primary Key", "type" => "primary_key", "table" => "false"],
    ["name" => "parent_id", "label" => "Foreign Key", "type" => "foreign_key", "table" => "false"],
    ["name" => "date", "label" => "Date (yyyy-mm-dd)", "type" => "auto_date", "table" => "true", "dump" => "true"],
    ["name" => "status", "label" => "Status", "type" => "text", "table" => "true"],
    ["name" => "step", "label" => "Step", "type" => "text", "table" => "true"],
    ["name" => "poster", "label" => "Poster", "type" => "select", "table" => "true", "dump" => "true",
        "select_list" => tldDirectory::getUserList("smartyOptions"), "source" => "smartyOptions"],
    ["name" => "comment", "label" => "Comment", "type" => "textarea", "table" => "true",
        "textarea_params" => " wrap=\"VIRTUAL\" cols=\"50\" rows=\"7\""],
    ["name" => "filename", "label" => "Filename", "type" => "file_upload", "table" => "true",
        "file_upload_dir" => "tasks_comments"]
];

$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include_once("header.inc.php");

//Include common db functions
include("db_common2.inc.php");
if (!$id) $id = 0;
//Include db functions
include("db_admin2.inc.php");
?>
