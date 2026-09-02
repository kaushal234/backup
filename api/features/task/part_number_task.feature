Feature: Test Part Number Task

  Scenario: Filters are declared on part number task resource
    Given the class "App\Entity\Task\PartNumberTask" is exposed on the API
    Then the filter "id" should be available and its type should be "int"
    And the filter "assignee" should be available and its type should be "string"
    And the filter "indiceFactor" should be available and its type should be "string"
    And the filter "location" should be available and its type should be "string"
    And the filter "status" should be available and its type should be "string"
    And the filter "partNumber" should be available and its type should be "string"
    And the filter "order[partNumber]" should be available and its type should be "string"
    And the filter "q" should be available and its type should be "string"

  Scenario: As basic User I should see part number in part number task collection
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/part_number_tasks"
    Then the response status code should be 200
    And the JSON node "hydra:member[0].@type" should be equal to the string "PartNumberTask"
    And the JSON node "hydra:member[0].partNumber" should exist

  Scenario: As basic User I should see part number when reading a part number task from task endpoint
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/tasks/53"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/task/schemas/task.json"
    And the JSON node "@type" should be equal to the string "PartNumberTask"
    And the JSON node "partNumber" should be equal to the string "pn-000001"

  Scenario: As creator of a part number task I should be able to edit its part number
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/part_number_tasks/53" with body:
    """
      {
        "partNumber": "00310-353-99999"
      }
      """
    Then the response status code should be 200
    And the JSON node "@type" should be equal to the string "PartNumberTask"
    And the JSON node "partNumber" should be equal to the string "00310-353-99999"

  Scenario: As basic User I should be able to create a part number task
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/part_number_tasks" with body:
    """
      {
        "module": "modules/15",
        "referenceId": 32,
        "indiceFactor": "IF 1",
        "escalationTrigger": 60,
        "escalationTriggerUnit": "DAYS",
        "startedAt": "1999-01-08 04:05:06",
        "dueDate": "2099-02-08 04:05:06",
        "shortDescription": "This is a part number task",
        "description": "This is a part number task description",
        "assignee": "people/13",
        "location": "locations/23",
        "partNumber": "00310-353-10000",
        "recipients": ["/people/29", "/people/55", "/people/56", "/people/61"]
      }
      """
    Then the response status code should be 201
    And the JSON node "@type" should be equal to the string "PartNumberTask"
    And the JSON node "partNumber" should be equal to the string "00310-353-10000"
    And the JSON node "location.@id" should be equal to the string "/locations/23"

