Dear Valued Customer,

The purpose of this message is to officially advise you that TLD has issued a {$sb.urgency_fullname} <a href="https://www.tld-gse.com/extranet/index.php?m[0]=sbs&m[1]=view&id={$sb.id}" title="Click to see this SB">SB#{$sb.id}</a>; following is a description of the bulletin:

{$sb.description}

This Bulletin is applicable to the following equipment you currently use (as per our records):
===============================================================================
{$erlist}
===============================================================================

1-1 : It is mandatory to implement this bulletin on all these units as soon as possible.
1-2 : It is very important to implement this bulletin on all these units, and we hope you can implement this job as soon as possible.
1-3 : It is in your best interest to implement this bulletin on all these units.

2-1: We have already sent you the necessary parts to your attention, and you can follow the delivery through the following link.
2-2: By replying to this message, you can request the sending of parts free of charge to the address you will indicate. Please reference the bulletin number as well as the TLD unit serial number(s) in any further communication.
{if !empty($sph)}
2-3: The parts for implementing this bulletin are available from the TLD Spare Parts Hub located in {$sph.city} {$sph.state}. Please contact them at {$sph.sph_email} or {$sph.tel} for price and delivery details. Please reference the bulletin number as well as the TLD unit serial number(s) in any further communication.
{else}
2-3: The parts for implementing this bulletin are available from any TLD Spare Parts Hub.  Please contact the nearest one for price and delivery details.  Please reference the bulletin number as well as the TLD unit serial number(s) in any further communication.
{/if}

3-1: Our Service managers or technicians will contact you shortly to organize a visit to implement this bulletin on your units
3-2: If you need assistance for the implementation of this service bulletin, please contact your nearest TLD Service Center.  Please reference the bulletin number as well as the TLD unit serial numbers in any further communication.

Please acknowledge the receipt of this message.

For complete details regarding this TLD Technical Bulletin, please access the TLD Service Module.  Our Sales, Service and Parts support teams stay at your disposal for any other information.

Best regards,

{$user.firstname} {$user.lastname}
{$user.title}
