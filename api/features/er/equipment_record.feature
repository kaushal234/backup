Feature: Test Equipment Records API

  Scenario: Request all ER
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_records"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_record/schemas/equipment_records.json"

  Scenario: Request a single ER as basic user should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_records/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_record/schemas/equipment_record.json"

  Scenario: Resource should not be accessible for evendors user
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_records"
    Then the response status code should be 403
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_records/1"
    Then the response status code should be 403

  Scenario: Filters are declared on resource
    Given the class "App\Entity\EquipmentRecord" is exposed on the API
    Then the filter "buyer" should be available and its type should be "string"
    And the filter "buyer.mainSalesRepresentative.asm" should be available and its type should be "string"
    And the filter "endUser" should be available and its type should be "string"
    And the filter "legacyId" should be available and its type should be "int"
    And the filter "serialNumber" should be available and its type should be "string"
    And the filter "airport" should be available and its type should be "string"
    And the filter "airport.code" should be available and its type should be "string"
    And the filter "airport.country.name" should be available and its type should be "string"
    And the filter "salesOrganisation.legacyId" should be available and its type should be "int"
    And the filter "contracts.startDate[strictly_before]" should be available and its type should be "DateTimeInterface"
    And the filter "contracts.startDate[strictly_after]" should be available and its type should be "DateTimeInterface"
    And the filter "greenTagDate[strictly_before]" should be available and its type should be "DateTimeInterface"
    And the filter "greenTagDate[strictly_after]" should be available and its type should be "DateTimeInterface"
    And the filter "estimatedGreenTagDate[strictly_before]" should be available and its type should be "DateTimeInterface"
    And the filter "estimatedGreenTagDate[strictly_after]" should be available and its type should be "DateTimeInterface"
    And the filter "greenTagDate[before]" should be available and its type should be "DateTimeInterface"
    And the filter "greenTagDate[after]" should be available and its type should be "DateTimeInterface"
    And the filter "estimatedGreenTagDate[before]" should be available and its type should be "DateTimeInterface"
    And the filter "estimatedGreenTagDate[after]" should be available and its type should be "DateTimeInterface"
    And the filter "contracts.endUserRepresentatives" should be available and its type should be "string"
    And the filter "contracts.buyerRepresentatives" should be available and its type should be "string"
    And the filter "not_between[contracts.startDate;contracts.expirationDate]" should be available and its type should be "string"
    And the filter "order[type]" should be available and its type should be "string"
    And the filter "order[model]" should be available and its type should be "string"
    And the filter "order[serialNumber]" should be available and its type should be "string"
    And the filter "product.family.productType" should be available and its type should be "string"
    And the filter "context[datetime_format]" should be available and its type should be "string"
    And the filter "odpFilter" should be available and its type should be "string"
    And the filter "late" should be available and its type should be "bool"
    And the filter "notShipped" should be available and its type should be "bool"
    And the filter "airport.code" should be available and its type should be "string"
    And the filter "endUser.name" should be available and its type should be "string"
    And the filter "product.family.productType.englishName" should be available and its type should be "string"
    And the filter "product.name" should be available and its type should be "string"
    And the filter "customerSerialNumber" should be available and its type should be "string"
    And the filter "location" should be available and its type should be "string"
    And the filter "order[customerSerialNumber]" should be available and its type should be "string"
    And the filter "order[product.name]" should be available and its type should be "string"
    And the filter "order[product.family.productType.englishName]" should be available and its type should be "string"
    And the filter "order[airport.code]" should be available and its type should be "string"
    And the filter "order[location]" should be available and its type should be "string"
    And the filter "order[endUser.name]" should be available and its type should be "string"
    And the filter "order[airport.country.name]" should be available and its type should be "string"
    And the filter "by_customer" should be available and its type should be "string"
    And the filter "columns" should be available and its type should be "string"
    And the query parameter "autocomplete" should be available

  Scenario: Request all equipment records with airport details
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_records?normalization_groups[]=iata_code_detail"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_record/schemas/equipment_records_with_airport.json"

  Scenario: Request equipment records with user for csv
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_records?product.family.productType=/sales/product_types/2&normalization_groups_override[]=equipment_list_user&context[datetime_format]=Y-m-d"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_record/schemas/equipment_records_with_user.json"
    And the JSON node "hydra:member[1].dateShipped" should match "~^\d{4}-\d{2}-\d{2}$~"

  Scenario: Request equipment records with buyer for csv
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_records?product.family.productType=/sales/product_types/2&normalization_groups_override[]=equipment_list_buyer&context[datetime_format]=Y-m-d"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_record/schemas/equipment_records_with_buyer.json"

  Scenario: Request a single ER
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_records/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_record/schemas/equipment_record.json"

  Scenario: Request a single ER qrcode
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_records/1/extranet_qrcode"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "image/png"

  Scenario: Request a single ER qrcode an a non existing ER
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_records/14500000112/extranet_qrcode"
    Then the response status code should be 404

  Scenario: Update an ER should not be allowed
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/equipment_records/1"
    Then the response status code should be 403

  Scenario: Create an ER should not be allowed
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_records"
    Then the response status code should be 405

  Scenario: Request all ER limited to end user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_records_user"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_record/schemas/equipment_records.json"
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_records_user"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_record/schemas/equipment_records.json"
    # There are 2 Er with this customer buyer but only 1 with this end user
    And the JSON node "hydra:totalItems" should be equal to 1

    Scenario: Request all ER as extranet should only display the ones I am authorized as buyer, user or maintainer
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_records?itemsPerPage=10"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_record/schemas/equipment_records.json"
    And the JSON node "hydra:member" should have 10 element
    And the JSON node "hydra:totalItems" should be superior to the number 19

    Scenario: Request all ER filtered by buyer, user or maintainer
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_records?by_customer=/sales/customers/36&serialNumber=LIGHT19"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_record/schemas/equipment_records.json"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:totalItems" should be equal to the string "1"
    And the JSON node "hydra:member[0].serialNumber" should be equal to the string "LIGHT19"

    Scenario: As an extranet user, ER without a ship, commissioning or delivery date are hidden
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    #T334455 belongs to an authorized customer but has no ship, commissioning nor delivery date
    When I send a "GET" request to "/equipment_records?serialNumber=T334455"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to the string "0"
    #The same ER is visible to an intranet user, who is not restricted by those dates
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_records?serialNumber=T334455"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to the string "1"

  Scenario: Request an ER is limited to end user, buyer or maintainer
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_records/11"
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    #User can see with end user
    When I send a "GET" request to "/equipment_records/12"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_record/schemas/equipment_record.json"
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    #User can see with buyer
    When I send a "GET" request to "/equipment_records/14"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_record/schemas/equipment_record.json"
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    #User can see with maintainer
    When I send a "GET" request to "/equipment_records/15"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_record/schemas/equipment_record.json"

  Scenario: As a psm, update an ER and add an entry in its Serials collection
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/equipment_records/16" with body:
    """
    {
      "factoryComment": "factory",
      "serials": [
        "/equipment_serials/2",
        {
          "component": "/equipment_serial_components/4",
          "serial": "killer123"
        },
        {
          "component": "/equipment_serial_components/5",
          "serial": "will not be saved"
        }
      ]
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_record/schemas/equipment_record.json"
    And the JSON node "serials" should have 2 elements

  Scenario: As a planner, try to update an ER, should be denied
    Given I authenticate as the intranet user "user-planner@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/equipment_records/16" with body:
    """
    {
      "factoryComment": "factory",
      "serials": [
        "/equipment_serials/2",
        {
          "component": "/equipment_serial_components/5",
          "serial": "killer123"
        }
      ]
    }
    """
    Then the response status code should be 403

  Scenario: As a link expert, I can update ER
    Given I authenticate as the intranet user "user-eng@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/equipment_records/16/link_synchronization" with body:
    """
    {
      "serialNumber": "T13000",
      "product": "/sales/products/2",
      "manufacturerLocation": "/locations/28",
      "salesOrganisation": "/locations/29",
      "emissionRating": "/emission_ratings/2"
    }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "This Equipment Record has no OBU, LINK component, then it's not synchronizable with Link"
    Given I authenticate as the intranet user "user-eng@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/equipment_records/14/link_synchronization" with body:
    """
    {
      "serialNumber": "T13000",
      "product": "/sales/products/2",
      "manufacturerLocation": "/locations/28",
      "salesOrganisation": "/locations/29",
      "emissionRating": "/emission_ratings/2"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_record/schemas/equipment_record.json"
    And the JSON node "serialNumber" should be equal to the string "T13000"
    And the JSON node "product.@id" should be equal to the string "/sales/products/2"
    And the JSON node "manufacturerLocation.@id" should be equal to the string "/locations/28"
    And the JSON node "salesOrganisation.@id" should be equal to the string "/locations/29"
    And the JSON node "emissionRating.@id" should be equal to the string "/emission_ratings/2"

  Scenario: basic user can download a equipment Record report csv through ODP
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "text/csv"
    When I send a "GET" request to "equipment_records?normalization_groups%5B0%5D=odp%3Aview&normalization_groups%5B1%5D=expose_legacy&normalization_groups%5B2%5D=order_line&normalization_groups%5B3%5D=order_transaction&normalization_groups%5B4%5D=order_factory&notShipped=1&order%5Bid%5D=DESC"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "text/csv; charset=utf-8"
    And the csv file headers are:
      | #ID | ER SN# | Model | Type | End User | Buyer | APC |  Country | Sales Organisation | Customer Asset # | Customer PO | First GT Date | First Estimated GT Date | Estimated GT Date | GT Date | Yellow Tag Date | Estimated Pick Up Date | Date Shipped | Work Order | Emission Rating | Requested Delivery Date | Factory Promised Delivery Date | Factory BU | SOL | Purchase Order Accepted Date | Pre Delivery Inspection | Incoterm | Incoterm Location | Invoice Number | Commissioning | Length (in mm) | Width (in mm) | Height (in mm) | Weight (in Kg) | Light | Payment Terms |

  Scenario: basic user can download a equipment Record report xls through ODP
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "equipment_records?notShipped=1&order%5Bid%5D=DESC&columns=id,serialNumber,model,type,endUser,buyer,airport.code,deliveredCountry,salesOrganisation,customerAssetNumber,order.customerPurchaseOrders,firstGreenTagDate,firstEstimatedGreenTagDate,estimatedGreenTagDate,greenTagDate,yellowTagDate,lastEquipmentShippingRecord.estimatedPickUpDate,dateShipped,workOrder,emissionRating,orderFactory.requestedDeliveryDate,orderFactory.factoryPromisedDeliveryDate,orderFactory.orderLine.factory,orderFactory.orderLine.legacyId,orderFactory.orderLine.purchaseOrderAcceptedDate,orderFactory.orderLine.inspection,orderFactory.orderLine.incoterm.code,orderFactory.orderLine.incotermLocation,orderTransaction.invoice,orderFactory.commissioning,length,width,height,weight,light,orderFactory.orderLine.paymentTerms,unitGrossSellingPrice"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Id | Serial Number | Model | Type | End User | Buyer | Airport Code | Delivered Country | Sales Organisation | Customer Asset Number | Customer Po | First Green Tag Date | First Estimated Green Tag Date | Estimated Green Tag Date | Green Tag Date | Yellow Tag Date | Estimated Pick Up Date | Date Shipped | Work Order | Emission Rating | Requested Delivery Date | Factory Promised Delivery Date | Factory Bu | Sol | Purchase Order Accepted Date | Pre Delivery Inspection | Incoterm | Incoterm Location | Invoice Number | Commissioning | Length (in Mm) | Width (in Mm) | Height (in Mm) | Weight (in Kg) | Light | Payment Terms | Unit Gross Selling Price |

  Scenario: Get excel export with Negotiated Transfer Price should be possible for ROLE_PSM
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "equipment_records?notShipped=1&order%5Bid%5D=DESC&columns=id,serialNumber,model,type,endUser,buyer,airport.code,deliveredCountry,salesOrganisation,customerAssetNumber,order.customerPurchaseOrders,firstGreenTagDate,estimatedGreenTagDate,greenTagDate,yellowTagDate,lastEquipmentShippingRecord.estimatedPickUpDate,dateShipped,workOrder,emissionRating,orderFactory.requestedDeliveryDate,orderFactory.factoryPromisedDeliveryDate,orderFactory.orderLine.factory,orderFactory.orderLine.legacyId,orderFactory.orderLine.purchaseOrderAcceptedDate,orderFactory.orderLine.inspection,orderFactory.orderLine.incoterm.code,orderFactory.orderLine.incotermLocation,orderTransaction.invoice,orderFactory.commissioning,length,width,height,weight,light,orderFactory.orderLine.paymentTerms,negotiatedTransferPrice,currency,unitGrossSellingPrice"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Id | Serial Number | Model | Type | End User | Buyer | Airport Code | Delivered Country | Sales Organisation | Customer Asset Number | Customer Po | First Green Tag Date | Estimated Green Tag Date | Green Tag Date | Yellow Tag Date | Estimated Pick Up Date | Date Shipped | Work Order | Emission Rating | Requested Delivery Date | Factory Promised Delivery Date | Factory Bu | Sol | Purchase Order Accepted Date | Pre Delivery Inspection | Incoterm | Incoterm Location | Invoice Number | Commissioning | Length (in Mm) | Width (in Mm) | Height (in Mm) | Weight (in Kg) | Light | Payment Terms | Negotiated Transfer Price | Negotiated Tp Currency | Unit Gross Selling Price |

  Scenario: As a extranet user, I can update some ER fields for my equipment
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/equipment_records/1/extranet_update" with body:
    """
    {
        "@id": "/equipment_records/1",
        "customerSerialNumber": "MyExtranetAsset",
        "airport": "/airports/109",
        "serialNumber": "TryToChangeSerial"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_record/schemas/equipment_record.json"
    And the JSON node "customerSerialNumber" should be equal to the string "MyExtranetAsset"
    And the JSON node "serialNumber" should be equal to the string "SN_001"
    And the JSON node "airport.@id" should be equal to the string "/airports/109"
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/equipment_records/29/extranet_update" with body:
    """
    {
        "@id": "/equipment_records/29",
        "customerSerialNumber": "MyExtranetAsset",
        "airport": "/airports/109"
    }
    """
    Then the response status code should be 403