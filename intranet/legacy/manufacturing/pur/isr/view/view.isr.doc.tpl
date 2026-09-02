<table cellpadding=10>
  <tr>
    {if $isr.doc_ship<>""}
	<td align="center">
	<a href="/en/private/uploads/isr/{$isr.doc_ship}" target="_blank">
		<img src="/shared/bluesphere/64x64/mimetypes/document.png" alt="Download Shipping Document">
		<br/>Shipping Document
	</a>
	</td>
	{/if}
	{if $isr.doc_qa<>""}
	<td align="center">
	<a href="/en/private/uploads/isr/{$isr.doc_qa}" target="_blank">
		<img src="/shared/bluesphere/64x64/mimetypes/document.png" alt="Download Quality Document">
		<br/>Quality Document
	</a>
	</td>
	{/if}
	{if $isr.doc_inv<>""}
	<td align="center">
	<a href="/en/private/uploads/isr/{$isr.doc_inv}" target="_blank">
		<img src="/shared/bluesphere/64x64/mimetypes/document.png" alt="Download Invoice Document">
		<br/>Invoice Document
	</a>
	</td>
	{/if}
  </tr>
</table>