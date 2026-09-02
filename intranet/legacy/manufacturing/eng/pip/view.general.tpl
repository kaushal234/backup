{if strtoupper(substr($pip.picture_filename, -3))=="JPG"}
	<a href="/en/private/uploads/pip/{$pip.picture_filename}">
		<img src="/en/private/uploads/pip/{$pip.picture_filename}" width="250" align="right">
	</a>
{elseif $pip.picture_filename<>""}
	<a href="/en/private/uploads/pip/{$pip.picture_filename}">
		<img src="/shared/bluesphere/64x64/mimetypes/document.png" align="right" alt="Download Attachment">
	</a>
{/if}
<h3>{$pip.status}</h3>
{if $pip.status=="SUSPENDED"}
	<p class="alert">This PIP was SUSPENDED {$pip.date_suspended}</a>
{/if}
{if $pip.status<>"CLOSED"}
	<h3>IF:&nbsp;{$pip.ifactor}&nbsp;&nbsp;
	Months Open:&nbsp;{$pip.monthsOpen}&nbsp;&nbsp;
	</h3>
{else}
	<h3>PIP Closed:&nbsp;{$pip.date_closed}</h3>
	<p><b>Resolution:</b> {$lastComment.comment}</p>
{/if}
<p>
<b>Factory:</b>&nbsp;{$pip.factory_fullname}&nbsp;&nbsp;
<b>Type:</b>&nbsp;{$pip.product_type}&nbsp;&nbsp;
<b>Model:</b>&nbsp;{$pip.model}&nbsp;&nbsp;<br>
<b>Date:</b>&nbsp;{$pip.date}&nbsp;&nbsp;
<b>Posted by:</b>&nbsp;{$pip.poster_fullname}&nbsp;&nbsp;&nbsp;
<b>Initiated by:</b>&nbsp;{$pip.initiator_fullname}<br>
<b>Description</b><br>
{$pip.description|nl2br}<br><br><br>
{if $pip.rejection_reason<>''}
	<b>Reason for Closing/Rejecting</b><br>
	{$pip.rejection_reason|nl2br}<br>
{/if}
</p>