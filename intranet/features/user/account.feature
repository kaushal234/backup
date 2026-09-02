Feature: My account

  Scenario: Test that I can set a user password
    Given I authenticate as "kevin@tld.fr" with "P@ssw0rd15chars"
    When I go to "/account/change-password"
    Then the response status code should be 200
    And I should see "Change password"
    When I fill in the following:
      | app_account_password[password][first]  | $3cr3T L0nger Pass |
      | app_account_password[password][second] | $3cr3T L0nger Pass |
    And press "Submit"
    Then the response status code should be 200
    And I should be on "/account"
    And I should see "Password changed successfully"

  Scenario: Test that a basic user cannot change his firstname and lastname
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    Then I go to "/"
    Then the response status code should be 200
    When I go to "/account/edit"
    Then I should see an "input[id='app_account_lastname'][disabled='disabled']" element
    And I should see an "input[id='app_account_firstname'][disabled='disabled']" element
    And I should see "Request to add new premise"
    And The module should be "USER"

  Scenario: Test that a super user can change his firstname and lastname
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    Then I go to "/"
    Then the response status code should be 200
    When I go to "/account/edit"
    Then I should see an "input[id='app_account_lastname']" element
    And I should see an "input[id='app_account_firstname']" element
    And I should see "Request to add new premise"

  Scenario: Test that basic user see his new intranet identifier
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/account"
    Then the response status code should be 200
    Then I should see "New Intranet Identifier"
