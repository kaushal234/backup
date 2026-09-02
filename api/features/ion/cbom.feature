Feature: A CBOM can be fetched through the API

  Scenario: Request a single CBOM without being authenticated should not be permitted
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/customized_bill_of_materials/site=500;project=PR0000098"
    Then the response status code should be 401

  Scenario: Filters are declared on resource
    Given the class "App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials" is exposed on the API
    Then the filter "depth" should be available and its type should be "string"
    Then the filter "date" should be available and its type should be "string"
    Then the filter "flatResult" should be available and its type should be "string"
    Then the filter "productSignalCodeFilter" should be available and its type should be "string"
    Then the filter "productSignalCodeFilterMethod" should be available and its type should be "string"
    Then the filter "productSignalCodeAttribute" should be available and its type should be "string"
    Then the filter "itemsSignalCodeFilter" should be available and its type should be "string"
    Then the filter "itemsSignalCodeFilterMethod" should be available and its type should be "string"
    Then the filter "itemsSignalCodeAttribute" should be available and its type should be "string"
    Then the filter "otherLanguage" should be available and its type should be "string"

  Scenario: Request a single CBOM for PIO
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/customized_bill_of_materials/site=500;project=T70021"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txList" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "site": 500,
          "project": "T70021"
        }
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "site" should be equal to the number 500
    And the JSON node "project" should be equal to the string "T70021"
    And the JSON node "product" should be equal to "TMX-150"
    And the JSON node "items[0].partNumber" should be equal to 1051213
    And the JSON node "items[0].position" should be equal to the number 10
    And the JSON node "items[0].standardItem" should be equal to "TMX-150"
    And the JSON node "items[0].quantity" should be equal to the number 1
    And the JSON node "items[0].productQuantity" should be equal to the number 0
    And the JSON node "items[0].engineeringRevision" should be equal to the string "I2"
    And the JSON node "items[0].unitOfMeasure" should be equal to the string "EA"
    And the JSON node "items[0].engineeringSignalCode" should be equal to the string "CH0"
    And the JSON node "items[0].itemDescription" should be equal to the string "CH0,INFORMATION           [EN]"
    And the JSON node "items[0].level" should be equal to the number 0
    And the JSON node "items[0].operation" should be equal to 0
    And the JSON node "items" should have 71 elements
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/cbom/schemas/customized_bill_of_materials.json"

  Scenario: Request a CBOM with variant
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/customized_bill_of_materials/site=660;project=T70000?normalizationGroups[]=variant"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txList" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "site": 660,
          "project": "T70000"
        }
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "productVariants" should have 1 element
    And the JSON node "productVariants[0].@type" should be equal to the string "ProductVariant"
    And the JSON node "productVariants[0].options" should have 49 element
    And the JSON node "productVariants[0].options[0].@type" should be equal to the string "ProductVariantOption"
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/cbom/schemas/customized_bill_of_materials_variants.json"

  Scenario: Request a single CBOM for PIO and filter the parent items by item signal code
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/customized_bill_of_materials/site=500;project=T70021?productSignalCodeFilter=CH0|CH1&productSignalCodeFilterMethod=Equals&productSignalCodeAttribute=engineeringSignalCode"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    Then the response status code should be 200
    And this client has been called on the operation "txList" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "site": 500,
          "project": "T70021",
          "productSignalCodeFilter": "CH0|CH1",
          "productSignalCodeFilterMethod": "Equals",
          "productSignalCodeAttribute": "engineeringSignalCode"
        }
      }
    }
    """
    And the JSON node "site" should be equal to the number 500
    And the JSON node "project" should be equal to the string "T70021"
    And the JSON node "product" should be equal to "TMX-150"
    And the JSON node "items" should have 4 elements
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/cbom/schemas/customized_bill_of_materials.json"

  Scenario: Request a CBOM with childs and parent with item signal code
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/extended_customized_bill_of_materials/site=500;project=T86458?depth=20&itemsSignalCodeAttribute=itemSignalCode&itemsSignalCodeFilter=I&itemsSignalCodeFilterMethod=StartsWith&otherLanguage=fr&productSignalCodeAttribute=itemSignalCode&productSignalCodeFilter=I&productSignalCodeFilterMethod=StartsWith"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txListExtended" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "site": 500,
          "project": "T86458",
          "depth": "20",
          "productSignalCodeFilter": "I",
          "productSignalCodeFilterMethod": "StartsWith",
          "productSignalCodeAttribute": "itemSignalCode",
          "itemsSignalCodeFilter": "I",
          "itemsSignalCodeFilterMethod": "StartsWith",
          "itemsSignalCodeAttribute": "itemSignalCode",
          "otherLanguage": "fr"
        }
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "site" should be equal to the number 500
    And the JSON node "project" should be equal to the string "T86458"
    And the JSON node "product" should be equal to "TMX-150 TCR INTERNATIONAL"
    And the JSON node "items[7].partNumber" should be equal to 4571001
    And the JSON node "items[7].position" should be equal to the number 100
    And the JSON node "items[7].standardItem" should be equal to "TMX-150 TCR INTERNATIONAL"
    And the JSON node "items[7].quantity" should be equal to the number 1
    And the JSON node "items[7].productQuantity" should be equal to the number 0
    And the JSON node "items[7].engineeringRevision" should be equal to the string "J"
    And the JSON node "items[7].unitOfMeasure" should be equal to the string "EA"
    And the JSON node "items[7].engineeringSignalCode" should be equal to the string "DCL"
    And the JSON node "items[7].itemDescription" should be equal to the string "DOC LIST TMX-150"
    And the JSON node "items[7].level" should be equal to the number 0
    And the JSON node "items[7].operation" should be equal to 0
    And the JSON node "items[7].itemSignalCode" should be empty
    And the JSON node "items[7].children[0].itemSignalCode" should be equal to the string "DCL"
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/cbom/schemas/extended_customized_bill_of_materials.json"