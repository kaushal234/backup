<?php

use Symfony\Component\Finder\Finder;

include_once("erp.inc.php");

if(!$user->isInGroup(array("gg_ADMIN","gg_ACCT","gg_PARTS","gg_SALES","gg_MIS","gg_PUR","gg_SUPPORT"))){
    $DEFAULT_ERROR[] = "You do not have permissions for this page..";
    return;
}

$DEFAULT_TITLE .= "\Archive";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=archive">Home</a>&nbsp;|&nbsp;
<a href="archive/Help.pdf">Help</a>&nbsp;|&nbsp;
<a href="archive/archive_admin.php">Maintain Archive</a>
EOF;

$DOCTYPES = array(
    "SALES ORDER ACK",
    "SALES INVOICE",
    "FINANCE INVOICE",
    "SALES QUOTATION",
    "PURCHASE ORDER",
    "PURCHASE INQUIRY",
    "PACKING SLIP"
);

switch($m[1]){
default:
    // Listing
    $erpRawList = tldLocation::getERPList();
    $erpList = array();
    foreach($erpRawList as $erpRaw){
        $erpList[$erpRaw['erp']]="{$erpRaw['location']} ({$erpRaw['erp']})";
    }
    // Form
    $form = new HTML_QuickForm('frmArchive', 'get');
    $form->addElement(	'header', 'title', 'Document Archive');
    $form->addElement(	'hidden', 'm[0]', 'archive');
    $form->addElement(	'select', 'erp', 'Company Number', $erpList);
    $form->addElement(	'select', 'doctype', 'Doc type', array_combine($DOCTYPES, $DOCTYPES));
    $form->addElement(	'text', 'id', 'Doc# (for invoices you must specify the prefix e.g. SLU22600308');
    $form->addElement(	'submit', 'btnSubmit', 'Submit');
    $form->setDefaults(array(
    	'erp'=>tldLocation::getERPByID($user->getBUID())
    ));
    $body.= $form->toHTML();

    if($erp && $doctype && $id){
        // secure the data
        $erp = TldDatabase::escape($erp);
        $doctype = TldDatabase::escape($doctype);
        $id = TldDatabase::escape($id);
        // Get the archive
        $arch = new tldArchive($erp);
        if (!(bool) $_GET['search_on_disk']) {
            $rows = $arch->byTypeID($doctype, $id);

            if (empty($rows)) {
                $DEFAULT_ERROR[] = 'No results found in database, <a href="'.$_SERVER['REQUEST_URI'].'&search_on_disk=1">click here</a> if you want to search for the file.';
                return;
            }
        } else {
            $finder = new Finder();

            $firstCharOfID = $doctype === 'SALES INVOICE' ? 3 : 0;

            $path = sprintf('%s/%s/%s/%s/',
                $erp,
                $doctype,
                substr($id, $firstCharOfID, 3),
                substr($id, $firstCharOfID, 4)
            );

            $fullPath = tldArchive::getWebRoot().'/'.$path;

            try {
                $finder->in($fullPath);
            } catch (Exception $e) {
                $DEFAULT_ERROR[] = 'No results found...';
                break;
            }

            $fileName = sprintf('%s_%s_%s',
                $erp,
                $doctype,
                $id
            );

            $finder->files()->name("$fileName*");

            if ($fileCount = $finder->count()) {
                $limit = 100;
                $body = sprintf('<p>%s file%s found %s</p><ul>', $fileCount, $fileCount > 1 ? 's' : '', $fileCount > $limit ? "(showing $limit first results)" : '');
                $i = 1;
                foreach($finder as $fileInfo) {
                    $body.= sprintf('<li><a href="/en/private/strs_pdf/archive/%1$s%2$s">%2$s</a><br>', $path, $fileInfo->getFilename()).'</li>';
                    ++$i;
                    if ($i > $limit) {
                        break;
                    }
                }
                $body.= '</ul>';
            } else {
                $DEFAULT_ERROR[] = 'No results found...';
            }

            return;
        }

        $smarty->assign("lines", $rows);
        $body .= $smarty->fetch("finance/archive/archive.list.tpl");
    }
}

