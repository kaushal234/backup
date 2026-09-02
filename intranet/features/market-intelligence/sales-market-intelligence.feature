Feature: Sales / Market Intelligence

  Scenario: As an anonymous user, test that i'm not allowed to see market intelligence pages
    When I go to "/sales/market-intelligences"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/sales/market-intelligences/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see market intelligence pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/market-intelligences"
    Then the response status code should be 200
    And The module should be "MIM"
    And I should see an "a[href$='/sales/market-intelligences/add']" element
    And I should see "Latest MIM"
    And I should see an "#mim_by_bu_container" element
    When I go to "/sales/market-intelligences/1/show"
    Then the response status code should be 200
    And I should see an "a[href$='/sales/customers/1/show']" element
    And I should see an "a[href$='/sales/customers/32/show']" element
    And I should see an "a[href$='/sales/customers/33/show']" element
    And I should see an "a[href$='/sales/competitors/1/show']" element
    And I should see "Link MIM"

  Scenario: A superuser user can delete market intelligence
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/market-intelligences/1/show"
    Then the response status code should be 200
    And I should see an "a[href$='/sales/market-intelligences/1/delete']" element
    When I go to "/sales/market-intelligences/1/delete"
    Then the response status code should be 200
    And I should be on "/sales/market-intelligences"
    And I should see "MIM removed successfully."
