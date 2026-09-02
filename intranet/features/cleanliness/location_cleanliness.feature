Feature: Location Cleanliness

  Scenario: As an anonymous user, test that i'm not allowed to see location cleanliness pages
    When I go to "/quality/cleanliness"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/quality/cleanliness/list"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, I can see my location cleanliness
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/cleanliness"
    And The module should be "CL"
    Then the response status code should be 200
    And I should see "Location Cleanliness"
    And I should not see "Add a rating to a location"

  Scenario: As a superuser, I can add a rating to a location
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/cleanliness"
    Then the response status code should be 200
    And I should see "Add a rating to a location"
    And I fill in the following:
      | cleanliness[location]   | /locations/29 |
      | cleanliness[date]       |   2016-04-01 |
      | cleanliness[rating]     |         0.69 |
    And press "Submit"
    Then the response status code should be 200
    And I should be on "/quality/cleanliness"

  Scenario: As a superuser, I can edit the rating of a location
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/cleanliness/list"
    Then the response status code should be 200
    And I should see "Edit"
    When I go to "/quality/cleanliness/60/edit"
    Then the response status code should be 200
    And I fill in the following:
      | cleanliness[rating] | 4.22 |
    And press "Submit"
    Then the response status code should be 200
    And I should be on "/quality/cleanliness"

  Scenario: As a superuser, I can delete a rating
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/cleanliness/48/delete"
    Then the response status code should be 200
    And I should be on "quality/cleanliness/list"
