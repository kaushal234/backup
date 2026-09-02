<?php
include_once("sales_service.inc.php");

$DEFAULT_TITLE .= "\Catalogue";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=catalogue">Home</a>
EOF;
switch($m[1]){
case 'sales.catalogue.datasheet.process':
    $body = "This page has been migrated and should not be displayed anymore.";
break;
case 'getfile':
    $body = "This page has been migrated and should not be displayed anymore.";
break;
case 'model':
	if(empty($id) || !is_numeric($id)){
		$DEFAULT_ERROR[] = "ERROR: ID sent empty or invalid";
		break;
	}
	$model = new tldDatasheet($id);
	$header = $model->getHeader();
	$model_name = $header["model"];
	$category_id = $header["parent_id"];
	$category = tldcatalogue::getCategories($category_id);
	$category_name = $category[0]["en"];
    if($user->isInGroup(['gg_ADMIN', 'role_PSM',"role_PSE", "role_PSA"])) {
        $DEFAULT_MENU .= <<<EOF
<a href="$php_self?m[0]=catalogue&m[1]=cat&id=$category_id">$category_name</a>
&nbsp;|&nbsp;$model_name
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
EOF;
    } else {
        $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=catalogue&m[1]=cat&id=$category_id">$category_name</a>
&nbsp;|&nbsp;$model_name
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp
EOF;
    }
	$DEFAULT_MENU .=<<<EOF
<a href="$php_self?m[0]=catalogue&m[1]=model&id=$id">Description</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=catalogue&m[1]=model&m[2]=history&id=$id">Revision History</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=catalogue&m[1]=model&m[2]=files&id=$id">Datasheets & Files</a>
&nbsp;|&nbsp;<a href="https://www.tld-gse.com/en/private/gallery3/index.php/search?q=$model_name" target="_blank" title="Search photo in TLD gallery">Photo</a>
EOF;


	switch($m[2]){
	case 'history':
        $body = "This page has been migrated and should not be displayed anymore.";
	break;
	case 'files':
        $body = "This page has been migrated and should not be displayed anymore.";
	break;
	case 'ntos':
        $body = "This page has been migrated and should not be displayed anymore.";
	break;
	}
	$smarty->assign("model",$header);
	$body .= $smarty->fetch("$PATH/catalogue/datasheet.tpl");
break;
case 'cat':
    $body = "This page has been migrated and should not be displayed anymore.";
break;
default:
    $body = "This page has been migrated and should not be displayed anymore.";
break;
}
