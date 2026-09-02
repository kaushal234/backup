Feature: ERP Item can be fetched through the API

  Scenario: Request Item without being authenticated should not be permitted
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/item_by_vendors/1200676"
    Then the response status code should be 401

  Scenario: Item should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/item_by_vendors/1200676"
    Then the response status code should be 403

  Scenario: Item should not be accessible to vendor user
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/item_by_vendors/1200676"
    Then the response status code should be 403

  Scenario: Request a single Item
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/item_by_vendors/1200676"
    And a "txItem" SOAP client has been created
    And this client has been called on the operation "txXRef" with the following request:
    """
    {
      "DataArea": {
          "txItem": {
              "item": "1200676",
              "itemCodeSystem": "SUP"
          }
      }
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/item_by_vendor/schemas/item_by_vendor.json"
    And the JSON node "@id" should be equal to the string "/ion/item_by_vendors/1200676"
    And the JSON node "item" should be equal to "1200676"
    And the JSON node "references[0].businessPartner" should be equal to "9MIG640"
    And the JSON node "references[0].businessPartnerName" should be equal to "TLD SHA"
    And the JSON node "references[0].partNumber" should be equal to "2211734"
    And the JSON node "references[0].itemDescription" should be equal to "BATTERY PACK,80V 277AH,iBS"

  Scenario: Filters are declared on resource
    Given the class "App\ION\Resources\MasterData\Items\ItemClassification\ItemByVendor" is exposed on the API
    Then the filter "item" should be available and its type should be "string"
    Then the filter "itemCodeSystem" should be available and its type should be "string"
