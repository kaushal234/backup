Feature: A CBOM can be fetched through the API

  Scenario: Request a single CBOM without being authenticated should not be permitted
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/customized-bill-of-materials/part_number_lists/site=500;project=PR0000098"
    Then the response status code should be 401

  Scenario: Filters are declared on resource
    Given the class "App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials\PartNumberList" is exposed on the API
    Then the filter "date" should be available and its type should be "string"

  Scenario: Request a single CBOM
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/customized-bill-of-materials/part_number_lists/site=500;project=T70021"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txPartNumberList" with the following request:
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
    And the JSON node "items[0].partNumber" should be equal to "1051213"
    And the JSON node "items" should have 71 elements
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/cbom/schemas/part_number_list.json"