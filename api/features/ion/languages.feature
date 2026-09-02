Feature: Test Language API

  Scenario: Request languages without being authenticated should not be permitted
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/languages"
    Then the response status code should be 401

  Scenario: Languages should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/languages"
    Then the response status code should be 403

  Scenario: Request list of all languages on LN
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/languages"
    And a "txDataLanguages" SOAP client has been created
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
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/languages.json"
    And the JSON node "hydra:member[0].iso639_2" should be equal to the string "en"
    And the JSON node "hydra:member[0].codeAlpha2" should be equal to the string ""
    And the JSON node "hydra:member[0].iso639" should be equal to the string "en"
    And the JSON node "hydra:member[1].iso639_2" should be equal to the string "en_US"
    And the JSON node "hydra:member[1].codeAlpha2" should be equal to the string "US"
    And the JSON node "hydra:member[1].iso639" should be equal to the string "en"
    And the JSON node "hydra:member" should have 6 elements