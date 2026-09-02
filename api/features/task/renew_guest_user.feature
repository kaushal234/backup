Feature: Test Renew Guest User Task
  Scenario: Test access to renew guest user task
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/tasks/51"
    And the JSON node "status" should be equal to the string "PENDING"
    And the JSON node "renewalDecision" should be equal to the string "PENDING"

  Scenario: Test that the assigner can accept to renew guest user
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/tasks/renew_guest_user/51/accept" with body:
    """
    {
      "renewalDurationMonths": 10
    }
    """
    Then the response status code should be 201
    And the JSON node "renewalDurationMonths" should be equal to "10"
    And the JSON node "status" should be equal to the string "CLOSED"
    And the JSON node "renewalDecision" should be equal to the string "YES"
    Given I authenticate as the intranet user "user-hr@tld.fr"
    When I send a "GET" request to "/guest_users/321"
    Then the response status code should be 200
    And the JSON node "plannedDisableAt" should be equal to the string "2026-11-01T00:00:00-04:00"

  Scenario: Test that the assigner can deny to renew guest user
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/tasks/renew_guest_user/52/deny" with body:
    """
    {}
    """
    Then the response status code should be 201
    And the JSON node "status" should be equal to the string "CLOSED"
    And the JSON node "renewalDecision" should be equal to the string "NO"