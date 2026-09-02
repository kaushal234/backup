Feature: ERP Tasks can be fetched through the API

  Scenario: Filters are declared on resource
    Given the class "App\ION\Resources\Manufacturing\JobShop\ShopLayout\Miscellaneous\Task" is exposed on the API
    Then the filter "checkActive" should be available and its type should be "string"

  Scenario: Request Tasks without being authenticated should not be permitted
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/tasks"
    Then the response status code should be 401

  Scenario: Tasks should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/tasks"
    Then the response status code should be 403

  Scenario: Request a collection of Tasks
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/tasks?checkActive=1"
    And a "txTasks" SOAP client has been created
    And this client has been called on the operation "txList" with the following request:
    """
    {
      "ControlArea": {
        "maxNumberOfObjects": 500,
        "Filter": {
          "LogicalExpression": {
            "logicalOperator": "and"
          }
        }
      },
      "DataArea": {
          "txTasks": {
              "checkActive": "1"
          }
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "@id" should be equal to the string "/ion/tasks"
    And the JSON node "@type" should be equal to the string "hydra:Collection"
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/task/schemas/collection.json"
    And the JSON node "hydra:member[0].code" should be equal to "100"
    And the JSON node "hydra:member[0].description" should be equal to the string "Warehouse Paid Break"
