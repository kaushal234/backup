Feature: Test Directory Position Category type API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Directory\PositionCategoryType" should only be available for intranet user

  Scenario: Request all position category types
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/position_category_types?order[name]=ASC"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_position_category_type/schemas/directory_position_category_types.json"

  Scenario: Request a given position category type
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/position_category_types/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_position_category_type/schemas/directory_position_category_type.json"

  Scenario: Update a given position category type with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/position_category_types/1" with body:
    """
    {
      "name": "Nope nope nope"
    }
    """
    Then the response status code should be 403

  Scenario: Update a given position category type with permission OK (superuser)
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/position_category_types/1" with body:
    """
    {
      "name": "A box in a box in a box"
    }
    """
    And the JSON node "name" should be equal to the string "A box in a box in a box"
    Then the response status code should be 200

  Scenario: Create a position category type with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/position_category_types" with body:
    """
    {
      "name": "Nope nope nope"
    }
    """
    Then the response status code should be 403

  Scenario: Create a position with permission OK (superuser)
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/position_category_types" with body:
    """
    {
      "name": "A box in a box in a box in a box"
    }
    """
    Then the response status code should be 201
    And the JSON node "name" should be equal to the string "A box in a box in a box in a box"
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_position_category_type/schemas/directory_position_category_type.json"

  Scenario: Create a position with an identical name than an existing one
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/position_category_types" with body:
    """
    {
      "name": "A box in a box in a box in a box"
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "violations[0].propertyPath" should be equal to "name"
    And the JSON node "violations[0].message" should contain "This value is already used."

  Scenario: Delete a position category type with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/position_category_types/3"
    Then the response status code should be 403

  Scenario: Delete a used category type
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/position_category_types/2"
    Then the response status code should be 422
    And the JSON node "hydra:description" should match "/^The position category type 'Category Type Family Section Container' is not deletable because it is used by \d+ position category\(ies\) \(.*\)$/"

  Scenario: Delete a position category type with permission OK (superuser)
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/position_category_types/3"
    Then the response status code should be 204
