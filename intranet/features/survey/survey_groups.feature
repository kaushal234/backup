Feature: Surveys / Groups

  Scenario: As an anonymous user, test that i'm not allowed to see groups add
    When I go to "/surveys/1/groups/add"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As an anonymous user, test that i'm not allowed to see groups show
    When I go to "/surveys/1/groups/3/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As an anonymous user, test that i'm not allowed to see groups edit
    When I go to "/surveys/1/groups/3/edit"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As an anonymous user, test that i'm not allowed to see groups delete
    When I go to "/surveys/1/groups/3/delete"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that I'm not allow on groups edit
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/surveys/1/groups/3/edit"
    Then I should not be on "/surveys/1/groups/3/edit"
    And I should see "You do not have permissions"

  Scenario: As a basic user, test that I'm not allow on groups delete
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/surveys/1/groups/3/delete"
    Then I should not be on "/surveys/1/groups/3/delete"
    And I should see "You do not have permissions"

  Scenario: As a basic user, test that I'm not allow on groups add
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/surveys/1/groups/add"
    Then I should not be on "/surveys/1/groups/add"
    And I should see "You do not have permissions"

  Scenario: As a super user, test that I'm allowed to see groups index page
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/surveys/1/groups"
    Then the response status code should be 200
    And The module should be "SRV"
    And I should see "GROUPS"
    And I should see "Group name"
    And I should see "Survey"
    And I should see "Created at"
    And I should see "Created by"
    And I should see "View"
    And I should see "Edit"
    And I should see "Delete"
    And I should see "Filter"
    And I should see "Group 0"
    And I should see "Number of distinct surveys."
    And I should see "Created by different users."
    And I should see "Created in the last month"
    And I should see "Created in the past 12 months"

  Scenario: I should be able to go to surveys/1/groups/1/show
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/surveys/1/groups/1/show"
    Then the response status code should be 200
    And I should see "Group 0"
    And I should see "Expiration date"
    And I should see "Created at"
    And I should see "Created by"
    And I should see "Edit"
    And I should see "Delete"

  Scenario: I should be able to add a new survey group
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    And I go to "/surveys/1/groups/add"
    And the response status code should be 200
    And I should see "Add a new group"
    And I should see "Group Name"
    And I should see "Survey"
    And I should see "Description"
    Then I fill in the following:
      | survey_group[name] | Group name |
      | survey_group[description] | Group description |
    And I press "submit"
    Then the response status code should be 200
    And I should be on "/surveys/1/groups"
    And I should see "Group name"

  Scenario: I should be able to edit a survey group
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/surveys/1/groups/1/edit"
    Then the response status code should be 200
    And I should see "Edit a group"
    And I should see "Group Name"
    And I should see "Survey"
    And I should see "Description"
    And I should see "Group 0"
    When I fill in the following:
      | survey_group[name] | Group Name in en |
    And I press "submit"
    Then the response status code should be 200
    And I should be on "/surveys/1/groups"
    And I should see "Group Name in en"

  Scenario: I can delete a group
    Given I am authenticated as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/surveys/1/groups/1/delete"
    Then the response status code should be 200
    And I should be on "/surveys/1/groups"
    And I should not see "Group Name in en"
