<?php
$DEFAULT_TITLE .= "\FAQ";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=faq">Home</a>
EOF;

if($user->isInGroup("superuser")){
    $DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="faq/faq_admin.php">Maintain</a>
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
    if(!_isModuleValid($module)){
        $DEFAULT_ERROR[] = "ERROR: Module $module not setup to use mod FAQ, please contact MIS";
        break;
    }
    // Get back link to the module
    $backLinkHtml = _getBackModuleLink($module, $parent_id);
    $body = $backLinkHtml;
    
    // Get form
    $form = new HTML_QuickForm('frmNew', 'post');
    $form->addElement(  'hidden', 'm[0]', 'faq');
    $form->addElement(  'hidden', 'm[1]', 'add');
    $form->addElement(  'hidden', 'module', $module);
    $form->addElement(  'hidden', 'parent_id', $parent_id);
    $form->addElement(  'header', 'title', "Add new $module FAQ");
    include('faq.fields.form.inc.php');
    // Rules
    $requiredFields = array('symptom','problem','solution');
    foreach($requiredFields as $requiredField){
        $form->addRule($requiredField, "Required", "required");
    }
    $form->addElement(  'submit', 'btnSubmit', 'Submit');
    
    if(!$form->validate()){
        $body .= $form->toHTML();
        break;
    }
    
    $vars = tldUtils::cleanupFormInput($form->exportValues());
    // Insert FAQ entry
    $e = tldModFAQ::insert($vars);
    if(is_string($e)){
        $DEFAULT_ERROR[] = "INTERNAL ERROR: Can not add new FAQ entry. Reason: $e";
        break;
    }
    $body = <<<EOF
<p>FAQ added successfully!</p>
<p>Add new FAQ for $module #$parent_id? <a href="$php_self?m[0]=faq&m[1]=add&module=$module&parent_id=$parent_id">Yes, add new FAQ...</a></p>
$backLinkHtml
EOF;
break;
case 'view':
    if(empty($id) || !is_numeric($id)){
        $DEFAULT_ERROR[] = "ERROR: Parameters sent empty or invalid";
        break;
    }
    $faq = new tldModFAQ($id);
    if($faq->isEmpty()){
        $DEFAULT_ERROR[] = "ERROR: FAQ not found";
        break;
    }
    $module = $faq->getModule();
    $parent_id = $faq->getParentID();
    $backLinkHtml = _getBackModuleLink($module, $parent_id);
    
    $DEFAULT_TITLE .= "\FAQ#$id";
    $DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=faq&m[1]=view&id=$id">Home</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=faq&m[1]=view&m[2]=edit&id=$id">Edit</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=faq&m[1]=view&m[2]=delete&id=$id">Delete</a>
EOF;

    if($user->isInGroup("superuser")){
        $DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="faq/faq_admin.php?mode=record_view&form_type=main_tpl&id=$id">Admin</a>
EOF;
}
    
    switch($m[2]){
    case 'edit':
        // Get form
        $form = new HTML_QuickForm('frmNew', 'post');
        $form->addElement(  'hidden', 'm[0]', 'faq');
        $form->addElement(  'hidden', 'm[1]', 'view');
        $form->addElement(  'hidden', 'm[2]', 'edit');
        $form->addElement(  'hidden', 'id', $id);
        $form->addElement(  'header', 'title', "Edit FAQ#$id");
        include('faq.fields.form.inc.php');
        // Rules
        $requiredFields = array('symptom','problem','solution');
        foreach($requiredFields as $requiredField){
            $form->addRule($requiredField, "Required", "required");
        }
        $form->addElement(  'submit', 'btnSubmit', 'Submit');
        $form->setDefaults($faq->itsHeader);
        
        if(!$form->validate()){
            $body .= $form->toHTML();
            break;
        }
        
        $vars = tldUtils::cleanupFormInput($form->exportValues());
        // Update FAQ entry
        $e = $faq->update(
            $vars,
            array("symptom","problem","solution")
        );
        if(is_string($e)){
            $DEFAULT_ERROR[] = "INTERNAL ERROR: Can not update FAQ entry. Reason: $e";
            break;
        }
        $backLinkHtml = _getBackModuleLink($module, $parent_id);
        $body = <<<EOF
<p>FAQ updated successfully!</p>
$backLinkHtml
EOF;
    break;
    case 'delete':
        // Get form
        $form = new HTML_QuickForm('frmNew', 'post');
        $form->addElement(  'hidden', 'm[0]', 'faq');
        $form->addElement(  'hidden', 'm[1]', 'view');
        $form->addElement(  'hidden', 'm[2]', 'delete');
        $form->addElement(  'hidden', 'id', $id);
        $form->addElement(  'header', 'title', "Confirm to delete FAQ#$id");
        $form->addElement(  'select', 'conf', 'Are you sure?', array(''=>'','Y'=>'Yes'));
        $form->addRule('conf', "Required", "required");
        $form->addElement(  'submit', 'btnSubmit', 'Submit');
        
        if(!$form->validate()){
            $body .= $form->toHTML();
            break;
        }
        
        $e = $faq->delete();
        if(is_string($e)){
            $DEFAULT_ERROR[] = "INTERNAL ERROR: Can not delete FAQ entry. Reason: $e";
            break;
        }
        $body = <<<EOF
<p>FAQ deleted successfully!</p>
$backLinkHtml
EOF;
    break;
    default:
        $report = new tldAssocTable(
            $faq->itsHeader,
            array(
                "id"           =>"ID#",
                "parent_id"    =>"Ref#",
                "module"       =>"Module",
                "symptom"      =>"Symptom",
                "problem"      =>"Problem",
                "solution"     =>"Solution"
            ),
            array("title"=>"FAQ #$id")
        );
        $body = $backLinkHtml.$report->fetch();
    break;
    }
break;
default:
    $body = <<<EOF
<h3>Module FAQ</h3>
<p>Welcome to common module FAQ</p>
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