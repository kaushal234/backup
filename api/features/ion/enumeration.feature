Feature: Test Enumeration API

  Scenario: Request enumerations without being authenticated should not be permitted
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/enumeration_lists/package=tc;domain=koor"
    Then the response status code should be 401

  Scenario: Enumeration should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/enumeration_lists/package=tc;domain=koor"
    Then the response status code should be 403

  Scenario: Request all tckoor enumerations
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/enumeration_lists/package=tc;domain=koor"
    And a "txEnums" SOAP client has been created
    And this client has been called on the operation "txShow" with the following request:
    """
    {
      "DataArea": {
        "txEnums": {
          "package": "tc",
          "domain": "koor"
        }
      }
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/enumeration_list.json"
    And the JSON node "enumerations[0].code" should be equal to 1
    And the JSON node "enumerations[0].constant" should be equal to the string "act.sfc"
    And the JSON node "enumerations[1].code" should be equal to 2
    And the JSON node "enumerations[1].constant" should be equal to the string "act.pur"
    And the JSON node "enumerations" should have 83 elements