<?php
include_once("common.inc.php");
include_once("product_support.inc.php");

$main_tpl=array(
  array("table"=>"eap","form_type"=>"main_tpl","title"=>"EAP Admin",
  "child_tables"=>array("eap_parts", ["mod_models" => ['where' => " AND module = 'EAP'"]]),
  "cancel"=>"table&form_type=","mode"=>"record_view",
  "prn_table_menu"=>"<a href=\"/en/private/manufacturing/eng/dev.php?m[0]=eap\">Back to EAP Module</a>&nbsp;|&nbsp;",
  "prn_record_menu"=>"<a href=\"/en/private/manufacturing/eng/dev.php?m[0]=eap&m[1]=view&id={id}\"><b>Back to EAP</b></a>&nbsp;|&nbsp;",
  "default_sort"=>"id"),
//start of table definitions
  array("name"=>"id",			"label"=>"ID&nbsp;####", 				"type"=>"primary_key",	"table"=>"true","dump"=>"true"),
  array("name"=>"parent_id",	"label"=>"Foreign Key",					"type"=>"foreign_key",	"table"=>"false"),
  array("name" => "parent_module", "label" => "Parent Module", "type" => "hidden"),
  array("name"=>"status",		"label"=>"Status",						"type"=>"locked_field",		"table"=>"true","dump"=>"true"),
  array("name"=>"dt_opened",	"label"=>"Date (YYYY-MM-DD)",		"type"=>"auto_date",	"table"=>"true","dump"=>"true"),
  array("name"=>"factory",	"label"=>"Factory",			"type"=>"lookup",	"table"=>"true","dump"=>"true",
		"select_query"=>"SELECT id,location FROM locations WHERE factory='Y' ORDER BY location",
		"select_field_1"=>"id","select_field_2"=>"location",
        "select_query_view"=>"SELECT * FROM locations WHERE id=",
		"select_field_view"=>"location"),
  array("name"=>"reporter",	"label"=>"Reporter",					"type"=>"select",	"table"=>"false","dump"=>"true",
  		"select_list"=>array(""=>"")+tldDirectory::getUserList("smartyOptions"), "source"=>"smartyOptions"),
  array("name"=>"assignee",	"label"=>"Engineer",					"type"=>"select",	"table"=>"false","dump"=>"true",
  		"select_list"=>array(""=>"")+tldDirectory::getUserList("smartyOptions"), "source"=>"smartyOptions"),
	array("name"=>"t_emno",	"label"=>"Reporting Person",		"type"=>"text",			"table"=>"false","dump"=>"true",
	"width"=>"30"),
//  array("name"=>"overnight",		"label"=>"Overnight?",		"type"=>"select",		"table"=>"true","dump"=>"true",
 // 		"select_list"=>array(	"Y", "N")
//		),
//	array("name"=>"pn",	"label"=>"Top PN",		"type"=>"text",			"table"=>"false","dump"=>"true",
//	"width"=>"30"),
  array("name"=>"reported_by",	"label"=>"Initiator",		"type"=>"text",		"table"=>"false","dump"=>"true",
	"width"=>"30"),
  array("name"=>"poster",	"label"=>"Poster",				"type"=>"select",	"table"=>"false","dump"=>"true",
	"select_list"=>array(""=>"")+tldDirectory::getUserList("smartyOptions"), "source"=>"smartyOptions"),
  array("name"=>"short_desc",	"label"=>"Short Description",		"type"=>"text",			"table"=>"true","dump"=>"true",
	"width"=>"100"),
  array("name"=>"description",	"label"=>"Description",		"type"=>"textarea",	"table"=>"false","dump"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"80\" rows=\"7\""),
  array("name"=>"filename",		"label"=>"Attachment",				"type"=>"file_upload",	"table"=>"false",
  		"file_upload_dir"=>"eap"),
  array("name"=>"category",		"label"=>"Category",		"type"=>"select",		"table"=>"false","dump"=>"true","dump"=>"true",
  		"select_list"=>array_values(tldList::optionsByListNameAsListItemListItem('list.eap.category'))
		),
  array("name"=>"ifactor",		"label"=>"IFactor",		"type"=>"select",		"table"=>"false","dump"=>"true","dump"=>"true",
  		"select_list"=>array(	"1", "10+")
		),
  array("name"=>"action_plan",	"label"=>"Action Plan",			"type"=>"textarea",		"table"=>"false","dump"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"80\" rows=\"7\""),
  array("name"=>"currency",		"label"=>"Currency",		"type"=>"select",		"table"=>"false","dump"=>"true","dump"=>"true",
  		"select_list"=>array("USD","EUR","RMB","TWD")
		),
  array("name"=>"cost",	"label"=>"Cost",		"type"=>"text",			"table"=>"true","dump"=>"true"),
  array("name"=>"expected_hours",	"label"=>"Expected Engineering Time",		"type"=>"integer",			"table"=>"true","dump"=>"true"),
  array("name"=>"info",	"label"=>"Critical Phase Change Closure",				"type"=>"select",	"table"=>"false","dump"=>"true",
  		"select_list"=>array(""=>"","Phase 1"=>"Phase 1","Phase 2"=>"Phase 2","Phase 3"=>"Phase 3","Phase 4"=>"Phase 4"), "source"=>"smartyOptions")
);

