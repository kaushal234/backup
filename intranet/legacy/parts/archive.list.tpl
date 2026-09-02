<table width="100%">
<tr><th>Doc Type</th><th>Doc Number</th><th>Date</th><th>File</th></tr>
{foreach name=lines item=line from=$lines}
    <tr>
    <td>{$line.doc_type}</td>
    <td>{$line.num}</td>
    <td>{$line.dat}</td>
    <td>
    <a href="/en/private/strs_pdf/archive/{$line.filepath}" target="_blank">Download</a></td>
    </tr>
{/foreach}
</table>
