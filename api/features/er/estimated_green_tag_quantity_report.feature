Feature: Test class EstimatedGreenTagQuantityReport Entity

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Support\EquipmentRecord\EstimatedGreenTagQuantityReport" is exposed on the API
    Then the filter "id" should be available and its type should be "int"
    And the filter "manufacturerLocation" should be available and its type should be "string"
    And the filter "equipmentRecords.combinationMode" should be available and its type should be "string"
    And the filter "equipmentRecords.product.family" should be available and its type should be "string"
    And the filter "day[before]" should be available and its type should be "DateTimeInterface"
    And the filter "day[after]" should be available and its type should be "DateTimeInterface"

  Scenario: Request all reports
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/estimated_green_tag_quantity_reports"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/estimated_green_tag_quantity_report/schemas/estimated_green_tag_quantity_reports.json"

  Scenario: Request a single report
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/estimated_green_tag_quantity_reports/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/estimated_green_tag_quantity_report/schemas/estimated_green_tag_quantity_report.json"

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Support\EquipmentRecord\EstimatedGreenTagQuantityReport" should only be available for "intranet" user
