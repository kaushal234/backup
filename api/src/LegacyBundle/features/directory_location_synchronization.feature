Feature: Test location double write API

  Background: Authenticate user
    Given I authenticate as the intranet user "user-superuser@tld.fr"

  Scenario: Create a directory location in legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/locations" with the body "tests/fixtures/json/directory_location/dummies/post.json"
    Then the response status code should be 201
    Then a new row has been inserted in the legacy table "locations"

  Scenario: Update a directory location in legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/locations/1" with the body "tests/fixtures/json/directory_location/dummies/put.json"
    Then the response status code should be 200
    And the column "location" from the "locations" legacy table has been updated with string "Say my name! Say my name !"
    And the column "company_name" from the "locations" legacy table has been updated
    And the column "email_domain" from the "locations" legacy table has been updated
    And the column "fw_inside_network" from the "locations" legacy table has been updated
    And the column "erp" from the "locations" legacy table has been updated
    # /juridical_locations/3 legacyId = 15
    And the column "juridical_location_id" from the "locations" legacy table has been updated with integer 15
    # /people/12 legacyId = 2359
    And the column "repid" from the "locations" legacy table has been updated with integer 2359
    And the column "dcur" from the "locations" legacy table has been updated
    And the column "timezone" from the "locations" legacy table has been updated
    And the column "street1" from the "locations" legacy table has been updated
    And the column "street2" from the "locations" legacy table has been updated
    And the column "city" from the "locations" legacy table has been updated
    And the column "town" from the "locations" legacy table has been updated
    And the column "postal_code" from the "locations" legacy table has been updated with string "CP 17700"
    And the column "country" from the "locations" legacy table has been updated with string "Italy"
    And the column "role" from the "locations" legacy table has been updated with string "SSO"
    And the column "factory" from the "locations" legacy table has been updated with string "Y"
    And the column "warehouse" from the "locations" legacy table has been updated with string "Y"
    And the column "sph" from the "locations" legacy table has been updated with string "Y"
    And the column "sh" from the "locations" legacy table has been updated with string "Y"
    And the column "hq" from the "locations" legacy table has been updated with string "Y"
    And the column "public" from the "locations" legacy table has been updated with integer 0
    And the column "hidden" from the "locations" legacy table has been updated with integer "0"
    And the column "disable" from the "locations" legacy table has been updated with integer "0"
    And the column "sales_org" from the "service" legacy table has been updated with string "Say my name! Say my name !"
    And the column "sso_service" from the "service" legacy table has been updated with string "Say my name! Say my name !"
    And the column "man_location" from the "service" legacy table has been updated with string "Say my name! Say my name !"
    And the column "sales_org" from the "warranty" legacy table has been updated with string "Say my name! Say my name !"
    And the column "man_location" from the "warranty" legacy table has been updated with string "Say my name! Say my name !"
    And the column "factory" from the "pi_family_matrix" legacy table has been updated with string "Say my name! Say my name !"
