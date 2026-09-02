Feature: Directory / Position Category Type

  Scenario: As an anonymous user, test that I'm not allowed to see position category type pages
    When I go to "/directory/position-category-types"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that I'm allowed to see position category type pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/position-category-types"
    Then the response status code should be 200
    And The module should be "ESM"
    And I should not see an "a[href$='/directory/position-category-types/1/edit']" element
    And I should not see an "a[href^='/directory/position-category-types/1/delete']" element
    And I should not see "New Category"
    And I should not see "New Category Type"

  Scenario: As superuser, test that I'm allowed to see position category type pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/position-category-types"
    Then the response status code should be 200
    And I should see an "a[href$='/directory/position-category-types/1/edit']" element
    And I should see an "a[href^='/directory/position-category-types/1/delete']" element
    And I should see "New Category"
    And I should see "New Category Type"

  Scenario: As a superuser, test that I can add a position category type
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/position-category-types/add"
    Then the response status code should be 200
    And The module should be "ESM"
    And I fill in "position_category_type[name]" with "CategautryType"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/directory/position-category-types"
    And I should see "Categautry"
    And I should see "The position category type have been successfully saved"

  Scenario: As a superuser, test that I can edit a position category type
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/position-category-types/2/edit"
    Then the response status code should be 200
    And The module should be "ESM"
    And I fill in "position_category_type[name]" with "Cat et Gautry Type"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/directory/position-category-types"
    And I should see "Cat et Gautry Type"
    And I should see "The position category type have been successfully saved"

  Scenario: As a superuser, test that I can delete a position category type
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/position-category-types/2/delete"
    Then the response status code should be 200
    And I should be on "/directory/position-category-types"
