Feature: Test Task Entity

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Task\Task" is exposed on the API
    Then the filter "status" should be available and its type should be "string"
    And the filter "module" should be available and its type should be "string"
    And the filter "legacyId" should be available and its type should be "int"
    And the filter "referenceId" should be available and its type should be "int"
    And the filter "createdBy" should be available and its type should be "string"
    And the filter "assignee" should be available and its type should be "string"
    And the filter "indiceFactor" should be available and its type should be "string"
    And the filter "location" should be available and its type should be "string"
    And the filter "startedAt[after]" should be available and its type should be "DateTimeInterface"
    And the filter "startedAt[before]" should be available and its type should be "DateTimeInterface"
    And the filter "dueDate[after]" should be available and its type should be "DateTimeInterface"
    And the filter "dueDate[before]" should be available and its type should be "DateTimeInterface"
    And the filter "order[id]" should be available and its type should be "string"
    And the filter "order[module.name]" should be available and its type should be "string"
    And the filter "order[createdBy.lastname]" should be available and its type should be "string"
    And the filter "order[assignee.lastname]" should be available and its type should be "string"
    And the filter "order[indiceFactor]" should be available and its type should be "string"
    And the filter "order[startedAt]" should be available and its type should be "string"
    And the filter "order[dueDate]" should be available and its type should be "string"
    And the filter "order[status]" should be available and its type should be "string"
    And the filter "q" should be available and its type should be "string"
    And the filter "columns" should be available and its type should be "string"

  Scenario: As basic User I should not be able to write(POST) a task with due date in the past
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
      "dueDate": "1999-02-08 04:05:06",
      "shortDescription": "This is a short description",
      "description": "This is a description",
      "assignee": "people/11",
      "recipients": ["/people/29", "/people/55", "/people/56", "/people/61"]
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be equal to the string "dueDate"
    And the JSON node "violations[0].message" should contain "This value should be greater than"

  Scenario: As basic User I should be able to write(POST) a task
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
    And the JSON should be valid according to the schema "tests/fixtures/json/task/schemas/task.json"
    And the JSON node "module.@id" should be equal to the string "/modules/13"
    And the JSON node "referenceId" should be equal to the string "3"
    And the JSON node "indiceFactor" should be equal to the string "IF 1"
    And the JSON node "escalationTrigger" should be equal to the string "60"
    And the JSON node "escalationTriggerUnit" should be equal to the string "DAYS"
    And the JSON node "startedAt" should be equal to the string "1999-01-08T04:05:06-05:00"
    And the JSON node "dueDate" should be equal to the string "2099-02-08T04:05:06-05:00"
    And the JSON node "shortDescription" should be equal to the string "This is a short description"
    And the JSON node "description" should be equal to the string "This is a description"
    And the JSON node "createdBy.@id" should be equal to the string "/people/11"
    And the JSON node "assignee.@id" should be equal to the string "/people/13"
    And the JSON node "recipients[0].@id" should be equal to the string "/people/29"
    And the JSON node "recipients[1].@id" should be equal to the string "/people/55"
    And the JSON node "recipients[2].@id" should be equal to the string "/people/56"
    And the JSON node "recipients[3].@id" should be equal to the string "/people/61"
    And an email should have been sent asynchronously with subject matching pattern "/Task #\S+ opened, for HR user by BASIC user/"
    And this asynchronous email should be sent only to "user-hr@tld.fr"
    And this asynchronous email should be sent as cc only to "user-qam@tld.fr, user-em@tld.fr, user-mlm@tld.fr, user-eng@tld.fr"

  Scenario: As user who is not the creator of the task I should not be able to edit(PUT) the task
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/tasks/9" with body:
    """
    {}
    """
    Then the response status code should be 403

  Scenario: As the creator of a task, I should be able to edit it (PUT), but the due date must remain unchanged.
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/tasks/54" with body:
    """
    {
      "module": "modules/13",
      "referenceId": 3,
      "indiceFactor": "IF 1",
      "escalationTrigger": 60,
      "escalationTriggerUnit": "DAYS",
      "startedAt": "1999-01-08 04:05:06",
      "dueDate": "2299-02-08 04:05:06",
      "shortDescription": "This is a short description edited",
      "description": "This is a description edited",
      "assignee": "people/13",
      "recipients": ["/people/29", "/people/55", "/people/56", "/people/61"]
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/task/schemas/task.json"
    And the JSON node "module.@id" should be equal to the string "/modules/13"
    And the JSON node "referenceId" should be equal to the string "3"
    And the JSON node "indiceFactor" should be equal to the string "IF 1"
    And the JSON node "escalationTrigger" should be equal to the string "60"
    And the JSON node "escalationTriggerUnit" should be equal to the string "DAYS"
    And the JSON node "startedAt" should be equal to the string "1999-01-08T04:05:06-05:00"
    And the JSON node "dueDate" should be equal to the string "2099-02-08T04:05:06-05:00"
    And the JSON node "shortDescription" should be equal to the string "This is a short description edited"
    And the JSON node "description" should be equal to the string "This is a description edited"
    And the JSON node "createdBy.@id" should be equal to the string "/people/11"
    And the JSON node "assignee.@id" should be equal to the string "/people/13"
    And the JSON node "recipients[0].@id" should be equal to the string "/people/29"
    And the JSON node "recipients[1].@id" should be equal to the string "/people/55"
    And the JSON node "recipients[2].@id" should be equal to the string "/people/56"
    And the JSON node "recipients[3].@id" should be equal to the string "/people/61"
    And an email should have been sent asynchronously with subject matching pattern "/Task #\S+ updated, for HR user by BASIC user/"
    And this asynchronous email should be sent only to "user-hr@tld.fr"
    And this asynchronous email should be sent as cc only to "user-qam@tld.fr, user-em@tld.fr, user-mlm@tld.fr, user-eng@tld.fr"

  Scenario: As authorized user who want to send a comment, transfer, reschedule with wrong parameters I should get an error Unprocessable Content
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "tasks/54/comment" with parameters:
      | key               | value                       |
      | notAllowParameter | Here we go!                 |
    Then the response status code should be 422

  Scenario: As user who is not assignee of the task when I send a comment the status should not be switch from PENDING to IN PROGRESS
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "tasks/54/comment" with parameters:
      | key               | value                       |
      | comment           | Here we go!                 |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/task/schemas/task.json"
    And the JSON node "status" should be equal to the string "PENDING"
    And an email should have been sent asynchronously with subject matching pattern "/Task #\S+ commented by BASIC user/"
    And this asynchronous email should be sent only to "user-hr@tld.fr"
    And this asynchronous email should be sent as cc only to "user-qam@tld.fr, user-em@tld.fr, user-mlm@tld.fr, user-eng@tld.fr"

  Scenario: As assignee of the task when I send a comment the status should be switch from PENDING to IN PROGRESS
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "tasks/54/comment" with parameters:
      | key               | value                       |
      | comment           | Here we go!                 |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/task/schemas/task.json"
    And the JSON node "status" should be equal to the string "IN PROGRESS"
    And an email should have been sent asynchronously with subject matching pattern "/Task #\S+ commented by HR user/"
    And this asynchronous email should be sent only to "user-hr@tld.fr"
    And this asynchronous email should be sent as cc only to "user-qam@tld.fr, user-em@tld.fr, user-mlm@tld.fr, user-eng@tld.fr"

  Scenario: As user who is not createdBy of the task when I send a close comment the status should not be switch from IN PROGRESS to CLOSED
    Given I authenticate as the intranet user "user-ast@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "tasks/54/close" with parameters:
      | key               | value                       |
      | comment      | Here we go!                 |
    Then the response status code should be 403

  Scenario: As basic user I can't set a task on pause when I send a close comment
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "tasks/11/pause" with parameters:
      | key               | value                       |
      | comment           | take a break                |
    Then the response status code should be 403
    
  Scenario: As mis I can set a task on pause when I send a close comment and this should create a notification only for assignee
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "tasks/11/pause" with parameters:
      | key               | value                       |
      | comment           | take a break                |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/task/schemas/task.json"
    And the JSON node "status" should be equal to the string "PAUSE"
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/notifications"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/notification/schemas/notifications.json"
    And the JSON node "hydra:totalItems" should be equal to 0
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/notifications"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/notification/schemas/notifications.json"
    And the JSON node "hydra:totalItems" should be equal to 1

  # TTS#55729 - task_6 fixture: createdBy=user_basic, assignee=user_hr (whose supervisor is user_superuser)
  Scenario: As supervisor of the assignee I should be able to close the task even if I am neither assignee nor createdBy
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "tasks/15/close" with parameters:
      | key               | value                       |
      | comment           | Closing on behalf of my team |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/task/schemas/task.json"
    And the JSON node "status" should be equal to the string "CLOSED"

  Scenario: As basic user I can't unpause a task when I send a comment
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "tasks/11/comment" with parameters:
      | key               | value                       |
      | comment           | take a break                |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/task/schemas/task.json"
    And the JSON node "status" should be equal to the string "PAUSE"

  Scenario: As mis user I can unpause a task when I send a comment
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "tasks/11/comment" with parameters:
      | key               | value                       |
      | comment           | take a break                |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/task/schemas/task.json"
    And the JSON node "status" should be equal to the string "IN PROGRESS"

  Scenario: As task assignee I can change status to CLOSED
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "tasks/49/close" with parameters:
      | key               | value                       |
      | comment           | Close as assignee                |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/task/schemas/task.json"
    And the JSON node "status" should be equal to the string "CLOSED"

  @resetFileTable
  Scenario: A coo user should not be able to transfer it with file and change reschedule date
    Given I authenticate as the intranet user "user-coo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "tasks/54/transfer" with parameters:
      | key               | value                       |
      | comment           | Transfer reason             |
      | rescheduleDate    | 2125-01-08 04:05:06         |
      | assignee          | people/29                   |
      | file              | @file.doc                   |
    Then the response status code should be 403

  Scenario: As creator of the task I should be able to transfer it with file and change reschedule date, and it should create a notification only for assignee
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "tasks/54/transfer" with parameters:
      | key               | value                       |
      | comment           | Transfer reason             |
      | rescheduleDate    | 2035-01-09 04:05:06         |
      | assignee          | people/29                   |
      | file              | @file.doc                   |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/task/schemas/task.json"
    And the JSON node "assignee.@id" should be equal to the string "/people/29"
    And the JSON node "rescheduleDate" should be equal to the string "2035-01-09T04:05:06-05:00"
    And no email should have been sent asynchronously
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/notifications"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/notification/schemas/notifications.json"
    And the JSON node "hydra:totalItems" should be equal to 0
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/notifications"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/notification/schemas/notifications.json"
    #User QAM already has 3 notification in the fixtures
    And the JSON node "hydra:totalItems" should be equal to 4
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/notifications"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/notification/schemas/notifications.json"
    #User EVP has 1 notification created when a task has been set in pause
    And the JSON node "hydra:totalItems" should be equal to 1

  Scenario: As hr user should not be able to reschedule it with file
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "tasks/54/reschedule" with parameters:
      | key               | value                       |
      | comment           | Reschedule Reason           |
      | rescheduleDate    | 2025-01-08 04:05:06         |
      | file              | @file.doc                   |
    Then the response status code should be 403

  Scenario: A user who is createdBy or assignee of the task should not be able to reschedule it with file if the reschedule date is before today
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "tasks/54/reschedule" with parameters:
      | key               | value                       |
      | comment           | Reschedule Reason           |
      | rescheduleDate    | 2000-01-10 04:05:06         |
      | file              | @file.doc                   |
    Then the response status code should be 422

  Scenario: A user who is createdBy or assignee of the task should be able to reschedule it with file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "tasks/54/reschedule" with parameters:
      | key               | value                       |
      | comment           | Reschedule Reason           |
      | rescheduleDate    | 2045-01-10 04:05:06         |
      | file              | @file.doc                   |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/task/schemas/task.json"
    And the JSON node "rescheduleDate" should be equal to the string "2045-01-10T04:05:06-05:00"
    And no email should have been sent asynchronously
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/notifications"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/notification/schemas/notifications.json"
    And the JSON node "hydra:totalItems" should be equal to 0
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/notifications"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/notification/schemas/notifications.json"
    And the JSON node "hydra:totalItems" should be equal to 5
    Given I authenticate as the intranet user "user-em@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/notifications"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/notification/schemas/notifications.json"
    And the JSON node "hydra:totalItems" should be equal to 1
    Given I authenticate as the intranet user "user-mlm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/notifications"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/notification/schemas/notifications.json"
    And the JSON node "hydra:totalItems" should be equal to 1
    Given I authenticate as the intranet user "user-eng@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/notifications"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/notification/schemas/notifications.json"
    And the JSON node "hydra:totalItems" should be equal to 1

  Scenario: As creator of the task when I send a close comment the status should be switch from IN PROGRESS to CLOSED
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "tasks/54/close" with parameters:
      | key          | value                       |
      | comment      | Here we go!                 |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/task/schemas/task.json"
    And the JSON node "lastComment" should be equal to the string "Here we go!"
    And the JSON node "status" should be equal to the string "CLOSED"
    And the JSON node "closedAt" should not be null
    And no email should have been sent asynchronously
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/notifications"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/notification/schemas/notifications.json"
    And the JSON node "hydra:totalItems" should be equal to 0
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/notifications"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/notification/schemas/notifications.json"
    And the JSON node "hydra:totalItems" should be equal to 6
    Given I authenticate as the intranet user "user-em@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/notifications"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/notification/schemas/notifications.json"
    And the JSON node "hydra:totalItems" should be equal to 2
    Given I authenticate as the intranet user "user-mlm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/notifications"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/notification/schemas/notifications.json"
    And the JSON node "hydra:totalItems" should be equal to 2
    Given I authenticate as the intranet user "user-eng@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/notifications"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/notification/schemas/notifications.json"
    And the JSON node "hydra:totalItems" should be equal to 2

  Scenario: As creator of the task I should be able to reopen a task who does not have more than 60 day old the status should be switch from CLOSED to PENDING
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/tasks/54/reopen" with parameters:
      | key               | value                       |
      | comment           | test reopen        |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/task/schemas/task.json"
    And the JSON node "status" should be equal to the string "PENDING"
    And the JSON node "lastComment" should be equal to the string "Here we go!"
    And the JSON node "closedAt" should be null
    And an email should have been sent asynchronously with subject matching pattern "/Task #\S+ has been reopened by BASIC user/"
    And this asynchronous email should be sent only to "user-qam@tld.fr"
    And this asynchronous email should be sent as cc only to "user-em@tld.fr, user-mlm@tld.fr, user-eng@tld.fr"

  Scenario: As creator of the task I should not be able to reopen a task closed more than 60 days ago
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/tasks/10/reopen" with parameters:
      | key               | value                       |
      | comment           | test reopen        |
    Then the response status code should be 403

  Scenario: Download excel task reports should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "/tasks?columns=id,module,referenceId,createdBy,assignee,createdAt,startedAt,dueDate,rescheduleDate,status,indiceFactor,shortDescription,description,escalationTrigger"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Id | Module | Reference Id | Created By | Assignee | Created At | Started At | Due Date | Reschedule Date | Status | Indice Factor | Short Description | Description | Escalation Trigger |

  Scenario: As coo user I should not be able to upload attached file
    Given I authenticate as the intranet user "user-coo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/tasks/9/files" with file "file" "file.doc"
    Then the response status code should be 403

  Scenario: As creator or assignee of the task I should be able to upload attached file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/tasks/54/files" with file "file" "file.doc"
    Then the response status code should be 201

  Scenario:  As an user who is not the creator of the task I should not be able to delete attached file
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "DELETE" request to "/tasks/54/files/3" with file "file" "file.doc"
    Then the response status code should be 403

  Scenario:  As createdBy of the task I should be able to delete attached file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "DELETE" request to "/tasks/54/files/3" with file "file" "file.doc"
    Then the response status code should be 204

  Scenario:  As createdBy of the task I should not be able to delete task
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "DELETE" request to "/tasks/54"
    Then the response status code should be 405

  # TTS#55805 - task_5 fixture: createdBy=user_basic, assignee=user_hr (whose supervisor is user_superuser)
  Scenario: As supervisor of the assignee I should be able to transfer the task even if I am neither assignee nor createdBy
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "tasks/14/transfer" with parameters:
      | key               | value                       |
      | comment           | Reassigning within my team  |
      | rescheduleDate    | 2035-01-10 04:05:06         |
      | assignee          | people/56                   |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/task/schemas/task.json"
    And the JSON node "assignee.@id" should be equal to the string "/people/56"