$eap_files_tpl=array(
  array("table"=>"eap_files","form_type"=>"eap_files_tpl",
  		"title"=>"Files",
  		"cancel"=>"record_view&form_type=main_tpl","mode"=>"table"
		),
  array("name"=>"id",			"label"=>"Primary Key",				"type"=>"primary_key",	"table"=>"false"),
  array("name"=>"parent_id",	"label"=>"Foreign Key",				"type"=>"foreign_key",	"table"=>"false"),
  array("name"=>"date",			"label"=>"Date (YYYY-MM-DD)",		"type"=>"auto_date",	"table"=>"true"),
  array("name"=>"short_desc",	"label"=>"Short Description",		"type"=>"text",			"table"=>"true",
  		"width"=>"25"
		),
  array("name"=>"long_desc",			"label"=>"Long Description",			"type"=>"textarea",		"table"=>"true","dump"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"80\" rows=\"7\""),
  array("name"=>"filename",		"label"=>"Filename",				"type"=>"file_upload",	"table"=>"true",
  		"file_upload_dir"=>"eap_files")
);

$eap_parts_tpl=array(
	array("table"=>"eap_parts","form_type"=>"eap_parts_tpl",
		"title"=>"Affected Parts","cancel"=>"record_view&form_type=main_tpl",
		"mode"=>"form_view"),
	array("name"=>"id",					"label"=>"Primary Key",			"type"=>"primary_key",	"table"=>"false"),
	array("name"=>"parent_id",			"label"=>"Foreign Key",			"type"=>"foreign_key",	"table"=>"false"),
	array("name"=>"pn",				"label"=>"PN",		"type"=>"text",			"table"=>"true",
	"width"=>"60"
	)
);

$eap_comments_tpl=array(
	array("table"=>"eap_comments","form_type"=>"eap_comments_tpl",
		"title"=>"Comments","cancel"=>"record_view&form_type=main_tpl",
		"mode"=>"form_view"),
	array("name"=>"id",				"label"=>"Primary Key",			"type"=>"primary_key",	"table"=>"false"),
	array("name"=>"parent_id",		"label"=>"Foreign Key",			"type"=>"foreign_key",	"table"=>"false"),
  array("name"=>"date",				"label"=>"Date (YYYY-MM-DD)",		"type"=>"auto_date",	"table"=>"true"),
  array("name"=>"comment",			"label"=>"Comment",			"type"=>"textarea",		"table"=>"true","dump"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"80\" rows=\"7\"")
);

$eap_proposals_tpl=array(
	array("table"=>"eap_proposals","form_type"=>"eap_proposals_tpl",
		"title"=>"Proposals","cancel"=>"record_view&form_type=main_tpl",
		"mode"=>"form_view"),
	array("name"=>"id",				"label"=>"Primary Key",			"type"=>"primary_key",	"table"=>"false"),
	array("name"=>"parent_id",		"label"=>"Foreign Key",			"type"=>"foreign_key",	"table"=>"false"),
  array("name"=>"dt_created",		"label"=>"Date (YYYY-MM-DD)",	"type"=>"auto_date",	"table"=>"true"),
  array("name"=>"action_plan",		"label"=>"Action Plan",			"type"=>"textarea",		"table"=>"true","dump"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"80\" rows=\"7\""),
  array("name"=>"filename",		"label"=>"Filename",				"type"=>"file_upload",	"table"=>"true",
  		"file_upload_dir"=>"eap_files"),
  array("name"=>"currency",		"label"=>"Currency",		"type"=>"select",		"table"=>"true","dump"=>"true",
  		"select_list"=>array("USD","EUR","RMB","TWD")
		),
	array("name"=>"cost",	"label"=>"Cost",		"type"=>"text",			"table"=>"true","dump"=>"true"),
);

