Feature: Application

  Scenario: As an anonymous user, test that i'm not allowed to see application pages
    When I go to "/mis/applications"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see application pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/applications"
    Then the response status code should be 200
    And I should not see an "a[href$='/mis/applications/add']" element
    And I should not see an "a[href$='/mis/applications/1/edit']" element
    When I go to "/mis/applications/add"
    Then the response status code should be 200
    And I should see "You do not have permissions"
    When I go to "/mis/applications/1/edit"
    Then the response status code should be 200
    And I should see "You do not have permissions"

  Scenario: As a user MISM, test that i'm allowed to see application pages
    Given I authenticate as "user-mism@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/applications"
    Then the response status code should be 200
    And I should see an "a[href$='/mis/applications/add']" element
    And I should see an "a[href$='/mis/applications/1/edit']" element

  Scenario: As user MISM, test that i can add application
    Given I authenticate as "user-mism@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/applications/add"
    And I fill in "application[name]" with "TOTO"
    And I fill in "application[jiraProjectId]" with "123456"
    And press "application[submit]"
    Then the response status code should be 200
    And I should be on "/mis/applications"
    And I should see "TOTO"
    And I should see "123456"

  Scenario: As a user MISM, test that i can edit application
    Given I authenticate as "user-mism@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/applications/1/edit"
    And I fill in "application[name]" with "TESTEDIT"
    And I fill in "application[jiraProjectId]" with "654321"
    And press "application[submit]"
    Then the response status code should be 200
    And I should be on "/mis/applications"
    And I should see "TESTEDIT"
    And I should see "654321"
