Feature: Test acronym category API

  Scenario: Request all acronym categories without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/acronym_categories"
    Then the response status code should be 401

  Scenario: Request a single acronym categories without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/acronym_categories/1"
    Then the response status code should be 401

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\AcronymCategory" should only be available for intranet user

  Scenario: Request all acronym categories
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/acronym_categories"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/acronym_category/schemas/acronym_categories.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\AcronymCategory" is exposed on the API
    Then the filter "order[name]" should be available and its type should be "string"

  Scenario: Request a single acronym category
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/acronym_categories/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/acronym_category/schemas/acronym_category.json"

  Scenario: User can't add an acronym category
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/acronym_categories"
    Then the response status code should be 405

  Scenario: User can't update an acronym category
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/acronym_categories/1"
    Then the response status code should be 405

  Scenario: User can't delete an acronym category
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/acronym_categories/1"
    Then the response status code should be 405
