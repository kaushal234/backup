Feature: Support / IATA Codes

  Scenario: As an anonymous user, test that i'm not allowed to see IATA code pages
    When I go to "/support/iata-codes"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/support/iata-codes/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see IATA code pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/iata-codes"
    Then the response status code should be 200
    And The module should be "APC"
    And I should see "No results"
    When I go to "/support/iata-codes/1/show"
    Then the response status code should be 200
    And I should see "Source"
    And I should not see an "a[href$='/support/iata-codes/1/edit']" element

  Scenario: As a superuser, test that i'm allowed to see admin links on IATA code pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/iata-codes"
    Then the response status code should be 200
    And I should see an "a[href$='/support/iata-codes/add']" element
    And I should see "No results"
    When I go to "/support/iata-codes/1/show"
    Then the response status code should be 200
    And I should see an "a[href$='/support/iata-codes/1/edit']" element

  Scenario: As a superuser, test that i can add a IATA code
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/iata-codes/add"
    Then the response status code should be 200
    When I fill in the following:
      | app_iata_codes[code]      | RIP    |
      | app_iata_codes[cityName]  | Johnny |
    And I select "Off-Line Point" from "app_iata_codes[type]"
    And I select "TLD" from "app_iata_codes[source]"
    And press "submit"
    Then the response status code should be 200
    And I should see "The Off-Line Point RIP has been successfully created"

  Scenario: As a superuser, test that i can edit a IATA code
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/iata-codes/1/edit"
    Then the response status code should be 200
    When I fill in "app_edit_iata_codes[code]" with "CZK"
    When I fill in "app_edit_iata_codes[cityName]" with "Zen Kane"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/support/iata-codes/1/show"
    And I should see "The Heliport CZK has been successfully edited"

  Scenario: As a ER Moo, test that I can add a IATA airport code
    Given I authenticate as "user-coo@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/iata-codes/add"
    Then the response status code should be 200
    When I fill in the following:
      | app_iata_codes[code]      | TLE    |
      | app_iata_codes[cityName]  | Toliara |
      | app_iata_codes[latitude]  | 0 |
      | app_iata_codes[longitude]  | 0 |
    And I select "Airport" from "app_iata_codes[type]"
    And I select "IATA" from "app_iata_codes[source]"
    And press "submit"
    Then the response status code should be 200
    And I should see "The Airport TLE has been successfully created"

  Scenario: As a ER Moo, test that I can edit a IATA airport code
    Given I authenticate as "user-coo@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/iata-codes/62/edit"
    Then the response status code should be 200
    When I fill in "app_edit_iata_codes[code]" with "CDG"
    When I fill in "app_edit_iata_codes[cityName]" with "Paris"
    When I fill in "app_edit_iata_codes[latitude]" with "55"
    When I fill in "app_edit_iata_codes[longitude]" with "2"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/support/iata-codes/62/show"
    And I should see "The Airport CDG has been successfully edited"
