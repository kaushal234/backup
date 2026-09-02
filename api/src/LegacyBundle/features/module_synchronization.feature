Feature: Test division double write API

  Background: Authenticate user
    Given I authenticate as the intranet user "user-superuser@tld.fr"

  Scenario: Create a module in legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules" with the body "tests/fixtures/json/module/module/dummies/post.json"
    Then the response status code should be 201
    And a new row has been inserted in the legacy table "com_modules"

  Scenario: Update a module in legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/modules/1" with the body "tests/fixtures/json/module/module/dummies/put.json"
    Then the response status code should be 200
    And the column "module" from the "com_modules" legacy table has been updated with string 'TUPE'
    And the column "acronym" from the "agr" legacy table has been updated with string 'TUPE'
    And the column "module" from the "mod_logs" legacy table has been updated with string 'TUPE'
    And the column "module" from the "tasks" legacy table has been updated with string 'TUPE'
    # /people/23 legacyId = 2370
    And the column "oid" from the "com_modules" legacy table has been updated with integer 2370
    And the column "dsc" from the "com_modules" legacy table has been updated with string "Test&eacute;"
    And the column "note" from the "com_modules" legacy table has been updated with string "Lorem ipsum&eacute; <b>dolor</b> sit amet."
    And the column "user_guide_id" from the "com_modules" legacy table has been updated
    And the column "help_page_id" from the "com_modules" legacy table has been updated
    And the column "migrated" from the "com_modules" legacy table has been updated
    # /people/29 legacyId = 2386
    And the column "key_user_id" from the "com_modules" legacy table has been updated with integer 2376
