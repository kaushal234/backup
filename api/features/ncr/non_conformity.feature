Feature: Test Non Conformity Entity

  Scenario: Resource should only be accessible for intranet and evendors users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Quality\NonConformity" should only be available for "intranet, evendors" user

  Scenario: Request all non conformities as authorized application
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/non_conformities"
    Then the response status code should be 403

  Scenario: Request a non conformity as authorized application
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/non_conformities/1"
    Then the response status code should be 403

  Scenario: Request all non conformities as basic user should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/non_conformities"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/non_conformity/schemas/non_conformities.json"

  Scenario: Request a single non conformity as basic user should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/non_conformities/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/non_conformity/schemas/non_conformity.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Quality\NonConformity" is exposed on the API
    Then the filter "supplierNumber" should be available and its type should be "string"
    Then the filter "supplierName" should be available and its type should be "string"
    Then the filter "q" should be available and its type should be "string"
    Then the filter "status" should be available and its type should be "string"
    Then the filter "location" should be available and its type should be "string"
    Then the filter "reportedBy" should be available and its type should be "string"
    Then the filter "order[id]" should be available and its type should be "string"
    Then the filter "order[reportedBy.lastname]" should be available and its type should be "string"
    Then the filter "order[solution]" should be available and its type should be "string"
    Then the filter "reportedBy.department" should be available and its type should be "string"
    Then the filter "processes" should be available and its type should be "string"
    Then the filter "normalizationGroups[]" should be available and its type should be "string"
    Then the filter "createdAt[before]" should be available and its type should be "DateTimeInterface"
    Then the filter "createdAt[after]" should be available and its type should be "DateTimeInterface"
    Then the filter "parts.partNumber" should be available and its type should be "string"
    Then the filter "parts.serialNumber" should be available and its type should be "string"
    Then the filter "equipmentRecords.serialNumber" should be available and its type should be "string"
    Then the filter "responsibles" should be available and its type should be "string"
    Then the filter "products" should be available and its type should be "string"
    Then the filter "rush" should be available and its type should be "bool"
    Then the filter "safety" should be available and its type should be "bool"
    Then the filter "environmentalIssue" should be available and its type should be "bool"
    Then the filter "parts.referenceNumber" should be available and its type should be "string"
    And the filter "columns" should be available and its type should be "string"

  Scenario: As basic user, I can't create NCR if I select a product and check environmental issue
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/non_conformities" with body:
    """
    {
      "location": "/locations/29",
      "hours": 1,
      "reportedBy": "/people/12",
      "supplierNumber": "TC4000",
      "problem": "boulon manquant",
      "shortDescription": "boulon is missing",
      "repairApprover": "/people/13",
      "currency": "/finance/currencies/2",
      "solution": "commander un nouveau boulon",
      "purchaseOrderNumber": "123456F",
      "rush": true,
      "chargeVendor": true,
      "failureType": "Hydraulic",
      "iFactor": "IF1000",
      "investigation": "I investigated, a boulon was missing",
      "scrap": true,
      "rework": true,
      "firstArticleInspection": true,
      "processes": ["/quality/processes/1"],
      "responsibles": ["/quality/responsibles/2"],
      "useAsIs": true,
      "derogation": true,
      "returnVendor": true,
      "chargeVendorForRepair": true,
      "supplierCorrectiveActionRequest": true,
      "internalCorrectiveActionRequest": true,
      "other": true,
      "actionComment": "il manque vraiment un boulon",
      "repairApprovalDate": "2021-12-24",
      "costBreakdown": "oui",
      "cost": 4.40,
      "workOrderReference": "456789F",
      "nonQualityCost": 0.01,
      "invoiceNumber": "4444444",
      "products": ["/sales/products/1"],
      "environmentalIssue": true,
      "safety": true,
      "crabs": ["/quality/crabs/2"],
      "parts": [
        {
          "reference": "PROD",
          "referenceNumber": "à moi",
          "serialNumber": "sn1234",
          "partNumber": "PN752822",
          "description": "boulon",
          "quantity": 1,
          "unitOfMeasure": "EA"
        }
      ]
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].message" should contain "You can't select products and environmental issue at the same time."

  Scenario: As basic user, I can create a NCR but not with all properties
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/non_conformities" with body:
    """
    {
      "location": "/locations/29",
      "hours": 1,
      "reportedBy": "/people/12",
      "problem": "boulon manquant",
      "shortDescription": "boulon is missing",
      "repairApprover": "/people/13",
      "currency": "/finance/currencies/2",
      "solution": "commander un nouveau boulon",
      "purchaseOrderNumber": "123456F",
      "rush": true,
      "chargeVendor": true,
      "failureType": "Hydraulic",
      "iFactor": "IF1000",
      "processes": ["/quality/processes/1"],
      "responsibles": ["/quality/responsibles/2"],
      "investigation": "I investigated, a boulon was missing",
      "scrap": true,
      "rework": true,
      "firstArticleInspection": true,
      "useAsIs": true,
      "derogation": true,
      "returnVendor": true,
      "chargeVendorForRepair": true,
      "supplierCorrectiveActionRequest": true,
      "internalCorrectiveActionRequest": true,
      "other": true,
      "actionComment": "il manque vraiment un boulon",
      "repairApprovalDate": "2021-12-24",
      "costBreakdown": "oui",
      "cost": 4.40,
      "workOrderReference": "456789F",
      "nonQualityCost": 0.01,
      "invoiceNumber": "4444444",
      "equipmentRecords": ["equipment_records/1"],
      "products": ["/sales/products/1"],
      "crabs": ["/quality/crabs/2"],
      "safety": true,
      "parts": [
        {
          "reference": "PROD",
          "referenceNumber": "à moi",
          "serialNumber": "sn1234",
          "partNumber": "PN752822",
          "description": "boulon",
          "quantity": 1,
          "unitOfMeasure": "EA"
        }
      ]
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/non_conformity/schemas/non_conformity.json"
    And the JSON node "location.@id" should be equal to the string "/locations/29"
    And the JSON node "hours" should be equal to the number 1
    And the JSON node "reportedBy.@id" should be equal to the string "/people/12"
    And the JSON node "supplierName" should be null
    And the JSON node "supplierNumber" should be null
    And the JSON node "problem" should be equal to the string "boulon manquant"
    And the JSON node "shortDescription" should be equal to the string "boulon is missing"
    And the JSON node "repairApprover" should be null
    And the JSON node "currency.@id" should be equal to the string "/finance/currencies/1"
    And the JSON node "solution" should be null
    And the JSON node "purchaseOrderNumber" should be null
    And the JSON node "rush" should be true
    And the JSON node "chargeVendor" should be false
    And the JSON node "failureType" should be null
    And the JSON node "iFactor" should be equal to the string "IF1000"
    And the JSON node "investigation" should be equal to the string "I investigated, a boulon was missing"
    And the JSON node "scrap" should be false
    And the JSON node "rework" should be false
    And the JSON node "firstArticleInspection" should be false
    And the JSON node "safety" should be true
    And the JSON node "useAsIs" should be false
    And the JSON node "derogation" should be false
    And the JSON node "returnVendor" should be false
    And the JSON node "chargeVendorForRepair" should be false
    And the JSON node "supplierCorrectiveActionRequest" should be false
    And the JSON node "internalCorrectiveActionRequest" should be false
    And the JSON node "other" should be false
    And the JSON node "actionComment" should be null
    And the JSON node "costBreakdown" should be null
    And the JSON node "cost" should be null
    And the JSON node "workOrderReference" should be null
    And the JSON node "nonQualityCost" should be equal to 50
    And the JSON node "invoiceNumber" should be null
    And the JSON node "status" should be equal to the string "PENDING"
    And the JSON node "parts" should have 1 element
    And the JSON node "parts[0].reference" should be equal to the string "PROD"
    And the JSON node "parts[0].referenceNumber" should be equal to the string "à moi"
    And the JSON node "parts[0].serialNumber" should be equal to the string "sn1234"
    And the JSON node "parts[0].partNumber" should be equal to the string "PN752822"
    And the JSON node "parts[0].description" should be equal to the string "boulon"
    And the JSON node "parts[0].quantity" should be equal to the number 1
    And the JSON node "parts[0].unitOfMeasure" should be equal to the string "EA"
    And the JSON node "equipmentRecords" should have 1 element
    And the JSON node "equipmentRecords[0].@id" should be equal to the string "/equipment_records/1"
    And the JSON node "products" should have 1 element
    And the JSON node "processes" should have 0 element
    And the JSON node "responsibles" should have 0 element
    And the JSON node "crabs[0].id" should be equal to 2
    And an email should have been sent asynchronously with subject matching pattern "/!!URGENT RUSH ORDER!! NCR#\S+ opened for Type English/"
    And this asynchronous email should be sent only to "user-quality@tld.fr"
    And this asynchronous email should not be sent as cc to "user-qe@tld.fr, user-qam@tld.fr"

  Scenario: As basic user, I can only update parts, models and crabs of NCR
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/non_conformities/6" with body:
    """
    {
      "location": "/locations/30",
      "hours": 2,
      "reportedBy": "/people/13",
      "supplierNumber": "CUMMINS",
      "problem": "vis manquante",
      "shortDescription": "vis is missing",
      "repairApprover": "/people/14",
      "currency": "/finance/currencies/3",
      "solution": "commander une nouvelle vis",
      "purchaseOrderNumber": "1234567F",
      "rush": false,
      "chargeVendor": false,
      "failureType": "Mechanical",
      "iFactor": "IF100",
      "investigation": "I investigated, a vis was missing",
      "scrap": false,
      "rework": false,
      "firstArticleInspection": false,
      "useAsIs": false,
      "derogation": false,
      "returnVendor": false,
      "chargeVendorForRepair": false,
      "supplierCorrectiveActionRequest": false,
      "internalCorrectiveActionRequest": false,
      "other": false,
      "actionComment": "il manque vraiment une vis",
      "repairApprovalDate": "2021-12-22",
      "costBreakdown": "non",
      "cost": 4.41,
      "workOrderReference": "4567891F",
      "nonQualityCost": 0.02,
      "invoiceNumber": "44444445",
      "equipmentRecords": ["equipment_records/1", "equipment_records/2"],
      "products": ["/sales/products/2"],
      "processes": ["/quality/processes/1"],
      "safety": false,
      "responsibles": ["/quality/responsibles/2"],
      "crabs": ["/quality/crabs/2"],
      "parts": [
        {
          "reference": "PROD",
          "referenceNumber": "à moi",
          "serialNumber": "sn1234",
          "partNumber": "PN7528221",
          "description": "boulon",
          "quantity": 1,
          "unitOfMeasure": "EA"
        }
      ]
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/non_conformity/schemas/non_conformity.json"
    And the JSON node "location.@id" should be equal to the string "/locations/29"
    And the JSON node "hours" should be equal to the number 1
    And the JSON node "reportedBy.@id" should be equal to the string "/people/12"
    And the JSON node "supplierName" should be null
    And the JSON node "supplierNumber" should be null
    And the JSON node "problem" should be equal to the string "boulon manquant"
    And the JSON node "shortDescription" should be equal to the string "boulon is missing"
    And the JSON node "repairApprover" should be null
    And the JSON node "currency.@id" should be equal to the string "/finance/currencies/1"
    And the JSON node "solution" should be null
    And the JSON node "purchaseOrderNumber" should be null
    And the JSON node "rush" should be true
    And the JSON node "chargeVendor" should be false
    And the JSON node "failureType" should be null
    And the JSON node "iFactor" should be equal to the string "IF1000"
    And the JSON node "investigation" should be equal to the string "I investigated, a boulon was missing"
    And the JSON node "scrap" should be false
    And the JSON node "rework" should be false
    And the JSON node "firstArticleInspection" should be false
    And the JSON node "useAsIs" should be false
    And the JSON node "derogation" should be false
    And the JSON node "returnVendor" should be false
    And the JSON node "chargeVendorForRepair" should be false
    And the JSON node "supplierCorrectiveActionRequest" should be false
    And the JSON node "internalCorrectiveActionRequest" should be false
    And the JSON node "safety" should be false
    And the JSON node "other" should be false
    And the JSON node "actionComment" should be null
    And the JSON node "costBreakdown" should be null
    And the JSON node "cost" should be null
    And the JSON node "workOrderReference" should be null
    And the JSON node "nonQualityCost" should be equal to 50
    And the JSON node "invoiceNumber" should be null
    And the JSON node "status" should be equal to the string "PENDING"
    And the JSON node "parts" should have 1 element
    And the JSON node "processes" should have 0 element
    And the JSON node "responsibles" should have 0 element
    And the JSON node "parts[0].partNumber" should be equal to the string "PN7528221"
    And the JSON node "equipmentRecords" should have 1 element
    And the JSON node "products" should have 1 element
    And the JSON node "products[0].@id" should be equal to the string "/sales/products/2"
    And the JSON node "crabs[0].id" should be equal to 2

  Scenario: As user production, I can update a NCR but not the status
    Given I authenticate as the intranet user "user-production@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/non_conformities/3" with body:
    """
    {
      "status": "CLOSED",
      "location": "/locations/30",
      "hours": 2,
      "reportedBy": "/people/13",
      "supplierNumber": "DAN0013",
      "problem": "vis manquante",
      "shortDescription": "vis is missing",
      "repairApprover": "/people/12",
      "currency": "finance/currencies/2",
      "solution": "commander une nouvelle vis",
      "purchaseOrderNumber": "123456E",
      "rush": false,
      "chargeVendor": false,
      "failureType": "Mechanical",
      "iFactor": "IF100",
      "investigation": "I investigated, a vis was missing",
      "scrap": false,
      "rework": false,
      "firstArticleInspection": false,
      "useAsIs": false,
      "derogation": false,
      "returnVendor": false,
      "chargeVendorForRepair": false,
      "supplierCorrectiveActionRequest": false,
      "internalCorrectiveActionRequest": false,
      "safety": true,
      "other": false,
      "actionComment": "il manque vraiment une vis",
      "repairApprovalDate": "2021-12-25",
      "costBreakdown": "non",
      "cost": 4.42,
      "workOrderReference": "456789E",
      "nonQualityCost": 0.02,
      "invoiceNumber": "3333333",
      "equipmentRecords": ["/equipment_records/1"],
      "products": ["/sales/products/1", "/sales/products/2"],
      "responsibles": ["/quality/responsibles/2"],
      "processes": ["/quality/processes/1"],
      "crabs": ["/quality/crabs/2"],
      "parts": [
        {
          "reference": "WHSE",
          "referenceNumber": "à toi",
          "serialNumber": "sn3456",
          "partNumber": "PN428924",
          "description": "vis",
          "quantity": 2,
          "unitOfMeasure": "KG"
        }
      ]
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/non_conformity/schemas/non_conformity.json"
    And the JSON node "status" should be equal to the string "PENDING"
    And the JSON node "location.@id" should be equal to the string "/locations/30"
    And the JSON node "hours" should be equal to the number 2
    And the JSON node "reportedBy.@id" should be equal to the string "/people/13"
    And the JSON node "supplierNumber" should be equal to DAN0013
    And the JSON node "problem" should be equal to the string "vis manquante"
    And the JSON node "shortDescription" should be equal to the string "vis is missing"
    And the JSON node "repairApprover.@id" should be equal to the string "/people/12"
    And the JSON node "currency.@id" should be equal to the string "/finance/currencies/2"
    And the JSON node "solution" should be equal to the string "commander une nouvelle vis"
    And the JSON node "purchaseOrderNumber" should be equal to the string "123456E"
    And the JSON node "rush" should be false
    And the JSON node "chargeVendor" should be false
    And the JSON node "failureType" should be equal to the string "Mechanical"
    And the JSON node "iFactor" should be equal to the string "IF100"
    And the JSON node "investigation" should be equal to the string "I investigated, a vis was missing"
    And the JSON node "scrap" should be false
    And the JSON node "rework" should be false
    And the JSON node "firstArticleInspection" should be false
    And the JSON node "safety" should be true
    And the JSON node "useAsIs" should be false
    And the JSON node "derogation" should be false
    And the JSON node "returnVendor" should be false
    And the JSON node "chargeVendorForRepair" should be false
    And the JSON node "supplierCorrectiveActionRequest" should be false
    And the JSON node "internalCorrectiveActionRequest" should be false
    And the JSON node "other" should be false
    And the JSON node "actionComment" should be equal to the string "il manque vraiment une vis"
    And the JSON node "costBreakdown" should be equal to the string "non"
    And the JSON node "cost" should be equal to the number 4.42
    And the JSON node "workOrderReference" should be equal to the string "456789E"
    And the JSON node "nonQualityCost" should be equal to the number 0.02
    And the JSON node "invoiceNumber" should be equal to "3333333"
    And the JSON node "parts" should have 1 elements
    And the JSON node "parts[0].reference" should be equal to the string "WHSE"
    And the JSON node "parts[0].referenceNumber" should be equal to the string "à toi"
    And the JSON node "parts[0].serialNumber" should be equal to the string "sn3456"
    And the JSON node "parts[0].partNumber" should be equal to the string "PN428924"
    And the JSON node "parts[0].description" should be equal to the string "vis"
    And the JSON node "parts[0].quantity" should be equal to the number 2
    And the JSON node "parts[0].unitOfMeasure" should be equal to the string "KG"
    And the JSON node "processes[0].category" should be equal to the string "ENGINEERING"
    And the JSON node "processes[0].description" should be equal to the string "Drawing Error/ Issue"
    And the JSON node "responsibles[0].name" should be equal to the string "Supplier"
    And the JSON node "equipmentRecords" should have 1 elements
    And the JSON node "equipmentRecords[0].@id" should be equal to "/equipment_records/1"
    And the JSON node "products" should have 2 elements
    And the JSON node "crabs[0].id" should be equal to 2

  Scenario: As user engineer, I can update a only investigation, problem and short description of NCR (on addition to basic permissions for edit)
    Given I authenticate as the intranet user "user-eng@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/non_conformities/3" with body:
    """
    {
      "status": "CLOSED",
      "location": "/locations/30",
      "hours": 2,
      "reportedBy": "/people/13",
      "supplierNumber": "DAN0013",
      "problem": "vis manquante updated",
      "shortDescription": "vis is missing updated",
      "repairApprover": "/people/12",
      "currency": "finance/currencies/2",
      "solution": "commander une nouvelle vis",
      "purchaseOrderNumber": "123456E",
      "rush": false,
      "chargeVendor": false,
      "failureType": "Mechanical",
      "iFactor": "IF100",
      "investigation": "I investigated, a vis was missing but updated",
      "scrap": false,
      "rework": false,
      "firstArticleInspection": false,
      "useAsIs": false,
      "derogation": false,
      "returnVendor": false,
      "chargeVendorForRepair": false,
      "supplierCorrectiveActionRequest": false,
      "internalCorrectiveActionRequest": false,
      "safety": true,
      "other": false,
      "actionComment": "il manque vraiment une vis",
      "repairApprovalDate": "2021-12-25",
      "costBreakdown": "non",
      "cost": 4.42,
      "workOrderReference": "456789E",
      "nonQualityCost": 0.02,
      "invoiceNumber": "3333333",
      "equipmentRecords": ["/equipment_records/1", "equipment_records/2"],
      "products": ["/sales/products/1", "/sales/products/2"],
      "responsibles": ["/quality/responsibles/2"],
      "processes": ["/quality/processes/1"],
      "crabs": ["/quality/crabs/2"],
      "parts": [
        {
          "reference": "WHSE",
          "referenceNumber": "à toi",
          "serialNumber": "sn3456",
          "partNumber": "PN428924",
          "description": "vis",
          "quantity": 2,
          "unitOfMeasure": "KG"
        }
      ]
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/non_conformity/schemas/non_conformity.json"
    And the JSON node "status" should be equal to the string "PENDING"
    And the JSON node "location.@id" should be equal to the string "/locations/30"
    And the JSON node "hours" should be equal to the number 2
    And the JSON node "reportedBy.@id" should be equal to the string "/people/13"
    And the JSON node "supplierNumber" should be equal to DAN0013
    And the JSON node "problem" should be equal to the string "vis manquante updated"
    And the JSON node "shortDescription" should be equal to the string "vis is missing updated"
    And the JSON node "repairApprover.@id" should be equal to the string "/people/12"
    And the JSON node "currency.@id" should be equal to the string "/finance/currencies/2"
    And the JSON node "solution" should be equal to the string "commander une nouvelle vis"
    And the JSON node "purchaseOrderNumber" should be equal to the string "123456E"
    And the JSON node "rush" should be false
    And the JSON node "chargeVendor" should be false
    And the JSON node "failureType" should be equal to the string "Mechanical"
    And the JSON node "iFactor" should be equal to the string "IF100"
    And the JSON node "investigation" should be equal to the string "I investigated, a vis was missing but updated"
    And the JSON node "scrap" should be false
    And the JSON node "rework" should be false
    And the JSON node "firstArticleInspection" should be false
    And the JSON node "safety" should be true
    And the JSON node "useAsIs" should be false
    And the JSON node "derogation" should be false
    And the JSON node "returnVendor" should be false
    And the JSON node "chargeVendorForRepair" should be false
    And the JSON node "supplierCorrectiveActionRequest" should be false
    And the JSON node "internalCorrectiveActionRequest" should be false
    And the JSON node "other" should be false
    And the JSON node "actionComment" should be equal to the string "il manque vraiment une vis"
    And the JSON node "costBreakdown" should be equal to the string "non"
    And the JSON node "cost" should be equal to the number 4.42
    And the JSON node "workOrderReference" should be equal to the string "456789E"
    And the JSON node "nonQualityCost" should be equal to the number 0.02
    And the JSON node "invoiceNumber" should be equal to "3333333"
    And the JSON node "parts" should have 1 elements
    And the JSON node "parts[0].reference" should be equal to the string "WHSE"
    And the JSON node "parts[0].referenceNumber" should be equal to the string "à toi"
    And the JSON node "parts[0].serialNumber" should be equal to the string "sn3456"
    And the JSON node "parts[0].partNumber" should be equal to the string "PN428924"
    And the JSON node "parts[0].description" should be equal to the string "vis"
    And the JSON node "parts[0].quantity" should be equal to the number 2
    And the JSON node "parts[0].unitOfMeasure" should be equal to the string "KG"
    And the JSON node "processes[0].category" should be equal to the string "ENGINEERING"
    And the JSON node "processes[0].description" should be equal to the string "Drawing Error/ Issue"
    And the JSON node "responsibles[0].name" should be equal to the string "Supplier"
    And the JSON node "equipmentRecords" should have 1 elements
    And the JSON node "equipmentRecords[0].@id" should be equal to the string "/equipment_records/1"
    And the JSON node "products" should have 2 elements
    And the JSON node "crabs[0].id" should be equal to 2

  Scenario: As reporter of NCR, I can edit all fields of NCR
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/non_conformities/3" with body:
    """
    {
      "location": "/locations/29",
      "hours": 3,
      "supplierNumber": "DAN0013",
      "problem": "vis manquante updated twice",
      "shortDescription": "vis is missing updated twice",
      "repairApprover": "/people/11",
      "currency": "finance/currencies/1",
      "solution": "commander une nouvelle vis updated",
      "purchaseOrderNumber": "123456EX",
      "rush": true,
      "chargeVendor": true,
      "failureType": "Mechanical",
      "iFactor": "IF10",
      "investigation": "I investigated, a vis was missing but updated twice",
      "scrap": true,
      "rework": true,
      "firstArticleInspection": true,
      "useAsIs": true,
      "derogation": true,
      "returnVendor": true,
      "chargeVendorForRepair": true,
      "supplierCorrectiveActionRequest": true,
      "internalCorrectiveActionRequest": true,
      "safety": false,
      "other": true,
      "actionComment": "il manque vraiment une vis, svp",
      "repairApprovalDate": "2021-12-26",
      "costBreakdown": "oui",
      "cost": 4.40,
      "workOrderReference": "456789EX",
      "nonQualityCost": 0.03,
      "invoiceNumber": "33333334",
      "equipmentRecords": ["/equipment_records/3", "equipment_records/4"],
      "products": ["/sales/products/1"],
      "responsibles": ["/quality/responsibles/1"],
      "processes": ["/quality/processes/2"],
      "crabs": ["/quality/crabs/1"]
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/non_conformity/schemas/non_conformity.json"
    And the JSON node "status" should be equal to the string "PENDING"
    And the JSON node "location.@id" should be equal to the string "/locations/29"
    And the JSON node "hours" should be equal to the number 3
    And the JSON node "reportedBy.@id" should be equal to the string "/people/13"
    And the JSON node "supplierNumber" should be equal to DAN0013
    And the JSON node "problem" should be equal to the string "vis manquante updated twice"
    And the JSON node "shortDescription" should be equal to the string "vis is missing updated twice"
    And the JSON node "repairApprover.@id" should be equal to the string "/people/11"
    And the JSON node "currency.@id" should be equal to the string "/finance/currencies/1"
    And the JSON node "solution" should be equal to the string "commander une nouvelle vis updated"
    And the JSON node "purchaseOrderNumber" should be equal to the string "123456EX"
    And the JSON node "rush" should be true
    And the JSON node "chargeVendor" should be true
    And the JSON node "failureType" should be equal to the string "Mechanical"
    And the JSON node "iFactor" should be equal to the string "IF10"
    And the JSON node "investigation" should be equal to the string "I investigated, a vis was missing but updated twice"
    And the JSON node "scrap" should be true
    And the JSON node "rework" should be true
    And the JSON node "firstArticleInspection" should be true
    And the JSON node "safety" should be false
    And the JSON node "useAsIs" should be true
    And the JSON node "derogation" should be true
    And the JSON node "returnVendor" should be true
    And the JSON node "chargeVendorForRepair" should be true
    And the JSON node "supplierCorrectiveActionRequest" should be true
    And the JSON node "internalCorrectiveActionRequest" should be true
    And the JSON node "other" should be true
    And the JSON node "actionComment" should be equal to the string "il manque vraiment une vis, svp"
    And the JSON node "costBreakdown" should be equal to the string "oui"
    And the JSON node "cost" should be equal to the number 4.40
    And the JSON node "workOrderReference" should be equal to the string "456789EX"
    And the JSON node "nonQualityCost" should be equal to the number 0.03
    And the JSON node "invoiceNumber" should be equal to "33333334"
    And the JSON node "parts" should have 1 elements
    And the JSON node "parts[0].reference" should be equal to the string "WHSE"
    And the JSON node "parts[0].referenceNumber" should be equal to the string "à toi"
    And the JSON node "parts[0].serialNumber" should be equal to the string "sn3456"
    And the JSON node "parts[0].partNumber" should be equal to the string "PN428924"
    And the JSON node "parts[0].description" should be equal to the string "vis"
    And the JSON node "parts[0].quantity" should be equal to the number 2
    And the JSON node "parts[0].unitOfMeasure" should be equal to the string "KG"
    And the JSON node "processes[0].category" should be equal to the string "PRODUCTION"
    And the JSON node "responsibles[0].name" should be equal to the string "TLD"
    And the JSON node "equipmentRecords" should have 2 elements
    And the JSON node "equipmentRecords[0].@id" should be equal to the string "/equipment_records/3"
    And the JSON node "equipmentRecords[1].@id" should be equal to the string "/equipment_records/4"
    And the JSON node "products" should have 1 elements
    And the JSON node "crabs[0].id" should be equal to 1

  Scenario: When I update supplier number of non conformity, it updates all VWC linked
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/ncr_vendor_warranty_claims/4"
    Then the response status code should be 200
    And the JSON node "supplierNumber" should be equal to the string "DAN0013"
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/non_conformities/1" with body:
    """
    {
      "supplierNumber": "SAR0017"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/non_conformity/schemas/non_conformity.json"
    And the JSON node "supplierNumber" should be equal to the string "SAR0017"
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/ncr_vendor_warranty_claims/4"
    Then the response status code should be 200
    And the JSON node "supplierNumber" should be equal to the string "SAR0017"

  Scenario: As qam, I can't close NCR is there are open tasks
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/non_conformities/1" with body:
    """
    {
      "status": "CLOSED"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].message" should be equal to the string "Action not possible, you can't set status to CLOSED as there are remaining open tasks on it."

  Scenario: As qam, I can't close NCR is there are open tasks
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/non_conformities/1" with body:
    """
    {
      "status": "REJECTED"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].message" should be equal to the string "Action not possible, you can't set status to REJECTED as there are remaining open tasks on it."

  Scenario: As authorized user, I can have a NCR flagged as safety and environmental
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/non_conformities/1" with body:
    """
    {
      "safety": true,
      "environmentalIssue": true
    }
    """
    Then the response status code should be 200

  Scenario: As qam, I can change status of NCR
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/non_conformities/1" with body:
    """
    {
      "status": "SUSPENDED"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "SUSPENDED"
    And no email should have been sent asynchronously

  Scenario: Email is sent when non conformity is closed
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/non_conformities/3" with body:
    """
    {
      "status": "CLOSED"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "CLOSED"
    And an email should have been sent asynchronously with subject matching pattern "/NCR#\S+ has been CLOSED/"
    And this asynchronous email should be sent to "user-hr@tld.fr"

  Scenario: Closed non conformity can't be updated by basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/non_conformities/3" with body:
    """
    {
      "shortDescription": "toto"
    }
    """
    Then the response status code should be 403

  Scenario: Closed non conformity can be updated  by QAM
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/non_conformities/6" with body:
    """
    {
      "shortDescription": "toto"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to the string "toto"

  #Put back to open to test REJECTED is also considered as CLOSED
  Scenario: As qam, I can change status of NCR
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/non_conformities/3" with body:
    """
    {
      "status": "SUSPENDED"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "SUSPENDED"
    And no email should have been sent asynchronously

  Scenario: Email is sent when non conformity is rejectec
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/non_conformities/3" with body:
    """
    {
      "status": "REJECTED"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "REJECTED"
    And an email should have been sent asynchronously with subject matching pattern "/NCR#\S+ has been REJECTED/"
    And this asynchronous email should be sent to "user-hr@tld.fr"

  Scenario: Closed non conformity can't be updated by basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/non_conformities/3" with body:
    """
    {
      "shortDescription": "toto"
    }
    """
    Then the response status code should be 403

  Scenario: Closed non conformity can be updated  by QAM
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/non_conformities/3" with body:
    """
    {
      "shortDescription": "tata"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to the string "tata"

  Scenario: As a basic user, I can access the custom reports
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/quality/non_conformities;x=location.name;y=status"
    Then the response status code should be 200
    And the JSON node "yTotals.CLOSED" should not exist

  Scenario: Download excel ncr reports should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "/quality/non_conformities?status=PENDING&columns=id,status,location,createdAt,closedAt,turnAroundTime,reportedBy,shortDescription,problem,processes,investigation,responsibles,products,purchaseOrderNumber,partNumbers,quantity,partsDescription,serialNumbers,refTypes,references,rush,scrap,rework,useAsIs,derogation,returnVendor,chargeVendorForRepair,supplierCorrectiveActionRequest,other,actionComment,repairApprover,repairApprovalDate,solution,hours,currency,cost,costBreakdown,invoiceNumber,supplierNumber,supplierName,failureType,iFactor"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Id | Status | Location | Created At | Closed At | Turn Around Time | Reported By | Short Description | Problem | Processes | Investigation | Responsibles | Products | Purchase Order Number | Part Numbers | Quantity | Parts Description | Serial Numbers | Ref Types | References | Rush | Scrap | Rework | Use As Is | Derogation | Return Vendor | Charge Vendor For Repair | Supplier Corrective Action Request | Other | Action Comment | Repair Approver | Repair Approval Date | Solution | Hours | Currency | Cost | Cost Breakdown | Invoice Number | Supplier Number | Supplier Name | Failure Type | I Factor |

  @resetFileTable
  Scenario: As a basic user, I can upload a file to a non conformity
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/quality/non_conformities/1/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"

  Scenario: As user qam I can change visibility of a file of a non conformity
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/non_conformities/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/non_conformity/schemas/non_conformity.json"
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
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/non_conformities/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/non_conformity/schemas/non_conformity.json"
    And the JSON node "files[0].public" should be false

  Scenario: As user basic I can't change visibility of a file of a non conformity
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/files/1" with body:
    """
    {
      "public": false
    }
    """
    Then the response status code should be 403

  Scenario: Upload an invalid file to a non conformity
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/quality/non_conformities/1/files" with file "file" "image.gif"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "files: The mime type of the file is invalid"
    And the JSON node "violations[0].propertyPath" should be equal to "files"
    And the JSON node "violations[0].message" should contain "The mime type of the file is invalid"

  Scenario: Download a non conformity attached file as an extranet user should not be possible
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/non_conformities/1/files/1"
    Then the response status code should be 403

  Scenario: Download a non conformity attached file as a basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/non_conformities/1/files/1"
    Then the response status code should be 200

  Scenario: As a basic user, I can't delete a file from a non conformity
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/non_conformities/1/files/1"
    Then the response status code should be 403

  Scenario: As a user qam, I can delete a file from a non conformity
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/non_conformities/1/files/1"
    Then the response status code should be 204

  Scenario: Change main file of a non conformity with permission OK
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/quality/non_conformities/1/main_file" with file "file" "image_1200x1200.jpg"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"

  Scenario: Basic users can't delete a non conformity main file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/non_conformities/1/main_file/2"
    Then the response status code should be 403

  Scenario: Superusers can delete a non conformity main file
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I send a "DELETE" request to "/quality/non_conformities/1/main_file/2"
    Then the response status code should be 204
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/non_conformities/1"
    And the response status code should be 200
    And the JSON node "mainFile" should be null

  Scenario: As user qam I can delete a non conformity if not used
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/non_conformities/1"
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "The Non Conformity '1' is not deletable because it is used by 1 Vendor Warranty Claim (4)"
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/non_conformities/3"
    And the response status code should be 422
    And the JSON node "hydra:description" should be equal to "The Non Conformity '3' is not deletable because it is used by 1 Crab (1)"
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/non_conformities/4"
    And the response status code should be 204