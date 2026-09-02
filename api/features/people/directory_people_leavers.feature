Feature: Leavers page — access control

  Scenario: HR user sees all recent leavers
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/people?peopleCurrentlyOrFutureDisabled[disabledAt]=2000-01-01"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be superior to the number 0

  Scenario: Basic user sees no leavers (no feature, not module admin)
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/people?peopleCurrentlyOrFutureDisabled[disabledAt]=2000-01-01"
    Then the response status code should be 200
    And the JSON node "hydra:member" should have 0 elements

  Scenario: Module mainAdmin sees leavers linked to their module's update tasks (already left and planned)
    Given I authenticate as the intranet user "tonton@david.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/people?peopleCurrentlyOrFutureDisabled[disabledAt]=2000-01-01"
    Then the response status code should be 200
    # One already left (jean-partie-depuis-peu) + one planned (bientot-partie) in their scope.
    And the JSON node "hydra:totalItems" should be equal to the number 2

  Scenario: Module mainAdmin sees planned (not yet left) leavers linked to their modules
    Given I authenticate as the intranet user "tonton@david.fr"
    And I add "Accept" header equal to "application/ld+json"
    # status=Planned only maps to disabled=false; without the fix the planned branch
    # is gated behind the feature, so a module admin would see 0 here.
    When I send a "GET" request to "/people?peopleCurrentlyOrFutureDisabled[disabledAt]=2000-01-01&disabled=false"
    Then the response status code should be 200
    # Only the planned leaver (bientot-partie, disabled=false) in their scope.
    And the JSON node "hydra:totalItems" should be equal to the number 1

  Scenario: Module mainAdmin sees fewer leavers than HR
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/people?peopleCurrentlyOrFutureDisabled[disabledAt]=2000-01-01"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be superior to the number 5
    Given I authenticate as the intranet user "tonton@david.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/people?peopleCurrentlyOrFutureDisabled[disabledAt]=2000-01-01"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be inferior to the number 3
