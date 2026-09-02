Feature: Planning daily delivery

  Scenario: As an allowed user, test that i'm allowed to see planning daily limit
    Given I authenticate as "user-psm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/planning"
    Then the response status code should be 200
    And I should see "Planning daily limit"
    And I should see an "a[href$='/sales/planning/add']" element
    And I should see an "a[href$='/sales/planning/1/delete']" element
    And I should see an "a[href$='/sales/planning/1/edit']" element

  Scenario: As an allowed user, test that i can add a planning daily limit
    Given I authenticate as "user-psm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/planning/add"
    Then the response status code should be 200
    Then I select "/locations/29" from "planning_daily_limit[factory]"
    Then I fill in "planning_daily_limit[days]" with "13"
    Then I fill in "planning_daily_limit[comment]" with "Pas de gros IVECO"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/sales/planning"
    And I should see "Planning daily limit has been successfully saved"
    And I should see "location_factory"
    And I should see "13"
    And I should see "Pas de gros IVECO"
    And I should see an "a[href$='/sales/planning/3/edit']" element

  Scenario: As an allowed user, test that i can edit a planning daily limit
    Given I authenticate as "user-psm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/planning/3/edit"
    Then the response status code should be 200
    Then I fill in "planning_daily_limit[days]" with "69"
    Then I fill in "planning_daily_limit[comment]" with "Pas de gros IVECO, ni de petit"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/sales/planning"
    And I should see "Planning daily limit has been successfully saved"
    And I should see "69"
    And I should see "Pas de gros IVECO, ni de petit"

  Scenario: As an allowed user, test that i can delete a planning daily limit
    Given I authenticate as "user-psm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/planning/3/delete"
    Then the response status code should be 200
    And I should be on "/sales/planning"
    And I should see "Planning daily limit deleted"