Feature: Directory / Subdivision

  Scenario: As an anonymous user, test that i'm not allowed to see subdivision pages
    When I go to "/directory/subdivisions"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/directory/subdivisions/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see subdivision pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/subdivisions"
    Then the response status code should be 200
    And The module should be "DIR"
    And I should see "Subdivision Listing"
    And I should not see an "a[href$='/directory/subdivisions/add']" element
    And I should not see an "a[href$='/directory/subdivisions/1/edit']" element
    And the ".footable tbody tr:nth-child(1) td:nth-child(1)" element should contain "Subalterne"

  Scenario: As a superuser, test that i'm allowed to see subdivision pages and perform some admin actions
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/subdivisions"
    And the response status code should be 200
    And I should see an "a[href$='/directory/subdivisions/add']" element
    And I should see an "a[href$='/directory/subdivisions/1/edit']" element
    And I should see an "a[href$='/directory/subdivisions/2/edit']" element

  Scenario: As a basic user, test that i'm allowed to see a single subdivision page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/subdivisions/1/show"
    Then the response status code should be 200
    And I should not see an "a[href$='/directory/subdivisions/1/edit']" element

  Scenario: As a superuser, test that i'm allowed to see a single subdivision page and perform some admin actions
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/subdivisions/1/show"
    Then the response status code should be 200
    And I should see an "a[href$='/directory/subdivisions/1/edit']" element

  Scenario: As a basic user, test that i can't add a subdivision
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/subdivisions/add"
    And I should see "You do not have permissions"

  Scenario: As a superuser, test that i can add a subdivision
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/subdivisions/add"
    Then the response status code should be 200
    When I fill in "sub_division[name]" with "Sue BD'Yves Ision"
    And I select "/divisions/1" from "sub_division[division]"
    And I attach the file "picture.png" to "sub_division[logo][file]"
    And press "submit"
    Then I should be on "/directory/subdivisions/5/show"
    And the response status code should be 200
    And I should see "Sue BD'Yves Ision"

  Scenario: As a basic user, test that i can't edit a subdivision
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/subdivisions/1/edit"
    And I should see "You do not have permissions"

  Scenario: As a superuser, test that i can edit a subdivision
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/subdivisions/1/edit"
    Then the response status code should be 200
    When I fill in "sub_division[name]" with "New subdivision"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/directory/subdivisions/1/show"
    And I should see "New subdivision"
