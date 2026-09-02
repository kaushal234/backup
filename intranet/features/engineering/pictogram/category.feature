Feature: Pictogram categories
  Scenario: As an anonymous user, test that i'm not allowed to see pictogram categories pages
    When I go to "/engineering/pictograms/categories"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/engineering/pictograms/categories/1"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a engineer, test that i'm allowed to see pictogram categories pages
    Given I authenticate as "user-eng@tld.fr" with "P@ssw0rd15chars"
    When I go to "/engineering/pictograms/categories"
    Then the response status code should be 200
    And I should see "Pictogram categories"
    And I should see "A/C loading & unloading"
    And I should see an "a[href$='/engineering/pictograms/categories/add']" element
    And I should see an "a[href$='/engineering/pictograms/categories/1/edit']" element
    And the response should contain "/engineering/pictograms/categories/1/delete?_token="

  Scenario: As a engineer, I can filter pictograms categories
    Given I authenticate as "user-eng@tld.fr" with "P@ssw0rd15chars"
    When I go to "/engineering/pictograms/categories"
    And I fill in the following:
      | filter_category[__search][value] | axles |
    And press "Search"
    Then I should see 1 "table.table tbody tr" elements
    And I should see "Axles & Wheels"

  Scenario: As a engineer, I can add a pictogram category
    Given I authenticate as "user-eng@tld.fr" with "P@ssw0rd15chars"
    When I go to "/engineering/pictograms/categories/add"
    And I fill in "category[name]" with "test new picto cat"
    And I fill in "category[color]" with "#FF0000"
    And I press "submit"
    And I should see "Pictogram category created with success"
    And I should see "test new picto cat"

  Scenario: As a engineer, I can edit a pictogram category
    Given I authenticate as "user-eng@tld.fr" with "P@ssw0rd15chars"
    When I go to "/engineering/pictograms/categories/16/edit"
    And I fill in "category[name]" with "picto cat updated"
    And I press "submit"
    Then the response status code should be 200
    And I should see "Pictogram category updated with success"
    And I should see "picto cat updated"

  Scenario: As a engineer, I can delete a pictogram category
    Given I authenticate as "user-eng@tld.fr" with "P@ssw0rd15chars"
    When I go to "/engineering/pictograms/categories/17/delete"
    Then the response status code should be 200
    And I should see "Pictogram category deleted with success"
    And I should not see "Cat to be deleted"

  Scenario: As a basic user, test that i'm not allowed to create, edit or delete pictogram category
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/engineering/pictograms/categories/add"
    Then the response status code should be 200
    Then I should not be on "/engineering/pictograms/categories/add"
    And I should see "You do not have permissions"
    Then I go to "/engineering/pictograms/categories/1/edit"
    Then the response status code should be 200
    Then I should not be on "/engineering/pictograms/categories/1/edit"
    And I should see "You do not have permissions"
    Then I go to "/engineering/pictograms/categories/1/delete"
    Then the response status code should be 200
    Then I should not be on "/engineering/pictograms/categories/1/delete"
    And I should see "You do not have permissions"
