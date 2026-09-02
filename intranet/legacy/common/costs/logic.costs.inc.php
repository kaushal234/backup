<?php

require_once __DIR__.'/../../legacy_autoload.php';

use Legacy\CostHandler;

$DEFAULT_TITLE .= "\Costs";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=costs">Home</a>
EOF;

if($user->isInGroup("superuser")){
    $DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="costs/costs_type_admin.php">Maintain Cost Type</a>
&nbsp;|&nbsp;<a href="costs/costs_admin.php">Maintain</a>
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
    // Check module conf
    if(!tldModCost::isModuleValid($module)){
        $DEFAULT_ERROR[] = "ERROR: Module $module not setup to use mod cost, please contact MIS";
        break;
    }
    // Get back link to the module
    $backLinkHtml = _getBackModuleLink($module, $parent_id);
    $body = $backLinkHtml;
    // Check permissions
    if(!tldModCost::isAllowed($user, $module, "add")){
        $DEFAULT_ERROR[] = "ERROR: You are not allowed to create costs entries";
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
    $form->addElement(  'hidden', 'm[0]', 'costs');
    $form->addElement(  'hidden', 'm[1]', 'add');
    $form->addElement(  'hidden', 'module', $module);
    $form->addElement(  'hidden', 'parent_id', $parent_id);
    $form->addElement(  'header', 'title', "Add new $module cost");
    include('costs.fields.form.inc.php');
    $form->setDefaults(array(
    	"uid"=>$user->getID(),
        'date'=>date('Y-m-d')
    ));

    if(!$form->validate()){
        $body .= $form->toHTML();
        break;
    }

    $vars = tldUtils::cleanupFormInput($form->exportValues());
    $vars['poster']=$user->getID();
    // Insert cost entry
    $e = tldModCost::insert($vars);
    if(is_string($e)){
        $DEFAULT_ERROR[] = "INTERNAL ERROR: Can not add new cost entry. Reason: $e";
        break;
    }
    // Display result
    $body = <<<EOF
<p>Cost added successfully!</p>
<p>Add new cost for $module #$parent_id? <a href="$php_self?m[0]=costs&m[1]=add&module=$module&parent_id=$parent_id">Yes, add new cost...</a></p>
$backLinkHtml
EOF;
break;
case 'view':
    if(empty($id) || !is_numeric($id)){
        $DEFAULT_ERROR[] = "ERROR: Parameters sent empty or invalid";
        break;
    }
    $cost = new tldModCost($id);
    if($cost->isEmpty()){
        $DEFAULT_ERROR[] = "ERROR: Cost not found";
        break;
    }
    $DEFAULT_TITLE .= "\Cost#$id";
    $DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=costs&m[1]=view&id=$id">Home</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=costs&m[1]=view&m[2]=edit&id=$id">Edit</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=costs&m[1]=view&m[2]=delete&m[3]=confirmation&id=$id">Delete</a>
EOF;

    if($user->isInGroup("superuser")){
        $DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="costs/costs_admin.php?mode=record_view&form_type=main_tpl&id=$id">Admin</a>
EOF;
}
    $module = $cost->getModule();
    $parent_id = $cost->getParentID();
    $backLinkHtml = _getBackModuleLink($module, $parent_id);
    // Add the link to go back
    $body = $backLinkHtml;
    // Check module conf
    if(!tldModCost::isModuleValid($module)){
        $DEFAULT_ERROR[] = "ERROR: Module $module not setup to use mod cost, please contact MIS";
        break;
    }
    // Currency handling
    $curList = tldForex::getCurrencyList();
    if(!empty($_GET['dcur']) && in_array($_GET['dcur'],$curList)){
        $sess['dcur']=$_GET['dcur'];
    }
    if(empty($sess['dcur'])){
        $sess['dcur']='USD';
    }
    // Check permissions
    if(!tldModCost::isAllowed($user, $module, "view")){
        $DEFAULT_ERROR[] = "ERROR: You are not allowed to view cost entry for this module";
        break;
    }

    switch($m[2]){
    case 'edit':
        if(!tldModCost::isAllowed($user, $module, "edit")){
            $DEFAULT_ERROR[] = "ERROR: You are not allowed to edit cost entry for this module";
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
        $form->addElement(  'hidden', 'm[0]', 'costs');
        $form->addElement(  'hidden', 'm[1]', 'view');
        $form->addElement(  'hidden', 'm[2]', 'edit');
        $form->addElement(  'hidden', 'id', $id);
        $form->addElement(  'header', 'title', "Edit cost#$id");
        include('costs.fields.form.inc.php');
        $form->setDefaults($cost->itsHeader);

        if(!$form->validate()){
            $body.= $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        // Update cost entry
        $e = $cost->update(
            $vars,
            array("type","description","um","qty","cur","price","uid","date")
        );
        if(is_string($e)){
            $DEFAULT_ERROR[] = "INTERNAL ERROR: Can not update cost entry. Reason: $e";
            break;
        }
        $backLinkHtml = _getBackModuleLink($module, $parent_id);
        $body = <<<EOF
<p>Cost updated successfully!</p>
$backLinkHtml
EOF;
    break;
    case 'delete':
        if(!tldModCost::isAllowed($user, $module, "delete")){
            $DEFAULT_ERROR[] = "ERROR: You are not allowed to delete cost entry for this module";
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
            $form->addElement(  'hidden', 'm[0]', 'costs');
            $form->addElement(  'hidden', 'm[1]', 'view');
            $form->addElement(  'hidden', 'm[2]', 'delete');
            $form->addElement(  'hidden', 'id', $id);
            $form->addElement(  'header', 'title', "Confirm to delete cost#$id");
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

        // Delete the cost entry
        $e = $cost->delete();
        if(is_string($e)){
            $DEFAULT_ERROR[] = "INTERNAL ERROR: Can not delete cost entry. Reason: $e";
            break;
        }
        $body = <<<EOF
<p>Cost deleted successfully!</p>
$backLinkHtml
EOF;
    break;
    default:
        // Currency selection
        $form = new HTML_QuickForm('frmNew', 'get');
        $form->addElement(  'hidden', 'm[0]', 'costs');
        $form->addElement(  'hidden', 'm[1]', 'view');
        $form->addElement(  'hidden', 'id', $id);
        $form->addElement(  'select', 'dcur', 'Change currency',
            array(''=>'')+tldForex::getCurrencyList(), array("onChange"=>"javascript:this.form.submit();"));
        $body .= $form->toHTML();
        // Cost view
        $report = new tldAssocTable(
            $cost->getHeader($sess['dcur']),
            array(
                "id"                =>"ID#",
                "date_open"         =>"Date creation",
                "poster_fullname"   =>"Poster",
                "parent_id"         =>"Ref#",
                "module"            =>"Module",
                "user_fullname"     =>"User concerned",
                "date"              =>"Date",
                "type"              =>"Type",
                "description"       =>"Description",
                //"um"                =>"UM",
                //"qty"               =>"Quantity",
                "cur"               =>"Currency",
                "price"             =>"Price",
                "dcur"		        =>"Default currency",
                "price_dcur"		=>"Price in Default currency",
            ),
            array("title"=>"Cost #$id")
        );
        $body.= $report->fetch();
    break;
    }
break;
default:
    $body = <<<EOF
<h3>Module Costs</h3>
<p>Welcome to common module costs</p>
EOF;
break;
}

function _isModuleValid($module){
    return in_array($module,array_keys(tldUtils::getModLinks()));
}

function _getBackModuleLink($module, $parent_id)
{
    $costHandler = new CostHandler();
    $parent_id = $costHandler->getLegacyId($module, $parent_id);

    $url = tldModLink::getURL($module, $parent_id);
    return "<p><a href=\"$url\">Click here to go back to $module #$parent_id</a></p>";
}

?>
