<?php
ob_start();
?>

<!-- JS TOOLS -->

<script src="/shared/javascript/jquery/jquery-ui-1.10.3.custom/js/jquery-1.9.1.js"></script>
<script src="/shared/javascript/jquery/jquery-ui-1.10.3.custom/js/jquery-ui-1.10.3.custom.min.js"></script>
<link rel="stylesheet" href="/shared/javascript/jquery/jquery-ui-1.10.3.custom/css/smoothness/jquery-ui-1.10.3.custom.min.css" />

<!-- Sales APP VIEW -->

<h3>Sales App Contents</h3>

<div id="hierarchy_container">
<?php
$level = 0;
foreach($sac->getRootElements() as $vars):
    $element = new tldConfiguratorElement((int)$vars['id']);
    echo _getElementTreeView($element,$level);
endforeach;
?>
</div>

<!-- END of Sales APP VIEW -->

<?php

function _getElementTreeView($element,$level){
    $html = _getElementView($element,$level);
    foreach($element->getChildElements() as $vars){
        $childElement = new tldConfiguratorElement((int)$vars['id']);
        $html.= _getElementTreeView($childElement,$level+1);
    }
    return $html;
}

function _getElementView($element,$level){
    // Look options
    $elementType = $element->getOptionByKey('type');
    // Depending on element type
    // -- Default info
    $value = $element->getValue();
    $id = $element->getID();
    // -- Specific
    switch($elementType['value']){
    case 'FOLDER':
        $icon = '<img src="/shared/icons/application/folder.png" width="16" />';
        // Construct HTML
        $htmlSpacer = _getSpacerByLevel($level);
        return <<<EOF
<div class="element_container">
  <p class="element_details">$htmlSpacer $icon  <a href='/en/private/sales_service/sales.php?m[0]=sac&m[1]=element&m[2]=view&id=$id'>$value</a></p>
</div>
EOF;
    break;
    }
    
}

function _getSpacerByLevel($level){
    $html = null;
    $htmlSpacer = " ----";
    for($i=1;$i<=$level;$i++){
        $html.=$htmlSpacer;
    }
    return $html;
}

function _getToolBar(){
    
}

return ob_get_clean();
