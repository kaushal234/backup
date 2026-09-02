<?php
include_once("product_support.inc.php");
include_once("sales_service.inc.php");

/*
 * Switch section to add modules
 * This allow to add record contents into the email
 *
 * How to use :
 *      -> Include the file where your class is located on top of this file
 *      -> Create getPrintVersion() method into your module class
 *      -> Add your module below into the switch structure below
 *      -> Always set the result into the variable $document
 *
 */

switch($module){
case 'spr':
case 'csr':
case 'csr2':
case 'toc':
case 'pdc':
case 'scar':
case 'sfr':
case 'wc':

    // 1. GET MODULE CLASS

    $moduleClassName = "tld".strtoupper($module);
    try{
        $reflectionModuleObj = new ReflectionClass($moduleClassName);
    }catch(ReflectionException $e){
        $DEFAULT_ERROR[] =  "ERROR: No print version available for this module";
        $DEFAULT_ERROR[] =  "Reason: Class for this module have an error. ({$e->getMessage()})";
        break;
    }
    $moduleObj = $reflectionModuleObj->newInstanceArgs(array($id));

    // 2. GET PRINT VERSION

    $document = '';
    if(in_array($module,array('csr2','csr'))){
    	$document .= "\n<p><a href=\"https://www.tld-gse.com/en/private/sales_service/service.php?m[0]=csr&m[1]=view&id=$id\">Click here to view details</a></p>\n";
    }
    $document .= $moduleObj->getPrintVersion();

    // 3. MISC

    switch($module){
    case 'csr2':
    case 'csr':
    case 'toc':
    case 'wc':
        $_FORM_DEFAULTS = array(
            'subject'=>$moduleObj->getDefaultEmailSubject()
        );
    break;
    default:
        $_FORM_DEFAULTS = array(
            'subject'=>strtoupper($module).'#'.$id
        );
    break;
    }
break;
default:
	$DEFAULT_ERROR[] =  "ERROR: Module is empty or not valid !";
break;
}

?>
