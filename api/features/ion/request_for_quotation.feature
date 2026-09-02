Feature: Test Request For Quotation API

  Scenario: Request a single Request for quotation without being authenticated should not be permitted
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/request_for_quotations"
    Then the response status code should be 401

  Scenario: Request for quotation should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/request_for_quotations"
    Then the response status code should be 403

  Scenario: Filters are declared on resource
    Given the class "App\ION\Resources\Procurement\RequestForQuotation" is exposed on the API

  Scenario: Request all Request For Quotations
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/request_for_quotations"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/request_for_quotation/schemas/request_for_quotations.json"
    And the JSON node "hydra:member" should have 9 elements

  Scenario: Request a list of Request For Quotation as a vendor user
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/request_for_quotations"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/request_for_quotation/schemas/request_for_quotations.json"
    And the JSON node "hydra:member" should have 2 elements

  Scenario: Request a given Request For Quotation as a vendor user
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/request_for_quotations/RFQ000005"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/request_for_quotation/schemas/request_for_quotation.json"
    And the JSON node "bidders" should have 2 elements

  Scenario: Request a given Request For Quotation as a vendor user should not be possible if I don't have permissions
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/request_for_quotations/RFQ000001"
    Then the response status code should be 403

  Scenario: Download a Request For Quotation Zip
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    Given I add "Accept" header equal to "application/zip"
    When I send a "GET" request to "/ion/request_for_quotations/RFQ000005/zip"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txMultiLevelBom" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "product": "1049951",
          "site": 400,
          "project": "",
          "depth": 20,
          "date": "2022-11-02T17:13:00Z"
        }
      }
    }
    """
    And a total of 1 request has been sent to ION
    Then the response status code should be 204
