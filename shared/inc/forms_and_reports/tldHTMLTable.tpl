<h3>{if isset($options.title)}{$options.title}{/if}</h3>

{html_table loop=$list cols=$options.cols table_attr=$options.attribs.table tr_attr=$options.attribs.tr td_attr=$options.attribs.td}
