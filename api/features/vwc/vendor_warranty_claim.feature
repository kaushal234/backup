Feature: Test Vendor Warranty Claim Entity

  Scenario: Request all vendor warranty claims without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claims"
    Then the response status code should be 401

  Scenario: Request a single vendor warranty claim without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claims/1"
    Then the response status code should be 401

  Scenario: Request all vendor warranty claims as intranet user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claims"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_warranty_claim/schemas/vendor_warranty_claims.json"

  Scenario: Request a vendor warranty claim as intranet user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claims/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_warranty_claim/schemas/vendor_warranty_claim.json"

  Scenario: Request all vendor warranty claims as extranet user should not be permitted
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claims"
    Then the response status code should be 403

  Scenario: Request a vendor warranty claim as extranet user should not be permitted
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claims/1"
    Then the response status code should be 403

  Scenario: Request all vendor warranty claims as not granted authorized application
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claims"
    Then the response status code should be 403

  Scenario: Request a vendor warranty claim as not granted authorized application
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claims/1"
    Then the response status code should be 403

  Scenario: Request all vendor warranty claims as a vendor user
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claims"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_warranty_claim/schemas/vendor_warranty_claims.json"

  Scenario: Request a ncr vendor warranty claim as a vendor user should show ncr details
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claims/2"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_warranty_claim/schemas/vendor_warranty_claim_with_origin_detail.json"
    And the JSON node "activity" should have 1 element

  Scenario: Request a wc vendor warranty claim as a vendor user should show wc details
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claims/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_warranty_claim/schemas/vendor_warranty_claim_with_origin_detail.json"

  Scenario: Download excel vendor warranty claim should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "/purchasing/vendor_warranty_claims?columns=id,status,createdAt,factory,assignee,supplierName,supplierNumber,module,requestedSupplierAction,supplierCorrectiveActionRequest.id,partNumbers,requestedCreditAmount,supplierCreditAmount,actualCreditAmount,currency,failureType,processes"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Id | Status | Created At | Factory | Assignee | Supplier Name | Supplier Number | Module | Requested Supplier Action | Supplier Corrective Action Request | Part Numbers | Requested Credit Amount | Supplier Credit Amount | Actual Credit Amount | Currency | Failure Type | Processes |

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Purchasing\VendorWarrantyClaim" is exposed on the API
    Then the filter "supplierNumber" should be available and its type should be "string"
    Then the filter "order[id]" should be available and its type should be "string"
    Then the filter "order[requestedCreditAmount]" should be available and its type should be "string"
    Then the filter "order[status.name]" should be available and its type should be "string"
    Then the filter "order[createdAt]" should be available and its type should be "string"
    Then the filter "order[closedAt]" should be available and its type should be "string"
    Then the filter "order[statusUpdatedAt]" should be available and its type should be "string"
    Then the filter "order[supplierName]" should be available and its type should be "string"
    Then the filter "order[supplierNumber]" should be available and its type should be "string"
    Then the filter "order[assignee.lastname]" should be available and its type should be "string"
    Then the filter "order[requestedSupplierAction]" should be available and its type should be "string"
    Then the filter "order[supplierCorrectiveActionRequest.id]" should be available and its type should be "string"
    Then the filter "order[supplierCreditAmount]" should be available and its type should be "string"
    Then the filter "order[actualCreditAmount]" should be available and its type should be "string"
    Then the filter "order[currency.name]" should be available and its type should be "string"
    Then the filter "parts.partNumber" should be available and its type should be "string"
    Then the filter "parts.serialNumber" should be available and its type should be "string"
    Then the filter "supplierCreditAmount[gt]" should be available and its type should be "string"
    Then the filter "supplierErp" should be available and its type should be "int"
    Then the filter "closedAt[before]" should be available and its type should be "DateTimeInterface"
    Then the filter "createdAt[after]" should be available and its type should be "DateTimeInterface"
    Then the filter "createdAt[before]" should be available and its type should be "DateTimeInterface"
    Then the filter "closedAt[after]" should be available and its type should be "DateTimeInterface"
    Then the filter "vendorToRespondAt[after]" should be available and its type should be "DateTimeInterface"
    Then the filter "vendorToRespondAt[before]" should be available and its type should be "DateTimeInterface"
    Then the filter "status" should be available and its type should be "string"
    Then the filter "status.name" should be available and its type should be "string"
    Then the filter "location" should be available and its type should be "string"
    Then the filter "factory" should be available and its type should be "string"
    Then the filter "supplierNumber" should be available and its type should be "string"
    Then the filter "assignee" should be available and its type should be "string"
    Then the filter "assignee.department" should be available and its type should be "string"
    Then the filter "exists[assignee]" should be available and its type should be "bool"
    Then the filter "open" should be available and its type should be "bool"
    Then the filter "supplierName" should be available and its type should be "string"
    Then the filter "accepted" should be available and its type should be "bool"
    Then the filter "poster" should be available and its type should be "string"
    And the filter "columns" should be available and its type should be "string"
    Then the filter "warrantyClaimId" should be available and its type should be "int"
    Then the filter "nonConformityId" should be available and its type should be "int"

  Scenario: Request vendor warranty claims as intranet user by NCR id
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/ncr_vendor_warranty_claims?nonConformity.id=1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_warranty_claim/schemas/vendor_warranty_claims.json"

  Scenario: Request vendor warranty claims as intranet user by WC id
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/wc_vendor_warranty_claims?warrantyClaimId=58"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_warranty_claim/schemas/vendor_warranty_claims.json"

  Scenario: As basic user I can't create a NCR VWC
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/purchasing/ncr_vendor_warranty_claims" with body:
    """
    {}
    """
    Then the response status code should be 403

  Scenario: As user quality I can create a NCR VWC (but not with all properties) and it updates NCR with supplier number
    Given I authenticate as the intranet user "user-quality@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/purchasing/ncr_vendor_warranty_claims" with body:
    """
    {
      "nonConformity": "/quality/non_conformities/1",
      "supplierCorrectiveActionRequest": "/quality/supplier_corrective_action_requests/1",
      "scarRequested": true,
      "type": "/purchasing/vendor_warranty_claim_types/1",
      "location": "/locations/29",
      "poster": "/people/42",
      "assignee": "/people/12",
      "createdAt": "2099-04-19",
      "requestedCreditAmount": 159.99,
      "currency": "/finance/currencies/1",
      "requestedSupplierAction": "send a new one",
      "supplierReturnMerchandiseAuthorization": "just for test",
      "supplierCreditNote": "no",
      "supplierCreditAmount": 99.99,
      "actualCreditAmount": 1.99,
      "supplierShipperName": "fedex",
      "supplierShippingInstruction": "emballer dans une boite en papier",
      "supplierStockVerified": "les stocks sont bien vérifiés",
      "tldStockVerified": "ça m'a l'air bon la aussi",
      "issueOrigin": "des problèmes",
      "correctiveAction": "on a fait des trucs",
      "trackingNumber": "123456",
      "accepted": true,
      "costBreakdown": "yes",
      "shipBackDefectivePart": true,
      "resolution": "revolution",
      "supplierName": "Pouet Inc.",
      "supplierNumber": "CUM0008",
      "parts": [
        {
          "partNumber": "7400021",
          "description": "Willy Waller",
          "unitOfMeasure": "BTC",
          "quantity": 42,
          "standardCost": 99.99,
          "comment": "avec ton Willi Waller Two Thousand Six là, jamais manger des bonnes patates aura été aussi facile"
        }
      ]
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_warranty_claim/schemas/vendor_warranty_claim.json"
    And the JSON node "nonConformity.@id" should be equal to the string "/quality/non_conformities/1"
    And the JSON node "supplierCorrectiveActionRequest.@id" should be equal to the string "/quality/supplier_corrective_action_requests/1"
    And the JSON node "scarRequested" should be true
    And the JSON node "type.@id" should be equal to the string "/purchasing/vendor_warranty_claim_types/1"
    And the JSON node "location.@id" should be equal to the string "/locations/29"
    And the JSON node "poster.@id" should be equal to the string "/people/85"
    And the JSON node "currency.@id" should be equal to the string "/finance/currencies/1"
    And the JSON node "createdAt" should be newer than 1 minute ago
    And the JSON node "assignee.@id" should be equal to the string "/people/56"
    And the JSON node "requestedSupplierAction" should be equal to the string "send a new one"
    And the JSON node "requestedCreditAmount" should be equal to 159.99
    And the JSON node "supplierReturnMerchandiseAuthorization" should be null
    And the JSON node "supplierCreditNote" should be null
    And the JSON node "supplierCreditAmount" should be null
    And the JSON node "actualCreditAmount" should be null
    And the JSON node "supplierShippingInstruction" should be null
    And the JSON node "supplierStockVerified" should be equal to the string "les stocks sont bien vérifiés"
    And the JSON node "tldStockVerified" should be equal to the string "ça m'a l'air bon la aussi"
    And the JSON node "issueOrigin" should be equal to the string "des problèmes"
    And the JSON node "correctiveAction" should be equal to the string "on a fait des trucs"
    And the JSON node "trackingNumber" should be null
    And the JSON node "costBreakdown" should be null
    And the JSON node "shipBackDefectivePart" should be false
    And the JSON node "resolution" should be null
    And the JSON node "supplierName" should be equal to the string "CUMMINS DIESEL SC"
    And the JSON node "supplierNumber" should be equal to the string "CUM0008"
    And the JSON node "parts" should have 1 element
    And the JSON node "parts[0].partNumber" should be equal to "7400021"
    And the JSON node "activity" should have 1 element
    And the JSON node "status.name" should be equal to the string "PENDING"
    And a total of 3 requests has been sent to ION
    And an email should have been sent asynchronously with subject matching pattern "/VWC#\S+ has been created/"
    And this asynchronous email should be sent only to "user-mlm@tld.fr, user-quality@tld.fr"
    Given I authenticate as the intranet user "user-quality@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/non_conformities/1"
    Then the response status code should be 200
    And the JSON node "supplierNumber" should be equal to the string "CUM0008"
    And the JSON node "supplierName" should be equal to the string "CUMMINS DIESEL SC"

  Scenario: As user rme I can't create a NCR VWC
    Given I authenticate as the intranet user "user-rme@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/purchasing/ncr_vendor_warranty_claims" with body:
    """
    {}
    """
    Then the response status code should be 403

  Scenario: As user rme I can create a WC VWC (but not with all properties)
    Given I authenticate as the intranet user "user-rme@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/purchasing/wc_vendor_warranty_claims" with body:
    """
    {
      "warrantyClaimId": 13,
      "supplierCorrectiveActionRequest": "/quality/supplier_corrective_action_requests/1",
      "scarRequested": true,
      "type": "/purchasing/vendor_warranty_claim_types/1",
      "location": "/locations/29",
      "poster": "/people/42",
      "assignee": "/people/12",
      "createdAt": "2099-04-19",
      "requestedCreditAmount": 159.99,
      "requestedSupplierAction": "send a new one",
      "supplierReturnMerchandiseAuthorization": "just for test",
      "trackingNumber": "123456",
      "supplierCreditNote": "no",
      "supplierCreditAmount": 99.99,
      "actualCreditAmount": 1.99,
      "supplierShipperName": "fedex",
      "supplierShippingInstruction": "emballer dans une boite en papier",
      "accepted": true,
      "costBreakdown": "yes",
      "shipBackDefectivePart": true,
      "resolution": "revolution",
      "supplierName": "CUMMINS DIESEL SC",
      "supplierNumber": "DAN0013",
      "parts": [
        {
          "partNumber": "7400021",
          "description": "Willy Waller",
          "unitOfMeasure": "BTC",
          "quantity": 42,
          "standardCost": 99.99,
          "comment": "avec ton Willi Waller Two Thousand Six là, jamais manger des bonnes patates aura été aussi facile"
        }
      ]
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_warranty_claim/schemas/vendor_warranty_claim.json"
    And the JSON node "supplierCorrectiveActionRequest.@id" should be equal to the string "/quality/supplier_corrective_action_requests/1"
    And the JSON node "scarRequested" should be true
    And the JSON node "type.@id" should be equal to the string "/purchasing/vendor_warranty_claim_types/1"
    And the JSON node "location.@id" should be equal to the string "/locations/29"
    And the JSON node "poster.@id" should be equal to the string "/people/84"
    And the JSON node "createdAt" should be newer than 1 minute ago
    And the JSON node "assignee.@id" should be equal to the string "/people/29"
    And the JSON node "requestedSupplierAction" should be equal to the string "send a new one"
    And the JSON node "requestedCreditAmount" should be equal to 159.99
    And the JSON node "supplierReturnMerchandiseAuthorization" should be null
    And the JSON node "trackingNumber" should be null
    And the JSON node "supplierCreditNote" should be null
    And the JSON node "supplierCreditAmount" should be null
    And the JSON node "supplierShipperName" should be null
    And the JSON node "actualCreditAmount" should be null
    And the JSON node "supplierShippingInstruction" should be null
    And the JSON node "costBreakdown" should be null
    And the JSON node "shipBackDefectivePart" should be false
    And the JSON node "resolution" should be null
    And the JSON node "supplierName" should be equal to the string "DANA SAS FRANCE"
    And the JSON node "supplierNumber" should be equal to "DAN0013"
    And the JSON node "parts" should have 1 element
    And the JSON node "accepted" should be false
    And the JSON node "parts[0].partNumber" should be equal to "7400021"
    And the JSON node "activity" should have 1 element
    And the JSON node "status.name" should be equal to the string "QA ANALYSIS"
    And a total of 3 requests has been sent to ION
    And an email should have been sent asynchronously with subject matching pattern "/VWC#\S+ has been created/"
    And this asynchronous email should be sent only to "user-mlm@tld.fr, user-quality@tld.fr, user-qam@tld.fr"

  Scenario: As user quality I can't create a WC VWC
    Given I authenticate as the intranet user "user-quality@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/purchasing/wc_vendor_warranty_claims" with body:
    """
    {}
    """
    Then the response status code should be 403

  Scenario: As user basic I can't update a VWC
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/ncr_vendor_warranty_claims/4" with body:
    """
    {}
    """
    Then the response status code should be 403

  Scenario: As not granted authorized app I can't update a VWC
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/ncr_vendor_warranty_claims/4" with body:
    """
    {}
    """
    Then the response status code should be 403

  Scenario: As user accountant I can update a VWC but not all properties
    Given I authenticate as the intranet user "user-accountant@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/ncr_vendor_warranty_claims/6" with body:
    """
    {
      "nonConformity": "/quality/non_conformities/1",
      "supplierCorrectiveActionRequest": "/quality/supplier_corrective_action_requests/2",
      "scarRequested": false,
      "type": "/purchasing/vendor_warranty_claim_types/2",
      "location": "/locations/30",
      "poster": "/people/43",
      "assignee": "/people/13",
      "currency": "/finance/currencies/2",
      "createdAt": "2099-04-29",
      "requestedCreditAmount": 160,
      "requestedSupplierAction": "send a new two",
      "supplierReturnMerchandiseAuthorization": "just for test, but updated",
      "trackingNumber": "123456",
      "supplierCreditNote": "yes",
      "supplierCreditAmount": 199.99,
      "actualCreditAmount": 11.99,
      "supplierShipperName": "fedex",
      "supplierShippingInstruction": "emballer dans une boite en papier, mais mise à jour",
      "accepted": true,
      "costBreakdown": "no",
      "shipBackDefectivePart": true,
      "resolution": "evolution",
      "supplierName": "CUMMINS DIESEL SC",
      "supplierNumber": "DAN0013",
      "parts": [
        {
            "partNumber": "7400021",
            "description": "Willy Waller 2006",
            "unitOfMeasure": "BTC",
            "quantity": 42,
            "standardCost": 99.99,
            "comment": "avec ton Willi Waller Two Thousand Six là, jamais manger des bonnes patates aura été aussi facile"
        }
      ]
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_warranty_claim/schemas/vendor_warranty_claim.json"
    And the JSON node "nonConformity.@id" should be equal to the string "/quality/non_conformities/1"
    And the JSON node "supplierCorrectiveActionRequest.@id" should be equal to the string "/quality/supplier_corrective_action_requests/2"
    And the JSON node "scarRequested" should be false
    And the JSON node "type.@id" should be equal to the string "/purchasing/vendor_warranty_claim_types/2"
    And the JSON node "location.@id" should be equal to the string "/locations/30"
    And the JSON node "poster.@id" should be equal to the string "/people/85"
    And the JSON node "currency.@id" should be equal to the string "/finance/currencies/2"
    And the JSON node "assignee.@id" should be equal to the string "/people/13"
    And the JSON node "requestedSupplierAction" should be equal to the string "send a new two"
    And the JSON node "requestedCreditAmount" should be equal to 160
    And the JSON node "supplierReturnMerchandiseAuthorization" should be null
    And the JSON node "trackingNumber" should be null
    And the JSON node "supplierCreditNote" should be null
    And the JSON node "supplierCreditAmount" should be null
    And the JSON node "actualCreditAmount" should be equal to 11.99
    And the JSON node "supplierShipperName" should be equal to the string "fedex"
    And the JSON node "supplierShippingInstruction" should be null
    And the JSON node "costBreakdown" should be equal to the string "no"
    And the JSON node "shipBackDefectivePart" should be false
    And the JSON node "resolution" should be null
    And the JSON node "supplierName" should be equal to the string "DANA SAS FRANCE"
    And the JSON node "supplierNumber" should be equal to "DAN0013"
    And the JSON node "accepted" should be false
    And the JSON node "parts[0].description" should be equal to the string "Willy Waller 2006"
    And the JSON node "activity" should have 2 elements
    And a total of 3 requests has been sent to ION
    And an email should have been sent asynchronously with subject matching pattern "/VWC#\S+ - Assignee Change/"
    And this asynchronous email should be sent only to "user-hr@tld.fr"
    And this asynchronous email should be sent as cc to "user-mlm@tld.fr"

  Scenario: As basic user I should be able to change all status of VWC except for QA ANALYSIS
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/ncr_vendor_warranty_claims/6/status" with body:
    """
    {
      "status": "/purchasing/vendor_warranty_claim_statuses/1"
    }
    """
    Then the response status code should be 200
    And the JSON node "status.name" should be equal to the string "QA ANALYSIS"
    And the JSON node "assignee.@id" should be equal to the string "/people/29"
    And a total of 3 requests has been sent to ION
    And an email should have been sent asynchronously with subject matching pattern "/VWC#\S+ status changed from PENDING to QA ANALYSIS/"
    And this asynchronous email should be sent to "user-qam@tld.fr"
    And no email should have been sent asynchronously with subject matching pattern "/VWC#\S+ - Assignee Change/"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/ncr_vendor_warranty_claims/6/status" with body:
    """
    {
      "status": "/purchasing/vendor_warranty_claim_statuses/2"
    }
    """
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/ncr_vendor_warranty_claims/6/status" with body:
    """
    {
      "status": "/purchasing/vendor_warranty_claim_statuses/2"
    }
    """
    Then the response status code should be 200
    And the JSON node "status.name" should be equal to the string "PENDING"
    And the JSON node "assignee.@id" should be equal to the string "/people/60"
    And an update log should have been inserted on resource "/purchasing/ncr_vendor_warranty_claims/6" with a changeset on the property "status" with values "QA ANALYSIS (/purchasing/vendor_warranty_claim_statuses/1)", "PENDING (/purchasing/vendor_warranty_claim_statuses/2)"
    And a total of 3 requests has been sent to ION
    And an email should have been sent asynchronously with subject matching pattern "/VWC#\S+ status changed from QA ANALYSIS to PENDING/"
    And this asynchronous email should be sent to "user-buyer@tld.fr"
    And no email should have been sent asynchronously with subject matching pattern "/VWC#\S+ - Assignee Change/"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/ncr_vendor_warranty_claims/6/status" with body:
    """
    {
      "status": "/purchasing/vendor_warranty_claim_statuses/3"
    }
    """
    Then the response status code should be 200
    And the JSON node "status.name" should be equal to the string "VENDOR_TO_RESPOND"
    And the JSON node "vendorToRespondAt" should be newer than 1 minute ago
    And the JSON node "assignee.@id" should be equal to the string "/people/60"
    And a total of 3 requests has been sent to ION
    And an email should have been sent asynchronously with subject matching pattern "/VWC#\S+ status changed from PENDING to VENDOR_TO_RESPOND/"
    And this asynchronous email should be sent only to "user-buyer@tld.fr, user-quality@tld.fr"
    And an email should have been sent asynchronously with subject matching pattern "/VWC#\S+, Pending RMA#/"
    And this asynchronous email should be sent to "devteam@tld-america.com"
    And this asynchronous email should be sent to "user-buyer@tld.fr"
    And no email should have been sent asynchronously with subject matching pattern "/VWC#\S+ - Assignee Change/"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/ncr_vendor_warranty_claims/6/status" with body:
    """
    {
      "status": "/purchasing/vendor_warranty_claim_statuses/4"
    }
    """
    Then the response status code should be 200
    And the JSON node "status.name" should be equal to the string "REVIEW_VENDOR_RESPONSE"
    And the JSON node "assignee.@id" should be equal to the string "/people/60"
    And a total of 3 requests has been sent to ION
    And an email should have been sent asynchronously with subject matching pattern "/VWC#\S+ status changed from VENDOR_TO_RESPOND to REVIEW_VENDOR_RESPONSE/"
    And this asynchronous email should be sent to "user-buyer@tld.fr"
    And no email should have been sent asynchronously with subject matching pattern "/VWC#\S+ - Assignee Change/"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/ncr_vendor_warranty_claims/6/status" with body:
    """
    {
      "status": "/purchasing/vendor_warranty_claim_statuses/5"
    }
    """
    Then the response status code should be 200
    And the JSON node "status.name" should be equal to the string "CREATE_PO"
    And the JSON node "assignee.@id" should be equal to the string "/people/60"
    And a total of 3 requests has been sent to ION
    And an email should have been sent asynchronously with subject matching pattern "/VWC#\S+ status changed from REVIEW_VENDOR_RESPONSE to CREATE_PO/"
    And this asynchronous email should be sent to "user-buyer@tld.fr"
    And no email should have been sent asynchronously with subject matching pattern "/VWC#\S+ - Assignee Change/"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/ncr_vendor_warranty_claims/6/status" with body:
    """
    {
      "status": "/purchasing/vendor_warranty_claim_statuses/6"
    }
    """
    Then the response status code should be 200
    And the JSON node "status.name" should be equal to the string "SHIP_TO_VENDOR"
    And the JSON node "assignee.@id" should be equal to the string "/people/39"
    And a total of 3 requests has been sent to ION
    And an email should have been sent asynchronously with subject matching pattern "/VWC#\S+ status changed from CREATE_PO to SHIP_TO_VENDOR/"
    And this asynchronous email should be sent to "user-parts@tld.fr"
    And no email should have been sent asynchronously with subject matching pattern "/VWC#\S+ - Assignee Change/"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/ncr_vendor_warranty_claims/6/status" with body:
    """
    {
      "status": "/purchasing/vendor_warranty_claim_statuses/7"
    }
    """
    Then the response status code should be 200
    And the JSON node "status.name" should be equal to the string "ISSUE_CREDIT_NOTE"
    And the JSON node "assignee.@id" should be equal to the string "/people/76"
    And a total of 3 requests has been sent to ION
    And an email should have been sent asynchronously with subject matching pattern "/VWC#\S+ status changed from SHIP_TO_VENDOR to ISSUE_CREDIT_NOTE/"
    And this asynchronous email should be sent to "user-accountant@tld.fr"
    And no email should have been sent asynchronously with subject matching pattern "/VWC#\S+ - Assignee Change/"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/ncr_vendor_warranty_claims/6/status" with body:
    """
    {
      "status": "/purchasing/vendor_warranty_claim_statuses/8"
    }
    """
    Then the response status code should be 200
    And the JSON node "status.name" should be equal to the string "REC_FROM_VENDOR"
    And the JSON node "assignee.@id" should be equal to the string "/people/80"
    And a total of 3 requests has been sent to ION
    And an email should have been sent asynchronously with subject matching pattern "/VWC#\S+ status changed from ISSUE_CREDIT_NOTE to REC_FROM_VENDOR/"
    And this asynchronous email should be sent to "user-ws@tld.fr"
    And no email should have been sent asynchronously with subject matching pattern "/VWC#\S+ - Assignee Change/"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/ncr_vendor_warranty_claims/6/status" with body:
    """
    {
      "status": "/purchasing/vendor_warranty_claim_statuses/9"
    }
    """
    Then the response status code should be 200
    And the JSON node "status.name" should be equal to the string "ISSUE_DEBIT_NOTE"
    And the JSON node "assignee.@id" should be equal to the string "/people/76"
    And a total of 3 requests has been sent to ION
    And an email should have been sent asynchronously with subject matching pattern "/VWC#\S+ status changed from REC_FROM_VENDOR to ISSUE_DEBIT_NOTE/"
    And this asynchronous email should be sent to "user-accountant@tld.fr"
    And no email should have been sent asynchronously with subject matching pattern "/VWC#\S+ - Assignee Change/"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/ncr_vendor_warranty_claims/6/status" with body:
    """
    {
      "status": "/purchasing/vendor_warranty_claim_statuses/10"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].message" should be equal to the string "Status not allowed, VWC does not required a SCAR."
    And no email should have been sent asynchronously
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/ncr_vendor_warranty_claims/6/status" with body:
    """
    {
      "status": "/purchasing/vendor_warranty_claim_statuses/11",
      "resolution": "resolved"
    }
    """
    Then the response status code should be 200
    And the JSON node "status.name" should be equal to the string "CLOSED_RESOLVED"
    And the JSON node "resolution" should be equal to the string "resolved"
    And the JSON node "assignee.@id" should be equal to the string "/people/56"
    And a total of 3 requests has been sent to ION
    And an email should have been sent asynchronously with subject matching pattern "/VWC#\S+ status changed from ISSUE_DEBIT_NOTE to CLOSED_RESOLVED/"
    And this asynchronous email should be sent to "user-mlm@tld.fr"
    And no email should have been sent asynchronously with subject matching pattern "/VWC#\S+ - Assignee Change/"

  Scenario: As a vendor user, I can't change status of vwc if current status is VENDOR_TO_RESPOND
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/ncr_vendor_warranty_claims/6/status" with body:
    """
    {
      "status": "/purchasing/vendor_warranty_claim_statuses/4"
    }
    """
    Then the response status code should be 403

  Scenario: As vendor user, I can only see VWC of my supplier for some statuses
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/wc_vendor_warranty_claims/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_warranty_claim/schemas/vendor_warranty_claim_with_origin_detail.json"
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/wc_vendor_warranty_claims/4"
    Then the response status code should be 404
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claims"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_warranty_claim/schemas/vendor_warranty_claims.json"
    And the JSON node "hydra:totalItems" should be equal to "2"

  Scenario: As basic user, I can't reopen a closed VWC
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/ncr_vendor_warranty_claims/6/status" with body:
    """
    {
      "status": "/purchasing/vendor_warranty_claim_statuses/3"
    }
    """
    Then the response status code should be 403

  Scenario: As user mlm, I can reopen a closed VWC
    Given I authenticate as the intranet user "user-mlm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/ncr_vendor_warranty_claims/6/status" with body:
    """
    {
      "status": "/purchasing/vendor_warranty_claim_statuses/3"
    }
    """
    Then the response status code should be 200
    And the JSON node "status.name" should be equal to the string "VENDOR_TO_RESPOND"
    And the JSON node "assignee.@id" should be equal to the string "/people/60"
    And a total of 3 requests has been sent to ION
    And an email should have been sent asynchronously with subject matching pattern "/VWC#\S+ status changed from CLOSED_RESOLVED to VENDOR_TO_RESPOND/"
    And this asynchronous email should be sent to "user-buyer@tld.fr"
    And no email should have been sent asynchronously with subject matching pattern "/VWC#\S+ - Assignee Change/"

  Scenario: As a vendor user, I can change status to REVIEW_VENDOR_RESPONSE with comment or update
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/comments" with parameters:
      | key             | value                                                                 |
      | resource        | /purchasing/ncr_vendor_warranty_claims/6                              |
      | message         | this is a response from authorized app                                |
      | metadata        | {"lastname": "eve", "firstname": "n'dors", "email":"toto@vendor.com"} |
      | file            | @file.pdf                                                             |
    Then the response status code should be 201
    And a total of 3 requests has been sent to ION
    And an email should have been sent asynchronously with subject matching pattern "/VWC#\S+ status changed from VENDOR_TO_RESPOND to REVIEW_VENDOR_RESPONSE/"
    And this asynchronous email should be sent to "user-buyer@tld.fr"
    And an email should have been sent asynchronously with subject matching pattern "/VWC#\S+, eVendor message from vendor.user@vendor.fr/"
    And this asynchronous email should be sent only to "user-buyer@tld.fr"
    And this asynchronous email should be sent as cc to "user-quality@tld.fr"
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/ncr_vendor_warranty_claims/6"
    Then the response status code should be 200
    And the JSON node "status.name" should be equal to the string "REVIEW_VENDOR_RESPONSE"
    And the JSON node "assignee.@id" should be equal to the string "/people/60"
    #to put back VWC in status VENDOR_TO_RESPOND so the next test works
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/ncr_vendor_warranty_claims/6/status" with body:
    """
    {
      "status": "/purchasing/vendor_warranty_claim_statuses/3"
    }
    """
    Then the response status code should be 200
    And the JSON node "status.name" should be equal to the string "VENDOR_TO_RESPOND"
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/ncr_vendor_warranty_claims/6/status" with body:
    """
    {
      "status": "/purchasing/vendor_warranty_claim_statuses/4"
    }
    """
    Then the response status code should be 200
    And the JSON node "status.name" should be equal to the string "REVIEW_VENDOR_RESPONSE"
    And the JSON node "assignee.@id" should be equal to the string "/people/60"
     #to put back VWC in status VENDOR_TO_RESPOND so the next test works
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/ncr_vendor_warranty_claims/6/status" with body:
    """
    {
      "status": "/purchasing/vendor_warranty_claim_statuses/3"
    }
    """
    Then the response status code should be 200
    And the JSON node "status.name" should be equal to the string "VENDOR_TO_RESPOND"

  Scenario: As granted vendor user I can update a VWC but not all properties
    Given I authenticate as the "evendors" user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/ncr_vendor_warranty_claims/6" with body:
    """
    {
      "nonConformity": "/quality/non_conformities/2",
      "supplierCorrectiveActionRequest": "/quality/supplier_corrective_action_requests/1",
      "scarRequested": true,
      "type": "/purchasing/vendor_warranty_claim_types/1",
      "location": "/locations/29",
      "poster": "/people/43",
      "assignee": "/people/14",
      "createdAt": "2099-04-29",
      "requestedCreditAmount": 159.99,
      "requestedSupplierAction": "send a new three",
      "supplierReturnMerchandiseAuthorization": "just for test, but updated twice",
      "trackingNumber": "123456",
      "supplierCreditNote": "yes",
      "supplierCreditAmount": 200.99,
      "actualCreditAmount": 12,
      "supplierShipperName": "ups",
      "supplierShippingInstruction": "emballer dans une boite en papier, mais mise à jour 2 fois",
      "accepted": false,
      "costBreakdown": "yes",
      "shipBackDefectivePart": false,
      "resolution": "evolution",
      "supplierName": "TOTO",
      "supplierNumber": "CU5000",
      "parts": []
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_warranty_claim/schemas/vendor_warranty_claim_with_origin_detail.json"
    And the JSON node "nonConformity.@id" should be equal to the string "/quality/non_conformities/1"
    And the JSON node "supplierCorrectiveActionRequest.@id" should be equal to the string "/quality/supplier_corrective_action_requests/2"
    And the JSON node "scarRequested" should be false
    And the JSON node "type.@id" should be equal to the string "/purchasing/vendor_warranty_claim_types/2"
    And the JSON node "location.@id" should be equal to the string "/locations/30"
    And the JSON node "poster.@id" should be equal to the string "/people/85"
    And the JSON node "assignee.@id" should be equal to the string "/people/60"
    And the JSON node "requestedSupplierAction" should be equal to the string "send a new two"
    And the JSON node "requestedCreditAmount" should be equal to 160
    And the JSON node "supplierReturnMerchandiseAuthorization" should be equal to the string "just for test, but updated twice"
    And the JSON node "trackingNumber" should be null
    And the JSON node "supplierCreditNote" should be null
    And the JSON node "supplierCreditAmount" should be equal to 200.99
    And the JSON node "actualCreditAmount" should be equal to 11.99
    And the JSON node "supplierShipperName" should be equal to the string "fedex"
    And the JSON node "supplierShippingInstruction" should be equal to the string "emballer dans une boite en papier, mais mise à jour 2 fois"
    And the JSON node "costBreakdown" should be equal to the string "no"
    And the JSON node "shipBackDefectivePart" should be false
    And the JSON node "resolution" should be equal to the string "resolved"
    And the JSON node "supplierName" should be equal to the string "DANA SAS FRANCE"
    And the JSON node "supplierNumber" should be equal to "DAN0013"
    And the JSON node "accepted" should be false
    And the JSON node "parts" should have 1 element
    And the JSON node "activity" should have 21 elements
    And a total of 3 requests has been sent to ION

  Scenario: As user ap I can update a VWC but not all properties
    Given I authenticate as the intranet user "user-ap@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/ncr_vendor_warranty_claims/6" with body:
    """
    {
      "nonConformity": "/quality/non_conformities/2",
      "supplierCorrectiveActionRequest": "/quality/supplier_corrective_action_requests/1",
      "scarRequested": true,
      "type": "/purchasing/vendor_warranty_claim_types/1",
      "location": "/locations/29",
      "poster": "/people/44",
      "assignee": "/people/12",
      "requestedCreditAmount": 161,
      "requestedSupplierAction": "send a new one",
      "supplierReturnMerchandiseAuthorization": "just for test, no update",
      "trackingNumber": "123456",
      "supplierCreditNote": "no",
      "supplierCreditAmount": 200,
      "actualCreditAmount": 11.99,
      "supplierShipperName": "fedex",
      "supplierShippingInstruction": "emballer dans une boite en papier",
      "accepted": true,
      "costBreakdown": "yes",
      "shipBackDefectivePart": true,
      "resolution": "revolution",
      "supplierName": "CUMMINS DIESEL SC",
      "supplierNumber": "CU2000",
      "parts": []
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_warranty_claim/schemas/vendor_warranty_claim.json"
    And the JSON node "nonConformity.@id" should be equal to the string "/quality/non_conformities/1"
    And the JSON node "supplierCorrectiveActionRequest.@id" should be equal to the string "/quality/supplier_corrective_action_requests/2"
    And the JSON node "scarRequested" should be false
    And the JSON node "type.@id" should be equal to the string "/purchasing/vendor_warranty_claim_types/2"
    And the JSON node "location.@id" should be equal to the string "/locations/30"
    And the JSON node "poster.@id" should be equal to the string "/people/85"
    And the JSON node "assignee.@id" should be equal to the string "/people/60"
    And the JSON node "requestedSupplierAction" should be equal to the string "send a new two"
    And the JSON node "requestedCreditAmount" should be equal to 160
    And the JSON node "supplierReturnMerchandiseAuthorization" should be equal to the string "just for test, but updated twice"
    And the JSON node "supplierCreditNote" should be equal to the string "no"
    And the JSON node "supplierCreditAmount" should be equal to 200
    And the JSON node "actualCreditAmount" should be equal to 11.99
    And the JSON node "supplierShipperName" should be equal to the string "fedex"
    And the JSON node "supplierShippingInstruction" should be equal to the string "emballer dans une boite en papier, mais mise à jour 2 fois"
    And the JSON node "trackingNumber" should be null
    And the JSON node "costBreakdown" should be equal to the string "no"
    And the JSON node "shipBackDefectivePart" should be false
    And the JSON node "resolution" should be equal to the string "resolved"
    And the JSON node "accepted" should be false
    And the JSON node "supplierName" should be equal to the string "DANA SAS FRANCE"
    And the JSON node "supplierNumber" should be equal to "DAN0013"
    And the JSON node "parts" should have 1 element
    And the JSON node "activity" should have 22 elements
    And a total of 3 requests has been sent to ION

  Scenario: As user qam I can update shipping properties of VWC
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/ncr_vendor_warranty_claims/6" with body:
    """
    {
      "supplierReturnMerchandiseAuthorization": "just for test, but updated",
      "trackingNumber": "123456",
      "supplierShippingInstruction": "emballer dans une boite en papier, mais mise à jour"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_warranty_claim/schemas/vendor_warranty_claim.json"
    And the JSON node "supplierReturnMerchandiseAuthorization" should be equal to the string "just for test, but updated"
    And the JSON node "trackingNumber" should be equal to "123456"
    And the JSON node "supplierShippingInstruction" should be equal to the string "emballer dans une boite en papier, mais mise à jour"
    And the JSON node "activity" should have 23 elements

  Scenario: As user psm I can update assignee and an email is sent
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/ncr_vendor_warranty_claims/6" with body:
    """
    {
      "assignee": "/people/39"
    }
    """
    Then the response status code should be 200
    And the JSON node "assignee.@id" should be equal to the string "/people/39"
    And an email should have been sent asynchronously with subject matching pattern "/VWC#\S+ - Assignee Change/"
    And this asynchronous email should be sent only to "user-parts@tld.fr"

  Scenario: As basic user I can post an internal comment on a VWC
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/comments" with body:
    """
    {
      "resource": "/purchasing/ncr_vendor_warranty_claims/6",
      "message": "just a comment",
      "public": false
    }
    """
    Then the response status code should be 201
    And an email should have been sent asynchronously with subject matching pattern "/VWC#\S+ - TLD Internal Communication/"
    And this asynchronous email should be sent only to "user-parts@tld.fr"
    And this asynchronous email should be sent as cc to "user-basic@tld.fr"
    And this asynchronous email should be sent as cc to "user-qam@tld.fr"
    And a "txBusinessPartner" SOAP client has been created
    And this client has been called on the operation "txShow" with the following request:
    """
    {
      "DataArea": {
        "txBusinessPartner": {
          "code": "DAN0013"
        }
      }
    }
    """
    And a total of 1 request has been sent to ION
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    And I send a "GET" request to "/purchasing/ncr_vendor_warranty_claims/6"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_warranty_claim/schemas/vendor_warranty_claim.json"
    #it only displays public comments for external users so it should still be 25
    And the JSON node "activity" should have 25 elements

  Scenario: As basic user I can post a public comment on a VWC
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/comments" with body:
    """
    {
      "resource": "/purchasing/ncr_vendor_warranty_claims/6",
      "message": "just a comment",
      "public": true,
      "metadata": {"recipients":["toto@vendor.com"]}
    }
    """
    Then the response status code should be 201
    And an email should have been sent asynchronously with subject matching pattern "/TLD eVendor message/"
    And this asynchronous email should be sent only to "toto@vendor.com"
    And this asynchronous email should be sent as cc to "user-basic@tld.fr"
    And this asynchronous email should be sent as cc to "user-parts@tld.fr"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    And I send a "GET" request to "/purchasing/ncr_vendor_warranty_claims/6"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_warranty_claim/schemas/vendor_warranty_claim.json"
    And the JSON node "activity" should have 26 elements

  Scenario: As a vendor user, I can comment a vwc
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/comments" with parameters:
      | key             | value                                                                 |
      | resource        | /purchasing/ncr_vendor_warranty_claims/6                              |
      | message         | this is a response from authorized app                                |
      | metadata        | {"lastname": "eve", "firstname": "n'dors", "email":"toto@vendor.com"} |
      | file            | @file.pdf                                                             |
    Then the response status code should be 201
    And an email should have been sent asynchronously with subject matching pattern "/VWC#\S+, eVendor message from vendor.user@vendor.fr/"
    And this asynchronous email should be sent only to "user-buyer@tld.fr"
    And this asynchronous email should be sent as cc to "user-quality@tld.fr"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    And I send a "GET" request to "/purchasing/ncr_vendor_warranty_claims/6"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_warranty_claim/schemas/vendor_warranty_claim.json"
    And the JSON node "activity" should have 28 elements

  Scenario: As granted vendor user I can comment a vwc
    Given I authenticate as the "evendors" user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/comments" with parameters:
      | key             | value                                                                 |
      | resource        | /purchasing/ncr_vendor_warranty_claims/6                              |
      | message         | this is a response from vendor                                        |
      | metadata        | {"lastname": "eve", "firstname": "n'dors"} |
      | file            | @file.pdf                                                             |
    Then the response status code should be 201
    And an email should have been sent asynchronously with subject matching pattern "/VWC#\S+, eVendor message from vendor.user@vendor.fr/"
    And this asynchronous email should be sent only to "user-buyer@tld.fr"
    Given I authenticate as the "evendors" user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    And I send a "GET" request to "/purchasing/ncr_vendor_warranty_claims/6"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_warranty_claim/schemas/vendor_warranty_claim.json"
    And the JSON node "activity" should have 28 elements

  Scenario: As not granted authorized application I can' comment a vwc for conversation
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/comments" with parameters:
      | key             | value                                      |
      | resource        | /purchasing/ncr_vendor_warranty_claims/6   |
      | message         | this is a response from vendor             |
      | metadata        | {"lastname": "eve", "firstname": "n'dors"} |
      | file            | @file.pdf                                  |
    Then the response status code should be 403

  Scenario: As granted user when I update the NCR VWC supplier information it update the NCR
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/ncr_vendor_warranty_claims/4" with body:
    """
    {
      "supplierNumber": "DAN0013"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_warranty_claim/schemas/vendor_warranty_claim.json"
    And the JSON node "supplierName" should be equal to the string "DANA SAS FRANCE"
    And the JSON node "supplierNumber" should be equal to "DAN0013"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/non_conformities/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/non_conformity/schemas/non_conformity.json"
    And the JSON node "supplierName" should be equal to the string "DANA SAS FRANCE"
    And the JSON node "supplierNumber" should be equal to "DAN0013"

  @resetFileTable
  Scenario: As a basic user, I can upload a file to a vendor warranty claim
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/purchasing/vendor_warranty_claims/1/files" with file "file" "file.doc"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"

  Scenario: As a vendor user, I can upload a file to a vendor warranty claim
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/purchasing/vendor_warranty_claims/1/files" with file "file" "file.doc"
    Then the response status code should be 201

  Scenario: As not granted authorized app, I can't upload a file to a vendor warranty claim
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/purchasing/vendor_warranty_claims/1/files" with file "file" "file.doc"
    Then the response status code should be 403

  Scenario: Upload an invalid file to a vendor warranty claim
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/purchasing/vendor_warranty_claims/1/files" with file "file" "image.gif"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "files: The mime type of the file is invalid"
    And the JSON node "violations[0].propertyPath" should be equal to "files"
    And the JSON node "violations[0].message" should contain "The mime type of the file is invalid"

  Scenario: As user pse I can change visibility of a file of a vendor warranty claim
    Given I authenticate as the intranet user "user-pse@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claims/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_warranty_claim/schemas/vendor_warranty_claim.json"
    And the JSON node "files[0].public" should be false
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/files/1" with body:
    """
    {
      "public": true
    }
    """
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-pse@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/files/1" with body:
    """
    {
      "public": true
    }
    """
    Then the response status code should be 204
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claims/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_warranty_claim/schemas/vendor_warranty_claim.json"
    And the JSON node "files[0].public" should be true
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/files/1" with body:
    """
    {
      "public": false
    }
    """
    Then the response status code should be 204
    Given I authenticate as the intranet user "user-qe@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/files/1" with body:
    """
    {
      "public": true
    }
    """
    Then the response status code should be 204

  Scenario: Download a vendor warranty claim attached file as an extranet user should not be possible
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claims/1/files/1"
    Then the response status code should be 403

  Scenario: Download a vendor warranty claim attached file as an not granted authorized app should not be possible
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claims/1/files/1"
    Then the response status code should be 403

  Scenario: Download a vendor warranty claim attached file as a basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claims/1/files/1"
    Then the response status code should be 200

  Scenario: Download a warranty claim attached file as an extranet user should not be possible
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/wc_vendor_warranty_claims/2/wc_files?fileId=60"
    Then the response status code should be 403

  Scenario: Download a vendor warranty claim attached file as an not granted authorized app should not be possible
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/wc_vendor_warranty_claims/2/wc_files?fileId=60"
    Then the response status code should be 403

  Scenario: Download a wc legacy attached file on a vendor warranty claim as a basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/wc_vendor_warranty_claims/2/wc_files?fileId=60"
    Then the response status code should be 200

  Scenario: Download a toc legacy attached file on a vendor warranty claim as a basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/wc_vendor_warranty_claims/2/toc_files?fileId=61"
    Then the response status code should be 200

  Scenario: Download a vendor warranty claim attached file as granted authorized app
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/files/1" with body:
    """
    {
      "public": true
    }
    """
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claims/1/files/1"
    Then the response status code should be 200

  Scenario: As a basic user, I can't delete a file from a vendor warranty claim
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/purchasing/vendor_warranty_claims/1/files/1"
    Then the response status code should be 403

  Scenario: As vendor user, I can't delete a file from a vendor warranty claim
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/purchasing/vendor_warranty_claims/1/files/1"
    Then the response status code should be 403

  Scenario: As a user quality, I can delete a file from a vendor warranty claim
    Given I authenticate as the intranet user "user-quality@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/purchasing/vendor_warranty_claims/1/files/1"
    Then the response status code should be 204

  Scenario: Change main file of a wc vwc with no permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/purchasing/wc_vendor_warranty_claims/1/main_file" with file "file" "image_1200x1200.jpg"
    Then the response status code should be 403

  Scenario: Change main file of a wc vwc with permission OK
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/purchasing/wc_vendor_warranty_claims/1/main_file" with file "file" "image_1200x1200.jpg"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"

  Scenario: Basic users can't delete a wc vwc main file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/purchasing/wc_vendor_warranty_claims/1/main_file/3"
    Then the response status code should be 403

  Scenario: Superusers can delete a wc vwc main file
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I send a "DELETE" request to "/purchasing/wc_vendor_warranty_claims/1/main_file/3"
    Then the response status code should be 204
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/wc_vendor_warranty_claims/1"
    And the response status code should be 200
    And the JSON node "mainFile" should be null

  Scenario: As a basic user, I can't delete a vwc
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/purchasing/vendor_warranty_claims/3"
    Then the response status code should be 403

  Scenario: As a superuser, I can delete a vwc
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/purchasing/vendor_warranty_claims/3"
    Then the response status code should be 204

  Scenario: As granted user I cant closed a VWC with CLOSED_RESOLVED(11) status
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/ncr_vendor_warranty_claims/4/status" with body:
    """
    {
      "status": "/purchasing/vendor_warranty_claim_statuses/11"
    }
    """
    Then the response status code should be 200
    And the JSON node "status.name" should be equal to the string "CLOSED_RESOLVED"

  Scenario: As granted user I can closed a VWC with CLOSED_LOW_VALUE(12) status
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/ncr_vendor_warranty_claims/4/status" with body:
    """
    {
      "status": "/purchasing/vendor_warranty_claim_statuses/12"
    }
    """
    Then the response status code should be 200
    And the JSON node "status.name" should be equal to the string "CLOSED_LOW_VALUE"

  Scenario: As granted user I can change VWC status to CLOSED_VENDOR_REJECTED(13)
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/ncr_vendor_warranty_claims/4/status" with body:
    """
    {
      "status": "/purchasing/vendor_warranty_claim_statuses/13"
    }
    """
    Then the response status code should be 200
    And the JSON node "status.name" should be equal to the string "CLOSED_VENDOR_REJECTED"

  Scenario: As granted user I can VWC change status to CLOSED_NOT_VENDOR_ISSUE(14)
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/ncr_vendor_warranty_claims/6/status" with body:
    """
    {
      "status": "/purchasing/vendor_warranty_claim_statuses/14"
    }
    """
    Then the response status code should be 200
    And the JSON node "status.name" should be equal to the string "CLOSED_NOT_VENDOR_ISSUE"

  Scenario: Get total claim amount based on location as basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claims_statistics?location=24"
    Then the response status code should be 200
    And the JSON node "totalClaimAmount" should be equal to 0
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_warranty_claim/schemas/vendor_warranty_claim_statistics.json"

  Scenario: Get top 10 flop vendors as basic user
  Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/purchasing/vendor_warranty_claims;x=top_ten_flop;y=supplier?options[location]=/locations/1&options[createdAfter]=2024-04-01&options[createdBefore]=2026-05-01"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"