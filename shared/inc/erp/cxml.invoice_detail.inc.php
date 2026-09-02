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
        <InvoiceDetailRequest>
            <InvoiceDetailRequestHeader invoiceID="123456"
              purpose="standard" operation="new"
              invoiceDate="2001-04-20T23:59:20-07:00">
                <InvoiceDetailHeaderIndicator/>
                <InvoiceDetailLineIndicator
                  isTaxInLine="yes"
                  isShippingInLine="yes"
                  isAccountingInLine="yes"/>
                <InvoicePartner>
                    <Contact role="soldTo" addressID="B2.4.319">
                        <Name xml:lang="en">Mike Smith</Name>
                        <PostalAddress name="default">
                            <DeliverTo>Mike Smith</DeliverTo>
                            <Street>15 Camino del Cerro</Street>
                            <City>Los Gatos</City>
                            <State>CA</State>
                            <PostalCode>95032</PostalCode>
                            <Country
                                isoCountryCode="US">United States</Country>
                        </PostalAddress>
                        <Email name="default">admin@ariba.com</Email>
                        <Phone name="work">
                            <TelephoneNumber>
                                <CountryCode
                                    isoCountryCode="US">1</CountryCode>
                                <AreaOrCityCode>408</AreaOrCityCode>
                                <Number>3582000</Number>
                            </TelephoneNumber>
                        </Phone>
                        <Fax name="work">
                            <TelephoneNumber>
                                <CountryCode
                                    isoCountryCode="US">1</CountryCode>
                                <AreaOrCityCode>408</AreaOrCityCode>
                                <Number>3582100</Number>
                            </TelephoneNumber>
                        </Fax>
                    </Contact>
                </InvoicePartner>
                <InvoicePartner>
                    <Contact role="remitTo" addressID="Billing">
                        <Name xml:lang="en">Lisa King</Name>
                        <PostalAddress name="billing department">
                            <DeliverTo>Lisa King</DeliverTo>
                            <Street>16 Castro Street</Street>
                            <City>Mountain View</City>
                            <State>CA</State>
                            <PostalCode>95035</PostalCode>
                            <Country
                                isoCountryCode="US">United States</Country>
                        </PostalAddress>
                        <Email name="default">lking@supplierbank.com</Email>
                        <Phone name="work">
                            <TelephoneNumber>
                                <CountryCode
                                    isoCountryCode="US">1</CountryCode>
                                <AreaOrCityCode>650</AreaOrCityCode>
                                <Number>9990000</Number>
                            </TelephoneNumber>
                        </Phone>
                    </Contact>
                    <IdReference identifier="00000-11111"
                      domain="accountReceivableID">
                        <Creator xml:lang="en">Supplier ERP</Creator>
                    </IdReference>
                    <IdReference identifier="123456789"
                      domain="bankRoutingID">
                        <Creator xml:lang="en">Supplier Bank</Creator>
                    </IdReference>
                </InvoicePartner>
                <InvoiceDetailPaymentTerm payInNumberOfDays="10"
                  percentageRate="10"/>
                <InvoiceDetailPaymentTerm payInNumberOfDays="20"
                  percentageRate="5"/>
                <InvoiceDetailPaymentTerm payInNumberOfDays="30"
                  percentageRate="0"/>
                <InvoiceDetailPaymentTerm payInNumberOfDays="40"
                  percentageRate="-5"/>
                <InvoiceDetailPaymentTerm payInNumberOfDays="50"
                  percentageRate="-10"/>
            </InvoiceDetailRequestHeader>
//iterate over the line items
            <InvoiceDetailOrder>
                <InvoiceDetailOrderInfo>
                    <OrderReference>
                        <DocumentReference payloadID="25510.10.81.230"/>
                    </OrderReference>
                    <MasterAgreementReference>
                        <DocumentReference payloadID="25510.10.81.002"/>
                    </MasterAgreementReference>
                </InvoiceDetailOrderInfo>
                <InvoiceDetailItem invoiceLineNumber="1" quantity="30">
                    <UnitOfMeasure>EA</UnitOfMeasure>
                    <UnitPrice>
                        <Money currency="USD">130</Money>
                    </UnitPrice>
                    <InvoiceDetailItemReference lineNumber="2">
                        <ItemID>
                            <SupplierPartID>TEX08134</SupplierPartID>
                        </ItemID>
                        <Description xml:lang="en">
