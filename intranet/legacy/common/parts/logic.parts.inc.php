<?php

use ApiBundle\Client;
use Symfony\Component\HttpClient\Exception\ClientException;

$DEFAULT_TITLE .= "\Parts";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=parts">Home</a>
EOF;

if($user->isInGroup("superuser")){
    $DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="parts/parts_admin.php">Maintain</a>
EOF;
}
$container = $kernel->getContainer();
$client = $container->get(Client::class);

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
        $DEFAULT_ERROR[] = "ERROR: Module $module unknow, please contact MIS";
        break;
    }
    
    // Get back link to the module
    $backLinkHtml = _getBackModuleLink($module, $parent_id);
    $body = $backLinkHtml;
    
    // Apply specific rules
    switch($module){
        case 'SB3':
            $sb = new tldSB3((int)$parent_id);
            if($sb->isPartsNotNeeded()){
                $DEFAULT_ERROR[] = "ERROR: You can not add parts to SB#$parent_id if no parts are needed !";
                break 2;
            }
            break;
    }
    
    // Get form
    $form = new HTML_QuickForm('frmNew', 'post');
    $form->addElement(  'hidden', 'm[0]', 'parts');
    $form->addElement(  'hidden', 'm[1]', 'add');
    $form->addElement(  'hidden', 'module', $module);
    $form->addElement(  'hidden', 'parent_id', $parent_id);
    $form->addElement(  'header', 'title', "Add new $module Parts");
    include('parts.fields.form.inc.php');
    // Rules
    foreach($requiredFields as $requiredField){
        $form->addRule($requiredField, "Required", "required");
    }
    $form->addElement(  'submit', 'btnSubmit', 'Submit');
    
    if(!$form->validate()){
        $body.= $form->toHTML();
        break;
    }
    
    $vars = tldUtils::cleanupFormInput($form->exportValues());
    // Specific treatment
    switch($module){
    case 'PDC':
    case 'SCAR':
        // look for EDM description
        try {
            $pn = $vars['pn'];
            $bom = $client->get(
                sprintf('/ion/bill-of-materials/intranet_views/site=;project=;product=%s', $pn),
                ['query' => ['depth' => 0]]
            );
            if (empty($bom['engineeringDescription'])) {
                $DEFAULT_ERROR[] = "EDM description not found for PN '{$vars['pn']}'";
                break;
            }
            $vars['dsc'] = TldDatabase::escape($bom['engineeringDescription']);
        } catch (ClientException $exception) {
            $DEFAULT_ERROR[] = "ERROR: BOM not found.";
            break;
        }
    break;
    }
    // Insert Parts entry
    $e = tldModParts::insert($vars);
    if(is_string($e)){
        $DEFAULT_ERROR[] = "INTERNAL ERROR: Can not add new Parts entry. Reason: $e";
        break;
    }
    $body = <<<EOF
<p>Parts added successfully!</p>
<p>Add new Parts for $module #$parent_id? <a href="$php_self?m[0]=parts&m[1]=add&module=$module&parent_id=$parent_id">Yes, add more parts...</a></p>
$backLinkHtml
EOF;
break;
case 'view':
    if(empty($id) || !is_numeric($id)){
        $DEFAULT_ERROR[] = "ERROR: Parameters sent empty or invalid";
        break;
    }
    $part = new tldModParts($id);
    if($part->isEmpty()){
        $DEFAULT_ERROR[] = "ERROR: Parts not found";
        break;
    }
    $module = $part->getModule();
    $parent_id = $part->getParentID();
    $backLinkHtml = _getBackModuleLink($module, $parent_id);
    
    $DEFAULT_TITLE .= "\Parts#$id";
    $DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=parts&m[1]=view&id=$id">Home</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=parts&m[1]=view&m[2]=edit&id=$id">Edit</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=parts&m[1]=view&m[2]=delete&id=$id">Delete</a>
