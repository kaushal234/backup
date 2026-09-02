Feature: Directory / Juridical Location

  Scenario: As an anonymous user, test that i'm not allowed to see juridical location pages
    When I go to "/directory/juridical-locations"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/directory/juridical-locations/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm not allowed to see juridical location pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/juridical-locations"
    Then the response status code should be 200
    And I should see "You do not have permissions"
    When I go to "/directory/juridical-locations/1/show"
    Then the response status code should be 200
    And I should see "You do not have permissions"

  Scenario: As a superuser, test that i'm allowed to see juridical location pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/juridical-locations"
    Then the response status code should be 200
    And The module should be "DIR"
    And I should see 5 "table.footable tr" elements
    And the "table.footable tbody tr:nth-child(2) td:nth-child(1)" element should contain a text
    When I go to "/directory/juridical-locations/1/show"
    Then the response status code should be 200
    And I should see "JURIDICAL LOCATION#"
    And the ".association-table tr:nth-child(2) td:nth-child(2)" element should contain a text

  Scenario: As a superuser, test that i can add a juridical location
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/juridical-locations/add"
    Then the response status code should be 200
    When I fill in the following:
      | juridical_location[name] | MyTestJuridicalLocation |
      | juridical_location[address][street1] | 1 paradise hill |
      | juridical_location[address][postalCode] | 92000 |
      | juridical_location[address][city] | BOULOGNE |
    And I select "France" from "juridical_location[address][country]"
    And press "submit"
    Then the response status code should be 200
    And I should see "MyTestJuridicalLocation"

  Scenario: As a superuser, test that i can edit a juridical location
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/juridical-locations/1/edit"
    Then the response status code should be 200
    When I fill in "juridical_location[name]" with "MyRemplacementTestJuridicalLocation"
    And I fill in "juridical_location[address][postalCode]" with "75013"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/directory/juridical-locations/1/show"
    And I should see "MyRemplacementTestJuridicalLocation"

  Scenario: As a superuser, test that i can delete a juridical location
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/juridical-locations/4/delete"
    Then the response status code should be 200
    And I should see "Confirmation"
    When I check "juridical_location[confirm]"
    And press "delete"
    Then the response status code should be 200
    And I should be on "/directory/juridical-locations"
    And I should see 5 "table.footable tr" elements
