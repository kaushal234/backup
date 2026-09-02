<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE cXML SYSTEM "http://xml.cXML.org/schemas/cXML/1.2.011/cXML.dtd">
<cXML payloadID="9949494" xml:lang="en-US"
timestamp="2005-02-04T18:39:49-08:00">
	<Response>
		<Status code="200" text="OK"/>
		<ProfileResponse effectiveDate="2005-01-01T05:24:29-08:00">
			<Transaction requestName="OrderRequest">
				<URL>https://www.tld-parts.com/cxmlOrders.php</URL>
				<Option name="service">tld-parts.orders</Option>
			</Transaction>
			<Transaction requestName="PunchOutSetupRequest">
				<URL>https://www.tld-parts.com/cxml.punchOut.php</URL>
				<Option name="service">tld-parts.signin</Option>
			</Transaction>
		</ProfileResponse>
	</Response>
</cXML>