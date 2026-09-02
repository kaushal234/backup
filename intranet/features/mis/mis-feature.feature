Feature: Mis / Feature

  Scenario: As an anonymous user, test that i'm not allowed to see features pages
    When I go to "/mis/features"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/mis/features/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i can't see features related to a feature
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/"
    Then the response status code should be 200
    When I go to "/mis/features"
    Then the response status code should be 200
    And I should be on "/"
    And I should see "You do not have permissions"
    When I go to "/mis/features/1/show"
    Then the response status code should be 200
    And I should be on "/"
    And I should see "You do not have permissions"

  Scenario: As a superuser, test that i can see features related to a feature
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/features"
    Then the response status code should be 200
    And The module should be "MIS"
    And I should be on "/mis/features"
    When I go to "/mis/features/1/show"
    Then the response status code should be 200
    And I should be on "/mis/features/1/show"