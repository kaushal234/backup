Feature: Test Planning daily exception

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Sales\EquipmentShippingRecord\PlanningDailyException" is exposed on the API
    Then the filter "factory" should be available and its type should be "string"
    Then the filter "date[before]" should be available and its type should be "DateTimeInterface"
    Then the filter "date[after]" should be available and its type should be "DateTimeInterface"

  Scenario: Request all planning daily exceptions is possible for basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/planning_daily_exceptions"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/planning_daily_exception/schemas/planning_daily_exceptions.json"

  Scenario: Get a planning daily exception is possible for all
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/planning_daily_exceptions/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/planning_daily_exception/schemas/planning_daily_exception.json"
    
  Scenario: Create a planning daily exception is not possible for not allowed user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/planning_daily_exceptions" with body:
    """
    {
      "factory": "/locations/29",
      "date": "2026-02-12",
      "comment": "Factory closure"
    }
    """
    Then the response status code should be 403

  Scenario: Create a planning daily exception should be possible for allowed user
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/planning_daily_exceptions" with body:
    """
    {
      "factory": "/locations/29",
      "date": "2026-02-12",
      "comment": "Factory closure"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/planning_daily_exception/schemas/planning_daily_exception.json"

  Scenario: Update a planning daily exception is not possible for not allowed user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/planning_daily_exceptions/1" with body:
    """
    {
      "comment": "Reduced capacity"
    }
    """
    Then the response status code should be 403

  Scenario: Update a planning daily exception should be possible for allowed user
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/planning_daily_exceptions/1" with body:
    """
    {
      "comment": "Factory closure confirmed"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/planning_daily_exception/schemas/planning_daily_exception.json"
    And the JSON node "comment" should be equal to "Factory closure confirmed"

  Scenario: Delete a planning daily exception is not possible for not allowed user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/planning_daily_exceptions/1"
    Then the response status code should be 403

  Scenario: Delete a planning daily exception should be possible for allowed user
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/planning_daily_exceptions/1"
    Then the response status code should be 204
