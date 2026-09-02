Feature: ERP MaterialList can be fetched through the API

  Scenario: Request MaterialList without being authenticated should not be permitted
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/material_lists/site=400;productionOrder=WOU000496"
    Then the response status code should be 401

  Scenario: MaterialList should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/material_lists/site=400;productionOrder=WOU000496"
    Then the response status code should be 403

  Scenario: Filters are declared on resource
    Given the class "App\ION\Resources\Manufacturing\MaterialList" is exposed on the API
    Then the filter "operation" should be available and its type should be "string"
    Then the filter "otherLanguage" should be available and its type should be "string"

  Scenario: Request a single MaterialList
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/material_lists/site=400;productionOrder=WOU000496"
    And a "txMaterialList" SOAP client has been created
    And this client has been called on the operation "txShow" with the following request:
    """
    {
      "DataArea": {
          "txMaterialList": {
              "site": "400",
              "productionOrder": "WOU000496"
          }
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "@id" should be equal to the string "/ion/material_lists/site=400;productionOrder=WOU000496"
    And the JSON node "site" should be equal to "400"
    And the JSON node "productionOrder" should be equal to "WOU000496"
    And the JSON node "productionOrderStatus" should be equal to "planned"
    And the JSON node "project" should be equal to "T82604"
    And the JSON node "projectStatus" should be equal to "active"
    And the JSON node "materials[0].@type" should be equal to "Material"
    And the JSON node "materials[0].position" should be equal to "10"
    And the JSON node "materials[0].operation" should be equal to "600"
    And the JSON node "materials[0].item" should be equal to "1000557"
    And the JSON node "materials[0].itemDescription" should be equal to "SWITCH, PRESSURE"
    And the JSON node "materials[0].warehouse" should be equal to "400MW1"
    And the JSON node "materials[0].netQuantity" should be equal to "2"
    And the JSON node "materials[0].estimatedQuantity" should be equal to "2"
    And the JSON node "materials[0].actualQuantity" should be equal to "0"
    And the JSON node "materials[0].revision" should be equal to "A"
    And the JSON node "materials[0].reportMaterial" should be equal to "manual"
    And the JSON node "materials[0].inventoryOnHand" should be equal to "59"
    And the JSON node "materials[0].inventoryOnOrder" should be equal to "81"
    And the JSON node "materials[0].costPrice" should be equal to "43.6"
    And the JSON node "materials[0].currency" should be equal to "USD"
    And the JSON node "materials" should have 205 elements
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/material_list/schemas/item.json"

  Scenario: Request a single MaterialList with operation
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/material_lists/site=400;productionOrder=WOU000496?operation=420"
    And a "txMaterialList" SOAP client has been created
    And this client has been called on the operation "txShow" with the following request:
    """
    {
      "DataArea": {
          "txMaterialList": {
              "site": "400",
              "productionOrder": "WOU000496",
              "operation": "420"
          }
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "@id" should be equal to the string "/ion/material_lists/site=400;productionOrder=WOU000496"
    And the JSON node "site" should be equal to "400"
    And the JSON node "productionOrder" should be equal to "WOU000496"
    And the JSON node "productionOrderStatus" should be equal to "planned"
    And the JSON node "project" should be equal to "T82604"
    And the JSON node "projectStatus" should be equal to "active"
    And the JSON node "materials[0].@type" should be equal to "Material"
    And the JSON node "materials[0].position" should be equal to "20"
    And the JSON node "materials[0].operation" should be equal to "420"
    And the JSON node "materials[0].item" should be equal to "1000895-360"
    And the JSON node "materials[0].itemDescription" should be equal to "HOSE &amp; SCUFF JACKET ASSEMBLY"
    And the JSON node "materials[0].warehouse" should be equal to "400MW1"
    And the JSON node "materials[0].netQuantity" should be equal to "2"
    And the JSON node "materials[0].estimatedQuantity" should be equal to "2"
    And the JSON node "materials[0].actualQuantity" should be equal to "0"
    And the JSON node "materials[0].unitOfMeasure" should be equal to "EA"
    And the JSON node "materials" should have 4 elements
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/material_list/schemas/item.json"
