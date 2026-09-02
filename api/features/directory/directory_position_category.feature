Feature: Test Directory Position Category API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Directory\PositionCategory" should only be available for intranet user

  Scenario: Request all position categories
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/position_categories?order[name]=ASC&order[directHeadcount]=ASC&order[positionCategoryType.name]=ASC&divisions.subDivisions.regions.businessUnits[]=/business_units/16"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_position_category/schemas/directory_position_categories.json"

  Scenario: Request a given position category
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/position_categories/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_position_category/schemas/directory_position_category.json"

  Scenario: Update a given position category with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/position_categories/1" with body:
    """
    {
      "name": "Nope nope nope",
      "description": "nothing",
      "positionCategoryType": "/position_category_types/1",
      "directHeadcount": true
    }
    """
    Then the response status code should be 403

  Scenario: Update a given position category with permission OK (superuser)
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/position_categories/1" with body:
    """
    {
      "name": "Dog & gory",
      "description": "woof",
      "positionCategoryType": "/position_category_types/2",
      "directHeadcount": true
    }
    """
    And the JSON node "name" should be equal to the string "Dog & gory"
    And the JSON node "description" should be equal to the string "woof"
    And the JSON node "directHeadcount" should be true
    And the JSON node "positionCategoryType.name" should be equal to the string "Category Type Family Section Container"
    Then the response status code should be 200

  Scenario: Create a position category with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/position_categories" with body:
    """
    {
      "name": "Nope nope nope",
      "positionCategoryType": "/position_category_types/1",
      "directHeadcount": true
    }
    """
    Then the response status code should be 403

  Scenario: Create a position with permission OK (superuser)
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/position_categories" with body:
    """
    {
      "name": "Bobcat & gory",
      "description": "meeeeeeeow",
      "positionCategoryType": "/position_category_types/2",
      "directHeadcount": true,
      "divisions": ["/divisions/1"]
    }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to the string "/position_categories/3"
    And the JSON node "name" should be equal to the string "Bobcat & gory"
    And the JSON node "description" should be equal to the string "meeeeeeeow"
    And the JSON node "directHeadcount" should be true
    And the JSON node "positionCategoryType.name" should be equal to the string "Category Type Family Section Container"
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_position_category/schemas/directory_position_category.json"

  Scenario: Create a position with an identical name than an existing one
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/position_categories" with body:
    """
    {
      "name": "Bobcat & gory",
      "description": "meeeeeeeow"
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "violations[0].propertyPath" should be equal to "name"
    And the JSON node "violations[0].message" should contain "This value is already used."

  Scenario: Delete a position with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/position_categories/3"
    Then the response status code should be 403

  Scenario: Create a classification on the newly created category before deleting the category (and therefore deleting the classification)
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/position_classifications" with body:
    """
    {
      "positions": ["/positions/1"],
      "positionCategory": "/position_categories/3",
      "businessUnit": "/business_units/16"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_position_classification/schemas/position_classification.json"
    And the JSON node "@id" should be equal to the string "/position_classifications/3"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/position_categories/3"
    Then the response status code should be 204
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/position_classifications/3"
    Then the response status code should be 404
