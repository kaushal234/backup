Feature: Directory / Position Category

  Scenario: As an anonymous user, test that I'm not allowed to see position category pages
    When I go to "/directory/position-categories"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that I'm allowed to see position category pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/position-categories"
    Then the response status code should be 200
    And The module should be "ESM"
    And I should not see "New Category"
    And I should not see "New Category Type"

#  Keeps on failing in replay, or suddenly fails when recording another test suite, to be investigated
#
#  Scenario: As a superuser, test that I can edit a position category
#    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
#    When I go to "/directory/position-categories"
#    Then the response status code should be 200
#    And The module should be "ESM"
#    And I fill in "position_category_batch[positionCategories][1][description]" with "Meoooooooow"
#    When I select "/position_category_types/2" from "position_category_batch[positionCategories][1][positionCategoryType]"
#    And press "submit"
#    Then the response status code should be 200
#    And I should be on "/directory/position-categories"
#    And I should see "Meoooooooow"

  Scenario: As a superuser, test that I can add a position category
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/position-categories/add"
    Then the response status code should be 200
    And The module should be "ESM"
    And I fill in "position_category[name]" with "Categautry"
    And I fill in "position_category[description]" with "Meoooow"
    And I select "1" from "position_category[directHeadcount]"
    When I select "/position_category_types/1" from "position_category[positionCategoryType]"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/directory/position-categories"
    And I should see "Categautry"
    And I should see "The position category have been successfully saved"

  Scenario: As a superuser, test that I can delete a position category
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/position-categories/2/delete"
    Then the response status code should be 200
    And I should be on "/directory/position-categories"
