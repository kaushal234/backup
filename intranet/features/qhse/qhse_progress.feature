Feature: QHSE ISO 14001 Progress

  Scenario: As an anonymous user, test that i'm not allowed to see QHSE pages
    When I go to "/quality/qhse-progress"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/quality/qhse-progress/list"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, I can see my location QHSE
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/qhse-progress"
    And The module should be "QHSE"
    Then the response status code should be 200
    And I should see "Quality Health Safety Environment"
    And I should not see "Add a rating to a location"

  Scenario: As a superuser, I can add a rating to a location
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/qhse-progress"
    Then the response status code should be 200
    And I should see "Add a rating to a location"
    And I fill in the following:
      | qhse[location]   | /locations/33 |
      | qhse[date]       |   2016-04-01 |
      | qhse[rating]     |         69   |
    And press "Submit"
    Then the response status code should be 200
    And I should be on "/quality/qhse-progress"

  Scenario: As a superuser, I can edit the rating of a location
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/qhse-progress/list"
    Then the response status code should be 200
    And I should see "Edit"
    When I go to "/quality/qhse-progress/52/edit"
    Then the response status code should be 200
    And I fill in the following:
      | qhse[rating] | 4 |
    And press "Submit"
    Then the response status code should be 200
    And I should be on "/quality/qhse-progress"

  Scenario: As a superuser, I can delete a rating
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/qhse-progress/52/delete"
    Then the response status code should be 200
    And I should be on "quality/qhse-progress/list"
