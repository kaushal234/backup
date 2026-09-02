Feature: A CBOM Materials can be fetched through the API

  Scenario: Request a single CBOM without being authenticated should not be permitted
    Given I add "Accept" header equal to "application/pdf"
    When I send a "GET" request to "/ion/bill-of-materials/shopfloor_pdfs/site=640;project=;product=1152805"
    Then the response status code should be 401

  Scenario: Filters are declared on resource
    Given the class "App\ION\Resources\Manufacturing\JobShop\BillOfMaterials\ShopfloorPdf" is exposed on the API
    Then the filter "date" should be available and its type should be "string"
    Then the filter "otherLanguage" should be available and its type should be "string"

  Scenario: Request a single CBOM
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/pdf"
    When I send a "GET" request to "/ion/bill-of-materials/shopfloor_pdfs/site=640;project=;product=1152805"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txBomShopfloorPDF" with the following request:
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
    Then the header "Content-Type" should be equal to "application/pdf"
    Then the header "Content-Disposition" should be equal to "inline; filename=pdf-testing-1152805.pdf"