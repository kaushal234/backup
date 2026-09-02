Feature: mis project

  Scenario: As an anonymous user, test that i'm not allowed to see mis project pages
    When I go to "/mis/projects"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As basic user, I should be able to access MIS Project page but I should not see confidential ones
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/projects"
    Then the response status code should be 200
    And I should not see an "a[href$='/mis/projects/1/show']" element
    And I should see an "a[href$='/mis/projects/2/show']" element
    When I go to "/mis/projects/1/show"
    Then the response status code should be 404
    When I go to "/mis/projects/2/show"
    Then the response status code should be 200
    And I should see "second project"

  Scenario: As basic user, I should not be able to access add form
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/projects/add"
    Then the response status code should be 200
    And I should see "You do not have permissions"

  Scenario: As CIO, I should be able to access add form
    Given I authenticate as "user-cio@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/projects"
    Then the response status code should be 200
    When I go to "/mis/projects/add"
    Then the response status code should be 200
    And I should see "Add project"

  Scenario: As basic user, I should not be able to do anything on project except a comment
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/projects/2/show"
    Then the response status code should be 200
    And I should not see an "a[href$='/mis/projects/2/edit']" element
    And I should not see an "a[href$='/mis/projects/2/update-status']" element
    And I should see an "a[href$='/mis/projects/2/comment']" element
    When I go to "/mis/projects/2/edit"
    Then the response status code should be 200
    And I should see "You do not have permissions"
    When I go to "/mis/projects/2/update-status"
    Then the response status code should be 403
    When I go to "/mis/projects/2/comment"
    Then the response status code should be 200
    And I should see "Comment"

  Scenario: As CIO, I should be able to access everything on project
    Given I authenticate as "user-cio@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/projects/1/show"
    Then the response status code should be 200
    And I should see "First project"
    And I should see "Edit"
    And I should see "Update status"
    When I go to "/mis/projects/1/edit"
    Then the response status code should be 200
    And I should see "Edit Project"
    And I should see "Revised Due Date"
    And I should see "Revised Estimated Hours"
    When I go to "/mis/projects/1/update-status"
    Then the response status code should be 200
    And I should see "Update Status"
    And I should see "Upload file"
    And I should see "First project"
    When I go to "/mis/projects/1/comment"
    Then the response status code should be 200
    And I should see "Comment"
    And I should see "Upload file"

  Scenario: As basic user I should be able to add a comment on project
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/projects/2/comment"
    Then the response status code should be 200
    When I fill in the following:
      | project_update_status[comment] | TEST COMMENT |
    And press "Submit"
    Then I should be on "/mis/projects/2/show"
    And I should see "TEST COMMENT"

  Scenario: As CIO I should be able to update status of a project
    Given I authenticate as "user-cio@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/projects/1/update-status"
    Then the response status code should be 200
    When I fill in the following:
      | project_update_status[comment]| TEST CHANGING STATUS |
    And I select "PHASE 0" from "project_update_status[status]"
    And press "Submit"
    Then I should be on "/mis/projects/1/show"
    And I should see "TEST CHANGING STATUS"
    And I should see "PHASE 0"

  Scenario: As mis user, I should be able to create project
    Given I authenticate as "user-mis@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/projects/add"
    And I fill in "project[name]" with "Test project"
    And I fill in "project[teamsLink]" with "https://tld-gse.com/william/nudes"
    Then I select "/mis/project_tags/7" from "project[tags][]"
    Then I select "/regions/7" from "project[region]"
    Then I select "IF 10" from "project[indicesFactor]"
    Then I fill in "project[description]" with "Test description"
    And I select "/modules/34" from "project[module]"
    And I select "/people/99" from "project[misOwner]"
    And I select "/people/94" from "project[projectManager]"
    And I fill in "project[startedAt]" with "03/02/2026"
    And I fill in "project[phases][0][estimatedClosureAt]" with "03/03/2026"
    And I fill in "project[phases][0][estimatedHours]" with "5"
    And I fill in "project[phases][1][estimatedClosureAt]" with "04/03/2026"
    And I fill in "project[phases][1][estimatedHours]" with "10"
    And I fill in "project[phases][2][estimatedClosureAt]" with "05/03/2026"
    And I fill in "project[phases][2][estimatedHours]" with "15"
    And I fill in "project[phases][3][estimatedClosureAt]" with "06/03/2026"
    And I fill in "project[phases][3][estimatedHours]" with "20"
    And I fill in "project[phases][4][estimatedClosureAt]" with "07/03/2026"
    And I fill in "project[phases][4][estimatedHours]" with "25"
    And I select "/people/11" from "project[moduleKeyUsers][]"
    And I select "/people/99" from "project[moduleKeyUsers][]"
    And I select "/people/12" from "project[misMembers][]"
    And I press "submit"
    Then the response status code should be 200
    And I should see "Project has been added"
    And I should see "Test project"
    And I should see "https://tld-gse.com/william/nudes"
    And I should see "PENDING"
    And I should see "Test description"
    And I should see "CSR"
    And I should see "ALVEST"
    And I should see "user MOO-ESR"
    And I should see "user MIS"
    And I should see "2026-03-02"
    And I should see "75"

  Scenario: As cio, I should be able to edit project and phases
    Given I authenticate as "user-cio@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/projects/5/edit"
    And I fill in "project[name]" with "Test project edited"
    And I fill in "project[teamsLink]" with "https://tld-gse.com/william/onlyfans"
    Then I select "/mis/project_tags/7" from "project[tags][]"
    And I fill in "project[phases][0][revisedClosureAt]" with "03/03/2026"
    And I fill in "project[phases][0][revisedEstimatedHours]" with "5"
    And I fill in "project[phases][1][revisedClosureAt]" with "04/03/2026"
    And I fill in "project[phases][1][revisedEstimatedHours]" with "10"
    And I fill in "project[phases][2][revisedClosureAt]" with "05/03/2026"
    And I fill in "project[phases][2][revisedEstimatedHours]" with "15"
    And I fill in "project[phases][3][revisedClosureAt]" with "06/03/2026"
    And I fill in "project[phases][3][revisedEstimatedHours]" with "20"
    And I fill in "project[phases][4][revisedClosureAt]" with "07/03/2026"
    And I fill in "project[phases][4][revisedEstimatedHours]" with "25"
    Then press "submit"
    And I wait until I see "Project has been edited"
    And I wait until I see "Test project edited"
    And I wait until I see "WEBSITE"
    And I wait until I see "https://tld-gse.com/william/onlyfans"