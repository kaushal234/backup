Feature: Test documentation
  Scenario: Get jsonld documentation
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/docs"
    Then the response status code should be 200

  Scenario: Get html documentation
    Given I add "Accept" header equal to "text/html"
    When I send a "GET" request to "/docs"
    Then the response status code should be 200