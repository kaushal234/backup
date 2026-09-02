Feature: Test Base task

  Scenario: Base task should be accessible only for intranet users
  Given I authenticate as the extranet user "julien.lepers@tld.com"
  And I add "Accept" header equal to "application/ld+json"
  When I send a "GET" request to "base_tasks"
  Then the response status code should be 403
  Given I authenticate as the extranet user "julien.lepers@tld.com"
  And I add "Accept" header equal to "application/ld+json"
  When I send a "GET" request to "base_tasks/1"
  Then the response status code should be 403
  Given I authenticate as the evendors user "vendor.user@vendor.fr"
  And I add "Accept" header equal to "application/ld+json"
  When I send a "GET" request to "base_tasks"
  Then the response status code should be 403
  Given I authenticate as the evendors user "vendor.user@vendor.fr"
  And I add "Accept" header equal to "application/ld+json"
  When I send a "GET" request to "base_tasks/1"
  Then the response status code should be 403

  Scenario: Filters are declared on resource
    Given the class "App\Entity\BaseTask" is exposed on the API
    Then the filter "status" should be available and its type should be "string"
    Then the filter "module" should be available and its type should be "string"
    Then the filter "createdBy" should be available and its type should be "string"
    Then the filter "assignee" should be available and its type should be "string"
    Then the filter "indiceFactor" should be available and its type should be "string"
    Then the filter "shortDescription" should be available and its type should be "string"
    Then the filter "description" should be available and its type should be "string"
    Then the filter "createdAt[after]" should be available and its type should be "DateTimeInterface"
    Then the filter "createdAt[before]" should be available and its type should be "DateTimeInterface"
    Then the filter "dueDate[after]" should be available and its type should be "DateTimeInterface"
    Then the filter "dueDate[before]" should be available and its type should be "DateTimeInterface"
    Then the filter "rescheduleDate[after]" should be available and its type should be "DateTimeInterface"
    Then the filter "rescheduleDate[before]" should be available and its type should be "DateTimeInterface"
    Then the filter "order[id]" should be available and its type should be "string"
    Then the filter "order[module.name]" should be available and its type should be "string"
    Then the filter "order[createdBy.lastname]" should be available and its type should be "string"
    Then the filter "order[assignee.lastname]" should be available and its type should be "string"
    Then the filter "order[indiceFactor]" should be available and its type should be "string"
    Then the filter "order[startedAt]" should be available and its type should be "string"
    Then the filter "order[dueDate]" should be available and its type should be "string"
    Then the filter "order[status]" should be available and its type should be "string"
    Then the filter "q" should be available and its type should be "string"
    And the filter "columns" should be available and its type should be "string"

  Scenario: As superuser I should not be able to get item to the base task.
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I send a "GET" request to "base_tasks/1"
    Then the response status code should be 404

  Scenario: As superuser I should not be able to post to the base task.
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "base_tasks" with body:
     """ {} """
    Then the response status code should be 405

  Scenario: As superuser I should not be able to put to the base task.
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "base_tasks" with body:
     """ {} """
    Then the response status code should be 405


  Scenario: As superuser I should not be able to delete to the base task.
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I send a "DELETE" request to "base_tasks/1"
    Then the response status code should be 405

  Scenario: As basic user I should be able to get collection to the base task.
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "base_tasks"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/base_task/schemas/base_tasks.json"

  Scenario: Download excel task reports should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "/base_tasks?columns=id,module,type,referenceId,createdBy,assignee,createdAt,dueDate,rescheduleDate,status,indiceFactor,shortDescription,description"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Id | Module | Type | Reference Id | Created By | Assignee | Created At | Due Date | Reschedule Date | Status | Indice Factor | Short Description | Description |