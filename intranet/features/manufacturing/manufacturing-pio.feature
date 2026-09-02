Feature: Manufacturing pio

  Scenario: As an anonymous user, test that i'm not allowed to see pio timekeeping page
    When I go to "/manufacturing/timekeeping"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm not allowed to see pio timekeeping page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/manufacturing/timekeeping"
    Then I should not be on "/manufacturing/timekeeping"
    And I should see "You do not have permissions"

  Scenario: As a super user, test that i'm allowed to see pio timekeeping page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/manufacturing/timekeeping"
    Then the response status code should be 200
