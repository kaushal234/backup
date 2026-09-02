Feature: Test sales catalog product types

  Scenario: Product types should not be accessible to vendor users
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/product_types"
    Then the response status code should be 403
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/product_types/1"
    Then the response status code should be 403

  Scenario: Only public product types should be accessible for extranet users
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/product_types"
    Then the response status code should be 200
    And the JSON node "hydra:member" should have 1 element
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    #Product type 1 is public
    When I send a "GET" request to "/sales/product_types/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_type/schemas/product_type.json"
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    #Product type 2 is not public
    When I send a "GET" request to "/sales/product_types/2"
    Then the response status code should be 404

  Scenario: Request all sales product types
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/product_types"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_type/schemas/product_types.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Sales\ProductType" is exposed on the API
    Then the filter "order[englishName]" should be available and its type should be "string"
    And the filter "order[id]" should be available and its type should be "string"
    And the filter "legacyId" should be available and its type should be "int"
    And the query parameter "autocomplete" should be available

  Scenario: Search sales product type on english name
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/product_types?englishName=Type English"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_type/schemas/product_types.json"

  Scenario: Request all sales product types for a select list
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/product_types?normalization_groups_override[]=catalogue_type_list"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_type/schemas/product_type_list.json"

  Scenario: Request a single sales product type
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/product_types/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_type/schemas/product_type.json"

  Scenario: Basic users can't update a product type
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/product_types/2" with the body "tests/fixtures/json/sales/product_type/dummies/put.json"
    Then the response status code should be 403

  Scenario: PSM can't update a product type
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/product_types/2" with the body "tests/fixtures/json/sales/product_type/dummies/put.json"
    Then the response status code should be 403

  Scenario: PSA can't update a product type
    Given I authenticate as the intranet user "user-psa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/product_types/2" with the body "tests/fixtures/json/sales/product_type/dummies/put.json"
    Then the response status code should be 403

  Scenario: PSE can't update a product type
    Given I authenticate as the intranet user "user-pse@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/product_types/2" with the body "tests/fixtures/json/sales/product_type/dummies/put.json"
    Then the response status code should be 403

  Scenario: Superuser can update a product type
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/product_types/2" with the body "tests/fixtures/json/sales/product_type/dummies/put.json"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_type/schemas/product_type.json"

  Scenario: User GTD can update a product type
    Given I authenticate as the intranet user "user-gtd@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/product_types/2" with body:
    """
    {
      "englishName": "PSM EDITED NAME"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_type/schemas/product_type.json"

  Scenario: MOO of CAT module can update a product type
    Given I authenticate as the intranet user "user-moo-cat@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/product_types/2" with body:
    """
    {
      "englishName": "MOO EDITED NAME"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_type/schemas/product_type.json"

  Scenario: Superuser can create a sales product type
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/product_types" with the body "tests/fixtures/json/sales/product_type/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_type/schemas/product_type.json"

  Scenario: Basic user can't create a sales product type
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/product_types" with the body "tests/fixtures/json/sales/product_type/dummies/post.json"
    Then the response status code should be 403

  Scenario: Some product types can't be renamed because of hardcoded values in SFR notifications...
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/product_types/3" with body:
    """
    {
      "englishName": "Bobby"
    }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should contain "Value 'Air Conditioners' is locked and can't be updated"
    And the JSON node "violations[0].propertyPath" should be equal to "englishName"
    And the JSON node "violations[0].message" should contain "Value 'Air Conditioners' is locked and can't be updated"

