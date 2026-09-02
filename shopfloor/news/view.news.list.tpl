
{foreach name=news item=news from=$news}
<div style="width:90%; margin:auto; padding;20px; border:1px #dedede;">
    <p style="font:18px bold; background:#dedede; padding:2px 0 2px 10px;">{$news.title} ({$news.date})</p>
    <p>{$news.en|nl2br}</p>
</div>
{/foreach}