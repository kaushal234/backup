Feature: Test module migrations steps API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Module\ModuleMigrationStep" should only be available for intranet user

  Scenario: Request all module migration steps
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/module_migration_steps"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module_migration_step/schemas/module_migration_steps.json"

  Scenario: Request a single module
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/module_migration_steps/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module_migration_step/schemas/module_migration_step.json"
    And the JSON node "shortDesc" should be equal to "Step fixed"
