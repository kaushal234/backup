Feature: Surveys / rating types

  Scenario: As an anonymous user, test that i'm not allowed to see rating types pages
    When I go to "surveys/1/rating-types"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As an anonymous user, test that i'm not allowed to see rating types delete
    When I go to "surveys/1/rating-types/1/delete"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As an anonymous user, test that i'm not allowed to see rating types edit
    When I go to "surveys/1/rating-types/1/edit"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As an anonymous user, test that i'm not allowed to see rating types add
    When I go to "surveys/1/rating-types/add"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that I'm not allowed on rating types add
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/surveys/1/rating-types/add"
    Then I should not be on "/surveys/1/rating-types/add"
    And I should see "You do not have permissions"

  Scenario: As a basic user, test that I'm not allowed on rating types edit
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/surveys/1/rating-types/1/edit"
    Then I should not be on "/surveys/1/rating-types/1/edit"
    And I should see "You do not have permissions"

  Scenario: As a basic user, test that I'm not allowed on rating types delete
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/surveys/1/rating-types/1/delete"
    Then I should not be on "/surveys/1/rating-types/1/delete"
    And I should see "You do not have permissions"

  Scenario: As a super user, test that I'm allowed to see rating types pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/surveys/1/rating-types"
    Then the response status code should be 200
    And The module should be "SRV"
    And I should see "RATING TYPES"
    And I should see "Description"
    And I should see "From"
    And I should see "From label"
    And I should see "To"
    And I should see "To label"
    And I should see "Survey"
    And I should see "View"
    And I should see "Edit"
    And I should see "Delete"

  Scenario: I should be able to add a new rating type
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/surveys/1/rating-types/add"
    Then the response status code should be 200
    And I should see "Add a new rating type"
    And I should see "Description"
    And I should see "From"
    And I should see "From label"
    And I should see "To"
    And I should see "To label"
    And I should see "Submit"
    And I should see "Cancel"
    When I fill in the following:
      | rating_type[min] | 0 |
      | rating_type[max] | 5 |
      | rating_type[minLabel] | "min label" |
      | rating_type[maxLabel] | "max label" |
      | rating_type[description] | "behat scenario" |
    And I press "submit"
    When the response status code should be 200
    And I should be on "/surveys/1/rating-types"
    And I should see "behat scenario"
    And I should see "min label"
    And I should see "max label"

  Scenario: I should be able to go to surveys/<id>/rating-types/<id>/show
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/surveys/1/rating-types/1/show"
    Then the response status code should be 200
    And I should see "Faible"
    And I should see "Haute"
    And I should see "Description"
    And I should see "From"
    And I should see "From label"
    And I should see "To"
    And I should see "To label"
    And I should see "Expiration date"
    And I should see "Created at"
    And I should see "Created by"

  Scenario: I should be able to edit a rating type
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/surveys/1/rating-types/1/edit"
    Then the response status code should be 200
    And I should see "Description"
    And I should see "From"
    And I should see "From label"
    And I should see "To"
    And I should see "To label"
    And I should see "Submit"
    And I should see "Cancel"
    And I press "submit"
    Then the response status code should be 200
    And I should be on "/surveys/1/rating-types"
    And I should see "0"
    And I should see "5"
    And I should see "Faible"
    And I should see "Haute"

  Scenario: I can delete rating types
    Given I am authenticated as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/surveys/1/rating-types/1/delete"
    Then the response status code should be 200
    And I should be on "/surveys/1/rating-types"
    And I should not see "Faible"
    And I should not see "Haute"
