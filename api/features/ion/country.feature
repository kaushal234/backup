Feature: Test ion Country API

  Scenario: Request all Countries
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/countries"
    Then the response status code should be 200
    And a "txCountries" SOAP client has been created
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
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/country/schemas/countries.json"

  Scenario: Request one country
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/countries/AZ"
    Then the response status code should be 200
    And a "txCountries" SOAP client has been created
    And this client has been called on the operation "Show" with the following request:
    """
    {
      "DataArea": {
        "txCountries": {
          "country": "AZ"
        }
      }
    }
    """
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/country/schemas/country.json"

  Scenario: Filters are declared on resource
    Given the class "App\ION\Resources\MasterData\CodeDefinitions\LogisticCodes\Country" is exposed on the API
    Then the ION filter "description" should be available and its type should be "string"
    Then the ION filter "country" should be available and its type should be "string"
