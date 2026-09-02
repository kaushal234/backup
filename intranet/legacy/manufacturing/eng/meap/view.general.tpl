{if strtoupper(substr($meap.filename, -3))=="JPG"}
	<a href="/en/private/uploads/meap/{$meap.filename}">
		<img src="/en/private/uploads/meap/{$meap.filename}" width="250" align="right">
	</a>
{elseif $meap.filename<>""}
	<a href="/en/private/uploads/meap/{$meap.filename}">
		<img src="/shared/bluesphere/64x64/mimetypes/document.png" align="right" alt="Download Attachment">
	</a>
{/if}
<h3>{$meap.status}</h3>
{if $meap.status=="SUSPENDED"}
	<p class="alert">This Master EAP was SUSPENDED {$meap.date_suspended}</a>
{/if}
{if $meap.status<>"CLOSED"}
	<h3>IF:&nbsp;{$meap.ifactor}&nbsp;&nbsp;
	Months Open:&nbsp;{$meap.monthsOpen}&nbsp;&nbsp;
	</h3>
{else}
	<h3>MEAP Closed:&nbsp;{$meap.date_closed}</h3>
	<p><b>Resolution:</b> {$lastComment.comment}</p>
{/if}
<p>
<b>Factory:</b>&nbsp;{$meap.factory_fullname}&nbsp;&nbsp;
<b>Type:</b>&nbsp;{$meap.product_type}&nbsp;&nbsp;
<b>Model:</b>&nbsp;{$meap.model}&nbsp;&nbsp;<br>
<b>Date:</b>&nbsp;{$meap.date}&nbsp;&nbsp;
<b>Posted by:</b>&nbsp;{$meap.poster_fullname}&nbsp;&nbsp;&nbsp;
<b>Initiated by:</b>&nbsp;{$meap.initiator_fullname}<br>
<b>Description</b><br>
{$meap.description|nl2br}<br><br><br>
{if $meap.rejection_reason<>''}
	<b>Reason for Closing/Rejecting</b><br>
	{$meap.rejection_reason|nl2br}<br>
{/if}
</p>