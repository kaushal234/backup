Feature: Test country double write API

  Background: Authenticate user
    Given I authenticate as the intranet user "user-superuser@tld.fr"

  Scenario: Create a country in legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/countries" with the body "tests/fixtures/json/country/dummies/post.json"
    Then the response status code should be 201
    Then a new row has been inserted in the legacy table "countries"

  Scenario: Update a country in legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/countries/10" with the body "tests/fixtures/json/country/dummies/put.json"
    Then the response status code should be 200
    And the column "name" from the "countries" legacy table has been updated
    And the column "alt_name" from the "countries" legacy table has been updated
    And the column "iso_code_2" from the "countries" legacy table has been updated
    And the column "iso_code_3" from the "countries" legacy table has been updated
    And the column "nb_code" from the "countries" legacy table has been updated
    And the column "region" from the "countries" legacy table has been updated
    And the column "sub_region" from the "countries" legacy table has been updated
    And the column "fips_code" from the "countries" legacy table has been updated
    And the column "fips_name" from the "countries" legacy table has been updated
    And the column "gps_lat" from the "countries" legacy table has been updated
    And the column "gps_long" from the "countries" legacy table has been updated
