Feature: Aircraft Compatibility

  Scenario: As an anonymous user, test that i'm not allowed to see aircraft compatibilities pages
    When I go to "/sales/aircraft-compatibilities"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/sales/aircraft-compatibilities/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see aircraft compatibilities pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/aircraft-compatibilities"
    Then the response status code should be 200
    When I go to "/sales/aircraft-compatibilities/2/show"
    Then the response status code should be 200
    And The module should be "AC"
    And I should not see an "a[href$='/sales/aircraft-compatibilities/add']" element
    And I should not see an "a[href$='/sales/aircraft-compatibilities/2/edit']" element

  Scenario: As a PSE user, test that i'm allowed to see aircraft compatibilities pages
    Given I authenticate as "user-pse@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/aircraft-compatibilities"
    Then the response status code should be 200
    When I go to "/sales/aircraft-compatibilities/2/show"
    Then the response status code should be 200
    And The module should be "AC"
    And I should see an "a[href$='/sales/aircraft-compatibilities/add']" element
    And I should see an "a[href$='/sales/aircraft-compatibilities/2/edit']" element

  Scenario: As a basic user, test that i'm not allowed to edit aircraft compatibilities
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/aircraft-compatibilities/1/edit"
    And I should see "You do not have permissions"

  Scenario: As a basic user, test that i'm not allowed to add aircraft compatibilities
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/aircraft-compatibilities/add"
    And I should see "You do not have permissions"
