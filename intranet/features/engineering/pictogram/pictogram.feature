Feature: Pictograms
  Scenario: As an anonymous user, test that i'm not allowed to see pictograms pages
    When I go to "/engineering/pictograms"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/engineering/pictograms/1"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a engineer, test that i'm allowed to see pictograms pages
    Given I authenticate as "user-eng@tld.fr" with "P@ssw0rd15chars"
    When I go to "/engineering/pictograms"
    Then the response status code should be 200
    And I should see "Pictograms"
    And I should see "Indicates main beam light ON"
    And I should see an "a[href$='/engineering/pictograms/add']" element
    And I should see an "a[href$='/engineering/pictograms/1']" element
    And the response should contain "/engineering/pictograms/1/delete?_token="

  Scenario: As a engineer, I can filter pictograms
    Given I authenticate as "user-eng@tld.fr" with "P@ssw0rd15chars"
    When I go to "/engineering/pictograms"
    And I fill in the following:
      | filter_pictogram[__search][value] | parking |
    And press "Search"
    Then I should see 1 "div.frame-loading-show div.row div.col" elements

  Scenario: As a engineer, I can access page to add a picto
    Given I authenticate as "user-eng@tld.fr" with "P@ssw0rd15chars"
    When I go to "/engineering/pictograms/add"
    Then the response status code should be 200
    And I should see "Add pictogram"
    And I should see "Name"
    And I should see "Category"
    And I should see "Main Picture"

  Scenario: As a engineer, I can see a pictogram page
    Given I authenticate as "user-eng@tld.fr" with "P@ssw0rd15chars"
    When I go to "/engineering/pictograms/1"
    Then the response status code should be 200
    And I should see "Indicates main beam light ON"
    And I should see "Lights"
    And I should see "Files"
    And I should see "Logs"
    And I should see an "a[href$='/engineering/pictograms/1/edit']" element

  Scenario: As a engineer, I can edit a picto
    Given I authenticate as "user-eng@tld.fr" with "P@ssw0rd15chars"
    When I go to "/engineering/pictograms/4/edit"
    And I fill in "pictogram[description]" with "picto updated ser"
    And I press "submit"
    Then the response status code should be 200
    And I should see "Pictogram updated with success"
    And I should see "picto updated ser"

  Scenario: As a engineer, I can delete a picto
    Given I authenticate as "user-eng@tld.fr" with "P@ssw0rd15chars"
    When I go to "/engineering/pictograms/5/delete"
    Then the response status code should be 200
    And I should see "Pictogram deleted with success"
    And I should not see "Picto to delete"

  Scenario: As a basic user, test that i'm not allowed to create, edit or delete pictogram
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/engineering/pictograms/add"
    Then the response status code should be 200
    Then I should not be on "/engineering/pictograms/add"
    And I should see "You do not have permissions"
    Then I go to "/engineering/pictograms/1/edit"
    Then the response status code should be 200
    Then I should not be on "/engineering/pictograms/1/edit"
    And I should see "You do not have permissions"
    Then I go to "/engineering/pictograms/1/delete"
    Then the response status code should be 200
    Then I should not be on "/engineering/pictograms/1/delete"
    And I should see "You do not have permissions"

  Scenario: As a basic user, I can access show page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/engineering/pictograms/1"
    Then the response status code should be 200
    And I should see "Indicates main beam light ON"
    And I should see "Lights"
    And I should see "Files"
    And I should see "Logs"
    And I should not see an "a[href$='/engineering/pictograms/1/edit']" element
    And I should not see an "#react" element