{if strtoupper(substr($eap.filename, -3))=="JPG"}
  <h3>EAP File</h3>
  <p>
	<a href="/en/private/uploads/eap/{$eap.filename}">
		<img src="/en/private/uploads/eap/{$eap.filename}" width="100" align="center">
	</a>
  </p>
{elseif $eap.filename<>""}
  <h3>EAP File</h3>
  <p>
	<a href="/en/private/uploads/eap/{$eap.filename}">
		<img src="/shared/bluesphere/64x64/mimetypes/document.png" align="center" alt="Download Attachment">
	</a>
  </p>
{/if}