Texas Instruments Superview Calculator - Heavy-Duty 12-Digit Print/Display
                        </Description>
                    </InvoiceDetailItemReference>
                    <SubtotalAmount>
                        <Money currency="USD">3900</Money>
                    </SubtotalAmount>
                    <Tax>
                        <Money currency="USD">390</Money>
                        <Description xml:lang="en">total item tax
                        </Description>
                        <TaxDetail purpose="tax" category="State sales tax"
                          percentageRate="8">
                            <TaxableAmount>
                                <Money currency="USD">3900</Money>
                            </TaxableAmount>
                            <TaxAmount>
                                <Money currency="USD">312</Money>
                            </TaxAmount>
                            <TaxLocation xml:lang="en">CA</TaxLocation>
                        </TaxDetail>
                        <TaxDetail purpose="tax" category="Federal sales tax"
                          percentageRate="2">
                            <TaxableAmount>
                                <Money currency="USD">3900</Money>
                            </TaxableAmount>
                            <TaxAmount>
                                <Money currency="USD">78</Money>
                            </TaxAmount>
                        </TaxDetail>
                    </Tax>
                    <InvoiceDetailLineShipping>
                        <InvoiceDetailShipping>
                            <Contact role="shipFrom" addressID="1000487">
                                <Name xml:lang="en">Los Gatos</Name>
                                <PostalAddress name="default">
                                    <Street>15 Camino del Cerro</Street>
                                    <City>Los Gatos</City>
                                    <State>CA</State>
                                    <PostalCode>95032</PostalCode>
                                    <Country isoCountryCode="US">
                                        United States</Country>
                                </PostalAddress>
                                <Email name="default">admin@ariba.com</Email>
                                <Phone name="work">
                                    <TelephoneNumber>
                                        <CountryCode isoCountryCode="US">
                                            1</CountryCode>
                                        <AreaOrCityCode>408</AreaOrCityCode>
                                        <Number>3582000</Number>
                                    </TelephoneNumber>
                                </Phone>
                                <Fax name="work">
                                    <TelephoneNumber>
                                        <CountryCode isoCountryCode="US">
                                            1</CountryCode>
                                        <AreaOrCityCode>408</AreaOrCityCode>
                                        <Number>3582100</Number>
                                    </TelephoneNumber>
                                </Fax>
                            </Contact>
                            <Contact role="shipTo" addressID="1000487">
                                <Name xml:lang="en">Los Gatos</Name>
                                <PostalAddress name="default">
                                    <DeliverTo>Jason Lynch</DeliverTo>
                                    <Street>34 Castro Street</Street>
                                    <City>Mountain View</City>
                                    <State>CA</State>
                                    <PostalCode>95035</PostalCode>
                                    <Country isoCountryCode="US">
                                        United States</Country>
                                </PostalAddress>
                                <Email name="default">admin@ariba.com</Email>
                                <Phone name="work">
                                    <TelephoneNumber>
                                        <CountryCode isoCountryCode="US">
                                            1</CountryCode>
                                        <AreaOrCityCode>408</AreaOrCityCode>
                                        <Number>3582000</Number>
                                    </TelephoneNumber>
                                </Phone>
                                <Fax name="work">
                                    <TelephoneNumber>
                                        <CountryCode isoCountryCode="US">
                                            1</CountryCode>
                                        <AreaOrCityCode>408</AreaOrCityCode>
                                        <Number>3582100</Number>
                                    </TelephoneNumber>
                                </Fax>
                            </Contact>
                        </InvoiceDetailShipping>
                        <Money currency="USD">20</Money>
                    </InvoiceDetailLineShipping>
                    <GrossAmount>
                        <Money currency="USD">4310</Money>
                    </GrossAmount>
                    <NetAmount>
                        <Money currency="USD">4310</Money>
                    </NetAmount>
                    <Distribution>
                        <Accounting name="Buyer assigned accounting code 1">
                            <AccountingSegment id="ABC123456789">
                                <Name xml:lang="en">Purchase</Name>
                                <Description xml:lang="en">Production Control</Description>
                            </AccountingSegment>
                        </Accounting>
                        <Charge>
                            <Money currency="USD">2000</Money>
                        </Charge>
                    </Distribution>
                    <Distribution>
                        <Accounting name="Buyer assigned accounting code 2">
                            <AccountingSegment id="ABC000000001">
                                <Name xml:lang="en">Trade</Name>
                                <Description xml:lang="en">Misc (Expensed)</Description>
                            </AccountingSegment>
                        </Accounting>
                        <Charge>
                            <Money currency="USD">2310</Money>
                        </Charge>
                    </Distribution>
                </InvoiceDetailItem>
            </InvoiceDetailOrder>
//end of loop
            <InvoiceDetailSummary>
                <SubtotalAmount>
                    <Money currency="USD">4900</Money>
                </SubtotalAmount>
                <Tax>
                    <Money currency="USD">490</Money>
                    <Description xml:lang="en">total tax</Description>
                    <TaxDetail purpose="tax" category="State sales tax"
                      percentageRate="8">
                        <TaxableAmount>
                            <Money currency="USD">4900</Money>
                        </TaxableAmount>
                        <TaxAmount>
                            <Money currency="USD">392</Money>
                        </TaxAmount>
                        <TaxLocation xml:lang="en">CA</TaxLocation>
                    </TaxDetail>
                    <TaxDetail purpose="tax" category="Federal sales tax"
                      percentageRate="2">
                        <TaxableAmount>
                            <Money currency="USD">4900</Money>
                        </TaxableAmount>
                        <TaxAmount>
                            <Money currency="USD">98</Money>
                        </TaxAmount>
                    </TaxDetail>
                </Tax>
                <ShippingAmount>
                    <Money currency="USD">30</Money>
                </ShippingAmount>
                <GrossAmount>
                    <Money currency="USD">5420</Money>
                </GrossAmount>
                <NetAmount>
                    <Money currency="USD">5420</Money>
                </NetAmount>
                <DueAmount>
                    <Money currency="USD">5420</Money>
                </DueAmount>
            </InvoiceDetailSummary>
        </InvoiceDetailRequest>
    </Request>
</cXML>
<?php
return ob_get_contents();
?>