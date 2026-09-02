Feature: Test sales catalog products

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Sales\Product" should only be available for "intranet,extranet" user

  Scenario: Request all sales products
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "sales/products"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product/schemas/products.json"

  Scenario: As an Extranet User, Request all sales products
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "sales/products"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product/schemas/products.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Sales\Product" is exposed on the API
    And the filter "order[family.name]" should be available and its type should be "string"
    And the filter "order[family.productType.englishName]" should be available and its type should be "string"
    And the filter "order[name]" should be available and its type should be "string"
    And the filter "order[id]" should be available and its type should be "string"
    And the filter "hidden" should be available and its type should be "bool"
    And the filter "name" should be available and its type should be "string"
    And the filter "family" should be available and its type should be "string"
    And the filter "financeFamily" should be available and its type should be "string"
    And the filter "manufacturingFamily" should be available and its type should be "string"
    And the filter "legacyId" should be available and its type should be "int"
    And the filter "name" should be available and its type should be "string"
    And the filter "q" should be available and its type should be "string"
    And the filter "normalization_groups[]" should be available and its type should be "string"
    And the filter "normalization_groups_override[]" should be available and its type should be "string"

  Scenario: Search sales products on partial name
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "sales/products?q=Produit&context[no_headers]=true"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product/schemas/products.json"

  Scenario: Search sales products for export
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "sales/products?normalization_groups_override[]=product_export"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product/schemas/products_export.json"

  Scenario: As the authorized app "link" I should be able to get products for export in json format
    Given I authenticate as the authorized application "link"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "sales/products?normalization_groups_override[]=product_export"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product/schemas/products_export.json"

  Scenario: As an authorized app other than "link" I should not be authorized to get products for export
    Given I authenticate as the authorized application "pio"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "sales/products?normalization_groups_override[]=product_export"
    Then the response status code should be 403

  Scenario: As the authorized app "link" I should be able to get products for export in csv format
    Given I authenticate as the authorized application "link"
    And I add "Accept" header equal to "text/csv"
    When I send a "GET" request to "sales/products?normalization_groups_override[]=product_export"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "text/csv; charset=utf-8"
    Then the header "Content-Disposition" should be equal to "inline; filename=data.csv"
    And the csv file headers are:
      | id | name | hidden | erpLocation | family | financeFamily | productType | productTypeId | productTypeDMSPhoto | familyDMSPhoto | familyId | dmsPhoto |

  Scenario: Search sales products on partial family name
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "sales/products?q=Family"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product/schemas/products.json"

  Scenario: Request a single sales products
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "sales/products/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product/schemas/product.json"

  Scenario: As an extranet User, a single sales products should not be accessible if not on a public group or hidden
#    Product hidden
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "sales/products/36"
    Then the response status code should be 404
