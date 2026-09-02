Feature: Aircraft

  Scenario: As an anonymous user, test that i'm not allowed to see aircraft pages
    When I go to "/sales/aircrafts"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see aircraft pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/aircrafts"
    Then the response status code should be 200
    And The module should be "AC"
    And I should not see an "a[href$='/sales/aircrafts/add']" element

  Scenario: As a PSE user, test that i'm allowed to see aircraft pages
    Given I authenticate as "user-pse@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/aircrafts"
    Then the response status code should be 200
    And The module should be "AC"
    And I should see an "a[href$='/sales/aircrafts/add']" element
    And I should see an "a[href$='/sales/aircrafts/1/edit']" element

  @javascript
  Scenario: As a basic user, test that i'm not allowed to edit aircraft
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/aircrafts/1/edit"
    And I wait until I see "You do not have permissions"

  @javascript
  Scenario: As a basic user, test that i'm not allowed to add aircraft
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/aircrafts/add"
    And I wait until I see "You do not have permissions"
