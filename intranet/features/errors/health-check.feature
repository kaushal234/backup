Feature: Health Check

  Scenario: Health Check endpoint is healthy
    Given I go to "/check"
    Then the response status code should be 200
    And I should see "OK"

