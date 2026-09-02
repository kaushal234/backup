Feature: Surveys / Items

  Scenario: As an anonymous user, test that i'm not allowed to see survey items edit
    When I go to "surveys/1/items/1/edit"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As an anonymous user, test that i'm not allowed to see survey items delete
    When I go to "surveys/1/items/1/delete"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As an anonymous user, test that i'm not allowed to see survey items add
    When I go to "surveys/1/items/add"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that I'm not allow on items edit
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/surveys/1/items/1/edit"
    Then I should not be on "/surveys/1/items/1/edit"
    And I should see "You do not have permissions"

  Scenario: As a basic user, test that I'm not allow on items delete
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/surveys/1/items/1/delete"
    Then I should not be on "/surveys/1/items/1/delete"
    And I should see "You do not have permissions"

  Scenario: As a basic user, test that I'm not allow on items add
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/surveys/1/items/add"
    Then I should not be on "/surveys/1/items/add"
    And I should see "You do not have permissions"

  Scenario: As a super user, test that I'm allowed to see survey items index page
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/surveys/1/items"
    Then the response status code should be 200
    And The module should be "SRV"
    And I should see "ITEMS"
    And I should see "Description"
    And I should see "Total answers"
    And I should see "Total comments"
    And I should see "Survey"
    And I should see "Group name"
    And I should see "Created at"
    And I should see "Created by"
    And I should see "Edit"
    And I should see "Delete"
    And I should see "Filter"
    And I should see "Description"
    And I should see "Group"
    And I should see "Total Answers"
    And I should see "Created in the last month"
    And I should see "Created in the past 12 months"

  Scenario: I should be able to add a new survey item
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/surveys/1/items/add"
    Then the response status code should be 200
    And I should see "Add a new survey item."
    And I should see "Group"
    And I should see "Survey"
    And I should see "Description"
    When I fill in the following:
      | survey_item[description] | Are you sure you work here? |
    And I press "submit"
    When the response status code should be 200
    And I should be on "/surveys/1/items"
    And I should see "Are you sure you work here?"

  Scenario: I should be able to edit a survey item
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/surveys/1/items/1/edit"
    Then the response status code should be 200
    And I should see "Group"
    And I should see "Survey"
    And I should see "Description"
    When I fill in the following:
      | survey_item[description] | Is this behat testing useful? |
    And I press "submit"
    Then the response status code should be 200
    And I should be on "/surveys/1/items"
    And I should see "Is this behat testing useful?"

  Scenario: I can delete rating types
    Given I am authenticated as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/surveys/1/items/1/delete"
    Then the response status code should be 200
    And I should be on "/surveys/1/items"
    And I should not see "Où vous remettez en question 0 ?"
