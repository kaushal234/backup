Feature: Test Jobs double write API

  Background: Authenticate user
    Given I authenticate as the intranet user "user-superuser@tld.fr"

  Scenario: Create an job in legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/jobs" with the body "tests/fixtures/json/job/dummies/post.json"
    Then the response status code should be 201
    Then a new row has been inserted in the legacy table "hr_jobs"
    And the column "title" from the "hr_jobs" legacy table has been inserted with string "Ninja Rockstar Developer"
    And a new row has been inserted in the legacy table "mod_logs"

  Scenario: Update a job in legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/jobs/1" with body:
    """
    {
      "businessUnit": "/business_units/11",
      "title": "another title",
      "experience": "another experience",
      "diploma": "another ploma",
      "description": "another one bites the dust",
      "enabled": false
    }
    """
    Then the response status code should be 200
    And the column "title" from the "hr_jobs" legacy table has been updated with string "another title"
    And the column "experience" from the "hr_jobs" legacy table has been updated with string "another experience"
    And the column "diploma" from the "hr_jobs" legacy table has been updated with string "another ploma"
    And the column "description" from the "hr_jobs" legacy table has been updated with string "another one bites the dust"
    And the column "status" from the "hr_jobs" legacy table has been updated with string "CLOSED"
    And the column "buid" from the "hr_jobs" legacy table has been updated with integer 60
    And a new row has been inserted in the legacy table "mod_logs"
