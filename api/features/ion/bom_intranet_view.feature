Feature: A CBOM Materials can be fetched through the API

  Scenario: Request a single CBOM without being authenticated should not be permitted
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/bill-of-materials/intranet_views/site=640;project=;product=1152805"
    Then the response status code should be 401

  Scenario: Filters are declared on resource
    Given the class "App\ION\Resources\Manufacturing\JobShop\BillOfMaterials\IntranetView" is exposed on the API
    Then the filter "date" should be available and its type should be "string"
    Then the filter "otherLanguage" should be available and its type should be "string"

  Scenario: Request a single CBOM
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/bill-of-materials/intranet_views/site=640;project=;product=1152805"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txBom" with the following request:
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
#    And the JSON node "itemOtherDescription" should be equal to the string ""
    And the JSON node "itemSelectionCode" should be equal to the string "SHA"
    And the JSON node "textByLanguages" should have 0 elements
#    And the JSON node "textByLanguages[0].name" should be equal to the string "English"
#    And the JSON node "textByLanguages[0].text" should be equal to "English text test\n"
    And the JSON node "items" should have 5 elements
    And the JSON node "items[0].itemSelectionCode" should be equal to the string "STL"
    And the JSON node "items[0].level" should be equal to the number 0
    And the JSON node "items[0].partNumber" should be equal to "1085287"
    And the JSON node "items[0].position" should be equal to the number 1
    And the JSON node "items[0].itemDescription" should be equal to the string "CALE PALONNIER"
#    And the JSON node "items[0].itemOtherDescription" should be equal to the string ""
    And the JSON node "items[0].engineeringDescription" should be equal to the string "CALE PALONNIER"
    And the JSON node "items[0].quantity" should be equal to the number 1
    And the JSON node "items[0].productQuantity" should be equal to the number 1
    And the JSON node "items[0].unitOfMeasure" should be equal to the string "EA"
    And the JSON node "items[0].engineeringRevisionEffectiveDate" should be equal to the string "2014-11-05T23:00:00Z"
    And the JSON node "items[0].engineeringRevisionExpiryDate" should be equal to the string "9999-12-29T23:00:00Z"
    And the JSON node "items[0].partNumberProject" should be equal to the string ""
    And the JSON node "items[0].engineeringRevision" should be equal to "B"
    And the JSON node "items[0].engineeringSignalCode" should be equal to the string ""
    And the JSON node "items[0].extraInformation" should be equal to the string ""
    And the JSON node "items[0].operation" should be equal to the number 0
    And the JSON node "items[0].pmoc" should be equal to the string "pc"
    And the JSON node "items[0].preventive" should be true
    And the JSON node "items[0].maintenance" should be false
    And the JSON node "items[0].overhaul" should be false
    And the JSON node "items[0].critical" should be true
    And the JSON node "items[0].textByLanguages" should have 2 elements
    And the JSON node "items[0].textByLanguages[0].name" should be equal to the string "English"
    And the JSON node "items[0].textByLanguages[0].text" should be equal to "Test text english\n"
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/bom/schemas/intranet_view.json"

  Scenario: Request a single CBOM with non conformity normalisation context
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/bill-of-materials/intranet_views/site=500;project=;product=1186245?normalizationGroups[]=ion:item:non_conformity"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txBom" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "site": 500,
          "project": "",
          "product": "1186245"
        }
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "site" should be equal to the number 500
    And the JSON node "numberNonConformity" should be equal to the number 0
    And the JSON node "items[0].numberNonConformity" should be equal to the number 0
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/bom/schemas/intranet_view.json"

  Scenario: Request a Multi Level BOM
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/bill-of-materials/intranet_multi_level_views/site=640;project=;product=1152805?depth=2"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txMultiLevelBom" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "site": 640,
          "project": "",
          "product": "1152805",
          "depth": "2"
        }
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "site" should be equal to the number 640
    And the JSON node "project" should be equal to the string ""
    And the JSON node "itemDescription" should be equal to the string "ASSEMBLY SHIM"
#    And the JSON node "itemOtherDescription" should be equal to the string ""
    And the JSON node "itemSelectionCode" should be equal to the string "SHA"
    And the JSON node "items" should have 5 elements
    And the JSON node "items[0].itemSelectionCode" should be equal to the string "STL"
    And the JSON node "items[0].level" should be equal to the number 0
    And the JSON node "items[0].partNumber" should be equal to "1085287"
    And the JSON node "items[0].position" should be equal to the number 1
    And the JSON node "items[0].itemDescription" should be equal to the string "CALE PALONNIER"
#    And the JSON node "items[0].itemOtherDescription" should be equal to the string ""
    And the JSON node "items[0].engineeringDescription" should be equal to the string "CALE PALONNIER"
    And the JSON node "items[0].quantity" should be equal to the number 1
    And the JSON node "items[0].unitOfMeasure" should be equal to the string "EA"
    And the JSON node "items[0].engineeringRevisionEffectiveDate" should be equal to the string "2014-11-05T23:00:00Z"
    And the JSON node "items[0].engineeringRevisionExpiryDate" should be equal to the string "9999-12-29T23:00:00Z"
    And the JSON node "items[0].partNumberProject" should be equal to the string ""
    And the JSON node "items[0].engineeringRevision" should be equal to "B"
    And the JSON node "items[0].engineeringSignalCode" should be equal to the string ""
    And the JSON node "items[0].extraInformation" should be equal to the string ""
    And the JSON node "items[0].operation" should be equal to the number 0
    And the JSON node "items[0].pmoc" should be equal to the string "pc"
    And the JSON node "items[0].preventive" should be true
    And the JSON node "items[0].maintenance" should be false
    And the JSON node "items[0].overhaul" should be false
    And the JSON node "items[0].critical" should be true
    And the JSON node "items[2].items" should have 1 element
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/bom/schemas/intranet_view.json"
