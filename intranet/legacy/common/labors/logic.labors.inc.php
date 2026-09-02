<?php
$DEFAULT_TITLE .= "\Labors";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=labors">Home</a>
EOF;

if($user->isInGroup("superuser")){
    $DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="labors/labors_admin.php">Maintain</a>
EOF;
}

switch($m[1]){
case 'add':
    $DEFAULT_TITLE .= "\Add";
    $module = strtoupper($_REQUEST['module']);
    $parent_id = $_REQUEST['parent_id'];
    if(empty($module) || !is_numeric($parent_id)){
        $DEFAULT_ERROR[] = "ERROR: Parameters sent empty or invalid";
        break;
    }
    // Get back link to the module
    $backLinkHtml = _getBackModuleLink($module, $parent_id);
    $body = $backLinkHtml;
    // Check permissions
    if(!tldModLabor::isAllowed($user, $module, "add")){
        $DEFAULT_ERROR[] = "ERROR: You are not allowed to create labors entries";
        break;
    }

    // Additional rules per module?
    switch($module){
    default:
        // no specific rules
    break;
    }

    // Get form
    $form = new HTML_QuickForm('frmNew', 'post');
    $form->addElement(  'hidden', 'm[0]', 'labors');
    $form->addElement(  'hidden', 'm[1]', 'add');
    $form->addElement(  'hidden', 'module', $module);
    $form->addElement(  'hidden', 'parent_id', $parent_id);
    $form->addElement(  'header', 'title', "Add new $module labor");
    include('labors.fields.form.inc.php');
    $form->setDefaults(array(
    	"user_id"=>$user->getID(),
        'dt_work'=>date('Y-m-d')
    ));

    if(!$form->validate()){
        $body .= $form->toHTML();
        break;
    }

    $vars = tldUtils::cleanupFormInput($form->exportValues());
    $vars['poster_id']=$user->getID();
    // Insert labor entry
    $e = tldModLabor::insert($vars);
    if(is_string($e)){
        $DEFAULT_ERROR[] = "INTERNAL ERROR: Can not add new labor entry. Reason: $e";
        break;
    }
    // Display result
    $body = <<<EOF
<p>Labor added successfully!</p>
<p>Add new labor for $module #$parent_id? <a href="$php_self?m[0]=labors&m[1]=add&module=$module&parent_id=$parent_id">Yes, add new labor...</a></p>
$backLinkHtml
EOF;
break;
case 'view':
    if(empty($id) || !is_numeric($id)){
        $DEFAULT_ERROR[] = "ERROR: Parameters sent empty or invalid";
        break;
    }
    $labor = new tldModLabor($id);
    if($labor->isEmpty()){
        $DEFAULT_ERROR[] = "ERROR: Labor not found";
        break;
    }
    $DEFAULT_TITLE .= "\Labor#$id";
    $DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=labors&m[1]=view&id=$id">Home</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=labors&m[1]=view&m[2]=edit&id=$id">Edit</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=labors&m[1]=view&m[2]=delete&m[3]=confirmation&id=$id">Delete</a>
EOF;

    if($user->isInGroup("superuser")){
        $DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="labors/labors_admin.php?mode=record_view&form_type=main_tpl&id=$id">Admin</a>
EOF;
}
    $module = $labor->getModule();
    $parent_id = $labor->getParentID();
    $backLinkHtml = _getBackModuleLink($module, $parent_id);
    // Add the link to go back
    $body = $backLinkHtml;
    // Check permissions
    if(!tldModLabor::isAllowed($user, $module, "view")){
        $DEFAULT_ERROR[] = "ERROR: You are not allowed to view labor entry for this module";
        break;
    }

    switch($m[2]){
    case 'edit':
        if(!tldModLabor::isAllowed($user, $module, "edit")){
            $DEFAULT_ERROR[] = "ERROR: You are not allowed to edit labor entry for this module";
            break;
        }
        // Additional rules per module?
        switch($module){
        default:
            // no specific rules
        break;
        }

        // Get form
        $form = new HTML_QuickForm('frmNew', 'post');
        $form->addElement(  'hidden', 'm[0]', 'labors');
        $form->addElement(  'hidden', 'm[1]', 'view');
        $form->addElement(  'hidden', 'm[2]', 'edit');
        $form->addElement(  'hidden', 'id', $id);
        $form->addElement(  'header', 'title', "Edit labor#$id");
        include('labors.fields.form.inc.php');
        $form->setDefaults($labor->itsHeader);

        if(!$form->validate()){
            $body.= $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        // Update labor entry
        $e = $labor->update(
            $vars,
            array('user_id','dt_work','description','nb_hours')
        );
        if(is_string($e)){
            $DEFAULT_ERROR[] = "INTERNAL ERROR: Can not update labor entry. Reason: $e";
            break;
        }
        $backLinkHtml = _getBackModuleLink($module, $parent_id);
        $body = <<<EOF
<p>Labor updated successfully!</p>
$backLinkHtml
EOF;
    break;
    case 'delete':
        if(!tldModLabor::isAllowed($user, $module, "delete")){
            $DEFAULT_ERROR[] = "ERROR: You are not allowed to delete labor entry for this module";
            break;
        }
        // Additional rules per module?
        switch($module){
        default:
            // no specific rules
        break;
        }

        switch($m[3]){
        case 'confirmation':
            // Get form
            $form = new HTML_QuickForm('frmNew', 'post');
            $form->addElement(  'hidden', 'm[0]', 'labors');
            $form->addElement(  'hidden', 'm[1]', 'view');
            $form->addElement(  'hidden', 'm[2]', 'delete');
            $form->addElement(  'hidden', 'id', $id);
            $form->addElement(  'header', 'title', "Confirm to delete labor#$id");
            $form->addElement(  'select', 'conf', 'Are you sure?', array(''=>'','Y'=>'Yes'));
            $form->addRule('conf', "Required", "required");
            $form->addElement(  'submit', 'btnSubmit', 'Submit');

            if(!$form->validate()){
                $body .= $form->toHTML();
                break 2;
            }
        break;
        default:
            // No confirmation by default
        break;
        }

        // Delete the labor entry
        $e = $labor->delete();
        if(is_string($e)){
            $DEFAULT_ERROR[] = "INTERNAL ERROR: Can not delete labor entry. Reason: $e";
            break;
        }
        $body = <<<EOF
<p>Labor deleted successfully!</p>
$backLinkHtml
EOF;
    break;
    default:
        // Labor view
        $report = new tldAssocTable(
            $labor->getHeader(),
            array(
                "id"                =>"ID#",
                "module"			=>"Module",
                "parent_id"			=>"Module ref#",
            	"poster_fullname"	=>"Poster",
                "dt_open"           =>"Date creation",
                "user_fullname"	    =>"User concerned",
                "dt_work"           =>"Work Date",
                "description"		=>"Description",
                "nb_hours"			=>"Nb Hours"
            ),
            array("title"=>"Labor #$id")
        );
        $body.= $report->fetch();
    break;
    }
break;
default:
    $body = <<<EOF
<h3>Module Labors</h3>
<p>Welcome to common module labors</p>
EOF;
break;
}

function _isModuleValid($module){
    return in_array($module,array_keys(tldUtils::getModLinks()));
}

function _getBackModuleLink($module, $parent_id){
    $url = tldModLink::getURL($module, $parent_id);
    return "<p><a href=\"$url\">Click here to go back to $module #$parent_id</a></p>";
}

?>
