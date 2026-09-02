Feature: Test Sub Category

  Scenario: Request all sub categories should be possible for everyone
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contract/sub_categories"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract_sub_category/schemas/sub_categories.json"

  Scenario: Request a single sub category should be possible for everyone
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contract/sub_categories/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract_sub_category/schemas/sub_category.json"

  Scenario: No POST allowed on sub category
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/contract/sub_categories" with body:
    """
    {
      "name": "Sous-catégorie Contrats IT",
      "category": "/contract/categories/1"
    }
    """
    Then the response status code should be 405

  Scenario: PUT allowed on displayedName sub category only for MOO, GKU and Chairman
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contract/sub_categories/1" with body:
    """
    {
      "name": "Duku"
    }
    """
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contract/sub_categories/1" with body:
    """
    {
      "name": "Duku"
    }
    """
    Then the response status code should be 200
    And the JSON node "name" should be equal to "Permanent without ending"
    Given I authenticate as the intranet user "user-chairman@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contract/sub_categories/1" with body:
    """
    {
      "displayedName": "Duku"
    }
    """
    Then the response status code should be 200
    And the JSON node "displayedName" should be equal to "Duku"

  Scenario: No DELETE allowed on sub category
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contract/sub_categories/1"
    Then the response status code should be 405



