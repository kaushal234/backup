Feature: A BOM Materials can be fetched through the API

  Scenario: Request a purchasing BOM without being authenticated should not be permitted
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/bill-of-materials/purchasing_views/site=500;project=;product=1247676"
    Then the response status code should be 401

  Scenario: Request a not existing purchasing BOM
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/bill-of-materials/purchasing_views/site=500;project=;product=1247676666"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txBomPurchasing" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "site": 500,
          "project": "",
          "product": "1247676666"
        }
      }
    }
    """
    Then the response status code should be 404

  Scenario: Request a purchasing BOM
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/bill-of-materials/purchasing_views/site=500;project=;product=1247676"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txBomPurchasing" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "site": 500,
          "project": "",
          "product": "1247676"
        }
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "site" should be equal to the number 500
    And the JSON node "project" should be equal to the string ""
    And the JSON node "product" should be equal to "1247676"
    And the JSON node "supplier" should be equal to the string ""
    And the JSON node "supplierName" should be equal to the string ""
    And the JSON node "supplySource" should be equal to the string "Job Shop"
    And the JSON node "leadTime" should be equal to the number 50
    And the JSON node "leadTimeUnit" should be equal to the string "Days"
    And the JSON node "inventoryOnHand" should be equal to the number 0
    And the JSON node "inventoryOnOrder" should be equal to the number 0
    And the JSON node "allocated" should be equal to the number 0
    And the JSON node "itemDescription" should be equal to the string "CABINE ELECTRICITY, ASSY"
    And the JSON node "itemOtherDescription" should be equal to the string ""
    And the JSON node "items" should have 22 elements
    And the JSON node "items[0].level" should be equal to the number 0
    And the JSON node "items[0].partNumber" should be equal to "7300491"
    And the JSON node "items[0].position" should be equal to the number 1
    And the JSON node "items[0].itemDescription" should be equal to the string "SWITCH, STEERING COLUMN"
    And the JSON node "items[0].itemOtherDescription" should be equal to the string ""
    And the JSON node "items[0].quantity" should be equal to the number 1
    And the JSON node "items[0].productQuantity" should be equal to the number 1
    And the JSON node "items[0].unitOfMeasure" should be equal to the string "EA"
    And the JSON node "items[0].engineeringRevisionEffectiveDate" should be equal to the string "1989-12-31T23:00:00Z"
    And the JSON node "items[0].engineeringRevisionExpiryDate" should be equal to the string "9999-12-29T23:00:00Z"
    And the JSON node "items[0].engineeringRevision" should be equal to "REL"
    And the JSON node "items[0].supplySource" should be equal to the string "Purchase"
    And the JSON node "items[0].supplier" should be equal to the string "COB0004"
    And the JSON node "items[0].supplierName" should be equal to the string "COBO SPA"
    And the JSON node "items[0].leadTime" should be equal to the number 60
    And the JSON node "items[0].leadTimeUnit" should be equal to the string "Days"
    And the JSON node "items[0].inventoryOnHand" should be equal to the number 72
    And the JSON node "items[0].inventoryOnOrder" should be equal to the number 0
    And the JSON node "items[0].allocated" should be equal to the number 0
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/bom/schemas/intranet_view.json"