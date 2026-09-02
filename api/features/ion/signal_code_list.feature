Feature: Signal code can be fetched through the API

  Scenario: Filters are declared on resource
    Given the class "App\ION\Resources\Manufacturing\JobShop\SignalCode" is exposed on the API

  Scenario: Request Tasks without being authenticated should not be permitted
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/signal_codes"
    Then the response status code should be 401

  Scenario: Tasks should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/signal_codes"
    Then the response status code should be 403

  Scenario: Request a collection of Tasks
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/signal_codes"
    And a "txItemSignals" SOAP client has been created
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
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "@id" should be equal to the string "/ion/signal_codes"
    And the JSON node "@type" should be equal to the string "hydra:Collection"
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/signal_code_list.json"
    And the JSON node "hydra:member[0].code" should be equal to the string "***"
    And the JSON node "hydra:member[0].description" should be equal to the string "Check Item Text"
