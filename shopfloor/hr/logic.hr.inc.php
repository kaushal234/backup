<?php
// Include hr config (file list)
include('hr.config.inc.php');

$DEFAULT_TITLE .= "\HR";
$DEFAULT_MENU.=<<<EOF
<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=hr">Home</a>
EOF;
// Menu by ERP company
switch($ERP){
case 500:
case 510:
case 520:
case 540:
case 900:
    $DEFAULT_MENU.=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=hr&m[1]=documents&m[2]=list&m[3]=taa&category=taa">Textes & accords applicables</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=hr&m[1]=documents&m[2]=list&category=pvce">PV CSE</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=hr&m[1]=documents&m[2]=list&category=pvcce">PV CCE</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=hr&m[1]=documents&m[2]=list&category=pvdp">PV DP</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=hr&m[1]=documents&m[2]=list&category=pvchsct">PV CHSCT</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=hr&m[1]=documents&m[2]=list&category=imp">Informations mutuelle et prévoyance</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=hr&m[1]=documents&m[2]=list&category=dpl">Déplacements (politique voyages, autres)</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=hr&m[1]=documents&m[2]=list&category=sal">Salaires</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=hr&m[1]=documents&m[2]=list&category=ukb">Utilisation Kelio / Baan</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=hr&m[1]=documents&m[2]=list&category=form">Formulaires</a>
EOF;
break;
}

switch($m[1] ?? null){
case 'documents':
	if($m[3]){
	$DEFAULT_MENU.=<<<EOF
<p>&nbsp;&nbsp;<a href="$php_self?m[0]=hr&m[1]=documents&m[2]=list&m[3]=taa&category=interessement">Accord d'interessement</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=hr&m[1]=documents&m[2]=list&m[3]=taa&category=participation">Accord de participation</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=hr&m[1]=documents&m[2]=list&m[3]=taa&category=pee">Plan Epargne Entreprise</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=hr&m[1]=documents&m[2]=list&m[3]=taa&category=perco">PERCO</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=hr&m[1]=documents&m[2]=list&m[3]=taa&category=35h">Accords 35H</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=hr&m[1]=documents&m[2]=list&m[3]=taa&category=femmes">Autres accords</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=hr&m[1]=documents&m[2]=list&m[3]=taa&category=composition">composition des IRP</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=hr&m[1]=documents&m[2]=list&m[3]=taa&category=reglement">Règlement intérieur</a>
EOF;
	}
    switch($m[2] ?? null){
    case 'out':
        // Check if file declared
        if(empty($FILE_LIST[$id])){
            $DEFAULT_ERROR[]="ERROR: File #$id unknow";
            break;
        }
        // Check access following Location
        if(!in_array($ERP,$FILE_LIST[$id]['acl'])){
            $DEFAULT_ERROR[]="ERROR: You do not have permissions from this location";
            break;
        }
//         echo $cfg['paths']['uploads']; die();
        // Get the file
        $file = new basicFile($GLOBALS['LOCAL_INTRANET_PATH'] . "/{$FILE_LIST[$id]['path']}");
        if(!$file->isFile()){
            $DEFAULT_ERROR[]="ERROR: File not found";
            break;
        }
        // Send the file
        $file->out();
        exit;
    break;
    case 'list':
        $fileToDisplay = array();
        // List files depending of ERP & category
        foreach($FILE_LIST as $id=>$vars){
            if($vars['category']!=$_REQUEST['category']) continue;
            if(!in_array($ERP,$vars['acl'])) continue;
            $fileToDisplay[]=$id;
        }
        // List files on screen ------->
        $body.= "<h3>Documents</h3>";
        foreach($fileToDisplay as $k=>$fileId){
            $k++;
            $body.=<<<EOF
<br>$k - <a href="$php_self?m[0]=hr&m[1]=documents&m[2]=out&id=$fileId">{$FILE_LIST[$fileId]['name']}</a>
EOF;
        }
    break;
    }
break;
default:
    switch($ERP){
    case 500:
    case 510:
    case 520:
    case 540:
    case 900:
        $filesToDisplay = [];
        // List files depending of ERP & category
        foreach($FILE_LIST as $id => $vars) {
            if ($vars['category'] === "unclassified") {
                $filesToDisplay[]=$id;
            }
        }

        $body.="<p>Welcome to the Human Resources homepage.</p>";
        $body.="<h4>Unclassed Documents</h4>";

        foreach ($filesToDisplay as $k=>$fileId) {
            $k++;
            $body.=<<<EOF
<br/>$k - <a href="$php_self?m[0]=hr&m[1]=documents&m[2]=out&id=$fileId">{$FILE_LIST[$fileId]['name']}</a>
EOF;
        }

    break;
    default:
        $body.= $smarty->fetch("hr/homepage.hr.tpl");
    break;
    }
break;
}

?>
