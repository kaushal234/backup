Feature: Test manufacturing margin double write API

  Scenario: Create a manufacturing margin in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/finance/manufacturing_margins" with the body "tests/fixtures/json/manufacturing_margin/dummies/post.json"
    Then the response status code should be 201
    And a new row has been inserted in the legacy table "mfg_margins"
    And a new row has been inserted in the legacy table "mod_logs"

  Scenario: Update a manufacturing margin in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/finance/manufacturing_margins/1" with the body "tests/fixtures/json/manufacturing_margin/dummies/put.json"
    Then the response status code should be 200
    # /equipment_records/13 legacy Id must be 37467
    And the column "er_id" from the "mfg_margins" legacy table has been updated with integer 37467
    And the column "cur" from the "mfg_margins" legacy table has been updated with string "TWD"
    And the column "year" from the "mfg_margins" legacy table has been updated with integer 2019
    And the column "month" from the "mfg_margins" legacy table has been updated with integer 10
    And the column "factory_rev" from the "mfg_margins" legacy table has been updated with integer 15641
    And the column "std_hour" from the "mfg_margins" legacy table has been updated with integer 128
    And the column "act_hour" from the "mfg_margins" legacy table has been updated with integer 122
    And the column "std_lab_cost" from the "mfg_margins" legacy table has been updated with integer 9991
    And the column "act_lab_cost" from the "mfg_margins" legacy table has been updated with integer 10001
    And the column "std_mat" from the "mfg_margins" legacy table has been updated with integer 99991
    And the column "act_mat" from the "mfg_margins" legacy table has been updated with integer 100001
    And the column "std_other_mat" from the "mfg_margins" legacy table has been updated with integer 991
    And the column "act_other_mat" from the "mfg_margins" legacy table has been updated with integer 1001
    And the column "std_other_dir_cost" from the "mfg_margins" legacy table has been updated with integer 91
    And the column "act_other_dir_cost" from the "mfg_margins" legacy table has been updated with integer 101
    And the column "comment" from the "mfg_margins" legacy table has been updated with string "Just for tests, but on edition"
    And the column "ocp_hours" from the "mfg_margins" legacy table has been updated with integer "-9"
    And a new row has been inserted in the legacy table "mod_logs"
