Feature: Test people API

  Scenario: Create a people on legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/people" with body:
    """
    {
      "firstname": "Leg",
      "lastname": "Acy",
      "password": "Mý v3ry B@d s3cr3T",
      "hidden": false,
      "disabled": false,
      "enableAt": "2023-10-26T00:00:00-0400",
      "closestAirport": "/airports/62",
      "premise": "/premises/1"
    }
    """
    Then the response status code should be 201
    And a new row has been inserted in the legacy table "people"
    And the column "enable_at" from the "people" legacy table has been inserted with string "2023-10-26"

  Scenario: Update a people
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/4" with the body "tests/fixtures/json/directory_people/dummies/put_full.json"
    Then the response status code should be 200
    And the column "phone" from the "people" legacy table has been updated with string "+33 5 17 17 17 17"
    And the column "direct_phone" from the "people" legacy table has been updated with string "+33 5 40 40 40 40"
    And the column "mobile" from the "people" legacy table has been updated with string "+33 6 69 69 69 69"
    And the column "fax" from the "people" legacy table has been updated with string "+33 2 00 01 10 00"
    And the column "home_phone" from the "people" legacy table has been updated with string "+33 01 01 01 01 01"
    And the column "firstname" from the "people" legacy table has been updated with string "new firstname"
    And the column "lastname" from the "people" legacy table has been updated with string "NEW LASTNAME"
    And the column "email" from the "people" legacy table has been updated with string "new.username@tld-gse.com"
    And the column "email" from the "people_groups" legacy table has been updated with string "new.username@tld-gse.com"
    And the column "poster" from the "file" legacy table has been updated with string "new.username@tld-gse.com"
    And the column "nickname" from the "people" legacy table has been updated with string "new nick"
    And the column "title" from the "people" legacy table has been updated with string "new job"
    And the column "baan_id" from the "people" legacy table has been updated with string "newlogin"
    And the column "baan_employee_id" from the "people" legacy table has been updated with string "6969"
    And the column "windows_id" from the "people" legacy table has been updated with string "windows95_4ever"
    And the column "bu_id" from the "people" legacy table has been updated with integer 56
    And the column "fct_id" from the "people" legacy table has been updated with integer 60
    And the column "div_id" from the "people" legacy table has been updated with integer 7
    And the column "dpt_id" from the "people" legacy table has been updated with integer 18
    And the column "contract_type" from the "people" legacy table has been updated with string "Saisonnier"
    And the column "coefficient" from the "people" legacy table has been updated with integer 95
    And the column "username" from the "people" legacy table has been updated with string "new.username@tld-gse.com"
    # /people/13 legacyId = 2360
    And the column "reports_to" from the "people" legacy table has been updated with integer 2360
    And the column "address" from the "people" legacy table has been updated with a string containing "rue Flaquette\nBarb Ship\nCP 17700 Zen\nCA\nItaly"

  Scenario: Update a people phone and update business unit
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/3" with body:
    """
    {
      "phones": [{
        "@id": "/phones/1",
        "type": "reception",
        "number": "+33 1 11 11 11 11"
      }],
      "businessUnit": "/business_units/11",
      "premise": "/premises/3"
    }
    """
    Then the response status code should be 200
    And  the column "phone" from the "people" legacy table has been updated with string "+33 1 11 11 11 11"
    And the column "bu_id" from the "people" legacy table has been updated with integer 60


  Scenario: Update a people phone and update business unit once again
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/3" with body:
    """
    {
      "phones": [{
        "type": "reception",
        "number": "+33 1 11 11 11 11"
      }],
      "businessUnit": "/business_units/10",
      "premise": "/premises/3"
    }
    """
    Then the response status code should be 200
    And  the column "phone" from the "people" legacy table has been updated with string "+33 1 11 11 11 11"
    And the column "bu_id" from the "people" legacy table has been updated with integer 56

  @resetFileTable
  Scenario: Change the user photo
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/people/12/photo" with file "file" "image_1200x1200.jpg"
    Then the response status code should be 201
    And the column "photo" from the "people" legacy table has been updated

  Scenario: Delete the user photo
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/people/12/photo/1"
    Then the response status code should be 204
    And the column "photo" from the "people" legacy table has been updated with string ""
