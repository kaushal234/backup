Feature: Test sales catalog products family

  Scenario: Product family should not be accessible to XU, except for public ones
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/product_families"
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    #Family 2 is not public
    When I send a "GET" request to "/sales/product_families/2"
    Then the response status code should be 404
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    #Family 1 is public
    When I send a "GET" request to "/sales/product_families/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_family/schemas/product_family.json"

  Scenario: Request all sales product families without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/product_families"
    Then the response status code should be 401

  Scenario: Request a single sales product family without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/product_families/6"
    Then the response status code should be 401

  Scenario: Request all sales products families
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/product_families"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_family/schemas/product_families.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Sales\ProductFamily" is exposed on the API
    Then the filter "order[name]" should be available and its type should be "string"
    And the filter "order[id]" should be available and its type should be "string"
    And the filter "hidden" should be available and its type should be "bool"
    And the filter "publicForTLD" should be available and its type should be "bool"
    And the filter "productType" should be available and its type should be "string"
    And the filter "legacyId" should be available and its type should be "int"
    And the filter "normalization_groups_override[]" should be available and its type should be "string"

  Scenario: Search sales product families on partial name
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/product_families?q=Family"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_family/schemas/product_families.json"

  Scenario: Search sales products families on partial product type name
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/product_families?q=Atron"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_family/schemas/product_families.json"

  Scenario: Request a single sales product family
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/product_families/5"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_family/schemas/product_family.json"

  Scenario: basic users can't update a product family
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/product_families/5" with the body "tests/fixtures/json/sales/product_family/dummies/put.json"
    Then the response status code should be 403

  Scenario: Superuser can update a product family
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/product_families/5" with the body "tests/fixtures/json/sales/product_family/dummies/put.json"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_family/schemas/product_family.json"

  Scenario: User PSM can update a product family
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/product_families/5" with body:
    """
    {
      "name": "PSM EDITED NAME"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_family/schemas/product_family.json"

  Scenario: When product family is hidden, it has to be non public
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/product_families/8" with body:
    """
    {
      "hidden": true
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_family/schemas/product_family.json"
    And the JSON node "hidden" should be true
    And the JSON node "publicForTLD" should be false

  Scenario: User PSA can update a product family
    Given I authenticate as the intranet user "user-psa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/product_families/5" with body:
    """
    {
      "name": "PSA EDITED NAME"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_family/schemas/product_family.json"

  Scenario: User PSE can update a product family
    Given I authenticate as the intranet user "user-pse@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/product_families/5" with body:
    """
    {
      "name": "PSE EDITED NAME"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_family/schemas/product_family.json"

  Scenario: MOO of CAT module can update a product family
    Given I authenticate as the intranet user "user-pse@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/product_families/5" with body:
    """
    {
      "name": "MOO EDITED NAME"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_family/schemas/product_family.json"

  Scenario: User CM of CAT module can update a product family
    Given I authenticate as the intranet user "user-cm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/product_families/5" with body:
    """
    {
      "name": "CM EDITED NAME"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_family/schemas/product_family.json"

  Scenario: Superuser can create a sales product family
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/product_families" with the body "tests/fixtures/json/sales/product_family/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_family/schemas/product_family.json"

  Scenario: Basic user can't create a sales product family
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/product_families" with the body "tests/fixtures/json/sales/product_family/dummies/post.json"
    Then the response status code should be 403

  Scenario: Request all sales product families for a select list
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/product_families?normalization_groups_override[]=catalogue_family_list"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_family/schemas/product_family_list.json"

  Scenario: As a superuser, I can delete a family and its products
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/product_families/24"
    Then the response status code should be 204
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "sales/products?family=/sales/product_families/24"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product/schemas/products.json"
    And the JSON node "hydra:totalItems" should be equal to 0

  Scenario: As a superuser, I can't delete a family when a product is used
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/product_families/3"
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "The Product family 'To be not deleted' is not deletable because it is used by 3 Demos (3, 6, 7)"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/products?family=/sales/product_families/3"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product/schemas/products.json"
    And the JSON node "hydra:totalItems" should be equal to 2

  Scenario: As a basic user, I can't delete a family
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/product_families/23"
    Then the response status code should be 403

  Scenario: As a superuser, I can see manufacturing factories on a family
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/product_families/26"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_family/schemas/product_family.json"
    And the JSON node "manufacturingFactories" should have 3 elements
    And the JSON node "manufacturingFactories[0].@id" should be equal to the string "/locations/29"
    And the JSON node "manufacturingFactories[0].@type" should be equal to the string "Location"
    And the JSON node "manufacturingFactories[0].name" should be equal to the string "location_factory"
    And the JSON node "manufacturingFactories[0].erp" should be equal to 540

  Scenario: User PSA can update manufacturing factories on a product family
    Given I authenticate as the intranet user "user-psa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/product_families/7" with body:
    """
    {
      "manufacturingFactories": [
        "/locations/29",
        "/locations/30",
        "/locations/36"
      ]
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_family/schemas/product_family.json"
    And the JSON node "manufacturingFactories" should have 3 elements
    And the JSON node "manufacturingFactories[0].@id" should be equal to the string "/locations/29"
    And the JSON node "manufacturingFactories[1].@id" should be equal to the string "/locations/30"
    And the JSON node "manufacturingFactories[2].@id" should be equal to the string "/locations/36"

  Scenario: User PSA can update manufacturing factories on a product family
    Given I authenticate as the intranet user "user-psa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/product_families/7" with body:
    """
    {
      "manufacturingFactories": [
        "/locations/29",
        "/locations/30"
      ]
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_family/schemas/product_family.json"
    And the JSON node "manufacturingFactories" should have 2 elements
    And the JSON node "manufacturingFactories[0].@id" should be equal to the string "/locations/29"
    And the JSON node "manufacturingFactories[1].@id" should be equal to the string "/locations/30"
    And the JSON node "manufacturingFactories[2].@id" should not exist
