Feature: Directory / Region

  Scenario: As an anonymous user, test that i'm not allowed to see region pages
    When I go to "/directory/regions"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/directory/regions/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see region pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/regions"
    Then the response status code should be 200
    And The module should be "DIR"
    And I should see "Region Listing"
    And I should not see an "a[href$='/directory/regions/add']" element
    And I should see 11 "table.footable tr" elements
    And the "table.footable tbody  tr:nth-child(2) td:nth-child(1)" element should contain a text
    And the "table.footable tbody  tr:nth-child(2) td:nth-child(2)" element should contain a text
    When I go to "/directory/regions/1/show"
    Then the response status code should be 200
    And I should see "Region Details"
    Then I should see "region#"
    And I should not see an "a[href$='/regions/1/edit']" element
    And I should not see an "a[href$='/regions/1/delete']" element

  Scenario: As a superuser, test that i'm allowed to see region pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/regions"
    And the response status code should be 200
    And I should see 11 "table.footable tr" elements
    And I should see an "a[href$='/directory/regions/add']" element
    When I go to "/directory/regions/1/show"
    Then the response status code should be 200
    And I should see an "a[href$='/regions/1/edit']" element
    And I should see an "a[href$='/regions/1/delete']" element

  Scenario: As a superuser, test that i can add a region
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/regions/add"
    Then the response status code should be 200
    When I fill in "region[name]" with "MyTestRegion"
    And I select "/people/1" from "region[representative]"
    And I select "/sub_divisions/1" from "region[subDivision]"
    And press "submit"
    Then the response status code should be 200
    And I should see "MyTestRegion"

  Scenario: As a superuser, test that i can edit a region
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/regions/1/edit"
    Then the response status code should be 200
    When I fill in "region[name]" with "MyRemplacementTestRegion"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/directory/regions/1/show"
    And I should see "MyRemplacementTestRegion"
