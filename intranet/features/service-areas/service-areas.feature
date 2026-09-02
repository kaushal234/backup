Feature: Service / Service Area

  Scenario: As an anonymous user, test that i'm not allowed to see service areas pages
    When I go to "/service-areas"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see service areas home
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service-areas"
    Then the response status code should be 200
    And I should see "Service Areas"

  Scenario: As a basic user, test that i'm not allowed to access the add page directly
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service-areas/add"
    Then I should be on "/account"
    And I should see "You do not have permissions"

  @javascript
  Scenario: As a MOO_TOC user, test that i'm allowed to submit the add form
    Given I authenticate as "user-moo-cat@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service-areas/add"
    And I wait until I see "Countries"
    Then I fill in "service_area[name]" with "Asia Pacific"
    Then I select "/people/32" from "service_area[representative]"
    Then I select "/countries/1" from "form[countries][]"
    And I wait until I see "CDG - Paris"
    Then I check "airport_62"
    And I press "submit"
    Then I wait until I see "Service Area successfully created"
    Then I wait until I see "Asia Pacific"
    And I should be on "/service-areas"

  Scenario: As a basic user, test that i'm not allowed to access the edit page directly
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service-areas/1/edit"
    Then I should be on "/account"
    And I should see "You do not have permissions"

  @javascript
  Scenario: As a MOO_TOC user, test that i'm allowed to submit the edit form
    Given I authenticate as "user-moo-cat@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service-areas/1/edit"
    And I wait until I see "Countries"
    Then I fill in "service_area[name]" with "Europe West Updated"
    And I press "submit"
    Then I wait until I see "Service Area successfully updated"
    Then I wait until I see "Europe West Updated"
    And I should be on "/service-areas"

  Scenario: As a basic user, test that i'm not allowed to delete a service area
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service-areas/2/delete"
    Then I should be on "/account"
    And I should see "You do not have permissions"

  @javascript
  Scenario: As a MOO_TOC user, test that i'm allowed to delete a service area with confirmation
    Given I authenticate as "user-moo-cat@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service-areas"
    Then I wait until I see "Europe West Updated"
    When I click on the 1st ".btn-danger[data-bs-toggle='modal']" element
    And I wait until I see "confirmation"
    And I click on the 1st ".modal.show a.btn-danger" element
    Then I wait until I see "successfully deleted"
    And I should be on "/service-areas"