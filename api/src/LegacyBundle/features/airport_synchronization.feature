Feature: Test airport_codes double write API

  Background: Authenticate user
    Given I authenticate as the intranet user "user-superuser@tld.fr"

  Scenario: Create an airport in legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/airports" with the body "tests/fixtures/json/iata_code/dummies/post.json"
    Then the response status code should be 201
    Then a new row has been inserted in the legacy table "airport_codes"
    And the column "type" from the "airport_codes" legacy table has been inserted with string "Airport"
    And a new row has been inserted in the legacy table "mod_logs"

  Scenario: Update an airport in legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/airports/61" with the body "tests/fixtures/json/iata_code/dummies/put.json"
    Then the response status code should be 200
    And the column "city_code_3" from the "airport_codes" legacy table has been updated with string "PTT"
    And the column "city_name" from the "airport_codes" legacy table has been updated with string "Bougeons avec la Poste"
    And the column "state" from the "airport_codes" legacy table has been updated with string "Jarnac"
    And the column "ctry_code_2" from the "airport_codes" legacy table has been updated with string "FR"
    And the column "airport_code" from the "airport_codes" legacy table has been updated with string "SOAD"
    And the column "airport_name" from the "airport_codes" legacy table has been updated with string "Lonely Day"
    And the column "source" from the "airport_codes" legacy table has been updated with string "TLD"
    And the column "type" from the "airport_codes" legacy table has not been updated
    And a new row has been inserted in the legacy table "mod_logs"
