Feature: Test Service Area API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Service\ServiceArea" should only be available for intranet user

  Scenario: Request all Service Areas
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service_areas"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/service_areas/schemas/service_areas.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Service\ServiceArea" is exposed on the API
    Then the filter "order[name]" should be available and its type should be "string"
    Then the filter "order[representative.lastname]" should be available and its type should be "string"
    And the filter "representative" should be available and its type should be "string"
    And the filter "airports" should be available and its type should be "string"
    And the filter "airports.country" should be available and its type should be "string"
    And the filter "airports.code" should be available and its type should be "string"
    And the filter "q" should be available and its type should be "string"

  Scenario: Request a given Service Area
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service_areas/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/service_areas/schemas/service_area.json"

  Scenario: Update a given Service Area with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service_areas/1" with body:
    """
    {
        "name": "Europe West Updated"
    }
    """
    Then the response status code should be 403

  Scenario: Update a given Service Area with permission OK
    Given I authenticate as the intranet user "user-moo-cat@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service_areas/1" with body:
    """
    {
        "name": "Europe West Updated"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/service_areas/schemas/service_area.json"
    And the JSON node "name" should be equal to "Europe West Updated"

  Scenario: Create a Service Area without permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/service_areas" with body:
    """
    {
        "name": "Asia Pacific",
        "representative": "/people/64",
        "airports": ["/airports/61"]
    }
    """
    Then the response status code should be 403

  Scenario: Create a Service Area with permission OK
    Given I authenticate as the intranet user "user-moo-cat@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/service_areas" with body:
    """
    {
        "name": "Asia Pacific",
        "representative": "/people/64",
        "airports": ["/airports/61"]
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/service_areas/schemas/service_area.json"

  Scenario: Create a Service Area with permission OK but name already existing
    Given I authenticate as the intranet user "user-moo-cat@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/service_areas" with body:
    """
    {
        "name": "Asia Pacific",
        "representative": "/people/64",
        "airports": ["/airports/61"]
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].message" should contain "This Service Area name is already used"

  Scenario: Create a Service Area without any airport
    Given I authenticate as the intranet user "user-moo-cat@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/service_areas" with body:
    """
    {
        "name": "Empty Zone",
        "representative": "/people/64",
        "airports": []
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].message" should contain "A Service Area must contain at least one airport"

  Scenario: Delete a given Service Area with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/service_areas/2"
    Then the response status code should be 403

  Scenario: Delete a given Service Area with permission OK
    Given I authenticate as the intranet user "user-moo-cat@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/service_areas/2"
    Then the response status code should be 204