Feature: Test Monthly activities can be managed

  Scenario: Request all tasks mappings without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/materials/warehouse/monthly_activities"
    Then the response status code should be 401

  Scenario: Request a single monthly activities without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/materials/warehouse/monthly_activities/1"
    Then the response status code should be 401

  Scenario: Monthly activities should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/materials/warehouse/monthly_activities"
    Then the response status code should be 403

  Scenario: Monthly activities should be accessible to intranet user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/materials/warehouse/monthly_activities"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/warehouse_monthly_activities/schemas/monthly_activities.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Materials\Warehouse\MonthlyActivities" is exposed on the API
    Then the filter "applicatedOn[after]" should be available and its type should be "DateTimeInterface"
    And the filter "applicatedOn[strictly_before]" should be available and its type should be "DateTimeInterface"
    And the filter "location" should be available and its type should be "string"
    And the filter "location.erp" should be available and its type should be "int"
    And the filter "context[datetime_format]" should be available and its type should be "string"
    And the filter "order[location.name]" should be available and its type should be "string"
    And the filter "order[applicatedOn]" should be available and its type should be "string"

  Scenario: Monthly activities detail should not be accessible to intranet user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/materials/warehouse/monthly_activities/1"
    Then the response status code should be 404

  Scenario: Creating a monthly activities should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/materials/warehouse/monthly_activities" with body:
    """
    {}
    """
    Then the response status code should be 405

  Scenario: Updating a monthly activities should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/materials/warehouse/monthly_activities/1" with body:
    """
    {}
    """
    Then the response status code should be 405

  Scenario: Deleting a monthly activities should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/materials/warehouse/monthly_activities/1"
    Then the response status code should be 405
