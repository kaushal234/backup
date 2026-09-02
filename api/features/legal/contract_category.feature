Feature: Test Category

  Scenario: Request all categories should be possible only for everyone
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contract/categories"
    Then the response status code should be 200

  Scenario: Request a single category should be possible only for everyone
    Given I authenticate as the intranet user "user-gch@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contract/categories/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract_category/schemas/category.json"

  Scenario: No POST allowed on category
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/contract/categories" with body:
    """
    {
      "name": "Duku"
    }
    """
    Then the response status code should be 405

  Scenario: PUT allowed on displayedName category only for MOO, GKU and Chairman
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contract/categories/1" with body:
    """
    {
      "name": "Défaice"
    }
    """
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contract/categories/1" with body:
    """
    {
      "name": "Défaice"
    }
    """
    Then the response status code should be 200
    And the JSON node "name" should be equal to "BANK"
    Given I authenticate as the intranet user "user-chairman@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contract/categories/1" with body:
    """
    {
      "displayedName": "Défaice"
    }
    """
    Then the response status code should be 200
    And the JSON node "displayedName" should be equal to "Défaice"


  Scenario: No DELETE allowed on category
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contract/categories/1"
    Then the response status code should be 405



