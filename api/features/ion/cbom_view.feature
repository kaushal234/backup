Feature: A CBOM can be fetched through the API

  Scenario: Request a single CBOM without being authenticated should not be permitted
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/customized-bill-of-materials/views/site=500;project=PR0000098"
    Then the response status code should be 401

  Scenario: Filters are declared on resource
    Given the class "App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials\View" is exposed on the API
    Then the filter "date" should be available and its type should be "string"
    Then the filter "otherLanguage" should be available and its type should be "string"

  Scenario: Request a single CBOM
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/customized-bill-of-materials/views/site=500;project=T70021?otherLanguage=en_US"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txCBOMView" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "site": 500,
          "project": "T70021",
          "otherLanguage": "en_US"
        }
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "site" should be equal to the number 500
    And the JSON node "project" should be equal to the string "T70021"
    And the JSON node "product" should be equal to "TMX-150"
    And the JSON node "itemDescription" should be equal to "T70021"
#    And the JSON node "itemOtherDescription" should be equal to ""
    And the JSON node "items[0].position" should be equal to the number 10
    And the JSON node "items[0].level" should be equal to the number 0
    And the JSON node "items[0].partNumberProject" should be equal to the string ""
    And the JSON node "items[0].engineeringSignalCode" should be equal to the string "CH0"
    And the JSON node "items[0].itemDescription" should be equal to the string "CH0,INFORMATION           [EN]"
    And the JSON node "items[0].itemOtherDescription" should be equal to the string "CH0,INFORMATION           [EN]"
    And the JSON node "items[0].quantity" should be equal to the number 1
    And the JSON node "items[0].productQuantity" should be equal to the number 1
    And the JSON node "items[0].operation" should be equal to 0
    And the JSON node "items[0].extraInformation" should be equal to the string ""
    And the JSON node "items[0].customized" should be false
    And the JSON node "items[0].engineeringSelectionCode" should be equal to the string "STL"
    And the JSON node "items" should have 71 elements
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/cbom/schemas/view.json"