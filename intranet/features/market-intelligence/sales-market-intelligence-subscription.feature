Feature: Sales / Market Intelligence

  Scenario: As an anonymous user, test that i'm not allowed to see market intelligence subscriptions pages
    When I go to "/sales/market-intelligence-subscriptions"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see market intelligence subscriptions pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/market-intelligence-subscriptions"
    Then the response status code should be 200
    And The module should be "MIM"
    And I should see an "a[href$='/sales/market-intelligence-subscriptions/add']" element
    And I should see "My MIM Subscriptions"

  Scenario: A superuser user can delete market intelligence subscriptions
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/market-intelligence-subscriptions/1/delete"
    Then the response status code should be 200
    And I should be on "/sales/market-intelligence-subscriptions"
    And I should see "This subscription has been correctly removed."
