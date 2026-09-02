Feature: A CBOM Materials can be fetched through the API

  Scenario: Request a single CBOM without being authenticated should not be permitted
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/customized-bill-of-materials/multi_level_views/site=500;project=PR0000098"
    Then the response status code should be 401

  Scenario: Filters are declared on resource
    Given the class "App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials\MultiLevelView" is exposed on the API
    Then the filter "date" should be available and its type should be "string"
    Then the filter "otherLanguage" should be available and its type should be "string"
    Then the filter "depth" should be available and its type should be "string"

  Scenario: Request a single CBOM
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/customized-bill-of-materials/multi_level_views/site=640;project=T75535?depth=20"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txMultiLevelView" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "site": 640,
          "project": "T75535",
          "depth": "20"
        }
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "site" should be equal to the number 640
    And the JSON node "project" should be equal to the string "T75535"
    And the JSON node "items" should have 76 elements
    And the JSON node "items[0].level" should be equal to the number 0
    And the JSON node "items[0].partNumber" should be equal to "1227283"
    And the JSON node "items[0].position" should be equal to the number 5
    And the JSON node "items[0].engineeringSignalCode" should be equal to the string "IEK"
    And the JSON node "items[0].itemDescription" should be equal to the string "POWER TRAIN, NBL-E"
    And the JSON node "items[0].itemOtherDescription" should be equal to the string ""
    And the JSON node "items[0].quantity" should be equal to the number 1
    And the JSON node "items[0].unitOfMeasure" should be equal to the string "EA"
    And the JSON node "items[0].engineeringRevision" should be equal to the string "E1"
    And the JSON node "items[0].engineeringRevisionEffectiveDate" should be equal to the string "2021-04-07T22:00:00Z"
    And the JSON node "items[0].engineeringRevisionExpiryDate" should be equal to the string "9999-12-29T23:00:00Z"
    And the JSON node "items[0].phantom" should be false
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/cbom/schemas/multi_level_view.json"