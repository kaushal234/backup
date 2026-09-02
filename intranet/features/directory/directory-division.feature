Feature: Directory / Division

  Scenario: As an anonymous user, test that i'm not allowed to see division pages
    When I go to "/directory/divisions"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/directory/divisions/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see division pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/divisions"
    Then the response status code should be 200
    And The module should be "DIR"
    And I should see "Division Listing"
    And I should not see an "a[href$='/directory/divisions/add']" element
    And I should not see an "a[href$='/directory/divisions/1/edit']" element
    And the ".footable tbody tr:nth-child(1) td:nth-child(1)" element should contain "By Zero"

  Scenario: As a superuser, test that i'm allowed to see division pages and perform some admin actions
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/divisions"
    And the response status code should be 200
    And I should see an "a[href$='/directory/divisions/add']" element
    And I should see an "a[href$='/directory/divisions/1/edit']" element
    And I should see an "a[href$='/directory/divisions/2/edit']" element

  Scenario: As a basic user, test that i'm allowed to see a single division page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/divisions/1/show"
    Then the response status code should be 200
    And I should not see an "a[href$='/directory/divisions/1/edit']" element

  Scenario: As a superuser, test that i'm allowed to see a single division page and perform some admin actions
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/divisions/1/show"
    Then the response status code should be 200
    And I should see an "a[href$='/directory/divisions/1/edit']" element

  Scenario: As a basic user, test that i can't add a division
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/divisions/add"
    And I should see "You do not have permissions"

  Scenario: As a superuser, test that i can add a division
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/divisions/add"
    Then the response status code should be 200
    When I fill in "division[name]" with "D'Yves Ision"
    And press "submit"
    Then I should be on "/directory/divisions/5/show"
    And the response status code should be 200
    And I should see "D'Yves Ision"

  Scenario: As a basic user, test that i can't edit a division
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/divisions/1/edit"
    And I should see "You do not have permissions"

  Scenario: As a superuser, test that i can edit a division
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/divisions/1/edit"
    Then the response status code should be 200
    When I fill in "division[name]" with "New division"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/directory/divisions/1/show"
    And I should see "New division"
