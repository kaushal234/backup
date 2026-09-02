<?php
ob_start();

echo<<<EOF
<?xml version="1.0" encoding="UTF-8"?>
EOF;
?>
<cXML payloadID="201003150513504024886@10.0.0.36" timestamp="<?= date('Y-m-d')."T".date("h:i:sP")?>" xml:lang="en-US">
    <Header>
        <From>
            <Credential domain="tld-gse">
                <Identity>www.tld-gse.com</Identity>
            </Credential>
        </From>
        <To>
            <Credential domain="DUNS">
                <Identity>123456789</Identity>
            </Credential>
        </To>
        <Sender>
            <Credential domain="tld-gse">
                <Identity>www.tld-gse.com</Identity>
                <SharedSecret><?= $auth['shared_secret'];?></SharedSecret>
            </Credential>
            <UserAgent>tld-gse eProcurement</UserAgent>
        </Sender>
    </Header>
    <Request>
        <OrderRequest>
            <OrderRequestHeader orderID="<?= $a['TLDHEADER']['POID']; ?>" orderDate="<?= date('Y-m-d')."T".date("h:i:sP")?>" type="new">
                <Total>
                    <Money currency="<?= $a['POORDERFTR']['CURRENCY'];?>"><?= $a['POORDERFTR']['GOODS'];?></Money>
                </Total>
                <ShipTo>
                    <Address addressID="0205043">
                        <Name xml:lang="en-US"><?= $a['POORDERHDR']['PARTNER'][1]['NAME'];?></Name>
                        <PostalAddress name="tld-gse">
                            <DeliverTo><?= $a['POORDERHDR']['PARTNER'][1]['CONTACT']['NAME'][0];?></DeliverTo>
                            <Street><?= $a['POORDERHDR']['PARTNER'][1]['ADDRESS']['ADDRLINE'][0];?></Street>
                            <Street><?= $a['POORDERHDR']['PARTNER'][1]['ADDRESS']['ADDRLINE'][1];?></Street>
                            <City><?= $a['POORDERHDR']['PARTNER'][1]['ADDRESS']['ADDRLINE'][2];?></City>
                            <State><?= $a['POORDERHDR']['PARTNER'][1]['ADDRESS']['ADDRLINE'][3];?></State>
                            <PostalCode>06095-2340</PostalCode>
                            <Country isoCountryCode="US">US</Country>
                        </PostalAddress>
                        <Email><?= $a['POORDERHDR']['PARTNER'][1]['CONTACT']['NAME'][2];?></Email>
                    </Address>
                </ShipTo>
                <BillTo>
                    <Address>
                        <Name xml:lang="en-US"><?= $a['POORDERHDR']['PARTNER'][1]['NAME'];?></Name>
                        <PostalAddress name="tld-gse">
                            <DeliverTo><?= $a['POORDERHDR']['PARTNER'][1]['CONTACT']['NAME'][0];?></DeliverTo>
                            <Street><?= $a['POORDERHDR']['PARTNER'][1]['ADDRESS']['ADDRLINE'][0];?></Street>
                            <Street><?= $a['POORDERHDR']['PARTNER'][1]['ADDRESS']['ADDRLINE'][1];?></Street>
                            <City><?= $a['POORDERHDR']['PARTNER'][1]['ADDRESS']['ADDRLINE'][2];?></City>
                            <State><?= $a['POORDERHDR']['PARTNER'][1]['ADDRESS']['ADDRLINE'][3];?></State>
                            <PostalCode>06095-2340</PostalCode>
                            <Country isoCountryCode="US">US</Country>
                        </PostalAddress>
                        <Email><?= $a['POORDERHDR']['PARTNER'][1]['CONTACT']['NAME'][2];?></Email>
                    </Address>
                </BillTo>
                <Shipping trackingDomain="NONE">
                    <Money currency="USD">0.00</Money>
                    <Description xml:lang="en-US"></Description>
                </Shipping>
            </OrderRequestHeader>
            <?php foreach($a['DETAIL'] as $i=>$line):?>
                <ItemOut quantity="<?= $line['QUANTITY'];?>" requestedDeliveryDate="" lineNumber="<?= $line['POLINENUM'];?>">
                    <ItemID>
                        <SupplierPartID><?= $line['ITEM'];?></SupplierPartID>
                    </ItemID>
                    <ItemDetail>
                        <UnitPrice>
                            <Money currency="<?= $a['POORDERFTR']['CURRENCY'];?>"><?= $line['PRIC'];?></Money>
                        </UnitPrice>
                        <Description xml:lang="en-US"><?= $line['DESCRIPTN'];?></Description>
                        <UnitOfMeasure><?= $line['UOMP'];?></UnitOfMeasure>
                        <Classification domain="UNSPSC"></Classification>
                        <ManufacturerPartID><?= $line['ITEM'];?></ManufacturerPartID>
                        <Extrinsic name="RushOrder">no</Extrinsic>
                        <Extrinsic name="ShippingInstructions"></Extrinsic>
                    </ItemDetail>
                </ItemOut>
            <?php endforeach;?>
        </OrderRequest>
    </Request>
</cXML>
<?php
return ob_get_contents();
?>