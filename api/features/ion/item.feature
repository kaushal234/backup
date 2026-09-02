Feature: ERP Item can be fetched through the API

  Scenario: Request Item without being authenticated should not be permitted
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/items/site=500;item=0024635"
    Then the response status code should be 401

  Scenario: Item should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/items/site=500;item=0024635"
    Then the response status code should be 403

  Scenario: Item should not be accessible to vendor user
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/items/site=500;item=0024635"
    Then the response status code should be 403

  Scenario: Filters are declared on resource
    Given the class "App\ION\Resources\MasterData\Items\Item" is exposed on the API
    Then the filter "site" should be available and its type should be "string"
    Then the filter "itemFilter" should be available and its type should be "string"
    Then the filter "itemDescriptionFilter" should be available and its type should be "string"
    Then the filter "itemFilterMethod" should be available and its type should be "string"

  Scenario: Request a single Item
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/items/site=500;item=0024635"
    And a "txItemsBySite" SOAP client has been created
    And this client has been called on the operation "txShow" with the following request:
    """
    {
      "DataArea": {
          "txItemsBySite": {
              "site": "500",
              "item": "0024635"
          }
      }
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/item/schemas/item.json"
    And the JSON node "@id" should be equal to the string "/ion/items/site=500;item=0024635"
    And the JSON node "site" should be equal to "500"
    And the JSON node "item" should be equal to "0024635"
    And the JSON node "itemDescription" should be equal to "WD40"
    And the JSON node "unitOfMeasure" should be equal to "EA"
    And the JSON node "buyer.@type" should be equal to "Employee"
    And the JSON node "buyer.employeeCode" should be equal to "13145"
    And the JSON node "buyer.fullName" should be equal to "Baptiste DAILLER"
    And the JSON node "buyer.erp" should exist
    And the JSON node "buyer.baanLegacyId" should be equal to "45"
    And the JSON node "buyer.emailAddress" should be equal to "baptiste.dailler@tld-europe.com"
    And the JSON node "businessPartner.@type" should be equal to "BusinessPartner"
    And the JSON node "businessPartner.code" should be equal to "WUR0005"
    And the JSON node "businessPartner.name" should be equal to "WURTH INDUSTRIE FRANCE SAS"

  Scenario: Request a list of Items
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/items?site=500&itemFilter=0024635&itemDescriptionFilter=WD4"
    And a "txItemsBySite" SOAP client has been created
    And this client has been called on the operation "txList" with the following request:
    """
    {
      "ControlArea": {
          "maxNumberOfObjects": 500,
          "Filter": {
              "LogicalExpression": {
                  "logicalOperator": "and"
              }
          }
      },
      "DataArea": {
          "txItemsBySite": {
              "itemFilter": "0024635",
              "itemDescriptionFilter": "WD4",
              "site": "500"
          }
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "hydra:member[0].site" should be equal to 500
    And the JSON node "hydra:member[0].item" should be equal to "0024635"
    And the JSON node "hydra:member[0].itemDescription" should be equal to the string "WD40"
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/item/schemas/items.json"
