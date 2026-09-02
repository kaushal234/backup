Feature: Sites can be fetched through the API

  Scenario: Request a single Site without being authenticated should not be permitted
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/sites?itemsPerPage=10"
    Then the response status code should be 401
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/sites/420"
    Then the response status code should be 401

  Scenario: Sites should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/sites?itemsPerPage=10"
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/sites/420"
    Then the response status code should be 403

  Scenario: Request a collection of Sites
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/sites"
    And a "Site_ES" SOAP client has been created
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
    Then the response status code should be 200
    And the JSON node "hydra:member[0].@id" should be equal to the string "/ion/sites/220"
    And the JSON node "hydra:member[0].siteID" should be equal to "220"
    And the JSON node "hydra:member[0].siteDescription" should be equal to the string "TLD PV"
    And the JSON node "hydra:member[0].siteAddressCode" should be equal to "S00000220"
    And the JSON node "hydra:member[0].siteAddressName" should be equal to "POWERVAMP Ltd"
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/sites/schemas/collection.json"

  Scenario: Request a single Site can't work because the ION endpoint does not work ʕ •`ᴥ•´ʔ
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/sites/420"
    Then the response status code should be 404
