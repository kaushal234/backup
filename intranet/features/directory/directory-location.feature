Feature: Directory / Location

  Scenario: As an anonymous user, test that i'm not allowed to see locations pages
    When I go to "/directory/locations"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/directory/locations/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see locations pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/locations"
    Then the response status code should be 200
    And I should see "Business Unit Parameters Listing"
    When I go to "/directory/locations/1/show"
    Then the response status code should be 200
    And I should see "Business Unit Parameters Details"

  Scenario: As a superuser, test that i'm allowed to see locations pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/locations"
    And The module should be "DIR"
    And the "table.footable tbody tr:nth-child(2) td:nth-child(1)" element should contain a text
    When I go to "/directory/locations?showDisabled=1"
    And The module should be "DIR"
    When I go to "/directory/locations/1/show"
    Then I should see "Location#"
    And the ".association-table tr:nth-child(1) td:nth-child(2)" element should contain "1"
    And the ".association-table tr:nth-child(2) td:nth-child(2)" element should contain a text

  Scenario: As a superuser, test that i can add a location
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/locations/add"
    Then the response status code should be 200
    When I fill in the following:
      | location[name]    | MyTestLocationName    |
      | location[company] | MyTestLocationCompany |
      | location[domain]  | MyTestLocationDomain  |
    And I select "/business_units/1" from "location[businessUnit]"
    And I select "/juridical_locations/1" from "location[juridicalLocation]"
    And I select "/people/1" from "location[representative]"
    And I select "Europe/Paris" from "location[timeZone]"
    And press "submit"
    Then the response status code should be 200
    And I should see "MyTestLocationName"

  Scenario: As a superuser, test that i can edit a location
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/locations/1/edit"
    Then the response status code should be 200
    When I fill in "location[name]" with "LocationNameEdited"
    And I select "Europe/Paris" from "location[timeZone]"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/directory/locations/1/show"
    And I should see "LocationNameEdited"
