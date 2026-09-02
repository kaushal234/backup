<AEX_GSEPOAcknowledgment xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xsi:noNamespaceSchemaLocation="C:\repository\wmtn\records\commercial\GSE\AEX_GSEPOAcknowledgment.xsd">
        <CommunicationArea>
                <Sender>{$a.comArea.sender}</Sender>
                <Receiver>{$a.comArea.receiver}</Receiver>
                <MessageSeqID>{$a.comArea.mesSeqID}</MessageSeqID>
                <CreationDateTime>{$a.comArea.creationDateTime}</CreationDateTime>
        </CommunicationArea>
        <POAcknowledgmentHeader>
                <PONumber>{$a.DATAAREA.PROCESS_SO.SOORDERHDR.SOID}</PONumber>
                <MessageTypeIndicator>{$a.header.mestypeind}</MessageTypeIndicator>
        </POAcknowledgmentHeader>
      
      	{foreach key=key item=line from=$a.lines}
			<POAcknowledgementDetails>
				<SupplierPartNumber>{$key}</SupplierPartNumber>
				<Quantity>{$line.qty}</Quantity>
			</POAcknowledgementDetails>
		{/foreach}
        
</AEX_GSEPOAcknowledgment>