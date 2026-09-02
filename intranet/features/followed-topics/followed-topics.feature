Feature: Followed topics

  Scenario: As an anonymous user, test that I'm not allowed to see followed topics pages
    When I go to "/followed-topics"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As an basic user, I'm allowed to see followed topics pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/account"
    And I should see an "a[href$='/followed-topics/']" element
    When I go to "/followed-topics"
    And I should see an "a[href$='/mis/trouble-tickets/1/show']" element