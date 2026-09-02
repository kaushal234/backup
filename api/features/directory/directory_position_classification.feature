Feature: Test Directory Position Classification type API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Directory\PositionClassification" should only be available for intranet user

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Directory\PositionClassification" is exposed on the API
    Then the filter "businessUnit" should be available and its type should be "string"
    Then the filter "positionCategory.divisions.subDivisions.regions.businessUnits" should be available and its type should be "string"
    Then the filter "businessUnit.legacyId" should be available and its type should be "int"
    Then the filter "positions.description" should be available and its type should be "string"
    Then the filter "positionCategory.name" should be available and its type should be "string"

  Scenario: Request all position classification
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/position_classifications"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_position_classification/schemas/position_classifications.json"

  Scenario: Request a given position classification
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/position_classifications/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_position_classification/schemas/position_classification.json"

  Scenario: Update a given position classification with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/position_classifications/1" with body:
    """
    {
      "positions": ["/positions/2"]
    }
    """
    Then the response status code should be 403

  Scenario: Update a given position classification with permission OK (user hr)
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/position_classifications/1" with body:
    """
    {
      "positions": ["/positions/2"],
      "budget": 5,
      "reforecast": 6,
      "previousYearCorrectedTotal": 10000,
      "comment": "hasta la mucho bye bye",
      "correction": 1
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_position_classification/schemas/position_classification.json"
    And the JSON node "positions[0].@id" should be equal to the string "/positions/2"
    And the JSON node "budget" should be equal to the number 5
    And the JSON node "reforecast" should be equal to the number 6
    And the JSON node "previousYearCorrectedTotal" should be equal to the number 51
    And the JSON node "comment" should be equal to the string "hasta la mucho bye bye"
    And the JSON node "correction" should be equal to the number 1

  Scenario: Create a position classification with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/position_classifications" with body:
    """
    {
      "positions": ["/positions/1"],
      "positionCategory": "/position_categories/1",
      "businessUnit": "/business_units/5"
    }
    """
    Then the response status code should be 403

  Scenario: Create a classification with permission OK (user hr)
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/position_classifications" with body:
    """
    {
      "positions": ["/positions/3"],
      "positionCategory": "/position_categories/2",
      "businessUnit": "/business_units/16"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_position_classification/schemas/position_classification.json"
    And the JSON node "@id" should be equal to the string "/position_classifications/4"
    And the JSON node "positions[0].@id" should be equal to the string "/positions/3"
    And the JSON node "positionCategory.@id" should be equal to the string "/position_categories/2"
    And the JSON node "businessUnit.@id" should be equal to the string "/business_units/16"

  Scenario: Create a classification with already defined category on bu
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/position_classifications" with body:
    """
    {
      "positions": ["/positions/3"],
      "positionCategory": "/position_categories/2",
      "businessUnit": "/business_units/16"
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "violations[0].propertyPath" should be equal to "positionCategory"
    And the JSON node "violations[0].message" should contain "This position category is already defined for this business unit."

  Scenario: Create a classification with division not active in category
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/position_classifications" with body:
    """
    {
      "positions": ["/positions/3"],
      "positionCategory": "/position_categories/2",
      "businessUnit": "/business_units/18"
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "violations[0].propertyPath" should be equal to "positionCategory"
    And the JSON node "violations[0].message" should contain "This business unit is not active for this position category"

  Scenario: Delete a classification with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/position_classifications/4"
    Then the response status code should be 403

  Scenario: Delete a classification with permission OK (superuser)
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/position_classifications/4"
    Then the response status code should be 204
