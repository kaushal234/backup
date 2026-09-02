<?php
include_once("common.inc.php");
include_once("forms_and_reports.inc.php");


$query = "SELECT id,model,zh FROM products_datasheets WHERE zh<>'' ";
$rows = tldUtils::getSqlToAssocArray($query);

foreach($rows as $row){
    // Init vars
    $id = $row['id'];
    $textSource = $row['zh'];
    $textInHtmlEntities = null;
    // Convert in UTF-8
    $textUtf8 = iconv('GB2312','UTF-8',$textSource);
    // Decode all existing entities
    $textUtf8EntityDecoded = html_entity_decode($textUtf8,ENT_COMPAT,'UTF-8');
    // Convert to html entities
    $offset = 0;
    while ($offset >= 0) {
        $textInHtmlEntities.="&#".ordutf8($textUtf8EntityDecoded, $offset).";";
    } 
    // Update
    $update = TldDatabase::escape($textInHtmlEntities);
    $query = "UPDATE products_datasheets SET zh='$update' WHERE id=$id";
    echo "<br>Update of #$id : ".tldUtils::sqlExecute($query);
}



function ordutf8($string, &$offset) {
    $code = ord(substr($string, $offset,1));
    if ($code >= 128) {        //otherwise 0xxxxxxx
        if ($code < 224) $bytesnumber = 2;                //110xxxxx
        else if ($code < 240) $bytesnumber = 3;        //1110xxxx
        else if ($code < 248) $bytesnumber = 4;    //11110xxx
        $codetemp = $code - 192 - ($bytesnumber > 2 ? 32 : 0) - ($bytesnumber > 3 ? 16 : 0);
        for ($i = 2; $i <= $bytesnumber; $i++) {
            $offset ++;
            $code2 = ord(substr($string, $offset, 1)) - 128;        //10xxxxxx
            $codetemp = $codetemp*64 + $code2;
        }
        $code = $codetemp;
    }
    $offset += 1;
    if ($offset >= strlen($string)) $offset = -1;
    return $code;
}

?>
