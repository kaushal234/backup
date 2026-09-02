Feature: A BOM can be fetched through the API for the manual generation

  Scenario: Filters are declared on resource
    Given the class "App\ION\Resources\Manufacturing\JobShop\BillOfMaterialItem" is exposed on the API
    Then the filter "date" should be available and its type should be "string"
    Then the filter "signalCodeFilter" should be available and its type should be "string"
    Then the filter "signalCodeFilterMethod" should be available and its type should be "string"
    Then the filter "signalCodeAttribute" should be available and its type should be "string"
    Then the filter "otherLanguage" should be available and its type should be "string"

  Scenario: Request a BOM
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/bill_of_material_items/site=500;project=;product=1051213"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txShow" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "site": 500,
          "project": "",
          "product": "1051213"
        }
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "site" should be equal to the number 500
    And the JSON node "project" should be equal to the string ""
    And the JSON node "product" should be equal to "1051213"
    And the JSON node "items" should have 0 elements
    And the JSON node "engineeringSignalCode" should be equal to the string "CH0"
    And the JSON node "itemDescription" should be equal to the string "CH0,INFORMATION           [EN]"
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/cbom/schemas/bill_of_material_item.json"

  Scenario: Request a single BOM with wrong PN
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/bill_of_material_items/site=500;project=;product=105121P"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txShow" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "site": 500,
          "project": "",
          "product": "105121P"
        }
      }
    }
    """
    Then the response status code should be 404



  Scenario: A vendor user can't request a CBOM of an item that has never been mentioned in one of their POs or RFQs
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/bill_of_material_items/site=500;project=;product=1051213"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txShow" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "site": 500,
          "project": "",
          "product": "1051213"
        }
      }
    }
    """
    And a "txBillOfMaterialsSecurity" SOAP client has been created
    And this client has been called on the operation "txShow" with the following request:
    """
    {
      "DataArea": {
        "txBillOfMaterialsSecurity": {
          "contactCode": "420000001",
          "project": "",
          "item": "1051213",
          "site": 500,
          "depth": 99
        }
      }
    }
    """
    And a total of 2 requests has been sent to ION
    Then the response status code should be 403

  Scenario: A vendor user can request a CBOM of an item that has been mentioned in one of their POs or RFQs
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/bill_of_material_items/site=500;project=;product=6400072"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txShow" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "site": 500,
          "project": "",
          "product": "6400072"
        }
      }
    }
    """
    And a "txBillOfMaterialsSecurity" SOAP client has been created
    And this client has been called on the operation "txShow" with the following request:
    """
    {
      "DataArea": {
        "txBillOfMaterialsSecurity": {
          "contactCode": "420000001",
          "project": "",
          "item": "6400072",
          "site": 500,
          "depth": 99
        }
      }
    }
    """
    And a total of 2 requests has been sent to ION
    Then the response status code should be 200

  Scenario: Request drawing of an item by naming rule
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/bill-of-materials/drawings/site=500;project=;product=1051213"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txDrawing" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "site": 500,
          "project": "",
          "product": "1051213"
        }
      }
    }
    """
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/pdf"

  Scenario: Request drawing of an item by drawing field (file naming different from rule naming)
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/bill-of-materials/drawings/site=500;project=;product=1143658"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txDrawing" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "site": 500,
          "project": "",
          "product": "1143658"
        }
      }
    }
    """
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/pdf"

  Scenario: Request BOM PDF testing of an item by naming rule
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/pdf"
    When I send a "GET" request to "/ion/bill_of_material_item_pdf/site=500;project=;product=1051213"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txShow" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "site": 500,
          "project": "",
          "product": "1051213"
        }
      }
    }
    """
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/pdf"

  Scenario: Request BOM PDF testing with drawing field different from naming rule
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/pdf"
    When I send a "GET" request to "/ion/bill_of_material_item_pdf/site=500;project=;product=1143658?date=2023-01-20T00:00:00-05:00"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txShow" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "site": 500,
          "project": "",
          "product": "1143658",
          "date": "2023-01-20"
        }
      }
    }
    """
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/pdf"

  Scenario: Request drawing of an item by wrong drawing field (file not exist with the field but exist with the rule) should fail
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/bill-of-materials/drawings/site=500;project=;product=1170830"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txDrawing" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "site": 500,
          "project": "",
          "product": "1170830"
        }
      }
    }
    """
    Then the response status code should be 404