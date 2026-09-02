Feature: Test crabs double write API

  Scenario: Create a crab in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/crabs" with body:
    """
    {
      "status": "CLOSED",
      "equipmentRecord": "/equipment_records/28",
      "part": {
          "partNumber": "PN752822",
          "description": "boulon",
          "quantity": 1,
          "unitOfMeasure": "EA"
      },
      "category": "Test",
      "department": "/quality/crab_departments/1",
      "nonConformity": "/quality/non_conformities/1",
      "code": "/quality/crab_codes/1",
      "description": "Un pingouin sur la banquise Se dandine"
    }
    """
    Then the response status code should be 201
    And a new row has been inserted in the legacy table "crabs"
    And the column "dt" from the "crabs" legacy table has been inserted
    And the column "pn" from the "crabs" legacy table has been inserted with string "PN752822"
    And the column "status" from the "crabs" legacy table has been inserted with string "TO-FIX"
    And the column "dsca" from the "crabs" legacy table has been inserted with string "Un pingouin sur la banquise Se dandine"
    And the column "dept" from the "crabs" legacy table has been inserted with string "Paint"
    And the column "erid" from the "crabs" legacy table has been inserted with integer 37482
    And the column "init_emno" from the "crabs" legacy table has been inserted with integer 2359
    And the column "code" from the "crabs" legacy table has been inserted with integer 493
    And the column "ncrid" from the "crabs" legacy table has been inserted with integer 1
    And 1 new rows have been inserted in the legacy table "mod_logs"
    And the column "module" from the "mod_logs" legacy table has been inserted with string "CRAB"
    And a message of class "App\Message\Quality\Crab\CrabWrite" should have been sent in the bus

  Scenario: Update crab in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/crabs/4" with body:
    """
    {
      "part": {
          "partNumber": "PN752823",
          "description": "boulon",
          "quantity": 1,
          "unitOfMeasure": "EA"
      }
    }
    """
    Then the response status code should be 200
    And the column "pn" from the "crabs" legacy table has been updated with string "PN752823"
    And a new row has been inserted in the legacy table "mod_logs"





