Feature: Renew guest user task
  Scenario: HR user should renew this guest user
    Given I authenticate as "user-hr@tld.fr" with "P@ssw0rd15chars"
    When I go to "/tasks/51/show"
    Then the response status code should be 200
    And I should see "Renewed guest user"
    And I should see "Duration (months)"
    And I should see "Yes"
    And I should see "No"
    Then I fill in "Duration (months)" with "10"
    Then I press "Yes"
    Then the response status code should be 200
    And I should be on "/tasks/51/show"
    And I should see "Guest user successfully renewed."

  Scenario: HR user should refuse to renew this guest user
    Given I authenticate as "user-hr@tld.fr" with "P@ssw0rd15chars"
    When I go to "/tasks/52/show"
    Then the response status code should be 200
    And I should see "Not renewed guest user"
    And I should see "Duration (months)"
    And I should see "Yes"
    And I should see "No"
    Then I press "No"
    Then the response status code should be 200
    And I should be on "/tasks/52/show"
    And I should see "Guest user not renewed."