$mod_models_tpl=array(
	array("table"=>"mod_models","form_type"=>"mod_models_tpl",
		"title"=>"Affected Model","cancel"=>"record_view&form_type=main_tpl",
		"mode"=>"table"),
	array("name"=>"id",			"label"=>"Primary Key",		"type"=>"primary_key",	"table"=>"true"),
	array("name"=>"module",		"label"=>"Module",			"type"=>"select",		"table"=>"true","dump"=>"true",
  		"select_list"=>array(	"EAP")
		),
	array("name"=>"parent_id",	"label"=>"Ref#",			"type"=>"foreign_key",			"table"=>"true" ,"dump"=>"true"),
	array("name"=>"type",		"label"=>"Type",			"type"=>"select",			"table"=>"true","dump"=>"true",
	    "select_list"=>tldType::getTypes("","smartyOptions"), "source"=>"smartyOptions"
	    ),
	array("name"=>"model",	"label"=>"Equipment Model",		"type"=>"select",			"table"=>"true","dump"=>"true",
  		"select_list" => tldModel::getList(), "source"=>"smartyOptions"
	),
);


$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include_once("header.inc.php");
include_once("db_common2.inc.php");
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

// Capture old record before update for audit logging in EAP log
$_eapAuditOldRecord = null;
if (($_REQUEST['mode'] ?? '') === 'update' && ($_REQUEST['form_type'] ?? '') === 'main_tpl'
    && !empty($_REQUEST['id'][0]) && is_numeric($_REQUEST['id'][0])) {
    $_eapAuditOldRecord = tldUtils::getSqlRowToAssocArray(
        "SELECT * FROM eap WHERE id=" . intval($_REQUEST['id'][0])
    );
}

if($user->isInGroup(array('gg_ENG',"eap","gg_ADMIN"))){
	include("db_admin2.inc.php");

    // After update: compare old vs new and log changes to EAP log
    if ($_eapAuditOldRecord !== null && !empty($_REQUEST['id'][0])) {
        $_eapAuditNewRecord = tldUtils::getSqlRowToAssocArray(
            "SELECT * FROM eap WHERE id=" . intval($_REQUEST['id'][0])
        );
        $_eapAuditFields = [
            'factory'        => 'Factory',
            'assignee'       => 'Engineer',
            'short_desc'     => 'Short Description',
            'description'    => 'Description',
            'category'       => 'Category',
            'ifactor'        => 'IFactor',
            'action_plan'    => 'Action Plan',
            'currency'       => 'Currency',
            'cost'           => 'Cost',
            'expected_hours' => 'Expected Engineering Time',
        ];
        $_eapAuditChanges = [];
        foreach ($_eapAuditFields as $_eapField => $_eapLabel) {
            $oldVal = htmlspecialchars(html_entity_decode(strip_tags((string)($_eapAuditOldRecord[$_eapField] ?? '')), ENT_QUOTES | ENT_HTML5, 'ISO-8859-1'), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'ISO-8859-1');
            $newVal = htmlspecialchars(html_entity_decode(strip_tags((string)($_eapAuditNewRecord[$_eapField] ?? '')), ENT_QUOTES | ENT_HTML5, 'ISO-8859-1'), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'ISO-8859-1');
            if ($oldVal !== $newVal) {
                $_eapAuditChanges[] = '<div style="margin-bottom:4px">'
                    . '<strong>' . $_eapLabel . '</strong><br>'
                    . '<div style="display:flex;gap:16px">'
                    . '<div style="flex:1;background:#fdd;padding:2px 4px">' . nl2br($oldVal) . '</div>'
                    . '<div style="flex:1;background:#dfd;padding:2px 4px">' . nl2br($newVal) . '</div>'
                    . '</div></div>';
            }
        }
        if (!empty($_eapAuditChanges)) {
            $_eapForLog = new tldEAP((int)$_REQUEST['id'][0]);
            $_eapForLog->addComment([
                'poster'  => $user->getID(),
                'comment' => TldDatabase::escape('<div><strong>Record edited</strong><br>' . implode('', $_eapAuditChanges) . '</div>'),
            ]);
        }
    }
}else{
	include("db_readonly2.inc.php");
}
?>
