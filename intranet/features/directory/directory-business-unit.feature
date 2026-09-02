Feature: Directory / Business Unit

  Scenario: As an anonymous user, test that i'm not allowed to see business unit pages
    When I go to "/directory/business-units"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/directory/business-units/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see business unit pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/business-units"
    Then the response status code should be 200
    And The module should be "DIR"
    And I should see "Business Unit Listing"
    And I should not see an "a[href$='/directory/business-units/add']" element
    And I should see 24 "table.footable tbody tr" elements
    And the "table.footable tbody  tr:nth-child(2) td:nth-child(1)" element should contain a text
    When I go to "/directory/business-units/1/show"
    Then the response status code should be 200
    And I should see "Business Unit Details"
    And I should not see an "a[href$='/directory/business-units/1/edit']" element
    And I should not see an "a[href$='/directory/business-units/1/delete']" element
    And I should see 6 ".association-table tr" elements
    And the ".association-table tr:nth-child(1) td:nth-child(2)" element should contain "1"
    And the ".association-table tr:nth-child(2) td:nth-child(2)" element should contain a text
    And the ".association-table tr:nth-child(3) td:nth-child(2)" element should contain a text
    And I should not see an ".activity" element

  Scenario: As a superuser, test that i'm allowed to see business unit pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/business-units"
    Then the response status code should be 200
    And I should see an "a[href$='/directory/business-units/add']" element
    When I go to "/directory/business-units/1/show"
    Then the response status code should be 200
    Then I should see "BUSINESS UNIT#"
    And I should see an "a[href$='/directory/business-units/1/edit']" element
    And I should see an "a[href$='/directory/business-units/1/delete']" element

  Scenario: As a superuser, test that i can add a business unit
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/business-units/add"
    Then the response status code should be 200
    When I fill in the following:
      | business_unit[name]   | MyTestBusinessUnit |
      | business_unit[domain] | @mydomain.com      |
      | business_unit[location] | /locations/13      |
      | business_unit[region] | /regions/2      |
    And press "submit"
    Then the response status code should be 200
    And I should see "MyTestBusinessUnit"

  Scenario: As a superuser, test that i can edit a business unit
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/business-units/3/edit"
    Then the response status code should be 200
    When I fill in the following:
      | business_unit[name]   | MyRemplacementTestBusinessUnit |
      | business_unit[domain] | @myotherdomain.com             |
      | business_unit[location] | /locations/14      |
      | business_unit[region] | /regions/3      |
    And press "submit"
    Then the response status code should be 200
    And I should be on "/directory/business-units/3/show"
    And I should see "MyRemplacementTestBusinessUnit"
