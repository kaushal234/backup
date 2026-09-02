Feature: Directory / Position

  Scenario: As an anonymous user, test that i'm not allowed to see positions pages
    When I go to "/directory/positions"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/directory/positions/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see positions pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/positions"
    Then the response status code should be 200
    And The module should be "DIR"
    And I should see "Position Listing"
    And I should not see "/directory/positions/add"
    When I go to "/directory/positions/1/show"
    Then the response status code should be 200
    And I should see an "a[href$='/directory/positions/1/people']" element
    And I should see "Position Details"
    And I should not see "/directory/positions/1/edit"

  Scenario: As a basic user, test that i can see position members
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/positions/1/people"
    Then the response status code should be 200
    And I should see "members of"

  Scenario: As a superuser, test that i'm allowed to see positions pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/positions"
    Then I should see "GCEO"
    And I should see an "a[href$='/directory/positions/1/show']" element
    And I should see an "a[href$='/directory/positions/2/show']" element
    And I should see an "a[href$='/directory/positions/3/show']" element
    And I should see an "a[href$='/directory/positions/add']" element
    When I go to "/directory/positions/1/show"
    Then I should see "position"
    And I should see "GCEO"
    And I should see "code"
    And I should see "description"
    And I should see "level"
    And I should see "Edit"

  Scenario: As a superuser, test that i can add a position
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/positions/add"
    Then the response status code should be 200
    When I fill in "position[code]" with "MyTestCode"
    And I fill in "position[description]" with "A short description"
    And I fill in "position[level]" with "/position_levels/1"
    And press "submit"
    Then the response status code should be 200
    And I should see "MyTestCode"

  Scenario: As a superuser, test that i can edit a position
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/positions/1/edit"
    Then the response status code should be 200
    When I fill in "position[code]" with "code_0_v1"
    And press "submit"
    When I go to "/directory/positions/1/edit"
    Then the response status code should be 200
    When I fill in "position[code]" with "code_0_v2"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/directory/positions/1/show"
    And I should see "code_0_v2"

  Scenario: As a superuser, test that i can delete a position
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/positions/11/delete"
    Then the response status code should be 200
    And I should see "Confirmation"
    When I check "position[confirm]"
    And press "delete"
    Then the response status code should be 200
    And I should be on "/directory/positions"
    And I should not see an "a[href$='/directory/positions/11/show']" element
