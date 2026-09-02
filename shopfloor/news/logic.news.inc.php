<?php
$DEFAULT_TITLE .= "\News";

switch($m[1] ?? null){
default:
    // Get latest news
    $query = "SELECT * FROM internal_news WHERE date <= now() ORDER BY internal_news.id DESC LIMIT 10";
    $rows = tldUtils::getSqlToAssocArray($query);
    // Display
    $smarty->assign("news",$rows);
    $body.= $smarty->fetch("news/view.news.list.tpl");
break;
}
