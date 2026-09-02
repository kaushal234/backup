Feature: A CBOM Materials can be fetched through the API

  Scenario: Request a single CBOM without being authenticated should not be permitted
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/bill-of-materials/shopfloor_views/site=640;project=;product=1152805"
    Then the response status code should be 401

  Scenario: Filters are declared on resource
    Given the class "App\ION\Resources\Manufacturing\JobShop\BillOfMaterials\ShopfloorView" is exposed on the API
    Then the filter "date" should be available and its type should be "string"
    Then the filter "otherLanguage" should be available and its type should be "string"

  Scenario: Request a single CBOM
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/bill-of-materials/shopfloor_views/site=640;project=;product=1152805"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txBomShopfloor" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "site": 640,
          "project": "",
          "product": "1152805"
        }
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "site" should be equal to the number 640
    And the JSON node "project" should be equal to the string ""
    And the JSON node "itemDescription" should be equal to the string "ASSEMBLY SHIM"
    And the JSON node "itemOtherDescription" should be equal to the string ""
    And the JSON node "items" should have 5 elements
    And the JSON node "items[0].level" should be equal to the number 1
    And the JSON node "items[0].partNumber" should be equal to "1085287"
    And the JSON node "items[0].position" should be equal to the number 1
    And the JSON node "items[0].itemDescription" should be equal to the string "CALE PALONNIER"
    And the JSON node "items[0].itemOtherDescription" should be equal to the string ""
    And the JSON node "items[0].quantity" should be equal to the number 1
    And the JSON node "items[0].unitOfMeasure" should be equal to the string "EA"
    And the JSON node "items[0].engineeringRevisionEffectiveDate" should be equal to the string "2014-11-05T23:00:00Z"
    And the JSON node "items[0].engineeringRevisionExpiryDate" should be equal to the string "9999-12-29T23:00:00Z"
    And the JSON node "items[0].engineeringRevision" should be equal to "B"
    And the JSON node "items[0].engineeringSignalCode" should be equal to the string ""
    And the JSON node "items[0].extraInformation" should be equal to the string ""
    And the JSON node "items[0].operation" should be equal to the number 0
    And the JSON node "items[0].warehouse" should be equal to the string ""
    And the JSON node "items[0].backflushIfMaterial" should be false
    And the JSON node "items[0].phantom" should be false
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/bom/schemas/shopfloor_view.json"