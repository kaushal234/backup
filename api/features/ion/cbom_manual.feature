Feature: A CBOM Manual can be fetched through the API

  Scenario: Request a single CBOM without being authenticated should not be permitted
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/customized-bill-of-materials/manuals/site=500;project=T70021"
    Then the response status code should be 401

  Scenario: Filters are declared on resource
    Given the class "App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials\Manuals" is exposed on the API
    Then the filter "date" should be available and its type should be "string"
    Then the filter "otherLanguage" should be available and its type should be "string"
    Then the filter "signalCodeFilter" should be available and its type should be "string"
    Then the filter "signalCodeFilterMethod" should be available and its type should be "string"
    Then the filter "signalCodeAttribute" should be available and its type should be "string"

  Scenario: Request a single CBOM
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/customized-bill-of-materials/manuals/site=500;project=T70021?signalCodeFilter=CH0|CH1|CH2|CH3|CH5&signalCodeFilterMethod=Equals&signalCodeAttribute=engineeringSignalCode"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txCBOMManual" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "site": 500,
          "project": "T70021",
          "signalCodeFilter": "CH0|CH1|CH2|CH3|CH5",
          "signalCodeFilterMethod": "Equals",
          "signalCodeAttribute": "engineeringSignalCode"
        }
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "site" should be equal to the number 500
    And the JSON node "project" should be equal to the string "T70021"
    And the JSON node "items" should have 30 elements
    And the JSON node "items[0].partNumber" should be equal to "1051213"
    And the JSON node "items[0].engineeringRevision" should be equal to the string "I2"
    And the JSON node "items[0].engineeringSignalCode" should be equal to the string "CH0"
    And the JSON node "items[0].position" should be equal to the number 10
    And the JSON node "items[0].itemDescription" should be equal to the string "CH0,INFORMATION           [EN]"
    And the JSON node "items[0].itemOtherDescription" should be equal to the string ""
    And the JSON node "items[0].signalCodeDescription" should be equal to the string "Chapter 0"
    And the JSON node "items[0].quantity" should be equal to the number 0
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/cbom/schemas/manuals.json"