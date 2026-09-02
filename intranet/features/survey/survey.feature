Feature: Surveys

  Scenario: As an anonymous user, test that i'm not allowed to see surveys homepage
    When I go to "/surveys"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As an anonymous user, test that i'm not allowed to see survey edit
    When I go to "/surveys/1/edit"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As an anonymous user, test that i'm not allowed to see survey show
    When I go to "/surveys/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As an anonymous user, test that i'm not allowed to see survey delete
    When I go to "/surveys/1/delete"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As an anonymous user, test that i'm not allowed to see survey add
    When I go to "/surveys/add"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that I'm not allow on survey edit page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/surveys/1/edit"
    Then I should not be on "/surveys/1/edit"
    And I should see "You do not have permissions"

  Scenario: As a basic user, test that I'm not allow on survey delete page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/surveys/1/delete"
    Then I should not be on "/surveys/1/delete"
    And I should see "You do not have permissions"

  Scenario: As a basic user, test that I'm not allow on survey add page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/surveys/add"
    Then I should not be on "/surveys/add"
    And I should see "You do not have permissions"

  Scenario: As a super user, test that I'm allowed to see survey items index page
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/surveys"
    Then the response status code should be 200
    And I should see "SURVEY"
    And I should see "Name"
    And I should see "Expiration date"
    And I should see "Created at"
    And I should see "Created by"
    And I should see "View"
    And I should see "Edit"
    And I should see "Delete"

  Scenario: I should be able to add a new survey item
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/surveys/add"
    Then the response status code should be 200
    And The module should be "SRV"
    And I should see "Add a survey"
    And I should see "Name"
    And I should see "Description"
    And I should see "Expiration Date"
    When I fill in the following:
      | survey[name] | This is a new survey |
    And I press "submit"
    When the response status code should be 200
    And I should be on "/surveys"
    And I should see "This is a new survey"

  Scenario: I should be able to edit a survey item
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/surveys/1/edit"
    Then the response status code should be 200
    And I should see "Group"
    And I should see "Survey"
    And I should see "Description"
    When I fill in the following:
      | survey[name] | Is this behat testing useful? |
    And I press "submit"
    Then the response status code should be 200
    And I should be on "/surveys"
    And I should see "Is this behat testing useful?"

  Scenario: I can delete a survey
    Given I am authenticated as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/surveys/2/delete"
    Then the response status code should be 200
    And I should be on "/surveys"
