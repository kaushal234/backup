Feature: Test Monthly activities can be managed

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Materials\Warehouse\TasksMapping" should only be available for intranet user

  Scenario: Tasks mappings should be accessible to intranet user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/materials/warehouse/tasks_mappings"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/warehouse_tasks_mappings/schemas/tasks_mappings.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Materials\Warehouse\TasksMapping" is exposed on the API
    Then the filter "location.erp" should be available and its type should be "int"

  Scenario: Tasks mapping detail should be accessible to intranet user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/materials/warehouse/tasks_mappings/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/warehouse_tasks_mappings/schemas/tasks_mapping.json"

  Scenario: Create a tasks mapping on another BU should not be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/materials/warehouse/tasks_mappings" with body:
    """
      {
        "location": "/locations/33",
        "inboundTasks": ["10"],
        "outboundTasks": ["11"],
        "administrativeTasks": ["12"],
        "excludedTasks": ["12"]
      }
    """
    Then the response status code should be 403

  Scenario: Create a tasks mapping using the same tasks number in distinct categories should not be allowed
    Given I authenticate as the intranet user "user-ws@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/materials/warehouse/tasks_mappings" with body:
    """
      {
        "location": "/locations/32",
        "inboundTasks": ["10", "11", "12345"],
        "outboundTasks": ["20", "11", "13", "a"],
        "administrativeTasks": ["10", "18", "13", "29"],
        "excludedTasks": ["10", "20", "18", "72"]
      }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "violations[0].propertyPath" should be equal to "outboundTasks"
    And the JSON node "violations[0].message" should contain "At least one task is already used as inbound task."
    And the JSON node "violations[1].propertyPath" should be equal to "administrativeTasks"
    And the JSON node "violations[1].message" should contain "At least one task is already used as inbound task."
    And the JSON node "violations[2].propertyPath" should be equal to "administrativeTasks"
    And the JSON node "violations[2].message" should contain "At least one task is already used as outbound task."
    And the JSON node "violations[3].propertyPath" should be equal to "excludedTasks"
    And the JSON node "violations[3].message" should contain "At least one task is already used as inbound task."
    And the JSON node "violations[4].propertyPath" should be equal to "excludedTasks"
    And the JSON node "violations[4].message" should contain "At least one task is already used as outbound task."
    And the JSON node "violations[5].propertyPath" should be equal to "excludedTasks"
    And the JSON node "violations[5].message" should contain "At least one task is already used as administrative task."
    And the JSON node "violations[6].propertyPath" should be equal to "inboundTasks[2]"
    And the JSON node "violations[6].message" should contain "This value should be an integer between 1 and 9999."
    And the JSON node "violations[7].propertyPath" should be equal to "outboundTasks[3]"
    And the JSON node "violations[7].message" should contain "This value should be an integer between 1 and 9999."

  Scenario: Create a tasks mapping without tasks should not be allowed
    Given I authenticate as the intranet user "user-ws@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/materials/warehouse/tasks_mappings" with body:
    """
      {
        "location": "/locations/32",
        "inboundTasks": [],
        "outboundTasks": [],
        "administrativeTasks": [],
        "excludedTasks": []
      }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "violations[0].propertyPath" should be equal to "inboundTasks"
    And the JSON node "violations[0].message" should contain "This collection should contain 1 element or more."
    And the JSON node "violations[1].propertyPath" should be equal to "outboundTasks"
    And the JSON node "violations[1].message" should contain "This collection should contain 1 element or more."
    And the JSON node "violations[2].propertyPath" should be equal to "administrativeTasks"
    And the JSON node "violations[2].message" should contain "This collection should contain 1 element or more."

  Scenario: Create a tasks mapping
    Given I authenticate as the intranet user "user-ws@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/materials/warehouse/tasks_mappings" with the body "tests/fixtures/json/warehouse_tasks_mappings/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/warehouse_tasks_mappings/schemas/tasks_mapping.json"
    And the JSON node "location.@id" should be equal to the string "/locations/32"
    And the JSON node "inboundTasks" should have 3 elements
    And the JSON node "inboundTasks[0]" should be equal to "10"
    And the JSON node "outboundTasks" should have 3 elements
    And the JSON node "outboundTasks[0]" should be equal to "11"
    And the JSON node "administrativeTasks" should have 3 elements
    And the JSON node "administrativeTasks[0]" should be equal to "12"
    And the JSON node "excludedTasks" should have 3 elements
    And the JSON node "excludedTasks[0]" should be equal to "13"

  Scenario: Create a tasks mapping on an existing location should trigger a validation error
    Given I authenticate as the intranet user "user-ws@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/materials/warehouse/tasks_mappings" with the body "tests/fixtures/json/warehouse_tasks_mappings/dummies/post.json"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "location: This value is already used."
    And the JSON node "violations[0].propertyPath" should be equal to "location"
    And the JSON node "violations[0].message" should contain "This value is already used."

  Scenario: Update a task mapping without permission should not be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/materials/warehouse/tasks_mappings/2" with the body "tests/fixtures/json/warehouse_tasks_mappings/dummies/put.json"
    Then the response status code should be 403

  Scenario: Update a task mapping with permission should be possible
    Given I authenticate as the intranet user "user-ws@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/materials/warehouse/tasks_mappings/4" with the body "tests/fixtures/json/warehouse_tasks_mappings/dummies/put.json"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/warehouse_tasks_mappings/schemas/tasks_mapping.json"
    And the JSON node "inboundTasks" should have 3 elements
    And the JSON node "inboundTasks[0]" should be equal to "110"
    And the JSON node "outboundTasks" should have 3 elements
    And the JSON node "outboundTasks[0]" should be equal to "111"
    And the JSON node "administrativeTasks" should have 3 elements
    And the JSON node "administrativeTasks[0]" should be equal to "112"
    And the JSON node "excludedTasks" should have 3 elements
    And the JSON node "excludedTasks[0]" should be equal to "113"
    # Location should be preserved
    And the JSON node "location.@id" should be equal to the string "/locations/32"

  Scenario: Deleting a task mapping should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/materials/warehouse/tasks_mappings/1"
    Then the response status code should be 405
