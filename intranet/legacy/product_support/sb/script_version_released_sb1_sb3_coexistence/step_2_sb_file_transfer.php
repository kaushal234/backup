<?php
include('product_support.inc.php');

// 1 - GET SB

$query = <<<EOF
SELECT * FROM sbs WHERE status LIKE 'LOCKED'
EOF;
$rows = tldUtils::getSqlToAssocArray($query);

// 2 - Foreach SB process FILES
foreach($rows as $sbHeader){
    echo '<hr>SB#'.$sbHeader['id'];

    // init
    $sb = new tldSB($sbHeader['id']);
    $filesToAdd = array();

    // 2.1 Look for MAIN file
    $filename = $sb->itsDetails['sbs_file'];
    if(empty($filename)){
        echo ' -> No MAIN file';
    }else{
        $filesToAdd[]=array(
            'desc'=>"SB#{$sb->getID()} file",
            'name'=>$filename,
            'path'=>tldUtils::getPathToUploadFile($sb->itsFileDir,$filename)
        );
    }

    // 2.2 Look for attached files
    $files = $sb->getFiles();
    echo ' -> Attached files '.count($files);
    foreach($files as $file){
        $filesToAdd[]=array(
            'desc'=>$file['description'],
            'name'=>$file['filename'],
            'path'=>tldUtils::getPathToUploadFile($sb->itsAttachmentDir,$file["filename"])
        );
    }

    // 2.3 Add MOD FILES
    foreach($filesToAdd as $k=>$fileToAdd){
        echo "<br> - File $k: {$fileToAdd['name']}";
        // Check file name  lengh
        if(strlen($fileToAdd['name'])>50){
            // calculate
            $cutVal = strlen($fileToAdd['name'])-50;
            // Cut begining of filename
            $fileToAdd['name'] = substr($fileToAdd['name'], $cutVal);
        }
        // insert in mod file
        $e = tldModFile::insert(
            tldUtils::cleanupFormInput(
                array(
                	"parent_id"=>$sb->getID(),
                	"module"=>'SB3',
                	"poster"=>$sb->itsDetails['entered_by'],
                	"description"=>$fileToAdd['desc'],
                	"filename"=>$fileToAdd['name']
                )
            ),
            array(
                'tmp_name'=>$fileToAdd['path'],
            	'name'=>$fileToAdd['name']
            )
        );
        if(is_string($e)){
            echo " ERROR: $e";
            continue;
        }
    }
}
echo '<hr>DONE !!!';
