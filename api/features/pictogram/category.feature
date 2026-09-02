Feature: Test Pictogram Category API

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Engineering\Pictogram\Category" is exposed on the API
    Then the filter "name" should be available and its type should be "string"
    And the filter "id" should be available and its type should be "int"
    And the filter "order[name]" should be available and its type should be "string"
    And the filter "order[id]" should be available and its type should be "string"
    And the filter "q" should be available and its type should be "string"

  Scenario: Request all pictogram categories
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/engineering/pictogram/categories"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/pictogram/schemas/categories.json"

  Scenario: Request pictogram categories with filters
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/engineering/pictogram/categories?name=Boom"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/pictogram/schemas/categories.json"
    And the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0].name" should be equal to the string "Boom"
    And the JSON node "hydra:member[0].color" should be null

  Scenario: Request one pictogram category
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/engineering/pictogram/categories/2"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/pictogram/schemas/category.json"
    And the JSON node "@id" should be equal to the string "/engineering/pictogram/categories/2"
    And the JSON node "id" should be equal to the string "2"
    And the JSON node "name" should be equal to the string "Braking"
    And the JSON node "color" should be equal to the string "#FF0000"

  Scenario: As a engineer, I can create a pictogram category
    Given I authenticate as the intranet user "user-eng@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/engineering/pictogram/categories" with body:
    """
    {
      "name": "Test new category",
      "color": "CC6969"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/pictogram/schemas/category.json"
    And the JSON node "name" should be equal to the string "Test new category"
    And the JSON node "color" should be equal to the string "CC6969"

  Scenario: As a engineer, I can update a pictogram category
    Given I authenticate as the intranet user "user-eng@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/engineering/pictogram/categories/16" with body:
    """
    {
      "name": "Test updated category",
      "color": "AABBCC"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/pictogram/schemas/category.json"
    And the JSON node "@id" should be equal to the string "/engineering/pictogram/categories/16"
    And the JSON node "name" should be equal to the string "Test updated category"
    And the JSON node "color" should be equal to the string "AABBCC"

  Scenario: As a engineer, I can remove a pictogram category
    Given I authenticate as the intranet user "user-eng@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/engineering/pictogram/categories/17"
    Then the response status code should be 204
    Given I authenticate as the intranet user "user-eng@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/engineering/pictogram/categories/17"
    Then the response status code should be 404

  Scenario: As a basic user, I am not allow to create a pictogram category
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/engineering/pictogram/categories"
    Then the response status code should be 403

  Scenario: As a basic user, I am not allow to update a pictogram category
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/engineering/pictogram/categories/16"
    Then the response status code should be 403

  Scenario: As a basic user, I am not allow to delete a pictogram category
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/engineering/pictogram/categories/16"
    Then the response status code should be 403