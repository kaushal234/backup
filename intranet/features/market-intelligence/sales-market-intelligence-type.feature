Feature: Sales / Market Intelligence Type

  Scenario: As an anonymous user, test that i'm not allowed to see market intelligence types pages
    When I go to "/sales/market-intelligence-types"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see market intelligence types pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/market-intelligence-subscriptions"
    Then the response status code should be 200
    And The module should be "MIM"
    And I should not see an "a[href$='/sales/market-intelligence-types/add']" element

  Scenario: A superuser user can add market intelligence type
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/market-intelligence-types/add"
    Then the response status code should be 200
    When I fill in "market_intelligence_type[name]" with "Test type"
    And press "Submit"
    And I should be on "/sales/market-intelligence-types"
    And I should see "Test type"
