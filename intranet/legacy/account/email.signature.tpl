
<DIV id=signature>

{$data.firstname} {$data.lastname}<BR>
{if !empty($data.title)} 		{$data.title}<BR>{/if}
{if !empty($data.division)} 	{$data.division}<BR>{/if}
{if !empty($data.department)} 	{$data.department}<BR>{/if}
{if !empty($data.bu)} 			{$data.bu}<BR>{/if}

<BR>

<TABLE>
	<TBODY>
		{if !empty($data.phone)}
		<TR><TD>Phone</TD><TD>: {$data.phone}</TD></TR>
		{/if}
		{if !empty($data.direct_phone)}
		<TR><TD>Direct Phone</TD><TD>: {$data.direct_phone}</TD></TR>
		{/if}
		{if !empty($data.mobile)}
		<TR><TD>Mobile</TD><TD>: {$data.mobile}</TD></TR>
		{/if}
		{if !empty($data.fax)}
		<TR><TD>Fax</TD><TD>: {$data.fax}</TD></TR>
		{/if}
		<TR><TD>Website</TD><TD>: <A href="https://www.tld-gse.com/">www.tld-gse.com</A></TD></TR>
	</TBODY>
</TABLE>

</DIV>