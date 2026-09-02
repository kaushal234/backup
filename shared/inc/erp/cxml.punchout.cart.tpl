<cXML version="1.0" payloadID="123" timestamp="2006-03-20T11:48:14-EST">
	<Header>
		<From>
			<Credential domain="DUNS">
			<Identity>056345309</Identity> 
			</Credential>
		</From>
		<To>
			<Credential domain="DUNS">
			<Identity>{$cart.punchoutLoginRequest->Header->To->Credential->Identity|escape}</Identity> 
			</Credential>
		</To>
		<Sender>
			<Credential domain="DUNS">
				<Identity>056345309</Identity> 
			</Credential>
			<UserAgent>Network</UserAgent> 
		</Sender>
	</Header>
	<Message>
		<PunchOutOrderMessage>
			<BuyerCookie>{$cart.punchoutLoginRequest->Request->PunchOutSetupRequest->BuyerCookie|escape}</BuyerCookie> 
			<PunchOutOrderMessageHeader operationAllowed="create">
				<Total>
					<Money currency="USD">0.00</Money> 
				</Total>
			</PunchOutOrderMessageHeader>
			{foreach key=key item=line from=$cart.lines}
			<ItemIn quantity="{$line.qty}">
				<ItemID>
					<SupplierPartID>{$line.item.ITEM|escape}</SupplierPartID>
				</ItemID>
				<ItemDetail>
					<UnitPrice>
						<Money currency="USD">0.00</Money> 
					</UnitPrice>
					<Description xml:lang="EN">{$line.item.DESCRIPTION|escape}</Description> 
					<UnitOfMeasure>{$line.item.UM|escape}</UnitOfMeasure>
					<ManufacturerPartID>{$line.item.ITEM|escape}</ManufacturerPartID>
				</ItemDetail>
			</ItemIn>
			{/foreach}
		</PunchOutOrderMessage>
	</Message>
</cXML>