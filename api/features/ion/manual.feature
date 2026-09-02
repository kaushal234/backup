Feature: A CBOM can be fetched through the API for the manual generation

  Scenario: Filters are declared on resource
    Given the class "App\ION\Resources\Manufacturing\JobShop\ManualCustomizedBillOfMaterials" is exposed on the API
    Then the filter "date" should be available and its type should be "string"
    Then the filter "signalCodeFilter" should be available and its type should be "string"
    Then the filter "signalCodeFilterMethod" should be available and its type should be "string"
    Then the filter "signalCodeAttribute" should be available and its type should be "string"
    Then the filter "otherLanguage" should be available and its type should be "string"

  Scenario: Request a single CBOM for manual
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/manual_customized_bill_of_materials/site=520;project=T70986"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txListExtended" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "site": 520,
          "project": "T70986"
        }
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "site" should be equal to the number 520
    And the JSON node "project" should be equal to the string "T70986"
    And the JSON node "product" should be equal to "JET-16"
    And the JSON node "items[0].partNumber" should be equal to 1235000
    And the JSON node "items[0].position" should be equal to the number 2
    And the JSON node "items[0].standardItem" should be equal to "JET-16"
    And the JSON node "items[0].quantity" should be equal to the number 1
    And the JSON node "items[0].engineeringRevision" should be equal to the string "B"
    And the JSON node "items[0].unitOfMeasure" should be equal to the string "EA"
    And the JSON node "items[0].engineeringSignalCode" should be equal to the string ""
    And the JSON node "items[0].itemDescription" should be equal to the string "KIT FLEX-RAC JET-16 AUTO B.P."
    And the JSON node "items[0].level" should be equal to the number 0
    And the JSON node "items[0].operation" should be equal to 20
    And the JSON node "items" should have 1448 elements
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/cbom/schemas/customized_bill_of_materials_extended.json"

  Scenario: Request a single CBOM for manual and filter the parent items by item signal code
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/manual_customized_bill_of_materials/site=500;project=T70021?signalCodeFilter=CH0|CH1&signalCodeFilterMethod=Equals&signalCodeAttribute=engineeringSignalCode"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txListExtended" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "site": 500,
          "project": "T70021",
          "signalCodeFilter": "CH0|CH1",
          "signalCodeFilterMethod": "Equals",
          "signalCodeAttribute": "engineeringSignalCode"
        }
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "site" should be equal to the number 500
    And the JSON node "project" should be equal to the string "T70021"
    And the JSON node "product" should be equal to "TMX-150"
    And the JSON node "items" should have 4 elements
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/cbom/schemas/customized_bill_of_materials_extended.json"

  Scenario: Request CBOM Part book PDF of a project
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/pdf"
    When I send a "GET" request to "/ion/manual_customized_bill_of_materials_pdf/site=500;project=T70021?signalCodeFilter=CH0|CH1&signalCodeFilterMethod=Equals&signalCodeAttribute=engineeringSignalCode"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txListExtended" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "site": 500,
          "project": "T70021",
          "signalCodeFilter": "CH0|CH1",
          "signalCodeFilterMethod": "Equals",
          "signalCodeAttribute": "engineeringSignalCode"
        }
      }
    }
    """
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/pdf"