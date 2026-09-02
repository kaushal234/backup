Feature: Directory / Department

  Scenario: As an anonymous user, test that i'm not allowed to see departments pages
    When I go to "/directory/departments"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/directory/departments/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see departments pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/departments"
    Then the response status code should be 200
    And The module should be "DIR"
    And I should see "Department Listing"
    And I should not see an "a[href$='/directory/departments/add']" element
    And I should see 6 "table.footable tr" elements
    And the "table.footable tbody tr:nth-child(2) td:nth-child(1)" element should contain a text
    And the "table.footable tbody  tr:nth-child(2) td:nth-child(2)" element should contain a yes or no
    And the "table.footable tbody  tr:nth-child(2) td:nth-child(3)" element should contain a yes or no
    When I go to "/directory/departments/1/show"
    Then the response status code should be 200
    And I should see "Department Details"
    And I should not see an "a[href$='/directory/departments/1/edit']" element
    And I should not see an "a[href$='/directory/departments/1/delete']" element
    And I should see 4 ".association-table tr" elements
    And the ".association-table tr:nth-child(1) td:nth-child(2)" element should contain "1"
    And the ".association-table tr:nth-child(2) td:nth-child(2)" element should contain a text
    And the ".association-table tr:nth-child(3) td:nth-child(2)" element should contain a yes or no
    And the ".association-table tr:nth-child(4) td:nth-child(2)" element should contain a yes or no

  Scenario: As a superuser, test that i'm allowed to see departments pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/departments"
    And I should see 6 "table.footable tr" elements
    And I should see an "a[href$='/directory/departments/add']" element
    When I go to "/directory/departments/1/show"
    Then I should see "DEPARTMENT#"
    And I should see an "a[href$='/directory/departments/1/edit']" element
    And I should see an "a[href$='/directory/departments/1/delete']" element

  Scenario: As a superuser, test that i can add a department
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/departments/add"
    Then the response status code should be 200
    When I fill in the following:
      | department[name] | MyTestDptOfTest |
    And I check "department[sso]"
    And press "submit"
    Then the response status code should be 200
    And I should see "MyTestDptOfTest"

  Scenario: As a superuser, test that i can edit a department
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/departments/1/edit"
    Then the response status code should be 200
    When I fill in the following:
      | department[name] | DptNameEdited |
    And press "submit"
    Then the response status code should be 200
    And I should be on "/directory/departments/1/show"
    And I should see "DptNameEdited"

  Scenario: As a superuser, test that i can delete a department
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/departments/1/delete"
    Then the response status code should be 200
    And I should see "Confirmation"
    When I check "department[confirm]"
    And press "delete"
    Then the response status code should be 200
    And I should be on "/directory/departments"
    And I should not see "DptNameEdited"
