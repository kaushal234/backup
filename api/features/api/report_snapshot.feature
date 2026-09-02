Feature: Test Report Snapshot API

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Report\ReportSnapshot" is exposed on the API
    And the filter "resource" should be available and its type should be "string"
    And the filter "x" should be available and its type should be "string"
    And the filter "y" should be available and its type should be "string"
    And the filter "createdAt[after]" should be available and its type should be "DateTimeInterface"
    And the filter "createdAt[before]" should be available and its type should be "DateTimeInterface"

  Scenario: Request all reports
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/report_snapshots"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report_snapshot/schemas/report_snapshots.json"