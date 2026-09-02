Feature: Sage Parts Tools

  Scenario: As an anonymous user, test that i'm not allowed to see Sage Parts Tools pages
    When I go to "/tools/operations"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, I can see Sage Parts Tools pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/tools/operations"
    Then the response status code should be 200
    And I should see "Operations"
    And I should see "Title"
    And I should see "Description"
    And I should see "Add Employee/Mechanic"
    And I should see an "a[href='http://portaltools/tools/addmechanic/addmechanic.aspx']" element
