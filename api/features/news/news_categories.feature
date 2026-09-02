Feature: Test news categories API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\News\NewsCategory" should only be available for intranet user

  Scenario: Request all news categories
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/news_categories"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/news_category/schemas/news_categories.json"

  Scenario: Request a single news category
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/news_categories/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/news_category/schemas/news_category.json"

  Scenario: Update a given news category - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/news_categories/1" with body:
    """
    {
      "name": "new name"
    }
    """
    Then the response status code should be 403

  Scenario: Update a given news category - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/news_categories/1" with body:
    """
    {
      "name": "new name"
    }
    """
    Then the response status code should be 200
    And the JSON node "name" should be equal to "new name"

  Scenario: Create a news category - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/news_categories" with body:
    """
    {
      "name": "my pretty category"
    }
    """
    Then the response status code should be 403

  Scenario: Create a news category - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/news_categories" with body:
    """
    {
      "name": "my pretty category"
    }
    """
    Then the response status code should be 201
    And the JSON node "name" should be equal to "my pretty category"

  Scenario: Create a news category with the same name
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/news_categories" with body:
    """
    {
      "name": "my pretty category"
    }
    """
    Then the response status code should be 422

  Scenario: Delete a news category - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    When I send a "DELETE" request to "/news_categories/1"
    Then the response status code should be 403

  Scenario: Delete a news category - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I send a "DELETE" request to "/news_categories/6"
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "The news category 'Category' is not deletable because it is used by 1 News (26)"

  Scenario: Request news category ordered by name
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/news_categories?order[name]"
    Then the response status code should be 200

  Scenario: Filters are declared on resource
    Given the class "App\Entity\News\NewsCategory" is exposed on the API
    Then the filter "legacyId" should be available and its type should be "int"
