Feature: Test task status is double written

  Scenario: When the task is closed, status should be double written
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "tasks/9/close" with parameters:
    | key          | value                       |
    | comment      | Here we go!                 |
    Then the response status code should be 201
    And the column "status" from the "tasks" legacy table has been updated with string 'CLOSED'

  Scenario: When task is transfer, assignee should be double written
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "tasks/9/transfer" with parameters:
      | key           | value                     |
      | assignee      | /people/1                 |
      | comment       | Here we go!               |
    Then the response status code should be 201
    And the column "assignee" from the "tasks" legacy table has been updated with integer 2547

  Scenario: When task is created on API, no task is created in legacy database
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/tasks" with body:
    """
    {
      "module": "modules/13",
      "referenceId": 3,
      "indiceFactor": "IF 1",
      "escalationTrigger": 60,
      "escalationTriggerUnit": "DAYS",
      "startedAt": "1999-01-08 04:05:06",
      "dueDate": "2099-02-08 04:05:06",
      "shortDescription": "This is a short description",
      "description": "This is a description",
      "assignee": "people/13",
      "recipients": ["/people/29", "/people/55", "/people/56", "/people/61"]
    }
    """
    Then the response status code should be 201
    And 0 new rows have been inserted in the legacy table "tasks"