#    Not on a public group
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "sales/products/35"
    Then the response status code should be 404

  Scenario: basic users can't update a product
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "sales/products/2" with the body "tests/fixtures/json/sales/product/dummies/put.json"
    Then the response status code should be 403

  Scenario: Superuser can update any product
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "sales/products/31" with the body "tests/fixtures/json/sales/product/dummies/put.json"
    Then the response status code should be 200
    And the JSON node "productStandardItems" should have 2 elements
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product/schemas/product.json"

  Scenario: User PLANNER can only update the standard items of a product
    Given I authenticate as the intranet user "user-planner@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "sales/products/1" with body:
    """
    {
      "name": "PLANNER EDITED NAME",
      "productStandardItems":
      [
        {
          "standardItem": "TGV454",
          "factory": "/locations/29"
        }
      ]
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product/schemas/product.json"
    And the JSON node "productStandardItems[0].standardItem" should be equal to "TGV454"
    And the JSON node "productStandardItems[0].factory.@id" should be equal to "/locations/29"
    And the JSON node "name" should not be equal to "PLANNER EDITED NAME"


  Scenario: User PSM can update a product if he has ROLE_PSM on location of product but can't update the model base hours nor updating product manufacturing
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "sales/products/2" with body:
    """
    {
      "name": "PSM EDITED NAME",
      "financeFamily": "/finance/finance_families/4",
      "productManufacturings": [
        {
          "id": 2,
          "industrialIncorporationParameter": 99,
          "factoryStandardEfficiency": 99
        }
      ]
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product/schemas/product.json"
    And the JSON node "name" should be equal to "PSM EDITED NAME"
    And the JSON node "financeFamily.@id" should be equal to "/finance/finance_families/4"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "manufacturing/product_manufacturings/2"
    Then the response status code should be 200
    And the JSON node "industrialIncorporationParameter" should be equal to 10
    And the JSON node "factoryStandardEfficiency" should be equal to 10

  Scenario: User PSM can't update a product if he hasn't ROLE_PSM on location of product
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "sales/products/31" with body:
    """
    {
      "name": "PSM EDITED NAME"
    }
    """
    Then the response status code should be 200
    And the JSON node "name" should be equal to "Name test"

  Scenario: The CAT MOO can update any product
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "sales/products/2" with body:
    """
    {
      "name": "MOO EDITED NAME"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product/schemas/product.json"
    And the JSON node "name" should be equal to "MOO EDITED NAME"

  Scenario: User PSE can update a product if he has ROLE_PSE on location of product
    Given I authenticate as the intranet user "user-pse@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "sales/products/2" with body:
    """
    {
      "name": "PSE EDITED NAME"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product/schemas/product.json"
    And the JSON node "name" should be equal to "PSE EDITED NAME"

  Scenario: User PSE can't update a product if he hasn't ROLE_PSE on location of product
    Given I authenticate as the intranet user "user-pse@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "sales/products/31" with body:
    """
    {
      "name": "PSE EDITED NAME"
    }
    """
    Then the response status code should be 403

  Scenario: User PSA can update a product if he has ROLE_PSA on location of product
    Given I authenticate as the intranet user "user-psa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "sales/products/2" with body:
    """
    {
      "name": "PSA EDITED NAME"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product/schemas/product.json"
    And the JSON node "name" should be equal to "PSA EDITED NAME"

  Scenario: User CFO can update only the finance family field in a product
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "sales/products/2" with body:
    """
    {
      "name": "CFO NAME",
      "financeFamily": "/finance/finance_families/4"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product/schemas/product.json"
    And the JSON node "name" should be equal to "PSA EDITED NAME"
    And the JSON node "financeFamily.@id" should be equal to "/finance/finance_families/4"

  Scenario: User PSA can't update a product if he hasn't ROLE_PSA on location of product
    Given I authenticate as the intranet user "user-psa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "sales/products/31" with body:
    """
    {
      "name": "PSA EDITED NAME"
    }
    """
    Then the response status code should be 403

  Scenario: Superuser can create a sales product
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "sales/products" with the body "tests/fixtures/json/sales/product/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product/schemas/product.json"

  Scenario: Superuser can't create a sales product if its name already exists
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "sales/products" with the body "tests/fixtures/json/sales/product/dummies/post.json"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "name: This value is already used"

  Scenario: Basic user can't create a sales product
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "sales/products" with the body "tests/fixtures/json/sales/product/dummies/post.json"
    Then the response status code should be 403

  Scenario: As a superuser, I can't delete a product if it's used
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/products/33"
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "The Product 'To be not deleted' is not deletable because it is used by 3 Demos (3, 6, 7)"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "sales/products/33"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product/schemas/product.json"

  Scenario: As a superuser, I can delete a product if it's not used
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/products/30"
    Then the response status code should be 204
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "sales/products/30"
    Then the response status code should be 404
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "sales/product_dms/5"
    Then the response status code should be 404

  Scenario: As a basic user, I can't delete a product
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/products/29"
    Then the response status code should be 403