EOF;

    if($user->isInGroup("superuser")){
        $DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="parts/parts_admin.php?mode=record_view&form_type=main_tpl&id=$id">Admin</a>
EOF;
}
    
    switch($m[2]){
    case 'edit':
        // Get form
        $form = new HTML_QuickForm('frmNew', 'post');
        $form->addElement(  'hidden', 'm[0]', 'parts');
        $form->addElement(  'hidden', 'm[1]', 'view');
        $form->addElement(  'hidden', 'm[2]', 'edit');
        $form->addElement(  'hidden', 'id', $id);
        $form->addElement(  'header', 'title', "Edit Part#$id");
        include('parts.fields.form.inc.php');
        // Rules
        foreach($requiredFields as $requiredField){
            $form->addRule($requiredField, "Required", "required");
        }
        $form->addElement(  'submit', 'btnSubmit', 'Submit');
        $form->setDefaults($part->itsHeader);
        
        if(!$form->validate()){
            $body .= $form->toHTML();
            break;
        }
        
        $vars = tldUtils::cleanupFormInput($form->exportValues());
        // Specific treatment
        switch($module){
        case 'PDC':
        case 'SCAR':
            // look for EDM description
            try {
                $pn = $vars['pn'];
                $bom = $client->get(
                    sprintf('/ion/bill-of-materials/intranet_views/site=;project=;product=%s', $pn),
                    ['query' => ['depth' => 0]]
                );
                if (empty($bom['engineeringDescription'])) {
                    $DEFAULT_ERROR[] = "EDM description not found for PN '{$vars['pn']}'";
                    break;
                }
                $vars['dsc'] = TldDatabase::escape($bom['engineeringDescription']);
            } catch (ClientException $exception) {
                $DEFAULT_ERROR[] = "ERROR: BOM not found.";
                break;
            }
        break;
        }
        // Update Parts entry
        $e = $part->update(
            $vars,
            array('pn','dsc','qty')
        );
        if(is_string($e)){
            $DEFAULT_ERROR[] = "INTERNAL ERROR: Can not update Part entry. Reason: $e";
            break;
        }
        $backLinkHtml = _getBackModuleLink($module, $parent_id);
        $body = <<<EOF
<p>Part updated successfully!</p>
$backLinkHtml
EOF;
    break;
    case 'delete':
        // Get form
        $form = new HTML_QuickForm('frmNew', 'post');
        $form->addElement(  'hidden', 'm[0]', 'parts');
        $form->addElement(  'hidden', 'm[1]', 'view');
        $form->addElement(  'hidden', 'm[2]', 'delete');
        $form->addElement(  'hidden', 'id', $id);
        $form->addElement(  'header', 'title', "Confirm to delete Part#$id");
        $form->addElement(  'select', 'conf', 'Are you sure?', array(''=>'','Y'=>'Yes'));
        $form->addRule('conf', "Required", "required");
        $form->addElement(  'submit', 'btnSubmit', 'Submit');
        
        if(!$form->validate() && $_REQUEST['conf']!='Y'){
            $body .= $form->toHTML();
            break;
        }
        
        $e = $part->delete($id);
        if(is_string($e)){
            $DEFAULT_ERROR[] = "INTERNAL ERROR: Can not delete Part entry. Reason: $e";
            break;
        }
        $body = <<<EOF
<p>Part deleted successfully!</p>
$backLinkHtml
EOF;
    break;
    default:
        $report = new tldAssocTable(
            $part->itsHeader,
            array(
                "id"           =>"ID#",
                "parent_id"    =>"Ref#",
                "module"       =>"Module",
                "pn"           =>"Part Number",
                "dsc"          =>"Description",
                "qty"          =>"Quantity"
            ),
            array("title"=>"Part#$id")
        );
        $body = $backLinkHtml.$report->fetch();
    break;
    }
break;
default:
    $body = <<<EOF
<h3>Module Parts</h3>
<p>Welcome to common module Parts</p>
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