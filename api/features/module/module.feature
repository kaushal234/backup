Feature: Test modules API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Module\Module" should only be available for intranet user

  Scenario: Request all modules
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/modules?itemsPerPage=25"
    Then the response status code should be 200
    And the JSON node "hydra:member" should have 25 element
    And the JSON node "hydra:totalItems" should be superior to the number 40
    And the JSON should be valid according to the schema "tests/fixtures/json/module/module/schemas/modules.json"

  Scenario: Filter modules - only with open update tasks
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/modules?withOpenUpdateTasksOnly=1"
    Then the response status code should be 200
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@type" should be equal to the string "Extended"
    And the JSON node "hydra:member[0].countUpdateTasks" should be equal to 9

  Scenario: Export XLS file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "modules?columns=id,name,operationalOwner&name=2RE"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Id | Name | Operational Owner |
    And the xlsx file should have 2 lines

  Scenario: Request modules-tree
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/modules_tree"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module/module/schemas/modules_tree.json"

  Scenario: Request a single module
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/modules/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module/module/schemas/module.json"
    And the JSON node "name" should be equal to "YEAH"

  Scenario: Update a given module - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/modules/20" with body:
    """
    {
      "name": "TRUC"
    }
    """
    Then the response status code should be 403

  Scenario: Update a given module - duplicated name
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/modules/20" with body:
    """
    {
      "name": "YEAH"
    }
    """
    Then the response status code should be 409

  Scenario: Update a given module - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/modules/20" with body:
    """
    {
      "name": "TRUC",
      "department": "/departments/3",
      "misRelative": false
    }
    """
    Then the response status code should be 200
    And the JSON node "name" should be equal to "TRUC"
    And the JSON node "department.name" should be equal to "dpt_rastaman"
    And the JSON node "misRelative" should be false

  Scenario: Update module status to DISABLED shouldn't be possible if the module get links with other items
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/modules/5" with body:
    """
    {
      "status": "DISABLED"
    }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to 'Disable module "SFR" is impossible because link exists with : 4 open trouble ticket(s), 1 open project(s), 1 default assignee(s). Please update those items first.'

  Scenario: Create a module - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules" with the body "tests/fixtures/json/module/module/dummies/post.json"
    Then the response status code should be 403

  Scenario: Create a module - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules" with the body "tests/fixtures/json/module/module/dummies/post.json"
    Then the response status code should be 201
    And the JSON nodes should be equal to:
      | name                     | POST                                 |
      | operationalOwner.@id     | /people/22                           |
      | keyUser.@id              | /people/29                           |
      | shortDescription         | Test                                 |
      | fullDescription          | Lorem ipsum <b>dolor</b> sit amet.   |
      | legacyLoc                | 0                                    |
    And the JSON node migrationEstimatedHours should be equal to the number 0
    And the JSON node dmsProcedureId should be null
    And the JSON node dmsHelpId should be null
    And the JSON node migrationCurrentStep should be null
    And the JSON node migrated should be false
    And the JSON node localKeyUsers should have 1 element
    And the JSON node "localKeyUsers[0].@id" should be equal to the string "/people/14"

  Scenario: Adding a LKU with already existing LKU region should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/modules/33" with body:
    """
    {
      "localKeyUsers": ["/people/11", "/people/12"]
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be equal to "localKeyUsers"
    And the JSON node "violations[0].message" should contain "Only one LKU per Region is permitted."

  Scenario: Update a module link
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/modules/20" with body:
    """
    {
      "requiredModules": ["\/modules/18", "\/modules\/19"]
    }
    """
    Then the response status code should be 200
    And one JSON array element at node requiredModules should contain "/modules/18" in property "@id"
    And one JSON array element at node requiredModules should contain "/modules/19" in property "@id"
    Then I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/modules/18"
    Then one JSON array element at node requiringModules should contain "/modules/20" in property "@id"

  Scenario: Update a module to migrated should populate the property migratedAt
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/modules/20" with body:
    """
    {
      "migrated": true,
      "transferOperationalOwner": true
    }
    """
    Then the response status code should be 200
    And the JSON node "migratedAt" should be newer than 1 minute ago

  Scenario: Request module ordered by name
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/modules?order[name]"
    Then the response status code should be 200

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Module\Module" is exposed on the API
    Then the filter "name" should be available and its type should be "string"
    And the filter "shortDescription" should be available and its type should be "string"
    And the filter "legacyId" should be available and its type should be "int"
    And the filter "application" should be available and its type should be "string"
    And the filter "application.name" should be available and its type should be "string"
    And the filter "department.name" should be available and its type should be "string"
    And the filter "status" should be available and its type should be "string"
    And the filter "operationalOwner" should be available and its type should be "string"
    And the filter "order[name]" should be available and its type should be "string"
    And the filter "order[shortDescription]" should be available and its type should be "string"
    And the filter "order[application.name]" should be available and its type should be "string"
    And the filter "order[department.name]" should be available and its type should be "string"
    And the filter "order[disabledForTroubleTicket]" should be available and its type should be "string"
    And the filter "columns" should be available and its type should be "string"
    And the filter "q" should be available and its type should be "string"
    And the filter "order[countUpdateTasks]" should be available and its type should be "string"
    And the filter "withOpenUpdateTasksOnly" should be available and its type should be "bool"
    And the filter "disabledForTroubleTicket" should be available and its type should be "bool"
    And the query parameter "exact[name]" should be available

  Scenario: A user can use the simple search in module
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/modules?q=test"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module/module/schemas/modules.json"

  Scenario: Update a module to desactivate MOO notification isn't possible if key user missing
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/modules/20" with body:
    """
    {
      "notifyOperationalOwner": false
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "keyUser: A Key User must be defined if notifyOperationalOwner is disabled."

  Scenario: Update a module to desactivate MOO notification is possible if key user present
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/modules/3" with body:
    """
    {
      "notifyOperationalOwner": false
    }
    """
    Then the response status code should be 200
    And the JSON node "notifyOperationalOwner" should be false

  Scenario: Update a module to desactivate GKU notification isn't possible if LKU missing
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/modules/3" with body:
    """
    {
      "notifyKeyUser": false
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "localKeyUsers: At least one Local Key User must be defined if notifyKeyUser is disabled."

  Scenario: Update a module to desactivate GKU notification is possible if LKU present
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/modules/27" with body:
    """
    {
      "notifyKeyUser": false
    }
    """
    Then the response status code should be 200
    And the JSON node "notifyKeyUser" should be false