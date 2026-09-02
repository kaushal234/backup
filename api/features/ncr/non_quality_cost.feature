Feature: Test Non Quality Cost Entity

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Quality\NonQualityCost" should only be available for intranet user

  Scenario: Request all non quality costs as authorized application
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/non_quality_costs"
    Then the response status code should be 403

  Scenario: Request a single non quality cost as authorized application
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/non_quality_costs/1"
    Then the response status code should be 403

  Scenario: Request all non quality costs as basic user should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/non_quality_costs"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/non_quality_cost/schemas/non_quality_costs.json"

  Scenario: Request a single non quality cost as basic user should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/non_quality_costs/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/non_quality_cost/schemas/non_quality_cost.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Quality\NonQualityCost" is exposed on the API
    Then the filter "order[location.name]" should be available and its type should be "string"

  Scenario: Update a non quality cost should not be possible as basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/non_quality_costs/1" with body:
    """
    {
      "defaultCosts": 45
    }
    """
    Then the response status code should be 403

  Scenario: Update a non quality cost should be possible as user qam
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/non_quality_costs/1" with body:
    """
    {
      "defaultCosts": 45
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/non_quality_cost/schemas/non_quality_cost.json"
    And the JSON node "defaultCosts" should be equal to the number 45

  Scenario: Create a non quality cost should not be possible if already exists on location
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/non_quality_costs" with body:
    """
    {
      "location": "/locations/29",
      "defaultCosts": 45
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].message" should be equal to the string "Default costs already defined for this location"

  Scenario: Create a non quality cost should not be possible for user basic
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/non_quality_costs" with body:
    """
    {
      "location": "/locations/29",
      "defaultCosts": 45
    }
    """
    Then the response status code should be 403

  Scenario: Create a non quality cost should be possible for user qam
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/non_quality_costs" with body:
    """
    {
      "location": "/locations/30",
      "defaultCosts": 45
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/non_quality_cost/schemas/non_quality_cost.json"
    And the JSON node "location.@id" should be equal to the string "/locations/30"
    And the JSON node "defaultCosts" should be equal to the number 45

