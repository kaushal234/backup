Feature: Test product manufacturings can be created and updated

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Manufacturing\ProductManufacturing" should only be available for intranet user

  Scenario: Product manufacturings should be accessible to basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/manufacturing/product_manufacturings"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/product_manufacturing/schemas/product_manufacturings.json"

  Scenario: Product manufacturing detail should be accessible to intranet user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/manufacturing/product_manufacturings/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/product_manufacturing/schemas/product_manufacturing.json"

  Scenario: Create a Product manufacturing directly should not be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/manufacturing/product_manufacturings" with body:
    """
    {}
    """
    Then the response status code should be 405

  Scenario: Update a Product manufacturing directly should not be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/manufacturing/product_manufacturings/1" with body:
    """
    {}
    """
    Then the response status code should be 405
