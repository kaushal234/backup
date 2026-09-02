Feature: Jobs can be created and edited using the API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\HumanResources\Job" should only be available for intranet user

  Scenario: User basic should be able to list jobs
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/jobs"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/job/schemas/jobs.json"

  Scenario: User basic should be able to fetch a job
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/jobs/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/job/schemas/job.json"

  Scenario: User basic should not be allowed to create a job
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/jobs" with the body "tests/fixtures/json/job/dummies/post.json"
    Then the response status code should be 403

  Scenario: User HR should be allowed to create a job
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/jobs" with the body "tests/fixtures/json/job/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/job/schemas/job.json"

  Scenario: User HR should be allowed to edit a job he created
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/jobs/3" with body:
    """
    {
      "title": "Ninja RockStar MultiFullStack Developer"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/job/schemas/job.json"
    And the JSON node "title" should be equal to the string "Ninja RockStar MultiFullStack Developer"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\HumanResources\Job" is exposed on the API
    Then the filter "businessUnit" should be available and its type should be "string"
    And the filter "enabled" should be available and its type should be "bool"
    And the filter "order[createdAt]" should be available and its type should be "string"
    And the filter "order[business.name]" should be available and its type should be "string"
    And the filter "createdAt[after]" should be available and its type should be "DateTimeInterface"
    And the filter "createdAt[before]" should be available and its type should be "DateTimeInterface"