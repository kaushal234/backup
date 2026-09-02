{if strtoupper(substr($gwf.picture_filename, -3))=="JPG"}
	<a href="/en/private/uploads/gwf/{$gwf.picture_filename}">
		<img src="/en/private/uploads/gwf/{$gwf.picture_filename}" width="250" align="right">
	</a>
{elseif $gwf.picture_filename<>""}
	<a href="/en/private/uploads/gwf/{$gwf.picture_filename}">
		<img src="/shared/bluesphere/64x64/mimetypes/document.png" align="right" alt="Download Attachment">
	</a>
{/if}

<h3>General</h3>

<table>
    <tr><td>GWF#</td><td>{$gwf.id}</td></tr>
    <tr><td>Confidential:</td><td>{$gwf.pvt}</td></tr>
    <tr><td>Status:</td><td>{$gwf.status}</td></tr>
	<tr><td>Closed Date:</td><td>{$gwf.dt_closed}</td></tr>
    <tr><td>Assignor:</td><td>{$gwf.assignor_fullname}</td></tr>
    <tr><td>Form Type:</td><td>{$gwf.payload_class}</td></tr>
    <tr><td>Date:</td><td>{$gwf.date}</td></tr>
    <tr><td>Importance Factor:</td><td>{$gwf.ifactor}</td></tr>
    <tr><td>Est Completion:</td><td>{$gwf.dest}</td></tr>
    <tr><td>Category:</td><td>{$gwf.ctg}</td></tr>
    <tr><td>Business Unit:</td><td>{$gwf.bu_fullname}</td></tr>
    <tr><td>Type:</td><td>{$gwf.type}</td></tr>
    <tr><td>Model:</td><td>{$gwf.model}</td></tr>
    <tr><td>Short Description:</td><td>{$gwf.dsca}</td></tr>
    <tr><td>Description</td><td>{$gwf.dscb|nl2br}<br><br></td></tr>
    <tr><td>Key Word 1:</td><td>{$gwf.kw1}</td></tr>
    <tr><td>Key Word 2:</td><td>{$gwf.kw2}</td></tr>
    <tr><td>Key Word 3:</td><td>{$gwf.kw3}</td></tr>
    <tr><td>Key Word 4:</td><td>{$gwf.kw4}</td></tr>
    <tr><td>Key Word 5:</td><td>{$gwf.kw5}</td></tr>
</table>