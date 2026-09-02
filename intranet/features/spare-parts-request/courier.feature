Feature: Courier

  Scenario: As an anonymous user, test that I'm not allowed to see courier pages
    When I go to "/parts/couriers"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that I'm allowed to see courier pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/parts/couriers"
    Then the response status code should be 200
    And The module should be "SPR"
    And I should not see "Add Courier"
    And I should see "Chronopost"

  Scenario: As a superuser, test that I can edit a courier
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/parts/couriers"
    Then the response status code should be 200
    And I fill in "courier_batch[couriers][1][name]" with "Just for test"
    And I fill in "courier_batch[couriers][1][url]" with "https://test.fr"
    And I fill in "courier_batch[couriers][1][parameterName]" with "test"
    And press "courier_batch_submit"
    Then the response status code should be 200
    And I should be on "/parts/couriers"
    And I should see "Couriers have been successfully updated."

  Scenario: As a superuser, test that I can add a courier
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/parts/couriers"
    Then the response status code should be 200
    And I fill in "courier[name]" with "ups"
    And I fill in "courier[url]" with "https://ups.fr"
    And I fill in "courier[parameterName]" with "tracking"
    And press "courier_submit"
    Then the response status code should be 200
    And I should be on "/parts/couriers"
    And I should see "ups"
    And I should see "Courier has been successfully added."
