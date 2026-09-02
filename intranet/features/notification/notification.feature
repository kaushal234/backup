Feature: Notification

  Scenario: As an anonymous user, test that i'm not allowed to see notification page
    When I go to "/notifications"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see notification page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/notifications"
    Then the response status code should be 200
    And I should see "Read all"
    And I should see "Delete all"