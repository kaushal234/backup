Feature: Test Directory Network API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Sales\SalesArea" should only be available for intranet user

  Scenario: Request all Sales Areas
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales_areas"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales_areas/schemas/sales_areas.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Sales\SalesArea" is exposed on the API
    Then the filter "order[country.name]" should be available and its type should be "string"
    And the filter "country" should be available and its type should be "string"
    And the filter "asm" should be available and its type should be "string"
    And the filter "sso" should be available and its type should be "string"

  Scenario: Request a given Sales Area
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales_areas/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales_areas/schemas/sales_area.json"

  Scenario: Update a given Sales Area with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales_areas/1" with body:
    """
    {
        "asm": "/people/13"
    }
    """
    Then the response status code should be 403

  Scenario: Update a given Sales Area with permission OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales_areas/1" with body:
    """
    {
        "asm": "/people/13"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales_areas/schemas/sales_area.json"
    And the JSON node "asm.@id" should not be equal to "/people/11"

  Scenario: Create a Sales Area without permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales_areas" with body:
    """
    {
        "country": "/countries/1",
        "asm": "/people/11",
        "network": "/locations/28"
    }
    """
    Then the response status code should be 403

  Scenario: Create a Sales Area with permission OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales_areas" with body:
    """
    {
        "country": "/countries/1",
        "asm": "/people/12",
        "sso": "/locations/28"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales_areas/schemas/sales_area.json"

  Scenario: Create a Sales Area with permission OK but already existing
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales_areas" with body:
    """
    {
        "country": "/countries/1",
        "asm": "/people/12",
        "sso": "/locations/28"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].message" should contain "This ASM is already linked to this country for this SSO"

  Scenario: Delete a given Sales Area with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales_areas/3"
    Then the response status code should be 403

  Scenario: Delete a given Sales Area with permission OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales_areas/3"
    Then the response status code should be 204
