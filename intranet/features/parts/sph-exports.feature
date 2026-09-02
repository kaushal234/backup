Feature: SPH Exports

  Scenario: As an anonymous user, test that i'm not allowed to see SPH MIP Exports
    When I go to "/parts/exports/prices"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: Download items exports for a given SPH
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/parts/exports/prices"
    Then the response status code should be 200
    And The module should be "SPH"
    And I should see an "a[href$='/parts/exports/prices/300']" element
    And I should see "MIP Export"
