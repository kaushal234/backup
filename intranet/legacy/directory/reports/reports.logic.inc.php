<?php
$DEFAULT_TITLE .= "\Reports";
$DEFAULT_MENU.= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=reports">Home</a>
EOF;

switch($m[1]){
    case 'listing':
        switch($m[2]){
            case 'userCleanUp':
                // Do query constraints
                // Get all active accounts
                if(!empty($ch_act)) {
                    $WHERE = "people.disabled = 'N' and people.hidden = 0";
                } else {
                    $WHERE = "'acl_auth_INTRANET' IN(SELECT group_name FROM people_groups WHERE parent_id=people.id)";
                }
                // Additional constraints
                switch($m[3]){
                    case 'division':
                        $WHERE.=" AND div_id=0";
                        break;
                    case 'bu':
                        $WHERE.=" AND (bu_id=0 OR locations.region_id IS NULL)";
                        break;
                    case 'department':
                        $WHERE.=" AND dpt_id=0";
                        break;
                    case 'function':
                        $WHERE.=" AND fct_id=0";
                        break;
                    case 'full':
                        $div_id = TldDatabase::escape($_REQUEST['div_id']);
                        $bu_id = TldDatabase::escape($_REQUEST['bu_id']);
                        $dpt_id = TldDatabase::escape($_REQUEST['dpt_id']);
                        $fct_id = TldDatabase::escape($_REQUEST['fct_id']);
                        if(empty($div_id) && empty($bu_id) && empty($dpt_id) && empty($fct_id)){
                            header("Location: $php_self?m[0]=reports&m[1]=full_search&error=error");
                            exit;
                        }
                        if(!empty($div_id)) $WHERE.=" AND div_id=$div_id";
                        if(!empty($bu_id))  $WHERE.=" AND bu_id=$bu_id";
                        if(!empty($dpt_id)) $WHERE.=" AND dpt_id=$dpt_id";
                        if(!empty($fct_id)) $WHERE.=" AND fct_id=$fct_id";
                        break;
                }
                // Get data
                $query = <<<EOF
SELECT
    people.*,
    CONCAT(lastname,', ',firstname, ' (', people.email, ')') AS fullname,
    region.division,
    locations.business_unit AS bu,
    dpt.dpt AS department,
    fct.dsc AS function_dsc
FROM people
    LEFT JOIN tld_functions AS fct ON fct.id=people.fct_id
    LEFT JOIN tld_departments AS dpt ON dpt.id=people.dpt_id
    LEFT JOIN tld_regions AS region ON region.id=people.div_id
    LEFT JOIN locations ON locations.id=people.bu_id
WHERE
    $WHERE
ORDER BY
    fullname
EOF;
                $userList =  tldUtils::getSqlToAssocArray($query);
                // User list report result
                $report = new tldReportColumnar(
                    $userList,
                    array(
                        "xItems"=>array(
                            "id"=>"Edit",
                            "fullname"=>"Fullname",
                            "email"=>"Email",
                            "division"=>"Division",
                            "bu"=>"Business Unit",
                            "department"=>"Department",
                            "function_dsc"=>"TLD function"
                        ),
                        "title"=>"Account list",
                        "links"=>array(
                            "id"=>"/en/private/directory/index.php?m[0]=people&m[1]=view&id="
                        )
                    )
                );
                if($m[3]=="full"){

                    // User list report result
                    $report = new tldReportColumnar(
                        $userList,
                        array(
                            "xItems"=>array(
                                "id"=>"Edit",
                                "fullname"=>"Fullname",
                                "email"=>"Email",
                                "division"=>"Division",
                                "bu"=>"Business Unit",
                                "department"=>"Department",
                                "function_dsc"=>"TLD function",
                                "login"=>"Last Login"
                            ),
                            "title"=>"Account list",
                            "links"=>array(
                                "id"=>"/en/private/directory/index.php?m[0]=people&m[1]=view&id="
                            )
                        )
                    );
                }
                $body = $report->fetch();
                // Get email list
                $e = array();
                foreach($userList AS $ul){
                    if($ul['email']) $e[] = $ul['email'];
                }
                $body .= "<h3>Email List:</h3>".implode('; ',$e);
                break;
        }
        break;
    case 'full_search':
        if($error) $DEFAULT_ERROR[] = "ERROR: You need to setup at least one field";
        // Listing
        $divList = tldRegion::getListAsIdDivision();
        $buList = tldLocation::getBuListAsIdBU();
        $dptList = tldDepartment::getListAsIdDepartment();
        $fctList = tldFunction::getListAsIdDescription();
        $form = new HTML_QuickForm('frmPeople', 'post');
        $form->addElement(	'header', 'title', "Search people by:");
        $form->addElement(	'hidden', 'm[0]', 'reports');
        $form->addElement(	'hidden', 'm[1]', 'listing');
        $form->addElement(	'hidden', 'm[2]', 'userCleanUp');
        $form->addElement(	'hidden', 'm[3]', 'full');
        $form->addElement(	'static', null, null,"Please select at least one criteria before submitting");
        $form->addElement(	'select', 'div_id', 'Division', 	array(""=>"")+$divList);
        $form->addElement(	'select', 'bu_id',  'BU', 			array(""=>"")+$buList);
        $form->addElement(	'select', 'dpt_id', 'Department',	array(""=>"")+$dptList);
        $form->addElement(	'select', 'fct_id', 'Function', 	array(""=>"")+$fctList);
        $form->addElement('checkbox', 'ch_act', 'Show all active people ?');
        $form->addElement(	'submit', 'btnSubmit', 'Submit');

        if(!$form->validate()){
            $body = $form->toHTML();
            break;
        }
        break;
    default:
        $body = $smarty->fetch("directory/reports/homepage.reports.tpl");
        break;
}


?>
