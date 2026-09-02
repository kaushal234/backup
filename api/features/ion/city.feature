Feature: Test City API

  Scenario: Request all Cities
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/cities"
    Then the response status code should be 200
    And a "txCities" SOAP client has been created
    And this client has been called on the operation "List" with the following request:
    """
    {
      "ControlArea": {
        "maxNumberOfObjects": 500,
        "Filter": {
            "LogicalExpression": {
                "logicalOperator": "and"
            }
        }
      }
    }
    """
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/city/schemas/cities.json"

  Scenario: Filters are declared on resource
    Given the class "App\ION\Resources\MasterData\Addresses\City" is exposed on the API
    Then the ION filter "description" should be available and its type should be "string"
    Then the ION filter "country" should be available and its type should be "string"
