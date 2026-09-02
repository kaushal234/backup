Feature: New Comers page — access control

  Scenario: HR user sees all recent new comers
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/people?peopleCurrentlyOrFutureEnabled[enableAt]=2000-01-01"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be superior to the number 0

  Scenario: Basic user sees no new comers (no feature, not module admin)
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/people?peopleCurrentlyOrFutureEnabled[enableAt]=2000-01-01"
    Then the response status code should be 200
    And the JSON node "hydra:member" should have 0 elements

  Scenario: Module mainAdmin sees new comers linked to their module's update tasks
    Given I authenticate as the intranet user "tonton@david.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/people?peopleCurrentlyOrFutureEnabled[enableAt]=2000-01-01"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be superior to the number 0

  Scenario: Module mainAdmin sees planned (not yet arrived) new comers linked to their modules
    Given I authenticate as the intranet user "tonton@david.fr"
    And I add "Accept" header equal to "application/ld+json"
    # status=Planned only maps to disabled=true; without the fix the planned branch
    # is gated behind the feature, so a module admin would see 0 here.
    When I send a "GET" request to "/people?peopleCurrentlyOrFutureEnabled[enableAt]=2000-01-01&disabled=true"
    Then the response status code should be 200
    # Only the planned new comer (jean-arrive-dans-2-jours, disabled=true) in their scope.
    And the JSON node "hydra:totalItems" should be equal to the number 1

  Scenario: Module mainAdmin sees fewer new comers than HR
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/people?peopleCurrentlyOrFutureEnabled[enableAt]=2000-01-01"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be superior to the number 100
    Given I authenticate as the intranet user "tonton@david.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/people?peopleCurrentlyOrFutureEnabled[enableAt]=2000-01-01"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be inferior to the number 20
    And the JSON node "hydra:totalItems" should be superior to the number 0
