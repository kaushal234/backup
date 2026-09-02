Feature: Test search nested properties

  Scenario: Existing nested property
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/locations?capability.factory=1"
    Then the response status code should be 200

  Scenario: Invalid nested property
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/locations?chuck.norris=yeah"
    Then the response status code should be 400
