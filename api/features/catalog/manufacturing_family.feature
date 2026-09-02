Feature: Test manufacturing family

  Scenario: Manufacturing families should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/manufacturing/manufacturing_families"
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/manufacturing/manufacturing_families/2"
    Then the response status code should be 403

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Manufacturing\ManufacturingFamily" is exposed on the API
    Then the filter "order[name]" should be available and its type should be "string"

  Scenario: As a basic user I can see all manufacturing families
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/manufacturing/manufacturing_families"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/manufacturing_family/schemas/manufacturing_families.json"

  Scenario: As a basic user, I can request one manufacturing family
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/manufacturing/manufacturing_families/2"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/manufacturing_family/schemas/manufacturing_family.json"

  Scenario: As a basic user, I can't create a manufacturing family
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/manufacturing/manufacturing_families" with body:
    """
    {
      "name": "SisiLafamille",
      "testDuration": 64
    }
    """
    Then the response status code should be 403

  Scenario: As a basic user, I can't update a manufacturing family
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/manufacturing/manufacturing_families/2" with body:
    """
      {
        "name": "SisiLafamille",
        "testDuration": 12
      }
    """
    Then the response status code should be 403

  Scenario: As a basic user, I can't delete a manufacturing family
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "DELETE" request to "/manufacturing/manufacturing_families/2"
    Then the response status code should be 403

  Scenario: As a MLM user, I can create a manufacturing family
    Given I authenticate as the intranet user "user-mlm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/manufacturing/manufacturing_families" with body:
    """
    {
      "name": "LaFamillefaFon",
      "testDuration": 12
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/manufacturing_family/schemas/manufacturing_family.json"

  Scenario: As a MLM user, I can update a manufacturing family
    Given I authenticate as the intranet user "user-mlm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/manufacturing/manufacturing_families/29" with body:
    """
    {
      "name": "LaFamilleTintin",
      "testDuration": 12
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/manufacturing_family/schemas/manufacturing_family.json"

  Scenario: As a MLM user, I can't delete a manufacturing family linked to a product
    Given I authenticate as the intranet user "user-mlm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "DELETE" request to "/manufacturing/manufacturing_families/2"
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "The Manufacturing Family 'Stan' is not deletable because it is used by 1 product (Produit Test)"

  Scenario: As a MLM user, I can delete a manufacturing family
    Given I authenticate as the intranet user "user-mlm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "DELETE" request to "/manufacturing/manufacturing_families/29"
    Then the response status code should be 204




