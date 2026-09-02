Feature: Test sales catalog product family dms
  Scenario: Only Product family DMS of type DATASHEET and where DMS is not confidential and on public families should be accessible and only on collection route
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/product_family_dms"
    Then the response status code should be 200
    And the JSON node "hydra:member" should have 1 element
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/product_family_dms/1"
    Then the response status code should be 403

  Scenario: Product family DMS should not be accessible to vendor users
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/product_family_dms"
    Then the response status code should be 403
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/product_family_dms/1"
    Then the response status code should be 403

  Scenario: Request all sales product family dms
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/product_family_dms"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_family_dms/schemas/product_family_dms.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Sales\ProductFamilyDMS" is exposed on the API
    Then the filter "order[id]" should be available and its type should be "string"
    And the filter "legacyId" should be available and its type should be "int"

  Scenario: Search sales product family dms on partial dms type
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/product_family_dms?q=Data"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_family_dms/schemas/product_family_dms.json"

  Scenario: Request a single sales product family dms
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/product_family_dms/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_family_dms/schemas/product_family_dms_details.json"

  Scenario: Basic users can't delete a product family dms
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/product_family_dms/2"
    Then the response status code should be 403

  Scenario: Superuser can delete a product family dms
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/product_family_dms/2"
    Then the response status code should be 204

  Scenario: User PSM can delete a product family dms
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/product_family_dms/3"
    Then the response status code should be 204

  Scenario: Superuser can create a sales product family dms
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/product_family_dms" with the body "tests/fixtures/json/sales/product_family_dms/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_family_dms/schemas/product_family_dms_details.json"

  Scenario: User PSM can create a sales product family dms
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/product_family_dms" with the body "tests/fixtures/json/sales/product_family_dms/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_family_dms/schemas/product_family_dms_details.json"

  Scenario: User PSE can create a sales product family dms
    Given I authenticate as the intranet user "user-pse@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/product_family_dms" with the body "tests/fixtures/json/sales/product_family_dms/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_family_dms/schemas/product_family_dms_details.json"
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/product_family_dms/6"
    Then the response status code should be 204

  Scenario: User PSA can create a sales product family dms
    Given I authenticate as the intranet user "user-psa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/product_family_dms" with the body "tests/fixtures/json/sales/product_family_dms/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_family_dms/schemas/product_family_dms_details.json"
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/product_family_dms/7"
    Then the response status code should be 204

  Scenario: Basic user can't create a sales product family dms
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/product_family_dms" with the body "tests/fixtures/json/sales/product_family_dms/dummies/post.json"
    Then the response status code should be 403
