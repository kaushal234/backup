Feature: MIS Support Teams

  Scenario: As an anonymous user, test that i'm not allowed to see mis project pages
    When I go to "/mis/projects"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As basic user, I should be able to access Support teams page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/support-teams"
    Then the response status code should be 200
    And I should not see an "a[href$='/mis/support-teams/1/edit']" element
    And I should not see an "a[href$='/mis/support-teams/1/delete']" element
    And I should not see an "a[href$='/mis/support-teams/add']" element

  @javascript
  Scenario: As CIO, I should be able to access add form
    Given I authenticate as "user-cio@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/support-teams"
    And I wait for "a[href$='/mis/support-teams/add']" element
    When I go to "/mis/support-teams/add"
    And I wait until I see "Add"
    Then I should be on "/mis/support-teams/add"

  @javascript
  Scenario: As CIO, I should be able to access edit form
    Given I authenticate as "user-cio@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/support-teams"
    And I wait for "a[href$='/mis/support-teams/1/edit']" element
    When I go to "/mis/support-teams/1/edit"
    And I wait until I see "Edit"
    Then I should be on "/mis/support-teams/1/edit"
