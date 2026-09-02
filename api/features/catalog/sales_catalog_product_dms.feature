Feature: Test sales catalog product dms

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Sales\ProductDMS" should only be available for intranet user

  Scenario: Request all sales product dms
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/product_dms"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_dms/schemas/product_dms.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Sales\ProductDMS" is exposed on the API
    Then the filter "order[id]" should be available and its type should be "string"

  Scenario: Search sales product dms on partial dms type
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/product_dms?q=Data"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_dms/schemas/product_dms.json"

  Scenario: Request a single sales product dms
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/product_dms/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_dms/schemas/product_dms_details.json"

  Scenario: Basic users can't delete a product dms
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/product_dms/2"
    Then the response status code should be 403

  Scenario: Superuser can delete a product dms
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/product_dms/2"
    Then the response status code should be 204

  Scenario: User PSM can delete a product dms
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/product_dms/3"
    Then the response status code should be 204

  Scenario: Superuser can create a sales product dms
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/product_dms" with the body "tests/fixtures/json/sales/product_dms/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_dms/schemas/product_dms_details.json"

  Scenario: User PSM can create a sales product dms
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/product_dms" with the body "tests/fixtures/json/sales/product_dms/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_dms/schemas/product_dms_details.json"

  Scenario: User PSA can create a sales product dms
    Given I authenticate as the intranet user "user-psa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/product_dms" with the body "tests/fixtures/json/sales/product_dms/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_dms/schemas/product_dms_details.json"
    When I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/product_dms/6"
    Then the response status code should be 204

  Scenario: User PSE can create a sales product dms
    Given I authenticate as the intranet user "user-pse@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/product_dms" with the body "tests/fixtures/json/sales/product_dms/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_dms/schemas/product_dms_details.json"
    When I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/product_dms/7"
    Then the response status code should be 204

  Scenario: Basic user can't create a sales product dms
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/product_dms" with the body "tests/fixtures/json/sales/product_dms/dummies/post.json"
    Then the response status code should be 403
