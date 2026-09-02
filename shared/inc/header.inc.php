<?php
session_start();
include_once('common.inc.php');

// Init variables

if(!isset($_SESSION['MY_SESS'])) {
    $_SESSION['MY_SESS'] = null;
}
$MY_SESS =& $_SESSION['MY_SESS'];
if(!isset($_SESSION['sess_error_message'])) {
    $_SESSION['sess_error_message'] = null;
}
$sess_error_message =& $_SESSION['sess_error_message'];

// Set default values
if(empty($form_type)) {
    $form_type = "main_tpl";
}

$running=${$form_type}[0]["table"];

if (empty($MY_SESS[$running]["sort_order"])) {
    $MY_SESS[$running]["sort_order"] = "DESC";
}
if (empty($MY_SESS[$running]["form_type"])) {
    $MY_SESS[$running]["form_type"] = "main_tpl";
}
if (empty($MY_SESS[$running]["sort"])) {
    $MY_SESS[$running]["sort"] = $main_tpl[0]["default_sort"];
}
if (empty($MY_SESS[$running]["offset"])) {
    $MY_SESS[$running]["offset"] = 0;
}

if (isset($sort)) {
    $MY_SESS[$running]["sort"] = $sort;
}
if (isset($offset)) {
    $MY_SESS[$running]["offset"] = $offset;
}
if (isset($sort_order)) {
    $MY_SESS[$running]["sort_order"] = $sort_order;
}
if (isset($q)) {
    $MY_SESS[$running]["q"] = stripslashes($q);
}
if (isset($id)) {
    $MY_SESS[$running]["id"] = $id;
}
if (isset($current_id)) {
    $MY_SESS[$running]["current_id"] = $current_id;
}
if (isset($parent_id)) {
    $MY_SESS[$running]["parent_id"] = $parent_id;
}
if (isset($form_type)) {
    $MY_SESS[$running]["form_type"] = $form_type;
}
if (isset($error_message)) {
    $MY_SESS[$running]["error_message"] = $error_message;
}
if (isset($adv_search_query)) {
    $MY_SESS[$running]["adv_search_query"] = $adv_search_query;
}

// Maximum rows in a table
$max_rows=15;
$form_array=${$MY_SESS[$running]["form_type"]};
